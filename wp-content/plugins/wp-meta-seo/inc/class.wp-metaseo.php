<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpMetaSeo
 * Main plugin functions here
 */
class WpMetaSeo
{
    /**
     * @var bool
     */
    private static $initiated = false;

    /**
     * Init
     */
    public static function init()
    {
        ob_start();
        if (!self::$initiated) {
            self::initHooks();
        }
    }

    /**
     * Initializes WordPress hooks
     */
    private static function initHooks()
    {
        self::$initiated = true;
    }

    /**
     * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
     * @param string $wp default wordpress version
     * @param string $php default php version
     */
    public static function pluginActivation($wp = '4.0', $php = '5.3.0')
    {
        global $wp_version;
        if (version_compare(PHP_VERSION, $php, '<')) {
            $flag = 'PHP';
        } elseif (version_compare($wp_version, $wp, '<')) {
            $flag = 'WordPress';
        } else {
            //Set two param as flags that determine whether show import meta data
            // from other SEO plugin button or not to 0
            update_option('_aio_import_notice_flag', 0);
            update_option('_yoast_import_notice_flag', 0);
            update_option('plugin_to_sync_with', 0);
            self::installDb();
            return;
        }

        $version = 'PHP' == $flag ? $php : $wp;
        deactivate_plugins(basename(__FILE__));
        wp_die(
            '<p>The <strong>WP Meta SEO</strong>
 plugin requires ' . $flag . '  version ' . $version . ' or greater.</p>',
            'Plugin Activation Error',
            array(
                'response' => 200,
                'back_link' => true
            )
        );

        if (!class_exists('DOMDocument')) {
            deactivate_plugins(basename(__FILE__));
            wp_die(
                '<p>To active WP Meta SEO plugin , please install  “dom” PHP extension </p>',
                'Plugin Activation Error',
                array(
                    'response' => 200,
                    'back_link' => true
                )
            );
        }
    }

    /* create metaseo_images table */
    public static function installDb()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'metaseo_images';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `post_id` int(11) NOT NULL,
			  `posts_optimized_id` text COLLATE utf8_unicode_ci NOT NULL,
			  `posts_need_to_optimize_id` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
			  `posts_prepare_to_optimize` text COLLATE utf8_unicode_ci NOT NULL,
			  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `alt_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `legend` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `description` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
			  `link` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }
}
