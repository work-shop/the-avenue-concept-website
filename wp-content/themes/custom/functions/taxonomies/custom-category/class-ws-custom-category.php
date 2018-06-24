<?php

class WS_Custom_Category extends WS_Taxonomy {

    public static $slug = 'custom-categories';

    public static $singular_name = 'Custom Category';

    public static $plural_name = 'Custom Categories';

    public static $registered_post_types = array( 'custom' );

    public static function register() { parent::register( WS_Custom_Category::$slug, WS_Custom_Category::$singular_name, WS_Custom_Category::$plural_name, WS_Custom_Category::$registered_post_types ); }

}

?>
