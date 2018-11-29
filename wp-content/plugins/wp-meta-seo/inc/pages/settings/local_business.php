<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (is_plugin_active(WPMSEO_ADDON_FILENAME)) {
    // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in 'wp-meta-seo-addon/inc/page/local_business.php' file
    echo $local_business_html;
}
