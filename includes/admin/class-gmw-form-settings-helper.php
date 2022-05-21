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
	 * @param  [type] $string [description].
	 *
	 * @return boolean         [description]
	 */
	public static function is_json( $string ) {
		return is_string( $string ) && is_array( json_decode( $string, true ) ) && ( json_last_error() == JSON_ERROR_NONE ) ? true : false;
	}

	/**
	 * Get list of pages.
	 *
	 * @return [type] [description]
	 */
	public static function get_pages() {

		$pages = array();

		foreach ( get_pages() as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}

		return $pages;
	}

	/**
	 * Generate array of post types
	 *
	 * @return [type] [description]
	 */
	public static function get_post_types() {

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
	 * Taxonomies picker.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 */
	public static function taxonomies( $value, $name_attr, $form ) {

		if ( empty( $value ) ) {
			$value = array();
		}

		// get all taxonmoies.
		$taxonomies = get_taxonomies();
		?>
		<div id="taxonomies-core-wrapper" class="gmw-setting-groups-container">

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

				$tax_options = ! empty( $value[ $taxonomy_name ] ) ? $value[ $taxonomy_name ] : array();
				$defaults    = array(
					'style'      => 'disable',
					'post_types' => $post_types,
					'label'      => '',
					'required'   => '',
				);

				$tax_options = wp_parse_args( $tax_options, $defaults );
				$pt_string   = esc_attr( implode( ',', $post_types ) );
				?>
				<div id="<?php echo esc_attr( $taxonomy_name ); ?>_cat" class="taxonomy-wrapper gmw-settings-group-wrapper" data-post_types="<?php echo $pt_string; // WPCS: XSS ok. ?>">

					<div class="taxonomy-header gmw-settings-group-header">
						<i class="gmw-settings-group-options-toggle gmw-taxonomy-options-toggle gmw-icon-cog gmw-tooltip" aria-label="Click to manage options."></i>
						<span class="gmw-taxonomy-label"><strong><?php echo esc_attr( $taxonomy->labels->singular_name ); ?></strong> ( Post Types - <?php echo $pt_string; // WPCS: XSS ok. ?> )</span>
					</div>

					<?php $style = ! empty( $tax_options['style'] ) ? $tax_options['style'] : 'disabled'; ?>

					<div class="taxonomy-settings-table-wrapper taxonomy-settings gmw-settings-group-content gmw-settings-multiple-fields-wrapper" data-type="<?php echo esc_attr( $style ); ?>">

						<?php $tax_name_attr = esc_attr( $name_attr . '[' . $taxonomy_name . ']' ); ?>

						<?php foreach ( $post_types as $pt ) { ?>
							<input type="hidden" name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[post_types][]" value="<?php echo esc_attr( $pt ); ?>" />
						<?php } ?>

						<div class="gmw-settings-panel-field">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_html_e( 'Usage', 'geo-my-wps' ); ?></label>
							</div>

							<div class="taxonomy-usage taxonomy-tab-content gmw-settings-panel-input-container">					

								<select name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[style]" class="taxonomy-usage gmw-smartbox-not gmw-options-toggle">

									<option value="disable" selected="selected">
										<?php esc_attr_e( 'Disable', 'geo-my-wps' ); ?>
									</option>	

									<option value="dropdown" <?php selected( 'dropdown', $tax_options['style'], true ); ?>>
										<?php esc_attr_e( 'Dropdown', 'geo-my-wp' ); ?>
									</option>

								</select>
							</div>

							<div class="gmw-settings-panel-description">
								<?php esc_attr_e( 'Select the taxonomy usage.', 'geo-my-wps' ); ?>
							</div>
						</div>

						<div class="gmw-settings-panel-field">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Field Label', 'geo-my-wps' ); ?></label>
							</div>

							<div class="taxonomy-label taxonomy-tab-content gmw-settings-panel-input-container">					
								<input 
									type="text"
									class="gmw-form-field regular-text text setting-taxonomy-label"
									name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[label]"
									value="<?php echo ! empty( $tax_options['label'] ) ? esc_attr( $tax_options['label'] ) : ''; ?>"
								>
							</div>

							<div class="gmw-settings-panel-description">
								<?php esc_attr_e( 'Enter the field lable or leave blank to hide it.', 'geo-my-wps' ); ?>
							</div>
						</div>

						<div class="gmw-settings-panel-field">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Required', 'geo-my-wps' ); ?></label>
							</div>

							<div class="taxonomy-required taxonomy-tab-content gmw-settings-panel-input-container">					
								<input
									type="checkbox"
									class="gmw-form-field checkbox setting-taxonomy-required"
									name="<?php echo $tax_name_attr; // WPCS: XSS ok. ?>[required]"
									value="1"
									<?php checked( 1, $tax_options['required'], true ); ?>
								>
							</div>

							<div class="gmw-settings-panel-description"><?php esc_attr_e( 'Make this a required field.', 'geo-my-wp' ); ?></div>
						</div>
					</div>					
				</div>

			<?php } ?>
		</div>

		<?php
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
				<span><?php esc_html_e( 'Select at least one post type above in order to see and setup the taxonomies.', 'geo-my-wp' ); ?></span>
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
				<span><?php esc_html_e( 'No taxonomies were found for the selected post type.', 'geo-my-wp' ); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Get users role.
	 *
	 * @since 4.0
	 *
	 * @return [type] [description]
	 */
	public static function get_user_roles() {

		global $wp_roles;

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

		// $query_args = array();
		/*
		if ( ! empty( $args['gmw_ajax_load_options_query_args'] ) ) {
			$query_args = $args['gmw_ajax_load_options_query_args'];
		}

		if ( self::is_json( $args['gmw_ajax_load_options_query_args'] ) ) {
			$query_args = json_decode( $args['gmw_ajax_load_options_query_args'] );
		}*/

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
	 * @return [type] [description]
	 */
	public static function get_custom_fields_array() {

		global $wpdb;

		$keys = $wpdb->get_col(
			"SELECT meta_key
        	FROM $wpdb->postmeta
        	GROUP BY meta_key
        	ORDER BY meta_id DESC"
		); // WPCS: db call ok, cache ok.

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
	 * @return [type] [description]
	 */
	public static function get_user_meta() {

		global $wpdb;

		$keys = $wpdb->get_col(
			"SELECT meta_key
        	FROM $wpdb->usermeta
        	GROUP BY meta_key
        	ORDER BY umeta_id DESC"
		); // WPCS: db call ok, cache ok.

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
		); // WPCS: db call ok, cache ok.

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
	 * @return [type] [description]
	 */
	public static function get_all_taxonomy_terms() {

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
				'label' => $term->slug . ' ( ID ' . $term_id . ' )' . ' ( ' . $term->taxonomy . ' ) ',
			);
		}


		return $output;
	}
	  
	/**
	 * Get terms taxonomy array
	 *
	 * @param  string  $taxonomy    taxonomy name.
	 *
	 * @param  array   $values      values.
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

				$selected = ( ! empty( $values ) && in_array( $term->$field, $values ) ) ? 'selected="selected"' : '';
				$term_id  = esc_attr( $term->$field );
				$label    = esc_attr( $term->name );

				if ( IS_ADMIN ) {
					$label .= ' ( ID ' . $term_id . ' )';
				}

				echo '<option value="' . $term_id . '" ' . $selected . ' >' . $label . '</option>'; // WPCS: XSS ok.
			}
		} else {

			$current_tax = $terms[0]->taxonomy;

			usort( $terms, array( 'self', 'sort_taxonomy_terms_groups' ) );

			echo '<optgroup label="' . esc_attr( $current_tax ) . '">'; // WPCS: XSS ok.

			foreach ( $terms as $term ) {

				$selected = in_array( $term->term_taxonomy_id, $values ) ? 'selected="selected"' : '';

				if ( $term->taxonomy != $current_tax ) {

					echo '</optgroup>';
					$current_tax = $term->taxonomy;
					echo '<optgroup label="' . esc_attr( $term->taxonomy ) . '">';
				}

				$term_id = esc_attr( $term->term_taxonomy_id );
				$label   = esc_attr( $term->slug );

				if ( IS_ADMIN ) {
					$label .= ' ( ID ' . $term_id . ' )';
				}

				echo '<option value="' . $term_id . '" ' . $selected . ' >' . $label . '</option>'; // WPCS: XSS ok.
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
				'fields'     => array(),
				'date_field' => array(),
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
							$fields['fields'][ bp_get_the_profile_field_id() ] = bp_get_the_profile_field_name();
						}
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
				$member_types[ $type->name ] = $type->labels['name'];
			};
		}

		return $member_types;
	}

	/**
	 * Get group types.
	 *
	 * @param  [type] $args [description].
	 *
	 * @return [type]       [description]
	 */
	public static function get_bp_group_types( $args ) {

		$group_types = array();

		if ( function_exists( 'bp_groups_get_group_types' ) ) {

			foreach ( bp_groups_get_group_types( array(), 'object' ) as $type ) {
				$group_types[ $type->name ] = $type->labels['name'];
			};
		}

		return $group_types;
	}

	
	/**
	 * Get BP Groups.
	 *
	 * @param  [type] $args [description].
	 *
	 * @return [type]       [description]
	 */
	public static function get_bp_groups( $args ) {

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
	 * @return [type] [description]
	 */
	public static function get_bp_group_meta() {

		global $wpdb;

		$keys = $wpdb->get_col(
			"SELECT meta_key
        	FROM {$wpdb->prefix}bp_groups_groupmeta
        	GROUP BY meta_key
        	ORDER BY meta_key ASC"
		); // WPCS: db call ok, cache ok.

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

	public static function get_templates( $args ) {

		$args = array(
			'component'   => $args['gmw_ajax_load_component'],
			'addon'       => $args['gmw_ajax_load_addon'],
			'folder_name' => $args['gmw_ajax_load_type'],
		);

		$templates =  gmw_get_templates( $args );

		$new_templates        = array();
		$new_dep_templates    = array();
		$new_custom_templates = array();

		// Marked deprecated templates.
		foreach ( $templates as $value => $name ) {

			if ( strpos( $value, 'custom_' ) !== false ) {

				$new_custom_templates[ $value ] = $name;

			} else {

				if ( strpos( $value, 'buddyboss' ) !== false && ! function_exists( 'buddyboss_theme' ) ) {

					$new_templates[ $value ] = $name . ' ( requires the BuddyBoss theme )';

				} elseif ( 'search-forms' === $args['folder_name'] && in_array( $value, array( 'default', 'compact', 'horizontal', 'horizontal-gray', 'gray', 'purple', 'yellow', 'blue', 'red' ), true ) ) {

					$name .= ' ( deprecated )';

					$new_dep_templates[ $value ] = $name;

				} elseif ( 'search-results' === $args['folder_name'] && in_array( $value, array( 'clean', 'custom', 'default', 'grid-gray', 'grid', 'purple', 'gray', 'yellow', 'blue', 'red' ), true ) ) {

					$name .= ' ( deprecated )';

					$new_dep_templates[ $value ] = $name;

				} else {

					$new_templates[ $value ] = $name;

				}
			}
		}

		return array_merge( $new_templates, $new_dep_templates, $new_custom_templates );
	}

	/**
	 * Get field options via AJAX call.
	 *
	 * @since 4.0
	 */
	public static function get_field_options_ajax() {

		// ajax_load_options holds the function name. If missing, abort.
		if ( empty( $_POST['args']['gmw_ajax_load_options'] ) ) { // WPCS: CSRF ok, sanitization ok.

			echo wp_json_encode( array() );
		} else {

			echo wp_json_encode( self::get_field_options( $_POST['args'] ) ); // WPCS: CSRF ok, sanitization ok.
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
			$xprofile_field = ( ! empty( $args['gmw_ajax_load_options_xprofile'] ) && 'date_field' === $args['gmw_ajax_load_options_xprofile'] ) ? $xprofile_field['date_field'] : $xprofile_field['fields'];

			$output = $xprofile_field;
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

		if ( ! empty( $args['gmw_ajax_load_options_disabled'] ) ) {
			$output = array( 'disabled' => __( 'Disabled', 'geo-my-wp' ) ) + $output;
		}

		return $output;
	}

	/**
	 * Generate form field options.
	 *
	 * @param  array || string $args can be string of pre-defined option name or array of field args.
	 *
	 * @since 4.0
	 *
	 * @author Eyal Fitoussi
	 *
	 * @return [type]       [description]
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
			'sub_option'    => ( ! empty( $_GET['page'] ) && 'gmw-settings' === $_GET['page'] ) ? false : true, // On settings page, set it to false by default.
		);

		if ( 'label' === $option ) {

			$defaults['name']     = 'label';
			$defaults['label']    = __( 'Field Label', 'geo-my-wp' );
			$defaults['desc']     = __( 'Enter the field\'s label or leave it blank to hide it.', 'geo-my-wp' );
			$defaults['priority'] = 10;

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
				'disabled'          => __( 'Disable Filter', 'gmw-my-wp' ),
				'pre_defined'       => __( 'Pre-defined', 'gmw-my-wp' ),
				'dropdown'          => __( 'Select dropdown', 'gmw-my-wp' ),
				'checkboxes'        => __( 'Checkboxes', 'gmw-my-wp' ),
				'smartbox'          => __( 'Smartbox', 'gmw-my-wp' ),
				'smartbox_multiple' => __( 'Smartbox Multiple', 'gmw-my-wp' ),
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
				'disabled'          => __( 'Disable', 'gmw-my-wp' ),
				'include'           => __( 'Include', 'gmw-my-wp' ),
				'exclude'           => __( 'Exclude', 'gmw-my-wp' ),
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

			$defaults['name']       = 'map_width';
			$defaults['type']       = 'text';
			$defaults['label']      = __( 'Map Width', 'geo-my-wp' );
			$defaults['default']    = '100%';
			$defaults['desc']       = __( 'Enter the map width in pixels or percentage ( ex. 200px or 100% ).', 'geo-my-wp' );
			$defaults['priority']   = 30;

		} elseif ( 'map_height' === $option ) {

			$defaults['name']       = 'map_height';
			$defaults['type']       = 'text';
			$defaults['label']      = __( 'Map Height', 'geo-my-wp' );
			$defaults['default']    = '300px';
			$defaults['desc']       = __( 'Enter the map height in pixels or percentage ( ex. 200px or 100% ).', 'geo-my-wp' );
			$defaults['priority']   = 40;

		} elseif ( 'map_type' === $option ) {

			$defaults['name']       = 'map_type';
			$defaults['type']       = 'select';
			$defaults['label']      = __( 'Map Type', 'geo-my-wp' );
			$defaults['default']    = array();
			$defaults['desc']       = __( 'Select the map type.', 'geo-my-wp' );
			$defaults['priority']   = 50;
			$defaults['class']      = 'gmw-smartbox-not';
			$defaults['options']    = array(
				'ROADMAP'   => __( 'ROADMAP', 'geo-my-wp' ),
				'SATELLITE' => __( 'SATELLITE', 'geo-my-wp' ),
				'HYBRID'    => __( 'HYBRID', 'geo-my-wp' ),
				'TERRAIN'   => __( 'TERRAIN', 'geo-my-wp' ),
			);
		
		} elseif ( 'location_form_exclude_groups' === $option ) {

			$defaults['name']       = 'location_form_exclude_groups';
			$defaults['type']       = 'multiselect';
			$defaults['label']      = __( 'Exclude Form Field Groups', 'geo-my-wp' );
			$defaults['desc']       = __( 'Select the field groups that you wish to exclude from the location form.', 'geo-my-wp' );
			$defaults['default']    = array();
			$defaults['priority']   = 5;
			$defaults['options']    = array(
				'location'    => __( 'Location', 'geo-my-wp' ),
				'address'     => __( 'Address', 'geo-my-wp' ),
				'coordinates' => __( 'Coordinates', 'geo-my-wp' ),
			);

		} elseif ( 'location_form_exclude_fields' === $option ) {

			$defaults['name']       = 'location_form_exclude_fields';
			$defaults['type']       = 'multiselect';
			$defaults['label']      = __( 'Exclude Form Fields', 'geo-my-wp' );
			$defaults['desc']       = __( 'Select specific fields that you wish to exclude from the location form.', 'geo-my-wp' );
			$defaults['default']    = array();
			$defaults['priority']   = 10;
			$defaults['options']    = array(
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
			
			$defaults['name']       = 'location_form_template';
			$defaults['type']       = 'select';
			$defaults['label']      = __( 'Form Template', 'geo-my-wp' );
			$defaults['desc']       = __( 'Select the Location form template.', 'geo-my-wp' );
			$defaults['default']    = 'location-form-tabs-top';
			$defaults['priority']   = 15;
			$defaults['class']      = 'gmw-smartbox-not';
			$defaults['options']    = array(
				'location-form-tabs-top'  => __( 'Tabs Top ', 'geo-my-wp' ),
				'location-form-tabs-left' => __( 'Tabs Left', 'geo-my-wp' ),
				'location-form-no-tabs'   => __( 'No Tabs', 'geo-my-wp' ),
			);
		}
		 elseif ( 'marker_grouping' === $option ) {

		 	$defaults['name']       = 'grouping';
			$defaults['type']       = 'select';
			$defaults['label']      = __( 'Markers Grouping', 'geo-my-wp' );
			$defaults['desc']       = __( 'Group markers that are close together on the map.', 'geo-my-wp' );
			$defaults['default']    = 'standard';
			$defaults['priority']   = 10;
			$defaults['options']    = array(
				'standard'           => __( 'No Grouping', 'geo-my-wp' ),
				'markers_clusterer'  => __( 'Markers clusterer', 'geo-my-wp' ),
				'markers_spiderfier' => __( 'Markers Spiderfier', 'geo-my-wp' ),
			);
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
		// $name_attr     = ! empty( $name_attr ) ? esc_attr( $name_attr . '[' . $field_name . ']' ) : $field_name;
		$attributes = array();
		$class_attr = ! empty( $field['class'] ) ? $field['class'] : '';
		$value      = '';

		if ( ! empty( $field['placeholder'] ) ) {

			$placeholder = 'placeholder="' . esc_attr( $field['placeholder'] ) . '"';

		} elseif ( in_array( $field_type, array( 'select', 'multiselect', 'multiselect_name_value', 'smartbox', 'smartbox_multiple' ) ) ) {

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
				$output .= '<label>';
				$output .= '<input type="checkbox" id="' . esc_attr( $id_attr ) . '" class="gmw-form-field checkbox ' . esc_attr( $class_attr ) . '"';
				$output .= ' name="' . esc_attr( $name_attr ) . '" value="1"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' ' . checked( '1', $value, false ) . ' />';
				$output .= isset( $field['cb_label'] ) ? esc_attr( $field['cb_label'] ) : '';
				$output .= '</label>';

				break;

			case 'multicheckbox':
				$option['default'] = is_array( $option['default'] ) ? $option['default'] : array();
				$value             = ( ! empty( $value ) && is_array( $value ) ) ? $value : $option['default'];

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
				$option['default'] = is_array( $option['default'] ) ? $option['default'] : array();

				if ( empty( $value ) ) {

					$value = $option['default'];

				} elseif ( ! is_array( $value ) ) {
					$value = explode( ',', $value );
				}

				// $value = ( ! empty( $value ) && is_array( $value ) ) ? $value : $option['default'];
				foreach ( $field['options'] as $key_val => $name ) {

					$key_val = esc_attr( $key_val );
					$checked = in_array( $key_val, $value ) ? 'checked="checked"' : ''; // WPCS: loose comparison ok.

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
					$output .= '&nbsp;&nbsp;';

					$rc++;
				}
				break;

			case 'select':
				if ( ! empty( $placeholder ) ) {
					$placeholder = 'data-' . $placeholder;
				}

				$output .= '<select id="' . esc_attr( $id_attr ) . '" class="gmw-form-field select ' . esc_attr( $class_attr ) . '" ' . $placeholder;
				$output .= ' name="' . esc_attr( $name_attr ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= '>';

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
					$selected = ( is_array( $value ) && in_array( $key_val, $value ) ) ? 'selected="selected"' : '';
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
				$output .= ' />';

				break;
		}

		return $output;
	}

	/**
	 * Custom Fields Settings.
	 *
	 * @param  array $args   field args.
	 *
	 * @param  array $values field values.
	 */
	public static function get_custom_field( $args = array(), $values = array() ) {

		// Default args.
		$defaults = array(
			'option_name' => '',
			'name'        => '',
			'is_original' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		// Default value.
		$defaults = array(
			'name'               => '',
			'usage'              => 'text',
			'type'               => 'CHAR',
			'date_type'          => 'yyyy/mm/dd',
			'compare'            => '=',
			'label'              => '',
			'second_label'       => '',
			'placeholder'        => '',
			'second_placeholder' => '',
			'value'              => '',
			'second_value'       => '',
			'required'           => 0,
		);

		$field_values = wp_parse_args( $values, $defaults );

		// Field options.
		$options = array(
			'usage'              => array(
				'hidden'      => 'Pre defined',
				'text'        => 'Textbox',
				'number'      => 'Number',
				'date'        => 'Date',
				'time'        => 'Time',
				'datetime'    => 'Date and Time',
				'select'      => 'Select dropdown',
				'multiselect' => 'Multi-select box',
				'checkboxes'  => 'Checkboxes',
				'radio'       => 'Radio buttons',
			),
			'smartbox'           => 0,
			'options'            => '',
			'second_options'     => '',
			'type'               => array(
				'CHAR'     => 'CHAR',
				'NUMERIC'  => 'NUMERIC',
				'BINARY'   => 'BINARY',
				'DATE'     => 'DATE',
				'TIME'     => 'TIME',
				'DECIMAL'  => 'DECIMAL',
				'SIGNED'   => 'SIGNED',
				'UNSIGNED' => 'UNSIGNED',
			),
			'date_type'          => array(
				'yyyy/mm/dd' => 'yyyy/mm/dd',
				'mm/dd/yyyy' => 'mm/dd/yyyy',
				'dd/mm/yyyy' => 'dd/mm/yyyy',
			),
			/*
			'time_format'    => array(
				'yyyy/mm/dd' => 'yyyy/mm/dd',
				'mm/dd/yyyy' => 'mm/dd/yyyy',
				'dd/mm/yyyy' => 'dd/mm/yyyy',
			),*/
			'step_time'          => '15',
			'compare'            => array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' /*'EXISTS', 'NOT EXISTS'*/ ),
			'date_compare'       => array( '=', '!=', '>', '>=', '<', '<=', 'BETWEEN', 'NOT BETWEEN' /*'EXISTS', 'NOT EXISTS'*/ ),
			'value'              => '',
			'second_value'       => '',
			'label'              => '',
			'second_label'       => '',
			'placeholderl'       => '',
			'second_placeholder' => '',
			'required'           => 0,
		);

		$option_name = esc_attr( $args['option_name'] );
		$field_name  = esc_attr( $args['name'] );
		$is_original = '';
		$disabled    = '';

		if ( $args['is_original'] ) {
			$is_original = 'original-field';
			$disabled    = 'disabled="disabled"';
		}
		?>
		<div class="gmw-custom-field-wrapper gmw-settings-group-wrapper <?php echo $is_original; // WPCS: XSS ok. ?>" data-field_name="<?php echo ( ! $is_original ) ? $field_name : ''; // WPCS: XSS ok. ?>">		

			<div class="custom-field-header gmw-settings-group-header">

				<i class="gmw-settings-group-drag-handle gmw-custom-field-sort-handle gmw-icon-sort gmw-tooltip" aria-label="<?php esc_attr_e( 'Drag to sort fields.', 'geo-my-wp' ); ?>" title="<?php esc_attr_e( 'Sort fields.', 'geo-my-wp' ); ?>"></i>
				<i class="gmw-settings-group-options-toggle gmw-custom-field-options-toggle gmw-icon-cog gmw-tooltip" aria-label="<?php esc_attr_e( 'Click to manage options.', 'geo-my-wp' ); ?>"></i>

				<span class="custom-field-label">
					<input 
						type="text"
						name="<?php echo $option_name . '[' . $field_name . '][name]';  // WPCS: XSS ok. ?>"
						value="<?php echo esc_attr( $field_values['name'] ); ?>"
						readonly="readonly"
						<?php echo $disabled; // WPCS: XSS ok. ?>
					/>
				</span>

				<i class="gmw-settings-group-delete-trigger gmw-custom-field-delete gmw-icon-cancel-circled gmw-tooltip" aria-label="<?php esc_attr_e( 'Click to delete field.', 'geo-my-wp' ); ?>"></i>
			</div>

			<div class="custom-field-settings gmw-settings-multiple-fields-wrapper gmw-settings-group-content">

				<div class="gmw-settings-panel-field custom-field-usage-option-wrap">

					<div class="gmw-settings-panel-header">
						<label class="gmw-settings-label"><?php esc_attr_e( 'Field Usage', 'geo-my-wp' ); ?></label>
					</div>

					<div class="custom-field-option-usage gmw-settings-panel-input-container">

						<select 
							<?php echo $disabled; // WPCS: XSS ok. ?>
							class="custom-field-usage-selector gmw-smartbox-not" 
							name="<?php echo $option_name . '[' . $field_name . '][usage]'; // WPCS: XSS ok. ?>"
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

				<div class="gmw-settings-panel-field custom-field-smartbox-option-wrap" data-usage="select,multiselect">

					<div class="gmw-settings-panel-header">
						<label
							for="custom-field-smartbox-<?php echo $field_name; // WPCS: XSS ok. ?>"
							class="gmw-settings-label"><?php esc_html_e( 'Smart Select Field', 'geo-my-wp' ); ?></label>
					</div>

					<div class="custom-field-option-smartbox gmw-settings-panel-input-container">

						<?php
						if ( ! isset( $field_values['smartbox'] ) ) {
							$field_values['smartbox'] = 0;
						}
						?>

						<input
							id="custom-field-smartbox-<?php echo $field_name; // WPCS: XSS ok. ?>"
							type="checkbox"
							name="<?php echo $option_name . '[' . $field_name . '][smartbox]'; // WPCS: XSS ok. ?>"
							value="1"
							<?php echo $disabled; // WPCS: XSS ok. ?>
							<?php checked( $field_values['smartbox'], 1, true ); ?>
						/>

					</div>

					<div class="gmw-settings-panel-description">
						<?php esc_attr_e( 'Enable smart select field.', 'geo-my-wp' ); ?>
					</div>
				</div>

				<?php if ( isset( $options['type'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-type-option-wrap" data-usage="text">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Field Type', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-type gmw-settings-panel-input-container">

							<select 
								<?php echo $disabled; // WPCS: XSS ok. ?>
								class="custom-field-type-selector gmw-smartbox-not" 
								name="<?php echo $option_name . '[' . $field_name . '][type]'; // WPCS: XSS ok. ?>"
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

				<?php } ?>

				<?php if ( isset( $options['date_type'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-date-format-option-wrap" data-usage="text,date,datetime">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Date Format', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-date-format gmw-settings-panel-input-container">

							<select 
								<?php echo $disabled; // WPCS: XSS ok. ?>
								class="custom-field-date-format-selector gmw-smartbox-not" 
								name="<?php echo $option_name . '[' . $field_name . '][date_type]'; // WPCS: XSS ok. ?>">

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

				<?php } ?>

				<?php if ( isset( $options['step_time'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-time-step-option-wrap" data-usage="time">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Time Step', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-time-step gmw-settings-panel-input-container">

							<input 
								type="number" 
								name="<?php echo $option_name . '[' . $field_name . '][step_time]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['step_time'] ) ? esc_attr( stripcslashes( $field_values['step_time'] ) ) : ''; ?>"  
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the date format.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['compare'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-comparison-option-wrap" data-usage="hidden,text,number,select,radio">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Compare', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-comparison gmw-settings-panel-input-container">

							<select 
								<?php echo $disabled; // WPCS: XSS ok. ?>
								class="custom-field-comparison-selector gmw-smartbox-not" 
								name="<?php echo $option_name . '[' . $field_name . '][compare]'; // WPCS: XSS ok. ?>"
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

				<?php if ( isset( $options['date_compare'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-date-comparison-option-wrap" data-usage="date">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Compare', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-data-comparison gmw-settings-panel-input-container">

							<select 
								<?php echo $disabled; // WPCS: XSS ok. ?>
								class="custom-field-date-comparison-selector gmw-smartbox-not" 
								name="<?php echo $option_name . '[' . $field_name . '][date_compare]'; // WPCS: XSS ok. ?>"
							>
								<?php
								if ( ! isset( $field_values['date_compare'] ) ) {
									$field_values['date_compare'] = '=';
								}
								?>

								<?php foreach ( $options['date_compare'] as $option ) { ?>
									<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $option, $field_values['date_compare'], true ); ?>><?php echo esc_attr( $option ); ?></option>
								<?php } ?>	
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the comparison operator.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['options'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-options-option-wrap" data-usage="select,multiselect,checkboxes,radio">

						<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label
										for="custom-field-options-<?php echo $field_name; // WPCS: XSS ok. ?>"
										class="gmw-settings-label"><?php esc_attr_e( 'Field Options', 'geo-my-wp' ); ?></label>
								</div>

								<div class="custom-field-option-options gmw-settings-panel-input-container">

									<?php
									if ( empty( $field_values['options'] ) ) {
										$field_values['options'] = '';
									}
									?>

									<textarea
										id="custom-field-options-<?php echo $field_name; // WPCS: XSS ok. ?>"
										name="<?php echo $option_name . '[' . $field_name . '][options]'; // WPCS: XSS ok. ?>"
										rows="10"
										cols="50"
										<?php echo $disabled; // WPCS: XSS ok. ?>
									/><?php echo isset( $field_values['options'] ) ? esc_textarea( stripcslashes( $field_values['options'] ) ) : ''; ?></textarea>

								</div>
							</div>

							<div class="gmw-settings-panel-inner-option custom-field-second-option">

								<div class="gmw-settings-panel-header">
									<label
										for="custom-field-options-<?php echo $field_name; // WPCS: XSS ok. ?>"
										class="gmw-settings-label"><?php esc_attr_e( 'Second Field Options', 'geo-my-wp' ); ?></label>
								</div>

								<div class="custom-field-option-options gmw-settings-panel-input-container">

									<?php
									if ( empty( $field_values['second_options'] ) ) {
										$field_values['second_options'] = '';
									}
									?>

									<textarea
										id="custom-field-options-<?php echo $field_name; // WPCS: XSS ok. ?>"
										name="<?php echo $option_name . '[' . $field_name . '][second_options]'; // WPCS: XSS ok. ?>"
										rows="10"
										cols="50"
										<?php echo $disabled; // WPCS: XSS ok. ?>
									/><?php echo isset( $field_values['second_options'] ) ? esc_textarea( stripcslashes( $field_values['second_options'] ) ) : ''; ?></textarea>

								</div>
							</div>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Enable smart select field.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<div class="gmw-settings-panel-field custom-field-label-option-wrap" data-usage="text,number,select,date,time,datetime,multiselect,checkboxes,radio">

					<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

						<div class="gmw-settings-panel-inner-option">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Field Label', 'geo-my-wp' ); ?></label>
							</div>

							<input 
								type="text" 
								name="<?php echo $option_name . '[' . $field_name . '][label]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['label'] ) ? esc_attr( stripcslashes( $field_values['label'] ) ) : ''; ?>"  
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-inner-option custom-field-second-option">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Label', 'geo-my-wp' ); ?></label>
							</div>

							<input 
								type="text" 
								name="<?php echo $option_name . '[' . $field_name . '][second_label]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['second_label'] ) ? esc_attr( stripcslashes( $field_values['second_label'] ) ) : ''; ?>"
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>

					</div>

					<div class="gmw-settings-panel-description">
						<?php esc_attr_e( 'Enter the field lable or leave blank to hide it.', 'geo-my-wp' ); ?>
					</div>
				</div>

				<div class="gmw-settings-panel-field custom-field-placeholder-option-wrap" data-usage="text,number,date,time,datetime,select,multiselect">

					<div class="custom-field-option-placeholder gmw-settings-panel-input-container gmw-settings-double-options-holder">

						<div class="gmw-settings-panel-inner-option">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Field Placeholder', 'geo-my-wp' ); ?></label>
							</div>

							<input 
								type="text" 
								name="<?php echo $option_name . '[' . $field_name . '][placeholder]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['placeholder'] ) ? esc_attr( stripcslashes( $field_values['placeholder'] ) ) : ''; ?>"  
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-inner-option custom-field-second-option">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Placeholder', 'geo-my-wp' ); ?></label>
							</div>

							<input 
								type="text" 
								name="<?php echo $option_name . '[' . $field_name . '][second_placeholder]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['second_placeholder'] ) ? esc_attr( stripcslashes( $field_values['second_placeholder'] ) ) : ''; ?>"
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>
					</div>

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Enter the placeholder or leave blank to hide it.', 'geo-my-wp' ); ?>
					</div>
				</div>

				<?php if ( isset( $options['value'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-value-option-wrap" data-usage="">

						<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label"><?php esc_attr_e( 'Default Value', 'geo-my-wp' ); ?></label>
								</div>

								<div>
									<input 
										type="text" 
										name="<?php echo $option_name . '[' . $field_name . '][value]'; // WPCS: XSS ok. ?>" 
										value="<?php echo isset( $field_values['value'] ) ? esc_attr( stripcslashes( $field_values['value'] ) ) : ''; ?>"  
										<?php echo $disabled; // WPCS: XSS ok. ?>
									/>
								</div>
							</div>

							<div class="gmw-settings-panel-inner-option custom-field-second-option">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Value', 'geo-my-wp' ); ?></label>
								</div>

								<div>
									<input 
										type="text" 
										name="<?php echo $option_name . '[' . $field_name . '][second_value]'; // WPCS: XSS ok. ?>" 
										value="<?php echo isset( $field_values['second_value'] ) ? esc_attr( stripcslashes( $field_values['second_value'] ) ) : ''; ?>"  
										<?php echo $disabled; // WPCS: XSS ok. ?>
									/>
								</div>
							</div>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter a default value or leave blank.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['required'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-required-option-wrap" data-usage="text,number,select,date,time,datetime,multiselect,radio">

						<div class="gmw-settings-panel-header">

							<?php
							if ( ! isset( $field_values['required'] ) ) {
								$field_values['required'] = 0;
							}
							?>

							<input
								id="custom-field-required-?php echo $field_name; // WPCS: XSS ok. ?>"
								type="checkbox"
								name="<?php echo $option_name . '[' . $field_name . '][required]'; // WPCS: XSS ok. ?>"
								value="1"
								<?php echo $disabled; // WPCS: XSS ok. ?>
								<?php checked( $field_values['required'], 1, true ); ?>
							/>
							<label
								for="custom-field-required-<?php echo $field_name; // WPCS: XSS ok. ?>"
								class="gmw-settings-label"><?php esc_attr_e( 'Required', 'geo-my-wp' ); ?></label>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Make this field required.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

			</div>
		</div>
		<?php
	}

	/**
	 * Output custom fields generateor.
	 *
	 * @param  [type] $args  [description].
	 *
	 * @param  [type] $value [description].
	 *
	 * @param  [type] $form  [description].
	 */
	public static function get_custom_fields( $args, $value, $form ) {

		$defaults = array(
			'option_name'         => '',
			'get_fields_function' => 'gmw_get_custom_fields',
			'select_field_label'  => __( ' -- Select Field -- ', 'geo-my-wp' ),
			'add_field_label'     => __( 'Add Field', 'geo-my-wp' ),
		);

		$args = wp_parse_args( $args, $defaults );
		?>
		<div class="gmw-custom-fields-wrapper">

			<div id="gmw-custom-fields-new-field-picker">

				<span>
					<select id="gmw-custom-fields-picker" class="gmw-smartbox" data-gmw_ajax_load_options="<?php echo esc_attr( $args['get_fields_function'] ); ?>">
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

				self::get_custom_field( $args, $value );
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

						self::get_custom_field( $args, $values );
					}
				}
				?>
			</div>	
		</div>
		<?php
	}
}
