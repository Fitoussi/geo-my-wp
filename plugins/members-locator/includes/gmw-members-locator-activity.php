<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Record location activity
 *
 * @param  [type] $args [description]
 *
 * @return [type]       [description]
 */
function gmw_record_member_location_activity( $user_id, $user_location ) {

	if ( ! function_exists( 'bp_activity_add' ) ) {
		return false;
	}

	// get user link
	$user_link = bp_core_get_userlink( $user_id );
	// get user's current address if exists
	$current_address = ! empty( $_COOKIE['gmw_ul_formatted_address'] ) ? '&daddr=' . str_replace( ' ', '+', urldecode( $_COOKIE['gmw_ul_formatted_address'] ) ) : '';
	// region and language
	$region   = '&region=' . gmw_get_option( 'general_settings', 'country_code', 'us' );
	$language = '&hl=' . gmw_get_option( 'general_settings', 'langauge_code', 'en' );

	// modify the address
	$activity_address = apply_filters( 'gmw_fl_activity_address_fields', $user_location['formatted_address'], $user_location, $user_id );

	// show address in activity only if enabled or not empty
	if ( ! empty( $activity_address ) ) {

		$activity_text     = __( '%1$s updated new location at %2$s', 'geo-my-wp' );
		$activity_location = '<a class="gmw-fl-location-activity gmw-icon-location" target="_blank" href="' . esc_url( 'https://maps.google.com/maps?f=d' . $language . $region . '&geocode=&saddr=' . $activity_address . $current_address . '&ie=UTF8&z=12' ) . '" >' . esc_attr( $activity_address ) . '</a>';
		$activity_action   = sprintf( $activity_text, $user_link, $activity_location );

	} else {
		$activity_text   = __( '%s updated new location', 'geo-my-wp' );
		$activity_action = sprintf( $activity_text, $user_link );
	}

	// generate activity arguments
	$args = apply_filters(
		'gmw_fl_activity_update_args', array(
			'user_id'       => $user_id,
			'action'        => $activity_action,
			'primary_link'  => bp_core_get_userlink( $user_id, false, true ),
			'component'     => buddypress()->members->id,
			'type'          => 'gmw_member_location_updated',
			'recorded_time' => gmdate( 'Y-m-d H:i:s' ),
		), $user_id, $user_location, $current_address
	);

	// generate activity
	$activity_id = bp_activity_add( $args );

	// do something after activity updated
	do_action( 'gmw_fl_after_activity_updated', $activity_id, $args, $user_id, $user_location );

	return $activity_id;
}
