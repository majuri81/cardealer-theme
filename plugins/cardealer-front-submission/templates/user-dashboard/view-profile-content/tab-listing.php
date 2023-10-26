<?php
global $car_dealer_options;

// Options.
$per_page     = ( isset( $car_dealer_options['cars-per-page'] ) && ! empty( $car_dealer_options['cars-per-page'] ) ) ? $car_dealer_options['cars-per-page'] : 12;
$cars_orderby = ( isset( $car_dealer_options['cars-default-sort-by'] ) && ! empty( $car_dealer_options['cars-default-sort-by'] ) ) ? $cars_orderby = $car_dealer_options['cars-default-sort-by'] : '';
$cars_order   = ( isset( $car_dealer_options['cars-default-sort-by-order'] ) && ! empty( $car_dealer_options['cars-default-sort-by-order'] ) ) ? $car_dealer_options['cars-default-sort-by-order'] : 'desc';
$lay_style    = ( isset( $car_dealer_options['listing-layout'] ) && ! empty( $car_dealer_options['listing-layout'] ) ) ? $car_dealer_options['listing-layout'] : 'view-grid';

// URL params.
$per_page      = ( isset( $_GET['cars_pp'] ) && ! empty( $_GET['cars_pp'] ) ) ? $_GET['cars_pp'] : $per_page;
$cars_orderby = ( isset( $_GET['cars_orderby'] ) && ! empty( $_GET['cars_orderby'] ) ) ? $_GET['cars_orderby'] : $cars_orderby;
$cars_order   = ( isset( $_GET['cars_order'] ) && ! empty( $_GET['cars_order'] ) && in_array( $_GET['cars_order'], array( 'desc', 'asc' ), true ) ) ? $_GET['cars_order'] : $cars_order;
$lay_style    = ( isset( $_GET['lay_style'] ) && ! empty( $_GET['lay_style'] ) ) ? $_GET['lay_style'] : $lay_style;
$page         = ( isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ) ? $_GET['page'] : 1;

$author_url        = get_author_posts_url( $user->ID );
$pagination_params = array(
	'profile-tab'  => 'listing',
);

if ( isset( $_GET['cars_pp'] ) ) {
	$pagination_params['cars_pp'] = $per_page;
}
if ( isset( $_GET['cars_orderby'] ) ) {
	$pagination_params['cars_orderby'] = $cars_orderby;
}
if ( isset( $_GET['cars_order'] ) ) {
	$pagination_params['cars_order'] = $cars_order;
}
if ( isset( $_GET['lay_style'] ) ) {
	$pagination_params['lay_style'] = $lay_style;
}

$args = array(
	'post_type'      => 'cars',
	'posts_per_page' => $per_page,
	'orderby'        => $cars_orderby,
	'order'          => $cars_order,
	'post_status'    => array( 'publish' ),
	'author'         => $user->ID,
	'paged'          => $page,
);

if ( isset( $cars_orderby ) && ! empty( $cars_orderby ) ) {
	if ( 'sale_price' === $cars_orderby ) {
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'final_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	} else {
		$args['orderby'] = $cars_orderby;
	}
}

$car_list = new WP_Query( $args );
?>
<div class="cardealer-userdash-tab-content-header">
	<h3><?php echo esc_html( $tab_data['title'] ); ?></h3>
	<?php
	if ( $car_list->have_posts() ) {
		?>
		<div class="cars-top-filters-box-right">
			<?php
			cardealer_cars_catalog_ordering();
			if ( ! wp_is_mobile() ) {
				cardealer_get_catlog_view();
			}
			?>
		</div>
		<?php
	}
	?>
</div>
<div class="cardealer-userdash-tab-content-data">
	<?php
	if ( $car_list->have_posts() ) {
		?>
		<div class="all-cars-list-arch row">
			<?php
			while ( $car_list->have_posts() ) :
				$car_list->the_post();
				get_template_part( 'template-parts/cars/content', 'cars' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php
		if ( 1 < $car_list->max_num_pages ) {
			?>
			<div class="cardealer-pagination cardealer-pagination--without-numbers cardealer-Pagination">
				<?php
				if ( 1 !== intval( $page ) ) {
					$pagination_params['page'] = $page - 1;
					$prev_url = add_query_arg( $pagination_params, $author_url );
					?>
					<a class="cardealer-button cardealer-button--previous cardealer-Button cardealer-Button--previous button" href="<?php echo esc_url( $prev_url ); ?>">
						<?php esc_html_e( 'Previous', 'cdfs-addon' ); ?>
					</a>
					<?php
				}

				if ( intval( $car_list->max_num_pages ) > intval( $page ) ) {
					$pagination_params['page'] = $page + 1;
					$next_url = $prev_url = add_query_arg( $pagination_params, $author_url );
					?>
					<a class="cardealer-button cardealer-button--next cardealer-Button cardealer-Button--next button" href="<?php echo esc_url( $next_url ); ?>">
						<?php esc_html_e( 'Next', 'cdfs-addon' ); ?>
					</a>
					<?php
				}
				?>
			</div>
			<?php
		}
	} else {
		?>
		<div class="all-cars-list-arch row">
			<div class="col-sm-12">
				<div class="alert alert-warning">
					<?php echo esc_html__( 'No vehicles found.', 'cdfs-addon' ); ?>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div>
