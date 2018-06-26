<div class="wpms_dashboard_widgets">
    <div class="wpms_dash_widgets wpms_dash_permalink"
         data-alt="<?php _e('It’s better using a permalink structure that
          is adding in your URL the category name and content title.
           This parameter can be changed in Settings > Permalinks WordPress menu.
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
                                <div class="panel-bottom">
                                    <h3 class="dashboard-title percent_1">50%</h3>
                                    <span class="dashboard-title percent_2"><?php _e('Optimized at:', 'wp-meta-seo') ?>
                                        <span class="percent">50%</span></span>
                                </div>
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
         data-alt="<?php _e('Meta titles are displayed in search engine results as a page title.
          It’s a good thing for SEO to have some custom and attractive ones. Be sure to fill at least
           the met information on your most popular pages', 'wp-meta-seo') ?>">
        <div class="row panel-statistics">
            <div class="tooltipped">
                <div class="panel panel-updates dashboard-card">
                    <div class="panel-body">
                        <div class="row">
                            <div class="wpms_dashboard_widgets_left">
                                <h4 class="panel-title dashboard-title"><?php _e('META TITLE', 'wp-meta-seo') ?></h4>
                                <div class="panel-bottom">
                                    <h3 class="dashboard-title percent_1">0%</h3>
                                    <span class="dashboard-title percent_2">
                                        <?php _e('Meta title filled:', 'wp-meta-seo') ?>
                                        <span class="percent">0/0</span>
                                    </span>
                                </div>
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
         data-alt="<?php _e('Meta descriptions are displayed in search engine results as a page description.
          It’s a good thing for SEO to have some custom and attractive ones. Be sure to fill at least the meta
           information on your most popular pages.', 'wp-meta-seo') ?>">
        <div class="row panel-statistics">
            <div class="tooltipped">
                <div class="panel panel-updates dashboard-card">
                    <div class="panel-body">
                        <div class="row">
                            <div class="wpms_dashboard_widgets_left">
                                <h4 class="panel-title dashboard-title">
                                    <?php _e('META DESCRIPTION', 'wp-meta-seo') ?>
                                </h4>
                                <div class="panel-bottom">
                                    <h3 class="dashboard-title percent_1">0%</h3>
                                    <span class="dashboard-title percent_2">
                                        <?php _e('Meta description filled:', 'wp-meta-seo') ?>
                                        <span class="percent">0/0</span>
                                    </span>
                                </div>
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
                                <div class="panel-bottom">
                                    <h3 class="dashboard-title percent_1">0%</h3>
                                    <span class="dashboard-title percent_2">
                                        <?php _e('Wrong resized images:', 'wp-meta-seo') ?>
                                        <span class="percent">0/0</span>
                                    </span>
                                </div>
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
           them in image search results. Plus, these tags improve the accessibility of your site and
            give more information about your images. Use our bulk image
             tool to quickly check and fix that.', 'wp-meta-seo') ?>">
        <div class="row panel-statistics">
            <div class="tooltipped">
                <div class="panel panel-updates dashboard-card">
                    <div class="panel-body">
                        <div class="row">
                            <div class="wpms_dashboard_widgets_left">
                                <h4 class="panel-title dashboard-title">
                                    <?php _e('IMAGE ALT', 'wp-meta-seo') ?>
                                </h4>
                                <div class="panel-bottom">
                                    <h3 class="dashboard-title percent_1">0%</h3>
                                    <span class="dashboard-title percent_2">
                                        <?php _e('Image data filled (in content):', 'wp-meta-seo') ?>
                                        <span class="percent">0/0</span>
                                    </span>
                                </div>
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
         data-alt="<?php _e('It is highly recommended to update or add new content on your website quite frequently.
          At least 3 updated or new content per month would be great :)', 'wp-meta-seo') ?>">
        <div class="row panel-statistics">
            <div class="tooltipped">
                <div class="panel panel-updates dashboard-card">
                    <div class="panel-body">
                        <div class="row">
                            <div class="wpms_dashboard_widgets_left">
                                <h4 class="panel-title dashboard-title">
                                    <?php _e('NEW OR UPDATED CONTENT', 'wp-meta-seo') ?>
                                </h4>
                                <div class="panel-bottom">
                                    <h3 class="dashboard-title percent_1">0%</h3>
                                    <span class="dashboard-title percent_2">
                                        <?php _e('Latest month new or updated content:', 'wp-meta-seo') ?>
                                        <span class="percent">0</span>
                                    </span>
                                </div>
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
         data-alt="<?php _e('The link title attribute does not have any SEO value for links.
          BUT links titles can influence click behavior for users, which may indirectly affect
           your SEO performance', 'wp-meta-seo') ?>">
        <div class="row panel-statistics">
            <div class="tooltipped">
                <div class="panel panel-updates dashboard-card">
                    <div class="panel-body">
                        <div class="row">
                            <div class="wpms_dashboard_widgets_left">
                                <h4 class="panel-title dashboard-title"><?php _e('LINK TITLES', 'wp-meta-seo') ?></h4>
                                <div class="panel-bottom">
                                    <h3 class="dashboard-title percent_1">0%</h3>
                                    <span class="dashboard-title percent_2">
                                        <?php _e('Links title completed:', 'wp-meta-seo') ?>
                                        <span class="percent">0/0</span>
                                    </span>
                                </div>
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
         data-alt="<?php _e('A website with a bunch of 404 errors doesn’t provide a good user experience,
          which is significantly important in content marketing and SEO. We recommend to use our internal
           broken link checker and redirect tool to fix all the 404 error you can periodically.', 'wp-meta-seo') ?>">
        <div class="row panel-statistics">
            <div class="tooltipped">
                <div class="panel panel-updates dashboard-card">
                    <div class="panel-body">
                        <div class="row">
                            <div class="wpms_dashboard_widgets_left">
                                <h4 class="panel-title dashboard-title"><?php _e('404 ERRORS', 'wp-meta-seo') ?></h4>
                                <div class="panel-bottom">
                                    <h3 class="dashboard-title percent_1"><?php echo $error_404['percent'] ?>%</h3>
                                    <span class="dashboard-title percent_2">
                                        <?php
                                        _e('Redirected 404 errors', 'wp-meta-seo');
                                        echo ': ';
                                        echo $error_404['count_404_redirected'] . '/' . $error_404['count_404'];
                                        ?>
                                </div>
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
</div>