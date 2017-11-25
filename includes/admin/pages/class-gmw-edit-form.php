<?php

/**
 * GMW_Edit_Form calss
 * 
 * Edit GMW forms in the back end
 * 
 * @since 2.5
 * @author FitoussiEyal
 *
 */
class GMW_Edit_Form {
	
	public $ajax_enabled = false;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		
		// trigger ajax form update
		if ( apply_filters( 'gmw_form_editor_ajax_enabled', true ) ) {

			$this->ajax_enabled = true;

			add_action( 'wp_ajax_gmw_update_admin_form', array( $this, 'ajax_update_form' ) );
		}

		// verify that this is Form edit page
		if ( empty( $_GET['page'] ) || $_GET['page'] != 'gmw-forms' || empty( $_GET['gmw_action'] ) || $_GET['gmw_action'] != 'edit_form' ) {
			return;
		}

		if ( ! $this->ajax_enabled ) {
			add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
			add_action( 'gmw_update_admin_form',	  array( $this, 'update_form' 	   ) );
		}

		// make sure form ID passed
		if ( empty( $_GET['form_id'] ) || ! absint( $_GET['form_id'] ) ) {
			wp_die( __( 'No form ID provided.', 'GMW' ) );
		}

		// get form data
		$this->form = GMW_Helper::get_form( $_GET['form_id'] );

		// varify if the form exists
		if ( empty( $this->form ) ) {
			wp_die( __( 'The form you are trying to edit doe\'s not exist!', 'GMW' ) );
		}
	}

	/**
     * GMW Function - add notice messages
     *
     * @access public
     * @since 3.0
     * @author Eyal Fitoussi
     *
     */
    public function notices_messages( $messages ) {
    
    	$messages['form_updated']     = __( 'Form successfully updated.', 'GMW' );
    	$messages['form_not_updated'] = __( 'There was an error while trying to update the form.', 'GMW' );
    	
    	return $messages;
    }
	
	/**
	 * Form Fields
	 *
	 * @access protected
	 * 
	 * @return void
	 */
	protected function init_form_settings() {

		// settings groups
		$this->form_settings_groups = apply_filters( 'gmw_form_settings_groups', array(
			array( 
                'id'    	=> 'hidden',
                'label' 	=> __( 'hidden', 'GMW' ),
                'priority'  => 1
            ),
            array( 
                'id'    	=> 'page_load_results',
                'label' 	=> __( 'Page Load Results', 'GMW' ),
                'priority'  => 5
            ),
            array( 
            	'id'		=> 'search_form',
				'label'		=> __( 'Search Form', 'GMW' ),
				'priority'	=> 10
        	),
        	array( 
            	'id'		=> 'form_submission',
				'label'		=> __( 'Form Submission', 'GMW' ),
				'priority'	=> 15
        	),
        	array( 
            	'id'		=> 'search_results',
				'label'		=> __( 'Search Results', 'GMW' ),
				'priority'	=> 20
        	),
        	array( 
            	'id'		=> 'results_map',
				'label'		=> __( 'Map', 'GMW' ),
				'priority'	=> 25
        	),
        ) );

		// settings fields
		$this->form_fields = array(
			'hidden' => array(
				array(),
			),
			'page_load_results'	=> array(

				'all_locations'  => array(
					'name'  		=> 'all_locations',
					'type'     		=> 'checkbox',
					'default'   	=> '',
					'label' 		=> __( 'Enable Page Load features', 'GMW' ),
					'desc'  		=> __( "By checking this checkbox GEO my WP will automatically display all existing locations on the initial load of the form. You can define extra filters for this initial results below.", 'GMW' ),
					'cb_label' 		=> __( 'Enable', 'GMW' ),
					'attributes'  	=> '',
					'priority'		=> 5
				),
				'user_location'	 => array(
					'name'     		=> 'user_location',
					'type'     		=> 'checkbox',
					'default'      	=> '',
					'label'    		=> __( "User's current location based results", "GMW" ),
					'desc'     		=> __( "GEO my WP will first check for the user's current location; If exists the locations will be displayed based on that. Note that an address entered below wont take into account if the user's current locaiton exists.", 'GMW' ),
					'cb_label' 		=> __( 'Enable', 'GMW' ),
					'attributes'  	=> '',
					'priority'		=> 10
				),
				'address_filter' => array(
					'name'        	=> 'address_filter',
					'type'			=> 'text',
					'default'       => '',
					'placeholder' 	=> __( 'Enter address', 'GMW' ),
					'label'       	=> __( 'Starting address', 'GMW' ),
					'desc'        	=> __( "Set the address for the initial search results. GEO my WP will search for locations near the address entered withing the radius you set below. Leave empty if you want to search all locations.", 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 15
				),
				'radius'         => array(
					'name'        	=> 'radius',
					'type'			=> 'text',
					'default'       => '',
					'placeholder' 	=> __( 'Radius value', 'GMW' ),
					'label'       	=> __( 'Radius / Distance', 'GMW' ),
					'desc'        	=> __( "Set the radius to be used when searching based on the address enterd above or when searching based on the user's current location.", 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 20
				),
				'units'           => array(
					'name'    		=> 'units',
					'type'    		=> 'select',
					'default'       => 'imperial',
					'label'   		=> __( 'Units', 'GMW' ),
					'desc'    		=> __( 'Set the units to be used.', 'GMW' ),
					'options' 		=> array(
						'imperial' 	=> __( 'Miles', 'GMW' ),
						'metric'   	=> __( 'Kilometers', 'GMW' )
					),
					'attributes'  	=> array( 'style' => 'width:220px' ),
					'priority'		=> 25
				),
				'city_filter' 	=> array(
					'name'        	=> 'city_filter',
					'type'			=> 'text',
					'default'       => '',
					'placeholder' 	=> __( 'City', 'GMW'),
					'label'       	=> __( 'Filter by city', 'GMW' ),
					'desc'        	=> __( 'Filter locations by city name. Note that by using this filter GEO my WP will not do a proximity search but will pull locations with the matching city name ( Leave blank for no filter ).', 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 30
				),
				'state_filter' 	=> array(
					'name'        	=> 'state_filter',
					'type'			=> 'text',
					'default'       => '',
					'placeholder' 	=> __( 'State', 'GMW'),
					'label'       	=> __( 'Filter by state', 'GMW' ),
					'desc'        	=> __( 'Filter locations by state name. Note that by using this filter GEO my WP will not do a proximity search but will pull locations with the matching state name ( Leave blank for no filter ).', 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 35
				),
				'zipcode_filter' => array(
					'name'        	=> 'zipcode_filter',
					'type'			=> 'text',
					'default'       => '',
					'placeholder' 	=> __( 'Zipcode', 'GMW'),
					'label'       	=> __( 'Filter by zipcode', 'GMW' ),
					'desc'        	=> __( 'Filter locations by zipcode. Note that by using this filter GEO my WP will not do a proximity search but will pull locations with the matching zipcode ( Leave blank for no filter ).', 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 40
				),
				'city_country' 	=> array(
					'name'        	=> 'country_filter',
					'type'			=> 'select',
					'default'       => '',
					'placeholder' 	=> __( 'Country', 'GMW'),
					'label'       	=> __( 'Filter by country', 'GMW' ),
					'desc'        	=> __( 'Filter locations by country name. Note that by using this filter GEO my WP will not do a proximity search but will pull locations with the matching country name ( Leave blank for no filter ).', 'GMW' ),
					'options'		=> gmw_get_countries_list_array( 'Disable' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 45
				),
				'display_results'  => array(
					'name'     		=> 'display_results',
					'type'     		=> 'checkbox',
					'default'       => '',
					'label'    		=> __( 'Display list of results', 'GMW' ),
					'desc'     		=> __( 'Display list of results', 'GMW' ),
					'cb_label' 		=> __( 'Enable', 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 55
				),
				'display_map'    => array(
					'name'    		=> 'display_map',
					'type'    		=> 'select',
					'default'       => '',
					'label'   		=> __( 'Display Map', 'GMW' ),
					'desc'    		=> __( "Display results on map: <ul><li><em>No map - Do not display map</li><li><em>In results - display the map above the list of results</em></li><li><em>Using shortcode - display the map anywhere on the page using the shortcode [gmw map=\"form ID\"]</em></li></ul>", 'GMW' ),
					'options' 		=> array(
						''        	=> __( 'Disable map', 'GMW' ),
						'results'   => __( 'Above the list of result', 'GMW' ),
						'shortcode' => __( 'Using shortcode', 'GMW' ),
					),
					'priority'		=> 55
				),
				'per_page'       => array(
					'name'        	=> 'per_page',
					'type'    		=> 'text',
					'default'       => '5,10,15,25',
					'placeholder' 	=> __( 'Enter values', 'GMW' ),
					'label'       	=> __( 'Results Per Page', 'GMW' ),
					'desc'        	=> __( 'Choose the number of results per page. By setting a single value you set the default number of results per page. By giving multiple values, comma separated, a select box will be created and the users will be able to set the number of results per page. You can use the value "-1" to display all results.', 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 60
				)
			),
			'search_form'    => array(

				'form_template'   => array(
					'name'     		=> 'form_template',
					'type'     		=> 'select',
					'default'       => 'gray',
					'label'    		=> __( 'Search Form Template', 'GMW' ),
					'desc'  		=> __( "<p><em>Choose The search form template.</em></p> You can find the search form templates folders in the <code>plugins folder/geo-my-wp/plugin/{$this->form['slug']}/search-forms/</code>. You can modify any of the templates or create your own.", 'GMW' ).
									   __( " If you do modify or create you own template files you should create/save them in your theme's or child theme's folder and the plugin will pull them from there. This way your changes will not be overridden once the plugin is updated.", 'GMW' ).
									   __( " Your custom template folders should be places under <code><strong>themes/your-theme-or-child-theme-folder/geo-my-wp/{$this->form['slug']}/search-form/your-search-form-tempalte-folder</strong></code>.", 'GMW' ).
									   __( " Your template folder must contain the search-form.php file and another folder named \"css\" and the style.css within it.", 'GMW' ),							
					'options'		=> GMW_Helper::get_addon_templates( $this->form['slug'], 'search-forms', array( '-1' => __( ' - Disabled search form - ' ) ) ),
					'attributes' 	=> array(),
					'priority'		=> 5
				),
				'address_field'   => array(
					'name'     		=> 'address_field',
					'type'     		=> 'function',
					'default'       => '',
					'label'    		=> __( 'Address Field', 'GMW' ),
					'cb_label' 		=> '',
					'desc'     		=> __( "<p><em><strong>Label</strong> - Enter the label for the address field ( ex. \"Enter your address\" ).</em></p>", 'GMW' ).
					              __( "<p><em><strong>Label within the input field</strong> - check this checkbox if you'd like to display the label within the address input field. Otherwise it will be displayed next to it.</em></p>", 'GMW' ). 
								  __( "<p><em><strong>Mandatory Field</strong> - Make the address field mandatory which will prevent users from submitting the form if no address entered. Otherwise if you allow the field to be empty and user submit a form without an address GEO my WP will display all results.</em></p>", 'GMW' ).
					              __( "<p><em><strong>Google Address Autocomplete</strong> - check this checkbox to trigger suggested results by Google Places while typing an address.</em></p>", 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 10       
				),
				'locator'    => array(
					'name'    		=> 'locator',
					'type'    		=> 'function',
					'default'       => '',
					'label'   		=> __( 'Auto locator', 'GMW' ),
					'desc'    		=> __( "locator button - once clicked GEO my WP will try to retrive the user's current location.", 'GMW' ).
					             __( "<p><em><strong>Do not use</strong> - No locator button will be displayed.</em></p>", 'GMW' ).
								 __( "<p><em><strong>Within the address field</strong> - display locator icon within the input address field.</em></p>", 'GMW' ).
								 __( "<p><em><strong>Choose icon</strong> - display any of the icons next to the address field.</em></p>", 'GMW' ),
					'attributes'  	=> '',
					'priority'		=> 15
				),
				'radius'          => array(
					'name'        	=> 'radius',
					'type'			=> 'text',
					'default'       => '5,10,15,25,50,100',
					'placeholder' 	=> __( 'Radius values', 'GMW' ),
					'label'       	=> __( 'Radius / Distance', 'GMW' ),
					'desc'        	=> __( "Enter multiple distance values comma separated in the input if you'd like to have a select dropdown menu of multiple radius values in the search form; this way the user can choose the distance when searching. Enter a single value to have a deafult distance value.", 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 20
				),
				'units'       => array(
					'name'    		=> 'units',
					'type'    		=> 'select',
					'default'       => 'both',
					'label'   		=> __( 'Distance Units', 'GMW' ),
					'desc'    		=> __( "Choose between Miles, Kilometers or both as a dropdown menu for the user to choose from.", 'GMW' ),
					'options' 		=> array(
						'both'     => __( 'Both', 'GMW' ),
						'imperial' => __( 'Miles', 'GMW' ),
						'metric'   => __( 'Kilometers', 'GMW' )
					),
					'attributes'  	=> '',
					'priority'		=> 25
				)
			),
			'form_submission' => array(
				
				'results_page'   => array(
					'name'    		=> 'results_page',
					'type'    		=> 'select',
					'default'       => '',
					'label'   		=> __( 'Results Page', 'GMW' ),
					'desc'    		=> __( "The results page will display the search results in the selected page when using the \"GMW Search Form\" widget or when you want to have the search form in one page and the results showing in a different page.", 'GMW' ).
							     	__( "Choose the results page from the dropdown menu and paste the shortcode [gmw form=\"results\"] into that page. To display the search result in the same page as the search form choose \"Same Page\" from the select box.", 'GMW' ),
					'options' 		=> self::get_pages(),
					'attributes'  	=> '',
					'priority'		=> 5
				),
				'per_page'         => array(
					'name'        	=> 'per_page',
					'type'			=> 'text',
					'default'       => '5,10,15,25',
					'placeholder' 	=> __( 'Enter values', 'GMW' ),
					'label'       	=> __( 'Results Per Page', 'GMW' ),
					'desc'        	=> __( 'Choose the number of results per page. By setting a single value you set the default number of results per page. By giving multiple values, comma separated, a select box will be created and the users will be able to set the number of results per page.', 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'attributes' 	=> array(),
					'priority'		=> 10
				),
				'display_results'  => array(
					'name'     		=> 'display_results',
					'type'     		=> 'checkbox',
					'default'       => '',
					'label'    		=> __( 'Display list of results', 'GMW' ),
					'desc'     		=> __( 'Display list of results', 'GMW' ),
					'cb_label' 		=> __( 'Enable', 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 15
				),
				'display_map'      => array(
					'name'    		=> 'display_map',
					'type'    		=> 'select',	
					'default'       => '',
					'label'   		=> __( 'Display Map', 'GMW' ),
					'desc'    		=> __( "Display results on the map: <ul><li><em>No map - Do not display map</li><li><em>In results - display the map above the list of results</em></li><li><em>Using shortcode - display the map anywhere on the page using the shortcode [gmw map=\"form ID\"]</em></li></ul>", 'GMW' ),	
					'options' 		=> array(
						''          => __( 'Disable map', 'GMW' ),
						'results'   => __( 'Above the list of result', 'GMW' ),
						'shortcode' => __( 'Using shortcode', 'GMW' ),
					),
					'attributes' 	=> array(),
					'priority'		=> 20
				)
			),
			'search_results' => array(
				
			/*	'results_page'   => array(
					'name'    		=> 'results_page',
					'type'    		=> 'select',
					'default'       => '',
					'label'   		=> __( 'Results Page', 'GMW' ),
					'desc'    		=> __( "The results page will display the search results in the selected page when using the \"GMW Search Form\" widget or when you want to have the search form in one page and the results showing in a different page.", 'GMW' ).
							     	__( "Choose the results page from the dropdown menu and paste the shortcode [gmw form=\"results\"] into that page. To display the search result in the same page as the search form choose \"Same Page\" from the select box.", 'GMW' ),
					'options' 		=> self::get_pages(),
					'attributes'  	=> '',
					'priority'		=> 5
				),
				'per_page'         => array(
					'name'        	=> 'per_page',
					'type'			=> 'text',
					'default'       => '5,10,15,25',
					'placeholder' 	=> __( 'Enter values', 'GMW' ),
					'label'       	=> __( 'Results Per Page', 'GMW' ),
					'desc'        	=> __( 'Choose the number of results per page. By setting a single value you set the default number of results per page. By giving multiple values, comma separated, a select box will be created and the users will be able to set the number of results per page.', 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'attributes' 	=> array(),
					'priority'		=> 10
				),
				'display_map'      => array(
					'name'    		=> 'display_map',
					'type'    		=> 'select',	
					'default'       => '',
					'label'   		=> __( 'Display Map', 'GMW' ),
					'desc'    		=> __( "Display results on the map: <ul><li><em>No map - Do not display map</li><li><em>In results - display the map above the list of results</em></li><li><em>Using shortcode - display the map anywhere on the page using the shortcode [gmw map=\"form ID\"]</em></li></ul>", 'GMW' ),	
					'options' 		=> array(
						''          => __( 'Disable map', 'GMW' ),
						'results'   => __( 'Above the list of result', 'GMW' ),
						'shortcode' => __( 'Using shortcode', 'GMW' ),
					),
					'attributes' 	=> array(),
					'priority'		=> 15
				),
				'display_results'  => array(
					'name'     		=> 'display_results',
					'type'     		=> 'checkbox',
					'default'       => '',
					'label'    		=> __( 'Display list of results', 'GMW' ),
					'desc'     		=> __( 'Display list of results', 'GMW' ),
					'cb_label' 		=> __( 'Enable', 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 20
				),*/
				'results_template' => array(
					'name'  		=> 'results_template',
					'type'     		=> 'select',
					'default'   		=> 'gray',
					'label' 		=> __( 'Results Template', 'GMW' ),
					'desc'  		=> __( "<p><em>Choose The search resuls template.</em></p> You can find the search results templates folders in the <code>plugins folder/geo-my-wp/plugin/{$this->form['slug']}/search-results/</code>. You can modify any of the templates or create your own.", 'GMW' ).
									   __( " If you do modify or create you own template files you should create/save them in your theme's or child theme's folder and the plugin will pull them from there. This way your changes will not be overridden once the plugin is updated.", 'GMW' ).
									   __( " Your custom template folders should be places in <code><strong>themes/your-theme-or-child-theme-folder/geo-my-wp/{$this->form['slug']}/search-results/your-search-results-template-folder</strong></code>.", 'GMW' ).
									   __( " Your template folder must contain the results.php file and another folder named \"css\" and the style.css within it.", 'GMW' ),
					'options'		=> GMW_Helper::get_addon_templates( $this->form['slug'], 'search-results' ),
					'attributes' 	=> array(),
					'priority'		=> 25
				),
				'image'   		  => array(
					'name'     		=> 'image',
					'type'     		=> 'function',
					'default'       => '',
					'label'    		=> __( 'Show Image', 'GMW' ),
					'desc'     		=> __( 'Display featured image and define its width and height in pixels.', 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 30
				),
				/*
				'by_driving'       => array(
					'name'       	=> 'by_driving',
					'type'       	=> 'checkbox',
					'default'       => '',
					'label'      	=> __( 'Driving Distance', 'GMW' ),
					'cb_label'   	=> __( 'Enable', 'GMW' ),
					'desc'       	=> __( 'While the results showing the radius distance from the user to each of the locations, this feature let you display the exact driving distance. Please note that each driving distance request counts with google API when you can have 2500 requests per day.', 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 35
				),
				*/
				'get_directions'   => array(
					'name'       	=> 'get_directions',
					'type'       	=> 'checkbox',
					'default'       => '',
					'label'      	=> __( 'Get Directions Link', 'GMW' ),
					'cb_label'   	=> __( 'Yes', 'GMW' ),
					'desc'       	=> __( "Display \"get directions\" link that will open a new window with google map shows the exact driving direction from the user to the location.", 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 40
				)
			),
			'results_map'    => array(
				
				'map_width'  => array(
					'name'        	=> 'map_width',
					'ty[e'			=> 'text',
					'default'       => '100%',
					'placeholder' 	=> __( 'Map width in px or %', 'GMW' ),
					'label'       	=> __( 'Map width', 'GMW' ),
					'desc'        	=> __( 'Enter the map\'s width in pixels or percentage. ex. 100% or 200px', 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 5
				),
				'map_height' => array(
					'name'        	=> 'map_height',
					'default'       => '300px',
					'placeholder' 	=> __( 'Map height in px or %', 'GMW' ),
					'label'       	=> __( 'Map height', 'GMW' ),
					'desc'        	=> __( 'Enter the map\'s height in pixels or percentage. ex. 100% or 200px', 'GMW' ),
					'attributes'  	=> array( 'size' => '25' ),
					'priority'		=> 10
				),
				'map_type'   => array(
					'name'    		=> 'map_type',
					'type'    		=> 'select',
					'default'       => 'ROADMAP',
					'label'   		=> __( 'Map type', 'GMW' ),
					'desc'    		=> __( 'Choose the map type', 'GMW' ),
					'options' 		=> array(
						'ROADMAP'   	=> __( 'ROADMAP', 'GMW' ),
						'SATELLITE' 	=> __( 'SATELLITE', 'GMW' ),
						'HYBRID'    	=> __( 'HYBRID', 'GMW' ),
						'TERRAIN'   	=> __( 'TERRAIN', 'GMW' )
					),
					'attributes'  	=> '',
					'priority'		=> 15
				),
				'zoom_level' => array(
					'name'    		=> 'zoom_level',
					'default'       => 'auto',
					'type'    		=> 'select',
					'label'   		=> __( 'Zoom level', 'GMW' ),			
					'options' 		=> array(
					'desc'    		=> __( 'Map zoom level', 'GMW' ),
						'auto' 	=> __( 'Auto Zoom', 'GMW' ),
						'1'    	=> '1',
						'2'    	=> '2',
						'3'    	=> '3',
						'4'    	=> '4',
						'5'    	=> '5',
						'6'    	=> '6',
						'7'    	=> '7',
						'8'    	=> '8',
						'9'    	=> '9',
						'10'   	=> '10',
						'11'   	=> '11',
						'12'   	=> '12',
						'13'   	=> '13',
						'14'   	=> '14',
						'15'   	=> '15',
						'16'   	=> '16',
						'17'   	=> '17',
						'18'   	=> '18',
					),
					'attributes'  	=> '',
					'priority'		=> 20
				),
				/*'yl_icon'     	=> array(
					'name'        	=> 'yl_icon',
					'type'  	  	=> 'checkbox',
					'default'       => '',
					'label' 	  	=> __( 'Open "User Location" info window', 'GMW'),
					'cb_label'    	=> __( 'Enable', 'GMW'),
					'desc'        	=> __( "Dynamically open on page load the info window of the marker which represents the user's location.", 'GMW' ),
					'attributes'  	=> array(),
					'priority'		=> 25
				),
				'map_frame'  	=> array(
					'name'       	=> 'map_frame',
					'type'       	=> 'checkbox',
					'default'       => '',
					'label'      	=> __( 'Map frame', 'GMW' ),
					'cb_label'   	=> __( 'Enable', 'GMW' ),
					'desc'       	=> __( 'show frame around the map?', 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 30
				),*/
				'no_results_enabled'  	=> array(
					'name'       	=> 'no_results_enabled',
					'type'       	=> 'checkbox',
					'default'       => '',
					'label'      	=> __( 'Show if no results', 'GMW' ),
					'cb_label'   	=> __( 'Enable', 'GMW' ),
					'desc'       	=> __( 'Display map even if no results were found.', 'GMW' ),
					'attributes' 	=> array(),
					'priority'		=> 35
				),
			),
		);
		
		$new_settings = array();
		$new_settings = apply_filters( 'gmw_' . $this->form['slug'] . '_form_settings', $new_settings, $this->form_fields, $this->form );
		$new_settings = apply_filters( 'gmw_form_settings', $new_settings, $this->form_fields, $this->form );

		// merge settings added from other plugins
		foreach ( $new_settings as $group => $fields ) {

			if ( isset( $this->form_fields[$group] ) ) {
				
				$this->form_fields[$group] = array_merge( $this->form_fields[$group], $fields );
			}
		}
		
		// backward capability for settings before settings groups were created
        foreach ( $this->form_fields as $key => $section ) { 

            if ( ! empty( $section[0] ) && ! empty( $section[1] ) && is_string( $section[0] ) ) {
                
                trigger_error( 'Using deprecated method for registering GMW settings and settings groups.', E_USER_NOTICE );

                $this->form_settings_groups[] = array(
                    'id'    => $key,
                    'label' => $section[0]
                );

                $this->form_fields[$key] = $section[1];
            }            
        }

        // backward capability for replacing std with default
        foreach ( $this->form_fields as $key => $section ) { 

            foreach ( $section as $sec_key => $sec_value ) {
                
                // skip hidden field
                if ( empty( $sec_value ) ) {
                	continue;
                }

                if ( isset( $sec_value['name'] ) && ! isset( $sec_value['default'] ) ) {

	                trigger_error( '"std" attribute is no longer supported in GMW settings and was replaced with "default" since version 3.0.', E_USER_NOTICE );

	                $this->form_fields[$key][$sec_key]['default'] = ! empty( $sec_value['default'] ) ? $sec_value['default'] : '';
	                
	                unset( $this->form_fields[$key][$sec_key]['std'] );
	            }
            }        
        }
	}
	
	/**
	 * Search form address field
	 */
	public static function form_settings_address_field( $value, $name_attr ) {
		?>
        <div>
            <p>
                <?php _e( 'Label', 'GMW' ); ?>:
                <input type="text" name="<?php echo $name_attr.'[title]'; ?>" size="30" placeholder="<?php _e( 'Enter label for the address field', 'GMW' ); ?>" value="<?php echo ( isset( $value['title'] ) ) ? sanitize_text_field( esc_attr( $value['title'] ) ) : ''; ?>" />
            </p>

            <label class="checkbox">
                
                <input type="checkbox" value="1" name="<?php echo $name_attr.'[mandatory]'; ?>" <?php if ( isset( $value['mandatory'] ) ) echo 'checked="checked"'; ?>>	
                
                <?php _e( 'Mandatory Field', 'GMW' ); ?>
            </label>

            <label class="checkbox">
                
                <input type="checkbox" value="1" name="<?php echo $name_attr.'[within]'; ?>" <?php if ( isset( $value['within'] ) ) echo 'checked="checked"'; ?>>	
                
                <?php _e( 'Place label inside the address field', 'GMW' ); ?>
            </label>
            
            <label class="checkbox">
                
                <input type="checkbox" value="1" name="<?php echo $name_attr.'[address_autocomplete]'; ?>" <?php if ( isset( $value['address_autocomplete'] ) ) echo 'checked="checked"'; ?>>	
                
                <?php _e( 'Enable Google Address Autocomplete', 'GMW' ); ?>
            </label>
        </div>
        <?php
	}

	/**
	 * Validate address field input
	 * 
	 * @param  array $output input values before validation
	 * 
	 * @return array validated input
	 */
	public function validate_address_field( $output ) {

		$output['title']     			= sanitize_text_field( $output['title'] );
		$output['within']    			= ! empty( $output['within'] ) ? 1 : NULL;
		$output['mandatory'] 			= ! empty( $output['mandatory'] ) ? 1 : NULL;
		$output['address_autocomplete'] = ! empty( $output['address_autocomplete'] ) ? 1 : NULL;
		
		return $output;
	}

	/**
     * Form Image field
     */
    public function form_settings_image( $value, $name_attr ) {
        ?>
        <div>
            <p>
                <input type="checkbox" name="<?php echo $name_attr.'[enabled]'; ?>" value="1" <?php echo ! empty( $value['enabled'] ) ? 'checked="checked"' : ''; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Width', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo $name_attr.'[width]'; ?>" value="<?php echo ( !empty( $value['width'] ) ) ? $value['width'] : '200px'; ?>" />
            </p>
            <p>
                <?php _e( 'Height', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo $name_attr.'[height]'; ?>" value="<?php echo ( !empty( $value['height'] ) ) ? $value['height'] : '200px'; ?>" />
            </p>
        </div>
        <?php
    }
	
	/**
	 * Validate image field
	 * 
	 * @param  array $output Input values before validation
	 * @return array         Input values after validation
	 */
	public function validate_image( $output ) {

		$output['enabled'] = ! empty( $output['enabled'] ) ? 1 : NULL;
		$output['width']   = ! empty( $output['width'] ) ? preg_replace( '/[^0-9%xp]/', '', $output['width'] ) : '200px';
		$output['height']  = ! empty( $output['height'] ) ? preg_replace( '/[^0-9%xp]/', '', $output['height'] ) : '200px';
		
		return $output;
	}

	/**
	 * Form locator field
	 */
	public static function form_settings_locator( $value, $name_attr ) {
		
		$value 	   	   = ! empty( $value ) ? $value : array();
		$locator_icons = glob( GMW_PATH . '/assets/images/locator-images/*.png' );
		$display_icon  = GMW_IMAGES . '/locator-images/';
		?>    	
	   	<p>
	   		<input onchange="jQuery( '#locator-options-wrapper' ).slideToggle();" type="checkbox" id="setting-search_form-auto_locator_enabled" class="setting-auto_locator enabled checkbox" name="<?php echo $name_attr.'[enabled]'; ?>" value="1" <?php if ( ! empty( $value['enabled'] ) ) echo 'checked="checked"'; ?>>
	   		<label for="setting-search_form-auto_locator_enabled"><?php _e( 'Enable', 'GMW' ); ?></label>
	   	</p> 

	   	<div id="locator-options-wrapper" <?php echo empty( $value['enabled'] ) ? 'style="display:none;"' : ''; ?>>

	   	   	<hr />

	     	<p>
	     		<input type="checkbox" id="setting-search_form-auto_locator_auto_submit" class="setting-auto_locator auto_submit checkbox" name="<?php echo $name_attr.'[submit]'; ?>" value="1" <?php echo ( ! empty( $value['submit'] )  ) ? 'checked="checked"' : ''; ?>>
	     		<label><?php _e( 'Auto form submission', 'GMW' ); ?></label>
	     	</p>

		   	<p style="margin: 15px 0;">	

		   		<label><strong><?php _e( 'Usage:', 'GMW' ); ?></strong></label>

	         	<label style="margin-right:5px">
	     			<input 
	     				type="radio" 
	     				id="setting-search_form-auto_locator_usage" 
	     				class="setting-auto_locator usage radio" 
	     				name="<?php echo $name_attr.'[usage]'; ?>" 
	     				value="icon" 
	     				onclick="jQuery( '#locator-text-options-wrapper' ).slideUp();jQuery( '#locator-icon-options-wrapper' ).slideDown();"
	     				<?php if ( empty( $value['usage'] ) || $value['usage'] == 'icon' ) echo 'checked="checked"'; ?>
	     			>
	     			<?php _e( 'Icon', 'GMW' ); ?>
	     		</label>

	     		<label style="margin-right:5px">
	     			<input 
	     				type="radio" 
	     				id="setting-search_form-auto_locator_usage" 
	     				class="setting-auto_locator usage radio" 
	     				name="<?php echo $name_attr.'[usage]'; ?>" 
	     				value="text" 
	     				onclick="jQuery( '#locator-icon-options-wrapper' ).slideUp(); jQuery( '#locator-text-options-wrapper' ).slideDown();"
	     				<?php if ( ! empty( $value['usage'] ) && $value['usage'] == 'text' ) echo 'checked="checked"'; ?>
	     			>
	     			<?php _e( 'Text', 'GMW' ); ?>
	     		</label>   
	     	</p>

	   		<div id="locator-icon-options-wrapper" <?php echo ( empty( $value['usage'] ) || $value['usage'] == 'icon' ) ? '' : 'style="display:none"'; ?>>
		   	
			   	<p><strong><?php _e( 'Choose Locator icon:', 'GMW' ); ?></strong></p>

			   	<p>
		         	<label style="margin-right:5px">
		     			<input type="radio" id="setting-search_form-auto_locator_icon" class="setting-auto_locator icon radio" name="<?php echo $name_attr.'[icon]'; ?>" value="_icon" checked="checked">
		     			<i style="font-size:22px;color:#666;" class="gmw-icon-target-light"></i>(font icon)
		     		</label>   

		         	<?php

		         	foreach ( $locator_icons as $locator_icon ) {
		         		?>
		         		<label>
		         			<input type="radio" id="setting-search_form-auto_locator_icon" class="setting-auto_locator icon radio" name="<?php echo $name_attr.'[icon]'; ?>" value="<?php echo esc_attr( basename( $locator_icon ) ); ?>" <?php if ( ! empty( $value['icon'] ) && $value['icon'] == basename( $locator_icon ) ) echo 'checked="checked"'; ?>>
		         			<img src="<?php echo esc_url( $display_icon . basename( $locator_icon ) ); ?>" height="30px" width="30px" />
		         		</label>                              	
						<?php
					} ?>
				</p>

				<p>
		     		<input type="checkbox" id="setting-search_form-auto_locator_within" class="setting-auto_locator within checkbox" name="<?php echo $name_attr.'[within]'; ?>" value="1" <?php echo ( ! empty( $value['within'] )  ) ? 'checked="checked"' : ''; ?>>
		     		<label><?php _e( 'Place icon inside the address field', 'GMW' ); ?></label>
		     	</p>

		    </div>

		    <div id="locator-text-options-wrapper" <?php if ( empty( $value['usage'] ) || $value['usage'] != 'text' ) echo 'style="display:none"'; ?>>

			    <p>
	                <label><strong><?php _e( 'Label:', 'GMW' ); ?></strong></label>
	                <input type="text" name="<?php echo $name_attr.'[label]'; ?>" size="30" placeholder="<?php _e( 'Enter label for locator button', 'GMW' ); ?>" value="<?php echo ( isset( $value['label'] ) ) ? sanitize_text_field( esc_attr( $value['label'] ) ) : ''; ?>" />
	            </p>

		    </div>

	    </div>
        <?php
	}

	/**
	 * Validate locator field
	 * 
	 * @param  array $output Input values before validation
	 * @return array         Input values after validation
	 */
	public function validate_locator( $output ) {

		$output['enabled'] = ! empty( $output['enabled'] ) ? 1 : NULL;
		$output['icon']    = ! empty( $output['icon'] )    ? sanitize_text_field( $output['icon'] ) : '_icon';
		$output['within']  = ! empty( $output['within'] )  ? 1 : NULL;
		$output['submit']  = ! empty( $output['submit'] )  ? 1 : NULL;

		return $output;
	}

	/**
	 * Get pages
	 */
	public static function get_pages() {
		$pages = array();
	
		$pages[''] = __( ' -- Same Page -- ', 'GMW' );
		foreach ( get_pages() as $page ) {
			$pages[$page->ID] = $page->post_title;
		}
	
		return $pages;
	}
	
	/**
	 * Form usage tab
	 * 
	 * @return [type] [description]
	 */
	public function form_usage() {
		?>
		<!-- form usage -->
    	<div class="gmw-settings-panel gmw-tab-panel form-usage">
            
            <table class="widefat gmw-form-usage-table">
            	<thead>
                	<tr>
                		<th scope="col" id="cb" class="manage-column" ><?php _e( 'Description', 'GMW'); ?></th>
                    	<th scope="col" id="cb" class="manage-column" ><?php _e( 'Post/Page Content', 'GMW' ); ?></th>
                    	<th scope="col" id="cb" class="manage-column" ><?php _e( 'Tempalte file', 'GMW' ); ?></th>                  	
                	</tr>
            	</thead>
         
            	<tbody>
            		<?php if ( $this->form['addon'] == 'global_maps' ) { ?>
            			<tr>
            				<td class="gmw-form-usage-desc">
                				<p><?php _e( 'Use this shortcode to display the global map anywhere on the page.', 'GMW' ); ?></p>
                			</td>
                			<td class="gmw-form-usage">
                				<p><code>[gmw form="<?php echo $this->form['ID']; ?>"]</code></p>
                			</td>
                			<td class="gmw-form-usage">
                				<p><code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw form="'.$this->form['ID'].'"]\'); &#63;&#62;'; ?></code></p>
                			</td>
                		</tr>
         			<?php } else { ?>

            		<tr>
            			<td class="gmw-form-usage-desc">
            				<p><?php _e( 'Use this shortcode to display the search form and search results of this form.', 'GMW' ); ?></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code>[gmw form="<?php echo $this->form['ID']; ?>"]</code></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw form="'.$this->form['ID'].'"]\'); &#63;&#62;'; ?></code></p>
            			</td>                			
            		</tr>
            		<tr>
            			<td class="gmw-form-usage-desc">
            				<p><?php _e( 'Use this shortcode to display only the search form of this form.', 'GMW' ); ?></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code>[gmw search_form="<?php echo $this->form['ID']; ?>"]</code></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw search_form="'.$this->form['ID'].'"]\'); &#63;&#62;'; ?></code></p>
            			</td>		
            		</tr>
            		<tr>
            			<td class="gmw-form-usage-desc">
            				<p><?php _e( 'Use this shortcode to display only the search results of this form.', 'GMW' ); ?></p>
            			</td>            
            			<td class="gmw-form-usage">
            				<p><code>[gmw search_results="<?php echo $this->form['ID']; ?>"]</code></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw search_results="'.$this->form['ID'].'"]\'); &#63;&#62;'; ?></code></p>
            			</td>
            		</tr>
            		<tr>
            			<td class="gmw-form-usage-desc">
            				<p><?php _e( 'Use this shortcode in the page where you would like to display the search results of any form. That is in case that you choose to display the search results in a different page than the search form or when using "GMW Form" widget.', 'GMW' ); ?></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code>[gmw form="results"]</code></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw form="results"]\'); &#63;&#62;'; ?></code></p>
            			</td>
            		</tr>
            		<tr>
            			<td class="gmw-form-usage-desc">
            				<p><?php _e( 'Use this shortcode to display the results map anywhere on a page. By default, the form you create will display the map above the list of results. Using this shortcode you can display the map anywhere else on the page. Note that when using this shortcode you also need to place either the <code>[gmw search_results="'.$this->form['ID'].'"]</code> or <code>[gmw form="results"]</code> shortocde on the page. You also need to set the "Display Map" feature ( "Search Results" tab ) to "using shortcode".', 'GMW' ); ?></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code>[gmw map="<?php echo $this->form['ID']; ?>"]</code></p>
            			</td>
            			<td class="gmw-form-usage">
            				<p><code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw map="'.$this->form['ID'].'"]\'); &#63;&#62;'; ?></code></p>
            			</td>    
            		</tr>
            		<?php } ?>
            	</tbody>
          	</table>
    	</div>
    	<?php
	}

	/**
	 * output edit form page.
	 *
	 * @access public
	 * @return void
	 */
	public function output() {

		// apply address field to all forms
		add_action( "gmw_{$this->form['addon']}_form_settings_address_field", array( $this, 'form_settings_address_field' ), 10, 2 );
		// apply image settings to all forms
		add_action( "gmw_{$this->form['addon']}_form_settings_image", array( $this, 'form_settings_image' ), 10, 2 );
		//  apply locator button to all forms
		add_action( "gmw_{$this->form['addon']}_form_settings_locator", array( $this, 'form_settings_locator' ), 10, 2 );

		//get form fields
		$this->init_form_settings();
		?>
		<!-- prevent scroll of main page -->
		<style>	
		body {
			overflow: hidden;
		}
		</style>

	    <div id="gmw-edit-form-page" class="wrap gmw-admin-page">
	    	
	    	<div id="top-area">

	    		<a 
    			class="form-editor-close" 
    			title="<?php _e( 'Return to list of forms', 'GMW' ); ?>" 
    			href="admin.php?page=gmw-forms"
	    		/>	
	    		</a>
				
				<h2 class="gmw-wrap-top-h2">
	                <i class="gmw-icon-pencil"></i>
	                <?php echo __( 'Editing Form', 'GMW' ) .' ' . $this->form['ID'] .' <em style="font-size: 12px">( '. $this->form['name'] . ' )</em> '; ?>
	            </h2>

        		<div id="action-buttons">
	        		
	        		<?php $delete_message = __( 'This action cannot be undone. Would you like to proceed?', 'GMW' ) ; ?>

					<!-- Delete Form button -->	
	                <a class="button action delete-form" title="<?php _e( 'Delete form', 'GMW' ); ?>" href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=delete_form&form_id='.$this->form['ID'] ); ?>" onclick="return confirm( '<?php echo $delete_message; ?>' );"><?php _e( 'Delete Form', 'GMW' ); ?></a>

	                <span style="margin-left: 5px">

                		<a class="button action" title="<?php _e( 'Duplicate form', 'GMW' ); ?>" href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=duplicate_form&slug='.$this->form['slug'].'&form_id='.$this->form['ID'] ); ?>"><?php _e( 'Duplicate Form', 'GMW' ); ?></a>                                                               
                	</span>
				</div>
	    	</div>

	    	<div id="form-wrapper">
		        
		        <!-- display form -->
		        <form method="post" action="" class="gmw-edit-form" data-ajax_enabled="<?php echo esc_attr( $this->ajax_enabled ); ?>" data-nonce="<?php echo wp_create_nonce( 'gmw_edit_form_nonce' ); ?>">
					
		        	<?php wp_nonce_field( 'gmw_edit_form_nonce', 'gmw_edit_form_nonce' ); ?> 

		        	<input type="hidden" name="gmw_action" value="update_admin_form" />
		        	<input id="form-title-input" type="hidden" name="gmw_form[title]" value="<?php echo ( ! empty( $this->form['title']) ) ? sanitize_text_field( esc_attr( $this->form['title'] ) ) : 'form_id_'. sanitize_text_field( esc_attr( $this->form['ID'] ) ); ?>" />
		           	
					<div id="left-sidebar">

						<div id="top-bar">

							<span id="form-title-input">
					        	<input type="text" name="gmw_form[title]" value="<?php echo ( ! empty( $this->form['title']) ) ? sanitize_text_field( esc_attr( $this->form['title'] ) ) : 'form_id_'. sanitize_text_field( esc_attr( $this->form['ID'] ) ); ?>" placeholder="Form title" />                                                        	                  
			            		<?php /* <input type="submit" class="button-primary update-form-title" value="<?php _e( 'Update', 'GMW' ); ?>" /> */ ?>

			            	</span>
		
						</div>
		            		
		           		<?php //gmw_admin_helpful_buttons(); ?>
		
						<!-- Form tabs -->
			            <ul class="gmw-tabs-wrapper gmw-edit-form-page-nav-tabs">

			            	<?php
			                foreach ( $this->form_settings_groups as $group ) {
			                				                	
			                	if ( $group['id'] != 'hidden' ) {
			                		echo '<li>';
			                    	echo '<a  href="#settings-' . sanitize_title( $group['id'] ) . '" id="'.esc_attr( $group['id'] ).'" class="gmw-nav-tab gmw-nav-trigger" data-name="'. sanitize_title( $group['id'] ).'">';
		                            echo '<span>' . esc_html( $group['label'] ) . '</span>';
		                            echo '</a>';
		                            echo '</li>';
			                	}
			                }
			                ?>  
			                <li>
			                	<a href="#" id="form-usage" class="gmw-nav-tab" data-name="form-usage"><?php _e( 'Form Usage', 'GMW' ); ?></a>
			                </li>

			            </ul>

			            <input type="submit" id="submit-button" class="button-primary" value="<?php _e( 'Save Changes', 'GMW' ); ?>" />

			            <!-- Update status message -->
						<div id="form-update-messages">
							<p class="success"><i class="gmw-icon-ok-light"></i><?php _e( 'Form updated!', 'GMW' ); ?></p>
							<p class="failed"><i class="gmw-icon-cancel"></i><?php _e( 'Form update failed!', 'GMW' ); ?></p>
						</div>

			        </div>

		           	<!-- tabs content -->
		           	<div class="panels-wrapper">      
			            
			            <!-- form cover - while updating -->
						<div id="gmw-form-cover">
							<div id="updating-info">
								<i class="gmw-icon-spin-3 animate-spin"></i>
								<span><?php _e( 'Updating form...', 'GMW' ); ?></span>
							</div>
						</div>

			            <?php 
		            	echo '<input type="hidden" name="gmw_form[ID]" value="' . $this->form['ID'] .'" />';	
		            	echo '<input type="hidden" name="gmw_form[slug]" value="' . sanitize_text_field( esc_attr( $this->form['slug'] ) ).'" />';	

		            	//form filds
			        	foreach ( $this->form_fields as $key => $section ) {      

			        		if ( ! array_filter( $section ) || ! is_array( $section ) ) {
			        			continue; 
			        		}

			        		//sort fields by priority
			        		uasort( $section, 'gmw_sort_by_priority' );

			        		?>
			                <div id="settings-<?php echo esc_attr( $key ); ?>" class="gmw-settings-panel gmw-tab-panel <?php echo esc_attr( $key ); ?>">
			                    
			                    <table class="widefat">

									<tbody>
									
									<?php 
									do_action( 'form_editor_tab_start', $key, $section, $this->form['ID'], $this->form );
									
									$rowNumber = 0;
										
				                    foreach ( $section as $sec => $option ) {
					
				                    	if ( $option['name'] == 'tab_top_message' || $option['name'] == 'tab_bottom_message' ) {
				                    		echo '<div style="background: white;border:1px solid #ddd;padding:5px;">';
				                    		echo '<'.$option['tag'].' style="'.implode(';', $option['attributes']).'">'.$option['message'].'</'.$option['tag'].'>';
				                    		echo '</div>';
				                    		continue;
				                    	}
				                    	
				                    	//check some values and set defaults
				                        $placeholder     = ! empty( $option['placeholder'] ) ? 'placeholder="' . esc_attr( $option['placeholder'] ) . '"' : '';
				                        $class           = ! empty( $option['class'] ) ? esc_attr( $option['class'] ) : '';
				                        $value           = ! empty( $this->form[$key][$option['name']] ) ? $this->form[$key][$option['name']] : $option['default'];
				                        $option['type']  = ! empty( $option['type'] ) ? $option['type'] : '';
				                        $attributes      = array();
				                        $checkboxDefault = isset( $option['default'] ) && !empty( $option['default'] ) ? $option['default'] : 1;
										$attr_id 		 = 'setting-'.esc_attr( $key ) . '-' . esc_attr( $option['name'] );
										$attr_name  	 = 'gmw_form['.esc_attr( $key).']['.esc_attr( $option['name'] ).']';

										//attributes
				                        if ( !empty( $option['attributes'] ) && is_array($option['attributes'] ) ) {
				                        	foreach ( $option['attributes'] as $attribute_name => $attribute_value ) {
				                        		$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
				                        	}
				                        }
				                        ?>	                     	                        
				                        <tr valign="top" id="sort-search-form-<?php echo esc_attr( $option['name'] ); ?>-tr" class="gmw-item-sort gmw-form-field-wrapper <?php echo esc_attr( $class ); ?>" >
				                        	<!-- Feature description -->
				                        	<td class="gmw-form-feature-desc">
				                        		
				                        		<label for="setting-<?php echo esc_attr( $option['name'] ); ?>" ><?php echo esc_html( $option['label'] ); ?></label>	              	
			                            		
			                            		<?php if ( ! empty( $option['desc'] ) ) { echo ' <em class="description">' . $option['desc'] . '</em>'; } ?>
			                            	</td>

				                        	<td class="gmw-form-feature-settings <?php echo esc_attr( $option['type'] ); ?>">
				                        		
				                        	<?php	

				                        	//display settings fields 
				 	                      	switch ( $option[ 'type' ] ) {
			    	                            		    	                              
		                                        // custom function
		    	                                case "function" :
		    	                              
		    	                                    $function   = ! empty( $option['function'] ) ? $option['function'] : $option['name'];
		    										$name_attr  = 'gmw_form['.esc_attr( $key ).']['.$option['name'].']';
		    										$this_value = ! empty( $this->form[$key][$sec] ) ? $this->form[$key][$sec] : array();

		    										do_action( 'gmw_' . $this->form['slug'].'_form_settings_' . $function, $this_value, $name_attr, $this->form, $key, $option );
				                                	do_action( 'gmw_form_settings_' . $function, $this_value, $name_attr, $this->form, $key, $option );
		    	                                break;
		    									
		    									// checkbox
		    	                                case "checkbox" :             
		    	                                    ?>
		    	                                    <label>
		    	                                    	<input type="checkbox" id="<?php echo $attr_id ?>" class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox" name="<?php echo $attr_name; ?>" value="1" <?php echo implode( ' ', $attributes ); ?> <?php checked( '1', $value ); ?> /> 
		    	                                    	<?php echo esc_html( $option['cb_label'] ); ?>
		    	                                    </label>
		    	                                    <?php      
		                                        break;
		    						             
		    						            // multi checkbox    						     
												case "multicheckbox" :

		                                            $option['default'] = is_array( $option['default'] ) ? $option['default'] : array();

		                                            $value = ( ! empty( $value ) && is_array( $value ) ) ? $value : $option['default'];

		                                            foreach ( $option['options'] as $v => $l ) {

		                                                $checked = in_array( $v, $value ) ? 'checked="checked"' : '';?>

		                                                <label>
		                                                	<input id="<?php echo $attr_id .'-'. esc_attr( $v ); ?>" class="setting-<?php echo $option['name']; ?> checkbox multicheckboxvalues" name="<?php echo $attr_name.'[]'; ?>" type="checkbox" value="<?php echo esc_attr( $v ); ?>" <?php echo $checked; ?> /> <?php echo esc_html( $l ); ?>
		                                                </label>

		                                                <?php
		                                            }

		                                        break;

		                                        // textarea field
		    	                                case "textarea" :
		    	                                	?><textarea id="<?php echo $attr_id ?>" class="setting-<?php echo $option['name']; ?> textarea large-text" cols="50" rows="3" name="<?php echo $attr_name; ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder ; ?>><?php echo esc_textarea( $value ); ?></textarea><?php
		    	                                break;
		    							
		    	                                // radio bttons
		    	                                case "radio" :
		    	                                	foreach ( $option['options'] as $keyVal => $name ) {
		    	                                    	?>
		    	                                     	<label><input type="radio" id="<?php echo $attr_id; ?>" class="setting-<?php echo esc_attr( $option['name'] ); ?> <?php echo esc_attr( $keyVal ); ?> radio" name="<?php echo $attr_name; ?>" value="<?php echo esc_attr( $keyVal ); ?>" <?php checked( $value, $keyVal ) ?> /><?php echo $name; ?></label>&nbsp;&nbsp;                                  	
		     	                                    	<?php    
		    	                                    }
		    	                                break;
		    									
		    									// select fields
		    	                                case "select" :
		    	                                	?>
		    	                                	<select id="<?php echo $attr_id ?>" class="setting-<?php echo $option['name']; ?> select" name="<?php echo $attr_name; ?>" <?php echo implode( ' ', $attributes ); ?>>
		    	                             			
		    	                             			<?php foreach ( $option['options'] as $keyVal => $name ) { ?>
		    	                                			
		    	                                			<?php echo '<option value="'.esc_attr( $keyVal ) . '" ' . selected( $value, $keyVal, false ) . '>' . $name. '</option>'; ?>
		    											
		    											<?php } ?>

		    	                                	</select>
		    	                                	<?php 
		    	                                break;
		    									
		    									// password
		    	                                case "password" :
		    	                                    ?>
		    	                                    <input type="password" id="<?php echo $attr_id ?>" class="setting-<?php echo $option['name']; ?> regular-text password" name="<?php echo $attr_name; ?>" value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> />
		    	                                    <?php
		    	                                break;

		    	                                // hidden
		    	                                case "hidden" :
		    	                                    ?>
		    	                                    <input type="hidden" id="<?php echo $attr_id ?>" class="setting-<?php echo $option['name']; ?> regular-text hidden" name="<?php echo $attr_name; ?>" value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>" <?php echo implode( ' ', $attributes ); ?> />
		    	                                    <?php
		    	                                break;
		                                        
		                                        //text field
		                                        case "" :
		                                        case "input" :
		                                        case "text" :   
		    	                                default :
		    	                                	?>
		    	                                	<input type="text" id="<?php echo $attr_id ?>" class="setting-<?php echo $option['name']; ?> regular-text text" name="<?php echo $attr_name; ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> />
		    	                                	<?php
		    	                                break;
			    	                        }
					
			                           		echo '</td>';
			                        	echo '</tr>';
				
			                        	$rowNumber++;
			                        }
			  
			                        do_action( 'form_editor_tab_end', $key, $section, $this->form['ID'], $this->form );            
									?>
									</tbody>
			                	</table>
			                </div>
			            <?php } ?>

			           	<?php $this->form_usage(); ?>

					</div>

					<?php /* <input type="submit" id="floating-submit" class="button-primary" value="<?php _e( 'Save Changes', 'GMW' ); ?>" /> */ ?>

		        </form>
		        
	        </div>

	        <?php 
	        if ( ! wp_script_is( 'chosen', 'enqueued' ) ) {
	            wp_enqueue_script( 'chosen' );
	            wp_enqueue_style( 'chosen' );
	        }
	        ?>
	    </div>   
	    <script>
	    jQuery( document ).ready( function() {

	    	// submit form when enter key presses in form title input box
	    	jQuery( '#gmw-form-editor-wrapper ul.gmw-tabs-wrapper.left-tabs li a' ).click( function( e ) {
    			// duplicate value from title field into a hidden field in the form
    			jQuery( '#tab-title-holder' ).html( jQuery( this ).find( 'span' ).html() );
	    	});
	    });
	    </script>         
    <?php       
    }

    /**
     * Update form via AJAX
     *
     * Run the form values through validations and udpate the form in database
     * 
     * @return void
     */
    public function ajax_update_form() {

    	// verify nonce
    	check_ajax_referer( 'gmw_edit_form_nonce', 'security', true );

    	// get the submitted values
    	parse_str( $_POST['form_values'], $form_values );

    	// validate the values
    	$valid_input = self::validate( $form_values['gmw_form'] );

    	global $wpdb;

    	$form_id = $valid_input['ID'];
    	unset( $valid_input['ID'] );

    	$title = $valid_input['title'];
    	unset( $valid_input['title'] );
    	unset( $valid_input['slug'] );

    	// udpate form in database
    	if ( $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
                'data'  => serialize( $valid_input ),
                'title' => $title
            ), 
            array( 'ID' => $form_id ), 
            array( 
                '%s',
                '%s'
            ), 
            array( '%d' )

        ) === FALSE ) {

			wp_die( 
				__( 'Failed saving data in database.', 'GMW' ), 
				__( 'Error', 'GMW' ), 
				array( 'response' => 403 ) 
			);
		
		} else {
			
			wp_send_json( $valid_input );
		}
    }

    /**
     * Update form via page load
     *
     * Run the form values through validations and udpate the form in database
     * 
     * @return void
     */
    public function update_form() {

    	// run a quick security check
        if ( empty( $_POST['gmw_edit_form_nonce'] ) || ! check_admin_referer( 'gmw_edit_form_nonce', 'gmw_edit_form_nonce' ) ) {
             wp_die( __( 'Cheatin\' eh?!', 'GMW' ) );
        }
   
    	// validate the values
    	$valid_input = self::validate( $_POST['gmw_form'] );

    	global $wpdb;

    	// udpate form in database
    	if ( $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
                'data'  => serialize( $valid_input ),
                'title' => $valid_input['title']
            ), 
            array( 'ID' => $valid_input['ID'] ), 
            array( 
                '%s',
                '%s'
            ), 
            array( '%d' )

        ) === FALSE ) {

        	// update forms in cache
        	GMW_Helper::update_forms_cache();

			wp_safe_redirect( 
				add_query_arg( 
					array( 
						'gmw_notice' 		=> 'form_not_update', 
						'gmw_notice_status' => 'error' 
					) 
				) 
			);

		} else {

			// update forms in cache
        	GMW_Helper::update_forms_cache();
        		
			wp_safe_redirect( 
				add_query_arg( 
					array( 
						'gmw_notice' 		=> 'form_updated', 
						'gmw_notice_status' => 'updated'
					) 
				) 
			);
		};
    	
    	exit;
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
    	add_filter( 'gmw_validate_form_settings_address_field', array( $this, 'validate_address_field' ) );
    	add_filter( 'gmw_validate_form_settings_image', 		array( $this, 'validate_image' 		   ) );
    	add_filter( 'gmw_validate_form_settings_locator', 		array( $this, 'validate_locator' 	   ) );

    	//get the current form being updated
    	$this->form = GMW_Helper::get_form( $values['ID'] );

    	$valid_input = array();

    	// get basic form values
    	$valid_input['ID'] 	  = absint( $values['ID'] );
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

                $option['type'] = ( ! empty( $option['type'] ) ) ? $option['type'] : 'text';

                switch ( $option['type'] ) {

                	// custom functions validation
                    case "function" :
	    					    		    				    	
	    				//save custom settings value as is. without validation
	    				if ( ! empty( $values[$section_name][$option['name']] ) ) {
	    					$valid_input[$section_name][$option['name']] = $values[$section_name][$option['name']];
                        } else {
                        	$values[$section_name][$option['name']] = $valid_input[$section_name][$option['name']] = '';
                        }

                        //use this filter to validate custom settigns
	    				$function = ! empty( $option['function'] ) ? $option['function'] : $option['name'];
	    				
						$valid_input[$section_name][$option['name']] = apply_filters( 'gmw_' . $valid_input['slug'] . '_validate_form_settings_' . $function, $valid_input[$section_name][$option['name']], $this->form );
                    	$valid_input[$section_name][$option['name']] = apply_filters( 'gmw_validate_form_settings_' . $function, $valid_input[$section_name][$option['name']], $this->form );

                    break;

                    // checkbox
                    case "checkbox" :

                    	$valid_input[$section_name][$option['name']] = ! empty( $values[$section_name][$option['name']] ) ? 1 : NULL;
                        
                    break;
                           
                    // multi checbox            
                   	case "multicheckbox" :

                        if ( empty( $values[$section_name][$option['name']] ) || ! is_array( $values[$section_name][$option['name']] ) ) {

                            $valid_input[$section_name][$option['name']] = is_array( $option['default'] ) ? $option['default'] : array();

                        } else {
                      
                            foreach ( $option['options'] as $v => $l ) {
                            	
                            	if ( in_array( $v, $values[$section_name][$option['name']] ) ) {

                                    $valid_input[$section_name][$option['name']][] = $v; 
                                }
                            }
                        }

                    break;

                    // select and radio buttons
                    case "select" :
                    case "radio" :
                        if ( ! empty( $values[$section_name][$option['name']] ) && in_array( $values[$section_name][$option['name']], array_keys( $option['options'] ) ) ) {
                            $valid_input[$section_name][$option['name']] = $values[$section_name][$option['name']];
                        } else {
                            $valid_input[$section_name][$option['name']] = ( ! empty( $option['default'] ) ) ? $option['default'] : '';
                        }
                    break;

                    // textarea
                    case "textarea" :
                        if ( !empty( $values[$section_name][$option['name']] ) ) {
                            $valid_input[$section_name][$option['name']] = esc_textarea( $values[$section_name][$option['name']] );
                        } else {
                            $valid_input[$section_name][$option['name']] = ( !empty( $option['default'] ) ) ? esc_textarea( $option['default'] ) : '';
                        }
                    break;

                    // text field
                    case "text" :
                    case "password" :

                        if ( !empty( $values[$section_name][$option['name']] ) ) {
                            $valid_input[$section_name][$option['name']] = sanitize_text_field( $values[$section_name][$option['name']] );
                        } else {
                            $valid_input[$section_name][$option['name']] = ( !empty( $option['default'] ) ) ? sanitize_text_field( $option['default'] ) : '';
                        }
                    break;
                }
            }
        }
    	
    	$valid_input = apply_filters( 'gmw_validated_form_settings', $valid_input, $this->form );

        //return formds
    	return $valid_input;
    }
}