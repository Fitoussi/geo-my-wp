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
		'component'       => '',
		'addon'           => '',
		'prefix'          => '',
		'address'         => '',
		'lat'             => '',
		'lng'             => '',
		'distance'        => '',
		'radius'          => '', // Duplciate of distance, to support other queries and elements of the plugin.
		'units'           => 'imperial',
		'options'         => array(),
		'address_filters' => array(),
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
	 * Objects without location enabled by default.
	 *
	 * @var boolean
	 */
	public $enable_objects_without_location = true;

	/**
	 * Check if using buddyboss theme.
	 *
	 * @var boolean
	 */
	public $is_buddyboss = false;

	/**
	 * Check if search form already loaded.
	 *
	 * @var boolean
	 */
	public $form_loaded = false;

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

		$this->is_bp_nouveau     = function_exists( 'bp_nouveau' ) ? true : false;
		$this->form['component'] = 'bp_directiory_geolocation';
		$this->form['addon']     = 'bp_' . $this->component . 's_directiory_geolocation';
		$this->form['prefix']    = $this->prefix;
		$this->form['units']     = 'metric' === $this->options['units'] ? 'metric' : 'imperial';
		$this->form['options']   = $this->options;

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

		} elseif ( function_exists( 'bp_ps_get_request' ) ) {
			$bp_search_values = bp_ps_get_request( 'search' );
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

		// Duplciate of distance, to support other queries and elements of the different extensions.
		$this->form['radius'] = $this->form['distance'];

		$this->form = apply_filters( 'gmw_' . $this->prefix . '_form_data', $this->form, $this );

		$this->enable_objects_without_location = apply_filters( 'gmw_' . $this->prefix . '_enable_objects_without_location', $this->enable_objects_without_location, $this->form, $this );

		$this->db_fields = apply_filters( 'gmw_' . $this->prefix . '_form_db_fields', $this->db_fields, $this->form );
		$this->db_fields = preg_filter( '/^/', 'gmw_locations.', $this->db_fields );
		$this->db_fields = apply_filters( 'gmw_' . $this->prefix . '_form_db_fields_prefixed', $this->db_fields, $this->form );
		$this->db_fields = implode( ',', $this->db_fields );

		// action hooks / filters.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add "Distance" to the order by filter.
		add_action( 'bp_' . $this->component . 's_directory_order_options', array( $this, 'orderby_distance' ), 5 );

		// SweetDate theme.
		if ( function_exists( 'sweetdate_setup' ) ) {

			add_action( 'kleo_bp_search_add_data', array( $this, 'sweetdate_directory_form' ) );
			add_action( 'bp_groups_directory_group_filter', array( $this, 'sweetdate_directory_form' ) );

			// For Kleo Theme.
		} elseif ( function_exists( 'kleo_setup' ) ) {

			add_filter( 'bp_directory_' . $this->component . 's_search_form', array( $this, 'kleo_directory_form' ) );

			// All themes.
		} else {

			add_filter( 'bp_directory_' . $this->component . 's_search_form', array( $this, 'directory_form' ) );
		}

		$this->action_hooks();

		// Modify the hook that is used to generate the location data in the results.
		$results_elements_filter = apply_filters( 'gmw_' . $this->prefix . '_' . $this->component . 's_loop_location_elements_hook', 'bp_directory_' . $this->component . 's_item' );

		// Skip if disabled.
		if ( ! empty( $results_elements_filter ) ) {

			global $buddyboss_platform_plugin_file;

			// Youzify plugin.
			if ( class_exists( 'Youzify' ) ) {

				add_action( $results_elements_filter, array( $this, 'add_elements_to_results' ) );

				// For BuddyBoss theme.
			} elseif ( function_exists( 'buddyboss_theme' ) || ! empty( $buddyboss_platform_plugin_file ) ) {

				$this->is_buddyboss = true;

				add_action( 'bp_member_members_list_item', array( $this, 'add_elements_to_results' ) );
				add_action( 'bp_directory_groups_item', array( $this, 'add_elements_to_results' ) );

			} elseif ( function_exists( 'buddyx_template_pack_check' ) ) {
				
				add_filter( 'bp_nouveau_get_member_meta', array( $this, 'add_elements_to_results_buddyx' ) );
				add_action( 'bp_directory_groups_item', array( $this, 'add_elements_to_results' ) );

				// For other themes.
			} else {

				add_action( $results_elements_filter, array( $this, 'add_elements_to_results' ) );
			}
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

		do_action( 'gmw_element_loaded', 'buddypress_directory', $this );

		add_action( 'bp_nouveau_feedback_messages', array( $this, 'modify_results_message' ) );
	}

	/**
	 * Modify results message when geocoding fails.
	 *
	 * @since 2.0
	 *
	 * @param  [type] $messages [description].
	 *
	 * @return [type]           [description]
	 */
	public function modify_results_message( $messages ) {

		if ( ! empty( $this->form['geocoding_failed'] ) ) {
			$messages['members-loop-none']['message'] = $this->labels['address_error_message'];
			$messages['groups-loop-none']['message']  = $this->labels['address_error_message'];
		}

		return $messages;
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

		$args = array(
			'prefix'       => $this->prefix,
			'component'    => $this->component,
			'is_buddyboss' => $this->is_buddyboss,
		);

		wp_localize_script( 'gmw-bpdg', 'gmwBpdg', $args );
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
				'address_error_message' => __( 'We could not verify the address that you entered.', 'geo-my-wp' ),
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

		$wrap_class = 'gmw-bpdg-address-field-wrapper';
		$locator    = '';

		if ( ! empty( $this->options['locator_button'] ) ) {
			$wrap_class .= ' gmw-locator-button-enabled';
			$locator     = '<i class="gmw-locator-button inside gmw-icon-target-light" data-locator_submit="1" data-form_id="' . $prefix . '"></i>';
		}

		$field = '';

		if ( $this->is_bp_nouveau && ! $profile_search ) {
			$field .= '<div id="gmw-' . $prefix . '-search-wrapper" class="dir-search ' . esc_attr( $this->component ) . 's-search bp-search">';
			$field .= '<form id="gmw-' . $prefix . '-form" class="bp-dir-search-form">';
		}

		$field .= '<div id="gmw-address-field-' . $prefix . '-wrapper" class="' . $wrap_class . '">';
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
				$field        .= '<div id="gmw-' . esc_attr( $this->prefix ) . '-radius-wrapper" class="gmw-bpdg-radius-field-wrapper ' . $nouveau_class . '">';
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
	 * Generate the search form element.
	 *
	 * @since 2.0
	 *
	 * @author Eyal Fitoussi
	 *
	 * @return [type] [description]
	 */
	public function get_form_elements() {

		$prefix      = esc_attr( $this->prefix );
		$search_form = array();

		if ( ! $this->is_bp_nouveau ) {
			$search_form['holder'] = '<div id="gmw-' . $prefix . '-form-temp-holder">';
		}

		$search_form['address_field'] = $this->get_form_address_field();
		$search_form['radius_field']  = $this->get_form_radius_field();
		$search_form['coords']        = '<input type="hidden" name="lat" id="gmw-lat-' . $prefix . '" value="' . esc_attr( $this->form['lat'] ) . '" />';
		$search_form['coords']       .= '<input type="hidden" name="lng" id="gmw-lng-' . $prefix . '" value="' . esc_attr( $this->form['lng'] ) . '" />';

		if ( ! $this->is_bp_nouveau ) {
			$search_form['/holder'] = '</div>';
		}

		return apply_filters( 'gmw_' . $prefix . '_search_form_html', $search_form, $this );
	}

	/**
	 * Modify the directory search form with GEolocation filters.
	 *
	 * This will be used by default for most themes.
	 *
	 * @since 4.0
	 *
	 * @author Eyal Fitoussi
	 *
	 * @param array $search_form_html form element.
	 */
	public function directory_form( $search_form_html ) {
		
		$this->form_loaded = true;

		$search_form = $this->get_form_elements();
		$search_form = implode( ' ', $search_form );

		return $search_form_html . $search_form;
	}

	/**
	 * Search form for the SweetDate theme.
	 *
	 * @since 4.0
	 *
	 * @author Eyal Fitoussi
	 */
	public function sweetdate_directory_form() {

		$search_form = $this->get_form_elements();

		// Only for the members page.
		if ( 'bp_groups_directory_group_filter' !== current_action() ) {

			$search_form['address_field'] = '<div class="three columns hz-textbox">' . $search_form['address_field'] . '</div>';
			$search_form['radius_field']  = '<div class="two columns">' . $search_form['radius_field'] . '</div>';

			unset( $search_form['holder'], $search_form['/holder'] );
		}

		?>
		<style type="text/css">

			body.directory.groups.buddypress #search-groups-form {
				display: flex;
				margin-bottom: 20px;
			}

			body.directory.groups.buddypress #search-groups-form * {
				margin-bottom: 0;
			}

			body.directory.groups.buddypress #search-groups-form > * {
				flex-grow: 1;
				margin-right: 10px;
				width: initial;
			}

			body.directory.groups.buddypress #search-groups-form.custom #gmw-bpgdg-radius-wrapper div.custom.dropdown {
				width: 100% ! important;
			}

			body.directory.groups.buddypress #groups_search_submit {
				margin-right: 0;
			}

			body.directory.groups.buddypress #search-groups-form #gmw-bpgdg-radius-wrapper {
				max-width: 120px;
			}

			#members-group-list #members-list .gmw-item-distance,
			body.directory.buddypress #groups-list .gmw-item-distance,
			body.directory.buddypress #members-list .gmw-item-distance {
				top: 100px;
				right: 16px;
			}
		</style>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery( '#gmw-' + gmwBpdg.prefix + '-form-temp-holder' ).children().detach().insertBefore( '#groups_search_submit' );
			});
		</script>
		<?php
		echo implode( ' ', $search_form );
	}

	/**
	 * Styling for the Kleo theme.
	 *
	 * @author Eyal Fitoussi
	 *
	 * @since 4.0
	 *
	 * @author Eyal Fitoussi
	 */
	public function kleo_directory_form( $search_form_html ) {

		$search_form = $this->get_form_elements();
		?>
		<style type="text/css">

			body.buddypress.directory.bp-legacy #buddypress div#members-dir-search,
			body.buddypress.directory.bp-legacy #buddypress div#group-dir-search {
				//display: grid;
				//grid-template-columns: 40% 40% auto;
				//grid-gap: 25px;
				display: flex;
			}

			body.buddypress.directory.bp-legacy #search-members-form,
			body.buddypress.directory.bp-legacy #search-groups-form {
				margin-left: initial;
				margin-right: initial;
				min-width: initial;
				flex-grow: 1;
				margin-right: 20px;
			}

			body.buddypress.directory.bp-legacy #buddypress .gmw-bpdg-radius-field-wrapper {
				width: 120px;
				margin-left: 20px;
			}

			body.buddypress.directory.bp-legacy #buddypress .gmw-distance-field {
				width: 100%;
			}

			body.buddypress.directory.bp-legacy #buddypress .gmw-bpdg-address-field-wrapper {
				//margin-left: auto;
				//margin-right: auto;
				display: inline-block;
				border-radius: 22px;
				border-style: solid;
				border-width: 1px;
				height: 33px;
				line-height: 30px;
				padding: 0 10px;
				-webkit-transition: .7s;
				-moz-transition: .7s;
				-o-transition: .7s;
				transition: .7s;
				text-align: left;
				box-shadow: 0 0 0 4px #f7f7f7;
				border-color: #e5e5e5;
				//margin-left: 0;
				//margin-right: 20px;
				//min-width: 30%;
				flex-grow: 1;
			}

			#members-group-list #members-list .gmw-item-distance,
			body.directory.buddypress #groups-list .gmw-item-distance,
			body.directory.buddypress #members-list .gmw-item-distance {
				top: 10px;
				right: 10px;
			}

		</style>
		<?php

		unset( $search_form['holder'], $search_form['/holder'] );

		return $search_form_html . implode( ' ', $search_form );
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
		echo gmw_get_map_element( $args, $this->form ); // WPCS XSS ok.
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
	/*public function map_location( $object, $info_window_args ) {

		$info_window = gmw_get_info_window_content( $group, $info_window_args, $this->form );		

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
	}*/

	public function the_location( $object ) {

		if ( ! empty( $this->options['map'] ) ) {

			$object->map_icon = '';
			$info_window_args = $this->get_info_window_args( $object );
			$location         = gmw_get_object_map_location( $object, $info_window_args, $this->form );

			if ( $location ) {
				$this->map_locations[] = $location;
			}
		}
	}

	/**
	 * Generate distance to each item in the results.
	 *
	 * @param object $object  object ( group/member ) data.
	 */
	public function get_distance( $object ) {

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

		$address = gmw_get_location_address( $object, $this->options['address_fields'] );
		$output  = '';

		if ( ! empty( $address ) ) {
			$output .= '<span class="gmw-item gmw-item-address"><i class="gmw-icon-location-thin"></i>' . esc_attr( $address ) . '</span>'; // WPCS: XSS ok.
		}

		return apply_filters( 'gmw_' . $this->prefix . '_' . $this->component . '_address', $output, $object, $this );
	}

	/**
	 * Get location data for the results.
	 */
	public function get_item_location_elements() {

		$component = $this->component;

		if ( 'group' === $this->component ) {

			global $groups_template;

			$objects_template = $groups_template;

		} else {

			global $members_template;

			$objects_template = $members_template;
		}

		$object = $objects_template->$component;
		$output = '';

		// abort if object does not have a location.
		if ( empty( $object->lat ) ) {
			return $output;
		}

		// Collect location.
		$this->the_location( $object );

		$output = '<div class="gmw-' . $this->prefix . '-location-meta-wrapper">';

		// show address in results.
		if ( ! empty( $this->options['address_fields'] ) ) {
			$output .= self::get_address( $object ); // WPCS: XSS ok.
		}

		if ( ! empty( $this->options['distance'] ) && ! empty( $object->distance ) ) {
			$output .= self::get_distance( $object ); // WPCS: XSS ok.
		}

		// show directions in results.
		if ( ! empty( $this->options['directions_link'] ) ) {

			$directions = gmw_get_directions_link( $object, $this->form, $this->labels['get_directions'] );

			$output .= '<span class="gmw-item gmw-item-directions">' . $directions . '</span>'; // WPCS: XSS ok.
		}

		$output .= '</div>';

		return apply_filters( 'gmw_' . $this->prefix . '_item_location_elements', $output, $object, $this );
	}

	/**
	 * Append location data to each item in the results.
	 */
	public function add_elements_to_results() {
		echo $this->get_item_location_elements(); // WPCS: XSS ok.
	}

	/**
	 * Append location data to each item in the results for BuddyX theme.
	 *
	 * @param array $meta memebr meta.
	 *
	 * @since 4.0
	 *
	 * @return [type]         [description]
	 */
	public function add_elements_to_results_buddyx( $meta ) {

		echo $this->get_item_location_elements(); // WPCS: XSS ok.

		return $meta;
	}
}
