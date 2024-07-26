<?php
/**
 * GEO my WP template functions.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Generate GEo my WP address filters.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 *
 * @param  array $gmw gmw form.
 *
 * @return [type]      [description]
 */
function gmw_form_get_address_filters( $gmw ) {

	$address_filters = array();

	// if on page load results.
	if ( ! empty( $gmw['page_load_action'] ) ) {

		if ( ! empty( $gmw['page_load_results']['city_filter'] ) ) {
			$address_filters['city'] = $gmw['page_load_results']['city_filter'];
		}

		if ( ! empty( $gmw['page_load_results']['state_filter'] ) ) {
			$address_filters['region_name'] = $gmw['page_load_results']['state_filter'];
		}

		if ( ! empty( $gmw['page_load_results']['zipcode_filter'] ) ) {
			$address_filters['postcode'] = $gmw['page_load_results']['zipcode_filter'];
		}

		if ( ! empty( $gmw['page_load_results']['country_filter'] ) ) {
			$address_filters['country_code'] = $gmw['page_load_results']['country_filter'];
		}
	}

	// if searching within state or country only is enabled.
	if ( ! empty( $gmw['submitted'] ) ) {

		// if searching state boundaries.
		if ( ! empty($gmw['boundaries_search']['state'] ) && ! empty( $gmw['form_values']['state'] ) ) {
			$address_filters['region_name'] = $gmw['form_values']['state'];
		}

		// When searchin boundaries of a country.
		if ( ! empty( $gmw['boundaries_search']['country'] ) && ! empty( $gmw['form_values']['country'] ) ) {
			$address_filters['country_code'] = $gmw['form_values']['country'];
		}
	}

	return $address_filters;
}

/**
 * Get search results from internal cache.
 *
 * @author Eyal Fitoussi.
 *
 * @param  array  $args [description].
 *
 * @param  string $key  [description].
 *
 * @return [type]       [description]
 */
function gmw_form_get_cached_results( $args = array(), $key = 'gmw_get_object_user_query' ) {

	if ( empty( $args ) ) {
		return false;
	}

	$hash            = md5( wp_json_encode( $args ) );
	$query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( $key );

	return get_transient( $query_args_hash );
}

/**
 * Get the search results message
 *
 * @param  [type] $gmw gmw form.
 *
 * @return [type]      [description]
 */
function gmw_get_results_message( $gmw ) {

	$allowed = array(
		'a'    => array(
			'title' => array(),
			'href'  => array(),
		),
		'p'    => array(),
		'em'   => array(),
		'span' => array(
			'class' => array(),
		),
	);

	return ! empty( $gmw['results_message'] ) ? wp_kses( $gmw['results_message'], $allowed ) : '';
}

/**
 * Display the search results message
 *
 * @param  [type] $gmw gmw form.
 */
function gmw_results_message( $gmw ) {
	echo gmw_get_results_message( $gmw ); // phpcs:ignore: XSS ok.
}

/**
 * Get no results message
 *
 * @param  array $gmw gmw form.
 *
 * @return mixed HTLM no results element.
 */
function gmw_get_no_results_message( $gmw = array() ) {

	// allowed characters can be filtered.
	$allowed = array(
		'a'      => array(
			'href'          => array(),
			'title'         => array(),
			'alt'           => array(),
			'class'         => array(),
			'id'            => array(),
			'data-id'       => array(),
			'data-distance' => array(),
		),
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'p'      => array(),
	);

	$message = isset( $gmw['no_results_message'] ) ? $gmw['no_results_message'] : '';

	// filter the no results message.
	$message = apply_filters( 'gmw_no_results_message', $message, $gmw );

	return wp_kses( $message, $allowed );
}

/**
 * Output no results message
 *
 * @param  array $gmw gmw form.
 */
function gmw_no_results_message( $gmw = array() ) {
	echo gmw_get_no_results_message( $gmw ); // phpcs:ignore: XSS ok.
}

/**
 * Generate map in search results
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 *
 * @param array   $gmw gmw form.
 *
 * @param boolean $init_visible show on page load?.
 *
 * @param boolean $implode impload the element?.
 */
function gmw_get_results_map( $gmw, $init_visible = true, $implode = true ) {

	$args = array(
		'map_id'                  => $gmw['ID'],
		'prefix'                  => $gmw['prefix'],
		'map_type'                => $gmw['addon'],
		'map_width'               => ! empty( $gmw['results_map']['map_width'] ) ? $gmw['results_map']['map_width'] : '100%',
		'map_height'              => ! empty( $gmw['results_map']['map_height'] ) ? $gmw['results_map']['map_height'] : '300px',
		'expand_on_load'          => ! empty( $gmw['results_map']['expand_on_load'] ) ? true : false,
		'boundaries_filter'       => ! empty( $gmw['results_map']['boundaries_filter']['usage'] ) ? $gmw['results_map']['boundaries_filter']['usage'] : 'disabled',
		'boundaries_filter_label' => ! empty( $gmw['results_map']['boundaries_filter']['label'] ) ? $gmw['results_map']['boundaries_filter']['label'] : '',
		'init_visible'            => $init_visible,
		'implode'                 => $implode,
	);

	return gmw_get_map_element( $args, $gmw );
}

/**
 * Output map in search results template file
 *
 * @param array   $gmw gmw form.
 *
 * @param boolean $init_visible show on page load?.
 */
function gmw_results_map( $gmw, $init_visible = true ) {

	if ( 'results' !== $gmw['map_usage'] ) {
		return;
	}

	do_action( 'gmw_before_map', $gmw );
	do_action( "gmw_{$gmw['prefix']}_before_map", $gmw );

	echo gmw_get_results_map( $gmw, $init_visible ); // phpcs:ignore: XSS ok.

	do_action( 'gmw_after_map', $gmw );
	do_action( "gmw_{$gmw['prefix']}_after_map", $gmw );
}

/**
 * Output map in shortcode
 *
 * @param array $gmw gmw form.
 */
function gmw_shortcode_map( $gmw ) {

	if ( 'shortcode' !== $gmw['map_usage'] ) {
		return;
	}

	do_action( 'gmw_before_shortcode_map', $gmw );

	echo gmw_get_results_map( $gmw ); // phpcs:ignore: XSS ok.

	do_action( 'gmw_after_shortcode_map', $gmw );
}

/**
 * Get info-window template data.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 *
 * @param array $gmw gmw form.
 *
 * @return [type]      [description]
 */
function gmw_get_info_window_template_data( $gmw ) {

	$iw_type       = $gmw['info_window']['iw_type'];
	$template_name = $gmw['info_window']['template'][ $iw_type ];

	// get info-window stylesheet.
	$template = gmw_get_info_window_template( $gmw['component'], $iw_type, $template_name );

	// If template wasn't found, check if it exists in the deprecated location of theme's folder.
	if ( strpos( $template_name, 'custom_' ) !== false && ! file_exists( $template['content_path'] ) ) {
		$template = gmw_get_info_window_template( $gmw['component'], $iw_type, $template_name, $gmw['addon'] );
	}

	if ( ! wp_style_is( $template['stylesheet_handle'], 'enqueued' ) && file_exists( $template['stylesheet_path'] ) ) {
		wp_enqueue_style( $template['stylesheet_handle'], $template['stylesheet_uri'] ); // phpcs:ignore.
	}

	// Add custom CSS as inline script.
	if ( ! empty( $gmw['info_window']['styles']['custom_css'] ) ) {

		// Needed when registering an inline style.
		if ( ! wp_style_is( $template['stylesheet_handle'], 'enqueued' ) ) {
			wp_register_style( $template['stylesheet_handle'], false ); // phpcs:ignore.
			wp_enqueue_style( $template['stylesheet_handle'] );
		}

		wp_add_inline_style( $template['stylesheet_handle'], $gmw['info_window']['styles']['custom_css'] );
	}

	return $template;
}

/**
 * Get pagination
 *
 * This function uses the WordPress function paginate_links();
 *
 * @version 1.0
 *
 * @param array $gmw gmw form.
 *
 * @author Eyal Fitoussi
 */
function gmw_get_pagination( $gmw = array() ) {

	// pagination arguments.
	$args = array(
		'id'        => $gmw['ID'],
		'total'     => $gmw['max_pages'],
		'prev_text' => __( 'Prev', 'geo-my-wp' ),
		'next_text' => __( 'Next', 'geo-my-wp' ),
		'page_name' => $gmw['paged_name'],
	);

	return GMW_Template_Functions_Helper::get_pagination( $args );
}

/**
 * Output pagination
 *
 * This function uses the WordPress function paginate_links();
 *
 * @version 1.0
 *
 * @param array $gmw gmw form.
 *
 * @author Eyal Fitoussi
 */
function gmw_pagination( $gmw = array() ) {
	echo gmw_get_pagination( $gmw ); // phpcs:ignore: XSS ok.
}

/**
 * Get pagination function for ajax forms
 *
 * @version 3.0
 *
 * @param array $gmw gmw form.
 *
 * @author Eyal Fitoussi
 */
function gmw_get_ajax_pagination( $gmw = array() ) {

	// pagination arguments.
	$args = array(
		'id'        => $gmw['ID'],
		'total'     => $gmw['max_pages'],
		'prev_text' => __( 'Prev', 'geo-my-wp' ),
		'next_text' => __( 'Next', 'geo-my-wp' ),
		'current'   => $gmw['paged'],
	);

	return GMW_Template_Functions_Helper::get_ajax_pagination( $args );
}

/**
 * Output AJAX pagination
 *
 * Pagination function for ajax forms.
 *
 * @version 3.0
 *
 * @param array $gmw gmw form.
 *
 * @author Eyal Fitoussi
 */
function gmw_ajax_pagination( $gmw = array() ) {
	echo gmw_get_ajax_pagination( $gmw ); // phpcs:ignore: XSS ok.
}

/**
 * Get per page dropdown in search results
 *
 * @since 1.0
 *
 * @param array $gmw gmw form.
 *
 * @author Eyal Fitoussi
 */
function gmw_get_per_page( $gmw = array() ) {

	$args = array(
		'id'            => $gmw['ID'],
		'label'         => __( 'Per page', 'geo-my-wp' ),
		'per_page'      => $gmw['page_load_action'] ? explode( ',', $gmw['page_load_results']['per_page'] ) : explode( ',', $gmw['search_results']['per_page'] ),
		'paged'         => $gmw['paged'],
		'total_results' => $gmw['total_results'],
		'page_name'     => $gmw['paged_name'],
		'submitted'     => $gmw['submitted'],
	);

	return GMW_Template_Functions_Helper::get_per_page( $args );
}

/**
 * Display per page dropdown in search results
 *
 * @since 1.0
 *
 * @param array $gmw gmw form.
 *
 * @author Eyal Fitoussi
 */
function gmw_per_page( $gmw = array() ) {

	do_action( 'gmw_before_per_page_filter', $gmw );

	echo gmw_get_per_page( $gmw ); // phpcs:ignore: XSS ok.

	do_action( 'gmw_after_per_page_filter', $gmw );
}

/**
 * Get the distance to location
 *
 * @param  object $object the item object.
 *
 * @return string distance + units
 */
function gmw_get_distance_to_location( $object = array() ) {

	if ( empty( $object->distance ) ) {
		return false;
	}

	$distance = $object->distance . ' ' . $object->units;
	$distance = apply_filters( 'gmw_distance_to_location', $distance, $object );

	return esc_html( $distance );
}

/**
 * Output the distance to location
 *
 * @param  object $object the item object.
 */
function gmw_distance_to_location( $object = array() ) {
	echo gmw_get_distance_to_location( $object ); // phpcs:ignore: XSS ok.
}

/**
 * Get excerpt
 *
 * Display specific number of words and add a read more link to
 * a content.
 *
 * @param array $args array of args.
 *
 * @param array $gmw  gmw form.
 *
 * @return mixed excerpt.
 */
function gmw_get_excerpt( $args = array(), $gmw = false ) {

	// temporary, to support older search results template files.
	if ( is_object( $args ) && ! empty( $gmw ) ) {

		gmw_trigger_error( 'Do not use gmw_get_excerpt nor gmw_excerpt functions directly to retrieve the post excerpt in the search results template file. Use gmw_search_results_post_excerpt functions instead. Since GEO my WP 3.0.' );

		echo gmw_search_results_post_excerpt( $args, $gmw ); // phpcs:ignore: XSS ok.

		return;
	}

	if ( empty( $args['content'] ) ) {
		return;
	}

	return GMW_Template_Functions_Helper::get_excerpt( $args );
}

/**
 * Output excerpt
 *
 * Display specific number of words and add a read more link to
 * a content.
 *
 * @param array $args array of args.
 * @param array $gmw  gmw form.
 */
function gmw_excerpt( $args = array(), $gmw = false ) {
	echo gmw_get_excerpt( $args, $gmw ); // phpcs:ignore: XSS ok.
}

/**
 * Get hours of operation.
 *
 * @param mixed   $location  location ID or location object.
 *
 * @param integer $object_id object ID.
 *
 * @since 3.0
 */
function gmw_get_hours_of_operation( $location = 0, $object_id = 0 ) {

	// if location ID provided.
	if ( is_int( $location ) ) {

		$days_hours = gmw_get_location_meta( $location, 'days_hours' );

	} elseif ( is_object( $location ) && ! empty( $location->location_id ) ) {

		$days_hours = gmw_get_location_meta( $location->location_id, 'days_hours' );

		// if location object provided.
	} elseif ( is_object( $location ) && ! empty( $location->object_type ) && ! empty( $location->object_id ) ) {

		$days_hours = gmw_get_location_meta_by_object( $location->object_type, $location->object_id, 'days_hours' );

		// if object type and object ID provided.
	} elseif ( is_string( $location ) && ! empty( $object_id ) ) {

		$days_hours = gmw_get_location_meta_by_object( $location, $object_id, 'days_hours' );

	} else {
		return;
	}

	$output = '';
	$data   = '';
	$count  = 0;

	if ( ! empty( $days_hours ) && is_array( $days_hours ) ) {

		foreach ( $days_hours as $dh ) {

			if ( array_filter( $dh ) ) {

				if ( ! apply_filters( 'gmw_get_hours_of_operation_allowed_html', false ) ) {

					$days  = esc_attr( $dh['days'] );
					$class = $days;
					$hours = esc_attr( $dh['hours'] );

				} else {

					$days  = wp_kses_post( $dh['days'] );
					$class = '';
					$hours = wp_kses_post( $dh['hours'] );
				}

				$count++;
				$data .= '<li class="day ' . $class . '"><span class="days">' . $days . ': </span><span class="hours">' . $hours . '</span></li>';
			}
		}
	}

	if ( 0 === $count ) {
		return false;
	}

	$output  = '';
	$output .= '<ul class="gmw-hours-of-operation">';
	$output .= $data;
	$output .= '</ul>';

	return $output;
}

/**
 * Display hours of operation.
 *
 * @param object  $location  location object..
 *
 * @param integer $object_id object ID.
 *
 * @since 3.0
 */
function gmw_hours_of_operation( $location = 0, $object_id = 0 ) {
	echo gmw_get_hours_of_operation( $location, $object_id ); // phpcs:ignore: XSS ok.
}

/**
 * Get BuddyPress avatar ( member || group ).
 *
 * @since 4.0
 *
 * @param  array  $args   arguments.
 *
 * @param  object $object object ( member, group... ).
 *
 * @param  array  $gmw    gmw form object.
 *
 * @return [type]         [description]
 */
function gmw_get_bp_avatar( $args = array(), $object = array(), $gmw = array() ) {

	$args = apply_filters( 'gmw_get_bp_avatar_args', $args, $object, $gmw );
	$args = wp_parse_args(
		$args,
		array(
			'object_type'  => 'user',
			'object_id'    => 0,
			'image_url'    => '',
			'permalink'    => true,
			'width'        => '150px',
			'height'       => '150px',
			'show_grav'    => true,
			'show_default' => true,
			'class'        => 'avatar',
		)
	);

	$avatar_args = array(
		'item_id' => $args['object_id'],
		'object'  => $args['object_type'],
		'type'    => 'full',
		'html'    => false,
		'no_grav' => $args['show_grav'] ? false : true,
	);

	if ( ! $args['show_default'] ) {
		add_filter( 'bp_core_default_avatar_' . $args['object_type'], '__return_false', 50 );
	}

	// Get avatar URL.
	$args['image_url'] = bp_core_fetch_avatar( $avatar_args );

	if ( ! $args['show_default'] ) {
		remove_filter( 'bp_core_default_avatar_' . $args['object_type'], '__return_false', 50 );
	}

	// Get permalink if needed.
	if ( ! empty( $args['permalink'] ) ) {

		if ( 'group' === $args['object_type'] ) {

			$args['permalink'] = function_exists( 'bp_get_group_url' ) ? bp_get_group_url( $object ) : bp_get_group_permalink( $object );

		} else {
			$args['permalink'] = function_exists( 'bp_members_get_user_url' ) ? bp_members_get_user_url( $args['object_id'] ) : bp_core_get_user_domain( $args['object_id'] );
		}
	}

	return gmw_get_image_element( $args, $object, $gmw ); // WPCS: XXS ok.
}

/**
 * Get array of BuddyPress groups from the database.
 *
 * @param  array $groups array of group IDs to retrive specific groups or leave empty to get all groups.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 *
 * @return [type]         [description]
 */
function gmw_get_bp_groups_from_db( $groups = array() ) {

	/**
	 * Use BP built in class to have more options when pulling groups from database.
	 *
	 * This might be a bit more memory consuming and so it is disabled by default.
	 */
	if ( apply_filters( 'gmw_ps_advanced_get_bp_groups_list', false ) && class_exists( 'BP_Groups_Group' ) ) {

		// Advanced method using BP class.
		$groups = BP_Groups_Group::get(
			apply_filters(
				'gmw_search_form_get_groups_list_args',
				array(
					'type'               => 'alphabetical',
					'per_page'           => 999,
					'orderby'            => 'date_created',
					'order'              => 'DESC',
					'page'               => null,
					'user_id'            => 0,
					'slug'               => array(),
					'search_terms'       => false,
					'search_columns'     => array(),
					'group_type'         => '',
					'group_type__in'     => '',
					'group_type__not_in' => '',
					'meta_query'         => false, // phpcs:ignore: slow query ok.
					'include'            => ! empty( $groups ) ? $groups : '',
					'parent_id'          => null,
					'update_meta_cache'  => true,
					'update_admin_cache' => false,
					'exclude'            => false,
					'show_hidden'        => false,
					'status'             => array(),
				)
			)
		);

		$groups = $groups['groups'];

		// Simple method directly from the database.
	} else {

		global $wpdb;

		$bp    = buddypress();
		$table = esc_sql( $bp->groups->table_name );
		$where = '';

		if ( ! empty( $groups ) ) {
			$groups     = array_map( 'absint', $groups );
			$groups_var = esc_sql( implode( ',', $groups ) );
			$where      = "WHERE id IN ( {$groups_var} )";
		}

		$groups = $wpdb->get_results( " SELECT id, name FROM {$table} {$where} " ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, db call ok.
	}

	$output = array();

	foreach ( $groups as $group ) {

		if ( 0 === absint( $group->id ) ) {
			continue;
		}

		$output[ $group->id ] = $group->name;
	}

	return $output;
}
