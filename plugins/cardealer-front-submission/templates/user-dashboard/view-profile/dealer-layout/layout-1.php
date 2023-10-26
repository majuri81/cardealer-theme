<div class="cardealer-userdash-header">

	<?php
	do_action( 'cardealer-dashboard/view-profile-dealer/before-header', $user );

	/*
	 * cardealer-dashboard/user-dashboard/view-profile-dealer/header hook.
	 *
	 * Hooked: cardealer_dashboard__view_profile_dealer__banner - 10
	 * Hooked: cardealer_dashboard__view_profile_dealer__userinfo - 20
	 */
	do_action( 'cardealer-dashboard/user-dashboard/view-profile-dealer/header', $user );

	do_action( 'cardealer-dashboard/user-dashboard/view-profile-dealer/after-header', $user );
	?>

</div>
<div class="cardealer-userdash-content">

	<?php
	do_action( 'cardealer-dashboard/user-dashboard/view-profile-dealer/before-content', $user );

	/*
	 * cardealer-dashboard/user-dashboard/view-profile-dealer/content hook.
	 *
	 * Hooked: cardealer_dashboard__view_profile_tabs - 10
	 */
	do_action( 'cardealer-dashboard/user-dashboard/view-profile-dealer/content', $user );

	do_action( 'cardealer-dashboard/user-dashboard/view-profile-dealer/after-content', $user );
	?>

</div>
