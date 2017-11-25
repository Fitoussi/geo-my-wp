<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get location object based on object type and object ID
 * 
 * @Since 3.0
 * 
 * @param  string $object_type the type of object we are looking for ( post, user...)
 * @param  int $object_id      object ID ( post ID, user ID... ).
 * 
 * @return object              complete location data.
 */
function gmw_get_location( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {
	return GMW_Location::get_location( $object_type, $object_id, $output, $cache );
}

/**
 * Get Location object by location ID
 *
 * @since 3.0
 * 
 * @param  integer   $location_id the location ID
 * @param  string    $fields      location fields comma separated	
 * @param  constant  $output      OBJECT | ARRAY_A | ARRAY_N
 * @param  boolean   $cache       look for location in cache first
 * 
 * @return [type]               [description]
 */
function gmw_get_location_by_id( $location_id = 0, $output = OBJECT, $cache = true ) {
	return GMW_Location::get_location_by_id( $location_id, $output, $cache );
}

/**
 * Get location ID
 *
 * @since 3.0
 *        
 * @param  string  $object_type object type ( post, user... )
 * @param  int     $object_id object ID ( post ID, user ID.... )
 * @param  boolean $cache $cache       look for location in cache first
 * 
 * @return int location ID
 */
function gmw_get_location_id( $object_type = 'post', $object_id = 0, $cache = true ) {
	$location = GMW_Location::get_location( $object_type, $object_id, OBJECT, $cache );
	return ! empty( $location->ID ) ? $location->ID : false;
}

/**
 * Get location meta by location ID.
 * 
 * @param  integer 			$location_id location ID
 * @param  string || array  $meta_keys   single meta key as a sting or array of keys
 * @param  boolean 			$cache   
 * 
 * @return string || array
 * 
 */
function gmw_get_location_meta( $location_id = 0, $meta_keys = array(), $cache = true ) {
	return GMW_Location::get_location_meta( $location_id, $meta_keys, $cache );
}

/**
 * Get location metadata by object type and object ID
 * 
 * @param  boolean $object_type object_type object type ( post, user... )
 * @param  boolean $object_id   object ID ( post ID, user ID.... )
 * @param  array   $meta_keys   sting of a single or array of multiple meta keys to retrive their values
 * 
 * @return [type]  string || array of values
 */
function gmw_get_location_meta_by_object( $object_type = false, $object_id = false, $meta_keys = array() ) {
	return GMW_Location::get_location_meta_by_object( $object_type, $object_id, $meta_keys );
}

/**
 * Update location meta
 * 
 * @param  integer $location_id 
 * @param  string  $meta_key   
 * @param  string  $meta_value  
 *
 * @since 3.0 
 * 
 * @return int meta ID
 */
function gmw_update_location_meta( $location_id = 0, $meta_key = '' , $meta_value = '' ) {
	return GMW_Location::update_location_meta( $location_id, $meta_key, $meta_value );
}

/**
 * Delete location meta
 * 
 * @param  integer $location_id [description]
 * @param  string  $meta_key    [description]
 * 
 * @return [type]               [description]
 */
function gmw_delete_location_meta( $location_id = 0, $meta_key = '' ) {
	return GMW_Location::delete_location_meta( $location_id, $meta_key );
}

/**
 * Delete location metadata by object type and object ID
 * 
 * @param  string $object_type object_type object type ( post, user... )
 * @param  int    $object_id   object ID ( post ID, user ID.... )
 * @param  string $meta_key    meta key to delete
 * 
 * @return [type]  string || array of values
 */
function gmw_delete_location_meta_by_object( $object_type = false, $object_id = 0, $meta_key = '' ) {
	return GMW_Location::delete_location_meta_by_object( $object_type, $object_id, $meta_key );
}

/**
 * Update location using object type, object ID and an address.
 *
 * The function will geocode the address and save it in the locations table in DB
 *
 * @since 3.0
 * @author Eyal Fitoussi
 * 
 * @param  string  $object_type   string ( post, user, comment.... )
 * @param  integer $object_id     int ( post ID, user ID, comment ID... )
 * @param  boolean $address       can be either a string or an array of address field for example:
 * $defaults = array(
 *      'street'    => 285 Fulton St,
 *      'apt'       => '',
 *      'city'      => 'New York',
 *      'state'     => 'NY',
 *      'zipcode'   => '10007',
 *      'country'   => 'USA'
 * );
 * @param  integer $user_id       the user whom the location belongs to. By default it will belong to the user who creates/update the location ( logeed in user ).
 * @param  boolean $force_refresh false to use geocoded address in cache || true to force address geocoding
 * 
 * @return int location ID
 */
function gmw_update_location( $object_type = '', $object_id = 0, $address = false, $user_id = 0, $force_refresh = false ) {

    // abort if data is missing
    if ( empty( $object_type ) || empty( $object_id ) || empty( $address ) ) {
        return;
    }

    if ( empty( $user_id ) ) {
    	$user_id = get_current_user_id();
    }

    $geo_address     = $address;
    $multiple_fields = false;

    // if address is an array
    if ( is_array( $address ) ) {
        
        $multiple_fields = true;

        $defaults = array(
            'street'    => '',
            'apt'       => '',
            'city'      => '',
            'state'     => '',
            'zipcode'   => '',
            'country'   => ''
        );
      
        // Parse incoming $args into an array and merge it with $defaults
        $address = wp_parse_args( $address, $defaults );

        $geo_address = $address;

        // remove apt from address field to be able to geocode it properly
        unset( $geo_address['apt'] );

        $geo_address = implode( ' ', $geo_address );
    }

    // include geocoder file
    include_once( GMW_PATH . '/includes/gmw-geocoder.php' );

    if ( ! function_exists( 'gmw_geocoder' ) ) {

    	trigger_error( 'Geocoder class not exists.', E_USER_NOTICE );

    	return false;
    }

    //geocode the address
    $geocoded_address = gmw_geocoder( $geo_address, $force_refresh );

    // abort if geocode failed
    if ( isset( $geocoded_address['error'] ) ) {
        
        //GMW_Location::delete_location( $object_type, $object_id, false );

        do_action( 'gmw_udpate_location_failed', $geocoded_address, $object_type, $object_id, $address );

        return;
    }

    // if multiple address field passed through array 
    // get the original address field entered
    if ( $multiple_fields ) {
 
        $street   = ! empty( $address['street'] ) ? sanitize_text_field( $address['street'] ) : $geocoded_address['street']; 
        $premise  = ! empty( $address['apt'] ) 	? sanitize_text_field( $address['apt'] ) : $geocoded_address['premise'];
        $city     = ! empty( $address['city'] ) ? sanitize_text_field( $address['city'] ) : $geocoded_address['city'];
        $postcode = ! empty( $address['zipcode'] ) ? sanitize_text_field( $address['zipcode'] ) : $geocoded_address['postcode'];
        $region_code  = $geocoded_address['region_code'];
        $country_code = $geocoded_address['country_code'];

    } else {

        $street       = $geocoded_address['street'];
        $premise      = $geocoded_address['premise'];
        $city         = $geocoded_address['city'];
        $region_code  = $geocoded_address['region_code'];
        $postcode     = $geocoded_address['postcode'];
        $country_code = $geocoded_address['country_code'];
    }

    // collect location data into array
    $location_data = array(
        'object_type'       => $object_type,
        'object_id'         => $object_id,
        'user_id'			=> $user_id,
        'latitude'          => $geocoded_address['lat'],
        'longitude'         => $geocoded_address['lng'],
        'street_number'     => $geocoded_address['street_number'],
        'street_name'       => $geocoded_address['street_name'],
        'street'            => $street,
        'premise'           => $premise,
        'neighborhood'      => $geocoded_address['neighborhood'],
        'city'              => $city,
        'county'            => $geocoded_address['county'],
        'region_name'       => $geocoded_address['region_name'],
        'region_code'       => $region_code,
        'postcode'          => $postcode,
        'country_name'      => $geocoded_address['country_name'],
        'country_code'      => $country_code,
        'address'           => is_array( $address ) ? implode( ' ', $address ) : $address,
        'formatted_address' => $geocoded_address['formatted_address'],
        'place_id'			=> $geocoded_address['place_id']
    );

    // modify the data if needed
    $location_data = apply_filters( "gmw_pre_update_location_data", $object_type, $location_data, $geocoded_address );
    $location_data = apply_filters( "gmw_pre_update_{$object_type}_location_data", $location_data, $geocoded_address );

    do_action( "gmw_pre_update_{$object_type}_location", $location_data, $geocoded_address );
    
    //Save information to database
    $location_id = GMW_Location::update_location( $location_data );

    do_action( "gmw_{$object_type}_location_updated", $location_data, $geocoded_address );

    return $location_id;
}

/**
 * Get specific location address fields
 *
 * @since 3.0
 *        
 * @param  string  $object_type object_type object type ( post, user... )
 * @param  boolean $object_id   object ID ( post ID, user ID.... )
 * @param  array   $fields      array of address fields to retrive
 * @param  string  $separator   character to be used as separator between fields
 * 
 * @return string
 */
function gmw_get_address_fields( $object_type = false, $object_id = 0, $fields = array( 'formatted_address' ), $separator = ', ' ) {

	// all address fields
    $all_fields = array( 
		'latitude',
		'longitude',
		'street_number',
		'street_name',
		'street',
		'premise',
		'neighborhood',
		'city',
		'county',
		'state',
		'region_name',
		'region_code',
		'postcode',
		'country',
		'country_name',
		'country_code',
		'address',
		'formatted_address'
	);

    // if string convert to array
    if ( is_string( $fields ) ) {
    	$fields = array( $fields );
    }

    // for backward compatibility
    if ( in_array( 'lat', $fields ) ) {
    	$fields[] = 'latitude';
    }

    // for backward compatibility
    if ( in_array( 'lng', $fields ) ) {
    	$fields[] = 'longitude';
    }

    // for backward compatibility
    if ( in_array( 'zipcode', $fields ) ) {
    	$fields[] = 'postcode';
    }

    if ( empty( $fields ) || $fields[0] == '*' ) {
    	$fields = $all_fields;
    } else {
		$fields = array_intersect( array_map( 'trim', $fields ), $all_fields );
	}

	// get location data
    $location = gmw_get_location( $object_type, $object_id );

    $fields_count = count( $fields );
    $count 		  = 1;
    $output 	  = '';

    // loop trough fields and get the specified address fields
    foreach ( $fields as $field ) {

    	if ( $field == 'country' ) {
    		$field = 'country_name';
    	}

    	if ( $field == 'state' ) {
    		$field = 'region_name';
    	}

    	if ( isset( $location->$field ) ) {
    		$output .= $location->$field;
    		if ( $count != $fields_count ) {	
    			$output .= $separator;
    		}
    	}

    	$count++;
    }

	return $output;
}

/**
 * get the address of an object ( post, user... )
 * 
 * @param  object || integer $location object or location ID
 * @param  array  $gmw  	the form being used if in the search results
 * 
 * @return string       address
 */
function gmw_get_location_address( $location, $fields = array( 'formatted_address' ), $gmw = array() ) {

	if ( ! empty( $fields['addon'] ) ) {

		$gmw    = $fields;
		$fields = array( 'formatted_address' );

		trigger_error( 'Since GEO my WP 3.0 gmw_get_location_address function excepts an additional $fields argument. You need to modify the arguments pass to the function. gmw_get_location_address( $location, $fields, $gmw ).', E_USER_NOTICE );
	}

	if ( ! is_array( $fields ) ) {
		$fields = explode( ',',$fields );
	}

	if ( empty( $fields ) ) {
		return;
	}

	// if location is integer
	if ( is_int( $location ) ) {
		
		// get location by ID
		$location = gmw_get_location_by_id( $location );
	
	// otherwise, abort if location is not an object
	} elseif ( ! is_object( $location ) ) {
		$location = false;
	}

	// abort if no location
	if ( empty( $location ) ) {
		return false;
	}
	
	$output = '';

	// loop trough fields and get the specified address fields
    foreach ( $fields as $field ) {

    	if ( $field == 'country' ) {
    		$field = 'country_name';
    	}

    	if ( $field == 'state' ) {
    		$field = 'region_name';
    	}

    	if ( isset( $location->$field ) ) {
    		$output .= $location->$field . ' ';
    	}
    }

	// modify the output address
	$output = apply_filters( 'gmw_location_address', $output, $location, $fields, $gmw );
	$output = apply_filters( "gmw_{$location->object_type}_location_address", $output, $location, $fields, $gmw );

	return ! empty( $output ) ? stripslashes( esc_html( $output ) ) : '';
}

	function gmw_location_address( $location, $fields = array(), $gmw = array() ) {
		echo gmw_get_location_address( $location, $fields, $gmw );
	}

/**
 * Return the address of a location as a link to google maps
 * 
 * @param  [type] $location [description]
 * @param  array  $gmw      [description]
 * @return [type]           [description]
 */
function gmw_get_linked_location_address( $location, $fields = array( 'formatted_address' ), $gmw = array() ) {

	$address = gmw_get_location_address( $location, $fields, $gmw );

    if ( empty( $address ) ) {
        return;
    }

	return '<a href="https://maps.google.com/?q='.$address.'" target="_blank">'.$address.'</a>';
}

/**
 * Output list of location meta fields
 * 
 * @param  boolean $location [description]
 * @param  array   $fields   [description]
 * @param  array   $labels   [description]
 * @return [type]            [description]
 */
function gmw_get_location_meta_list( $location = false, $fields = array(), $labels = array() ) {

	// check if $location is an object and contains location meta. This will usually be used in the loop
	if ( is_object( $location ) ) {
		
		if ( empty( $location->ID ) ) {
			return;
		}

		$location_id = $location->ID;

		if ( ! empty( $location->location_meta ) ) {
			$location_meta = $location->location_meta;
		} else {
			$location_meta = gmw_get_location_meta( $location_id, $fields );
		}	

	// Otherwise, check for location ID
	} elseif ( is_numeric( $location ) ) {

		$location = absint( $location );
        
        if ( ! $location ) {
        	return;
        }

        // get location meta from database
		$location_meta = gmw_get_location_meta( $location, $fields );
		$location_id   = $location;

	// Abort if $location doenst match.
	} else {
		return;
	}
		
	// maybe string comma separated
	if ( ! is_array( $location_meta ) ) {
		$location_meta = explode( ',', $location_meta );
	}

	if ( empty( $location_meta ) ) {
		return;
	}
	
	$count  = 0;
	$output = '';
	
	// loop through fields
	foreach ( $location_meta as $field => $value ) {

		if ( empty( $value ) || ( ! empty( $fields ) && ! in_array( $field, $fields ) ) ) {
			continue;
		}

		$count++;

		// check for field label
		$label = isset( $labels[$field] ) ? $labels[$field] : $field;
		
		// email field
		if ( $field == 'email' ) {
			
			$value = esc_url( $value );

			$output .= '<li class="field '.sanitize_key( esc_attr( $field ) ).'">';
			$output .= '<span class="label">'.esc_html( $label ).': </span>';
			$output .= '<span class="info"><a href="mailto:'.$value.'">'.$value.'</a></span>';
			$output .= '</li>';			

		// website field
		} elseif ( in_array( $field, array( 'website', 'url', 'site' ) )  ) {
			
			$url = parse_url( $value );

			if ( empty( $url['scheme'] ) ) {
				$url['scheme'] = 'http';
			}
			
			$scheme = $url['scheme'].'://';
			$path   = str_replace( $scheme,'',$value );
			
			$output .= '<li class="field '.sanitize_key( esc_attr( $field ) ).'">';
			$output .= '<span class="label">'.esc_html( $label ).': </span>';
			$output .= '<span class="info"><a href="'.esc_url( $scheme.$path ).'" target="_blank">'. esc_html( $path ). '</a></span>';
			$output .= '</li>';

		// phone field
		} elseif ( in_array( $field, array( 'phone', 'cell', 'tel', 'telephone', 'mobile' ) ) ) {
			
			$value = esc_attr( $value );
			
			$output .= '<li class="field '.sanitize_key( esc_attr( $field ) ).'">';
			$output .= '<span class="label">'.esc_html( $label ).': </span>';
			$output .= '<span class="info"><a href="tel:'.$value.'">'.$value.'</a></span>';
			$output .= '</li>';

		// other fields
		} else {
			
			$output .= '<li class="field '.sanitize_key( esc_attr( $field ) ).'">';
			$output .= '<span class="label">'.esc_html( $label ).': </span>';
			$output .= '<span class="info">'.esc_attr( $value ).'</span></a>';
			$output .= '</li>';
		}
	}

	return $count == 0 ? false : '<ul class="gmw-location-meta gmw-additional-info-wrapper">'.$output.'</ul>';
}

	function gmw_location_meta_list( $location, $fields = array(), $labels = array() ) {
		echo gmw_get_location_meta_list( $location, $fields, $labels );
	}

/**
 * Get directions link
 * 
 * @param  object $location    location object
 * @param  array  $from_coords array of coords array( lat,lng )
 * @param  string $label       default "get directions"
 * 
 * @return [type]              [description]
 */
function gmw_get_directions_link( $location, $from_coords = array(), $label = '' ) {

    // if location ID pass get the location data
    if ( is_int( $location ) ) {
        
        $location = gmw_get_location_by_id( $location );
    
    // abort if not ID or object data
    } elseif ( ! is_object( $location ) ) {

        $location = false;
    }

    //abort if no coordinates
    if ( empty( $location->lat ) || empty( $location->lng ) ) {
        return;
    }

    $args = array(
        'to_lat' => $location->lat,
        'to_lng' => $location->lng
    );

    if ( isset( $from_coords[0] ) && isset( $from_coords[1] )  ) {
        
        $args['from_lat'] = $from_coords[0];
        $args['from_lng'] = $from_coords[1];
    
    } else {

        $user_coords = gmw_get_user_current_coords();

        if ( $user_coords != false ) {
            $args['from_lat'] = $user_coords['lat'];
            $args['from_lng'] = $user_coords['lng'];
        }
    }

    $args['units'] = ( empty( $location->units ) || $location->units == 'mi' ) ? 'imperial' : 'metric';
    
    if ( $label != '' ) {
        $args['label'] = $label;
    }

    return GMW_Maps_API::get_directions_link( $args );
}

    function gmw_directions_link( $location, $from_coords = array(), $label = '' ) {
        echo gmw_get_directions_link( $location, $from_coords, $label );
    }
