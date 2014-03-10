<?php

/**
 * GMW function - Geocode address
 * @since 1.0
 * @author Eyal Fitoussi
 * @author This function inspired by a script written by Pippin Williamson
 */
function GmwConvertToCoords($address) {

    $address_hash = md5($address);

    $args     = array('address' => urlencode($address), 'sensor' => 'false');
    $url      = add_query_arg($args, 'http://maps.googleapis.com/maps/api/geocode/json');
    $response = wp_remote_get($url);

    if (is_wp_error($response))
        return;

    $data = wp_remote_retrieve_body($response);

    if (is_wp_error($data))
        return;

    if ($response['response']['code'] == 200) {

        $data = json_decode($data);

        do_action('gmw_geocode_raw_data', $address, $data);

        if ($data->status === 'OK') {

            $location['street']        = false;
            $location['apt']           = false;
            $location['city']          = false;
            $location['state_short']   = false;
            $location['state_long']    = false;
            $location['zipcode']       = false;
            $location['country_short'] = false;
            $location['country_long']  = false;

            $coordinates = $data->results[0]->geometry->location;

            $location['lat']               = $coordinates->lat;
            $location['lng']               = $coordinates->lng;
            $location['formatted_address'] = (string) $data->results[0]->formatted_address;

            $address_componenets = $data->results[0]->address_components;

            foreach ($address_componenets as $ac) :

                if ($ac->types[0] == 'street_number') :

                    $street_number = esc_attr($ac->long_name);
                endif;

                if ($ac->types[0] == 'route') :
                    $street_f = esc_attr($ac->long_name);

                    if (isset($street_number) && !empty($street_number))
                        $location['street'] = $street_number . ' ' . $street_f;
                    else
                        $location['street'] = $street_f;
                endif;

                if ($ac->types[0] == 'subpremise')
                    $location['apt'] = esc_attr($ac->long_name);

                if ($ac->types[0] == 'locality')
                    $location['city'] = esc_attr($ac->long_name);

                if ($ac->types[0] == 'administrative_area_level_1') :

                    $location['state_short'] = esc_attr($ac->short_name);
                    $location['state_long']  = esc_attr($ac->long_name);

                endif;

                if ($ac->types[0] == 'postal_code')
                    $location['zipcode'] = esc_attr($ac->long_name);

                if ($ac->types[0] == 'country') :

                    $location['country_short'] = esc_attr($ac->short_name);
                    $location['country_long']  = esc_attr($ac->long_name);

                endif;

            endforeach;

            do_action('gmw_geocoded_location', $location);

            return $location;
        } elseif ($data->status === 'ZERO_RESULTS') {
            return false;
        } elseif ($data->status === 'INVALID_REQUEST') {
            return false;
        } else {
            return false;
        }
    } else {
        return false;
    }

}
