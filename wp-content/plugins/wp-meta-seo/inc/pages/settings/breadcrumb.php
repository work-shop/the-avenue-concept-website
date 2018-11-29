<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div id="breadcrumb" class="content-box">
    <div class="ju-settings-option wpms-no-maxheight wpms-no-background">
        <div class="ju-settings-option wpms_width_100 wpms-no-shadow">
            <div class="wpms_row_full">
                <input type="hidden" name="_metaseo_breadcrumbs[include_home]" value="0">
                <label class="ju-setting-label text"
                       data-alt="<?php esc_html_e('Include the Home element in the breadcrumb', 'wp-meta-seo'); ?>">
                    <?php esc_html_e('Include Home', 'wp-meta-seo') ?>
                </label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" id="include_home"
                               name="_metaseo_breadcrumbs[include_home]"
                               value="1" <?php checked($breadcrumbs['include_home'], 1) ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="ju-settings-option wpms_width_100 wpms-no-shadow">
            <div class="wpms_row_full">
                <input type="hidden" name="_metaseo_breadcrumbs[clickable]" value="0">
                <label class="ju-setting-label text"
                       data-alt="<?php esc_html_e('The breadcrumb element can be clickable or not', 'wp-meta-seo'); ?>">
                    <?php esc_html_e('Clickable breadcrumb', 'wp-meta-seo') ?>
                </label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" id="clickable"
                               name="_metaseo_breadcrumbs[clickable]"
                               value="1" <?php checked($breadcrumbs['clickable'], 1) ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="ju-settings-option wpms_width_100 wpms-no-shadow">
            <div class="wpms_row_full">
                <label class="ju-setting-label text"
                       data-alt="<?php esc_html_e('The separator that materialize the breadcrumb levels', 'wp-meta-seo'); ?>">
                    <?php esc_html_e('Breadcrumb separator', 'wp-meta-seo') ?>
                </label>
                <p class="p-d-20">
                    <label>
                        <input id="breadcrumbs_separator" class="wpms_width_100" name="_metaseo_breadcrumbs[separator]"
                               type="text"
                               value="<?php echo esc_attr(htmlentities($breadcrumbs['separator'])) ?>" size="50">
                    </label>
                </p>
            </div>
        </div>
    </div>

    <div class="ju-settings-option wpms_right m-r-0">
        <div class="wpms_row_full">
            <input type="hidden" name="_metaseo_breadcrumbs[include_home]" value="0">
            <label class="ju-setting-label text" data-alt="<?php esc_html_e('If home is included, you may want to force a text.
                         By default it’s the content title', 'wp-meta-seo'); ?>">
                <?php esc_html_e('Home text', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <input type="checkbox" id="home_text_default"
                           name="_metaseo_breadcrumbs[home_text_default]"
                           value="1" <?php checked($breadcrumbs['home_text_default'], 1) ?>>
                    <span class="slider round"></span>
                </label>
            </div>
            <?php
            if ((int) $breadcrumbs['home_text_default'] === 0) {
                $class = 'hide';
            } else {
                $class = 'show';
            }
            ?>
            <p class="<?php echo esc_attr('p-lr-20 tr_home_text wpms_width_100 wpms_left ' . $class) ?>">
                <label>
                    <input id="breadcrumbs_home_text" class="wpms_width_100" name="_metaseo_breadcrumbs[home_text]"
                           type="text"
                           value="<?php echo esc_attr($breadcrumbs['home_text']) ?>" size="50">
                </label>
            </p>
        </div>
    </div>

    <div class="ju-settings-option wpms_width_100">
        <div class="wpms_row_full">
            <input type="hidden" name="_metaseo_breadcrumbs[include_home]" value="0">
            <label class="ju-setting-label text wpms_width_100 wpms_left" data-alt="<?php esc_html_e('Generate a breadcrumb navigation based on your categories or page levels.
                         The shortcode can be included in theme layouts', 'wp-meta-seo'); ?>">
                <?php esc_html_e('PHP Shortcode', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                            <textarea class="textarea-shortcode-breadcrumb" readonly>

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
            </p>
        </div>
    </div>

    <div class="ju-settings-option wpms_width_100">
        <div class="wpms_row_full">
            <input type="hidden" name="_metaseo_breadcrumbs[include_home]" value="0">
            <label class="ju-setting-label text wpms_width_100 wpms_left" data-alt="<?php esc_html_e('Generate a breadcrumb navigation based on your categories or page levels.
                         The WordPress shortcode can be called anywhere in your content', 'wp-meta-seo'); ?>">
                <?php esc_html_e('WordPress Shortcode', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                    <input type="text" class="wp-shortcode-breadcrumb" size="50" readonly value="[wpms_breadcrumb reverse=”0″]">
                </label>
            </p>
        </div>
    </div>
    <div class="wpms_width_100 wpms_left m-t-20">
        <button type="button"
                class="wpms_save_settings_breadcrumb ju-button orange-button waves-effect waves-light"><?php esc_html_e('Save', 'wp-meta-seo') ?></button>
        <span class="message_saved"><?php esc_html_e('Saved', 'wp-meta-seo') ?></span>
    </div>

</div>