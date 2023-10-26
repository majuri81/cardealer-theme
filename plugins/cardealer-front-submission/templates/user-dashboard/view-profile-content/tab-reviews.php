<?php
global $car_dealer_options;

$cdfs_review = new CDFS_Review();
$rating_data = $cdfs_review->get_dealer_rating( $user->ID, 'all' );

$dealer_rating_1_label = isset( $car_dealer_options['dealer_rating_1_label'] ) ? $car_dealer_options['dealer_rating_1_label'] : esc_html__( 'Customer Service', 'cdfs-addon' );
$dealer_rating_2_label = isset( $car_dealer_options['dealer_rating_2_label'] ) ? $car_dealer_options['dealer_rating_2_label'] : esc_html__( 'Buying Process', 'cdfs-addon' );
$dealer_rating_3_label = isset( $car_dealer_options['dealer_rating_3_label'] ) ? $car_dealer_options['dealer_rating_3_label'] : esc_html__( 'Overall Experience', 'cdfs-addon' );

$pagination_params = array(
	'profile-tab'  => 'reviews',
);

$author_url = get_author_posts_url( $user->ID );
$page       = ( isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ) ? (int) $_GET['page'] : 1;

$args = array(
	'post_type'      => 'dealer_review',
	'posts_per_page' => 3,
	'post_status'    => array( 'publish' ),
	'paged'          => $page,
	'meta_query' => array(
		array(
			'key'     => 'added_to',
			'value'   => $user->ID,
			'compare' => '=',
		),
	),
);

$dealer_review_list = new WP_Query( $args );
?>
<div class="cdfs-row-left">
	<div class="cdfs-dealer-average-rating">
		<h5 class="cdfs-dealer-average-rating-heading"><?php esc_html_e('Average rating', 'cdfs-addon'); ?></h5>
		<div class="cdfs-dealer-average-rating-numbers">
			<span class="cdfs-rating"><?php echo esc_html( $rating_data['avg_rating'] ); ?></span>
			<span class="cdfs-outoff-rating"><?php esc_html_e( '/5', 'cdfs-addon' ); ?></span>
		</div>
		<div class="cdfs-dealer-average-rating-html">
			<?php echo $cdfs_review->get_rating_html( $rating_data['avg_rating'] ); ?>
		</div>
		<div class="cdfs-dealer-label">
			(<?php  printf( esc_html( _n( 'Based on %d rating', 'Based on %d ratings', $rating_data['count'], 'cdfs-addon' ) ), esc_html( $rating_data['count'] ) ); ?>)
		</div>
	</div>
	<div class="cdfs-dealer-average-rating-item cdfs-dealer-average-dealer_rating_1">
		<div class="cdfs-dealer-average-rating-heading">
			<div class="cdfs-dealer-rating-label">
				<?php echo esc_html( $dealer_rating_1_label ); ?>
			</div>
			<span><strong><?php echo esc_html( $rating_data['dealer_rating_1'] ); ?></strong> <?php esc_html_e( 'out of 5.0', 'cdfs-addon' ); ?></span>
		</div>
		<div class="cdfs-dealer-average-rating-html">
			<?php echo $cdfs_review->get_rating_html( $rating_data['dealer_rating_1'] ); ?>
		</div>
	</div>
	<div class="cdfs-dealer-average-rating-item cdfs-dealer-average-dealer_rating_2">
		<div class="cdfs-dealer-average-rating-heading">
			<div class="cdfs-dealer-rating-label">
				<?php echo esc_html( $dealer_rating_2_label ); ?>
			</div>
			<span><strong><?php echo esc_html( $rating_data['dealer_rating_2'] ); ?></strong> <?php esc_html_e( 'out of 5.0', 'cdfs-addon' ); ?></span>
		</div>
		<div class="cdfs-dealer-average-rating-html">
			<?php echo $cdfs_review->get_rating_html( $rating_data['dealer_rating_2'] ); ?>
		</div>
	</div>
	<div class="cdfs-dealer-average-rating-item cdfs-dealer-average-dealer_rating_3">
		<div class="cdfs-dealer-average-rating-heading">
			<div class="cdfs-dealer-rating-label">
				<?php echo esc_html( $dealer_rating_3_label ); ?>
			</div>
			<span><strong><?php echo esc_html( $rating_data['dealer_rating_3'] ); ?></strong> <?php esc_html_e( 'out of 5.0', 'cdfs-addon' ); ?></span>
		</div>
		<div class="cdfs-dealer-average-rating-html">
			<?php echo $cdfs_review->get_rating_html( $rating_data['dealer_rating_3'] ); ?>
		</div>
	</div>
	<div class="cdfs-dealer-recommendations">
		<div class="cdfs-dealer-recommendations-label"><?php esc_html_e( 'Recommend', 'cdfs-addon'); ?></div>
		<div class="cdfs-likes">
			<i class="far fa-thumbs-up"></i>
			<?php esc_html_e( 'Yes', 'cdfs-addon'); ?>
			<strong>(<?php echo esc_html( $rating_data['likes'] ); ?>)</strong>
		</div>
		<div class="cdfs-dislikes">
			<i class="far fa-thumbs-down"></i>
			<?php esc_html_e( 'No', 'cdfs-addon'); ?>
			<strong>(<?php echo esc_html( $rating_data['dislikes'] ); ?>)</strong>
		</div>
	</div>
</div>
<div class="cdfs-row-right">
	<div class="cdfs-review-content-data">
		<div class="cdfs-review-data-inner">
			<h5><?php esc_html_e( 'Reviews', 'cdfs-addon'); ?></h5>
			<span>(<?php echo esc_html( $rating_data['count'] ); ?>)</span>
		</div>
	<?php
	if ( $dealer_review_list->have_posts() ) {
		?>
		<div class="all-cars-list-arch">
			<?php
			while ( $dealer_review_list->have_posts() ) :
				$dealer_review_list->the_post();

				$avg_rating = get_post_meta( get_the_ID(), 'avg_rating', true );
				?>
				<div class="cdfs-single-review">
					<div class="cdfs-dealer-average-rating-html">
						<?php echo $cdfs_review->get_rating_html( $avg_rating ); ?>
						<span class="cdfs-ratings-average"><strong><?php echo esc_html( $avg_rating ); ?></strong> <?php esc_html_e( 'out of 5.0', 'cdfs-addon' ); ?></span>
					</div>
					<div class="cdfs-single-label">
						<?php the_title(); ?>
					</div>
					<div class="cdfs-single-content">
						<?php the_content(); ?>
					</div>
				</div>
				<?php

			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php
		if ( 1 < $dealer_review_list->max_num_pages ) {
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

				if ( intval( $dealer_review_list->max_num_pages ) > intval( $page ) ) {
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
		<div class="all-review-list row">
			<div class="col-sm-12">
				<div class="alert alert-warning">
					<?php echo esc_html__( 'No reviews found.', 'cdfs-addon' ); ?>
				</div>
			</div>
		</div>
		<?php
	}
	?>
	</div>
</div>
