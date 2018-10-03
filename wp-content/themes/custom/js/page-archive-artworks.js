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

    return this;
};


export { ArtworksArchiveManager, isArtworksArchive };
