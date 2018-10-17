'use strict';

function dropdowns( config ) {
	//console.log('dropdowns.js loaded');

	$(document).ready( function() {

		var dropdownDelay = 200, timer;

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

		$( '.dropdown-link' ).click(function(e) {
			if( $(window).width() <= 767 ){
				e.preventDefault();
				var currentLink = $(this);
				if( currentLink.hasClass('mobile-closed') ){
					openMobileDropdown( currentLink );
				}else{
					closeMobileDropdown(currentLink);
				}
			}
		});

		$(config.blanketSelector).click(function(){
			closeDropdown();
		});

	});

	//open the dropdown
	function openDropdown( link ){
		//console.log(link);
		//console.log('openDropdown');

		if( $(link).hasClass('closed') ){
			$(link).removeClass('closed').addClass('open');
			if( $(window).width() > 767 ){
				$('body').removeClass(config.bodyOffClass).addClass(config.bodyOnClass);
			} else{
				$('body').removeClass('mobile-dropdown-off').addClass('mobile-dropdown-on');
			}
		}

	}	

	//close the dropdown
	function closeDropdown(link){
		//console.log(link);
		//console.log('closeDropdown');

		if( $(link).hasClass('open') ){
			$(link).removeClass('open').addClass('closed');	
			if( $(window).width() > 767 ){
				$('body').removeClass(config.bodyOnClass).addClass(config.bodyOffClass);
			}	
		}
	}


	//open the mobile dropdown
	function openMobileDropdown( link ){
		//console.log(link);
		console.log('openDMobileropdown');

		var item = link.parent('.has-sub-menu');

		if( $(item).hasClass('closed') ){
			$(link).removeClass('mobile-closed').addClass('mobile-open');
			$(item).removeClass('closed').addClass('open');	
			$('body').removeClass('mobile-dropdown-off').addClass('mobile-dropdown-on');
		}

	}	

	//close the mobile dropdown
	function closeMobileDropdown(link){
		//console.log(link);
		console.log('closeMobileDropdown');

		var item = link.parent('.has-sub-menu');

		if( $(item).hasClass('open') ){
			$(link).removeClass('mobile-open').addClass('mobile-closed');
			$(item).removeClass('open').addClass('closed');	
			$('body').removeClass('mobile-dropdown-on').addClass('mobile-dropdown-off');
		}
	}

}

export { dropdowns };
