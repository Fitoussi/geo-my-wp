<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW Single Location shortcode
 *
 * @version 1.0
 *
 * @param array $atts array of arguments.
 *
 * @author Eyal Fitoussi
 */
function gmw_single_location_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {

		$atts = array(
			'object' => 'post',
		);

		// item_type replaced with object_type - remove in the future.
	} elseif ( empty( $atts['object'] ) ) {

		if ( ! empty( $atts['object_type'] ) ) {
			$atts['object'] = $atts['object_type'];

			unset( $atts['object_type'] );

		} elseif ( ! empty( $atts['item_type'] ) ) {
			$atts['object'] = $atts['item_type'];

			gmw_trigger_error( 'item_type shortcode attribute is deprecated. Please use "object" instead.' );

			unset( $atts['item_type'] );
		} else {
			$atts['object'] = 'post';
		}
	}

	// check if standard class of the single object exists.
	if ( class_exists( "GMW_Single_{$atts['object']}_Location" ) ) {

		$class_name = "GMW_Single_{$atts['object']}_Location";

		// otherwise, can use the filter for custom class.
	} else {

		$class_name = apply_filters( 'gmw_single_' . $atts['object'] . '_location_class', '' );

		if ( ! class_exists( $class_name ) ) {
			return;
		}
	}

	$single_location = new $class_name( $atts );

	return $single_location->output();
}
add_shortcode( 'gmw_single_location', 'gmw_single_location_shortcode' );
