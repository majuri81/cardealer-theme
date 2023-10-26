<div class="cardealer-userdash-banner-wrap">
	<?php
	echo sprintf(
		'<img alt="%s" src="%s" class="%s" width="%s" height="%s">',
		esc_attr( $user->display_name ),
		esc_url( $banner_url ),
		esc_attr( 'cardealer-userdash-banner' ),
		1140,
		360
	);
	?>
</div>
