jQuery(document).ready(function ($) {
    'use strict';
    var wpms_dash_widgets = 0;
    function dashImgQtip() {
        jQuery('.img-infos-tooltip').qtip({
            content: {
                attr: 'data-alt'
            },
            position: {
                my: 'bottom center',
                at: 'top center'
            },
            style: {
                tip: {
                    corner: true
                },
                classes: 'wpms-widgets-qtip'
            },
            show: 'hover',
            hide: {
                fixed: true,
                delay: 10
            }
        });
    }

    // Knob
    $.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'wpms',
            task: 'dash_permalink',
            wpms_nonce: wpms_localize.wpms_nonce
        },
        success: function (res) {
            if (parseInt(res) === 100) {
                $('.wpms_dash_permalink').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.url_rewwrite + '100%'});
            } else {
                $('.wpms_dash_permalink').attr({'src': wpms_localize.images_url + 'icon-info/icon-info.png', 'data-alt': wpms_localize.dashboard_tooltips.url_rewwrite + res + '%'});
            }
            dashImgQtip();
            wpms_dash_widgets++;
            if(wpms_dash_widgets === 1){
                wpms_dash_widgets_newcontent();
            }
        }
    });

    function wpms_dash_widgets_newcontent(){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'dash_newcontent',
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (parseInt(res[0]) >= 3) {
                    $('.wpms_dash_newcontent').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.fresh_content + res[1][0] + wpms_localize.dashboard_tooltips.elements});
                } else {
                    $('.wpms_dash_newcontent').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.fresh_content + res[1][0] + wpms_localize.dashboard_tooltips.elements});
                }
                dashImgQtip();
                wpms_dash_widgets++;
                if(wpms_dash_widgets === 2){
                    wpms_dash_widgets_linkmeta();
                }
            }
        });
    }

    function wpms_dash_widgets_linkmeta(){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'dash_linkmeta',
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (parseInt(res[0]) === 100) {
                    $('.wpms_dash_linkmeta').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.link_title + res[1][0] + '/' + res[1][1] + ' = 100%'});
                } else {
                    $('.wpms_dash_linkmeta').attr({'src': wpms_localize.images_url + 'icon-info/icon-info.png', 'data-alt': wpms_localize.dashboard_tooltips.link_title + res[1][0] + '/' + res[1][1] + ' = ' + res[0] + '%'});
                }
                dashImgQtip();
                wpms_dash_widgets++;
                if(wpms_dash_widgets === 3){
                    wpms_dash_widgets_metatitle();
                }
            }
        });
    }

    function wpms_dash_widgets_metatitle(){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'dash_metatitle',
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (parseInt(res[0]) === 100) {
                    $('.wpms_dash_metatitle').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.metatitle + res[1][0] + '/' + res[1][1] + ' = 100%'});
                } else {
                    $('.wpms_dash_metatitle').attr({'src': wpms_localize.images_url + 'icon-info/icon-info.png', 'data-alt': wpms_localize.dashboard_tooltips.metatitle + res[1][0] + '/' + res[1][1] + ' = ' + res[0] + '%'});
                }
                dashImgQtip();
                wpms_dash_widgets++;
                if(wpms_dash_widgets === 4){
                    wpms_dash_widgets_imagemeta(1,0,0,0);
                }
            }
        });
    }

    function wpms_dash_widgets_imagemeta(page,imgs_statis,imgs_meta,imgs_count){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'dash_imgsmeta',
                page : page,
                imgs_statis : imgs_statis,
                imgs_metas_statis : imgs_meta,
                imgs_count : imgs_count,
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if(typeof res.status === "undefined"){
                    wpms_dash_widgets_imagemeta(page+1 , res.imgs_statis[0] , res.imgs_metas_statis[0] , res.imgs_count);
                }else{
                    if (parseInt(res.imgs_statis[2]) === 100) {
                        $('.wpms_dash_imgsresize').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.images_resized + res.imgs_statis[0]});
                    } else {
                        $('.wpms_dash_imgsresize').attr({'src': wpms_localize.images_url + 'icon-info/icon-info.png', 'data-alt': wpms_localize.dashboard_tooltips.images_resized + res.imgs_statis[0]});
                    }

                    if (parseInt(res.imgs_metas_statis[2]) === 100) {
                        $('.wpms_dash_imgsmeta').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.image_alt + res.imgs_metas_statis[0] +'/'+ res.imgs_metas_statis[1] + ' = 100%'});
                    } else {
                        $('.wpms_dash_imgsmeta').attr({'src': wpms_localize.images_url + 'icon-info/icon-info.png', 'data-alt': wpms_localize.dashboard_tooltips.image_alt + res.imgs_metas_statis[0] +'/'+ res.imgs_metas_statis[1] + ' = ' + res.imgs_metas_statis[2] + '%'});
                    }
                    dashImgQtip();
                    wpms_dash_widgets++;
                    if(wpms_dash_widgets === 5){
                        if (parseInt(wpms_localize.addon_active) === 0) {
                            wpms_dash_widgets_metadesc();
                        } else {
                            wpms_dash_widgets_duplicate_title();
                        }
                    }
                }
            }
        });
    }

    function wpms_dash_widgets_duplicate_title(){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'dash_duplicate_title',
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (parseInt(res.percent) >= 90) {
                    $('.wpms_dash_duplicate_metatitle').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.duplicate_title + res.count_post_duplicate});
                } else {
                    $('.wpms_dash_duplicate_metatitle').attr({'src': wpms_localize.images_url + 'icon-info/icon-info.png', 'data-alt': wpms_localize.dashboard_tooltips.duplicate_title + res.count_post_duplicate});
                }
                dashImgQtip();
                wpms_dash_widgets++;
                if(wpms_dash_widgets === 6){
                    wpms_dash_widgets_duplicate_desc();
                }
            }
        });
    }

    function wpms_dash_widgets_duplicate_desc(){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'dash_duplicate_desc',
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (parseInt(res.percent) >= 90) {
                    $('.wpms_dash_duplicate_metadesc').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.duplicate_desc + res.count_post_duplicate});
                } else {
                    $('.wpms_dash_duplicate_metadesc').attr({'src': wpms_localize.images_url + 'icon-info/icon-info.png', 'data-alt': wpms_localize.dashboard_tooltips.duplicate_desc + res.count_post_duplicate});
                }
                dashImgQtip();
                wpms_dash_widgets++;
                if(wpms_dash_widgets === 7){
                    wpms_dash_widgets_metadesc();
                }
            }
        });
    }

    function wpms_dash_widgets_metadesc(){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'dash_metadesc',
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (parseInt(res[0]) === 100) {
                    $('.wpms_dash_metadesc').attr({'src': wpms_localize.images_url + 'checklist/checklist.png', 'data-alt': wpms_localize.dashboard_tooltips.metadesc + res[1][0] + '/' + res[1][1] + ' = 100%'});
                } else {
                    $('.wpms_dash_metadesc').attr({'src': wpms_localize.images_url + 'icon-info/icon-info.png', 'data-alt': wpms_localize.dashboard_tooltips.metadesc + res[1][0] + '/' + res[1][1] + ' = ' + res[0] + '%'});
                }
                dashImgQtip();
            }
        });
    }

    function reloadWeb(){
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'reload-web',
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (res.status) {
                    $('.page-loader').hide();
                    $('.site_img').attr('src', res.link + '?v=' + Math.random());
                } else {
                    if (res.statusCode === 100 || res.statusCode === 101 ) {
                        setTimeout(function () {
                            reloadWeb()
                        }, 10000);
                    }
                }
            }
        });
    }

    $('.btn-reload-web').on('click', function () {
        $('.page-loader').show();
        reloadWeb();
    });

    dashImgQtip();
});