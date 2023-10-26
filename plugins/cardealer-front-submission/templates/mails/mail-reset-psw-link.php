<?php
/**
 * Reset password mail
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/mails/mail-reset-psw-link.php
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php do_action( 'cdfs_psw_reset_email_header' ); ?>

<p>
	<?php esc_html_e( 'This is the mail regarding password request. Please check the following details:', 'cdfs-addon' ); ?>
</p>
<p>
	<?php
	/* translators: %s: user name */
	printf( __( 'Username: %s', 'cdfs-addon' ), $user_login ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
	?>
</p>
<p>
	<?php esc_html_e( 'To reset your password, visit the following address:', 'cdfs-addon' ); ?>
</p>
<p>
	<a class="link" href="
	<?php
	echo esc_url(
		add_query_arg(
			array(
				'key'        => $reset_key,
				'cdfs_login' => rawurlencode( $user_login ),
			),
			cdfs_get_lost_password_url()
		)
	);
	?>
	">
			<?php esc_html_e( 'Click here to reset your password', 'cdfs-addon' ); ?></a>
</p>

<?php do_action( 'cdfs_psw_reset_email_footer' ); ?>
