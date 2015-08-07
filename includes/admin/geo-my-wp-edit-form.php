<?php

/**
 * GMW_Edit_Form calss
 * 
 * Edit GMW forms in teh back end
 * 
 * @since 2.5
 * @author FitoussiEyal
 *
 */
class GMW_Edit_Form {
		
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		
		$this->forms       = get_option( 'gmw_forms' );
		$this->forms_group = 'gmw_forms_group';
	
		//address field
		add_action( 'gmw_posts_form_settings_address_field',   array( $this, 'form_settings_address_field' ), 10, 4 );
		add_action( 'gmw_friends_form_settings_address_field', array( $this, 'form_settings_address_field' ), 10, 4 );
		add_action( 'gmw_form_settings_auto_results',  		   array( $this, 'auto_results'    			   ), 10, 4 );
		add_action( 'admin_init', 							   array( $this, 'register_settings' 				  ) );
	}
	
	/**
	 * init form settings function.
	 *
	 * @access protected
	 * @return void
	 */
	protected function init_form_settings() {

		$form_settings = apply_filters('gmw_' . $_GET['form_type'] . '_form_settings', array(
				'hidden' => array(
						__('hidden'),
						array(),
				),
				'page_load_results'    => array(
						__( 'Page Load Results', 'GMW' ),
						array(
								'all_locations'     => array(
										'name'  	=> 'all_locations',
										'std'   	=> '',
										'label' 	=> __( 'Enable Page Load features', 'GMW' ),
										'desc'  	=> __( "By checking this checkbox GEO my WP will automatically display all existing locations on the initial load of the form. You can define extra filters for this initial results below.", 'GMW' ),
										'type'     	=> 'checkbox',
										'cb_label' 	=> __( 'Yes', 'GMW' ),
								),
								'user_location'    => array(
										'name'     => 'user_location',
										'std'      => '',
										'label'    => __( "User's current location based results", "GMW" ),
										'desc'     => __( "GEO my WP will first check for the user's current location; If exists the locations will be displayed based on that. Note that an address entered below wont take into account if the user's current locaiton exists.", 'GMW' ),
										'type'     => 'checkbox',
										'cb_label' => __( 'Yes', 'GMW' ),
								),
								'address_filter' 	=> array(
										'name'        => 'address_filter',
										'std'         => '',
										'placeholder' => __( 'Enter address', 'GMW' ),
										'label'       => __( 'Starting address', 'GMW' ),
										'desc'        => __( "Set the address for the initial search results. GEO my WP will search for locations near the address entered withing the radius you set below. Leave empty if you want to search all locations.", 'GMW' ),
										'attributes'  => array( 'size' => '25' )
								),
								'radius'          => array(
										'name'        => 'radius',
										'std'         => '',
										'placeholder' => __( 'Radius value', 'GMW' ),
										'label'       => __( 'Radius / Distance', 'GMW' ),
										'desc'        => __( "Set the radius to be used when searching based on the address enterd above or when searching based on the user's current location.", 'GMW' ),
										'attributes'  => array( 'size' => '8' )
								),
								'units'           => array(
										'name'    => 'units',
										'std'     => 'imperial',
										'label'   => __( 'Units', 'GMW' ),
										'desc'    => __( 'Set the units to be used.', 'GMW' ),
										'type'    => 'select',
										'options' => array(
												'imperial' => __( 'Miles', 'GMW' ),
												'metric'   => __( 'Kilometers', 'GMW' )
										),
								),
								'city_filter' 	=> array(
										'name'        => 'city_filter',
										'std'         => '',
										'placeholder' => __( 'City', 'GMW'),
										'label'       => __( 'Filter by city', 'GMW' ),
										'desc'        => __( 'Filter locations by city name. Note that by using this filter GEO my WP will not do a proximity search but will pull locations with the matching city name ( Leave blank for no filter ).', 'GMW' ),
										'attributes'  => array( 'size' => '15' )
								),
								'state_filter' 	=> array(
										'name'        => 'state_filter',
										'std'         => '',
										'placeholder' => __( 'State', 'GMW'),
										'label'       => __( 'Filter by state', 'GMW' ),
										'desc'        => __( 'Filter locations by state name. Note that by using this filter GEO my WP will not do a proximity search but will pull locations with the matching state name ( Leave blank for no filter ).', 'GMW' ),
										'attributes'  => array( 'size' => '15' )
								),
								'zipcode_filter' 	=> array(
										'name'        => 'zipcode_filter',
										'std'         => '',
										'placeholder' => __( 'Zipcode', 'GMW'),
										'label'       => __( 'Filter by zipcode', 'GMW' ),
										'desc'        => __( 'Filter locations by zipcode. Note that by using this filter GEO my WP will not do a proximity search but will pull locations with the matching zipcode ( Leave blank for no filter ).', 'GMW' ),
										'attributes'  => array( 'size' => '15' )
								),
								'city_country' 	=> array(
										'name'        => 'country_filter',
										'std'         => '',
										'placeholder' => __( 'Country', 'GMW'),
										'label'       => __( 'Filter by country', 'GMW' ),
										'desc'        => __( 'Filter locations by country name. Note that by using this filter GEO my WP will not do a proximity search but will pull locations with the matching country name ( Leave blank for no filter ).', 'GMW' ),
										'attributes'  => array( 'size' => '15' )
								),
								'display_posts'    => array(
										'name'     => 'display_posts',
										'std'      => '',
										'label'    => __( 'Display list of results', 'GMW' ),
										'desc'     => __( 'Display list of results', 'GMW' ),
										'type'     => 'checkbox',
										'cb_label' => __( 'Yes', 'GMW' ),
										'attributes' => array()
								),
								'display_map'      => array(
										'name'    => 'display_map',
										'std'     => 'na',
										'label'   => __( 'Display Map', 'GMW' ),
										'desc'    => __( "Display results on map: <ul><li><em>No map - Do not display map</li><li><em>In results - display the map above the list of results</em></li><li><em>Using shortcode - display the map anywhere on the page using the shortcode [gmw map=\"form ID\"]</em></li></ul>", 'GMW' ),
										'type'    => 'radio',
										'options' => array(
												'na'        => __( 'No map', 'GMW' ),
												'results'   => __( 'Within results', 'GMW' ),
												'shortcode' => __( 'Using shortcode', 'GMW' ),
										),
								),
								'per_page'         => array(
										'name'        => 'per_page',
										'std'         => '5,10,15,25',
										'placeholder' => __( 'Enter values', 'GMW' ),
										'label'       => __( 'Results Per Page', 'GMW' ),
										'desc'        => __( 'Choose the number of results per page. By setting a single value you set the default number of results per page. By giving multiple values, comma separated, a select box will be created and the users will be able to set the number of results per page. You can use the value "-1" to display all results.', 'GMW' ),
										'attributes'  => array( 'style' => 'width:170px' )
								),
						),
				),
				'search_form'    => array(
						__( 'Search Form', 'GMW' ),
						array(
								'form_template'   => array(
										'name'     		=> 'form_template',
										'std'      		=> '',
										'label'    		=> __( 'Search Form Template', 'GMW' ),
										'desc'  		=> __( "<p><em>Choose The search form template.</em></p> You can find the search form templates folders in the <code>plugins folder/geo-my-wp/plugin/{$_GET['form_type']}/search-forms/</code>. You can modify any of the templates or create your own.", 'GMW' ).
														   __( " If you do modify or create you own template files you should create/save them in your theme's or child theme's folder and the plugin will pull them from there. This way your changes will not be overridden once the plugin is updated.", 'GMW' ).
														   __( " Your custom template folders should be places under <code><strong>themes/your-theme-or-child-theme-folder/geo-my-wp/{$_GET['form_type']}/search-form/your-search-form-tempalte-folder</strong></code>.", 'GMW' ).
														   __( " Your template folder must contain the search-form.php file and another folder named \"css\" and the style.css within it.", 'GMW' ),
										'type'     		=> 'select',
										'options'		=> self::search_form_templates( $_GET['gmw_form_prefix'] ),
										'attributes' 	=> array(),
								),
								'address_field'   => array(
										'name'     => 'address_field',
										'std'      => '',
										'label'    => __( 'Address Field', 'GMW' ),
										'cb_label' => '',
										'desc'     => __( "<p><em><strong>Label</strong> - Enter the label for the address field ( ex. \"Enter your address\" ).</em></p>", 'GMW' ).
										              __( "<p><em><strong>Label within the input field</strong> - check this checkbox if you'd like to display the label within the address input field. Otherwise it will be displayed next to it.</em></p>", 'GMW' ). 
													  __( "<p><em><strong>Mandatory Field</strong> - Make the address field mandatory which will prevent users from submitting the form if no address entered. Otherwise if you allow the field to be empty and user submit a form without an address GEO my WP will display all results.</em></p>", 'GMW' ).
										              __( "<p><em><strong>Google Address Autocomplete</strong> - check this checkbox to trigger suggested results by Google Places while typing an address.</em></p>", 'GMW' ),
										'type'     => 'function',
								),
								'radius'          => array(
										'name'        => 'radius',
										'std'         => '5,10,15,25,50,100',
										'placeholder' => __( 'Radius values', 'GMW' ),
										'label'       => __( 'Radius / Distance', 'GMW' ),
										'desc'        => __( "Enter multiple distance values comma separated in the input if you'd like to have a select dropdown menu of multiple radius values in the search form; this way the user can choose the distance when searching. Enter a single value to have a deafult distance value.", 'GMW' ),
										'attributes'  => array( 'size' => '20' )
								),
								'units'           => array(
										'name'    => 'units',
										'std'     => 'both',
										'label'   => __( 'Distance Units', 'GMW' ),
										'desc'    => __( "Choose between Miles, Kilometers or both as a dropdown menu for the user to choose from.", 'GMW' ),
										'type'    => 'select',
										'options' => array(
												'both'     => __( 'Both', 'GMW' ),
												'imperial' => __( 'Miles', 'GMW' ),
												'metric'   => __( 'Kilometers', 'GMW' )
										),
								),
								'locator_icon'    => array(
										'name'    => 'locator_icon',
										'std'     => 'gmw_na',
										'label'   => __( 'Locator Button', 'GMW' ),
										'desc'    => __( "locator button - once clicked GEO my WP will try to retrive the user's current location.", 'GMW' ).
										             __( "<p><em><strong>Do not use</strong> - No locator button will be displayed.</em></p>", 'GMW' ).
													 __( "<p><em><strong>Within the address field</strong> - display locator icon within the input address field.</em></p>", 'GMW' ).
													 __( "<p><em><strong>Choose icon</strong> - display any of the icons next to the address field.</em></p>", 'GMW' ),
										'type'    => 'radio',
										'options' => self::get_locator_icons()
								),
								'locator_submit'  => array(
										'name'     => 'locator_submit',
										'std'      => '',
										'label'    => __( 'Locator auto-submit', 'GMW' ),
										'desc'     => __( "This feature works in conjunction with the locator button above. If checked the address found using the locator button will be dynamically entered in the address field and the form will be automatically submitted.", 'GMW' ),
										'type'     => 'checkbox',
										'cb_label' => __( 'Yes', 'GMW' ),
								),
						)
				),
				'search_results' => array(
						__( 'Search Results', 'GMW' ),
						array(
								'results_page'     => array(
										'name'    => 'results_page',
										'std'     => '',
										'label'   => __( 'Results Page', 'GMW' ),
										'desc'    => __( "The results page will display the search results in the selected page when using the \"GMW Search Form\" widget or when you want to have the search form in one page and the results showing in a different page.", 'GMW' ).
												     __( "Choose the results page from the dropdown menu and paste the shortcode [gmw form=\"results\"] into that page. To display the search result in the same page as the search form choose \"Same Page\" from the select box.", 'GMW' ),
										'type'    => 'select',
										'options' => self::get_pages()
								),
								'results_template' => array(
										'name'  		=> 'results_template',
										'std'   		=> '',
										'label' 		=> __( 'Results Template', 'GMW' ),
										'desc'  		=> __( "<p><em>Choose The search resuls template.</em></p> You can find the search results templates folders in the <code>plugins folder/geo-my-wp/plugin/{$_GET['form_type']}/search-results/</code>. You can modify any of the templates or create your own.", 'GMW' ).
														   __( " If you do modify or create you own template files you should create/save them in your theme's or child theme's folder and the plugin will pull them from there. This way your changes will not be overridden once the plugin is updated.", 'GMW' ).
														   __( " Your custom template folders should be places in <code><strong>themes/your-theme-or-child-theme-folder/geo-my-wp/{$_GET['form_type']}/search-results/your-search-results-template-folder</strong></code>.", 'GMW' ).
														   __( " Your template folder must contain the results.php file and another folder named \"css\" and the style.css within it.", 'GMW' ),
										'type'     		=> 'select',
										'options'		=> GMW_Edit_Form::search_results_templates( $_GET['gmw_form_prefix'] ),
										'attributes' 	=> array(),
								),
								'auto_results'     => array(
										'name'     => 'auto_results',
										'std'      => '',
										'label'    => __( 'Auto Results', 'GMW' ),
										'cb_label' => '',
										'desc'     => __( 'This feature will automatically run initial search and display results based on the user\'s current location (if exists via cookies) when he/she first goes to a search page. You need to define the radius and the units for this initial search .', 'GMW' ),
										'type'     => 'function'
								),
								'auto_all_results'     => array(
										'name'  	=> 'auto_all_results',
										'std'   	=> '',
										'label' 	=> __( 'Display all locations on page load', 'GMW' ),
										'desc'  	=> __( "Using this feature the plugin will automatically display all locations on page load. Note that if the auto results feature above is enabled the plugin will first look for location based on the user's current location. If user's location does not exist the plugin then will display all locations.", 'GMW' ),
										'type'     	=> 'checkbox',
										'cb_label' 	=> __( 'Yes', 'GMW' ),
								),
								'per_page'         => array(
										'name'        => 'per_page',
										'std'         => '5,10,15,25',
										'placeholder' => __( 'Enter values', 'GMW' ),
										'label'       => __( 'Results Per Page', 'GMW' ),
										'desc'        => __( 'Choose the number of results per page. By setting a single value you set the default number of results per page. By giving multiple values, comma separated, a select box will be created and the users will be able to set the number of results per page.', 'GMW' ),
										'attributes'  => array( 'style' => 'width:170px' )
								),
								'display_map'      => array(
										'name'    => 'display_map',
										'std'     => 'na',
										'label'   => __( 'Display Map', 'GMW' ),
										'desc'    => __( "Display results on the map: <ul><li><em>No map - Do not display map</li><li><em>In results - display the map above the list of results</em></li><li><em>Using shortcode - display the map anywhere on the page using the shortcode [gmw map=\"form ID\"]</em></li></ul>", 'GMW' ),
										'type'    => 'radio',
										'options' => array(
												'na'        => __( 'No map', 'GMW' ),
												'results'   => __( 'In results', 'GMW' ),
												'shortcode' => __( 'Using shortcode', 'GMW' ),
										),
								),
								'by_driving'       => array(
										'name'       => 'by_driving',
										'std'        => '',
										'label'      => __( 'Driving Distance', 'GMW' ),
										'cb_label'   => __( 'Yes', 'GMW' ),
										'desc'       => __( 'While the results showing the radius distance from the user to each of the locations, this feature let you display the exact driving distance. Please note that each driving distance request counts with google API when you can have 2500 requests per day.', 'GMW' ),
										'type'       => 'checkbox',
										'attributes' => array()
								),
								'get_directions'   => array(
										'name'       => 'get_directions',
										'std'        => '',
										'label'      => __( 'Get Directions Link', 'GMW' ),
										'cb_label'   => __( 'Yes', 'GMW' ),
										'desc'       => __( "Display \"get directions\" link that will open a new window with google map shows the exact driving direction from the user to the location.", 'GMW' ),
										'type'       => 'checkbox',
										'attributes' => array()
								)
						)
				),
				'results_map'    => array(
						__( 'Map', 'GMW' ),
						array(
								'map_width'  => array(
										'name'        => 'map_width',
										'std'         => '100%',
										'placeholder' => __( 'Map width in px or %', 'GMW' ),
										'label'       => __( 'Map width', 'GMW' ),
										'desc'        => __( 'Enter the map\'s width in pixels or percentage. ex. 100% or 200px', 'GMW' ),
										'attributes'  => array( 'size' => '7' )
								),
								'map_height' => array(
										'name'        => 'map_height',
										'std'         => '300px',
										'placeholder' => __( 'Map height in px or %', 'GMW' ),
										'label'       => __( 'Map height', 'GMW' ),
										'desc'        => __( 'Enter the map\'s height in pixels or percentage. ex. 100% or 200px', 'GMW' ),
										'attributes'  => array( 'size' => '7' )
								),
								'map_type'   => array(
										'name'    => 'map_type',
										'std'     => 'ROADMAP',
										'label'   => __( 'Map type', 'GMW' ),
										'desc'    => __( 'Choose the map type', 'GMW' ),
										'type'    => 'select',
										'options' => array(
												'ROADMAP'   => __( 'ROADMAP', 'GMW' ),
												'SATELLITE' => __( 'SATELLITE', 'GMW' ),
												'HYBRID'    => __( 'HYBRID', 'GMW' ),
												'TERRAIN'   => __( 'TERRAIN', 'GMW' )
										),
								),
								'zoom_level' => array(
										'name'    => 'zoom_level',
										'std'     => 'auto',
										'label'   => __( 'Zoom level', 'GMW' ),
										'desc'    => __( 'Map zoom level', 'GMW' ),
										'type'    => 'select',
										'options' => array(
												'auto' => 'Auto Zoom',
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
										)
								),
								'yl_icons'     => array(
										'name'        => 'yl_icon',
										'std'         => '',
										'label' 	  => __( 'Open "User Location" info window', 'GMW'),
										'cb_label'    => __( 'Yes', 'GMW'),
										'desc'        => __( "Dynamically open on page load the info window of the marker which represents the user's location.", 'GMW' ),
										'type'  	  => 'checkbox',
										'attributes'  => array()
								),
								'map_frame'  => array(
										'name'       => 'map_frame',
										'std'        => '',
										'label'      => __( 'Map frame', 'GMW' ),
										'cb_label'   => __( 'Yes', 'GMW' ),
										'desc'       => __( 'show frame around the map?', 'GMW' ),
										'type'       => 'checkbox',
										'attributes' => array()
								),
						),
				),
		));

		return $form_settings;
	}
	
	/**
	 * address field form settings
	 *
	 */
	public static function form_settings_address_field( $gmw_forms, $formID, $section, $option ) {
		?>
        <div>
            <p>
                <?php _e( 'Label', 'GMW' ); ?>:
                <input type="text" name="<?php echo 'gmw_forms['.$_GET['formID'].']['.$section.'][address_field][title]'; ?>" size="30" placeholder="<?php _e( 'enter title for the address field', 'GMW' ); ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['address_field']['title'] ) ) ? $gmw_forms[$formID][$section]['address_field']['title'] : ''; ?>" />
            </p>
            <p>
                <input type="checkbox" value="1" name="<?php echo 'gmw_forms['.$_GET['formID'].']['.$section.'][address_field][within]'; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['address_field']['within'] ) ) echo 'checked="checked"'; ?>>	
                <?php _e( 'Label within the input field', 'GMW' ); ?>
            </p>
            <p>
                <input type="checkbox" value="1" name="<?php echo 'gmw_forms['.$_GET['formID'].']['.$section.'][address_field][mandatory]'; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['address_field']['mandatory'] ) ) echo 'checked="checked"'; ?>>	
                <?php _e( 'Mandatory Field', 'GMW' ); ?>
            </p>
            <p>
                <input type="checkbox" value="1" name="<?php echo 'gmw_forms['.$_GET['formID'].']['.$section.'][address_field][address_autocomplete]'; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['address_field']['address_autocomplete'] ) ) echo 'checked="checked"'; ?>>	
                <?php _e( 'Google Address Autocomplete', 'GMW' ); ?>
            </p>
        </div>
        <?php
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
	 * Get search forms temaplte files
	 * @return multitype:string NULL
	 */
	public static function search_form_templates( $prefix ) {
	
		$themes = array();
		$themes['no_form'] = __( ' -- Disable search form -- ' );
		
		$folder = array();
		
		if ( GEO_my_WP::gmw_check_addon( 'posts' ) != false ) {
			$folder['pt'] = array(
					GMW_PT_PATH .'/search-forms/',
					'posts/search-forms/'
			);
		}
		if ( GEO_my_WP::gmw_check_addon( 'friends' ) != false && class_exists( 'BuddyPress' ) ) {
			$folder['fl'] = array(
					GMW_FL_PATH .'/search-forms/',
					'friends/search-forms/'
			);
		}

		$folder = apply_filters( 'gmw_admin_search_forms_folder', $folder );
		
		if ( !array_key_exists( $prefix, $folder ) )
			return array();
		
		foreach ( glob( $folder[$prefix][0].'*', GLOB_ONLYDIR ) as $dir ) {
			$themes[basename($dir)] = basename($dir);
		}
	
		$custom_templates = glob( STYLESHEETPATH . '/geo-my-wp/'.$folder[$prefix][1].'*', GLOB_ONLYDIR );
		
		if ( !empty( $custom_templates ) ) {
			foreach ( $custom_templates as $dir ) {
				$themes['custom_'.basename($dir)] = 'Custom: '.basename($dir);
			}
		}
		
		return $themes;
	}
	
	/**
	 * Get results template fiels
	 * @return multitype:string NULL
	 */
	public static function search_results_templates( $prefix ) {
	
		if ( GEO_my_WP::gmw_check_addon( 'posts' ) != false ) {
			$folder['pt'] = array(
					GMW_PT_PATH .'/search-results/',
					'posts/search-results/'
			);
		}
		if ( GEO_my_WP::gmw_check_addon( 'friends' ) != false && class_exists( 'BuddyPress' ) ) {
			$folder['fl'] = array(
					GMW_FL_PATH .'/search-results/',
					'friends/search-results/'
			);
		}

		$folder = apply_filters( 'gmw_admin_results_templates_folder', $folder );
				
		if ( !array_key_exists( $prefix, $folder ) )
			return array();
		
		$themes = array();
		foreach ( glob( $folder[$prefix][0].'*', GLOB_ONLYDIR ) as $dir ) {
			$themes[basename($dir)] = basename($dir);
		}
	
		$custom_templates = glob( STYLESHEETPATH . '/geo-my-wp/'.$folder[$prefix][1].'*', GLOB_ONLYDIR );
		
		if ( !empty( $custom_templates ) ) {
			foreach ( $custom_templates as $dir ) {
				$themes['custom_'.basename($dir)] = 'Custom: '.basename($dir);
			}
		}
		
		return $themes;	
	}
	
	/**
	 * locator icons
	 */
	public static function get_locator_icons() {
		
		$icons         = array();
		$locator_icons = glob( GMW_PATH . '/assets/images/locator-images/*.png' );
		$display_icon  = GMW_IMAGES . '/locator-images/';
	
		$icons['gmw_na'] 			   = __( 'Do not use', 'GMW' );
		$icons['within_address_field'] = __( 'Within the address field', 'GMW' );
		
		foreach ( $locator_icons as $locator_icon ) {
			$icons[basename( $locator_icon )] = '<img src="' . $display_icon . basename( $locator_icon ) . '" height="30px" width="30px"/>';
		}
		return $icons;	
	}
	
	/**
	 * auto results settings
	 *
	 */
	public static function auto_results( $gmw_forms, $formID, $section, $option ) {
	?>
        <div>
            <p>
                <input name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][auto_search][on]'; ?>" type="checkbox" value="1" <?php if ( isset( $gmw_forms[$formID][$section]['auto_search']['on'] ) ) echo "checked='checked'"; ?>/>
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>	
            <p>

                <?php _e( 'Radius', 'GMW' ); ?>		
                <input type="text" id="gmw-auto-radius" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][auto_search][radius]'; ?>" size="5" value="<?php echo ( isset( $gmw_forms[$formID][$section]['auto_search']['radius'] ) ) ? $gmw_forms[$formID][$section]['auto_search']['radius'] : "50"; ?>" />	
            </p>
            <p>
                <select id="gmw-auto-units" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][auto_search][units]'; ?>">
                    <option value="imperial" <?php echo 'selected="selected"'; ?>><?php _e( 'Miles', 'GMW' ); ?></option>
                    <option value="metric"   <?php if ( isset( $gmw_forms[$formID][$section]['auto_search']['units'] ) && $gmw_forms[$formID][$section]['auto_search']['units'] == "metric" ) echo 'selected="selected"'; ?>><?php _e( 'Kilometers', 'GMW' ); ?></option>
                </select>
            </p>
        </div>
        <?php

    }
		
	/**
	 * register_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_settings() {

		//varify nonce
		if ( !isset( $_POST['option_page'] ) || $_POST['option_page'] != 'gmw_forms_group' )
			return;
	
		if ( !isset( $_POST['action'] ) || $_POST['action'] != 'update' )
			return;
		 
		register_setting( $this->forms_group, 'gmw_forms', array( $this, 'validate' ) );		
	}
	
	/**
	 * Update form
	 *
	 * @param $newForm
	 */
	function validate( $newForm ) {

		foreach ( $this->forms as $key => $form ) {
			if ( $key == key( $newForm ) ) {
				$this->forms[$key] = $newForm[$key];
			}
		}

		return $this->forms;	
	}
	
	/**
	 * output edit form page.
	 *
	 * @access public
	 * @return void
	 */
	public function output() {
	 
	$this->form_settings = $this->init_form_settings();

	$gmw_forms = get_option('gmw_forms');
	$formID    = $_GET['formID'];
	$gmw_form  = $gmw_forms[$formID];
	$form_type = $_GET['form_type'];
	?>
        <div class="wrap">
        
            <h2 class="gmw-wrap-top-h2">
            	<i class="fa fa-pencil"></i>
                <?php echo _e('Edit Form', 'GMW'); ?>
                <?php gmw_admin_support_button(); ?>
            </h2>

            <div class="clear"></div>
            
            <?php
            if ( !empty( $_GET['settings-updated'] ) ) {
            	echo '<div class="updated" style="clear:both"><p>' . __( 'Form successfully updated', 'GMW' ) . '</p></div>';
            }
            ?>
            
            <form method="post" action="options.php">

	            <?php echo '<input type="hidden" name="gmw_forms['.$formID.'][ID]" 			value="' . $gmw_form['ID'].'" 		  />'; ?>
	            <?php echo '<input type="hidden" name="gmw_forms['.$formID.'][addon]" 		value="' . $gmw_form['addon'].'" 	  />'; ?>
	            <?php echo '<input type="hidden" name="gmw_forms['.$formID.'][form_title]" 	value="' . $gmw_form['form_title'].'" />'; ?>
	            <?php echo '<input type="hidden" name="gmw_forms['.$formID.'][form_type]" 	value="' . $gmw_form['form_type'].'"  />'; ?>
	            <?php echo '<input type="hidden" name="gmw_forms['.$formID.'][prefix]" 		value="' . $gmw_form['prefix'].'" 	  />'; ?>
	            <?php echo '<input type="hidden" name="gmw_forms['.$formID.'][ajaxurl]" 	value="' . GMW_AJAX.'" 				  />'; ?>
				
	            <?php settings_fields($this->forms_group); ?>
				
                <div class="gmw-form-details-panel" style="display:block !important">

                    <table class="widefat">
                    	<thead>
                        	<tr>
                            	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:18% !important;padding:11px 10px"><?php _e('Form Details', 'GMW'); ?></th>
                            	<th></th>
                            </tr>
                       	</thead>
						<tbody>
							<tr valign="top">
	                        	<td class="gmw-form-feature-desc">
	                        		<label><?php _e( 'Form ID', 'GMW' ); ?></label>	              	                         		
                            	</td>
	                        	<td class="gmw-form-feature-settings">
	                        		<?php echo $formID; ?>
	                        	</td>
	                        </tr>
	                        <tr valign="top">
	                        	<td class="gmw-form-feature-desc">
	                        		<label><?php _e( 'Form Type', 'GMW' ); ?></label>	              	                         		
                            	</td>
	                        	<td class="gmw-form-feature-settings">
	                        		<?php echo $gmw_forms[$formID]['form_title']; ?>
	                        	</td>
	                        </tr>
	                        <tr valign="top">
	                        	<td class="gmw-form-feature-desc">
	                        		<label><?php _e( 'Form Name', 'GMW' ); ?></label>	              	                         		
                            	</td>
	                        	<td class="gmw-form-feature-settings">
	                        		<input type="text" name="gmw_forms[<?php echo $formID; ?>][name]" value="<?php echo ( isset( $gmw_forms[$formID]['name'] ) && !empty( $gmw_forms[$formID]['name']) ) ? $gmw_forms[$formID]['name'] : 'form_id_'.$gmw_forms[$formID]['ID']; ?>" />                                                        	
	                        		<span style="margin-left: 5px">
	                        			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'GMW'); ?>" />
	                        		</span>
	                        	</td>
	                        </tr>
	                        <tr valign="top">
	                        	<td class="gmw-form-feature-desc">
	                        		<label><?php _e( 'Actions', 'GMW') ;?></label>	              	                         		
                            	</td>
	                        	<td class="gmw-form-feature-settings">
	                            	<span style="margin-left: 5px">
	                            		<a class="button action" title="<?php __('Delete this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_action=delete_form&formID=<?php echo $formID; ?>" onclick="return confirm('sure?');"><?php _e('Delete This Form', 'GMW'); ?></a>                                                               
	                            	</span>
	                            	<span style="margin-left: 5px">
	                            		<a class="button action" title="<?php __('Go back to list of forms', 'GMW'); ?>" href="<?php echo admin_url( 'admin.php?page=gmw-forms' ); ?>" ><?php _e('Go Back To List of Forms', 'GMW'); ?></a>                                                               
	                            	</span>
	                            	<span>
	                            		<?php echo GMW_Forms::new_form_list(); ?>
	                            	</span>
	                        	</td>
	                        </tr>
						</tbody>
					</table>

				</div>
               
				                
				<h2 style="margin:20px 0px 10px"><?php _e( 'Form Editor', 'GMW' ); ?></h2>

				<!-- Form tabs -->
                <div class="gmw-tabs-table gmw-edit-form-page-nav-tabs gmw-nav-tab-wrapper">
                	<?php
                    foreach ($this->form_settings as $key => $section) {
                    	
                    	if ( ( empty( $section[1] ) ) )
                    		continue;
                    	
                    	if ($key != 'hidden') {
                        	echo '<a  href="#settings-' . sanitize_title($key) . '" id="'.sanitize_title($key).'" class="gmw-nav-tab gmw-nav-trigger">' . esc_html($section[0]) . '</a>';
                    	}
                    }

                    echo '<a href="#settings-form-usage" id="form-usage" class="gmw-nav-tab gmw-nav-trigger" style="float:right;margin-bottom: -2px">'.__( 'Form Usage', 'GMW' ).'</a>';
                    ?>   	   
                </div>
               
                <?php 
            	foreach ( $this->form_settings as $key => $section ) {                	
            		
            		if ( empty( $section[1] ) )
            			continue; ?>

                    <div id="settings-<?php echo sanitize_title($key); ?>" class="gmw-settings-panel">
	                    <table class="widefat">
	                    	<thead>
	                        	<tr>
	                            	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:18% !important;padding:11px 10px"><?php _e('Feature', 'GMW'); ?></th>
	                            	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:57%;padding:11px 10px">
	                            		<?php _e('Settings', 'GMW'); ?>
	                            		<input type="submit" class="button-primary" style="float:right" value="<?php _e('Save Changes', 'GMW'); ?>" />
	                            	</th>
	                            </tr>
	                       	</thead>
							<tbody>
							
							<?php 
							do_action( 'form_editor_tab_start', $key, $section, $formID, $gmw_forms[$formID] );
							
							$rowNumber = 0;
									
		                    foreach ( $section[1] as $sec => $option ) {
		
		                    	if ( $option['name'] == 'tab_top_message' || $option['name'] == 'tab_bottom_message' ) {
		                    		echo '<div style="background: white;border:1px solid #ddd;padding:5px;">';
		                    		echo '<'.$option['tag'].' style="'.implode(';', $option['attributes']).'">'.$option['message'].'</'.$option['tag'].'>';
		                    		echo '</div>';
		                    		continue;
		                    	}
		                    	
		                        $placeholder     = (!empty($option['placeholder']) ) ? 'placeholder="' . $option['placeholder'] . '"' : '';
		                        $class           = !empty($option['class']) ? $option['class'] : '';
		                        $value           = ( isset($gmw_forms[$formID][$key][$option['name']]) && !empty($gmw_forms[$formID][$key][$option['name']]) ) ? $gmw_forms[$formID][$key][$option['name']] : $option['std'];
		                        $option['type']  = !empty($option['type']) ? $option['type'] : '';
		                        $attributes      = array();
		                        $checkboxDefault = ( isset($option['std']) && !empty($option['std']) ) ? $option['std'] : 1;
		
		                        if ( !empty( $option['attributes']) && is_array($option['attributes'] ) ) {
		                        	foreach ($option['attributes'] as $attribute_name => $attribute_value) {
		                        		$attributes[] = esc_attr($attribute_name) . '="' . esc_attr($attribute_value) . '"';
		                        	}
		                        }
		                        		                     	                        
		                        //$alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : '';
								$alternate = '';

		                        echo '<tr valign="top" id="sort-search-form-'.$option['name'].'-tr" class="gmw-item-sort ' . $class . ' ' . $alternate . '" >';
		                        	echo '<td class="gmw-form-feature-desc">';
		                        		echo '<label for="setting-' . $option['name'] . '" >' . $option['label'] . '</label>';	              	
	                            		
	                            		if ( !empty($option['desc'] ) ) {
	                                		echo ' <p class="description">' . $option['desc'] . '</p>';
	                            		}
	                            		
	                            		echo '</td>';

		                        	echo '<td class="gmw-form-feature-settings '.$option['type'].'">';
		                        			
			                        switch ( $option['type'] ) {
			
			                            case "function" :
			
			                                $function = ( isset( $option['function'] ) && !empty($option['function']) ) ? $option['function'] : $option['name'];

			                                do_action( 'gmw_' . $_GET['form_type'].'_form_settings_' . $function, $gmw_forms, $formID, $key, $option );
			                                do_action( 'gmw_form_settings_' . $function, $gmw_forms, $formID, $key, $option );
			
			                            break;
			                                
			                            case "multicheckbox" :
			                                foreach ($option['options'] as $keyVal => $name) {
			                                    ?><p><label><input id="setting-<?php echo $key.'-'.$option['name'] .'-'.$keyVal; ?>" class="setting-<?php echo $option['name']; ?>" name="<?php echo 'gmw_forms['.$formID.']['.$key.']['.$option['name'].']['.$keyVal.']'; ?>" type="checkbox" value="<?php echo $checkboxDefault; ?>" <?php if ( isset($gmw_forms[$formID][$key][$option['name']][$keyVal]) && $gmw_forms[$formID][$key][$option['name']][$keyVal] == $checkboxDefault) echo 'checked="checked"'; ?> /> <?php echo $name; ?></label></p> <?php
			                                }
			                            break;
			                            
			                            case "multicheckboxvalues" :
			                            	foreach ($option['options'] as $keyVal => $name) {
			                            		?><p><label><input id="setting-<?php echo $key.'-'.$option['name'] .'-'.$keyVal; ?>" class="setting-<?php echo $option['name']; ?> setting-<?php echo $key.'-'.$option['name']; ?>" name="<?php echo 'gmw_forms['.$formID.']['.$key.']['.$option['name'].'][]'; ?>" type="checkbox" value="<?php echo $keyVal; ?>" <?php if ( !empty( $gmw_forms[$formID][$key][$option['name']] ) && in_array( $keyVal, $gmw_forms[$formID][$key][$option['name']] ) ) echo 'checked="checked"'; ?> /> <?php echo $name; ?></label></p> <?php
	                            			}
	                            		break;
			                            
			                            case "checkbox" :
			                                ?><p><label><input id="setting-<?php echo $key.'-'.$option['name']; ?>" class="setting-<?php echo $option['name']; ?>" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" type="checkbox" value="<?php echo $checkboxDefault; ?>" <?php echo implode(' ', $attributes); ?> <?php checked($checkboxDefault, $value); ?> /> <?php echo $option['cb_label']; ?></label></p><?php
			                            break;
			                            
			                            case "textarea" :
			                                ?><textarea id="setting-<?php echo $key.'-'.$option['name']; ?>" class="large-text" cols="50" rows="3" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?>><?php echo esc_textarea($value); ?></textarea><?php
			                            break;
			                            
			                            case "radio" :
			
				                            $rc = 1;
				                            foreach ($option['options'] as $keyVal => $name) {
				                                $checked = ( $rc == 1 ) ? 'checked="checked"' : checked($value, $keyVal, false);
				                                echo '<span id="setting-'.$key.'-'.$option['name'] .'-'.$keyVal.'-wrapper"><input type="radio" id="setting-'.$key.'-'.$option['name'] .'-'.$keyVal.'" class="setting-' . $option['name'] . '" name="gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']" value="' . esc_attr($keyVal) . '" ' . $checked . ' />' . $name . ' </span>';
				                                $rc++;
				                            }
			                            break;
			                            
			                            case "select" :
			                                ?><select id="setting-<?php echo $key.'-'.$option['name']; ?>" class="regular-text" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" <?php echo implode(' ', $attributes); ?>><?php
			                                foreach ($option['options'] as $keyVal => $name)
			                                    echo '<option value="' . esc_attr($keyVal) . '" ' . selected($value, $keyVal, false) . '>' . esc_html($name) . '</option>';
			                                ?></select><?php
			                            break;
			                            
			                            case "multiselect" :
			                            	?><select multiselect id="setting-<?php echo $key.'-'.$option['name']; ?>" class="regular-text" name="<?php echo 'gmw_forms['.$formID.']['.$key.']['.$option['name'].'][]'; ?>" <?php echo implode(' ', $attributes); ?>><?php
	                            			 foreach ( $option['options'] as $keyVal => $name ) {                            			
	                            			 	$selected = ( in_array( $keyVal, $value ) ) ? 'selected="selected"' : '';
	                            		     	echo '<option value="' . esc_attr($keyVal) . '" ' .$selected. '>' . esc_html($name) . '</option>';
	                            			 }
	                            		     ?></select><?php
	                            		break;
			                            
			                            case "password" :
			                                ?><input id="setting-<?php echo $key.'-'.$option['name']; ?>" class="regular-text" type="password" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> /><?php
			                            break;
			                            
			                            case "hidden" :
			                                ?><input id="setting-<?php echo $key.'-'.$option['name']; ?>" class="gmw-form-hidden-field" id="setting-<?php echo $option['name']; ?>" class="regular-text" type="hidden" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" value="<?php echo $value; ?>" /><?php
			                            break;
			                                
			                            default :
			                                ?><input id="setting-<?php echo $key.'-'.$option['name']; ?>" class="regular-text" type="text" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> /><?php
			                            break;
		                            }
		
	                           		echo '</td>';
	                        	echo '</tr>';
		
	                        	$rowNumber++;
	                        }
	                        
	                        do_action( 'form_editor_tab_end', $key, $section, $formID, $gmw_forms[$formID] );
	                        
							?>
							</tbody>
                        	<tfoot>
                        		<tr style="height:40px;">
                            		<th scope="col" id="cb" class="manage-column  column-cb check-column" style="width:50px;padding:11px 10px">
                                		<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'GMW'); ?>" />
                            		</th>
                              		<th scope="col" id="id" class="manage-column"></th>	
                            	</tr>
                        	</tfoot>
                    	</table>
                    </div>
                <?php } ?>

                <!-- form usage -->
            	<div id="settings-form-usage" class="gmw-settings-panel">
	                <table class="widefat">
	                	<thead>
	                    	<tr>
	                        	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:20% !important;padding:11px 10px"><?php _e('Post/Page Content', 'GMW'); ?></th>
	                        	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:40% !important;padding:11px 10px"><?php _e('Tempalte file', 'GMW'); ?></th>
	                        	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:40%;padding:11px 10px"><?php _e('Description', 'GMW'); ?></th>
	                    	</tr>
	                	</thead>
	             
	                	<tbody>
	                		<?php if ( $gmw_form['addon'] == 'global_maps' ) { ?>
	                			<tr>
		                			<td>
		                				<code>[gmw form="<?php echo $formID; ?>"]</code>
		                			</td>
		                			<td>
		                				<code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw form="'.$formID.'"]\'); &#63;&#62;'; ?></code>
		                			</td>
		                			<td>
		                				<?php _e( 'Use this shortcode to display the global map anywhere on the page.', 'GMW' ); ?>
		                			</td>
		                		</tr>
	             			<?php } else { ?>

	                		<tr>
	                			<td>
	                				<code>[gmw form="<?php echo $formID; ?>"]</code>
	                			</td>
	                			<td>
	                				<code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw form="'.$formID.'"]\'); &#63;&#62;'; ?></code>
	                			</td>
	                			<td>
	                				<?php _e( 'Use this shortcode to display the search form and search results of this form.', 'GMW' ); ?>
	                			</td>
	                		</tr>
	                		<tr>
	                			<td>
	                				<code>[gmw search_form="<?php echo $formID; ?>"]</code>
	                			</td>
	                			<td>
	                				<code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw search_form="'.$formID.'"]\'); &#63;&#62;'; ?></code>
	                			</td>
	                			<td>
	                				<?php _e( 'Use this shortcode to display only the search form of this form.', 'GMW' ); ?>
	                			</td>
	                		</tr>
	                		<tr>
	                			<td>
	                				<code>[gmw search_results="<?php echo $formID; ?>"]</code>
	                			</td>
	                			<td>
	                				<code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw search_results="'.$formID.'"]\'); &#63;&#62;'; ?></code>
	                			</td>
	                			<td>
	                				<?php _e( 'Use this shortcode to display only the search results of this form.', 'GMW' ); ?>
	                			</td>
	                		</tr>
	                		<tr>
	                			<td>
	                				<code>[gmw map="<?php echo $formID; ?>"]</code>
	                			</td>
	                			<td>
	                				<code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw map="'.$formID.'"]\'); &#63;&#62;'; ?></code>
	                			</td>
	                			<td>
	                				<?php _e( 'Use this shortcode to display the results map anywhere on a page. By default, the form you create will display the map above the list of results. Using this shortcode you can display the map anywhere else on the page. Note that this shortcode must go together with one of the results shortocdes mentioned above. You also need to set the "Display Map" feature ( "Search Results" tab ) to "using shortcode".', 'GMW' ); ?>
	                			</td>
	                		</tr>
	                		<tr>
	                			<td>
	                				<code>[gmw form="results"]</code>
	                			</td>
	                			<td>
	                				<code><?php echo '&#60;&#63;php echo do_shortcode(\'[gmw form="results"]\'); &#63;&#62;'; ?></code>
	                			</td>
	                			<td>
	                				<?php _e( 'Use this shortcode in the page where you would like to display the search results. That is in case that you choose to display the search results in a different page than the search form or when using "GMW Form" widget.', 'GMW' ); ?>
	                			</td>
	                		</tr>
	                		<?php } ?>
	                	</tbody>
	              	</table>
	        	</div>
				<br />
            </form>
            
            <?php $current_tab = ( isset( $_COOKIE['gmw_admin_tab'] ) ) ? $_COOKIE['gmw_admin_tab'] : false; ?>
            
            <script type="text/javascript">
			jQuery(document).ready(function($) {
		        //jQuery(document).ready(function($) {
			        
		        //	$("#settings-search_form table tbody").sortable({
				//		items:'.gmw-item-sort',
		        //        opacity: 0.5,
		       //         cursor: 'pointer',
		       //         axis: 'y',
		       //         update: function() {
		      //              jQuery.post(
		    	//                    ajaxurl, 
		    	//                    { 
			    //	                  action: 'list_update_order', 
			   // 	                  order: jQuery(this).sortable('serialize') 
			   // 	                }, 
		    	//                    function(response){
		       //                 		console.log(response);
		       //             		}
			 	//            );
		       //         }
		      //    });
		      //  });
	        
	        	if ( jQuery('#setting-page_load_results-display_posts').is(':checked') ) {
					jQuery("#setting-page_load_results-display_map-results-wrapper").show();
				} else {
					jQuery("#setting-page_load_results-display_map-results-wrapper").hide();
					if ( jQuery("#setting-page_load_results-display_map-results").is(":checked") ) {
						jQuery("#setting-page_load_results-display_map-shortcode").attr("checked","checked");
					}
				}
				
	        	jQuery('#setting-page_load_results-display_posts').change(function() {
					if ( jQuery(this).is(':checked') ) {
						jQuery("#setting-page_load_results-display_map-results-wrapper").show();
					} else {
						jQuery("#setting-page_load_results-display_map-results-wrapper").hide();
						if ( jQuery("#setting-page_load_results-display_map-results").is(":checked") ) {
							jQuery("#setting-page_load_results-display_map-shortcode").attr("checked","checked");
						}
					}
	        	});
	        	
	            jQuery('.gmw-form-hidden-field').each(function() {
	                jQuery(this).closest('tr').hide();
	            });
	
				if ( '<?php echo $current_tab; ?>' != false ) { 
	         		jQuery('#<?php echo $current_tab; ?>').click();
	         	}
	         	
	            jQuery('#form-usage-trigger').click(function() {
					jQuery('#usage-table-wrapper').slideToggle();
					
	            });
	
	            if ( jQuery( '#settings-search_form #setting-form_template' ).val() == 'no_form' ) {
	            	jQuery("#settings-search_form :input").attr("disabled", true);
	            	jQuery("#settings-search_form #setting-form_template").attr("disabled", false);
	            	jQuery("#settings-search_form .manage-column .button-primary").attr("disabled", false);              	
	            } 
	            
	            jQuery('#settings-search_form #setting-form_template').change(function() {
	                if ( jQuery(this).val() == 'no_form' ) {
	                	jQuery("#settings-search_form :input").attr("disabled", true);
	                	jQuery("#settings-search_form #setting-form_template").attr("disabled", false);
	                	jQuery("#settings-search_form .manage-column .button-primary").attr("disabled", false);              	
	                } else {
	                	jQuery("#settings-search_form :input").attr("disabled", false);
	                }
	            });
			});
        </script>
        </div>            
        <?php       
    }

}