<?php

namespace DeliciousBrains\WPMDBTP;

use DeliciousBrains\WPMDB\Container;

class Initialize {

	public function __construct() { }

	public function registerAddon() {
		$container = Container::getInstance();
		$container->add( 'tp_addon', 'DeliciousBrains\WPMDBTP\ThemePluginFilesAddon' )
		          ->withArguments( [
			          'addon',
			          'properties',
			          'dynamic_properties',
			          'template',
			          'filesystem',
			          'profile_manager',
			          'util',
			          'transfers_files_util',
			          'transfers_receiver',
			          'tp_addon_finalize',
		          ] );

		$container->add( 'tp_addon_finalize', 'DeliciousBrains\WPMDBTP\ThemePluginFilesFinalize' )
		          ->withArguments( [
			          'form_data',
			          'filesystem',
			          'transfers_files_util',
			          'error_log',
			          'http',
			          'state_data_container',
			          'queue_manager',
		          ] );

		$container->add( 'transfer_check', 'DeliciousBrains\WPMDBTP\TransferCheck' )
		          ->withArguments( [
			          'form_data',
			          'http',
			          'error_log',
		          ] );

		$container->add( 'tp_addon_local', 'DeliciousBrains\WPMDBTP\ThemePluginFilesLocal' )
		          ->withArguments( [
			          'transfers_files_util',
			          'util',
			          'transfers_files_file_processor',
			          'queue_manager',
			          'transfers_files_transfer_manager',
			          'transfers_receiver',
			          'migration_state_manager',
			          'http',
			          'filesystem',
			          'transfer_check',
		          ] );

		$container->add( 'tp_addon_remote', 'DeliciousBrains\WPMDBTP\ThemePluginFilesRemote' )
		          ->withArguments( [
			          'transfers_files_util',
			          'transfers_files_file_processor',
			          'queue_manager',
			          'transfers_files_transfer_manager',
			          'transfers_receiver',
			          'http',
			          'http_helper',
			          'migration_state_manager',
			          'settings',
			          'properties',
			          'transfers_sender',
			          'filesystem',
			          'scramble'
		          ] );

	}
}
