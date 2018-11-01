'use strict';

function navCurve( ) {
	//console.log('nav-curve.js loaded');

	$(document).ready( function() {

		var dropdownDelay = 100, timer;

		$( $('#nav-menus') ).hover(
			function() {
				if( window.innerWidth > 767 ){
					timer = setTimeout(function() {
						showCurve();
					}, dropdownDelay);
				}
			}, function() {
				if( window.innerWidth > 767 ){
					clearTimeout(timer);
					hideCurve();
				}
			});

	});


	function showCurve(){
		//console.log('showCurve');

		if( $('body').hasClass('curve-off') ){
			$('body').removeClass('curve-off').addClass('curve-on');
		}

	}	

	function hideCurve(){
		//console.log('hideCurve');

		if( $('body').hasClass('curve-on') ){
			$('body').removeClass('curve-on').addClass('curve-off');
		}
	}



}

export { navCurve };
