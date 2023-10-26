<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'cardealer_before_dashboard_navigation' );
?>

<nav class="cardealer-dashboard-navigation">
	<ul>
		<?php
		foreach ( cdfs_get_dashboard_endpoints() as $endpoint_id => $endpoint_data ) {
			if ( isset( $endpoint_data['in_navbar'] ) && false === $endpoint_data['in_navbar'] ) {
				continue;
			}
			?>
			<li class="<?php echo esc_attr( cdfs_dashboard_item_classes( $endpoint_data['endpoint'] ) ); ?>">
				<a href="<?php echo esc_url( cdfs_get_cardealer_dashboard_endpoint_url( $endpoint_id ) ); ?>"><i class="<?php echo esc_attr( $endpoint_data['icon'] ); ?>"></i> <?php echo esc_html( $endpoint_data['title'] ); ?></a>
			</li>
			<?php
		}
		?>
	</ul>
</nav>

<?php do_action( 'cardealer_after_dashboard_navigation' ); ?>
