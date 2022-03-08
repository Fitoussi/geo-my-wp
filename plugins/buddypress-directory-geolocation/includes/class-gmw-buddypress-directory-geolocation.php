<?php
/**
 * GEO my WP BuddyPress Directory Geolocation class.
 *
 * Base class to integrate geolocation with the directory pages of BuddyPress.
 *
 * @author Eyal Fitoussi
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_BuddyPress_Directory_Geolocation class.
 *
 * @since 4.0.
 */
class GMW_BuddyPress_Directory_Geolocation {

	/**
	 * Prefix.
	 *
	 * @var string
	 */
	public $prefix = 'bpmdg';

	/**
	 * Component.
	 *
	 * @var string
	 */
	public $component = 'member';

	/**
	 * Page options/settings.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Gmw_location database fields that will be pulled in the search query
	 *
	 * The fields can be modified using the filter 'gmw_database_fields'
	 *
	 * @var array
	 */
	public $db_fields = array(
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

	/**
	 * Form values.
	 *
	 * @var array
	 */
	public $form = array(
		'prefix'   => '',
		'address'  => '',
		'lat'      => '',
		'lng'      => '',
		'distance' => '',
		'units'    => 'imperial',
	);

	/**
	 * GEO my WP locations.
	 *
	 * @var array
	 */
	public $locations = array();

	/**
	 * Map locations holder
	 *
	 * @var array
	 */
	public $map_locations = array();

	/**
	 * Is BP using the Nouveau template pack?
	 *
	 * @var boolean
	 */
	public $is_bp_nouveau = false;

	/**
	 * Doing ajax?
	 *
	 * @var boolean
	 */
	public $doing_ajax = false;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Get options.
		$this->options = $this->get_options();

		// Allow filtering the options.
		$this->options = apply_filters( 'gmw_' . $this->prefix . '_form_options', $this->options, $this );

		// abort if options disabled.
		if ( empty( $this->options['enabled'] ) ) {
			return;
		}

		$this->is_bp_nouveau  = function_exists( 'bp_nouveau' ) ? true : false;
		$this->form['prefix'] = $this->prefix;
		$this->form['units']  = 'metric' === $this->options['units'] ? 'metric' : 'imperial';

		// labels.
		$this->labels            = $this->labels();
		$this->doing_ajax        = defined( 'DOING_AJAX' ) ? 1 : 0;
		$this->is_profile_search = ! empty( $_REQUEST['bp_profile_search'] ) ? true : false;
		$this->radius_values     = str_replace( ' ', '', explode( ',', $this->options['radius'] ) );
		$bp_search_values        = array();

		// Look for form values in the BP Profile Search plugin.
		if ( function_exists( 'bps_get_request' ) ) {
			$bp_search_values = bps_get_request( 'search' );
			$bp_search_values = ! empty( $bp_search_values['gmw_bpsgeo_location_gmw_proximity'] ) ? $bp_search_values['gmw_bpsgeo_location_gmw_proximity'] : array();
		}

		// Use BP Profile Search values if exists.
		if ( ! empty( $bp_search_values ) ) {

			$this->form['address'] = ! empty( $bp_search_values['address'] ) ? sanitize_text_field( wp_unslash( $bp_search_values['address'] ) ) : '';
			$this->form['lat']     = ! empty( $bp_search_values['lat'] ) ? sanitize_text_field( wp_unslash( $bp_search_values['lat'] ) ) : '';
			$this->form['lng']     = ! empty( $bp_search_values['lng'] ) ? sanitize_text_field( wp_unslash( $bp_search_values['lng'] ) ) : '';

			if ( 1 === count( $this->radius_values ) ) {
				$this->form['distance'] = end( $this->radius_values );
			} else {
				$this->form['distance'] = ! empty( $bp_search_values['distance'] ) ? sanitize_text_field( wp_unslash( $bp_search_values['distance'] ) ) : '';
			}

			// Otherwise, look in cookies or $_REQUEST.
		} else {

			// get the default addres value from URL if exists.
			if ( $this->doing_ajax && isset( $_REQUEST['address'] ) ) { // WPCS: CSRF ok.

				$this->form['address'] = sanitize_text_field( wp_unslash( $_REQUEST['address'] ) ); // WPCS: CSRF ok.

				// otherwise, check in cookies.
			} elseif ( isset( $_COOKIE[ 'gmw_' . $this->prefix . '_address' ] ) && 'undefined' !== $_COOKIE[ 'gmw_' . $this->prefix . '_address' ] ) {

				$this->form['address'] = urldecode( wp_unslash( $_COOKIE[ 'gmw_' . $this->prefix . '_address' ] ) ); // WPCS: sanitization ok.
			}

			// get the default latitude value from URL if exists.
			if ( ! $this->doing_ajax && isset( $_REQUEST['lat'] ) ) { // WPCS: CSRF ok.

				$this->form['lat'] = sanitize_text_field( wp_unslash( $_REQUEST['lat'] ) ); // WPCS: CSRF ok.

			} elseif ( isset( $_COOKIE[ 'gmw_' . $this->prefix . '_lat' ] ) && 'undefined' !== $_COOKIE[ 'gmw_' . $this->prefix . '_lat' ] ) {

				$this->form['lat'] = urldecode( wp_unslash( $_COOKIE[ 'gmw_' . $this->prefix . '_lat' ] ) ); // WPCS: sanitization ok.
			}

			// get the default latitude value from URL if exists.
			if ( ! $this->doing_ajax && isset( $_REQUEST['lng'] ) ) {

				$this->form['lng'] = sanitize_text_field( wp_unslash( $_REQUEST['lng'] ) ); // WPCS: CSRF ok.

			} elseif ( isset( $_COOKIE[ 'gmw_' . $this->prefix . '_lng' ] ) && 'undefined' !== $_COOKIE[ 'gmw_' . $this->prefix . '_lng' ] ) {

				$this->form['lng'] = urldecode( wp_unslash( $_COOKIE[ 'gmw_' . $this->prefix . '_lng' ] ) ); // WPCS: sanitization ok.
			}

			// if single, default value get it from the options.
			if ( 1 === count( $this->radius_values ) ) {

				$this->form['distance'] = end( $this->radius_values );

				// check in URL if exists.
			} elseif ( ! $this->doing_ajax && ! empty( $_REQUEST['distance'] ) ) { // WPCS: CSRF ok.

				$this->form['distance'] = sanitize_text_field( wp_unslash( $_REQUEST['distance'] ) ); // WPCS: CSRF ok.

				// otherwise, maybe in cookies.
			} elseif ( isset( $_COOKIE[ 'gmw_' . $this->prefix . '_radius' ] ) && 'undefined' !== $_COOKIE[ 'gmw_' . $this->prefix . '_radius' ] ) {
				$this->form['distance'] = urldecode( wp_unslash( $_COOKIE[ 'gmw_' . $this->prefix . '_radius' ] ) ); // WPCS: sanitization ok.
			}
		}

		$this->form = apply_filters( 'gmw_' . $this->prefix . '_form_data', $this->form, $this );

		// action hooks / filters.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add "Distance" to the order by filter.
		add_action( 'bp_' . $this->component . 's_directory_order_options', array( $this, 'orderby_distance' ), 5 );

		// Modify the search form of the directory.
		add_filter( 'bp_directory_' . $this->component . 's_search_form', array( $this, 'directory_form' ) );

		$this->action_hooks();

		// Modify the hook that is used to generate the location data in the results.
		$results_elements_filter = apply_filters( 'gmw_gl_' . $this->prefix . '_' . $this->component . 's_loop_location_elements_hook', 'bp_directory_' . $this->component . 's_item' );

		// Skip if disabled.
		if ( ! empty( $results_elements_filter ) ) {
			add_action( $results_elements_filter, array( $this, 'add_elements_to_results' ) );
		}

		// enable map.
		if ( ! empty( $this->options['map'] ) ) {

			// Load scripts early when the Nouveau template is enabled.
			if ( $this->is_bp_nouveau ) {
				GMW_Maps_API::load_scripts( array( 'markers_clusterer' ) );
			}

			add_action( 'bp_after_directory_' . $this->component . 's', array( $this, 'map_element' ), 50 );
			add_action( 'bp_after_' . $this->component . 's_loop', array( $this, 'trigger_js_and_map' ) );
		}
	}

	/**
	 * Get plugin options.
	 *
	 * @return [type] [description]
	 */
	public function get_options() {
		return array();
	}

	/**
	 * Run custom queries using action hooks.
	 */
	public function action_hooks() {}

	/**
	 * Can be used to filter address fields using a hook.
	 *
	 * @return [type] [description]
	 */
	public function get_address_filters() {
		return apply_filters( 'gmw_' . $this->prefix . '_get_address_filters', '', $this );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'gmw-bpdg' );
	}

	/**
	 * Labels
	 *
	 * @since 2.6.1
	 *
	 * @return unknown
	 */
	public function labels() {

		$output = apply_filters(
			'gmw_' . $this->prefix . '_labels',
			array(
				'address_label'         => __( 'Address', 'geo-my-wp' ),
				'radius_label'          => __( 'Within', 'geo-my-wp' ),
				'address_placeholder'   => __( 'Enter Address...', 'geo-my-wp' ),
				'miles'                 => __( 'Miles', 'geo-my-wp' ),
				'kilometers'            => __( 'Kilometers', 'geo-my-wp' ),
				'sortby_distance'       => __( 'Distance', 'geo-my-wp' ),
				'get_directions'        => __( 'Get directions', 'geo-my-wp' ),
				'address_error_message' => __( 'The address could not be verified. Try a different address.', 'geo-my-wp' ),
			)
		);

		return $output;
	}

	/**
	 * Add distance option to order by dropdown.
	 */
	public function orderby_distance() {
		if ( apply_filters( 'gmw_' . $this->prefix . '_sortby_distance', true ) ) {
			echo '<option value="distance">' . esc_html( $this->labels['sortby_distance'] ) . '</option>';
		}
	}

	/**
	 * Radius field.
	 *
	 * @param  boolean $profile_search  is this inside the BP Prorfile search form?.
	 *
	 * @return [type]                  [description]
	 */
	public function get_form_address_field( $profile_search = false ) {

		$prefix = esc_attr( $this->prefix );
		$args   = array(
			'id'             => $this->prefix,
			'slug'           => 'address',
			'name'           => 'address',
			'is_array'       => false,
			'type'           => $this->is_bp_nouveau ? 'search' : 'text',
			'label_disabled' => true,
			'placeholder'    => $this->labels['address_placeholder'],
			'value'          => $this->form['address'],
			'class'          => ! empty( $this->options['address_autocomplete'] ) ? 'gmw-address-autocomplete' : '',
			'inner_element'  => false,
			'wrapper_open'   => false,
			'wrapper_close'  => false,
		);

		$wrap_class = '';
		$locator    = '';

		if ( ! empty( $this->options['locator_button'] ) ) {
			$wrap_class = 'class="gmw-locator-button-enabled"';
			$locator    = '<i class="gmw-locator-button inside gmw-icon-target-light" data-locator_submit="1" data-form_id="' . $prefix . '"></i>';
		}

		$field = '';

		if ( $this->is_bp_nouveau && ! $profile_search ) {
			$field .= '<div id="gmw-' . $prefix . '-search-wrapper" class="dir-search ' . esc_attr( $this->component ) . 's-search bp-search">';
			$field .= '<form id="gmw-' . $prefix . '-form" class="bp-dir-search-form">';
		}

		$field .= '<div id="gmw-address-field-' . $prefix . '-wrapper"' . $wrap_class . '">';
		$field .= $locator;
		$field .= gmw_get_form_field( $args, $this->form );
		$field .= '</div>';

		if ( $this->is_bp_nouveau && ! $profile_search ) {
			$field .= '</form>';
			$field .= '</div>';
		}

		return $field;
	}

	/**
	 * Radius field.
	 *
	 * @param  boolean $profile_search  is this inside the BP Prorfile search form?.
	 *
	 * @return [type]                  [description]
	 */
	public function get_form_radius_field( $profile_search = false ) {

		$default_radius   = apply_filters( 'gmw_' . $this->prefix . '_search_form_default_radius_value', '', $this );
		$dropdown_enabled = count( $this->radius_values ) > 1 ? true : false;
		$options          = array();

		if ( $dropdown_enabled ) {

			$options[ $default_radius ] = 'metric' === $this->form['units'] ? $this->labels['kilometers'] : $this->labels['miles'];

			foreach ( $this->radius_values as $value ) {
				$options[ $value ] = $value;
			}
		}

		$args = array(
			'id'             => $this->prefix,
			'slug'           => 'distance',
			'name'           => 'distance',
			'type'           => $dropdown_enabled ? 'select' : 'hidden',
			'label_disabled' => true,
			'value'          => $this->form['distance'],
			'class'          => '',
			'wrapper_close'  => false,
			'wrapper_open'   => false,
			'inner_element'  => false,
			'options'        => $options,
		);

		$field = '';

		if ( $dropdown_enabled ) {

			if ( ! $profile_search ) {
				$nouveau_class = $this->is_bp_nouveau ? 'dir-search ' . esc_attr( $this->component ) . 's-search bp-search select-wrap' : '';
				$field        .= '<div id="gmw-' . esc_attr( $this->prefix ) . '-radius-wrapper" class="' . $nouveau_class . '">';
			}

			$field .= gmw_get_form_field( $args );

			if ( ! $profile_search ) {
				$field .= '<span class="select-arrow" aria-hidden="true"></span>';
				$field .= '</div>';
			}
		} else {
			$field .= gmw_get_form_field( $args );
		}

		return $field;
	}

	/**
	 * Modify the groupss search form - append GMW field to it.
	 *
	 * @param array $search_form_html form element.
	 */
	public function directory_form( $search_form_html ) {

		$prefix                       = esc_attr( $this->prefix );
		$search_form                  = array();
		$search_form['address_field'] = $this->get_form_address_field();
		$search_form['radius_field']  = $this->get_form_radius_field();
		$search_form['coords']        = '<input type="hidden" name="lat" id="gmw-lat-' . $prefix . '" value="' . esc_attr( $this->form['lat'] ) . '" />';
		$search_form['coords']       .= '<input type="hidden" name="lng" id="gmw-lng-' . $prefix . '" value="' . esc_attr( $this->form['lng'] ) . '" />';

		$search_form = apply_filters( 'gmw_' . $prefix . '_search_form_html', $search_form, $this );
		$search_form = implode( ' ', $search_form );

		if ( ! $this->is_bp_nouveau ) {
			$search_form = '<div id="gmw-' . $prefix . '-form-temp-holder">' . $search_form . '</div>';
		}

		return $search_form_html . $search_form;
	}

	/**
	 * Generate the map element
	 */
	public function map_element() {

		// map args.
		$args = array(
			'map_id'     => $this->prefix,
			'map_type'   => 'bp_' . $this->component . 's_directory_geolocation',
			'prefix'     => $this->prefix,
			'map_width'  => $this->options['map_width'],
			'map_height' => $this->options['map_height'],
			'form_data'  => $this->form,
		);

		// display the map element.
		echo GMW_Maps_API::get_map_element( $args ); // WPCS XSS ok.
	}

	/**
	 * Generate the map
	 */
	public function trigger_js_and_map() {

		// create the map object.
		$map_args = array(
			'map_id'               => $this->prefix,
			'map_type'             => 'bp_' . $this->component . 's_directory_geolocation',
			'prefix'               => $this->prefix,
			'info_window_type'     => 'standard',
			'info_window_ajax'     => false,
			'info_window_template' => 'default',
			'group_markers'        => 'markers_clusterer',
			'render_on_page_load'  => false,
		);

		$map_options = array(
			'zoom'      => 'auto',
			'mapTypeId' => ! empty( $this->options['map_type'] ) ? $this->options['map_type'] : 'ROADMAP',
		);

		$user_position = array(
			'lat'        => $this->form['lat'],
			'lng'        => $this->form['lng'],
			'address'    => $this->form['address'],
			'map_icon'   => GMW()->default_icons['user_location_icon_url'],
			'icon_size'  => GMW()->default_icons['user_location_icon_size'],
			'iw_content' => __( 'You are here', 'geo-my-wp' ),
			'iw_open'    => false,
		);

		// triggers map on page load.
		$map_args = gmw_get_map_object( $map_args, $map_options, $this->map_locations, $user_position, array() );
		?>
		<script>       
		jQuery( window ).ready( function() {

			var mapArgs = <?php echo wp_json_encode( $map_args ); ?>;

			// create map if not exists
			if ( typeof GMW_Maps.bpdg == 'undefined' ) {

				// generate map when ajax is triggered
				GMW_Maps.bpdg = new GMW_Map( mapArgs.settings, mapArgs.map_options, {} );
				// initiate it
				GMW_Maps.bpdg.render( mapArgs.locations, mapArgs.user_location );

			// update existing map
			} else {
				GMW_Maps.bpdg.update( mapArgs.locations, mapArgs.user_location );
			}
		});
		</script>
		<?php
	}

	/**
	 * Generate group location for the map.
	 *
	 * @param  object $object      object ( group/member ) data.
	 *
	 * @param  object $info_window info window.
	 */
	public function map_location( $object, $info_window ) {

		// add lat/lng locations array to pass to map.
		return apply_filters(
			'gmw_' . $this->prefix . '_' . $this->component . '_data',
			array(
				'ID'                  => $object->id,
				'lat'                 => $object->lat,
				'lng'                 => $object->lng,
				'map_icon'            => '',
				'icon_size'           => GMW()->default_icons['location_icon_size'],
				'info_window_content' => $info_window,
			),
			$object,
			$this
		);
	}

	/**
	 * Generate distance to each item in the results.
	 *
	 * @param object $object  object ( group/member ) data.
	 */
	public function get_distance( $object ) {

		if ( empty( $this->options['distance'] ) || empty( $object->distance ) ) {
			return;
		}

		$output = '<span class="gmw-item gmw-item-distance">' . esc_attr( $object->distance ) . ' ' . esc_attr( $object->units ) . '</span>';

		// display the distance in results.
		return apply_filters( 'gmw_' . $this->prefix . '_' . $this->component . '_distance', $output, $object, $this ); // WPCS: XSS ok.
	}

	/**
	 * Generate group address.
	 *
	 * @param  object $object  object ( group/member ) data.
	 *
	 * @return [type]         [description]
	 */
	public function get_address( $object ) {

		if ( empty( $this->options['address_fields'] ) ) {
			return;
		}

		$address = gmw_get_location_address( $object, $this->options['address_fields'] );
		$output  = '';

		if ( ! empty( $address ) ) {
			$output .= '<div class="gmw-item gmw-item-address"><i class="gmw-icon-location-thin"></i>' . esc_attr( $address ) . '</div>'; // WPCS: XSS ok.
		}

		return apply_filters( 'gmw_' . $this->prefix . '_' . $this->component . '_address', $output, $object, $this );
	}

	/**
	 * Append location data to the list of groups
	 */
	public function add_elements_to_results() {

		$component = $this->component;

		if ( 'group' === $this->component ) {

			global $groups_template;

			$objects_template = $groups_template;

		} else {

			global $members_template;

			$objects_template = $members_template;
		}

		$object = $objects_template->$component;

		// abort if user does not have a location.
		if ( empty( $object->lat ) ) {
			return;
		}

		// show address in results.
		echo self::get_address( $object ); // WPCS: XSS ok.

		// distance.
		echo self::get_distance( $object ); // WPCS: XSS ok.

		// show directions in results.
		if ( ! empty( $this->options['directions_link'] ) ) {
			echo '<span class="gmw-item gmw-item-directions">' . gmw_get_directions_link( $object, $this->form, $this->labels['get_directions'] ) . '</span>'; // WPCS: XSS ok.
		}
	}
}
