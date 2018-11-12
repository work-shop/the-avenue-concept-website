<?php

/** Theme-specific global constants for NAM */
define( '__ROOT__', dirname( __FILE__ ) );

require_once( __ROOT__ . '/functions/class-ws-abstract-taxonomy.php' );
require_once( __ROOT__ . '/functions/class-ws-abstract-custom-post-type.php' );

require_once( __ROOT__ . '/functions/library/class-ws-cdn-url.php');

require_once( __ROOT__ . '/functions/taxonomies/custom-category/class-ws-custom-category.php');

require_once( __ROOT__ . '/functions/post-types/custom-post/class-ws-custom-post.php');

require_once( __ROOT__ . '/functions/class-ws-site-admin.php' );
require_once( __ROOT__ . '/functions/class-ws-site-init.php' );

require_once( __ROOT__ . '/functions/library/class-ws-flexible-content.php' );
require_once( __ROOT__ . '/functions/library/class-helpers.php' );

new WS_Site();
new WS_Site_Admin();

add_filter( 'redirect_canonical', function( $redirect_url ) {

  $url = explode( '?' . $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_URI'] );
  $url = $url[0];
    //var_dump($url);

  if( preg_match('/^.*?\/artworks\/[^\/]+\/?$/m', $url) == 1 ){
        //die;
    return false;
  } else{
    return $redirect_url;
  }
});

    //if this is an artwork single, hijack the template and get it to use the artwork-single.php template
add_filter( 'template_include', function( $template ) {
        //var_dump($template);

  $url = explode( '?' . $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_URI'] );
  $url = $url[0];
    //var_dump($url);

  if( preg_match('/^.*?\/artworks\/[^\/]+\/?$/m', $url) == 1 ){
    $new_template = get_template_directory() . '/artwork-single.php';
    $template = $new_template;
  }

  return $template;
});

// if ( ! function_exists('rid_remove_jqmigrate_console_log') ) {
//     function rid_remove_jqmigrate_console_log( $scripts ) {
//         if ( ! empty( $scripts->registered['jquery'] ) ) {
//             $scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
//         }
//     }
//     add_action( 'wp_default_scripts', 'rid_remove_jqmigrate_console_log' );
// }



function get_artwork_meta_field( $data ) {
  $artworks = get_post_meta( 821, 'zoho', true);

  if ( empty( $artworks ) ) {
    return null;
  }

  return $artworks;
}

function get_paypal( $data ) {

  $mode = 'TEST';
  $endpoint = 'https://pilot-payflowpro.paypal.com';
  $endpoint2 = 'https://pilot-payflowlink.paypal.com';

  $mode = 'LIVE';
  $endpoint = 'https://payflowpro.paypal.com';
  $endpoint2 = 'https://payflowlink.paypal.com';

  $partner = 'PayPal';
  $vendor = 'avenuepvd';
  $user = 'workshop';
  $pwd = 'Cmi!!2012';
  $trxtype = 'S';
  $createsecuretoken = 'Y';
  $securetokenid = uniqid('', true);
  $amt = $_POST['input_8'];
  $firstname = $_POST['input_1_3'];
  $lastname = $_POST['input_1_6'];
  $address1 = $_POST['input_2_1'];                
  $address2 = $_POST['input_2_2'];
  $city = $_POST['input_2_3'];
  $state = $_POST['input_2_4'];
  $zip = $_POST['input_2_5'];
  $email = $_POST['input_6'];
  $phone = $_POST['input_7'];

  $postData = 
  'USER=' . $user .
  '&PARTNER=' . $partner .
  '&VENDOR=' . $vendor .
  '&PWD=' . $pwd .
  '&TRXTYPE=' . $trxtype .
  '&AMT=' . $amt .
  '&CREATESECURETOKEN=' . $createsecuretoken .
  '&SECURETOKENID=' . $securetokenid . 
  '&BILLTOFIRSTNAME=' . $firstname .
  '&BILLTOLASTNAME=' . $lastname .
  '&BILLTOSTREET=' . $address1 .
  '&BILLTOCITY=' . $city .
  '&BILLTOSTATE=' . $state .
  '&BILLTOZIP=' . $zip .
  '&EMAIL=' . $email .
  '&PHONENUM=' . $phone
  ;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

  $resp = curl_exec($ch);

  if( $resp ){
    parse_str($resp, $arr);
  }

  $iframe = '<iframe src="' . $endpoint2 . '?MODE=' . $mode . '&SECURETOKENID=' . $securetokenid . '&SECURETOKEN=' . $arr["SECURETOKEN"] . '&TEMPLATE=MOBILE" name="test_iframe" scrolling="yes" width="570px" height="540px" id="paypal-iframe"></iframe>';

  $response = array(
    'iframe' => $iframe , 
    'amount' => $amt
  );


  return $response;
}


function update_entry( $data ){
  wp_update_post( array(
    'ID' => 917,
    'post_content'   => $_POST['entryId'],
    'post_content'   => $_POST
  ));
  $entry_id = $_POST['entryId'];
  gform_update_meta( $entry_id, '13', 'updated from API' );
  return $_POST;
}


add_action( 'rest_api_init', function () {
  register_rest_route( 'zoho/v1', '/artworks', array(
    'methods' => WP_REST_Server::ALLMETHODS,
    'callback' => 'get_artwork_meta_field',
  ) );
  register_rest_route( 'paypal/v1', '/iframe', array(
    'methods' => WP_REST_Server::ALLMETHODS,
    'callback' => 'get_paypal',
  ) );
  register_rest_route( 'paypal/v1', '/silent', array(
    'methods' => WP_REST_Server::ALLMETHODS,
    'callback' => 'update_entry',
  ) );
} );




add_filter( 'parse_query', 'ts_hide_pages_in_wp_admin' );
function ts_hide_pages_in_wp_admin($query) {
  global $pagenow,$post_type;
  if (is_admin() && $pagenow=='edit.php' && $post_type =='page') {
    $query->query_vars['post__not_in'] = array('821');
  }
}

  /**
 * Use * for origin
 */
  add_action( 'rest_api_init', function() {

    remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
    add_filter( 'rest_pre_serve_request', function( $value ) {
      header( 'Access-Control-Allow-Origin: *' );
      return $value;

    });
  }, 1 );

  function grant_gforms_editor_access() {

    $role = get_role( 'editor' );
    $role->add_cap( 'gform_full_access' );
  }
  // Tie into the 'after_switch_theme' hook
  add_action( 'init', 'grant_gforms_editor_access' );

  add_filter( 'gform_confirmation_anchor', '__return_false' );

  add_filter( 'gform_confirmation_3', 'custom_confirmation_message', 10, 4 );
  function custom_confirmation_message( $confirmation, $form, $entry, $ajax ) {
    //$confirmation = $entry['id'];
    $entryId =  $entry['id'];
    $confirmation = '<script>var entryId = ' . $entryId . '</script>';
    return $confirmation;
  }

  ?>
