<?php
/**
 * CDFS Core Functions
 *
 * @author   PotenzaGlobalSolutions
 * @category Class
 * @package  CDFS/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-includes/pluggable.php';


if ( ! function_exists( 'cdfs_get_shortcode_templates' ) ) {
	/**
	 * Get shortcode template parts.
	 *
	 * @param string $slug .
	 * @param string $name .
	 * @param string $atts .
	 */
	function cdfs_get_shortcode_templates( $slug, $name = '', $atts = '' ) {
		if ( ! empty( $atts ) && is_array( $atts ) ) {
			extract( $atts );
		}

		$template = '';

		$template_path = 'cardealer-front-submission/templates/';
		$plugin_path   = trailingslashit( CDFS_PATH );
		// Look in yourtheme/template-parts/shortcodes/slug-name.php.
		if ( $name ) {
			$template = locate_template(
				array(
					$template_path . "{$slug}-{$name}.php",
				)
			);
		}

		// Get default slug-name.php.
		if ( ! $template && $name && file_exists( $plugin_path . "templates/{$slug}-{$name}.php" ) ) {
			$template = $plugin_path . "templates/{$slug}-{$name}.php";
		}

		// If template file doesn't exist, look in yourtheme/template-parts/shortcodes/slug.php.
		if ( ! $template ) {
			$template = locate_template(
				array(
					$template_path . "{$slug}.php",
				)
			);
		}

		// Get default slug.php.
		if ( ! $template && file_exists( $plugin_path . "templates/{$slug}.php" ) ) {
			$template = $plugin_path . "templates/{$slug}.php";
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'cdfs_get_shortcode_templates', $template, $slug, $name );

		if ( $template ) {
			include $template;
		}
	}
}


if ( ! function_exists( 'cdfs_get_template' ) ) {
	/**
	 * Get other templates.
	 *
	 * @access public
	 * @param string $template_name .
	 * @param array  $args (default: array()) .
	 * @param string $template_path (default: '') .
	 * @param string $default_path (default: '') .
	 */
	function cdfs_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}

		$located = cdfs_locate_template( $template_name, $template_path, $default_path );

		if ( ! file_exists( $located ) ) {
			return;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters( 'cdfs_get_template', $located, $template_name, $args, $template_path, $default_path );

		do_action( 'cdfs_before_template_part', $template_name, $template_path, $located, $args );

		include $located;

		do_action( 'cdfs_after_template_part', $template_name, $template_path, $located, $args );
	}
}

if ( ! function_exists( 'cdfs_locate_template' ) ) {
	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 *      yourtheme       /   $template_path  /   $template_name
	 *      yourtheme       /   $template_name
	 *      $default_path   /   $template_name
	 *
	 * @access public
	 * @param string $template_name .
	 * @param string $template_path (default: '') .
	 * @param string $default_path (default: '') .
	 * @return string
	 */
	function cdfs_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'cardealer-front-submission/templates';
		}

		if ( ! $default_path ) {
			$default_path = untrailingslashit( plugin_dir_path( CDFS_PLUGIN_FILE ) ) . '/templates/';
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		/**
		 * Filters template path of the requested template file.
		 *
		 * @since 1.0
		 * @param string    $template       Full Path of the template.
		 * @param string    $template_path  Template path in plugin for all template files.
		 * @param string    $template_name  Name of the template to locate.
		 * @visible         true
		 */
		return apply_filters( 'cdfs_locate_template', $template, $template_name, $template_path );
	}
}

if ( ! function_exists( 'cdfs_get_page_permalink' ) ) {
	/**
	 * Retrieve page permalink.
	 *
	 * @param string $page .
	 * @return string
	 */
	function cdfs_get_page_permalink( $page ) {
		$page_id   = cdfs_get_page_id( $page );
		$permalink = 0 < $page_id ? get_permalink( $page_id ) : get_home_url();
		return apply_filters( 'cdfs_get_' . $page . '_page_permalink', $permalink );
	}
}

if ( ! function_exists( 'cdfs_get_page_id' ) ) {
	/**
	 * Retrieve page ids. returns -1 if no page is found.
	 *
	 * @param string $page .
	 * @return int
	 */
	function cdfs_get_page_id( $endpoint ) {
		$page_id = apply_filters( 'cdfs_get_' . $endpoint . '_page_id', get_option( 'cdfs_' . $endpoint . '_page_id' ), $endpoint );

		return $page_id ? absint( $page_id ) : -1;
	}
}


if ( ! function_exists( 'cdfs_create_page' ) ) {
	/**
	 * Create a page and store the ID in an option.
	 *
	 * @param mixed  $slug Slug for the new page .
	 * @param string $option Option name to store the page's ID .
	 * @param string $page_title (default: '') Title for the new page .
	 * @param string $page_content (default: '') Content for the new page .
	 * @param int    $post_parent (default: 0) Parent for the new page .
	 * @return int page ID
	 */
	function cdfs_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
		global $wpdb;

		$option_value = get_option( $option );

		if ( $option_value > 0 && ( $page_object = get_post( $option_value ) ) ) {
			if ( 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ) ) ) {
				// Valid page is already in place.
				return $page_object->ID;
			}
		}

		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
		}

		$valid_page_found = apply_filters( 'cdfs_create_page_id', $valid_page_found, $slug, $page_content );

		if ( $valid_page_found ) {
			if ( $option ) {
				update_option( $option, $valid_page_found );
			}
			return $valid_page_found;
		}

		// Search for a matching valid trashed page.
		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
		}

		if ( $trashed_page_found ) {
			$page_id   = $trashed_page_found;
			$page_data = array(
				'ID'          => $page_id,
				'post_status' => 'publish',
			);
			wp_update_post( $page_data );
		} else {
			$page_data = array(
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => $slug,
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_parent'    => $post_parent,
				'comment_status' => 'closed',
			);
			$page_id   = wp_insert_post( $page_data );
		}

		if ( $option ) {
			update_option( $option, $page_id );
		}
		return $page_id;
	}
}

if ( ! function_exists( 'cdfs_add_notice' ) ) {
	/**
	 * Add and store a notice.
	 *
	 * @param string $message .
	 * @param string $notice_type .
	 */
	function cdfs_add_notice( $message, $notice_type = 'success' ) {
		if ( CDFS()->session ) {
			$notices = CDFS()->session->get( 'cdfs_notices', array() );
			// Backward compatibility.
			if ( 'success' === $notice_type ) {
				$message = apply_filters( 'cdfs_add_message', $message );
			}

			$notices[ $notice_type ][] = apply_filters( 'cdfs_add_' . $notice_type, $message );
			CDFS()->session->set( 'cdfs_notices', $notices );
		}
	}
}

if ( ! function_exists( 'cdfs_print_notices' ) ) {
	/**
	 * Print notices
	 */
	function cdfs_print_notices() {
		if ( CDFS()->session ) {
			$all_notices  = CDFS()->session->get( 'cdfs_notices' );
			$notice_types = apply_filters( 'cdfs_notice_types', array( 'error', 'success', 'notice' ) );
			foreach ( $notice_types as $notice_type ) {
				if ( cdfs_notice_count( $notice_type ) > 0 ) {
					cdfs_get_shortcode_templates(
						"notices/{$notice_type}",
						null,
						array(
							'messages' => array_filter( $all_notices[ $notice_type ] ),
						)
					);
				}
			}
			cdfs_clear_notices();
		}
	}
}

if ( ! function_exists( 'cdfs_set_notices' ) ) {
	/**
	 * Set all notices at once.
	 *
	 * @param string $notices .
	 */
	function cdfs_set_notices( $notices ) {
		CDFS()->session->set( 'cdfs_notices', $notices );
	}
}


if ( ! function_exists( 'cdfs_notice_count' ) ) {
	/**
	 * Get the count of notices added, either for all notices (default) or for one.
	 * particular notice type specified by $notice_type.
	 *
	 * @since 2.1
	 * @param string $notice_type The name of the notice type - either error, success or notice. [optional].
	 * @return int
	 */
	function cdfs_notice_count( $notice_type = '' ) {
		if ( ! did_action( 'cdfs_init_action' ) ) {
			return;
		}
		$notice_count = 0;
		$all_notices  = CDFS()->session->get( 'cdfs_notices', array() );

		if ( isset( $all_notices[ $notice_type ] ) ) {
			$notice_count = absint( count( $all_notices[ $notice_type ] ) );
		} elseif ( empty( $notice_type ) ) {
			foreach ( $all_notices as $notices ) {
				$notice_count += absint( count( $all_notices ) );
			}
		}
		return $notice_count;
	}
}


if ( ! function_exists( 'cdfs_setcookie' ) ) {
	/**
	 * Set a cookie - wrapper for setcookie using WP constants.
	 *
	 * @param string $name .
	 * @param string $value .
	 * @param string $expire .
	 * @param string $secure .
	 */
	function cdfs_setcookie( $name, $value, $expire = 0, $secure = false ) {
		if ( ! headers_sent() ) {
			setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure );
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			headers_sent( $file, $line );
			trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
		}
	}
}


if ( ! function_exists( 'is_user_logged_in' ) ) {
	/**
	 * Checks if the current visitor is a logged in user.
	 *
	 * @return bool True if user is logged in, false if not logged in.
	 */
	function is_user_logged_in() {
		$user = wp_get_current_user();
		if ( empty( $user->ID ) ) {
			return false;
		}
		return true;
	}
}

if ( ! function_exists( 'cdfs_clear_notices' ) ) {
	/**
	 * Unset all notices.
	 */
	function cdfs_clear_notices() {
		if ( ! did_action( 'cdfs_init_action' ) ) {
			return;
		}
		CDFS()->session->set( 'cdfs_notices', null );
	}
}

if ( ! function_exists( 'cdfs_get_notices' ) ) {
	/**
	 * Get notices
	 *
	 * @param string $notice_type .
	 */
	function cdfs_get_notices( $notice_type = '' ) {
		if ( ! did_action( 'cdfs_init_action' ) ) {
			return;
		}

		if ( ! CDFS()->session ) {
			return;
		}

		$all_notices = CDFS()->session->get( 'cdfs_notices', array() );
		if ( empty( $notice_type ) ) {
			$notices = $all_notices;
		} elseif ( isset( $all_notices[ $notice_type ] ) ) {
			$notices = $all_notices[ $notice_type ];
		} else {
			$notices = array();
		}
		return $notices;
	}
}

if ( ! function_exists( 'cdfs_get_reference_link' ) ) {
	/**
	 * Get referer link
	 */
	function cdfs_get_reference_link() {
		if ( function_exists( 'wp_get_raw_referer' ) ) {
			return wp_get_raw_referer();
		}

		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			return wp_unslash( $_REQUEST['_wp_http_referer'] );
		} elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			return wp_unslash( $_SERVER['HTTP_REFERER'] );
		}
		return false;
	}
}

if ( ! function_exists( 'cdfs_get_goole_api_keys' ) ) {
	/**
	 * Get google captcha keys
	 *
	 * @param string $key_type .
	 */
	function cdfs_get_goole_api_keys( $key_type = '' ) {
		global $car_dealer_options;
		if ( 'site_key' === $key_type ) {
			$key = ( isset( $car_dealer_options['google_captcha_site_key'] ) && ! empty( $car_dealer_options['google_captcha_site_key'] ) ) ? $car_dealer_options['google_captcha_site_key'] : '';
		}
		if ( 'secret_key' === $key_type ) {
			$key = ( isset( $car_dealer_options['google_captcha_secret_key'] ) && ! empty( $car_dealer_options['google_captcha_secret_key'] ) ) ? $car_dealer_options['google_captcha_secret_key'] : '';
		}
		return $key;
	}
}


if ( ! function_exists( 'cdfs_check_captcha_exists' ) ) {
	/**
	 * Get google captcha keys
	 */
	function cdfs_check_captcha_exists() {
		global $car_dealer_options;
		$key = ( isset( $car_dealer_options['google_captcha_site_key'] ) && ! empty( $car_dealer_options['google_captcha_site_key'] ) ) ? $car_dealer_options['google_captcha_site_key'] : '';
		$key = ( isset( $car_dealer_options['google_captcha_secret_key'] ) && ! empty( $car_dealer_options['google_captcha_secret_key'] ) ) ? $car_dealer_options['google_captcha_secret_key'] : '';
		if ( empty( $key ) ) {
			return false;
		}
		return true;
	}
}

if ( ! function_exists( 'cdfs_validate_google_captcha' ) ) {
	/**
	 * Validate google captcha
	 *
	 * @param string $captcha .
	 */
	function cdfs_validate_google_captcha( $captcha ) {
		$secret_key = cdfs_get_goole_api_keys( 'secret_key' );
		if ( empty( $secret_key ) ) {
			return array( 'success' => true );
		}

		$response = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $captcha . '&remoteip=' . $_SERVER['REMOTE_ADDR'] );
		return json_decode( $response['body'], true );
	}
}

if ( ! function_exists( 'cdfs_captcha_enabled' ) ) {
	/**
	 * Check captcha enabled
	 */
	function cdfs_captcha_enabled() {
		global $car_dealer_options;

		if ( isset( $car_dealer_options['google_captcha_secret_key'] ) && ! empty( $car_dealer_options['google_captcha_secret_key'] ) && isset( $car_dealer_options['google_captcha_site_key'] ) && ! empty( $car_dealer_options['google_captcha_site_key'] ) ) {
			return true;
		}
		return false;
	}
}

if ( ! function_exists( 'cdfs_validate_captcha' ) ) {
	/**
	 * Validate_captcha
	 *
	 * @param string $redirect_url .
	 */
	function cdfs_validate_captcha( $redirect_url = null ) {
		$status      = true;
		$ajax_action = array( 'cdfs_do_login', 'cdfs_do_ajax_user_register', 'cdfs_save_car' );
		if ( cdfs_captcha_enabled() ) { // google captcha.
			$captcha_res             = cdfs_clean( $_POST['g-recaptcha-response'] );
			$is_ajax                 = ( isset( $_POST['action'] ) && in_array( $_POST['action'], $ajax_action ) ) ? 'yes' : 'no';
			$cdfs_recaptcha_response = cdfs_validate_google_captcha( $captcha_res );
			if ( isset( $cdfs_recaptcha_response['success'] ) && 1 !== (int) $cdfs_recaptcha_response['success'] ) {
				if ( 'no' === $is_ajax ) { // if not ajax call.
					cdfs_add_notice( esc_html__( 'Please check the the captcha form', 'cdfs-addon' ), 'error' );
					if ( null !== $redirect_url ) {
						wp_safe_redirect( $redirect_url );
						die;
					}
				}
				$status = false;
			}
		}
		return $status;
	}
}

if ( ! function_exists( 'cdfs_user_activation_method' ) ) {
	/**
	 * Retrieve page ids. returns -1 if no page is found.
	 * since version 1.2.3
	 *
	 * @return int
	 */
	function cdfs_user_activation_method() {
		global $car_dealer_options;
		if ( isset( $car_dealer_options['cdfs_user_activation'] ) && ! empty( $car_dealer_options['cdfs_user_activation'] ) ) {
			return $car_dealer_options['cdfs_user_activation'];
		}
		return 'default';
	}
}

/**
 * Class builder
 *
 * @param string|array $class  List of classes, either string (separated with space) or array.
 * @param bool         $echo   Whether to return or echo.
 * @return string
 */
function cdfs_class_builder( $class = '', $echo = false ) {
	$classes = array();

	if ( ( is_array( $class ) || is_string( $class ) ) && ! empty( $class ) ) {

		if ( is_array( $class ) ) {
			$class = implode( ' ', $class );
		}

		// If $class is string, convert it to array.
		if ( is_string( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = $class;
	}

	// Sanitize classes.
	$classes = array_map( 'sanitize_html_class', $classes );
	$classes = array_map( 'esc_attr', $classes );

	// Convert array to string.
	$class_str = implode( ' ', array_filter( array_unique( $classes ) ) );

	if ( $echo ) {
		echo esc_attr( $class_str );
	} else {
		return $class_str;
	}
}

/**
 * Get theme option
 *
 * @param string $option_id option id.
 * @param string $fallback fallback.
 * @param bool   $param param.
 */
function cdfs_get_theme_option( $option_id, $fallback = '', $param = false ) {
	global $car_dealer_options;

	if ( empty( $car_dealer_options ) ) {
		$car_dealer_options = get_option( 'car_dealer_options' );
	}

	$output = ( isset( $car_dealer_options[ $option_id ] ) && $car_dealer_options[ $option_id ] ) ? $car_dealer_options[ $option_id ] : $fallback;

	if (
		( isset( $car_dealer_options[ $option_id ] ) && $car_dealer_options[ $option_id ] )
		&& is_array( $car_dealer_options[ $option_id ] )
		&& ! empty( $car_dealer_options[ $option_id ] )
		&& $param && isset( $car_dealer_options[ $option_id ][ $param ] )
	) {
		$output = $car_dealer_options[ $option_id ][ $param ];
	}

	return $output;
}

add_filter( 'body_class', 'cdfs_user_dashboard_add_body_class' );
function cdfs_user_dashboard_add_body_class( $classes ) {
    if ( is_author() ) {
        $classes[] = 'cardealer-dashboard';
    }
    return $classes;
}

add_filter( 'cardealer_hide_header_banner', 'cdfs_user_dashboard_hide_banner' );
function cdfs_user_dashboard_hide_banner( $hide ) {
	if ( is_author() ) {
		$hide = true;
	}
	return $hide;
}

function cdfs_tgmpa_is_subscriptio_enabled( $enabled) {
	$enabled = true;
	return $enabled;
}
add_filter( 'cardealer_tgmpa_is_subscriptio_enabled', 'cdfs_tgmpa_is_subscriptio_enabled' );

function cdfs_tgmpa_is_woocommerce_enabled( $enabled) {
	$enabled = true;
	return $enabled;
}
add_filter( 'cardealer_tgmpa_is_woocommerce_enabled', 'cdfs_tgmpa_is_woocommerce_enabled' );

function cdfs_pricing_plan_enabled() {
	global $car_dealer_options;

	$pricing_plan_enabled = ( isset( $car_dealer_options['cdfs_pricing_plan'] ) && '' !== $car_dealer_options['cdfs_pricing_plan'] ) ? $car_dealer_options['cdfs_pricing_plan'] : true;
	$pricing_plan_enabled = filter_var( $pricing_plan_enabled, FILTER_VALIDATE_BOOLEAN );
	$pricing_plan_enabled = apply_filters( 'cdfs_pricing_plan_enabled', $pricing_plan_enabled );

	return $pricing_plan_enabled;
}

function cdfs_get_free_cars_limit() {
	global $car_dealer_options;

	$cars_limit = ( isset( $car_dealer_options['cdfs_cars_limit'] ) && '' !== $car_dealer_options['cdfs_cars_limit'] ) ? $car_dealer_options['cdfs_cars_limit'] : 0;
	$cars_limit = (int) apply_filters( 'cdfs_get_free_cars_limit', $cars_limit );

	return $cars_limit;
}

function cdfs_get_free_cars_image_limit() {
	global $car_dealer_options;

	$cars_img_limit = ( isset( $car_dealer_options['cdfs_cars_img_limit'] ) && '' !== $car_dealer_options['cdfs_cars_img_limit'] ) ? $car_dealer_options['cdfs_cars_img_limit'] : 0;
	$cars_img_limit = (int) apply_filters( 'cdfs_get_free_cars_image_limit', $cars_img_limit );

	return $cars_img_limit;
}

function cdfs_auto_publish_enabled() {
	global $car_dealer_options;

	$auto_publish = 0;

	if (
		$car_dealer_options['cdfs_auto_publish']
		&& ( isset( $car_dealer_options['cdfs_auto_publish']['auto_publish'] ) && 1 == (int) $car_dealer_options['cdfs_auto_publish']['auto_publish'] )
	) {
		$auto_publish = 1;
	}

	$auto_publish = apply_filters( 'cdfs_auto_publish_enabled', $auto_publish );
	$auto_publish = filter_var( $auto_publish, FILTER_VALIDATE_BOOLEAN );

	return $auto_publish;
}

function cdfs_auto_publish_status() {
	$auto_publish        = cdfs_auto_publish_enabled();
	$auto_publish_status = 'pending';

	if ( $auto_publish ) {
		$auto_publish_status = 'publish';
	}

	if ( 'listing_payment' === $_POST['subscription_plan'] ) {
		$auto_publish_status = 'pending';
	}

	$auto_publish_status = apply_filters( 'cdfs_auto_publish_status', $auto_publish_status );

	return $auto_publish_status;
}

function cdfs_get_add_car_packages( $user_id ) {
	$subscriptions_plans = array();

	$cdfs_free_limit           = cdfs_get_free_cars_limit();
	$cdfs_free_available_limit = cdfs_get_user_subscription_available_car_limit( 'free', get_current_user_id() );

	$subscriptions_plans['free'] = array(
		'plan_id'             => 'free',
		'plan_name'           => esc_html__( 'Free', 'cdfs-addon' ),
		'car_limit'           => $cdfs_free_limit,
		'car_limit_available' => $cdfs_free_available_limit,
		'image_limit'        => cdfs_get_user_car_subscription_image_limit( 'free' ),
		'submit_type'        => 'free',
	);

	$subscriptions = array();

	if ( function_exists( 'subscriptio_get_subscriptions' ) && is_user_logged_in() ) {
		$subscriptions = subscriptio_get_subscriptions( array( 'customer' => $user_id ) );
	}

	if ( $subscriptions && ! empty( $subscriptions ) ) {
		foreach ( $subscriptions as $subscription ) {
			$car_limit           = get_post_meta( $subscription->get_id(), 'cdfs_car_limt', true );
			$available_car_limit = cdfs_get_user_subscription_available_car_limit( $subscription->get_id(), $user_id );
			$subscriptions_plans[ $subscription->get_id() ] = array(
				'plan_id'             => $subscription->get_id(),
				'plan_name'           => $subscription->get_formatted_product_name(),
				'car_limit'           => $car_limit,
				'car_limit_available' => $available_car_limit,
				'image_limit'         => cdfs_get_user_car_subscription_image_limit( $subscription->get_id() ),
				'submit_type'         => 'subscription',
			);
		}
	}

	$listing_payment_enabled = cdfs_listing_payment_enabled();

	if ( $listing_payment_enabled ) {
		$subscriptions_plans['listing_payment'] = array(
			'plan_id'             => 'listing_payment',
			'plan_name'           => esc_html__( 'Pay This Item', 'cdfs-addon' ),
			// 'car_limit'           => '',
			// 'car_limit_available' => cdfs_get_user_subscription_available_car_limit( 'listing_payment', $user_id ),
			'car_limit'           => 'na',
			'car_limit_available' => 'na',
			'image_limit'         => cdfs_get_user_car_subscription_image_limit( 'listing_payment' ),
			'submit_type'         => 'listing_payment',
		);
	}

	return $subscriptions_plans;
}

function cdfs_get_add_car_package_columns() {
	$columns = array(
		'plan_cb'             => '',
		'plan_name'           => esc_html__( 'Package Name', 'cdfs-addon' ),
		// 'car_limit'           => esc_html__( 'Car Limit', 'cdfs-addon' ),
		'car_limit_available' => esc_html__( 'Available Limit', 'cdfs-addon' ),
		'image_limit'         => esc_html__( 'Image Limit', 'cdfs-addon' ),
		// 'plan_id'             => esc_html__( 'Plan ID', 'cdfs-addon' ),
	);
	return $columns;
}

function cdfs_get_listing_type_labels() {
	return array(
		'listing_payment' => esc_html__( 'Listing Payment', 'cdfs-addon' ),
		'subscription'    => esc_html__( 'Subscription', 'cdfs-addon' ),
		'free'            => esc_html__( 'Free', 'cdfs-addon' ),
	);
}

function cdfs_get_vehicle_listing_type( $vehicle_id ) {
	$listing_type = get_post_meta( $vehicle_id, 'cdfs_listing_type', true );

	if ( empty( $listing_type ) ) {
		$listing_type    = 'free';
		$subscription_id = get_post_meta( $vehicle_id, 'cdfs_subscription_id', true );
		if ( 'free' !== $subscription_id ) {
			$user_id       = get_current_user_id();
			$subscriptions = function_exists( 'subscriptio_get_subscriptions' ) ? subscriptio_get_subscriptions( array( 'customer' => $user_id ) ) : array();
			if ( array_key_exists( $subscription_id, $subscriptions ) ) {
				$listing_type = 'subscription';
			}
		}
	}

	$listing_type = apply_filters( 'cdfs_get_vehicle_listing_type', $listing_type, $vehicle_id );

	return $listing_type;
}

function cdfs_get_vehicle_listing_type_label( $vehicle_id ) {
	$listing_type        = cdfs_get_vehicle_listing_type( $vehicle_id );
	$listing_type_labels = cdfs_get_listing_type_labels();
	$listing_type_label  = ( isset( $listing_type_labels[ $listing_type ] ) && ! empty( $listing_type_labels[ $listing_type ] ) ) ? $listing_type_labels[ $listing_type ] : '';

	$listing_type_label = apply_filters( 'cdfs_get_vehicle_listing_type_label', $listing_type_label, $vehicle_id );

	return $listing_type_label;
}

function cdfs_user_profile_images_handler() {
	$user                  = wp_get_current_user();
	$profile_images_update = array();

	/*Profile img*/
	$allowed_file_types    = array( 'jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG' );
	$profile_images        = array(
		'cdfs_user_avatar' => array(
			'url_meta'   => 'cdfs_user_avatar',
			'path_meta'  => 'cdfs_user_avatar_path',
			'image_crop' => array(
				'width'  => 150,
				'height' => 150,
				'crop'   => true,
			),
		),
		'cdfs_user_banner' => array(
			'url_meta'  => 'cdfs_user_banner',
			'path_meta' => 'cdfs_user_banner_path',
			'image_crop' => array(
				'width'  => 1140,
				'height' => 360,
				'crop'   => true,
			),
		),
	);

	foreach( $profile_images as $image_field_name => $profile_image_data ) {
		if ( isset( $_FILES[ $image_field_name ] ) && is_array( $_FILES[ $image_field_name ] ) && ! empty( $_FILES[ $image_field_name ] ) && isset( $_FILES[ $image_field_name ]['name'] ) && ! empty( $_FILES[ $image_field_name ]['name'] ) ) {
			$profile_image          = $_FILES[ $image_field_name ];
			$profile_image_pathinfo = pathinfo( $profile_image['name'] );
			$profile_image_ext      = $profile_image_pathinfo['extension'];

			if ( ! in_array( $profile_image_ext, $allowed_file_types ) ) {
				cdfs_add_notice( esc_html__( 'Please select the profile image with the right extension (jpg, jpeg, and png).', 'cdfs-addon' ), 'error' );
			} else {

				$upload_dir  = wp_upload_dir();
				$upload_url  = $upload_dir['url'];
				$upload_path = $upload_dir['path'];

				// Upload full image.
				if ( ! function_exists( 'wp_handle_upload' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				$uploaded_image = wp_handle_upload( $profile_image, array( 'test_form' => false ) );

				if ( ! is_wp_error( $uploaded_image ) ) {

					// Crop image.
					$uploaded_image_cropped = image_make_intermediate_size(
						$uploaded_image['file'],
						$profile_image_data['image_crop']['width'],
						$profile_image_data['image_crop']['height'],
						$profile_image_data['image_crop']['crop']
					);

					// Delete original uploaded image.
					if ( file_exists( $uploaded_image['file'] ) ) {
						unlink( $uploaded_image['file'] );
					}

					if ( ! $uploaded_image_cropped ) {
						$got_error_validation = true;
						cdfs_add_notice( esc_html__( 'Unable to crop image. Please try again.', 'cdfs-addon' ), 'error' );
					} else {

						// Get path and url of cropped image.
						$uploaded_image_cropped_url  = $upload_url . '/' . $uploaded_image_cropped['file'];
						$uploaded_image_cropped_path = $upload_path . '/' . $uploaded_image_cropped['file'];
						$uploaded_image_cropped_path = str_replace( array('/', '\\'), '/', $uploaded_image_cropped_path );

						$profile_images_update[ $image_field_name ] = array(
							$profile_image_data['url_meta']  => $uploaded_image_cropped_url,
							$profile_image_data['path_meta'] => $uploaded_image_cropped_path,
						);

						// Delete old avatar.
						$old_avatar_path = get_the_author_meta( $profile_image_data['path_meta'], $user->ID );

						if (
							! empty( $old_avatar_path )
							&& $uploaded_image_cropped_path !== $old_avatar_path
							&& file_exists( $old_avatar_path )
						) {

							/*Check if prev avatar exists in another users except current user*/
							$args     = array(
								'meta_key'     => $profile_image_data['path_meta'],
								'meta_value'   => $old_avatar_path,
								'meta_compare' => '=',
								'exclude'      => array( $user->ID ),
							);
							$users_db = get_users( $args );
							if ( empty( $users_db ) ) {
								unlink( $old_avatar_path );
							}
						}
					}
				}
			}
		}
	}

	return $profile_images_update;
}

function cdfs_get_add_car_form_sections_data( $return_type = 'all' ) {
	$sections = array(
		'cars-image-gallery' => array(
			'label'    => esc_html__( 'Vehicle Images', 'cdfs-addon' ),
			'template' => 'cars/cars-templates/cars-image-gallery.php',
			'display'  => true,
		),
		'cars-location' => array(
			'label'    => esc_html__( 'Vehicle Location', 'cdfs-addon' ),
			'template' => 'cars/cars-templates/cars-location.php',
			'display'  => true,
		),
		'car-attributes' => array(
			'label'    => esc_html__( 'Vehicle Attributes', 'cdfs-addon' ),
			'template' => 'cars/cars-templates/car-attributes.php',
			'display'  => false,
		),
		'cars-review-stamps' => array(
			'label'    => esc_html__( 'Vehicle Review Stamps', 'cdfs-addon' ),
			'template' => 'cars/cars-templates/cars-review-stamps.php',
			'display'  => false,
		),
		'cars-pdf-brochure' => array(
			'label'    => esc_html__( 'Vehicle PDF Brochure', 'cdfs-addon' ),
			'template' => 'cars/cars-templates/cars-pdf-brochure.php',
			'display'  => false,
		),
		'car-additional-info' => array(
			'label'    => esc_html__( 'Additional Information', 'cdfs-addon' ),
			'template' => 'cars/cars-templates/car-additional-info.php',
			'display'  => false,
		),
		'cars-excerpt' => array(
			'label'    => esc_html__( 'Vehicle Excerpt', 'cdfs-addon' ),
			'template' => 'cars/cars-templates/cars-excerpt.php',
			'display'  => false,
		),
		'cars-packages' => array(
			'label'    => esc_html__( 'Vehicle Packages', 'cdfs-addon' ),
			'template' => 'cars/cars-templates/cars-packages.php',
			'display'  => false,
		),
	);

	$sections = apply_filters( 'cdfs_add_car_form_sections', $sections );

	if ( 'options' === $return_type ) {
		$sections = array_map( function( $section_data ) {
			return $section_data['label'];
		}, $sections );
	} elseif ( 'defaults' === $return_type ) {
		$sections = array_map( function( $section_data ) {
			return ( isset( $section_data['display'] ) ? filter_var( $section_data['display'], FILTER_VALIDATE_BOOLEAN ) : false );
		}, $sections );
	}

	return $sections;
}

function cdfs_get_add_car_form_sections( $shortcode_sections = array() ) {
	global $car_dealer_options;

	$sections     = array();
	$all_sections = cdfs_get_add_car_form_sections_data();

	$form_sections_default    = array(
		'cars-image-gallery'  => '1',
		'cars-location'       => '1',
		'car-attributes'      => '1',
		'cars-review-stamps'  => '1',
		'cars-pdf-brochure'   => '1',
		'car-additional-info' => '1',
		'cars-excerpt'        => '1',
		'cars-packages'       => '1',
	);

	if ( ! empty( $shortcode_sections ) ) {
		foreach ( $shortcode_sections as $section ) {
			if ( isset( $all_sections[ $section ] ) ) {
				$sections[ $section ] = $all_sections[ $section ];
			}
		}
	} else {
		$selected_sections = ( isset( $car_dealer_options['cdfs_form_sections'] ) && ! empty( $car_dealer_options['cdfs_form_sections'] ) ) ? $car_dealer_options['cdfs_form_sections'] : $form_sections_default;

		foreach ( $selected_sections as $section_k => $visible ) {
			$visible = filter_var( $visible, FILTER_VALIDATE_BOOLEAN );
			if ( $visible && isset( $all_sections[ $section_k ] ) && ! empty( $all_sections[ $section_k ] ) ) {
				$sections[ $section_k ] = $all_sections[ $section_k ];
			}
		}
	}

	return $sections;
}

add_action( 'action_before_cars_excerpt', 'cdfs_action_before_cars_excerpt' );
add_action( 'action_after_cars_excerpt', 'cdfs_action_after_cars_excerpt' );

function cdfs_action_before_cars_excerpt() {
	add_filter( 'tiny_mce_before_init', 'cdfs_add_car_form_configure_tiny_mce_editors', 10, 2 );
}
function cdfs_action_after_cars_excerpt() {
	remove_filter( 'tiny_mce_before_init', 'cdfs_add_car_form_configure_tiny_mce_editors' );
}

add_action( 'action_before_cars_additional_info', 'cdfs_action_before_cars_additional_info' );
add_action( 'action_after_cars_additional_info', 'cdfs_action_after_cars_additional_info' );
function cdfs_action_before_cars_additional_info() {
	add_filter( 'tiny_mce_before_init', 'cdfs_add_car_form_configure_tiny_mce_editors', 10, 2 );
}
function cdfs_action_after_cars_additional_info() {
	remove_filter( 'tiny_mce_before_init', 'cdfs_add_car_form_configure_tiny_mce_editors' );
}

function cdfs_add_car_form_configure_tiny_mce_editors( $settings, $editor_id ) {
	$toolbar1  = $settings['toolbar1'];
	$toolbar1  = explode( ',', $toolbar1 );
	$wp_more_i = array_search( 'wp_more', $toolbar1, true );

	if ( $wp_more_i ) {
		unset( $toolbar1[ $wp_more_i ] );
	}

	$toolbar1             = implode( ',', $toolbar1 );
	$settings['toolbar1'] = $toolbar1;

	return $settings;
}

function cdfs_get_add_car_image_upload_size_limit_in_mb() {
	global $car_dealer_options;

	$size_limit_default = 4;
	$option_k      = 'cdfs_add_car_image_upload_size_limit';
	$size_limit_mb = ( isset( $car_dealer_options[ $option_k ] ) && ! empty( $car_dealer_options[ $option_k ] ) ) ? $car_dealer_options[ $option_k ] : $size_limit_default; // Default size limit: 4 Mb.
	$size_limit_mb = intval( apply_filters( 'cdfs_add_car_image_upload_size_limit_in_mb', $size_limit_mb ) );

	if ( $size_limit_mb < 0 ) {
		$size_limit_mb = $size_limit_default;
	}

	return $size_limit_mb;
}

function cdfs_get_add_car_image_upload_size_limit_in_bytes() {
	$size_limit_mb    = cdfs_get_add_car_image_upload_size_limit_in_mb();
	$size_limit_bytes = 1048576 * $size_limit_mb;
	$size_limit_bytes = intval( apply_filters( 'cdfs_max_media_upload_size', $size_limit_bytes ) );
	$size_limit_bytes = intval( apply_filters( 'cdfs_add_car_image_upload_size_limit_in_bytes', $size_limit_bytes ) );

	return $size_limit_bytes;
}

function cdfs_get_add_car_image_upload_size_limit_text() {
	$image_size_limit_mb    = cdfs_get_add_car_image_upload_size_limit_in_mb();
	$image_size_limit_bytes = cdfs_get_add_car_image_upload_size_limit_in_bytes();
	$image_size_limit_text  = sprintf( esc_html__( '%s Mb (%s Bytes)', 'cdfs-addon' ), $image_size_limit_mb, $image_size_limit_bytes );

	$image_size_limit_text = apply_filters( 'cdfs_add_car_image_upload_size_limit_text', $image_size_limit_text, $image_size_limit_mb, $image_size_limit_bytes );

	return $image_size_limit_text;
}

function cdfs_vehicle_views_enabled() {
	global $car_dealer_options;

	$car_dealer_options = ( ! empty( $car_dealer_options ) ) ? $car_dealer_options : get_option( 'car_dealer_options' );

	$enable_vehicle_views = ( isset( $car_dealer_options['enable_vehicle_views'] ) && ! empty( $car_dealer_options['enable_vehicle_views'] ) ) ? $car_dealer_options['enable_vehicle_views'] : 'yes';
	$enable_vehicle_views = filter_var( $enable_vehicle_views, FILTER_VALIDATE_BOOLEAN );
	$enable_vehicle_views = apply_filters( 'cdfs_vehicle_views_enabled', $enable_vehicle_views );

	return $enable_vehicle_views;
}

function cdfs_show_vehicle_views() {
	global $car_dealer_options;

	$car_dealer_options = ( ! empty( $car_dealer_options ) ) ? $car_dealer_options : get_option( 'car_dealer_options' );

	$views_enabled = cdfs_vehicle_views_enabled();

	if ( $views_enabled ) {
		$show_vehicle_views = ( isset( $car_dealer_options['display_vehicle_statistics'] ) ) ? $car_dealer_options['display_vehicle_statistics'] : '1';
	} else {
		$show_vehicle_views = 0;
	}

	$show_vehicle_views = filter_var( $show_vehicle_views, FILTER_VALIDATE_BOOLEAN );
	$show_vehicle_views = apply_filters( 'cdfs_vehicle_views_enabled', $show_vehicle_views );

	return $show_vehicle_views;
}

if ( ! function_exists( 'cdfs_vc_link_attr' ) ) {
	/**
	 * VC Link Attrinutes
	 *
	 * @param string $url_vars URL vars.
	 */
	function cdfs_vc_link_attr( $url_vars ) {
		$link_attr_array = array();
		$link_attr_str   = '';

		if ( ! empty( $url_vars ) && is_array( $url_vars ) ) {
			if ( isset( $url_vars['target'] ) && '_blank' === $url_vars['target'] ) {
				if ( isset( $url_vars['rel'] ) && ! empty( $url_vars['rel'] ) ) {
					$url_vars['rel'] = $url_vars['rel'] . ' noopener';
				} else {
					$url_vars['rel'] = 'noopener';
				}
			}

			foreach ( $url_vars as $url_var_k => $url_var_v ) {
				if ( ! empty( $url_var_v ) ) {
					if ( 'url' === $url_var_k ) {
						$link_attr_array[] = 'href="' . esc_url( $url_var_v ) . '"';
					} else {
						$link_attr_array[] = $url_var_k . '="' . esc_attr( $url_var_v ) . '"';
					}
				}
			}
		}

		$link_attr_str = implode( ' ', $link_attr_array );

		return $link_attr_str;
	}
}
