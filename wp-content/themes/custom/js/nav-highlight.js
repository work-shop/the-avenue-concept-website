'use strict';


function navHighlight( config ) {
	//console.log('nav-highlight.js loaded');

	$(document).ready( function() {

		var str = window.location.href.split(window.location.host);
		var currentUrl = str[1];
		//console.log('currentUrl: ' + currentUrl);

		var selector = '#page-nav a[href$="' + currentUrl + '"]';
		//console.log('selector: ' + selector);

		var activeLink = $(selector);
		//console.log(activeLink.attr('href'));
		activeLink.addClass('active');

	});

}

export { navHighlight };
