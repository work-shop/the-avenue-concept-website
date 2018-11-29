<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
wp_enqueue_style('m-style-qtip');
wp_enqueue_style('wpms-myqtip');
wp_enqueue_script('jquery-qtip');
wp_enqueue_script('my-qtips-js');

$image_src = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/welcome-illustration/welcome-illustration.png';
$srcset2x  = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/welcome-illustration/welcome-illustration@2x.png';
$srcset3x  = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/welcome-illustration/welcome-illustration@3x.png';
$lefts     = array(
    array(
        'title' => __('Google Search Console Keywords', 'wp-meta-seo'),
        'help'  => __('During the content edition, WP Meta SEO will detect similar keywords with the Google Search Console and return suggestions based on that', 'wp-meta-seo')
    ),
    array(
        'title' => __('Duplicate meta checker', 'wp-meta-seo'),
        'help'  => __('Check and fix in one click duplicate meta from the bulk meta editor', 'wp-meta-seo')
    ),
    array(
        'title' => __('404 errors autoindex', 'wp-meta-seo'),
        'help'  => __('Automatically run 404 error check in your content and make redirect', 'wp-meta-seo')
    ),
    array(
        'title' => __('Email report with WP Meta SEO data', 'wp-meta-seo'),
        'help'  => __('Send Email report with custom content and WP Meta SEO content (404 errors, missing meta...)', 'wp-meta-seo')
    ),
    array(
        'title' => __('Email Report With Analytics', 'wp-meta-seo'),
        'help'  => __('Send Email report with Google Analytics data set of your choice', 'wp-meta-seo')
    ),
    array(
        'title' => __('Custom post type sitemap (WooCommerce...)', 'wp-meta-seo'),
        'help'  => __('Use WordPress Custom Post Type as sitemap source. For example add automatically WooCommerce content in your XML/HTML sitemaps', 'wp-meta-seo')
    ),
    array(
        'title' => __('Sitemap automatic submission', 'wp-meta-seo'),
        'help'  => __('Automatically submit your sitemap to the Google Search Console and index your page URLs faster', 'wp-meta-seo')
    )
);

$rights = array(
    array(
        'title' => __('Sitemap link checker', 'wp-meta-seo'),
        'help'  => __('Run automatic sitemap link check to detect errors on sitemap links', 'wp-meta-seo')
    ),
    array(
        'title' => __('SEO for WPML and Polylang', 'wp-meta-seo'),
        'help'  => __('Filter your content by language in the meta and image bulk edition. Generate sitemap by WPML and Polylang languages', 'wp-meta-seo')
    ),
    array(
        'title' => __('Redirect rules and custom redirect', 'wp-meta-seo'),
        'help'  => __('Add rules to automatically redirect a set of URL, a custom URL or 404 errors', 'wp-meta-seo')
    ),
    array(
        'title' => __('Google search console 404 redirect', 'wp-meta-seo'),
        'help'  => __('Import 404 errors from the Google Search Console and redirect them', 'wp-meta-seo')
    ),
    array(
        'title' => __('WooCommerce SEO Optimization', 'wp-meta-seo'),
        'help'  => __('Edit the meta information for the WooCommerce product category listing', 'wp-meta-seo')
    ),
    array(
        'title' => __('Redirect with link manager', 'wp-meta-seo'),
        'help'  => __('Redirect using the WordPress link manager', 'wp-meta-seo')
    ),
    array(
        'title' => __('Google Local Business', 'wp-meta-seo'),
        'help'  => __('Add Google Business Local Information for your website and get your business information displayed in search results', 'wp-meta-seo')
    )
);
?>
<div class="dashboard">
    <div class="better-top">
        <img src="<?php echo esc_url($image_src); ?>"
             srcset="<?php echo esc_url($srcset2x); ?> 2x,<?php echo esc_url($srcset3x); ?> 3x"
             class="Illustration_Welcome">
        <h1 class="top_h1"><?php esc_html_e('Get More SEO Performance', 'wp-meta-seo') ?></h1>
        <h1 class="top_h1 wpms-no-margin"><?php esc_html_e('with WP Meta SEO PRO ADDON', 'wp-meta-seo') ?></h1>
    </div>

    <div class="wpms_width_100 wpms_left p-tb-20 addon-feature">
        <div class="better-layout wpms_left">
            <?php
            foreach ($lefts as $left) :
                ?>
                <div class="ju-settings-option">
                    <div class="wpms_row_full">
                        <label class="ju-setting-label label-dash-widgets"
                               data-alt="<?php echo esc_html($left['title']); ?>"><?php echo esc_html($left['title']); ?></label>
                        <div class="right-checkbox">
                            <div class="panel-addon"><?php esc_html_e('Pro addon feature', 'wp-meta-seo'); ?></div>
                        </div>
                    </div>
                    <p class="description p-d-20"><?php echo esc_html($left['help']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="better-layout wpms_right">
            <?php
            foreach ($rights as $right) :
                ?>
                <div class="ju-settings-option">
                    <div class="wpms_row_full">
                        <label class="ju-setting-label label-dash-widgets"
                               data-alt="<?php echo esc_html($right['title']); ?>"><?php echo esc_html($right['title']); ?></label>
                        <div class="right-checkbox">
                            <div class="panel-addon"><?php esc_html_e('Pro addon feature', 'wp-meta-seo'); ?></div>
                        </div>
                    </div>
                    <p class="description p-d-20"><?php echo esc_html($right['help']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>