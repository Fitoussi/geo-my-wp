<?php
/**
 * GEO my WP form editor.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Edit_Form calss
 *
 * Edit GMW forms in the back end
 *
 * @since 2.5
 *
 * @author Fitoussi Eyal
 */
class GMW_Form_Editor {

	/**
	 * Enable / disable ajax in form editor.
	 *
	 * @var boolean
	 */
	public $ajax_enabled = true;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// trigger ajax form update.
		if ( apply_filters( 'gmw_form_editor_ajax_enabled', true ) ) {

			$this->ajax_enabled = true;

			add_action( 'wp_ajax_gmw_update_admin_form', array( $this, 'ajax_update_form' ) );
		}

		// verify that this is Form edit page.
		if ( empty( $_GET['page'] ) || 'gmw-forms' !== $_GET['page'] || empty( $_GET['gmw_action'] ) || 'edit_form' !== $_GET['gmw_action'] ) {
			return;
		}

		do_action( 'gmw_admin_form_editor_init', $_GET );

		if ( ! $this->ajax_enabled ) {
			add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
			add_action( 'gmw_update_admin_form', array( $this, 'update_form' ) );
		}

		// make sure form ID passed.
		if ( empty( $_GET['form_id'] ) || ! absint( $_GET['form_id'] ) ) {
			wp_die( __( 'No form ID provided.', 'geo-my-wp' ) );
		}

		$form_id = (int) $_GET['form_id'];

		// get form data.
		$this->form = GMW_Forms_Helper::get_form( $form_id );

		if ( ! gmw_is_addon_active( $this->form['addon'] ) ) {

			$link = 'The extension this form belongs to is deactivated. <a href="' . esc_url( 'admin.php?page=gmw-extensions' ) . '">Manage extensions</a>';

			wp_die( $link );
		}

		// varify if the form exists.
		if ( empty( $this->form ) ) {
			wp_die( __( 'The form you are trying to edit doe\'s not exist!', 'geo-my-wp' ) );
		}
	}

	/**
	 * GMW Function - add notice messages.
	 *
	 * @param  [type] $messages [description].
	 *
	 * @return [type]           [description]
	 */
	public function notices_messages( $messages ) {

		$messages['form_updated']     = __( 'Form successfully updated.', 'geo-my-wp' );
		$messages['form_not_updated'] = __( 'There was an error while trying to update the form.', 'geo-my-wp' );

		return $messages;
	}

	/**
	 * Form groups
	 *
	 * @return [type] [description]
	 */
	public function fields_groups() {

		// settings groups.
		$groups = array(
			'hidden'            => array(
				'slug'     => 'hidden',
				'label'    => __( 'hidden', 'geo-my-wp' ),
				'fields'   => array(),
				'priority' => 1,
			),
			'page_load_results' => array(
				'slug'     => 'page_load_results',
				'label'    => __( 'Page Load Results', 'geo-my-wp' ),
				'fields'   => array(
					'enabled'         => array(
						'name'       => 'enabled',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Enable Page Load Features', 'geo-my-wp' ),
						'desc'       => __( 'Check this checkbox to dynamically display all existing posts on page load. You can filter the initial search result using the rest of the filters below.', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'attributes' => '',
						'priority'   => 10,
					),
					'user_location'   => array(
						'name'       => 'user_location',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( "Visitor's Current Location Filter", 'geo-my-wp' ),
						'desc'       => __( "GEO my WP will first check for the visitor's current location on page load. And If exists, the locations will be displayed based on that. Notice that the address filter below will be ignored if the visitor's location exists.", 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'attributes' => '',
						'priority'   => 20,
					),
					'address_filter'  => array(
						'name'        => 'address_filter',
						'type'        => 'text',
						'default'     => '',
						'placeholder' => __( 'Enter an address', 'geo-my-wp' ),
						'label'       => __( 'Address Filter', 'geo-my-wp' ),
						'desc'        => __( 'Enter an address to search for locations neaby it when the form first loads.', 'geo-my-wp' ),
						'attributes'  => array( 'size' => '25' ),
						'priority'    => 30,
					),
					'radius'          => array(
						'name'        => 'radius',
						'type'        => 'text',
						'default'     => '',
						'placeholder' => __( 'Ex. 100', 'geo-my-wp' ),
						'label'       => __( 'Distance ( radius )', 'geo-my-wp' ),
						'desc'        => __( 'Enter default radius value, which will be used when visitor\'s location exists or when address is provided.', 'geo-my-wp' ),
						'attributes'  => array(),
						'priority'    => 40,
					),
					'units'           => array(
						'name'       => 'units',
						'type'       => 'select',
						'default'    => 'imperial',
						'label'      => __( 'Units', 'geo-my-wp' ),
						'desc'       => __( 'Select the distance units.', 'geo-my-wp' ),
						'options'    => array(
							'imperial' => __( 'Miles', 'geo-my-wp' ),
							'metric'   => __( 'Kilometers', 'geo-my-wp' ),
						),
						'attributes' => array(),
						'priority'   => 50,
					),
					'city_filter'     => array(
						'name'        => 'city_filter',
						'type'        => 'text',
						'default'     => '',
						'placeholder' => __( 'Enter city', 'geo-my-wp' ),
						'label'       => __( 'City Filter', 'geo-my-wp' ),
						'desc'        => __( 'Filter locations by city, or leave blank to omit. When using this filter, GEO my WP does not do a proximity search, but will pull locations with the exact matching city name.', 'geo-my-wp' ),
						'attributes'  => array(),
						'priority'    => 60,
					),
					'state_filter'    => array(
						'name'        => 'state_filter',
						'type'        => 'text',
						'default'     => '',
						'placeholder' => __( 'Enter state', 'geo-my-wp' ),
						'label'       => __( 'State Filter', 'geo-my-wp' ),
						'desc'        => __( 'Filter locations by state, or leave blank to omit. When using this filter, GEO my WP does not do a proximity search but will pull locations with the exact matching state name.', 'geo-my-wp' ),
						'attributes'  => array( 'size' => '25' ),
						'priority'    => 70,
					),
					'zipcode_filter'  => array(
						'name'        => 'zipcode_filter',
						'type'        => 'text',
						'default'     => '',
						'placeholder' => __( 'Enter postcode', 'geo-my-wp' ),
						'label'       => __( 'Zipcode Filter', 'geo-my-wp' ),
						'desc'        => __( 'Filter locations by zipcode, or leave blank to omit. When using this filter, GEO my WP does not do a proximity search but will pull locations with the exact matching zipcode.', 'geo-my-wp' ),
						'attributes'  => array( 'size' => '25' ),
						'priority'    => 80,
					),
					'country_filter'  => array(
						'name'        => 'country_filter',
						'type'        => 'select',
						'default'     => '',
						'placeholder' => __( 'Enter country', 'geo-my-wp' ),
						'label'       => __( 'Country Filter', 'geo-my-wp' ),
						'desc'        => __( 'Filter locations by country, or leave blank to omit. When using this filter, GEO my WP does not do a proximity search but will pull locations with the exact matching country name.', 'geo-my-wp' ),
						'options'     => gmw_get_countries_list_array( 'Disable' ),
						'attributes'  => array( 'size' => '25' ),
						'priority'    => 90,
					),
					'display_results' => array(
						'name'       => 'display_results',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Display list of results', 'geo-my-wp' ),
						'desc'       => __( 'Display list of results.', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 100,
					),
					'display_map'     => array(
						'name'     => 'display_map',
						'type'     => 'select',
						'default'  => '',
						'label'    => __( 'Display Map', 'geo-my-wp' ),
						'desc'     => __( 'Disable the map completely, display it above the list of result, or display it anywhere on the page using the shortcode <code>[gmw map=\"form ID\"]</code>.', 'geo-my-wp' ),
						'options'  => array(
							''          => __( 'Disable map', 'geo-my-wp' ),
							'results'   => __( 'Above the list of result', 'geo-my-wp' ),
							'shortcode' => __( 'Using shortcode', 'geo-my-wp' ),
						),
						'priority' => 110,
					),
					'per_page'        => array(
						'name'        => 'per_page',
						'type'        => 'text',
						'default'     => '5,10,15,25',
						'placeholder' => __( 'Enter values', 'geo-my-wp' ),
						'label'       => __( 'Results Per Page', 'geo-my-wp' ),
						'desc'        => __( 'Set the per page value of the initial form load. Enter multiple values, comma separated, to display a per page select dropdown menu in the search results, or enter a single value to set a default per-page value.', 'geo-my-wp' ),
						'attributes'  => array(),
						'priority'    => 120,
					),
				),
				'priority' => 10,
			),
			'search_form'       => array(
				'slug'     => 'search_form',
				'label'    => __( 'Search Form', 'geo-my-wp' ),
				'fields'   => array(
					'form_template'  => array(
						'name'       => 'form_template',
						'type'       => 'select',
						'default'    => '',
						'label'      => __( 'Search Form Template', 'geo-my-wp' ),
						'desc'       => __( 'Select The search form template file.', 'geo-my-wp' ),
						'options'    => array( '' => __( 'Disabled', 'geo-my-wp' ) ) + gmw_get_search_form_templates( $this->form['component'], $this->form['addon'] ),
						'attributes' => array(),
						'priority'   => 10,
					),
					'address_field'  => array(
						'name'       => 'address_field',
						'type'       => 'function',
						'default'    => '',
						'label'      => __( 'Address Field', 'geo-my-wp' ),
						'cb_label'   => '',
						'desc'       => __( '<ul><li>- Label - enter a lable that you would like to use or leave blank for no label.</li><li>- Placeholder - enter a placeholder that you would like to use or leave blank for no placeholder.</li><li>Mandatory - check to make the field mandatory</li><li>- Address autocomplete - check to enable Google address autocomplete feature.</li><li>- Locator button - check to display a locator button inside the address field.</li><li>-Locator submit - check to dynamically submit the form when the address was found using the locator button.</li></ul>', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 20,
					),
					'locator_button' => array(
						'name'       => 'locator_button',
						'type'       => 'fields_group',
						'label'      => __( 'Locator Button', 'geo-my-wp' ),
						'desc'       => __( '<p>Using the locator button the visitor can dynamically retrieve his current location.</p><p>- Select "disabled" to disable the locator button.</p><p>- Select "Image" and select an image that will be used as the locator button.</p><p>- Select "Text" and enter any text that you would like to use as the locator button.</p><p>- Check the "Auto form Submission" text box to dynamically submit the search form once the visitors locator was found.</p>', 'geo-my-wp' ),
						'fields'     => array(
							array(
								'name'       => 'locator',
								'type'       => 'select',
								'default'    => '',
								'label'      => __( 'Usage', 'geo-my-wp' ),
								'attributes' => array(),
								'options'    => array(
									'disabled' => __( 'Disabled', 'geo-my-wp' ),
									'text'     => __( 'Text', 'geo-my-wp' ),
									'image'    => __( 'Image', 'geo-my-wp' ),
								),
								'priority'   => 5,
							),
							array(
								'name'       => 'locator_text',
								'type'       => 'text',
								'default'    => '',
								'label'      => __( 'Label', 'geo-my-wp' ),
								'attributes' => array(),
								'priority'   => 10,
							),
							array(
								'name'       => 'locator_image',
								'type'       => 'radio',
								'default'    => '',
								'label'      => __( 'Image', 'geo-my-wp' ),
								'options'    => $this->locator_options(),
								'attributes' => array(),
								'priority'   => 15,
							),
							array(
								'name'       => 'locator_submit',
								'type'       => 'checkbox',
								'default'    => '',
								'cb_label'   => __( 'Auto submission', 'geo-my-wp' ),
								'attributes' => array(),
								'priority'   => 20,
							),
						),
						'attributes' => '',
						'optionsbox' => 1,
						'priority'   => 30,
					),
					'radius'         => array(
						'name'        => 'radius',
						'type'        => 'text',
						'default'     => '5,10,15,25,50,100',
						'placeholder' => __( 'Radius values', 'geo-my-wp' ),
						'label'       => __( 'Distance ( radius )', 'geo-my-wp' ),
						'desc'        => __( 'Enter multiple distance values, comma separated, to display a select dropdown menu in the search form. Or enter a single value to set a default distance value.', 'geo-my-wp' ),
						'attributes'  => array( 'size' => '25' ),
						'priority'    => 40,
					),
					'units'          => array(
						'name'       => 'units',
						'type'       => 'select',
						'default'    => 'both',
						'label'      => __( 'Distance Units', 'geo-my-wp' ),
						'desc'       => __( 'Choose between miles or kilometers to set a default units value, or select "both" to display a select dropdown menu in the search form.', 'geo-my-wp' ),
						'options'    => array(
							'both'     => __( 'Both', 'geo-my-wp' ),
							'imperial' => __( 'Miles', 'geo-my-wp' ),
							'metric'   => __( 'Kilometers', 'geo-my-wp' ),
						),
						'attributes' => '',
						'priority'   => 50,
					),
				),
				'priority' => 20,
			),
			'form_submission'   => array(
				'slug'     => 'form_submission',
				'label'    => __( 'Form Submission', 'geo-my-wp' ),
				'fields'   => array(
					'results_page'    => array(
						'name'       => 'results_page',
						'type'       => 'select',
						'default'    => '',
						'label'      => __( 'Results Page', 'geo-my-wp' ),
						'desc'       => __( 'The results page displays the search results in the selected page when using the "GMW Search Form" widget, or when you wish to have the search form in one page and the results showing in a different page. To use this feature, select the results page from the dropdown menu and paste the shortcode <code>[gmw form="results"]</code> into the content area of that page. Otherwise, select "Same Page" to display both the search form and search results in the same page.', 'geo-my-wp' ),
						'options'    => array( '' => __( ' -- Same Page -- ', 'geo-my-wp' ) ) + GMW_Form_Settings_Helper::get_pages(),
						'attributes' => '',
						'priority'   => 10,
					),
					'display_results' => array(
						'name'       => 'display_results',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Display list of results', 'geo-my-wp' ),
						'desc'       => __( 'Check this checkbox to output a list of results on form submission.', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 30,
					),
					'display_map'     => array(
						'name'       => 'display_map',
						'type'       => 'select',
						'default'    => '',
						'label'      => __( 'Display Map', 'geo-my-wp' ),
						'desc'       => __( 'Select if to disable the map completely, display it above the list of result, or display it anywhere on the page using the shortcode <code>[gmw map="form ID"]</code>.', 'geo-my-wp' ),
						'options'    => array(
							''          => __( 'Disable map', 'geo-my-wp' ),
							'results'   => __( 'Above the list of result', 'geo-my-wp' ),
							'shortcode' => __( 'Using shortcode', 'geo-my-wp' ),
						),
						'attributes' => array(),
						'priority'   => 40,
					),
				),
				'priority' => 30,
			),
			'search_results'    => array(
				'slug'     => 'search_results',
				'label'    => __( 'Search Results', 'geo-my-wp' ),
				'fields'   => array(
					'results_template' => array(
						'name'       => 'results_template',
						'type'       => 'select',
						'default'    => 'gray',
						'label'      => __( 'Results Template', 'geo-my-wp' ),
						'desc'       => __( 'Select the search results template file.', 'geo-my-wp' ),
						'options'    => gmw_get_search_results_templates( $this->form['component'], $this->form['addon'] ),
						'attributes' => array(),
						'priority'   => 10,
					),
					'per_page'         => array(
						'name'        => 'per_page',
						'type'        => 'text',
						'default'     => '5,10,15,25',
						'placeholder' => __( 'Enter values', 'geo-my-wp' ),
						'label'       => __( 'Results Per Page', 'geo-my-wp' ),
						'desc'        => __( 'Enter multiple values, comma separated, to display a per page select dropdown menu in the search results, or enter a single value to set a default per-page value.', 'geo-my-wp' ),
						'attributes'  => array(),
						'priority'    => 20,
					),
					'image'            => array(
						'name'       => 'image',
						'type'       => 'function',
						'default'    => '',
						'label'      => __( 'Image', 'geo-my-wp' ),
						'desc'       => __( '<p>Check this checkbox to display the image of each location in the list of results, then enter the width and height in pixels ( enter numeric value only, without "px" ).</p>', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 30,
					),
					/*
					'by_driving'       => array(
						'name'       	=> 'by_driving',
						'type'       	=> 'checkbox',
						'default'       => '',
						'label'      	=> __( 'Driving Distance', 'geo-my-wp' ),
						'cb_label'   	=> __( 'Enable', 'geo-my-wp' ),
						'desc'       	=> __( 'While the results showing the radius distance from the user to each of the locations, this feature let you display the exact driving distance. Please note that each driving distance request counts with google API when you can have 2500 requests per day.', 'geo-my-wp' ),
						'attributes' 	=> array(),
						'priority'		=> 35
					),
					*/
					'location_meta'    => array(
						'name'        => 'location_meta',
						'type'        => 'multiselect',
						'default'     => '',
						'label'       => __( 'Location Meta', 'geo-my-wp' ),
						'placeholder' => __( 'Select location metas', 'geo-my-wp' ),
						'desc'        => __( 'Select the the location meta fields which you would like to display for each location in the list of results.', 'geo-my-wp' ),
						'options'     => array(
							'phone'   => __( 'Phone', 'geo-my-wp' ),
							'fax'     => __( 'Fax', 'geo-my-wp' ),
							'email'   => __( 'Email', 'geo-my-wp' ),
							'website' => __( 'Website', 'geo-my-wp' ),
						),
						// 'options'       => GMW_Form_Settings_Helper::get_location_meta(),
						'attributes'  => '',
						'priority'    => 35,
					),
					'opening_hours'    => array(
						'name'       => 'opening_hours',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Hours of Operation', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'desc'       => __( 'Display opening days & hours for each location in the list of results.', 'geo-my-wp' ),
						'attributes' => '',
						'priority'   => 40,
					),
					'directions_link'  => array(
						'name'       => 'directions_link',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Directions Link', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'desc'       => __( 'Display directions link, that will open a new window showing the driving directions, in each location in the list of results.', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 45,
					),
				),
				'priority' => 40,
			),
			'results_map'       => array(
				'slug'     => 'results_map',
				'label'    => __( 'Map', 'geo-my-wp' ),
				'fields'   => array(
					'map_width'  => array(
						'name'        => 'map_width',
						'type'        => 'text',
						'default'     => '100%',
						'placeholder' => __( 'Map width in px or %', 'geo-my-wp' ),
						'label'       => __( 'Map width', 'geo-my-wp' ),
						'desc'        => __( 'Enter the map\'s width in pixels or percentage ( ex. 100% or 200px ).', 'geo-my-wp' ),
						'attributes'  => array(),
						'priority'    => 10,
					),
					'map_height' => array(
						'name'        => 'map_height',
						'type'        => 'text',
						'default'     => '300px',
						'placeholder' => __( 'Map height in px or %', 'geo-my-wp' ),
						'label'       => __( 'Map height', 'geo-my-wp' ),
						'desc'        => __( 'Enter the map\'s height in pixels or percentage ( ex. 100% or 200px ).', 'geo-my-wp' ),
						'attributes'  => array(),
						'priority'    => 20,
					),
					'zoom_level' => array(
						'name'       => 'zoom_level',
						'default'    => 'auto',
						'type'       => 'select',
						'label'      => __( 'Zoom level', 'geo-my-wp' ),
						'desc'       => __( 'Select "Auto zoom" to fit all the markers on the map, or select a numeric value that will be used to zoom into the marker which represents the visitor\'s current location on the map.', 'geo-my-wp' ),
						'options'    => array(
							'auto' => __( 'Auto Zoom', 'geo-my-wp' ),
							'1'    => '1',
							'2'    => '2',
							'3'    => '3',
							'4'    => '4',
							'5'    => '5',
							'6'    => '6',
							'7'    => '7',
							'8'    => '8',
							'9'    => '9',
							'10'   => '10',
							'11'   => '11',
							'12'   => '12',
							'13'   => '13',
							'14'   => '14',
							'15'   => '15',
							'16'   => '16',
							'17'   => '17',
							'18'   => '18',
							'19'   => '19',
							'20'   => '20',
						),
						'attributes' => '',
						'priority'   => 40,
					),
					/*
					'yl_icon'         => array(
						'name'        	=> 'yl_icon',
						'type'  	  	=> 'checkbox',
						'default'       => '',
						'label' 	  	=> __( 'Open "User Location" info window', 'geo-my-wp'),
						'cb_label'    	=> __( 'Enable', 'geo-my-wp'),
						'desc'        	=> __( "Dynamically open on page load the info window of the marker which represents the user's location.", 'geo-my-wp' ),
						'attributes'  	=> array(),
						'priority'		=> 25
					),
					'no_results_enabled'  	=> array(
						'name'       	=> 'no_results_enabled',
						'type'       	=> 'checkbox',
						'default'       => '',
						'label'      	=> __( 'Show if no results', 'geo-my-wp' ),
						'cb_label'   	=> __( 'Enable', 'geo-my-wp' ),
						'desc'       	=> __( 'Display map even if no results were found.', 'geo-my-wp' ),
						'attributes' 	=> array(),
						'priority'		=> 35
					), */
				),
				'priority' => 50,
			),
		);

		$groups['results_map']['fields']['map_type'] = array(
			'name'       => 'map_type',
			'type'       => 'hidden',
			'default'    => 'ROADMAP',
			'label'      => __( 'Map type', 'geo-my-wp' ),
			'desc'       => __( 'Select the map type.', 'geo-my-wp' ),
			'attributes' => '',
			'priority'   => 30,
		);

		if ( 'google_maps' === GMW()->maps_provider && isset( $groups['results_map'] ) ) {

			$groups['results_map']['fields']['map_type']['type']    = 'select';
			$groups['results_map']['fields']['map_type']['options'] = array(
				'ROADMAP'   => __( 'ROADMAP', 'geo-my-wp' ),
				'SATELLITE' => __( 'SATELLITE', 'geo-my-wp' ),
				'HYBRID'    => __( 'HYBRID', 'geo-my-wp' ),
				'TERRAIN'   => __( 'TERRAIN', 'geo-my-wp' ),
			);
		}

		$disable_additional_fields = apply_filters( 'gmw_form_editor_disable_additional_fields', true, $groups, $this->form['slug'], $this );
		$disable_additional_fields = apply_filters( 'gmw_' . $this->form['slug'] . '_form_editor_disable_additional_fields', $disable_additional_fields, $groups, $this->form['slug'], $this );

		// Contact info and hours of operation settings are disabled by default. It can be enabled using this filter.
		if ( $disable_additional_fields ) {
			unset( $groups['search_results']['fields']['location_meta'], $groups['search_results']['fields']['opening_hours'] );
		}

		$temp_array = array();

		// generate slug for groups. To easier unset groups if needed.
		foreach ( $groups as $group ) {
			$temp_array[ $group['slug'] ] = $group;
		}

		$groups = $temp_array;
		$groups = apply_filters( 'gmw_' . $this->form['slug'] . '_form_settings_groups', $groups, $this->form );
		$groups = apply_filters( 'gmw_' . $this->form['addon'] . '_addon_form_settings_groups', $groups, $this->form );
		$groups = apply_filters( 'gmw_form_settings_groups', $groups, $this->form );

		uasort( $groups, 'gmw_sort_by_priority' );

		return $groups;
	}

	/**
	 * Get fields
	 *
	 * @return [type] [description]
	 */
	public function get_fields() {

		$fields = array();

		// loop through settings groups.
		foreach ( $this->form_settings_groups as $key => $group ) {

			// verify groups slug.
			if ( empty( $group['slug'] ) ) {
				continue;
			}

			// Generate the group if does not exsist.
			if ( ! isset( $fields[ $group['slug'] ] ) ) {

				$fields[ $group['slug'] ] = ! empty( $group['fields'] ) ? $group['fields'] : array();

				// otehrwise, merge the fields of the existing group
				// with the current group.
			} else {

				$fields[ $group['slug'] ] = array_merge_recursive( $fields[ $group['slug'] ], $group['fields'] );

				// remove the duplicate group/tab.
				unset( $this->form_settings_groups[ $key ] );
			}

			// allow filtering the specific group.
			$fields[ $group['slug'] ] = apply_filters( 'gmw_' . $group['slug'] . '_form_settings', $fields[ $group['slug'] ], $this->form['slug'], $this->form );
		}

		// filter all fields groups.
		$fields = apply_filters( 'gmw_' . $this->form['slug'] . '_form_settings', $fields, $this->form );
		$fields = apply_filters( 'gmw_' . $this->form['addon'] . '_addon_form_settings', $fields, $this->form );
		$fields = apply_filters( 'gmw_form_settings', $fields, $this->form );

		return $fields;
	}

	/**
	 * Form Fields
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function init_form_settings() {

		// get groups.
		$this->form_settings_groups = $this->fields_groups();

		// get fields.
		$this->form_fields = $this->get_fields();

		// allow plugins to extend the form fields
		// $new_settings = array();
		// $this->form_fields = apply_filters( 'gmw_' . $this->form['slug'] . '_form_settings', $this->form_fields, $this->form );
		// $this->form_fields = apply_filters( 'gmw_form_settings', $this->form_fields, $this->form );
		// merge settings added from other plugins
		/*
		foreach ( $new_settings as $group => $fields ) {

			if ( empty( $this->form_fields[$group] ) ) {
				$this->form_fields[$group] = array();
			}

			$this->form_fields[$group] = array_merge( $this->form_fields[$group], $fields );
		} */

		// backward capability for settings before settings groups were created.
		foreach ( $this->form_fields as $key => $section ) {

			if ( ! empty( $section[0] ) && ! empty( $section[1] ) && is_string( $section[0] ) ) {

				trigger_error( 'Using deprecated method for registering GMW settings and settings groups.', E_USER_NOTICE );

				$this->form_settings_groups[] = array(
					'slug'  => $key,
					'label' => $section[0],
				);

				$this->form_fields[ $key ] = $section[1];
			}
		}

		// backward capability for replacing std with default.
		foreach ( $this->form_fields as $key => $section ) {

			foreach ( $section as $sec_key => $sec_value ) {

				// skip hidden field.
				if ( empty( $sec_value ) ) {
					continue;
				}

				if ( isset( $sec_value['std'] ) && ! isset( $sec_value['default'] ) ) {

					gmw_trigger_error( '"std" attribute is no longer supported in GMW settings and was replaced with "default" since version 3.0.', E_USER_NOTICE );

					$this->form_fields[ $key ][ $sec_key ]['default'] = ! empty( $sec_value['default'] ) ? $sec_value['default'] : '';

					unset( $this->form_fields[ $key ][ $sec_key ]['std'] );
				}
			}
		}
	}

	/**
	 * Get locator button images
	 *
	 * @return [type] [description]
	 */
	public function locator_options() {

		$locator_images = glob( GMW_PATH . '/assets/images/locator-images/*.png' );
		$display_image  = GMW_IMAGES . '/locator-images/';

		$options = array();

		foreach ( $locator_images as $locator_image ) {
			$basename                         = basename( $locator_image );
			$options[ basename( $basename ) ] = '<img src="' . esc_url( $display_image . $basename ) . '" height="30px" width="30px" />';
		}

		return $options;
	}

	/**
	 * Form usage tab.
	 */
	public function form_usage() {
		?>
		<!-- form usage -->
		<div class="gmw-settings-panel gmw-tab-panel form-usage">

			<table class="widefat gmw-form-usage-table">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column" style="width: 33%;"><?php _e( 'Description', 'geo-my-wp' ); ?></th>
						<th scope="col" id="cb" class="manage-column" style="width: 27%;"><?php _e( 'Post/Page Content', 'geo-my-wp' ); ?></th>
						<th scope="col" id="cb" class="manage-column" style="width: 40%;"><?php _e( 'Tempalte file', 'geo-my-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php if ( 'global_maps' == $this->form['addon'] ) { ?>

						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php _e( 'Display the global map anywhere on the page.', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[gmw_global_map form="<?php echo esc_attr( $this->form['ID'] ); ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[gmw_global_map form="' . esc_attr( $this->form['ID'] ) . '"]\' ); &#63;&#62;'; ?></code></p>
							</td>
						</tr>

					<?php } else { ?>

					<?php $scpx = ( 'ajax_forms' !== $this->form['addon'] ) ? 'gmw' : 'gmw_ajax_form'; ?>

						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php _e( 'Display the complete form ( search form, map, and search results ).', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[<?php echo $scpx; ?> form="<?php echo esc_attr( $this->form['ID'] ); ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[ ' . $scpx .' form="' . esc_attr( $this->form['ID'] ) . '"]\' ); &#63;&#62;'; ?></code></p>
							</td>                			
						</tr>
						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php _e( 'Display the search form only.', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[<?php echo $scpx; ?> search_form="<?php echo esc_attr( $this->form['ID'] ); ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[ ' . $scpx .' search_form="' . esc_attr( $this->form['ID'] ) . '"]\' ); &#63;&#62;'; ?></code></p>
							</td>		
						</tr>
						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php _e( 'Display the search results of this form only. Can be used to display the search results in a different page or when using the search form in a widget.', 'geo-my-wp' ); ?></p>
							</td>            
							<td class="gmw-form-usage">
								<p><code>[<?php echo $scpx; ?> search_results="<?php echo esc_attr( $this->form['ID'] ); ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[ ' . $scpx .' search_results="' . esc_attr( $this->form['ID'] ) . '"]\' ); &#63;&#62;'; ?></code></p>
							</td>
						</tr>
						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php _e( 'Display the results map anywhere on a page. By default, the form you create will display the map above the list of results, but using this shortcode you can display the map anywhere else on the page. Notice that you need to set the "Display map" setting of the "Form Submission" tab to "Using shortcode". ', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[<?php echo $scpx; ?> map="<?php echo esc_attr( $this->form['ID'] ); ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[ ' . $scpx .' map="' . esc_attr( $this->form['ID'] ) . '"]\' ); &#63;&#62;'; ?></code></p>
							</td>    
						</tr>

						<?php if ( 'ajax_forms' !== $this->form['addon'] ) { ?>
							<tr>
								<td class="gmw-form-usage-desc">
									<p><?php _e( 'Display the search results of any form.', 'geo-my-wp' ); ?></p>
								</td>
								<td class="gmw-form-usage">
									<p><code>[<?php echo $scpx; ?> form="results"]</code></p>
								</td>
								<td class="gmw-form-usage">
									<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[gmw form="results"]\' ); &#63;&#62;'; ?></code></p>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function premium_settings_tab() {
		?>
		<!-- form usage -->
		<div class="gmw-settings-panel gmw-tab-panel premium-settings">
			<div id="premium-features-tab-inner">

				<h2>GEO my WP Premium Form Features</h2>

                <div style="margin-top: 20px;display: inline-block;">
                    <div class="addon-wrapper" style="border-right: 1px solid #ddd;">
                        <p class="desc">Want to extend your forms with premium features like keywords search, custom fields, custom map icons, AJAX-powered info-windows, Map Styles, and more?</p>

                        <div class="addon-inner">
                            <a href="https://geomywp.com/extensions/premium-settings/" target="_blank">
                                <img src="https://geomywp.com/wp-content/uploads/addons-images/premium_settings.png">
                                <h3 class="title">Premium Settings</h3>
                            </a>
                        </div>

                        <p class="desc">Checkout the <a href="https://geomywp.com/extensions/premium-settings/" target="_blank">Premium Settings extension</a> page to learn about the premium features it provides.</p>
                    </div>

                    <div class="addon-wrapper">

                        <p class="desc">Would you like to build AJAX powered search forms and provide a smoother experience for your users?</p>

                        <div class="addon-inner">
                            <a href="https://geomywp.com/extensions/ajax-forms/" target="_blank">
                                <img src="https://geomywp.com/wp-content/uploads/addons-images/gmw_ajax_forms.png">        
                                <h3 class="title">AJAX Forms</h2>
                            </a>
                        </div>

                        <p class="desc">With the <a href="https://geomywp.com/extensions/ajax-forms/" target="_blank">AJAX Forms extension</a> you can use GEO my WP's forms builder to create AJAX powered forms. Forms are submitted and the results displayed dynamically, without reloading the page. Providing smoother experience for the users of your site.</p>
                    </div>

                    <div id="support-note"><p class="desc" style="color: green;">Remember that by purchasing GEO my WP's extensions you also support the developer of GEO my WP and the future development of GEO my WP plugin and the extensions.</p></div>

                    <div id="all-extensions-link"><a href="https://geomywp.com/extensions/" target="_blank"><h2>See all extensions</h2></a></div>
                </div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get form fields
	 *
	 * @param  [type] $option  [description]
	 * @param  [type] $tab     [description]
	 * @param  [type] $section [description]
	 * @param  [type] $form    [description]
	 * @return [type]          [description]
	 */
	public function get_form_field( $option, $tab, $section, $form ) {

		$option['name'] = esc_attr( $option['name'] );
		$attr_id        = esc_attr( 'setting-' . $tab . '-' . $option['name'] );
		$placeholder    = ! empty( $option['placeholder'] ) ? 'placeholder="' . esc_attr( $option['placeholder'] ) . '"' : '';
		$attr_name      = esc_attr( 'gmw_form[' . $tab . '][' . $option['name'] . ']' );
		$value          = ! empty( $this->form[ $tab ][ $option['name'] ] ) ? $this->form[ $tab ][ $option['name'] ] : $option['default'];
		$attributes     = array();

		if ( ! isset( $option['type'] ) ) {
			$option['type'] = 'text';
		}

		//attributes
		if ( ! empty( $option['attributes'] ) && is_array( $option['attributes'] ) ) {
			foreach ( $option['attributes'] as $attribute_name => $attribute_value ) {
				$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		//display settings fields
		switch ( $option['type'] ) {

			// custom function
			case 'function':

				$function = ! empty( $option['function'] ) ? $option['function'] : $option['name'];
				//$this_value = ! empty( $this->form[$tab][$sec] ) ? $this->form[$tab][$sec] : array();

				do_action( 'gmw_' . $this->form['slug'] . '_form_settings_' . $function, $value, $attr_name, $this->form, $this->form_fields, $tab, $option );
				do_action( 'gmw_' . $this->form['addon'] . '_addon_form_settings_' . $function, $value, $attr_name, $this->form, $this->form_fields, $tab, $option );
				do_action( 'gmw_form_settings_' . $function, $value, $attr_name, $this->form, $this->form_fields, $tab, $option );

				break;

			// checkbox
			case 'checkbox':
				?>
				<label>
					<input 
						type="checkbox" 
						id="<?php echo $attr_id; ?>"
						class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox" 
						name="<?php echo $attr_name; ?>"
						value="1" <?php echo implode( ' ', $attributes ); ?>
						<?php checked( '1', $value ); ?> 
					/> 
					<?php echo esc_html( $option['cb_label'] ); ?>
				</label>
				<?php
				break;

			// multi checkbox
			case 'multicheckbox':
				$option['default'] = is_array( $option['default'] ) ? $option['default'] : array();

				$value = ( ! empty( $value ) && is_array( $value ) ) ? $value : $option['default'];

				foreach ( $option['options'] as $v => $l ) {

					$checked = in_array( $v, $value ) ? 'checked="checked"' : '';
					?>

					<label>
						<input 
							id="<?php echo $attr_id . '-' . esc_attr( $v ); ?>" 
							class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox multicheckboxvalues" 
							name="<?php echo $attr_name . '[]'; ?>" 
							type="checkbox" value="<?php echo esc_attr( $v ); ?>" 
							<?php echo $checked; ?> />
							<?php echo esc_html( $l ); ?>
					</label>

					<?php
				}

				break;

			case 'multicheckboxvalues':
				$option['default'] = is_array( $option['default'] ) ? $option['default'] : array();

				$value = ( ! empty( $value ) && is_array( $value ) ) ? $value : $option['default'];

				foreach ( $option['options'] as $keyVal => $name ) {

					$checked = in_array( $keyVal, $value ) ? 'checked="checked"' : '';
					?>
					<label>
						<input 
							type="checkbox" 
							id="<?php echo $attr_id . '-' . esc_attr( $keyVal ); ?>"
							class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox multicheckboxvalues" 
							name="<?php echo $attr_name . '[]'; ?>" 
							value="<?php echo sanitize_title( $keyVal ); ?>"
							<?php echo $checked; ?>
						/> 
						<?php echo esc_html( $name ); ?>
					</label>
					<?php
				}
				break;

			// textarea field
			case 'textarea':
				?>
				<textarea 
					id="<?php echo $attr_id; ?>"
					class="setting-<?php echo $option['name']; ?> textarea large-text" 
					name="<?php echo $attr_name; ?>"
					<?php echo implode( ' ', $attributes ); ?> 
					<?php echo $placeholder; ?>>
					<?php echo esc_textarea( $value ); ?>
				</textarea>
				<?php
				break;

			// radio bttons
			case 'radio':
				?>
				<div class="setting-radio-buttons-wrapper <?php echo esc_attr( $option['name'] ); ?>">
				<?php
				foreach ( $option['options'] as $keyVal => $name ) {
					?>
					<label>
						<input 
							type="radio" 
							id="<?php echo $attr_id; ?>"
							class="setting-<?php echo esc_attr( $option['name'] ); ?> <?php echo esc_attr( $keyVal ); ?> radio"
							name="<?php echo $attr_name; ?>"
							value="<?php echo esc_attr( $keyVal ); ?>"
							<?php checked( $value, $keyVal ); ?>
						/>
						<?php
							$allwed = array(
								'a'   => array(
									'href'  => array(),
									'title' => array(),
								),
								'img' => array(
									'src' => array(),
								),
							);
						?>
						<?php echo wp_kses( $name, $allwed ); ?>
					</label>                              	
					<?php
				}
				echo '</div>';
				break;

			// select fields
			case 'select':
				?>
				<select 
					id="<?php echo $attr_id; ?>"
					class="setting-<?php echo esc_attr( $option['name'] ); ?> select" 
					name="<?php echo $attr_name; ?>"

					<?php if ( ! empty( $option['placeholder'] ) ) { ?>
					data-placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
					<?php } ?>

					<?php echo implode( ' ', $attributes ); ?>
				>
					<?php foreach ( $option['options'] as $keyVal => $name ) { ?>
						<?php echo '<option value="' . esc_attr( $keyVal ) . '" ' . selected( $value, $keyVal, false ) . '>' . esc_html( $name ) . '</option>'; ?>
					<?php } ?>
				</select>
				<?php
				break;

			case 'multiselect':
				?>
				<select 
					id="<?php echo $attr_id; ?>"
					multiple 
					class="setting-<?php echo esc_attr( $option['name'] ); ?> multiselect"
					name="<?php echo $attr_name; ?>[]"

					<?php if ( ! empty( $option['placeholder'] ) ) { ?>
					data-placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
					<?php } ?>

					<?php echo implode( ' ', $attributes ); ?>
				>
					<?php
					foreach ( $option['options'] as $keyVal => $name ) {
						$selected = ( is_array( $value ) && in_array( $keyVal, $value ) ) ? 'selected="selected"' : '';
						echo '<option value="' . esc_attr( $keyVal ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
					}
					?>
				</select>
				<?php

				break;

			// password
			case 'password':
				?>
				<input 
					type="password" 
					id="<?php echo $attr_id; ?>"
					class="setting-<?php echo esc_attr( $option['name'] ); ?> regular-text password" 
					name="<?php echo $attr_name; ?>"
					value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>" 
					<?php echo implode( ' ', $attributes ); ?> 
					<?php echo $placeholder; ?> 
				/>
				<?php
				break;

			// hidden
			case 'hidden':
				?>
				<input 
					type="hidden" 
					id="<?php echo $attr_id; ?>"
					class="setting-<?php echo esc_attr( $option['name'] ); ?> regular-text hidden" 
					name="<?php echo $attr_name; ?>"
					value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>" 
					<?php echo implode( ' ', $attributes ); ?> 
				/>
				<?php
				break;

			// number
			case 'number':
				?>
				<input 
					type="number" 
					id="<?php echo $attr_id; ?>"
					class="setting-<?php echo esc_attr( $option['name'] ); ?> regular-text" 
					name="<?php echo $attr_name; ?>"
					value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>" 
					<?php echo implode( ' ', $attributes ); ?> 
				/>
				<?php
				break;

			//text field
			case '':
			case 'input':
			case 'text':
			default:
				?>
				<input 
					type="text" 
					id="<?php echo $attr_id; ?>"
					class="setting-<?php echo esc_attr( $option['name'] ); ?> regular-text text" 
					name="<?php echo $attr_name; ?>"
					value="<?php echo esc_attr( $value ); ?>" 
					<?php echo implode( ' ', $attributes ); ?> 
					<?php echo $placeholder; ?> 
				/>
				<?php
				break;
		}
	}

	/**
	 * output edit form page.
	 *
	 * @access public
	 * @return void
	 */
	public function output() {

		// apply to all forms
		add_action( 'gmw_form_settings_address_field', array( 'GMW_Form_Settings_Helper', 'address_field' ), 10, 2 );
		add_action( 'gmw_form_settings_image', array( 'GMW_Form_Settings_Helper', 'image' ), 10, 2 );

		//get form fields
		$this->init_form_settings();
		?>

		<div id="gmw-edit-form-page" class="wrap gmw-admin-page">

			<div id="form-wrapper">
		        
		        <form method="post" action="" class="gmw-edit-form" data-ajax_enabled="<?php echo esc_attr( $this->ajax_enabled ); ?>" data-nonce="<?php echo wp_create_nonce( 'gmw_edit_form_nonce' ); ?>">
					
		        	<?php wp_nonce_field( 'gmw_edit_form_nonce', 'gmw_edit_form_nonce' ); ?> 

		        	<input type="hidden" name="gmw_action" value="update_admin_form" />
		           	
		           	<div id="top-area">

			    		<a class="form-editor-close" 
		    			   title="<?php _e( 'Return to list of forms', 'geo-my-wp' ); ?>" 
		    			   href="admin.php?page=gmw-forms"></a>
						
						<h2 class="gmw-wrap-top-h2">
			                <i class="gmw-icon-pencil"></i>
			                <?php echo __( 'Edit Form', 'geo-my-wp' ) .' ' . $this->form['ID'] .' <em style="font-size: 13px;font-weight: 100">( '. $this->form['name'] . ' )</em> '; ?>
			            </h2>

		        		<div id="action-buttons">
			        		
			        		<span id="form-title-input">
			        			<span id="form-name-label"><?php _e( 'Name', 'geo-my-wp' ); ?></span>
					        	<input type="text" name="gmw_form[title]" value="<?php echo ( ! empty( $this->form['title']) ) ? sanitize_text_field( esc_attr( $this->form['title'] ) ) : 'form_id_'. sanitize_text_field( esc_attr( $this->form['ID'] ) ); ?>" placeholder="Form title" />
			            	</span>

			        		<?php $delete_message = __( 'This action cannot be undone. Would you like to proceed?', 'geo-my-wp' ) ; ?>

							<!-- Delete Form button -->	
			                <a class="button action delete-form" title="<?php _e( 'Delete form', 'geo-my-wp' ); ?>" href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=delete_form&form_id='.$this->form['ID'] ); ?>" onclick="return confirm( '<?php echo $delete_message; ?>' );"><?php _e( 'Delete Form', 'geo-my-wp' ); ?></a>

		                	<a class="button action" title="<?php _e( 'Duplicate form', 'geo-my-wp' ); ?>" href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=duplicate_form&slug='.$this->form['slug'].'&form_id='.$this->form['ID'] ); ?>"><?php _e( 'Duplicate Form', 'geo-my-wp' ); ?></a>                           
		                	
		                	<input type="submit" id="submit-button" class="button-primary" value="<?php _e( 'Save Changes', 'geo-my-wp' ); ?>" />

				            <!-- Update status message -->
							<div id="form-update-messages">
								<p class="success"><i class="gmw-icon-ok-light"></i><?php _e( 'Form updated!', 'geo-my-wp' ); ?></p>
								<p class="failed"><i class="gmw-icon-cancel"></i><?php _e( 'Form update failed!', 'geo-my-wp' ); ?></p>
							</div>
						</div>
			    	</div>
		    	
					<div id="left-sidebar">
		            				
			            <ul class="gmw-tabs-wrapper gmw-edit-form-page-nav-tabs">
			            	<?php
			                foreach ( $this->form_settings_groups as $group ) {
			                	
			                	if ( isset( $group['id'] ) ) {
			                		$group['slug'] = $group['id'];
			                	}

			                	// verify that there are settings for this tab
			                	if ( empty( $this->form_fields[$group['slug']] ) ) {
			                		continue;
			                	}

			                	if ( $group['slug'] != 'hidden' ) { ?>
			                		<li>
			                    		<a 
			                    			href="#settings-<?php echo sanitize_title( $group['slug'] ); ?>" 
			                    			id="<?php echo esc_attr( $group['slug'] ); ?>" 
			                    			class="gmw-nav-tab gmw-nav-trigger" 
			                    			data-name="<?php echo sanitize_title( $group['slug'] ); ?>"
			                    		>
		                            	<span><?php echo esc_attr( $group['label'] ); ?></span>
		                            	</a>
		                            </li>
			                	<?php }
			                }
			                ?>  
			                <li>
			                	<a href="#" id="form-usage" class="gmw-nav-tab" data-name="form-usage"><?php _e( 'Form Usage', 'geo-my-wp' ); ?></a>
			                </li>
							
							<?php if ( ! gmw_is_addon_active( 'premium_settings' ) ) { ?>
				                <li>
				                	<a href="#" id="premium-settings" class="gmw-nav-tab" data-name="premium-settings"><?php _e( 'Premium Settings', 'geo-my-wp' ); ?></a>
				                </li>
				            <?php } ?>

			            </ul>
			        </div>

		           	<!-- tabs content -->
		           	<div class="panels-wrapper">      
			            
						<div id="gmw-form-cover">
							<div id="updating-info">
								<i class="gmw-icon-spin-3 animate-spin"></i>
								<span><?php _e( 'Updating form...', 'geo-my-wp' ); ?></span>
							</div>
						</div>
	            
		            	<input type="hidden" name="gmw_form[ID]" value="<?php echo absint( $this->form['ID'] ); ?>" />	
		            	<input type="hidden" name="gmw_form[slug]" value="<?php echo sanitize_text_field( esc_attr( $this->form['slug'] ) ); ?>" />
		            	<input type="hidden" name="gmw_form[addon]" value="<?php echo sanitize_text_field( esc_attr( $this->form['addon'] ) ); ?>" />
		            	<input type="hidden" name="gmw_form[component]" value="<?php echo sanitize_text_field( esc_attr( $this->form['component'] ) ); ?>" />

		            	<?php
		            	//form filds
			        	foreach ( $this->form_fields as $tab => $section ) {      

			        		if ( ! array_filter( $section ) || ! is_array( $section ) ) {
			        			continue; 
			        		}
			        		//sort fields by priority
			        		uasort( $section, 'gmw_sort_by_priority' );
			        		?>
			                <div id="settings-<?php echo $tab; ?>" class="gmw-settings-panel gmw-tab-panel <?php echo $tab; ?> <?php echo esc_attr( $this->form['component'] ) . ' ' . esc_attr( $this->form['slug'] ). ' ' . esc_attr( $this->form['addon'] ); ?>">
			                    <table class="widefat">
									<tbody>
									<?php 
									do_action( 'form_editor_tab_start', $tab, $section, $this->form['ID'], $this->form );
									
				                    foreach ( $section as $sec => $option ) {          
				                        ?>
				                        <tr 
				                        	valign="top" 
				                        	id="<?php echo esc_attr( $tab ); ?>-<?php echo esc_attr( $option['name'] ); ?>-tr" 
				                        	class="gmw-item-sort gmw-form-field-wrapper <?php echo ! empty( $option['class'] ) ? esc_attr( $option['class'] ) : ''; ?> <?php echo esc_attr( $tab ); ?> <?php echo ! empty( $option['type'] ) ? esc_attr($option['type'] ) : ''; ?>"
				                        >
				                        	<td class="gmw-form-feature-desc">      		
				                        		<?php if ( isset( $option['label'] ) ) { ?>
					                        		<label for="setting-<?php echo esc_attr( $option['name'] ); ?>">
					                        			<?php echo esc_html( $option['label'] ); ?>	
					                        		</label>	              	
			                            		<?php } ?>

			                            		<?php if ( isset( $option['desc'] ) ) { ?>
				                            		<div class="gmw-form-feature-desc-content">	
				                            			<em class="description">
				                            				<?php
																echo wp_kses(
																	$option['desc'],
																	array(
																		'a' => array(
																			'href'   => array(),
																			'title'  => array(),
																			'target' => array(),
																		),
																		'code' => array(),
																	)
																);
															?>
				                            			</em>
				                            		</div>
				                            	<?php } ?>
			                            	</td>

				                        	<td class="gmw-form-feature-settings <?php echo ! empty( $option['type'] ) ? esc_attr($option['type'] ) : ''; ?>">	
				                        		<?php if ( $option['type'] == 'fields_group' && array_filter( $option['fields'] ) ) { ?>
				                        				
				                        			<?php $ob_class = ! empty( $option['optionsbox'] ) ? 'gmw-options-box' : ''; ?>

				                        			<div class="<?php echo $ob_class; ?> <?php if ( isset( $option['name'] ) ) echo 'fields-group-'.esc_attr( $option['name'] ); ?>">					                        			
						                        			<?php foreach ( $option['fields'] as $option ) { ?>

						                        				<div class="single-option option-<?php echo esc_attr( $option['name'] );?> <?php echo esc_attr( $option['type'] ); ?>">
							                        				<?php /*if ( $option['type'] == 'checkbox' ) { ?>

										                        		<?php $this->get_form_field( $option, $tab, $section, $this->form ); ?>
										                        				
								                        				<?php if ( ! empty( $option['desc'] ) ) { ?>
										                            		<p class="description"><?php echo $option['desc']; ?></p>
										                            	<?php } ?>

							                        				<?php } else { */ ?>
	                 					
								                        				<?php if ( ! empty( $option['label'] ) ) { ?>
											                        		<label for="setting-<?php echo esc_attr( $option['name'] ); ?>">
											                        			<?php echo esc_html( $option['label'] ); ?>	
											                        		</label>	              	
									                            		<?php } ?>
				 														
				 														<div class="option-content">
									                        				<?php $this->get_form_field( $option, $tab, $section, $this->form ); ?>
									                        				
									                        				<?php if ( isset( $option['desc'] ) ) { ?>
											                            		<p class="description">
											                            			<?php
																						echo wp_kses(
																							$option['desc'],
																							array(
																								'a' => array(
																									'href'   => array(),
																									'title'  => array(),
																									'target' => array(),
																								),
																								'code' => array(),
																							)
																						);
																					?>
											                            		</p>
											                            	<?php } ?>
											                           	</div>
										                           	<?php //} ?>
						                        				</div>
						                        			<?php }	?>
						                        		</div>
					                        		</div>

				                        		<?php } else { 
				                        			$this->get_form_field( $option, $tab, $section, $this->form );
				                        		}
				                        		?>
			                           		</td>			                           
			                        	</tr>
			                        <?php } ?> 
			                        <?php do_action( 'form_editor_tab_end', $tab, $section, $this->form['ID'], $this->form ); ?>	
									</tbody>
			                	</table>
			                </div>
			            <?php } ?>

			           	<?php $this->form_usage(); ?>
				
			           	<?php 
			           		if ( ! gmw_is_addon_active( 'premium_settings' ) ) {
			           			$this->premium_settings_tab(); 
			           		}
			           	?>
					</div>
		        </form>
	        </div>

	        <?php 
	        if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
	            wp_enqueue_script( 'select2' );
	            wp_enqueue_style( 'select2' );
	        }
	        ?>
	    </div>   
	    <script>

	    jQuery( document ).ready( function( $ ) {

	    	// save form using control+s keys
	    	$( document ).on( 'keydown', function( e ){
	    		
	    		if ( ( e.controlKey || e.metaKey ) && ( e.which == 83 ) ) {
					
					e.preventDefault();
					
				
					$( '#gmw-edit-form-page' ).find( '#submit-button' ).click();

					return false
				}
	    	});

	    	// prevent scroll of body when form editor open
	    	$( 'body, html' ).css( 'overflow', 'hidden' ); 

	    	// submit form when enter key presses in form title input box
	    	$( '#gmw-form-editor-wrapper ul.gmw-tabs-wrapper.left-tabs li a' ).click( function( e ) {
    			// duplicate value from title field into a hidden field in the form
    			$( '#tab-title-holder' ).html( $( this ).find( 'span' ).html() );
	    	});
	    });
	    </script>         
    <?php       
    }

	/**
	 * Validate single field
	 * @param  [type] $value  [description]
	 * @param  [type] $option [description]
	 * @param  [type] $form   [description]
	 * @return [type]         [description]
	 */
	public function validate_field( $value, $option, $form ) {

		switch ( $option['type'] ) {

			// custom functions validation
			case 'function':
				//save custom settings value as is. without validation
				if ( ! empty( $value ) ) {
					$valid_value = $value;
				} else {
					$value = $valid_value = '';
				}

				//use this filter to validate custom settigns
				$function = ! empty( $option['function'] ) ? $option['function'] : $option['name'];

				$valid_value = apply_filters( 'gmw_' . $form['slug'] . '_validate_form_settings_' . $function, $valid_value, $form );
				$valid_value = apply_filters( 'gmw_' . $this->form['addon'] . '_addon_validate_form_settings_' . $function, $valid_value, $form );
				$valid_value = apply_filters( 'gmw_validate_form_settings_' . $function, $valid_value, $form );

				break;

			// checkbox
			case 'checkbox':
				$valid_value = ! empty( $value ) ? 1 : '';
				break;

			// multi checbox
			case 'multicheckbox':
				if ( empty( $value ) || ! is_array( $value ) ) {
					$valid_value = is_array( $option['default'] ) ? $option['default'] : array();
				} else {
					foreach ( $option['options'] as $v => $l ) {
						if ( in_array( $v, $value ) ) {
							$valid_value[] = $v;
						}
					}
				}

				break;

			case 'multicheckboxvalues':
				if ( empty( $value ) || ! is_array( $value ) ) {
					$valid_value = is_array( $option['default'] ) ? $option['default'] : array();
				} else {

					$valid_value = array();

					foreach ( $option['options'] as $keyVal => $name ) {

						if ( in_array( $keyVal, $value ) ) {

							$valid_value[] = $keyVal;
						}
					}
				}
				break;

			case 'multiselect':
				if ( empty( $value ) || ! is_array( $value ) ) {
					$valid_value = is_array( $option['default'] ) ? $option['default'] : array();
				} else {

					$valid_value = array();

					foreach ( $option['options'] as $keyVal => $name ) {
						if ( in_array( $keyVal, $value ) ) {
							$valid_value[] = $keyVal;
						}
					}
				}
				break;

			// select and radio buttons
			case 'select':
			case 'radio':
				if ( ! empty( $value ) && in_array( $value, array_keys( $option['options'] ) ) ) {
					$valid_value = $value;
				} else {
					$valid_value = ! empty( $option['default'] ) ? $option['default'] : '';
				}
				break;

			// textarea
			case 'textarea':
				if ( ! empty( $value ) ) {
					$valid_value = sanitize_textarea_field( $value );
				} else {
					$valid_value = ! empty( $option['default'] ) ? sanitize_textarea_field( $option['default'] ) : '';
				}
				break;

			case 'number':
				if ( ! empty( $value ) ) {
					$num_value = $value;
				} else {
					$num_value = isset( $option['default'] ) ? $option['default'] : '';
				}
				$valid_value = preg_replace( '/[^0-9]/', '', $num_value );
				break;

			// text field
			case "''":
			case 'text':
			case 'hidden';
			case 'password':
				if ( ! empty( $value ) ) {
					$this_value = $value;
				} else {
					$this_value = ! empty( $option['default'] ) ? $option['default'] : '';
				}
				$valid_value = sanitize_text_field( $this_value );
				break;
		}

		return $valid_value;
	}

	/**
	 * Validate form input fields
	 *
	 * @param  array $values Form values after form submission
	 *
	 * @return array validated/sanitized values
	 */
	public function validate( $values ) {

		// hooks for custom validations
		add_filter( 'gmw_validate_form_settings_address_field', array( 'GMW_Form_Settings_Helper', 'validate_address_field' ) );
		add_filter( 'gmw_validate_form_settings_image', array( 'GMW_Form_Settings_Helper', 'validate_image' ) );

		//get the current form being updated
		$this->form = GMW_Forms_Helper::get_form( $values['ID'] );

		$valid_input = array();

		// get basic form values
		$valid_input['ID']    = absint( $values['ID'] );
		$valid_input['title'] = sanitize_text_field( $values['title'] );
		$valid_input['slug']  = sanitize_text_field( $values['slug'] );

		// get form fields
		$this->init_form_settings();

		// loop through and validate fields
		foreach ( $this->form_fields as $section_name => $section ) {

			foreach ( $section as $sec => $option ) {

				if ( is_array( $section ) && ! array_filter( $section ) ) {
					continue;
				}

				$option['type'] = ! empty( $option['type'] ) ? $option['type'] : 'text';

				if ( 'fields_group' === $option['type'] && array_filter( $option['fields'] ) ) {

					foreach ( $option['fields'] as $option ) {

						if ( empty( $values[ $section_name ][ $option['name'] ] ) ) {
							$values[ $section_name ][ $option['name'] ] = '';
						}

						$valid_input[ $section_name ][ $option['name'] ] = $this->validate_field( $values[ $section_name ][ $option['name'] ], $option, $this->form );
					}

				} else {

					if ( empty( $values[ $section_name ][ $option['name'] ] ) ) {
						$values[ $section_name ][ $option['name'] ] = '';
					}

					$valid_input[ $section_name ][ $option['name'] ] = $this->validate_field( $values[ $section_name ][ $option['name'] ], $option, $this->form );
				}
			}
		}

		$valid_input = apply_filters( 'gmw_validated_form_settings', $valid_input, $this->form );

		//return formds
		return $valid_input;
	}

	/**
	 * Update form via AJAX
	 *
	 * Run the form values through validations and update the form in database
	 *
	 * @return void
	 */
	public function ajax_update_form() {

		// verify nonce
		check_ajax_referer( 'gmw_edit_form_nonce', 'security', true );

		// get the submitted values
		parse_str( $_POST['form_values'], $form_values );

		$form = $form_values['gmw_form'];

		// validate the values
		$valid_input = self::validate( $form );

		global $wpdb;

		$form_id = $valid_input['ID'];
		unset( $valid_input['ID'] );

		$title = $valid_input['title'];
		unset( $valid_input['title'] );
		unset( $valid_input['slug'] );

		// update form in database
		if ( $wpdb->update(

			$wpdb->prefix . 'gmw_forms',
			array(
				'data'  => serialize( $valid_input ),
				'title' => $title,
			),
			array( 'ID' => $form_id ),
			array(
				'%s',
				'%s',
			),
			array( '%d' )
		) === false ) {

			wp_die(
				__( 'Failed saving data in database.', 'geo-my-wp' ),
				__( 'Error', 'geo-my-wp' ),
				array( 'response' => 403 )
			);

		} else {

			// delete form from cache. We are updating it with new data.
			wp_cache_delete( 'all_forms', 'gmw_forms' );
			wp_cache_delete( $form['ID'], 'gmw_forms' );

			wp_send_json( $valid_input );
		}
	}

	/**
	 * Update form via page load
	 *
	 * Run the form values through validations and update the form in database
	 *
	 * @return void
	 */
	public function update_form() {

		// run a quick security check.
		if ( empty( $_POST['gmw_edit_form_nonce'] ) || ! check_admin_referer( 'gmw_edit_form_nonce', 'gmw_edit_form_nonce' ) ) {
			wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
		}

		// validate the values.
		$valid_input = self::validate( $_POST['gmw_form'] );

		global $wpdb;

		// update form in database.
		if ( $wpdb->update(

			$wpdb->prefix . 'gmw_forms',
			array(
				'data'  => maybe_serialize( $valid_input ),
				'title' => $valid_input['title'],
			),
			array( 'ID' => $valid_input['ID'] ),
			array(
				'%s',
				'%s',
			),
			array( '%d' )
		) === false ) {

			// update forms in cache.
			GMW_Forms_Helper::update_forms_cache();

			wp_safe_redirect(
				add_query_arg(
					array(
						'gmw_notice'        => 'form_not_update',
						'gmw_notice_status' => 'error',
					)
				)
			);

		} else {

			// update forms in cache.
			GMW_Forms_Helper::update_forms_cache();

			wp_safe_redirect(
				add_query_arg(
					array(
						'gmw_notice'        => 'form_updated',
						'gmw_notice_status' => 'updated',
					)
				)
			);
		};

		exit;
	}
}
