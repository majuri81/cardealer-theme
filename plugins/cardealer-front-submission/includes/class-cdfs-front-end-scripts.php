<?php
/**
 * Handle frontend scripts.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CDFS_Frontend_Scripts Class.
 */
class CDFS_Frontend_Scripts {

	/**
	 * Static
	 *
	 * @var $scripts .
	 */
	private static $scripts = array();
	/**
	 * Static
	 *
	 * @var $styles .
	 */
	private static $styles = array();
	/**
	 * Static
	 *
	 * @var $wp_localize_scripts .
	 */
	private static $wp_localize_scripts = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
	}
	
	public static function is_preview_mode() {
		$preview_mode = false;
		$actions      = [
			'elementor',
			'elementor_get_templates',
			'elementor_save_template',
			'elementor_get_template',
			'elementor_delete_template',
			'elementor_import_template',
			'elementor_library_direct_actions',
		];

		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $actions ) || isset( $_REQUEST['elementor-preview'] ) ) {
			$preview_mode = true;
		}

		return $preview_mode;
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {
		global $post,$wp;

		if ( ! did_action( 'cdfs_init_action' ) ) {
			return;
		}

		// Apply only plugin pages.
		if ( cdfs_is_user_account_page() || self::is_preview_mode() ) {
			self::add_scripts();
			self::add_styles();
		}
	}

	/**
	 * Add styles for use.
	 */
	public static function add_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'cdhl-css-helper-admin', trailingslashit( CDFS_URL ) . 'css/cdfs-helper' . $suffix . '.css', false, true );
		wp_enqueue_style( 'cdhl-css-forms-admin', trailingslashit( CDFS_URL ) . 'css/cdfs-forms' . $suffix . '.css', false, true );
		wp_enqueue_style( 'cdfs-dealer-dashboard', trailingslashit( CDFS_URL ) . 'css/cdfs-dealer-dashboard' . $suffix . '.css', false, true );
		wp_enqueue_style( 'jquery-confirm' );
	}

	/**
	 * Add scripts.
	 */
	public static function add_scripts() {
		global $car_dealer_options;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$google_maps_api = ( isset( $car_dealer_options['google_maps_api'] ) && ! empty( $car_dealer_options['google_maps_api'] ) ) ? $car_dealer_options['google_maps_api'] : '';

		wp_register_script( 'cdfs-helper-sortable', trailingslashit( CDFS_URL ) . 'js/jquery-ui' . $suffix . '.js', array( 'jquery' ), '1.13.1', true );
		wp_register_script( 'cdfs-helper-js', trailingslashit( CDFS_URL ) . 'js/cdfs-helper' . $suffix . '.js', array( 'jquery' ), CDFS_VERSION, true );
		wp_register_script( 'cdfs-form_validation', trailingslashit( CDFS_URL ) . 'js/cdfs-form_validation' . $suffix . '.js', array( 'jquery' ), CDFS_VERSION, true );

		wp_register_script( 'cdfs-google-recaptcha-apis', 'https://www.google.com/recaptcha/api.js?&render=explicit', array(), '1.2.8.1', true );
		wp_register_script( 'cardealer-google-maps-apis', '//maps.google.com/maps/api/js?sensor=false&libraries=places&key=' . $google_maps_api, array(), '1.2.8.1', true );
		wp_register_script( 'cdhl-google-location-picker', trailingslashit( CDFS_URL ) . 'js/google-map/locationpicker/locationpicker.jquery' . $suffix . '.js', array( 'jquery', 'cardealer-google-maps-apis' ), '0.1.16', true );

		$car_model             = get_taxonomy( 'car_model' );
		$image_size_limit_text = cdfs_get_add_car_image_upload_size_limit_text();
		$model_field_label     = isset( $car_model->labels->singular_name ) ? $car_model->labels->singular_name : esc_html__( 'Model', 'cdfs-addon' );

		wp_localize_script(
			'cdfs-helper-js',
			'cdfs_obj',
			apply_filters(
				'cdfs_helper_js_localize_script',
				array(
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'model_field'            => sprintf( esc_html__( 'Select %s', 'cdfs-addon' ), $model_field_label ),
					'alerttxt'               => esc_html__( 'Alert', 'cdfs-addon' ),
					'errortxt'               => esc_html__( 'Error!', 'cdfs-addon' ),
					'delalerttex'            => esc_html__( 'This will delete without updating the vehicle. Are you sure want to delete it?', 'cdfs-addon' ),
					/* translators: %s: img limit */
					'imglimittxt'            => esc_html__( 'Sorry! You can upload at most {{limit}} images.', 'cdfs-addon' ),
					'select_package_err'     => esc_html__( 'Select a package to proceed.', 'cdfs-addon' ),
					'img_select_package_err' => esc_html__( 'No package selected. Select a package to proceed.', 'cdfs-addon' ),
					'imgtypetxt'             => esc_html__( 'The file(s) [file] is not an image.', 'cdfs-addon' ),
					// 'img_type_alert'         => esc_html__( 'One or more selected files are not image. Files: [file]', 'cdfs-addon' ),
					'img_type_error'         => esc_html__( 'One or more selected files are not image.', 'cdfs-addon' ),
					'img_type_title'         => esc_html__( 'File Type Error', 'cdfs-addon' ),
					'pdftypetxt'             => esc_html__( 'The file [file] is not a PDF file, Please upload PDF file only.', 'cdfs-addon' ),
					'exceededtxt'            => esc_html__( 'File size exceeded than 4 MB.', 'cdfs-addon' ),
					/* translators: %s: file size limit text */
					'size_exceed_error'      => sprintf( esc_html__( 'One or more selected file exceeds the allowed file size limit: %s.', 'cdfs-addon' ), $image_size_limit_text ),
					'size_exceed_title'      => esc_html__( 'Image Size Error', 'cdfs-addon' ),
					'redirectmsg'            => esc_html__( 'Redirecting to vehicle listing page.', 'cdfs-addon' ),
					'imageprocess'           => esc_html__( 'Processing the image files.', 'cdfs-addon' ),
					'vehicleprocess'         => esc_html__( 'Processing vehicle data.', 'cdfs-addon' ),
					'btn_delete'             => esc_html__( 'Delete', 'cdfs-addon' ),
				)
			)
		);
		
		wp_enqueue_script( 'cdfs-form_validation' );

		// jquery-confirm.
		wp_enqueue_script( 'jquery-confirm' );

		// Captcha js script.
		$captcha_sitekey    = cdfs_get_goole_api_keys( 'site_key' );
		$captcha_secret_key = cdfs_get_goole_api_keys( 'secret_key' );
		if ( isset( $captcha_secret_key ) && ! empty( $captcha_secret_key ) && isset( $captcha_sitekey ) && ! empty( $captcha_sitekey ) ) {
			wp_enqueue_script( 'cdfs-google-recaptcha-apis' );
		}

		if ( self::is_preview_mode() ) {
			wp_enqueue_script( 'cardealer-google-maps-apis' );
			wp_enqueue_script( 'cdhl-google-location-picker' );
			wp_enqueue_editor();
		}

		wp_enqueue_script( 'cdfs-helper-js' );
	}

}

CDFS_Frontend_Scripts::init();
