<?php

use \WPMDB\Transfers\Files\Excludes;

/**
 * Class WPMDBPro_Theme_Plugin_Files_Local
 *
 * Handles local themes/plugins logic
 *
 */
class WPMDBPro_Theme_Plugin_Files_Local extends WPMDBPro_Addon {

	public $transfer_util;
	public $transfer_manager;
	public $file_processor;
	public $queueManager;
	public $receiver;

	public function __construct(
		$plugin_file_path,
		\WPMDB\Transfers\Files\Util $util,
		\WPMDB\Transfers\Files\FileProcessor $file_processor,
		\WPMDB\Queue\Manager $queue_manager,
		\WPMDB\Transfers\Files\TransferManager $transfer_manager,
		WPMDB\Transfers\Receiver $receiver
	) {
		parent::__construct( $plugin_file_path );

		$this->queueManager     = $queue_manager;
		$this->transfer_util    = $util;
		$this->file_processor   = $file_processor;
		$this->transfer_manager = $transfer_manager;
		$this->receiver         = $receiver;

		add_action( 'wp_ajax_wpmdb_initiate_file_migration', array( $this, 'ajax_initiate_file_migration' ) );
		add_action( 'wpmdb_initiate_migration', array( $this, 'transfer_check' ) );
		add_action( 'wp_ajax_wpmdb_get_queue_items', array( $this, 'ajax_get_queue_items' ) );
		add_action( 'wp_ajax_wpmdb_transfer_files', array( $this, 'ajax_transfer_files' ) );
	}
	
	/**
	 *
	 * @TODO Break this up into smaller, testable functions
	 * @return bool|null
	 */
	public function ajax_initiate_file_migration() {
		$this->check_ajax_referer( 'wpmdb-initiate-file-migration' );
		$this->set_time_limit();

		$key_rules = array(
			'action'             => 'key',
			'stage'              => 'string',
			'excludes'           => 'string',
			'migration_state_id' => 'key',
			'folders'            => 'string',
			'nonce'              => 'key',
		);

		$this->set_post_data( $key_rules );

		if ( empty( $this->state_data['folders'] ) ) {
			return $this->transfer_util->ajax_error( __( 'Error: empty folder list supplied.', 'wp-migrate-db' ) );
		}

		$excludes = isset( $this->state_data['excludes'] ) ? $this->state_data['excludes'] : '';
		$excludes = explode( '\n', str_replace( '"', '', $excludes ) );

		//State data populated
		$files = json_decode( $this->state_data['folders'] );

		if ( ! is_array( $files ) ) {
			return $this->transfer_util->ajax_error( __( 'Invalid folder list supplied (invalid array)', 'wp-migrate-db' ) );
		}

		// @TODO this needs to be implemented for remotes on a pull
		$verified_folders = $this->verify_files_for_migration( $files );

		if ( 'pull' === $this->state_data['intent'] ) {
			// Set up local meta data
			$file_list = $this->transfer_util->get_remote_files( $files, $this, 'wpmdbtp_respond_to_get_remote_' . $this->state_data['stage'], $excludes );
		} else {

			// Push = get local files
			$abs_path  = 'plugins' === $this->state_data['stage'] ? WP_PLUGIN_DIR : WP_CONTENT_DIR . '/themes/';
			$file_list = $this->file_processor->get_local_files( $verified_folders, $abs_path, $excludes, $this->state_data['stage'] );
		}

		if ( ! $file_list ) {
			$this->end_ajax( $file_list );
		}

		$queue_status = $this->populate_queue( $file_list, $this->state_data['intent'] );
		set_site_transient( 'wpmdb_queue_status', $queue_status );

		return $this->end_ajax( json_encode( [ 'queue_status' => $queue_status ] ) );
	}

	/**
	 * Get queue items in batches to populate the UI
	 *
	 * @return mixed|null
	 */
	public function ajax_get_queue_items() {
		$this->check_ajax_referer( 'wpmdb-get-queue-items' );
		$this->set_time_limit();

		$key_rules = array(
			'action'             => 'key',
			'stage'              => 'string',
			'migration_state_id' => 'key',
			'nonce'              => 'key',
		);

		$this->set_post_data( $key_rules );

		if ( empty( $this->state_data['folders'] ) ) {
			return $this->transfer_util->ajax_error( __( 'Error: empty folder list supplied.', 'wp-migrate-db' ) );
		}

		$queue_status = get_site_transient( 'wpmdb_queue_status' );
		$count        = apply_filters( 'wpmdb_tranfers_queue_batch_size', 1000 );
		$offset       = isset( $queue_status['offset'] ) ? $queue_status['offset'] : 0;

		$q_data = $this->queueManager->list_jobs( $count, $offset );

		if ( empty( $q_data ) ) {
			delete_site_transient( 'wpmdb_queue_status' );

			return $this->end_ajax( json_encode( [ 'status' => 'complete' ] ) );
		}

		$file_data  = $this->process_file_data( $q_data );
		$result_set = $this->transfer_util->process_queue_data( $file_data, $this->state_data, 0 );

		$queue_status['offset'] = $offset + $count;
		set_site_transient( 'wpmdb_queue_status', $queue_status );

		return $this->end_ajax( json_encode( [ 'queue_status' => $queue_status, 'items' => $result_set ] ) );
	}

	/**
	 *
	 * Fires on `wpmdb_initiate_migration`
	 *
	 * @param $state_data
	 *
	 * @return null
	 */
	public function transfer_check( $state_data ) {
		$message   = null;
		$form_data = $this->parse_migration_form_data( $state_data['form_data'] );

		if ( ! isset( $form_data['migrate_themes'] ) && ! isset( $form_data['migrate_plugins'] ) ) {
			return;
		}

		if ( ! isset( $state_data['intent'] ) ) {
			$this->log_error( 'Unable to determine migration intent - $state_data[\'intent\'] empty' );

			return $this->end_ajax( json_encode( [
				'wpmdb_error' => 1,
				'body'        => __( 'A problem occured starting the Theme & Plugin files migration.', 'wp-migrate-db' ),
			] ) );
		}

		$key                 = 'push' === $state_data['intent'] ? 'remote' : 'local';
		$site_details        = $state_data['site_details'][ $key ];
		$tmp_folder_writable = $site_details['local_tmp_folder_writable'];

		// $tmp_folder_writable is `null` if remote doesn't have T&P addon installed
		if ( false !== $tmp_folder_writable ) {
			return;
		}

		$tmp_folder_error_message = isset( $site_details['local_tmp_folder_check']['message'] ) ? $site_details['local_tmp_folder_check']['message'] : '';

		$error_message = __( 'Unfortunately it looks like we can\'t migrate your theme or plugin files. However, running a migration without theme and plugin files should work. Please uncheck the Theme Files checkbox, uncheck the Plugin Files checkbox, and try your migration again.', 'wp-migrate-db' );
		$link          = 'https://deliciousbrains.com/wp-migrate-db-pro/doc/theme-plugin-files-errors/';
		$more          = __( 'More Details »', 'wp-migrate-db' );

		$message = sprintf( '<p class="t-p-error">%s</p><p class="t-p-error">%s <a href="%s" target="_blank">%s</a></p>', $error_message, $tmp_folder_error_message, $link, $more );

		return $this->end_ajax( json_encode( [
			'wpmdb_error' => 1,
			'body'        => $message,
		] ) );
	}

	/**
	 * @return null
	 */
	public function ajax_transfer_files() {
		$this->check_ajax_referer( 'wpmdb-transfer-files' );
		$this->set_time_limit();

		$key_rules = array(
			'action'             => 'key',
			'stage'              => 'string',
			'offset'             => 'numeric',
			'migration_state_id' => 'key',
			'nonce'              => 'key',
		);

		$this->set_post_data( $key_rules );
		$count = apply_filters( 'wpmdbtp_file_batch_size', 100 );
		$data  = $this->queueManager->list_jobs( $count );

		$processed = $this->process_file_data( $data );

		if ( empty( $data ) ) {
			do_action( 'wpmdbtp_file_transfer_complete' );

			// Clear out queue in case there is a next step
			$this->queueManager->truncate_queue();

			return $this->end_ajax( json_encode( [ 'status' => 'complete' ] ) );
		}

		$remote_url = $this->state_data['url'];
		$processed  = $this->transfer_manager->manage_file_transfer( $remote_url, $processed, $this->state_data );

		$result = [
			'status' => $processed,
		];

		//Client should check error status for files and if a 500 is encountered kill the migration stage
		return $this->end_ajax( json_encode( $result ) );
	}


	/**
	 * Process data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function process_file_data( $data ) {
		$result_set = [];

		if ( ! empty( $data ) ) {
			foreach ( $data as $size => $record ) {
				$display_path                  = $record->file['subpath'];
				$record->file['relative_path'] = $display_path;

				$result_set[] = $record->file;
			}
		}

		return $result_set;
	}

	/**
	 *
	 * @param array $file_data
	 *
	 * @return mixed
	 */
	protected function populate_queue( $file_data, $intent ) {
		foreach ( $file_data['files'] as $item ) {
			if ( is_array( $item ) ) {
				$this->transfer_util->enqueue_files( $item, $this->queueManager );
			}
		}

		$queue_status = [
			'total'    => $file_data['meta']['count'],
			'size'     => $file_data['meta']['size'],
			'manifest' => $file_data['meta']['manifest'],
		];

		if ( 'pull' === $intent ) {
			$this->transfer_util->remove_tmp_folder( $this->state_data['stage'] );
			try {
				$this->transfer_util->save_queue_status( $queue_status, $this->state_data['stage'], $this->state_data['migration_state_id'] );
			} catch ( \Exception $e ) {
				return $this->transfer_util->ajax_error( sprintf( __( 'Unable to save local queue status - %s', 'wp-migrate-db' ), $e->getMessage() ) );
			}
		}

		// Push
		try {
			$this->transfer_util->save_queue_status_to_remote( $queue_status, $this, 'wpmdbtp_respond_to_save_queue_status' );
		} catch ( Exception $e ) {
			$this->transfer_util->ajax_error( $e->getMessage() );
		}

		// Manifest can get quite large, so remove it once it's no longer needed
		unset( $queue_status['manifest'] );

		return $queue_status;
	}

	public function verify_files_for_migration( $files ) {
		$paths = [];

		foreach ( $files as $file ) {
			if ( $this->filesystem->file_exists( $file ) ) {
				$paths[] = $file;
			}
		}

		return $paths;
	}
}
