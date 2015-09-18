<?php

// Replaces previous_post_link()
// if $fallback is set to true, previous_post_link() will be called if there is no post found
function previous_post_smart( $format = '&laquo; %link', $title = '%title', $fallback = true, $in_same_term = true, $excluded_terms = '', $taxonomy = 'category' ) {
	return Smarter_Navigation::adjacent_post( $format, $title, true, $fallback, $in_same_term, $excluded_terms, $taxonomy );
}

// Replaces next_post_link()
// if $fallback is set to true, next_post_link() will be called if there is no post found
function next_post_smart( $format = '%link &raquo;', $title = '%title', $fallback = true, $in_same_term = true, $excluded_terms = '', $taxonomy = 'category' ) {
	return Smarter_Navigation::adjacent_post( $format, $title, false, $fallback, $in_same_term, $excluded_terms, $taxonomy );
}

// Returns the previous or next post id in the set
function get_adjacent_id_smart( $previous = false ) {
	return Smarter_Navigation::get_adjacent_id( $previous );
}

// Displays a link to the persistent referrer
function referrer_link( $format = '%link', $title = '%title', $sep = '&raquo;', $sepdirection = 'left' ) {
	echo Smarter_Navigation::referrer_link( $format, $title, $sep, $sepdirection );
}

// Retrieve the term, based on the referrer URL. Useful if you have posts with multiple terms
// $taxonomy defaults to 'category'. Can be changed to custom taxonomy
function get_referrer_term( $taxonomy = 'category' ) {
	global $posts;

	if ( ! $referrer_url = get_referrer_url( false ) )
		return false;

	foreach ( get_the_terms( $posts[0]->ID, $taxonomy ) as $term ) {
		$term_link = get_term_link( $term->term_id, $taxonomy );

		if ( false !== strpos( $referrer_url, $term_link ) )
			return $term;
	}

	return false;
}

// Retrieve the category, based on the referrer URL. Useful if you have posts with multiple categories
// Uses get_referrer_term()
function get_referrer_category() {

	$referrer = get_referrer_term( 'category' );

	if ( is_wp_error( $referrer ) )
		return false;

	return $referrer;
}

// Retrieve the full referrer URL
function get_referrer_url() {
	return Smarter_Navigation::get_referrer_url();
}

