<?php
/**
 * Members Locator template functions.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Search form BP member types filter.
 *
 * Requires the Premium Settings extension.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_bp_member_types_field( $gmw = array() ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_bp_member_types_field' ) ) {
		return;
	}

	// remove action which was old way of adding the field to the form.
	remove_action( 'gmw_search_form_filters', 'gmw_ps_fl_enable_bp_object_types_search_form_filter', 20 );

	do_action( 'gmw_before_search_form_bp_member_types_field', $gmw );

	echo gmw_get_search_form_bp_member_types_field( $gmw ); // phpcs:ignore. already escaped.

	do_action( 'gmw_after_search_form_bp_member_types_field', $gmw );
}

/**
 * Search form BP Groups filter.
 *
 * Requires the Premium Settings extension.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_bp_groups_field( $gmw = array() ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_bp_groups_field' ) ) {
		return;
	}

	// remove action which was old way of adding the field to the form.
	remove_action( 'gmw_search_form_filters', 'gmw_ps_fl_enable_bp_object_types_search_form_filter', 20 );

	do_action( 'gmw_before_search_form_bp_groups_field', $gmw );

	echo gmw_get_search_form_bp_groups_field( $gmw ); // phpcs:ignore. already escaped.

	do_action( 'gmw_after_search_form_bp_groups_field', $gmw );
}

/**
 * Get buddyPress Xprofile Fields
 *
 * @version 1.0
 *
 * @param array $gmw gmw form.
 *
 * @author Eyal Fitoussi
 */
function gmw_get_search_form_xprofile_fields( $gmw ) {

	// Look for profile fields in form settings.
	$all_fields = ! empty( $gmw['search_form']['xprofile_fields']['fields'] ) ? $gmw['search_form']['xprofile_fields']['fields'] : array();

	// look for date profile field in form settings.
	if ( ! empty( $gmw['search_form']['xprofile_fields']['date_field'] ) ) {
		array_unshift( $all_fields, $gmw['search_form']['xprofile_fields']['date_field'] );
	}

	// abort if no profile fields were chosen.
	if ( empty( $all_fields ) ) {
		return;
	}

	$all_fields    = apply_filters( 'gmw_fl_form_xprofile_field_before_displayed', $all_fields, $gmw );
	$multiple_wrap = apply_filters( 'gmw_xf_multiple_fields_wrapper', false, $gmw );

	$output = '';

	if ( $multiple_wrap ) {
		$output .= '<div id="gmw-search-form-xprofile-fields-' . esc_attr( $gmw['ID'] ) . '" class="gmw-search-form-xprofile-fields gmw-fl-form-xprofile-fields gmw-search-form-multiple-fields-wrapper">';
	}

	$values = isset( $gmw['form_values']['xf'] ) ? $gmw['form_values']['xf'] : array();

	$custom_xf_plugin = class_exists( 'Bxcft_Plugin' ) ? 'old' : 'new';

	if ( class_exists( 'BP_Xprofile_CFTR' ) ) {
		$custom_xf_plugin = 'new';
	}

	foreach ( $all_fields as $field_id ) {

		$field_id   = absint( $field_id );
		$field_data = new BP_XProfile_Field( $field_id );

		// field label can be modified.
		$label = apply_filters( 'gmw_fl_xprofile_form_field_label', $field_data->name, $field_id, $field_data );
		$value = '';

		// get the submitted value if form submitted.
		if ( isset( $values[ $field_id ] ) ) {

			$value = $values[ $field_id ];

			// otherwise set default values.
		} elseif ( empty( $gmw['submitted'] ) ) {

			$value = apply_filters( 'gmw_fl_xprofile_form_default_value', '', $field_id, $field_data );
		}

		// Sanitize values.
		if ( '' !== $value ) {
			$value = is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( stripslashes( $value ) );
		}

		$field_name = sanitize_title( $field_data->name );
		$field_args = array(
			'id'            => $gmw['ID'],
			'type'          => 'text',
			'name'          => 'xf',
			'sub_name'      => $field_id,
			'is_array'      => false,
			'slug'          => $field_name,
			'wrapper_class' => 'gmw-xprofile-field-wrapper editfield field_type_' . esc_attr( $field_data->type ) . ' field_' . $field_id . ' field_' . $field_name,
			'class'         => 'gmw-xprofile-field',
			'label'         => $label,
			'placeholder'   => $label,
			'value'         => $value,
		);

		// For fields with options all.
		$option_all = apply_filters( 'gmw_fl_xprofile_form_dropdown_option_all', __( ' -- All -- ', 'geo-my-wp' ), $field_id, $field_data );

		$fields       = array();
		$wrapper_args = array();

		// display field.
		switch ( $field_data->type ) {

			// date field.
			case 'datebox':
			case 'birthdate':
				if ( ! is_array( $value ) ) {
					$value = array(
						'min' => '',
						'max' => '',
					);
				}

				$wrapper_args = array(
					'id'    => $gmw['ID'],
					'slug'  => $field_name,
					'class' => $field_args['class'],
					'label' => __( 'Age Range', 'geo-my-wp' ),
				);

				$field_args['type']        = 'number';
				$field_args['class']       = 'range-min';
				$field_args['placeholder'] = 'Min';
				$field_args['label']       = '';
				$field_args['array_key']   = 'min';

				$fields[] = $field_args;

				$field_args['type']        = 'number';
				$field_args['class']       = 'range-max';
				$field_args['placeholder'] = 'Max';
				$field_args['label']       = '';
				$field_args['array_key']   = 'max';

				$fields[] = $field_args;

				break;

			// textbox field.
			case 'textbox':
				$field_args['type'] = 'text';
				$fields[]           = $field_args;

				break;

			// number field.
			case 'number':
				$field_args['type'] = 'number';
				$fields[]           = $field_args;

				break;

			// textarea.
			case 'textarea':
				$field_args['type'] = 'textarea';
				$fields[]           = $field_args;

				break;

			// selectbox.
			case 'selectbox':
			case 'multiselectbox':
				// get options.
				$children = $field_data->get_children();
				$options  = array();

				foreach ( $children as $child ) {
					$option             = trim( $child->name );
					$options[ $option ] = $option;
				}

				if ( 'selectbox' === $field_data->type ) {

					$field_args['type'] = 'select';
					$field_args['is_array'];
				} else {

					$field_args['type']     = 'multiselect';
					$field_args['is_array'] = true;
				}

				$field_args['options']          = $options;
				$field_args['show_options_all'] = $option_all;

				$fields[] = $field_args;

				break;

			// radio buttons.
			case 'radio':
				// get options.
				$children = $field_data->get_children();
				$options  = array();

				foreach ( $children as $child ) {
					$option             = trim( $child->name );
					$options[ $option ] = $option;
				}

				$field_args['type']             = 'radio';
				$field_args['show_reset_field'] = true;
				$field_args['options']          = $options;

				$fields[] = $field_args;

				break;

			// checkboxes.
			case 'checkbox':
				// get options.
				$children = $field_data->get_children();
				$options  = array();

				foreach ( $children as $child ) {

					$option             = trim( $child->name );
					$options[ $option ] = $option;
				}

				$field_args['type']     = 'checkboxes';
				$field_args['options']  = $options;
				$field_args['is_array'] = true;

				$fields[] = $field_args;

				break;

			/**
			 * Make taxnommt and post types field typea compatible with
			 * GEO my WP.
			 *
			 * Buddypress Xprofile Custom Fields Type plugin
			 *
			 * @author Miguel López <miguel@donmik.com>
			 */
			case 'select_custom_taxonomy':
			case 'multiselect_custom_taxonomy':
				$taxonomy_selected = false;
				$options           = array();

				if ( 'old' === $custom_xf_plugin ) {

					$name_of_allow_new_tags = 'allow_new_tags';

					if ( class_exists( 'Bxcft_Field_Type_MultiSelectCustomTaxonomy' ) ) {
						$name_of_allow_new_tags = Bxcft_Field_Type_MultiSelectCustomTaxonomy::ALLOW_NEW_TAGS;
					}

					$options = $field_data->get_children();

					foreach ( $options as $option ) {

						if ( $name_of_allow_new_tags !== $option->name && taxonomy_exists( $option->name ) ) {

							$taxonomy_selected = $option->name;

							break;
						}
					}
				} else {
					$taxonomy_selected = bp_xprofile_get_meta( $field_id, 'field', 'selected_taxonomy', true );
				}

				if ( ! empty( $taxonomy_selected ) ) {

					$terms = get_terms(
						$taxonomy_selected,
					);

					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

						foreach ( $terms as $term ) {
							$options[ $term->term_id ] = $term->name;
						}
					}
				}

				if ( 'select_custom_taxonomy' === $field_data->type ) {

					$field_args['type'] = 'select';
				} else {

					$field_args['type']     = 'multiselect';
					$field_args['is_array'] = true;
				}

				$field_args['options']          = $options;
				$field_args['show_options_all'] = $option_all;

				$fields[] = $field_args;

				break;

			// Fields belong to Buddypress Xprofile Custom Fields Type plugin.
			case 'select_custom_post_type':
			case 'multiselect_custom_post_type':
				$post_type_selected = false;
				$options            = array();

				if ( 'old' === $custom_xf_plugin ) {

					$options = $field_data->get_children();

					// get the post type need to filter.
					$post_type_selected = $options[0]->name;

				} else {
					$post_type_selected = bp_xprofile_get_meta( $field_id, 'field', 'selected_post_type', true );
				}

				if ( ! empty( $post_type_selected ) ) {

					// Get the posts of the selected custom post type.
					$posts = new WP_Query(
						array(
							'posts_per_page' => -1,
							'post_type'      => $post_type_selected,
							'orderby'        => 'title',
							'order'          => 'ASC',
						)
					);

					if ( ! empty( $posts ) ) {

						foreach ( $posts->posts as $post ) {
							$options[ $post->ID ] = $post->post_title;
						}
					}
				}

				if ( 'select_custom_post_type' === $field_data->type ) {

					$field_args['type'] = 'select';
				} else {

					$field_args['type']     = 'multiselect';
					$field_args['is_array'] = true;
				}

				$field_args['options']          = $options;
				$field_args['show_options_all'] = $option_all;

				$fields[] = $field_args;

				break;

			default:
				// Deprecated.
				$output = apply_filters( 'gmw_fl_get_xprofile_fields', $output, $field_id, $field_data, $label, $field_args['class'], $field_id, $value );

				// New filter.
				$output = apply_filters( 'gmw_fl_get_xprofile_fields_default', $output, $field_id, $field_args, $field_data );

				break;
		}

		$output .= 1 === count( $fields ) ? gmw_get_form_field( $fields[0], $gmw ) : GMW_Search_Form_Helper::get_complex_field( $wrapper_args, $fields, $gmw );
	}

	if ( $multiple_wrap ) {
		$output .= '</div>';
	}

	return $output;
}

/**
 * Display xprofile fields filters in search form.
 *
 * @param  array $gmw gmw forms.
 */
function gmw_search_form_xprofile_fields( $gmw ) {

	do_action( 'gmw_before_search_form_xprofile_fields', $gmw );

	echo gmw_get_search_form_xprofile_fields( $gmw ); /// phpcs:ignore. already escaped.

	do_action( 'gmw_after_search_form_xprofile_fields', $gmw );
}

/**
 * Display add friend button in search results.
 *
 * @param  object $member  member object.
 *
 * @param  array  $gmw     gmw form.
 *
 * @param  string $where   where the output is ( info_window, search_results ).
 *
 * @return [type]         [description]
 */
function gmw_search_results_bp_friendship_button( $member, $gmw, $where = 'search_results' ) {

	if ( ! function_exists( 'bp_add_friend_button' ) || empty( $gmw[ $where ]['friendship_button'] ) ) {
		return;
	}

	bp_add_friend_button( $member->id );
}

/**
 * Display member types in search results.
 *
 * @param  object $member  member object.
 *
 * @param  array  $gmw     gmw form.
 *
 * @param  string $where   where the output is ( info_window, search_results ).
 *
 * @since 4.0
 *
 * @return [type]         [description]
 */
function gmw_search_results_member_types( $member, $gmw, $where = 'search_results' ) {

	// Require the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_results_member_types' ) || empty( $gmw[ $where ]['member_types']['enabled'] ) ) {
		return;
	}

	echo gmw_get_search_results_member_types( $member, $gmw, $where ); // phpcs:ignore.
}

/**
 * Display last activity.
 *
 * @param  object $member  member object.
 *
 * @param  array  $gmw     gmw form.
 *
 * @param  string $where   where the output is ( info_window, search_results ).
 *
 * @return [type]         [description]
 */
function gmw_search_results_bp_last_active( $member, $gmw, $where = 'search_results' ) {

	if ( ! function_exists( 'bp_get_member_last_active' ) || empty( $gmw[ $where ]['last_active'] ) ) {
		return;
	}

	echo '<span class="activity">' . bp_get_member_last_active() . '</span>'; // phpcs:ignore.
}

/**
 * Generate the xprofile query args to pass to BuddyPress bp_has_members() function.
 *
 * @since 4.0.
 *
 * @param  array $gmw           [description].
 *
 * @param  array $fields_values [description].
 *
 * @return [type]                [description]
 */
function gmw_get_xprofile_query_args( $gmw = array(), $fields_values = array() ) {

	// look for xprofile values in URL.
	if ( empty( $fields_values ) ) {

		if ( isset( $gmw['form_values']['xf'] ) && array_filter( $gmw['form_values']['xf'] ) ) {

			$fields_values = $gmw['form_values']['xf'];

			// otherwise, can do something custom with xprofile fields
			// by passing array of array( fields => value ).
		} else {
			$fields_values = apply_filters( 'gmw_' . $gmw['prefix'] . '_xprofile_fields_query_default_values', array(), $gmw );
		}
	}

	if ( empty( $fields_values ) ) {
		return array();
	}

	$meta_args = array();

	foreach ( $fields_values as $field_id => $value ) {

		if ( empty( $value ) || ( is_array( $value ) && ! array_filter( $value ) ) ) {
			continue;
		}

		// get the field data.
		$field_data = new BP_XProfile_Field( $field_id );

		switch ( $field_data->type ) {

			case 'textbox':
			case 'textarea':
				$this_query = array(
					'field'   => $field_id,
					'value'   => $value,
					'compare' => 'LIKE',
				);

				break;

			case 'number':
				$this_query = array(
					'field'   => $field_id,
					'value'   => $value,
					'compare' => '=',
					'type'    => 'NUMERIC',
				);

				break;

			case 'selectbox':
			case 'radio':
			case 'select_custom_post_type':
			case 'select_custom_taxonomy':
				$this_query = array(
					'field'   => $field_id,
					'value'   => $value,
					'compare' => '=',
				);

				break;

			case 'multiselectbox':
			case 'checkbox':
			case 'multiselect_custom_post_type':
			case 'multiselect_custom_taxonomy':
				$this_query = array( 'relation' => 'OR' );

				foreach ( $value as $item ) {

					$this_query[] = array(
						'field'   => $field_id,
						'value'   => sprintf( ':"%s";', $item ),
						'compare' => 'LIKE',
					);
				}

				break;

			case 'datebox':
			case 'birthdate':
				if ( ! is_array( $value ) || ! array_filter( $value ) ) {
					break;
				}

				$min = ! empty( $value['min'] ) ? $value['min'] : '1';
				$max = ! empty( $value['max'] ) ? $value['max'] : '200';

				if ( $min > $max ) {
					$max = $min;
				}

				$time  = time();
				$day   = gmdate( 'j', $time );
				$month = gmdate( 'n', $time );
				$year  = gmdate( 'Y', $time );
				$ymin  = $year - $max - 1;
				$ymax  = $year - $min;

				$this_query = array( 'relation' => 'AND' );

				$this_query[] = array(
					'field'   => $field_id,
					'value'   => "$ymin-$month-$day",
					'compare' => '>=',
				);

				$this_query[] = array(
					'field'   => $field_id,
					'value'   => "$ymax-$month-$day",
					'compare' => '<=',
				);

				break;
		}

		// create the meta query args.
		$meta_args[] = apply_filters(
			'gmw_fl_xprofile_query_field_args',
			$this_query,
			$gmw,
			$field_id,
			$field_data,
			$fields_values,
		);
	}

	if ( ! empty( $meta_args ) ) {
		$meta_args['relation'] = 'AND';
	}

	return $meta_args;
}

/**
 * Query xprofile fields
 *
 * Note $form_values might come from URL. It needs to be sanitized before being used
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 *
 * @param array $fields_values form values.
 *
 * @param array $gmw gmw form.
 *
 * @author Some of the code in this function was inspired by the code written by Andrea Taranti the creator of BP Profile Search - Thank you
 */
function gmw_query_xprofile_fields( $fields_values = array(), $gmw = array() ) {

	global $bp, $wpdb;

	$users_id = array();

	foreach ( $fields_values as $field_id => $value ) {

		if ( empty( $value ) || ( is_array( $value ) && ! array_filter( $value ) ) ) {
			continue;
		}

		// get the field data.
		$field_data = new BP_XProfile_Field( $field_id );

		$sql = $wpdb->prepare(
			'
			SELECT `user_id`
			FROM %s
			WHERE `field_id` = %d ',
			$bp->profile->table_name_data,
			$field_id,
		); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		switch ( $field_data->type ) {

			case 'textbox':
			case 'textarea':
				$value   = str_replace( '&', '&amp;', $value );
				$escaped = '%' . $wpdb->esc_like( trim( $value ) ) . '%';

				$sql .= $wpdb->prepare( 'AND value LIKE %s', $escaped );

				break;

			case 'number':
				$sql .= $wpdb->prepare( 'AND value = %d', $value );

				break;

			case 'selectbox':
			case 'radio':
			case 'select_custom_post_type':
			case 'select_custom_taxonomy':
				$value = str_replace( '&', '&amp;', $value );
				$sql  .= $wpdb->prepare( 'AND value = %s', $value );

				break;

			case 'multiselectbox':
			case 'checkbox':
			case 'multiselect_custom_post_type':
			case 'multiselect_custom_taxonomy':
				$values = $value;
				$like   = array();

				foreach ( $values as $value ) {
					$value   = str_replace( '&', '&amp;', $value );
					$escaped = '%' . $wpdb->esc_like( $value ) . '%';
					$like[]  = $wpdb->prepare( 'value = %s OR value LIKE %s', $value, $escaped );
				}

				$sql .= 'AND (' . implode( ' OR ', $like ) . ')';

				break;

			case 'datebox':
			case 'birthdate':

				df();
				if ( ! is_array( $value ) || ! array_filter( $value ) ) {
					break;
				}

				$min = ! empty( $value['min'] ) ? $value['min'] : '1';
				$max = ! empty( $value['max'] ) ? $value['max'] : '200';

				if ( $min > $max ) {
					$max = $min;
				}

				$time  = time();
				$day   = gmdate( 'j', $time );
				$month = gmdate( 'n', $time );
				$year  = gmdate( 'Y', $time );
				$ymin  = $year - $max - 1;
				$ymax  = $year - $min;

				if ( '' !== $max ) {
					$sql .= $wpdb->prepare( ' AND DATE(value) > %s', "$ymin-$month-$day" );
				}
				if ( '' !== $min ) {
					$sql .= $wpdb->prepare( ' AND DATE(value) <= %s', "$ymax-$month-$day" );
				}

				break;
		}

		$results  = $wpdb->get_col( $sql, 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$users_id = empty( $users_id ) ? $results : array_intersect( $users_id, $results );

		// abort if no users found for this fields.
		if ( empty( $users_id ) ) {
			return -1;
		}
	}

	return $users_id;
}

/**
 * Display Xprofile Fields in search results.
 *
 * @version 1.0
 *
 * @param integer $member_id member ID.
 *
 * @param array   $fields    array of xfields to display.
 *
 * @author Eyal Fitoussi
 */
function gmw_get_member_xprofile_fields( $member_id = 0, $fields = array() ) {

	if ( empty( $fields ) ) {
		return;
	}

	$output = '';

	foreach ( $fields as $field_id ) {

		$field_id    = absint( $field_id );
		$field_data  = new BP_XProfile_Field( $field_id );
		$field_value = xprofile_get_field_data( $field_id, $member_id );

		// verify field.
		if ( empty( $field_data->id ) || empty( $field_value ) ) {
			continue;
		}

		if ( 'datebox' === $field_data->type ) {

			$age = intval( gmdate( 'Y', time() - strtotime( $field_value ) ) ) - 1970;
			// translators: %s for age.
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

	return '' === $output ? false : '<ul class="gmw-xprofile-fields">' . $output . '</ul>';
}

/**
 * Display xprofile fields in search results.
 *
 * @param  object $member the member object.
 *
 * @param  array  $gmw    gmw form.
 *
 * @param  string $where  where the output is ( info_window, search_results ).
 *
 * @since 4.0 ( moved from Premium Settings ).
 */
function gmw_search_results_member_xprofile_fields( $member, $gmw = array(), $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['xprofile_fields'] ) ) {
		return;
	}

	echo gmw_get_search_results_meta_fields( 'xprofile_field', $gmw[ $where ]['xprofile_fields'], $member, $gmw ); // phpcs:ignore. WPCS: XSS OK.
}

// phpcs:disable.
/*function gmw_search_results_member_xprofile_fields( $member, $gmw = array(), $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['xprofile_fields'] ) ) {
		return;
	}

	// Look for profile fields in form settings.
	$total_fields = ! empty( $gmw[ $where ]['xprofile_fields']['fields'] ) ? $gmw[ $where ]['xprofile_fields']['fields'] : array();

	// look for date profile field in form settings.
	if ( ! empty( $gmw[ $where ]['xprofile_fields']['date_field'] ) ) {
		array_unshift( $total_fields, $gmw[ $where ]['xprofile_fields']['date_field'] );
	}

	// abort if no profile fields were chosen.
	if ( empty( $total_fields ) ) {
		return;
	}

	echo gmw_get_member_xprofile_fields( $member->id, $total_fields ); // WPCS: XSS ok.
}*/
// phpcs:enable.
