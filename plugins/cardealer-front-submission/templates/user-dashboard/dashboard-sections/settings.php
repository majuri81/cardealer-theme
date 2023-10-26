<?php
// Content title.
cdfs_get_template(
	'user-dashboard/content-title.php',
	array(
		'endpoint_title' => $endpoint_title,
	)
);

$cdfs_show_email    = get_user_meta( $user->ID, 'cdfs_show_email', true );
$cdfs_show_phone    = get_user_meta( $user->ID, 'cdfs_show_phone', true );
$cdfs_show_whatsapp = get_user_meta( $user->ID, 'cdfs_show_whatsapp', true );

do_action( 'cardealer-dashboard/user-settings/before-form', $user );
?>
<form id="cdfs-edit-account-form" class="cdfs-edit-account-form edit-account form-horizontal" action="" method="post" enctype="multipart/form-data">

	<?php do_action( 'cardealer-dashboard/user-settings/form-start', $user ); ?>

	<fieldset>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="cdfs_show_email"><?php esc_html_e( 'Show Email', 'cdfs-addon' ); ?></label>
			<div class="cdfs-input-checkbox-wrap">
				<div class="profile-setting-checkbox">
					<label class="setting-checkbox">
						<input name="cdfs_show_email" type="checkbox" id="cdfs_show_email" value="1" <?php checked( $cdfs_show_email ); ?> />
						<span></span>
					</label>
				</div>
				<span><?php esc_html_e( 'Show email on the profile and other pages.', 'cdfs-addon' ); ?></span>
			</div>
		</div>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="cdfs_show_phone"><?php esc_html_e( 'Show Phone Number', 'cdfs-addon' ); ?></label>
			<div class="cdfs-input-checkbox-wrap">
				<div class="profile-setting-checkbox">
					<label class="setting-checkbox">
						<input name="cdfs_show_phone" type="checkbox" id="cdfs_show_phone" value="1" <?php checked( $cdfs_show_phone ); ?> />
						<span></span>
					</label>
				</div>
				<span><?php esc_html_e( 'Show phone number on the profile and other pages.', 'cdfs-addon' ); ?></span>
			</div>
		</div>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="cdfs_show_whatsapp"><?php esc_html_e( 'Show WhatsApp', 'cdfs-addon' ); ?></label>
			<div class="cdfs-input-checkbox-wrap">
				<div class="profile-setting-checkbox">
					<label class="setting-checkbox">
						<input name="cdfs_show_whatsapp" type="checkbox" id="cdfs_show_whatsapp" value="1" <?php checked( $cdfs_show_whatsapp ); ?> />
						<span></span>
					</label>
				</div>
				<span><?php esc_html_e( 'Show WhatsApp number on the profile and other pages.', 'cdfs-addon' ); ?></span>
			</div>
		</div>
		<div class="clearfix"></div>
	</fieldset>

	<?php do_action( 'cardealer-dashboard/user-settings/extra-fields', $user ); ?>

	<?php
	if ( cdfs_check_captcha_exists() ) {
		?>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<div class="form-group">
				<div id="login_captcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr( cdfs_get_goole_api_keys( 'site_key' ) ); ?>"></div>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php
	}
	?>

	<p>
		<?php wp_nonce_field( 'update_user_settings' ); ?>
		<input type="submit" class="cdfs-Button button" name="update_user_settings" value="<?php esc_attr_e( 'Save changes', 'cdfs-addon' ); ?>" />
		<input type="hidden" name="action" value="update_user_settings" />
	</p>

	<?php do_action( 'cardealer-dashboard/user-settings/form-end', $user ); ?>
</form>
<?php
do_action( 'cardealer-dashboard/user-settings/after-form', $user );
