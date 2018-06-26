<?php
if (!class_exists('MetaSeoLinkListTable')) {
    require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-link-list-table.php');
}

$metaseo_list_table = new MetaSeoLinkListTable();
$metaseo_list_table->processAction();
$metaseo_list_table->prepare_items();

if (!empty($_REQUEST['_wp_http_referer'])) {
    wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
    exit;
}
?>

<div class="wrap seo_extended_table_page">
    <div id="icon-edit-pages" class="icon32 icon32-posts-page"></div>

    <?php echo '<h1>' . __('Link editor', 'wp-meta-seo') . '</h1>'; ?>

    <form id="wp-seo-meta-form" action="" method="post">
        <?php $metaseo_list_table->searchBox1(); ?>
        <?php $metaseo_list_table->display(); ?>
    </form>

    <?php
    $w = '100%';
    $text = __('Bring your WordPress website SEO to the next level with the PRO Addon: Email Report,
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

        $('.btn_apply_follow').on('click', function () {
            wpmsUpdateFollow(this);
        });

        // index link
        $('.wpms_scan_link').on('click', function () {
            var $this = $(this);
            wpmsScanLink($this);
        });
    });

</script>