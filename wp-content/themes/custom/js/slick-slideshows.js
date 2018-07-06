'use strict';

var slick = require ('slick-carousel');

function slickSlideshows( config ) {
	//console.log('slick-slideshows.js loaded');

	$( document ).ready( function() {

		$('.slick-default').slick({
			slidesToShow: config.slidesToShow,
			dots: config.dots,
			arrows: config.arrows,
			autoplay: config.autoplay,
			fade: config.fade,
			autoplaySpeed: config.autoplaySpeed,
			speed: config.speed
		});

		$('.slick-home').slick({
			slidesToShow: config.slidesToShow,
			dots: false,
			arrows: true,
			autoplay: config.autoplay,
			fade: false,
			autoplaySpeed: config.autoplaySpeed,
			speed: config.speed
		});

	});

}


export { slickSlideshows };
