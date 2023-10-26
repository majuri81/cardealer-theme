<?php
/**
 * User cars listing
 *
 */

// Notices.
cdfs_print_notices();

if ( is_user_logged_in() ) {
	$user    = wp_get_current_user();
	$user_id = $user->ID;
} else {
	return;
}

global $car_dealer_options;

$show_vehicle_views   = cdfs_show_vehicle_views();
$status               = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
?>
<div class="cardealer-dashboard-content-title-wrapper">
	<?php
	// Content title.
	cdfs_get_template(
		'user-dashboard/content-title.php',
		array(
			'endpoint_title' => $endpoint_title,
		)
	);
	?>
	<div class="cardealer-dashboard-content-sorting">
		<div class="cardealer-dashboard-content-sorting-label"><?php echo esc_html__( 'Status', 'cdfs-addon' ); ?></div>
		<form>
			<select name="status" onchange="this.form.submit()">
				<option value="all" <?php selected( $status, 'all' ); ?> ><?php esc_html_e( 'All', 'cdfs-addon' ); ?></option>
				<option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'cdfs-addon' ); ?></option>
				<option value="draft" <?php selected( $status, 'draft' ); ?> ><?php esc_html_e( 'Draft', 'cdfs-addon' ); ?></option>
				<option value="publish" <?php selected( $status, 'publish' ); ?>><?php esc_html_e( 'Publish', 'cdfs-addon' ); ?></option>
			</select>
		</form>
	</div>
</div>
<?php do_action( 'cardealer-dashboard/content/before-items' ); ?>
<div class="cardealer-dashboard-content-items cardealer-dashboard-content-my-items">
	<?php
	$args   = array(
		'post_type'      => 'cars',
		'posts_per_page' => '10',
		'orderby'        => 'id',
		'author'         => $user_id,
		'order'          => 'DESC',
		'paged'          => $current_page,
	);

	if ( 'all' === $status || ! $status ) {
		$args['post_status'] = array( 'publish', 'pending', 'draft' );
	} else {
		$args['post_status'] = array( $status );
	}

	/**
	 * Filters the list of arguments for dealer car listing.
	 *
	 * @param array $args The list of arguments.
	 */
	$args     = apply_filters( 'cdfs_dealer_vehicle_listing_args', $args );
	$car_list = new WP_Query( $args );

	if ( $car_list->have_posts() ) {

		$advertise_item_statuses = cdfs_advertise_item_statuses();
		$cart_items              = array();
		$cart_advertise_items    = array();

		global $woocommerce;
		if ( $woocommerce ) {
			$cart_items = $woocommerce->cart->get_cart();
		}

		foreach ( $cart_items as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['cardealer_order_type'] ) && 'advertise_item' === $cart_item['cardealer_order_type'] ) {
				$cart_advertise_items[] = $cart_item['_cd_vehicle_id'];
			}
        }

		while ( $car_list->have_posts() ) :
			$car_list->the_post();
			global $post;

			$vehicle_id       = $post->ID;
			$advertise_status = ( ! empty( $post->cdfs_advertise_item_status ) && array_key_exists( $post->cdfs_advertise_item_status, $advertise_item_statuses ) ) ? $post->cdfs_advertise_item_status : 'advertise_it';
			$is_featured      = ( isset( $post->featured ) && 1 === (int) $post->featured ) ? true : false;

			global $wp_post_statuses;
			$vehicle_status       = get_post_status( $vehicle_id );
			$vehicle_status_label = esc_attr__( 'Invalid', 'cdfs-addon' );

			if ( isset( $wp_post_statuses[ $vehicle_status ] ) && ! empty( $wp_post_statuses[ $vehicle_status ] ) ) {
				$post_status_data = $wp_post_statuses[ $vehicle_status ];
				$vehicle_status_label = $post_status_data->label;
			}

			// if ( ! $is_featured || in_array( $advertise_status, array( 'advertise_it', 'cancelled' ), true ) ) {
			if (
				in_array( $advertise_status, array( 'advertise_it', 'cancelled' ), true )
				|| ( 'added_to_cart' === $advertise_status && ! in_array( $vehicle_id, $cart_advertise_items, true ) )
			) {
				$advertise_status_data = $advertise_item_statuses['advertise_it'];
			} else {
				$advertise_status_data = $advertise_item_statuses[ $advertise_status ];
			}
			// $advertise_status = 'add';

			$listing_type_label = cdfs_get_vehicle_listing_type_label( $vehicle_id );
			?>
			<div class="cardealer-dashboard-content-grid style-default">
				<div class="row">
					<div class="col-lg-4 col-md-5 col-sm-5">
						<div class="cardealer-list-item car-item">
							<div class="cardealer-list-item-meta-tools">
								<?php
								if ( $show_vehicle_views ) {
									$vehicle_views = CDFS_Vehicle_Statistics::get_vehicle_view_count( $vehicle_id );
									?>
									<div class="cardealer-list-item-view-statistics">
										<span class="item-view-statistics" data-toggle="tooltip" data-placement="right" data-original-title="<?php echo esc_attr__( 'Item Views', 'cdfs-addon' ); ?>">
											<i class="far fa-eye"></i>&nbsp;<?php echo esc_html( $vehicle_views ); ?>
										</span>
									</div>
									<?php
								}

								$allowed_post_status    = cdfs_advertise_item_get_allowed_post_statuses();
								$advertise_item_enabled = cdfs_advertise_item_enabled();
								if ( in_array( $post->post_status, $allowed_post_status, true ) && $advertise_item_enabled ) {
									?>
									<div class="cardealer-list-item-featured">
										<?php
										$advertise_item_class = array(
											'advertise-item',
											'advertise-item-status__' . str_replace( '_', '-', $advertise_status ),
											'advertise-item-color-' . $advertise_status_data['color'],
										);

										// if ( ! $is_featured || in_array( $advertise_status, array( 'advertise_it', 'cancelled' ), true ) ) {
										if (
											in_array( $advertise_status, array( 'advertise_it', 'cancelled' ), true )
											|| ( 'added_to_cart' === $advertise_status && ! in_array( $vehicle_id, $cart_advertise_items, true ) )
										) {
											$advertise_url    = add_query_arg(
												array(
													'cdfs_advertise_item' => $vehicle_id,
												),
												cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' )
											);
											$advertise_url          = wp_nonce_url( $advertise_url, 'advertise_item_nonce', 'nonce' );
											$advertise_item_class[] = 'advertise-item-link';
											if ( class_exists( 'WooCommerce' ) ) {
												?>
												<a href="<?php echo esc_url( $advertise_url ); ?>" class="<?php echo cdfs_class_builder( $advertise_item_class ); ?>" data-toggle="tooltip" data-placement="right" title="<?php echo esc_attr( $advertise_status_data['label'] ); ?>" data-original-title="">
													<i class="fas fa-star" aria-hidden="true"></i>
												</a>
												<?php
											} else {
												$advertise_item_class[] = 'advertise-item-icon';
												?>
												<span class="<?php echo cdfs_class_builder( $advertise_item_class ); ?>" data-toggle="tooltip" data-placement="right" title="<?php echo esc_attr( $advertise_status_data['label'] ); ?>" data-original-title="">
													<i class="fas fa-star" aria-hidden="true"></i>
												</span>
												<?php
											}
										} else {
											$advertise_item_class[] = 'advertise-item-icon';
											?>
											<span class="<?php echo cdfs_class_builder( $advertise_item_class ); ?>" data-toggle="tooltip" data-placement="right" title="<?php echo esc_attr( $advertise_status_data['label'] ); ?>" data-original-title="">
												<i class="fas fa-star" aria-hidden="true"></i>
											</span>
											<?php
										}
										?>
									</div>
									<?php
								}
								?>
							</div>
							<div class="cardealer-list-item-image car-image">
								<?php
								$vehicle_catalog_image_size = ( isset( $car_dealer_options['vehicle-catalog-image-size'] ) && ! empty( $car_dealer_options['vehicle-catalog-image-size'] ) ) ? $car_dealer_options['vehicle-catalog-image-size'] : 'car_catalog_image';
								$catalog_size               = ( wp_is_mobile() ) ? 'car_tabs_image' : $vehicle_catalog_image_size;

								echo wp_kses_post( cardealer_get_cars_image( $catalog_size, $vehicle_id ) );
								?>
								<div class="cardealer-list-item-overlay">
									<a href="<?php echo esc_url( get_permalink( $vehicle_id ) ); ?>"><?php esc_html_e( 'Details', 'cdfs-addon' ); ?></a>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-8 col-md-7 col-sm-7">
						<div class="cardealer-list-item-details">
							<div class="cardealer-list-item-title">
								<a href="<?php echo esc_url( get_permalink( $vehicle_id ) ); ?>"><?php echo get_the_title( $vehicle_id ); ?></a>
								<p><?php echo wp_kses_post( cardealer_car_short_content( $vehicle_id ) ); ?></p>
							</div>
							<?php
							cardealer_car_price_html( '', $vehicle_id );

							$year         = get_the_terms( $vehicle_id, 'car_year' );
							$transmission = get_the_terms( $vehicle_id, 'car_transmission' );
							$mileage      = get_the_terms( $vehicle_id, 'car_mileage' );

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
								cdfs_get_cardealer_dashboard_car_edit_button( $vehicle_id );
								cdfs_get_cardealer_dashboard_car_actions_button( $vehicle_id );
								cdfs_get_cardealer_dashboard_car_clone_button( $vehicle_id );
								cardealer_classic_compare_cars_overlay_link( $vehicle_id );
								cardealer_wishlist_button( $vehicle_id, true );
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
								<?php echo esc_html( ucfirst( $vehicle_status_label ) ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php

			endwhile;
		wp_reset_postdata();
	} else {
		echo '<div class="alert alert-warning">' . esc_html__( 'No vehicles available.', 'cdfs-addon' ) . '</div>';
	}

	if ( 1 < $car_list->max_num_pages ) {
		?>
		<div class="cardealer-pagination cardealer-pagination--without-numbers cardealer-Pagination">
			<?php
			if ( 1 !== $current_page ) {
				?>
				<a class="cardealer-button cardealer-button--previous cardealer-Button cardealer-Button--previous button" href="<?php echo esc_url( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items', $current_page - 1 ) ); ?>">
					<?php esc_html_e( 'Previous', 'cdfs-addon' ); ?>
				</a>
				<?php
			}

			if ( intval( $car_list->max_num_pages ) !== $current_page ) {
				?>
				<a class="cardealer-button cardealer-button--next cardealer-Button cardealer-Button--next button" href="<?php echo esc_url( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items', $current_page + 1 ) ); ?>">
					<?php esc_html_e( 'Next', 'cdfs-addon' ); ?>
				</a>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>
</div>
<?php
do_action( 'action_after_cars_listing' );

do_action( 'cardealer-dashboard/content/after-items' );
