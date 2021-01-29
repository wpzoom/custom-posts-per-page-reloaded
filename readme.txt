=== Custom Posts Per Page Reloaded ===

Contributors: WPZOOM, jeremyfelt
Donate link: https://www.wpzoom.com/
Tags: admin, administration, settings, archives, posts-per-page, paged, posts, count, number, custom-post-type
Requires at least: 4.3
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Custom Posts Per Page Reloaded provides a settings page in your WordPress admin that allows you to specify how many posts are displayed for different views.

== Description ==

Custom Posts Per Page Reloaded allows you to specify how many posts are displayed per page depending on your current view. Once settings are changed, the *Blog pages show at most* setting in the *Reading* menu will be ignored.

Settings are available for:

* Home (Index) Page
    * As long as view is set to blog posts, not static page.
* Category Pages
* Tag Pages
* Author Pages
* Archive Pages
* Search Pages
* Default Page (*Any page not covered above.*)
* Custom Post Type archive pages
    * All Custom Post Types are detected automatically.

Each of the above settings can have a different value for the first page displayed **and** subsequent paged views.

Custom Posts Per Page makes it easy to manage exactly how your content is displayed to your readers, especially when different views have different layouts, rather than relying on the single setting in the Reading menu or having to hard code options in your custom theme.

== Installation ==

1. Upload 'custom-posts-per-page-count.php' to your plugin directory, usually 'wp-content/plugins/', or install automatically via your WordPress admin page.
1. Active Custom Posts Per Page in your plugin menu.
1. Configure using the Posts Per Page menu under Settings in your admin page. (*See Screenshot*)

That's it! The current setting for *Blog pages show at most* under *Reading* will be used to fill in the default values. You can take over from there.

This is a fork (an updated clone) of [Custom Posts Per Page](https://wordpress.org/plugins/custom-posts-per-page/) by [Jeremy Felt](https://jeremyfelt.com/).

== Frequently Asked Questions ==
= What are you doing with found_posts? =

* An issue was appearing in plugins that assisted with pagination when the setting for posts per page was different from subsequent pages. To resolve this issue, we do some math and return an incorrect found_posts value when that scenario occurs. This doesn't affect any core WordPress functionality, but could confuse other plugins that are looking for a correct value. I wouldn't worry about this much, but keep it in mind if you are seeing issues *and* have two different values entered as described.

== Screenshots ==

1. An overview of the Custom Posts Per Page settings screen.

== Changelog ==

= 1.0.0 =
* Initial release.