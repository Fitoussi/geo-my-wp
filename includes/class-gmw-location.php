<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GMW_Location
 *
 * This class responsible for location process. Craete, update, delete....
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 *
 */
class GMW_Location {

	/**
	 * locations table name
	 *
	 * @var string
	 */
	public static $table_name = 'gmw_locations';

	/**
	 * Locations table format
	 *
	 * @var array
	 */
	public static $format = array(
		'%s',
		'%d',
		'%d',
		'%d',
		'%s',
		'%d',
		'%d',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
		'%s',
	);

	/**
	 * Get locations table
	 *
	 * @param  boolean $base_prefix use based prefix instead of blog prefix when in multisite.
	 *
	 * @return [type]               [description]
	 */
	public static function get_table() {

		global $wpdb;

		$table = $wpdb->base_prefix . self::$table_name;

		return $table;
	}

	/**
	 * default locations database table values
	 *
	 * @return array
	 */
	public static function default_values() {

		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 1;

		if ( ! $user_id ) {
			$user_id = 1;
		}

		return array(
			'object_type'       => '',
			'object_id'         => 0,
			'blog_id'           => gmw_get_blog_id(),
			'user_id'           => $user_id,
			'status'            => 1,
			'parent'            => 0,
			'featured'          => 0,
			'title'             => '',
			'latitude'          => 0.000000,
			'longitude'         => 0.000000,
			'street_number'     => '',
			'street_name'       => '',
			'street'            => '',
			'premise'           => '',
			'neighborhood'      => '',
			'city'              => '',
			'county'            => '',
			'region_name'       => '',
			'region_code'       => '',
			'postcode'          => '',
			'country_name'      => '',
			'country_code'      => '',
			'address'           => '',
			'formatted_address' => '',
			'place_id'          => '',
			'map_icon'          => '_default.png',
			'created'           => '0000-00-00 00:00:00',
			'updated'           => '0000-00-00 00:00:00',
		);
	}

	/**
	 * Helper to verify an ID.
	 *
	 * @param numeric $id ID as numeric values
	 *
	 * @return absint
	 *
	 * @since 3.0
	 */
	public static function verify_id( $id ) {

		// verify location ID
		if ( ! is_numeric( $id ) ) {
			return false;
		}

		$id = absint( $id );

		if ( ! $id ) {
			return false;
		}

		return $id;
	}

	/**
	 * Try to get locations data.
	 *
	 * The function will try to get the locations data based on global variables
	 *
	 * in case that the object data ( object_type or object_id ) cannot be verified.
	 *
	 * @since 3.0
	 *
	 * @param boolean $parent true to get only prent location false to get all locations
	 *
	 * @return location object or empty if no locaiton was found
	 *
	 * @since 3.0
	 */
	private static function try_get_locations( $parent = false, $output = OBJECT, $cache = true ) {

		// get common globals
		global $post, $comment, $user;

		$location = $found = false;

		// check for post ID and try to get the location if found
		if ( ! empty( $post->ID ) ) {

			$object_type = 'post';
			$object_id   = $post->ID;
			$found       = true;

			// otherwise look for user ID
		} elseif ( ! empty( $user->ID ) ) {

			$object_type = 'user';
			$object_id   = $user->ID;
			$found       = true;

			// Otherwise, maybe comment ID
		} elseif ( ! empty( $comment->comment_ID ) ) {

			$object_type = 'comment';
			$object_id   = $comment->comment_ID;
			$found       = true;
		}

		// if object type and object ID were found, get the location from database
		if ( $found ) {
			$location = $parent == true ? self::get( $object_type, $object_id, $output, $cache ) : self::get_locations( $object_type, $object_id, $output, $cache );
		}

		return $location;
	}

	/**
	 * Deprecated - use update() instead
	 *
	 * @return [type] [description]
	 */
	public static function update_location( $args ) {
		self::update( $args );
	}

	/**
	 * Save location - Save location to gmw_locations database table.
	 *
	 * To save a location you need to pass an array of location data which includes the location
	 *
	 * fields and the object data.
	 *
	 * See the default_values array above for the location fields you need to pass.
	 *
	 * @param  array $args array of location fields and data.
	 *
	 * @return int location ID
	 *
	 * @since 3.0
	 */
	public static function update( $args ) {

		// verify object ID
		if ( ! self::verify_id( $args['object_id'] ) ) {

			trigger_error( 'Trying to update a location using invalid object ID.', E_USER_NOTICE );

			return false;
		}

		// verify valid coordinates
		if ( ! is_numeric( $args['latitude'] ) || ! is_numeric( $args['longitude'] ) ) {

			trigger_error( 'Trying to update a location using invalid coordinates.', E_USER_NOTICE );

			return false;
		}

		// parse location args with default location args
		$location_data = wp_parse_args( $args, self::default_values() );

		// verify country code
		if ( empty( $location_data['country_code'] ) || strlen( $location_data['country_code'] ) != 2 ) {

			if ( empty( $location_data['country_name'] ) ) {

				$location_data['country_code'] = '';

			} else {

				// get list of countries code. We will use it to make sure that the only the country code passes to the column.
				$countries = gmw_get_countries_list_array();

				// look for the country code based on the country name
				$country_code = array_search( ucwords( $location_data['country_name'] ), $countries );

				// get the country code from the list
				$location_data['country_code'] = ! empty( $country_code ) ? $country_code : '';
			}
		}

		// verify user ID
		if ( ! self::verify_id( $location_data['user_id'] ) ) {

			trigger_error( 'Trying to update a location using invalid user ID.', E_USER_NOTICE );

			return false;
		}

		// check for existing location
		$saved_location = self::get( $location_data['object_type'], $location_data['object_id'] );

		global $wpdb;

		$table = self::get_table();

		// modify the new location args before saving
		$location_data = apply_filters( 'gmw_pre_save_location_data', $location_data, $saved_location );
		$location_data = apply_filters( "gmw_pre_save_{$location_data['object_type']}_location_data", $location_data, $saved_location );

		// do some custom functions before saving location
		do_action( 'gmw_pre_save_location', $location_data, $saved_location );
		do_action( "gmw_pre_save_{$location_data['object_type']}_location", $location_data, $saved_location );

		// insert new location if not already exists in database
		if ( ! $saved_location ) {

			// udpate the current data - time
			$location_data['created'] = current_time( 'mysql' );

			// insert new location to database
			$wpdb->insert( $table, $location_data, self::$format );

			// get the new location ID
			$location_id = $wpdb->insert_id;

			$updated = false;

			// otherwise, update existing location
		} else {

			// modify the new location args before saving
			do_action( 'gmw_pre_update_location', $location_data, $saved_location );
			do_action( "gmw_pre_update_{$location_data['object_type']}_location", $location_data, $saved_location );

			// get existing location ID
			$location_id = isset( $saved_location->ID ) ? (int) $saved_location->ID : 0;

			// verify location ID
			if ( ! is_int( $location_id ) || 0 == $location_id ) {
				return false;
			}

			// Keep created time as its original time
			$location_data['created'] = $saved_location->created;

			// updated time based on current time
			$location_data['updated'] = current_time( 'mysql' );

			// update location
			$wpdb->update(
				$table,
				$location_data,
				array( 'ID' => $location_id ),
				self::$format,
				array( '%d' )
			);

			$updated = true;

			do_action( 'gmw_location_updated', $location_data );
			do_action( "gmw_{$location_data['object_type']}_location_updated", $location_data );
		}

		// append Location ID to location data array
		$location_data = array( 'ID' => $location_id ) + $location_data;

		// make it into an object
		$location_data = (object) $location_data;

		// do some custom functions once location saved
		do_action( 'gmw_save_location', $location_id, $location_data, $updated );
		do_action( "gmw_save_{$location_data->object_type}_location", $location_id, $location_data, $updated );

		// set updated location in cache
		//wp_cache_set( $location_id, $location_data, 'gmw_locations' );
		wp_cache_set( $location_data->object_type . '_' . $location_data->object_id, $location_data, 'gmw_location' );
		wp_cache_set( $location_id, $location_data, 'gmw_location' );

		wp_cache_delete( $location_data->object_type . '_' . $location_data->object_id, 'gmw_locations' );

		return $location_id;
	}

	/**
	 * Check if location exists in database
	 *
	 * @param int $location_id location ID
	 *
	 * @return boolean
	 *
	 * @since 3.0
	 */
	public static function exists( $location_id = 0 ) {

		// verify location ID
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		global $wpdb, $blog_id;

		// database table name
		$table = self::get_table();

		// look for the location in database
		$location_id = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT ID 
				FROM   $table 
				WHERE  ID = %d",
				$location_id
			)
		);

		return ! empty( $location_id ) ? true : false;
	}

	/**
	 * Get the object type of a location
	 *
	 * @param  integer $location_id [description]
	 *
	 * @return [type]               [description]
	 *
	 * @since 3.0
	 */
	public static function get_object_type( $location_id = 0 ) {

		// verify location ID
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		global $wpdb;

		// database table name
		$table = self::get_table();

		// look for the location in database
		$object_type = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT object_type 
				FROM   $table 
				WHERE  ID = %d",
				$location_id
			)
		);

		return $object_type;

	}

	/**
	 * Get location from database by location ID.
	 *
	 * @param  integer  $location_id location ID
	 * @param  constant $output      OBJECT || ARRAY_A || ARRAY_N  the output type of the location data
	 * @param  boolean  $cache       Look for location in cache
	 *
	 * @return object || Array return the location data
	 *
	 * @since 3.0
	 */
	public static function get_by_id( $location_id = 0, $output = OBJECT, $cache = true ) {

		// verify location ID
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		global $wpdb;

		// look for location in cache if needed
		$location = $cache ? wp_cache_get( $location_id, 'gmw_location' ) : false;

		// get location from database if not found in cache
		if ( false === $location ) {

			$table    = self::get_table();
			$location = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT *, latitude as lat, longitude as lng
	            	FROM   $table
	            	WHERE  ID = %d",
					$location_id
				),
				OBJECT
			);

			// set location in cache if found
			if ( ! empty( $location ) ) {
				wp_cache_set( $location->object_type . '_' . $location->object_id, $location, 'gmw_location' );
				wp_cache_set( $location_id, $location, 'gmw_location' );
			}
		}

		// if no location found
		if ( empty( $location ) ) {
			return null;
		}

		// conver to array if needed
		if ( ARRAY_A == $output || ARRAY_N == $output ) {
			$location = gmw_to_array( $location, $output );
		}

		return $location;
	}

	/**
	 * Get location data based on object_type and object_id pairs.
	 *
	 * The returned location will be the parent location in case that the object_type - object_id pair has multiple locations
	 *
	 * @param  string   $object_type the object type post, user...
	 * @param  integer  $object_id   the object ID. post ID, User ID...
	 * @param  constant $output     OBJECT || ARRAY_A || ARRAY_N  the output type of the location data
	 * @param  boolean  $cache      Look for location in cache
	 *
	 * @return object || Array return the location data
	 *
	 * @since 3.0
	 */
	public static function get( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {

		// verify object type and object ID. If any of them empty use try_get_location function
		if ( empty( $object_type ) || empty( $object_id ) ) {

			// try to get location usign global variables
			return self::try_get_locations( true, $output, $cache );
		}

		// verify object types
		if ( ! in_array( $object_type, GMW()->object_types ) ) {

			trigger_error( 'Trying to get a location using invalid object type.', E_USER_NOTICE );

			return false;
		}

		//verify object ID
		if ( ! self::verify_id( $object_id ) ) {

			trigger_error( 'Trying to get a location using invalid object ID.', E_USER_NOTICE );

			return false;
		}

		$object_id = absint( $object_id );

		// look for locations in cache if needed
		$location = $cache ? wp_cache_get( $object_type . '_' . $object_id, 'gmw_location' ) : false;

		if ( false === $location ) {

			global $wpdb;

			$blog_id  = gmw_get_blog_id( $object_type );
			$table    = self::get_table();
			$location = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT *, latitude as lat, longitude as lng
		            FROM   $table
		            WHERE  blog_id     = %d 
		            AND    object_type = %s 
		            AND    object_id   = %d
		            AND    parent      = 0",
					$blog_id,
					$object_type,
					$object_id
				),
				OBJECT
			);

			// save to cache if location found
			if ( ! empty( $location ) ) {
				wp_cache_set( $object_type . '_' . $object_id, $location, 'gmw_location' );
				wp_cache_set( $location->ID, $location, 'gmw_location' );
			}
		}

		// if no location found
		if ( empty( $location ) ) {
			return null;
		}

		// convert to array if needed
		if ( ARRAY_A == $output || ARRAY_N == $output ) {
			$location = gmw_to_array( $location, $output );
		}

		return $location;
	}

	/**
	 * Get all locations based on object_type - object_ID pair.
	 *
	 * @param  string   $object_type the object type ( post, user... ).
	 * @param  integer  $object_id   the object ID  ( post ID, User ID... ).
	 * @param  constant $output      OBJECT || ARRAY_A || ARRAY_N  the output type of the location data
	 * @param  boolean  $cache       Look for location in cache
	 *
	 * @return array of locations data.
	 *
	 * since 3.0
	 */
	public static function get_locations( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {

		// try to get location if object type/ID do not exist
		if ( empty( $object_type ) || empty( $object_id ) ) {

			return self::try_get_locations( false, $output, $cache );
		}

		// verify object type
		if ( ! in_array( $object_type, GMW()->object_types ) ) {

			trigger_error( 'Trying to get a location using invalid object type.', E_USER_NOTICE );

			return false;
		}

		// verify object ID
		if ( ! is_numeric( $object_id ) || ! absint( $object_id ) ) {

			trigger_error( 'Trying to get a location using invalid object ID.', E_USER_NOTICE );

			return false;
		}

		$object_id = absint( $object_id );

		// look for locations in cache if needed
		$locations = $cache ? wp_cache_get( $object_type . '_' . $object_id, 'gmw_locations' ) : false;

		// if no location found in cache get it from database
		if ( false === $locations ) {

			global $wpdb;

			$blog_id   = gmw_get_blog_id( $object_type );
			$table     = self::get_table();
			$locations = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT *
		            FROM   $table
		            WHERE  blog_id     = %d 
		            AND    object_type = %s 
		            AND    object_id   = %d",
					$blog_id,
					$object_type,
					$object_id
				),
				OBJECT
			);

			// save to cache if location found
			if ( ! empty( $locations ) ) {
				wp_cache_set( $object_type . '_' . $object_id, serialize( $locations ), 'gmw_locations' );
			}
		}

		// if no location found
		if ( empty( $locations ) ) {
			return null;
		}

		$locations = maybe_unserialize( $locations );

		// convert to array of arrays
		if ( ARRAY_A == $output || ARRAY_N == $output ) {
			foreach ( $locations as $key => $value ) {
				$locations[ $key ] = gmw_to_array( $value, $output );
			}
		}

		return $locations;
	}

	/**
	 * query_address_fields
	 *
	 * filter query based on specific address fields
	 *
	 * This filter does not do a proximity search but pulls locations from database
	 *
	 * matching exactly the address fields filters.
	 *
	 * @since 3.0
	 *
	 * @return [type] [description]
	 */
	public static function query_address_fields( $address_filters = array(), $gmw = array() ) {

		// modify the filters filters
		$address_filters = apply_filters( 'gmw_query_address_fields', $address_filters, $gmw );

		// abort if nothing passed
		if ( empty( $address_filters ) ) {
			return '';
		}

		global $wpdb;

		$output = '';

		// build the query
		foreach ( $address_filters as $key => $value ) {

			if ( empty( $value ) ) {
				continue;
			}

			$key = str_replace( '_filter', '', $key );

			// filter region
			if ( in_array( $key, array( 'region_name', 'region_code', 'state' ) ) ) {

				$output .= $wpdb->prepare( ' AND ( gmw_locations.region_name = %s OR gmw_locations.region_code = %s )', $value, $value );

				// filter country
			} elseif ( in_array( $key, array( 'country_name', 'country_code', 'country' ) ) ) {

				$output .= $wpdb->prepare( ' AND ( gmw_locations.country_name = %s OR gmw_locations.country_code = %s )', $value, $value );

				// filter postcode
			} elseif ( 'postcode' == $key || 'zipcode' == $key ) {

				$output .= $wpdb->prepare( ' AND gmw_locations.postcode = %s', $value );

				// filter the rest
			} elseif ( in_array( $key, array( 'street', 'county', 'neighborhood', 'city' ) ) ) {

				$output .= $wpdb->prepare( " AND gmw_locations.{$key} = %s", $value );
			}
		}

		return $output;
	}

	/**
	 * Query locations from GEO my WP database table
	 *
	 * This function will search for locations based on address and distance entered in the search forms.
	 *
	 * It will return a multidimentional array of objects_id and locations_data.
	 *
	 * The objects ID array can be used to filter a search query ( WP_Query, bp_has_Members and so on ); by passing
	 *
	 * the objects_id array into the include arument of the query args.
	 *
	 * The location data can be used to display the address and coordinates of each object in the results.
	 *
	 * Usually, each item in the location data array shoul be merge with its object in the search results.
	 *
	 * For example, before running bp_has_members() you can call the function
	 *
	 * $locations = GMW_Location::get_locations_data();. Then filter the BuddyPress members query
	 *
	 * bp_has_locations( array( 'includes' => $locations['objects_id'] ) );
	 *
	 * @since 3.0
	 *
	 * @author Eyal Fitoussi
	 *
	 * @return multidimentional array of locations data.
	 */
	public static function get_locations_data( $args = array(), $address_filters = array(), $location_meta = array(), $db_table = 'gmw_locations', $db_fields = array(), $gmw = array() ) {

		// default values
		$args = wp_parse_args(
			$args, array(
				'object_type'       => 'post',
				'lat'               => false,
				'lng'               => false,
				'radius'            => false,
				'units'             => 'imperial',
				'unique'            => '',
				'count'             => '',
				'offset'            => '',
				'paged'             => 1,
				'orderby'           => '',
				'object__in'        => '',
				'output_objects_id' => true,
			)
		);

		$args = apply_filters( 'gmw_get_locations_data_args', $args, $gmw );

		if ( empty( $db_fields ) ) {

			// default db fields
			$db_fields = array(
				'ID as location_id',
				'object_type',
				'object_id',
				'user_id',
				'latitude as lat',
				'longitude as lng',
				'street',
				'city',
				'region_name',
				'postcode',
				'country_code',
				'address',
				'formatted_address',
				'map_icon',
			);
		}

		// modify the database columns if needed
		$db_fields = apply_filters( 'gmw_get_locations_db_fields', $db_fields, $gmw );
		$db_fields = apply_filters( "gmw_get_{$args['object_type']}_locations_db_fields", $db_fields, $gmw );

		// for cache key
		//$args['db_fields'] = $db_fields;

		$count  = 0;
		$output = '';

		// generate the db fields
		foreach ( $db_fields as $field ) {

			if ( $count > 0 ) {
				$output .= ', ';
			}

			$count++;

			if ( strpos( $field, 'as' ) !== false ) {

				$field = explode( ' as ', $field );

				$output .= "gmw_locations.{$field[0]} as {$field[1]}";

				// Here we are including latitude and longitude fields
				// using their original field name.
				// for backward compatibility, we also need to have "lat" and "lng"
				// in the location object and that is what we did in the line above.
				// The lat and lng field are too involve and need to carfully change it.
				// eventually we want to completly move to using latitude and longitude.
				if ( 'latitude' == $field[0] || 'longitude' == $field[0] ) {
					$output .= ",gmw_locations.{$field[0]}";
				}
			} else {

				$output .= "gmw_locations.{$field}";
			}
		}

		$args['db_fields']       = $output;
		$args['db_table']        = $db_table;
		$args['address_filters'] = $address_filters;

		$args = apply_filters( 'gmw_get_locations_query_args', $args, $gmw );
		$args = apply_filters( "gmw_get_{$args['object_type']}_locations_query_args", $args, $gmw );

		if ( ! empty( $gmw['prefix'] ) ) {
			$args = apply_filters( "gmw_{$gmw['prefix']}_get_locations_query_args", $args, $gmw );
		}

		$internal_cache = GMW()->internal_cache;

		if ( $internal_cache ) {
			// prepare for cache
			$hash            = md5( json_encode( $args ) );
			$query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_' . $args['object_type'] . '_locations' );
		}

		if ( ! $internal_cache || false === ( $locations = get_transient( $query_args_hash ) ) ) {
			//if ( 1 == 1 ) {
			//print_r( 'locations query done' );

			global $wpdb;

			$clauses['select']          = 'SELECT';
			$clauses['fields']          = $args['db_fields'];
			$clauses['distance']        = '';
			$clauses['from']            = "FROM {$wpdb->base_prefix}{$db_table} gmw_locations";
			$clauses['where']           = $wpdb->prepare( " WHERE gmw_locations.object_type = '%s' AND gmw_locations.parent = '0'", $args['object_type'] );
			$clauses['address_filters'] = '';
			$clauses['having']          = '';
			$clauses['orderby']         = 'ORDER BY gmw_locations.ID';
			$clauses['limit']           = '';

			if ( ! empty( $args['object__in'] ) ) {

				// escape terms ID
				$terms_id = esc_sql( $args['object__in'] );
				$terms_id = implode( ',', $terms_id );

				$clauses['where'] .= " AND object_id IN ( {$terms_id} ) ";
			}

			// if object type uses database table as global, means it doesn't save locations per blog,
			// such as "user" we search within the entire database table. Otherwise, if data saved per blog, such as "post", we will filter locations based on blog ID
			//if ( ! in_array( $args['object_type'], GMW()->global_db_objects ) ) {

			$loc_blog_id = gmw_get_blog_id( $args['object_type'] );

			if ( absint( $loc_blog_id ) ) {
				$clauses['where'] .= " AND gmw_locations.blog_id = {$loc_blog_id} ";
			}

			$clauses['address_filters'] = self::query_address_fields( $address_filters );

			if ( is_numeric( $args['count'] ) ) {

				if ( is_numeric( $args['offset'] ) ) {
					$clauses['limit'] = $wpdb->prepare( 'LIMIT %d, %d', $args['offset'], $args['count'] );
				} else {
					$clauses['limit'] = $wpdb->prepare( 'LIMIT %d, %d', $args['count'] * ( $args['paged'] - 1 ), $args['count'] );
				}
			}

			// if address entered, do a proximity search and get locations within the radius entered.
			if ( empty( $clauses['address_filters'] ) && ! empty( $args['lat'] ) && ! empty( $args['lng'] ) ) {

				// Get earth radius based on units
				if ( 'imperial' == $args['units'] || 3959 == $args['units'] || 'miles' == $args['units'] ) {
					$earth_radius = 3959;
					$units        = 'mi';
					$degree       = 69.0;
				} else {
					$earth_radius = 6371;
					$units        = 'km';
					$degree       = 111.045;
				}

				// add units to locations data
				$clauses['fields'] .= ", '{$units}' AS units";

				/**
				 * since these values are repeatable, we escape them previous
				 *
				 * the query instead of running multiple prepares.
				 */
				$lat = esc_sql( $args['lat'] );
				$lng = esc_sql( $args['lng'] );

				$clauses['distance'] = ", ROUND( {$earth_radius} * acos( cos( radians( {$lat} ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance";

				/*
				$rad = deg2rad( $args['lat'] );
				$a   = cos( $rad );
				$b   = deg2rad( $args['lng'] );
				$c   = sin( $rad );

				$clauses['distance'] = $wpdb->prepare( ",
					ROUND( %d * acos( %s * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - ( %s ) ) + ( %s ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance",
					array(
						$earth_radius,
						$a,
						$b,
						$c
					)
				);
				*/

				// make sure we pass only numeric or decimal as radius
				if ( ! empty( $args['radius'] ) && is_numeric( $args['radius'] ) ) {

					$radius = esc_sql( $args['radius'] );

					// calculate the between point
					$bet_lat1 = $lat - ( $radius / $degree );
					$bet_lat2 = $lat + ( $radius / $degree );
					$bet_lng1 = $lng - ( $radius / ( $degree * cos( deg2rad( $lat ) ) ) );
					$bet_lng2 = $lng + ( $radius / ( $degree * cos( deg2rad( $lat ) ) ) );

					$clauses['where'] .= " AND gmw_locations.latitude BETWEEN {$bet_lat1} AND {$bet_lat2}";
					$clauses['where'] .= " AND gmw_locations.longitude BETWEEN {$bet_lng1} AND {$bet_lng2} ";

					$clauses['having'] = "HAVING distance <= {$radius} OR distance IS NULL";
				}

				$clauses['orderby'] = 'ORDER BY distance ASC';
			}

			if ( '' !== $args['orderby'] && 'distance' !== $args['orderby'] ) {
				$clauses['orderby'] = 'ORDER BY gmw_locations.' . $args['orderby'];
			}

			//wp_send_json( $clauses );
			// query the locations
			$locations = $wpdb->get_results(
				implode( ' ', apply_filters( 'gmw_get_locations_query_clauses', $clauses, $args, $gmw ) )
			);

			/**
			 * Collect locations into an array of objects and locations data.
			 *
			 * This way we can easily append objects with thier location.
			 *
			 * This feature is enabled by default, but can be disabled
			 *
			 * If not needed to preserve performance.
			 *
			 */
			if ( $args['output_objects_id'] ) {

				$locations_data = array(
					'objects_id'     => array(),
					'featured_ids'   => array(),
					'locations_data' => array(),
				);

				// abort if no locations found
				if ( ! empty( $locations ) ) {

					// modify the locations query
					foreach ( $locations as $value ) {

						// collect objects id into an array
						$locations_data['objects_id'][] = $value->object_id;

						if ( isset( $value->featured_location ) && $value->featured_location == 1 ) {
							$locations_data['featured_ids'][] = $value->object_id;
						}

						// replace array keys with object id to be able to do some queries later
						$locations_data['locations_data'][ $value->object_id ] = $value;
					}
				}

				$locations = $locations_data;
			}

			// set new query in transient only if cache enabled
			if ( $internal_cache ) {
				set_transient( $query_args_hash, $locations, GMW()->internal_cache_expiration );
			}
		}

		return $locations;
	}

	/**
	 * Delete location using object type - ID pair
	 *
	 * The parent location data and all associated location meta will be deleted.
	 *
	 * @param  string  $object_type object type ( post, user... ).
	 * @param  integer $object_id   object id ( post iD, user ID... ).
	 *
	 * @return boolean true for deleted false for failed
	 *
	 * @since 3.0
	 *
	 */
	public static function delete( $object_type = '', $object_id = 0, $delete_meta = false ) {

		// verify data
		if ( empty( $object_type ) || empty( $object_id ) ) {
			return false;
		}

		// verify object type
		if ( ! in_array( $object_type, GMW()->object_types ) ) {

			trigger_error( 'Trying to delete a location using invalid object type.', E_USER_NOTICE );

			return false;
		}

		// verify object ID
		if ( ! is_numeric( $object_id ) || ! absint( $object_id ) ) {

			trigger_error( 'Trying to delete a location using invalid object ID.', E_USER_NOTICE );

			return false;
		}

		$object_id = absint( $object_id );

		// get location to make sure it exists
		// this will get the parent location
		$location = self::get( $object_type, $object_id );

		// abort if no location found
		if ( empty( $location ) ) {
			return false;
		}

		do_action( 'gmw_before_location_deleted', $location->ID, $location );
		do_action( 'gmw_before_' . $object_type . '_location_deleted', $location->ID, $location );

		global $wpdb;

		// delete location from database
		$table   = self::get_table();
		$deleted = $wpdb->delete(
			$table,
			array( 'ID' => $location->ID ),
			array( '%d' )
		);

		// abort if failed to delete
		if ( empty( $deleted ) ) {
			return false;
		}

		do_action( 'gmw_location_deleted', $location->ID, $location );
		do_action( 'gmw_' . $object_type . '_location_deleted', $location->ID, $location );

		// clear locations from cache
		wp_cache_delete( $object_type . '_' . $object_id, 'gmw_location' );
		wp_cache_delete( $location->ID, 'gmw_location' );
		wp_cache_delete( $object_type . '_' . $object_id, 'gmw_locations' );

		// delete the location metadata associated with this location if needed
		if (  true == $delete_meta ) {
			GMW_Location_Meta::delete_all( $location->ID );
		}

		return $location->ID;
	}

	/**
	 * Delete all locations based on object type - and object ID pair
	 *
	 * all locations data and all associated location meta will be deleted.
	 *
	 * @param  string  $object_type object type ( post, user... ).
	 * @param  integer $object_id   object id ( post iD, user ID... ).
	 * @param  boolean $delete_meta to delete associate location meta
	 *
	 * @return boolean true for deleted false for failed
	 *
	 * @since 3.0
	 */
	public static function delete_locations( $object_type = '', $object_id = 0, $delete_meta = false ) {

		// verify data
		if ( empty( $object_type ) || empty( $object_id ) ) {
			return false;
		}

		// verify object type
		if ( ! in_array( $object_type, GMW()->object_types ) ) {

			trigger_error( 'Trying to delete a location using invalid object type.', E_USER_NOTICE );

			return false;
		}

		// verify object ID
		if ( ! is_numeric( $object_id ) || ! absint( $object_id ) ) {

			trigger_error( 'Trying to delete a location using invalid object ID.', E_USER_NOTICE );

			return false;
		}

		$object_id = absint( $object_id );

		global $wpdb;

		$blog_id = gmw_get_blog_id( $object_type );

		// delete location from database
		$table   = self::get_table();
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"
				DELETE
	            FROM   $table
	            WHERE  blog_id     = %d 
	            AND    object_type = %s 
	            AND    object_id   = %d",
				$blog_id,
				$object_type,
				$object_id
			)
		);

		// abort if failed to delete
		if ( empty( $deleted ) ) {
			return false;
		}

		// clear locations from cache
		wp_cache_delete( $object_type . '_' . $object_id, 'gmw_location' );
		wp_cache_delete( $object_type . '_' . $object_id, 'gmw_locations' );

		// delete the location metadata associated with this location if needed
		if ( true == $delete_meta ) {
			GMW_Location_Meta::delete_all( $location->ID );
		}

		return $deleted;
	}

	/**
	 * Delete location using location ID
	 *
	 * The location data and all associated location meta will be deleted
	 *
	 * @param  integer $location_id the location ID
	 *
	 * @return boolean  true if deleted false if failed
	 *
	 * @since 3.0
	 */
	public static function delete_by_id( $location_id = 0, $delete_meta = false ) {

		// check if location exists
		$location = self::get_by_id( $location_id );

		if ( empty( $location ) ) {
			return false;
		}

		return self::delete( $location->object_type, $location->object_id, $delete_meta );
	}
}
