'use strict';

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
function isArtworksArchive() { console.error('isArtworksArchive is unimplemented'); return false; }

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

    var query = {
        from: '01-01-2017',
        to: '12-31-2017'
    };

    return this;
};



function artworksArchive() {

    var archive = new ArtworksArchiveManager();

    archive.init();

}

export { artworksArchive, isArtworksArchive };
