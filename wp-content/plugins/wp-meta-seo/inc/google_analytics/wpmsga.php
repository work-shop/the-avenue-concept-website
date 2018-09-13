<?php
/**
 * Class manage instance of WPMSGA
 */
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
if (!class_exists('WpmsGaManager')) {

    /**
     * Class WpmsGaManager
     */
    final class WpmsGaManager
    {
        /**
         * Instance
         *
         * @var null
         */
        private static $instance = null;
        /**
         * Config
         *
         * @var null
         */
        public $config = null;
        /**
         * Tracking
         *
         * @var null
         */
        public $tracking = null;
        /**
         * Frontend item reports
         *
         * @var null
         */
        public $frontend_item_reports = null;
        /**
         * Backend item reports
         *
         * @var null
         */
        public $backend_item_reports = null;
        /**
         * Controller
         *
         * @var null
         */
        public $controller = null;

        /**
         * Construct forbidden
         */
        private function __construct()
        {
            if (null !== self::$instance) {
                _doing_it_wrong(
                    __FUNCTION__,
                    esc_html__('This is not allowed, please read the documentation!', 'wp-meta-seo'),
                    '4.6'
                );
            }
        }

        /**
         * Instance
         *
         * @return null|WpmsGaManager
         */
        public static function instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

/**
 * Returns a unique instance of WPMSGA
 *
 * @return null|WpmsGaManager
 */
function WPMSGA()
{
    return WpmsGaManager::instance();
}

/*
 * Start WPMSGA
 */
WPMSGA();
