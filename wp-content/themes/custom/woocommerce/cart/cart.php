<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_cart' );

?>

<?php if(false): ?>
<?php if( !is_user_logged_in() && has_membership_in_cart() ): ?>
<div class="notice woocommerce-error notice-membership-double-check">
	<h4 class="error ">You're about to purchase a <span class=" bold">new</span> membership. Would you like to renew your membership instead? &nbsp; <a href="/renew-your-membership" class="button button-simple">Renew Membership</a></h4>
</div>
<?php endif; ?>
<?php if(false): ?>
	<?php if( is_user_logged_in() && is_member() && has_membership_in_cart() ): ?>
	<div class="notice woocommerce-error notice-membership-double-check">
		<h4 class="bold">You're about to purchase a *new* membership. Would you like to renew your membership instead? &nbsp; <a href="/my-account/subscriptions" class="modal-toggle button button-brand" data-modal-target="modal-login-ajax">Renew Membership</a></h4>
	</div>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<?php if( wcs_cart_contains_renewal() ){ ?>
	
<?php } ?>

<?php if( wcs_cart_contains_early_renewal() ){ ?>

<?php } ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">


	<div class="woocommerce-cart-form__contents">

		<?php do_action( 'woocommerce_before_cart_table' ); ?>

		<div class="row cart-headings">
			<div class="col-4 col-md-6">
				<h4 class="bold cart-heading">Product</h4>
			</div>
			<div class="col">
				<h4 class="bold cart-heading">Price</h4>
			</div>
			<div class="col">
				<h4 class="bold cart-heading">Quantity</h4>
			</div>
			<div class="col">
				<h4 class="bold cart-heading righted">Total</h4>
			</div>
			<div class="col-1 product-remove-heading">
			</div>
		</div>

		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php // This flag lets you know whether a user is eligible for a discount at all. ?>
		<?php //$user_elible_for_discount = is_member() || has_membership_in_cart(); ?>
		<?php
			//if( is_member() || has_membership_in_cart() ):
				//$user_eligible_for_discount = true;
		//else:
			//$user_eligible_for_discount = false;
		//endif;
		?>

		<?php 
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ):
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key ); ?>
		<?php // these are definitions of the discount quantity, in dollars, applicable to this line item, and whether there's a discount. ?>

		<?php //if ( $cart_item['variation_id'] !== 0 ) : ?>
		<?php //$discount = get_membership_discount( $cart_item['variation_id'] ); ?>
		<?php //else: ?>
		<?php //$discount = get_membership_discount( $product_id ); ?>
		<?php //endif; ?>
		<?php //$product_has_discount = $discount > 0; ?>
		<?php //var_dump( $cart_item ); ?>

		<div class="row cart-row woocommerce-cart-form__cart-item <?php if( $product_has_discount ): echo ' has-discount '; if( $user_eligible_for_discount ): echo ' discounted '; else: echo ' not-discounted '; endif; endif; ?> <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" data-discount="<?php echo $discount; ?>">
			<div class="product-name col-4 col-md-6" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">

				<?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>

				<?php
					//if ( ! $product_permalink ) {
				echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;';
					//} else {
						//echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key );
					//}
					// Meta data.
				echo wc_get_formatted_cart_item_data( $cart_item );
					// Backorder notification.
				if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
					echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>';
				}
				?>
			</div>
			<div class="product-price col" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
				<?php
				echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				?>
			</div>
			<div class="product-quantity col" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>"><?php
			if ( $_product->is_sold_individually() ) {
				$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
			} else {
				$product_quantity = woocommerce_quantity_input( array(
					'input_name'    => "cart[{$cart_item_key}][qty]",
					'input_value'   => $cart_item['quantity'],
					'max_value'     => $_product->get_max_purchase_quantity(),
					'min_value'     => '0',
					'product_name'  => $_product->get_name(),
				), $_product, false );
			}
			echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
			?>
		</div>
		<div class="product-subtotal col righted" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
			<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
		</div>
		<div class="col-1 product-remove">
			<?php
					// @codingStandardsIgnoreLine
			echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
				'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
				esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
				__( 'Remove this item', 'woocommerce' ),
				esc_attr( $product_id ),
				esc_attr( $_product->get_sku() )
			), $cart_item_key );
			?>
		</div>
	</div>
<?php endif; ?>
<?php endforeach; ?>

<?php do_action( 'woocommerce_cart_contents' ); ?>

<?php if ( wc_coupons_enabled() ) { ?>
	<div class="row cart-discount cart-row coupon bg-light">
		<div class="col-md-6">
			<h4 class="bold">
				Have a Discount Code?
			</h4>
		</div>
		<div class="d-flex col-md-6 justify-content-end">
			<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Discount Code', 'woocommerce' ); ?>" />
			<input type="submit" class="button button-small ml3 coupon-button" name="apply_coupon" value="<?php esc_attr_e( 'Apply Discount', 'woocommerce' ); ?>" />
		</div>
		<?php do_action( 'woocommerce_cart_coupon' ); ?>
	</div>
<?php } ?>
<div class="row cart-update">
	<div class="col-md-12 d-flex justify-content-end">
		<button type="submit" class="button" id="update-cart-button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>">
			<?php esc_html_e( 'Update cart', 'woocommerce' ); ?>
		</button>
	</div>
</div>

<?php do_action( 'woocommerce_cart_actions' ); ?>

<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce'  ); ?>
<?php do_action( 'woocommerce_after_cart_contents' ); ?>

</div>
<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>


<div class="cart-collaterals">
	<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
		?>
	</div>

	<?php do_action( 'woocommerce_after_cart' ); ?>
