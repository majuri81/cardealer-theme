<?php
/**
 * Wishlist
 *
 * @author   PotenzaGlobalSolutions
 * @category Class
 * @package  CDFS/Classes
 * @version  1.0.0
 */

/**
 * Wishlist class.
 */
class CDFS_Review {
	/**
	 * Init
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'manage_edit-dealer_review_columns', array( $this, 'manage_review_columns' ) );
		add_action( 'manage_dealer_review_posts_custom_column', array( $this, 'manage_review_columns_content' ), 10, 2 );
		add_action( 'save_post', array( $this, 'update_average_rating' ), 10,3 );
	}

	/**
	 * Register Review post Type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		global $car_dealer_options;
		
		$dealer_rating_1_label = isset( $car_dealer_options['dealer_rating_1_label'] ) ? $car_dealer_options['dealer_rating_1_label'] : esc_html__( 'Customer Service', 'cdfs-addon' );
		$dealer_rating_2_label = isset( $car_dealer_options['dealer_rating_2_label'] ) ? $car_dealer_options['dealer_rating_2_label'] : esc_html__( 'Buying Process', 'cdfs-addon' );
		$dealer_rating_3_label = isset( $car_dealer_options['dealer_rating_3_label'] ) ? $car_dealer_options['dealer_rating_3_label'] : esc_html__( 'Overall Experience', 'cdfs-addon' );

		$labels = array(
			'name'                  => esc_html__( 'Dealer Reviews', 'cdfs-addon' ),
			'singular_name'         => esc_html__( 'Dealer Review', 'cdfs-addon' ),
			'menu_name'             => esc_html__( 'Dealer Reviews', 'cdfs-addon' ),
			'name_admin_bar'        => esc_html__( 'Dealer Review', 'cdfs-addon' ),
			'add_new'               => esc_html__( 'Add New', 'cdfs-addon' ),
			'add_new_item'          => esc_html__( 'Add New Review', 'cdfs-addon' ),
			'new_item'              => esc_html__( 'New Review', 'cdfs-addon' ),
			'edit_item'             => esc_html__( 'Edit Review', 'cdfs-addon' ),
			'view_item'             => esc_html__( 'View Review', 'cdfs-addon' ),
			'all_items'             => esc_html__( 'All Reviews', 'cdfs-addon' ),
			'search_items'          => esc_html__( 'Search Reviews', 'cdfs-addon' ),
			'parent_item_colon'     => esc_html__( 'Parent Reviews:', 'cdfs-addon' ),
			'not_found'             => esc_html__( 'No Review found.', 'cdfs-addon' ),
			'not_found_in_trash'    => esc_html__( 'No Review found in Trash.', 'cdfs-addon' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => esc_html__( 'Description.', 'cdfs-addon' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'capability_type'     => 'post',
			'has_archive'         => false,
			'exclude_from_search' => false,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array(
				'title',
				'editor',
			),
			'menu_icon'           => 'dashicons-feedback',
		);

		register_post_type( 'dealer_review', $args );

		// Add custom fields
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			acf_add_local_field_group(
				array(
					'key'    => 'group_6270d6745f051',
					'title'  => 'Dealer Review Fields',
					'fields' => array(
						array(
							'key' => 'field_6270d7d6aa77b',
							'label' => 'Added by',
							'name' => 'added_by',
							'type' => 'user',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'role' => '',
							'allow_null' => 0,
							'multiple' => 0,
							'return_format' => 'id',
						),
						array(
							'key' => 'field_6270d857c2dcb',
							'label' => 'Added To',
							'name' => 'added_to',
							'type' => 'user',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'role' => '',
							'allow_null' => 0,
							'multiple' => 0,
							'return_format' => 'id',
						),
						array(
							'key' => 'field_6270d87ac2dcc',
							'label' => $dealer_rating_1_label,
							'name' => 'dealer_rating_1',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'choices' => array(
								1 => '1',
								2 => '2',
								3 => '3',
								4 => '4',
								5 => '5',
							),
							'default_value' => false,
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'return_format' => 'value',
							'ajax' => 0,
							'placeholder' => '',
						),
						array(
							'key' => 'field_6270d8c6c2dcd',
							'label' => $dealer_rating_2_label,
							'name' => 'dealer_rating_2',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'choices' => array(
								1 => '1',
								2 => '2',
								3 => '3',
								4 => '4',
								5 => '5',
							),
							'default_value' => false,
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'return_format' => 'value',
							'ajax' => 0,
							'placeholder' => '',
						),
						array(
							'key' => 'field_6270d8d2c2dce',
							'label' => $dealer_rating_3_label,
							'name' => 'dealer_rating_3',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'choices' => array(
								1 => '1',
								2 => '2',
								3 => '3',
								4 => '4',
								5 => '5',
							),
							'default_value' => false,
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'return_format' => 'value',
							'ajax' => 0,
							'placeholder' => '',
						),
						array(
							'key' => 'field_6270d8fba57c8',
							'label' => 'Recommended',
							'name' => 'recommended',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'choices' => array(
								'yes' => 'Yes',
								'no' => 'No',
							),
							'default_value' => false,
							'allow_null' => 0,
							'multiple' => 0,
							'ui' => 0,
							'return_format' => 'value',
							'ajax' => 0,
							'placeholder' => '',
						),
					),
					'location' => array(
						array(
							array(
								'param' => 'post_type',
								'operator' => '==',
								'value' => 'dealer_review',
							),
						),
					),
					'menu_order' => 0,
					'position' => 'normal',
					'style' => 'default',
					'label_placement' => 'left',
					'instruction_placement' => 'label',
					'hide_on_screen' => '',
					'active' => true,
					'description' => '',
					'show_in_rest' => 0,
				)
			);
		}
	}

	function update_average_rating( $post_id, $post, $update ) {

		// Only set for post_type = post!
		if ( 'dealer_review' !== $post->post_type ) {
			return;
		}

		if ( ! $update ) {
			return;
		}

		$dealer_rating_1 = get_post_meta( $post_id, 'dealer_rating_1', true );
		$dealer_rating_2 = get_post_meta( $post_id, 'dealer_rating_2', true );
		$dealer_rating_3 = get_post_meta( $post_id, 'dealer_rating_3', true );

		$avg_rating = ( $dealer_rating_1 + $dealer_rating_2 + $dealer_rating_3 ) / 3;
		update_post_meta( $post_id, 'avg_rating', round( $avg_rating, 1 ) );
	}

	function get_rating_html( $rating ) {
		$rating_width = ( ( $rating * 100 ) / 5 ) . '%';
		?>
		<div class="cdfs-star-ratings">
			<div class="fill-ratings" style="width: <?php echo esc_attr( $rating_width ); ?>;">
				<span><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i></span>
			</div>
			<div class="empty-ratings">
				<span><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Register Review post Type.
	 *
	 * @return void
	 */
	public function get_dealer_rating( $user_id, $rating_type = 'avg_rating' ) {
		$args = array(
			'post_type'      => 'dealer_review',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish' ),
			'meta_query' => array(
				array(
					'key'     => 'added_to',
					'value'   => $user_id,
					'compare' => '=',
				),
			),
		);
		$dealer_review_data = new WP_Query( $args );

		$ratings               = array(
			'likes'           => 0,
			'dislikes'        => 0,
			'dealer_rating_1' => 0,
			'dealer_rating_2' => 0,
			'dealer_rating_3' => 0,
			'avg_rating'      => 0,
			'count'           => $dealer_review_data->found_posts,
		);

		if ( $dealer_review_data->have_posts() ) {
			while ( $dealer_review_data->have_posts() ) {
				$dealer_review_data->the_post();

				$post_id = get_the_id();

				if ( 'all' === $rating_type ) {
					$dealer_rating_1 = get_post_meta( $post_id, 'dealer_rating_1', true );
					$dealer_rating_2 = get_post_meta( $post_id, 'dealer_rating_2', true );
					$dealer_rating_3 = get_post_meta( $post_id, 'dealer_rating_3', true );
					$avg_rating      = get_post_meta( $post_id, 'avg_rating', true );
					$recommended     = get_post_meta( $post_id, 'recommended', true );

					if ( $dealer_rating_1 ) {
						$ratings['dealer_rating_1'] = $ratings['dealer_rating_1'] + $dealer_rating_1;
					}

					if ( $dealer_rating_2 ) {
						$ratings['dealer_rating_2'] = $ratings['dealer_rating_2'] + $dealer_rating_2;
					}

					if ( $dealer_rating_3 ) {
						$ratings['dealer_rating_3'] = $ratings['dealer_rating_3'] + $dealer_rating_3;
					}

					if ( $avg_rating ) {
						$ratings['avg_rating'] = $ratings['avg_rating'] + $avg_rating;
					}

					if ( 'yes' === $recommended ) {
						$ratings['likes']++;
					}

					if ( 'no' === $recommended ) {
						$ratings['dislikes']++;
					}
				} else {
					$dealer_rating = get_post_meta( $post_id, $rating_type, true );
					$ratings[$rating_type] = $ratings[$rating_type] + $dealer_rating;
				}
			}

			if ( 'all' === $rating_type ) {
				$ratings['dealer_rating_1'] = round( $ratings['dealer_rating_1'] / $ratings['count'], 1 );
				$ratings['dealer_rating_2'] = round( $ratings['dealer_rating_2'] / $ratings['count'], 1 );
				$ratings['dealer_rating_3'] = round( $ratings['dealer_rating_3'] / $ratings['count'], 1 );
				$ratings['avg_rating']      = round( $ratings['avg_rating'] / $ratings['count'], 1 );

				$rating_data = $ratings;
			} else {
				if ( 'likes' !== $rating_type && 'dislikes' !== $rating_type ) {
					$ratings[$rating_type] = round( $ratings[$rating_type] / $ratings['count'], 1 );
				}

				$rating_data = array(
					'count' => $ratings['count']
				);
				$rating_data[$rating_type] = $ratings[$rating_type];
			}
		} else {
			$rating_data = $ratings;
		}

		wp_reset_postdata();

		return $rating_data;
	}

	/**
	 * Register Review post Type.
	 *
	 * @return void
	 */
	public function get_submitted_user_review( $user_id, $added_to ) {
		$args = array(
			'post_type'      => 'dealer_review',
			'posts_per_page' => -1,
			'author'         => $user_id,
			'post_status'    => array( 'publish', 'pending' ),
			'meta_query'     => array(
				array(
					'key'     => 'added_to',
					'value'   => $added_to,
					'compare' => '=',
				),
			),
		);
		$dealer_review_data = new WP_Query( $args );

		if ( $dealer_review_data->have_posts() ) {
			$first_post = $dealer_review_data->posts[0];
			return $first_post->ID;
		}

		return false;
	}

	function manage_review_columns( $column ) {
		unset( $column['date'] );

		$column['content']    = esc_html__( 'Content', 'cdfs-addon' );
		$column['avg_rating'] = esc_html__( 'Average Rating', 'cdfs-addon' );
		$column['to']         = esc_html__( 'To', 'cdfs-addon' );
		$column['date']       = esc_html__( 'Date', 'cdfs-addon' );

		return $column;
	}

	function manage_review_columns_content( $column_name, $post_id ) {

		if ( 'content' === $column_name ) {
			echo get_the_content( $post_id );
		}

		if ( 'to' === $column_name ) {
			$author_to_id = get_post_meta( $post_id, 'added_to', true );
			if ( $author_to_id ) {
				echo get_the_author_meta( 'display_name', $author_to_id );
			} else {
				echo "N/A";
			}
		}

		if ( 'avg_rating' === $column_name ) {
			echo get_post_meta( $post_id, 'avg_rating', true );
		}
	}
}

$cdfs_review = new CDFS_Review();
$cdfs_review->init();
