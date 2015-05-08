<?php
/**
 * GMW FL Shortcode - Display single member location
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_member_location($member) {

	/*
	 * extract the attributes
	*/
	extract( shortcode_atts(
			array(
					'user_id'        		=> false,
					'display_name'			=> 1,
					'directions'     		=> 1,
					'map_height'     		=> '250px',
					'map_width'      		=> '250px',
					'map_type'       		=> 'ROADMAP',
					'zoom_level'     		=> 13,
					'address'        		=> 1,
					'no_location'    		=> 0,
					'address_fields' 		=> 'formatted_address',
					'show_on_single_post'	=> 1,
			), $member));

	if ( $user_id == false && !bp_is_user() && ( !is_single() || $show_on_single_post != 1 ) )
		return;

	$scID = rand(1, 9999);

	if ( $user_id != false ) {
		$member_id = $user_id;
	} elseif ( bp_is_user() ) {
		global $bp;
		$member_id = $bp->displayed_user->id;
	} elseif ( is_single() ) {
		global $post;
		$member_id = $post->post_author;
	}

	$member_info = gmw_get_member_info_from_db($member_id);

	if ( isset( $member_info ) && $member_info != false ) {

		/*
		 * get the full address
		*/
		$address_fields = explode( ',', $address_fields );

		if ( !isset( $address_fields ) || empty( $address_fields ) || count( $address_fields ) == 5 ) {
			$address_array[] = $member_info->formatted_address;
		} else {
			$address_array = array();

			foreach ($address_fields as $field) {
				$address_array[] = $member_info->$field;
			}
		}

		$show_address = apply_filters('gmw_fl_single_member_location_address', implode(' ', $address_array), $member_info, $member );

		/*
		 * display the map and information
		*/
		$member_map = false;
		$member_map .='';
		$member_map .= '<div id="gmw-single-member-sc-wrapper-' . $scID . '" class="gmw-single-member-sc-wrapper gmw-single-member-sc-wrapper-' . $member_id . '">';

		if ( $display_name == 1 ) {
			$member_map .= '<h3 class="display-name">'.bp_core_get_userlink( $member_id ).'</h3>';
		}
		$member_map .= '<div class="map-wrapper" style="width:' . $map_width . '; height:' . $map_height . ';">';
		$member_map .= 		'<div id="gmw-single-member-sc-map-' . $scID . '" class="gmw-map" style="width:100%; height:100%"></div>';
		$member_map .= 		'<img class="gmw-map-loader" src="' . GMW_IMAGES . '/map-loader.gif" style="position:absolute;top:45%;left:25%;width:50%" />';
		$member_map .= '</div>'; // map wrapper //

		if ( isset( $address_fields ) && !empty( $address_fields ) && $address_fields != 0 ) {
			$member_map .= '<div class="address-wrapper"><span>' . __('Address: ', 'GMW') . '</span>' . $show_address . '</div>';
		}

		if ($directions == 1) {
			$member_map .= '<div  class="direction-wrapper">';
			$member_map .= 		'<div id="single-member-form-wrapper-' . $scID . '" class="single-member-form-wrapper" style="display:none;">';
			$member_map .= 			'<form action="https://maps.google.com/maps" method="get" target="_blank">';
			$member_map .= 				'<input type="text" name="saddr" />';
			$member_map .= 				'<input type="hidden" name="daddr" value="' . $show_address . '" /><br />';
			$member_map .= 				'<input type="submit" class="button" value="' . __('Go', 'GMW') . '" />';
			$member_map .= 			'</form>';
			$member_map .= 		'</div>';
			$member_map .= 		'<span><a href="#" class="single-member-toggle" id="single-member-toggle-' . $scID . '">' . __('Get Directions', 'GMW') . '</a></span>';
			$member_map .= '</div>';
		}

		$member_map .= '</div>'; // map wrapper //
		?>
        <script>
            jQuery(document).ready(function($) {

                $(function() {
                    $('#single-member-toggle-' +<?php echo $scID; ?>).click(function(event) {
                        event.preventDefault();
                        $('#single-member-form-wrapper-' +<?php echo $scID; ?>).slideToggle();
                    });
                });

                geocoder = new google.maps.Geocoder();
                geocoder.geocode({'address': '<?php echo $show_address; ?>'}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var mapSingle = new google.maps.Map(document.getElementById('gmw-single-member-sc-map-' + <?php echo $scID; ?>), {
                            zoom: parseInt(<?php echo $zoom_level; ?>),
                            center: new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()),
                            mapTypeId: google.maps.MapTypeId['<?php echo $map_type; ?>'],
                        });

                        marker = new google.maps.Marker({
                            position: new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng()),
                            map: mapSingle,
                            shadow: 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow'
                        });
                    }
                });
            });
        </script>
        <?php
        return apply_filters( 'gmw_fl_single_member_location', $member_map, $member_info );
    } elseif ( isset( $no_location ) ) {
        return apply_filters('gmw_fl_no_location_message', bp_core_get_user_displayname($member_id) . __(' has not added a location yet', 'GMW'), $member_id);
    }
}
add_shortcode('gmw_member_location', 'gmw_member_location');