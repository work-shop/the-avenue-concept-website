<?php
if (!empty($_COOKIE[$class_btn_close])) {
    $check = time() - (int)$_COOKIE[$class_btn_close];
    $month = 30 * 24 * 60 * 60;
}

if ((empty($_COOKIE[$class_btn_close]) || (!empty($_COOKIE[$class_btn_close]) && $check >= $month))
    && !is_plugin_active(WPMSEO_ADDON_FILENAME)) :
    ?>
    <div class="wpms_notification wpms_wrap_notification" style="width: <?php echo $w ?>">
        <div class="notification_dashboard">
            <div class="tooltipped">
                <div class="panel panel-updates dashboard-card">
                    <div class="panel-body">
                        <div class="row">
                            <div class="wpms_dashboard_widgets_content">
                                <span class="dashboard_noti_title">
                                    <?php _e('WP META SEO PRO ADDON', 'wp-meta-seo') ?>
                                </span>
                                <p class="dashboard-title msg"><?php echo $text ?></p>
                                <a class="more-info" href="https://www.joomunited.com/wordpress-products/wp-meta-seo"
                                   target="_blank"><?php _e('MORE INFORMATION', 'wp-meta-seo') ?></a>
                                <a data-page="<?php echo $class_btn_close ?>"
                                   class="dashboard-title wpmsclose_notification <?php echo $class_btn_close ?>">
                                    <?php _e('CLOSE FOR ONE MONTH', 'wp-meta-seo') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>