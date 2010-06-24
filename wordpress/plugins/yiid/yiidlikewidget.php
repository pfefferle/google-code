<?php
/*
 Plugin Name: yiid.it for WordPress
 Plugin URI: http://www.yiid.it/
 Description: Adds the yiid.it like/dislike button below each post
 Version: 0.1
 Author: Matthias Pfefferle
 Author URI: http://pfefferle.yiid.com/
 */

add_action('the_content', array('YiidItWidget', 'addLikeButton'), 21);

/**
 * YiidItWidget Widget-Class
 */
class YiidItWidget {
  /**
   * adds the like/dislike button
   *
   * @return string
   */
  function addLikeButton($content) {
    $widgetCode = '<iframe scrolling="no" frameborder="0" marginwidth="0" marginheight="0" style="overflow: hidden; width: 400px; height: 30px;" src="http://widgets.yiid.com/w/like/full.php?lang=de&type=like&url='.urlencode(get_permalink()).'" allowtransparency="true"></iframe>';

    if (is_home() || is_page()) {
      return $content . $widgetCode;
    } else {
      return $content;
    }
  }
}
?>