<?php
/**
 * Main Geocoder class.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GEO my WP base geocoder class.
 *
 * Can be extended to work with different geocoding APIs.
 *
 * @Since 3.1.
 *
 * @Author Eyal Fitoussi
 */
class GMW_Geocoder {

	/**
	 * Provider.
	 *
	 * @var string
	 */
	public $provider = 'nominatim';

	/**
	 * Geocoding URL.
	 *
	 * @var string
	 */
	public $geocode_url = 'https://nominatim.openstreetmap.org/search';

	/**
	 * Reverse geocoding URL.
	 *
	 * @var string
	 */
	public $reverse_geocode_url = 'https://nominatim.openstreetmap.org/reverse';

	/**
	 * Geocoding type.
	 *
	 * @var string
	 */
	public $type = 'geocode';

	/**
	 * Address or coords to geocode.
	 *
	 * @var string
	 */
	public $location = '';

	/**
	 * Endpoint parameters.
	 *
	 * @var array
	 */
	public $params = array();

	/**
	 * Default location fields to return.
	 *
	 * @var array
	 */
	public $location_fields = array(
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
		'formatted_address' => '',
		'lat'               => '', // to support older versions.
		'lng'               => '', // to support older versions.
		'latitude'          => '',
		'longitude'         => '',
		'place_id'          => '',
	);

	/**
	 * [__construct description]
	 *
	 * @param string $provider geocoding provider.
	 */
	public function __construct( $provider = '' ) {

		$this->provider = ! empty( $provider ) ? $provider : $this->provider;
		$this->params   = array(
			'region'   => gmw_get_option( 'general_settings', 'country_code', 'us' ),
			'language' => gmw_get_option( 'general_settings', 'language_code', 'en' ),
		);
	}

	/**
	 * Get endpoint parameters.
	 *
	 * @param array $options options to pass to the geocoder.
	 *
	 * @return options.
	 */
	public function get_endpoint_params( $options ) {
		return $options;
	}

	/**
	 * Prepare address for the geocoder. Remove unwanted characters.
	 *
	 * @param  string $raw_data string.
	 *
	 * @return [type]           [description]
	 */
	public function parse_raw_data( $raw_data ) {

		$characters = array(
			' ' => '+',
			'?' => '',
			'&' => '',
			'=' => '',
			'#' => '',
		);

		if ( 'google_maps' === $this->provider ) {
			$characters[','] = '';
		}

		// Clean up address from invalid characters.
		$invalid_chars = apply_filters(
			'gmw_geocoder_invalid_characters',
			$characters,
			$this,
			$raw_data
		);

		return trim( strtolower( str_replace( array_keys( $invalid_chars ), array_values( $invalid_chars ), $raw_data ) ) );
	}

	/**
	 * Check if the location provided is address or coordiantes.
	 *
	 * We need to know if to geocode or reverse geocoder.
	 *
	 * @param  mixed $raw_data string of address || array of coords.
	 *
	 * @return [type]           [description]
	 */
	public function verify_data( $raw_data = '' ) {

		if ( empty( $raw_data ) && ! empty( $this->location ) ) {
			$raw_data = $this->location;
		}

		$raw_data = apply_filters( 'gmw_geocoder_raw_data', $raw_data );

		// if data is array, then it should be coordinates.
		if ( is_array( $raw_data ) ) {

			// convert to lat,lng comma separated.
			$location   = implode( ',', $raw_data );
			$this->type = 'reverse_geocode';

			// if not array, then it should be an address.
		} else {

			$location   = $this->parse_raw_data( $raw_data );
			$this->type = 'geocode';
		}

		return $location;
	}

	/**
	 * Get endpoint URL.
	 *
	 * @return [type] [description]
	 */
	public function get_endpoint_url() {

		$url_type = $this->type . '_url';

		// can modify the URL params.
		$args = apply_filters(
			'gmw_geocoder_endpoint_args',
			array(
				'url_base'   => $this->$url_type . '?',
				'url_params' => $this->params,
			),
			$this
		);

		// deprecated. Will be removed in the future.
		$args = apply_filters( 'gmw_geocoder_endpoint_url', $args, $this );

		// remove any extra spaces from parameters.
		$params = array_map( 'trim', $args['url_params'] );
		$url    = $args['url_base'];

		/**
		 * If region exists, lets place it at the beggining of the array.
		 *
		 * We do this to prevnt the &region renders as ®ion and break the URL.
		 *
		 * This solution should work until we find a less hacky one.
		 */
		if ( array_key_exists( 'region', $params ) ) {

			$url .= 'region=' . $params['region'] . '&';

			unset( $params['region'] );
		}

		return $url . http_build_query( $params );
	}

	/**
	 * Get the geolocation data using a child class.
	 *
	 * @param  array $geocoded_data the geocoded data.
	 *
	 * @return [type]                [description]
	 */
	public function get_data( $geocoded_data ) {
		return $geocoded_data;
	}

	/**
	 * Geocode function.
	 *
	 * @param  mixed   $raw_data       string of address || array of coords.
	 *
	 * @param  array   $options       geocoder options.
	 *
	 * @param  boolean $force_refresh [description].
	 *
	 * @return [type]                 [description]
	 */
	public function geocode( $raw_data = '', $options = array(), $force_refresh = false ) {

		// Verify location.
		$this->location = $this->verify_data( $raw_data );

		// abort if no location provided.
		if ( empty( $this->location ) ) {

			$status = __( 'Location is missing.', 'geo-my-wp' );

			return $this->failed( $status, array() );
		}

		// Get geocoder default options.
		$this->params = $this->get_endpoint_params( $this->params );

		// Merge provided options.
		if ( is_array( $options ) ) {
			$this->params = array_merge( $this->params, $options );
		}

		// look for geocoded location in cache.
		$address_hash    = md5( $this->location );
		$location_output = get_transient( 'gmw_geocoded_' . $address_hash );
		$location_output = apply_filters( 'gmw_transient_location_output', $location_output, $address_hash );
		$response        = array();

		// if no location found in cache or if forced referesh try to geocode.
		if ( true === $force_refresh || false === $location_output ) {

			// get data from the provider.
			$result = wp_remote_get( $this->get_endpoint_url() );

			// abort if remote connection failed.
			if ( is_wp_error( $result ) ) {
				return $this->failed( $result->get_error_message(), $result );
			}

			// look for geocoded data.
			$geocoded_data = wp_remote_retrieve_body( $result );

			// abort if no data found.
			if ( is_wp_error( $geocoded_data ) ) {

				$status = __( 'Geocoding failed', 'geo-my-wp' );

				return $this->failed( $status, $geocoded_data );
			}

			// if response successful.
			if ( 200 === wp_remote_retrieve_response_code( $result ) ) {

				// decode the data.
				$geocoded_data = json_decode( $geocoded_data );

				// if geocoding success.
				if ( ! empty( $geocoded_data ) ) {

					// get geocoded data. Return either location fields or error message.
					$response = $this->get_data( $geocoded_data );

					$response['data'] = $geocoded_data;

					// If location was found.
					if ( $response['geocoded'] ) {

						// add missing address field.
						if ( 'reverse_geocode' === $this->type ) {
							$response['result']['address'] = $response['result']['formatted_address'];
						} else {
							$response['result']['address'] = sanitize_text_field( urldecode( $this->location ) );
						}

						// hook after geocoding.
						do_action( 'gmw_geocoded_location', $response['result'], $response, $address_hash );

						// Modify cache expiration time.
						$expiration = apply_filters( 'gmw_geocoder_transient_expiration', DAY_IN_SECONDS * 7 );

						// cache location.
						set_transient( 'gmw_geocoded_' . $address_hash, $response['result'], $expiration );

						// we need to pass the output via $location_output.
						$location_output = $response['result'];

						// can run custom function on sucess.
						$this->success( $location_output, $response );

						// return error message.
					} else {
						return $this->failed( $response['result'], $geocoded_data );
					}

					// If geocode failed display errors.
				} else {

					$status = __( 'Location data was not found.', 'geo-my-wp' );

					return $this->failed( $status, $geocoded_data );
				}
			} else {

				$status = __( 'Unable to contact the API service or failed geocoding.', 'geo-my-wp' );

				return $this->failed( $status, $geocoded_data );
			}
		}

		return apply_filters( 'gmw_geocoded_location_output', $location_output, $response, $address_hash );
	}

	/**
	 * Success call back function.
	 *
	 * Can be used in class child.
	 *
	 * @param  [type] $location_output [description].
	 *
	 * @param  [type] $response        [description].
	 */
	public function success( $location_output, $response ) {}

	/**
	 * Location failed function.
	 *
	 * @param  string $status error message.
	 *
	 * @param  array  $data   geocoder data.
	 *
	 * @return [type]         [description]
	 */
	public function failed( $status, $data ) {

		// generate warning showing the error message when geocoder fails.
		if ( 'ZERO_RESULTS' !== $status ) {

			$message = $status;

			if ( ! empty( $data->error_message ) ) {
				$message .= ' - ' . $data->error_message;
			}

			gmw_trigger_error( 'GEO my WP geocoder failed. Error : ' . $message );
		}

		return array(
			'error' => $status,
			'data'  => $data,
		);
	}
}

/**
 *
 * GEO my WP Geocoder.
 *
 * @since 1.0
 *
 * @author Eyal Fitoussi
 *
 * @param  string||array $raw_data can be address as a string or coords as of array( 'latitude','longitude' ).
 *
 * @param  boolean       $force_refresh true to ignore data saved in cache.
 *
 * @return array geocoded data.
 */
function gmw_geocoder( $raw_data = '', $force_refresh = false ) {

	// get provider.
	$provider = GMW()->geocoding_provider;

	// Generate class name.
	$class_name = 'GMW_' . $provider . '_Geocoder';

	// verify that provider geocoding exists. Otherwise, use Nominatim as default.
	if ( ! class_exists( 'GMW_' . $provider . '_Geocoder' ) ) {
		$class_name = 'GMW_Nominatim_Geoocoder';
	}

	$geocoder = new $class_name( $provider );

	return $geocoder->geocode( $raw_data, array(), $force_refresh );
}
