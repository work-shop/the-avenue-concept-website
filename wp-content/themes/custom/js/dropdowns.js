'use strict';

function dropdowns( config ) {
	//console.log('dropdowns.js loaded');

	$(document).ready( function() {

		var dropdownDelay = 10, timer;

		$( config.linkSelector ).hover(
			function() {
				if( $(window).width() > 767 ){
					var currentLink = $(this);
					timer = setTimeout(function() {
						openDropdown( currentLink );
					}, dropdownDelay);
				}
			}, function() {
				if( $(window).width() > 767 ){
					var currentLink = $(this);
					clearTimeout(timer);
					closeDropdown(currentLink);
				}
			}
			);

		$( config.linkSelector ).click(function(e) {
			if( $(window).width() <= 767 ){
				e.preventDefault();
				var currentLink = $(this);
				if( currentLink.hasClass('closed') ){
					openDropdown( currentLink );
				}else{
					closeDropdown(currentLink);
				}
			}
		});

		$(config.blanketSelector).click(function(){
			closeDropdown();
		});

	});

	//open the dropdown
	function openDropdown( link ){
		console.log('openDropdown');

		if( $(link).hasClass('closed') ){
			$(link).removeClass('closed').addClass('open');
			if( $(window).width() > 767 ){
				$('body').removeClass(config.bodyOffClass).addClass(config.bodyOnClass);
			}
		}

	}	

	//close the dropdown
	function closeDropdown(link){
		//console.log(link);
		//console.log('closeDropdown');

		if( $(link).hasClass('open') ){
			//console.log('link open');
			$(link).removeClass('open').addClass('closed');	
			if( $(window).width() > 767 ){
				//console.log('breakpoint');
				$('body').removeClass(config.bodyOnClass).addClass(config.bodyOffClass);
			}	
		}
	}

}

export { dropdowns };
