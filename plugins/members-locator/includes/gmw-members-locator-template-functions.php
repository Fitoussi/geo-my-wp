<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'gmw_search_results_bp_avatar' ) ) {
	/**
	 * Display featured image in search results
	 *
	 * @param  [type] $post [description]
	 * @param  array  $gmw  [description]
	 * @return [type]       [description]
	 */
	function gmw_search_results_bp_avatar( $object_type, $gmw = array() ) {

		if ( ! $gmw['search_results']['image']['enabled'] ) {
			return;
		}

		$object_type = ( 'bp_groups_locator' == $gmw['component'] ) ? 'group' : 'member';

		$permalink_function = 'bp_' . $object_type . '_permalink';
		$avatar_function    = 'bp_' . $object_type . '_avatar';
		?>
		<a class="image" href="<?php $permalink_function(); ?>" >
			<?php
			$avatar_function(
				array(
					'type'   => 'full',
					'width'  => $gmw['search_results']['image']['width'],
					'height' => $gmw['search_results']['image']['height'],
				)
			);
			?>
		</a>                                                              
		<?php
	}
}

/**
 * Search form BP member types filter
 *
 * @param  array $gmw [description]
 * @return [type]      [description]
 */
function gmw_search_form_bp_member_types( $gmw = array() ) {

	if ( empty( $gmw['search_form']['member_types_filter'] ) ) {
		return;
	}
	
	$settings = $gmw['search_form']['member_types_filter'];

	if ( ! isset( $settings['usage'] ) || $settings['usage'] == 'disabled' || $settings['usage'] == 'pre_defined' ) {
		return;
	}

	$url_px = gmw_get_url_prefix();

	// can be used with premium features to pass specific
	// member types via array
	if ( empty( $settings['member_types'] ) ) {

		$member_types = array();

		foreach ( bp_get_member_types( array(), 'object' ) as $type ) {
			$member_types[ $type->name ] = $type->labels['name'];
		}
	} else {

		$member_types = array_flip( $settings['member_types'] );
	}

	$args = array(
		'id'               => $gmw['ID'],
		'usage'            => isset( $settings['usage'] ) ? $settings['usage'] : 'disabled',
		'show_options_all' => isset( $settings['show_options_all'] ) ? $settings['show_options_all'] : __( 'Search member types', 'geo-my-wp' ),
	);

	$element = GMW_Search_Form_Helper::bp_member_types_filter( $args, $member_types );

	$output = '';

	if ( $args['usage'] != 'pre_defined' ) {

		$output .= '<div class="gmw-form-field-wrapper gmw-bp-member-types-wrapper gmw-bp-member-type-' . esc_attr( $args['usage'] ) . '">';

		if ( ! empty( $settings['label'] ) ) {

			$tag = ( $args['usage'] == 'checkboxes' ) ? 'span' : 'label';

			$output .= '<' . $tag . ' class="gmw-field-label">' . esc_attr( $settings['label'] ) . '</' . $tag . '>';
		}

		$output .= $element;
		$output .= '</div>';

	} else {
		$output .= $element;
	}

	echo $output;
}

/**
 * Search form BP Groups filter
 *
 * @param  array $gmw [description]
 * @return [type]      [description]
 */
function gmw_search_form_bp_groups_filter( $gmw = array() ) {

	if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'groups' ) ) {
		return;
	}

	// abort if no need to display the groups filter
	if ( ! isset( $gmw['search_form']['bp_groups']['usage'] ) || $gmw['search_form']['bp_groups']['usage'] == 'pre_defined' ) {
		return;
	}

	$settings = $gmw['search_form']['bp_groups'];

	// set args
	$args = array(
		'id'               => $gmw['ID'],
		'usage'            => isset( $settings['usage'] ) ? $settings['usage'] : 'dropdown',
		'show_options_all' => isset( $settings['show_options_all'] ) ? $settings['show_options_all'] : __( 'Search Groups', 'gmw-premium-settings' ),
	);

	// get the filter element
	$element = GMW_Search_Form_Helper::bp_groups_filter( $args, $settings['groups'] );

	$output = '';

	if ( $args['usage'] != 'pre_defined' ) {

		$output .= '<div class="gmw-form-field-wrapper gmw-bp-groups-wrapper gmw-bp-groups-' . esc_attr( $args['usage'] ) . '">';

		if ( ! empty( $settings['label'] ) ) {

			$tag = ( $args['usage'] == 'checkboxes' ) ? 'span' : 'label';

			$output .= '<' . $tag . ' class="gmw-field-label">' . esc_attr( $settings['label'] ) . '</' . $tag . '>';
		}

		$output .= $element;
		$output .= '</div>';

	} else {
		$output .= $element;
	}

	echo $output;
}

/**
 * Get buddyPress Xprofile Fields
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 */
function gmw_get_search_form_xprofile_fields( $gmw ) {

	// Look for profile fields in form settings
	$total_fields = ! empty( $gmw['search_form']['xprofile_fields']['fields'] ) ? $gmw['search_form']['xprofile_fields']['fields'] : array();

	// look for date profile field in form settings
	if ( ! empty( $gmw['search_form']['xprofile_fields']['date_field'] ) ) {
		array_unshift( $total_fields, $gmw['search_form']['xprofile_fields']['date_field'] );
	}

	// abort if no profile fields were chosen
	if ( empty( $total_fields ) ) {
		return;
	}

	$output  = '';
	$output .= '<div id="gmw-search-form-xprofile-fields-' . esc_attr( $gmw['ID'] ) . '" class="gmw-search-form-xprofile-fields gmw-fl-form-xprofile-fields">';

	$total_fields = apply_filters( 'gmw_fl_form_xprofile_field_before_displayed', $total_fields, $gmw );

	$values = isset( $gmw['form_values']['xf'] ) ? $gmw['form_values']['xf'] : array();

	foreach ( $total_fields as $field_id ) {

		$field_id    = absint( $field_id );
		$fid         = 'field_' . $field_id;
		$field_class = 'gmw-xprofile-field';
		$field_data  = new BP_XProfile_Field( $field_id );

		// field label can be modified
		$label = apply_filters( 'gmw_fl_xprofile_form_field_label', $field_data->name, $field_id, $field_data );
		$label = esc_html( $label );
		$value = '';

		// get the submitted value if form submitted
		if ( isset( $values[ $field_id ] ) ) {

			$value = $values[ $field_id ];

			// otherwise set default values
		} elseif ( empty( $gmw['submitted'] ) ) {

			$value = apply_filters( 'gmw_fl_xprofile_form_default_value', '', $field_id, $field_data );
		}

		if ( $value != '' ) {
			$value = is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( stripslashes( $value ) );
		}

		// field wrapper
		$output .= '<div class="gmw-form-field-wrapper gmw-xprofile-field-wrapper editfield field_type_' . esc_attr( $field_data->type ) . ' field_' . $field_id . ' field_' . sanitize_title( $field_data->name ) . '">';

		// display field
		switch ( $field_data->type ) {

			// date field
			case 'datebox':
			case 'birthdate':
				if ( ! is_array( $value ) ) {
					$value = array(
						'min' => '',
						'max' => '',
					);
				}

				$output .= '<label class="gmw-field-label" for="' . $fid . '">' . __( 'Age Range (min - max)', 'geo-my-wp' ) . '</label>';
				$output .= '<input type="number" name="xf[' . $field_id . '][min]" id="' . $fid . '_min" class="' . $field_class . ' range-min" value="' . $value['min'] . '" placeholder="' . __( 'Min', 'geo-my-wp' ) . '" />';
				$output .= '<input type="number" name="xf[' . $field_id . '][max]" id="' . $fid . '_max" class="' . $field_class . ' range-max" value="' . $value['max'] . '" placeholder="' . __( 'Max', 'geo-my-wp' ) . '" />';
				break;

			// textbox field
			case 'textbox':
				$output .= '<label class="gmw-field-label" for="' . $fid . '">' . $label . '</label>';
				$output .= '<input type="text" name="xf[' . $field_id . ']" id="' . $fid . '" class="' . $field_class . '" value="' . $value . '" placeholder=" ' . $label . '" />';
				break;

			// number field
			case 'number':
				$output .= '<label class="gmw-field-label" for="' . $fid . '">' . $label . '</label>';
				$output .= '<input type="number" name="xf[' . $field_id . ']" id="' . $fid . '" value="' . $value . '" placeholder=" ' . $label . '" />';
				break;

			// textarea
			case 'textarea':
				$output .= '<label class="gmw-field-label" for="' . $fid . '">' . $label . '</label>';
				$output .= '<textarea rows="5" cols="40" name="xf[' . $field_id . ']" id="' . $fid . '" class="' . $field_class . '">' . $value . '</textarea>';
				break;

			// selectbox
			case 'selectbox':
				$output .= '<label class="gmw-field-label" for="' . $fid . '">' . $label . '</label>';
				$output .= '<select name="xf[' . $field_id . ']" id="' . $fid . '" class="' . $field_class . '">';

				// all option
				$option_all = apply_filters( 'gmw_fl_xprofile_form_dropdown_option_all', __( ' -- All -- ', 'geo-my-wp' ), $field_id, $field_data );

				if ( ! empty( $option_all ) ) {
					$output .= '<option value="">' . esc_attr( $option_all ) . '</option>';
				}

				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {
					$option   = trim( $child->name );
					$selected = ( $option == $value ) ? "selected='selected'" : '';
					$output  .= '<option ' . $selected . ' value="' . $option . '">' . __( $option, 'geo-my-wp' ) . '</option>';
				}

				$output .= '</select>';

				break;

			// field belong to Buddypress Xprofile Custom Fields Type plugin
			case 'select_custom_post_type':
				$options = $field_data->get_children();

				// get the post type need to filter
				$post_type_selected = $options[0]->name;

				if ( $options ) {

					$post_type_selected = $options[0]->name;

					// Get posts of custom post type selected.
					$posts = new WP_Query(
						array(
							'posts_per_page' => -1,
							'post_type'      => $post_type_selected,
							'orderby'        => 'title',
							'order'          => 'ASC',
						)
					);

					$output .= '<label class="gmw-field-label" for="' . $fid . '">' . $label . '</label>';
					$output .= '<select name="xf[' . $field_id . ']" id="' . $fid . '" class="' . $field_class . '">';

					$option_all = apply_filters( 'gmw_fl_xprofile_form_dropdown_option_all', __( ' -- All -- ', 'geo-my-wp' ), $field_id, $field_data );

					if ( ! empty( $option_all ) ) {
						$output .= '<option value="">' . esc_attr( $option_all ) . '</option>';
					}

					if ( $posts ) {
						foreach ( $posts->posts as $post ) {
							$selected = ( $post->ID == $value ) ? "selected='selected'" : '';
							$output .= '<option ' . $selected . ' value="' . $post->ID . '">' . $post->post_title . '</option>';
						}
					}

					$output .= '</select>';
				}

				break;

			// multiselect box
			case 'multiselectbox':
			case 'multiselect_custom_post_type':
			case 'multiselect_custom_taxonomy':
				$output .= '<label class="gmw-field-label" for="' . $fid . '">' . $label . '</label>';
				$output .= '<select name="xf[' . $field_id . '][]" id="' . $fid . '" class="' . $field_class . '" multiple="multiple">';

				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {
					$option   = trim( $child->name );
					$selected = ( ! empty( $value ) && in_array( $option, $value ) ) ? "selected='selected'" : '';

					$output .= '<option ' . $selected . ' value="' . $option . '">' . __( $option, 'geo-my-wp' ) . '</option>';
				}

				$output .= '</select>';

				break;

			// radio buttons
			case 'radio':
				$output .= '<div class="radio">';
				$output .= '<span class="label gmw-field-label">' . $label . '</span>';

				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {
					$option  = trim( $child->name );
					$checked = ( $child->name == $value ) ? "checked='checked'" : '';

					$output .= '<label><input ' . $checked . ' type="radio" name="xf[' . $field_id . ']" value="' . $option . '" />' . __( $option, 'geo-my-wp' ) . '</label>';
				}

				$output .= '<a href="#" onclick="event.preventDefault();jQuery(this).closest(\'div\').find(\'input\').prop(\'checked\', false);">' . __( 'Clear', 'buddypress' ) . '</a><br/>';
				$output .= '</div>';

				break;

			// checkboxes
			case 'checkbox':
				$output .= '<div class="checkbox">';
				$output .= '<span class="label gmw-field-label">' . $label . '</span>';

				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {
					$option  = trim( $child->name );
					$checked = ( ! empty( $value ) && in_array( $option, $value ) ) ? "checked='checked'" : '';

					$output .= '<label><input ' . $checked . ' type="checkbox" name="xf[' . $field_id . '][]" value="' . $option . '" />' . $option . '</label>';
				}
				$output .= '</div>';

				break;

			/**
			 * Make multiselect_custom_taxonomy field type compatible with
			 * GEO my WP.
			 *
			 * Buddypress Xprofile Custom Fields Type plugin
			 *
			 * @author Miguel López <miguel@donmik.com>
			 */
			case 'multiselect_custom_taxonomy':
				$name_of_allow_new_tags = 'allow_new_tags';

				if ( class_exists( 'Bxcft_Field_Type_MultiSelectCustomTaxonomy' ) ) {
					$name_of_allow_new_tags = Bxcft_Field_Type_MultiSelectCustomTaxonomy::ALLOW_NEW_TAGS;
				}

				$options = $field_data->get_children();

				$taxonomy_selected = false;

				foreach ( $options as $option ) {

					if ( $name_of_allow_new_tags !== $option->name && taxonomy_exists( $option->name ) ) {

						$taxonomy_selected = $option->name;

						break;
					}
				}

				if ( $taxonomy_selected ) {

					$terms = get_terms(
						$taxonomy_selected,
						array( 'hide_empty' => false )
					);

					if ( $terms ) {

						$output .= '<label class="gmw-field-label" for="' . $fid . '">' . $label . '</label>';
						$output .= '<select name="xf[' . $field_id . '][]" id="' . $fid . '" class="' . $field_class . '" multiple="multiple">';

						foreach ( $terms as $term ) {

							$selected = ( ! empty( $value ) && in_array( $term->term_id, $value ) ) ? "selected='selected'" : '';
							$output  .= sprintf(
								'<option value="%s"%s>%s</option>',
								$term->term_id, $selected, $term->name
							);
						}

						$output .= '</select>';
					}
				}

				break;

			default:
				$output = apply_filters( 'gmw_fl_get_xprofile_fields', $output, $field_id, $field_data, $label, $field_class, $fid, $value );
				break;

		} // switch

		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}

function gmw_search_form_xprofile_fields( $gmw ) {
	echo gmw_get_search_form_xprofile_fields( $gmw );
}

/**
 * Query xprofile fields
 *
 * Note $formValues might come from URL. It needs to be sanitized before being used
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 *
 * @author Some of the code in this function was inspired by the code written by Andrea Taranti the creator of BP Profile Search - Thank you
 */
function gmw_query_xprofile_fields( $fields_values = array(), $gmw = array() ) {

	global $bp, $wpdb, $wp_version;

	$users_id = array();

	foreach ( $fields_values as $field_id => $value ) {

		if ( empty( $value ) || ( is_array( $value ) && ! array_filter( $value ) ) ) {
			continue;
		}

		// get the field data
		$field_data = new BP_XProfile_Field( $field_id );

		$sql = $wpdb->prepare( "SELECT `user_id` FROM {$bp->profile->table_name_data} WHERE `field_id` = %d ", $field_id );

		switch ( $field_data->type ) {

			case 'textbox':
			case 'textarea':
				$value = str_replace( '&', '&amp;', $value );

				if ( $wp_version < 4.0 ) {
					$escaped = '%' . esc_sql( like_escape( trim( $value ) ) ) . '%';
				} else {
					$escaped = '%' . $wpdb->esc_like( trim( $value ) ) . '%';
				}

				$sql .= $wpdb->prepare( 'AND value LIKE %s', $escaped );

				break;

			case 'number':
				$sql .= $wpdb->prepare( 'AND value = %d', $value );

				break;

			case 'selectbox':
			case 'radio':
				$value = str_replace( '&', '&amp;', $value );
				$sql  .= $wpdb->prepare( 'AND value = %s', $value );

				break;

			case 'multiselectbox':
			case 'checkbox':
				$values = $value;
				$like   = array();

				foreach ( $values as $value ) {
					$value = str_replace( '&', '&amp;', $value );
					if ( $wp_version < 4.0 ) {
						$escaped = '%' . esc_sql( like_escape( $value ) ) . '%';
					} else {
						$escaped = '%' . $wpdb->esc_like( $value ) . '%';
					}

					$like[] = $wpdb->prepare( 'value = %s OR value LIKE %s', $value, $escaped );
				}

				$sql .= 'AND (' . implode( ' OR ', $like ) . ')';

				break;

			case 'datebox':
			case 'birthdate':
				if ( ! is_array( $value ) || ! array_filter( $value ) ) {
					continue;
				}

				$min = ! empty( $value['min'] ) ? $value['min'] : '1';
				$max = ! empty( $value['max'] ) ? $value['max'] : '200';

				if ( $min > $max ) {
					$max = $min;
				}

				$time  = time();
				$day   = date( 'j', $time );
				$month = date( 'n', $time );
				$year  = date( 'Y', $time );
				$ymin  = $year - $max - 1;
				$ymax  = $year - $min;

				if ( $max !== '' ) {
					$sql .= $wpdb->prepare( ' AND DATE(value) > %s', "$ymin-$month-$day" );
				}
				if ( $min !== '' ) {
					$sql .= $wpdb->prepare( ' AND DATE(value) <= %s', "$ymax-$month-$day" );
				}

				break;
		}

		$results  = $wpdb->get_col( $sql, 0 );
		$users_id = empty( $users_id ) ? $results : array_intersect( $users_id, $results );

		// abort if no users found for this fields
		if ( empty( $users_id ) ) {
			return -1;
		}
	}

	return $users_id;
}

/**
 * GMW FL search results function - xprofile fields
 *
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_member_xprofile_fields( $member_id = 0, $fields = array() ) {

	if ( empty( $fields ) ) {
		return;
	}

	$output = '';

	foreach ( $fields as $field_id ) {

		$field_data  = new BP_XProfile_Field( $field_id );
		$field_value = xprofile_get_field_data( $field_id, $member_id );

		if ( empty( $field_value ) ) {
			continue;
		}

		if ( $field_data->type == 'datebox' ) {
			$age              = intval( date( 'Y', time() - strtotime( $field_value ) ) ) - 1970;
			$field_value      = sprintf( __( ' %s Years old', 'geo-my-wp' ), $age );
			$field_data->name = __( 'Age', 'geo-my-wp' );
		}

		$output .= '<li class="gmw-xprofile-field type-' . esc_attr( $field_data->type ) . ' id-' . esc_attr( $field_id ) . '">';
		$output .= '<span class="label">' . esc_html( $field_data->name ) . ':</span>';
		$output .= '<span class="field">';
		$output .= is_array( $field_value ) ? implode( ', ', $field_value ) : $field_value;
		$output .= '</span>';
		$output .= '</li>';
	}

	return $output == '' ? false : '<ul class="gmw-xprofile-fields">' . $output . '</ul>';
}
