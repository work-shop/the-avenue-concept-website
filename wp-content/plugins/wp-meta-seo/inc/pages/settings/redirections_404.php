<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div id="redirections_404" class="content-box">
    <div class="ju-settings-option height_140 p-lrb-20">
        <div class="wpms_row_full">
            <input type="hidden" class="wpms_redirect_homepage" name="wpms_redirect[wpms_redirect_homepage]"
                   value="<?php echo esc_attr($defaul_settings_404['wpms_redirect_homepage']) ?>">
            <label class="ju-setting-label text" style="padding-left: 0">
                <?php esc_html_e('Global home redirect', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <?php
                    if (isset($defaul_settings_404['wpms_redirect_homepage'])
                        && (int) $defaul_settings_404['wpms_redirect_homepage'] === 1) :
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
            <p class="ju-description text_left p-tb-20 border-top-e4e8ed">
                <?php esc_html_e('Redirect all 404 errors to home page', 'wp-meta-seo'); ?>
            </p>
        </div>
    </div>

    <div class="ju-settings-option height_140 wpms_right m-r-0">
        <div class="wpms_row_full">
            <label class="ju-setting-label wpms_width_100 wpms_left">
                <?php esc_html_e('Custom 404 page', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                    <select name="wpms_redirect[wpms_type_404]"
                            class="wpms_type_404"
                        <?php echo ((int) $defaul_settings_404['wpms_redirect_homepage'] === 1) ? 'disabled' : '' ?>>
                        <?php foreach ($types_404 as $k => $type_404) : ?>
                            <option <?php selected($defaul_settings_404['wpms_type_404'], $k) ?>
                                    value="<?php echo esc_attr($k) ?>"><?php echo esc_html($type_404) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                    if ((int) $defaul_settings_404['wpms_redirect_homepage'] === 1
                        || $defaul_settings_404['wpms_type_404'] !== 'custom_page') {
                        $disable = 'disabled';
                    } else {
                        $disable = '';
                    }
                    ?>
                    <select name="wpms_redirect[wpms_page_redirected]"
                            class="wpms_page_redirected" <?php echo esc_attr($disable) ?>>
                        <option value="none"><?php esc_html_e('— Select —', 'wp-meta-seo') ?></option>
                        <?php foreach ($posts as $value) : ?>
                            <option <?php selected($defaul_settings_404['wpms_page_redirected'], $value->ID) ?>
                                    value="<?php echo esc_attr($value->ID) ?>"><?php echo esc_html($value->post_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>
        </div>
    </div>

    <?php
    if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
        // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in 'wp-meta-seo-addon/inc/page/link_settings.php' file
        echo $link_settings_html;
    }
    ?>

    <div class="wpms_width_100 wpms_left">
        <button type="button"
                class="wpms_save_settings404 ju-button orange-button waves-effect waves-light"><?php esc_html_e('Save', 'wp-meta-seo') ?></button>
        <span class="message_saved"><?php esc_html_e('Saved', 'wp-meta-seo') ?></span>
    </div>
</div>