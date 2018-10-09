'use strict';

import { ArtworksMap } from './module-map.js';
import { ArtworkRenderer } from './module-render-artworks.js';
import { ZohoConnection } from './module-zoho-connection.js';
import { extractArtworkNameFromURL } from './module-url-manager.js';

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
function isArtworksSingle() { return $('.artwork-single').length > 0; }

/**
 * This class manages getting and rendering artworks on the home page.
 */
function SingleArtworksManager() {
    if ( !(this instanceof SingleArtworksManager)) { return new SingleArtworksManager(); }

    console.log('SingleArtworksManager loaded.');
}

/**
 * This method sets up the home page artworks logic.
 */
SingleArtworksManager.prototype.init = function() {
    console.log('SingleArtworksManager.init() called');
    var self = this;

    var conn = new ZohoConnection();

    var slug = extractArtworkNameFromURL();

    this.map = new ArtworksMap( '#single-artwork-map' );
    this.renderer = new ArtworkRenderer();

    conn.getArtworks( slug, function( error, artworks ) {

        if ( error ) {

            // handle connection or API error.
            console.error( 'Zoho API Error: ' + error.message );
            self.renderError( 'API Error!', error.message );

        } else if ( artworks.length < 1 ) {

            // handle 404 error – no such artwork
            console.error( 'Zoho 404 Error: No artworks returned for the slug \"' + slug + '\"' );
            self.renderError( 'Not Found!', 'There aren\'t any artworks by that name in our database.' );

        } else if ( artworks.length > 1 ) {

            // handle weird error - multiple artworks with matching slugs.
            console.error( 'Zoho Error: Multiple artworks returned for the slug \"' + slug + '\"' );
            self.renderError( 'Multiple Artworks!', 'Oops, you found an error! Looks like there are multiple artworks by this name in our database.' );

        } else {

            // success case.
            self.renderArtworkToPage( artworks[0] );

        }

    });

    return this;
};

/**
 *
 *
 */
SingleArtworksManager.prototype.renderArtworkToPage = function( artwork ) {

    console.log( artwork );
    this.map.init();

    var title = artwork.name;
    var description = artwork.description;

    var artist = artwork.artist[0]; // In this case, we're assuming there's only one artist associated with each artwork.

    var thumbnail = artwork.featured_media;
    var featured_images = artwork.media.filter( function( media ) { return media.type === 'image' && media.featured; });
    var regular_images = artwork.media.filter( function( media ) { return media.type === 'image' && !media.featured; });
    var featured_videos = artwork.media.filter( function( media ) { return media.type === 'video' && media.featured; });
    var regular_videos = artwork.media.filter( function( media ) { return media.type === 'video' && !media.featured; });

    var program = artwork.program;
    var media = artwork.medium;
    var installed_date = artwork.dates.created.format('MMMM DD, YYYY');

    $('title').text( title  + ' | The Avenue Concept' );

    $('.artwork-title').html( title);

    $('.artwork-description').html( description );

    this.map.update( [this.renderer.renderMapObjects( artwork )], {zoom: 17, center: artwork.position } );

};


SingleArtworksManager.prototype.renderError = function( type, message ) {

    console.log( type );
    console.log( message );

};


function singleArtwork() {

    var single = new SingleArtworksManager();

    single.init();

}


export { singleArtwork, isArtworksSingle };
