<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<form method="post">
    <?php wp_nonce_field('wpms-setup-wizard', 'wizard_nonce'); ?>
    <div class="wizard-header">
        <div class="title font-size-35"><?php esc_html_e('Environment Check', 'wp-meta-seo'); ?></div>
        <p class="description">
            <?php esc_html_e('We have checked your server environment. 
            If you see some warning below it means that some plugin features may not work properly.
            Reload the page to refresh the results', 'wp-meta-seo'); ?>
        </p>
    </div>
    <div class="wizard-content">
        <div class="version-container">
            <div class="label_text p-b-20"><?php esc_html_e('PHP Version', 'wp-meta-seo'); ?></div>
            <div class="ju-settings-option wpms_width_100">
                <div class="wpms_row_full">
                    <label class="ju-setting-label php_version">
                        <?php esc_html_e('PHP ', 'wp-meta-seo'); ?>
                        <?php echo esc_html(PHP_VERSION) ?>
                        <?php esc_html_e('version', 'wp-meta-seo'); ?>
                    </label>

                    <div class="right-checkbox">
                        <?php if (version_compare(PHP_VERSION, '5.3', '<')) : ?>
                            <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information.png') ?>"
                                 srcset="<?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information@2x.png') ?> 2x, <?php echo esc_url(WPMETASEO_PLUGIN_URL . '/assets/images/icon-information/icon-information@3x.png') ?> 3x"
                                 class="img_warning">
                        <?php else : ?>
                            <input type="checkbox" checked disabled class="filled-in media_checkbox"/>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <?php if (version_compare(PHP_VERSION, '5.3', '<')) : ?>
                <p class="description text_left">
                    <?php esc_html_e('Your PHP version is ', 'wp-meta-seo'); ?>
                    <?php echo esc_html(PHP_VERSION) ?>
                    <?php esc_html_e('. For performance and security reasons it better to run PHP 7.2+. Comparing to previous versions the execution time of PHP 7.X is more than twice as fast and has 30 percent lower memory consumption', 'wp-meta-seo'); ?>
                </p>
            <?php else : ?>
                <p class="description">
                    <?php esc_html_e('Great ! Your PHP version is ', 'wp-meta-seo'); ?>
                    <?php echo esc_html(PHP_VERSION) ?>
                </p>
            <?php endif; ?>

        </div>

        <div class="other-container">
            <div class="label_text p-b-20"><?php esc_html_e('PHP Extensions', 'wp-meta-seo'); ?></div>
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
                <p class="description text_left">
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
                <p class="description text_left">
                    <?php esc_html_e('PHP libxml extension has not been detected. You need to activate in order to load site screenshot homepage image in dashboard', 'wp-meta-seo'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <div class="wizard-footer">
        <div class="wpms_row_full">
            <input type="submit" value="<?php esc_html_e('Continue', 'wp-meta-seo'); ?>" class="m-tb-20"
                   name="wpms_save_step"/>
        </div>

        <a href="<?php echo esc_url(admin_url('admin.php?page=metaseo_dashboard')) ?>"
           class="go-to-dash"><span><?php esc_html_e('Skip installer and go to Dashboard', 'wp-meta-seo'); ?></span></a>
    </div>
</form>