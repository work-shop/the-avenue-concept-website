'use strict';


import createHistory from 'history/createBrowserHistory';
var queryString = require('query-string');
var objectAssign = require('object-assign');

const base_url = '/artworks';
const prequery_seperator = '?';

var currentState = [];

/**
 * file: module-url-manager.js
 *
 * This file manages URL state with history push state.
 */

function URLManager( defaultState = { view: 'thumbnails', 'on-view': true } ) {
    //console.log('creating new URLManager instance.');
    if ( !(this instanceof URLManager)) { return new URLManager( defaultState ); }
    var self = this;

    self.defaultState = objectAssign( {}, defaultState );

    self.history = createHistory();

}

/**
 * Calling this module parses the current URL into
 * a set of filtering parameters that can be passed
 * to the filter module.
 *
 * By default, 'year' takes precendence over 'to' and 'from' in the query string.
 *
 * Urls for filtering are represented by query strings:
 *
 * ?view={viewtype}
 * &on-view=urlEncode({ installed_on_or_after })
 * &program=urlEncode({ program })
 * &from=urlEncode({ installed_on_or_after })
 * &to=urlEncode({ installed_on_or_before })
 *
 * @return criteria.medium ?string a medium to match artwork against
 * @return criteria.program ?string a program to match artwork against
 * @return criteria.from ?string a moment data object representing the first possible install date inclusive.
 * @return criteria.to ?string a moment data object representing the last possible install date inclusive.
 * @return criteria.on_view ?boolean a boolean indicating whether to get only art on view, or only art not on view.
 */
URLManager.prototype.parseURL = function( withDefaults = false ) {

    var stateChange = {};
    var query = queryString.parse( window.location.search );

    if ( typeof query.view !== 'undefined' ) {
        stateChange.view = query.view;
    }

    if ( typeof query.program !== 'undefined' ) {
        stateChange.program = query.program;
    }

    if ( typeof query['on-view'] !== 'undefined' ) {
        stateChange['on-view'] = query['on-view'] == 'true';
    }

    if ( typeof query.year !== 'undefined' ) {

        stateChange.year = query.year;

    } else {

        if ( typeof query.from !== 'undefined' ) {
            stateChange.from = query.from;
        }

        if ( typeof query.to !== 'undefined' ) {
            stateChange.to = query.to;
        }

    }

    var result = objectAssign( {}, ( withDefaults ) ? this.defaultState : {}, this.history.location.state, stateChange );

    query = queryString.stringify( result );

    this.history.replace( base_url + prequery_seperator + query, result );

    return objectAssign({}, result);

};


URLManager.prototype.parseURLWithDefaults = function() { return this.parseURL( true ); };


/**
 * Update the current url state to reflect a given
 * set of query parameters passed in the form of an object.
 */
URLManager.prototype.updateURL = function( state = {} ) {

    var newState = objectAssign( {}, this.history.location.state, state );

    var query = queryString.stringify( newState );

    this.history.push( base_url + prequery_seperator + query, newState );

    return objectAssign( {}, newState );

};


/**
 * Watch the history state for changes, executing the
 * passed callback whenever the state changes
 *
 * @param callback a function that takes the state that we're transitioning to, and the action triggering the transition.
 * @return this URLManager the url manager instance.
 */
URLManager.prototype.on = function( callback = function() {} ) {

    this.history.listen( function( location, action ) {


    });

    return this;

};

/**
 * Retrieve the proper name of the artwork from the URL,
 * to be used with a Artwork_Title query from Zoho.
 *
 * @return string the name of the artwork represented by the slug
 */
function extractArtworkNameFromURL() {

    var pathComponents = window.location.pathname.split('/');
    return pathComponents.slice( -1 )[0];

}


export { URLManager, extractArtworkNameFromURL };
