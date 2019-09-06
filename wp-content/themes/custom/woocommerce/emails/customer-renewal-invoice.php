<?php
/**
 * Customer renewal invoice email
 *
 * @author  Brent Shepherd
 * @package WooCommerce_Subscriptions/Templates/Emails
 * @version 1.4.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php 
$user = get_user_by('email', $email->recipient); 
$first_name = $user->user_firstname; 
$email_address = $user->user_email; 
$user_id = $user->ID;
?>

<?php if ( 'pending' == $order->get_status() || 'on-hold' == $order->get_status()) : ?>
<p>
	Your membership has expired. You can renew your membership on our website, by logging into your account. 
</p>
<p>
	<?php if($email_address): ?>
		Your account email address is <?php printf($email_address); ?>
		<br>
	<?php endif; ?>
	<?php if($user_login): ?>
		Your username is <?php printf($user_login); ?>
	<?php endif; ?>
</p>
<p>
	<?php
			// translators: %1$s: name of the blog, %2$s: link to checkout payment url, note: no full stop due to url at the end
	echo wp_kses( sprintf( _x( '%2$s', 'In customer renewal invoice email', 'woocommerce-subscriptions' ), esc_html( get_bloginfo( 'name' ) ), '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . esc_html__( 'Click Here to Renew Now.', 'woocommerce-subscriptions' ) . '</a>' ), array( 'a' => array( 'href' => true ) ) );
	?>
</p>
<br>

<?php elseif ( 'failed' == $order->get_status() ) : ?>
	<p>
		<?php
				// translators: %1$s: name of the blog, %2$s: link to checkout payment url, note: no full stop due to url at the end
		echo wp_kses( sprintf( _x( 'The automatic payment to renew your subscription with %1$s has failed. To reactivate the subscription, please login and pay for the renewal from your account page: %2$s', 'In customer renewal invoice email', 'woocommerce-subscriptions' ), esc_html( get_bloginfo( 'name' ) ), '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . esc_html__( 'Pay Now &raquo;', 'woocommerce-subscriptions' ) . '</a>' ), array( 'a' => array( 'href' => true ) ) ); ?>
	</p>
<?php endif; ?>
<?php do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email ); ?>
If you have any questions, please <a href="/contact" target="_blank">contact us.</a>
<p>Thank you.</p>	
<?php do_action( 'woocommerce_email_footer', $email ); ?>
