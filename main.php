<?php
/*
Plugin Name: Smarter Navigation
Description: Generates more specific previous / next post links based on referrer.
Author: scribu
Version: 1.2.1a
Author URI: http://scribu.net
Plugin URI: http://scribu.net/wordpress/smarter-navigation

Copyright (C) 2010 scribu.net (scribu AT gmail DOT com)

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

Smarter_Navigation_Cookie::init();

class Smarter_Navigation_Cookie {
	const NAME = 'smarter-navigation';
	const SEP = '__SEP__';
	const COUNT = 500;

	static $data = array(
		'ids' => '',
		'url' => '',
		'title' => ''
	);

	// Constructor
	function init() {
		add_action('template_redirect', array(__CLASS__, 'manage_cookie'));
	}

	function manage_cookie() {
		// Default conditions
		$read_cond = is_single();
		$clear_cond = is_home();
		$set_cond = true;

		if ( apply_filters('smarter_nav_read', $read_cond) )
			self::read_cookie();
		elseif ( apply_filters('smarter_nav_clear', $clear_cond) )
			self::clear_cookie();
		elseif ( apply_filters('smarter_nav_set', $set_cond) )
			self::set_cookie();
	}

	private function read_cookie() {
		if ( empty($_COOKIE[self::NAME]) )
			return false;

		self::$data = $_COOKIE[self::NAME];
		self::$data['ids'] = explode(' ', self::$data['ids']);

		self::validate();
	}

	// Checks if the current post is in the data set
	private function validate() {
		global $posts;

		if ( !in_array($posts[0]->ID, self::$data['ids']) )
			self::$data = null;
	}

	private function set_cookie() {
		$data = array(
			'ids' => implode(' ', self::collect_ids()),
			'url' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'title' => trim(wp_title(self::SEP, false, 'left'))
		);

		// Store data in cookies
		$r = array();
		foreach ( $data as $key => $value )
			$r[self::get_name($key)] = setcookie(self::get_name($key), $value, 0, '/');
	}

	private function clear_cookie() {
		if ( empty($_COOKIE[self::NAME]) )
			return;

		foreach ( array_keys($_COOKIE[self::NAME]) as $key )
			setcookie(self::get_name($key), false, 0, '/');
	}

	private function get_name($key) {
		return self::NAME . '[' . $key . ']';
	}

	private function collect_ids() {
		global $wpdb, $wp_query;

		$query = $wp_query->request;

		// replace SELECT
		$query = explode('FROM', $query, 2);
		$query = "SELECT {$wpdb->posts}.ID FROM" . $query[1];

		// replace LIMIT
		// todo: make sure we're replacing the last LIMIT clause
		$query = explode('LIMIT', $query, 2);

		$count = self::COUNT;

		$limit = explode(',', $query[1]);
		$start = (int) $limit[0];
		$finish = (int) $limit[1];

		$new_start = $start - $count/2 + ($finish - $start)/2;
		if ( $new_start < 0 )
			$new_start = 0;

		$new_finish = $new_start + $count;

		$query = $query[0] . "LIMIT $new_start, $new_finish";

		return $wpdb->get_col($query);
	}
}


class Smarter_Navigation_Display {

	static function referrer_link($format = '%link', $title = '%title', $sep = '&raquo;', $sepdirection = 'left') {
		$url = @Smarter_Navigation_Cookie::$data['url'];

		if ( !is_single() or empty($url) )
			return false;

		$title = str_replace('%title', self::get_title($sep, $sepdirection), $title);
		$link = sprintf("<a href='%s'>%s</a>", $url, $title);
		echo str_replace('%link', $link, $format);
	}

	static function get_title($sep, $sepdir) {
		$sep = trim($sep);

		if ( ! $title = @Smarter_Navigation_Cookie::$data['title'] )
			$title = 'Referrer';

		$parts = array_slice(explode(Smarter_Navigation_Cookie::SEP, $title), 1);

		if ( 'right' == $sepdir )
			$parts = array_reverse($parts);

		return implode(" $sep ", $parts);
	}

	static function adjacent_post($format, $title, $previous, $fallback, $in_same_cat, $excluded_categories) {
		if ( !is_single() )
			return false;

		$id = self::get_adjacent_id($previous);

		if ( false === $id )
			return false;

		if ( -1 == $id ) {
			if ( !$fallback )
				return false;

			if ( $previous )
				return previous_post_link($format, $title, $in_same_cat, $excluded_categories);
			else
				return next_post_link($format, $title, $in_same_cat, $excluded_categories);
		}

		$title = str_replace('%title', get_the_title($id), $title);
		$link = sprintf("<a href='%s'>%s</a>", get_permalink($id), $title);
		echo str_replace('%link', $link, $format);
	}

	static function get_adjacent_id($previous = false) {
		global $post;

		if ( ! $ids = @array_reverse(Smarter_Navigation_Cookie::$data['ids']) )
			return -1;	// no data

		$pos = array_search($post->ID, $ids);

		// Get adjacent id
		if ( $previous ) {
			if ( 0 === $pos )
				return false;

			$id = $ids[$pos - 1];
		} else {
			if ( count($ids) - 1 === $pos )
				return false;

			$id = $ids[$pos + 1];
		}

		return $id;
	}
}

include dirname(__FILE__) . '/template-tags.php';

