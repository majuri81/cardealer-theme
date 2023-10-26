<?php
/**
 * CDFS form haneler.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle frontend forms.
 *
 * @author PotenzaGlobalSolutions
 */
class CDFS_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'update_account_details' ) );
		add_action( 'template_redirect', array( __CLASS__, 'add_dealer_review' ) );
		add_action( 'template_redirect', array( __CLASS__, 'update_user_settings' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_reset_password_link' ) );

		add_action( 'wp_loaded', array( __CLASS__, 'process_user_login' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_user_registration' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_user_forgot_password' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_user_password_reset' ), 20 );

	}

	/**
	 * Remove key and cdfs_login from query string, set cookie, and redirect to account page to show the form.
	 */
	public static function redirect_reset_password_link() {
		if ( cdfs_is_user_account_page() && ! empty( $_GET['key'] ) && ! empty( $_GET['cdfs_login'] ) ) {
			$value = sprintf( '%s:%s', wp_unslash( $_GET['cdfs_login'] ), wp_unslash( $_GET['key'] ) );

			cdfs_set_reset_password_cookie( $value );
			wp_safe_redirect( add_query_arg( 'show-password-reset-form', 'true', cdfs_get_lost_password_url() ) );
			exit;
		}
	}

	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function update_account_details() {
		global $car_dealer_options;

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if (
			( empty( $_POST['action'] ) || 'update_account_details' !== $_POST['action'] )
			|| empty( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'update_account_details' )
		) {
			return;
		}

		$demo_mode = isset( $car_dealer_options['demo_mode'] ) ? (bool) $car_dealer_options['demo_mode'] : '';
		if ( $demo_mode ) {
			cdfs_add_notice( esc_html__( "The site is currently in demo mode, you can't edit profile.", 'cdfs-addon' ), 'error' );
			$rediect_url = cdfs_get_cardealer_dashboard_endpoint_url( 'profile' );
			wp_safe_redirect( $rediect_url );
			exit;
		}

		if ( ! cdfs_validate_captcha() ) { // captcha serverd side validation.
			return;
		}

		nocache_headers();

		$errors = new WP_Error();
		$user   = new stdClass();

		$current_user = wp_get_current_user();
		$user->ID     = ( isset( $current_user->ID ) ? (int) $current_user->ID : 0 );

		if ( $user->ID <= 0 ) {
			return;
		}

		// Personal Details.
		$account_first_name = ! empty( $_POST['account_first_name'] ) ? cdfs_clean( $_POST['account_first_name'] ) : '';
		$account_last_name  = ! empty( $_POST['account_last_name'] ) ? cdfs_clean( $_POST['account_last_name'] ) : '';
		$account_email      = ! empty( $_POST['account_email'] ) ? cdfs_clean( $_POST['account_email'] ) : '';
		$account_mobile     = ! empty( $_POST['account_mobile'] ) ? cdfs_clean( $_POST['account_mobile'] ) : '';
		$account_whatsapp   = ! empty( $_POST['account_whatsapp'] ) ? cdfs_clean( $_POST['account_whatsapp'] ) : '';
		$dealer_overview    = ! empty( $_POST['dealer_overview'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dealer_overview'] ) ) : '';
		$dealer_location    = ! empty( $_POST['dealer_location'] ) ? (array) $_POST['dealer_location'] : array();
		$dealer_location    = array_map( 'esc_attr', $dealer_location );
		$y_tunnus           = ! empty( $_POST['account_y_tunnus'] ) ? cdfs_clean( $_POST['account_y_tunnus'] ) : '';
		$company_name       = ! empty( $_POST['account_company_name'] ) ? cdfs_clean( $_POST['account_company_name'] ) : '';
		$e_invoice_number = ! empty( $_POST['account_e_invoice_number'] ) ? cdfs_clean( $_POST['account_e_invoice_number'] ) : '';

		// Login Details.
		$pass_cur  = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : '';
		$pass1     = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : '';
		$pass2     = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : '';
		$save_pass = true;

		$user->first_name = $account_first_name;
		$user->last_name  = $account_last_name;
		$user->user_registration_y_tunnus  = $y_tunnus;

		$user->display_name  = implode( ' ', array_filter( array( $account_first_name, $account_last_name ) ) );

		// Handle required fields.
		$required_fields = apply_filters(
			'cdfs_update_account_details_required_fields',
			array(
				'account_first_name' => esc_html__( 'First name', 'cdfs-addon' ),
				'account_last_name'  => esc_html__( 'Last name', 'cdfs-addon' ),
				'account_email'      => esc_html__( 'Email address', 'cdfs-addon' ),
			)
		);

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				/* translators: %s: field name */
				cdfs_add_notice( sprintf( __( '%s is a required field.', 'cdfs-addon' ), '<strong>' . esc_html( $field_name ) . '</strong>' ), 'error' );
			}
		}

		if ( $account_email ) {
			$account_email = sanitize_email( $account_email );
			if ( ! is_email( $account_email ) ) {
				cdfs_add_notice( esc_html__( 'Please provide a valid email address.', 'cdfs-addon' ), 'error' );
			} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
				cdfs_add_notice( esc_html__( 'This email address is already registered.', 'cdfs-addon' ), 'error' );
			}
			$user->user_email = $account_email;
		}

		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			cdfs_add_notice( esc_html__( 'Please fill out all password fields.', 'cdfs-addon' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			cdfs_add_notice( esc_html__( 'Please enter your current password.', 'cdfs-addon' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			cdfs_add_notice( esc_html__( 'Please re-enter your password.', 'cdfs-addon' ), 'error' );
			$save_pass = false;
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			cdfs_add_notice( esc_html__( 'New passwords do not match.', 'cdfs-addon' ), 'error' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			cdfs_add_notice( esc_html__( 'Your current password is incorrect.', 'cdfs-addon' ), 'error' );
			$save_pass = false;
		}

		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}

		if ( $errors->get_error_messages() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				cdfs_add_notice( $error, 'error' );
			}
		}

        if ($y_tunnus) {
            if (strlen($y_tunnus) != 10 || is_numeric(substr($y_tunnus, 0, 1)) || is_numeric(substr($y_tunnus, 1, 2))) {
                cdfs_add_notice( esc_html__( 'Invalid VAT ID.', 'cdfs-addon' ), 'error' );
            }
        }
        
        if ($account_mobile) {
            if (!preg_match("/^\+?[0-9]{1,4}-?[0-9]+$/", str_replace(' ', '', $account_mobile))) {
                cdfs_add_notice( esc_html__( 'Invalid Phone Number.', 'cdfs-addon' ), 'error' );
            }
        }

		$profile_images_update = cdfs_user_profile_images_handler();

		if ( cdfs_notice_count( 'error' ) === 0 ) {

			wp_update_user( $user );

			// Update mobile
			update_user_meta( $user->ID, 'user_registration_user_phone', $account_mobile );
			update_user_meta( $user->ID, 'account_whatsapp', $account_whatsapp );
			update_user_meta( $user->ID, 'dealer_overview', $dealer_overview );
			update_user_meta( $user->ID, 'dealer_location', $dealer_location );
			update_user_meta( $user->ID, 'user_registration_y_tunnus', $y_tunnus );
			update_user_meta( $user->ID, 'user_registration_company_name', $company_name );
			update_user_meta( $user->ID, 'user_registration_e_invoice_number', $e_invoice_number );
            
			// Update user meta with image url and path.
			foreach ( $profile_images_update as $image_field_name => $profile_image_update_data ) {
				foreach ( $profile_image_update_data as $meta_k => $meta_v ) {
					update_user_meta( $user->ID, $meta_k, $meta_v );
				}
			}

			// Update additional user fields.
			if ( function_exists( 'cdfs_user_register_additional_field' ) ) {
				$additional_field = cdfs_user_register_additional_field();
				if ( $additional_field && is_array( $additional_field ) ) {
					foreach ( $additional_field as $field_key => $field_val ) {
						if ( isset( $_POST[$field_key] ) ) {
							update_user_meta( $user->ID, $field_key, cdfs_clean( $_POST[$field_key] ) );	
						}
					}
				}
			}

			// Social Profiles.
			$social_profiles = apply_filters( 'cardealer_user_profile_meta_fields_social_profiles', array() );
			foreach ( $social_profiles as $profile_id => $profile ) {
				$profile_val = ! empty( $_POST[ $profile_id ] ) ? cdfs_clean( $_POST[ $profile_id ] ) : '';
				update_user_meta( $user->ID, $profile_id, $profile_val );
			}


			cdfs_add_notice( esc_html__( 'Account details changed successfully.', 'cdfs-addon' ) );

			do_action( 'cdfs_update_account_details', $user->ID, $_POST );
			do_action( 'cardealer-dashboard/update-profile', $_POST, $current_user );
		}

		$rediect_url = cdfs_get_cardealer_dashboard_endpoint_url( 'profile' );
		wp_safe_redirect( $rediect_url );
		exit;
	}

	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function add_dealer_review() {
		global $car_dealer_options;

		$cdfs_review = new CDFS_Review();

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if (
			( empty( $_POST['action'] ) || 'add_dealer_review' !== $_POST['action'] )
			|| empty( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'add_dealer_review' )
		) {
			return;
		}
		
		$added_to = isset( $_POST['added_to'] ) ? sanitize_text_field( wp_unslash( $_POST['added_to'] ) ) : '';
		if ( $added_to ) {
			$user_id = $added_to;
		} else {
			$user_id = get_query_var( 'author' );
		}

		$demo_mode = false;
		if ( isset( $car_dealer_options['demo_mode'] ) ) {
			$demo_mode = (bool) $car_dealer_options['demo_mode'];
			if ( $demo_mode ) {
				$error = true;
				cdfs_add_notice( esc_html__( "The site is currently in demo mode, you can't submit the review.", 'cdfs-addon' ), 'error' );				
				$rediect_url = get_author_posts_url( $user_id ) . '?profile-tab=write-review';
				wp_safe_redirect( $rediect_url );
				exit;
			}
		}

		$error            = false;
		$review_title     = isset( $_POST['review_title'] ) ? sanitize_text_field( wp_unslash( $_POST['review_title'] ) ) : '';
		$review_content   = isset( $_POST['review_content'] ) ? sanitize_text_field( wp_unslash( $_POST['review_content'] ) ) : '';
		$recommend_dealer = isset( $_POST['recommend_dealer'] ) ? sanitize_text_field( wp_unslash( $_POST['recommend_dealer'] ) ) : 'yes';
		$dealer_rating_1  = isset( $_POST['dealer_rating_1'] ) ? sanitize_text_field( wp_unslash( $_POST['dealer_rating_1'] ) ) : '';
		$dealer_rating_2  = isset( $_POST['dealer_rating_2'] ) ? sanitize_text_field( wp_unslash( $_POST['dealer_rating_2'] ) ) : '';
		$dealer_rating_3  = isset( $_POST['dealer_rating_3'] ) ? sanitize_text_field( wp_unslash( $_POST['dealer_rating_3'] ) ) : '';

		if ( ! is_user_logged_in() ) {
			$error = true;
			cdfs_add_notice( esc_html__( 'Please login to the review.', 'cdfs-addon' ), 'error' );
		}

		if ( ! $review_title ) {
			$error = true;
			cdfs_add_notice( esc_html__( 'Review title field is required.', 'cdfs-addon' ), 'error' );
		}

		if ( ! $review_content ) {
			$error = true;
			cdfs_add_notice( esc_html__( 'Please add review content.', 'cdfs-addon' ), 'error' );
		}

		if ( ! $recommend_dealer ) {
			$error = true;
			cdfs_add_notice( esc_html__( 'Please selecr recommendation.', 'cdfs-addon' ), 'error' );
		}

		if ( ! $dealer_rating_1 || ! $dealer_rating_2 || ! $dealer_rating_3 ) {
			$error = true;
			cdfs_add_notice( esc_html__( 'Please select all the ratings.', 'cdfs-addon' ), 'error' );
		}

		if ( ! $error ) {
			$review_id = $cdfs_review->get_submitted_user_review( get_current_user_id(), $added_to );
			if ( $review_id ) {
				$review_data = array(
					'ID'           => $review_id,
					'post_author'  => get_current_user_id(),
					'post_content' => $review_content,
					'post_title'   => $review_title,
					'post_status'  => 'pending',
					'post_type'    => 'dealer_review',
				);
				$new_review_id = wp_update_post( wp_slash( $review_data ), true );

			} else {
				$review_data = array(
					'post_author'           => get_current_user_id(),
					'post_content'          => $review_content,
					'post_title'            => $review_title,
					'post_status'           => 'pending',
					'post_type'             => 'dealer_review',
				);
				$new_review_id = wp_insert_post( wp_slash( $review_data ), true );
			}

			if ( 0 !== $new_review_id && ! is_wp_error( $new_review_id ) ) {

				$avg_rating = ( $dealer_rating_1 + $dealer_rating_2 + $dealer_rating_3 ) / 3;

				update_post_meta( $new_review_id, 'added_by', get_current_user_id() );
				update_post_meta( $new_review_id, 'added_to', $added_to );
				update_post_meta( $new_review_id, 'dealer_rating_1', $dealer_rating_1 );
				update_post_meta( $new_review_id, 'dealer_rating_2', $dealer_rating_2 );
				update_post_meta( $new_review_id, 'dealer_rating_3', $dealer_rating_3 );
				update_post_meta( $new_review_id, 'avg_rating', round( $avg_rating, 1 ) );
				update_post_meta( $new_review_id, 'recommended', $recommend_dealer );
				cdfs_add_notice( esc_html__( 'Review added successfully.', 'cdfs-addon' ) );
			} else {
				cdfs_add_notice( esc_html__( 'Error in adding Review.', 'cdfs-addon' ), 'error' );
			}
		}

		$rediect_url = get_author_posts_url( $user_id ) . '?profile-tab=write-review';
		wp_safe_redirect( $rediect_url );
		exit;
	}

	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function update_user_settings() {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if (
			( empty( $_POST['action'] ) || 'update_user_settings' !== $_POST['action'] )
			|| empty( $_POST['_wpnonce'] )
			|| ! wp_verify_nonce( $_POST['_wpnonce'], 'update_user_settings' )
		) {
			return;
		}

		if ( ! cdfs_validate_captcha() ) { // captcha serverd side validation.
			return;
		}

		nocache_headers();

		$user    = wp_get_current_user();
		$user_id = ( isset( $user->ID ) ? (int) $user->ID : 0 );
		$meta    = array();

		if ( $user_id <= 0 ) {
			return;
		}

		// Personal Details.
		$meta['cdfs_show_email']    = isset( $_POST['cdfs_show_email'] ) ? true : false;
		$meta['cdfs_show_phone']    = isset( $_POST['cdfs_show_phone'] ) ? true : false;
		$meta['cdfs_show_whatsapp'] = isset( $_POST['cdfs_show_whatsapp'] ) ? true : false;

		$meta = apply_filters( 'cardealer-dashboard/user-settings/user-meta', $meta, $user, $_POST );

		// Update user meta.
		foreach ( $meta as $key => $value ) {
			update_user_meta( $user_id, $key, $value );
		}

		cdfs_add_notice( esc_html__( 'Settings updated successfully.', 'cdfs-addon' ) );

		do_action( 'cardealer-dashboard/user-settings/after-settings-updated', $_POST, $user );

        
		$rediect_url = cdfs_get_cardealer_dashboard_endpoint_url( 'settings' );
		wp_safe_redirect( $rediect_url );
		exit;
	}

	/**
	 * Process the login form.
	 */
	public static function process_user_login() {
		$nonce_value = isset( $_POST['cdfs-login-nonce'] ) ? $_POST['cdfs-login-nonce'] : '';

		if ( ( ! empty( $_POST['login'] ) ) && wp_verify_nonce( $nonce_value, 'cdfs-login' ) ) {
			if ( ! cdfs_validate_captcha() ) { // captcha server side validation.
				return;
			}
			try {
				$creds = array(
					'user_password' => $_POST['password'],
					'remember'      => isset( $_POST['rememberme'] ),
				);

				$username         = trim( $_POST['username'] );
				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'cdfs_process_user_login_errors', $validation_error, $_POST['username'], $_POST['password'] );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $username ) ) {
					throw new Exception( '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . esc_html__( 'Username is required.', 'cdfs-addon' ) );
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
						add_user_to_blog( get_current_blog_id(), $user_data->ID, $userrole );
					}
				}

				// Perform the login.
				$user     = wp_signon( apply_filters( 'cdfs_login_credentials', $creds ), is_ssl() );
				$redirect = '';

				if ( is_wp_error( $user ) ) {
					$message = $user->get_error_message();
					$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $username ) . '</strong>', $message );
					throw new Exception( $message );
				} else {
					// get user info.
					$activation_error = '';
					$userinfo         = get_user_meta( $user->ID, 'cdfs_user_status', true );
					if (
						! in_array( 'car_dealer', $user->roles )
						&& ! in_array( 'customer', $user->roles )
						&& ! in_array( 'subscriber', $user->roles )
						&& ! in_array( 'administrator', $user->roles )
					) { // If user login with roles other than car_dealer.
						wp_logout();
						$redirect = add_query_arg( 'invalid-role', '1', cdfs_get_page_permalink( 'dealer_login' ) );
					} elseif ( ! empty( $userinfo ) && 'pending' === $userinfo ) { // Check user status.
						wp_logout();

						$activate_method = cdfs_user_activation_method();
						if ( 'mail' === $activate_method ) {
							$activation_error = '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . esc_html__( 'Please activate your account with the activation link sent to your registered mail account.', 'cdfs-addon' );
						} elseif ( 'admin' === $activate_method ) {
							$activation_error = '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . esc_html__( 'Your account is waiting for admin approval. Once your account is approved by the admin, you will be able to manage vehicles.', 'cdfs-addon' );
						}

						if ( $activation_error ) {
							throw new Exception( apply_filters( 'cdfs_user_activation_err_msg', $activation_error ) );
						}
					} elseif ( isset( $_POST['redirect_to'] ) && ! empty( $_POST['redirect_to'] ) ) {
						$redirect = sanitize_text_field( wp_unslash( $_POST['redirect_to'] ) );
					} elseif ( cdfs_get_reference_link() ) {
						$redirect = cdfs_get_reference_link();
					} else {
						$redirect = cdfs_get_page_permalink( 'dealer_login' );
					}

					if ( $redirect ) {
						wp_redirect( wp_validate_redirect( apply_filters( 'cdfs_login_redirect', $redirect, $user ), cdfs_get_page_permalink( 'dealer_login' ) ) );
						exit;
					}
				}
			} catch ( Exception $e ) {
				cdfs_add_notice( $e->getMessage(), 'error' );
			}
		}
	}

	/**
	 * Handle lost password form.
	 */
	public static function process_user_forgot_password() {
		if ( isset( $_POST['cdfs_action'] ) && isset( $_POST['user_login'] ) && isset( $_POST['cdhl_nonce'] ) && wp_verify_nonce( $_POST['cdhl_nonce'], 'cdhl-lost-psw' ) ) {

			// generate password reset link.
			$success = cdfs_send_password_reset_link();

			// If successful, redirect to user account login page with query arg set.
			if ( $success ) {
				cdfs_add_notice( esc_html__( 'Password reset link is successfully sent to your email address, please check.', 'cdfs-addon' ), 'success' );
				wp_redirect( add_query_arg( 'password-reset-link-sent', 'true', cdfs_get_page_permalink( 'dealer_login' ) ) );
				exit;
			}
		}
	}

	/**
	 * Handle reset password form.
	 */
	public static function process_user_password_reset() {
		$reset_post_fields = array( 'password_1', 'password_2', 'reset_psw_key', 'reset_psw_login', 'cdfs_nonce' );

		foreach ( $reset_post_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				return;
			}
			$reset_post_fields[ $field ] = $_POST[ $field ];
		}

		if ( ! wp_verify_nonce( $reset_post_fields['cdfs_nonce'], 'cdfs-reset-psw' ) ) {
			return;
		}

		if ( ! cdfs_validate_captcha() ) { // captcha serverd side validation.
			return;
		}

		$user = cdfs_check_password_reset_key( $reset_post_fields['reset_psw_key'], $reset_post_fields['reset_psw_login'] );

		if ( $user instanceof WP_User ) {
			if ( empty( $reset_post_fields['password_1'] ) ) {
				cdfs_add_notice( esc_html__( 'Please enter your password.', 'cdfs-addon' ), 'error' );
			}

			if ( $reset_post_fields['password_1'] !== $reset_post_fields['password_2'] ) {
				cdfs_add_notice( esc_html__( 'Passwords do not match.', 'cdfs-addon' ), 'error' );
			}

			$errors = new WP_Error();

			do_action( 'validate_password_reset', $errors, $user );

			if ( is_wp_error( $errors ) && $errors->get_error_messages() ) {
				foreach ( $errors->get_error_messages() as $error ) {
					cdfs_add_notice( $error, 'error' );
				}
			}

			if ( 0 === cdfs_notice_count( 'error' ) ) {
				cdfs_set_user_password( $user, $reset_post_fields['password_1'] );

				wp_redirect( add_query_arg( 'password-reset-done', 'true', cdfs_get_page_permalink( 'dealer_login' ) ) );
				exit;
			}
		}
	}

	/**
	 * Process the registration form.
	 */
	public static function process_user_registration() {
		global $car_dealer_options;

		$nonce_value = isset( $_POST['cdfs-register-nonce'] ) ? $_POST['cdfs-register-nonce'] : '';

		if ( ! empty( $_POST['register'] ) && wp_verify_nonce( $nonce_value, 'cdfs-register' ) ) {
			$username      = wp_unslash( $_POST['username'] );
			$password      = wp_unslash( $_POST['password'] );
			$email         = wp_unslash( $_POST['email'] );
			$reg_user_type = wp_unslash( $_POST['reg_user_type'] );
			$user_role     = cdfs_get_usertype_userrole( cdfs_clean( $reg_user_type ) );
            $y_tunnus      = wp_unslash( $_POST['VAT ID'] );
			if ( ! cdfs_validate_captcha() ) { // captcha serverd side validation.
				return;
			}

			$demo_mode = isset( $car_dealer_options['demo_mode'] ) ? (bool) $car_dealer_options['demo_mode'] : '';
			if ( $demo_mode ) {
				cdfs_add_notice( esc_html__( "The site is currently in demo mode, registration is disabled.", 'cdfs-addon' ), 'error' );
			} else {
				try {
					$validation_error = new WP_Error();
					$validation_error = apply_filters( 'cdfs_process_user_registration_errors', $validation_error, $username, $password, $email );

					if ( $validation_error->get_error_code() ) {
						throw new Exception( $validation_error->get_error_message() );
					}

					$new_user = cdfs_create_user( sanitize_email( $email ), cdfs_clean( $username ), $password, $user_role );

					if ( is_wp_error( $new_user ) ) {
						throw new Exception( $new_user->get_error_message() );
					} else {
						if ( 'mail' === $activate_method ) {
						$activate_method = cdfs_user_activation_method();
							cdfs_add_notice( esc_html__( 'Account successfully created. Please activate your account with the activation link sent to your registered mail account.', 'cdfs-addon' ), 'success' );
						} elseif ( 'admin' === $activate_method ) {
							cdfs_add_notice( esc_html__( 'Account successfully created. Your account is waiting for admin approval.', 'cdfs-addon' ), 'success' );
						} else {
							cdfs_add_notice( esc_html__( 'Account successfully created.', 'cdfs-addon' ), 'success' );
						}
					}
				} catch ( Exception $e ) {
					cdfs_add_notice( '<strong>' . esc_html__( 'Error:', 'cdfs-addon' ) . '</strong> ' . $e->getMessage(), 'error' );
				}
			}
		}
	}

}

CDFS_Form_Handler::init();
