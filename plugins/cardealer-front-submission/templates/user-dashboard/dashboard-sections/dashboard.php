<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Content title.
cdfs_get_template(
	'user-dashboard/content-title.php',
	array(
		'endpoint_title' => $endpoint_title,
	)
);

do_action( 'cardealer-dashboard/content/before-dashboard' );
?>
<div class="dashboard-content-inner">
	<?php
	foreach ( cdfs_get_dashboard_endpoints() as $endpoint_id => $endpoint_data ) {
		if ( isset( $endpoint_data['in_dashgrid'] ) && false === $endpoint_data['in_dashgrid'] ) {
			continue;
		}
		?>
		<div class="dashboard-content-item">
			<a href="<?php echo esc_url( cdfs_get_cardealer_dashboard_endpoint_url( $endpoint_id ) ); ?>"><span class="dashboard-item-icon"><i class="<?php echo esc_attr( $endpoint_data['icon'] ); ?>"></i></span><span class="dashboard-item-title"><?php echo esc_html( $endpoint_data['title'] ); ?></span></a>
		</div>
		<?php
	}
	?>
</div>
<?php
do_action( 'cardealer-dashboard/content/after-dashboard' );
