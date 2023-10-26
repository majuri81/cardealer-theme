<?php
if ( is_user_logged_in() ) {
	$cdfs_review = new CDFS_Review();
	$review_id   = $cdfs_review->get_submitted_user_review( get_current_user_id(), $user->ID );

	if ( $review_id ) {
		?>
		<div class="cdfs-dealer-review-notice"><?php esc_html_e( 'You already submitted review. If you leave another review, previous will be updated.', 'cdfs-addon' ); ?></div>
		<?php
	}

	do_action( 'cardealer-profile/dealer-review/before-form', $user ); ?>
	<form id="cdfs-dealer-review-form" class="cdfs-dealer-review-form form-horizontal" action="add_dealer_review" method="post" enctype="multipart/form-data">
		<?php
		global $car_dealer_options;

		do_action( 'cardealer-profile/dealer-review/form-start', $user );

		$dealer_rating_1_label = isset( $car_dealer_options['dealer_rating_1_label'] ) ? $car_dealer_options['dealer_rating_1_label'] : esc_html__( 'Customer Service', 'cdfs-addon' );
		$dealer_rating_2_label = isset( $car_dealer_options['dealer_rating_2_label'] ) ? $car_dealer_options['dealer_rating_2_label'] : esc_html__( 'Buying Process', 'cdfs-addon' );
		$dealer_rating_3_label = isset( $car_dealer_options['dealer_rating_3_label'] ) ? $car_dealer_options['dealer_rating_3_label'] : esc_html__( 'Overall Experience', 'cdfs-addon' );
		?>
		<fieldset>
			<div class="cdfs-form-row cdfs-row-full">
				<label class="cdfs-input-label"><?php echo esc_html( $dealer_rating_1_label ); ?></label>
				<div class="cdfs-input-wrap cdfs-rating-group">
					<div class="rating">
						<input type="radio" id="dealer_rating_1-5" name="dealer_rating_1" value="5" /><label for="dealer_rating_1-5" title="<?php esc_attr_e( '5 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_1-4" name="dealer_rating_1" value="4" /><label for="dealer_rating_1-4" title="<?php esc_attr_e( '4 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_1-3" name="dealer_rating_1" value="3" /><label for="dealer_rating_1-3" title="<?php esc_attr_e( '3 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_1-2" name="dealer_rating_1" value="2" /><label for="dealer_rating_1-2" title="<?php esc_attr_e( '2 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_1-1" name="dealer_rating_1" value="1" /><label for="dealer_rating_1-1" title="<?php esc_attr_e( '1 star', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
					</div>
				</div>
			</div>
			<div class="cdfs-form-row cdfs-row-full">
				<label class="cdfs-input-label"><?php echo esc_html( $dealer_rating_2_label ); ?></label>
				<div class="cdfs-input-wrap cdfs-rating-group">
					<div class="rating">
						<input type="radio" id="dealer_rating_2-5" name="dealer_rating_2" value="5" /><label for="dealer_rating_2-5" title="<?php esc_attr_e( '5 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_2-4" name="dealer_rating_2" value="4" /><label for="dealer_rating_2-4" title="<?php esc_attr_e( '4 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_2-3" name="dealer_rating_2" value="3" /><label for="dealer_rating_2-3" title="<?php esc_attr_e( '3 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_2-2" name="dealer_rating_2" value="2" /><label for="dealer_rating_2-2" title="<?php esc_attr_e( '2 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_2-1" name="dealer_rating_2" value="1" /><label for="dealer_rating_2-1" title="<?php esc_attr_e( '1 star', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
					</div>
				</div>
			</div>
			<div class="cdfs-form-row cdfs-row-full">
				<label class="cdfs-input-label"><?php echo esc_html( $dealer_rating_3_label ); ?></label>
				<div class="cdfs-input-wrap cdfs-rating-group">
					<div class="rating">
						<input type="radio" id="dealer_rating_3-5" name="dealer_rating_3" value="5" /><label for="dealer_rating_3-5" title="<?php esc_attr_e( '5 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_3-4" name="dealer_rating_3" value="4" /><label for="dealer_rating_3-4" title="<?php esc_attr_e( '4 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_3-3" name="dealer_rating_3" value="3" /><label for="dealer_rating_3-3" title="<?php esc_attr_e( '3 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_3-2" name="dealer_rating_3" value="2" /><label for="dealer_rating_3-2" title="<?php esc_attr_e( '2 stars', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
						<input type="radio" id="dealer_rating_3-1" name="dealer_rating_3" value="1" /><label for="dealer_rating_3-1" title="<?php esc_attr_e( '1 star', 'cdfs-addon' ); ?>"><i class="far fa-star"></i></label>
					</div>
				</div>
			</div>
			<div class="cdfs-form-row cdfs-row-full">
				<label class="cdfs-input-label"><?php esc_html_e( 'Do you recommend this dealer?', 'cdfs-addon' ); ?></label>
				<div class="cdfs-input-wrap cdfs-radio-group-wrap">
					<div class="cdfs-radio-group">
						<input type="radio" id="recommend-yes" name="recommend_dealer" value="yes" checked>
						<label for="recommend-yes"><?php esc_html_e( 'Yes', 'cdfs-addon' ); ?></label>
					</div>
					<div class="cdfs-radio-group">
						<input type="radio" name="recommend_dealer" value="no">
						<label for="recommend-no"><?php esc_html_e( 'no', 'cdfs-addon' ); ?></label>
					</div>
				</div>
			</div>
			<div class="cdfs-form-row cdfs-row-full">
				<label class="cdfs-input-label" for="review_title"><?php esc_html_e( 'Title', 'cdfs-addon' ); ?></label>
				<div class="cdfs-input-wrap">
					<input type="text" class="cdfs-input cdfs-input-text input-text cdhl_validate" name="review_title" id="review_title" />
				</div>
			</div>
			<div class="cdfs-form-row cdfs-row-full">
				<label class="cdfs-input-label cdfs-label-top" for="review_content"><?php esc_html_e( 'Your Review', 'cdfs-addon' ); ?></label>
				<div class="cdfs-input-wrap">
					<textarea id="review_content" class="cdfs-input cdfs-input-textarea" name="review_content" rows="4" cols="50"></textarea>
				</div>
			</div>
		</fieldset>

		<?php do_action( 'cdfs_dealer_review_form', $user ); ?>
		<div class="cdfs-form-button">
			<?php wp_nonce_field( 'add_dealer_review' ); ?>
			<input type="submit" class="cdfs-Button button" name="add_dealer_review" value="<?php esc_attr_e( 'Submit Review', 'cdfs-addon' ); ?>" />
			<input type="hidden" name="action" value="add_dealer_review" />
			<input type="hidden" name="added_to" value="<?php echo esc_attr( $user->ID ); ?>" />
		</div>

		<?php
		do_action( 'cardealer-profile/dealer-review/form-end', $user );
		?>
	</form>
	<?php
	do_action( 'cardealer-profile/dealer-review/after-form' );
} else {
	?>
	<div class="cdfs-dealer-review-notice">
		<?php
		/* translators: %s: url */
		printf(
			wp_kses(
				/* translators: %s: url */
				__( 'Please <a href="%1$s">login</a> first to post a review.', 'cdfs-addon' ),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
						'class'  => array(),
					),
				)
			),
			esc_url( cdfs_get_page_permalink( 'dealer_login' ) )
		)
		?>
	</div>
	<?php
}
