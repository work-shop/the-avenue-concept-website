'use strict';


function loading( config ){

	console.log('loading.js loaded');

	$( document ).ready( function() {
		setTimeout(function(){
			$( '.' + config.loadingClass ).addClass( config.loadedClass );
		}, config.loadDelay );
	});

}


export { loading };