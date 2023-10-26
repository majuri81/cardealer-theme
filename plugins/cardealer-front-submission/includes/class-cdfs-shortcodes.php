<?php
/**
 * Shortcodes
 *
 * @author   PotenzaGlobalSolutions
 * @category Class
 * @package  CDFS/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CDFS Shortcodes class.
 */
class CDFS_Shortcodes {

	/**
	 * Init shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'cardealer_dealer_login' => __CLASS__ . '::dealer_login',
			'cardealer_add_car'      => __CLASS__ . '::add_car',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}

	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function Callback function.
	 * @param array    $atts     Attributes. Default to empty array.
	 * @param array    $wrapper  Customer wrapper data.
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'cdfs',
			'before' => null,
			'after'  => null,
		)
	) {
		ob_start();

		// @codingStandardsIgnoreStart
		// echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		// echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];
		// @codingStandardsIgnoreEnd

		return ob_get_clean();
	}

	/**
	 * Dealer login page shortcode.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function dealer_login( $atts ) {
		return self::shortcode_wrapper( array( 'CDFS_Shortcode_Dealer_Login', 'output' ), $atts );
	}

	/**
	 * Add car page shortcode.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function add_car( $atts ) {
		return self::shortcode_wrapper( array( 'CDFS_Shortcode_Add_Car', 'output' ), $atts );
	}
}
