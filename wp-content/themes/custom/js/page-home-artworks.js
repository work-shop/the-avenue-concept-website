'use strict';

var makeMap = require('@work-shop/map-module');
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

    $(window).on('load', function( ) {

        self.map = makeMap({
            selector: '#home-map-container',
            map: {
                streetViewControl: false
            },
            data: {
              marker: {
                icon: {
                  fillColor: 'red',
                },
                popup: {
                  placement: 'left',
                  pointer: '8px',
                  on: {
                    open: function () {
                      console.log( 'opened:' + this._options.id );
                    },
                    close: function () {
                      console.log( 'closed:' + this._options.id );
                    }
                  }
                }
              }
            },
            render: {
              center: { lat: 41.8240, lng: -71.4128 },
              zoom: 14
            }
        })[0];

        self.map.data(
            [
                {
                    marker: {
                        position: { lat: 41.8240, lng: -71.4128 },
                        icon: { fillColor: '#6ba442' }
                    }
                },
                {
                    marker: {
                        position: { lat: 41.8244, lng: -71.4132 },
                        icon: { fillColor: '#6ba442' }
                    }
                }

            ]
        ).removeFeatures().render( { zoom: 16 } );


    });



    return this;
};



function homePage() {

    var homepage = new HomePageArtworksManager();

    homepage.init();


}

export { homePage, isHomePage };
