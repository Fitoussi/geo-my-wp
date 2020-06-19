<?php
/**
 * GEO my WP Single Location Widget.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display GEO my WP search form
 *
 * @since 1.0.0
 */
class GMW_Single_Location_Widget extends GMW_Widget {

	/**
	 * Widget ID
	 *
	 * @var string
	 */
	public $widget_id = 'gmw_single_location_widget';

	/**
	 * Widget class
	 *
	 * @var string
	 */
	public $widget_class = 'geo-my-wp widget-single-location';

	/**
	 * Help page link
	 *
	 * @var string
	 */
	public $help_link = 'https://docs.geomywp.com/article/162-single-location-widget/';

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->widget_description = __( 'Display the location of a single object ( Post, BP member... ).', 'geo-my-wp' );
		$this->widget_name        = __( 'GEO my WP Single Location', 'geo-my-wp' );

		$objects = array();

		$zoom_options = array(
			'auto' => __( 'Auto Zoom', 'geo-my-wp' ),
		);

		for ( $i = 1; $i < 21; $i++ ) {
			$zoom_options[ $i ] = $i;
		}

		$this->settings = array(
			'widget_title'   => array(
				'type'        => 'text',
				'default'     => __( 'Single location', 'geo-my-wp' ),
				'label'       => __( 'Widget title', 'geo-my-wp' ),
				'description' => __( 'Enter a title for the widget or leave blank to omit.', 'geo-my-wp' ),
			),
			'element_id'     => array(
				'type'        => 'number',
				'step'        => 1,
				'min'         => 1,
				'max'         => '',
				'default'     => wp_rand( 100, 549 ),
				'label'       => __( 'Element ID', 'geo-my-wp' ),
				'description' => __( 'Use the element ID to assign a unique ID to this shortcode. The unique ID can be useful for styling purposes as well when using the hooks provided by the shortcode when custom modifications required.', 'geo-my-wp' ),
			),
			'object'         => array(
				'type'        => 'select',
				'default'     => '',
				'label'       => __( 'Object', 'geo-my-wp' ),
				'options'     => apply_filters( 'gmw_single_location_widget_objects', array() ),
				'description' => __( 'Select the object that you would like to display.', 'geo-my-wp' ),
			),
			'object_id'      => array(
				'type'        => 'number',
				'step'        => 1,
				'min'         => '',
				'max'         => '',
				'default'     => 0,
				'label'       => __( 'Object ID', 'geo-my-wp' ),
				'description' => __( 'Item ID is the ID of the item that you want to display. For example, if you want to show the location of a specific post, the item ID will be the post ID of that post. Same goes for member ID and so on. Enter 0 if you want the item to be displayed based on the single item page or based on the item being displayed inside a loop.', 'geo-my-wp' ),
			),
			/**
			'show_in_single_post' => array(
				'type'        => 'checkbox',
				'default'     => 0,
				'label'       => __( 'Show in single post page', 'geo-my-wp' ),
				'description' => __( 'Show the post author location when viewing a single post page. The author must have his location added via GEO my WP ( using Members Locator add-on and the item ID above needs to be set to 0.', 'geo-my-wp' ),
			),*/
			'elements'       => array(
				'type'        => 'text',
				'default'     => 'title,distance,map,address,directions_form,directions_panel',
				'label'       => __( 'Elements', 'geo-my-wp' ),
				'description' => __( 'Enter the elements that you would like to display, and in the order that you want to display them, comma separated. The available elements are title, distance, map, address, directions_link, directions_form, directions_panel and location_meta ( when availabe for the object ).', 'geo-my-wp' ),
			),
			'address_fields' => array(
				'type'        => 'multicheckbox',
				'default'     => array( 'address' ),
				'label'       => __( 'Address fields', 'geo-my-wp' ),
				'options'     => array(
					'address'      => __( 'Address', 'geo-my-wp' ),
					'street'       => __( 'Street', 'geo-my-wp' ),
					'city'         => __( 'City', 'geo-my-wp' ),
					'region_code'  => __( 'State', 'geo-my-wp' ),
					'postcode'     => __( 'Postcode', 'geo-my-wp' ),
					'country_code' => __( 'Country', 'geo-my-wp' ),
				),
				'description' => __( 'Choose the address fields that you would like to display.', 'geo-my-wp' ),
			),
			'location_meta'  => array(
				'type'        => 'text',
				'default'     => '',
				'label'       => __( 'Location meta', 'geo-my-wp' ),
				'description' => __( 'Enter the location meta that you would like to display, comma separated. Ex. Phone,fax,email,website.', 'geo-my-wp' ),
			),
			'units'          => array(
				'type'    => 'select',
				'default' => '',
				'label'   => __( 'Distance units', 'geo-my-wp' ),
				'options' => array(
					'imperial' => __( 'Miles', 'geo-my-wp' ),
					'metric'   => __( 'Kilometers', 'geo-my-wp' ),
				),
			),
			'map_width'      => array(
				'type'        => 'text',
				'default'     => '100%',
				'label'       => __( 'Map width', 'geo-my-wp' ),
				'description' => __( 'Set the map width in pixels or percentage ( ex. 250px or 100% ).', 'geo-my-wp' ),
			),
			'map_height'     => array(
				'type'        => 'text',
				'default'     => '250px',
				'label'       => __( 'Map height', 'geo-my-wp' ),
				'description' => __( 'Set the map height in pixels or percentage ( ex. 250px or 100% ).', 'geo-my-wp' ),
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
			'default' => '',
			'label'   => __( 'Zoom level', 'geo-my-wp' ),
			'options' => $zoom_options,
		);

		$this->settings['scrollwheel'] = array(
			'type'        => 'checkbox',
			'default'     => 0,
			'label'       => __( 'Mouse wheel zoom', 'geo-my-wp' ),
			'description' => __( 'When enabled, the map will zoom in/out using the mouse scroll wheel.', 'geo-my-wp' ),
		);

		$this->settings['object_map_icon'] = array(
			'type'        => 'text',
			'default'     => GMW()->default_icons['location_icon_url'],
			'label'       => __( 'Object map icon', 'geo-my-wp' ),
			'description' => __( 'Link to the image that you want to use as the map icon that marks the object location on the map.', 'geo-my-wp' ),
		);

		$this->settings['object_info_window'] = array(
			'type'        => 'text',
			'default'     => 'distance,title,address',
			'label'       => __( 'Object info window elements', 'geo-my-wp' ),
			'description' => __( 'Enter the elements that you would like to display in the map info window of the object, in the order that you want to display them, comma saperated. Leave blank to disable the info-window. The elements available are distance, title, address.', 'geo-my-wp' ),
		);

		$this->settings['user_map_icon'] = array(
			'type'        => 'text',
			'default'     => GMW()->default_icons['user_location_icon_url'],
			'label'       => __( 'User location map icon', 'geo-my-wp' ),
			'description' => __( 'Link to the image that you would like to use as the map marker that marks the user\'s location on the map. Leave blank to disable.', 'geo-my-wp' ),
		);

		$this->settings['user_info_window'] = array(
			'type'        => 'text',
			'default'     => __( 'Your Location', 'geo-my-wp' ),
			'label'       => __( 'User location info window', 'geo-my-wp' ),
			'description' => __( 'Enter the content that you would like to display in the user location info-window. Leave the blank to disable.', 'geo-my-wp' ),
		);

		$this->settings['no_location_message'] = array(
			'type'        => 'text',
			'default'     => __( 'No location found', 'geo-my-wp' ),
			'label'       => __( 'No location message', 'geo-my-wp' ),
			'description' => __( 'The message that you would like to display if no location exists for the item being displayed. Leave blank for no message.', 'geo-my-wp' ),
		);

		$this->register();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args     array of args.
	 *
	 * @param array $instance array of values.
	 */
	public function widget( $args, $instance ) {

		ob_start();

		$title = apply_filters( 'widget_title', $instance['widget_title'], $instance, $this->id_base );
		$title = ! empty( $title ) ? esc_html( $title ) : '';

		echo $args['before_widget']; // WPCS: XSS ok.

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title']; // WPCS: XSS ok.
		}

		$instance['address_fields'] = ! empty( $instance['address_fields'] ) ? implode( ',', $instance['address_fields'] ) : 'city,country';

		/** $instance['widget_title']   = ! empty( $instance['widget_title'] ) ? htmlentities( $args['before_title'] . $instance['widget_title'] . $args['after_title'], ENT_QUOTES ) : 0; */

		if ( function_exists( 'gmw_single_location_shortcode' ) ) {
			echo gmw_single_location_shortcode( $instance ); // WPCS: XSS ok.
		}

		echo $args['after_widget']; // WPCS: XSS ok.

		$content = ob_get_clean();

		echo $content; // WPCS: XSS ok.
	}
}
register_widget( 'GMW_Single_Location_Widget' );
