=== Plugin Name ===
Contributors: betzster, snomura
Tags: links, icons, favicons, bookmarks, blogroll, images
Requires at least: 2.5
Tested up to: 3.0.1
Stable tag: 2.0.4

Automatically adds favicons to blogroll/bookmark links.

== Description ==

Automatically adds favicons next to links in your blogroll. Also adds favicons to the links admin area.

**Features:**

* Automatically caches favicon files locally.
* Icons in admin area
* Refresh Cache Button
* Adds `class="blogroll-favicon"`
* Add a default favicon if link has no favicon

                   
== Installation ==

1. Upload the folder `blogroll-links-favicons` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Links > Link Favicons and choose Refresh Cache. Click Update. This may take awhile depending on how many links you have!

== Frequently Asked Questions ==

= Are favicons cached locally? =
Yes. The actual images are cached in /blogroll-links-favicons/cache/. This is new in v2.0.

= Some bookmarks aren't showing any icon at all, why? =
Try refreshing the favicons. Go to Links > Link Favicons and choose the refresh cache option. This refreshes all favicons. To refresh a single favicon, open the edit page for that link and click "Update Link." Be careful, as page loads may slow to a crawl during this process which could last up to a minute or more if you've got a large number of links.

= How often is the cache refreshed? =
At this point, refreshes must be done manually. 

== Screenshots ==

1. Admin and Front-End Icons!

== Changelog ==

= 2.0.2 =
Fixes 2.0 release bugs. (no cache folder & refresh problem)

= 2.0 =
Adds true local image caching & admin area icons.

== Changelog ==

= 2.0.4 =
* Fixed "parse_url() expects exactly 1 parameter" bug
* Adds better PHP 4 compatibility

= 2.0.3 =
* Add plugin page 'settings' link.
* Fix author links.

= 2.0.2 =
* Fix refresh button problem

= 2.0.1 =
* Fix cache folder problem

= 2.0 =
* Add true local image caching
* Add icon display in admin area
* Add bundled default icon
* Add single link icon refresh.

= 1.6.3 =
* Remove Mosaic Mode
* Fix default favicon

= 1.6.2 =
* Add Mosaic Mode

= 1.6.1 =
* Fix text-indent bug on ul li a

= 1.6 =
* Change the way favicons are bound to links

= 1.5 =
* Tweak Regex again

= 1.4 =
* Tweak Regex

= 1.3 =
* WP nounce Tweak broke reverted back

= 1.2 =
* WP nounce Tweak

= 1.1 =
* Added screenshot

= 1.0 =
* Code and presentation clean up for 1.0 release

= 0.9.1 =
* Linked fav icon

= 0.9 =
* Updated Settings link

= 0.8 =
* Changed Sub menu location ,now under links

= 0.7 =
* Ability to add a default favicon if missing from site

= 0.6 =
* Force image size to be 16x 16

= 0.5 =
* Don't show blank images

= 0.4 =
* Added wpnonce security check, fixed bug introduced in 0.3

= 0.3 =
* Added support fro hyphens in URLs

= 0.2 =
* Added snoopy class for URL fetching

= 0.1 =
* First version Check In

