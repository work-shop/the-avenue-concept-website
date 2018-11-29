<?php
	
	/**
	 * This class configures the parameters seo
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 2017 Webraftic Ltd
	 * @version 1.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	class WbcrUpm_ConfigUpdates extends Wbcr_FactoryClearfy200_Configurate {

		public function registerActionsAndFilters()
		{
			/**
			 * Plugin updates
			 */
			$plugins_update = $this->getOption('plugin_updates');

			switch( $this->getOption('plugin_updates') ) {
				case 'disable_plugin_updates':
					add_filter('site_transient_update_plugins', array($this, 'lastCheckedNow'), 50);
					add_action('admin_init', array($this, 'adminInitForPlugins'));
					add_filter('auto_update_plugin', '__return_false');
					break;
				case 'enable_plugin_auto_updates':
					add_filter('auto_update_plugin', array($this, 'pluginsAutoUpdate'), 50, 2);
					break;
			}

			if( $plugins_update != 'disable_plugin_updates' ) {
				add_filter('site_transient_update_plugins', array($this, 'disablePluginNotifications'), 50);
				add_filter('http_request_args', array($this, 'httpRequestArgsRemovePlugins'), 5, 2);
			}

			/**
			 * Theme updates
			 */
			switch( $this->getOption('theme_updates') ) {
				case 'disable_theme_updates':
					add_filter('site_transient_update_themes', array($this, 'lastCheckedNow'), 50);
					add_action('admin_init', array($this, 'adminInitForThemes'));
					add_filter('auto_update_theme', '__return_false');
					break;
				case 'enable_theme_auto_updates':
					add_filter('auto_update_theme', '__return_true', 1);
					break;
			}

			/**
			 * disable wp default translation update
			 */

			if( $this->getOption('auto_tran_update') ) {
				add_filter('auto_update_translation', '__return_false', 1);
			}

			/**
			 * control WP Auto core update
			 */

			switch( $this->getOption('wp_update_core') ) {
				case 'disable_core_updates':
					$this->disableAllCoreUpdates();
					break;
				case 'disable_core_auto_updates':
					add_filter('allow_major_auto_core_updates', '__return_false');
					add_filter('allow_dev_auto_core_updates', '__return_false');
					add_filter('allow_minor_auto_core_updates', '__return_false');
					break;
				case 'major':
					add_filter('allow_major_auto_core_updates', '__return_true');
					break;
				case 'development':
					add_filter('allow_dev_auto_core_updates', '__return_true');
					break;
				default:
					add_filter('allow_minor_auto_core_updates', '__return_true');
					break;
			}

			/**
			 * disable wp default translation update
			 */
			if( $this->getOption('enable_update_vcs') ) {
				add_filter('automatic_updates_is_vcs_checkout', '__return_false', 1);
			}

			/**
			 * disable updates nags for all users except admin
			 */
			if( $this->getOption('updates_nags_only_for_admin') && !current_user_can('update_core') ) {
				remove_action('admin_notices', 'update_nag', 3);
			}

			add_action('schedule_event', array($this, 'filterCronEvents'));
		}

		/**
		 * Filter cron events
		 * @param $event
		 * @return bool
		 */
		public function filterCronEvents($event)
		{
			$core_updates = $this->getOption('wp_update_core') == 'disable_core_updates';
			$plugins_updates = $this->getOption('plugin_updates') == 'disable_plugin_updates';
			$themes_updates = $this->getOption('theme_updates') == 'disable_theme_updates';

			if( !is_object($event) || empty($event->hook) ) {
				return $event;
			}

			switch( $event->hook ) {
				case 'wp_version_check':
					$event = $core_updates
						? false
						: $event;
					break;
				case 'wp_update_plugins':
					$event = $plugins_updates
						? false
						: $event;
					break;
				case 'wp_update_themes':
					$event = $themes_updates
						? false
						: $event;
					break;
				case 'wp_maybe_auto_update':
					$event = $core_updates
						? false
						: $event;
					break;
			}

			return $event;
		}

		/**
		 * Enables plugin automatic updates on an individual basis.
		 *
		 * @param bool $update Whether the item has automatic updates enabled
		 * @param object $item Object holding the asset to be updated
		 * @return bool True of automatic updates enabled, false if not
		 */
		public function pluginsAutoUpdate($update, $item)
		{
			$filters = $this->getOption('plugins_update_filters');

			$slug_parts = explode('/', $item->plugin);
			$actual_slug = array_shift($slug_parts);

			if( !empty($filters) ) {
				if( isset($filters['disable_auto_updates']) && isset($filters['disable_auto_updates'][$actual_slug]) ) {
					return false;
				}

				if( isset($filters['disable_updates']) && isset($filters['disable_updates'][$actual_slug]) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Disables plugin updates on an individual basis.
		 *
		 * @param object $plugins Plugins that may have update notifications
		 * @return object Updated plugins list with updates
		 */
		public function disablePluginNotifications($plugins)
		{
			if( !isset($plugins->response) || empty($plugins->response) ) {
				return $plugins;
			}

			$filters = $this->getOption('plugins_update_filters');

			if( !empty($filters) && isset($filters['disable_updates']) ) {
				foreach((array)$plugins->response as $slug => $plugin) {
					$slug_parts = explode('/', $slug);
					$actual_slug = array_shift($slug_parts);
					if( isset($filters['disable_updates'][$actual_slug]) ) {
						unset($plugins->response[$slug]);
					}
				}
			}

			return $plugins;
		}

		/**
		 * Disables theme and plugin http requests on an individual basis.
		 *
		 * @param array $r Request array
		 * @param string $url URL requested
		 * @return array Updated Request array
		 */
		public function httpRequestArgsRemovePlugins($r, $url)
		{
			if( 0 !== strpos($url, 'https://api.wordpress.org/plugins/update-check/1.1/') ) {
				return $r;
			}

			if( isset($r['body']['plugins']) ) {
				$r_plugins = json_decode($r['body']['plugins'], true);
				$filters = $this->getOption('plugins_update_filters');

				if( isset($r_plugins['plugins']) && !empty($r_plugins['plugins']) ) {
					foreach($r_plugins['plugins'] as $slug => $plugin) {
						$slug_parts = explode('/', $slug);
						$actual_slug = array_shift($slug_parts);

						if( isset($filters['disable_updates']) && isset($filters['disable_updates'][$actual_slug]) ) {
							unset($r_plugins['plugins'][$slug]);

							if( false !== $key = array_search($slug, $r_plugins['active']) ) {
								unset($r_plugins['active'][$key]);
								$r_plugins['active'] = array_values($r_plugins['active']);
							}
						}
					}
				}
				$r['body']['plugins'] = json_encode($r_plugins);
			}

			return $r;
		}


		public function disableAllCoreUpdates()
		{
			add_action('admin_init', array($this, 'adminInitForCore'));

			/*
			 * Disable All Automatic Updates
			 * 3.7+
			 *
			 * @author	sLa NGjI's @ slangji.wordpress.com
			 */
			add_filter('automatic_updater_disabled', '__return_true');
			add_filter('allow_minor_auto_core_updates', '__return_false');
			add_filter('allow_major_auto_core_updates', '__return_false');
			add_filter('allow_dev_auto_core_updates', '__return_false');
			add_filter('auto_update_core', '__return_false');
			add_filter('wp_auto_update_core', '__return_false');
			add_filter('auto_core_update_send_email', '__return_false');
			add_filter('send_core_update_notification_email', '__return_false');
			add_filter('automatic_updates_send_debug_email', '__return_false');
			add_filter('automatic_updates_is_vcs_checkout', '__return_true');
			remove_action('admin_notices', 'update_nag', 3);
			remove_action('admin_notices', 'maintenance_nag');
		}

		/**
		 * Initialize and load the plugin stuff
		 * @author scripts@schloebe.de
		 */
		function adminInitForPlugins()
		{
			/*
			 * 2.8 to 3.0
			 */
			remove_action('load-plugins.php', 'wp_update_plugins');
			remove_action('load-update.php', 'wp_update_plugins');
			remove_action('admin_init', '_maybe_update_plugins');
			remove_action('wp_update_plugins', 'wp_update_plugins');
			wp_clear_scheduled_hook('wp_update_plugins');

			/*
			 * 3.0
			 */
			remove_action('load-update-core.php', 'wp_update_plugins');
			wp_clear_scheduled_hook('wp_update_plugins');
		}

		function adminInitForThemes()
		{
			/*
			 * 2.8 to 3.0
			 */
			remove_action('load-themes.php', 'wp_update_themes');
			remove_action('load-update.php', 'wp_update_themes');
			remove_action('admin_init', '_maybe_update_themes');
			remove_action('wp_update_themes', 'wp_update_themes');
			wp_clear_scheduled_hook('wp_update_themes');

			/*
			 * 3.0
			 */
			remove_action('load-update-core.php', 'wp_update_themes');
			wp_clear_scheduled_hook('wp_update_themes');
		}

		/**
		 * Initialize and load the plugin stuff
		 * @author scripts@schloebe.de
		 */
		function adminInitForCore()
		{
			/*
			 * 2.8 to 3.0
			 */
			remove_action('wp_version_check', 'wp_version_check');
			remove_action('admin_init', '_maybe_update_core');
			wp_clear_scheduled_hook('wp_version_check');

			/*
			 * 3.7+
			 */
			remove_action('wp_maybe_auto_update', 'wp_maybe_auto_update');
			remove_action('admin_init', 'wp_maybe_auto_update');
			remove_action('admin_init', 'wp_auto_update_core');
			wp_clear_scheduled_hook('wp_maybe_auto_update');
		}

		public function lastCheckedNow($transient)
		{
			global $wp_version;

			include ABSPATH . WPINC . '/version.php';
			$current = new stdClass;
			$current->updates = array();
			$current->version_checked = $wp_version;
			$current->last_checked = time();

			return $current;
		}
	}