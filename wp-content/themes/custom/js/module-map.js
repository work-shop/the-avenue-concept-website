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
            streetViewControl: false,
            // gestureHandling: 'greedy',
            styles: [
                {
                    'featureType': 'poi.business',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'poi.park',
                    'stylers': [
                        {
                            'visibility': 'on'
                        }
                    ]
                },
                {
                    'featureType': 'poi.park',
                    'elementType': 'labels.text',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'poi.school',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'road.arterial',
                    'elementType': 'labels.icon',
                    'stylers': [
                        {
                            'visibility': 'off'
                        }
                    ]
                },
                {
                    'featureType': 'road.highway',
                    'stylers': [
                        {
                            'color': '#ffffff'
                        }
                    ]
                },
                {
                    'featureType': 'road.highway',
                    'elementType': 'geometry.stroke',
                    'stylers': [
                        {
                            'color': '#e1e1e1'
                        }
                    ]
                }
                ]

            } )[0];

    }

    return this;
};

/**
 * Given a set of map-ready marker objects,
 * update the map module with the set of valid maerk
 */
 ArtworksMap.prototype.update = function( objects, options ) {

    var validated = this.validate( objects );

    this.map.data( validated.valid ).removeFeatures().render( options );

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
