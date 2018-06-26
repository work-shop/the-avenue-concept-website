<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WPMSEOMeta
 * This class implements defaults and value validation for all WPMSEO Post Meta values.
 * Based on some work of Yoast SEO plugin
 */
class WPMSEOMeta
{
    /**
     * @var string
     */
    public static $meta_prefix = '_metaseo_meta';
    /**
     * @var string
     */
    public static $form_prefix = 'metaseo_wpmseo_';
    /**
     * @var int
     */
    public static $meta_length = 320;
    /**
     * @var int
     */
    public static $meta_title_length = 69;
    /**
     * @var int
     */
    public static $meta_keywords_length = 256;
    /**
     * @var string
     */
    public static $meta_length_reason = '';
    /**
     * @var array
     */
    public static $meta_fields = array(
        'general' => array(
            'snippetpreview' => array(
                'type' => 'snippetpreview',
                'title' => '',
                'help' => '',
            ),
            'title' => array(
                'type' => 'textarea',
                'title' => '',
                'default_value' => '',
                'description' => '',
                'help' => '',
                'rows' => 2
            ),
            'desc' => array(
                'type' => 'textarea',
                'title' => '',
                'default_value' => '',
                'class' => 'desc',
                'rows' => 3,
                'description' => '',
                'help' => '',
            ),
            'keywords' => array(
                'type' => 'textarea',
                'title' => '',
                'default_value' => '',
                'description' => '',
                'help' => '',
                'rows' => 2
            ),
            'metaseo_chart' => array(
                'type' => 'metaseo_chart',
                'title' => '',
                'default_value' => '',
                'class' => 'metaseo_chart',
                'rows' => 2,
                'description' => '',
                'help' => '',
            )
        ),
        'social' => array(
            'opengraph-title' => array(
                'type' => 'text',
                'title' => '',
                'default_value' => '',
                'description' => '',
                'help' => '',
            ),
            'opengraph-desc' => array(
                'type' => 'textarea',
                'title' => '',
                'default_value' => '',
                'class' => 'desc',
                'rows' => 3,
                'description' => '',
                'help' => '',
            ),
            'opengraph-image' => array(
                'type' => 'upload',
                'title' => '',
                'default_value' => '',
                'class' => 'desc',
                'description' => '',
                'help' => '',
            ),
            'twitter-title' => array(
                'type' => 'text',
                'title' => '',
                'default_value' => '',
                'description' => '',
                'help' => '',
            ),
            'twitter-desc' => array(
                'type' => 'textarea',
                'title' => '',
                'default_value' => '',
                'class' => 'desc',
                'rows' => 3,
                'description' => '',
                'help' => '',
            ),
            'twitter-image' => array(
                'type' => 'upload',
                'title' => '',
                'default_value' => '',
                'class' => 'desc',
                'description' => '',
                'help' => '',
            ),
        ),
        'non_form' => array(
            'linkdex' => array(
                'type' => null,
                'default_value' => '0',
            ),
        ),
    );
    /**
     * @var array
     */
    public static $fields_index = array();
    /**
     * @var array
     */
    public static $defaults = array();

    /**
     * Init
     */
    public static function init()
    {
        add_filter('update_post_metadata', array(__CLASS__, 'remove_meta_if_default'), 10, 5);
        add_filter('add_post_metadata', array(__CLASS__, 'dont_save_meta_if_default'), 10, 4);
    }

    /**
     * Retrieve the meta box form field definitions for the given tab and post type.
     *
     * @static
     *
     * @param  string $tab Tab for which to retrieve the field definitions.
     * @param  string $post_type Post type of the current post.
     *
     * @return array             Array containing the meta box field definitions
     */
    public static function getMetaFieldDefs($tab, $post_type = 'post')
    {
        if (!isset(self::$meta_fields[$tab])) {
            return array();
        }

        $field_defs = self::$meta_fields[$tab];
        switch ($tab) {
            case 'non-form':
                $field_defs = array();
                break;


            case 'general':
                $options = get_option('wpmseo_titles');
                if ($options['usemetakeywords'] === true) {
                    $hr = esc_url(admin_url('admin.php?page=wpmseo_titles#top#post_types'));
                    $field_defs['metakeywords']['description'] = sprintf(
                        $field_defs['metakeywords']['description'],
                        '<a target="_blank" href="' . $hr . '">',
                        '</a>'
                    );
                } else {
                    unset($field_defs['metakeywords']);
                }

                $field_defs = apply_filters('wpmseo_metabox_entries', $field_defs);
                break;


            case 'advanced':
                break;
        }

        return apply_filters('wpmseo_metabox_entries_' . $tab, $field_defs, $post_type);
    }

    /**
     * Get a custom post meta value
     * Returns the default value if the meta value has not been set
     *
     * @internal Unfortunately there isn't a filter available to hook into before returning the results
     * for get_post_meta(), get_post_custom() and the likes. That would have been the preferred solution.
     *
     * @static
     *
     * @param  string $key Internal key of the value to get (without prefix).
     * @param  int $postid Post ID of the post to get the value for.
     *
     * @return string         All 'normal' values returned from get_post_meta() are strings.
     *                        Objects and arrays are possible, but not used by this plugin
     *                        and therefore discarted (except when the special 'serialized' field def
     *                        value is set to true - only used by add-on plugins for now).
     *                        Will return the default value if no value was found..
     *                        Will return empty string if no default was found (not one of our keys) or
     *                        if the post does not exist.
     */
    public static function getValue($key, $postid = 0)
    {
        global $post;

        $postid = absint($postid);
        if ($postid === 0) {
            if ((isset($post) && is_object($post)) && (isset($post->post_status)
                    && $post->post_status !== 'auto-draft')) {
                $postid = $post->ID;
            } else {
                return '';
            }
        }

        $custom = get_post_custom($postid);

        if (isset($custom[self::$meta_prefix . $key][0])) {
            $unserialized = maybe_unserialize($custom[self::$meta_prefix . $key][0]);
            if ($custom[self::$meta_prefix . $key][0] === $unserialized) {
                return $custom[self::$meta_prefix . $key][0];
            } else {
                return '';
            }
        }

        if (isset(self::$defaults[self::$meta_prefix . $key])) {
            return self::$defaults[self::$meta_prefix . $key];
        } else {
            return '';
        }
    }

    /**
     * Update a meta value for a post
     *
     * @static
     *
     * @param  string $key The internal key of the meta value to change (without prefix).
     * @param  mixed $meta_value The value to set the meta to.
     * @param  int $post_id The ID of the post to change the meta for.
     *
     * @return bool   whether the value was changed
     */
    public static function setValue($key, $meta_value, $post_id)
    {
        return update_post_meta($post_id, self::$meta_prefix . $key, $meta_value);
    }
}
