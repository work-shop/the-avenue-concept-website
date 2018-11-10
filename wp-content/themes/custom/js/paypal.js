'use strict';

function paypal() {
	//console.log('paypal.js loaded');


	$(document).ready( function() {

		if( $('body').hasClass('page-id-189') ){

			if(window.location.hash && window.location.hash === '#donation-form-container') {
				scrollToForm();
			} 

			var wpPostData = {};

			$(document).on('gform_confirmation_loaded', function(){
				//console.log('gform_confirmation_loaded');

				$('.donation-loading').addClass('active');
				$('.donation-error').remove();

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

				scrollToForm();
				wpPostData = $(this).serialize();

			});

		}

	});

	function scrollToForm(){
		var offset = 300;
		if( $(window).width() < 768 ){
			offset = 148;
		}
		$('html,body').animate({
			scrollTop: $('#donation-form-container').offset().top - offset
		}, 300);
	}



}

export { paypal };
