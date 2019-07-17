<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div id="menu_source_menus" class="wpms_source wpms_source_menu content-box">
    <h1 class="h1_top"><?php esc_html_e('Source : Menu', 'wp-meta-seo') ?></h1>
    <?php
    $terms = get_terms(array('taxonomy' => 'nav_menu', 'hide_empty' => false, 'orderby' => 'term_id', 'order' => 'ASC'));
    if (!empty($terms)) {
        ?>
        <div class="ju-settings-option">
            <div class="wpms_row_full">
                <label class="ju-setting-label text">
                    <?php esc_html_e('Check all menus', 'wp-meta-seo') ?>
                </label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" class="sitemap_check_all" data-type="menu" id="wpms_check_all_menus"
                               value="1">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="ju-settings-option wpms_xmp_order wpms_right m-r-0">
            <div class="wpms_row_full">
                <label class="ju-setting-label text wpms_width_100 wpms_left">
                    <?php esc_html_e('Order', 'wp-meta-seo') ?>
                </label>
                <p class="p-d-20">
                    <label>
                        <select class="wpms_display_order_menus wpms-large-input wpms_width_100">
                            <?php
                            for ($i = 1; $i <= 4; $i ++) {
                                if ((int) $i === (int) $sitemap->settings_sitemap['wpms_display_order_menus']) {
                                    echo '<option selected value="' . esc_attr($i) . '">' . esc_html($i) . '</option>';
                                } else {
                                    echo '<option value="' . esc_attr($i) . '">' . esc_html($i) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </label>
                </p>
            </div>
        </div>

        <?php
        foreach ($terms as $value) {
            $sitemap->viewMenus($value);
        }
        echo '<input name="_metaseo_settings_sitemap[wpms_check_firstsave]" type="hidden" value="1">';
    }
    ?>
</div>