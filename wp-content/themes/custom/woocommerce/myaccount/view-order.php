<?php
/**
 * View Order
 *
 * Shows the details of a particular order on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
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
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="notice woocommerce-message">
	<p><?php
	/* translators: 1: order number 2: order date 3: order status */
	printf(
		__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce' ),
		'' . $order->get_order_number() . '',
		'' . wc_format_datetime( $order->get_date_created() ) . '',
		'' . wc_get_order_status_name( $order->get_status() ) . ''
	);
	?></p>
</div>

<?php 
//echo '<pre>' , var_dump($order) , '</pre>';
//echo $order->get_date_paid();
//$date_paid = $order->get_date_paid();
//$date=date_create("2013-03-15");
//echo date_format($date_paid,"n/j/Y");
//var_dump($order); ?>

<?php if ( $notes = $order->get_customer_order_notes() ) : ?>
	<div class="row">
		<div class="col-12 col-md-6 view-order-order-updates">
			<h3 class="bold"><?php _e( 'Order updates', 'woocommerce' ); ?></h3>
			<ol class="woocommerce-OrderUpdates commentlist notes">
				<?php foreach ( $notes as $note ) : ?>
					<li class="woocommerce-OrderUpdate comment note">
						<div class="woocommerce-OrderUpdate-inner comment_container">
							<div class="woocommerce-OrderUpdate-text comment-text">
								<p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n( __( 'l F jS Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); ?></p>
								<div class="woocommerce-OrderUpdate-description description">
									<?php echo wpautop( wptexturize( $note->comment_content ) ); ?>
								</div>
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>
	<?php do_action( 'woocommerce_view_order', $order_id ); ?>
</div>