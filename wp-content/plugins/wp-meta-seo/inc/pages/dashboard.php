<?php

if (!class_exists('MetaSeoDashboard')) {
    require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-dashboard.php');
}

wp_enqueue_style('m-style-qtip');
wp_enqueue_script('jquery-qtip');
wp_enqueue_style('wpms-myqtip');

$site_name = preg_replace('/(^(http|https):\/\/[w]*\.*)/', '', get_site_url());
$pieces = explode("/", $site_name);
$url = 'http://www.alexa.com/siteinfo/' . $pieces[0];
$dashboard = new MetaSeoDashboard();
$options_dashboard = get_option('options_dashboard');
$error_404 = $dashboard->get404Link();
$plugin_imgRecycle_file = 'imagerecycle-pdf-image-compression/wp-image-recycle.php';
?>
<h1 style="text-align: center;"><?php _e('WP Meta SEO dashboard', 'wp-meta-seo') ?></h1>
<div class="dashboard">
    <div class="col-md-9">
        <div class="row panel-statistics">
            <div class="wpms_dash_widgets wpms_dash_permalink"
                 data-alt="<?php _e('It’s better using a permalink structure that is adding
                  in your URL the category name and content title. This parameter can be changed
                   in Settings > Permalinks WordPress menu.
                    Tag recommended is %category%/%postname%', 'wp-meta-seo') ?>">
                <div class="row panel-statistics">
                    <div class="tooltipped">
                        <div class="panel panel-updates dashboard-card">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="wpms_dashboard_widgets_left">
                                        <h4 class="panel-title dashboard-title">
                                            <?php _e('PERMALINKS SETTINGS', 'wp-meta-seo') ?>
                                        </h4>
                                        <h3 class="dashboard-title percent_1">50%</h3>
                                        <p class="dashboard-title percent_2"><?php _e('Optimized at:', 'wp-meta-seo') ?>
                                            <span class="percent">50%</span></p>
                                    </div>
                                    <div class="wpms_dashboard_widgets_right">
                                        <div class="progress-rating">
                                            <div class="determinate percent_3" style="width: 50%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpms_dash_widgets wpms_dash_metatitle"
                 data-alt="<?php _e('Meta titles are displayed in search engine results
                  as a page title. It’s a good thing for SEO to have some custom and attractive ones.
                   Be sure to fill at least the met information on your most popular pages', 'wp-meta-seo') ?>">
                <div class="row panel-statistics">
                    <div class="tooltipped">
                        <div class="panel panel-updates dashboard-card">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="wpms_dashboard_widgets_left">
                                        <h4 class="panel-title dashboard-title">
                                            <?php _e('META TITLE', 'wp-meta-seo') ?>
                                        </h4>
                                        <h3 class="dashboard-title percent_1">0%</h3>
                                        <p class="dashboard-title percent_2">
                                            <?php _e('Meta title filled:', 'wp-meta-seo') ?>
                                            <span class="percent">0/0</span>
                                        </p>
                                    </div>
                                    <div class="wpms_dashboard_widgets_right">
                                        <div class="progress-rating">
                                            <div class="determinate percent_3" style="width: 0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpms_dash_widgets wpms_dash_metadesc"
                 data-alt="<?php _e('Meta descriptions are displayed in search
                  engine results as a page description. It’s a good thing for SEO to have some
                   custom and attractive ones. Be sure to fill at least the meta information on
                    your most popular pages.', 'wp-meta-seo') ?>">
                <div class="row panel-statistics">
                    <div class="tooltipped">
                        <div class="panel panel-updates dashboard-card">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="wpms_dashboard_widgets_left">
                                        <h4 class="panel-title dashboard-title">
                                            <?php _e('META DESCRIPTION', 'wp-meta-seo') ?>
                                        </h4>
                                        <h3 class="dashboard-title percent_1">0%</h3>
                                        <p class="dashboard-title percent_2">
                                            <?php _e('Meta description filled:', 'wp-meta-seo') ?>
                                            <span class="percent">0/0</span>
                                        </p>
                                    </div>
                                    <div class="wpms_dashboard_widgets_right">
                                        <div class="progress-rating">
                                            <div class="determinate percent_3" style="width: 0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
                if (!class_exists('MetaSeoAddonAdmin')) {
                    require_once WPMETASEO_ADDON_PLUGIN_DIR . '/inc/class.metaseo-addon-admin.php';
                }
                $metaseo_addon = new MetaSeoAddonAdmin();
                $duplicateTitle = $metaseo_addon->getDuplicateMetatitle();
                $duplicateDesc = $metaseo_addon->getDuplicateMetadesc();
                require_once WPMETASEO_ADDON_PLUGIN_DIR . '/inc/page/dashboard/duplicate_metatitle_widgets.php';
                require_once WPMETASEO_ADDON_PLUGIN_DIR . '/inc/page/dashboard/duplicate_metadesc_widgets.php';
            }
            ?>

            <div class="wpms_dash_widgets wpms_dash_imgsresize"
                 data-alt="<?php _e('Display image at its natural size, do not use HTML resize.
                  It happens usually when you use handles to resize an image. You have a bulk
                   edition tool to fix that.', 'wp-meta-seo') ?>">
                <div class="row panel-statistics">
                    <div class="tooltipped">
                        <div class="panel panel-updates dashboard-card">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="wpms_dashboard_widgets_left">
                                        <h4 class="panel-title dashboard-title">
                                            <?php _e('HTML IMAGE RESIZING', 'wp-meta-seo') ?>
                                        </h4>
                                        <h3 class="dashboard-title percent_1">0%</h3>
                                        <p class="dashboard-title percent_2">
                                            <?php _e('Wrong resized images:', 'wp-meta-seo') ?>
                                            <span class="percent">0/0</span>
                                        </p>
                                    </div>
                                    <div class="wpms_dashboard_widgets_right">
                                        <div class="progress-rating">
                                            <div class="determinate percent_3" style="width: 0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpms_dash_widgets wpms_dash_imgsmeta"
                 data-alt="<?php _e('We recommend to use both alt text.
                  The main advantage is that it helps search engines discover your images and display
                   them in image search results. Plus, these tags improve the accessibility of your site
                    and give more information about your images. Use our bulk
                     image tool to quickly check and fix that.', 'wp-meta-seo') ?>">
                <div class="row panel-statistics">
                    <div class="tooltipped">
                        <div class="panel panel-updates dashboard-card">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="wpms_dashboard_widgets_left">
                                        <h4 class="panel-title dashboard-title">
                                            <?php _e('IMAGE ALT', 'wp-meta-seo') ?>
                                        </h4>
                                        <h3 class="dashboard-title percent_1">0%</h3>
                                        <p class="dashboard-title percent_2">
                                            <?php _e('Image data filled (in content):', 'wp-meta-seo') ?>
                                            <span class="percent">0/0</span>
                                        </p>
                                    </div>
                                    <div class="wpms_dashboard_widgets_right">
                                        <div class="progress-rating">
                                            <div class="determinate percent_3" style="width: 0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpms_dash_widgets wpms_dash_newcontent"
                 data-alt="<?php _e('It is highly recommended to update or add new content on
                  your website quite frequently. At least 3 updated or new
                   content per month would be great :)', 'wp-meta-seo') ?>">
                <div class="row panel-statistics">
                    <div class="tooltipped">
                        <div class="panel panel-updates dashboard-card">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="wpms_dashboard_widgets_left">
                                        <h4 class="panel-title dashboard-title">
                                            <?php _e('NEW OR UPDATED CONTENT', 'wp-meta-seo') ?>
                                        </h4>
                                        <h3 class="dashboard-title percent_1">0%</h3>
                                        <p class="dashboard-title percent_2">
                                            <?php _e('Latest month new or updated content:', 'wp-meta-seo') ?>
                                            <span class="percent">0</span>
                                        </p>
                                    </div>
                                    <div class="wpms_dashboard_widgets_right">
                                        <div class="progress-rating">
                                            <div class="determinate percent_3" style="width: 0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpms_dash_widgets wpms_dash_linkmeta"
                 data-alt="<?php _e('The link title attribute does not have any SEO
                  value for links. BUT links titles can influence click behavior for users, which may
                   indirectly affect your SEO performance', 'wp-meta-seo') ?>">
                <div class="row panel-statistics">
                    <div class="tooltipped">
                        <div class="panel panel-updates dashboard-card">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="wpms_dashboard_widgets_left">
                                        <h4 class="panel-title dashboard-title">
                                            <?php _e('LINK TITLES', 'wp-meta-seo') ?>
                                        </h4>
                                        <h3 class="dashboard-title percent_1">0%</h3>
                                        <p class="dashboard-title percent_2">
                                            <?php _e('Links title completed:', 'wp-meta-seo') ?>
                                            <span class="percent">0/0</span>
                                        </p>
                                    </div>
                                    <div class="wpms_dashboard_widgets_right">
                                        <div class="progress-rating">
                                            <div class="determinate percent_3" style="width: 0"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpms_dash_widgets wpms_dash_404_error"
                 data-alt="<?php _e('A website with a bunch of 404 errors doesn’t provide a good
                  user experience, which is significantly important in content marketing and SEO.
                   We recommend to use our internal broken link checker and redirect tool to fix all
                    the 404 error you can periodically.', 'wp-meta-seo') ?>">
                <div class="row panel-statistics">
                    <div class="tooltipped">
                        <div class="panel panel-updates dashboard-card">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="wpms_dashboard_widgets_left">
                                        <h4 class="panel-title dashboard-title">
                                            <?php _e('404 ERRORS', 'wp-meta-seo') ?>
                                        </h4>
                                        <h3 class="dashboard-title percent_1"><?php echo $error_404['percent'] ?>%</h3>
                                        <p class="dashboard-title percent_2">
                                            <?php
                                            _e('Redirected 404 errors:', 'wp-meta-seo');
                                            echo $error_404['count_404_redirected'] . '/' . $error_404['count_404'];
                                            ?>
                                        </p>
                                    </div>
                                    <div class="wpms_dashboard_widgets_right">
                                        <div class="progress-rating">
                                            <div class="determinate percent_3"
                                                 style="width: <?php echo $error_404['percent'] ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (file_exists(WP_PLUGIN_DIR . '/imagerecycle-pdf-image-compression')) : ?>
                <?php
                if (!is_plugin_active($plugin_imgRecycle_file)) :
                    ?>
                    <div class="wpms_dash_widgets"
                         data-alt="<?php _e('Images represent around 60% of a web page weight.
                          An image compression reduce the image size by up to 70% while preserving
                           the same visual quality. Small loading time is great for SEO!', 'wp-meta-seo') ?>">
                        <div class="row panel-statistics">
                            <div class="tooltipped">
                                <div class="panel panel-updates dashboard-card">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="wpms_dashboard_widgets_left">
                                                <h4 class="panel-title dashboard-title">
                                                    <?php _e('IMAGE COMPRESSION', 'wp-meta-seo') ?>
                                                </h4>
                                                <h3 class="dashboard-title percent_1">0%</h3>
                                                <p class="dashboard-title percent_2">
                                                    <?php _e('Use ImageRecycle image compression
                                                     plugin to activate this feature', 'wp-meta-seo') ?>
                                                    : 0/0
                                                </p>
                                            </div>
                                            <div class="wpms_dashboard_widgets_right">
                                                <div class="progress-rating">
                                                    <div class="determinate percent_3" style="width: 0"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <?php $optimizer = $dashboard->getImagesCount(); ?>
                    <div class="wpms_dash_widgets"
                         data-alt="<?php _e('Images represent around 60% of a web page weight.
                          An image compression reduce the image size by up to 70% while preserving
                           the same visual quality. Small loading time is great for SEO!', 'wp-meta-seo') ?>">
                        <div class="row panel-statistics">
                            <div class="tooltipped">
                                <div class="panel panel-updates dashboard-card">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="wpms_dashboard_widgets_left">
                                                <h4 class="panel-title dashboard-title">
                                                    <?php _e('IMAGE COMPRESSION', 'wp-meta-seo') ?>
                                                </h4>
                                                <h3 class="dashboard-title percent_1">
                                                    <?php echo $optimizer['percent'] . '%' ?>
                                                </h3>
                                                <p class="dashboard-title percent_2">
                                                    <?php
                                                    _e('Compressed images', 'wp-meta-seo');
                                                    echo ': ';
                                                    echo $optimizer['image_optimize'] . '/' . $optimizer['count_image'];
                                                    ?>
                                                </p>
                                            </div>
                                            <div class="wpms_dashboard_widgets_right">
                                                <div class="progress-rating">
                                                    <div class="determinate percent_3"
                                                         style="width: <?php echo $optimizer['percent'] . '%' ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>

    <div style="width:75%;margin: 0 auto;min-height: 200px;padding: 0 10px 0 10px;">
        <div class="left">
            <div class="dashboard-left" id='dashboard-left'>
                <div id="alexa-ranking">
                    <?php $dashboard->displayRank($url) ?>
                </div>
            </div>
        </div>

        <div class="right">
            <div class="dashboard-right">
                <div style="display: none">
                    <?php _e("We can't get rank of this site from Alexa.com!", "wp-meta-seo") ?>
                </div>
                <div style="clear:left"></div>
                <div id="wpmetaseo-update-version">
                    <h4><?php echo __('Latest WP Meta SEO News', 'wp-meta-seo') ?></h4>
                    <ul>
                        <li><a target="_blank"
                               href="https://www.joomunited.com/wordpress-products/wp-meta-seo">
                                <?php _e('More information about WP Meta SEO', 'wp-meta-seo'); ?>
                            </a>
                        </li>
                        <li><a target="_blank"
                               href="https://www.joomunited.com/">
                                <?php _e('Other plugins from JoomUnited', 'wp-meta-seo'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
    if (!empty($_COOKIE['close_dashboard'])) {
        $check = time() - (int)$_COOKIE['close_dashboard'];
        $month = 30 * 24 * 60 * 60;
    }

    if ((empty($_COOKIE['close_dashboard']) || (!empty($_COOKIE['close_dashboard']) && $check >= $month))
        && !is_plugin_active(WPMSEO_ADDON_FILENAME)) :
        ?>
        <div class="wpms_dashboard_notification wpms_wrap_notification">
            <div class="notification_dashboard">
                <div class="tooltipped">
                    <div class="panel panel-updates dashboard-card">
                        <div class="panel-body">
                            <div class="row">
                                <div class="wpms_dashboard_widgets_content">
                                    <p class="dashboard_noti_title">
                                        <?php _e('WP META SEO PRO ADDON', 'wp-meta-seo') ?>
                                    </p>
                                    <p class="dashboard-title msg">
                                        <?php _e('Bring your WordPress website SEO to the next level with the PRO Addon:
                                         Email Report, Google Search Console Connect, Automatic Redirect,
                                          Advanced Sitemaps and more!', 'wp-meta-seo') ?>
                                    </p>
                                    <a class="more-info"
                                       href="https://www.joomunited.com/wordpress-products/wp-meta-seo"
                                       target="_blank"><?php _e('MORE INFORMATION', 'wp-meta-seo') ?></a>
                                    <a data-page="close_dashboard"
                                       class="dashboard-title wpmsclose_notification close_dashboard">
                                        <?php _e('CLOSE FOR ONE MONTH', 'wp-meta-seo') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        replace_url_img();
    });

    function replace_url_img() {
        var url = '<?php echo WPMETASEO_PLUGIN_URL; ?>';
        var icon_tip = url + 'img/icon_tip.png';
        var globe_sm = url + 'img/globe-sm.jpg';
        jQuery('.img-inline').attr('src', globe_sm);
        jQuery('#alexa-ranking .tt img').attr('src', icon_tip);
    }

</script>