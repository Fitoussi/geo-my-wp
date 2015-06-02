<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;
    
/**
 * GMW deprecated function - Display radius distance.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_by_radius( $gmw, $post ) {
	_deprecated_function( 'gmw_pt_by_radius', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $post, $gmw );
}

function gmw_pt_thumbnail( $gmw, $post ) {
	if ( !isset( $gmw['search_results']['featured_image']['use'] ) || !has_post_thumbnail( $post-> ID ) )
		return;
	_deprecated_function( 'gmw_pt_thumbnail', '2.5', 'the_post_thumbnail' );
	the_post_thumbnail( array( $gmw['search_results']['featured_image']['width'], $gmw['search_results']['featured_image']['height'] ) );
}

/**
 * GMW deprecated function - Additional information.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_additional_info( $gmw, $post, $tag ) {
	_deprecated_function( 'gmw_pt_additional_info', '2.5', 'gmw_additional_info' );
	gmw_additional_info( $post, $gmw, $gmw['search_results']['additional_info'], $gmw['labels']['search_results']['contact_info'], $tag );
}

/**
 * GMW deprecated function - Excerpt from content.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_excerpt( $gmw, $post ) {
	_deprecated_function( 'gmw_pt_excerpt', '2.5', 'gmw_excerpt' );
	if ( !isset( $gmw['search_results']['excerpt']['use'] ) )
		return;
	gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'] );
}

/**
 * GMW deprecated function - results message.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_within( $gmw, $sm, $om, $rm, $wm, $fm, $nm ) {
	_deprecated_function( 'gmw_pt_within', '2.5', 'gmw_results_message' );
	gmw_results_message( $gmw, false );
}

/**
 * GMW deprecated function - get directions link
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_directions( $gmw, $post, $title ) {
	if ( !isset( $gmw['search_results']['get_directions'] ) )
		return;
	_deprecated_function( 'gmw_pt_directions', '2.5', 'gmw_directions_link' );
	gmw_directions_link( $post, $gmw, $title );
}

/**
 * GMW deprecated function - get driving distance
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_driving_distance( $gmw, $post, $class, $title ) {
	if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
		return;
	_deprecated_function( 'gmw_pt_driving_distance', '2.5', 'gmw_driving_distance' );
	gmw_driving_distance( $post, $gmw, $title );	
}

/**
 * GMW deprecated function - pagination
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_paginations( $gmw ) {
	_deprecated_function( 'gmw_pt_paginations', '2.5', 'gmw_pagination' );
	gmw_pagination( $gmw, 'paged', $gmw['max_pages'] );
}

/**
 * GMW deprecated function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_per_page_dropdown( $gmw, $class ) {
	_deprecated_function( 'gmw_pt_per_page_dropdown', '2.5', 'gmw_per_page' );
	gmw_per_page( $gmw, $gmw['total_results'], 'paged' );
}

/**
 * GMW deprecated function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_per_page_dropdown( $gmw, $class ) {
	_deprecated_function( 'gmw_fl_per_page_dropdown', '2.5', 'gmw_per_page' );
	global $members_template;
	gmw_per_page( $gmw, $members_template->total_member_count, 'upage' );
}
	
/**
 * GMW deprecated function - Display user's full address
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_member_address( $gmw ) {
	_deprecated_function( 'gmw_fl_member_address', '2.5', 'gmw_location_address' );
	global $members_template;
	gmw_location_address( $members_template->member, $gmw );
}

/**
 * GMW deprecated function - Display distance from user
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_by_radius( $gmw ) {
	_deprecated_function( 'gmw_fl_by_radius', '2.5', 'gmw_distance_to_location' );
	global $members_template;
	gmw_distance_to_location( $members_template->member, $gmw );
}

/**
 * GMW deprecated function - directions link to user
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_directions_link( $gmw, $title ) {
	if ( !isset( $gmw['search_results']['get_directions'] ) )
		return;
	global $members_template;
	_deprecated_function( 'gmw_fl_directions_link', '2.5', 'gmw_directions_link' );
	gmw_directions_link( $members_template->member, $gmw, $title );
}

/**
 * GMW deprecated function - results message.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_wihtin_message( $gmw ) {
	global $members_template;
	_deprecated_function( 'gmw_fl_within_message', '2.5', 'gmw_results_message' );
	gmw_results_message( $gmw, false );
}

function gmw_fl_driving_distance( $gmw, $member ) {
	if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
		return;
	global $members_template;
	_deprecated_function( 'gmw_fl_driving_distance', '2.5', 'gmw_driving_distance' );
	gmw_driving_distance( $members_template->member, $gmw, false);
}

/**
 * GMW deprecated function - display members count
 * @para  $gmw
 * @param $gmw_options
 */
function gmw_fl_member_count($gmw) {
	global $members_template;
	_deprecated_function( 'gmw_fl_member_count', '2.5', 'member->member_count' );
	echo $members_template->member->member_count;
}

/**
 * GMW deprecated function - no members found
 */
function gmw_fl_no_members( $gmw ) {
	_deprecated_function( 'gmw_fl_no_members', '2.5', 'gmw_no_results_found' );
	gmw_no_results_found( $gmw, $gmw['labels']['search_results']['fl_no_results'] );
}

/*
 *GMW Users Geolocation deprecated functions 
 */
	
/**
 * GMW deprecated function - pagination
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_paginations( $gmw ) {
	_deprecated_function( 'gmw_ug_paginations', '2.5', 'gmw_pagination' );
	gmw_pagination( $gmw, 'paged', $gmw['max_pages'] );
}

/**
 * GMW UG deprecated function - Display Radius distance
 * @since 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_by_radius( $gmw, $user ) {
	_deprecated_function( 'gmw_ug_by_radius', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $user, $gmw );
}

/**
 * GMW UG deprectated function - Get directions.
 * @since 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_directions( $gmw, $user, $title ) {
	if ( !isset( $gmw['search_results']['get_directions'] ) )
		return;

	if ( empty( $title ) )
		$title = $gmw['labels']['search_results']['directions'];

	_deprecated_function( 'gmw_ug_directions', '2.5', 'gmw_directions_link' );
	gmw_directions_link( $user, $gmw, $title );
}

/**
 * GMW GL deprecated function - driving distance
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_driving_distance( $gmw, $user ) {
	if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
		return;

	_deprecated_function( 'gmw_ug_driving_distance', '2.5', 'gmw_driving_distance' );
	gmw_driving_distance( $user, $gmw, false);
}

/**
 * GMW GL deprecated function - display within distance message
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_within( $gmw, $sm, $om, $rm , $wm, $fm, $nm ) {
	_deprecated_function( 'gmw_ug_within', '2.5', 'gmw_results_message' );
	gmw_results_message( $gmw, false );
}

/**
 * GMW deprecated function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_per_page( $gmw, $class ) {
	_deprecated_function( 'gmw_ug_per_page_dropdown', '2.5', 'gmw_per_page' );
	gmw_per_page( $gmw, $gmw['total_user_count'], 'paged' );
}

/**
 * GMW deprecated function - avatar
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_avatar( $gmw, $user ) {
	_deprecated_function( 'gmw_ug_avatar', '2.5', 'get_avatar' );
	echo get_avatar( $user->ID, $gmw['search_results']['avatar']['width'] );
}

/*
 *groups locator 
 */
/**
 * GMW GL function - Display group's full address
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_group_address( $gmw ) {
	global $groups_template;
	_deprecated_function( 'gmw_gl_group_address', '2.5', 'gmw_location_address' );
	gmw_location_address( $groups_template->group, $gmw );
}

/**
 * GMW GL function - Display Radius distance
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_by_radius($gmw) {
	_deprecated_function( 'gmw_gl_by_radius', '2.5', 'gmw_distance_to_location' );
	global $groups_template;
	gmw_distance_to_location( $groups_template->group, $gmw );
}

/**
 * GMW GL function - "Get directions" link
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_get_directions($gmw) {
	if ( !isset( $gmw['search_results']['get_directions'] ) )
		return;

	global $groups_template;
	if ( empty( $title ) )
		$title = 'Get directions';

	_deprecated_function( 'gmw_gl_get_directions', '2.5', 'gmw_directions_link' );
	gmw_directions_link( $groups_template->group, $gmw, $title );
}

/**
 * GMW GL function - display within distance message
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_within_message( $gmw ) {
	_deprecated_function( 'gmw_gl_within_message', '2.5', 'gmw_results_message' );
	gmw_results_message( $gmw, false );
}

/**
 * Deprecated  - for older versions
 */
function gmgl_distance( $group, $gmw ) {
	_deprecated_function( 'gmgl_distance', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $group, $gmw );
}

/**
 * Deprecated - for older versions
 */
function gmgl_get_directions( $group, $gmw, $title ) {
	_deprecated_function( 'gmgl_get_directions', '2.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link(  $group, $gmw, $title );
}

function gmw_gl_driving_distance( $gmw, $member ) {
	if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
		return;
	global $groups_template;
	_deprecated_function( 'gmw_gl_driving_distance', '2.5', 'gmw_driving_distance' );
	gmw_driving_distance( $groups_template->group, $gmw, false);
}

/**
 * GMW deprecated function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_per_page_dropdown( $gmw, $class ) {
	_deprecated_function( 'gmw_gl_per_page_dropdown', '2.5', 'gmw_per_page' );
	global $groups_template;
	gmw_per_page( $gmw, $groups_template->total_group_count, 'grpage' );
}

function gmw_gl_group_number( $gmw ) {
	global $groups_template;
	_deprecated_function( 'gmw_gl_group_number', '2.5', '$groups_template->group->group_count' );
	return $groups_template->group->group_count;
}

/**
 * premium settings
 */
/**
 * Deprecated - for older versions
 */
function gmw_ps_pt_excerpt( $info, $gmw, $count ) {
	_deprecated_function( 'gmw_ps_pt_excerpt', '1.5', 'gmw_get_excerpt' );
	echo gmw_get_excerpt( $info, $gmw, $info->post_content, $count );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_pt_get_address( $post, $gmw ) {
	_deprecated_function( 'gmw_ps_pt_get_address', '1.5', 'gmw_get_location_address' );
	echo gmw_get_location_address( $post, $gmw );
}

/**
 * Deprecated  - for older versions
 */
function gmw_ps_pt_distance( $post, $gmw ) {
	_deprecated_function( 'gmw_ps_pt_distance', '1.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $post, $gmw );
}

/**
 * Deprecated  - for older versions
 */
function gmw_ps_pt_additional_info( $post, $gmw ) {
	_deprecated_function( 'gmw_ps_pt_additional_info', '1.5', 'gmw_additional_info' );
	gmw_additional_info( $post, $gmw, $gmw['info_window']['additional_info'], $gmw['labels']['info_window'], 'ul' );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_pt_get_directions( $post, $gmw, $title ) {
	_deprecated_function( 'gmw_ps_pt_get_directions', '1.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link(  $post, $gmw, $title );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_fl_iw_member_address( $member, $gmw ) {
	_deprecated_function( 'gmw_ps_fl_iw_member_address', '1.5', 'gmw_get_location_address' );
	echo gmw_get_location_address( $member, $gmw );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_fl_distance( $member, $gmw ) {
	_deprecated_function( 'gmw_ps_fl_distance', '1.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $member, $gmw );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_fl_get_directions( $member, $gmw, $title ) {
	_deprecated_function( 'gmw_ps_fl_get_directions', '1.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link(  $member, $gmw, $title );
}

/**
 * Global Maps
 *
 */
/**
 * Deprecated - for older versions
 */
function gmpt_excerpt( $info, $gmw, $count ) {
	_deprecated_function( 'gmpt_excerpt', '2.5', 'gmw_get_excerpt' );
	echo gmw_get_excerpt( $info, $gmw, $info->post_content, $count );
}

/**
 * Deprecated - for older versions
 */
function gmpt_address( $post, $gmw ) {
	_deprecated_function( 'gmpt_address', '2.5', 'gmw_get_location_address' );
	echo gmw_get_location_address( $post, $gmw );
}

/**
 * Deprecated  - for older versions
 */
function gmpt_distance( $post, $gmw ) {
	_deprecated_function( 'gmpt_distance', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $post, $gmw );
}

/**
 * Deprecated  - for older versions
 */
function gmpt_additional_info( $post, $gmw ) {
	_deprecated_function( 'gmpt_additional_info', '2.5', 'gmw_additional_info' );
	gmw_additional_info( $post, $gmw, $gmw['info_window']['additional_info'], 'info_window', 'div' );
}

/**
 * Deprecated - for older versions
 */
function gmpt_get_directions( $post, $gmw, $title ) {
	_deprecated_function( 'gmpt_get_directions', '2.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link(  $post, $gmw, $title );
}

/**
 * Deprecated - for older versions
 */
function gmfl_distance( $member, $gmw ) {
	_deprecated_function( 'gmfl_distance', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $member, $gmw );
}

/**
 * Deprecated - for older versions
 */
function gmfl_get_directions( $member, $gmw, $title ) {
	_deprecated_function( 'gmfl_get_directions', '2.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link( $member, $gmw, $title );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_pt_read_more_link( $post, $label, $class ) {
	_deprecated_function( 'gmfl_get_directions', '2.5', 'gmw_get_excerpt' );
	return;
}

/**
 * Deprecated - User's current location class
 *
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Current_location_Dep {

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
if ( !GEO_my_WP::gmw_check_addon( 'current_location' ) ) {
	new GMW_Current_Location_Dep;
}

/**
 * GMW Widget - User's current location
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Current_Location_Widget_Dep extends WP_Widget {

	/**
	 * __constructor
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
				'gmw_current_location_widget', // Base ID
				__('GMW Current Location', 'GMW'), // Name
				array('description' => __('Get/display the user\'s current location', 'GMW'),) // Args
		);

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget($args, $instance) {

		extract($args);

		$widget_title   	  = $instance['widget_title']; // the widget title
		$title_location 	  = $instance['title_location'];
		$display_by     	  = ( !empty( $display_by ) ) ? implode(',', $display_by) : 'city';
		$name_guest     	  = $instance['name_guest'];
		$title_location 	  = $instance['title_location'];
		$text_only			  = $instance['text_only'];
		$map				  = $instance['map'];
		$map_height			  = $instance['map_height'];
		$map_width  		  = $instance['map_width'];
		$map_type			  = $instance['map_type'];
		$zoom_level 		  = $instance['zoom_level'];
		$scrollwheel		  = $instance['scrollwheel'];
		$map_marker			  = ( !empty( $instance['map_marker'] ) ) ? $instance['map_marker'] : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
		$user_message		  = $instance['user_message'];
		$guest_message		  = $instance['guest_message'];
		$get_location_message = $instance['get_location_message'];

		echo $before_widget;

		if ( isset( $widget_title ) && !empty( $widget_title ) )
			echo $before_title . $widget_title . $after_title;

		echo do_shortcode('[gmw_current_location
				display_by="'.$display_by.'"
				show_name="'.$name_guest.'"
				title_location="'.$title_location.'"
				text_only="'.$text_only.'"
				map="'.$map.'"
				map_height="'.$map_height.'"
				map_width="'.$map_width.'"
				map_type="'.$map_type.'"
				zoom_level='.$zoom_level.'"
				scrollwheel="'.$scrollwheel.'"
				map_marker="'.$map_marker.'"
				]');

		echo '<div class="clear"></div>';

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update( $new_instance, $old_instance ) {
		 
		$instance['widget_title']         = strip_tags($new_instance['widget_title']);
		$instance['title_location']       = strip_tags($new_instance['title_location']);
		$instance['short_code_location']  = $new_instance['short_code_location'];
		$instance['display_by']           = $new_instance['display_by'];
		$instance['name_guest']           = $new_instance['name_guest'];
		$instance['text_only']            = $new_instance['text_only'];
		$instance['map']          		  = $new_instance['map'];
		$instance['map_width']         	  = $new_instance['map_width'];
		$instance['map_height']           = $new_instance['map_height'];
		$instance['map_type']         	  = $new_instance['map_type'];
		$instance['zoom_level']           = $new_instance['zoom_level'];
		$instance['scrollwheel']          = $new_instance['scrollwheel'];
		$instance['map_marker']           = $new_instance['map_marker'];
		$instance['user_message']         = $new_instance['user_message'];
		$instance['guest_message']        = $new_instance['guest_message'];
		$instance['get_location_message'] = $new_instance['get_location_message'];
		 
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {

		$defaults = array(
				'widget_title'   		=> __('Current Location', 'GMW'),
				'title_location' 		=> __('Your Location', 'GMW'),
				'display_by'     		=> 'city,country',
				'name_guest'     		=> 1,
				'user_message' 			=> 'Hello',
				'guest_message' 		=> 'Hello, guest!',
				'text_only'      		=> 0,
				'map'     		 		=> 0,
				'map_height'     		=> '200px',
				'map_width'      		=> '200px',
				'map_type'       		=> 'ROADMAP',
				'zoom_level'     		=> 12,
				'scrollwheel'    		=> 1,
				'map_marker'			=> 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
				'get_location_message' 	=> 'Get your current location'
		);

		$instance = wp_parse_args( (array ) $instance, $defaults );

		if ( !empty( $instance['display_by'] ) && !is_array( $instance['display_by'] ) ) {
			$instance['display_by'] = explode( ',', $instance['display_by'] );
		}
		?>

        <p>
            <label><?php echo esc_attr( __( "Widget's Title", 'GMW' ) ); ?>:</label>     
            <input type="text" name="<?php echo $this->get_field_name('widget_title'); ?>" value="<?php if ( isset( $instance['widget_title'] ) ) echo $instance['widget_title']; ?>" class="widefat" />
        </p>
        <p>
            <label><?php echo esc_attr( __( 'Location Title', 'GMW' ) ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name('title_location'); ?>" value="<?php if (isset($instance['title_location'])) echo $instance['title_location']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "The title that will be displayed before the location. For example Your location...", 'GMW' ); ?>
            </em>
        </p>
         <p>
            <label><?php echo esc_attr(__( 'Display Location:' ) ); ?></label><br />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "The address fields to be displayed.", 'GMW' ); ?>
            </em>
            <input type="checkbox" value="street"  name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('street', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Street', 'GMW'); ?></label><br />
            <input type="checkbox" value="city"    name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('city', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('City', 'GMW'); ?></label><br />
            <input type="checkbox" value="state"   name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('state', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('State', 'GMW'); ?></label><br />
            <input type="checkbox" value="zipcode" name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('zipcode', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Zipcode', 'GMW'); ?></label><br />
            <input type="checkbox" value="country" name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('country', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Country', 'GMW'); ?></label><br />
        </p>
        <p>
        	<input type="checkbox" value="1" name="<?php echo $this->get_field_name('name_guest'); ?>" <?php if ( isset( $instance["name_guest"] ) ) echo 'checked="checked"'; ?> class="checkbox" />
            <label><?php echo esc_attr( __( 'Display guest/User Name', 'GMW' ) ); ?></label>
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Display greeting with \"guest\" or user name before the location.", 'GMW' ); ?>
            </em>
        </p>      
         <p>
            <label><?php echo esc_attr( __( 'Greeting message ( logged in users )', 'GMW' ) ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name('user_message'); ?>" value="<?php if ( isset($instance['user_message'] ) ) echo $instance['user_message']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Text that will be displayed before the user name. For example \"Hello username\" ( requires the Display guest/User Name chekbox to be checked ).", 'GMW' ); ?>
            </em>           
        </p>    
        <p>
            <label><?php echo esc_attr( __( 'Greeting message ( guests )', 'GMW' ) ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name('guest_message'); ?>" value="<?php if ( isset( $instance['guest_message'])) echo $instance['guest_message']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Text that will be displayed when user is not looged in. for example \"Hello Guest\" ( requires the Display guest/User Name chekbox to be checked ).", 'GMW' ); ?>
            </em>
        </p>          
        <p>
        	<input type="checkbox" value="1" name="<?php echo $this->get_field_name('map'); ?>" <?php if ( isset( $instance["map"] ) ) echo 'checked="checked"'; ?> class="checkbox" />
            <label><?php echo esc_attr( __( 'Display Google Map', 'GMW' ) ); ?></label>
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Display Google map showing the user's location.", 'GMW' ); ?>
            </em>
        </p>       
        <p>
            <label><?php echo esc_attr( __( 'Map Height', 'GMW') ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name( 'map_height' ); ?>" value="<?php if ( isset( $instance['map_height'] ) ) echo $instance['map_height']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Set the map height in pixels or percentage ( ex. 250px ).", 'GMW' ); ?>
            </em>
        </p>
        <p>
            <label><?php echo esc_attr( __( 'Map Width', 'GMW') ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name( 'map_width' ); ?>" value="<?php if ( isset( $instance['map_width'] ) ) echo $instance['map_width']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Set the map width in pixels or percentage ( ex. 100% ).", 'GMW' ); ?>
            </em>
        </p> 
        <p>
            <label><?php echo esc_attr( __( 'Map Marker', 'GMW') ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name( 'map_marker' ); ?>" value="<?php echo ( !empty( $instance['map_marker'] ) ) ? $instance['map_marker'] : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'; ?>" class="widefat" />
        	<em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Link to the image that will be used as the map marker.", 'GMW' ); ?>
            </em>  
        </p>       
        <p>
            <label><?php echo _e( 'Map Type', 'GMW'); ?>:</label>
            <select name="<?php echo $this->get_field_name( 'map_type' ); ?>">
        		<option value="ROADMAP"   <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "ROADMAP" ) echo 'selected="selected"'; ?>>ROADMAP</options>
        		<option value="SATELLITE" <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "SATELLITE" ) echo 'selected="selected"'; ?> >SATELLITE</options>
        		<option value="HYBRID"    <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "HYBRID" ) echo 'selected="selected"'; ?>>HYBRID</options>
        		<option value="TERRAIN"   <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "TERRAIN" ) echo 'selected="selected"'; ?>>TERRAIN</options>
            </select>
        </p>       
         <p>
            <label><?php echo _e( 'Zoom Level', 'GMW' ); ?>:</label>
            <select name="<?php echo $this->get_field_name('zoom_level'); ?>">
        	<?php for ($i = 1; $i < 18; $i++): ?>
            	<option value="<?php echo $i; ?> " <?php if (isset($instance['zoom_level']) && $instance['zoom_level'] == $i) echo "selected"; ?>><?php echo $i; ?></option>
        	<?php endfor; ?> 
            </select>
        </p>
        <p>
        	<input type="checkbox" value="1" name="<?php echo $this->get_field_name('scrollwheel'); ?>" <?php if ( isset( $instance["scrollwheel"] ) ) echo 'checked="checked"'; ?> class="checkbox" />
            <label><?php echo esc_attr( __( 'ScrollWheel Enabled', 'GMW' ) ); ?></label>       
        	<em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "When enabled the map will zoom in/out using the mouse scrollwheel.", 'GMW' ); ?>
            </em> 
        </p>
        <?php
    }
}
if ( !GEO_my_WP::gmw_check_addon( 'current_location' ) ) {    
	add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Current_Location_Widget_Dep" );' ) );
}