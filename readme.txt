=== Smarter Navigation ===
Contributors: scribu
Donate link: http://scribu.net/wordpress
Tags: archive, navigation, next, previous, referrer
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: trunk

Generates more specific previous / next post links based on referrer.

== Description ==

The default `previous_post_link()` and `next_post_link()` have an option to restrict adjacent posts to the current category. This plugin takes one step further:

If you visit an archive page (category, tag, date, author, search etc.) and then visit a single post from that page, the previous_post_smart() and next_post_smart() will point only to the other posts in that archive page.

This is particularly useful for photoblogs (that’s where I use it).

= Template tags =
You can simply replace `previous_post_link()` with `previous_post_smart()` and keep the first two arguments: $format & $title. 

If there isn’t a previous post in a set, the normal template tag will be called.

The same goes for `next_post_link()`.

There is also a `referrer_link()` template tag which also has two arguments that work in the same way: $format & $title.

You can learn more about these by looking at the template-tags.php file.

== Installation ==

1. Unzip the archive and put the folder into your plugins folder (/wp-content/plugins/).
1. Activate the plugin from the Plugins admin menu.
1. Insert the template tags in your theme as needed.
