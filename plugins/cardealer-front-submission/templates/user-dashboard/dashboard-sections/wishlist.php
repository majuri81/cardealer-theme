<?php
/**
 * User wishlist.
 */
global $car_dealer_options;

$wishlist_status = ( isset( $car_dealer_options['cars-is-wishlist-on'] ) ) ? $car_dealer_options['cars-is-wishlist-on'] : 'yes';
if ( 'no' === $wishlist_status ) {
	return;
}

// Content title.
cdfs_get_template(
	'user-dashboard/content-title.php',
	array(
		'endpoint_title' => $endpoint_title,
	)
);

do_action( 'cardealer-dashboard/content/before-wishlist' );
?>
<div class="cardealer-dashboard-content-items cardealer-dashboard-content-wishlist">
	<?php
	$cdfs_wishlist   = new CDFS_Wishlist();
	$wishlist_items  = $cdfs_wishlist->get_wishlist();

	if ( $wishlist_items ) {
		foreach( $wishlist_items as $wishlist_item_id ) {
			if ( $wishlist_item_id ) {
				$listing_type_label = cdfs_get_vehicle_listing_type_label( $wishlist_item_id );
				?>
				<div class="cardealer-dashboard-content-grid style-default">
					<div class="row">
						<div class="col-lg-4 col-md-5 col-sm-5">
							<div class="cardealer-list-item car-item">
								<a href="javascript:void(0)" data-id="<?php echo esc_attr( $wishlist_item_id ); ?>" class="cdfs-remove-wishlist" title="<?php echo esc_attr__( 'Remove from Wishlist', 'cdfs-addon' ); ?>"><span>x</span></a>
								<div class="cardealer-list-item-image car-image">
									<?php
									$vehicle_catalog_image_size = ( isset( $car_dealer_options['vehicle-catalog-image-size'] ) && ! empty( $car_dealer_options['vehicle-catalog-image-size'] ) ) ? $car_dealer_options['vehicle-catalog-image-size'] : 'car_catalog_image';
									$catalog_size               = ( wp_is_mobile() ) ? 'car_tabs_image' : $vehicle_catalog_image_size;

									echo wp_kses_post( cardealer_get_cars_image( $catalog_size, $wishlist_item_id ) );
									?>
									<div class="cardealer-list-item-overlay">
										<a href="<?php echo esc_url( get_permalink( $wishlist_item_id ) ); ?>"><?php esc_html_e( 'Details', 'cdfs-addon' ); ?></a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-8 col-md-7 col-sm-7">
							<div class="cardealer-list-item-details">
								<div class="cardealer-list-item-title">
									<a href="<?php echo esc_url( get_permalink( $wishlist_item_id ) ); ?>"><?php echo get_the_title( $wishlist_item_id ); ?></a>
									<p><?php echo wp_kses_post( cardealer_car_short_content( $wishlist_item_id ) ); ?></p>
								</div>
								<?php
								cardealer_car_price_html( '', $wishlist_item_id );

								$year         = get_the_terms( $wishlist_item_id, 'car_year' );
								$transmission = get_the_terms( $wishlist_item_id, 'car_transmission' );
								$mileage      = get_the_terms( $wishlist_item_id, 'car_mileage' );

								if ( $year || $transmission || $mileage ) {

									$car_year         = '';
									$car_transmission = '';
									$car_mileage      = '';

									if ( ! is_wp_error( $year ) && isset( $year[0]->name ) ) {
										$car_year = $year[0]->name;
									}
									if ( ! is_wp_error( $year ) && isset( $transmission[0]->name ) ) {
										$car_transmission = $transmission[0]->name;
									}
									if ( ! is_wp_error( $year ) && isset( $mileage[0]->name ) ) {
										$car_mileage = $mileage[0]->name;
									}
									?>
									<div class="cardealer-list-items-attributes car-list">
										<ul class="list-inline">
										<?php
										if ( $car_year ) {
											?>
											<li class="cardealer-list-item-attribute">
												<i class="fas fa-calendar-alt"></i><?php echo esc_html( $car_year ) ?>
											</li>
											<?php
										}
										if ( $car_transmission ) {
											$car_transmission = strlen( $car_transmission ) > 5 ? substr( $car_transmission, 0, 5 ) . '...' : $car_transmission;
											?>
											<li class="cardealer-list-item-attribute" title="<?php echo esc_html( $car_transmission ); ?>">
												<i class="fas fa-cog"></i><?php echo esc_html( $car_transmission ); ?>
											</li>
											<?php
										}
										if ( $car_mileage ) {
											?>
											<li class="cardealer-list-item-attribute" >
												<i class="glyph-icon flaticon-gas-station"></i><?php echo esc_html( cardealer_get_cars_formated_mileage( $car_mileage ) ); ?>
											</li>
											<?php
										}
										?>
										</ul>
									</div>
									<?php
								}
								?>
							</div>
						</div>
						<div class="col-md-12">
							<div class="cardealer-list-items-bottom">
								<ul class="cardealer-list-items-bottom-actions">
									<?php
									cardealer_classic_compare_cars_overlay_link( $wishlist_item_id );
									?>
								</ul>
								<div class="car-type">
									<?php
									if ( ! empty( $listing_type_label ) ) {
										?>
										<span><i class="far fa-hdd"></i> <?php echo esc_html( $listing_type_label ); ?></span>
										<?php
									}
									?>
								</div>
								<div class="car-status">
									<span><?php esc_html_e( 'Status', 'cdfs-addon' ); ?> : </span>
									<?php echo esc_html( ucfirst( get_post_status( $wishlist_item_id ) ) ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		}
	} else {
		echo '<div class="alert alert-warning">' . esc_html__( 'No vehicles added in wishlist.', 'cdfs-addon' ) . '</div>';
	}
	?>
</div>
<?php
do_action( 'cardealer-dashboard/content/after-wishlist' );
