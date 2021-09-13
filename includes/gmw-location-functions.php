<?php
/**
 * GEO my WP - location functions.
 *
 * The class queries posts based on location.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if location exists in database.
 *
 * @param  integer $location_id location ID.
 *
 * @return boolean
 */
function gmw_is_location_exists( $location_id = 0 ) {
	return GMW_Location::exists( $location_id );
}

/**
 * Get a location object.
 *
 * Accepts either a location ID integer or an array of object type and object ID as the first argument
 *
 * @Since 3.0
 *
 * @param  mixed  integer || array $args ( int ) location ID || ( array ) array( 'object_type' => '', 'object_id' => 0 ).
 * @param  constant                $output    OBJECT || ARRAY_A || ARRAY_N.
 * @param  boolean                 $cache     look for location in cache?.
 *
 * @return object  the complete location object.
 */
function gmw_get_location( $args = 0, $output = OBJECT, $cache = true ) {

	// when specific location ID provided.
	if ( gmw_verify_id( $args ) ) {

		return GMW_Location::get( $args, $output, $cache );

		// when array of object type and object ID.
	} elseif ( is_array( $args ) ) {

		$object_type = ! empty( $args['object_type'] ) ? $args['object_type'] : 'post';
		$object_id   = ! empty( $args['object_id'] ) ? $args['object_id'] : 0;

		return GMW_Location::get_by_object( $object_type, $object_id, $output, $cache );

		// deprecated way of passing arguments. Use one of the methods above first argument instead.
	} elseif ( is_string( $args ) && gmw_verify_id( $output ) ) {

		/** gmw_trigger_error( 'You are using a deprecated way of passing arguments to the gmw_get_location() function. Do not pass object type and object ID as the first and second arguments. Instead, pass the location ID or an array of object_type and object_id key/values as the first argument.' ); */

		return GMW_Location::get_by_object( $args, $output, $cache );

	} else {
		return;
	}
}

/**
 * Get Location object by location ID
 *
 * @since 3.0
 *
 * @param  integer  $location_id the location ID.
 * @param  constant $output      OBJECT | ARRAY_A | ARRAY_N.
 * @param  boolean  $cache       look for location in cache first.
 *
 * @uses gmw_get_location();
 *
 * @return object              the complete location object
 */
function gmw_get_location_by_id( $location_id = 0, $output = OBJECT, $cache = true ) {
	return gmw_get_location( $location_id, $output, $cache );
}

/**
 * Get a location by object type and object ID pair.
 *
 * @Since 3.2
 *
 * @param  string   $object_type  the type of object we are looking for ( post, user...).
 * @param  int      $object_id    object ID ( post ID, user ID... ).
 * @param  constant $output       OBJECT | ARRAY_A | ARRAY_N.
 * @param  boolean  $cache        look for location in cache first.
 *
 * @return object              complete location data.
 */
function gmw_get_location_by_object( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {
	return GMW_Location::get_by_object( $object_type, $object_id, $output, $cache );
}

/**
 * Get the location ID by object type and object ID.
 *
 * @Since 3.0
 *
 * @param  string  $object_type  the type of object we are looking for ( post, user...).
 * @param  int     $object_id    object ID ( post ID, user ID... ).
 * @param  boolean $cache        look for location ID in cache first?.
 *
 * @return int - location ID
 */
function gmw_get_location_id( $object_type = 'post', $object_id = 0, $cache = true ) {

	$location = gmw_get_location_by_object( $object_type, $object_id, OBJECT, $cache );

	return ! empty( $location->ID ) ? (int) $location->ID : false;
}

/**
 * Get all locations of an object based on object type and object ID.
 *
 * When multiple location per object exist.
 *
 * @Since 3.2
 *
 * @param  string   $object_type  the type of object we are looking for ( post, user...).
 * @param  int      $object_id    object ID ( post ID, user ID... ).
 * @param  constant $output       OBJECT | ARRAY_A | ARRAY_N the output of each location in the array.
 * @param  boolean  $cache        look for location in cache first?.
 *
 * @return array            array of locations.
 */
function gmw_get_locations( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {
	return GMW_Location::get_locations_by_object( $object_type, $object_id, $output, $cache );
}

/**
 * Delete a location.
 *
 * First argument accepts either an integer as a location ID
 *
 * to delete a specific location ( mostly to be used when multiple locations per object exist )
 *
 * or an array of object_type and object_id to delete the default location of an object.
 *
 * @Since 3.0
 *
 * @param mixed   $args        int as location ID || array of object_type and object_id.
 * @param boolean $delete_meta location meta as well?.
 *
 * @return integer              ID of the deleted location.
 */
function gmw_delete_location( $args = 0, $delete_meta = true ) {

	// when location ID provided.
	if ( gmw_verify_id( $args ) ) {

		return GMW_Location::delete( $args, $delete_meta );

		// when array of object type and object ID.
	} elseif ( is_array( $args ) ) {

		$object_type = ! empty( $args['object_type'] ) ? $args['object_type'] : '';
		$object_id   = ! empty( $args['object_id'] ) ? $args['object_id'] : 0;

		return gmw_delete_location_by_object( $object_type, $object_id, $delete_meta );

		// deprecated way of passing arguments. Use the methods above.
	} elseif ( is_string( $args ) && gmw_verify_id( $delete_meta ) ) {

		gmw_trigger_error( 'You are using a deprecated way of passing arguments to the gmw_delete_location() function. Do not pass object type and object ID as the first and second arguments. Instead, pass the location ID or an array of object_type and object_id key/values as the first argument.' );

		return gmw_delete_location_by_object( $args, $delete_meta, true );

	} else {
		return false;
	}
}

/**
 * Delete location by object.
 *
 * Delete the default location of an object.
 *
 * @param  string  $object_type  the type of object we are looking for ( post, user...).
 * @param  int     $object_id    object ID ( post ID, user ID... ).
 * @param  boolean $delete_meta  location meta as well?.
 *
 * @return integer              ID of the deleted location.
 */
function gmw_delete_location_by_object( $object_type = '', $object_id = 0, $delete_meta = true ) {
	return GMW_Location::delete_by_object( $object_type, $object_id, $delete_meta );
}

/**
 * Get location meta by location ID.
 *
 * @param  integer         $location_id location ID.
 * @param  string || array $meta_keys   single meta key as a sting, mutiple keys as string comma separated, or array of keys.
 * @param  boolean         $cache       use cached version?.
 *
 * @return string || array
 */
function gmw_get_location_meta( $location_id = 0, $meta_keys = '', $cache = true ) {
	return GMW_Location_Meta::get( $location_id, $meta_keys, $cache );
}

/**
 * Get location metadata by object type and object ID
 *
 * @param  string  $object_type object_type object type ( post, user... ).
 * @param  integer $object_id   object ID ( post ID, user ID.... ).
 * @param  array   $meta_keys   string of a single or array of multiple meta keys to retrieve their values.
 *
 * @return [type]  string || array of values
 */
function gmw_get_location_meta_by_object( $object_type = '', $object_id = 0, $meta_keys = array() ) {
	return GMW_Location_Meta::get_by_object( $object_type, $object_id, $meta_keys );
}

/**
 * Create / Update location meta
 *
 * @param  integer $location_id the location ID.
 * @param  string  $meta_key    meta key to update.
 * @param  string  $meta_value  the new meta value.
 *
 * @since 3.0
 *
 * @return int meta ID
 */
function gmw_update_location_meta( $location_id = 0, $meta_key = '', $meta_value = '' ) {
	return GMW_Location_Meta::update( $location_id, $meta_key, $meta_value );
}

/**
 * Create / Update single or multiple location metas.
 *
 * @param  integer $location_id location ID.
 * @param  array   $metadata    location metadata in meta_key => meta value pairs.
 * @param  mixed   $meta_value  can also update a single location meta by passing the meta_key as
 *
 * a string to the metadata argument and the meta_value as the third argument,.
 *
 * @since 3.0
 *
 * @return int meta ID
 */
function gmw_update_location_metas( $location_id = 0, $metadata = array(), $meta_value = '' ) {
	return GMW_Location_Meta::update_metas( $location_id, $metadata, $meta_value );
}

/**
 * Delete location meta
 *
 * @param  integer $location_id [description].
 * @param  string  $meta_key    [description].
 *
 * @return [type]               [description]
 */
function gmw_delete_location_meta( $location_id = 0, $meta_key = '' ) {
	return GMW_Location_Meta::delete( $location_id, $meta_key );
}

/**
 * Delete location metadata by object type and object ID
 *
 * @param  string $object_type object_type object type ( post, user... ).
 * @param  int    $object_id   object ID ( post ID, user ID.... ).
 * @param  string $meta_key    meta key to delete.
 *
 * @return [type]  string || array of values
 */
function gmw_delete_location_meta_by_object( $object_type = '', $object_id = 0, $meta_key = '' ) {
	return GMW_Location::delete_by_object( $object_type, $object_id, $meta_key );
}

/**
 * Insert new location by passing a complete location data exept for location ID ( ID argument ).
 *
 * @param  array $location_data location data.
 *
 * @return integer                new location ID
 */
function gmw_insert_location( $location_data = array() ) {
	return GMW_Location::insert( $location_data );
}

/**
 * Update / create location data by passing an array with the complete location data.
 *
 * If location does not exist and location ID ( ID argument ) was not provided,
 *
 * The function will create a new location based on object type and object ID.
 *
 * @param  array $location_data complete location data.
 *
 * @return int location ID
 */
function gmw_update_location_data( $location_data = array() ) {
	return GMW_Location::update( $location_data );
}

/**
 * Set location status.
 *
 * @param  integer $location_id the location ID.
 * @param  integer $status      1 to activate location || 0 to deactivate location.
 *
 * @return location status      status 1 || 0.
 */
function gmw_set_location_status( $location_id = 0, $status = 1 ) {
	return GMW_Location::set_status( $location_id, $status );
}

/**
 * Update or create a location.
 *
 * You can update a specific location by providing a location ID
 *
 * ( mostly used when multiple locations per object exist ),
 *
 * or update/create the default location of an object by passing object type and object ID.
 *
 * The function can geocode a single or multiple address fields,
 *
 * or reverse geocode coordinates, and save it in the locations table in DB.
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 *
 * @param  array   $args = array(
 *     'location_id'   => 0, // ( int ) pass specific location ID when updating a specific location. Otherwise, the plugin will get the default location based on object type and object ID.
 *     'object_type'   => '' // ( string ) required if location ID was not provided.
 *     'object_id'     => 0, // ( int ) required if location ID was not provided.
 *     'location_name' => '' // ( string )location name. this is optional and not being used by default.
 *     'user_id'       => 0, // the user whom the location belongs to. Logged in user will be used by default if not provided.
 * );.
 *
 * @param  mixed   $location string || array - to pass an address it can be either a string or an array of address field or pass array of coordinartes. See examples below:
 *
 *  // Single address field as a string
 *  $location = '285 Fulton St New York, NY 10007 USA';
 *
 *  // Multiple address fields as an array
 *  $location = array(
 *      'street'    => 285 Fulton St,
 *      'apt'       => '',
 *      'city'      => 'New York',
 *      'state'     => 'NY',
 *      'zipcode'   => '10007',
 *      'country'   => 'USA'
 *  );
 *
 *  // Coordinates as an array.
 *  $location = array(
 *      'lat' => 26.1345,
 *      'lng' => -80.4362
 *  );.
 *
 * @param  boolean $force_refresh false to use the cached geocoded location || true to force new location geocoding.
 *
 * @param  integer $dep_user_id       deprecated.
 * @param  boolean $dep_force_refresh deprecated.
 *
 * @return int location ID
 */
function gmw_update_location( $args = array(), $location = array(), $force_refresh = array(), $dep_user_id = 0, $dep_force_refresh = false ) {

	// abort if missing arguments or location.
	if ( empty( $args ) || empty( $location ) ) {
		return;
	}

	// deprecated way of passing arguments. It is here for backward compatibilty.
	if ( is_string( $args ) && gmw_verify_id( $location ) ) {

		gmw_trigger_error( 'You are using a deprecated way of passing arguments to the gmw_update_location() function. Do not pass object type and object ID as first and second arguments. Instead, use an array as the first argument.' );

		$args = array(
			'location_id'   => 0,
			'object_type'   => $args,
			'object_id'     => $location,
			'location_name' => '',
			'user_id'       => $dep_user_id,
		);

		$location      = $force_refresh;
		$force_refresh = $dep_force_refresh;

		// new way of passing data via an array.
	} elseif ( is_array( $args ) ) {

		$args = wp_parse_args(
			$args,
			array(
				'location_id'   => 0,
				'object_type'   => '',
				'object_id'     => 0,
				'location_name' => '',
				'user_id'       => 0,
			)
		);

	} else {

		return;
	}

	// if specific location ID provided we will try to update this location.
	if ( ! empty( $args['location_id'] ) ) {

		$location_id    = $args['location_id'];
		$saved_location = gmw_get_location( $location_id );

		// abort if location does not exist.
		if ( empty( $saved_location ) ) {
			return false;
		}

		$object_type = $saved_location->object_type;
		$object_id   = $saved_location->object_id;

	} elseif ( ! empty( $args['object_type'] ) && ! empty( $args['object_id'] ) ) {

		$location_id = 0;
		$object_type = $args['object_type'];
		$object_id   = $args['object_id'];

	} else {

		return;
	}

	$location_name = $args['location_name'];
	$user_id       = ! empty( $args['user_id'] ) ? $args['user_id'] : get_current_user_id();
	$geo_address   = $location;
	$type          = 'address_single';

	// if location is an array.
	if ( is_array( $location ) ) {

		// check if coordinates provided.
		if ( ! empty( $location['lat'] ) && ! empty( $location['lng'] ) ) {

			$type = 'coords';

			$geo_address = array(
				$location['lat'],
				$location['lng'],
			);

			// otherwise, it should be multiple address fields.
		} else {

			$type = 'address_multiple';

			$defaults = array(
				'street'  => '',
				'apt'     => '',
				'city'    => '',
				'state'   => '',
				'zipcode' => '',
				'country' => '',
			);

			// Parse incoming $args into an array and merge it with $defaults.
			$location    = wp_parse_args( $location, $defaults );
			$geo_address = $location;

			// remove apt from address field to be able to geocode it properly.
			unset( $geo_address['apt'] );

			// generate the address into a single line.
			$geo_address = implode( ' ', $geo_address );

			// remove some extra blank spaces which can mess up the geocoding.
			$geo_address = str_replace( '  ', ' ', $geo_address );
		}
	}

	// verify that geocoder function exists.
	if ( ! function_exists( 'gmw_geocoder' ) ) {

		gmw_trigger_error( 'Geocoder function not exists.' );

		return false;
	}

	// geocode the location.
	$geocoded_data = gmw_geocoder( $geo_address, $force_refresh );

	// abort if geocode failed.
	if ( isset( $geocoded_data['error'] ) ) {

		// Deprecated due to typo. Will be removed in the future.
		do_action( 'gmw_udpate_location_failed', $geocoded_data, $object_type, $object_id, $location );

		do_action( 'gmw_update_location_failed', $geocoded_data, $object_type, $object_id, $location );

		return;
	}

	$latitude  = $geocoded_data['latitude'];
	$longitude = $geocoded_data['longitude'];

	// if multiple address field provided preserve their values.
	if ( 'address_multiple' === $type ) {

		$street       = ! empty( $location['street'] ) ? sanitize_text_field( $location['street'] ) : $geocoded_data['street'];
		$premise      = ! empty( $location['apt'] ) ? sanitize_text_field( $location['apt'] ) : $geocoded_data['premise'];
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

		if ( 'coords' === $type ) {

			$latitude  = $location['lat'];
			$longitude = $location['lng'];
			$location  = $geocoded_data['formatted_address'];
		}
	}

	// collect location data into array.
	$location_data = array(
		'ID'                => $location_id,
		'object_type'       => $object_type,
		'object_id'         => $object_id,
		'user_id'           => $user_id,
		'title'             => $location_name,
		'latitude'          => (float) $latitude,
		'longitude'         => (float) $longitude,
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
		'place_id'          => $geocoded_data['place_id'],
	);

	// modify the data if needed.
	$location_data = apply_filters( 'gmw_pre_update_location_data', $location_data, $object_type, $geocoded_data );
	$location_data = apply_filters( "gmw_pre_update_{$object_type}_location_data", $location_data, $geocoded_data );

	// Save information to database.
	return gmw_update_location_data( $location_data );
}

/**
 * Get specific location address fields.
 *
 * @param  array $args array of arguments.
 *
 * @return address field.
 */
function gmw_get_location_address_fields( $args = array() ) {
	return gmw_get_address_fields( $args );
}

/**
 * Get specific address fields of a location.
 *
 * @Since 3.0
 *
 * @param  array   $args          object type or location ID.
 * @param  integer $dep_object_id object ID ( post ID, user ID... ). Use when first argument is object type.
 * @param  array   $dep_fields    the address fields to output.
 * @param  string  $dep_separator separator to be used between the different address fields.
 *
 * @return string   the address fields of a location.
 */
function gmw_get_address_fields( $args = array(), $dep_object_id = 0, $dep_fields = array( 'formatted_address' ), $dep_separator = ', ' ) {

	$location = array();

	if ( is_array( $args ) ) {

		$args = wp_parse_args(
			$args,
			array(
				'location_id' => 0,
				'object_type' => '',
				'object_id'   => 0,
				'fields'      => 'formatted_address',
				'separator'   => ', ',
				'output'      => 'string',
			)
		);

		$args = apply_filters( 'gmw_get_address_fields_args', $args );

		// get location by location ID.
		if ( ! empty( $args['location_id'] ) ) {

			// get location data.
			$location = gmw_get_location( $args['location_id'] );

		} else {

			$location = gmw_get_location_by_object( $args['object_type'], $args['object_id'] );
		}

		$fields      = $args['fields'];
		$separator   = $args['separator'];
		$output_type = $args['output'];

		// deprecated way of passing args. use an array as first argument.
	} elseif ( is_string( $args ) && gmw_verify_id( $dep_object_id ) ) {

		gmw_trigger_error( 'You are using a deprecated way of passing arguments to the gmw_get_address_fields() function. Do not pass object type and object ID as first and second arguments. Instead, use an array as the first argument.' );

		$location    = gmw_get_location_by_object( $args, $dep_object_id );
		$fields      = $dep_fields;
		$separator   = $dep_separator;
		$output_type = 'string';

	} else {

		return false;
	}

	if ( empty( $location ) ) {
		return false;
	}

	// all address fields.
	$all_fields = array(
		'title',
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
		'formatted_address',
	);

	if ( empty( $fields ) ) {
		$fields = array( 'formatted_address' );
	}

	// if string convert to array.
	if ( is_string( $fields ) ) {
		$fields = explode( ',', $fields );
	}

	// if showing all fields.
	if ( empty( $fields ) || '*' === $fields[0] ) {

		$output = array_intersect_key( (array) $location, array_flip( $all_fields ) );

		// when showing specific fields.
	} else {

		$output = array();

		// loop trough fields and get the specified address fields.
		foreach ( $fields as $field ) {

			// check old field names for backward compatibilty.
			if ( 'country' === $field ) {
				$field = 'country_name';
			}

			if ( 'state' === $field ) {
				$field = 'region_name';
			}

			if ( 'zipcode' === $field ) {
				$field = 'postcode';
			}

			if ( 'lng' === $field ) {
				$field = 'longitude';
			}

			if ( 'lat' === $field ) {
				$field = 'latitude';
			}

			// verify that field is allowed and exists in the location.
			if ( in_array( $field, $all_fields, true ) && isset( $location->$field ) ) {
				$output[ $field ] = $location->$field;
			}
		}
	}

	// array.
	if ( 'array' === $output_type ) {

		return $output;

		// object.
	} elseif ( 'object' === $output_type ) {

		return (object) $output;

		// string.
	} else {

		return implode( $separator, $output );
	}
}

/**
 * Get location meta values.
 *
 * Can retrieve a single or multiple values, and use a separator between.
 *
 * @since 3.0.2
 *
 * @param  mixed  $args       int as location ID || array of object_type and object ID.
 * @param  array  $meta_keys  array of meta_keys to retrieve.
 * @param  string $separator  characters to be used as separator between fields.
 * @param  string $dep_sep    deprecated separator argument.
 *
 * @return string
 */
function gmw_get_location_meta_values( $args = 0, $meta_keys = '', $separator = ', ', $dep_sep = ', ' ) {

	// get location meta by location ID.
	if ( gmw_verify_id( $args ) ) {

		$output = gmw_get_location_meta( $args, $meta_keys );

		// get location meta by object type object ID.
	} elseif ( is_array( $args ) ) {

		$object_type = ! empty( $args['object_type'] ) ? $args['object_type'] : '';
		$object_id   = ! empty( $args['object_id'] ) ? $args['object_id'] : 0;

		// get location data.
		$output = gmw_get_location_meta_by_object( $object_type, $object_id, $meta_keys );

		// deprecated way of passing args. Use one of the methods above.
	} elseif ( is_string( $args ) && gmw_verify_id( $meta_keys ) ) {

		gmw_trigger_error( 'You are using a deprecated way of passing arguments to the gmw_get_location_meta_values() function. Do not pass object type and object ID as first and second arguments. Instead, pass location ID or array of object type and object ID as the first argument.' );

		$object_type = $args;
		$object_id   = $meta_keys;
		$meta_keys   = $separator;
		$separator   = $dep_sep;

		$output = gmw_get_location_meta_by_object( $object_type, $object_id, $meta_keys );

	} else {
		return;
	}

	// abort if no metas found.
	if ( empty( $output ) ) {
		return;
	}

	if ( is_array( $output ) && ! empty( $separator ) ) {
		return implode( $output, $separator );
	}

	return $output;
}

/**
 * Get specific location or location meta fields
 *
 * @since 3.0.2
 *
 * @param  array   $args = array(
 *     'location_id'   => 0,      // ( int ) if getting meta of a specific location
 *                                   ( when multiple location per object exist ).
 *     'object_type'   => 'post', // ( sting ) requried if location ID is not provided.
 *     'object_id'     => 0,      // ( int ) requried if location ID is not provided.
 *     'fields'        => 'formatted_address', // location or location meta field as array or comma separated
 *     'separator'     => ' ',    // characters to separate between the different fields.
 *     'location_meta' => 0,      // set to true if the field are location meta instead of location.
 * );.
 *
 * @param integer $dep_object_id     object ID - deprecated.
 * @param array   $dep_fields        array of fields - deprecated.
 * @param string  $dep_separator     separator - deprecated.
 * @param boolean $dep_location_meta deprecated.
 * @return string
 */
function gmw_get_location_fields( $args = array(), $dep_object_id = 0, $dep_fields = array( 'formatted_address' ), $dep_separator = ', ', $dep_location_meta = 0 ) {

	// deprecated way for passing arguments.
	if ( is_string( $args ) && gmw_verify_id( $dep_object_id ) ) {

		/** Trigger_error( 'You are using a deprecated way of passing arguments to the gmw_get_location_fields() function. Do not pass object type and object ID as first and second arguments. Instead, pass all arguments in an array as the first argument.', E_USER_NOTICE ); */

		$args = array(
			'location_id'   => 0,
			'object_type'   => $args,
			'object_id'     => $dep_object_id,
			'fields'        => $dep_fields,
			'separator'     => $dep_separator,
			'location_meta' => $dep_location_meta,
		);

		// abort if args is empty.
	} elseif ( ! is_array( $args ) || empty( $args ) ) {
		return false;
	}

	$args = wp_parse_args(
		$args,
		array(
			'location_id'   => 0,
			'object_type'   => 'post',
			'object_id'     => 0,
			'fields'        => 'formatted_address',
			'separator'     => ' ',
			'location_meta' => 0,
		)
	);

	// modify the args.
	$args = apply_filters( 'gmw_get_location_fields_args', $args );

	// get the fields.
	$output = gmw_get_address_fields( $args );

	// When we know for sure that we need location meta fields.
	if ( ! empty( $args['location_meta'] ) || empty( $output ) ) {

		if ( ! empty( $args['location_id'] ) ) {

			$loc_args = (int) $args['location_id'];

		} else {

			$loc_args = array(
				'object_type' => $args['object_type'],
				'object_id'   => (int) $args['object_id'],
			);
		}

		$output = gmw_get_location_meta_values( $loc_args, $args['fields'], $args['separator'] );
	}

	return $output;
}

/**
 * Location fields shortcode
 *
 * Disply location fields or location meta.
 *
 * @since 3.0.2
 *
 * DEPRECATED.
 *
 * @param  array $args [description].
 *
 * @return [type]       [description]
 */
function gmw_get_location_fields_shortcode( $args ) {
	return gmw_get_location_fields( $args );
}

/**
 * Get the address of an object
 *
 * Usually will be used in the search results.
 *
 * @param  mixed $location location object or location ID.
 * @param  mixed $fields   address fields as array or comma separated string.
 * @param  array $gmw      the form being used if in the search results.
 *
 * @return string       address
 */
function gmw_get_location_address( $location, $fields = array( 'formatted_address' ), $gmw = array() ) {

	if ( ! empty( $fields['addon'] ) ) {

		$gmw    = $fields;
		$fields = array( 'formatted_address' );

		gmw_trigger_error( 'Since GEO my WP 3.0 gmw_get_location_address function excepts an additional $fields argument. You need to modify the arguments pass to the function. gmw_get_location_address( $location, $fields, $gmw ).' );
	}

	$fields = apply_filters( 'gmw_get_location_address_fields', $fields, $location, $gmw );

	if ( ! is_array( $fields ) ) {
		$fields = explode( ',', $fields );
	}

	if ( empty( $fields ) ) {
		return;
	}

	// if location is ID rather than object get the location.
	if ( gmw_verify_id( $location ) ) {

		// get location by ID.
		$location = gmw_get_location( $location );

		// otherwise, abort if location is not an object.
	} elseif ( ! is_object( $location ) ) {
		$location = false;
	}

	// abort if location not exists.
	if ( empty( $location ) ) {
		return false;
	}

	$output = '';

	// loop trough fields and get the specified address fields.
	foreach ( $fields as $field ) {

		if ( 'zipcode' === $field ) {
			$field = 'postcode';
		}

		if ( 'country' === $field ) {
			$field = 'country_name';
		}

		if ( 'state' === $field ) {
			$field = 'region_name';
		}

		if ( isset( $location->$field ) ) {
			$output .= $location->$field . ' ';
		}
	}

	// modify the output address.
	$output = apply_filters( 'gmw_location_address', $output, $location, $fields, $gmw );

	// in some cases object type might not provided. This might also be a non location.
	if ( ! empty( $location->object_type ) ) {
		$output = apply_filters( "gmw_{$location->object_type}_location_address", $output, $location, $fields, $gmw );
	}

	// abort if no address found.
	if ( empty( $output ) ) {
		return '';
	}

	// To enable HTML in the address pass an array with the allowed attributes.
	$allowed_html = apply_filters( 'gmw_get_location_address_allowed_html', false );

	if ( empty( $allowed_html ) ) {
		return stripslashes( esc_html( $output ) );
	} else {
		return stripslashes( wp_kses( $output, $allowed_html ) );
	}
}

/**
 * Display the address of an object.
 *
 * @param  object $location the location object.
 * @param  array  $fields   array of address fields.
 * @param  array  $gmw      gmw form.
 */
function gmw_location_address( $location, $fields = array(), $gmw = array() ) {
	echo gmw_get_location_address( $location, $fields, $gmw ); // WPSC: XSS ok.
}

/**
 * Generate the address of a location as a link to google maps.
 *
 * Usually will be used in the search results.
 *
 * @param  mixed $location location object or location ID.
 * @param  mixed $fields   address field as array or comma separated string.
 * @param  array $gmw      the form being used if in the search results.
 *
 * @return string       address
 */
function gmw_get_linked_location_address( $location, $fields = array( 'formatted_address' ), $gmw = array() ) {

	// get the address.
	$address = gmw_get_location_address( $location, $fields, $gmw );

	if ( empty( $address ) ) {
		return;
	}

	return '<a href="https://maps.google.com/?q=' . $address . '" target="_blank">' . $address . '</a>';
}

/**
 * Check if is featured object.
 *
 * @param  stting  $object_type object type.
 * @param  integer $object_id   object ID.
 *
 * @return [type]              [description].
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
 * @param  string  $object_type object type.
 * @param  integer $object_id   object ID.
 * @param  integer $value       value.
 *
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
	return gmw_update_featured_location( $object_type, $object_id, $value );
}

/**
 * Check if featured location.
 *
 * @param  [type] $object_type [description].
 * @param  [type] $object_id   [description].
 *
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
			$object_type,
			$object_id
		)
	); // WPCS: db call ok, cache ok.

	return ! empty( $featured ) ? true : false;
}

/**
 * Update featured location value.
 *
 * @param  string  $object_type object type.
 * @param  integer $object_id   object ID.
 * @param  integer $value       value.
 */
function gmw_update_featured_location( $object_type = 'post', $object_id = 0, $value = 0 ) {

	global $wpdb;

	// update location, if exists, with featured value.
	$updated = $wpdb->update(
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
	); // WPCS: db call ok, cache ok.

	if ( $updated ) {
		do_action( 'gmw_featured_location_updated', $object_type, $object_id, $value );
	}
}

/**
 * Update parent location value.
 *
 * @param  integer $object_type object type.
 *
 * @param  integer $object_id   object id.
 */
function gmw_unset_object_parent_locations( $object_type = 'post', $object_id = 0 ) {

	$object_id = absint( $object_id );

	if ( empty( $object_id ) ) {

		gmw_trigger_error( 'Object ID is missing when trying to unset parent locations' );

		return false;
	}

	global $wpdb;

	$updated = $wpdb->query(
		$wpdb->prepare(
			"
            UPDATE {$wpdb->base_prefix}gmw_locations 
            SET   `parent`      = '0' 
            WHERE `object_type` = %s
            AND   `object_id`   = %d",
			array( $object_type, $object_id )
		)
	); // WPCS: db call ok, cache ok.

	if ( $updated ) {
		do_action( 'gmw_object_parent_locations_unset', $object_type, $object_id );
	}

	return $updated;
}

/**
 * Update parent location value.
 *
 * @param  integer $location_id object type.
 *
 * @param  integer $value       value.
 */
function gmw_update_parent_location( $location_id = 0, $value = 1 ) {

	$locations_id = absint( $location_id );
	$value        = ! empty( absint( $value ) ) ? 1 : 0;

	if ( empty( $location_id ) ) {

		gmw_trigger_error( 'Location ID is missing when trying to update parent location' );

		return false;
	}

	global $wpdb;

	$location = gmw_get_location( $location_id );

	gmw_unset_object_parent_locations( $location->object_type, $location->object_id );

	// update location, if exists, with featured value.
	$updated = $wpdb->update(
		$wpdb->base_prefix . 'gmw_locations',
		array(
			'parent' => $value,
		),
		array(
			'ID' => $location_id,
		),
		array(
			'%d',
			'%d',
		)
	); // WPCS: db call ok, cache ok.

	if ( $updated ) {
		do_action( 'gmw_parent_location_updated', $location_id, $value );
	}

	return $updated;
}

/**
 * Check if parent location.
 *
 * @param  integer $location_id object type.
 *
 * @return [type]              [description]
 */
function gmw_is_parent_location( $location_id ) {

	global $wpdb;

	$parent = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT `parent` 
			FROM {$wpdb->prefix}gmw_locations 
			WHERE ID = %s",
			$location_id
		)
	); // WPCS: db call ok, cache ok.

	return ! empty( $parent ) ? true : false;
}

/**
 * Get the location types from database.
 *
 * @param  array $args [description].
 *
 * @return [type]       [description]
 */
function gmw_get_registered_location_types( $args = array() ) {

	$query_args = array(
		'post_type'           => 'gmw_location_type',
		'ignore_sticky_posts' => true,
		'posts_per_page'      => -1,
		'suppress_filters'    => true,
		'orderby'             => 'ID',
	);

	if ( ! empty( $args['include'] ) ) {
		$query_args['post__in'] = is_array( $args['include'] ) ? $args['include'] : explode( ',', $args['include'] );
	}

	if ( ! empty( $args['exlude'] ) ) {
		$query_args['post__not_in'] = is_array( $args['exclude'] ) ? $args['exclude'] : explode( ',', $args['exclude'] );
	}

	if ( ! empty( $args['orderby'] ) ) {
		$query_args['orderby'] = $args['orderby'];
	}

	// Modify query args before pulling location types data.
	$query_args = apply_filters( 'gmw_ml_get_location_types_args', $query_args, $args );

	$posts = get_posts( $query_args );

	if ( empty( $posts ) ) {
		return false;
	}

	$location_types = array();

	foreach ( $posts as $post ) {

		$location_type = array(
			'ID'          => $post->ID,
			'title'       => $post->post_title,
			'description' => $post->post_excerpt,
		);

		$data = ( ! empty( $post->post_content ) && is_serialized( $post->post_content ) ) ? maybe_unserialize( $post->post_content ) : array();

		$location_type = array_merge( $location_type, $data );

		$location_types[] = (object) $location_type;
	}

	return $location_types;
}

/**
 * Get a specific location type.
 *
 * @param  integer $id location type ID.
 *
 * @return [type]      [description]
 */
function gmw_get_registered_location_type( $id = 0 ) {

	$location_type_id = absint( $id );

	if ( empty( $location_type_id ) ) {
		return false;
	}

	$args = array(
		'include' => $location_type_id,
	);

	$location_type = gmw_get_registered_location_types( $args );

	return ! empty( $location_type[0] ) ? $location_type[0] : false;
}

/**
 * Get list of location meta fields
 *
 * Will usually be used in the results.
 *
 * @param  mixed $location can be location object or location ID.
 * @param  array $fields   array of fields.
 * @param  array $labels   array of label for each field.
 *
 * @return HTML elements
 */
function gmw_get_location_meta_list( $location = false, $fields = array(), $labels = array() ) {

	// check if $location is an object and contains location meta. This will usually be used in the loop.
	if ( is_object( $location ) ) {

		// look for location meta in the object.
		// It might generated during the loop.
		if ( ! empty( $location->location_meta ) ) {

			$location_meta = $location->location_meta;

		} else {

			// can sometimes be location_id in some loops.
			if ( isset( $location->location_id ) ) {

				$location_id = $location->location_id;

				// otherwwise, it might be saved as ID.
			} elseif ( isset( $location->ID ) ) {

				$location_id = $location->ID;

				// return if no location ID.
			} else {
				return;
			}

			// get the location meta.
			$location_meta = gmw_get_location_meta( $location_id, $fields );
		}

		// Otherwise, check for location ID.
	} elseif ( is_numeric( $location ) ) {

		$location = absint( $location );

		if ( ! $location ) {
			return;
		}

		// get location meta from database.
		$location_meta = gmw_get_location_meta( $location, $fields );
		$location_id   = $location;

		// Abort if $location doenst match.
	} else {
		return;
	}

	// maybe string comma separated.
	if ( ! is_array( $location_meta ) ) {
		$location_meta = explode( ',', $location_meta );
	}

	if ( empty( $location_meta ) ) {
		return;
	}

	$count         = 0;
	$output        = '';
	$labels        = apply_filters( 'gmw_get_location_meta_list_labels', $labels, $fields, $location );
	$location_meta = apply_filters( 'gmw_location_meta_list_field_before_output', $location_meta, $location );

	// loop through fields.
	foreach ( $location_meta as $field => $value ) {

		if ( empty( $value ) || ( ! empty( $fields ) && ! in_array( $field, $fields, true ) ) ) {
			continue;
		}

		$count++;

		// check for field label.
		$label = isset( $labels[ $field ] ) ? $labels[ $field ] : $field;

		// email field.
		if ( 'email' === $field ) {

			$value = sanitize_email( $value );

			$output .= '<li class="field ' . sanitize_key( esc_attr( $field ) ) . '">';
			$output .= '<span class="label">' . esc_html( $label ) . ': </span>';
			$output .= '<span class="info"><a href="mailto:' . $value . '">' . $value . '</a></span>';
			$output .= '</li>';

			// website field.
		} elseif ( in_array( $field, array( 'website', 'url', 'site' ), true ) ) {

			$url = wp_parse_url( $value );

			if ( empty( $url['scheme'] ) ) {
				$url['scheme'] = 'http';
			}

			$scheme = $url['scheme'] . '://';
			$path   = str_replace( $scheme, '', $value );

			$output .= '<li class="field ' . sanitize_key( esc_attr( $field ) ) . '">';
			$output .= '<span class="label">' . esc_html( $label ) . ': </span>';
			$output .= '<span class="info"><a href="' . esc_url( $scheme . $path ) . '" target="_blank">' . esc_html( $path ) . '</a></span>';
			$output .= '</li>';

			// phone field.
		} elseif ( in_array( $field, array( 'phone', 'cell', 'tel', 'telephone', 'mobile' ), true ) ) {

			$value = esc_attr( $value );

			$output .= '<li class="field ' . sanitize_key( esc_attr( $field ) ) . '">';
			$output .= '<span class="label">' . esc_html( $label ) . ': </span>';
			$output .= '<span class="info"><a href="tel:' . $value . '">' . $value . '</a></span>';
			$output .= '</li>';

			// other fields.
		} else {

			$output .= '<li class="field ' . sanitize_key( esc_attr( $field ) ) . '">';
			$output .= '<span class="label">' . esc_html( $label ) . ': </span>';
			$output .= '<span class="info">' . esc_attr( $value ) . '</span></a>';
			$output .= '</li>';
		}
	}

	return 0 === $count ? false : '<ul class="gmw-location-meta gmw-additional-info-wrapper">' . $output . '</ul>';
}

/**
 * Output list of location meta fields
 *
 * Will usually be used in the results.
 *
 * @param  mixed $location can be location object or location ID.
 * @param  array $fields   array of fields.
 * @param  array $labels   array of label for each field.
 */
function gmw_location_meta_list( $location, $fields = array(), $labels = array() ) {
	echo gmw_get_location_meta_list( $location, $fields, $labels ); // WPCS: XSS ok.
}

/**
 * Get directions link
 *
 * Usually will be used in the results.
 *
 * @param  object $location    location object or location ID.
 * @param  array  $from_coords array of coords array( lat,lng ).
 * @param  string $label       link label - default "get directions".
 *
 * @return HTML element        link to Google Maps.
 */
function gmw_get_directions_link( $location, $from_coords = array(), $label = '' ) {

	// if location ID pass get the location data.
	if ( is_int( $location ) ) {

		$location = gmw_get_location( $location );

		// abort if not ID or object data.
	} elseif ( ! is_object( $location ) ) {

		$location = false;
	}

	// abort if no coordinates.
	if ( empty( $location->lat ) || empty( $location->lng ) ) {

		// maybe coords in latitude, longitude.
		if ( empty( $location->latitude ) || empty( $location->longitude ) ) {

			return;

		} else {
			$location->lat = $location->latitude;
			$location->lng = $location->longitude;
		}
	}

	$args = array(
		'to_lat' => $location->lat,
		'to_lng' => $location->lng,
	);

	if ( ! empty( $from_coords[0] ) && ! empty( $from_coords[1] ) ) {

		$args['from_lat'] = $from_coords[0];
		$args['from_lng'] = $from_coords[1];

	} elseif ( ! empty( $from_coords['lat'] ) && ! empty( $from_coords['lng'] ) ) {

		$args['from_lat'] = $from_coords['lat'];
		$args['from_lng'] = $from_coords['lng'];

	} else {

		$ulc_prefix = gmw_get_ulc_prefix();

		if ( ! empty( $_COOKIE[ $ulc_prefix . 'lat' ] ) && ! empty( $_COOKIE[ $ulc_prefix . 'lng' ] ) ) {

			$args['from_lat'] = urldecode( sanitize_text_field( wp_unslash( $_COOKIE[ $ulc_prefix . 'lat' ] ) ) );
			$args['from_lng'] = urldecode( sanitize_text_field( wp_unslash( $_COOKIE[ $ulc_prefix . 'lng' ] ) ) );
		}
	}

	$args['units'] = ( empty( $location->units ) || 'mi' === $location->units ) ? 'imperial' : 'metric';

	if ( '' !== $label ) {
		$args['label'] = $label;
	}

	return GMW_Maps_API::get_directions_link( $args );
}

/**
 * Output directions link
 *
 * Usually will be used in the results.
 *
 * @param  object $location    location object or location ID.
 * @param  array  $from_coords array of coords array( lat,lng ).
 * @param  string $label       link label - default "get directions".
 */
function gmw_directions_link( $location, $from_coords = array(), $label = '' ) {
	echo gmw_get_directions_link( $location, $from_coords, $label ); // WPCS: XSS ok.
}
