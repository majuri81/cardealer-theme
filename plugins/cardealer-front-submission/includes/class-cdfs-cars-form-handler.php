<?php
/**
 * CDFS car form handler.
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
class CDFS_Cars_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		// add_action( 'wp_loaded', array( __CLASS__, 'process_car_save' ), 20 );.
	}

	/**
	 * Get logged in user id.
	 */
	public static function get_user_id() {
		if ( is_user_logged_in() ) {
			return get_current_user_id();
		}
		return false;
	}

	/**
	 * Save car save [ insert / update ]
	 */
	public static function process_car_save() {
		global $car_dealer_options;

		if ( isset( $car_dealer_options['demo_mode'] ) ) {
			$demo_mode = (bool) $car_dealer_options['demo_mode'];
			if ( $demo_mode ) {
				return array( 'status' => 'demo_mode' );
			}
		}

		if ( empty( $_POST['action'] ) || 'cdfs_save_car' !== $_POST['action'] ) {
			return array( 'status' => 2 );
		}

		$user = self::get_user_id();
		if ( ! $user ) { // return if not logged in.
			return array( 'status' => 'login' );
		}

		if ( isset( $_POST['cdfs-car-form-nonce-field'] ) ) {
			if ( empty( $_POST['cdfs-car-form-nonce-field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cdfs-car-form-nonce-field'] ) ), 'cdfs-car-form' ) ) {
				return array( 'status' => 'nonce' );
			}
		}

		nocache_headers();
		if ( ! cdfs_validate_captcha( false ) ) { // captcha serverd side validation.
			return array( 'status' => 3 );
		}

		$post_data   = $_POST;
		$car_data    = ( isset( $_POST['car_data'] ) ) ? wp_unslash( $_POST['car_data'] ): null;
		$vehicle_cat = ( isset( $_POST['vehicle_cat'] ) ) ? cdfs_clean( $_POST['vehicle_cat'] ): null;
		$taxonomies  = get_object_taxonomies( 'cars' );
		$submit_type = ( isset( $_POST['submit_type'] ) && $_POST['submit_type'] ) ? $_POST['submit_type'] : 'free';

		// Validate insertion of car if insert vehicle action is called.
		$validateinsert = self::validate_car_insert( $post_data );

		// Somthing went wrong.
		if ( 0 == $validateinsert['status'] ) {
			return array(
				'status'      => 2,
				'submit_type' => $submit_type,
			);
		// Car insert limit exceeded.
		} elseif ( 2 == $validateinsert['status'] ) {
			return array(
				'status'      => 5,
				'limit'       => $validateinsert['limit'],
				'submit_type' => $submit_type,
			);
		// Car image upload limit exceeded.
		} elseif ( 3 == $validateinsert['status'] ) {
			return array(
				'status'      => 'image_limit',
				'limit'       => $validateinsert['limit'],
				'submit_type' => $submit_type,
			);
		}

		/**
		 * Filters the array of vehicle required fields.
		 *
		 * @since 1.0
		 * @param array      $required_fields   Array of fields to put in required fields in add car form(server side validation).
		 * @visible           true
		 */
		$required_fields = apply_filters(
			'cdfs_car_required_fields',
			array(
				'year'          => esc_html__( 'Year', 'cdfs-addon' ),
				'make'          => esc_html__( 'Make', 'cdfs-addon' ),
				'model'         => esc_html__( 'Model', 'cdfs-addon' ),
				'regular_price' => esc_html__( 'Regular Price', 'cdfs-addon' ),
				'transmission' => esc_html__( 'Transmission', 'cdfs-addon' ),
				'fuel_type' => esc_html__( 'Power Type', 'cdfs-addon' ),
				'verokanta' => esc_html__( 'Tax Rate', 'cdfs-addon' )

			)
		);

		// check required fields.
		$val_err_fields = array();
		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $car_data[ $field_key ] ) ) {
				$val_err_fields[] = $field_name;
			}
		}
		if ( ! empty( $val_err_fields ) ) {
			return array(
				'status'      => 4,
				'err_fields'  => $val_err_fields,
				'submit_type' => $submit_type,
			);
		}

		if ( ( isset( $car_data[ 'regular_price' ] ) && $car_data[ 'regular_price' ] && isset( $car_data[ 'sale_price' ] ) && $car_data[ 'sale_price' ] ) && ( intval( $car_data[ 'sale_price' ] ) >= intval( $car_data[ 'regular_price' ] ) ) ) {
			return array(
				'status'      => 'invalid_sale_price',
				'submit_type' => $submit_type,
			);
		}
		
		if (strlen($car_data[ 'registration-date' ] ) > 0) {
		    $dateFormats = [ 'd.m.Y', 'd-m-Y', 'd/m/Y', 'd m Y' ];
		    $isDateValid = false;
		    foreach ($dateFormats as $dateFormat) {
		        if ( DateTime::createFromFormat( $dateFormat, $car_data[ 'registration-date' ] ) !== false ) {
		            $isDateValid = true;
        	    }
		    }
		    
		    if (!$isDateValid) {
		        return array(
    				'status'      => 10,
    				'submit_type' => $submit_type,
    			);
		    }
		}

		if ( cdfs_notice_count( 'error' ) !== 0 ) {
			return array(
				'status'      => 2,
				'submit_type' => $submit_type,
			);
		}

		// Attributes.
		if ( ! empty( $car_data ) ) {

			if ( isset( $car_dealer_options['cdfs_allow_add_attribute'] ) ) {

				$cdfs_allow_add_attribute = (bool) $car_dealer_options['cdfs_allow_add_attribute'];
				if ( ! $cdfs_allow_add_attribute ) {

					$notallow_add_attribute = array();
					$allow_add_attribute    = array(
						'car_year',
						'car_make',
						'car_model',
						'car_body_style',
						'car_transmission',
						'car_condition',
						'car_drivetrain',
						'car_engine',
						'car_exterior_color',
						'car_interior_color',
						'car_fuel_type',
						'car_trim',
					);

					foreach( $car_data as $car_field_id => $car_field_val ) 	{
						if ( $car_field_val ) {
							if ( in_array( 'car_' . $car_field_id, $allow_add_attribute ) ) {
								$term_id = term_exists( $car_field_val, 'car_' . $car_field_id );
								if ( empty( $term_id ) ) {
									$new_tax_obj = get_taxonomy( 'car_' . $car_field_id );
									if ( isset( $new_tax_obj->labels->singular_name ) ) {
										$notallow_add_attribute[] = $new_tax_obj->labels->singular_name;
									}
								}
							} else if ( in_array( $car_field_id, $taxonomies ) ) {
								$term_id = term_exists( $car_field_val, $car_field_id );
								if ( empty( $term_id ) ) {
									$new_tax_obj = get_taxonomy( $car_field_id );
									if ( isset( $new_tax_obj->labels->singular_name ) ) {
										$notallow_add_attribute[] = $new_tax_obj->labels->singular_name;
									}
								}
							}
						}
					}

					if ( ! empty( $notallow_add_attribute ) ) {
						return array(
							'status'      => 8,
							'err_fields'  => $notallow_add_attribute,
							'submit_type' => $submit_type,
						);
					}
				}
			}

			// Check if model related to make
			if ( isset( $car_dealer_options['cdfs_make_model_relation'] ) ) {
				$cdfs_make_model_relation = filter_var( $car_dealer_options['cdfs_make_model_relation'], FILTER_VALIDATE_BOOLEAN );
				if ( $cdfs_make_model_relation ) {

					$make_term_id  = '';
					$model_term_id = '';
					$model         = cdfs_clean( $car_data['model'] );
					$make          = cdfs_clean( $car_data['make'] );

					$make_term_exist  = term_exists( $make, 'car_make' );
					$model_term_exist = term_exists( $model, 'car_model' );

					if ( ! empty( $make_term_exist ) ) {
						if ( is_array( $make_term_exist ) ) {
							$make_term_id = isset( $make_term_exist['term_id'] ) ? $make_term_exist['term_id'] : '';
						} else {
							$make_term_id = $make_term_exist;
						}
					}

					if ( ! empty( $model_term_exist ) ) {
						if ( is_array( $model_term_exist ) ) {
							$model_term_id = isset( $model_term_exist['term_id'] ) ? $model_term_exist['term_id'] : '';
						} else {
							$model_term_id = $model_term_exist;
						}
					}

					if ( $make_term_id && $model_term_id ) {
						if ( ! metadata_exists( 'term', $model_term_id, 'parent_make' ) ) {
							return array(
								'status'      => 9,
								'submit_type' => $submit_type,
							);
						} else {
							$meta_value = get_term_meta( $model_term_id, 'parent_make', true );
							if ( (int) $meta_value !== (int) $make_term_id ) {
								return array(
									'status'      => 9,
									'submit_type' => $submit_type,
								);
							}
						}
					}
				}
			}

			$post_status = cdfs_auto_publish_status();

			if ( isset( $car_data['car_title'] ) && $car_data['car_title'] ) {
				$car_title  = cdfs_clean( $car_data['car_title'] ); // car title.
			} else {
				$car_title  = cdfs_clean( $car_data['year'] ) . ' ' . cdfs_clean( $car_data['make'] ) . ' ' . cdfs_clean( $car_data['model'] ); // car title.
			}

			// Deprecated filter.
			$car_title  = apply_filters_deprecated( 'cdfs_car_title', array( $car_title, $car_data ), '5.1.0', 'cdfs_add_car_vehicle_title' );
			$car_title  = apply_filters( 'cdfs_add_car_vehicle_title', $car_title, $car_data );

			$car_excerpt = isset( $_POST['car_excerpt'] ) ? $_POST['car_excerpt'] : '';
			if ( isset( $_POST['cdfs_action_car_id'] ) && ! empty( $_POST['cdfs_action_car_id'] ) ) { // Update car.
				$status = 7;
				$car_id = wp_update_post(
					array(
						'ID'           => cdfs_clean( $_POST['cdfs_action_car_id'] ),
						'post_title'   => $car_title,
						'post_excerpt' => $car_excerpt,
						'post_status'  => $post_status,
					)
				);
			} else { // Insert car.
				$status = 1;
				$car_id = wp_insert_post(
					array(
						'post_status'  => $post_status,
						'post_type'    => 'cars',
						'post_title'   => $car_title,
						'post_excerpt' => $car_excerpt,
					)
				);
			}

			// checkbox fields i.e. array('taxonomy'=> 'fieldname').
			$checkbox_fields = apply_filters(
				'cdfs_checkbox_fields',
				array(
					'car_features_options'      => 'features_and_options',
				)
			);

			$editor_fields = apply_filters( 'cdfs_editor_fields', array( 'vehicle_overview', 'technical_specifications', 'general_information' ) );
			if ( ! is_wp_error( $car_id ) ) {

				$additional_tax_arr = array();
				foreach ( $taxonomies as $new_tax ) {
					$new_tax_obj = get_taxonomy( $new_tax );
					if ( 'car_features_options' !== $new_tax ) {
						if( isset($new_tax_obj->include_in_filters) && $new_tax_obj->include_in_filters == true ) {
							$additional_tax_arr[] = $new_tax;
						}
					}
				}

				update_post_meta( $car_id, 'cdfs_listing_type', $submit_type );

				// Add plan ID for car.
				if ( isset( $_POST['subscription_plan'] ) && ! empty( $_POST['subscription_plan'] ) ) {
					$plan = cdfs_clean( $_POST['subscription_plan'] );
					update_post_meta( $car_id, 'cdfs_subscription_id', $plan );
				}

				if ( 'listing_payment' === $submit_type ) {
					$listing_price    = cdfs_get_item_listing_price();
					$listing_duration = cdfs_get_item_listing_duration();

					$now              = current_time( 'mysql' );
					$expiry           = date( 'Y-m-d H:i:s', strtotime( $now ) + ( 86400 * $listing_duration ) );
					$expiry_timestamp = strtotime( $now ) + ( 86400 * $listing_duration );

					update_post_meta( $car_id, 'cdfs_listing_duration', $listing_duration );
					update_post_meta( $car_id, '_price', $listing_price );
					// update_post_meta( $car_id, 'cdfs_item_listing_expiry', $expiry );
					update_post_meta( $car_id, 'cdfs_item_listing_expiry_timestamp', $expiry_timestamp );
				} else {
					/*
					// Add plan ID for car.
					if ( isset( $_POST['subscription_plan'] ) && ! empty( $_POST['subscription_plan'] ) ) {
						$plan = cdfs_clean( $_POST['subscription_plan'] );
						update_post_meta( $car_id, 'cdfs_subscription_id', $plan );
					}
					*/
				}

				// enter empty data for check box fields if check box fields are not set.
				foreach ( $checkbox_fields as $tax => $c_field ) {
					if ( ! in_array( $c_field, $car_data ) ) {
						wp_set_object_terms( $car_id, array(), $tax, false );
					}
				}

				// Set vehicle category
				if ( $vehicle_cat ) {
					wp_set_object_terms( $car_id, cdfs_clean( $vehicle_cat ), 'vehicle_cat', false );
				}

				/**
				 * Filters vehicle post data submitted from front submission form.
				 *
				 * @since 1.0
				 * @param array       $car_data Array of vehicle data.
				 * @param int         $car_id   Vehicle ID.
				 * @visible           true
				 */
				$car_data = apply_filters( 'cdfs_custom_car_data', $car_data, $car_id );

				$review_stamp_limit = isset( $car_dealer_options['review_stamp_limit'] ) ? $car_dealer_options['review_stamp_limit'] : 1;

				$review_stamp_link_keys = array();
				for ( $k = 1; $k <= $review_stamp_limit; $k++ ) {
					$review_stamp_link_keys[] = 'review_stamp_link_' . $k;
				}

				foreach ( $car_data as $field => $value ) {
					if ( 'car_title' === $field ) {
						continue;
					}

					$field_taxonomy = 'car_' . $field;
					if ( in_array( $field_taxonomy, $taxonomies ) && ! in_array( $field, $checkbox_fields ) ) { // check for taxonomy fields.
						wp_set_object_terms( $car_id, cdfs_clean( $value ), $field_taxonomy, false );
					} elseif ( in_array( $field, $additional_tax_arr ) ) { // check for taxonomy fields.
						wp_set_object_terms( $car_id, cdfs_clean( $value ), $field, false );
					} elseif ( in_array( $field, $checkbox_fields ) ) { // checkbox fields.
						// Checkbox options input.
						$car_options = cdfs_clean( $value );
						if ( 'features_and_options' === $field ) {
							$field_taxonomy = 'car_features_options';

							// Code to add other options for features_and_options.
							if ( isset( $car_data['cdfs-other'] ) && ! empty( $car_data['cdfs-other'] ) && ! empty( $car_data['cdfs-other-opt'] ) ) {
								$fno_opts = explode( ',', cdfs_clean( $car_data['cdfs-other-opt'] ) );
								if ( ! empty( $fno_opts ) ) {
									foreach ( $fno_opts as $fno_opt ) {
										$car_options[] = trim( cdfs_clean( $fno_opt ) );
									}
								}
							}
						}
						wp_set_object_terms( $car_id, $car_options, $field_taxonomy, false );
					} elseif ( 'vehicle_location' === $field ) { // Variable car_location.
						if ( ! empty( $_POST['cdfs_lat'] ) && ! empty( $_POST['cdfs_lng'] ) ) {
							$location = array(
								'address' => cdfs_clean( $value ),
								'lng'     => cdfs_clean( $_POST['cdfs_lng'] ),
								'lat'     => cdfs_clean( $_POST['cdfs_lat'] ),
								'zoom'    => '10',
							);
							update_post_meta( $car_id, $field, $location );
							update_field( 'vehicle_location_address', cdfs_clean( $value ), $car_id );
							update_field( 'vehicle_location_lat', cdfs_clean( $_POST['cdfs_lat'] ), $car_id );
							update_field( 'vehicle_location_lng', cdfs_clean( $_POST['cdfs_lng'] ), $car_id );
						}
					} elseif ( in_array( $field, $review_stamp_link_keys ) ) {
						update_field( $field, $value, $car_id );
					} else {
						if ( in_array( $field, $editor_fields ) ) { // Do not sanitize text editor fields.
							update_post_meta( $car_id, $field, wp_kses_post( $value ) ); // echo '<pre>'; print_r($value);die();.
						} else {
							update_post_meta( $car_id, $field, cdfs_clean( $value ) );
						}
					}

				}

				// Set relation ship for make model
				if ( isset( $car_dealer_options['cdfs_make_model_relation'] ) ) {
					$cdfs_make_model_relation = filter_var( $car_dealer_options['cdfs_make_model_relation'], FILTER_VALIDATE_BOOLEAN );
					if ( $cdfs_make_model_relation ) {

						$car_make  = get_the_terms( $car_id, 'car_make' );
						$car_model = get_the_terms( $car_id, 'car_model' );

						if ( isset( $car_make[0]->term_id ) && isset( $car_model[0]->term_id ) ) {
							$car_make_id  = $car_make[0]->term_id;
							$car_model_id = $car_model[0]->term_id;
							add_term_meta( $car_model_id, 'parent_make' , $car_make_id );
						}
					}
				}

				/**
				 * Fires once a vehicle is added and all meta fields has been saved.
				 *
				 * @param int     $car_id  Post ID.
				 */
				do_action( 'cardealer_cars_after_save_post', $car_id );

				// Send notification mail to admin about new car add.
				if ( ! isset( $_POST['cdfs_action_car_id'] ) ) { // if new car added [ donot send notification on update car ].
					// get site details.
					$site_title  = get_bloginfo( 'name' );
					$site_email  = get_bloginfo( 'admin_email' );
					$admin       = get_user_by( 'email', $site_email );
					$dealer_data = get_user_by( 'id', $user );

					// Send email notification.
					$subject  = esc_html__( 'Uusi auto lisätty!', 'cdfs-addon' );
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
					$headers .= 'From: ' . $site_title . ' <' . $site_email . '>' . "\r\n";

					$car_data = array(
						'admin_name'   => $admin->user_login,
						'dealer_name'  => $dealer_data->user_login,
						'dealer_email' => $dealer_data->user_email,
						'mail_html'    => cdfs_get_html_mail_body( $car_id ),
					);
					// Mail to admin.
					ob_start();
					cdfs_get_template(
						'mails/mail-add-car-admin-notification.php',
						array(
							'car_data'  => $car_data,
							'site_data' => array( 'site_title' => $site_title ),
						)
					);
					$admin_message = ob_get_contents();
					ob_end_clean();

					// send mail.
					try {
						wp_mail( $site_email, $subject, $admin_message, $headers );
					} catch ( Exception $e ) {
						cdfs_add_notice( $e->getMessage(), 'error' );
						return array( 'status' => 2 );
					}
				}

				return array(
					'status'      => $status,
					'post_id'     => $car_id,
					'submit_type' => $submit_type,
				);
			} else {
				// There was an error in the car insertion.
				return array(
					'status'      => 2,
					'submit_type' => $submit_type,
				);
			}
		} else {
			return array(
				'status'      => 2,
				'submit_type' => $submit_type,
			);
		}
	}

	/**
	 * Function to upload car images and pdf
	 * return: attachment id if success and false if error
	 */
	public static function process_image_upload() {
		global $car_dealer_options;

		$sorted_img_list    = explode( ',', $_POST['file_attachments'] ); // img order.
		$car_id             = cdfs_clean( $_POST['car_id'] );
		$max_file_size      = cdfs_get_add_car_image_upload_size_limit_in_bytes();
		$max_file_size_mb   = cdfs_get_add_car_image_upload_size_limit_in_mb();
		$imgs_size_error    = '';
		$files_not_uploaded = '';

		if ( isset( $_FILES ) && ! empty( $_FILES ) ) {
			$files_uploaded = false;
			foreach ( $_FILES as $field => $images ) {
				$new_uploads        = array();
				$files_not_uploaded = array();
				$imgs_size_error    = array();

				// Allowed file types.
				switch ( $field ) {
					case 'pdf_file':
						$allowed_types = array( 'pdf' );
						break;
					default:
						$allowed_types = array( 'jpg', 'jpeg', 'png', 'gif' );
				}

				if ( is_array( $images['name'] ) ) {
					foreach ( $images['name'] as $key => $value ) {

						// Remove image from sorted array.
						$ordered_arr_key = array_search( $value, $sorted_img_list );
						if ( $ordered_arr_key ) {
							unset( $sorted_img_list[ $ordered_arr_key ] );
						}

						// Do not upload if size exceeds.
						if ( $images['size'][ $key ] > $max_file_size ) {
							$imgs_size_error[] = $images['name'][ $key ];
							continue;
						}

						$file_array    = array(
							'name'     => $images['name'][ $key ],
							'type'     => $images['type'][ $key ],
							'tmp_name' => $images['tmp_name'][ $key ],
							'error'    => $images['error'][ $key ],
							'size'     => $images['size'][ $key ],
						);
						$_FILES        = array( $field => $file_array );
						$attachment_id = cdfs_handle_attachment( $field, $car_id, $allowed_types );
						if ( $attachment_id ) {
							$new_uploads[]     = $attachment_id; // map attachment_id with image.
							$sorted_img_list[] = $attachment_id; // map attachment_id with image.
						} else {
							$files_not_uploaded[] = $images['name'][ $key ];
						}
					}

					if ( empty( $sorted_img_list ) ) {
						$sorted_img_list = null;
					}
					update_field( $field, $sorted_img_list, $car_id );
					$files_uploaded = true;
				} else { // single file.
					if ( isset( $images ) && ! empty( $images ) ) {
						$_FILES = array( $field => $images );
						foreach ( $_FILES as $field_name => $array ) {
							$attachment_id = cdfs_handle_attachment( $field_name, $car_id, $allowed_types );
							if ( $attachment_id ) {
								$files_uploaded = true;
								update_field( $field, $attachment_id, $car_id );
							} else {
								$files_not_uploaded[] = $images['name'];
							}
						}
					}
				}
			}
		} elseif ( ! empty( $sorted_img_list ) ) {// if no files uploaded then perform only img sorting update.
			update_field( 'car_images', $sorted_img_list, $car_id );
			$files_uploaded = true;
		}
		if ( true == $files_uploaded ) {
			return array(
				'status'             => true,
				'file_size_error'    => $imgs_size_error,
				'files_not_uploaded' => $files_not_uploaded,
				'file_size_limit'    => ( $max_file_size_mb ),
			);
		} else {
			return array( 'status' => false );
		}
	}

	/**
	 * Validate car insert limit and image limit
	 *
	 * Return : true (if validated) / false ( if not validated )
	 */
	public static function validate_car_insert( $post_data ) {
		global $car_dealer_options;

		$car_limit     = cdfs_get_free_cars_limit();
		$img_limit     = cdfs_get_free_cars_image_limit();
		$uploaded_imgs = isset( $_POST['car_img_cnt'] ) ? cdfs_clean( $_POST['car_img_cnt'] ) : 0;
		$sub_plan_id   = isset( $_POST['subscription_plan'] ) ? cdfs_clean( $_POST['subscription_plan'] ) : 'free';
		$cdfs_car_id   = isset( $_POST['cdfs_action_car_id'] ) ? cdfs_clean( $_POST['cdfs_action_car_id'] ) : '';

		$user = self::get_user_id();

		if ( $user ) {

			$car_limit = cdfs_get_user_subscription_available_car_limit( $sub_plan_id, $user );
			$img_limit = cdfs_get_user_car_subscription_image_limit( $sub_plan_id );

			if ( $cdfs_car_id ) {
				$subscription_id = get_post_meta( $cdfs_car_id, 'cdfs_subscription_id', true );
				if ( 'free' === $subscription_id && empty( $subscription_id ) ) {
					$subscription_id = 'free';
				}

				if ( $sub_plan_id != $subscription_id && $car_limit <= 0 ) {
					return array(
						'status' => 2,
						'limit'  => $car_limit,
					);
				}
			} else {
				if ( empty( $car_limit ) || $car_limit <= 0 ) {
					return array(
						'status' => 2,
						'limit'  => $car_limit,
					);
				}
			}

			if ( $uploaded_imgs > $img_limit ) {
				return array(
					'status' => 3,
					'limit'  => $img_limit,
				);
			}
			return array(
				'status' => 1,
			);
		}
		return array(
			'status' => 0,
		);
	}

}

CDFS_Cars_Form_Handler::init();
