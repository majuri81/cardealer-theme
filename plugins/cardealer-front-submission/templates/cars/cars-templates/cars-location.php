<?php
/**
 * Car form image gallery upload
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-templates/cars-location.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'action_before_cars_location' );

$address = '';
$lat     = '40.712775';
$lng     = '-74.005973';
if ( isset( $id ) ) {
	$vehicle_location = get_post_meta( $id, 'vehicle_location', true );
	if ( ! empty( $vehicle_location ) ) {
		$address = $vehicle_location['address'];
		$lng     = $vehicle_location['lng'];
		$lat     = $vehicle_location['lat'];
	}
} else {
	// get the default.
	global $car_dealer_options;
	$location_exits = true;
	$lat            = '';
	$lan            = '';

	if ( isset( $car_dealer_options['default_value_lat'] ) && isset( $car_dealer_options['default_value_long'] ) && ! empty( $car_dealer_options['default_value_lat'] ) && ! empty( $car_dealer_options['default_value_long'] ) ) {
		$lat = $car_dealer_options['default_value_lat'];
		$lng = $car_dealer_options['default_value_long'];
	}
}
?>
<div class="row">
	<div class="col-sm-12">
		<?php cdfs_get_template( 'cars/cars-templates/cars-location-admin-notice.php' ); ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="form-group">
			<input
				id="cdfs-vehicle-location"
				type="text"
				class="form-control cdfs-vehicle-location"
				data-name="vehicle_location"
				name="car_data[vehicle_location]"
				value="<?php echo esc_attr( $address ); ?>"
				placeholder="<?php esc_html_e( 'Enter vehicle location', 'cdfs-addon' ); ?>"
			/>
			<input type="hidden" class="form-control" name="cdfs_lat" style="width: 110px" id="cdfs-lat" value="<?php echo esc_attr( $lat ); ?>" />
			<input type="hidden" class="form-control" name="cdfs_lng" style="width: 110px" id="cdfs-lng" value="<?php echo esc_attr( $lng ); ?>" />
			<div id="cdfs-vehicle-location-area"></div>
		</div>
	</div>
</div>

<?php
do_action( 'action_after_cars_images' );
