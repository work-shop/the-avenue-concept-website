<?php

/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Modified by Joomunited
 */

/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

if (!class_exists('WpmsGaTools')) {

    /**
     * Class WpmsGaTools
     */
    class WpmsGaTools
    {
        /**
         * Get google analytics client
         *
         * @param object $client         Google analytics client
         * @param array  $access         Access info to connect
         * @param array  $access_default Access default info to connect
         *
         * @return mixed
         */
        public static function setClient($client, $access, $access_default)
        {
            if (isset($access['wpmsga_dash_userapi']) && (int) $access['wpmsga_dash_userapi'] === 1) {
                if (!empty($access['wpmsga_dash_clientid']) && !empty($access['wpmsga_dash_clientsecret'])) {
                    $client->setClientId($access['wpmsga_dash_clientid']);
                    $client->setClientSecret($access['wpmsga_dash_clientsecret']);
                } else {
                    $client->setClientId($access_default[0]);
                    $client->setClientSecret($access_default[1]);
                }
            } else {
                $client->setClientId($access_default[0]);
                $client->setClientSecret($access_default[1]);
            }

            return $client;
        }

        /**
         * Get selected profile
         *
         * @param array  $profiles List profiles
         * @param string $profile  Selected profile
         *
         * @return boolean
         */
        public static function getSelectedProfile($profiles, $profile)
        {
            if (!empty($profiles)) {
                foreach ($profiles as $item) {
                    if ($item[1] === $profile) {
                        return $item;
                    }
                }
            }
            return false;
        }

        /**
         * Get color
         *
         * @param string $colour Color
         * @param float  $per    Percent
         *
         * @return string
         */
        public static function colourVariator($colour, $per)
        {
            $colour = substr($colour, 1);
            $rgb    = '';
            $per    = $per / 100 * 255;
            if ($per < 0) {
                // Darker
                $per = abs($per);
                for ($x = 0; $x < 3; $x ++) {
                    $c   = hexdec(substr($colour, (2 * $x), 2)) - $per;
                    $c   = ($c < 0) ? 0 : dechex($c);
                    $rgb .= (strlen($c) < 2) ? '0' . $c : $c;
                }
            } else {
                // Lighter
                for ($x = 0; $x < 3; $x ++) {
                    $c   = hexdec(substr($colour, (2 * $x), 2)) + $per;
                    $c   = ($c > 255) ? 'ff' : dechex($c);
                    $rgb .= (strlen($c) < 2) ? '0' . $c : $c;
                }
            }
            return '#' . $rgb;
        }

        /**
         * Variations
         *
         * @param string $base String
         *
         * @return array
         */
        public static function variations($base)
        {
            $variations[] = $base;
            $variations[] = self::colourVariator($base, - 10);
            $variations[] = self::colourVariator($base, + 10);
            $variations[] = self::colourVariator($base, + 20);
            $variations[] = self::colourVariator($base, - 20);
            $variations[] = self::colourVariator($base, + 30);
            $variations[] = self::colourVariator($base, - 30);
            return $variations;
        }

        /**
         * Check roles
         *
         * @param array   $access_level Access level
         * @param boolean $tracking     Tracking
         *
         * @return boolean
         */
        public static function checkRoles($access_level, $tracking = false)
        {
            if (is_user_logged_in() && isset($access_level)) {
                $current_user = wp_get_current_user();
                $roles        = (array) $current_user->roles;
                if ((current_user_can('manage_options')) && !$tracking) {
                    return true;
                }
                if (count(array_intersect($roles, $access_level)) > 0) {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        }

        /**
         * Set cache
         *
         * @param string  $name       Option cache name
         * @param string  $value      Option cache value
         * @param integer $expiration Expiration
         *
         * @return void
         */
        public static function setCache($name, $value, $expiration = 0)
        {
            $option = array('value' => $value, 'expires' => time() + (int) $expiration);
            update_option('wpmsga_cache_' . $name, $option);
        }

        /**
         * Remove cache
         *
         * @param string $name Option cache name
         *
         * @return void
         */
        public static function deleteCache($name)
        {
            delete_option('wpmsga_cache_' . $name);
        }

        /**
         * Get cache
         *
         * @param string $name Option cache name
         *
         * @return boolean
         */
        public static function getCache($name)
        {
            $option = get_option('wpmsga_cache_' . $name);

            if (false === $option || !isset($option['value']) || !isset($option['expires'])) {
                return false;
            }

            if ($option['expires'] < time()) {
                delete_option('wpmsga_cache_' . $name);
                return false;
            } else {
                return $option['value'];
            }
        }

        /**
         * Clear cache
         *
         * @return void
         */
        public static function clearCache()
        {
            global $wpdb;
            $wpdb->query('DELETE FROM $wpdb->options WHERE option_name LIKE "wpmsga_cache_qr%%"');
        }

        /**
         * Get root domain
         *
         * @param string $domain Site domain
         *
         * @return array
         */
        public static function getRootDomain($domain)
        {
            $root = explode('/', $domain);
            preg_match(
                '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i',
                str_ireplace('www', '', isset($root[2]) ? $root[2] : $domain),
                $root
            );
            return $root;
        }

        /**
         * Strip protocol
         *
         * @param string $domain Site domain
         *
         * @return mixed
         */
        public static function stripProtocol($domain)
        {
            return str_replace(array('https://', 'http://', ' '), '', $domain);
        }
    }

}
