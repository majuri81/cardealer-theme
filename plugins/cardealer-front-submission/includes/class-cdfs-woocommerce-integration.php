<?php
/**
 * CDFS WooCommerce Integration
 *
 * @package  CDFS/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

defined( 'ABSPATH' ) || exit;

/**
 * CDFS_Woocommerce_Integration class.
 */
class CDFS_Woocommerce_Integration {

	/**
	 * Constructor for the class.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'cdfs_add_cronjob' ) );
		add_action( 'wp_loaded', array( $this, 'add_to_cart_advertise_item' ) );

		add_filter( 'cardealer_order_add_cart_item_data_advertise_item', array( $this, 'set_advertise_item_cart_item_data' ), 10, 4 );

		add_action( 'cardealer_order_after_added_cart_item_data_advertise_item', array( $this, 'set_advertise_item_cart_item_vehicle_data' ), 10, 4 );
		add_action( 'cardealer_add_checkout_order_line_item_meta_advertise_item', array( $this, 'add_advertise_item_checkout_order_line_item_meta' ), 10, 4 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hidden_order_itemmeta' ), 99 );
		add_action( 'cardealer_before_remove_cart_item_advertise_item', array( $this, 'unset_advertise_item_cart_item_vehicle_data' ), 10, 4 );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'add_custom_price' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'cdfs_woocommerce_order_status_changed' ), 10, 3 );
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'woocommerce_add_to_cart_redirect' ), 10, 2 );

		add_action( 'cardealer_wc_cars_order_status_changed_inward_advertise_item', array( $this, 'set_advertise_item_status' ), 10, 5 );
		add_action( 'cardealer_wc_cars_order_status_changed_outward_advertise_item', array( $this, 'set_advertise_item_status' ), 10, 5 );

		add_action( 'cardealer_wc_cars_order_status_changed_outward_listing_payment', array( $this, 'set_listing_payment_status' ), 10, 5 );

		// Unset featured by admin.
		add_action( 'cardealer_vehicle_unset_featured', array( $this, 'unset_vehicle_featured' ) );

		// Cart
		add_filter( 'woocommerce_cart_item_product', array( $this, 'cart_item_product' ), 10, 3 );
	}

	/**
	 * Set update vehicle status.
	 */
	public function cdfs_add_cronjob() {
		// Listing Payment expiry cron job.
		add_action( 'cdfs_item_listing_expiry_cron_action', array( $this, 'cdfs_run_item_listing_expiry_cron' ) );
		if ( ! wp_next_scheduled ( 'cdfs_item_listing_expiry_cron_action' ) ) {
			wp_schedule_event( time(), 'hourly', 'cdfs_item_listing_expiry_cron_action' );
		}

		// Advertisement expiry cron job.
		add_action( 'cdfs_advertise_item_expiry_cron_action', array( $this, 'cdfs_run_advertise_item_expiry_cron' ) );
		if ( ! wp_next_scheduled ( 'cdfs_advertise_item_expiry_cron_action' ) ) {
			wp_schedule_event( time(), 'hourly', 'cdfs_advertise_item_expiry_cron_action' );
		}
	}

	/**
	 * Set update vehicle status.
	 */
	public function cdfs_run_item_listing_expiry_cron() {

		$item_listing_args = array(
			'post_type'      => 'cars',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => 'cdfs_listing_type',
					'value'   => 'listing_payment',
					'compare' => '=',
				),
			),
		);
		$item_listing_query = new WP_Query( $item_listing_args );

		if ( $item_listing_query->have_posts() ) {
			while ( $item_listing_query->have_posts() ) {
				$item_listing_query->the_post();

				// $cdfs_item_listing_expiry        = get_post_meta( get_the_ID(), 'cdfs_item_listing_expiry', true );
				$cdfs_item_listing_expiry_timestamp = get_post_meta( get_the_ID(), 'cdfs_item_listing_expiry_timestamp', true );
				// $expiry_datestring               = strtotime( $cdfs_item_listing_expiry );
				$expiry_timestamp                   = $cdfs_item_listing_expiry_timestamp;
				$current_timestamp                  = strtotime( current_time( 'mysql' ) );

				if ( (int) $current_timestamp > (int) $expiry_timestamp ) {
					$update = wp_update_post( array(
						'ID'          => get_the_ID(),
						'post_status' => 'draft',
					) );
					if ( $update ) {
						delete_post_meta( get_the_ID(), 'cdfs_item_listing_expiry' );
						delete_post_meta( get_the_ID(), 'cdfs_item_listing_expiry_timestamp' );
						delete_post_meta( get_the_ID(), 'cdfs_listing_duration' );
						delete_post_meta( get_the_ID(), 'cdfs_listing_type' );
						delete_post_meta( get_the_ID(), 'cdfs_subscription_id' );
					}
				}
			}
		}
		wp_reset_postdata();
	}

	public function cdfs_run_advertise_item_expiry_cron() {
		$current_date        = new DateTime();
		$current_timestamp   = $current_date->getTimestamp();

		$advertise_item_args = array(
			'post_type'      => 'cars',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => 'cdfs_advertise_item_status',
					'value'   => 'advertised',
					'compare' => '=',
				),
				array(
					'key'     => 'cdfs_advertise_item_expiry_date',
					'value'   => $current_timestamp,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				),
			),
		);
		$advertise_item_query = new WP_Query( $advertise_item_args );

		if ( $advertise_item_query->have_posts() ) {
			while ( $advertise_item_query->have_posts() ) {
				$advertise_item_query->the_post();
				$vehicle_id = get_the_ID();

				delete_post_meta( $vehicle_id, 'cdfs_advertise_item_status' );
				delete_post_meta( $vehicle_id, 'cdfs_advertise_item_expiry_date' );
				delete_post_meta( $vehicle_id, 'featured' );
				delete_post_meta( $vehicle_id, 'advertisement_by_user' );
			}
		}
		wp_reset_postdata();
	}

	/**
	 * Set update vehicle status.
	 */
	public function cdfs_woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {

		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $item_id => $item ) {
			$cd_item_type    = $item->get_meta( 'cardealer_order_type', true );
			$cardealer_order_types = cardealer_order_types();
			$cardealer_order_type  = ( ! empty( $cd_item_type ) && array_key_exists( $cd_item_type, $cardealer_order_types ) ) ? $cd_item_type : 'sell_vehicle';

			if ( 'listing_payment' === $cardealer_order_type ) {
				$cd_vehicle_id = $item->get_meta( '_cd_vehicle_id', true );
				if ( $cd_vehicle_id ) {
					$cdfs_listing_type = get_post_meta( $cd_vehicle_id, 'cdfs_listing_type', true );
					if ( 'listing_payment' === $cdfs_listing_type ) {
						if ( 'completed' === $new_status ) {
							wp_update_post( array(
								'ID'          => $cd_vehicle_id,
								'post_status' => 'publish',
							) );
						/*
						} elseif ( 'completed' === $old_status ) {
							wp_update_post( array(
								'ID'          => $cd_vehicle_id,
								'post_status' => 'draft',
							) );
						*/
						}
					}
				}
			}
		}
	}

	public function set_listing_payment_status( $item_id, $item, $order, int $vehicle_id, $action ) {
		wp_update_post( array(
			'ID'          => $vehicle_id,
			'post_status' => 'draft',
		) );
		update_post_meta( $vehicle_id, 'cdfs_subscription_id', 'free' );
		update_post_meta( $vehicle_id, 'cdfs_listing_type', 'free' );
		delete_post_meta( $vehicle_id, 'cdfs_item_listing_expiry_timestamp' );
		delete_post_meta( $vehicle_id, 'cdfs_listing_duration' );
	}

	/**
	 * Add to cart advertise item.
	 */
	public function add_to_cart_advertise_item() {
		global $car_dealer_options;

		if ( ! isset( $_GET['cdfs_advertise_item'] ) || ! is_numeric( wp_unslash( $_GET['cdfs_advertise_item'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}

		$url       = cdfs_get_cardealer_dashboard_endpoint_url( 'my-items' );
		$demo_mode = isset( $car_dealer_options['demo_mode'] ) ? (bool) $car_dealer_options['demo_mode'] : '';

		if ( $demo_mode ) {
			cdfs_add_notice( esc_html__( "The site is currently in demo mode, advertise is disabled.", 'cdfs-addon' ), 'error' );
			wp_safe_redirect( $url );
			die();
		}

		$vehicle_id = (int) sanitize_text_field( wp_unslash( $_GET['cdfs_advertise_item'] ) );
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'advertise_item_nonce' ) ) {
			cdfs_add_notice( esc_html__( 'Unable to verify security code. Please try again.', 'cdfs-addon' ), 'error' );
		} else {
			$cdfs_advertise_item_enabled = cdfs_advertise_item_enabled();
			if ( ! $cdfs_advertise_item_enabled ) {
				cdfs_add_notice( esc_html__( 'This feature is not enabled.', 'cdfs-addon' ), 'error' );
			} else {
				$user = wp_get_current_user();
				if ( ! $user->exists() ) {
					cdfs_add_notice( esc_html__( 'Please log in to process this request.', 'cdfs-addon' ), 'error' );
				} else {
					$vehicle = get_post( $vehicle_id );
					if ( ! $vehicle ) {
						cdfs_add_notice( esc_html__( 'The vehicle does not exist.', 'cdfs-addon' ), 'error' );
					} else {
						if ( ! is_a( $vehicle, 'WP_Post' ) || 'cars' !== $vehicle->post_type ) {
							cdfs_add_notice( esc_html__( 'Invalid vehicle ID.', 'cdfs-addon' ), 'error' );
						} else {
							if ( (int) $user->ID !== (int) $vehicle->post_author ) {
								cdfs_add_notice( esc_html__( 'You are not allowed to advertise this item.', 'cdfs-addon' ), 'error' );
							} else {
								$allowed_post_status = cdfs_advertise_item_get_allowed_post_statuses();
								if ( ! in_array( $vehicle->post_status, $allowed_post_status, true ) ) {
									cdfs_add_notice( esc_html__( 'This item is not valid to advertise it.', 'cdfs-addon' ), 'error' );
								} else {
									global $car_dealer_options;
									$listing_price = cdfs_get_advertisement_price();
									$checkout_url  = wc_get_checkout_url() . '?add-to-cart=' . $vehicle_id;
									$url = add_query_arg(
										array(
											'add-to-cart'         => $vehicle_id,
											'cardealer_order_type' => 'advertise_item',
										),
										wc_get_checkout_url()
									);
								}
							}
						}
					}
				}
			}
		}
		wp_safe_redirect( $url );
		die();
	}

	/**
	 * Set advertisement status.
	 *
	 * @param array      $cart_item_data  Array of cart item data.
	 * @param string/int $vehicle_id      Product/vehicle ID.
	 * @param string/int $variation_id    Variation ID.
	 * @param string/int $quantity        Quantity.
	 * @return array
	 */
	public function set_advertise_item_cart_item_data( $cart_item_data, $vehicle_id, $variation_id, $quantity ) {
		$listing_duration = cdfs_get_advertisement_duration();
		$listing_price    = cdfs_get_advertisement_price();

		$cart_item_data['_cdfs_advertisement_duration'] = $listing_duration;
		$cart_item_data['_cdfs_advertisement_price']    = $listing_price;

		return $cart_item_data;
	}

	/**
	 * Set advertisement status.
	 *
	 * @param array      $cart_item_data  Array of cart item data.
	 * @param string/int $vehicle_id      Product/vehicle ID.
	 * @param string/int $variation_id    Variation ID.
	 * @param string/int $quantity        Quantity.
	 * @return array
	 */
	public function set_advertise_item_cart_item_vehicle_data( $cart_item_data, $vehicle_id, $variation_id, $quantity ) {
		update_post_meta( $vehicle_id, 'cdfs_advertise_item_status', 'added_to_cart' );
	}

	public function add_advertise_item_checkout_order_line_item_meta( $item, $cart_item_key, $values, $order ) {
		if ( array_key_exists( '_cdfs_advertisement_duration', $values ) ) {
			$item->add_meta_data( '_cdfs_advertisement_duration', $values['_cdfs_advertisement_duration'] );
		}
		if ( array_key_exists( '_cdfs_advertisement_price', $values ) ) {
			$item->add_meta_data( '_cdfs_advertisement_price', $values['_cdfs_advertisement_price'] );
		}
	}

	public function hidden_order_itemmeta( $itemmetas ) {
		$itemmetas[] = '_cdfs_advertisement_duration';
		$itemmetas[] = '_cdfs_advertisement_price';
		return $itemmetas;
	}

	/**
	 * Set advertisement status.
	 *
	 * @param array      $cart_item_data  Array of cart item data.
	 * @param string/int $product_id      Product ID.
	 * @param string/int $variation_id    Variation ID.
	 * @param string/int $quantity        Quantity.
	 * @return array
	 */
	public function unset_advertise_item_cart_item_vehicle_data( $cart_item_key, $cart_item_data, $wc_cart, $vehicle_id ) {
		delete_post_meta( $vehicle_id, 'cdfs_advertise_item_status' );
		// delete_post_meta( $vehicle_id, 'cdfs_advertisement_duration' );
		// delete_post_meta( $vehicle_id, 'cdfs_advertisement_price' );
	}

	// Set price dynamically.
	public function add_custom_price( $cart_object ) {
		$cardealer_order_types = cardealer_order_types();

		$product_id = false;

		if (
			( isset( $_GET['add-to-cart'] ) && is_numeric( wp_unslash( $_GET['add-to-cart'] ) ) )
			&& ( isset( $_GET['cardealer_order_type'] ) && 'advertise_item' === $_GET['cardealer_order_type'] )
		) {
			$product_id = absint( wp_unslash( $_REQUEST['add-to-cart'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		foreach ( $cart_object->cart_contents as $key => $value ) {

			if ( isset( $value['cardealer_order_type'] ) && $value['cardealer_order_type'] ) {
				if ( 'advertise_item' === $value['cardealer_order_type'] ) {
					$listing_price = cdfs_get_advertisement_price();
					$value['data']->set_price( $listing_price );
				}

				if ( 'listing_payment' === $value['cardealer_order_type'] ) {
					$listing_price = cdfs_get_item_listing_price();
					$value['data']->set_price( $listing_price );
				}
			}

			$cart_object->cart_contents[ $key ] = $value;
		}

	}

	public function woocommerce_add_to_cart_redirect( $url, $product ) {
		if ( $product && 'cars' === get_post_type( $product->get_id() ) ) {
			if (
				( isset( $_GET['add-to-cart'] ) && is_numeric( wp_unslash( $_GET['add-to-cart'] ) ) )
				&& ( isset( $_GET['cardealer_order_type'] ) && 'advertise_item' === $_GET['cardealer_order_type'] )
			) {
				$url = wc_get_checkout_url();
			}
		}
		return $url;
	}

	public function set_advertise_item_status( $item_id, $item, $order, int $vehicle_id, $action ) {
		$order_status = $order->get_status();
		// var_dump( $action );
		// var_dump( current_action() );
		// var_dump( $order->get_status() );
		// die();
		$listing_duration        = $item->get_meta( '_cdfs_advertisement_duration', true );
		$listing_price           = $item->get_meta( '_cdfs_advertisement_price', true );
		$expiry_timestamp        = $this->get_expiry_timestamp( $listing_duration );
		$advertise_item_status = cdfs_wc_to_advertise_item_status( $order_status );

		// var_dump( $listing_duration );
		// var_dump( $listing_price );
		// var_dump( $expiry_timestamp );
		// var_dump( $advertise_item_status );

		update_post_meta( $vehicle_id, 'cdfs_advertise_item_status', $advertise_item_status );

		if ( 'advertised' === $advertise_item_status ) {
			update_post_meta( $vehicle_id, 'cdfs_advertise_item_expiry_date', $expiry_timestamp );
			update_post_meta( $vehicle_id, 'featured', 1 );
			$seller = $order->get_user();
			if ( $seller ) {
				update_post_meta( $vehicle_id, 'advertisement_by_user', $seller->ID );
			}
		} else {
			delete_post_meta( $vehicle_id, 'cdfs_advertise_item_expiry_date' );
			delete_post_meta( $vehicle_id, 'featured' );
			delete_post_meta( $vehicle_id, 'advertisement_by_user' );
		}
		// die();
	}

	public function get_expiry_timestamp( $dureation ) {
		$current_date         = new DateTime();
		$current_timestamp    = $current_date->getTimestamp();
		$expire_timestamp_new = strtotime( sprintf( '+%s days', $dureation ), $current_timestamp );
		$expire_date_new      = new DateTime();
		$expire_date          = $expire_date_new->setTimestamp( $expire_timestamp_new );
		$expire_timestamp     = $expire_date->getTimestamp();		var_dump( $expire_timestamp );

		// echo "<h3>Current Time</h3>";
		// var_dump( $current_timestamp );
		// echo '<pre>';
		// print_r( $current_date );
		// echo '</pre>';

		// echo "<h3>Expire Time</h3>";
		// var_dump( $expire_timestamp );
		// echo '<pre>';
		// print_r( $expire_date );
		// echo '</pre>';

		return $expire_timestamp;
	}

	public function unset_advertise_item( $item_id, $item, $order, int $vehicle_id, $action ) {
		/*
		$vehicle_id            = (int) $item->get_meta( '_cd_vehicle_id', true );
		$qty                   = (int) $item->get_quantity();
		$vehicle_stock_reduced = $item->get_meta( '_vehicle_stock_reduced', true );

		if ( $vehicle_id && count( $qty ) > 0 && ! $vehicle_stock_reduced ) {
			$vehicle_in_stock = (int) cardealer_get_vehicle_stock( $vehicle_id );
			$new_stock        = $vehicle_in_stock - $qty;

			$stock_update_status = update_post_meta( $vehicle_id, 'total_vehicle_in_stock', $new_stock );

			if ( $stock_update_status ) {
				if ( $new_stock <= 0 ) {
					update_post_meta( $vehicle_id, 'car_status', 'sold' );
				}

				$item->add_meta_data( '_vehicle_stock_reduced', $qty, true );
				$item->save();
			}
		}
		*/
	}

	public function unset_vehicle_featured( $post_id ) {
		delete_post_meta( $post_id, 'cdfs_advertise_item_status' );
		delete_post_meta( $post_id, 'cdfs_advertise_item_expiry_date' );
		delete_post_meta( $post_id, 'featured' );
		delete_post_meta( $post_id, 'advertisement_by_user' );
	}

	public function cart_item_product( $product, $cart_item, $cart_item_key ) {
		if (
			( isset( $cart_item['cardealer_order_type'] ) && 'advertise_item' === $cart_item['cardealer_order_type'] )
			&& ( isset( $cart_item['_cdfs_advertisement_price'] ) && ! empty( $cart_item['_cdfs_advertisement_price'] ) )
		) {
			$product->set_price( $cart_item['_cdfs_advertisement_price'] );
		}
		return $product;
	}

}
new CDFS_Woocommerce_Integration();



function bbloomer_only_one_in_cart( $passed, $added_product_id ) {
   wc_empty_cart();
   return $passed;
}
// add_filter( 'woocommerce_add_to_cart_validation', 'bbloomer_only_one_in_cart', 9999, 2 );

