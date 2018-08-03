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
	return GMW_Location::get( $object_type, $object_id, $output, $cache );
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
	return GMW_Location::get_by_id( $location_id, $output, $cache );
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

	$location = gmw_get_location( $object_type, $object_id, OBJECT, $cache );
    
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
	return GMW_Location_Meta::get( $location_id, $meta_keys, $cache );
}

/**
 * Get location metadata by object type and object ID
 * 
 * @param  string  $object_type object_type object type ( post, user... )
 * @param  integer $object_id   object ID ( post ID, user ID.... )
 * @param  array   $meta_keys   string of a single or array of multiple meta keys to retrive their values
 * 
 * @return [type]  string || array of values
 */
function gmw_get_location_meta_by_object( $object_type = '', $object_id = 0, $meta_keys = array() ) {
	return GMW_Location_Meta::get_by_object( $object_type, $object_id, $meta_keys );
}

/**
 * Create / Update location meta
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
	return GMW_Location_Meta::update( $location_id, $meta_key, $meta_value );
}

/**
 * Create / Update multiple location metas
 * 
 * @param  integer $location_id 
 * @param  array   $metadata    location metadata in meta_key => meta value pairs
 * @param  mixed   $meta_value  can also update single meta by passing single meta_data and meta_vale
 *
 * @since 3.0 
 * 
 * @return int meta ID
 */
function gmw_update_location_metas( $location_id = 0, $metadata = array() , $meta_value = '' ) {
    return GMW_Location_Meta::update_metas( $location_id, $metadata, $meta_value );
}

/**
 * Delete location by object type and object ID
 * 
 * @param  string  $object_type [description]
 * @param  integer $object_id   [description]
 * @param  string  $meta_key    [description]
 * @return [type]               [description]
 */
function gmw_delete_location( $object_type = '', $object_id = 0, $delete_meta = false ) {
    return GMW_Location::delete( $object_type, $object_id, $delete_meta );
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
	return GMW_Location_Meta::delete( $location_id, $meta_key );
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
function gmw_delete_location_meta_by_object( $object_type = '', $object_id = 0, $meta_key = '' ) {
	return GMW_Location::delete_by_object( $object_type, $object_id, $meta_key );
}

/**
 * Update location data by passing an array with the complete location data.
 * 
 * @param  array  $location_data complete location data
 * 
 * @return int location ID
 */
function gmw_update_location_data( $location_data = array() ) {
    return GMW_Location::update( $location_data );
}

/**
 * Update location using object type, object ID and an address or coordinates.
 *
 * The function will geocode the address, or reverse geocode coords, and save it in the locations table in DB
 *
 * @since 3.0
 * 
 * @author Eyal Fitoussi
 * 
 * @param  string           $object_type   string ( post, user, comment.... )
 * @param  integer          $object_id     int ( post ID, user ID, comment ID... )
 * @param  string || array  $location      to pass an address it can be either a string or an array of address field for example:
 * 
 * $location = array(
 *      'street'    => 285 Fulton St,
 *      'apt'       => '',
 *      'city'      => 'New York',
 *      'state'     => 'NY',
 *      'zipcode'   => '10007',
 *      'country'   => 'USA'
 * );
 *
 * or pass a set of coordinates via an array of lat,lng. Ex 
 *
 * $location = array( 
 *     'lat' => 26.1345,
 *     'lng' => -80.4362
 * );
 * 
 * @param  integer $user_id      the user whom the location belongs to. By default it will belong to the user who creates/update the location ( logged in user ).
 * @param  boolean $force_refresh false to use geocoded address in cache || true to force address geocoding
 * 
 * @return int location ID
 */
function gmw_update_location( $object_type = '', $object_id = 0, $location = array(), $user_id = 0, $force_refresh = false ) {
    
    // abort if data is missing
    if ( empty( $object_type ) || empty( $object_id ) || empty( $location ) ) {
        return;
    }

    if ( empty( $user_id ) ) {
    	$user_id = get_current_user_id();
    }
 
    $geo_address = $location;
    $type        = 'address_single';

    // if location is an array
    if ( is_array( $location ) ) {
        
        if ( ! empty( $location['lat'] ) && ! empty( $location['lng'] ) ) {

            $type = 'coords';

            $geo_address = array(
                $location['lat'],
                $location['lng']
            );

        } else {

            $type = 'address_multiple';

            $defaults = array(
                'street'    => '',
                'apt'       => '',
                'city'      => '',
                'state'     => '',
                'zipcode'   => '',
                'country'   => ''
            );
          
            // Parse incoming $args into an array and merge it with $defaults
            $location = wp_parse_args( $location, $defaults );

            $geo_address = $location;

            // remove apt from address field to be able to geocode it properly
            unset( $geo_address['apt'] );

            $geo_address = implode( ' ', $geo_address );
        }
    }

    if ( ! function_exists( 'gmw_geocoder' ) ) {

    	trigger_error( 'Geocoder function not exists.', E_USER_NOTICE );

    	return false;
    }

    //geocode the location
    $geocoded_data = gmw_geocoder( $geo_address, $force_refresh );

    // abort if geocode failed
    if ( isset( $geocoded_data['error'] ) ) {
        
        do_action( 'gmw_udpate_location_failed', $geocoded_data, $object_type, $object_id, $location );

        return;
    }

    $latitude  = $geocoded_data['latitude'];
    $longitude = $geocoded_data['longitude'];

    // if multiple address field passed through array 
    // get the original address field entered
    if ( $type == 'address_multiple' ) {
 
        $street       = ! empty( $location['street'] ) ? sanitize_text_field( $location['street'] ) : $geocoded_data['street']; 
        $premise      = ! empty( $location['apt'] ) 	? sanitize_text_field( $location['apt'] ) : $geocoded_data['premise'];
        $city         = ! empty( $location['city'] ) ? sanitize_text_field( $location['city'] ) : $geocoded_data['city'];
        $postcode     = ! empty( $location['zipcode'] ) ? sanitize_text_field( $location['zipcode'] ) : $geocoded_data['postcode'];
        $region_code  = $geocoded_data['region_code'];
        $country_code = $geocoded_data['country_code'];
        $latitude     = $geocoded_data['lat'];
        $longitude    = $geocoded_data['lng'];

    } else {

        $street       = $geocoded_data['street'];
        $premise      = $geocoded_data['premise'];
        $city         = $geocoded_data['city'];
        $region_code  = $geocoded_data['region_code'];
        $postcode     = $geocoded_data['postcode'];
        $country_code = $geocoded_data['country_code'];

        if ( $type == 'coords' ) {

            $latitude  = $location['lat'];
            $longitude = $location['lng'];
            $location  = $geocoded_data['formatted_address'];
        }
    }

    // collect location data into array
    $location_data = array(
        'object_type'       => $object_type,
        'object_id'         => $object_id,
        'user_id'			=> $user_id,
        'latitude'          => $latitude,
        'longitude'         => $longitude,
        'street_number'     => $geocoded_data['street_number'],
        'street_name'       => $geocoded_data['street_name'],
        'street'            => $street,
        'premise'           => $premise,
        'neighborhood'      => $geocoded_data['neighborhood'],
        'city'              => $city,
        'county'            => $geocoded_data['county'],
        'region_name'       => $geocoded_data['region_name'],
        'region_code'       => $region_code,
        'postcode'          => $postcode,
        'country_name'      => $geocoded_data['country_name'],
        'country_code'      => $country_code,
        'address'           => is_array( $location ) ? implode( ' ', $location ) : $location,
        'formatted_address' => $geocoded_data['formatted_address'],
        'place_id'			=> $geocoded_data['place_id']
    );

    // modify the data if needed
    $location_data = apply_filters( "gmw_pre_update_location_data", $location_data, $object_type, $geocoded_data );
    $location_data = apply_filters( "gmw_pre_update_{$object_type}_location_data", $location_data, $geocoded_data );
    
    // Save information to database
    return gmw_update_location_data( $location_data );
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

    if ( empty( $fields ) ) {
        $fields = array( 'formatted_address' );
    }

    // if string convert to array
    if ( is_string( $fields ) ) {
    	$fields = explode( ',', $fields );
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
 * Get location meta values based on object type and object ID.
 *
 * Can retrive a single or multiple values, and use a separator between.
 *
 * @since 3.0.2
 *        
 * @param  string  $object_type object_type object type ( post, user... )
 * @param  boolean $object_id   object ID ( post ID, user ID.... )
 * @param  array   $fields      array of meta_keys to retrive thier values
 * @param  string  $separator   character to be used as separator between fields
 * 
 * @return string
 * 
 */
function gmw_get_location_meta_values( $object_type = false, $object_id = 0, $meta_keys = array( '' ), $separator = ', ' ) {

    if ( empty( $meta_keys ) ) {
        return;
    }

    // we must pass an array for multiple fields
    if ( is_string( $meta_keys ) ) {
        $meta_keys = explode( ',', $meta_keys );
    }

    // get location data
    $output = gmw_get_location_meta_by_object( $object_type, $object_id, $meta_keys );

    // abort if no metas found
    if ( empty( $output ) ) {
        return;
    }

    if ( ! empty( $separator ) ) {
        return implode( $output, $separator );
    }

    return $output;
}

/**
 * Get location fields - address fields or location meta.
 *
 * @since 3.0.2
 *        
 * @param  string  $object_type object_type object type ( post, user... )
 * @param  boolean $object_id   object ID ( post ID, user ID.... )
 * @param  array   $fields      array of address fields to retrive
 * @param  string  $separator   character to be used as separator between fields
 * 
 * @return string
 * 
 */
function gmw_get_location_fields( $object_type = false, $object_id = 0, $fields = array( 'formatted_address' ), $separator = ', ', $location_meta = 0 ) {

	// When we know for sure that we need location meta fields.
	if ( ! empty( $location_meta ) ) {

		return gmw_get_location_meta_values( $object_type, $object_id, $fields, $separator );

	} else {

		// try to get location field.
		$output = gmw_get_address_fields( $object_type, $object_id, $fields, $separator );

		// if location field was not found, try location meta.
		if ( empty( $output ) ) {
			$output = gmw_get_location_meta_values( $object_type, $object_id, $fields, $separator );
		}

		return $output;
	}
}

/**
 * gmw_location_fields shortcode
 *
 * Disply location fields or location meta.
 *
 * @since 3.0.2
 * 
 * @param  [type] $atts [description]
 * @return [type]       [description]
 */
function gmw_get_location_fields_shortcode( $atts ) {

    //default shortcode attributes
    $args = shortcode_atts( array(
        'object_type'   => 'post',
        'object_id'     => 0,
        'location_meta' => 0,
        'fields'        => '',
        'separator'     => ' ',
    ), $atts );

    return gmw_get_location_fields( $args['object_type'], $args['object_id'], $args['fields'], $args['separator'], $args['location_meta'] );
}
add_shortcode( 'gmw_location_fields', 'gmw_get_location_fields_shortcode' );

/**
 * Get specific address fields as an array.
 * 
 * @param  string  $object_type [description]
 * @param  integer $object_id   [description]
 * @param  array   $fields      [description]
 * @return [type]               [description]
 */
function gmw_get_location_address_fields( $object_type = 'post', $object_id = 0, $fields = array() ) {

    $location = gmw_get_location( $object_type, $object_id );

    if ( ! $location ) {
        return false;
    }

    if ( ! is_array( $fields ) ) {
        $fields = explode( ',',$fields );
    }

    if ( empty( $fields ) ) {

        $fields = array( 
            'lat',
            'lng',
            'latitude', 
            'longitude', 
            'street_number', 
            'street_name',
            'street',
            'premise',
            'neighborhood',
            'city',
            'county',
            'region_name',
            'region_code',
            'postcode',
            'country_name',
            'country_code',
            'address',
            'formatted_address'
        );
    }

    $output = array();

    return ( object ) array_intersect_key( ( array ) $location, array_flip( $fields ) );
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

	// if location is ID rather than object
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

    // in some cases object type might not provided. This might also be a non location.
    if ( ! empty( $location->object_type ) ) {
	   $output = apply_filters( "gmw_{$location->object_type}_location_address", $output, $location, $fields, $gmw );
    }

	return ! empty( $output ) ? stripslashes( esc_attr( $output ) ) : '';
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
 * Check if is featured object.
 *
 * @param  [type] $object_type [description]
 * @param  [type] $object_id   [description]
 * @return [type]              [description]
 */
function gmw_is_featured_object( $object_type = 'post', $object_id = 0 ) {

	$featured = false;

	if ( 'post' === $object_type ) {

		$featured = get_post_meta( $object_id, 'gmw_featured_object', true );

	} elseif ( 'user' === $object_type ) {

		$featured = get_user_meta( $object_id, 'gmw_featured_object', true );

	} elseif ( 'bp_group' === $object_type ) {

		$featured = groups_get_groupmeta( $object_id, 'gmw_featured_object', true );

	} else {

		$featured = apply_filters( 'gmw_is_featured_object', $featured, $object_type, $object_id );
	}

	return ! empty( $featured ) ? true : false;
}

/**
 * Update featured object value.
 *
 * @param  string  $object_type [description]
 * @param  integer $object_id   [description]
 * @param  integer $value       [description]
 * @return [type]               [description]
 */
function gmw_update_featured_object( $object_type = 'post', $object_id = 0, $value = 0 ) {

	if ( 'post' === $object_type ) {

		update_post_meta( $object_id, 'gmw_featured_object', $value );

	} elseif ( 'user' === $object_type ) {

		update_user_meta( $object_id, 'gmw_featured_object', $value );

	} elseif ( 'bp_group' === $object_type ) {

		groups_update_groupmeta( $object_id, 'gmw_featured_object', $value );

	} else {
		do_action( 'gmw_update_featured_object', $object_type, $object_id, $value );
	}

	// lets update featured location as well.
	gmw_update_featured_location( $object_type, $object_id, $value );

	return;
}

/**
 * Check if featured location.
 *
 * @param  [type] $object_type [description]
 * @param  [type] $object_id   [description]
 * @return [type]              [description]
 */
function gmw_is_featured_location( $object_type, $object_id ) {

	global $wpdb;

	$featured = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT `featured` 
			FROM {$wpdb->prefix}gmw_locations 
			WHERE object_type = %s
			AND object_id = %d",
			$object_type, $object_id
		)
	);

	return ! empty( $featured ) ? true : false;
}

/**
 * Update featured location value.
 *
 * @param  string  $object_type [description]
 * @param  integer $object_id   [description]
 * @param  integer $value       [description]
 * @return [type]               [description]
 */
function gmw_update_featured_location( $object_type = 'post', $object_id = 0, $value = 0 ) {

	global $wpdb;

	// update location, if exists, with featured value.
	$wpdb->update(
		$wpdb->base_prefix . 'gmw_locations',
		array(
			'featured' => $value,
		),
		array(
			'object_id'   => $object_id,
			'object_type' => $object_type,
		),
		array(
			'%d',
			'%d',
			'%s',
		)
	);

	return;
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

		// look for location meta in the object.
        // It might generated during the loop.
		if ( ! empty( $location->location_meta ) ) {

			$location_meta = $location->location_meta;
		
        } else {

            // can sometimes be location_id in some loops
            if ( isset( $location->location_id ) ) {
                
                $location_id = $location->location_id;
            
            // otherwwise, it might be saved as ID
            } elseif ( isset( $location->ID ) ) {

                $location_id = $location->ID;
            
            // return if no location ID 
            } else {
                return;
            }

            // get the location meta
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
	$labels = apply_filters( 'gmw_get_location_meta_list_labels', $labels, $fields, $location );

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
			
			$value = sanitize_email( $value );

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
            
        // maybe coords in latitude, longitude
        if ( empty( $location->latitude ) || empty( $location->longitude ) ) {
            
            return;
        
        } else {
            $location->lat = $location->latitude;
            $location->lng = $location->longitude;
        }
    }
   
    $args = array(
        'to_lat' => $location->lat,
        'to_lng' => $location->lng
    );

    if ( ! empty( $from_coords[0] ) && ! empty( $from_coords[1] )  ) {
        $args['from_lat'] = $from_coords[0];
        $args['from_lng'] = $from_coords[1];
    } else {
        //$user_coords = gmw_get_user_current_coords();

        if ( ! empty( $_COOKIE['gmw_ul_lat'] ) && ! empty( $_COOKIE['gmw_ul_lng'] ) ) {
            $args['from_lat'] = urldecode( $_COOKIE['gmw_ul_lat'] );;
            $args['from_lng'] = urldecode( $_COOKIE['gmw_ul_lng'] );;
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
