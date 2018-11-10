'use strict';

function paypal() {
	//console.log('paypal.js loaded');


	$(document).ready( function() {

		if( $('body').hasClass('page-id-189') ){

			var wpPostData = {};

			$(document).on('gform_confirmation_loaded', function(){
				//console.log('gform_confirmation_loaded');

				$('.donation-loading').addClass('active');

				$('html,body').animate({
					scrollTop: $('#donation-form-container').offset().top - 300
				}, 200);

				var wpEndpoint = 'https://theavenueconcept.org/wp-json/paypal/v1/iframe';

				$.ajax({
					url: wpEndpoint,
					type: 'POST',
					data: wpPostData
				})
				.done(function(data) {
					//console.log('success');
					//console.log(data);
					$('#paypal-target').append(data.iframe);
					setTimeout(function() {
						$('.donation-loading').removeClass('active');
						$('#donation-summary-amount').html('$ ' + data.amount);
						$('.donation-summary').addClass('active');
						$('#donation-form-container').addClass('iframe-loaded');
					}, 1000);
				})
				.fail(function(error) {
					console.log(error);
				})
				.always(function() {
					//console.log('complete');
				});

			});


			$(document).on('gform_post_render', function(){
				//console.log('gform_post_render');
			});


			var form = $('#donation form');
			form.on('submit', function() {
				//console.log('on submit');

				wpPostData = $(this).serialize();

			});

		}

	});



}

export { paypal };
