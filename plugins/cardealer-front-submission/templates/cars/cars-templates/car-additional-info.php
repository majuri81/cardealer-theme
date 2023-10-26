<?php
/**
 * Car form additional-information
 * This template can be overridden by copying it to yourtheme/cardealer-front-submission/cars/cars-templates/car-additional-info.php.
 *
 * @author  PotenzaGlobalSolutions
 * @package CDFS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'action_before_cars_additional_info' );

$car_fields = cdfs_get_car_form_fields( 'car_tabs' );
?>
<div class="cdfs-form clearfix">
	<?php
	if ( ! empty( $car_fields ) ) {
		foreach ( $car_fields as $field ) {
			$value = array();
			if ( ! empty( $id ) ) {
				if ( 'features_and_options' === $field['name'] ) { // For taxonomy.
					$tax_obj = wp_get_post_terms( $id, 'car_features_options' );
					foreach ( $tax_obj as $obj ) {
						$value[] = $obj->name;
					}
				} else { // other than taxonomy.
					$value = get_post_meta( $id, $field['name'], true );
				}
			}

			$subsection_name_slug = str_replace( '_', '-', $field['name'] );
			$subsection_id    = 'additional-info-' . $subsection_name_slug;
			$subsection_class = 'additional-info-subsection additional-info-subsection-' . $subsection_name_slug;

			if ( in_array( $field['type'], array( 'checkbox' ), true ) ) {
				?>
				<div id="<?php echo esc_attr( $subsection_id ); ?>" class="<?php echo esc_attr( $subsection_class ); ?>">
					<div class="form-group">
						<label><?php echo esc_html( $field['placeholder'] ); ?></label>
						<?php
						if ( 'features_and_options' === $field['name'] ) {
							?>
							<div class="<?php echo esc_attr( $subsection_name_slug ); ?>-container">
								<?php
								$options = array(
									'taxonomy'   => 'car_features_options',
									'orderby'    => 'name',
									'order'      => 'ASC',
									'parent'     => 0,
									'hide_empty' => false, // can be 1, '1' too.
								);

								$features_and_options = get_terms( $options );

								foreach ( $features_and_options as $parent_term ) {
									$cheched = '';
									if ( is_array( $value ) ) {
										if ( in_array( $parent_term->name, $value ) ) {
											$cheched = 'checked';
										}
									}
									?>
									<div class="col-sm-4">
										<ul class="feature_list">
											<li>
												<label>
													<input
														id="cdfs-<?php echo esc_attr( $parent_term->slug ); ?>"
														type="<?php echo esc_attr( $field['type'] ); ?>"
														class="form-control cdfs-<?php echo esc_attr( $parent_term->slug ); ?> cdfs-checkbox"
														name="car_data[<?php echo esc_attr( $field['name'] ); ?>][]"
														value="<?php echo esc_attr( $parent_term->name ); ?>"
													<?php echo esc_attr( $cheched ); ?>
													/>
													<span><?php echo $parent_term->name; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?></span>
												</label>
												<?php
												foreach ( get_terms( 'car_features_options', array( 'hide_empty' => false, 'parent' => $parent_term->term_id ) ) as $child_term ) {
													?>
													<ul class="feature_list_sub">
														<li>
															<label>
																<input
																	id="cdfs-<?php echo esc_attr( $child_term->slug ); ?>"
																	type="<?php echo esc_attr( $field['type'] ); ?>"
																	class="form-control cdfs-<?php echo esc_attr( $child_term->slug ); ?> cdfs-checkbox"
																	name="car_data[<?php echo esc_attr( $field['name'] ); ?>][]"
																	value="<?php echo esc_attr( $child_term->name ); ?>"
																<?php echo esc_attr( $cheched ); ?>
																/>
																<span><?php echo $child_term->name; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotE ?></span>
															</label>
														</li>
													</ul>
												<?php } ?>
											</li>
										</ul>
									</div>
								<?php } ?>
								<div class="col-sm-4">
									<label>
										<input
											id="cdfs-other"
											type="<?php echo esc_attr( $field['type'] ); ?>"
											class="form-control cdfs-other cdfs-checkbox"
											name="car_data[cdfs-other]"
											value="<?php echo esc_html_e( 'Other', 'cdfs-addon' ); ?>"
										/>
										<span><?php esc_html_e( 'Other', 'cdfs-addon' ); ?></span>
									</label>
									<input
										id="cdfs-cdfs-other-opt"
										type="textbox"
										class="form-control cdfs-cdfs-other-opt cdfs-hidden"
										name="car_data[cdfs-other-opt]"
										value=""
										placeholder="<?php esc_html_e( 'Enter options by comma separated', 'cdfs-addon' ); ?>"
									/>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			} elseif ( 'editor' === $field['type'] ) {
				?>
				<div id="<?php echo esc_attr( $subsection_id ); ?>" class="<?php echo esc_attr( $subsection_class ); ?>">
					<div class="form-group">
						<label><?php echo esc_html( $field['placeholder'] ); ?></label>
						<?php
						// default settings - Kv_front_editor.php.
						$content   = ! ( empty( $value ) ) ? $value : $field['placeholder'];
						$editor_id = 'cdfs-' . $field['name'];
						$settings  = array(
							'wpautop'                 => true, // use wpautop?.
							'media_buttons'           => false, // show insert/upload button(s).
							'textarea_name'           => 'car_data[' . $field['name'] . ']', // set the textarea name to something different, square brackets [] can be used here.
							'textarea_rows'           => 6,
							'tabindex'                => '',
							'remove_linebreaks'       => false, // Don't remove line breaks.
							'convert_newlines_to_brs' => true, // Convert newline characters to BR tags.
							'editor_css'              => '', // extra styles for both visual and HTML editors buttons.
							'editor_class'            => 'cdfs_editor', // add extra class(es) to the editor textarea.
							'teeny'                   => false, // output the minimal editor config used in Press This.
							'dfw'                     => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4).
							'tinymce'                 => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array().
							'quicktags'               => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array().
						);
						wp_editor( $content, $editor_id, $settings );
						?>
					</div>
				</div>
				<?php
			}
		}
	}
	?>
</div>

<?php
do_action( 'action_after_cars_additional_info' );
