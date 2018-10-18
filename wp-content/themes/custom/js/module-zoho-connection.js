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
var json = require('json-serialize');
//const LocalStorage = require('localstorage');

const zoho_base_uri = 'https://creator.zoho.com/api';

const ownername = 'the_avenue_concept';
const database_name = 'artworks-database';
const view_name = 'All_Public_Artwork';
const authtoken = '1864b7e6b391f6b94d5cae6a8a9bbd60';

import { Artwork, mapMediaType } from './module-zoho-artwork.js';


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

    //console.log( uri );

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

    if ( typeof arr === 'undefined' || arr.length === 0 ) { return []; }
    if ( arr.charAt(0) !== '[' ) { return [ arr ]; }

    var result = arr.split(', ');

    result[0] = result[0].substring( 1 );

    result[ result.length - 1 ] = result[ result.length - 1 ].substring( 0, result[ result.length - 1 ].length - 1 );

    // result = result.map( function( m, i ) {
    //     if ( i > 0 ) { return m.substring( 1 ); }
    //     else { return m; }
    // });

    return result;

}

function parseBoolean( str ) {
    return str.trim().toLowerCase() === 'true';
}


function processMediaObjects( media ) {
    var result = [];

    media.Media_Type.forEach( function( type, type_i ) {

        var media_item = {
            Media_Type: type,
            ID: media.ID[ type_i ],
            Photographer_Author: media.Photographer_Author[ type_i ],
            Media_Title: media.Media_Title[ type_i ],
            Website_Featured_Image: parseBoolean( media.Website_Featured_Image[ type_i ] ),
            Image: media.Image[ type_i ],
            Video_URL: media.Video_URL[ type_i ],
            Media_File: media.Media_File[ type_i ],
            Vimeo_or_Youtube: media.Vimeo_or_Youtube[ type_i ]
        };

        result.push( media_item );

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


    self.getArtworks = function( slug, callback = function() {} ) {

        //console.log( makeZohoUri( view_name, ( slug ) ? 'Slug == \"'+ slug +'\"' : undefined ) );

        $.ajax({
            crossDomain: true,
            url: makeZohoUri( view_name, ( slug ) ? 'Slug == \"'+ slug +'\"' : undefined ),
            dataType: 'jsonp',
            type: 'GET',
            success: function( d ) {

                var artworks = d.Add_Artwork.map( function( artwork ) {

                    artwork.Add_Artist = [{
                        ID: artwork['Add_Artist.ID'],
                        Name: artwork['Add_Artist.Name'],
                        Biography: artwork['Add_Artist.Biography'],
                        Country_Of_Origin: artwork['Add_Artist.Country_Of_Origin'],
                        Current_Location: artwork['Add_Artist.Current_Location'],
                        Website: artwork['Add_Artist.Website']
                    }];

                    artwork.Add_Location = [{
                        ID: artwork['Add_Location.ID'],
                        Location_Name: artwork['Add_Location.Location_Name'],
                        Latitude: artwork['Add_Location.Latitude'],
                        Longitude: artwork['Add_Location.Longitude'],
                    }];

                    artwork.Add_Media = processMediaObjects({
                        Media_Title: unpackStringArray( artwork['Add_Media.Media_Title'] ),
                        Public: unpackStringArray( artwork['Add_Media.Public'] ),
                        Image: unpackStringArray( artwork['Add_Media.Image'] ),
                        Media_Type: unpackStringArray( artwork['Add_Media.Media_Type'] ),
                        Video_URL: unpackStringArray( artwork['Add_Media.Video_URL'] ),
                        Media_File: unpackStringArray( artwork['Add_Media.Media_File'] ),
                        ID: unpackStringArray( artwork['Add_Media.ID'] ),
                        Photographer_Author: unpackStringArray( artwork['Add_Media.Photographer_Author'] ),
                        Website_Featured_Image: unpackStringArray( artwork['Add_Media.Website_Featured_Image'] ),
                        Vimeo_or_Youtube: unpackStringArray( artwork['Add_Media.Vimeo_or_Youtube'] )
                    });

                    artwork.Partners_Sponsors = unpackStringArray( artwork['Partners_Sponsors.Name'] );

                    artwork.Medium_field1 = unpackStringArray( artwork.Medium_field1 );

                    return new Artwork( artwork );

                });

                callback( null, artworks );



                // async.parallel(
                //     d.Add_Artwork.map( function( artwork ) {
                //         return function( artwork_done ) {
                //
                //             async.parallel({
                //                 media: function( media_done ) {
                //
                //                     async.parallel( unpackStringArray( artwork.Add_Media ).map( function( media_name ) {
                //                         return get_resource( 'All_Public_Media', 'Media_Title == \"' + media_name + '\"', false, function( x ) { return x.Add_Media; });
                //                     }), function( err, values ) {
                //                         if ( err ) { media_done( err ); }
                //                         media_done( null, values.reduce( function( a,b ) { return a.concat( b ); }, []));
                //                     });
                //
                //                 }
                //
                //             }, function( err, values ) {
                //
                //                 if ( err ) { artwork_done( err ); }
                //
                //                 artwork.Medium_field1 = unpackStringArray( artwork.Medium_field1 );
                //
                //                 artwork.Add_Artist = [{
                //                     ID: artwork['Add_Artist.ID'],
                //                     Name: artwork['Add_Artist.Name'],
                //                     Biography: artwork['Add_Artist.Biography'],
                //                     Country_Of_Origin: artwork['Add_Artist.Country_Of_Origin'],
                //                     Current_Location: artwork['Add_Artist.Current_Location'],
                //                     Website: artwork['Add_Artist.Website']
                //                 }];
                //
                //                 artwork.Add_Location = [{
                //                     ID: artwork['Add_Location.ID'],
                //                     Location_Name: artwork['Add_Location.Location_Name'],
                //                     Latitude: artwork['Add_Location.Latitude'],
                //                     Longitude: artwork['Add_Location.Longitude'],
                //                 }];
                //
                //                 artwork.Add_Media = values.media;
                //
                //                 artwork = new Artwork( artwork )
                //
                //                 artwork_done( null, artwork );
                //
                //             });
                //
                //         };
                //     }),
                //     callback
                // );

            },
            error: callback
        });

    };

}

export { ZohoConnection };
