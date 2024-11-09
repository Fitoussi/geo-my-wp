<?php
/**
 * GEO my WP Enqueue Scripts
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Block direct requests.
}

/**
 * Register Google Maps API.
 *
 * @since 3.1
 */
function gmw_register_google_maps_api() {

	if ( apply_filters( 'gmw_google_maps_api', true ) ) {

		if ( ! wp_script_is( 'google-maps', 'registered' ) ) {

			$china = gmw_get_option( 'api_providers', 'google_maps_api_china', '' );
			$url   = empty( $china ) ? '://maps.googleapis.com/maps/api/js?' : '://maps.google.cn/maps/api/js?';

			// Generate Google API url. elements can be modified via filters.
			$google_url = apply_filters(
				'gmw_google_maps_api_url',
				array(
					'protocol' => 'https',
					'url_base' => $url,
					'url_data' => http_build_query(
						apply_filters(
							'gmw_google_maps_api_args',
							array(
								'region'    => gmw_get_option( 'general_settings', 'country_code', 'us' ),
								'libraries' => 'google_maps' === GMW()->maps_provider ? 'places' : '',
								'key'       => trim( gmw_get_option( 'api_providers', 'google_maps_client_side_api_key', '' ) ),
								'language'  => gmw_get_option( 'general_settings', 'language_code', 'en' ),
								'loading'   => 'async',
								'callback'  => 'Function.prototype',
							)
						)
					),
				),
				gmw_get_options_group()
			);

			wp_register_script( 'google-maps', implode( '', $google_url ), array(), GMW_VERSION, true );
		}
	}
}

/**
 * Enqueue forms stylesheet when needed.
 *
 * @since 4.0
 *
 * @param  string $where [description].
 */
function gmw_enqueue_form_stylesheet( $where = 'form' ) {

	if ( ! wp_style_is( 'gmw-frontend', 'enqueue' ) ) {
		wp_enqueue_style( 'gmw-frontend' );
	}

	if ( ! wp_style_is( 'gmw-forms', 'enqueue' ) ) {
		wp_enqueue_style( 'gmw-forms' );
	}

	/*if ( in_array( $where, array( 'form', 'location_form', 'buddypress_directory', 'locations_dashboard', 'single_location', 'single_object_locations' ), true ) && ! wp_style_is( 'gmw-forms', 'enqueue' ) ) {
		wp_enqueue_style( 'gmw-forms' );
	}*/
}
add_action( 'gmw_element_loaded', 'gmw_enqueue_form_stylesheet' );

/**
 * GMW enqueue scripts and styles
 *
 * Note, some additional script / styles enqueue in class-gmw-maps-api.php file.
 */
function gmw_enqueue_scripts() {

	$gmw_options      = gmw_get_options_group();
	$maps_provider    = GMW()->maps_provider;
	$geocode_provider = GMW()->geocoding_provider;
	$main_scripts     = array( 'jquery' );
	$map_scripts      = array( 'jquery', 'gmw' );
	$lf_scripts       = array( 'jquery', 'gmw' );
	$lf_styles        = array();

	// load maps provider.
	if ( 'google_maps' === $maps_provider ) {

		$main_scripts[] = 'google-maps';
		gmw_register_google_maps_api();

	} elseif ( 'leaflet' === $maps_provider ) {

		$map_scripts[] = 'leaflet';
		$lf_scripts[]  = 'leaflet';
		$lf_styles[]   = 'leaflet';

		wp_register_script( 'leaflet', GMW_URL . '/assets/lib/leaflet/leaflet.min.js', array(), '1.7.1', true );
		wp_register_style( 'leaflet', GMW_URL . '/assets/lib/leaflet/leaflet.min.css', array(), '1.7.1' );

		//wp_enqueue_script( 'locationiq-autocomplete', 'https://tiles.locationiq.com/v3/libs/leaflet-geocoder/1.9.6/leaflet-geocoder-locationiq.min.js', $map_scripts, '0.1.7', true );
		//wp_enqueue_style( 'locationiq-autocomplete','https://tiles.locationiq.com/v3/libs/leaflet-geocoder/1.9.6/leaflet-geocoder-locationiq.min.css', array(), '1.7.1' );

	} else {
		do_action( 'gmw_register_maps_provider_' . $maps_provider );
	}

	// load geocoding providers.
	if ( 'google_maps' === $geocode_provider ) {

		// Load geocoding provider only if other than Google Maps.
		// Otherwise, no need to load Google Maps again as it is already loaded as map provider.
		if ( 'google_maps' !== $maps_provider ) {
			$main_scripts[] = 'google-maps';
			gmw_register_google_maps_api();
		};

		// Load custom geocoding provider.
	} else {
		do_action( 'gmw_register_geocoding_provider_' . $geocode_provider );
	}

	// $main_scripts = apply_filters( 'gmw_main_script_dependencies', $main_scripts );
	// register gmw script
	wp_register_script( 'gmw', GMW_URL . '/assets/js/gmw.core.min.js', $main_scripts, GMW_VERSION, true );

	// Variables to localize as JavaScript.
	$options = apply_filters(
		'gmw_localize_options',
		array(
			'settings'           => array(
				'general' => ! empty( $gmw_options['general_settings'] ) ? $gmw_options['general_settings'] : array(),
				'api'     => isset( $gmw_options['api_providers'] ) ? $gmw_options['api_providers'] : array(),
			),
			'mapsProvider'          => $maps_provider,
			'geocodingProvider'     => $geocode_provider,
			'googleAdvancedMarkers' => empty( $gmw_options['api_providers']['google_maps_legacy_marker'] ) ? true : false,
			'defaultIcons'          => GMW()->default_icons,
			'isAdmin'               => IS_ADMIN,
			'ajaxUrl'               => GMW()->ajax_url,
			'locatorAlerts'         => apply_filters( 'gmw_auto_locator_alerts_enabled', true ) ? '1' : '0',
			'ulcPrefix'             => gmw_get_ulc_prefix(),
			'pageLocatorRefresh'    => true,
			'protocol'              => is_ssl() ? 'https' : 'http',
		),
		$gmw_options
	);

	if ( is_admin() ) {
		$options['get_field_options_ajax_nonce'] = wp_create_nonce('gmw_get_field_options_ajax_nonce' );
	}

	wp_localize_script( 'gmw', 'gmwVars', $options );

	// register location form.
	wp_register_style( 'gmw-location-form', GMW_URL . '/includes/location-form/assets/css/gmw.location.form.min.css', $lf_styles, GMW_VERSION );
	wp_register_script( 'gmw-location-form', GMW_URL . '/includes/location-form/assets/js/gmw.location.form.min.js', $lf_scripts, GMW_VERSION, true );

	// include GMW main stylesheet.
	wp_register_style( 'gmw-frontend', GMW_URL . '/assets/css/gmw.frontend.min.css', array(), GMW_VERSION );
	//wp_enqueue_style( 'gmw-frontend' );
	wp_register_style( 'gmw-forms', GMW_URL . '/assets/css/gmw.forms.min.css', array( 'gmw-frontend' ), GMW_VERSION );

	$colors_css_output = '';

	// Load color styles from settings page.
	if ( ! empty( $gmw_options['styles'] ) ) {

		foreach ( $gmw_options['styles'] as $color_name => $color_value ) {

			if ( empty( $color_value ) ) {
				continue;
			}
			$colors_css_output .= '--gmw-' . str_replace( '_', '-', $color_name ) . ':' . $color_value . ';';
		}
	}

	wp_add_inline_style( 'gmw-frontend', ':root{' . $colors_css_output . '}' );

	// Map script.
	wp_register_script( 'gmw-map', GMW_URL . '/assets/js/gmw.map.min.js', $map_scripts, GMW_VERSION, true );

	// register in front-end only.
	if ( ! IS_ADMIN ) {

		// load styles in head.
		$form_styles = apply_filters( 'gmw_load_form_styles_in_head', array() );

		// load form stylesheets early.
		if ( ! empty( $form_styles ) ) {
			foreach ( $form_styles as $form_style ) {
				gmw_enqueue_form_styles( $form_style );
			}
		}

		// register scripts/styles in admin only.
	} else {

		wp_register_script( 'gmw-admin', GMW_URL . '/assets/js/gmw.admin.min.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );

		// fonts file in admin only. In front-end it is combined with front-end stylesheet.
		wp_enqueue_style( 'gmw-fonts', GMW_URL . '/assets/css/gmw.font.min.css', array(), GMW_VERSION );
		wp_enqueue_style( 'gmw-admin', GMW_URL . '/assets/css/gmw.admin.min.css', array(), GMW_VERSION );

		// enqueue on GMW admin pages only.
		if ( ( ! empty( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'gmw' ) !== false ) || ( isset( $_GET['post_type'] ) && 'gmw_location_type' === $_GET['post_type'] )  ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			wp_enqueue_script( 'gmw-admin' );
		}

		// register locations importer.
		wp_register_script( 'gmw-locations-importer', GMW_URL . '/includes/admin/pages/import-export/locations-importer/assets/js/gmw.locations.importer.min.js', array( 'jquery' ), GMW_VERSION, true );
		wp_register_style( 'gmw-locations-importer', GMW_URL . '/includes/admin/pages/import-export/locations-importer/assets/css/gmw.locations.importer.min.css', array(), GMW_VERSION );

		// register chosen scripts/style in back-end.
		/*
		if ( ! wp_style_is( 'chosen', 'registered' ) ) {
			wp_register_style( 'chosen', GMW_URL . '/assets/lib/chosen/chosen.min.css', array(), '1.8.7' );
		}
		if ( ! wp_script_is( 'chosen', 'registered' ) ) {
			wp_register_script( 'chosen', GMW_URL . '/assets/lib/chosen/chosen.jquery.min.js', array( 'jquery' ), '1.8.7', true );
		}*/

		// register select2 script and style.
		if ( ! wp_style_is( 'gmw-select2', 'registered' ) ) {
			wp_register_style( 'gmw-select2', GMW_URL . '/assets/lib/select2/css/select2.min.css', array(), '4.0.13' );
		}

		if ( ! wp_script_is( 'gmw-select2', 'registered' ) ) {
			wp_register_script( 'gmw-select2', GMW_URL . '/assets/lib/select2/js/select2.full.min.js', array( 'jquery' ), '4.0.13', true );
		}

		if ( ! wp_script_is( 'jquery-confirm', 'registered' ) ) {
			wp_register_script( 'jquery-confirm', GMW_URL . '/assets/lib/jquery-confirm/jquery-confirm.min.js', array( 'jquery' ), GMW_VERSION, true );
			wp_register_style( 'jquery-confirm', GMW_URL . '/assets/lib/jquery-confirm/jquery-confirm.min.css', array(), GMW_VERSION );
		}

		// Menu icon styling.
		$style = '.menu-top.toplevel_page_gmw-extensions img {
			margin-top: -5px;
			height: 28px;
			width: auto;
		}
		body #footer-thankyou {
			position: fixed;
			bottom: 7px;
			z-index: 999;
			margin-left: 240px;
		}

		body.toplevel_page_gmw-extensions #footer-thankyou,
		.geo-my-wp_page_gmw-forms:not( .geo-my-wp_page_gmw-form-editor ) #footer-thankyou {
			margin-left: 0;
		}';
		wp_add_inline_style( 'wp-admin', $style );
	}
}
add_action( 'wp_enqueue_scripts', 'gmw_enqueue_scripts', 50 );
add_action( 'admin_enqueue_scripts', 'gmw_enqueue_scripts' );
