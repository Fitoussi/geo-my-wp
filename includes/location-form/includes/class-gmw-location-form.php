<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'GMW_Location_Form' ) ) :
	
/**
 * GEO my WP Location Form class
 *
 * 
 *
 * @version 1.0
 * 
 * @since 3.0
 * 
 */
class GMW_Location_Form {

	/**
	 * @since 3.0
	 * 
	 * Public $args
	 * 
	 * Default arguments
	 */
	protected $args = array(
		'slug'					=> '',
		'object_type' 			=> 'post',					  // Object type being used in this form.
		'object_id' 		  	=> 0,						  // Object ID.
		'user_id'				=> 0,						  // User ID of the user updating the location.
		'exclude_tabs'			=> 0,						  // array of tabs to exclude from the location form. Otherwise set to 0 if no need to exclude.
		'ajax_enabled'			=> 1,						  // Enable / disable AJAX form submission
		'update_on_submission'	=> 1,						  // Use the built in form submission process to update the location. Otherwise you can use your own functions to save the location data.
		'confirm_required'		=> 1, 						  // require address to be confirmed before saving the location. Otherwise users will be able to entere any address - coordinates without being confirmed.
		'auto_confirm'			=> 0,
		'default_user_location'	=> 0,						  // auto-populate location fields with the user's current position if exsit. That is in case that there is no location save for the user.
		'stand_alone'			=> 1,						  // Wrap the location form within <form> element. That is if the location form is a stand alone and not within another form.
		'form_element'	  		=> '#gmw-location-form',      // form wrapper element. If the location form is within another form the main form element should be used in here.
		'form_template'			=> 'location-form-tabs-left', // Form template name.
		'submit_enabled'		=> 1, 						  // Show "Submit" button within the location form. That can be used when the location form is a stand alone. If the location form is within another form then the submit button of that form should be used.
		'address_autocomplete'  => 1, 						  // Enabled / disable Google Address autocomplete
		'geolocation_button'	=> 1,						  // Enable / disable auto locator button in the address field
		'map_zoom_level'  		=> 12, 						  // Initial map zoom level
        'map_type'        		=> 'ROADMAP', 				  // map type ROADMAP, TERRAIN, SATELLITE or HYBRID
        'map_lat'         		=> '40.7827096', 			  // Map initial latitude
        'map_lng'         		=> '-73.9653099', 			  // Map initial longitude
        'update_callback' 		=> 'gmw_lf_update_location',  // AJAX save callback function
		'delete_callback' 		=> 'gmw_lf_delete_location',  // AJAX delete callback function
		'location_required'     => 0
	);

	protected $boolean_items = array( 
		'ajax_enabled',
		'default_user_location', 
		'stand_alone', 
		'submit_enabled', 
		'address_autocomplete', 
		'confirm_required',
		'geolocation_button'
	);

	/**
	 * @since 3.0
	 * 
	 * Public $args
	 * 
	 * Array for child class to extends the main array above
	 */
	protected $ext_args = array();

	/**
	 * [__construct description]
	 * @param array $atts shortcode values
	 */
    function __construct( $atts = array() ) {
  
    	// extend the default args
		$this->args = array_merge( $this->args, $this->ext_args );
		
		// get the shortcode atts
		$this->args = shortcode_atts( $this->args, $atts, 'gmw_location_form' );

		// filter the location form args
		$this->args = apply_filters( 'gmw_location_form_args', $this->args, $this->args['object_type'], $this->args['slug'] );

		// allow boolean attributes accespt 1/yes/true as true value.
		foreach ( $this->boolean_items as $boolean_item ) {
			$this->args[$boolean_item] = filter_var( $this->args[$boolean_item], FILTER_VALIDATE_BOOLEAN );
		}

		// verify user ID
		$this->args['user_id'] = ! empty( $this->args['user_id'] ) ? $this->args['user_id'] : get_current_user_id();

		if ( ! empty( $this->args['stand_alone'] ) ) {
			$this->args['form_element'] = '#gmw-location-form';
		} 

		// get location from database if exist
		$this->saved_location = $this->get_saved_location();

		// get existing location ID
		$this->location_id = ! empty( $this->saved_location ) ? $this->saved_location->ID : 0;

		// get the user's current position
		$this->user_location = gmw_get_user_current_location();

		// get locationmeta from database if exist
		$this->saved_locationmeta = $this->get_saved_locationmeta();

		// form tabs
		$this->tabs = $this->form_tabs();

		// exclude tabs
		$this->tabs = $this->exclude_tabs( $this->tabs );

		// location form fields
		$this->fields = $this->form_fields();

		// location form messages
		$this->messages = $this->action_messages();

		// get template folders
		$this->template_folders = self::get_folders();

		// pass the values to JavaScript
    	wp_enqueue_style( 'gmw-location-form' );
    	wp_enqueue_script( 'gmw-location-form' );

	    wp_localize_script( 'gmw-location-form', 'gmw_lf_args', array(
	    	'args'			 => $this->args,
	    	'saved_location' => $this->saved_location,
	    	'tabs'			 => $this->tabs,
	    	'fields'	 	 => $this->fields,
	    	'messages'		 => $this->messages,
	    	'ajaxurl'		 => GMW()->ajax_url,
	    	'nonce' 	 	 =>	wp_create_nonce( "gmw_lf_update_location" )
	    ) );

	    // load chosen if not already loader 
	    if ( ! wp_script_is( 'chosen', 'enqueued' ) ) {
            wp_enqueue_script( 'chosen' );
            wp_enqueue_style( 'chosen' );
        }
    }

    /**
     * Form messages
     *
     * @since 3.0
     * 
     * @return array messages display in the location form.
     */
    public function action_messages() {

    	return apply_filters( 'gmw_lf_form_messages', array(
    		'confirming_location' 	=> __( 'Comnfirming Location...', 'GMW' ),
    		'location_exists' 		=> __( 'Location confirmed', 'GMW' ),
    		'location_not_exists'  	=> __( 'No location found', 'GMW' ),
    		'location_changed' 	 	=> __( 'Location changed', 'GMW' ),
    		'location_saved' 	 	=> __( 'Location updated!', 'GMW' ),
    		'location_not_saved' 	=> __( 'There was a problem saving your location. Please try again.', 'GMW' ),
    		'location_deleted' 	 	=> __( 'Location deleted!', 'GMW' ),
    		'location_not_deleted'	=> __( 'There was a problem deleting your location. Please try again.', 'GMW' ),
    		'location_found' 		=> __( 'Location found!', 'GMW' ),
    		'geocoder_failed' 		=> __( 'We were unable to retrieve your location. Please enter a valid address or coordinates.', 'GMW' ),
    		'location_blank' 		=> __( 'No location entered.', 'GMW' ),
    		'location_required' 	=> __( 'You must enter a location to proceed.', 'GMW' ),
    		'confirm_required' 		=> __( 'You must confirm your location before it can be saved', 'GMW' ),
    		'confirm_message' 		=> __( 'You have not confirmed your location. Would you like to proceed?', 'GMW' ),
    		'delete_confirmation'	=> __( 'This action cannot be undone. Would you like to proceed?', 'GMW' ),
    		'coords_invalid'		=> __( 'Coordinates are missing or invalid.', 'GMW' )
    	));
    }

    /**
     * Get location from database if exists.
     *
     * @since 3.0
     * 
     * @return array | false location details from database if exists
     */
    protected function get_saved_location() {
    	
    	$location = GMW_Location::get_location( $this->args['object_type'], $this->args['object_id'] );

		return ! empty( $location ) ? $location : false;        
    } 

    /**
     * get location meta field from databse if exists.
     *
     * We getting all meta fields associate with the location to be able to display 
     *
     * thier values in the location form if needed
     *
     * @since 3.0
     * 
     * @return object | false all the location meta fields attached to the locaiton we are editing
     */
    protected function get_saved_locationmeta() {

    	//abort, if no location exists
    	if ( ! $this->saved_location ) {
    		return;
    	}

    	// pull the location meta from database
    	$location_meta = gmw_get_location_meta( $this->saved_location->ID );

		return ! empty( $location_meta ) ? $location_meta : false;
    } 

    /**
     * Get template folder.
     *
     * Check if custom folders and fields exist in child/theme folder, if so, we will use that. Otherwise, use the plugin's templates.
     * 
     * @return [type] [description]
     */
    public function get_folders() {

    	return array(
    		'form_fields'    => file_exists( STYLESHEETPATH . '/geo-my-wp/location-form/form-fields/' ) ? STYLESHEETPATH . '/geo-my-wp/location-form/form-fields/' : GMW_PATH . '/includes/location-form/templates/form-fields/',
    		'form_templates' => file_exists( STYLESHEETPATH . '/geo-my-wp/includes/location-form/form-templates/' ) ? STYLESHEETPATH . '/geo-my-wp/location-form/form-templates/' : GMW_PATH . '/includes/location-form/templates/location-forms/'
    	);
    }

    /**
     * Form tabs
     * 
     * @return [type] [description]
     */
    public function form_tabs() {
    	
    	$tabs = apply_filters( 'gmw_location_form_tabs', array(
			'location' 	=> array(
                'label'        => __( 'Location', 'GMW' ),
                'icon'         => 'gmw-icon-location',
				'fields_group' => array ( 'location' ),
                'priority'     => 5,
			),
			'address' 	  => array(
                'label'        => __( 'Address', 'GMW' ),
                'icon'         => 'gmw-icon-flag',
				'fields_group' => array ( 'address' ),
                'priority'     => 10,
			),
			'coordinates' => array(
				'label'        => __( 'Coordinates', 'GMW' ),
                'icon'         => 'gmw-icon-compass',
				'fields_group' => array ( 'coordinates' ),
                'priority'     => 15,
			),
    	) );

    	return $tabs;
	}

	/**
	 * Exclude tabs
	 * @return [type] [description]
	 */
	public function exclude_tabs( $tabs = array() ) {

		$tabs = apply_filters( 'gmw_location_form_exclude_tabs', $tabs );

    	if ( ! empty( $this->args['exclude_tabs'] ) ) {

	    	$excluded_tabs = explode( ',', $this->args['exclude_tabs'] );
	 	
	 		// remove excluded tabs
	    	foreach ( $excluded_tabs as $tab ) {
	    		unset( $tabs[$tab] );
	    	}
	    }

	    return $tabs;
	}

    /**
     * Dfault location fields.
     *
     * @since 3.0 
     * 
     * Fields can be extended using add-ons via the filter 'gmw_location_form_fields'.
     *
     * Each field must have its key serves as a slug to be able to easily pull the field data.
     * 
     * Example :
     *
     *  //this 'my_form_field' array key will serve as a slug
     *  'my_form_field' => array (
     * 		'name'  	  => ( required ) will be used for the field's name attribute.
     * 	 	'label' 	  => ( optional ) will be used for the field's label attribute.
     * 	 	'type'  	  => will be used for the field's type attribute. If left blank the type "text" will be used.
     * 	  	'default'     => ( optional ) default value for the field.
     * 	   	'id'		  => ( optional ) will be used for the field's ID attribute.
     * 	    'class' 	  => ( optional ) will be used for the field's class attribute.
     * 	    'placeholder' => ( optional ) will be used for the field's placeholder attribute ( text fields only ).
     *      'desc'		  => ( options ) field description will show below the field.
     *      'attributes'  => ( optional ) array of attribute_name => attribute_value ( ex. 'size' => '50' ).
     *      'priority'    => Position of the field.
     *      'meta_key'	  => ( optional ) its value will be a location meta_key to save the value of the filed to.
     *   );
     * 
     */
    public function form_fields() {
    	
    	return apply_filters( 'gmw_location_form_fields', array(
			
			'location' => array(
				'label' 	=> __( 'Find Your Location', 'GMW' ),
				'fields'	=> array(
					'address' 	=> array(
						'name'        => 'address',
	                    'label'       => __( 'Address', 'GMW' ),
	                    'type'        => 'address',
						'default'     => '',
						'id'          => 'gmw-lf-address',
						'class'		  => $this->args['address_autocomplete'] ? 'gmw-lf-address-autocomplete' : '',
						'placeholder' => __( 'Enter an address...', 'GMW' ),
	                    'desc'        => __( 'Type an address to see suggested results.', 'GMW' ),
						'attributes'  => array( 'style' => 'width:100%' ),
	                    'priority'    => 5,
	                    'required'    => false,
					),
					'map'		=> array(
						'name'        => 'map',
	                    'label'       => '',
	                    'type'        => 'map',
						'default'     => '',
						'id'          => 'gmw-lf-map',
						'class'		  => 'gmw-map',
						'placeholder' => '',
						'desc'        => __( 'Drag the marker to your position on the map..', 'GMW' ),
						'attributes'  => array( 'style' => 'height:210px;width:100%' ),
	                    'priority'    => 10,
	                    'required'    => false,
					)
				)
			),
			'address' => array(
				'label'		=> __( 'Enter Address', 'GMW' ),
				'fields'	=> array(
	                'street' 	=> array(
						'name'        => 'street',
						'label'       => __( 'Street', 'GMW' ),
						'type'        => 'text',
						'default'     => '',
						'id'          => 'gmw-lf-street',
						'class'		  => '',
						'placeholder' => '',
						'desc'        => '',
						'attributes'  => '',
	                    'priority'    => 5,
	                    'required'    => false
					),
					'premise' 	 => array(
						'name'        => 'premise',
						'label'       => __( 'Apt/Suit', 'GMW' ),
						'type'        => 'text',
						'default'     => '',
						'id'          => 'gmw-lf-premise',
						'class'		  => '',
						'placeholder' => '',
						'desc'        => '',
						'attributes'  => '',
	                    'priority'    => 10,
	                    'required'    => false
					),
					'city' 		 => array(
						'name'        => 'city',
						'label'       => __( 'City', 'GMW' ),
						'type'        => 'text',
						'default'     => '',
						'id'          => 'gmw-lf-city',
						'class'		  => '',
						'placeholder' => '',
						'desc'        => '',
						'attributes'  => '',
	                    'priority'    => 15,
	                    'required'    => false
					),
					'region_name' => array(
						'name'        => 'region_name',
						'label'       => __( 'State', 'GMW' ),
						'type'        => 'text',
						'default'     => '',
						'id'          => 'gmw-lf-region-name',
						'placeholder' => '',
						'desc'        => '',
						'attributes'  => '',
	                    'priority'    => 20,
	                    'required'    => false
					),
					'postcode'	  => array(
						'name'        => 'postcode',
						'label'       => __( 'Zipcode', 'GMW' ),
						'type'        => 'text',
						'default'     => '',
						'id'          => 'gmw-lf-postcode',
						'class'		  => '',
						'placeholder' => '',
						'desc'        => '',		
						'attributes'  => '',
	                    'priority'    => 25,
	                    'required'    => false
					),
					'country_code'	=> array(
						'name'        => 'country_code',
						'label'       => __( 'Country', 'GMW' ),
						'type'        => 'text',
						'options'	  => '',
						//'options'	  => gmw_get_countries_list_array(),
						'default'     => '',
						'id'          => 'gmw-lf-country-code',
						'class'		  => '',
						'placeholder' => '',
						'desc'        => '',
						'attributes'  => '',
	                    'priority'    => 30,
	                    'required'    => false
					)
				)
			),
			'coordinates' => array(
				'label'		=> __( 'Enter Coordinates', 'GMW' ), 
				'fields'	=> array(
					'latitude'	=> array(
						'name'        => 'latitude',
						'label'       => __( 'Latitude', 'GMW' ),
						'type'        => 'text',
						'default'     => '',
						'id'          => 'gmw-lf-latitude',
						'class'		  => '',
						'placeholder' => '',		
						'desc'        => '',				
						'attributes'  => '',
	                    'priority'    => 5,
	                    'required'    => false
					),
					'longitude' => array(
						'name'        => 'longitude',
						'label'       => __( 'Longitude', 'GMW' ),
						'type'        => 'text',
						'default'     => '',
						'id'          => 'gmw-lf-longitude',
						'class'		  => '',
						'placeholder' => '',			
						'desc'        => '',				
						'attributes'  => '',
	                    'priority'    => 10,
	                    'required'    => false
					)
				)
			),
			'actions' => array(
				'label'		=> false,
				'fields'	=> array(
					'submit_location' => array(
						'name'        => 'submit_location',
						'label'       => __( 'Update location', 'GMW' ),
						'type'        => 'submit',
						'default'     => '',
						'id'          => 'gmw-lf-submit-location',
						'class'		  => 'gmw-lf-form-action button action-button',
						'placeholder' => '',			
						'desc'        => '',				
						'attributes'  => '',
	                    'priority'    => 5,
	                    'required'    => false
					),
					'delete_location' => array(
						'name'        => 'delete_location',
						'label'       => __( 'Delete Location', 'GMW' ),
						'type'        => 'button',
						'default'     => '',
						'id'          => 'gmw-lf-delete-location',
						'class'		  => 'gmw-lf-form-action button action-button',
						'placeholder' => '',			
						'desc'        => '',				
						'attributes'  => '',
	                    'priority'    => 10,
	                    'required'    => false
					),
					'confirm_location' => array(
						'name'        => 'confirm_location',
						'label'       => __( 'Confirm Location', 'GMW' ),
						'type'        => 'button',
						'default'     => '',
						'id'          => 'gmw-lf-confirm-location',
						'class'		  => 'gmw-lf-form-action button action-button gmw-lf-confirm-location',
						'placeholder' => '',		
						'desc'        => '',				
						'attributes'  => '',
	                    'priority'    => 15,
	                    'required'    => false
					),
					'message' => array(
						'name'        => 'message',
						'label'       => '',
						'type'        => 'message',
						'default'     => '',
						'id'          => 'gmw-lf-action-message',
						'class'		  => 'gmw-lf-form-action',
						'placeholder' => '',			
						'desc'        => '',				
						'attributes'  => '',
	                    'priority'    => 20,
	                    'required'    => false
					),
					
					'loader' => array(
						'name'        => 'loader',
						'label'       => '',
						'type'        => 'loader',
						'default'     => '',
						'id'          => 'gmw-lf-action-loader',
						'class'		  => 'gmw-lf-form-action gmw-icon-spin-3 animate-spin',
						'placeholder' => '',			
						'desc'        => '',				
						'attributes'  => '',
	                    'priority'    => 25,
	                    'required'    => false
					)
				)
			),
    	));
    }

    /**
     * Display form tabs
     * 
     * @return [type] [description]
     */
    protected function display_tabs() {

    	// sort tabs by priority
    	uasort( $this->tabs, 'gmw_sort_by_priority' );

    	$tab_count = 1;
    	$output    = '';

    	// loop through tabs        
        foreach ( $this->tabs as $key => $tab ) {

        	$status = ( $tab_count == 1 ) ? 'active' : '';

        	$output .= '<li id="'.sanitize_title( $key ).'-tab" class="gmw-lf-tab '. $status.' ">';
		    
		    $output .= '<a href="#" class="tab-anchor" data-name="'.sanitize_title( $key ).'">';

		    if ( ! empty( $tab['icon'] ) ) {
		    	$output .= '<i class="'.esc_attr( $tab['icon'] ).'"></i>';
		    }
		    $output .= '<span>'. esc_attr( $tab['label'] ) .'</span>';
		    $output .= '</a>';
		    $output .= '</li>';

		    $tab_count++;
        }

        return $output;
    }

    /**
     * Output a specific group of fields.
     *
     * @since 3.0
     * 
     * @param  string $fields_group the name of the fields' groups to be displayed.
     * 
     * @param  array  $exclude  array of fields to exclude from the group
     * 
     * @return void
     *
     */
    public function display_form_fields_group( $fields_group, $exclude = array() ) {

    	// check if group fields title exists and if so display it
    	if ( ! empty(  $this->form_fields[$fields_group]['label'] ) && apply_filters( 'gmw_lf_group_fields_title', true , $this->fields, $fields_group ) ) {
    		echo '<h3>'. $this->form_fields[$fields_group]['label'] .'</h3>';
    	}

    	// sort fields
    	uasort( $this->fields[$fields_group]['fields'], 'gmw_sort_by_priority' );

        // loop through and display fields             
        foreach ( $this->fields[$fields_group]['fields'] as $slug => $field ) {

        	// skip field if excluded
            if ( in_array( $slug, $exclude ) || $slug == 'section_title' ) {
                continue;
            }

            // display the form field
            $this->display_form_field( $fields_group, $slug );
        }
    } 

    /**
     * Display a single location form field
     *
     * @since 3.0
     *        
     * @param  string $fields_group name of the fields group the field belongs to.
     * 
     * @param  string $slug field slug
     * 
     * @return display the location field   
     */
    public function display_form_field( $fields_group, $slug ) {

    	// field must have slug
    	if ( empty( $slug ) ) {
    		return;
    	}

    	// get the field value
    	$field = $this->fields[$fields_group]['fields'][$slug];

    	// make sure name_attr exists otherwise create one
    	$fieldName = ! empty( $field['name'] ) ? $field['name'] : 'gmw_lf_'.$slug;

        // look for user's current position in cookies to automatically fill out the location form if user location not already exist in databse.
        if ( $this->args['default_user_location'] && ( in_array( $fields_group, array( 'address', 'coordinates', 'location' ) ) ) && empty( $this->saved_location ) && ! empty( $this->user_location ) ) {

        	// get user location if found
        	$field['value'] = isset( $this->user_location->$fieldName ) ? stripslashes( $this->user_location->$fieldName ) : '';
 
        } else {
        	
        	//get tvalue from saved location if exists
        	$field['value'] = isset( $this->saved_location->$fieldName ) ? stripslashes( $this->saved_location->$fieldName ) : '';
        }      

        // get values from location meta for fields with meta_key arg
        if ( ! empty( $field['meta_key'] ) ) {

        	$fieldName = 'gmw_location_form[location_meta]['.$field['meta_key'].']';

        	if ( ! empty( $this->saved_locationmeta[$field['meta_key']] ) ) {
        		$field['value'] = $this->saved_locationmeta[$field['meta_key']];
        	}
        }
       
        // generate label if not exists
        $field['label'] = ! empty( $field['label'] ) ? $field['label'] : '';
       
        // generate default ID if not exists
        $field['id'] = ! empty( $field['id'] ) ? $field['id'] : 'gmw-lf-'.$slug;
        
        $extra_field = ! in_array( $fields_group, array( 'address', 'coordinates', 'location', 'actions' ) ) ? 'gmw-lf-extra-field' : '';
    	$loc_meta = ! empty( $field['meta_key'] ) ? 'location-meta' : '';
    	$chosen = $field['type'] == 'select'  ? 'gmw-chosen' : '';

        // generate class attribute
        $class = 'gmw-lf-field '. $loc_meta . ' ' . $extra_field.' group_'. $fields_group . ' ' . $field['type'] . '-field ' . $slug .' '.$chosen;
        $field['class'] = ! empty( $field['class'] ) ? $class . ' ' . $field['class'] : $class;
        
        // placeholder attribute
    	$field['placeholder'] = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
        
        // generate the field attributes
        $attributes = array();

        if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
            
            foreach( $field['attributes'] as $attribute_name => $attribute_value ) {
                
                $attributes[] = $attribute_name . '="' . $attribute_value . '"';
                
                $field['attributes'] = implode( ' ', $attributes );
            }
        }

        // include form field file       
        include( $this->template_folders['form_fields'] . $field['type'].'-field.php' );
    }

    /**
     * Create hidden submission fields
     *
     * This fields are hidden and holding the location information.
     *
     * Once for submitted the plugin will grab the location information
     *
     * from these hidden fields.
     * 
     * @since 3.0
     * 
     * @return $output html of the hidden fields
     */
    protected function submission_fields() {

    	// check for locaiton ID
        $location_id = ( $this->saved_location ) ? ( int ) $this->saved_location->ID : '';

        $output = '<div class="gmw-lf-submission-fields-wrapper">';

        // add few more hidden fields with location data
        $output .= '<input type="hidden" class="gmw-lf-submission-field location-id" id="gmw_lf_location_id" name="gmw_location_form[ID]" 		   value="'.absint( $location_id ).'" />';
        $output .= '<input type="hidden" class="gmw-lf-submission-field object_type" id="gmw_lf_object_type" name="gmw_location_form[object_type]" value="'.esc_attr( $this->args['object_type'] ).'" />';
        $output .= '<input type="hidden" class="gmw-lf-submission-field object-id"   id="gmw_lf_object_id"   name="gmw_location_form[object_id]"   value="'.absint( $this->args['object_id'] ).'" />';
        $output .= '<input type="hidden" class="gmw-lf-submission-field user-id"   	 id="gmw_lf_user_id"   	 name="gmw_location_form[user_id]" 	   value="'.absint( $this->args['user_id'] ).'" />';
        $output .= '<input type="hidden" class="gmw-lf-submission-field auto-update" id="gmw_lf_auto_update" name="gmw_location_form[auto_update]" value="'.absint( $this->args['update_on_submission'] ).'" />';
        $output .= '<input type="hidden" class="gmw-lf-submission-field action" 	 id="gmw_lf_action" 	 name="gmw_action" 		      	 	   value="update_lf_location" />';
        $output .= '<input type="hidden" class="gmw-lf-submission-field location-id" id="gmw_lf_slug" name="gmw_lf_slug" 		   value="'.esc_attr( $this->args['slug'] ).'" />';

    	// the default location fields
        $address_fields = apply_filters( 'gmw_lf_submission_fields', array(
            'latitude',              
            'longitude',               
            'street_number',     
            'street_name', 
            'street',    
            'premise',         
            'neighborhood',     
            'city', 
            'county',          
            'region_name',     
            'region_code',     
            'postcode',          
            'country_name',      
            'country_code',      
            'address',           
            'formatted_address',
            'place_id'
        ));

        // loop through and create submission fields
        foreach ( $address_fields as $field ) {

            $group = ( $field == 'latitude' || $field == 'longitude' ) ? 'group_coordinates' : 'group_address';

            // check for user's current position to automatically fill out the location form if location does not exists in databse.
	        if ( $this->args['default_user_location'] && empty( $this->saved_location ) && ! empty( $this->user_location ) ) {

	        	// get field value from user current location if exists
	        	$value = isset( $this->user_location->$field ) ? $this->user_location->$field : '';
	 
	        } else {
	        	
	        	// get fields value from saved location if exists
	        	$value = ( !empty( $this->saved_location->$field ) ) ? stripslashes( $this->saved_location->$field ) : '';
	        }      

	        $field = esc_attr( $field );

            $output .= '<input type="hidden" class="gmw-lf-submission-field '.$field.' '.$group.'" id="gmw_lf_'.$field.'" name="gmw_location_form['.$field.']"  value="'.esc_attr( sanitize_text_field( stripslashes( $value ) ) ).'" />';
        }

       	wp_nonce_field( 'gmw_lf_update_location', 'gmw_lf_update_location' );

        $output .= '</div>';

        return $output;
    }

  	/**
     * display the location form
     *
     * The function will include the location form file that was specified.
     *
     * The plugin will first look for the file in the theme/child theme's folder and if found it will load it from there.
     *
     * Otherwise the plugin's default location form will be loaded.
     *
     * @since 3.0
     * 
     * @return void - include a PHP template file.
     * 
     */
    public function display_form() {

    	$gmw_location_form = $this;

   		// form wrapper
    	echo '<div id="gmw-location-form-wrapper" class="gmw-location-form-wrapper '.esc_attr( $gmw_location_form->args['form_template'] ) .'">';
    	
    	do_action( 'gmw_before_location_form', $gmw_location_form );
    	
    	// wrap within a form if needed
	    if ( $gmw_location_form->args['stand_alone'] ) { 
	        echo '<form id="gmw-location-form" class="gmw-lf-form" name="gmw_lf_location_form" method="post">';
	    } 

	    // hidden location fields
    	echo $gmw_location_form->submission_fields();

	    // include the form template
    	include( $gmw_location_form->template_folders['form_templates'] . $gmw_location_form->args['form_template'].'.php' );

		if ( $gmw_location_form->args['stand_alone'] ) { 
		    echo '</form>';
		}

		echo '</div>';
    } 
    
    /**
     * Ajax submission when updating location on submission
     * 
     * @return [type] [description]
     */
    public static function ajax_submission() {

    	//verify ajax nonce
		if ( ! check_ajax_referer( 'gmw_lf_update_location', 'security', false ) ) {

			//abort if bad nonce
			wp_die( __( 'Trying to cheat or something?', 'GMW' ), __( 'Error', 'GMW' ), array( 'response' => 403 ) );
		}

    	// parse the form values
	    parse_str( $_POST['formValues'], $form_values );

	    return $form_values;
    }

    /**
     * Page load submission when updating location on submission
     * 
     * @return [type] [description]
     */
    public static function page_load_submission() {

    	// if in admin dashboard
		if ( IS_ADMIN ) {

			// verify admin nonce
			check_admin_referer( 'gmw_lf_update_location', 'gmw_lf_update_location' );

		// when updating location in front-end
		} else {

			// verify nonce
			if ( empty( $_POST ) || ! isset( $_POST['gmw_lf_update_location'] ) || ! wp_verify_nonce( $_POST['gmw_lf_update_location'], 'gmw_lf_update_location' ) ) {
				
				// abort if bad nonce
				wp_die( __( 'Trying to cheat or something?', 'GMW' ), __( 'Error', 'GMW' ), array( 'response' => 403 ) );
			}
		}

		// form values
		return $_POST; 	    
    }

    /**
     * Update location on submission
     * 
     * @return void
     */
    public static function update_location_on_submission() {

    	// if updating location via ajax
    	if ( defined( 'DOING_AJAX' ) ) {
    		
	    	//parse the form values
		    parse_str( $_POST['formValues'], $form_values );

		// when updating location via page load
		} else {

			//form values
			$form_values = $_POST; 	    
		}

		if ( ! empty( $form_values['gmw_location_form']['auto_update'] ) ) {
    		self::update_location();
    	} else {
    		return;
    	}
    }

    /**
     * Update location.
     * 
     * @return [type] [description]
     */
    public static function update_location( $object_type = false, $object_id = false ) {

    	//if saving location via ajax
    	if ( defined( 'DOING_AJAX' ) ) {
    		
	    	//parse the form values
		    $form_values = self::ajax_submission();

		//when saving location via page load
		} else {

			$form_values = self::page_load_submission();
		}
	    
	    if ( empty( $form_values ) ) {
	    	return;
	    }

	    // Submitted location values
	    $location = $form_values['gmw_location_form'];

	    // abort if not location found
	    if ( empty( $location['latitude'] ) || empty( $location['longitude'] ) ) {
	    	return;
	    }

	    // get the object type
	    $location['object_type'] = ! empty( $object_type ) ? $object_type : $location['object_type'];

	    // get the object ID
	    $location['object_id'] = ! empty( $object_id ) ? $object_id : $location['object_id'];

 		// get the location ID if exists
	    //$location_id = ! empty( $location['ID'] ) ? $location['ID'] : 0;

	    // location meta
	    $location_meta = ! empty( $location['location_meta'] ) ? $location['location_meta'] : array();

	    $location['map_icon'] = ( isset( $location['map_icon'] ) ) ? $location['map_icon'] : '_default.png';
	    
	   	$location_args = array(
	    	'object_type'		=> $location['object_type'],
			'object_id'			=> (int) $location['object_id'],
			'user_id'			=> (int) $location['user_id'],
			'parent'			=> 0,
			'status'        	=> 1,
			'featured'			=> 0,
			'title'				=> ! empty( $location['title'] ) ? $location['title'] : '',
			'latitude'          => $location['latitude'],
			'longitude'         => $location['longitude'],
			'street_number'     => $location['street_number'],
	        'street_name'       => $location['street_name'],
	        'street' 			=> $location['street'],
			'premise'       	=> $location['premise'],
			'neighborhood'  	=> $location['neighborhood'],
			'city'              => $location['city'],
			'county'            => $location['county'],
			'region_name'   	=> $location['region_name'],
			'region_code'   	=> $location['region_code'],
			'postcode'      	=> $location['postcode'],
			'country_name'  	=> $location['country_name'],
			'country_code'  	=> $location['country_code'],
			'address'           => $location['address'],
	        'formatted_address' => $location['formatted_address'],
	        'place_id'			=> $location['place_id'],
			'map_icon'			=> ! empty( $location['map_icon'] ) ? $location['map_icon'] : '_default.png',
		);
		
		// filter location args before updating location
		$location_args = apply_filters( 'gmw_lf_location_args_before_location_updated', $location_args, $location, $form_values );
	    $location_args = apply_filters( 'gmw_lf_'.$location['object_type'].'_location_args_before_location_updated', $location_args, $location, $form_values );

	    // run custom functions before updating location
		do_action( 'gmw_lf_before_location_updated', $location, $location_args, $form_values );
	    do_action( 'gmw_lf_before_'.$location['object_type'].'_location_updated', $location, $location_args, $form_values );

		// save location
		$location['ID'] = GMW_Location::update_location( $location_args );

		// filter location meta before updating
		$location_meta = apply_filters( 'gmw_lf_location_meta_before_location_updated', $location_meta, $location, $form_values );
	    $location_meta = apply_filters( 'gmw_lf_'.$location['object_type'].'_location_meta_before_location_updated', $location_meta, $location, $form_values );

		// save location meta
		if ( ! empty( $location_meta ) ) {

			foreach ( $location_meta as $meta_key => $meta_value ) {

				if ( ! is_array( $meta_value ) ) {
					$meta_value = trim( $meta_value );
				}

				if ( empty( $meta_value ) || ( is_array( $meta_value ) && ! array_filter( $meta_value ) ) ) {
								
					gmw_delete_location_meta( $location['ID'], $meta_key );

				} else {
					
					gmw_update_location_meta( $location['ID'], $meta_key, $meta_value );
				}
			}
		}

		//do something after location updated
		do_action( 'gmw_lf_after_location_updated', $location, $form_values );
	    do_action( 'gmw_lf_after_'.$location['object_type'].'_location_updated', $location, $form_values );

	    //send the location ID back to AJAX call
	    if ( defined( 'DOING_AJAX' ) ) {

	    	wp_send_json( ! empty( $location['ID']  ) ? $location['ID'] : false );
	    
	    } else {

	    	if ( IS_ADMIN ) {

	    	} else {

	    		// reload page to prevent re-submission
	    		wp_redirect( $_SERVER['REQUEST_URI'] );
	    		exit;
	    	}
	    }
    }

    /**
     * Delete location from database
     * 
     * @return [type] [description]
     */
    public static function delete_location() {
    		
		// verify AJAX nonce
		if ( ! check_ajax_referer( 'gmw_lf_update_location', 'security', false ) ) {

			//abort if bad nonce
			wp_die( __( 'Trying to cheat or something?', 'GMW' ), __( 'Error', 'GMW' ), array( 'response' => 403 ) );
		}

    	// parse the form values
    	parse_str( $_POST['formValues'], $form_values );

    	// get the location values
	    $location = $form_values['gmw_location_form'];

    	// get the location ID if exists
	    $location['ID'] = ! empty( $location['ID'] ) ? $location['ID'] : 0;

    	// abort if there is no location ID to delete
	   	if ( empty( $location['ID'] ) ) {
	   		die();
	   	}
	   
	   	// do something before location deleted
		do_action( 'gmw_lf_before_location_deleted', $location, $form_values );
	    do_action( 'gmw_lf_before_'.$location['object_type'].'_location_deleted', $location, $form_values );

	   	$location_id = GMW_Location::delete_location_by_id( $location['ID'], true );

	   	// do something after location deleted
		do_action( 'gmw_lf_after_location_deleted', $location, $form_values );
	    do_action( 'gmw_lf_after_'.$location['object_type'].'_location_deleted', $location, $form_values );

	   	// send the location ID back to AJAX call
	   	wp_send_json( ! empty( $location_id  ) ? $location_id : false );
    }
}
endif;

/**
 * Process Location form submission
 * @return void
 */
function gmw_update_lf_location() {
	GMW_Location_Form::update_location_on_submission();
}
//action create via gmw_process_actions function for page load submission
add_action( 'gmw_update_lf_location', 'gmw_update_lf_location' );

//process form submission via ajax
add_action( 'wp_ajax_gmw_lf_update_location', 'gmw_update_lf_location' );
add_action( 'wp_ajax_gmw_lf_delete_location', array( 'GMW_Location_Form', 'delete_location' ) );
?>