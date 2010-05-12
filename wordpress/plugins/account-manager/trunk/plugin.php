<?php
/*
Plugin Name: Account Manager
Plugin URI: http://notizblog.org/
Description: Mozillas Account Manager for WordPress
Version: 0.1
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/
add_filter('query_vars', array('MozillaAccountManager', 'queryVars'));
add_action('parse_request', array('MozillaAccountManager', 'parseRequest'));
add_action('host_meta_xrd', array('MozillaAccountManager', 'hostMetaXrd'));
add_action('init', array('MozillaAccountManager', 'init'));

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

/**
 *
 * @author Matthias Pfefferle
 */
class MozillaAccountManager {

  /**
   *
   * 
   */
  function init(){
    global $current_user;
    global $user_ID;
    
    get_currentuserinfo();
    
    header('X-Account-Management: '.get_option( 'siteurl' ).'/?account-manager=amcd');
    
    if ('' == $user_ID) {
      header('X-Account-Management-Status: passive;');
    } else {
      header('X-Account-Management-Status: active; name="'.$current_user->display_name.'"');
    }
  }

  function queryVars($vars) {
    $vars[] = 'account-manager';

    return $vars;
  }  

  function parseRequest() {
    global $wp_query, $wp;

    if( array_key_exists('account-manager', $wp->query_vars) ) {
      if ($wp->query_vars['account-manager'] == 'amcd') {
        MozillaAccountManager::printAmcd();
      }
    }
  }

  function printAmcd() {
    $connect = array();
    $connect = array('method' => 'POST',
                                 'path' => get_option( 'siteurl' ).'/wp-login.php',
                                 'params' => array('username' => 'log', 'password' => 'pwd')
                    );
    
    $disconnect = array('method' => 'GET',
                        'path' => wp_logout_url()
                       );
                  
    $amcd = array();
    $amcd['methods'] = array("username-password-form" => array('connect' => $connect,
                                                               'disconnect' => $disconnect,
                                                               'sessionstatus' => $sessionstatus)
                                                              );
    header('Content-Type: application/json; charset=' . get_option('blog_charset'), true);
    echo json_encode($amcd);
    
    exit;
  }

  function hostMetaXrd() {
    echo "<Link rel='http://services.mozilla.com/amcd/0.1' href='".get_option( 'siteurl' )."/?account-manager=amcd' />";
  }
}
?>