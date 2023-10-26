<?php
/**
 * Advertisement
 */
function cdfs_advertise_item_get_allowed_post_statuses() {
	return apply_filters(
		'cdfs_advertise_item_get_allowed_post_statuses',
		array(
			'publish',
		)
	);
}

function cdfs_advertise_item_enabled() {
	global $car_dealer_options;

	$advertise_item_enabled = ( isset( $car_dealer_options['cdfs_advertise_item_enabled'] ) && ! empty( $car_dealer_options['cdfs_advertise_item_enabled'] ) ) ? $car_dealer_options['cdfs_advertise_item_enabled'] : 'no';
	$advertise_item_enabled = filter_var( $advertise_item_enabled, FILTER_VALIDATE_BOOLEAN );
	$advertise_item_enabled = apply_filters( 'cdfs_advertise_item_enabled', $advertise_item_enabled );

	return $advertise_item_enabled;
}

function cdfs_get_advertisement_price() {

	global $car_dealer_options;

	$advertisement_price = ( isset( $car_dealer_options['cdfs_advertisement_price'] ) && ! empty( $car_dealer_options['cdfs_advertisement_price'] ) ) ? floatval( $car_dealer_options['cdfs_advertisement_price'] ) : '3';

	return apply_filters( 'cdfs_get_advertisement_price', $advertisement_price );

}

function cdfs_get_advertisement_price_formatted() {
	global $car_dealer_options;

	$price_formatted     = '';
	$advertisement_price = cdfs_get_advertisement_price();
	$currency_code       = ( isset( $car_dealer_options['cars-currency-symbol'] ) && ! empty( $car_dealer_options['cars-currency-symbol'] ) ) ? $car_dealer_options['cars-currency-symbol'] : '';
	$currency_symbol     = ( function_exists( 'cdhl_get_currency_symbols' ) ) ? cdhl_get_currency_symbols( $currency_code ) : '$';
	$symbol_position     = ( isset( $car_dealer_options['cars-currency-symbol-placement'] ) && ! empty( $car_dealer_options['cars-currency-symbol-placement'] ) ) ? $car_dealer_options['cars-currency-symbol-placement'] : '1';

	$symbol_positions = array(
		'1' => '%1$s%2$s',
		'2' => '%2$s%1$s',
		'3' => '%1$s %2$s',
		'4' => '%2$s %1$s',
	);

	$curreny_format = $symbol_positions[ $symbol_position ];

	$price_formatted = sprintf(
		$curreny_format,
		$currency_symbol,
		$advertisement_price
	);

	return apply_filters( 'cdfs_get_advertisement_price_formatted', $price_formatted );
}

function cdfs_get_advertisement_duration() {
	global $car_dealer_options;

	$advertisement_duration = ( isset( $car_dealer_options['cdfs_advertisement_duration'] ) && ! empty( $car_dealer_options['cdfs_advertisement_duration'] ) ) ? $car_dealer_options['cdfs_advertisement_duration'] : '30';

	return intval( apply_filters( 'cdfs_get_advertisement_duration', $advertisement_duration ) );
}
