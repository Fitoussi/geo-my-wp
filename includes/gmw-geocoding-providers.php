<?php
/**
 * Geocoding service providers.
 *
 * @package geo-my-wp.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Google Maps Geocoder.
 *
 * @since 3.1.
 *
 * @author Eyal Fitoussi.
 */
class GMW_Google_Maps_Geocoder extends GMW_Geocoder {

	/**
	 * Provider.
	 *
	 * @var string
	 */
	public $provider = 'google_maps';

	/**
	 * Geocode API URL.
	 *
	 * @var string
	 */
	public $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json';

	/**
	 * Reverse geocode API URl.
	 *
	 * @var string
	 */
	public $reverse_geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json';

	/**
	 * Get endpoint parameters.
	 *
	 * @param array $options geocoder options.
	 *
	 * @return [type] [description]
	 */
	public function get_endpoint_params( $options ) {

		$location = ( 'reverse_geocode' === $this->type ) ? 'latlng' : 'address';

		$params = array(
			$location  => $this->location,
			'region'   => $options['region'],
			'language' => $options['language'],
			'key'      => trim( gmw_get_option( 'api_providers', 'google_maps_server_side_api_key', '' ) ),
		);

		return $params;
	}

	/**
	 * Get endpoint URL.
	 *
	 * @return [type] [description]
	 */
	public function get_endpoint_url() {

		$china = gmw_get_option( 'api_providers', 'google_maps_api_china', '' );

		if ( '' !== $china ) {
			$this->geocode_url = 'https://maps.google.cn/maps/api/geocode/json';
		}

		// deprecated filer.
		$this->params = apply_filters( 'gmw_google_maps_api_geocoder_args', $this->params );

		// url encode params values.
		/** $this->params = array_map( 'urlencode', $this->params ); */

		return parent::get_endpoint_url();
	}

	/**
	 * Get result data.
	 *
	 * @param  object $geocoded_data geocolocation data.
	 *
	 * @return [type]                [description]
	 */
	public function get_data( $geocoded_data ) {

		// if failed geocoding return error message.
		if ( 'OK' !== $geocoded_data->status ) {
			return array(
				'geocoded' => false,
				'result'   => $geocoded_data->status,
			);

			// Otherwise, return location data.
		} else {
			return array(
				'geocoded' => true,
				'result'   => $this->get_location_fields( $geocoded_data, $this->location_fields ),
			);
		}
	}

	/**
	 * Collect location fields.
	 *
	 * @param  object $geocoded_data geolocation data.
	 *
	 * @param  array  $location      $location data.
	 *
	 * @return [type]                [description]
	 */
	public function get_location_fields( $geocoded_data = array(), $location = array() ) {

		// default values.
		$location['formatted_address'] = sanitize_text_field( $geocoded_data->results[0]->formatted_address );
		$location['lat']               = sanitize_text_field( $geocoded_data->results[0]->geometry->location->lat );
		$location['lng']               = sanitize_text_field( $geocoded_data->results[0]->geometry->location->lng );
		$location['latitude']          = sanitize_text_field( $geocoded_data->results[0]->geometry->location->lat );
		$location['longitude']         = sanitize_text_field( $geocoded_data->results[0]->geometry->location->lng );
		$location['place_id']          = ! empty( $geocoded_data->results[0]->place_id ) ? sanitize_text_field( $geocoded_data->results[0]->place_id ) : '';

		$address_componenets = $geocoded_data->results[0]->address_components;

		// loop through address fields and collect data.
		foreach ( $address_componenets as $geocoded_data ) {

			switch ( $geocoded_data->types[0] ) {

				// street number.
				case 'street_number':
					$location['street_number'] = sanitize_text_field( $geocoded_data->long_name );
					$location['street']        = sanitize_text_field( $geocoded_data->long_name );
					break;

				// street name and street.
				case 'route':
					// street name.
					$location['street_name'] = sanitize_text_field( $geocoded_data->long_name );

					// street ( number + name ).
					if ( ! empty( $location['street_number'] ) ) {

						$location['street'] = $location['street_number'] . ' ' . $location['street_name'];

					} else {

						$location['street'] = $location['street_name'];
					}
					break;

				// premise.
				case 'subpremise':
					$location['premise'] = sanitize_text_field( $geocoded_data->long_name );
					break;

				// neigborhood.
				case 'neighborhood':
					$location['neighborhood'] = sanitize_text_field( $geocoded_data->long_name );
					break;

				// city.
				case 'sublocality_level_1':
				case 'locality':
				case 'postal_town':
					$location['city'] = sanitize_text_field( $geocoded_data->long_name );
					break;

				// county.
				case 'administrative_area_level_2':
				case 'political':
					$location['county'] = sanitize_text_field( $geocoded_data->long_name );
					break;

				// region / state.
				case 'administrative_area_level_1':
					$location['region_code'] = sanitize_text_field( $geocoded_data->short_name );
					$location['region_name'] = sanitize_text_field( $geocoded_data->long_name );
					break;

				// postal code.
				case 'postal_code':
					$location['postcode'] = sanitize_text_field( $geocoded_data->long_name );
					break;

				// country.
				case 'country':
					$location['country_code'] = sanitize_text_field( $geocoded_data->short_name );
					$location['country_name'] = sanitize_text_field( $geocoded_data->long_name );
					break;
			}
		}

		return $location;
	}

	/**
	 * Return error message.
	 *
	 * @param  string $status status code.
	 *
	 * @return [type]         [description]
	 */
	public function get_error_message( $status ) {

		if ( 'ZERO_RESULTS' === $status ) {
			return array(
				'geocoded' => false,
				'error'    => __( 'The data entered could not be geocoded.', 'geo-my-wp' ),
			);
		} elseif ( 'INVALID_REQUEST' === $status ) {
			return array(
				'geocoded' => false,
				'error'    => __( 'Invalid request. Did you enter an address?', 'geo-my-wp' ),
			);
		} elseif ( 'OVER_QUERY_LIMIT' === $status ) {
			return array(
				'geocoded' => false,
				'error'    => __( 'Something went wrong while retrieving your location.', 'geo-my-wp' ) . '<span style="display:none">OVER_QUERY_LIMIT</span>',
			);
		} else {
			return array(
				'geocoded' => false,
				'error'    => __( 'Something went wrong while retrieving your location.', 'geo-my-wp' ),
			);
		}
	}
}

/**
 * Nominatim Geocoder ( OpenStreetMaps ).
 *
 * @since 3.1.
 *
 * @author Eyal Fitoussi.
 */
class GMW_Nominatim_Geocoder extends GMW_Geocoder {

	/**
	 * Provider.
	 *
	 * @var string
	 */
	public $provider = 'nominatim';

	/**
	 * Geoocde URL.
	 *
	 * @var string
	 */
	public $geocode_url = 'https://nominatim.openstreetmap.org/search';

	/**
	 * Reverse geocode URL.
	 *
	 * @var string
	 */
	public $reverse_geocode_url = 'https://nominatim.openstreetmap.org/reverse';

	/**
	 * Get endpoint parameters.
	 *
	 * @param array $options geocoder options.
	 *
	 * @return [type] [description]
	 */
	public function get_endpoint_params( $options ) {

		$params = array(
			'format'          => 'json',
			'email'           => gmw_get_option( 'api_providers', 'nominatim_email', '' ),
			'region'          => $options['region'],
			'accept-language' => $options['language'],
			'addressdetails'  => 1,
			'zoom'            => 18,
			'limit'           => 10,
		);

		if ( 'reverse_geocode' === $this->type ) {

			$coords        = explode( ',', $this->location );
			$params['lat'] = $coords[0];
			$params['lon'] = $coords[1];

		} else {
			$params['q'] = urldecode( $this->location );
		}

		return $params;
	}

	/**
	 * Return geocoding data.
	 *
	 * @param  object $geocoded_data geocoder data.
	 *
	 * @return [type]                [description]
	 */
	public function get_data( $geocoded_data ) {

		if ( ! empty( $geocoded_data->error ) ) {
			return array(
				'geocoded' => false,
				'result'   => $geocoded_data->error,
			);
		} else {
			return array(
				'geocoded' => true,
				'result'   => $this->get_location_fields( $geocoded_data, $this->location_fields ),
			);
		}
	}

	/**
	 * Look for location based on country code.
	 *
	 * @param  object $geocoded_data geocoder data.
	 *
	 * @return [type]                [description]
	 */
	public function get_location_by_region( $geocoded_data ) {

		$location = false;

		// loop through locations and get the one in the default region code.
		foreach ( $geocoded_data as $location_details ) {

			// Look for results based on country code, and abort once found.
			if ( ! empty( $location_details->address->country_code ) && strtolower( $this->params['region'] ) === strtolower( $location_details->address->country_code ) ) {

				$location = $location_details;

				break;
			}
		}

		// If location with region was not found, get the first location
		// in the result.
		if ( ! $location ) {
			$location = $geocoded_data[0];
		}

		return $location;
	}

	/**
	 * Collect Location Fields.
	 *
	 * @param  array $geocoded_data geocoder data.
	 *
	 * @param  array $location      location data.
	 *
	 * @return [type]                [description]
	 */
	public function get_location_fields( $geocoded_data = array(), $location = array() ) {

		$location_data = array();

		// If multiple location returned, we will look for the
		// result with the default country code.
		if ( is_array( $geocoded_data ) ) {

			if ( 1 === count( $geocoded_data ) ) {
				$geocoded_data = $geocoded_data[0];
			} else {
				$geocoded_data = $this->get_location_by_region( $geocoded_data );
			}
		}

		foreach ( $geocoded_data as $field_name => $field_value ) {

			if ( 'display_name' === $field_name ) {
				$location['formatted_address'] = sanitize_text_field( $field_value );
			}

			if ( 'lat' === $field_name ) {
				$location['lat']      = sanitize_text_field( $field_value );
				$location['latitude'] = $location['lat'];
			}

			if ( 'lon' === $field_name ) {
				$location['lng']       = sanitize_text_field( $field_value );
				$location['longitude'] = $location['lng'];
			}

			if ( 'place_id' === $field_name ) {
				$location['place_id'] = sanitize_text_field( $field_value );
			}
		}

		$address_componenets = $geocoded_data->address;

		// loop through address fields and collect data.
		foreach ( $address_componenets as $field_name => $field_value ) {

			if ( 'house_number' === $field_name ) {
				$location['street_number'] = sanitize_text_field( $field_value );
			}

			if ( 'road' === $field_name ) {
				$location['street_name'] = sanitize_text_field( $field_value );
				$location['street']      = $location['street_number'] . ' ' . $location['street_name'];
			}

			if ( 'city' === $field_name ) {
				$location['city'] = sanitize_text_field( $field_value );
			}

			if ( 'county' === $field_name ) {
				$location['county'] = sanitize_text_field( $field_value );
			}

			if ( 'state' === $field_name ) {
				$location['region_name'] = sanitize_text_field( $field_value );
			}

			if ( 'postcode' === $field_name ) {
				$location['postcode'] = sanitize_text_field( $field_value );
			}

			if ( 'country' === $field_name ) {
				$location['country_name'] = sanitize_text_field( $field_value );
			}

			if ( 'country_code' === $field_name ) {
				$location['country_code'] = sanitize_text_field( $field_value );
			}
		}

		// Look for City in different address field.
		if ( isset( $address_componenets->city ) ) {

			$location['city'] = $address_componenets->city;

		} elseif ( isset( $address_componenets->town ) ) {

			$location['town'] = $address_componenets->town;

		} elseif ( isset( $address_componenets->suburb ) ) {

			$location['city'] = $address_componenets->suburb;
		}

		return $location;
	}
}
