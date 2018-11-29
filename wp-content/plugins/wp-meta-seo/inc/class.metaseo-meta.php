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
     * WPMS meta prefix
     *
     * @var string
     */
    public static $meta_prefix = '_metaseo_meta';
    /**
     * WPMS form prefix
     *
     * @var string
     */
    public static $form_prefix = 'metaseo_wpmseo_';
    /**
     * Meta description max length
     *
     * @var integer
     */
    public static $meta_length = 320;
    /**
     * Meta title max length
     *
     * @var integer
     */
    public static $meta_title_length = 69;
    /**
     * Meta keywords max length
     *
     * @var integer
     */
    public static $meta_keywords_length = 256;
    /**
     * Meta length reason
     *
     * @var string
     */
    public static $meta_length_reason = '';
    /**
     * Meta fields
     *
     * @var array
     */
    public static $meta_fields = array(
        'general'  => array(
            'snippetpreview' => array(
                'type'     => 'snippetpreview',
                'title'    => '',
                'help'     => '',
                'classrow' => 'wpms_width_100'
            ),
            'title'          => array(
                'type'          => 'text',
                'title'         => '',
                'default_value' => '',
                'description'   => '',
                'help'          => '',
                'rows'          => 2,
                'class'         => 'wpms_width_100 has-length wpms-large-input',
                'classrow'      => 'wpms_width_100'
            ),
            'desc'           => array(
                'type'          => 'textarea',
                'title'         => '',
                'default_value' => '',
                'class'         => 'desc wpms_width_100 has-length',
                'rows'          => 3,
                'description'   => '',
                'help'          => '',
                'classrow'      => 'wpms_width_100'
            ),
            'keywords'       => array(
                'type'          => 'text',
                'title'         => '',
                'default_value' => '',
                'description'   => '',
                'help'          => '',
                'rows'          => 2,
                'class'         => 'wpms_width_100 has-length wpms-large-input',
                'classrow'      => 'wpms_width_100'
            ),
            'metaseo_chart'  => array(
                'type'          => 'metaseo_chart',
                'title'         => '',
                'default_value' => '',
                'class'         => 'metaseo_chart',
                'rows'          => 2,
                'description'   => '',
                'help'          => ''
            )
        ),
        'social'   => array(
            'facebook' => array(
                'opengraph-title' => array(
                    'type'          => 'text',
                    'title'         => '',
                    'default_value' => '',
                    'description'   => '',
                    'help'          => '',
                    'class'         => 'wpms_width_100 wpms-large-input',
                    'classrow'      => 'wpms_width_50'
                ),
                'opengraph-desc'  => array(
                    'type'          => 'text',
                    'title'         => '',
                    'default_value' => '',
                    'class'         => 'wpms_width_100 wpms-large-input',
                    'rows'          => 3,
                    'description'   => '',
                    'help'          => '',
                    'classrow'      => 'wpms_width_50'
                ),
                'opengraph-image' => array(
                    'type'          => 'upload',
                    'title'         => '',
                    'default_value' => '',
                    'class'         => 'wpms-large-input',
                    'description'   => '',
                    'help'          => '',
                    'classrow'      => 'wpms_width_100'
                )
            ),
            'twitter'  => array(
                'twitter-title' => array(
                    'type'          => 'text',
                    'title'         => '',
                    'default_value' => '',
                    'description'   => '',
                    'help'          => '',
                    'class'         => 'wpms_width_100 wpms-large-input',
                    'classrow'      => 'wpms_width_50'
                ),
                'twitter-desc'  => array(
                    'type'          => 'text',
                    'title'         => '',
                    'default_value' => '',
                    'class'         => 'wpms_width_100 wpms-large-input',
                    'rows'          => 3,
                    'description'   => '',
                    'help'          => '',
                    'classrow'      => 'wpms_width_50'
                ),
                'twitter-image' => array(
                    'type'          => 'upload',
                    'title'         => '',
                    'default_value' => '',
                    'class'         => 'wpms-large-input',
                    'description'   => '',
                    'help'          => '',
                    'classrow'      => 'wpms_width_100'
                )
            )

        ),
        'non_form' => array(
            'linkdex' => array(
                'type'          => null,
                'default_value' => '0'
            ),
        ),
    );

    /**
     * Default
     *
     * @var array
     */
    public static $defaults = array();

    /**
     * Init
     *
     * @return void
     */
    public static function init()
    {
        add_filter('update_post_metadata', array(__CLASS__, 'remove_meta_if_default'), 10, 5);
        add_filter('add_post_metadata', array(__CLASS__, 'dont_save_meta_if_default'), 10, 4);
    }

    /**
     * Retrieve the meta box form field definitions for the given tab and post type.
     *
     * @param string $tab       Tab for which to retrieve the field definitions.
     * @param string $post_type Post type of the current post.
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
                    $hr                                        = esc_url(admin_url('admin.php?page=wpmseo_titles#top#post_types'));
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
     * @param string  $key    Internal key of the value to get (without prefix).
     * @param integer $postid Post ID of the post to get the value for.
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
     * @param string  $key        The internal key of the meta value to change (without prefix).
     * @param mixed   $meta_value The value to set the meta to.
     * @param integer $post_id    The ID of the post to change the meta for.
     *
     * @return boolean   whether the value was changed
     */
    public static function setValue($key, $meta_value, $post_id)
    {
        return update_post_meta($post_id, self::$meta_prefix . $key, $meta_value);
    }
}
