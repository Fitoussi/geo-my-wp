<?php
/**
 * GMW Infow Window Template functions.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Display address fields in infow window
 *
 * @param  object $object the location object.
 *
 * @param  array  $gmw    gmw form.
 */
function gmw_info_window_address( $object, $gmw = array() ) {
	gmw_search_results_address( $object, $gmw, 'info_window' );
}

/**
 * Linked address in the info window.
 *
 * Display address that links to a new page with Google Map
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 */
function gmw_info_window_linked_address( $object, $gmw = array() ) {

	if ( empty( $gmw['info_window']['address_fields'] ) ) {
		return;
	}

	$output = gmw_get_linked_location_address( $object, $gmw['info_window']['address_fields'], $gmw );

	if ( ! empty( $output ) ) {
		echo '<i class="gmw-icon-location-thin"></i>' . $output; // WPCS: xss ok.
	}
}

/**
 * Display distance in AJAX info-window
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 */
function gmw_info_window_distance( $object = array(), $gmw = array() ) {
	gmw_search_results_distance( $object, $gmw, 'info_window' );
}

/**
 * Display list of location meta in info-window
 *
 * @param  object  $object location object.
 *
 * @param  array   $gmw    gmw form.
 *
 * @param  boolean $label  [description].
 *
 * @return [type]          [description]
 */
function gmw_info_window_location_meta( $object, $gmw = array(), $label = true ) {
	gmw_search_results_location_meta( $object, $gmw, $label, $where = 'info_window' );
}

/**
 * Display hours of operation in info-window
 *
 * @param  object  $object location object.
 *
 * @param  array   $gmw    gmw form.
 *
 * @param  boolean $label  [description].
 */
function gmw_info_window_hours_of_operation( $object, $gmw = array(), $label = true ) {
	gmw_search_results_hours_of_operation( $object, $gmw, $label, 'info_window' );
}

/**
 * Display directions link in info window
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 */
function gmw_info_window_directions_link( $object, $gmw = array() ) {
	gmw_search_results_directions_link( $object, $gmw, 'info_window' );
}

/**
 * Display directions system in info window
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 */
function gmw_info_window_directions_system( $object, $gmw = array() ) {

	// Disabled temporarily.
	return;
	// to support custom templates that have $gmw as first
	// argument and do no have $object.
	if ( empty( $gmw ) ) {
		$gmw    = $object;
		$object = new stdClass();
	}

	if ( ! $gmw['info_window']['directions_system'] ) {
		return;
	}

	$args = array(
		'element_id'  => absint( $gmw['ID'] ),
		'origin'      => ! empty( $gmw['form_values']['address'] ) ? implode( ' ', $gmw['form_values']['address'] ) : '',
		'destination' => ! empty( $object->address ) ? $object->address : '',
		'units'       => ! empty( $gmw['form_values']['units'] ) ? $gmw['form_values']['units'] : '',
	);

	echo gmw_get_directions_system( $args ); // WPCS: XSS ok.
}

/**
 * Get the location title in the info window.
 *
 * @since 3.6.2
 *
 * @author Eyal Fitoussi
 *
 * @param  string $title  original title.
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 *
 * @return string         title.
 */
function gmw_get_info_window_title( $title, $object, $gmw ) {
	return gmw_get_search_results_title( $title, $object, $gmw );
}

/**
 * Output the location title in the info window.
 *
 * @since 3.6.2
 *
 * @author Eyal Fitoussi
 *
 * @param  string $title  title.
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_info_window_title( $title, $object, $gmw ) {
	echo gmw_get_search_results_title( $title, $object, $gmw ); // WPCS: XSS ok.
}

/**
 * Output the permalinked title in the info window.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 *
 * @param  url    $url   permalink.
 *
 * @param  string $title  title.
 *
 * @param  object $object location object.
 *
 * @param  array  $gmw    gmw form.
 */
function gmw_info_window_linked_title( $url, $title, $object, $gmw ) {
	echo gmw_get_search_results_linked_title( $url, $title, $object, $gmw ); // WPCS: XSS ok.
}

/**
 * Get the location permalink in the info window.
 *
 * Modify the pemalink and append it with some location data.
 *
 * @since 3.6.2
 *
 * @author Eyal Fitoussi
 *
 * @param  string $url    original permalink.
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 *
 * @return string         modified permalink.
 */
function gmw_get_info_window_permalink( $url, $object, $gmw ) {
	return gmw_get_search_results_permalink( $url, $object, $gmw );
}

/**
 * Display the location permalink in the info window.
 *
 * @since 3.6.2
 *
 * @author Eyal Fitoussi
 *
 * @param  string $url    original permalink.
 * @param  object $object location object.
 * @param  array  $gmw    gmw form.
 */
function gmw_info_window_permalink( $url, $object, $gmw ) {
	echo gmw_get_info_window_permalink( $url, $object, $gmw ); // WPCS: XSS ok.
}

/**
 * Posts locator iw functions
 */
if ( gmw_is_addon_active( 'posts_locator' ) ) {

	/**
	 * Display featured image in info window
	 *
	 * @param  object $post post object.
	 *
	 * @param  array  $gmw  gmw form.
	 */
	function gmw_info_window_featured_image( $post, $gmw = array() ) {
		gmw_search_results_featured_image( $post, $gmw, 'info_window' );
	}

	/**
	 * Display excerpt in info window
	 *
	 * @param  object $post post object.
	 *
	 * @param  array  $gmw  gmw form.
	 */
	function gmw_info_window_post_excerpt( $post, $gmw = array() ) {
		gmw_search_results_post_excerpt( $post, $gmw, $where = 'info_window' );
	}
}

/**
 * Users Locator functions
 */
if ( gmw_is_addon_active( 'users_locator' ) ) {

	/**
	 * Display user avatar in info window
	 *
	 * @param  object $user user object.
	 *
	 * @param  array  $gmw  gmw form.
	 */
	function gmw_info_window_user_avatar( $user, $gmw = array() ) {
		gmw_search_results_user_avatar( $user, $gmw, 'info_window' );
	}
}

/**
 * BuddyPress group and memebr functions
 */
if ( class_exists( 'buddypress' ) && ( gmw_is_addon_active( 'members_locator' ) || gmw_is_addon_active( 'bp_groups_locator' ) ) ) {

	/**
	 * Display BP avatar in info window ( for group or member )
	 *
	 * @param  object $object user/group object.
	 *
	 * @param  array  $gmw  gmw form.
	 */
	function gmw_info_window_bp_avatar( $object, $gmw = array() ) {
		gmw_search_results_bp_avatar( $object, $gmw, 'info_window' );
	}

	/**
	 * Display xprofile fields in search results
	 *
	 * @param  object $member user object.
	 *
	 * @param  array  $gmw    gmw form.
	 */
	function gmw_info_window_member_xprofile_fields( $member, $gmw = array() ) {

		if ( ! function_exists( 'gmw_get_member_xprofile_fields' ) ) {
			return;
		}

		// Look for profile fields in form settings.
		$total_fields = ! empty( $gmw['info_window']['xprofile_fields']['fields'] ) ? $gmw['info_window']['xprofile_fields']['fields'] : array();

		// look for date profile field in form settings.
		if ( '' !== $gmw['info_window']['xprofile_fields']['date_field'] ) {
			array_unshift( $total_fields, $gmw['info_window']['xprofile_fields']['date_field'] );
		}

		// abort if no profile fields were chosen.
		if ( empty( $total_fields ) ) {
			return;
		}

		if ( is_object( $member ) ) {
			$user_id = $member->id;
		} elseif ( is_int( $member ) ) {
			$user_id = $member;
		} else {
			return false;
		}

		echo gmw_get_member_xprofile_fields( $user_id, $total_fields ); // WPCS: XSS ok.
	}
}
