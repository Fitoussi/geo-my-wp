<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'GMW_Location' ) ) :

/**
 * Class GMW_Location
 *
 * This class responsible for location and locationmeta process. Craete, update, delete....
 * 
 */
class GMW_Location {

	/**
	 * GEO my WP locations table
	 * @var string
	 */
	public static $locations_table = 'gmw_locations';

	/**
	 * GEO my WP Location meta table
	 * @var string
	 */
	public static $locationmeta_table = 'gmw_locationmeta';

	/**
	 * Locations table format
	 * @var array
	 */
	public static $location_format = array( 
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
		'%s'
	);

	/**
	 * [__construct description]
	 */
	function __construct() {}

	/**
	 * Get locations table 
	 * 
	 * @param  boolean $base_prefix use based prefix instead of blog prefix when in multisite.
	 * 
	 * @return [type]               [description]
	 */
	public static function get_locations_table() {

		global $wpdb;

		$table = $wpdb->base_prefix . self::$locations_table;

		return $table;
	}

	/**
	 * Get locationmeta table 
	 * 
	 * @param  boolean $base_prefix use based prefix instead of blog prerfix when in multisite.
	 * 
	 * @return [type]               [description]
	 */
	public static function get_locationmeta_table() {

		global $wpdb;

		$table = $wpdb->base_prefix . self::$locationmeta_table;

		return $table;
	}

	/**
	 * default locations database table values
	 * 
	 * @return array
	 */
	public static function default_location_database_values() {

		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 1;
	
		return array( 
			'object_type'		=> '',
			'object_id'			=> 0,
			'blog_id'			=> gmw_get_blog_id(),
			'user_id'			=> $user_id,
			'status'        	=> 1,
			'parent'			=> 0,
			'featured'			=> 0,
			'title'				=> '',
			'latitude'          => 0.000000,
			'longitude'         => 0.000000,
			'street_number' 	=> '',
			'street_name' 		=> '',
			'street'			=> '',
			'premise'       	=> '',
			'neighborhood'  	=> '',
			'city'          	=> '',
			'county'			=> '',
			'region_name'   	=> '',
			'region_code'   	=> '',
			'postcode'      	=> '',
			'country_name'  	=> '',
			'country_code'  	=> '',
			'address'			=> '',
			'formatted_address' => '',
			'place_id'			=> '',
			'map_icon'			=> '_default.png',
			'created'       	=> '0000-00-00 00:00:00',
			'updated'       	=> '0000-00-00 00:00:00',
		);
	}

	/**
	 * helper to verify an ID.
	 * 
	 * @param numeric $id ID as numeric values
	 * 
	 * @return absint   
	 *
	 * DONE 
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
	 * DONE
	 */
	private static function try_get_locations( $parent = false, $output = OBJECT, $cache = true ) {

		// get common globals
		global $post, $comment, $user;

		$location = $found = false;

		// check for post ID and try to get the location if found
		if ( ! empty( $post->ID ) ) {

			$object_type = 'post';
			$object_id   = $post->ID;
			$found 		 = true;
		
		// otherwise look for user ID
		} elseif ( ! empty( $user->ID ) ) {
			
			$object_type = 'user';
			$object_id   = $user->ID;
			$found 		 = true; 
		
		// Otherwise, maybe comment ID
		} elseif ( ! empty( $comment->comment_ID ) ) {

			$object_type = 'comment';
			$object_id   = $comment->comment_ID;
			$found 		 = true; 
		}

		// if object type and object ID were found, get the location from database
		if ( $found ) {
			$location = $parent == true ? self::get_location( $object_type, $object_id, $output, $cache ) : self::get_locations( $object_type, $object_id, $output, $cache );
		}

		return $location;
	}

	/**
	 * Save location - Save location to gmw_locations database table.
	 *
	 * To save a location you need to pass an array of location data which includes the location
	 *
	 * fields and the object data.
	 *
	 * See the default_location_database_values array above for the location fields you need to pass.
	 * 	
	 * @param  array $args array of location fields and data.
	 * 
	 * @return int location ID 
	 *
	 *  DONE
	 */
	public static function update_location( $args ) {

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
		$location_data = wp_parse_args( $args, self::default_location_database_values() );

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
		$saved_location = self::get_location( $location_data['object_type'], $location_data['object_id'] );

		global $wpdb;

		$table = self::get_locations_table();

		// modify the new location args before saving
		$location_data = apply_filters( "gmw_pre_save_location_data", $location_data, $saved_location );
		$location_data = apply_filters( "gmw_pre_save_{$location_data['object_type']}_location_data", $location_data, $saved_location );

		// do some custom functions before saving location
		do_action( "gmw_pre_save_location", $location_data, $saved_location );
		do_action( "gmw_pre_save_{$location_data['object_type']}_location", $location_data, $saved_location );

		// insert new location if not already exists in database
		if ( ! $saved_location ) {

			// udpate the current data - time
			$location_data['created'] = current_time( 'mysql' );

			// insert new location to database
			$wpdb->insert( $table, $location_data, self::$location_format );
		
			// get the new location ID
			$location_id = $wpdb->insert_id;

			$updated = false;

		// otherwise, update existing location
		} else {

			// get existing location ID
			$location_id = isset( $saved_location->ID ) ? (int) $saved_location->ID : 0;

			// verify location ID
			if ( ! is_int( $location_id ) || $location_id === 0 ) {
				return false;
			}

			// Keep created time as its original time
			$location_data['created'] = $saved_location->created;

			// updated time based on current time
			$location_data['updated'] = current_time( 'mysql' );

			$location_format = self::$location_format;

			// update location
			$wpdb->update( 
				$table, 
				$location_data, 
				array( 'ID' => $location_id ), 
				self::$location_format, 
				array( '%d' ) 
			);

			$updated = true;
		}

		// append Location ID to location data array
		$location_data = array( 'ID' => $location_id ) + $location_data;

		// make it into an object
		$location_data = ( object ) $location_data;

		// do some custom functions once location saved
		do_action( "gmw_save_location", $location_id, $location_data, $updated );
		do_action( "gmw_save_{$location_data->object_type}_location", $location_id, $location_data, $updated );

		// set updated location in cache
		//wp_cache_set( $location_id, $location_data, 'gmw_locations' );
		wp_cache_set( $location_data->object_type.'_'.$location_data->object_id, $location_data, 'gmw_location' );
        wp_cache_set( $location_id, $location_data, 'gmw_location' );

        wp_cache_delete( $location_data->object_type.'_'.$location_data->object_id, 'gmw_locations' );

		return $location_id;
	}

	/**
	 * Check if location exists in database
	 * 
	 * @param int $location_id location ID
	 * 
	 * @return boolean    
	 *
	 * DONE 
	 */
	public static function location_exists( $location_id = 0 ) {

		// verify location ID
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}


		global $wpdb, $blog_id;
		
		// database table name
		$table = self::get_locations_table();

		// look for the location in database
		$location_id = $wpdb->get_var( 
			$wpdb->prepare( "
				SELECT ID 
				FROM   $table 
				WHERE  ID = %d",
				$location_id 
			) 
		);

		return ! empty( $location_id ) ? true : false;
	}

	/**
	 * Get the object of a location
	 * 
	 * @param  integer $location_id [description]
	 * @return [type]               [description]
	 */
	public static function get_location_object_type( $location_id = 0 ) {

		// verify location ID
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		global $wpdb;

		// database table name
		$table = self::get_locations_table();

		// look for the location in database
		$object_type = $wpdb->get_var( 
			$wpdb->prepare( "
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
	 * DONE
	 */
	public static function get_location_by_id( $location_id = 0, $output = OBJECT, $cache = true ) {

		// verify location ID
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		global $wpdb;

		// look for location in cache if needed
		$location = $cache ? wp_cache_get( $location_id, 'gmw_location' ) : false;

		// get location from database if not found in cache
		if ( false === $location ) {

			$table    = self::get_locations_table();
			$location = $wpdb->get_row( 
				$wpdb->prepare( "
					SELECT *
	            	FROM   $table
	            	WHERE  ID = %d", 
	            	$location_id 
		        ), 
	        OBJECT );

			// set location in cache if found
			if ( ! empty( $location ) ) {
				wp_cache_set( $location->object_type.'_'.$location->object_id, $location, 'gmw_location' );
	        	wp_cache_set( $location_id, $location, 'gmw_location' );
	        }
	    }
		
		// if no location found
		if ( empty( $location ) ) {
			return null;
		}

		// conver to array if needed
		if ( $output == ARRAY_A || $output == ARRAY_N ) {
			$location = gmw_to_array( $location, $output );
		}
			 
	    return $location;
	}

	/**
	 * Get location data based on object_type and object_id pairs.
	 *
	 * The returned location will be the parent location in case that the object_type - object_id pair has multiple locations
	 * 
	 * @param  string  $object_type the object type post, user...
	 * @param  integer $object_id   the object ID. post ID, User ID...
	 * @param  constant $output     OBJECT || ARRAY_A || ARRAY_N  the output type of the location data 
	 * @param  boolean  $cache      Look for location in cache
	 *
	 * @return object || Array return the location data
	 *
	 * DONE
	 */
	public static function get_location( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {

		// verify object type and object ID. If any of them empty use try_get_location function
		if ( empty( $object_type ) || empty( $object_id ) ) {

			// try to get location usign global variables
			return self::try_get_locations( true, $output, $cache );
		} 

		// verify object types
		if ( ! in_array( $object_type, array( 'post', 'user', 'comment', 'term' ) ) ) {

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
		$location = $cache ? wp_cache_get( $object_type.'_'.$object_id, 'gmw_location' ) : false;

		if ( false === $location ) {
			
			global $wpdb;

			$blog_id  = gmw_get_blog_id( $object_type );
			$table    = self::get_locations_table();
			$location = $wpdb->get_row( 
				$wpdb->prepare( "
					SELECT *
		            FROM   $table
		            WHERE  blog_id     = %d 
		            AND    object_type = %s 
		            AND    object_id   = %d
		            AND    parent      = 0",
		            $blog_id,
            		$object_type, 
            		$object_id 
            	), 
            OBJECT );

			// save to cache if location found
			if ( ! empty( $location ) ) {
            	wp_cache_set( $object_type.'_'.$object_id, $location, 'gmw_location' );
            	wp_cache_set( $location->ID, $location, 'gmw_location' );
            }
		}
          
        // if no location found
		if ( empty( $location ) ) {
			return null;
		}

		// convert to array if needed
		if ( $output == ARRAY_A || $output == ARRAY_N ) {
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
	 * DONE
	 */
	public static function get_locations( $object_type = '', $object_id = 0, $output = OBJECT, $cache = true ) {

		// try to get location if object type/ID do not exist
		if ( empty( $object_type ) || empty( $object_id ) ) {

			return self::try_get_locations( false, $output, $cache );
		} 

		// verify object type
		if ( ! in_array( $object_type, array( 'post', 'user', 'comment', 'term' ) ) ) {

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
		$locations = $cache ? wp_cache_get( $object_type.'_'.$object_id, 'gmw_locations' ) : false;

		// if no location found in cache get it from database
		if ( false === $locations ) {

			global $wpdb;

			$blog_id   = gmw_get_blog_id( $object_type );
			$table     = self::get_locations_table();
			$locations = $wpdb->get_results( 
				$wpdb->prepare( "
					SELECT *
		            FROM   $table
		            WHERE  blog_id     = %d 
		            AND    object_type = %s 
		            AND    object_id   = %d", 
		            $blog_id,
            		$object_type, 
            		$object_id 
            	), 
            OBJECT );

			// save to cache if location found
			if ( ! empty( $locations ) ) {
            	wp_cache_set( $object_type.'_'.$object_id, serialize( $locations ), 'gmw_locations' );
            }
		}
          
        // if no location found
		if ( empty( $locations ) ) {
			return null;
		}

		$locations = maybe_unserialize( $locations );

		// convert to array of arrays
		if ( $output == ARRAY_A || $output == ARRAY_N ) {
			foreach ( $locations as $key => $value ) {
				$locations[$key] = gmw_to_array( $value, $output );
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
        		
        		$output .= $wpdb->prepare( " AND ( gmw_locations.region_name = %s OR gmw_locations.region_code = %s )", $value, $value );
        	
        	// filter country
        	} elseif ( in_array( $key, array( 'country_name', 'country_code', 'country' ) ) ) {
        		
        		$output .= $wpdb->prepare( " AND ( gmw_locations.country_name = %s OR gmw_locations.country_code = %s )", $value, $value );
        	
        	// filter postcode
        	} elseif ( $key  == 'postcode' || $key  == 'zipcode' ) {
        	
        		$output .= $wpdb->prepare( " AND gmw_locations.postcode = %s", $value );
        	
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
		$args = wp_parse_args( $args, array(
			'object_type' 	 => 'post',
			'lat'		  	 => false,
			'lng'		  	 => false,
			'radius'	  	 => false,
			'units'		  	 => 'imperial'
		) );
		
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
		        'map_icon'
		    );
		}

		// modify the database columns if needed
		$db_fields = apply_filters( 'gmw_get_locations_db_fields', $db_fields, $gmw );
		$db_fields = apply_filters( "gmw_get_{$args['object_type']}_locations_db_fields", $db_fields, $gmw );

		$count  = 0;
		$output = '';

		// generate the db fields
		foreach ( $db_fields as $field ) {

			if ( $count > 0 ) {
				$output .= ', ';
			}

			$count++;

			if ( strpos( $field, 'as' ) !== FALSE ) {
				
				$field = explode( ' as ', $field );
				
				$output .= "{$field[0]} as {$field[1]}";

				// Here we are including latitude and longitude fields
				// using their original field name.
				// for backward compatibility, we also need to have "lat" and "lng" 
				// in the location object and that is what we did in the line above.
				// The lat and lng field are too involve and need to carfully change it.
				// eventually we wont to completly move to using latitude and longitude.
				if ( $field[0] == 'latitude' || $field[0] == 'longitude' ) {
					$output .= ",gmw_locations.{$field[0]}";
				}

			} else {

				$output .= "gmw_locations.{$field}";
			}
		}
		
		$db_fields = $output;

		$args['db_fields'] = $db_fields;
        $args['db_table']  = $db_table;
        $args['address_filters'] = $address_filters;

        $internal_cache = GMW()->internal_cache;

        if ( $internal_cache ) {
	        
	        // prepare for cache
	        $hash = md5( json_encode( $args ) );

	        $query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_'.$args['object_type'].'_locations' );
	    }
           
        if ( ! $internal_cache || false === ( $locations_data = get_transient( $query_args_hash ) ) ) {
        //if ( 1 == 1 ) {	
            //print_r( 'locations query done' );
        
	        // Get earth radius based on units
	        if ( $args['units'] == 'imperial' ) {
	        	$earth_radius = 3959;
	        	$units = 'mi';
	        } else {
	        	$earth_radius = 6371;
	        	$units = 'km';
	        }

	        // add units to locations data
	        $db_fields .= ", '{$units}' AS units";

			global $wpdb;

			$clauses['select']	 = "SELECT";
			$clauses['fields'] 	 = $db_fields;
			$clauses['distance'] = "";
			$clauses['from']	 = "FROM {$wpdb->base_prefix}{$db_table} gmw_locations";

			$clauses['where'] = $wpdb->prepare( " WHERE gmw_locations.object_type = '%s' AND gmw_locations.parent = '0'", $args['object_type'] );

			// if object type uses database table as global, means it doesn't save locations per blog,
			// such as "user" we search within the entire database table. Otherwise, if data saved per blog, such as "post", we will filter locations based on blog ID
			//if ( ! in_array( $args['object_type'], GMW()->global_db_objects ) ) {	
			
			$loc_blog_id = gmw_get_blog_id( $args['object_type'] );

			if ( absint( $loc_blog_id ) ) {
				$clauses['where'] .= $wpdb->prepare( "AND gmw_locations.blog_id = %d", $loc_blog_id );
			}

			$clauses['address_filters'] = self::query_address_fields( $address_filters );
			$clauses['having']   = '';
			$clauses['orderby']  = '';

		 	// if address entered, do a proximity search and get locations within the radius entered.
	        if ( empty( $clauses['address_filters'] ) && ! empty( $args['lat'] ) && ! empty( $args['lng'] ) ) {

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
        		
	        	$clauses['distance'] = $wpdb->prepare( ", 
	        		ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance", 
	    			array( 
	    				$earth_radius, 
	    				$args['lat'], 
	    				$args['lng'], 
	    				$args['lat']
	    			) 
	    		);
				
	        	// make sure we pass only numeric or decimal as radius
	            if ( ! empty( $args['radius'] ) && is_numeric( $args['radius'] ) ) {
	        	   $clauses['having'] = $wpdb->prepare( "HAVING distance <= %s OR distance IS NULL", $args['radius'] );
	        	}

	        	$clauses['orderby'] = "ORDER BY distance";
		    } 

		  	// query the locations
		    $locations = $wpdb->get_results( 
		    	implode( ' ', apply_filters( 'gmw_get_locations_query_clauses', $clauses, $args['object_type'], $gmw ) ) 
		    );

		    $locations_data = array(
				'objects_id' 	 => array(),
				'locations_data' => array()
			);

		    // abort if no locations found
		    if ( ! empty( $locations ) ) {

			   	// modify the locations query
			    foreach ( $locations as $value ) {
						
					// collect objects id into an array
					$locations_data['objects_id'][] = $value->object_id;
					// replace array keys with object id to be able to do some queries later
					$locations_data['locations_data'][ $value->object_id ] = $value;
			    }
			}

            // set new query in transient only if cache enabled     
            if ( $internal_cache ) {  
            	set_transient( $query_args_hash, $locations_data, GMW()->internal_cache_expiration );
            }
        }

	    return $locations_data;
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
	 * DONE
	 */
	public static function delete_location( $object_type = '', $object_id = 0, $delete_meta = false ) {

		// verify data
		if ( empty( $object_type ) || empty( $object_id ) ) {
			return false;
		} 

		// verify object type
		if ( ! in_array( $object_type, array( 'post', 'user', 'comment', 'term' ) ) ) {

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
		$location = self::get_location( $object_type, $object_id );
		
		// abort if no location found
		if ( empty( $location ) ) {
			return false;
		}

		do_action( 'gmw_before_location_deleted', $location->ID, $location );
	    do_action( 'gmw_before_'.$object_type.'_location_deleted', $location->ID, $location );

		global $wpdb;

		// delete location from database
		$table   = self::get_locations_table();
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
	    do_action( 'gmw_'.$object_type.'_location_deleted', $location->ID, $location );

		// clear locations from cache
		wp_cache_delete( $object_type.'_'.$object_id, 'gmw_location' );
		wp_cache_delete( $location->ID, 'gmw_location' );
		wp_cache_delete( $object_type.'_'.$object_id, 'gmw_locations' );
	
		// delete the location metadata associated with this location if needed		
		if ( $delete_meta == true ) {
			self::delete_all_location_meta( $location->ID );
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
	 * DONE
	 */
	public static function delete_locations( $object_type = '', $object_id = 0, $delete_meta = false ) {

		// verify data
		if ( empty( $object_type ) || empty( $object_id ) ) {
			return false;
		} 

		// verify object type
		if ( ! in_array( $object_type, array( 'post', 'user', 'comment', 'term' ) ) ) {

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
		$table   = self::get_locations_table();
		$deleted = $wpdb->query( 
			$wpdb->prepare( "
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
		wp_cache_delete( $object_type.'_'.$object_id, 'gmw_location' );
		wp_cache_delete( $object_type.'_'.$object_id, 'gmw_locations' );
	
		// delete the location metadata associated with this location if needed		
		if ( $delete_meta == true ) {
			self::delete_all_location_meta( $location->ID );
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
	 * DONE
	 */
	public static function delete_location_by_id( $location_id = 0, $delete_meta = false ) {
		
        // check if location exists
        $location = self::get_location_by_id( $location_id );

        if ( empty( $location ) ) {
        	return false;
        }

		return self::delete_location( $location->object_type, $location->object_id, $delete_meta );
		
		/*
		global $wpdb;

		// delete the location
		$table = self::get_locations_table();

		// delete location from database
		$deleted = $wpdb->delete( 
			$table, 
			array( 'ID' => $location_id ), 
			array( '%d' ) 
		);

		if ( empty( $deleted ) ) {
			return false;
		}

		$object_type = $location->object_type;
		$object_id   = $location->object_id;

		// clear locations from cache
		wp_cache_delete( $location_id, 'gmw_location' );
		wp_cache_delete( $object_type.'_'.$object_id, 'gmw_location' );
		wp_cache_delete( $object_type.'_'.$object_id, 'gmw_locations' );

		if ( $delete_meta == true ) {
			//delete location meta
			self::delete_all_location_meta( $location_id );
		}

		return $location_id;
		*/
	}

	/**
	 * Location meta exists
	 *
	 * Verify if location meta exists based on its meta_id
	 * 		
	 * @param  absint $meta_id 
	 * 
	 * @return boolean true || false
	 *
	 * DONE
	 * 
	 */
	public static function location_meta_exists( $meta_id = 0 ) {

		if ( ! self::verify_id( $meta_id ) ) {
			return;
		}

		global $wpdb;

		$table   = self::get_locationmeta_table();
		$meta_id = $wpdb->get_var( 
			$wpdb->prepare( "
				SELECT meta_id 
				FROM   $table 
				WHERE  meta_id = %d", 
				$meta_id 
			) 
		);

		return ! empty( $meta_id ) ? true : false;
	}

	/**
	 * Get location meta ID
	 *
	 * Get a location meta ID by passing the location ID and meta_key
	 * 	
	 * @param  integer $location_id [description]
	 * @param  string  $meta_key    [description]
	 * 
	 * @return meta ID if found or false otherwise.
	 *
	 * DONE
	 * 
	 */
	public static function get_location_meta_id( $location_id = 0, $meta_key = '' ) {

		//verify location ID
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		// verify meta key
		if ( ! is_string( $meta_key ) ) {
			return false;
		}

		//sanitize meta_key
		$meta_key = sanitize_key( $meta_key );

		global $wpdb;

		$table = self::get_locationmeta_table();

		//get the meta ID from database if exists
		$meta_id = (int) $wpdb->get_var( 
			$wpdb->prepare( "
				SELECT meta_id
				FROM   $table
				WHERE  location_id = %d
				AND    meta_key    = %s
			", $location_id, $meta_key ) 
		);

		return ! empty( $meta_id ) ? $meta_id : false;
	}

	/**
	 * Update location meta.
	 *
	 * Update existing or create a new location meta
	 * 	
	 * @param  integer $location_id Location ID
	 * @param  string  $meta_key    meta key
	 * @param  string  $meta_value  meta value
	 * 
	 * @return [type]               [description]
	 *
	 * DONE 
	 *
	 * TODO : Cache
	 */
	public static function update_location_meta( $location_id = 0, $meta_key = '' , $meta_value = '' ) {

		// verify if location exists
		if ( ! self::location_exists( $location_id ) ) {
			return false;
		}

		// verify meta key
		if ( ! is_string( $meta_key ) || empty( $meta_key ) ) {

			trigger_error( 'Trying to update a location meta using invalid or missing meta key.', E_USER_NOTICE );

			return false;
		}

		global $wpdb;
		
		// sanitize meta_key
		$meta_key   = sanitize_key( $meta_key );
		$meta_value = maybe_serialize( $meta_value );
		$table 		= self::get_locationmeta_table();

		// check if meta already exists and get its ID from database
		$save_meta = $wpdb->get_row( 
			$wpdb->prepare( "
				SELECT 	meta_id, meta_value
				FROM 	$table
				WHERE 	location_id = %d
				AND 	meta_key    = %s
			", $location_id, $meta_key ) 
		);

		// abort if meta already exists and the value is the same.
		if ( ! empty( $save_meta->meta_id ) && ! empty( $save_meta->meta_value ) && $save_meta->meta_value == $meta_value ) {
			return;
		}

		$meta_id = ! empty( $save_meta->meta_id ) ? ( int ) $save_meta->meta_id : 0;

 		// the new meta data
		$metadata = array(
			'meta_id'	  => $meta_id,
			'location_id' => $location_id,
			'meta_key'    => $meta_key,
			'meta_value'  => $meta_value
		);

		$object_type = self::get_location_object_type( $location_id );

		// modify the new location meta args before saving
		$metadata = apply_filters( "gmw_pre_save_location_meta", $metadata );
		$metadata = apply_filters( "gmw_pre_save_{$object_type}_location_meta", $metadata );

		// if not yet exists, add new location meta
		if ( empty( $meta_id ) ) {

			//insert new location to database
			$wpdb->insert( 
				$table, 
				$metadata,
				array( 
					'%d', 
					'%d', 
					'%s', 
					'%s' 
				) 
			);

			//get the new location ID
			$meta_id = $wpdb->insert_id;

			$created = true;

		// otherwise, update existing location
		} else {

			// update location
			$wpdb->update( 
				$table, 
				$metadata, 
				array( 'meta_id' => $meta_id ), 
				array( 
					'%d', 
					'%d', 
					'%s', 
					'%s' 
				),
				array( '%d' ) 
			);	

			$created = false;
		}

		// do something after location meta updated
		do_action( "gmw_save_location_meta", $object_type, $meta_id, $location_id, $meta_key, $meta_value, $created );
		do_action( "gmw_save_{$object_type}_location_meta", $meta_id, $location_id, $meta_key, $meta_value, $created );

		self::check_location_meta_cache( $location_id, true );

		return $meta_id;
	}

	/**
	 * Create / Update multiple location metas
	 * 
	 * @since   3.0
	 *
	 * @param   string  $object_type the object being updated ( post, user, comment.... )
	 * @param   int     $location_id the ID of the corresponding location
	 * @param   array   $args location metadata in meta_key => meta value pairs
	 * 
	 * @return  array   array of updated/created metadata IDs
	 *
	 * DONE
	 *
	 * TODO : cache
	 * 
	 */
	public static function update_location_metas( $location_id = 0, $metadata = array(), $meta_value = false ) {

		// verify if location exists
		if ( ! self::location_exists( $location_id ) ) {
			return false;
		}

		// verify meta_keys
		if ( empty( $metadata ) ) {
			return false;
		}

		$metadata_ids = false;

		// loop through all meta_key => meta_values sets
		if ( is_array( $metadata ) ) {
			
			$metadata_ids = array();

			foreach( $metadata as $meta_key => $meta_value ) {

				$meta_id = self::update_location_meta( $location_id, $meta_key , $meta_value );

				if ( ! empty( $meta_id ) ) {
					$metadata_ids[] = $meta_id;
				}
			}

		// can be also used to update a single meta data
		// in case that a single key value pair passed
		} elseif ( ! empty( $meta_value ) ) {

			$metadata_ids = self::update_location_meta( $location_id, $metadata , $meta_value );
		}

		return ! empty( $metadata_ids ) ? $metadata_ids : false;
	}

	/**
	 * Get location meta by location ID.
	 * 
	 * @param  integer 			$location_id location ID
	 * @param  string || array  $meta_keys   single meta key as a string or array of keys
	 * @param  boolean $cache   
	 * 
	 * @return string || array
	 *
	 * DONE 
	 *
	 * TODO: cache
	 */
	public static function get_location_meta( $location_id = 0, $meta_keys = '', $cache = true ) {

		if ( ! self::verify_id( $location_id ) ) {

			//trigger_error( 'Trying to get meta using invalid location ID.', E_USER_NOTICE );

			return false;
		}

		// get location metas from either cache if exists or from database
        $location_metas = self::check_location_meta_cache( $location_id, false );

        // return all location metas if no meta keys passed to the function.
		if ( empty( $meta_keys ) ) {
			return $location_metas;
		}

		// if a single meta key passed as string.
		if ( is_string( $meta_keys ) ) {

			$meta_key = sanitize_key( $meta_keys );

			return ! empty( $location_metas[$meta_key] ) ? $location_metas[$meta_key] : false;
		}

		// if multiple meta keys passed as an array
		if ( is_array( $meta_keys ) ) {

			$output = array();

			foreach ( $meta_keys as $meta_key ) {

				$meta_key = sanitize_key( $meta_key );

				if ( isset( $location_metas[$meta_key] ) ) {
				
					$output[$meta_key] = $location_metas[$meta_key];		
				}
			}

			return $output;
		}

		return false;

        //$meta_hash = 'gmw'.GMW_Cache_Helper::get_transient_version( 'gmw_location_'.$location_id.'_meta' );

        //$output = $cache ? get_transient( $meta_hash ) : false;
		/*
		// if array of specific meta keys passes get their meta values
		if ( ! empty( $meta_keys ) && is_array( $meta_keys ) ) {

			$hash = md5( json_encode( $meta_keys ) );

			//echo GMW_Cache_Helper::get_transient_version( 'gmw_get_location_'.$location_id.'_metas' );

        	$locationmeta_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_location'.$location_id.'_metas' );

        	$output = $cache ? get_transient( $locationmeta_hash ) : false;

        	if ( false === $output ) {

				// sanitaize meta keys
				$meta_keys = array_map( 'sanitize_key', $meta_keys );	
				$values    = $meta_keys;	
				
				array_unshift( $values, $location_id );

				//look for meta values in database
				$results = $wpdb->get_results( $wpdb->prepare( "
					SELECT meta_key, meta_value
					FROM $table
					WHERE location_id = %d
					AND meta_key IN (".str_repeat( "%s,", count( $meta_keys ) - 1 ) . "%s )
				", $values ) );
		
				$output = array();
				
				// generate array of $key => unserialized value
				foreach ( $results as $key => $value ) {
					$output[$value->meta_key] = maybe_unserialize( $value->meta_value );
				}

				// update transient       
            	set_transient( $locationmeta_hash, $output, DAY_IN_SECONDS * 30 );
			}

			return $output;

		// otherwise, if a single meta_key passed
		} elseif ( ! empty( $meta_keys ) && is_string( $meta_keys ) ) {

			$meta_value = $cache ? wp_cache_get( $location_id . '_' . $meta_keys, 'gmw_location_meta' ) : false;

			// if no location found in cache get it from database
			if ( false === $meta_value ) {

				//sanitize meta key
				$meta_keys = sanitize_key( $meta_keys );

				//look for meta values in database
				$meta_value = $wpdb->get_var( 
					$wpdb->prepare( "
						SELECT 	meta_value
						FROM 	$table
						WHERE 	location_id = %d
						AND 	meta_key = %s
					", $location_id, $meta_keys ) 
				);

				wp_cache_set( $location_id . '_' . $meta_keys, $meta_value, 'gmw_location_meta' );
			}

			return $meta_value ? maybe_unserialize( $meta_value ) : '';

		//otehrwise get all meta values of the specific location ID
		} else {

			$output = $cache ? wp_cache_get( $location_id, 'gmw_all_location_meta' ) : false;

			// if no location found in cache get it from database
			if ( false === $output ) {

				$results = $wpdb->get_results( 
					$wpdb->prepare( "
						SELECT 	meta_key, meta_value
			            FROM    $table
			            WHERE	location_id = %d
					", $location_id ) 
				);

				$output = array();
				
				foreach ( $results as $key => $value ) {
					$output[$value->meta_key] = maybe_unserialize( $value->meta_value );
				}

				wp_cache_set( $location_id, $output, 'gmw_all_location_meta' );
			}

			if ( ! empty( $meta_keys ) && is_array( $meta_keys ) ) {

				$new_output = array();

				foreach ( $meta_keys as $meta_key ) {
	
					if ( isset( $output[$meta_key] ) ) {
					
						$new_output[$meta_key] = $output[$meta_key];		
					}
				}

				$output = $new_output;
			}

			return $output;
		} */
	}

	/**
	 * Get or update location meta in internal cache
	 *
	 * @param  integer $location_id  location ID
	 * @param  boolean $force_update true || false if to force update the meta in cache
	 * 
	 * @return array contains all location meta associate with the location ID
	 * 
	 */
	/*
	public static function check_location_meta_cache( $location_id = 0, $force_update = false ) {

		if ( empty( $location_id ) ) {
			return false;
		}

		// if cache disabled force update data
		if ( ! GMW()->internal_cache ) {
			$force_update = true;
		}
			
		$cache_key = 'gmw_location_'.$location_id.'_meta';
	    $output    = ! $force_update ? get_transient( $cache_key ) : false;

	    // if no value found generate it again
        if ( false === $output ) {

	    	global $wpdb;

	    	$table = self::get_locationmeta_table();

			$results = $wpdb->get_results( 
				$wpdb->prepare( "
					SELECT 	meta_key, meta_value
		            FROM    $table
		            WHERE	location_id = %d
				", $location_id ) 
			);

			$output = array();
		
			foreach ( $results as $key => $value ) {
				$output[$value->meta_key] = maybe_unserialize( $value->meta_value );
			}

			// save in transien only if cache enabled
			if ( GMW()->internal_cache ) {
				set_transient( $cache_key, $output, GMW()->internal_cache_expiration );	
			}		
		}

		return $output;
	}
	*/

	/**
	 * Get or update location meta in object cache
	 *
	 * @param  integer $location_id  location ID
	 * @param  boolean $force_update true || false if to force update the meta in cache
	 * 
	 * @return array contains all location meta associate with the location ID
	 * 
	 */
	public static function check_location_meta_cache( $location_id = 0, $force_update = false ) {

		if ( empty( $location_id ) ) {
			return false;
		}
			
		//$cache_key = 'gmw_location_'.$location_id.'_meta';
	    $output = ! $force_update ? wp_cache_get( $location_id, 'gmw_location_metas' ) : false;

	    // if no value found generate it again
        if ( false === $output ) {

	    	global $wpdb;

	    	$table = self::get_locationmeta_table();

			$results = $wpdb->get_results( 
				$wpdb->prepare( "
					SELECT 	meta_key, meta_value
		            FROM    $table
		            WHERE	location_id = %d
				", $location_id ) 
			);

			$output = array();
		
			foreach ( $results as $key => $value ) {
				$output[$value->meta_key] = maybe_unserialize( $value->meta_value );
			}		

			 wp_cache_set( $location_id, $output, 'gmw_location_metas' );
		}

		return $output;
	}

	/**
	 * Get location meta by object type and object ID
	 *
	 * function will get the meta value of the parent location based on the object type/id pair
	 *
	 * @since 3.0
	 * 
	 * @param  string  $object_type 
	 * @param  integer $object_id   
	 * @param  string  $meta_key    
	 * 
	 * @return array || false
	 *
	 * DONE
	 * 
	 */
	public static function get_location_meta_by_object( $object_type = '', $object_id = 0, $meta_key = '' ) {

		// get the location data based on the object type and object ID
		$location = self::get_location( $object_type, $object_id );

		// verify location
		if ( empty( $location ) ) {
			return false;
		}

		return self::get_location_meta( $location->ID, $meta_key );		
	}

	/**
	 * Delete location meta.
	 * 
	 * Deletes one or more location metas from database.
	 *
	 * @since 3.0
	 *
	 * @author Eyal Fitoussi <fitoussi_eyal@hotmail.com>
	 *
	 * @param   int     $location_id the location ID
	 * @param   array   $meta_keys   array in key-value pairs
	 *
	 * @return boolean deleted true || false
	 *
	 *  DONE
	 * 
	 */
	public static function delete_location_meta( $location_id = 0, $meta_key = '' ) {

		// verify location ID
		if ( ! self::verify_id( $location_id ) ) {

			trigger_error( 'Trying to delete location meta using invalid location ID.', E_USER_NOTICE );

			return false;
		}

		// verify meta key
		if ( ! is_string( $meta_key ) ) {

			trigger_error( 'Trying to delete a location meta using invalid or missing meta key.', E_USER_NOTICE );

			return false;
		}

		// senitaize key
		$meta_key = sanitize_key( $meta_key );

		global $wpdb;
		
		$table = self::get_locationmeta_table();

		// check if meta key exists before deleting it
		$saved_meta = $wpdb->get_row( 
			$wpdb->prepare( "
				SELECT *
				FROM   $table
				WHERE  location_id = %d
				AND    meta_key    = %s", 
				$location_id, 
				$meta_key 
			) 
		);

		if ( empty( $saved_meta ) ) {
			return false;
		}

		$object_type = self::get_location_object_type( $location_id );

		// do something before deleting the location meta
		do_action( "gmw_pre_delete_location_meta", $object_type, $location_id, $meta_key, $saved_meta->meta_value );
		do_action( "gmw_pre_delete_{$object_type}_location_meta", $location_id, $meta_key, $saved_meta->meta_value );

		// delete from DB
		$deleted = $wpdb->delete( 
			$table, 
			array( 
				'location_id' => $location_id, 
				'meta_key'    => $meta_key 
			), 
			array( 
				'%d', 
				'%s' 
		) );

		// do something after deleting the loation
		do_action( "gmw_deleted_location_meta", $object_type, $location_id, $meta_key, $saved_meta->meta_value );
		do_action( "gmw_deleted_{$object_type}location_meta", $location_id, $meta_key, $saved_meta->meta_value );

		//wp_cache_delete( $location_id . '_' . $meta_key, 'gmw_location_meta' );
		//wp_cache_delete( $location_id, 'gmw_all_location_meta' );

		self::check_location_meta_cache( $location_id, true );

		return ! empty( $deleted ) ? true : false; 
	}

	/**
	 * Delete location meta by object type and object ID
	 *
	 * @since 3.0
	 * 
	 * @param  string  $object_type 
	 * @param  integer $object_id   
	 * @param  string  $meta_key    
	 * 
	 * @return TRUE || FALSE if meta deleted or not
	 *
	 * DONE
	 * 
	 */
	public static function delete_location_meta_by_object( $object_type = '', $object_id = 0, $meta_key = '' ) {

		// get the location data based on the object type and object ID
		$location = self::get_location( $object_type, $object_id );

		// verify location
		if ( empty( $location ) ) {
			return false;
		}

		return self::delete_location_meta( $location->ID, $meta_key );		
	}

	/**
	 * Delete all location metas associated with a location
	 *
	 * @param  integer $location_id the location ID
	 *
	 * @since 3.0 
	 *
	 * @author Eyal Fitoussi <fitoussi@geomywp.com>
	 * 
	 * @return boolean meta delete true || false
	 *
	 * DONE
	 */
	public static function delete_all_location_meta( $location_id = 0  ) {

		// verify location ID
		if ( ! self::verify_id( $location_id ) ) {

			trigger_error( 'Trying to delete location metas using invalid location ID.', E_USER_NOTICE );

			return false;
		}

		global $wpdb;

		$table 		 = self::get_locationmeta_table();
		$object_type = self::get_location_object_type( $location_id );

		// do something before deleting the location meta
		do_action( "gmw_pre_delete_all_location_meta", $object_type, $location_id );
		do_action( "gmw_pre_delete_all_{$object_type}_location_meta", $location_id );
		
		// delete all meta associate with the location
		$wpdb->delete( 
			$table, 
			array( 'location_id' => $location_id ), 
			array( '%d' ) 
		);

		// do something before deleting the location meta
		do_action( "gmw_all_location_meta_deleted", $object_type, $location_id );
		do_action( "gmw_all_{$object_type}_location_meta_deleted", $location_id );

		// remove from cache
		delete_transient( 'gmw_location_'.$location_id.'_meta' );

		return true;
	}
}
endif;