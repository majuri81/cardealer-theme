<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/my-user-account/user-details-car-page.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() ) {
	return;
}
do_action( 'cdfs_before_car_user_profile' );
?>
<div class="cdfs-user-account" style="display:none" >
	<div class="col-sm-12">
		<div class="section-left">
			<?php esc_html_e( 'Welcome', 'cdfs-addon' ); ?> <strong><span id="cdfs_user_name" class="dealer-name"> </strong>
		</div>
		<div class="section-right">
			<a href="<?php echo esc_url( cdfs_get_cardealer_dashboard_endpoint_url( 'dashboard-logout' ) ); ?>"><?php echo esc_html__( 'Logout?', 'cdfs-addon' ); ?></a>
		</div>
	</div>
</div>

<?php do_action( 'cdfs_after_car_user_profile' ); ?>
