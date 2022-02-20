<?php
/**
 * GEO my WP Forms Helper Class.
 *
 * Helper class for form editor.
 *
 * @author Eyal Fitoussi.
 *
 * @package     geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Helper class
 */
class GMW_Forms_Helper {

	/**
	 * [__construct description]
	 */
	public function __construct() {}

	/**
	 * Default form values.
	 *
	 * @param  array $form form settings.
	 *
	 * @return [type]       [description]
	 */
	public static function default_settings( $form = array() ) {

		$css = '/* Below is an example of basic CSS that you could use to modify some of the basic colors and text of the search results template. */
/*div.gmw-results-wrapper[data-id="' . absint( $form['ID'] ) . '"] {
	
	--gmw-form-color-primary: #1C90FF;
	--gmw-form-color-hover-primary: #256fb8;
	--gmw-form-font-color-primary: white;

	--gmw-form-color-secondary: #63CC61;
	--gmw-form-color-hover-secondary: #70d56e;
	--gmw-form-font-color-secondary: white;

	--gmw-form-color-accent: #317ABE;
	--gmw-form-color-hover-accent: #de7a22;
	--gmw-form-font-color-accent: white;

	--gmw-form-title-font-color: #1e90ff;
	--gmw-form-title-font-hover-color: #1c80e0;

	--gmw-form-link-color: #2f8be4;
	--gmw-form-link-color-hover: #236db5;

	--gmw-form-font-color: #333;
	--gmw-form-background-color-primary: #fbfcfe;
	--gmw-form-font-size: 14px;
}*/
';

		$form_data = array(
			'general_settings'  => array(
				'form_name'       => 'form_id_' . $form['ID'],
				'visible_options' => '',
			),
			'page_load_results' => array(
				'enabled'         => 1,
				'user_location'   => '',
				'address_filter'  => '',
				'radius'          => '200',
				'units'           => 'imperial',
				'city_filter'     => '',
				'state_filter'    => '',
				'zipcode_filter'  => '',
				'country_filter'  => '',
				'display_results' => 1,
				'display_map'     => 'results',
				'per_page'        => '5,10,15,25,50',
			),
			'search_form'       => array(
				'form_template'  => 'responsive-1',
				'address_field'  => array(
					'usage'                => 'single',
					'label'                => 'Address',
					'placeholder'          => 'Type an address',
					'address_autocomplete' => 1,
					'locator'              => 1,
					'locator_submit'       => '',
					'required'             => '',
				),
				'locator_button' => array(
					'usage'          => 'disabled',
					'text'           => __( 'Get my current location', 'geo-my-wp' ),
					'url'            => GMW_IMAGES . '/locator-images/locate-me-blue.png',
					'image'          => GMW_IMAGES . '/locator-images/locate-me-blue.png',
					'locator_submit' => '',
				),
				'radius'         => array(
					'usage'            => 'select',
					'label'            => 'Radius',
					'show_options_all' => 'Miles',
					'options'          => "5\n10\n25\n50\n100",
					'default_value'    => '50',
				),
				'units'          => array(
					'options' => 'imperial',
					'label'   => 'Units',
				),
				'submit_button'  => array(
					'label' => __( 'Submit', 'geo-my-wp' ),
				),
				'styles'         => array(
					'enhanced_fields'    => 1,
					'custom_css'         => '',
					'disable_stylesheet' => '',
				),
			),
			'form_submission'   => array(
				'results_page'    => '',
				'display_results' => 1,
				'display_map'     => 'results',
			),
			'search_results'    => array(
				'results_template' => 'responsive-2',
				'results_view'     => array(
					'default' => 'grid',
					'toggle'  => 1,
				),
				'per_page'         => '5,10,15,25,50',
				'address'          => array(
					'enabled' => 1,
					'fields'  => array(),
					'linked'  => 1,
				),
				'image'            => array(
					'enabled'      => 1,
					'width'        => '120px',
					'height'       => 'auto',
					'no_image_url' => GMW_IMAGES . '/no-image.jpg',
				),
				'excerpt'          => array(
					'usage' => 'disabled',
					'count' => '10',
					'link'  => 'read more...',
				),
				'location_meta'    => array(),
				'opening_hour'     => '',
				'directions_link'  => 1,
				'taxonomies'       => 1,
				'styles'           => array(
					'enhanced_fields'    => 1,
					'custom_css'         => $css,
					'disable_stylesheet' => '',
				),
			),
			'results_map'       => array(
				'map_width'  => '100%',
				'map_height' => '300px',
				'map_type'   => 'ROADMAP',
				'zoom_level' => 'auto',
			),
		);

		$form_data = apply_filters( 'gmw_form_default_settings', $form_data, $form );

		if ( ! empty( $form['addon'] ) ) {
			$form_data = apply_filters( 'gmw_' . $form['addon'] . '_addon_form_default_settings', $form_data, $form );
		}

		if ( ! empty( $form['slug'] ) ) {
			$form_data = apply_filters( 'gmw_' . $form['slug'] . '_form_default_settings', $form_data, $form );
		}

		return $form_data;
	}

	/**
	 * Get all forms from database
	 *
	 * @return array of forms
	 */
	public static function get_forms() {

		$forms = wp_cache_get( 'all_forms', 'gmw_forms' );

		if ( false === $forms ) {

			global $wpdb;

			$forms  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}gmw_forms", ARRAY_A ); // WPCS: db call ok, cache ok.
			$output = array();

			foreach ( $forms as $form ) {

				// if happened that form has no data we need to apply the defaults.
				if ( empty( $form['data'] ) ) {
					$form['data'] = maybe_serialize( self::default_settings( $form ) );
				}

				$data = maybe_unserialize( $form['data'] );

				// most likely bad data.
				if ( ! is_array( $data ) ) {
					$data = array();
				}

				$output[ $form['ID'] ] = array_merge( $form, $data );

				unset( $output[ $form['ID'] ]['data'] );
			}

			$forms = $output;

			wp_cache_set( 'all_forms', $forms, 'gmw_forms' );
		}

		return ! empty( $forms ) ? $forms : array();
	}

	/**
	 * Get specific form by form ID.
	 *
	 * @param integer $form_id form ID.
	 *
	 * @return array specific form if form ID pass otherwise all forms
	 */
	public static function get_form( $form_id = 0 ) {

		absint( $form_id );

		// abort if no ID passes.
		if ( empty( $form_id ) ) {

			return false;
		}

		$form = wp_cache_get( $form_id, 'gmw_forms' );

		if ( false === $form ) {

			global $wpdb;

			$form = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}gmw_forms WHERE ID = %d",
					$form_id
				),
				ARRAY_A
			); // WPCS: db call ok, cache ok.

			if ( ! empty( $form ) ) {

				// if happens that form has no data, we need to apply the defaults.
				if ( empty( $form['data'] ) ) {
					$form['data'] = self::default_settings( $form );
				}

				$form = array_merge( $form, maybe_unserialize( $form['data'] ) );

				unset( $form['data'] );

				wp_cache_set( $form_id, $form, 'gmw_forms' );

			} else {

				$form = false;

				wp_cache_delete( $form_id, 'gmw_forms' );
			}
		}

		return ! empty( $form ) ? $form : false;
	}

	/**
	 * Delte form
	 *
	 * @param  integer $form_id [description].
	 *
	 * @return [type]           [description]
	 */
	public static function delete_form( $form_id = 0 ) {

		if ( ! absint( $form_id ) ) {
			return false;
		}

		global $wpdb;

		// delete form from database.
		$delete = $wpdb->delete(
			$wpdb->prefix . 'gmw_forms',
			array(
				'ID' => $form_id,
			),
			array(
				'%d',
			)
		); // WPCS: db call ok, cache ok.

		// update forms in cache.
		self::update_forms_cache();

		return $delete;
	}

	/**
	 * Update forms cache
	 */
	public static function update_forms_cache() {

		global $wpdb;

		$forms = $wpdb->get_results( "SELECT ID, data FROM {$wpdb->prefix}gmw_forms", ARRAY_A ); // WPCS: db call ok, cache ok.

		$output = array();

		foreach ( $forms as $form ) {
			$output[ $form['ID'] ] = maybe_unserialize( $form['data'] );
		}

		$forms = $output;

		wp_cache_set( 'all_forms', $forms, 'gmw_forms' );
	}
}
