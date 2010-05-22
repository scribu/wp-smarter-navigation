<?php
/*
Plugin Name: Smarter Navigation
Description: Generates more specific previous / next post links based on referrer.
Author: scribu
Version: 1.3-alpha
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

class Smarter_Navigation {
	const NAME = 'smarter-navigation';
	const SEP = '__SEP__';
	const COUNT = 500;

	static $data = array(
		'ids' => '',
		'url' => '',
		'paging' => '',
		'title' => ''
	);

	function init() {
		add_action('template_redirect', array(__CLASS__, 'manage_cookie'));
	}


	function manage_cookie() {
		// Default conditions
		$read_cond = is_single();
		$clear_cond = is_home();
		$set_cond = !is_404() && !is_singular();

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

		if ( !in_array(self::get_current_id(), self::$data['ids']) )
			self::$data = null;
	}

	public function set_cookie($data = '') {
		$defaults = array(
			'ids' => implode(' ', self::collect_ids()),
			'url' => self::get_current_url(),
			'paging' => implode(' ', array(self::get_current_id(), get_query_var('paged'), get_query_var('posts_per_page'))),
			'title' => trim(wp_title(self::SEP, false, 'left')),
		);

		$data = wp_parse_args($data, $defaults);

		foreach ( array_keys($defaults) as $key )
			setcookie(self::get_name($key), $data[$key], 0, '/');
	}

	public function clear_cookie() {
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
		preg_match("/^\s*SELECT\s+.*?\s+FROM/i", $query, $matches);
		$query = preg_replace("/^\s*SELECT\s+.*?\s+FROM/i", "SELECT {$wpdb->posts}.ID FROM", $query);

		// replace LIMIT
		preg_match('/LIMIT\s+(\d+)(,\s+(\d+))?\s*$/', $query, $matches);
		$limit = $matches[0];
		if ( 2 == count($matches) ) {
			$start = 0;
			$finish = $matches[1];
		}
		else {
			$start = $matches[1];
			$finish = $matches[3];
		}

		$count = self::COUNT;

		$new_start = $start - $count/2 + ($finish - $start)/2;
		if ( $new_start < 0 )
			$new_start = 0;

		$new_finish = $new_start + $count;

		$query = str_replace($limit, "LIMIT $new_start, $new_finish", $query);

		return $wpdb->get_col($query);
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

		if ( ! $ids = @array_reverse(self::$data['ids']) )
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


	static function referrer_link($format = '%link', $title = '%title', $sep = '&raquo;', $sepdirection = 'left') {
		$url = self::get_referrer_url();

		if ( !is_single() || empty($url) )
			return false;

		$title = str_replace('%title', self::get_title($sep, $sepdirection), $title);
		$link = sprintf("<a href='%s'>%s</a>", $url, $title);
		echo str_replace('%link', $link, $format);
	}

	static function get_referrer_url() {
		global $wp_rewrite;
	
		$base_url = @self::$data['url'];

		if ( !$base_url )
			return '';

		if ( ! $tmp = @self::$data['paging'] )
			return $base_url;

		$current_id = self::get_current_id();

		list($initial_id, $base_page, $posts_per_page) = explode(' ', $tmp);
		if ( !$base_page )
			$base_page = 1;

		if ( $current_id == $initial_id )
			return $base_url;

		$ids = @self::$data['ids'];

		$i = array_search($initial_id, $ids);
		$c = array_search($current_id, $ids);

		$add = (int) floor(($c-$i) / $posts_per_page);

		if ( !$add )
			return $base_url;

		$new_page = $base_page + $add;

		if ( $wp_rewrite->using_permalinks() ) {
			$base_url = str_replace("/page/$base_page", '', $base_url);
			$adjusted_url = get_pagenum_link($new_page);
			$adjusted_url = str_replace(self::get_current_url(), $base_url, $adjusted_url);
		}
		else {
			if ( $new_page > 1 )
				$adjusted_url = add_query_arg('paged', $new_page, $base_url);
			else
				$adjusted_url = remove_query_arg('paged', $base_url);
		}

		return $adjusted_url;
	}

	static function get_title($sep, $sepdir) {
		$sep = trim($sep);

		if ( ! $title = @self::$data['title'] )
			$title = 'Referrer';

		$parts = array_slice(explode(self::SEP, $title), 1);

		if ( 'right' == $sepdir )
			$parts = array_reverse($parts);

		return implode(" $sep ", $parts);
	}


	private static function get_current_id() {
		return $GLOBALS['posts'][0]->ID;
	}

	private static function get_current_url() {
		$pageURL = ($_SERVER["HTTPS"] == "on") ? 'https://' : 'http://';

		if ( $_SERVER["SERVER_PORT"] != "80" )
			$pageURL .= $_SERVER["SERVER_NAME"]. ":" .$_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		else
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

		return $pageURL;
	}
}
Smarter_Navigation::init();

include dirname(__FILE__) . '/template-tags.php';

