<?php

// Replaces previous_post_link()
// if $fallback is set to true, previous_post_link() will be called if there is no post found
function previous_post_smart($format = '&laquo; %link', $title = '%title', $fallback = true, $in_same_cat = false, $excluded_categories = '') {
	return Smarter_Navigation_Display::adjacent_post($format, $title, true, $fallback, $in_same_cat, $excluded_categories);
}

// Replaces next_post_link()
// if $fallback is set to true, next_post_link() will be called if there is no post found
function next_post_smart($format = '%link &raquo;', $title = '%title', $fallback = true, $in_same_cat = false, $excluded_categories = '') {
	return Smarter_Navigation_Display::adjacent_post($format, $title, false, $fallback, $in_same_cat, $excluded_categories);
}

// Returns the previous or next post id in the set
function get_adjacent_id_smart($previous = false) {
	return Smarter_Navigation_Display::get_adjacent_id($previous);
}

// Displays a link to the persistent referer
function referrer_link($format = '%link', $title = '%title', $sep = '&raquo;', $sepdirection = 'left') {
	return Smarter_Navigation_Display::referrer_link($format, $title, $sep, $sepdirection);
}

function get_referrer_url() {
	return @Smarter_Navigation_Cookie::$data['url'];
}

