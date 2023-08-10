<?php
/**
 * GEO my WP Location field for BP Profile Search plugin.
 *
 * This file display the location field of GEO my WP in BP Profile Search form.
 *
 * You can modify this file to apply custom changes. However, it is recomended
 * to place this file in your child theme's ( or theme's ) folder to prevent changes
 * from being overwritten with the next update of the plugin.
 *
 * You need to place this file in
 * your-theme's-or-child-theme's-folder/buddypress/members/
 *
 * @package geo-my-wp
 */

$bps_options = bps_meta( $F->id );
$name        = esc_attr( $name );
$id          = esc_attr( $id ); // WPCS: globals override ok.
$label       = $f->label;

// Get the geolocation field options.
$geo_options    = $bps_options['template_options'][ $bps_options['template'] ];
$address_ph     = ! empty( $geo_options['gmw_bpsgeo_placeholder'] ) ? esc_attr( $geo_options['gmw_bpsgeo_placeholder'] ) : '';
$address_ac     = ! empty( $geo_options['gmw_bpsgeo_address_autocomplete'] ) ? ' gmw-address-autocomplete' : '';
$radius_options = ! empty( $geo_options['gmw_bpsgeo_radius'] ) ? explode( ',', trim( $geo_options['gmw_bpsgeo_radius'] ) ) : array( '5', '25', '50', '100' );
$default_radius = esc_attr( end( $radius_options ) );

// Get submitted values.
$bpsgeo_values = ! empty( $value ) ? $value : array(
	'address'  => '',
	'distance' => '',
	'units'    => '',
	'lat'      => 'lat',
	'lng'      => 'lng',
);

$address_value = ! empty( $bpsgeo_values['address'] ) ? esc_attr( $bpsgeo_values['address'] ) : '';

if ( function_exists( 'sweetdate_setup' ) ) {

	if ( 'members/bps-form-legacy' === $bps_options['template'] ) {

		include 'bps-form-sweetdate-legacy.php';

	} elseif ( 'members/bps-form-horizontal' === $bps_options['template'] ) {

		include 'bps-form-sweetdate-horizontal.php';
	}
} else {
	include 'bps-form-base.php';
}

do_action( 'gmw_element_loaded', 'bp_profile_search_geolocation', $geo_options );
