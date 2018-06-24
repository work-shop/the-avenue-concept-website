"use strict";

var stickyNavProperties = {
	offset : {},
	triggerPosition : {}
};

function stickyNav( config ) {
	console.log("sticky-nav.js loaded");

	stickyNavProperties.selector = config.selector || '#nav';
	stickyNavProperties.navHeight = config.navHeight || 75;
	stickyNavProperties.mobileNavHeight = config.mobileNavHeight || 50;
	stickyNavProperties.element = $(stickyNavProperties.selector);
	stickyNavProperties.mobileBreakpoint = config.mobileBreakpoint;
	stickyNavProperties.activeOnMobile = config.activeOnMobile;

	$(document).ready( function() {

		calculatePositions();

		$('body').on({ 'touchmove': function(e) { 
			window.requestAnimationFrame(checkNavPosition); } 
		});

		$( window ).scroll( function() {
			window.requestAnimationFrame(checkNavPosition);
		});

		$( window ).resize( function() {
			window.requestAnimationFrame(calculatePositions);
			window.requestAnimationFrame(checkNavPosition);
		});	

	});

}


function calculatePositions(){

	stickyNavProperties.offset = stickyNavProperties.element.offset();
	stickyNavProperties.triggerPosition = stickyNavProperties.offset.top;

}


function checkNavPosition(){
	
	if( $(window).width() > stickyNavProperties.mobileBreakpoint || stickyNavProperties.activeOnMobile ){
				
		if ( $(window).scrollTop() >= stickyNavProperties.triggerPosition && stickyNavProperties.element.hasClass('static') ){
			toggleNav();
		}else if($(window).scrollTop() < stickyNavProperties.triggerPosition && stickyNavProperties.element.hasClass('fixed') ){
			toggleNav();
		}

	}

}


function toggleNav(){

	if ( stickyNavProperties.element.hasClass('static') ){
		stickyNavProperties.element.removeClass('static').addClass('fixed');
		$('body').addClass('sticky-nav-fixed');
	}else if( stickyNavProperties.element.hasClass('fixed') ){
		stickyNavProperties.element.removeClass('fixed').addClass('static');
		$('body').removeClass('sticky-nav-fixed');			
	}	

}


export { stickyNav };