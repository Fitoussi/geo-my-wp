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
	 * Is BuddyBoss plugin installed.
	 *
	 * @var boolean
	 */
	public $is_buddyboss = false;

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
				'palceholder'          => gmw_get_admin_setting_args(
					array(
						'name'       => 'placeholder',
						'type'       => 'text',
						'default'    => '',
						'label'      => __( 'Address Field Placeholder', 'geo-my-wp' ),
						'desc'       => __( 'Enter the placeholder for the address field.', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 10,
					)
				),
				'address_autocomplete' => gmw_get_admin_setting_args(
					array(
						'name'       => 'address_autocomplete',
						'type'       => 'checkbox',
						'cb_label'   => 'Enabled',
						'default'    => '',
						'label'      => __( 'Address Autocomplete', 'geo-my-wp' ),
						'desc'       => __( 'Enable the address autocomplete feature of Google Maps.', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 20,
					)
				),
				'locator_button'       => gmw_get_admin_setting_args(
					array(
						'name'       => 'locator_button',
						'default'    => '',
						'label'      => __( 'Locator button', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'desc'       => __( 'Display locator button inside the address field.', 'geo-my-wp' ),
						'type'       => 'checkbox',
						'attributes' => array(),
						'priority'   => 30,
					)
				),
				'radius'               => gmw_get_admin_setting_args(
					array(
						'name'        => 'radius',
						'type'        => 'text',
						'default'     => '10,25,50,100,200',
						'label'       => __( 'Radius', 'geo-my-wp' ),
						'placeholder' => __( 'Enter radius values', 'geo-my-wp' ),
						'desc'        => __( 'Enter a single numeric value as the default value or multiple values ( comma separated ) that will be displayed as a dropdown select box in the search form.', 'geo-my-wp' ),
						'attributes'  => array(),
						'priority'    => 40,
					)
				),
				'units'                => gmw_get_admin_setting_args(
					array(
						'name'       => 'units',
						'type'       => 'select',
						'default'    => '3959',
						'label'      => __( 'Distance Units', 'geo-my-wp' ),
						'desc'       => __( 'Select miles or kilometers as the default units value or select "Select box filter" to display a units dropdown menu filter in the search form.', 'geo-my-wp' ),
						'options'    => array(
							'both'     => __( 'Select box filter', 'geo-my-wp' ),
							'imperial' => __( 'Miles', 'geo-my-wp' ),
							'metric'   => __( 'Kilometers', 'geo-my-wp' ),
						),
						'attributes' => array(),
						'priority'   => 50,
					)
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

		$this->is_buddyboss = function_exists( 'bp_ps_meta' ) ? true : false;

		if ( $this->is_buddyboss ) {
			add_action( 'save_post', array( $this, 'bp_ps_update_meta' ), 12, 2 );
		}

		add_action( 'add_meta_boxes', array( $this, 'add_options_meta_box' ) );
	}

	/**
	 * Update BuddyBoss Profile Search post meta.
	 *
	 * @param int    $form form ID.
	 *
	 * @param object $post $post.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function bp_ps_update_meta( $form, $post ) {

		if ( 'bp_ps_form' !== $post->post_type || 'publish' !== $post->post_status ) {
			return false;
		}
		if ( empty( $_POST['options'] ) && empty( $_POST['bp_ps_options'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, CSRF ok.
			return false;
		}

		$posted = isset( $_POST['bp_ps_options'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bp_ps_options'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, sanitization ok, CSRF ok.

		if ( isset( $posted['field_name'] ) ) {

			$meta = get_post_meta( $form, 'bp_ps_options', 1 );

			if ( empty( $meta ) ) {
				$meta = array();
			}

			foreach ( $posted as $key => $value ) {

				if ( strpos( $key, 'gmw' ) === false) {
					continue;
				}

				$meta[ $key ] = $value;
			}
		}

		update_post_meta( $form, 'bp_ps_options', $meta );

		return true;
	}

	/**
	 * Add options meta box.
	 *
	 * @since 3.3
	 */
	public function add_options_meta_box() {

		$screen = $this->is_buddyboss ? 'bp_ps_form' : 'bps_form';

		add_meta_box(
			'gmw_bpsgeo_location_options',
			__( 'Location Field Options', 'geo-my-wp' ),
			array( $this, 'options_meta_box_content' ),
			$screen,
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

		if ( $this->is_buddyboss ) {

			$bps_options = bp_ps_meta( $post->ID );
			$options     = $bps_options;
			$name_attr   = 'bp_ps_options';

		} else {

			$bps_options = bps_meta( $post->ID );
			$options     = $bps_options['template_options'][ $bps_options['template'] ];
			$name_attr   = 'options';
		}

		?>
		<style type="text/css">

			#gmw_bpsgeo_location_options {
				display:  none;
			}

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
				margin: 8px 0;
			}

			.gmw-bpsgeo-location-field-option.gmw-bpsgeo-placeholder-field-option {
				border: 0;
				padding-top: 5px;
			}

			#gmw_bpsgeo_location_options .desc {
				margin-top: 10px;
				display: block;
			}

			.gmw-bpsgeo-additional-features-box p {
				font-size: 14px;
				padding: 12px 15px 12px;
				box-sizing: border-box;
				color: white;
				background: #4699E8;
				margin: 0;
			}

			.gmw-bpsgeo-additional-features-box p a {
				color: white;
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

			echo gmw_get_admin_settings_field( $field, $name_attr, $value ); // phpcs:ignore. XSS ok.

			if ( ! empty( $field['desc'] ) ) {
				echo '<em class="desc">' . esc_html( $field['desc'] ) . '</em>';
			}

			echo '</div>';
		}
		echo '</div>';

		$allowed = array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
			),
		);

		echo '<div class="gmw-bpsgeo-additional-features-box"><p>';

		if ( gmw_is_addon_active( 'bp_members_directory_geolocation' ) ) {

			echo wp_kses(
				/* translators: %s link to Members Locator Settings page. */
				sprintf( __( 'Additional features available via the <a href="%s">Settings page of the BuddyPress Members Directory Geolocation extension</a>', 'geo-my-wp' ), admin_url( 'admin.php?page=gmw-settings&tab=members_locator&sub_tab=bp_members_directory_geolocation' ) ),
				$allowed
			);

		} else {

			echo wp_kses(
				/* translators: %s link to the BP Directory Geolocation extension page. */
				sprintf( __( 'Check out the <a href="%s" target="_blank">BuddyPress Members Directory Geolocation extension</a> for additional geolocation features. Display a map above the list of members, display the distance, address, and a directions link in each member in the results.', 'geo-my-wp' ), 'https://geomywp.com/extensions/buddypress-members-directory-geolocation/' ),
				$allowed
			);
		}
		?>
		</p></div>

		<?php $warning = __( 'You can only have one location field per form.', 'geo-my-wp' ); ?>

		<script type="text/javascript">

			jQuery( document ).ready( function($) {

				// Show location field options only when needed.
				function gmw_check_for_location_field() {

					jQuery( '#field_box select' ).each( function() {

						jQuery( '#gmw_bpsgeo_location_options' ).hide();

						if ( jQuery( this ).val() == 'gmw_bpsgeo_location' ) {
							jQuery( '#gmw_bpsgeo_location_options' ).show();

							return false;
						}
					});
				}

				jQuery( document ).on( 'change', '#field_box select', function() {
					gmw_check_for_location_field();
				});

				jQuery( document ).on( 'click', '.remove_field.delete', function() {
					gmw_check_for_location_field();
				});

				gmw_check_for_location_field();

				// Prevent from creating multiple location fields.
				jQuery( '#field_box' ).on( 'change', 'select.bps_col2', function( event ) {

					if ( $( this ).val() == 'gmw_bpsgeo_location' ) {

						if ( $( '#field_box' ).find( 'select.bps_col2 option:selected[value="gmw_bpsgeo_location"]' ).length > 1 ) {

							jQuery( this ).find( 'optgroup:not([label="GEO my WP"] ) option:first' ).prop( 'selected', true );

							alert( '<?php echo esc_html( $warning ); // WPCS: XSS ok. ?>' );
						}
					}
				});
			});
		</script>
		<?php
	}
}
new GMW_BP_Profile_Search_Geolocation_Admin();
