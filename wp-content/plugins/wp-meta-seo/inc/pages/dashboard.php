<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('MetaSeoDashboard')) {
    require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-dashboard.php');
}

wp_enqueue_style('m-style-qtip');
wp_enqueue_style('wpms-myqtip');
wp_enqueue_script('jquery-qtip');
wp_enqueue_script('my-qtips-js');
$addon_imgs = array(
    'search_console' => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/google-search-console/google-search-console.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/google-search-console/google-search-console@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/google-search-console/google-search-console@3x.png'
    ),
    '404'            => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/404/404.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/404/404@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/404/404@3x.png'
    ),
    'business'       => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/business/business.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/business/business@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/business/business@3x.png'
    ),
    'woocommerce'    => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/woocommerce/woocommerce.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/woocommerce/woocommerce@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/woocommerce/woocommerce@3x.png'
    ),
    'polylang'       => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/polylang/polylang.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/polylang/polylang@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/polylang/polylang@3x.png'
    ),
    'icon-cache-activation'       => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-activation/icon-cache-activation.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-activation/icon-cache-activation@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-activation/icon-cache-activation@3x.png'
    ),
    'icon-php-version'       => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-php-version/icon-php-version.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-php-version/icon-php-version@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-php-version/icon-php-version@3x.png'
    ),
    'group'       => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/group/group.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/group/group@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/group/group@3x.png'
    ),
    'icon-expire-headers'       => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-expire-headers/icon-expire-headers.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-expire-headers/icon-expire-headers@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-expire-headers/icon-expire-headers@3x.png'
    ),
    'icon-cache-clean-up'       => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-clean-up/icon-cache-clean-up.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-clean-up/icon-cache-clean-up@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-clean-up/icon-cache-clean-up@3x.png'
    ),
    'icon-cache-clean-up-copy'       => array(
        '1x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-clean-up-copy/icon-cache-clean-up-copy.png',
        '2x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-clean-up-copy/icon-cache-clean-up-copy@2x.png',
        '3x' => WPMETASEO_PLUGIN_URL . 'assets/images/dashboard/icon-cache-clean-up-copy/icon-cache-clean-up-copy@3x.png'
    ),

);

$site_name              = preg_replace('/(^(http|https):\/\/[w]*\.*)/', '', get_site_url());
$pieces                 = explode('/', $site_name);
$url                    = 'http://www.alexa.com/siteinfo/' . $pieces[0];
$dashboard              = new MetaSeoDashboard();
$options_dashboard      = get_option('options_dashboard');
$plugin_imgRecycle_file = 'imagerecycle-pdf-image-compression/wp-image-recycle.php';

// get web screenshot
$upload_dir = wp_upload_dir();
$server_check = parse_url(home_url());
if (isset($server_check['host']) && $server_check['host'] === 'localhost') {
    $web_screenshot = 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png';
} else {
    if (!file_exists($upload_dir['basedir'] . '/wpms-web-screenshot.jpg')) {
        $urlboxUrl = $dashboard::thumbalizr();
        if ($urlboxUrl === 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png') {
            $web_screenshot = 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png';
        } else {
            // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Fix warning in some case
            $content = @file_get_contents($urlboxUrl);
            if ($content) {
                file_put_contents($upload_dir['basedir'] . '/wpms-web-screenshot.jpg', $content);
                $web_screenshot = $upload_dir['baseurl'] . '/wpms-web-screenshot.jpg';
            } else {
                $web_screenshot = 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png';
            }
        }
    } else {
        $web_screenshot = $upload_dir['baseurl'] . '/wpms-web-screenshot.jpg';
    }
}

if (class_exists('MetaSeoAddonAdmin')) {
    $badge = __('Pro addon installed', 'wp-meta-seo');
} else {
    $badge = __('Pro addon feature', 'wp-meta-seo');
}
?>
<div class="dashboard">
    <h1 class="top_h1"><?php esc_html_e('SEO DASHBOARD', 'wp-meta-seo') ?></h1>
    <a class="top_link" href="<?php echo esc_url(home_url()) ?>" target="_blank"><?php echo esc_url(home_url()) ?></a>
    <div class="wpms_width_100 wpms_left p-tb-20">
        <div class="wpms_left web-left">
            <img class="site_img" src="<?php echo esc_url($web_screenshot) ?>">
            <input type="button" class="button btn-reload-web" value="<?php esc_html_e('Reload', 'wp-meta-seo'); ?>">
            <img class="page-loader" src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/page_loader.gif') ?>">
        </div>

        <div class="wpms_right dash-infos-right">
            <div class="ju-settings-option">
                <div class="wpms_row_full">
                    <label class="ju-setting-label">
                        <img src="<?php echo esc_url($addon_imgs['icon-cache-activation']['1x']) ?>"
                             srcset="<?php echo esc_url($addon_imgs['icon-cache-activation']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['icon-cache-activation']['3x']) ?> 3x"
                             class="">
                    </label>

                    <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('It’s better using a permalink structure that is adding
                  in your URL the category name and content title. This parameter can be changed
                   in Settings > Permalinks WordPress menu.
                    Tag recommended is %category%/%postname%', 'wp-meta-seo') ?>"><?php esc_html_e('URL Rewrite', 'wp-meta-seo'); ?></label>
                    <div class="right-checkbox">
                        <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                             height="24"
                             class="img-infos-tooltip wpms_dash_permalink" data-alt="1234">
                    </div>
                </div>
            </div>

            <div class="ju-settings-option">
                <div class="wpms_row_full">
                    <label class="ju-setting-label">
                        <img src="<?php echo esc_url($addon_imgs['icon-php-version']['1x']) ?>"
                             srcset="<?php echo esc_url($addon_imgs['icon-php-version']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['icon-php-version']['3x']) ?> 3x"
                             class="">
                    </label>

                    <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('Display image at its natural size, do not use HTML resize.
                  It happens usually when you use handles to resize an image. You have a bulk
                   edition tool to fix that.', 'wp-meta-seo') ?>"><?php esc_html_e('HTML, Image resizing', 'wp-meta-seo'); ?></label>
                    <div class="right-checkbox">
                        <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                             height="24"
                             class="img-infos-tooltip wpms_dash_imgsresize">
                    </div>
                </div>
            </div>

            <div class="ju-settings-option">
                <div class="wpms_row_full">
                    <label class="ju-setting-label">
                        <img src="<?php echo esc_url($addon_imgs['group']['1x']) ?>"
                             srcset="<?php echo esc_url($addon_imgs['group']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['group']['3x']) ?> 3x"
                             class="">
                    </label>

                    <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('Meta titles are displayed in search engine results
                  as a page title. It’s a good thing for SEO to have some custom and attractive ones.
                   Be sure to fill at least the met information on your most popular pages', 'wp-meta-seo') ?>"><?php esc_html_e('Meta Titles', 'wp-meta-seo'); ?></label>
                    <div class="right-checkbox">
                        <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                             height="24"
                             class="img-infos-tooltip wpms_dash_metatitle">
                    </div>
                </div>
            </div>

            <div class="ju-settings-option">
                <div class="wpms_row_full">
                    <label class="ju-setting-label">
                        <img src="<?php echo esc_url($addon_imgs['icon-expire-headers']['1x']) ?>"
                             srcset="<?php echo esc_url($addon_imgs['icon-expire-headers']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['icon-expire-headers']['3x']) ?> 3x"
                             class="">
                    </label>

                    <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('We recommend to use both alt and title text. The main advantage is that it helps search engines discover and index your website images. Plus, those tags improve the accessibility of your website by giving more information about your images. Use our bulk image tool to quickly check and fix that', 'wp-meta-seo') ?>"><?php esc_html_e('Image Alt', 'wp-meta-seo'); ?></label>
                    <div class="right-checkbox">
                        <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                             height="24"
                             class="img-infos-tooltip wpms_dash_imgsmeta">
                    </div>
                </div>
            </div>

            <div class="ju-settings-option">
                <div class="wpms_row_full">
                    <label class="ju-setting-label">
                        <img src="<?php echo esc_url($addon_imgs['icon-cache-clean-up']['1x']) ?>"
                             srcset="<?php echo esc_url($addon_imgs['icon-cache-clean-up']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['icon-cache-clean-up']['3x']) ?> 3x"
                             class="">
                    </label>

                    <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('Meta descriptions are displayed in search
                  engine results as a page description. It’s a good thing for SEO to have some
                   custom and attractive ones. Be sure to fill at least the meta information on
                    your most popular pages.', 'wp-meta-seo') ?>"><?php esc_html_e('Meta Description', 'wp-meta-seo'); ?></label>
                    <div class="right-checkbox">
                        <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                             height="24"
                             class="img-infos-tooltip wpms_dash_metadesc">
                    </div>
                </div>
            </div>

            <div class="ju-settings-option">
                <div class="wpms_row_full">
                    <label class="ju-setting-label">
                        <i class="material-icons wpms-middle">
                            link
                        </i>
                    </label>

                    <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('The link title attribute does not have any SEO
                  value for links. BUT links titles can influence click behavior for users, which may
                   indirectly affect your SEO performance', 'wp-meta-seo') ?>"><?php esc_html_e('Link titles', 'wp-meta-seo'); ?></label>
                    <div class="right-checkbox">
                        <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                             height="24"
                             class="img-infos-tooltip wpms_dash_linkmeta">
                    </div>
                </div>
            </div>

            <div class="ju-settings-option">
                <div class="wpms_row_full">
                    <label class="ju-setting-label">
                        <img src="<?php echo esc_url($addon_imgs['icon-cache-clean-up-copy']['1x']) ?>"
                             srcset="<?php echo esc_url($addon_imgs['icon-cache-clean-up-copy']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['icon-cache-clean-up-copy']['3x']) ?> 3x"
                             class="">
                    </label>

                    <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('It is highly recommended to update or add new content on
                  your website quite frequently. At least 3 updated or new
                   content per month would be great :)', 'wp-meta-seo') ?>"><?php esc_html_e('Fresh content', 'wp-meta-seo'); ?></label>
                    <div class="right-checkbox">
                        <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                             height="24"
                             class="img-infos-tooltip wpms_dash_newcontent">
                    </div>
                </div>
            </div>

            <?php
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                if (!class_exists('MetaSeoAddonAdmin')) {
                    require_once WPMETASEO_ADDON_PLUGIN_DIR . '/inc/class.metaseo-addon-admin.php';
                }
                ?>
                <div class="ju-settings-option">
                    <div class="wpms_row_full">
                        <label class="ju-setting-label">
                            <i class="material-icons wpms-middle">
                                done_all
                            </i>
                        </label>

                        <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('Check for duplicate meta titles in your content. Make sure your meta titles are unique in each content', 'wp-meta-seo') ?>"><?php esc_html_e('Duplicate meta titles', 'wp-meta-seo'); ?></label>
                        <div class="right-checkbox">
                            <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                                 height="24"
                                 class="img-infos-tooltip wpms_dash_duplicate_metatitle">
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>

            <div class="ju-settings-option">
                <div class="wpms_row_full">
                    <label class="ju-setting-label">
                        <i class="material-icons wpms-middle">
                            code
                        </i>
                    </label>

                    <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('It is recommended to generate a XML sitemap and submit it to the Google Search Console', 'wp-meta-seo') ?>"><?php esc_html_e('XML Sitemap', 'wp-meta-seo'); ?></label>
                    <div class="right-checkbox">
                        <?php
                        if (get_option('wpms_sitemap_submit', false)) :
                            ?>
                            <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/checklist/checklist.png') ?>"
                                 class="img-infos-tooltip" data-alt="<?php echo esc_html__('Sitemap submited', 'wp-meta-seo') ?>">
                        <?php else : ?>
                            <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-info/icon-info.png') ?>"
                                 class="img-infos-tooltip" data-alt="<?php echo esc_html__('Sitemap not submit', 'wp-meta-seo') ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                if (!class_exists('MetaSeoAddonAdmin')) {
                    require_once WPMETASEO_ADDON_PLUGIN_DIR . '/inc/class.metaseo-addon-admin.php';
                }
                ?>
                <div class="ju-settings-option">
                    <div class="wpms_row_full">
                        <label class="ju-setting-label">
                            <i class="material-icons wpms-middle">
                                done_all
                            </i>
                        </label>

                        <label class="ju-setting-label label-dash-widgets" data-alt="<?php esc_attr_e('Check for duplicate meta descriptions in your content. Make sure your meta descriptions are unique in each content', 'wp-meta-seo') ?>"><?php esc_html_e('Duplicate meta descriptions', 'wp-meta-seo'); ?></label>
                        <div class="right-checkbox">
                            <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/update_loading.gif') ?>"
                                 height="24"
                                 class="img-infos-tooltip wpms_dash_duplicate_metadesc">
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <div class="wpms_width_100 wpms_left p-tb-20 addon-feature">
        <h2 class="title-top"><?php esc_html_e('Additional Optimization', 'wp-meta-seo'); ?></h2>
        <p class="description description-top"><?php esc_html_e('This is the next optimizations to go for a really better SEO performance', 'wp-meta-seo'); ?></p>
        <div class="ju-settings-option">
            <div class="addon-img">
                <img src="<?php echo esc_url($addon_imgs['search_console']['1x']) ?>"
                     srcset="<?php echo esc_url($addon_imgs['search_console']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['search_console']['3x']) ?> 3x"
                     class="m-t-50">
            </div>
            <div class="wpms_row_full">
                <label class="ju-setting-label label-dash-widgets"><?php esc_html_e('Google search console', 'wp-meta-seo'); ?></label>
                <div class="right-checkbox">
                    <div class="panel-addon"><a href="https://www.joomunited.com/wordpress-products/wp-meta-seo" target="_blank"><?php echo esc_html($badge); ?></a></div>
                </div>
            </div>
            <p class="description p-d-20"><?php esc_html_e('Connect WP Meta SEO and the Google Search Console to get Google keywords recommendation while writing your content!', 'wp-meta-seo'); ?></p>
        </div>

        <div class="ju-settings-option">
            <div class="addon-img">
                <img src="<?php echo esc_url($addon_imgs['404']['1x']) ?>"
                     srcset="<?php echo esc_url($addon_imgs['404']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['404']['3x']) ?> 3x"
                     class="m-t-50">
            </div>
            <div class="wpms_row_full">
                <label class="ju-setting-label label-dash-widgets"><?php esc_html_e('404 Automatic Index', 'wp-meta-seo'); ?></label>
                <div class="right-checkbox">
                    <div class="panel-addon"><a href="https://www.joomunited.com/wordpress-products/wp-meta-seo" target="_blank"><?php echo esc_html($badge); ?></a></div>
                </div>
            </div>
            <p class="description p-d-20"><?php esc_html_e('Activate the 404 error automatic index to record all internal and external 404 errors along with hits, source, text...', 'wp-meta-seo'); ?></p>
        </div>

        <div class="ju-settings-option m-r-0">
            <div class="addon-img">
                <img src="<?php echo esc_url($addon_imgs['business']['1x']) ?>"
                     srcset="<?php echo esc_url($addon_imgs['business']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['business']['3x']) ?> 3x"
                     class="m-t-30">
            </div>
            <div class="wpms_row_full">
                <label class="ju-setting-label label-dash-widgets"><?php esc_html_e('Google local business', 'wp-meta-seo'); ?></label>
                <div class="right-checkbox">
                    <div class="panel-addon"><a href="https://www.joomunited.com/wordpress-products/wp-meta-seo" target="_blank"><?php echo esc_html($badge); ?></a></div>
                </div>
            </div>
            <p class="description p-d-20"><?php esc_html_e('Google My Business is a free and easy-to-use tool for businesses and organizations to manage their online presence across Google, including Search and Maps.', 'wp-meta-seo'); ?></p>
        </div>

        <div class="ju-settings-option">
            <div class="addon-img">
                <img src="<?php echo esc_url($addon_imgs['woocommerce']['1x']) ?>"
                     srcset="<?php echo esc_url($addon_imgs['woocommerce']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['woocommerce']['3x']) ?> 3x"
                     class="m-t-55">
            </div>
            <div class="wpms_row_full">
                <label class="ju-setting-label label-dash-widgets"><?php esc_html_e('WOOCOMERCE', 'wp-meta-seo'); ?></label>
                <div class="right-checkbox">
                    <div class="panel-addon"><a href="https://www.joomunited.com/wordpress-products/wp-meta-seo" target="_blank"><?php echo esc_html($badge); ?></a></div>
                </div>
            </div>
            <p class="description p-d-20"><?php esc_html_e('Edit the meta information for the WooCommerce products category listing', 'wp-meta-seo'); ?></p>
        </div>

        <div class="ju-settings-option">
            <div class="addon-img">
                <img src="<?php echo esc_url($addon_imgs['polylang']['1x']) ?>"
                     srcset="<?php echo esc_url($addon_imgs['polylang']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['polylang']['3x']) ?> 3x"
                     class="m-t-30">
            </div>
            <div class="wpms_row_full">
                <label class="ju-setting-label label-dash-widgets"><?php esc_html_e('POLYLANG', 'wp-meta-seo'); ?></label>
                <div class="right-checkbox">
                    <div class="panel-addon"><a href="https://www.joomunited.com/wordpress-products/wp-meta-seo" target="_blank"><?php echo esc_html($badge); ?></a></div>
                </div>
            </div>
            <p class="description p-d-20"><?php esc_html_e('Polylang multilingual plugin optimized. Filter your content by language in the meta and image bulk edition. Generate sitemap by Polylang languages', 'wp-meta-seo'); ?></p>
        </div>

        <div class="ju-settings-option m-r-0">
            <div class="addon-img">
                <img src="<?php echo esc_url($addon_imgs['polylang']['1x']) ?>"
                     srcset="<?php echo esc_url($addon_imgs['polylang']['2x']) ?> 2x, <?php echo esc_url($addon_imgs['polylang']['3x']) ?> 3x"
                     class="m-t-30">
            </div>
            <div class="wpms_row_full">
                <label class="ju-setting-label label-dash-widgets"><?php esc_html_e('WPML', 'wp-meta-seo'); ?></label>
                <div class="right-checkbox">
                    <div class="panel-addon"><a href="https://www.joomunited.com/wordpress-products/wp-meta-seo" target="_blank"><?php echo esc_html($badge); ?></a></div>
                </div>
            </div>
            <p class="description p-d-20"><?php esc_html_e('WPML multilingual plugin optimized. Filter your content by language in the meta and image bulk edition. Generate sitemap by WPML languages', 'wp-meta-seo'); ?></p>
        </div>
    </div>
</div>