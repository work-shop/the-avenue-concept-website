<?php
if (!class_exists('MetaSeoContentListTable')) {
    require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-content-list-table.php');
}

$metaseo_list_table = new MetaSeoContentListTable();
$metaseo_list_table->processAction();
$metaseo_list_table->prepare_items();

if (!empty($_REQUEST['_wp_http_referer'])) {
    wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
    exit;
}
?>

<div class="wrap seo_extended_table_page">
    <div id="icon-edit-pages" class="icon32 icon32-posts-page"></div>
    <?php echo '<h1>' . __('Content Meta', 'wp-meta-seo') . '</h1>'; ?>
    <form id="wp-seo-meta-form" action="" method="post">
        <?php
        $metaseo_list_table->search_box(__('Search Posts', 'wp-meta-seo'), 'wpms_content');
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