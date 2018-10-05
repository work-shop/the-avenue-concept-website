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

    self.filterer.init( function( error, filter, diff ) {
        if ( error ) { console.error( error ); }

        self.filter = filter;
        self.diff = diff;

        var add = self.filter( parsed );

        var thumbs = self.renderer.renderThumbnails( add );

        $('.artworks-main').append( thumbs );

        self.handleViewClickStream();

        self.handleStatusClickStream();

        self.handleProgramsClickStream();

        self.handleYearClickStream();

    });

    return self;
};

/**
 * Handle given a new state constructed by the URL Manager,
 * construct a state diff against the previous page state,
 * remove the '.remove' key, using whatever transitions seem
 * appropriate, and then add the `.add` key, using whatever transitions seem
 * appropriate.
 *
 * @param state the current filter state.
 */
ArtworksArchiveManager.prototype.handleNewState = function( state ) {

    var diffObject = this.diff( state );

    console.log( diffObject );

    if ( diffObject.remove.length === 0 && diffObject.add.length === 0 ) {
        // no change to the set of displayed artworks.

    } else {


    }


};


/**
 *
 *
 *
 */
ArtworksArchiveManager.prototype.handleProgramsClickStream = function() {

    var self = this;

    $('.sidebar-program-button').on('click', function() {

        console.log( $(this).data('artworks-filter') );

        var newState;

        if ( typeof $(this).data('artworks-filter') === 'undefined' ) {

            newState = self.urlmanager.updateURL( { program: undefined } );

        } else {

            newState = self.urlmanager.updateURL( { program: $(this).data('artworks-filter') } );

        }

        self.handleNewState( newState );

    });

};


/**
 *
 *
 *
 */
ArtworksArchiveManager.prototype.handleViewClickStream = function() {

    var self = this;

    $('.sidebar-view-button').on('click', function() {

        var newState = self.urlmanager.updateURL( { view: $(this).data('artworks-view') } );

        self.handleNewState( newState );

    });

};

/**
 *
 *
 *
 */
ArtworksArchiveManager.prototype.handleYearClickStream = function() {

    var self = this;

    $('#sidebar-select-year-from, #sidebar-select-year-to').on( 'change', function() {

        var from = $( '#sidebar-select-year-from' ).val();
        var to = $( '#sidebar-select-year-to' ).val();

        var newState;

        if ( parseInt( from ) > parseInt( to ) ) {

            console.error('"From:" date is greater than "To:" date. Handle?');
            newState = {};

        } else if ( from === to ) {

            newState = self.urlmanager.updateURL( { year: from, from: undefined, to: undefined} );

        } else {

            newState = self.urlmanager.updateURL( { year: undefined, from: '01-01-' + from, to: '12-31-' + to } );

        }

        self.handleNewState( newState );

    });

};


/**
 *
 *
 *
 */
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
