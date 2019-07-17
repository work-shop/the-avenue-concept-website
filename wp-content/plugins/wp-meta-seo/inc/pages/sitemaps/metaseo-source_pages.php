<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div id="menu_source_pages" class="wpms_source wpms_source_pages content-box">
    <h1 class="h1_top"><?php esc_html_e('Source : Page', 'wp-meta-seo') ?></h1>
    <div class="ju-settings-option">
        <div class="wpms_row_full">
            <label class="ju-setting-label text" data-alt="<?php echo esc_attr('Include all elements in the sitemap', 'wp-meta-seo') ?>">
                <?php esc_html_e('Check all pages', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <input type="checkbox" class="sitemap_check_all" data-type="pages" id="wpms_check_all_pages"
                           value="1">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="ju-settings-option">
        <div class="wpms_row_full">
            <label class="ju-setting-label text">
                <?php esc_html_e('Check all pages in current page', 'wp-meta-seo') ?>
            </label>
            <div class="ju-switch-button">
                <label class="switch">
                    <input type="checkbox" class="sitemap_check_all_posts_in_page" data-type="pages" id="wpms_check_all_posts_in_page_pages"
                           value="1">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="ju-settings-option">
        <div class="wpms_row_full">
            <label class="ju-setting-label text wpms_width_100 wpms_left">
                <?php esc_html_e('Public name', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                    <input type="text" class="public_name_pages wpms-large-input wpms_width_100"
                           value="<?php echo esc_attr($sitemap->settings_sitemap['wpms_public_name_pages']) ?>">
                </label>
            </p>
        </div>
    </div>

    <div class="ju-settings-option wpms_xmp_custom_column">
        <div class="wpms_row_full">
            <label class="ju-setting-label text wpms_width_100 wpms_left" data-alt="<?php echo esc_attr('Column selection if you’re using the HTML sitemap', 'wp-meta-seo') ?>">
                <?php esc_html_e('HTML Sitemap column', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                    <select class="wpms_display_column wpms_display_column_pages wpms-large-input wpms_width_100">
                        <?php
                        for ($i = 1; $i <= $sitemap->settings_sitemap['wpms_html_sitemap_column']; $i ++) {
                            if ((int) $sitemap->settings_sitemap['wpms_display_column_pages'] === (int) $i) {
                                echo '<option selected value="' . esc_attr($i) . '">' . esc_html($sitemap->columns[$i]) . '</option>';
                            } else {
                                echo '<option value="' . esc_attr($i) . '">' . esc_html($sitemap->columns[$i]) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </label>
            </p>
        </div>
    </div>

    <div class="ju-settings-option wpms_xmp_order">
        <div class="wpms_row_full">
            <label class="ju-setting-label text wpms_width_100 wpms_left">
                <?php esc_html_e('Order', 'wp-meta-seo') ?>
            </label>
            <p class="p-d-20">
                <label>
                    <select class="wpms_display_order_pages wpms-large-input wpms_width_100">
                        <?php
                        for ($i = 1; $i <= 4; $i ++) {
                            if ((int) $sitemap->settings_sitemap['wpms_display_order_pages'] === (int) $i) {
                                echo '<option selected value="' . esc_html($i) . '">' . esc_html($i) . '</option>';
                            } else {
                                echo '<option value="' . esc_html($i) . '">' . esc_html($i) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </label>
            </p>
        </div>
    </div>

    <div id="wrap_sitemap_option_pages" class="wrap_sitemap_option">
        <?php
        $listpages = $sitemap->getPages();
        foreach ($listpages as $value) {
            if (empty($sitemap->settings_sitemap['wpms_sitemap_pages'][$value->ID]['frequency'])) {
                $pagefrequency = 'monthly';
            } else {
                $pagefrequency = $sitemap->settings_sitemap['wpms_sitemap_pages'][$value->ID]['frequency'];
            }
            if (empty($sitemap->settings_sitemap['wpms_sitemap_pages'][$value->ID]['priority'])) {
                $pagepriority = '1.0';
            } else {
                $pagepriority = $sitemap->settings_sitemap['wpms_sitemap_pages'][$value->ID]['priority'];
            }
            $slpr      = $sitemap->viewPriority(
                'priority_pages_' . $value->ID,
                '_metaseo_settings_sitemap[wpms_sitemap_pages][' . $value->ID . '][priority]',
                $pagepriority
            );
            $slfr      = $sitemap->viewFrequency(
                'frequency_pages_' . $value->ID,
                '_metaseo_settings_sitemap[wpms_sitemap_pages][' . $value->ID . '][frequency]',
                $pagefrequency
            );
            $permalink = get_permalink($value->ID);
            echo '<div class="wpms_row wpms_row_record">';
            echo '<div style="line-height:30px">';
            if (isset($sitemap->settings_sitemap['wpms_sitemap_pages'][$value->ID]['post_id'])
                && (int) $sitemap->settings_sitemap['wpms_sitemap_pages'][$value->ID]['post_id'] === (int) $value->ID) {
                echo '<input class="wpms_sitemap_input_link checked"
                 type="hidden" data-type="page" value="' . esc_attr($permalink) . '">';
                echo '<div class="pure-checkbox">';
                echo '<input class="cb_sitemaps_pages wpms_xmap_pages"
                 id="' . esc_attr('wpms_sitemap_pages_' . $value->ID) . '" type="checkbox"
                  name="' . esc_attr('_metaseo_settings_sitemap[wpms_sitemap_pages][' . $value->ID . '][post_id]') . '"
                   value="' . esc_attr($value->ID) . '" checked>';
                echo '<label for="' . esc_attr('wpms_sitemap_pages_' . $value->ID) . '" class="wpms-text ju-setting-label">' . esc_html($value->post_title) . '</label>';
                echo '</div>';
            } else {
                echo '<input class="wpms_sitemap_input_link" type="hidden"
                 data-type="page" value="' . esc_attr($permalink) . '">';
                echo '<div class="pure-checkbox">';
                echo '<input class="cb_sitemaps_pages wpms_xmap_pages"
                 id="' . esc_attr('wpms_sitemap_pages_' . $value->ID) . '" type="checkbox"
                  name="' . esc_attr('_metaseo_settings_sitemap[wpms_sitemap_pages][' . $value->ID . '][post_id]') . '"
                   value="' . esc_attr($value->ID) . '">';
                echo '<label for="' . esc_attr('wpms_sitemap_pages_' . $value->ID) . '" class="wpms-text ju-setting-label">' . esc_html($value->post_title) . '</label>';
                echo '</div>';
            }
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method MetaSeoSitemap::viewPriority and MetaSeoSitemap::viewFrequency
            echo '<div class="wpms_right">' . $slpr . $slfr . '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
    <div class="holder holder_pages"></div>
</div>