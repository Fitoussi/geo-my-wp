<?php
/**
 * GEO my WP - Posts Locator AJAX info-window loader.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

// get the post object.
$post = get_post( $location->object_id ); // WPCS: override ok.

// get additional post location data.
$location_data = gmw_get_post_location( $location->location_id, true );

$fields = array(
	'object_type',
	'object_id',
	'lat',
	'lng',
	'latitude',
	'longitude',
	'street',
	'premise',
	'city',
	'region_code',
	'region_name',
	'postcode',
	'country_code',
	'country_name',
	'address',
	'formatted_address',
	'location_name',
	'featured_location',
);

// append location to the post object.
foreach ( $fields as $field ) {

	if ( isset( $location_data->$field ) ) {
		$post->$field = $location_data->$field;
	}
}

// get location meta if needed and append it to the post.
if ( ! empty( $gmw['info_window']['location_meta'] ) ) {
	$post->location_meta = gmw_get_location_meta( $location->location_id, $gmw['info_window']['location_meta'] );
}

// append distance + units to the post.
$post->distance = $location->distance;
$post->units    = $location->units;

// filter post object.
$post = apply_filters( 'gmw_' . $gmw['prefix'] . '_post_before_info_window', $post, $gmw ); // WPCS: override ok.

//$iw_type  = ! empty( $gmw['info_window']['iw_type'] ) ? $gmw['info_window']['iw_type'] : 'popup';
//$template = $gmw['info_window']['template'][ $iw_type ];

// include template.
//$template_data = gmw_get_info_window_template( 'posts_locator', $iw_type, $template, 'ajax_forms' );

require $gmw['info_window_template']['content_path'];

do_action( 'gmw_' . $gmw['prefix'] . '_after_post_info_window', $gmw, $post );
