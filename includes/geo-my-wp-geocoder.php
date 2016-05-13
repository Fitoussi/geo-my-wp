<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * GMW function - Geocode address
 * @since 1.0
 * @author Eyal Fitoussi
 * @author This function inspired by a script written by Pippin Williamson - Thank you
 */
function gmw_geocoder( $raw_address, $force_refresh = false ) {

    $invalid_chars = array( " " => "+", "," => "", "?" => "", "&" => "", "=" => "" , "#" => "" );
    $raw_address   = trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_address ) ) );

    if ( empty( $raw_address ) ) {
        return false;
    }

    $address_hash = md5( $raw_address );
    $location     = get_transient( 'gmw_geocoded_'.$address_hash );
    
    if ( $force_refresh == true || $location === false ) {

        global $gmw_options;

        //Build Google API url. elements can be modified via filters
        $url = apply_filters( 'gmw_google_maps_api_geocoder_url', array( 
            'protocol'  => is_ssl() ? 'https' : 'http',
            'url_base'  => '://maps.googleapis.com/maps/api/geocode/json?',
            'url_data'  => http_build_query( apply_filters( 'gmw_google_maps_api_geocoder_args', array(
                    'address'   => $raw_address,
                    'key'       => gmw_get_option( 'general_settings', 'google_api', '' ),
                    'region'    => $gmw_options['general_settings']['country_code'],
                    'language'  => $gmw_options['general_settings']['language_code'],
                    'sansor'    => 'false'
            ) ), '', '&amp;'),
        ), $gmw_options );

        $response = wp_remote_get( implode( '', $url ) );

        if( is_wp_error( $response ) )
            return;

        $data = wp_remote_retrieve_body( $response );
        
        if( is_wp_error( $data ) )
            return;

        if ( $response['response']['code'] == 200 ) {

            $data = json_decode( $data );

            if ( !empty( $data ) && $data->status === 'OK' ) {

                $location['geocoded']      = true;
                $location['street_number'] = false;
                $location['street_name']   = false;
                $location['street']        = false;
                $location['apt']           = false;
                $location['city']          = false;
                $location['state_short']   = false;
                $location['state_long']    = false;
                $location['zipcode']       = false;
                $location['country_short'] = false;
                $location['country_long']  = false;

                $location['lat']               = sanitize_text_field( $data->results[0]->geometry->location->lat );
                $location['lng']               = sanitize_text_field( $data->results[0]->geometry->location->lng );
                $location['formatted_address'] = sanitize_text_field( $data->results[0]->formatted_address );

                $address_componenets = $data->results[0]->address_components;

                foreach ( $address_componenets as $data ) {

                    switch ( $data->types[0] ) {

                        case 'street_number' :
                        $location['street_number'] = $location['street'] = sanitize_text_field( $data->long_name );
                        break;

                        case 'route' :
                        $location['street_name'] = sanitize_text_field( $data->long_name );

                        if ( !empty( $location['street_number'] ) ) {
                            $location['street'] = $location['street_number'].' '.$location['street_name'];
                        } else {
                            $location['street'] = $location['street_name'];
                        }
                        break;

                        case 'subpremise' :
                        $location['apt'] = sanitize_text_field( $data->long_name );
                        break;

                        case 'sublocality_level_1' :
                        case 'locality' :
                        case 'postal_town' :
                        $location['city'] = sanitize_text_field( $data->long_name );
                        break;

                        case 'administrative_area_level_1' :
                        case 'administrative_area_level_2' :
                        $location['state_short']  = sanitize_text_field( $data->short_name );
                        $location['state_long']   = sanitize_text_field( $data->long_name );
                        break;

                        case 'postal_code' :
                        $location['zipcode'] = sanitize_text_field( $data->long_name );
                        break;

                        case 'country' :
                        $location['country_short'] = sanitize_text_field( $data->short_name );
                        $location['country_long']  = sanitize_text_field( $data->long_name );
                        break;
                    }    
                }

                do_action( 'gmw_geocoded_location', $location );

                    // cache coordinates for 3 months
                set_transient( 'gmw_geocoded_'.$address_hash, $location, 3600*24*30*3 );

            } elseif ( $data->status === 'ZERO_RESULTS' ) {
                return array( 'geocoded' => false, 'error' => __( 'The address entered could not be geocoded.', 'GMW' ) );
            } elseif ( $data->status === 'INVALID_REQUEST' ) {
                return array( 'geocoded' => false, 'error' => __( 'Invalid request. Did you enter an address?', 'GMW' ) );
            } elseif ( $data->status === 'OVER_QUERY_LIMIT' ) { 
                return array( 'geocoded' => false, 'error' => __( 'Something went wrong while retrieving your location.', 'GMW' ) . '<span style="display:none">OVER_QUERY_LIMIT</span>' );
            } else {
                return array( 'geocoded' => false, 'error' => __( 'Something went wrong while retrieving your location.', 'GMW' ) );
            }

        } else {
            return array( 'geocoded' => false, 'error' => __( 'Unable to contact Google API service.', 'GMW' ) );
        }
    } 
    return $location;
}