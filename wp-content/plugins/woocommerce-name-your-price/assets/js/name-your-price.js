jQuery( document ).ready( function($) {

	/**
	 * woocommerce_nyp_update function
	 * Wraps all important nyp callbacks for plugins that maybe don't have elements available on load
	 * ie: quickview, bundles, etc
	 */
	$.fn.woocommerce_nyp_update = function() {

		/**
		 * Name Your Price Handler for individual items
		 */
		$( this ).on( 'woocommerce-nyp-update', function() {

			// Some important objects.
			var $cart 			= $( this );
			var $nyp 			= $cart.find( '.nyp' );
			var $nyp_input 		= $cart.find( '.nyp-input' );
			var $submit 		= $cart.find(':submit');
			var $ajax_cart_button 	= $cart.find('.ajax_add_to_cart');

			// The current price.
			var form_price 	= $nyp_input.val();

			// Add a div to hold the error message.
			var $error = $cart.find( '.woocommerce-nyp-message' );

			if ( ! $error.length ){
				$('<div class="woocommerce-nyp-message woocommerce-error"></div>').hide().prependTo($nyp);
			}

			// The default error message.
			var error_message = $nyp.data( 'hide-minimum' ) ? $nyp.data( 'hide-minimum-error' ) : $nyp.data( 'minimum-error' );
			var error_tag = "%%MINIMUM%%";
			var error = false;
			var error_price = ''; // This will hold the formatted price for the error message.

			// Convert price to default decimal setting for calculations.
			var form_price_num 	= woocommerce_nyp_unformat_price( form_price );

			var min_price 			= parseFloat( $nyp.data( 'min-price' ) );
			var max_price 			= parseFloat( $nyp.data( 'max-price' ) );
			var annual_minimum	= parseFloat( $nyp.data( 'annual-minimum' ) );

			// Get variable billing period data.
			var $nyp_period		= $cart.find( '.nyp-period' );
			var form_period		= $nyp_period.val();

			// If has variable billing period AND a minimum then we need to annulalize min price for comparison.
			if ( annual_minimum > 0 ){

				// Calculate the price over the course of a year for comparison.
				form_annulualized_price = form_price_num * woocommerce_nyp_params.annual_price_factors[form_period];

				// If the calculated annual price is less than the annual minimum.
				if( form_annulualized_price < annual_minimum ){

					error = annual_minimum / woocommerce_nyp_params.annual_price_factors[form_period];

					// In the case of variable period we need to adjust the error message a bit.
					error_price = woocommerce_nyp_format_price( error, woocommerce_nyp_params.currency_format_symbol, true ) + ' / ' + $nyp_period.find('option[value="' + form_period + '"]').text();

				}

			// Otherwise a regular product or subscription with non-variable periods, compare price directly.
			} else if ( form_price_num < min_price ) {
				error = min_price;
				error_price = woocommerce_nyp_format_price( min_price, woocommerce_nyp_params.currency_format_symbol, true );

			// Check maximum price.
			} else if ( form_price_num > max_price ) {
				error = max_price;
				error_message = $nyp.data( 'maximum-error' );
				error_tag = "%%MAXIMUM%%";
				error_price = woocommerce_nyp_format_price( max_price, woocommerce_nyp_params.currency_format_symbol, true );
			}

			// Maybe auto-format the input.
			if( $.trim( form_price ) != '' ){
				$nyp_input.val( woocommerce_nyp_format_price( form_price_num ) );
			}

			// Always add the price to the button as data for AJAX add to cart.
			$submit.data( $nyp_input.attr('name'), woocommerce_nyp_format_price( form_price_num ) );

			// If we've set an error, show message and prevent submit.
			if ( error ){

				// Disable submit.
				$submit.prop( 'disabled', true ).addClass( 'disabled' );

				// Show error.
				error_message = error_message.replace( error_tag, error_price );

				// For some reason slideDown doesn't happen on page load, so we can use that to not focus the input right away,
				// which is useful if someone nulls out the initial input and therefore loads in error state.
				$error.html( error_message ).slideDown( function() {
				    $nyp_input.focus();
				});

			// Otherwise allow submit and update.
			} else {

				// Allow submit.
				$submit.prop( 'disabled', false ).removeClass( 'disabled' );

				// Remove error.
				$error.slideUp();

				// Product add ons compatibility.
				$(this).find( '#product-addons-total' ).data( 'price', form_price_num );
				$cart.trigger( 'woocommerce-product-addons-update' );

				// Bundles compatibility.
				$nyp.data( 'price', form_price_num );
				$cart.trigger( 'woocommerce-nyp-updated-item' );
				$( 'body' ).trigger( 'woocommerce-nyp-updated' );

			}

		} ); // End woocommerce-nyp-update handler.

		// NYP update on change to any nyp input.
		$( this ).on( 'change', '.nyp-input, .nyp-period', function() { 
			var $cart = $(this).closest( '.cart, .nyp-product' );
			$cart.trigger( 'woocommerce-nyp-update' );
		} );

		// Trigger right away.
		$( this ).find( '.nyp-input' ).trigger( 'change' );

		/**
		 * Handle NYP Variations
		 */

		if ( $( this ).hasClass( 'variations_form' ) ) {

			// Some important objects.
			var $variation_form 	= $(this);
			var $add_to_cart 		= $(this).find( 'button.single_add_to_cart_button' );
			var $nyp 				= $(this).find( '.nyp' );
			var $nyp_input 			= $nyp.find( '.nyp-input' );
			var $minimum 			= $nyp.find( '.minimum-price' );
			var $subscription_terms = $nyp.find( '.subscription-details' );
			var $error      = $variation_form.find( '.woocommerce-nyp-message' );

			// The add to cart text.
			var default_add_to_cart_text 	= $add_to_cart.html();

			// Hide the nyp form by default.
			$nyp.hide();
			$minimum.hide();

			// Listeners

			// When variation is found, decide if it is NYP or not.
			$variation_form

			.on( 'found_variation', function( event, variation ) {

				// Clear any disabled attributes.
				$add_to_cart.attr( 'disabled', false );

				// Hide any existing error message.
				$error.slideUp();

				// If NYP show the price input and tweak the data attributes.
				if ( typeof variation.is_nyp != undefined && variation.is_nyp == true ) {

					// Switch add to cart button text if variation is NYP.
					$add_to_cart.html( variation.add_to_cart_text );

					// Get the prices out of data attributes.
					var display_price = typeof variation.display_price != 'undefined' ? variation.display_price : '';
					var minimum_price = typeof variation.minimum_price != 'undefined' ? variation.minimum_price : '';
					var maximum_price = typeof variation.maximum_price != 'undefined' ? variation.maximum_price : '';

					// Maybe auto-format the input.
					if( $.trim( display_price ) != '' ){
						$nyp_input.val( woocommerce_nyp_format_price( display_price ) );
					} else {
						$nyp_input.val( '' );
					}

					// Maybe show subscription terms.
					if( $subscription_terms.length && variation.subscription_terms ){
						$subscription_terms.html( variation.subscription_terms );
					}

					// Maybe show minimum price html.
					if( variation.minimum_price_html ){
						$minimum.html( variation.minimum_price_html ).show();
					} else {
						$minimum.hide();
					}

					// Set the NYP data attributes for JS validation on submit.
					$nyp.data( 'min-price', minimum_price ).slideDown();
					$nyp.data( 'max-price', maximum_price );

					// Toggle minimum error message between explicit and obscure.
					$nyp.data( 'hide-minimum', variation.hide_minimum );

					// Product add ons compatibility.
					var form_price_num 	= woocommerce_nyp_unformat_price( $nyp_input.val() );
					$(this).find( '#product-addons-total' ).data( 'price', form_price_num );
					$(this).trigger( 'woocommerce-product-addons-update' );

				// If not NYP, hide the price input.
				} else {

					// Use default add to cart button text if variation is not NYP.
					$add_to_cart.html( default_add_to_cart_text );

					// Hide.
					$nyp.slideUp();

				}

			} )

			.on( 'reset_image', function( event ) {

				$add_to_cart.html( default_add_to_cart_text );

				// Clear any disabled attributes.
				$add_to_cart.attr( 'disabled', false );
				$nyp.slideUp();
				$error.hide();

			} )

			// Hide the price input when reset is clicked.
			.on( 'click', '.reset_variations', function( event ) {

				$add_to_cart.html( default_add_to_cart_text );

				// Clear any disabled attributes.
				$add_to_cart.attr( 'disabled', false );
				$nyp.slideUp();
				$error.hide();

			} );

			// Need to re-trigger some things on load since Woo unbinds the found_variation event.
			$( this ).find( '.variations select, .variations input[type=radio]' ).trigger( 'change' );

		}


	} // End fn.woocommerce_nyp_update().

	/**
	 * Run when Quick view item is launched.
	 */
	$( 'body' ).on( 'quick-view-displayed', function() {
		$( 'body' ).find( '.cart:not(.cart_group)' ).each( function() {
			$( this ).woocommerce_nyp_update();
		} );
	} );

	/**
	 * Run when a Composite component is re-loaded.
	 */
	$( 'body .component' ).on( 'wc-composite-component-loaded', function() {
		$( this ).find( '.cart:not(.cart_group)' ).each( function() {
			$( this ).woocommerce_nyp_update();
		} );
	} );

	/**
	 * Run on load.
	 */
	$( 'body' ).find( '.cart:not(.cart_group, .grouped_form)' ).each( function() {
		$( this ).woocommerce_nyp_update();
	} );

	/**
	 * Run on load for grouped products.
	 */
	$( 'body .grouped_form' ).find( '.nyp-product' ).each( function() {
		$( this ).woocommerce_nyp_update();
	} );

	/**
	 * Helper functions
	 */
	// Format the price with accounting.js.
	function woocommerce_nyp_format_price( price, currency_symbol, format ){

		if ( typeof currency_symbol === 'undefined' ) {
			currency_symbol = '';
		}

		if ( typeof format === 'undefined' ) {
			format = false;
		}

		var currency_format = format ? woocommerce_nyp_params.currency_format : '%v';

		return accounting.formatMoney( price, {
				symbol : currency_symbol,
				decimal : woocommerce_nyp_params.currency_format_decimal_sep,
				thousand: woocommerce_nyp_params.currency_format_thousand_sep,
				precision : woocommerce_nyp_params.currency_format_num_decimals,
				format: currency_format
		}).trim();

	}

	// Get absolute value of price and turn price into float decimal.
	function woocommerce_nyp_unformat_price( price ){
		return Math.abs( parseFloat( accounting.unformat( price, woocommerce_nyp_params.currency_format_decimal_sep ) ) );
	}

} );
