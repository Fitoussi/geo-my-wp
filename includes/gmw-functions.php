<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
	
	$value = ! empty( $gmw_options[$group][$key] ) ? $gmw_options[$group][$key] : $default;
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
	
	if ( empty( $group ) || $group == 'gmw_options' ) {
		return $gmw_options;
	}

	if ( ! empty( $gmw_options[$group] ) ) {
		return $gmw_options[$group];
	}

	return false;
}

/**
 * Get specific form data
 * 
 * @param  boolean $id form ID
 * @return array      Form data
 */
function gmw_get_form( $id = false ) {
	return GMW_Helper::get_form( $id );
}

/**
 * Get specific form data
 * 
 * @return array GEo my WP forms data
 */
function gmw_get_forms() {
	return GMW_Helper::get_forms();
}

/**
 * Check if add-on is active
 * 
 * @param  string $addon slug/name of the addon
 * 
 * @return boolean true/false
 */
function gmw_is_addon_active( $addon = '' ) {
	return GMW_Helper::is_addon_active( $addon );
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
 * Get addon ( extensions ) status
 * 
 * @return array  data of all loaded addons
 */
function gmw_get_addon_status( $addon = false ) {

	return ! empty( GMW()->addons_status[$addon] ) ? GMW()->addons_status[$addon] : 'inactive';
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
 * Get addons ( extensions ) data
 * 
 * @return array  data of all loaded addons
 */
function gmw_get_addons_data() {

	return GMW()->addons;
}

/**
 * Get addon ( extension ) data
 * 
 * @param  string $addon slug/name of the addon to pull its data
 * 
 * @return array  add-on's data
 */
function gmw_get_addon_data( $addon = false ) {
	
	if ( ! empty( GMW()->addons[$addon] ) )  {
		return GMW()->addons[$addon];
	} else {
		return false;
	}
}

/**
 * Update addon_data
 * 
 * @param  [type] $addon [description]
 * @return [type]        [description]
 */
function gmw_update_addon_status( $addon = false, $status = 'active', $details = false ) {

	if ( empty( $addon ) || ! in_array( $status, array( 'active', 'inactive', 'disabled' ) ) ) {
		return;
	}

	// get addons data from database
	$addons_status = get_option( 'gmw_addons_status' );

	if ( empty( $addons_status ) ) {
		$addons_status = array();
	}

	// update addon data
	$addons_status[$addon] = $status;

	// save new data in database
	update_option( 'gmw_addons_status', $addons_status );

	// update status in global
	GMW()->addons_status = $addons_status;
	GMW()->addons[$addon]['status'] = $status;
	GMW()->addons[$addon]['status_details'] = $details;

	update_option( 'gmw_addons_data', GMW()->addons );

	return GMW()->addons[$addon];
}

function gmw_update_addon_data( $addon = array() ) {

	if ( empty( $addon ) ) {
		return;
	}

	$addon_data = get_option( 'gmw_addons_data' );

	if ( empty( $addons_data ) ) {
		$addons_data = array();
	}

	$addons_data[$addon['slug']] = $addon;

	update_option( 'gmw_addons_data', $addons_data );

	GMW()->addons = $addons_data;
}

/**
 * Get the user current location
 * 
 * @param  array  $fields field to output from the list of field below
 * @param  string $type   array or object
 * 
 * @return ARRAY | OBJECT of the user location
 */
function gmw_get_user_current_location() {
	
	// abort if user's location does not exist in cookies
	if ( empty( $_COOKIE['gmw_ul_lat'] ) || empty( $_COOKIE['gmw_ul_lng'] ) ) {
		return false;
	}

	$fields = array( 
		'lat',          	
		'lng',
		'street',
		'city',
		'region_name',
		'region_code',
		'postcode',
		'country_name',
		'country_code',
		'address',
		'formatted_address'
	);
	
	$location = wp_cache_get( 'gmw_user_current_location' );

	if ( false === $location ) {

		$location = ( object ) array();

		foreach ( $fields as $field ) {
		    
		    if ( ! empty( $_COOKIE['gmw_ul_'. $field] ) ) {
		        $location->$field = urldecode( $_COOKIE['gmw_ul_'.$field] );
		    } else {
		    	$location->$field = '';
		    }
		}	
		
		wp_cache_set( 'gmw_user_current_location', $location, '', 86400 );
	}

	return $location;
}

/**
 * Processes all GMW actions sent via POST and GET by looking for the 'gmw_action'
 * request and running do_action() to call the function
 *
 * @since 2.5
 * @return void
 */
function gmw_process_actions() {
	
	if ( isset( $_POST['gmw_action'] ) ) {
		do_action( 'gmw_' . $_POST['gmw_action'], $_POST );
	}

	if ( isset( $_GET['gmw_action'] ) ) {
		do_action( 'gmw_' . $_GET['gmw_action'], $_GET );
	}
}
if ( IS_ADMIN ) {
	add_action( 'admin_init', 'gmw_process_actions' );
} else {
	add_action( 'init', 'gmw_process_actions' );
}

/**
 * GMW Function - Covert object to array
 * 
 * @since  2.5
 * @param  object
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

function gmw_multiexplode( $delimiters, $string ) {

    $ready  = str_replace( $delimiters, $delimiters[0], $string );
    $launch = explode( $delimiters[0], $ready );

    return $launch;
}

/**
 * Sort array by priority. For Settings and forms pages.
 * @param  [type] $a [description]
 * @param  [type] $b [description]
 * @return [type]    [description]
 */
function gmw_sort_by_priority( $a, $b ) {

    $a['priority'] = ( ! empty( $a['priority'] ) ) ? $a['priority'] : 99;
    $b['priority'] = ( ! empty( $b['priority'] ) ) ? $b['priority'] : 99;
    
    if ( $a['priority'] == $b['priority'] ) {
        return 0;
    }

    return $a['priority'] - $b['priority'];
}

/**
 * Convert object to array
 * 
 * @param  object $object 
 * @param  [type] $output ARRAY_A || ARRAY_N
 * 
 * @return array
 */
function gmw_to_array( $object, $output = ARRAY_A ) {

	if ( $output == ARRAY_A ) {
        return ( array ) $object;
	} elseif ( $output == ARRAY_N ) {
        return array_values( ( array ) $object );
	} else {
		return $object;
	}
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
 * For older PHP version that does not have the array_replace_recursive function
 */
if ( ! function_exists( 'array_replace_recursive' ) ) {
	
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
 * get tempalte file and its stylesheet
 *
 * @since 3.0
 * 
 * @param  string $addon         the slug of the add-on which the tmeplate file belongs to.
 * @param  string $folder_name   folder name ( search-forms, search-results... ).
 * @param  string $template_name tempalte name
 * 
 * @return 
 */
function gmw_get_template( $slug='posts_locator', $folder_name='search-forms', $template_name='default' ) {
	return GMW_Helper::get_template( $slug, $folder_name, $template_name );
}

/**
 * Create new map element
 *
 * Pass the arguments to display a map. Each element created is pushed into the global map elements.
 * The global map elements pass to the map.js file. The map.js loop through the map elements
 * and generates each map based on the arguments passed to the function.
 *
 * More information about google maps API can be found here - https://developers.google.com/maps/documentation/javascript/reference#MapOptions
 */
function gmw_new_map_element( $map_args = array(), $map_options = array(), $locations = array(), $user_position = array(), $form = array() ) {
	return GMW_Maps_API::get_map_args( $map_args, $map_options, $locations, $user_position, $form );
}


function gmw_get_map( $map_args = array(), $map_options = array(), $locations = array(), $user_position = array(), $form = array() ) {
	return GMW_Maps_API::get_map( $map_args, $map_options, $locations, $user_position, $form );	
}

function gmw_get_directions_form( $args = array() ) {
	return GMW_Maps_API::directions_form( $args );
}

/**
 * GMW function - live directions results panel. To be used with live directions function only.
 * @param unknown_type $info
 * @param unknown_type $gmw
 */
function gmw_get_directions_panel( $id = 0 ) {
	return GMW_Maps_API::directions_panel( $id );
}
	
/**
 * Enqueue search form/results stylesheet earlier in the <HEAD> tag
 *
 * By default, since GEO my WP uses shortcodes to display its forms, search forms and search results stylesheet loads outside the <head> tag.
 * This can cause the search forms / results look out of styling for a short moment on page load. As well it can cause HTML validation error.
 * 
 * You can use this function to overcome this issue. Pass an array of the form id and the pages which you want to load the stylesheet early in the head.
 * 
 * @param  array array( 
 *         		'form_id' 	  => the id of the form to load its stylesheets,
 *           	'pages'        => array of pages ID where you'd like to load the form's stylesheets. Empty array to load on every page,
 *            	'folders_name' => array of the folders name to load early. Right now the function supports search-forms and search-results.
 *         );
 *         
 * @return void 
 */
function gmw_enqueue_form_styles( $args = array( 'form_id' => 0, 'pages' => array(), 'folders_name' => array( 'search-forms', 'search-results' ) ) ) {

	$page_id = get_the_ID();
		
	$form = gmw_get_form( $args['form_id'] );	

	// abort if form doesnt exist
	if ( empty( $form ) ) {
		return;
	}

	if ( empty( $args['pages'] ) || ( is_array( $args['pages'] ) && in_array( $page_id, $args['pages'] ) ) ) {
		
		// get the addon slug
		$addon_data = gmw_get_addon_data( $form['slug'] );

		if ( in_array( 'search-forms', $args['folders_name'] ) ) {

			$template = $form['search_form']['form_template'];

			// Get custom tempalte and css from child/theme folder
			if ( strpos( $template, 'custom_' ) !== false ) {

				$template     	   = str_replace( 'custom_', '', $template );
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-forms-custom-{$template}";
				$stylesheet_url	   = get_stylesheet_directory_uri(). "/geo-my-wp/{$addon_data['templates_folder']}/search-forms/{$template}/css/style.css";
		
			// load tempalte files from plugin's folder
			} else {
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-forms-{$template}";
				$stylesheet_url    = $addon_data['plugin_url']."/templates/search-forms/{$template}/css/style.css";
			}

			if ( ! wp_style_is( $stylesheet_handle, 'enqueued' ) ) {
				wp_enqueue_style( $stylesheet_handle, $stylesheet_url, array(), GMW_VERSION );
			}
		}
		
		if ( in_array( 'search-results', $args['folders_name'] ) ) {

			$template = $form['search_results']['results_template'];

			// Get custom tempalte and css from child/theme folder
			if ( strpos( $template, 'custom_' ) !== false ) {

				$template     	   = str_replace( 'custom_', '', $template );
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-results-custom-{$template}";
				$stylesheet_url	   = get_stylesheet_directory_uri(). "/geo-my-wp/{$addon_data['templates_folder']}/search-results/{$template}/css/style.css";
		
			// load tempalte files from plugin's folder
			} else {
				$stylesheet_handle = "gmw-{$addon_data['prefix']}-search-results-{$template}";
				$stylesheet_url    = $addon_data['plugin_url']."/templates/search-results/{$template}/css/style.css";
			}

			if ( ! wp_style_is( $stylesheet_handle, 'enqueued' ) ) {
				wp_enqueue_style( $stylesheet_handle, $stylesheet_url, array(), GMW_VERSION );
			}
		}
	}	
}

/**
 * Info window content
 *
 * Generate the information that will be displayed in the info-window that opens when clicking on a map marker.
 *
 * The information can be modifyed via the filter below
 * 
 * @return [type]    [description]
 */
function gmw_get_info_window_content( $location, $args, $gmw=array() ) {
	
	$default_args = array(
        'url'    	=> '#',
        'title'  	=> false,
        'image_url' => false,
        'image'		=> false
    );

	$args = wp_parse_args( $args, $default_args );

	// labels
	$labels = gmw_get_labels()['info_window'];
	
	// object URL
	if ( $args['url'] != '#' && ! empty( $args['url'] ) ) {
		$args['url'] = esc_url( $args['url'] );
	}

	// address
	$address = ! empty( $location->formatted_address ) ? $location->formatted_address : $location->address;
	
	// prefix
	$prefix = ! empty( $gmw['prefix'] ) ? ' '.esc_attr( $gmw['prefix'] ) : '';

	$output  	     = array();
	$output['wrap'] = '<div class="gmw-info-window-wrapper'.$prefix.'" data-location_id="'.absint( $location->location_id ) .'" data-object="'. esc_attr( $location->object_type ).'" data-prefix="'.$prefix.'">';
	
	// Look for image
	if ( ! empty( $args['image_url'] ) || ! empty( $args['image'] ) ) {

		if ( ! empty( $args['image_url'] ) ) {
			$output['image'] = '<div class="image"><a href="'. $args['url'] .'"><img tag="'.esc_attr( $args['title'] ).'" src="'.esc_html( $args['image_url'] ) .'" /></a></div>';
		} else {
			$output['image'] = '<div class="image"><a href="'. $args['url'] .'">'. $args['image'] .'</a></div>';
		}
	}

	$output['header'] = '<div class="header">';

	// distance if exists
	if ( ! empty( $location->distance ) ) {
		$output['distance'] = '<span class="distance">'. esc_attr( $location->distance ) . ' ' .$location->units.'</span>';
	}

	// title
	if ( ! empty( $args['title'] ) ) {
		$output['title'] = '<h2 class="title"><a href="'.$args['url'] .'">'. esc_attr( $args['title'] ) .'</a></h2>';
	}

	$output['/header'] = '</div>';

	// content
	$output['content'] = '<div class="content">';
	
	// location meta if needed
	if ( ! empty( $location->location_meta ) && apply_filters( 'gmw_enable_info_window_location_meta', false) ) {

		$output['location_meta'] = '<ul class="location-meta">';

		foreach ( $location->location_meta as $field => $value ) {

    		if ( ! empty( $value ) ) {

    			$label = ! empty( $labels[$field] ) ? esc_attr( $labels[$field] ) : esc_attr( $field );

    			$output['lm_'.$field] = '<li><span class="label '.$label.'">'. $label .'</span>'. esc_attr( $value ) .'</li>';
    		}
		}

		$output['/location_meta'] = '</ul>';
	}
	
	// address
	$output['address'] = '<span class="address"><i class="gmw-icon-location"></i>'. esc_attr( $address ) .'</span>';
	// directions link
	$output['directions'] = '<span class="directions">'.gmw_get_directions_link( $location, $gmw ).'</span>';

	$output['/content'] = '</div>';
	$output['/wrap']    = '</div>';

	// modify the output
	$output = apply_filters( 'gmw_info_window_content', $output, $location, $args, $gmw );

	if ( ! empty( $prefix ) ) {
		$output = apply_filters( "gmw_{$prefix}_info_window_content", $output, $location, $args, $gmw );
	}

	// output content
	return implode( ' ', $output );
}

/**
 * Array of countries that can be used for select dropdown.
 * 
 * @return array of countries
 */
function gmw_get_countries_list_array( $first = false ) {

	$countries = array(
		'0' => '',
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
?>