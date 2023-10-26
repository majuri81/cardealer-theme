<?php
/**
 * CDFS car functions.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle car actions.
 *
 * @author PotenzaGlobalSolutions
 */
class CDFS_Cars {

	/**
	 * Car methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'process_car_actions' ), 10 );
		add_action( 'cdfs_clone_vehicle', array( __CLASS__, 'copy_meta_fields' ), 10, 2 );
		add_action( 'cdfs_clone_vehicle', array( __CLASS__, 'copy_taxonomies' ), 50, 2 );
	}

	/**
	 * Save car save [ update status / trash ]
	 */
	public static function process_car_actions() {
		global $car_dealer_options;

		// Bail early if it's not a car action.
		if ( ! isset( $_GET['cdfs_car_action'] ) ) {
			return;
		}

		// Check user if logged in to prevent from unautorised action.
		if ( ! is_user_logged_in() )  {
			cdfs_add_notice( esc_html__( 'Cheating! Please login and try again.', 'cdfs-addon' ), 'error' );
			wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
			die;
		}

		if ( isset( $_GET['cdfs_nonce'] ) && wp_verify_nonce( $_GET['cdfs_nonce'], 'cdhl-action' ) ) {

			$demo_mode = isset( $car_dealer_options['demo_mode'] ) ? (bool) $car_dealer_options['demo_mode'] : '';
			if ( $demo_mode ) {
				cdfs_add_notice( esc_html__( "The site is currently in demo mode, you can't perform any action.", 'cdfs-addon' ), 'error' );
				wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
				die;
			}

			if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {

				$vehicle_id = absint( wp_unslash( $_GET['id'] ) );

				// Get current vehicle.
				$vehicle = get_post( $vehicle_id );

				// Vehicle not found.
				if ( ! $vehicle ) {
					cdfs_add_notice( esc_html__( 'Vehicle not found.', 'cdfs-addon' ), 'error' );
					wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
					die;
				}

				// Not a post or "cars" post type.
				if ( ! is_a( $vehicle, 'WP_Post' ) || 'cars' !== $vehicle->post_type ) {
					cdfs_add_notice( esc_html__( 'Invalid vehicle ID.', 'cdfs-addon' ), 'error' );
					wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
					die;
				}

				$author = get_post_field( 'post_author', $vehicle_id );
				$user   = wp_get_current_user();

				if ( intval( $author ) == intval( $user->ID ) ) {

					switch ( $_GET['cdfs_car_action'] ) {
						case 'disable':
							CDFS_Cars::car_action_disable( $vehicle_id );
							break;
						case 'enable':
							CDFS_Cars::car_action_enable( $vehicle_id );
							break;
						case 'trash':
							CDFS_Cars::car_action_trash( $vehicle_id );
							break;
						case 'clone':
							CDFS_Cars::car_action_clone( $vehicle_id );
							break;
						default:
							do_action( 'cdfs_car_action' );
							cdfs_add_notice( esc_html__( 'Invalid action.', 'cdfs-addon' ), 'error' );
							wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
							die;
							break;
					}

				} else {
					cdfs_add_notice( esc_html__( 'This vehicle does not belong to you.', 'cdfs-addon' ), 'error' );
					wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
					die;
				}
			} else {
				cdfs_add_notice( esc_html__( 'Please provide vehicle ID.', 'cdfs-addon' ), 'error' );
				wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
				die;
			}
		} else {
			cdfs_add_notice( esc_html__( 'Invalid Nonce. Please refresh page and try again.', 'cdfs-addon' ), 'error' );
			wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
			die;
		}
	}

	public static function car_action_disable( $vehicle_id ) {
		wp_update_post( array(
			'ID'          => $vehicle_id,
			'post_status' => 'draft',
		) );
		cdfs_add_notice( esc_html__( 'Vehicle status is successfully set to draft.', 'cdfs-addon' ), 'success' );
		wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
		die;
	}

	public static function car_action_enable( $vehicle_id ) {
		$vehicle =  get_post( $vehicle_id );

		$subscription_id = get_post_meta( $vehicle_id, 'cdfs_subscription_id', true );
		$can_enabled     = true;

		if ( empty( $subscription_id ) && empty( $vehicle->cdfs_listing_type ) ) {
			$subscription_id            = 'free';
			$vehicle->cdfs_listing_type = 'free';
		}

		$subscription_cars       = cdfs_get_user_subscription_cars( $subscription_id, get_current_user_id() );
		$subscription_cars_count = count( $subscription_cars );

		if ( 'free' === $vehicle->cdfs_listing_type ) {
			$free_car_limit = cdfs_get_free_cars_limit();
			if (
				cdfs_get_user_subscription_available_car_limit( $subscription_id, get_current_user_id() ) <= 0
				&& (int) $subscription_cars_count >= (int) $free_car_limit
				&& ! array_key_exists( $vehicle_id, $subscription_cars )
			) {
				$can_enabled = false;
			} else {
				$can_enabled = true;
			}
		} elseif ( 'listing_payment' === $vehicle->cdfs_listing_type ) {
			$can_enabled = true;
		} elseif ( 'subscription' === $vehicle->cdfs_listing_type ) {
			$subscription_data = function_exists( 'subscriptio_get_subscription' ) ? subscriptio_get_subscription( $subscription_id ) : array();
			$status            = ( $subscription_data && ! empty( $subscription_data ) ) ? $subscription_data->get_status() : '';
			if (
				'active' !== $status
				|| (
					0 === cdfs_get_user_subscription_available_car_limit( $subscription_id, get_current_user_id() )
					&& ! array_key_exists( $vehicle_id, $subscription_cars )
				)
			) {
				$can_enabled = false;
			} else {
				$can_enabled = true;
			}
		}

		if ( ! $can_enabled ) {
			cdfs_add_notice( esc_html__( 'You can not enable vehicle, please check your subscription plan!', 'cdfs-addon' ), 'error' );
		} else {
			wp_update_post( array(
				'ID'          => $vehicle_id,
				'post_status' => 'publish',
			) );
			cdfs_add_notice( esc_html__( 'Vehicle is successfully enabled.', 'cdfs-addon' ), 'success' );
		}
		wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
		die;
	}

	public static function car_action_trash( $vehicle_id ) {
		// get all attachments of this post.
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => '-1',
				'post_parent'    => $vehicle_id,
			)
		);

		if ( wp_delete_post( $vehicle_id, true ) ) { // delete car post forcefully.
			if ( ! empty( $attachments ) ) {
				foreach ( $attachments as $attachment ) {
					wp_delete_attachment( $attachment->ID );
				}
			}
		}

		cdfs_add_notice( esc_html__( 'Vehicle is successfully deleted.', 'cdfs-addon' ), 'success' );
		wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
		die;
	}

	public static function car_action_clone( $vehicle_id ) {

		if ( ! cdfs_is_vehicle_clone_enabled() ) {
			cdfs_add_notice( esc_html__( 'Invalid action.', 'cdfs-addon' ), 'error' );
			wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
			die;
		}

		// Check author have limit.
		$subscription_id = get_post_meta( $vehicle_id, 'cdfs_subscription_id', true );
		if ( ! cdfs_get_user_subscription_available_car_limit( $subscription_id, get_current_user_id() ) ) {
			cdfs_add_notice( esc_html__( 'You can not clone vehicle, please check your subscription plan!', 'cdfs-addon' ), 'error' );
			wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
			die;
		}

		// Clone vehicle.
		$new_vehicle = CDFS_Cars::clone_vehicle( $vehicle_id );

		// Check if vehicle cloned.
		if ( is_wp_error( $new_vehicle ) ) {
			$error_message  = $new_vehicle->get_error_message();
			$notice = sprintf(
				esc_html__( 'The vehicle clone failed, could not create a copy of the vehicle. Error: %s', 'cdfs-addon' ),
				$error_message
			);
			echo $notice;
			cdfs_add_notice( $notice, 'error' );
			wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
			die;
		}

		cdfs_add_notice( esc_html__( 'Car cloned successfully.', 'cdfs-addon' ), 'success' );
		wp_safe_redirect( cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' ) );
		die;
	}

	/**
	 * Creates a copy of the vehicle from an existing vehicle.
	 *
	 * This is the main functions that does the cloning.
	 *
	 * @param int|string|WP_Post $vehicle   The ID or original post object.
	 * @param string             $status    Optional. The intended destination status.
	 * @return int|WP_Error
	 */
	static function clone_vehicle( $vehicle = null ) {
		$car_dealer_options = get_option( 'car_dealer_options' );

		if ( $vehicle instanceof WP_Post ) {
			$_vehicle = $vehicle;
		} else {
			$_vehicle = WP_Post::get_instance( $vehicle );
		}

		if ( ! $_vehicle ) {
			return new WP_Error( 'vehicle_null', esc_html__( 'Vehicle empty. Please provide a valid vehicle to clone.', "cdfs-addon" ) );
		}

		// Author.
		$new_vehicle_author = wp_get_current_user();

		$post_status = cdfs_auto_publish_status();

		$clone_vehicle_data = array(
			'post_author'           => $new_vehicle_author->ID,
			'post_content'          => $_vehicle->post_content,
			'post_content_filtered' => $_vehicle->post_content_filtered,
			'post_title'            => $_vehicle->post_title,
			// 'post_name'             => '',
			'post_excerpt'          => $_vehicle->post_excerpt,
			'post_status'           => $post_status,
			'post_type'             => $_vehicle->post_type,
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'post_password'         => $_vehicle->post_password,
			'to_ping'               => $_vehicle->to_ping,
			'pinged'                => $_vehicle->pinged,
			'post_parent'           => $_vehicle->post_parent,
			'menu_order'            => 0,
		);

		/**
		 * Filter new vehicle values.
		 *
		 * @param array   $clone_vehicle_data New vehicle data.
		 * @param WP_Post $_vehicle           Original vehicle post object.
		 *
		 * @return array
		 */
		$clone_vehicle_data = apply_filters( 'cdfs_clone_vehicle_data', $clone_vehicle_data, $_vehicle );

		$new_vehicle_id = wp_insert_post( wp_slash( $clone_vehicle_data ), true );

		if ( 0 !== $new_vehicle_id && ! is_wp_error( $new_vehicle_id ) ) {

			do_action( 'cdfs_clone_vehicle', $new_vehicle_id, $_vehicle, $post_status );

			delete_post_meta( $new_vehicle_id, '_cdfs_original_vehicle' );
			add_post_meta( $new_vehicle_id, '_cdfs_original_vehicle', $_vehicle->ID );
		}

		return $new_vehicle_id;
	}

	/**
	 * Copies the meta information of a post to another post
	 *
	 * @param int     $new_vehicle_id The new vehicle ID.
	 * @param WP_Post $vehicle        The original vehicle.
	 */
	static function copy_meta_fields( $new_vehicle_id, $vehicle ) {
		$post_meta_keys = get_post_custom_keys( $vehicle->ID );
		if ( empty( $post_meta_keys ) ) {
			return;
		}

		$exclude_list = array(
			'_edit_lock',
			'_edit_last',
		);

		/**
		 * Filters the list of meta field to be excluded from vehicle cloning.
		 *
		 * @param array $exclude_list  List of meta field to exclude.
		 * @return array
		 */
		$exclude_list = apply_filters( 'cdfs_clone_vehicle_meta_fields_exclude_list', $exclude_list );

		$exclude_list_str = '(' . implode( ')|(', $exclude_list ) . ')';
		if ( strpos( $exclude_list_str, '*' ) !== false ) {
			$exclude_list_str = str_replace( array( '*' ), array( '[a-zA-Z0-9_]*' ), $exclude_list_str );

			$meta_keys = array();
			foreach ( $post_meta_keys as $meta_key ) {
				if ( ! preg_match( '#^' . $exclude_list_str . '$#', $meta_key ) ) {
					$meta_keys[] = $meta_key;
				}
			}
		} else {
			$meta_keys = array_diff( $post_meta_keys, $exclude_list );
			$meta_keys = array_values( $meta_keys );
		}

		/**
		 * Filters the list of meta fields names when clonng a vehicle.
		 *
		 * @param array $meta_keys The list of meta fields name, with the ones in the exclude_list already removed.
		 *
		 * @return array
		 */
		$meta_keys = apply_filters( 'cdfs_clone_vehicle_meta_keys_filter', $meta_keys );

		foreach ( $meta_keys as $meta_key ) {
			$meta_values = get_post_custom_values( $meta_key, $vehicle->ID );
			foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				if ( function_exists( 'map_deep' ) ) {
					$meta_value = map_deep( $meta_value, array( __CLASS__, 'addslashes_to_strings_only' ) );
				} else {
					$meta_value = wp_slash( $meta_value );
				}
				add_post_meta( $new_vehicle_id, $meta_key, $meta_value );
			}
		}
	}

	/**
	 * Copies the taxonomies of a post to another post.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int     $new_vehicle_id The new vehicle ID.
	 * @param WP_Post $vehicle        The original vehicle.
	 */
	static function copy_taxonomies( $new_vehicle_id, $vehicle ) {
		global $wpdb;
		if ( isset( $wpdb->terms ) ) {
			// Clear default category (added by wp_insert_post).
			wp_set_object_terms( $new_vehicle_id, null, 'category' );

			$post_taxonomies = get_object_taxonomies( $vehicle->post_type );
			$exclude_list = array(
				'car_stock_number',
				'car_vin_number',
			);

			/**
			 * Filters the list of taxonomies to be excluded from vehicle cloning.
			 *
			 * @param array $exclude_list  List of meta field to exclude.
			 * @return array
			 */
			/**
			 * Filters the taxonomy exclude_list when copying a post.
			 *
			 * @param array $exclude_list The taxonomy exclude_list from the options.
			 *
			 * @return array
			 */
			$exclude_list = apply_filters( 'cdfs_clone_vehicle_taxonomies_exclude_list', $exclude_list );

			$taxonomies = array_diff( $post_taxonomies, $exclude_list );
			$taxonomies = array_values( $taxonomies );

			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $vehicle->ID, $taxonomy, array( 'orderby' => 'term_order' ) );
				$terms      = array();
				$num_terms  = count( $post_terms );
				for ( $i = 0; $i < $num_terms; $i++ ) {
					$terms[] = $post_terms[ $i ]->slug;
				}
				wp_set_object_terms( $new_vehicle_id, $terms, $taxonomy );
			}
		}
	}

	/**
	 * Adds slashes only to strings.
	 *
	 * @ignore
	 *
	 * @param mixed $value Value to slash only if string.
	 * @return string|mixed
	 */
	static function addslashes_to_strings_only( $value ) {
		return is_string( $value ) ? addslashes( $value ) : $value;
	}

}

CDFS_Cars::init();
