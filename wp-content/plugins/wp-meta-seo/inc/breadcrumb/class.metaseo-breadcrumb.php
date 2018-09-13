<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class MetaSeoBreadcrumb
 */
class MetaSeoBreadcrumb
{
    /**
     * Breadcrumbs array
     *
     * @var array
     */
    public $breadcrumbs = array();
    /**
     * Breadcrumb settings
     *
     * @var array
     */
    public $breadcrumb_settings = array();
    /**
     * Template no anchor
     *
     * @var string
     */
    public $template_no_anchor;

    /**
     * MetaSeoBreadcrumb constructor.
     */
    public function __construct()
    {
        $home_title = get_the_title(get_option('page_on_front'));
        if (empty($home_title)) {
            $home_title = get_bloginfo('title');
        }
        $this->breadcrumb_settings = array(
            'separator'         => ' &gt; ',
            'include_home'      => 1,
            'home_text_default' => 0,
            'home_text'         => $home_title,
            'clickable'         => 1,
            'apost_post_root'   => get_option('page_for_posts'),
            'apost_page_root'   => get_option('page_on_front')
        );
        $breadcrumb_settings       = get_option('_metaseo_breadcrumbs');
        if (is_array($breadcrumb_settings)) {
            $this->breadcrumb_settings = array_merge($this->breadcrumb_settings, $breadcrumb_settings);
        }
    }

    /**
     * Breadcrumb Trail Filling Function
     *
     * This functions fills the breadcrumb trail.
     *
     * @return void
     */
    public function checkPosts()
    {
        global $wp_query;
        //For the front page, as it may also validate as a page, do it first
        if (is_front_page()) {
            global $current_site;
            $site_name = get_option('blogname');
            $this->addBreadcrumb($site_name, WPMSEO_TEMPLATE_BREADCRUMB, array('home', 'current-item'));
            if (!is_main_site()) {
                $site_name = get_site_option('site_name');
                $template  = '<span property="itemListElement" typeof="ListItem">
<a property="item" typeof="WebPage" title="' . esc_attr__('Go to %title%.', 'wp-meta-seo') . '" href="%link%" class="%type%">
<span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>';
                $this->addBreadcrumb(
                    $site_name,
                    $template,
                    array(
                        'main-home'
                    ),
                    get_home_url($current_site->blog_id)
                );
            }
        } elseif (is_singular()) {
            if (is_attachment()) {
                // attachments
                $this->attachment();
            } else {
                // other post types
                $this->post(get_post());
            }
        } elseif (is_search()) {
            $this->search();
        } elseif (is_author()) {
            $this->author();
        } elseif (is_archive()) {
            $type     = $wp_query->get_queried_object();
            $type_str = get_query_var('post_type');
            if (is_array($type_str)) {
                $type_str = reset($type_str);
            }
            //For date based archives
            if (is_date()) {
                $this->archiveByDate($this->getType());
            } elseif (is_post_type_archive() && !isset($type->taxonomy)
                      && (!is_numeric($this->breadcrumb_settings['apost_' . $type_str . '_root']))) {
                $this->archiveByPosttype();
            } elseif (is_category() || is_tag() || is_tax()) {
                $this->archiveByTerm();
            }
        } elseif (is_404()) {
            $this->addBreadcrumb('404', WPMSEO_TEMPLATE_BREADCRUMB, array('404', 'current-item'));
        } else {
            $type = $wp_query->get_queried_object();
            if (isset($type->taxonomy)) {
                $this->archiveByTerm();
            }
        }
        // home
        if (!is_front_page()) {
            if (!empty($this->breadcrumb_settings['include_home'])) {
                $this->home();
            }
        }
    }

    /**
     * Render breadcrumb
     *
     * @param boolean $return  Whether to return or echo the trail. (optional)
     * @param boolean $reverse Whether to reverse the output or not. (optional)
     *
     * @return string
     */
    public function breadcrumbDisplay($return = false, $reverse = false)
    {
        // order breadcrumb
        if ($reverse) {
            ksort($this->breadcrumbs);
        } else {
            krsort($this->breadcrumbs);
        }

        $html     = '';
        $position = 1;
        //The main compiling loop
        foreach ($this->breadcrumbs as $key => $breadcrumb) {
            // for reverse has true
            if ($reverse) {
                if ($key > 0) {
                    $html .= $this->breadcrumb_settings['separator'];
                }
            } else {
                if ($position > 1) {
                    $html .= $this->breadcrumb_settings['separator'];
                }
            }

            $html .= $this->generateBreadcrumb($breadcrumb, $position);
            $position ++;
        }

        if ($return) {
            return $html; // for return has true
        } else {
            // phpcs:ignore WordPress.Security.EscapeOutput -- Content escaped in the method generateBreadcrumb
            echo $html; // for return has false
        }
    }

    /**
     * Generate breadcrumb
     *
     * @param array   $breadcrumb Breadcrumb info
     * @param integer $position   Position of breadcrumb element
     *
     * @return mixed
     */
    public function generateBreadcrumb($breadcrumb, $position)
    {
        $params = array(
            '%title%'    => esc_attr(strip_tags($breadcrumb['name'])),
            '%link%'     => esc_url($breadcrumb['url']),
            '%htitle%'   => esc_html($breadcrumb['name']),
            '%type%'     => esc_attr($breadcrumb['type']),
            '%ftitle%'   => esc_attr(strip_tags($breadcrumb['name'])),
            '%fhtitle%'  => esc_html($breadcrumb['name']),
            '%position%' => esc_attr($position)
        );
        //The type may be an array, implode it if that is the case
        if (is_array($params['%type%'])) {
            $params['%type%'] = implode(' ', $params['%type%']);
        }

        if (empty($this->breadcrumb_settings['clickable'])) {
            return str_replace(array_keys($params), $params, $this->template_no_anchor);
        } else {
            if ($breadcrumb['click']) {
                //Return template
                return str_replace(array_keys($params), $params, $breadcrumb['template']);
            } else {
                //Return template
                return str_replace(array_keys($params), $params, $this->template_no_anchor);
            }
        }
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This functions fills a breadcrumb for posts
     *
     * @param object $post WP_Post Instance of WP_Post object to create a breadcrumb for
     *
     * @return void
     */
    public function post($post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        $arrays = array(
            'name'     => get_the_title($post),
            'template' => WPMSEO_TEMPLATE_BREADCRUMB,
            'type'     => array('post', 'post-' . $post->post_type, 'current-item'),
            'url'      => null,
            'id'       => $post->ID,
            'click'    => false
        );

        if (is_attachment()) {
            $template           = '<span property="itemListElement" typeof="ListItem">
<a property="item" typeof="WebPage" title="' . esc_attr__('Go to %title%.', 'wp-meta-seo') . '" href="%link%" class="%type%">
<span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>';
            $arrays['template'] = $template;
            $arrays['url']      = get_permalink($post);
            $arrays['click']    = true;
        }
        $this->breadcrumbs[] = $arrays;
        if ($post->post_type === 'page') {
            $frontpage = get_option('page_on_front');
            if ($post->post_parent && (int) $post->ID !== (int) $post->post_parent && (int) $frontpage !== (int) $post->post_parent) {
                $this->postParents($post->post_parent, $frontpage);
            }
        } else {
            $this->postHierarchy($post->ID);
        }
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This recursive functions fills the trail with breadcrumbs for parent posts/pages.
     *
     * @param integer $id        The id of the parent page.
     * @param integer $frontpage The id of the front page.
     *
     * @return WP_Post The parent we stopped at
     */
    public function postParents($id, $frontpage)
    {
        $parent = get_post($id);
        // Add to breadcrumbs list
        $template = '<span property="itemListElement" typeof="ListItem">
<a property="item" typeof="WebPage" title="' . esc_attr__('Go to %title%.', 'wp-meta-seo') . '" href="%link%" class="%type%">
<span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>';
        $this->addBreadcrumb(
            get_the_title($id),
            $template,
            array('post', 'post-' . $parent->post_type),
            get_permalink($id),
            $id
        );
        if ($parent->post_parent >= 0 && $parent->post_parent && (int) $id !== (int) $parent->post_parent && (int) $frontpage !== (int) $parent->post_parent) {
            //If valid call this function
            $parent = $this->postParents($parent->post_parent, $frontpage);
        }
        return $parent;
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This functions fills a breadcrumb for an attachment page.
     *
     * @return void
     */
    public function attachment()
    {
        $post = get_post();
        // Add to breadcrumbs list
        $this->addBreadcrumb(
            get_the_title(),
            WPMSEO_TEMPLATE_BREADCRUMB,
            array('post', 'post-attachment', 'current-item'),
            null,
            $post->ID
        );
        //Done with the current item, now on to the parents
        $frontpage = get_option('page_on_front');
        if ($post->post_parent >= 0 && $post->post_parent && (int) $post->ID !== (int) $post->post_parent
            && (int) $frontpage !== (int) $post->post_parent) {
            $parent = get_post($post->post_parent);
            //set the parent's breadcrumb
            $this->post($parent);
        }
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This functions fills a breadcrumb for a search page.
     *
     * @return void
     */
    public function search()
    {
        $template = '<span property="itemListElement" typeof="ListItem">
<span property="name">' . esc_html__('Search results for "%htitle%"', 'wp-meta-seo') . '</span>
<meta property="position" content="%position%"></span>';
        $this->addBreadcrumb(get_search_query(), $template, array('search', 'current-item'));
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This functions fills a breadcrumb for an author page.
     *
     * @return void
     */
    public function author()
    {
        if (get_query_var('author_name')) {
            $author = get_user_by('slug', get_query_var('author_name'));
        } else {
            $author = get_userdata(get_query_var('author'));
        }
        // array author_name values
        $author_name = array('display_name', 'nickname', 'first_name', 'last_name');
        if (in_array('display_name', $author_name)) {
            // Add to breadcrumbs list
            $template = '<span property="itemListElement" typeof="ListItem">
<span property="name">' . esc_html__('Articles by: %htitle%', 'wp-meta-seo') . '</span>
<meta property="position" content="%position%"></span>';
            $this->addBreadcrumb(
                get_the_author_meta(
                    'display_name',
                    $author->ID
                ),
                $template,
                array(
                    'author',
                    'current-item'
                ),
                null,
                $author->ID
            );
        }
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This functions fills a breadcrumb for a post type archive (WP 3.1 feature)
     *
     * @return void
     */
    public function archiveByPosttype()
    {
        $type = $this->getType();
        // Add to breadcrumbs list
        $this->addBreadcrumb(
            post_type_archive_title('', false),
            WPMSEO_TEMPLATE_BREADCRUMB,
            array(
                'archive',
                'post-' . $type . '-archive',
                'current-item'
            )
        );
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This functions fills a breadcrumb for a date archive.
     *
     * @param string $type The type to restrict the date archives to
     *
     * @return void
     */
    public function archiveByDate($type)
    {
        $date_template = '<span property="itemListElement" typeof="ListItem">
<a property="item" typeof="WebPage" title="' . esc_attr__('Go to the %title% archives.', 'wp-meta-seo') . '" href="%link%" class="%type%">
<span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>';
        if (is_day() || is_single()) {
            $arrays = array(
                'name'     => get_the_time(_x('d', 'day archive breadcrumb date format', 'wp-meta-seo')),
                'template' => WPMSEO_TEMPLATE_BREADCRUMB,
                'type'     => array('archive', 'date-day'),
            );

            if (is_day()) {
                $arrays['type']  = 'current-item';
                $arrays['url']   = null;
                $arrays['click'] = false;
            }
            // if is single
            if (is_single()) {
                $arrays['template'] = $date_template;
                $url                = get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d'));
                $url                = $this->addPosttypeArg($url, $type);
                $arrays['url']      = $url;
                $arrays['click']    = true;
            }

            $this->breadcrumbs[] = $arrays;
        }

        //Now deal with the month breadcrumb
        if (is_month() || is_day() || is_single()) {
            $arrays = array(
                'name'     => get_the_time(_x('F', 'month archive breadcrumb date format', 'wp-meta-seo')),
                'template' => WPMSEO_TEMPLATE_BREADCRUMB,
                'type'     => array('archive', 'date-month'),
            );

            if (is_month()) {
                $arrays['type']  = 'current-item';
                $arrays['url']   = null;
                $arrays['click'] = false;
            }

            if (is_day() || is_single()) {
                $arrays['template'] = $date_template;
                $url                = get_month_link(get_the_time('Y'), get_the_time('m'));
                $url                = $this->addPosttypeArg($url, $type);
                $arrays['url']      = $url;
                $arrays['click']    = true;
            }

            $this->breadcrumbs[] = $arrays;
        }


        $arrays = array(
            'name'     => get_the_time(_x('Y', 'year archive breadcrumb date format', 'wp-meta-seo')),
            'template' => WPMSEO_TEMPLATE_BREADCRUMB,
            'type'     => array('archive', 'date-year'),
        );

        //If this is a year archive, add current-item type
        if (is_year()) {
            $arrays['type']  = 'current-item';
            $arrays['url']   = null;
            $arrays['click'] = false;
        }
        // day or month or single
        if (is_day() || is_month() || is_single()) {
            //We're linking, so set the linked template
            $arrays['template'] = $date_template;
            $url                = get_year_link(get_the_time('Y'));
            $url                = $this->addPosttypeArg($url, $type);
            $arrays['url']      = $url;
            $arrays['click']    = true;
        }

        $this->breadcrumbs[] = $arrays;
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This function fills a breadcrumb for any taxonomy archive, was previously two separate functions
     *
     * @return void
     */
    public function archiveByTerm()
    {
        global $wp_query;
        $term = $wp_query->get_queried_object();
        // Add to breadcrumbs list
        $template = '<span property="itemListElement" typeof="ListItem">
<a property="item" typeof="WebPage" title="' . esc_attr__('Go to the %title% category archives.', 'wp-meta-seo') . '" href="%link%" class="%type%">
<span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>';
        $this->addBreadcrumb(
            $term->name,
            $template,
            array(
                'archive',
                'taxonomy',
                $term->taxonomy,
                'current-item'
            ),
            null,
            $term->term_id
        );
        //Get parents of current term
        if ($term->parent) {
            $this->termParents($term->parent, $term->taxonomy);
        }
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This recursive functions fills the trail with breadcrumbs for parent terms.
     *
     * @param integer $id       The id of the term.
     * @param string  $taxonomy The name of the taxonomy that the term belongs to
     *
     * @return WP_Term The term we stopped at
     */
    public function termParents($id, $taxonomy)
    {
        //Get the current category
        $term = get_term($id, $taxonomy);
        // Add to breadcrumbs list
        $template = '<span property="itemListElement" typeof="ListItem">
<a property="item" typeof="WebPage" title="' . esc_attr__('Go to the %title% category archives.', 'wp-meta-seo') . '" href="%link%" class="%type%">
<span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>';
        $this->addBreadcrumb(
            $term->name,
            $template,
            array(
                'taxonomy',
                $taxonomy
            ),
            $this->addPosttypeArg(
                get_term_link($term),
                null,
                $taxonomy
            ),
            $id
        );
        if ($term->parent && (int) $term->parent !== (int) $id) {
            $term = $this->termParents($term->parent, $taxonomy);
        }
        return $term;
    }

    /**
     * Add a enlement to lists
     *
     * @param string $name     Breadcrumb name
     * @param string $template Breadcrumb template
     * @param array  $type     Breadcrumb type
     * @param string $url      Breadcrumb URL
     * @param null   $id       Breadcrumb id
     *
     * @return void
     */
    public function addBreadcrumb(
        $name = '',
        $template = '',
        array $type = array(),
        $url = '',
        $id = null
    ) {
        $allowed_html             = wp_kses_allowed_html('post');
        $tmp                      = '<span property="itemListElement" typeof="ListItem">
<a property="item" typeof="WebPage" title="' . esc_attr__('Go to %title%.', 'wp-meta-seo') . '" href="%link%" class="%type%">
<span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>';
        $this->template_no_anchor = WPMSEO_TEMPLATE_BREADCRUMB;
        if (is_null($template)) {
            $template = wp_kses($tmp, $allowed_html);
        } else {
            //Loose comparison, evaluates to true if URL is '' or null
            if (is_null($url)) {
                $this->template_no_anchor = wp_kses($template, $allowed_html);
                $template                 = wp_kses($tmp, $allowed_html);
            } else {
                $template = wp_kses($template, $allowed_html);
            }
        }

        // check click or not
        if (empty($this->breadcrumb_settings['clickable'])) {
            $click = false;
        } else {
            if (is_null($url)) {
                $click = false;
            } else {
                $click = true;
            }
        }

        // add to array
        $this->breadcrumbs[] = array(
            'name'     => $name,
            'template' => $template,
            'type'     => $type,
            'url'      => $url,
            'id'       => $id,
            'click'    => $click
        );
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This function fills breadcrumbs for any post taxonomy
     *
     * @param integer $id The id of the post to figure out the taxonomy for
     *
     * @return void
     */
    public function postHierarchy($id)
    {
        $taxonomy = 'category';
        if (is_taxonomy_hierarchical($taxonomy)) {
            // Get all term of object
            $wpms_object      = get_the_terms($id, $taxonomy);
            $potential_parent = 0;
            $term             = false;
            // check array
            if (is_array($wpms_object)) {
                $wpms_use_term = key($wpms_object);
                foreach ($wpms_object as $key => $object) {
                    if ($object->parent > 0 && ($potential_parent === 0 || $object->parent === $potential_parent)) {
                        $wpms_use_term    = $key;
                        $potential_parent = $object->term_id;
                    }
                }
                $term = $wpms_object[$wpms_use_term];
            }

            if ($term instanceof WP_Term) {
                //Fill out the term hiearchy
                $this->termParents($term->term_id, $taxonomy);
            }
        }
        //else {//$this->post_terms($id, $taxonomy);
        //}
    }

    /**
     * Adds the post type argument to the URL iff the passed in type is not post
     *
     * @param string $url      The URL to possibly add the post_type argument to
     * @param string $type     The type to possibly add to the URL
     * @param string $taxonomy If we're dealing with a taxonomy term, the taxonomy of that term
     *
     * @return string The possibly modified URL
     */
    public function addPosttypeArg($url, $type = null, $taxonomy = null)
    {
        global $wp_taxonomies;
        if (is_null($type)) {
            $type = $this->getType();
        }

        // add post_type to url
        $query_arg = (!($taxonomy && $type === $wp_taxonomies[$taxonomy]->object_type[0]) && $type !== 'post');
        if ($query_arg) {
            $url = add_query_arg(array('post_type' => $type), $url);
        }
        return $url;
    }

    /**
     * Get post type
     *
     * @param string $default Post type
     *
     * @return string
     */
    public function getType($default = 'post')
    {
        $type = get_query_var('post_type', $default);
        if ($type === '' || is_array($type)) {
            $post = get_post();
            if ($post instanceof WP_Post) {
                $type = $post->post_type;
            } else {
                $type = $default;
            }
        }
        return esc_attr($type);
    }

    /**
     * A Breadcrumb Trail Filling Function
     *
     * This functions fills a breadcrumb for the home page.
     *
     * @return void
     */
    public function home()
    {
        global $current_site;
        //Get the site name
        $site_name = get_option('blogname');
        $template  = '<span property="itemListElement" typeof="ListItem">
<a property="item" typeof="WebPage" title="' . esc_attr__('Go to %title%.', 'wp-meta-seo') . '" href="%link%" class="%type%">
<span property="name">%htitle%</span></a><meta property="position" content="%position%"></span>';

        if (!empty($this->breadcrumb_settings['home_text_default'])) {
            $title = $this->breadcrumb_settings['home_text'];
        } else {
            $title = $site_name;
        }
        $this->addBreadcrumb($title, $template, array('home'), get_home_url());
        if (!is_main_site()) {
            //Get the site name
            $site_name = get_site_option('site_name');
            // Add to breadcrumbs list
            $this->addBreadcrumb($site_name, $template, array('main-home'), get_home_url($current_site->blog_id));
        }
    }
}
