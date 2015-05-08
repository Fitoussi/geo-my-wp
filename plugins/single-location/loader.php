<?php
define( 'GMW_SL_PATH', GMW_PATH . '/plugins/single-location/' );
define( 'GMW_SL_URL', GMW_URL . '/plugins/single-location/' );

//include scripts in the front end
function gmw_sl_register_scripts() {
	wp_register_style( 'gmw-sl-style', GMW_SL_URL.'assets/css/gmw-sl-style.css' );
	wp_enqueue_style( 'gmw-sl-style' );
	wp_register_script( 'gmw-sl-live-directions', GMW_SL_URL.'/assets/js/gmw-sl-live-directions.min.js', array( 'jquery' ), GMW_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'gmw_sl_register_scripts' );

//include files in front-end
if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
	include( 'includes/gmw-sl-class.php' );
	include( 'includes/gmw-sl-shortcodes.php' );
}

//include widgets
include( 'includes/gmw-sl-widgets.php' );