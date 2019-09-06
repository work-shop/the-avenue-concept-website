<?php 
function has_membership_in_cart(){
  foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
    $product = $cart_item['data'];
    if (has_term('memberships', 'product_cat', $product->id)) {
      return true;
    }

  } 
  return false;
}

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );


function get_membership($user_id = null) {
  if (null == $user_id) {$user_id = get_current_user_id();}
    //if ( 0 == $user_id ) { return false; }

  $flat_products = array();

  $subscriptions = get_posts(array(
    'numberposts' => -1,
    'meta_key' => '_customer_user',
    'meta_value' => $user_id,
    'post_type' => 'shop_subscription',
    'post_status' => 'wc-active',
  ));

  foreach ($subscriptions as $subscription) {

    $subscription = wcs_get_subscription($subscription->ID);
    foreach( $subscription->get_items() as $item ) {

      $flat_products[] = wc_get_product( $item->get_product_id() );

    }

  }

  return $flat_products;

}
?>