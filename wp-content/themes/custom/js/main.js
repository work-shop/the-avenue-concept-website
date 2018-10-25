'use strict';

global.$ = require('jquery');
global.jQuery = global.$;
window.$ = global.$;

import { config } from './config.js';
import { loading } from './loading.js';
//import { viewportLabel } from './viewport-label.js';
import { linksNewtab } from './links-newtab.js';
import { dropdowns } from './dropdowns.js';
import { jqueryAccordian } from './jquery-accordian.js';
import { accordian } from './accordian.js';
import { clickyNav } from './clicky-nav.js';
import { stickyNav } from './sticky-nav.js';
import { jumpLinks } from './jump-links.js';
import { modals } from './modals.js';
import { scrollSpy } from './scroll-spy.js';
import { menuToggle } from './menu-toggle.js';
import { slickSlideshows } from './slick-slideshows.js';
import { filter } from './filter.js';
import { singlePost } from './single-post.js';
import { announcements } from './announcements.js';
import { livereload } from './livereload-client.js';

/**
 * Artwork related imports
 */
 import { isHomePage, homePage } from './page-home-artworks.js';
 import { isArtworksSingle, singleArtwork } from './page-single-artworks.js';
 import { isArtworksArchive, artworksArchive } from './page-archive-artworks.js';

 livereload();

 loading(config.loading);
 linksNewtab(config.linksNewtab);
//viewportLabel(config.viewportLabel);
dropdowns(config.dropdowns);
jqueryAccordian();
accordian();
clickyNav(config.clickyNav);
stickyNav(config.stickyNav);
jumpLinks(config.jumpLinks);
modals(config.modals);
scrollSpy(config.scrollSpy);
menuToggle(config.menuToggle);
slickSlideshows(config.slickSlideshows);
filter();
singlePost();
announcements();

$.ajax({
	crossDomain: true,
	url : 'http://staging-theavenueconcept.kinsta.com/wp-json/zoho/v1/artworks',
	method: 'GET',
	success : function( data ) {
		console.log(data);
		// var temp = data[1].split('var zohothe_avenue_conceptview45 = ');
		// temp = temp[1].slice(0,-1);
		// temp = JSON.parse(temp);
		// console.log(temp);
	},
	error: function( e ) {
		console.error( e );
	}
});

$( document ).ready( function() {

	if ( isHomePage() ) {

		homePage();

	} else if ( isArtworksArchive() ) {

		artworksArchive();

	} else if ( isArtworksSingle() ) {

		singleArtwork();

	}

});
