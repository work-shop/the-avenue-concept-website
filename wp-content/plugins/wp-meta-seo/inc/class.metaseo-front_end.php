<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
include_once(WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmstools.php');
include_once(WPMETASEO_PLUGIN_DIR . 'inc/google_analytics/wpmsgapi.php');

/**
 * Class MetaSeoFront
 * Class that holds most of the admin functionality for Meta SEO.
 */
class MetaSeoFront
{
    /**
     * @var array
     */
    public $ga_tracking;
    /**
     * @var array
     */
    public $gaDisconnect;

    /**
     * MetaSeoFront constructor.
     */
    public function __construct()
    {
        $this->ga_tracking = array(
            'wpmsga_dash_tracking' => 1,
            'wpmsga_dash_tracking_type' => 'universal',
            'wpmsga_dash_anonim' => 0,
            'wpmsga_dash_remarketing' => 0,
            'wpmsga_event_tracking' => 0,
            'wpmsga_event_downloads' => 'zip|mp3*|mpe*g|pdf|docx*|pptx*|xlsx*|rar*',
            'wpmsga_aff_tracking' => 0,
            'wpmsga_event_affiliates' => '/out/',
            'wpmsga_hash_tracking' => 0,
            'wpmsga_author_dimindex' => 0,
            'wpmsga_pubyear_dimindex' => 0,
            'wpmsga_category_dimindex' => 0,
            'wpmsga_user_dimindex' => 0,
            'wpmsga_tag_dimindex' => 0,
            'wpmsga_speed_samplerate' => 1,
            'wpmsga_event_bouncerate' => 0,
            'wpmsga_enhanced_links' => 0,
            'wpmsga_dash_adsense' => 0,
            'wpmsga_crossdomain_tracking' => 0,
            'wpmsga_crossdomain_list' => '',
            'wpmsga_cookiedomain' => '',
            'wpmsga_cookiename' => '',
            'wpmsga_cookieexpires' => '',
            'wpmsga_track_exclude' => array(),
        );

        $ga_tracking = get_option('_metaseo_ggtracking_settings');
        if (is_array($ga_tracking)) {
            $this->ga_tracking = array_merge($this->ga_tracking, $ga_tracking);
        }

        $this->gaDisconnect = array(
            'wpms_ga_uax_reference' => '',
            'wpmsga_dash_tracking_type' => 'universal',
            'wpmsga_code_tracking' => ''
        );
        $gaDisconnect = get_option('_metaseo_ggtracking_disconnect_settings');
        if (is_array($gaDisconnect)) {
            $this->gaDisconnect = array_merge($this->gaDisconnect, $gaDisconnect);
        }

        add_action('wp_head', array($this, 'trackingCode'), 99);
    }

    /**
     * Create tracking code on front-end
     * @return bool
     */
    public function trackingCode()
    {
        if (!empty($this->ga_tracking['wpmsga_code_tracking'])) {
            require_once 'google_analytics/tracking/custom.php';
            return false;
        }

        if (WpmsGaTools::checkRoles($this->ga_tracking['wpmsga_track_exclude'], true)) {
            return false;
        }

        $google_alanytics = get_option('wpms_google_alanytics');
        $traking_mode = $this->ga_tracking['wpmsga_dash_tracking'];
        if ($traking_mode > 0) {
            if (empty($google_alanytics['tableid_jail'])) {
                $tracking_code = trim($this->gaDisconnect['wpmsga_code_tracking']);
                if (!empty($tracking_code)) {
                    echo '<script type="text/javascript">';
                    echo strip_tags(stripslashes($this->gaDisconnect['wpmsga_code_tracking']), '</script>');
                    echo '</script>';
                } else {
                    if (empty($this->gaDisconnect['wpms_ga_uax_reference'])) {
                        return false;
                    }
                    $traking_type = $this->gaDisconnect['wpmsga_dash_tracking_type'];
                    if ($traking_type == "classic") {
                        echo "\n<!-- BEGIN WPMSGA v" . WPMSEO_VERSION . " Classic Tracking
                         - https://wordpress.org/plugins/wp-meta-seo/ -->\n";
                        require_once 'google_analytics/tracking/classic_disconnect.php';
                        echo "\n<!-- END WPMSGA Classic Tracking -->\n\n";
                    } else {
                        echo "\n<!-- Universal Tracking - https://wordpress.org/plugins/wp-meta-seo/ -->\n";
                        require_once 'google_analytics/tracking/universal_disconnect.php';
                        echo "\n<!-- END WPMSGA Universal Tracking -->\n\n";
                    }
                }
            } else {
                $traking_type = $this->ga_tracking['wpmsga_dash_tracking_type'];
                if ($traking_type == "classic") {
                    echo "\n<!-- Classic Tracking - https://wordpress.org/plugins/wp-meta-seo/ -->\n";
                    if ($this->ga_tracking['wpmsga_event_tracking']) {
                        require_once 'google_analytics/tracking/events-classic.php';
                    }
                    require_once 'google_analytics/tracking/code-classic.php';
                    echo "\n<!-- END WPMSGA Classic Tracking -->\n\n";
                } else {
                    echo "\n<!-- Universal Tracking - https://wordpress.org/plugins/wp-meta-seo/ -->\n";
                    if ($this->ga_tracking['wpmsga_event_tracking']
                        || $this->ga_tracking['wpmsga_aff_tracking'] || $this->ga_tracking['wpmsga_hash_tracking']) {
                        require_once 'google_analytics/tracking/events-universal.php';
                    }
                    require_once 'google_analytics/tracking/code-universal.php';
                    echo "\n<!-- END WPMSGA Universal Tracking -->\n\n";
                }
            }
        }
        return true;
    }
}
