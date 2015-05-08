<?php
/**
 * GMW Current Location shortcode
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_current_location_shortcode( $atts ) {

	if ( !class_exists( 'GMW_Current_location' ) )
		return;

	$current_location = new GMW_Current_location( $atts );

	return $current_location->display();
}
add_shortcode( 'gmw_current_location', 'gmw_current_location_shortcode' );