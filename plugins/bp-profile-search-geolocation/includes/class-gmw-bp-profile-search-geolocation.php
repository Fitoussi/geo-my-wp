<?php
/**
 * GEO my WP BP Profile Search geolocation class
 *
 * @author Eyal Fitoussi
 *
 * @created 3/2/2019
 *
 * @since 3.3
 *
 * @package gmw-bp-profile-search-integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_BP_Profile_Search_Geolocation
 *
 * @since 3.3
 */
class GMW_BP_Profile_Search_Geolocation {

	public $is_buddyboss = false;

	/**
	 * __construct function.
	 */
	public function __construct() {

		$this->is_buddyboss = function_exists( 'bp_ps_meta' ) ? true : false;

		// Generate location field.
		add_filter( 'bps_add_fields', array( $this, 'add_location_field' ), 10 ); // Profile Search stand alone plugin.
		add_filter( 'bp_ps_add_fields', array( $this, 'add_location_field' ), 10 ); // For Profile Search in BuddyBoss.

		add_filter( 'bp_get_template_stack', array( $this, 'template_stack' ), 30 );

		// Add location filter on form submission.
		add_filter( 'bps_filters_template_field', array( $this, 'generate_location_field_filter' ), 50, 2 );
		add_filter( 'bp_ps_filters_template_field', array( $this, 'generate_location_field_filter' ), 50, 2 );

		// Proceed with query filter only if BP Members Directory Geolocation is not installed.
		// When installed, we will use its built-in query filter.
		//if ( ! class_exists( 'GMW_BP_Members_Directory_Geolocation_Addon' ) || ! gmw_get_option( 'bp_members_directory_geolocation', 'enabled', false ) ) {
			add_action( 'bp_user_query_uid_clauses', array( $this, 'modify_search_query' ), 500, 2 );
		//}
	}

	/**
	 * Add location field file to BP template stack.
	 *
	 * @param  [type] $stack [description].
	 *
	 * @return [type]        [description]
	 */
	public function template_stack( $stack ) {

		$stack[] = GMW_BPSGEO_PATH . '/templates/bps-fields';

		return $stack;
	}

	/**
	 * Get geolocation form values.
	 *
	 * @param  string $type type of BPS request.
	 *
	 * @return [type]       [description]
	 */
	public function bpsgeo_get_request( $type = 'search' ) {

		$form_values = '';

		if ( function_exists( 'bps_get_request' ) ) {

			$form_values = bps_get_request( $type );

		} elseif ( function_exists( 'bp_ps_get_request' ) ) {

			$form_values = bp_ps_get_request( $type );
		}

		if ( ! empty( $form_values['gmw_bpsgeo_location_gmw_proximity'] ) ) {
			return $form_values['gmw_bpsgeo_location_gmw_proximity'];
		} else {
			return array();
		}
	}

	/**
	 * Generate the location field.
	 *
	 * @param array $fields array of fields objects.
	 *
	 * @return array modified fields array.
	 */
	public function add_location_field( $fields ) {

		$field = new stdClass();

		$field->group          = 'GEO my WP';
		$field->id             = 'gmw_bpsgeo_location';
		$field->code           = 'gmw_bpsgeo_location';
		$field->name           = __( 'Location', 'geo-my-wp' );
		$field->description    = '';
		$field->type           = 'gmw_bpsgeo_location';
		$field->format         = 'gmw_bpsgeo_location';
		$field->search         = '';
		$field->sort_directory = 'bps_xprofile_sort_directory';
		$field->get_value      = 'bps_xprofile_get_value';
		$field->options        = array();
		$field->value          = '';

		$fields[] = $field;

		return $fields;
	}

	/**
	 * Modify memebrs search query to include memebrs based on location.
	 *
	 * This filter takes place when BP Members Directory Geolocation extension is not installed.
	 *
	 * Otherwise, we use the query of BP Members Directory Geolocation extension
	 *
	 * by modifying the form values above using the function above.
	 *
	 * @param  array  $clauses  original BP query clauses.
	 * @param  object $query    original query object.
	 *
	 * @return array  clauses
	 */
	public function modify_search_query( $clauses, $query ) {

		$values = $this->bpsgeo_get_request( 'search' );

		// Abort if address field left blank.
		if ( empty( $values['address'] ) ) {
			return $clauses;
		}

		global $wpdb;

		$table_name = $wpdb->base_prefix . 'gmw_locations';

		// Use coordinates if provided in submitted values.
		if ( ! empty( $values['lat'] ) && ! empty( $values['lng'] ) ) {

			$lat = $values['lat'];
			$lng = $values['lng'];

			// Otherwise, geocode the address.
		} else {

			$location_data = gmw_geocoder( $values['address'] );

			if ( empty( $location_data ) || isset( $location_data['error'] ) ) {
				return;
			}

			$lat = $location_data['lat'];
			$lng = $location_data['lng'];
		}

		$earth_radius = 'metric' === $values['units'] ? 6371.0088 : 3958.7613;

		$sql = array(
			'select'  => '',
			'where'   => array(),
			'having'  => '',
			'orderby' => '',
		);

		$sql['select'] = $wpdb->prepare(
			"
			SELECT object_id, ROUND( %s * acos( cos( radians( %s ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance
			FROM {$wpdb->base_prefix}gmw_locations gmw_locations",
			array( $earth_radius, $lat, $lng, $lat )
		);

		$sql['where'] = "WHERE object_type = 'user'";

		if ( ! empty( $values['distance'] ) ) {
			$sql['having'] = $wpdb->prepare( 'Having distance <= %s OR distance IS NULL', $values['distance'] );
		}

		$sql['orderby'] = 'ORDER BY distance';

		$sql = apply_filters( 'gmw_bpsgeo_location_query_clauses', $sql, $query, $this );

		// Get users id based on location.
		$results = $wpdb->get_col( implode( ' ', $sql ), 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		// Abort if no users were found.
		if ( empty( $results ) ) {

			$clauses['where'][] = '1 = 0';

			return $clauses;
		}

		// Get the user ID column based on the Select caluse.
		$column             = strpos( $clauses['select'], 'u.user_id' ) ? 'user_id' : 'ID';
		$users_id           = implode( ', ', esc_sql( $results ) );
		$clauses['where'][] = "u.{$column} IN ( {$users_id } )";

		return $clauses;
	}

	/**
	 * Generate location filter text.
	 *
	 * @param  string $output original filter output.
	 *
	 * @param  object $field  field object.
	 *
	 * @return mixed
	 */
	public function generate_location_field_filter( $output, $field ) {

		$values = $field->value;

		// Abort if not searching by location.
		if ( empty( $values['address'] ) ) {
			return esc_html__( 'is everywhere', 'geo-my-wp' );
		}

		$label = 'metric' === $values['units'] ? __( 'km', 'geo-my-wp' ) : __( 'Miles', 'geo-my-wp' );

		if ( ! empty( $values['distance'] ) ) {
			/* translators: %1$s: distance, %2$s: units, %3$s: address */
			$output = sprintf( esc_html__( 'is within %1$s %2$s of %3$s', 'geo-my-wp' ), $values['distance'], $label, $values['address'] );
		} else {
			/* translators: %1$s: address */
			$output = sprintf( esc_html__( 'is nearby %1$s', 'geo-my-wp' ), $values['address'] );
		}

		do_action( 'gmw_element_loaded', 'bp_profile_search_geolocation', $this );

		return $output;
	}
}
new GMW_BP_Profile_Search_Geolocation();
