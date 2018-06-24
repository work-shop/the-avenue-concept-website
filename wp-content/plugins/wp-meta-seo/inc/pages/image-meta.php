<?php
if (!class_exists('MetaSeoImageListTable')) {
    require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-image-list-table.php');
}

wp_enqueue_style('m-style-qtip');
wp_enqueue_script('jquery-qtip');
wp_enqueue_style('wpms-myqtip');
$metaseo_list_table = new MetaSeoImageListTable();
$metaseo_list_table->processAction();
$metaseo_list_table->prepare_items();

if (!empty($_REQUEST['_wp_http_referer'])) {
    wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
    exit;
}
?>

<div class="wrap seo_extended_table_page">
    <div id="icon-edit-pages" class="icon32 icon32-posts-page"></div>
    <?php echo '<h1>' . __('Image Meta', 'wp-meta-seo') . '</h1>'; ?>
    <form id="wp-seo-meta-form" action="" method="post">
        <?php $metaseo_list_table->searchBox1(); ?>
        <?php $metaseo_list_table->display(); ?>
    </form>

</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        //Scan all posts to find a group of images in their content
        metaSeoScanImages();
        autosize(document.querySelectorAll('.metaseo-img-meta'));

        jQuery('.image_scan_meta').qtip({
            content: {
                attr: 'alt'
            },
            position: {
                my: 'bottom center',
                at: 'top center'
            },
            style: {
                tip: {
                    corner: true
                },
                classes: 'wpms-widgets-qtip_show_arow'
            },
            show: 'hover',
            hide: {
                fixed: true,
                delay: 10
            }
        });
    });

</script>