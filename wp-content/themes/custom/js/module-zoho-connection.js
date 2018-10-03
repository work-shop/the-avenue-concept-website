'use strict';

/**
 * file: module-zoho-connection.js
 *
 * This file gets artwork from zoho, caching results, and
 * serving them back for further processing. This module
 * is callback driven, and makes asynchronous requests.
 */
var $ = require('jquery');
var async = require('async');

const zoho_base_uri = 'https://creator.zoho.com/api';

const ownername = 'the_avenue_concept';
const database_name = 'artworks-database';
const view_name = 'All_Public_Artwork';
const authtoken = '1864b7e6b391f6b94d5cae6a8a9bbd60';

import { Artwork } from './module-zoho-artwork.js';


/**
 * Given a viewname, optional condition selector, and optional fieldname selector,
 * make a Zoho API request URI.
 */
function makeZohoUri( viewname, condition = false, fieldname = false ) {
    var uri =  [
            [zoho_base_uri, 'json', database_name, 'view', viewname ].join('/'),
            '?authtoken='+authtoken+'&scope=creatorapi&zc_ownername='+ownername,
            ( condition ) ? '&criteria=(' +condition + ')' : '',
            ( fieldname ) ? '&' + fieldname : ''

        ].join('');


    return uri;
}

/**
 * Given a viewname, filtering criterion, and optional selector function, return
 * a function that can be invoked with a callback to get a set of resources from zoho.
 *
 */
function get_resource( view_name, criterion, fieldname, selector = function( x ) { return x; } ) {

    var uri = makeZohoUri( view_name, criterion, fieldname );

    return function( done ) {
        $.ajax({
            crossDomain: true,
            url: uri,
            dataType: 'jsonp',
            type: 'GET',
            success: function( v ) { done( null, selector( v ) ); },
            error: done
        });
    };
}


/**
 * Given a string representation of an array '[a,b,c]',
 * return an array of strings ['a', 'b', 'c'].
 */
function unpackStringArray( arr ) {

    var result = arr.split(',');

    result[0] = result[0].substring( 1 );

    result[ result.length - 1 ] = result[ result.length - 1 ].substring( 0, result[ result.length - 1 ].length - 1 );

    result = result.map( function( m, i ) {
        if ( i > 0 ) { return m.substring( 1 ); }
        else { return m; }
    });

    return result;

}


/**
 * ZohoConnection Module.
 * This module
 */
function ZohoConnection() {
    console.log('creating new ZohoConnection instance.');
    if ( !(this instanceof ZohoConnection)) { return new ZohoConnection(); }
    var self = this;


    self.getArtworks = function( parameters, callback = function() {} ) {

        $.ajax({
            crossDomain: true,
            url: makeZohoUri( view_name ),
            dataType: 'jsonp',
            type: 'GET',
            success: function( d ) {

                async.parallel(
                    d.Add_Artwork.map( function( artwork ) {
                        return function( artwork_done ) {

                            async.parallel({
                                artist: get_resource( 'All_Artists', 'Name == \"' + artwork.Add_Artist + '\"', false, function( x ) { return x.Add_Artist; } ),
                            //    get_resource( 'All_Locations', 'Location_Name == \"'+ artwork.Location_Name +'\"', function( x ) { return x.Add_Location ),
                                media: function( media_done ) {

                                    async.parallel( unpackStringArray( artwork.Add_Media ).map( function( media_name ) {
                                        return get_resource( 'All_Medias', 'Media_Title == \"' + media_name + '\"', false, function( x ) { return x.Add_Media; });
                                    }), function( err, values ) {
                                        if ( err ) { media_done( err ); }
                                        media_done( null, values.reduce( function( a,b ) { return a.concat( b ); }, []));
                                    });

                                }

                            }, function( err, values ) {

                                if ( err ) { artwork_done( err ); }

                                artwork.Add_Artist = values.artist;
                                artwork.Add_Media = values.media;

                                artwork_done( null, artwork );

                            });

                        };
                    }),
                    function( err, artworks ) {
                        if ( err ) { callback( err ); }

                        callback( null, artworks.map( function( artwork ) {

                            artwork.Medium_field1 = unpackStringArray( artwork.Medium_field1 );

                            return new Artwork( artwork );

                        } ) );
                    }
                );

            },
            error: callback
        });

    };

}

export { ZohoConnection };
