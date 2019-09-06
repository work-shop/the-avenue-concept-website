<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list(varname) any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();

/**
 * My Account navigation.
 * @since 2.6.0
 */
//turn off native navigation
//do_action( 'woocommerce_account_navigation' ); ?>

<div class="woocommerce-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 * @since 2.6.0
		 */
		//do_action( 'woocommerce_account_content' );
		?>
		<div class="row">
			<div class="col-md-4 mb2 col-xs-6 my-account-link-boxes">
				<a href="/my-account/subscriptions" class="bold display-block centered white bg-brand p1 pt3 pb3">
					Memberships
				</a>
			</div>
			<div class="col-md-4 mb2 col-xs-6 my-account-link-boxes">
				<a href="/my-account/orders" class="bold display-block centered white bg-brand p1 pt3 pb3">
					Review Order History
				</a>
			</div>
			<div class="col-md-4 mb2 col-xs-6 my-account-link-boxes">
				<a href="/my-account/edit-account" class="bold display-block centered white bg-brand p1 pt3 pb3">
					Edit Account Details
				</a>
			</div>
			<div class="col-md-4 mb2 col-xs-6 my-account-link-boxes">
				<a href="/my-account/payment-methods" class="bold display-block centered white bg-brand p1 pt3 pb3">
					Payment Methods
				</a>
			</div>
			<div class="col-md-4 mb2 col-xs-6 my-account-link-boxes">
				<a href="/my-account/edit-account" class="bold display-block centered white bg-brand p1 pt3 pb3">
					Change Password
				</a>
			</div>
			<div class="col-md-4 mb2 col-xs-6 my-account-link-boxes">
				<a href="/contact" class="bold display-block centered white bg-brand p1 pt3 pb3">
					Get Help With My Account
				</a>
			</div>

		</div>
	</div>
