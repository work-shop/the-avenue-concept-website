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


function nl2br (str, is_xhtml) {   
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

/**
 *
 *
 */
 SingleArtworksManager.prototype.renderArtworkToPage = function( artwork ) {
    //console.log('renderArtworkToPage');

    console.log( artwork );
    this.map.init();

    var title = artwork.name;

    var description = artwork.description;

    description = nl2br( description );

    //var thumbnail = artwork.featured_media;
    var featured_images = artwork.media.filter( function( media ) { return media.type === 'image' && media.featured; });
    var regular_images = artwork.media.filter( function( media ) { return media.type === 'image' && !media.featured; });
    var featured_videos = artwork.media.filter( function( media ) { return media.type === 'video' && media.featured; });
    var regular_videos = artwork.media.filter( function( media ) { return media.type === 'video' && !media.featured; });

    for (var i = 0; i < regular_images.length; i++) {
        var image = '<div class="single-artwork-slide" style="background-image: url(' + regular_images[i].image.src + ')";></div>';
        $('.slick-single-artwork').append( image );
        
        if( i === ( regular_images.length - 1 )){
            $('.slick-single-artwork').slick({
                slidesToShow: 1,
                dots: true,
                arrows: true,
                autoplay: true,
                fade: false,
                autoplaySpeed: 7000,
                speed: 700
            });

        }
    }


    var artists = '';
    for (var i = 0; i < artwork.artist.length; i++) {
        if( i > 0 && i < (artwork.artist.length) ){
            artists += ', ';
        }
        artists = artists + artwork.artist[i].name;
    }
    var date = artwork.dates.created.format('YYYY');
    var medium = '';
    for (var i = 0; i < artwork.medium.length; i++) {
        if( i > 0 && i < ( artwork.medium.length ) ){
            medium += ', ';
        }
        medium = medium + artwork.medium[i];
    }
    var location = artwork.location.name;
    var sponsors = artwork.sponsors;
    var program = artwork.program;
    program = '<a href="/artworks/?program=' + program + '" class="sidebar-program-button sidebar-button">' + program + '</a>';

    $('title').text( title  + ' | The Avenue Concept' );
    $('.single-artwork-title').html( title );
    $('.single-artwork-description').html( description );

    if ( typeof artists !== 'undefined' ) {
        if ( artists.trim() ) {
            $('#single-artwork-meta-artist').html( artists );
        } else{
            $('.single-meta-artist').hide();
        } 
    } else{
        $('.single-meta-artist').hide();
    }

    if ( typeof date !== 'undefined' ) {
        if ( date.trim() ) {
            $('#single-artwork-meta-date').html( date );
        } else{
            $('.single-meta-date').hide();
        } 
    } else{
        $('.single-meta-date').hide();
    }

    if ( typeof medium !== 'undefined' ) {
        if ( medium.trim() ) {
            $('#single-artwork-meta-medium').html( medium );
        } else{
            $('.single-meta-medium').hide();
        }
    } else{
        $('.single-meta-medium').hide();
    }

    if ( typeof location !== 'undefined' ) {
        if ( location.trim() ) {
            $('#single-artwork-meta-location').html( location );
        } else{
            $('.single-meta-location').hide();
        }
    } else{
        $('.single-meta-location').hide();
    }

    if ( typeof sponsors !== 'undefined' ) {
        if ( sponsors.trim() ) {
            $('#single-artwork-meta-sponsors').html( sponsors );
        } else{
            $('.single-meta-sponsors').hide();
        }
    } else{
        $('.single-meta-sponsors').hide();
    }

    if ( typeof program !== 'undefined' ||  program.trim() ) {
        if ( program.trim() ) {
            $('#single-artwork-meta-program').html( program );
        }
        else{
            $('.single-meta-program').hide();
        }
    }

    for (var i = 0; i < regular_images.length; i++) {
        var image = '<div class="single-artwork-image"><img src="' + regular_images[i].image.src + '" /></div>';
        $('.single-artwork-images-container').append( image );
    }

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
