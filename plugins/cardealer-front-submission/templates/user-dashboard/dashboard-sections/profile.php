<?php
define('DONOTCACHEPAGE', true);

// Content title.
cdfs_get_template(
	'user-dashboard/content-title.php',
	array(
		'endpoint_title' => $endpoint_title,
	)
);

do_action( 'cardealer-dashboard/edit-profile/before-form', $user );
?>
<form id="cdfs-edit-account-form" class="cdfs-edit-account-form edit-account form-horizontal" action="" method="post" enctype="multipart/form-data">

	<?php
	do_action_deprecated( 'cdfs_edit_account_form_start', array(), '4.1.0', 'cardealer-dashboard/edit-profile/form-start' );
	do_action( 'cardealer-dashboard/edit-profile/form-start', $user );
	?>

	<fieldset>
		<legend><?php esc_html_e( 'Personal Information', 'cdfs-addon' ); ?></legend>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="account_first_name"><?php esc_html_e( 'First name', 'cdfs-addon' ); ?> <span class="required">*</span></label>
			<div class="cdfs-input-wrap">
				<input type="text" class="cdfs-input cdfs-input-text input-text cdhl_validate" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" />
			</div>
		</div>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="account_last_name"><?php esc_html_e( 'Last name', 'cdfs-addon' ); ?> <span class="required">*</span></label>
			<div class="cdfs-input-wrap">
				<input type="text" class="cdfs-input cdfs-input-text input-text cdhl_validate" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" />
			</div>
		</div>
		
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="account_email"><?php esc_html_e( 'Email address', 'cdfs-addon' ); ?> <span class="required">*</span></label>
			<div class="cdfs-input-wrap">
				<input type="email" class="cdfs-input cdfs-input-email input-text cdhl_validate" name="account_email" id="account_email" value="<?php echo esc_attr( $user->user_email ); ?>" />
			</div>
		</div>
		
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="account_mobile"><?php esc_html_e( 'Phone', 'cdfs-addon' ); ?></label>
			<div class="cdfs-input-wrap">
				<input type="text" class="cdfs-input cdfs-input-text input-text" name="account_mobile" id="account_mobile" value="<?php echo esc_attr( $account_mobile ); ?>" />
			</div>
		</div>
		
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="account_y_tunnus"><?php esc_html_e( 'VAT ID', 'cdfs-addon' ); ?> <span class="required">*</span></label>
			<div class="cdfs-input-wrap">
				<input type="text" class="cdfs-input cdfs-input-text input-text cdhl_validate" name="account_y_tunnus" id="account_y_tunnus" value="<?php echo esc_attr( $user->user_registration_y_tunnus ); ?>" />
			</div>
		</div>
		
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="account_company_name"><?php esc_html_e( 'Company Name', 'cdfs-addon' ); ?> <span class="required">*</span></label>
			<div class="cdfs-input-wrap">
				<input type="text" class="cdfs-input cdfs-input-text input-text cdhl_validate" name="account_company_name" id="account_company_name" value="<?php echo esc_attr( $user->user_registration_company_name ); ?>" />
			</div>
		</div>
		
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="account_company_name"><?php esc_html_e( 'E-Invoice Number', 'cdfs-addon' ); ?> <span class="required">*</span></label>
			<div class="cdfs-input-wrap">
				<input type="text" class="cdfs-input cdfs-input-text input-text cdhl_validate" name="account_e_invoice_number" id="account_e_invoice_number" value="<?php echo esc_attr( $user->user_registration_e_invoice_number ); ?>" />
			</div>
		</div>
		
		<?php
		if ( function_exists( 'cdfs_user_register_additional_field' ) ) {
			$additional_field = cdfs_user_register_additional_field();
			if ( $additional_field && is_array( $additional_field ) )  {
				foreach ( $additional_field as $field_key => $field_val ) {
					$value = get_user_meta( $user->ID, $field_key, true );
					if ( 'text' === $field_val['type'] ) {
						?>
						<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide cdfs-form-row-<?php echo esc_attr( $field_key ); ?>">
							<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field_val['name'] ); ?></label>
							<div class="cdfs-input-wrap">
								<input type="text" class="cdfs-input cdfs-input-text input-text" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
							</div>
						</div>
						<?php
					} elseif ( 'select' === $field_val['type'] ) {
						if ( isset( $field_val['options'] ) && $field_val['options'] && is_array( $field_val['options'] ) ) {
							?>
							<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wid cdfs-select-wrap cdfs-form-row-<?php echo esc_attr( $field_key ); ?>">
								<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field_val['name'] ); ?></label>
								<div class="cdfs-input-wrap">
									<select name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>">
										<option value=""><?php esc_html_e( 'Select option', 'cdfs-addon' ); ?></option>
										<?php
										foreach ( $field_val['options'] as $key => $val ) {
											?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $val ); ?></option>
											<?php
										}
										?>
									</select>
								</div>
							</div>
							<?php
						}
					}
				}
			}
			
		}
		?>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<div class="profile-image-preview">
				<div id="profile-img-preview" class="cdfs-img-select-preview-wrapper <?php echo esc_attr( $cdfs_user_avatar ? 'with-image' : 'without-image' ); ?>">
					<img class="cdfs-img-select-preview img-responsive img-thumb" src="<?php echo esc_url( $cdfs_user_avatar ? $cdfs_user_avatar : $user_img_default ); ?>"/>
				</div>
			</div>
			<div class="profile-image-info">
				<label for="cdfs_user_avatar"><?php esc_html_e( 'Profile Image', 'cdfs-addon' ); ?></label>
				<div class="cdfs-image-upload">
					<a href="#" class="button select-image"><?php esc_html_e( 'Select Image', 'cdfs-addon' ); ?></a>
					<div class="select-file-info">
						<div class="select-file-label"><?php esc_html_e( 'No file selected', 'cdfs-addon' ); ?></div>
						<div class="select-file-note"><?php esc_html_e( 'JPEG, PNG (Dimension: 150x150, minimum)', 'cdfs-addon' ); ?></div>
					</div>
					<input type="file" id="cdfs_user_avatar" name="cdfs_user_avatar" class="form-control cdfs-img-select-view-control" dara-preview_el="#profile-img-preview" accept="image/png, image/jpeg" />
				</div>
			</div>
		</div>
		<?php
		if ( 'dealer' === $user_type ) {
			?>
			<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
				<div class="profile-image-preview">
					<div id="profile-banner-preview" class="cdfs-img-select-preview-wrapper <?php echo esc_attr( $cdfs_user_banner ? 'with-image' : 'without-image' ); ?>">
						<img class="cdfs-img-select-preview img-responsive img-thumb" src="<?php echo esc_url( $cdfs_user_banner ? $cdfs_user_banner : $user_img_default ); ?>"/>
					</div>
				</div>
				<div class="profile-image-info">
					<label for="cdfs_user_banner"><?php esc_html_e( 'Profile Banner', 'cdfs-addon' ); ?></label>
					<div class="cdfs-image-upload">
						<a href="#" class="button select-image"><?php esc_html_e( 'Select Image', 'cdfs-addon' ); ?></a>
						<div class="select-file-info">
							<div class="select-file-label"><?php echo esc_html__( 'No file selected', 'cdfs-addon' ); ?></div>
							<div class="select-file-note"><?php esc_html_e( 'JPEG, PNG (Dimension: 1140x360, minimum)', 'cdfs-addon' ); ?></div>
						</div>
						<input type="file" id="cdfs_user_banner" name="cdfs_user_banner" class="form-control cdfs-img-select-view-control" dara-preview_el="#profile-banner-preview" accept="image/png, image/jpeg" />
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<div class="clearfix"></div>
	</fieldset>

	<?php do_action( 'cardealer-dashboard/edit-profile/after-personal-information', $user ); ?>
    
	<?php do_action( 'cardealer-dashboard/edit-profile/after-other-information', $user ); ?>

	<fieldset>
		<legend><?php esc_html_e( 'Password change', 'cdfs-addon' ); ?></legend>

		<div class="cdfs-form-row cdfs-form-row-wide form-row form-row-wide">
			<label for="password_current"><?php esc_html_e( 'Current password', 'cdfs-addon' ); ?></label>
			<div class="cdfs-input-wrap">
				<input type="password" class="cdfs-input cdfs-input-password input-text" name="password_current" id="password_current" />
				<div class="cdfs-input-description"><?php esc_html_e( 'Leave blank to leave unchanged.', 'cdfs-addon' ); ?></div>
			</div>
		</div>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="password_1"><?php esc_html_e( 'New password', 'cdfs-addon' ); ?></label>
			<div class="cdfs-input-wrap">
				<input type="password" class="cdfs-input cdfs-input-password input-text" name="password_1" id="password_1" />
				<div class="cdfs-input-description"><?php esc_html_e( 'Leave blank to leave unchanged.', 'cdfs-addon' ); ?></div>
			</div>
		</div>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<label for="password_2"><?php esc_html_e( 'Confirm new password', 'cdfs-addon' ); ?></label>
			<div class="cdfs-input-wrap">
				<input type="password" class="cdfs-input cdfs-input-password input-text" name="password_2" id="password_2" />
			</div>
		</div>
	</fieldset>

	<?php do_action( 'cardealer-dashboard/edit-profile/after-password-change', $user ); ?>

	<?php do_action( 'cardealer-dashboard/edit-profile/after-social-profiles', $user ); ?>

	<?php
	if ( cdfs_check_captcha_exists() ) {
		?>
		<div class="cdfs-form-row cdfs-form-row--wide form-row form-row-wide">
			<div class="form-group">
				<div id="login_captcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr( cdfs_get_goole_api_keys( 'site_key' ) ); ?>"></div>
			</div>
		</div>
		<div class="clear"></div>
		<?php
	}
	do_action( 'cdfs_edit_account_form', $user );
	?>

	<div>
		<?php wp_nonce_field( 'update_account_details' ); ?>
		<input type="submit" class="cdfs-Button button" name="update_account_details" value="<?php esc_attr_e( 'Save changes', 'cdfs-addon' ); ?>" />
		<input type="hidden" name="action" value="update_account_details" />
	</div>

	<?php
	do_action_deprecated( 'cdfs_edit_account_form_end', array(), '4.1.0', 'cardealer-dashboard/edit-profile/form-end' );
	do_action( 'cardealer-dashboard/edit-profile/form-end', $user );
	?>
</form>
<?php
do_action( 'cardealer-dashboard/edit-profile/after-form' );
