<?php
/**
 * CDFS Cars Functions
 *
 * @author   PotenzaGlobalSolutions
 * @category Class
 * @package  CDFS/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add car menu items.
if ( ! function_exists( 'cdfs_add_car_menu' ) ) {
	/**
	 * Add car menu
	 *
	 * @param string $items .
	 * @param string $args .
	 */
	function cdfs_add_car_menu( $items, $args ) {
		global $car_dealer_options;

		if ( ! isset( $car_dealer_options['cdfs-menu'] ) || 1 !== (int) $car_dealer_options['cdfs-menu'] || ! class_exists( 'CDFS' ) ) {
			return $items;
		}

		$menu_label = ! empty( $car_dealer_options['cdfs-menu-label'] ) ? $car_dealer_options['cdfs-menu-label'] : esc_html__( 'Add vehicle', 'cdfs-addon' );

		if ( 'primary-menu' !== $args->theme_location ) {
			return $items;
		}

		$url = cdfs_get_add_car_url();

		$menu_items          = '<li class="menu-item cdfs-add-vehicle"><a href="' . esc_url( $url ) . '" class="listing_add_cart heading-font button">';
			$menu_items     .= '<div>';
				$menu_items .= $menu_label;
			$menu_items     .= '</div>';
		$menu_items         .= '</a></li>';

		return $items . $menu_items;
	}
}
add_filter( 'wp_nav_menu_items', 'cdfs_add_car_menu', 20, 2 );



/*
 * Display add cars form
 */
if ( ! function_exists( 'cdfs_get_car_form_fields' ) ) {
	/**
	 * Assign fieldgroup key
	 *
	 * @param string $fieldgroup .
	 */
	function cdfs_get_car_form_fields( $fieldgroup = 'car_data' ) {
		global $car_dealer_options;

		if ( function_exists( 'acf_get_fields' ) ) {
			if ( 'car_data' === $fieldgroup ) {
				$fields = acf_get_fields( 'group_588f1cea78c99' );
			} else {
				$fields = acf_get_fields( 'group_588f0eef75bc1' );
			}

		} else {
			$fields = array();
		}

		$form_fields            = array();
		$form_fields_additional = array();
		$additional_attrs_slugs = array();
		$taxonomies             = cdfs_get_cars_taxonomy();
		$additional_attrs       = get_option( 'cdhl_additional_attributes' );
		$cdhl_core_attributes   = get_option( 'cdhl_core_attributes' );

		if ( $additional_attrs ) {
			$additional_attrs_slugs = array_column( $additional_attrs, 'slug' );
		}

		/**
		 * Filters the array of fields to put in required fields for add vehicle form in front submission.
		 *
		 * @since 1.0
		 * @param array         $required_fields    Array of fields to put in required fields in add car form(front side validation).
		 * @visible             true
		 */
		$required_fields = apply_filters( 'cdfs_form_required_fields', array( 'year', 'make', 'model', 'regular_price' ) );

		$review_stamp_limit = isset( $car_dealer_options['review_stamp_limit'] ) ? $car_dealer_options['review_stamp_limit'] : 1;
		for( $i=1; $i<=$review_stamp_limit; $i++ ) {
			$review_stamp_array[] = 'review_stamp_logo_' . $i;
			$review_stamp_array[] = 'review_stamp_link_' . $i;
		}

		$additional_field[] = array(
			'label'        => esc_html__( 'Title', 'cdfs-addon' ),
            'name'         => 'car_title',
            'type'         => 'text',
            'description'  => esc_html__( 'If the title is not entered then it will be auto-generated using Year, Make, and Model.', 'cdfs-addon' ),
		);

		$fields = array_merge( $additional_field, $fields );

		foreach ( $fields as $field ) {
			if ( in_array( strtolower( $field['type'] ), array( 'tab', 'file', 'google_map', 'message' ), true ) ) {
				continue;
			}

			if ( in_array( strtolower( $field['name'] ), $review_stamp_array, true ) ) {
				continue;
			}

			$fieldname     = $field['name'];
			$description   = isset( $field['description'] ) ? $field['description'] : '';
			$require_class = ( in_array( $fieldname, $required_fields ) ) ? 'cdhl_validate' : '';
			$extra_classes = apply_filters( 'cdfs_additional_classes', array( $require_class ) );
			$extra_classes = ' ' . implode( ' ', $extra_classes );

			switch ( $fieldname ) {
				case 'condition':
				case 'enable_request_price':
				case 'car_status':

					$default = '';
					$options = array();
					if ( 'condition' === $fieldname ) {
						// Get all conditions from database.
						$conditions = get_terms(
							array(
								'taxonomy'   => 'car_condition',
								'hide_empty' => false,
							)
						);

						$options    = array(
							'' => sprintf( esc_html__( 'Select %s', 'cdfs-addon' ), $field['label'] ),
						);

						foreach ( $conditions as $condition ) {
							$options[ $condition->slug ] = $condition->name;
						}
					} elseif ( 'enable_request_price' === $fieldname ) {
						$default = '0';
						$options = array(
							'1' => esc_html__( 'Yes', 'cdfs-addon' ),
							'0' => esc_html__( 'No', 'cdfs-addon' ),
						);
					} else {
						$options = array(
							'unsold' => esc_html__( 'Unsold', 'cdfs-addon' ),
							'sold'   => esc_html__( 'Sold', 'cdfs-addon' ),
						);
					}

					if ( isset( $field['taxonomy'] ) && isset( $cdhl_core_attributes[$field['taxonomy']]['is_dropdown'] ) && 'yes' === $cdhl_core_attributes[$field['taxonomy']]['is_dropdown'] ) {

						$form_fields[$fieldname] = array(
							'type'         => 'select',
							'name'         => $fieldname,
							'class'        => 'cdfs-select2 select' . $extra_classes,
							'placeholder'  => $field['label'],
							'options'      => $options,
							'description'  => $description,
						);

					} else {

						// radio button.
						$form_fields[$fieldname] = array(
							'type'         => 'radio',
							'name'         => $fieldname,
							'class'        => 'radio' . $extra_classes,
							'placeholder'  => $field['label'],
							'options'      => $options,
							'default'      => $default,
							'description'  => $description,
						);
					}

					break;
				case 'year':

					if ( isset( $field['taxonomy'] ) && isset( $cdhl_core_attributes[$field['taxonomy']]['is_dropdown'] ) && 'yes' === $cdhl_core_attributes[$field['taxonomy']]['is_dropdown'] ) {

						// Get all field_terms from database.
						$field_terms = get_terms(
							array(
								'taxonomy'   => $field['taxonomy'],
								'hide_empty' => false,
							)
						);

						$options    = array(
							'' => sprintf( esc_html__( 'Select %s', 'cdfs-addon' ), $field['label'] ),
						);

						foreach ( $field_terms as $field_term ) {
							$options[ $field_term->slug ] = $field_term->name;
						}

						$form_fields[$fieldname] = array(
							'type'         => 'select',
							'name'         => $fieldname,
							'description'  => $description,
							'options'      => $options,
							'class'        => 'cdfs-select2 cdfs_len_limit' . $extra_classes,
							'placeholder'  => $field['label'],
						);

					} else {
						// number fields.
						$form_fields[$fieldname] = array(
							'type'         => 'number',
							'name'         => $fieldname,
							'description'  => $description,
							'class'        => 'cdfs_len_limit cdfs-autofill' . $extra_classes,
							'placeholder'  => $field['label'],
						);
					}

					break;
				case 'mileage':
				case 'fuel_economy':
				case 'stock_number':
				case 'city_mpg':
				case 'highway_mpg':
				case 'regular_price':
				case 'sale_price':
					$form_fields[$fieldname] = array(
						'type'         => 'number',
						'name'         => $fieldname,
						'class'        => $extra_classes,
						'description'  => $description,
						'placeholder'  => $field['label'],
					);

					break;
				case 'video_link':
					// url fields.
					$form_fields[$fieldname] = array(
						'type'         => 'url',
						'name'         => $fieldname,
						'description'  => $description,
						'class'        => 'url' . $extra_classes,
						'placeholder'  => $field['label'],
					);

					break;
				case 'features_and_options':
					// checkbox fields.
					$form_fields[$fieldname] = array(
						'type'         => 'checkbox',
						'name'         => $fieldname,
						'description'  => $description,
						'class'        => 'cdfs-checkbox' . $extra_classes,
						'placeholder'  => $field['label'],
					);

					break;
				case 'vehicle_overview':
				case 'technical_specifications':
				case 'general_information':
					// All Editor fields.
					$form_fields[$fieldname] = array(
						'type'         => 'editor',
						'name'         => $fieldname,
						'description'  => $description,
						'class'        => 'cdfs-editor' . $extra_classes,
						'placeholder'  => $field['label'],
					);
					break;
				case 'pdf_file':
					break;
				case 'car_images':
					$form_fields[$fieldname] = array(
						'type'         => 'gallery',
						'name'         => $fieldname,
						'description'  => $description,
						'class'        => 'cdfs-editor' . $extra_classes,
						'placeholder'  => $field['label'],
					);
					break;
				default:
					// All text fields.
					$class       = '';
					$new_tax_obj = '';

					if ( in_array( 'car_' . $fieldname, $taxonomies ) && 'vin_number' !== $fieldname ) { // add autofill only for taxonomy fields.
						$class = 'cdfs-autofill';
					}
					if ( isset($field['taxonomy']) && ! empty($field['taxonomy']) ) {
						$new_tax_obj = get_taxonomy( $field['taxonomy'] );
						if( isset($new_tax_obj->include_in_filters) && $new_tax_obj->include_in_filters == true ) {
							$class = 'cdfs-autofill';
							$fieldname = $field['taxonomy'];
						}
					}

					if ( isset( $field['taxonomy'] ) && 'taxonomy' === $field['type'] && in_array( $field['taxonomy'], $additional_attrs_slugs, true ) ) {

						if ( isset( $new_tax_obj->is_dropdown ) && 'yes' === $new_tax_obj->is_dropdown ) {
							$field_terms = get_terms(
								array(
									'taxonomy'   => $field['taxonomy'],
									'hide_empty' => false,
								)
							);

							$options    = array(
								'' => sprintf( esc_html__( 'Select %s', 'cdfs-addon' ), $new_tax_obj->labels->singular_name ),
							);

							foreach ( $field_terms as $term ) {
								$options[ $term->slug ] = $term->name;
							}

							$form_fields_additional[] = array(
								'type'         => 'select',
								'name'         => $fieldname,
								'description'  => $description,
								'options'      => $options,
								'class'        => 'cdfs-select2' . $extra_classes,
								'placeholder'  => $field['label'],
							);
						} else {
							$form_fields_additional[] = array(
								'type'         => 'text',
								'name'         => $fieldname,
								'description'  => $description,
								'class'        => $class . $extra_classes,
								'placeholder'  => $field['label'],
							);
						}
					} else {

						if ( isset( $car_dealer_options['cdfs_make_model_relation'] ) ) {
							$cdfs_make_model_relation = filter_var( $car_dealer_options['cdfs_make_model_relation'], FILTER_VALIDATE_BOOLEAN );
							if ( $cdfs_make_model_relation ) {
								if ( 'make' === $fieldname ) {
									$extra_classes .= ' cdfs-relation-enabled';
								}
							}
						}

						if ( isset( $new_tax_obj->is_dropdown ) && 'yes' === $new_tax_obj->is_dropdown ) {
							
							$field_terms_array = array(
								'taxonomy'   => $field['taxonomy'],
								'hide_empty' => false,
							);

							$car_id   = isset( $_GET['car-id'] ) ? (int) $_GET['car-id'] : '';
							$edit_car = isset( $_GET['edit-car'] ) ? (int) $_GET['edit-car'] : '';

							if ( $car_id && $edit_car && 'model' === $fieldname && isset( $cdfs_make_model_relation ) && $cdfs_make_model_relation ) {
								$car_make  = get_the_terms( $car_id, 'car_make' );
								if ( isset( $car_make[0]->term_id ) ) {
									$field_terms_array['meta_key']   = 'parent_make';
									$field_terms_array['meta_value'] = $car_make[0]->term_id;
								}
							}

							$field_terms = get_terms( $field_terms_array );
							$options     = array(
								'' => sprintf( esc_html__( 'Select %s', 'cdfs-addon' ), $new_tax_obj->labels->singular_name ),
							);

							foreach ( $field_terms as $term ) {
								$options[ $term->slug ] = $term->name;
							}

							$form_fields[$fieldname] = array(
								'type'         => 'select',
								'name'         => $fieldname,
								'description'  => $description,
								'options'      => $options,
								'class'        => 'cdfs-select2' . $extra_classes,
								'placeholder'  => $field['label'],
							);
						} else {
							$form_fields[$fieldname] = array(
								'type'         => 'text',
								'name'         => $fieldname,
								'description'  => $description,
								'class'        => $class . $extra_classes,
								'placeholder'  => $field['label'],
							);
						}
					}
			}
		}

		$form_fields = array_merge( $form_fields, $form_fields_additional );

		/**
		 * Filters the vehicle form fields for add new vehicle page in front submission.
		 *
		 * @since 1.0
		 * @param array         $form_fields    An array of vehicle fields to be displayed on add vehicle page.
		 * @visible             true
		 */
		return apply_filters( 'cdfs_car_form_fields_items', $form_fields );
	}
}

if ( ! function_exists( 'cdfs_get_cars_taxonomy' ) ) {
	/**
	 * Get Taxomony of Cars Posttype
	 */
	function cdfs_get_cars_taxonomy() {
		$taxonomies    = get_object_taxonomies( 'cars' );
		$taxonomyarray = array();
		foreach ( $taxonomies as $taxonomy ) {
			$tax_obj = get_taxonomy( $taxonomy );
			if ( 'car_features_options' !== $taxonomy ) {
				$taxonomyarray[ $tax_obj->label ] = $taxonomy;
			}
		}
		return $taxonomyarray;
	}
}



add_action( 'wp_ajax_cdfs_get_autocomplete', 'cdfs_autocomplete_fields' );
add_action( 'wp_ajax_nopriv_cdfs_get_autocomplete', 'cdfs_autocomplete_fields' );
if ( ! function_exists( 'cdfs_autocomplete_fields' ) ) {
	/**
	 * Auto complete fields in car form.
	 */
	function cdfs_autocomplete_fields() {
		global $car_dealer_options;

		$responsearray = array();

		if ( isset( $_POST['action'] ) && 'cdfs_get_autocomplete' === $_POST['action'] ) {
			$fieldname  = 'car_' . cdfs_clean( wp_unslash( $_POST['fieldName'] ) );
			$search_val = cdfs_clean( $_POST['search'] );
			$make       = isset( $_POST['make'] ) ? cdfs_clean( $_POST['make'] ) : '';
			$options    = array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false, // can be 1, '1' too.
				'name__like' => $search_val,
			);

			if ( isset( $car_dealer_options['cdfs_make_model_relation'] ) && $make && 'car_model' === $fieldname ) {
				$car_make                 = get_term_by( 'slug', $make, 'car_make' );
				$cdfs_make_model_relation = filter_var( $car_dealer_options['cdfs_make_model_relation'], FILTER_VALIDATE_BOOLEAN );

				if ( $cdfs_make_model_relation ) {
					$options['meta_query'] = array(
						 array(
							'key'       => 'parent_make',
							'value'     => $car_make->term_id,
						 )
					);
				}
			}

			// search value.
			$fieldvalue = get_terms( $fieldname, $options );
			if ( is_wp_error( $fieldvalue ) ) {
				$fieldname  = cdfs_clean( wp_unslash( $_POST['fieldName'] ) );
				
				$new_tax_obj = get_terms( $fieldname, $options );
				if ( ! is_wp_error( $new_tax_obj ) ) {
					$result = array();
					foreach ( $new_tax_obj as $key => $value ) {
						$result[] = array(
							'label' => $value->name,
							'value' => $value->name,
						);
					}
					// Prepare response.
					$responsearray = array(
						'status' => true,
						'msg'    => esc_html__( 'Found Match!', 'cdfs-addon' ),
						'data'   => $result,
					);
				} else {
					$responsearray = array(
						'status' => true,
						'msg'    => esc_html__( 'Not Found', 'cdfs-addon' ),
						'data'   => array(),
					);
				}
			} else {
				if ( ! empty( $fieldvalue ) ) { // If found match.
					$result = array();
					foreach ( $fieldvalue as $key => $value ) {
						$result[] = array(
							'label' => $value->name,
							'value' => $value->name,
						);
					}

					// Prepare response.
					$responsearray = array(
						'status' => true,
						'msg'    => esc_html__( 'Found Match!', 'cdfs-addon' ),
						'data'   => $result,
					);
				} else {
					$responsearray = array(
						'status' => true,
						'msg'    => esc_html__( 'Not Found', 'cdfs-addon' ),
						'data'   => array(),
					);
				}
			}
		} else {
			$responsearray = array( 'status' => false );
		}

		// Send Result.
		echo json_encode( $responsearray );
		die;
	}
}

if ( ! function_exists( 'cdfs_handle_attachment' ) ) {
	/**
	 * Image upload handle.
	 *
	 * @param string $file_handler .
	 * @param string $post_id .
	 * @param string $allowed_types .
	 */
	function cdfs_handle_attachment( $file_handler, $post_id, $allowed_types = array( 'jpg', 'jpeg', 'png', 'gif' ) ) {
		// check to make sure its a successful upload.
		if ( ! isset( $_FILES[ $file_handler ]['error'] ) ) {
			return false;
		}
		if ( UPLOAD_ERR_OK !== $_FILES[ $file_handler ]['error'] ) {
			return false; }

		$ext = pathinfo( $_FILES[ $file_handler ]['name'] );
		$ext = $ext['extension'];
		if ( ! in_array( strtolower( $ext ), $allowed_types ) ) {
			/* translators: $s: Please upload file */
			cdfs_add_notice( sprintf( esc_html__( 'Please upload file(s) with %s extension.', 'cdfs-addon' ), implode( ', ', $allowed_types ) ), 'error' );
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attach_id = media_handle_upload( $file_handler, $post_id );
		if ( is_wp_error( $attach_id ) ) {
			return false;
		}
		return $attach_id;
	}
}

if ( ! function_exists( 'cdfs_delete_post_attachments' ) ) {
	/**
	 * Unset / Remove attachments
	 *
	 * @param string $post_id .
	 * @param string $field .
	 * @param string $attachment_ids .
	 */
	function cdfs_delete_post_attachments( $post_id, $field, $attachment_ids ) {
		foreach ( $attachment_ids as $id ) {
			try {
				// Update post attachments.
				$attachments = get_post_meta( $post_id, $field, true );
				if ( ( $key = array_search( $id, $attachments ) ) !== false ) {
					unset( $attachments[ $key ] );
					update_post_meta( $post_id, $field, $attachments );
				}
			} catch ( Exception $e ) {
				cdfs_add_notice( $e->getMessage(), 'error' );
			}
		}
	}
}

add_action( 'wp_ajax_cdfs_delete_attachment', 'cdfs_delete_attachment' );
add_action( 'wp_ajax_nopriv_cdfs_delete_attachment', 'cdfs_delete_attachment' );
if ( ! function_exists( 'cdfs_delete_attachment' ) ) {
	/**
	 * Remove attachments.
	 */
	function cdfs_delete_attachment() {
		$responsearray = array(
			'status' => false,
			'msg'    => esc_html__( 'Something went wrong!', 'cdfs-addon' ),
		);

		if ( isset( $_POST['action'] ) && 'cdfs_delete_attachment' === $_POST['action'] ) {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cdfs-car-form' ) ) {
				$responsearray = array( 'status' => false );
			} else {
				$attach_id   = cdfs_clean( $_POST['attach_id'] );
				$parent_id   = cdfs_clean( $_POST['parent_id'] );
				$field       = cdfs_clean( $_POST['field'] );

				if ( isset( $attach_id ) ) {
					if ( strpos( $field, 'review_stamp_logo' ) !== false || 'pdf_file' === $field ) {
						delete_post_meta( $parent_id, $field );
					} else {
						// Update post attachments.
						$attachments = get_post_meta( $parent_id, $field, true );
						if ( ( $key = array_search( $attach_id, $attachments ) ) !== false ) {
							unset( $attachments[ $key ] );
							update_post_meta( $parent_id, $field, $attachments );
						}
					}

					$responsearray = array(
						'status' => true,
						'msg'    => esc_html__( 'Successfully dropped image!', 'cdfs-addon' ),
					);
				}
			}
		}
		// Send Result.
		echo wp_json_encode( $responsearray );
		die;
	}
}

add_action( 'wp_ajax_cdfs_save_car', 'cdfs_do_save' );
add_action( 'wp_ajax_nopriv_cdfs_save_car', 'cdfs_do_save' );
if ( ! function_exists( 'cdfs_do_save' ) ) {
	/**
	 * Function to save car data
	 */
	function cdfs_do_save() {
		$status         = false;
		$msg            = esc_html__( 'Something went wrong!', 'cdfs-addon' );
		$car_id         = '';
		$type           = '';
		$submit_type    = '';
		$invalid_fields = array();

		if ( isset( $_POST['action'] ) && 'cdfs_save_car' === $_POST['action'] ) {
		    $result = CDFS_Cars_Form_Handler::process_car_save();

            // Check if user wants to save as a draft
            if ($_POST['isSaving'] == 0) {
                
            }

			if ( ! empty( $result ) ) {

				$submit_type = $result['submit_type'];
				switch ( $result['status'] ) {
					case 1:
						$status = true;
						$msg    = esc_html__( 'Vehicle added successfully!', 'cdfs-addon' );
						$car_id = $result['post_id'];
						$type   = 'add';
						break;
					case 3:
						$status = false;
						$msg    = esc_html__( 'Please check captcha form.', 'cdfs-addon' );
						break;
					case 4:
						$status = false;
						$msg = sprintf(
							/* translators: %s: list of required fields */
							esc_html__( 'Please fill required fields: %s', 'cdfs-addon' ),
							esc_html( implode( ', ', $result['err_fields'] ) )
						);
						break;
					case 5:
						if ( isset( $_POST['cdfs_action_car_id'] ) && $_POST['cdfs_action_car_id'] ) {
							$status = false;
							$msg    = esc_html__( 'Sorry! The limit for the selected package has been exceeded. You cannot update a car to this package.', 'cdfs-addon' );
						} else {
							$status = false;
							$msg    = esc_html__( 'Sorry! The limit for the selected package has been exceeded. You cannot add a new car.', 'cdfs-addon' );
						}

						break;
					case 'invalid_sale_price':
						$invalid_fields[] = 'sale_price';
						$status = false;
						$msg    = esc_html__( 'Sale price should be lower then regular price', 'cdfs-addon' );
						break;
					case 'image_limit':
						$status = false;
						$msg    = sprintf(
							/* translators: %s: number of images */
							_n( 'Sorry! You can upload maximum %s image.', 'Sorry! You can upload maximum %s images.', $result['limit'], 'cdfs-addon' ),
							number_format_i18n( $result['limit'] )
						);
						break;
					case 7:
						$status = true;
						$msg    = esc_html__( 'Vehicle data successfully saved!', 'cdfs-addon' );
						$car_id = $result['post_id'];
						$type   = 'update';
						break;
					case 8:
						$status = false;
						$msg = sprintf(
							/* translators: %s: list of required fields */
							esc_html__( 'New values not allowed for %s, Please select the value from existing dropdown/Autocomplete', 'cdfs-addon' ),
							esc_html( implode( ', ',  $result['err_fields'] ) )
						);
						break;
					case 9:
						$status = false;
						$msg    = esc_html__( 'Make model relation enable but model is not related to make. Please enter/select model related to make.', 'cdfs-addon' );;
						break;
					case 10:
					    $invalid_fields[] = 'registration_date';
						$status = false;
						$msg    = esc_html__( 'Invalid Registration Date.', 'cdfs-addon' );
						break;
					case 'login':
						$status = false;
						$msg    = esc_html__( 'Please login to proceed.', 'cdfs-addon' );
						break;
					case 'nonce':
						$status = false;
						$msg    = esc_html__( 'Security nonce verification failed. Try again later.', 'cdfs-addon' );
						break;
					case 'demo_mode':
						$status = false;
						$msg    = esc_html__( "The site is currently in demo mode, You can't add/update listing.", 'cdfs-addon' );
						break;
					default:
						$status = false;
						$msg    = esc_html__( 'There is an error inserting vehicle, please try again later!', 'cdfs-addon' );
				}
			}
		}
		echo wp_json_encode(
			array(
				'status'         => $status,
				'type'           => $type,
				'car_id'         => $car_id,
				'message'        => $msg,
				'submit_type'    => $submit_type,
				'invalid_fields' => $invalid_fields,
			)
		);
		die;
	}
}



add_action( 'wp_ajax_cdfs_upload_images', 'cdfs_do_upload_images' );
add_action( 'wp_ajax_nopriv_cdfs_upload_images', 'cdfs_do_upload_images' );
if ( ! function_exists( 'cdfs_do_upload_images' ) ) {
	/**
	 * Remove attachments
	 */
	function cdfs_do_upload_images() {
		$status      = false;
		$redirect    = '';
		$status_msg  = esc_html__( 'Something went wrong!', 'cdfs-addon' );
		$submit_type = isset( $_POST['submit_type'] ) ? $_POST['submit_type'] : 'default';
		$car_id      = isset( $_POST['car_id'] ) ? $_POST['car_id'] : '';

		if ( isset( $_POST['action'] ) && 'cdfs_upload_images' === $_POST['action'] ) {
			$imgupload = CDFS_Cars_Form_Handler::process_image_upload();
			if ( true === (bool) $imgupload['status'] ) {
				if ( 'listing_payment' === $submit_type ) {
					$checkout_url_args = array(
						'add-to-cart'          => $car_id,
						'cardealer_order_type' => 'listing_payment',
					);

					if ( isset( $_POST['is_webview'] ) && 'yes' === $_POST['is_webview'] ) {
						$checkout_url_args['is_webview'] = 'yes';
					}

					$redirect = add_query_arg( $checkout_url_args, wc_get_checkout_url() );
				} else {
					$redirect = cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' );
				}

				$status_msg = esc_html__( 'Vehicle images uploaded successfully!!', 'cdfs-addon' );
				$type       = isset( $_POST['type'] ) ? $_POST['type'] : 'update';

				if ( 'add' === $type ) {
					cdfs_add_notice( esc_html__( 'Vehicle added successfully!', 'cdfs-addon' ) );
				} elseif ( 'update' === $type ) {
					cdfs_add_notice( esc_html__( 'Vehicle is successfully saved!', 'cdfs-addon' ) );
				}

				$status = true;
				if ( ! empty( $imgupload['file_size_error'] ) ) {
					/* translators: $s: Following images not uploaded due to image size exceeded than */
					cdfs_add_notice( sprintf( esc_html__( 'Following images not uploaded due to image size exceeded than %1$s MB: %2$s.', 'cdfs-addon' ), $imgupload['file_size_limit'], implode( ',', $imgupload['file_size_error'] ) ), 'error' );
				}
			}
		}
		echo wp_json_encode(
			array(
				'status'     => $status,
				'redirect'   => $redirect,
				'status_msg' => $status_msg,
			)
		);
		die;
	}
}

if ( ! function_exists( 'cdfs_get_html_mail_body' ) ) {
	/**
	 * Function For HTML Mail Body
	 *
	 * @param string $car_id .
	 */
	function cdfs_get_html_mail_body( $car_id ) {
		$car_dealer_options = get_option( 'car_dealer_options' );

		$vehivle_data = array();
		$product      = '';

		// Image
		$vehivle_data['car_thumbnail'] = array(
			'value'   => cardealer_get_cars_image( 'car_thumbnail', $car_id ),
		);

		// Price
		$sale_price    = false;
		$regular_price = false;
		$price         = array();
		// '&nbsp;';
		if ( function_exists( 'get_field' ) ) {
			$sale_price    = (int) get_field( 'sale_price', $car_id );
			$regular_price = (int) get_field( 'regular_price', $car_id );
		} else {
			$sale_price    = (int) get_post_meta( $post_id, 'sale_price', true );
			$regular_price = (int) get_post_meta( $post_id, 'regular_price', true );
		}

		$currency_code            = ( isset( $car_dealer_options['cars-currency-symbol'] ) && ! empty( $car_dealer_options['cars-currency-symbol'] ) ) ? $car_dealer_options['cars-currency-symbol'] : 'USD';
		$currency_symbol          = ( function_exists( 'cdhl_get_currency_symbols' ) ) ? cdhl_get_currency_symbols( $currency_code ) : '$';
		$symbol_position          = (int) ( ( isset( $car_dealer_options['cars-currency-symbol-placement'] ) && ! empty( $car_dealer_options['cars-currency-symbol-placement'] ) ) ? $car_dealer_options['cars-currency-symbol-placement'] : '1' );
		$seperator                = (bool) ( ( isset( $car_dealer_options['cars-disable-currency-separators'] ) && '' != $car_dealer_options['cars-disable-currency-separators'] ) ? $car_dealer_options['cars-disable-currency-separators'] : '1' );
		$seperator_symbol         = ( isset( $car_dealer_options['cars-thousand-separator'] ) && ! empty( $car_dealer_options['cars-thousand-separator'] ) ) ? $car_dealer_options['cars-thousand-separator'] : ',';
		$decimal_places           = ( ! empty( $car_dealer_options['cars-number-decimals'] ) && is_numeric( $car_dealer_options['cars-number-decimals'] ) ) ? $car_dealer_options['cars-number-decimals'] : 0;
		$decimal_separator_symbol = ( isset( $car_dealer_options['cars-decimal-separator'] ) && ! empty( $car_dealer_options['cars-decimal-separator'] ) ) ? $car_dealer_options['cars-decimal-separator'] : '.';

		if ( $regular_price || $sale_price ) {

			if ( $sale_price ) {
				if ( $seperator ) {
					$sale_price = number_format( $sale_price, $decimal_places, $decimal_separator_symbol, $seperator_symbol );
				}
				if ( 1 === $symbol_position || 3 === $symbol_position ) {
					$price[] = '<span>' . esc_html__( 'Sale Price: ', 'cdfs-addon' ) . ( ( 1 === $symbol_position ) ? "{$currency_symbol}{$sale_price}" : "{$currency_symbol} {$sale_price}" ) . '</span>';
				} else {
					$price[] = '<span>' . esc_html__( 'Sale Price: ', 'cdfs-addon' ) . ( ( 2 === $symbol_position ) ? "{$sale_price}{$currency_symbol}" : "{$sale_price} {$currency_symbol}" ) . '</span>';
				}
			}
			if ( $regular_price ) {
				if ( $seperator ) {
					$regular_price = number_format( $regular_price, $decimal_places, $decimal_separator_symbol, $seperator_symbol );
				}
				if ( 1 === $symbol_position || 3 === $symbol_position ) {
					$price[] = '<span>' . esc_html__( 'Sale Price: ', 'cdfs-addon' ) . ( ( 1 === $symbol_position ) ? "{$currency_symbol}{$regular_price}" : "{$currency_symbol} {$regular_price}" ) . '</span>';
				} else {
					$price[] = '<span>' . esc_html__( 'Sale Price: ', 'cdfs-addon' ) . ( ( 2 === $symbol_position ) ? "{$regular_price}{$currency_symbol}" : "{$regular_price} {$currency_symbol}" ) . '</span>';
				}
			}
		}
		if ( is_array( $price ) && ! empty( $price ) ) {
			$price = implode( '&nbsp;&nbsp;', $price );
		} else {
			$price = '&mdash;';
		}

		$vehivle_data['vehicle_price'] = array(
			'label'   => esc_html__( 'Vehicle Price', 'cdfs-addon' ),
			'value'   => $price,
			'valuex'   => '<span>' . esc_html__( 'Sale Price: ', 'cdfs-addon' ) . $sale_price . '</span><span>&nbsp;&nbsp;' . esc_html__( 'Regular Price : ', 'cdfs-addon' ) . $regular_price . '</span>',
		);

		$cars_taxonomy_array  = cdfs_get_cars_taxonomy();
		$cars_taxonomy_array  = array_flip( $cars_taxonomy_array );

		foreach ( $cars_taxonomy_array as $vehicle_tax => $vehicle_tax_label ) {
			$vehicle_terms = wp_get_post_terms( $car_id, $vehicle_tax );
			if ( ! is_wp_error( $vehicle_terms ) ) {
				$vehivle_data[ $vehicle_tax ] = array(
					'label' => $vehicle_tax_label,
					'value' => ( ! empty( $vehicle_terms ) ) ? $vehicle_terms[0]->name : '&mdash;',
				);
			}
		}

		/**
		 * Filters the HTML of mail body for vehicle attributes.
		 *
		 * @since 1.0
		 * @param string    $product    contents of the mail body for vehicle.
		 * $param int       $car_id     Vehicle ID.
		 * @visible         true
		 */
		$vehivle_data = apply_filters( 'cdfs_get_mail_body_vehicle_data', $vehivle_data, $car_id );

		$product .= '<table class="compare-list compare-datatable" width="100%" border="1" cellspacing="0" cellpadding="5">';
		$product .= '<tbody>';

		foreach ( $vehivle_data as $vehivle_data_k => $vehivle_data_v ) {

			if ( ! isset( $vehivle_data_v['value'] ) ) {
				continue;
			}

			$product .= '<tr class="' . esc_attr( $vehivle_data_k ) . '">';
			if ( ! isset( $vehivle_data_v['label'] ) || '' === $vehivle_data_v['label'] ) {
				$product .= '<td colspan=2 style="text-align:center">';
			} else {
				$product .= '<td>';
				$product .= $vehivle_data_v['label'];
				$product .= '</td>';
				$product .= '<td>';
			}
			$product .= $vehivle_data_v['value'];
			$product .= '</td>';
			$product .= '</tr>';
		}

		$product .= '</tbody>';
		$product .= '</table>';

		/**
		 * Filters the HTML of mail body for vehicle attributes.
		 *
		 * @since 1.0
		 * @param string    $product    contents of the mail body for vehicle.
		 * $param int       $car_id     Vehicle ID.
		 * @visible         true
		 */
		return apply_filters( 'cdfs_get_html_mail_body', $product, $car_id );
	}
}

/**
 * User vehicle notification on post status publish.
 */
if ( is_admin() ) {
	add_action( 'transition_post_status', 'cdfs_send_publish_notification', 10, 3 );
}

if ( ! function_exists( 'cdfs_send_publish_notification' ) ) {
	/**
	 * Send publish notification
	 *
	 * @param string $new_status .
	 * @param string $old_status .
	 * @param string $post .
	 */
	function cdfs_send_publish_notification( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status || 'publish' === $old_status ) {
			return;
		}

		if ( 'cars' !== $post->post_type ) {
			return; // restrict the filter to a specific post type.
		}

		// send notification.
		// get site details.
		$site_title = get_bloginfo( 'name' );
		$site_email = get_bloginfo( 'admin_email' );
		$admin      = get_user_by( 'email', $site_email );

		$dealer_id = get_post_field( 'post_author', $post->ID );
		if ( empty( $dealer_id ) ) { // return if no dealer is assigned.
			return;
		}
		$dealer_data = get_user_by( 'id', $dealer_id );

		// Send email notification.
		$subject  = esc_html__( 'Vehicle Listed For Sale - Nordic Partners', 'cdfs-addon' );
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
		$headers .= 'From: ' . html_entity_decode( $site_title, ENT_QUOTES ) . ' <' . $site_email . '>' . "\r\n";

		$car_data = array(
			'admin_name'    => $admin->user_login,
			'dealer_name'   => $dealer_data->user_login,
			'vehicle_title' => $post->post_title,
			'vehicle_link'  => get_permalink( $post->ID ),
			'dealer_email'  => $dealer_data->user_email,
			'mail_html'     => cdfs_get_html_mail_body( $post->ID ),
		); 
		// Mail to admin.
		ob_start();
		cdfs_get_template(
			'mails/mail-dealer-publish-notification.php',
			array(
				'car_data'  => $car_data,
				'site_data' => array( 'site_title' => $site_title ),
			)
		);
		$dealer_message = ob_get_contents();
		ob_end_clean();

		// send mail.
		try {
			wp_mail( $dealer_data->user_email, $subject, $dealer_message, $headers );
		} catch ( Exception $e ) {
			cdfs_add_notice( $e->getMessage(), 'error' );
		}
	}
}

/**
 * Check whether car clone functionality is enabled.
 *
 * @return void
 */
function cdfs_is_vehicle_clone_enabled() {
	return apply_filters( 'cdfs_is_vehicle_clone_enabled', false );
}

add_filter( 'cardealer_get_show_hide_list_layout_style', 'cdfs_author_list_layout_style' );
function cdfs_author_list_layout_style( $layouts ) {

	if ( is_author() ) {
		foreach ( $layouts as $layout_k => $layout_v ) {
			if ( 'view-masonry' === $layout_v ) {
				unset( $layouts[ $layout_k ] );
			}
		}
	}

	return $layouts;
}


add_filter( 'cardealer_assets_script_data', 'cdfs_exend_cardealer_assets_script_data', 9999, 2 );
function cdfs_exend_cardealer_assets_script_data( $script_data, $script_key ) {
	if ( ! is_admin() && is_author() ) {
		$selected_user = get_queried_object();
		$user_type     = cdfs_get_usertype( $selected_user );
		$author_url    = get_author_posts_url( $selected_user->ID );
		$cars_form_url = $author_url;
		$tabs          = cdfs_get_user_profile_tabs( $user_type );
		$profile_tab   = isset( $_GET['profile-tab'] ) && ! empty( $_GET['profile-tab'] ) ? sanitize_text_field( wp_unslash( $_GET['profile-tab'] ) ) : '';
		$page          = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		if ( ! empty( $profile_tab ) && array_key_exists( $profile_tab, $tabs ) ) {
			$cars_form_url = add_query_arg( array(
				'profile-tab' => $profile_tab,
			), $cars_form_url );
		}

		if ( ! empty( $profile_tab ) && array_key_exists( $profile_tab, $tabs ) ) {
			$cars_form_url = add_query_arg( array(
				'page' => $page,
			), $cars_form_url );
		}

		if ( 'cardealer-vehicle-filter' === $script_key ) {
			$script_data['action'] = 'enqueue';

			unset( $script_data['localize']['vehicle_filter_js_object']['cars_form_url'] );

			$script_data['localize']['vehicle_filter_js_object']['author_url']    = $author_url;
			$script_data['localize']['vehicle_filter_js_object']['cars_form_url'] = $cars_form_url;
			$script_data['localize']['vehicle_filter_js_object']['cdfs_dashboard'] = 'yes';
		}
	}
	return $script_data;
}

add_filter( 'cardealer_get_grid_column', 'cdfs_author_cars_grid_column' );
function cdfs_author_cars_grid_column( $col ) {
	if ( is_author() ) {
		$col= 4;
	}
	return $col;
}

function cdfs_extend_google_maps_js( $script_data, $script_key ) {
	global $wp, $car_dealer_options;

	if ( is_author() && ! isset( $wp->query_vars['profile'] ) && 'cardealer-map' === $script_key ) {
		$script_data['action'] = 'enqueue';
		$zoom = 10;
		if ( isset( $car_dealer_options['default_value_zoom'] ) && ! empty( $car_dealer_options['default_value_zoom'] ) ) {
			$zoom = (int) $car_dealer_options['default_value_zoom'];
		}
		$script_data['localize']['cardealer_map_obj'] = array(
			'zoom' => $zoom,
		);
	}

	return $script_data;
}
add_filter( 'cardealer_assets_script_data', 'cdfs_extend_google_maps_js', 10, 2 );

function cdfs_cars_listing_grid_view_class( $classes, $columns ) {

	if ( is_author() ) {
		$classes = array( 'col-lg-3 col-md-4 col-sm-4 col-xs-6' );
	}

	return $classes;
}
add_filter( 'cardealer_grid_view_class', 'cdfs_cars_listing_grid_view_class', 10, 2 );

add_action( 'cardealer/cars-list/list-view/list-style-classic/after-car-info-top-right', 'cdfs_cars_list_add_seller_info' );
add_action( 'cardealer/cars-list/list-view/list-style-default/after-car-details-end', 'cdfs_cars_list_add_seller_info' );
function cdfs_cars_list_add_seller_info() {
	global $post, $car_dealer_options;

	$display_dealer_logo_name = true;

	if ( isset( $car_dealer_options['display_dealer_logo_name'] ) && '' !== $car_dealer_options['display_dealer_logo_name'] ) {
		$display_dealer_logo_name = $car_dealer_options['display_dealer_logo_name'];
	}

	$display_dealer_logo_name = filter_var( $display_dealer_logo_name, FILTER_VALIDATE_BOOLEAN );

	if ( ! $display_dealer_logo_name ) {
		return;
	}

	$post_author_id  = get_post_field( 'post_author', $post->ID );
	$author_name     = get_the_author_meta( 'display_name', $post_author_id );
	$author_url      = get_author_posts_url( $post_author_id );
	$user_avatar_url = cdfs_get_avatar_url( $post_author_id );
	?>
	<div class="car-list-seller-info">
		<div class="seller-info-thumb">
			<a href="<?php echo esc_url( $author_url ); ?>">
				<img height="30" width="30" src="<?php echo esc_url( $user_avatar_url ); ?>" class="img-circle">
			</a>
		</div>
		<div class="seller-info-details">
			<div class="seller-info-title"><a href="<?php echo esc_url( $author_url ); ?>"><?php echo esc_html( $author_name ); ?></a></div>
		</div>
	</div>
	<?php
}

add_filter( 'cardealer_cars_filter_methods', 'cdfs_cardealer_cars_filter_methods' );
function cdfs_cardealer_cars_filter_methods( $filter_methods ) {
	if ( is_author() ) {
		$filter_methods = 'no';
	}
	return $filter_methods;
}

add_action( 'wp_ajax_cdfc_get_models', 'cdfc_get_models' );
add_action( 'wp_ajax_nopriv_cdfc_get_models', 'cdfc_get_models' );
if ( ! function_exists( 'cdfc_get_models' ) ) {
	/**
	 * Remove attachments.
	 */
	function cdfc_get_models() {
		$responsearray = array(
			'status' => false,
			'msg'    => esc_html__( 'Something went wrong!', 'cdfs-addon' ),
		);

		if ( isset( $_POST['action'] ) && 'cdfc_get_models' === $_POST['action'] ) {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cdfs-car-form' ) ) {
				$responsearray = array( 'status' => false );
			} else {

				$make = cdfs_clean( $_POST['make'] );
				if ( $make ) {
					
					
					$car_make  = get_term_by( 'slug', $make, 'car_make' );

					if ( isset( $car_make->term_id ) && $car_make->term_id ) {
						
						$terms = get_terms( array(
							'taxonomy'    => 'car_model',
							'hide_empty'  => false,
							'meta_key'    => 'parent_make',
							'meta_value'  => $car_make->term_id
						) );

						if ( $terms ) {
							foreach( $terms as $term ) {
								$options[$term->slug] = $term->name;
							}
						}
					}
				}
				
				$responsearray = array(
					'status'  => true,
					'msg'     => esc_html__( 'Successfully dropped image!', 'cdfs-addon' ),
					'options' => $options,
				);
			}
		}

		// Send Result.
		echo wp_json_encode( $responsearray );
		die;
	}
}