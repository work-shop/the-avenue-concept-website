'use strict';

function accordian() {
	//console.log('accordian.js loaded');

	$(document).ready( function() {
		//remember that a line is commented out in jquery-accordion.js:237 to stop siblings from collapsing
		$('.accordion').accordion({
			'transitionSpeed': 400
		});
	});

}

export { accordian };
