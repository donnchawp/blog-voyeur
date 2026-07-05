<?php
/*
Plugin Name: Blog Voyeur
Plugin URI: http://ocaoimh.ie/blog-voyeur/
Description: Peek at what your users are doing
Version: 0.2
Author: Donncha O Caoimh
Author URI: http://ocaoimh.ie/
*/

/*  Copyright 2007 Donncha O Caoimh (email : donncha@ocaoimh.ie)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function voyeur_install() {
	global $wpdb;

	$table_name = $wpdb->prefix . "voyeur";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name)
		return true;

	$sql = "CREATE TABLE {$table_name} (
		id bigint(20) NOT NULL auto_increment,
		voyeur_date datetime NOT NULL default '0000-00-00 00:00:00',
		name varchar(100) NOT NULL default '',
		email varchar(200) NOT NULL default '',
		url varchar(100) NOT NULL default '',
		request varchar(200) NOT NULL default '',
		referrer varchar(200) NOT NULL default '',
		PRIMARY KEY  (`id`),
		KEY `email` (`email`),
		KEY `url` (`url`),
		KEY `request` (`request`)
		);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}
register_activation_hook(__FILE__,'voyeur_install');

function voyeur_add_pages() {
	add_management_page('Voyeur', 'Voyeur', 'manage_options', 'blogvoyeur', 'voyeur_manage_page');
}
add_action('admin_menu', 'voyeur_add_pages');

function voyeur_manage_page() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'voyeur';
	$out = false;
	if( !isset( $_GET[ 'action' ] ) )
		$out = wp_cache_get( 'voyeur_list' );
	if( $out ) {
		echo $out;
		return;
	}
	$out = "<div class='wrap'>";
	$table_name = $wpdb->prefix . 'voyeur';
	if( $_GET[ 'action' ] == 'user' ) {
		$last = $wpdb->get_results( "SELECT * FROM $table_name WHERE email = '" . $wpdb->escape( $_GET[ 'email' ] ) . "' ORDER BY ID DESC LIMIT 0,100" );
		if( !is_array( $last ) )
			$last = array();
		foreach( $last as $visit ) {
			$key = md5( $visit->email . $visit->request );
			if( !isset( $visits[ $key ] ) )
				$visits[ $key ] = $visit;
		}
	} else {
		$last = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY ID DESC LIMIT 0,100" );
		if( !is_array( $last ) )
			$last = array();
		foreach( $last as $visit ) {
			if( !isset( $visits[ $visit->email ] ) )
				$visits[ $visit->email ] = $visit;
		}
	}
	if( !is_array( $visits ) ) {
		_e( 'Sorry. no visitor traffic yet.' );
		echo '</div>';
		return;
	}
	$out .= "<table><tr><th>Last Visit</th><th>Name</th><th>Visited</th><th>Last Comment</th></tr>\n";
	foreach( $visits as $visit ) {
		$comment = $wpdb->get_row( "SELECT * FROM {$wpdb->comments} WHERE comment_author_email = '{$visit->email}' ORDER BY comment_ID DESC LIMIT 0,1" );
		$post = get_post( $comment->comment_post_ID );
		$out .= "<tr><td>{$visit->voyeur_date}</td><td><a href='?page=blogvoyeur&action=user&email=" . urlencode( $visit->email ) . "'>{$visit->name}</a>";
		if( $visit->url ) $out .= " (<a href='{$visit->url}'>homepage</a>)";
		$out .= "</td>\n";
		if( $visit->request == 'News Reader' ) {
			$out .= "<td>News Reader</td>";
		} else {
			$out .= "<td><a href='{$visit->request}'>" . substr( $visit->request, 0, 40 ) . "</a></td>";
		}
		$out .= "<td><a href='" . get_permalink( $post->ID ) . "#{$comment->comment_ID}'>{$post->post_name}</a></td></tr>\n";
	}
	$out .= "</table></div>";
	if( !isset( $_GET[ 'action' ] ) )
		wp_cache_set( 'voyeur_list', $out );
	echo $out;
}

function voyeur_log_cookies() {
	global $wpdb, $current_user;
	if( isset( $_GET[ 'voyeur' ] ) && $_GET[ 'voyeur' ] == 'disable' ) {
		setcookie( 'voyeur_disable_'. COOKIEHASH, '1', time() + 30000000, COOKIEPATH );
		header( 'Location: ' . get_option( 'siteurl' ) );
		exit;
	}
	if( isset( $_COOKIE[ 'voyeur_disable_'. COOKIEHASH ] ) )
		return;

	if( isset($_COOKIE['comment_author_'.COOKIEHASH]) ) {
		sanitize_comment_cookies();
		$comment_status = $wpdb->get_var( "SELECT comment_approved FROM {$wpdb->comments} WHERE comment_author_email = '{$_COOKIE['comment_author_email_'.COOKIEHASH]}' AND comment_approved = '1' LIMIT 0,1" );
		if( 'spam' == $comment_status || !$comment_status )
			return;
		if( $_GET[ 'voyeur' ] == 1 ) {
			$req = 'News Reader';
		} elseif( isset( $_GET[ 'req' ] ) ) {
			$req = $wpdb->escape( urldecode( $_GET[ 'req' ] ) );
		} else {
			$req = $wpdb->escape( $_SERVER[ 'REQUEST_URI' ] );
		}
		if( $_COOKIE['comment_author_email_'.COOKIEHASH] != $current_user->user_email ) {
			$wpdb->query( "INSERT INTO {$wpdb->prefix}voyeur ( `voyeur_date` , `name` , `email` , `url` , `request`, `referrer` ) VALUES ( NOW(), '" . $_COOKIE['comment_author_'.COOKIEHASH] . "', '" . $_COOKIE['comment_author_email_'.COOKIEHASH] . "', '" . $_COOKIE['comment_author_url_'.COOKIEHASH] . "', '$req', '" . $wpdb->escape( $_SERVER[ 'HTTP_REFERER' ] ) . "' )" );
			wp_cache_set( 'voyeur_list', false );
		}
	}
	if( $_GET[ 'voyeur' ] == 1 ) {
		header( 'Content-type: image/gif' );
		readfile( ABSPATH . 'wp-includes/images/smilies/icon_smile.gif' );
		exit;
	}
}
add_action( 'init', 'voyeur_log_cookies' );

function voyeur_comment_form( $post_id ) {
	if( isset($_COOKIE['comment_author_'.COOKIEHASH]) ) {
		?><div id='voyeurcommentform'></div><?php
	}
}
add_action( 'comment_form', 'voyeur_comment_form' );

function voyeur_footer() {
	if( !isset($_COOKIE['comment_author_'.COOKIEHASH]) || isset( $_COOKIE[ 'voyeur_disable_'. COOKIEHASH ] ) )
		return;
	?>
	<script type="text/javascript">
	<!--
	document.getElementById('voyeurcommentform').innerHTML = '<p><img src="<?php echo trailingslashit( get_option( 'siteurl' ) ); ?>wp-includes/images/smilies/icon_smile.gif"> You\'re being watched. <a href="<?php echo trailingslashit( get_option( 'siteurl' ) ); ?>?voyeur=disable">Disable</a>.</p>';
	// -->
	</script>
	<?php
	//voyeur_welcome(); // TODO
}
add_action( 'wp_footer', 'voyeur_footer' );

function voyeur_welcome() {
	global $wpdb;

	if( !isset($_COOKIE['comment_author_'.COOKIEHASH]) || isset( $_COOKIE[ 'voyeur_disable_'. COOKIEHASH ] ) )
		return;

	if( isset($_COOKIE['comment_author_'.COOKIEHASH]) ) {
		sanitize_comment_cookies();
		$comment_author = $_COOKIE['comment_author_'.COOKIEHASH];
		$comment_author_email = $_COOKIE['comment_author_email_'.COOKIEHASH];
		$last_comment = wp_cache_get( 'lastcomment' . md5( $comment_author_email ) );
		if( !$last_comment ) {
			$last_comment = $wpdb->get_row( "SELECT * FROM {$wpdb->comments} WHERE comment_author_email = '$comment_author_email' ORDER BY comment_ID DESC LIMIT 0,1" );
			wp_cache_set( 'lastcomment' . md5( $comment_author_email ), $last_comment );
		}
		$table_name = $wpdb->prefix . 'voyeur';
		$last = $wpdb->get_results( "SELECT * FROM $table_name WHERE email = '$comment_author_email' ORDER BY id DESC LIMIT 0,5" );
		?>document.getElementById('welcomemsg').innerHTML = '<br /><h4>Hi <?php echo $comment_author ?>!</h4><p>Welcome Back!</p><br />';<?php
		if( $last ) {
			?><p>Your last visits:<ol><?php
			foreach( $last as $visit ) {
				?>document.getElementById('welcomemsg').innerHTML += '<li><a href="<?php echo $visit->request ?>"><?php echo $visit->request ?></a></li>';<?php
			}
			?></ol></p><?php
		}
	}
}

function voyeur_feed( $content ) {
	global $post;
	if( !is_feed() )
		return $content;

	return $content . '<p><img src="' . trailingslashit( get_option( 'siteurl' ) ) . '?voyeur=1"></p>';
}
add_filter( 'the_content', 'voyeur_feed' );

?>
