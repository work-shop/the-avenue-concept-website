<?php
	/**
	 * Hide my wp core class
	 * @author Webcraftic <wordpress.webraftic@gmail.com>
	 * @copyright (c) 19.02.2018, Webcraftic
	 * @version 1.0
	 */

	// Exit if accessed directly
	if( !defined('ABSPATH') ) {
		exit;
	}

	if( !class_exists('WUP_Plugin') ) {
		
		if( !class_exists('WUP_PluginFactory') ) {
			if( defined('LOADING_UPDATES_MANAGER_AS_ADDON') ) {
				class WUP_PluginFactory {
					
				}
			} else {
				class WUP_PluginFactory extends Wbcr_Factory400_Plugin {
					
				}
			}
		}
		
		class WUP_Plugin extends WUP_PluginFactory {
			
			/**
			 * @var Wbcr_Factory400_Plugin
			 */
			private static $app;
			
			/**
			 * @var bool
			 */
			private $as_addon;
			
			/**
			 * @param string $plugin_path
			 * @param array $data
			 * @throws Exception
			 */
			public function __construct($plugin_path, $data)
			{
				$this->as_addon = isset($data['as_addon']);
				
				if( $this->as_addon ) {
					$plugin_parent = isset($data['plugin_parent'])
						? $data['plugin_parent']
						: null;
					
					if( !($plugin_parent instanceof Wbcr_Factory400_Plugin) ) {
						throw new Exception('An invalid instance of the class was passed.');
					}
					
					self::$app = $plugin_parent;
				} else {
					self::$app = $this;
				}
				
				if( !$this->as_addon ) {
					parent::__construct($plugin_path, $data);
				}

				$this->setTextDomain();
				$this->setModules();
				
				$this->globalScripts();
				
				if( is_admin() ) {
					$this->adminScripts();
				}

				add_action('plugins_loaded', array($this, 'pluginsLoaded'));
			}

			/**
			 * @return Wbcr_Factory400_Plugin
			 */
			public static function app()
			{
				return self::$app;
			}

			protected function setTextDomain()
			{
				// Localization plugin
				load_plugin_textdomain('webcraftic-updates-manager', false, dirname(WUP_PLUGIN_BASE) . '/languages/');
			}
			
			protected function setModules()
			{
				if( !$this->as_addon ) {
					self::app()->load(array(
						array('libs/factory/bootstrap', 'factory_bootstrap_400', 'admin'),
						array('libs/factory/forms', 'factory_forms_400', 'admin'),
						array('libs/factory/pages', 'factory_pages_401', 'admin'),
						array('libs/factory/clearfy', 'factory_clearfy_200', 'all'),
						array('libs/factory/notices', 'factory_notices_400', 'all')
					));
				}
			}
			
			private function registerPages()
			{

				$admin_path = WUP_PLUGIN_DIR . '/admin/pages';

				self::app()->registerPage('WbcrUpm_UpdatesPage', $admin_path . '/updates.php');
				self::app()->registerPage('WbcrUpm_PluginsPage', $admin_path . '/plugins.php');
				self::app()->registerPage('WbcrUpm_AdvancedPage', $admin_path . '/advanced.php');

				if( !$this->as_addon ) {
					self::app()->registerPage('WbcrUpm_MoreFeaturesPage', $admin_path . '/more-features.php');
				}
			}
			
			private function adminScripts()
			{
				require_once(WUP_PLUGIN_DIR . '/admin/boot.php');
				$this->registerPages();
			}
			
			private function globalScripts()
			{
			}

			public function pluginsLoaded()
			{
				require(WUP_PLUGIN_DIR . '/includes/classes/class.configurate-updates.php');
				new WbcrUpm_ConfigUpdates(self::$app);
			}
		}
	}