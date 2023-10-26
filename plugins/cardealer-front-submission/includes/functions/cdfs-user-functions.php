<?php
/**
 * CDFS User Functions
 *
 * @author   PotenzaGlobalSolutions
 * @category Class
 * @package  CDFS/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'cardealer_user_profile_meta_fields', 'cdfs_user_meta_fields_account_status', 5 );
function cdfs_user_meta_fields_account_status( $fields ) {

	$fields['account_status'] = array(
		'title'  => esc_html__( 'Car Dealer Frontend Submission - Acount status', 'cdfs-addon' ),
		'fields' => apply_filters( 'cardealer_user_profile_meta_fields_account_status', array(
			'cdfs_user_status' => array(
				'label'       => __( 'Status', 'cdfs-addon' ),
				'description' => '',
				'class'       => '',
				'type'        => 'select',
				'options'     => array(
					'active'  => __( 'Active', 'cdfs-addon' ),
					'pending' => __( 'Pending', 'cdfs-addon' ),
				),
			),
		) ),
	);

	return $fields;
}

add_filter( 'cardealer_user_profile_meta_fields', 'cdfs_user_meta_fields_profile_fields', 5 );
function cdfs_user_meta_fields_profile_fields( $fields ) {

	$profile_fields = array(
		'cdfs_user_avatar' => array(
			'label'              => __( 'Profile Image', 'cdfs-addon' ),
			'description'        => '',
			'class'              => '',
			'type'               => 'image_url',
			'allowed_file_types' => array( 'jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG' ),
			'file_btn'           => esc_html__( 'Select Image', 'cdfs-addon' ),
			'file_note'          => esc_html__( 'JPGE, PNG (Dimension: 150x150, minimum)', 'cdfs-addon' ),
			'image_crop'         => array(
				'width'  => 150,
				'height' => 150,
				'crop'   => true,
			),
		),
		'account_mobile' => array(
			'label'       => __( 'Mobile', 'cdfs-addon' ),
			'description' => '',
			'class'       => '',
			'type'        => 'text',
		),
		'account_whatsapp' => array(
			'label'       => __( 'WhatsApp', 'cdfs-addon' ),
			'description' => '',
			'class'       => '',
			'type'        => 'text',
		),
	);

	$fields['profile_fields'] = array(
		'title'  => esc_html__( 'Car Dealer Frontend Submission - Profile Fields', 'cdfs-addon' ),
		'fields' => apply_filters( 'cardealer_user_profile_meta_fields_profile_fields', $profile_fields ),
	);

	$dealer_profile_fields = array(
		'cdfs_user_banner' => array(
			'label'              => __( 'Profile Banner', 'cdfs-addon' ),
			'description'        => '',
			'class'              => '',
			'type'               => 'image_url',
			'allowed_file_types' => array( 'jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG' ),
			'image_crop'         => array(
				'width'  => 1140,
				'height' => 360,
				'crop'   => true,
			),
			'file_btn'          => esc_html__( 'Select Image', 'cdfs-addon' ),
			'file_note'         => esc_html__( 'JPGE, PNG (Dimension: 1140x360, minimum)', 'cdfs-addon' ),
		),
		'dealer_overview' => array(
			'label'       => __( 'Overview', 'cdfs-addon' ),
			'description' => '',
			'class'       => '',
			'type'        => 'textarea',
		),
		'dealer_location' => array(
			'label'       => __( 'Location', 'cdfs-addon' ),
			'description' => '',
			'class'       => '',
			'type'        => 'google_map',
		),
	);

	$fields['dealer-fields'] = array(
		'title'  => esc_html__( 'Car Dealer Frontend Submission - Profile Fields (for Dealers)', 'cdfs-addon' ),
		'fields' => apply_filters( 'cardealer_user_profile_meta_fields_profile_fields', $dealer_profile_fields ),
	);

	return $fields;
}

add_filter( 'cardealer_user_profile_meta_fields', 'cdfs_user_meta_fields_account_settings', 5 );
function cdfs_user_meta_fields_account_settings( $fields ) {

	$fields['user_settings'] = array(
		'title'  => esc_html__( 'Car Dealer Frontend Submission - User Settings', 'cdfs-addon' ),
		'fields' => apply_filters( 'cardealer_user_profile_meta_fields_user_settings', array(
			'cdfs_show_email' => array(
				'label'       => __( 'Show Email', 'cdfs-addon' ),
				'description' => '',
				'class'       => '',
				'type'        => 'checkbox',
			),
			'cdfs_show_phone' => array(
				'label'       => __( 'Show Phone', 'cdfs-addon' ),
				'description' => '',
				'class'       => '',
				'type'        => 'checkbox',
			),
			'cdfs_show_whatsapp' => array(
				'label'       => __( 'Show WhatsApp', 'cdfs-addon' ),
				'description' => '',
				'class'       => '',
				'type'        => 'checkbox',
			),
		) ),
	);

	return $fields;
}

add_action( 'after_setup_theme', 'cdfs_hide_top_admin_menu_bar', 100 );
if ( ! function_exists( 'cdfs_hide_top_admin_menu_bar' ) ) {
	/**
	 * Disable admin bar for users with "Car Dealer" role.
	 */
	function cdfs_hide_top_admin_menu_bar() {
		if ( ! defined( 'DOING_AJAX' ) && current_user_can( 'car_dealer' ) ) {
			show_admin_bar( false );
		}
	}
}

add_action( 'admin_init', 'cdfs_redirect_car_dealer_user' );
if ( ! function_exists( 'cdfs_redirect_car_dealer_user' ) ) {
	/**
	 * Redirect Car Dealer user to car dealer account page it try to login in admin panel
	 */
	function cdfs_redirect_car_dealer_user() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( isset( $current_user ) && ! empty( $current_user ) ) {
				$roles = $current_user->roles;
				if ( ! empty( $roles ) && in_array( 'car_dealer', $roles ) && ! defined( 'DOING_AJAX' ) ) {
					wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url() );
					exit;
				}
			}
		}
	}
}

add_filter( 'cardealer_topbar_available_items', 'cdfs_topbar_available_items' );
function cdfs_topbar_available_items( $available_items ) {
	$available_items['dealer_dashboard'] = esc_html__( 'Dealer Dashboard', 'cdfs-addon' );
	return $available_items;
}

add_filter( 'cardealer_options_topbar_fields', 'cdfs_topbar_fields' );
function cdfs_topbar_fields( $fields ) {

	$fields[] = array(
		'id'     => 'topbar_dealer_dashboard_settings_start',
		'type'   => 'section',
		'title'  => esc_html__( '"Dealer Dashboard" Element Settings', 'cdfs-addon' ),
		'indent' => true,
		'class'  => 'cardealer_hide_it',
	);
	$fields[] = array(
		'id'       => 'topbar_dealer_dashboard_login_label',
		'type'     => 'text',
		'title'    => esc_html__( 'Log-in Label', 'cdfs-addon' ),
		'desc'     => esc_html__( 'Set a label to display when the user is not logged in.', 'cdfs-addon' ),
		'default'  => esc_html__( 'Login', 'cdfs-addon' ),
	);
	$fields[] = array(
		'id'       => 'topbar_dealer_dashboard_loggedin_label',
		'type'     => 'text',
		'title'    => esc_html__( 'Logged-in Label', 'cdfs-addon' ),
		'desc'     => esc_html__( 'Set a label to display when the user is logged-in.', 'cdfs-addon' ),
		'default'  => esc_html__( 'My Dashboard', 'cdfs-addon' ),
	);

	return $fields;
}


add_action( 'topbar_element_dealer_dashboard', 'cdfs_topbar_content_dealer_dashboard', 10, 2 );
function cdfs_topbar_content_dealer_dashboard( $field, $labels ) {
	$login_link    = cdfs_get_page_permalink( 'dealer_login' );
	$loggedin_link = cdfs_get_cardealer_dashboard_endpoint_url();
	echo sprintf(
		'<a href="%s"><i class="%s"></i> %s</a>',
		esc_url( ( is_user_logged_in() ) ? $loggedin_link : $login_link ),
		esc_attr( ( is_user_logged_in() ) ? 'far fa-user' : 'fas fa-lock' ),
		esc_html( ( is_user_logged_in() ) ? $labels['loggedin_label'] : $labels['login_label'] )
	);
}

if ( ! function_exists( 'cdfs_create_user' ) ) {

	/**
	 * Create a new user.
	 *
	 * @param  string $email : User email.
	 * @param  string $username : User username.
	 * @param  string $password : User password.
	 * @return int|WP_Error Returns WP_Error on failure, Int (user ID) on success.
	 */
	function cdfs_create_user( $email, $username = '', $password = '', $role = 'car_dealer', $additional_data = array() ) {
		// Check the email address.
		if ( empty( $email ) || ! is_email( $email ) ) {
			return new WP_Error( 'registration-error-invalid-email', esc_html__( 'Please provide a valid email address.', 'cdfs-addon' ) );
		}

		if ( email_exists( $email ) ) {
			return new WP_Error( 'registration-error-email-exists', esc_html__( 'An account is already registered with your email address. Please log in.', 'cdfs-addon' ) );
		}

		// Handle username creation.
		if ( ! empty( $username ) ) {
			$username = sanitize_user( $username );

			if ( ! validate_username( $username ) ) {
				return new WP_Error( 'registration-error-invalid-username', esc_html__( 'Please enter a valid account username.', 'cdfs-addon' ) );
			}

			if ( username_exists( $username ) ) {
				return new WP_Error( 'registration-error-username-exists', esc_html__( 'An account is already registered with that username. Please choose another.', 'cdfs-addon' ) );
			}
		} else {
			return new WP_Error( 'registration-error-username-empty', esc_html__( 'Please enter username.', 'cdfs-addon' ) );
		}

		// Handle password creation.
		if ( empty( $password ) ) {
			return new WP_Error( 'registration-error-missing-password', esc_html__( 'Please enter an account password.', 'cdfs-addon' ) );
		}

		// Use WP_Error to handle registration errors.
		$errors = new WP_Error();
		do_action( 'cdfs_register_post', $username, $email, $errors );
		$errors = apply_filters( 'cdfs_registration_errors', $errors, $username, $email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		$new_user_data = apply_filters(
			'cdfs_new_customer_data',
			array(
				'user_login' => $username,
				'user_pass'  => $password,
				'user_email' => $email,
				'role'       => $role,
			)
		);

		$user_id = wp_insert_user( $new_user_data );
		if ( is_wp_error( $user_id ) ) {
			return new WP_Error( 'registration-error', '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . esc_html__( 'Couldn\'t register you. Please contact us if you continue to have problems.', 'cdfs-addon' ) );
		}
		// Set status of new user.
		$userinfo        = array(
			'email'     => $email,
			'user_data' => $new_user_data,
			'user_id'   => $user_id,
		);

		if ( $additional_data && is_array( $additional_data ) ) {
			foreach ( $additional_data as $field_key => $field_val ) {
				add_user_meta( $user_id, $field_key, $field_val );
			}
		}

		$activate_method = cdfs_user_activation_method();
		if ( 'mail' === $activate_method ) {
			add_user_meta( $user_id, 'cdfs_user_status', 'pending', true );
			$mail_sent = cdfs_send_activation_link_mail( $userinfo ); // send activation link.
			if ( false === $mail_sent ) {
				return false;
			}
		} elseif ( 'admin' === $activate_method ) {
			add_user_meta( $user_id, 'cdfs_user_status', 'pending', true );
			$mail_sent = cdfs_send_registration_pending_for_admin_approval_mail( $userinfo );
			if ( false === $mail_sent ) {
				return false;
			}
		} else {
			$mail_sent = cdfs_send_registration_mail( $userinfo );
			if ( false === $mail_sent ) {
				return false;
			}
		}
		return $user_id;
	}
}

if ( ! function_exists( 'cdfs_clean' ) ) {
	/**
	 * Cdfs clean
	 *
	 * @param string $var .
	 */
	function cdfs_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'cdfs_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}
}

if ( ! function_exists( 'cdfs_is_user_account_page' ) ) {
	/**
	 * User account page
	 */
	function cdfs_is_user_account_page() {
		global $post;

		$is_dealer_login_page   = ( is_singular( 'page' ) && has_shortcode( $post->post_content, 'cardealer_dealer_login' ) );
		$is_add_car_page        = ( is_singular( 'page' ) && has_shortcode( $post->post_content, 'cardealer_add_car' ) );
		$is_dealer_dashboard    = is_author();

		$is_user_account_page = ( $is_dealer_login_page || $is_dealer_dashboard || $is_add_car_page );

		$is_user_account_page = apply_filters( 'cdfs_is_user_account_page', $is_user_account_page, $post );

		return $is_user_account_page;
	}
}

if ( ! function_exists( 'cdfs_reset_forgot_psw_url' ) ) {
	/**
	 * Reset forgot password link displayed when wrong login details are added
	 *
	 * @param string $lostpassword_url .
	 * @param string $redirect .
	 */
	function cdfs_reset_forgot_psw_url( $lostpassword_url, $redirect ) {
		if ( isset( $_POST['cdfs-login-nonce'] ) ) {
			return cdfs_get_lost_password_url();
		}
		return $lostpassword_url;
	}
}
add_filter( 'lostpassword_url', 'cdfs_reset_forgot_psw_url', 10, 2 );

if ( ! function_exists( 'cdfs_user_register_additional_field' ) ) {
	/**
	 * Hook to add additinal fields
	 */
	 function cdfs_user_register_additional_field() {

		$fields = array();

		/*
		ex.
		$fields = array(
			'field_id'    => array(
				'name'    => 'Phone',
				'type'    => 'text/select',
				'options' => array(
					'key' => 'Label',
				)
			)
		);
		*/

		return apply_filters( 'cdfs_user_register_additional_field', $fields );
	}
}

add_action( 'wp_ajax_cdfs_do_ajax_user_login', 'cdfs_do_user_login' );
add_action( 'wp_ajax_nopriv_cdfs_do_ajax_user_login', 'cdfs_do_user_login' );
if ( ! function_exists( 'cdfs_do_user_login' ) ) {
	/**
	 * User login with ajax
	 *
	 * @throws Exception In case of failures, an exception is thrown.
	 */
	function cdfs_do_user_login() {
		$responsearray = array(
			'status'    => false,
			'message'   => esc_html__( 'Something went wrong!', 'cdfs-addon' ),
		);
		$nonce_value   = isset( $_POST['cdfs-login-nonce'] ) ? cdfs_clean( $_POST['cdfs-login-nonce'] ) : '';

		if ( isset( $_POST['action'] ) && 'cdfs_do_ajax_user_login' === $_POST['action'] && wp_verify_nonce( $nonce_value, 'cdfs-login' ) ) {

			if ( ! cdfs_validate_captcha() ) { // captcha serverd side validation.
				echo wp_json_encode(
					array(
						'status'  => false,
						'message' => '<strong>' . esc_html__(
							'Error:',
							'cdfs-addon'
						) . '</strong> ' . esc_html__(
							'Please check captcha form!',
							'cdfs-addon'
						),
					)
				);
				die;
			}
			try {
				$creds = array(
					'user_password' => cdfs_clean( wp_unslash( $_POST['password'] ) ),
					'remember'      => isset( $_POST['rememberme'] ),
				);

				if ( empty( $_POST['username'] ) || empty( $_POST['password'] ) ) {
					throw new Exception( '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . esc_html__( 'Please fill the required fields.', 'cdfs-addon' ) );
				}

				$username         = trim( cdfs_clean( wp_unslash( $_POST['username'] ) ) );
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'cdfs_process_user_login_errors', $validation_error, $username, wp_unslash( $_POST['password'] ) );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . $validation_error->get_error_message() );
				}

				if ( is_email( $username ) && apply_filters( 'cdfs_get_username_from_email', true ) ) {
					$user = get_user_by( 'email', $username );
					if ( ! $user ) {
						$user = get_user_by( 'login', $username );
					}

					if ( isset( $user->user_login ) ) {
						$creds['user_login'] = $user->user_login;
					} else {
						throw new Exception( '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . esc_html__( 'A user could not be found with this email address.', 'cdfs-addon' ) );
					}
				} else {
					$creds['user_login'] = $username;
				}

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( 'login', $username );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						$usertype = cdfs_get_usertype( $user_data );
						$userrole = cdfs_get_usertype_userrole( $usertype );
						add_user_to_blog( get_current_blog_id(), $user_data->ID, $usertype );
					}
				}

				add_action( 'set_logged_in_cookie', 'cdfs_logged_in_update_cookie' );

				// Perform the login.
				$user = wp_signon( apply_filters( 'cdfs_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					$message = $user->get_error_message();
					$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $username ) . '</strong>', $message );
					throw new Exception( $message );
				} else {
					// get user info.
					$userinfo = get_user_meta( $user->ID, 'cdfs_user_status', true );
					wp_set_current_user( $user->ID );

					// If user login with roles other than car_dealer.
					if (
						! in_array( 'car_dealer', $user->roles )
						&& ! in_array( 'customer', $user->roles )
						&& ! in_array( 'subscriber', $user->roles )
						&& ! in_array( 'administrator', $user->roles )
					) {
						wp_logout();
						$responsearray = array(
							'status'  => false,
							'message' => esc_html__( 'Please login with "Dealer Or Customer" account.', 'cdfs-addon' ),
						);
					} elseif ( ! empty( $userinfo ) && 'pending' === $userinfo ) { // check user status.
						wp_logout();

						$activate_method = cdfs_user_activation_method();
						if ( 'mail' === $activate_method ) {
							$responsearray = array(
								'status'  => false,
								'message' => esc_html__( 'Please activate your account with the activation link sent to your registered mail account.', 'cdfs-addon' ),
							);
						} elseif ( 'admin' === $activate_method ) {
							$responsearray = array(
								'status'  => false,
								'message' => esc_html__( 'Your account is waiting for admin approval. Once your account is approved by the admin, you will be able to manage vehicles.', 'cdfs-addon' ),
							);
						}
					} else {
						$logined_user  = explode( '@', $username, 2 );
						$logined_user  = $logined_user[0];
						$responsearray = array(
							'status'         => true,
							'cdfs_user_name' => $logined_user,
							'message'        => esc_html__( 'Successfully logged in!', 'cdfs-addon' ),
							'new_nouce'      => wp_create_nonce( 'cdfs-car-form' ),
						);
					}
				}
			} catch ( Exception $e ) {
				$responsearray = array(
					'status'  => false,
					'message' => $e->getMessage(),
				);
			}
		}
		echo wp_json_encode( $responsearray );
		die;
	}
}

/**
 * Update the cookie after user login.
 *
 */
function cdfs_logged_in_update_cookie( $logged_in_cookie ){
    $_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
}

add_action( 'wp_ajax_cdfs_do_ajax_user_register', 'cdfs_do_user_registration' );
add_action( 'wp_ajax_nopriv_cdfs_do_ajax_user_register', 'cdfs_do_user_registration' );
if ( ! function_exists( 'cdfs_do_user_registration' ) ) {
	/**
	 * User login with ajax
	 *
	 * @throws Exception In case of failures, an exception is thrown.
	 */
	function cdfs_do_user_registration() {
		global $car_dealer_options;

		$responsearray = array(
			'status'    => false,
			'message'   => esc_html__( 'Something went wrong!', 'cdfs-addon' ),
		);
		$nonce_value   = isset( $_POST['cdfs-register-nonce'] ) ? wp_unslash( $_POST['cdfs-register-nonce'] ) : '';

		if ( isset( $_POST['action'] ) && 'cdfs_do_ajax_user_register' === $_POST['action'] && wp_verify_nonce( $nonce_value, 'cdfs-register' ) ) {
			$username        = wp_unslash( $_POST['username'] );
			$password        = wp_unslash( $_POST['password'] );
			$email           = wp_unslash( $_POST['email'] );
			$reg_user_type   = wp_unslash( $_POST['reg_user_type'] );
			$additional_data = array();
			$user_role       = cdfs_get_usertype_userrole( cdfs_clean( $reg_user_type ) );

			if ( function_exists( 'cdfs_user_register_additional_field' ) ) {
				$additional_field = cdfs_user_register_additional_field();
				if ( $additional_field && is_array( $additional_field ) )  {
					foreach ( $additional_field as $field_key => $field_val ) {
						if ( isset( $_POST[$field_key] ) ) {
							$additional_data[$field_key] = wp_unslash( $_POST[$field_key] );
						}
					}
				}
			}

			if ( isset( $car_dealer_options['demo_mode'] ) ) {
				$demo_mode = (bool) $car_dealer_options['demo_mode'];
				if ( $demo_mode ) {
					echo wp_json_encode(
						array(
							'status'  => false,
							'message' => '<strong>' . esc_html__(
								'Error:',
								'cdfs-addon'
							) . '</strong> ' . esc_html__(
								"The site is currently in demo mode, registration is disabled.",
								'cdfs-addon'
							),
						)
					);
					die;
				}
			}

			if ( ! cdfs_validate_captcha() ) { // captcha server side validation.
				echo wp_json_encode(
					array(
						'status'  => false,
						'message' => '<strong>' . esc_html__(
							'Error:',
							'cdfs-addon'
						) . '</strong> ' . esc_html__(
							'Please check captcha form!',
							'cdfs-addon'
						),
					)
				);
				die;
			}

			try {
				if ( empty( $_POST['username'] ) || empty( $_POST['password'] ) || empty( $_POST['email'] ) ) {
					throw new Exception( esc_html__( 'Please fill the required fields.', 'cdfs-addon' ) );
				}

				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'cdfs_process_user_registration_errors', $validation_error, $username, $password, $email );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( $validation_error->get_error_message() );
				}

				$new_user = cdfs_create_user( sanitize_email( $email ), cdfs_clean( $username ), $password, $user_role, $additional_data );

				if ( is_wp_error( $new_user ) ) {
					throw new Exception( $new_user->get_error_message() );
				} else {

					add_action( 'set_logged_in_cookie', 'cdfs_logged_in_update_cookie' );

					// if success, then login automatically.
					$user = wp_signon(
						apply_filters(
							'cdfs_login_credentials',
							array(
								'user_login'    => cdfs_clean( $username ),
								'user_password' => cdfs_clean( $password ),
								'remember'      => false,
							)
						),
						is_ssl()
					);

					if ( is_wp_error( $user ) ) {
						$message = $user->get_error_message();
						$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $username ) . '</strong>', $message );
						throw new Exception( $message );
					}

					wp_set_current_user( $user->ID );

					$logined_user  = explode( '@', $username, 2 );
					$logined_user  = $logined_user[0];
					$responsearray = array(
						'status'         => true,
						'cdfs_user_name' => $logined_user,
						'message'        => esc_html__( 'You are successfully registered!', 'cdfs-addon' ),
						'new_nouce'      => wp_create_nonce( 'cdfs-car-form' ),
					);
				}
			} catch ( Exception $e ) {
				$responsearray = array(
					'status'  => false,
					'message' => '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . $e->getMessage(),
				);
			}
		}
		echo wp_json_encode( $responsearray );
		die;
	}
}


if ( ! function_exists( 'cdfs_send_activation_link_mail' ) ) {
	/**
	 * Send user/dealer registration mail
	 *
	 * @param string $userinfo .
	 */
	function cdfs_send_activation_link_mail( $userinfo = array() ) {
		if ( empty( $userinfo ) ) {
			return false;
		}
		// get site details.
		$site_title = get_bloginfo( 'name' );
		$site_email = get_bloginfo( 'admin_email' );
		$site_url   = site_url();

		// Send email notification.
		$to       = $userinfo['email'];
		$subject  = esc_html__( 'New Registration', 'cdfs-addon' );
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
		$headers .= 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";

		// Mail to user.
		ob_start();
		// make activation_token.

		// generate random string.
		$str              = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random_string    = substr( str_shuffle( str_repeat( $str, ceil( 10 / strlen( $str ) ) ) ), 0, 10 );
		$activation_token = md5( uniqid( $random_string . $userinfo['email'] . time(), true ) );
		cdfs_get_template(
			'mails/mail-register-user.php',
			array(
				'user_data'        => $userinfo['user_data'],
				'mail_to'          => 'user',
				'site_data'        => array(
					'site_title' => $site_title,
					'site_url'   => $site_url,
				),
				'activation_token' => $activation_token,
				'action'           => 'activation_link_mail',
			)
		);
		$user_message = ob_get_contents();
		ob_end_clean();

		// send mail.
		try {
			wp_mail( $to, $subject, $user_message, $headers ); // Mail to user.
			add_user_meta( $userinfo['user_id'], 'cdfs_user_activation_tkn', $activation_token, true ); // set activation token.
			return true;
		} catch ( Exception $e ) {
			cdfs_add_notice( $e->getMessage(), 'error' );
			return false;
		}
		return false;
	}
}

if ( ! function_exists( 'cdfs_send_registration_mail' ) ) {
	/**
	 * Send user/dealer registration mail
	 *
	 * @param array $userinfo .
	 */
	function cdfs_send_registration_mail( $userinfo = array() ) {
		if ( empty( $userinfo ) ) {
			return false;
		}
		// Get site details.
		$site_title = get_bloginfo( 'name' );
		$site_email = get_bloginfo( 'admin_email' );
		$site_url   = site_url();
		// Send email notification.
		$to            = $userinfo['email'];
		$admin_subject = esc_html__( 'New Dealer Registration', 'cdfs-addon' );
		$user_subject  = esc_html__( 'Registration Success', 'cdfs-addon' );
		$headers       = 'MIME-Version: 1.0' . "\r\n";
		$headers      .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
		$headers      .= 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";

		// Mail to user.
		ob_start();
		cdfs_get_template(
			'mails/mail-register-user.php',
			array(
				'user_data' => $userinfo['user_data'],
				'mail_to'   => 'user',
				'site_data' => array(
					'site_title' => $site_title,
					'site_url'   => $site_url,
				),
				'action'    => 'registration_mail',
			)
		);
		$user_message = ob_get_contents();
		ob_end_clean();

		// Mail to admin.
		ob_start();
		cdfs_get_template(
			'mails/mail-register-user.php',
			array(
				'user_data' => $userinfo['user_data'],
				'mail_to'   => 'admin',
				'site_data' => array(
					'site_title' => $site_title,
					'site_url'   => $site_url,
				),
				'action'    => 'admin_register_user',
			)
		);
		$admin_message = ob_get_contents();
		ob_end_clean();

		// send mail.
		try {
			wp_mail( $to, $user_subject, $user_message, $headers ); // mail to user.
			wp_mail( $site_email, $admin_subject, $admin_message, $headers ); // Mail to admin.
			return true;
		} catch ( Exception $e ) {
			cdfs_add_notice( $e->getMessage(), 'error' );
			return false;
		}
		return false;
	}
}

if ( ! function_exists( 'cdfs_send_registration_pending_for_admin_approval_mail' ) ) {
	/**
	 * Send user/dealer registration mail
	 *
	 * @param array $userinfo .
	 */
	function cdfs_send_registration_pending_for_admin_approval_mail( $userinfo = array() ) {
		if ( empty( $userinfo ) ) {
			return false;
		}
		// get site details.
		$site_title = get_bloginfo( 'name' );
		$site_email = get_bloginfo( 'admin_email' );
		$site_url   = site_url();
		// Send email notification.
		$to            = $userinfo['email'];
		$admin_subject = esc_html__( 'New Dealer Registration', 'cdfs-addon' );
		$user_subject  = esc_html__( 'Registration Success.', 'cdfs-addon' );
		$headers       = 'MIME-Version: 1.0' . "\r\n";
		$headers      .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
		$headers      .= 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";

		// Mail to user.
		ob_start();
		cdfs_get_template(
			'mails/mail-register-user.php',
			array(
				'user_data' => $userinfo['user_data'],
				'mail_to'   => 'user',
				'site_data' => array(
					'site_title' => $site_title,
					'site_url'   => $site_url,
				),
				'action'    => 'registration_pending_for_admin_approval_mail',
			)
		);
		$user_message = ob_get_contents();
		ob_end_clean();

		// Mail to admin.
		ob_start();
		cdfs_get_template(
			'mails/mail-register-user.php',
			array(
				'user_data' => $userinfo['user_data'],
				'mail_to'   => 'admin',
				'site_data' => array(
					'site_title' => $site_title,
					'site_url'   => $site_url,
				),
				'action'    => 'admin_register_user',
			)
		);
		$admin_message = ob_get_contents();
		ob_end_clean();

		// send mail.
		try {
			wp_mail( $to, $user_subject, $user_message, $headers ); // mail to user.
			wp_mail( $site_email, $admin_subject, $admin_message, $headers ); // Mail to admin.
			return true;
		} catch ( Exception $e ) {
			cdfs_add_notice( $e->getMessage(), 'error' );
			return false;
		}
		return false;
	}
}

if ( ! function_exists( 'cdfs_send_user_account_status_change_mail' ) ) {
	/**
	 * Send user/dealer registration mail
	 *
	 * @param array $userinfo .
	 */
	function cdfs_send_user_account_status_change_mail( $userinfo = array() ) {
		if ( empty( $userinfo ) ) {
			return false;
		}

		// get site details.
		$site_title = get_bloginfo( 'name' );
		$site_email = get_bloginfo( 'admin_email' );
		$site_url   = site_url();
		// Send email notification.
		$to           = $userinfo->data->user_email;
		$user_subject = esc_html__( 'Account activation alert.', 'cdfs-addon' );
		$headers      = 'MIME-Version: 1.0' . "\r\n";
		$headers     .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
		$headers     .= 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";
		$user_data    = array(
			'user_login' => $userinfo->data->user_login,
			'user_email' => $userinfo->data->user_email,
		);
		// Mail to user.
		ob_start();
		cdfs_get_template(
			'mails/mail-register-user.php',
			array(
				'user_data' => $user_data,
				'mail_to'   => 'user',
				'site_data' => array(
					'site_title' => $site_title,
					'site_url'   => $site_url,
				),
				'action'    => 'send_user_account_status_change_mail',
			)
		);
		$user_message = ob_get_contents();
		ob_end_clean();

		// send mail.
		try {
			wp_mail( $to, $user_subject, $user_message, $headers ); // mail to user.
			return true;
		} catch ( Exception $e ) {
			cdfs_add_notice( $e->getMessage(), 'error' );
			return false;
		}
		return false;
	}
}

if ( ! function_exists( 'cdfs_send_user_activation_mail' ) ) {
	/**
	 * Send user/dealer registration mail
	 *
	 * @param array $userinfo .
	 */
	function cdfs_send_user_activation_mail( $userinfo = array() ) {
		if ( empty( $userinfo ) ) {
			return false;
		}
		// get site details.
		$site_title = get_bloginfo( 'name' );
		$site_email = get_bloginfo( 'admin_email' );
		$site_url   = site_url();
		// Send email notification.
		$to       = $userinfo['user_email'];
		$subject  = esc_html__( 'Acccount Activated - DO NOT REPLY', 'cdfs-addon' );
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
		$headers .= 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";

		// Mail to user.
		ob_start();
		cdfs_get_template(
			'mails/mail-register-user.php',
			array(
				'user_data' => $userinfo,
				'mail_to'   => 'user',
				'site_data' => array(
					'site_title' => $site_title,
					'site_url'   => $site_url,
				),
				'action'    => 'activation_mail',
			)
		);
		$user_message = ob_get_contents();
		ob_end_clean();

		// Mail to admin.
		ob_start();
		cdfs_get_template(
			'mails/mail-register-user.php',
			array(
				'user_data' => $userinfo,
				'mail_to'   => 'admin',
				'site_data' => array(
					'site_title' => $site_title,
					'site_url'   => $site_url,
				),
				'action'    => 'admin_user_activated',
			)
		);
		$admin_message = ob_get_contents();
		ob_end_clean();

		// send mail.
		try {
			wp_mail( $to, $subject, $user_message, $headers ); // Mail to user.
			wp_mail( $site_email, $subject, $admin_message, $headers ); // Mail to admin.
			return true;
		} catch ( Exception $e ) {
			cdfs_add_notice( $e->getMessage(), 'error' );
			return false;
		}
		return false;
	}
}

if ( ! function_exists( 'cdfs_activate_user_account_by_token' ) ) {
	/**
	 * Function which activates user account
	 *
	 * @param string $token .
	 */
	function cdfs_activate_user_account_by_token( $token ) {
		if ( ! empty( $token ) ) {
			$user_args = array(
				// 'role'    => 'car_dealer',
				'meta_key'   => 'cdfs_user_activation_tkn',
				'meta_value' => $token,
			);
			$user      = get_users( $user_args );
			if ( ! empty( $user ) && isset( $user[0]->ID ) ) {
				$user_status = get_user_meta( $user[0]->ID, 'cdfs_user_status', true );
				if ( ! empty( $user_status ) && ( 'active' === $user_status ) ) {
					cdfs_add_notice( esc_html__( 'Your account is already active.', 'cdfs-addon' ), 'success' );
					return true;
				}

				update_user_meta( $user[0]->ID, 'cdfs_user_status', 'active' );
				delete_user_meta( $user[0]->ID, 'cdfs_user_activation_tkn' );

				$user_data = array(
					'user_name'      => $user[0]->data->user_login,
					'user_email'     => $user[0]->data->user_email,
					'roles'          => $user[0]->roles,
				);

				cdfs_send_user_activation_mail( $user_data );
				cdfs_add_notice( esc_html__( 'Congratulations! Your account is activated successfully.', 'cdfs-addon' ), 'success' );
				return true;
			}
		}
		cdfs_add_notice( esc_html__( 'Error! Invalid activation link.', 'cdfs-addon' ), 'error' );
		return false;
	}
}

add_action( 'subscriptio_subscription_status_changing', 'cdfs_subscription_status_changing', 10, 3 );
/**
 * Subscription status changing
 *
 * @param string $subscription .
 * @param string $old_statu .
 * @param string $new_statu .
 */
function cdfs_subscription_status_changing( $subscription, $old_status, $new_status ) {

	$old_statuses = array(
		'trial',
		'active',
		'set-to-cancel',
	);

	$new_statuses = array(
		'pending',
		'expired',
		'paused',
		'suspended',
		'cancelled',
	);

	$subscription_id = $subscription->get_id();
	$user_id         = $subscription->get_customer_id();

	if ( in_array( $new_status, $new_statuses, true ) && in_array( $new_status, $new_statuses, true ) ) {
		$args = array(
			'post_type'      => 'cars',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'author'         => $user_id,
			'post_status'    => array( 'publish', 'pending' ),
			'meta_query' => array(
				array(
					'key'     => 'cdfs_subscription_id',
					'value'   => array( $subscription_id ),
					'compare' => 'IN',
				),
			),
		);
		$cars_data = new WP_Query( $args );
		$cars_ids  = isset( $cars_data->posts ) ? $cars_data->posts : array();

		foreach( $cars_ids as $car_id ) {
			wp_update_post( array(
				'ID'          => $car_id,
				'post_status' => 'draft',
			) );
		}
	}

	if ( 'active' === $new_status ) {
		$items = $subscription->get_items();
		foreach ( $items as $item ) {
			$_product_id = $item->get_product_id();
			if ( subscriptio_is_subscription_product( $_product_id ) ) {

				$post_limit  = intval( get_post_meta( $_product_id, 'cdfs_car_quota', true ) );
				$image_limit = intval( get_post_meta( $_product_id, 'cdfs_car_images_quota', true ) );

				if ( ! metadata_exists( 'post', $subscription_id, 'cdfs_car_limt' ) ) {
					update_post_meta( $subscription_id, 'cdfs_car_limt', $post_limit );
				}

				if ( ! metadata_exists( 'post', $subscription_id, 'cdfs_img_limt' ) ) {
					update_post_meta( $subscription_id, 'cdfs_img_limt', $image_limit );
				}
			}
		}
	}
}

/*
 * Get user avatar URL.
 *
 * @param int $user_id User ID.
 * @return string|false
 */
function cdfs_get_avatar_url( $user_id = 0 ) {
	$user_id = (int) $user_id;
	$url     = trailingslashit( CDFS_URL ) . 'images/profile-default.jpg';

	if ( ! empty( $user_id ) ) {
		$cdfs_user_avatar   = get_user_meta( $user_id, 'cdfs_user_avatar', true );
		if ( $cdfs_user_avatar ) {
			$url = $cdfs_user_avatar;
		}
	}

	$url = apply_filters( 'cdfs_get_avatar_url', $url, $user_id );

	return $url;
}

/*
 * Get user banner URL.
 *
 * @param int $user_id User ID.
 * @return string|false
 */
function cdfs_get_banner_url( $user_id = 0 ) {
	$user_id = (int) $user_id;
	$url     = trailingslashit( CDFS_URL ) . 'images/banner-default.jpg';;

	if ( ! empty( $user_id ) ) {
		$cdfs_user_banner = get_user_meta( $user_id, 'cdfs_user_banner', true );
		if ( $cdfs_user_banner ) {
			$url = $cdfs_user_banner;
		}
	}

	$url = apply_filters( 'cdfs_get_banner_url', $url, $user_id );

	return $url;
}

/*
 * Get user profile layout.
 *
 * @param int $user_id User ID.
 * @return string
 */
function cdfs_get_profile_layout( $user_id = 0 ) {
	$user_id = (int) $user_id;
	$layout  = 'layout-1';

	if ( ! empty( $user_id ) ) {
		$profile_layout = get_user_meta( $user_id, 'cdfs_profile_layout', true );
		$layout         = ( $profile_layout ) ? $profile_layout : $layout;
	}

	$layout = apply_filters( 'cdfs_get_profile_layout', $layout, $user_id );

	return $layout;
}

function cdfs_get_user_email( $user_id ) {
	$user_email      = false;
	$user_email_meta = get_the_author_meta( 'user_email', $user_id );

	if ( $user_email_meta ) {
		$user_email = $user_email_meta;
	}

	return $user_email;
}

function cdfs_get_user_phone( $user_id ) {
	$phone      = false;
	$phone_meta = get_the_author_meta( 'account_mobile', $user_id );

	if ( $phone_meta ) {
		$phone = $phone_meta;
	}

	$phone = apply_filters( 'cdfs_get_user_phone', $phone, $user_id );

	return $phone;
}

function cdfs_get_user_phone_url( $phone = '', $user_id = 0 ) {
	$phone_url    = false;
	$phone_parsed = '';

	if ( empty( $phone ) && $user_id ) {
		$phone = cdfs_get_user_phone( $user_id );
	}

	if ( ! empty( $phone ) ) {
		// Get first character
		$first_char = substr( $phone, 0, 1 );

		// Prepare URL safe phone number.
		$phone_parsed = preg_replace( "/[^0-9]/", "", $phone );

		// Add '+' before phone if first character was '+'.
		if ( '+' === $first_char ) {
			$phone_parsed = '+' . $phone_parsed;
		}
	}

	$phone_url = "tel:{$phone_parsed}";

	$phone_url = apply_filters( 'cdfs_get_user_phone_url', $phone_url, $phone_parsed, $phone, $user_id );

	return $phone_url;
}

function cdfs_get_user_whatsapp( $user_id ) {
	$user_whatsapp      = false;
	$user_whatsapp_meta = get_the_author_meta( 'account_whatsapp', $user_id );

	if ( $user_whatsapp_meta ) {
		$user_whatsapp = $user_whatsapp_meta;
	}

	// Remove extra characters from the number.
	$user_whatsapp = preg_replace( "/[^0-9]/", "", $user_whatsapp );

	$user_whatsapp = apply_filters( 'cdfs_get_user_whatsapp', $user_whatsapp, $user_id );

	return $user_whatsapp;
}

function cdfs_get_lost_password_url() {

	$lost_password_url = add_query_arg( array(
		'cdfs-action' => 'lostpassword',
	), cdfs_get_page_permalink( 'dealer_login' ) );

	return apply_filters( 'cdfs_get_lost_password_url', $lost_password_url );
}

/**
 * Handles sending password retrieval email to user.
 *
 * Based on retrieve_password() in core wp-login.php.
 *
 * @uses $wpdb WordPress Database object
 * @return bool True: when finish. False: on error
 */
function cdfs_send_password_reset_link() {
	$login = cdfs_clean( trim( $_POST['user_login'] ) );

	if ( ! cdfs_validate_captcha() ) { // captcha serverd side validation.
		return;
	}

	if ( empty( $login ) ) {
		cdfs_add_notice( __( 'Enter a username or email address.', 'cdfs-addon' ), 'error' );
		return false;
	} else {
		// Check on username first, may be emails is used as usernames.
		$user_data = get_user_by( 'login', $login );
	}

	// If no user found, check if emailid id provided in input, if yes then retrive details based on email.
	if ( ! $user_data && is_email( $login ) && apply_filters( 'cdfs_get_username_from_email', true ) ) {
		$user_data = get_user_by( 'email', $login );
	}
	$errors = new WP_Error();
	do_action( 'cdfs_lostpassword_error', $errors );

	if ( $errors->get_error_code() ) {
		cdfs_add_notice( $errors->get_error_message(), 'error' );
		return false;
	}

	if ( ! $user_data ) {
		cdfs_add_notice( __( 'Invalid username or email.', 'cdfs-addon' ), 'error' );
		return false;
	}

	if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
		cdfs_add_notice( __( 'Invalid username or email.', 'cdfs-addon' ), 'error' );
		return false;
	}

	// Get password reset key.
	$key = get_password_reset_key( $user_data );

	if ( is_wp_error( $key ) ) {
		cdfs_add_notice( $key->get_error_message(), 'error' );
		return false;
	}

	// get site details.
	$site_title = get_bloginfo( 'name' );
	$site_email = get_bloginfo( 'admin_email' );

	// Send email notification.
	$to       = $user_data->data->user_email;
	$subject  = esc_html__( 'Password Reset', 'cdfs-addon' );
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
	$headers .= 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";

	ob_start();
	cdfs_get_template(
		'mails/mail-reset-psw-link.php',
		array(
			'reset_key'  => $key,
			'user_login' => $user_data->user_login,
		)
	);
	$message = ob_get_contents();
	ob_end_clean();

	// send mail.
	try {
		$mail_stat = wp_mail( $to, $subject, $message, $headers );
	} catch ( Exception $e ) {
		cdfs_add_notice( $e->getMessage(), 'error' );
		return false;
	}
	do_action( 'cdfs_reset_password_notification', $user_data->user_login, $key );
	return true;
}

/**
 * Lost password page handling.
 */
function cdfs_lost_user_password() {
	if ( ! empty( $_GET['show-password-reset-form'] ) ) {
		if ( isset( $_COOKIE[ 'cdfs-resetpass-' . COOKIEHASH ] ) && 0 < strpos( $_COOKIE[ 'cdfs-resetpass-' . COOKIEHASH ], ':' ) ) {
			list( $rp_login, $rp_key ) = array_map( 'cdfs_clean', explode( ':', wp_unslash( $_COOKIE[ 'cdfs-resetpass-' . COOKIEHASH ] ), 2 ) );
			$user                      = cdfs_check_password_reset_key( $rp_key, $rp_login );

			// reset key / login is correct, display reset password form with hidden key / login values.
			if ( is_object( $user ) ) {
				return cdfs_get_template(
					'my-user-account/form-reset-user-password.php',
					array(
						'key'   => $rp_key,
						'login' => $rp_login,
					)
				);
			} else {
				cdfs_set_reset_password_cookie();
			}
		}
	}

	// Show lost password form by default.
	cdfs_get_template( 'my-user-account/form-forgot-password.php' );
}

/**
 * Retrieves a user row based on password reset key and login.
 *
 * @uses $wpdb WordPress Database object
 *
 * @param string $key Hash to validate sending user's password.
 * @param string $login The user login.
 * @return WP_User|bool User's database row on success, false for invalid keys
 */
function cdfs_check_password_reset_key( $key, $login ) {
	// Check for the password reset key.
	// Get user data or an error message in case of invalid or expired key.
	$user = check_password_reset_key( $key, $login );

	if ( is_wp_error( $user ) ) {
		cdfs_add_notice( $user->get_error_message(), 'error' );
		return false;
	}
	return $user;
}

/**
 * Set or unset the cookie.
 *
 * @param string $value for reset password.
 */
function cdfs_set_reset_password_cookie( $value = '' ) {
	$rp_cookie = 'cdfs-resetpass-' . COOKIEHASH;
	$rp_path   = current( explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );

	if ( $value ) {
		setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
	} else {
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
	}
}

/**
 * Handles resetting the user's password.
 *
 * @param object $user The user.
 * @param string $new_pass New password for the user in plaintext.
 */
function cdfs_set_user_password( $user, $new_pass ) {
	wp_set_password( $new_pass, $user->ID );
	cdfs_set_reset_password_cookie();
	wp_password_change_notification( $user ); // notify admin regarding password change of user.
}
