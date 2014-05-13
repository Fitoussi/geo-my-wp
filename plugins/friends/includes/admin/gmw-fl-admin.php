<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_FL_Admin class.
 */

class GMW_FL_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->add_ons  = get_option( 'gmw_addons' );
        $this->settings = get_option( 'gmw_options' );

        add_filter( 'gmw_admin_settings', array( $this, 'settings_init' ) );
        add_filter( 'gmw_admin_new_form_button', array( $this, 'new_form_button' ), 1, 1 );
        add_filter( 'gmw_friends_form_settings', array( $this, 'form_settings_init' ), 1, 1 );
        add_filter( 'gmw_admin_shortcodes_page', array( $this, 'shortcodes_page' ) ,1, 10 );

        //form settings
        add_action( 'gmw_friends_form_settings_friends_search_form_template', array( $this, 'friends_search_form_template' ), 2, 4 );
        add_action( 'gmw_friends_form_settings_xprofile_fields', array( $this, 'form_settings_xprofile_fields' ), 1, 4 );
        add_action( 'gmw_friends_form_settings_address_field', array( $this, 'form_settings_address_field' ), 1, 4 );
        add_action( 'gmw_friends_form_settings_results_template', array( $this, 'form_settings_results_template' ), 1, 4 );
        add_action( 'gmw_friends_form_settings_auto_results', array( $this, 'form_settings_auto_results' ), 1, 4 );
        add_action( 'gmw_friends_form_settings_show_avatar', array( $this, 'show_avatar' ), 1, 4 );

    }

    /**
     * addon settings page function.
     *
     * @access public
     * @return $settings
     */
    public function settings_init( $settings ) {

        $settings['features'][1][3] = array(
            'name'     => 'member_location_widget',
            'label'    => __( 'Member\'s Location Widget', 'GMW' ),
            'std'      => '',
            'cb_label' => __( 'Yes', 'GMW' ),
            'desc'     => __( 'Display location of a member in the side bar', 'GMW' ),
            'type'     => 'checkbox'
        );

        return $settings;

    }

    /**
     * New form button function.
     *
     * @access public
     * @return $buttons
     */
    public function new_form_button( $buttons ) {

        $buttons[10] = array(
            'name'       => 'friends',
            'addon'      => 'friends',
            'title'      => __( 'Members Locator', 'GMW' ),
            'link_title' => __( 'Create new Members form', 'GMW' ),
            'prefix'     => 'fl',
            'color'      => 'FFC793'
        );
        return $buttons;

    }

    /**
     * friends locator search form template
     *
     */
    public function friends_search_form_template( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <select name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][form_template]'; ?>">
                <?php foreach ( glob( GMW_FL_PATH . 'search-forms/*', GLOB_ONLYDIR ) as $dir ) { ?>
                    <option value="<?php echo basename( $dir ); ?>" <?php if ( isset( $gmw_forms[$formID][$section]['form_template'] ) && $gmw_forms[$formID][$section]['form_template'] == basename( $dir ) ) echo 'selected="selected"'; ?>><?php echo basename( $dir ); ?></option>
                <?php } ?>

                <?php foreach ( glob( STYLESHEETPATH . '/geo-my-wp/friends/search-forms/*', GLOB_ONLYDIR ) as $dir ) { ?>
                    <?php $cThems = 'custom_' . basename( $dir ) ?>
                    <option value="<?php echo $cThems; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['form_template'] ) && $gmw_forms[$formID][$section]['form_template'] == $cThems ) echo 'selected="selected"'; ?>><?php _e( 'Custom Form: ', 'GMW' ); ?><?php echo basename( $dir ); ?></option>
                <?php } ?>

            </select>
        </div>
        <?php

    }

    function form_settings_xprofile_fields( $gmw_forms, $formID, $section, $option ) {
        global $bp;

        if ( bp_is_active( 'xprofile' ) ) :
            if ( function_exists( 'bp_has_profile' ) ) :
                if ( bp_has_profile( 'hide_empty_fields=0' ) ) :

                    $dateboxes    = array();
                    $dateboxes[0] = '';

                    while ( bp_profile_groups() ) :
                        bp_the_profile_group();

                        //echo '<strong>'. bp_get_the_profile_group_name (). ':</strong><br />';

                        while ( bp_profile_fields() ) :
                            bp_the_profile_field();

                            if ( ( bp_get_the_profile_field_type() == 'datebox' ) ) {
                                $dateboxes[] = bp_get_the_profile_field_id();
                            }

                            if ( (bp_get_the_profile_field_type() == 'radio') || (bp_get_the_profile_field_type() == 'selectbox') || (bp_get_the_profile_field_type() == 'multiselectbox') || ( bp_get_the_profile_field_type() == 'checkbox' ) ) {

                                $field_id = bp_get_the_profile_field_id();
                                ?>
                                <input type="checkbox" name="<?php echo 'gmw_forms[' . $formID . '][' . $section . '][profile_fields][]'; ?>" value="<?php echo $field_id; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['profile_fields'] ) && in_array( $field_id, $gmw_forms[$formID][$section]['profile_fields'] ) ) echo ' checked=checked'; ?>/>
                                <label><?php bp_the_profile_field_name(); ?></label>
                                <br />
                                <?php
                            }

                        endwhile;

                    endwhile;
                    ?>

                    <label><strong style="margin:5px 0px;float:left;width:100%"><?php _e( 'Choose the "Age Range" Field', 'GMW' ); ?></strong></label><br />
                    <select name="<?php echo 'gmw_forms[' . $formID . '][' . $section . '][profile_fields_date]'; ?>">

                        <?php
                        foreach ( $dateboxes as $datebox ) {

                            $field    = new BP_XProfile_Field( $datebox );
                            $selected = ( $gmw_forms[$formID][$section]['profile_fields_date'] == $datebox ) ? 'selected="selected"' : '';
                            ?>
                            <option value="<?php echo $datebox; ?>" <?php echo $selected; ?> ><?php echo $field->name; ?></option>
                        <?php } ?>
                    </select> 

                    <?php
                endif;

            endif;
        endif;

        if ( !bp_is_active( 'xprofile' ) ) {
            if ( is_multisite() )
                $site_url = network_site_url( '/wp-admin/network/admin.php?page=bp-components&updated=true' );
            else
                $site_url = site_url( '/wp-admin/admin.php?page=bp-components&updated=true' );
            _e( 'Your buddypress profile fields are deactivated.  To activate and use them <a href="' . $site_url . '"> click here</a>.', 'GMW' );
        }

    }

    /**
     * address field form settings
     *
     */
    public function form_settings_address_field( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <p>
                <?php _e( 'Field title', 'GMW' ); ?>:
                <input type="text" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][address_field][title]'; ?>" size="30" placeholder="<?php _e( 'enter title for the address field', 'GMW' ); ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['address_field']['title'] ) ) ? $gmw_forms[$formID][$section]['address_field']['title'] : ''; ?>" />
            </p>
            <p>
                <input type="checkbox" value="1" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][address_field][within]'; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['address_field']['within'] ) ) echo 'checked="checked"'; ?>>	
                <?php _e( 'Within the input field', 'GMW' ); ?>
            </p>
            <p>
                <input type="checkbox" value="1" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][address_field][mandatory]'; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['address_field']['mandatory'] ) ) echo 'checked="checked"'; ?>>	
                <?php _e( 'Mandatory Field', 'GMW' ); ?>
            </p>
        </div>

        <?php

    }

    /**
     * results template form settings
     *
     */
    public function form_settings_results_template( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <select name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][results_template]'; ?>">

                <?php foreach ( glob( GMW_FL_PATH . 'search-results/*', GLOB_ONLYDIR ) as $dir ) { ?>

                    <option value="<?php echo basename( $dir ); ?>" <?php if ( isset( $gmw_forms[$formID][$section]['results_template'] ) && $gmw_forms[$formID][$section]['results_template'] == basename( $dir ) ) echo 'selected="selected"'; ?>><?php echo basename( $dir ); ?></option>

                <?php } ?>

                <?php foreach ( glob( STYLESHEETPATH . '/geo-my-wp/friends/search-results/*', GLOB_ONLYDIR ) as $dir ) { ?>

                    <?php $cThems = 'custom_' . basename( $dir ) ?>
                    <option value="<?php echo $cThems; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['results_template'] ) && $gmw_forms[$formID][$section]['results_template'] == $cThems ) echo 'selected="selected"'; ?>><?php _e( 'Custom Template:' ); ?> <?php echo basename( $dir ); ?></option>

                <?php } ?>

            </select>
        </div>
        <?php

    }

    /**
     * auto results settings
     *
     */
    public function form_settings_auto_results( $gmw_forms, $formID, $section, $option ) {
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
     * Get pages
     */
    public function get_pages() {
        $pages = array();

        $pages[''] = __( ' -- Same Page -- ', 'GMW' );
        foreach ( get_pages() as $page ) {
            $pages[$page->ID] = $page->post_title;
        }

        return $pages;

    }

    /**
     * locator icons
     */
    public function get_locator_icons() {
        $icons         = array();
        $locator_icons = glob( GMW_PATH . '/assets/images/locator-images/*.png' );
        $display_icon  = GMW_IMAGES . '/locator-images/';

        $icons['gmw_na'] = __( 'Do not use', 'GMW' );
        foreach ( $locator_icons as $locator_icon ) {
            $icons[basename( $locator_icon )] = '<img src="' . $display_icon . basename( $locator_icon ) . '" height="30px" width="30px"/>';
        }
        return $icons;

    }

    /**
     * Avatar
     */
    public function show_avatar( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <p>
                <input type="checkbox" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][avatar][use]'; ?>" value="1" <?php echo ( isset( $gmw_forms[$formID][$section]['avatar']['use'] ) ) ? "checked=checked" : ""; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Height', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][avatar][width]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['avatar']['width'] ) && !empty( $gmw_forms[$formID][$section]['avatar']['width'] ) ) ? $gmw_forms[$formID][$section]['avatar']['width'] : '200px'; ?>" />
            </p>
            <p>
                <?php _e( 'Width', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][avatar][height]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['avatar']['height'] ) && !empty( $gmw_forms[$formID][$section]['avatar']['height'] ) ) ? $gmw_forms[$formID][$section]['avatar']['height'] : '200px'; ?>" />
            </p>
        </div>
        <?php

    }

    /**
     * form settings function.
     *
     * @access public
     * @return $settings
     */
    function form_settings_init( $settings ) {

    	$settings['search_form'][1] = array(
    			'form_template'   => array(
    					'name'     => 'form_template',
    					'std'      => '',
    					'label'    => __( 'Search Form Template', 'GMW' ),
    					'desc'     => __( 'Choose the search form template that you want to use.', 'GMW' ),
    					'type'     => 'function',
    					'function' => 'friends_search_form_template'
    			),
    			'address_field'   => array(
    					'name'  => 'address_field',
    					'std'   => '',
    					'label' => __( 'Address Field', 'GMW' ),
    					'desc'  => __( 'Type the title for the address field (ex. example "Enter your address"). The title wll be displayed either next to the address input field or within the field if you check its checkbox. You can also choose to have the address field mandatory which will prevent users from submitting the form if no address entered. Otherwise, if you allow the field to be empty and user submit a form with no address the plugin will display all results.', 'GMW' ),
    					'type'  => 'function',
    			),
    			'xprofile_fields' => array(
    					'name'  => 'xprofile_fields',
    					'std'   => '',
    					'label' => __( 'Xprofile Fields', 'GMW' ),
    					'desc'  => __( 'Choose the Xprofile fields that you want to display in the search form. You can choose one or more Xprofile fields created using checkboxs, select box and multiselect box that will all be displayed as checkboxes. You can also choose one Xprofile field created using date field for the age range field.', 'GMW' ),
    					'type'  => 'function',
    			),
    			'radius'          => array(
    					'name'        => 'radius',
    					'std'         => '5,10,15,25,50,100',
    					'placeholder' => __( 'Radius values comma separated', 'GMW' ),
    					'label'       => __( 'Radius / Distance', 'GMW' ),
    					'desc'        => __( 'Enter distance values in the input box comma separated if you want to have a select dropdown menu of multiple radius values in the search form. If only one value entered it will be the default value of the search form which will be hidden.', 'GMW' ),
    					'attributes'  => array( 'size' => '30' )
    			),
    			'units'           => array(
    					'name'    => 'units',
    					'std'     => 'both',
    					'label'   => __( 'Units', 'GMW' ),
    					'desc'    => __( 'Choose if to show both type of units as a dropdown or a single type as defaule.', 'GMW' ),
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
    					'label'   => __( 'Locator Icon', 'GMW' ),
    					'desc'    => __( 'Display the locator button. The locator button will get the user&#39;s current location and submit the search form based of the location found.', 'GMW' ),
    					'type'    => 'radio',
    					'options' => $this->get_locator_icons()
    			),
    			'locator_submit'  => array(
    					'name'     => 'locator_submit',
    					'std'      => '',
    					'label'    => __( 'Locator auto-submit', 'GMW' ),
    					'desc'     => __( 'When checked, automatically submit the form when location found using the Locator button.', 'GMW' ),
    					'type'     => 'checkbox',
    					'cb_label' => __( 'Yes', 'GMW' ),
    			),
    	);

    	$settings['search_results'][1] = array(
    			'results_page'     => array(
    					'name'    => 'results_page',
    					'std'     => '',
    					'label'   => __( 'Results Page', 'GMW' ),
    					'desc'    => __( 'The results page will display the search results in the selected page when using the "GMW Search Form" widget or when you want to have the search form in one page and the results showing in a different page.
    							Choose the results page from the dropdown menu and paste the shortcode [gmw form="results"] into that page. To display the search result in the same page as the search form choose "Same Page" from the select box.', 'GMW' ),
    					'type'    => 'select',
    					'options' => $this->get_pages()
    			),
    			'results_template' => array(
    					'name'     => 'results_template',
    					'std'      => '',
    					'label'    => __( 'Results Template', 'GMW' ),
    					'cb_label' => '',
    					'desc'     => __( 'Choose The resuls template file (results.php). You can find the search results template files in the <code>plugins folder/geo-my-wp/plugin/friends/search-results</code>. You can modify any of the templates files or create your own.
    							If you do modify or create you own template files you should create/save them in your theme or child theme folder and the plugin will pull them from there. This way your changes will not be removed once the plugin is updated.
    							You will need to create the folders and save your results template there <code><strong>themes/your-theme-or-child-theme-folder/geo-my-wp/friends/search-results/your-results-theme-folder</strong></code>.
    							Your theme folder will contain the results.php file and another folder named "css" and the style.css within it.', 'GMW' ),
    					'type'     => 'function',
    			),
    			'auto_results'     => array(
    					'name'     => 'auto_results',
    					'std'      => '',
    					'label'    => __( 'Auto Results', 'GMW' ),
    					'cb_label' => '',
    					'desc'     => __( 'This feature will automatically run initial search and display results based on the user\'s current location (if exists via cookies) when he/she first goes to a search page. You need to define the radius and the units for this initial search .', 'GMW' ),
    					'type'     => 'function'
    			),
    			'display_members'  => array(
    					'name'     => 'display_members',
    					'std'      => '',
    					'label'    => __( 'Display Members?', 'GMW' ),
    					'desc'     => __( 'Display results as list of memebrs', 'GMW' ),
    					'type'     => 'checkbox',
    					'cb_label' => __( 'Yes', 'GMW' ),
    			),
    			'display_map'      => array(
    					'name'    => 'display_map',
    					'std'     => 'na',
    					'label'   => __( 'Display Map?', 'GMW' ),
    					'desc'    => __( 'Display results on map. You can do so automatically above the list of results or manually using the shortcode [gmw map="form ID"].', 'GMW' ),
    					'type'    => 'radio',
    					'options' => array(
    							'na'        => __( 'No map', 'GMW' ),
    							'results'   => __( 'In results', 'GMW' ),
    							'shortcode' => __( 'Using shortcode', 'GMW' ),
    					),
    			),
    			'show_avatar'      => array(
    					'name'  => 'show_avatar',
    					'std'   => '',
    					'label' => __( 'Avatar', 'GMW' ),
    					'desc'  => __( 'Display avatar and define its width and height in pixels or percentage.', 'GMW' ),
    					'type'  => 'function',
    			),
    			'per_page'         => array(
    					'name'        => 'per_page',
    					'std'         => '5,10,15,25',
    					'placeholder' => __( 'Enter values', 'GMW' ),
    					'label'       => __( 'Results Per Page', 'GMW' ),
    					'desc'        => __( 'Choose the number of results per page. By setting a single value you set the default number of results per page. By giving multiple values, comma separated, a select box will be created and the users will be able to set the number of results per page.', 'GMW' ),
    					'attributes'  => array( 'style' => 'width:170px' )
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
    					'desc'       => __( 'Display "get directions" link that will open a new window with google map that shows the exact driving direction from the user to the location.', 'GMW' ),
    					'type'       => 'checkbox',
    					'attributes' => array()
    			),
    	);
    	$settings['results_map'][1]    = array(
    			'map_width'  => array(
    					'name'        => 'map_width',
    					'std'         => '100%',
    					'placeholder' => __( 'Map width in px or %', 'GMW' ),
    					'label'       => __( 'Map Width', 'GMW' ),
    					'desc'        => __( 'Enter the map\'s width in pixels or percentage. ex. 100% or 200px', 'GMW' ),
    					'attributes'  => array( 'size' => '7' )
    			),
    			'map_height' => array(
    					'name'        => 'map_height',
    					'std'         => '300px',
    					'placeholder' => __( 'Map height in px or %', 'GMW' ),
    					'label'       => __( 'Map Height', 'GMW' ),
    					'desc'        => __( 'Enter the map\'s height in pixels or percentage. ex. 100% or 200px', 'GMW' ),
    					'attributes'  => array( 'size' => '7' )
    			),
    			'map_type'   => array(
    					'name'    => 'map_type',
    					'std'     => 'ROADMAP',
    					'label'   => __( 'Map Type', 'GMW' ),
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
    					'label'   => __( 'Zoom Level', 'GMW' ),
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
    					'label' 	  => __( 'Auto-open "Your Location" Info Window', 'GMAPS'),
    					'cb_label'    => __( 'Yes', 'GMAPS'),
    					'desc'        => __( 'Automatically open the info window of the marker which represents the location that the user entered.', 'GMAPS' ),
    					'type'  	  => 'checkbox',
    					'attributes'  => array()
    			),
    			'map_frame'  => array(
    					'name'       => 'map_frame',
    					'std'        => '',
    					'label'      => __( 'Map Frame', 'GMW' ),
    					'cb_label'   => __( 'Yes', 'GMW' ),
    					'desc'       => __( 'show frame around the map?', 'GMW' ),
    					'type'       => 'checkbox',
    					'attributes' => array()
    			),
    	);
    	return $settings;

    }
    
    public function shortcodes_page( $shortcodes ) {
    
    	$shortcodes['member_info'] = array(
    			'name'		  	=> __( 'Member Information', 'GMW' ),
    			'basic_usage' 	=> '[gmw_member_info]',
    			'template_usage'=> '&#60;&#63;php echo do_shortcode(\'[gmw_member_info]\'); &#63;&#62;',
    			'desc'        	=> __( 'Easy way to display any of the location information of a member.', 'GMW' ),
    			'attributes'  	=> array(
    					array(
    							'attr'	 => __( 'user_id', 'GMW' ),
    							'values' => array(
    									__( 'User ID','GMW' ),
    							),
    							'desc'	 => __( 'Use the user_id only if you want to display information of a specific member. 
    									When using the shortcode on a profile page or within
    									a members loop you don\'t need to use the user_id attribute. 
    									The shortcode will use the user ID of the member that is being displayed or the user ID of
    									each member within the members loop.', 'GMW')
    					),
    					array(
    							'attr'	 => __( 'info', 'GMW' ),
    							'values' => array(
    									__( 'street','GMW' ),
    									__( 'apt','GMW' ),
    									__( 'city','GMW' ),
    									__( 'state - state\'s short name (ex FL )','GMW' ),
    									__( 'state_long - state\'s long name (ex Florida )','GMW' ),
    									__( 'zipcode','GMW' ),
    									__( 'country - country short name (ex IL )','GMW' ),
    									__( 'country_long - country long name (ex Israel )','GMW' ),
    									__( 'address','GMW' ),
    									__( 'formatted_address','GMW' ),   								
    							),
    							'desc'	 => __( 'Use a single value or multiple values comma separated of the information you would like to display. 
    									For example you can use info="city,state,country_long" to display "Hollywood FL United States"', 'GMW')
    					),
    
    					array(
    							'attr'	 => __( 'divider', 'GMW' ),
    							'values' => array(
    									__( 'any character','GMW' ),
    							),
    							'desc'	 => __( 'Use any character that you would like to display between the fields you choose above"', 'GMW')
    					),
    			),
    			'examples'  => array(
    					array(
    							'example' => __( '[gmw_member_info user_id="3" info="city,state_long,zipcode" divider=","]', 'GMW' ),
    							'desc'	  => __( 'This shortcode will display the information of the memebr with ID 3 which is ( for example ) "Hollywood,Florida,33021"', 'GMW' )

    					),
    					array(
    							'example' => __( '[gmw_member_info info="city,state" divider="-"]', 'GMW' ),
    							'desc'	  => __( 'Use the shortcode without user_id when within a members loop to display the city and state of each member a memebrs loop."', 'GMW' )

    					),
    					array(
    							'example' => __( 'City:', 'GMW' ) . ' [gmw_member_info info="city"] <br />'
    							. __( 'State:' , 'GMW' ) . '[gmw_member_info info="state"]<br />'
    							. __( 'Country:' , 'GMW' ) . '[gmw_member_info info="country_long"]<br />',
    							'desc'	  => __( 'Use this example in a profile page to display the information of a member:', 'GMW' ) . '<br />'
    							.__ ( 'City: Hollywood', 'GMW' ) . '<br />'
    							. __( 'State: FL', 'GMW' ) . '<br />'
    							. __( 'Country: United States', 'GMW' ) . '<br />',

    					),
    			),
    
    	);
    
    	return $shortcodes;
    
    }

}
new GMW_FL_Admin();
?>