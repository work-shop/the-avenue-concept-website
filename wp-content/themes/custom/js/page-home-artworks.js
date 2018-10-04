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
 * This method sets up the home page artworks logic.
 */
HomePageArtworksManager.prototype.init = function() {
    console.log('HomePageArtworksManager.init() called');
    var self = this;

    var filterer = new ArtworkFilterer();
    var renderer = new ArtworkRenderer();

    filterer.init( function( error, filter ) {

        var artworks = filter({ featured: true });

        console.log( artworks );

    });





        // self.map = makeMap({
        //     selector: '#home-map-container',
        //     map: {
        //         streetViewControl: false
        //     },
        //     data: {
        //       marker: {
        //         icon: {
        //           fillColor: 'red',
        //         },
        //         popup: {
        //           placement: 'left',
        //           pointer: '8px',
        //           on: {
        //             open: function () {
        //               console.log( 'opened:' + this._options.id );
        //             },
        //             close: function () {
        //               console.log( 'closed:' + this._options.id );
        //             }
        //           }
        //         }
        //       }
        //     },
        //     render: {
        //       center: { lat: 41.8240, lng: -71.4128 },
        //       zoom: 14
        //     }
        // })[0];
        //
        // //var artworks = filterer.filter( { featured: true } )
        //
        // self.map.data(
        //     //renderer.renderMapObjects( artworks )
        //
        //     [
        //         {
        //             marker: {
        //                 position: { lat: 41.8240, lng: -71.4128 },
        //                 icon: { fillColor: '#6ba442' }
        //             }
        //         },
        //         {
        //             marker: {
        //                 position: { lat: 41.8240, lng: -71.414 },
        //                 icon: { fillColor: '#6ba442' },
        //             }
        //         }
        //
        //     ]
        // ).removeFeatures().render();

    return this;
};



function homePage() {

    var homepage = new HomePageArtworksManager();

    homepage.init();


}

export { homePage, isHomePage };
