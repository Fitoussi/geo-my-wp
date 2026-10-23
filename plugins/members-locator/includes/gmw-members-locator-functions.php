<?php
/**
 * GEO my WP - Members Locator functions.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate the user location form
 *
 * @param  array $args shortcode attributes.
 *
 * @return [type]       [description]
 */
function gmw_member_location_form( $args = array() ) {

	// verify user ID.
	if ( empty( $args['object_id'] ) ) {

		if ( isset( $args['user_id'] ) ) {

			$args['object_id'] = $args['user_id'];

		} elseif ( isset( $args['member_id'] ) ) {

			$args['object_id'] = $args['member_id'];

		} else {
			$args['object_id'] = get_current_user_id();
		}
	}

	// default args.
	$defaults = array(
		'location_id'    => 0,
		'object_id'      => $args['object_id'],
		'form_template'  => 'location-form-tabs-top',
		'submit_enabled' => 1,
		'stand_alone'    => 1,
		'ajax_enabled'   => 1,
		'auto_confirm'   => 1,
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_member_location_form_args', $args );

	if ( ! absint( $args['object_id'] ) ) {
		return;
	}

	require_once 'class-gmw-member-location-form.php';

	if ( ! class_exists( 'GMW_Member_Location_Form' ) ) {
		return;
	}

	// generate new location form.
	$location_form = new GMW_Member_Location_Form( $args );

	// display the location form.
	$location_form->display_form();
}

/**
 * Generate the member location form using shortcode
 *
 * @param  array $atts shortcode attributes.
 *
 * @return [type]       [description]
 */
function gmw_fl_member_location_form_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	ob_start();

	gmw_member_location_form( $atts );

	$content = ob_get_clean();

	return $content;
}
add_shortcode( 'gmw_member_location_form', 'gmw_fl_member_location_form_shortcode' );

/**
 * Load member info-window contant via AJAX.
 *
 * @param object $location location object.
 *
 * @param array  $gmw gmw form.
 *
 * @since 4.4.0.3
 *
 * @return void
 */
function gmw_members_locator_ajax_info_window_loader( $location, $gmw ) {

	if ( empty( $location ) || ! isset( $location->object_id ) || ! isset( $location->location_id ) ) {
		return;
	}

	$member_id   = filter_var( $location->object_id, FILTER_VALIDATE_INT, [ 'options' => [ 'min_range' => 1 ] ] );
	$location_id = filter_var( $location->location_id, FILTER_VALIDATE_INT, [ 'options' => [ 'min_range' => 1 ] ] );

	if ( false === $member_id || false === $location_id || ! get_userdata( $member_id ) || ! bp_is_user_active( $member_id ) ) {
		return;
	}

	// Match BuddyPress's members-component access policy, including private communities.
	if ( function_exists( 'bp_get_community_visibility' ) && ! bp_current_user_can( 'bp_view', [ 'bp_component' => 'members' ] ) ) {
		return;
	}

	// BuddyBoss's legacy API returns false when Private Website is enabled.
	if ( function_exists( 'bp_enable_private_network' ) && ! is_user_logged_in() && ! bp_enable_private_network() ) {
		return;
	}

	$location_data = gmw_get_user_location( $location_id, true );

	// Never attach another object's location or metadata to the requested member.
	if (
		empty( $location_data )
		|| (int) ( $location_data->ID ?? 0 ) !== $location_id
		|| 'user' !== ( $location_data->object_type ?? '' )
		|| (int) ( $location_data->object_id ?? 0 ) !== $member_id
		|| (int) gmw_get_blog_id( 'user' ) !== (int) ( $location_data->blog_id ?? 0 )
		|| ( apply_filters( 'gmw_search_query_location_status_enabled', true, $gmw ) && 1 !== (int) ( $location_data->status ?? 0 ) )
	) {
		return;
	}

	if ( bp_has_members(
		array(
			'include' => array( $member_id ),
			'type'    => 'alphabetical',
		)
	) ) {

		while ( bp_members() ) {

			bp_the_member();

			global $members_template;

			// Retain BuddyPress query/privacy filters, but only render the requested member.
			if ( $member_id !== (int) bp_get_member_user_id() ) {
				continue;
			}

			$fields        = array(
				'lat',
				'lng',
				'latitude',
				'longitude',
				'street',
				'premise',
				'city',
				'region_code',
				'region_name',
				'postcode',
				'country_code',
				'country_name',
				'address',
				'formatted_address',
				'location_name',
				'featured_location',
			);

			// append location to the member object.
			foreach ( $location_data as $field => $value ) {
				$members_template->member->$field = $value;
			}

			// get location meta if needed and append it to the member.
			if ( ! empty( $gmw['info_window']['location_meta'] ) ) {
				$members_template->member->location_meta = gmw_get_location_meta( $location_data->ID, $gmw['info_window']['location_meta'] );
			}

			// append distance + units to member.
			$members_template->member->distance = ! empty( $location->distance ) ? $location->distance : '';
			$members_template->member->units    = ! empty( $location->units ) ? $location->units : '';

			// modify member object.
			$members_template->member = apply_filters( 'gmw_ajaxfmsfl_member_before_info_window', $members_template->member, $gmw );
			$member                   = $members_template->member;

			$file_path = gmw_validate_ajax_info_window_template_path( $gmw['info_window_template']['content_path'], $gmw );

			if ( false === $file_path ) {

				gmw_trigger_error( 'Info-window template file is missing.' );

				return;
			}

			require $file_path;

			do_action( 'gmw_ajaxfmsfl_after_member_info_window', $member, $gmw );
		}
	}
}
