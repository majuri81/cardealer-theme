<?php
/**
 * CDFS Logger.
 *
 * @package  CDFS
 * @author   PotenzaGlobalSolutions
 */

/**
 * CDFS Logger
 */
trait CDFS_Logger {

	/**
	 * Log
	 *
	 * @param string $message message.
	 * @param string $version version.
	 * @param string $type type.
	 */
	public static function log( $message, $version = '', $type = '' ) {
		$file    = CDFS_LOG_DIR . 'cdfs_' . $version . '_' . date_i18n( 'm-d-Y' ) . '.txt';
		$open    = fopen( $file, 'a' );
		$log_txt = date_i18n( 'm-d-Y @ H:i:s' ) . ' ' . ' ' . $type . ' ' . $message . "\n";
		$write   = fputs( $open, $log_txt );
		fclose( $open );
	}
}
