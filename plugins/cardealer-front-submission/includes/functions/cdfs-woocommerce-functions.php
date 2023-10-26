<?php
/**
 * CDFS WooCommerce Functions
 *
 * @author   PotenzaGlobalSolutions
 * @category Class
 * @package  CDFS/Classes
 * @version  1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add meta for add car and image limit
 */
add_action( 'woocommerce_product_options_general_product_data', 'cdfs_woocommerce_product_options_general_product_data', 999 );

if ( ! function_exists( 'cdfs_woocommerce_product_options_general_product_data' ) ) {
	/**
	 * Woocommerce product options general product data
	 */
	function cdfs_woocommerce_product_options_general_product_data() {
		global $post;
		$post_id = $post->ID;

		// Load product object.
		$product              = wc_get_product( $post_id );
		$php_max_file_uploads = ini_get( 'max_file_uploads' );

		if ( class_exists( 'Subscriptio' ) || class_exists( 'RP_SUB' ) ) {
			woocommerce_wp_text_input(
				array(
					'id'                => 'cdfs_car_quota',
					'placeholder'       => esc_html__( 'e.g. 100', 'cdfs-addon' ),
					'label'             => esc_html__( 'Cars Quota', 'cdfs-addon' ),
					'desc_tip'          => 'true',
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
						'min'  => '0',
					),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'cdfs_car_images_quota',
					'placeholder'       => esc_html__( 'e.g. 100', 'cdfs-addon' ),
					'label'             => esc_html__( 'Cars Images Quota', 'cdfs-addon' ),
					'desc_tip'          => 'true',
					'description'     => sprintf(
						wp_kses(
							/* translators: %s: url */
							__( 'Make sure your system PHP setting <strong>max_file_uploads</strong> has greater or equal value to <strong>Cars Images Quota</strong> value otherwise all image will not uploaded. Currently <strong>max_file_uploads</strong> set for <strong>%1$s</strong>', 'cdfs-addon' ),
							array(
								'strong' => array(),
							)
						),
						$php_max_file_uploads
					),
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
						'min'  => '0',
					),
				)
			);
		}
	}
}

add_action( 'woocommerce_process_product_meta', 'cdfs_woocommerce_process_product_meta' );

if ( ! function_exists( 'cdfs_woocommerce_process_product_meta' ) ) {
	/**
	 * Save meta.
	 *
	 * @param string $post_id .
	 */
	function cdfs_woocommerce_process_product_meta( $post_id ) {
		if ( isset( $_POST['cdfs_car_quota'] ) ) {
			$cdfs_car_quota = $_POST['cdfs_car_quota'];
			update_post_meta( $post_id, 'cdfs_car_quota', $cdfs_car_quota );
		}
		if ( isset( $_POST['cdfs_car_images_quota'] ) ) {
			$cdfs_car_images_quota = $_POST['cdfs_car_images_quota'];
			update_post_meta( $post_id, 'cdfs_car_images_quota', $cdfs_car_images_quota );
		}
	}
}

add_action( 'template_redirect', 'cdfs_template_redirect' );

if ( ! function_exists( 'cdfs_template_redirect' ) ) {
	/**
	 * Checkout page redirection.
	 */
	function cdfs_template_redirect() {
		if ( class_exists( 'Subscriptio' ) || class_exists( 'RP_SUB' ) ) {
			if ( function_exists( 'is_checkout' ) && is_checkout() ) {
				if ( isset( $_GET['add-to-cart'] ) && ! empty( $_GET['add-to-cart'] ) ) {
					$product_id = $_GET['add-to-cart'];
					if ( 'cars' !== get_post_type($product_id ) ) {
						WC()->cart->empty_cart();
						WC()->cart->add_to_cart( $product_id, 1 );
						wp_redirect( wc_get_checkout_url() );
					}
				}
			}
		}
	}
}

add_action( 'woocommerce_thankyou', 'cdfs_custom_process_order', 10, 1 );

if ( ! function_exists( 'cdfs_custom_process_order' ) ) {
	/**
	 * Add/update meta after order place.
	 *
	 * @param string $order_id .
	 */
	function cdfs_custom_process_order( $order_id ) {
		if ( class_exists( 'Subscriptio' ) || class_exists( 'RP_SUB' ) ) {
			$order   = new WC_Order( $order_id );
			$data    = $order->get_data();
			$items   = $order->get_items();
			$user_id = $order->get_user_id();

			$cdfs_car_limit = 0;
			$cdfs_img_limit = 0;

			foreach ( $items as $item ) {
				$product_id     = $item->get_product_id();
				$post_limit     = get_post_meta( $product_id, 'cdfs_car_quota', true );
				$image_limit    = get_post_meta( $product_id, 'cdfs_car_images_quota', true );
				$is_subscriptio = ( get_post_meta( $product_id, '_subscriptio', true ) );

				if ( 'yes' === $is_subscriptio ) {
					$user_car_limt = get_user_meta( $user_id, 'cdfs_car_limt', true );
					$user_img_limt = get_user_meta( $user_id, 'cdfs_img_limt', true );

					$user_car_limt = ( $user_car_limt < 1 ) ? 0 : intval( $user_car_limt );
					$user_img_limt = ( $user_img_limt < 1 ) ? 0 : intval( $user_img_limt );

					$post_limit  = intval( get_post_meta( $product_id, 'cdfs_car_quota', true ) );
					$image_limit = intval( get_post_meta( $product_id, 'cdfs_car_images_quota', true ) );

					$cdfs_car_limit = intval( $user_car_limt ) + intval( $post_limit );
					$cdfs_img_limit = intval( $image_limit );

					update_user_meta( $user_id, 'cdfs_car_limt', $cdfs_car_limit );
					update_user_meta( $user_id, 'cdfs_img_limt', $cdfs_img_limit );
				}
			}
		}
		return $order_id;
	}
}

add_action( 'woocommerce_order_status_cancelled', 'cdfs_action_woocommerce_cancelled_order', 99, 1 );
add_action( 'woocommerce_order_status_refunded', 'cdfs_action_woocommerce_cancelled_order', 99, 1 );

if ( ! function_exists( 'cdfs_action_woocommerce_cancelled_order' ) ) {
	/**
	 * Remove/add meta on order cancelled or refunded.
	 *
	 * @param string $order_id .
	 */
	function cdfs_action_woocommerce_cancelled_order( $order_id ) {
		if ( class_exists( 'Subscriptio' ) || class_exists( 'RP_SUB' ) ) {
			$subscriptions = ( class_exists( 'Subscriptio_Order_Handler' ) ) ? Subscriptio_Order_Handler::get_subscriptions_from_order_id( $order_id ) : array();
			foreach ( $subscriptions as $subscription ) {
				// Write transaction.
				$transaction = new Subscriptio_Transaction( null, 'order_cancellation' );
				$transaction->add_subscription_id( $subscription->id );
				$transaction->add_order_id( $order_id );
				$transaction->add_product_id( $subscription->product_id );
				$transaction->add_variation_id( $subscription->variation_id );

				try {
					// Cancel subscription.
					$subscription->cancel();

					// Update transaction.
					$transaction->update_result( 'success' );
					$transaction->update_note( __( 'Pending subscription canceled due to canceled order.', 'cdfs-addon' ), true );
				} catch ( Exception $e ) {
					$transaction->update_result( 'error' );
					$transaction->update_note( $e->getMessage() );
				}
			}

			$order          = new WC_Order( $order_id );
			$data           = $order->get_data();
			$items          = $order->get_items();
			$user_id        = $order->get_user_id();
			$cdfs_car_limit = $cdfs_img_limit = 0;

			foreach ( $items as $item ) {
				$product_id     = $item->get_product_id();
				$post_limit     = get_post_meta( $product_id, 'cdfs_car_quota', true );
				$image_limit    = get_post_meta( $product_id, 'cdfs_car_images_quota', true );
				$is_subscriptio = ( get_post_meta( $product_id, '_subscriptio', true ) );

				if ( 'yes' === $is_subscriptio ) {
					$user_car_limt = get_user_meta( $user_id, 'cdfs_car_limt', true );
					$user_img_limt = get_user_meta( $user_id, 'cdfs_img_limt', true );

					$user_car_limt = ( $user_car_limt < 1 ) ? 0 : intval( $user_car_limt );
					$user_img_limt = ( $user_img_limt < 1 ) ? 0 : intval( $user_img_limt );

					$post_limit  = intval( get_post_meta( $product_id, 'cdfs_car_quota', true ) );
					$image_limit = intval( get_post_meta( $product_id, 'cdfs_car_images_quota', true ) );

					$cdfs_car_limit = intval( $user_car_limt ) - intval( $post_limit );
					$cdfs_img_limit = intval( $image_limit );

					update_user_meta( $user_id, 'cdfs_car_limt', $cdfs_car_limit );
					update_user_meta( $user_id, 'cdfs_img_limt', $cdfs_img_limit );
				}
			}
		}
	}
}

function cdfs_advertise_item_statuses() {
	$price_formatted        = cdfs_get_advertisement_price_formatted();
	$advertisement_duration = cdfs_get_advertisement_duration();
	return apply_filters(
		'cdfs_advertise_item_statuses',
		array(
			'advertise_it'  => array(
				'label'  => sprintf( esc_html__( 'Advertise for %s for %s days.', 'cdfs-addon' ), $price_formatted, $advertisement_duration ),
				'color' => 'gray',
			),
			'added_to_cart'    => array(
				'label' => esc_html__( 'Added to Cart', 'cdfs-addon' ),
				'color' => 'yellow',
			),
			'pending'          => array(
				'label' => esc_html__( 'Pending Advertise', 'cdfs-addon' ),
				'color' => 'yellow',
			),
			'advertised'         => array(
				'label' => esc_html__( 'Advertised', 'cdfs-addon' ),
				'color' => 'green',
			),
			'expired'         => array(
				'label' => esc_html__( 'Expired', 'cdfs-addon' ),
				'color' => 'red',
			),
			'cancelled'         => array(
				'label' => esc_html__( 'Cancelled', 'cdfs-addon' ),
				'color' => 'red',
			),
		)
	);
}

function cdfs_wc_to_advertise_item_status( $wc_status = '' ) {
	$statuses = array(
		'new'               => 'advertise_it',
		'cart'              => 'added_to_cart',
		'pending'           => 'pending',
		'wc-pending'        => 'pending',
		'processing'        => 'pending',
		'wc-processing'     => 'pending',
		'on-hold'           => 'pending',
		'wc-on-hold'        => 'pending',
		'checkout-draft'    => 'pending',
		'wc-checkout-draft' => 'pending',
		'cancelled'         => 'cancelled',
		'wc-cancelled'      => 'cancelled',
		'refunded'          => 'advertise_it',
		'wc-refunded'       => 'advertise_it',
		'failed'            => 'advertise_it',
		'wc-failed'         => 'advertise_it',
		'completed'         => 'advertised',
		'wc-completed'      => 'advertised',
	);

	$fl_status = ( isset( $statuses[ $wc_status ] ) && ! empty( $statuses[ $wc_status ] ) ) ? $statuses[ $wc_status ] : false;
	return $fl_status;
}
