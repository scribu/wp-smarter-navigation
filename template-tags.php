<?php

// Displays a link to the persistent referer
function referrer_link($format = '%link', $title = '%title', $sep = '&raquo;', $sepdirection = 'left') {
	global $persistent_referrer;

	if ( !is_single() )
		return false;

	// If there's no cookie
	if ( empty($persistent_referrer->data) )
		return false;

	$url = $persistent_referrer->data['url'];

	$title = str_replace('%title', $persistent_referrer->get_title($sep, $sepdirection), $title);
	$link = sprintf("<a href='%s'>%s</a>", $url, $title);
	echo str_replace('%link', $link, $format);
}

// Replaces previous_post_link().
function previous_post_smart($format = '&laquo; %link', $title = '%title') {
	adjacent_post_smart($format, $title, true);
}

// Replaces next_post_link()
function next_post_smart($format = '%link &raquo;', $title = '%title') {
	adjacent_post_smart($format, $title);
}

// Helper function (you should NOT use this one in your theme)
function adjacent_post_smart($format, $title, $previous = false) {
	if ( !is_single() )
		return false;

	$id = get_adjacent_id_smart($previous);

	// If there's no cookie, generate normal nav link
	if ( -1 == $id ) {
		if ( $previous )
			return previous_post_link($format, $title);
		else
			return next_post_link($format, $title);
	}

	// If there is a cookie, but there isn't a link, bail
	if ( false === $id )
		return false;

	$title = str_replace('%title', get_the_title($id), $title);
	$link = sprintf("<a href='%s'>%s</a>", get_permalink($id), $title);
	echo str_replace('%link', $link, $format);
}

function get_adjacent_id_smart($previous = false) {
	global $post, $persistent_referrer;

	if ( ! $ids = @array_reverse($persistent_referrer->data['ids']) )
		return -1;	// no cookie

	$pos = array_search($post->ID, $ids);

	// Get adjacent id
	if ( $previous ) {
		if ( 0 === $pos ) 
			return false;
		else 
			$id = $ids[$pos - 1];
	} else {
		if ( count($ids) - 1 === $pos ) 
			return false;
		else
			$id = $ids[$pos + 1];
	}

	return $id;
}
