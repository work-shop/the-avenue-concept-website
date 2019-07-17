<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('MetaSeoLinkListTable')) {
    require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-link-list-table.php');
}

wp_enqueue_style('m-style-qtip');
wp_enqueue_script('jquery-qtip');
wp_enqueue_script('my-qtips-js');
add_thickbox();
$metaseo_list_table = new MetaSeoLinkListTable();
$metaseo_list_table->processAction();
$metaseo_list_table->prepare_items();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
if (!empty($_REQUEST['_wp_http_referer'])) {
    wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
    exit;
}
?>

<div class="wrap seo_extended_table_page">
    <div id="icon-edit-pages" class="icon32 icon32-posts-page"></div>
    <form id="wp-seo-meta-form" class="wpms-form-table" action="" method="post">
        <div id="meta-bulk-actions" style="display:none;">
            <div class="m-tb-20">
                <h3 class="wpms-top-h3"><?php esc_html_e('Apply to', 'wp-meta-seo') ?></h3>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="mbulk_copy link-apply-action wpms-checkbox" value="all">
                        <?php esc_html_e('All Links', 'wp-meta-seo') ?>
                    </label>
                </p>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="mbulk_copy link-apply-action wpms-checkbox" value="selected" checked="checked">
                        <?php esc_html_e('Only link selection', 'wp-meta-seo') ?>
                    </label>
                </p>
            </div>

            <div class="m-tb-20">
                <h3 class="wpms-top-h3"><?php esc_html_e('Apply the following actions', 'wp-meta-seo') ?></h3>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="wpms-bulk-action link-bulk-action wpms-checkbox" value="follow">
                        <?php esc_html_e('Follow', 'wp-meta-seo') ?>
                    </label>
                </p>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="wpms-bulk-action link-bulk-action wpms-checkbox" value="nofollow">
                        <?php esc_html_e('UnFollow', 'wp-meta-seo') ?>
                    </label>
                </p>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="wpms-bulk-action link-bulk-action wpms-checkbox" value="copy_title">
                        <?php esc_html_e('Copy link text as link title', 'wp-meta-seo') ?>
                    </label>
                </p>
            </div>

            <button type="button"
                    class="ju-button orange-button btn_bulk_link wpms-small-btn wpms_left"><?php esc_html_e('Apply now', 'wp-meta-seo') ?></button>
            <span class="spinner wpms-spinner spinner_apply_follow wpms_left"></span>
        </div>

        <?php
        echo '<h1 class="wpms-top-h1">' . esc_html__('Link Editor', 'wp-meta-seo') . '</h1>';
        $metaseo_list_table->searchBox1();
        $metaseo_list_table->display();
        ?>
    </form>

    <?php
    $w               = '100%';
    $text            = esc_html__('Bring your WordPress website SEO to the next level with the PRO Addon: Email Report,
     Google Search Console Connect, Automatic Redirect, Advanced Sitemaps and more!', 'wp-meta-seo');
    $class_btn_close = 'close_linkmeta';
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/notification.php');
    ?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.metaseo_link_title').bind('input propertychange', function () {
            $(this).closest('tr').find('.wpms_update_link').show();
        });

        // remove link in source 404
        $('.wpms_remove_link').on('click', function () {
            var link_id = $(this).data('link_id');
            wpmsRemoveLink(link_id);
        });

        // update link title
        $('.wpms_update_link').on('click', function () {
            saveMetaLinkChanges(this);
        });

        $('.wpms_change_follow').on('click', function () {
            wpmsChangeFollow(this);
        });

        $('.btn_bulk_link').on('click', function () {
            wpmsLinkDoAction(this);
        });

        // index link
        $('.wpms_scan_link').on('click', function () {
            var $this = $(this);
            wpmsScanLink($this);
        });
    });

</script>