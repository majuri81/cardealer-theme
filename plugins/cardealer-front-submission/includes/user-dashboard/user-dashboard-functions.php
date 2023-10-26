<?php
/**
 * Dashboard menu items.
 *
 * @return array
 */
function cdfs_get_dashboard_endpoints( $all = false ) {
	global $car_dealer_options;
	$wishlist_status = ( isset( $car_dealer_options['cars-is-wishlist-on'] ) ) ? $car_dealer_options['cars-is-wishlist-on'] : 'yes';

	$endpoints = apply_filters( 'cardealer_dashboard_endpoints', array() );
	if ( ! $all ) {
		// Remove missing endpoints.
		foreach ( $endpoints as $endpoint_id => $endpoint_data ) {
			if ( ! isset( $endpoint_data['endpoint'] ) || empty( $endpoint_data['endpoint'] ) ) {
				unset( $endpoints[ $endpoint_id ] );
			}

			if ( 'no' === $wishlist_status && 'my-wishlist' === $endpoint_id ) {
				unset( $endpoints[ $endpoint_id ] );
			}
		}
	}
	return $endpoints;
}

function cardealer_dashboard_endpoints( $endpoints = array() ) {
	$endpoints = array_merge(
		$endpoints,
		array(
			'dashboard'        => array(
				'endpoint'         => 'dashboard',
				'endpoint_default' => 'dashboard',
				'title'            => esc_html__( 'Dashboard', 'cdfs-addon' ),
				'icon'             => 'fas fa-tachometer-alt',
				'in_dashgrid'      => false,
			),
			'my-items'         => array(
				'endpoint'         => cdfs_get_theme_option( 'dashboard_endpoint_my-items' ),
				'endpoint_default' => 'my-items',
				'title'            => esc_html__( 'My Items', 'cdfs-addon' ),
				'icon'             => 'far fa-list-alt',
			),
			'my-wishlist'      => array(
				'endpoint'         => cdfs_get_theme_option( 'dashboard_endpoint_my-wishlist' ),
				'endpoint_default' => 'my-wishlist',
				'title'            => esc_html__( 'My Offers', 'cdfs-addon' ),
				'icon'             => 'far fa-bookmark',
			),
			'my-subscriptions' => array(
				'endpoint'         => cdfs_get_theme_option( 'dashboard_endpoint_my-subscriptions' ),
				'endpoint_default' => 'my-subscriptions',
				'title'            => esc_html__( 'My Subscriptions', 'cdfs-addon' ),
				'icon'             => 'far fa-hdd',
			),
			'profile'          => array(
				'endpoint'         => cdfs_get_theme_option( 'dashboard_endpoint_profile' ),
				'endpoint_default' => 'profile',
				'title'            => esc_html__( 'Profile', 'cdfs-addon' ),
				'icon'             => 'far fa-address-card',
			),
			'settings'         => array(
				'endpoint'         => cdfs_get_theme_option( 'dashboard_endpoint_settings' ),
				'endpoint_default' => 'settings',
				'title'            => esc_html__( 'Settings', 'cdfs-addon' ),
				'icon'             => 'fas fa-cog',
			),
			'view-profile'     => array(
				'endpoint'         => cdfs_get_theme_option( 'dashboard_endpoint_view-profile' ),
				'endpoint_default' => 'view-profile',
				'title'            => esc_html__( 'View Profile', 'cdfs-addon' ),
				'icon'             => 'fas fa-id-card-alt',
				'in_navbar'        => false,
				'in_dashgrid'      => false,
			),
			'dashboard-logout' => array(
				// 'endpoint'         => cdfs_get_theme_option( 'dashboard_endpoint_dashboard-logout' ),
				'endpoint'         => 'dashboard-logout',
				'endpoint_default' => 'dashboard-logout',
				'title'            => esc_html__( 'Logout', 'cdfs-addon' ),
				'icon'             => 'fas fa-sign-out-alt',
			),
		)
	);

	return $endpoints;
}
add_filter( 'cardealer_dashboard_endpoints', 'cardealer_dashboard_endpoints' );

function cdfs_get_user_profile_tabs_data( $return_type = 'all', $usertype = '' ) {
	$tabs = array(
		'overview'     => array(
			'title'             => esc_html__( 'Overview', 'cdfs-addon' ),
			'tab_icon'          => 'fas fa-info-circle',
			'content_type'      => 'callback',
			'callback'          => 'cardealer_dashboard__user_profile_tab__overview',
			'display'           => true,
			'allowed_user_type' => array(
				'dealer',
			),
		),
		'listing'      => array(
			'title'             => esc_html__( 'Listing', 'cdfs-addon' ),
			'tab_icon'          => 'fas fa-list',
			'content_type'      => 'callback',
			'callback'          => 'cardealer_dashboard__user_profile_tab__listing',
			'display'           => true,
			'allowed_user_type' => array(
				'dealer',
				'user',
			),
		),
		'reviews'      => array(
			'title'             => esc_html__( 'Reviews', 'cdfs-addon' ),
			'tab_icon'          => 'fas fa-comment',
			'content_type'      => 'callback',
			'callback'          => 'cardealer_dashboard__user_profile_tab__reviews',
			'display'           => true,
			'allowed_user_type' => array(
				'dealer',
			),
		),
		'write-review' => array(
			'title'             => esc_html__( 'Write a Review', 'cdfs-addon' ),
			'tab_icon'          => 'far fa-comment-dots',
			'content_type'      => 'callback',
			'callback'          => 'cardealer_dashboard__user_profile_tab__write_review',
			'display'           => true,
			'allowed_user_type' => array(
				'dealer',
			),
		),
		'location'     => array(
			'title'             => esc_html__( 'Location', 'cdfs-addon' ),
			'tab_icon'          => 'fas fa-map-marker-alt',
			'content_type'      => 'callback',
			'callback'          => 'cardealer_dashboard__user_profile_tab__location',
			'display'           => true,
			'allowed_user_type' => array(
				'dealer',
			),
		),
		'contact'     => array(
			'title'             => esc_html__( 'Contact', 'cdfs-addon' ),
			'tab_icon'          => 'fas fa-envelope',
			'content_type'      => 'callback',
			'callback'          => 'cardealer_dashboard__user_profile_tab__contact',
			'display'           => true,
			'allowed_user_type' => array(
				'dealer',
				'user',
			),
		),
	);

	$tabs = apply_filters( 'cardealer_dashboard_user_profile_tabs', $tabs );

	if ( ! empty( $usertype ) ) {
		$tabs = array_filter( $tabs, function( $v, $k ) use( $usertype ) {
			return in_array( $usertype, $v['allowed_user_type'], true );
		}, ARRAY_FILTER_USE_BOTH );
	}

	if ( 'options' === $return_type ) {
		$tabs = array_map( function( $tab_data ) {
			return $tab_data['title'];
		}, $tabs );
	} elseif ( 'defaults' === $return_type ) {
		$tabs = array_map( function( $tab_data ) {
			return ( isset( $tab_data['display'] ) ? filter_var( $tab_data['display'], FILTER_VALIDATE_BOOLEAN ) : false );
		}, $tabs );
	}

	return $tabs;
}

function cdfs_get_user_profile_tabs( $user_type = '' ) {
	global $car_dealer_options;

	$tabs     = array();
	$all_tabs = cdfs_get_user_profile_tabs_data();
	$field_k  = 'cdfs_profile_tabs_' . $user_type;

	$tabs_default    = array(
		'cars-image-gallery'  => '1',
		'cars-location'       => '1',
		'car-attributes'      => '1',
		'cars-review-stamps'  => '1',
		'cars-pdf-brochure'   => '1',
		'car-additional-info' => '1',
		'cars-excerpt'        => '1',
	);

	$selected_tabs = ( isset( $car_dealer_options[ $field_k ] ) && ! empty( $car_dealer_options[ $field_k ] ) ) ? $car_dealer_options[ $field_k ] : $tabs_default;

	foreach ( $selected_tabs as $tab_k => $visible ) {
		$visible = filter_var( $visible, FILTER_VALIDATE_BOOLEAN );
		if ( $visible && isset( $all_tabs[ $tab_k ] ) && ! empty( $all_tabs[ $tab_k ] ) ) {
			$tabs[ $tab_k ] = $all_tabs[ $tab_k ];
			$tabs[ $tab_k ]['tab_type'] = 'built_in';
		}
	}

	// Merge custom tabs.
	$custom_tabs = cdfs_get_custom_seller_profile_tabs( $user_type );
	$tabs        = array_merge( $tabs, $custom_tabs );

	foreach ( $tabs as $tab_k => $tab_data ) {
		// Check if title set.
		if ( ! isset( $tab_data['title'] ) ) {
			unset( $tabs[ $tab_k ] );
			continue;
		}

		// Check allowed user type.
		if ( ! in_array( $user_type, $tab_data['allowed_user_type'], true ) ) {
			unset( $tabs[ $tab_k ] );
			continue;
		}

		$has_content = false;

		if ( 'content' === $tab_data['content_type'] && ! empty( $tab_data['content'] ) ) {
			$has_content = true;
		}elseif ( 'callback' === $tab_data['content_type'] && ! empty( $tab_data['callback'] ) ) {
			$callback_found = false;

			if ( is_callable( $tab_data['callback'] ) ) {
				$callback_found = true;
			}

			if ( ! $callback_found ) {

				if ( 'built_in' === $tab_data['tab_type'] ) {
					$callbak_fallback = sanitize_title( "cardealer_dashboard__user_profile_tab__{$tab_k}" );
				}elseif ( 'custom' === $tab_data['tab_type'] ) {
					$callbak_fallback = sanitize_title( 'cardealer_dashboard__user_profile_tab__' . $tab_data['callback'] );
				}
				$callbak_fallback = str_replace( '-', '_', $callbak_fallback );

				if ( is_callable( $callbak_fallback ) ) {
					$tabs[ $tab_k ]['callback'] = $callbak_fallback;
					$callback_found = true;
				}
			}

			if ( $callback_found ) {
				$has_content = true;
			}
		}

		if ( ! $has_content ) {
			unset( $tabs[ $tab_k ] );
			continue;
		}
	}

	return $tabs;
}

function cdfs_limit_string_chars( $string, $limit_type = '' ) {
	$limit_pattern = '/[^a-z0-9]/';
	if ( 'slug' === $limit_type ) {
		$limit_pattern = '/[^a-z0-9-]/';
	}elseif ( 'function' === $limit_type ) {
		$limit_pattern = '/[^a-z0-9_]/';
	}

	// Strip out any %-encoded octets.
	$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $string );

	// Convert uppercase to lowercase.
	$sanitized = strtolower( $sanitized );

	// Limit to A-Z, a-z, 0-9, '_', '-'.
	$sanitized = preg_replace( $limit_pattern, '', $sanitized );

	return $sanitized;
}

function cdfs_get_custom_seller_profile_tabs( $user_type = '' ) {
	global $car_dealer_options;

	$custom_tabs          = array();
	$custom_tabs_k        = 'cdfs_custom_profile_tabs_' . $user_type;
	$custom_tab_sr        = 0;
	if ( isset( $car_dealer_options[ $custom_tabs_k ] ) && ! empty( $car_dealer_options[ $custom_tabs_k ] ) && is_array( $car_dealer_options[ $custom_tabs_k ] ) ) {
		if ( isset( $car_dealer_options[ $custom_tabs_k ]['redux_repeater_data'] ) && ! empty( $car_dealer_options[ $custom_tabs_k ]['redux_repeater_data'] ) && is_array( $car_dealer_options[ $custom_tabs_k ]['redux_repeater_data'] ) ) {
			$content_types        = array( 'content', 'callback' );
			$custom_tabs_repeater = $car_dealer_options[ $custom_tabs_k ]['redux_repeater_data'];
			$tab_title_k          = 'custom_profile_tab__tab_title__' . $user_type;
			$tab_slug_k           = 'custom_profile_tab__tab_slug__' . $user_type;
			$tab_icon_k           = 'custom_profile_tab__tab_icon__' . $user_type;
			$tab_content_type_k   = 'custom_profile_tab__tab_content_type__' . $user_type;
			$tab_content_k        = 'custom_profile_tab__tab_content__' . $user_type;
			$tab_callback_k       = 'custom_profile_tab__tab_callback__' . $user_type;

			$slug_duplicates = array();
			foreach ( $custom_tabs_repeater as $custom_tabs_repeater_k => $custom_tabs_repeater_data ) {
				$title_data        = false;
				$slug_data         = false;
				$icon_data         = false;
				$content_type_data = false;
				$content_data      = false;
				$callback_data     = false;

				// Title.
				if (
					isset( $car_dealer_options[ $tab_title_k ] )
					&& is_array( $car_dealer_options[ $tab_title_k ] )
					&& ! empty( $car_dealer_options[ $tab_title_k ] )
					&& isset( $car_dealer_options[ $tab_title_k ][ $custom_tabs_repeater_k ] )
					&& ! empty( $car_dealer_options[ $tab_title_k ][ $custom_tabs_repeater_k ] )
				) {
					$title_data = $car_dealer_options[ $tab_title_k ][ $custom_tabs_repeater_k ];
				}

				// Slug.
				if (
					isset( $car_dealer_options[ $tab_slug_k ] )
					&& is_array( $car_dealer_options[ $tab_slug_k ] )
					&& ! empty( $car_dealer_options[ $tab_slug_k ] )
					&& isset( $car_dealer_options[ $tab_slug_k ][ $custom_tabs_repeater_k ] )
					&& ! empty( $car_dealer_options[ $tab_slug_k ][ $custom_tabs_repeater_k ] )
				) {
					$slug_data = $car_dealer_options[ $tab_slug_k ][ $custom_tabs_repeater_k ];
					$slug_data = cdfs_limit_string_chars( $slug_data, 'slug' );
				}

				if ( $title_data && ! $slug_data ) {
					$slug_data = cdfs_limit_string_chars( str_replace( ' ', '-', $title_data ), 'slug' );
				}

				// Icon.
				if (
					isset( $car_dealer_options[ $tab_icon_k ] )
					&& is_array( $car_dealer_options[ $tab_icon_k ] )
					&& ! empty( $car_dealer_options[ $tab_icon_k ] )
					&& isset( $car_dealer_options[ $tab_icon_k ][ $custom_tabs_repeater_k ] )
					&& ! empty( $car_dealer_options[ $tab_icon_k ][ $custom_tabs_repeater_k ] )
				) {
					$icon_data = $car_dealer_options[ $tab_icon_k ][ $custom_tabs_repeater_k ];
				}

				// Content type.
				if (
					isset( $car_dealer_options[ $tab_content_type_k ] )
					&& is_array( $car_dealer_options[ $tab_content_type_k ] )
					&& ! empty( $car_dealer_options[ $tab_content_type_k ] )
					&& isset( $car_dealer_options[ $tab_content_type_k ][ $custom_tabs_repeater_k ] )
					&& ! empty( $car_dealer_options[ $tab_content_type_k ][ $custom_tabs_repeater_k ] )
					&& in_array( $car_dealer_options[ $tab_content_type_k ][ $custom_tabs_repeater_k ], $content_types, true )
				) {
					$content_type_data = $car_dealer_options[ $tab_content_type_k ][ $custom_tabs_repeater_k ];
				}

				if (
					( $content_type_data && 'content' === $content_type_data )
					&& isset( $car_dealer_options[ $tab_content_k ] )
					&& is_array( $car_dealer_options[ $tab_content_k ] )
					&& ! empty( $car_dealer_options[ $tab_content_k ] )
					&& isset( $car_dealer_options[ $tab_content_k ][ $custom_tabs_repeater_k ] )
					&& ! empty( $car_dealer_options[ $tab_content_k ][ $custom_tabs_repeater_k ] )
				) {
					$content_data = $car_dealer_options[ $tab_content_k ][ $custom_tabs_repeater_k ];
				}

				if (
					( $content_type_data && 'callback' === $content_type_data )
					&& isset( $car_dealer_options[ $tab_callback_k ] )
					&& is_array( $car_dealer_options[ $tab_callback_k ] )
					&& ! empty( $car_dealer_options[ $tab_callback_k ] )
					&& isset( $car_dealer_options[ $tab_callback_k ][ $custom_tabs_repeater_k ] )
					&& ! empty( $car_dealer_options[ $tab_callback_k ][ $custom_tabs_repeater_k ] )
				) {
					$callback_data = $car_dealer_options[ $tab_callback_k ][ $custom_tabs_repeater_k ];
				}

				if (
					$title_data
					&& ( ( 'content' === $content_type_data && $content_data ) || ( 'callback' === $content_type_data && $callback_data ) )
				) {
					$custom_tab_sr++;
					$custom_tab_key = 'custom-tab-' . $custom_tab_sr;

					$slug_duplicates[ $slug_data ][] = $slug_data;

					if ( count( $slug_duplicates[ $slug_data ] ) > 1 ) {
						$custom_tab_key = $slug_data . '-' . count( $slug_duplicates[ $slug_data ] );
					} else {
						$custom_tab_key = $slug_data;
					}

					$custom_tabs[ $custom_tab_key ] = array(
						'title'             => $title_data,
						'tab_icon'          => $icon_data,
						'content_type'      => $content_type_data,
						'display'           => true,
						'allowed_user_type' => array( $user_type ),
						'tab_type'          => 'custom',
					);
					if ( 'content' === $content_type_data ) {
						$custom_tabs[ $custom_tab_key ]['content'] = $content_data;
					}elseif ( 'callback' === $content_type_data ) {
						$custom_tabs[ $custom_tab_key ]['callback'] = $callback_data;
					}

				}
			}
		}
	}

	return $custom_tabs;
}

/**
 * Get dashboard menu item classes.
 *
 * @param string $endpoint Endpoint.
 * @return string
 */
function cdfs_dashboard_item_classes( $endpoint ) {
	global $wp;

	$qv = $wp->query_vars;
	unset( $qv['author_name'] );

	$classes = array(
		'cardealer-dashboard-navigation-link',
		'cardealer-dashboard-navigation-link-' . $endpoint,
	);

	// Set current item class.
	$current = isset( $qv[ $endpoint ] );

	if ( empty( $qv ) && 'dashboard' === $endpoint ) {
		$current = true; // Dashboard is not an endpoint, so needs a custom check.
	}

	if ( $current ) {
		$classes[] = 'is-active';
	}

	$classes = apply_filters( 'woocommerce_account_menu_item_classes', $classes, $endpoint );

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}

/**
 * Get account endpoint URL.
 *
 * @param string $endpoint_id Endpoint ID.
 * @return string
 */
function cdfs_get_cardealer_dashboard_endpoint_url( $endpoint_id = '', $value = '' ) {
	$current_user = wp_get_current_user();
	$author_url   = get_author_posts_url( $current_user->ID );

	if ( empty( $endpoint_id ) || 'dashboard' === $endpoint_id ) {
		return $author_url;
	}

	$endpoint = cdfs_get_theme_option( 'dashboard_endpoint_' . $endpoint_id );
	if ( 'dashboard-logout' === $endpoint_id ) {
		$login_link = cdfs_get_page_permalink( 'dealer_login' );
		return wp_logout_url( $login_link );
	}

	$permalink_structure = get_option( 'permalink_structure' );

	if ( $value ) {
		if ( '' === $permalink_structure ) {
			$url = add_query_arg( array(
				$endpoint => $value,
			), $author_url );
		} else {
			$url = trailingslashit( trailingslashit( $author_url ) . $endpoint ) . user_trailingslashit( $value );
		}
	} else {
		if ( '' === $permalink_structure ) {
			$url = add_query_arg( array(
				$endpoint => '',
			), $author_url );
		} else {
			$url = user_trailingslashit( trailingslashit( $author_url ) . $endpoint );
		}
	}

	return $url;
}

/**
 * Get dashboard endpoint title.
 *
 * @param string $endpoint_id Endpoint ID.
 * @param string $action      Action.
 * @return string
 */
function cdfs_get_cardealer_dashboard_endpoint_title( $endpoint, $action = '' ) {
	$endpoint_title = '';
	$endpoints      = cdfs_get_dashboard_endpoints();

	if ( isset( $endpoints[ $endpoint ] ) && isset( $endpoints[ $endpoint ]['title'] ) && ! empty( $endpoints[ $endpoint ]['title'] ) ) {
		$endpoint_title = $endpoints[ $endpoint ]['title'];
	}

	return apply_filters( 'cardealer-dashboard/endpoint-title', $endpoint_title, $endpoint, $endpoints );
}

function cdfs_set_usertypes( $types ) {

	$types = array_merge(
		$types,
		array(
			'user'   => array(
				'label'  => esc_html__( 'User', 'cdfs-addon' ),
			),
			'dealer' => array(
				'label'  => esc_html__( 'Dealer', 'cdfs-addon' ),
			),
		)
	);

	return $types;
}
add_filter( 'cdfs_usertypes', 'cdfs_set_usertypes' );

function cdfs_get_usertypes() {
	global $car_dealer_options;

	$car_dealer_options = ( ! empty( $car_dealer_options ) ) ? $car_dealer_options : get_option( 'car_dealer_options' );
	$usertypes          =  apply_filters( 'cdfs_usertypes', array() );
	$usertypes_new      = array();

	foreach ( $usertypes as $usertype_k => $usertype_data ) {
		$label__key = 'cdfs_usertype_label_' . $usertype_k;
		$usertypes[ $usertype_k ]['label_original'] = $usertypes[ $usertype_k ]['label'];

		if ( isset( $car_dealer_options[ $label__key ] ) && ! empty( $car_dealer_options[ $label__key ] ) ) {
			$usertypes[ $usertype_k ]['label'] = $car_dealer_options[ $label__key ];
		}
	}

	return apply_filters( 'cdfs_get_usertypes', $usertypes, $car_dealer_options );
}

function cdfs_get_usertype_userroles() {
	$userroles = array(
		'dealer' => 'car_dealer',
		'user'   => 'subscriber',
	);
	return apply_filters( 'cdfs_get_usertype_userroles', $userroles );
}

function cdfs_get_usertype_userrole( $usertype = 'user' ) {
	$usertypes = cdfs_get_usertypes();
	$usertype  = ( ! empty( $usertype ) && array_key_exists( $usertype, $usertypes ) ) ? $usertype : 'user';
	$userroles = cdfs_get_usertype_userroles();
	$userrole  = $userroles[ $usertype ];

	return apply_filters( 'cdfs_get_usertype_userroles', $userrole, $userroles, $usertype, $usertypes );
}

function cdfs_get_usertype( $user = false ) {
	$usertype = ( cdfs_has_user_role( 'car_dealer', $user ) ) ? 'dealer' : 'user';
	return apply_filters( 'cdfs_get_usertype', $usertype, $user );
}

function cdfs_get_usertype_label( $user = false ) {
	$usertypes      = cdfs_get_usertypes();
	$usertype       = cdfs_get_usertype( $user );
	$usertype_label = '';

	$usertype_label = ( isset( $usertypes[ $usertype ]['label'] ) && ! empty( $usertypes[ $usertype ]['label'] ) ) ? $usertypes[ $usertype ]['label'] : $usertypes[ $usertype ]['label_original'];

	return apply_filters( 'cdfs_get_usertype_label', $usertype_label, $usertype, $usertypes, $user );
}

function cdfs_has_user_role( $role, $user = false ) {
	if ( ! $user ) {
		$user = wp_get_current_user();
	}

	if ( in_array( $role, (array) $user->roles ) ) {
		return true;
	}

	return false;
}


/*
 * Get add car url.
 *
 * @return string
 */
function cdfs_get_add_car_url( $id = null ) {
	global $car_dealer_options;

	$url = cdfs_get_page_permalink( 'add_car' );
	if ( $id ) {
		// Add car page based on category
		$data = get_the_terms( $id, 'vehicle_cat' );
		if ( isset( $data[0]->slug ) && $data[0]->slug ) {
			$page_id = isset( $car_dealer_options['cdfs_' . $data[0]->slug . '_add_car_page_id'] ) ? $car_dealer_options['cdfs_' . $data[0]->slug . '_add_car_page_id'] : '';
			if ( $page_id ) {
				$url = get_permalink( $page_id );
			}
		}
	}

	return $url;
}


function cdfs_get_cardealer_dashboard_car_edit_button( $id ) {
	$cdfs_nonce  = wp_create_nonce( 'cdhl-action' );
	$add_car_url = cdfs_get_add_car_url( $id );

	$edit_car_url = add_query_arg( array(
		'edit-car'        => '1',
		'car-id'          => $id,
		'cdfs_nonce'      => $cdfs_nonce,
	), $add_car_url );
	?>
	<li>
		<a href="<?php echo esc_url( $edit_car_url ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Edit', 'cdfs-addon' ); ?>" class="edit-car" >
			<i class="fa fa-pencil" ></i><?php esc_attr_e( 'Edit', 'cdfs-addon' ); ?>
		</a>
	</li>
	<?php
}

function cdfs_get_cardealer_dashboard_car_actions_button( $id ) {

	$post_status = get_post_status( $id );
	$cdfs_url    = cdfs_get_cardealer_dashboard_endpoint_url();
	$cdfs_nonce  = wp_create_nonce( 'cdhl-action' );

	if ( $post_status ) {
			if ( 'draft' === $post_status || 'pending' === $post_status ) {
				$delete_car_url = add_query_arg( array(
					'cdfs_car_action' => 'trash',
					'id'              => $id,
					'cdfs_nonce'      => $cdfs_nonce,
				), $cdfs_url );
			?>
			<li><a href="<?php echo esc_url( $delete_car_url ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete', 'cdfs-addon' ); ?>" class="delete-car" data-alttxt="<?php esc_html_e( 'Alert', 'cdfs-addon' ); ?>" ><i class="fa fa-trash-o" ></i><?php esc_attr_e( 'Delete', 'cdfs-addon' ); ?></a></li>
			<?php
		}
		if ( 'draft' === $post_status ) {
			$enable_car_url = add_query_arg( array(
				'cdfs_car_action' => 'enable',
				'id'              => $id,
				'cdfs_nonce'      => $cdfs_nonce,
			), $cdfs_url );
			?>
			<li><a href="<?php echo esc_url( $enable_car_url ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Enable', 'cdfs-addon' ); ?>" class="edit-car" ><i class="fa fa-check-circle-o" ></i><?php esc_attr_e( 'Enable', 'cdfs-addon' ); ?></a></li>
			<?php
		}
		if ( 'publish' === $post_status ) {
			$disable_car_url = add_query_arg( array(
				'cdfs_car_action' => 'disable',
				'id'              => $id,
				'cdfs_nonce'      => $cdfs_nonce,
			), $cdfs_url );
			?>
			<li><a href="<?php echo esc_url( $disable_car_url ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Disable', 'cdfs-addon' ); ?>" class="edit-car" ><i class="fa fa-ban" ></i><?php esc_attr_e( 'Disable', 'cdfs-addon' ); ?></a></li>
			<?php
		}
	}
}

function cdfs_get_cardealer_dashboard_car_clone_button( $id ) {
	$post_status = get_post_status( $id );
	$cdfs_url    = cdfs_get_cardealer_dashboard_endpoint_url();
	$cdfs_nonce  = wp_create_nonce( 'cdhl-action' );

	if ( cdfs_is_vehicle_clone_enabled() && 'publish' === $post_status ) {
		$clone_url = add_query_arg( array(
			'cdfs_car_action' => 'clone',
			'id'              => $id,
			'cdfs_nonce'      => $cdfs_nonce,
		), $cdfs_url );
		?>
		<li>
			<a href="<?php echo esc_url( $clone_url ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Clone', 'cdfs-addon' ); ?>" class="edit-car" ><i class="fa fa-clone" ></i><?php esc_attr_e( 'Clone', 'cdfs-addon' ); ?></a>
		</li>
		<?php
	}
}

/**
 * Get user available subscription limit.
 *
 * @param string $subscription_id subscription id.
 * @return int
 */
function cdfs_get_user_subscription_cars( $subscription_id, $user_id ) {
	$cars = array();

	$args = array(
		'post_type'      => 'cars',
		'posts_per_page' => -1,
		'orderby'        => 'id',
		'author'         => $user_id,
		'order'          => 'DESC',
		'post_status'    => 'publish',
	);

	if ( 'free' === $subscription_id ) {
		$args['meta_query'] = array(
			'relation' => 'AND',
			// array(
				// 'key'     => 'cdfs_listing_type',
				// 'compare' => 'NOT EXISTS',
			// ),
			array(
				'relation' => 'OR',
				array(
					'key'     => 'cdfs_subscription_id',
					'value'   => $subscription_id,
				),
				array(
					'key'     => 'cdfs_subscription_id',
					'compare' => 'NOT EXISTS',
				),
			),
		);
	} elseif ( 'listing_payment' === $subscription_id ) {
		$args['meta_query'] = array(
			'relation' => 'AND',
			// array(
				// 'key'     => 'cdfs_listing_type',
				// 'compare' => 'NOT EXISTS',
			// ),
			array(
				'relation' => 'AND',
				array(
					'key'     => 'cdfs_listing_type',
					'value'   => 'listing_payment',
				),
				array(
					'key'     => 'cdfs_subscription_id',
					'compare' => 'listing_payment',
				),
			),
		);
	} else {
		$args['post_status'] = 'any';
		$args['meta_query']  = array(
			array(
				'key'     => 'cdfs_subscription_id',
				'value'   => $subscription_id,
			),
		);
	}

	$cars_data = new WP_Query( $args );

	foreach ( $cars_data->posts as $car ) {
		$cars[ $car->ID ] = $car;
	}

	return $cars;
}

/**
 * Get user available subscription limit.
 *
 * @param string $subscription_id subscription id.
 * @return int
 */
function cdfs_get_user_subscription_available_car_limit( $subscription_id, $user_id ) {

	$avl_limit      = 0;
	$free_car_limit = cdfs_get_free_cars_limit();

	$args = array(
		'post_type'      => 'cars',
		'posts_per_page' => -1,
		'orderby'        => 'id',
		'author'         => $user_id,
		'order'          => 'DESC',
		'post_status'    => 'any',
	);

	if ( 'free' === $subscription_id ) {
		// $args['post_status'] = 'publish';
		$args['meta_query']  = array(
			'relation' => 'AND',
			// array(
				// 'key'     => 'cdfs_listing_type',
				// 'compare' => 'NOT EXISTS',
			// ),
			array(
				'relation' => 'OR',
				array(
					'key'     => 'cdfs_subscription_id',
					'value'   => $subscription_id,
				),
				array(
					'key'     => 'cdfs_subscription_id',
					'compare' => 'NOT EXISTS',
				),
			),
		);
	} elseif ( 'listing_payment' === $subscription_id ) {
	} else {
		$args['meta_query'] = array(
			array(
				'key'     => 'cdfs_subscription_id',
				'value'   => $subscription_id,
			),
		);
	}

	$added_cars = 0;

	if ( 'listing_payment' !== $subscription_id ) {
		$cars_data  = new WP_Query( $args );
		$added_cars = isset( $cars_data->found_posts ) ? (int) $cars_data->found_posts : 0;
	}

	if ( 'free' === $subscription_id ) {
		$avl_limit  = $free_car_limit - $added_cars;
		/*
		if ( $added_cars ) {
		} elseif( $added_cars > $free_car_limit ) {
			$avl_limit = 0;
		} else {
			$avl_limit = $free_car_limit;
		}
		*/
	} elseif ( 'listing_payment' === $subscription_id ) {
		$avl_limit = 1;
	} else {
		$subscription_data = subscriptio_get_subscription( $subscription_id );
		$status            = ( $subscription_data ) ? $subscription_data->get_status() : '';
		if ( 'active' === $status ) {
			$cdfs_car_limt = (int) get_post_meta( $subscription_id, 'cdfs_car_limt', true );
			if ( $added_cars ) {
				$avl_limit  = $cdfs_car_limt - $added_cars;
			} elseif( $added_cars > $cdfs_car_limt ) {
				$avl_limit = 0;
			} else {
				$avl_limit = $cdfs_car_limt;
			}
		}
	}

	if ( $avl_limit < 0 ) {
		// $avl_limit = 0;
	}

	return (int) $avl_limit;
}

/**
 * Get user subscription image limit.
 *
 * @param string $subscription_id subscription id.
 * @return int
 */
function cdfs_get_user_car_subscription_image_limit( $subscription_id ) {
	global $car_dealer_options;

	$avl_limit      = 0;
	if ( 'free' === $subscription_id ) {
		$free_img_limit = cdfs_get_free_cars_image_limit();
		$avl_limit      = $free_img_limit;
	} elseif ( 'listing_payment' === $subscription_id ) {
		$avl_limit = cdfs_get_item_listing_image_limit();
	} else {
		$cdfs_img_limt     = (int) get_post_meta( $subscription_id, 'cdfs_img_limt', true );
		$subscription_data = subscriptio_get_subscription( $subscription_id );
		$status            = $subscription_data->get_status();
		if ( 'active' === $status ) {
			$avl_limit = $cdfs_img_limt;
		}
	}

	return $avl_limit;
}

function cdfs_add_custom_hidden_field( $fields ) {

	global $cdfs_seller_form, $cdfs_seller_form_fields;

	if ( $cdfs_seller_form ) {
		$fields = array_merge( $fields, $cdfs_seller_form_fields );
	}

	return $fields;
}
add_filter( 'wpcf7_form_hidden_fields', 'cdfs_add_custom_hidden_field' );


function cdfs_seller_contact_form_before_send_mail( $contact_form, &$abort, $submission ) {
	global $car_dealer_options;

	$posted_data = array();
	$usertypes   = cdfs_get_usertypes();

	if ( $submission ) {
		$posted_data = $submission->get_posted_data();
	}

	if (
		( isset( $posted_data['cdfs_seller_form'] ) && 'yes' === $posted_data['cdfs_seller_form'] )
		&& ( isset( $posted_data['cdfs_seller_form_user_type'] ) && array_key_exists( $posted_data['cdfs_seller_form_user_type'], $usertypes ) )
	) {
		$user_type   = sanitize_text_field( wp_unslash( $posted_data['cdfs_seller_form_user_type'] ) );
		$user_id     = absint( sanitize_text_field( wp_unslash( $posted_data['cdfs_seller_form_user_id'] ) ) );
		$user_email  = cdfs_get_user_email( $user_id );
		$sendmail_to = false;

		if ( isset( $car_dealer_options[ 'cdfs_usertyp_sendmail_to_' . $user_type ] ) && ! empty( $car_dealer_options[ 'cdfs_usertyp_sendmail_to_' . $user_type ] ) ) {
			$sendmail_to = $car_dealer_options[ 'cdfs_usertyp_sendmail_to_' . $user_type ];
		}

		if ( 'seller' === $sendmail_to && $user_email ) {
			$mail = $contact_form->prop( 'mail' );
			$mail['recipient'] = sanitize_email( $user_email );
			$contact_form->set_properties(
				array(
					'mail' => $mail,
				)
			);
		}
	}

	return $contact_form;
}

add_action( 'wpcf7_before_send_mail', 'cdfs_seller_contact_form_before_send_mail', 8, 3 );

function cdfs_get_plan_url() {
	global $car_dealer_options;

	$get_plan_url = '';

	if ( isset( $car_dealer_options['cdfs_plan_pricing_page'] ) && ! empty( $car_dealer_options['cdfs_plan_pricing_page'] ) ) {
		$get_plan_url = get_permalink( $car_dealer_options['cdfs_plan_pricing_page'] );
	}

	return apply_filters( 'cardealer-dashboard/get-plan-url', $get_plan_url );
}


function cdfs_get_view_public_profile_url() {

	// $url = cdfs_get_cardealer_dashboard_endpoint_url( 'view-profile' );

	$url = add_query_arg( array(
		'cdfs-action' => 'view-public-profile',
	), cdfs_get_cardealer_dashboard_endpoint_url() );

	return apply_filters( 'cdfs_get_view_public_profile_url', $url );
}

function cdfs_get_exit_public_profile_view_url() {

	$url = add_query_arg( array(
		'cdfs-action' => 'exit-public-profile',
	), cdfs_get_cardealer_dashboard_endpoint_url() );

	return apply_filters( 'cdfs_get_exit_public_profile_view_url', $url );
}
