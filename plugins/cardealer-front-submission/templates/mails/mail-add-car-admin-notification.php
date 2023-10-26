<?php
/**
 * Admin notification on Add vehicle by dealer
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/mails/mail-add-car-admin-notification.php
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php do_action( 'cdfs_admin_car_add_notification_header' ); ?>
	<p>
		<?php
		/* translators: %s: author name */
		printf( esc_html__( 'Myyjä on lisännyt uuden auton!', 'cdfs-addon' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
		?>
	</p>
	<p>
		<?php
		/* translators: %s: dealer name */
		printf( esc_html__( 'Myyjän Nimi: %s', 'cdfs-addon' ), $car_data['dealer_name'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
		?>
	</p>
	<p>
		<?php
		/* translators: %s: dealer emial */
		printf( esc_html__( 'Sähköposti: %s', 'cdfs-addon' ), $car_data['dealer_email'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
		?>
	</p>
	<p>
		<u><?php // printf( esc_html__( 'Vehicle Details', 'cdfs-addon' ) ); ?></u>
	</p>
	<?php // echo $car_data['mail_html']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?>

	<br><br>
	<p>
		<img src="https://nordtrade.boostiprojektit.fi/wp-content/uploads/2023/10/NP_logo_italic1.png" width="250" />
	</p>
<?php do_action( 'cdfs_admin_car_add_notification_footer' ); ?>
