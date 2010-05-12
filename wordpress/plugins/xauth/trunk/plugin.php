<?php
/*
Plugin Name: XAuth
Plugin URI: http://wordpress.org/extend/plugins/xauth/
Description: Tell other sites that you are a &lt;your-blogs-name&gt; - user through XAuth
Version: 0.13
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
    echo '<script type="text/javascript">';
    echo '  window.onload = function() {';
    echo '    XAuth.expire();';
    echo '  }';
    echo '</script>'."\n";
  }

  /**
   * parse the request to show the XAuth loginPage()
   *
   * @see loginPage()
   */
  function parseRequest() {
    global $wp_query, $wp;

    if( array_key_exists('xauth', $wp->query_vars) ) {
      if ($wp->query_vars['xauth'] == 'login') {
        $redirect_to = $wp->query_vars['redirect_to'];

        XAuthPlugin::loginPage($redirect_to);
      }
    }
  }

  /**
   * adds "xauth" and "redirect_to" as query-var
   *
   * @param array $vars
   * @return array
   */
  function queryVars($vars) {
    $vars[] = 'xauth';
    $vars[] = 'redirect_to';

    return $vars;
  }

  /**
   * redirects the user. this runs after loginPage()
   *
   * @see loginPage()
   * @param string $redirect_to
   * @param string $requested_redirect_to
   * @param ???? $user
   * @return string
   */
  function redirectWrapper($redirect_to, $requested_redirect_to, $user) {
    // If they're on the login page, don't do anything
    if ( !isset ( $user->user_login ) ) {
      return $redirect_to;
    } else {
      return get_option( 'siteurl' )."?xauth=login&redirect_to=".urlencode($redirect_to);
    }
  }

  /**
   * an intermediate step to run the XAuth extender
   */
  function loginPage($redirect_to) {
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Google XAuth Demo</title>
    <script type="text/javascript" src="http://xauth.org/xauth.js"></script>
    <script type="text/javascript">
      function doLogin(doneUrl) {
        XAuth.extend({
          token: "<?php echo get_option( 'siteurl' ); ?>",
          expire: new Date().getTime() + 60*60*24*1000,
          extend: ["*"],
          callback: makeRedirectFunc(doneUrl)
        });
      }

      function makeRedirectFunc(doneUrl) {
        return function() {
          if (doneUrl) {
            location.replace(doneUrl);
          }
        }
      }
    </script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php
      wp_admin_css('install', true);
      do_action('admin_head');
    ?>
  </head>
  <body onload="doLogin('<?php echo $redirect_to; ?>')">
    <p>tell XAuth.org that you are online...</p>
    <form id="login_redirect_form" action="<?php echo $redirect_to; ?>" method="get">
      <input type="submit" value="Continue" /></div>
    </form>
  </body>
</html>
<?php
  exit;
  }
}