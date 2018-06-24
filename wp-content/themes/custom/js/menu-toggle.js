"use strict";

function menuToggle( config ) {
	console.log('menu-toggle.js loaded');

	$(document).ready( function() {
		$(config.menuToggleSelector).click(function(e) {
			e.preventDefault();
			menuToggle();
		});				
		
	});

	//open and close the menu
	function menuToggle(){

		if($('body').hasClass(config.bodyOffClass)){
			$(config.menuToggleSelector).removeClass('closed').addClass('open');
			$(config.blanketSelector).removeClass('off').addClass('on');						
			$('body').removeClass(config.bodyOffClass).addClass(config.bodyOnClass);
		}
		else if($('body').hasClass(config.bodyOnClass)){
			$(config.menuToggleSelector).removeClass('open').addClass('closed');
			$(config.blanketSelector).removeClass('on').addClass('off');			
			$('body').removeClass(config.bodyOnClass).addClass(config.bodyOffClass);
		}

	}	

}

export { menuToggle };
