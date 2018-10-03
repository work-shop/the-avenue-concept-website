'use strict';


import { Artwork } from './module-zoho-artworks.js';

/**
 * file: module-render-artworks.js
 *
 * This file constructs jQuery representations of artwork data
 * and returns for further processing.
 */

function ArtworkRenderer() {
    console.log('creating new ArtworkRenderer instance.');
    if ( !(this instanceof ArtworkRenderer)) { return new ArtworkRenderer(); }
    var self = this;

}

/**
 * Given an array of artworks or a single artwork object
 *
 */
function renderSlide( artwork = {}, index = 0 ) {

    var artworkNode = $('<div>').addClass('artwork-slide');

    return artworkNode;
}

/**
 * Given an array of artworks or a single artwork object
 *
 */
function renderListRow( artwork = {}, index = 0 ) {

    var artworkNode = $('<div>').addClass('artwork-list-row');

    return artworkNode;
}

/**
 * Given an array of artworks or a single artwork object
 *
 */
function renderThumbnail( artwork = {}, index = 0 ) {

    var artworkNode = $('<div>').addClass('artwork-thumbnail');

    return artworkNode;
}

/**
 * Given an array of artworks or a single artwork object
 *
 */
function renderMapObject( artwork = {}, index = 0 ) {

    var feature = { marker: artwork };

    var popup = { content: renderThumbnail( artwork ).html() };

    feature.marker.popup = popup;

    return feature;

}


/**
 * This routine gathers functionality for determining
 * whether the input represents a single artwork, an
 * array of Artwork, or represents some unknown object,
 * and an error should be thrown.
 */
function renderWith( renderFunction ) {
    return function( artworks = [] ) {

        if ( artworks instanceof Artwork ) {

            return renderFunction( artworks );

        } else if ( Array.isArray( artworks ) ) {

            return artworks.map( renderFunction );

        } else {

            throw new Error( 'RenderArtwork: encountered an input that\'s neither an Artwork or an Array of Artwork' );

        }

    };

}


/**
 * Render a set of artwork as slides to be added to slick.
 */
ArtworkRenderer.prototype.renderSlideshow = renderWith( renderSlide );

/**
 * Render a set of artwork as rows to be added to an artwork table.
 */
ArtworkRenderer.prototype.renderListRows = renderWith( renderListRow );

/**
 * Render a set of artwork as thumbnails to be added to an artwork grid.
 */
ArtworkRenderer.prototype.renderThumbnails = renderWith( renderThumbnail );

/**
 * Render a set of artwork as map objects to be plotted on the map.
 */
ArtworkRenderer.prototype.renderMapObjects = renderWith( renderMapObject );

export { ArtworkRenderer };
