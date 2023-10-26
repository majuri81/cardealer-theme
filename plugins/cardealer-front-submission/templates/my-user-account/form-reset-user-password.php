<?php
/**
 * Lost password reset form.
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/my-user-account/form-reset-user-password.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

cdfs_print_notices(); ?>

<form method="post" class="cdfs-reset-password cdfs-user-form" id="cdfs-reset-password">

	<p><?php echo apply_filters( 'cdfs_reset_password_message', esc_html__( 'Enter a new password below.', 'cdfs-addon' ) ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?></p>
	<input type="hidden" name="cdfs_nonce" value="<?php echo wp_create_nonce( 'cdfs-reset-psw' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?>" />
	<p class="cdfs-form">
		<label for="password_1"><?php esc_html_e( 'New password', 'cdfs-addon' ); ?> <span class="required">*</span></label>
		<input type="password" class="cdfs-input input-text cdhl_validate" name="password_1" id="password_1" />
	</p>
	<p class="cdfs-form">
		<label for="password_2"><?php esc_html_e( 'Re-enter new password', 'cdfs-addon' ); ?> <span class="required">*</span></label>
		<input type="password" class="cdfs-input input-text cdhl_validate" name="password_2" id="password_2" />
	</p>

	<input type="hidden" name="reset_psw_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
	<input type="hidden" name="reset_psw_login" value="<?php echo esc_attr( $args['login'] ); ?>" />
	<?php if ( cdfs_check_captcha_exists() ) { ?>
	<p class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
		<div class="form-group">
			<div id="login_captcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr( cdfs_get_goole_api_keys( 'site_key' ) ); ?>"></div>
		</div>  
	</p>
	<div class="clear"></div>
		<?php
	}
	do_action( 'cdfs_resetpassword_form' );
	?>

	<p class="cdfs-form">
		<input type="submit" class="cdfs-button button" value="<?php esc_attr_e( 'Save', 'cdfs-addon' ); ?>" />
	</p>
</form>
