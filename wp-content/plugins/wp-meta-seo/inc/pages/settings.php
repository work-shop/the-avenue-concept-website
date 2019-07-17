<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
wp_enqueue_style('m-style-qtip');
wp_enqueue_script('jquery-qtip');

$tabs_data = array(
    array(
        'id'    => 'general',
        'title' => __('General', 'wp-meta-seo'),
        'icon'  => 'home'
    )
);

if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
    $tabs_data[] = array(
        'id'    => 'local_business',
        'title' => __('Local business', 'wp-meta-seo'),
        'icon'  => 'account_circle'
    );
}

$tabs_data[] = array(
    'id'    => 'redirections_404',
    'title' => __('Redirections and 404', 'wp-meta-seo'),
    'icon'  => 'directions'
);

$tabs_data[] = array(
    'id'    => 'breadcrumb',
    'title' => __('Breadcrumb', 'wp-meta-seo'),
    'icon'  => 'horizontal_split'
);

if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
    $tabs_data[] = array(
        'id'    => 'send_email',
        'title' => __('Send Email', 'wp-meta-seo'),
        'icon'  => 'email'
    );
}

$tabs_data[] = array(
    'id'    => 'social',
    'title' => __('Social', 'wp-meta-seo'),
    'icon'  => 'share'
);

$tabs_data[] = array(
    'id'       => 'image_compression',
    'title'    => __('Image compression', 'wp-meta-seo'),
    'icon'     => 'compare'
);

$tabs_data[] = array(
    'id'       => 'jutranslation',
    'title'    => __('Translation', 'wp-meta-seo'),
    'icon'     => 'format_color_text'
);

$tabs_data[] = array(
    'id' => 'system_check',
    'title' => __('System Check', 'wp-meta-seo'),
    'content' => 'system-check',
    'icon' => 'verified_user',
);

$setting_switch_fields = array(
    'metaseo_showkeywords'   => array(
        'label' => __('Meta keywords', 'wp-meta-seo'),
        'help'  => __('Active the meta keyword edition feature', 'wp-meta-seo'),
    ),
    'metaseo_metatitle_tab'  => array(
        'label' => __('Meta title as page title', 'wp-meta-seo'),
        'help'  => __('When meta title is filled use it as page title instead of the content title', 'wp-meta-seo'),
    ),
    'metaseo_showtmetablock' => array(
        'label' => __('Meta block edition', 'wp-meta-seo'),
        'help'  => __('Activate the OnPage analysis and meta edition below the content', 'wp-meta-seo'),
    ),
    'metaseo_linkfield'      => array(
        'label' => __('Link text field', 'wp-meta-seo'),
        'help'  => __('Add back the missing title field in the Insert/Edit URL box', 'wp-meta-seo'),
    ),
    'metaseo_seovalidate'    => array(
        'label' => __('Force SEO validation', 'wp-meta-seo'),
        'help'  => __('Allow user to force on page SEO criteria validation by clicking on the icon', 'wp-meta-seo'),
    ),
    'metaseo_index'          => array(
        'label' => __('Post/Page index', 'wp-meta-seo'),
        'help'  => __('Add an option to say to search engine: hey!
                 Do not index this content', 'wp-meta-seo'),
    ),
    'metaseo_follow'         => array(
        'label' => __('Post/Page follow', 'wp-meta-seo'),
        'help'  => __('Add an option to setup Follow/Nofollow instruction for each content', 'wp-meta-seo'),
    ),
    'metaseo_overridemeta'   => array(
        'label' => __('Use image information from bulk editor', 'wp-meta-seo'),
        'help'  => __('Override the image information (Alt text) with image bulk editor content', 'wp-meta-seo'),
    )
);

?>

<div class="ju-main-wrapper">
    <div class="ju-left-panel">
        <div class="ju-logo">
            <a href="https://www.joomunited.com/" target="_blank">
                <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . 'assets/wordpress-css-framework/images/logo-joomUnited-white.png') ?>"
                     alt="<?php esc_html_e('JoomUnited logo', 'wp-meta-seo') ?>">
            </a>
        </div>
        <div class="ju-menu-search">
            <i class="material-icons ju-menu-search-icon">
                search
            </i>

            <input type="text" class="ju-menu-search-input"
                   placeholder="<?php esc_html_e('Search settings', 'wp-meta-seo') ?>"
            >
        </div>
        <ul class="ju-tabs tabs ju-menu-tabs">
            <?php foreach ($tabs_data as $value) : ?>
                <li class="tab" data-tab-title="<?php echo esc_attr($value['title']) ?>">
                    <a href="#<?php echo esc_attr($value['id']) ?>"
                       class="link-tab white-text waves-effect waves-light <?php echo (empty($value['sub_tabs'])) ? 'no-submenus' : 'with-submenus' ?>"
                    >
                        <i class="material-icons menu-tab-icon"><?php echo esc_html($value['icon']) ?></i>
                        <span class="tab-title"
                              title="<?php echo esc_attr($value['title']) ?>"><?php echo esc_html($value['title']) ?></span>
                        <?php
                        if ($value['id'] === 'system_check') {
                            if (version_compare(PHP_VERSION, '7.2.0', '<') || !in_array('curl', get_loaded_extensions()) || !extension_loaded('libxml')) {
                                echo '<i class="material-icons system-checkbox material-icons-menu-alert" style="float: right;vertical-align: text-bottom;">info</i>';
                            }
                        }
                        ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <form method="post" action="">
        <div class="ju-right-panel">
            <div class="ju-content-wrapper">
                <div id="profiles-container">
                    <?php foreach ($tabs_data as $value) : ?>
                        <div class="ju-content-wrapper" id="<?php echo esc_attr($value['id']) ?>" style="display: none">
                            <?php
                            if (!empty($value['sub_tabs'])) :
                                ?>
                                <div class="ju-top-tabs-wrapper">
                                    <ul class="tabs ju-top-tabs">
                                        <?php
                                        foreach ($value['sub_tabs'] as $tab_id => $tab_label) :
                                            ?>

                                            <li class="tab">
                                                <a href="#<?php echo esc_html($tab_id) ?>"
                                                   class="link-tab waves-effect waves-light">
                                                    <?php echo esc_html($tab_label) ?>
                                                </a>
                                            </li>

                                            <?php
                                        endforeach;
                                        ?>
                                    </ul>
                                </div>
                                <?php
                            endif;
                            ?>
                            <?php if ($value['id'] !== 'image_compression' && $value['id'] !== 'cloud') : ?>
                                <div class="wpms_width_100 top_bar">
                                    <h1><?php echo esc_html($value['title']) . ' ' . esc_html__('Settings', 'wp-meta-seo') ?></h1>
                                </div>
                            <?php endif; ?>

                            <?php
                            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
                            if (isset($_POST['btn_wpms_save']) && $value['id'] !== 'cloud') {
                                ?>
                                <div class="wpms_width_100 top_bar saved_infos">
                                    <?php
                                    require WPMETASEO_PLUGIN_DIR . '/inc/pages/settings/saved_info.php';
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                            <?php include_once(WPMETASEO_PLUGIN_DIR . '/inc/pages/settings/' . $value['id'] . '.php'); ?>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        <?php
        // phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect, Generic.WhiteSpace.ScopeIndent.IncorrectExact, WordPress.Security.NonceVerification.Missing -- View request, no action
        if (!empty($_POST['wpmf_hash'])) :
        ?>
        $('.ju-top-tabs .link-tab[href="#<?php echo esc_html($_POST['wpmf_hash']) ?>"]').click();
        <?php
        endif;
        // phpcs:enable
        ?>

        jQuery('.wrap_wpms_settings tr label').qtip({
            content: {
                attr: 'for'
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
                delay: 10
            }

        });

        $('.wpms_save_settings_breadcrumb').on('click', function () {
            var separator = $('#breadcrumbs_separator').val();
            var home_text = $('#breadcrumbs_home_text').val();
            if ($('#include_home').is(":checked")) {
                var include_home = 1;
            } else {
                include_home = 0;
            }

            if ($('#clickable').is(":checked")) {
                var clickable = 1;
            } else {
                clickable = 0;
            }

            if ($('#home_text_default').is(":checked")) {
                var home_text_default = 1;
            } else {
                home_text_default = 0;
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    'action': 'wpms',
                    'task': 'save_settings_breadcrumb',
                    'separator': separator,
                    'home_text': home_text,
                    'include_home': include_home,
                    'clickable': clickable,
                    'home_text_default': home_text_default,
                    'wpms_nonce': wpms_localize.wpms_nonce
                },
                success: function (res) {
                    if (res) {
                        $('.message_saved').fadeIn(10).delay(2000).fadeOut(2000);
                    } else {
                        alert('Save errors !')
                    }
                }
            });
        });

        $('.wpms_save_settings404').on('click', function () {
            var home_redirected = $('.wpms_redirect_homepage').val();
            var type_404 = $('.wpms_type_404').val();
            var page_redirected = $('.wpms_page_redirected').val();

            if ($('#scanlinkenable').is(":checked")) {
                var enable = 1;
            } else {
                enable = 0;
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    'action': 'wpms',
                    'task': 'save_settings404',
                    'wpms_redirect[wpms_redirect_homepage]': home_redirected,
                    'wpms_redirect[wpms_type_404]': type_404,
                    'wpms_redirect[wpms_page_redirected]': page_redirected,
                    'enable': enable,
                    'numberFrequency': $('#numberFrequency').val(),
                    'showlinkFrequency': $('#showlinkFrequency').val(),
                    'wpms_nonce': wpms_localize.wpms_nonce
                },
                success: function (res) {
                    if (res) {
                        $('.message_saved').fadeIn(10).delay(2000).fadeOut(2000);
                    } else {
                        alert('Save errors !')
                    }
                }
            });
        });

        $('.wpms_save_settingemail').on('click', function () {
            if ($('#showSmtpenable').is(":checked")) {
                var enable = 1;
            } else {
                enable = 0;
            }

            if ($('#showautentication').is(":checked")) {
                var showautentication = 'yes';
            } else {
                showautentication = 'no';
            }


            var $this = $(this);
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpms_save_settingemail',
                    enable: enable,
                    host: $('#showSmtpHost').val(),
                    type_encryption: $('[name="wpms_email_settings[type_encryption]"]:checked').val(),
                    port: $('#showSmtpPort').val(),
                    autentication: showautentication,
                    username: $('#showSmtpUser').val(),
                    password: $('#showSmtpPass').val(),
                    wpms_nonce: wpms_localize.wpms_nonce
                },
                success: function (res) {
                    if (res) {
                        $this.closest('.content-box').find('.message_saved').fadeIn(10).delay(2000).fadeOut(2000);
                    } else {
                        alert('Save errors !')
                    }
                }
            });
        });

        $('.wpms_local_business').on('click', function () {
            if ($('#local_business_enable').is(":checked")) {
                var enable = 1;
            } else {
                enable = 0;
            }
            $this = $(this);
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpms_save_local_business',
                    enable: enable,
                    logo: $('#wpms_local_business_logo').val(),
                    type_name: $('#wpms_local_business_type_name').val(),
                    country: $('#wpms_local_business_country').val(),
                    address: $('#wpms_local_business_address').val(),
                    city: $('#wpms_local_business_city').val(),
                    state: $('#wpms_local_business_state').val(),
                    phone: $('#wpms_local_business_phone').val(),
                    pricerange: $('#wpms_local_business_pricerange').val(),
                    wpms_nonce: wpms_localize.wpms_nonce
                },
                success: function (res) {
                    if (res) {
                        $this.closest('.content-box').find('.message_saved').fadeIn(10).delay(2000).fadeOut(2000);
                    } else {
                        alert('Save errors !')
                    }
                }
            });
        });

        $(".wpms-seoImgAdd").on("click", function () {
            var file_frame,
                $this = $('.local-business-bar');
            if (undefined !== file_frame) {
                file_frame.open();
                return;
            }
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select or Upload Media For your profile gallery',
                button: {
                    text: 'Use this media'
                },
                multiple: false
            });
            file_frame.on('select', function () {
                var attachment = file_frame.state().get('selection').first().toJSON(),
                    imgId = attachment.id;
                if (typeof attachment.sizes.thumbnail === "undefined") {
                    imgUrl = attachment.url;
                } else {
                    imgUrl = attachment.sizes.thumbnail.url;
                }
                var imgInfo = "<span><strong>URL: </strong>" + attachment.sizes.full.url + "</span>";
                imgInfo = imgInfo + "<span><strong>Width: </strong>" + attachment.sizes.full.width + "px</span>";
                imgInfo = imgInfo + "<span><strong>Height: </strong>" + attachment.sizes.full.height + "px</span>";
                $this.find('#wpms_local_business_logo').val(imgId);
                $this.find('.wpms-seoImgRemove').removeClass('wpms-seo-hidden');
                $this.find('img').remove();
                $this.find('.wpms-seo-image-preview').html("<img src='" + imgUrl + "' />");
                $this.parents('.wpms-seo-image').find('.image-info').html(imgInfo);
            });
            // Now display the actual file_frame
            file_frame.open();
        });

        $(".wpms-seoImgRemove").on("click", function (e) {
            e.preventDefault();
            if (confirm("Are you sure?")) {
                var $this = $('.local-business-bar');
                $this.find('input').val('');
                $this.find('.wpms-seoImgRemove').addClass('wpms-seo-hidden');
                $this.find('.wpms-seo-image-preview').html('<i class="material-icons business-img-default">photo</i>');
                $this.parents('.wpms-seo-image').find('.image-info').html('');
            }
        });

        $('.wpms_type_404').on('change', function () {
            var type_404 = $(this).val();
            if (type_404 === 'wp-meta-seo-page' || type_404 === 'none') {
                $('.wpms_page_redirected').prop('disabled', true);
            } else if (type_404 === 'custom_page') {
                $('.wpms_page_redirected').prop('disabled', false);
            }
        });

        $('.cb_option').unbind('click').bind('click', function () {
            var check = $(this).attr('checked');
            var type = $(this).attr('type');
            var value;
            if (type === 'checkbox') {
                if (check === 'checked') {
                    value = 1;
                } else {
                    value = 0;
                }
                $('input[name="wpms_redirect[' + $(this).data('label') + ']"]').val(value);

                if ($(this).data('label') === 'wpms_redirect_homepage') {
                    if (check === 'checked') {
                        $('.wpms_type_404,.wpms_page_redirected').prop('disabled', true);
                    } else {
                        $('.wpms_type_404,.wpms_page_redirected').prop('disabled', false);
                    }
                }
            }
        });
    });

</script>