# Blog Voyeur

A small WordPress plugin that logs by name where and when known users visit your blog, using the cookie left after someone leaves a comment. Published on WordPress.org: <https://wordpress.org/plugins/blog-voyeur/>.

> **⚠️ Warning:** This plugin dates from 2008 and no longer works. It identified people reading your posts in a feed reader by embedding a hidden tracking image (a "web bug") in the RSS feed; when that image loaded, the reader's WordPress comment cookie was sent back to identify them. Modern feed readers block remote images by default, and browsers now block cookies sent from such cross-site (third-party) contexts, so the technique no longer functions. It is also untested with recent versions of WordPress and PHP.

## Releasing

Two-phase, CI-deployed flow:

1. **`make release VERSION=x.y.z`** (run from `trunk`) bumps the version in `voyeur.php` and `readme.txt`, assembles the changelog from the GitHub milestone `x.y.z`, and opens a PR with the changelog editable in the body.
2. Merging that PR runs `.github/workflows/create-release.yml`, which writes the changelog into `readme.txt`, tags, builds `build/blog-voyeur.zip` (from `git archive`), creates the GitHub release, and deploys to WordPress.org SVN.

Before a release: create a GitHub milestone named exactly `x.y.z` and assign the release's PRs to it, and add the `WORDPRESSORG_SVN_USERNAME` / `WORDPRESSORG_SVN_PASSWORD` repository secrets. WordPress.org release confirmation is enabled, so the merged release is held pending an email confirmation before going live.

`make build` packages the plugin from the tracked files at HEAD; `release.config.json` holds the per-repo specifics.
