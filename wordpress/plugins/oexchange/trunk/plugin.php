<?php
/*
Plugin Name: OExchange
Plugin URI:
Description:
Version: 0.1
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

add_action('parse_request', array('OExchangePlugin', 'parseRequest'));
add_filter('query_vars', array('OExchangePlugin', 'queryVars'));
add_action('host_meta_xrd', array('OExchangePlugin', 'hostMetaXrd'));
add_action('webfinger_xrd', array('OExchangePlugin', 'hostMetaXrd'));

/**
 * OExchange class
 * 
 * @author Matthias Pfefferle
 */
class OExchangePlugin {
  /**
   * add 'oexchange' as a valid query variables.
   *
   * @param array $vars
   * @return array
   */
  function queryVars($vars) {
    $vars[] = 'oexchange';

    return $vars;
  }
  
  function parseRequest() {
    global $wp_query, $wp;

    if( array_key_exists('oexchange', $wp->query_vars) ) {
      if ($wp->query_vars['oexchange'] == 'xrd') {
        $xrd = OExchangePlugin::createXrd();
        //header('Content-Type: application/xrd+xml; charset=' . get_option('blog_charset'), true);
        header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
        echo $xrd;
        exit;
      }
    }
  }
  
  function createXrd() {
    $xrd  = "<?xml version='1.0' encoding='UTF-8'?>";
    $xrd .= '<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">';
    $xrd .= '  <Subject>'.'</Subject>';
    $xrd .= '  <Property
        type="http://www.oexchange.org/spec/0.8/prop/vendor">'.get_option('blogname').'</Property>';
    $xrd .= '  <Property 
        type="http://www.oexchange.org/spec/0.8/prop/title">'.get_option('blogdescription').'</Property>';
    $xrd .= '  <Property 
        type="http://www.oexchange.org/spec/0.8/prop/name">"Press This" bookmarklet</Property>';
    $xrd .= '  <Property 
        type="http://www.oexchange.org/spec/0.8/prop/prompt">Press This</Property>';

    $xrd .= '  <Link 
        rel= "icon" 
        href="'.get_option( 'siteurl' ).'/favicon.ico"
        type="image/vnd.microsoft.icon" 
        />';

    $xrd .= '  <Link 
        rel= "http://www.oexchange.org/spec/0.8/rel/offer" 
        href="'.get_option( 'siteurl' ).'/?oexchange=press-this"
        type="text/html" 
        />';
    $xrd .= '</XRD>';
    
    return $xrd;
  }
  
  function hostMetaXrd() {
    echo '<Link 
            rel="http://oexchange.org/spec/0.8/rel/resident-target" 
            type="application/xrd+xml"
            href="'.get_option( 'siteurl' ).'/?oexchange=xrd" />'."\n";
  }
}
?>