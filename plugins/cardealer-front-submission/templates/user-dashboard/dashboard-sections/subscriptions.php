<?php
// Content title.
cdfs_get_template(
	'user-dashboard/content-title.php',
	array(
		'endpoint_title' => $endpoint_title,
	)
);

$current_user = wp_get_current_user();
$user_id      = ( isset( $current_user->ID ) ? (int) $current_user->ID : 0 );

do_action( 'cardealer-dashboard/content/before-subscriptions' );
?>
<div class="cardealer-dashboard-subs-wrapper">
	<?php do_action( 'cardealer_dashboard_my_subscriptions_before_subscriptions', $subscriptions ); ?>
	<table class="cardealer-dashboard-subs-table" style="width:100%">
		<thead>
			<tr>
				<?php
				foreach ( $columns as $column_id => $column_name ) {
					?>
					<th class="cardealer-dashboard-subs-table__header cardealer-dashboard-subs-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
					<?php
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			global $car_dealer_options;

			$cdfs_free_limit           = cdfs_get_free_cars_limit();
			$cdfs_free_available_limit = cdfs_get_user_subscription_available_car_limit( 'free', $user_id );

			foreach ( $columns as $column_id => $column_name ) {
				?>
				<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id); ?>" data-title="<?php echo esc_attr( $column_name); ?>">
					<?php
					if ( has_action( "cardealer-dashboard/subscriptions/column/$column_id" ) ) {
						do_action( "cardealer-dashboard/subscriptions/column/$column_id", $column_id, $subscription );
					} elseif ( $column_id === 'subscription-id' ) {
						esc_html_e( 'Free', 'cdfs-addon' );
					} elseif ( $column_id === 'subscription-products') {
						esc_html_e( 'Free', 'cdfs-addon' );
					} elseif ( $column_id === 'subscription-status') {
						esc_html_e( 'Active', 'cdfs-addon' );
					} elseif ( $column_id === 'subscription-availibility' ) {
						$cdfs_free_available_limit = $cdfs_free_available_limit < 0 ? 0 : $cdfs_free_available_limit;
						echo ( intval( $cdfs_free_limit ) ) ? sprintf( '%s/%s', $cdfs_free_available_limit, intval( $cdfs_free_limit ) ) : '&mdash;';
					} elseif ( $column_id === 'subscription-expiry') {
						esc_html_e( 'N/A', 'cdfs-addon' );
					}
					?>
				</td>
				<?php
			}
			if ( ! empty( $subscriptions ) ) {
				foreach ( $subscriptions as $subscription ) {
					$car_limit           = get_post_meta( $subscription->get_id(), 'cdfs_car_limt', true );
					$available_car_limit = cdfs_get_user_subscription_available_car_limit( $subscription->get_id(), $user_id );
					?>
					<tr class="cardealer-dashboard-subs-table__row cardealer-dashboard-subs-table__row--status-<?php echo esc_attr( $subscription->get_status() ); ?>">
						<?php
						foreach ( $columns as $column_id => $column_name ) {
							?>
							<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id); ?>" data-title="<?php echo esc_attr( $column_name); ?>">

								<?php
								if ( has_action( "cardealer-dashboard/subscriptions/column/$column_id" ) ) {
									do_action( "cardealer-dashboard/subscriptions/column/$column_id", $column_id, $subscription );
								// Subscription ID.
								} elseif ( $column_id === 'subscription-id' ) {
									?>
									<a href="<?php echo esc_url( RP_SUB_WC_Account::get_subscription_endpoint_url( $subscription, 'view-subscription' ) ); ?>">
										<?php echo esc_html( $subscription->get_subscription_number() ); ?>
									</a>
									<?php
								// Subscription/Plan.
								} elseif ( $column_id === 'subscription-products') {
									echo $subscription->get_formatted_product_name();
								} elseif ( $column_id === 'subscription-status') {
									echo esc_html( $subscription->get_status_label() );

								} elseif ( $column_id === 'subscription-total') {
									echo $subscription->get_formatted_recurring_total();
								} elseif ( $column_id === 'subscription-actions') {
									foreach ( RP_SUB_WC_Account::get_subscription_actions( $subscription, true) as $key => $action ) {
										?>
										<a href="<?php echo esc_url( $action['url'] ); ?>" class="woocommerce-button button <?php echo sanitize_html_class( $key); ?>"><?php echo esc_html( $action['name']); ?></a>
										<?php
									}
								// Subscription usage.
								} elseif ( $column_id === 'subscription-availibility' ) {
									echo ( intval( $car_limit ) ) ? sprintf( '%s/%s', $available_car_limit, intval( $car_limit ) ) : '&mdash;';

								// Subscription expiry.
								} elseif ( $column_id === 'subscription-expiry') {
									if ( $subscription->get_scheduled_subscription_expire() ) {
										echo $subscription->get_scheduled_subscription_expire()->format( 'Y-m-d H:i' );
									} else {
										echo '&mdash;';
									}
								}
								?>
							</td>
							<?php
						}
						?>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
	<?php
	do_action( 'cardealer_dashboard_my_subscriptions_after_subscriptions', $subscriptions );
	?>
</div>
<?php
do_action( 'cardealer-dashboard/content/before-subscriptions' );
