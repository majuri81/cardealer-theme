<?php
$cdfs_show_email    = filter_var( get_the_author_meta( 'cdfs_show_email', $user->ID ), FILTER_VALIDATE_BOOLEAN );
$cdfs_show_phone    = filter_var( get_the_author_meta( 'cdfs_show_phone', $user->ID ), FILTER_VALIDATE_BOOLEAN );
$cdfs_show_whatsapp = filter_var( get_the_author_meta( 'cdfs_show_whatsapp', $user->ID ), FILTER_VALIDATE_BOOLEAN );
$email              = cdfs_get_user_email( $user->ID );
$phone              = cdfs_get_user_phone( $user->ID );
$phone_url          = cdfs_get_user_phone_url( $phone, $user->ID );
$whatsapp           = cdfs_get_user_whatsapp( $user->ID );
?>
<div class="cardealer-userdash-userinfo">
	<div class="cardealer-userdash-avatar-wrap">
		<?php
		echo sprintf(
			'<img alt="%s" src="%s" class="%s" width="%s" height="%s">',
			esc_attr( $user->display_name ),
			esc_url( $avatar_url ),
			esc_attr( 'cardealer-userdash-avatar img-circle' ),
			150,
			150
		);
		?>
	</div>
	<div class="cardealer-userdash-details">
		<h3 class="cardealer-userdash-detail-name"><?php echo esc_html( $user->display_name ); ?></h3>
		<span class="cardealer-userdash-detail-usertype"><?php echo esc_html( $user_type_label ); ?></span>
		<?php
		cdfs_get_template(
			'user-dashboard/social-profiles.php',
			array(
				'user'      => $user,
			)
		);
		?>
	</div>
	<div class="cardealer-userdash-buttons">
		<?php
		if ( $cdfs_show_email && $email ) {
			?>
			<a href="<?php echo esc_url( "mailto:{$email}" ); ?>" class="cardealer-userdash-btn cardealer-userdash-btn-red"><i class="far fa-envelope"></i><?php echo esc_html__( 'Send Mail', 'cdfs-addon' ); ?></a>
			<?php
		}
		if ( $cdfs_show_phone && $phone ) {
			$phone_label = sprintf(
				/* translators: %s phone number. */
				esc_html__( 'Call %s', 'cdfs-addon' ),
				$phone
			);
			?>
			<a href="<?php echo esc_url( $phone_url ); ?>" class="cardealer-userdash-btn cardealer-userdash-btn-white"><i class="fas fa-phone-alt"></i><?php echo esc_html( $phone_label ); ?></a>
			<?php
		}
		if ( $cdfs_show_whatsapp && $whatsapp ) {
			?>
			<a href="<?php echo esc_url( "https://wa.me/{$whatsapp}" ); ?>" class="cardealer-userdash-btn cardealer-userdash-btn-white" target="_blank" rel="noreferrer noopener"><i class="fab fa-whatsapp"></i><?php echo esc_html__( 'WhatsApp', 'cdfs-addon' ); ?></a>
			<?php
		}
		?>
	</div>
</div>
