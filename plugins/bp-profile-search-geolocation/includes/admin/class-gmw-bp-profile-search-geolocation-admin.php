<?php
/**
 * GEO my WP BP Profile Search geolocation admin settings.
 *
 * @author Eyal Fitoussi
 *
 * @created 3/2/2019
 *
 * @since 3.3
 *
 * @package gmw-bp-profile-search-integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_BP_Profile_Search_Geolocation_Admin class
 *
 * @since 3.3
 */
class GMW_BP_Profile_Search_Geolocation_Admin {

	/**
	 * Get the admin setting fields
	 *
	 * @access public
	 *
	 * @param integer $form_id the  BPS form ID.
	 *
	 * @param array   $form_options BPS form options.
	 *
	 * @return array of fields.
	 */
	public function get_settings( $form_id, $form_options ) {

		$settings = apply_filters(
			'gmw_bpsgeo_form_settings',
			array(
				'palceholder'          => array(
					'name'        => 'placeholder',
					'type'        => 'text',
					'default'     => '',
					'label'       => __( 'Address Field Placeholder', 'geo-my-wp' ),
					'placeholder' => __( 'Enter address...', 'geo-my-wp' ),
					'desc'        => __( 'Enter a placeholder for the address field.', 'geo-my-wp' ),
					'attributes'  => array(),
					'priority'    => 10,
				),
				'address_autocomplete' => array(
					'name'       => 'address_autocomplete',
					'type'       => 'checkbox',
					'cb_label'   => 'Enabled',
					'default'    => '',
					'label'      => __( 'Address Autocomplete', 'geo-my-wp' ),
					'desc'       => __( 'Enable Google address autocomplete feature.', 'geo-my-wp' ),
					'attributes' => array(),
					'priority'   => 20,
				),
				'locator_button'       => array(
					'name'       => 'locator_button',
					'default'    => '',
					'label'      => __( 'Locator button', 'geo-my-wp' ),
					'cb_label'   => __( 'Enable', 'geo-my-wp' ),
					'desc'       => __( 'Display locator button inside the address field.', 'geo-my-wp' ),
					'type'       => 'checkbox',
					'attributes' => array(),
					'priority'   => 30,
				),
				'radius'               => array(
					'name'        => 'radius',
					'type'        => 'text',
					'default'     => '10,25,50,100,200',
					'label'       => __( 'Radius', 'geo-my-wp' ),
					'placeholder' => __( 'Enter radius values', 'geo-my-wp' ),
					'desc'        => __( 'Enter a single numeric value to be used as the default, or multiple values, comma separated, that will be displayed as a dropdown select box in the search form.', 'geo-my-wp' ),
					'attributes'  => array(),
					'priority'    => 40,
				),
				'units'                => array(
					'name'       => 'units',
					'type'       => 'select',
					'default'    => '3959',
					'label'      => __( 'Distance Units', 'geo-my-wp' ),
					'desc'       => __( 'Select miles, kilometers, or both to display a dropdown menu in the search form.', 'geo-my-wp' ),
					'options'    => array(
						'both'     => __( 'Both', 'geo-my-wp' ),
						'imperial' => __( 'Miles', 'geo-my-wp' ),
						'metric'   => __( 'Kilometers', 'geo-my-wp' ),
					),
					'attributes' => array(),
					'priority'   => 50,
				),
			),
			$form_id,
			$form_options
		);

		uasort( $settings, 'gmw_sort_by_priority' );

		return $settings;
	}

	/**
	 * [__construct description]
	 */
	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'add_options_meta_box' ) );
	}

	/**
	 * Add options meta box.
	 *
	 * @since 3.3
	 */
	public function add_options_meta_box() {
		add_meta_box(
			'gmw_bpsgeo_location_options',
			__( 'Location Field Options', 'geo-my-wp' ),
			array( $this, 'options_meta_box_content' ),
			'bps_form',
			'advanced',
			'high'
		);
	}

	/**
	 * Location field options meta box content.
	 *
	 * @param  object $post post object.
	 *
	 * @since 3.3
	 */
	public function options_meta_box_content( $post ) {

		$bps_options = bps_meta( $post->ID );
		$options     = $bps_options['template_options'][ $bps_options['template'] ];
		?>
		<style type="text/css">

			.gmw-bpsgeo-location-field-option {
				border-top: 1px solid #efefef;
				padding: 15px 0 20px;
				display: block;
			}

			.gmw-bpsgeo-placeholder-field-option {
				border: 0;
				padding-top: 5px;
			}

			.gmw-bpsgeo-location-field-option p {
				font-weight: bold;
			}

			#gmw_bpsgeo_location_options .desc {
				margin-top: 5px;
				display: block;
			}

			.gmw-bpsgeo-options-trigger-wrap {
				background: #efefef;
				padding: 4px 3px;
				box-sizing: border-box;
				border: 1px solid #ddd;
				border-radius: 5px;
				font-size: 12px;
				cursor: pointer;
				box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
				margin-left: 1px;
			}

			.gmw-bpsgeo-options-trigger-wrap a {
				color: #333;
			}
		</style>

		<?php

		$settings = $this->get_settings( $post->id, $bps_options );

		foreach ( $settings as $field ) {

			echo '<div class="gmw-bpsgeo-location-field-option gmw-bpsgeo-' . esc_attr( $field['name'] ) . '-field-option">';
			echo '<p>' . esc_html( $field['label'] ) . '</p>';

			$field['name'] = 'gmw_bpsgeo_' . $field['name'];
			$value         = ! empty( $options[ $field['name'] ] ) ? $options[ $field['name'] ] : '';

			echo GMW_Form_Settings_Helper::get_form_field( $field, 'options', $value ); // WPCS: XSS ok.

			if ( ! empty( $field['desc'] ) ) {
				echo '<em class="desc">' . esc_html( $field['desc'] ) . '</em>';
			}

			echo '</div>';
		}

		$label       = __( 'manage options', 'geo-my-wp' );
		$warning     = __( 'You can only have one location field per form.', 'geo-my-wp' );
		$loc_enabled = '0';
		$loc_element = '';
		$field_id    = '';

		foreach ( $bps_options['field_code'] as $key => $code ) {

			if ( 'gmw_location_ph' === $code ) {

				$loc_enabled = '1';
				$field_id    = esc_attr( $key );
				$loc_element = '#field_div' . $field_id;
			}
		}
		?>
		<script type="text/javascript">

			jQuery( document ).ready( function($) {

				// Prevent from creating multiple location fields.
				jQuery( '#field_box' ).on( 'change', 'select.bps_col2', function( event ) {

					if ( $( this ).val() == 'gmw_location_ph' ) {

						if ( $( '#field_box' ).find( 'select.bps_col2 option:selected[value="gmw_location_ph"]' ).length > 1 ) {

							jQuery( this ).find( 'optgroup:not([label="GEO my WP Location"] ) option:first' ).prop( 'selected', true );

							alert( '<?php echo $warning; // WPCS: XSS ok. ?>' );							
						}
					}
				});

				if ( '<?php echo $loc_enabled; // WPCS: XSS ok. ?>' != '1' ) {
					return;
				}

				var fieldId   = '<?php echo $field_id; // WPCS: XSS ok. ?>';
				var fieldBox  = jQuery( '#bps_fields_box .inside #field_box <?php echo $loc_element; // WPCS: XSS ok. ?>' );
				var trigger   = jQuery( '<span id="field_mode' + fieldId + '" class="gmw-bpsgeo-options-trigger-wrap bps_col5"><a class=" gmw-icon-cog gmw-bpsgeo-location-options-trigger"><?php echo $label; // WPCS: XSS ok. ?></a></span><input type="hidden" name="bps_options[field_mode][<?php echo $field_id; // WPCS: XSS ok. ?>]" value="distance"/>' );
				var nameField = jQuery( '<input type="hidden" id="field_name' + fieldId + '" class="bps_col2" name="bps_options[field_name][' + fieldId + ']" value="gmw_location_ph"/>' );

				// replace original mode box.
				fieldBox.find( '.bps_col5' ).replaceWith( trigger );

				// Scroll to location options box and show it if it is hidden.
				jQuery( '#field_box' ).on( 'click', '.gmw-bpsgeo-location-options-trigger', function( event ) {

					optionsBox = $( '.postbox#gmw_bpsgeo_location_options' );
					optionsBox.show();

					if ( ! optionsBox.find( '.inside' ).is( ':visible' ) ) {
						optionsBox.find( 'h2.hndle' ).click();
					}

					optionsBox.get(0).scrollIntoView();
				});
			});
		</script>
		<?php
	}
}
new GMW_BP_Profile_Search_Geolocation_Admin();
