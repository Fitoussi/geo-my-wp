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
	global $gmw_forms;
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

if ( !function_exists( 'array_replace_recursive' ) ) {
	
	function array_replace_recursive($base, $replacements) {
	    
	    foreach (array_slice(func_get_args(), 1) as $replacements) {
			$bref_stack = array(&$base);
			$head_stack = array($replacements);

			do {
				end($bref_stack);

				$bref = &$bref_stack[key($bref_stack)];
				$head = array_pop($head_stack);

				unset($bref_stack[key($bref_stack)]);

				foreach (array_keys($head) as $key) {
					if (isset($bref[$key]) && is_array($bref[$key]) && is_array($head[$key])) {
						$bref_stack[] = &$bref[$key];
						$head_stack[] = $head[$key];
					} else {
						$bref[$key] = $head[$key];
					}
				}
			} while(count($head_stack));
		}

		return $base;
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
		'mapLoaderElement' 	=> '#gmw-map-loader-'.$mapID,
		'locations'			=> array(),
		'infoWindowType'	=> 'normal',
		'zoomLevel'			=> 13,
		'resizeMapElement'	=> 'gmw-resize-map-trigger-'.$mapID,
		'zoomPosition'		=> false,
		'mapOptions'		=> array(
				'mapTypeId'				 => 'ROADMAP',
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
		'resizeMapControl'	=> false,
		'draggableWindow'	=> false
	);
	
	//merge default args with incoming args
	$gmwMapElements[$mapID] = array_replace_recursive( $defaultArgs, $args );

	//allow plugins modify the map
	$gmwMapElements[$mapID] = apply_filters( 'gmw_map_element', $gmwMapElements[$mapID], $gmwMapElements[$mapID]['form'] );
	$gmwMapElements[$mapID] = apply_filters( "gmw_map_element_{$mapID}", $gmwMapElements[$mapID], $gmwMapElements[$mapID]['form'] );
	$gmwMapElements[$mapID] = apply_filters( "gmw_{$args['prefix']}_map_element", $gmwMapElements[$mapID], $gmwMapElements[$mapID]['form'] );
	
	$temp_locations = array();

	/**
	 * This is "dirty" but temporary security fix. To prevent the map object
	 * from passing sensitive data via the browser console. This fix might slow things 
	 * down when performing a search. However, a proper solution will be provided in a future
	 * update.
	 */
	if ( ! empty( $gmwMapElements[$mapID]['locations'] ) ) {

		foreach ( $gmwMapElements[$mapID]['locations'] as $k => $v ) {

			if ( is_array( $v ) ) {
				$v = ( object ) $v;
			}

			if ( ! empty( $v->ID ) ) {
				$temp_locations[$k]['ID'] = $v->ID;
			}

			if ( ! empty( $v->lat ) ) {
				$temp_locations[$k]['lat'] = $v->lat;
			}
			
			if ( ! empty( $v->long ) ) {
				$temp_locations[$k]['long'] = $v->long;
			}

			if ( ! empty( $v->lng ) ) {
				$temp_locations[$k]['lng'] = $v->lng;
			}
			
			if ( ! empty( $v->mapIcon ) ) {
				$temp_locations[$k]['mapIcon'] = $v->mapIcon;
			} else {
				$temp_locations[$k]['mapIcon'] = '_default.png';
			}

			if ( ! empty( $v->info_window_content ) ) {
				$temp_locations[$k]['info_window_content'] = $v->info_window_content;
			}

			( object ) $temp_locations[$k];
 		}
	} 

	$gmwMapElements[$mapID]['locations'] = $temp_locations;
	
	if ( ! empty( $gmwMapElements[$mapID]['form']['results'] ) ) {
		$gmwMapElements[$mapID]['form']['results'] = $temp_locations;
	}
	
	/***** end of temporary fix *****/

	if ( $return ) {
		return $gmwMapElements[$mapID];
	}
}

/**
 * Enqueue search form/results stylesheet earlier in the <HEAD> tag
 * @param  array $args array key is the form id and value is array of pages ID where the styles need to load early
 * @return void 
 */
function gmw_enqueue_form_styles( $args ) {

	global $gmw_forms;

	$page_id = get_the_ID();

	foreach ( $args as $key => $value ) {
		
		if ( empty( $value ) || ( is_array( $value ) && in_array( $page_id, $value ) ) ) {
			
			$form  = $gmw_forms[$key];	
			$sForm = $form['search_form']['form_template'];

			$folders = apply_filters( 'gmw_search_forms_folder', array(
				'pt'  => array(
						'url'	 => GMW_URL.'/plugins/posts/search-forms/',
						'path'	 => GMW_PATH.'/plugins/posts/search-forms/',
						'custom' => 'posts/search-forms/'
				),		
				'fl'  => array(
						'url'	 => GMW_URL.'/plugins/friends/search-forms/',
						'path'	 => GMW_PATH.'/plugins/friends/search-forms/',
						'custom' => 'friends/search-forms/'
				),
			), $form );

			//Load custom search form and css from child/theme folder
			if ( strpos( $sForm, 'custom_' ) !== false ) {
				$sForm  		   = str_replace( 'custom_', '', $sForm );
				$stylesheet_handle = "gmw-{$form['ID']}-{$form['prefix']}-search-form-{$sForm}";
				$stylesheet_url	   = get_stylesheet_directory_uri()."/geo-my-wp/{$folders[$form['prefix']]['custom']}{$sForm}/css/style.css";
			} else {
				$stylesheet_handle = "gmw-{$form['ID']}-{$form['prefix']}-search-form-{$sForm}";
				$stylesheet_url    = $folders[$form['prefix']]['url'].$sForm.'/css/style.css';
			}

			if ( !wp_style_is( $stylesheet_handle, 'enqueued' ) ) {
				wp_enqueue_style( $stylesheet_handle, $stylesheet_url, array(), GMW_VERSION );
			}

			$sResults = $form['search_results']['results_template'];	
			$rFolders  = apply_filters( 'gmw_search_results_folder', array(
				'pt'  => array(
						'url'	 => GMW_URL.'/plugins/posts/search-results/',
						'path'	 => GMW_PATH.'/plugins/posts/search-results/',
						'custom' => 'posts/search-results/'
				),		
				'fl'  => array(
						'url'	 => GMW_URL.'/plugins/friends/search-results/',
						'path'	 => GMW_PATH.'/plugins/friends/search-results/',
						'custom' => 'friends/search-results/'
				),
			), $form );
			
			//Load custom search form and css from child/theme folder
			if ( strpos( $sResults, 'custom_' ) !== false ) {
			
				$sResults  	 = str_replace( 'custom_', '', $sResults );
				$style_title = "gmw-{$form['ID']}-{$form['prefix']}-search-results-{$sResults}";
				$style_url 	 = get_stylesheet_directory_uri()."/geo-my-wp/{$rFolders[$form['prefix']]['custom']}{$sResults}/css/style.css";
			
			} else {
				$style_title = "gmw-{$form['ID']}-{$form['prefix']}-search-results-{$sResults}";
				$style_url 	 = $rFolders[$form['prefix']]['url'].$sResults.'/css/style.css';
			}
			
			if ( !wp_style_is( $style_title, 'enqueued' ) ) {
				wp_enqueue_style( $style_title, $style_url, array(), GMW_VERSION );
			}
		}
	}
}
?>