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


    new WS_Site();
    new WS_Site_Admin();


?>
