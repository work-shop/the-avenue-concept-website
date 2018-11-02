'use strict';

var stickyNavProperties = {
	offset : {},
	triggerPosition : {}
};

function stickyNav( config ) {
	//console.log("sticky-nav.js loaded");

	$(document).ready( function() {

		if( $('.page-nav').length ){
			//console.log('page nav present');

			stickyNavProperties.selector = config.selector || '#nav';
			stickyNavProperties.navHeight = config.navHeight || 75;
			stickyNavProperties.mobileNavHeight = config.mobileNavHeight || 50;
			stickyNavProperties.element = $(stickyNavProperties.selector);
			stickyNavProperties.mobileBreakpoint = config.mobileBreakpoint;
			stickyNavProperties.activeOnMobile = config.activeOnMobile;

			calculatePositions();

			$('body').on({ 'touchmove': function() { 
				window.requestAnimationFrame(checkNavPosition); } 
			});

			$( window ).scroll( function() {
				window.requestAnimationFrame(checkNavPosition);
			});

			$( window ).resize( function() {
				window.requestAnimationFrame(calculatePositions);
				window.requestAnimationFrame(checkNavPosition);
			});	

			setTimeout(function() {
				window.requestAnimationFrame(checkNavPosition); 
			}, 200);

			setTimeout(function() {
				window.requestAnimationFrame(calculatePositions); 
			}, 3000);

		}

	});

}


function calculatePositions(){
	stickyNavProperties.offset = stickyNavProperties.element.offset();
	stickyNavProperties.triggerPosition = stickyNavProperties.offset.top - 90;
}


function checkNavPosition(){
	
	if( $(window).width() > stickyNavProperties.mobileBreakpoint || stickyNavProperties.activeOnMobile ){

		//var footerTrigger = $('#footer').offset().top - $(window).height();

		if ( $(window).scrollTop() >= stickyNavProperties.triggerPosition && stickyNavProperties.element.hasClass('before') ){
			toggleNav();
		}else if($(window).scrollTop() < stickyNavProperties.triggerPosition && stickyNavProperties.element.hasClass('after') ){
			toggleNav();
		}

		// if( $(window).scrollTop() >= footerTrigger && stickyNavProperties.element.hasClass('shown') ){
		// 	stickyNavProperties.element.addClass('hidden');		
		// } else if( $(window).scrollTop() < footerTrigger && stickyNavProperties.element.hasClass('hidden') ){
		// 	stickyNavProperties.element.removeClass('hidden');
		// }

	}

}


function toggleNav(){

	if ( stickyNavProperties.element.hasClass('before') ){
		stickyNavProperties.element.removeClass('before').addClass('after');
		$('body').addClass('sticky-nav-after');
	}else if( stickyNavProperties.element.hasClass('after') ){
		stickyNavProperties.element.removeClass('after').addClass('before');
		$('body').removeClass('sticky-nav-after');			
	}	

}

function hideNav(){

	if ( stickyNavProperties.element.hasClass('hidden') ){
		stickyNavProperties.element.removeClass('hidden');
	}else {
		stickyNavProperties.element.addClass('hidden');		
	}	

}


export { stickyNav };