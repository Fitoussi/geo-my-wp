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
	
	global $gmw_options;

	$protocol = is_ssl() ? 'https' : 'http';
		
	//register google maps api
	if ( ! wp_script_is( 'google-maps', 'registered' ) && apply_filters( 'gmw_google_maps_api', true ) ) {

		//Build Google API url. elements can be modified via filters
		$google_url = apply_filters( 'gmw_google_maps_api_url', array( 
			'protocol'	=> $protocol,
			'url_base'	=> '://maps.googleapis.com/maps/api/js?',
			'url_data'	=> http_build_query( apply_filters( 'gmw_google_maps_api_args', array(
				'libraries' => 'places',
            	'key'		=> gmw_get_option( 'general_settings', 'google_api', '' ),
         		'region'	=> gmw_get_option( 'general_settings', 'country_code', 'US' ),
              	'language'	=> gmw_get_option( 'general_settings', 'language_code', 'EN' ),
        	) ), '', '&amp;'),
		), $gmw_options );

		wp_register_script( 'google-maps', implode( '', $google_url ) , array(), GMW_VERSION, true );
	}

	// register gmw script
	wp_register_script( 'gmw', GMW_URL.'/assets/js/gmw.min.js', array( 'jquery', 'google-maps' ), GMW_VERSION, true );

	// ajax url
	wp_localize_script( 'gmw', 'gmwAjaxUrl', GMW()->ajax_url );

	// localize gmw options
	wp_localize_script( 'gmw', 'gmwSettings', $gmw_options );

	wp_enqueue_style( 'gmw-fonts', GMW_URL . '/assets/css/gmw.font.min.css', array(), GMW_VERSION );

	// register chosen scripts/style
	if ( ! wp_style_is( 'chosen', 'registered' ) ) {
		wp_register_style( 'chosen',  GMW_URL . '/assets/css/lib/chosen.min.css', array(), GMW_VERSION );
	}
	if ( ! wp_script_is( 'chosen', 'registered' ) ) {
		wp_register_script( 'chosen', GMW_URL . '/assets/js/lib/chosen.jquery.min.js', array( 'jquery' ), GMW_VERSION, true );
	}

	// register location form scripts/styles
	wp_register_style( 'gmw-location-form', GMW_URL.'/includes/location-form/assets/css/gmw.lf.css', array(), GMW_VERSION );            
    wp_register_script( 'gmw-location-form', GMW_URL.'/includes/location-form/assets/js/gmw.lf.min.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );
   
	// register in front-end only
	if ( ! IS_ADMIN ) {
						
		// include GMW main stylesheet
		wp_enqueue_style( 'gmw-frontend', GMW_URL.'/assets/css/gmw.front.min.css', array(), GMW_VERSION );
							
		//register gmw map script
		wp_register_script( 'gmw-map', GMW_URL.'/assets/js/gmw.map.min.js', array( 'google-maps', 'gmw' ), GMW_VERSION, true );			
		$form_styles = apply_filters( 'gmw_load_form_styles_in_head', array() );

		// load form stylesheets early
		if ( ! empty( $form_styles ) ) {
			foreach( $form_styles as $form_style ) {
				gmw_enqueue_form_styles( $form_style );
			}
		}

	//register scripts/styles in admin only
	} else {
		
		wp_enqueue_style( 'gmw-admin', GMW_URL . '/assets/css/gmw.admin.min.css', array(), GMW_VERSION );
		
		// enqueue on GMW admin pages only
		if ( ! empty( $_GET['page'] ) && strpos( $_GET['page'], 'gmw') !== false ) {
			
			wp_enqueue_script( 'gmw-admin', GMW_URL . '/assets/js/gmw.admin.min.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );
		}

		// register locations importer
    	wp_register_script( 'gmw-locations-importer', GMW_URL . '/includes/admin/pages/import-export/locations-importer/assets/js/gmw.locations.importer.min.js', array( 'jquery' ), GMW_VERSION );
    	wp_register_style( 'gmw-locations-importer', GMW_URL . '/includes/admin/pages/import-export/locations-importer/assets/css/gmw.locations.importer.min.css', array(), GMW_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'gmw_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'gmw_enqueue_scripts' );