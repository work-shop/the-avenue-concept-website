<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!empty($_COOKIE[$class_btn_close])) {
    $check = time() - (int) $_COOKIE[$class_btn_close];
    $month = 30 * 24 * 60 * 60;
}

if ((empty($_COOKIE[$class_btn_close]) || (!empty($_COOKIE[$class_btn_close]) && $check >= $month))
    && !is_plugin_active(WPMSEO_ADDON_FILENAME)) :
    ?>
    <div class="wpms_notification wpms_wrap_notification" style="width: <?php echo esc_html($w) ?>">
        <div class="notification_dashboard">
            <div class="tooltipped">
                <div class="panel panel-updates dashboard-card">
                    <div class="panel-body">
                        <div class="row">
                            <div class="wpms_dashboard_widgets_content">
                                <span class="dashboard_noti_title">
                                    <?php esc_html_e('WP META SEO PRO ADDON', 'wp-meta-seo') ?>
                                </span>
                                <p class="dashboard-title msg"><?php echo esc_html($text) ?></p>
                                <a class="more-info" href="https://www.joomunited.com/wordpress-products/wp-meta-seo"
                                   target="_blank"><?php esc_html_e('MORE INFORMATION', 'wp-meta-seo') ?></a>
                                <a data-page="<?php echo esc_attr($class_btn_close) ?>"
                                   class="<?php echo esc_attr('dashboard-title wpmsclose_notification ' . $class_btn_close) ?>">
                                    <?php esc_html_e('CLOSE FOR ONE MONTH', 'wp-meta-seo') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>