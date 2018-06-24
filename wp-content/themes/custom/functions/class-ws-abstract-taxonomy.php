<?php

abstract class WS_Taxonomy {

    protected static function register($slug, $singular_name, $plural_name, $post_types = array(), $public = true) {
        if ( function_exists('register_taxonomy') ) {
            register_taxonomy(
                $slug,
                $post_types,
                array(
                    'labels' => array(
                        'name'                              => $plural_name,
                        'singular_name'                     => $singular_name,
                        'menu_name'                         => $plural_name,
                        'all_items'                         => 'All ' . $plural_name,
                        'edit_item'                         => 'Edit ' . $singular_name,
                        'view_item'                         => 'View ' . $singular_name,
                        'update_item'                       => 'Update ' . $singular_name,
                        'add_new_item'                      => 'Add New ' . $singular_name,
                        'new_item_name'                     => 'New ' . $singular_name . ' Name',
                        'parent_item'                       => 'Parent '.$singular_name,
                        'parent_item_colon'                 => 'Parent ' . $singular_name . ':',
                        'search_items'                      => 'Search ' . $plural_name,
                        'popular_items'                     => 'Frequently used ' . $plural_name,
                        'separate_items_with_commas'        => 'Separate ' . $plural_name . ' with commas',
                        'add_or_remove_items'               => 'Add or Remove ' . $plural_name,
                        'choose_from_most_used'             => 'Choose from the most frequently used ' . $plural_name,
                        'not_found'                         => 'No ' . $plural_name . ' found.'
                    ),
                    'public' => $public,
                    'show_in_rest' => true,
                    'show_tag_cloud' => false,
                    'show_in_quick_edit' => false,
                    'show_admin_column' => true,
                    'hierarchical' => true,
                    'capabilities' => array(
                        'manage_terms'                      => 'manage_categories',
                        'edit_terms'                        => 'manage_categories',
                        'delete_terms'                      => 'manage_categories',
                        'assign_terms'                      => 'edit_posts'
                    ),
                    'sort' => true
                )
            );
        }
    }

}

?>
