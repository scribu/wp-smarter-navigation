<?php

// Displays a link to the persistent referer
function referrer_link($format = '%link', $title = '%title', $sep = '&raquo;', $sepdirection = 'left') {
	global $persistent_referrer;

	if ( !is_single() or empty($persistent_referrer->data) )
		return false;

	$url = $persistent_referrer->data['url'];

	$title = str_replace('%title', smarterNavDisplay::get_title($sep, $sepdirection), $title);
	$link = sprintf("<a href='%s'>%s</a>", $url, $title);
	echo str_replace('%link', $link, $format);
}

// Replaces previous_post_link()
function previous_post_smart($format = '&laquo; %link', $title = '%title') {
	smarterNavDisplay::adjacent_post($format, $title, true);
}

// Replaces next_post_link()
function next_post_smart($format = '%link &raquo;', $title = '%title') {
	smarterNavDisplay::adjacent_post($format, $title);
}

// Returns the previous or next post id in the set
function get_adjacent_id_smart($previous = false) {
	return smarterNavDisplay::get_adjacent_id($previous);
}
