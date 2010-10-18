<?php
/*
Plugin Name: SalmonPress
Plugin URI: http://code.google.com/p/salmonpress/
Description: Salmon plugin for WordPress.
Version: 0.0.1
Author: Arne Roomann-Kurrik
Author URI: http://roomanna.com
*/
/**
 * Copyright 2009 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


//error_reporting(E_ALL);
define('SALMONPRESS_VERSION', '0.0.1');
define('SALMONPRESS_LOG_FILE', '/var/log/salmonpress.log');

require_once 'salmon.php';
require_once 'magicsig.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

/**
 * Static class to register callback handlers and otherwise configure
 * WordPress for accepting Salmon posts.
 */
class SalmonPress {
  /**
   * Constructor.
   */
  public function __construct() {
    // Init feeds.
    add_action('atom_head', array('SalmonPress', 'print_feed_link'));
    add_action('rss_head', array('SalmonPress', 'print_feed_link'));
    add_action('rss2_head', array('SalmonPress', 'print_feed_link'));

    // Query handler
    add_action('parse_query', array('SalmonPress', 'parse_query'));
    add_filter('query_vars', array('SalmonPress', 'queryvars'));
    add_action('init', array('SalmonPress', 'flush_rewrite_rules'));
    add_action('generate_rewrite_rules', array('SalmonPress', 'add_rewrite_rules'));

    // xrd discovery
    add_action("host_meta_xrd", array("SalmonPress", "add_xrd_discovery_links"));
    add_action('webfinger_xrd', array('SalmonPress', 'add_xrd_discovery_links'), 10, 1);
    add_action('webfinger_xrd', array('SalmonPress', 'add_xrd_crypt_keys'), 10, 1);
  }

  /**
   * show discovery links
   *
   * @param Object $user default null
   */
  function add_xrd_discovery_links($user = null) {
    $url = SalmonPress::generate_api_url($user);
    echo "<Link rel='salmon' href='$url'/>\n";
    echo "<Link rel='http://salmon-protocol.org/ns/salmon-replies' href='$url' />\n";
    echo "<Link rel='http://salmon-protocol.org/ns/salmon-mention' href='$url' />\n";
  }

  function add_xrd_crypt_keys($user) {
    $sig = MagicSig::get_public_sig($user->ID);
    $encoded = MagicSig::to_string($sig);

    echo '<Link rel="magic-public-key" href="data:application/magic-public-key,'.$encoded.'"/>';

    echo '<Property xmlns:mk="http://salmon-protocol.org/ns/magic-key"
                    type="http://salmon-protocol.org/ns/magic-key"
                    mk:key_id="1">
            '.$encoded.'
          </Property>';
  }

  /**
   *
   * @return array
   */
  function get_users() {
    global $wpdb;

    $sql =  "SELECT * FROM $wpdb->users";
    $users = $wpdb->get_results($wpdb->prepare($sql));

    return $users;
  }

  function generate_api_url($user = null) {
    $url = get_bloginfo('wpurl');
    $url .= "/?salmonpress=true";

    if ($user !== null) {
      $url .= "&amp;author=".$user->ID;
    }

    return $url;
  }

  /**
   * Outputs any passed arguments to the log file configured in
   * SALMONPRESS_LOG_FILE.  Objects are print_r'd into the file.
   * @param mixed ... Any parameters to convert to strings and log.
   */
  public static function debug() {
    $num_args = func_num_args();
    $arg_list = func_get_args();

    for ($i = 0; $i < $num_args; $i++) {
      error_log(print_r($arg_list[$i], true) . "\n", 3, SALMONPRESS_LOG_FILE);
    }
  }

  /**
   * Prints the link pointing to the salmon endpoint to a syndicated feed.
   */
  public static function print_feed_link() {
    echo "<link rel='salmon' href='".SalmonPress::generate_api_url()."'/>";
  }

  /**
   * Checks a query for the 'salmonpress' parameter and attempts to parse a
   * Salmon post if the parameter exists.
   */
  public static function parse_query($wp_query) {
    if (isset($wp_query->query_vars['salmonpress'])) {
      SalmonPress::parse_salmon_post();
    }
  }

  /**
   * Adds the 'salmonpress' query variable to wordpress.
   */
  public static function queryvars($queryvars) {
    $queryvars[] = 'salmonpress';
    return $queryvars;
  }

  /**
   * Clears the cached rewrite rules so that we may add our own.
   */
  public static function flush_rewrite_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }

  /**
   * Adds a rewrite rule so that http://mysite.com/index.php?salmonpress=true
   * can be rewritten as http://mysite.com/salmonpress
   */
  public static function add_rewrite_rules($wp_rewrite) {
    global $wp_rewrite;
    $new_rules = array('salmonpress/?(.+)' => 'index.php?salmonpress=' . $wp_rewrite->preg_index(1),
                       'salmonpress' => 'index.php?salmonpress=true');
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
  }

  /**
   * Attempts to parse data sent to the Salmon endpoint and post it as a
   * comment for the current blog.
   */
  public static function parse_salmon_post() {
    // Allow cross domain JavaScript requests, from salmon-playground.
    if (strtoupper($_SERVER['REQUEST_METHOD']) == "OPTIONS" &&
        strtoupper($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) == "POST") {
      // See https://developer.mozilla.org/En/HTTP_access_control
      header('HTTP/1.1 200 OK');
      header('Access-Control-Allow-Origin: * ');
      die();
    }

    //TODO(kurrik): Check that this always works, even if always_populate_raw_post_data is Off
    $request_body = @file_get_contents('php://input');
    $array = MagicSig::parse($request_body);
    $entry = SalmonEntry::from_atom($array['data']);

    // Validate the request if the option is set.
    if (get_option('salmonpress_validate')) {
      if ($entry->validate() === false) {
        header('HTTP/1.1 403 Forbidden');
        print "The posted Salmon entry's signature did not validate.";
        die();
      }
    }

    $commentdata = $entry->to_commentdata();
    if ($commentdata === false) {
      header('HTTP/1.1 400 Bad Request');
      print "The posted Salmon entry was malformed.";
    } else if (!isset($commentdata['user_id'])) {
      if (get_option('comment_registration')) {
        header('HTTP/1.1 403 Forbidden');
        print "The blog settings only allow registered users to post comments.";
        die();
      }
    } else {
      wp_new_comment($commentdata);
      header('HTTP/1.1 201 Created');
      print "The Salmon entry was posted.";
    }
    die();
  }
}

// Init the plugin
new SalmonPress();