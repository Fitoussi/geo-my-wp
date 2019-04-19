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
?>
<div class="gmw-bpsgeo-location-fields-inner gmw-flexed-wrapper">

	<div class="gmw-bpsgeo-address-field-wrap gmw-bpsgeo-location-field-wrap bps-textbox">

		<input 
			type="text"
			id="<?php echo $id; // WPCS: XSS ok. ?>_address"
			class="gmw-bpsgeo-address-field<?php echo $address_ac; // WPCS: XSS ok. ?> form-control"
			name="<?php echo $name; // WPCS: XSS ok. ?>[address]" 
			placeholder="<?php echo $address_ph; // WPCS: XSS ok. ?>"
			value="<?php echo $address_value; // WPCS: XSS ok. ?>"
		>
		<?php if ( ! empty( $geo_options['gmw_bpsgeo_locator_button'] ) ) { ?>
			<i class="gmw-bpsgeo-locator-button gmw-locator-button inside gmw-icon-target-light"></i>
		<?php } ?>			
	</div>

	<?php if ( count( $radius_options ) > 1 ) { ?>

		<div class="gmw-bpsgeo-distance-field-wrap gmw-bpsgeo-location-field-wrap bps-selectbox">

			<select 
				id="<?php echo $id; // WPCS: XSS ok. ?>_distance"
				class="gmw-bpsgeo-distance-field form-control" 
				name="<?php echo $name; // WPCS: XSS ok. ?>[distance]">

				<option value="" selected="selected">
					<?php esc_html_e( 'Within', 'geo-my-wp' ); ?>	
				</option>

				<?php
				foreach ( $radius_options as $option ) {

					$option   = esc_attr( $option );
					$selected = ( ! empty( $bpsgeo_values['distance'] ) && $option === $bpsgeo_values['distance'] ) ? 'selected="selected"' : '';

					echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>'; // WPCS: XSS ok.
				}
				?>
			</select>
		</div>

	<?php } else { ?>

		<input 
			type="hidden"
			id="<?php echo $id; // WPCS: XSS ok. ?>_distance"
			name="<?php echo $name; // WPCS: XSS ok. ?>[distance]" 
			value="<?php echo $default_radius; // WPCS: XSS ok. ?>"
			class="gmw-bpsgeo-distance-field"
		>
	<?php } ?>

	<?php if ( 'both' === $geo_options['gmw_bpsgeo_units'] ) { ?>

		<div class="gmw-bpsgeo-units-field-wrap bps-selectbox gmw-bpsgeo-location-field-wrap">

			<select 
				id="<?php echo $id; // WPCS: XSS ok. ?>_units"
				class="gmw-bpsgeo-units-field form-control" 
				name="<?php echo $name; // WPCS: XSS ok. ?>[units]">

				<option value="imperial" selected="selected">
					<?php esc_html_e( 'Mi', 'geo-my-wp' ); ?>		
				</option>

				<option value="metric" <?php selected( $bpsgeo_values['units'], 'metric' ); ?>>
					<?php esc_html_e( 'Km', 'geo-my-wp' ); ?>
				</option>

			</select>

		</div>

	<?php } else { ?>
		<input 
			type="hidden"
			id="<?php echo $id; // WPCS: XSS ok. ?>_units"
			class="gmw-bpsgeo-units-field"
			name="<?php echo $name; // WPCS: XSS ok. ?>[units]"
			value="<?php echo esc_attr( $geo_options['gmw_bpsgeo_units'] ); ?>"
		>
	<?php } ?>

	<input 
		type="hidden"
		id="<?php echo $id; // WPCS: XSS ok. ?>_lat"
		class="gmw-bpsgeo-lat gmw-lat"
		name="<?php echo $name; // WPCS: XSS ok. ?>[lat]"
		value="<?php echo ! empty( $bpsgeo_values['lat'] ) ? esc_attr( $bpsgeo_values['lat'] ) : ''; ?>"
	>
	<input 
		type="hidden"
		id="<?php echo $id; // WPCS: XSS ok. ?>_lng"
		class="gmw-bpsgeo-lng gmw-lng"
		name="<?php echo $name; // WPCS: XSS ok. ?>[lng]"
		value="<?php echo ! empty( $bpsgeo_values['lng'] ) ? esc_attr( $bpsgeo_values['lng'] ) : ''; ?>"
	>

</div>
<?php
// enqueue location field JS file.
if ( ! wp_script_is( 'gmw-bpsgeo', 'enqueued' ) ) {
	wp_enqueue_script( 'gmw-bpsgeo' );
}
