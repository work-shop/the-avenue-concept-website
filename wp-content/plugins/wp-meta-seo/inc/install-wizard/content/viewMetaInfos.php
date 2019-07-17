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
        <div class="title font-size-35"><?php esc_html_e('Meta information', 'wp-meta-seo'); ?></div>
        <p class="description"><?php esc_html_e('Select and configure the main optimization options', 'wp-meta-seo') ?></p>
    </div>
    <div class="wizard-content">
        <div class="ju-settings-option wpms_width_100 p-d-20">
            <div class="wpms_row_full">
                <input type="hidden" name="metaseo_showtmetablock" value="0">
                <label class="ju-setting-label text">
                    <?php esc_html_e('Meta block & OnPage optimization', 'wp-meta-seo') ?>
                </label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="metaseo_showtmetablock" value="1" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
                <p class="description text_left p-lr-20">
                    <?php esc_html_e('Active plugin feature: Fill the seach engine page title and description and analyse your  page content', 'wp-meta-seo'); ?>
                </p>
            </div>
            <div class="wpms_row_full p-lr-20">
                <a class="meta-more-details expanded"><?php esc_html_e('More details', 'wp-meta-seo') ?></a>
                <img src="<?php echo esc_url(WPMETASEO_PLUGIN_URL . 'assets/images/metablock.png'); ?>" width="560"
                     height="256">
            </div>
        </div>

        <div class="ju-settings-option  wpms_width_100 p-d-20">
            <div class="wpms_row_full">
                <input type="hidden" name="home_meta_active" value="0">
                <label class="ju-setting-label text">
                    <?php esc_html_e('Homepage meta information', 'wp-meta-seo') ?></label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="home_meta_active" value="1" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
                <p class="description text_left p-d-20">
                    <?php esc_html_e('Fill your Homepage search engine title and description for search engine', 'wp-meta-seo'); ?>
                </p>

                <div class="ju-settings-option wpms-no-shadow wpms_width_100 p-d-20">
                    <label class="wpms_width_100 p-b-20 wpms_left text label_text">
                        <?php esc_html_e('Search engine title', 'wp-meta-seo'); ?>
                    </label>

                    <label>
                        <input type="text" class="metaseo_title_home wpms_width_100 text_field"
                               name="metaseo_title_home">
                    </label>
                </div>

                <div class="ju-settings-option wpms-no-shadow wpms_width_100 p-d-20">
                    <label class="wpms_width_100 p-b-20 wpms_left text label_text">
                        <?php esc_html_e('Search engine description', 'wp-meta-seo'); ?>
                    </label>

                    <label>
                        <textarea class="metaseo_desc_home wpms_width_100 p-tb-20 text_field"
                                  name="metaseo_desc_home"></textarea>
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

        <a href="<?php echo esc_url(admin_url('admin.php?page=metaseo_dashboard')) ?>"
           class="go-to-dash"><span><?php esc_html_e('Skip installer and go to Dashboard', 'wp-meta-seo'); ?></span></a>
    </div>
</form>
<?php
// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript -- enqueue script not work
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<?php
// phpcs:enable
?>
<script>
    jQuery(document).ready(function ($) {
        $('.meta-more-details').on('click', function () {
            if ($(this).hasClass('expanded')) {
                $(this).removeClass('expanded');
            } else {
                $(this).addClass('expanded');
            }
        });
    });
</script>