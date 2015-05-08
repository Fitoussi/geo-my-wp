<?php
define( 'GMW_CL_PATH', GMW_PATH . '/plugins/current-location' );
define( 'GMW_CL_URL', GMW_URL . '/plugins/current-location' );

//include scripts in the front end
function gmw_cl_register_scripts() {
	
	wp_register_script( 'gmw-cl', GMW_CL_URL.'/assets/js/gmw-cl.min.js', array( 'jquery', 'gmw-js' ), GMW_VERSION, true );
	wp_register_style( 'gmw-cl-style', GMW_CL_URL.'/assets/css/gmw-cl-style.css' );
	wp_enqueue_style( 'gmw-cl-style' );
}
add_action( 'wp_enqueue_scripts', 'gmw_cl_register_scripts' );

//include files in front-end
if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
	include( 'includes/geo-my-wp-current-location-class.php' );
	include( 'includes/geo-my-wp-current-location-shortcode.php' );
}

//include widgets
include( 'includes/geo-my-wp-current-location-widget.php' );