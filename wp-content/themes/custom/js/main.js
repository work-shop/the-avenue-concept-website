'use strict';

global.$ = require('jquery');
global.jQuery = global.$;
window.$ = global.$;

import { config } from './config.js';
import { loading } from './loading.js';
import { viewportLabel } from './viewport-label.js';
import { linksNewtab } from './links-newtab.js';
//import { dropdowns } from './dropdowns.js';
//import { stickyNav } from './sticky-nav.js';
import { jumpLinks } from './jump-links.js';
import { modals } from './modals.js';
import { scrollSpy } from './scroll-spy.js';
import { menuToggle } from './menu-toggle.js';
import { slickSlideshows } from './slick-slideshows.js';
import { livereload } from './livereload-client.js';

livereload();

loading(config.loading);
linksNewtab(config.linksNewtab);
viewportLabel(config.viewportLabel);
//dropdowns(config.dropdowns);
//stickyNav(config.stickyNav);
jumpLinks(config.jumpLinks);
modals(config.modals);
scrollSpy(config.scrollSpy);
menuToggle(config.menuToggle);
slickSlideshows(config.slickSlideshows);

console.log('main.js loaded, with gulp!');
