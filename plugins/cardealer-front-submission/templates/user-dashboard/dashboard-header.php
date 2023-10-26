<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package CarDealer
 */
get_header();

global $car_dealer_options;

$dashboard_sidebar = 'no_sidebar';
$sidebar_position  = 'right';
$sidebar_active    = false;

$container_size  = 'fixed';
$container_class = ( 'full' === $container_size ) ? 'container-fluid' : 'container';

do_action( 'cardealer_before_dealer_dashboard_wrap' );
?>
<div class="content-wrapper blog white-bg page-section-ptb">
	<div class="<?php echo esc_attr( $container_class ); ?>">
