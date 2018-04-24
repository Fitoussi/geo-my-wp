<?php
// Block direct requests
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW enqueue scripts and styles
 *
 * Note, some additional script / styles enqueue in class-gmw-maps-api.php file.
 * @return [type] [description]
 */
function gmw_enqueue_scripts() {

	$gmw_options    = gmw_get_options_group();
	$protocol       = is_ssl() ? 'https' : 'http';
	$api_key_status = true;
	$api_key_option = gmw_get_option( 'general_settings', 'google_maps_api_usage', 'enabled' );

	// enabled/ disable API key registration
	if ( empty( $api_key_option ) || 'enabled' == $api_key_option ) {

		$api_key_status = true;

	} elseif ( 'disabled' == $api_key_option || ( IS_ADMIN && 'admin' == $api_key_option ) || ( ! IS_ADMIN && 'frontend' == $api_key_option ) ) {

		$api_key_status = false;
	}

	//register google maps api. Can be disabled via filter other than the settings.
	if ( apply_filters( 'gmw_google_maps_api', true ) && $api_key_status ) {

		$handle = 'google-maps';

		if ( ! wp_script_is( 'google-maps', 'registered' ) ) {

			//Build Google API url. elements can be modified via filters
			$google_url = apply_filters(
				'gmw_google_maps_api_url', array(
					'protocol' => $protocol,
					'url_base' => '://maps.googleapis.com/maps/api/js?',
					'url_data' => http_build_query(
						apply_filters(
							'gmw_google_maps_api_args', array(
								'libraries' => 'places',
								'key'       => gmw_get_option( 'general_settings', 'google_api', '' ),
								'region'    => gmw_get_option( 'general_settings', 'country_code', 'US' ),
								'language'  => gmw_get_option( 'general_settings', 'language_code', 'EN' ),
							)
						), '', '&amp;'
					),
				), $gmw_options
			);

			wp_register_script( 'google-maps', implode( '', $google_url ), array(), GMW_VERSION, true );
		}

		// if Maps API registration is disabled, the google maps handle can be modified
		// in case that another plugin loads the Maps API
	} else {

		$handle = apply_filters( 'gmw_google_maps_api_handle', false );
	}

	// register gmw script
	wp_register_script(
		'gmw',
		GMW_URL . '/assets/js/gmw.min.js',
		! empty( $handle ) ? array( 'jquery', $handle ) : array( 'jquery' ),
		GMW_VERSION,
		true
	);

	// ajax url
	wp_localize_script( 'gmw', 'gmwAjaxUrl', GMW()->ajax_url );

	// check if in admin or front-end
	wp_localize_script( 'gmw', 'gmwIsAdmin', array( IS_ADMIN ) );

	$js_geocoder = ( apply_filters( 'gmw_client_side_geocoder_enabled', true ) ) ? array( true ) : array( false );

	wp_localize_script( 'gmw', 'clientSideGeocoder', $js_geocoder );

	/**
	 * localize gmw options
	 *
	 * For now it seems that we don't need to pass all the settings
	 *
	 * So we only pass the general settings.
	 *
	 * This can be changed in the future if needed.
	 **/
	$general_settings = $gmw_options['general_settings'];

	// remove the API key from the localize settings.
	unset( $general_settings['google_api'] );

	wp_localize_script( 'gmw', 'gmwSettings', array( 'general_settings' => $general_settings ) );

	// fonts
	wp_enqueue_style( 'gmw-fonts', GMW_URL . '/assets/css/gmw.font.min.css', array(), GMW_VERSION );

	// register location form scripts/styles
	wp_register_style( 'gmw-location-form', GMW_URL . '/includes/location-form/assets/css/gmw.location.form.min.css', array(), GMW_VERSION );
	wp_register_script( 'gmw-location-form', GMW_URL . '/includes/location-form/assets/js/gmw.location.form.min.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );

	// register in front-end only
	if ( ! IS_ADMIN ) {

		// include GMW main stylesheet
		wp_enqueue_style( 'gmw-frontend', GMW_URL . '/assets/css/gmw.frontend.min.css', array(), GMW_VERSION );

		//register gmw map script
		wp_register_script( 'gmw-map', GMW_URL . '/assets/js/gmw.map.min.js', array( 'gmw' ), GMW_VERSION, true );

		// load styles in head
		$form_styles = apply_filters( 'gmw_load_form_styles_in_head', array() );

		// load form stylesheets early
		if ( ! empty( $form_styles ) ) {
			foreach ( $form_styles as $form_style ) {
				gmw_enqueue_form_styles( $form_style );
			}
		}

		//register scripts/styles in admin only
	} else {

		wp_enqueue_style( 'gmw-admin', GMW_URL . '/assets/css/gmw.admin.min.css', array(), GMW_VERSION );

		// enqueue on GMW admin pages only
		if ( ! empty( $_GET['page'] ) && strpos( $_GET['page'], 'gmw' ) !== false ) {
			wp_enqueue_script( 'gmw-admin', GMW_URL . '/assets/js/gmw.admin.min.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );
		}

		// register locations importer
		wp_register_script( 'gmw-locations-importer', GMW_URL . '/includes/admin/pages/import-export/locations-importer/assets/js/gmw.locations.importer.min.js', array( 'jquery' ), GMW_VERSION );
		wp_register_style( 'gmw-locations-importer', GMW_URL . '/includes/admin/pages/import-export/locations-importer/assets/css/gmw.locations.importer.min.css', array(), GMW_VERSION );

		// register chosen scripts/style in back-end
		if ( ! wp_style_is( 'chosen', 'registered' ) ) {
			wp_register_style( 'chosen', GMW_URL . '/assets/lib/chosen/chosen.min.css', array(), GMW_VERSION );
		}
		if ( ! wp_script_is( 'chosen', 'registered' ) ) {
			wp_register_script( 'chosen', GMW_URL . '/assets/lib/chosen/chosen.jquery.min.js', array( 'jquery' ), GMW_VERSION, true );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'gmw_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'gmw_enqueue_scripts' );
