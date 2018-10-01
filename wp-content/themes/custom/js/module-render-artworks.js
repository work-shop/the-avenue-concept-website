'use strict';

/**
 * file: module-render-artworks.js
 *
 * This file constructs jQuery representations of artwork data
 * and returns for further processing.
 */

function ArtworkRenderer() {
    console.log('creating new ArtworkRenderer instance.');
    if ( !(this instanceof ArtworkRenderer)) { return new ArtworkRenderer(); }


}

module.exports = ArtworkRenderer
