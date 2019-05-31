<?php
/**
 * GEO my WP internal cache system.
 *
 * @author Eyal Fitoussi. Inspired by the work done by Mike Jolley on WP Job Manager plugin.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_Cache_Helper class.
 *
 * This AWESOME class and idea was "stolen" from WP JOb Manager plugin developed by Mike Jolley. Thank you!
 */
class GMW_Cache_Helper {

	/**
	 * Init.
	 */
	public static function init() {

		add_action( 'gmw_save_location', array( __CLASS__, 'flush_locations_cache' ), 99, 2 );
		add_action( 'gmw_location_deleted', array( __CLASS__, 'flush_locations_cache' ), 99, 2 );
		add_action( 'gmw_featured_location_updated', array( __CLASS__, 'flush_cache_by_object' ), 99 );
		add_action( 'save_post', array( __CLASS__, 'flush_post_query_cache' ), 99, 2 );
		add_action( 'set_object_terms', array( __CLASS__, 'set_term' ), 10, 4 );
		add_action( 'edited_term', array( __CLASS__, 'edited_term' ), 10, 3 );
		add_action( 'create_term', array( __CLASS__, 'edited_term' ), 10, 3 );
		add_action( 'delete_term', array( __CLASS__, 'edited_term' ), 10, 3 );
		add_action( 'gmw_clear_expired_transients', array( __CLASS__, 'clear_expired_transients' ), 10 );
	}

	/**
	 * Flush all objects.
	 *
	 * @since 3.0.3
	 */
	public static function flush_all() {
		self::get_transient_version( 'gmw_get_object_post_locations', true );
		self::get_transient_version( 'gmw_get_object_post_query', true );
		self::get_transient_version( 'gmw_get_object_user_locations', true );
		self::get_transient_version( 'gmw_get_object_user_query', true );
		self::get_transient_version( 'gmw_get_object_bp_group_locations', true );
		self::get_transient_version( 'gmw_get_object_bp_group_query', true );
	}

	/**
	 * Flush locations and query cache when saving or deleting a location
	 *
	 * @param  int    $location_id   location ID.
	 *
	 * @param  object $location_data location object.
	 */
	public static function flush_locations_cache( $location_id, $location_data ) {
		self::get_transient_version( 'gmw_get_object_' . $location_data->object_type . '_locations', true );
		self::get_transient_version( 'gmw_get_object_' . $location_data->object_type . '_query', true );
	}

	/**
	 * Flush locations and query cache by object id
	 *
	 * @param  string $object_type object type.
	 */
	public static function flush_cache_by_object( $object_type ) {
		self::get_transient_version( 'gmw_get_object_' . $object_type . '_locations', true );
		self::get_transient_version( 'gmw_get_object_' . $object_type . '_query', true );
	}

	/**
	 * Flush post query cache when updating a post
	 *
	 * @param  int    $post_id post ID.
	 *
	 * @param  object $post    post object.
	 */
	public static function flush_post_query_cache( $post_id, $post ) {
		self::get_transient_version( 'gmw_get_object_post_query', true );
	}

	/**
	 * When any post has a term set
	 *
	 * @param int    $object_id object ID.
	 *
	 * @param array  $terms tax terms.
	 *
	 * @param int    $tt_ids term taxonomy ID.
	 *
	 * @param string $taxonomy tax name.
	 */
	public static function set_term( $object_id = '', $terms = '', $tt_ids = '', $taxonomy = '' ) {
		self::get_transient_version( 'gmw_get_' . sanitize_text_field( $taxonomy ) . '_terms', true );
		self::get_transient_version( 'gmw_get_the_' . sanitize_text_field( $taxonomy ) . '_terms', true );
	}

	/**
	 * When any term is edited
	 *
	 * @param int    $term_id term ID.
	 *
	 * @param int    $tt_id term taxonomy ID.
	 *
	 * @param string $taxonomy tax name.
	 */
	public static function edited_term( $term_id = '', $tt_id = '', $taxonomy = '' ) {
		self::get_transient_version( 'gmw_get_' . sanitize_text_field( $taxonomy ) . '_terms', true );
		self::get_transient_version( 'gmw_get_the_' . sanitize_text_field( $taxonomy ) . '_terms', true );
	}

	/**
	 * Get transient version
	 *
	 * When using transients with unpredictable names, e.g. those containing an md5
	 * hash in the name, we need a way to invalidate them all at once.
	 *
	 * When using default WP transients we're able to do this with a DB query to
	 * delete transients manually.
	 *
	 * With external cache however, this isn't possible. Instead, this function is used
	 * to append a unique string (based on a random number ) to each transient. When transients
	 * are invalidated, the transient version will increment and data will be regenerated.
	 *
	 * @param  string  $group   Name for the group of transients we need to invalidate.
	 *
	 * @param  boolean $refresh true to force a new version.
	 *
	 * @return string transient version based on time(), 10 digits
	 */
	public static function get_transient_version( $group, $refresh = false ) {

		$transient_name  = $group . '_transient_version';
		$transient_value = get_transient( $transient_name );

		if ( false === $transient_value || true === $refresh ) {

			self::delete_version_transients( $transient_value );

			// set_transient( $transient_name, $transient_value = time() );
			// 2147483647 largest value can be used as random on some OS.
			$rnd = wp_rand( 0, 2147483647 );

			set_transient( $transient_name, $transient_value = $rnd );
		}

		return $transient_value;
	}

	/**
	 * When the transient version increases, this is used to remove all past transients to avoid filling the DB.
	 *
	 * Note: this only works on transients appended with the transient version,
	 *
	 * and when object caching is not being used.
	 *
	 * @param string $version transient version.
	 */
	private static function delete_version_transients( $version ) {

		if ( ! wp_using_ext_object_cache() && ! empty( $version ) ) {

			global $wpdb;

			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM {$wpdb->options} 
					WHERE option_name LIKE %s;",
					'\_transient\_%' . $version
				)
			); // WPCS: db call ok, cache ok.
		}
	}

	/**
	 * Clear expired transients
	 */
	public static function clear_expired_transients() {
		global $wpdb;

		if ( ! wp_using_ext_object_cache() && ! defined( 'WP_SETUP_CONFIG' ) && ! defined( 'WP_INSTALLING' ) ) {

			$sql = "
				DELETE a, b FROM $wpdb->options a, $wpdb->options b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
				AND b.option_value < %d";

			$rows = $wpdb->query(
				$wpdb->prepare(
					$sql,
					$wpdb->esc_like( '_transient_gmw' ) . '%',
					$wpdb->esc_like( '_transient_timeout_gmw' ) . '%',
					time()
				)
			); // WPCS: db call ok, cache ok, unprepared sql ok.
		}
	}
}

GMW_Cache_Helper::init();
