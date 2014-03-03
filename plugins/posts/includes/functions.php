<?php 
/**
 * GMW PT Shortcode - Shortcode display location of a single post, post type or a page.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_single_location($single) {
	extract(shortcode_atts(array(
		'map_height' 	  => '250px',
		'map_width' 	  => '250px',
		'map_type' 		  => 'ROADMAP',
		'zoom_level' 	  => 13,
		'additional_info' => 'address,phone,fax,email,website',
		'post_id'		  => 0,
		'directions'	  => 1
	),$single));

	global $wpdb, $mmc;
	if(!isset($mmc) || empty($mmc) ) $mmc = 0;
	$mmc++;

	//if ( $post_id != 0 || is_single() ) {
			
	/*
	 * check if user entered post id
	*/
	if ( $post_id == 0 ) :
		global $post;
		$post_id = $post->ID;
	endif;

	$post_info = false;
	$post_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "places_locator WHERE post_id = %d",array($post_id)), ARRAY_A );
	
	//if post has no location stop the function
	if ( empty ($post_info) ) return;
	
	$single_map = '';
	$single_map .=	'<div class="gmw-single-post-sc-wrapper">';
	$single_map .=		'<div class="gmw-single-post-sc-map-wrapper">';
	$single_map .=			'<div id="gmw-single-post-map-'.$mmc.'" class="gmw-map" style="width:' . $map_width.'; height:' . $map_height.';"></div>';
	$single_map .= 		'</div>';

	if ( $directions == 1 ) :

		$single_map .= 			'<div class="gmw-single-post-sc-directions-wrapper">';
		$single_map .= 				'<div id="gmw-single-post-sc-form-'.$mmc.'" class="gmw-single-post-sc-form" style="display:none;">';
		$single_map .=					'<form action="http://maps.google.com/maps" method="get" target="_blank">';
		$single_map .= 						'<input type="text" size="35" name="saddr" value="" placeholder="Your location" />';
		$single_map .= 						'<input type="hidden" name="daddr" value="'. $post_info[0]['address'].'" />';
		$single_map .= 						'<input type="submit" class="button" value="'.__('GO','GMW').'" />';
		$single_map .= 					'</form>';
		$single_map .= 				'</div>';
		$single_map .= 				'<a href="#" id="gmw-single-post-sc-directions-trigger-'.$mmc.'"  class="gmw-single-post-sc-directions-trigger">'.__('Get Directions','GMW').'</a>';
		$single_map .=			'</div>';

	endif;

	//if we are showing additional information
	if ( isset($additional_info) || $addiotional_info != 0 ) :
	
		$additional_info = explode(',', $additional_info );

		$single_map .= '<div class="gmw-single-post-sc-additional-info">';
		
		if ( in_array('address', $additional_info) ) { $single_map .= '<div class="gmw-address"><span>' . __('Address: ','GMW'); $single_map .= '</span>'; $single_map .= ( isset($post_info[0]['formatted_address']) && !empty($post_info[0]['formatted_address'])) ? $post_info[0]['formatted_address'] : __('N/A','GMW'); $single_map .= '</div>'; }
		if ( in_array('phone', $additional_info) ) { $single_map   .= '<div class="gmw-phone"><span>'. __('Phone: ','GMW'); $single_map .= '</span>'; $single_map .= ( isset($post_info[0]['phone']) && !empty($post_info[0]['phone'])) ? $post_info[0]['phone'] : __('N/A','GMW'); $single_map .= '</div>'; }
		if ( in_array('fax', $additional_info) ) { $single_map     .= '<div class="gmw-fax"><span>'. __('Fax: ','GMW'); $single_map .= '</span>'; $single_map .= ( isset($post_info[0]['fax']) && !empty($post_info[0]['fax'])) ? $post_info[0]['fax'] : __('N/A','GMW'); $single_map .= '</div>'; }
		if ( in_array('email', $additional_info) ) { $single_map   .= '<div class="gmw-email"><span>'. __('Email: ','GMW'); $single_map .= '</span>'; $single_map .= ( isset($post_info[0]['email']) && !empty($post_info[0]['email'])) ? '<a href="mailto:'.$post_info[0]['email'].' >'.$post_info[0]['email'].'</a>' : __('N/A','GMW'); $single_map .= '</div>'; }
		if ( in_array('website', $additional_info) ) { $single_map .= '<div class="gmw-website"><span>'. __('Website: ','GMW'); $single_map .= '</span>'; $single_map .= ( isset($post_info[0]['website']) && !empty($post_info[0]['website']) ) ? '<a href="http://' . $post_info[0]['website']. '" target="_blank">' .$post_info[0]['website']. '</a>' : "N/A"; $single_map .= '</div>'; }
		
		$single_map .= '</div>';
	
	endif;

	$single_map .= '</div>';

	?>
	<script>
	
		jQuery(document).ready(function($) {
		   
			$('#gmw-single-post-sc-directions-trigger-<?php echo $mmc; ?>').click(function(event){
		   	 	event.preventDefault();
		    	$('#gmw-single-post-sc-form-<?php echo $mmc; ?>').slideToggle(); 
		    }); 
				
			var gmwSinglePostMap = new google.maps.Map(document.getElementById('gmw-single-post-map-<?php echo $mmc; ?>'), {
				zoom: parseInt(<?php echo $zoom_level; ?>),
	    		center: new google.maps.LatLng(<?php echo $post_info[0]['lat']; ?>, <?php echo $post_info[0]['long']; ?>),
	    		mapTypeId: google.maps.MapTypeId['<?php echo $map_type; ?>'],
				mapTypeControlOptions: {
					style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
	    		}
			});	
			
			var infowindow = new google.maps.InfoWindow(); 

			if ( '<?php echo $post_info[0]['phone']; ?>' != '' ) gmwPhone = '<?php echo $post_info[0]['phone']; ?>'; else gmwPhone = 'N/A';
			if ( '<?php echo $post_info[0]['fax']; ?>' != '' ) gmwFax = '<?php echo $post_info[0]['fax']; ?>'; else gmwFax = 'N/A';
			if ( '<?php echo $post_info[0]['email']; ?>' != '' ) gmwEmail = '<?php echo $post_info[0]['email']; ?>'; else gmwEmail = 'N/A';
			if ( '<?php echo $post_info[0]['website']; ?>' != '' ) gmwWebsite = '<a href="http://<?php echo $post_info[0]['website']; ?>" target="_blank"><?php echo $post_info[0]['website']; ?></a>'; else gmwWebsite = 'N/A';
			
			gmwSinglePostInfoWindow =
        		'<div class="wppl-info-window" style="font-size: 13px;color: #555;line-height: 18px;font-family: arial;">' +
        		'<div class="map-info-title" style="color: #457085;text-transform: capitalize;font-size: 16px;margin-bottom: -10px;"><?php echo $post_info[0]['post_title']; ?></div>' +
        		'<br /> <span style="font-weight: bold;color: #333;">Address: </span><?php echo $post_info[0]['address']; ?>'  + 
        		'<br /> <span style="font-weight: bold;color: #333;">Phone: </span>' + gmwPhone + 
        		'<br /> <span style="font-weight: bold;color: #333;">Fax: </span>' + gmwFax + 
        		'<br /> <span style="font-weight: bold;color: #333;">Email: </span>' + gmwEmail + 
        		'<br /> <span style="font-weight: bold;color: #333;">Website: </span>' + gmwWebsite;
        		
			gmwSinglePostMarker = new google.maps.Marker({
				position: new google.maps.LatLng(<?php echo $post_info[0]['lat']; ?>, <?php echo $post_info[0]['long']; ?>),
				map: gmwSinglePostMap,
				shadow:'https://chart.googleapis.com/chart?chst=d_map_pin_shadow'       
			});

			google.maps.event.addListener(gmwSinglePostMarker, 'click', function() {
			   infowindow.setContent(gmwSinglePostInfoWindow);
			   infowindow.open(gmwSinglePostMap, gmwSinglePostMarker);
			});
		});		
		</script>
	<?php
	return $single_map;
	//}
}
add_shortcode( 'gmw_single_location' , 'gmw_single_location' );

/* when post status changes - change it in our table as well */
function filter_transition_post_status( $new_status, $old_status, $post ) { 
    global $wpdb;
    $wpdb->query( 
        $wpdb->prepare( 
          "UPDATE " . $wpdb->prefix . "places_locator SET post_status=%s WHERE post_id=%d", 
           $new_status,$post->ID
         ) 
     );
}
add_action('transition_post_status', 'filter_transition_post_status', 10, 3);

/* delete info from our database after post was deleted */
function delete_address_map_rows()	{
	global $wpdb, $post;
	$wpdb->query(
		$wpdb->prepare( 
			"DELETE FROM " . $wpdb->prefix . "places_locator WHERE post_id=%d",$post->ID
		)
	);
}
add_action('before_delete_post', 'delete_address_map_rows'); 


?>
