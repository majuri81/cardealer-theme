<?php
/**
 * CDFS class cache helper.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * CDFS_Cache_Helper class.
 *
 * @author PotenzaGlobalSolutions
 */
class CDFS_Cache_Helper {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
	}

	/**
	 * Get prefix for use with wp_cache_set. Allows all cache in a group to be invalidated at once.
	 *
	 * @param  bool $group cache bool.
	 * @return string
	 */
	public static function get_cache_prefix( $group ) {
		$prefix = wp_cache_get( 'cdfs_' . $group . '_cache_prefix', $group );

		if ( false === $prefix ) {
			$prefix = 1;
			wp_cache_set( 'cdfs_' . $group . '_cache_prefix', $prefix, $group );
		}

		return 'cdfs_cache_' . $prefix . '_';
	}



	/**
	 * Notices function.
	 */
	public static function notices() {
		if ( ! function_exists( 'w3tc_pgcache_flush' ) || ! function_exists( 'w3_instance' ) ) {
			return;
		}

		$config   = w3_instance( 'W3_Config' );
		$enabled  = $config->get_integer( 'dbcache.enabled' );
		$settings = array_map( 'trim', $config->get_array( 'dbcache.reject.sql' ) );

		if ( $enabled && ! in_array( '_cdfs_session_', $settings ) ) {
			?>
			<div class="error">
				<p>
					<?php
					/* translators: %s: url */
					printf( __( 'In order for <strong>database caching</strong> to work with Car dealer Frontend Submission, you must add %1$s to the "Ignored Query Strings" option in <a href="%2$s">W3 Total Cache settings</a>.', 'cdfs-addon' ), '<code>_cdfs_session_</code>', admin_url( 'admin.php?page=w3tc_dbcache' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
					?>
				</p>
			</div>
			<?php
		}
	}
}

CDFS_Cache_Helper::init();
