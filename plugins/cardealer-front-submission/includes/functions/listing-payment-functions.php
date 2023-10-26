<?php
function cdfs_listing_payment_enabled() {
	global $car_dealer_options;

	$listing_payment_enabled = ( isset( $car_dealer_options['cdfs_listing_payment_enabled'] ) && ! empty( $car_dealer_options['cdfs_listing_payment_enabled'] ) ) ? $car_dealer_options['cdfs_listing_payment_enabled'] : 'no';
	$listing_payment_enabled = filter_var( $listing_payment_enabled, FILTER_VALIDATE_BOOLEAN );
	$listing_payment_enabled = apply_filters( 'cdfs_listing_payment_enabled', $listing_payment_enabled );

	return $listing_payment_enabled;
}

function cdfs_get_item_listing_price() {
	global $car_dealer_options;

	$listing_price = ( isset( $car_dealer_options['cdfs_item_listing_price'] ) && ! empty( $car_dealer_options['cdfs_item_listing_price'] ) ) ? floatval( $car_dealer_options['cdfs_item_listing_price'] ) : '3';

	return apply_filters( 'cdfs_get_item_listing_price', $listing_price );

}

function cdfs_get_item_listing_price_formatted() {
	global $car_dealer_options;

	$price_formatted = '';
	$listing_price   = cdfs_get_item_listing_price();
	$currency_code   = ( isset( $car_dealer_options['cars-currency-symbol'] ) && ! empty( $car_dealer_options['cars-currency-symbol'] ) ) ? $car_dealer_options['cars-currency-symbol'] : '';
	$currency_symbol = ( function_exists( 'cdhl_get_currency_symbols' ) ) ? cdhl_get_currency_symbols( $currency_code ) : '$';
	$symbol_position = ( isset( $car_dealer_options['cars-currency-symbol-placement'] ) && ! empty( $car_dealer_options['cars-currency-symbol-placement'] ) ) ? $car_dealer_options['cars-currency-symbol-placement'] : '1';

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
		$listing_price
	);

	return apply_filters( 'cdfs_get_item_listing_price_formatted', $price_formatted );
}

function cdfs_get_item_listing_image_limit() {
	global $car_dealer_options;

	$image_limit = ( isset( $car_dealer_options['cdfs_listing_image_limit'] ) && '' !== $car_dealer_options['cdfs_listing_image_limit'] ) ? floatval( $car_dealer_options['cdfs_listing_image_limit'] ) : '5';

	return apply_filters( 'cdfs_get_item_listing_image_limit', $image_limit );

}

function cdfs_get_item_listing_duration() {
	global $car_dealer_options;

	$listing_duration = ( isset( $car_dealer_options['cdfs_listing_duration'] ) && ! empty( $car_dealer_options['cdfs_listing_duration'] ) ) ? intval( $car_dealer_options['cdfs_listing_duration'] ) : '30';

	return apply_filters( 'cdfs_get_item_listing_duration', $listing_duration );
}
