'use strict';

/**
 * file: page-home-artworks.js
 *
 * This file runs the state-management, artwork getting, and artwork rendering
 * for the /home page on the site.
 */

/**
 * This method returns true if the current page is the home page,
 * false otherwise.
 */
function isArtworksSingle() { console.error('isSingleArtworks is unimplemented!'); return false; }

/**
 * This class manages getting and rendering artworks on the home page.
 */
function SingleArtworksManager() {
    if ( !(this instanceof SingleArtworksManager)) { return new SingleArtworksManager(); }

    console.log('SingleArtworksManager loaded.');
}

/**
 * This method sets up the home page artworks logic.
 */
SingleArtworksManager.prototype.init = function() {
    console.log('SingleArtworksManager.init() called');

    return this;
};


function singleArtwork() {

    var single = new SingleArtworksManager();

    single.init();

}


export { singleArtwork, isArtworksSingle };
