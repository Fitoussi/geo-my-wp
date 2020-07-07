<?php
/**
 * GEO my WP general function.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get a specific gmw option from database.
 *
 * @since 2.6.1
 *
 * @param  string $group   name of options group.
 *
 * @param  string $key     option name.
 *
 * @param  mixed  $default default value if nothign was found.
 *
 * @return mixed
 */
function gmw_get_option( $group = '', $key = '', $default = false ) {

	$gmw_options = GMW()->options;

	$value = ! empty( $gmw_options[ $group ][ $key ] ) ? $gmw_options[ $group ][ $key ] : $default;
	$value = apply_filters( 'gmw_get_option', $value, $group, $key, $default );

	return apply_filters( 'gmw_get_option_' . $group . $key, $value, $group, $key, $default );
}

/**
 * Get gmw options group from database.
 *
 * @since 2.6.1
 *
 * @param  string $group   name of options group.
 *
 * @return array
 */
function gmw_get_options_group( $group = 'gmw_options' ) {

	$gmw_options = GMW()->options;

	if ( empty( $group ) || 'gmw_options' === $group ) {
		return $gmw_options;
	}

	if ( ! empty( $gmw_options[ $group ] ) ) {
		return $gmw_options[ $group ];
	}

	return false;
}

/**
 * Get blog ID of an object.
 *
 * @param  string $object object.
 *
 * @return blog ID.
 */
function gmw_get_object_blog_id( $object = '' ) {

	if ( '' !== $object && isset( GMW()->locations_blogs[ $object ] ) && absint( GMW()->locations_blogs[ $object ] ) ) {
		return GMW()->locations_blogs[ $object ];
	}

	return false;
}

/**
 * Get blog ID
 *
 * @param  string $object object.
 *
 * @return [type] [description]
 */
function gmw_get_blog_id( $object = '' ) {

	if ( is_multisite() ) {

		if ( '' !== $object ) {

			$loc_blog = gmw_get_object_blog_id( $object );

			if ( false !== $loc_blog ) {
				return $loc_blog;
			}
		}

		global $blog_id;

		return $blog_id;

	} else {
		return 1;
	}
}

/**
 * Get specific form data
 *
 * @param  boolean $id form ID.
 *
 * @return array      Form data
 */
function gmw_get_form( $id = false ) {
	return GMW_Forms_Helper::get_form( $id );
}

/**
 * Get specific form data
 *
 * @return array GEo my WP forms data
 */
function gmw_get_forms() {
	return GMW_Forms_Helper::get_forms();
}

/**
 * Get addons ( extensions ) data
 *
 * @return array  data of all loaded addons
 */
function gmw_get_object_types() {
	return GMW()->object_types;
}

/**
 * Verify if input is a proper numeric ID.
 *
 * @param  integer $id the id to verify.
 *
 * @return boolean.
 *
 * @since 3.2
 */
function gmw_verify_id( $id = 0 ) {
	return GMW_Location::verify_id( $id );
}

/**
 * Check if add-on is active
 *
 * @param  string $addon slug/name of the addon.
 *
 * @return boolean true/false
 */
function gmw_is_addon_active( $addon = '' ) {
	if ( ! empty( GMW()->addons_status[ $addon ] ) && 'active' === GMW()->addons_status[ $addon ] && ! isset( $_POST['gmw_premium_license'] ) ) { // WPCS: CSRF ok.
		return true;
	} else {
		return false;
	}
}

/**
 * Get addon ( extension ) status.
 *
 * @param string $addon addon slug.
 *
 * @return string  the addon status.
 */
function gmw_get_addon_status( $addon = '' ) {
	return ( '' !== $addon && ! empty( GMW()->addons_status[ $addon ] ) ) ? GMW()->addons_status[ $addon ] : 'inactive';
}

/**
 * Get addons ( extensions ) status
 *
 * @return array  data of all loaded addons
 */
function gmw_get_addons_status() {
	return GMW()->addons_status;
}

/**
 * Get addons ( extensions ) data.
 *
 * @param boolean $get_licenses get also licenses data?.
 *
 * @return array  data of all loaded addons
 */
function gmw_get_addons_data( $get_licenses = false ) {
	return ( IS_ADMIN && $get_licenses ) ? array_merge_recursive( GMW()->addons, GMW()->licenses ) : GMW()->addons;
}

/**
 * Get addon ( extension ) data
 *
 * @param  string  $addon slug/name of the addon to pull its data.
 *
 * @param  string  $var   specific data value.
 *
 * @param  boolean $get_license_data get also license data?.
 *
 * @return array  add-on's data
 */
function gmw_get_addon_data( $addon = '', $var = '', $get_license_data = false ) {

	if ( ! empty( GMW()->addons[ $addon ] ) ) {

		$addon = GMW()->addons[ $addon ];

		if ( IS_ADMIN && $get_license_data ) {

			$licenses = GMW()->licenses[ $addon ];

			if ( ! empty( $licenses ) ) {
				$addon = array_merge( $addons, $licenses );
			}
		}

		if ( '' !== $var ) {

			if ( isset( $addon[ $var ] ) ) {

				return $addon[ $var ];

			} else {

				return false;
			}
		} else {

			return $addon;
		}
	} else {
		return false;
	}
}

/**
 * Get addon ( extension ) data
 *
 * @param  string $addon slug/name of the addon to pull its data.
 *
 * @param  string $var   specific data value.
 *
 * @return array  add-on's data
 */
function gmw_get_addon_license_data( $addon = '', $var = '' ) {

	if ( ! empty( GMW()->licenses[ $addon ] ) ) {

		$licenses = GMW()->licenses;

		if ( '' !== $var ) {
			if ( isset( $licenses[ $addon ][ $var ] ) ) {
				return $licenses[ $addon ][ $var ];
			} else {
				return false;
			}
		} else {
			return $licenses[ $addon ];
		}
	} else {
		return false;
	}
}

/**
 * Get object type by object.
 *
 * @param  string $object object.
 *
 * @return [type]          [description]
 */
function gmw_get_object_type( $object = false ) {
	return ( $object && isset( GMW()->object_types[ $object ] ) ) ? GMW()->object_types[ $object ] : false;
}

/**
 * Update addon status
 *
 * @param  string $addon    addon slug.
 *
 * @param  string $status  new status.
 *
 * @param  mixed  $details any status details.
 *
 * @return [type]           [description]
 */
function gmw_update_addon_status( $addon = '', $status = 'active', $details = false ) {

	if ( empty( $addon ) || ! in_array( $status, array( 'active', 'inactive', 'disabled' ), true ) ) {
		return;
	}

	// get addons data from database.
	$addons_status = get_option( 'gmw_addons_status' );

	if ( empty( $addons_status ) ) {
		$addons_status = array();
	}

	// update addon data.
	$addons_status[ $addon ] = $status;

	// save new data in database.
	update_option( 'gmw_addons_status', $addons_status );

	// update status in global.
	GMW()->addons_status                      = $addons_status;
	GMW()->addons[ $addon ]['status']         = $status;
	GMW()->addons[ $addon ]['status_details'] = $details;

	update_option( 'gmw_addons_data', GMW()->addons );

	return GMW()->addons[ $addon ];
}

/**
 * Update addon data
 *
 * @param  array $addon [description].
 *
 * @return [type]        [description]
 */
function gmw_update_addon_data( $addon = array() ) {

	if ( empty( $addon ) ) {
		return;
	}

	$addons_data = get_option( 'gmw_addons_data' );

	if ( empty( $addons_data ) ) {
		$addons_data = array();
	}

	$addons_data[ $addon['slug'] ] = $addon;

	update_option( 'gmw_addons_data', $addons_data );

	GMW()->addons = $addons_data;
}

/**
 * Generate warning or error message.
 *
 * @since 3.2
 *
 * @param  string $message the message.
 *
 * @param  [type] $type    error type.
 */
function gmw_trigger_error( $message = '', $type = E_USER_NOTICE ) {

	// get debugging data.
	$debug   = debug_backtrace();
	$message = esc_html( $message );

	// verify that debuggin data exist to generate custom error message.
	if ( ! empty( $debug[0] ) ) {

		// show errors only if debug is set to true.
		if ( WP_DEBUG !== true && WP_DEBUG !== 'true' ) {
			return;
		}

		$debug = $debug[0];
		$file  = ! empty( $debug['file'] ) ? esc_html( $debug['file'] ) : 'file name is missing';
		$line  = ! empty( $debug['line'] ) ? esc_html( $debug['line'] ) : 'line nunmber is missing';

		// generate full error message.
		$full_message = "{$message} In <b>{$file}</b> on line <b>{$line}</b>";

		// output message.
		switch ( $type ) {

			case E_USER_ERROR:
				echo "<br><b>Fatal error:</b> {$full_message}<br />\n"; // WPCS: XSS ok.
				exit( 1 );
			break;

			case E_USER_WARNING:
				echo "<br><b>Warning:</b> {$full_message}<br />\n"; // WPCS: XSS ok.
				break;

			case E_USER_NOTICE:
				echo "<br><b>Notice:</b> {$full_message}<br />\n"; // WPCS: XSS ok.
				break;

			default:
				echo "<br><b>Unknown error type:</b> {$full_message}<br />\n"; // WPCS: XSS ok.
				break;
		}

		// otherwise, use built in function.
	} else {
		trigger_error( $message, $type ); // WPCS: XSS ok.
	}
}

/**
 * Get URL prefix
 *
 * @return [type] [description]
 */
function gmw_get_url_prefix() {
	return GMW()->url_prefix;
}

/**
 * Get user Location prefix.
 *
 * @return [type] [description]
 */
function gmw_get_ulc_prefix() {
	return GMW()->ulc_prefix;
}

/**
 * Get the user current location
 *
 * @return OBJECT of the user location
 */
function gmw_get_user_current_location() {
	return GMW_Helper::get_user_current_location();
}

/**
 * Get the user current coords
 *
 * @return ARRAY of the user current coordinates
 */
function gmw_get_user_current_coords() {

	$ul = GMW_Helper::get_user_current_location();

	if ( empty( $ul ) ) {
		return false;
	}

	return array(
		'lat' => $ul->lat,
		'lng' => $ul->lng,
	);
}

/**
 * Get the user current address
 *
 * @return ARRAY of the user current address
 */
function gmw_get_user_current_address() {

	$ul = GMW_Helper::get_user_current_location();

	if ( empty( $ul ) ) {
		return false;
	}

	return $ul->formatted_address;
}

/**
 * Processes all GMW actions sent via POST and GET by looking for the 'gmw_action'
 * request and running do_action() to call the function
 *
 * @since 2.5
 * @return void
 */
function gmw_process_actions() {

	if ( isset( $_POST['gmw_action'] ) ) { // WPCS: CSRF ok.
		do_action( 'gmw_' . wp_unslash( $_POST['gmw_action'] ), $_POST ); // WPCS: CSRF ok, sanitization ok.
	}

	if ( isset( $_GET['gmw_action'] ) ) { // WPCS: CSRF ok.
		do_action( 'gmw_' . wp_unslash( $_GET['gmw_action'] ), $_GET ); // WPCS: CSRF ok, sanitization ok.
	}
}

if ( IS_ADMIN ) {
	add_action( 'admin_init', 'gmw_process_actions' );
} else {
	add_action( 'init', 'gmw_process_actions' );
}

/**
 * Get form submitted values.
 *
 * @param  string $prefix       gmw URL param prefix.
 *
 * @param  string $query_string [description].
 *
 * @return [type]               [description]
 */
function gmw_get_form_values( $prefix = '', $query_string = '' ) {

	$output = array();

	if ( ! empty( $query_string ) ) {

		$query_string = '' === $prefix ? $query_string : str_replace( $prefix, '', $query_string );

		parse_str( $query_string, $output );

		// for some case where address is not an array.
		if ( isset( $output['address'] ) && ! is_array( $output['address'] ) ) {
			$output['address'] = urldecode( $output['address'] );
		}
	}

	if ( ! empty( $output['sortby'] ) ) {
		$output['orderby'] = $output['sortby'];
	}

	return $output;
}

/**
 * GMW Function - Covert object to array
 *
 * @param  array $data the array to convert.
 *
 * @since  2.5
 *
 * @return Array/multidimensional array
 */
function gmw_object_to_array( $data ) {

	if ( is_array( $data ) || is_object( $data ) ) {

		$result = array();

		foreach ( $data as $key => $value ) {
			$result[ $key ] = gmw_object_to_array( $value );
		}

		return $result;
	}

	return $data;
}

/**
 * Sort array by priority. For Settings and forms pages.
 *
 * @param  [type] $a [description].
 * @param  [type] $b [description].
 *
 * @return [type]    [description]
 */
function gmw_sort_by_priority( $a, $b ) {

	$a['priority'] = ( ! empty( $a['priority'] ) ) ? $a['priority'] : 99;
	$b['priority'] = ( ! empty( $b['priority'] ) ) ? $b['priority'] : 99;

	if ( $a['priority'] === $b['priority'] ) {
		return 0;
	}

	return $a['priority'] - $b['priority'];
}

/**
 * Convert object to array
 *
 * @param  object $object object to convert.
 *
 * @param  [type] $output ARRAY_A || ARRAY_N.
 *
 * @return array
 */
function gmw_to_array( $object, $output = ARRAY_A ) {

	if ( ARRAY_A === $output ) {

		return (array) $object;

	} elseif ( ARRAY_N === $output ) {

		return array_values( (array) $object );

	} else {
		return $object;
	}
}

/**
 * Bulild a unit array
 *
 * @param  string $units imperial/metric.
 *
 * @return array        array
 */
function gmw_get_units_array( $units = 'imperial' ) {

	if ( 'imperial' === $units ) {
		return array(
			'radius'    => 3959,
			'name'      => 'mi',
			'long_name' => 'miles',
			'map_units' => 'ptm',
			'units'     => 'imperial',
		);
	} else {
		return array(
			'radius'    => 6371,
			'name'      => 'km',
			'long_name' => 'kilometers',
			'map_units' => 'ptk',
			'units'     => 'metric',
		);
	}
}

/**
 * Calculate the distance between two points
 *
 * @param  [type] $start_lat latitude of start point.
 * @param  [type] $start_lng longitude of start point.
 * @param  [type] $end_lat   latitude of end point.
 * @param  [type] $end_lng   longitude of end point.
 * @param  string $units     m for miles k for kilometers.
 *
 * @since 3.0
 *
 * @return [type]            [description]
 */
function gmw_calculate_distance( $start_lat, $start_lng, $end_lat, $end_lng, $units = 'm' ) {

	$rad      = M_PI / 180;
	$radius   = in_array( $units, array( 'k', 'metric', 'kilometers', 'K', 'kilometer' ), true ) ? 6371 : 3959;
	$distance = acos( sin( $end_lat * $rad ) * sin( $start_lat * $rad ) + cos( $end_lat * $rad ) * cos( $start_lat * $rad ) * cos( $end_lng * $rad - $start_lng * $rad ) ) * $radius;

	return round( $distance, 2 );
}

/**
 * Get labels
 *
 * Most of the labels of the forms are set below.
 * it makes it easier to manage and it is now possible to modify a single or multiple
 * labels using the filter provided instead of using the translation files.
 *
 * You can create a custom function in the functions.php file of your theme and hook it using the filter gmw_shortcode_set_labels.
 * You should check for the $form['ID'] in your custom function to make sure the function apply only for the required forms.
 *
 * @since 2.5
 */

/*
Function gmw_get_labels( $form = array() ) {

	$labels = array(
		'search_form'		=> array(
			'radius_within'		=> __( 'Within',   'geo-my-wp' ),
			'kilometers'		=> __( 'Kilometers',      'geo-my-wp' ),
			'miles'				=> __( 'Miles', 'geo-my-wp' ),
			'show_options'		=> __( 'Advanced options', 'geo-my-wp' ),
			'select_groups'		=> __( 'Select Groups', 'geo-my-wp' ),
			'no_groups'			=> __( 'No Groups', 'geo-my-wp' ),
			'all_groups'		=> __( 'All Groups', 'geo-my-wp' )
		),
		'pagination'		=> array(
			'prev'  => __( 'Prev', 	'geo-my-wp' ),
			'next'  => __( 'Next', 	'geo-my-wp' ),
		),
		'search_results'	=> array(
			'distance'          => __( 'Distance: ', 'geo-my-wp' ),
			'driving_distance'	=> __( 'Driving distance:', 'geo-my-wp' ),
			'address'           => __( 'Address: ',  'geo-my-wp' ),
			'formatted_address' => __( 'Address: ',  'geo-my-wp' ),
			'directions'        => __( 'Get directions', 'geo-my-wp' ),
			'your_location'     => __( 'Your Location ', 'geo-my-wp' ),
			'not_avaliable'		=> __( 'N/A', 'geo-my-wp' ),
			'read_more'			=> __( 'Read more',	'geo-my-wp' ),
			'contact_info'		=> array(
				'phone'	  		=> __( 'Phone: ', 'geo-my-wp' ),
				'fax'	  		=> __( 'Fax: ', 'geo-my-wp' ),
				'email'	  		=> __( 'Email: ', 'geo-my-wp' ),
				'website' 		=> __( 'website: ', 'geo-my-wp' ),
				'na'	  		=> __( 'N/A', 'geo-my-wp' ),
				'contact_info'	=> __( 'Contact Information','geo-my-wp' ),
			),
			'opening_hours'			=> __( 'Opening Hours' ),
			'member_info'			=> __( 'Member Information', 'geo-my-wp' ),
			'google_map_directions' => __( 'Show directions on Google Map', 'geo-my-wp' ),
			'active_since'			=> __( 'active %s', 'geo-my-wp' ),
			'per_page'				=> __( 'per page', 'geo-my-wp' ),
		),
		'results_message' 	=> array(
				'showing'
		),
		'info_window'		=> array(
			'address'  			 => __( 'Address: ', 'geo-my-wp' ),
			'directions'         => __( 'Get Directions', 'geo-my-wp' ),
			'formatted_address'  => __( 'Formatted Address: ', 'geo-my-wp' ),
			'distance' 			 => __( 'Distance: ', 'geo-my-wp' ),
			'phone'	   			 => __( 'Phone: ', 'geo-my-wp' ),
			'fax'	   			 => __( 'Fax: ', 'geo-my-wp' ),
			'email'	   			 => __( 'Email: ', 'geo-my-wp' ),
			'website'  			 => __( 'website: ', 'geo-my-wp' ),
			'na'	   			 => __( 'N/A', 'geo-my-wp' ),
			'your_location'		 => __( 'Your Location ', 'geo-my-wp' ),
			'contact_info'		 => __( 'Contact Information','geo-my-wp' ),
			'read_more'			 => __( 'Read more', 'geo-my-wp' ),
			'member_info'	     => __( 'Member Information', 'geo-my-wp' )
		)
	);

	//modify the labels
	$labels = apply_filters( 'gmw_set_labels', $labels, $form );

	if ( ! empty( $form['ID'] ) ) {
		$labels = apply_filters( "gmw_set_labels_{$form['ID']}", $labels, $form );
	}

	return $labels;
}
*/

/**
 * Get template file and its stylesheet
 *
 * @since 3.0
 *
 * @param  array $args array(
 *  $component     => the slug of the add-on/component which the template file belongs to.
 *  $addon         => the slug of the addon when not the original addon of the component.
 *  $folder_name   =>  folder name ( search-forms, search-results... ).
 *  $template_name => template name
 * );.
 *
 * @return array of templates.
 */
function gmw_get_templates( $args = array() ) {
	return GMW_Helper::get_templates( $args );
}

/**
 * Get search form template
 *
 * @param  string $component [description].
 *
 * @param  string $addon     [description].
 *
 * @return [type]
 */
function gmw_get_search_form_templates( $component = 'posts_locator', $addon = '' ) {

	$args = array(
		'component'   => $component,
		'addon'       => $addon,
		'folder_name' => 'search-forms',
	);

	return gmw_get_templates( $args );
}

/**
 * Get search results template
 *
 * @param  string $component [description].
 *
 * @param  string $addon     [description].
 *
 * @return [type]                [description]
 */
function gmw_get_search_results_templates( $component = 'posts_locator', $addon = '' ) {

	$args = array(
		'component'   => $component,
		'addon'       => $addon,
		'folder_name' => 'search-results',
	);

	return gmw_get_templates( $args );
}

/**
 * Get info-window template
 *
 * @param  string $component  component.
 *
 * @param  string $iw_type    info window type.
 *
 * @param  string $addon      addon slug.
 *
 * @return [type]                [description]
 */
function gmw_get_info_window_templates( $component = 'posts_locator', $iw_type = 'popup', $addon = '' ) {

	$args = array(
		'component'   => $component,
		'addon'       => $addon,
		'folder_name' => 'info-window',
		'iw_type'     => $iw_type,
	);

	return gmw_get_templates( $args );
}

/**
 * Get template file and its stylesheet
 *
 * @since 3.0
 *
 * @param array $args array(
 *   'component'        => slug of the addon / component the template belongs to.
 *   'addon'            => use this if the component exists inside another add-on. ex. Global Maps which uses different components.
 *   'folder_name'      => folder name ( search-forms, search-results... ).
 *   'template_name'    => template name ( default, gray... ).
 *   'iw_type'          => info window type ( popup, infobox... ). Folder name must be set to info-window.
 *   'file_name'        => file name ( content.php ... ).
 *   'include_template' => true || false to include or return file.
 * );.
 *
 * @return array of templates
 */
function gmw_get_template( $args = array() ) {
	return GMW_Helper::get_template( $args );
}

/**
 * Get search form template
 *
 * @param  string  $component     component.
 * @param  string  $template_name tempalte name.
 * @param  string  $addon         addon slug.
 * @param  boolean $include       include or return the template file.
 *
 * @return [type]                [description]
 */
function gmw_get_search_form_template( $component = 'posts_locator', $template_name = 'default', $addon = '', $include = false ) {

	$args = array(
		'component'        => $component,
		'addon'            => $addon,
		'folder_name'      => 'search-forms',
		'template_name'    => $template_name,
		'include_template' => $include,
	);

	return gmw_get_template( $args );
}

/**
 * Get search results template
 *
 * @param  string  $component     component.
 * @param  string  $template_name tempalte name.
 * @param  string  $addon         addon slug.
 * @param  string  $file_name     file name.
 * @param  boolean $include       include or return the file.
 *
 * @return [type]                [description]
 */
function gmw_get_search_results_template( $component = 'posts_locator', $template_name = 'default', $addon = '', $file_name = 'content.php', $include = false ) {

	$args = array(
		'component'        => $component,
		'addon'            => $addon,
		'folder_name'      => 'search-results',
		'template_name'    => $template_name,
		'file_name'        => $file_name,
		'include_template' => $include,
	);

	return gmw_get_template( $args );
}

/**
 * Get info-window template
 *
 * @param  string  $component     component.
 * @param  string  $iw_type       info window type.
 * @param  string  $template_name tempalte name.
 * @param  string  $addon         addon slug.
 * @param  boolean $include       include or return the file.
 *
 * @return [type]                [description]
 */
function gmw_get_info_window_template( $component = 'posts_locator', $iw_type = 'popup', $template_name = 'default', $addon = '', $include = false ) {

	$args = array(
		'component'        => $component,
		'addon'            => $addon,
		'folder_name'      => 'info-window',
		'iw_type'          => $iw_type,
		'template_name'    => $template_name,
		'include_template' => $include,
	);

	return gmw_get_template( $args );
}

/**
 * Element toggle button
 *
 * Will usually be used with Popup info-window
 *
 * @param  array $args [description].
 *
 * @return [type]       [description]
 */
function gmw_get_element_toggle_button( $args = array() ) {

	$defaults = array(
		'id'           => 0,
		'show_icon'    => 'gmw-icon-arrow-down',
		'hide_icon'    => 'gmw-icon-arrow-up',
		'target'       => '#gmw-popup-info-window',
		'animation'    => 'height',
		'open_length'  => '100%',
		'close_length' => '35px',
		'duration'     => '200',
		'init_visible' => true,
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_element_toggle_button_args', $args );

	if ( $args['init_visible'] ) {
		$state = 'expand';
		$icon  = $args['hide_icon'];
	} else {
		$state = 'collapse';
		$icon  = $args['show_icon'];
	}

	$id = ! empty( $args['id'] ) ? 'id="gmw-element-toggle-button-' . esc_attr( $args['id'] ) . '"' : '';

	return '<span ' . $id . ' class="gmw-element-toggle-button ' . esc_attr( $icon ) . '" data-state="' . $state . '" data-target="' . esc_attr( $args['target'] ) . '" data-show_icon="' . esc_attr( $args['show_icon'] ) . '" data-hide_icon="' . esc_attr( $args['hide_icon'] ) . '" data-animation="' . esc_attr( $args['animation'] ) . '" data-open_length="' . esc_attr( $args['open_length'] ) . '" data-close_length="' . esc_attr( $args['close_length'] ) . '" data-duration="' . esc_attr( $args['duration'] ) . '"></span>';
}

/**
 * Display toggle button in info window
 *
 * @param  array $args [description].
 */
function gmw_element_toggle_button( $args = array() ) {
	echo gmw_get_element_toggle_button( $args ); // WPCS: XSS ok.
}

/**
 * Toggle left element.
 *
 * @param  string $target [description].
 *
 * @param  array  $length [description].
 */
function gmw_left_element_toggle_button( $target = '', $length = '-300px' ) {

	echo gmw_get_element_toggle_button(
		array(
			'target'       => $target,
			'animation'    => 'transform',
			'open_length'  => 'translatex(0px)',
			'close_length' => 'translatex(' . esc_attr( $length ) . ')',
			'hide_icon'    => 'gmw-icon-arrow-left',
			'show_icon'    => 'gmw-icon-arrow-right',
		)
	); // WPCS: XSS ok.
}

/**
 * Toggle left element
 *
 * @param  string $target [description].
 *
 * @param  array  $length [description].
 */
function gmw_right_element_toggle_button( $target = '', $length = '300px' ) {

	echo gmw_get_element_toggle_button(
		array(
			'target'       => $target,
			'animation'    => 'transform',
			'open_length'  => 'translatex(0px)',
			'close_length' => 'translatex(' . esc_attr( $length ) . ')',
			'hide_icon'    => 'gmw-icon-arrow-right',
			'show_icon'    => 'gmw-icon-arrow-left',
		)
	); // WPCS: XSS ok.
}

/**
 * Toggle button for left side info-window
 */
function gmw_left_window_toggle_button() {

	echo gmw_get_element_toggle_button(
		array(
			'animation'    => 'width',
			'open_length'  => '100%',
			'close_length' => '30px',
			'hide_icon'    => 'gmw-icon-arrow-left',
			'show_icon'    => 'gmw-icon-arrow-right',
		)
	); // WPCS: XSS ok.
}

/**
 * Toggle button for right side info-window
 */
function gmw_right_window_toggle_button() {

	echo gmw_get_element_toggle_button(
		array(
			'animation'    => 'width',
			'open_length'  => '100%',
			'close_length' => '30px',
			'hide_icon'    => 'gmw-icon-arrow-right',
			'show_icon'    => 'gmw-icon-arrow-left',
		)
	); // WPCS: XSS ok.
}

/**
 * Get close button for info window.
 *
 * @param string $icon the font icon.
 */
function gmw_get_element_close_button( $icon = 'gmw-icon-cancel-circled' ) {
	return '<span class="iw-close-button ' . esc_attr( $icon ) . '"></span>';
}

/**
 * Output button for info window.
 *
 * @param string $icon the font icon.
 */
function gmw_element_close_button( $icon = 'gmw-icon-cancel-circled' ) {
	echo gmw_get_element_close_button( $icon ); // WPCS: XSS ok.
}

/**
 * Get info window dragging element
 *
 * @param array $args array of arguments.
 *
 * @return HTML element.
 */
function gmw_get_element_dragging_handle( $args = array() ) {

	$defaults = array(
		'icon'        => 'gmw-icon-sort',
		'target'      => '#gmw-popup-info-window',
		'containment' => 'window',
		'handle'      => '',
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_draggable_button_args', $args );

	if ( '' !== $args['handle'] ) {
		$display = 'style="display:none"';
		$remote  = ' remote-toggle';
	} else {
		$display = '';
		$remote  = '';
	}

	return '<span class="gmw-draggable ' . esc_attr( $args['icon'] ) . $remote . '" data-draggable="' . esc_attr( $args['target'] ) . '" data-containment="' . esc_attr( $args['containment'] ) . '" data-handle="' . esc_attr( $args['handle'] ) . '" ' . $display . '></span>';
}

/**
 * Output info window dragging element
 *
 * @param array $args array of arguments.
 */
function gmw_element_dragging_handle( $args = array() ) {
	echo gmw_get_element_dragging_handle( $args ); // WPCS: XSS ok.
}

/**
 * Create new map element
 *
 * Pass the arguments to display a map. Each element created is pushed into the global map elements.
 *
 * The global map elements pass to the map.js file. The map.js loop through the map elements
 *
 * and generates each map based on the arguments passed to the function.
 *
 * More information about google maps API can be found here - https://developers.google.com/maps/documentation/javascript/reference#MapOptions.
 *
 * @param array $map_args      array of map arguments.
 *
 * @param array $map_options   map options.
 *
 * @param array $locations     array of locations object.
 *
 * @param array $user_position the user's location.
 *
 * @param array $form          gmw form.
 */
function gmw_get_map_object( $map_args = array(), $map_options = array(), $locations = array(), $user_position = array(), $form = array() ) {
	return GMW_Maps_API::get_map_args( $map_args, $map_options, $locations, $user_position, $form );
}

/**
 * Get map element
 *
 * @param array $map_args      array of map arguments.
 *
 * @param array $map_options   map options.
 *
 * @param array $locations     array of locations object.
 *
 * @param array $user_position the user's location.
 *
 * @param array $form          gmw form.
 *
 * @return [type]                [description]
 */
function gmw_get_map( $map_args = array(), $map_options = array(), $locations = array(), $user_position = array(), $form = array() ) {
	return GMW_Maps_API::get_map( $map_args, $map_options, $locations, $user_position, $form );
}

/**
 * Get map element
 *
 * @param  array $args array of arguments.
 *
 * @return [type]       [description]
 */
function gmw_get_map_element( $args = array() ) {
	return GMW_Maps_API::get_map_element( $args );
}

/**
 * Get directions system form
 *
 * @param  array $args [description].
 *
 * @return [type]       [description]
 */
function gmw_get_directions_form( $args = array() ) {
	return GMW_Maps_API::get_directions_form( $args );
}

/**
 * Get directions system panel
 *
 * @param integer $id panel ID.
 *
 * @return HTML element.
 */
function gmw_get_directions_panel( $id = 0 ) {
	return GMW_Maps_API::get_directions_panel( $id );
}

/**
 * Get directions system
 *
 * @param  array $args array of arguments.
 *
 * @return [type]       [description]
 */
function gmw_get_directions_system( $args = array() ) {
	return GMW_Maps_API::get_directions_system( $args );
}

/**
 * Enqueue search form/results stylesheet earlier in the <HEAD> tag
 *
 * By default, since GEO my WP uses shortcodes to display its forms, search forms and search results stylesheet loads outside the <head> tag.
 * This can cause the search forms / results look out of styling for a short moment on page load. As well it can cause HTML validation error.
 *
 * You can use this function to overcome this issue. Pass an array of the form id and the pages which you want to load the stylesheet early in the head.
 *
 * @param  array $args array(
 *     'form_id'     => the id of the form to load its stylesheets,
 *     'pages'        => array of pages ID where you'd like to load the form's stylesheets. Empty array to load on every page,
 *     'folders_name' => array of the folders name to load early. Right now the function supports search-forms and search-results.
 * );.
 *
 * @return void
 */
function gmw_enqueue_form_styles( $args = array(
	'form_id'      => 0,
	'pages'        => array(),
	'folders_name' => array( 'search-forms', 'search-results' ),
) ) {

	$page_id = get_the_ID();
	$form    = gmw_get_form( $args['form_id'] );

	// abort if form doesnt exist.
	if ( empty( $form ) ) {
		return;
	}

	if ( empty( $args['pages'] ) || ( is_array( $args['pages'] ) && in_array( $page_id, $args['pages'], true ) ) ) {

		// get the addon slug.
		$addon_data = gmw_get_addon_data( $form['slug'] );

		if ( in_array( 'search-forms', $args['folders_name'], true ) ) {

			$template = $form['search_form']['form_template'];

			// Get custom template and css from child/theme folder.
			if ( strpos( $template, 'custom_' ) !== false ) {

				$template          = str_replace( 'custom_', '', $template );
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-forms-custom-{$template}";
				$stylesheet_uri    = get_stylesheet_directory_uri() . "/geo-my-wp/{$addon_data['templates_folder']}/search-forms/{$template}/css/style.css";

				// load template files from plugin's folder.
			} else {
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-forms-{$template}";
				$stylesheet_uri    = $addon_data['plugin_url'] . "/templates/search-forms/{$template}/css/style.css";
			}

			if ( ! wp_style_is( $stylesheet_handle, 'enqueued' ) ) {
				wp_enqueue_style( $stylesheet_handle, $stylesheet_uri, array(), GMW_VERSION );
			}
		}

		if ( in_array( 'search-results', $args['folders_name'], true ) ) {

			$template = $form['search_results']['results_template'];

			// Get custom template and css from child/theme folder.
			if ( strpos( $template, 'custom_' ) !== false ) {

				$template          = str_replace( 'custom_', '', $template );
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-results-custom-{$template}";
				$stylesheet_uri    = get_stylesheet_directory_uri() . "/geo-my-wp/{$addon_data['templates_folder']}/search-results/{$template}/css/style.css";

				// load template files from plugin's folder.
			} else {
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-results-{$template}";
				$stylesheet_uri    = $addon_data['plugin_url'] . "/templates/search-results/{$template}/css/style.css";
			}

			if ( ! wp_style_is( $stylesheet_handle, 'enqueued' ) ) {
				wp_enqueue_style( $stylesheet_handle, $stylesheet_uri, array(), GMW_VERSION );
			}
		}
	}
}

/**
 * Ajax info window loader
 *
 * This is a global function that can be used to generate
 *
 * info-window via AJAX.
 *
 * The function triggered using the hooks below.
 *
 * This means that the ajax callback function should be gmw_info_window_init.
 */
function gmw_ajax_info_window_init() {

	/**
	 * We used to pass the form object via the map_args and return it
	 * via info_window ajax. This seems unessacery so we now pass the form ID
	 * only and get the form using a function.
	 * We leave this here for now in case for some reason we need the
	 * additional data generated to the form during the search query process.
	 * $gmw = $_POST['form'];
	 */
	if ( isset( $_POST['location'] ) ) {
		$location = is_object( $_POST['location'] ) ? $_POST['location'] : (object) $_POST['location']; // WPCS: CSRF ok, sanitization ok.
	} else {
		$location = new stdClass();
	}

	if ( ! empty( $_POST['form'] ) ) {

		$gmw = $_POST['form']; // WPCS: CSRF ok, sanitization ok.

	} elseif ( ! empty( $_POST['form_id'] ) ) {

		$gmw = gmw_get_form( $_POST['form_id'] ); // WPCS: CSRF ok, sanitization ok.

	} else {

		gmw_trigger_error( 'Info-window form ID missing' );

		die( 'There was a problem loading this content.' );
	}

	// include info-window template functions.
	include_once GMW_PATH . '/includes/template-functions/gmw-info-window-template-functions.php';

	// modify the location object.
	$location = apply_filters( 'gmw_location_pre_ajax_info_window_init', $location, $gmw );
	$location = apply_filters( 'gmw_' . $gmw['prefix'] . '_location_pre_ajax_info_window_init', $location, $gmw );

	// execute custom info-window functions.
	do_action( 'gmw_' . $gmw['prefix'] . '_ajax_info_window_init', $location, $gmw );
	do_action( 'gmw_ajax_info_window_init', $location, $gmw );

	die();
}
add_action( 'wp_ajax_gmw_info_window_init', 'gmw_ajax_info_window_init' );
add_action( 'wp_ajax_nopriv_gmw_info_window_init', 'gmw_ajax_info_window_init' );

/**
 * Info window content
 *
 * Generate the information that will be displayed in the info-window that opens when clicking on a map marker.
 *
 * The information can be modifyed via the filter below
 *
 * @param object $location the location object.
 *
 * @param array  $args     array of arguments.
 *
 * @param array  $gmw      gmw form.
 *
 * @return [type]    [description]
 */
function gmw_get_info_window_content( $location, $args = array(), $gmw = array() ) {
	return GMW_Maps_API::get_info_window_content( $location, $args, $gmw );
}

/**
 * Array of countries that can be used for select dropdown.
 *
 * @param string $first pass label as the first option. False for no first option.
 *
 * @return array of countries
 */
function gmw_get_countries_list_array( $first = false ) {

	$countries = array(
		'0'  => '',
		'AF' => 'Afghanistan',
		'AX' => 'Aland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua And Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia And Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CD' => 'Congo, Democratic Republic',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Cote D\'Ivoire',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island & Mcdonald Islands',
		'VA' => 'Holy See (Vatican City State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran, Islamic Republic Of',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle Of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KR' => 'Korea',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia, Federated States Of',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory, Occupied',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barthelemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts And Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin',
		'PM' => 'Saint Pierre And Miquelon',
		'VC' => 'Saint Vincent And Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome And Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia And Sandwich Isl.',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard And Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad And Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks And Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Viet Nam',
		'VG' => 'Virgin Islands, British',
		'VI' => 'Virgin Islands, U.S.',
		'WF' => 'Wallis And Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
	);

	if ( empty( $first ) ) {
		unset( $countries[0] );
	} else {
		$countries[0] = $first;
	}

	return $countries;
}

/**
 * Array of countries.
 *
 * @return [type] [description]
 */
function gmw_get_countries_array() {

	return array(
		array(
			'code' => 'US',
			'name' => 'United States',
		),
		array(
			'code' => 'CA',
			'name' => 'Canada',
		),
		array(
			'code' => 'AU',
			'name' => 'Australia',
		),
		array(
			'code' => 'FR',
			'name' => 'France',
		),
		array(
			'code' => 'DE',
			'name' => 'Germany',
		),
		array(
			'code' => 'IS',
			'name' => 'Iceland',
		),
		array(
			'code' => 'IE',
			'name' => 'Ireland',
		),
		array(
			'code' => 'IT',
			'name' => 'Italy',
		),
		array(
			'code' => 'ES',
			'name' => 'Spain',
		),
		array(
			'code' => 'SE',
			'name' => 'Sweden',
		),
		array(
			'code' => 'AT',
			'name' => 'Austria',
		),
		array(
			'code' => 'BE',
			'name' => 'Belgium',
		),
		array(
			'code' => 'FI',
			'name' => 'Finland',
		),
		array(
			'code' => 'CZ',
			'name' => 'Czech Republic',
		),
		array(
			'code' => 'DK',
			'name' => 'Denmark',
		),
		array(
			'code' => 'NO',
			'name' => 'Norway',
		),
		array(
			'code' => 'GB',
			'name' => 'United Kingdom',
		),
		array(
			'code' => 'CH',
			'name' => 'Switzerland',
		),
		array(
			'code' => 'NZ',
			'name' => 'New Zealand',
		),
		array(
			'code' => 'RU',
			'name' => 'Russian Federation',
		),
		array(
			'code' => 'PT',
			'name' => 'Portugal',
		),
		array(
			'code' => 'NL',
			'name' => 'Netherlands',
		),
		array(
			'code' => 'IM',
			'name' => 'Isle of Man',
		),
		array(
			'code' => 'AF',
			'name' => 'Afghanistan',
		),
		array(
			'code' => 'AX',
			'name' => 'Aland Islands ',
		),
		array(
			'code' => 'AL',
			'name' => 'Albania',
		),
		array(
			'code' => 'DZ',
			'name' => 'Algeria',
		),
		array(
			'code' => 'AS',
			'name' => 'American Samoa',
		),
		array(
			'code' => 'AD',
			'name' => 'Andorra',
		),
		array(
			'code' => 'AO',
			'name' => 'Angola',
		),
		array(
			'code' => 'AI',
			'name' => 'Anguilla',
		),
		array(
			'code' => 'AQ',
			'name' => 'Antarctica',
		),
		array(
			'code' => 'AG',
			'name' => 'Antigua and Barbuda',
		),
		array(
			'code' => 'AR',
			'name' => 'Argentina',
		),
		array(
			'code' => 'AM',
			'name' => 'Armenia',
		),
		array(
			'code' => 'AW',
			'name' => 'Aruba',
		),
		array(
			'code' => 'AZ',
			'name' => 'Azerbaijan',
		),
		array(
			'code' => 'BS',
			'name' => 'Bahamas',
		),
		array(
			'code' => 'BH',
			'name' => 'Bahrain',
		),
		array(
			'code' => 'BD',
			'name' => 'Bangladesh',
		),
		array(
			'code' => 'BB',
			'name' => 'Barbados',
		),
		array(
			'code' => 'BY',
			'name' => 'Belarus',
		),
		array(
			'code' => 'BZ',
			'name' => 'Belize',
		),
		array(
			'code' => 'BJ',
			'name' => 'Benin',
		),
		array(
			'code' => 'BM',
			'name' => 'Bermuda',
		),
		array(
			'code' => 'BT',
			'name' => 'Bhutan',
		),
		array(
			'code' => 'BO',
			'name' => 'Bolivia, Plurinational State of',
		),
		array(
			'code' => 'BQ',
			'name' => 'Bonaire, Sint Eustatius and Saba',
		),
		array(
			'code' => 'BA',
			'name' => 'Bosnia and Herzegovina',
		),
		array(
			'code' => 'BW',
			'name' => 'Botswana',
		),
		array(
			'code' => 'BV',
			'name' => 'Bouvet Island',
		),
		array(
			'code' => 'BR',
			'name' => 'Brazil',
		),
		array(
			'code' => 'IO',
			'name' => 'British Indian Ocean Territory',
		),
		array(
			'code' => 'BN',
			'name' => 'Brunei Darussalam',
		),
		array(
			'code' => 'BG',
			'name' => 'Bulgaria',
		),
		array(
			'code' => 'BF',
			'name' => 'Burkina Faso',
		),
		array(
			'code' => 'BI',
			'name' => 'Burundi',
		),
		array(
			'code' => 'KH',
			'name' => 'Cambodia',
		),
		array(
			'code' => 'CM',
			'name' => 'Cameroon',
		),
		array(
			'code' => 'CV',
			'name' => 'Cape Verde',
		),
		array(
			'code' => 'KY',
			'name' => 'Cayman Islands',
		),
		array(
			'code' => 'CF',
			'name' => 'Central African Republic',
		),
		array(
			'code' => 'TD',
			'name' => 'Chad',
		),
		array(
			'code' => 'CL',
			'name' => 'Chile',
		),
		array(
			'code' => 'CN',
			'name' => 'China',
		),
		array(
			'code' => 'CX',
			'name' => 'Christmas Island',
		),
		array(
			'code' => 'CC',
			'name' => 'Cocos (Keeling) Islands',
		),
		array(
			'code' => 'CO',
			'name' => 'Colombia',
		),
		array(
			'code' => 'KM',
			'name' => 'Comoros',
		),
		array(
			'code' => 'CG',
			'name' => 'Congo',
		),
		array(
			'code' => 'CD',
			'name' => 'Congo, the Democratic Republic of the',
		),
		array(
			'code' => 'CK',
			'name' => 'Cook Islands',
		),
		array(
			'code' => 'CR',
			'name' => 'Costa Rica',
		),
		array(
			'code' => 'CI',
			'name' => 'Cote d\'Ivoire',
		),
		array(
			'code' => 'HR',
			'name' => 'Croatia',
		),
		array(
			'code' => 'CU',
			'name' => 'Cuba',
		),
		array(
			'code' => 'CW',
			'name' => 'Curaçao',
		),
		array(
			'code' => 'CY',
			'name' => 'Cyprus',
		),
		array(
			'code' => 'DJ',
			'name' => 'Djibouti',
		),
		array(
			'code' => 'DM',
			'name' => 'Dominica',
		),
		array(
			'code' => 'DO',
			'name' => 'Dominican Republic',
		),
		array(
			'code' => 'EC',
			'name' => 'Ecuador',
		),
		array(
			'code' => 'EG',
			'name' => 'Egypt',
		),
		array(
			'code' => 'SV',
			'name' => 'El Salvador',
		),
		array(
			'code' => 'GQ',
			'name' => 'Equatorial Guinea',
		),
		array(
			'code' => 'ER',
			'name' => 'Eritrea',
		),
		array(
			'code' => 'EE',
			'name' => 'Estonia',
		),
		array(
			'code' => 'ET',
			'name' => 'Ethiopia',
		),
		array(
			'code' => 'FK',
			'name' => 'Falkland Islands (Malvinas)',
		),
		array(
			'code' => 'FO',
			'name' => 'Faroe Islands',
		),
		array(
			'code' => 'FJ',
			'name' => 'Fiji',
		),
		array(
			'code' => 'GF',
			'name' => 'French Guiana',
		),
		array(
			'code' => 'PF',
			'name' => 'French Polynesia',
		),
		array(
			'code' => 'TF',
			'name' => 'French Southern Territories',
		),
		array(
			'code' => 'GA',
			'name' => 'Gabon',
		),
		array(
			'code' => 'GM',
			'name' => 'Gambia',
		),
		array(
			'code' => 'GE',
			'name' => 'Georgia',
		),
		array(
			'code' => 'GH',
			'name' => 'Ghana',
		),
		array(
			'code' => 'GI',
			'name' => 'Gibraltar',
		),
		array(
			'code' => 'GR',
			'name' => 'Greece',
		),
		array(
			'code' => 'GL',
			'name' => 'Greenland',
		),
		array(
			'code' => 'GD',
			'name' => 'Grenada',
		),
		array(
			'code' => 'GP',
			'name' => 'Guadeloupe',
		),
		array(
			'code' => 'GU',
			'name' => 'Guam',
		),
		array(
			'code' => 'GT',
			'name' => 'Guatemala',
		),
		array(
			'code' => 'GG',
			'name' => 'Guernsey',
		),
		array(
			'code' => 'GN',
			'name' => 'Guinea',
		),
		array(
			'code' => 'GW',
			'name' => 'Guinea-Bissau',
		),
		array(
			'code' => 'GY',
			'name' => 'Guyana',
		),
		array(
			'code' => 'HT',
			'name' => 'Haiti',
		),
		array(
			'code' => 'HM',
			'name' => 'Heard Island and McDonald Islands',
		),
		array(
			'code' => 'VA',
			'name' => 'Holy See (Vatican City State)',
		),
		array(
			'code' => 'HN',
			'name' => 'Honduras',
		),
		array(
			'code' => 'HK',
			'name' => 'Hong Kong',
		),
		array(
			'code' => 'HU',
			'name' => 'Hungary',
		),
		array(
			'code' => 'IN',
			'name' => 'India',
		),
		array(
			'code' => 'ID',
			'name' => 'Indonesia',
		),
		array(
			'code' => 'IR',
			'name' => 'Iran, Islamic Republic of',
		),
		array(
			'code' => 'IQ',
			'name' => 'Iraq',
		),
		array(
			'code' => 'IL',
			'name' => 'Israel',
		),
		array(
			'code' => 'JM',
			'name' => 'Jamaica',
		),
		array(
			'code' => 'JP',
			'name' => 'Japan',
		),
		array(
			'code' => 'JE',
			'name' => 'Jersey',
		),
		array(
			'code' => 'JO',
			'name' => 'Jordan',
		),
		array(
			'code' => 'KZ',
			'name' => 'Kazakhstan',
		),
		array(
			'code' => 'KE',
			'name' => 'Kenya',
		),
		array(
			'code' => 'KI',
			'name' => 'Kiribati',
		),
		array(
			'code' => 'KP',
			'name' => 'Korea, Democratic People\'s Republic of',
		),
		array(
			'code' => 'KR',
			'name' => 'Korea, Republic of',
		),
		array(
			'code' => 'KW',
			'name' => 'Kuwait',
		),
		array(
			'code' => 'KG',
			'name' => 'Kyrgyzstan',
		),
		array(
			'code' => 'LA',
			'name' => 'Lao People\'s Democratic Republic',
		),
		array(
			'code' => 'LV',
			'name' => 'Latvia',
		),
		array(
			'code' => 'LB',
			'name' => 'Lebanon',
		),
		array(
			'code' => 'LS',
			'name' => 'Lesotho',
		),
		array(
			'code' => 'LR',
			'name' => 'Liberia',
		),
		array(
			'code' => 'LY',
			'name' => 'Libyan Arab Jamahiriya',
		),
		array(
			'code' => 'LI',
			'name' => 'Liechtenstein',
		),
		array(
			'code' => 'LT',
			'name' => 'Lithuania',
		),
		array(
			'code' => 'LU',
			'name' => 'Luxembourg',
		),
		array(
			'code' => 'MO',
			'name' => 'Macao',
		),
		array(
			'code' => 'MK',
			'name' => 'Macedonia',
		),
		array(
			'code' => 'MG',
			'name' => 'Madagascar',
		),
		array(
			'code' => 'MW',
			'name' => 'Malawi',
		),
		array(
			'code' => 'MY',
			'name' => 'Malaysia',
		),
		array(
			'code' => 'MV',
			'name' => 'Maldives',
		),
		array(
			'code' => 'ML',
			'name' => 'Mali',
		),
		array(
			'code' => 'MT',
			'name' => 'Malta',
		),
		array(
			'code' => 'MH',
			'name' => 'Marshall Islands',
		),
		array(
			'code' => 'MQ',
			'name' => 'Martinique',
		),
		array(
			'code' => 'MR',
			'name' => 'Mauritania',
		),
		array(
			'code' => 'MU',
			'name' => 'Mauritius',
		),
		array(
			'code' => 'YT',
			'name' => 'Mayotte',
		),
		array(
			'code' => 'MX',
			'name' => 'Mexico',
		),
		array(
			'code' => 'FM',
			'name' => 'Micronesia, Federated States of',
		),
		array(
			'code' => 'MD',
			'name' => 'Moldova, Republic of',
		),
		array(
			'code' => 'MC',
			'name' => 'Monaco',
		),
		array(
			'code' => 'MN',
			'name' => 'Mongolia',
		),
		array(
			'code' => 'ME',
			'name' => 'Montenegro',
		),
		array(
			'code' => 'MS',
			'name' => 'Montserrat',
		),
		array(
			'code' => 'MA',
			'name' => 'Morocco',
		),
		array(
			'code' => 'MZ',
			'name' => 'Mozambique',
		),
		array(
			'code' => 'MM',
			'name' => 'Myanmar',
		),
		array(
			'code' => 'NA',
			'name' => 'Namibia',
		),
		array(
			'code' => 'NR',
			'name' => 'Nauru',
		),
		array(
			'code' => 'NP',
			'name' => 'Nepal',
		),
		array(
			'code' => 'NC',
			'name' => 'New Caledonia',
		),
		array(
			'code' => 'NI',
			'name' => 'Nicaragua',
		),
		array(
			'code' => 'NE',
			'name' => 'Niger',
		),
		array(
			'code' => 'NG',
			'name' => 'Nigeria',
		),
		array(
			'code' => 'NU',
			'name' => 'Niue',
		),
		array(
			'code' => 'NF',
			'name' => 'Norfolk Island',
		),
		array(
			'code' => 'MP',
			'name' => 'Northern Mariana Islands',
		),
		array(
			'code' => 'OM',
			'name' => 'Oman',
		),
		array(
			'code' => 'PK',
			'name' => 'Pakistan',
		),
		array(
			'code' => 'PW',
			'name' => 'Palau',
		),
		array(
			'code' => 'PS',
			'name' => 'Palestinian Territory, Occupied',
		),
		array(
			'code' => 'PA',
			'name' => 'Panama',
		),
		array(
			'code' => 'PG',
			'name' => 'Papua New Guinea',
		),
		array(
			'code' => 'PY',
			'name' => 'Paraguay',
		),
		array(
			'code' => 'PE',
			'name' => 'Peru',
		),
		array(
			'code' => 'PH',
			'name' => 'Philippines',
		),
		array(
			'code' => 'PN',
			'name' => 'Pitcairn',
		),
		array(
			'code' => 'PL',
			'name' => 'Poland',
		),
		array(
			'code' => 'PR',
			'name' => 'Puerto Rico',
		),
		array(
			'code' => 'QA',
			'name' => 'Qatar',
		),
		array(
			'code' => 'RE',
			'name' => 'Reunion',
		),
		array(
			'code' => 'RO',
			'name' => 'Romania',
		),
		array(
			'code' => 'RW',
			'name' => 'Rwanda',
		),
		array(
			'code' => 'BL',
			'name' => 'Saint Barthélemy',
		),
		array(
			'code' => 'SH',
			'name' => 'Saint Helena',
		),
		array(
			'code' => 'KN',
			'name' => 'Saint Kitts and Nevis',
		),
		array(
			'code' => 'LC',
			'name' => 'Saint Lucia',
		),
		array(
			'code' => 'MF',
			'name' => 'Saint Martin (French part)',
		),
		array(
			'code' => 'PM',
			'name' => 'Saint Pierre and Miquelon',
		),
		array(
			'code' => 'VC',
			'name' => 'Saint Vincent and the Grenadines',
		),
		array(
			'code' => 'WS',
			'name' => 'Samoa',
		),
		array(
			'code' => 'SM',
			'name' => 'San Marino',
		),
		array(
			'code' => 'ST',
			'name' => 'Sao Tome and Principe',
		),
		array(
			'code' => 'SA',
			'name' => 'Saudi Arabia',
		),
		array(
			'code' => 'SN',
			'name' => 'Senegal',
		),
		array(
			'code' => 'RS',
			'name' => 'Serbia',
		),
		array(
			'code' => 'SC',
			'name' => 'Seychelles',
		),
		array(
			'code' => 'SL',
			'name' => 'Sierra Leone',
		),
		array(
			'code' => 'SG',
			'name' => 'Singapore',
		),
		array(
			'code' => 'SX',
			'name' => 'Sint Maarten (Dutch part)',
		),
		array(
			'code' => 'SK',
			'name' => 'Slovakia',
		),
		array(
			'code' => 'SI',
			'name' => 'Slovenia',
		),
		array(
			'code' => 'SB',
			'name' => 'Solomon Islands',
		),
		array(
			'code' => 'SO',
			'name' => 'Somalia',
		),
		array(
			'code' => 'ZA',
			'name' => 'South Africa',
		),
		array(
			'code' => 'GS',
			'name' => 'South Georgia and the South Sandwich Islands',
		),
		array(
			'code' => 'LK',
			'name' => 'Sri Lanka',
		),
		array(
			'code' => 'SD',
			'name' => 'Sudan',
		),
		array(
			'code' => 'SR',
			'name' => 'Suriname',
		),
		array(
			'code' => 'SJ',
			'name' => 'Svalbard and Jan Mayen',
		),
		array(
			'code' => 'SZ',
			'name' => 'Swaziland',
		),
		array(
			'code' => 'SY',
			'name' => 'Syrian Arab Republic',
		),
		array(
			'code' => 'TW',
			'name' => 'Taiwan, Province of China',
		),
		array(
			'code' => 'TJ',
			'name' => 'Tajikistan',
		),
		array(
			'code' => 'TZ',
			'name' => 'Tanzania, United Republic of',
		),
		array(
			'code' => 'TH',
			'name' => 'Thailand',
		),
		array(
			'code' => 'TL',
			'name' => 'Timor-Leste',
		),
		array(
			'code' => 'TG',
			'name' => 'Togo',
		),
		array(
			'code' => 'TK',
			'name' => 'Tokelau',
		),
		array(
			'code' => 'TO',
			'name' => 'Tonga',
		),
		array(
			'code' => 'TT',
			'name' => 'Trinidad and Tobago',
		),
		array(
			'code' => 'TN',
			'name' => 'Tunisia',
		),
		array(
			'code' => 'TR',
			'name' => 'Turkey',
		),
		array(
			'code' => 'TM',
			'name' => 'Turkmenistan',
		),
		array(
			'code' => 'TC',
			'name' => 'Turks and Caicos Islands',
		),
		array(
			'code' => 'TV',
			'name' => 'Tuvalu',
		),
		array(
			'code' => 'UG',
			'name' => 'Uganda',
		),
		array(
			'code' => 'UA',
			'name' => 'Ukraine',
		),
		array(
			'code' => 'AE',
			'name' => 'United Arab Emirates',
		),
		array(
			'code' => 'UM',
			'name' => 'United States Minor Outlying Islands',
		),
		array(
			'code' => 'UY',
			'name' => 'Uruguay',
		),
		array(
			'code' => 'UZ',
			'name' => 'Uzbekistan',
		),
		array(
			'code' => 'VU',
			'name' => 'Vanuatu',
		),
		array(
			'code' => 'VE',
			'name' => 'Venezuela, Bolivarian Republic of',
		),
		array(
			'code' => 'VN',
			'name' => 'Viet Nam',
		),
		array(
			'code' => 'VG',
			'name' => 'Virgin Islands, British',
		),
		array(
			'code' => 'VI',
			'name' => 'Virgin Islands, U.S.',
		),
		array(
			'code' => 'WF',
			'name' => 'Wallis and Futuna',
		),
		array(
			'code' => 'EH',
			'name' => 'Western Sahara',
		),
		array(
			'code' => 'YE',
			'name' => 'Yemen',
		),
		array(
			'code' => 'ZM',
			'name' => 'Zambia',
		),
		array(
			'code' => 'ZW',
			'name' => 'Zimbabwe',
		),
	);
}
