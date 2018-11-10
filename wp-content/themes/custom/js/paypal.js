'use strict';

function paypal() {
	//console.log('paypal.js loaded');


	$(document).ready( function() {

		if( $('body').hasClass('page-id-189') ){

			var donationLoading = $('<div>').addClass('donation-loading');
			$('.gform_wrapper').append(donationLoading);

			var wpPostData = {};

			$(document).on('gform_confirmation_loaded', function(){
				//console.log('gform_confirmation_loaded');
				console.log($('#donation-form-container').offset().top);

				$('html,body').animate({
					scrollTop: $('#donation-form-container').offset().top - 300
				}, 200);

				var wpEndpoint = '/wp-json/paypal/v1/iframe';

				$.ajax({
					url: wpEndpoint,
					type: 'POST',
					data: wpPostData
				})
				.done(function(data) {
					console.log('success');
					console.log(data);
					$('#paypal-target').append(data);
					$('.donation-loading').removeClass('active');
				})
				.fail(function(error) {
					console.log('error');
				})
				.always(function() {
					//console.log('complete');
				});

			});

			$(document).on('gform_post_render', function(){
				setTimeout(function() {
					if( $('.gform_validation-error').length > 0 ){
						$('.donation-loading').removeClass('active');
					}
				}, 500);

			});

			var form = $('#donation form');
			form.on('submit', function(e) {
				//console.log('on submit');

				$('.donation-loading').addClass('active');
				wpPostData = $(this).serialize();

			});

		}

	});



}

export { paypal }
