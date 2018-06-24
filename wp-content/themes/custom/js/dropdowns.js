'use strict';

function dropdowns( config ) {
	console.log('dropdowns.js loaded');

	$(document).ready( function() {

		var dropdownDelay = 180, timer;

		$( config.linkSelector ).hover(
			function() {
				var currentLink = $(this);
				timer = setTimeout(function() {
					openDropdown( currentLink );
				}, dropdownDelay);
			}, function() {
				clearTimeout(timer);
			}
			);

		$(config.blanketSelector).click(function(){
			closeDropdown();
		});

	});

	//open the dropdown
	function openDropdown( link ){
		console.log(link);
		var linkTarget = link.data('dropdown-target');
		//console.log(linkTarget);
		var dropdownTarget = 'menu[data-dropdown="' + linkTarget + '"]';
		//console.log(dropdownTarget);
		var dropdown = $(dropdownTarget);
		//console.log(dropdown);

		closeDropdown();

		if($('body').hasClass(config.bodyOffClass)){
			$(dropdown).removeClass('off').addClass('on');
			$(link).removeClass('off').addClass('on');
			$(config.blanketSelector).removeClass('off').addClass('on');						
			$('body').removeClass(config.bodyOffClass).addClass(config.bodyOnClass);
		}

	}	

	//close the dropdown
	function closeDropdown(){

		if($('body').hasClass(config.bodyOnClass)){
			$( config.linkSelector ).removeClass('on').addClass('off');
			$(config.dropdownSelector).removeClass('on').addClass('off');
			$(config.blanketSelector).removeClass('on').addClass('off');			
			$('body').removeClass(config.bodyOnClass).addClass(config.bodyOffClass);
		}

	}

}

export { dropdowns };
