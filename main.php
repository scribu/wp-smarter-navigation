<?php
/*
Plugin Name: Smarter Navigation
Description: Generates more specific previous / next post links based on referrer.
Author: scribu
Version: 1.3
Author URI: http://scribu.net
Plugin URI: http://scribu.net/wordpress/smarter-navigation


Copyright (C) 2010-2011 Cristi Burcă (scribu@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class Smarter_Navigation {
	const NAME = 'smarter-navigation';
	const SEP = '__SEP__';

	static $data;

	function init() {
		add_action( 'template_redirect', array( __CLASS__, 'manage_cookie' ) );
		add_action( 'posts_clauses', array( __CLASS__, 'posts_clauses' ), 10, 2 );
	}

	function manage_cookie() {
		// Default conditions
		$clear_cond = false;
		$read_cond = is_singular();
		$set_cond = !is_404();

		if ( apply_filters( 'smarter_nav_clear', $clear_cond ) )
			self::clear_cookie();
		elseif ( apply_filters( 'smarter_nav_read', $read_cond ) )
			self::read_cookie();
		elseif ( apply_filters( 'smarter_nav_set', $set_cond ) )
			self::set_cookie();
	}

	private function read_cookie() {
		if ( empty( $_COOKIE[self::NAME] ) )
			return false;

		self::$data = $_COOKIE[self::NAME];

		if ( isset( self::$data['query'] ) )
			self::$data['query'] = json_decode( stripslashes( self::$data['query'] ), true );
	}

	public function set_cookie( $data = '' ) {
		$data = wp_parse_args( $data, array(
			'query' => json_encode( $GLOBALS['wp_query']->query ),
			'url' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'title' => trim( wp_title( self::SEP, false, 'left' ) ),
		) );

		foreach ( $data as $key => $value )
			setcookie( self::get_name( $key ), $value, 0, '/' );
	}

	public function clear_cookie() {
		if ( empty( $_COOKIE[self::NAME] ) )
			return;

		foreach ( array_keys( $_COOKIE[self::NAME] ) as $key )
			setcookie( self::get_name( $key ), false, 0, '/' );
	}

	private function get_name( $key ) {
		return self::NAME . '[' . $key . ']';
	}

	static function adjacent_post( $format, $title, $previous, $fallback, $in_same_cat, $excluded_categories ) {
		if ( !is_single() )
			return false;

		$id = self::get_adjacent_id( $previous );

		if ( !$id )
			return false;

		if ( -1 == $id ) {
			if ( !$fallback )
				return false;

			if ( $previous )
				return previous_post_link( $format, $title, $in_same_cat, $excluded_categories );
			else
				return next_post_link( $format, $title, $in_same_cat, $excluded_categories );
		}

		$title = str_replace( '%title', get_the_title( $id ), $title );
		$link = sprintf( "<a href='%s'>%s</a>", get_permalink( $id ), $title );
		echo str_replace( '%link', $link, $format );
	}

	private static $cache;

	/**
	 * @return -1 if there's no data, 0 if no post found, post id otherwise
	 */
	static function get_adjacent_id( $previous = false ) {
		if ( !isset( self::$data['query'] ) )
			return -1;

		$previous = (bool) $previous;

		if ( !isset( self::$cache[$previous] ) ) {
			$args = array_merge( self::$data['query'], array(
				'smarter_navigation' => $previous ? '<' : '>',
				'order' => $previous ? 'DESC' : 'ASC',
				'ignore_sticky_posts' => true,
				'nopaging' => true,
			) );

			$q = new WP_Query( $args );

			self::$cache[$previous] = empty( $q->posts ) ? 0 : $q->posts[0]->ID;
		}

		return self::$cache[$previous];
	}

	static function posts_clauses( $bits, $wp_query ) {
		global $wpdb;

		$direction = $wp_query->get( 'smarter_navigation' );

		if ( !$direction )
			return $bits;

		$orderby = preg_split( '|\s+|', $bits['orderby'] );
		$orderby = reset( $orderby );

		$field = explode( '.', $orderby );
		$field = end( $field );

		$post = get_queried_object();

		if ( isset( $post->$field ) ) {
			$bits['where'] .= $wpdb->prepare( " AND $orderby $direction %s ", $post->$field );
			$bits['limits'] = 'LIMIT 1';
		} else {
			$bits['where'] = ' AND 1 = 0';
		}

		return $bits;
	}

	static function referrer_link( $format = '%link', $title_format = '%title', $sep = '&raquo;', $sepdirection = 'left' ) {
		$url = self::get_referrer_url();

		if ( !is_single() || empty( $url ) )
			return false;

		$title = self::get_title( $sep, $sepdirection );
		if ( empty( $title ) )
			return;

		$title_format = str_replace( '%title', $title, $title_format );
		$link = sprintf( "<a href='%s'>%s</a>", $url, $title_format );
		echo str_replace( '%link', $link, $format );
	}

	static function get_referrer_url() {
		global $wp_rewrite;

		if ( !isset( self::$data['url'] ) || !isset( self::$data['query'] ) )
			return '';

		return self::$data['url'];
	}

	static function get_title( $sep, $sepdir ) {
		$sep = trim( $sep );

		if ( ! $title = @self::$data['title'] )
			$title = 'Referrer';

		$parts = array_slice( explode( self::SEP, $title ), 1 );

		if ( 'right' == $sepdir )
			$parts = array_reverse( $parts );

		return implode( " $sep ", $parts );
	}
}
Smarter_Navigation::init();

include dirname( __FILE__ ) . '/template-tags.php';

