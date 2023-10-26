<?php
/**
 * User add new car
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-add.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'cdfs_add_car_page_start' );

$car_edit = false;

if ( ! empty( $_GET['edit-car'] ) && wp_unslash( $_GET['edit-car'] ) ) {
	$car_edit = true;
}

$restricted                   = false;
$packages                     = array();
$form_sections                = array();
$additional_attributes        = array();
$car_action                   = ( $car_edit ) ? 'edit' : 'add';
$vehicle_category             = isset( $args['vehicle_category'] ) ? $args['vehicle_category'] : '';
$additional_attributes_string = isset( $args['additional_attributes'] ) ? $args['additional_attributes'] : '';

if ( isset( $args['form_sections'] ) ) {
	if ( $args['form_sections'] ) {
		$form_sections = explode( ',', $args['form_sections'] );
	} else {
		$form_sections = array( 'cars-required-section' );
	}
}

if ( $additional_attributes_string ) {
	$additional_attributes = explode( ',', $additional_attributes_string );
}

if ( is_user_logged_in() ) {
	$user    = wp_get_current_user();
	$user_id = $user->ID;
	$packages = cdfs_get_add_car_packages( $user_id );
}

$vars = array(
	'car_action' => $car_action,
	'car_edit'   => $car_edit,
);

$car_id = '';

if ( $car_edit ) {
	if ( ! is_user_logged_in() ) {
		echo '<h4>' . esc_html__( 'Please login.', 'cdfs-addon' ) . '</h4>';
		return false;
	}

	$is_webview=( isset( $_GET['is_webview'] ) ? $_GET['is_webview'] : '' );
	if( $is_webview != 'yes' ) //Skip nonce if requested from web view
	{
		if ( ! isset( $_GET['cdfs_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['cdfs_nonce'] ), 'cdhl-action' ) ) {
			echo '<h4>' . esc_html__( 'Invalid action, try again later.', 'cdfs-addon' ) . '</h4>';
			return false;
		}
	}

	if ( ! empty( $_GET['car-id'] ) ) {

		$car_id   = intval( sanitize_text_field( wp_unslash( $_GET['car-id'] ) ) );
		$car_user = get_post_field( 'post_author', $car_id );

		if ( intval( $user_id ) !== intval( $car_user ) ) {
			echo '<h4>' . esc_html__( 'You are not the owner of this vehicle.', 'cdfs-addon' ) . '</h4>';
			return false;
		}
	} else {
		echo '<h4>' . esc_html__( 'No vehicle to edit.', 'cdfs-addon' ) . '</h4>';
		return false;
	}

}
$vars['id']     = $car_id;
$vars['car_id'] = $car_id;
?>
<div class="cdfs-add-car-page cdfs">
	<div class="cdfs-add-car-form-wrapper cdfs-add-car-form-action-<?php echo esc_attr( $car_action ); ?>">

		<?php do_action( 'cdfs_before_add_car_form' ); ?>

		<form method="POST" action="" enctype="multipart/form-data" id="cdfs_car_form">

			<?php do_action( 'cdfs/add_car_form/inside-form/start' ); ?>

			<?php
			if ( $car_edit ) {
				?>
				<input name="cdfs_action_car_id" value="<?php echo esc_attr( $car_id ); ?>" type="hidden">
				<?php
			} else {
				$action = 'cdfs_add_car'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
			?>
			<input name="cdfs_car_form_action" class="form-control" value="cdfs_add_car" type="hidden">
			<?php
			wp_nonce_field( 'cdfs-car-form', 'cdfs-car-form-nonce-field' );

			$important_fields         = array( 'car_title', 'year', 'make', 'model', 'stock_number', 'vin_number', 'regular_price', 'sale_price', 'enable_request_price', 'request_price_label' );
			$vars['important_fields'] = $important_fields;

			if ( isset( $args['additional_attributes'] ) ) {
				$vars['additional_attributes'] = $additional_attributes;
			}

			$add_vehicle_sections = cdfs_get_add_car_form_sections( $form_sections );
			?>
			<div id="cdfs-av-sections" class="cdfs-av-sections">
				<!-- Important Fields -->
				<div id="important-fields" class="cdfs-av-section cdfs-av-section-important-fields">
					<div id="car-important-fields-heading" class="cdfs-av-section-heading"><h4 class="cdfs-av-title"><?php esc_html_e( 'Add Vehicle Details', 'cdfs-addon' )?></h4></div>
					<div id="car-important-fields-content" class="cdfs-av-section-content">
						<div class="cdfs-av-section-content-inner">
							<?php cdfs_get_template( 'cars/cars-templates/car-attributes-important.php', $vars ); ?>
						</div>
					</div>
				</div>
				<?php
				if ( $packages && ! in_array( 'cars-packages', $add_vehicle_sections ) ) {
					if ( isset( $packages['free'] ) ) {
						$max_file_uploads     = $packages['free']['image_limit'];
						$php_max_file_uploads = ini_get( 'max_file_uploads' );

						if ( $max_file_uploads > $php_max_file_uploads ) {
							$max_file_uploads = $php_max_file_uploads;
						}
						?>
						<input type="hidden" class="cdfs-add-car-package-hidden" name="subscription_plan" value="<?php echo esc_attr( $packages['free']['plan_id'] ); ?>" data-image_limit="<?php echo esc_attr( $max_file_uploads ); ?>" data-submit_type="<?php echo esc_attr( $packages['free']['submit_type'] ); ?>">
						<?php
					}
				}

				foreach ( $add_vehicle_sections as $add_vehicle_section_k => $add_vehicle_section ) {

					$section_classes = array(
						// 'panel',
						// 'panel-default',
						'cdfs-av-section',
						"cdfs-av-section-$add_vehicle_section_k",
					);
					$section_classes = cdfs_class_builder( $section_classes );
					?>
					<div id="<?php echo esc_attr( $add_vehicle_section_k ); ?>" class="<?php echo esc_attr( $section_classes ); ?>">
						<div id="<?php echo esc_attr( $add_vehicle_section_k ); ?>-heading" class="cdfs-av-section-heading">
							<h4 class="cdfs-av-title"><?php echo esc_html( $add_vehicle_section['label'] ); ?></h4>
						</div>
						<div id="<?php echo esc_attr( $add_vehicle_section_k ); ?>-content" class="cdfs-av-section-content">
							<div class="cdfs-av-section-content-inner"><?php cdfs_get_template( $add_vehicle_section['template'], $vars ); ?></div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
			<?php
			cdfs_get_template( 'my-user-account/user-details-car-page.php' ); // User details on ajax login.
			if ( cdfs_check_captcha_exists() && is_user_logged_in() ) {
				?>
				<p class="cdfs-form-row">
					<div class="form-group">
						<div id="car_form_captcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr( cdfs_get_goole_api_keys( 'site_key' ) ); ?>" style="<?php echo ( ( ! isset( $user_id ) ) ? 'display:none' : '' ); ?>"></div>
					</div>
				</p>
				<?php
			}
			
			if ( $vehicle_category ) {
				?>
				<input type="hidden" class="cdfs-add-car-vehicle_cat-hidden" name="vehicle_cat" value="<?php echo esc_attr( $vehicle_category ); ?>">
				<?php
			}
			?>

			<?php do_action( 'cdfs/add_car_form/inside-form/end' ); ?>

		</form>

		<?php
		if ( ! is_user_logged_in() ) {
			cdfs_get_template( 'user-dashboard/login.php' );
		}
		?>

		<?php do_action( 'cdfs_after_add_car_form' ); ?>

	</div>

	<?php
	if ( ! isset( $user_id ) ) {
		$class    = 'disabled';
		$disabled = 'disabled=disabled';
	} else {
		$class    = '';
		$disabled = '';
	}

	$label                   = ( $car_edit ) ? esc_html__( 'Update Details', 'cdfs-addon' ) : esc_html__( 'Submit Details', 'cdfs-addon' );
	$listing_payment_enabled = cdfs_listing_payment_enabled();
	?>
	<div class="form-group cdfs-submit-car-button">
		<button id="cdfs-submit-car" class="button btn cdfs-submit-car <?php echo esc_html( $class ); ?>" <?php echo esc_html( $disabled ); ?>><?php echo esc_html( $label ); ?></button>
		<?php
		/*
		$cdfs_listing_type = '';
		if ( isset( $car_id ) && $car_id ) {
			$cdfs_listing_type = get_post_meta( $car_id, 'cdfs_listing_type', true );
		}
		// if ( ( ! $cdfs_listing_type && $car_edit ) || ( ! $car_edit && 'yes' === $listing_payment_enabled ) ) {
		if ( $listing_payment_enabled ) {
			?>
			<button id="cdfs-pay-per-car" class="button btn cdfs-submit-car"><?php esc_html_e( 'Pay For Item', 'cdfs-addon' ); ?></button>
			<?php
		}
		*/
		?>
	</div>

</div>
<?php
do_action( 'cdfs_add_car_page_end' );
