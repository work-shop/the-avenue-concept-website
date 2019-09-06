<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s Customer first name */ ?>
<?php 
$user = get_user_by('login',$user_login); 
$first_name = $user->user_firstname; 
$email_address = $user->user_email; 
?>
<p>
	Hello<?php if( $first_name ): ?><?php printf(' '); printf( $first_name ); ?><?php endif; ?>,
</p>
	<p>Thanks for creating an account on our website.</p>
	<?php if($email_address): ?>
		<p>Your account email address is <?php printf($email_address); ?></p>
	<?php endif; ?>
	<?php if($user_login): ?>
		<p>Your username is <?php printf($user_login); ?></p>
	<?php endif; ?>
	<p><?php printf( __( 'You can access your account area to view orders, change your password, and more at: %3$s', 'woocommerce' ), esc_html( $blogname ), '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); ?></p><?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>
	<?php if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && $password_generated ) : ?>
		<?php /* translators: %s Auto generated password */ ?>
		<p><?php printf( esc_html__( 'Your password has been automatically generated: %s', 'woocommerce' ), '<strong>' . esc_html( $user_pass ) . '</strong>' ); ?></p>
	<?php endif; ?>
	<p>If you have any questions, please <a href="https://theavenueconcept.org/contact" target="_blank">contact us.</a></p>
	<p>Thank you.</p>
	<?php
	do_action( 'woocommerce_email_footer', $email );
