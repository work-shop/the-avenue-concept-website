'use strict';
var wpmseo_target_id;
jQuery(document).ready(function ($) {
    var wpmseo_uploader;
    $('.wpmseo_image_upload_button').click(function (e) {
        wpmseo_target_id = $(this).attr('id').replace(/_button$/, '');
        e.preventDefault();
        if (wpmseo_uploader) {
            wpmseo_uploader.open();
            return;
        }
        wpmseo_uploader = wp.media.frames.file_frame = wp.media({
            title: wpmseoMediaL10n.choose_image,
            button: {text: wpmseoMediaL10n.choose_image},
            multiple: false
        });

        wpmseo_uploader.on('select', function () {
            var attachment = wpmseo_uploader.state().get('selection').first().toJSON();
            $('#' + wpmseo_target_id).val(attachment.url);
            wpmseo_uploader.close();
        });

        wpmseo_uploader.open();
    });
});
