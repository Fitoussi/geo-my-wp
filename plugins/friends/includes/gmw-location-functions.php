<?php
/**
 * GMW FL function - display members's location
 */
function gmw_fl_get_member_location( $args=false ) {
	global $bp, $wpdb;

	$args = ( $args != false) ? $args : array( 'location' => array('formatted_address'), 'member_id' => false, 'message' => '' );
	
	$args = apply_filters('gmw_fl_get_member_location_args', $args);
	
	$member_id = ( $args['member_id'] != false ) ? $member_id : $bp->displayed_user->id;
	
	$member_info = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM `wppl_friends_locator`
			WHERE member_id = %d", $member_id
		));
	
	if ( empty($member_info) ) return $args['message'];
	
	$mem_loc = array();
	
	foreach ( $args['location'] as $lc ) :
		$mem_loc[] = $member_info->$lc;
	endforeach;
	
	return apply_filters('gmw_fl_get_member_location', implode(' ',$mem_loc), $member_info);
}

function gmw_fl_member_location( $args=false ) {
	echo gmw_fl_get_member_location( $args );
}

/**
 * GMW Fl function - Query all users and save in transient. later will be used when need to show all users or random users
 */
function gmw_all_users_transient() {
	global $wpdb;
	$gmwMembers = $wpdb->get_results("SELECT gmwusers.*, wpusers.ID, wpusers.display_name
			FROM wppl_friends_locator gmwusers INNER JOIN ".$wpdb->prefix."users wpusers ON gmwusers.member_id = wpusers.ID", ARRAY_A);
	set_transient( 'gmwAllUsers', $gmwMembers, 60*60*0.25 );
}

if( false === get_transient('gmwAllUsers') ) {
	add_action('bp_init','gmw_all_users_transient');
}

/**
 * GMW FL Shortcode - Display single member location
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_member_location($member) {
	$show_address = array();
	
	if ( !bp_is_user() ) return;

	/*
	 * extract the attributes
	 */
	extract(shortcode_atts(array(
		'directions' 	 => '1',
		'map_height' 	 => '250px',
		'map_width' 	 => '250px',
		'map_type' 		 => 'ROADMAP',
		'zoom_level' 	 => 13,
		'address' 		 => 1,
		'no_location' 	 => 0
			
	),$member));
	
    global $bp, $wpdb, $mmc; 
    
    if( !isset($mmc) ) $mmc = 0;
    $mmc++;
    
    /*
     * get member information from database
     */
    $member_info = $wpdb->get_row( 
    		$wpdb->prepare(
    			"SELECT * FROM wppl_friends_locator 
    			WHERE member_id = %s", $bp->displayed_user->id
    		 ), 
    	 ARRAY_A );
	
    echo $before_widget;
    
    echo $before_title . $bp->displayed_user->fullname . '&#39;s Location' . $after_title;
    
	if ( isset( $member_info ) && !empty( $member_info ) && $member_info != 0 ) {
		
		/*
		 * get the full address
		 */
		$show_address = $member_info['street'] .' '. $member_info[0]['city'] .' ' . $member_info['state'] .' ' . $member_info['zipcode'] .' ' . $member_info['country'];
				
    	$pxStyle = ' style="position:relative;" ';
    	if ( strpos($map_width,'px') !== false ) {
    		$pxStyle = ' style="float:left;position:relative;" ';
    	}
    	
    	/*
    	 * display the map and information
    	 */
    	$member_map = false;
    	$member_map .='';	
    	$member_map .=	'<div id="gmw-single-member-sc-wrapper-'.$member_info['member_id'].'" class="gmw-single-member-sc-wrapper">';
    	
    	if( $address == 1 ) {
    		$member_map .=		'<div class="gmw-single-member-sc-address-wrapper"><span>' .__('Address: ','GMW'). '</span>' . apply_filters('gmw_fl_single_member_location_address', $show_address, $member_info, $member).'</div>';
		}
		$member_map .=		'<div class="gmw-single-member-sc-map-wrapper" '.$pxStyle.'>';
		$member_map .=			'<div id="gmw-single-member-sc-map-'.$mmc.'" class="gmw-map" style="width:'.$map_width.'; height:'.$map_height.';"></div>';
		$member_map .=			'<img class="gmw-map-loader" src="'.GMW_URL. '/images/map-loader.gif" style="position:absolute;top:45%;left:25%;width:50%" />';
		$member_map .=		'</div>';// map wrapper //
		
		if ( $directions == 1 ) {
			$member_map .= 			'<div  class="gmw-single-member-sc-direction-wrapper">';
	    	$member_map .= 				'<div id="gmw-single-member-sc-form-wrapper-'.$mmc.'" class="gmw-single-member-sc-form-wrapper" style="display:none;">';
			$member_map .=					'<form action="http://maps.google.com/maps" method="get" target="_blank">';
			$member_map .= 						'<input type="text" name="saddr" />';
			$member_map .= 						'<input type="hidden" name="daddr" value="'. $show_address.'" /><br />';
			$member_map .= 						'<input type="submit" class="button" value="' . __('Go','GMW') .'" />';
			$member_map .= 					'</form>'; 
			$member_map .= 				'</div>';
    		$member_map .= 				'<span><a href="#" class="gmw-single-member-sc-toggle" id="gmw-single-member-sc-toggle-'.$mmc.'">'. __('Get Directions','GMW'). '</a></span>';
    		$member_map .= 			'</div>';
    	}
    	
    	$member_map .=	'</div>';// map wrapper //
        	
    	?>
    	<script>
	    	jQuery(document).ready(function($){
	    		$(function() {
	    			$('#gmw-single-member-sc-toggle-'+<?php echo $mmc; ?>).click(function(event){
	    				event.preventDefault();
	    				$('#gmw-single-member-sc-form-wrapper-'+<?php echo $mmc; ?>).slideToggle(); 
	    			}); 
	    		});
	    		
		    	geocoder = new google.maps.Geocoder();
		    	geocoder.geocode( { 'address': '<?php echo $show_address; ?>'}, function(results,status) {
					if (status == google.maps.GeocoderStatus.OK) {
						var mapSingle = new google.maps.Map(document.getElementById('gmw-single-member-sc-map-'+ <?php echo $mmc; ?>), {
							zoom: parseInt(<?php echo $zoom_level; ?>),
							center: new google.maps.LatLng(results[0].geometry.location.lat(),results[0].geometry.location.lng()),
							mapTypeId: google.maps.MapTypeId['<?php echo $map_type; ?>'],
						});	
				
						marker = new google.maps.Marker({
							position: new google.maps.LatLng(results[0].geometry.location.lat(),results[0].geometry.location.lng()),
							map: mapSingle,
							shadow:'https://chart.googleapis.com/chart?chst=d_map_pin_shadow'       
						});
					}
				});	
	    	});
    	</script>
    	<?php
    	
    	return apply_filters('gmw_fl_single_member_location', $member_map, $member_info);
    	 
	} else {
		if ( isset($no_location) ) return $bp->displayed_user->fullname . __(' has not added a location yet','GMW');
	}
}
add_shortcode( 'gmw_member_location' , 'gmw_member_location' );

function gmw_delete_bp_user($user_id) {
	global $wpdb;
	
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM wppl_friends_locator WHERE member_id=%d",$user_id
		)
	);
	do_action('gmw_fl_after_delete_location', $user_id);
}
add_action('delete_user', 'gmw_delete_bp_user');
?>