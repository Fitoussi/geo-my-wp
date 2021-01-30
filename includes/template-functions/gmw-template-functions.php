<?php
/**
 * GEO my WP template functions.
 *
 * Generates the proximity search forms.
 *
 * This class should be extended for different object types.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
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
	echo gmw_get_results_message( $gmw ); // WPCS: XSS ok.
}

/**
 * Get no results message
 *
 * @param  array $gmw gmw form.
 *
 * @return HTLM element.
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
	echo gmw_get_no_results_message( $gmw ); // WPCS: XSS ok.
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
		'map_id'         => $gmw['ID'],
		'prefix'         => $gmw['prefix'],
		'map_type'       => $gmw['addon'],
		'map_width'      => $gmw['results_map']['map_width'],
		'map_height'     => $gmw['results_map']['map_height'],
		'expand_on_load' => ! empty( $gmw['results_map']['expand_on_load'] ) ? true : false,
		'init_visible'   => $init_visible,
	);

	return GMW_Maps_API::get_map_element( $args, $implode );
}

/**
 * Output map in search results template file
 *
 * @param array   $gmw gmw form.
 * @param boolean $init_visible show on page load?.
 */
function gmw_results_map( $gmw, $init_visible = true ) {

	if ( 'results' !== $gmw['map_usage'] ) {
		return;
	}

	do_action( 'gmw_before_map', $gmw );
	do_action( "gmw_{$gmw['prefix']}_before_map", $gmw );

	echo gmw_get_results_map( $gmw, $init_visible ); // WPCS: XSS ok.

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

	echo gmw_get_results_map( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_shortcode_map', $gmw );
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
	echo gmw_get_pagination( $gmw ); // WPCS: XSS ok.
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
	echo gmw_get_ajax_pagination( $gmw ); // WPCS: XSS ok.
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
	echo gmw_get_per_page( $gmw ); // WPCS: XSS ok.
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
	echo gmw_get_distance_to_location( $object ); // WPCS: XSS ok.
}

/**
 * Get excerpt
 *
 * Display specific number of words and add a read more link to
 * a content.
 *
 * @param array $args array of args.
 * @param array $gmw  gmw form.
 *
 * @return excerpt.
 */
function gmw_get_excerpt( $args = array(), $gmw = false ) {

	// temporary, to support older search results template files.
	if ( is_object( $args ) && ! empty( $gmw ) ) {

		gmw_trigger_error( 'Do not use gmw_get_excerpt nor gmw_excerpt functions directly to retrieve the post excerpt in the search results template file. Use gmw_search_results_post_excerpt functions instead. Since GEO my WP 3.0.' );

		echo gmw_search_results_post_excerpt( $args, $gmw ); // WPCS: XSS ok.

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
	echo gmw_get_excerpt( $args, $gmw ); // WPCS: XSS ok.
}

/**
 * Get hours of operation.
 *
 * @param object  $location  location object..
 *
 * @param integer $object_id object ID.
 *
 * @since 3.0
 */
function gmw_get_hours_of_operation( $location = 0, $object_id = 0 ) {

	// if location ID provided.
	if ( is_int( $location ) ) {

		$days_hours = gmw_get_location_meta( $location, 'days_hours' );

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
	echo gmw_get_hours_of_operation( $location, $object_id ); // WPCS: XSS ok.
}
