<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div id="general" class="content-box">
        <input type="hidden" name="wpms_hash" class="wpms_hash" value="">
        <input type="hidden" name="wpms_nonce"
               value="<?php echo esc_html(wp_create_nonce('wpms_nonce')) ?>">
        <div class="ju-settings-option height_160 p-lrb-20">
            <div class="wpms_row_full">
                <input type="hidden" name="_metaseo_settings[home_meta_active]" value="0">
                <label class="ju-setting-label" style="padding-left: 0" data-alt="<?php esc_html_e('Force Home page meta title and description here', 'wp-meta-seo'); ?>">
                    <?php esc_html_e('Homepage meta information', 'wp-meta-seo') ?>
                </label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="_metaseo_settings[home_meta_active]"
                               value="1" <?php checked($home_meta_active, 1) ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                <p class="ju-description text_left p-tb-20 border-top-e4e8ed">
                    <?php esc_html_e('Force Home page meta title and description here', 'wp-meta-seo'); ?>
                </p>
            </div>
        </div>

        <div class="ju-settings-option height_160 ">
            <div class="wpms_row_full">
                <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_html_e('You can define your home page meta title in the content
                         itself (a page, a post category…),
                          if for some reason it’s not possible, use this setting', 'wp-meta-seo'); ?>">
                    <?php esc_html_e('Homepage meta title', 'wp-meta-seo') ?>
                </label>
                <p class="p-d-20">
                    <label>
                        <input type="text" class="wpms_width_100" name="_metaseo_settings[metaseo_title_home]"
                               value="<?php echo esc_attr($metaseo_title_home) ?>">
                    </label>
                </p>
            </div>
        </div>

        <div class="ju-settings-option height_160">
            <div class="wpms_row_full">
                <label class="ju-setting-label wpms_width_100 wpms_left" data-alt="<?php esc_html_e('You can define your home page meta description in the content
                         itself (a page, a post category…),
                         if for some reason it’s not possible, use this setting', 'wp-meta-seo'); ?>">
                    <?php esc_html_e('Homepage meta description', 'wp-meta-seo') ?>
                </label>
                <p class="p-d-20">
                    <label>
                        <input type="text" class="wpms_width_100" name="_metaseo_settings[metaseo_desc_home]"
                               value="<?php echo esc_attr($metaseo_desc_home) ?>">
                    </label>
                </p>
            </div>
        </div>

        <?php
        foreach ($setting_switch_fields as $setting_switch_name => $setting_switch_details) :
            ?>
            <div class="ju-settings-option height_160 p-lrb-20">
                <div class="wpms_row_full">
                    <input type="hidden" name="_metaseo_settings[<?php echo esc_html($setting_switch_name) ?>]"
                           value="0">
                    <label class="ju-setting-label" style="padding-left: 0"  data-alt="<?php echo esc_attr($setting_switch_details['help']) ?>">
                        <?php echo esc_html($setting_switch_details['label']) ?>
                    </label>
                    <div class="ju-switch-button">
                        <label class="switch">
                            <input type="checkbox"
                                   name="_metaseo_settings[<?php echo esc_html($setting_switch_name) ?>]"
                                   value="1" <?php checked(${$setting_switch_name}, 1) ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <p class="ju-description text_left p-tb-20 border-top-e4e8ed">
                        <?php echo esc_html($setting_switch_details['help']); ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="wpms_width_100 wpms_left">
            <button type="submit"
                    class="btn_wpms_save ju-button orange-button waves-effect waves-light"><?php esc_html_e('Save Changes', 'wp-meta-seo') ?></button>
        </div>
</div>