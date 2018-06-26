<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class MetaSeoDashboard
 * This class implements the statistical criteria of Meta SEO
 */
class MetaSeoDashboard
{
    /**
     * max length meta title
     * @var int
     */
    public static $meta_title_length = 69;
    /**
     * max length meta description
     * @var int
     */
    public static $meta_desc_length = 320;
    /**
     * Get image optimize
     * @return array
     */
    public static function moptimizationChecking()
    {
        global $wpdb;
        $imgs = 0;
        $imgs_are_good = 0;
        $imgs_metas_are_good = array();
        $response = array(
            'imgs_statis' => array(0, 0, 100),
            'imgs_metas_statis' => array(0, 0, 100),
        );

        $imgs_metas_are_good['alt'] = 0;
        $imgs_metas_are_not_good['alt'] = 0;

        $post_types = MetaSeoContentListTable::getPostTypes();
        $query = "SELECT `ID`,`post_content`
					FROM $wpdb->posts
					WHERE `post_type` IN ($post_types)
					AND `post_content` <> ''
					AND `post_content` LIKE '%<img%>%' 
					ORDER BY ID";

        $posts = $wpdb->get_results($query);
        if (count($posts) > 0) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $upload_dir = wp_upload_dir();

            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis)) {
                    $meta_analysis = array();
                }

                $doc->loadHTML($post->post_content);
                $tags = $doc->getElementsByTagName('img');
                foreach ($tags as $tag) {
                    $img_src = $tag->getAttribute('src');

                    if (!preg_match('/\.(jpg|png|gif)$/i', $img_src, $matches)) {
                        continue;
                    }

                    $img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img_src);
                    if (!file_exists($img_path)) {
                        continue;
                    }

                    $width = $tag->getAttribute('width');
                    $height = $tag->getAttribute('height');
                    if (list($real_width, $real_height) = @getimagesize($img_path)) {
                        $ratio_origin = $real_width / $real_height;
                        //Check if img tag is missing with/height attribute value or not
                        if (!$width && !$height) {
                            $width = $real_width;
                            $height = $real_height;
                        } elseif ($width && !$height) {
                            $height = $width * (1 / $ratio_origin);
                        } elseif ($height && !$width) {
                            $width = $height * ($ratio_origin);
                        }

                        if (($real_width <= $width && $real_height <= $height)
                            || (!empty($meta_analysis) && !empty($meta_analysis['imgresize']))) {
                            $imgs_are_good++;
                        }

                        if (trim($tag->getAttribute('alt'))
                            || (!empty($meta_analysis) && !empty($meta_analysis['imgalt']))) {
                            $imgs_metas_are_good['alt']++;
                        }
                    }

                    $imgs++;
                }
            }

            //Report analytic of images optimization
            $imgs_metas = $imgs_metas_are_good['alt'];
            $response['imgs_statis'][0] = $imgs_are_good;
            $response['imgs_statis'][1] = $imgs;
            $response['imgs_metas_statis'][0] = $imgs_metas;
            $response['imgs_metas_statis'][1] = $imgs;

            if (!empty($imgs)) {
                $percent_iresizing = ceil($imgs_are_good / $imgs * 100);
            } else {
                $percent_iresizing = 100;
            }
            $response['imgs_statis'][2] = $percent_iresizing;
            if (!empty($imgs)) {
                $percent_imeta = ceil($imgs_metas / $imgs * 100);
            } else {
                $percent_imeta = 100;
            }
            $response['imgs_metas_statis'][2] = $percent_imeta;
        }

        return $response;
    }

    /**
     * Display rank of site
     * @param $url
     */
    public function displayRank($url)
    {
        $rank = $this->getRank($url);
        if ($rank !== '') {
            echo $rank;
        } else {
            echo __('We can\'t get rank of this site from Alexa.com!', 'wp-meta-seo');
        }
    }

    /**
     * Get rank of site
     * @param $url
     * @return mixed|string
     */
    public function getRank($url)
    {
        if (!function_exists('curl_version')) {
            if (!$content = @file_get_contents($url)) {
                return '';
            }
        } else {
            if (!is_array($url)) {
                $url = array($url);
            }
            $contents = $this->getContents($url);
            $content = $contents[0];
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        @$doc->loadHTML($content);
        $doc->preserveWhiteSpace = false;

        $finder = new DOMXPath($doc);
        $classname = 'note-no-data';
        $nodes = $finder->query("//section[contains(@class, '$classname')]");
        if ($nodes->length < 1) {
            $classname = 'rank-row';
            $nodes = $finder->query("//div[contains(@class, '$classname')]");
        }

        $tmp_dom = new DOMDocument();
        foreach ($nodes as $key => $node) {
            $tmp_dom->appendChild($tmp_dom->importNode($node, true));
        }

        $html = trim($tmp_dom->saveHTML());
        $html = str_replace('We don\'t have', __('Alexa doesn\'t have', 'wp-meta-seo'), $html);
        $html = str_replace('Get Certified', '', $html);
        $html = str_replace('"/topsites/countries', '"http://www.alexa.com/topsites/countries', $html);
        return $html;
    }

    /**
     * Get content a file
     * @param $urls
     * @return array
     */
    public function getContents($urls)
    {
        $mh = curl_multi_init();
        $curl_array = array();
        $useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';
        foreach ($urls as $i => $url) {
            $curl_array[$i] = curl_init($url);
            curl_setopt($curl_array[$i], CURLOPT_URL, $url);
            curl_setopt($curl_array[$i], CURLOPT_USERAGENT, $useragent); // set user agent
            curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$i], CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl_array[$i], CURLOPT_ENCODING, "UTF-8");
            curl_multi_add_handle($mh, $curl_array[$i]);
        }

        $running = null;
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $contents = array();
        foreach ($urls as $i => $url) {
            $content = curl_multi_getcontent($curl_array[$i]);
            $contents[$i] = $content;
        }

        foreach ($urls as $i => $url) {
            curl_multi_remove_handle($mh, $curl_array[$i]);
        }
        curl_multi_close($mh);
        return $contents;
    }

    /**
     * update option dashboard
     * @param $name
     * @return mixed
     */
    public static function updateDashboard($name)
    {
        $options_dashboard = get_option('options_dashboard');
        MetaSeoDashboard::updateOptionDash($options_dashboard, $name);
        $options_dashboard = get_option('options_dashboard');
        $results = $options_dashboard[$name];
        return $results;
    }

    /**
     *  get Count posts
     */
    public static function getCountPost()
    {
        global $wpdb;
        $where = array();
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        $post_type = "'" . implode("', '", esc_sql($post_types)) . "'";
        $where[] = "post_type IN ($post_type)";
        $states = get_post_stati(array('show_in_admin_all_list' => true));
        $all_states = "'" . implode("', '", $states) . "'";
        $where[] = "post_status IN ($all_states)";
        $query = "SELECT COUNT(ID) "
            . " FROM $wpdb->posts "
            . " WHERE " . implode(' AND ', $where);

        $total_posts = $wpdb->get_var($query);
        return $total_posts;
    }

    /**
     * get params meta title filled for dashboard
     * @return array
     */
    public static function metaTitle()
    {
        $total_posts = MetaSeoDashboard::getCountPost();
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($post_types['attachment'])) {
            unset($post_types['attachment']);
        }

        $results = array(0, array(0, (int)$total_posts));
        $args = array(
            'posts_per_page' => -1,
            'post_type' => $post_types,
            'meta_key' => '_metaseo_metatitle',
            'meta_query' => array(
                array(
                    'key' => '_metaseo_metatitle',
                    'value' => '',
                    'compare' => '!=',
                ),
            ),
        );
        $query = new WP_Query($args);
        $posts = $query->get_posts();
        $metatitle_filled = 0;
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis)) {
                    $meta_analysis = array();
                }

                $meta_title = get_post_meta($post->ID, '_metaseo_metatitle', true);
                if (($meta_title != '' && mb_strlen($meta_title, 'UTF-8') <= self::$meta_title_length)
                    || (!empty($meta_analysis) && !empty($meta_analysis['metatitle']))) {
                    $metatitle_filled++;
                }
            }

            $results = array(ceil($metatitle_filled / $total_posts * 100), array($metatitle_filled, (int)$total_posts));
        }

        return $results;
    }

    /**
     * get html meta title filled for dashboard
     */
    public static function dashboardMetaTitle()
    {
        $results = MetaSeoDashboard::updateDashboard('metatitle');
        wp_send_json($results);
    }

    /**
     * get params meta description filled for dashboard
     * @return array
     */
    public static function metaDesc()
    {
        $total_posts = MetaSeoDashboard::getCountPost();
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (!empty($post_types['attachment'])) {
            unset($post_types['attachment']);
        }

        $results = array(0, array(0, $total_posts));
        $args = array(
            'posts_per_page' => -1,
            'post_type' => $post_types,
            'meta_key' => '_metaseo_metadesc',
            'meta_query' => array(
                array(
                    'key' => '_metaseo_metadesc',
                    'value' => '',
                    'compare' => '!=',
                ),
            ),
        );
        $query = new WP_Query($args);
        $posts = $query->get_posts();
        $metadesc_filled = 0;
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis)) {
                    $meta_analysis = array();
                }

                $meta_desc = get_post_meta($post->ID, '_metaseo_metadesc', true);
                if (($meta_desc != '' && mb_strlen($meta_desc, 'UTF-8') <= self::$meta_desc_length)
                    || (!empty($meta_analysis) && !empty($meta_analysis['metadesc']))) {
                    $metadesc_filled++;
                }
            }

            $results = array(ceil($metadesc_filled / $total_posts * 100), array($metadesc_filled, $total_posts));
        }

        return $results;
    }

    /**
     * Return html description filled for dashboard
     */
    public static function dashboardMetaDesc()
    {
        $results = MetaSeoDashboard::updateDashboard('metadesc');
        wp_send_json($results);
    }

    /**
     * Return link_meta for dashboard
     * @return array
     */
    public static function linkMeta()
    {
        global $wpdb;
        $mlink_complete = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpms_links WHERE meta_title !='' AND type ='url'");
        $mcount_link = $wpdb->get_var(
            "SELECT COUNT(*) FROM " . $wpdb->prefix . "wpms_links WHERE type ='url'"
        );

        if ($mcount_link == 0) {
            $link_percent = 100;
        } else {
            $link_percent = ceil($mlink_complete / $mcount_link * 100);
        }

        $results = array($link_percent, array($mlink_complete, $mcount_link));
        return $results;
    }

    /**
     * Return html link_meta for dashboard
     */
    public static function dashboardLinkMeta()
    {
        $results = MetaSeoDashboard::linkMeta();
        wp_send_json($results);
    }

    /**
     * Return permalink for dashboard
     * @return int
     */
    public static function permalink()
    {
        $permalink = 50;
        $permalink_structure = get_option('permalink_structure');
        if (strpos($permalink_structure, 'postname') == false && strpos($permalink_structure, 'category') == false) {
            $permalink = 0;
        } elseif (strpos($permalink_structure, 'postname') == true
            && strpos($permalink_structure, 'category') == true) {
            $permalink = 100;
        } elseif (strpos($permalink_structure, 'postname') == true
            || strpos($permalink_structure, 'category') == true) {
            $permalink = 50;
        }
        return $permalink;
    }

    /**
     * Return html permalink for dashboard
     */
    public static function dashboardPermalink()
    {
        $permalink = MetaSeoDashboard::permalink();
        wp_send_json($permalink);
    }

    /**
     * Return count new content updated for dashboard
     * @return array
     */
    public static function newContent()
    {
        $total_posts = MetaSeoDashboard::getCountPost();
        $newcontent_args = array(
            'date_query' => array(
                array(
                    'column' => 'post_modified_gmt',
                    'after' => '30 days ago'
                )
            ),
            'posts_per_page' => -1,
            'post_type' => array('post', 'page'),
        );

        $newcontent = new WP_Query($newcontent_args);

        if (count($newcontent->get_posts()) >= $total_posts) {
            $count_new = 100;
        } else {
            $count_new = ceil(count($newcontent->get_posts()) / $total_posts * 100);
        }
        $results = array($count_new, array(count($newcontent->get_posts()), $total_posts));
        return $results;
    }

    /**
     * Return html new content updated for dashboard
     */
    public static function dashboardNewContent()
    {
        $results = MetaSeoDashboard::updateDashboard('newcontent');
        wp_send_json($results);
    }

    /**
     * Return count link 404 , count link 404 is redirected , percent
     * @return array
     */
    public static function get404Link()
    {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM " . $wpdb->prefix . "wpms_links WHERE (broken_internal=%d OR broken_indexed=%d) ",
            array(1, 1)
        );
        $count_404 = $wpdb->get_var($sql);

        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM " . $wpdb->prefix . "wpms_links
             WHERE link_url_redirect != '' AND (broken_internal=%d OR broken_indexed=%d) ",
            array(1, 1)
        );
        $count_404_redirected = $wpdb->get_var($sql);
        if ($count_404 != 0) {
            $percent = ceil($count_404_redirected / $count_404 * 100);
        } else {
            $percent = 100;
        }
        return array('count_404' => $count_404, 'count_404_redirected' => $count_404_redirected, 'percent' => $percent);
    }

    /**
     * Return count image is optimized
     * @return int
     */
    public function getImagesOptimizer()
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM INFORMATION_SCHEMA.TABLES
            WHERE table_name = %s AND TABLE_SCHEMA = %s", array($wpdb->prefix . 'wpio_images', $wpdb->dbname));
        $row = $wpdb->get_results($sql);
        if (!empty($row)) {
            $query = 'SELECT distinct file FROM ' . $wpdb->prefix . 'wpio_images';
            $files = $wpdb->get_results($query);
            $image_optimize = 0;
            foreach ($files as $file) {
                if (file_exists(str_replace('/', DIRECTORY_SEPARATOR, ABSPATH . $file->file))) {
                    $image_optimize++;
                }
            }
            return $image_optimize;
        } else {
            return 0;
        }
    }

    /**
     * Get count image
     * @return array
     */
    public function getImagesCount()
    {
        $image_optimize = $this->getImagesOptimizer();
        $allowed_ext = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'pdf');
        $count_image = 0;
        $scan_dir = str_replace('/', DIRECTORY_SEPARATOR, ABSPATH);
        foreach (new RecursiveIteratorIterator(new IgnorantRecursiveDirectoryIterator($scan_dir)) as $filename) {
            if (!in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowed_ext)) {
                continue;
            }

            $count_image++;
        }

        if ($count_image == 0) {
            $precent = 0;
        } else {
            $precent = ceil($image_optimize / $count_image * 100);
        }
        return array('image_optimize' => $image_optimize, 'count_image' => $count_image, 'percent' => $precent);
    }

    /**
     * update time dashboard update
     * @param $name
     */
    public static function dashLastUpdate($name)
    {
        if ($name == 'metadesc') {
            update_option('_wpms_dash_last_update', time());
        }
    }

    /**
     * update option dashboard
     * @param array $options_dashboard  all criteria in dashboard
     * @param string $name option name
     */
    public static function updateOptionDash($options_dashboard, $name)
    {
        $last_update_post = get_option('wpms_last_update_post');
        $last_dash_update = get_option('_wpms_dash_last_update');
        if (empty($options_dashboard) || is_array($options_dashboard)
            || (!empty($options_dashboard) && empty($options_dashboard[$name]))
            || (!empty($options_dashboard) && !empty($options_dashboard[$name]) && !empty($last_update_post) && $last_update_post > $last_dash_update)) {
            $results = array();
            switch ($name) {
                case 'metatitle':
                    $results = MetaSeoDashboard::metaTitle();
                    break;
                case 'metadesc':
                    $results = MetaSeoDashboard::metaDesc();
                    break;
                case 'newcontent':
                    $results = MetaSeoDashboard::newContent();
                    break;
                case 'image_meta':
                    $results = MetaSeoDashboard::moptimizationChecking();
                    break;
            }
            $options_dashboard[$name] = $results;
            update_option('options_dashboard', $options_dashboard);
            MetaSeoDashboard::dashLastUpdate($name);
        }
    }

    /**
     * get image metas
     */
    public static function dashImgsMeta()
    {
        global $wpdb;
        $imgs = 0;
        $imgs_are_good = 0;
        $imgs_metas_are_good = array();
        $meta_keys = array('alt');
        // create default value
        $response = array(
            'imgs_statis' => array(0, 0, 100),
            'imgs_metas_statis' => array(0, 0, 100),
        );

        $options_dashboard = get_option('options_dashboard');
        $option_last_update_post = get_option('wpms_last_update_post');
        $option_last_dash_update = get_option('_wpms_dash_last_update');
        // get response from options_dashboard option
        if (!empty($options_dashboard) && is_array($options_dashboard)
            && !empty($options_dashboard['image_meta']) && $option_last_update_post < $option_last_dash_update) {
            $results = $options_dashboard['image_meta'];
            $results['status'] = true;
            wp_send_json($results);
        }

        // find img good and not good in post content to update
        foreach ($meta_keys as $meta_key) {
            $imgs_metas_are_good[$meta_key] = 0;
        }

        $limit = 50;
        $offset = ($_POST['page'] - 1) * $limit;
        $post_types = MetaSeoContentListTable::getPostTypes();
        $query = "SELECT `ID`,`post_content`
					FROM $wpdb->posts
					WHERE `post_type` IN ($post_types)
					AND `post_content` <> ''
					AND `post_content` LIKE '%<img%>%' 
					ORDER BY ID LIMIT $limit OFFSET $offset";

        $posts = $wpdb->get_results($query);
        if (count($posts) > 0) {
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $upload_dir = wp_upload_dir();

            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis)) {
                    $meta_analysis = array();
                }
                // load dom html
                $doc->loadHTML($post->post_content);
                $tags = $doc->getElementsByTagName('img');
                foreach ($tags as $tag) {
                    $img_src = $tag->getAttribute('src');

                    if (!preg_match('/\.(jpg|png|gif)$/i', $img_src, $matches)) {
                        continue;
                    }

                    $img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img_src);
                    if (!file_exists($img_path)) {
                        continue;
                    }

                    $width = $tag->getAttribute('width');
                    $height = $tag->getAttribute('height');
                    if (list($real_width, $real_height) = @getimagesize($img_path)) {
                        $ratio_origin = $real_width / $real_height;
                        //Check if img tag is missing with/height attribute value or not
                        if (!$width && !$height) {
                            $width = $real_width;
                            $height = $real_height;
                        } elseif ($width && !$height) {
                            $height = $width * (1 / $ratio_origin);
                        } elseif ($height && !$width) {
                            $width = $height * ($ratio_origin);
                        }

                        if (($real_width <= $width && $real_height <= $height)
                            || (!empty($meta_analysis) && !empty($meta_analysis['imgresize']))) {
                            $imgs_are_good++;
                        }
                        foreach ($meta_keys as $meta_key) {
                            if (trim($tag->getAttribute($meta_key))
                                || (!empty($meta_analysis) && !empty($meta_analysis['imgalt']))) {
                                $imgs_metas_are_good[$meta_key]++;
                            }
                        }
                        $imgs++;
                    }
                }
            }

            $countImg = $imgs + (int)$_POST['imgs_count'];
            //Report analytic of images optimization
            $c_imgs_metas = $imgs_metas_are_good['alt'];
            // get results for image resize
            $response['imgs_statis'][0] = $imgs_are_good + (int)$_POST['imgs_statis'];
            // get results for image meta
            $response['imgs_metas_statis'][0] = $c_imgs_metas + (int)$_POST['imgs_metas_statis'];
            $response['imgs_statis'][1] = $response['imgs_metas_statis'][1] = $response['imgs_count'] = $countImg;
            $response['page'] = (int)$_POST['page'];
        } else {
            if (!empty($_POST['imgs_count'])) {
                $percent_iresizing = ceil($_POST['imgs_statis'] / $_POST['imgs_count'] * 100);
            } else {
                $percent_iresizing = 100;
            }
            $response['imgs_statis'][2] = $percent_iresizing;
            if (!empty($_POST['imgs_count'])) {
                $percent_imeta = ceil($_POST['imgs_metas_statis'] / $_POST['imgs_count'] * 100);
            } else {
                $percent_imeta = 100;
            }

            $response['imgs_metas_statis'][2] = $percent_imeta;
            $options_dashboard['image_meta'] = array(
                'imgs_statis' => array($_POST['imgs_statis'], $_POST['imgs_count'], $percent_iresizing),
                'imgs_metas_statis' => array($_POST['imgs_metas_statis'], $_POST['imgs_count'], $percent_imeta)
            );

            // update options_dashboard option
            if (!empty($options_dashboard) && is_array($options_dashboard)) {
                if (empty($options_dashboard['image_meta'])) {
                    update_option('options_dashboard', $options_dashboard);
                    MetaSeoDashboard::dashLastUpdate('image_meta');
                } else {
                    $option_last_update_post = get_option('wpms_last_update_post');
                    $option_last_dash_update = get_option('_wpms_dash_last_update');
                    if ((!empty($option_last_update_post) && $option_last_update_post > $option_last_dash_update)
                        || empty($option_last_update_post)) {
                        update_option('options_dashboard', $options_dashboard);
                        MetaSeoDashboard::dashLastUpdate('image_meta');
                    }
                }
            } else {
                update_option('options_dashboard', $options_dashboard);
                MetaSeoDashboard::dashLastUpdate('image_meta');
            }

            $options_dashboard = get_option('options_dashboard');
            $results = $options_dashboard['image_meta'];

            $results['status'] = true;
            wp_send_json($results);

        }

        wp_send_json($response);
    }
}
