'use strict';

import { ZohoConnection } from './module-zoho-connection.js';

var reduce = require('reduce-object');
var moment = require('moment');

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
    console.log('creating new ArtworkFilterer instance.');
    if ( !(this instanceof ArtworkFilterer)) { return new ArtworkFilterer(); }
    var self = this;

    /**
     * @member zoho ZohoConnection the connection to the zoho database.
     */
    self.zoho = new ZohoConnection();
    self.artworks = [];
    self.initialized = false;

}



/**
 * Given a set of criteria from the URL manager, replaces
 * certain keys with values appropriate for filtering artwork
 * against.
 *
 *
 *
 */
var preprocess = function( criteria, metadata = { date_parse_string: 'DD-MMM-YYYY'} ) {
    if ( typeof criteria.year !== 'undefined' ) {

        criteria.from = moment( '01-01-' + criteria.year, metadata.date_parse_string );
        criteria.to = moment( '31-12-' + criteria.year, metadata.date_parse_string );

        delete criteria.year;

    } else {

        if ( typeof criteria.from !== 'undefined' ) {

            criteria.from = moment( criteria.from, metadata.date_parse_string );

        }

        if ( typeof criteria.from !== 'undefined' ) {

            criteria.from = moment( criteria.from, metadata.date_parse_string );

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

        // Get the artworks taht are inbounds of the dates.
        var inbounds_artworks = self.artworks.filter( function( artwork ) {

            var after = true, before = true;

            if ( typeof test_criteria.from !== 'undefined' ) {

                after = artwork.dates.created.isSameOrAfter( test_criteria.from );

            }

            if ( typeof test_criteria.to !== 'undefined' ) {

                before = artwork.dates.created.isSameOrBefore( test_criteria.to );

            }

            return before && after;

        });

        delete test_criteria.from;
        delete test_criteria.to;

        return inbounds_artworks.filter( function( artwork ) {

            return reduce( test_criteria, function( acc, criterion_value, criterion_key ) {

                return (acc && artwork[ criterion_key ] === criterion_value) || (acc && typeof artwork[ criterion_key ] === 'undefined');

            }, true);

        });

    };
};


ArtworkFilterer.prototype.init = function( callback, featured = false ) {
    var self = this;

    if ( self.initialized ) { callback( null, filter ); }

    self.zoho.getArtworks( { featured: featured }, function( err, artworks ) {
        if ( err ) { callback( err ); }

        self.initialized = true;
        self.artworks = artworks;

        callback( null, filter( self ) );

    });

};



export { ArtworkFilterer };
