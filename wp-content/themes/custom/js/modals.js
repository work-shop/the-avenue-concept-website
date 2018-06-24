'use strict';

var modalProperties = {};

function modals( config ) {
	console.log('modals.js loaded');

	modalProperties.modalClass = config.modalClass || 'modal';
	modalProperties.modalToggleClass = config.modalToggleClass || 'modal-toggle';
	modalProperties.modalCloseClass = config.modalCloseClass || 'modal-close';
	modalProperties.modalOnBodyClass = config.modalOnBodyClass || 'modal-on';
	modalProperties.modalOffBodyClass = config.modalOffBodyClass || 'modal-off';

	$( document ).ready( function() {

		$( '.' + modalProperties.modalCloseClass ).click(function(e){
			e.preventDefault();
			closeModal();	
		});

		$('.blanket').click(function(e){
			e.preventDefault();
			closeModal();	
		});			

		$( '.' + modalProperties.modalToggleClass ).click(function(e){
			e.preventDefault();
			var target = $(this).data('modal-target');
			modalToggle(target, false);	
		});

		$('.modal-swap').click(function(e){
			e.preventDefault();
			var target = $(this).data('modal-target');
			modalToggle(target,true);	
		});

	});

}


function modalToggle(_target, swap){

	var modalTarget = '#' + _target;

	if(swap){
		$('.' + modalProperties.modalClass).removeClass('on');
		$('.' + modalTarget).removeClass('off').addClass('on');
	}
	else{
		console.log('else');
		if( $('body').hasClass( modalProperties.modalOffBodyClass ) ){
			$(modalTarget).removeClass('off').addClass('on');
			$('body').removeClass( modalProperties.modalOffBodyClass ).addClass( modalProperties.modalOnBodyClass );
		}	
	}

}


function closeModal(){

	if($('body').hasClass( modalProperties.modalOnBodyClass )){
		$( '.' + modalProperties.modalClass ).removeClass('on').addClass('off');
		$('body').removeClass( modalProperties.modalOnBodyClass ).addClass( modalProperties.modalOffBodyClass );
	}

}


export { modals };