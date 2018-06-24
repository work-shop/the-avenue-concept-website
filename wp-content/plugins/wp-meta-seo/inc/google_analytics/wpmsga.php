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
         * @var null
         */
        private static $instance = null;
        /**
         * @var null
         */
        public $config = null;
        /**
         * @var null
         */
        public $tracking = null;
        /**
         * @var null
         */
        public $frontend_item_reports = null;
        /**
         * @var null
         */
        public $backend_item_reports = null;
        /**
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
                    __("This is not allowed, please read the documentation!", 'wp-meta-seo'),
                    '4.6'
                );
            }
        }

        /**
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
 */
function WPMSGA()
{
    return WpmsGaManager::instance();
}

/*
 * Start WPMSGA
 */
WPMSGA();
