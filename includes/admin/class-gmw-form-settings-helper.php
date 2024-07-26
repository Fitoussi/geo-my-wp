<?php
/**
 * GEO my WP form settings helper.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GEO my WP form settings helper class.
 */
class GMW_Form_Settings_Helper {

	/**
	 * Array of existings post types.
	 *
	 * @var array
	 */
	public static $post_types = array();

	/**
	 * Check if string is json.
	 *
	 * @param  string $string [description].
	 *
	 * @return boolean         [description]
	 */
	public static function is_json( $string ) {
		return is_string( $string ) && is_array( json_decode( $string, true ) ) && ( json_last_error() === JSON_ERROR_NONE ) ? true : false;
	}

	/**
	 * Get list of pages.
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_pages( $args = array() ) { //phpcs:ignore.

		$pages = array();

		foreach ( get_pages() as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}

		return $pages;
	}

	/**
	 * Generate array of post types.
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_post_types( $args = array() ) { //phpcs:ignore.

		$output = array();

		foreach ( get_post_types() as $post ) {
			$output[ $post ] = get_post_type_object( $post )->labels->name . ' ( ' . $post . ' )';
		}

		return $output;
	}

	/**
	 * Taxonomy group sorting
	 *
	 * @param  [type] $a [description].
	 *
	 * @param  [type] $b [description].
	 *
	 * @return [type]    [description]
	 */
	public static function sort_taxonomy_terms_groups( $a, $b ) {
		return strcmp( $a->taxonomy, $b->taxonomy );
	}

	/**
	 * Generate taxonomies settings for the form editor.
	 *
	 * @param  [type] $args      [description].
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 */
	public static function form_editor_taxonomies( $args, $value, $name_attr, $form ) {

		$taxonomies_options = wp_parse_args(
			$args,
			array(
				'where'               => 'search_form_taxonomies',
				'multiple_post_types' => 0,
				'sortable'            => 0,
				'usage'               => 1,
				'usage_options'       => array(
					'disabled' => __( 'Disable', 'geo-my-wp' ),
					'select'   => __( 'Select dropdown', 'geo-my-wp' ),
				),
				'smartbox'            => 0,
				'label'               => 1,
				'options_all'         => 0,
				'include_terms'       => 0,
				'exclude_terms'       => 0,
				'orderby'             => 0,
				'order'               => 0,
				'count'               => 0,
				'hide_empty'          => 0,
				'required'            => 1,
			)
		);

		$taxonomies_options = apply_filters( 'gmw_form_editor_taxonomies_options', $taxonomies_options, $form );

		if ( empty( $value ) ) {
			$value = array();
		}

		// get all taxonmoies.
		$taxonomies = get_taxonomies();

		// Order taxonmies based on saved data.
		if ( ! empty( $value ) ) {
			$taxonomies = array_merge( array_flip( array_keys( $value ) ), $taxonomies );
		}

		$multiple_pt       = ! empty( $taxonomies_options['multiple_post_types'] ) ? ' multiple-post-types ' : '';
		$incexc_class      = ! empty( $taxonomies_options['include_exclude_terms'] ) ? ' incexc-terms-enabled' : '';
		$sortable_taxonomy = '';
		$id_attr           = '';

		if ( 'incexc_terms' === $taxonomies_options['where'] ) {

			$id_attr = 'inc-exc-';

		} elseif ( gmw_is_addon_active( 'premium_settings' ) || 'global_maps' === $form['addon'] ) {
			$sortable_taxonomy = ' gmw-sortable-item ';
		}

		?>
		<div id="<?php echo $id_attr; // phpcs:ignore: XSS ok. ?>taxonomies-wrapper"
			class="gmw-setting-groups-container gmw-<?php echo $id_attr;  // phpcs:ignore: XSS ok. ?>taxonomies-wrapper gmw-settings-group-draggable-area <?php echo $multiple_pt; // phpcs:ignore: XSS ok. ?><?php echo $incexc_class; // phpcs:ignore: XSS ok. ?>">

			<?php
			$all_post_types = get_post_types();

			foreach ( $taxonomies as $taxonomy_name ) {

				$taxonomy = get_taxonomy( $taxonomy_name );

				// Abort if taxonomies was not found.
				if ( empty( $taxonomy ) || ! is_object( $taxonomy ) ) {
					continue;
				}

				$post_types = $taxonomy->object_type;

				// skip If post type of the taxonomy does not exists.
				if ( 0 === count( array_intersect( $post_types, $all_post_types ) ) ) {
					continue;
				}

				// Set default taxonomy value.
				if ( empty( $value[ $taxonomy_name ] ) ) {

					$value[ $taxonomy_name ] = array(
						'style'      => 'na',
						'post_types' => $post_types,
					);
				}

				$tax_option = $value[ $taxonomy_name ];
				?>
				<div id="<?php echo esc_attr( $taxonomy_name ); ?>_cat" class="taxonomy-wrapper gmw-settings-group-wrapper<?php echo $sortable_taxonomy; // phpcs:ignore: XSS ok. ?>"
					data-post_types="<?php echo esc_attr( implode( ',', $post_types ) ); ?>">

					<div class="taxonomy-header gmw-settings-group-header">

						<?php if ( ! empty( $taxonomies_options['sortable'] ) ) { ?>

							<?php wp_enqueue_script( 'jquery-ui-sortable' ); ?>

							<i class="gmw-settings-group-drag-handle gmw-taxonomy-sort-handle gmw-icon-sort gmw-tooltip--"
								aria-label="Drag to sort taxonomies." title="Sort taxonomy"></i>
						<?php } ?>

						<i class="gmw-settings-group-options-toggle gmw-taxonomy-options-toggle gmw-icon-cog gmw-tooltip--"
							aria-label="Click to manage options."></i>
						<span class="gmw-taxonomy-label"><strong>
								<?php echo esc_html( $taxonomy->labels->singular_name ); ?>
							</strong> (
							<?php echo esc_attr( implode( ', ', $post_types ) ); ?> )
						</span>
					</div>

					<?php $style = ! empty( $tax_option['style'] ) ? $tax_option['style'] : 'disabled'; ?>

					<div class="taxonomy-settings-table-wrapper taxonomy-settings gmw-settings-group-content gmw-settings-multiple-fields-wrapper"
						data-type="<?php echo esc_attr( $style ); ?>">

						<?php $tax_name_attr = esc_attr( $name_attr . '[' . $taxonomy_name . ']' ); ?>

						<?php foreach ( $post_types as $pt ) { ?>
							<input type="hidden" name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[post_types][]"
								value="<?php echo esc_attr( $pt ); ?>" />
						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['usage_options'] ) ) { ?>

							<div class="gmw-settings-panel-field taxonomy-usage-option-wrap">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php echo esc_attr_e( 'Usage', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="taxonomy-usage taxonomy-tab-content gmw-settings-panel-input-container">

									<select name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[style]"
										class="taxonomy-usage gmw-smartbox-not">

										<?php foreach ( $taxonomies_options['usage_options'] as $usage_value => $usage_label ) { ?>

											<option value="<?php echo esc_attr( $usage_value ); ?>" <?php selected( $usage_value, $tax_option['style'], true ); ?>>
												<?php echo esc_attr( $usage_label ); ?>
											</option>

										<?php } ?>

									</select>
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Select the field usage.', 'geo-my-wp' ); ?>
								</div>
							</div>

						<?php } ?>

						<?php $tax_label = esc_attr( stripcslashes( $taxonomy->labels->name ) ); ?>

						<?php if ( ! empty( $taxonomies_options['smartbox'] ) ) { ?>

							<div class="gmw-settings-panel-field" data-usage="select,multiselect">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Smart Select Field', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="gmw-settings-panel-input-container">
									<label class="gmw-checkbox-toggle-field">
										<input type="checkbox" name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[smartbox]"
											value="1" <?php echo ! empty( $tax_option['smartbox'] ) ? 'checked="checked"' : ''; ?> />
										<span class="gmw-checkbox-toggle"></span>
										<span class="gmw-checkbox-label">
											<?php esc_attr_e( 'Enable', 'geo-my-wp' ); ?>
										</span>
									</label>
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Enable smart select field.', 'geo-my-wp' ); ?>
								</div>

							</div>
						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['label'] ) ) { ?>

							<div class="gmw-settings-panel-field taxonomy-enabled-settings" data-usage="checkboxes,select,multiselect">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Field Label', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="tax-content gmw-settings-panel-input-container">

									<input type="text" placeholder="<?php esc_attr_e( 'Taxonomy label', 'geo-my-wp' ); ?>"
										name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[label]"
										value="<?php echo isset( $tax_option['label'] ) ? esc_attr( stripcslashes( $tax_option['label'] ) ) : $tax_label; // phpcs:ignore: XSS ok. ?>" />
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Enter the field\'s label or leave it blank to hide it.', 'geo-my-wp' ); ?>
								</div>
							</div>

						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['options_all'] ) ) { ?>

							<div class="gmw-settings-panel-field taxonomy-enabled-settings" data-usage="select,multiselect">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Options All Label', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="tax-content gmw-settings-panel-input-container">

									<input type="text" placeholder="<?php esc_attr_e( 'Options all label', 'geo-my-wp' ); ?>"
										name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[show_options_all]"
										value="<?php echo isset( $tax_option['show_options_all'] ) ? esc_attr( stripcslashes( $tax_option['show_options_all'] ) ) : 'All ' . $tax_label; // phpcs:ignore: XSS ok. ?>" />
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Enter the lable that will be the first option in the select dropdown ( or leave it blank ). This option will have no value and usually will display all options.', 'geo-my-wp' ); ?>
								</div>
							</div>
						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['include_terms'] ) ) { ?>

							<div class="gmw-settings-panel-field option-include-terms" data-usage="">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Include Terms', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="tax-content gmw-settings-panel-input-container">

									<?php $include_value = isset( $tax_option['include'] ) ? $tax_option['include'] : ''; ?>

									<select multiple data-placeholder="Select terms" class="taxonomies-picker"
										data-gmw_ajax_load_options="gmw_get_taxonomy_terms"
										data-gmw_ajax_load_options_taxonomy="<?php echo esc_attr( $taxonomy_name ); ?>"
										name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[include][]">

										<?php
										if ( ! empty( $tax_option['include'] ) ) {
											foreach ( $tax_option['include'] as $tax_value ) {
												echo '<option selected="selected" value="' . esc_attr( $tax_value ) . '">' . esc_html__( 'Click to load option', 'geo-my-wp' ) . '</option>';
											}
										}
										?>
									</select>

								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Select specific taxonomy terms to include.', 'geo-my-wp' ); ?>
								</div>
							</div>
						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['exclude_terms'] ) ) { ?>

							<div class="gmw-settings-panel-field option-exclude-terms" data-usage="">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Exclude Terms', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="tax-content gmw-settings-panel-input-container">

									<?php $exclude_value = isset( $tax_option['exclude'] ) ? $tax_option['exclude'] : ''; ?>

									<select multiple data-placeholder="Select terms" class="taxonomies-picker"
										name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[exclude][]"
										data-gmw_ajax_load_options="gmw_get_taxonomy_terms"
										data-gmw_ajax_load_options_taxonomy="<?php echo esc_attr( $taxonomy_name ); ?>">
										<?php
										if ( ! empty( $tax_option['exclude'] ) ) {
											foreach ( $tax_option['exclude'] as $tax_value ) {
												echo '<option selected="selected" value="' . esc_attr( $tax_value ) . '">' . esc_html__( 'Click to load option', 'geo-my-wp' ) . '</option>';
											}
										}
										?>
										<?php // phpcs:disable.
										// echo GMW_Form_Settings_Helper::get_taxonomy_terms( $taxonomy_name, $exclude_value );
										// phpcs:enable. ?>
									</select>
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Select specific taxonomy terms to exclude.', 'geo-my-wp' ); ?>
								</div>
							</div>
						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['orderby'] ) ) { ?>

							<div class="gmw-settings-panel-field taxonomy-enabled-settings" data-usage="checkboxes,select,multiselect">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Sort By', 'geo-my-wp' ); ?>
									</label>
								</div>

								<?php $selected = ! empty( $tax_option['orderby'] ) ? $tax_option['orderby'] : ''; ?>

								<div class="tax-content gmw-settings-panel-input-container">

									<select name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[orderby]"
										class="gmw-smartbox-not">
										<option value="id" selected="selected">
											<?php esc_attr_e( 'ID', 'geo-my-wp' ); ?>
										</option>
										<option value="name" <?php selected( 'name', $selected, true ); ?>>
											<?php esc_attr_e( 'Name', 'geo-my-wp' ); ?>
														</option>
														<option value="slug" <?php selected( 'slug', $selected, true ); ?>>
											<?php esc_attr_e( 'Slug', 'geo-my-wp' ); ?>
										</option>
									</select>

								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Select how to sort the taxonomy terms.', 'geo-my-wp' ); ?>
								</div>
							</div>
						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['order'] ) ) { ?>

							<div class="gmw-settings-panel-field taxonomy-enabled-settings" data-usage="checkboxes,select,multiselect">
								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Order', 'geo-my-wp' ); ?>
									</label>
								</div>

								<?php $selected = ! empty( $tax_option['order'] ) ? $tax_option['order'] : ''; ?>

								<div class="tax-content gmw-settings-panel-input-container">
									<select name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[order]"
										class="gmw-smartbox-not">
										<option value="ASC" selected="selected">
											<?php esc_attr_e( 'ASC', 'geo-my-wp' ); ?>
										</option>
										<option value="DESC" <?php selected( 'DESC', $selected, true ); ?>>
											<?php esc_attr_e( 'DESC', 'geo-my-wp' ); ?>
										</option>
									</select>
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Select the order of the terms.', 'geo-my-wp' ); ?>
								</div>
							</div>
						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['count'] ) ) { ?>

							<div class="gmw-settings-panel-field taxonomy-enabled-settings" data-usage="checkboxes,select,multiselect">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Show Posts Count', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="tax-content gmw-settings-panel-input-container">

									<label class="gmw-checkbox-toggle-field">
										<input type="checkbox" name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[show_count]"
											value="1" <?php echo ! empty( $tax_option['show_count'] ) ? 'checked="checked"' : ''; ?> />
										<span class="gmw-checkbox-toggle"></span>
										<span class="gmw-checkbox-label">
											<?php esc_attr_e( 'Enable', 'geo-my-wp' ); ?>
										</span>
									</label>
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Display the posts count for each term.', 'geo-my-wp' ); ?>
								</div>

							</div>
						<?php } ?>

						<?php if ( ! empty( $taxonomies_options['hide_empty'] ) ) { ?>

							<div class="gmw-settings-panel-field taxonomy-enabled-settings" data-usage="checkboxes,select,multiselect">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php esc_attr_e( 'Hide Empty', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="tax-content gmw-settings-panel-input-container">

									<label class="gmw-checkbox-toggle-field">
										<input type="checkbox" name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[hide_empty]"
											value="1" <?php echo ! empty( $tax_option['hide_empty'] ) ? 'checked="checked"' : ''; ?> />
										<span class="gmw-checkbox-toggle"></span>
										<span class="gmw-checkbox-label">
											<?php esc_attr_e( 'Enable', 'geo-my-wp' ); ?>
										</span>
									</label>
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Hide terms without posts.', 'geo-my-wp' ); ?>
								</div>
							</div>
						<?php } ?>

						<?php
                        // phpcs:disable.
                        /*if ( ! empty( GMW()->options['post_types_settings']['per_category_icons']['enabled'] ) ) { ?>

                            <div class="gmw-settings-panel-field taxonomy-enabled-settings" style="display:none">

                                <label class="gmw-settings-panel-header">
                                    <input
                                        type="checkbox"
                                        class="category-icon"
                                        name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[cat_icons]"
                                        value="1"
                                        <?php echo ! empty( $tax_option['cat_icons'] ) ? 'checked="checked"' : ''; ?> />
                                    <?php esc_attr_e( 'Category icons', 'geo-my-wp' ); ?>
                                </label>

                            </div>

                        <?php } */
                        // phpcs:enable. ?>

						<?php if ( ! empty( $taxonomies_options['required'] ) ) { ?>

							<div class="gmw-settings-panel-field" data-usage="select,multiselect">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label">
										<?php echo esc_attr_e( 'Required', 'geo-my-wp' ); ?>
									</label>
								</div>

								<div class="taxonomy-required taxonomy-tab-content gmw-settings-panel-input-container">

									<label class="gmw-checkbox-toggle-field">
										<input type="checkbox" class="gmw-form-field checkbox setting-taxonomy-required"
											name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[required]" value="1" <?php echo ! empty( $tax_option['required'] ) ? 'checked="checked"' : ''; ?>>
										<span class="gmw-checkbox-toggle"></span>
										<span class="gmw-checkbox-label">
											<?php esc_attr_e( 'Enable', 'geo-my-wp' ); ?>
										</span>
									</label>
								</div>

								<div class="gmw-settings-panel-description">
									<?php esc_attr_e( 'Make this a required field.', 'geo-my-wp' ); ?>
								</div>
							</div>
						<?php } ?>

					</div>
				</div>

			<?php } ?>

			<?php // phpcs:disable.
            // echo self::include_exclude_terms( $val, $name_attr . '[include_exclude_terms]', $form ); // phpcs:ignore: XSS ok.
            // phpcs:enable. ?>
		</div>


		<?php
        // phpcs:disable.
        /*$tax_name_attr = esc_attr( $name_attr . '[include_exclude_terms]' ); ?>

            <div id="include-exclude-tax-terms" class="gmw-setting-groups-container">

                <div class="gmw-settings-group-wrapper">

                    <div class="gmw-settings-group-conten gmw-settings-multiple-fields-wrapper">

                        <div class="gmw-settings-panel-field">

                            <div class="gmw-settings-panel-header">
                                <label class="gmw-settings-label"><?php echo esc_attr_e( 'Usage', 'geo-my-wp' ); ?></label>
                            </div>

                            <div class="taxonomy-usage taxonomy-tab-content gmw-settings-panel-input-container">

                                <select name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[usage]" class="gmw-smartbox-not">
                                    <option value="disable" <?php selected( 'disable', $tax_option['style'], true ); ?>><?php esc_attr_e( 'Disable', 'geo-my-wp' ); ?></option>
                                    <option value="include" <?php selected( 'include', $tax_option['style'], true ); ?>><?php esc_attr_e( 'Include Terms', 'geo-my-wp' ); ?></option>
                                    <option value="exclude" <?php selected( 'exclude', $tax_option['style'], true ); ?>><?php esc_attr_e( 'Exclude Terms', 'geo-my-wp' ); ?></option>
                                </select>
                            </div>

                            <div class="gmw-settings-panel-description">
                                <?php esc_attr_e( 'Select the field usage.', 'geo-my-wp' ); ?>
                            </div>
                        </div>

                        <div class="gmw-settings-panel-field option-include-terms">

                            <div class="gmw-settings-panel-header">
                                <label class="gmw-settings-label"><?php esc_attr_e( 'Include Terms', 'geo-my-wp' ); ?></label>
                            </div>

                            <div class="tax-content gmw-settings-panel-input-container">

                                <?php $include_value = isset( $tax_option['include'] ) ? $tax_option['include'] : ''; ?>

                                <select
                                    multiple
                                    data-placeholder="Select terms"
                                    class="taxonomies-picker"
                                    data-gmw_ajax_load_options="gmw_get_taxonomy_terms"
                                    data-gmw_ajax_load_options_taxonomy="<?php echo esc_attr( $taxonomy_name ); ?>"
                                    name="<?php echo $tax_name_attr; // phpcs:ignore: XSS ok. ?>[terms_id][]">

                                    <?php
                                    if ( ! empty( $tax_option['include'] ) ) {
                                        foreach ( $tax_option['include'] as $tax_value ) {
                                            echo '<option selected="selected" value="' . $tax_value . '">' . __( 'Click to load options', 'geo-my-wp' ) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>

                            </div>

                            <div class="gmw-settings-panel-description">
                                <?php esc_attr_e( 'Select specific taxonmoy terms to include.', 'geo-my-wp' ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php */
        // phpcs:enable.
		$allwed = array(
			'a' => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
			),
		);
		?>
		<div id="taxonomies-messages-wrapper">
			<div class="post-types-taxonomies-message select-taxonomy gmw-admin-notice-box gmw-admin-notice-error">
				<span>
					<?php esc_html_e( 'Select at least one post type above in order to see and setup the taxonomies.', 'geo-my-wp' ); ?>
				</span>
			</div>
			<div class="post-types-taxonomies-message multiple-selected gmw-admin-notice-box gmw-admin-notice-error">
				<span>
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %s link to the premium settings extension page */
							__( 'Taxonomies are not available when selecting multiple post types. This feature is available with the <a href="%s" target="_blank">Premium Settings extension</a>.', 'geo-my-wp' ),
							'https://geomywp.com/extensions/premium-settings'
						),
						$allwed
					);
					?>
				</span>
			</div>
			<div class="post-types-taxonomies-message taxonomies-not-found gmw-admin-notice-box gmw-admin-notice-error">
				<span>
					<?php esc_html_e( 'No taxonomies were found for the selected post type.', 'geo-my-wp' ); ?>
				</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Meta Fields Form Settings.
	 *
	 * @param  array $args    field args.
	 *
	 * @param  array $values  field values.
	 *
	 * @param  array $options enable/disable field options.
	 *
	 * @since 4.4 ( moved from Premium Settings ).
	 */
	public static function get_custom_field( $args = array(), $values = array(), $options = array() ) {

		if ( empty( self::$post_types ) ) {
			self::$post_types = GMW_Form_Settings_Helper::get_post_types( array() );
		}

		// Default args.
		$default_args = array(
			'option_name'     => '',
			'name'            => '',
			'is_original'     => true,
			'disable_wrapper' => false,
		);

		$args = wp_parse_args( $args, $default_args );

		// Default value.
		$default_values = array(
			'name'                     => '',
			'usage'                    => 'text',
			'type'                     => 'CHAR',
			'compare'                  => '=',
			'date_compare'             => '=',
			'label'                    => '',
			'field_output'             => '%field%',
			'second_label'             => '',
			'options'                  => '',
			'second_options'           => '',
			'show_options_all'         => '',
			'seconds_show_options_all' => '',
			'placeholder'              => '',
			'second_placeholder'       => '',
			'value'                    => '',
			'second_value'             => '',
			'required'                 => 0,
			'step'                     => '1',
			'min_value'                => '0',
			'max_value'                => '100',
			'value_prefix'             => '',
			'value_suffix'             => '',
			'date_format'              => 'm/d/Y',
			'time_format'              => 'h:iK',
			'disable_field'            => 0,
			'post_types_cond'          => array(),
			// 'datetime_format'          => 'm/d/Y h:iK', //phpcs:ignore.
		);

		$field_values = wp_parse_args( $values, $default_values );

		// Field options.
		$default_options = array(
			'usage'           => array(
				'disabled'     => 'Disable',
				'pre_defined'  => 'Pre defined',
				'text'         => 'Textbox',
				'number'       => 'Number',
				'select'       => 'Select dropdown',
				'multiselect'  => 'Multi-select box',
				'checkboxes'   => 'Checkboxes',
				'radio'        => 'Radio buttons',
				'slider'       => 'Slider',
				'range_slider' => 'Range Slider',
				'date'         => 'Date',
				'time'         => 'Time',
				// 'datetime'    => 'Date and Time', //phpcs:ignore.
			),
			'compare'         => array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'BETWEEN', 'NOT BETWEEN' ),
			'date_compare'    => array( '=', '!=', '>', '>=', '<', '<=', 'BETWEEN', 'NOT BETWEEN' ),
			'slider_compare'  => array( '=', '!=', '>', '>=', '<', '<=' ),
			'smartbox'        => 1,
			'options'         => 1,
			'dynamic_options' => 0,
			'step'            => 1,
			'disable_field'   => 1,
			'value'           => 1,
			'label'           => 1,
			'field_output'    => 0,
			'options_all'     => 1,
			'placeholder'     => 1,
			'required'        => 1,
			'second_enabled'  => 1,
			'slider_options'  => 1,
			'min_value'       => 1,
			'max_value'       => 1,
			'date_format'     => 1,
			'time_format'     => 1,
			'post_types_cond' => 0,
			// 'datetime_format' => 1, //phpcs:ignore.
		);

		$options = wp_parse_args( $options, $default_options );

		$is_original = '';
		$disabled    = '';
		$field_name  = esc_attr( $args['name'] );

		if ( ! empty( $args['option_name'] ) ) {
			$name_attr = esc_attr( $args['option_name'] ) . '[' . $field_name . ']';
		} else {
			$name_attr = $field_name;
		}

		if ( $args['is_original'] ) {
			$is_original = 'original-field';
			$disabled    = 'disabled="disabled"';
		}
		?>
		<?php if ( ! $args['disable_wrapper'] ) { ?>

			<div class="gmw-custom-field-wrapper gmw-settings-group-drag-handle gmw-settings-group-wrapper gmw-sortable-item <?php echo $is_original; // phpcs:ignore:XSS ok. ?> " data-field_name="<?php echo ( ! $is_original ) ? $field_name : ''; // phpcs:ignore:XSS ok. ?>">

				<div class="custom-field-header gmw-settings-group-header">

					<i class="gmw-settings-group-drag-handl gmw-custom-field-sort-handle gmw-icon-sort gmw-tooltip--" aria-label="<?php esc_attr_e( 'Drag to sort fields.', 'geo-my-wp' ); ?>" title="<?php esc_attr_e( 'Sort fields.', 'geo-my-wp' ); ?>"></i>
					<i class="gmw-settings-group-options-toggle gmw-custom-field-options-toggle gmw-icon-cog gmw-tooltip--" aria-label="<?php esc_attr_e( 'Click to manage options.', 'geo-my-wp' ); ?>"></i>



					<div class="custom-field-label">
						<strong>Label: </strong>
						<span><?php echo ( isset( $field_values['label'] ) && ! is_array( $field_values['label'] ) ) ? esc_attr( stripcslashes( $field_values['label'] ) ) : '( No label )'; ?></span>
					</div>

					<div class="custom-field-slug">
						<strong>Slug/Id: </strong>
						<span><?php echo esc_attr( $field_values['name'] ); ?></span>
						<input
							type="hidden"
							class="gmw-custom-field-name"
							name="<?php echo $name_attr . '[name]';  // phpcs:ignore:XSS ok. ?>"
							value="<?php echo esc_attr( $field_values['name'] ); ?>"
							readonl="readonl"
							<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
						/>
					</div>

					<i class="gmw-settings-group-delete-trigger gmw-custom-field-delete gmw-icon-trash gmw-tooltip--" aria-label="<?php esc_attr_e( 'Click to delete field.', 'geo-my-wp' ); ?>"></i>
				</div>

		<?php } ?>

			<div class="custom-field-settings gmw-settings-multiple-fields-wrapper gmw-settings-group-content">

				<?php if ( ! empty( $options['usage'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-usage-option-wrap">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Field Usage', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-usage gmw-settings-panel-input-container">

							<select
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								class="custom-field-usage-selector gmw-smartbox-not"
								name="<?php echo $name_attr . '[usage]'; // phpcs:ignore:XSS ok. ?>"
							>
								<?php
								if ( empty( $field_values['usage'] ) ) {
									$field_values['usage'] = 'text';
								}
								?>

								<?php foreach ( $options['usage'] as $option_value => $option_label ) { ?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $option_value, $field_values['usage'], true ); ?>><?php echo esc_attr( $option_label ); ?></option>
								<?php } ?>
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Select the field usage.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php
				/*// phpcs:enable.
				if ( ! empty( $options['type'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-type-option-wrap" data-usage="text">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Field Type', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-type gmw-settings-panel-input-container">

							<select
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								class="custom-field-type-selector gmw-smartbox-not"
								name="<?php echo $name_attr . '[type]'; // phpcs:ignore:XSS ok. ?>"
							>
								<?php
								if ( ! isset( $field_values['type'] ) ) {
									$field_values['type'] = 'CHAR';
								}
								?>

								<?php foreach ( $options['type'] as $option_value => $option_label ) { ?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $option_value, $field_values['type'], true ); ?>><?php echo esc_attr( $option_label ); ?></option>
								<?php } ?>
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the field type.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php }
				*/// phpcs:disable.
				?>

				<?php if ( $options['options'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-options-option-wrap" data-usage="select,multiselect,checkboxes,radio">

						<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label
										for="custom-field-options-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
										class="gmw-settings-label"><?php esc_attr_e( 'Field Options', 'geo-my-wp' ); ?></label>
								</div>

								<div class="custom-field-option-options gmw-settings-panel-input-container">

									<?php
									if ( empty( $field_values['options'] ) ) {
										$field_values['options'] = '';
									}
									?>

									<?php if ( empty( $options['dynamic_options'] ) ) { ?>

										<textarea
											id="custom-field-options-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
											name="<?php echo $name_attr . '[options]'; // phpcs:ignore:XSS ok. ?>"
											rows="10"
											cols="50"
											<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
										/><?php echo isset( $field_values['options'] ) ? esc_textarea( stripcslashes( $field_values['options'] ) ) : ''; ?></textarea>

									<?php } else { ?>

										<select multiple
											id="custom-field-options-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
											name="<?php echo $name_attr . '[options]'; // phpcs:ignore:XSS ok. ?>"
											data-gmw_ajax_load_options="<?php echo esc_attr( $options['dynamic_options'] ); ?>"
											data-placeholder="Select options..."
										></select>

									<?php } ?>

								</div>
							</div>

							<?php if ( $options['second_enabled'] ) { ?>

								<div class="gmw-settings-panel-inner-option custom-field-second-option">

									<div class="gmw-settings-panel-header">
										<label
											for="custom-field-options-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
											class="gmw-settings-label"><?php esc_attr_e( 'Second Field Options', 'geo-my-wp' ); ?></label>
									</div>

									<div class="custom-field-option-options gmw-settings-panel-input-container">

										<?php
										if ( empty( $field_values['second_options'] ) ) {
											$field_values['second_options'] = '';
										}
										?>

										<textarea
											id="custom-field-options-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
											name="<?php echo $name_attr . '[second_options]'; // phpcs:ignore:XSS ok. ?>"
											rows="10"
											cols="50"
											<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
										/><?php echo isset( $field_values['second_options'] ) ? esc_textarea( stripcslashes( $field_values['second_options'] ) ) : ''; ?></textarea>

									</div>
								</div>

							<?php } ?>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( '1. Enter each option on a new line. For more control, you can specify both a value and label like this: ', 'geo-my-wp' ); ?>
							<br />
							<?php esc_html_e( 'option_1 : Option 1', 'geo-my-wp' ); ?>
							<br />
							<?php esc_html_e( '2. If this field was generated using the ACF ( Advanced Custom Fields ) plugin, you can use the placeholder {acf_field_options} anywhere in the textarea to populate the options that you entered in the ACF plugin. You can combine the placeholder with options manually added to the textarea.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php
				/* // phpcs:enable.
				if ( ! empty( $options['date_type'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-date-format-option-wrap" data-usage="date,datetime" data-usage_not="time">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Date Format', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-date-format gmw-settings-panel-input-container">

							<select
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								class="custom-field-date-format-selector gmw-smartbox-not"
								name="<?php echo $name_attr . '[date_type]'; // phpcs:ignore:XSS ok. ?>">

								<?php
								if ( ! isset( $field_values['date_type'] ) ) {
									$field_values['date_type'] = 'yyyy/mm/dd';
								}
								?>

								<?php foreach ( $options['date_type'] as $option_value => $option_label ) { ?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $option_value, $field_values['date_type'], true ); ?>><?php echo esc_attr( $option_label ); ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the date format.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php }
				*/
				// phpcs:disable.
				?>

				<?php if ( $options['date_format'] ) { ?>

					<?php
					$date   = date( 'm/d/Y', time() );
					$date_2 = date( 'F d, Y', time() );
					?>

					<div class="gmw-settings-panel-field custom-field-date-format-option-wrap" data-usage="date">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Date Format', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-date-format gmw-settings-panel-input-container">

							<input
								type="text"
								name="<?php echo $name_attr . '[date_format]'; // phpcs:ignore:XSS ok. ?>"
								value="<?php echo isset( $field_values['date_format'] ) ? esc_attr( stripcslashes( $field_values['date_format'] ) ) : 'm/d/Y'; ?>"
								placeholder="m/d/Y"
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-description">
							<?php
							/* translators: %1$s: date, %2$s: date. */
							echo sprintf( __( 'Enter the date format. For example, m/d/Y for "%1$s", or F d, Y for "%2$s". You can find the list of available format tokens on <a href="%1$s" target="_blank">this page</a>.', 'geo-my-wp' ), $date, $date_2, 'https://flatpickr.js.org/formatting/' ); // phpcs:ignore:XSS ok.
							?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['time_format'] ) { ?>

					<?php
					$time   = date( 'h:i:sa', time() );
					$time_2 = date( 'H:i', time() );
					?>

					<div class="gmw-settings-panel-field custom-field-time-format-option-wrap" data-usage="time">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Time Format', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-time-format gmw-settings-panel-input-container">

							<input
								type="text"
								name="<?php echo $name_attr . '[time_format]'; // phpcs:ignore:XSS ok. ?>"
								value="<?php echo isset( $field_values['time_format'] ) ? esc_attr( stripcslashes( $field_values['time_format'] ) ) : 'h:iK'; ?>"
								placeholder="h:iK"
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-description">
							<?php
							/* translators: %1$s: time, %2$s: time. */
							echo sprintf( __( 'Enter the time format. For example, h:i:sK for "%1$s", or H:i for "%2$s". You can find the list of available format tokens on <a href="%1$s" target="_blank">this page</a>.', 'geo-my-wp' ), $time, $time_2, 'https://flatpickr.js.org/formatting/' ); // phpcs:ignore:XSS ok.
							?>
						</div>
					</div>

					<?php
					/*
					if ( $options['datetime_format'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-datetime-format-option-wrap" data-usage="datetime" data-usage_not="date,time">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Display Format', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-datetime-format gmw-settings-panel-input-container">

							<input
								type="text"
								name="<?php echo $name_attr . '[datetime_format]'; // phpcs:ignore:XSS ok. ?>"
								value="<?php echo isset( $field_values['datetime_format'] ) ? esc_attr( stripcslashes( $field_values['datetime_format'] ) ) : 'm/d/Y h:iK'; ?>"
								placeholder="m/d/Y h:iK"
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter the field lable or leave blank to hide it.', 'geo-my-wp' ); ?>
						</div>
					</div>

					<?php
					*/
					?>

				<?php } ?>

				<?php if ( ! empty( $options['compare'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-general-comparison-option-wrap" data-usage="pre_defined,hidden,number">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Compare Type', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-comparison gmw-settings-panel-input-container">

							<select
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								class="custom-field-general-comparison-selector custom-field-comparison-selector gmw-smartbox-not"
								name="<?php echo $name_attr . '[compare]'; // phpcs:ignore:XSS ok. ?>"
							>
								<?php
								if ( ! isset( $field_values['compare'] ) ) {
									$field_values['compare'] = '=';
								}
								?>

								<?php foreach ( $options['compare'] as $option ) { ?>
									<option value="<?php echo esc_attr( str_replace( ' ', '_', $option ) ); ?>" <?php selected( $option, $field_values['compare'], true ); ?>><?php echo esc_attr( $option ); ?></option>
								<?php } ?>
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the comparison operator.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( ! empty( $options['date_compare'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-date-comparison-option-wrap" data-usage="date,time">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Compare Type', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-date-comparison gmw-settings-panel-input-container">

							<select
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								class="custom-field-date-comparison-selector custom-field-comparison-selector gmw-smartbox-not"
								name="<?php echo $name_attr . '[date_compare]'; // phpcs:ignore:XSS ok. ?>"
							>
								<?php
								if ( ! isset( $field_values['date_compare'] ) ) {
									$field_values['date_compare'] = '=';
								}
								?>

								<?php foreach ( $options['date_compare'] as $option ) { ?>
									<option value="<?php echo esc_attr( str_replace( ' ', '_', $option ) ); ?>" <?php selected( $option, $field_values['date_compare'], true ); ?>><?php echo esc_attr( $option ); ?></option>
								<?php } ?>
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the comparison operator.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( ! empty( $options['slider_compare'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-slider-comparison-option-wrap" data-usage="slider">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Compare Type', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-slider-comparison custom-field-comparison-selector gmw-settings-panel-input-container">

							<select
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								class="custom-field-slider-comparison-selector gmw-smartbox-not"
								name="<?php echo $name_attr . '[slider_compare]'; // phpcs:ignore:XSS ok. ?>"
							>
								<?php
								if ( ! isset( $field_values['slider_compare'] ) ) {
									$field_values['slider_compare'] = '=';
								}
								?>

								<?php foreach ( $options['slider_compare'] as $option ) { ?>
									<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $option, $field_values['slider_compare'], true ); ?>><?php echo esc_attr( $option ); ?></option>
								<?php } ?>
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the comparison operator.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['label'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-label-option-wrap" data-usage="text,number,select,date,time,datetime,multiselect,checkboxes,radio,range_slider">

						<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label"><?php esc_attr_e( 'Field Label', 'geo-my-wp' ); ?></label>
								</div>
								<input
									type="text"
									name="<?php echo $name_attr . '[label]'; // phpcs:ignore:XSS ok. ?>"
									value="<?php echo ( isset( $field_values['label'] ) && ! is_array( $field_values['label'] ) ) ? esc_attr( stripcslashes( $field_values['label'] ) ) : ''; ?>"
									<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								/>
							</div>

							<?php if ( $options['second_enabled'] ) { ?>

								<div class="gmw-settings-panel-inner-option custom-field-second-option">

									<div class="gmw-settings-panel-header">
										<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Label', 'geo-my-wp' ); ?></label>
									</div>

									<input
										type="text"
										name="<?php echo $name_attr . '[second_label]'; // phpcs:ignore:XSS ok. ?>"
										value="<?php echo isset( $field_values['second_label'] ) ? esc_attr( stripcslashes( $field_values['second_label'] ) ) : ''; ?>"
										<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
									/>
								</div>

							<?php } ?>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter the field lable or leave blank to omit it.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['field_output'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-output-option-wrap">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Field Output', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-output gmw-settings-panel-input-container">

							<div class="gmw-settings-panel-inner-option">

								<input
									type="text"
									name="<?php echo $name_attr . '[field_output]'; // phpcs:ignore:XSS ok. ?>"
									value="<?php echo ( isset( $field_values['field_output'] ) && ! is_array( $field_values['field_output'] ) ) ? esc_attr( stripcslashes( $field_values['field_output'] ) ) : ''; ?>"
									<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								/>
							</div>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter the field lable or leave blank to hide it.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['placeholder'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-placeholder-option-wrap" data-usage="text,number,date,time,datetime">

						<div class="custom-field-option-placeholder gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label"><?php esc_attr_e( 'Field Placeholder', 'geo-my-wp' ); ?></label>
								</div>

								<input
									type="text"
									name="<?php echo $name_attr . '[placeholder]'; // phpcs:ignore:XSS ok. ?>"
									value="<?php echo ( isset( $field_values['placeholder'] ) && ! is_array( $field_values['placeholder'] ) ) ? esc_attr( stripcslashes( $field_values['placeholder'] ) ) : ''; ?>"
									<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								/>
							</div>

							<?php if ( $options['second_enabled'] ) { ?>

								<div class="gmw-settings-panel-inner-option custom-field-second-option">

									<div class="gmw-settings-panel-header">
										<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Placeholder', 'geo-my-wp' ); ?></label>
									</div>

									<input
										type="text"
										name="<?php echo $name_attr . '[second_placeholder]'; // phpcs:ignore:XSS ok. ?>"
										value="<?php echo isset( $field_values['second_placeholder'] ) ? esc_attr( stripcslashes( $field_values['second_placeholder'] ) ) : ''; ?>"
										<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
									/>
								</div>

							<?php } ?>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Enter the placeholder or leave blank to hide it.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['options_all'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-options-all-option-wrap" data-usage="select,multiselect,radio">

						<div class="custom-field-option-options-all gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label"><?php esc_attr_e( 'Options All Label', 'geo-my-wp' ); ?></label>
								</div>

								<input
									type="text"
									name="<?php echo $name_attr . '[show_options_all]'; // phpcs:ignore:XSS ok. ?>"
									value="<?php echo isset( $field_values['show_options_all'] ) ? esc_attr( stripcslashes( $field_values['show_options_all'] ) ) : ''; ?>"
									<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								/>
							</div>

							<?php if ( $options['second_enabled'] ) { ?>

								<div class="gmw-settings-panel-inner-option custom-field-second-option">

									<div class="gmw-settings-panel-header">
										<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Options All Label', 'geo-my-wp' ); ?></label>
									</div>

									<input
										type="text"
										name="<?php echo $name_attr . '[second_show_options_all]'; // phpcs:ignore:XSS ok. ?>"
										value="<?php echo isset( $field_values['second_show_options_all'] ) ? esc_attr( stripcslashes( $field_values['second_show_options_all'] ) ) : ''; ?>"
										<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
									/>
								</div>

							<?php } ?>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Enter the placeholder or leave blank to hide it.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['min_value'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-min-value-option-wrap" data-usage="range_slider">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Minimum Value', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-min-value gmw-settings-panel-input-container">

							<input
								type="number"
								name="<?php echo $name_attr . '[min_value]'; // phpcs:ignore:XSS ok. ?>"
								value="<?php echo isset( $field_values['min_value'] ) ? esc_attr( stripcslashes( $field_values['min_value'] ) ) : ''; ?>"
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
							/>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter the lowest value of the slider.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['max_value'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-max-value-option-wrap" data-usage="range_slider">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Maximum Value', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-max-value gmw-settings-panel-input-container">

							<input
								type="number"
								name="<?php echo $name_attr . '[max_value]'; // phpcs:ignore:XSS ok. ?>"
								value="<?php echo isset( $field_values['max_value'] ) ? esc_attr( stripcslashes( $field_values['max_value'] ) ) : ''; ?>"
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
							/>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter the highest value of the slider.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['value'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-value-option-wrap" data-usage="pre_defined,hidden,slider" data-usage_not="range_slider">

						<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label"><?php esc_attr_e( 'Default Value', 'geo-my-wp' ); ?></label>
								</div>

								<div>
									<input
										type="text"
										name="<?php echo $name_attr . '[value]'; // phpcs:ignore:XSS ok. ?>"
										value="<?php echo isset( $field_values['value'] ) ? esc_attr( stripcslashes( $field_values['value'] ) ) : ''; ?>"
										<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
									/>
								</div>
							</div>

							<?php if ( $options['second_enabled'] ) { ?>

								<div class="gmw-settings-panel-inner-option custom-field-second-option">

									<div class="gmw-settings-panel-header">
										<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Value', 'geo-my-wp' ); ?></label>
									</div>

									<div>
										<input
											type="text"
											name="<?php echo $name_attr . '[second_value]'; // phpcs:ignore:XSS ok. ?>"
											value="<?php echo isset( $field_values['second_value'] ) ? esc_attr( stripcslashes( $field_values['second_value'] ) ) : ''; ?>"
											<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
										/>
									</div>
								</div>

							<?php } ?>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter a default value or leave blank.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['slider_options'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-value-prefix-option-wrap" data-usage="range_slider">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Value Prefix', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-min-value-prefix gmw-settings-panel-input-container">

							<input
								type="text"
								name="<?php echo $name_attr . '[value_prefix]'; // phpcs:ignore:XSS ok. ?>"
								value="<?php echo isset( $field_values['value_prefix'] ) ? esc_attr( stripcslashes( $field_values['value_prefix'] ) ) : ''; ?>"
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
							/>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter the text that appears before the slider value.', 'geo-my-wp' ); ?>
						</div>
					</div>

					<div class="gmw-settings-panel-field custom-field-value-suffix-option-wrap" data-usage="range_slider">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_attr_e( 'Value Suffix', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-min-value-suffix gmw-settings-panel-input-container">

							<input
								type="text"
								name="<?php echo $name_attr . '[value_suffix]'; // phpcs:ignore:XSS ok. ?>"
								value="<?php echo isset( $field_values['value_suffix'] ) ? esc_attr( stripcslashes( $field_values['value_suffix'] ) ) : ''; ?>"
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
							/>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter the text that appears after the slider value.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( ! empty( $options['step'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-step-option-wrap" data-usage="range_slider">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Step', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-step gmw-settings-panel-input-container">

							<?php
							if ( empty( $field_values['step'] ) ) {
								$field_values['step'] = 1;
							}
							?>

							<input
								type="number"
								name="<?php echo $name_attr . '[step]'; // phpcs:ignore:XSS ok. ?>"
								value="<?php echo isset( $field_values['step'] ) ? esc_attr( stripcslashes( $field_values['step'] ) ) : '1'; ?>"
								<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Enter the slider step.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['smartbox'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-smartbox-option-wrap" data-usage="select,multiselect">

						<div class="gmw-settings-panel-header">
							<label
								for="custom-field-smartbox-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
								class="gmw-settings-label"><?php esc_html_e( 'Smart Select Field', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-smartbox gmw-settings-panel-input-container">

							<?php
							if ( ! isset( $field_values['smartbox'] ) ) {
								$field_values['smartbox'] = 0;
							}
							?>
							<label class="gmw-checkbox-toggle-field">
								<input
									id="custom-field-smartbox-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
									type="checkbox"
									name="<?php echo $name_attr . '[smartbox]'; // phpcs:ignore:XSS ok. ?>"
									value="1"
									<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
									<?php checked( $field_values['smartbox'], 1, true ); ?>
								/>
								<span class="gmw-checkbox-toggle"></span>
								<span class="gmw-checkbox-label"><?php esc_attr_e( 'Enable', 'geo-my-wp' ); ?></span>
							</label>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enable smart select field.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['post_types_cond'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-post-types-cond-option-wrap" data-usage="">

						<div class="gmw-settings-panel-header">

							<?php
							if ( ! isset( $field_values['post_types_cond'] ) ) {
								$field_values['post_types_cond'] = 0;
							}
							?>
							<label
								for="custom-field-post-types-cond-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
								class="gmw-settings-label"><?php esc_attr_e( 'Conditional Post Types ( beta )', 'geo-my-wp' ); ?></label>
						</div>

						<?php $no_sm_class = ! empty( $is_original ) ? 'gmw-smartbox-not' : ''; ?>

						<div class="gmw-settings-panel-input-container">
							<label class="gmw-multiselect-field">
								<select
									multiple
									id="custom-field-post-type-cond-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
									name="<?php echo $name_attr . '[post_types_cond][]'; // phpcs:ignore:XSS ok. ?>"
									class="post-types-selector <?php echo $no_sm_class;  // phpcs:ignore:XSS ok. ?>"
									<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
								/>
								<?php
								foreach ( self::$post_types as $post_name => $post_label ) {

									$selected = ( isset( $field_values['post_types_cond'] ) && is_array( $field_values['post_types_cond'] ) && in_array( $post_name, $field_values['post_types_cond'] ) ) ? ' selected="selected" ' : '';

									echo '<option value="' . esc_attr( $post_name ) . '" ' . $selected . '>'; // phpcs:ignore: XSS ok.
									echo esc_attr( $post_label );
									echo '</option>';
								}
								?>
								</select>
							</label>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the post types that you would like to sync this field with. This way this field will be visible only when one of those post types is selected in the front-end form. * This feature is relevant only when selecting multiple post types from the Post Types form option above.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['required'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-required-option-wrap" data-usage="text,number,select,date,time,datetime,multiselect,radio">

						<div class="gmw-settings-panel-header">

							<?php
							if ( ! isset( $field_values['required'] ) ) {
								$field_values['required'] = 0;
							}
							?>
							<label
								for="custom-field-required-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
								class="gmw-settings-label"><?php esc_attr_e( 'Required', 'geo-my-wp' ); ?></label>
						</div>

						<div class="gmw-settings-panel-input-container">
							<label class="gmw-checkbox-toggle-field">
								<input
									id="custom-field-required-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
									type="checkbox"
									name="<?php echo $name_attr . '[required]'; // phpcs:ignore:XSS ok. ?>"
									value="1"
									<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
									<?php checked( $field_values['required'], 1, true ); ?>
								/>
								<span class="gmw-checkbox-toggle"></span>
								<span class="gmw-checkbox-label"><?php esc_attr_e( 'Enable', 'geo-my-wp' ); ?></span>
							</label>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Make this field required.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( $options['disable_field'] ) { ?>

					<div class="gmw-settings-panel-field custom-field-disable-field-option-wrap" data-usage="">

						<div class="gmw-settings-panel-header">

							<?php
							if ( ! isset( $field_values['disable_field'] ) ) {
								$field_values['disable_field'] = 0;
							}
							?>

							<label
								for="custom-field-disable-field-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
								class="gmw-settings-label"><?php esc_html_e( 'Disable This Field', 'geo-my-wp' ); ?></label>
						</div>

						<div class="gmw-settings-panel-input-container">
							<label class="gmw-checkbox-toggle-field">
								<input
									id="custom-field-disable-field-<?php echo $field_name; // phpcs:ignore:XSS ok. ?>"
									type="checkbox"
									name="<?php echo $name_attr . '[disable_field]'; // phpcs:ignore:XSS ok. ?>"
									value="1"
									<?php echo $disabled; // phpcs:ignore:XSS ok. ?>
									<?php checked( $field_values['disable_field'], 1, true ); ?>
								/>
								<span class="gmw-checkbox-toggle"></span>
								<span class="gmw-checkbox-label"><?php esc_attr_e( 'Enable', 'geo-my-wp' ); ?></span>
							</label>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Check this checkbox to exclude this field from the search form.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

			</div>

		<?php if ( ! $args['disable_wrapper'] ) { ?>
			</div>
		<?php } ?>
		<?php
	}

	/**
	 * Output custom fields generateor.
	 *
	 * @param  array  $args  [description].
	 *
	 * @param  [type] $value [description].
	 *
	 * @param  array  $form  [description].
	 *
	 * @param  array  $options  [description].
	 *
	 * @since 4.4 ( moved from Premium Settings ).
	 */
	public static function get_custom_fields( $args, $value, $form, $options = array() ) {

		$defaults = array(
			'option_name'         => '',
			'get_fields_function' => 'gmw_get_custom_fields',
			'select_field_label'  => __( ' -- Select Field -- ', 'geo-my-wp' ),
			'add_field_label'     => __( 'Add Field', 'geo-my-wp' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$field_options = '';

		foreach ( $args as $cf_key => $cf_value ) {

			if ( ! in_array( $cf_key, array_keys( $defaults ) ) ) {
				$field_options .= ' data-gmw_ajax_load_' . $cf_key . '="' . $cf_value . '"';
			}
		}

		?>
		<div class="gmw-custom-fields-wrapper">

			<div id="gmw-custom-fields-new-field-picker">

				<span>
					<select class="gmw-custom-fields-picker gmw-smartbox" data-gmw_ajax_load_options="<?php echo esc_attr( $args['get_fields_function'] ); ?>" data-gmw_ajax_load_is_custom_fields="1" <?php echo $field_options; ?>>
						<option value=""><?php echo esc_attr( $args['select_field_label'] ); ?></option>
					</select>
				</span>

				<input
					type="button"
					class="gmw-new-custom-field-button gmw-settings-action-button button-primary" style="grid-column: span 1;margin-top: 0;padding: 13px;"
					form_id="<?php echo esc_attr( $form['ID'] ); ?>"
					value="<?php echo esc_attr( $args['add_field_label'] ); ?>"
				/>
			</div>

			<?php
			$args = array(
				'option_name' => $args['option_name'],
				'name'        => '%%field_name%%',
			);

			self::get_custom_field( $args, $value, $options );
			?>
			<div id="custom-fields-holder" class="gmw-setting-groups-container gmw-settings-group-draggable-area">
				<?php

				if ( ! empty( $value ) ) {

					foreach ( $value as $field_name => $values ) {

						$args = array(
							'option_name' => $args['option_name'],
							'name'        => $field_name,
							'is_original' => false,
						);

						self::get_custom_field( $args, $values, $options );
					}
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get users role.
	 *
	 * @since 4.0
	 *
	 * @param array $args argument.
	 *
	 * @return object of user roles.
	 */
	public static function get_user_roles( $args = array() ) {

		global $wp_roles;

		// phpcs:ignore.
		return $wp_roles->get_names();
	}

	/**
	 * Get authors.
	 *
	 * @since 4.0
	 *
	 * @param array $args args.
	 *
	 * @return [type] [description]
	 */
	public static function get_users( $args ) {

        // phpcs:disable.
		// $query_args = array();
		/*
        if ( ! empty( $args['gmw_ajax_load_options_query_args'] ) ) {
            $query_args = $args['gmw_ajax_load_options_query_args'];
        }

        if ( self::is_json( $args['gmw_ajax_load_options_query_args'] ) ) {
            $query_args = json_decode( $args['gmw_ajax_load_options_query_args'] );
        }*/
         // phpcs:enable.

		$query_args = array(
			'fields' => array( 'ID', 'display_name', 'user_email', 'user_nicename', 'user_login' ),
		);

		$query_args = apply_filters( 'gmw_ajax_load_get_users_args', $query_args, $args );
		$users      = get_users( $query_args );
		$output     = array();

		foreach ( $users as $user ) {
			$output[ $user->ID ] = '[' . $user->ID . '] [ ' . $user->display_name . ' ] [ ' . $user->user_nicename . ' ] [ ' . $user->user_email . ' ] [ ' . $user->user_login . ' ] ';
		}

		return $output;
	}

	/**
	 * Get array of taxonomy terms.
	 *
	 * @since 4.0
	 *
	 * @param  [type] $taxonomy [description].
	 *
	 * @return [type]           [description]
	 */
	public static function get_taxonomy_terms_array( $taxonomy ) {

		if ( empty( $taxonomy ) ) {
			return array();
		}

		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		);

		$terms = get_terms( $args );

		// Abort if error or nothing was found.
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		$output = array();

		// Collect terms into an array.
		foreach ( $terms as $term ) {
			$output[ $term->term_id ] = $term->name . ' ( ID ' . $term->term_id . ' )';
		}

		return $output;
	}

	/**
	 * Get an array of all post custom fields.
	 *
	 * @since 4.0
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_custom_fields_array( $args = array() ) {

		global $wpdb;

        // phpcs:disable.
        // db call ok, cache ok.
		$keys = $wpdb->get_col(
			"SELECT meta_key
        	FROM $wpdb->postmeta
        	GROUP BY meta_key
        	ORDER BY meta_id DESC"
		);
        // phpcs:enable.

		$output = array();

		if ( $keys ) {

			natcasesort( $keys );

			// Collect terms into an array.
			foreach ( $keys as $key ) {

				$key = esc_attr( $key );

				$output[ $key ] = $key;
			}
		}

		/**
		 * Collect fields generated by the Advacned Custom Fields plugin.
		 */
		if ( class_exists( 'ACF' ) ) {

			$args = array(
				'post_type'      => 'acf-field',
				'posts_per_page' => -1,
			);

			$fields = new WP_Query( $args );

			if ( ! empty( $fields->posts ) ) {

				foreach ( $fields->posts as $post ) {

					if ( ! empty( $post->post_excerpt ) && ! isset( $output[ $post->post_excerpt ] ) ) {
						$output[ $post->post_excerpt ] = $post->post_excerpt;
					}
				}
			}
		}

		return $output;
	}

	// phpcs:disable.
	/*
	public static function get_advanced_custom_field_ajax() {

		if ( empty( $_POST['field_name'] ) || empty( $_POST['action'] ) || 'gmw_new_advanced_custom_field' !== $_POST['action'] ) {
			return wp_send_json( false );
		}

		$field = false;

		if ( function_exists( 'acf_get_field' ) ) {
			$field = acf_get_field(  $_POST['field_name'] );
		}

		return wp_send_json( $field );
	}
	*/
	// phpcs:enable.

	// phpcs:disable.
	/**
	 * Get an array of all post custom fields.
	 *
	 * @since 4.0
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	/*
	public static function get_advanced_custom_fields_array( $args = array() ) {

		$args = array(
			'post_type'      => 'acf-field',
			'posts_per_page' => -1,
		);

		$fields = new WP_Query( $args );

		if ( empty( $fields->posts ) ) {
			return array();
		}

		$output        = array();
		$allowed_types = array( 'text', 'email', 'url', 'number', 'range', 'select', 'checkbox', 'radio', 'date_picker', 'time_picker' );
		$allowed_types = array( 'clone', 'flexible_content', 'gallery', 'repeater', 'range', 'select', 'checkbox', 'radio', 'date_picker', 'time_picker' );


		foreach ( $fields->posts as $post ) {

			$field_data = maybe_unserialize( $post->post_content );

			if ( ! empty( $field_data['type'] ) && in_array( $field_data['type'], $allowed_types, true ) ) {
				$output[ $post->ID ] = ! empty( $post->post_title ) ? $post->post_title : '(no label)';
			}
		}

		return $output;
	}
	*/
	// phpcs:enable.

	/**
	 * Get an array of all user meta fields.
	 *
	 * @since 4.0
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_user_meta( $args = array() ) {

		global $wpdb;

        // phpcs:disable.
        // db call ok, cache ok.
		$keys = $wpdb->get_col(
			"SELECT meta_key
        	FROM $wpdb->usermeta
        	GROUP BY meta_key
        	ORDER BY umeta_id DESC"
		);
        // phpcs:enable.

		$output = array();

		if ( $keys ) {

			natcasesort( $keys );

			// Collect terms into an array.
			foreach ( $keys as $key ) {

				$key = esc_attr( $key );

				$output[ $key ] = $key;
			}
		}

		return $output;
	}

	/**
	 * Get GEO my WP's location meta fields.
	 *
	 * @return [type] [description]
	 */
	public static function get_location_meta() {

		global $wpdb, $blog_id;

        // phpcs:disable.
        // db call ok, cache ok.
		$location_meta = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT DISTINCT meta.`meta_key`
			 	FROM {$wpdb->base_prefix}gmw_locationmeta meta
			 	INNER JOIN {$wpdb->base_prefix}gmw_locations locations
			 	ON meta.location_id = locations.ID
			 	WHERE locations.blog_id = %d",
				array( $blog_id )
			)
		);
        // phpcs:enable.

		if ( empty( $location_meta ) ) {
			return array();
		}

		$new_array = array();

		foreach ( $location_meta as $meta ) {

			// skip days_hours since it has its own settings.
			if ( 'days_hours' === $meta || 0 === strpos( $meta, '_' ) ) {
				continue;
			}

			$new_array[ $meta ] = $meta;
		}

		$location_meta = $new_array;

		return $location_meta;
	}

	/**
	 * Get all taxonomy terms into an array.
	 *
	 * @since 4.0.
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_all_taxonomy_terms( $args = array() ) {

		$taxonomies = get_object_taxonomies( array_values( get_post_types() ) );
		$args       = array(
			'taxonomy'   => $taxonomies,
			'hide_empty' => false,
		);

		$terms = get_terms( $args );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		usort( $terms, array( 'self', 'sort_taxonomy_terms_groups' ) );

		$output = array();

		foreach ( $terms as $term ) {

			$term_id = $term->term_taxonomy_id;

			$output[] = array(
				'value' => $term_id,
				'label' => $term->slug . ' ( ID ' . $term_id . ' ) ( ' . $term->taxonomy . ' ) ',
			);
		}

		return $output;
	}

	/**
	 * Get all taxonomy terms into an array.
	 *
	 * @since 4.0.
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_peepso_profile_fields( $args = array() ) {

		if ( ! class_exists( 'PeepSoUser' ) ) {
			return array();
		}

		$peepso_user    = PeepSoUser::get_instance( 0 );
		$profile_fields = new PeepSoProfileFields( $peepso_user );
		$profile_fields = $profile_fields->load_fields();
		$output         = array();

		foreach ( $profile_fields as $field_type => $args ) {

			if ( in_array( $args->meta->class, array( 'text', 'selectsingle', 'location' ), true ) ) {
				$output[ $args->id ] = $args->title . ' ( Field ID ' . $args->id . ' )';
			}
		}

		return $output;
	}

	/**
	 * Get terms taxonomy array
	 *
	 * @param  string  $taxonomy    taxonomy name.
	 *
	 * @param  mixed   $values      values.
	 *
	 * @param  boolean $sort_groups use option groups.
	 *
	 * @param  string  $field       field type.
	 *
	 * @return [type]               [description]
	 */
	public static function get_taxonomy_terms( $taxonomy = 'category', $values = array(), $sort_groups = false, $field = 'term_id' ) {

		if ( ! is_array( $values ) ) {
			$values = explode( ',', $values );
		}

		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		);

		$terms = get_terms( $args );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		if ( ! $sort_groups ) {

			if ( 'term_taxonomy_id' !== $field ) {
				$field = 'term_id';
			}

			$current_tax = $terms[0]->taxonomy;

			foreach ( $terms as $term ) {

				$selected = ( ! empty( $values ) && in_array( $term->$field, $values ) ) ? 'selected="selected"' : ''; // phpcs:ignore.
				$term_id  = esc_attr( $term->$field );
				$label    = esc_attr( $term->name );

				if ( IS_ADMIN ) {
					$label .= ' ( ID ' . $term_id . ' )';
				}

				echo '<option value="' . $term_id . '" ' . $selected . ' >' . $label . '</option>'; // phpcs:ignore: XSS ok.
			}
		} else {

			$current_tax = $terms[0]->taxonomy;

			usort( $terms, array( 'self', 'sort_taxonomy_terms_groups' ) );

			echo '<optgroup label="' . esc_attr( $current_tax ) . '">'; // phpcs:ignore: XSS ok.

			foreach ( $terms as $term ) {

				$selected = in_array( $term->term_taxonomy_id, $values ) ? 'selected="selected"' : ''; // phpcs:ignore.

				if ( $term->taxonomy !== $current_tax ) {

					echo '</optgroup>';
					$current_tax = $term->taxonomy;
					echo '<optgroup label="' . esc_attr( $term->taxonomy ) . '">';
				}

				$term_id = esc_attr( $term->term_taxonomy_id );
				$label   = esc_attr( $term->slug );

				if ( IS_ADMIN ) {
					$label .= ' ( ID ' . $term_id . ' )';
				}

				echo '<option value="' . $term_id . '" ' . $selected . ' >' . $label . '</option>'; // phpcs:ignore: XSS ok.
			}
		}
	}

	/**
	 * Get BNP xprofile fields array
	 *
	 * @return [type]               [description]
	 */
	public static function get_xprofile_fields() {

		// verify BuddyPress plugin.
		if ( ! class_exists( 'Buddypress' ) ) {
			return array();
		}

		global $bp;

		// show message if Xprofile Fields component deactivated.
		if ( ! bp_is_active( 'xprofile' ) ) {

			gmw_trigger_error( esc_html__( 'Buddypress xprofile fields component is deactivated. You need to activate in in order to use this feature.', 'geo-my-wp' ), E_USER_NOTICE );

			return array();
		}

		// check for profile fields.
		if ( function_exists( 'bp_has_profile' ) ) {

			$args = array(
				'hide_empty_fields' => false,
				'member_type'       => bp_get_member_types(),
			);

			$fields = array(
				'all_fields'    => array(),
				'date_field'    => array(),
				'no_date_field' => array(),
			);

			// display profile fields.
			if ( bp_has_profile( $args ) ) {

				while ( bp_profile_groups() ) {

					bp_the_profile_group();

					while ( bp_profile_fields() ) {

						bp_the_profile_field();

						$field_type = bp_get_the_profile_field_type();

						if ( 'datebox' === $field_type || 'birthdate' === $field_type ) {

							$fields['date_field'][ bp_get_the_profile_field_id() ] = bp_get_the_profile_field_name();

						} else {

							$fields['no_date_field'][ bp_get_the_profile_field_id() ] = bp_get_the_profile_field_name();
						}

						$fields['all_fields'][ bp_get_the_profile_field_id() ] = bp_get_the_profile_field_name();
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Get member types.
	 *
	 * @param  [type] $args [description].
	 *
	 * @return [type]       [description]
	 */
	public static function get_bp_member_types( $args ) {

		$member_types = array();

		if ( function_exists( 'bp_get_member_types' ) ) {

			foreach ( bp_get_member_types( array(), 'object' ) as $type ) {
				$member_types[ $type->name ] = ! empty( $type->labels['name'] ) ? $type->labels['name'] : $type->name;
			}
		}

		return $member_types;
	}

	/**
	 * Get group types.
	 *
	 * @param array $args argument.
	 *
	 * @return [type]       [description]
	 */
	public static function get_bp_group_types( $args = array() ) {

		$group_types = array();

		if ( function_exists( 'bp_groups_get_group_types' ) ) {

			foreach ( bp_groups_get_group_types( array(), 'object' ) as $type ) {
				$group_types[ $type->name ] = $type->labels['name'];
			}
		}

		return $group_types;
	}

	/**
	 * Get BP Groups.
	 *
	 * @param array $args argument.
	 *
	 * @return [type]       [description]
	 */
	public static function get_bp_groups( $args = array() ) {

		$output = array();

		if ( class_exists( 'BP_Groups_Group' ) ) {

			$groups = BP_Groups_Group::get(
				array(
					'type'     => 'alphabetical',
					'per_page' => 999,
				)
			);

			foreach ( $groups['groups'] as $group ) {

				if ( 0 === absint( $group->id ) ) {
					continue;
				}

				$output[ $group->id ] = $group->name;
			}
		}

		return $output;
	}

	/**
	 * Get an array of all BP group meta fields.
	 *
	 * @since 4.0
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_bp_group_meta( $args = array() ) {

		global $wpdb;

        // phpcs:disable.
        // db call ok, cache ok.
		$keys = $wpdb->get_col(
			"SELECT meta_key
        	FROM {$wpdb->prefix}bp_groups_groupmeta
        	GROUP BY meta_key
        	ORDER BY meta_key ASC"
		);
        // phpcs:enable.

		$output = array();

		if ( $keys ) {

			natcasesort( $keys );

			// Collect terms into an array.
			foreach ( $keys as $key ) {

				$key = esc_attr( $key );

				$output[ $key ] = $key;
			}
		}

		return $output;
	}

	/**
	 * Get an array of Gravity Forms's forms.
	 *
	 * @since 4.4
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_gforms_forms( $args = array() ) {

		$output = array();

		if ( ! class_exists( 'GFAPI' ) ) {
			return $output;
		}

		$forms = GFAPI::get_forms();

		if ( empty( $forms ) ) {
			return $output;
		}

		// Collect terms into an array.
		foreach ( $forms as $form ) {
			$output[ $form['id'] ] = $form['title'] . ' ( form ID ' . $form['id'] . ' )';
		}

		return $output;
	}

	/**
	 * Get an array of Gravity Forms's form field.
	 *
	 * @since 4.4
	 *
	 * @param array $args argument.
	 *
	 * @return [type] [description]
	 */
	public static function get_gforms_fields( $args = array() ) {

		$output = array();

		if ( ! class_exists( 'GFAPI' ) || empty( $args['gmw_ajax_load_gform_id'] ) ) {
			return $output;
		}

		$form = GFAPI::get_form( $args['gmw_ajax_load_gform_id'] );

		if ( empty( $form['fields'] ) ) {
			return $output;
		}

		$fields = array(
			'usage'  => 'include',
			'fields' => array(),
		);

		$exclude_fields = '';

		if ( ! empty( $args['gmw_ajax_load_include_fields'] ) ) {

			$fields['fields'] = explode( ',', $args['gmw_ajax_load_include_fields'] );

		} elseif ( ! empty( $args['gmw_ajax_load_exclude_fields'] ) ) {

			$fields['usage']  = 'exclude';
			$fields['fields'] = explode( ',', $args['gmw_ajax_load_exclude_fields'] );
		}

		// Collect terms into an array.
		foreach ( $form['fields'] as $field ) {

			if ( ! empty( $fields['fields'] ) ) {

				if ( in_array( $field->type, $fields['fields'], true ) ) {

					if ( 'include' === $fields['usage'] ) {

						$output[ $field->id ] = $field->label . ' ( field ID ' . $field->id . ' )';

						continue;

					} else {

						continue;
					}
				} elseif ( 'include' === $fields['usage'] ) {
					continue;
				}
			}

			$output[ $field->id ] = $field->label . ' ( field ID ' . $field->id . ' )';
		}

		return $output;
	}

	/**
	 * Get list of GMW template files.
	 *
	 * @param mixed $args argument to filter template files.
	 *
	 * @return array
	 */
	public static function get_templates( $args ) {

		$args = array(
			'component'   => $args['gmw_ajax_load_component'],
			'addon'       => $args['gmw_ajax_load_addon'],
			'folder_name' => $args['gmw_ajax_load_type'],
		);

		$templates = gmw_get_templates( $args );

		$new_templates        = array();
		$new_dep_templates    = array();
		$new_custom_templates = array();
		$bp_template          = function_exists( 'bp_get_theme_package_id' ) ? bp_get_theme_package_id() : '';

		// Marked deprecated templates.
		foreach ( $templates as $value => $name ) {

			if ( strpos( $value, 'custom_' ) !== false ) {

				$new_custom_templates[ $value ] = $name;

			} else {

				global $buddyboss_platform_plugin_file;

				if ( strpos( $value, 'buddyboss' ) !== false && ( ! function_exists( 'buddyboss_theme' ) || empty( $buddyboss_platform_plugin_file ) ) ) {

					$new_templates[ $value ] = $name . ' ( requires the BuddyBoss theme and BuddyBoss platform plugin )';

				} elseif ( strpos( $value, 'youzify' ) !== false && ! class_exists( 'Youzify' ) ) {

					$new_templates[ $value ] = $name . ' ( requires the Youzify plugin )';

				} elseif ( strpos( $value, 'peepso' ) !== false && ! class_exists( 'PeepSo' ) ) {

					$new_templates[ $value ] = $name . ' ( requires the PeepSo plugin )';

				} elseif ( strpos( $value, 'buddypress-legacy' ) !== false && 'legacy' !== $bp_template ) {

					$new_templates[ $value ] = $name . ' ( requires the BuddyPress Legacy template pack )';

				} elseif ( strpos( $value, 'buddypress-nouveau' ) !== false && 'nouveau' !== $bp_template ) {

					$new_templates[ $value ] = $name . ' ( requires the BuddyPress Nouveau template pack )';

				} elseif ( strpos( $value, 'rehub' ) !== false && ! function_exists( 'rehub_framework_register_scripts' ) ) {

					$new_templates[ $value ] = $name . ' ( requires the ReHub theme )';

				} elseif ( 'search-forms' === $args['folder_name'] && in_array( $value, array( 'default', 'compact', 'horizontal', 'horizontal-gray', 'gray', 'purple', 'yellow', 'blue', 'red', 'left-white', 'right-white' ), true ) ) {

					$name .= ' ( deprecated )';

					$new_dep_templates[ $value ] = '* ' . $name;

				} elseif ( 'search-results' === $args['folder_name'] && in_array( $value, array( 'clean', 'custom', 'default', 'grid-gray', 'grid', 'purple', 'gray', 'yellow', 'blue', 'red' ), true ) ) {

					$name .= ' ( deprecated )';

					$new_dep_templates[ $value ] = '* ' . $name;

				} elseif ( 'info-window' === $args['folder_name'] && in_array( $value, array( 'center-white', 'left-white', 'right-white' ), true ) ) {

					$name .= ' ( deprecated )';

					$new_dep_templates[ $value ] = '* ' . $name;

				} else {

					$new_templates[ $value ] = $name;

				}
			}
		}

		$output = array_merge( $new_templates, $new_dep_templates, $new_custom_templates );

        // phpcs:disable.
		/*if ( 'ajax_forms' === $args['addon'] && 'search-results' === $args['folder_name'] ) {
            $output = array_merge( array( 'disabled' => __( 'Disable the search results', 'geo-my-wp' ) ), $output );
        }*/
        // phpcs:enable.

		return $output;
	}

	/**
	 * Get field options via AJAX call.
	 *
	 * @since 4.0
	 */
	public static function get_field_options_ajax() {

		// ajax_load_options holds the function name. If missing, abort.
		if ( empty( $_POST['args']['gmw_ajax_load_options'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, CSRF ok, sanitization ok.
			echo wp_json_encode( array() );
		} else {
			echo wp_json_encode( self::get_field_options( $_POST['args'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, CSRF ok, sanitization ok.
		}

		die;
	}

	/**
	 * Get field options.
	 *
	 * This function get the options of a setting field that is generated via AJAX.
	 *
	 * @param array $args field arguments.
	 *
	 * @since 4.0
	 *
	 * @author Eyal Fitoussi
	 *
	 * @return [type] [description]
	 */
	public static function get_field_options( $args = array() ) {

		if ( empty( $args ) ) {
			return array();
		}

		$output = array();
		$action = $args['gmw_ajax_load_options'];

		// Get pages.
		if ( 'gmw_get_pages' === $action ) {
			$output = self::get_pages( $args );
		}

		// Get post types.
		if ( 'gmw_get_post_types' === $action ) {
			$output = self::get_post_types( $args );
		}

		// Get users.
		if ( 'gmw_get_users' === $action ) {
			$output = self::get_users( $args );
		}

		// Get user roles.
		if ( 'gmw_get_user_roles' === $action ) {
			$output = self::get_user_roles( $args );
		}

		// Get taxonomy terms.
		if ( 'gmw_get_taxonomy_terms' === $action ) {

			$output = array();

			if ( ! empty( $args['gmw_ajax_load_options_taxonomy'] ) ) {
				$output = self::get_taxonomy_terms_array( $args['gmw_ajax_load_options_taxonomy'] );
			}
		}

		// Get custom fields.
		if ( 'gmw_get_custom_fields' === $action ) {
			$output = self::get_custom_fields_array( $args );
		}

		/*
		// Get custom fields.
		if ( 'gmw_get_advanced_custom_fields' === $action ) {
			$output = self::get_advanced_custom_fields_array( $args );
		}
		*/

		// Get custom fields.
		if ( 'gmw_get_user_meta' === $action ) {
			$output = self::get_user_meta( $args );
		}

		if ( 'gmw_get_location_meta' === $action ) {
			$output = self::get_location_meta();
		}

		if ( 'gmw_get_bp_xprofile_fields' === $action ) {

			$xprofile_field = self::get_xprofile_fields();
			$output         = $xprofile_field['all_fields'];

			if ( ! empty( $args['gmw_ajax_load_options_xprofile'] ) ) {

				if ( 'date_field' === $args['gmw_ajax_load_options_xprofile'] ) {

					$output = $xprofile_field['date_field'];

				} elseif ( 'no_date_field' === $args['gmw_ajax_load_options_xprofile'] ) {

					$output = $xprofile_field['no_date_field'];

				} else {

					$output = $xprofile_field['all_fields'];
				}
			}
		}

		if ( 'gmw_get_bp_member_types' === $action ) {
			$output = self::get_bp_member_types( $args );
		}

		if ( 'gmw_get_bp_group_types' === $action ) {
			$output = self::get_bp_group_types( $args );
		}

		if ( 'gmw_get_bp_groups' === $action ) {
			$output = self::get_bp_groups( $args );
		}

		// Get custom fields.
		if ( 'gmw_get_bp_group_meta' === $action ) {
			$output = self::get_bp_group_meta( $args );
		}

		if ( 'gmw_get_templates' === $action ) {
			$output = self::get_templates( $args );
		}

		if ( 'gmw_get_all_taxonomy_terms' === $action ) {
			$output = self::get_all_taxonomy_terms( $args );
		}

		if ( 'gmw_get_peepso_profile_fields' === $action ) {
			$output = self::get_peepso_profile_fields( $args );
		}

		if ( 'gmw_get_gforms_forms' === $action ) {
			$output = self::get_gforms_forms( $args );
		}

		if ( 'gmw_get_gforms_fields' === $action ) {
			$output = self::get_gforms_fields( $args );
		}

		if ( ! empty( $args['gmw_ajax_load_options_disabled'] ) ) {
			$output = array( 'disabled' => __( 'Disabled', 'geo-my-wp' ) ) + $output;
		}

		$output = apply_filters( 'gmw_get_field_options_via_ajax', $output, $action, $args );

		return $output;
	}

	/**
	 * Generate form field options.
	 *
	 * @since 4.0
	 *
	 * @author Eyal Fitoussi
	 *
	 * @param mixed $args array || string $args can be string of pre-defined option name or array of field args.
	 *
	 * @return array
	 */
	public static function get_setting_args( $args ) {

		if ( is_string( $args ) ) {

			$option = $args;
			$args   = array();

		} else {
			// Specific option type.
			$option = isset( $args['option_type'] ) ? $args['option_type'] : '';
		}

		// Default option args.
		$defaults = array(
			'name'          => '',
			'type'          => 'text',
			'default'       => '',
			'label'         => '',
			'cb_label'      => '',
			'placeholder'   => '',
			'desc'          => '',
			'options'       => array(),
			'class'         => '',
			'attributes'    => array(),
			'force_default' => 0,
			'priority'      => 0,
			// phpcs:ignore.
			'sub_option'    => ( ! empty( $_GET['page'] ) && 'gmw-settings' === $_GET['page'] ) ? false : true, // On settings page, set it to false by default.
			'fields'        => array(),
			'ps_required'   => 0,
		);

		if ( 'label' === $option ) {

			$defaults['name']     = 'label';
			$defaults['label']    = __( 'Field Label', 'geo-my-wp' );
			$defaults['desc']     = __( 'Enter the field\'s label or leave it blank to hide it.', 'geo-my-wp' );
			$defaults['priority'] = 10;

		} elseif ( 'smartbox' === $option ) {

			$defaults['name']     = 'smartbox';
			$defaults['type']     = 'checkbox';
			$defaults['cb_label'] = __( 'Enable', 'geo-my-wp' );
			$defaults['label']    = __( 'Select Smart Box', 'geo-my-wp' );
			$defaults['desc']     = __( 'Enable select smart box.', 'geo-my-wp' );
			$defaults['priority'] = 8;

		} elseif ( 'placeholder' === $option ) {

			$defaults['name']     = 'placeholder';
			$defaults['label']    = __( 'Placeholder', 'geo-my-wp' );
			$defaults['desc']     = __( 'Enter the field\'s placeholder or leave blank to hide it.', 'geo-my-wp' );
			$defaults['priority'] = 15;

		} elseif ( 'show_options_all' === $option ) {

			$defaults['name']     = 'show_options_all';
			$defaults['label']    = __( 'Options all label', 'geo-my-wp' );
			$defaults['desc']     = __( 'Enter the lable that will be the first option in the select dropdown ( or leave it blank ). This option will have no value and usually will display all options.', 'geo-my-wp' );
			$defaults['priority'] = 30;

		} elseif ( 'required' === $option ) {

			$defaults['name']     = 'required';
			$defaults['type']     = 'checkbox';
			$defaults['label']    = __( 'Required', 'geo-my-wp' );
			$defaults['cb_label'] = __( 'Enable', 'geo-my-wp' );
			$defaults['desc']     = __( 'Make this a required field.', 'geo-my-wp' );
			$defaults['priority'] = 80;

		} elseif ( 'usage_select' === $option ) {

			$defaults['name']     = 'usage';
			$defaults['type']     = 'select';
			$defaults['label']    = __( 'Usage', 'geo-my-wp' );
			$defaults['default']  = 'disabled';
			$defaults['desc']     = __( 'Select the field usage.', 'geo-my-wp' );
			$defaults['priority'] = 5;
			$defaults['class']    = 'gmw-smartbox-not gmw-options-toggle';
			$defaults['options']  = array(
				'disabled'    => __( 'Disable', 'geo-my-wp' ),
				'pre_defined' => __( 'Pre-defined', 'geo-my-wp' ),
				'select'      => __( 'Select dropdown', 'geo-my-wp' ),
				'multiselect' => __( 'Multi-Select box', 'geo-my-wp' ),
				'checkboxes'  => __( 'Checkboxes', 'geo-my-wp' ),
				'radio'       => __( 'Radio buttons', 'geo-my-wp' ),
			);

		} elseif ( 'usage_include_exclude' === $option ) {

			$defaults['name']     = 'usage';
			$defaults['type']     = 'select';
			$defaults['label']    = __( 'Usage', 'geo-my-wp' );
			$defaults['default']  = 'disabled';
			$defaults['desc']     = __( 'Select the field usage.', 'geo-my-wp' );
			$defaults['priority'] = 5;
			$defaults['class']    = 'gmw-smartbox-not gmw-options-toggle';
			$defaults['options']  = array(
				'disabled' => __( 'Disable', 'geo-my-wp' ),
				'include'  => __( 'Include', 'geo-my-wp' ),
				'exclude'  => __( 'Exclude', 'geo-my-wp' ),
			);

		} elseif ( 'address_fields_output' === $option ) {

			$defaults['name']       = 'address_fields';
			$defaults['type']       = 'multiselect';
			$defaults['label']      = __( 'Address Fields', 'geo-my-wp' );
			$defaults['default']    = array();
			$defaults['desc']       = __( 'Select the address fields to display.', 'geo-my-wp' );
			$defaults['priority']   = 10;
			$defaults['attributes'] = array( 'data' => 'multiselect_address_fields' );
			$defaults['options']    = array(
				'address'      => __( 'Full address', 'geo-my-wp' ),
				'street'       => __( 'Street', 'geo-my-wp' ),
				'premise'      => __( 'Apt/Suit ', 'geo-my-wp' ),
				'neighborhood' => __( 'Neighborhood', 'geo-my-wp' ),
				'county'       => __( 'County', 'geo-my-wp' ),
				'city'         => __( 'City', 'geo-my-wp' ),
				'region_name'  => __( 'State / Region name', 'geo-my-wp' ),
				'region_code'  => __( 'State / Region code', 'geo-my-wp' ),
				'postcode'     => __( 'Postcode', 'geo-my-wp' ),
				'country_name' => __( 'Country name', 'geo-my-wp' ),
				'country_code' => __( 'Country code', 'geo-my-wp' ),
			);

		} elseif ( 'map_width' === $option ) {

			$defaults['name']     = 'map_width';
			$defaults['type']     = 'text';
			$defaults['label']    = __( 'Map Width', 'geo-my-wp' );
			$defaults['default']  = '100%';
			$defaults['desc']     = __( 'Enter the map width in pixels or percentage ( ex. 200px or 100% ).', 'geo-my-wp' );
			$defaults['priority'] = 30;

		} elseif ( 'map_height' === $option ) {

			$defaults['name']     = 'map_height';
			$defaults['type']     = 'text';
			$defaults['label']    = __( 'Map Height', 'geo-my-wp' );
			$defaults['default']  = '300px';
			$defaults['desc']     = __( 'Enter the map height in pixels or percentage ( ex. 200px or 100% ).', 'geo-my-wp' );
			$defaults['priority'] = 40;

		} elseif ( 'map_type' === $option ) {

			$defaults['name']     = 'map_type';
			$defaults['type']     = 'select';
			$defaults['label']    = __( 'Map Type', 'geo-my-wp' );
			$defaults['default']  = array();
			$defaults['desc']     = __( 'Select the map type.', 'geo-my-wp' );
			$defaults['priority'] = 50;
			$defaults['class']    = 'gmw-smartbox-not';
			$defaults['options']  = array(
				'ROADMAP'   => __( 'ROADMAP', 'geo-my-wp' ),
				'SATELLITE' => __( 'SATELLITE', 'geo-my-wp' ),
				'HYBRID'    => __( 'HYBRID', 'geo-my-wp' ),
				'TERRAIN'   => __( 'TERRAIN', 'geo-my-wp' ),
			);

		} elseif ( 'location_form_exclude_fields_groups' === $option ) {

			$defaults['name']     = 'location_form_exclude_fields_groups';
			$defaults['type']     = 'multiselect';
			$defaults['label']    = __( 'Exclude Form Field Groups', 'geo-my-wp' );
			$defaults['desc']     = __( 'Select the field groups that you wish to exclude from the location form.', 'geo-my-wp' );
			$defaults['default']  = array();
			$defaults['priority'] = 5;
			$defaults['options']  = array(
				'location'    => __( 'Location', 'geo-my-wp' ),
				'address'     => __( 'Address', 'geo-my-wp' ),
				'coordinates' => __( 'Coordinates', 'geo-my-wp' ),
				'contact'     => __( 'Contact Info', 'geo-my-wp' ),
				'days_hours'  => __( 'Days & Hours', 'geo-my-wp' ),
			);

		} elseif ( 'location_form_exclude_fields' === $option ) {

			$defaults['name']     = 'location_form_exclude_fields';
			$defaults['type']     = 'multiselect';
			$defaults['label']    = __( 'Exclude Form Fields', 'geo-my-wp' );
			$defaults['desc']     = __( 'Select specific fields that you wish to exclude from the location form.', 'geo-my-wp' );
			$defaults['default']  = array();
			$defaults['priority'] = 10;
			$defaults['options']  = array(
				'address'      => __( 'Address ( with autocomplete )', 'geo-my-wp' ),
				'map'          => __( 'Map', 'geo-my-wp' ),
				'street'       => __( 'Street', 'geo-my-wp' ),
				'premise'      => __( 'Apt/Suit ', 'geo-my-wp' ),
				'city'         => __( 'City', 'geo-my-wp' ),
				'region_name'  => __( 'State', 'geo-my-wp' ),
				'postcode'     => __( 'Postcode', 'geo-my-wp' ),
				'country_code' => __( 'Country', 'geo-my-wp' ),
				'latitude'     => __( 'Latitude', 'geo-my-wp' ),
				'longitude'    => __( 'Longitude', 'geo-my-wp' ),
			);

		} elseif ( 'location_form_template' === $option ) {

			$defaults['name']     = 'location_form_template';
			$defaults['type']     = 'select';
			$defaults['label']    = __( 'Form Template', 'geo-my-wp' );
			$defaults['desc']     = __( 'Select the Location form template.', 'geo-my-wp' );
			$defaults['default']  = 'location-form-tabs-top';
			$defaults['priority'] = 15;
			$defaults['class']    = 'gmw-smartbox-not';
			$defaults['options']  = array(
				'location-form-tabs-top'  => __( 'Tabs Top ', 'geo-my-wp' ),
				'location-form-tabs-left' => __( 'Tabs Left', 'geo-my-wp' ),
				'location-form-no-tabs'   => __( 'No Tabs', 'geo-my-wp' ),
			);

		} elseif ( 'marker_grouping' === $option ) {

			$defaults['name']     = 'grouping';
			$defaults['type']     = 'select';
			$defaults['label']    = __( 'Markers Grouping', 'geo-my-wp' );
			$defaults['desc']     = __( 'Enable this to group markers that are close together on the map.', 'geo-my-wp' );
			$defaults['default']  = 'standard';
			$defaults['priority'] = 10;
			$defaults['class']    = 'gmw-smartbox-not';
			$defaults['options']  = array(
				'standard'           => __( 'No Grouping', 'geo-my-wp' ),
				'markers_clusterer'  => __( 'Markers clusterer', 'geo-my-wp' ),
				'markers_spiderfier' => __( 'Markers Spiderfier ( Deprecated )', 'geo-my-wp' ),
			);

		} elseif ( 'map_controls' === $option ) {

			$controls = array(
				'zoomControl'      => __( 'Zoom', 'geo-my-wp' ),
				'scrollwheel'      => __( 'Scrollwheel zoom', 'geo-my-wp' ),
				'resizeMapControl' => __( 'Resize map trigger', 'geo-my-wp' ),
			);

			if ( 'google_maps' === GMW()->maps_provider ) {
				$controls['rotateControl']      = __( 'Rotate Control', 'geo-my-wp' );
				$controls['scaleControl']       = __( 'Scale', 'geo-my-wp' );
				$controls['mapTypeControl']     = __( 'Map Type', 'geo-my-wp' );
				$controls['streetViewControl']  = __( 'Street View', 'geo-my-wp' );
				$controls['overviewMapControl'] = __( 'Overview', 'geo-my-wp' );
			}

			$defaults['name']        = 'map_controls';
			$defaults['type']        = 'multiselect';
			$defaults['placeholder'] = __( 'Select map controls', 'geo-my-wp' );
			$defaults['label']       = __( 'Map Controls', 'geo-my-wp' );
			$defaults['desc']        = __( 'Select the map controls that you wish to enable.', 'geo-my-wp' );
			$defaults['default']     = 'standard';
			$defaults['options']     = $controls;
			$defaults['priority']    = 50;

		} elseif ( 'premium_message' === $option ) {

			$defaults['extension_name']  = 'Premium Settings';
			$defaults['extension_class'] = 'GMW_Premium_Settings_Addon';
			$defaults['field_name']      = 'Premium Settings';
			$defaults['extension_link']  = 'https://geomywp.com/extensions/premium-settings';
			$defaults['message']         = '';
			$defaults['option_disabled'] = false;

			$args = wp_parse_args( $args, $defaults );

			if ( empty( $args['message'] ) ) {

				if ( $args['option_disabled'] ) {

					/* translators: %1$s: link to extensions page, %2$s: field name. */
					$args['message'] = sprintf( __( 'This feature requires the <a href="%1$s" target="_blank">%2$s</a> extension.', 'geo-my-wp' ), $args['extension_link'], $args['extension_name'] );

				} else {

					/* translators: %1$s: link to extensions page, %2$s: field name. */
					$args['message'] = sprintf( __( 'Visit the <a href="%1$s" target="_blank">%2$s</a> extension\'s page for additional %3$s options.', 'geo-my-wp' ), $args['extension_link'], $args['extension_name'], $args['field_name'] );
				}
			}
		}

		return wp_parse_args( $args, $defaults );
	}

	/**
	 * Get a single form field.
	 *
	 * @param  array  $field        field array.
	 *
	 * @param  string $name_attr    name attribute.
	 *
	 * @param  string $field_value  value.
	 *
	 * @return [type]            [description]
	 */
	public static function get_settings_field( $field = array(), $name_attr = '', $field_value = '' ) {

		$default_value = isset( $field['default'] ) ? $field['default'] : '';
		$field_name    = $field['name'];
		$id_attr       = ! empty( $field['id'] ) ? $field['id'] : 'gmw-form-field-' . $field_name;
		$field_type    = isset( $field['type'] ) ? $field['type'] : 'text';
		// phpcs:ignore.
		// $name_attr     = ! empty( $name_attr ) ? esc_attr( $name_attr . '[' . $field_name . ']' ) : $field_name;
		$attributes = array();
		$class_attr = ! empty( $field['class'] ) ? $field['class'] : '';
		$value      = '';

		if ( ! empty( $field['placeholder'] ) ) {

			$placeholder = 'placeholder="' . esc_attr( $field['placeholder'] ) . '"';

		} elseif ( in_array( $field_type, array( 'select', 'multiselect', 'multiselect_name_value', 'smartbox', 'smartbox_multiple' ), true ) ) {

			$placeholder = 'placeholder="' . esc_attr__( 'Select options...', 'geo-my-wp' ) . '"';
		} else {

			$placeholder = '';
		}

		if ( ! empty( $field_value ) ) {

			$value = $field_value;

		} elseif ( ! empty( $field['value'] ) ) {
			$value = $field['value'];
		}

		if ( ! empty( $name_attr ) ) {

			$name_attr = $name_attr . '[' . $field_name . ']';

		} elseif ( ! empty( $field['name_attr'] ) ) {

			$name_attr = $field['name_attr'] . '[' . $field_name . ']';
		} else {
			$name_attr = $field_name;
		}

		// attributes.
		if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
			foreach ( $field['attributes'] as $attribute_name => $attribute_value ) {

				if ( 'class' === $attribute_name ) {
					$class_attr .= ' ' . $attribute_value;
				} else {
					$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}
		}

		$output = '';

		switch ( $field_type ) {

			case '':
			case 'input':
			case 'text':
			default:
				$output .= '<input type="text" id="' . esc_attr( $id_attr ) . '" class="gmw-form-field regular-text text ' . esc_attr( $class_attr ) . '" name="' . esc_attr( $name_attr ) . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '" ' . implode( ' ', $attributes ) . ' ' . $placeholder . ' />';

				break;

			case 'checkbox':
				$toggle_class = '';

				if ( ! isset( $field['cb_toogle'] ) || ! empty( $field['cb_toogle'] ) ) {
					$toggle_class = ' class="gmw-checkbox-toggle-field" ';

					if ( ! empty( $field['toggle_label'] ) ) {
						$output .= '<span class="gmw-toggle-label gmw-settings-label">';
						$output .= isset( $field['cb_label'] ) ? esc_attr( $field['cb_label'] ) : '';
						$output .= '</span>';
					}
				}

				$output .= '<label' . $toggle_class . '>';
				$output .= '<input type="checkbox" id="' . esc_attr( $id_attr ) . '" class="gmw-form-field checkbox ' . esc_attr( $class_attr ) . '"';
				$output .= ' name="' . esc_attr( $name_attr ) . '" value="1"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' ' . checked( '1', $value, false ) . ' />';
				$output .= '<span class="gmw-checkbox-toggle"></span>';
				$output .= '<span class="gmw-checkbox-label">';
				$output .= isset( $field['cb_label'] ) ? esc_attr( $field['cb_label'] ) : '';
				$output .= '</span>';
				$output .= '</label>';

				break;

			case 'multicheckbox':
				$field['default'] = is_array( $field['default'] ) ? $field['default'] : array();
				$value            = ( ! empty( $value ) && is_array( $value ) ) ? $value : $field['default'];

				foreach ( $field['options'] as $key_val => $name ) {

					$key_val = esc_attr( $key_val );
					$value   = ! empty( $value[ $key_val ] ) ? $value[ $key_val ] : $default_value;
					$output .= '<label>';
					$output .= '<input type="checkbox" id="' . esc_attr( $id_attr ) . '-' . $key_val . '" class="gmw-form-field ' . esc_attr( $field_name ) . ' ' . $key_val . ' ' . esc_attr( $class_attr ) . ' checkbox multicheckbox" name="' . esc_attr( $name_attr ) . '[' . $key_val . ']" value="1" ' . checked( '1', $value ) . '/>';
					$output .= esc_html( $name );
					$output .= '</label>';

				}
				break;

			case 'multicheckboxvalues':
				$field['default'] = is_array( $field['default'] ) ? $field['default'] : array();

				if ( empty( $value ) ) {

					$value = $field['default'];

				} elseif ( ! is_array( $value ) ) {
					$value = explode( ',', $value );
				}

				// phpcs:ignore.
				// $value = ( ! empty( $value ) && is_array( $value ) ) ? $value : $option['default'];
				foreach ( $field['options'] as $key_val => $name ) {

					$key_val = esc_attr( $key_val );
					// phpcs:ignore.
					$checked = in_array( $key_val, $value ) ? 'checked="checked"' : ''; // loose comparison ok.

					$output .= '<label>';
					$output .= '<input type="checkbox" id="' . esc_attr( $id_attr ) . '-' . $key_val . '"';
					$output .= ' class="gmw-form-field ' . esc_attr( $field_name ) . ' ' . $key_val . ' ' . esc_attr( $class_attr ) . ' checkbox multicheckboxvalues"';
					$output .= ' name="' . esc_attr( $name_attr ) . '[]"';
					$output .= ' value="' . $key_val . '"';
					$output .= $checked;
					$output .= ' />';
					$output .= esc_html( $name );
					$output .= '</label>';

				}
				break;

			case 'textarea':
				$output .= '<textarea id="' . esc_attr( $id_attr ) . '"';
				$output .= ' class="gmw-form-field textarea large-text ' . esc_attr( $class_attr ) . '"';
				$output .= ' cols="50" rows="8" name="' . esc_attr( $name_attr ) . '"';
				$output .= implode( ' ', $attributes );
				$output .= ' ' . $placeholder . '>';
				$output .= esc_textarea( $value );
				$output .= '</textarea>';

				break;

			case 'radio':
				$rc = 1;

				foreach ( $field['options'] as $key_val => $name ) {

					$checked = ( 1 === $rc ) ? 'checked="checked"' : checked( $value, $key_val, false );
					$allwed  = array(
						'a'   => array(
							'href'  => array(),
							'title' => array(),
						),
						'img' => array(
							'src' => array(),
						),
					);

					$output .= '<label>';
					$output .= '<input type="radio" id="' . esc_attr( $id_attr ) . '"';
					$output .= ' class="gmw-form-field ' . esc_attr( $field_name ) . ' ' . $key_val . ' ' . esc_attr( $class_attr ) . ' radio"';
					$output .= ' name="' . esc_attr( $name_attr ) . '"';
					$output .= ' value="' . esc_attr( $key_val ) . '"';
					$output .= ' ' . $checked;
					$output .= ' />';
					$output .= wp_kses( $name, $allwed );
					$output .= '</label>';

					$rc++;
				}
				break;

			case 'select':
			case 'selectbox':
				if ( ! empty( $placeholder ) ) {
					$placeholder = 'data-' . $placeholder;
				}

				$output .= '<select id="' . esc_attr( $id_attr ) . '" class="gmw-form-field select ' . esc_attr( $class_attr ) . '" ' . $placeholder;
				$output .= ' name="' . esc_attr( $name_attr ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= '>';

				if ( 'country_code' === $field['name'] && ! empty( $value ) ) {
					$value = strtoupper( $value );
				}

				if ( 'language_code' === $field['name'] && ! empty( $key_val ) ) {
					$value = strtolower( $value );
				}

				foreach ( $field['options'] as $key_val => $name ) {
					$output .= '<option value="' . esc_attr( $key_val ) . '" ' . selected( $value, $key_val, false ) . '>' . esc_html( $name ) . '</option>';
				}

				$output .= '</select>';

				break;

			case 'multiselect':
			case 'multiselect_name_value':
				if ( ! empty( $placeholder ) ) {
					$placeholder = 'data-' . $placeholder;
				}

				$output .= '<select id="' . esc_attr( $id_attr ) . '" multiple ' . $placeholder;
				$output .= ' class="gmw-form-field multiselect regular-text ' . esc_attr( $class_attr ) . '"';
				$output .= ' name="' . esc_attr( $name_attr ) . '[]"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= '>';

				if ( ! empty( $value ) && ! is_array( $value ) ) {
					$value = explode( ',', $value );
				}

				foreach ( $field['options'] as $key_val => $name ) {
					// phpcs:ignore.
					$selected = ( is_array( $value ) && in_array( $key_val, $value ) ) ? 'selected="selected"' : ''; // loose compration OK.
					$output  .= '<option value="' . esc_attr( $key_val ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
				}

				$output .= '</select>';

				break;

			case 'password':
				$output .= '<input type="password" id="' . esc_attr( $id_attr ) . '"';
				$output .= ' class="gmw-form-field regular-text password ' . esc_attr( $class_attr ) . '" name="' . esc_attr( $name_attr ) . '"';
				$output .= ' value="' . esc_attr( sanitize_text_field( $value ) ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' ' . $placeholder;
				$output .= '/>';

				break;

			case 'hidden':
				$output .= '<input type="hidden" id="' . esc_attr( $id_attr ) . '"';
				$output .= ' class="gmw-form-field hidden ' . esc_attr( $class_attr ) . '" name="' . esc_attr( $name_attr ) . '"';
				$output .= ' value="' . esc_attr( sanitize_text_field( $value ) ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' />';

				break;

			// number.
			case 'number':
				$output .= '<input type="number" id="' . esc_attr( $id_attr ) . '"';
				$output .= ' class="gmw-form-field number ' . esc_attr( $class_attr ) . '"';
				$output .= ' name="' . esc_attr( $name_attr ) . '"';
				$output .= ' value="' . esc_attr( sanitize_text_field( $value ) ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' ' . $placeholder;
				$output .= ' />';

				break;

			case 'button':
				$output .= '<input type="button" id="' . esc_attr( $id_attr ) . '"';
				$output .= ' class="gmw-form-field button ' . esc_attr( $class_attr ) . '"';
				$output .= ! empty( $field['btn_label'] ) ? ' value="' . esc_attr( $field['btn_label'] ) . '"' : '';
				// $output .= ' name="' . esc_attr( $name_attr ) . '"';
				// $output .= ' value="' . esc_attr( sanitize_text_field( $value ) ) . '"';
				// $output .= ' value="' . esc_attr( sanitize_text_field( $value ) ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' />';

				break;
		}

		return $output;
	}
}
// phpcs:disable.
// add_action( 'wp_ajax_gmw_new_advanced_custom_field', array( 'GMW_Form_Settings_Helper', 'get_advanced_custom_field_ajax' ), 10 );
// add_action( 'wp_ajax_nopriv_gmw_new_advanced_custom_field', array( 'GMW_Form_Settings_Helper', 'get_advanced_custom_field_ajax' ), 10 );
// phpcs:enable.
