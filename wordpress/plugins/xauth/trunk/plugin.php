<?php
/*
Plugin Name: XAuth
Plugin URI: http://notizblog.org/
Description: Tell other sites that you are a &lt;your-blogs-name&gt; - user
Version: 0.1
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

add_action('login_head', array('XAuthPlugin', 'addExpireJS'));
add_action('parse_request', array('XAuthPlugin', 'parseRequest'));
add_filter('login_redirect', array('XAuthPlugin', 'redirectWrapper'), 10, 3);
add_filter('query_vars', array('XAuthPlugin', 'queryVars'));

/**
 * XAuth plugin
 *
 * @author Matthias Pfefferle
 * @link http://xauth.org/spec/
 */
class XAuthPlugin {

  /**
   * adds the expire header to logout a user by visiting this page
   */
  function addExpireJS() {
    echo '<script type="text/javascript" src="http://xauth.org/xauth.js"></script>'."\n";
    echo '<script type="text/javascript">XAuth.expire();</script>'."\n";
  }

  function parseRequest() {
    global $wp_query, $wp;

    if( array_key_exists('xauth', $wp->query_vars) ) {
      if ($wp->query_vars['xauth'] == 'login') {
        MozillaAccountManager::printAmcd();
      }
    }
  }

  /**
   *
   */
  function queryVars($vars) {
    $vars[] = 'xauth';

    return $vars;
  }

  /**
   *
   *
   */
  function redirectWrapper($redirect_to, $requested_redirect_to, $user) {
    // If they're on the login page, don't do anything
    if ( !isset ( $user->user_login ) ) {
      return $redirect_to;
    } else {
      return get_option( 'siteurl' )."?xauth=login&redirect_to=".urlencode($redirect_to);
    }
  }
}