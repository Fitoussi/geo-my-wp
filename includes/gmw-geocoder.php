<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'gmw_geocoder' ) ) :

/**
 * 
 * GMW function - Geocode address
 * 
 * @since 1.0
 * 
 * @author Eyal Fitoussi
 * @author This function inspired by a script written by Pippin Williamson - Thank you
 * 
 * @param  string||array  $raw_data  can be address as a string or coords as of array( 'latitude','longitude' ) 
 * @param  boolean $force_refresh true to ignore data saved in cache
 * 
 * @return array geocoded data.
 * 
 */
function gmw_geocoder( $raw_data = '', $force_refresh = false ) {

    // if data is array, then it should be coordinates
    if ( is_array( $raw_data ) ) {
        
        // convert to lat,lng comma separated
        $data = implode( ',', $raw_data ); 
        $type = 'latlng';

    // if not array, then it should be an address
    } else {

        // remove invalid characters from address
        $invalid_chars = array( " " => "+", "," => "", "?" => "", "&" => "", "=" => "" , "" => "" );
        $data          = trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_data ) ) );
        $type          = 'address';
    }
    
    // abort if data is blank
    if ( empty( $data ) ) {
        return false;
    }

    // look for geocoded location in cache
    $address_hash = md5( $data );
    $location     = get_transient( 'gmw_geocoded_'.$address_hash );
    
    // if no location found in cache or if forced referesh try to geocode
    if ( $force_refresh == true || $location === false ) {

        //Build Google API url. elements can be modified via filters
        $url = apply_filters( 'gmw_google_maps_api_geocoder_url', array( 
            'protocol'  => is_ssl() ? 'https' : 'http',
            'url_base'  => '://maps.googleapis.com/maps/api/geocode/json?',
            'url_data'  => http_build_query( apply_filters( 'gmw_google_maps_api_geocoder_args', array(
                $type       => $data,
                'key'       => gmw_get_option( 'general_settings', 'google_api', '' ),
                'region'    => gmw_get_option( 'general_settings', 'country_code', 'us' ),
                'language'  => gmw_get_option( 'general_settings', 'language_code', 'en' ),
            ) ), '', '&amp;' ),
        ) );

        // try geocoding
        $response = wp_remote_get( implode( '', $url ) );

        // abort if geocoding failed
        if ( is_wp_error( $response ) ) {
            return;
        }

        // get geocoded data
        $data = wp_remote_retrieve_body( $response );

        // abort if geocoding failed
        if ( is_wp_error( $data ) ) {
            return;
        }

        // if geocoding success
        if ( $response['response']['code'] == 200 ) {

            // decode the data
            $data = json_decode( $data );

            if ( ! empty( $data ) && $data->status === 'OK' ) {

                // default values
                $location = array(
                    'street_number'     => '',
                    'street_name'       => '',
                    'street'            => '',
                    'premise'           => '',
                    'neighborhood'      => '',
                    'city'              => '',
                    'county'            => '',
                    'region_name'       => '',
                    'region_code'       => '',
                    'postcode'          => '',
                    'country_name'      => '',
                    'country_code'      => '',
                    'address'           => '',
                    'formatted_address' => sanitize_text_field( $data->results[0]->formatted_address ),
                    // to support older versions
                    'lat'               => sanitize_text_field( $data->results[0]->geometry->location->lat ),
                    // to support older versions
                    'lng'               => sanitize_text_field( $data->results[0]->geometry->location->lng ),
                    'latitude'          => sanitize_text_field( $data->results[0]->geometry->location->lat ),
                    'longitude'         => sanitize_text_field( $data->results[0]->geometry->location->lng ),
                    'place_id'          => ! empty( $data->results[0]->place_id ) ? sanitize_text_field( $data->results[0]->place_id ) : '',
                );

                $address_componenets = $data->results[0]->address_components;

                // loop through address fields and collect data
                foreach ( $address_componenets as $data ) {

                    switch ( $data->types[0] ) {

                        // street number
                        case 'street_number' :
                            $location['street_number'] = $location['street'] = sanitize_text_field( $data->long_name );
                        break;

                        // street name and street
                        case 'route' :
                            
                            // street name
                            $location['street_name'] = sanitize_text_field( $data->long_name );

                            // street ( number + name )
                            if ( ! empty( $location['street_number'] ) ) {
                                
                                $location['street'] = $location['street_number'].' '.$location['street_name'];
                            
                            } else {
                                
                                $location['street'] = $location['street_name'];
                            }
                        break;

                        // premise
                        case 'subpremise' :
                            $location['premise'] = sanitize_text_field( $data->long_name );
                        break;

                        // neigborhood
                        case 'neighborhood' :
                            $location['neighborhood'] = sanitize_text_field( $data->long_name );
                        break;

                        // city
                        case 'sublocality_level_1' :
                        case 'locality' :
                        case 'postal_town' :
                            $location['city'] = sanitize_text_field( $data->long_name );
                        break;

                        // county
                        case 'administrative_area_level_2' :
                        case 'political' :
                            $location['county'] = sanitize_text_field( $data->long_name );
                        break;

                        // region / state
                        case 'administrative_area_level_1' :
                            $location['region_code']  = sanitize_text_field( $data->short_name );
                            $location['region_name']   = sanitize_text_field( $data->long_name );
                        break;

                        // postal code
                        case 'postal_code' :
                            $location['postcode'] = sanitize_text_field( $data->long_name );
                        break;

                        // country
                        case 'country' :
                            $location['country_code'] = sanitize_text_field( $data->short_name );
                            $location['country_name']  = sanitize_text_field( $data->long_name );
                        break;
                    }    
                }

                // hook after geocoding
                do_action( 'gmw_geocoded_location', $location );

                // cache location for 3 months
                set_transient( 'gmw_geocoded_'.$address_hash, $location, 365 * DAY_IN_SECONDS );

            // otherwise, if no results. display errors
            } elseif ( $data->status === 'ZERO_RESULTS' ) {
                return array( 'geocoded' => false, 'error' => __( 'The data entered could not be geocoded.', 'geo-my-wp' ) );
            } elseif ( $data->status === 'INVALID_REQUEST' ) {
                return array( 'geocoded' => false, 'error' => __( 'Invalid request. Did you enter an address?', 'geo-my-wp' ) );
            } elseif ( $data->status === 'OVER_QUERY_LIMIT' ) { 
                return array( 'geocoded' => false, 'error' => __( 'Something went wrong while retrieving your location.', 'geo-my-wp' ) . '<span style="display:none">OVER_QUERY_LIMIT</span>' );
            } else {
                return array( 'geocoded' => false, 'error' => __( 'Something went wrong while retrieving your location.', 'geo-my-wp' ) );
            }

        } else {
            return array( 'geocoded' => false, 'error' => __( 'Unable to contact Google API service.', 'geo-my-wp' ) );
        }
    } 
    
    return $location;
}

endif;
