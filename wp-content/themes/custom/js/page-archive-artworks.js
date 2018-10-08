'use strict';


import { ArtworksMap } from './module-map.js';

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
function ArtworksArchiveManager( config ) {
    if ( !(this instanceof ArtworksArchiveManager)) { return new ArtworksArchiveManager( config ); }
    console.log('ArtworksArchiveManager loaded.');

    this.config = config;

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
    self.map = new ArtworksMap( '#artworks-map-map', self.config );

    var parsed = self.urlmanager.parseURLWithDefaults();

    self.filterer.init( function( error, filter, diff, getValues ) {
        if ( error ) { throw new Error( error ); }

        var programs = getValues( 'program' );
        console.log( programs );

        self.filter = filter;
        self.diff = diff;
        self.map.init();

        console.log( parsed );

        self.doStateTransitionByDiff( self.diff( parsed ) );

        self.handleViewClickStream();

        self.handleStatusClickStream();

        self.handleProgramsClickStream();

        self.handleYearClickStream();

        // self.handleLocationClickStream();

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

    if ( diffObject.remove.length !== 0 || diffObject.add.length !== 0 ) {

        this.doStateTransitionByDiff( diffObject );

    }

};

ArtworksArchiveManager.prototype.doStateTransitionByDiff = function( diffObject ) {

    console.log( diffObject );

    var artworksToRemove = $( diffObject.remove.map( function( artwork ) { return '.artwork-' + artwork.slug; }).join(', ') );
    var thumbs = $( '#artworks-thumbnails' );
    var list = $( '#artworks-list' );
    var map_list = $( '#artworks-map-list' );

    this.map.update( this.renderer.renderMapObjects( this.filterer.getCurrentState() ) );




}


/**
 * Helper method to manage body classes
 * based on the particular active
 * view passed into the state.
 *
 * @param String view the current view, one of map, thumbnails, list.
 */
function manageBodyClassesForView( view ) {
    // handle adding and removing bodyclasses.
    //$( document.body ).removeClass( '' )


}


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
