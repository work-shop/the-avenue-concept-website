jQuery(document).ready(function ($) {
    'use strict';
    var wpms_dash_widgets = 0;
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
            $('.wpms_dash_permalink .percent_1').html(res +'%');
            $('.wpms_dash_permalink .percent_2 span.percent').html(res +'%');
            $('.wpms_dash_permalink .percent_3').css('width',res + '%');
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
                $('.wpms_dash_newcontent .percent_1').html(res[0] +'%');
                $('.wpms_dash_newcontent .percent_2 span.percent').html(res[1][0]);
                $('.wpms_dash_newcontent .percent_3').css('width',res[0] + '%');
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
                $('.wpms_dash_linkmeta .percent_1').html(res[0] +'%');
                $('.wpms_dash_linkmeta .percent_2 span.percent').html(res[1][0] + '/' + res[1][1]);
                $('.wpms_dash_linkmeta .percent_3').css('width',res[0] + '%');
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
                $('.wpms_dash_metatitle .percent_1').html(res[0] +'%');
                $('.wpms_dash_metatitle .percent_2 span.percent').html(res[1][0] + '/' + res[1][1]);
                $('.wpms_dash_metatitle .percent_3').css('width',res[0] + '%');

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
                    $('.wpms_dash_imgsresize .percent_1').html(res.imgs_statis[2] +'%');
                    $('.wpms_dash_imgsresize .percent_2 span.percent').html(res.imgs_statis[0] +'/'+ res.imgs_statis[1]);
                    $('.wpms_dash_imgsresize .percent_3').css('width',res.imgs_statis[2] + '%');

                    $('.wpms_dash_imgsmeta .percent_1').html(res.imgs_metas_statis[2] +'%');
                    $('.wpms_dash_imgsmeta .percent_2 span.percent').html(res.imgs_metas_statis[0] +'/'+ res.imgs_metas_statis[1]);
                    $('.wpms_dash_imgsmeta .percent_3').css('width',res.imgs_metas_statis[2] + '%');

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
                $('.wpms_dash_duplicate_metatitle .percent_1').html(res.percent +'%');
                $('.wpms_dash_duplicate_metatitle .percent_2 span.percent').html(res.count_post_duplicate + '/' + res.total_items);
                $('.wpms_dash_duplicate_metatitle .percent_3').css('width',res.percent + '%');

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
                $('.wpms_dash_duplicate_metadesc .percent_1').html(res.percent +'%');
                $('.wpms_dash_duplicate_metadesc .percent_2 span.percent').html(res.count_post_duplicate + '/' + res.total_items);
                $('.wpms_dash_duplicate_metadesc .percent_3').css('width',res.percent + '%');
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
                $('.wpms_dash_metadesc .percent_1').html(res[0] +'%');
                $('.wpms_dash_metadesc .percent_2 span.percent').html(res[1][0] + '/' + res[1][1]);
                $('.wpms_dash_metadesc .percent_3').css('width',res[0] + '%');
            }
        });
    }

    jQuery('.wpms_dash_widgets').qtip({
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
});