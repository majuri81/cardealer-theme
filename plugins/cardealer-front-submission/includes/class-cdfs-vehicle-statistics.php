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
class CDFS_Vehicle_Statistics {

	/**
	 * Constructor function.
	 */
	public function __construct() {
		$this->init();
	}

	public static function get_tble_name() {
		global $wpdb;
		return $wpdb->prefix . 'cdfs_vehicle_statistics';
	}

	/**
	 * Init
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'create_vehicle_statistics_table' ), 10 );
		add_action( 'wp', array( __CLASS__, 'vehicle_view_count' ), 10 );
	}

	/**
	 * Create table for vehicle statistics
	 */
	public static function vehicle_view_count() {
		global $wpdb;

		if ( is_singular( 'cars' ) ) {

			$table_name         = self::get_tble_name();
			$vehicle_data_array = array();
			$today_date         = date( 'Y-m-d' );
			$vehicle_id         = get_the_ID();
			$data               = array(
				'vehicle_id' => $vehicle_id,
				'date'       => $today_date
			);

			if ( is_multisite() ) {
				$cookie_name = 'cdfd_vehicle_viewed_' . get_current_blog_id();
			} else {
				$cookie_name = 'cdfd_vehicle_viewed';
			}

			if ( isset( $_COOKIE[ $cookie_name ] ) && $_COOKIE[ $cookie_name ] ) {
				$vehicle_data_array = maybe_unserialize( sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) );
				if ( ! in_array( $vehicle_id, $vehicle_data_array ) ) {

					$vehicle_data_array[] = $vehicle_id;
					$vehicle_data         = maybe_serialize( $vehicle_data_array );

					setcookie( $cookie_name, $vehicle_data, time() + 86400, '/' );
					$_COOKIE[ $cookie_name ] = $vehicle_data;

					self::vehicle_view_count_update( $data );
				}
			} else {
				$vehicle_data_array[] = $vehicle_id;
				$vehicle_data         = maybe_serialize( $vehicle_data_array );

				setcookie( $cookie_name, $vehicle_data, time() + 86400, '/' );
				$_COOKIE[ $cookie_name ] = $vehicle_data;

				self::vehicle_view_count_update( $data );
			}
		}
	}

	public static function vehicle_view_count_update( $data ) {
		global $wpdb;

		$date       = $data['date'];
		$vehicle_id = $data['vehicle_id'];
		$table_name = self::get_tble_name();
		$result     = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE `vehicle_id` = %s AND `date` = %s",
				$vehicle_id,
				$date
			),
			ARRAY_A
		);

		if ( isset( $result[ 'vehicle_views' ] ) && $result[ 'vehicle_views' ] ) {
			$vehicle_views = $result[ 'vehicle_views' ] + 1;
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table_name} SET vehicle_views = %s WHERE vehicle_id = %s AND date = %s",
					$vehicle_views,
					$vehicle_id,
					$date
				)
			);
		} else {
			$wpdb->insert(
				$table_name,
				array(
					'vehicle_id'    => $vehicle_id,
					'vehicle_views' => 1,
					'date'          => $date,
				)
			);
		}
	}

	/**
	 * Create table for vehicle statistics
	 */
	public static function create_vehicle_statistics_table() {
		global $wpdb;

		$table_name = self::get_tble_name();
		if ( ! $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpdb->prefix . 'cdfs_vehicle_statistics"' ) == $table_name ) {

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				  id mediumint(8) unsigned NOT NULL auto_increment,
				  vehicle_id mediumint(8) NOT NULL,
				  vehicle_views varchar(255) NULL,
				  date date NULL,
				  PRIMARY KEY  (id)
				) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			wp_reset_query();
		}
	}

	public static function get_vehicle_view_count( $vehicle_id, $date = '' ) {
		global $wpdb;

		$table_name = self::get_tble_name();

		if ( $date ) {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT {$table_name}.vehicle_views AS vehicle_views FROM {$table_name} WHERE `vehicle_id` = %s AND `date` = %s",
					$vehicle_id,
					$date
				),
				ARRAY_A
			);
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT SUM({$table_name}.vehicle_views) AS vehicle_views FROM {$table_name} WHERE `vehicle_id` = %s",
					$vehicle_id,
				),
				ARRAY_A
			);
		}

		return isset( $result['vehicle_views'] ) ? $result['vehicle_views'] : 0;
	}
}

new CDFS_Vehicle_Statistics();
