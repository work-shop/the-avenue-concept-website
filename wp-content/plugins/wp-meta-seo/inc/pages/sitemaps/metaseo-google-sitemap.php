<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="wrap wpms_wrap">
    <?php
    if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
        echo '<div id="links-check-list" class="white-popup mfp-hide">';
        echo '<p style="color: red" class="description link-check-msg">'.esc_html__('wpms-sitemap.xml file is not exists, please regenerate sitemaps before run sitemap link check', 'wp-meta-seo').'</p>';
        echo '<div style="width: 90%; margin: 0 auto">';
        echo '<div class="sitemap-link-process ju-button orange-button wpms_width_100 wpms_scan" style="display: none; padding: 13px 0 !important;">';
        esc_html_e('Checking sitemaps links... keep this window opened :)', 'wp-meta-seo');
        echo '<div class="wpms_process" data-w="0"></div>';
        echo '</div></div>';
        echo '<div class="wpms_table_linkchecker wpms-form-table">';
        ?>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <th scope="col" class="manage-column column-keyword column-primary" style="width: 40%">
                    <a class="wpms_linkcheck_sort" data-type="url">
                        <?php esc_html_e('URL', 'wp-meta-seo') ?>
                    </a>
                </th>
                <th scope="col" class="manage-column column-source column-primary" style="width: 10%">
                    <a class="wpms_linkcheck_sort" data-type="source">
                        <?php esc_html_e('Source', 'wp-meta-seo') ?>
                    </a>
                </th>
                <th scope="col" class="manage-column column-date column-primary" style="width: 15%">
                    <a class="wpms_linkcheck_sort" data-type="date">
                        <?php esc_html_e('Last modified date', 'wp-meta-seo') ?>
                    </a>
                </th>
                <th scope="col" class="manage-column column-frequency column-primary">
                    <a class="wpms_linkcheck_sort" data-type="frequency">
                        <?php esc_html_e('Frequency', 'wp-meta-seo') ?>
                    </a>
                </th>
                <th scope="col" class="manage-column column-priority column-primary">
                    <a class="wpms_linkcheck_sort" data-type="priority">
                        <?php esc_html_e('Priority', 'wp-meta-seo') ?>
                    </a>
                </th>
                <th scope="col" class="manage-column column-status column-primary">
                    <a class="wpms_linkcheck_sort" data-type="status">
                        <?php esc_html_e('Status', 'wp-meta-seo') ?>
                    </a>
                </th>
            </tr>
            </thead>

            <tbody id="the-list" class="wpms_list_links_sitemap"></tbody>
        </table>
        <?php
        echo '</div>';
        echo '</div>';
    }
    $custom_post_types = get_post_types(array('public' => true, 'exclude_from_search' => false, '_builtin' => false));
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/sitemaps/sitemap_menus.php');
    ?>
    <form method="post" id="wpms_xmap_form" action="">
        <input type="hidden" name="action" value="wpms_save_sitemap_settings">
        <?php
        require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/sitemaps/general.php');
        require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/sitemaps/metaseo-source_menu.php');
        require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/sitemaps/metaseo-source_posts.php');
        require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/sitemaps/metaseo-source_pages.php');
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            if (!empty($custom_post_types)) {
                //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- No impact on assignment
                foreach ($custom_post_types as $post_type => $label) {
                    ob_start();
                    require(WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/posts_custom.php');
                    $html = ob_get_contents();
                    ob_end_clean();
                    // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in 'wp-meta-seo-addon/inc/page/sitemaps/posts_custom.php' file
                    echo $html;
                }
            }

            ob_start();
            require(WPMETASEO_ADDON_PLUGIN_DIR . 'inc/page/sitemaps/custom_url.php');
            $html = ob_get_contents();
            ob_end_clean();
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in 'wp-meta-seo-addon/inc/page/sitemaps/custom_url.php' file
            echo $html;
        }

        echo '<div class="content-box div_wpms_save_sitemaps">
<button type="button" class="ju-button orange-button waves-effect waves-light wpms_save_create_sitemaps">' . esc_html__('Regenerate and save sitemaps', 'wp-meta-seo') . '</button>
 <span class="spinner spinner_save_sitemaps"></span><label class="msg-success">' . esc_html__('Sitemap saved and regenerated', 'wp-meta-seo') . '</label></div>';
        if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
            echo '<p class="content-box description">
' . esc_html__('Sitemap automatic submission to Google Search Console on save, ', 'wp-meta-seo') . '
<a href="' . esc_url(admin_url('admin.php?page=metaseo_console&tab=settings')) . '" style="color: #ff8726">
' . esc_html__('requires authentication', 'wp-meta-seo') . '</a></p>';
        }
        ?>
    </form>
</div>

<?php
$w               = '99%';
$text            = esc_html__('Bring your WordPress website SEO to the next level with the PRO Addon:
 Sitemap for any custom post type, auto submission to the Google Search Console and more!', 'wp-meta-seo');
$class_btn_close = 'close_sitemap';
if (!empty($_COOKIE['close_dashboard'])) {
    $check = time() - (int) $_COOKIE['close_dashboard'];
    $month = 30 * 24 * 60 * 60;
}

if ((empty($_COOKIE['close_dashboard']) || (!empty($_COOKIE['close_dashboard']) && $check >= $month))
    && !is_plugin_active(WPMSEO_ADDON_FILENAME)) {
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/notification.php');
}
