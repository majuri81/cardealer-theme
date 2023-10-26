<?php
/**
 * User Registration mail body
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/mails/mail-register-user.php
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
do_action( 'cdfs_register_user_header' );
switch ( $action ) {
	case 'activation_link_mail': // send activation link.
		$activation_link = add_query_arg(
			array(
				'usr-activate' => $activation_token
			),
			cdfs_get_page_permalink( 'dealer_login' )
		);
		?>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Hello %s, ', 'cdfs-addon' ), $user_data['user_login'] );
			?>
		</p>
		<p><?php printf( esc_html__( 'You are successfully registered!', 'cdfs-addon' ), $user_data['user_login'] ); ?></p>
		<strong><?php esc_html_e( 'Your account details:', 'cdfs-addon' ); ?></strong>
		<p>
			<?php
			/* translators: %s: username */
			printf( esc_html__( 'Username: %s', 'cdfs-addon' ) . '<br>', $user_data['user_login'] );
			/* translators: %s: user email*/
			printf( esc_html__( 'Email: %s', 'cdfs-addon' ), $user_data['user_email'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p><?php echo esc_html__( 'To activate your account please click on the activation link below or copy the activation link and open it in a browser.', 'cdfs-addon' ); ?></p>
		<p><a href="<?php echo esc_url( $activation_link ); ?>" target="_blank"><?php echo esc_html__( 'Activation Link', 'cdfs-addon' ); ?></a></p>
		<p><a href="<?php echo esc_url( $activation_link ); ?>" target="_blank"><?php echo esc_html( $activation_link ); ?></a></p>
		<?php
		break;
	case 'registration_mail': // user registration.
		?>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Hello %s, Your account is successfully registered and activated!', 'cdfs-addon' ), $user_data['user_login'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p>
			<?php esc_html_e( 'Details: ', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Username: %s ', 'cdfs-addon' ), $user_data['user_login'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p>
			<?php
			/* translators: %s: user email */
			printf( esc_html__( 'Email: %s ', 'cdfs-addon' ), $user_data['user_email'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<?php
		break;
	case 'registration_pending_for_admin_approval_mail': // user registration.
		?>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Hello %s,', 'cdfs-addon' ), $user_data['user_login'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p>
			<?php esc_html_e( 'Your account has been successfully registered. Your account status is pending for admin approval!', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Once your account has been approved, you will be able to list vehicles for sale.', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Details: ', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Username: %s ', 'cdfs-addon' ), $user_data['user_login'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p>
			<?php
			/* translators: %s: user email */
			printf( esc_html__( 'Email: %s ', 'cdfs-addon' ), $user_data['user_email'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<?php
		break;
	case 'send_user_account_status_change_mail': // sent to dealer when admin change account status pending to active.
		?>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Hello %s,', 'cdfs-addon' ), $user_data['user_login'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p>
			<?php esc_html_e( 'Your account is now approved and activated by admin.', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Now you can able to add a vehicle and manage the profile etc.', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Following are the details: ', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Username: %s ', 'cdfs-addon' ), $user_data['user_login'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p>
			<?php
			/* translators: %s: user email */
			printf( esc_html__( 'Email: %s ', 'cdfs-addon' ), $user_data['user_email'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<?php
		break;
	case 'admin_register_user': // admin notification on user registration.
		?>
		<p>
			<?php esc_html_e( 'Hello, New user is registered! ', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Following are the details: ', 'cdfs-addon' ); ?>
		</p>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Username: %s ', 'cdfs-addon' ), $user_data['user_login'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p>
			<?php
			/* translators: %s: user email */
			printf( esc_html__( 'Email: %s ', 'cdfs-addon' ), $user_data['user_email'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p>
			<?php echo esc_html__( 'Role: Car Dealer ', 'cdfs-addon' ); ?>
		</p>
		<?php
		break;
	case 'admin_user_activated': // admin notification on user account activation.
		?>
		<p><?php echo esc_html__( 'Hello,', 'cdfs-addon' ); ?></p>
		<p><?php echo esc_html__( 'A user account has been activated.', 'cdfs-addon' ); ?></p>
		<strong><?php esc_html_e( 'Account Details:', 'cdfs-addon' ); ?></strong>
		<p>
			<?php
			printf( esc_html__( 'Username: %s', 'cdfs-addon' ) . '<br>', $user_data['user_name'] );
			/* translators: %s: user email */
			printf( esc_html__( 'Email: %s', 'cdfs-addon' ) . '<br>', $user_data['user_email'] );
			/* translators: %s: User role */
			printf( esc_html__( 'Role: %s', 'cdfs-addon' ), $user_data['roles'][0] );
			?>
		</p>
		<?php
		break;
	default: // notification to user when account is activated.
		?>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Hello %s,', 'cdfs-addon' ), $user_data['user_name'] ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
			?>
		</p>
		<p><?php echo esc_html__( 'Your account is successfully activated!', 'cdfs-addon' ); ?></p>
		<strong><?php esc_html_e( 'Account Details:', 'cdfs-addon' ); ?></strong>
		<p>
			<?php
			/* translators: %s: user name */
			printf( esc_html__( 'Username: %s', 'cdfs-addon' ) . '<br>', $user_data['user_name'] );
			/* translators: %s: user email */
			printf( esc_html__( 'Email: %s', 'cdfs-addon' ) . '<br>', $user_data['user_email'] );
			/* translators: %s: User role */
			printf( esc_html__( 'Role: %s', 'cdfs-addon' ), $user_data['roles'][0] );
			?>
		</p>
		<?php
}
?>
<br><br>
<p>
	<img src="https://nordtrade.boostiprojektit.fi/wp-content/uploads/2023/10/NP_logo_italic1.png" width="250" />
</p>

<?php do_action( 'cdfs_register_user_footer' ); ?>
