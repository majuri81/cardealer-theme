<?php $profile_tab = isset( $_GET['profile-tab'] ) && ! empty( $_GET['profile-tab'] ) ? $_GET['profile-tab'] : ''; ?>
<div class="cardealer-userdash-tabs">
	<div class="cardealer-userdash-tabs-inner">
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<?php
			$tab_sr = 1;

			if ( count( $tabs ) > 1 ) {
				foreach ( $tabs as $tab_k => $tab_data ) {
					$tab_class = array(
						'cardealer-userdash-tab',
						"cardealer-userdash-tab-{$tab_k}",
					);

					if ( ( ! empty( $profile_tab ) && array_key_exists( $profile_tab, $tabs ) && $profile_tab === $tab_k ) || ( empty( $profile_tab ) && 1 === $tab_sr ) ) {
						$tab_class[] = 'active';
					}
					$tab_class_str = implode( ' ', array_filter( array_unique( $tab_class ) ) );
					?>
					<li role="presentation" class="<?php echo esc_attr( $tab_class_str ); ?>">
						<a href="#<?php echo esc_attr( $tab_k ); ?>" aria-controls="<?php echo esc_attr( $tab_k ); ?>" data-tab_id="<?php echo esc_attr( $tab_k ); ?>" role="tab" data-toggle="tab">
							<?php if ( $tab_data['tab_icon'] ) { ?><i class="<?php echo esc_attr( $tab_data['tab_icon'] ); ?>"></i><?php } echo esc_html( $tab_data['title'] ); ?>
						</a>
					</li>
					<?php
					$tab_sr++;
				}
			}
			?>
		</ul>
		<!-- Tab panes -->
		<div class="tab-content">
			<?php
			$tab_content_sr = 1;
			foreach ( $tabs as $tab_k => $tab_data ) {
				$tab_content_class = array(
					'cardealer-userdash-tab-content',
					"cardealer-userdash-tab-content-{$tab_k}",
					'tab-pane',
				);

				if ( ( ! empty( $profile_tab ) && array_key_exists( $profile_tab, $tabs ) && $profile_tab === $tab_k ) || ( empty( $profile_tab ) && 1 === $tab_content_sr ) ) {
					$tab_content_class[] = 'active';
				}

				$tab_content_class_str = implode( ' ', array_filter( array_unique( $tab_content_class ) ) );
				?>
				<div id="<?php echo esc_attr( $tab_k ); ?>" class="<?php echo esc_attr( $tab_content_class_str ); ?>" role="tabpanel">
					<div class="cardealer-userdash-tab-content-inner">
						<?php
						if ( 'listing' === $tab_k && 'callback' === $tab_data['content_type'] ) {
							call_user_func( $tab_data['callback'], $tab_k, $tab_data, $user );
						} else {
							?>
							<div class="cardealer-userdash-tab-content-header"><h3><?php echo esc_html( $tab_data['title'] ); ?></h3></div>
							<div class="cardealer-userdash-tab-content-data">
								<?php
								if ( 'callback' === $tab_data['content_type'] ) {
									call_user_func( $tab_data['callback'], $tab_k, $tab_data, $user );
								} elseif ( 'content' === $tab_data['content_type'] ) {
									echo wp_kses_post( do_shortcode( $tab_data['content'] ) );
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
				$tab_content_sr++;
			}
			?>
		</div>
	</div>
</div>
