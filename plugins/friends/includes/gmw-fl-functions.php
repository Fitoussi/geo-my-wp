<?php

/**
 * GMW FL function - get members location from database or cache
 * @param unknown_type $user_id
 */
function gmw_get_member_info_from_db($user_id) {

    $info = wp_cache_get('gmw_member_info', $group    = $user_id);

    if (false === $info) {

        global $wpdb;

        $info = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT * FROM `wppl_friends_locator`
						WHERE member_id = %d", $user_id
        ));
        wp_cache_set('gmw_member_info', $info, $user_id);
    }

    return ( isset($info) ) ? $info : false;

}

/**
 * GMW FL function - display members's info
 */
function gmw_get_member_info($args) {
    global $bp, $members_template;

    $args = apply_filters('gmw_fl_get_member_info_args', $args);

    //default shortcode attributes
    extract(
            shortcode_atts(
                    array(
        'user_id'  => false,
        'info' 	   => 'formatted_address',
        'message'  => '',
        'divider'  => ' '
                    ), $args)
    );

    if ($user_id != false)
        $user_id = $user_id;

    elseif ( isset( $bp->displayed_user->id ) )
        $user_id = $bp->displayed_user->id;
    
    elseif ( isset( $members_template->member->id ) )
    	$user_id = $members_template->member->id;
    
    else return;

    $member_info = gmw_get_member_info_from_db( $user_id );

    if (!isset($member_info) || $member_info === false)
        return $message;

    $mem_loc = array();

    $info = explode(',', str_replace(' ', '', $info));
    $count    = 1;

    foreach ($info as $lc) :

        $loc       = $member_info->$lc;
        if ($count < count($info))
            $loc .= $divider;
        $mem_loc[] = $loc;
        $count++;

    endforeach;

    return apply_filters('gmw_fl_get_member_info', implode(' ', $mem_loc), $member_info);

}

add_shortcode('gmw_member_info', 'gmw_get_member_info');

function gmw_fl_member_info($args = false) {
    echo gmw_get_member_info($args);
}

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
        'user_id'        => false,
        'directions'     => '1',
        'map_height'     => '250px',
        'map_width'      => '250px',
        'map_type'       => 'ROADMAP',
        'zoom_level'     => 13,
        'address'        => 1,
        'no_location'    => 0,
        'address_fields' => 'formatted_address'
                    ), $member));

    global $bp;

    if ($user_id == false && !bp_is_user())
        return;

    $scID = rand(1, 9999);

    $member_id = ( $user_id != false ) ? $user_id : $bp->displayed_user->id;

    $member_info = gmw_get_member_info_from_db($member_id);

    if (isset($member_info) && $member_info != false) {

        /*
         * get the full address
         */
        $address_fields = explode(',', $address_fields);

        if (!isset($address_fields) || empty($address_fields) || count($address_fields) == 5) {
            $address_array[] = $member_info->formatted_address;
        } else {
            $address_array = array();

            foreach ($address_fields as $field) {
                $address_array[] = $member_info->$field;
            }
        }

        $show_address = apply_filters('gmw_fl_single_member_location_address', implode(' ', $address_array), $member_info, $member);

        $pxStyle = ' style="position:relative;" ';
        if (strpos($map_width, 'px') !== false) {
            $pxStyle = ' style="float:left;position:relative;" ';
        }

        /*
         * display the map and information
         */
        $member_map = false;
        $member_map .='';
        $member_map .= '<div id="gmw-single-member-sc-wrapper-' . $scID . '" class="gmw-single-member-sc-wrapper gmw-single-member-sc-wrapper-' . $member_id . '">';

        if ($address == 1) {
            $member_map .= '<div class="gmw-single-member-sc-address-wrapper"><span>' . __('Address: ', 'GMW') . '</span>' . $show_address . '</div>';
        }
        $member_map .= '<div class="gmw-single-member-sc-map-wrapper" ' . $pxStyle . '>';
        $member_map .= '<div id="gmw-single-member-sc-map-' . $scID . '" class="gmw-map" style="width:' . $map_width . '; height:' . $map_height . ';"></div>';
        $member_map .= '<img class="gmw-map-loader" src="' . GMW_IMAGES . '/map-loader.gif" style="position:absolute;top:45%;left:25%;width:50%" />';
        $member_map .= '</div>'; // map wrapper //

        if ($directions == 1) {
            $member_map .= '<div  class="gmw-single-member-sc-direction-wrapper">';
            $member_map .= '<div id="gmw-single-member-sc-form-wrapper-' . $scID . '" class="gmw-single-member-sc-form-wrapper" style="display:none;">';
            $member_map .= '<form action="http://maps.google.com/maps" method="get" target="_blank">';
            $member_map .= '<input type="text" name="saddr" />';
            $member_map .= '<input type="hidden" name="daddr" value="' . $show_address . '" /><br />';
            $member_map .= '<input type="submit" class="button" value="' . __('Go', 'GMW') . '" />';
            $member_map .= '</form>';
            $member_map .= '</div>';
            $member_map .= '<span><a href="#" class="gmw-single-member-sc-toggle" id="gmw-single-member-sc-toggle-' . $scID . '">' . __('Get Directions', 'GMW') . '</a></span>';
            $member_map .= '</div>';
        }

        $member_map .= '</div>'; // map wrapper //
        ?>
        <script>
            jQuery(document).ready(function($) {

                $(function() {
                    $('#gmw-single-member-sc-toggle-' +<?php echo $scID; ?>).click(function(event) {
                        event.preventDefault();
                        $('#gmw-single-member-sc-form-wrapper-' +<?php echo $scID; ?>).slideToggle();
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
        return apply_filters('gmw_fl_single_member_location', $member_map, $member_info);
    } elseif (isset($no_location)) {
        return apply_filters('gmw_fl_no_location_message', bp_core_get_user_displayname($member_id) . __(' has not added a location yet', 'GMW'), $member_id);
    }

}

add_shortcode('gmw_member_location', 'gmw_member_location');

function gmw_fl_delete_bp_user($user_id) {
    global $wpdb;

    $wpdb->query(
            $wpdb->prepare(
                    "DELETE FROM wppl_friends_locator WHERE member_id=%d", $user_id
            )
    );
    do_action('gmw_fl_after_delete_location', $user_id);

}

add_action('delete_user', 'gmw_fl_delete_bp_user');
?>