<?php
/**
 * Single Product Price Input
 * 
 * @author 		Kathy Darling
 * @package 	WC_Name_Your_Price/Templates
 * @version     2.9.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="nyp" <?php echo WC_Name_Your_Price_Helpers::get_data_attributes( $nyp_product, $prefix ); ?> >

	<?php do_action( 'woocommerce_nyp_before_price_input', $nyp_product ); ?>

	<label for="<?php echo 'nyp' . $prefix; ?>">
			<?php printf( _x( '%s ( %s )', 'In case you need to change the order of Name Your Price ( $currency_symbol )', 'wc_name_your_price' ), stripslashes ( get_option( 'woocommerce_nyp_label_text', __( 'Name Your Price', 'wc_name_your_price' ) ) ), get_woocommerce_currency_symbol() ); ?>
	</label>

	<?php echo WC_Name_Your_Price_Helpers::get_price_input( $nyp_product, $prefix ); ?>

	<?php do_action( 'woocommerce_nyp_after_price_input', $nyp_product ); ?>

</div>