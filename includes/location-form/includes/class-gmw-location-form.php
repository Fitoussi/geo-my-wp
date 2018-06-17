<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GEO my WP Location Form class.
 *
 * @version 1.0
 *
 * @since 3.0
 *
 */
class GMW_Location_Form {

	/**
	 * Usually add-on's slug
	 *
	 * @var string
	 */
	public $slug = false;

	/**
	 * Object type
	 */
	public $object_type = false;

	/**
	 * Exclude form tabs
	 *
	 * Additional groups can be excluded via the $args array
	 *
	 * @var array
	 */
	public $exclude_fields_groups = array();

	/**
	 * Exclude form fields
	 *
	 * Additional fields can be excluded via the $args array
	 *
	 * @var array
	 */
	public $exclude_fields = array();

	/**
	 * Existing location if saved in database.
	 *
	 * @var array
	 */
	public $saved_location = array();

	/**
	 * Existing location ID
	 *
	 * @var integer
	 */
	public $location_id = 0;

	/**
	 * User's current location
	 *
	 * @var boolean
	 */
	public $user_location = false;

	/**
	 * Save location meta
	 *
	 * @var boolean
	 */
	public $saved_locationmeta = false;

	/**
	 * Form Tabs
	 *
	 * @var array
	 */
	public $tabs = array();

	/**
	 * Form Fields
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * Form templates folders
	 *
	 * @var array
	 */
	public $template_folders = array();

	/**
	 * @since 3.0
	 *
	 * Public $args
	 *
	 * Default arguments
	 */
	private $default_args = array(
		//'slug'					=> '',
		//'object_type' 			=> 'post',					  // Object type being used in this form.
		'object_id'                 => 0,                         // Object ID.
		'user_id'                   => 0,                         // User ID of the user updating the location.
		//'exclude_tabs'			=> '',						  // array of tabs to exclude from the location form. Otherwise set to 0 if no need to exclude.
		'exclude_fields_groups'     => '',                        // array of fields groups to exclude from the location form.
		'exclude_fields'            => '',                        // exclude fields
		'ajax_enabled'              => 1,                         // Enable / disable AJAX form submission
		'update_on_submission'      => 1,                         // Use the built in form submission process to update the location. Otherwise you can use your own functions to save the location data.
		'confirm_required'          => 1,                         // require address to be confirmed before saving the location. Otherwise users will be able to entere any address - coordinates without being confirmed.
		'auto_confirm'              => 0,
		'default_user_location'     => 0,                         // auto-populate location fields with the user's current position if exsist. That is in case that there is no location save for the user.
		'stand_alone'               => 1,                         // Wrap the location form within <form> element. That is if the location form is a stand alone and not within another form.
		'form_element'              => '#gmw-location-form',      // form wrapper element. If the location form is within another form the main form element should be used in here.
		'form_template'             => 'location-form-tabs-left', // Form template name.
		'submit_enabled'            => 1,                         // Show "Submit" button within the location form. That can be used when the location form is a stand alone. If the location form is within another form then the submit button of that form should be used.
		'preserve_submitted_values' => 0,                         // when form submitted via page load, populate the form with the submitted values.
		'address_autocomplete'      => 1,                         // Enabled / disable Google Address autocomplete
		'geolocation_button'        => 1,                         // Enable / disable auto locator button in the address field
		'map_zoom_level'            => 12,                        // Initial map zoom level
		'map_type'                  => 'ROADMAP',                 // map type ROADMAP, TERRAIN, SATELLITE or HYBRID
		'map_lat'                   => '40.7827096',              // Map initial latitude
		'map_lng'                   => '-73.9653099',             // Map initial longitude
		'update_callback'           => 'gmw_lf_update_location',  // AJAX save callback function
		'delete_callback'           => 'gmw_lf_delete_location',  // AJAX delete callback function
		'location_required'         => 0,
	);

	/**
	 * @since 3.0
	 *
	 * Public $args
	 *
	 * Array for child class to extends the main array above
	 */
	protected $ext_defaults = array();

	/**
	 * [$boolean_items description]
	 * @var array
	 */
	private $boolean_items = array(
		'ajax_enabled',
		'default_user_location',
		'stand_alone',
		'submit_enabled',
		'address_autocomplete',
		'confirm_required',
		'geolocation_button',
	);

	/**
	 * Passed arguments
	 *
	 * @param array $args [description]
	 */
	function __construct( $args = array() ) {

		// extend the default args
		$defaults = array_merge( $this->default_args, $this->ext_defaults );

		// get the shortcode atts
		//$this->args = shortcode_atts( $this->args, $atts, 'gmw_location_form' );

		$this->args = wp_parse_args( $args, $defaults );

		// filter the location form args
		$this->args = apply_filters( 'gmw_location_form_args', $this->args, $this->object_type, $this->slug );

		// allow boolean attributes accespt 1/yes/true as true value.
		foreach ( $this->boolean_items as $boolean_item ) {
			$this->args[ $boolean_item ] = filter_var( $this->args[ $boolean_item ], FILTER_VALIDATE_BOOLEAN );
		}

		// verify user ID
		$this->args['user_id'] = ! empty( $this->args['user_id'] ) ? $this->args['user_id'] : get_current_user_id();

		if ( $this->args['stand_alone'] ) {
			$this->args['form_element'] = '#gmw-location-form';
		}

		// get fields groups excluded via form arguments
		if ( empty( $this->args['exclude_fields_groups'] ) ) {

			$this->args['exclude_fields_groups'] = array();

		} elseif ( ! is_array( $this->args['exclude_fields_groups'] ) ) {

			$this->args['exclude_fields_groups'] = explode( ',', $this->args['exclude_fields_groups'] );
		}

		if ( ! empty( $this->exclude_fields_groups ) ) {

			if ( ! is_array( $this->exclude_fields_groups ) ) {

				$this->exclude_fields_groups = explode( ',', $this->exclude_fields_groups );

			}

			$this->args['exclude_fields_groups'] = array_merge( $this->exclude_fields_groups, $this->args['exclude_fields_groups'] );
		}

		// exclude fields
		if ( empty( $this->args['exclude_fields'] ) ) {

			$this->args['exclude_fields'] = array();

		} elseif ( ! is_array( $this->args['exclude_fields'] ) ) {

			$this->args['exclude_fields'] = explode( ',', $this->args['exclude_fields'] );
		}

		if ( ! empty( $this->exclude_fields ) ) {

			if ( ! is_array( $this->exclude_fields ) ) {

				$this->exclude_fields = explode( ',', $this->exclude_fields );

			}

			$this->args['exclude_fields'] = array_merge( $this->exclude_fields, $this->args['exclude_fields'] );
		}

		if ( $this->args['preserve_submitted_values'] && ! $this->args['ajax_enabled'] && ! empty( $_POST['gmw_action'] ) && 'update_lf_location' == $_POST['gmw_action'] && ! empty( $_POST['gmw_lf_slug'] ) && $_POST['gmw_lf_slug'] == $this->slug ) {

			$this->saved_location = (object) $_POST['gmw_location_form'];

		} else {

			// get location from database if exist
			$this->saved_location = $this->get_saved_location();
		}

		$this->saved_location = apply_filters( 'gmw_location_form_default_location', $this->saved_location, $this->args, $this );

		// Make sure default location is an object, rather than array.
		if ( is_array( $this->saved_location ) ) {
			$this->saved_location = (object) $this->saved_location;
		}

		// get existing location ID
		$this->location_id = ! empty( $this->saved_location ) ? absint( $this->saved_location->ID ) : 0;

		// get the user's current position
		$this->user_location = gmw_get_user_current_location();

		// get locationmeta from database if exist
		$this->saved_locationmeta = $this->get_saved_locationmeta();

		// form tabs
		$this->tabs = $this->form_tabs();

		// location form fields
		$this->fields = $this->form_fields();

		// exclude fields
		$this->exclude_fields_groups();

		// exclude some fields
		$this->exclude_fields();

		// location form messages
		$this->messages = $this->action_messages();

		// get template folders
		$this->template_folders = self::get_folders();

		// enqueue scripts in footer
		add_action( 'wp_footer', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'admin_footer', array( $this, 'enqueue_scripts' ), 10 );
	}

	public function enqueue_scripts() {

		if ( ! wp_script_is( 'gmw-location-form', 'enqueued' ) ) {
			wp_enqueue_style( 'gmw-location-form' );
			wp_enqueue_script( 'gmw-location-form' );
		}

		do_action( 'gmw_location_form_enqueue_script' );

		// load chosen if not already loaded
		if ( ! wp_script_is( 'chosen', 'enqueued' ) ) {
			wp_enqueue_script( 'chosen' );
			wp_enqueue_style( 'chosen' );
		}

		wp_localize_script(
			'gmw-location-form', 'gmw_lf_args', array(
				'slug'           => $this->slug,
				'object_type'    => $this->object_type,
				'args'           => $this->args,
				'saved_location' => $this->saved_location,
				'user_location'  => $this->user_location,
				'tabs'           => $this->tabs,
				'fields'         => $this->fields,
				'messages'       => $this->messages,
				'nonce'          => wp_create_nonce( 'gmw_lf_update_location' ),
			)
		);
	}

	/**
	 * Form messages
	 *
	 * @since 3.0
	 *
	 * @return array messages display in the location form.
	 */
	public function action_messages() {

		return apply_filters(
			'gmw_lf_form_messages', array(
				'confirming_location'  => __( 'Comnfirming Location...', 'geo-my-wp' ),
				'location_exists'      => __( 'Location confirmed', 'geo-my-wp' ),
				'location_not_exists'  => __( 'No location found', 'geo-my-wp' ),
				'location_changed'     => __( 'Location changed', 'geo-my-wp' ),
				'location_saved'       => __( 'Location updated!', 'geo-my-wp' ),
				'location_not_saved'   => __( 'There was a problem saving your location. Please try again.', 'geo-my-wp' ),
				'location_deleted'     => __( 'Location deleted!', 'geo-my-wp' ),
				'location_not_deleted' => __( 'There was a problem deleting your location. Please try again.', 'geo-my-wp' ),
				'location_found'       => __( 'Location found!', 'geo-my-wp' ),
				'geocoder_failed'      => __( 'We were unable to retrieve your location. Enter a valid address or coordinates.', 'geo-my-wp' ),
				'location_missing'     => __( 'No location entered.', 'geo-my-wp' ),
				'location_required'    => __( 'You must enter a location to proceed.', 'geo-my-wp' ),
				'confirm_required'     => __( 'You must confirm your location before it can be saved', 'geo-my-wp' ),
				'confirm_message'      => __( 'You have not confirmed your location. Would you like to proceed?', 'geo-my-wp' ),
				'delete_confirmation'  => __( 'This action cannot be undone. Would you like to proceed?', 'geo-my-wp' ),
				'coords_invalid'       => __( 'Coordinates are missing or invalid.', 'geo-my-wp' ),
			)
		);
	}

	/**
	 * Get location from database if exists.
	 *
	 * @since 3.0
	 *
	 * @return array | false location details from database if exists
	 */
	protected function get_saved_location() {

		$location = gmw_get_location( $this->object_type, $this->args['object_id'] );

		return ! empty( $location ) ? $location : false;
	}

	/**
	 * get location meta field from databse if exists.
	 *
	 * We getting all meta fields associate with the location to be able to display
	 *
	 * thier values in the location form if needed
	 *
	 * @since 3.0
	 *
	 * @return object | false all the location meta fields attached to the location we are editing
	 */
	protected function get_saved_locationmeta() {

		//abort, if no location exists
		if ( ! $this->saved_location ) {
			return false;
		}

		// pull the location meta from database
		$location_meta = gmw_get_location_meta( $this->saved_location->ID );

		return ! empty( $location_meta ) ? $location_meta : false;
	}

	/**
	 * Get template folder.
	 *
	 * Check if custom folders and fields exist in child/theme folder, if so, we will use that. Otherwise, use the plugin's templates.
	 *
	 * @return [type] [description]
	 */
	public function get_folders() {

		return array(
			'form_fields'    => file_exists( STYLESHEETPATH . '/geo-my-wp/location-form/form-fields/' ) ? STYLESHEETPATH . '/geo-my-wp/location-form/form-fields/' : GMW_PATH . '/includes/location-form/templates/form-fields/',
			'form_templates' => file_exists( STYLESHEETPATH . '/geo-my-wp/includes/location-form/form-templates/' ) ? STYLESHEETPATH . '/geo-my-wp/location-form/form-templates/' : GMW_PATH . '/includes/location-form/templates/location-forms/',
		);
	}

	/**
	 * Form tabs
	 *
	 * @return [type] [description]
	 */
	public function form_tabs() {

		$tabs = apply_filters(
			'gmw_location_form_tabs', array(
				'location'    => array(
					'label'        => __( 'Location', 'geo-my-wp' ),
					'icon'         => 'gmw-icon-location',
					'fields_group' => array( 'location' ),
					'priority'     => 5,
				),
				'address'     => array(
					'label'        => __( 'Address', 'geo-my-wp' ),
					'icon'         => 'gmw-icon-flag',
					'fields_group' => array( 'address' ),
					'priority'     => 10,
				),
				'coordinates' => array(
					'label'        => __( 'Coordinates', 'geo-my-wp' ),
					'icon'         => 'gmw-icon-compass',
					'fields_group' => array( 'coordinates' ),
					'priority'     => 15,
				),
			), $this->args, $this->object_type, $this->slug
		);

		return $tabs;
	}

	/**
	 * Exclude tabs and its fields
	 *
	 * Note that this function exclude the tabs only, not thier containers
	 * with the field. The containers are being excluded via JS.
	 *
	 * @return [type] [description]
	 */
	/*
	public function exclude_tabs() {

		if ( array_filter( $this->args['exclude_fields_groups'] ) ) {

			foreach ( $this->args['exclude_fields_groups'] as $exclude_tab ) {

				if ( isset( $this->tabs[$exclude_tab] ) ) {

					if ( ! empty( $this->fields[$exclude_tab]['fields'] ) ) {
						// collect all fields that belong to excluded tab. We will than exlcude the fields as well
						$this->args['exclude_fields'] = array_merge( $this->args['exclude_fields'], array_keys( $this->fields[$exclude_tab]['fields'] ) );
					}
					// exclude tab
					unset( $this->tabs[$exclude_tab] );
				}
			}
		}
	}
	*/

	/**
	 * Default location fields.
	 *
	 * @since 3.0
	 *
	 * Fields can be extended using add-ons via the filter 'gmw_location_form_fields'.
	 *
	 * Each field must have its key serves as a slug to be able to easily pull the field data.
	 *
	 * Example :
	 *
	 *  //this 'my_form_field' array key will serve as a slug
	 *  'my_form_field' => array (
	 *      'name'        => ( required ) will be used for the field's name attribute.
	 *      'label'       => ( optional ) will be used for the field's label attribute.
	 *      'type'        => will be used for the field's type attribute. If left blank the type "text" will be used.
	 *      'default'     => ( optional ) default value for the field.
	 *      'id'          => ( optional ) will be used for the field's ID attribute.
	 *      'class'       => ( optional ) will be used for the field's class attribute.
	 *      'placeholder' => ( optional ) will be used for the field's placeholder attribute ( text fields only ).
	 *      'desc'        => ( options ) field description will show below the field.
	 *      'attributes'  => ( optional ) array of attribute_name => attribute_value ( ex. 'size' => '50' ).
	 *      'priority'    => Position of the field.
	 *      'meta_key'    => ( optional ) its value will be a location meta_key to save the value of the filed to.
	 *   );
	 *
	 */
	public function form_fields() {

		return apply_filters(
			'gmw_location_form_fields', array(
				'location'    => array(
					'label'  => __( 'Find Your Location', 'geo-my-wp' ),
					'fields' => array(
						'address' => array(
							'name'        => 'address',
							'label'       => __( 'Address', 'geo-my-wp' ),
							'type'        => 'address',
							'default'     => '',
							'id'          => 'gmw-lf-address',
							'class'       => $this->args['address_autocomplete'] ? 'gmw-lf-address-autocomplete' : '',
							'placeholder' => __( 'Enter an address...', 'geo-my-wp' ),
							'desc'        => __( 'Type an address to see suggested results.', 'geo-my-wp' ),
							'attributes'  => array( 'style' => 'width:100%' ),
							'priority'    => 5,
							'required'    => false,
						),
						'map'     => array(
							'name'        => 'map',
							'label'       => '',
							'type'        => 'map',
							'default'     => '',
							'id'          => 'gmw-lf-map',
							'class'       => 'gmw-map',
							'placeholder' => '',
							'desc'        => __( 'Drag the marker to your position on the map..', 'geo-my-wp' ),
							'attributes'  => array( 'style' => 'height:210px;width:100%' ),
							'priority'    => 10,
							'required'    => false,
						),
					),
				),
				'address'     => array(
					'label'  => __( 'Enter Address', 'geo-my-wp' ),
					'fields' => array(
						'street'       => array(
							'name'        => 'street',
							'label'       => __( 'Street', 'geo-my-wp' ),
							'type'        => 'text',
							'default'     => '',
							'id'          => 'gmw-lf-street',
							'class'       => '',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 5,
							'required'    => false,
						),
						'premise'      => array(
							'name'        => 'premise',
							'label'       => __( 'Apt/Suit', 'geo-my-wp' ),
							'type'        => 'text',
							'default'     => '',
							'id'          => 'gmw-lf-premise',
							'class'       => '',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 10,
							'required'    => false,
						),
						'city'         => array(
							'name'        => 'city',
							'label'       => __( 'City', 'geo-my-wp' ),
							'type'        => 'text',
							'default'     => '',
							'id'          => 'gmw-lf-city',
							'class'       => '',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 15,
							'required'    => false,
						),
						'region_name'  => array(
							'name'        => 'region_name',
							'label'       => __( 'State', 'geo-my-wp' ),
							'type'        => 'text',
							'default'     => '',
							'id'          => 'gmw-lf-region-name',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 20,
							'required'    => false,
						),
						'postcode'     => array(
							'name'        => 'postcode',
							'label'       => __( 'Zipcode', 'geo-my-wp' ),
							'type'        => 'text',
							'default'     => '',
							'id'          => 'gmw-lf-postcode',
							'class'       => '',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 25,
							'required'    => false,
						),
						'country_code' => array(
							'name'        => 'country_code',
							'label'       => __( 'Country', 'geo-my-wp' ),
							'type'        => 'text',
							'options'     => '',
							//'options'	  => gmw_get_countries_list_array(),
							'default'     => '',
							'id'          => 'gmw-lf-country-code',
							'class'       => '',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 30,
							'required'    => false,
						),
					),
				),
				'coordinates' => array(
					'label'  => __( 'Enter Coordinates', 'geo-my-wp' ),
					'fields' => array(
						'latitude'  => array(
							'name'        => 'latitude',
							'label'       => __( 'Latitude', 'geo-my-wp' ),
							'type'        => 'text',
							'default'     => '',
							'id'          => 'gmw-lf-latitude',
							'class'       => '',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 5,
							'required'    => false,
						),
						'longitude' => array(
							'name'        => 'longitude',
							'label'       => __( 'Longitude', 'geo-my-wp' ),
							'type'        => 'text',
							'default'     => '',
							'id'          => 'gmw-lf-longitude',
							'class'       => '',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 10,
							'required'    => false,
						),
					),
				),
				'actions'     => array(
					'label'  => false,
					'fields' => array(
						'submit_location'  => array(
							'name'        => 'submit_location',
							'label'       => __( 'Update location', 'geo-my-wp' ),
							'type'        => 'submit',
							'default'     => '',
							'id'          => 'gmw-lf-submit-location',
							'class'       => 'gmw-lf-form-action button action-button',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 5,
							'required'    => false,
						),
						'delete_location'  => array(
							'name'        => 'delete_location',
							'label'       => __( 'Delete Location', 'geo-my-wp' ),
							'type'        => 'button',
							'default'     => '',
							'id'          => 'gmw-lf-delete-location',
							'class'       => 'gmw-lf-form-action button action-button',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 10,
							'required'    => false,
						),
						'confirm_location' => array(
							'name'        => 'confirm_location',
							'label'       => __( 'Confirm Location', 'geo-my-wp' ),
							'type'        => 'button',
							'default'     => '',
							'id'          => 'gmw-lf-confirm-location',
							'class'       => 'gmw-lf-form-action button action-button gmw-lf-confirm-location',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 15,
							'required'    => false,
						),
						'message'          => array(
							'name'        => 'message',
							'label'       => '',
							'type'        => 'message',
							'default'     => '',
							'id'          => 'gmw-lf-action-message',
							'class'       => 'gmw-lf-form-action',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 20,
							'required'    => false,
						),

						'loader'           => array(
							'name'        => 'loader',
							'label'       => '',
							'type'        => 'loader',
							'default'     => '',
							'id'          => 'gmw-lf-action-loader',
							'class'       => 'gmw-lf-form-action gmw-icon-spin-3 animate-spin',
							'placeholder' => '',
							'desc'        => '',
							'attributes'  => '',
							'priority'    => 25,
							'required'    => false,
						),
					),
				),
			), $this
		);
	}

	/**
	 * Exclude tabs and its fields
	 *
	 * Note that this function exclude the tabs only, not thier containers
	 * with the field. The containers are being excluded via JS.
	 *
	 * @return [type] [description]
	 */
	public function exclude_fields_groups() {

		if ( array_filter( $this->args['exclude_fields_groups'] ) ) {

			foreach ( $this->args['exclude_fields_groups'] as $fields_group ) {

				//if ( isset( $this->fields[$fields_group] ) ) {

				if ( ! empty( $this->fields[ $fields_group ]['fields'] ) ) {
					// collect all fields and the tab that belong to the excluded group. We will exlcude the fields of the group and the tab if exists
					$this->args['exclude_fields'] = array_merge( $this->args['exclude_fields'], array_keys( $this->fields[ $fields_group ]['fields'] ) );
				}

					// exclude tab
				if ( isset( $this->tabs[ $fields_group ] ) ) {
					unset( $this->tabs[ $fields_group ] );
				}
				//}
			}
		}
	}

	/**
	 * Exclude fields
	 *
	 * @return [type] [description]
	 */
	public function exclude_fields() {

		if ( array_filter( $this->args['exclude_fields'] ) ) {

			foreach ( $this->fields as $fields_group => $group_args ) {

				//$this->fields[$fields_group]['fields'] = array_diff_key( $this->fields[$fields_group]['fields'], array_flip( $this->args['exclude_fields'] ) );

				foreach ( $this->args['exclude_fields'] as $exclude_field ) {

					// disable and hide excluded fields
					if ( isset( $this->fields[ $fields_group ]['fields'][ $exclude_field ] ) ) {

						// when excluding the main address field ( with the autocomplete )
						// we actually only hide it. At the moment the field is too invlove with the JavaSctipt
						// and the other field that things might break if we completly remove it.
						// The field being completly removed only when excluding the entire Location tab.
						// Which is done in via the JavaScript file
						if ( in_array( $exclude_field, array( 'address', 'delete_location', 'message', 'loader' ) ) ) {
							$this->fields[ $fields_group ]['fields'][ $exclude_field ]['attributes'] = array( 'disabled' => 'disabled' );
							$this->fields[ $fields_group ]['fields'][ $exclude_field ]['type']       = 'hidden';
						} else {
							unset( $this->fields[ $fields_group ]['fields'][ $exclude_field ] );
						}
					}
				}
			}
		}
	}

	/**
	 * Display form tabs
	 *
	 * @return [type] [description]
	 */
	protected function display_tabs() {

		// sort tabs by priority
		uasort( $this->tabs, 'gmw_sort_by_priority' );

		$tab_count = 1;
		$output    = '';

		// loop through tabs
		foreach ( $this->tabs as $key => $tab ) {

			$status = ( 1 == $tab_count ) ? 'active' : '';

			$output .= '<li id="' . sanitize_title( $key ) . '-tab" class="gmw-lf-tab ' . $status . ' ">';

			$output .= '<a href="#" class="tab-anchor" data-name="' . sanitize_title( $key ) . '">';

			if ( ! empty( $tab['icon'] ) ) {
				$output .= '<i class="' . esc_attr( $tab['icon'] ) . '"></i>';
			}
			$output .= '<span>' . esc_attr( $tab['label'] ) . '</span>';
			$output .= '</a>';
			$output .= '</li>';

			$tab_count++;
		}

		return $output;
	}

	/**
	 * Output a specific group of fields.
	 *
	 * @since 3.0
	 *
	 * @param  string $fields_group the name of the fields' groups to be displayed.
	 *
	 * @param  array  $exclude  array of fields to exclude from the group
	 *
	 * @return void
	 *
	 */
	public function display_form_fields_group( $fields_group, $exclude = array() ) {

		if ( empty( $this->fields[ $fields_group ] ) ) {
			return;
		}

		// check if group fields title exists and if so display it
		if ( ! empty( $this->form_fields[ $fields_group ]['label'] ) && apply_filters( 'gmw_lf_group_fields_title', true, $this->fields, $fields_group ) ) {
			echo '<h3>' . esc_attr( $this->form_fields[ $fields_group ]['label'] ) . '</h3>';
		}

		// sort fields
		uasort( $this->fields[ $fields_group ]['fields'], 'gmw_sort_by_priority' );

		// loop through and display fields
		foreach ( $this->fields[ $fields_group ]['fields'] as $slug => $field ) {

			// skip field if excluded
			if ( in_array( $slug, $exclude ) || 'section_title' == $slug ) {
				continue;
			}

			// display the form field
			$this->display_form_field( $fields_group, $slug );
		}
	}

	/**
	 * Display a single location form field
	 *
	 * @since 3.0
	 *
	 * @param  string $fields_group name of the fields group the field belongs to.
	 *
	 * @param  string $slug field slug
	 *
	 * @return display the location field
	 */
	public function display_form_field( $fields_group, $slug ) {

		// field must have slug
		if ( empty( $slug ) ) {
			return;
		}

		// get the field value
		$field = $this->fields[ $fields_group ]['fields'][ $slug ];

		// make sure name_attr exists otherwise create one
		$field_name = ! empty( $field['name'] ) ? $field['name'] : 'gmw_lf_' . $slug;

		// Deprecated variable.
		$fieldName = $field_name;

		// look for user's current position in cookies to automatically fill out the location form if user location not already exist in databse.
		if ( $this->args['default_user_location'] && ( in_array( $fields_group, array( 'address', 'coordinates', 'location' ) ) ) && empty( $this->saved_location ) && ! empty( $this->user_location ) ) {

			// get user location if found
			$field['value'] = isset( $this->user_location->$field_name ) ? stripslashes( $this->user_location->$field_name ) : '';

		} else {

			//get tvalue from saved location if exists
			$field['value'] = isset( $this->saved_location->$field_name ) ? stripslashes( $this->saved_location->$field_name ) : '';
		}

		// get values from location meta for fields with meta_key arg
		if ( ! empty( $field['meta_key'] ) ) {

			$field_name = 'gmw_location_form[location_meta][' . $field['meta_key'] . ']';

			if ( ! empty( $this->saved_locationmeta[ $field['meta_key'] ] ) ) {
				$field['value'] = $this->saved_locationmeta[ $field['meta_key'] ];
			}
		}

		// generate label if not exists
		$field['label'] = ! empty( $field['label'] ) ? $field['label'] : '';

		// generate default ID if not exists
		$field['id'] = ! empty( $field['id'] ) ? $field['id'] : 'gmw-lf-' . $slug;

		$extra_field = ! in_array( $fields_group, array( 'address', 'coordinates', 'location', 'actions' ) ) ? 'gmw-lf-extra-field' : '';
		$loc_meta    = ! empty( $field['meta_key'] ) ? 'location-meta' : '';
		$chosen      = 'select' == $field['type'] ? 'gmw-smartbox' : '';

		// generate class attribute
		$class          = 'gmw-lf-field ' . $loc_meta . ' ' . $extra_field . ' group_' . $fields_group . ' ' . $field['type'] . '-field ' . $slug . ' ' . $chosen;
		$field['class'] = ! empty( $field['class'] ) ? $class . ' ' . $field['class'] : $class;

		// placeholder attribute
		$field['placeholder'] = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';

		// generate the field attributes
		$attributes = array();

		if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {

			foreach ( $field['attributes'] as $attribute_name => $attribute_value ) {

				$attributes[] = $attribute_name . '="' . $attribute_value . '"';

				$field['attributes'] = implode( ' ', $attributes );
			}
		}

		// include form field file
		include( $this->template_folders['form_fields'] . $field['type'] . '-field.php' );
	}

	/**
	 * Create hidden submission fields
	 *
	 * This fields are hidden and holding the location information.
	 *
	 * Once for submitted the plugin will grab the location information
	 *
	 * from these hidden fields.
	 *
	 * @since 3.0
	 *
	 * @return $output html of the hidden fields
	 */
	protected function submission_fields() {

		// check for locaiton ID
		$location_id = $this->saved_location ? (int) $this->saved_location->ID : '';

		$output = '<div class="gmw-lf-submission-fields-wrapper">';

		// add few more hidden fields with location data
		$output .= '<input type="hidden" class="gmw-lf-submission-field location-id" id="gmw_lf_location_id" name="gmw_location_form[ID]" 		   value="' . absint( $location_id ) . '" />';
		$output .= '<input type="hidden" class="gmw-lf-submission-field object_type" id="gmw_lf_object_type" name="gmw_location_form[object_type]" value="' . esc_attr( $this->object_type ) . '" />';
		$output .= '<input type="hidden" class="gmw-lf-submission-field object-id" id="gmw_lf_object_id" name="gmw_location_form[object_id]"   value="' . absint( $this->args['object_id'] ) . '" />';
		$output .= '<input type="hidden" class="gmw-lf-submission-field user-id" id="gmw_lf_user_id" name="gmw_location_form[user_id]" 	   value="' . absint( $this->args['user_id'] ) . '" />';
		$output .= '<input type="hidden" class="gmw-lf-submission-field auto-update" id="gmw_lf_auto_update" name="gmw_location_form[auto_update]" value="' . absint( $this->args['update_on_submission'] ) . '" />';
		$output .= '<input type="hidden" class="gmw-lf-submission-field action" id="gmw_lf_action" name="gmw_action" 		      	 	   value="update_lf_location" />';
		$output .= '<input type="hidden" class="gmw-lf-submission-field gmw-slug" id="gmw_lf_slug" name="gmw_lf_slug" value="' . esc_attr( $this->slug ) . '" />';
		$output .= '<input type="hidden" class="gmw-lf-submission-field gmw-lf-stand-alone" id="gmw_lf_stand_alone" name="gmw_lf_stand_alone" value="' . absint( $this->args['stand_alone'] ) . '" />';

		// the default location fields
		$address_fields = apply_filters(
			'gmw_lf_submission_fields', array(
				'latitude',
				'longitude',
				'street_number',
				'street_name',
				'street',
				'premise',
				'neighborhood',
				'city',
				'county',
				'region_name',
				'region_code',
				'postcode',
				'country_name',
				'country_code',
				'address',
				'formatted_address',
				'place_id',
			), $this
		);

		$excluded_fields = false;

		/*if ( ! empty( $this->args['exclude_fields'] ) ) {

			$excluded_fields = array_intersect( array( 'street','city', 'postcode' ), $this->args['exclude_fields'] );

			if ( in_array( 'street', $excluded_fields ) ) {
				$excluded_fields[] = 'street_name';
				$excluded_fields[] = 'street_number';
			}
		} */

		// loop through and create submission fields
		foreach ( $address_fields as $field ) {

			$group = ( 'latitude' == $field || 'longitude' == $field ) ? 'group_coordinates' : 'group_address';

			// check for user's current position to automatically fill out the location form if location does not exists in databse.
			if ( $this->args['default_user_location'] && empty( $this->saved_location ) && ! empty( $this->user_location ) ) {

				// get field value from user current location if exists
				$value = isset( $this->user_location->$field ) ? $this->user_location->$field : '';

			} else {

				// get fields value from saved location if exists
				$value = ( ! empty( $this->saved_location->$field ) ) ? stripslashes( $this->saved_location->$field ) : '';
			}

			$disable = '';

			/*if ( $excluded_fields != false ) {
				if ( in_array( $field, $excluded_fields ) ) {
					$disable = 'disabled="disabled"';
				}
			} */

			$field = esc_attr( $field );

			$output .= '<input type="hidden" class="gmw-lf-submission-field ' . $field . ' ' . $group . '" id="gmw_lf_' . $field . '" name="gmw_location_form[' . $field . ']"  value="' . esc_attr( stripslashes( $value ) ) . '">';
		}

		wp_nonce_field( 'gmw_lf_update_location', 'gmw_lf_update_location' );

		$output .= '</div>';

		return $output;
	}

	/**
	 * display the location form
	 *
	 * The function will include the location form file that was specified.
	 *
	 * The plugin will first look for the file in the theme/child theme's folder and if found it will load it from there.
	 *
	 * Otherwise the plugin's default location form will be loaded.
	 *
	 * @since 3.0
	 *
	 * @return void - include a PHP template file.
	 *
	 */
	public function display_form() {

		$gmw_location_form = $this;

		// form wrapper
		echo '<div id="gmw-location-form-wrapper" class="gmw-location-form-wrapper ' . esc_attr( $gmw_location_form->args['form_template'] ) . '">';

		do_action( 'gmw_before_location_form', $gmw_location_form );

		// wrap within a form if needed
		if ( $gmw_location_form->args['stand_alone'] ) {
			echo '<form id="gmw-location-form" class="gmw-lf-form" name="gmw_lf_location_form" method="post">';
		}

		// hidden location fields
		echo $gmw_location_form->submission_fields();

		// include the form template
		include( $gmw_location_form->template_folders['form_templates'] . $gmw_location_form->args['form_template'] . '.php' );

		if ( $gmw_location_form->args['stand_alone'] ) {
			echo '</form>';
		}

		echo '</div>';
	}

	/**
	 * Ajax submission when updating location on submission
	 *
	 * @return [type] [description]
	 */
	public static function ajax_submission() {

		//verify ajax nonce
		if ( ! check_ajax_referer( 'gmw_lf_update_location', 'security', false ) ) {

			//abort if bad nonce
			wp_die( __( 'Trying to cheat or something?', 'geo-my-wp' ), __( 'Error', 'geo-my-wp' ), array( 'response' => 403 ) );
		}

		// parse the form values
		parse_str( $_POST['formValues'], $form_values );

		return $form_values;
	}

	/**
	 * Page load submission when updating location on submission
	 *
	 * @return [type] [description]
	 */
	public static function page_load_submission() {

		// if in admin dashboard
		if ( IS_ADMIN ) {

			// verify admin nonce
			check_admin_referer( 'gmw_lf_update_location', 'gmw_lf_update_location' );

			// when updating location in front-end
		} else {

			// verify nonce
			if ( empty( $_POST ) || ! isset( $_POST['gmw_lf_update_location'] ) || ! wp_verify_nonce( $_POST['gmw_lf_update_location'], 'gmw_lf_update_location' ) ) {

				// abort if bad nonce
				wp_die( __( 'Trying to cheat or something?', 'geo-my-wp' ), __( 'Error', 'geo-my-wp' ), array( 'response' => 403 ) );
			}
		}

		// form values
		return $_POST;
	}

	/**
	 * Update location on submission
	 *
	 * @return void
	 */
	public static function update_location_on_submission() {

		// if updating location via ajax
		if ( defined( 'DOING_AJAX' ) ) {

			//parse the form values
			parse_str( $_POST['formValues'], $form_values );

			// when updating location via page load
		} else {

			//form values
			$form_values = $_POST;
		}

		if ( ! empty( $form_values['gmw_location_form']['auto_update'] ) ) {
			self::update_location();
		} else {
			return;
		}
	}

	/**
	 * Update location.
	 *
	 * @return [type] [description]
	 */
	public static function update_location( $object_type = false, $object_id = false ) {

		//if saving location via ajax
		if ( defined( 'DOING_AJAX' ) ) {

			//parse the form values
			$form_values = self::ajax_submission();

			//when saving location via page load
		} else {
			$form_values = self::page_load_submission();
		}

		if ( empty( $form_values ) ) {
			return;
		}

		// Submitted location values
		$location = $form_values['gmw_location_form'];

		// abort if no location found
		if ( empty( $location['latitude'] ) || empty( $location['longitude'] ) ) {
			return;
		}

		// get the object type
		$location['object_type'] = ! empty( $object_type ) ? $object_type : $location['object_type'];

		// get the object ID
		$location['object_id'] = ! empty( $object_id ) ? $object_id : $location['object_id'];

		// get the location ID if exists
		//$location_id = ! empty( $location['ID'] ) ? $location['ID'] : 0;

		// location meta
		$location_meta = ! empty( $location['location_meta'] ) ? $location['location_meta'] : array();

		// map icon if exists
		$location['map_icon'] = ! empty( $location['map_icon'] ) ? $location['map_icon'] : '_default.png';

		$location_args = array(
			'object_type'       => $location['object_type'],
			'object_id'         => (int) $location['object_id'],
			'user_id'           => (int) $location['user_id'],
			'parent'            => 0,
			'status'            => 1,
			'featured'          => 0,
			'title'             => ! empty( $location['title'] ) ? $location['title'] : '',
			'latitude'          => $location['latitude'],
			'longitude'         => $location['longitude'],
			'street_number'     => $location['street_number'],
			'street_name'       => $location['street_name'],
			'street'            => $location['street'],
			'premise'           => $location['premise'],
			'neighborhood'      => $location['neighborhood'],
			'city'              => $location['city'],
			'county'            => $location['county'],
			'region_name'       => $location['region_name'],
			'region_code'       => $location['region_code'],
			'postcode'          => $location['postcode'],
			'country_name'      => $location['country_name'],
			'country_code'      => $location['country_code'],
			'address'           => $location['address'],
			'formatted_address' => $location['formatted_address'],
			'place_id'          => $location['place_id'],
			'map_icon'          => $location['map_icon'],
		);

		// filter location args before updating location
		$location_args = apply_filters( 'gmw_lf_location_args_before_location_updated', $location_args, $location, $form_values );
		$location_args = apply_filters( 'gmw_lf_' . $location['object_type'] . '_location_args_before_location_updated', $location_args, $location, $form_values );

		// run custom functions before updating location
		do_action( 'gmw_lf_before_location_updated', $location, $location_args, $form_values );
		do_action( 'gmw_lf_before_' . $location['object_type'] . '_location_updated', $location, $location_args, $form_values );

		// save location
		$location['ID'] = gmw_update_location_data( $location_args );

		// filter location meta before updating
		$location_meta = apply_filters( 'gmw_lf_location_meta_before_location_updated', $location_meta, $location, $form_values );
		$location_meta = apply_filters( 'gmw_lf_' . $location['object_type'] . '_location_meta_before_location_updated', $location_meta, $location, $form_values );

		// save location meta
		if ( ! empty( $location_meta ) ) {

			foreach ( $location_meta as $meta_key => $meta_value ) {

				if ( ! is_array( $meta_value ) ) {
					$meta_value = trim( $meta_value );
				}

				if ( empty( $meta_value ) || ( is_array( $meta_value ) && ! array_filter( $meta_value ) ) ) {
					gmw_delete_location_meta( $location['ID'], $meta_key );
				} else {
					gmw_update_location_meta( $location['ID'], $meta_key, $meta_value );
				}
			}
		}

		//do something after location updated
		do_action( 'gmw_lf_after_location_updated', $location, $form_values );
		do_action( 'gmw_lf_after_' . $location['object_type'] . '_location_updated', $location, $form_values );

		//send the location ID back to AJAX call
		if ( defined( 'DOING_AJAX' ) ) {

			wp_send_json( ! empty( $location['ID'] ) ? $location['ID'] : false );

		} else {

			if ( IS_ADMIN ) {

			} else {

				// reload page to prevent re-submission
				wp_redirect( $_SERVER['REQUEST_URI'] );

				// exist only if stand alone form. Otherwise, we need
				// to allow the original form to process
				if ( $_POST['gmw_lf_stand_alone'] ) {
					exit;
				}
			}
		}
	}

	/**
	 * Delete location from database
	 *
	 * @return [type] [description]
	 */
	public static function delete_location() {

		// verify AJAX nonce
		if ( ! check_ajax_referer( 'gmw_lf_update_location', 'security', false ) ) {

			//abort if bad nonce
			wp_die( __( 'Trying to cheat or something?', 'geo-my-wp' ), __( 'Error', 'geo-my-wp' ), array( 'response' => 403 ) );
		}

		// parse the form values
		parse_str( $_POST['formValues'], $form_values );

		// get the location values
		$location = $form_values['gmw_location_form'];

		// get the location ID if exists
		$location['ID'] = ! empty( $location['ID'] ) ? $location['ID'] : 0;

		// abort if there is no location ID to delete
		if ( empty( $location['ID'] ) ) {
			die();
		}

		// do something before location deleted
		do_action( 'gmw_lf_before_location_deleted', $location, $form_values );
		do_action( 'gmw_lf_before_' . $location['object_type'] . '_location_deleted', $location, $form_values );

		$location_id = GMW_Location::delete_by_id( $location['ID'], true );

		// do something after location deleted
		do_action( 'gmw_lf_after_location_deleted', $location, $form_values );
		do_action( 'gmw_lf_after_' . $location['object_type'] . '_location_deleted', $location, $form_values );

		// send the location ID back to AJAX call
		wp_send_json( ! empty( $location_id ) ? $location_id : false );
	}
}

/**
 * Process Location form submission
 * @return void
 */
function gmw_update_lf_location() {
	GMW_Location_Form::update_location_on_submission();
}
//action create via gmw_process_actions function for page load submission
add_action( 'gmw_update_lf_location', 'gmw_update_lf_location' );

//process form submission via ajax
add_action( 'wp_ajax_gmw_lf_update_location', 'gmw_update_lf_location' );
add_action( 'wp_ajax_gmw_lf_delete_location', array( 'GMW_Location_Form', 'delete_location' ) );
