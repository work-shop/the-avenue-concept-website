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


?>
