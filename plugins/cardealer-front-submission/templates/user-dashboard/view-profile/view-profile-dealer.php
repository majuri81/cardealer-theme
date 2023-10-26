<?php
do_action( 'cardealer-dashboard/view-profile-dealer/before-view-profile', $user );

do_action( 'cardealer-dashboard/view-profile-dealer/{$layout}/before-view-profile', $user );

cdfs_get_template(
	"user-dashboard/view-profile/dealer-layout/{$layout}.php",
	array(
		'user'      => $user,
		'user_type' => $user_type,
		'layout'    => $layout,
	)
);

do_action( 'cardealer-dashboard/view-profile-dealer/{$layout}/after-view-profile', $user );

do_action( 'cardealer-dashboard/view-profile-dealer/after-view-profile', $user );
