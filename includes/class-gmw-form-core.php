<?php
/**
 * GEO my WP Base Form class.
 *
 * Generates the proximity search forms.
 *
 * This class should be extended for different forms.
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Base Proximity search form for GEO my WP.
 *
 * Need to be extended with child class.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
class GMW_Form_Core {

	/**
	 * Form ID
	 *
	 * @var string
	 */
	public $ID = 0;

	/**
	 * Form Prefix
	 *
	 * @var string
	 */
	public $prefix = '';

	/**
	 * Object type.
	 *
	 * Default to post.
	 *
	 * @var string
	 */
	public $object_type = 'post';

	/**
	 * The form being displayed
	 *
	 * @since 2.6.1
	 *
	 * @var array
	 *
	 * @access public
	 */
	public $form = array();

	/**
	 * Elements that GEO my WP form shortcodes accepts
	 *
	 * Can be filtered via apply_filters( 'gmw_shortcode_allowed_elements' );
	 *
	 * @var array
	 */
	public $allowed_form_elements = array(
		'search_form',
		'map',
		'search_results',
		'form',
	);

	/**
	 * If form element allowed
	 *
	 * @var boolean
	 */
	public $element_allowed = true;

	/**
	 * Address Filters.
	 *
	 * @var boolean
	 */
	public $address_filter = false;

	/**
	 * The gmw_location database fields that will be pulled in the search query.
	 *
	 * The default fields are generated via the method parse_db_fields().
	 *
	 * They can be overidden via this variable.
	 *
	 * @var array
	 */
	public $db_fields = array();

	/**
	 * Array of arguments that we be passed into the search query.
	 *
	 * @var array
	 */
	public $gmw_query_args = array();

	/**
	 * Array of arguments that can be used to cache queries using.
	 *
	 * GMW internal cache system.
	 *
	 * @var array
	 */
	public $query_cache_args = array();

	/**
	 * The holder for the search query.
	 *
	 * @var array|object
	 */
	public $query = array();

	/**
	 * $map_locations
	 *
	 * Holder for the location data that will pass to the map generator
	 *
	 * @var array
	 */
	public $map_locations = array();

	/**
	 * Show / hide location without location from search results
	 *
	 * @var boolean
	 */
	public $enable_objects_without_location = true;

	/**
	 * Generate the info-window content during query loop?
	 *
	 * This can be disabled if needed. For example, if using AJAX info-window.
	 *
	 * @var boolean
	 */
	public $get_info_window_content = true;

	/**
	 * Enable or disable the info-window tempalte files.
	 *
	 * To be used with premium extensions that use AJAX powered info windows.
	 *
	 * @var boolean
	 */
	public $load_info_window_templates = false;

	/**
	 * __construct
	 *
	 * Verify some data and generate default values.
	 *
	 * @param array $form the form being processed.
	 */
	public function __construct( $form ) {}

	/**
	 * Generate the DB fields that will be pulled from GMW locations DB table for the different search queries.
	 *
	 * @since 4.0
	 *
	 * @return [type]            [description]
	 */
	public function parse_db_fields() {

		if ( ! empty( $this->db_fields ) ) {

			$db_fields = $this->db_fields;

		} else {

			$db_fields = array(
				// '',
				'ID as location_id',
				'object_type',
				'object_id',
				'title as location_name',
				'user_id',
				'latitude',
				'longitude',
				'latitude as lat',
				'longitude as lng',
				'street_name',
				'street_number',
				'street',
				'premise',
				'neighborhood',
				'city',
				'region_name',
				'region_code',
				'postcode',
				'country_name',
				'country_code',
				'address',
				'formatted_address',
			);
		}

		$db_fields = apply_filters( 'gmw_form_db_fields', $db_fields, $this->form );
		$db_fields = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_db_fields', $db_fields, $this->form );
		$db_fields = preg_filter( '/^/', 'gmw_locations.', $db_fields );
		$db_fields = apply_filters( 'gmw_form_db_fields_prefixed', $db_fields, $this->form );

		// Deprecated. To be removed in the future. Use one of the filters below instead.
		if ( isset( $this->form['addon'] ) && 'ajax_forms' === $this->form['addon'] ) {

			$db_fields = apply_filters( 'gmw_ajaxfms_ajax_form_db_fields', $db_fields, $this->form );

			// Deprecated. To be removed in the future. Use one of the filters below instead.
		} elseif ( isset( $this->form['addon'] ) && 'global_maps' === $this->form['addon'] ) {

			$db_fields = apply_filters( 'gmw_gmaps_global_map_db_fields', $db_fields, $this->form );

			// Needed for normal forms only.
		} else {

			// The below is temporary. To be removed in the future.
			// This will add the dynamic location_class and location_count to the query results. This use to be done via the loop but now uses a function ( gmw_object_class() ) directly in the template file.
			// This is here to suppport older version of the template files that do not have that function in them.
			$db_fields[] = "CONCAT( 'single-{$this->form['object_type']} gmw-single-item gmw-single-{$this->form['object_type']} gmw-object-', ifnull( gmw_locations.object_id, '0' ), ' gmw-location-', ifnull( gmw_locations.ID, '0' ) ) AS location_class";
			$db_fields[] = "'' AS location_count";
		}

		return implode( ',', $db_fields );
	}

	/**
	 * Set default form values in child class.
	 *
	 * The form values here will be appended to the default form values generated by the setup_defaults() method.
	 */
	public function set_default_form_values() {}

	/**
	 * Setup the default form values.
	 *
	 * Setup the default form values and execute some hooks.
	 */
	public function setup_defaults() {

		$this->ID            = $this->form['ID'];
		$this->prefix        = $this->form['prefix'];
		$this->object_type   = $this->form['object_type'];
		$this->url_px        = gmw_get_url_prefix();
		$this->cache_enabled = GMW()->internal_cache;

		$this->form['elements']         = ! empty( $this->form['params']['elements'] ) ? explode( ',', $this->form['params']['elements'] ) : array();
		$this->form['submitted']        = false;
		$this->form['page_load_action'] = false;
		$this->form['form_values']      = array();
		$this->form['lat']              = false;
		$this->form['lng']              = false;
		$this->form['address']          = false;
		$this->form['paged']            = 1;
		$this->form['per_page']         = -1;
		$this->form['get_per_page']     = false; // Deprecated. Use per_page instead.
		$this->form['query_args']       = array();
		$this->form['address_filters']  = array();
		$this->form['orderby']          = 'distance';
		$this->form['units_array']      = false;
		$this->form['radius']           = false;
		$this->form['map_enabled']      = false;
		$this->form['map_usage']        = 'results';
		$this->form['results_enabled']  = false;
		$this->form['display_list']     = false; // Deprecated. Replaced with results_enabled.
		$this->form['has_locations']    = false;
		$this->form['results']          = array();
		$this->form['results_count']    = 0;
		$this->form['total_results']    = 0;
		$this->form['max_pages']        = 0;
		$this->form['in_widget']        = ! empty( $this->form['params']['widget'] ) ? true : false;
		$this->form['modify_permalink'] = 1;
		$this->form['no_results_map_enabled'] = true;
		$this->form['user_loggedin']    = ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) ? 1 : 0;

		// Set additional form values in child class.
		$this->set_default_form_values();

		$this->db_fields                       = $this->parse_db_fields();
		$this->enable_objects_without_location = apply_filters( 'gmw_form_enable_objects_without_location', $this->enable_objects_without_location, $this->form, $this );

		// Deprecated filters. Use the below instead.
		if ( 'global_maps' === $this->form['addon'] ) {
			$this->form = apply_filters( 'gmw_gmaps_global_map_default_form_values', $this->form, $this );
		}

		if ( 'ajax_forms' === $this->form['addon'] ) {
			$this->form = apply_filters( 'gmw_ajaxfms_ajax_form_default_values', $this->form, $this );
			$this->form = apply_filters( 'gmw_' . $this->form['prefix'] . '_ajax_form_default_values', $this->form, $this );
		}

		// New filters to modify form values.
		$this->form = apply_filters( 'gmw_default_form_values', $this->form, $this );
		$this->form = apply_filters( "gmw_{$this->form['component']}_default_form_values", $this->form, $this );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_default_form_values", $this->form, $this );

		$this->query_cache_args['user_loggedin']                    = $this->form['user_loggedin'];
		$this->query_cache_args['showing_objects_without_location'] = $this->enable_objects_without_location;
		$this->query_cache_args['db_fields']                        = $this->db_fields;
	}

	/**
	 * Info window arguments.
	 *
	 * This is where some data that will pass to the map info-window is generated
	 *
	 * as ach object might generate this data differently.
	 *
	 * This method will run in the_location() method and will have the $object availabe to use.
	 *
	 * For posts types, for example, we will use the function as below:
	 *
	 * return array(
	 *      'type'            => 'standard',
	 *      'url'             => get_permalink( $post_id ),
	 *      'title'           => get_the_title( $post_id ),
	 *      'image'           => get_the_post_thumbnail( $post_id ),
	 *      'directions_link' => true,
	 *      'address'         => true,
	 *      'distance'        => true,
	 *      'location_meta'   => array( 'phone', 'fax', 'email' )
	 * );
	 *
	 * @param  array $object the object data.
	 *
	 * @return array of arguments.
	 */
	public function get_info_window_args( $object ) {

		return array(
			'prefix'          => $this->prefix,
			'type'            => 'standard',
			'url'             => '#',
			'title'           => false,
			'image_url'       => false,
			'image'           => true,
			'directions_link' => true,
			'address'         => true,
			'distance'        => true,
			'location_meta'   => false,
		);
	}

	/**
	 * Enqueue info-window stylesheet once on page load.
	 *
	 * This will be used with AJAX powered info-windows that use template files.
	 *
	 * @since 4.0
	 */
	public function get_info_window_template_data() {
		$this->form['info_window_template'] = gmw_get_info_window_template_data( $this->form );
	}

	/**
	 * Results message.
	 *
	 * The placeholders will be replaced using the function get_results_message();
	 *
	 * @since 1.0.
	 *
	 * @var array
	 */
	public function results_message_placeholders() {
		return array(
			'count_message'           => __( 'Showing {from_count} - {to_count} of {total_results} locations', 'geo-my-wp' ),
			'location_message'        => __( ' within {radius}{units} from {address}', 'geo-my-wp' ),
			'load_more_count_message' => __( 'Showing {results_count} of {total_results} locations', 'geo-my-wp' ),
			'single_count_message'    => __( '1 location found', 'geo-my-wp' ),
		);
	}

	/**
	 * Generate results message.
	 *
	 * @return [type] [description]
	 */
	public function get_results_message() {

		$message = $this->results_message_placeholders();
		$args    = array(
			'page'             => $this->form['paged'],
			'per_page'         => absint( $this->form['per_page'] ),
			'results_count'    => $this->form['results_count'],
			'total_count'      => $this->form['total_results'],
			'form_submitted'   => $this->form['submitted'],
			'address'          => $this->form['address'],
			'radius'           => $this->form['radius'],
			'units'            => $this->form['units'],
			'count_message'    => $message['count_message'],
			'location_message' => $message['location_message'],
		);

		return GMW_Template_Functions_Helper::generate_results_message( $args, $this->form );
	}

	/**
	 * Generate the "No results" message.
	 *
	 * @return [type] [description]
	 */
	public function no_results_message() {

		// display geocoder error if failed. Otherwise, show no results message.
		if ( ! empty( $this->form['location']['error'] ) ) {

			$message = $this->form['location']['error'];

		} else {

			$message = __( 'No results found', 'geo-my-wp' );
		}

		return $message;
	}

	/**
	 * Get user's location to pass to the map.
	 *
	 * This is the location of the address entered by the user in the address field
	 *
	 * of the search form or the location retrived when the locator button clicked.
	 *
	 * @since 4.0
	 *
	 * @return [type] [description]
	 */
	public function get_user_position() {

		$user_position = array(
			'lat'        => isset( $this->form['lat'] ) ? $this->form['lat'] : false,
			'lng'        => isset( $this->form['lng'] ) ? $this->form['lng'] : false,
			'address'    => isset( $this->form['address'] ) ? $this->form['address'] : false,
			'map_icon'   => GMW()->default_icons['user_location_icon_url'],
			'icon_size'  => GMW()->default_icons['user_location_icon_size'],
			'iw_content' => __( 'Your Location', 'gmw-ajax-forms' ),
			'iw_open'    => ! empty( $this->form['results_map']['yl_icon'] ) ? true : false,
		);

		$user_position = apply_filters( 'gmw_form_user_position', $user_position, $this->form, $this );
		$user_position = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_user_position', $user_position, $this->form, $this );

		return $user_position;
	}

	/**
	 * Address geocoder.
	 *
	 * @since 1.1
	 *
	 * Used to geocode address on page load or when the coords are missing for some reason on form submission.
	 *
	 * @param  string $address the address to geocode.
	 *
	 * @return array of lat and lng.
	 */
	public function geocode( $address ) {

		$output = array(
			'address' => '',
			'lat'     => false,
			'lng'     => false,
		);

		// if geocoder class is missing for some reason.
		if ( ! function_exists( 'gmw_geocoder' ) ) {

			$output['lat'] = -1;
			$output['lng'] = -1;

			gmw_trigger_error( 'GEO my WP geocoder function is missing.' );

			// otherwise, try geocoding the address.
		} else {

			$geocoded = gmw_geocoder( $address );

			// if geocode failed, return -1 as lat/lng which will return no results.
			if ( isset( $geocoded['error'] ) ) {

				$output['lat'] = -1;
				$output['lng'] = -1;

			} else {

				$output['address'] = $address;
				$output['lat']     = isset( $geocoded['lat'] ) ? $geocoded['lat'] : -1;
				$output['lng']     = isset( $geocoded['lng'] ) ? $geocoded['lng'] : -1;
			}
		}

		return $output;
	}

	/**
	 * Generate the default location on page load.
	 *
	 * This is set in the "Address Filter" that is set in the Page Load Results tab of the form.
	 *
	 * @return [type] [description]
	 */
	public function get_page_load_location() {

		$page_load_options = $this->form['page_load_results'];
		$user_location     = gmw_get_user_current_location();
		$output            = array(
			'address' => '',
			'lat'     => '',
			'lng'     => '',
		);

		// check for user's location.
		if ( ! empty( $page_load_options['user_location'] ) && ! empty( $user_location ) ) {

			$output = array(
				'address' => $user_location->formatted_address,
				'lat'     => $user_location->lat,
				'lng'     => $user_location->lng,
			);

			// otherwise, check if pre-defined address provided.
		} elseif ( ! empty( $page_load_options['address_filter'] ) ) {

			$output['address'] = $page_load_options['address_filter'];

			// geocode the address.
			$coords = $this->geocode( $output['address'] );

			// set new coords.
			$output['lat'] = $coords['lat'];
			$output['lng'] = $coords['lng'];

			// For internal cache purposes.
			$this->form['page_load_results']['geocoded_data'] = $coords;

		} else {

			$output = apply_filters( 'gmw_form_page_load_location', $output, $user_location, $this->form );
			$output = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_page_load_location', $output, $user_location, $this->form );
		}

		return $output;
	}

	/**
	 * Pass an array of arguments to the search query.
	 *
	 * Arguments can be modified using the filter
	 *
	 * apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_args', $this->form['query_args'], $this->form, $this );
	 *
	 * @since 4.0
	 *
	 * @return [type] [description]
	 */
	public function get_query_args() {
		return array();
	}

	/**
	 * Parse the query arguments.
	 *
	 * @since 4.0
	 */
	public function parse_query_args() {

		// Get the query arguments.
		$this->form['query_args'] = $this->get_query_args();

		// Pass form values to the query. Can be used with different filters inside the main query.
		// Also used for intenal cache purposes.
		$this->form['query_args']['gmw_args'] = $this->form['page_load_action'] ? $this->form['page_load_results'] : $this->form['form_values'];

		// Modify query args.
		$this->form['query_args'] = apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_args', $this->form['query_args'], $this->form, $this );
	}

	/**
	 * Some global action hooks and filters that fire before that search query takes place.
	 *
	 * @since 4.0
	 */
	public function pre_search_query_hooks() {

		$object_type = 'bp_group' === $this->form['object_type'] ? 'group' : $this->form['object_type'];

		// Deprecated filter. Use any of the below filters instead.
		$this->form = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_before_' . $object_type . 's_query', $this->form, $this );

		// New filters.
		$this->form = apply_filters( 'gmw_form_before_search_query', $this->form, $this );
		$this->form = apply_filters( 'gmw_' . $this->form['component'] . '_form_before_search_query', $this->form, $this );
		$this->form = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_before_search_query', $this->form, $this );
	}

	/**
	 * That's where the search query goes.
	 *
	 * The results need to be passed to $this->query.
	 *
	 * @since 4.0
	 */
	public function parse_search_query() {
		$this->query = array();
	}

	/**
	 * Some global action hooks and filters that fire after that search query took place.
	 *
	 * Note that many hooks and filters were deprecated. Those are hooks for the specific form types and they were replaced with global hooks.
	 *
	 * @since 4.0
	 */
	public function post_search_query_hooks() {

		$object_type = 'bp_group' === $this->form['object_type'] ? 'group' : $this->form['object_type'];

		// Deprecated filters for Global Maps. To be removed. Use any of the below instead.
		if ( 'global_maps' === $this->form['addon'] ) {

			// Posts global maps.
			if ( 'gmapspt' === $this->form['prefix'] ) {
				$this->query        = apply_filters( 'gmw_gmapspt_cached_posts_query', $this->query, $this->form );
				$this->query        = apply_filters( 'gmw_gmapspt_posts_query', $this->query, $this->form );
				$this->query->posts = apply_filters( 'gmw_gmapspt_posts_after_posts_query', $this->query->posts, $this->form );
			}

			// Members global maps.
			if ( 'gmapsfl' === $this->form['prefix'] ) {
				$this->query          = apply_filters( 'gmw_gmapsfl_cached_members_query', $this->query, $this->form );
				$this->query          = apply_filters( 'gmw_gmapsfl_members_query', $this->query, $this->form );
				$this->query->results = apply_filters( 'gmw_gmapsfl_members_after_members_query', $this->query->results, $this->form );
			}

			// users global maps.
			if ( 'gmapsul' === $this->form['prefix'] ) {
				$this->query          = apply_filters( 'gmw_gmapsul_cached_users_query', $this->query, $this->form );
				$this->query          = apply_filters( 'gmw_gmapsul_users_query', $this->query, $this->form );
				$this->query->results = apply_filters( 'gmw_gmapsul_users_after_users_query', $this->query->results, $this->form );
			}

			// Groups global maps.
			if ( 'gmapsgl' === $this->form['prefix'] ) {
				$this->locations = apply_filters( 'gmw_gmapsgl_cached_groups_locations', $this->locations, $this->form );
				$this->locations = apply_filters( 'gmw_gmapsgl_groups_locations', $this->locations, $this->form );
				$this->locations = apply_filters( 'gmw_gmapsgl_groups_after_groups_query', $this->locations, $this->form );
			}
		}

		// Deprecated filter. To be removed. Use the below instead.
		$this->form  = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_after_' . $object_type . '_query', $this->form, $this );
		$this->query = apply_filters( 'gmw_' . $this->form['prefix'] . '_' . $object_type . 's_before_' . $object_type . 's_loop', $this->query, $this->form, $this );

		// New available filters.
		$this->form  = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_after_search_query', $this->form, $this );
		$this->query = apply_filters( 'gmw_' . $this->form['prefix'] . '_query_after_search_query', $this->query, $this->form, $this );
	}

	/**
	 * Parse the search results.
	 *
	 * Pass some results variables into the form object.
	 *
	 * this->form['results']       = array();
	 *
	 * $this->form['results_count'] = 0;
	 *
	 * $this->form['total_results'] = 0;
	 *
	 * $this->form['max_pages']     = 0;
	 *
	 * @since 4.0
	 */
	public function parse_query_results() {

		$this->form['results']       = array();
		$this->form['results_count'] = 0;
		$this->form['total_results'] = 0;
		$this->form['max_pages']     = 0;
	}

	/**
	 * Execute the search query.
	 *
	 * Note the methods that you need to place in the child class for this query to work properly.
	 *
	 * 1. $this->get_query_args() - this should return an array of the search query arguments.
	 *
	 * 2. $this->parse_search_query() - this is where the search query should takes place.
	 *
	 * 3. $this->parse_query_results() - this is where you generate some results data once the resultes were found.
	 *
	 * @return [type] [description]
	 */
	public function search_query() {

		// Parse the query arguments.
		$this->parse_query_args();

		// Hooks and filters before search query.
		$this->pre_search_query_hooks();

		// Look for query in internal cache.
		if ( $this->cache_enabled ) {

			// Some variables for internal cache.
			$this->form['query_args']['gmw_cache_args'] = $this->query_cache_args;

			// cache key.
			$hash            = md5( wp_json_encode( $this->form['query_args'] ) );
			$query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_' . $this->form['object_type'] . '_query' );
			$this->query     = get_transient( $query_args_hash );
		}

		// Performe new search query ( when not found in cache ).
		if ( ! $this->cache_enabled || empty( $this->query ) ) {

			/**
			 * Use the parse_search_query(); method in a child class to performe the search query of a specific object.
			 *
			 * @var [type]
			 */
			$this->parse_search_query();

			// set new query in internal cache.
			if ( $this->cache_enabled ) {
				set_transient( $query_args_hash, $this->query, GMW()->internal_cache_expiration );
			}
		}

		// Hooks and filters after search query.
		$this->post_search_query_hooks();

		// Parse the query results.
		$this->parse_query_results();

		return apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_results', $this->form['results'], $this->form, $this );
	}

	/**
	 * The members loop.
	 *
	 * To be used when displaying map only without the results.
	 *
	 * In that case we need to run the loop in order to collect some data for the map.
	 *
	 * @since 4.0
	 */
	public function object_loop() {}

	/**
	 * Check if locations exists
	 *
	 * @return boolean [description]
	 */
	public function has_locations() {
		return $this->form['has_locations'] ? true : false;
	}

	/**
	 * Get template files and enqueue stylesheets and custom CSS.
	 *
	 * @since 4.0
	 *
	 * @param  string $template_type 'search-form' || 'search-results'.
	 *
	 * @return [type]                [description]
	 */
	public function get_template_file( $template_type = 'search_form' ) {

		if ( 'search_results' === $template_type ) {
			$function  = 'gmw_get_search_results_template';
			$type      = 'results';
			$file_name = 'content.php';
		} else {
			$type      = 'form';
			$function  = 'gmw_get_search_form_template';
			$file_name = '';
		}

		// get results template file.
		$template = $function( $this->form['component'], $this->form[ 'search_' . $type ][ $type . '_template' ], $this->form['addon'], $file_name );

		// enqueue stylesheet if not already enqueued.
		if ( ! wp_style_is( $template['stylesheet_handle'], 'enqueued' ) && file_exists( $template['stylesheet_path'] ) ) {
			wp_register_style( $template['stylesheet_handle'], $template['stylesheet_uri'], array( 'gmw-frontend' ), GMW_VERSION, false );
			wp_enqueue_style( $template['stylesheet_handle'] );
		}

		// Add custom CSS as inline script.
		if ( ! empty( $this->form[ 'search_' . $type ]['styles']['custom_css'] ) ) {

			// Needed when registering an inline style.
			if ( ! wp_style_is( $template['stylesheet_handle'], 'enqueued' ) ) {
				wp_register_style( $template['stylesheet_handle'], false );
				wp_enqueue_style( $template['stylesheet_handle'] );
			}

			wp_add_inline_style( $template['stylesheet_handle'], $this->form[ 'search_' . $type ]['styles']['custom_css'] );
		}

		return $template;
	}

	/**
	 * Outputs the search form element on the page.
	 *
	 * @return void
	 */
	public function search_form() {}

	/**
	 * Outputs the search results element on the page.
	 *
	 * @return void
	 */
	public function search_results() {}

	/**
	 * Generate the map element on the page.
	 *
	 * @return void
	 */
	public function map() {

		$args = array(
			'map_id'         => $this->form['ID'],
			'prefix'         => $this->form['prefix'],
			'map_type'       => $this->form['addon'],
			'map_width'      => $this->form['results_map']['map_width'],
			'map_height'     => $this->form['results_map']['map_height'],
			'expand_on_load' => ! empty( $this->form['results_map']['expand_on_load'] ) ? true : false,
		);

		echo gmw_get_map_element( $args, $this->form ); // WPCS: XSS ok.
	}

	/**
	 * Generate the map.
	 *
	 * @return [type] [description]
	 */
	public function generate_map() {

		// disable map dynamically.
		if ( ! apply_filters( 'gmw_trigger_map', true, $this->form, $this ) ) {
			return;
		}

		$iw_type     = ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard';
		$iw_ajax     = ! empty( $this->form['info_window']['ajax_enabled'] ) ? 1 : 0;
		$iw_template = ! empty( $this->form['info_window']['template'][ $iw_type ] ) ? $this->form['info_window']['template'][ $iw_type ] : 'default';

		if ( $this->load_info_window_templates ) {

			$this->get_info_window_template_data();

			// Force AJAX when templates are loaded.
			$iw_ajax = 1;
		}

		$map_args = array(
			'map_id'               => $this->form['ID'],
			'map_type'             => $this->form['addon'],
			'prefix'               => $this->form['prefix'],
			'info_window_type'     => $iw_type,
			'info_window_ajax'     => $iw_ajax,
			'info_window_template' => $iw_template,
			'group_markers'        => ! empty( $this->form['map_markers']['grouping'] ) ? $this->form['map_markers']['grouping'] : 'standard',
			'draggable_window'     => isset( $this->form['info_window']['draggable_use'] ) ? true : false,
			'hide_no_locations'    => false,
		);

		$map_options = array(
			'zoom'      => $this->form['results_map']['zoom_level'],
			'mapTypeId' => isset( $this->form['results_map']['map_type'] ) ? $this->form['results_map']['map_type'] : 'ROADMAP',
		);

		// generate the map.
		return gmw_get_map_object( $map_args, $map_options, $this->map_locations, $this->get_user_position(), $this->form );
	}

	/**
	 * Run some tasks for each location in the results.
	 *
	 * @param  object $object object in the results.
	 *
	 * @param  array  $gmw    gmw form.
	 *
	 * @return [type]         [description]
	 */
	public function the_location( $object, $gmw = array() ) {

		/**
		 * To support older template files.
		 *
		 * New tempalte files should have the action hook 'gmw_the_object_location'.
		 *
		 * To be removed.
		 */
		if ( 'gmw_search_results_loop_item_start' === current_action() ) {

			if ( did_action( 'gmw_the_object_location' ) ) {

				remove_action( 'gmw_search_results_loop_item_start', array( $this, 'the_location' ), 10, 2 );

				return $object;
			}

			// For older version of template files where the first argument of the action hook was $gmw.
			if ( is_array( $object ) ) {
				$object = $gmw;
			}
		}

		// Collect map data.
		if ( $this->form['map_enabled'] ) {

			$iw_args  = $this->get_info_window_content ? $this->get_info_window_args( $object ) : array();
			$location = gmw_get_object_location_map( $object, $iw_args, $this->form );

			if ( $location ) {
				$this->map_locations[] = $location;
			}
		}

		$object = apply_filters( 'gmw_form_the_location', $object, $this->form, $this );
	}

	/**
	 * Send json data for AJAX calls.
	 *
	 * @param  array $json_data [description].
	 *
	 * @since 4.0
	 */
	public function send_json( $json_data = array() ) {

		// Deprecated filter. User the below instead.
		if ( 'global_maps' === $this->form['addon'] ) {
			$json_data = apply_filters( 'gmw_gmaps_global_map_json_data', $json_data, $this->form, $this );
		}

		// Deprecated filter. User the below instead.
		if ( 'ajax_forms' === $this->form['addon'] ) {
			$json_data = apply_filters( 'gmw_ajaxfms_ajax_form_json_data', $json_data, $this->form, $this );
		}

		$json_data = apply_filters( 'gmw_form_json_data', $json_data, $this->form );
		$json_data = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_json_data', $json_data, $this->form );

		wp_send_json( $json_data );
	}
}
