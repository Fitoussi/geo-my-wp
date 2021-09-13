<?php
/**
 * GEO my WP - GMW_Location class.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class GMW_Location
 *
 * This class responsible for location process. Craete, update, delete....
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 */
class GMW_Location {

	/**
	 * Locations table name
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
		'ID'                => '%d',
		'object_type'       => '%s',
		'object_id'         => '%d',
		'blog_id'           => '%d',
		'user_id'           => '%d',
		'status'            => '%s',
		'parent'            => '%d',
		'featured'          => '%d',
		'location_type'     => '%d',
		'title'             => '%s',
		'latitude'          => '%s',
		'longitude'         => '%s',
		'street_number'     => '%s',
		'street_name'       => '%s',
		'street'            => '%s',
		'premise'           => '%s',
		'neighborhood'      => '%s',
		'city'              => '%s',
		'county'            => '%s',
		'region_name'       => '%s',
		'region_code'       => '%s',
		'postcode'          => '%s',
		'country_name'      => '%s',
		'country_code'      => '%s',
		'address'           => '%s',
		'formatted_address' => '%s',
		'place_id'          => '%s',
		'map_icon'          => '%s',
		'radius'            => '%f',
		'created'           => '%s',
		'updated'           => '%s',
	);

	/**
	 * Get the default format which can be modified via filter.
	 *
	 * @return [type] [description]
	 */
	public static function get_format() {
		return apply_filters( 'gmw_locations_table_default_format', self::$format );
	}

	/**
	 * Get locations table
	 *
	 * @return [type]               [description]
	 */
	public static function get_table() {

		global $wpdb;

		$table = $wpdb->base_prefix . self::$table_name;

		return $table;
	}

	/**
	 * Default locations database table values.
	 *
	 * @return array
	 */
	public static function default_values() {

		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 1;

		if ( ! $user_id ) {
			$user_id = 1;
		}

		return apply_filters(
			'gmw_locations_table_default_values',
			array(
				'ID'                => 0,
				'object_type'       => '',
				'object_id'         => 0,
				'blog_id'           => gmw_get_blog_id(),
				'user_id'           => $user_id,
				'status'            => 1,
				'parent'            => 0,
				'featured'          => 0,
				'location_type'     => 0,
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
				'radius'            => 0.0,
				'created'           => current_time( 'mysql' ),
				'updated'           => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Helper to verify an ID.
	 *
	 * @param numeric $id ID as numeric values.
	 *
	 * @return absint
	 *
	 * @since 3.0
	 */
	public static function verify_id( $id = 0 ) {

		// verify location ID.
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
	 * Check if location exists in database
	 *
	 * @param int $location_id location ID.
	 *
	 * @return boolean
	 *
	 * @since 3.0
	 */
	public static function exists( $location_id = 0 ) {

		// verify location ID.
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		global $wpdb, $blog_id;

		// database table name.
		$table = self::get_table();

		// look for the location in database.
		$location_id = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT ID 
                FROM   $table 
				WHERE  ID = %d",
				$location_id
			)
		); // WPCS: db call ok, cache ok, unprepared SQL ok.

		return ! empty( $location_id ) ? true : false;
	}

	/**
	 * Get the object type of a location
	 *
	 * @param  integer $location_id [description].
	 *
	 * @return [type]               [description]
	 *
	 * @since 3.0
	 */
	public static function get_object_type( $location_id = 0 ) {

		// verify location ID.
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		global $wpdb;

		// database table name.
		$table = self::get_table();

		// look for the location in database.
		$object_type = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT object_type 
				FROM   $table 
				WHERE  ID = %d",
				$location_id
			)
		); // WPCS: db call ok, cache ok, unprepared SQL ok.

		return $object_type;

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
	 * @param boolean  $parent true to get only prent location false to get all locations.
	 *
	 * @param constant $output output type - OBJECT | ARRAY.
	 *
	 * @param boolean  $cache  get from cache - true | false.
	 *
	 * @return location object or empty if no locaiton was found.
	 */
	private static function try_get_locations( $parent = false, $output = OBJECT, $cache = true ) {

		// get common globals.
		global $post, $comment, $user;

		$location = false;
		$found    = false;

		// check for post ID and try to get the location if found.
		if ( ! empty( $post->ID ) ) {

			$object_type = 'post';
			$object_id   = $post->ID;
			$found       = true;

			// otherwise look for user ID.
		} elseif ( ! empty( $user->ID ) ) {

			$object_type = 'user';
			$object_id   = $user->ID;
			$found       = true;

			// Otherwise, maybe comment ID.
		} elseif ( ! empty( $comment->comment_ID ) ) {

			$object_type = 'comment';
			$object_id   = $comment->comment_ID;
			$found       = true;
		}

		// if object type and object ID were found, get the location from database.
		if ( $found ) {
			$location = ! empty( $parent ) ? self::get_by_object( $object_type, $object_id, $output, $cache ) : self::get_locations( $object_type, $object_id, $output, $cache );
		}

		return $location;
	}

	/**
	 * Inset location - Create new or update an existing location in gmw_locations database table.
	 *
	 * To save a location you need to pass an array of location data which includes the location
	 *
	 * fields and the object data.
	 *
	 * When the location ID is provided in the array ( ID argument ) the location will be updated.
	 *
	 * Otherwise, a new location will be created.
	 *
	 * See the default_values array above for the location fields you need to pass.
	 *
	 * @param array $location_data array of location fields and data.
	 *
	 * @return int location ID
	 *
	 * @since 3.0
	 */
	public static function insert( $location_data ) {

		// verify object ID.
		if ( ! self::verify_id( $location_data['object_id'] ) ) {

			gmw_trigger_error( 'Trying to update a location using invalid object ID.' );

			return false;
		}

		// verify valid coordinates.
		if ( ! is_numeric( $location_data['latitude'] ) || ! is_numeric( $location_data['longitude'] ) ) {

			gmw_trigger_error( 'Trying to update a location using invalid coordinates.' );

			return false;
		}

		// verify user ID.
		if ( ! self::verify_id( $location_data['user_id'] ) ) {

			gmw_trigger_error( 'Trying to update a location using invalid user ID.' );

			return false;
		}

		$location_id    = 0;
		$update         = false;
		$saved_location = false;

		// If location ID provided, we will look for and update that location.
		if ( ! empty( $location_data['ID'] ) ) {

			$update      = true;
			$location_id = (int) $location_data['ID'];

			// Look for the existing location.
			$saved_location = self::get_by_id( $location_data['ID'] );

			// Abort if location was not found.
			if ( empty( $saved_location ) ) {

				gmw_trigger_error( 'Trying to update a location using invalid location ID.' );

				return 0;
			}
		} else {

			$saved_location = self::get_by_object( $location_data['object_type'], $location_data['object_id'] );
		}

		// modify the new location args before saving.
		$location_data = apply_filters( 'gmw_pre_save_location_data', $location_data, $saved_location );
		$location_data = apply_filters( "gmw_pre_save_{$location_data['object_type']}_location_data", $location_data, $saved_location );

		// do some custom functions before saving location.
		do_action( 'gmw_pre_save_location', $location_data, $saved_location );
		do_action( "gmw_pre_save_{$location_data['object_type']}_location", $location_data, $saved_location );

		// If the location is not set as parent, we will check if the same object has other another location that is set as a parent.
		// If not, we will set this location as the parent one. We would like to have a parent location for each object.
		if ( empty( $location_data['parent'] ) ) {

			if ( ! empty( $location_data['object_type'] ) && ! empty( $location_data['object_id'] ) ) {

				$parent_location = self::get_by_object( $location_data['object_type'], $location_data['object_id'], OBJECT, false );

				if ( empty( $parent_location ) || empty( $parent_location->parent ) ) {
					$location_data['parent'] = 1;
				}
			}
		}

		// verify that we are passing country code and not a name.
		if ( ! empty( $location_data['country_code'] ) && strlen( $location_data['country_code'] ) != 2 ) {

			// get list of countries code. We will use it to make sure that the only the country code passes to the column.
			$countries = gmw_get_countries_list_array();

			// look for the country code based on the country name.
			$country_code = array_search( ucwords( $location_data['country_name'] ), $countries, true );

			// get the country code from the list.
			$location_data['country_code'] = ! empty( $country_code ) ? $country_code : '';
		}

		global $wpdb;

		$table          = self::get_table();
		$default_values = self::default_values();

		// update existing location.
		if ( $update ) {

			// verify location ID.
			if ( ! is_int( $location_id ) || 0 === $location_id ) {
				return false;
			}

			// modify the new location args before saving.
			do_action( 'gmw_pre_update_location', $location_data, $saved_location );
			do_action( "gmw_pre_update_{$location_data['object_type']}_location", $location_data, $saved_location );

			$new_location_data = array();
			$new_format        = array();

			// updated time based on current date/time.
			$location_data['updated'] = current_time( 'mysql' );

			// Order the location data and the location format based on the default values.
			foreach ( $default_values as $key => $field ) {

				if ( array_key_exists( $key, $location_data ) ) {

					$new_location_data[ $key ] = $location_data[ $key ];
					$new_format[ $key ]        = self::$format[ $key ];
				}
			}

			// make sure that there are no extra columns.
			$location_data = array_intersect_key( $location_data, $default_values );

			// update location.
			$wpdb->update(
				$table,
				$new_location_data,
				array( 'ID' => $location_id ),
				$new_format,
				array( '%d' )
			); // WPCS: db call ok, cache ok, unprepared SQL ok.

			$updated = true;

			do_action( 'gmw_location_updated', $location_data );
			do_action( "gmw_{$location_data['object_type']}_location_updated", $location_data );

			// Create new location.
		} else {

			// Merge location values with default values and make sure that there are no extra cloumns.
			$location_data = wp_parse_args( array_intersect_key( $location_data, $default_values ), $default_values );

			// insert new location to database.
			$wpdb->insert( $table, $location_data, self::get_format() ); // WPCS: db call ok, cache ok, unprepared SQL ok.

			// get the new location ID.
			$location_id = $wpdb->insert_id;

			$updated = false;

			// append Location ID to location data array.
			$location_data = array( 'ID' => $location_id ) + $location_data;
		}

		// make it into an object.
		$location_data = (object) $location_data;

		// set updated location in cache.
		wp_cache_set( $location_id, $location_data, 'gmw_location' );
		wp_cache_set( $location_data->object_type . '_' . $location_data->object_id, $location_data, 'gmw_location' );
		wp_cache_delete( $location_data->object_type . '_' . $location_data->object_id, 'gmw_locations' );

		// do some custom functions once location saved.
		do_action( 'gmw_save_location', $location_id, $location_data, $updated );
		do_action( "gmw_save_{$location_data->object_type}_location", $location_id, $location_data, $updated );

		return $location_id;
	}

	/**
	 * Update an existing location.
	 *
	 * When location ID is provided, it will be used to update the location.
	 *
	 * Otherwise, a default location will be retrieved based on object type and object ID.
	 *
	 * @param  [type] $args [description].
	 *
	 * @return [type]       [description]
	 */
	public static function update( $args ) {

		if ( empty( $args['ID'] ) ) {

			$saved_location = self::get_by_object( $args['object_type'], $args['object_id'] );

			if ( ! empty( $saved_location ) ) {
				$args = array( 'ID' => $saved_location->ID ) + $args;
			}
		}

		return self::insert( $args );
	}

	/**
	 * Deprecated - use update() instead
	 *
	 * @param array $args array of lcoation arguments.
	 *
	 * @return [type] [description]
	 */
	public static function update_location( $args ) {
		return self::update( $args );
	}

	/**
	 * Deprecated. Function self::get() now gets the location by ID.
	 *
	 * @param  integer $location_id [description].
	 * @param  [type]  $output      [description].
	 * @param  boolean $cache       [description].
	 *
	 * @return [type]               [description]
	 */
	public static function get_by_id( $location_id = 0, $output = OBJECT, $cache = true ) {
		return self::get( $location_id, $output, $cache );
	}

	/**
	 * Get location from database by location ID.
	 *
	 * @param  integer  $location_id location ID.
	 *
	 * @param  constant $output      OBJECT || ARRAY_A || ARRAY_N  the output type of the location data.
	 *
	 * @param  boolean  $cache       Look for location in cache.
	 *
	 * @return object || Array return the location data
	 *
	 * @since 3.2
	 */
	public static function get( $location_id = 0, $output = OBJECT, $cache = true ) {

		// verify location ID.
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		global $wpdb;

		// look for location in cache if needed.
		$location = $cache ? wp_cache_get( $location_id, 'gmw_location' ) : false;

		// get location from database if not found in cache.
		if ( false === $location ) {

			$table    = self::get_table();
			$location = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT *, latitude as lat, longitude as lng, title as location_name, featured as featured_location
	            	FROM   $table
	            	WHERE  ID = %d",
					$location_id
				),
				OBJECT
			); // WPCS: db call ok, cache ok, unprepared SQL ok.

			// set location in cache if found.
			if ( ! empty( $location ) ) {
				wp_cache_set( $location->object_type . '_' . $location->object_id, $location, 'gmw_location' );
				wp_cache_set( $location_id, $location, 'gmw_location' );
			}
		}

		// if no location found.
		if ( empty( $location ) ) {
			return null;
		}

		// make sure ID in integer.
		$location->ID = ( int ) $location->ID;

		// conver to array if needed.
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
	 *
	 * @param  integer  $object_id   the object ID. post ID, User ID...
	 *
	 * @param  constant $output     OBJECT || ARRAY_A || ARRAY_N  the output type of the location data.
	 *
	 * @param  boolean  $cache      Look for location in cache.
	 *
	 * @return object || Array return the location data
	 *
	 * @since 3.2
	 */
	public static function get_by_object( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {

		// verify object type and object ID. If any of them empty use try_get_location function.
		if ( empty( $object_type ) || empty( $object_id ) ) {

			// try to get location usign global variables.
			return self::try_get_locations( true, $output, $cache );
		}

		// verify object types.
		if ( ! in_array( $object_type, GMW()->object_types, true ) ) {

			gmw_trigger_error( 'Trying to get a location using invalid object type.' );

			return false;
		}

		// Verify object ID.
		if ( ! self::verify_id( $object_id ) ) {

			gmw_trigger_error( 'Trying to get a location using invalid object ID.' );

			return false;
		}

		$object_id = absint( $object_id );

		// look for locations in cache if needed.
		$location = $cache ? wp_cache_get( $object_type . '_' . $object_id, 'gmw_location' ) : false;

		if ( false === $location ) {

			global $wpdb;

			$blog_id  = gmw_get_blog_id( $object_type );
			$table    = self::get_table();
			$location = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT *, latitude as lat, longitude as lng, title as location_name, featured as featured_location
		            FROM     $table
		            WHERE    blog_id     = %d 
		            AND      object_type = %s 
		            AND      object_id   = %d
		            ORDER BY location_type ASC, parent DESC, ID ASC", // We first get locations without location type, then by Parent, then by ID.
					$blog_id,
					$object_type,
					$object_id
				),
				OBJECT
			); // WPCS: unprepared SQL ok, db call ok.

			// save to cache if location found.
			if ( ! empty( $location ) ) {
				wp_cache_set( $object_type . '_' . $object_id, $location, 'gmw_location' );
				wp_cache_set( $location->ID, $location, 'gmw_location' );
			}
		}

		// if no location found.
		if ( empty( $location ) ) {
			return null;
		}

		// make sure ID in integer.
		$location->ID = (int) $location->ID;

		// convert to array if needed.
		if ( ARRAY_A == $output || ARRAY_N == $output ) {
			$location = gmw_to_array( $location, $output );
		}

		return $location;
	}

	/**
	 * This function used to get all locations by object.
	 *
	 * Instead use the new self::get_locations_by_object() function.
	 *
	 * We will leave this function for the future for more of a general uses.
	 *
	 * @param  string  $object_type [description].
	 * @param  integer $object_id   [description].
	 * @param  [type]  $output      [description].
	 * @param  boolean $cache       [description].
	 * @return [type]               [description]
	 */
	public static function get_locations( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {
		return self::get_locations_by_object( $object_type, $object_id, $output, $cache );
	}

	/**
	 * Get all locations based on object_type - object_ID pair.
	 *
	 * @param  string   $object_type the object type ( post, user... ).
	 * @param  integer  $object_id   the object ID  ( post ID, User ID... ).
	 * @param  constant $output      OBJECT || ARRAY_A || ARRAY_N  the output type of the location data.
	 * @param  boolean  $cache       Look for location in cache.
	 *
	 * @return array of locations data.
	 *
	 * since 3.2
	 */
	public static function get_locations_by_object( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {

		// try to get location if object type/ID do not exist.
		if ( empty( $object_type ) || empty( $object_id ) ) {

			return self::try_get_locations( false, $output, $cache );
		}

		// verify object type.
		if ( ! in_array( $object_type, GMW()->object_types, true ) ) {

			gmw_trigger_error( 'Trying to get a location using invalid object type.' );

			return false;
		}

		// verify object ID.
		if ( ! is_numeric( $object_id ) || ! absint( $object_id ) ) {

			gmw_trigger_error( 'Trying to get a locations using invalid object ID.' );

			return false;
		}

		$object_id = absint( $object_id );

		// look for locations in cache.
		$locations = $cache ? wp_cache_get( $object_type . '_' . $object_id, 'gmw_locations' ) : false;

		// if no locations found in cache get it from database.
		if ( false === $locations ) {

			global $wpdb;

			$blog_id   = gmw_get_blog_id( $object_type );
			$table     = self::get_table();
			$locations = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT *, latitude as lat, longitude as lng, title as location_name, featured as featured_location
		            FROM   $table
		            WHERE  blog_id     = %d 
		            AND    object_type = %s 
		            AND    object_id   = %d",
					$blog_id,
					$object_type,
					$object_id
				),
				OBJECT
			); // WPCS: db call ok, cache ok, unprepared SQL ok.

			// save to cache if location found.
			if ( ! empty( $locations ) ) {
				wp_cache_set( $object_type . '_' . $object_id, maybe_serialize( $locations ), 'gmw_locations' );
			}
		}

		// if no location found.
		if ( empty( $locations ) ) {
			return array();
		}

		$locations = maybe_unserialize( $locations );

		// convert to array of arrays.
		if ( ARRAY_A == $output || ARRAY_N == $output ) {
			foreach ( $locations as $key => $value ) {
				$locations[ $key ] = gmw_to_array( $value, $output );
			}
		}

		return $locations;
	}

	/**
	 * Query Address Fields
	 *
	 * Filter query based on specific address fields.
	 *
	 * This filter does not do a proximity search but pulls locations from database
	 *
	 * matching exactly the address fields filters.
	 *
	 * @since 3.0
	 *
	 * @param  array $address_filters [description].
	 *
	 * @param  array $gmw             [description].
	 *
	 * @return [type] [description]
	 */
	public static function query_address_fields( $address_filters = array(), $gmw = array() ) {

		// modify the filters filters.
		$address_filters = apply_filters( 'gmw_query_address_fields', $address_filters, $gmw );

		// abort if nothing passed.
		if ( empty( $address_filters ) ) {
			return '';
		}

		global $wpdb;

		$output = '';

		// build the query.
		foreach ( $address_filters as $key => $value ) {

			if ( empty( $value ) ) {
				continue;
			}

			$key = str_replace( '_filter', '', $key );

			// filter region.
			if ( in_array( $key, array( 'region_name', 'region_code', 'state' ), true ) ) {

				$output .= $wpdb->prepare( ' AND ( gmw_locations.region_name = %s OR gmw_locations.region_code = %s )', $value, $value );

				// filter country.
			} elseif ( in_array( $key, array( 'country_name', 'country_code', 'country' ), true ) ) {

				$output .= $wpdb->prepare( ' AND ( gmw_locations.country_name = %s OR gmw_locations.country_code = %s )', $value, $value );

				// filter postcode.
			} elseif ( 'postcode' === $key || 'zipcode' === $key ) {

				$output .= $wpdb->prepare( ' AND gmw_locations.postcode = %s', $value );

				// filter the rest.
			} elseif ( in_array( $key, array( 'street', 'county', 'neighborhood', 'city' ), true ) ) {

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

		// default values.
		$args = wp_parse_args(
			$args,
			array(
				'object_type'        => 'post',
				'lat'                => false,
				'lng'                => false,
				'radius'             => false,
				'units'              => 'imperial',
				'unique'             => '',
				'count'              => '',
				'offset'             => '',
				'paged'              => 1,
				'orderby'            => '',
				'object__in'         => '',
				'output_objects_id'  => true,
				'multiple_locations' => false,
			)
		);

		$args = apply_filters( 'gmw_get_locations_data_args', $args, $gmw );

		if ( empty( $db_fields ) ) {

			// default db fields.
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

		// modify the database columns if needed.
		$db_fields = apply_filters( 'gmw_get_locations_db_fields', $db_fields, $gmw );
		$db_fields = apply_filters( "gmw_get_{$args['object_type']}_locations_db_fields", $db_fields, $gmw );

		// for cache key.
		//$args['db_fields'] = $db_fields;

		$count  = 0;
		$output = '';

		// generate the db fields.
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
				// eventually we want to completely move to using latitude and longitude.
				if ( 'latitude' === $field[0] || 'longitude' === $field[0] ) {
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
			// prepare for cache.
			$hash            = md5( wp_json_encode( $args ) );
			$query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_' . $args['object_type'] . '_locations' );
		}

		if ( ! $internal_cache || false === ( $locations = get_transient( $query_args_hash ) ) ) {

			global $wpdb;

			$clauses['select']          = 'SELECT';
			$clauses['fields']          = $args['db_fields'];
			$clauses['distance']        = '';
			$clauses['from']            = "FROM {$wpdb->base_prefix}{$db_table} gmw_locations";
			$clauses['where']           = $wpdb->prepare( " WHERE gmw_locations.object_type = '%s'", $args['object_type'] );
			$clauses['address_filters'] = '';
			$clauses['having']          = '';
			$clauses['orderby']         = 'ORDER BY gmw_locations.ID';
			$clauses['limit']           = '';

			if ( ! empty( $args['object__in'] ) ) {

				// escape terms ID.
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

				// Get earth radius based on units.
				if ( 'imperial' === $args['units'] || 3959 == $args['units'] || 'miles' === $args['units'] ) {
					$earth_radius = 3959;
					$units        = 'mi';
					$degree       = 69.0;
				} else {
					$earth_radius = 6371;
					$units        = 'km';
					$degree       = 111.045;
				}

				// add units to locations data.
				$clauses['fields'] .= ", '{$units}' AS units";

				/**
				 * Since these values are repeatable, we escape them previous.
				 *
				 * The query instead of running multiple prepares.
				 */
				$lat = esc_sql( $args['lat'] );
				$lng = esc_sql( $args['lng'] );

				$clauses['distance'] = ", ROUND( {$earth_radius} * acos( cos( radians( {$lat} ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance";

				// make sure we pass only numeric or decimal as radius.
				if ( ! empty( $args['radius'] ) && is_numeric( $args['radius'] ) ) {

					$radius = esc_sql( $args['radius'] );

					// calculate the between point.
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

			// query the locations.
			$locations = $wpdb->get_results(
				implode( ' ', apply_filters( 'gmw_get_locations_query_clauses', $clauses, $args, $gmw ) )
			); // WPCS: db call ok, cache ok, unprepared SQL ok.

			/**
			 * Collect locations into an array of objects and locations data.
			 *
			 * This way we can easily append objects with thier location.
			 *
			 * This feature is enabled by default, but can be disabled
			 *
			 * If not needed to preserve performance.
			 */
			if ( $args['output_objects_id'] ) {

				$locations_data = array(
					'objects_id'     => array(),
					'featured_ids'   => array(),
					'locations_data' => array(),
				);

				// abort if no locations found.
				if ( ! empty( $locations ) ) {

					// modify the locations query.
					foreach ( $locations as $value ) {

						// collect objects id into an array.
						$locations_data['objects_id'][] = $value->object_id;

						if ( isset( $value->featured_location ) && $value->featured_location == 1 ) {
							$locations_data['featured_ids'][] = $value->object_id;
						}

						// replace array keys with object id to be able to do some queries later.
						$locations_data['locations_data'][ $value->object_id ] = $value;
					}
				}

				$locations = $locations_data;
			}

			// set new query in transient only if cache enabled.
			if ( $internal_cache ) {
				set_transient( $query_args_hash, $locations, GMW()->internal_cache_expiration );
			}
		}

		return $locations;
	}

	/**
	 * Change post location status
	 *
	 * @since 3.0
	 *
	 * @param  integer $location_id [description].
	 * @param  integer $status      [description].
	 *
	 * @return [type]               [description]
	 */
	public static function set_status( $location_id = 0, $status = 1 ) {

		$status      = self::verify_id( $status );
		$location_id = self::verify_id( $location_id );

		if ( ! $location_id ) {
			return false;
		}

		global $wpdb;

		return $wpdb->query(
			$wpdb->prepare(
				"
	            UPDATE {$wpdb->base_prefix}gmw_locations 
	            SET   `status`      = %s 
	            WHERE `ID`          = %s",
				array( $status, $location_id )
			)
		); // WPCS: db call ok, cache ok, unprepared SQL ok.
	}

	/**
	 * Delete location using location ID.
	 *
	 * Deprecated - use self::delete() instaed.
	 *
	 * The location data and all associated location meta will be deleted.
	 *
	 * @param  integer $location_id the location ID.
	 *
	 * @param  boolean $delete_meta true or false if to delete the location meta belog to that location.
	 *
	 * @return boolean  true if deleted false if failed.
	 *
	 * @since 3.0
	 */
	public static function delete_by_id( $location_id = 0, $delete_meta = true ) {
		return self::delete( $location_id, $delete_meta );
	}

	/**
	 * Delete location using location ID or location object.
	 *
	 * The location data and all associated location meta will be deleted
	 *
	 * @param  integer||object $location the location ID || location object.
	 *
	 * @param  boolean         $delete_meta true or false if to delete the location meta belog to that location.
	 *
	 * @return boolean  true if deleted false if failed
	 *
	 * @since 3.2
	 */
	public static function delete( $location = 0, $delete_meta = true ) {

		// if location is not an object.
		if ( ! is_object( $location ) ) {

			// verify location ID.
			if ( ! self::verify_id( $location ) ) {
				return false;
			}

			// get location to make sure it exists.
			// this will get the parent location.
			$location = self::get( $location );
		}

		// abort if no location found.
		if ( empty( $location ) ) {
			return false;
		}

		do_action( 'gmw_before_location_deleted', $location->ID, $location );
		do_action( 'gmw_before_' . $location->object_type . '_location_deleted', $location->ID, $location );

		global $wpdb;

		// delete location from database.
		$table   = self::get_table();
		$deleted = $wpdb->delete(
			$table,
			array( 'ID' => $location->ID ),
			array( '%d' )
		); // WPCS: db call ok, cache ok, unprepared SQL ok.

		// abort if failed to delete.
		if ( empty( $deleted ) ) {
			return false;
		}

		// When deleting a location, we check if there are other location for the same object ID.
		// And if there are, we need to make sure that there is one set as the parent location.
		if ( ! empty( $location->object_type ) && ! empty( $location->object_id ) ) {

			$parent_location = self::get_by_object( $location->object_type, $location->object_id, OBJECT, false );

			if ( ! empty( $parent_location ) && empty( $parent_location->parent ) ) {

				$wpdb->update(
					$wpdb->base_prefix . 'gmw_locations',
					array(
						'parent' => 1,
					),
					array(
						'ID' => $parent_location->ID,
					),
					array(
						'%d',
					)
				); // WPCS: db call ok, cache ok.
			}
		}

		// clear locations from cache.
		wp_cache_delete( $location->object_type . '_' . $location->object_id, 'gmw_location' );
		wp_cache_delete( $location->ID, 'gmw_location' );
		wp_cache_delete( $location->object_type . '_' . $location->object_id, 'gmw_locations' );

		do_action( 'gmw_location_deleted', $location->ID, $location );
		do_action( 'gmw_' . $location->object_type . '_location_deleted', $location->ID, $location );

		// delete the location metadata associated with this location if needed.
		if ( true == $delete_meta ) {
			GMW_Location_Meta::delete_all( $location->ID );
		}

		return $location->ID;
	}

	/**
	 * Delete location using object type and object ID pair
	 *
	 * The parent location data and all associated location meta will be deleted.
	 *
	 * @param  string  $object_type object type ( post, user... ).
	 *
	 * @param  integer $object_id   object id ( post iD, user ID... ).
	 *
	 * @param  boolean $delete_meta true or false if to delete the location meta belog to that location.
	 *
	 * @return boolean true for deleted false for failed
	 *
	 * @since 3.2
	 */
	public static function delete_by_object( $object_type = '', $object_id = 0, $delete_meta = true ) {

		// verify data.
		if ( empty( $object_type ) || empty( $object_id ) ) {
			return false;
		}

		// verify object type.
		if ( ! in_array( $object_type, GMW()->object_types, true ) ) {

			gmw_trigger_error( 'Trying to delete a location using invalid object type.' );

			return false;
		}

		// verify object ID.
		if ( ! is_numeric( $object_id ) || ! absint( $object_id ) ) {

			gmw_trigger_error( 'Trying to delete a location using invalid object ID.' );

			return false;
		}

		$object_id = absint( $object_id );

		// get location to make sure it exists
		// this will get the parent location.
		$location = self::get_by_object( $object_type, $object_id );

		// abort if no location found.
		if ( empty( $location ) ) {
			return false;
		}

		return self::delete( $location, $delete_meta );
	}

	/**
	 * This function used to delete all locations by object.
	 *
	 * Instead, use the new self::delete_locations_by_object() function.
	 *
	 * We will leave this function for the future for more of a general uses.
	 *
	 * @param  string  $object_type [description].
	 *
	 * @param  integer $object_id   [description].
	 *
	 * @param  [type]  $delete_meta [description].
	 *
	 * @return [type]               [description]
	 */
	public static function delete_locations( $object_type = '', $object_id = 0, $delete_meta = true ) {
		return self::delete_locations_by_object( $object_type, $object_id, $delete_meta );
	}

	/**
	 * Delete all locations based on object type - and object ID pair
	 *
	 * All locations data and all associated location meta will be deleted.
	 *
	 * @param  string  $object_type object type ( post, user... ).
	 * @param  integer $object_id   object id ( post iD, user ID... ).
	 * @param  boolean $delete_meta to delete associate location meta.
	 *
	 * @return boolean true for deleted false for failed
	 *
	 * @since 3.0
	 */
	public static function delete_locations_by_object( $object_type = '', $object_id = 0, $delete_meta = true ) {

		// verify data.
		if ( empty( $object_type ) || empty( $object_id ) ) {
			return false;
		}

		// verify object type.
		if ( ! in_array( $object_type, GMW()->object_types, true ) ) {

			gmw_trigger_error( 'Trying to delete a location using invalid object type.' );

			return false;
		}

		// verify object ID.
		if ( ! is_numeric( $object_id ) || ! absint( $object_id ) ) {

			gmw_trigger_error( 'Trying to delete a location using invalid object ID.' );

			return false;
		}

		$object_id = absint( $object_id );

		global $wpdb;

		$blog_id = gmw_get_blog_id( $object_type );

		// delete location from database.
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
		); // WPCS: db call ok, cache ok, unprepared SQL ok.

		// abort if failed to delete.
		if ( empty( $deleted ) ) {
			return false;
		}

		// clear locations from cache.
		wp_cache_delete( $object_type . '_' . $object_id, 'gmw_location' );
		wp_cache_delete( $object_type . '_' . $object_id, 'gmw_locations' );

		// delete the location metadata associated with this location if needed.
		if ( true == $delete_meta ) {
			GMW_Location_Meta::delete_all( $location->ID );
		}

		return $deleted;
	}
}
