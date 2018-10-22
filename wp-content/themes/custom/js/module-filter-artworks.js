'use strict';

import { ZohoConnection } from './module-zoho-connection.js';

var reduce = require('reduce-object');
var moment = require('moment');
var objectAssign = require('object-assign');

/**
 * file: module-filter-artworks.js
 *
 * This file filters raw API responses from Zoho down to
 * the set of queried posts.
 *
 * @param zoho this module is implicitly parameterized over a zoho request module,
 *             which is responsible for getting and caching the total set of artwork
 *             data available from zoho.
 *
 */
function ArtworkFilterer() {
    //console.log('creating new ArtworkFilterer instance.');
    if ( !(this instanceof ArtworkFilterer)) { return new ArtworkFilterer(); }
    var self = this;

    /**
     * @member zoho ZohoConnection the connection to the zoho database.
     */
    self.zoho = new ZohoConnection();
    self.artworks = [];
    self.initialized = false;
    self.currentState = [];

}



/**
 * Given a set of criteria from the URL manager, replaces
 * certain keys with values appropriate for filtering artwork
 * against.
 *
 *
 *
 */
var preprocess = function( criteria, metadata = { date_parse_string: 'MM-DD-YYYY'} ) {

    if ( typeof criteria['on-view'] !== 'undefined') {
        criteria.on_view = ( criteria['on-view'] === null ) ? true : criteria['on-view'];
    }

    if ( typeof criteria.year !== 'undefined' ) {

        criteria.from = moment( '01-01-' + criteria.year, metadata.date_parse_string );
        criteria.to = moment( '12-31-' + criteria.year, metadata.date_parse_string );

        delete criteria.year;

    } else {

        if ( typeof criteria.from !== 'undefined' ) {

            criteria.from = moment( criteria.from, metadata.date_parse_string );

        }

        if ( typeof criteria.to !== 'undefined' ) {

            criteria.to = moment( criteria.to, metadata.date_parse_string );

        }

    }

    return criteria;

};


/**
 * Given a set of criteria to filter the artwork by, return
 * the filtered set of artwork matching that criteria. All keys are optional;
 * if no key is present, then the filtering dimension is ignored.
 *
 *
 * @param criteria.medium ?string a medium to match artwork against
 * @param criteria.program ?string a program to match artwork against
 * @param criteria.installed_on_or_after ?moment a moment data object representing the first possible install date inclusive.
 * @param criteria.installed_on_or_before ?moment a moment data object representing the last possible install date inclusive.
 * @param criteria.on_view_now ?boolean a boolean indicating whether to get only art on view, or only art not on view.
 * @param criteria.featured ?boolean a boolean indicating whether the artwork is featured or not.
 *
 * @return Array<Artwork> an array of artworks matching the criteria.
 */
var filter = function( self, metadata ) {
    return function( criteria = {} ) {
        if ( !self.initialized ) {
            var errorMessage = 'Error: ArtworkFilterer is not initialized!\n' +
                               'Call .init() and supply a callback = function( err, filter ).\n' +
                               'then, use filter() to filter artworks!';

            throw new Error( errorMessage );

        }

        // Noramalize dates in the test criteria.
        var test_criteria = preprocess( criteria, metadata );

        //console.log( self.artworks );

        // Get the artworks taht are inbounds of the dates.
        var inbounds_artworks = self.artworks.filter( function( artwork ) {

            var after = true, before = true;



            if ( typeof test_criteria.from !== 'undefined' ) {

                after = artwork.dates.created.isSameOrAfter( test_criteria.from );

            }

            if ( typeof test_criteria.to !== 'undefined' ) {

                before = artwork.dates.created.isSameOrBefore( test_criteria.to );

            }

            // console.log( artwork.dates.created );
            // console.log( test_criteria.from );
            // console.log( after );
            // console.log( test_criteria.to  );
            // console.log( before );


            return before && after;

        });



        // Delete the date keys.
        delete test_criteria.from;
        delete test_criteria.to;
        delete test_criteria.year;

        var final_artworks = inbounds_artworks.filter( function( artwork ) {

            return reduce( test_criteria, function( acc, criterion_value, criterion_key ) {

                return (acc && typeof criterion_value === 'undefined') || (acc && artwork[ criterion_key ] === criterion_value) || (acc && typeof artwork[ criterion_key ] === 'undefined');

            }, true);

        });

        self.currentState = final_artworks.slice( 0 );

        return final_artworks;

    };
};

/**
 * Given a new set of filter criteria,
 * compute the diff, in terms of artworks added
 * and removed, represented by the change in filtering
 * parameters.
 *
 * @see filter();
 * @param criteria.medium ?string a medium to match artwork against
 * @param criteria.program ?string a program to match artwork against
 * @param criteria.installed_on_or_after ?moment a moment data object representing the first possible install date inclusive.
 * @param criteria.installed_on_or_before ?moment a moment data object representing the last possible install date inclusive.
 * @param criteria.on_view_now ?boolean a boolean indicating whether to get only art on view, or only art not on view.
 * @param criteria.featured ?boolean a boolean indicating whether the artwork is featured or not.
 *
 * @return diff.add Array<Artwork> an array of artwork added by this filter step.
 * @return diff.remove Array<Artwork> an array of artwork removed by this filter step.
 */
var diff = function( self ) {
    return function ( criteria ) {

        criteria = objectAssign( {}, criteria );

        var oldState = self.currentState.slice(0).map( function( a ) { return { artwork: a, removed: true }; });
        var newState = filter( self )( criteria ).map( function( a ) { return { artwork: a, added: true }; });

        // console.log( oldState );
        // console.log( newState );

        var diffObject = {};

        newState.forEach( function( newArtwork ) {

            for ( var i = 0; i < oldState.length; i++ ) {

                var oldArtwork = oldState[ i ];

                if ( newArtwork.artwork.equals( oldArtwork.artwork ) ) {
                    // If we find an artwork in both the old
                    // and new state, we just remove it from the
                    // current state, since it represents no change.
                    // -=-
                    // we know we don't need to check that artwork
                    // again, since we know we're working with sets.
                    // -=-
                    // after iteration is complete, currentState will
                    // represent the elements that were removed from
                    // the set in the state diff.
                    oldArtwork.removed = false;
                    newArtwork.added = false;
                    break;

                }

            }

        });

        diffObject.add = newState.filter( function( n ) { return n.added; }).map( function( a ) { return a.artwork; });
        diffObject.remove = oldState.filter( function( o ) { return o.removed; }).map( function( a ) { return a.artwork; });

        return diffObject;

    };
};


/**
 * Given a set of dimensions, get the set of admissable values
 * for those dimensions as present on the total set of artwork
 * being filtered.
 */
var values = function( self ) {
    return function( dimension, selector = function( x ) { return x; } ) {

        var values= {};

        self.artworks.forEach( function( artwork ) {

            if ( typeof artwork[dimension] !== 'undefined' && artwork[dimension] !== null && Object.keys( artwork[dimension] ).length !== 0 ) {

                values[ selector( artwork[ dimension ] ) ] = artwork[ dimension ];

            }

        });

        return Object.values( values );

    };
};


ArtworkFilterer.prototype.init = function( callback, featured = false ) {
    var self = this;

    if ( self.initialized ) { callback( null, filter( self ), diff( self ), values( self ) ); }

    self.zoho.getArtworks( undefined, function( err, artworks ) {
        if ( err ) { callback( err ); }

        self.initialized = true;
        self.artworks = artworks;
        self.currentState = artworks;

        callback( null, filter( self ), diff( self ), values( self ) );

    });

};


ArtworkFilterer.prototype.getCurrentState = function() { return this.currentState; };


export { ArtworkFilterer };
