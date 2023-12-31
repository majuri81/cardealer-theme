<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package CarDealer
 */

$custom_sidebar = get_post_meta( get_the_ID(), 'custom_sidebar', true );
if ( $custom_sidebar ) {
	if ( is_active_sidebar( $custom_sidebar ) ) {
		dynamic_sidebar( $custom_sidebar );
	}
} elseif ( is_active_sidebar( 'sidebar-left' ) ) {
	dynamic_sidebar( 'sidebar-left' );
} else {
	if ( current_user_can( 'administrator' ) ) {
		?>
		<span>
			<?php
			echo sprintf(
				wp_kses(
					/* translators: 1: URL */
					__( 'No widgets added in left sidebar.<br>Click <a href="%s">here</a> to add widgets.', 'cardealer' ),
					array(
						'a'  => array(
							'class' => array(),
							'href'  => array(),
						),
						'br' => array(),
					)
				),
				esc_url( admin_url( 'widgets.php' ) )
			);
			?>
		</span>
		<?php
	} else {
		echo esc_html__( 'No widgets founds.', 'cardealer' );
	}
}
?>
<span></span>
