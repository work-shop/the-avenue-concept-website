<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$wizard = new WpmsInstallWizard();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
$step      = isset($_GET['step']) ? sanitize_key($_GET['step']) : '';
$next_link = $wizard->getNextLink($step);
?>

<form method="post">
    <?php wp_nonce_field('wpms-setup-wizard', 'wizard_nonce'); ?>
    <input type="hidden" name="wpms_save_step" value="1"/>
    <div class="wizard-header">
        <div class="title font-size-35"><?php esc_html_e('Social', 'wp-meta-seo'); ?></div>
        <p class="description"><?php esc_html_e('If your website got user social engagement, you can customize the Twitter and facebook preview of your content here.', 'wp-meta-seo') ?></p>
    </div>
    <div class="wizard-content">
        <div class="ju-settings-option wpms_width_100 p-d-20">
            <div class="wpms_row_full p-d-20">
                <div class="ju-settings-option wpms-no-shadow wpms_width_100 wpms-no-shadow p-b-20">
                    <label class="wpms_width_100 p-b-20 wpms_left text label_text">
                        <?php esc_html_e('Facebook profile url', 'wp-meta-seo'); ?>
                    </label>

                    <label>
                        <input type="text" class="metaseo_showfacebook wpms_width_100 text_field"
                               name="metaseo_showfacebook">
                    </label>
                </div>

                <div class="ju-settings-option wpms-no-shadow wpms_width_100 wpms-no-shadow p-b-20">
                    <label class="wpms_width_100 p-b-20 wpms_left text label_text">
                        <?php esc_html_e('Facebook App ID', 'wp-meta-seo'); ?>
                    </label>

                    <label>
                        <input type="text" class="metaseo_showfbappid wpms_width_100 text_field"
                               name="metaseo_showfbappid">
                    </label>
                </div>

                <div class="ju-settings-option wpms-no-shadow wpms_width_100 wpms-no-shadow p-b-20">
                    <label class="wpms_width_100 p-b-20 wpms_left text label_text">
                        <?php esc_html_e('Twitter username', 'wp-meta-seo'); ?>
                    </label>

                    <label>
                        <input type="text" class="metaseo_showtwitter wpms_width_100 text_field"
                               name="metaseo_showtwitter">
                    </label>
                </div>

                <div class="ju-settings-option wpms-no-shadow wpms_width_100 wpms-no-shadow p-b-20">
                    <label class="wpms_width_100 p-b-20 wpms_left text label_text">
                        <?php esc_html_e('The default card type to use', 'wp-meta-seo'); ?>
                    </label>

                    <label>
                        <select class="metaseo_twitter_card text_field wpms_width_100" name="metaseo_twitter_card">
                            <option value="summary"><?php esc_html_e('Summary', 'wp-meta-seo'); ?></option>
                            <option value="summary_large_image"><?php esc_html_e('Summary with large image', 'wp-meta-seo'); ?></option>
                        </select>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="wizard-footer">
        <div class="wpms_row_full">
            <input type="submit" value="<?php esc_html_e('Continue', 'wp-meta-seo'); ?>" class="m-tb-20"
                   name="wpms_save_step"/>
        </div>
    </div>
</form>