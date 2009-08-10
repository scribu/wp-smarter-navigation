<?php

// Displays a link to the persistent referer
function referrer_link($format = '%link', $title = '%title', $sep = '&raquo;', $sepdirection = 'left') {
	global $persistent_referrer;

	$url = @$persistent_referrer->data['url'];

	if ( !is_single() or empty($url) )
		return false;

	$title = str_replace('%title', smarterNavDisplay::get_title($sep, $sepdirection), $title);
	$link = sprintf("<a href='%s'>%s</a>", $url, $title);
	echo str_replace('%link', $link, $format);
}

// Replaces previous_post_link()
// if $fallback is set to true, previous_post_link() will be called if there is no post found
function previous_post_smart($format = '&laquo; %link', $title = '%title', $fallback = true) {
	smarterNavDisplay::adjacent_post($format, $title, true, $fallback);
}

// Replaces next_post_link()
// if $fallback is set to true, next_post_link() will be called if there is no post found
function next_post_smart($format = '%link &raquo;', $title = '%title', $fallback = true) {
	smarterNavDisplay::adjacent_post($format, $title, false, $fallback);
}

// Returns the previous or next post id in the set
function get_adjacent_id_smart($previous = false) {
	return smarterNavDisplay::get_adjacent_id($previous);
}
