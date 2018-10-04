'use strict';

var moment = require('moment');
var cheerio = require('cheerio');
const creator_export = 'https://creatorexport.zoho.com';

/**
 * Media Type Mapper
 *
 * This routine determines the goverining media class for a given
 * piece of attached media.
 */
function mapMediaType( media_type ) {

    if ( media_type.toLowerCase().indexOf('image') !== -1 ) {

        return 'image';

    } else if ( media_type.toLowerCase().indexOf('video') !== -1 ) {

        return 'video';

    } else {

        return 'other';

    }

}

/**
 * Given a media object corresponding to an image,
 * this routine parses out the image URLs that
 * grab that image either from zoho or whatever
 * remote location the image is being hosted at.
 */
function createImageSources( image_html ) {

    var $ = cheerio.load( image_html );
    var image = $('img');

    var src = image.attr('src');
    var lowqual = image.attr('lowqual');
    var downqual = image.attr('downqual');
    var medqual = image.attr('medqual');

    if ( src.indexOf( '://' ) === -1 ) {

        return {
            type: 'zoho',
            src: creator_export + src,
            lowqual: ( lowqual ) ? creator_export + lowqual : false,
            downqual: ( downqual ) ? creator_export + downqual : false,
            medqual: ( medqual ) ? creator_export + medqual : false,
        };

    } else {

        return {
            type: 'other',
            src: src
        };

    }

}

function createVideoSource( video_url ) {
    console.log( 'Artwork.createVideoSource: This method is not implemented!');
    return video_url;
}

function createFileSource( media_file ) {
    console.log( 'Artwork.createMediaObject: This method is not implemented!');
    return media_file;
}

/**
 * Media Extractor Function
 *
 * Given a raw All_Medias response from Zoho,
 * parse the repsonse into a simpler represetation
 * for further processing.
 */
function createMediaObject( media ) {

    var featured_results = [];

    var media_results = media.map( function( media_object ) {

        var media_result = {
            type: mapMediaType( media_object.Media_Type ),
            media_type: media_object.Media_Type,
            photographer: media_object.Photographer_Author,
            id: media_object.ID,
            title: media_object.Media_Title,
            featured: media_object.Website_Featured_Image
        };

        if ( media_result.type === 'image' ) {

            media_result.image = createImageSources( media_object.Image );

        } else if ( media_result.type === 'video' ) {

            media_result.video_url = createVideoSource( media_object.Video_URL );

        } else {

            media_result.file_url = createFileSource( media_object.Media_File );

        }

        if ( media_result.featured ) { featured_results.push( media_result ); }

        return media_result;

    });

    return [ media_results, featured_results ];

}


/**
 * Artist Extractor Function
 *
 * Given a raw All_Artists response from Zoho,
 * parse the repsonse into a simpler represetation
 * for further processing.
 */
function createArtistObject( artists ) {

    return artists.map( function( artist ) {

        return {
            id: artist.ID,
            name: artist.Name,
            biography: artist.Biography,
            website: $( artist.Website ).attr('href')
        };

    });

}


/**
 * Artist Extractor Function
 *
 * Given a raw All_Artists response from Zoho,
 * parse the repsonse into a simpler represetation
 * for further processing.
 */
function createLocationObject( locations ) {
    if ( locations.length === 0 ) {

        return {};

    } else {

        console.log('Artwork.createLocationObject: This method is not implemented!');
        return {};

    }
}

/**
 * The Artwork object onsolidates all of the info about
 * an artwork – including related information like artist,
 * media, and location – needed for rendering a specific artwork
 * to the page as a page or as a card, map icon, or list row.
 *
 *
 */
function Artwork( data ) {
    if (!(this instanceof Artwork)) { return new Artwork( data ); }
    var self = this;

    console.log( data );

    self.name = data.Artwork_Title;
    self.description = data.Artwork_Description;

    self.dates = {
        created: moment( data.Date_Created, 'DD-MMM-YYYY HH:mm:ss' ),
        installed: moment( data.Date_Installed, 'DD-MMM-YYYY HH:mm:ss' )
    };

    var media = createMediaObject( data.Add_Media );

    self.artist = createArtistObject( data.Add_Artist );
    self.media = media[0];
    self.location = createLocationObject( data.Add_Location );
    self.featured_media = media[1][0] || {};
    self.featured = data.Feature_Artwork_on_Homepage;

    self.program = data.Program;
    self.medium = data.Medium_field1;

    if ( data.Latitude.length !== 0 && data.Longitude.length !== 0 ) {

        self.position = {
            lat: data.Latitude,
            lng: data.Longitude
        };

    } else if ( typeof data.Add_Location !== 'undefined' && data.Add_Location.length > 0 && data.Add_Location.Latitude.length !== 0 && data.Add_Location.Longitude.length !== 0 ) {

        self.position = {
            lat: data.Add_Location.Latitude,
            lng: data.Add_Location.Longitude
        };

    }

}

export { Artwork };
