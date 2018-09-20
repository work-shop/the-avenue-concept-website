'use strict';

function livereload() {
    if ( window.location.href.indexOf('localhost') !== -1 ) {
        document.write('<script src="http://' + (location.host || 'localhost').split(':')[0] + ':35729/livereload.js?snipver=1"></' + 'script>');
        //console.log( 'livereload written.' );
    } else {
        //console.log( 'livereload ignored.' );
    }

}

export { livereload };