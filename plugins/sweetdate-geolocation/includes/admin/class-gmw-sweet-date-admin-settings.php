<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Sweet_Date_Admin class
 */
class GMW_Sweet_Date_Admin_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->settings = get_option( 'gmw_options' );

		// create post types settings tab/group
		add_filter( 'gmw_admin_settings_groups', array( $this, 'admin_settings_group' ), 5 );
		add_filter( 'gmw_admin_settings', array( $this, 'settings_init' ) );
		add_filter( 'admin_footer', array( $this, 'footer_script' ) );

		if ( empty( $this->settings['sweet_date'] ) ) {
			add_filter( 'admin_init', array( $this, 'default_options' ) );
		}
	}

	/**
	 * Set default values if not exists
	 */
	public function default_options() {

		$this->settings['sweet_date'] = array(
			'enabled'              => 1,
			'address_autocomplete' => 1,
			'radius'               => '10,25,50,100,200',
			'units'                => '3959',
			'orderby'              => 1,
			'map'                  => 1,
			'map_width'            => '100%',
			'map_height'           => '300px',
			'map_type'             => 'ROADMAP',
			'distance'             => 1,
			'address'              => 1,
			'directions_link'      => 1,
		);

		update_option( 'gmw_options', $this->settings );
	}

	/**
	 * Create Post Types settings group
	 *
	 * @param  [type] $groups [description]
	 * @return [type]         [description]
	 */
	public function admin_settings_group( $groups ) {

		$groups[] = array(
			'id'    => 'sweet_date',
			'label' => __( 'Sweet Date', 'geo-my-wp' ),
			'icon'  => 'location-outline',
		);

		return $groups;
	}

	/**
	 * addon settings page function.
	 *
	 * @access public
	 * @return $settings
	 */
	public function settings_init( $settings ) {

		$settings['sweet_date'] = array(
			'enabled'              => array(
				'name'       => 'enabled',
				'type'       => 'checkbox',
				'cb_label'   => 'Enabled',
				'default'    => '',
				'label'      => __( 'Enable Geolocation', 'geo-my-wp' ),
				'desc'       => __( 'Enable/disable the geolocation features in the Members Directory page of the Sweet Date theme.', 'geo-my-wp' ),
				'attributes' => array(),
				'priority'   => 10,
			),
			'address_autocomplete' => array(
				'name'       => 'address_autocomplete',
				'type'       => 'checkbox',
				'cb_label'   => 'Enabled',
				'default'    => '',
				'label'      => __( 'Address Autocomplete', 'geo-my-wp' ),
				'desc'       => __( 'Enable address autocomplete feature in the address field.', 'geo-my-wp' ),
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
				'desc'       => __( 'Select miles or kilometers.', 'geo-my-wp' ),
				'options'    => array(
					'3959' => __( 'Miles', 'geo-my-wp' ),
					'6371' => __( 'Kilometers', 'geo-my-wp' ),
				),
				'attributes' => array(),
				'priority'   => 50,
			),
			'orderby'              => array(
				'name'       => 'orderby',
				'type'       => 'checkbox',
				'cb_label'   => 'Enabled',
				'default'    => '',
				'label'      => __( 'Orderby Filter', 'geo-my-wp' ),
				'desc'       => __( 'Enable Orderby dropdown menu in the search form.', 'geo-my-wp' ),
				'attributes' => array(),
				'priority'   => 60,
			),
			'map'                  => array(
				'name'       => 'map',
				'type'       => 'checkbox',
				'default'    => '',
				'cb_label'   => 'Enabled',
				'label'      => __( 'Map', 'geo-my-wp' ),
				'desc'       => __( 'Enable map above list of members.', 'geo-my-wp' ),
				'attributes' => array(),
				'priority'   => 70,
			),
			'map_width'            => array(
				'name'        => 'map_width',
				'type'        => 'text',
				'default'     => '100%',
				'label'       => __( 'Map Width', 'geo-my-wp' ),
				'placeholder' => __( 'Enter map width', 'geo-my-wp' ),
				'desc'        => __( 'Map width in pixels or percentage ( ex. 100% or 200px ).', 'geo-my-wp' ),
				'attributes'  => array(),
				'priority'    => 80,

			),
			'map_height'           => array(
				'name'        => 'map_height',
				'type'        => 'text',
				'default'     => '300px',
				'label'       => __( 'Map Height', 'geo-my-wp' ),
				'placeholder' => __( 'Enter map height', 'geo-my-wp' ),
				'desc'        => __( 'Map height in pixels or percentage ( ex. 100% or 200px ).', 'geo-my-wp' ),
				'attributes'  => array(),
				'priority'    => 90,
			),
			'distance'             => array(
				'name'       => 'distance',
				'type'       => 'checkbox',
				'default'    => '',
				'label'      => __( 'Distance', 'geo-my-wp' ),
				'cb_label'   => __( 'Enabled', 'geo-my-wp' ),
				'desc'       => __( 'Display the distance to each member in the list of results.', 'geo-my-wp' ),
				'attributes' => array(),
				'priority'   => 110,
			),
			'address'              => array(
				'name'       => 'address',
				'default'    => '',
				'label'      => __( 'Address', 'geo-my-wp' ),
				'cb_label'   => __( 'Enabled', 'geo-my-wp' ),
				'desc'       => __( 'Display the address of each member in the list of results.', 'geo-my-wp' ),
				'type'       => 'checkbox',
				'attributes' => array(),
				'priority'   => 120,
			),
			'directions_link'      => array(
				'name'       => 'directions_link',
				'type'       => 'checkbox',
				'default'    => '',
				'label'      => __( 'Directions Link', 'geo-my-wp' ),
				'cb_label'   => __( 'Enabled', 'geo-my-wp' ),
				'desc'       => __( 'Display directions link, which will open a new window with Google map showing the driving directions, in each member in the list of results.', 'geo-my-wp' ),
				'attributes' => array(),
				'priority'   => 130,
			),
		);

		if ( 'google_maps' == GMW()->maps_provider ) {
			$settings['sweet_date']['map_type'] = array(
				'name'       => 'map_type',
				'type'       => 'select',
				'default'    => 'ROADMAP',
				'label'      => __( 'Map Type', 'geo-my-wp' ),
				'desc'       => __( 'Select the map type.', 'geo-my-wp' ),
				'options'    => array(
					'ROADMAP'   => __( 'ROADMAP', 'geo-my-wp' ),
					'SATELLITE' => __( 'SATELLITE', 'geo-my-wp' ),
					'HYBRID'    => __( 'HYBRID', 'geo-my-wp' ),
					'TERRAIN'   => __( 'TERRAIN', 'geo-my-wp' ),
				),
				'attributes' => array(),
				'priority'   => 100,
			);
		}

		return $settings;
	}

	/**
	 * JavaScripts
	 *
	 * @return [type] [description]
	 */
	function footer_script() {
		?>
		<script>
		jQuery( document ).ready( function( $ ) {

			function hideRows() {
				$( "#sweet_date-enabled-tr" ).show().siblings().toggle();
			}

			if ( ! $( '#setting-sweet_date-enabled' ).is( ':checked' ) ) {
				hideRows();
			}

			$( '#setting-sweet_date-enabled' ).on( 'change', function() {
				hideRows();
			});
		} );
		</script>
		<?php
	}
}
