'use strict';


import createHistory from 'history/createBrowserHistory';

/**
 * file: module-url-manager.js
 *
 * This file manages URL state with history push state.
 */

function URLManager() {
    console.log('creating new URLManager instance.');
    if ( !(this instanceof URLManager)) { return new URLManager(); }
    var self = this;

}

/**
 * Calling this module parses the current URL into
 * a set of filtering parameters that can be passed
 * to the filter module
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
 * @return criteria.from ?moment a moment data object representing the first possible install date inclusive.
 * @return criteria.to ?moment a moment data object representing the last possible install date inclusive.
 * @return criteria.on_view ?boolean a boolean indicating whether to get only art on view, or only art not on view.
 */
URLManager.prototype.parseURL = function() {
    return {};
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
