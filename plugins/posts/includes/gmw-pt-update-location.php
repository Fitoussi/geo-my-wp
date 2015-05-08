<?php

/**
 * GMW PT function - add location to GEO my WP 
 * 
 * use this function if you want to add location using a custom form.
 * 
 * function accepts an accociative array as below:
 * 
 * $args = array (
 *                  'post_id'          => false, 	//must pass post id in order to work
 *                  'address'           => false,	//can be eiter single line of full address field or an array of the adress components ex ( array( 'street' => '5060 lincoln st', 'city' => 'hollywood', 'state' => 'florida' )
 *                  'additional_info'   => array( 'phone' => false, 'fax' => false, 'email' => false, 'website' => false ),
 *                  'map_icon'          => false
 *  		);
 */
function gmw_pt_update_location( $args ) {

    $defaults = array(
        'post_id'         => false,
        'address'         => false,
        'additional_info' => array( 'phone' => false, 'fax' => false, 'email' => false, 'website' => false ),
        'map_icon'        => '_default.png',
    );

    $r = wp_parse_args( $args, $defaults );

    extract( $r );

    if ( empty( $post_id ) || empty( $address ) )
        return;

    if ( empty( $map_icon ) ) {
        $map_icon = '_default.png';
    }
    
    //if multiple address fields
    if ( is_array( $address ) ) {

        $address_apt = implode( ' ', $address );

        unset( $address['apt'] );

        $address = implode( ' ', $address );

        $returned_address = GEO_my_WP::geocoder( $address );

        if ( !in_array( 'street', $args['address'] ) || empty( $args['address']['street'] ) ) {
        	$street  = $returned_address['street'];
        }
        
        if ( !in_array( 'apt', $args['address'] ) || empty( $args['address']['apt'] ) ) {
        	$apt = $returned_address['apt'];
        }
        
        if ( !in_array( 'city', $args['address'] ) || empty( $args['address']['city'] ) ) {
        	$city = $returned_address['city'];
        }
               
        if ( !in_array( 'zipcode', $args['address'] ) || empty( $args['address']['zipcode'] ) ) {
        	$zipcode = $returned_address['zipcode'];
        }
        
        $state   = $returned_address['state_short'];
        $country = $returned_address['country_short'];

    } else {

        $address     = $address;
        $address_apt = $address;

        $returned_address = GEO_my_WP::geocoder( $address );

        $street  = $returned_address['street'];
        $apt     = $returned_address['apt'];
        $city    = $returned_address['city'];
        $state   = $returned_address['state_short'];
        $zipcode = $returned_address['zipcode'];
        $country = $returned_address['country_short'];

    }

    $locationArgs = array(
        'args'     => $args,
        'geocoded' => $returned_address
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
    		'post_title'        => stripslashes( get_the_title( $locationArgs['args']['post_id'] ) ),
    		'post_status'       => 'publish',
    		'street_number'    	=> $locationArgs['geocoded']['street_number'],
    		'street_name'       => $locationArgs['geocoded']['street_name'],
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
    		'phone'             => $locationArgs[ 'args' ][ 'additional_info' ][ 'phone' ],
    		'fax'               => $locationArgs[ 'args' ][ 'additional_info' ][ 'fax' ],
    		'email'             => $locationArgs[ 'args' ][ 'additional_info' ][ 'email' ],
    		'website'           => $locationArgs[ 'args' ][ 'additional_info' ][ 'website' ],
    		'lat'               => $locationArgs[ 'geocoded' ][ 'lat' ],
    		'long'              => $locationArgs[ 'geocoded' ][ 'lng' ],
    		'map_icon'          => $map_icon,
    )
    );

    do_action( 'gmw_pt_after_location_updated', $locationArgs );
}