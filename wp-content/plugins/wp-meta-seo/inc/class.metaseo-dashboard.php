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
     * Max length meta title
     *
     * @var integer
     */
    public static $meta_title_length = 60;
    /**
     * Max length meta description
     *
     * @var integer
     */
    public static $meta_desc_length = 158;

    /**
     * Get image optimize
     *
     * @return array
     */
    public static function moptimizationChecking()
    {
        global $wpdb;
        $imgs                = 0;
        $imgs_are_good       = 0;
        $imgs_metas_are_good = array();
        $response            = array(
            'imgs_statis'       => array(0, 0, 100),
            'imgs_metas_statis' => array(0, 0, 100),
        );

        $imgs_metas_are_good['alt']     = 0;
        $imgs_metas_are_not_good['alt'] = 0;

        $post_types = MetaSeoContentListTable::getPostTypes();
        foreach ($post_types as &$post_type) {
            $post_type = esc_sql($post_type);
        }
        $post_types = implode("', '", $post_types);
        $where      = array();
        $where[]    = 'post_type IN (\'' . $post_types . '\')';
        $where[]    = 'post_content <> ""';
        $where[]    = 'post_content LIKE "%<img%>%"';
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $posts = $wpdb->get_results('SELECT ID,post_content FROM ' . $wpdb->posts . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY ID');
        if (count($posts) > 0) {
            $upload_dir = wp_upload_dir();
            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis)) {
                    $meta_analysis = array();
                }

                $img_tags = wpmsExtractTags($post->post_content, 'img', true, true);
                foreach ($img_tags as $tag) {
                    if (empty($tag['attributes']['src'])) {
                        continue;
                    }

                    $img_src = $tag['attributes']['src'];
                    if (!preg_match('/\.(jpg|jpeg|png|gif)$/i', $img_src, $matches)) {
                        continue;
                    }

                    $img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img_src);
                    if (!file_exists($img_path)) {
                        continue;
                    }

                    $attrs = array('width', 'height', 'alt');
                    foreach ($attrs as $attr) {
                        if (empty($tag['attributes'][$attr])) {
                            ${$attr}  = false;
                        } else {
                            ${$attr}  = $tag['attributes'][$attr];
                        }
                    }

                    if (list($real_width, $real_height) = getimagesize($img_path)) {
                        $ratio_origin = $real_width / $real_height;
                        //Check if img tag is missing with/height attribute value or not
                        if (!$width && !$height) {
                            $width  = $real_width;
                            $height = $real_height;
                        } elseif ($width && !$height) {
                            $height = $width * (1 / $ratio_origin);
                        } elseif ($height && !$width) {
                            $width = $height * ($ratio_origin);
                        }

                        if (($real_width <= $width && $real_height <= $height)
                            || (!empty($meta_analysis) && !empty($meta_analysis['imgresize']))) {
                            $imgs_are_good ++;
                        }

                        if ($alt && trim($alt)
                            || (!empty($meta_analysis) && !empty($meta_analysis['imgalt']))) {
                            $imgs_metas_are_good['alt'] ++;
                        }
                    }

                    $imgs ++;
                }
            }

            //Report analytic of images optimization
            $imgs_metas                       = $imgs_metas_are_good['alt'];
            $response['imgs_statis'][0]       = $imgs_are_good;
            $response['imgs_statis'][1]       = $imgs;
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
     * Get content a file
     *
     * @param array $urls URL infos
     *
     * @return array
     */
    public function getContents($urls)
    {
        $mh         = curl_multi_init();
        $curl_array = array();
        $useragent  = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';
        foreach ($urls as $i => $url) {
            $curl_array[$i] = curl_init($url);
            curl_setopt($curl_array[$i], CURLOPT_URL, $url);
            curl_setopt($curl_array[$i], CURLOPT_USERAGENT, $useragent); // set user agent
            curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$i], CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl_array[$i], CURLOPT_ENCODING, 'UTF-8');
            curl_multi_add_handle($mh, $curl_array[$i]);
        }

        $running = null;
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $contents = array();
        foreach ($urls as $i => $url) {
            $content      = curl_multi_getcontent($curl_array[$i]);
            $contents[$i] = $content;
        }

        foreach ($urls as $i => $url) {
            curl_multi_remove_handle($mh, $curl_array[$i]);
        }
        curl_multi_close($mh);
        return $contents;
    }

    /**
     * Update option dashboard
     *
     * @param string $name Option name
     *
     * @return mixed
     */
    public static function updateDashboard($name)
    {
        $options_dashboard = get_option('options_dashboard');
        self::updateOptionDash($options_dashboard, $name);
        $options_dashboard = get_option('options_dashboard');
        $results           = $options_dashboard[$name];
        return $results;
    }

    /**
     * Get Count posts
     *
     * @return null|string
     */
    public static function getCountPost()
    {
        global $wpdb;
        $post_types = MetaSeoContentListTable::getPostTypes();
        foreach ($post_types as &$post_type) {
            $post_type = esc_sql($post_type);
        }
        $post_types = implode("', '", $post_types);
        $states     = get_post_stati(array('show_in_admin_all_list' => true));
        foreach ($states as &$state) {
            $state = esc_sql($state);
        }

        $all_states = implode("', '", $states);
        $where      = array();
        $where[]    = 'post_type IN (\'' . $post_types . '\')';
        $where[]    = 'post_status IN (\'' . $all_states . '\')';
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $total_posts = $wpdb->get_var('SELECT COUNT(ID) FROM ' . $wpdb->posts . ' WHERE ' . implode(' AND ', $where));
        return $total_posts;
    }

    /**
     * Get params meta title filled for dashboard
     *
     * @return array
     */
    public static function metaTitle()
    {
        global $wpdb;
        $total_posts = self::getCountPost();
        $results     = array(0, array(0, (int) $total_posts));

        $post_types = MetaSeoContentListTable::getPostTypes('attachment');
        foreach ($post_types as &$post_type) {
            $post_type = esc_sql($post_type);
        }
        $post_types = implode("', '", $post_types);

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        foreach ($states as &$state) {
            $state = esc_sql($state);
        }

        $all_states = implode("', '", $states);
        $where      = array();
        $where[]    = 'post_type IN (\'' . $post_types . '\')';
        $where[]    = 'post_status IN (\'' . $all_states . '\')';
        $query      = 'SELECT DISTINCT ID '
                      . ' FROM ' . $wpdb->posts . ' as p'
                      . ' LEFT JOIN (SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_metaseo_metatitle" AND meta_value != "") mt ON mt.post_id = p.ID ';
        $query      .= ' WHERE ' . implode(' AND ', $where);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $ps = $wpdb->get_results($query);

        $metatitle_filled = 0;
        if (!empty($ps)) {
            foreach ($ps as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis)) {
                    $meta_analysis = array();
                }

                $meta_title = get_post_meta($post->ID, '_metaseo_metatitle', true);
                if (($meta_title !== '')
                    || (!empty($meta_analysis) && !empty($meta_analysis['metatitle']))) {
                    $metatitle_filled ++;
                }
            }

            $results = array(
                ceil($metatitle_filled / $total_posts * 100),
                array($metatitle_filled, (int) $total_posts)
            );
        }

        return $results;
    }

    /**
     * Get html meta title filled for dashboard
     *
     * @return void
     */
    public static function dashboardMetaTitle()
    {
        $results = self::updateDashboard('metatitle');
        wp_send_json($results);
    }

    /**
     * Get params meta description filled for dashboard
     *
     * @return array
     */
    public static function metaDesc()
    {
        global $wpdb;
        $total_posts = self::getCountPost();
        $results     = array(0, array(0, $total_posts));

        $post_types = MetaSeoContentListTable::getPostTypes('attachment');
        foreach ($post_types as &$post_type) {
            $post_type = esc_sql($post_type);
        }
        $post_types = implode("', '", $post_types);

        $states = get_post_stati(array('show_in_admin_all_list' => true));
        foreach ($states as &$state) {
            $state = esc_sql($state);
        }
        $all_states = implode("', '", $states);

        $where   = array();
        $where[] = 'post_type IN (\'' . $post_types . '\')';
        $where[] = 'post_status IN (\'' . $all_states . '\')';
        $query   = 'SELECT DISTINCT ID '
                   . ' FROM ' . $wpdb->posts . ' as p'
                   . ' LEFT JOIN (SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_metaseo_metadesc" AND meta_value != "") mt ON mt.post_id = p.ID ';
        $query   .= ' WHERE ' . implode(' AND ', $where);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $wpms_posts      = $wpdb->get_results($query);
        $metadesc_filled = 0;
        if (!empty($wpms_posts)) {
            foreach ($wpms_posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis)) {
                    $meta_analysis = array();
                }

                $meta_desc = get_post_meta($post->ID, '_metaseo_metadesc', true);
                if (($meta_desc !== '')
                    || (!empty($meta_analysis) && !empty($meta_analysis['metadesc']))) {
                    $metadesc_filled ++;
                }
            }

            $results = array(ceil($metadesc_filled / $total_posts * 100), array($metadesc_filled, $total_posts));
        }

        return $results;
    }

    /**
     * Return html description filled for dashboard
     *
     * @return void
     */
    public static function dashboardMetaDesc()
    {
        $results = self::updateDashboard('metadesc');
        wp_send_json($results);
    }

    /**
     * Return link_meta for dashboard
     *
     * @return array
     */
    public static function linkMeta()
    {
        global $wpdb;
        $mlink_complete = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpms_links WHERE meta_title !="" AND type ="url"');
        $mcount_link    = $wpdb->get_var(
            'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpms_links WHERE type ="url"'
        );

        if ((int) $mcount_link === 0) {
            $link_percent = 100;
        } else {
            $link_percent = ceil($mlink_complete / $mcount_link * 100);
        }

        $results = array($link_percent, array($mlink_complete, $mcount_link));
        return $results;
    }

    /**
     * Return html link_meta for dashboard
     *
     * @return void
     */
    public static function dashboardLinkMeta()
    {
        $results = self::linkMeta();
        wp_send_json($results);
    }

    /**
     * Return permalink for dashboard
     *
     * @return integer
     */
    public static function permalink()
    {
        $permalink           = 50;
        $permalink_structure = get_option('permalink_structure');
        if (!strpos($permalink_structure, 'postname') && !strpos($permalink_structure, 'category')) {
            $permalink = 0;
        } elseif (strpos($permalink_structure, 'postname')
                  && strpos($permalink_structure, 'category')) {
            $permalink = 100;
        } elseif (strpos($permalink_structure, 'postname')
                  || strpos($permalink_structure, 'category')) {
            $permalink = 50;
        }
        return $permalink;
    }

    /**
     * Return html permalink for dashboard
     *
     * @return void
     */
    public static function dashboardPermalink()
    {
        $permalink = self::permalink();
        wp_send_json($permalink);
    }

    /**
     * Return count new content updated for dashboard
     *
     * @return array
     */
    public static function newContent()
    {
        $total_posts     = self::getCountPost();
        $newcontent_args = array(
            'date_query'     => array(
                array(
                    'column' => 'post_modified_gmt',
                    'after'  => '30 days ago'
                )
            ),
            'posts_per_page' => - 1,
            'post_type'      => array('post', 'page'),
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
     *
     * @return void
     */
    public static function dashboardNewContent()
    {
        $results = self::updateDashboard('newcontent');
        wp_send_json($results);
    }

    /**
     * Update time dashboard update
     *
     * @param string $name Option name
     *
     * @return void
     */
    public static function dashLastUpdate($name)
    {
        if ($name === 'metadesc') {
            update_option('_wpms_dash_last_update', time());
        }
    }

    /**
     * Update option dashboard
     *
     * @param array  $options_dashboard All criteria in dashboard
     * @param string $name              Option name
     *
     * @return void
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
                    $results = self::metaTitle();
                    break;
                case 'metadesc':
                    $results = self::metaDesc();
                    break;
                case 'newcontent':
                    $results = self::newContent();
                    break;
                case 'image_meta':
                    $results = self::moptimizationChecking();
                    break;
            }
            $options_dashboard[$name] = $results;
            update_option('options_dashboard', $options_dashboard);
            self::dashLastUpdate($name);
        }
    }

    /**
     * Return count link 404 , count link 404 is redirected , percent
     *
     * @return array
     */
    public static function get404Link()
    {
        global $wpdb;
        $count_404 = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpms_links WHERE (broken_internal = 1 OR broken_indexed = 1)');

        $count_404_redirected = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpms_links
             WHERE link_url_redirect != "" AND (broken_internal = 1 OR broken_indexed = 1)');
        if ((int) $count_404 !== 0) {
            $percent = ceil($count_404_redirected / $count_404 * 100);
        } else {
            $percent = 100;
        }
        return array('count_404' => $count_404, 'count_404_redirected' => $count_404_redirected, 'percent' => $percent);
    }

    /**
     * Get image metas
     *
     * @return void
     */
    public static function dashImgsMeta()
    {
        if (empty($_POST['wpms_nonce'])
            || !wp_verify_nonce($_POST['wpms_nonce'], 'wpms_nonce')) {
            die();
        }

        global $wpdb;
        $imgs                = 0;
        $imgs_are_good       = 0;
        $imgs_metas_are_good = array();
        $meta_keys           = array('alt');
        // create default value
        $response = array(
            'imgs_statis'       => array(0, 0, 100),
            'imgs_metas_statis' => array(0, 0, 100),
        );

        $options_dashboard       = get_option('options_dashboard');
        $option_last_update_post = get_option('wpms_last_update_post');
        $option_last_dash_update = get_option('_wpms_dash_last_update');
        // get response from options_dashboard option
        if (!empty($options_dashboard) && is_array($options_dashboard)
            && !empty($options_dashboard['image_meta']) && $option_last_update_post < $option_last_dash_update) {
            $results           = $options_dashboard['image_meta'];
            $results['status'] = true;
            wp_send_json($results);
        }

        // find img good and not good in post content to update
        foreach ($meta_keys as $meta_key) {
            $imgs_metas_are_good[$meta_key] = 0;
        }

        $limit      = 50;
        $offset     = ($_POST['page'] - 1) * $limit;
        $post_types = MetaSeoContentListTable::getPostTypes();
        foreach ($post_types as &$post_type) {
            $post_type = esc_sql($post_type);
        }
        $post_types = implode("', '", $post_types);
        $where      = array();
        $where[]    = 'post_type IN (\'' . $post_types . '\')';
        $where[]    = 'post_content <> "" AND post_content LIKE "%<img%>%"';

        // check count posts have image tag in content
        $all_posts = $wpdb->get_var('SELECT COUNT(ID) FROM ' . $wpdb->posts . ' WHERE post_content <> "" AND post_content LIKE "%<img%>%"');
        if (empty($all_posts)) {
            $options_dashboard['image_meta']  = array(
                'imgs_statis'       => array(0, 0, 100),
                'imgs_metas_statis' => array(0, 0, 100)
            );
            update_option('options_dashboard', $options_dashboard);
            self::dashLastUpdate('image_meta');
            $results           = $options_dashboard['image_meta'];
            $results['status'] = true;
            wp_send_json($results);
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $posts = $wpdb->get_results($wpdb->prepare('SELECT ID,post_content FROM ' . $wpdb->posts . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY ID LIMIT %d OFFSET %d', array(
            $limit,
            $offset
        )));
        if (count($posts) > 0) {
            $upload_dir = wp_upload_dir();

            foreach ($posts as $post) {
                $meta_analysis = get_post_meta($post->ID, 'wpms_validate_analysis', true);
                if (empty($meta_analysis)) {
                    $meta_analysis = array();
                }

                $img_tags = wpmsExtractTags($post->post_content, 'img', true, true);
                foreach ($img_tags as $tag) {
                    if (empty($tag['attributes']['src'])) {
                        continue;
                    }

                    $img_src = $tag['attributes']['src'];
                    if (!preg_match('/\.(jpg|png|gif)$/i', $img_src, $matches)) {
                        continue;
                    }

                    $img_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img_src);
                    if (!file_exists($img_path)) {
                        continue;
                    }

                    $width = false;
                    $height = false;
                    if (isset($tag['attributes']['width'])) {
                        $width = $tag['attributes']['width'];
                    }

                    if (isset($tag['attributes']['height'])) {
                        $height = $tag['attributes']['height'];
                    }

                    if (list($real_width, $real_height) = getimagesize($img_path)) {
                        $ratio_origin = $real_width / $real_height;
                        //Check if img tag is missing with/height attribute value or not
                        if (!$width && !$height) {
                            $width  = $real_width;
                            $height = $real_height;
                        } elseif ($width && !$height) {
                            $height = $width * (1 / $ratio_origin);
                        } elseif ($height && !$width) {
                            $width = $height * ($ratio_origin);
                        }

                        if (($real_width <= $width && $real_height <= $height)
                            || (!empty($meta_analysis) && !empty($meta_analysis['imgresize']))) {
                            $imgs_are_good ++;
                        }
                        foreach ($meta_keys as $meta_key) {
                            if (isset($tag['attributes'][$meta_key])
                                || (!empty($meta_analysis) && !empty($meta_analysis['imgalt']))) {
                                $imgs_metas_are_good[$meta_key] ++;
                            }
                        }
                        $imgs ++;
                    }
                }
            }

            $countImg = $imgs + (int) $_POST['imgs_count'];
            //Report analytic of images optimization
            $c_imgs_metas = $imgs_metas_are_good['alt'];
            // get results for image resize
            $response['imgs_statis'][0] = $imgs_are_good + (int) $_POST['imgs_statis'];
            // get results for image meta
            $response['imgs_metas_statis'][0] = $c_imgs_metas + (int) $_POST['imgs_metas_statis'];
            $response['imgs_statis'][1]       = $countImg;
            $response['imgs_metas_statis'][1] = $countImg;
            $response['imgs_count']           = $countImg;
            $response['page']                 = (int) $_POST['page'];
        } else {
            // run with last page
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
            $options_dashboard['image_meta']  = array(
                'imgs_statis'       => array($_POST['imgs_statis'], $_POST['imgs_count'], $percent_iresizing),
                'imgs_metas_statis' => array($_POST['imgs_metas_statis'], $_POST['imgs_count'], $percent_imeta)
            );

            // update options_dashboard option
            $option_last_update_post = get_option('wpms_last_update_post');
            $option_last_dash_update = get_option('_wpms_dash_last_update');
            if ((!empty($option_last_update_post) && $option_last_update_post > $option_last_dash_update)
                || empty($option_last_update_post)) {
                update_option('options_dashboard', $options_dashboard);
                self::dashLastUpdate('image_meta');
            }

            $results           = $options_dashboard['image_meta'];
            $results['status'] = true;
            wp_send_json($results);
        }

        wp_send_json($response);
    }

    /**
     * Connect with webpage test api
     *
     * @param string  $page     URL of page check
     * @param string  $key      Key of webpage test api
     * @param boolean $run_time Run time
     * @param boolean $type     Type check
     *
     * @return string
     */
    public static function getTestId($page, $key, $run_time = false, $type = false)
    {
        $idTest = wpmsGetOption('webpage_testid');
        if (!empty($idTest)) {
            return $idTest;
        }

        $testID = '';
        if (!$type) {
            $type = 'xml';
        }
        if (!$run_time) {
            $run_time = 1;
        }
        $runTest  = 'http://www.webpagetest.org/runtest.php?url=' .
                    $page . '&runs=' . $run_time . '&f=' . $type . '&k=' . $key;
        $response = wp_remote_get($runTest);
        if (is_array($response)) {
            $xmlres = simplexml_load_string($response['body']);
            if ($xmlres) {
                if ((string) $xmlres->statusText === 'Ok') {
                    $testID = (string) $xmlres->data->testId;
                }

                if ((string) $xmlres->statusText === 'Invalid API Key') {
                    $testID = 'Invalid API Key';
                }
            }
        }

        return $testID;
    }

    /**
     * Get response from URL
     *
     * @param string $url URL
     *
     * @return SimpleXMLElement|boolean
     */
    public static function sxe($url)
    {
        // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Fix warning in some case
        $xml = @file_get_contents($url);
        if (empty($xml)) {
            return false;
        }
        foreach ($http_response_header as $header) {
            if (preg_match('#^Content-Type: text/xml; charset=(.*)#i', $header, $m)) {
                switch (strtolower($m[1])) {
                    case 'utf-8':
                        // do nothing
                        break;

                    case 'iso-8859-1':
                        $xml = utf8_encode($xml);
                        break;

                    default:
                        $xml = iconv($m[1], 'utf-8', $xml);
                }
                break;
            }
        }

        return simplexml_load_string($xml);
    }

    /**
     * Get screenshot website
     *
     * @return string|array
     */
    public static function thumbalizr()
    {
        $url = home_url();
        $idTest   = self::getTestId($url, 'A.8fbb3da31a268442d7636c6510558702');
        if (empty($idTest) || $idTest === 'Invalid API Key') {
            return 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png';
        }
        wpmsSetOption('webpage_testid', $idTest);
        $urlTest  = 'http://www.webpagetest.org/xmlResult/' . $idTest . '/';
        $xmlResult = self::sxe($urlTest);
        if (!$xmlResult) {
            return 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png';
        }

        $status   = '';
        if ($xmlResult !== false) {
            $status = (int) $xmlResult->statusCode;
        }

        if ($status < 200) {
            return array('status' => false, 'statusCode' => $status);
        } elseif ($status === 200) {
            wpmsSetOption('webpage_testid', 0);
            return (string) $xmlResult->data->run[0]->firstView->images->screenShot;
        }

        return 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png';
    }

    /**
     * Reload web
     *
     * @return void
     */
    public static function reloadWeb()
    {
        $upload_dir   = wp_upload_dir();
        $server_check = parse_url(home_url());
        if (isset($server_check['host']) && $server_check['host'] === 'localhost') {
            $link = 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png';
        } else {
            $response = self::thumbalizr();
            if (is_array($response)) {
                wp_send_json($response);
            }
            // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Fix warning in some case
            $content = @file_get_contents($response);
            if ($content) {
                file_put_contents($upload_dir['basedir'] . '/wpms-web-screenshot.jpg', $content);
                $link = $upload_dir['baseurl'] . '/wpms-web-screenshot.jpg';
            } else {
                $link = 'https://ps.w.org/wp-meta-seo/assets/banner-772x250.png';
            }
        }

        wp_send_json(array('status' => true, 'link' => $link));
    }
}
