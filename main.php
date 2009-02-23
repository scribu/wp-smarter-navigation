<?php
/*
Plugin Name: Smarter Navigation
Description: Generates more specific previous / next post links based on referrer.
Author: scribu
Version: 1.0.3
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

class persistent_referrer {
	var $name;
	var $data = array(
		'ids' => '',
		'url' => '',
		'title' => ''
	);

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
		global $posts;

		// Collect ids
		foreach ( $posts as $post )
			$data['ids'][] = $post->ID;
		$data['ids'] = @implode(' ', $data['ids']);

		// Collect URL
		$data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// Collect title
		$data['title'] = trim(wp_title('__SEP__', false, 'left'));

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
	}

	function get_title($sep, $sepdir) {
		$sep = trim($sep);
		$parts = explode('__SEP__', $this->data['title']);
		unset($parts[0]);

		if ( 'right' == $sepdir )
			$parts = array_reverse($parts);

		return implode(" $sep ", $parts);
	}
}

$GLOBALS['persistent_referrer'] = new persistent_referrer('WP_PERSISTENT_referrer');

include_once(dirname(__FILE__) . '/template-tags.php');

