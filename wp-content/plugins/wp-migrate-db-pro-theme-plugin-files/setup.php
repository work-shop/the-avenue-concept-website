<?php

function wpmdb_setup_theme_plugin_files_addon( $cli ) {
	global $wpmdbpro_theme_plugin_files;

	// Allows hooks to bypass the regular admin / ajax checks to force load the addon (required for the CLI addon).
	$force_load = apply_filters( 'wp_migrate_db_pro_theme_plugin_files_force_load', false );

	if ( false === $force_load && ! is_null( $wpmdbpro_theme_plugin_files ) ) {
		return $wpmdbpro_theme_plugin_files;
	}

	if ( false === $force_load && ( ! function_exists( 'wp_migrate_db_pro_loaded' ) || ! wp_migrate_db_pro_loaded() ) ) {
		return false;
	}

	// Load pro classes
	$register_pro = new \DeliciousBrains\WPMDB\Pro\RegisterPro();
	$register_pro->loadContainer();
	$register_pro->loadTransfersContainer();

	$container = \DeliciousBrains\WPMDB\Container::getInstance();

	// Register classes with the Container
	( new \DeliciousBrains\WPMDBTP\Initialize() )->registerAddon();

	$container->get( 'tp_addon' )->register();
	$container->get( 'tp_addon_local' )->register();
	$container->get( 'tp_addon_remote' )->register();

	load_plugin_textdomain( 'wp-migrate-db-pro-theme-plugin-files', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	if ( $cli ) {
		//		$wpmdbpro_theme_plugin_files = \DeliciousBrains\WPMDB\Container::getInstance()->get( 'tp_addon_cli' );
	} else {
		$wpmdbpro_theme_plugin_files = \DeliciousBrains\WPMDB\Container::getInstance()->get( 'tp_addon' );
	}

	return $wpmdbpro_theme_plugin_files;
}

