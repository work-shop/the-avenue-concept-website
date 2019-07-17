<?php
/**
 * Name Your Price Admin Class
 *
 * Adds a name your price setting tab and saves name your price meta data.
 *
 * @package		WooCommerce Name Your Price
 * @subpackage	WC_NYP_Meta_Box_Product_Data
 * @category	Class
 * @author		Kathy Darling
 * @since		1.0
 */
class WC_NYP_Meta_Box_Product_Data {

	/**
	 * Deprecated 2.7.0, use WC_Name_Your_Price_Helpers::get_simple_supported_types()
	 */
	static $simple_supported_types = array( 'simple', 'subscription', 'bundle', 'composite', 'deposit', 'mix-and-match' );

	/**
	 * Bootstraps the class and hooks required actions & filters.
	 *
	 * @since 1.0
	 */
	public static function init() {

		// Product Meta boxes.
		add_filter( 'product_type_options', array( __CLASS__, 'product_type_options' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'add_to_metabox' ) );
		add_action( 'woocommerce_name_your_price_options_pricing', array( __CLASS__, 'add_variable_billing_input' ), 10, 2 );
		add_action( 'woocommerce_name_your_price_options_pricing', array( __CLASS__, 'add_suggested_price_inputs' ), 20, 2 );
		add_action( 'woocommerce_name_your_price_options_pricing', array( __CLASS__, 'add_minimum_price_inputs' ), 30, 2 );
		add_action( 'woocommerce_name_your_price_options_pricing', array( __CLASS__, 'add_maximum_price_inputs' ), 40, 2 );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_meta' ) );

		// Variable Product.
		add_action( 'woocommerce_variation_options', array( __CLASS__, 'product_variations_options' ), 10, 3 );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'add_to_variations_metabox'), 10, 3 );
		add_action( 'woocommerce_name_your_price_options_variation_pricing', array( __CLASS__, 'add_suggested_price_inputs' ), 20, 3 );
		add_action( 'woocommerce_name_your_price_options_variation_pricing', array( __CLASS__, 'add_minimum_price_inputs' ), 30, 3 );
		add_action( 'woocommerce_name_your_price_options_variation_pricing', array( __CLASS__, 'add_maximum_price_inputs' ), 40, 3 );

		// Save NYP variations.
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_product_variation' ), 30, 2 );

		// Variable Bulk Edit.
		add_action( 'woocommerce_variable_product_bulk_edit_actions', array( __CLASS__, 'bulk_edit_actions' ) );

		// Handle bulk edits to data in WC 2.4+.
		add_action( 'woocommerce_bulk_edit_variations', array( __CLASS__, 'bulk_edit_variations' ), 10, 4 );

	}


    /*-----------------------------------------------------------------------------------*/
	/* Write Panel / metabox */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Add checkbox to product data metabox title
	 *
	 * @param array $options
	 * @return array
	 * @since 1.0
	 */
	public static function product_type_options( $options ){

		$options['nyp'] = array(
			'id' => '_nyp',
			'wrapper_class' => "show_if_" . implode( " show_if_", WC_Name_Your_Price_Helpers::get_simple_supported_types() ),
			'label' => __( 'Name Your Price', 'wc_name_your_price'),
			'description' => __( 'Customers are allowed to determine their own price.', 'wc_name_your_price'),
			'default' => 'no'
		);

		return $options;

	}

	/**
	 * Metabox display callback.
	 *
	 * @return print HTML
	 * @since 1.0
	 */
	public static function add_to_metabox(){
		global $post, $thepostid, $product_object; 

		// If variable billing is enabled, continue to show options. Otherwise, deprecate.
		$show_billing_period_options = apply_filters( 'wc_nyp_supports_variable_billing_period', WC_Name_Your_Price_Helpers::is_billing_period_variable( $product_object ) ); ?>
		
		<div class="options_group show_if_nyp">

			<?php do_action( 'woocommerce_name_your_price_options_pricing', $product_object, $show_billing_period_options ); ?>

		</div>

		<?php

	}

	/**
	 * Add variable billing period input to product metabox
	 *
	 * @param  object WC_Product $product_object
	 * @param  bool $show_billing_period_options
	 * @return print HTML
	 * @since  2.8.0
	 */
	public static function add_variable_billing_input( $product_object, $show_billing_period_options ){

		if( class_exists( 'WC_Subscriptions' ) && $show_billing_period_options ) {

			// Make billing period variable.
			woocommerce_wp_checkbox( array(
					'id' => '_variable_billing',
					'wrapper_class' => 'show_if_subscription',
					'label' => __( 'Variable Billing Period', 'wc_name_your_price' ),
					'description' => __( 'Allow the customer to set the billing period.', 'wc_name_your_price' ) ) );
		}

	}

	/**
	 * Add suggested inputs to product metabox
	 *
	 * @param  object WC_Product $product_object
	 * @param  bool $show_billing_period_options
	 * @param  mixed int|false $loop - for use in variations
	 * @return print HTML
	 * @since  2.8.0
	 */
	public static function add_suggested_price_inputs( $product_object, $show_billing_period_options, $loop = false ){

		// Suggested Price.
		woocommerce_wp_text_input( array(
			'id' => is_int( $loop ) ? "variation_suggested_price[$loop]" : "_suggested_price",
			'class' => 'wc_input_price short',
			'wrapper_class' => is_int( $loop ) ? 'form-row form-row-first' : '',
			'label' => __( 'Suggested Price', 'wc_name_your_price') . ' ('.get_woocommerce_currency_symbol().')' ,
			'desc_tip' => 'true',
			'description' => __( 'Price to pre-fill for customers.  Leave blank to not suggest a price.', 'wc_name_your_price' ),
			'data_type' => 'price',
			'value' => $product_object->get_meta( '_suggested_price', true )
		) );

		if( class_exists( 'WC_Subscriptions' ) && $show_billing_period_options && $loop === false ) {

			// Suggested Billing Period.
			woocommerce_wp_select( array(
				'id'          => '_suggested_billing_period',
				'label'       => __( 'per', 'wc_name_your_price' ),
				'wrapper_class' => 'show_if_subscription',
				'options'     => WC_Name_Your_Price_Helpers::get_subscription_period_strings()
				)
			);
		}

	}

	/**
	 * Add minimum inputs to product metabox
	 *
	 * @param  object WC_Product $product_object
	 * @param  bool $show_billing_period_options
	 * @param  mixed int|false $loop - for use in variations
	 * @return print HTML
	 * @since  2.8.0
	 */
	public static function add_minimum_price_inputs( $product_object, $show_billing_period_options = false, $loop = false ){

		// Minimum Price.
		woocommerce_wp_text_input( array(
			'id' => is_int( $loop ) ? "variation_min_price[$loop]" : "_min_price",
			'class' => 'wc_input_price short',
			'wrapper_class' => is_int( $loop ) ? 'form-row form-row-last' : '',
			'label' => __( 'Minimum Price', 'wc_name_your_price') . ' (' . get_woocommerce_currency_symbol() . ')',
			'desc_tip' => 'true',
			'description' =>  __( 'Lowest acceptable price for product. Leave blank to not enforce a minimum. Must be less than or equal to the set suggested price.', 'wc_name_your_price' ),
			'data_type' => 'price',
			'value' => $product_object->get_meta( '_min_price', true )
		) );

		if( class_exists( 'WC_Subscriptions' ) && $show_billing_period_options && $loop === false ) {
			// Minimum Billing Period.
			woocommerce_wp_select( array(
				'id'          => '_minimum_billing_period',
				'label'       => __( 'per', 'wc_name_your_price' ),
				'wrapper_class' => 'show_if_subscription',
				'options'     => WC_Name_Your_Price_Helpers::get_subscription_period_strings()
				)
			);
		}

		// Option to hide minimum price.
		woocommerce_wp_checkbox( array(
			'id' => is_int( $loop ) ? "variation_hide_nyp_minimum[$loop]" : "_hide_nyp_minimum",
			'label' => __( 'Hide Minimum Price', 'wc_name_your_price' ),
			'wrapper_class' => is_int( $loop ) ? 'form-row form-row-first' : '',
			'description' => __( 'Option to not show the minimum price on the front end.', 'wc_name_your_price' ),
			'value' => $product_object->get_meta( '_hide_nyp_minimum', true ),
			'desc_tip'    => true, 
		) );

	}

	/**
	 * Add maximum inputs to product metabox
	 *
	 * @param  object WC_Product $product_object
	 * @param  bool $show_billing_period_options
	 * @param  mixed int|false $loop - for use in variations
	 * @return print HTML
	 * @since  2.8.0
	 */
	public static function add_maximum_price_inputs( $product_object, $show_billing_period_options = false, $loop = false ){

		// Maximum Price.
		woocommerce_wp_text_input( array(
			'id' => is_int( $loop ) ? "variation_maximum_price[$loop]" : "_maximum_price",
			'class' => 'wc_input_price short',
			'wrapper_class' => is_int( $loop ) ? 'form-row form-row-first' : '',
			'label' => __( 'Maximum Price', 'wc_name_your_price') . ' (' . get_woocommerce_currency_symbol() . ')',
			'desc_tip' => 'true',
			'description' =>  __( 'Highest acceptable price for product. Leave blank to not enforce a maximum.', 'wc_name_your_price' ),
			'data_type' => 'price',
			'value' => $product_object->get_meta( '_maximum_price', true )
		) );

	}

	/**
	 * Save extra meta info
	 *
	 * @param object $product
	 * @return void
	 * @since 1.0 (renamed in 2.0)
	 */
	public static function save_product_meta( $product ) {

	   	$suggested = $minimum = '';

	   	if ( isset( $_POST['_nyp'] ) && in_array( $product->get_type(), WC_Name_Your_Price_Helpers::get_simple_supported_types() ) ) {
			$product->update_meta_data( '_nyp', 'yes' );
			// Removing the sale price removes NYP items from Sale shortcodes.
			$product->set_sale_price( '' );
			$product->delete_meta_data( '_has_nyp' );
		} else {
			$product->update_meta_data( '_nyp', 'no' );
		}

		if ( isset( $_POST['_suggested_price'] ) ) {
			$suggested = ( trim( $_POST['_suggested_price'] ) === '' ) ? '' : wc_format_decimal( $_POST['_suggested_price'] );
			$product->update_meta_data( '_suggested_price', $suggested );
		}

		if ( isset( $_POST['_min_price'] ) ) {
			$minimum = ( trim( $_POST['_min_price'] ) === '' ) ? '' : wc_format_decimal( $_POST['_min_price'] );
			$product->update_meta_data( '_min_price', $minimum );

			// Set the regular price as the min price to enable WC to sort by price.
			if( 'yes' == $product->get_meta( '_nyp', true ) ) {
				$product->set_price( $minimum );
				$product->set_regular_price( $minimum );
				$product->set_sale_price( '' );

				if( $product->is_type( 'subscription' ) ){
					$product->update_meta_data( '_subscription_price', $minimum );
				}
			}

		}

		// Show error if minimum price is higher than the suggested price.
		if ( $suggested && $minimum && $minimum > $suggested ) {
			WC_Admin_Meta_Boxes::add_error( __( 'The minimum price should not be higher than the suggested price for Name Your Price products. Please review your prices.', 'wc_name_your_price' ) );
		}

		// Variable Billing Periods.

		// save whether subscription is variable billing or not (only for regular subscriptions)
		if ( isset( $_POST['_variable_billing'] ) && $product->is_type( 'subscription' ) ) {
			$product->update_meta_data( '_variable_billing', 'yes' );
		} else {
			$product->delete_meta_data( '_variable_billing' );
		}

		// Suggested period - don't save if no suggested price.
		if ( $product->is_type( 'subscription' ) && isset( $_POST['_suggested_billing_period'] ) && array_key_exists( $_POST['_suggested_billing_period'], WC_Name_Your_Price_Helpers::get_subscription_period_strings() ) ){

			$suggested_period = wc_clean( $_POST['_suggested_billing_period'] );

			$product->update_meta_data( '_suggested_billing_period', $suggested_period );
		} else {
			$product->delete_meta_data( '_suggested_billing_period' );
		}

		// Minimum period - don't save if no minimum price.
		if ( $product->is_type( 'subscription' ) && $minimum && isset( $_POST['_minimum_billing_period'] ) && array_key_exists( $_POST['_minimum_billing_period'], WC_Name_Your_Price_Helpers::get_subscription_period_strings() ) ){

			$minimum_period = wc_clean( $_POST['_minimum_billing_period'] );

			$product->update_meta_data( '_minimum_billing_period', $minimum_period );
		} else {
			$product->delete_meta_data( '_minimum_billing_period' );
		}

		// Hide or obscure minimum price.
		$hide = isset( $_POST['_hide_nyp_minimum'] ) ? 'yes' : 'no';
		$product->update_meta_data( '_hide_nyp_minimum', $hide );

		// Maximum price.
		if ( isset( $_POST['_maximum_price'] ) ) {
			$maximum = ( trim( $_POST['_maximum_price'] ) === '' ) ? '' : wc_format_decimal( $_POST['_maximum_price'] );
			$product->update_meta_data( '_maximum_price', $maximum );
		}
	
	}


	/**
	 * Add NYP checkbox to each variation
	 *
	 * @param string $loop
	 * @param array $variation_data
	 * @param WP_Post $variation
	 * return print HTML
	 * @since 2.0
	 */
	public static function product_variations_options( $loop, $variation_data, $variation ){ 

		$variation_object = wc_get_product( $variation->ID );

		$variation_is_nyp = $variation_object->get_meta( '_nyp', 'edit' ); ?>

		<label><input type="checkbox" class="checkbox variation_is_nyp" name="variation_is_nyp[<?php echo $loop; ?>]" <?php checked( $variation_is_nyp, 'yes' ); ?> /> <?php _e( 'Name Your Price', 'wc_name_your_price'); ?> <a class="tips" data-tip="<?php _e( 'Customers are allowed to determine their own price.', 'wc_name_your_price'); ?>" href="#">[?]</a></label>

		<?php

	}

	/**
	 * Add NYP price inputs to each variation
	 *
	 * @param string $loop
	 * @param array $variation_data
	 * @param WP_Post $variation
	 * @return print HTML
	 * @since 2.0
	 */
	public static function add_to_variations_metabox( $loop, $variation_data, $variation ){

		$variation_object = wc_get_product( $variation->ID ); ?>

		<div class="variable_nyp_pricing">

			<?php do_action( 'woocommerce_name_your_price_options_variation_pricing', $variation_object, false, $loop ); ?>

		</div>

		<?php 

	}



	/**
	 * Save extra meta info for variable products
	 *
	 * @param int $variation_id
	 * @param int $i
	 * return void
	 * @since 2.0
	 */
	public static function save_product_variation( $variation_id, $i ){

		$variation = wc_get_product( $variation_id );
		$variation_suggested_price = $variation_min_price = $variation_max_price = '';

		// Set NYP status.
		$variation_is_nyp = isset( $_POST['variation_is_nyp'][$i] ) ? 'yes' : 'no';
		$variation->update_meta_data( '_nyp', $variation_is_nyp );

		// Save suggested price.
		if ( isset( $_POST['variation_suggested_price'][$i] ) ) {
			$variation_suggested_price = ( trim( $_POST['variation_suggested_price'][$i]  ) === '' ) ? '' : wc_format_decimal( $_POST['variation_suggested_price'][$i] );
			$variation->update_meta_data( '_suggested_price', $variation_suggested_price );
		}

		// Save minimum price.
		if ( isset( $_POST['variation_min_price'][$i] ) ) {
			$variation_min_price = ( trim( $_POST['variation_min_price'][$i]  ) === '' ) ? '' : wc_format_decimal( $_POST['variation_min_price'][$i] );
			$variation->update_meta_data( '_min_price', $variation_min_price );

			// If NYP, set prices to minimum.
			if( $variation_is_nyp == 'yes' ){
				$new_price = $variation_min_price === '' ? 0 : wc_format_decimal( $variation_min_price );
				$variation->set_price( $new_price );
				$variation->set_regular_price( $new_price );
				$variation->set_sale_price( '' );

				if( isset( $_POST['product-type'] ) && 'variable-subscription' == $_POST['product-type'] ){
					$variation->update_meta_data( '_subscription_price', $new_price );
				}
			} 
		}

		// Hide or obscure minimum price.
		$variation_hide_nyp_minimum = isset( $_POST['variation_hide_nyp_minimum'][$i] ) ? 'yes' : 'no';
		$variation->update_meta_data( '_hide_nyp_minimum', $variation_hide_nyp_minimum );

		// Maximum price.
		if ( isset( $_POST['variation_maximum_price'][$i] ) ) {
			$variation_max_price = ( trim( $_POST['variation_maximum_price'][$i] ) === '' ) ? '' : wc_format_decimal( $_POST['variation_maximum_price'][$i] );
			$variation->update_meta_data( '_maximum_price', $variation_max_price );
		}

		// Show error if minimum price is higher than the suggested price.
		if ( $variation_suggested_price && $variation_min_price && $variation_min_price > $variation_suggested_price ) {
			WC_Admin_Meta_Boxes::add_error( __( 'The minimum price should not be higher than the suggested price for Name Your Price variations. Please review your prices.', 'wc_name_your_price' ) );
		}

		// Save the meta.
		$variation->save();

	}

    /*-----------------------------------------------------------------------------------*/
	/* Bulk Edit */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Add options to variations bulk edit
	 *
	 * @return print HTML
	 * @since 2.0
	 */
	public static function bulk_edit_actions(){ ?>
		<optgroup label="<?php esc_attr_e( 'Name Your Price', 'wc_name_your_price' ); ?>">
			<option value="toggle_nyp"><?php _e( 'Toggle &quot;Name Your Price&quot;', 'wc_name_your_price' ); ?></option>
			<option value="variation_suggested_price"><?php _e( 'Set suggested prices', 'wc_name_your_price' ); ?></option>
			<option value="variation_suggested_price_increase"><?php _e( 'Increase suggested prices (fixed amount or %)', 'wc_name_your_price' ); ?></option>
			<option value="variation_suggested_price_decrease"><?php _e( 'Decrease suggested prices (fixed amount or %)', 'wc_name_your_price' ); ?></option>
			<option value="variation_min_price"><?php _e( 'Set minimum prices', 'wc_name_your_price' ); ?></option>
			<option value="variation_min_price_increase"><?php _e( 'Increase minimum prices (fixed amount or %)', 'wc_name_your_price' ); ?></option>
			<option value="variation_min_price_decrease"><?php _e( 'Decrease minimum prices (fixed amount or %)', 'wc_name_your_price' ); ?></option>
			<option value="variation_toggle_hide_min_price"><?php _e( 'Toggle &quot;hide minimum price&quot;', 'wc_name_your_price' ); ?></option>
			<option value="variation_maximum_price"><?php _e( 'Set maximum prices', 'wc_name_your_price' ); ?></option>
			<option value="variation_maximum_price_increase"><?php _e( 'Increase maximum prices (fixed amount or %)', 'wc_name_your_price' ); ?></option>
			<option value="variation_maximum_price_decrease"><?php _e( 'Decrease maximum prices (fixed amount or %)', 'wc_name_your_price' ); ?></option>
		</optgroup>
		
		<?php
	}



	/**
	 * Save NYP meta data when it is bulk edited from the Edit Product screen
	 *
	 * @param string $bulk_action The bulk edit action being performed
	 * @param array $data An array of data relating to the bulk edit action. $data['value'] represents the new value for the meta.
	 * @param int $variable_product_id The post ID of the parent variable product.
	 * @param array $variation_ids An array of post IDs for the variable prodcut's variations.
	 * @since 2.3.6
	 */
	public static function bulk_edit_variations( $bulk_action, $data, $variable_product_id, $variation_ids ) {

		switch ( $bulk_action ) {
			case 'toggle_nyp':
				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					$_nyp = $variation->get_meta( '_nyp' );
					// Check for definitive 'yes' as new variations will have null values for _nyp meta key.
					$is_nyp = 'yes' === $_nyp ? 'no' : 'yes';
					$variation->update_meta_data( '_nyp', wc_clean( $is_nyp ) );
					$variation->save_meta_data();
				}
			break;
			case 'variation_suggested_price':

				$meta_key = str_replace( 'variation', '', $bulk_action );
				$new_price = trim( $data['value'] ) === '' ? '' : wc_format_decimal( $data['value'] );
				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						$variation->save_meta_data();
					}
				}

			break;
			case 'variation_min_price':

				$meta_key = str_replace( 'variation', '', $bulk_action );
				$new_price = trim( $data['value'] ) === '' ? '' : wc_format_decimal( $data['value'] );

				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						// Set minimum price as regular price.
						$variation->set_price( $new_price );
						$variation->set_regular_price( $new_price );
						$variation->set_sale_price( '' );
						$variation->save();
					}
				}

			break;
			case 'variation_suggested_price_increase':
				$meta_key = str_replace( array('variation', '_increase' ), '', $bulk_action );
				$percentage = isset( $data['percentage'] ) && $data['percentage'] == 'yes' ? true : false;

				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$price = $variation->get_meta( $meta_key );
						if( $percentage ){
							$new_price = $price * ( 1 + $data['value'] / 100 );
						} else {
							$new_price = $price + $data['value'];
						}
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						$variation->save_meta_data();
					}
				}

			break;
			case 'variation_min_price_increase':

				$meta_key = str_replace( array('variation', '_increase' ), '', $bulk_action );
				$percentage = isset( $data['percentage'] ) && $data['percentage'] == 'yes' ? true : false;

				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$price = $variation->get_meta( $meta_key );
						if( $percentage ){
							$new_price = $price * ( 1 + $data['value'] / 100 );
						} else {
							$new_price = $price + $data['value'];
						}
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						// Set minimum price as regular price.
						$variation->set_price( $new_price );
						$variation->set_regular_price( $new_price );
						$variation->set_sale_price( '' );
						$variation->save();
					}
				}

			break;
			case 'variation_suggested_price_decrease':

				$meta_key = str_replace( array('variation', '_decrease' ), '', $bulk_action );
				$percentage = isset( $data['percentage'] ) && $data['percentage'] == 'yes' ? true : false;

				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$price = $variation->get_meta( $meta_key );
						if( $percentage ){
							$new_price = $price * ( 1 - $data['value'] / 100 );
						} else {
							$new_price = $price - $data['value'];
						}
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						$variation->save_meta_data();
					}
				}

			break;
			case 'variation_min_price_decrease':

				$meta_key = str_replace( array('variation', '_decrease' ), '', $bulk_action );
				$percentage = isset( $data['percentage'] ) && $data['percentage'] == 'yes' ? true : false;

				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$price = $variation->get_meta( $meta_key );
						if( $percentage ){
							$new_price = $price * ( 1 - $data['value'] / 100 );
						} else {
							$new_price = $price - $data['value'];
						}
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						// Set minimum price as regular price.
						$variation->set_price( $new_price );
						$variation->set_regular_price( $new_price );
						$variation->set_sale_price( '' );
						$variation->save();
					}
				}
			break;
			case 'variation_toggle_hide_min_price':
				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					$_hide_nyp_minimum = $variation->get_meta( '_hide_nyp_minimum' );
					// Check for definitive 'yes' as new variations will have null values for _hide_nyp_minimum meta key.
					$is_hide_nyp_minimum = 'yes' === $_hide_nyp_minimum ? 'no' : 'yes';
					$variation->update_meta_data( '_hide_nyp_minimum', wc_clean( $is_hide_nyp_minimum ) );
					$variation->save_meta_data();
				}
			break;
			case 'variation_maximum_price':

				$meta_key = str_replace( 'variation', '', $bulk_action );
				$new_price = trim( $data['value'] ) === '' ? '' : wc_format_decimal( $data['value'] );
				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						$variation->save_meta_data();
					}
				}

			break;
			case 'variation_maximum_price_increase':
				$meta_key = str_replace( array('variation', '_increase' ), '', $bulk_action );
				$percentage = isset( $data['percentage'] ) && $data['percentage'] == 'yes' ? true : false;

				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$price = $variation->get_meta( $meta_key );
						if( $percentage ){
							$new_price = $price * ( 1 + $data['value'] / 100 );
						} else {
							$new_price = $price + $data['value'];
						}
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						$variation->save_meta_data();
					}
				}

			break;
			case 'variation_maximum_price_decrease':

				$meta_key = str_replace( array('variation', '_decrease' ), '', $bulk_action );
				$percentage = isset( $data['percentage'] ) && $data['percentage'] == 'yes' ? true : false;

				foreach ( $variation_ids as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					if( WC_Name_Your_Price_Helpers::is_nyp( $variation ) ) {
						$price = $variation->get_meta( $meta_key );
						if( $percentage ){
							$new_price = $price * ( 1 - $data['value'] / 100 );
						} else {
							$new_price = $price - $data['value'];
						}
						$variation->update_meta_data( $meta_key, wc_format_decimal( $new_price ) );
						$variation->save_meta_data();
					}
				}

			break;

		}

	}

}
WC_NYP_Meta_Box_Product_Data::init();
