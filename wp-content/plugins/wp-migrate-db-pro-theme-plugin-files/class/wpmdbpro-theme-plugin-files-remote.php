<?php

/**
 * Class WPMDBPro_Theme_Plugin_Files
 *
 *
 */
class WPMDBPro_Theme_Plugin_Files_Remote extends WPMDBPro_Addon {

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

		add_action( 'wp_ajax_nopriv_wpmdbtp_respond_to_get_remote_themes', array( $this, 'ajax_respond_to_get_remote_themes' ) );
		add_action( 'wp_ajax_nopriv_wpmdbtp_respond_to_get_remote_plugins', array( $this, 'ajax_respond_to_get_remote_plugins' ) );
		add_action( 'wp_ajax_nopriv_wpmdbtp_respond_to_save_queue_status', array( $this, 'ajax_respond_to_save_queue_status' ) );
		add_action( 'wp_ajax_nopriv_wpmdb_transfers_send_file', array( $this, 'ajax_respond_to_request_files', ) );
		add_action( 'wp_ajax_nopriv_wpmdb_transfers_receive_file', array( $this, 'ajax_respond_to_post_file' ) );
		add_filter( 'wpmdb_establish_remote_connection_data', array( $this, 'establish_remote_connection_data' ) );
	}

	public function establish_remote_connection_data( $data ) {
		$receiver         = $this->receiver;
		$tmp_folder_check = $receiver->is_tmp_folder_writable( 'themes' );

		$data['remote_theme_plugin_files_available'] = true;
		$data['remote_theme_plugin_files_version']   = $this->plugin_version;
		$data['remote_tmp_folder_check']             = $tmp_folder_check;
		$data['remote_tmp_folder_writable']          = $tmp_folder_check['status'];

		return $data;
	}

	public function ajax_respond_to_get_remote_themes() {
		$this->respond_to_get_remote_folders( 'themes' );
	}

	public function ajax_respond_to_get_remote_plugins() {
		$this->respond_to_get_remote_folders( 'plugins' );
	}

	/**
	 * @param $stage
	 *
	 * @return mixed|null
	 */
	public function respond_to_get_remote_folders( $stage ) {
		add_filter( 'wpmdb_before_response', array( $this, 'scramble' ) );

		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'intent'          => 'key',
			'folders'         => 'string',
			'excludes'        => 'string',
			'stage'           => 'string',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );

		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'intent',
			'folders',
			'excludes',
			'stage',
		) );

		$verification = $this->verify_signature( $filtered_post, $this->settings['key'] );

		if ( ! $verification ) {
			return $this->transfer_util->ajax_error( $this->invalid_content_verification_error . ' (#100tp)', $filtered_post );
		}

		$abs_path = 'plugins' === $stage ? WP_PLUGIN_DIR : WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;
		$files    = $this->file_processor->get_local_files( unserialize( $this->state_data['folders'] ), $this->slash_one_direction( $abs_path ), unserialize( $this->state_data['excludes'] ), $stage );

		if ( empty( $files ) ) {
			return $this->end_ajax( __( 'No files returned from the remote server.', 'wp-migrate-db' ) . ' (#101tp)' );
		}

		// @TODO potentially use streaming in future
		$str = serialize( $files );

		return $this->end_ajax( $str );
	}

	/**
	 *
	 * Fired off a nopriv AJAX hook that listens to pull requests for file batches
	 *
	 * @return mixed
	 */
	public function ajax_respond_to_request_files() {

		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'stage'           => 'string',
			'intent'          => 'string',
			'bottleneck'      => 'numeric',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );
		$filtered_post = $this->filter_post_elements( $this->state_data, array(
			'action',
			'remote_state_id',
			'stage',
			'intent',
			'bottleneck',
		) );

		$settings = $this->settings;

		if ( ! $this->verify_signature( $filtered_post, $settings['key'] ) ) {
			return $this->transfer_util->ajax_error( $this->invalid_content_verification_error . ' (#100tp)', $filtered_post );
		}

		$sender = new \WPMDB\Transfers\Sender( $this, $this->transfer_util, new \WPMDB\Transfers\Files\Payload( $this, $this->transfer_util, new \WPMDB\Transfers\Files\Chunker( $this, $this->transfer_util ) ) );

		try {
			$sender->respond_to_send_file( $this->state_data );
		} catch ( \Exception $e ) {
			$this->transfer_util->catch_general_error( $e->getMessage() );
		}
	}

	/**
	 *
	 * Respond to request to save queue status
	 *
	 * @return mixed|null
	 */
	public function ajax_respond_to_save_queue_status() {
		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'stage'           => 'string',
			'intent'          => 'string',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );
		$state_data    = $this->state_data;
		$filtered_post = $this->filter_post_elements( $state_data, array(
			'action',
			'remote_state_id',
			'intent',
			'stage',
		) );

		$settings = $this->settings;

		if ( ! $this->verify_signature( $filtered_post, $settings['key'] ) ) {
			return $this->transfer_util->ajax_error( $this->invalid_content_verification_error . ' (#100tp)', $filtered_post );
		}

		if ( empty( $_POST['queue_status'] ) ) {
			return $this->transfer_util->ajax_error( __( 'Saving queue status to remote failed.' ) );
		}

		$queue_status = filter_var( $_POST['queue_status'], FILTER_SANITIZE_STRING );
		$queue_data   = unserialize( gzdecode( base64_decode( $queue_status ) ) );

		if ( $queue_data ) {
			$this->transfer_util->remove_tmp_folder( $state_data['stage'] );

			try {
				$this->transfer_util->save_queue_status( $queue_data, $state_data['stage'], $state_data['remote_state_id'] );
			} catch ( \Exception $e ) {
				return $this->transfer_util->ajax_error( sprintf( __( 'Unable to save remote queue status - %s', 'wp-migrate-db' ), $e->getMessage() ) );
			}

			return $this->end_ajax( json_encode( true ) );
		}
	}

	/**
	 *
	 * Receive POSTed file data
	 *
	 * @throws Exception
	 */
	public function ajax_respond_to_post_file() {
		$key_rules = array(
			'action'          => 'key',
			'remote_state_id' => 'key',
			'stage'           => 'string',
			'intent'          => 'string',
			'sig'             => 'string',
		);

		$this->set_post_data( $key_rules, 'remote_state_id' );
		$state_data    = $this->state_data;
		$filtered_post = $this->filter_post_elements( $state_data, array(
			'action',
			'remote_state_id',
			'stage',
			'intent',
		) );

		$settings = $this->settings;

		if ( ! $this->verify_signature( $filtered_post, $settings['key'] ) || ! isset( $_POST['content'] ) ) {
			throw new \Exception( __( 'Failed to respond to payload post.', 'wp-migrate-db' ) );
		}

		$payload_content = filter_var( $_POST['content'], FILTER_SANITIZE_STRING );
		$receiver        = $this->receiver;

		try {
			$receiver->receive_post_data( $state_data['stage'], $payload_content );
		} catch ( \Exception $e ) {
			return $this->transfer_util->catch_general_error( $e->getMessage() );
		}
	}
}
