<?php
/**
 * Car form image gallery upload
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-templates/cars-image-gallery.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'action_before_cars_images' );
$image_size_limit_mb    = cdfs_get_add_car_image_upload_size_limit_in_mb();
$image_size_limit_bytes = cdfs_get_add_car_image_upload_size_limit_in_bytes();
?>
<div class="row">
	<div class="col-sm-12">
		<div class="form-group">
			<label><?php echo esc_html__( 'Upload Images', 'cdfs-addon' ); ?> <span class="upload-image-limit">
			<?php
			/* translators: %1$s: Image Upload Limit */
			echo wp_kses( sprintf( __( '(Upload Limit: %1$s)', 'cdfs-addon' ), '<span class="upload-image-limit-count"></span>' ), array( 'span' => array( 'class' => true, ) ) );
			?>
			</span></label>
			<!-- <input type="file" id="car-imgs" name="car_images[]" class="form-control user_picked_files" multiple /> -->
			<div class="cdfs-image-upload">
				<div class="select-file-info">
					<div class="select-file-icon"></div>
					<div class="select-file-note"><?php echo wp_kses( __( 'Drag and drop images here, <span>OR</span>', 'cdfs-addon' ), array( 'span' => array( 'class' => true, ) ) ); ?></div>
				</div>
				<a href="#" class="button select-image"><?php echo esc_html__( 'Choose Files', 'cdfs-addon' ); ?></a>
				<input type="file" id="car-imgs" name="car_images[]" class="form-control user_picked_files" multiple data-image_size_limit="<?php echo esc_attr( $image_size_limit_bytes ); ?>" />
			</div>
		</div>
		<div class="cdfs-image-upload-size-limit">
		<?php
		/* translators: %1$s: Image size limit mb, %2$s: Image size limit bytes */
		printf( esc_html__( 'Image Size Limit: %1$s Mb (%2$s Bytes)', 'cdfs-addon' ), $image_size_limit_mb, $image_size_limit_bytes );
		?>
		</div>
		<div class = "form-group cdfs_order">
			<input id="file_attachments" name="file_attachments" type="hidden" class="form-control file_attachments"/>
		</div>
		<ul class="cdfs_uploaded_files">
			<?php
			if ( function_exists( 'get_field' ) && isset( $id ) ) {
				$field  = 'car_images';
				$images = get_field( $field, $id );
				if ( $images ) {
					foreach ( $images as $image ) {
						?>
						<li file="<?php echo esc_attr( $image['id'] ); ?>" class="cdfs-item">
							<img class="img-thumb" src="<?php echo esc_url( $image['sizes']['car_thumbnail'] ); ?>"/>
							<a href="javascript:void(0)" data-field="<?php echo esc_attr( $field ); ?>" data-parent_id="<?php echo esc_attr( $id ); ?>" data-attach_id="<?php echo esc_attr( $image['id'] ); ?>" class="drop_img_item" title="Delete Image"><span class="remove">x</span></a>
						</li>
						<?php
					}
				}
			}
			?>
		</ul>
	</div>
</div>
<?php
do_action( 'action_after_cars_images' );
