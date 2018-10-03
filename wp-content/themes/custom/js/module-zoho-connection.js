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


function ZohoConnection() {
    console.log('creating new ZohoConnection instance.');
    if ( !(this instanceof ZohoConnection)) { return new ZohoConnection(); }
    var self = this;

    const options = {
        method: 'GET',
        uri: [zoho_base_uri, 'json', database_name, 'view', view_name ].join('/') ,
        headers: {
            'Access-Control-Allow-Origin': '*',
            'Content-Type': 'text/plain'
        },
        qs: {
            authtoken: authtoken,
            scope: 'creatorapi',
            zc_ownername: 'the_avenue_concept',
            raw: true
        }
    };


    function makeZohoUri( viewname, condition = false, fieldname = false ) {
        var uri =  [
                [zoho_base_uri, 'json', database_name, 'view', viewname ].join('/'),
                '?authtoken='+authtoken+'&scope=creatorapi&zc_ownername='+ownername,
                ( condition ) ? '&criteria=(' +condition + ')' : '',
                ( fieldname ) ? '&' + fieldname : ''

            ].join('');


        return uri;
    }


    function getMediaCriterion( artwork_media ) {

        var media = artwork_media.split(',');

        media[0] = media[0].substring( 1 );

        media[ media.length - 1 ] = media[ media.length - 1 ].substring( 0, media[ media.length - 1 ].length - 1 );

        media = media.map( function( m, i ) {
            if ( i > 0 ) { return m.substring( 1 ); }
            else { return m; }
        });

        return media;

    }

    function get_resource( view_name, criterion, fieldname, selector = function( x ) { return x; } ) {

        var uri = makeZohoUri( view_name, criterion, fieldname );

        return function( artist_done ) {
            $.ajax({
                crossDomain: true,
                url: uri,
                dataType: 'jsonp',
                type: 'GET',
                success: function( v ) {
                    artist_done( null, selector( v ) );
                },
                error: artist_done
            });
        };
    }


    self.getArtworks = function( parameters, callback = function() {} ) {


        $.ajax({
            crossDomain: true,
            url: [options.uri, '?authtoken='+authtoken+'&scope=creatorapi&zc_ownername='+ownername].join(''),
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

                                    async.parallel( getMediaCriterion( artwork.Add_Media ).map( function( media_name ) {
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

                            artwork.Medium_field1 = getMediaCriterion( artwork.Medium_field1 );

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
