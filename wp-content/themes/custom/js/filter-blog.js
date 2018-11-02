'use strict';

var baseUrl = 'https://theavenueconcept.org/wp-json/wp/v2/';
var pagination = 1;
var currentCategory = 'all';
var updating = false;
var full = false;

function filterBlog() {
	//console.log('filter-blog.js loaded');

	var categoryFiltered = false;
	var categoryFilteredCurrent = 'all';

	$(document).ready( function() {

		if( $('body').hasClass('page-id-193') ){

			//initialization

			var categoryStart;
			var urlVars = getUrlVars();
			var urlCategory = urlVars.category;

			if( !isEmpty(urlCategory) ){
				var categoryButtonSelector = '.filter-button[data-target=' + urlCategory + ']';
				var categoryButtonCheck = $(categoryButtonSelector);
				if( !isEmpty(categoryButtonCheck) ){
					$(categoryButtonSelector).addClass('filter-active');
					categoryStart = urlVars.category;
				}
			} else{
				categoryStart = 'all';
			}

			filterCategories(categoryStart);
			
			if( categoryStart !== 'all'){
				updateUrl(categoryStart);
			}



			//events

			$('.filter-button-category').click(function(e) {
				e.preventDefault();
				var category;

				if( $(this).hasClass('filter-active') ){
					categoryFiltered = false;
					categoryFilteredCurrent = 'all';
					category = 'all';
					filterCategories('all');
					$(this).removeClass('filter-active');
				} else{
					scrollToFilter();
					category = $(this).data('target');
					filterCategories(category);
					filterButtonActivate( $(this), 'categories' );
				}
				updateUrl(category);
			});	

			window.addEventListener('popstate', function(e) {
	  			// e.state is equal to the data-attribute of the last image we clicked
	  			console.log(e.state);

	  			if( e.state == null ){

	  			} else{
	  				var category = e.state.category;
	  				var categoryButtonSelector = '.filter-button[data-target=' + category + ']';
	  				var categoryButtonCheck = $(categoryButtonSelector);
	  				if( !isEmpty(categoryButtonCheck) ){
	  					$('.filter-button-category').removeClass('filter-active');
	  					$(categoryButtonSelector).addClass('filter-active');
	  				}  			
	  				filterCategories( category );	
	  			}

	  		});

			$( window ).scroll( function() {
				window.requestAnimationFrame(checkPosition);
			});

		}


	});// end document.ready


	function filterCategories( category ) {
		console.log('filterCategories: ' + category);
		currentCategory = category;
		pagination = 1;
		
		clearFilterMessages();
		$('.post').remove();

		if( category !== 'all'){
			categoryFiltered = true;
			categoryFilteredCurrent = category;
			getPostsByCategory( category );
		} else{
			filterButtonActivate( $('#filter-button-all') );
			getPosts( 'all' );
		}

	}


	function getPostsByCategory( slug ){
		//console.log('getCategoryID');

		var categoryEndpoint = baseUrl + 'categories?slug=' + slug;
		var categoryID;

		$.ajax({
			url: categoryEndpoint,
			dataType: 'json'
		})
		.done(function(data) {
			//console.log('successful request for category');
			//console.log(data);
			if( data.length === 1 ){
				//console.log('categoryID in getCategoryID: ' + categoryID );
				categoryID = data[0].id;
				getPosts( categoryID );
			} else{
				console.log('more than one category found with this slug, or some other array problem');
				getPosts( 'all' );
				throwError();
			}
		})
		.fail(function() {
			console.log('error getting category from API');
			throwError();
		})
		.always(function() {
			//console.log('completed request for category');
		});
	}


	function getPosts( categoryID, page ){
		//console.log('getPosts with category: ' + category);

		if ( isEmpty(page) ){
			page = 1;
		} 

		var endpoint;

		if ( !isEmpty(categoryID) ){

			if(categoryID === 'all'){
				endpoint = baseUrl + 'posts?_embed&page=' + page + '&per_page=10';
			}else{
				endpoint = baseUrl + 'posts?categories=' + categoryID + '&_embed&page=' + page + '&per_page=10';
			}

			$.ajax({
				url: endpoint,
				dataType: 'json'
			})
			.done(function(data) {
				//console.log('successful request for posts');
				//console.log(data);
				renderPosts(data);
			})
			.fail(function() {
				console.log('error loading posts from API');
				throwError();
			})
			.always(function() {
				//console.log('completed request for posts');
			});

		} else{
			console.log('no category all or categoryID supplied to get posts');
		}

	}


	function renderPosts( posts ){
		//console.log('renderPosts');

		var postsFound = false;
		hideElements();

		if( !isEmpty(posts) ){
			postsFound = true;
			for (var i = 0; i < posts.length; i++) {
				renderPost( posts[i] );
			}
			setTimeout(function() {
				updating = false;
			}, 500);
		}else{
			updating = false;
			//console.log('no posts in API response');
			$('#filter-messages').addClass('filter-show');
		}

	}



	function renderPost( post ){

		var root = $('<div>')
		.addClass('post');

		var card = $('<div>')
		.addClass('card')
		.addClass('card-post');

		var imageContainer = $('<div>')
		.addClass('card-image');

		var imageSrc;
		if( typeof post._embedded['wp:featuredmedia'][0].media_details.sizes.blog === 'undefined' ){
			if( typeof post._embedded['wp:featuredmedia'][0].source_url !== 'undefined' ){
				imageSrc = post._embedded['wp:featuredmedia'][0].source_url;
			}
		} else{
			imageSrc = post._embedded['wp:featuredmedia'][0].media_details.sizes.blog.source_url;
		}
		if( !isEmpty(imageSrc)){
			var image = $('<img>')
			.attr('src', imageSrc);
		}

		var link1 = $('<a>')
		.attr('href', post.link);

		var link2 = $('<a>')
		.attr('href', post.link)
		.html(post.title.rendered);

		var textContainer = $('<div>')
		.addClass('card-text');

		var date = $('<h5>')
		.addClass('font-main')
		.addClass('card-post-date')
		.addClass('mb0')
		.html(post.acf.post_date);

		var title = $('<h4>')
		.addClass('font-main')
		.addClass('card-post-title')
		.addClass('mt0')
		.addClass('mb1');

		var postCategories = $('<div>')
		.addClass('post-categories');
		var categories = post._embedded['wp:term'][0];

		for (var i = 0; i < categories.length; i++) {
			var html = categories[i].name;
			var href = '/blog/?category=' + categories[i].slug;
			if( i !== (categories.length - 1) ){
				html += ', ';
			}
			var link = $('<a>')
			.attr('href', href)
			.html(html);
			postCategories.append(link);
		}

		if( !isEmpty(imageSrc)){
			link1.append(image);
			imageContainer.append(link1);
			card.append(imageContainer);
		}
		title.append(link2);
		textContainer.append(date).append(title).append(postCategories);
		card.append(textContainer);
		root.append(card);

		$('.blog-posts-container').append(root);

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
		return ( typeof val === 'undefined' || val === null || val.length <= 0 ) ? true : false;
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

	function updateUrl(category){
		var stateObj = {
			category: category
		};
		if( category === 'all' ){
			history.pushState(stateObj, category, '/blog/' );
		} else{
			history.pushState(stateObj, category, '/blog/?category=' + category );
		}
		
	}


	function checkPosition(){

		var footerTrigger = $('#footer').offset().top - $(window).height();

		if( $(window).scrollTop() >= footerTrigger && updating === false ){
			console.log('footerTrigger');

			updating = true;
			pagination ++;
			getPosts(currentCategory, pagination);

		}
		// } else if( $(window).scrollTop() < footerTrigger && stickyNavProperties.element.hasClass('hidden') ){
		// 	stickyNavProperties.element.removeClass('hidden');
		// }

	}


	function stripTrailingSlash(url){
		return url.replace(/\/$/, "");
	}


	function throwError(){
		console.log('throwError');
	}


}


export { filterBlog };