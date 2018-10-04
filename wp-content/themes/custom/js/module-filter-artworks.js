'use strict';

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
function ArtworkFilterer( zoho ) {
    console.log('creating new ArtworkFilterer instance.');
    if ( !(this instanceof ArtworkFilterer)) { return new ArtworkFilterer( zoho ); }
    var self = this;

    /**
     * @member zoho ZohoConnection the connection to the zoho database.
     */
    self.zoho = zoho;

}

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
ArtworkFilterer.prototype.filter = function( criteria = {} ) {
    console.error('ArtworkFilterer.filter() is not implemented!');
    return [];
};

export { ArtworkFilterer };
