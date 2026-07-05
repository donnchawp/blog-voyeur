=== Blog Voyeur ===
Contributors: donncha
Tags: privacy,user,stats,log
Tested up to: 2.7.1
Stable tag: 0.3
Donate link: http://ocaoimh.ie/wordpress-plugins/gifts-and-donations/

Log by name where and when users visit your blog.

**Warning:** This plugin dates from 2008 and no longer works. It identified people reading your posts in a feed reader by embedding a hidden tracking image (a "web bug") in the RSS feed; when that image loaded, the reader's WordPress comment cookie was sent back to identify them. Modern feed readers block remote images by default, and browsers now block cookies sent from such cross-site (third-party) contexts, so the technique no longer functions. It is also untested with recent versions of WordPress and PHP.

== Description ==
Use the cookie left after someone leaves a comment to identify their future visits to your blog.

== Changelog ==

### 0.3 - 2026-07-05
* Added a notice that this plugin is unmaintained and no longer works: it relied on a tracking image in the RSS feed plus a cross-site cookie, both of which modern feed readers and browsers now block.

### 0.2
* Existing WordPress.org release (imported to GitHub).
