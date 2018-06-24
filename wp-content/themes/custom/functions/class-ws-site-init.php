<?php


class WS_Site {

    public function __construct() {

        add_action('init', array( $this, 'register_image_sizing') );
        add_action('init', array( $this, 'register_theme_support') );
        add_action('init', array( $this, 'register_post_types_and_taxonomies' ) );

        add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

        add_filter('show_admin_bar', '__return_false');

        new WS_CDN_Url();

    }


    public function register_post_types_and_taxonomies() {

        WS_Custom_Category::register();

        WS_Custom_Post::register();

    }

    public function register_image_sizing() {
        if ( function_exists( 'add_image_size' ) ) {

            add_image_size('social_card', 1200, 630, array( 'x_crop_position' => 'center', 'y_crop_position' => 'center'));
            add_image_size('acf_preview', 300, 300, false);
            add_image_size('page_hero', 1440, 660, false);

        }
    }

    public function register_theme_support() {
        if ( function_exists( 'add_theme_support' ) ) {

            add_theme_support( 'menus' );

        }
    }


    public function enqueue_scripts_and_styles() {
        if ( function_exists( 'get_template_directory_uri' ) && function_exists( 'wp_enqueue_style' ) && function_exists( 'wp_enqueue_script' ) ) {

            $main_css = '/bundles/bundle.css';
            $main_js = '/bundles/bundle.js';

            $compiled_resources_dir = get_template_directory();
            $compiled_resources_uri = get_template_directory_uri();

            $main_css_ver = filemtime( $compiled_resources_dir . $main_css ); // version suffixes for cache-busting.
            $main_js_ver = filemtime( $compiled_resources_dir . $main_css ); // version suffixes for cache-busting.

            wp_enqueue_style('main-css', $compiled_resources_uri . $main_css, array(), null);
            wp_enqueue_script('jquery');
            wp_enqueue_script('main-js', $compiled_resources_uri . $main_js, array('jquery'), $main_js_ver);

        }
    }

}

?>
