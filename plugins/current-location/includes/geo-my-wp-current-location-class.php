<?php

/**
 * User's current location class
 *
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Current_location {

	/**
	 * @since 1.0
	 * Public $args
	 * Array of Incoming arguments
	 *
	 */
	protected $args = array(
			'element_id'				=> 0,
			'elements'					=> 'username,address,map,location_form',
			'location_form_trigger' 	=> 'Get your current location',
			'address_field_placeholder'	=> 'Enter address',
			'address_fields' 			=> 'city,country',
			'address_label'   			=> 'Your location',	
			'user_greeting' 			=> 'Hello',
			'guest_greeting' 			=> 'Hello, guest!',
			'map_height' 				=> '200px',
			'map_width'  				=> '200px',
			'map_marker'				=> 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
			'map_type'					=> 'ROADMAP',
			'zoom_level' 				=> 8,
			'scrollwheel_map_zoom'		=> 1,
	);

	/**
	 * @since 1.0
	 * Public $args
	 * Array for child class to extends the main array above
	 */
	protected $ext_args = array();
	
	/**
	 * @since 2.6.1
	 * Public $user_position
	 * array contains the current user position if exists
	 */
	public  $user_position = array(
			'exists' 	=> false,
			'lat'		=> false,
			'lng'		=> false,
			'address'	=> false
	);

	/**
	 * @since 2.6.1
	 * Public $elements
	 * Displayed name
	 */
	public $displayed_name;

	/**
	 * __constructor
	 */
	public function __construct( $atts=array() ) {
		
		//extend the default args
		$this->args = array_merge( $this->args, $this->ext_args );
		
		//get the shortcode atts
		$this->args = shortcode_atts( $this->args, $atts );
		
		//set random element id if not exists
		$this->args['element_id'] = ( !empty( $this->args['element_id'] ) ) ? $this->args['element_id'] : rand( 550, 1000 );
		
		//get elements to display
		$this->elements_value = explode( ',', $this->args['elements'] );
		
		//check that we have at least one element to display
		if ( empty( $this->elements_value ) )
			return;
		
		//global cl maps holder
		global $gmw_current_location_maps;
		if ( empty( $gmw_current_location_maps ) ) {
			$gmw_current_location_maps = array();
		}
		
		//check for the user's current position
		if ( !empty( $_COOKIE['gmw_lat'] ) && !empty( $_COOKIE['gmw_lng'] ) ) {

			$this->user_position['exists']  	 = true;
			$this->user_position['lat'] 		 = urldecode( $_COOKIE['gmw_lat'] );
			$this->user_position['lng'] 		 = urldecode( $_COOKIE['gmw_lng'] );
			$this->user_position['full_address'] = ( !empty( $_COOKIE['gmw_formatted_address'] ) ) ? urldecode( $_COOKIE['gmw_formatted_address'] ) : urldecode( $_COOKIE['gmw_address'] );
			$this->user_position['address'] 	 = false;

			//get address based on shortcode attributes
			if ( !empty( $this->args['address_fields'] ) ) {
					
				if ( $this->args['address_fields'] == 'address' ) {
					$this->user_position['address'] = $this->user_position['full_address'];
				} else {
					foreach ( explode( ',', $this->args['address_fields'] ) as $field ) {
						if ( !empty( $_COOKIE['gmw_' . $field] ) ) {
							$CurrentAddress[] = urldecode( $_COOKIE['gmw_' . $field] );
						}
					}
					$this->user_position['address'] = implode(' ', $CurrentAddress);
				}
			}
		}

		//fun enqueue script to localize the maps scripts in the footer
		add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * display the user name/guest
	 */
	public function displayed_name() {
			
		if ( is_user_logged_in() ) {

			global $current_user;
			get_currentuserinfo();

			$this->displayed_name = $this->args['user_greeting'].' '.$current_user->user_login.'!';
		} else {
			$this->displayed_name = $this->args['guest_greeting'];
		}
			
		$output = '<div class="gmw-cl-element gmw-cl-welcome-message">'.$this->displayed_name.'</div>';
		$output = apply_filters( 'gmw_cl_displayed_name', $output, $this->args, $this->user_position );
		
		return $output;
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Display location with hyperlink trigger for the locaiton form
	 */
	public function address() {
			
		$output  = '<div class="gmw-cl-element gmw-cl-address-wrapper">';
		
		if ( !empty( $this->args['address_label'] ) ) {
			$output .= '<span class="gmw-cl-title">'.$this->args['address_label'].'</span>';
		}

		$output .= 	'<div class="gmw-cl-location location-exists">';
		$output .= 		'<i class="gmw-location-icon fa fa-map-marker"></i>';
		$output .= 		$this->user_position['address'];
		$output .=  '</div>'; 
		$output .= '</div>';
			
		$output = apply_filters( 'gmw_cl_address', $output, $this->args, $this->user_position );

		return $output;
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Create map element
	 */
	public function map() {

		if ( !$this->user_position['exists'] )
			return;
		
		//map arguments
		$gmw = array(
				'ID' 			=> $this->args['element_id'],
				'prefix'		=> 'cl',
				'addon'			=> 'current_location',
				'results_map' 	=> array(
						'map_width'  => $this->args['map_width'],
						'map_height' => $this->args['map_height'],
				),
				'args'			=> $this->args,
				'user_position' => $this->user_position
		);
		
		//get the map element
		$output = gmw_get_results_map( $gmw );
		
		//map options - pass to via global map object to JavaScript which will display the map
		$mapArgs = array(
				'mapId' 	 		=> $this->args['element_id'],
				'mapType'			=> 'current_location',
				'prefix'			=> 'cl',
				'zoomLevel'			=> $this->args['zoom_level'],
				'mapOptions'		=> array(
						'mapTypeId'			=> $this->args['map_type'],
						'scrollwheel'	 	=> ( !empty( $this->args['scrollwheel_map_zoom'] ) ) ? true : false,
						'mapTypeControl' 	=> false,
						'streetViewControl' => false,
						'panControl'		=> false
				),
				'userPosition'		=> array(
						'lat'		=> $this->user_position['lat'],
						'lng'		=> $this->user_position['lng'],
						'address' 	=> $this->user_position['address'],
						'mapIcon'	=> $this->args['map_marker'],
				)
		);
		
		gmw_new_map_element( $mapArgs );

	    return $output;
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Hidden form which holds the found address fields.
	 * The location fields will pass on form submission using $_POST.
	 * The location is than avaliable to be used with.
	 *
	 */
	private function hidden_form() {

		$address_fields = array( 'street_number', 'street_name', 'street', 'city', 'state', 'state_long', 'zipcode', 'country', 'country_long', 'address', 'formatted_address', 'lat', 'lng' );
			
		$output  = '<div id="gmw-cl-hidden-form-wrapper-'.$this->args['element_id'].'" style="display:none;">';
		$output .= '<form id="gmw-cl-hidden-form-'.$this->args['element_id'].'" method="post">';
		$output .= '<input type="hidden" id="gmw_cl_action" name="gmw_cl_action" value="post" />';
			
		foreach ( $address_fields as $field ) {
			$output .= "<input type=\"text\" id=\"gmw_cl_{$field}_{$this->args['element_id']}\" name=\"gmw_cl_location[{$field}]\" value=\"\" />";
		}
			
		$output .= wp_nonce_field( 'gmw_cl_nonce', 'gmw_cl_nonce' );
		$output .= '</form>';
		$output .= '</div>';
		
		return $output;
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Pop-up form template
	 */
	public function form_template() {

		$display = ( !empty( $this->user_position['exists'] ) ) ? 'style="display:none"' : '';

		$output  = '';
		$output .= "<div id=\"gmw-cl-form-wrapper-{$this->args['element_id']}\" class=\"gmw-cl-element gmw-cl-form-wrapper\">";

		if ( !empty( $this->args['location_form_trigger'] ) ) {
			$output .=  '<a href="#" class="gmw-cl-form-trigger" title="'.$this->args['location_form_trigger'].'">'.$this->args['location_form_trigger'].'</a></span>'; 
		}

		$output .= 	"<form id=\"gmw-cl-form-{$this->args['element_id']}\" name=\"gmw_cl_form\" onsubmit=\"return false\"  {$display}>";	
		
		$output .= 		"<div class=\"gmw-cl-address-input-wrapper\">";
		$output .=			"<i id=\"gmw-cl-locator-trigger-{$this->args['element_id']}\" class=\"gmw-cl-locator-trigger fa fa-location-arrow\"></i>";
		$output .= 			"<input type=\"text\" name=\"gmw_cl_address\" id=\"gmw-cl-address-input-{$this->args['element_id']}\" class=\"gmw-cl-address-input\" value=\"\" autocomplete=\"off\" placeholder=\"{$this->args['address_field_placeholder']}\" />";
		$output .= 			"<a href=\"#\" type=\"submit\" id=\"gmw-cl-form-submit-icon-{$this->args['element_id']}\" class=\"gmw-cl-form-submit-icon fa fa-search\"></a>";
		$output .= 		"</div>";

		$output .=		"<div id=\"gmw-cl-respond-wrapper-{$this->args['element_id']}\" style=\"display:none;\">";
		$output .= 			"<div id=\"gmw-cl-spinner-{$this->args['element_id']}\" class=\"gmw-cl-spinner\"><i class=\"fa fa-refresh fa-spin fa-1x fa-fw margin-bottom\"></i></div>";
		$output .= 			"<p id=\"gmw-cl-message-{$this->args['element_id']}\" class=\"gmw-cl-message\"></p>";
		$output .=		"</div>";
		$output .= 		"<input type=\"hidden\" class=\"gmw-cl-element-id\" value=\"{$this->args['element_id']}\" />";
		$output .= 	'</form>';
		$output .= '</div>';

		$output = apply_filters( 'gmw_cl_form_template', $output );

		$output .= $this->hidden_form();

		return $output;
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Enqueue the cl JavaScript file as well localize the maps object
	 *
	 */
	public function enqueue_scripts() {

		if ( !wp_script_is( 'gmw-cl-map', 'enqueue' ) ) {
			wp_enqueue_script( 'gmw-cl' );
		}
	}

	/**
	 * @since 2.6.1
	 * @access public
	 *
	 * Display all elements based on shortcode attributes
	 *
	 */
	public function display() {
				
		//ge telements to display
		$elements_value = explode( ',', $this->args['elements'] );	

		//check that we have at least one element to display
		if ( empty( $elements_value ) )
			return;
		
		$elements = array();
		
		//build the elements array
		$elements['element_wrap_start'] = '<div id="gmw-cl-wrapper-'.$this->args['element_id'].'" class="gmw-cl-wrapper">';	
		
		foreach ( $elements_value as $value ) {
			$elements[$value] = false;
		}		
		
		$elements['element_wrap_end'] = '</div>';

		if ( isset( $elements['username'] ) ) {
			$elements['username'] = $this->displayed_name();
		}
			
		if ( isset( $elements['address'] ) && !empty( $this->user_position['exists'] ) ) {
			$elements['address'] = $this->address();		
		} 

		if ( isset( $elements['location_form'] ) ) {
			$elements['location_form'] = $this->form_template();
		}

		if ( isset( $elements['map'] ) && $this->user_position['exists'] ) {
			$elements['map'] = $this->map();
		}

		$output = implode('', $elements);

		//display the element
		return apply_filters( 'gmw_cl_display_output', $output, $elements, $this->args, $this->user_position, get_current_user_id() );
	}
}

/**
 * Submit user current location on cl form submission
 * @param unknown_type $location
 */
function gmw_cl_submitted_location() {
	
	//check for cl form submission
	if ( empty( $_POST['gmw_cl_action'] ) )
		return;

	//varify nonce
	if ( empty( $_POST['gmw_cl_nonce'] ) || !wp_verify_nonce( $_POST['gmw_cl_nonce'], 'gmw_cl_nonce' ) )
		return;
		
	foreach ( $_POST['gmw_cl_location'] as $field => $value ) {
		
		unset( $_COOKIE["gmw_{$field}"]);
		setcookie( "gmw_{$field}", "", time()-300 );
		
		if ( !empty( $value ) ) {
			setcookie( "gmw_{$field}", stripslashes( $value ), strtotime( '+7 days' ), '/' );
		}
	}
	
	//do something with the information
	do_action( 'gmw_user_current_location_submitted', $_POST['gmw_cl_location'], get_current_user_id() );

	//reload page to prevent form resubmission
	wp_redirect( $_SERVER['REQUEST_URI'] );
	exit;
}
add_action( 'init', 'gmw_cl_submitted_location' );