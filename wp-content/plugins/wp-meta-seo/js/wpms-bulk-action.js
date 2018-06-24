jQuery(document).ready(function ($) {

    $('.tablenav.top #post-query-submit , .tablenav.bottom #post-query-submit').click(function (e) {
        if ($('.metaseo-filter').val() === 'bulk-copy-metatitle') {
            e.preventDefault();
            $('#bulk-copy-metatitle').show();
        }
    });
    
    /*
     * Copy image alt and image title from image name
     */
    $('.btn_do_copy').click(function () {
        var $this = $(this);
        var sl_bulk = $('.mbulk_copy').val();
        if (parseInt(sl_bulk) === 0) {
            return;
        }

        var mpost_selected = [];
        var check_alt = false;
        var check_title = false;
        var maction_post = 'bulk_post_copy';
        var maction_img = 'bulk_image_copy';
        $(".metaseo_post").each(function () {
            if ($(this).is(':checked')) {
                mpost_selected.push($(this).val());
            }
        });

        if (sl_bulk !== 'all') {
            if ($this.hasClass('post_do_copy')) {
                var maction = 'bulk_post_copy';
                var mtype = 'post_title';
                wpms_ajax_coppy($this, maction, mpost_selected, sl_bulk, mtype);
            } else {
                if ($this.hasClass('image_do_copy_alt')) {
                    var mtype = 'image_alt';
                    $.each(mpost_selected, function (i, v) {
                        if ($('#img-alt-' + v).val() !== '')
                            check_alt = true;
                    });

                    if (check_alt) {
                        if (confirm(wpmseobulkL10n.metaseo_message_false_copy)) {
                            wpms_ajax_coppy($this, maction_img, mpost_selected, sl_bulk, mtype);
                        }
                    } else {
                        wpms_ajax_coppy($this, maction_img, mpost_selected, sl_bulk, mtype);
                    }

                } else {
                    var mtype = 'image_title';
                    $.each(mpost_selected, function (i, v) {
                        if ($('#img-title-' + v).val() !== '')
                            check_title = true;
                    });

                    if (check_title) {
                        if (confirm(wpmseobulkL10n.metaseo_message_false_copy)) {
                            wpms_ajax_coppy($this, maction_img, mpost_selected, sl_bulk, mtype);
                        }
                    } else {
                        wpms_ajax_coppy($this, maction_img, mpost_selected, sl_bulk, mtype);
                    }
                }
            }
        } else {
            if ($this.hasClass('image_do_copy_alt')) {
                wpms_ajax_check_exist('alt', $this, maction_img, mpost_selected, sl_bulk, 'image_alt');
            } else if ($this.hasClass('image_do_copy_title')) {
                wpms_ajax_check_exist('title', $this, maction_img, mpost_selected, sl_bulk, 'image_title');
            } else if ($this.hasClass('post_do_copy')) {
                wpms_ajax_coppy($this, maction_post, mpost_selected, sl_bulk, 'post_title');
            }
        }
    });
    
    /*
     * function copy
     */
    function wpms_ajax_coppy($this, maction, mpost_selected, sl_bulk, mtype) {
        $this.closest('.tablenav').find('.spinner').show().css('visibility', 'visible');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: maction,
                ids: mpost_selected,
                sl_bulk: sl_bulk,
                mtype: mtype
            },
            success: function (res) {
                if (res) {
                    window.location.assign(document.URL);
                }

                $this.closest('.tablenav').find('.spinner').hide();
            }

        });
    }
    
    /*
     * function check empty title and alt
     */
    function wpms_ajax_check_exist(type, $this, maction, mpost_selected, sl_bulk, mtype) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'ajax_check_exist',
                type: type
            },
            success: function (res) {
                if (res) {
                    if (confirm(wpmseobulkL10n.metaseo_message_false_copy)) {
                        wpms_ajax_coppy($this, maction, mpost_selected, sl_bulk, mtype);
                    }
                } else {
                    wpms_ajax_coppy($this, maction, mpost_selected, sl_bulk, mtype);
                }
            }
        });
    }

});