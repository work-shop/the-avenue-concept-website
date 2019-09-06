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
require_once( __ROOT__ . '/functions/ecommerce-helpers.php' );

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
  //return $_GET;
  wp_update_post( array(
    'ID' => 917,
    'post_title'   => $_GET['RESPMSG'],
    'post_content'   => $_POST
  ));
  $entry_id = $_POST['USER1'];
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
    if ( ! current_user_can( 'administrator' ) ) {
      $query->query_vars['post__not_in'] = array('821');
    }
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





function add_donation_purpose_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
  $donation_purpose_value = filter_input( INPUT_POST, 'donation-purpose' );

  if ( empty( $donation_purpose_value ) ) {
    return $cart_item_data;
  }

  $cart_item_data['donation-purpose'] = $donation_purpose_value;

  return $cart_item_data;
}

add_filter( 'woocommerce_add_cart_item_data', 'add_donation_purpose_to_cart_item', 10, 3 );



function display_donation_purpose_in_cart( $item_data, $cart_item ) {
  if ( empty( $cart_item['donation-purpose'] ) ) {
    //die;
    return $item_data;
  } 

  $item_data[] = array(
    'key'     => __( 'Donation Purpose', 'tac' ),
    'value'   => wc_clean( $cart_item['donation-purpose'] ),
    'display' => '',
  );

  return $item_data;
}

add_filter( 'woocommerce_get_item_data', 'display_donation_purpose_in_cart', 10, 2 );


function add_donation_purpose_to_order_items( $item, $cart_item_key, $values, $order ) {
  if ( empty( $values['donation-purpose'] ) ) {
    return;
  }

  $item->add_meta_data( __( 'Donation Purpose', 'tac' ), $values['donation-purpose'] );
}

add_action( 'woocommerce_checkout_create_order_line_item', 'add_donation_purpose_to_order_items', 10, 4 );


//rename order status
add_filter( 'wc_order_statuses', 'rename_order_statuses', 20, 1 );
function rename_order_statuses( $order_statuses ) {
  $order_statuses['wc-processing'] = _x( 'Paid', 'Order status', 'woocommerce' );
  return $order_statuses;
}

add_filter( 'bulk_actions-edit-shop_order', 'custom_dropdown_bulk_actions_shop_order', 20, 1 );
function custom_dropdown_bulk_actions_shop_order( $actions ) {
  $actions['mark_processing'] = __( 'Mark Paid', 'woocommerce' );
  return $actions;
}

foreach( array( 'post', 'shop_order' ) as $hook )
  add_filter( "views_edit-$hook", 'shop_order_modified_views' );

function shop_order_modified_views( $views ){

  if( isset( $views['wc-processing'] ) )
    $views['wc-processing'] = str_replace( 'Processing', __( 'Paid', 'woocommerce'), $views['wc-processing'] );

  return $views;
}


/**
 * Function adds a BCC header to emails that match our array
 *
 * @param string $headers The default headers being used
 * @param string $object  The email type/object that is being processed
 */
function add_bcc_to_certain_emails( $headers, $object ) {
  // email types/objects to add bcc to
  $add_bcc_to = array(
    'customer_renewal_invoice'    // Renewal invoice from WooCommerce Subscriptions
  );
  // if our email object is in our array
  if ( in_array( $object, $add_bcc_to ) ) {
    // change our headers
    $headers = array(
      $headers,
      'Bcc: info+tac-orders@workshop.co' ."\r\n",
    );
  }
  return $headers;
}
add_filter( 'woocommerce_email_headers', 'add_bcc_to_certain_emails', 10, 2 );



