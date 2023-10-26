<?php
/**
 * Car listing actions [ edit/disable/delete/view/gallery ]
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-templates/cars-actions.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $post_status;

$status_class = '';
$post_status  = 'publish';

if ( 'draft' === get_post_status( $id ) ) {
	$status_class = 'cdfs-disabled-car';
	$post_status  = 'draft';
} elseif ( 'pending' === get_post_status( $id ) ) {
	$status_class = 'cdfs-pending-car';
	$post_status  = 'pending';
}

$cdfs_nonce = wp_create_nonce( 'cdhl-action' );
?>
<div class="car-overlay-banner">
	<ul>
		<?php
		if ( 'publish' === $post_status ) {
			?>
			<li class="cdfs-car-list <?php echo esc_attr( $status_class ); ?>">
			<a href="<?php echo esc_url( get_permalink( $id ) ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'View', 'cdfs-addon' ); ?>"><i class="fa fa-link"></i><?php esc_attr_e( 'View', 'cdfs-addon' ); ?></a></li>
			<?php
		}

		// compare.
		if ( 'publish' === $post_status ) {
			if ( isset( $_COOKIE['cars'] ) && ! empty( $_COOKIE['cars'] ) ) {
				$car_in_compare = json_decode( $_COOKIE['cars'] );
			}
			if ( isset( $car_in_compare ) && ! empty( $car_in_compare ) && in_array( $id, $car_in_compare ) ) {
				$cars = json_decode( $_COOKIE['cars'] );
				if ( $cars ) {
					?>
					<li><a href="javascript:void(0)" data-toggle="tooltip" title="<?php echo esc_attr__( 'Compare', 'cdfs-addon' ); ?>" class="compare_pgs compared_pgs" data-id="<?php echo esc_attr( $id ); ?>"><i class="fa fa-check"></i><?php echo esc_attr__( 'Compare', 'cdfs-addon' ); ?></a></li>
					<?php
				}
			} else {
				?>
				<li><a href="javascript:void(0)" data-toggle="tooltip" title="<?php esc_attr_e( 'Compare', 'cdfs-addon' ); ?>" class="compare_pgs" data-id="<?php echo esc_attr( $id ); ?>"><i class="fa fa-exchange"></i><?php esc_attr_e( 'Compare', 'cdfs-addon' ); ?></a></li>
				<?php
			}
		}

		$images = cardealer_get_images_url( 'car_catalog_image', $id );
		if ( ! empty( $images ) ) {
			?>
			<li class="pssrcset"><a href="javascript:void(0)" data-toggle="tooltip" title="<?php esc_attr_e( 'Gallery', 'cdfs-addon' ); ?>" class="psimages" data-image="<?php echo implode( ', ', $images ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?>"><i class="fa fa-expand" ></i><?php esc_attr_e( 'Gallery', 'cdfs-addon' ); ?></a></li>
			<?php
		}
		?>
		<li><a href="<?php echo esc_url( $my_acc_page . 'add-car/?edit-car=1&car-id=' . $id . '&cdfs_nonce=' . $cdfs_nonce ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Edit', 'cdfs-addon' ); ?>" class="edit-car" ><i class="fa fa-pencil" ></i><?php esc_attr_e( 'Edit', 'cdfs-addon' ); ?></a></li>

		<?php
		if ( isset( $post_status ) ) {
			if ( 'draft' === $post_status || 'pending' === $post_status ) {
				?>
				<li><a href="<?php echo esc_url( $my_acc_page . '?cdfs_car_action=trash&id=' . $id . '&cdfs_nonce=' . $cdfs_nonce ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete', 'cdfs-addon' ); ?>" class="delete-car" data-alttxt="<?php esc_html_e( 'Alert', 'cdfs-addon' ); ?>" ><i class="fa fa-trash-o" ></i><?php esc_attr_e( 'Delete', 'cdfs-addon' ); ?></a></li>
				<?php
			}
			if ( 'draft' === $post_status ) {
				?>
				<li><a href="<?php echo esc_url( $my_acc_page . '?cdfs_car_action=enable&id=' . $id . '&cdfs_nonce=' . $cdfs_nonce ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Enable', 'cdfs-addon' ); ?>" class="edit-car" ><i class="fa fa-check-circle-o" ></i><?php esc_attr_e( 'Enable', 'cdfs-addon' ); ?></a></li>
				<?php
			}
			if ( 'publish' === $post_status ) {
				?>
				<li><a href="<?php echo esc_url( $my_acc_page . '?cdfs_car_action=disable&id=' . $id . '&cdfs_nonce=' . $cdfs_nonce ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Disable', 'cdfs-addon' ); ?>" class="edit-car" ><i class="fa fa-ban" ></i><?php esc_attr_e( 'Disable', 'cdfs-addon' ); ?></a></li>
				<?php
			}
		}

		if ( cdfs_is_vehicle_clone_enabled() && 'publish' === $post_status ) {
			$clone_url = add_query_arg( array(
				'cdfs_car_action' => 'clone',
				'id'              => $id,
				'cdfs_nonce'      => $cdfs_nonce,
			), $my_acc_page );
			?>
			<li><a href="<?php echo esc_url( $clone_url ); ?>" data-toggle="tooltip" title="<?php esc_attr_e( 'Clone', 'cdfs-addon' ); ?>" class="edit-car" ><i class="fa fa-clone" ></i><?php esc_attr_e( 'Clone', 'cdfs-addon' ); ?></a></li>
			<?php
		}
		?>
	</ul>
</div>
