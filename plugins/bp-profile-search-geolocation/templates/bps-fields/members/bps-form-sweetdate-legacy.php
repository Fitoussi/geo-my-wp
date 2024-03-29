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
	.kleo-page .form-search.custom .gmw-bpsgeo-location-fields-inner {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(120px, auto));
		grid-column-gap: var(--gmw-elements-gap);
		grid-row-gap: var(--gmw-elements-gap);
		align-items: center;
	}

	.kleo-page .form-search.custom .gmw-bpsgeo-location-fields-inner {
		align-items: flex-start;
	}

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
		padding: 6px 5px;
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

	@media screen and (max-width: 700px) {

		.kleo-page .form-search.custom .gmw-bpsgeo-location-fields-inner {
			grid-auto-flow: row;
			grid-template-columns: none;
			grid-row-gap: 0;
			grid-column-gap: 0;
		}
	}
</style>

<div class="five mobile-four columns">
	<label class="right inline" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_attr( $f->label ); // WPCS: XSS ok. ?></label>
</div>

<div class="seven mobile-four columns kleo-text">

	<div class="gmw-bpsgeo-location-fields-inner">

		<div class="gmw-bpsgeo-address-field-wrap gmw-bpsgeo-location-field-wrap bps-textbox">

			<input type="text" id="<?php echo $id; // WPCS: XSS ok. ?>_address"
				class="gmw-bpsgeo-address-field<?php echo $address_ac; // WPCS: XSS ok. ?> form-control"
				name="<?php echo $name; // WPCS: XSS ok. ?>[address]"
				placeholder="<?php echo $address_ph; // WPCS: XSS ok. ?>"
				value="<?php echo $address_value; // WPCS: XSS ok. ?>">
			<?php if ( ! empty( $geo_options['gmw_bpsgeo_locator_button'] ) ) { ?>
				<i class="gmw-bpsgeo-locator-button gmw-locator-button inside gmw-icon-target-light"></i>
			<?php } ?>
		</div>

		<?php if ( count( $radius_options ) > 1 ) { ?>

			<div class="gmw-bpsgeo-distance-field-wrap gmw-bpsgeo-location-field-wrap bps-selectbox">

				<select id="<?php echo $id; // WPCS: XSS ok. ?>_distance" class="gmw-bpsgeo-distance-field form-control"
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

			<input type="hidden" id="<?php echo $id; // WPCS: XSS ok. ?>_distance"
				name="<?php echo $name; // WPCS: XSS ok. ?>[distance]"
				value="<?php echo $default_radius; // WPCS: XSS ok. ?>" class="gmw-bpsgeo-distance-field">
		<?php } ?>

		<?php if ( 'both' === $geo_options['gmw_bpsgeo_units'] ) { ?>

			<div class="gmw-bpsgeo-units-field-wrap bps-selectbox gmw-bpsgeo-location-field-wrap">

				<select id="<?php echo $id; // WPCS: XSS ok. ?>_units" class="gmw-bpsgeo-units-field form-control"
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
			<input type="hidden" id="<?php echo $id; // WPCS: XSS ok. ?>_units" class="gmw-bpsgeo-units-field"
				name="<?php echo $name; // WPCS: XSS ok. ?>[units]"
				value="<?php echo esc_attr( $geo_options['gmw_bpsgeo_units'] ); ?>">
		<?php } ?>

		<input type="hidden" id="<?php echo $id; // WPCS: XSS ok. ?>_lat" class="gmw-bpsgeo-lat gmw-lat"
			name="<?php echo $name; // WPCS: XSS ok. ?>[lat]"
			value="<?php echo ! empty( $bpsgeo_values['lat'] ) ? esc_attr( $bpsgeo_values['lat'] ) : ''; ?>">
		<input type="hidden" id="<?php echo $id; // WPCS: XSS ok. ?>_lng" class="gmw-bpsgeo-lng gmw-lng"
			name="<?php echo $name; // WPCS: XSS ok. ?>[lng]"
			value="<?php echo ! empty( $bpsgeo_values['lng'] ) ? esc_attr( $bpsgeo_values['lng'] ) : ''; ?>">
	</div>
</div>

<?php
// enqueue location field JS file.
if ( ! wp_script_is( 'gmw-bpsgeo', 'enqueued' ) ) {
	wp_enqueue_script( 'gmw-bpsgeo' );
}

do_action( 'gmw_element_loaded', 'bp_profile_search_geolocation', $geo_options );
