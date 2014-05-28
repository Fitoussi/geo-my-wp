<?php
/**
 * GMW function - add location to Users
 *
 * use this function if you want to add location to users using a custom form.
 *
 * function accepts an accociative array as below:
 *
 * $args = array (
 *                  'user_id'           => false, 	//must pass user id in order for it to work
 *                  'address'           => false,	//can be eiter single line of full address field or an array of the adress components ex ( array( 'street' => '5060 lincoln st', 'city' => 'hollywood', 'state' => 'florida' )
 *                  'map_icon'          => false
 *  		);
 */
function gmw_update_user_location( $args ) {

	$defaults = array(
			'user_id'         => false,
			'address'         => false,
			'map_icon'        => '_default.png',
	);

	$r = wp_parse_args( $args, $defaults );

	extract( $r );

	if ( $user_id == false || $address == false )
		return;

	if ( $map_icon == false )
		$map_icon = '_default.png';

	if ( is_array( $address ) ) {

		$address_apt = implode( ' ', $address );

		unset( $address[ 'apt' ] );

		$address = implode( ' ', $address );

		$returned_address = GEO_my_WP::geocoder( $address );

		if ( !in_array( 'street', $args[ 'address' ] ) )
			$street  = $returned_address[ 'street' ];

		if ( !in_array( 'apt', $args[ 'address' ] ) )
			$apt     = $returned_address[ 'apt' ];

		if ( !in_array( 'city', $args[ 'address' ] ) )
			$city    = $returned_address[ 'city' ];

		$state   = $returned_address[ 'state_short' ];

		if ( !in_array( 'zipcode', $args[ 'address' ] ) )
			$zipcode = $returned_address[ 'zipcode' ];

		$country = $returned_address[ 'country_short' ];

	} else {

		$address     = $address;
		$address_apt = $address;

		$returned_address = GEO_my_WP::geocoder( $address );

		$street  = $returned_address[ 'street' ];
		$apt     = $returned_address[ 'apt' ];
		$city    = $returned_address[ 'city' ];
		$state   = $returned_address[ 'state_short' ];
		$zipcode = $returned_address[ 'zipcode' ];
		$country = $returned_address[ 'country_short' ];

	}

	$locationArgs = array(
			'args'     => $args,
			'geocoded' => $returned_address
	);

	$LocationArgs = apply_filters( 'gmw_user_before_location_updated', $locationArgs );

	do_action( 'gmw_user_before_location_updated', $locationArgs );

	//Save information to database
	global $wpdb;
	$wpdb->replace( 'wppl_friends_locator', array(
			'member_id'         => $locationArgs['args']['user_id'],
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
			'map_icon'          => $map_icon,
	)
	);

	do_action( 'gmw_user_after_location_updated', $locationArgs );

}