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
	public static function get_pages( $args = array() ) {

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
	public static function get_post_types( $args = array() ) {

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

		$multiple_pt  = ! empty( $taxonomies_options['multiple_post_types'] ) ? ' multiple-post-types ' : '';
		$incexc_class = ! empty( $taxonomies_options['include_exclude_terms'] ) ? ' incexc-terms-enabled' : '';
		?>
		<div id="taxonomies-wrapper"
			class="gmw-setting-groups-container gmw-settings-group-draggable-area <?php echo $multiple_pt; // phpcs:ignore: XSS ok. ?><?php echo $incexc_class; // phpcs:ignore: XSS ok. ?>">

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
				<div id="<?php echo esc_attr( $taxonomy_name ); ?>_cat" class="taxonomy-wrapper gmw-settings-group-wrapper"
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
												echo '<option selected="selected" value="' . esc_attr( $tax_value ) . '">' . esc_html__( 'Click to load options', 'geo-my-wp' ) . '</option>';
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
												echo '<option selected="selected" value="' . esc_attr( $tax_value ) . '">' . esc_html_e( 'Click to load options', 'geo-my-wp' ) . '</option>';
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
										<?php echo esc_attr_e( 'Required', 'geo-my-wps' ); ?>
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
							__( 'Taxonomies are not available when selecting multiple post types.', 'geo-my-wp' ),
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
	 * Get users role.
	 *
	 * @since 4.0
	 *
	 * @param array $args argument.
	 *
	 * @return array of user roles.
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

		$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

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

		return $output;
	}

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
		$terms      = get_terms( $taxonomies, array( 'hide_empty' => false ) );

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

		$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

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
		if ( empty( $_POST['args']['gmw_ajax_load_options'] ) ) { // phpcs:ignore: CSRF ok, sanitization ok.

			echo wp_json_encode( array() );
		} else {

			echo wp_json_encode( self::get_field_options( $_POST['args'] ) ); // phpcs:ignore: CSRF ok, sanitization ok.
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
				'disabled'    => __( 'Disable', 'gmw-my-wp' ),
				'pre_defined' => __( 'Pre-defined', 'gmw-my-wp' ),
				'select'      => __( 'Select dropdown', 'gmw-my-wp' ),
				'multiselect' => __( 'Multi-Select box', 'gmw-my-wp' ),
				'checkboxes'  => __( 'Checkboxes', 'gmw-my-wp' ),
				'radio'       => __( 'Radio buttons', 'gmw-my-wp' ),
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
				'disabled' => __( 'Disable', 'gmw-my-wp' ),
				'include'  => __( 'Include', 'gmw-my-wp' ),
				'exclude'  => __( 'Exclude', 'gmw-my-wp' ),
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
				'address'      => __( 'Formatted address ( full address )', 'geo-my-wp' ),
				'street'       => __( 'Street', 'geo-my-wp' ),
				'premise'      => __( 'Apt/Suit ', 'geo-my-wp' ),
				'city'         => __( 'City', 'geo-my-wp' ),
				'region_name'  => __( 'State', 'geo-my-wp' ),
				'postcode'     => __( 'Postcode', 'geo-my-wp' ),
				'country_code' => __( 'Country', 'geo-my-wp' ),
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
				'markers_spiderfier' => __( 'Markers Spiderfier', 'geo-my-wp' ),
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
				$toggle_class = ( ! isset( $field['cb_toogle'] ) || ! empty( $field['cb_toogle'] ) ) ? ' class="gmw-checkbox-toggle-field" ' : '';

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
