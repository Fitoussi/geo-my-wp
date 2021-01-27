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

	include_once 'class-gmw-member-location-form.php';

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
