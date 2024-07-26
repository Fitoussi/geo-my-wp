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

?>
<style type="text/css">
	.gmw-bpsgeo-address-field-wrap {
		display: flex;
		justify-content: flex-start;
		align-items: flex-start;
		position: relative;
	}

	.gmw-bpsgeo-address-field-wrap input.gmw-bpsgeo-address-field {
		width: 100%;
		box-sizing: border-box;
		padding-right: 35px;
	}

	.gmw-bpsgeo-address-field-wrap i.gmw-bpsgeo-locator-button {
		position: absolute;
		right: 0;
		padding: 5px 10px;
		cursor: pointer;
	}

	.gmw-bpsgeo-units-field-wrap select,
	.gmw-bpsgeo-distance-field-wrap select {
		width: 100%;
	}

	/** Sweetdate theme  **/
	.kleo-page .gmw-bpsgeo-location-field-wrap.bps-selectbox div.dropdown {
		width: 100% ! important;
	}
</style>

<div class="gmw-bpsgeo-address-field-wrap gmw-bpsgeo-location-field-wrap bps-textbox two columns hz-textbox">

	<input type="text" id="<?php echo esc_attr( $id ); ?>_address"
		class="gmw-bpsgeo-address-field<?php echo esc_attr( $address_ac ); ?> form-control"
		name="<?php echo esc_attr( $name ); ?>[address]" placeholder="<?php echo esc_attr( $address_ph ); ?>"
		value="<?php echo esc_attr( $address_value ); ?>">
	<?php if ( ! empty( $geo_options['gmw_bpsgeo_locator_button'] ) ) { ?>
		<i class="gmw-bpsgeo-locator-button gmw-locator-button inside gmw-icon-target-light"></i>
	<?php } ?>
</div>

<?php if ( count( $radius_options ) > 1 ) { ?>

	<div class="gmw-bpsgeo-distance-field-wrap gmw-bpsgeo-location-field-wrap bps-selectbox two columns hz-textbox">

		<select id="<?php echo esc_attr( $id ); ?>_distance" class="gmw-bpsgeo-distance-field form-control"
			name="<?php echo esc_attr( $name ); ?>[distance]">

			<option value="" selected="selected">
				<?php esc_html_e( 'Within', 'geo-my-wp' ); ?>
			</option>

			<?php
			foreach ( $radius_options as $option ) {

				$selected = ( ! empty( $bpsgeo_values['distance'] ) && $option === $bpsgeo_values['distance'] ) ? 'selected="selected"' : '';

				echo '<option value="' . esc_attr( $option ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $option ) . '</option>';
			}
			?>
		</select>
	</div>

<?php } else { ?>

	<input type="hidden" id="<?php echo esc_attr( $id ); ?>_distance"
		name="<?php echo esc_attr( $name ); ?>[distance]" value="<?php echo esc_attr( $default_radius ); ?>"
		class="gmw-bpsgeo-distance-field">
<?php } ?>

<?php if ( 'both' === $geo_options['gmw_bpsgeo_units'] ) { ?>

	<div class="gmw-bpsgeo-units-field-wrap bps-selectbox gmw-bpsgeo-location-field-wrap two columns hz-textbox">

		<select id="<?php echo esc_attr( $id ); ?>_units" class="gmw-bpsgeo-units-field form-control"
			name="<?php echo esc_attr( $name ); ?>[units]">

			<option value="imperial" selected="selected">
				<?php esc_html_e( 'Mi', 'geo-my-wp' ); ?>
			</option>

			<option value="metric" <?php selected( $bpsgeo_values['units'], 'metric' ); ?>>
				<?php esc_html_e( 'Km', 'geo-my-wp' ); ?>
			</option>

		</select>

	</div>

<?php } else { ?>
	<input type="hidden" id="<?php echo esc_attr( $id ); ?>_units" class="gmw-bpsgeo-units-field"
		name="<?php echo esc_attr( $name ); ?>[units]"
		value="<?php echo esc_attr( $geo_options['gmw_bpsgeo_units'] ); ?>">
<?php } ?>

<input type="hidden" id="<?php echo esc_attr( $id ); ?>_lat" class="gmw-bpsgeo-lat gmw-lat"
	name="<?php echo esc_attr( $name ); ?>[lat]"
	value="<?php echo ! empty( $bpsgeo_values['lat'] ) ? esc_attr( $bpsgeo_values['lat'] ) : ''; ?>">
<input type="hidden" id="<?php echo esc_attr( $id ); ?>_lng" class="gmw-bpsgeo-lng gmw-lng"
	name="<?php echo esc_attr( $name ); ?>[lng]"
	value="<?php echo ! empty( $bpsgeo_values['lng'] ) ? esc_attr( $bpsgeo_values['lng'] ) : ''; ?>">

<?php
// enqueue location field JS file.
if ( ! wp_script_is( 'gmw-bpsgeo', 'enqueued' ) ) {
	wp_enqueue_script( 'gmw-bpsgeo' );
}

do_action( 'gmw_element_loaded', 'bp_profile_search_geolocation', $geo_options );
