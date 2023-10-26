<?php
/**
 * Car packages
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-templates/cars-packages.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$car_action           = $args['car_action'];
$car_edit             = $args['car_edit'];
$php_max_file_uploads = ini_get( 'max_file_uploads' );
?>
<div id="cdfs-subscription" class="cdfs-av-section ">
	<div id="cars-pdf-brochure-content" class="cdfs-av-section-content">
		<div class="cdfs-av-section-content-inner">
			<div class="form-group">
				<?php
				$selected_plan_id = 'free';
				if ( $car_edit ) {
					$car_id            = $args['car_id'];
					$selected_plan_id  = get_post_meta( $car_id, 'cdfs_subscription_id', true );
					$cdfs_listing_type = get_post_meta( $car_id, 'cdfs_listing_type', true );
					if ( 'listing_payment' === $cdfs_listing_type ) {
						$selected_plan_id = 'listing_payment';
					}elseif ( '' === $cdfs_listing_type && '' === $selected_plan_id ) {
						$selected_plan_id = 'free';
					}
				}
				$selected_plan_id = ( 'listing_payment' === $selected_plan_id || 'free' === $selected_plan_id ) ? $selected_plan_id : (int) $selected_plan_id;

				// $pricing_plan_enabled = cdfs_pricing_plan_enabled();
				$user_id              = get_current_user_id();
				$packages             = cdfs_get_add_car_packages( $user_id );
				$package_columns      = cdfs_get_add_car_package_columns();
				$new_image_limits     = array_column( $packages, 'image_limit', 'plan_id' );

				if ( ! empty( $packages ) ) {
					?>
					<table class="cdfs-add-car-packages-table table table-striped table-hover">
						<tr class="package-row package-row-head">
							<?php
							foreach ( $package_columns as $column_id => $column_name ) {
								$column_class = array(
									'package-column',
									'package-column-head',
									'package-column-' . $column_id,
								);
								?>
								<th class="<?php cdfs_class_builder( $column_class, true ); ?>"><?php echo esc_html( $column_name ); ?></th>
								<?php
							}
							?>
						</tr>
						<?php
						$row_sr = 1;
						foreach( $packages as $plan_key => $plan_data ) {
							$row_class = array(
								'package-row',
								'package-row-data',
								'package-row-' . $row_sr,
								'package-row-' . ( ( $row_sr % 2 == 0 ) ? 'even' : 'odd' ),
							);
							?>
							<tr class="<?php cdfs_class_builder( $row_class, true ); ?>">
								<?php
								foreach ( $package_columns as $column_id => $column_name ) {
									$column_class = array(
										'package-column',
										'package-column-data',
										'package-column-' . $column_id,
									);
									if ( 'plan_cb' === $column_id ) {
										$car_limit_available = ( ( 'na' === $plan_data['car_limit_available'] ) ? 1 : $plan_data['car_limit_available'] );
										$plan_type           = ( 'listing_payment' === $plan_key || 'free' === $plan_key ) ? $plan_key : 'subscription';
										$plan_key            = ( 'listing_payment' === $plan_key || 'free' === $plan_key ) ? $plan_key : (int) $plan_key;
										$checked             = false;
										$disabled            = false;

										if ( 'edit' === $car_action ) {
											if ( 'free' === $plan_type ) {
												$checked  = ( $plan_key === $selected_plan_id && $car_limit_available >= 0 ) ? true : false;
												if ( $plan_key === $selected_plan_id ) {
													$disabled = $car_limit_available >= 0 ? false : true;
												} else {
													$disabled = $car_limit_available > 0 ? false : true;
												}
											}elseif ( 'subscription' === $plan_type ) {
												$subscription_data = subscriptio_get_subscription( $plan_key );
												$status            = ( $subscription_data ) ? $subscription_data->get_status() : '';

												$checked  = ( $plan_key === $selected_plan_id && 'active' === $status ) ? true : false;
												if ( $plan_key === $selected_plan_id && 'active' === $status ) {
													$disabled = ( $car_limit_available >= 0 ) ? false : true;
												} else {
													$disabled = ( $car_limit_available > 0 ) ? false : true;
												}
											}elseif ( 'listing_payment' === $plan_type ) {
												$checked  = ( $plan_key === $selected_plan_id ) ? true : false;
											}
										} else {
											if ( 'free' === $plan_type && $plan_key ) {
												$disabled = ( $car_limit_available > 0 ) ? false : true;
												$checked  = true;
											}elseif ( 'subscription' === $plan_type ) {
												$disabled = ( $car_limit_available > 0 ) ? false : true;
											}elseif ( 'listing_payment' === $plan_type ) {
											}
										}

										$max_file_uploads = $plan_data['image_limit'];
										if ( $max_file_uploads > $php_max_file_uploads ) {
											$max_file_uploads = $php_max_file_uploads;
										}										
										?>
										<td class="<?php cdfs_class_builder( $column_class, true ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
											<input
												type="radio"
												class="cdfs-add-car-package"
												name="subscription_plan"
												value="<?php echo esc_attr( $plan_key ); ?>"
												data-image_limit="<?php echo esc_attr( $max_file_uploads ); ?>"
												data-submit_type="<?php echo esc_attr( $plan_data['submit_type'] ); ?>"
												<?php checked( $checked ); ?>
												<?php disabled( $disabled ); ?>
											>
										</td>
										<?php
									} else {
										if ( isset( $plan_data[ $column_id ] ) ) {
											?>
											<td class="<?php cdfs_class_builder( $column_class, true ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
												<?php
												if ( 'listing_payment' === $plan_key && 'plan_name' === $column_id ) {
													$listing_price_formatted = cdfs_get_item_listing_price_formatted();
													$listing_duration        = cdfs_get_item_listing_duration();
													printf(
														esc_html__( 'Pay this item for %s for %s days.', 'cdfs-addon' ),
														$listing_price_formatted,
														$listing_duration
													);
													if ( $car_edit && 'listing_payment' === $plan_key && 'listing_payment' === $selected_plan_id ) {
														?>
														<span class="edit-listing-payment-warning"><?php echo esc_html__( 'Editing this vehicle will be charged as new entry.', 'cdfs-addon' ); ?></span>
														<?php
													}
												} else {
													if ( 'car_limit_available' === $column_id ) {
														if ( 'na' === $plan_data[ $column_id ] ) {
															echo esc_html__( 'N/A', 'cdfs-addon' );
														} else {
															if ( $plan_data[ $column_id ] < 0 ) {
																echo 0;
															} else {
																echo esc_html( $plan_data[ $column_id ] );
															}
														}
													} else {
														echo esc_html( $plan_data[ $column_id ] );
													}
												}

												?>
											</td>
											<?php
										}
									}
									?>
									<?php
								}
								?>
							</tr>
							<?php
							$row_sr++;
						}
						?>
					</table>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
