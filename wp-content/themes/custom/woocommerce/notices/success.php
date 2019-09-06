<?php
/**
 * Show messages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/notices/success.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://github.com/work-shop/newport-art-museum-website
 * @author 		work-shop
 * @package 	work-shop/newport-art-museum-website
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $messages ) {
	return;
}

?>

<?php foreach ( $messages as $message ) : ?>
	<div class="woocommerce-message woocommerce-success notice row" role="alert">
		<div class="notice-text col-10">
			<h4><?php echo wp_kses_post( $message ); ?></h4>
		</div>
		<div class="notice-close justify-content-end col-2">
			<a href="#" class="notice-close-link">
				<span class="icon" data-icon="’"></span>
			</a>
		</div>
	</div>
<?php endforeach; ?>
