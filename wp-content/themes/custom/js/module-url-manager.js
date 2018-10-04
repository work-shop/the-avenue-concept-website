'use strict';


import createHistory from 'history/createBrowserHistory';
var queryString = require('query-string');
var objectAssign = require('object-assign');

/**
 * file: module-url-manager.js
 *
 * This file manages URL state with history push state.
 */

function URLManager( defaultState = { view: 'thumbs' } ) {
    console.log('creating new URLManager instance.');
    if ( !(this instanceof URLManager)) { return new URLManager( defaultState ); }
    var self = this;

    self.defaultState = defaultState;

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
URLManager.prototype.parseURL = function() {

    var result = {};
    var query = queryString.parse( window.location.search );

    if ( typeof query.view !== 'undefined' ) {
        result.view = query.view;
    }

    if ( typeof query.program !== 'undefined' ) {
        result.program = query.program;
    }

    if ( typeof query['on-view'] !== 'undefined' ) {
        result.on_view = query['on-view'];
    }

    if ( typeof query.year !== 'undefined' ) {

        result.year = query.year;

    } else {

        if ( typeof query.from !== 'undefined' ) {
            result.from = query.from;
        }

        if ( typeof query.to !== 'undefined' ) {
            result.to = query.to;
        }

    }

    result = objectAssign( this.defaultState, result );

    return result;
};

/**
 * Update the current url state to reflect a given
 * set of query parameters passed in the form of an object.
 */
URLManager.prototype.updateURL = function( state = {} ) {

    return this;
};

/**
 * Register a handler
 *
 */
URLManager.prototype.onUpdateURL = function( callback ) {

    return this;
};

export { URLManager };
