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

	if ( bp_has_members(
		array(
			'include' => array( $location->object_id ),
			'type'    => 'alphabetical',
		)
	) ) {

		while ( bp_members() ) {

			bp_the_member();

			global $members_template;

			// get additional user location data.
			$location_data = gmw_get_user_location( $location->location_id, true );
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
				$members_template->member->location_meta = gmw_get_location_meta( $location->location_id, $gmw['info_window']['location_meta'] );
			}

			// append distance + units to member.
			$members_template->member->distance = ! empty( $location->distance ) ? $location->distance : '';
			$members_template->member->units    = ! empty( $location->units ) ? $location->units : '';

			// modify member object.
			$members_template->member = apply_filters( 'gmw_ajaxfmsfl_member_before_info_window', $members_template->member, $gmw );
			$member                   = $members_template->member;

			$file_path   = realpath( $gmw['info_window_template']['content_path'] );
			$base_path   = realpath( GMW_FL_PATH . '/templates' ) . DIRECTORY_SEPARATOR;
			$base_custom = realpath( get_stylesheet_directory() . '/geo-my-wp' ) . DIRECTORY_SEPARATOR;

			if ( false === $file_path || strpos( $file_path, $base_path ) !== 0 && strpos( $file_path, $base_custom ) !== 0 ) {

				gmw_trigger_error( 'Info-window template file is missing.' );

				return;
			}

			require $gmw['info_window_template']['content_path'];

			do_action( 'gmw_ajaxfmsfl_after_member_info_window', $member, $gmw );
		}
	}
}
