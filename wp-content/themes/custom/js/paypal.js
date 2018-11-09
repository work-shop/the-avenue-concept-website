'use strict';

function paypal() {
	//console.log('paypal.js loaded');

	var endpoint = 'https://pilot-payflowpro.paypal.com';
	var partner = 'Paypal';
	var vendor = 'avenuepvd';
	var user = 'workshop';
	var pwd = 'Cmi!!2012';
	var trxtype = 'S';
	var amt = 40;
	var createsecuretoken = 'Y';
	var securetokenid = '12528208de1413abc3d60c86cb14';

	var postData = {
		PARTNER : partner,
		VENDOR : vendor,
		USER : user,
		PWD : pwd,
		TRXTYPE : trxtype,
		AMT : amt,
		CREATESECURETOKEN : createsecuretoken,
		SECURETOKENID : securetokenid
	};


	$(document).ready( function() {

		if( $('body').hasClass('page-id-189') ){

			// $.ajax({
			// 	url: endpoint,
			// 	type: 'POST',
			// 	data: postData
			// })
			// .done(function() {
			// 	console.log('success');
			// 	console.log(data);
			// })
			// .fail(function() {
			// 	console.log('error');
			// })
			// .always(function() {
			// 	console.log('complete');
			// });

		}

	});

}

export { paypal }
