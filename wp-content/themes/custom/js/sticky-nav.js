"use strict";

var stickyNavProperties = {
	offset : {},
	triggerPosition : {}
};

function stickyNav( config ) {
	//console.log("sticky-nav.js loaded");

	$(document).ready( function() {

		stickyNavProperties.selector = config.selector || '#nav';
		stickyNavProperties.navHeight = config.navHeight || 75;
		stickyNavProperties.mobileNavHeight = config.mobileNavHeight || 50;
		stickyNavProperties.element = $(stickyNavProperties.selector);
		stickyNavProperties.mobileBreakpoint = config.mobileBreakpoint;
		stickyNavProperties.activeOnMobile = config.activeOnMobile;

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
	stickyNavProperties.triggerPosition = 20;
}


function checkNavPosition(){
	
	if( $(window).width() > stickyNavProperties.mobileBreakpoint || stickyNavProperties.activeOnMobile ){

		var footerTrigger = $('#footer').offset().top - $(window).height();

		if ( $(window).scrollTop() >= stickyNavProperties.triggerPosition && stickyNavProperties.element.hasClass('static') ){
			toggleNav();
		}else if($(window).scrollTop() < stickyNavProperties.triggerPosition && stickyNavProperties.element.hasClass('fixed') ){
			toggleNav();
		}

		if( $(window).scrollTop() >= footerTrigger && stickyNavProperties.element.hasClass('shown') ){
			stickyNavProperties.element.addClass('hidden');		
		} else if( $(window).scrollTop() < footerTrigger && stickyNavProperties.element.hasClass('hidden') ){
			stickyNavProperties.element.removeClass('hidden');
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

function hideNav(){

	if ( stickyNavProperties.element.hasClass('hidden') ){
		stickyNavProperties.element.removeClass('hidden');
	}else {
		stickyNavProperties.element.addClass('hidden');		
	}	

}


export { stickyNav };