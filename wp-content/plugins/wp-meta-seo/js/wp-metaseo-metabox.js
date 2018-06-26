function msClean(str) {
    if (str === '' || typeof (str) === 'undefined') {
        return '';
    }

    try {
        str = str.replace(/<\/?[^>]+>/gi, '');
        str = str.replace(/\[(.+?)](.+?\[\/\\1])?/g, '');
        str = jQuery('<div/>').html(str).text();
    } catch (e) {
    }

    return str;
}

function msReplaceVariables(str, callback) {
    if (typeof str === 'undefined') {
        return;
    }

    if (jQuery(wpmsdivtitle).length) {
        str = str.replace(/%%title%%/g, jQuery(wpmsdivtitle).val().replace(/(<([^>]+)>)/ig, ''));
    }

    // These are added in the head for performance reasons.
    str = str.replace(/%%sitedesc%%/g, wpmseoMetaboxL10n.sitedesc);
    str = str.replace(/%%sitename%%/g, wpmseoMetaboxL10n.sitename);
    str = str.replace(/%%sep%%/g, wpmseoMetaboxL10n.sep);
    str = str.replace(/%%page%%/g, wpmseoMetaboxL10n.page);

    // excerpt
    var excerpt = '';
    if (jQuery('#excerpt').length) {
        excerpt = msClean(jQuery('#excerpt').val().replace(/(<([^>]+)>)/ig, ''));
        str = str.replace(/%%excerpt_only%%/g, excerpt);
    }
    if ('' === excerpt && jQuery('#content').length) {
        excerpt = jQuery('#content').val().replace(/(<([^>]+)>)/ig, '').substring(0, wpmseoMetaboxL10n.wpmseo_meta_desc_length - 1);
    }
    str = str.replace(/%%excerpt%%/g, excerpt);

    // parent page
    if (jQuery('#parent_id').length && jQuery('#parent_id option:selected').text() !== wpmseoMetaboxL10n.no_parent_text) {
        str = str.replace(/%%parent_title%%/g, jQuery('#parent_id option:selected').text());
    }

    // remove double separators
    var esc_sep = msEscapeFocusKw(wpmseoMetaboxL10n.sep);
    var pattern = new RegExp(esc_sep + ' ' + esc_sep, 'g');
    str = str.replace(pattern, wpmseoMetaboxL10n.sep);

    if (str.indexOf('%%') !== -1 && str.match(/%%[a-z0-9_-]+%%/i) !== null) {
        var regex = /%%[a-z0-9_-]+%%/gi;
        var matches = str.match(regex);
        for (var i = 0; i < matches.length; i++) {
            if (typeof (replacedVars[ matches[ i ] ]) === 'undefined') {
                str = str.replace(matches[ i ], replacedVars[ matches[ i ] ]);
            } else {
                var replaceableVar = matches[ i ];

                // create the cache already, so we don't do the request twice.
                replacedVars[ replaceableVar ] = '';
                msAjaxReplaceVariables(replaceableVar, callback);
            }
        }
    }
    callback(str);
}

function msAjaxReplaceVariables(replaceableVar, callback) {
    jQuery.post(ajaxurl, {
        action: 'wpmseo_replace_vars',
        string: replaceableVar,
        post_id: jQuery('#post_ID').val(),
        _wpnonce: wpmseoMetaboxL10n.wpmseo_replace_vars_nonce
    }, function (data) {
        if (data) {
            replacedVars[ replaceableVar ] = data;
        }

        msReplaceVariables(replaceableVar, callback);
    });
}

/*
 * Change meta title in meta box
 */
function msUpdateTitle(force) {
    var title = '';
    var titleElm = jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'title');
    if (!titleElm.length) {
        return;
    }
    var titleLengthError = jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'title-length-warning');
    var divHtml = jQuery('<div />');
    var snippetTitle = jQuery('#wpmseosnippet_title');

    if (titleElm.val() !== '') {
        var len = wpmseoMetaboxL10n.wpmseo_meta_title_length - titleElm.val().length;
        metaseo_status_length(len, '#' + wpmseoMetaboxL10n.field_prefix + 'title-length');
        jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'title-length').html(len);
    } else {
        jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'title-length').addClass('length-true').removeClass('length-wrong').html('<span class="good">' + wpmseoMetaboxL10n.wpmseo_meta_title_length + '</span>');
    }

    if (titleElm.val()) {
        title = titleElm.val().replace(/(<([^>]+)>)/ig, '');
    } else {
        title = wpmseoMetaboxL10n.wpmseo_title_template;
        title = divHtml.html(title).text();
    }
    if (title === '') {
        snippetTitle.html('');
        titleLengthError.hide();
        return;
    }

    title = msClean(title);
    title = jQuery.trim(title);
    title = divHtml.text(title).html();

    if (force) {
        titleElm.val(title);
    }

    msReplaceVariables(title, function (title) {
        title = msSanitizeTitle(title);

        jQuery('#wpmseosnippet_title').html(title);

        // do the placeholder
        var placeholder_title = divHtml.html(title).text();
        titleElm.attr('placeholder', placeholder_title);

        var titleElement = document.getElementById('wpmseosnippet_title');
        if (titleElement !== null) {
            if (titleElement.scrollWidth > titleElement.clientWidth) {
                titleLengthError.show();
            } else {
                titleLengthError.hide();
            }
        }
    });
}

/*
 * Change meta keywords in meta box
 */
function msUpdateKeywords() {
    var keywordsElm = jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'keywords');
    if (keywordsElm.val() !== '') {
        var len = wpmseoMetaboxL10n.wpmseo_meta_keywords_length - keywordsElm.val().length;
        metaseo_status_length(len, '#' + wpmseoMetaboxL10n.field_prefix + 'keywords-length');
        jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'keywords-length').html(len);
    } else {
        jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'keywords-length').addClass('length-true').removeClass('length-wrong').html('<span class="good">' + wpmseoMetaboxL10n.wpmseo_meta_keywords_length + '</span>');
    }
}

/*
 * Clean title
 */
function msSanitizeTitle(title) {
    title = msClean(title);
    return title;
}

/*
 * Change meta description in meta box
 */
function msUpdateDesc() {
    var desc = jQuery.trim(msClean(jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'desc').val()));
    var divHtml = jQuery('<div />');
    var snippet = jQuery('#wpmseosnippet');

    if (desc === '' && wpmseoMetaboxL10n.wpmseo_desc_template !== '') {
        desc = wpmseoMetaboxL10n.wpmseo_desc_template;
    }

    if (desc !== '') {
        msReplaceVariables(desc, function (desc) {
            desc = divHtml.text(desc).html();
            desc = msClean(desc);

            var len = wpmseoMetaboxL10n.wpmseo_meta_desc_length - desc.length;
            metaseo_status_length(len, '#' + wpmseoMetaboxL10n.field_prefix + 'desc-length');



            desc = msSanitizeDesc(desc);

            // Clear the autogen description.
            snippet.find('.desc span.autogen').html('');
            // Set our new one.
            snippet.find('.desc span.content').html(desc);
        });
    } else {
        var len = wpmseoMetaboxL10n.wpmseo_meta_desc_length;
        metaseo_status_length(len, '#' + wpmseoMetaboxL10n.field_prefix + 'desc-length');
    }
}

/*
 * Sanitize description
 */
function msSanitizeDesc(desc) {
    desc = msTrimDesc(desc);
    return desc;
}

function msTrimDesc(desc) {
    if (desc.length > wpmseoMetaboxL10n.wpmseo_meta_desc_length) {
        var space;
        if (desc.length > wpmseoMetaboxL10n.wpmseo_meta_desc_length) {
            space = desc.lastIndexOf(' ', (wpmseoMetaboxL10n.wpmseo_meta_desc_length - 3));
        } else {
            space = wpmseoMetaboxL10n.wpmseo_meta_desc_length;
        }
        desc = desc.substring(0, space).concat(' ...');
    }
    return desc;
}

/*
 * Update Url
 */
function msUpdateURL() {
    var url;
    if (jQuery('#editable-post-name-full').length) {
        var name = jQuery('#editable-post-name-full').text();
        url = wpmseoMetaboxL10n.wpmseo_permalink_template.replace('%postname%', name).replace('http://', '');
    }

    jQuery('#wpmseosnippet').find('.url').html(url);
}

function msUpdateSnippet() {
    if (typeof wpmseoMetaboxL10n.show_keywords !== "undefined" && parseInt(wpmseoMetaboxL10n.show_keywords) === 1) {
        msUpdateKeywords();
    }
    msUpdateURL();
    msUpdateTitle();
    msUpdateDesc();
}

function msEscapeFocusKw(str) {
    return str.replace(/[\-\[\]\/\{}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
}

function metaseo_status_length(len, id) {
    if (len < 0) {
        jQuery(id).addClass('length-wrong').removeClass('length-true');
        len = '<span class="wrong">' + len + '</span>';
    } else {
        jQuery(id).addClass('length-true').removeClass('length-wrong');
        len = '<span class="good">' + len + '</span>';
    }

    jQuery(id).html(len);
}

(function () {
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();

var replacedVars = [];  // jshint ignore:line
var wpmsdivtitle = '';
jQuery(document).ready(function ($) {
    // title
    if (wpmseoMetaboxL10n.plugin_active.indexOf('gutenberg.php') !== -1 && typeof wp.blocks !== "undefined") {
        wpmsdivtitle = '.editor-post-title__input';
    } else {
        wpmsdivtitle = '#title';
    }

    if (jQuery('.wpmseo-metabox-tabs-div').length > 0) {
        var active_tab = window.location.hash;
        if (active_tab === '' || active_tab.search('wpmseo') === -1) {
            active_tab = 'general';
        } else {
            active_tab = active_tab.replace('#wpmseo_', '');
        }
        jQuery('.' + active_tab).addClass('active');

        var descElm = jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'desc');
        var desc = jQuery.trim(msClean(descElm.val()));
        desc = jQuery('<div />').html(desc).text();
        descElm.val(desc);

        jQuery('a.wpmseo_tablink').click(function () {
            jQuery('.wpmseo-metabox-tabs li').removeClass('active');
            jQuery('.wpmseotab').removeClass('active');

            var link = jQuery(this).data('link').replace('wpmseo_', '');
            jQuery('.wpmseo-metabox-tabs-div .' + link).addClass('active');
            jQuery(this).parent('.wpmseo-metabox-tabs-div li').addClass('active');

            if (jQuery(this).hasClass('scroll')) {
                var scrollto = jQuery(this).attr('href').replace('wpmseo_', '');
                jQuery('html, body').animate({
                    scrollTop: jQuery(scrollto).offset().top
                }, 500
                        );
            }
        }
        );
    }

    jQuery('.wpmseo-heading').hide();
    jQuery('.wpmseo-metabox-tabs').show();

    jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'title').keyup(function () {
        msUpdateTitle();
    });

    jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'keywords').keyup(function () {
        msUpdateKeywords();
    });

    jQuery(wpmsdivtitle).keyup(function () {
        msUpdateTitle();
        msUpdateDesc();
    });

    jQuery('#parent_id').change(function () {
        msUpdateTitle();
        msUpdateDesc();
    });

    // DON'T 'optimize' this to use descElm! descElm might not be defined and will cause js errors (Soliloquy issue)
    jQuery('#' + wpmseoMetaboxL10n.field_prefix + 'desc').keyup(function () {
        msUpdateDesc();
    });

    jQuery('.gsc_keywords_filter .btnfilter').click(function () {
        var startDate = $('.startDate').val();
        var endDate = $('.endDate').val();
        var postId = $('#post_ID').val();
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms_filter_search_keywords',
                startDate: startDate,
                endDate: endDate,
                postId: postId
            },
            success: function (res) {
                if(res.status){
                    $('.wpms_load_more_keyword').data('page',2);
                    $('.wpms_list_gsc_keywords tr').remove();
                    $('.wpms_list_gsc_keywords').append(res.html);
                }
            }
        });
    });

    jQuery('.wpms_load_more_keyword').click(function () {
        var $this = $(this);
        var page = $this.data('page');
        var startDate = $('.startDate').val();
        var endDate = $('.endDate').val();
        var postId = $('#post_ID').val();
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms_filter_search_keywords',
                startDate: startDate,
                endDate: endDate,
                postId: postId,
                page:page
            },
            success: function (res) {
                if(res.status){
                    $this.data('page',res.page);
                    $('.wpms_list_gsc_keywords tr').remove();
                    $('.wpms_list_gsc_keywords').append(res.html);
                }
            }
        });
    });

    // Set time out because of tinymce is initialized later then this is done
    setTimeout(
            function () {
                msUpdateSnippet();

                // Adding events to content and excerpt
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content') !== null) {
                    tinyMCE.get('content').on('blur', msUpdateDesc);
                }

                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('excerpt') !== null) {
                    tinyMCE.get('excerpt').on('blur', msUpdateDesc);
                }
            },
            500
            );

    jQuery('.metaseo_help').qtip({
        content: {
            attr: 'alt'
        },
        position: {
            my: 'bottom left',
            at: 'top center'
        },
        style: {
            tip: {
                corner: true
            },
            classes: 'metaseo-qtip qtip-rounded'
        },
        show: 'click',
        hide: {
            fixed: true,
            delay: 500
        }

    });

    jQuery('.metaseo_tool').qtip({
        content: {
            attr: 'data-alt'
        },
        position: {
            my: 'bottom left',
            at: 'top center'
        },
        style: {
            tip: {
                corner: true
            },
            classes: 'metaseo-qtip qtip-rounded'
        },
        show: 'hover',
        hide: {
            fixed: true,
            delay: 100
        }

    });
});