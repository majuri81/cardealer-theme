<?php
/**
 * User cars listing
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-listing.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Notices.
cdfs_print_notices();

/**
 * User Account navigation.
 */
do_action( 'action_before_cars_listing' ); ?>
<div class="cdfs-cars-listing product-listing content col-lg-12 col-md-12 col-sm-12">
	<?php
		global $post_status;

	if ( is_user_logged_in() ) {
		$user    = wp_get_current_user();
		$user_id = $user->ID;
	} else {
		return;
	}

		$paged       = ( get_query_var( 'paged' ) !== 0 ) ? get_query_var( 'paged' ) : 1;// phpcs:ignore WordPress.WP.GlobalVariablesOverride
		$args        = array(
			'post_type'      => 'cars',
			'posts_per_page' => '10',
			'orderby'        => 'id',
			'post_status'    => array( 'publish', 'pending', 'draft' ),
			'author'         => $user_id,
			'order'          => 'DESC',
			'paged'          => $paged,
		);

		/**
		 * Filters the list of arguments for dealer car listing.
		 *
		 * @param array $args The list of arguments.
		 */
		$args = apply_filters( 'cdfs_dealer_vehicle_listing_args', $args );

		$car_list    = new WP_Query( $args );
		$my_acc_page = get_permalink();
		$list_style  = cardealer_get_inv_list_style();
		if ( $car_list->have_posts() ) {
			while ( $car_list->have_posts() ) :
				$car_list->the_post();
				?>
				<div class="all-cars-list-arch">
					<div class="car-grid style-<?php echo esc_attr( $list_style ); ?>">
						<div class="row">
							<div class="col-lg-3 col-md-3 col-sm-4">
								<div class="car-item gray-bg text-center">
									<div class="car-image">
										<?php
											$id = get_the_ID(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
											echo cardealer_get_cars_condition( $id ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
											echo cardealer_get_cars_status( $id ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
											echo cardealer_get_cars_image( 'car_catalog_image', $id ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
										if ( 'default' === $list_style ) {
											cdfs_get_template(
												'cars/cars-templates/car-actions.php',
												array(
													'id' => $id,
													'my_acc_page' => $my_acc_page,
												)
											);
										} else {
											$images = cardealer_get_images_url( 'car_catalog_image', $id );
											?>
												<div class="car-overlay-banner">
													<?php
													if ( ! empty( $images ) ) {
														?>
														<ul>
															<li class="pssrcset"><a href="javascript:void(0)" data-toggle="tooltip" title="<?php esc_attr_e( 'Gallery', 'cdfs-addon' ); ?>" class="psimages" data-image="<?php echo implode( ', ', $images ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?>"><i class="fa fa-expand" ></i><?php esc_attr_e( 'Gallery', 'cdfs-addon' ); ?></a></li>
														</ul>
														<?php
													}
													?>
												</div>
												<?php
										}
										?>
									</div>
								</div>
							</div>
							<?php
							if ( 'classic' === $list_style ) {
								?>
							<div class="col-lg-9 col-md-9 col-sm-8">
								<div class="car-details">
									<div class="car-title">
										<a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
									</div>
									<div class="car-info">
										<div class="car-info-content">
											<?php cardealer_get_cars_list_attribute(); ?>
											<div class="car-status">
												<span><?php esc_html_e( 'Status', 'cdfs-addon' ); ?> : </span>
												<?php echo esc_html( ucfirst( get_post_status( $id ) ) ); ?>
											</div>
										</div>
										<div class="car-info-price car-location">
											<?php
											cardealer_car_price_html( $id );
											$vehicle_loc = get_post_meta( $id, 'vehicle_location', true );
											if ( ! empty( $vehicle_loc ) && isset( $vehicle_loc['address'] ) ) {
												?>
											<p>
												<strong><?php echo esc_html__( 'Location:', 'cdfs-addon' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?></strong>
												<?php echo esc_html( $vehicle_loc['address'] ); ?>
											</p>
												<?php
											}
											?>
										</div>
								</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="car-description">
									<p><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
								</div>
							</div>
							<div class="col-md-12">
								<div class="car-bottom">
									<ul class="car-bottom-actions classic-list">
										<?php
										cdfs_get_template(
											'cars/cars-templates/car-actions.php',
											array(
												'id' => $id,
												'my_acc_page' => $my_acc_page,
											)
										);
										?>

									</ul>
									<div class="car-review-stamps">
										<?php cardealer_get_vehicle_review_stamps( $id ); ?>
									</div>
								</div>
							</div>
								<?php
							} else {
								?>
							<div class="col-lg-9 col-md-9 col-sm-8">
								<div class="car-details">
									<div class="car-title">
										<a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
										<p><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
									</div>
									<?php
									cardealer_car_price_html( $id );
									if ( get_post_status( $id ) === 'publish' ) {
										?>
										<a class="button red pull-right" href="<?php echo esc_url( get_the_permalink() ); ?>"><?php esc_html_e( 'Details', 'cdfs-addon' ); ?></a>
										<?php
									}
									cardealer_get_cars_list_attribute();
									?>
									<div class="car-status">
										<span><?php esc_html_e( 'Status', 'cdfs-addon' ); ?> : </span>
										<?php echo esc_html( ucfirst( get_post_status( $id ) ) ); ?>
									</div>
								</div>
							</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
				<?php
			endwhile;
			wp_reset_postdata();
		} else {
			echo '<div class="all-cars-list-arch"><div class="col-sm-12"><div class="alert alert-warning">' . esc_html__( 'No vehicles added.', 'cdfs-addon' ) . '</div></<div></<div>';
		}

		?>
	<div id="cars-pagination-nav-container" class="pagination-nav text-center">
		<?php echo cardealer_cars_pagination( true, $car_list ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?>
	</div>
</div>

<?php
do_action( 'action_after_cars_listing' );
