<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="shop_table woocommerce-checkout-review-order-table">
	<div class="review-order-top">
		<div class="row">
			<div class="product-name col">
				<h4 class="bold">
					<?php _e( 'Product', 'woocommerce' ); ?>
				</h4>
			</div>
			<div class="product-total col-5 righted">
				<h4 class="bold">
					<?php _e( 'Total', 'woocommerce' ); ?>
				</h4>
			</div>
		</div>
	</div>
	<div>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );
		?>

		<?php
		//if( is_member() || has_membership_in_cart() ):
			//$user_eligible_for_discount = true;
		//else:
			//$user_eligible_for_discount = false;
		//endif;
		?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );


			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ){ ?>

				<?php //$discount = get_membership_discount( $product_id ); ?>
				<?php //$product_has_discount = $discount > 0; ?>

				<div class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?> row review-order-row <?php if( $product_has_discount ): echo ' has-discount '; if( $user_eligible_for_discount ): echo ' discounted '; else: echo ' not-discounted '; endif; endif; ?>">
					<div class="col product-name">
						<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
						<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
					</div>
					<div class="product-total col-5 righted">
						<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
					</div>
				</div>

				<?php
			}
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</div>

	<div>

		<div class="row review-order-row  cart-subtotal">
			<div class="col">
				<h4 class="bold">
					<?php _e( 'Subtotal', 'woocommerce' ); ?>
				</h4>
			</div>
			<div class="col righted">
				<h4 class="bold">
					<?php wc_cart_totals_subtotal_html(); ?>
				</h4>
			</div>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
		<div class="row review-order-row rowcart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
			<div class="col">
				<h4 class="bold">
					<?php wc_cart_totals_coupon_label( $coupon ); ?>
				</h4>
			</div>
			<div class="col righted">
				<h4 class="bold">
					<?php wc_cart_totals_coupon_html( $coupon ); ?>
				</h4>
			</div>
		</div>
	<?php endforeach; ?>

	<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

	<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

	<?php wc_cart_totals_shipping_html(); ?>

	<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

<?php endif; ?>

<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
<div class="fee row review-order-row">
	<div class="col">
		<h4 class="bold">
			<?php echo esc_html( $fee->name ); ?>
		</h4>
	</div>
	<div class="col righted">
		<h4 class="bold">
			<?php wc_cart_totals_fee_html( $fee ); ?>
		</h4>
	</div>
</div>
<?php endforeach; ?>

<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
	<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
	<div class=" review-order-row tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
		<div class="col">
			<h4 class="bold">
				<?php echo esc_html( $tax->label ); ?>
			</h4>
		</div>
		<div class="col righted">
			<h4 class="bold">
				<?php echo wp_kses_post( $tax->formatted_amount ); ?>
			</h4>
		</div>
	</div>
<?php endforeach; ?>
<?php else : ?>
	<div class="row review-order-row tax-total">
		<div class="col">
			<h4 class="bold">
				<?php echo esc_html( WC()->countries->tax_or_vat() ); ?>
			</h4>
		</div>
		<div class="col righted">
			<h4 class="bold">
				<?php wc_cart_totals_taxes_total_html(); ?>
			</h4>
		</div>
	</div>
<?php endif; ?>
<?php endif; ?>

<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

<div class="order-total review-order-row row">
	<div class="col">
		<h4 class="bold">
			<?php _e( 'Total', 'woocommerce' ); ?>
		</h4>
	</div>
	<div class="col righted">
		<h4 class="bold review-order-total">
			<?php wc_cart_totals_order_total_html(); ?>
		</h4>
	</div>
</div>
<?php
if( has_membership_in_cart() ):
	$has_recurring_products = true;
else:
	$has_recurring_products = false;
endif;
?>
<?php //temporarily hiding recurring total notes ?>
<?php if ( $has_recurring_products && false ): ?>
	<div class="review-order-row row">
		<div class="col">
			<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
		</div>
	</div>
<?php endif; ?>
</div>
</div>
