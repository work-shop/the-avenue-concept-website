'use strict';

global.$ = require('jquery');
global.jQuery = global.$;
window.$ = global.$;

import { config } from './config.js';
import { loading } from './loading.js';
//import { viewportLabel } from './viewport-label.js';
import { linksNewtab } from './links-newtab.js';
import { dropdowns } from './dropdowns.js';
import { navCurve } from './nav-curve.js';
import { jqueryAccordian } from './jquery-accordian.js';
import { accordian } from './accordian.js';
import { clickyNav } from './clicky-nav.js';
import { stickyNav } from './sticky-nav.js';
import { jumpLinks } from './jump-links.js';
import { modals } from './modals.js';
import { scrollSpy } from './scroll-spy.js';
import { menuToggle } from './menu-toggle.js';
import { slickSlideshows } from './slick-slideshows.js';
import { filterBlog } from './filter-blog.js';
import { singlePost } from './single-post.js';
import { announcements } from './announcements.js';
import { paypal } from './paypal.js';
import { livereload } from './livereload-client.js';
import { membershipLevels } from './membership-levels.js';
import { navHighlight } from './nav-highlight.js';
import { donate } from './donate.js';
import { filter } from './filter.js';

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
navCurve();
jqueryAccordian();
accordian();
clickyNav(config.clickyNav);
stickyNav(config.stickyNav);
jumpLinks(config.jumpLinks);
modals(config.modals);
scrollSpy(config.scrollSpy);
menuToggle(config.menuToggle);
slickSlideshows(config.slickSlideshows);
filterBlog();
singlePost();
announcements();
paypal();
navHighlight();
donate();
filter();

$( document ).ready( function() {

	if ( isHomePage() ) {
		homePage();
	} else if ( isArtworksArchive() ) {
		artworksArchive();
	} else if ( isArtworksSingle() ) {
		singleArtwork();
	} else if ( $('body').hasClass('page-id-1254') ){
		membershipLevels();
	} 

});
