<?php
/**
 * Car form Review Stamps upload
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-templates/cars-review-stamps.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'action_before_cars_review_stamps' );

?>
<div class="review-stamps">
	<?php
	global $car_dealer_options;

	$review_stamp_limit = isset( $car_dealer_options['review_stamp_limit'] ) ? $car_dealer_options['review_stamp_limit'] : 1;
	for( $i = 1; $i <= $review_stamp_limit; $i++ ) {
		$link  = '';
		$field = 'review_stamp_logo_' . $i;
		?>
		<div class="review-stamp review-stamp-<?php echo esc_attr( $i ); ?>">
			<div class="row">
				<div class="col-sm-6">
					<div class="form-group stamp-group">
						<label><?php echo sprintf( esc_html__( 'Review Stamp Logo %d', 'cdfs-addon' ), $i ); ?></label>
						<?php
						if ( isset( $id ) ) {
							$link    = get_post_meta( $id, 'review_stamp_link_' . $i, $single = true );
							$file_id = get_post_meta( $id, $field, $single = true );
							if ( ! empty( $file_id ) ) {
								$file_url = wp_get_attachment_image_url( $file_id, 'medium' );
								?>
								<li class="cdfs-item">
									<img class="img-thumb img-review-stamp-logo" src="<?php echo esc_url( $file_url ); ?>"/>
									<a href="javascript:void(0)" data-field="<?php echo esc_attr( $field ); ?>" data-parent_id="<?php echo esc_attr( $id ); ?>" data-attach_id="<?php echo esc_attr( $file_id ); ?>" class="drop_img_item"><span class="remove">x</span></a>
								</li>
								<?php
							}
						}
						?>
						<input type="file" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" class="review_stamp_logo" />
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group stamp-group">
						<label><?php echo sprintf( esc_html__( 'Review Stamp Link %d', 'cdfs-addon' ), $i ); ?></label>
						<input type="url" name="car_data[<?php echo esc_attr( 'review_stamp_link_' . $i); ?>]" id="<?php echo esc_attr( 'review_stamp_link_' . $i); ?>" class="form-control cdfs-review-stamp-link" value="<?php echo esc_url($link);?>" placeholder=""/>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div>

<?php
do_action( 'action_after_cars_review_stamps' );
