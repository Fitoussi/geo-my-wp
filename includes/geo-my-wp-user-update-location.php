<?php
/**
 * GMW function - add location to Users
 *
 * use this function if you want to add location to users using a custom form.
 *
 * function accepts an accociative array as below:
 *
 * $args = array (
 *      'user_id'   => false, 	//must pass user id in order for it to work
 *      'address'   => false,	//can be eiter single line of full address field or an array of the adress components ex ( array( 'street' => '5060 lincoln st', 'city' => 'hollywood', 'state' => 'florida' )
 *      'map_icon'  => false
 *  );
 */
function gmw_update_user_location( $args ) {

	//default args
	$args = array_replace_recursive( array(
			'user_id'         => false,
			'address'         => false,
			'map_icon'        => '_default.png',
	), $args );

	if ( empty( $args['user_id'] ) || empty( $args['address'] ) )
		return;

	if ( empty( $args['map_icon'] ) ) {
		$args['map_icon'] = '_default.png';
	}

	$address_apt 	 = $address = $args['address'];
	$multiple_fields = false;

	if ( is_array( $args['address'] ) ) {
		
		$mulitple_field = true;
		$address_apt    = implode( ' ', $address );

		unset( $address[ 'apt' ] );

		$address = implode( ' ', $address );
	}

	//geocode the address
	$geocoded_address = GEO_my_WP::geocoder( $address );

	//if geocode failed delete the user's location
	if ( isset( $geocoded_address['error'] ) ) {
		
		global $wpdb;

    	$wpdb->query( $wpdb->prepare( "DELETE FROM wppl_friends_locator WHERE member_id = %d", $args['user_id'] ) );

    	do_action('gmw_user_after_location_deleted', $args, $geocoded_address );

		return;
	}

	//if multiple address field passed through array
	if ( $multiple_fields == true ) {

		//if no street entered by the user try to get it from the geocoded details
		if ( !in_array( 'street', $args['address'] ) ) {
			$street = $geocoded_address['street'];
		}

		//if no apt entered by the user try to get it from the geocoded details
		if ( !in_array( 'apt', $args['address'] ) ) {
			$apt = $geocoded_address['apt'];
		}

		//if no city entered by the user try to get it from the geocoded details
		if ( !in_array( 'city', $args['address'] ) ) {
			$city = $geocoded_address['city'];
		}

		$state = $geocoded_address[ 'state_short' ];

		//if no zipcode entered by the user try to get it from the geocoded details
		if ( !in_array( 'zipcode', $args['address'] ) ) {
			$zipcode = $geocoded_address['zipcode'];
		}

		$country = $geocoded_address['country_short'];

	//if single address field entered
	} else {

		$street  = $geocoded_address['street'];
		$apt     = $geocoded_address['apt'];
		$city    = $geocoded_address['city'];
		$state   = $geocoded_address['state_short'];
		$zipcode = $geocoded_address['zipcode'];
		$country = $geocoded_address['country_short'];
	}

	//get the locaiton information into array
	$locationArgs = array(
			'args'     => $args,
			'address'  => $address,
			'geocoded' => $geocoded_address
	);

	//allow plugins filter the information
	$LocationArgs = apply_filters( 'gmw_user_before_location_updated', $locationArgs );

	//actions before saving the location in database
	do_action( 'gmw_user_before_location_updated', $locationArgs );

	//Save information to database
	global $wpdb;

	$wpdb->replace( 'wppl_friends_locator', array(
		'member_id'         => $args['user_id'],
		//'street_number'		=> $locationArgs['geocoded']['street_number'],
		//'street_name'		=> $locationArgs['geocoded']['street_name'],
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
		'lat'               => $locationArgs['geocoded']['lat'],
		'long'              => $locationArgs['geocoded']['lng'],
		'map_icon'          => $args['map_icon'],
		)
	);

	//actions after saving the location in database
	do_action( 'gmw_user_after_location_updated', $locationArgs );
}