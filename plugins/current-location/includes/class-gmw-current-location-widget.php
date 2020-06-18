<?php
/**
 * GEO my WP Current Location Widget.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current Locations Widget
 *
 * @since 1.0.0
 */
class GMW_Current_Location_Widget extends GMW_Widget {

	/**
	 * Widget ID
	 *
	 * @var string
	 */
	public $widget_id = 'geo_my_wp_current_location_widget';

	/**
	 * Widget class
	 *
	 * @var string
	 */
	public $widget_class = 'geo-my-wp widget-current-location';

	/**
	 * Help page link
	 *
	 * @var string
	 */
	public $help_link = 'https://docs.geomywp.com/article/171-current-location-widget/';

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->widget_description = __( 'Retrieve and display the user\'s current position', 'geo-my-wp' );
		$this->widget_name        = __( 'GEO my WP Current Location', 'geo-my-wp' );

		$zoom_options = array();

		for ( $i = 1; $i < 21; $i++ ) {
			$zoom_options[ $i ] = $i;
		}

		$this->settings = array(
			'widget_title'              => array(
				'type'        => 'text',
				'default'     => __( 'Current Location', 'geo-my-wp' ),
				'label'       => __( 'Widget title', 'geo-my-wp' ),
				'description' => __( 'Enter a title for the widget or leave blank to omit.', 'geo-my-wp' ),
			),
			'element_id'                => array(
				'type'        => 'number',
				'step'        => 1,
				'min'         => 1,
				'max'         => '',
				'default'     => wp_rand( 550, 1000 ),
				'label'       => __( 'Element ID', 'geo-my-wp' ),
				'description' => __( 'Use the element ID to assign a unique ID to this shortcode. The unique ID can be useful for styling purposes as well when using the hooks provided by the shortcode when custom modifications required.', 'geo-my-wp' ),
			),
			'elements'                  => array(
				'type'        => 'text',
				'default'     => 'username,address,map,location_form',
				'label'       => __( 'Elements', 'geo-my-wp' ),
				'description' => __( 'Enter the elements that you would like to display, and in the order that you want to display them, comma separated. The available elements are username,address,location_form and map.', 'geo-my-wp' ),
			),
			'location_form_trigger'     => array(
				'type'        => 'text',
				'default'     => 'Get your current location',
				'label'       => __( 'Location form trigger', 'geo-my-wp' ),
				'description' => __( 'Enter in the input box the text that you would like to use as the form trigger. Leave it blank to omit.', 'geo-my-wp' ),
			),
			'address_field_placeholder' => array(
				'type'        => 'text',
				'default'     => 'Enter address',
				'label'       => __( 'Address field placeholder', 'geo-my-wp' ),
				'description' => __( 'Enter in the input box the text that you would like to use as the address field placeholder.', 'geo-my-wp' ),
			),
			'address_fields'            => array(
				'type'        => 'multicheckbox',
				'default'     => array( 'address' ),
				'label'       => __( 'Address fields', 'geo-my-wp' ),
				'options'     => array(
					'address'      => __( 'Full Address', 'geo-my-wp' ),
					'street'       => __( 'Street', 'geo-my-wp' ),
					'city'         => __( 'City', 'geo-my-wp' ),
					'region_code'  => __( 'State', 'geo-my-wp' ),
					'postcode'     => __( 'Postcode', 'geo-my-wp' ),
					'country_code' => __( 'Country', 'geo-my-wp' ),
				),
				'description' => __( 'Choose the address fields that you would like to display. Check the Full Address checkbox to display the full address ( when provided ), or check any of the address fields to display only specific fields.', 'geo-my-wp' ),
			),
			'address_label'             => array(
				'type'        => 'text',
				'default'     => 'Your location',
				'label'       => __( 'Location meta', 'geo-my-wp' ),
				'description' => __( 'Enter a label for the address field. Leave it blank to omit.', 'geo-my-wp' ),
			),
			'address_autocomplete'      => array(
				'type'        => 'checkbox',
				'default'     => 1,
				'label'       => __( 'Address autocomplete', 'geo-my-wp' ),
				'description' => __( 'Enable live suggested results by Google address autocompelte while typing an address. Note that this feature is only available with Google Maps API.', 'geo-my-wp' ),
			),
			'user_greeting'             => array(
				'type'        => 'text',
				'default'     => 'Hello',
				'label'       => __( 'User greeting message ( logged in users )', 'geo-my-wp' ),
				'description' => __( 'Type in the input box any text that you would like to use as a greeting that will show before the username. For example, type "Hello " to show "Hello {username}.', 'geo-my-wp' ),
			),
			'guest_greeting'            => array(
				'type'        => 'text',
				'default'     => 'Hello, guest!',
				'label'       => __( 'Guest greeting message', 'geo-my-wp' ),
				'description' => __( 'Enter in the input box any text that you would like to use as a greeting when a logged-out user is visiting your site. For example, "Hello Guest!', 'geo-my-wp' ),
			),
			'map_height'                => array(
				'type'        => 'text',
				'default'     => '250px',
				'label'       => __( 'Map height', 'geo-my-wp' ),
				'description' => __( 'Set the map height in pixels or percentage ( ex. 250px or 100% ).', 'geo-my-wp' ),
			),
			'map_width'                 => array(
				'type'        => 'text',
				'default'     => '100%',
				'label'       => __( 'Map width', 'geo-my-wp' ),
				'description' => __( 'Set the map width in pixels or percentage ( ex. 250px or 100% ).', 'geo-my-wp' ),
			),
			'map_marker'                => array(
				'type'        => 'text',
				'default'     => GMW()->default_icons['user_location_icon_url'],
				'label'       => __( 'Map marker icon', 'geo-my-wp' ),
				'description' => __( 'Link to the image that you want to use as the map marker.', 'geo-my-wp' ),
			),
		);

		if ( 'google_maps' === GMW()->maps_provider ) {

			$this->settings['map_type'] = array(
				'type'    => 'select',
				'default' => '',
				'label'   => __( 'Map type', 'geo-my-wp' ),
				'options' => array(
					'ROADMAP'   => __( 'ROADMAP', 'geo-my-wp' ),
					'SATELLITE' => __( 'SATELLITE', 'geo-my-wp' ),
					'HYBRID'    => __( 'HYBRID', 'geo-my-wp' ),
					'TERRAIN'   => __( 'TERRAIN', 'geo-my-wp' ),
				),
			);
		}

		$this->settings['zoom_level'] = array(
			'type'    => 'select',
			'default' => 13,
			'label'   => __( 'Zoom level', 'geo-my-wp' ),
			'options' => $zoom_options,
		);

		$this->settings['scrollwheel_zoom'] = array(
			'type'        => 'checkbox',
			'default'     => 0,
			'label'       => __( 'Mouse wheel zoom', 'geo-my-wp' ),
			'description' => __( 'When enabled, the map will zoom in/out using the mouse scroll wheel.', 'geo-my-wp' ),
		);

		$this->settings['ajax_update'] = array(
			'type'        => 'hidden',
			'default'     => 0,
			'label'       => __( 'Update via ajax', 'geo-my-wp' ),
			'description' => __( 'Check this checkbox to update the location form via AJAX instead of page load.', 'geo-my-wp' ),
		);

		$this->settings['loading_message'] = array(
			'type'        => 'text',
			'default'     => 'Retrieving your current location...',
			'label'       => __( 'Loading message', 'geo-my-wp' ),
			'description' => __( 'Enter a message to display while retrieving the user\'s location. Leave blank to omit.', 'geo-my-wp' ),
		);

		$this->register();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args widget arguments.
	 *
	 * @param array $instance widget values.
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		echo $before_widget;

		$instance['address_fields'] = ! empty( $instance['address_fields'] ) ? implode( ',', $instance['address_fields'] ) : 'city,country';
		$instance['widget_title']   = ! empty( $instance['widget_title'] ) ? htmlentities( $args['before_title'] . $instance['widget_title'] . $args['after_title'], ENT_QUOTES ) : 0;

		if ( class_exists( 'GMW_Current_Location' ) ) {

			$current_location = new GMW_Current_Location( $instance );

			echo $current_location->output();
		}

		echo $after_widget;
	}
}
register_widget( 'GMW_Current_Location_Widget' );
