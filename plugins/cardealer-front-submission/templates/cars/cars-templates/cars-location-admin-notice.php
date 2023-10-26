<?php
if ( current_user_can( 'administrator' ) ) {
	?>
	<p><?php printf( wp_kses( __( 'The Google Maps API key is not provided. Please make sure that Google Maps API keys have been added to the <a href="%1$s" target="_blank">Theme Options > Google API Settings > "Google Maps API Key"</a> field. Also, ensure that the API Account and API Keys are configured and working properly.', 'cdfs-addon' ), array( 'a' => array( 'href' => true, 'target' => true ) ) ), esc_url( admin_url( 'admin.php?page=cardealer' ) ) ); ?></p>
	<p><?php echo wp_kses( __( '<b>Note</b>: This information will be visible only to administrators. No visitors will see this notice.', 'cdfs-addon' ), array( 'b' => array() ) ); ?></p>
	<?php
}
