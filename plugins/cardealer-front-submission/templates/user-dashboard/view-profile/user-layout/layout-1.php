<div class="cardealer-userdash-header">

	<?php
	do_action( 'cardealer-dashboard/view-profile-user/before-header', $user );

	/*
	 * cardealer-dashboard/user-dashboard/view-profile-user/header hook.
	 *
	 * Hooked: cardealer_dashboard__view_profile_dealer__userinfo - 10
	 */
	do_action( 'cardealer-dashboard/user-dashboard/view-profile-user/header', $user );

	do_action( 'cardealer-dashboard/user-dashboard/view-profile-user/after-header', $user );
	?>

</div>
<div class="cardealer-userdash-content">

	<?php
	do_action( 'cardealer-dashboard/user-dashboard/view-profile-user/before-content', $user );

	/*
	 * cardealer-dashboard/user-dashboard/view-profile-user/content hook.
	 *
	 * Hooked: cardealer_dashboard__view_profile_tabs - 10
	 */
	do_action( 'cardealer-dashboard/user-dashboard/view-profile-user/content', $user );

	do_action( 'cardealer-dashboard/user-dashboard/view-profile-user/after-content', $user );
	?>

</div>
