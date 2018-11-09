<?php
/*
Plugin Name: WP Migrate DB Pro Theme & Plugin Files
Plugin URI: http://deliciousbrains.com/wp-migrate-db-pro/
Description: An extension to WP Migrate DB Pro, allows for migrating Theme & Plugin files.
Author: Delicious Brains
Version: 1.0.3
Author URI: http://deliciousbrains.com
Network: True
*/

// Copyright (c) 2017 Delicious Brains. All rights reserved.
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

require_once 'version.php';
$GLOBALS['wpmdb_meta']['wp-migrate-db-pro-theme-plugin-files']['folder'] = basename( plugin_dir_path( __FILE__ ) );

class WPMDB_Pro_Theme_Plugin_Files_Setup {

	public $wpmdbpro_theme_plugin_files = null;

	public function __construct( $cli = false ) {

		// By default load plugin on admin pages, a little later than WP Migrate DB Pro.
		add_action( 'admin_init', array( $this, 'init' ), 22 );

		// Handle CLI load
		add_action( 'wp_migrate_db_pro_cli_before_load', array( $this, 'before_cli_load' ) );
	}

	public function init( $cli = false ) {

		if ( ! class_exists( 'WPMDBPro_Addon' ) ) {
			return false;
		}

		// Allows hooks to bypass the regular admin / ajax checks to force load the Theme & Plugin Files addon (required for the CLI addon)
		$force_load = apply_filters( 'wp_migrate_db_pro_theme_plugin_files_force_load', false );

		if ( false === $force_load && ! null !== $this->wpmdbpro_theme_plugin_files ) {
			$this->wpmdbpro_theme_plugin_files;
		}

		if ( false === $force_load && ( ! function_exists( 'wp_migrate_db_pro_loaded' ) || ! wp_migrate_db_pro_loaded() ) ) {
			return false;
		}

		load_plugin_textdomain( 'wp-migrate-db-pro-theme-plugin-files', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Test if below PHP 5.4
		if ( version_compare( phpversion(), '5.4', '<' ) ) {
			add_action( 'wpmdb_notices', array( $this, 'template_min_php' ) );

			return false;
		}

		// @TODO nuke once autoloading set up
		require_once dirname( __FILE__ ) . '/class/wpmdbpro-theme-plugin-files.php';
		require_once dirname( __FILE__ ) . '/class/cli/wpmdbpro-theme-plugin-files-cli.php';

		if ( $cli ) {
			$this->wpmdbpro_theme_plugin_files = new WPMDBPro_Theme_Plugin_Files_CLI( __FILE__ );
		} else {
			$this->wpmdbpro_theme_plugin_files = new WPMDBPro_Theme_Plugin_Files( __FILE__ );
		}

		return $this->wpmdbpro_theme_plugin_files;
	}

	public function template_min_php() {
		global $wpmdbpro;
		$wpmdbpro->template( 'min-php-message' );
	}

	public function before_cli_load() {

		// Force load Theme & Plugin Files addon
		add_filter( 'wp_migrate_db_pro_theme_plugin_files_force_load', '__return_true' );
		$this->init( true );
	}
}

new WPMDB_Pro_Theme_Plugin_Files_Setup();
