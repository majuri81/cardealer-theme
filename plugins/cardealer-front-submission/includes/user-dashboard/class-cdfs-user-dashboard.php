<?php
class CDFS_User_Dashboard {

	/**
	 * Endpoints to add to wp.
	 *
	 * @var array
	 */
	public $endpoints = array();

	/**
	 * Constructor for the session class.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );

		if ( ! is_admin() ) {
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			// add_filter( 'request', array( $this, 'rewrite_filter_request' ) );
			add_filter( 'author_template', array( $this, 'dashboard_template' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		}

		$this->init_endpoints();
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_endpoints() {
		// Query vars to add to WP.
		$this->endpoints  = apply_filters( 'cardealer_dashboard_endpoints', array() );
	}

	/**
	 * Endpoint mask describing the places the endpoint should be added.
	 *
	 * @since 2.6.2
	 * @return int
	 */
	public function get_endpoints_mask() {
		return EP_AUTHORS;
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		$mask      = $this->get_endpoints_mask();
		$endpoints = $this->get_endpoints();

		unset( $endpoints['dashboard'] );

		foreach ( $endpoints as $endpoint_id => $endpoint_data ) {
			if ( ! empty( $endpoint_data['endpoint'] ) ) {
				add_rewrite_endpoint( $endpoint_data['endpoint'], $mask );
				flush_rewrite_rules();
			}
		}
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$endpoints = $this->get_endpoints();

		unset( $endpoints['dashboard'] );

		foreach ( $endpoints as $endpoint_id => $endpoint_data ) {
			$vars[] = $endpoint_data['endpoint'];
		}

		return $vars;
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_endpoints() {
		return apply_filters( 'cardealer_dashboard_get_endpoints', $this->endpoints );
	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;
		$endpoints = $this->get_endpoints();

		unset( $endpoints['dashboard'] );

		foreach ( $endpoints as $endpoint_id => $endpoint_data ) {
			if ( isset( $wp->query_vars[ $endpoint_data['endpoint'] ] ) ) {
				return $endpoint_data['endpoint'];
			}
		}
		return '';
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;
		$endpoints = $this->get_endpoints();

		unset( $endpoints['dashboard'] );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// Map query vars to their keys, or get them if endpoints are not supported.
		foreach ( $endpoints as $endpoint_id => $endpoint_data ) {
			if ( isset( $_GET[ $endpoint_data['endpoint'] ] ) ) {
				$wp->query_vars[ $endpoint_id ] = sanitize_text_field( wp_unslash( $_GET[ $endpoint_data['endpoint'] ] ) );
			} elseif ( isset( $wp->query_vars[ $endpoint_data['endpoint'] ] ) ) {
				$wp->query_vars[ $endpoint_id ] = $wp->query_vars[ $endpoint_data['endpoint'] ];
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	function rewrite_filter_request( $vars ) {

		$endpoints = $this->endpoints;

		unset( $endpoints['dashboard'] );

		foreach ( $endpoints as $endpoint_id => $endpoint_data ) {
			if ( isset( $vars[ $endpoint_data['endpoint'] ] ) ) {
				$vars[ $endpoint_data['endpoint'] ] = true;
			}
		}

		return $vars;
	}

	function dashboard_template( $template ) {
		global $wp, $cdfs_viewing_public_profile;

		$current_user  = wp_get_current_user();
		$selected_user = get_queried_object();

		$secure = ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) );
		if ( isset( $_GET['cdfs-action'] ) && 'view-public-profile' === $_GET['cdfs-action'] ) {

			setcookie(
				'cdfs_view_public_profile',    // Name.
				'yes',                         // Value.
				time() + (60*60*24*30),        // Expire.
				COOKIEPATH ? COOKIEPATH : '/', // Path.
				COOKIE_DOMAIN,                 // Domain.
				$secure,
				false
			);

			wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url() );
			die;
		}

		if ( isset( $_GET['cdfs-action'] ) && 'exit-public-profile' === $_GET['cdfs-action'] ) {
			setcookie(
				'cdfs_view_public_profile',    // Name.
				'no',                         // Value.
				time() + (60*60*24*30),        // Expire.
				COOKIEPATH ? COOKIEPATH : '/', // Path.
				COOKIE_DOMAIN,                 // Domain.
				$secure,
				false
			);

			wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url() );
			die;
		}

		$view_profile_mode = false;

		if (
			$selected_user->ID !== $current_user->ID
			|| (
				$selected_user->ID === $current_user->ID
				&& (
					isset( $wp->query_vars['view-profile'] )
					|| ( isset( $_COOKIE['cdfs_view_public_profile'] ) && 'yes' === $_COOKIE['cdfs_view_public_profile'] )
				)
			)
		) {
			$view_profile_mode = true;
		}

		if ( $view_profile_mode ) {
			if ( isset( $_COOKIE['cdfs_view_public_profile'] ) && 'yes' === $_COOKIE['cdfs_view_public_profile'] ) {
				$cdfs_viewing_public_profile = $selected_user->ID === $current_user->ID;
			}
			$template = cdfs_locate_template( 'user-dashboard/view-profile/view-profile.php' );
		} else {
			$template = cdfs_locate_template( 'user-dashboard/dashboard.php' );
		}

		return $template;
	}

	function template_redirect() {
		$queried_object    = get_queried_object();

		if ( is_user_logged_in() && is_singular( 'page' ) && has_shortcode( $queried_object->post_content, 'cardealer_dealer_login' ) ) {
			wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url() );
			die;
		}
	}
}

return new CDFS_User_Dashboard();
