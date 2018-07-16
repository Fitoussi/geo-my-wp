<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User's current location class
 *
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Current_Location {

	/**
	 * @since 1.0
	 * Public $args
	 * Array of Incoming arguments
	 *
	 */
	protected $args = array(
		'element_id'                => 0,
		'elements'                  => 'username,address,map,location_form',
		'location_form_trigger'     => 'Update your current location',
		'clear_location_trigger'    => 'Clear location',
		'address_field_placeholder' => 'Enter address',
		'address_fields'            => 'city,country',
		'address_label'             => '',
		'address_autocomplete'      => 1,
		'user_greeting'             => 'Hello',
		'guest_greeting'            => 'Hello, guest!',
		'map_height'                => '200px',
		'map_width'                 => '200px',
		'map_icon_url'              => '',
		'map_icon_size'             => '',
		'map_type'                  => 'ROADMAP',
		'zoom_level'                => 8,
		'scrollwheel_zoom'          => 1,
		'expand_on_load'            => 0,
		'ajax_update'               => 0,
		'loading_message'           => 'Retrieving your current location...',
	);

	/**
	 * @since 3.0
	 *
	 * Public $args
	 *
	 * Array for child class to extends default args above
	 */
	protected $ext_args = array();

	/**
	 * @since 2.6.1
	 *
	 * Public $user_position
	 *
	 * array contains the current user position if exists
	 */
	public $user_position = array(
		'exists'  => false,
		'lat'     => false,
		'lng'     => false,
		'address' => false,
	);

	/**
	 * @since 2.6.1
	 *
	 * Public $displayed_name
	 *
	 * Displayed name
	 */
	public $displayed_name;

	/**
	 * Current location status
	 *
	 * Indicates if current location object present on the page
	 *
	 * @var boolean
	 */
	static $current_location_enabled = false;

	/**
	 * __constructor
	 */
	public function __construct( $atts = array() ) {

		if ( isset( $atts['map_marker'] ) ) {

			$atts['map_icon_url'] = $atts['map_marker'];

			unset( $atts['map_marker'] );
		}

		// extend the default args
		$this->args = array_merge( $this->args, $this->ext_args );

		// get the shortcode atts
		$this->args = shortcode_atts( $this->args, $atts, 'gmw_current_location' );

		// set random element id if not provided
		$this->args['element_id'] = ! empty( $this->args['element_id'] ) ? $this->args['element_id'] : rand( 550, 1000 );

		// elements to generate
		$this->elements_value = explode( ',', $this->args['elements'] );

		// check that we have at least one element to display
		if ( empty( $this->elements_value ) ) {
			return;
		}

		// If icon size provided, make it an array.
		if ( ! empty( $this->args['map_icon_size'] ) ) {
			$this->args['map_icon_size'] = explode( ',', $this->args['map_icon_size'] );
		}

		// Default icon URL and size.
		if ( '' == $this->args['map_icon_url'] ) {

			$this->args['map_icon_url']  = GMW()->default_icons['user_location_icon_url'];

			// use default icon size if no size provided
			if ( '' == $this->args['map_icon_size'] ) {
				$this->args['map_icon_size'] = GMW()->default_icons['user_location_icon_size'];
			}
		}

		$this->current_location = gmw_get_user_current_location();

		// check for the user's current position in cookies
		if ( ! empty( $this->current_location ) ) {

			$this->user_position['exists']  = true;
			$this->user_position['lat']     = $this->current_location->lat;
			$this->user_position['lng']     = $this->current_location->lng;
			$this->user_position['address'] = false;

			// generate address based on shortcode attributes
			if ( ! empty( $this->args['address_fields'] ) ) {

				// if showing full address
				if ( 'address' == $this->args['address_fields'] ) {

					$this->user_position['address'] = ! empty( $this->current_location->formatted_address ) ? $this->current_location->formatted_address : '';

					// generate multiple address fields
				} else {

					foreach ( explode( ',', $this->args['address_fields'] ) as $field ) {

						if ( 'state' == $field ) {
							$field = 'region_code';
						}

						if ( 'country' == $field ) {
							$field = 'country_code';
						}

						if ( ! empty( $this->current_location->$field ) ) {
							$this->user_position['address'] .= $this->current_location->$field . ' ';
						}
					}
				}
			}

			// if user location not exists prevent ajax submission.
			// This is temporary untill we can generate map using ajax
		} else {
			$this->args['ajax_update'] = 0;
		}

		// enqueue script to localize the maps scripts in the footer
		add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * display the user name/guest
	 */
	public function displayed_name() {

		// when user is logged in
		if ( is_user_logged_in() ) {

			global $current_user, $wp_version;

			if ( version_compare( $wp_version, '4.5', '<' ) ) {
				get_currentuserinfo();
			} else {
				wp_get_current_user();
			}

			//wp_get_current_user()
			$this->displayed_name = $this->args['user_greeting'] . ' ' . $current_user->display_name . '!';

			$displayed = 'user';

			// otherwise, refer to as a guest
		} else {

			$current_user = false;

			$this->displayed_name = $this->args['guest_greeting'];

			$displayed = 'guest';
		}

		$output = '<div class="gmw-cl-element welcome-message ' . $displayed . '">' . esc_attr( $this->displayed_name ) . '</div>';
		$output = apply_filters( 'gmw_current_location_displayed_name', $output, $this->args, $current_user, $this->user_position );

		return $output;
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Display location with hyperlink trigger for the locaiton form
	 */
	public function address() {

		$output = '<div class="gmw-cl-element gmw-cl-address-wrapper">';

		if ( ! empty( $this->args['address_label'] ) ) {
			$output .= '<span class="gmw-cl-title">' . esc_html( $this->args['address_label'] ) . '</span>';
		}

		$output .= '<div class="gmw-cl-address location-exists">';
		$output .= '<i class="gmw-location-icon gmw-icon-location"></i>';
		$output .= '<span class="address-holder">' . stripslashes( esc_html( $this->user_position['address'] ) ) . '</span>';
		$output .= '</div>';
		$output .= '</div>';

		$output = apply_filters( 'gmw_current_location_address', $output, $this->args, $this->user_position );

		return $output;
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Create map element
	 */
	public function map() {

		if ( ! $this->user_position['exists'] ) {
			return;
		}

		//map args
		$map_args = array(
			'map_id'            => $this->args['element_id'],
			'map_type'          => 'current_location',
			'prefix'            => 'cl',
			'map_width'         => $this->args['map_width'],
			'map_height'        => $this->args['map_height'],
			'expand_on_load'    => $this->args['expand_on_load'],
			'init_visible'      => true,
			'hide_no_locations' => false,
		);

		// map options
		$map_options = array(
			'mapTypeId'         => $this->args['map_type'],
			'scrollwheel'       => ! empty( $this->args['scrollwheel_zoom'] ) ? true : false,
			'mapTypeControl'    => false,
			'streetViewControl' => false,
			'panControl'        => false,
			'zoom'              => $this->args['zoom_level'],
		);

		// user position
		$user_position = array(
			'lat'       => $this->user_position['lat'],
			'lng'       => $this->user_position['lng'],
			'address'   => $this->user_position['address'],
			'map_icon'  => $this->args['map_icon_url'],
			'icon_size' => $this->args['map_icon_size'],
		);

		return gmw_get_map( $map_args, $map_options, array(), $user_position );
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Pop-up form template
	 */
	public function form_template() {

		$display = ! empty( $this->user_position['exists'] ) ? 'style="display:none"' : '';

		$this->args['element_id'] = esc_attr( $this->args['element_id'] );
		$ajax_enabled             = esc_attr( $this->args['ajax_update'] );
		$autocomplete             = ! empty( $this->args['address_autocomplete'] ) ? 'gmw-address-autocomplete' : '';

		$output  = '';
		$output .= '<div id="gmw-cl-form-wrapper-' . $this->args['element_id'] . '" data-ajax_enabled="' . $ajax_enabled . '" class="gmw-cl-element gmw-cl-form-wrapper" data-element-id="' . $this->args['element_id'] . '">';

		$output .= '<form id="gmw-cl-form-' . $this->args['element_id'] . '" class="gmw-cl-form" onsubmit="return false;" name="gmw_cl_form" ' . $display . ' data-element-id="' . $this->args['element_id'] . '">';
		$output .= '<div class="gmw-cl-address-input-wrapper">';
		$output .= '<i id="gmw-cl-locator-trigger-' . $this->args['element_id'] . '" class="gmw-cl-locator-trigger gmw-icon-target-light" title="Get your current location"></i>';
		$output .= '<a href="#" id="gmw-cl-form-submit-' . $this->args['element_id'] . '" class="gmw-cl-form-submit gmw-icon-search" title="Search submit"></a>';
		$output .= '<input type="text" name="gmw_cl_address" id="gmw-cl-address-input-' . $this->args['element_id'] . '" class="gmw-cl-address-input ' . $autocomplete . '" value="" autocomplete="off" placeholder="' . esc_attr( $this->args['address_field_placeholder'] ) . '" />';
		$output .= '</div>';

		$output .= '<div id="gmw-cl-respond-wrapper-' . $this->args['element_id'] . '" style="display:none;" class="gmw-cl-respond-wrapper">';
		$output .= '<p id="gmw-cl-message-' . $this->args['element_id'] . '" data-loading_message="' . esc_html( $this->args['loading_message'] ) . '" class="gmw-cl-message"></p>';
		$output .= '</div>';
		$output .= '<input type="hidden" class="gmw-cl-element-id" value="' . $this->args['element_id'] . '" />';
		$output .= '</form>';

		if ( ! empty( $this->current_location ) ) {

			if ( ! empty( $this->args['location_form_trigger'] ) ) {

				$update_text = esc_attr( $this->args['location_form_trigger'] );

				$output .= '<a href="#" class="gmw-cl-form-trigger" title="' . $update_text . '"><i class="gmw-icon-spin3"></i>' . $update_text . '</a>';
			}

			if ( ! empty( $this->args['clear_location_trigger'] ) ) {

				$clear_text = esc_attr( $this->args['clear_location_trigger'] );

				$output .= '<a href="#" class="gmw-cl-clear-location-trigger" title="' . $clear_text . '"><i class="gmw-icon-cancel-circled"></i>' . $clear_text . '</a>';
			}
		}

		$output .= '</div>';

		$output = apply_filters( 'gmw_cl_form_template', $output );

		return $output;
	}

	/**
	 * @since 2.6.1
	 *
	 * @access public
	 *
	 * Enqueue the cl JavaScript file as well localize the maps object
	 *
	 */
	public function enqueue_scripts() {

		// load gmw main script and Google Maps API
		if ( ! wp_script_is( 'gmw-current-location', 'enqueue' ) ) {

			// generate hidden form only once
			echo self::current_location_fields();

			// enqueue scripts
			//wp_enqueue_script( 'gmw-current-location' );
			wp_localize_script( 'gmw', 'gmw_cl_nonce', wp_create_nonce( 'gmw_current_location_nonce' ) );
		}
	}

	/**
	 * @since 2.6.1
	 *
	 * @access public
	 *
	 * Display all elements based on shortcode attributes
	 */
	public function output() {

		// get elements to display
		$elements_value = explode( ',', $this->args['elements'] );

		// check that we have at least one element to display
		if ( empty( $elements_value ) ) {
			return;
		}

		self::$current_location_enabled = true;

		$elements = array();

		//build the elements array
		$elements['element_wrap_start'] = '<div id="gmw-current-location-wrapper-' . esc_attr( $this->args['element_id'] ) . '" class="gmw-current-location-wrapper">';

		foreach ( $elements_value as $value ) {
			$elements[ $value ] = false;
		}

		$elements['element_wrap_end'] = '</div>';

		if ( isset( $elements['username'] ) ) {
			$elements['username'] = $this->displayed_name();
		}

		if ( isset( $elements['address'] ) && ! empty( $this->user_position['exists'] ) ) {
			$elements['address'] = $this->address();
		}

		if ( isset( $elements['location_form'] ) ) {
			$elements['location_form'] = $this->form_template();
		}

		if ( isset( $elements['map'] ) && $this->user_position['exists'] ) {
			$elements['map'] = $this->map();
		}

		$output = implode( '', $elements );

		// enqueue main script if not loaded already.
		if ( ! wp_script_is( 'gmw', 'enqueued' ) ) {
			wp_enqueue_script( 'gmw' );
		}

		// display the element
		return apply_filters( 'gmw_cl_display_output', $output, $elements, $this->args, $this->user_position, get_current_user_id() );
	}

	/**
	 * Current location hidden fields
	 *
	 * @return [type] [description]
	 */
	public static function current_location_fields() {

		$address_fields = array(
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
			'lat',
			'lng',
		);

		$output = '<form id="gmw-current-location-hidden-form" method="post" style="display:none;">';

		$output .= '<input type="hidden" id="gmw_cl_action" name="gmw_action" value="current_location_submit" />';

		foreach ( $address_fields as $field ) {
			$output .= '<input type="hidden" id="gmw_cl_' . $field . '" name="gmw_cl_location[' . $field . ']" value="" />';
		}

		$output .= wp_nonce_field( 'gmw_cl_nonce', 'gmw_cl_nonce' );
		$output .= '</form>';

		return $output;
	}

	/**
	 * Update current location in cookies
	 *
	 * @param  [type] $current_location [description]
	 * @return [type]                   [description]
	 */
	public static function update_cookies( $current_location, $ajax = false ) {

		$address_fields = array(
			'street',
			'city',
			'region_name',
			'region_code',
			'postcode',
			'country_name',
			'country_code',
			'address',
			'formatted_address',
			'lat',
			'lng',
		);

		$cache = (object) array();

		// save location fields
		foreach ( $address_fields as $field ) {

			// clear cookie
			unset( $_COOKIE[ "gmw_ul_{$field}" ] );
			setcookie( "gmw_ul_{$field}", '', time() - 300 );

			// save new value if exists
			if ( ! empty( $current_location[ $field ] ) ) {

				$cache->$field = $current_location[ $field ];

				setcookie( "gmw_ul_{$field}", sanitize_text_field( stripslashes( $current_location[ $field ] ) ), strtotime( '+7 days' ), '/' );
			} else {
				$cache->$field = '';
			}
		}

		// do something with the information
		do_action( 'gmw_user_current_location_submitted', $current_location, get_current_user_id(), $ajax );

		// save user location in cache
		wp_cache_set( 'gmw_user_current_location', $cache, '', 86400 );

		if ( $ajax ) {

			// done
			return wp_send_json( ! empty( $current_location ) ? $current_location : array() );

		} else {

			//reload page to prevent form resubmission
			wp_redirect( $_SERVER['REQUEST_URI'] );

			exit;
		}
	}

	/**
	 * update current location
	 *
	 * @return [type] [description]
	 */
	public static function page_load_update_location() {

		// Abort if location not found or nonce not verified
		if ( empty( $_POST['gmw_cl_location'] ) || empty( $_POST['gmw_cl_nonce'] ) || ! wp_verify_nonce( $_POST['gmw_cl_nonce'], 'gmw_cl_nonce' ) ) {
			//reload page to prevent form resubmission
			wp_redirect( $_SERVER['REQUEST_URI'] );

			exit;
		}

		// update cookies
		self::update_cookies( $_POST['gmw_cl_location'], false );
	}

	/**
	 * Update Current Location Data.
	 * 
	 * @return [type] [description]
	 */
	public static function ajax_update_location() {

		//verify AJAX nonce
		if ( ! check_ajax_referer( 'gmw_current_location_nonce', 'security', false ) ) {

			//abort if bad nonce
			wp_die( __( 'Trying to cheat or something?', 'geo-my-wp' ), __( 'Error', 'geo-my-wp' ), array( 'response' => 403 ) );
		}

		//parse the form values
		parse_str( $_POST['form_values'], $form_values );

		// update cookies
		self::update_cookies( $form_values['gmw_cl_location'], true );
	}
}
add_action( 'gmw_current_location_submit', array( 'GMW_Current_location', 'page_load_update_location' ) );
add_action( 'wp_ajax_gmw_update_current_location', array( 'GMW_Current_location', 'ajax_update_location' ) );
add_action( 'wp_ajax_nopriv_gmw_update_current_location', array( 'GMW_Current_location', 'ajax_update_location' ) );
