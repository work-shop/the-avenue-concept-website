'use strict';

var moment = require('moment');
var cheerio = require('cheerio');



const base_url = 'artworks';


const creator_export = 'https://creatorexport.zoho.com/file/';
const user_name = 'the_avenue_concept';
const database_name = 'artworks-database';
const view_name = 'All_Public_Media';
const split_string = 'image-download/';


const media_encryption_key = 'rOrBktNV54sebRgAnUCpp6TGghu26QsJJG2u5feg6CMKT0mmyhnguqhg0QwAeC00xKa76zfJCw6UZtwTfzuFkzYzNpmKpUw46OsG';


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
function createImageSources( image_html, media ) {

    var $ = cheerio.load( image_html );
    var image = $('img');

    var src = image.attr('src');

    if ( src.indexOf( '://' ) === -1 ) {

        var image_name = src.substring( src.indexOf( split_string ) + split_string.length );

        var true_image_src =
            creator_export +
            user_name + '/' +
            database_name + '/' +
            view_name + '/' +
            media.ID + '/' +
            'Image/image-download/' +
            media_encryption_key +
            '?filepath=/' + image_name;

        return {
            type: 'zoho',
            src: true_image_src
        };

    } else {

        return {
            type: 'other',
            src: src
        };

    }

}


/**
 * Given a zoho video URL field, stored as an a tag,
 * parses the a tag, extracts the href, and returns that.
 *
 * @param string video url field
 * @return string the video's url
 */
function createVideoSource( video_url, vimeo_or_youtube ) {
    var $ = cheerio.load( video_url );
    var image = $('a');

    var src = image.attr('href');

    return {
        type: vimeo_or_youtube,
        src: src
    };
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

            media_result.image = createImageSources( media_object.Image, media_object );

        } else if ( media_result.type === 'video' ) {

            media_result.video_url = createVideoSource( media_object.Video_URL, media_object.Vimeo_or_Youtube );

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
function createLocationObject( location ) {
    if ( location.length === 1 ) {

        return location[0].Location_Name;

    } else {

        return '';

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

    //console.log( data );

    self.name = data.Artwork_Title;
    self.description = data.Artwork_Description;
    self.slug = data.Slug;
    self.url = '/' + base_url + '/' + self.slug;
    self.id = data.ID;

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
    self.on_view = data.On_View_Now;

    self.program = data.Program;
    self.medium = data.Medium_field1;
    self.partners_and_sponsors = data.Partners_Sponsors;

    if ( data.Latitude.length !== 0 && data.Longitude.length !== 0 ) {

        self.position = {
            lat: data.Latitude,
            lng: data.Longitude
        };

    } else if ( typeof data.Add_Location !== 'undefined' && typeof data.Add_Location.Latitude === 'number' && typeof data.Add_Location.Longitude === 'number' ) {

        self.position = {
            lat: data.Add_Location.Latitude,
            lng: data.Add_Location.Longitude
        };

    }
}

/**
 * Returns true if this artwork has a valid set of Lat / Lng coordinates
 * for use with the map.
 *
 * @return bool true if this artwork has a valid coordinate pair, false otherwise.
 */
Artwork.prototype.hasLatLng = function() {
    return (typeof this.position !== 'undefined') && (typeof this.position.lat === 'number') && (typeof this.position.lng === 'number');
};


/**
 * Given a test object, returns true of this and the other object represent
 * the same artwork.
 *
 * @param object other test object to check equality against
 * @return true if other is equal to this
 */
Artwork.prototype.equals = function( other ) {
    return other instanceof Artwork && other.id === this.id;
};


export { Artwork, mapMediaType };
