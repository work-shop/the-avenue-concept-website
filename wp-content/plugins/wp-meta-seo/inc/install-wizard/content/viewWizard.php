<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$image_src = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/welcome-illustration/welcome-illustration.png';
$srcset2x  = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/welcome-illustration/welcome-illustration@2x.png';
$srcset3x  = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/welcome-illustration/welcome-illustration@3x.png';
?>
<form method="post">
    <div class="start-wizard">
        <div class="start-wizard-image">
            <img src="<?php echo esc_url($image_src); ?>"
                 srcset="<?php echo esc_url($srcset2x); ?> 2x,<?php echo esc_url($srcset3x); ?> 3x"
                 class="Illustration_Welcome">
        </div>
        <div class="start-wizard-container">
            <div class="title">
                <?php esc_html_e('Welcome to WP Meta SEO configuration wizard', 'wp-meta-seo') ?>
            </div>
            <p class="description">
                <?php esc_html_e('This wizard will help you with some server compatibility check and with plugin main configuration. We will guide you through the plugin main SEO setup', 'wp-meta-seo') ?>
            </p>
        </div>
        <div class="start-wizard-footer configuration-footer">
            <a href="<?php echo esc_url(add_query_arg('step', 'environment', remove_query_arg('activate_error'))) ?>"
               class="next-button">
                <input type="button" class="ju-button orange-button" value="<?php esc_attr_e('Continue to environment check', 'wp-meta-seo'); ?>">
            </a>

            <a href="<?php echo esc_url(admin_url('admin.php?page=metaseo_dashboard')) ?>" class="backup-button">
                <?php esc_html_e('Skip installer and go to Dashboard', 'wp-meta-seo'); ?></a>
        </div>
    </div>
</form>