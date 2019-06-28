'use strict';


function membershipLevels( config ) {
	console.log('membership-levels.js loaded');

	$(document).ready( function() {

		$( '.input-membership-level input' ).change(function() {
			console.log( $('.input-membership-level input:checked').val() );
			var price = $('.input-membership-level input:checked').val()
			$('#input_7_8').val(price);
		});

	});

}

export { membershipLevels };
