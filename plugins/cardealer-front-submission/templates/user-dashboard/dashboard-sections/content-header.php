<?php global $wp; ?>
<div class="cardealer-dashboard-content-header-buttons">
	<a href="<?php echo esc_url( $add_car_url ); ?>" class="cardealer-dashboard-content-header-add-car btn btn-primary"><i class="fas fa-plus"></i><?php echo esc_html__( 'Add Car', 'cdfs-addon' ); ?></a>
	<?php
	if ( $pricing_plan_enabled && isset( $wp->query_vars['my-subscriptions'] ) && ! empty( $get_plan_url ) ) {
		?>
		<a href="<?php echo esc_url( $get_plan_url ); ?>" class="cardealer-dashboard-content-header-add-car btn btn-primary"><i class="far fa-hdd"></i><?php echo esc_html( $get_plan_label ); ?></a>
		<?php
	}
	?>
</div>
<a href="<?php echo esc_url( $logout_url ); ?>" class="cardealer-dashboard-content-header-logout btn btn-default pull-right"><?php echo esc_html__( 'Logout', 'cdfs-addon' ); ?> <i class="fas fa-power-off"></i></a>
