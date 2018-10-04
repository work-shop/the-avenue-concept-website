'use strict';


import { Artwork } from './module-zoho-artwork.js';

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
 * Given an artwork, select a fill color for the marker on
 * the map based on artwork parameters.
 * By default, this routine colors the marker differently
 * based in program.
 *
 * @param Artwork artwork object
 * @return string a CSS-friendly representation of a color.
 */
function renderFillColor( artwork ) {
    if ( artwork.program === '3-D' ) {
        return '#dddb91';
    } else {
        return '#6ba442';
    }
}

/**
 * Create a placeholder image to append into any image location,
 * and switch out the placeholder image with the real image source
 * once it's finished downloading.
 *
 * @param Artwork artwork object
 * @return jQuery img tag, with loading handler that switches out the source after the image loaded.
 */
function createAsynchrounousImage( artwork ) {

    var img = $('<img>')
                    .attr('src', 'loading.png')
                    .attr('data-src', artwork.featured_media.image.src );

    var loading = $('<img>').attr('src', artwork.featured_media.image.src );

    loading.on('load', function() { img.attr('src', img.data('src') ); });

    return img;

}

/**
 * Given an array of artworks or a single artwork object
 * render a slide for use with the slick slideshow on the homepage.
 *
 * @param Artwork artwork object
 * @return jQuery element to be appended to the dom.
 */
function renderSlide( artwork = {}, index = 0 ) {

    // build consitiuent HTML elements.
    var root = $('<div>')
                    .addClass('artwork-slide')
                    .addClass('slide-' + index )
                    .addClass('featured-artwork')
                    .addClass('col-sm-6')
                    .attr('id', artwork.slug );

    var a = $('<a>')
                    .addClass('artwork-link')
                    .attr('href', artwork.url );

    var title = $('<h1>')
                    .addClass('artwork-title')
                    .text( artwork.name );

    var img = createAsynchrounousImage( artwork );

    //assemble elements into single structure.
    a.append( title );
    a.append( img );
    root.append( a );

    // return jQuery html.
    return root;
}

/**
 * Given an array of artworks or a single artwork object,
 * render a table row representing that artwork.
 *
 * @param Artwork artwork object
 * @return jQuery element to be appended to the dom.
 */
function renderListRow( artwork = {}, index = 0 ) {

    var artworkWrapper = $('<div>').addClass('artwork-list-row');

    // var linkTag = $('<a>').attr('href', artwork.getURL() );
    // var title = $('<h1>').text( artwork.name );

    // artworkWrapper.append( linkTag ).append( title );

    return artworkWrapper;
}

/**
 * Given an array of artworks or a single artwork object,
 * render a thumbnail for use in the Artworks Grid.
 * By default, this routine is also used to render the
 * the styling for the map popups.
 *
 * @param Artwork artwork object
 * @return jQuery element to be appended to the dom.
 */
function renderThumbnail( artwork = {}, index = 0 ) {

    // build consitiuent HTML elements.
    var root = $('<div>')
                    .addClass('artwork-thumbnail')
                    .addClass('slide-' + index )
                    .addClass('featured-artwork')
                    .addClass('col-sm-6')
                    .attr('id', artwork.slug );

    var a = $('<a>')
                    .addClass('artwork-link')
                    .attr('href', artwork.url );

    var title = $('<h4>')
                    .addClass('artwork-title')
                    .text( artwork.name );

    var img = $('<img>')
                    .attr('src', artwork.featured_media.image.src);

    //assemble elements into single structure.
    a.append( title );
    a.append( img );
    root.append( a );

    return root;
}

/**
 * Given an array of artworks or a single artwork object,
 * render that set as a set of map marker configuration objects.
 * By default, uses the `renderThumbnail` routine to provide
 * html for the marker.
 *
 * @param Artwork artwork object
 * @return JSON marker configuration object for use with the Map Module
 */
function renderMapObject( artwork = {}, index = 0 ) {

    var feature = { marker: artwork };

    feature.marker.icon = { fillColor: renderFillColor( artwork ) };

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
ArtworkRenderer.prototype.renderSlideshowSlides = renderWith( renderSlide );

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
