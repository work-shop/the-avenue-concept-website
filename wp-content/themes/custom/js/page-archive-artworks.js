'use strict';


var makeMap = require('@work-shop/map-module');

import { ArtworkFilterer } from './module-filter-artworks.js';
import { ArtworkRenderer } from './module-render-artworks.js';
import { URLManager } from './module-url-manager.js';

/**
 * file: page-home-artworks.js
 *
 * This file runs the state-management, artwork getting, and artwork rendering
 * for the /home page on the site.
 */

/**
 * This method returns true if the current page is the archive artworks page,
 * false otherwise.
 */
function isArtworksArchive() { return $( document.body ).hasClass('page-id-187'); }

/**
 * This class manages getting and rendering artworks on the archive artworks page
 */
function ArtworksArchiveManager() {
    if ( !(this instanceof ArtworksArchiveManager)) { return new ArtworksArchiveManager(); }
    console.log('ArtworksArchiveManager loaded.');
}

/**
 * This method sets up the archive artworks logic.
 */
ArtworksArchiveManager.prototype.init = function() {
    console.log('ArtworksArchiveManager.init() called');
    var self = this;

    self.filterer = new ArtworkFilterer();
    self.renderer = new ArtworkRenderer();
    self.urlmanager = new URLManager();

    var parsed = self.urlmanager.parseURL();

    self.filterer.init( function( error, filter ) {
        if ( error ) { console.error( error ); }

        var artworks = filter( parsed );

        console.log( artworks );

        var thumbs = self.renderer.renderThumbnails( artworks );

        $('.artworks-main').append( thumbs );

        self.handleViewClickStream()



    });

    return self;
};

function handleNewURL( state ) {

}

ArtworksArchiveManager.prototype.handleViewClickStream = function() {

    var self = this;

    $('.sidebar-view-button').on('click', function() {

        var state = self.urlmanager.updateURL( { view: $(this).data('artwork-view') } );

        console.log( state );


    });

};


function artworksArchive() {

    var archive = new ArtworksArchiveManager();

    archive.init();

}

export { artworksArchive, isArtworksArchive };
