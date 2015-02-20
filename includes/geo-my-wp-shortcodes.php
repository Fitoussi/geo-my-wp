<?php

/**
 * User's current location class
 *
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Current_location {

    /**
     * __constructor
     */
    public function __construct() {

        add_shortcode( 'gmw_current_location', array($this, 'current_location' ) );
        add_action( 'wp_enqueue_scripts', 	   array($this, 'register_scripts_frontend' ) );

        if ( !has_action( 'wp_footer', array( $this, 'cl_template' ) ) ) {
            add_action( 'wp_footer', array( $this, 'cl_template' ) );
        }
        add_action( 'init', array( $this, 'submitted_location' ) );

    }

    /**
     * Register scripts
     */
    public function register_scripts_frontend() {
        wp_register_script( 'gmw-cl-js', GMW_URL . '/assets/js/gmw-cl.min.js', array('jquery'), GMW_VERSION, true );
    }

    /**
     * Get current location
     * @param $args
     */
    public function current_location( $org_args ) {

    	$args = shortcode_atts( array(
    			'scid'		 			=> rand( 1,100 ),
    			'title'      			=> 'Your location',
    			'display_by' 			=> 'city,country',
    			'text_only'	 			=> 0,
    			'show_name'  			=> 1,
    			'user_message' 			=> 'Hello',
    			'guest_message' 		=> 'Hello, guest!',
    			'map'		 			=> 1,
    			'map_height' 			=> '200px',
    			'map_width'  			=> '200px',
    			'map_type'				=> 'ROADMAP',
    			'zoom_level' 			=> 12,
    			'scrollwheel'			=> 1,
    			'map_marker'			=> 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
    			'get_location_message' 	=> 'Get your current location'
    			
    	), $org_args );
    	
    	extract($args);

    	if ( empty( $map_marker ) ) $map_marker = 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
    	$userAddress  = false;
    	$current_user = false;
    	$location  	  = false;
    	$location 	 .= '';
    	$location 	 .= '<div id="gmw-cl-wrapper-'.$scid.'" class="gmw-cl-wrapper">';

    	if ( $show_name == 1 ) {
    	
    		if ( is_user_logged_in() ) {
    			global $current_user;
    			get_currentuserinfo();
    			$hMessage = $user_message.' '.$current_user->user_login.'!';
    		} else {
    			$hMessage = $guest_message;
    		}
    	
    		$location .= '<div class="gmw-cl-welcome-message">'.$hMessage.'</div>';
    	}
    	
    	if ( !empty( $_COOKIE['gmw_lat'] ) && !empty( $_COOKIE['gmw_lng'] ) ) {
    	
    		$userAddress   = array();
    	
    		foreach ( explode( ',', $display_by ) as $field ) {
    			if ( isset( $_COOKIE['gmw_' . $field] ) ) {
    				$userAddress[] = urldecode($_COOKIE['gmw_' . $field]);
    			}
    		}
    	
    		$location .= '<div class="gmw-cl-location-title-wrapper">';
    		if ( isset( $title ) && !empty( $title ) ) {
    			$location .= '<span class="gmw-cl-title">'.$title.'</span>';
    		}
    	
    		$location .= '<span class="gmw-cl-location"><a href="#" class="gmw-cl-form-trigger" title="' . __( 'Your Current Location', 'GMW' ) . '">'.implode(' ', $userAddress) . '</a></span>';
    		$location .= '</div>';
    		
    		if ( $map == 1 ) {
    			
    			$latitude  = urldecode( $_COOKIE['gmw_lat'] );
    			$longitude = urldecode( $_COOKIE['gmw_lng'] );
    			
    			$location .= '';
    			$location .= '<div class="gmw-cl-map-wrapper" style="width:'.$map_width.'; height:'.$map_height.'">';
    			$location .= 	'<div id="gmw-cl-map-'.$scid.'" class="gmw-cl-map-wrapper gmw-map" style="width:100%; height:100%;"></div>';
    			$location .= '</div>';
    		}   		 
    	} else { 	
    		//disable map since we dont have location	
    		$map = false;
    		
    		$location .= '<span class="gmw-cl-title"><a href="#" class="gmw-cl-form-trigger" title="'.$get_location_message.'">';
    		$location .= $get_location_message;
    		$location .= '</a></span>';
    	}
    	?>
        <script>
            jQuery(document).ready(function($) {
                if ( '<?php echo $map; ?>' == 1 ) {
                	var userLoc  = new google.maps.LatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>);
                    var gmwClMap = new google.maps.Map(document.getElementById('gmw-cl-map-<?php echo $scid; ?>'), {
                        zoom: parseInt(<?php echo $zoom_level; ?>),
                        center: userLoc,
                        mapTypeId: google.maps.MapTypeId['<?php echo $map_type; ?>'],
                        scrollwheel:'<?php echo $scrollwheel; ?>',
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                        }
                    });
     					
                    gmwClMarker = new google.maps.Marker({
                        position: userLoc,
                        map: gmwClMap,
                        icon:'<?php echo $map_marker; ?>'
                    });            
                };
            });
        </script>
        <?php 
    	//if only text we need
    	if ( $text_only == 1 ) {		
    		if ( !empty( $userAddress ) ) {
    			return apply_filters( 'gmw_cl_display_text_only', '<span class="gmw-cl-location">'.implode(' ', $userAddress).'</span>', $userAddress, $current_user );
    		} else {
    			return false;
    		}
    	}
    	
    	if ( !wp_script_is( 'gmw-cl-js', 'enqueue' ) ) {
    		wp_enqueue_script( 'gmw-cl-js' );
    	}
		//wp_localize_script( 'gmw-cl-js', 'gmwSettings', get_option( 'gmw_options' ) );
    	$location .= '</div>';

    	return apply_filters( 'gmw_cl_display_widget', $location, $userAddress, $display_by, $title, $show_name );

    }

    public function hidden_form() {

        $form = '<div id="gmw-cl-hidden-form-wrapper">
                    <form id="gmw-cl-hidden-form" method="post">
                        <input type="hidden" id="gmw-cl-street" 		   name="gmw_cl_location[street]" value="" />
                        <input type="hidden" id="gmw-cl-city"   		   name="gmw_cl_location[city]" value="" />
                        <input type="hidden" id="gmw-cl-state" 			   name="gmw_cl_location[state]" value="" />
                        <input type="hidden" id="gmw-cl-state-long" 	   name="gmw_cl_location[state_long]" value="" />
                        <input type="hidden" id="gmw-cl-zipcode" 		   name="gmw_cl_location[zipcode]" value="" />
                        <input type="hidden" id="gmw-cl-country" 		   name="gmw_cl_location[country]" value="" />
                        <input type="hidden" id="gmw-cl-country-long" 	   name="gmw_cl_location[country_long]" value="" />
                        <input type="hidden" id="gmw-cl-org-address" 	   name="gmw_cl_location[address]" value="" />
                        <input type="hidden" id="gmw-cl-formatted-address" name="gmw_cl_location[formatted_address]" value="" />
                        <input type="hidden" id="gmw-cl-lat" 			   name="gmw_cl_location[lat]" value="" />
                        <input type="hidden" id="gmw-cl-lng" 			   name="gmw_cl_location[lng]" value="" />
                        <input type="hidden" id="gmw-cl-action" 		   name="gmw_cl_action" value="post" />
                    </form>
                </div>';

        return $form;

    }

    /**
     * Current location form
     */
    public function cl_template() {

    	$template  = '';
    	$template .= '<div id="gmw-cl-form-wrapper" class="gmw-cl-form-wrapper">';
    	$template .= 	'<span id="gmw-cl-close-btn">X</span>';
    	$template .= 	'<form id="gmw-cl-form" name="gmw_cl_form" onsubmit="return false">';
    	$template .= 		'<div id="gmw-cl-info-wrapper">';
    	$template .= 			'<div id="gmw-cl-location-message">' . __('- Enter Your Location -', 'GMW') . '</div>';
    	$template .= 			'<div id="gmw-cl-input-fields"><input type="text" name="gmw-cl_address" id="gmw-cl-address" value="" placeholder="zipcode or full address..." /><input id="gmw-cl-submit-address" type="submit" value="go" /></div>';
    	$template .= 			'<div> - or - </div>';
    	$template .= 			'<div id="gmw-cl-get-location"><a href="#" id="gmw-cl-trigger" >';
    	$template .= 				__('Get your current location', 'GMW');
    	$template .= 			'</a></div>';
    	$template .= 		'</div>';
    	$template .=		'<div id="gmw-cl-respond-wrapper" style="display:none;">';
    	$template .= 			'<div id="gmw-cl-spinner"><img src="'.GMW_IMAGES.'/gmw-loader.gif" /></div>';
    	$template .= 			'<div id="gmw-cl-message"></div>';
    	$template .= 			'<div id="gmw-cl-map" style="width:100%;height:100px;display:none;"></div>';
    	$template .=		'</div>';
    	$template .= 	'</form>';
    	$template .= '</div>';
    	 
    	$template = apply_filters( 'gmw_current_location_form', $template );

    	$template .= $this->hidden_form();

    	echo $template;
    }

    /**
     * Submit user current location
     * @param unknown_type $location
     */
    public function submitted_location( $location ) {

        if ( empty( $_POST['gmw_cl_action'] ) )
        	return;

        //do something with the information
        do_action( 'gmw_user_current_location_submitted', $_POST['gmw_cl_location'], get_current_user_id() );	
        
        //reload page to prevent form resubmission
        wp_redirect( $_SERVER['REQUEST_URI'] );
        exit;
    }
}
new GMW_Current_Location;