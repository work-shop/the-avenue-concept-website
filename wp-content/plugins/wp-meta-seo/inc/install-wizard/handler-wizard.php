<?php
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Class WpmsHandlerWizard
 */
class WpmsHandlerWizard
{
    /**
     * WpmsHandlerWizard constructor.
     */
    public function __construct()
    {
    }

    /**
     * Save Environment handle
     *
     * @param string $current_step Current step
     *
     * @return void
     */
    public static function saveEvironment($current_step)
    {
        check_admin_referer('wpms-setup-wizard', 'wizard_nonce');
        /*
         * Do no thing
         */
        $wizard = new WpmsInstallWizard();
        wp_safe_redirect(esc_url_raw($wizard->getNextLink($current_step)));
        exit;
    }

    /**
     * Save social
     *
     * @param string $current_step Current step
     *
     * @return void
     */
    public static function saveSocial($current_step)
    {
        check_admin_referer('wpms-setup-wizard', 'wizard_nonce');

        $options = array(
            'metaseo_showfacebook' => '',
            'metaseo_showfbappid'  => '',
            'metaseo_showtwitter'  => '',
            'metaseo_twitter_card' => 'summary'
        );

        foreach ($options as $name => $value) {
            if (isset($_POST[$name])) {
                wpmsSetOption($name, $_POST[$name]);
            }
        }
        $wizard = new WpmsInstallWizard();
        wp_safe_redirect(esc_url_raw($wizard->getNextLink($current_step)));
        exit;
    }

    /**
     * Save home meta
     *
     * @param string $current_step Current step
     *
     * @return void
     */
    public static function saveMetaInfos($current_step)
    {
        check_admin_referer('wpms-setup-wizard', 'wizard_nonce');
        $options = array(
            'home_meta_active'       => 1,
            'metaseo_showtmetablock' => 1,
            'metaseo_title_home'     => '',
            'metaseo_desc_home'      => ''
        );

        foreach ($options as $name => $value) {
            if (isset($_POST[$name])) {
                wpmsSetOption($name, $_POST[$name]);
            }
        }
        $wizard = new WpmsInstallWizard();
        wp_safe_redirect(esc_url_raw($wizard->getNextLink($current_step)));
        exit;
    }

    /**
     * Save Google Analytics
     *
     * @param string $current_step Current step
     *
     * @return void
     */
    public static function saveGoogleAnalytics($current_step)
    {
        check_admin_referer('wpms-setup-wizard', 'wizard_nonce');
        if (!empty($_POST['wpms_ga_code'])) {
            $wpms_ga_code  = $_POST['wpms_ga_code'];
            $midnight      = strtotime('tomorrow 00:00:00'); // UTC midnight
            $midnight      = $midnight + 8 * 3600; // UTC 8 AM
            $error_timeout = $midnight - time();

            require_once WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmstools.php';
            require_once WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmsgapi.php';
            require_once WPMETASEO_PLUGIN_DIR . 'inc/autoload.php';
            $config = new Google_Config();
            $config->setCacheClass('Google_Cache_Null');
            $client = new Google_Client($config);
            $client->setScopes('https://www.googleapis.com/auth/analytics.readonly');
            $client->setAccessType('offline');
            $client->setApplicationName('WP Meta SEO');
            $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            $client           = WpmsGaTools::setClient($client, array(), array(WPMS_CLIENTID, WPMS_CLIENTSECRET));
            $service          = new Google_Service_Analytics($client);
            $google_alanytics = array();
            if (!stripos('x' . $wpms_ga_code, 'UA-', 1)) {
                WpmsGaTools::deleteCache('gapi_errors');
                WpmsGaTools::deleteCache('last_error');
                WpmsGaTools::clearCache();
                try {
                    $client->authenticate($wpms_ga_code);
                    $getAccessToken = $client->getAccessToken();
                    if ($getAccessToken) {
                        try {
                            $client->setAccessToken($getAccessToken);
                            $google_alanytics['googleCredentials']
                                = $client->getAccessToken();
                        } catch (Google_IO_Exception $e) {
                            WpmsGaTools::setCache(
                                'wpmsga_dash_lasterror',
                                date('Y-m-d H:i:s') . ': ' . esc_html($e),
                                $error_timeout
                            );
                        } catch (Google_Service_Exception $e) {
                            WpmsGaTools::setCache(
                                'wpmsga_dash_lasterror',
                                date('Y-m-d H:i:s') . ': ' . esc_html('(' . $e->getCode() . ') ' . $e->getMessage()),
                                $error_timeout
                            );
                            WpmsGaTools::setCache(
                                'wpmsga_dash_gapi_errors',
                                $e->getCode(),
                                $error_timeout
                            );
                        } catch (Exception $e) {
                            WpmsGaTools::setCache(
                                'wpmsga_dash_lasterror',
                                date('Y-m-d H:i:s') . ': ' . esc_html($e),
                                $error_timeout
                            );
                        }
                    }

                    if (!empty($google_alanytics['profile_list'])) {
                        $profiles = $google_alanytics['profile_list'];
                    } else {
                        $profiles = self::refreshProfiles($service, $error_timeout);
                    }

                    $google_alanytics['code']              = $wpms_ga_code;
                    $google_alanytics['googleCredentials'] = $getAccessToken;
                    $google_alanytics['profile_list']      = $profiles;
                    update_option('wpms_google_alanytics', $google_alanytics);
                } catch (Google_IO_Exception $e) {
                    echo '';
                } catch (Google_Service_Exception $e) {
                    echo '';
                } catch (Exception $e) {
                    echo '';
                }
            } else {
                echo '<div class="error"><p>' . esc_html__('The access code is 
<strong>NOT</strong> your <strong>Tracking ID</strong>
 (UA-XXXXX-X). Try again, and use the red link to get your access code', 'wp-meta-seo') . '.</p></div>';
            }

            update_option('wpms_google_alanytics', $google_alanytics);
        }

        if (isset($_POST['wpms_ga_uax_reference'])) {
            $opts                          = get_option('_metaseo_ggtracking_disconnect_settings');
            $opts['wpms_ga_uax_reference'] = $_POST['wpms_ga_uax_reference'];
            update_option(
                '_metaseo_ggtracking_disconnect_settings',
                $opts
            );
        }

        $wizard = new WpmsInstallWizard();
        wp_safe_redirect(esc_url_raw($wizard->getNextLink($current_step)));
        exit;
    }

    /**
     * Retrieves all Google Analytics Views with details
     *
     * @param object $service       Google analytics server
     * @param string $error_timeout Timeout
     *
     * @return array
     */
    public static function refreshProfiles($service, $error_timeout)
    {
        try {
            $ga_dash_profile_list = array();
            $startindex           = 1;
            $totalresults         = 65535; // use something big
            while ($startindex < $totalresults) {
                $profiles = $service->management_profiles->listManagementProfiles(
                    '~all',
                    '~all',
                    array(
                        'start-index' => $startindex
                    )
                );
                $items    = $profiles->getItems();

                $totalresults = $profiles->getTotalResults();

                if ($totalresults > 0) {
                    foreach ($items as $profile) {
                        $timetz                 = new DateTimeZone($profile->getTimezone());
                        $localtime              = new DateTime('now', $timetz);
                        $timeshift              = strtotime($localtime->format('Y-m-d H:i:s')) - time();
                        $ga_dash_profile_list[] = array(
                            $profile->getName(),
                            $profile->getId(),
                            $profile->getwebPropertyId(),
                            $profile->getwebsiteUrl(),
                            $timeshift,
                            $profile->getTimezone(),
                            $profile->getDefaultPage()
                        );
                        $startindex ++;
                    }
                }
            }

            if (empty($ga_dash_profile_list)) {
                WpmsGaTools::setCache(
                    'last_error',
                    date('Y-m-d H:i:s') . ': No properties were found in this account!',
                    $error_timeout
                );
            } else {
                WpmsGaTools::deleteCache('last_error');
            }
            return $ga_dash_profile_list;
        } catch (Google_IO_Exception $e) {
            WpmsGaTools::setCache(
                'last_error',
                date('Y-m-d H:i:s') . ': ' . esc_html($e),
                $error_timeout
            );
            return $ga_dash_profile_list;
        } catch (Google_Service_Exception $e) {
            WpmsGaTools::setCache(
                'last_error',
                date('Y-m-d H:i:s') . ': ' . esc_html('(' . $e->getCode() . ') ' . $e->getMessage()),
                $error_timeout
            );
            WpmsGaTools::setCache(
                'gapi_errors',
                $e->getCode(),
                $error_timeout
            );
            return $ga_dash_profile_list;
        } catch (Exception $e) {
            WpmsGaTools::setCache(
                'last_error',
                date('Y-m-d H:i:s') . ': ' . esc_html($e),
                $error_timeout
            );
            return $ga_dash_profile_list;
        }
    }
}
