<?php
/**
 * Show error messages
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/notices/error.php.
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
?>
<ul class="cdfs-message cdfs-message-error cardealer-error">
	<?php foreach ( $messages as $message ) : ?>
		<li><?php echo wp_kses_post( $message ); ?></li>
	<?php endforeach; ?>
</ul>
