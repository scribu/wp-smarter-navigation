<?php
/*
Plugin Name: Smarter Navigation
Description: Generates more specific previous / next post links based on referrer.
Author: scribu
Version: 1.1.1
Author URI: http://scribu.net
Plugin URI: http://scribu.net/wordpress/smarter-navigation

Copyright (C) 2009 scribu.net (scribu AT gmail DOT com)

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

$GLOBALS['persistent_referrer'] = new persistent_referrer('wp-persistent-referrer');

class persistent_referrer {
	var $name;
	var $data = array(
		'ids' => '',
		'url' => '',
		'title' => ''
	);
	var $sep = '__SEP__';

	// Constructor
	function persistent_referrer($name) {
		$this->name = $name;

		// Fire as soon as posts have been retrieved
		add_action('wp', array($this, 'manage_cookie'));
	}

	function manage_cookie() {
		// Default conditions
		$read_cond = is_single();
		$set_cond = is_archive() || is_search();
		$clear_cond = true;

		if ( apply_filters('smarter_nav_read', $read_cond) )
			$this->read_cookie();
		elseif ( apply_filters('smarter_nav_set', $set_cond) )
			$this->set_cookie();
		elseif ( apply_filters('smarter_nav_clear', $clear_cond) )
			$this->clear_cookie();
	}

	function clear_cookie() {
		if ( empty($_COOKIE[$this->name]) )
			return false;

		foreach ( array_keys($this->data) as $key )
			setcookie($this->name."[$key]", false, time() - 3600, '/');
	}

	function set_cookie() {
		// Collect ids
		$data['ids'] = implode(' ', $this->_collect_ids());

		// Collect URL
		$data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// Collect title
		$data['title'] = trim(wp_title($this->sep, false, 'left'));

		if ( empty($data['title']) )
			$data['title'] = 'Referrer';

		// Store data in cookies
		foreach ( $data as $key => $value )
			setcookie($this->name."[$key]", $value, 0 , '/');
	}

	function read_cookie() {
		if ( empty($_COOKIE[$this->name]) )
			return false;

		$this->data = $_COOKIE[$this->name];
		$this->data['ids'] = explode(' ', $this->data['ids']);

		$this->validate();		
	}

	// Checks if the current post is in the data set
	function validate() {
		global $posts;

		if ( !in_array($posts[0]->ID, $this->data['ids']) )
			unset($this->data);
//			$this->clear_cookie();	// cookie might still be useful
	}

	function _collect_ids() {
		global $wpdb, $wp_query;

		$query = $wp_query->request;

		// replace SELECT
		$query = explode('FROM', $query, 2);
		$query = "SELECT {$wpdb->posts}.ID FROM" . $query[1];

		// replace LIMIT
		// todo: make sure we're replacing the last LIMIT clause
		$query = explode('LIMIT', $query, 2);

		$count = 500;

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


class smarterNavDisplay {
	function get_title($sep, $sepdir) {
		global $persistent_referrer;

		$sep = trim($sep);
		$parts = explode($persistent_referrer->sep, $persistent_referrer->data['title']);
		unset($parts[0]);

		if ( 'right' == $sepdir )
			$parts = array_reverse($parts);

		return implode(" $sep ", $parts);
	}

	function adjacent_post($format, $title, $previous = false) {
		if ( !is_single() )
			return false;

		$id = smarterNavDisplay::get_adjacent_id($previous);

		// If there's no data, generate normal nav link
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

	function get_adjacent_id($previous = false) {
		global $post, $persistent_referrer;

		if ( ! $ids = @array_reverse($persistent_referrer->data['ids']) )
			return -1;	// no data

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
}

include_once(dirname(__FILE__) . '/template-tags.php');

