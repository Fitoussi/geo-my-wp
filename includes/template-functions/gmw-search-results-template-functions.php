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
 * Display address fields in search results
 *
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_address( $object, $gmw = array() ) {

	if ( empty( $gmw['search_results']['address_fields'][0] ) || 'address' === $gmw['search_results']['address_fields'][0] ) {
		$fields = array( 'formatted_address' );
	} else {
		$fields = $gmw['search_results']['address_fields'];
	}

	$output = gmw_get_location_address( $object, $fields, $gmw );

	if ( ! empty( $output ) ) {
		echo '<i class="gmw-icon-location-thin"></i>' . $output; // WPCS: XSS ok.
	}
}

/**
 * Display address that links to a new page with Google Map
 *
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_linked_address( $object, $gmw = array() ) {

	if ( empty( $gmw['search_results']['address_fields'] ) ) {
		$fields = array( 'formatted_address' );
	} else {
		$fields = $gmw['search_results']['address_fields'];
	}

	$output = gmw_get_linked_location_address( $object, $fields, $gmw );

	if ( ! empty( $output ) ) {
		echo '<i class="gmw-icon-location-thin"></i>' . $output; // WPCS: XSS ok.
	}
}

/**
 * Get the distance to location
 *
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_distance( $object = array(), $gmw = array() ) {
	$distance = gmw_get_distance_to_location( $object );
	if ( $distance ) {
		echo '<span class="distance">' . $distance . '</span>'; // WPCS: XSS ok.
	}
}

/**
 * Display list of location meta in search results
 *
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 * @param  string $label  label before meta value.
 */
function gmw_search_results_location_meta( $object, $gmw = array(), $label = true ) {

	if ( empty( $gmw['search_results']['location_meta'] ) ) {
		return;
	}

	$data = gmw_get_location_meta_list( $object, $gmw['search_results']['location_meta'] );

	if ( empty( $data ) ) {
		return;
	}

	$output = '<div class="gmw-location-meta-wrapper">';

	if ( ! empty( $label ) ) {
		$label   = is_string( $label ) ? esc_html( $label ) : __( 'Contact Information', 'geo-my-wp' );
		$output .= '<h3>' . $label . '</h3>';
	}

	$output .= $data;
	$output .= '</div>';

	echo $output; // WPCS: XSS ok.
}

/**
 * Display hours of operation in search results
 *
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 * @param  string $label  label before meta value.
 */
function gmw_search_results_hours_of_operation( $object, $gmw = array(), $label = true ) {

	if ( ! isset( $gmw['search_results']['opening_hours'] ) || '' === $gmw['search_results']['opening_hours'] ) {
		return;
	}

	$data = gmw_get_hours_of_operation( $object );

	if ( false === $data ) {
		return;
	}

	$output = '';

	$output .= '<div class="gmw-hours-of-operation-wrapper">';

	if ( ! empty( $label ) ) {
		$label   = is_string( $label ) ? esc_html( $label ) : __( 'Hours of operation', 'geo-my-wp' );
		$output .= '<h3>' . $label . '</h3>';
	}

	$output .= $data;
	$output .= '</div>';

	echo $output; // WPCS: XSS ok.
}

/**
 * Display directions link in search results
 *
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_search_results_directions_link( $object, $gmw = array() ) {

	if ( ! isset( $gmw['search_results']['directions_link'] ) || '' === $gmw['search_results']['directions_link'] ) {
		return;
	}

	$from_coords = array(
		'lat' => $gmw['lat'],
		'lng' => $gmw['lng'],
	);

	echo '<span class="gmw-directions-link">' . gmw_get_directions_link( $object, $from_coords ) . '</span>'; // WPCS: XSS ok.
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
 * @param  array $args array of arguments.
 *
 * return HTML element.
 */
function gmw_get_search_results_orderby_filter( $gmw = array(), $args = false ) {

	if ( empty( $gmw['search_results']['orderby'] ) ) {
		return;
	}

	$orderby = explode( ',', $gmw['search_results']['orderby'] );

	if ( count( $orderby ) < 1 ) {
		return;
	}

	$options = array();

	// generate orderby options.
	foreach ( $orderby as $item ) {

		$item = explode( ':', $item );

		if ( isset( $item[0] ) ) {
			$options[ $item[0] ] = isset( $item[1] ) ? $item[1] : $item[0];
		}
	}

	if ( ! $args ) {

		$args = array(
			'id' => $gmw['ID'],
		);
	}

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

	if ( apply_filters( 'gmw_results_title_location_name_enabled', false ) && ! empty( $object->location_name ) && $object->location_name !== $title ) {
		$title .= ' - ' . esc_html( $object->location_name );
	}

	// append the address to the permalink.
	return esc_html( apply_filters( "gmw_{$gmw['prefix']}_get_location_title", $title, $object, $gmw ) );
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

	// abort if no address.
	if ( empty( $gmw['address'] ) || empty( $gmw['modify_permalink'] ) ) {
		return $url;
	}

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

	// append the address to the permalink.
	return esc_url( apply_filters( "gmw_{$gmw['prefix']}_get_location_permalink", $url . '?' . http_build_query( $url_args ), $url, $url_args, $object, $gmw ) );
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
