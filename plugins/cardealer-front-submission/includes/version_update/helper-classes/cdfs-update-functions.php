<?php
/**
 * Car Dealer Front End Submission Addon Updater Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @author   PotenzaGlobalSolutions
 * @package  CDFS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'CDFS_update_100_version' ) ) {
	/**
	 * Execute new updates.
	 */
	function CDFS_update_100_version() {

	}
}

if ( ! function_exists( 'CDFS_update_100_db_version' ) ) {
	/**
	 * Update DB Version.
	 */
	function CDFS_update_100_db_version() {
		CDFS::cdfs_update_db_version( '1.0.0' );
	}
}
