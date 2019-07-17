<?php

/**
 * WC_Name_Your_Price_Cart class.
 */
class WC_Name_Your_Price_Cart {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Functions for cart actions - ensure they have a priority before addons (10).
		add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 5, 2 );
		add_filter( 'woocommerce_subscription_is_purchasable', array( $this, 'is_purchasable' ), 5, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 5, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 11, 2 );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 11, 1 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_cart_item' ), 5, 6 );

	}

	/*-----------------------------------------------------------------------------------*/
	/* Cart Filters */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Override woo's is_purchasable in cases of nyp products.
	 * @since 1.0
	 */
	public function is_purchasable( $purchasable , $product ) {
		if( ( $product->is_type( WC_Name_Your_Price_Helpers::get_simple_supported_types() ) && WC_Name_Your_Price_Helpers::is_nyp( $product ) ) || ( $product->is_type( WC_Name_Your_Price_Helpers::get_variable_supported_types() ) && WC_Name_Your_Price_Helpers::has_nyp( $product ) ) ) {
			$purchasable = true;
		}
		return $purchasable;
	}

	/**
	 * Add cart session data.
	 * @param array $cart_item_data extra cart item data we want to pass into the item.
	 * @param int   $product_id contains the id of the product to add to the cart.
	 * @param int   $variation_id ID of the variation being added to the cart.
	 * @since 1.0
	 */
	public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

		// An NYP item can either be a product or variation.
		$nyp_id = $variation_id ? $variation_id : $product_id;

		$posted_nyp_field = 'nyp' . apply_filters( 'nyp_field_prefix', '', $nyp_id );

		// No need to check is_nyp b/c this has already been validated by validate_add_cart_item().
		if( isset( $_REQUEST[ $posted_nyp_field ] ) ) {
			$cart_item_data['nyp'] = ( double ) WC_Name_Your_Price_Helpers::standardize_number( $_REQUEST[ $posted_nyp_field ] );
		}

		// Add the subscription billing period (the input name is nyp-period).
		$posted_nyp_period_field = 'nyp-period' . apply_filters( 'nyp_field_prefix', '', $nyp_id );

		if ( WC_Name_Your_Price_Helpers::is_subscription( $nyp_id ) && WC_Name_Your_Price_Helpers::is_billing_period_variable( $nyp_id ) && isset( $_REQUEST[ $posted_nyp_period_field ] ) && array_key_exists( $_REQUEST[ $posted_nyp_period_field ], WC_Name_Your_Price_Helpers::get_subscription_period_strings() ) ) {
			$cart_item_data['nyp_period'] = $_REQUEST[ $posted_nyp_period_field ];
		}

		return $cart_item_data;
	}

	/**
	 * Adjust the product based on cart session data.
	 *
	 * @param  array $cart_item $cart_item['data'] is product object in session
	 * @param  array $values cart item array
	 * @since 1.0
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {

		// No need to check is_nyp b/c this has already been validated by validate_add_cart_item().
		if ( isset( $values['nyp'] ) ) {
			$cart_item['nyp'] = $values['nyp'];

			// Add the subscription billing period.
			if ( WC_Name_Your_Price_Helpers::is_subscription( $cart_item['data'] ) && isset( $values['nyp_period'] ) && array_key_exists( $values['nyp_period'], WC_Name_Your_Price_Helpers::get_subscription_period_strings() ) ){
				$cart_item['nyp_period'] = $values['nyp_period'];
			}

			$cart_item = $this->add_cart_item( $cart_item );
		}

		return $cart_item;
	}

	/**
	 * Change the price of the item in the cart.
	 * @since 1.0
	 */
	public function add_cart_item( $cart_item ) {

		$the_product = $cart_item['data'];

		// Adjust price in cart if nyp is set.
		if ( WC_Name_Your_Price_Helpers::is_nyp( $the_product ) && isset( $cart_item['nyp'] ) ) {

			$the_product->set_price( $cart_item['nyp'] );
			$the_product->set_sale_price( $cart_item['nyp'] );
			$the_product->set_regular_price( $cart_item['nyp'] );

			// Subscription-specific price and variable billing period. 
			if ( $the_product->is_type( array( 'subscription', 'subscription_variation' ) ) ) {
				
				$the_product->update_meta_data( '_subscription_price', $cart_item['nyp'] );

				if ( WC_Name_Your_Price_Helpers::is_billing_period_variable( $the_product ) && isset( $cart_item['nyp_period'] ) ) {
					$the_product->update_meta_data( '_subscription_period', $cart_item['nyp_period'] );
					// Variable billing period is always a "per" interval.
					$the_product->update_meta_data( '_subscription_period_interval', 1 );
				}
			}		

		}
		return $cart_item;
	}

	/**
	 * Check this is a NYP product before adding to cart.
	 * @since 1.0
	 */
	public function validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations= '', $cart_item_data = array() ) {

		// An NYP item can either be a product or variation.
		$nyp_id = $variation_id ? $variation_id : $product_id;

		// Skip if not a NYP product - send original status back.
		if ( ! WC_Name_Your_Price_Helpers::is_nyp( $nyp_id ) ){
			return $passed;
		}

		$prefix = apply_filters( 'nyp_field_prefix', '', $nyp_id );

		// Get the price from the order again params or from the posted value (posted can be null string).
		if( isset( $cart_item_data['nyp'] ) ){
			$input = $cart_item_data['nyp'];
		} else {
			// get_posted_price() runs the price through the standardize_number() helper.
			$input = WC_Name_Your_Price_Helpers::get_posted_price( $nyp_id, $prefix );
		}

		// Null error message.
		$error_message = '';

		// The product title.
		$nyp_product = wc_get_product( $nyp_id );
		$product_title = $nyp_product->get_title();

		// Get minimum price.
		$minimum = WC_Name_Your_Price_Helpers::get_minimum_price( $nyp_product );

		// Get maximum price.
		$maximum = WC_Name_Your_Price_Helpers::get_maximum_price( $nyp_product );

		// Minimum error template.
		$error_template = WC_Name_Your_Price_Helpers::is_minimum_hidden( $nyp_product ) ? 'hide_minimum' : 'minimum';

		// Check that it is a positive numeric value.
		if ( ! is_numeric( $input ) || is_infinite( $input ) || floatval( $input ) < 0 ) {
			$passed = false;
			$error_message = WC_Name_Your_Price_Helpers::error_message( 'invalid', array( '%%TITLE%%' => $product_title ) );
		// Check that it is greater than minimum price for variable billing subscriptions.
		} elseif ( $minimum && WC_Name_Your_Price_Helpers::is_subscription( $nyp_product ) && WC_Name_Your_Price_Helpers::is_billing_period_variable( $nyp_product ) ) {

			// Get the posted billing period, defaults to 'month'.
			$period = WC_Name_Your_Price_Helpers::get_posted_period( $nyp_product, $prefix );

			// Minimum billing period.
			$minimum_period = WC_Name_Your_Price_Helpers::get_minimum_billing_period( $nyp_product );

			// Annual minimum.
			$minimum_annual = WC_Name_Your_Price_Helpers::annualize_price( $minimum, $minimum_period );

			// Annual input.
			$input_annual = WC_Name_Your_Price_Helpers::annualize_price( $input, $period );

			// By standardizing the prices over the course of a year we can safely compare them.
			if ( $input_annual < $minimum_annual ) {
				$passed = false;

				$factors = WC_Name_Your_Price_Helpers::annual_price_factors();

				// If set period is in the $factors array we can calc the min price shown in the error according to entered period.
				if ( isset( $factors[$period] ) ){
					$error_price = $minimum_annual / $factors[$period];
					$error_period = $period;
				// Otherwise, just show the saved minimum price and period.
				} else {
					$error_price = $minimum;
					$error_period = $minimum_period;
				}

				// The minimum is a combo of price and period.
				$minimum_error = wc_price( $error_price ) . ' / ' . $error_period;
				$error_message = WC_Name_Your_Price_Helpers::error_message( $error_template, array( '%%TITLE%%' => $product_title, '%%MINIMUM%%' => $minimum_error ), $nyp_product );

			}
		// Check that it is greater than minimum price.
		} elseif ( $minimum && floatval( $input ) < floatval( $minimum ) ) {
			$passed = false;
			$error_message = WC_Name_Your_Price_Helpers::error_message( $error_template, array( '%%TITLE%%' => $product_title, '%%MINIMUM%%' => wc_price( $minimum ) ), $nyp_product );
		// Check that it is less than maximum price.
		} elseif ( $maximum && floatval( $input ) > floatval( $maximum ) ) {
			$passed = false;
			$error_message = WC_Name_Your_Price_Helpers::error_message( 'maximum', array( '%%TITLE%%' => $product_title, '%%MAXIMUM%%' => wc_price( $maximum ) ), $nyp_product );
		}

		// Show the error message.
		if( $error_message ){
			wc_add_notice( $error_message, 'error' );
		}
		return $passed;
	}

} // End class.
