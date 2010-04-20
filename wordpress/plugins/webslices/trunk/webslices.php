<?php
/*
 Plugin Name: Webslices
 Plugin URI: http://notizblog.org/
 Description: Webslices for themes using <code>get_class();</code>
 Version: 0.2
 Author: Matthias Pfefferle
 Author URI: http://notizblog.org/
 */

add_filter('post_class', array('Webslices', 'addSlice'));
add_filter('wp_head', array('Webslices', 'addHeader'));

class Webslices {
  function addSlice($class) {
    if (is_page() || is_single()) {
      $class[] = "hslice";

    }
    
    return $class;
  }

  function addHeader() {
    if (is_page() || is_single()) {
      echo '<link rel="default-slice" type="application/x-hatom" href="'.get_permalink().'#post-'.get_the_ID().'"/>';
    }
  }
}

?>