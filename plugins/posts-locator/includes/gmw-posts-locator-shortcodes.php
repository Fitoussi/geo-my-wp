<?php
/**
 * GEO my WP - Posts Locator shortcodes page.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate the post location form.
 *
 * @param  array $atts shortcode attributes.
 *
 * @return [type]       [description]
 */
function gmw_post_location_form_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	ob_start();

	gmw_post_location_form( $atts );

	$content = ob_get_clean();

	return $content;
}
add_shortcode( 'gmw_post_location_form', 'gmw_post_location_form_shortcode' );

/**
 * Output the location address fields of a post.
 *
 * @param  array $atts shortcode attributes.
 *
 * @see gmw_get_post_address() for the list of attributes ( includes/gmw-posts-locator-functions.php ).
 */
function gmw_get_post_address_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	ob_start();

	echo gmw_get_post_address( $atts ); // WPCS: XSS ok.

	$content = ob_get_clean();

	return $content;
}
add_shortcode( 'gmw_post_address', 'gmw_get_post_address_shortcode' );

/**
 * Output post location fields or location meta fields.
 *
 * @param  array $atts shortcode attributes.
 *
 * @see gmw_get_post_location_fields() for the list of attributes ( includes/gmw-posts-locator-functions.php ).
 */
function gmw_get_post_location_fields_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	ob_start();

	echo gmw_get_post_location_fields( $atts ); // WPCS: XSS ok.

	$content = ob_get_clean();

	return $content;
}
add_shortcode( 'gmw_post_location_fields', 'gmw_get_post_location_fields_shortcode' );
