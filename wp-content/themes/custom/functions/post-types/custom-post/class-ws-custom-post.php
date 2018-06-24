<?php

class WS_Custom_Post extends WS_Custom_Post_Type {

    public static $slug = 'custom';

    public static $singular_name = 'Custom Post';

    public static $plural_name = 'Custom Posts';

    public static $post_options = array(
        'menu_icon'                 => 'dashicons-products',
        'hierarchical'              => false,
        'has_archive'               => true,
        'menu_position'             => 4,
        'supports'                  => array(
                                        'title',
                                        'revisions'
                                    ),
        'rewrite'                   => array(
                                        'slug' => 'custom-categories',
                                        'with_front' => false,
                                        'feeds' => true,
                                        'pages' => true
                                    ),
        'taxonomies'                => array( '' )

    );

    public static $query_options = array(

    );


    /**
     * ==== Instance Members and Methods ====
     */

    public function __construct( $id ) {

        $this->id = $id;

    }

    public function validate() {

    }

    public function create() {

    }

}

?>
