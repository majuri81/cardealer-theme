<?php
/**
 * Car form PDF Brochure upload
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-templates/cars-pdf-brochure.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'action_before_cars_images' );

?>
<div class="row">
	<div class="col-sm-12">
		<div class="form-group">
			<label><?php echo esc_html__( 'Upload Brochure', 'cdfs-addon' ); ?></label>
			<?php
			if ( isset( $id ) ) {
				$field   = 'pdf_file';
				$file_id = get_post_meta( $id, $field, $single = true );
				if ( isset( $file_id ) ) {
					if ( wp_attachment_is( 'pdf', $file_id ) ) {
						$pdf_file_url = wp_get_attachment_url( $file_id );
						?>
						<li class="cdfs-item">
						<a href="javascript:void(0)" data-field="<?php echo esc_attr( $field ); ?>" data-parent_id="<?php echo esc_attr( $id ); ?>" data-attach_id="<?php echo esc_attr( $file_id ); ?>" class="drop_img_item"><span class="remove">x</span></a>
						<a href="<?php echo esc_url( $pdf_file_url ); ?>" download><i class="fa fa-file-pdf-o"></i><?php echo esc_html__( 'PDF Brochure', 'cdfs-addon' ); ?></a></li>
						<?php
					}
				}
			}
			?>
			<input type="file" id="car-pdf" name="pdf_file" accept="application/pdf" />
		</div>
	</div>
</div>

<?php
do_action( 'action_after_cars_images' );
