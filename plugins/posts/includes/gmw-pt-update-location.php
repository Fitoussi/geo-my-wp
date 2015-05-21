<?php
/**
 * GMW PT function - add location to GEO my WP 
 * 
 * use this function if you want to add location using a custom form.
 * 
 * function accepts an accociative array as below:
 * 
 * $args = array (
 *       'post_id'           => false, 	//must pass post id in order to work
 *       'address'           => false,	//can be eiter single line of full address field or an array of the adress components ex ( array( 'street' => '5060 lincoln st', 'city' => 'hollywood', 'state' => 'florida' )
 *       'additional_info'   => array( 'phone' => false, 'fax' => false, 'email' => false, 'website' => false ),
 *       'map_icon'          => false
 *  );
 */
function gmw_pt_update_location( $args, $force_refresh = false ) {

    $args = array_replace_recursive( array(
        'post_id'         => false,
        'address'         => false,
        'additional_info' => array( 'phone' => false, 'fax' => false, 'email' => false, 'website' => false ),
        'map_icon'        => '_default.png',
    ), $args );

    if ( empty( $args['post_id'] ) || empty( $args['address'] ) )
        return;

    if ( empty( $args['map_icon'] ) ) {
        $args['map_icon'] = '_default.png';
    }

    $address_apt     = $address = $args['address'];
    $multiple_fields = false;

    if ( is_array( $args['address'] ) ) {
        
        $mulitple_field = true;
        $address_apt    = implode( ' ', $address );

        unset( $address[ 'apt' ] );

        $address = implode( ' ', $address );
    }

    //geocode the address
    $geocoded_address = GEO_my_WP::geocoder( $address, $force_refresh );

    //if geocode failed delete the user's location
    if ( isset( $geocoded_address['error'] ) ) {
        
        global $wpdb;

        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}places_locator WHERE `post_id` = %d", array( $args['post_id'] ) ) );

        do_action('gmw_pt_udpate_location_after_location_deleted', $args, $geocoded_address );

        return;
    }

    //if multiple address field passed through array
    if ( $multiple_fields == true ) {
    
        if ( !in_array( 'street', $args['address'] ) || empty( $args['address']['street'] ) ) {
        	$street  = $geocoded_address['street'];
        }
        
        if ( !in_array( 'apt', $args['address'] ) || empty( $args['address']['apt'] ) ) {
        	$apt = $geocoded_address['apt'];
        }
        
        if ( !in_array( 'city', $args['address'] ) || empty( $args['address']['city'] ) ) {
        	$city = $geocoded_address['city'];
        }
               
        if ( !in_array( 'zipcode', $args['address'] ) || empty( $args['address']['zipcode'] ) ) {
        	$zipcode = $geocoded_address['zipcode'];
        }
        
        $state   = $geocoded_address['state_short'];
        $country = $geocoded_address['country_short'];

    } else {

        $street  = $geocoded_address['street'];
        $apt     = $geocoded_address['apt'];
        $city    = $geocoded_address['city'];
        $state   = $geocoded_address['state_short'];
        $zipcode = $geocoded_address['zipcode'];
        $country = $geocoded_address['country_short'];

    }

    $locationArgs = array(
        'args'     => $args,
        'address'  => $address,
        'geocoded' => $geocoded_address
    );

    $LocationArgs = apply_filters( 'gmw_pt_before_location_updated', $locationArgs );

    do_action( 'gmw_pt_before_location_updated', $locationArgs );

    //$featuredPost = ( isset( $_POST['_wppl_featured_post'] ) ) ? $_POST['_wppl_featured_post'] : 0;
    
    //Save information to database
    global $wpdb;
    $wpdb->replace( $wpdb->prefix . 'places_locator', array(
    		'post_id'           => $locationArgs['args']['post_id'],
    		'feature'           => 0,
    		'post_type'         => get_post_type( $locationArgs['args']['post_id'] ),
    		'post_title'        => get_the_title( $locationArgs['args']['post_id'] ),
    		'post_status'       => 'publish',
    		'street_number'    	=> ( !empty( $locationArgs['geocoded']['street_number'] ) ) ? $locationArgs['geocoded']['street_number'] : '',
    		'street_name'       => ( !empty( $locationArgs['geocoded']['street_name'] ) ) ? $locationArgs['geocoded']['street_name'] : '',
    		'street'            => $street,
    		'apt'               => $apt,
    		'city'              => $city,
    		'state'             => $state,
    		'state_long'        => $locationArgs['geocoded']['state_long'],
    		'zipcode'           => $zipcode,
    		'country'           => $country,
    		'country_long'      => $locationArgs['geocoded']['country_long'],
    		'address'           => $address_apt,
    		'formatted_address' => $locationArgs['geocoded']['formatted_address'],
    		'phone'             => $locationArgs['args']['additional_info']['phone'],
    		'fax'               => $locationArgs['args']['additional_info']['fax'],
    		'email'             => $locationArgs['args']['additional_info']['email'],
    		'website'           => $locationArgs['args']['additional_info']['website'],
    		'lat'               => $locationArgs['geocoded']['lat'],
    		'long'              => $locationArgs['geocoded']['lng'],
    		'map_icon'          => $args['map_icon'],
    ));
    do_action( 'gmw_pt_after_location_updated', $locationArgs );
}