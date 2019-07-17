jQuery(document).ready(function ($) {
    var mcheck = 0;
    if (typeof wpmscliffpyles.use_validate !== "undefined" && parseInt(wpmscliffpyles.use_validate) === 1) {
        wpms_validate_analysis();
    }

    function reload_analysis(first_load) {
        var mpageurl = '', title = '', mcontent = '', current_editor = '';
        var meta_title = $('#metaseo_wpmseo_title').val();
        var meta_desc = $('#metaseo_wpmseo_desc').val();

        if (typeof wp.blocks !== "undefined") {
            mpageurl = $('#wp-admin-bar-view').find('a').attr('href');
            current_editor = 'gutenberg';
            if (parseInt(first_load) === 1) {
                title = wpmscliffpyles.post_title;
                mcontent = wpmscliffpyles.post_content;
            } else {
                title = $('.editor-post-title__input').val();
                if (typeof wp.data !== "undefined" && typeof wp.data.select('core/editor') !== "undefined") {
                    mcontent = wp.data.select('core/editor').getEditedPostContent();
                }
            }
        } else {
            mpageurl = $('#editable-post-name-full').text();
            title = $('#title').val();
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content') !== null) {
                mcontent = tinyMCE.editors.content.getContent();
            } else {
                mcontent = $('#content').val();
            }
        }

        $('.wpmseotab .spinner').css({'visibility': ' inherit'}).show();
        $('.metaseo_right').html('');
        $.ajax({
            dataType: 'json',
            method: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wpms',
                'task': 'reload_analysis',
                'datas': {
                    'editor': current_editor,
                    'first_load': first_load,
                    'post_id': jQuery('.metaseo-progress-bar').data('post_id'),
                    'title': title,
                    'meta_title': meta_title,
                    'mpageurl': mpageurl,
                    'meta_desc': meta_desc,
                    'content': mcontent
                },
                'wpms_nonce': wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (res) {
                    $('.wpmseotab .spinner').hide();
                    $('.metaseo_right').html(res.output);
                    mcheck = parseInt(res.check);
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

                    drawInactive(res.circliful);

                }
            }
        });
    }

    // init load analysis
    reload_analysis(1);

    // reload analysis
    $('#reload_analysis').on('click', function () {
        reload_analysis(0);
    });

    function drawInactive(circliful) {
        $('.metaseo-progress-bar').circleProgress({
            value: circliful / 100,
            size: 250,
            thickness: 8,
            fill: {
                gradient: ["#34e0ff", "#5dadff"]
            }
        }).on('circle-animation-progress', function (event, progress) {
            $(this).find('strong').html(Math.round(circliful) + '<i>%</i>');
        });
    }

    function wpms_validate_analysis() {
        jQuery(document).on('click', '.metaseo-dashicons.icons-mboxwarning', function () {
            var $this = $(this);
            $this.html('done').removeClass('icons-mboxwarning').addClass('icons-mboxdone');
            if (mcheck === 0) {
                mcheck = jQuery('#metaseo_alanysis_ok').val();
                mcheck++;
            } else {
                mcheck++;
            }
            var circliful = Math.ceil((mcheck * 100) / 7);
            jQuery.ajax({
                dataType: 'json',
                method: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'wpms',
                    'task': 'validate_analysis',
                    'post_id': jQuery('.metaseo-progress-bar').data('post_id'),
                    'field': $this.parent('.metaseo_analysis').data('title'),
                    'wpms_nonce': wpms_localize.wpms_nonce
                },
                success: function (res) {
                    if (res !== false) {
                        drawInactive(circliful);
                    }
                }
            });

        });
    }
});