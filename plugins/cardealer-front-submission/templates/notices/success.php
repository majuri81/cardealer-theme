<?php
/**
 * Show messages
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/notices/success.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! $messages ) {
	return;
}

foreach ( $messages as $message ) :
	?>
	<div class="cdfs-message cdfs-message-success"><?php echo wp_kses_post( $message ); ?></div>
	<?php
endforeach;
