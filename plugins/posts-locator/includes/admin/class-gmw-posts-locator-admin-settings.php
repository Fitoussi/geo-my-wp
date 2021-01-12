<?php
/**
 * GMW Posts Locator Admin Settings.
 *
 * @author Eyal Fitoussi
 *
 * @since 1.0
 *
 * @package gmw-multiple-locations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PT_Admin class
 *
 * Post type locator admin functions
 */
class GMW_Posts_Locator_Admin_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// setup default values for settings.
		add_filter( 'gmw_admin_settings_setup_defaults', array( $this, 'setup_defaults' ) );
		add_filter( 'gmw_admin_settings', array( $this, 'admin_settings' ), 5 );
	}

	/**
	 * Defaukt options will setup when no options exist. Usually when plugin first installed.
	 *
	 * @param  [type] $defaults [description].
	 *
	 * @return [type]           [description]
	 */
	public function setup_defaults( $defaults ) {

		$defaults['post_types_settings'] = array(

			'edit_post_exclude_tabs'        => array(
				'dynamic'    => 1,
				'address'    => 1,
				'coords'     => 1,
				'contact'    => 1,
				'days-hours' => 1,
			),
			'edit_post_page_map_latitude'   => '40.711544',
			'edit_post_page_map_longitude'  => '-74.013486',
			'edit_post_page_map_type'       => 'ROADMAP',
			'edit_post_page_map_zoom_level' => 7,
			'post_types'                    => array( 'post' ),
		);

		return $defaults;
	}

	/**
	 * Post types settings in GEO my WP main settings page.
	 *
	 * @param array $settings settings array.
	 *
	 * @access public
	 * @return $settings
	 */
	public function admin_settings( $settings ) {

		$settings['post_types_settings']['post_types'] = array(
			'name'       => 'post_types',
			'type'       => 'multiselect',
			'default'    => '',
			'label'      => __( 'Post Types', 'geo-my-wp' ),
			'desc'       => __( 'Select the post types where you would like to enable geotagging. GEO my WP Location section will be added to the "Edit Post" page of the selected post types.', 'geo-my-wp' ),
			'options'    => gmw_get_post_types_array(),
			'attributes' => array(),
			'priority'   => 5,
		);

		$zoom_levels = array();

		for ( $i = 1; $i <= 21; $i++ ) {
			$zoom_levels[ $i ] = $i;
		}

		$settings['post_types_settings']['edit_post_page_options'] = array(
			'name'       => 'edit_post_page_options',
			'type'       => 'fields_group',
			'label'      => __( 'Map Settings ( "Edit Post" Page )', 'geo-my-wp' ),
			'desc'       => __( 'Setup the map of the Location section in the "Edit Post" page.', 'geo-my-wp' ),
			'fields'     => array(
				'edit_post_page_map_latitude'   => array(
					'name'        => 'edit_post_page_map_latitude',
					'type'        => 'text',
					'default'     => '40.711544',
					'placeholder' => __( 'Latitude', 'geo-my-wp' ),
					'label'       => __( 'Default latitude', 'geo-my-wp' ),
					'desc'        => __( 'Enter the latitude of the default location that will show when the map first loads.', 'geo-my-wp' ),
					'attributes'  => array(),
					'priority'    => 5,
				),
				'edit_post_page_map_longitude'  => array(
					'name'        => 'edit_post_page_map_longitude',
					'type'        => 'text',
					'default'     => '-74.013486',
					'placeholder' => __( 'Longitude', 'geo-my-wp' ),
					'label'       => __( 'Default longitude', 'geo-my-wp' ),
					'desc'        => __( 'Enter the longitude of the default location that will show when the map first loads.', 'geo-my-wp' ),
					'attributes'  => array(),
					'priority'    => 10,
				),
				'edit_post_page_map_type'       => array(
					'name'       => 'edit_post_page_map_type',
					'type'       => 'select',
					'default'    => 'ROADMAP',
					'label'      => __( 'Map type', 'geo-my-wp' ),
					'desc'       => __( 'Select the map type.', 'geo-my-wp' ),
					'options'    => array(
						'ROADMAP'   => __( 'ROADMAP', 'geo-my-wp' ),
						'SATELLITE' => __( 'SATELLITE', 'geo-my-wp' ),
						'HYBRID'    => __( 'HYBRID', 'geo-my-wp' ),
						'TERRAIN'   => __( 'TERRAIN', 'geo-my-wp' ),
					),
					'attributes' => array(),
					'priority'   => 15,
				),
				'edit_post_page_map_zoom_level' => array(
					'name'       => 'edit_post_page_map_zoom_level',
					'type'       => 'select',
					'default'    => 7,
					'label'      => __( 'Map type', 'geo-my-wp' ),
					'desc'       => __( 'Select the zoom level of the map.', 'geo-my-wp' ),
					'options'    => $zoom_levels,
					'attributes' => array(),
					'priority'   => 20,
				),
			),
			'attributes' => '',
			'optionsbox' => 1,
			'priority'   => 20,
		);

		$settings['post_types_settings']['location_mandatory'] = array(
			'name'       => 'location_mandatory',
			'type'       => 'checkbox',
			'default'    => 0,
			'label'      => __( 'Mandatory Location', 'geo-my-wp' ),
			'cb_label'   => __( 'Enable', 'geo-my-wp' ),
			'desc'       => __( 'Prevent post submission when no location entered.', 'geo-my-wp' ),
			'attributes' => array(),
			'priority'   => 30,
		);

		return $settings;
	}
}
new GMW_Posts_Locator_Admin_Settings();
