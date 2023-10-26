<?php
/**
 * Wishlist
 *
 * @author   PotenzaGlobalSolutions
 * @category Class
 * @package  CDFS/Classes
 * @version  1.0.0
 */

/**
 * Wishlist class.
 */
class CDFS_Wishlist {
	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {

		add_action( 'wp_ajax_add_cdfs_wishlist', array( $this, 'add_cdfs_wishlist' ) );
		add_action( 'wp_ajax_nopriv_add_cdfs_wishlist', array( $this, 'add_cdfs_wishlist' ) );

		add_action( 'wp_ajax_remove_cdfs_wishlist', array( $this, 'remove_cdfs_wishlist' ) );
		add_action( 'wp_ajax_nopriv_remove_cdfs_wishlist', array( $this, 'remove_cdfs_wishlist' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_footer', array( $this, 'cdfs_wishlist_msg' ) );
	}

	/**
	 * Add cdfs wishlist ajax call.
	 *
	 * @return void
	 */
	public function load_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'cdfs-wishlist-js', trailingslashit( CDFS_URL ) . 'js/cdfs-wishlist' . $suffix . '.js', array( 'jquery' ), '1.2.8.1', true );

		wp_localize_script(
			'cdfs-wishlist-js',
			'cdfs_wishlist_obj',
			array(
				'cdfs_wishlist_url' => cdfs_get_cardealer_dashboard_endpoint_url( 'my-wishlist' ),
				'cdfs_nonce'        => wp_create_nonce( 'cdfs_nonce' ),
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_enqueue_script( 'cdfs-wishlist-js' );
	}

	/**
	 * Add cdfs wishlist ajax call.
	 *
	 * @return void
	 */
	public function add_cdfs_wishlist() {

		$data_response  = array();
		$cdfs_nonce = isset( $_POST['ajax_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ajax_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $cdfs_nonce, 'cdfs_nonce' ) ) {
			wp_die();
		}

		$car_id = isset( $_POST['car_id'] ) ? sanitize_text_field( wp_unslash( $_POST['car_id'] ) ) : '';

		if ( $this->is_car_in_wishlist( $car_id ) ) {
			$count_cars = $this->count_cars();
			$data_response  = array(
				'added' => false,
				'count' => $count_cars,
			);
		} else {
			$this->add_car_in_wishlist( $car_id );
			$count_cars = $this->count_cars();
			$data_response  = array(
				'added' => true,
				'count' => $count_cars,
			);
		}

		echo wp_json_encode( $data_response );
		wp_die();

	}

	/**
	 * Remove cdfs wishlist ajax call
	 *
	 * @return void
	 */
	public function remove_cdfs_wishlist() {
		$car_id = isset( $_POST['car_id'] ) ? sanitize_text_field( wp_unslash( $_POST['car_id'] ) ) : '';

		$cdfs_nonce = isset( $_POST['ajax_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ajax_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $cdfs_nonce, 'cdfs_nonce' ) ) {
			wp_die();
		}

		$this->remove_car_from_wishlist( $car_id );

		$count_cars = $this->count_cars();

		echo wp_json_encode( $count_cars );
		wp_die();
	}

	/**
	 * Check if car in wishlist.
	 *
	 * @param int $car_id car id.
	 *
	 * @return bool
	 */
	public function is_car_in_wishlist( $car_id ) {
		$_wishlist = $this->get_wishlist();

		if ( in_array( $car_id, $_wishlist ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return true;
		}

		return false;
	}

	/**
	 * Add car in wishlist.
	 *
	 * @param int $car_id car id.
	 *
	 * @return void
	 */
	public function add_car_in_wishlist( $car_id ) {
		$_wishlist = $this->get_wishlist();

		if ( ! in_array( $car_id, $_wishlist ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			$_wishlist[] = $car_id;
			$this->set_wishlist( $_wishlist );
		}
	}

	/**
	 * Remove car from wishlist.
	 *
	 * @param int $car_id car id.
	 *
	 * @return void
	 */
	public function remove_car_from_wishlist( $car_id ) {
		$_wishlist = $this->get_wishlist();

		if ( in_array( $car_id, $_wishlist ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			$key = array_search( $car_id, $_wishlist ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( false !== $key ) {
				unset( $_wishlist[ $key ] );
			}

			$this->set_wishlist( $_wishlist );
		}
	}

	/**
	 * Get wishlist.
	 */
	public function get_wishlist() {

		$_wishlist      = array();
		$_wishlist_data = '';

		if ( is_user_logged_in() ) {

			$user_id   = get_current_user_id();
			$user_meta = get_user_option( 'cdfs_wishlist', $user_id );

			if ( is_serialized( $user_meta ) ) {
				$_wishlist = maybe_unserialize( $user_meta );
			}
		} else {

			if ( is_multisite() && isset( $_COOKIE[ 'cdfs_wishlist_' . get_current_blog_id() ] ) ) {
				$_wishlist_data = sanitize_text_field( wp_unslash( $_COOKIE[ 'cdfs_wishlist_' . get_current_blog_id() ] ) );
			} elseif ( isset( $_COOKIE['cdfs_wishlist'] ) ) {
				$_wishlist_data = sanitize_text_field( wp_unslash( $_COOKIE['cdfs_wishlist'] ) );
			}

			if ( $_wishlist_data && is_serialized( $_wishlist_data ) ) {
				$_wishlist = maybe_unserialize( $_wishlist_data );
			}
		}

		if ( class_exists( 'SitePress' ) && $_wishlist ) {
			$tlp_wishlist = array();
			foreach ( $_wishlist as $wishlist ) {
				$tlp_wishlist[] = apply_filters( 'wpml_object_id', $wishlist, 'car' );
			}
			$_wishlist = $tlp_wishlist;
		}

		if ( ! is_array( $_wishlist ) ) {
			$_wishlist = array();
		}

		return $_wishlist;
	}

	/**
	 * Set wishlist.
	 */
	public function set_wishlist( $_wishlist = array() ) {

		if ( ! is_serialized( $_wishlist ) ) {
			$_wishlist_data = maybe_serialize( $_wishlist );
		}

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			update_user_option( $user_id, 'cdfs_wishlist', $_wishlist_data );
		} else {
			if ( is_multisite() ) {
				setcookie( 'cdfs_wishlist_' . get_current_blog_id(), $_wishlist_data, time() + 86400, '/' );
				$_COOKIE[ 'cdfs_wishlist_' . get_current_blog_id() ] = $_wishlist_data;
			} else {
				setcookie( 'cdfs_wishlist', $_wishlist_data, time() + 86400, '/' );
				$_COOKIE['cdfs_wishlist'] = $_wishlist_data;
			}
		}
	}

	/**
	 * Get cars count
	 */
	public function count_cars() {
		$_wishlist  = $this->get_wishlist();
		$count_cars = count( $_wishlist );

		return $count_cars;
	}

	/**
	 * Wishlist Massgages.
	 */
	public function cdfs_wishlist_msg() {
		global $car_dealer_options;

		$vehicle_added_text = isset( $car_dealer_options['vehicle_added_text'] ) ? $car_dealer_options['vehicle_added_text'] : esc_html__( 'Vehicle added!', 'cdfs-addon' );
		if ( $vehicle_added_text ) {
			?>
			<div class="cdfs-wishlist-popup-message vehicle-added">
				<div class="cdfs-wishlist-message">
					<?php echo esc_html( $vehicle_added_text ); ?>
				</div>
			</div>
			<?php
		}

		$already_in_wishlist_text = isset( $car_dealer_options['already_in_wishlist_text'] ) ? $car_dealer_options['already_in_wishlist_text'] : esc_html__( 'Vehicle already in wishlist!', 'cdfs-addon' );
		if ( $already_in_wishlist_text ) {
			?>
			<div class="cdfs-wishlist-popup-message already-in-wishlist">
				<div class="cdfs-wishlist-message">
					<?php echo esc_html( $already_in_wishlist_text ); ?>
				</div>
			</div>
			<?php
		}
	}
}

$cdfs_wishlist = new CDFS_Wishlist();
$cdfs_wishlist->init();
