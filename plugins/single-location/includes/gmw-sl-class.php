<?php
// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

/**
 * GMW_Single_Location Class
*
* Core class for displaying location information of an item ( post, member, group.. )
* You can extend this class based on your needs.
*
* @version 1.0
* @author Eyal Fitoussi
* @since 2.6.1
*/
class GMW_Single_Location {

	/**
	 * @since 2.6.1
	 * Public $args
	 * Array of Incoming arguments
	 */
	protected $args = array(
		'element_id'			=> 0,
		'item_id'         		=> 0,
		'elements'				=> 0,
		'item_type'				=> 'post',
		'address_fields'		=> 'address',
		'units'					=> 'm',
		'map_height'      		=> '250px',
		'map_width'       		=> '250px',
		'map_type'        		=> 'ROADMAP',
		'zoom_level'      		=> 'auto',
		'scrollwheel_map_zoom'	=> 1,
		'expand_map_on_load'	=> 0,
		'item_map_icon'			=> 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
		'user_map_icon'   	  	=> 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
		'user_info_window'	  	=> 'Your Location',
		'item_info_window'		=> 'title,address,distance',
		'no_location_message'   => 0,
		'is_widget'				=> 0,
		'widget_title'			=> 0
	);

	/**
	 * @since 2.6.1
	 * Public $args
	 * Array for child class to extends the main array above
	 */
	protected $ext_args = array();
	
	/**
	 * @since 2.6.1
	 * Public $item_info
	 * Object contains the item location information
	 */
	public $item_info;

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
	 * Public $this->elements
	 * array contains the elements to be output
	 */
	public $elements = array();

	/**
	 * @since 2.6.1
	 * Public $labels
	 * array of labels that can be filtered
	 */
	public $labels = array();
	
	/**
	 * @since 2.6.1
	 * @access public
	 * display the title of an item
	 */
	public function title() {}
	
	/**
	 * @since 2.6.1
	 * @access public
	 * display additional information
	 */
	public function additional_info() {}
	
	/**
	 * @since 2.6.1
	 * @access public
	 * display no location message
	 */
	public function no_location_message() {}
	
	/**
	 * Constaruct
	 * @param unknown_type $args
	 */
	public function __construct( $atts=array() ) {
		
		//extend the default args
		$this->args = array_merge( $this->args, $this->ext_args );
		
		//get the shortcode atts
		$this->args = shortcode_atts( $this->args, $atts );

		//set random element id if not exists
		$this->args['element_id'] = ( !empty( $this->args['element_id'] ) ) ? $this->args['element_id'] : rand( 100, 549 );
				
		//get elements to display
		$this->elements_value = explode( ',', $this->args['elements'] );
		
		//check that we have at least one element to display
		if ( empty( $this->elements_value ) )
			return;
		
		//get the item information
		$this->item_info = $this->item_info();
	
		//if item has no location, abort!
		if ( empty( $this->item_info ) ) {
			return ( !empty( $this->args['no_location_message'] ) ) ? $this->no_location_message() : false;
		}

		//labels
		$this->labels = $this->labels();
		$this->labels['live_directions'] = array(
				'start_point' 		=> __( 'Start point', 'GMW' ),
				'end_point' 		=> __( 'Destination point', 'GMW' ),
				'directions_label'	=> __( 'Directions', 'GMW' ),
				'from'				=> __( 'From:' , 'GMW' ),
				'to'				=> __( 'To:' , 'GMW' ),
				'driving'			=> __( 'Driving' , 'GMW' ),
				'walking'			=> __( 'Walking' , 'GMW' ),
				'bicycling'			=> __( 'Bicycling' , 'GMW' ),
				'transit'			=> __( 'Transit' , 'GMW' ),
				'units_label'		=> __( 'Distance Units' , 'GMW' ),
				'units_mi'			=> __( 'Miles' , 'GMW' ),
				'units_km'			=> __( 'Kilometers' , 'GMW' ),
				'avoid_label'		=> __( 'Avoid' , 'GMW' ),
				'avoid_hw'			=> __( 'highways' , 'GMW' ),
				'avoid_tolls'		=> __( 'Tolls' , 'GMW' ),
				'trigger_label'		=> __( 'Show directions', 'GMW' )
		);
		
		$this->labels = apply_filters( "gmw_sl_labels", $this->labels, $this->args );
		
		//check for the user's current position in URL. 
		if ( !empty( $_GET['lat'] ) && !empty( $_GET['lng'] ) ) {
		
			$this->user_position['exists']  = true;
			$this->user_position['lat'] 	= sanitize_text_field( $_GET['lat'] );
			$this->user_position['lng'] 	= sanitize_text_field( $_GET['lng'] );
			$this->user_position['address'] = sanitize_text_field( $_GET['address'] );
		
		//otherwise check for user location in cookies
		} elseif ( !empty( $_COOKIE['gmw_lat'] ) && !empty( $_COOKIE['gmw_lng'] ) ) {

			$this->user_position['exists']  = true;
			$this->user_position['lat'] 	= urldecode( $_COOKIE['gmw_lat'] );
			$this->user_position['lng'] 	= urldecode( $_COOKIE['gmw_lng'] );
			$this->user_position['address'] = urldecode( $_COOKIE['gmw_address'] );
		}
			
		//check if this is widget and we use widget title
		if ( $this->args['is_widget'] && !empty( $this->args['widget_title'] ) ) {
			$this->elements['widget_title'] = true;
		}
		
		//build the elements array
		$this->elements['element_wrap_start'] = '<div id="gmw-sl-wrapper-'.esc_attr( $this->args['element_id'] ).'" class="gmw-sl-wrapper gmw-sl-'.esc_attr( $this->args['item_type'] ).'-wrapper gmw-single-'.esc_attr( $this->args['item_type'] ).'-sc-wrapper">';
		
		foreach ( $this->elements_value as $value ) {
			$this->elements[$value] = false;
		}
		
		$this->elements['element_wrap_end'] = '</div>';
	}

	/**
	 * 
	 * Get address
	 * 
	 * @since 2.6.1
	 * @access public
	 * 
	 * The address of the displayed item
	 */
	public function address() {
	
		//if item has no location, abort!
		if ( empty( $this->item_info ) ) {
			return ( !empty( $this->args['no_location_message'] ) ) ? $this->no_location_message() : false;
		}
		
		//get the full address
        if ( empty( $this->args['address_fields'] ) || $this->args['address_fields'] == 'address' ) {
            $address = ( !empty( $this->item_info->formatted_address ) ) ? $this->item_info->formatted_address : $this->item_info->address;
        } else {
        	
        	$this->args['address_fields'] = ( !is_array( $this->args['address_fields'] ) ) ? explode( ',', $this->args['address_fields'] ) : $this->args['address_fields'];
        	
            $address_array = array();

            foreach ( $this->args['address_fields'] as $field ) {
                $address_array[] = $this->item_info->$field;
            }
            
            $address = implode(' ', $address_array);
        }

        $output = '<div class="gmw-sl-address-wrapper gmw-sl-element"><i class="gmw-location-icon fa fa-map-marker"></i><span class="address">'.esc_attr( stripslashes( $address ) ).'</span></div>';
			
		return apply_filters( 'gmw_sl_address', $output, $address, $this->args, $this->item_info, $this->user_position );
	}
	
	/**
	 * 
	 * Show Distance
	 * 
	 * @since 2.6.1
	 * @access public
	 * 
	 * Get the distance betwwen the user's position to the item being displayed 
	 */
	public function distance() {

		//if item has no location, abort!
		if ( empty( $this->item_info ) ) {
			return ( !empty( $this->args['no_location_message'] ) ) ? $this->no_location_message() : false;
		}
		
		if ( !$this->user_position['exists'] )
			return;
			
		$theta 	  = $this->user_position['lng'] - $this->item_info->long;
		$distance = sin( deg2rad( $this->user_position['lat']  ) ) * sin( deg2rad( $this->item_info->lat ) ) +  cos( deg2rad( $this->user_position['lat'] ) ) * cos( deg2rad( $this->item_info->lat) ) * cos( deg2rad( $theta ) );
		$distance = acos( $distance );
		$distance = rad2deg( $distance );
		$miles 	  = $distance * 60 * 1.1515;

		if ( $this->args['units'] == "k" ) {
			$distance = ( $miles * 1.609344 );
			$units	  = 'km';
		} else {
			$distance = ( $miles * 0.8684 );
			$units	  = 'mi';
		}

		$distance = round( $distance, 2 );
		
		$output = '<div id="gmw-sl-distance-wrapper-'.esc_attr( $this->args['element_id'] ).'" class="gmw-sl-distance-wrapper gmw-sl-element gmw-sl-'.esc_attr( $this->args['item_type'] ).'-distance-wrapper distance-wrapper"><i class="gmw-distance-icon fa fa-compass"></i><span class="label">'.esc_attr( $this->labels['distance_label'] ). '</span> <span>'.$distance.' '.$units.'</span></div>';
			
		return apply_filters( 'gmw_sl_distance', $output, $distance, $units, $this->args, $this->item_info, $this->user_position );
	}

	/**
	 * Map element
	 * @since 2.6.1
	 * @access public
	 */
	public function map() {

		//if item has no location, abort!
		if ( empty( $this->item_info ) ) {
			return ( !empty( $this->args['no_location_message'] ) ) ? $this->no_location_message() : false;
		}
				
		//map arguments
		$gmw = array(
				'ID' 				 => $this->args['element_id'],
				'prefix'			 => 'sl',
				'addon'				 => 'single_location',
				'results_map' 		 => array(
						'map_width'  => $this->args['map_width'],
						'map_height' => $this->args['map_height'],
				),
				'args'				 => $this->args,
				'user_position' 	 => $this->user_position,
				'item_info'			 => $this->item_info,
				'expand_map_on_load' => $this->args['expand_map_on_load']
		);
		
		//get the map element
		$output = gmw_get_results_map( $gmw );
		
		$post_map_icon = ( !empty( $this->args['item_map_icon'] ) ) ? $this->args['item_map_icon'] : false;
		
		$mapArgs = array(
				'mapId' 	 		=> $this->args['element_id'],
				'mapType'			=> 'single_location',
				'prefix'			=> 'sl',
				'mapElement' 		=> 'gmw-map-'.$this->args['element_id'],
				'locations'			=> array( 0 => array(
					'lat'				  	=> $this->item_info->lat,
					'long'				  	=> $this->item_info->long,
					'info_window_content' 	=> $this->info_window_content(),
					'mapIcon'				=> apply_filters( 'gmw_sl_post_map_icon', $post_map_icon, $this->args, $this->item_info, $this->user_position ),
				)),
				'zoomLevel'			=> $this->args['zoom_level'],
				'mapOptions'		=> array(
					'mapTypeId'			=> $this->args['map_type'],
					'mapTypeControl' 	=> false,
					'streetViewControl' => false,
					'scrollwheel'		=> ( !empty( $this->args['scrollwheel_map_zoom'] ) ) ? true : false,
					'panControl'		=> false
				),
				'userPosition'		=> array(
					'lat'		=> $this->user_position['lat'],
					'lng'		=> $this->user_position['lng'],
					'address' 	=> $this->user_position['address'],
					'mapIcon'	=> $this->args['user_map_icon'],
					'iwContent' => ! empty( $this->args['user_info_window'] ) ? $this->args['user_info_window'] : null,			
				)
		);

		gmw_new_map_element( $mapArgs );
	    
	    return $output;
	}

	/**
	 * directions function
	 * @since 2.6.1
	 * @access public
	 */
	public function directions() {
	
		//if item has no location, abort!
		if ( empty( $this->item_info ) ) {
			return ( !empty( $this->args['no_location_message'] ) ) ? $this->no_location_message() : false;
		}
				
		$element_id = esc_attr( $this->args['element_id'] );
		$i_type 	= esc_attr( $this->args['item_type'] );

		$output  = '';
		$output .= "<div id=\"gmw-sl-directions-wrapper-{$element_id}\" class=\"gmw-sl-directions-wrapper gmw-sl-element gmw-sl-{$i_type}-direction-wrapper directions-wrapper\">";
		$output .= 	"<div class=\"gmw-sl-directions-trigger-wrapper\"><i class=\"gmw-directions-icon fa fa-arrow-circle-right\"></i><a href=\"#\" id=\"gmw-sl-directions-form-trigger-{$element_id}\" class=\"gmw-sl-directions-form-trigger gmw-sl-{$i_type}-directions-form-trigger\" onclick=\"event.preventDefault();jQuery('#gmw-sl-directions-form-wrapper-{$element_id}').slideToggle();\">".esc_attr( $this->labels['directions_label'] )."</a></div>";
		$output .= 	"<div id=\"gmw-sl-directions-form-wrapper-{$element_id}\" class=\"gmw-single-post-sc-form gmw-sl-directions-form-wrapper gmw-sl-{$i_type}-directions-form-wrapper\" style=\"display:none;\">";
		$output .= 		"<form action=\"https://maps.google.com/maps\" method=\"get\" target=\"_blank\">";
		$output .= 		"<div class=\"address-field-wrapper\">";
		$output .= 				'<label for="start-address-'.$element_id.'">'.esc_attr( $this->labels['live_directions']['from'] ).'</label>';
		$output .= 				'<input type="text" size="35" id="start-address-'.$element_id.'" class="start-address" name="saddr" value="'.esc_attr( $this->user_position['address'] ).'" placeholder="Your location" />';
		$output .= 				"<i class=\"get-directions-link-submit fa fa-search get-directions-submit-icon\" onclick=\"jQuery( this ).closest( 'form' ).submit();\"></i>";
		$output .= 			"</div>";
		$output .= 			'<input type="hidden" name="daddr" value="'.esc_attr( $this->item_info->address ).'" />';
		$output .= 		"</form>";
		$output .= 	"</div>";
		$output .= "</div>";
			
		return apply_filters( 'gmw_sl_directions', $output, $this->args, $this->item_info, $this->user_position );
	}
	
	/**
	 * live directions function
	 * @since 2.6.1
	 * @access public
	 */
	public function live_directions() {

		//if item has no location, abort!
		if ( empty( $this->item_info ) ) {
			return ( !empty( $this->args['no_location_message'] ) ) ? $this->no_location_message() : false;
		}
		
		$info = new stdClass();
		
		$info->ID 	    	= $this->args['element_id'];
		$info->address  	= $this->item_info->address;
		$info->lat 	    	= $this->item_info->lat;
		$info->long 		= $this->item_info->long;
		$gmw['org_address'] = $this->user_position['address'];
		$gmw['labels']  	= $this->labels;
		$gmw['ID']			= $this->args['element_id'];
		$element_id 		= esc_attr( $this->args['element_id'] );
		$i_type 			= esc_attr( $this->args['item_type'] );

		$output  = "<div class=\"gmw-sl-live-directions-trigger-wrapper\">";
		$output .= 	"<i class=\"gmw-live-directions-icon fa fa-arrow-circle-right\"></i>";
		$output .= 	"<a href=\"#\" id=\"gmw-sl-live-directions-trigger-{$element_id}\" class=\"gmw-sl-live-directions-trigger gmw-sl-{$i_type}-live-directions-trigger\" onclick=\"event.preventDefault();jQuery('#gmw-sl-live-directions-wrapper-{$element_id}, #gmw-sl-live-directions-panel-wrapper-{$element_id}').slideToggle();\">". esc_attr( $this->labels['live_directions']['trigger_label'] )."</a>";
		$output .= "</div>";		
		$output .= "<div id=\"gmw-sl-live-directions-wrapper-{$element_id}\" class=\"gmw-sl-live-directions-wrapper gmw-sl-element gmw-sl-{$i_type}-live-direction-wrapper live-directions-wrapper\">";
		$output .=  "<input type=\"hidden\" class=\"gmw-sl-directions-element-id\" value=\"{$element_id}\" />";
		$output .=  gmw_get_live_directions( $info, $gmw );
		$output .= "</div>";
				
		wp_enqueue_script( 'gmw-sl-live-directions' );
		
		return apply_filters( 'gmw_sl_live_directions', $output, $this->args, $this->item_info, $this->user_position );
	}

	/**
	 * Live directions panel
	 * Holder for the results of the live directions
	 * 
	 * @since 2.6.1
	 */
	public function directions_panel() {	
		
		$info 	  = new stdClass();		
		$info->ID = $this->args['element_id'];
		
		$output  = '<div id="gmw-sl-live-directions-panel-wrapper-'.esc_attr( $this->args['element_id'] ).'" class="gmw-sl-live-directions-panel-wrapper gmw-sl-element gmw-sl-'.esc_attr( $this->args['item_type'] ).'-live-direction-panel-wrapper">';
		$output .= gmw_get_live_directions_panel( $info, false );
		$output .= "</div>";
		
		return apply_filters( 'gmw_sl_live_directions_panel', $output, $this->args, $this->item_info, $this->user_position );
	}
	
	/**
	 * Create the content of the info window
	 * @since 2.5
	 * @param unknown_type $post
	 */
	public function info_window_content() {
			 
		if ( empty( $this->args['item_info_window'] ) )
			return false;
		
		$iw_elements_array = explode( ',', $this->args['item_info_window'] );

		$iw_elements = array();
						
		$iw_elements['iw_wrap_start'] = '<div class="gmw-iw-wrapper gmw-sl-iw-wrapper gmw-sl-'.esc_attr( $this->args['item_type'] ).'-iw-wrapper">';

		foreach ( $iw_elements_array as $value ) {
			$iw_elements[$value] = false;
		}

		$output = array();
		
		$iw_elements['iw_wrap_end'] = '</div>';
		
		
		if ( isset( $iw_elements['distance'] ) ) {
			$iw_elements['distance'] = $this->distance();
		}
		if ( isset( $iw_elements['title'] ) ) {
			$iw_elements['title'] = $this->title();
		}
		if ( isset( $iw_elements['address'] ) ) {
			$iw_elements['address'] = $this->address();	
		}	
		if ( isset( $iw_elements['additional_info'] ) ) {
			$iw_elements['additional_info'] = $this->additional_info();
		}
		
		$output = apply_filters( 'gmw_sl_item_info_window', $iw_elements, $this->args, $this->item_info, $this->user_position );
		 
		return implode( ' ', $output );
	}
	
	/**
	 * Display elements based on arguments
	 * @since 2.6.1
	 * @access public
	 */
	public function display() {
			
		//check that we have at least one element to display
		if ( empty( $this->elements_value ) ) 
			return;
		
		if ( empty( $this->item_info ) ) {
			return ( !empty( $this->args['no_location_message'] ) ) ? $this->no_location_message() : false;
		}

		if ( !empty( $this->elements['widget_title'] ) ) {
			$this->elements['widget_title'] = html_entity_decode($this->args['widget_title']);
		}
		
		if ( isset( $this->elements['title'] ) ) {
			$this->elements['title'] = $this->title();
		}

		if ( isset( $this->elements['address'] ) ) {
			$this->elements['address'] = $this->address();
		}
		
		if ( isset( $this->elements['distance'] ) ) {
			$this->elements['distance'] = $this->distance();
		}

		if ( isset( $this->elements['directions_link'] ) ) {
			$this->elements['directions_link'] = $this->directions();
		}
		
		if ( isset( $this->elements['live_directions'] ) ) {
			$this->elements['live_directions'] = $this->live_directions();
		}
		
		if ( isset( $this->elements['live_directions'] ) && isset( $this->elements['directions_panel'] ) ) {
			$this->elements['directions_panel'] = $this->directions_panel();
		}

		if ( isset( $this->elements['additional_info'] ) && ! empty( $this->args['additional_info'] ) ) {
			$this->elements['additional_info'] = $this->additional_info();
		}

		if ( isset( $this->elements['map'] ) ) {
			$this->elements['map'] = $this->map();
		}

		do_action( 'gmw_sl_before_output_elements', $this->elements, $this->args, $this->item_info, $this->user_position );
		
		$output = implode('', $this->elements);

		return apply_filters( 'gmw_sl_display_output', $output, $this->elements, $this->args, $this->item_info, $this->user_position );
	}
}