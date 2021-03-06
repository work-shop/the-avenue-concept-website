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
 * extract the name of the image from
 * the internal ZOho image src string.
 */
 function extractImageName( url ) {
    return url.substring( url.indexOf( split_string ) + split_string.length );
}


/**
 *
 *
 *
 */
 function mapTrueImageSource( id, image_name ) {
    return creator_export +
    user_name + '/' +
    database_name + '/' +
    view_name + '/' +
    id + '/' +
    'Image/image-download/' +
    media_encryption_key +
    '?filepath=/' + image_name;
}

/**
 * Given a media object corresponding to an image,
 * this routine parses out the image URLs that
 * grab that image either from zoho or whatever
 * remote location the image is being hosted at.
 */
 function createImageSources( image_html, media ) {

    console.log('create image source');

    var $ = cheerio.load( image_html );
    var image = $('img');

    //if ( media.Website_Featured_Image ) {
        //console.log( image_html );
    //}

    var src = image.attr('src');
    var low = image.attr( 'lowqual');
    var med = image.attr( 'medqual');

    //console.log(src);

    if( typeof src !== 'undefined' ){

        if ( src.indexOf( '://' ) === -1 ) {

            var true_image_src = mapTrueImageSource( media.ID, extractImageName( src ) );

            var true_image_low = (typeof low === 'undefined') ? true_image_src : mapTrueImageSource( media.ID, extractImageName( low ) );
            var true_image_med = (typeof med === 'undefined') ? true_image_src : mapTrueImageSource( media.ID, extractImageName( med ) );

            // console.log('qualities:');
            // console.log( true_image_src );
            // console.log( true_image_med );
            // console.log('');

            return {
                type: 'zoho',
                has_low_quality_versions: (typeof low !== 'undefined') || (typeof med !== 'undefined'),
                src: true_image_src,
                high: true_image_src,
                med: true_image_med,
                low: true_image_low
            };

        } else {

            return {
                type: 'other',
                has_low_quality_versions: false,
                src: src,
                high: src,
                med: src,
                low: src
            };

        }

    } else {

        return {
            type: 'other',
            has_low_quality_versions: false,
            src: src,
            high: src,
            med: src,
            low: src
        };

    }

}


/**
 * This function builds preformatted resize urls
 * from Google Cloud Storage urls.
 */
 function createResizedImages( resize_url ) {

    resize_url = resize_url.replace(/^http:\/\//i, 'https://');

    return {
        type: 'zoho',
        has_low_quality_versions: true,
        src: resize_url,
        high: resize_url + '=s1920-rj-l85',
        med: resize_url + '=s1200-rj-l85',
        low: resize_url + '=s768-rj-l85'
    };

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
            featured: media_object.Website_Featured_Image,
            resize_url: media_object.Resize_URL
        };

        if ( media_result.type === 'image' ) {

            if ( media_result.resize_url !== '' ) {

                media_result.image = createResizedImages( media_result.resize_url );

            } else {
                console.log('no resize url');
                console.log(media_object);
                media_result.image = createImageSources( media_object.Image, media_object );

            }

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

    self.artwork_blog_post_link = data.Artwork_Blog_Post_Link;
    self.press_coverage = data.Press_Coverage;
    //console.log( data.Press_Coverage );

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
