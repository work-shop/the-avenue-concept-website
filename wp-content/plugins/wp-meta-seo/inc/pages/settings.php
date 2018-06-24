<?php
wp_enqueue_style('m-style-qtip');
wp_enqueue_script('jquery-qtip');
?>
<div class="wrap wrap_wpms_settings">
    <h1><?php _e('WP Meta SEO global settings', 'wp-meta-seo') ?></h1>
    <div class="tab-header">
        <ul class="tabs wpmstabs">
            <li class="tab wpmstab col active"><a href="#wpms-global"><?php _e('Global', 'wp-meta-seo') ?></a></li>
            <li class="tab wpmstab col"><a
                        href="#wpms-redirection"><?php _e('Redirections and 404', 'wp-meta-seo') ?></a></li>
            <li class="tab wpmstab col"><a href="#wpms-breadcrumb"><?php _e('Breadcrumb', 'wp-meta-seo') ?></a></li>
            <?php
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) :
                ?>
                <li class="tab wpmstab col"><a href="#wpms-email"><?php _e('Send Email', 'wp-meta-seo') ?></a></li>
                <li class="tab wpmstab col"><a
                            href="#wpms-local_usiness"><?php _e('Local business', 'wp-meta-seo') ?></a></li>
                <?php
            endif;
            ?>
            <li class="tab wpmstab col"><a href="#wpms-jutranslation"><?php _e('Translation', 'wp-meta-seo') ?></a></li>
        </ul>
    </div>
    <div class="wpms_content_settings">
        <div id="wpms-global" class="content-box">
            <form method="post" action="options.php">
                <?php
                settings_fields('Wp Meta SEO');
                do_settings_sections('metaseo_settings');
                ?>
                <p class="submit"><input type="submit" name="submit" id="submit" class="wpmsbtn" value="Save Changes">
                </p>
            </form>
        </div>

        <div id="wpms-redirection" class="content-box">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><?php _e('Global home redirect', 'wp-meta-seo') ?></th>
                    <td>
                        <input type="hidden" class="wpms_redirect_homepage" name="wpms_redirect[wpms_redirect_homepage]"
                               value="<?php echo $defaul_settings_404['wpms_redirect_homepage'] ?>">
                        <label><?php _e('Redirect all 404 errors to home page', 'wp-meta-seo'); ?></label>
                        <div class="switch-optimization">
                            <label class="switch switch-optimization">
                                <?php
                                if (isset($defaul_settings_404['wpms_redirect_homepage'])
                                    && $defaul_settings_404['wpms_redirect_homepage'] == 1) :
                                    ?>
                                    <input type="checkbox" class="cb_option" id="wpms_redirect_homepage"
                                           data-label="wpms_redirect_homepage"
                                           value="1" checked>
                                <?php else : ?>
                                    <input type="checkbox" class="cb_option" id="wpms_redirect_homepage"
                                           data-label="wpms_redirect_homepage"
                                           value="1">
                                <?php endif; ?>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Custom 404 page', 'wp-meta-seo') ?></th>
                    <td>
                        <label>
                            <select name="wpms_redirect[wpms_type_404]"
                                    class="wpms_type_404"
                                <?php echo ($defaul_settings_404['wpms_redirect_homepage'] == 1) ? "disabled" : "" ?>>
                                <?php foreach ($types_404 as $k => $type_404) : ?>
                                    <option <?php selected($defaul_settings_404['wpms_type_404'], $k) ?>
                                            value="<?php echo $k ?>"><?php echo $type_404 ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php
                            if ($defaul_settings_404['wpms_redirect_homepage'] == 1
                                || $defaul_settings_404['wpms_type_404'] != 'custom_page') {
                                $disable = 'disabled';
                            } else {
                                $disable = '';
                            }
                            ?>
                            <select name="wpms_redirect[wpms_page_redirected]"
                                    class="wpms_page_redirected" <?php echo $disable ?>>
                                <option value="none"><?php _e('— Select —', 'wp-meta-seo') ?></option>
                                <?php foreach ($posts as $post) : ?>
                                    <option <?php selected($defaul_settings_404['wpms_page_redirected'], $post->ID) ?>
                                            value="<?php echo $post->ID ?>"><?php echo $post->post_title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                echo $link_settings_html;
            }
            ?>
            <div class="button wpms_save_settings404"><?php _e('Save', 'wp-meta-seo') ?></div>
            <span class="message_saved"><?php _e('Saved', 'wp-meta-seo') ?></span>
        </div>

        <div id="wpms-breadcrumb" class="content-box">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="<?php _e('The separator that materialize the breadcrumb levels', 'wp-meta-seo') ?>">
                            <?php _e('Breadcrumb separator', 'wp-meta-seo') ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input id="breadcrumbs_separator" name="_metaseo_breadcrumbs[separator]" type="text"
                                   value="<?php echo htmlentities($breadcrumbs['separator']) ?>" size="50">
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php _e('Include the Home element in the breadcrumb', 'wp-meta-seo') ?>">
                            <?php _e('Include Home', 'wp-meta-seo') ?></label>
                    </th>
                    <td>
                        <input name="_metaseo_breadcrumbs[include_home]" type="hidden" value="0">
                        <div class="switch-optimization">
                            <label class="switch switch-optimization">
                                <input type="checkbox" id="include_home"
                                       name="_metaseo_breadcrumbs[include_home]"
                                       value="1" <?php checked($breadcrumbs['include_home'], 1) ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php _e('If home is included, you may want to force a text.
                         By default it’s the content title', 'wp-meta-seo') ?>">
                            <?php _e('Home text', 'wp-meta-seo') ?>
                        </label>
                    </th>
                    <td>
                        <input name="_metaseo_breadcrumbs[home_text_default]" type="hidden" value="0">
                        <div class="switch-optimization">
                            <label class="switch switch-optimization">
                                <input type="checkbox" id="home_text_default"
                                       name="_metaseo_breadcrumbs[home_text_default]"
                                       value="1" <?php checked($breadcrumbs['home_text_default'], 1) ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </td>
                </tr>

                <?php
                if ($breadcrumbs['home_text_default'] == 0) {
                    $class = 'hide';
                } else {
                    $class = 'show';
                }
                ?>
                <tr class="tr_home_text <?php echo $class ?>">
                    <th scope="row">
                        <label></label>
                    </th>
                    <td>
                        <label>
                            <input id="breadcrumbs_home_text" name="_metaseo_breadcrumbs[home_text]" type="text"
                                   value="<?php echo $breadcrumbs['home_text'] ?>" size="50">
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="<?php _e('The breadcrumb element can be clickable or not', 'wp-meta-seo') ?>">
                            <?php _e('Clickable breadcrumb', 'wp-meta-seo') ?></label>
                    </th>
                    <td>
                        <input name="_metaseo_breadcrumbs[clickable]" type="hidden" value="0">
                        <div class="switch-optimization">
                            <label class="switch switch-optimization">
                                <input type="checkbox" id="clickable"
                                       name="_metaseo_breadcrumbs[clickable]"
                                       value="1" <?php checked($breadcrumbs['clickable'], 1) ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php _e('Generate a breadcrumb navigation based on your categories or page levels.
                         The shortcode can be included in theme layouts', 'wp-meta-seo') ?>">
                            <?php _e('PHP Shortcode', 'wp-meta-seo') ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <textarea style="width: 700px;height:200px;resize:both" readonly>
        /**
        * @param bool $return Whether to return or echo the trail. (optional)
        * @param bool $reverse Whether to reverse the output or not. (optional)
        */
        if(function_exists('wpms_breadcrumb')){
            $return = false;
            $reverse = false;
            echo '<div class="breadcrumbs" typeof="BreadcrumbList" vocab="https://schema.org/">';
            wpms_breadcrumb($return,$reverse);
            echo '</div>';
        }
                            </textarea>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php _e('Generate a breadcrumb navigation based on your categories or page levels.
                         The WordPress shortcode can be called anywhere in your content', 'wp-meta-seo') ?>">
                            <?php _e('WordPress Shortcode', 'wp-meta-seo') ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="text" size="50" readonly value="[wpms_breadcrumb reverse=”0″]">
                        </label>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="button wpms_save_settings_breadcrumb"><?php _e('Save', 'wp-meta-seo') ?></div>
            <span class="message_saved"><?php _e('Saved', 'wp-meta-seo') ?></span>
        </div>

        <div id="wpms-jutranslation" class="content-box">
            <?php \Joomunited\WPMetaSEO\Jutranslation\Jutranslation::getInput(); ?>
        </div>

        <?php
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            echo $html_tabemail;
            echo $local_business_html;
        }
        ?>

    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
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
                    'home_text_default': home_text_default
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
                    'showlinkFrequency': $('#showlinkFrequency').val()
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
                    autentication: $('[name="wpms_email_settings[autentication]"]:checked').val(),
                    username: $('#showSmtpUser').val(),
                    password: $('#showSmtpPass').val()
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
                    pricerange: $('#wpms_local_business_pricerange').val()
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
                $this = $(this).parents('.wpms-seo-image-wrapper');
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
                $this.find('.wpms-seo-image-preview').append("<img src='" + imgUrl + "' />");
                $this.parents('.wpms-seo-image').find('.image-info').html(imgInfo);
            });
            // Now display the actual file_frame
            file_frame.open();
        });

        $(".wpms-seoImgRemove").on("click", function (e) {
            e.preventDefault();
            if (confirm("Are you sure?")) {
                var $this = $(this).parents('.wpms-seo-image-wrapper');
                $this.find('input').val('');
                $this.find('.wpms-seoImgRemove').addClass('wpms-seo-hidden');
                $this.find('img').remove();
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