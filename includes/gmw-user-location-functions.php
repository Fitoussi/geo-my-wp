<?php
/**
 * GMW User Location functions.
 *
 * @package gmw-my-wp.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Try to get the user ID
 *
 * We first checking if BuddyPress is activated and if so we will try to get
 *
 * the user ID using $bp global or within the loop.
 *
 * Otherwise, we will check for the logged in user ID
 *
 * @return [type] [description]
 */
function gmw_try_get_user_id() {

	$user_id = 0;

	// if BuddyPress activated we look for member ID.
	if ( class_exists( 'BuddyPress' ) ) {

		global $members_template;

		// look for member ID in the loop.
		if ( ! empty( $members_template->member->id ) ) {

			$user_id = $members_template->member->id;

			// look for displayed user ID.
		} elseif ( ! empty( buddypress()->displayed_user->id ) ) {

			$user_id = buddypress()->displayed_user->id;

		} elseif ( ! empty( buddypress()->loggedin_user->id ) ) {

			$user_id = buddypress()->loggedin_user->id;
		}
	}

	// if not found via BuddyPress look for loggedin user ID.
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	return $user_id;
}

/**
 * Check if user exists.
 *
 * This is in case that the user was deleted but the location
 *
 * still exists in database.
 *
 * @param  integer $user_id [description].
 *
 * @return [type]           [description]
 */
function gmw_is_user_exists( $user_id = 0 ) {

	if ( empty( $user_id ) ) {
		return false;
	}

	global $wpdb;

	// look for user in database.
	$user_id = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT ID 
            FROM $wpdb->users 
            WHERE ID = %d",
			$user_id
		)
	);

	// abort if user not exists.
	if ( empty( $user_id ) ) {

		return false;

	} else {

		return true;
	}
}

/**
 * Get the location of a user.
 *
 * @since 3.0
 *
 * @param  integer $id  user ID to retrieve the default location of a specific user. Or location ID
 *
 * to retrieve a specific location.
 *
 * @param  boolean $by_location_id  when set to true the first argument has to be a location ID.
 *
 * @return object  complete location object
 */
function gmw_get_user_location( $id = 0, $by_location_id = false ) {

	if ( $by_location_id ) {
		return gmw_get_location( $id );
	}

	// if no specific user ID pass, look for logged in user object.
	if ( empty( $id ) ) {

		// try to get user ID.
		$id = gmw_try_get_user_id();

		// abort if no user ID.
		if ( empty( $id ) ) {
			return false;
		}
	}

	// get user location from database.
	return gmw_get_location_by_object( 'user', $id );
}

/**
 * Get all locations of a user.
 *
 * @since 3.2
 *
 * @param  integer $user_id User ID.
 *
 * @return object of locations.
 */
function gmw_get_user_locations( $user_id = 0 ) {

	// if no specific user ID pass, look for logged in user object.
	if ( empty( $user_id ) ) {

		// try to get user ID.
		$user_id = gmw_try_get_user_id();

		// abort if no user ID.
		if ( empty( $user_id ) ) {
			return false;
		}
	}

	// get user's locations from database.
	return GMW_Location::get_locations_by_object( 'user', $user_id );
}

/**
 * Get user's location meta from database.
 *
 * @since 3.0
 *
 * @param  integer $user_id the user ID.
 *
 * @param  mixed   $meta_keys string of a single or array of multiple meta keys to retrieve their values.
 *
 * @return [type]           [description]
 */
function gmw_get_user_location_meta( $user_id = 0, $meta_keys = array() ) {

	// if no specific user ID pass, look for logged in user object.
	if ( empty( $user_id ) ) {

		// try to get user ID.
		$user_id = gmw_try_get_user_id();

		// abort if no user ID.
		if ( empty( $user_id ) ) {
			return;
		}
	}

	// get user location from database.
	return gmw_get_location_meta_by_object( 'user', $user_id, $meta_keys );
}

/**
 * Get the user location data from database.
 *
 * This function returns locations data and user data such as user name, displya name, email...
 *
 * The function also verify that the user exists in database. That is in case
 *
 * That the user was deleted but the location still exists in database.
 *
 * @since 3.0
 *
 * @param init    $id             user or location ID.
 *
 * @param boolean $by_location_id true for passing location ID as first argument.
 *
 * @return object  user data + location
 *
 * TODO : Cache for locations data.
 *
 * When doing cache we need to make sure we delete cache data when user data
 *
 * is changed as well. Not only when location is modified.
 */
function gmw_get_user_location_data( $id = 0, $by_location_id = false ) {

	$fields = implode(
		',',
		apply_filters(
			'gmw_get_user_location_data_fields',
			array(
				'gmw.ID',
				'gmw.latitude',
				'gmw.longitude',
				'gmw.latitude as lat',
				'gmw.longitude as lng',
				'gmw.address',
				'gmw.formatted_address',
				'gmw.street_number',
				'gmw.street_name',
				'gmw.street',
				'gmw.city',
				'gmw.region_code',
				'gmw.region_name',
				'gmw.postcode',
				'gmw.country_code',
				'gmw.country_name',
				'featured',
				'title as location_name',
				'users.ID as user_id',
				'users.user_login',
				'users.user_nicename',
				'users.display_name',
				'users.user_email',
				'users.user_registered',
				'users.user_status',
			),
			$id,
			$by_location_id
		)
	);

	if ( empty( $id ) ) {

		// try to get user ID.
		$id = gmw_try_get_user_id();

		// abort if no user ID.
		if ( empty( $id ) ) {
			return;
		}

		$by_location_id = false;
	}

	global $wpdb;

	$gmw_table   = $wpdb->base_prefix . 'gmw_locations';
	$users_table = $wpdb->base_prefix . 'users';

	if ( ! $by_location_id ) {

		$sql = $wpdb->prepare(
			"
            SELECT     $fields
            FROM       $gmw_table  gmw
            INNER JOIN $users_table users
            ON         gmw.object_id   = users.ID
            WHERE      gmw.object_type = 'user'
            AND        gmw.object_id   = %d
        ",
			$id
		);

	} else {

		$sql = $wpdb->prepare(
			"
            SELECT     $fields
            FROM       $gmw_table  gmw
            INNER JOIN $users_table users
            ON         gmw.object_id = users.ID
            WHERE      gmw.object_type = 'user'
            AND        gmw.ID = %d
        ",
			$id
		);
	}

	$location_data = $wpdb->get_row( $sql, OBJECT );

	return ! empty( $location_data ) ? $location_data : false;
}

/**
 * Get user data and user locations data from database.
 *
 * The function returns all the locations of a specific user and the user object.
 *
 * @since 3.2
 *
 * @param integer $user_id user ID.
 *
 * @return array(
 *     'locations' => array of all the user's locations,
 *     'user'      => the user object
 * );
 */
function gmw_get_user_locations_data( $user_id = 0 ) {

	if ( empty( $user_id ) ) {

		// try to get user ID.
		$user_id = gmw_try_get_user_id();

		// abort if no user ID.
		if ( empty( $user_id ) ) {
			return;
		}
	}

	$user = get_userdata( $user_id );

	if ( empty( $user ) ) {
		return false;
	}

	$locations = GMW_Location::get_locations_by_object( 'user', $user_id );

	return empty( $locations ) ? false : array(
		'locations' => $locations,
		'user'      => $user,
	);
}

/**
 * Get specific user address fields
 *
 * @since 3.0
 *
 * @param  array $args array(
 *     'location_id' => 0,                   // when getting a specifc location by its ID.
 *     'user_id'     => 0,                   // When getting the default location of a user using the user ID.
 *     'fields'      => 'formatted_address', // address fields comma separated
 *     'separator'   => ', ',                // Separator between fields.
 *     'output'      => 'string'             // output type ( object, array or string ).
 * );
 *
 * @return Mixed object || array || string
 */
function gmw_get_user_address( $args = array() ) {

	// to support older versions. should be removed in the future
	/*
	if ( empty( $args['fields'] ) && ! empty( $args['info'] ) ) {

		trigger_error( 'The "info" shortcode attribute of the shortcode [gmw_member_address] is deprecated since GEO my WP version 3.0. Please use the shortcode attribute "fields" instead.', E_USER_NOTICE );

		$args['fields'] = $args['info'];
	}*/

	// if no specific user ID pass, look for logged in user ID.
	if ( empty( $args['location_id'] ) ) {
		$args['object_type'] = 'user';
		$args['object_id']   = ! empty( $args['user_id'] ) ? $args['user_id'] : gmw_try_get_user_id();
	}

	// get user address fields.
	return gmw_get_address_fields( $args );
}
add_shortcode( 'gmw_user_address', 'gmw_get_user_address' );

/**
 * Output the user address fields.
 *
 * @param array $args see arguments in the function gmw_get_user_address().
 */
function gmw_user_address( $args = array() ) {
	echo gmw_get_user_address( $args );
}

/**
 * Get location or location meta fields of a user.
 *
 * @since 3.0.2
 *
 * @param  array $args array(
 *     'location_id'   => 0,                   // when getting a specifc location by its ID.
 *     'user_id'       => 0,                   // When getting the default location of a user using the user ID.
 *     'fields'        => 'formatted_address', // location or meta fields, comma separated.
 *     'separator'     => ', ',                // Separator between fields.
 *     'location_meta' => 0                    // Set to 1 when the fields argument is location meta.
 *     'output'        => 'string'             // object || array || string
 * );
 *
 * @return Mixed object || array || string
 */
function gmw_get_user_location_fields( $args = array() ) {

	if ( empty( $args['location_id'] ) ) {
		$args['object_type'] = 'user';
		$args['object_id']   = ! empty( $args['user_id'] ) ? $args['user_id'] : gmw_try_get_user_id();
	}

	return gmw_get_location_fields( $args );
}
add_shortcode( 'gmw_user_location_fields', 'gmw_get_user_location_fields' );

/**
 * Update user location.
 *
 * The function will geocode the address, or reverse geocode coords, and save it in the locations table in DB
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 *
 * @param  integer         $user_id.
 * @param  string || array $location to pass an address it can be either a string or an array of address field for example:
 *
 * $location = array(
 *     'street'    => 285 Fulton St,
 *     'apt'       => '',
 *     'city'      => 'New York',
 *     'state'     => 'NY',
 *     'zipcode'   => '10007',
 *     'country'   => 'USA'
 * );
 *
 * or pass a set of coordinates via an array of lat,lng. Ex
 *
 * $location = array(
 *    'lat' => 26.1345,
 *    'lng' => -80.4362
 * );
 *
 * @param  string          $location_name name of the location ( optional ).
 *
 * @param  boolean         $force_refresh false to use geocoded address in cache || true to force address geocoding.
 *
 * @return int location ID
 */
function gmw_update_user_location( $user_id = 0, $location = array(), $location_name = '', $force_refresh = false ) {

	if ( ! gmw_is_user_exists( $user_id ) ) {
		return;
	}

	$args = array(
		'object_type'   => 'user',
		'object_id'     => $user_id,
		'location_name' => $location_name,
		'user_id'       => $user_id,
	);

	return gmw_update_location( $args, $location, $force_refresh );
}

/**
 * Update user location metas
 *
 * Can update/create single or multiple user location metas.
 *
 * For a single location meta pass the user ID, meta key and meta value
 *
 * For multiple metas pass the user ID and an array of meta_key => meta_value pairs
 *
 * @since 3.0
 *
 * @param  integer $user_id    user ID.
 * @param  array   $metadata   single meta key or array of meta_key => meta_value.
 * @param  boolean $meta_value single meta value, when passing single meta_key as $metadata.
 *
 * @return [type]              [description]
 */
function gmw_update_user_location_meta( $user_id = 0, $metadata = array(), $meta_value = false ) {

	// look for location ID
	$location_id = gmw_get_location_id( 'user', $user_id );

	// abort if location not exists
	if ( empty( $location_id ) ) {
		return false;
	}

	gmw_update_location_metas( $location_id, $metadata, $meta_value );
}

/**
 * Delete user location
 *
 * @since 3.0
 *
 * @param int     $user_id     user_id.
 *
 * @param boolean $delete_meta true to also delete location meta.
 *
 * @return [type]          [description]
 */
function gmw_delete_user_location( $user_id = 0, $delete_meta = true ) {

	if ( empty( $user_id ) ) {
		return;
	}

	do_action( 'gmw_before_user_location_deleted', $user_id );

	gmw_delete_location_by_object( 'user', $user_id, $delete_meta );

	do_action( 'gmw_after_user_location_deleted', $user_id );
}

/**
 * Delete user from GEO my WP database when user deleted from WordPress
 *
 * @since 3.0
 *
 * @param  int $user_id user ID.
 */
function gmw_delete_user_location_action( $user_id ) {
	gmw_delete_user_location( $user_id, true );
}
add_action( 'delete_user', 'gmw_delete_user_location_action' );

/**
 * Change user location status
 *
 * @since 3.0
 *
 * @param  integer $user_id user ID.
 * @param  integer $status  status 1 || 0.
 *
 * @return [type]           [description]
 */
function gmw_user_location_status( $user_id = 0, $status = 1 ) {

	$status = ( 1 == $status ) ? 1 : 0;

	global $wpdb;

	return $wpdb->query(
		$wpdb->prepare(
			"
            UPDATE {$wpdb->prefix}gmw_locations 
			SET   `status`      = $status 
            WHERE `object_type` = 'user' 
            AND   `object_id`   = %d",
			array( $user_id )
		)
	);
}
