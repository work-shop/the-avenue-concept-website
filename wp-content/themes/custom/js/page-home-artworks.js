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
function isHomePage() { return $(document.body).hasClass('home'); }

/**
 * This class manages getting and rendering artworks on the home page.
 */
function HomePageArtworksManager() {
    if ( !(this instanceof HomePageArtworksManager)) { return new HomePageArtworksManager(); }

    console.log('HomePageArtworksManager loaded');
}

/**
 * This method sets up the home page artworks logic.
 */
HomePageArtworksManager.prototype.init = function() {
    console.log('HomePageArtworksManager.init() called');

    return this;
};


export { HomePageArtworksManager, isHomePage };
