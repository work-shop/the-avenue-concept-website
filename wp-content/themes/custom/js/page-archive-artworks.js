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

    var parsed = self.urlmanager.parseURLWithDefaults();

    self.filterer.init( function( error, filter ) {
        if ( error ) { console.error( error ); }

        self.filter = filter;

        var artworks = filter( parsed );

        var thumbs = self.renderer.renderThumbnails( artworks );

        $('.artworks-main').append( thumbs );

        self.handleViewClickStream();

        self.handleStatusClickStream();

    });

    return self;
};

ArtworksArchiveManager.prototype.handleNewState = function( state ) {

    var artworks = this.filter( state );

    console.log( artworks );

};


ArtworksArchiveManager.prototype.handleViewClickStream = function() {

    var self = this;

    $('.sidebar-view-button').on('click', function() {

        var newState = self.urlmanager.updateURL( { view: $(this).data('artworks-view') } );

        self.handleNewState( newState );

    });

};

ArtworksArchiveManager.prototype.handleStatusClickStream = function() {

    var self = this;

    $('.sidebar-status-input').on('click', function() {

        var update = {};
        var archived = $('.sidebar-status-input[name=archived]:checked').length > 0;
        var on_view = $('.sidebar-status-input[name=on-view]:checked').length > 0;

        if ( (archived && on_view) || !(archived || on_view) ) {

            update['on-view'] = undefined;

        } else if ( archived ) {

            update['on-view'] = false;

        } else if ( on_view ) {

            update['on-view'] = true;

        }

        var newState = self.urlmanager.updateURL( update );

        self.handleNewState( newState );


    });

};


function artworksArchive() {

    var archive = new ArtworksArchiveManager();

    archive.init();

}

export { artworksArchive, isArtworksArchive };
