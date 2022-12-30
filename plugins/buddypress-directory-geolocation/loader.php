<?php
/**
 * GEO my WP BuddyPress Directory Geolocation add-on.
 *
 * This is the base class of geolocation features that can be extended to integrat with the directory pages of BuddyPress.
 *
 * @author Eyal Fitoussi
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'includes/class-gmw-buddypress-directory-geolocation.php';

function gmw_bpdg_register_scripts() {
	wp_register_script( 'gmw-bpdg', GMW_PLUGINS_URL . '/buddypress-directory-geolocation/assets/js/gmw.bpdg.min.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'gmw_bpdg_register_scripts' );

