<?php
/**
 * Dealer notification on Vehicle published by Admin
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/mails/mail-dealer-publish-notification.php
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php do_action( 'cdfs_dealer_publish_notification_header' ); ?>
	<p>
		<?php
		/* translators: %s: author name */
		printf( esc_html__( 'Autosi on lisätty myytäväksi ja löytyy osoitteesta %1$s', 'cdfs-addon' ), $car_data['vehicle_link']  ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
		?>
	</p>
	
	<?php //echo $car_data['mail_html']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?>

	<br><br>
	<p>
		<img src="https://nordtrade.boostiprojektit.fi/wp-content/uploads/2023/10/NP_logo_italic1.png" width="250" />
	</p>
<?php do_action( 'cdfs_dealer_publish_notification_footer' ); ?>
