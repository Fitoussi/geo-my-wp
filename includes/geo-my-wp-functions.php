<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * Get an option
 *
 * Get a specific gmw option from database.
 *
 * Thanks to pippin williamson for this awesome function
 *
 * @since 2.6.1
 * @return mixed
 */
function gmw_get_option( $group = '', $key ='', $default = false ) {
	global $gmw_options;
	$value = !empty( $gmw_options[$group][$key] ) ? $gmw_options[$group][$key] : $default;
	$value = apply_filters( 'gmw_get_option', $value, $group, $key, $default );
	return apply_filters( 'gmw_get_option_'.$group.$key, $value, $group, $key, $default );
}

/**
 * Get group of options from database
 *
 * @since 2.6.1
 * @return mixed
 */
function gmw_get_options_group( $group = 'gmw_options' ) {
	global $gmw_options;
	
	if ( $group == 'gmw_options' )
		return $gmw_options;

	if ( !empty( $gmw_options[$group] ) ) 
		return $gmw_options[$group];

	return false;
}

/**
 * get GEO my WP form data from db
 * @param  boolean $formID - form ID
 * @return array  specific form if form ID pass otherwise all forms
 */
function gmw_get_form( $formID = false ) {
	$gmw_forms = get_options( 'gmw_forms' );
	return ( !empty( $formID ) ) ? $gmw_forms[$formID] : $gmw_forms;
}

/**
 * Bulild a unit array 
 * @param  srring $unit imperial/metric
 * @return array        array
 */
function gmw_get_units_array( $units = 'imperial' ) {
	if ( $units == "imperial" ) {
		return array( 'radius' => 3959, 'name' => "mi", 'long_name' => 'miles', 'map_units' => "ptm", 'units' => 'imperial' );
	} else {
		return array( 'radius' => 6371, 'name' => "km", 'long_name' => 'kilometers', 'map_units' => 'ptk', 'units' => "metric" );
	}
}

/**
 * Create new map element
 *
 * Pass the arguments to display a map. each element created push into the global map elements.
 * The global map elements pass to the map.js file. The map.js loop through the map elements
 * and display each map based on the aruments entered here.
 *
 * More information about google maps API can be found here - https://developers.google.com/maps/documentation/javascript/reference#MapOptions
 */
function gmw_new_map_element( $args, $return = false ) {

	global $gmwMapElements;
	
	//check if global already set
	if ( empty( $gmwMapElements ) ) {
		$gmwMapElements = array();
	}
	
	$mapID = ( !empty( $args['mapId'] ) ) ? $args['mapId'] : rand( 100, 1000 );

	//default map args
	$defaultArgs = array(
		'mapId' 	 		=> $mapID,
		'mapType'			=> 'na',
		'prefix'			=> 'na',
		'mapElement' 		=> 'gmw-map-'.$mapID,
		'triggerMap'		=> true,
		'form' 		 		=> false,
		'hiddenElement' 	=> '#gmw-map-wrapper-'.$mapID,					
		'mapLoaderElement' 	=> 'gmw-map-loader-'.$mapID,
		'locations'			=> array(),
		'infoWindowType'	=> 'normal',
		'zoomLevel'			=> 13,
		'mapTypeId'			=> 'ROADMAP',
		'resizeMapElement'	=> 'gmw-resize-map-trigger-'.$mapID,
		'zoomPosition'		=> false,
		'mapOptions'		=> array(
				'backgroundColor' 		 => '#f7f7f7',
				'disableDefaultUI' 		 => false,
				'disableDoubleClickZoom' => false,
				'draggable'				 => true,
				'maxZoom'		 		 => null,
				'minZoom'		 		 => null,
				'panControl'	 		 => true,
				'zoomControl'	 		 => true,
				'mapTypeControl' 		 => true,
				'rotateControl'  		 => true,
				'scaleControl'			 => true,
				'scrollwheel'	 		 => true,
				'streetViewControl' 	 => true,
				'styles'				 => null,
				'tilt'					 => null,
		),
		'userPosition'		=> array(
				'lat'		=> '40.758895',
				'lng'		=> '-73.985131',
				'location'	=> false,
				'address' 	=> false,
				'mapIcon'	=> 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
				'iwContent' => null,
				'iwOpen'	=> true
		),
		'markersDisplay'	=> 'normal',
		'markers'			=> array(),
		'infoWindow'		=> null,
		'resizeMapControl'	=> false
	);

	//merge default args with incoming args
	$gmwMapElements[$mapID] = wp_parse_args( $args, $defaultArgs );

	//allow plugins modify the map
	$gmwMapElements[$mapID] = apply_filters( 'gmw_map_element', $gmwMapElements[$mapID], $gmwMapElements[$mapID]['form'] );
	$gmwMapElements[$mapID] = apply_filters( "gmw_map_element_{$mapID}", $gmwMapElements[$mapID], $gmwMapElements[$mapID]['form'] );
	$gmwMapElements[$mapID] = apply_filters( "gmw_{$args['prefix']}_map_element", $gmwMapElements[$mapID], $gmwMapElements[$mapID]['form'] );

	if ( $return ) 
		return $gmwMapElements[$mapID];
}

?>