<?php
/**
 * GEO my WP BP Directory Geolocation admin settings.
 *
 * @author Eyal Fitoussi
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_BuddyPress_Directory_Geolocation_Admin_Settings class.
 *
 * @since 4.0.
 */
class GMW_BuddyPress_Directory_Geolocation_Admin_Settings {

	/**
	 * Default settings.
	 *
	 * @return [type] [description]
	 */
	public static function get_defaults() {

		return array(
			'enabled'              => 1,
			'address_autocomplete' => 1,
			'locator_button'       => 1,
			'radius'               => '10,25,50,100,200',
			'units'                => 'imperial',
			'map'                  => 1,
			'map_width'            => '100%',
			'map_height'           => '300px',
			'map_type'             => 'ROADMAP',
			'distance'             => 1,
			'address_fields'       => 'address',
			'directions_link'      => 1,
		);
	}

	/**
	 * Admin settings.
	 *
	 * @param string $component the component being displayed.
	 *
	 * @return [type]           [description]
	 */
	public static function get_settings( $component = 'group' ) {

		$settings = array(
			'enabled'              => gmw_get_admin_setting_args(
				array(
					'name'     => 'enabled',
					'type'     => 'checkbox',
					'cb_label' => 'Enabled',
					'default'  => '',
					'label'    => __( 'Enable Geolocation', 'geo-my-wp' ),
					/* translators: %s component */
					'desc'     => sprintf( __( 'Enable the geolocation features in the Buddypress %s directory page.', 'geo-my-wp' ), $component . 's' ),
					'class'    => 'gmw-options-toggle',
					'priority' => 10,
				)
			),
			'search_form_settings' => array(
				'name'     => 'search_form_settings',
				'type'     => 'fields_group',
				'label'    => __( 'Search Form Settings', 'geo-my-wp' ),
				'fields'   => array(
					'address_autocomplete' => gmw_get_admin_setting_args(
						array(
							'name'     => 'address_autocomplete',
							'type'     => 'checkbox',
							'cb_label' => 'Enabled',
							'default'  => '',
							'label'    => __( 'Address Autocomplete', 'geo-my-wp' ),
							'desc'     => __( 'Enable the address autocomplete feature.', 'geo-my-wp' ),
							'priority' => 5,
						)
					),
					'locator_button'       => gmw_get_admin_setting_args(
						array(
							'name'     => 'locator_button',
							'default'  => '',
							'label'    => __( 'Locator button', 'geo-my-wp' ),
							'cb_label' => __( 'Enable', 'geo-my-wp' ),
							'desc'     => __( 'Display locator button inside the address field.', 'geo-my-wp' ),
							'type'     => 'checkbox',
							'priority' => 10,
						)
					),
					'radius'               => gmw_get_admin_setting_args(
						array(
							'name'        => 'radius',
							'type'        => 'text',
							'default'     => '10,25,50,100,200',
							'label'       => __( 'Radius', 'geo-my-wp' ),
							'placeholder' => __( 'Enter radius values', 'geo-my-wp' ),
							'desc'        => __( 'Enter a single numeric value as the default radius value or multiple values ( comma separated ) that will be displayed as a dropdown select box in the search form.', 'geo-my-wp' ),
							'priority'    => 15,
						)
					),
					'units'                => gmw_get_admin_setting_args(
						array(
							'name'       => 'units',
							'type'       => 'select',
							'default'    => 'imperial',
							'label'      => __( 'Distance Units', 'geo-my-wp' ),
							'desc'       => __( 'Select miles or kilometers.', 'geo-my-wp' ),
							'options'    => array(
								'imperial' => __( 'Miles', 'geo-my-wp' ),
								'metric'   => __( 'Kilometers', 'geo-my-wp' ),
							),
							'class'      => 'gmw-smartbox-not',
							'attributes' => array(),
							'priority'   => 20,
						)
					),
				),
				'priority' => 10,
			),

			'map_settings'         => array(
				'name'     => 'map_settings',
				'type'     => 'fields_group',
				'label'    => __( 'Map Settings', 'geo-my-wp' ),
				'fields'   => array(
					'map'        => gmw_get_admin_setting_args(
						array(
							'name'     => 'map',
							'type'     => 'checkbox',
							'default'  => '',
							'cb_label' => 'Enabled',
							'label'    => __( 'Map', 'geo-my-wp' ),
							'desc'     => __( 'Display map above list of results.', 'geo-my-wp' ),
							'class'    => 'gmw-options-toggle',
							'priority' => 5,
						)
					),
					'map_width'  => gmw_get_admin_setting_args(
						array(
							'name'        => 'map_width',
							'option_type' => 'map_width',
							'priority'    => 10,
						)
					),
					'map_height' => gmw_get_admin_setting_args(
						array(
							'name'        => 'map_height',
							'option_type' => 'map_height',
							'priority'    => 15,
						)
					),
				),
				'priority' => 15,
			),
			'results_settings'     => array(
				'name'     => 'map_settings',
				'type'     => 'fields_group',
				'label'    => __( 'Results List Settings', 'geo-my-wp' ),
				'fields'   => array(
					'distance'        => gmw_get_admin_setting_args(
						array(
							'name'     => 'distance',
							'type'     => 'checkbox',
							'default'  => '',
							'label'    => __( 'Distance', 'geo-my-wp' ),
							'cb_label' => __( 'Enabled', 'geo-my-wp' ),
							/* translators: %s component */
							'desc'     => sprintf( __( 'Display the distance to each %s in the list of results.', 'geo-my-wp' ), $component ),
							'priority' => 5,
						)
					),
					'address_fields'  => gmw_get_admin_setting_args(
						array(
							'name'        => 'address_fields',
							'option_type' => 'address_fields_output',
							'default'     => array(),
							'label'       => __( 'Address fields', 'geo-my-wp' ),
							/* translators: %s component */
							'desc'        => sprintf( __( 'Select the address fields that you wish to display for each %s in the list or leave black to hide the address.', 'geo-my-wp' ), $component ),
							'priority'    => 10,
						)
					),
					'directions_link' => gmw_get_admin_setting_args(
						array(
							'name'     => 'directions_link',
							'type'     => 'checkbox',
							'default'  => '',
							'label'    => __( 'Directions Link', 'geo-my-wp' ),
							'cb_label' => __( 'Enabled', 'geo-my-wp' ),
							/* translators: %s component */
							'desc'     => sprintf( __( 'Display directions link for each %s in the list of results.', 'geo-my-wp' ), $component ),
							'priority' => 15,
						)
					),
				),
				'priority' => 20,
			),
		);

		if ( 'google_maps' === GMW()->maps_provider ) {

			$settings['map_settings']['fields']['map_type'] = gmw_get_admin_setting_args(
				array(
					'option_type' => 'map_type',
					'name'        => 'map_type',
					'priority'    => 20,
				)
			);
		}

		return $settings;
	}
}
