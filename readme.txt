=== Smarter Navigation ===
Contributors: scribu
Donate link: http://scribu.net/wordpress
Tags: archive, navigation, next, previous, referrer
Requires at least: 2.8
Tested up to: 3.0
Stable tag: trunk

Generates more specific previous / next post links based on referrer.

== Description ==

**Version 1.2 requires PHP 5.**

The default `previous_post_link()` and `next_post_link()` have an option to restrict adjacent posts to the current category. This plugin takes it one step further:

If you visit an archive page (category, tag, date, author, search etc.) and then visit a single post from that page, the `previous_post_smart()` and `next_post_smart()` will point only to the other posts in that archive page.

This is particularly useful for photoblogs (that’s where I use it).

= Template tags =
You can simply replace `previous_post_link()` with `previous_post_smart()` and keep the first two arguments: $format & $title. 

If there isn’t a previous post in a set, the normal template tag will be called.

The same goes for `next_post_link()`.

There is also a `referrer_link()` template tag which displays a link to the referrer. Args:

* $format = '%link'
* $title = '%title'
* $sep = '&amp;raquo;'
* $seplocation = 'left'

You can also use `get_referrer_category()` to retrieve the category object, based on the referrer url. This is useful when you have posts in multiple categories.

You can learn more about these by looking at the `template-tags.php` file.

== Installation ==

1. Unzip the archive and put the folder into your plugins folder (/wp-content/plugins/).
1. Activate the plugin from the Plugins admin menu.
1. Insert the template tags in your theme as needed.

== Frequently Asked Questions ==

= "Parse error: syntax error, unexpected..." Help! =

Make sure your host is running PHP 5. Add this line to wp-config.php to check:

`var_dump(PHP_VERSION);`

== Changelog ==

= 1.2 =
* moved to PHP5 syntax
* added get_referrer_category() and get_referrer_url() template tags
* added $in_same_cat and $excluded_categories arguments to previous_post_smart() & next_post_smart()
* [more info](http://scribu.net/wordpress/smarter-navigation/sn-1-2.html)

= 1.1.2 =
* added $fallback parameter to *_post_smart()

= 1.1.1 =
* better SQL limit

= 1.1 =
* handles posts split on multiple pages
* better behaviour when multiple tabs open
* [more info](http://scribu.net/wordpress/smarter-navigation/sn-1-1.html)

= 1.0 =
* initial release
* [more info](http://scribu.net/wordpress/smarter-navigation/sn-1-0.html)

