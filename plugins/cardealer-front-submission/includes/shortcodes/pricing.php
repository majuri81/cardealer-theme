<?php
/**
 *
 * Functions for create priocing shortcode.
 *
 * @author   PotenzaGlobalSolutions
 * @package  CDFS
 * @category Core
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** ****************************************************************************
 *
 * Shortcode : cd_pricing
 *
 * **************************************************************************** */

add_shortcode( 'cd_pricing', 'cdfs_shortcode_pricing' );

if ( ! function_exists( 'cdfs_shortcode_pricing' ) ) {
	/**
	 * Shortcode for pricing
	 *
	 * @param array $atts attributes.
	 */
	function cdfs_shortcode_pricing( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'             => '',
				'subtitle'          => '',
				'price'             => '',
				'frequency'         => '',
				'features'          => '',
				'btntext'           => esc_html__( 'Click Here', 'cdfs-addon' ),
				'bestseller'        => false,
				'product_plan_link' => '',
				'custom_link'       => '',
				'css'               => '',
				'element_id'        => uniqid( 'cd_pricing_' ),
			),
			$atts
		);

		extract( $atts );

		if ( empty( $title ) || empty( $features ) ) {
			return;
		}

		$css = $atts['css'];

		$custom_class = vc_shortcode_custom_css_class( $css, ' ' );

		$element_classes = array(
			'cd-pricing-table',
			'pricing-table',
			'text-center',
			$custom_class,
		);

		if ( $bestseller ) {
			$element_classes[] = 'active';
		}

		$features = explode( "\n", $features );

		// Clean br tags from lines.
		foreach ( $features as $line_k => $line ) {
			$line        = trim( $line );
			$line_length = strlen( $line );

			if ( substr( $line, -6 ) === '<br />' || substr( $line, -4 ) === '<br>' ) {
				if ( substr( $line, -6 ) === '<br />' ) {
					$line = mb_substr( $line, 0, $line_length - 6 );
				} elseif ( substr( $line, -4 ) === '<br>' ) {
					$line = mb_substr( $line, 0, $line_length - 4 );
				}
			}
			$features[ $line_k ] = $line;
		}

		$custom_link_vars = vc_build_link( $custom_link );
		$button_link_attr = vc_build_link( '' );

		if ( ! empty( $product_plan_link ) ) {
			if ( 'custom' === $product_plan_link ) {
				$custom_link_vars = vc_build_link( $custom_link );
				$button_link_attr = wp_parse_args( $custom_link_vars, $button_link_attr );
			} elseif( function_exists( 'wc_get_checkout_url' ) ) {
				$url = add_query_arg( array(
					'add-to-cart' => $product_plan_link,
				), wc_get_checkout_url() );
				$button_link_attr['url'] = $url;
			} else {
				$button_link_attr['url'] = '#';
			}
		} else {
			$button_link_attr['url'] = '#';
		}

		$button_link_attr['class'] = 'button button-border gray';

		$element_classes = implode( ' ', array_filter( array_unique( $element_classes ) ) );

		$button_link_attr_str = cdfs_vc_link_attr( $button_link_attr );

		ob_start();
		?>
		<div class="<?php echo esc_attr( $element_classes ); ?>">
			<?php
			if ( $bestseller ) {
				$price_ribbon_img = CDFS_URL . '/images/ribbon.png';
				?>
				<div class="pricing-ribbon">
					<img src="<?php echo esc_url( $price_ribbon_img ); ?>" alt="">
				</div>
				<?php
			}
			?>
			<div class="pricing-title">
				<h2 class="<?php echo ( $bestseller ? 'text-white text-bg' : '' ); ?>"><?php echo esc_html( $atts['title'] ); ?></h2>
				<span><?php echo esc_html( $atts['subtitle'] ); ?></span>
				<div class="pricing-prize">
					<h2><?php echo esc_html( $atts['price'] ); ?></h2>
					<span><?php echo esc_html( $atts['frequency'] ); ?></span>
				</div>
			</div>
			<div class="pricing-list">
				<?php
				if ( ! empty( $features ) ) {
					?>
					<ul>
						<?php
						foreach ( $features as $feature ) {
							?>
							<li>
								<?php
								echo wp_kses(
									$feature,
									array(
										'a'    => array(
											'class' => true,
											'href'  => true,
										),
										'b'    => array(),
										'span' => array(
											'class' => true,
										),
									)
								);
								?>
							</li>
							<?php
						}
						?>
					</ul>
					<?php
				}
				?>
			</div>
			<div class="pricing-order">
				<a <?php echo $button_link_attr_str; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?>>
					<?php echo esc_html( $atts['btntext'] ); ?>
				</a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

/** ****************************************************************************
 *
 * Visual Composer Integration
 *
 * **************************************************************************** */

if ( ! function_exists( 'cdfs_pricing_shortcode_vc_map' ) ) {
	/**
	 * Pricing shortcode map
	 */
	function cdfs_pricing_shortcode_vc_map() {

		if ( function_exists( 'vc_map' ) ) {

			/* Product Plans */
			$product_plans_array = array(
				'' => esc_html__( 'Choose plan', 'cdfs-addon' ),
			);

			if ( function_exists( 'cdhl_get_pricing_plans' ) ) {
				$product_plans_array = cdhl_get_pricing_plans( array(
					'show_custom_option_before' => true,
					'custom_option_before'      => array(
						'custom' => esc_html__( 'Custom', 'cdfs-addon' ),
					),
				) );
			}

			vc_map(
				array(
					'name'                    => esc_html__( 'Pricing', 'cdfs-addon' ),
					'description'             => esc_html__( 'Pricing', 'cdfs-addon' ),
					'base'                    => 'cd_pricing',
					'class'                   => 'cd_element_wrapper',
					'controls'                => 'full',
					'icon'                    => ( function_exists( 'cardealer_vc_shortcode_icon' ) ) ? cardealer_vc_shortcode_icon( 'cd_pricing' ) : '',
					'category'                => esc_html__( 'Potenza', 'cdfs-addon' ),
					'show_settings_on_create' => true,
					'params'                  => array(
						array(
							'type'        => 'textfield',
							// 'holder'      => 'div',
							'class'       => '',
							'heading'     => esc_html__( 'Title', 'cdfs-addon' ),
							'description' => esc_html__( 'Enter pricing title.', 'cdfs-addon' ),
							'param_name'  => 'title',
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'class'       => '',
							'heading'     => esc_html__( 'Sub Title', 'cdfs-addon' ),
							'description' => esc_html__( 'Enter pricing sub title.', 'cdfs-addon' ),
							'param_name'  => 'subtitle',
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							// 'holder'      => 'div',
							'class'       => '',
							'heading'     => esc_html__( 'Price', 'cdfs-addon' ),
							'description' => esc_html__( 'Enter price. e.g.10.00 $', 'cdfs-addon' ),
							'param_name'  => 'price',
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'class'       => '',
							'heading'     => esc_html__( 'Frequency', 'cdfs-addon' ),
							'description' => esc_html__( 'Enter price. e.g.Per Month', 'cdfs-addon' ),
							'param_name'  => 'frequency',
							'admin_label' => true,
						),
						array(
							'type'        => 'textarea',
							'class'       => '',
							'heading'     => esc_html__( 'Features', 'cdfs-addon' ),
							'description' => esc_html__( 'Add features. Add each feature on a new line.', 'cdfs-addon' ),
							'param_name'  => 'features',
						),
						array(
							'type'        => 'textfield',
							'class'       => '',
							'heading'     => esc_html__( 'Button Title', 'cdfs-addon' ),
							'description' => esc_html__( 'Enter button title.', 'cdfs-addon' ),
							'param_name'  => 'btntext',
							'admin_label' => true,
						),
						array(
							'type'        => 'checkbox',
							'heading'     => esc_html__( 'Best Seller', 'cdfs-addon' ),
							'description' => esc_html__( 'Select this checkbox to display the table as best-seller/featured.', 'cdfs-addon' ),
							'param_name'  => 'bestseller',
							'holder'      => 'div',
							'admin_label' => true,
						),
						array(
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Plan', 'cdfs-addon' ),
							'description' => esc_html__( "Select a plan or custom to set a custom for button link. Note: Work with only 'Subscriptio' plugin", 'cdfs-addon' ),
							'param_name'  => 'product_plan_link',
							'value'       => array_flip( $product_plans_array ),
							'admin_label' => true,
						),
						array(
							'type'        => 'vc_link',
							'class'       => '',
							'heading'     => esc_html__( 'Custom Link', 'cdfs-addon' ),
							'description' => esc_html__( 'Enter custom link.', 'cdfs-addon' ),
							'param_name'  => 'custom_link',
							'dependency'  => array(
								'element' => 'product_plan_link',
								'value'   => 'custom',
							),
						),
					),
				)
			);
		}
	}
}
add_action( 'vc_before_init', 'cdfs_pricing_shortcode_vc_map' );
