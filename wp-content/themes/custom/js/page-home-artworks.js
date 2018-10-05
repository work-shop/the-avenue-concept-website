'use strict';

var makeMap = require('@work-shop/map-module');

import { ArtworkFilterer } from './module-filter-artworks.js';
import { ArtworkRenderer } from './module-render-artworks.js';

/**
 * file: page-home-artworks.js
 *
 * This file runs the state-management, artwork getting, and artwork rendering
 * for the /home page on the site.
 */

/**
 * This method returns true if the current page is the home page,
 * false otherwise.
 */
function isHomePage() { return $(document.body).hasClass('home'); }

/**
 * This class manages getting and rendering artworks on the home page.
 */
function HomePageArtworksManager() {
    console.log('HomePageArtworksManager loaded');
    if ( !(this instanceof HomePageArtworksManager)) { return new HomePageArtworksManager(); }


}


/**
 *
 *
 */

 function drawMap( mapData ) {

     const map = makeMap({
         selector: '#home-map-container',
         map: { streetViewControl: false }
     })[0];

     map.data( mapData ).removeFeatures().render();

 }

/**
 * This method sets up the home page artworks logic.
 */
HomePageArtworksManager.prototype.init = function() {
    console.log('HomePageArtworksManager.init() called');

    var filterer = new ArtworkFilterer();
    var renderer = new ArtworkRenderer();

    filterer.init( function( error, filter ) {

        var featuredArtworkSlides = renderer.renderSlideshowSlides( filter({ featured: true }) );

        var mapObjects = renderer.renderMapObjects( filter().filter( function( artwork ) { return artwork.hasLatLng(); } ) );

        console.log('all artworks returned from Zoho:');
        console.log( filter() );
        console.log('\n');

        $('.slick-featured-artworks').append( featuredArtworkSlides );

        drawMap( mapObjects );

    });

    return this;
};



function homePage() {

    var homepage = new HomePageArtworksManager();

    homepage.init();


}

export { homePage, isHomePage };
