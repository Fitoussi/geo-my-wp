<?php
/**
 * GMW Search Results Template functions.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get address fields in search results
 *
 * @since 4.0
 *
 * @param  object  $object location object.
 *
 * @param  array   $fields address fields to output.
 *
 * @param  boolean $linked link address to google map.
 *
 * @param  array   $gmw    gmw form.
 */
function gmw_get_search_results_address( $object, $fields = array(), $linked = false, $gmw = array() ) {
	return $linked ? gmw_get_linked_location_address( $object, $fields, $gmw ) : gmw_get_location_address( $object, $fields, $gmw );
}

/**
 * Display address fields in search results.
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 *
 * @param  string $where  search_results || info_window.
 */
function gmw_search_results_address( $object, $gmw = array(), $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['address']['enabled'] ) ) {
		return;
	}

	if ( empty( $gmw[ $where ]['address']['fields'][0] ) || 'address' === $gmw[ $where ]['address']['fields'][0] ) {
		$fields = array( 'address' );
	} else {
		$fields = $gmw[ $where ]['address']['fields'];
	}

	if ( ! isset( $gmw[ $where ]['address']['linked'] ) ) {
		$gmw[ $where ]['address']['linked'] = true;
	}

	$output = gmw_get_search_results_address( $object, $fields, $gmw[ $where ]['address']['linked'], $gmw );

	if ( ! empty( $output ) ) {
		echo '<div class="gmw-item gmw-item-address"><i class="gmw-icon-location-thin"></i>' . $output . '</div>'; // WPCS: XSS ok.
	}
}

/**
 * Display address that links to a new page with Google Map.
 *
 * DEPRECATED since 4.0. Use gmw_search_results_address() instead.
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_linked_address( $object, $gmw = array() ) {
	gmw_search_results_address( $object, $gmw );
}

/**
 * Get the distance to location
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 *
 * @param  string $where  search_results || info_window.
 */
function gmw_search_results_distance( $object = array(), $gmw = array(), $where = 'search_results', $html_wrap = true ) {

	if ( empty( $object->distance ) || empty( $gmw[ $where ]['distance'] ) ) {
		return;
	}

	$distance = gmw_get_distance_to_location( $object );

	if ( $distance ) {

		if ( $html_wrap ) {
			$distance = '<span class="gmw-item distance">' . $distance . '</span>'; // WPCS: XSS ok.
		}

		echo $distance; // WPCS: XSS ok.
	}
}

/**
 * Display list of location meta in search results
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 *
 * @param  string $label  label before meta value.
 *
 * @param  string $where  search_results || info_window.
 */
function gmw_search_results_location_meta( $object, $gmw = array(), $label = true, $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['location_meta'] ) ) {
		return;
	}

	$data = gmw_get_location_meta_list( $object, $gmw[ $where ]['location_meta'] );

	if ( empty( $data ) ) {
		return;
	}

	$output = '<div class="gmw-item gmw-location-meta-wrapper">';

	if ( ! empty( $label ) ) {

		$label   = is_string( $label ) ? esc_html( $label ) : __( 'Contact Information', 'geo-my-wp' );
		$output .= '<span class="gmw-location-meta-label gmw-section-label">' . $label . '</span>';
	}

	$output .= $data;
	$output .= '</div>';

	echo $output; // WPCS: XSS ok.
}

/**
 * Display hours of operation in search results.
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 *
 * @param  string $label  label before meta value.
 *
 * @param  string $where  search_results || info_window.
 */
function gmw_search_results_hours_of_operation( $object, $gmw = array(), $label = true, $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['opening_hours'] ) ) {
		return;
	}

	$data = gmw_get_hours_of_operation( $object );

	if ( empty( $data ) ) {
		return;
	}

	$output  = '';
	$output .= '<div class="gmw-item gmw-hours-of-operation-wrapper">';

	if ( ! empty( $label ) ) {
		$label   = is_string( $label ) ? esc_html( $label ) : __( 'Hours of operation', 'geo-my-wp' );
		$output .= '<span class="gmw-hop-label gmw-section-label">' . $label . '</span>';
	}

	$output .= $data;
	$output .= '</div>';

	echo $output; // WPCS: XSS ok.
}

/**
 * Display directions link in search results
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 *
 * @param  string $where  search_results || info_window.
 */
function gmw_search_results_directions_link( $object, $gmw = array(), $where = 'search_results', $label = '', $html_wrap = true ) {

	if ( empty( $gmw[ $where ]['directions_link'] ) ) {
		return;
	}

	$from_location = $gmw['address'];

	$output = gmw_get_directions_link( $object, $from_location, $label, false, $gmw );

	if ( $html_wrap ) {
		$output = '<div class="gmw-item gmw-item-directions gmw-directions-link">' . $output . '</div>';
	}

	echo $output; // WPCS: XSS ok.
}

/**
 * Get directions system
 *
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_directions_system( $object, $gmw = array() ) {

	$args = array(
		'element_id'  => absint( $gmw['ID'] ),
		'origin'      => ! empty( $gmw['form_values']['address'] ) ? implode( ' ', $gmw['form_values']['address'] ) : '',
		'destination' => ! empty( $object->address ) ? $object->address : '',
		'units'       => ! empty( $gmw['form_values']['units'] ) ? $gmw['form_values']['units'] : '',
	);

	echo gmw_get_directions_system( $args ); // WPCS: XSS ok.
}

/**
 * Get orderby dropdown in search results template file
 *
 * @since 3.0
 *
 * @param  array $gmw  gmw form.
 *
 * return HTML element.
 */
function gmw_get_search_results_orderby_filter( $gmw = array() ) {

	if ( empty( $gmw['search_results']['orderby']['enabled'] ) || empty( $gmw['search_results']['orderby']['options'] ) ) {
		return;
	}

	// Explode options from textarea value.
	$orderby = explode( PHP_EOL, $gmw['search_results']['orderby']['options'] );
	$options = array();

	foreach ( $orderby as $option ) {
		$option          = explode( ' : ', $option );
		$val             = trim( $option[0] );
		$options[ $val ] = ! empty( $option[1] ) ? trim( $option[1] ) : $val;
	}

	if ( count( $options ) < 1 ) {
		return;
	}

	$args = array(
		'id'           => $gmw['ID'],
		'ajax_enabled' => 'ajax_forms' === $gmw['addon'] ? true : false,
	);

	return GMW_Template_Functions_Helper::get_orderby_filter( $args, $options );
}

/**
 * Output orderby dropdown in search results template file
 *
 * @since 3.0
 *
 * @param  array $gmw  gmw form.
 */
function gmw_search_results_orderby_filter( $gmw = array() ) {
	echo gmw_get_search_results_orderby_filter( $gmw ); // WPCS: XSS ok.
}

/**
 * Get the location title in the search results.
 *
 * @since 3.4.1
 *
 * @author Eyal Fitoussi
 *
 * @param  string $title  original title.
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 *
 * @return string         title.
 */
function gmw_get_search_results_title( $title, $object, $gmw ) {

	$title = apply_filters( 'gmw_get_location_title', $title, $object, $gmw );
	$title = apply_filters( "gmw_{$gmw['prefix']}_get_location_title", $title, $object, $gmw );

	if ( ! empty( $title ) ) {
		$title = esc_html( stripslashes( $title ) );
	}

	// append the address to the permalink.
	return $title;
}

/**
 * Display the location title in the search results.
 *
 * @since 3.4.1
 *
 * @author Eyal Fitoussi
 *
 * @param  string $title  title.
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_title( $title, $object, $gmw ) {
	echo gmw_get_search_results_title( $title, $object, $gmw ); // WPCS: XSS ok.
}

/**
 * Get the location permalink in the search results.
 *
 * Modify the pemalink and append it with some location data.
 *
 * @since 3.3.1
 *
 * @author Eyal Fitoussi
 *
 * @param  string $url    original permalink.
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 *
 * @return string         modified permalink.
 */
function gmw_get_search_results_permalink( $url, $object, $gmw ) {

	$url_args = array();
	$new_url  = $url;

	// Modify URL parameters only when needed.
	if ( ! empty( $gmw['address'] ) && ! empty( $gmw['modify_permalink'] ) ) {

		// get the permalink args.
		$url_args = array(
			'lid'     => $object->location_id,
			'address' => str_replace( ' ', '+', $gmw['address'] ),
			'lat'     => $gmw['lat'],
			'lng'     => $gmw['lng'],
		);

		if ( ! empty( $object->distance ) ) {
			$url_args['distance'] = $object->distance . $object->units;
		}

		$parsed_url = wp_parse_url( $url );
		$new_url    = apply_filters(
			'gmw_get_serach_results_permalink_args',
			array(
				'url'       => $url,
				'separator' => empty( $parsed_url['query'] ) ? '?' : '&',
				'args'      => $url_args,
			),
		);

		$new_url['args'] = http_build_query( $new_url['args'] );
		$new_url         = implode( $new_url );
	}

	$new_url = apply_filters( 'gmw_get_search_results_permalink', $new_url, $object, $gmw );

	// append the address to the permalink.
	return esc_url( apply_filters( "gmw_{$gmw['prefix']}_get_location_permalink", $new_url, $url, $url_args, $object, $gmw ) );
}

/**
 * Display the location permalink in the search results.
 *
 * @since 3.3.1
 *
 * @author Eyal Fitoussi
 *
 * @param  string $url    original permalink.
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_permalink( $url, $object, $gmw ) {
	echo gmw_get_search_results_permalink( $url, $object, $gmw ); // WPCS: XSS ok.
}

/**
 * Get the location's title with the permalink in the search results.
 *
 * Modified the permalink and append it with some location data when needed.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 *
 * @param  string $url    original permalink.
 *
 * @param  string $title  the title.
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 *
 * @return string         modified permalink.
 */
function gmw_get_search_results_linked_title( $url, $title, $object, $gmw ) {

	$output = '';
	$url    = gmw_get_search_results_permalink( $url, $object, $gmw ); // Already escaped.
	$title  = gmw_get_search_results_title( $title, $object, $gmw ); // Already escaped.
	$atts   = '';

	if ( empty( $url ) ) {
		return $title;
	}

	$attributes = apply_filters( 'gmw_get_search_results_linked_title_attr', array(), $object, $gmw );

	if ( is_array( $attributes ) && ! empty( $attributes ) ) {

		foreach ( $attributes as $key => $value ) {

			$attributes[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';

			unset( $attributes[ $key ] );
		}

		$atts = implode( ' ', $attributes );
	}

	return '<a href="' . $url . '" ' . $atts . '>' . $title . '</a>'; // WPCS: XSS ok. Already escaped in original functions.
}

/**
 * Output the linked title in the search results.
 *
 * @param  string $url    permalink.
 *
 * @param  string $title  title.
 *
 * @param  object $object object in the results.
 *
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_linked_title( $url, $title, $object, $gmw ) {
	echo gmw_get_search_results_linked_title( $url, $title, $object, $gmw ); // WPCS: XSS ok.
}

/**
 * Get result view toggle.
 *
 * @since 4.0.
 *
 * @param array $gmw gme form.
 */
function gmw_get_results_view_toggle( $gmw ) {

	if ( empty( $gmw['search_results']['results_view']['toggle'] ) ) {
		return;
	}

	$view = ! empty( $_COOKIE[ 'gmw_' . $gmw['ID'] . '_results_view' ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ 'gmw_' . $gmw['ID'] . '_results_view' ] ) ) : $gmw['search_results']['results_view']['default'];
	$grid = '';
	$list = '';

	if ( 'grid' === $view ) {
		$grid = ' active';
	} else {
		$list = ' active';
	}

	$output  = '<div class="gmw-results-view-toggle-wrapper">';
	$output .= '<span class="gmw-icon-th-large' . $grid . '" data-view="grid" title="' . esc_attr__( 'Grid View', 'geo-my-wp' ) . '"></span>';
	$output .= '<span class="gmw-icon-th-list ' . $list . '" data-view="list" title="' . esc_attr__( 'List View', 'geo-my-wp' ) . '"></span>';
	$output .= '</div>';

	return $output;
}

/**
 * Output result view toggle.
 *
 * @since 4.0.
 *
 * @param array $gmw gme form.
 */
function gmw_results_view_toggle( $gmw ) {
	echo gmw_get_results_view_toggle( $gmw ); // WPCS: XSS ok.
}

/**
 * Output BP avatar in search results.
 *
 * @param  object $object memebr | group object.
 *
 * @param  array  $gmw    gmw form object.
 *
 * @param  string $where  search_results || info_window.
 *
 * @return [type]         [description]
 */
function gmw_search_results_bp_avatar( $object = array(), $gmw = array(), $where = 'search_results' ) {

	// Abort if iamge is disabled.
	if ( empty( $gmw[ $where ]['image']['enabled'] ) ) {
		return;
	}

	$settings = $gmw[ $where ]['image'];

	$args = array(
		'object_type'  => 'bp_group' === $object->object_type ? 'group' : 'user',
		'object_id'    => $object->object_id,
		'width'        => ! empty( $settings['width'] ) ? $settings['width'] : '150px',
		'height'       => ! empty( $settings['height'] ) ? $settings['height'] : '150px',
		'show_grav'    => ! empty( $settings['show_grav'] ) ? $settings['show_grav'] : false,
		'show_default' => ! empty( $settings['show_default'] ) ? $settings['show_default'] : false,
		'where'        => $where,
	);

	echo gmw_get_bp_avatar( $args, $object, $gmw ); // WPCS: XSS ok.
}

/**
 * Get meta field value in search results.
 *
 * @since 4.0
 *
 * @param  string $type   type of meta field post_meta || user_meta || bp_user_meta || xprofile_field.
 *
 * @param  string $field  field name/slug.
 *
 * @param  object $object the location object in the loop.
 *
 * @return [type]         [description]
 */
function gmw_get_meta_field_value( $type, $field, $object ) {

	$value = '';

	if ( empty( $field ) ) {
		return;
	}

	if ( 'post_meta' === $type ) {

		$value = get_post_meta( $object->ID, $field, true );

	} elseif ( 'user_meta' === $type ) {

		$value = get_user_meta( $object->ID, $field, true );

		if ( ! empty( $field ) ) {

			// Get value for PeepSo select field.
			// PHP 8.0+.
			if ( function_exists( 'str_contains' ) ) {

				if ( str_contains( $field, 'peepso_user_field_' ) && ( is_array( $value ) || str_contains( $value, 'option_' ) ) ) {

					$ps_field_id = str_replace( 'peepso_user_field_', '', $field );
					$options     = maybe_unserialize( get_post_meta( $ps_field_id, 'select_options', true ) );

					if ( ! is_array( $value ) ) {
						$value = ! empty( $options[ $value ] ) ? $options[ $value ] : $value;
					} else {
						$value = array_intersect_key( $options, array_flip( $value ) );
					}
				}

				// PHP -8.0.
			} elseif ( strpos( $field, 'peepso_user_field_' ) !== false && ( is_array( $value ) || strpos( $value, 'option_' ) !== false ) ) {

				$ps_field_id = str_replace( 'peepso_user_field_', '', $field );
				$options     = maybe_unserialize( get_post_meta( $ps_field_id, 'select_options', true ) );

				if ( ! is_array( $value ) ) {
					$value = ! empty( $options[ $value ] ) ? $options[ $value ] : $value;
				} else {
					$value = array_intersect_key( $options, array_flip( $value ) );
				}
			}
		}
	} elseif ( 'bp_user_meta' === $type ) {

		$value = get_user_meta( $object->id, $field, true );

	} elseif ( 'xprofile_field' === $type && function_exists( 'xprofile_get_field' ) ) {

		$field_id   = absint( $field );
		$field_data = xprofile_get_field( $field_id, $object->id, true );

		if ( empty( $field_data ) ) {
			return;
		}

		$value = maybe_unserialize( $field_data->data->value );

		if ( ! empty( $value ) && 'datebox' === $field_data->type ) {
			$value = intval( gmdate( 'Y', time() - strtotime( $value ) ) ) - 1970;
		}
	} elseif ( 'bp_group_meta' === $type && function_exists( 'groups_get_groupmeta' ) ) {

		$value = groups_get_groupmeta( $object->id, $field, true );

	} elseif ( 'gforms_entry_meta' === $type && function_exists( 'gform_get_meta' ) ) {

		$value = isset( $object->{$field} ) ? $object->{$field} : gform_get_meta( $object->id, $field );
	}

	return $value;
}

/**
 * Get Search results meta fields.
 *
 * Get the meta fields in search results and info-window.
 *
 * @since 3.0
 *
 * @param  string $type         type of meta fields post_meta || user_meta || bp_user_meta || xprofile_field.
 *
 * @param  array  $meta_fields  array of meta fields name/slug.
 *
 * @param  object $object       the location object in the loop.
 *
 * @param  array  $gmw          gmw form.
 *
 * @return [type]         [description]
 */
function gmw_get_search_results_meta_fields( $type = 'post_meta', $meta_fields = array(), $object = array(), $gmw = array() ) {

	if ( empty( $meta_fields ) ) {
		return;
	}

	$output = '';

	foreach ( $meta_fields as $field => $options ) {

		if ( empty( $options['field_output'] ) ) {
			continue;
		}

		$field_name  = $options['name'];
		$field_value = gmw_get_meta_field_value( $type, $field, $object );

		if ( empty( $field_value ) ) {
			continue;
		}

		$field_value = maybe_unserialize( $field_value );
		$field_value = is_array( $field_value ) ? implode( ', ', $field_value ) : $field_value;
		$field_value = esc_html( $field_value );

		$output .= '<li class="gmw-meta-field field-name-' . esc_attr( $field_name ) . '">';

		if ( ! empty( $options['label'] ) ) {
			$output .= '<span class="label">' . esc_html( $options['label'] ) . '</span>';
		}

		if ( wp_http_validate_url( $field_value ) ) {
			$field_value = '<a href="' . $field_value . '">' . $field_value . '</a>';
		}

		$options['field_output'] = esc_html( $options['field_output'] );

		$field_value  = apply_filters( 'gmw_results_meta_field_value',$field_value, $field, $options, $gmw );
		$field_output = str_replace( '%field%', $field_value, $options['field_output'] );
		$field_output = apply_filters( 'gmw_meta_field_results_output', $field_output, $field_value, $field, $options, $gmw );

		$output .= '<span class="field">';
		$output .= $field_output;
		$output .= '</span>';
		$output .= '</li>';
	}

	return empty( $output ) ? false : '<ul class="gmw-meta-fields ' . esc_attr( $type ) . '">' . $output . '</ul>';
}

/**
 * Search results meta fields.
 *
 * Display meta fields in search results and info-window.
 *
 * Requires the Premium Settings extension.
 *
 * @since 4.0
 *
 * @param  object $object the object in the loop.
 *
 * @param  array  $gmw    gmw form.
 *
 * @param  string $where  seasrch_results || info_window.
 *
 * @return [type]         [description]
 */
function gmw_search_results_meta_fields( $object = array(), $gmw = array(), $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['meta_fields'] ) ) {
		return;
	}

	$component = $gmw['component'];
	$type      = 'post_meta';

	if ( 'users_locator' === $component ) {

		$type = 'user_meta';

	} elseif ( 'members_locator' === $component ) {

		$type = 'bp_user_meta';

	} elseif ( 'bp_groups_locator' === $component ) {

		$type = 'bp_user_meta';

	} elseif ( 'gforms_entries_locator' === $component ) {

		$type = 'gforms_entry_meta';
	}

	echo gmw_get_search_results_meta_fields( $type, $gmw[ $where ]['meta_fields'], $object, $gmw ); // WPCS: XSS OK.
}
