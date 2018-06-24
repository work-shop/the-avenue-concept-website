'use strict';

function viewportLabel( config ) {
	console.log('viewport-label.js loaded');

    var timeout = false, // holder for timeout id
    delay = 50, // delay after event is "complete" to run callback
    w = 0,
    viewportLabelPx = $(config.viewportLabelPxSelector);


	$( document ).ready( function() {

		viewportLabelUpdate();

	});


	window.addEventListener('resize', function() {
		clearTimeout(timeout);
		timeout = setTimeout(viewportLabelUpdate, delay);
	});
	


	//update the viewport label
	function viewportLabelUpdate(){
		w = $(window).width();
		viewportLabelPx.text(w);
	}


}

export { viewportLabel };
