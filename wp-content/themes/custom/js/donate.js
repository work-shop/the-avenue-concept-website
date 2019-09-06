'use strict';


function donate() {
	//console.log('donation.js loaded');

	$(document).ready( function() {

		$( '.button-donation-select' ).click(function(e) {
			e.preventDefault();

			if( $( '.button-donation-toggle' ).hasClass('active') ){
				$( '#nyp-fields' ).addClass('hidden');
				$( '#nyp-button' ).addClass('hidden');
				$( '#donate-button' ).removeClass('hidden');
			}
			var donationCartID = $(this).data('cart-ID'); 
			donationButton(donationCartID);
			$( '.button-donation-level' ).removeClass('active');
			$(this).addClass('active');
		});

		$( '.button-donation-toggle' ).click(function(e) {
			e.preventDefault();
			if( $(this).hasClass('active') === false ){
				$( '#nyp-fields' ).removeClass('hidden');
				$( '#nyp-button' ).removeClass('hidden');
				$( '#donate-button' ).addClass('hidden');
				$( '.button-donation-level' ).removeClass('active');
				$(this).addClass('active');
			}
		});

	});

	function donationButton(donationCartUrl){
		$('#donate-button').attr('href',donationCartUrl);
	}

}

export { donate };


