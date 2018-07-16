<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Helper class
 *
 */
class GMW_Forms_Helper {

	/**
	 * [__construct description]
	 */
	public function __construct() {}

	/**
	 * Default form values
	 *
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public static function default_settings( $form = array() ) {

		$form_data = array(

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
				'per_page'        => '5,10,15,25',
			),
			'search_form'       => array(
				'form_template'  => 'default',
				'address_field'  => array(
					'label'                => 'Address',
					'placeholder'          => 'Enter Address',
					'address_autocomplete' => 1,
					'locator'              => 1,
					'locator_submit'       => 1,
					'mandatory'            => '',
				),
				'locator'        => 'disabled',
				'locator_text'   => '',
				'locator_image'  => 'blue-dot.png',
				'locator_submit' => '',
				'radius'         => '5,10,25,50,100,150,200',
				'units'          => 'imperial',

			),
			'form_submission'   => array(
				'results_page'    => '',
				'display_results' => 1,
				'display_map'     => 'results',
			),
			'search_results'    => array(
				'results_template' => 'gray',
				'per_page'         => '5,10,15,25',
				'image'            => array(
					'enabled' => 1,
					'width'   => '100',
					'height'  => '100',
				),
				'location_meta'    => array(),
				'opening_hour'     => '',
				'directions_link'  => 1,
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

			$forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}gmw_forms", ARRAY_A );

			$output = array();

			foreach ( $forms as $form ) {

				// if happened that form has no data we need to apply the defaults
				if ( empty( $form['data'] ) ) {
					$form['data'] = serialize( GMW_Forms_Helper::default_settings( array() ) );
				}

				$data = maybe_unserialize( $form['data'] );

				// most likely bad data
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
	 * Get specific form by form ID
	 *
	 * @param  boolean $formID - form ID
	 *
	 * @return array specific form if form ID pass otherwise all forms
	 */
	public static function get_form( $form_id = false ) {

		absint( $form_id );

		// abort if no ID passes
		if ( empty( $form_id ) ) {

			return false;
		}

		$form = wp_cache_get( $form_id, 'gmw_forms' );

		if ( false === $form ) {

			global $wpdb;

			$form = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}gmw_forms WHERE ID = %d", $form_id
				), ARRAY_A
			);

			if ( ! empty( $form ) ) {

				// if happens that form has no data, we need to apply the defaults
				if ( empty( $form['data'] ) ) {
					$form['data'] = GMW_Forms_Helper::default_settings( array() );
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
	 * @param  integer $form_id [description]
	 * @return [type]           [description]
	 */
	public static function delete_form( $form_id = 0 ) {

		if ( ! absint( $form_id ) ) {
			return false;
		}

		global $wpdb;

		//delete form from database
		$delete = $wpdb->delete(
			$wpdb->prefix . 'gmw_forms',
			array(
				'ID' => $form_id,
			),
			array(
				'%d',
			)
		);

		// update forms in cache
		GMW_Forms_Helper::update_forms_cache();

		return $delete;
	}

	/**
	 * Update forms cache
	 *
	 * @return [type] [description]
	 */
	public static function update_forms_cache() {

		global $wpdb;

		$forms = $wpdb->get_results( "SELECT ID, data FROM {$wpdb->prefix}gmw_forms", ARRAY_A );

		$output = array();

		foreach ( $forms as $form ) {
			$output[ $form['ID'] ] = unserialize( $form['data'] );
		}

		$forms = $output;

		wp_cache_set( 'all_forms', $forms, 'gmw_forms' );
	}
}
