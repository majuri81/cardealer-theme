<?php
/**
 * My Account Shortcodes
 *
 * @author      PotenzaGlobalSolutions
 * @category    Shortcodes
 * @package     CDFS
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shows the 'my account' section where the customer can view past orders and update their information.
 *
 * @category Shortcodes
 * @package  CDFS/Shortcodes/Add_Car
 * @author   PotenzaGlobalSolutions
 */
class CDFS_Shortcode_Add_Car {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts attributes.
	 * @return string
	 */
	public static function get( $atts ) {
		return CDFS_Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @param array $atts sttributes.
	 */
	public static function output( $atts ) {
		global $wp;
		if ( ! is_user_logged_in() ) {
			$message = apply_filters( 'cardealer_add_car_message', '' );

			if ( ! empty( $message ) ) {
				cdfs_add_notice( $message );
			}

			if ( ! empty( $_GET['invalid-role'] ) ) {
				cdfs_add_notice( esc_html__( 'Please login with "Dealer Or Customer" account.', 'cdfs-addon' ), 'error' );
			}

			// check for user activation token.
			if ( isset( $_GET['usr-activate'] ) && ! empty( $_GET['usr-activate'] ) ) {
				cdfs_activate_user_account_by_token( cdfs_clean( trim( $_GET['usr-activate'] ) ) );
			}

			wp_enqueue_script( 'cardealer-google-maps-apis' );
			wp_enqueue_script( 'cdhl-google-location-picker' );
			cdfs_get_template( 'cars/cars-add.php', $atts );
		} else {
			wp_enqueue_script( 'cardealer-google-maps-apis' );
			wp_enqueue_script( 'cdhl-google-location-picker' );
			cdfs_get_template( 'cars/cars-add.php', $atts );
		}
	}

}
