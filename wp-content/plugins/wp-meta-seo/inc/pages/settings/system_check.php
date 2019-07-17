<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="content-box wpms_width_80">
    <p class="ju-description text_center" style="margin: 0">
        <?php esc_html_e('We have checked your server environment. 
            If you see some warning below it means that some plugin features may not work properly.
            Reload the page to refresh the results', 'wp-meta-seo'); ?>
    </p>
    <div class="wpms_width_100 p-tb-20 wpms_left text label_text"><?php esc_html_e('PHP Version', 'wp-meta-seo'); ?></div>
    <div class="ju-settings-option wpms_width_100">
        <div class="wpms_row_full">
            <label class="ju-setting-label php_version">
                <?php esc_html_e('PHP ', 'wp-meta-seo'); ?>
                <?php echo esc_html(PHP_VERSION) ?>
                <?php esc_html_e('version', 'wp-meta-seo'); ?>
            </label>

            <div class="right-checkbox">
                <?php
                if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
                    //phpcs:ignore WordPress.XSS.EscapeOutput -- Echo icon html
                    echo '<i class="material-icons system-checkbox material-icons-success">check_circle</i>';
                } elseif (version_compare(PHP_VERSION, '7.2.0', '<') &&
                          version_compare(PHP_VERSION, '7.0.0', '>=')) {
                    echo '<img src="' . esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-notification.png') . '" class="img_notification">';
                } else {
                    echo '<i class="material-icons system-checkbox material-icons-info">info</i>';
                }
                ?>
            </div>

        </div>
    </div>

    <?php if (version_compare(PHP_VERSION, '7.2.0', '<')) : ?>
        <p class="ju-description text_left p_warning">
            <?php esc_html_e('Your PHP version is ', 'wp-meta-seo'); ?>
            <?php echo esc_html(PHP_VERSION) ?>
            <?php esc_html_e('. For performance and security reasons it better to run PHP 7.2+. Comparing to previous versions the execution time of PHP 7.X is more than twice as fast and has 30 percent lower memory consumption', 'wp-meta-seo'); ?>
        </p>
    <?php else : ?>
        <p class="ju-description text_center">
            <?php esc_html_e('Great ! Your PHP version is ', 'wp-meta-seo'); ?>
            <?php echo esc_html(PHP_VERSION) ?>
        </p>
    <?php endif; ?>


    <div class="wpms_width_100 p-tb-20 wpms_left text label_text"><?php esc_html_e('PHP Extensions', 'wp-meta-seo'); ?></div>
    <div class="ju-settings-option wpms_width_100">
        <div class="wpms_row_full">
            <label class="ju-setting-label"><?php esc_html_e('Curl', 'wp-meta-seo'); ?></label>
            <div class="right-checkbox">
                <?php if (!in_array('curl', get_loaded_extensions())) : ?>
                    <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information.png') ?>"
                         srcset="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information@2x.png') ?> 2x, <?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information@3x.png') ?> 3x"
                         class="img_warning">
                <?php else : ?>
                    <input type="checkbox" id="php_curl" name="php_curl" checked
                           value="php_curl" disabled class="filled-in media_checkbox"/>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (!in_array('curl', get_loaded_extensions())) : ?>
        <p class="ju-description p_warning">
            <?php esc_html_e('PHP Curl extension has not been detected. You need to activate in order to load video in media library and for all the cloud connections (like Google Drive, Dropbox...)', 'wp-meta-seo'); ?>
        </p>
    <?php endif; ?>

    <div class="ju-settings-option wpms_width_100">
        <div class="wpms_row_full">
            <label class="ju-setting-label"><?php esc_html_e('Libxml', 'wp-meta-seo'); ?></label>
            <div class="right-checkbox">
                <?php if (!extension_loaded('libxml')) : ?>
                    <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information.png') ?>"
                         srcset="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information@2x.png') ?> 2x, <?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information@3x.png') ?> 3x"
                         class="img_warning">
                <?php else : ?>
                    <input type="checkbox" id="libxml" name="libxml" checked
                           value="libxml" disabled class="filled-in media_checkbox"/>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (!extension_loaded('libxml')) : ?>
        <p class="ju-description p_warning">
            <?php esc_html_e('PHP libxml extension has not been detected. You need to activate in order to load site screenshot homepage image in dashboard', 'wp-meta-seo'); ?>
        </p>
    <?php endif; ?>
</div>