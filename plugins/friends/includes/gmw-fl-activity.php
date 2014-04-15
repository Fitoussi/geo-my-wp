<?php

/**
 * GMW Location function - post location to activity
 * @param $args
 * @return boolean|Ambigous <number, boolean, unknown, mixed>
 */
function gmw_location_record_activity($args) {
    global $bp;

    if (!function_exists('bp_activity_add'))
        return false;

    $from_user_link = bp_core_get_userlink($bp->loggedin_user->id);

    $defaults = array(
        'id'                => false,
        'location'          => false,
        'user_id'           => $bp->loggedin_user->id,
        'action'            => false,
        'content'           => '',
        'primary_link'      => bp_core_get_userlink($bp->loggedin_user->id, false, true),
        'component'         => $bp->gmw_location->id,
        'type'              => 'gmw_location',
        'item_id'           => false,
        'secondary_item_id' => false,
        'recorded_time'     => gmdate("Y-m-d H:i:s")
    );

    $r = wp_parse_args($args, $defaults);
    extract($r);

    $cCity    = ( isset($_COOKIE['gmw_city']) ) ? urldecode($_COOKIE['gmw_city']) : false;
    $cState   = ( isset($_COOKIE['gmw_state']) ) ? urldecode($_COOKIE['gmw_state']) : false;
    $cCountry = ( isset($_COOKIE['gmw_country']) ) ? urldecode($_COOKIE['gmw_country']) : false;

    $region = ( WPLANG ) ? explode('_', WPLANG) : array('en', 'US');

    $activity_id = bp_activity_add(array(
        //'id' => $id, 
        'user_id'           => $bp->loggedin_user->id,
        'action'            => sprintf(__('%s Updated new location at <a target="_blank" href="http://maps.google.com/maps?f=d&hl=' . $region[0] . '&region=' . $region[1] . '&geocode=&saddr=' . $location . '&daddr=' . $cCity . ' ' . $cState . ' ' . $cCountry . '&ie=UTF8&z=12" >' . $location . '</a>', 'GMW'), $from_user_link),
        'content'           => $content,
        'primary_link'      => $primary_link,
        'component'         => $component,
        'type'              => $type,
        'item_id'           => $item_id,
        'secondary_item_id' => $secondary_item_id,
        'recorded_time'     => $recorded_time
            ));

    if ($type == 'gmw_location')
        bp_update_user_meta($bp->loggedin_user->id, 'bp_latest_update', array('id' => $activity_id, 'content' => wp_filter_kses($content)));

    return $activity_id;

}

?>