'use strict';

function singlePost( config ) {
	//console.log("single-post.js loaded");

	$(document).ready( function() {

		if( $('.single-post').length ){
			//console.log('this is the single post template');

			var heading = $('.page-hero-title');
			resizeHeading(heading);
		}

	});

}

function resizeHeading( $heading ){

	var $characters = $heading.text().trim().length;

	if ( ($characters >= 0) && ($characters < 30) ) {
		$heading.addClass('characters-low');
	}
	else if ( ($characters >= 30) && ($characters < 70) ) {
		$heading.addClass('characters-medium');
	}
	else {
		$heading.addClass('characters-high');
	} 

}





export { singlePost };