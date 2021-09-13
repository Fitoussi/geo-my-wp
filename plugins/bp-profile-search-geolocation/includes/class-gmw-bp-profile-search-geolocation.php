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

	/**
	 * __construct function.
	 */
	public function __construct() {

		// Generate location field.
		add_filter( 'bps_field_config', array( $this, 'field_display' ), 50, 2 );
		add_action( 'bps_filter_selector', array( $this, 'field_selector' ), 50 );
		add_action( 'bps_filter_labels', array( $this, 'field_label' ), 50 );
		add_filter( 'bps_add_fields', array( $this, 'add_location_field' ), 10 );
		add_filter( 'bp_get_template_stack', array( $this, 'template_stack' ), 30 );

		// Add location filter on form submission.
		add_filter( 'bps_filters_template_field', array( $this, 'generate_location_field_filter' ), 50, 2 );

		// Modify form data when using the Members Directory geolocation extension.
		add_filter( 'gmw_bpmdg_form_data', array( $this, 'modify_form_data' ), 50, 2 );
		add_filter( 'gmw_kleo_geo_form_data', array( $this, 'modify_form_data' ), 50, 2 );

		// Proceed with query filter only if BP Members Directory Geolocation is not installed.
		// When installed, we will use its built-in query filter.
		if ( ! class_exists( 'GMW_BP_Members_Directory_Geolocation_Addon' ) ) {
			add_action( 'bp_user_query_uid_clauses', array( $this, 'modify_search_query' ), 500, 2 );
		}
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
	 * Add location field display
	 *
	 * @param  array  $displays existing displays.
	 *
	 * @param  object $field    field object.
	 *
	 * @return array            modified displays.
	 */
	public function field_display( $displays, $field ) {

		$displays['gmw_bpsgeo_location'] = array(
			'gmw_proximity' => 'proximity',
		);

		return $displays;
	}

	/**
	 * Add location field selector
	 *
	 * @param  array $labels existing labels.
	 *
	 * @return array         modified labels.
	 */
	public function field_selector( $labels ) {

		$labels['gmw_proximity'] = 'proximity';

		return $labels;
	}

	/**
	 * Add location field label type.
	 *
	 * @param  array $labels existing labels.
	 *
	 * @return array         modified labels.
	 */
	public function field_label( $labels ) {

		$labels['gmw_proximity'] = 'is within';

		return $labels;
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
		$results = $wpdb->get_col( implode( ' ', $sql ), 0 ); // WPCS: db call ok, unprepared sql ok, cache ok.

		// Abort if no users were found.
		if ( empty( $results ) ) {

			$clauses['where'][] = '1 = 0';

			return $clauses;
		}

		// Get the user ID column based on the orderby type.
		$column = in_array( $query->query_vars['type'], array( 'active', 'newest', 'popular', 'online' ), true ) ? 'user_id' : 'ID';

		$users_id           = implode( ', ', esc_sql( $results ) );
		$clauses['where'][] = "u.{$column} IN ( {$users_id } )";

		return $clauses;
	}

	/**
	 * Modify the form data of the BP Members Directory Geolocation extension ( when activated )
	 *
	 * By passing the location field values from BP Profile Search form.
	 *
	 * @param  array  $form_data form data of BP Members Directory Geolocation extension.
	 *
	 * @param  object $gmw_data  gmw form data.
	 *
	 * @return [type]            [description]
	 */
	public function modify_form_data( $form_data, $gmw_data ) {

		// Clear BP members directory form values when clearing BPS filters.
		if ( ! empty( $_GET['bps_form'] ) && 'clear' === $_GET['bps_form'] ) { // WPCS: CSRF ok.

			$form_data['prefix']  = 'bpsgeo';
			$form_data['address'] = '';
			$form_data['lat']     = '';
			$form_data['lng']     = '';

			return $form_data;
		}

		$values = $this->bpsgeo_get_request( 'search' );

		// Abort if address field left blank.
		if ( empty( $values['address'] ) ) {
			return $form_data;
		}

		$filter = current_filter();

		if ( 'gmw_bpmdg_form_data' === $filter ) {

			$prefix = 'bpbdg';

		} elseif ( 'gmw_kleo_geo_form_data' === $filter ) {

			if ( 'member' !== $gmw_data->component ) {
				return $form_data;
			}

			$prefix = 'kleo_geo';

		} else {
			return $form_data;
		}

		$ajax = defined( 'DOING_AJAX' );

		/**
		 * Modify the address field on page load or during ajax but when all the values match
		 *
		 * Between the BP Members Directory Geolocation extension and the BP Profile Fields Geolocation forms.
		 *
		 * This means that the orderby filter was changed.
		 *
		 * When the form values between the 2 plugins do not match it means that the BP Members Directory Geolocation
		 *
		 * form was submitted and in this case we don't override the values from BP Profile Fields Geolocation.
		 */
		if ( ! $ajax || ( $ajax && $values['address'] === $form_data['address'] && $values['units'] === $form_data['units'] ) && $values['distance'] === $form_data['radius'] ) {

			$form_data['prefix']  = 'bpsgeo';
			$form_data['address'] = $values['address'];
			$form_data['lat']     = $values['lat'];
			$form_data['lng']     = $values['lng'];
			$form_data['radius']  = $values['distance'];
			$form_data['units']   = $values['units'];
		}

		return $form_data;
	}

	/**
	 * Generate location filter text.
	 *
	 * @param  string $output original filter output.
	 *
	 * @param  object $field  field object.
	 *
	 * @return modified output.
	 */
	public function generate_location_field_filter( $output, $field ) {

		$values = $field->value;

		// Abort if not searching by location.
		if ( empty( $values['address'] ) ) {
			return __( 'is everywhere', 'geo-my-wp' );
		}

		$label = 'metric' === $values['units'] ? __( 'km', 'geo-my-wp' ) : __( 'Miles', 'geo-my-wp' );

		if ( ! empty( $values['distance'] ) ) {
			/* translators: %1$s: distance, %2$s: units, %3$s: address */
			$output = sprintf( esc_html__( 'is within %1$s %2$s of %3$s', 'geo-my-wp' ), $values['distance'], $label, $values['address'] );
		} else {
			/* translators: %1$s: address */
			$output = sprintf( esc_html__( 'is nearby %1$s', 'geo-my-wp' ), $values['address'] );
		}

		return $output;
	}
}
new GMW_BP_Profile_Search_Geolocation();
