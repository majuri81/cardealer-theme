<?php
do_action( 'cardealer-dashboard/view-profile-user/before-view-profile', $user );

do_action( 'cardealer-dashboard/view-profile-user/{$layout}/before-view-profile', $user );

cdfs_get_template(
	"user-dashboard/view-profile/user-layout/{$layout}.php",
	array(
		'user'      => $user,
		'user_type' => $user_type,
		'layout'    => $layout,
	)
);

do_action( 'cardealer-dashboard/view-profile-user/{$layout}/after-view-profile', $user );

do_action( 'cardealer-dashboard/view-profile-user/after-view-profile', $user );
