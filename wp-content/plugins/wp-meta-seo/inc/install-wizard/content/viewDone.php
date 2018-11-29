<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$image_src = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/done/illustration-done.png';
$srcset2x  = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/done/illustration-done@2x.png';
$srcset3x  = WPMETASEO_PLUGIN_URL . 'inc/install-wizard/content/done/illustration-done@3x.png';
?>
<div class="wizard-content-done">
    <div class="wizard-done">
        <div class="wizard-done-image">
            <img src="<?php echo esc_url($image_src); ?>"
                 srcset="<?php echo esc_url($srcset2x); ?> 2x,<?php echo esc_url($srcset3x); ?> 3x"
                 class="Illustration---Done">

        </div>
        <div class="wizard-done-container">
            <div class="title"><?php esc_html_e('Done', 'wp-meta-seo') ?></div>
            <p class="description">
                <?php esc_html_e('You have now completed the plugin quick configuration', 'wp-meta-seo') ?>
            </p>
        </div>
        <div class="wizard-done-footer configuration-footer">
            <a href="<?php echo esc_url(admin_url('admin.php?page=metaseo_dashboard')) ?>" class="button">
                <?php esc_html_e('Go to dashboard', 'wp-meta-seo'); ?></a>
        </div>
    </div>
</div>
