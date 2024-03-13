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
	 * The prefix added to the URL parameters.
	 *
	 * The prefix is blank by default.
	 *
	 * @var string
	 *
	 * @access public
	 */
	public $url_px = '';

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
	 * Internal cache enabled/disabled.
	 *
	 * @var boolean
	 */
	public $cache_enabled = true;

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
	 * Hold the locations from search results.
	 *
	 * @since 4.0
	 *
	 * @var array
	 */
	public $results = array();

	/**
	 * Can be used to collect locations.
	 *
	 * @since 4.1.1
	 *
	 * @var array
	 */
	public $locations = array();

	/**
	 * Search results enabled/disabled.
	 *
	 * @var boolean
	 */
	public $show_results = false;

	/**
	 * Hold the HTML results for AJAX forms.
	 *
	 * @since 4.0
	 *
	 * @var array
	 */
	public $html_results = '';

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
	 * Enable AJAX on page load results.
	 *
	 * For AJAX powered forms.
	 *
	 * @since 4.0
	 *
	 * @var boolean
	 */
	public $page_load_results_ajax = false;

	/**
	 * Holds the results template path and uri.
	 *
	 * @var array
	 *
	 * @since 4.0
	 */
	public $results_template = array();

	/**
	 * __construct
	 *
	 * Verify some data and generate default values.
	 *
	 * @param array $form the form being processed.
	 */
	public function __construct( $form ) {

		$this->form = $form;

		// Verify form element.
		if ( ! $this->verify_form_element() ) {
			return;
		}

		$this->setup_defaults();
	}

	/**
	 * Verify the form element passes to the [gmw] shortcode.
	 *
	 * @since  4.0
	 *
	 * @return bool
	 */
	public function verify_form_element() {

		// verify that the form element is legit.
		if ( ! wp_doing_ajax() && ! empty( $this->form['current_element'] ) && ! in_array( $this->form['current_element'], $this->allowed_form_elements, true ) ) {

			$this->element_allowed = false;

			$message = sprintf(
				/* translators: %s replaced with form type */
				__( 'The [gmw] shortcode attribute "%s" is an invalid form type.', 'geo-my-wp' ),
				$this->form['current_element']
			);

			gmw_trigger_error( $message );

			return false;
		}

		return true;
	}

	/**
	 * Initiator for AJAX powered forms.
	 *
	 * @param  mixed $form array || int $form array or form ID.
	 *
	 * @return mixed
	 */
	public static function init( $form = array() ) {

		// Get the form in case we pass form ID.
		if ( is_numeric( $form ) ) {
			$form = gmw_get_form( $form ); // phpcs:ignore: CSRF ok.
		}

		if ( empty( $form ) || ! is_array( $form ) ) {
			return __( 'GEO my WP form doesn\'t exist.', 'geo-my-wp' );
		}

		/**
		 * Include required files and functions.
		 *
		 * Using the hooks below we make sure
		 *
		 * we include some global maps files and functions only when needed.
		 *
		 * Which means when the shortcode and maps takes place.
		 *
		 * @param  array  $form [description]
		 * @return [type]       [description]
		 */

		// Deprecated filters. Use the below instead.
		if ( 'ajax_forms' === $form['addon'] ) {
			do_action( 'gmw_ajaxfms_form_init', $form );
			do_action( 'gmw_' . $form['prefix'] . '_form_init', $form );
		}

		// Deprecated filters. Use the below instead.
		if ( 'global_maps' === $form['addon'] ) {
			do_action( 'gmw_global_map_init', $form );
			do_action( 'gmw_' . $form['prefix'] . '_global_map_init', $form );
		}

		// New filters.
		do_action( 'gmw_pre_form_init', $form );
		do_action( 'gmw_' . $form['component'] . '_pre_form_init', $form );
		do_action( 'gmw_' . $form['prefix'] . '_pre_form_init', $form );

		$custom_class_name = apply_filters( 'gmw_form_custom_class_name', '', $form );

		// For custom class name.
		if ( '' !== $custom_class_name ) {

			$class_name = $custom_class_name;

			// Otherwise, use core file and class names.
		} else {

			$folder_name = GMW()->addons[ $form['component'] ]['templates_folder'];
			$file_name   = 'class-gmw-' . str_replace( '_', '-', $form['slug'] ) . '-form.php';
			$file_path   = GMW()->addons[ $form['addon'] ]['plugin_dir'] . '/plugins/' . $folder_name . '/includes/' . $file_name;
			$class_name  = 'GMW_' . $form['slug'] . '_Form';

			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}

		// If class doens't exist.
		if ( ! class_exists( $class_name ) ) {

			$class_name = ucwords( $class_name, '_' );

			if ( ! class_exists( $class_name ) ) {
				return $class_name . ' class is missing.';
			}
		}

		// initiate the class.
		return new $class_name( $form );
	}

	/**
	 * Generate the DB fields that will be pulled from GMW locations DB table for the different search queries.
	 *
	 * @since 4.0
	 *
	 * @return [type]            [description]
	 */
	public function parse_db_fields() {

		$db_fields = gmw_parse_form_db_fields( $this->db_fields, $this->form );

		return implode( ',', $db_fields );
	}

	/**
	 * Set default form values in child class.
	 *
	 * The form values here will be appended to the default form values generated by the setup_defaults() method.
	 *
	 * Usually, in the child class this method will check for the form state, if it is a page load or if was submitted.
	 *
	 * And based on that it will append some additional default values to the form.
	 */
	public function set_default_values() {
	}

	/**
	 * Setup the default form values.
	 *
	 * Setup the default form values and execute some hooks.
	 */
	public function setup_defaults() {

		$this->url_px = gmw_get_url_prefix();

		$this->form['elements']                        = ! empty( $this->form['params']['elements'] ) ? explode( ',', $this->form['params']['elements'] ) : array();
		$this->form['user_loggedin']                   = ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) ? 1 : 0;
		$this->form['in_widget']                       = ! empty( $this->form['params']['widget'] ) ? true : false;
		$this->form['submitted']                       = false;
		$this->form['page_load_action']                = false;
		$this->form['form_values']                     = array();
		$this->form['lat']                             = '';
		$this->form['lng']                             = '';
		$this->form['address']                         = '';
		$this->form['paged_name']                      = 'page';
		$this->form['paged']                           = 1;
		$this->form['per_page']                        = -1;
		$this->form['get_per_page']                    = false; // Deprecated. Use per_page instead.
		$this->form['query_args']                      = array();
		$this->form['address_filters']                 = array();
		$this->form['orderby']                         = 'distance';
		$this->form['order']                           = 'ASC';
		$this->form['units_array']                     = false;
		$this->form['radius']                          = false;
		$this->form['map_enabled']                     = false;
		$this->form['map_usage']                       = 'results';
		$this->form['results_enabled']                 = false;
		$this->form['display_list']                    = false; // Deprecated. Replaced with results_enabled.
		$this->form['has_locations']                   = false;
		$this->form['results']                         = array();
		$this->form['results_count']                   = 0;
		$this->form['total_results']                   = 0;
		$this->form['max_pages']                       = 0;
		$this->form['results_message']                 = __( 'Showing locations', 'geo-my-wp' );
		$this->form['no_results_message']              = __( 'No results found.', 'geo-my-wp' );
		$this->form['modify_permalink']                = 0;
		$this->form['html_results']                    = '';
		$this->form['no_results_map_enabled']          = true;
		$this->form['enable_page_load_ajax']           = $this->page_load_results_ajax;
		$this->form['enable_objects_without_location'] = apply_filters( 'gmw_form_enable_objects_without_location', $this->enable_objects_without_location, $this->form, $this ); // Deprecated filter. Use 'gmw_default_form_values' instead.

		$bounds_search = apply_filters(
			'gmw_search_within_boundaries',
			array(
				'state'   => false,
				'country' => true,
			),
			$this->form
		);

		if ( ! is_array( $bounds_search ) ) {
			$bounds_search = array(
				'state'   => $bounds_search,
				'country' => $bounds_search,
			);
		}

		$this->form['boundaries_search'] = $bounds_search;

		/**
		 * This is where the child class apply its default form values.
		 *
		 * Child class should do so based on page load or form submission values.
		 */
		$this->set_default_values();

		// Address filters.
		$this->form['address_filters'] = gmw_form_get_address_filters( $this->form );

		// For page load default proximity search.
		if ( $this->form['page_load_action'] ) {

			$page_load_location    = $this->get_page_load_location();
			$this->form['address'] = $page_load_location['address'];
			$this->form['radius']  = ! empty( $this->form['page_load_results']['radius'] ) ? $this->form['page_load_results']['radius'] : false;
			$this->form['units']   = ! empty( $this->form['page_load_results']['units'] ) ? $this->form['page_load_results']['units'] : 'imperial';

			// Check if geocoder failed ( -1 for lat/lng ).
			if ( -1 !== $page_load_location['lat'] && '' !== $page_load_location['lat'] ) {
				$this->form['lat'] = $page_load_location['lat'];
				$this->form['lng'] = $page_load_location['lng'];
			}
			// phpcs:disable.
			/*else {
				$this->form['lat'] = '';
				$this->form['lng'] = '';
			}*/
			// phpcs:enable.
		}

		$this->ID            = $this->form['ID'];
		$this->prefix        = empty( $this->prefix ) ? $this->form['prefix'] : $this->prefix;
		$this->object_type   = empty( $this->object_type ) ? $this->form['object_type'] : $this->object_type;
		$this->cache_enabled = GMW()->internal_cache;
		$this->db_fields     = $this->parse_db_fields();

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
		$this->query_cache_args['showing_objects_without_location'] = $this->form['enable_objects_without_location'];
		$this->query_cache_args['db_fields']                        = $this->db_fields;

		// For internal cache when using WPML plugin.
		if ( class_exists( 'SitePress' ) ) {
			$this->query_cache_args['wpml'] = apply_filters( 'wpml_current_language', null );
		}
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
	public function get_info_window_args( $object ) { // phpcs:ignore.

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
			'location_message'        => __( ' within {radius} {units} from {address}', 'geo-my-wp' ),
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
			// phpcs:ignore.
			// 'icon_size'  => 'google_maps' === GMW()->maps_provider ? null : GMW()->default_icons['user_location_icon_size'],
			'iw_content' => __( 'Your Location', 'geo-my-wp' ),
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
	 * Parse the search query arguments.
	 *
	 * Query args can be modified using the filter:
	 *
	 * apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_args', $this->form['query_args'], $this->form, $this );
	 *
	 * @since 4.0
	 */
	public function parse_query_args() {

		/**
		 * GMW Query args to pass to query.
		 *
		 * @var array
		 */
		$gmw_location_args = array(
			'gmw_enabled'                         => true,
			'gmw_address'                         => $this->form['address'],
			'gmw_lat'                             => $this->form['lat'],
			'gmw_lng'                             => $this->form['lng'],
			'gmw_radius'                          => $this->form['radius'],
			'gmw_units'                           => $this->form['units'],
			'gmw_address_filters'                 => $this->form['address_filters'],
			'gmw_swlatlng'                        => ! empty( $this->form['form_values']['swlatlng'] ) ? $this->form['form_values']['swlatlng'] : '',
			'gmw_nelatlng'                        => ! empty( $this->form['form_values']['nelatlng'] ) ? $this->form['form_values']['nelatlng'] : '',
			'gmw_enable_objects_without_location' => $this->form['enable_objects_without_location'],
		);

		// Get the query arguments from child class and merge them with gmw query args.
		$this->form['query_args'] = wp_parse_args( $this->get_query_args(), $gmw_location_args );

		// Pass form values to the query. Can be used with different filters inside the main query.
		// Also used for intenal cache purposes.
		$this->form['query_args']['gmw_args'] = $this->form['page_load_action'] ? $this->form['page_load_results'] : $this->form['form_values'];

		// phpcs:ignore.
		// $this->form['query_args'] = apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_args', $this->form['query_args'], $this->form, $this ); // Modify query args.
	}

	/**
	 * Some global action hooks and filters that fire before that search query takes place.
	 *
	 * @since 4.0
	 */
	public function pre_search_query_hooks() {

		//$object_type = 'bp_group' === $this->form['object_type'] ? 'group' : $this->form['object_type'];

		if ( 'members_locator' === $this->form['component'] ) {

			$object_type = 'member';

		} elseif ( 'bp_groups_locator' === $this->form['component'] ) {

			$object_type = 'group';

		} else {

			$object_type = $this->form['object_type'];
		}

		// Deprecated filters. Use any of the below new filters instead.
		$this->form = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_before_' . $object_type . 's_query', $this->form, $this );
		$this->form = apply_filters( 'gmw_' . $this->form['component'] . '_form_before_' . $object_type . 's_query', $this->form, $this );

		if ( 'nbpost' === $this->form['prefix'] ) {
			$this->form = apply_filters( 'gmw_nbp_form_before_posts_query', $this->form, $this );
		}

		// New filters.
		$this->form = apply_filters( 'gmw_form_before_search_query', $this->form, $this );
		$this->form = apply_filters( 'gmw_' . $this->form['component'] . '_form_before_search_query', $this->form, $this );
		$this->form = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_before_search_query', $this->form, $this );

		// Modify query args.
		$this->form['query_args'] = apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_args', $this->form['query_args'], $this->form, $this );
	}

	/**
	 * That's where the search query goes.
	 *
	 * Use this method in the child class to performe the search query and pass the results into $this->query.
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

		//$object_type = 'bp_group' === $this->form['object_type'] ? 'group' : $this->form['object_type'];

		if ( 'members_locator' === $this->form['component'] ) {

			$object_type = 'member';

		} elseif ( 'bp_groups_locator' === $this->form['component'] ) {

			$object_type = 'group';

		} else {

			$object_type = $this->form['object_type'];
		}

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
				$this->query->locations = apply_filters( 'gmw_gmapsgl_cached_groups_locations', $this->query->locations, $this->form );
				$this->query->locations = apply_filters( 'gmw_gmapsgl_groups_locations', $this->query->locations, $this->form );
				$this->query->locations = apply_filters( 'gmw_gmapsgl_groups_after_groups_query', $this->query->locations, $this->form );
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
	 * Use in child class to pass some results variables into the form object.
	 *
	 * this->form['results']       = array(); // search results.
	 *
	 * $this->form['results_count'] = 0; // number of results currently showing on the page.
	 *
	 * $this->form['total_results'] = 0; // Total results.
	 *
	 * $this->form['max_pages']     = 0; // Maximum number of pages.
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
	 * Since 4.0
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

		if ( ! empty( $this->form['results'] ) ) {
			$this->form['has_locations'] = true;
		}

		return apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_results', $this->form['results'], $this->form, $this );
	}

	/**
	 * Check if locations exists
	 *
	 * @return boolean [description]
	 */
	public function has_locations() {
		return $this->form['has_locations'] ? true : false;
	}

	/**
	 * The object loop.
	 *
	 * To be used when displaying map only without the results.
	 *
	 * In that case we need to run the loop in order to collect some data for the map.
	 *
	 * @since 4.0
	 *
	 * @param boolean $include true || false if to include the single-result.php template file. That is mainly to be used with deprecated file of the AJAX forms extension.
	 */
	public function object_loop( $include = false ) {} // phpcs:ignore.

	/**
	 * Get template files and enqueue stylesheets and custom CSS.
	 *
	 * @since 4.0
	 *
	 * @param  string $template_type 'search-form' || 'search-results'.
	 *
	 * @param  string $template_name template name.
	 *
	 * @param  string $file_name     name of the file to include.
	 *
	 * @return [type]                [description]
	 */
	public function get_template_file( $template_type = 'search_form', $template_name = '', $file_name = 'content.php' ) {

		$folder_name = '';
		$type        = '';

		if ( 'search_form' === $template_type ) {

			$type          = 'form';
			$folder_name   = 'search-forms';
			$template_name = '' !== $template_name ? $template_name : $this->form['search_form']['form_template'];

		} elseif ( 'search_results' === $template_type ) {

			$type          = 'results';
			$folder_name   = 'search-results';
			$template_name = '' !== $template_name ? $template_name : $this->form['search_results']['results_template'];
		}

		$args = array(
			'component'     => $this->form['component'],
			'addon'         => $this->form['addon'],
			'folder_name'   => $folder_name,
			'template_name' => $template_name,
			'file_name'     => $file_name,
		);

		$template = gmw_get_template( $args );

		// enqueue stylesheet if not already enqueued.
		if ( ! wp_style_is( $template['stylesheet_handle'], 'enqueued' ) && file_exists( $template['stylesheet_path'] ) ) {
			wp_register_style( $template['stylesheet_handle'], $template['stylesheet_uri'], array( 'gmw-frontend' ), GMW_VERSION, false );
			wp_enqueue_style( $template['stylesheet_handle'] );
		}

		// Add custom CSS as inline script.
		if ( ! empty( $this->form[ 'search_' . $type ]['styles']['custom_css'] ) ) {

			// Needed when registering an inline style.
			if ( ! wp_style_is( $template['stylesheet_handle'], 'enqueued' ) ) {
				wp_register_style( $template['stylesheet_handle'], false ); // phpcs:ignore.
				wp_enqueue_style( $template['stylesheet_handle'] );
			}

			wp_add_inline_style( $template['stylesheet_handle'], $this->form[ 'search_' . $type ]['styles']['custom_css'] );
		}

		if ( 'results' === $type && ! empty( $this->form['search_results']['results_view']['grid_columns'] ) ) {

			$template_name   = 'gmw-template-' . str_replace( 'custom_', '', $this->form['search_results']['results_template'] );
			$grid_column_css = '.' . $template_name . ' {--gmw-results-grid-col: ' . absint( $this->form['search_results']['results_view']['grid_columns'] ) . ';}';

			// Needed when registering an inline style.
			if ( ! wp_style_is( $template['stylesheet_handle'], 'enqueued' ) ) {
				wp_register_style( $template['stylesheet_handle'], false ); // phpcs:ignore.
				wp_enqueue_style( $template['stylesheet_handle'] );
			}

			wp_add_inline_style( $template['stylesheet_handle'], $grid_column_css );
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
	public function search_results() {

		if ( ! $this->show_results || ! gmw_verify_template_file_requirement( $this->form['search_results']['results_template'] ) ) {
			return;
		}

		// if locations found.
		do_action( 'gmw_have_locations_start', $this->form, $this );
		do_action( 'gmw_have_' . $this->form['prefix'] . '_locations_start', $this->form, $this );

		// generate no results message.
		if ( ! $this->form['has_locations'] ) {
			$this->form['no_results_message'] = $this->no_results_message();
		} else {
			$this->form['results_message'] = $this->get_results_message();
		}

		// phpcs:disable.
		// These are being used in the results template files.
		$gmw       = $this->form;
		$gmw_form  = $this;
		$gmw_query = $this->query;
		// phpcs:enables.

		// Maybe already generated somewhere else.
		if ( empty( $this->results_template ) ) {
			$this->results_template = $this->get_template_file( 'search_results' );
		}

		// temporary to support older versions of the plugin.
		// This global should now live at the begining of the results template file.
		global $members_template, $groups_template;

		$bp_class_ok = false;

		// Temporary solution to add the #buddypress wrapping element to custom results template files.
		if ( apply_filters( 'gmw_search_results_buddypress_id_attr_enabled', true ) ) {

			if ( 'members_locator' === $this->form['component'] && ! empty( $this->results_template['is_custom'] ) ) {

				$bp_class_ok = true;

				echo '<div id="buddypress">';
			}
		}

		include $this->results_template['content_path'];

		if ( $bp_class_ok ) {
			echo '</div>';
		}

		// Reset query for posts locator queries.
		if ( 'posts_locator' === $this->form['component'] ) {
			wp_reset_query();
		}

		do_action( 'gmw_have_locations_end', $this->form, $this );
		do_action( 'gmw_have_' . $this->form['prefix'] . '_locations_end', $this->form, $this );
	}

	/**
	 * Generate the map element on the page.
	 */
	public function map() {

		$args = array(
			'map_id'                  => $this->form['ID'],
			'prefix'                  => $this->form['prefix'],
			'map_type'                => $this->form['addon'],
			'map_width'               => $this->form['results_map']['map_width'],
			'map_height'              => $this->form['results_map']['map_height'],
			'boundaries_filter'       => ! empty( $this->form['results_map']['boundaries_filter']['usage'] ) ? $this->form['results_map']['boundaries_filter']['usage'] : 'disabled',
			'boundaries_filter_label' => ! empty( $this->form['results_map']['boundaries_filter']['label'] ) ? $this->form['results_map']['boundaries_filter']['label'] : '',
			'expand_on_load'          => ! empty( $this->form['results_map']['expand_on_load'] ) ? true : false,
		);

		echo gmw_get_map_element( $args, $this->form ); // phpcs:ignore: XSS ok.
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
		 * New template files should have the action hook 'gmw_the_object_location'.
		 *
		 * @TODO To be removed.
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
			$location = gmw_get_object_map_location( $object, $iw_args, $this->form );

			if ( $location ) {
				$this->map_locations[] = $location;
			}
		}

		$object = apply_filters( 'gmw_form_the_location', $object, $this->form, $this );
	}

	/**
	 * For AJAX submissions.
	 *
	 * Use this method in child class to performe the search query and return the required data that will then pass via wp_send_json() to the JavaScript file.
	 *
	 * @since 4.0
	 *
	 * @return [type] [description]
	 */
	public function get_json_data() {
		return array();
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

	/**
	 * Initiate GEO my WP form during AJAX form submission.
	 *
	 * @since 4.0
	 */
	public static function ajax_submission() {

		// deprecated filters.
		do_action( 'gmw_ajaxfms_form_submission', $_POST ); // phpcs:ignore: CSRF ok.
		do_action( 'wp_ajax_gmw_ajax_forms_form_submission' );
		do_action( 'wp_ajax_nopriv_gmw_ajax_forms_form_submission' );

		if ( ! isset( $_POST['form_id'] ) ) { // phpcs:ignore: CSRF ok.
			die( 'GEO my WP form ID is missing' );
		}

		// New filter.
		do_action( 'gmw_pre_ajax_form_sumission' ); // phpcs:ignore: CSRF ok.

		$form_object = static::init( absint( $_POST['form_id'] ) ); // phpcs:ignore: CSRF ok.

		if ( empty( $form_object ) || ! is_object( $form_object ) ) {
			die( $form_object ); // phpcs:ignore: XSS ok.
		}

		$json_data = $form_object->get_json_data();

		$form_object->send_json( $json_data );
	}
}
add_action( 'wp_ajax_gmw_form_ajax_submission', array( 'GMW_Form_Core', 'ajax_submission' ), 10 );
add_action( 'wp_ajax_nopriv_gmw_form_ajax_submission', array( 'GMW_Form_Core', 'ajax_submission' ), 10 );
