'use strict';


import { Artwork } from './module-zoho-artwork.js';

/**
 * file: module-render-artworks.js
 *
 * This file constructs jQuery representations of artwork data
 * and returns for further processing.
 */

 function ArtworkRenderer() {
    //console.log('creating new ArtworkRenderer instance.');
    if ( !(this instanceof ArtworkRenderer)) { return new ArtworkRenderer(); }
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
        return '#6ba442';
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
 function createAsynchrounousImage( src ) {

    var img = $('<img>')
    .attr('src', '/wp-content/themes/custom/images/default.png')
    .attr('data-src', src );

    var loading = $('<img>').attr('src', src );

    loading.on('load', function() { img.attr('src', img.data('src') ); });

    return img;

}

/**
 * Given an artwork, get the features image object for the artwork.
 */
 function getFeaturedImageSrc( artwork ) {

    if( typeof artwork.featured_media.image !== 'undefined' ) {

        //console.log('featured image');
        // if ( artwork.featured_media.image.has_low_quality_versions === false ){
        //     console.log(artwork.featured_media.image);
        // }


        //get the med attribute here

        return artwork.featured_media.image.low;

    } else {

        //console.log('no featured image');

        var image_media = artwork.media.filter( function( m ) { return m.type === 'image'; });

        if ( image_media.length > 0 ) {

            return image_media[0].image.low;

        } else {
            // handle default case.
            return '/wp-content/themes/custom/images/default.png';

        }
    }

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
    // var root = $('<div>')
    // .addClass('artwork-slide')
    // .addClass('slide-' + index )
    // .addClass('featured-artwork')
    // .addClass('col-sm-6')
    // .addClass('artwork-' + artwork.slug );

    // var a = $('<a>')
    // .addClass('artwork-link')
    // .attr('href', artwork.url );

    // var title = $('<h1>')
    // .addClass('artwork-title')
    // .text( artwork.name );

    // var img = createAsynchrounousImage( getFeaturedImageSrc( artwork ) );

    // //assemble elements into single structure.
    // a.append( title );
    // a.append( img );
    // root.append( a );

    // // return jQuery html.
    // return root;
}


/**
 * Given an array of artworks or a single artwork object,
 * render a table row representing that artwork.
 *
 * @param Artwork artwork object
 * @return jQuery element to be appended to the dom.
 */
 function renderListRow( artwork = {}, index = 0 ) {

    // var artworkWrapper = $('<div>').addClass('artwork-list-row').addClass('artwork-' + artwork.slug );

    // // var linkTag = $('<a>').attr('href', artwork.getURL() );
    // // var title = $('<h1>').text( artwork.name );

    // // artworkWrapper.append( linkTag ).append( title );

    // return artworkWrapper;
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

    //console.log(artwork);

    // build consitiuent HTML elements.
    var root = $('<div>')
    .addClass('artwork-item')
    .addClass('artwork-thumbnail')
    .addClass('artwork-active')
    .addClass('artwork-item-index-' + index )
    .addClass('artwork-' + artwork.slug );

    var a = $('<a>')
    .addClass('artwork-item-link')
    .attr('href', artwork.url );

    var text = $('<div>')
    .addClass('artwork-item-text');

    var title = $('<h4>')
    .addClass('artwork-item-title')
    .text( artwork.name );

    var artists = '';
    for (var i = 0; i < artwork.artist.length; i++) {
        if( i > 0 && i < (artwork.artist.length) ){
            artists += ', ';
        }
        artists += artwork.artist[i].name;
    }
    var artist = $('<h4>')
    .addClass('artwork-item-artist')
    .text( artists );

    var year = $('<h4>')
    .addClass('artwork-item-year')
    .text( artwork.dates.created.format('YYYY') );

    var location = $('<h4>')
    .addClass('artwork-item-location')
    .text( artwork.location );

    var img = $('<img class="artwork-item-image">').attr('src', getFeaturedImageSrc( artwork ) );

    text.append( title );
    text.append( artist );
    text.append( year );
    text.append( location );

    //assemble elements into single structure.
    a.append( img );
    a.append( text );
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

    var mapObject = { marker: artwork };

    mapObject.marker.icon = {
       url: '/wp-content/themes/custom/images/marker.png',
       size: new google.maps.Size(45, 75),
       scaledSize: new google.maps.Size(45, 75),
       origin: new google.maps.Point( 0, 0 ),
       anchor: new google.maps.Point( 22.5, 75 )
   };

   var popup = { content: renderThumbnail( artwork ).prop('outerHTML') };

   mapObject.marker.popup = popup;

   return mapObject;

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
