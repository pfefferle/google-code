<?php
/*
Plugin Name: Trackback Helper
Plugin URI: http://notizblog.org/
Description: Some function for a better trackback handling
Version: 0.1
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

add_action('wp_head', array('TrackbackHelper', 'addRdf'));

class TrackbackHelper {
	function addRdf() {
		if (is_single() || is_page()) {
			echo "\n<!--\n";
			trackback_rdf();
			echo "\n-->\n";
		}
	}
}

?>