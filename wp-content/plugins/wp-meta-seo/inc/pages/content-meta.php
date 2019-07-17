<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('MetaSeoContentListTable')) {
    require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-content-list-table.php');
}

add_thickbox();
$metaseo_list_table = new MetaSeoContentListTable();
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
                <h3 class="wpms-top-h3"><?php esc_html_e('Apply bulk action to', 'wp-meta-seo') ?></h3>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="mbulk_copy wpms-checkbox" value="all">
                        <?php esc_html_e('All Posts', 'wp-meta-seo') ?>
                    </label>
                </p>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="mbulk_copy wpms-checkbox" value="only-selection" checked="checked">
                        <?php esc_html_e('Only post selection', 'wp-meta-seo') ?>
                    </label>
                </p>
            </div>

            <div class="m-tb-20">
                <h3 class="wpms-top-h3"><?php esc_html_e('Action', 'wp-meta-seo') ?></h3>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="wpms-bulk-action wpms-checkbox" value="post-copy-title">
                        <?php esc_html_e('Copy Title as Meta Title', 'wp-meta-seo') ?>
                    </label>
                </p>
                <p>
                    <label class="wpms-text-action">
                        <input type="checkbox" class="wpms-bulk-action wpms-checkbox" value="post-copy-desc">
                        <?php esc_html_e('Copy Title as Meta Description', 'wp-meta-seo') ?>
                    </label>
                </p>
            </div>

            <button type="button" name="do_copy" data-action="bulk_post_copy"
                                 class="ju-button orange-button btn_do_copy post_do_copy wpms-small-btn wpms_left"><?php esc_html_e('Apply now', 'wp-meta-seo') ?></button>
            <span class="spinner wpms-spinner wpms-spinner-copy wpms_left"></span>
            <label class="bulk-msg"><?php esc_html_e('Done! You may ', 'wp-meta-seo') ?><a href="<?php echo esc_url(admin_url('admin.php?page=metaseo_content_meta')) ?>"><?php esc_html_e('close the window and refresh the page...', 'wp-meta-seo') ?></a></label>
        </div>
        <?php
        echo '<h1 class="wpms-top-h1">' . esc_html__('Content Meta', 'wp-meta-seo') . '</h1>';
        $metaseo_list_table->searchBox(esc_html__('Search Posts', 'wp-meta-seo'), 'wpms_content');
        $metaseo_list_table->display();
        ?>
    </form>

</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.metaseo_post_follow').on('click', function () {
            var page_id = $(this).val();
            if ($(this).is(':checked')) {
                var follow = 'follow';
            } else {
                follow = 'nofollow';
            }
            metaseo_update_pagefollow(page_id, follow);
        });

        $('.metaseo_post_index').on('click', function () {
            var page_id = $(this).val();
            if ($(this).is(':checked')) {
                var index = 'index';
            } else {
                index = 'noindex';
            }
            metaseo_update_pageindex(page_id, index);
        });

        $('.metaseo-metatitle').each(function () {
            metaseo_titlelength(this.id, false);
            metaseo_updateTitle(this.id, false);
        });

        $('.metaseo-metakeywords').each(function () {
            metaseo_keywordlength(this.id);
            metaseo_updatekeywords(this.id, false);
        });

        $('.metaseo-metadesc').each(function () {
            metaseo_desclength(this.id);
            metaseo_updateDesc(this.id, false);
        });

        $('.metaseo-metatitle').bind('input propertychange', function () {
            metaseo_titlelength(this.id, true);
        });

        $('.metaseo-metatitle').blur(function () {
            metaseo_updateTitle(this.id, true);
        });

        $('.metaseo-metakeywords').bind('input propertychange', function () {
            metaseo_keywordlength(this.id);
        });

        $('.metaseo-metakeywords').blur(function () {
            metaseo_updatekeywords(this.id, true);
        });

        $('.metaseo-metadesc').bind('input propertychange', function () {
            metaseo_desclength(this.id);
        });

        $('.metaseo-metadesc').blur(function () {
            metaseo_updateDesc(this.id, true);
        });

        $('.metaseo-metadesc, .metaseo-metatitle').bind('input propertychange', function () {
            var idNumber = this.id.substr(this.id.lastIndexOf('-') + 1);
            if (this.id === 'metaseo-metatitle-' + idNumber) {
                if (!$(this).val()) {
                    var post_title = $('#post-title-' + idNumber).text();
                    $('#snippet_title' + idNumber).text(post_title);
                }
            }

        });
    });
</script>