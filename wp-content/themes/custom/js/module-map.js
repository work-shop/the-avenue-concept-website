'use strict';

var makeMap = require('@work-shop/map-module');

function ArtworksMap( selector, config ) {
    if ( !(this instanceof ArtworksMap)) { return new ArtworksMap( selector, config ); }

    this.selector = selector;
    this.config = config;

}


ArtworksMap.prototype.init = function() {
    if ( typeof this.map === 'undefined' ) {

        this.map = makeMap({

            selector: this.selector,
            map: { streetViewControl: false }

        } )[0];

    }

    console.log( this.map );

    return this;
};

/**
 * Given a set of map-ready marker objects,
 * update the map module with the set of valid maerk
 */
ArtworksMap.prototype.update = function( objects ) {

    var validated = this.validate( objects );

    this.map.data( validated.valid ).removeFeatures().render();

    return validated;

};

/**
 *
 *
 */
ArtworksMap.prototype.validate = function( objects ) {

    var invalid = [];

    return {

        valid: objects.filter( function( object ) {

            var valid = object.marker.hasLatLng();

            if ( !valid ) {
                invalid.push( object );
                console.log( `Artwork \"${ object.marker.name }\" (id: ${ object.marker.id }) has no lat/lng position and was omitted from the map.` );
            }

            return valid;

        }),

        invalid: invalid

    };

};



export { ArtworksMap };
