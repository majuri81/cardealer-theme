<?php
defined( 'ABSPATH' ) || exit;

cdfs_get_template( 'user-dashboard/dashboard-header.php' );
	?>
	<div class="cardealer-dashboard-wrapper">
		<div class="cardealer-dashboard-sidebar">
			<?php
			do_action( 'cardealer-dashboard/before-sidebar' );

			/**
			 * Dashboard sidebar.
			 */
			do_action( 'cardealer-dashboard/sidebar' );

			do_action( 'cardealer-dashboard/after-sidebar' );
			?>
		</div>
		<div class="cardealer-dashboard-content">
			<div class="cardealer-dashboard-content-header">
			<?php
			do_action( 'cardealer-dashboard/before-header' );

			/**
			 * Dashboard header.
			 */
			do_action( 'cardealer-dashboard/header' );

			do_action( 'cardealer-dashboard/after-header' );
			?>
			</div>
			<div class="cardealer-dashboard-content-main">
				<?php
				do_action( 'cardealer-dashboard/before-content' );

				/**
				 * Dashboard content.
				 */
				do_action( 'cardealer-dashboard/content' );

				do_action( 'cardealer-dashboard/after-content' );
				?>
			</div>
		</div>
	</div>
	<?php
cdfs_get_template( 'user-dashboard/dashboard-footer.php' );
