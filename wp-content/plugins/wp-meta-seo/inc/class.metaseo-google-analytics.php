<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class MetaSeoGoogleAnalytics
 * Base class for displaying your google analytics.
 */
class MetaSeoGoogleAnalytics
{
    /**
     * ajax display google analytics
     */
    public static function itemsReport()
    {
        include_once(WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmstools.php');
        include_once(WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmsgapi.php');
        $google_alanytics = get_option('wpms_google_alanytics');

        if (!isset($_POST['wpms_security_backend_item_reports'])
            || !wp_verify_nonce($_POST['wpms_security_backend_item_reports'], 'wpms_backend_item_reports')) {
            wp_die(-30);
        }

        if (isset($_POST['projectId']) && $_POST['projectId'] !== 'false') {
            $projectId = $_POST['projectId'];
        } else {
            $projectId = false;
        }
        $from = $_POST['from'];
        $to = $_POST['to'];
        $query = $_POST['query'];
        if (isset($_POST['filter'])) {
            $filter_id = $_POST['filter'];
        } else {
            $filter_id = false;
        }
        if (ob_get_length()) {
            ob_clean();
        }

        if (!empty($google_alanytics['tableid_jail'])) {
            if (empty($controller)) {
                $controller = new WpmsGapiController();
            }
        } else {
            wp_die(-99);
        }

        if (!empty($google_alanytics['googleCredentials']) && !empty($google_alanytics['tableid_jail'])
            && isset($from) && isset($to)) {
            if (empty($controller)) {
                $controller = new WpmsGapiController();
            }
        } else {
            wp_die(-24);
        }

        if ($projectId == false) {
            $projectId = $google_alanytics['tableid_jail'];
        }
        $profile_info = WpmsGaTools::getSelectedProfile($google_alanytics['profile_list'], $projectId);
        if (isset($profile_info[4])) {
            $controller->timeshift = $profile_info[4];
        } else {
            $controller->timeshift = (int)current_time('timestamp') - time();
        }

        $filter = false;
        if ($filter_id) {
            $uri_parts = explode('/', get_permalink($filter_id), 4);

            if (isset($uri_parts[3])) {
                $uri = '/' . $uri_parts[3];
                // allow URL correction before sending an API request
                $filter = apply_filters('wpmsga_backenditem_uri', $uri);
                $lastchar = substr($filter, -1);

                if (isset($profile_info[6]) && $profile_info[6] && $lastchar == '/') {
                    $filter = $filter . $profile_info[6];
                }

                // Encode URL
                $filter = rawurlencode(rawurldecode($filter));
            } else {
                wp_die(-25);
            }
        }

        $queries = explode(',', $query);
        $results = array();
        foreach ($queries as $value) {
            $results[] = $controller->get($projectId, $value, $from, $to, $filter);
        }

        wp_send_json($results);
    }

    /**
     * Update analytics option
     */
    public static function updateOption()
    {
        $options = get_option('wpms_google_alanytics');
        if (isset($_POST['userapi'])) {
            $options['wpmsga_dash_userapi'] = $_POST['userapi'];
            update_option('wpms_google_alanytics', $options);
        }
        wp_send_json(true);
    }

    /**
     * ajax clear author
     */
    public static function clearAuthor()
    {
        delete_option('wpms_google_alanytics');
        wp_send_json(true);
    }

    /**
     * @param $map
     * @return mixed|string
     */
    public static function map($map)
    {
        $map = explode('.', $map);
        if (isset($map[1])) {
            $map[0] += ord('map');
            return implode('.', $map);
        } else {
            return str_ireplace('map', chr(112), $map[0]);
        }
    }
}
