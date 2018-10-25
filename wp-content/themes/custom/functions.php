<?php

/** Theme-specific global constants for NAM */
define( '__ROOT__', dirname( __FILE__ ) );

require_once( __ROOT__ . '/functions/class-ws-abstract-taxonomy.php' );
require_once( __ROOT__ . '/functions/class-ws-abstract-custom-post-type.php' );

require_once( __ROOT__ . '/functions/library/class-ws-cdn-url.php');

require_once( __ROOT__ . '/functions/taxonomies/custom-category/class-ws-custom-category.php');

require_once( __ROOT__ . '/functions/post-types/custom-post/class-ws-custom-post.php');

require_once( __ROOT__ . '/functions/class-ws-site-admin.php' );
require_once( __ROOT__ . '/functions/class-ws-site-init.php' );

require_once( __ROOT__ . '/functions/library/class-ws-flexible-content.php' );
require_once( __ROOT__ . '/functions/library/class-helpers.php' );

new WS_Site();
new WS_Site_Admin();

add_filter( 'redirect_canonical', function( $redirect_url ) {

    $url = explode( '?' . $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_URI'] );
    $url = $url[0];
    //var_dump($url);

    if( preg_match('/^.*?\/artworks\/[^\/]+\/?$/m', $url) == 1 ){
        //die;
        return false;
    } else{
        return $redirect_url;
    }
});

    //if this is an artwork single, hijack the template and get it to use the artwork-single.php template
add_filter( 'template_include', function( $template ) {
        //var_dump($template);

    $url = explode( '?' . $_SERVER['QUERY_STRING'], $_SERVER['REQUEST_URI'] );
    $url = $url[0];
    //var_dump($url);

    if( preg_match('/^.*?\/artworks\/[^\/]+\/?$/m', $url) == 1 ){
        $new_template = get_template_directory() . '/artwork-single.php';
        $template = $new_template;
    }

    return $template;
});

// if ( ! function_exists('rid_remove_jqmigrate_console_log') ) {
//     function rid_remove_jqmigrate_console_log( $scripts ) {
//         if ( ! empty( $scripts->registered['jquery'] ) ) {
//             $scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
//         }
//     }
//     add_action( 'wp_default_scripts', 'rid_remove_jqmigrate_console_log' );
// }

// function check_for_rest( $content ){
//     if ( REST_REQUEST ){
//         return $content;
//         //return $content;
//     }else {
//             return $content;

//     }
// }

// add_filter('the_content','check_for_rest');

// var_dump($wp_filter["the_content"]);


// remove_filter( 'the_content', 'wptexturize'        );
// remove_filter( 'the_content', 'convert_smilies'    );
// remove_filter( 'the_content', 'convert_chars'      );
// remove_filter( 'the_content', 'wpautop'            );
// remove_filter( 'the_content', 'shortcode_unautop'  );
// remove_filter( 'the_content', 'prepend_attachment' );

register_rest_route( 'zoho', '/zoho', array(
    'methods'         => WP_REST_Server::ALLMETHODS,
) );


?>
