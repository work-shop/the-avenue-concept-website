<?php
	/**
	 * Admin boot
	 * @author Webcraftic <alex.kovalevv@gmail.com>
	 * @copyright Webcraftic 25.05.2017
	 * @version 1.0
	 */

	/**
	 * Ошибки совместимости с похожими плагинами
	 */
	function wbcr_upm_admin_conflict_notices_error($notices, $plugin_name)
	{
		if( $plugin_name != WUP_Plugin::app()->getPluginName() ) {
			return $notices;
		}

		$warnings = array();

		$default_notice = WUP_Plugin::app()
				->getPluginTitle() . ': ' . __('We found that you have the plugin %s installed. The functions of this plugin already exist in %s. Please deactivate plugin %s to avoid conflicts between plugins\' functions.', 'webcraftic-updates-manager');
		$default_notice .= ' ' . __('If you do not want to deactivate the plugin %s for some reason, we strongly recommend do not use the same plugins\' functions at the same time!', 'webcraftic-updates-manager');

		if( is_plugin_active('companion-auto-update/companion-auto-update.php') ) {
			$warnings[] = sprintf($default_notice, 'Companion Auto Update', WUP_Plugin::app()
				->getPluginTitle(), 'Companion Auto Update', 'Companion Auto Update');
		}

		if( is_plugin_active('disable-updates/disable-updates.php') ) {
			$warnings[] = sprintf($default_notice, 'Disable Updates', WUP_Plugin::app()
				->getPluginTitle(), 'Disable Updates', 'Disable Updates');
		}

		if( is_plugin_active('disable-wordpress-updates/disable-updates.php') ) {
			$warnings[] = sprintf($default_notice, 'Disable All WordPress Updates', WUP_Plugin::app()
				->getPluginTitle(), 'Disable All WordPress Updates', 'Disable All WordPress Updates');
		}

		if( is_plugin_active('stops-core-theme-and-plugin-updates/main.php') ) {
			$warnings[] = sprintf($default_notice, 'Easy Updates Manager', WUP_Plugin::app()
				->getPluginTitle(), 'Easy Updates Manager', 'Easy Updates Manager');
		}

		if( empty($warnings) ) {
			return $notices;
		}
		$notice_text = '';
		foreach((array)$warnings as $warning) {
			$notice_text .= '<p>' . $warning . '</p>';
		}

		$notices[] = array(
			'id' => 'ump_plugin_compatibility',
			'type' => 'error',
			'dismissible' => true,
			'dismiss_expires' => 0,
			'text' => $notice_text
		);

		return $notices;
	}

	//add_action('admin_notices', 'wbcr_upm_admin_conflict_notices_error');
	add_filter('wbcr_factory_admin_notices', 'wbcr_upm_admin_conflict_notices_error', 10, 2);

	function wbcr_upm_rating_widget_url($page_url, $plugin_name)
	{
		if( $plugin_name == WUP_Plugin::app()->getPluginName() ) {
			return 'https://goo.gl/Be2hQU';
		}

		return $page_url;
	}

	add_filter('wbcr_factory_pages_401_imppage_rating_widget_url', 'wbcr_upm_rating_widget_url', 10, 2);

	function wbcr_upm_group_options($options)
	{
		$options[] = array(
			'name' => 'plugin_updates',
			'title' => __('Disable plugin updates', 'webcraftic-updates-manager'),
			'tags' => array('disable_all_updates'),
			'values' => array('disable_all_updates' => 'disable_plugin_updates')
		);
		$options[] = array(
			'name' => 'theme_updates',
			'title' => __('Disable theme updates', 'webcraftic-updates-manager'),
			'tags' => array('disable_all_updates'),
			'values' => array('disable_all_updates' => 'disable_theme_updates')
		);
		$options[] = array(
			'name' => 'auto_tran_update',
			'title' => __('Disable Automatic Translation Updates', 'webcraftic-updates-manager'),
			'tags' => array('disable_all_updates')
		);
		$options[] = array(
			'name' => 'wp_update_core',
			'title' => __('Disable wordPress core updates', 'webcraftic-updates-manager'),
			'tags' => array('disable_all_updates'),
			'values' => array('disable_all_updates' => 'disable_core_updates')
		);
		$options[] = array(
			'name' => 'enable_update_vcs',
			'title' => __('Enable updates for VCS Installations', 'webcraftic-updates-manager'),
			'tags' => array()
		);
		$options[] = array(
			'name' => 'plugins_update_filters',
			'title' => __('Plugin filters', 'webcraftic-updates-manager'),
			'tags' => array()
		);
		$options[] = array(
			'name' => 'updates_nags_only_for_admin',
			'title' => __('Updates nags only for Admin', 'webcraftic-updates-manager'),
			'tags' => array('recommended')
		);

		return $options;
	}

	add_filter("wbcr_clearfy_group_options", 'wbcr_upm_group_options');

	function wbcr_upm_allow_quick_mods($mods)
	{
		$mods['disable_all_updates'] = array(
			'title' => __('One click disable all updates', 'webcraftic-updates-manager'),
			'icon' => 'dashicons-update'
		);

		return $mods;
	}

	add_filter("wbcr_clearfy_allow_quick_mods", 'wbcr_upm_allow_quick_mods');

	function wbcr_ump_set_plugin_meta($links, $file)
	{
		if( $file == WUP_PLUGIN_BASE ) {

			$url = 'https://clearfy.pro';

			if( get_locale() == 'ru_RU' ) {
				$url = 'https://ru.clearfy.pro';
			}

			$url .= '?utm_source=wordpress.org&utm_campaign=' . WUP_Plugin::app()->getPluginName();

			$links[] = '<a href="' . $url . '" style="color: #FF5722;font-weight: bold;" target="_blank">' . __('Get ultimate plugin free', 'webcraftic-updates-manager') . '</a>';
		}

		return $links;
	}

	if( !defined('LOADING_UPDATES_MANAGER_AS_ADDON') ) {
		add_filter('plugin_row_meta', 'wbcr_ump_set_plugin_meta', 10, 2);
	}





