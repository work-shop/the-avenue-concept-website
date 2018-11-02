'use strict';

function filter() {
	//console.log('filter.js loaded');

	var categoryFiltered = false;
	var categoryFilteredCurrent = 'all';

	$(document).ready( function() {

		//if( $('.filters').length ){

			var filterClassStart = 'all';
			var urlVars = getUrlVars();
			var urlCategory = urlVars.category;
			if( !isEmpty(urlCategory) ){
				console.log('urlCategory: ' + urlCategory);
				var categoryButtonSelector = '.filter-button[data-target=filter-' + urlCategory + ']';
				var categoryButtonCheck = $(categoryButtonSelector);
				if( !isEmpty(categoryButtonCheck) ){
					$(categoryButtonSelector).addClass('filter-active');
					filterClassStart = 'filter-' + urlVars.category;
				}
			}
			filterCategories(filterClassStart);

			$('.filter-button-category').click(function(e) {
				e.preventDefault();
				if( $(this).hasClass('filter-active') ){
					categoryFiltered = false;
					categoryFilteredCurrent = 'all';
					filterCategories('all');
					$(this).removeClass('filter-active');
				} else{
					scrollToFilter();
					var filterClass = $(this).data('target');
					filterCategories(filterClass);
					filterButtonActivate( $(this), 'categories' );
				}
			});	

		//}

	});// end document.ready


	function filterCategories(filterClass) {
		//console.log('filterCategories: ' + filterClass);
		clearFilterMessages();

		if( filterClass !== 'all'){
			categoryFiltered = true;
			categoryFilteredCurrent = filterClass;
		} else{
			filterButtonActivate( $('#filter-button-all') );
		}

		var elements = $('.filter-target');
		var newElements = getElementsByCategory( elements, filterClass );
		updateElements(newElements);

	}


	function getElementsByCategory( elements, filterClass ){
		//console.log('getElementsByCategory with filterClass: ' + filterClass);
		var newElements = [];

		$.each(elements, function(index, val) {
			var element = $(val);
			if( element.hasClass(filterClass) || filterClass === 'all' ){
				////console.log(element);
				newElements.push(element);
			}
		});

		return newElements;
	}


	function updateElements(newElements){
		//console.log('updateElements');
		var elementsFound = false;
		hideElements();

		$.each(newElements, function(index, val) {
			var element = $(val);
			element.addClass('filter-show');
			elementsFound = true;
		});

		if( !elementsFound ){
			//console.log('no elements found');
			$('#filter-messages').addClass('filter-show');
		}
	}


	function hideElements(){
		var elements = $('.filter-target');
		elements.removeClass('filter-show');
	}


	function clearFilterMessages(){
		$('#filter-messages').removeClass('filter-show');
	}


	function filterButtonActivate(button){
		$('.filters .filter-active').removeClass('filter-active');
		button.addClass('filter-active');		
	}


	function scrollToFilter(){
		var offset = 150;
		$('html,body').animate({
			scrollTop: $('.filters').offset().top - offset
		}, 100);
	}


	function isEmpty(val){
		return (val === undefined || val === null || val.length <= 0) ? true : false;
	}


	// Read a page's GET URL variables and return them as an associative array.
	function getUrlVars(){
		var vars = [], hash;
		var url = stripTrailingSlash(window.location.href);
		var hashes = url.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++){
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}


	function stripTrailingSlash(url){
		return url.replace(/\/$/, "");
	}


}


export { filter };
