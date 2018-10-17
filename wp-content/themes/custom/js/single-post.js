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

	// var $characters = $heading.text().length;

	// if (($characters >= 1) && ($characters < 20)) {
	// 	if( window.width() > 767 ){
	// 		$heading.css('font-size', '5vw');
	// 	} else{
			
	// 	}
	// }
	// else if (($characters >= 20) && ($characters < 30)) {
	// 	$heading.css('font-size', '4vw');
	// }
	// else if (($characters >= 30) && ($characters < 40)) {
	// 	$heading.css('font-size', '3vw');
	// }
	// else if (($characters >= 40) && ($characters < 50)) {
	// 	$heading.css('font-size', '2vw');
	// }
	// else {
	// 	$heading.css('font-size', '2vw');
	// }    

}





export { singlePost };