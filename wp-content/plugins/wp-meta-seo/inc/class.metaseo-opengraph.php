<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class MetaSeoOpenGraph
 * Get meta data
 */
class MetaSeoOpenGraph
{
    /**
     * Get meta title for title tag
     *
     * @param boolean $is_shop Check is shop
     * @param integer $id      Id of post
     *
     * @return mixed|string
     */
    public function getTitle($is_shop, $id)
    {
        if ($is_shop) {
            $shop       = get_post($id);
            $meta_title = get_post_meta($id, '_metaseo_metatitle', true);
            if ($meta_title !== maybe_unserialize($meta_title)) {
                $meta_title = '';
            }

            if ($meta_title === '') {
                $meta_title = $shop->post_title;
            }

            return esc_html($meta_title);
        }

        $meta_title = get_post_meta($id, '_metaseo_metatitle', true);
        if ($meta_title !== maybe_unserialize($meta_title)) {
            $meta_title = '';
        }

        if ($meta_title === '') {
            $post = get_post($id);
            if (empty($post)) {
                return '';
            }
            $meta_title = $post->post_title;
        }

        return esc_html($meta_title);
    }

    /**
     * Get meta title for meta tag
     *
     * @param array  $settings   Meta seo settings
     * @param string $meta_title Meta title
     *
     * @return string
     */
    public function getMetaTitle($settings, $meta_title)
    {
        $meta_title_esc = esc_attr($meta_title);
        // check homepage is a page
        if ($meta_title === '' && is_front_page()) {
            $meta_title_esc = esc_attr($settings['metaseo_title_home']);
            if ($meta_title_esc !== maybe_unserialize($meta_title_esc)) {
                $meta_title_esc = '';
            }
        }

        return esc_attr($meta_title_esc);
    }

    /**
     * Get meta keyword for meta tag
     *
     * @param array   $settings Meta seo settings
     * @param integer $id       Id of post
     *
     * @return string
     */
    public function getKeyword($settings, $id)
    {
        $keywords = '';
        if (isset($settings['metaseo_showkeywords']) && (int) $settings['metaseo_showkeywords'] === 1) {
            $meta_keywords = get_post_meta($id, '_metaseo_metakeywords', true);
            $keywords      = esc_attr($meta_keywords);
        }
        return esc_attr($keywords);
    }

    /**
     * Get meta description for meta tag
     *
     * @param array   $settings Meta seo settings
     * @param integer $id       Id of post
     * @param string  $content  Content of post
     *
     * @return string
     */
    public function getDesc($settings, $id, $content)
    {
        $meta_desc_esc = get_post_meta($id, '_metaseo_metadesc', true);
        if ($meta_desc_esc !== maybe_unserialize($meta_desc_esc)) {
            $meta_desc_esc = '';
        }

        if ($meta_desc_esc === '') {
            $content = strip_shortcodes($content);
            $content = trim(strip_tags($content));
            if (strlen($content) > MPMSCAT_DESC_LENGTH) {
                $meta_desc_esc = substr($content, 0, 316) . ' ...';
            } else {
                $meta_desc_esc = $content;
            }
        }

        if (get_post_meta($id, '_metaseo_metadesc', true) === '' && is_front_page()) {
            $meta_desc_esc = esc_attr($settings['metaseo_desc_home']);
            if ($meta_desc_esc !== maybe_unserialize($meta_desc_esc)) {
                $meta_desc_esc = '';
            }
        }
        return esc_attr($meta_desc_esc);
    }

    /**
     * Get meta facebook title
     *
     * @param string  $meta_title_esc Meta title default
     * @param integer $id             Id of post
     *
     * @return mixed|string
     */
    public function getFbtitle($meta_title_esc, $id)
    {
        $meta_fbtitle = get_post_meta($id, '_metaseo_metaopengraph-title', true);
        if ($meta_fbtitle !== maybe_unserialize($meta_fbtitle)) {
            $meta_fbtitle = '';
        }

        if ($meta_fbtitle === '') {
            $meta_fbtitle = $meta_title_esc;
        }

        return esc_attr($meta_fbtitle);
    }

    /**
     * Get meta facebook description
     *
     * @param string  $meta_desc_esc Meta description default
     * @param integer $id            Id of post
     *
     * @return mixed|string
     */
    public function getFbdesc($meta_desc_esc, $id)
    {
        $meta_fbdesc = get_post_meta($id, '_metaseo_metaopengraph-desc', true);
        if ($meta_fbdesc !== maybe_unserialize($meta_fbdesc)) {
            $meta_fbdesc = '';
        }

        if ($meta_fbdesc === '') {
            $meta_fbdesc = $meta_desc_esc;
        }

        return esc_attr($meta_fbdesc);
    }

    /**
     * Get meta facebook image and twiter image
     *
     * @param integer $id Id of post
     *
     * @return array
     */
    public function getImage($id)
    {
        $meta_twimage = get_post_meta($id, '_metaseo_metatwitter-image', true);
        $meta_fbimage = get_post_meta($id, '_metaseo_metaopengraph-image', true);

        $default_image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail');
        if (empty($meta_twimage) && isset($default_image[0])) {
            $meta_twimage = $default_image[0];
        }

        if (empty($meta_fbimage) && isset($default_image[0])) {
            $meta_fbimage = $default_image[0];
        }

        return array(
            esc_url($meta_fbimage),
            esc_url($meta_twimage)
        );
    }

    /**
     * Get meta twiter title
     *
     * @param string  $meta_title_esc Meta title default
     * @param integer $id             Id of post
     *
     * @return string
     */
    public function getTwtitle($meta_title_esc, $id)
    {
        $twitter_title = get_post_meta($id, '_metaseo_metatwitter-title', true);
        if ($twitter_title !== maybe_unserialize($twitter_title)) {
            $twitter_title = '';
        }

        $meta_twtitle = esc_attr($twitter_title);
        if ($meta_twtitle === '') {
            $meta_twtitle = $meta_title_esc;
        }

        return esc_attr($meta_twtitle);
    }

    /**
     * Get meta twiter description
     *
     * @param string  $meta_desc_esc Meta description default
     * @param integer $id            Id of post
     *
     * @return string
     */
    public function getTwdesc($meta_desc_esc, $id)
    {
        $twitter_desc = get_post_meta($id, '_metaseo_metatwitter-desc', true);
        if ($twitter_desc !== maybe_unserialize($twitter_desc)) {
            $twitter_desc = '';
        }

        $meta_twdesc = esc_attr($twitter_desc);
        if ($meta_twdesc === '') {
            $meta_twdesc = $meta_desc_esc;
        }

        return esc_attr($meta_twdesc);
    }

    /**
     * Get meta twiter card
     *
     * @param array $settings Meta seo settings
     *
     * @return string
     */
    public function getTwCard($settings)
    {
        if ((!empty($settings['metaseo_twitter_card']))) {
            $meta_twcard = $settings['metaseo_twitter_card'];
        } else {
            $meta_twcard = 'summary';
        }

        return esc_attr($meta_twcard);
    }

    /**
     * Get meta for home page
     *
     * @param array $settings Meta seo settings
     *
     * @return array
     */
    public function getHome($settings)
    {
        // get option reading
        $mpage_for_posts = get_option('page_for_posts');
        $mshow_on_front  = get_option('show_on_front');
        $title           = '';
        $desc            = '';
        $page_follow     = 'follow';
        $page_index      = 'index';
        if ($mshow_on_front === 'posts') {
            $title = $settings['metaseo_title_home'];
            $desc  = $settings['metaseo_desc_home'];
            if ($title !== maybe_unserialize($title)) {
                $title = '';
            }

            if ($desc !== maybe_unserialize($desc)) {
                $desc = '';
            }

            // set meta title when setting is empty
            if ($settings['metaseo_title_home'] === '') {
                $title = get_bloginfo('name') . ' - ' . get_bloginfo('description');
            }

            // set meta description when setting is empty
            if ($settings['metaseo_desc_home'] === '') {
                $desc = get_bloginfo('description');
            }
        } elseif ($mshow_on_front === 'page') { // is page posts
            $title       = get_post_meta($mpage_for_posts, '_metaseo_metatitle', true);
            $desc        = get_post_meta($mpage_for_posts, '_metaseo_metadesc', true);
            $page_follow = get_post_meta($mpage_for_posts, '_metaseo_metafollow', true);
            $page_index  = get_post_meta($mpage_for_posts, '_metaseo_metaindex', true);
        }

        return array(
            'title'       => esc_attr($title),
            'desc'        => esc_attr($desc),
            'page_follow' => esc_attr($page_follow),
            'page_index'  => esc_attr($page_index)
        );
    }

    /**
     * Get meta for front page
     *
     * @param array $settings Settings
     *
     * @return array
     */
    public function getFrontPageMeta($settings)
    {
        $mpage_on_front = get_option('page_on_front');
        $title          = get_post_meta($mpage_on_front, '_metaseo_metatitle', true);
        $desc           = get_post_meta($mpage_on_front, '_metaseo_metadesc', true);

        if ($title === '') {
            $title = $settings['metaseo_title_home'];
        }

        if ($desc === '') {
            $desc = $settings['metaseo_desc_home'];
        }

        $page_follow = get_post_meta($mpage_on_front, '_metaseo_metafollow', true);
        $page_index  = get_post_meta($mpage_on_front, '_metaseo_metaindex', true);
        return array(
            'title'       => esc_attr($title),
            'desc'        => esc_attr($desc),
            'page_follow' => esc_attr($page_follow),
            'page_index'  => esc_attr($page_index)
        );
    }

    /**
     * Get meta for tag , category
     *
     * @param object $wp_query Wordpress query
     * @param array  $settings Meta seo settings
     *
     * @return array
     */
    public function getTagMeta($wp_query, $settings)
    {
        $term              = $wp_query->get_queried_object();
        $meta_keywords_esc = '';
        if (is_object($term) && !empty($term)) {
            if (function_exists('get_term_meta')) {
                $cat_metatitle = get_term_meta($term->term_id, 'wpms_category_metatitle', true);
                $cat_metadesc  = get_term_meta($term->term_id, 'wpms_category_metadesc', true);
            } else {
                $cat_metatitle = get_metadata('term', $term->term_id, 'wpms_category_metatitle', true);
                $cat_metadesc  = get_metadata('term', $term->term_id, 'wpms_category_metadesc', true);
            }

            if (isset($settings['metaseo_showkeywords']) && (int) $settings['metaseo_showkeywords'] === 1) {
                if (function_exists('get_term_meta')) {
                    $meta_keywords_esc = get_term_meta($term->term_id, 'wpms_category_metakeywords', true);
                } else {
                    $meta_keywords_esc = get_metadata('term', $term->term_id, 'wpms_category_metakeywords', true);
                }
            }

            if (isset($cat_metatitle) && $cat_metatitle !== '') {
                $title = $cat_metatitle;
            } else {
                $title = $term->name;
            }

            if (isset($cat_metadesc) && $cat_metadesc !== '') {
                $desc = $cat_metadesc;
            } else {
                $desc = $term->description;
            }
        } else {
            $title = '';
            $desc  = '';
        }

        return array(
            'title'   => esc_attr($title),
            'desc'    => esc_attr($desc),
            'keyword' => esc_attr($meta_keywords_esc)
        );
    }

    /**
     * Get meta facebook admin and twitter site
     *
     * @param array   $settings Meta seo settings
     * @param integer $id       Id of post
     *
     * @return array
     */
    public function getUserMeta($settings, $id)
    {
        $post = get_post($id);
        if (empty($post)) {
            return array(
                'meta_twitter_site' => '',
                'facebook_admin'    => ''
            );
        }
        $meta_twitter_site = get_user_meta($post->post_author, 'mtwitter', true);
        $facebook_admin    = get_user_meta($post->post_author, 'mfacebook', true);

        if ($settings) {
            if ($meta_twitter_site === '' && $settings['metaseo_showtwitter'] !== '') {
                $meta_twitter_site = $settings['metaseo_showtwitter'];
            }

            if ($facebook_admin === '' && $settings['metaseo_showfacebook'] !== '') {
                $facebook_admin = $settings['metaseo_showfacebook'];
            }
        }
        return array(
            'meta_twitter_site' => esc_attr($meta_twitter_site),
            'facebook_admin'    => esc_attr($facebook_admin)
        );
    }

    /**
     * Get current URL
     *
     * @return mixed|string
     */
    public function getCurentUrl()
    {
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) {
            $http = 'https';
        } else {
            $http = 'http';
        }
        $current_url = $http . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $current_url = esc_url($current_url);
        return $current_url;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        if (is_front_page() || is_home()) {
            $type = 'website';
        } elseif (is_singular()) {
            $type = 'article';
        } else {
            // We use "object" for archives etc. as article doesn't apply there.
            $type = 'object';
        }
        return $type;
    }

    /**
     * Render meta tag
     *
     * @param integer $id                Id of post
     * @param array   $settings          Meta seo settings
     * @param string  $meta_twimage      Meta twiter image
     * @param string  $meta_twcard       Meta twiter card
     * @param string  $meta_twitter_site Meta twiter site
     * @param string  $sitename          Site name
     * @param string  $meta_twdesc       Meta twiter description
     * @param string  $meta_twtitle      Meta twiter title
     * @param string  $facebook_admin    Meta facebook admin
     * @param string  $meta_fbimage      Meta facebook image
     * @param string  $meta_fbdesc       Meta facebook description
     * @param string  $current_url       Current url
     * @param string  $type              Meta type
     * @param string  $fbapp_id          Meta facebook app id
     * @param string  $meta_fbtitle      Meta facebook title
     * @param string  $meta_desc_esc     Meta description
     * @param string  $meta_keywords_esc Meta keywords
     * @param string  $meta_title_esc    Meta title
     * @param string  $page_index        Page index
     * @param string  $page_follow       Page follow
     *
     * @return array
     */
    public function getPatterns(
        $id,
        $settings,
        $meta_twimage,
        $meta_twcard,
        $meta_twitter_site,
        $sitename,
        $meta_twdesc,
        $meta_twtitle,
        $facebook_admin,
        $meta_fbimage,
        $meta_fbdesc,
        $current_url,
        $type,
        $fbapp_id,
        $meta_fbtitle,
        $meta_desc_esc,
        $meta_keywords_esc,
        $meta_title_esc,
        $page_index,
        $page_follow
    ) {
        $patterns = array(
            'twitter_image'  => array(
                '#<meta name="twitter:image" [^<>]+ ?>#i',
                '<meta name="twitter:image" content="' . esc_url($meta_twimage) . '" />',
                ($meta_twimage !== '' ? true : false)
            ),
            'twitter_card'   => array(
                '#<meta name="twitter:card" [^<>]+ ?>#i',
                '<meta name="twitter:card" content="' . esc_attr($meta_twcard) . '" />',
                ($meta_twcard !== '' ? true : false)
            ),
            'twitter_site'   => array(
                '#<meta name="twitter:site" [^<>]+ ?>#i',
                '<meta name="twitter:site" content="' . esc_attr('@' . $meta_twitter_site) . '" />',
                ($meta_twitter_site !== '' ? true : false)
            ),
            'twitter_domain' => array(
                '#<meta name="twitter:domain" [^<>]+ ?>#i',
                '<meta name="twitter:domain" content="' . esc_attr($sitename) . '" />',
                ($sitename !== '' ? true : false)
            ),
            'twitter_desc'   => array(
                '#<meta name="twitter:description" [^<>]+ ?>#i',
                '<meta name="twitter:description" content="' . esc_attr($meta_twdesc) . '" />',
                ($meta_twdesc !== '' ? true : false)
            ),
            'twitter_title'  => array(
                '#<meta name="twitter:title" [^<>]+ ?>#i',
                '<meta name="twitter:title" content="' . esc_attr($meta_twtitle) . '" />',
                ($meta_twtitle !== '' ? true : false)
            ),
            'facebook_admin' => array(
                '#<meta property="fb:admins" [^<>]+ ?>#i',
                '<meta property="fb:admins" content="' . esc_attr($facebook_admin) . '" />',
                ($facebook_admin !== '' ? true : false)
            ),
            'facebook_image' => array(
                '#<meta property="og:image" [^<>]+ ?>#i',
                '<meta property="og:image" content="' . esc_url($meta_fbimage) . '" />',
                ($meta_fbimage !== '' ? true : false)
            ),
            'site_name'      => array(
                '#<meta property="og:site_name" [^<>]+ ?>#i',
                '<meta property="og:site_name" content="' . esc_attr($sitename) . '" />',
                ($sitename !== '' ? true : false)
            ),
            'og:description' => array(
                '#<meta property="og:description" [^<>]+ ?>#i',
                '<meta property="og:description" content="' . esc_attr($meta_fbdesc) . '" />',
                ($meta_fbdesc !== '' ? true : false)
            ),
            'og:url'         => array(
                '#<meta property="og:url" [^<>]+ ?>#i',
                '<meta property="og:url" content="' . esc_url($current_url) . '" />',
                ($current_url !== '' ? true : false)
            ),
            'og:type'        => array(
                '#<meta property="og:type" [^<>]+ ?>#i',
                '<meta property="og:type" content="' . esc_attr($type) . '" />',
                ($type !== '' ? true : false)
            ),
            'fb:app_id'      => array(
                '#<meta property="fb:app_id" [^<>]+ ?>#i',
                '<meta property="fb:app_id" content="' . esc_attr($fbapp_id) . '" />',
                ($type !== '' ? true : false)
            ),
            'og:title'       => array(
                '#<meta property="og:title" [^<>]+ ?>#i',
                '<meta property="og:title" content="' . esc_attr($meta_fbtitle) . '" />',
                ($meta_fbtitle !== '' ? true : false)
            ),
            '_description'   => array(
                '#<meta name="description" [^<>]+ ?>#i',
                '<meta name="description" content="' . esc_attr($meta_desc_esc) . '" />',
                ($meta_desc_esc !== '' ? true : false)
            ),
            'keywords'       => array(
                '#<meta name="keywords" [^<>]+ ?>#i',
                '<meta name="keywords" content="' . esc_attr($meta_keywords_esc) . '" />',
                ($meta_keywords_esc !== '' ? true : false)
            ),
            'title'          => array(
                '#<meta name="title" [^<>]+ ?>#i',
                '<meta name="title" content="' . esc_attr($meta_title_esc) . '" />',
                ($meta_title_esc !== '' ? true : false)
            )
        );

        if (!empty($settings['metaseo_follow']) || !empty($settings['metaseo_index'])) {
            $patterns['follow'] = array(
                '#<meta name="robots" [^<>]+ ?>#i',
                '<meta name="robots" content="' . esc_attr($page_index . ',' . $page_follow) . '" />'
            );
        }

        if (get_post_meta($id, '_metaseo_metatitle', true) !== '') {
            $patterns['title'] = array(
                '#<meta name="title" [^<>]+ ?>#i',
                '<meta name="title" content="' . esc_attr($meta_title_esc) . '" />',
                ($meta_title_esc !== '' ? true : false)
            );
        }

        // unset meta tag if empty value
        if ($meta_keywords_esc === '') {
            unset($patterns['keywords']);
        }

        if (!isset($fbapp_id) || (isset($fbapp_id) && $fbapp_id === '')) {
            unset($patterns['fb:app_id']);
        }

        if ($meta_twitter_site === '') {
            unset($patterns['twitter_site']);
        }

        if ($meta_twimage === '') {
            unset($patterns['twitter_image']);
        }

        if ($meta_twtitle === '') {
            unset($patterns['twitter_title']);
        }

        if ($meta_twdesc === '') {
            unset($patterns['twitter_desc']);
        }

        if ($meta_fbdesc === '') {
            unset($patterns['og:description']);
        }

        if ($meta_desc_esc === '') {
            unset($patterns['_description']);
        }

        if ($facebook_admin === '') {
            unset($patterns['facebook_admin']);
        }

        if ($meta_fbimage === '') {
            unset($patterns['facebook_image']);
        }

        $default_settings = array(
            'metaseo_showsocial'    => 1,
            'metaseo_metatitle_tab' => 0
        );

        if (is_array($settings)) {
            $default_settings = array_merge($default_settings, $settings);
        }

        if (empty($default_settings['metaseo_metatitle_tab'])) {
            unset($patterns['_title']);
        }

        // unset meta tag if empty value
        if ((isset($default_settings['metaseo_showsocial']) && (int) $default_settings['metaseo_showsocial'] === 0)) {
            unset($patterns['twitter_image']);
            unset($patterns['twitter_card']);
            unset($patterns['twitter_site']);
            unset($patterns['twitter_domain']);
            unset($patterns['twitter_desc']);
            unset($patterns['twitter_title']);
            unset($patterns['facebook_admin']);
            unset($patterns['facebook_image']);
            unset($patterns['site_name']);
            unset($patterns['og:description']);
            unset($patterns['og:title']);
            unset($patterns['og:type']);
            unset($patterns['og:url']);
        }

        return $patterns;
    }
}
