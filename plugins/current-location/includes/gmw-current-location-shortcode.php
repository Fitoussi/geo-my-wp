<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW Current Location shortcode
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 */
function gmw_current_location_shortcode( $atts ) {

	// abort if class was not found
	if ( ! class_exists( 'GMW_Current_location' ) ) {
		return;
	}

	// new shortcode
	$current_location = new GMW_Current_location( $atts );

	// display the shortcode
	return $current_location->output();
}
add_shortcode( 'gmw_current_location', 'gmw_current_location_shortcode' );
