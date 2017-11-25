<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW Single Location shortcode
 * 
 * @version 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_single_location_shortcode( $atts ) {
	
	// item_type replaced with object_type - remove in the future
	if ( empty( $atts['object_type'] ) && ! empty( $atts['item_type'] ) ) {
		$atts['object_type'] = $atts['item_type'];

		trigger_error( 'item_type shortcode attribute is deprecated. Please use object_type instead.', E_USER_NOTICE );

		unset( $atts['item_type'] );
	}

	$object_type = 'post';

	if ( ! empty( $atts['object_type'] ) ) {
		
		$object_type = $atts['object_type'];
	
	} elseif ( ! empty( $atts['item_type'] ) ) {
		
		$object_type = $atts['item_type'];
	}

	// make sure the class of the item exists
	if ( ! class_exists( "GMW_Single_{$object_type}_Location" ) ) {
		return;
	}

	$class_name = "GMW_Single_{$object_type}_Location";

	$single_location = new $class_name( $atts );

	return $single_location->output();
}
add_shortcode( 'gmw_single_location', 'gmw_single_location_shortcode' );