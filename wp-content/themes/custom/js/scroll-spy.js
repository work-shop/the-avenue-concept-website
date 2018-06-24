"use strict";

//this spy function is goverened by the targets, and if there is a corresponding link, it will become active


var scrollSpyProperties = {};

var activated = false;
var firstPass = true;

function scrollSpy( config ){

	console.log('scroll-spy.js loaded');

	$(window).on('load', function() {		

		scrollSpyProperties.targets = $(config.spyTargetSelector);
		scrollSpyProperties.links = $(config.spyLinkSelector);
		scrollSpyProperties.offset = config.spyOffset;
		scrollSpyProperties.spyActiveClass = config.spyActiveClass;

		if( config.firstElementSelector ){
			scrollSpyProperties.startElement = $(config.firstElementSelector);
			scrollSpyProperties.startElement.addClass('active');
			scrollSpyProperties.currentElement = scrollSpyProperties.startElement;
		}else{
			scrollSpyProperties.currentElement = scrollSpyProperties.targets[0];
		}		

		scrollSpyProperties.spyMap = [];

		update();

		setTimeout(function() {	update(); setTimeout(function() { update(); }, 5000); }, 2500);

	});

}



function update(){

	//console.log('scrollSpyProperties update');

	scrollSpyProperties.targets.each(function( i ) {

		//create an object to store the necessary data for this target
		scrollSpyProperties.spyMap[i] = {};
		scrollSpyProperties.spyMap[i].target = $(this); 
		var offset = $(this).offset();
		scrollSpyProperties.spyMap[i].targetOffset = Math.round(offset.top);


		//take the ID of this target element, and see if there is a link that matches it

		//if there is a link that pairs with this target, store that as well
		var elementId = $(this).attr('id');
		var link = checkLinks(elementId);

		if( link !== undefined ){
			scrollSpyProperties.spyMap[i].hasLink = true;
			scrollSpyProperties.spyMap[i].link = link;
		} else{
			scrollSpyProperties.spyMap[i].hasLink = false;
		}


		// console.log('target:');
		// console.log('#' + scrollSpyProperties.spyMap[i].target.attr('id') + ': ' + scrollSpyProperties.spyMap[i].targetOffset);
		// console.log('targetOffset: ' + scrollSpyProperties.spyMap[i].targetOffset);
		// console.log('hasLink: ' + scrollSpyProperties.spyMap[i].hasLink);
		// console.log('link: ' + scrollSpyProperties.spyMap[i].link);

	});

	spy();

}


function checkLinks( targetId ){

	var link;

	scrollSpyProperties.links.each(function( j ){

		var linkHref = $(this).attr('href');
		var linkId = linkHref.replace('#','');

		if( linkId === targetId){
			link = $(this);
		}

	});

	if( link ){
		return link;
	} else{
		return;
	}

}


function spy(){

	var nElements = scrollSpyProperties.spyMap.length;

	for(var i = 0; i < nElements; i++ ){

		var userLocation, targetOffsetPosition, tolerance, targetPosition, nextTargetOffsetPosition, nextTargetPosition;

		userLocation = $(window).scrollTop() + scrollSpyProperties.offset;

		targetOffsetPosition = scrollSpyProperties.spyMap[i].targetOffset;
		//tolerance = ($(window).height() - scrollSpyProperties.offset) / 2;
		tolerance = scrollSpyProperties.offset;
		targetPosition = targetOffsetPosition - tolerance;

		if( i < (nElements - 1) ){
			nextTargetOffsetPosition = scrollSpyProperties.spyMap[i+1].targetOffset;
			nextTargetPosition = nextTargetOffsetPosition - tolerance;
		}

		//if the user's window.scrollTop is greater than or equal to the offsetTop of the element we're currently checking AND it's not the last targetable element OR the user's window.scrollTop is less than the next element then we think this element should be active
		if( userLocation >= targetPosition && ( ( i === nElements - 1 ) || (userLocation < nextTargetPosition) ) || firstPass ) {

			//if the element we think should be active is not the current element
			if(scrollSpyProperties.currentElement !== (scrollSpyProperties.spyMap[i].target) || firstPass ){

				if( firstPass ){
					firstPass = false;
				}

				//console.log('targetOffset: ' + scrollSpyProperties.spyMap[i].targetOffset);
				//console.log( 'targetPosition: ' + targetPosition );

				scrollSpyProperties.currentElement.removeClass(scrollSpyProperties.spyActiveClass);

				scrollSpyProperties.spyMap[i].target.addClass(scrollSpyProperties.spyActiveClass);
				scrollSpyProperties.spyMap[i].target.addClass('activated');

				if( scrollSpyProperties.spyMap[i].hasLink ){

					scrollSpyProperties.links.filter(scrollSpyProperties.spyActiveClass).removeClass(scrollSpyProperties.spyActiveClass);
					scrollSpyProperties.spyMap[i].link.addClass(scrollSpyProperties.spyActiveClass);
					//scrollSpyProperties.spyMap[i].link.parent.addClass('active');

				}

				scrollSpyProperties.currentElement = scrollSpyProperties.spyMap[i].target;

			}

		}

	}

	if( activated === false ){
		activate();
		activated = true;
	}

}


function activate(){

	var spyTrigger = debounce(function() {
		window.requestAnimationFrame(spy);
		//console.log('scrollTop: ' + $(window).scrollTop());
	}, 10);

	var spyUpdate = debounce(function() {
		window.requestAnimationFrame(update);	
	}, 50);		

	window.addEventListener('scroll', spyTrigger);
	window.addEventListener('resize', spyUpdate);

}


// Returns a function, that, as long as it continues to be invoked, will not be triggered. The function will be called after it stops being called for N milliseconds. If `immediate` is passed, trigger the function on the leading edge, instead of the trailing.
function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
}




export { scrollSpy };