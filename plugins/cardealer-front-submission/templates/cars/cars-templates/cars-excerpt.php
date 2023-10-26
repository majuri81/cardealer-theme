<?php
/**
 * Car form image gallery upload
 *
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-templates/cars-excerpt.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'action_before_cars_excerpt' );

$address = '';
$lat     = '';
$lng     = '';

if ( isset( $id ) && $id ) {
	$vehicle_location = get_post_meta( $id, 'vehicle_location', true );
	if ( ! empty( $vehicle_location ) ) {
		$address = $vehicle_location['address'];
		$lng     = $vehicle_location['lng'];
		$lat     = $vehicle_location['lat'];
	}
}
?>
<div class="form-group">
	<?php
	// default settings - Kv_front_editor.php.
	$content   = ( isset( $id ) && $id ) ? get_the_excerpt( $id ) : '';
	$editor_id = 'cdfs-car-excerpt';
	$settings  = array(
		'wpautop'                 => true,          // use wpautop?.
		'media_buttons'           => false,         // show insert/upload button(s).
		'textarea_name'           => 'car_excerpt', // set the textarea name to something different, square brackets [] can be used here.
		'textarea_rows'           => 5,
		'tabindex'                => '',
		'remove_linebreaks'       => false,         // Don't remove line breaks.
		'convert_newlines_to_brs' => true,          // Convert newline characters to BR tags.
		'editor_css'              => '',            // extra styles for both visual and HTML editors buttons.
		'editor_class'            => 'cdfs_editor', // add extra class(es) to the editor textarea.
		'teeny'                   => false,         // output the minimal editor config used in Press This.
		'dfw'                     => false,         // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4).
		// 'tinymce'                 => true,          // load TinyMCE, can be used to pass settings directly to TinyMCE using an array().
		'tinymce' => array(
			'theme_advanced_buttons1' => 'bold,italic,underline',
			'theme_advanced_buttons2' => '',
			'theme_advanced_buttons3' => ''
		),
		'quicktags'               => true,          // load Quicktags, can be used to pass settings directly to Quicktags using an array().
	);
	wp_editor( $content, $editor_id, $settings );
	?>
</div>

<?php
do_action( 'action_after_cars_excerpt' );
