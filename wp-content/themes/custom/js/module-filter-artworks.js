'use strict';

/**
 * file: module-filter-artworks.js
 *
 * This file filters raw API responses from Zoho down to
 * the set of queried posts.
 */

function ArtworkFilterer() {
    console.log('creating new ArtworkFilterer instance.');
    if ( !(this instanceof ArtworkFilterer)) { return new ArtworkFilterer(); }


}

module.exports = ArtworkFilterer;
