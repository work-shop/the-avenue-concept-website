<?php

abstract class WS_Custom_Post_Type {

    /**
     * A simple helper utility method to check a map for a specific key,
     * and return a default of it's not present.
     */
    private static function default_for_key( $key, $options, $default ) {
        return array_key_exists( $key, $options ) ? $options[$key] : $default;
    }

    /**
     * The register static method is used to register the instance post type
     * in WordPress
     */
    public static function register( ) {

        if ( function_exists( 'register_post_type' ) ) {
            register_post_type(
                static::$slug,
                array(
                    'labels' => array(
                        'name'                          => static::$plural_name,
                        'singular_name'                 => static::$singular_name,
                        'add_new'                       => 'Add New',
                        'add_new_item'                  => 'Add New ' . static::$singular_name,
                        'edit_item'                     => 'Edit ' . static::$singular_name,
                        'new_item'                      => 'New ' . static::$singular_name,
                        'view_item'                     => 'View ' . static::$singular_name,
                        'view_items'                    => 'View ' . static::$plural_name,
                        'search_items'                  => 'Search ' . static::$plural_name,
                        'not_found'                     => 'No ' . static::$plural_name . ' found',
                        'not_found_in_trash'            => 'No ' . static::$plural_name . ' found in the trash',
                        'parent_item_colon'             => 'Parent ' . static::$singular_name. ':',
                        'all_items'                     => 'All ' . static::$plural_name,
                        'archives'                      => static::$singular_name . ' List',
                        'attributes'                    => static::$singular_name . ' Attributes',
                        'insert_into_item'              => 'Insert into ' . static::$singular_name,
                        'uploaded_to_this_item'         => 'Uploaded to this ' . static::$singular_name,
                        'featured_image'                => static::$singular_name . ' Featured Image',
                        'set_featured_image'            => 'Set Featured Image',
                        'remove_featured_image'         => 'Remove ' . static::$singular_name . ' Image',
                        'use_featured_image'            => 'Use as ' . static::$singular_name . ' Image',
                        'menu_name'                     => static::$plural_name // Default
                        /*
                        'filter_items_list'             => '',
                        'items_list_navigation'         => '',
                        'items_list'                    => '',
                        'name_admin_bar'                => ''
                         */

                    ),
                    'public' => true,
                    'menu_position' => WS_Custom_Post_Type::default_for_key( 'menu_position', static::$post_options, 5), // Before Posts Divider
                    'menu_icon' => WS_Custom_Post_Type::default_for_key( 'menu_icon',  static::$post_options, 'dashicons-posts'),
                    // 'capabilities_type' => array(str_replace(' ', '_', strtolower(  static::$singular_name ) ), str_replace(' ', '_', strtolower(  static::$plural_name )) ),
                    'hierarchical' => WS_Custom_Post_Type::default_for_key( 'hierarchical',  static::$post_options, false),
                    'supports' => WS_Custom_Post_Type::default_for_key( 'supports', static::$post_options, array()),
                    'taxonomies' => array_merge( WS_Custom_Post_Type::default_for_key( 'taxonomy',  static::$post_options, array()), array('groups') ),
                    'has_archive' => WS_Custom_Post_Type::default_for_key( 'has_archive',  static::$post_options, true),
                    'rewrite' => WS_Custom_Post_Type::default_for_key( 'rewrite',  static::$post_options, array() ),
                    'query_var' => true,
                    'can_export' => true,
                    'show_in_rest' => true
                )

            );

        }

    }

    /**
     * This static method retrieves a set of posts for the child's post-type.
     */
    public static function get_posts( $options = array() ) {
        if ( function_exists('get_posts') ) {

            $called_class = get_called_class();
            $opts = array_merge( static::$query_options, $options, array( 'post_type' => static::$slug ) );

            foreach ( ($posts = get_posts( $opts )) as $key => $value ) {
                $posts[ $key ] = new $called_class( $value );
            }

            return $posts;

        } else {

            return array();

        }
    }


    /**
     * ==== Instance Members and Methods ====
     */

    protected $post;

    public function __construct( $post ) {
        $this->post = $post;
    }

    public abstract function validate();

    public abstract function create();

    // public abstract function render_card();
    //
    // public abstract function render_page();

}




?>
