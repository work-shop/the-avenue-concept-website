<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('MetaSeoBrokenLinkTable')) {
    require_once(WPMETASEO_PLUGIN_DIR . '/inc/class.metaseo-broken-link-table.php');
}

wp_enqueue_script('wplink');
wp_enqueue_style('editor-buttons');
wp_enqueue_style('metaseo-google-icon');
wp_enqueue_style('m-style-qtip');
wp_enqueue_script('jquery-qtip');

$metaseo_list_table = new MetaSeoBrokenLinkTable();
$metaseo_list_table->processAction();
$metaseo_list_table->prepare_items();
$a = json_encode($metaseo_list_table->items);

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
if (!empty($_REQUEST['_wp_http_referer'])) {
    wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])));
    exit;
}
?>

    <div class="wrap broken_link_table seo_extended_table_page">
        <div id="icon-edit-pages" class="icon32 icon32-posts-page"></div>
        <form id="wp-seo-meta-form" class="wpms-form-table" action="" method="post">
            <?php
            echo '<h1 class="wpms-top-h1">' . esc_html__('404 & Redirects', 'wp-meta-seo') . '</h1>';
            $metaseo_list_table->searchBox1();
            $metaseo_list_table->brokenFilter('sl_broken[]');
            $metaseo_list_table->display();
            ?>
        </form>

        <?php
        $w               = '100%';
        $text            = esc_html__('Bring your WordPress website SEO to the next level with the PRO Addon: Automatic
         redirect based on rules, automatic 404 error index, redirect with link manager,
          Email notification and more!', 'wp-meta-seo');
        $class_btn_close = 'close_broken_link';
        require_once(WPMETASEO_PLUGIN_DIR . 'inc/pages/notification.php');
        ?>
    </div>
<?php
wp_enqueue_script('wpms-broken-link');
?>