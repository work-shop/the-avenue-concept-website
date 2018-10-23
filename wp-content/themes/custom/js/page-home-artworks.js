'use strict';


import { ArtworkFilterer } from './module-filter-artworks.js';
import { ArtworkRenderer } from './module-render-artworks.js';

import { ArtworksMap } from './module-map.js';


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
    //console.log('HomePageArtworksManager loaded');
    if ( !(this instanceof HomePageArtworksManager)) { return new HomePageArtworksManager(); }


}

/**
 * This method sets up the home page artworks logic.
 */
 HomePageArtworksManager.prototype.init = function() {
    //console.log('HomePageArtworksManager.init() called');

    var filterer = new ArtworkFilterer();
    var renderer = new ArtworkRenderer();
    var map = new ArtworksMap('#home-map-container');

    map.init();

    filterer.init( function( error, filter ) {

        if ( error ) { console.error( error ); }

        var featuredArtworkSlides = renderer.renderThumbnails( filter({ featured: true }) );

        map.update( renderer.renderMapObjects( filter() ) );

        //console.log('all artworks returned from Zoho:');
        //console.log( filter() );
        //console.log('\n');

        $('.slick-featured-artworks').append( featuredArtworkSlides );

        // $('.slick-featured-artworks').slick({
        //     slidesToShow: 1,
        //     dots: true,
        //     arrows: true,
        //     autoplay: true,
        //     fade: false,
        //     autoplaySpeed: 7000,
        //     speed: 700
        // });

    });

    return this;
};



function homePage() {

    var homepage = new HomePageArtworksManager();

    homepage.init();

}

export { homePage, isHomePage };
