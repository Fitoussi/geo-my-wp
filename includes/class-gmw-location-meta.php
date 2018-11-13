<?php
/**
 * GEO my WP Location Meta class.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class GMW_Location_Meta
 *
 * This class responsible for location meta process. Craete, update, delete....
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 */
class GMW_Location_Meta {

	/**
	 * Location meta table name
	 *
	 * @var string
	 */
	public static $table_name = 'gmw_locationmeta';

	/**
	 * Get locationmeta table
	 *
	 * @return [type] [description]
	 *
	 * @since 3.0
	 */
	public static function get_table() {

		global $wpdb;

		$table = $wpdb->base_prefix . self::$table_name;

		return $table;
	}

	/**
	 * Helper to verify an ID.
	 *
	 * @param integer $id any ID to verify.
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
	 *
	 * Verify if location meta exists based on its meta_id
	 *
	 * @param  absint $meta_id meta ID.
	 *
	 * @return boolean true || false
	 *
	 * @since 3.0
	 */
	public static function exists( $meta_id = 0 ) {

		if ( ! self::verify_id( $meta_id ) ) {
			return;
		}

		global $wpdb;

		$table   = self::get_table();
		$meta_id = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT meta_id 
				FROM   $table 
				WHERE  meta_id = %d",
				$meta_id
			)
		); // WPCS: unprepared SQL ok, db call ok, cache ok.

		return ! empty( $meta_id ) ? true : false;
	}

	/**
	 * Get location meta ID
	 *
	 * Get a location meta ID by passing the location ID and meta_key
	 *
	 * @param  absint $location_id [description].
	 * @param  string $meta_key    [description].
	 *
	 * @return meta ID if found or false otherwise.
	 *
	 * @since 3.0
	 */
	public static function get_meta_id( $location_id = 0, $meta_key = '' ) {

		// verify location ID.
		if ( ! self::verify_id( $location_id ) ) {
			return false;
		}

		// verify meta key.
		if ( ! is_string( $meta_key ) ) {
			return false;
		}

		// sanitize meta_key.
		$meta_key = sanitize_key( $meta_key );

		global $wpdb;

		$table = self::get_table();

		// get the meta ID from database if exists.
		$meta_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT meta_id
				FROM   $table
				WHERE  location_id = %d
				AND    meta_key    = %s
			",
				$location_id,
				$meta_key
			)
		); // WPCS: unprepared SQL ok, db call ok, cache ok.

		return ! empty( $meta_id ) ? $meta_id : false;
	}

	/**
	 * Uupdate
	 *
	 * Update existing or create a new location meta
	 *
	 * @param  integer $location_id Location ID.
	 * @param  string  $meta_key    meta key.
	 * @param  string  $meta_value  meta value.
	 *
	 * @return [type]
	 *
	 * @since 3.0
	 */
	public static function update( $location_id = 0, $meta_key = '', $meta_value = '' ) {

		// verify if location exists.
		if ( ! GMW_Location::exists( $location_id ) ) {
			return false;
		}

		// verify meta key.
		if ( ! is_string( $meta_key ) || empty( $meta_key ) ) {

			gmw_trigger_error( 'Trying to update a location meta using invalid or missing meta key.' );

			return false;
		}

		global $wpdb;

		// sanitize meta_key.
		$meta_key   = sanitize_key( $meta_key );
		$meta_value = maybe_serialize( $meta_value );
		$table      = self::get_table();

		// check if meta already exists and get its ID from database.
		$save_meta = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT 	meta_id, meta_value
				FROM 	$table
				WHERE 	location_id = %d
				AND 	meta_key    = %s
			",
				$location_id,
				$meta_key
			)
		); // WPCS: unprepared SQL ok, db call ok, cache ok.

		// abort if meta already exists and the value is the same.
		if ( ! empty( $save_meta->meta_id ) && ! empty( $save_meta->meta_value ) && $save_meta->meta_value === $meta_value ) {
			return;
		}

		$meta_id = ! empty( $save_meta->meta_id ) ? (int) $save_meta->meta_id : 0;

		// the new meta data.
		$metadata = array(
			'meta_id'     => $meta_id,
			'location_id' => $location_id,
			'meta_key'    => $meta_key, // WPCS: slow query ok.
			'meta_value'  => $meta_value, // WPCS: slow query ok.
		);

		$object_type = GMW_Location::get_object_type( $location_id );

		// modify the new location meta args before saving.
		$metadata = apply_filters( 'gmw_pre_save_location_meta', $metadata );
		$metadata = apply_filters( "gmw_pre_save_{$object_type}_location_meta", $metadata );

		// if not yet exists, add new location meta.
		if ( empty( $meta_id ) ) {

			// insert new location to database.
			$wpdb->insert(
				$table,
				$metadata,
				array(
					'%d',
					'%d',
					'%s',
					'%s',
				)
			); // WPCS: db call ok, cache ok.

			// get the new location ID.
			$meta_id = $wpdb->insert_id;

			$created = true;

			// otherwise, update existing location.
		} else {

			// update location.
			$wpdb->update(
				$table,
				$metadata,
				array( 'meta_id' => $meta_id ),
				array(
					'%d',
					'%d',
					'%s',
					'%s',
				),
				array( '%d' )
			); // WPCS: db call ok, cache ok.

			$created = false;
		}

		// do something after location meta updated.
		do_action( 'gmw_save_location_meta', $object_type, $meta_id, $location_id, $meta_key, $meta_value, $created );
		do_action( "gmw_save_{$object_type}_location_meta", $meta_id, $location_id, $meta_key, $meta_value, $created );

		self::check_cache( $location_id, true );

		return $meta_id;
	}

	/**
	 * Create / Update multiple location metas
	 *
	 * @since   3.0
	 *
	 * @param   int   $location_id the ID of the corresponding location.
	 * @param   array $metadata    location metadata in meta_key => meta value pairs.
	 * @param   mixed $meta_value  can also update single meta by passing a single meta key as a string
	 *
	 * to the metadata argument.
	 *
	 * @return  array   array of updated/created metadata IDs
	 *
	 * @since 3.0
	 */
	public static function update_metas( $location_id = 0, $metadata = array(), $meta_value = false ) {

		// verify if location exists.
		if ( ! GMW_Location::exists( $location_id ) ) {
			return false;
		}

		// verify meta_keys.
		if ( empty( $metadata ) ) {
			return false;
		}

		$metadata_ids = false;

		// loop through all meta_key => meta_values sets.
		if ( is_array( $metadata ) ) {

			$metadata_ids = array();

			foreach ( $metadata as $meta_key => $meta_value ) {

				$meta_id = self::update( $location_id, $meta_key, $meta_value );

				if ( ! empty( $meta_id ) ) {
					$metadata_ids[] = $meta_id;
				}
			}

			// can be also used to update a single meta data
			// in case that a single key value pair passed.
		} elseif ( ! empty( $meta_value ) ) {

			$metadata_ids = self::update( $location_id, $metadata, $meta_value );
		}

		return ! empty( $metadata_ids ) ? $metadata_ids : false;
	}

	/**
	 * Get location meta by location ID.
	 *
	 * @param  integer         $location_id location ID.
	 *
	 * @param  string || array $meta_keys   single meta key as a string or multiple keys
	 *
	 * as array or comma separated string.
	 *
	 * @param  boolean         $cache use cached value?.
	 *
	 * @return string || array
	 *
	 * @since 3.0
	 */
	public static function get( $location_id = 0, $meta_keys = '', $cache = true ) {

		if ( ! self::verify_id( $location_id ) ) {

			return false;
		}

		// get location metas from either cache if exists or from database.
		$location_metas = self::check_cache( $location_id, false );

		// return all location metas if no meta keys passed to the function.
		if ( empty( $meta_keys ) ) {
			return $location_metas;
		}

		// if a string passed as key/s.
		if ( is_string( $meta_keys ) ) {

			// if a single meta key passed as string ( which means without commas ).
			if ( strpos( $meta_keys, ',' ) === false ) {

				$meta_key = sanitize_key( $meta_keys );

				return ! empty( $location_metas[ $meta_key ] ) ? $location_metas[ $meta_key ] : false;
			}

			// otherwise, if commas provided in the string,
			// that means that we have multiple meta keys
			// So we convert the string into array.
			$meta_keys = explode( ',', $meta_keys );
		}

		// if multiple meta keys passed as an array.
		if ( is_array( $meta_keys ) ) {

			$output = array();

			foreach ( $meta_keys as $meta_key ) {

				$meta_key = sanitize_key( $meta_key );

				if ( isset( $location_metas[ $meta_key ] ) ) {

					$output[ $meta_key ] = $location_metas[ $meta_key ];
				}
			}

			return $output;
		}

		return false;
	}

	/**
	 * Get or update location meta in object cache
	 *
	 * @param  integer $location_id  location ID.
	 * @param  boolean $force_update true || false if to force update the meta in cache.
	 *
	 * @return array contains all location meta associate with the location ID
	 *
	 * @since 3.0
	 */
	public static function check_cache( $location_id = 0, $force_update = false ) {

		if ( empty( $location_id ) ) {
			return false;
		}

		/** $cache_key = 'gmw_location_'.$location_id.'_meta'; */
		$output = ! $force_update ? wp_cache_get( $location_id, 'gmw_location_metas' ) : false;

		// if no value found generate it again.
		if ( false === $output ) {

			global $wpdb;

			$table = self::get_table();

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT 	meta_key, meta_value
		            FROM    $table
		            WHERE	location_id = %d
				",
					$location_id
				)
			); // WPCS: unprepared SQL ok, db call ok, cache ok.

			$output = array();

			foreach ( $results as $key => $value ) {
				$output[ $value->meta_key ] = maybe_unserialize( $value->meta_value ); // WPCS: slow query ok.
			}

			wp_cache_set( $location_id, $output, 'gmw_location_metas' );
		}

		return $output;
	}

	/**
	 * Get location meta by object type and object ID.
	 *
	 * Function will get the meta value of the parent location based on the object type/id pair.
	 *
	 * @param  string  $object_type object type.
	 * @param  integer $object_id object ID.
	 * @param  string  $meta_key meta key.
	 *
	 * @return array || false
	 *
	 * @since 3.0
	 */
	public static function get_by_object( $object_type = '', $object_id = 0, $meta_key = '' ) {

		// get the location data based on the object type and object ID.
		$location = GMW_Location::get_by_object( $object_type, $object_id );

		// verify location.
		if ( empty( $location ) ) {
			return false;
		}

		return self::get( $location->ID, $meta_key );
	}

	/**
	 * Delete location meta.
	 *
	 * Deletes single or multiple location metas from database.
	 *
	 * @since 3.0
	 *
	 * @author Eyal Fitoussi <fitoussi_eyal@hotmail.com>
	 *
	 * @param   int   $location_id the location ID.
	 * @param   array $meta_key    array in key-value pairs.
	 *
	 * @return boolean deleted true || false
	 */
	public static function delete( $location_id = 0, $meta_key = '' ) {

		// verify location ID.
		if ( ! self::verify_id( $location_id ) ) {

			gmw_trigger_error( 'Trying to delete location meta using invalid location ID.' );

			return false;
		}

		// verify meta key.
		if ( ! is_string( $meta_key ) ) {

			gmw_trigger_error( 'Trying to delete a location meta using invalid or missing meta key.' );

			return false;
		}

		// senitaize key.
		$meta_key = sanitize_key( $meta_key );

		global $wpdb;

		$table = self::get_table();

		// check if meta key exists before deleting it.
		$saved_meta = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT *
				FROM   $table
				WHERE  location_id = %d
				AND    meta_key    = %s",
				$location_id,
				$meta_key
			)
		); // WPCS: unprepared SQL ok, db call ok, cache ok.

		if ( empty( $saved_meta ) ) {
			return false;
		}

		$object_type = GMW_Location::get_object_type( $location_id );

		// do something before deleting the location meta.
		do_action( 'gmw_pre_delete_location_meta', $object_type, $location_id, $meta_key, $saved_meta->meta_value );
		do_action( "gmw_pre_delete_{$object_type}_location_meta", $location_id, $meta_key, $saved_meta->meta_value );

		// delete from DB.
		$deleted = $wpdb->delete(
			$table,
			array(
				'location_id' => $location_id,
				'meta_key'    => $meta_key, // WPCS: slow query ok.
			),
			array(
				'%d',
				'%s',
			)
		); // WPCS: unprepared SQL ok, db call ok, cache ok.

		// do something after deleting the loation.
		do_action( 'gmw_deleted_location_meta', $object_type, $location_id, $meta_key, $saved_meta->meta_value );
		do_action( "gmw_deleted_{$object_type}location_meta", $location_id, $meta_key, $saved_meta->meta_value );

		/** Wp_cache_delete( $location_id . '_' . $meta_key, 'gmw_location_meta' ); */
		/** Wp_cache_delete( $location_id, 'gmw_all_location_meta' );. */
		self::check_cache( $location_id, true );

		return ! empty( $deleted ) ? true : false;
	}

	/**
	 * Delete location meta by object type and object ID
	 *
	 * @since 3.0
	 *
	 * @param  string  $object_type object type.
	 * @param  integer $object_id   object ID.
	 * @param  string  $meta_key    meta key.
	 *
	 * @return TRUE || FALSE if meta deleted or not
	 */
	public static function delete_by_object( $object_type = '', $object_id = 0, $meta_key = '' ) {

		// get the location data based on the object type and object ID.
		$location = GMW_Location::get_by_object( $object_type, $object_id );

		// verify location.
		if ( empty( $location ) ) {
			return false;
		}

		return self::delete( $location->ID, $meta_key );
	}

	/**
	 * Delete all location metas associated with a location
	 *
	 * @param integer $location_id the location ID.
	 *
	 * @since 3.0
	 *
	 * @author Eyal Fitoussi <fitoussi@geomywp.com>
	 *
	 * @return boolean meta delete true || false
	 */
	public static function delete_all( $location_id = 0 ) {

		// verify location ID.
		if ( ! self::verify_id( $location_id ) ) {

			gmw_trigger_error( 'Trying to delete location metas using invalid location ID.' );

			return false;
		}

		global $wpdb;

		$table       = self::get_table();
		$object_type = GMW_Location::get_object_type( $location_id );

		// do something before deleting the location meta.
		do_action( 'gmw_pre_delete_all_location_meta', $object_type, $location_id );
		do_action( "gmw_pre_delete_all_{$object_type}_location_meta", $location_id );

		// delete all meta associate with the location.
		$wpdb->delete(
			$table,
			array( 'location_id' => $location_id ),
			array( '%d' )
		); // WPCS: unprepared SQL ok, db call ok, cache ok.

		// do something before deleting the location meta.
		do_action( 'gmw_all_location_meta_deleted', $object_type, $location_id );
		do_action( "gmw_all_{$object_type}_location_meta_deleted", $location_id );

		// remove from cache.
		delete_transient( 'gmw_location_' . $location_id . '_meta' );

		return true;
	}
}
