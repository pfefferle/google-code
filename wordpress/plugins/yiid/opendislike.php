<?php
/*
 Plugin Name: OpenDislike for WordPress
 Plugin URI: http://www.opendislike.org/
 Description: Adds the OpenDislike button below each post
 Version: 0.1
 Author: Matthias Pfefferle
 Author URI: http://pfefferle.yiid.com/
 */

add_action('the_content', array('OpenDislikeWidget', 'addDislikeButton'), 21);

/**
 * OpenDislike Widget-Class
 */
class OpenDislikeWidget {
  /**
   * adds the dislike button
   *
   * @return string
   */
  function addDislikeButton($content) {
    $widgetCode = '<iframe src="http://widgets.yiid.com/widget/dislike?f=ffffff&bg=64BE4B&url='.urlencode(get_permalink()).'" style="border:none; overflow:hidden; width:100px; height: 25px; clear: both;" frameborder="0"></iframe>';
    return $content . $widgetCode;
  }
}
?>