<?php
/*
Plugin Name: rev-canonical
Plugin URI: http://notizblog.org/
Description: rev-canonical
Version: 0.1
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

// register
add_action('wp_head', 'revCanonical');

function revCanonical() {
	if (is_page() || is_single()) {
		echo '<link rev="canonical" type="text/html" href="'.twitter_link().'" />'."\n";
	}
}