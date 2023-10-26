<?php
/**
 * Handle admin scripts.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CDFS_Admin_Scripts Class.
 */
class CDFS_Admin_Scripts {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_admin_scripts' ) );
	}

	/**
	 * Register/queue admin scripts.
	 */
	public static function load_admin_scripts() {
		global $post, $wp, $car_dealer_options;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$google_maps_api = ( isset( $car_dealer_options['google_maps_api'] ) && ! empty( $car_dealer_options['google_maps_api'] ) ) ? $car_dealer_options['google_maps_api'] : '';

		wp_register_script( 'cardealer-google-maps-apis', '//maps.google.com/maps/api/js?sensor=false&libraries=places&key=' . $google_maps_api, array(), CDFS_VERSION, true );
		wp_register_script( 'cdhl-google-location-picker', trailingslashit( CDFS_URL ) . 'js/google-map/locationpicker/locationpicker.jquery' . $suffix . '.js', array( 'jquery', 'cardealer-google-maps-apis' ), '0.1.16', true );
		wp_register_script( 'cdfs-admin-js', trailingslashit( CDFS_URL ) . 'js/cdfs-admin' . $suffix . '.js', array( 'jquery' ), CDFS_VERSION, true );

		wp_enqueue_script( 'cdfs-admin-js' );

		wp_enqueue_style( 'cdfs-admin-css', trailingslashit( CDFS_URL ) . 'css/cdfs-admin' . $suffix . '.css', false, true );

	}

}

CDFS_Admin_Scripts::init();
