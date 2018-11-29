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
        var sl_bulk = $('.mbulk_copy:checked').val();
        if (typeof sl_bulk === "undefined" || $('.wpms-bulk-action:checked').length === 0) {
            return;
        }

        var mpost_selected = [];
        var action = $this.data('action');
        if (sl_bulk !== 'all') {
            $(".metaseo_post").each(function () {
                if ($(this).is(':checked')) {
                    mpost_selected.push($(this).val());
                }
            });

            $('.wpms-bulk-action:checked').each(function (i, v) {
                var action_name = $(v).val();
                wpms_ajax_coppy($this, action, mpost_selected, sl_bulk, action_name);
            });
        } else {
            switch (action) {
                case 'bulk_post_copy':
                    $('.wpms-bulk-action:checked').each(function (i, v) {
                        var action_name = $(v).val();
                        wpms_ajax_coppy($this, 'bulk_post_copy', 0, 'all', action_name);
                    });
                    break;
                case 'bulk_image_copy':
                    $('.wpms-bulk-action:checked').each(function (i, v) {
                        var action_name = $(v).val();
                        wpms_ajax_check_exist($this, 'bulk_image_copy', 0, 'all', action_name);
                    });
                    break;
            }
        }
    });
    
    /*
     * function copy
     */
    function wpms_ajax_coppy($this, maction, mpost_selected, sl_bulk, action_name) {
        $('.wpms-spinner-copy').show().css('visibility', 'visible');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: maction,
                ids: mpost_selected,
                sl_bulk: sl_bulk,
                action_name: action_name,
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                $('.wpms-spinner-copy').hide();
                $('.bulk-msg').fadeIn(100).delay(1000);
            }

        });
    }
    
    /*
     * function check empty title and alt
     */
    function wpms_ajax_check_exist($this, maction, mpost_selected, sl_bulk, action_name) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'wpms',
                task: 'ajax_check_exist',
                action_name: action_name,
                wpms_nonce: wpms_localize.wpms_nonce
            },
            success: function (res) {
                if (res) {
                    if (confirm(wpmseobulkL10n.metaseo_message_false_copy)) {
                        wpms_ajax_coppy($this, maction, mpost_selected, sl_bulk, action_name);
                    }
                } else {
                    wpms_ajax_coppy($this, maction, mpost_selected, sl_bulk, action_name);
                }
            }
        });
    }

});