<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function gmw_fl_location_activity_querystring_filter( $query_string, $object ) {
	
	// not on a checkin area, then return the query without changing it!
	if( !bp_is_current_component( 'gmw_location' )  )
		return $query_string;

	/* Set up the cookies passed on this AJAX request. Store a local var to avoid conflicts */
	if ( !empty( $_POST['cookie'] ) )
		$_GMW_LOCATION_COOKIE = wp_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
	else
		$_GMW_LOCATION_COOKIE = &$_COOKIE;

	$defaults = array( 'page' => false );
	$r = wp_parse_args( $query_string, $defaults );
	extract( $r, EXTR_SKIP );

	//default values to filter on
	$object = 'location';
	$action = 'gmw_location';

	$gmw_location_qs = false;

	$gmw_location_qs[] = 'object='.$object;

	/***
	 * Check if any cookie values are set. If there are then override the default params passed to the
	* template loop
	*/
	if ( !empty( $_GMW_LOCATION_COOKIE['gmw-location-filter'] ) && '-1' != $_GMW_LOCATION_COOKIE['gmw-location-filter'] && $_GMW_LOCATION_COOKIE['gmw-location-filter'] != 'gmw_location') {
		$gmw_location_qs[] = 'type=' . $_BP_CI_COOKIE['gmw-location-filter'];
		$gmw_location_qs[] = 'action=' . $_BP_CI_COOKIE['gmw-location-filter'];

	} else {
		$gmw_location_qs[] = 'type=' . $object;
		$gmw_location_qs[] = 'action=' . $action;
	}

	if( !empty( $_GMW_LOCATION_COOKIE['gmw-location-filter'] ) && '-1' != $_GMW_LOCATION_COOKIE['gmw-location-filter'] && $_GMW_LOCATION_COOKIE['gmw-location-filter'] == 'gmw_location' ) {
		// this is my trick to transsform a filter to a scope !
		$gmw_location_qs[] = 'scope=friends';
			
	}

	if( bp_is_current_component('gmw_location') && bp_is_my_profile() ) {
		$gmw_location_qs[] = 'show_hidden=1';
	}

	if ( !empty( $page ) )
		$gmw_location_qs[]= 'page='.$page;

	$query_string = empty( $gmw_location_qs ) ? '' : join( '&', (array)$gmw_location_qs );

	return apply_filters( 'gmw_location_activity_querystring_filter', $query_string, $object, $action );
}

//add_filter( 'bp_ajax_querystring', 'gmw_fl_location_activity_querystring_filter', 15, 2  );