<?php
add_action( 'cardealer-dashboard/view-profile/before-view-profile', 'cdfs_viewing_public_profile_notice', 0 );

// Dashboard Sidebar.
add_action( 'cardealer-dashboard/sidebar', 'cardealer_dashboard_user_info' );
add_action( 'cardealer-dashboard/sidebar', 'cardealer_dashboard_navigation' );

// Dashboard Content.
add_action( 'cardealer-dashboard/header', 'cardealer_dashboard_header' );
add_action( 'cardealer-dashboard/content', 'cardealer_dashboard_content' );
add_action( 'cardealer-dashboard/before-content', 'cdfs_print_notices' );

// Dashboard Endpoint Content.
add_action( 'cardealer-dashboard/endpoint-content/dashboard', 'cardealer_dashboard_endpoint_content_dashboard' );
add_action( 'cardealer-dashboard/endpoint-content/my-items', 'cardealer_dashboard_endpoint_content_items' );
add_action( 'cardealer-dashboard/endpoint-content/my-wishlist', 'cardealer_dashboard_endpoint_content_wishlist' );
add_action( 'cardealer-dashboard/endpoint-content/my-subscriptions', 'cardealer_dashboard_endpoint_content_subscriptions' );
add_action( 'cardealer-dashboard/endpoint-content/profile', 'cardealer_dashboard_endpoint_content_profile' );
add_action( 'cardealer-dashboard/endpoint-content/settings', 'cardealer_dashboard_endpoint_content_settings' );

// Edit Profile
add_action( 'cardealer-dashboard/edit-profile/other-information-end', 'cardealer_dashboard_edit_profile_add_overview_field' );
add_action( 'cardealer-dashboard/edit-profile/other-information-end', 'cardealer_dashboard_edit_profile_add_location_field', 15 );

// View Profile (Dealer)
add_action( 'cardealer-dashboard/user-dashboard/view-profile-dealer/header', 'cardealer_dashboard__view_profile_dealer__banner' );
add_action( 'cardealer-dashboard/user-dashboard/view-profile-dealer/header', 'cardealer_dashboard__view_profile_dealer__userinfo', 20 );
add_action( 'cardealer-dashboard/user-dashboard/view-profile-dealer/content', 'cardealer_dashboard__user_profile_tabs' );
add_action( 'cardealer-profile/dealer-review/before-form', 'cdfs_print_notices' );

// View Profile (User)
add_action( 'cardealer-dashboard/user-dashboard/view-profile-user/header', 'cardealer_dashboard__view_profile_dealer__userinfo' );
add_action( 'cardealer-dashboard/user-dashboard/view-profile-user/content', 'cardealer_dashboard__user_profile_tabs' );
