<?php
function cdfs_viewing_public_profile_notice() {
	global $cdfs_viewing_public_profile;

	if ( true !== $cdfs_viewing_public_profile ) {
		return;
	}

	$exit_url = cdfs_get_exit_public_profile_view_url();
	?>
	<div class="public-profile-notice"><a href="<?php echo esc_url( $exit_url ); ?>"><i class="fas fa-arrow-left"></i><?php esc_html_e( 'Viewing public profile, switch back', 'cdfs-addon' ); ?></a></div>
	<?php
}

if ( ! function_exists( 'cardealer_dashboard_user_info' ) ) {

	function cardealer_dashboard_user_info() {
		cdfs_get_template( 'user-dashboard/user-info.php' );
	}

}

if ( ! function_exists( 'cardealer_dashboard_navigation' ) ) {

	function cardealer_dashboard_navigation() {
		cdfs_get_template( 'user-dashboard/navigation.php' );
	}

}

if ( ! function_exists( 'cardealer_dashboard_header' ) ) {

	/**
	 * Dashboard header output.
	 */
	function cardealer_dashboard_header() {
		global $car_dealer_options;

		$pricing_plan_enabled = cdfs_pricing_plan_enabled();

		// No endpoint found? Default to dashboard.
		cdfs_get_template(
			'user-dashboard/dashboard-sections/content-header.php',
			array(
				'current_user'      => get_user_by( 'id', get_current_user_id() ),
				'add_car_url'       => cdfs_get_add_car_url(),
				'logout_url'        => cdfs_get_cardealer_dashboard_endpoint_url( 'dashboard-logout' ),
				'get_plan_url'      => cdfs_get_plan_url(),
				'pricing_plan_enabled' => $pricing_plan_enabled,
				'get_plan_label'    => apply_filters( 'cardealer-dashboard/get-plan-label', esc_html__( 'Get New Plan', 'cdfs-addon' ) ),
			)
		);
	}
}

if ( ! function_exists( 'cardealer_dashboard_content' ) ) {

	/**
	 * Dashboard content output.
	 */
	function cardealer_dashboard_content() {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {

			foreach ( $wp->query_vars as $key => $value ) {

				// Ignore pagename param.
				if ( 'author_name' === $key ) {
					continue;
				}

				if ( has_action( 'cardealer-dashboard/endpoint-content/' . $key ) ) {
					do_action( 'cardealer-dashboard/endpoint-content/' . $key, $value );
					return;
				}
			}
		}

		// No endpoint found? Default to dashboard.
		do_action( 'cardealer-dashboard/endpoint-content/dashboard' );
	}
}

function cardealer_dashboard_endpoint_content_dashboard() {
	$endpoint_title = cdfs_get_cardealer_dashboard_endpoint_title( 'dashboard' );
	cdfs_get_template(
		'user-dashboard/dashboard-sections/dashboard.php',
		array(
			'endpoint_title' => $endpoint_title,
			'current_user'   => get_user_by( 'id', get_current_user_id() ),
		)
	);
}

/**
 * Dashboard items output.
 *
 * @param int $current_page Current page number.
 */
function cardealer_dashboard_endpoint_content_items( $current_page ) {
	$current_page   = empty( $current_page ) ? 1 : absint( $current_page );
	$endpoint_title = cdfs_get_cardealer_dashboard_endpoint_title( 'my-items' );

	cdfs_get_template(
		'user-dashboard/dashboard-sections/items.php',
		array(
			'current_page'   => $current_page,
			'endpoint_title' => $endpoint_title,
			'current_user'   => get_user_by( 'id', get_current_user_id() ),
		)
	);
}

/**
 * Dashboard wishlist output.
 *
 * @param int $current_page Current page number.
 */
function cardealer_dashboard_endpoint_content_wishlist( $current_page ) {
	$current_page   = empty( $current_page ) ? 1 : absint( $current_page );
	$endpoint_title = cdfs_get_cardealer_dashboard_endpoint_title( 'my-wishlist' );

	cdfs_get_template(
		'user-dashboard/dashboard-sections/wishlist.php',
		array(
			'current_page'   => $current_page,
			'endpoint_title' => $endpoint_title,
			'current_user'   => get_user_by( 'id', get_current_user_id() ),
		)
	);
}

/**
 * Dashboard subscriptions output.
 *
 * @param int $current_page Current page number.
 */
function cardealer_dashboard_endpoint_content_subscriptions( $current_page ) {

	$current_page   = empty( $current_page ) ? 1 : absint( $current_page );
	$endpoint_title = cdfs_get_cardealer_dashboard_endpoint_title( 'my-subscriptions' );
	$subscriptions  = ( function_exists( 'subscriptio_get_subscriptions' ) && is_user_logged_in() ) ? subscriptio_get_subscriptions( array( 'customer' => get_current_user_id() ) ) : array();

	$template_args = array(
		'current_page'   => $current_page,
		'endpoint_title' => $endpoint_title,
		'current_user'   => get_user_by( 'id', get_current_user_id() ),
		'subscriptions'  => $subscriptions,
		'columns'        => apply_filters( 'cardealer-dashboard/subscriptions/columns', array(
			'subscription-id'           => esc_html__( 'Subscription ID', 'cdfs-addon' ),
			'subscription-products'     => esc_html__( 'Subscription/Plan', 'cdfs-addon' ),
			'subscription-availibility' => esc_html__( 'Availaible/Limit', 'cdfs-addon' ),
			'subscription-expiry'       => esc_html__( 'Expiry', 'cdfs-addon' ),
			'subscription-status'       => esc_html__( 'Status', 'cdfs-addon' ),
			// 'subscription-total'     => esc_html__( 'Recurring', 'cdfs-addon' ),
			// 'subscription-actions'   => esc_html__( 'Actions', 'cdfs-addon' ),
		)),
	);

	cdfs_get_template( 'user-dashboard/dashboard-sections/subscriptions.php', $template_args );
}

function cardealer_dashboard_endpoint_content_profile() {
	$endpoint_title = cdfs_get_cardealer_dashboard_endpoint_title( 'profile' );
	$current_user   = wp_get_current_user();
	$user_type      = cdfs_get_usertype( $current_user );

	$account_mobile   = get_user_meta( $current_user->ID, 'account_mobile', true );
	$account_whatsapp = get_user_meta( $current_user->ID, 'account_whatsapp', true );
	$cdfs_user_avatar = get_user_meta( $current_user->ID, 'cdfs_user_avatar', true );
	$cdfs_user_banner = get_user_meta( $current_user->ID, 'cdfs_user_banner', true );
	$user_img_default = trailingslashit( CDFS_URL ) . 'images/profile-img-placeholder.jpg';

	cdfs_get_template(
		'user-dashboard/dashboard-sections/profile.php',
		array(
			'endpoint_title'   => $endpoint_title,
			'current_user'     => $current_user,
			'user'             => $current_user,
			'user_type'        => $user_type,
			'account_mobile'   => $account_mobile,
			'account_whatsapp' => $account_whatsapp,
			'cdfs_user_avatar' => $cdfs_user_avatar,
			'cdfs_user_banner' => $cdfs_user_banner,
			'user_img_default' => $user_img_default,

		)
	);
}

function cardealer_dashboard_endpoint_content_settings() {
	$endpoint_title = cdfs_get_cardealer_dashboard_endpoint_title( 'settings' );
	$current_user   = wp_get_current_user();

	cdfs_get_template(
		'user-dashboard/dashboard-sections/settings.php',
		array(
			'endpoint_title' => $endpoint_title,
			'current_user'   => $current_user,
			'user'           => $current_user,
		)
	);
}

function cardealer_dashboard_edit_profile_add_overview_field( $user ) {
	$user_type = cdfs_get_usertype( $user );

	if ( 'dealer' !== $user_type ) {
		return;
	}

	$dealer_overview = get_user_meta( $user->ID, 'dealer_overview', true );

	?>
	<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
		<label class="cdfs-label-top" for="dealer_overview"><?php esc_html_e( 'Overview', 'cdfs-addon' ); ?></label>
		<div class="cdfs-input-wrap">
			<textarea id="dealer_overview" class="cdfs-input cdfs-input-textarea input-textarea" name="dealer_overview" rows="5" cols="40"><?php echo esc_textarea( $dealer_overview ); ?></textarea>
		</div>
	</div>
	<?php
}

function cardealer_dashboard_edit_profile_add_location_field( $user ) {
	$user_type = cdfs_get_usertype( $user );

	if ( 'dealer' !== $user_type ) {
		return;
	}

	wp_enqueue_script( 'cardealer-google-maps-apis' );
	wp_enqueue_script( 'cdhl-google-location-picker' );

	$address = '';
	$lat     = '40.712775';
	$lng     = '-74.005973';
	$zoom    = '10';
	if ( isset( $user->ID ) ) {
		$dealer_location = get_user_meta( $user->ID, 'dealer_location', true );
		if ( ! empty( $dealer_location ) ) {
			$address = $dealer_location['address'];
			$lng     = $dealer_location['lng'];
			$lat     = $dealer_location['lat'];
			$zoom    = $dealer_location['zoom'];
		}
	} else {
		// get the default.
		global $car_dealer_options;
		$location_exits = true;
		$lat            = '';
		$lan            = '';

		if (
			( isset( $car_dealer_options['default_value_lat'] ) && ! empty( $car_dealer_options['default_value_lat'] ) )
			&& ( isset( $car_dealer_options['default_value_long'] ) && ! empty( $car_dealer_options['default_value_long'] ) )
			&& ( isset( $car_dealer_options['default_value_zoom'] ) && ! empty( $car_dealer_options['default_value_zoom'] ) )
		) {
			$lat  = $car_dealer_options['default_value_lat'];
			$lng  = $car_dealer_options['default_value_long'];
			$zoom = $car_dealer_options['default_value_zoom'];
		}
	}
	if ( empty( $zoom ) ) {
		$zoom = 10;
	}
	?>
	<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
		<label class="cdfs-label-top" for="dealer_location"><?php esc_html_e( 'Location', 'cdfs-addon' ); ?></label>
		<div class="dealer-location-wrapper" style="width:100%">
			<input
				id="dealer_location"
				type="text"
				class="cdfs-input cdfs-input-text input-text"
				data-name="dealer_location"
				name="dealer_location[address]"
				value="<?php echo esc_attr( $address ); ?>"
				placeholder="<?php echo esc_attr__( 'Enter your location', 'cdfs-addon' ); ?>"
			/>
			<input type="hidden" class="form-control" name="dealer_location[lat]" style="width: 110px" id="dealer_location_lat" value="<?php echo esc_attr( $lat ); ?>" />
			<input type="hidden" class="form-control" name="dealer_location[lng]" style="width: 110px" id="dealer_location_lng" value="<?php echo esc_attr( $lng ); ?>" />
			<input type="hidden" class="form-control" name="dealer_location[zoom]" style="width: 110px" id="dealer_location_zoom" value="<?php echo esc_attr( $zoom ); ?>" />
			<div id="dealer-location-map" style="width:100%;height:300px;"></div>
		</div>
	</div>
	<?php
}

function cardealer_dashboard__view_profile_dealer__banner() {
	$user       = get_queried_object();
	$banner_url = cdfs_get_banner_url( $user->ID );

	cdfs_get_template(
		'user-dashboard/view-profile-content/dealer-banner.php',
		array(
			'user'       => $user,
			'banner_url' => $banner_url,
		)
	);
}

function cardealer_dashboard__view_profile_dealer__userinfo( $user ) {
	$avatar_url      = cdfs_get_avatar_url( $user->ID );
	$user_type_label = cdfs_get_usertype_label( $user );
	$user_type       = cdfs_get_usertype( $user );

	cdfs_get_template(
		'user-dashboard/view-profile-content/dealer-userinfo.php',
		array(
			'user'            => $user,
			'avatar_url'      => $avatar_url,
			'user_type_label' => $user_type_label,
			'user_type'       => $user_type,
		)
	);
}

function cardealer_dashboard__user_profile_tabs( $user ) {
	global $cdfs_view_profile_tabs_params;

	$user_type = cdfs_get_usertype( $user );
	$tabs      = cdfs_get_user_profile_tabs( $user_type );
	$location  = '';
	$overview  = '';

	$cdfs_view_profile_tabs_params = array(
		'user'      => $user,
		'user_type' => $user_type,
		'tabs'      => array(),
		'overview'  => '',
		'location'  => '',
	);

	foreach ( $tabs as $tab_k => $tab_data ) {
		if ( 'overview' === $tab_k ) {
			$dealer_overview = get_the_author_meta( 'dealer_overview', $user->ID );

			if ( $dealer_overview ) {
				$overview = $dealer_overview;
			}

			if ( empty( $overview ) ) {
				unset( $tabs[ $tab_k ] );
			} else {
				$cdfs_view_profile_tabs_params['overview'] = $overview;
			}
		}elseif ( 'location' === $tab_k ) {
			$dealer_location = get_user_meta( $user->ID, 'dealer_location', true );
			if ( $dealer_location && is_array( $dealer_location ) && ! empty( $dealer_location ) ) {
				$location = $dealer_location;
			}

			if ( empty( $location ) ) {
				unset( $tabs[ $tab_k ] );
			} else {
				$cdfs_view_profile_tabs_params['location'] = $location;
			}
		}
	}

	$cdfs_view_profile_tabs_params['tabs'] = $tabs;

	if ( ! empty( $tabs ) ) {
		cdfs_get_template( 'user-dashboard/view-profile-content/tabs.php', $cdfs_view_profile_tabs_params );
	}

	unset( $GLOBALS['cdfs_view_profile_tabs_params'] );
}

function cardealer_dashboard__user_profile_tab__overview( $tab_k, $tab_data, $user ) {
	global $cdfs_view_profile_tabs_params;

	cdfs_get_template(
		"user-dashboard/view-profile-content/tab-overview.php",
		array(
			'user'     => $user,
			'tab_k'    => $tab_k,
			'tab_data' => $tab_data,
			'overview' => $cdfs_view_profile_tabs_params['overview'],
		)
	);
}
function cardealer_dashboard__user_profile_tab__listing( $tab_k, $tab_data, $user ) {
	cdfs_get_template(
		"user-dashboard/view-profile-content/tab-listing.php",
		array(
			'user'     => $user,
			'tab_k'    => $tab_k,
			'tab_data' => $tab_data,
		)
	);
}
function cardealer_dashboard__user_profile_tab__reviews( $tab_k, $tab_data, $user ) {
	cdfs_get_template(
		"user-dashboard/view-profile-content/tab-reviews.php",
		array(
			'user'     => $user,
			'tab_k'    => $tab_k,
			'tab_data' => $tab_data,
		)
	);
}
function cardealer_dashboard__user_profile_tab__write_review( $tab_k, $tab_data, $user ) {
	cdfs_get_template(
		"user-dashboard/view-profile-content/tab-write-review.php",
		array(
			'user'     => $user,
			'tab_k'    => $tab_k,
			'tab_data' => $tab_data,
		)
	);
}
function cardealer_dashboard__user_profile_tab__location( $tab_k, $tab_data, $user ) {
	global $cdfs_view_profile_tabs_params;

	cdfs_get_template(
		"user-dashboard/view-profile-content/tab-location.php",
		array(
			'user'     => $user,
			'tab_k'    => $tab_k,
			'tab_data' => $tab_data,
			'location' => $cdfs_view_profile_tabs_params['location'],
		)
	);
	unset( $GLOBALS['dealer_location'] );
}
function cardealer_dashboard__user_profile_tab__contact( $tab_k, $tab_data, $user ) {
	global $cdfs_view_profile_tabs_params, $car_dealer_options;

	$user_type = cdfs_get_usertype( $user );
	$form_id   = 0;
	$field_k   = 'cdfs_usertyp_contact_form_' . $user_type;

	if ( isset( $car_dealer_options[ $field_k ] ) && ! empty( $car_dealer_options[ $field_k ] ) ) {
		$form_id = $car_dealer_options[ $field_k ];
	}

	global $cdfs_seller_form, $cdfs_seller_form_fields;

	$cdfs_seller_form        = true;
	$cdfs_seller_form_fields = array(
		'cdfs_seller_form'           => 'yes',
		'cdfs_seller_form_user_id'   => $user->ID,
		'cdfs_seller_form_user_type' => $user_type,
	);

	cdfs_get_template(
		"user-dashboard/view-profile-content/tab-contact.php",
		array(
			'user'      => $user,
			// 'user_id'   => $user->ID,
			// 'user_type' => $user_type,
			// 'tab_k'     => $tab_k,
			'tab_data'  => $tab_data,
			'form_id'   => $form_id,
		)
	);

	$cdfs_seller_form        = false;
	$cdfs_seller_form_fields = array();
}
