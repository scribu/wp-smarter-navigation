=== Smarter Navigation ===
Contributors: scribu
Tags: navigation, previous, next, referrer
Requires at least: 3.1
Tested up to: 3.3
Stable tag: trunk

Generates more specific previous / next post links based on referrer.

== Description ==

When displaying a single post, you might want to show links to the previous and next posts in the same category.

That's fine; WordPress let's you do this with `previous_post_link()` and `next_post_link()`.

But what if that post is in multiple categories?

What if the user came to that post from a tag page or from an author page? Wouldn't it make more sense to display previous / next posts from that particular set?

Well, you can do this with a similar pair of functions, provided by this plugin: `previous_post_smart()` and `next_post_smart()`.

Here's how it works:

Whenever a visitor goes to an archive page (category, tag, date, author, search etc.), the plugin notes which archive it is in a browser cookie.

Then, if the visitor goes to a single post from that archive page, the plugin generates the prev / next links based on the information in the cookie.

Links: [Plugin News](http://scribu.net/wordpress/smarter-navigation) | [Author's Site](http://scribu.net)

== Installation ==

1. Unzip the archive and put the folder into your plugins folder (/wp-content/plugins/).
1. Activate the plugin from the Plugins admin menu.

= Basic usage =

Go to your theme directoy and open single.php.

Replace 

`previous_post_link(` with `previous_post_smart(` 

and

`next_post_link(` with `next_post_smart(`

= Referrer link =

If you also want to display a link back to the list of posts, add this line (also in single.php):

`<?php referrer_link(); ?>`

= Posts with multiple categories =

If you want for example to [higlight the category](http://wordpress.org/support/topic/366588) that the user came from, you can use `get_referrer_category()` to retrieve the category object.

For further reference, all the template tags are located in [smarter-navigation/template-tags.php](http://plugins.trac.wordpress.org/browser/smarter-navigation/trunk/template-tags.php).

== Frequently Asked Questions ==

= "Parse error: syntax error, unexpected..." Help! =

Make sure your host is running PHP 5. Add this line to wp-config.php to check:

`var_dump(PHP_VERSION);`

== Changelog ==

= 1.3 =
* store query vars in cookie instead of individual post ids
* fix referer link
* [more info](http://scribu.net/wordpress/smarter-navigation/sn-1-3.html)

= 1.2.1 =
* enable $in_same_cat by default

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

