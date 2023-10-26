<div class="row">
	<div class="col-sm-12">
		<?php
		// Notices.
		cdfs_print_notices();

		do_action( 'cdfs_before_user_login_form' );
		?>
		<div class="row" id="cdfs_user_login">
			<div class="col-sm-6">
				<div class="cdfs_login">
					<h3 class="cdfs_login-title"><?php esc_html_e( 'Login', 'cdfs-addon' ); ?></h3>
					<form class="cdfs-form cdfs-form-login cdfs-user-form login" method="post" id="cdfs-form-user-login">
						<?php do_action( 'cdfs_login_form_start' ); ?>
						<div class="cdfs-form-row cdfs-msg" style="display:none"></div>
						<div class="cdfs-form-row">
							<label for="username"><?php esc_html_e( 'Username or email address', 'cdfs-addon' ); ?> <span class="required">*</span></label>
							<div class="cdfs-input-wrap"><input type="text" class="cdfs-Input cdhl_validate" name="username" id="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /></div>
						</div>
						<div class="cdfs-form-row">
							<label for="password"><?php esc_html_e( 'Password', 'cdfs-addon' ); ?> <span class="required">*</span></label>
							<div class="cdfs-input-wrap"><input class="cdfs-Input cdhl_validate" type="password" name="password" id="password" /></div>
						</div>
						<?php
						if ( cdfs_check_captcha_exists() ) {
							?>
							<div class="cdfs-form-row">
								<div class="form-group">
									<div id="login_captcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr( cdfs_get_goole_api_keys( 'site_key' ) ); ?>"></div>
								</div>
							</div>
							<?php
						}
						do_action( 'cdfs_login_form' );
						if ( isset( $_GET['redirect_to'] ) && '' !== $_GET['redirect_to'] ) {
							?>
							<input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $_GET['redirect_to'] ); ?>" />
							<?php
						}
						?>
						<div class="cdfs-form-row">
							<?php wp_nonce_field( 'cdfs-login', 'cdfs-login-nonce' ); ?>
							<button type="submit" class="cdfs-button button" name="login" value="<?php esc_attr_e( 'Login', 'cdfs-addon' ); ?>" id="form-user-login"><?php esc_attr_e( 'Login', 'cdfs-addon' ); ?></button>
							<label class="cdfs-form__label cdfs-form__label-for-checkbox inline">
								<input class="cdfs-form_input cdfs-form_input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'cdfs-addon' ); ?></span>
							</label>
						</div>
						<?php
						$lost_password_url = cdfs_get_lost_password_url();
						if ( ! empty( $lost_password_url ) ) {
							?>
							<div class="cdfs-LostPassword lost_user_password">
								<a href="<?php echo esc_url( $lost_password_url ); ?>"><?php esc_html_e( 'Lost your password?', 'cdfs-addon' ); ?></a>
							</div>
							<?php
						}
						do_action( 'cdfs_login_form_end' );
						?>
					</form>
				</div>
			</div><!-- .cdfs_login -->
			<div class="col-sm-6">
				<div class="cdfs_register">
					<h3 class="cdfs_login-title"><?php esc_html_e( 'Register', 'cdfs-addon' ); ?></h3>
					<form method="post" class="register cdfs-user-form" id="cdfs-form-register">
						<?php do_action( 'cdfs_register_form_start' ); ?>
						<div class="cdfs-form-row cdfs-msg" style="display:none"></div>
						<div class="cdfs-form-row">
							<label for="reg_username"><?php esc_html_e( 'Username', 'cdfs-addon' ); ?> <span class="required">*</span></label>
							<div class="cdfs-input-wrap"><input type="text" class="cdfs-Input cdhl_validate" name="username" id="reg_username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /></div>
						</div>
						<div class="cdfs-form-row">
							<label for="reg_email"><?php esc_html_e( 'Email address', 'cdfs-addon' ); ?> <span class="required">*</span></label>
							<div class="cdfs-input-wrap"><input type="email" class="cdfs-Input cdhl_validate cardealer_mail" name="email" id="reg_email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /></div>
						</div>
						<div class="cdfs-form-row">
							<label for="reg_password"><?php esc_html_e( 'Password', 'cdfs-addon' ); ?> <span class="required">*</span></label>
							<div class="cdfs-input-wrap"><input type="password" class="cdfs-Input cdhl_validate" name="password" id="reg_password" /></div>
						</div>
						<div class="cdfs-form-row">
							<label for="reg_user_type"><?php esc_html_e( 'User Type', 'cdfs-addon' ); ?> <span class="required">*</span></label>
							<select name="reg_user_type" id="reg_user_type" class="cdhl_validate">
								<option value=""><?php esc_html_e( 'Select user type', 'cdfs-addon' ); ?></option>
								<?php
								$default_usertype = apply_filters( 'cdfs_default_usertype', '' );
								$usertypes        = cdfs_get_usertypes();
								foreach ( $usertypes as $usertype => $usertype_data ) {
									$usertype_label = ( isset( $usertype_data['label'] ) && ! empty( $usertype_data['label'] ) ) ? $usertype_data['label'] : $usertype_data['label_original'];
									?>
									<option value="<?php echo esc_attr( $usertype ); ?>" <?php selected( $default_usertype, $usertype ); ?>><?php echo esc_html( $usertype_label ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<?php
						if ( function_exists( 'cdfs_user_register_additional_field' ) ) {
							$additional_field = cdfs_user_register_additional_field();
							if ( $additional_field && is_array( $additional_field ) )  {
								foreach ( $additional_field as $field_key => $field_val ) {
									if ( 'text' === $field_val['type'] ) {
										?>
										<div class="cdfs-form-row cdfs-form-row-<?php echo esc_attr( $field_key ); ?>">
											<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field_val['name'] ); ?></label>
											<div class="cdfs-input-wrap"><input type="text" class="cdfs-Input" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" /></div>
										</div>
										<?php
									} elseif ( 'select' === $field_val['type'] ) {
										if ( isset( $field_val['options'] ) && $field_val['options'] && is_array( $field_val['options'] ) ) {
											?>
											<div class="cdfs-form-row cdfs-form-row-<?php echo esc_attr( $field_key ); ?>">
												<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field_val['name'] ); ?></label>
												<select name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>">
													<option value=""><?php esc_html_e( 'Select option', 'cdfs-addon' ); ?></option>
													<?php
													foreach ( $field_val['options'] as $key => $val ) {
														?>
														<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $val ); ?></option>
														<?php
													}
													?>
												</select>
											</div>
											<?php
										}
									}
								}
							}
							
						}
						?>
						
						<?php if ( cdfs_check_captcha_exists() ) { ?>
						<div class="cdfs-form-row">
							<div class="form-group">
								<div id="register_captcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr( cdfs_get_goole_api_keys( 'site_key' ) ); ?>"></div>
							</div>
						</div>
						<?php } ?>
						<div class="cdfs-form-row">
							<?php
							$privacy_policy_url = get_privacy_policy_url();
							echo apply_filters(
								'cdfs_register_user_privacy_msg',
								sprintf(
									wp_kses(
										/* translators: %s: url */
										__( 'Your personal data will be used in mapping with the vehicles you added to the website, to manage access to your account, and for other purposes described in our <a href="%1$s" class="cd-policy-terms" target="_blank">privacy policy</a>.', 'cdfs-addon' ),
										array(
											'a' => array(
												'href'   => array(),
												'target' => array(),
												'class'  => array(),
											),
										)
									),
									( $privacy_policy_url ) ? $privacy_policy_url : '#'
								)
							); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE
							?>
						</div>
						<?php
						do_action( 'cdfs_register_form' );
						?>
						<div class="cdfs-form-row">
							<?php wp_nonce_field( 'cdfs-register', 'cdfs-register-nonce' ); ?>
							<button type="submit" id="cdfs-form-register-btn" class="cdfs-button button" name="register" value="<?php esc_attr_e( 'Register', 'cdfs-addon' ); ?>"><?php esc_attr_e( 'Register', 'cdfs-addon' ); ?></button>
						</div>
						<?php do_action( 'cdfs_register_form_end' ); ?>
					</form>
				</div>
			</div><!-- .cdfs_register -->
		</div><!-- .cdfs_user_login -->
		<?php
		do_action( 'cdfs_after_user_login_form' );
		?>
	</div><!-- .col-sm-12 -->
</div><!-- .row -->
