<?php
/**
 * Background Updater
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    CDFS_Background_Updater
 * @version  1.0.3
 * @package  CDFS/Classes
 * @category Class
 * @author   PotenzaGlobalSolutions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_CDFS_Async_Request', false ) ) {
	include_once CDFS_ABSPATH . 'includes/version_update/libraries/wp-cdfs-async-request.php';
}

if ( ! class_exists( 'WP_CDFS_Background_Process', false ) ) {
	include_once CDFS_ABSPATH . 'includes/version_update/libraries/wp-cdfs-background-process.php';
}

require_once 'class-cdfs-logger.php'; // LOGGER TRAIT.

/**
 * Background_Updater Class.
 */
class CDFS_Background_Updater extends WP_CDFS_Background_Process {

	/**
	 * USING LOGGER TRAIT FOR LOGGIN PROCESS
	 *
	 * @var string
	 */
	protected $action = 'CDFS_updater';

	use CDFS_Logger;
	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
	 */
	public function dispatch() {
		$dispatched = parent::dispatch();
		if ( is_wp_error( $dispatched ) ) {
			CDFS::log(
				/* translators: $s: Unable to dispatch Car Dealer Front End Submission Addon Updater */
				sprintf( esc_html__( 'Unable to dispatch Car Dealer Front End Submission Addon Updater updater: %s', 'cdfs-addon' ), $dispatched->get_error_message() ),
				CDFS_VERSION,
				'ERROR'
			);
		}
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function.
	 * @return mixed
	 */
	protected function task( $callback ) {
		if ( ! defined( 'CDFS_UPDATING' ) ) {
			define( 'CDFS_UPDATING', true );
		}

		include_once dirname( __FILE__ ) . '/cdfs-update-functions.php';

		if ( is_callable( $callback ) ) {
			CDFS::log(
				/* translators: $s: Running */
				sprintf( esc_html__( 'Running %s callback', 'cdfs-addon' ), $callback ),
				CDFS_VERSION,
				'INFO'
			);
			call_user_func( $callback );
			CDFS::log(
				/* translators: $s: Finished */
				sprintf( esc_html__( 'Finished %s callback', 'cdfs-addon' ), $callback ),
				CDFS_VERSION,
				'INFO'
			);
		} else {
			CDFS::log(
				/* translators: $s: Could not find */
				sprintf( esc_html__( 'Could not find %s callback', 'cdfs-addon' ), $callback ),
				CDFS_VERSION,
				'NOTICE'
			);
		}
		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		CDFS::log(
			esc_html__( 'Data update complete', 'cdfs-addon' ),
			CDFS_VERSION,
			'INFO'
		);
		CDFS::CDFS_update_db_version();
		update_option( 'cdfs_version_status', 'updated' );
		parent::complete();
	}
}
