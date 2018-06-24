<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Modified by Joomunited
 */

// Exit if accessed directly

/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

if (!class_exists('WpmsGapiController')) {
    require_once(WPMETASEO_PLUGIN_DIR . 'inc/class.metaseo-admin.php');

    /**
     * Class WpmsGapiController
     */
    class WpmsGapiController extends MetaSeoAdmin
    {
        /**
         * @var Google_Service_Analytics
         */
        public $service;
        /**
         * @var
         */
        public $timeshift;
        /**
         * @var string
         */
        private $managequota;
        /**
         * @var null|WpmsGaManager
         */
        private $wpmsga;

        /**
         * WpmsGapiController constructor.
         */
        public function __construct()
        {
            parent::__construct();
            $google_alanytics = get_option('wpms_google_alanytics');
            $this->wpmsga = WPMSGA();
            include_once(WPMETASEO_PLUGIN_DIR . 'inc/autoload.php');
            $config = new Google_Config();
            $config->setCacheClass('Google_Cache_Null');
            if (function_exists('curl_version')) {
                $curlversion = curl_version();
                if (isset($curlversion['version']) && (version_compare(PHP_VERSION, '5.3.0') >= 0)
                    && version_compare($curlversion['version'], '7.10.8') >= 0 && defined('GADWP_IP_VERSION')
                    && GADWP_IP_VERSION) {
                    $config->setClassConfig(
                        'Google_IO_Curl',
                        array(
                            'options' => array(
                                CURLOPT_IPRESOLVE => GADWP_IP_VERSION
                            )
                        )
                    ); // Force CURL_IPRESOLVE_V4 or CURL_IPRESOLVE_V6
                }
            }

            $this->client = new Google_Client($config);
            $this->client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
            $this->client->setAccessType('offline');
            $this->client->setApplicationName('WP Meta SEO');
            $this->client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            $this->setErrorTimeout();
            $this->managequota = 'u' . get_current_user_id() . 's' . get_current_blog_id();
            $this->client = WpmsGaTools::setClient($this->client, $google_alanytics, $this->access);
            $this->service = new Google_Service_Analytics($this->client);
            if (!empty($google_alanytics['googleCredentials'])) {
                $token = $google_alanytics['googleCredentials'];
                if ($token) {
                    try {
                        $this->client->setAccessToken($token);
                    } catch (Google_IO_Exception $e) {
                        WpmsGaTools::setCache(
                            'wpmsga_dash_lasterror',
                            date('Y-m-d H:i:s') . ': ' . esc_html($e),
                            $this->error_timeout
                        );
                    } catch (Google_Service_Exception $e) {
                        WpmsGaTools::setCache(
                            'wpmsga_dash_lasterror',
                            date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()),
                            $this->error_timeout
                        );
                        WpmsGaTools::setCache(
                            'wpmsga_dash_gapi_errors',
                            $e->getCode(),
                            $this->error_timeout
                        );
                        $this->resetToken();
                    } catch (Exception $e) {
                        WpmsGaTools::setCache(
                            'wpmsga_dash_lasterror',
                            date('Y-m-d H:i:s') . ': ' . esc_html($e),
                            $this->error_timeout
                        );
                        $this->resetToken();
                    }
                }
            }
        }

        /**
         * Handles errors returned by GAPI Library
         *
         * @return boolean
         */
        public function gapiErrorsHandler()
        {
            $errors = WpmsGaTools::getCache('gapi_errors');
            if ($errors === false || !isset($errors[0])) { // invalid error
                return false;
            }
            if (isset($errors[1][0]['reason'])
                && ($errors[1][0]['reason'] == 'invalidCredentials'
                    || $errors[1][0]['reason'] == 'authError'
                    || $errors[1][0]['reason'] == 'insufficientPermissions'
                    || $errors[1][0]['reason'] == 'required'
                    || $errors[1][0]['reason'] == 'keyExpired')) {
                $this->resetToken(false);
                return true;
            }
            if (isset($errors[1][0]['reason'])
                && ($errors[1][0]['reason'] == 'userRateLimitExceeded'
                    || $errors[1][0]['reason'] == 'quotaExceeded')) {
                if ($this->wpmsga->config->options['api_backoff'] <= 5) {
                    usleep(rand(100000, 1500000));
                    return false;
                } else {
                    return true;
                }
            }
            if ($errors[0] == 400 || $errors[0] == 401 || $errors[0] == 403) {
                return true;
            }
            return false;
        }

        /**
         * Calculates proper timeouts for each GAPI query
         *
         * @param
         *            $daily
         * @return number
         */
        public function getTimeouts($daily)
        {
            $local_time = time() + $this->timeshift;
            if ($daily) {
                $nextday = explode('-', date('n-j-Y', strtotime(' +1 day', $local_time)));
                $midnight = mktime(0, 0, 0, $nextday[0], $nextday[1], $nextday[2]);
                return $midnight - $local_time;
            } else {
                $nexthour = explode('-', date('H-n-j-Y', strtotime(' +1 hour', $local_time)));
                $newhour = mktime($nexthour[0], 0, 0, $nexthour[1], $nexthour[2], $nexthour[3]);
                return $newhour - $local_time;
            }
        }

        /**
         * Handles the token reset process
         * @param bool $all
         */
        public function resetToken($all = true)
        {
            update_option('wpms_google_alanytics', array());
        }

        /**
         * Get and cache Core Reports
         * @param $projectId
         * @param $from
         * @param $to
         * @param $metrics
         * @param $options
         * @param $serial
         * @return bool|Google_Service_Analytics_GaData|int|mixed
         */
        private function handleCorereports($projectId, $from, $to, $metrics, $options, $serial)
        {
            try {
                if ($from == "today") {
                    $timeouts = 0;
                } else {
                    $timeouts = 1;
                }
                $transient = WpmsGaTools::getCache($serial);
                if ($transient === false) {
                    if ($this->gapiErrorsHandler()) {
                        return -23;
                    }
                    $data = $this->service->data_ga->get('ga:' . $projectId, $from, $to, $metrics, $options);
                    WpmsGaTools::setCache($serial, $data, $this->getTimeouts($timeouts));
                } else {
                    $data = $transient;
                }
            } catch (Google_Service_Exception $e) {
                WpmsGaTools::setCache(
                    'last_error',
                    date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()),
                    $this->error_timeout
                );
                WpmsGaTools::setCache(
                    'gapi_errors',
                    $e->getCode(),
                    $this->error_timeout
                );
                return $e->getCode();
            } catch (Exception $e) {
                WpmsGaTools::setCache('last_error', date('Y-m-d H:i:s') . ': ' . esc_html($e), $this->error_timeout);
                return $e->getCode();
            }
            if ($data->getRows() > 0) {
                return $data;
            } else {
                return -21;
            }
        }

        /**
         * Generates serials for transients
         *
         * @param
         *            $serial
         * @return string
         */
        public function getSerial($serial)
        {
            return sprintf("%u", crc32($serial));
        }

        /**
         * Analytics data for Area Charts (Admin Dashboard Widget report)
         * @param $projectId
         * @param $from
         * @param $to
         * @param $query
         * @param string $filter
         * @return array|int|string
         */
        private function getAreachartData($projectId, $from, $to, $query, $filter = '')
        {
            switch ($query) {
                case 'users':
                    $title = __("Users", 'wp-meta-seo');
                    break;
                case 'pageviews':
                    $title = __("Page Views", 'wp-meta-seo');
                    break;
                case 'visitBounceRate':
                    $title = __("Bounce Rate", 'wp-meta-seo');
                    break;
                case 'organicSearches':
                    $title = __("Organic Searches", 'wp-meta-seo');
                    break;
                case 'uniquePageviews':
                    $title = __("Unique Page Views", 'wp-meta-seo');
                    break;
                default:
                    $title = __("Sessions", 'wp-meta-seo');
            }
            $metrics = 'ga:' . $query;
            if ($from == "today" || $from == "yesterday") {
                $dimensions = 'ga:hour';
                $dayorhour = __("Hour", 'wp-meta-seo');
            } elseif ($from == "365daysAgo" || $from == "1095daysAgo") {
                $dimensions = 'ga:yearMonth, ga:month';
                $dayorhour = __("Date", 'wp-meta-seo');
            } else {
                $dimensions = 'ga:date,ga:dayOfWeekName';
                $dayorhour = __("Date", 'wp-meta-seo');
            }
            $options = array('dimensions' => $dimensions, 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
            }
            $serial = 'qr2_' . $this->getSerial($projectId . $from . $metrics . $filter);
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array($dayorhour, $title));
            if ($from == "today" || $from == "yesterday") {
                foreach ($data->getRows() as $row) {
                    $wpmsga_data[] = array((int)$row[0] . ':00', round($row[1], 2));
                }
            } elseif ($from == "365daysAgo" || $from == "1095daysAgo") {
                foreach ($data->getRows() as $row) {
                    /*
                     * translators:
                     * Example: 'F, Y' will become 'November, 2015'
                     * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
                     */
                    $wpmsga_data[] = array(
                        date_i18n(__('F, Y', 'wp-meta-seo'), strtotime($row[0] . '01')),
                        round($row[2], 2)
                    );
                }
            } else {
                foreach ($data->getRows() as $row) {
                    /*
                     * translators:
                     * Example: 'l, F j, Y' will become 'Thusday, November 17, 2015'
                     * For details see: http://php.net/manual/en/function.date.php#refsect1-function.date-parameters
                     */
                    $wpmsga_data[] = array(
                        date_i18n(__('l, F j, Y', 'wp-meta-seo'), strtotime($row[0])),
                        round($row[2], 2)
                    );
                }
            }

            return $wpmsga_data;
        }

        /**
         * Analytics data for Bottom Stats (bottom stats on main report)
         * @param $projectId
         * @param $from
         * @param $to
         * @param string $filter
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        private function getNottomstats($projectId, $from, $to, $filter = '')
        {
            $options = array('dimensions' => null, 'quotaUser' => $this->managequota . 'p' . $projectId);
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
                $metrics = 'ga:uniquePageviews,ga:users,ga:pageviews,ga:BounceRate,ga:organicSearches,ga:pageviewsPerSession';
            } else {
                $metrics = 'ga:sessions,ga:users,ga:pageviews,ga:BounceRate,ga:organicSearches,ga:pageviewsPerSession';
            }
            $serial = 'qr3_' . $this->getSerial($projectId . $from . $filter);
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                if ($data == -21) {
                    return array_fill(0, 6, 0);
                } else {
                    return $data;
                }
            }
            $wpmsga_data = array();
            foreach ($data->getRows() as $row) {
                $wpmsga_data = array_map('floatval', $row);
            }

            // i18n support
            $wpmsga_data[0] = number_format_i18n($wpmsga_data[0]);
            $wpmsga_data[1] = number_format_i18n($wpmsga_data[1]);
            $wpmsga_data[2] = number_format_i18n($wpmsga_data[2]);
            $wpmsga_data[3] = number_format_i18n($wpmsga_data[3], 2);
            $wpmsga_data[4] = number_format_i18n($wpmsga_data[4]);
            $wpmsga_data[5] = number_format_i18n($wpmsga_data[5], 2);

            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts & Table Charts (content pages)
         * @param $projectId
         * @param $from
         * @param $to
         * @param string $filter
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        private function getContentPages($projectId, $from, $to, $filter = '')
        {
            $metrics = 'ga:pageviews';
            $dimensions = 'ga:pageTitle';
            $options = array(
                'dimensions' => $dimensions,
                'sort' => '-ga:pageviews',
                'quotaUser' => $this->managequota . 'p' . $projectId
            );
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
            }
            $serial = 'qr4_' . $this->getSerial($projectId . $from . $filter);
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array(__("Pages", 'wp-meta-seo'), __("Views", 'wp-meta-seo')));
            foreach ($data->getRows() as $row) {
                $wpmsga_data[] = array(esc_html($row[0]), (int)$row[1]);
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts & Table Charts (referrers)
         * @param $projectId
         * @param $from
         * @param $to
         * @param string $filter
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        private function getReferrers($projectId, $from, $to, $filter = '')
        {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:source';
            $options = array(
                'dimensions' => $dimensions,
                'sort' => '-ga:sessions',
                'quotaUser' => $this->managequota . 'p' . $projectId
            );
            if ($filter) {
                $options['filters'] = 'ga:medium==referral;ga:pagePath==' . $filter;
            } else {
                $options['filters'] = 'ga:medium==referral';
            }
            $serial = 'qr5_' . $this->getSerial($projectId . $from . $filter);
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array(__("Referrers", 'wp-meta-seo'), __("Sessions", 'wp-meta-seo')));
            foreach ($data->getRows() as $row) {
                $wpmsga_data[] = array(esc_html($row[0]), (int)$row[1]);
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts & Table Charts (searches)
         * @param $projectId
         * @param $from
         * @param $to
         * @param string $filter
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        private function getSearches($projectId, $from, $to, $filter = '')
        {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:keyword';
            $options = array(
                'dimensions' => $dimensions,
                'sort' => '-ga:sessions',
                'quotaUser' => $this->managequota . 'p' . $projectId
            );
            if ($filter) {
                $options['filters'] = 'ga:keyword!=(not set);ga:pagePath==' . $filter;
            } else {
                $options['filters'] = 'ga:keyword!=(not set)';
            }
            $serial = 'qr6_' . $this->getSerial($projectId . $from . $filter);
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }

            $wpmsga_data = array(array(__("Searches", 'wp-meta-seo'), __("Sessions", 'wp-meta-seo')));
            foreach ($data->getRows() as $row) {
                $wpmsga_data[] = array(esc_html($row[0]), (int)$row[1]);
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts & Table Charts (location reports)
         * @param $projectId
         * @param $from
         * @param $to
         * @param string $filter
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        private function getLocations($projectId, $from, $to, $filter = '')
        {
            $metrics = 'ga:sessions';
            $title = __("Countries", 'wp-meta-seo');
            $serial = 'qr7_' . $this->getSerial($projectId . $from . $filter);
            $dimensions = 'ga:country';
            $local_filter = '';
            $options = array(
                'dimensions' => $dimensions,
                'sort' => '-ga:sessions',
                'quotaUser' => $this->managequota . 'p' . $projectId
            );
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
                if ($local_filter) {
                    $options['filters'] .= ';' . $local_filter;
                }
            } else {
                if ($local_filter) {
                    $options['filters'] = $local_filter;
                }
            }
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array($title, __("Sessions", 'wp-meta-seo')));
            foreach ($data->getRows() as $row) {
                if (isset($row[2])) {
                    $wpmsga_data[] = array(esc_html($row[0]) . ', ' . esc_html($row[1]), (int)$row[2]);
                } else {
                    $wpmsga_data[] = array(esc_html($row[0]), (int)$row[1]);
                }
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Org Charts (traffic channels, device categories)
         * @param $projectId
         * @param $from
         * @param $to
         * @param $query
         * @param string $filter
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        private function getOrgchartData($projectId, $from, $to, $query, $filter = '')
        {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:' . $query;
            $options = array(
                'dimensions' => $dimensions,
                'sort' => '-ga:sessions',
                'quotaUser' => $this->managequota . 'p' . $projectId
            );
            if ($filter) {
                $options['filters'] = 'ga:pagePath==' . $filter;
            }
            $serial = 'qr8_' . $this->getSerial($projectId . $from . $query . $filter);
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $block = ($query == 'channelGrouping') ? __("Channels", 'wp-meta-seo') : __("Devices", 'wp-meta-seo');
            $wpmsga_data = array(
                array(
                    '<div style="color:black; font-size:1.1em">' . $block . '</div>
<div style="color:darkblue; font-size:1.2em">' . (int)$data['totalsForAllResults']["ga:sessions"] . '</div>',
                    ""
                )
            );
            foreach ($data->getRows() as $row) {
                $shrink = explode(" ", $row[0]);
                $wpmsga_data[] = array(
                    '<div style="color:black; font-size:1.1em">' . esc_html($shrink[0]) . '</div>
<div style="color:darkblue; font-size:1.2em">' . (int)$row[1] . '</div>',
                    '<div style="color:black; font-size:1.1em">' . $block . '</div>
<div style="color:darkblue; font-size:1.2em">' . (int)$data['totalsForAllResults']["ga:sessions"] . '</div>'
                );
            }
            return $wpmsga_data;
        }

        /**
         * Analytics data for Pie Charts (traffic mediums,
         * serach engines, social networks, browsers, screen rsolutions, etc.)
         * @param $projectId
         * @param $from
         * @param $to
         * @param $query
         * @param string $filter
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        private function getPiechartData($projectId, $from, $to, $query, $filter = '')
        {
            $metrics = 'ga:sessions';
            $dimensions = 'ga:' . $query;

            if ($query == 'source') {
                $options = array(
                    'dimensions' => $dimensions,
                    'sort' => '-ga:sessions',
                    'quotaUser' => $this->managequota . 'p' . $projectId
                );
                if ($filter) {
                    $options['filters'] = 'ga:medium==organic;ga:keyword!=(not set);ga:pagePath==' . $filter;
                } else {
                    $options['filters'] = 'ga:medium==organic;ga:keyword!=(not set)';
                }
            } else {
                $options = array(
                    'dimensions' => $dimensions,
                    'sort' => '-ga:sessions',
                    'quotaUser' => $this->managequota . 'p' . $projectId
                );
                if ($filter) {
                    $options['filters'] = 'ga:' . $query . '!=(not set);ga:pagePath==' . $filter;
                } else {
                    $options['filters'] = 'ga:' . $query . '!=(not set)';
                }
            }
            $serial = 'qr10_' . $this->getSerial($projectId . $from . $query . $filter);
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array(__("Type", 'wp-meta-seo'), __("Sessions", 'wp-meta-seo')));
            $i = 0;
            $included = 0;
            foreach ($data->getRows() as $row) {
                if ($i < 20) {
                    $wpmsga_data[] = array(str_replace("(none)", "direct", esc_html($row[0])), (int)$row[1]);
                    $included += $row[1];
                    $i++;
                } else {
                    break;
                }
            }
            $totals = $data->getTotalsForAllResults();
            $others = $totals['ga:sessions'] - $included;
            if ($others > 0) {
                $wpmsga_data[] = array(__('Other', 'wp-meta-seo'), $others);
            }

            return $wpmsga_data;
        }

        /**
         * Analytics data for Frontend Widget (chart data and totals)
         * @param $projectId
         * @param $from
         * @param $anonim
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        public function frontendWidgetStats($projectId, $from, $anonim)
        {
            $content = '';
            $to = 'yesterday';
            $metrics = 'ga:sessions';
            $dimensions = 'ga:date,ga:dayOfWeekName';
            $options = array('dimensions' => $dimensions, 'quotaUser' => $this->managequota . 'p' . $projectId);
            $serial = 'qr2_' . $this->getSerial($projectId . $from . $metrics);
            $data = $this->handleCorereports($projectId, $from, $to, $metrics, $options, $serial);
            if (is_numeric($data)) {
                return $data;
            }
            $wpmsga_data = array(array(__("Date", 'wp-meta-seo'), __("Sessions", 'wp-meta-seo')));
            $max = 1;
            if ($anonim) {
                $max_array = array();
                foreach ($data->getRows() as $item) {
                    $max_array[] = $item[2];
                }
                $max = max($max_array) ? max($max_array) : 1;
            }
            foreach ($data->getRows() as $row) {
                $wpmsga_data[] = array(
                    date_i18n(__('l, F j, Y', 'wp-meta-seo'), strtotime($row[0])),
                    ($anonim ? round($row[2] * 100 / $max, 2) : (int)$row[2])
                );
            }
            $totals = $data->getTotalsForAllResults();
            return array($wpmsga_data, $anonim ? 0 : number_format_i18n($totals['ga:sessions']));
        }

        /**
         * Analytics data for Realtime component (the real-time report)
         * @param $projectId
         * @return array|int|mixed
         */
        private function getRealtime($projectId)
        {
            $metrics = 'rt:activeUsers';
            $dimensions = 'rt:pagePath,rt:source,rt:keyword,rt:trafficType,rt:visitorType,rt:pageTitle';
            try {
                $serial = 'qr_realtimecache_' . $this->getSerial($projectId);
                $transient = WpmsGaTools::getCache($serial);
                if ($transient === false) {
                    if ($this->gapiErrorsHandler()) {
                        return -23;
                    }
                    $data = $this->service->data_realtime->get(
                        'ga:' . $projectId,
                        $metrics,
                        array(
                            'dimensions' => $dimensions,
                            'quotaUser' => $this->managequota . 'p' . $projectId
                        )
                    );
                    WpmsGaTools::setCache($serial, $data, 55);
                } else {
                    $data = $transient;
                }
            } catch (Google_Service_Exception $e) {
                WpmsGaTools::setCache(
                    'last_error',
                    date('Y-m-d H:i:s') . ': ' . esc_html("(" . $e->getCode() . ") " . $e->getMessage()),
                    $this->error_timeout
                );
                WpmsGaTools::setCache(
                    'gapi_errors',
                    $e->getCode(),
                    $this->error_timeout
                );
                return $e->getCode();
            } catch (Exception $e) {
                WpmsGaTools::setCache(
                    'last_error',
                    date('Y-m-d H:i:s') . ': ' . esc_html($e),
                    $this->error_timeout
                );
                return $e->getCode();
            }
            if ($data->getRows() < 1) {
                return -21;
            }
            $i = 0;
            $wpmsga_data = $data;
            foreach ($data->getRows() as $row) {
                $wpmsga_data->rows[$i] = array_map('esc_html', $row);
                $i++;
            }
            return array($wpmsga_data);
        }

        /**
         * Handles ajax requests and calls the needed methods
         * @param $projectId
         * @param $query
         * @param bool $from
         * @param bool $to
         * @param string $filter
         * @return array|bool|Google_Service_Analytics_GaData|int|mixed
         */
        public function get($projectId, $query, $from = false, $to = false, $filter = '')
        {
            if (empty($projectId) || !is_numeric($projectId)) {
                wp_die(-26);
            }

            $groups = array(
                'sessions',
                'users',
                'organicSearches',
                'visitBounceRate',
                'pageviews',
                'uniquePageviews'
            );
            if (in_array($query, $groups)) {
                return $this->getAreachartData($projectId, $from, $to, $query, $filter);
            }
            if ($query == 'bottomstats') {
                return $this->getNottomstats($projectId, $from, $to, $filter);
            }
            if ($query == 'locations') {
                return $this->getLocations($projectId, $from, $to, $filter);
            }
            if ($query == 'referrers') {
                return $this->getReferrers($projectId, $from, $to, $filter);
            }
            if ($query == 'contentpages') {
                return $this->getContentPages($projectId, $from, $to, $filter);
            }
            if ($query == 'searches') {
                return $this->getSearches($projectId, $from, $to, $filter);
            }
            if ($query == 'realtime') {
                return $this->getRealtime($projectId);
            }
            if ($query == 'channelGrouping' || $query == 'deviceCategory') {
                return $this->getOrgchartData($projectId, $from, $to, $query, $filter);
            }

            $arrs = array(
                'medium',
                'visitorType',
                'socialNetwork',
                'source',
                'browser',
                'operatingSystem',
                'screenResolution',
                'mobileDeviceBranding'
            );
            if (in_array($query, $arrs)) {
                return $this->getPiechartData($projectId, $from, $to, $query, $filter);
            }
            wp_die(-27);
        }
    }
}
