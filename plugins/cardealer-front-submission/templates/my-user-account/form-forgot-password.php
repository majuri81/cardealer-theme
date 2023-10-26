<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/my-user-account/form-forgot-password.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

cdfs_print_notices(); ?>

<div class="cdfs-reset-password-form">
	<h3 class="cdfs_login-title"><?php esc_html_e( 'Reset Password', 'cdfs-addon' ); ?></h3>
	<form method="post" class="cdfs_lost_user_password">

		<input id="cdhl_nonce" name="cdhl_nonce" class="form-control" value="<?php echo wp_create_nonce( 'cdhl-lost-psw' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?>" type="hidden">
		<input type="hidden" name="cdfs_action" value="cdfs_password_reset" />

		<div class="cdfs-form-row"><?php echo apply_filters( 'cdfs_lost_user_password_message', __( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'cdfs-addon' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?></div>

		<div class="cdfs-form-row">
			<label for="user_login"><?php esc_html_e( 'Username or email', 'cdfs-addon' ); ?></label>
			<div class="cdfs-input-wrap">
				<input class="cdfs-Input" type="text" name="user_login" id="user_login" />
			</div>
		</div>
		<?php if ( cdfs_check_captcha_exists() ) { ?>
		<div class="cdfs-form-row">
			<div class="form-group">
				<div id="login_captcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr( cdfs_get_goole_api_keys( 'site_key' ) ); ?>"></div>
			</div>  
		</div>
		<div class="clear"></div>
		<?php } ?>
		<?php do_action( 'cdfs_forgot_password_form' ); ?>

		<div class="cdfs-form-row form-row-btn">
			<input type="submit" class="cdfs-button button" value="<?php esc_attr_e( 'Send reset password link', 'cdfs-addon' ); ?>" />
		</div>

	</form>
</div>