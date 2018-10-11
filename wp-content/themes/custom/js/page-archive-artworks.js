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
    //console.log('ArtworksArchiveManager loaded.');

    this.config = config;

}

/**
 * This method sets up the archive artworks logic.
 */
 ArtworksArchiveManager.prototype.init = function() {
    //console.log('ArtworksArchiveManager.init() called');
    var self = this;

    self.filterer = new ArtworkFilterer();
    self.renderer = new ArtworkRenderer();
    self.urlmanager = new URLManager();
    self.map = new ArtworksMap( '#artworks-map-map', self.config );

    var parsed = self.urlmanager.parseURLWithDefaults();

    //console.log('parsed:');
    //console.log(parsed);

    manageClassesForView( parsed );
    manageClassesForProgram( parsed );
    manageClassesForYear( parsed );
    manageClassesForLocation( parsed );

    self.filterer.init( function( error, filter, diff, getValues ) {
        if ( error ) { throw new Error( error ); }

        var programs = getValues( 'program' );
        var locations = getValues( 'location', function( x ) { return x.name; } ).map( function( l ) { return l.name; });
        self.buildLocations( locations, parsed );
        self.buildPrograms( programs, parsed );

        self.filter = filter;
        self.diff = diff;
        self.map.init();

        //console.log( parsed );

        self.doInitialStateTransition( self.diff( parsed ) );

        self.handleViewClickStream();

        self.handleStatusClickStream();

        self.handleProgramsClickStream();

        self.handleYearClickStream();

        self.handleLocationClickStream();

        self.handleResetClickStream();

        self.handleMobileToggle();

    });

    // manageClassesForView( parsed );
    // manageClassesForProgram( parsed );
    // manageClassesForYear( parsed );
    // manageClassesForLocation( parsed );

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

    // console.log('handleNewState: ');
    // console.log( state );

    manageClassesForView( state );
    manageClassesForProgram( state );
    manageClassesForYear( state );
    manageClassesForLocation( state );

    if ( diffObject.remove.length !== 0 || diffObject.add.length !== 0 ) {
        this.doStateTransitionByDiff( diffObject );
    } else if ( state.view === 'map' ) {
        this.map.update( this.renderer.renderMapObjects( this.filterer.getCurrentState() ) );
    }

};

ArtworksArchiveManager.prototype.doInitialStateTransition = function( diff ) {

    var thumbs = $( '#artworks-thumbnails-row' );
    var list = $( '#artworks-list-list' );
    var map_list = $( '#artworks-map-list-list' );

    thumbs.append( this.renderer.renderThumbnails( this.filterer.artworks ) );
    list.append( this.renderer.renderThumbnails( this.filterer.artworks ) );
    map_list.append( this.renderer.renderThumbnails( this.filterer.artworks ) );

    this.doStateTransitionByDiff( diff );

};


ArtworksArchiveManager.prototype.doStateTransitionByDiff = function( diffObject ) {

    //console.log('doStateTransitionByDiff: ');
    //console.log( diffObject );

    const fade_duration = 500;

    var artworksToRemove = $( diffObject.remove.map( function( artwork ) { return '.artwork-' + artwork.slug; }).join(', ') );
    var artworksToAdd = $( diffObject.add.map( function( artwork ) { return '.artwork-' + artwork.slug; }).join(', ') );

    artworksToRemove.fadeOut( { duration: fade_duration });
    artworksToAdd.fadeIn( { duration: fade_duration });

    this.map.update( this.renderer.renderMapObjects( this.filterer.getCurrentState() ) );

};



ArtworksArchiveManager.prototype.buildPrograms = function( programs, state ) {

    var programsContainer = $('.program-filters');

    for (var i = 0; i < programs.length; i++) {
        programsContainer.append(' <a href="#" class="sidebar-program-button sidebar-button" data-artworks-filter="' + programs[i] + '">' + programs[i] + '</a> ');
    }

    manageClassesForProgram( state );

};


ArtworksArchiveManager.prototype.buildLocations = function( locations, state ) {

    //console.log('doStateTransitionByDiff: ');
    //console.log( diffObject );
    var locationsSelect = $('#sidebar-select-location');

    for (var i = 0; i < locations.length; i++) {
        locationsSelect.append('<option value="' + locations[i] + '">' + locations[i] + '</option>');
    }

    manageClassesForLocation( state );


};


/**
 * Helper method to manage body classes
 * based on the state
 *
 * @param object state the current state based on the URL
 */
 function manageClassesForView( state ) {
    // console.log('manageClassesForView: ');
    // console.log( state );

    $( 'body' ).removeClass( 'artworks-view-map' ).removeClass( 'artworks-view-list' ).removeClass( 'artworks-view-thumbnails' );
    $( 'body' ).addClass( 'artworks-view-' + state.view );

    $('.sidebar-view-button').removeClass('active');
    $('a[data-artworks-view="' + state.view + '"]').addClass('active');
}

/**
 * Helper method to manage body classes
 * based on the state
 *
 * @param object state the current state based on the URL
 */
 function manageClassesForProgram( state ) {
    // console.log('manageClassesForprogram: ');
    // console.log( state );

    // if no program, else program filter is on
    if( typeof state.program !== 'undefined' ){
        $( 'body' ).addClass( 'artworks-program-filtered' );
        $('.sidebar-program-button').removeClass('active');
        $('a[data-artworks-filter="' + state.program + '"]').addClass('active');
    } else{
        $( 'body' ).removeClass( 'artworks-program-filtered' );
        $('.sidebar-program-button').removeClass('active');
        $('#artworks-filter-all').addClass('active');
    }

}

/**
 * Helper method to manage body classes
 * based on the state
 *
 * @param object state the current state based on the URL
 */
 function manageClassesForYear( state ) {
    // console.log('manageClassesForYear: ');
    // console.log( state );

    // if no year, else year filter is on
    if( typeof state.from !== 'undefined' || typeof state.to !== 'undefined' ){
        $( 'body' ).addClass( 'artworks-year-filtered' );
        if( typeof state.from !== 'undefined' ){
            var str = state.from.split('-');
            $( '#sidebar-select-year-from option[value="' + str[2] + '"]').attr('selected', 'selected');
        }
        if( typeof state.to !== 'undefined' ){
            var str = state.to.split('-');
            $( '#sidebar-select-year-to option[value="' + str[2] + '"]').attr('selected', 'selected');
        }
    } else{
        $( '#sidebar-select-year-from' ).prop('selectedIndex', 0);
        $( '#sidebar-select-year-to' ).prop('selectedIndex', 0);
        $( 'body' ).removeClass( 'artworks-year-filtered' );
    }

}

/**
 * Helper method to manage body classes
 * based on the state
 *
 * @param object state the current state based on the URL
 */
 function manageClassesForLocation( state ) {
    // console.log('manageClassesForLocation: ');
    // console.log( state );

    // if no location, else location filter is on
    if( typeof(state.location) !== 'undefined' ){
        $( 'body' ).addClass( 'artworks-location-filtered' );
    } else{
        $('#sidebar-select-location').prop('selectedIndex', 0);
        $( 'body' ).removeClass( 'artworks-location-filtered' );
    }

}



/**
 *
 *
 *
 */
 ArtworksArchiveManager.prototype.handleViewClickStream = function() {

    var self = this;

    // console.log('handleViewClickStream: ');
    // console.log( self );

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



/**
 *
 *
 *
 */
 ArtworksArchiveManager.prototype.handleProgramsClickStream = function() {

    var self = this;

    $('.sidebar-program-button').on('click', function() {

        //console.log( $(this).data('artworks-filter') );

        var newState;

        if ( typeof $(this).data('artworks-filter') === 'undefined' ) {

            newState = self.urlmanager.updateURL( { program: undefined } );

        } else {

            newState = self.urlmanager.updateURL( { program: $(this).data('artworks-filter') } );

        }

        self.handleNewState( newState );

    });

    $('#clear-program').on('click', function() {
        var newState;
        newState = self.urlmanager.updateURL( { program: undefined } );
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

        if( from === null ){
            from = '2012';
        }
        if( to === null ){
            var latestYear = $( '#sidebar-select-year-to option:last' ).val();
            to = latestYear;
        }

        var newState;

        if ( parseInt( from ) > parseInt( to ) ) {

            console.error('"From:" date is greater than "To:" date. Handle?');
            newState = {};

        } else {

            newState = self.urlmanager.updateURL( { from: '01-01-' + from, to: '12-31-' + to } );
            console.log(newState);
        }

        self.handleNewState( newState );

    });

    $('#clear-year').on('click', function() {
        var newState;
        newState = self.urlmanager.updateURL( { from: undefined, to: undefined } );
        self.handleNewState( newState );
    });

};

/**
 *
 *
 *
 */
 ArtworksArchiveManager.prototype.handleLocationClickStream = function() {

    var self = this;

    $('#sidebar-select-location').on( 'change', function() {

        var location = $( '#sidebar-select-location' ).val();

        var newState;

        if ( typeof location === 'undefined' ) {
            newState = self.urlmanager.updateURL( { location: undefined } );
        } else {
            newState = self.urlmanager.updateURL( { location: location } );
        }

        self.handleNewState( newState );

    });

    $('#clear-location').on('click', function() {
        var newState;
        newState = self.urlmanager.updateURL( { location: undefined } );
        self.handleNewState( newState );
    });

};

/**
 *
 *
 *
 */
 ArtworksArchiveManager.prototype.handleResetClickStream = function() {

    var self = this;

    $('.sidebar-button-reset').on( 'click', function() {
        var newState;
        newState = self.urlmanager.updateURL( { program: undefined, from: undefined, to: undefined, location: undefined } );
        self.handleNewState( newState );
    });

};

/**
 *
 *
 *
 */
 ArtworksArchiveManager.prototype.handleMobileToggle = function() {

    $('.sidebar-mobile-toggle').on( 'click', function() {
        if( $('.artworks-sidebar').hasClass('mobile-closed') ){
            $('.artworks-sidebar').removeClass('mobile-closed');
            $('.artworks-sidebar').addClass('mobile-open');
        } else if( $('.artworks-sidebar').hasClass('mobile-open') ){
            $('.artworks-sidebar').removeClass('mobile-open');
            $('.artworks-sidebar').addClass('mobile-closed');
        }
    });

};



function artworksArchive() {

    var archive = new ArtworksArchiveManager();

    archive.init();

}

export { artworksArchive, isArtworksArchive };
