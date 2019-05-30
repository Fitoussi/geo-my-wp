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
					'desc'        => __( 'Enter a single numeric value to be used as the default or multiple values comma separated that will be displayed as a dropdown select box in the search form.', 'geo-my-wp' ),
					'attributes'  => array(),
					'priority'    => 40,
				),
				'units'                => array(
					'name'       => 'units',
					'type'       => 'select',
					'default'    => '3959',
					'label'      => __( 'Distance Units', 'geo-my-wp' ),
					'desc'       => __( 'Select miles or kilometers as the default units value or select "Both" to display a units dropdown menu filter in the search form.', 'geo-my-wp' ),
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

			#gmw_bpsgeo_location_options .inside {
				padding: 0;
			}

			.gmw-bpsgeo-fields-wrapper {
				padding: 0 12px 12px;
			}

			.gmw-bpsgeo-location-field-option {
				border-top: 1px solid #efefef;
				padding: 15px 0 20px;
				display: block;
			}

			.gmw-bpsgeo-location-field-option p {
				font-weight: bold;
			}

			.gmw-bpsgeo-location-field-option.gmw-bpsgeo-placeholder-field-option {
				border: 0;
				padding-top: 5px;
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

			.gmw-bpsgeo-additional-features-box {
				padding: 5px 20px 10px;
				box-sizing: border-box;
				color: white;
				margin-bottom: 15px;
				background: #fff9dc;
				color: #555;
				border-top: 8px solid #ffe669;
				margin-bottom: 0;
			}

			.gmw-bpsgeo-additional-features-box p {
				font-size: 14px;
			}
		</style>

		<?php

		$settings = $this->get_settings( $post->id, $bps_options );

		echo '<div class="gmw-bpsgeo-fields-wrapper">';

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

		echo '</div>';
		?>
		<div class="gmw-bpsgeo-additional-features-box">
			<p><?php printf( __( 'Need additional features to display Google Map, the distance, diretions link, and more? Check out the <a href="%s" target="_blank">BuddyPress Members Directory Geolocation extension</a>.', 'geo-my-wp' ), 'https://geomywp.com/extensions/buddypress-members-directory-geolocation/' ); ?></p>
		</div>

		<?php

		$label       = __( 'manage options', 'geo-my-wp' );
		$warning     = __( 'You can only have one location field per form.', 'geo-my-wp' );
		$loc_enabled = '0';
		$loc_element = '';
		$field_id    = '';

		foreach ( $bps_options['field_code'] as $key => $code ) {

			if ( 'gmw_bpsgeo_location' === $code ) {

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

					if ( $( this ).val() == 'gmw_bpsgeo_location' ) {

						if ( $( '#field_box' ).find( 'select.bps_col2 option:selected[value="gmw_bpsgeo_location"]' ).length > 1 ) {

							jQuery( this ).find( 'optgroup:not([label="GEO my WP"] ) option:first' ).prop( 'selected', true );

							alert( '<?php echo $warning; // WPCS: XSS ok. ?>' );							
						}
					}
				});

				if ( '<?php echo $loc_enabled; // WPCS: XSS ok. ?>' != '1' ) {
					return;
				}

				var fieldId   = '<?php echo $field_id; // WPCS: XSS ok. ?>';
				var fieldBox  = jQuery( '#bps_fields_box .inside #field_box <?php echo $loc_element; // WPCS: XSS ok. ?>' ).find( '.bps_col5' );
				var trigger   = jQuery( '<span id="field_mode' + fieldId + '" class="bps_col5 gmw-bpsgeo-options-trigger-wrap"><a class=" gmw-icon-cog gmw-bpsgeo-location-options-trigger"><?php echo $label; // WPCS: XSS ok. ?></a></span>' );

				fieldBox.hide();
				trigger.insertAfter( fieldBox );

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
