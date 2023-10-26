<?php
$user      = get_queried_object();
$user_type = cdfs_get_usertype( $user );
$layout    = cdfs_get_profile_layout( $user->ID );

$view_profile_classes = array(
	'cardealer-userdash',
	'cardealer-userdash-' . $user->ID,
	'cardealer-userdash-type-' . $user_type,
	'cardealer-userdash-layout-' . $layout,
);

$view_profile_classes_str = implode( ' ', array_filter( array_unique( $view_profile_classes ) ) );

cdfs_get_template( 'user-dashboard/dashboard-header.php' );

	do_action( 'cardealer-dashboard/view-profile/before-view-profile', $user );
	?>
	<div id="cardealer-userdash-<?php echo esc_attr( $user->ID ); ?>" class="<?php echo esc_attr( $view_profile_classes_str ); ?>">
		<?php
		cdfs_get_template(
			"user-dashboard/view-profile/view-profile-{$user_type}.php",
			array(
				'user'      => $user,
				'user_type' => $user_type,
				'layout'    => $layout,
			)
		);
		?>
	</div>
	<?php
	do_action( 'cardealer-dashboard/view-profile/after-view-profile', $user );

cdfs_get_template( 'user-dashboard/dashboard-footer.php' );
