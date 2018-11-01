'use strict';

function dropdowns( config ) {
	//console.log('dropdowns.js loaded');

	$(document).ready( function() {

		var dropdownDelay = 200, timer;

		$( config.linkSelector ).hover(
			function() {
				if( window.innerWidth > 767 ){
					var currentLink = $(this);
					timer = setTimeout(function() {
						openDropdown( currentLink );
					}, dropdownDelay);
				}
			}, function() {
				if( window.innerWidth > 767 ){
					var currentLink = $(this);
					clearTimeout(timer);
					closeDropdown(currentLink);
				}
			}
			);

		$( '.dropdown-link' ).click(function(e) {
			if( window.innerWidth <= 767 ){
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

		if( $(link).hasClass('closed') ){
			$(link).removeClass('closed').addClass('open');
			if( window.innerWidth > 767 ){
				$('body').removeClass(config.bodyOffClass).addClass(config.bodyOnClass);
			} else{
				$('body').removeClass('mobile-dropdown-off').addClass('mobile-dropdown-on');
			}
		}

	}	

	//close the dropdown
	function closeDropdown(link){
		//console.log(link);

		if( $(link).hasClass('open') ){
			$(link).removeClass('open').addClass('closed');	
			if( window.innerWidth > 767 ){
				$('body').removeClass(config.bodyOnClass).addClass(config.bodyOffClass);
			}	
		}
	}


	//open the mobile dropdown
	function openMobileDropdown( link ){
		//console.log(link);

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

		var item = link.parent('.has-sub-menu');

		if( $(item).hasClass('open') ){
			$(link).removeClass('mobile-open').addClass('mobile-closed');
			$(item).removeClass('open').addClass('closed');	
			$('body').removeClass('mobile-dropdown-on').addClass('mobile-dropdown-off');
		}
	}


		//open the mobile dropdown
	function showCurve(){
		//console.log('showCurve');

		if( $('body').hasClass('curve-off') ){
			$('body').removeClass('curve-off').addClass('curve-on');
		}

	}	

	//close the mobile dropdown
	function hideCurve(){
		//console.log('hideCurve');

		if( $('body').hasClass('curve-on') ){
			$('body').removeClass('curve-on').addClass('curve-off');
		}
	}



}

export { dropdowns };
