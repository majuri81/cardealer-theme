<?php
$social_profiles       = apply_filters( 'cardealer_user_profile_meta_fields_social_profiles', array() );
$social_profiles_count = 0;
$social_profile_links  = '';
foreach ( $social_profiles as $profile_ids => $profile ) {
	$profile_icon = $profile['icon'];
	$profile_url  = get_user_meta( $user->ID, $profile_ids, true );
	if ( $profile_url ) {
		$social_profiles_count++;
		$social_profile_links .= '<li class="cardealer-dashboard-user-info--social-profile cardealer-dashboard-user-info--social-profile-' . $profile_ids . '">';
		$social_profile_links .= '<a href="' . esc_url( $profile_url ) . '" target="_blank" rel="noreferrer noopener"><i class="' . esc_attr( $profile_icon ) . '"></i></a>';
		$social_profile_links .= '</li>';
	}
}
if ( $social_profiles_count > 0 && ! empty( $social_profile_links ) ) {
	?>
	<div class="cardealer-dashboard-user-info--social-profiles-wrap">
		<ul class="cardealer-dashboard-user-social-profiles">
		<?php
		echo wp_kses(
			$social_profile_links,
			array(
				'li' => array(
					'class' => true,
				),
				'a'  => array(
					'href'   => true,
					'target' => true,
					'rel'    => true,
				),
				'i'  => array(
					'class' => true,
				),
			)
		);
		?>
		</ul>
	</div>
	<?php
}
