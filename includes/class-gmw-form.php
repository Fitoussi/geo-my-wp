<?php
/**
 * GEO my WP Form class.
 *
 * Generates the proximity search forms.
 *
 * This class should be extended for different object types.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_Form_Init class
 *
 * Create the form object and display its elements
 *
 * @since 2.6.1
 *
 * @author FitoussiEyal
 */
class GMW_Form {

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
	 * The form being displayed
	 *
	 * @since 2.6.1
	 *
	 * @var array
	 *
	 * @access public
	 */
	public $form;

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
	 * Object permalink modifier filter.
	 *
	 * Pass the filter hook that is being used to modify the permalink of the object.
	 *
	 * This will be used to modify each permalink in the results loop and append the address to it so it
	 *
	 * could be used when viwing a single object page.
	 *
	 * for example the filter for the post permalink is 'the_permalink'
	 *
	 * @var boolean | string
	 */
	public $object_permalink_hook = false;

	/**
	 * Address Filters.
	 *
	 * @var boolean
	 */
	public $address_filter = false;

	/**
	 * Locations table.
	 *
	 * @var string
	 */
	protected $locations_table = 'gmw_locations';

	/**
	 * Locationmeta table.
	 *
	 * @var string
	 */
	protected $location_meta_table = 'gmw_locationmeta';

	/**
	 * Array of arguments that can be used to cache queries using.
	 *
	 * GMW internal cache system.
	 *
	 * @var array
	 */
	public $query_cache_args = array();

	/**
	 * The gmw_location database fields that will be pulled in the search query
	 *
	 * The fields can be modified using the filter 'gmw_location_query_db_fields'
	 *
	 * @var array
	 */
	public $db_fields = array(
		// '',
		'ID as location_id',
		'object_type',
		'object_id',
		'title as location_name',
		'user_id',
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

	/**
	 * $objects_id
	 *
	 * Holder for the object IDs of the locations pulled from
	 *
	 * GEO my WP DB
	 *
	 * @var array
	 */
	public $objects_id = array();

	/**
	 * To be used with pagination and per page features
	 *
	 * @var string
	 */
	public $paged_name = 'paged';

	/**
	 * Show / hide location without location from search results
	 *
	 * @var boolean
	 */
	public $enable_objects_without_location = true;

	/**
	 * $locations_data
	 *
	 * Holder for the locations data pulled from GEO my WP DB
	 *
	 * @var array
	 */
	public $locations_data = array();

	/**
	 * $map_locations
	 *
	 * Holder for the location data that will pass to the map generator
	 *
	 * @var array
	 */
	public $map_locations = array();

	/**
	 * The holder for the search query.
	 *
	 * @var array|object
	 */
	public $query = array();

	/**
	 * Results message
	 *
	 * @var array
	 */
	public function results_message_placeholders() {
		return array(
			'count_message'    => __( 'Showing {from_count} - {to_count} of {total_results} locations', 'geo-my-wp' ),
			'location_message' => __( ' within {radius}{units} from {address}', 'geo-my-wp' ),
		);
	}

	/**
	 * [$show_results description]
	 *
	 * @var boolean
	 */
	public $show_results = false;

	/**
	 * Trigger map once the shortcode done
	 *
	 * @var boolean
	 */
	protected $render_map = false;

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
			'image'           => false,
			'directions_link' => true,
			'address'         => true,
			'distance'        => true,
			'location_meta'   => false,
		);
	}

	/**
	 * Create a custom search query in child class to filter the search results based on location.
	 *
	 * As an example you can look in the file: geo-my-wp/plugins/posts-locator/includes/class-posts-locator-form.php
	 *
	 * @return [type] [description]
	 */
	public function search_query() {
		return array();
	}

	/**
	 * Do or output something before the search results.
	 */
	public function before_search_results() {}

	/**
	 * Do or output something after the search results.
	 */
	public function after_search_results() {}

	/**
	 * __construct
	 *
	 * Verify some data and generate default values.
	 *
	 * @param array $form the form being processed.
	 */
	public function __construct( $form ) {

		$this->form = $form;

		/**
		 * Get current form element ( form, map, results... )
		// $this->form['current_element'] = key( $attr );
		// set form="results" as search results element
		// if ( isset( $attr['form'] ) && $attr['form'] == 'results' ) {
		// $this->form['current_element'] = 'search_results';
		// }
		*/

		// verify that the form element is lagit.
		if ( ! in_array( $this->form['current_element'], $this->allowed_form_elements, true ) ) {

			$this->element_allowed = false;

			$message = sprintf(
				/* translators: %s replaced with form type */
				__( '%s is invalid form type.', 'geo-my-wp' ),
				$this->form['current_element']
			);

			return gmw_trigger_error( $message );
		}

		// get from default values.
		$this->setup_defaults();
	}

	/**
	 * Verify default form args
	 */
	public function setup_defaults() {

		$this->ID     = $this->form['ID'];
		$this->prefix = $this->form['prefix'];
		$this->url_px = gmw_get_url_prefix();

		// Get current page slug. Home page and singular slug is different than other pages.
		$page_name = ( is_front_page() || is_single() ) ? 'page' : $this->paged_name;

		$this->form['elements']         = ! empty( $this->form['params']['elements'] ) ? explode( ',', $this->form['params']['elements'] ) : array();
		$this->form['submitted']        = false;
		$this->form['page_load_action'] = false;
		$this->form['form_values']      = array();
		$this->form['lat']              = false;
		$this->form['lng']              = false;
		$this->form['address']          = false;
		$this->form['org_address']      = false; // deprecated, use "address" instead.
		$this->form['get_per_page']     = false;
		$this->form['units_array']      = false;
		$this->form['radius']           = false;
		$this->form['map_enabled']      = false;
		$this->form['map_usage']        = 'results';
		$this->form['display_list']     = false;
		$this->form['paged_name']       = $page_name;
		$this->form['paged']            = get_query_var( $page_name ) ? get_query_var( $page_name ) : 1;
		$this->form['per_page']         = -1;
		$this->form['has_locations']    = false;
		$this->form['results']          = array();
		$this->form['results_count']    = 0;
		$this->form['total_results']    = 0;
		$this->form['max_pages']        = 0;
		$this->form['in_widget']        = ! empty( $this->form['params']['widget'] ) ? true : false;
		$this->form['modify_permalink'] = 1;
		$this->form['address_filters']  = array();

		// check if form submitted.
		if ( isset( $_GET[ $this->url_px . 'form' ] ) && isset( $_GET[ $this->url_px . 'action' ] ) && 'fs' === $_GET[ $this->url_px . 'action' ] ) { // WPCS: CSRF ok.

			$this->form['submitted']    = true;
			$this->form['form_values']  = $this->get_form_values();
			$this->form['map_enabled']  = '' === $this->form['form_submission']['display_map'] ? false : true;
			$this->form['map_usage']    = $this->form['form_submission']['display_map'];
			$this->form['display_list'] = $this->form['form_submission']['display_results'];

			// otherwise check if page load results is set.
		} elseif ( $this->form['page_load_results']['enabled'] ) {

			$this->form['page_load_action'] = true;
			$this->form['form_values']      = $this->get_form_values();
			$this->form['map_enabled']      = '' === $this->form['page_load_results']['display_map'] ? false : true;
			$this->form['map_usage']        = $this->form['page_load_results']['display_map'];
			$this->form['display_list']     = $this->form['page_load_results']['display_results'];
		}

		// for older version. to prevent PHP warnings.
		$this->form['search_results']['results_page'] = $this->form['form_submission']['results_page'];
		$this->form['search_results']['display_map']  = $this->form['map_usage'];

		/* temporary to support previous version of template files ( will be removed ) */
		if ( function_exists( 'gmw_3_deprecated_form_settings' ) ) {
			$this->form = gmw_3_deprecated_form_settings( $this->form );
		}
		/* End deprecated */

		$this->enable_objects_without_location = apply_filters( 'gmw_form_enable_objects_without_location', $this->enable_objects_without_location, $this->form, $this );

		$this->db_fields = apply_filters( 'gmw_form_db_fields', $this->db_fields, $this->form, $this );

		// can modify form values.
		$this->form = apply_filters( 'gmw_default_form_values', $this->form, $this );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_default_form_values", $this->form, $this );
	}

	/**
	 * Get submitted form values from URL
	 *
	 * @return [type] [description]
	 */
	public function get_form_values() {
		$qs = isset( $_SERVER['QUERY_STRING'] ) ? wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // WPCS: CSRF ok, sanitization ok.
		return gmw_get_form_values( $this->url_px, wp_unslash( $qs ) );
	}

	/**
	 * Get search results page
	 *
	 * @return [type] [description]
	 */
	public function get_results_page() {

		// if already contains URL do nothing.
		if ( ! empty( $this->form['form_submission']['results_page'] ) && strpos( $this->form['form_submission']['results_page'], 'http' ) !== false ) {

			return $this->form['form_submission']['results_page'];
		}

		// if this is page ID.
		if ( ! empty( $this->form['form_submission']['results_page'] ) ) {

			return get_permalink( $this->form['form_submission']['results_page'] );
		}

		// if no page ID set and its in widget, get the results page from settings page.
		if ( $this->form['in_widget'] ) {

			$this->form['form_submission']['results_page'] = get_permalink( GMW()->options['general_settings']['results_page'] );

			return $this->form['form_submission']['results_page'];
		}

		// otherwise false.
		return false;
	}

	/**
	 * Display search form
	 *
	 * @return void
	 */
	public function search_form() {

		// enable/disable form filter.
		if ( apply_filters( "gmw_{$this->form['ID']}_disable_search_form", false, $this ) ) {
			return;
		}

		// verify search form tempalte.
		if ( empty( $this->form['search_form']['form_template'] ) || '-1' === $this->form['search_form']['form_template'] || 'no_form' === $this->form['search_form']['form_template'] ) {
			return;
		}

		// get search form template files.
		$search_form = gmw_get_search_form_template( $this->form['component'], $this->form['search_form']['form_template'], $this->form['addon'] );

		// enqueue style only once.
		if ( ! wp_style_is( $search_form['stylesheet_handle'], 'enqueued' ) ) {
			wp_enqueue_style( $search_form['stylesheet_handle'], $search_form['stylesheet_uri'], array( 'gmw-frontend' ), GMW_VERSION );
		}

		// temporary for older versions. This function should be used in the search form.
		$this->form['form_submission']['results_page'] = $this->get_results_page();

		// to support older versions of search form tempalte files.
		$this->form['search_results']['results_page'] = $this->form['form_submission']['results_page'];

		do_action( 'gmw_before_search_form', $this->form, $this );
		do_action( "gmw_{$this->form['prefix']}_before_search_form", $this->form, $this );

		$gmw      = $this->form;
		$gmw_form = $this;

		include $search_form['content_path'];

		do_action( 'gmw_after_search_form', $this->form, $this );
		do_action( "gmw_{$this->form['prefix']}_after_search_form", $this->form, $this );

		// load main JavaScript file.
		if ( ! wp_script_is( 'gmw', 'enqueued' ) ) {
			wp_enqueue_script( 'gmw' );
		}
	}

	/**
	 * Display the map anywhere on the page using the shortcode
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
			'init_visible'   => 'shortcode' === $this->form['map_usage'] ? false : true,
		);

		echo GMW_Maps_API::get_map_element( $args ); // WPCS: XSS ok.
	}

	/**
	 * Generate the map element
	 *
	 * @return [type] [description]
	 */
	public function map_element() {

		// disable map dynamically.
		if ( ! apply_filters( 'gmw_trigger_map', true, $this->form, $this ) ) {
			return;
		}

		$iw_type     = ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard';
		$iw_ajax     = ! empty( $this->form['info_window']['ajax_enabled'] ) ? 1 : 0;
		$iw_template = ! empty( $this->form['info_window']['template'][ $iw_type ] ) ? $this->form['info_window']['template'][ $iw_type ] : 'default';

		$map_args = array(
			'map_id'               => $this->form['ID'],
			'map_type'             => $this->form['addon'],
			'prefix'               => $this->form['prefix'],
			'info_window_type'     => $iw_type,
			'info_window_ajax'     => $iw_ajax,
			'info_window_template' => $iw_template,
			'group_markers'        => ! empty( $this->form['map_markers']['grouping'] ) ? $this->form['map_markers']['grouping'] : 'standard',
			'draggable_window'     => isset( $this->form['info_window']['draggable_use'] ) ? true : false,
		);

		$map_options = array(
			'zoom'      => $this->form['results_map']['zoom_level'],
			'mapTypeId' => isset( $this->form['results_map']['map_type'] ) ? $this->form['results_map']['map_type'] : 'ROADMAP',
		);

		$user_position = array(
			'lat'        => $this->form['lat'],
			'lng'        => $this->form['lng'],
			'address'    => $this->form['address'],
			'map_icon'   => GMW()->default_icons['user_location_icon_url'],
			'icon_size'  => null,
			'iw_content' => __( 'Your location', 'geo-my-wp' ),
			'iw_open'    => ! empty( $this->form['results_map']['yl_icon'] ) ? true : false,
		);

		// generate the map.
		return gmw_get_map_object( $map_args, $map_options, $this->map_locations, $user_position, $this->form );
	}

	/**
	 * Verify some data before running the search query
	 *
	 * @return void
	 */
	public function pre_search_query() {

		$this->form = apply_filters( 'gmw_pre_search_query_args', $this->form, $this );

		// run search query on form submission or page load results.
		if ( $this->form['submitted'] || $this->form['page_load_action'] ) {

			// on page load results.
			if ( $this->form['page_load_action'] ) {

				$this->page_load_results();

				// collect values as query cache arguments.
				$this->query_cache_args = $this->form['page_load_results'];

				// Otherwise, on form submission
				// make sure that the form that was submitted is the one we query and display.
			} elseif ( absint( $this->form['ID'] ) === absint( $this->form['form_values']['form'] ) ) {

				$this->form_submission();

				// collect values as query cache arguments.
				$this->query_cache_args = $this->form['form_values'];

				// otherwise abort.
			} else {

				return;
			}

			$this->query_cache_args['user_loggedin']                    = ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) ? 1 : 0;
			$this->query_cache_args['db_fields']                        = $this->db_fields;
			$this->query_cache_args['showing_objects_without_location'] = $this->enable_objects_without_location;

			do_action( 'gmw_form_before_search_query', $this->form, $this );

			// run the search query using child class.
			$results = $this->search_query();

			$this->form['has_locations'] = ! empty( $results ) ? true : false;

			// generate map if needed.
			if ( $this->form['map_enabled'] && ( $this->form['has_locations'] || ! empty( $this->form['results_map']['no_results_enabled'] ) ) ) {
				$this->render_map = true;
			}

			// load main JavaScript and Google APIs.
			if ( ! wp_script_is( 'gmw', 'enqueued' ) ) {
				wp_enqueue_script( 'gmw' );
			}

			$this->show_results = true;

			// Otherwise, do something custom.
		} else {

			do_action( 'gmw_main_shortcode_custom_function', $this->form, $this );
			do_action( 'gmw_' . $this->form['prefix'] . '_main_shortcode_custom_function', $this->form, $this );
		}
	}

	/**
	 * Form_submission
	 *
	 * Generate some data on form submitted
	 *
	 * before search query takes place
	 *
	 * @version 3.0
	 *
	 * @return [type] [description]
	 */
	public function form_submission() {

		// get form values.
		$form_values = $this->form['form_values'];

		$this->form['radius']       = isset( $form_values['distance'] ) ? $form_values['distance'] : 500;
		$this->form['address']      = ( isset( $form_values['address'] ) && array_filter( $form_values['address'] ) ) ? implode( ' ', $form_values['address'] ) : '';
		$this->form['org_address']  = $this->form['address'];
		$per_page                   = isset( $this->form['search_results']['per_page'] ) ? current( explode( ',', $this->form['search_results']['per_page'] ) ) : -1;
		$this->form['get_per_page'] = isset( $form_values['per_page'] ) ? $form_values['per_page'] : $per_page;
		$this->form['units']        = isset( $form_values['units'] ) ? $form_values['units'] : 'imperial';
		$this->form['units_array']  = gmw_get_units_array( $this->form['units'] );

		// Get lat/lng if exist in URL.
		if ( ! empty( $form_values['lat'] ) && ! empty( $form_values['lng'] ) ) {

			$this->form['lat'] = $form_values['lat'];
			$this->form['lng'] = $form_values['lng'];

			// Otherwise look for an address to geocode.
		} elseif ( ! empty( $this->form['address'] ) ) {

			// include geocoder.
			include_once GMW_PATH . '/includes/gmw-geocoder.php';

			if ( function_exists( 'gmw_geocoder' ) ) {
				$this->geocoded_location = gmw_geocoder( $this->form['address'] );
				$this->form['location']  = $this->geocoded_location;
			}

			// if geocode was unsuccessful return error message.
			if ( isset( $this->form['location']['error'] ) ) {

				return;

			} else {

				$this->form['lat'] = $this->form['location']['lat'];
				$this->form['lng'] = $this->form['location']['lng'];
			}
		}

		// filter the form values before running search query.
		$this->form = apply_filters( 'gmw_form_submitted_before_results', $this->form, $this );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_form_submitted_before_results", $this->form, $this );
	}

	/**
	 * Page_load_results
	 *
	 * Generate some data on page load
	 *
	 * before search query takes place
	 *
	 * @version 3.0
	 *
	 * @return [type] [description]
	 */
	public function page_load_results() {

		// get form values.
		$form_values = $this->form['form_values'];

		$page_load_options          = $this->form['page_load_results'];
		$this->form['address']      = '';
		$this->form['org_address']  = '';
		$this->form['get_per_page'] = ! empty( $form_values['per_page'] ) ? $form_values['per_page'] : current( explode( ',', $page_load_options['per_page'] ) );
		$this->form['radius']       = ! empty( $page_load_options['radius'] ) ? $page_load_options['radius'] : 200;
		$this->form['units']        = ! empty( $page_load_options['units'] ) ? $page_load_options['units'] : 'imperial';
		$this->form['units_array']  = gmw_get_units_array( $page_load_options['units'] );

		$user_location = gmw_get_user_current_location();

		// display results based on user's current location.
		if ( ! empty( $page_load_options['user_location'] ) && ! empty( $user_location ) ) {

			// get user's current location.
			$this->form['address'] = $user_location->address;

			// append it to page load results as well to easier add it to query cache args.
			$this->form['lat']                      = $user_location->lat;
			$this->form['lng']                      = $user_location->lng;
			$this->form['page_load_results']['lat'] = $user_location->lat;
			$this->form['page_load_results']['lng'] = $user_location->lng;

			// Otherwise look for an address filter.
		} elseif ( ! empty( $page_load_options['address_filter'] ) ) {

			// get the addres value.
			$this->form['address'] = sanitize_text_field( $page_load_options['address_filter'] );

			// include the geocoder.
			include GMW_PATH . '/includes/gmw-geocoder.php';

			// try to geocode the address.
			if ( function_exists( 'gmw_geocoder' ) ) {
				$this->form['location'] = gmw_geocoder( $this->form['address'] );
			}

			// if geocode was unsuccessful return error message.
			if ( isset( $this->form['location']['error'] ) ) {

				return false;

			} else {

				// append it to page load results as well to easier add it to query cache args.
				$this->form['lat']                      = $this->form['location']['lat'];
				$this->form['lng']                      = $this->form['location']['lng'];
				$this->form['page_load_results']['lat'] = $this->form['location']['lat'];
				$this->form['page_load_results']['lng'] = $this->form['location']['lng'];
			}
		}

		// filter the form value before query.
		$this->form = apply_filters( 'gmw_page_load_results_before_results', $this->form, $this );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_page_load_results_before_results", $this->form, $this );
	}

	/**
	 * Get address fields to filter the search query
	 *
	 * @since 3.0
	 *
	 * @return [type] [description]
	 */
	public function get_address_filters() {

		$this->form['address_filters'] = array();

		// if on page load results.
		if ( $this->form['page_load_action'] ) {

			if ( ! empty( $this->form['page_load_results']['city_filter'] ) ) {
				$this->form['address_filters']['city'] = $this->form['page_load_results']['city_filter'];
			}

			if ( ! empty( $this->form['page_load_results']['state_filter'] ) ) {
				$this->form['address_filters']['region_name'] = $this->form['page_load_results']['state_filter'];
			}

			if ( ! empty( $this->form['page_load_results']['zipcode_filter'] ) ) {
				$this->form['address_filters']['postcode'] = $this->form['page_load_results']['zipcode_filter'];
			}

			if ( ! empty( $this->form['page_load_results']['country_filter'] ) ) {
				$this->form['address_filters']['country_code'] = $this->form['page_load_results']['country_filter'];
			}
		}

		// if searching within state or country only is enabled.
		if ( apply_filters( 'gmw_search_within_boundaries', true, $this->form, $this ) && $this->form['submitted'] ) {

			// if searching state boundaries.
			if ( isset( $this->form['form_values']['state'] ) && '' !== $this->form['form_values']['state'] ) {
				$this->form['address_filters']['region_name'] = $this->form['form_values']['state'];
			}

			// When searchin boundaries of a country.
			if ( isset( $this->form['form_values']['country'] ) && '' !== $this->form['form_values']['country'] ) {
				$this->form['address_filters']['country_code'] = $this->form['form_values']['country'];
			}
		}

		return $this->form['address_filters'];
	}

	/**
	 * Prepare data before quering locations
	 *
	 * @param array $object__in array of object ids to include.
	 *
	 * @return [type] [description]
	 */
	public function pre_get_locations_data( $object__in = '' ) {

		$args = array(
			'object_type' => $this->form['object_type'],
			'lat'         => $this->form['lat'],
			'lng'         => $this->form['lng'],
			'radius'      => ! empty( $this->form['radius'] ) ? $this->form['radius'] : false,
			'units'       => $this->form['units_array']['units'],
			'object__in'  => $object__in,
			'db_fields'   => $this->db_fields,
		);

		// address filters.
		$address_filters = $this->get_address_filters();

		$location_meta = ! empty( $this->form['search_results']['location_meta'] ) ? $this->form['search_results']['location_meta'] : false;

		// query locations from database.
		$locations = GMW_Location::get_locations_data( $args, $address_filters, $location_meta, $this->locations_table, $this->db_fields, $this->form );

		// get locations data.
		if ( ! empty( $locations ) ) {
			$this->locations_data = $locations['locations_data'];
			$this->objects_id     = $locations['objects_id'];
			$this->featured_ids   = $locations['featured_ids'];
		}

		return $this->objects_id;
	}

	/**
	 * Append the address, coords distance and units to the permalink of the locaiton in the loop
	 *
	 * This information can be useful when viwing an sinlge object page ( post, member... ) linked from the search results.
	 *
	 * We can use this data to display it on the map or calculate directions and so on.
	 *
	 * @param  string $url the original URL.
	 *
	 * @return string modified URL with address
	 */
	public function append_address_to_permalink( $url ) {

		// abort if no address.
		if ( empty( $this->form['address'] ) ) {
			return $url;
		}

		// get the permalink args.
		$url_args = array(
			'address' => str_replace( ' ', '+', $this->form['address'] ),
			'lat'     => $this->form['lat'],
			'lng'     => $this->form['lng'],
		);

		// append the address to the permalink.
		return esc_url( apply_filters( "gmw_{$this->form['prefix']}_location_permalink", $url . '?' . http_build_query( $url_args ), $url, $url_args ) );
	}

	/**
	 * Generate location data to pass to the map.
	 *
	 * Array contains latitude, longitude, map icon and info window content.
	 *
	 * @param object  $object the object data.
	 *
	 * @param boolean $info_window if in.
	 *
	 * @return array
	 */
	public function get_map_location( $object, $info_window = false ) {

		// allow disabling info window data. If using AJAX for example.
		if ( apply_filters( 'gmw_form_get_info_window_content', true, $this->form, $this ) ) {
			$info_window = gmw_get_info_window_content( $object, $this->get_info_window_args( $object ), $this->form );
		}

		// enable number icons. It is now disabled by default.
		if ( apply_filters( 'gmw_form_enable_numbered_map_icons', false, $this->form, $this ) ) {

			$map_icon  = isset( $object->location_count ) ? 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' . $object->location_count . '|FF776B|000000' : '';
			$icon_size = array( 21, 34 );

		} else {

			$map_icon  = isset( $object->map_icon ) ? $object->map_icon : GMW()->default_icons['location_icon_url'];
			$icon_size = null;
		}

		$args = apply_filters(
			'gmw_form_map_location_args',
			array(
				'ID'                  => $object->object_id,
				'location_id'         => $object->location_id,
				'object_id'           => $object->object_id,
				'object_type'         => $object->object_type,
				'lat'                 => $object->lat,
				'lng'                 => $object->lng,
				'distance'            => isset( $object->distance ) ? $object->distance : null,
				'units'               => isset( $object->units ) ? $object->units : null,
				'map_icon'            => apply_filters( 'gmw_' . $this->form['prefix'] . '_map_icon', $map_icon, $object, $this->form, $this ),
				'icon_size'           => $icon_size,
				'info_window_content' => $info_window,
			),
			$object,
			$this->form,
			$this
		);

		return apply_filters( 'gmw_' . $this->form['prefix'] . '_form_map_location_args', $args, $object, $this->form, $this );
	}

	/**
	 * The location
	 *
	 * This method must run within the object results loop. It could be posts loop, members loop and so on.
	 *
	 * This method provides features attached to each location/object in the loop
	 *
	 * You need to call it using parent::the_location( $object ) in your child class.
	 *
	 * @param integer $object_id the object ID.
	 *
	 * @param object  $object the object data.
	 *
	 * @return $object modified object
	 */
	public function the_location( $object_id, $object ) {

		// setup class tag.
		$object->location_class  = 'single-' . $this->form['object_type'];
		$object->location_class .= ' gmw-single-item gmw-single-' . $this->form['object_type'] . ' gmw-object-' . $object->object_id . ' gmw-location-' . $object->location_id;

		// check if this is first location in the loop.
		if ( empty( $this->form['location_count'] ) ) {

			// temporary fix for page load results when setting per page to -1.
			if ( -1 === $this->form['get_per_page'] ) {
				$this->form['get_per_page'] = 1;
			}

			// count loop to be able to set the last location at the end of this function.
			$this->form['loop_count']     = 1;
			$this->form['location_count'] = 1;

			if ( 1 !== absint( $this->form['paged'] ) ) {
				$this->form['location_count'] = ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) ) + 1;
			}

			$this->form['location_count'] = (int) $this->form['location_count'];

			$object = apply_filters( 'gmw_form_the_location_first', $object, $this->form, $this );

			$object->location_class .= ' first-location';

		} else {

			// increase count.
			$this->form['loop_count']++;
			$this->form['location_count']++;
		}

		// location count to display in map markers and list of results.
		$object->location_count = $this->form['location_count'];

		// if location exists, merge it with the object.
		if ( isset( $this->locations_data[ $object_id ] ) ) {

			$location = $this->locations_data[ $object_id ];

			foreach ( $location as $key => $value ) {
				// add location data into object.
				$object->$key = $value;
			}
		}

		if ( isset( $object->location_id ) ) {

			if ( isset( $object->featured_location ) && 1 === $object->featured_location ) {
				$object->location_class .= ' gmw-featured-location';
			}

			// append address to each permalink in the loop.
			if ( apply_filters( 'gmw_append_address_to_permalink', false, $object->object_type, $this ) && ! empty( $this->object_permalink_hook ) ) {
				add_filter( $this->object_permalink_hook, array( $this, 'append_address_to_permalink' ) );
			}

			// get location meta from database if needed.
			if ( ! empty( $this->form['search_results']['location_meta'] ) ) {
				$object->location_meta = gmw_get_location_meta( $object->location_id, $this->form['search_results']['location_meta'] );
			}

			// if map enabled, collect some data to pass to the map script.
			if ( $this->form['map_enabled'] ) {

				/**
				// $info_window = false;
				// allow disabling info window data. If using AJAX for example.
				// if ( apply_filters( 'gmw_form_get_info_window_content', true, $this->form, $this ) ) {
				// $info_window = gmw_get_info_window_content( $object, $this->get_info_window_args( $object ), $this->form );
				// }
				*/
				$this->map_locations[] = $this->get_map_location( $object, false );
			}
		}

		// check if last location in the loop.
		if ( absint( $this->form['loop_count'] ) === absint( $this->form['results_count'] ) ) {

			$object->location_class .= ' last-location';

			// filter the location when loop ends.
			$object = apply_filters( 'gmw_form_the_location_last', $object, $this->form, $this );

			// unset loop count. We don't need it outside the loop.
			unset( $this->form['loop_count'] );
		}

		// filter each location in the loop.
		$object = apply_filters( 'gmw_form_the_location', $object, $this->form, $this );

		return $object;
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
	 * Display the search results.
	 *
	 * @return void
	 */
	public function search_results() {

		if ( ! $this->show_results ) {
			return;
		}

		// get results template file.
		$results_template = gmw_get_search_results_template( $this->form['component'], $this->form['search_results']['results_template'], $this->form['addon'] );

		// enqueue stylesheet if not already enqueued.
		if ( ! wp_style_is( $results_template['stylesheet_handle'], 'enqueued' ) ) {
			wp_enqueue_style( $results_template['stylesheet_handle'], $results_template['stylesheet_uri'] );
		}

		$this->before_search_results();

		// if locations found.
		do_action( 'gmw_have_locations_start', $this->form, $this );
		do_action( 'gmw_have_' . $this->form['prefix'] . '_locations_start', $this->form, $this );

		// generate no results message.
		if ( ! $this->form['has_locations'] ) {
			$this->form['no_results_message'] = $this->no_results_message();
		} else {
			$this->form['results_message'] = $this->results_message();
		}

		$gmw       = $this->form;
		$gmw_form  = $this;
		$gmw_query = $this->query;

		// temporary to support older versions of the plugin.
		// This global should now be at the begining of the results template file.
		global $members_template, $groups_template;

		include $results_template['content_path'];

		do_action( 'gmw_have_locations_end', $this->form, $this );
		do_action( 'gmw_have_' . $this->form['prefix'] . '_locations_end', $this->form, $this );

		$this->after_search_results();
	}

	/**
	 * Generate results message.
	 *
	 * @return [type] [description]
	 */
	public function results_message() {

		$message = $this->results_message_placeholders();

		$args = array(
			'page'             => $this->form['paged'],
			'per_page'         => absint( $this->form['get_per_page'] ),
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
	 * Generate "No results" message
	 *
	 * @return [type] [description]
	 */
	public function no_results_message() {

		// display geocoder error if failed. Otherwise, show no results message.
		if ( ! empty( $this->form['location']['error'] ) ) {

			$message = $this->form['location']['error'];

		} elseif ( empty( $message ) ) {

			$message = __( 'No results found', 'geo-my-wp' );
		}

		return $message;
	}

	/**
	 * Display the form elements.
	 *
	 * @return void
	 */
	public function output() {

		// do something before the output.
		do_action( 'gmw_shortcode_start', $this->form, $this );
		do_action( "gmw_{$this->form['prefix']}_shortcode_start", $this->form, $this );

		// if using the "elements" shortcode attribute to display the form.
		if ( 'form' === $this->form['current_element'] && ! empty( $this->form['elements'] ) ) {

			if ( in_array( 'map', $this->form['elements'], true ) ) {
				$this->form['map_usage'] = 'shortcode';
			}

			if ( in_array( 'search_results', $this->form['elements'], true ) ) {
				$this->form['display_list'] = true;
			} else {
				$this->form['display_list'] = false;
			}

			// loop through and generate the elements.
			foreach ( $this->form['elements'] as $element ) {

				if ( ! in_array( $element, array( 'search_form', 'map', 'search_results' ), true ) ) {
					continue;
				}

				if ( method_exists( $this, $element ) ) {

					if ( 'search_results' === $element || ( 'map' === $element && ! $this->form['display_list'] ) ) {
						$this->pre_search_query();
					}

					$this->$element();
				}
			}

			// otherwise, generate in normal order.
		} else {

			// display search form.
			if ( 'search_form' === $this->form['current_element'] || 'form' === $this->form['current_element'] ) {
				$this->search_form();
			}

			// display map using shortcode.
			if ( 'map' === $this->form['current_element'] && 'shortcode' === $this->form['map_usage'] ) {

				$this->map();

				if ( ! $this->form['display_list'] ) {
					$this->pre_search_query();
				}
			}

			// display search results.
			if ( $this->form['display_list'] && in_array( $this->form['current_element'], array( 'form', 'search_results' ), true ) ) {

				$this->pre_search_query();

				if ( $this->show_results ) {
					$this->search_results();
				}
			}
		}

		if ( $this->render_map ) {
			$this->map_element();
		}

		// do something after the output.
		do_action( 'gmw_shortcode_end', $this->form, $this );
		do_action( "gmw_{$this->form['prefix']}_shortcode_end", $this->form, $this );
	}
}
