'use strict';

/**
 * file: module-zoho-connection.js
 *
 * This file gets artwork from zoho, caching results, and
 * serving them back for further processing. This module
 * is callback driven, and makes asynchronous requests.
 */

const Zoho = require('zoho');

const database_name = 'artworks-database';
const view_name = 'All_Public_Artwork';
const authtoken = '1864b7e6b391f6b94d5cae6a8a9bbd60';

function ZohoConnection() {
    console.log('creating new ZohoConnection instance.');
    if ( !(this instanceof ZohoConnection)) { return new ZohoConnection(); }
    var self = this;

    var creator = new Zoho.Creator({
        authtoken: authtoken
    });



    self.getArtworks = function( parameters, callback ) {

        creator.viewRecordsInView( database_name, view_name, {}, function( err, data ) {
            console.log( err );
            console.log( data );
        });

        // callback({
        //     'error': true,
        //     'message': 'The ZohoConnection module is currently unimplemented'
        // });

    };

}

export { ZohoConnection };
