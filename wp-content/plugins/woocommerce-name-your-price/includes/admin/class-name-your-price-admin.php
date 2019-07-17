<?php
/**
 * Name Your Price Admin Class
 *
 * Adds a name your price setting tab, quick edit, bulk edit, loads metabox class.
 *
 * @package		WooCommerce Name Your Price
 * @subpackage	WC_Name_Your_Price_Admin
 * @category	Class
 * @author		Kathy Darling
 * @since		1.0
 */
class WC_Name_Your_Price_Admin {

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

		// Include product meta boxes.
		add_action( 'admin_init', array( __CLASS__, 'admin_includes' ) );

		// Admin Scripts.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'meta_box_script' ), 20 );

		// Add Help Tab.
		add_action( 'contextual_help', array( __CLASS__, 'add_help_tab' ) );

		// Edit Products screen.
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'admin_price_html' ), 20, 2 );
		
		// Product Filters.
		add_filter( 'woocommerce_product_filters', array( __CLASS__, 'product_filters' ) );
		add_filter( 'parse_query', array( __CLASS__, 'product_filters_query' ) );

		// Quick Edit.
		add_action( 'manage_product_posts_custom_column', array( __CLASS__, 'column_display'), 10, 2 );
		add_action( 'woocommerce_product_quick_edit_end',  array( __CLASS__, 'quick_edit') );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'quick_edit_scripts'), 20 );
		add_action( 'woocommerce_product_quick_edit_save', array( __CLASS__, 'quick_edit_save') );

		// Admin Settings via settings API.
		add_filter( 'woocommerce_get_settings_pages', array( __CLASS__, 'add_settings_page' ) );

	}

	/**
	 * Admin init.
	 */
	public static function admin_includes() {
		include_once( 'meta-boxes/class-wc-nyp-meta-box-product-data.php' );
	}


	/**
	 * Javascript to handle the NYP metabox options
	 *
	 * @param string $hook
	 * @return void
	 * @since 1.0
	 */
    public static function meta_box_script( $hook ){

		// Check if on Edit-Post page (post.php or new-post.php).
		if( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ){
			return;
		}

		// Now check to see if the $post type is 'product'.
		global $post;
		if ( ! isset( $post ) || 'product' != $post->post_type ){
			return;
		}

		// Enqueue and localize.
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'woocommerce_nyp_metabox', WC_Name_Your_Price()->plugin_url() . '/assets/js/admin/nyp-metabox'. $suffix . '.js', array( 'jquery' ), WC_Name_Your_Price()->version, true );
		
		$strings = array ( 'enter_value' => __( 'Enter a value', 'wc_name_your_price' ),
							'price_adjust' => __( 'Enter a value (fixed or %)', 'wc_name_your_price' ) );

		wp_localize_script( 'woocommerce_nyp_metabox', 'woocommerce_nyp_metabox', $strings );

	}

	/**
	 * Add help tab for product meta
	 *
	 * @return print html
	 * @since 1.0
	 */
    public static function add_help_tab(){

    	if ( ! function_exists( 'get_current_screen' ) ){
    		return;
    	}

		$screen = get_current_screen();

		// Product/Coupon/Orders.
		if ( ! in_array( $screen->id, array( 'product', 'edit-product' ) ) ){
			return;
		}

		$screen->add_help_tab( array(
	    'id'	=> 'woocommerce_nyp_tab',
	    'title'	=> __('Name Your Price', 'wc_name_your_price'),
	    'content'	=>

	    	'<h4>' . __( 'Name Your Price', 'wc_name_your_price' ) . '</h4>' .

	    	'<p>' . __( 'In the "Product Meta" metabox, check the Name Your Price checkbox to allow your customers to enter their own price.', 'wc_name_your_price' ) . '</p>' .

	    	'<p>' . __( 'As of Name Your Price version 2.0, this ability is available for "Simple", "Subscription", "Bundled", "Variable" and "Variable Subscriptions" Products.', 'wc_name_your_price' ) . '</p>' .

	    	'<h4>' . __( 'Suggested Price', 'wc_name_your_price' ) . '</h4>' .

	    	'<p>' . __( 'This is the price you\'d like to suggest to your customers.  The Name Your Price input will be prefilled with this value.  To not suggest a price at all, you may leave this field blank.', 'wc_name_your_price' ) . '</p>' .

	    	'<p>' . __( 'This value must be a positive number.', 'wc_name_your_price' ) . '</p>' .

	    	'<h4>' . __( 'Minimum Price', 'wc_name_your_price' ) . '</h4>' .

	    	'<p>' . __( 'This is the lowest price you are willing to accept for this product.  To not enforce a minimum (ie: to accept any price, including zero), you may leave this field blank.', 'wc_name_your_price' ) . '</p>' .

	    	'<p>' . __( 'This value must be a positive number that is less than or equal to the set suggested price.', 'wc_name_your_price' ) . '</p>' .

	    	'<h4>' . __( 'Subscriptions', 'wc_name_your_price' ) . '</h4>' .

	    	'<p>' . __( 'If you have a name your price subscription product, the subscription time period fields are still needed, but the price will be disabled in lieu of the Name Your Price suggested and minimum prices.', 'wc_name_your_price' ) . '</p>' .

	    	'<p>' . __( 'As of Name Your Price version 2.0, you can now allow variable billing periods.', 'wc_name_your_price' ) . '</p>'

	    ) );

	}

    /*-----------------------------------------------------------------------------------*/
	/* Product Overview - edit columns */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Change price in edit screen to NYP
	 *
	 * @param string $price
	 * @param object $product
	 * @return string
	 * @since 1.0
	 */
	public static function admin_price_html( $price, $product ){

		if( WC_Name_Your_Price_Helpers::is_nyp( $product ) && ! isset( $product->is_filtered_price_html ) ){
			$price = wc_get_price_html_from_text() . WC_Name_Your_Price_Helpers::get_price_string( $product, 'minimum', true );
		} else if( WC_Name_Your_Price_Helpers::has_nyp( $product ) && ! isset( $product->is_filtered_price_html ) ){		
			$price = wc_get_price_html_from_text() . WC_Name_Your_Price_Helpers::get_price_string( $product, 'minimum-variation', true );
		}

		return $price;

	}

	/**
	 * Add NYP as option to product filters in admin 
	 *
	 * @param string $output
	 * @return string
	 * @since 2.0.0
	 */
	public static function product_filters( $output ){
		global $wp_query;

		$startpos = strpos ( $output, '<select name="product_type"' );

		if( $startpos !== false ) {
			
			$endpos = strpos( $output, '</select>', $startpos );
			
			if( $endpos !== false ) {

				$current = isset( $wp_query->query['product_type'] ) ? $wp_query->query['product_type'] : false;

				$nyp_option = sprintf( '<option value="name-your-price" %s > &#42; %s</option>',
					selected( 'name-your-price', $current, false ),
					__( 'Name Your Price', 'wc_name_your_price' )
				);

				$output = substr_replace( $output, $nyp_option, $endpos, 0 );
			}
			
		}

		return $output;

	}

	/**
	 * Filter the products in admin based on options
	 *
	 * @param mixed $query
	 * @since 2.0.0
	 */
	public static function product_filters_query( $query ) {
		global $typenow;

	    if ( $typenow == 'product' ) {

	    	if ( isset( $query->query_vars['product_type'] ) ) {
		    	// Subtypes.
		    	if ( $query->query_vars['product_type'] == 'name-your-price' ) {
			    	$query->query_vars['product_type']  = '';
			    	$query->is_tax = false;
			    	$meta_query = array(
			    		'relation' => 'OR',
						array(
							'key' => '_nyp',
							'value' => 'yes',
							'compare' => '=',
						),
						array(
							'key' => '_has_nyp',
							'value' => 'yes',
							'compare' => '='
						)
					);
					$query->query_vars['meta_query'] = $meta_query;
			    }
		    }
		}
	}


    /*-----------------------------------------------------------------------------------*/
	/* Quick Edit */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Display the column content
	 *
	 * @param string $column_name
	 * @param int $post_id
	 * @return print HTML
	 * @since 1.0
	 */
	public static function column_display( $column_name, $post_id ) {

		switch ( $column_name ) {

			case 'price' :

				$_product = wc_get_product( $post_id );

				// Custom inline data for NYP.
				$nyp = WC_Name_Your_Price_Helpers::is_nyp( $_product ) ? 'yes' : 'no';

				// If variable billing is enabled, continue to show options. Otherwise, deprecate.
				$is_sub = wc_bool_to_string( WC_Name_Your_Price_Helpers::is_subscription( $_product ) );
				$is_variable_billing = WC_Name_Your_Price_Helpers::is_billing_period_variable( $_product );

				$show_variable_billing = wc_bool_to_string( apply_filters( 'wc_nyp_supports_variable_billing_period',  $is_variable_billing ) ); 
				$is_variable_billing = wc_bool_to_string( $is_variable_billing );

				$suggested = wc_format_localized_price( WC_Name_Your_Price_Helpers::get_suggested_price( $_product ) );
				$suggested_period = WC_Name_Your_Price_Helpers::get_suggested_billing_period( $_product );

				$min = wc_format_localized_price( WC_Name_Your_Price_Helpers::get_minimum_price( $_product ) );
				$min_period = WC_Name_Your_Price_Helpers::get_minimum_billing_period( $_product );

				$max = wc_format_localized_price( WC_Name_Your_Price_Helpers::get_maximum_price( $_product ) );

				$is_nyp_allowed = wc_bool_to_string( $_product->is_type( WC_Name_Your_Price_Helpers::get_simple_supported_types() ) );

				$is_minimum_hidden = wc_bool_to_string( WC_Name_Your_Price_Helpers::is_minimum_hidden( $_product ) );

				echo '
					<div class="hidden" id="nyp_inline_' . $post_id . '">
						<div class="nyp">' . $nyp . '</div>
						<div class="is_sub">' . $is_sub . '</div>
						<div class="show_variable_billing">' . $show_variable_billing . '</div>
						<div class="is_variable_billing">' . $is_variable_billing . '</div>
						<div class="suggested_price">' . $suggested . '</div>
						<div class="suggested_period">' . $suggested_period . '</div>
						<div class="min_price">' . $min . '</div>
						<div class="min_period">' . $min_period . '</div>
						<div class="max_price">' . $max . '</div>
						<div class="is_nyp_allowed">' . $is_nyp_allowed . '</div>
						<div class="is_minimum_hidden">' . $is_minimum_hidden . '</div>
					</div>
				';

			break;

		}

	}

	/**
	 * Add quick edit fields
	 *
	 * @return print HTML
	 * @since 1.0
	 */
	public static function quick_edit() {  ?>

		<style>
			.inline-edit-row fieldset .show_if_nyp .form-field { clear: both; }

			.inline-edit-row fieldset .show_if_nyp .form-field._suggested_billing_period_field, 
			.inline-edit-row fieldset .show_if_nyp .form-field._minimum_billing_period_field { clear: none; }

			.inline-edit-row fieldset .show_if_nyp label { display: inline-block; float: left; font-size: 12px; font-style: italic; line-height: 1.2; margin-right: .5em; }
			.inline-edit-row fieldset .show_if_nyp ._variable_billing_field label { float: none; }

			.inline-edit-row fieldset .show_if_nyp input[type="text"] {
				box-sizing: border-box;
				width: 15em;
				float: left;
				margin: 1px 1% 1px 1px;
			}

			.inline-edit-row fieldset .show_if_nyp .woocommerce-help-tip { display: none; }
		</style>

			<br class="clear" />

		    <div id="nyp-fields" class="inline-edit-group">

		    	<?php wp_nonce_field( 'wc_nyp_nonce_action', 'wc_nyp_nonce_field' ); ?>

		    	<label>
					<input type="checkbox" name="_nyp" class="nyp" value="yes">
					<span class="checkbox-title"><?php _e( 'Name Your Price', 'wc_name_your_price' ); ?></span>
				</label>

				<div class="options_group show_if_nyp">

					<?php do_action( 'woocommerce_name_your_price_options_pricing', new WC_Product, true ); ?>

				</div>

			</div>

	  <?php
	}

	/**
	 * Load the scripts for dealing with the quick edit
	 *
	 * @param string $hook
	 * @return void
	 * @since 1.0
	 */
	public static function quick_edit_scripts( $hook ) {
		global $post_type;

		if ( $hook == 'edit.php' && $post_type == 'product' ){
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
 			wp_enqueue_script( 'nyp-quick-edit', WC_Name_Your_Price()->plugin_url() . '/assets/js/admin/nyp-quick-edit'. $suffix .'.js', array( 'jquery' ), WC_Name_Your_Price()->version, true );
		}

	}

	/**
	 * Save quick edit changes
	 *
	 * @param WC_Product object $product
	 * @return void
	 * @since 1.0
	 * @since 2.0 modified to only work in WC 2.1
	 *
	 */
	public static function quick_edit_save( $product ) {

		// Only save for simple suppported types.
		if( isset( $_POST['wc_nyp_nonce_field'] ) && wp_verify_nonce( $_POST['wc_nyp_nonce_field'], 'wc_nyp_nonce_action' ) ) {
			WC_NYP_Meta_Box_Product_Data::save_product_meta( $product );
			$product->save();
		}

	}


	/*-----------------------------------------------------------------------------------*/
	/* Admin Settings */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Include the settings page class
	 * compatible with WooCommerce 2.1
	 *
	 * @param array $settings ( the included settings pages )
	 * @return array
	 * @since 2.0
	 */
	public static function add_settings_page( $settings ) {

		$settings[] = include( 'class-wc-settings-nyp.php' );

		return $settings;
	}

	/*-----------------------------------------------------------------------------------*/
 	/* Deprecated Methods */
   	/*-----------------------------------------------------------------------------------*/
   
   	/**
	 * Save extra meta info for variable products
	 *
	 * @param int $post_id
	 * @return void
	 * @since 2.0
	 */
	public static function save_variable_product_meta( $post_id ){

		_deprecated_function( __METHOD__, '2.4.0', 'Meta for the variable product now saved during sync' );

		$product = wc_get_product( $post_id );

		return $product->sync();
	}


}
WC_Name_Your_Price_Admin::init();
