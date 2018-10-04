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

    var filterer = new ArtworkFilterer();
    var renderer = new ArtworkRenderer();
    var urlmanager = new URLManager();

    console.log( urlmanager.parseURL() );

    // filterer.init( function( error, filter ) {
    //
    //
    //
    // });

    return this;
};



function artworksArchive() {

    var archive = new ArtworksArchiveManager();

    archive.init();

}

export { artworksArchive, isArtworksArchive };
