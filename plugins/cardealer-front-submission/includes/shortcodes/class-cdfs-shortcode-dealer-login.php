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
 * @package  CDFS/Shortcodes/Dealer_Login
 * @author   PotenzaGlobalSolutions
 */
class CDFS_Shortcode_Dealer_Login {

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
			$message = apply_filters( 'cardealer_dealer_login_message', '' );

			if ( ! empty( $message ) ) {
				cdfs_add_notice( $message );
			}
			if ( ! empty( $_GET['password-reset-link-sent'] ) ) {
				cdfs_add_notice( esc_html__( 'Password reset link is successfully sent to your email address, please check.', 'cdfs-addon' ), 'success' );
			}

			if ( ! empty( $_GET['invalid-role'] ) ) {
				cdfs_add_notice( esc_html__( 'Please login with "Dealer Or Customer" account.', 'cdfs-addon' ), 'error' );
			}

			// After password reset, add confirmation message.
			if ( ! empty( $_GET['password-reset-done'] ) ) {
				cdfs_add_notice( esc_html__( 'Your password has been reset successfully.', 'cdfs-addon' ), 'success' );
			}

			// check for user activation token.
			if ( isset( $_GET['usr-activate'] ) && ! empty( $_GET['usr-activate'] ) ) {
				cdfs_activate_user_account_by_token( cdfs_clean( trim( $_GET['usr-activate'] ) ) );
			}

			if ( isset( $_GET['cdfs-action'] ) && 'lostpassword' === $_GET['cdfs-action'] ) {
				cdfs_lost_user_password();
			} else {
				cdfs_get_template( 'user-dashboard/login.php' );
			}
		}
	}

}
