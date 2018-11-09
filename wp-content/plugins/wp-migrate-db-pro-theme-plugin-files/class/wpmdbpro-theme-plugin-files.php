<?php

/**
 * Class WPMDBPro_Theme_Plugin_Files
 *
 *
 */

use \WPMDB\Transfers\Receiver;
use \WPMDB\Queue\Manager;

class WPMDBPro_Theme_Plugin_Files extends WPMDBPro_Addon {

	protected $wpmdb_theme_plugin_files_local;
	protected $wpmdb_theme_plugin_files_remote;

	/**
	 * @var object $wpmdbpro
	 */
	public $wpmdbpro;

	/**
	 * An array strings used for translations
	 *
	 * @var array $strings
	 */
	protected $strings;

	/**
	 * @var array $default_file_ignores
	 */
	protected $default_file_ignores;

	/**
	 * @var object $file_ignores
	 */
	protected $file_ignores;

	/**
	 * @var array $accepted_fields
	 */
	protected $accepted_fields;
	public $transfer_helpers;
	public $receiver;

	public function __construct( $plugin_file_path ) {
		parent::__construct( $plugin_file_path );
		global $wpmdbpro;
		$this->wpmdbpro = $wpmdbpro;

		$this->plugin_slug    = 'wp-migrate-db-pro-theme-plugin-files';
		$this->plugin_version = $GLOBALS['wpmdb_meta']['wp-migrate-db-pro-theme-plugin-files']['version'];

		if ( ! $this->meets_version_requirements( '1.8.5' ) ) {
			return;
		}

		$this->transfer_helpers = new \WPMDB\Transfers\Files\Util( $this, $this->filesystem );
		$this->receiver         = new \WPMDB\Transfers\Receiver( $this, $this->transfer_helpers, new \WPMDB\Transfers\Files\Payload( $this, $this->transfer_helpers, new \WPMDB\Transfers\Files\Chunker( $this, $this->transfer_helpers ) ) );

		require_once dirname( __FILE__ ) . '/wpmdbpro-theme-plugin-files-local.php';
		require_once dirname( __FILE__ ) . '/wpmdbpro-theme-plugin-files-remote.php';
		require_once dirname( __FILE__ ) . '/cli/wpmdbpro-theme-plugin-files-cli.php';

		add_action( 'wpmdb_after_advanced_options', array( $this, 'migration_form_controls' ) );
		add_action( 'wpmdb_load_assets', array( $this, 'load_assets' ) );
		add_action( 'wpmdb_before_finalize_migration', array( $this, 'maybe_finalize_tp_migration' ) );
		add_action( 'wpmdb_migration_complete', array( $this, 'cleanup_transfer_migration' ) );
		add_action( 'wpmdb_respond_to_push_cancellation', array( $this, 'remove_tmp_files' ) );
		add_action( 'wpmdb_cancellation', array( $this, 'remove_tmp_files' ) );

		add_filter( 'wpmdb_diagnostic_info', array( $this, 'diagnostic_info' ) );
		add_filter( 'wpmdb_establish_remote_connection_data', array( $this, 'establish_remote_connection_data' ) );
		add_filter( 'wpmdb_accepted_profile_fields', array( $this, 'accepted_profile_fields' ) );
		add_filter( 'wpmdb_nonces', array( $this, 'add_nonces' ) );
		add_filter( 'wpmdb_data', array( $this, 'js_variables' ) );
		add_filter( 'wpmdb_site_details', array( $this, 'filter_site_details' ) );

		// Fields that can be saved in a 'profile'
		$this->accepted_fields = array(
			'migrate_themes',
			'migrate_plugins',
			'select_plugins',
			'select_themes',
			'file_ignores',
		);

		$this->wpmdb_theme_plugin_files_local = new WPMDBPro_Theme_Plugin_Files_Local(
			$plugin_file_path,
			$this->transfer_helpers,
			new \WPMDB\Transfers\Files\FileProcessor( $this, $this->filesystem ),
			new \WPMDB\Queue\Manager( $this ),
			new \WPMDB\Transfers\Files\TransferManager(
				$this,
				new \WPMDB\Queue\Manager( $this ),
				new \WPMDB\Transfers\Files\Payload( $this, $this->transfer_helpers, new \WPMDB\Transfers\Files\Chunker( $this, $this->transfer_helpers ) ),
				$this->transfer_helpers
			),
			$this->receiver
		);

		$this->wpmdb_theme_plugin_files_remote = new WPMDBPro_Theme_Plugin_Files_Remote(
			$plugin_file_path,
			$this->transfer_helpers,
			new \WPMDB\Transfers\Files\FileProcessor( $this, $this->filesystem ),
			new \WPMDB\Queue\Manager( $this ),
			new \WPMDB\Transfers\Files\TransferManager(
				$this,
				new \WPMDB\Queue\Manager( $this ),
				new \WPMDB\Transfers\Files\Payload( $this, $this->transfer_helpers, new \WPMDB\Transfers\Files\Chunker( $this, $this->transfer_helpers ) ),
				$this->transfer_helpers
			),
			$this->receiver
		);
	}

	/**
	 * Whitelist media setting fields for use in AJAX save in core
	 *
	 * @param array $profile_fields Array of profile fields
	 *
	 * @return array Updated array of profile fields
	 */
	public function accepted_profile_fields( $profile_fields ) {
		return array_merge( $profile_fields, $this->accepted_fields );
	}

	/**
	 * Load media related assets in core plugin
	 */
	public function load_assets() {
		$plugins_url = trailingslashit( plugins_url( $this->plugin_folder_name ) );
		$version     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : $this->plugin_version;
		$ver_string  = '-' . str_replace( '.', '', $this->plugin_version );

		$src = $plugins_url . 'asset/build/css/styles.css';
		wp_enqueue_style( 'wp-migrate-db-pro-theme-plugin-files-styles', $src, array( 'wp-migrate-db-pro-styles' ), $version );

		$src = $plugins_url . "asset/build/js/bundle{$ver_string}.js";
		wp_enqueue_script( 'wp-migrate-db-pro-theme-plugin-files-script', $src, array(
			'jquery',
			'wp-migrate-db-pro-script',
		), $version, true );

		wp_localize_script( 'wp-migrate-db-pro-theme-plugin-files-script', 'wpmdbtp_settings', $this->localize_scripts() );
	}

	public function localize_scripts() {
		$loaded_profile = $this->wpmdbpro->get( 'default_profile' );

		if ( isset( $_GET['wpmdb-profile'] ) ) {
			$loaded_profile = $this->wpmdbpro->get_profile( (int) $_GET['wpmdb-profile'] );
		}

		return array(
			'strings'        => $this->get_strings(),
			'loaded_profile' => $loaded_profile,
		);
	}


	/**
	 * Get translated strings for javascript and other functions
	 *
	 * @return array Array of translations
	 */
	public function get_strings() {
		$strings = array(
			'themes'                 => __( 'Themes', 'wp-migrate-db-pro-theme-plugin-files' ),
			'plugins'                => __( 'Plugins', 'wp-migrate-db-pro-theme-plugin-files' ),
			'theme_and_plugin_files' => __( 'Theme & Plugin Files', 'wp-migrate-db-pro-theme-plugin-files' ),
			'theme_active'           => __( '(active)', 'wp-migrate-db-pro-theme-plugin-files' ),
			'select_themes'          => __( 'Please select themes for migration.', 'wp-migrate-db-pro-theme-plugin-files' ),
			'select_plugins'         => __( 'Please select plugins for migration.', 'wp-migrate-db-pro-theme-plugin-files' ),
			'remote'                 => __( 'remote', 'wp-migrate-db-pro-theme-plugin-files' ),
			'local'                  => __( 'local', 'wp-migrate-db-pro-theme-plugin-files' ),
			'failed_to_transfer'     => __( 'Failed to transfer file.', 'wp-migrate-db-pro-theme-plugin-files' ),
			'file_transfer_error'    => __( 'Theme & Plugin Files Transfer Error', 'wp-migrate-db-pro-theme-plugin-files' ),
			'loading_transfer_queue' => __( 'Loading transfer queue', 'wp-migrate-db-pro-theme-plugin-files' ),
			'current_transfer'       => __( 'Transferring: ', 'wp-migrate-db-pro-theme-plugin-files' ),
		);

		if ( is_null( $this->strings ) ) {
			$this->strings = $strings;
		}

		return $this->strings;
	}

	/**
	 * Add media related javascript variables to the page
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function js_variables( $data ) {
		$data['theme_plugin_files_version'] = $this->plugin_version;

		return $data;
	}


	/**
	 * Adds extra information to the core plugin's diagnostic info
	 */
	public function diagnostic_info( $diagnostic_info ) {
		$diagnostic_info['themes-plugins'] = array(
			"Theme & Plugin Files",
			'Transfer Bottleneck' => size_format( $this->get_transfer_bottleneck() ),
			'Themes Permissions'  => decoct( fileperms( $this->slash_one_direction( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' ) ) & 0777 ),
			'Plugins Permissions' => decoct( fileperms( $this->slash_one_direction( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' ) ) & 0777 ),
		);

		return $diagnostic_info;
	}

	/**
	 * Check the remote site has the media addon setup
	 *
	 * @param array $data Connection data
	 *
	 * @return array Updated connection data
	 */
	public function establish_remote_connection_data( $data ) {
		$data['theme_plugin_files_available'] = '1';
		$data['theme_plugin_files_version']   = $this->plugin_version;

		//@TODO - move to core plugin
		if ( function_exists( 'ini_get' ) ) {
			$max_file_uploads = ini_get( 'max_file_uploads' );
		}

		$max_file_uploads                            = ( empty( $max_file_uploads ) ) ? 20 : $max_file_uploads;
		$data['theme_plugin_files_max_file_uploads'] = apply_filters( 'wpmdbtp_max_file_uploads', $max_file_uploads );

		return $data;
	}

	/**
	 * Media addon nonces for core javascript variables
	 *
	 * @param array $nonces Array of nonces
	 *
	 * @return array Updated array of nonces
	 */
	public function add_nonces( $nonces ) {
		$nonces['wpmdb_migrate_themes_plugins']  = WPMDB_Utils::create_nonce( 'migrate-themes-plugins' );
		$nonces['wpmdb_save_ignores']            = WPMDB_Utils::create_nonce( 'wpmdb-save-ignores' );
		$nonces['wpmdb_initiate_file_migration'] = WPMDB_Utils::create_nonce( 'wpmdb-initiate-file-migration' );
		$nonces['wpmdb_transfer_files']          = WPMDB_Utils::create_nonce( 'wpmdb-transfer-files' );
		$nonces['wpmdb_get_queue_items']         = WPMDB_Utils::create_nonce( 'wpmdb-get-queue-items' );

		return $nonces;
	}

	public function migration_form_controls() {
		$this->template( 'migrate' );
	}

	/**
	 *
	 * @return array
	 *
	 */

	public function get_local_themes() {
		$themes       = wp_get_themes();
		$active_theme = wp_get_theme();
		$set_active   = false;
		$theme_list   = array();

		foreach ( $themes as $key => $theme ) {
			if ( ! is_multisite() ) {
				$set_active = ( $key == $active_theme->stylesheet );
			}

			$theme_list[ $key ] = array(
				array(
					'name'   => html_entity_decode( $theme->Name ),
					'active' => $set_active,
					'path'   => $this->slash_one_direction( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $key ),
				),
			);
		}

		return $theme_list;
	}

	/**
	 * @return array
	 */
	public function get_plugin_paths() {
		$plugin_root = $this->slash_one_direction( WP_PLUGIN_DIR );

		$plugins_dir  = @opendir( $plugin_root );
		$plugin_files = array();

		if ( $plugins_dir ) {
			while ( false !== ( $file = readdir( $plugins_dir ) ) ) {
				if ( '.' === $file[0] ) {
					continue;
				}

				if ( stristr( $file, 'wp-migrate-db' ) ) {
					continue;
				}

				if ( is_dir( $plugin_root . DIRECTORY_SEPARATOR . $file ) ) {
					$plugin_files[ $file ] = $plugin_root . DIRECTORY_SEPARATOR . $file;
				} else {
					if ( '.php' === substr( $file, - 4 ) ) {
						$plugin_files[ $file ] = $plugin_root . DIRECTORY_SEPARATOR . $file;
					}
				}
			}
			closedir( $plugins_dir );
		}

		return $plugin_files;
	}

	/**
	 * @return array
	 */
	public function get_local_plugins() {
		$plugins      = get_plugins();
		$plugin_paths = $this->get_plugin_paths();

		// @TODO get MU plugins in the list as well
		$active_plugins = $this->get_active_plugins();

		$plugin_list = array();

		foreach ( $plugins as $key => $plugin ) {
			$base_folder = preg_replace( '/\/(.*)\.php/i', '', $key );

			$plugin_excluded = $this->check_plugin_exclusions( $base_folder );

			if ( $plugin_excluded ) {
				continue;
			}

			$plugin_path         = array_key_exists( $base_folder, $plugin_paths ) ? $plugin_paths[ $base_folder ] : false;
			$plugin_list[ $key ] = array(
				array(
					'name'   => $plugin['Name'],
					'active' => in_array( $key, $active_plugins ),
					'path'   => $plugin_path,
				),
			);
		}

		return $plugin_list;
	}

	/**
	 *
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public function check_plugin_exclusions( $plugin ) {

		// Exclude MDB plugins
		$plugin_exclusions = apply_filters( 'wpmdbtp_plugin_list', array( 'wp-migrate-db' ) );

		foreach ( $plugin_exclusions as $exclusion ) {
			if ( stristr( $plugin, $exclusion ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array|bool|mixed|void
	 */
	protected function get_active_plugins() {
		$active_plugins = get_option( 'active_plugins' );

		if ( is_multisite() ) {

			// get active plugins for the network
			$network_plugins = get_site_option( 'active_sitewide_plugins' );
			if ( $network_plugins ) {
				$network_plugins = array_keys( $network_plugins );
				$active_plugins  = array_merge( $active_plugins, $network_plugins );
			}
		}

		return $active_plugins;
	}

	/**
	 * @param $site_details
	 *
	 * @return mixed
	 */
	public function filter_site_details( $site_details ) {
		$folder_writable = $this->receiver->is_tmp_folder_writable( 'themes' );

		$site_details['plugins']                   = $this->get_local_plugins();
		$site_details['plugins_path']              = $this->slash_one_direction( WP_PLUGIN_DIR );
		$site_details['themes']                    = $this->get_local_themes();
		$site_details['themes_path']               = $this->slash_one_direction( WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;
		$site_details['content_dir']               = $this->slash_one_direction( WP_CONTENT_DIR );
		$site_details['local_tmp_folder_check']    = $folder_writable;
		$site_details['local_tmp_folder_writable'] = $folder_writable['status'];
		$site_details['transfer_bottleneck']       = $this->get_transfer_bottleneck();
		$site_details['max_request_size']          = $this->get_bottleneck();
		$site_details['php_os']                    = PHP_OS;

		return $site_details;
	}

	/**
	 *
	 * @return int
	 */
	public function get_transfer_bottleneck() {
		$bottleneck = $this->get_max_upload_size();

		// Subtract 250 KB from min for overhead
		$bottleneck -= 250000;

		return $bottleneck;
	}

	/*
	 * Finalize T&P migration if necessary. Always runs on destination site.
	 */
	public function maybe_finalize_tp_migration() {
		if ( isset( $this->wpmdbpro->state_data['stage'] ) && ! in_array( $this->wpmdbpro->state_data['stage'], array( 'themes', 'plugins' ) ) ) {
			return false;
		}

		// Check that the number of files transferred is correct, throws exception
		$this->verify_file_transfer();

		// TODO: state_data['files_to_migrate'] does not get set on push
		$state_data = $this->wpmdbpro->state_data;
		$form_data  = $this->parse_migration_form_data( $state_data['form_data'] );

		if ( ! isset( $form_data['migrate_themes'] ) && ! isset( $form_data['migrate_plugins'] ) ) {
			return;
		}

		$files_to_migrate = array(
			'themes'  => ( isset( $form_data['migrate_themes'] ) && is_array( $form_data['select_themes'] ) ) ? $form_data['select_themes'] : array(),
			'plugins' => ( isset( $form_data['migrate_plugins'] ) && is_array( $form_data['select_plugins'] ) ) ? $form_data['select_plugins'] : array(),
		);

		foreach ( $files_to_migrate as $stage => $folder ) {
			$dest_path = trailingslashit( ( 'plugins' === $stage ) ? WP_PLUGIN_DIR : WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' );
			$tmp_path  = Receiver::get_temp_dir() . $stage . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
			foreach ( $folder as $file_folder ) {
				$folder_name = basename( str_replace( '\\', '/', $file_folder ) );
				$dest_folder = $dest_path . $folder_name;
				$tmp_source  = $tmp_path . $folder_name;
				$return      = $this->move_folder_into_place( $tmp_source, $dest_folder, $stage );

				if ( is_wp_error( $return ) ) {
					$this->transfer_helpers->ajax_error( $return->get_error_message() );
				}
			}
		}
	}

	/**
	 * @param string $source
	 * @param string $dest
	 * @param string $stage
	 *
	 * @return bool|WP_Error
	 */
	public function move_folder_into_place( $source, $dest, $stage ) {

		$fs          = $this->filesystem;
		$dest_backup = false;

		if ( ! $fs->file_exists( $source ) ) {
			$message = sprintf( __( 'Temporary file not found when finalizing Theme & Plugin Files migration: %s ', 'wp-migrate-db-pro-theme-plugin-files' ), $source );
			$this->log_error( $message );
			error_log( $message );

			return new WP_Error( 'wpmdbpro_theme_plugin_files_error', $message );
		}

		if ( $fs->file_exists( $dest ) ) {
			if ( ! $fs->is_writable( $dest ) ) {
				$message = sprintf( __( 'Unable to overwrite destination file when finalizing Theme & Plugin Files migration: %s', 'wp-migrate-db-pro-theme-plugin-files' ), $source );
				$this->log_error( $message );
				error_log( $message );

				return new WP_Error( 'wpmdbpro_theme_plugin_files_error', $message );
			}

			$backup_dir = Receiver::get_temp_dir() . $stage . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR;
			if ( ! $fs->is_dir( $backup_dir ) ) {
				$fs->mkdir( $backup_dir );
			}
			$dest_backup = $backup_dir . basename( $dest ) . '.' . time() . '.bak';
			$dest_backup = $fs->move( $dest, $dest_backup ) ? $dest_backup : false;
		}

		if ( ! $fs->move( $source, $dest ) ) {
			$message = sprintf( __( 'Unable to move file into place when finalizing Theme & Plugin Files migration. Source: %s | Destination: %s', 'wp-migrate-db-pro-theme-plugin-files' ), $source, $dest );
			$this->log_error( $message );
			error_log( $message );

			// attempt to restore backup
			if ( $dest_backup ) {
				$fs->move( $dest_backup, $dest );
			}

			return new WP_Error( 'wpmdbpro_theme_plugin_files_error', $message );
		}

		return true;
	}

	public function cleanup_transfer_migration() {
		$manager = new \WPMDB\Queue\Manager( $this );
		$manager->drop_tables();

		$this->remove_tmp_files();
	}

	public function remove_tmp_files() {
		$this->transfer_helpers->remove_tmp_folder( 'themes' );
		$this->transfer_helpers->remove_tmp_folder( 'plugins' );

		$this->remove_chunk_file();
	}

	public function remove_chunk_file() {
		if ( isset( $this->wpmdbpro->state_data['migration_state_id'] ) ) {
			$chunk_file = \WPMDB\Transfers\Files\Chunker::get_chunk_path( $this->wpmdbpro->state_data['migration_state_id'] );
			if ( $this->filesystem->file_exists( $chunk_file ) ) {
				$this->filesystem->unlink( $chunk_file );
			}
		}
	}

	/**
	 *
	 * Fires on the `wpmdb_before_finalize_migration` hook
	 *
	 * @throws Exception
	 */
	public function verify_file_transfer() {
		if ( isset( $this->wpmdbpro->state_data['stage'] ) && ! in_array( $this->wpmdbpro->state_data['stage'], array( 'themes', 'plugins' ) ) ) {
			return false;
		}

		$stages     = array();
		$form_data  = $this->wpmdbpro->form_data;
		$state_data = $this->wpmdbpro->state_data;

		if ( isset( $form_data['migrate_themes'] ) && '1' === $form_data['migrate_themes'] ) {
			$stages[] = 'themes';
		}

		if ( isset( $form_data['migrate_plugins'] ) && '1' === $form_data['migrate_plugins'] ) {
			$stages[] = 'plugins';
		}

		$migration_key = isset( $state_data['type'] ) && 'push' === $state_data['type'] ? $state_data['remote_state_id'] : $state_data['migration_state_id'];

		foreach ( $stages as $stage ) {
			$filename      = '.' . $migration_key . '-manifest';
			$manifest_path = Receiver::get_temp_dir() . $stage . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $filename;
			$queue_info    = unserialize( file_get_contents( $manifest_path ) );

			if ( ! $queue_info ) {
				throw new \Exception( sprintf( __( 'Unable to verify file migration, %s does not exist.' ), $manifest_path ) );
			}

			if ( ! isset( $queue_info['total'] ) ) {
				continue;
			}

			try {
				// Throws exception
				$this->transfer_helpers->check_manifest( $queue_info['manifest'], $stage );
			} catch ( \Exception $e ) {
				$this->end_ajax( json_encode( array( 'wpmdb_error' => 1, 'body' => $e->getMessage() ) ) );
			}
		}
	}
}
