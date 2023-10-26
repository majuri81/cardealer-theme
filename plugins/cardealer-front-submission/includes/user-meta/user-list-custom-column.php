<?php
/**
 *
 * Functions for the template functions for car pages.
 *
 * @author   PotenzaGlobalSolutions
 * @package  CDFS
 */

if ( ! function_exists( 'cdfs_add_column' ) ) {
	/**
	 * Add Column
	 *
	 * @param array $column Columns.
	 */
	function cdfs_add_column( $column ) {
		$column['cdfs_user_status'] = esc_html__( 'Status', 'cdfs-addon' );
		return $column;
	}
}
add_filter( 'manage_users_columns', 'cdfs_add_column' );

if ( ! function_exists( 'cdfs_add_column_value' ) ) {
	/**
	 * This will add column value in user list table
	 *
	 * @param array  $val value.
	 * @param string $column_name column name.
	 * @param int    $user_id user id.
	 */
	function cdfs_add_column_value( $val, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'cdfs_user_status':
				$status = ( ! empty( $val ) ) ? $val : '&mdash;';
				$user   = get_userdata( $user_id );
				if ( $user instanceof WP_User ) {
					// If user login with roles other than car_dealer.
					if ( in_array( 'car_dealer', $user->roles, true ) || in_array( 'subscriber', $user->roles ) ) {
						$cdfs_user_status = get_user_meta( $user_id, 'cdfs_user_status', true );
						$status           = ( isset( $cdfs_user_status ) && 'pending' === $cdfs_user_status ) ? esc_html__( 'Pending', 'cdfs-addon' ) : esc_html__( 'Active', 'cdfs-addon' );
					}
				}
				return $status;
			default:
		}
	}
}
add_filter( 'manage_users_custom_column', 'cdfs_add_column_value', 99, 3 );
