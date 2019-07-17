<?php

/**
 * WC_Name_Your_Price_Helpers class.
 */
class WC_Name_Your_Price_Helpers {

	/**
	 * Supported product types.
	 * The nyp product type is how the ajax add to cart functionality is disabled in old version of WC.
	 *
	 * @var array
	 */
	private static $simple_supported_types = array( 'simple', 'subscription', 'bundle', 'composite', 'variation', 'subscription_variation', 'deposit', 'mix-and-match' );

	/**
	 * Supported variable product types.
	 *
	 * @var array
	 */
	private static $variable_supported_types = array( 'variable', 'variable-subscription' );

	/**
	 * Get supported "simple" types.
	 * 
	 * @return	array
	 * @access	public
	 * @since	2.7.0
	 */
	public static function get_simple_supported_types() {
		return apply_filters( 'wc_nyp_simple_supported_types', self::$simple_supported_types );
	}

	/**
	 * Get supported "variable" types.
	 * 
	 * @return	array
	 * @access	public
	 * @since	2.7.0
	 */
	public static function get_variable_supported_types() {
		return apply_filters( 'wc_nyp_variable_supported_types', self::$variable_supported_types );
	}

	/**
	 * Verify this is a Name Your Price product.
	 *
	 * @param 	mixed int|obj $product
	 * @return 	return boolean
	 * @access 	public
	 * @since 	1.0
	 */
	public static function is_nyp( $product ){

		$product = self::maybe_get_product_instance( $product );

		if ( ! $product ){
			return FALSE;
		}
		
		$is_nyp = $product && $product->is_type( self::get_simple_supported_types() ) && $product->get_meta( '_nyp' ) == 'yes' ? true : false;

		return apply_filters( 'woocommerce_is_nyp', $is_nyp, $product->get_id(), $product );

	}


	/**
	 * Get the suggested price.
	 *
	 * @param 	mixed obj|int $product
	 * @return 	return number or FALSE
	 * @access 	public
	 * @since 2.0
	 */
	public static function get_suggested_price( $product ) {

		$product = self::maybe_get_product_instance( $product );

		if ( ! $product ){
			return FALSE;
		}

		$suggested = $product->get_meta( '_suggested_price', true, 'edit' ); 

		// Filter the raw suggested price @since 1.2.
		return apply_filters( 'woocommerce_raw_suggested_price', $suggested, $product->get_id() );

	}


	/**
	 * Get the minimum price.
	 *
	 * @param 	mixed obj|int $product
	 * @return 	return string
	 * @access 	public
	 * @since 	2.0
	 */
	public static function get_minimum_price( $product ){
	
		$product = self::maybe_get_product_instance( $product );

		if ( ! $product ){
			return FALSE;
		}

		$minimum = $product->get_meta( '_min_price', true, 'edit' );

		// Filter the raw minimum price @since 1.2.
		return apply_filters( 'woocommerce_raw_minimum_price', $minimum, $product->get_id() );

	}

	/**
	 * Get the maximum price.
	 *
	 * @param 	mixed obj|int $product
	 * @return 	return string
	 * @access 	public
	 * @since 	2.8.0
	 */
	public static function get_maximum_price( $product ){
	
		$product = self::maybe_get_product_instance( $product );

		if ( ! $product ){
			return FALSE;
		}

		$maximum = $product->get_meta( '_maximum_price', true, 'edit' );

		// Filter the raw maximum price @since 2.8.0.
		return apply_filters( 'woocommerce_raw_maximum_price', $maximum, $product->get_id() );

	}

	/**
	 * Get the minimum price for a variable product.
	 *
	 * @param   mixed obj|int $product
	 * @return 	return string
	 * @access 	public
	 * @since 	2.3
	 */
	public static function get_minimum_variation_price( $product ){

		$product = self::maybe_get_product_instance( $product );

		if ( ! $product ){
			return FALSE;
		}

		$minimum = $product->get_variation_price( 'min' );

		// Filter the raw minimum price @since 1.2.
		return apply_filters( 'woocommerce_raw_minimum_variation_price', $minimum, $product->get_id() );

	}

	/**
	 * Check if Subscriptions plugin is installed and this is a subscription product.
	 *
	 * @param   mixed obj|int $product
	 * @access 	public
	 * @return 	return boolean returns true for subscription, variable-subscription and subscription_variation
	 * @since 	2.0
	 */
	public static function is_subscription( $product ){

		return class_exists( 'WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product );

	}


	/**
	 * Is the billing period variable.
	 *
	 * @param   mixed obj|int $product
	 * @return 	return string
	 * @access 	public
	 * @since 	2.0
	 */
	public static function is_billing_period_variable( $product ) {

		$product = self::maybe_get_product_instance( $product );

		if ( ! $product ){
			return false;
		}

		$variable = $product->is_type( 'subscription' ) && $product->get_meta( '_variable_billing' ) == 'yes' ? true : false;

		return apply_filters ( 'woocommerce_is_billing_period_variable', $variable, $product->get_id() );
	}


	/**
	 * Get the Suggested Billing Period for subscription.
	 *
	 * @param   mixed obj|int $product.
	 * @return 	return string
	 * @access 	public
	 * @since 	2.0
	 */
	public static function get_suggested_billing_period( $product ) {

		$product = self::maybe_get_product_instance( $product );

		// Set month as the default billing period.
		if ( ! ( $period = $product->get_meta( '_suggested_billing_period' ) ) ){
		 	$period = 'month';
		}

		// Filter the raw minimum price @since 1.2.
		return apply_filters( 'woocommerce_suggested_billing_period', $period, $product->get_id() );

	}


	/**
	 * Get the Minimum Billing Period for subscriptsion
	 *
	 * @param   mixed obj|int $product
	 * @return 	return string
	 * @access 	public
	 * @since 	2.0
	 */
	public static function get_minimum_billing_period( $product ) {

		$product = self::maybe_get_product_instance( $product );

		// Set month as the default billing period.
		if ( ! ( $period = $product->get_meta( '_minimum_billing_period' ) ) ){
		 	$period = 'month';
		}

		// Filter the raw minimum price @since 1.2.
		return apply_filters( 'woocommerce_minimum_billing_period', $period, $product->get_id() );

	}


	/**
	 * Determine if variable has NYP variations.
	 *
	 * @param   mixed obj|int $product
	 * @return 	return string
	 * @access 	public
	 * @since 	2.0
	 */
	public static function has_nyp( $product ) {

		$product = self::maybe_get_product_instance( $product );

		if ( ! $product ){
			return FALSE;
		}

		$has_nyp = $product->is_type( self::get_variable_supported_types() ) && $product->get_meta( '_has_nyp', true, 'edit' ) == 'yes' ? true : false;

		return apply_filters( 'woocommerce_has_nyp_variations', $has_nyp, $product );

	}

	/**
	 * Are we obscuring/hiding the minimum price.
	 *
	 * @param 	mixed int|obj $product
	 * @return 	return boolean
	 * @access 	public
	 * @since 	2.8.0
	 */
	public static function is_minimum_hidden( $product ){

		$product = self::maybe_get_product_instance( $product );

		if ( ! $product ){
			return FALSE;
		}
		
		$is_hidden = $product && $product->get_meta( '_hide_nyp_minimum' ) == 'yes' ? true : false;

		return apply_filters( 'woocommerce_is_minimum_hidden', $is_hidden, $product->get_id(), $product );

	}

	/**
	 * Standardize number.
	 *
	 * Switch the configured decimal and thousands separators to PHP default
	 *
	 * @return 	return string
	 * @access 	public
	 * @since 	1.2.2
	 */
	public static function standardize_number( $value ){

		$value = trim( str_replace( wc_get_price_thousand_separator(), '', stripslashes( $value ) ) );

		return wc_format_decimal( $value );

	}


	/**
	 * Annualize Subscription Price.
	 * convert price to "per year" so that prices with different billing periods can be compared
	 *
	 * @return 	string
	 * @access 	public
	 * @since 	2.0
	 */
	public static function annualize_price( $price = false, $period = null ){

		$factors = self::annual_price_factors();

		if( isset( $factors[$period] ) && $price ) {
			$price = $factors[$period] * self::standardize_number( $price );
		}

		return wc_format_decimal( $price );

	}


	/**
	 * Annualize Subscription Price.
	 * convert price to "per year" so that prices with different billing periods can be compared
	 *
	 * @return 	array
	 * @access 	public
	 * @since 	2.0
	 */
	public static function annual_price_factors(){

		return array_map( 'esc_attr', apply_filters( 'woocommerce_nyp_annual_factors' ,
							array ( 'day' => 365,
										'week' => 52,
										'month' => 12,
										'year' => 1 ) ) );

	}


	/**
	 * Get the "Minimum Price: $10" minimum string.
	 *
	 * @param   mixed obj|int $product
	 * @return 	$price string
	 * @access 	public
	 * @since 	2.0
	 */
	public static function get_minimum_price_html( $product ) {

		$product = self::maybe_get_product_instance( $product );

		// Start the price string.
		$html = '';

		// If not nyp quit early.
		if ( ! self::is_nyp( $product ) ){
			return $html;
		}

		// Get the minimum price.
		$minimum = self::get_minimum_price( $product ); 

		if( $minimum > 0 && ! self::is_minimum_hidden( $product ) ){

			// Get the minimum: text option.
			$minimum_text = stripslashes( get_option( 'woocommerce_nyp_minimum_text', __( 'Minimum Price:', 'wc_name_your_price' ) ) );

			// Formulate a price string.
			$price_string = self::get_price_string( $product, 'minimum' );

			$html .= sprintf( '<span class="minimum-text">%s</span> <span class="amount">%s</span>', $minimum_text, $price_string );


		} 

		return apply_filters( 'woocommerce_nyp_minimum_price_html', $html, $product );

	}


	/**
	 * Get the "Suggested Price: $10" price string.
	 *
	 * @param   mixed obj|int $product
	 * @return 	string
	 * @access 	public
	 * @since 	2.0
	 */
	public static function get_suggested_price_html( $product ) {

		$product = self::maybe_get_product_instance( $product );

		// Start the price string.
		$html = '';

		// If not nyp quit early.
		if ( ! self::is_nyp( $product ) ){
			return $html;
		}

		// Get suggested price.
		$suggested = self::get_suggested_price( $product ); 

		if ( $suggested > 0 ) {

			// Get the suggested: text option.
			$suggested_text = stripslashes( get_option( 'woocommerce_nyp_suggested_text', __( 'Suggested Price:', 'wc_name_your_price' ) ) );

			// Formulate a price string.
			$price_string = self::get_price_string( $product );

			// Put it all together.
			$html .= sprintf( '<span class="suggested-text">%s</span> %s', $suggested_text, $price_string );

		} 

		return apply_filters( 'woocommerce_nyp_suggested_price_html', $html, $product );

	}


	/**
	 * Format a price string.
	 *
	 * @since 	2.0
	 * @param	mixed obj|int $product
	 * @param	string $type ( minimum or suggested )
	 * @param 	bool $show_null_as_zero in the admin you may wish to have a null string display as $0.00
	 * @return	string
	 * @access	public
	 * @since	2.0
	 */
	public static function get_price_string( $product, $type = 'suggested', $show_null_as_zero = false ) {

		// Start the price string.
		$html = '';

		$product = self::maybe_get_product_instance( $product );

		// Minimum or suggested price.
		switch( $type ){
			case 'minimum-variation':
				$price = self::get_minimum_variation_price( $product );
				break;
			case 'minimum':
				$price = self::get_minimum_price( $product );
				break;
			default:
				$price = self::get_suggested_price( $product );
				break;
		}

		if( $show_null_as_zero || $price != '' ){

			// Set the billing period to either suggested or minimum.
			if( self::is_subscription( $product ) && self::is_billing_period_variable( $product ) ) {
				// Minimum or suggested period.
				$period = 'minimum' == $type ? self::get_minimum_billing_period( $product ) : self::get_suggested_billing_period( $product );

				$product->update_meta_data( '_subscription_period', $period );
			}

			// Get subscription price string. 
			// If you filter woocommerce_get_price_html you end up doubling the period $99 / month / week.
			// As Subs add the string after the woocommerce_get_price_html filter has run.
			if( self::is_subscription( $product ) && 'woocommerce_get_price_html' != current_filter() ) { 

				$include = array( 
					'price' => wc_price( $price ),
					'subscription_length' => false,
					'sign_up_fee'         => false,
					'trial_length'        => false );
				
				$html = WC_Subscriptions_Product::get_price_string( $product, $include );

			// Non-subscription products.
			} else {
				$html = wc_price( $price );
			}

		}

		return apply_filters( 'woocommerce_nyp_price_string', $html, $product, $price );

	}


	/**
	 * Get Price Value Attribute.
	 * 
	 * @param	mixed obj|int $product
	 * @return	string
	 * @access	public
	 * @since	2.1
	 */
	public static function get_price_value_attr( $product, $prefix = false ) {

		$product = self::maybe_get_product_instance( $product );

		if ( ( $posted = self::get_posted_price( $product, $prefix ) ) != '' ) {
			$price = $posted;
		} else {
			$price = self::get_initial_price( $product );
		}

		return $price;
	}


	/**
	 * Get Posted Price.
	 * 
	 * @param 	mixed obj|int $product
	 * @param	string $prefix - needed for composites and bundles
	 * @return	string
	 * @access	public
	 * @since	2.0
	 */
	public static function get_posted_price( $product = false, $prefix = false ) {

		// The $product is now useless, so we can deprecate that in the future?
		return isset( $_REQUEST['nyp' . $prefix] ) ? self::standardize_number( $_REQUEST['nyp' . $prefix] ) : '';
	}


	/**
	 * Get Initial Price - Suggested, then minimum, then null.
	 * 
	 * @param	mixed obj|int $product
	 * @return	string
	 * @access	public
	 * @since	2.1
	 */
	public static function get_initial_price( $product ) {

		$product = self::maybe_get_product_instance( $product );

		if ( ( $suggested = self::get_suggested_price( $product ) ) != '' ) {
			$price = $suggested;
		} elseif ( ! self::is_minimum_hidden( $product ) && ( $minimum = self::get_minimum_price( $product ) ) != '' ) {
			$price =  $minimum;
		} else {
			$price = '';
		}

		return apply_filters( 'woocommerce_nyp_get_initial_price', $price, $product );
	}

	/**
	 * Get Period Value Attribute.
	 * 
	 * @param	mixed int|object $product
	 * @return	string
	 * @access	public
	 * @since	2.7.0
	 */
	public static function get_period_value_attr( $product, $prefix = false ) {

		$product = self::maybe_get_product_instance( $product );

		if ( ( $posted = self::get_posted_period( $product, $prefix ) ) != '' ) {
			$price = $posted;
		} else {
			$price = self::get_initial_period( $product );
		}

		return $price;
	}

	/**
	 * Get Posted Billing Period.
	 * 
	 * @param	string $product - not needed
	 * @param	string $prefix - needed for composites and bundles
	 * @return	string
	 * @access	public
	 * @since	2.0
	 */
	public static function get_posted_period( $product = false, $prefix = false ) {

		// The $product is now useless, so we can deprecate that in the future?
		return isset( $_REQUEST['nyp-period' . $prefix] ) && array_key_exists( $_REQUEST['nyp-period' . $prefix], self::get_subscription_period_strings() ) ? $_REQUEST['nyp-period' . $prefix] : '';
	}

	/**
	 * Get Initial Billing Period.
	 * 
	 * @param	mixed obj|int $product
	 * @param	string $prefix - needed for composites and bundles
	 * @return	string
	 * @access	public
	 * @since	2.7.0
	 */
	public static function get_initial_period( $product ) {

		$product = self::maybe_get_product_instance( $product );

		// Go through a few options to find the $period we should display.
		if ( $suggested_period = self::get_suggested_billing_period( $product ) ) {
			$period = $suggested_period;
		} elseif ( $minimum_period = self::get_minimum_billing_period( $product ) ) {
			$period = $minimum_period;
		} else {
			$period = 'month';
		}
		return $period;
	}

	/**
	 * Generate markup for NYP Price input.
	 * Returns a text input with formatted value.
	 * 
	 * @param	mixed obj|int $product
	 * @param	string $prefix - needed for composites and bundles
	 * @return	string
	 * @access	public
	 * @since	2.0
	 */
	public static function get_price_input( $product, $prefix = false ) {

		$product = self::maybe_get_product_instance( $product );

		$price = self::get_price_value_attr( $product, $prefix );

		$return = sprintf( '<input id="nyp%s" name="nyp%s" type="text" value="%s" title="nyp" class="input-text amount nyp-input text" />', esc_attr( $prefix ), esc_attr( $prefix ), esc_attr( self::format_price( $price ) ) );

		return apply_filters( 'woocommerce_get_price_input', $return, $product->get_id(), $prefix );

	}

	/**
	 * Generate Markup for Subscription Period Input.
	 * 
	 * @param	string $input
	 * @param	mixed obj|int $product
	 * @param	string $prefix - needed for composites and bundles
	 * @return	string
	 * @access	public
	 * @since	2.0
	 */
	public static function get_subscription_period_input( $input, $product, $prefix ) {

		// Get product object.
		$product = self::maybe_get_product_instance( $product );

		// Create the dropdown select element.
		$period = self::get_period_value_attr( $product, $prefix );

		// The pre-selected value.
		$selected = $period ? $period : 'month';

		// Get list of available periods from Subscriptions plugin
		$periods = self::get_subscription_period_strings();

		if( $periods ) :

			$period_input = sprintf( '<span class="per">/ </span><select id="nyp-period%s" name="nyp-period%s" class="nyp-period">', $prefix, $prefix );

			foreach ( $periods as $i => $period ) :
				$period_input .= sprintf( '<option value="%s" %s>%s</option>', $i, selected( $i, $selected, false ), $period );
			endforeach;

			$period_input .= '</select>';

			$period_input = '<span class="nyp-billing-period"> ' . $period_input . '</span>';

			$input .= apply_filters( 'wc_nyp_subscription_period_input', $period_input, $product, $prefix );

		endif;

    	return $input;

	}

	/**
	 * Format price with local decimal point.
	 * Similar to wc_price(). 
	 * 
	 * @param	string $price
	 * @return	string
	 * @access	public
	 * @since	2.1
	 */
	public static function format_price( $price ){ 

		$decimals    = wc_get_price_decimals();
		$decimal_separator     = wc_get_price_decimal_separator();
		$thousand_separator   = wc_get_price_thousand_separator();

		if( $price != "" ) {

			$price           = apply_filters( 'raw_woocommerce_price', floatval( $price ) );
			$price           = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

			if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $decimals > 0 ) {
				$price = wc_trim_zeros( $price );
			}
			
		}

		return $price;
	}


	/**
	 * Generate Markup for Subscription Periods.
	 * 
	 * @param	string $input
	 * @param	mixed obj|int $product
	 * @return	string
	 * @access	public
	 * @since	2.0
	 */
	public static function get_subscription_terms( $input = '', $product ) {

		$terms = '&nbsp;';

		// Get product object.
		$product = self::maybe_get_product_instance( $product );

		// Parent variable subscriptions don't have a billing period, so we get a array to string notice. therefore only apply to simple subs and sub variations.
		if( $product->is_type( 'subscription' ) || $product->is_type( 'subscription_variation' ) ) {

			if( self::is_billing_period_variable( $product ) ) {
				// Don't display the subscription price, period or length.
				$include = array(
					'price' => '',
					'subscription_price'  => false,
					'subscription_period' => false
				);

			} else {
				$include = array( 
					'price' => '', 
					'subscription_price'  => false 
				);
				// If we don't show the price we don't get the "per" backslash so add it back.
				if( WC_Subscriptions_Product::get_interval( $product ) == 1 ){
					$terms .= '<span class="per">/ </span>';
				}
			}

			$terms .= WC_Subscriptions_Product::get_price_string( $product, $include );

		} 	

		// Piece it all together - JS needs a span with this class to change terms on variation found event.
		// Use details class to mimic Subscriptions plugin, leave terms class for backcompat.
		if( 'woocommerce_get_price_input' == current_filter() ){
			$terms = '<span class="subscription-details subscription-terms">' . $terms . '</span>';
		}

		return $input . $terms;

	}


	/**
	 * Get data attributes for use in name-your-price.js
	 *
	 * @param	mixed obj|int $product
	 * @param	string $prefix - needed for composites and bundles
	 * @return	string
	 * @access	public
	 * @since	2.0
	 */
	public static function get_data_attributes( $product, $prefix  = null ) {

		// Get product object.
		$product = self::maybe_get_product_instance( $product );

		$price = (double) self::get_price_value_attr( $product, $prefix );
		$minimum = self::get_minimum_price( $product ); 

		$attributes = array( 
			'price' => $price,
			'minimum-error' => self::error_message( 'minimum_js' ),
			'hide-minimum' => self::is_minimum_hidden( $product ),
			'hide-minimum-error' => self::error_message( 'hide_minimum_js' ),
			'max-price' => self::get_maximum_price( $product ),
			'maximum-error' => self::error_message( 'maximum_js' ),
		);
	
		if( self::is_subscription( $product ) && self::is_billing_period_variable( $product ) ){

				$period = self::get_period_value_attr( $product, $prefix );
				$minimum_period = self::get_minimum_billing_period( $product );
				$annualized_minimum = self::annualize_price( $minimum, $minimum_period );

				$attributes['period'] = esc_attr( $period ) ? esc_attr( $period ) : 'month';
				$attributes['annual-minimum'] = $annualized_minimum > 0  ? (double) $annualized_minimum : 0;

		} else {

			$attributes['min-price'] = $minimum && $minimum > 0 ? (double) $minimum : 0;

		}

		

		$data_string = '';

		foreach( $attributes as $key => $attribute ) {
			$data_string .= sprintf( 'data-%s="%s" ', esc_attr( $key ), esc_attr( $attribute ) );
		}

		return $data_string;

	}


	/**
	 * The error message template.
	 *
	 * @param 	string $id selects which message to use
	 * @return 	return string
	 * @access 	public
	 * @since 	2.1
	 */
	public static function get_error_message_template( $id = null ){

		$errors = apply_filters( 'woocommerce_nyp_error_message_templates', 
			array( 
				'invalid' => __( '&quot;%%TITLE%%&quot; could not be added to the cart. Please enter a valid, positive number.', 'wc_name_your_price' ), 
				'minimum' => __( '&quot;%%TITLE%%&quot; could not be added to the cart. Please enter at least %%MINIMUM%%.', 'wc_name_your_price' ),
				'hide_minimum' => __( '&quot;%%TITLE%%&quot; could not be added to the cart. Please enter a higher amount.', 'wc_name_your_price' ),
				'minimum_js' => __( 'Please enter at least %%MINIMUM%%.', 'wc_name_your_price' ),
				'hide_minimum_js' => __( 'Please enter a higher amount.', 'wc_name_your_price' ),
				'maximum' => __( '&quot;%%TITLE%%&quot; could not be added to the cart. Please enter less than or equal to %MAXIMUM%%.', 'wc_name_your_price' ),
				'maximum_js' => __( 'Please enter less than or equal to %%MAXIMUM%%.', 'wc_name_your_price' )
			) 
		);

		return isset( $errors[$id] ) ? $errors[ $id ] : '';

	}


	/**
	 * Get error message.
	 *
	 * @param 	string $id - the error template to use
	 * @param 	array $tags - array of tags and their respective replacement values
	 * @param 	obj $product - the relevant product object
	 * @return 	return string
	 * @access 	public
	 * @since 	2.1
	 */
	public static function error_message( $id, $tags = array(), $product = null ){

		$message = self::get_error_message_template( $id );

		foreach( $tags as $tag => $value ){
			$message = str_replace( $tag, $value, $message );
		}
				
		return apply_filters( 'woocommerce_nyp_error_message', $message, $id, $tags, $product );

	}


	/**
	 * Return an i18n'ified associative array of all possible subscription periods.
	 * Ready for Subs 2.0 but with backcompat.
	 *
	 * @since 2.2.8
	 */
	public static function get_subscription_period_strings( $number = 1, $period = '' ) {
		if( function_exists( 'wcs_get_subscription_period_strings' ) ) {
			$strings = wcs_get_subscription_period_strings( $number, $period );
		} else {
			$strings = WC_Subscriptions_Manager::get_subscription_period_strings( $number, $period );
		}
		return apply_filters( 'wc_nyp_subscription_strings', $strings, $number, $period );
	}

	/**
	 * Wrapper to check whether we have a product ID or product and if we have the former, return the later.
	 * @props Prospress!
	 *
	 * @param mixed $product A WC_Product object or product ID
	 * @return WC_Product
	 * @since 2.2.0
	 */
	public static function maybe_get_product_instance( $product ) {

		if ( ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		return $product;
	}

} //end class