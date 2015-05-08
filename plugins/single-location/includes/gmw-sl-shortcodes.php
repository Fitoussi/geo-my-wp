<?php

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

/**
 * GMW Single Location shortcode
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_single_location_shortcode( $atts ) {
	
	$atts['item_type'] = ( !empty( $atts['item_type'] ) ) ? $atts['item_type'] : 'post';

	//make sure the class of the item exists
	if ( !class_exists( "GMW_Single_{$atts['item_type']}_Location" ) )
		return;

	$class_name = "GMW_Single_{$atts['item_type']}_Location";

	$single_location = new $class_name( $atts );

	return $single_location->display();
}
add_shortcode( 'gmw_single_location', 'gmw_single_location_shortcode' );