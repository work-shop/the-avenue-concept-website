'use strict';


function announcements() {
	//console.log('announcements.js loaded');

	$(document).ready( function() {

		$('#sitewide-alert-close').click(function(e) {
			e.preventDefault();
			$('#sitewide-alert').addClass('hidden');
			$('body').removeClass('sitewide-alert-on');
			var cookie = 'tac_show_sitewide_alert';
			var d = new Date();
			d.setHours(23,59,59,999);
			var expires = 'expires='+d.toUTCString();
			document.cookie = cookie + '=' + 'false' + ';' + expires + ';path=/';
		});

		$('#announcement-close').click(function(e) {
			e.preventDefault();
			$('#home-announcement').addClass('hidden');
			$('body').removeClass('announcement-on');
			//var cookie = 'tac_show_sitewide_alert';
			//var d = new Date();
			//d.setHours(23,59,59,999);
			//var expires = 'expires='+d.toUTCString();
			//document.cookie = cookie + '=' + 'false' + ';' + expires + ';path=/';
		});

	});

}

export { announcements };
