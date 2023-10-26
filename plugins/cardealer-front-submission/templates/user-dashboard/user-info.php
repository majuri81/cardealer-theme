<?php
$user             = wp_get_current_user();
$view_profile_url = "cdfs_get_view_public_profile_url()";
$user_type        = cdfs_get_usertype( $user );
$user_type_label  = cdfs_get_usertype_label( $user );
$avatar_url       = cdfs_get_avatar_url( $user->ID );
?>
<div class="cardealer-dashboard-user-info text-center">
	<div class="cardealer-dashboard-user-info--profile-img">
		<a class="cardealer-dashboard-user-info--profile-img-link" href="<?php echo esc_url( $view_profile_url ); ?>">
			<?php
			echo sprintf(
				'<img alt="%s" src="%s" class="%s" width="%s" height="%s">',
				esc_attr( $user->display_name ),
				esc_url( $avatar_url ),
				esc_attr( 'cardealer-dashboard-user-info--profile-avatar img-circle' ),
				90,
				90
			);
			?>
		</a>
	</div>
	<span class="cardealer-dashboard-user-info--profile-title-link"><h3 class="cardealer-dashboard-user-info--profile-title"><?php echo esc_html( $user->display_name ); ?></h3></span>
	<span class="cardealer-dashboard-user-info--profile-type cardealer-dashboard-user-info--profile-type-<?php echo esc_attr( $user_type ); ?>"><?php echo esc_html( $user->user_registration_company_name /* $user_type_label */ ); ?></span> 
	<span class="cardealer-dashboard-user-info--profile-email"><?php echo esc_html( $user->user_email ); ?></span> 
	<?php
	cdfs_get_template(
		'user-dashboard/social-profiles.php',
		array(
			'user'      => $user,
		)
	);
	?>
</div>
