<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Set labels
 * most of the labels of the forms are set below.
 * it makes it easier to manage and it is now possible to modify a single or multiple
 * labels using the filter provided instead of using the translation files.
 *
 * You can create a custom function in the functions.php file of your theme and hook it using the filter gmw_shortcode_set_labels.
 * You should check for the $form['ID'] in your custom function to make sure the function apply only for the required forms.
 * 
 * @since 2.5
 */
function gmw_get_labels( $form = array() ) {

	$labels = array(
		'search_form'		=> array(
			'search_site'		=> __( 'Search Site', 'GMW' ),
			'radius_within'		=> __( 'Within',   'GMW' ),
			'kilometers'		=> __( 'Kilometers',      'GMW' ),
			'miles'				=> __( 'Miles', 'GMW' ),
			'submit'			=> __( 'Submit', 'GMW' ),
			'get_my_location' 	=> __( 'Get my current location','GMW'),
			'show_options'		=> __( 'Show Options', 'GMW' ),
			'select_groups'		=> __( 'Select Groups', 'GMW' ),
			'no_groups'			=> __( 'No Groups', 'GMW' ),
			'all_groups'		=> __( 'All Groups', 'GMW' )
		),
		'pagination'		=> array(
			'prev'  => __( 'Prev', 	'GMW' ),
			'next'  => __( 'Next', 	'GMW' ),
		),
		'form_submission'   => array(
			'per_page'			=> __( 'per page', 'GMW' ),
		),
		'search_results'	=> array(
			'pt_results_message' 	  => array(
				'showing'	=> __( 'Showing %s out of %s results', 'GMW' ),
				'within'	=> __( 'within %s from %s', 'GMW' ),
			),
			'fl_results_message' => array(
				'showing'	=> __( 'Viewing %s out of %s members', 'GMW' ),
				'within'	=> __( 'within %s from %s', 'GMW' ),
			),
			'gl_results_message' => array(
				'showing' 	=> __( 'Viewing %s out of %s groups', 'GMW' ),
				'within'	=> __( 'within %s from %s', 'GMW' ),
			),
			'ug_results_message' => array(
				'showing'	=> __( 'Viewing %s out of %s users', 'GMW' ),
				'within'	=> __( 'within %s from %s', 'GMW' ),
			),
			'distance'          => __( 'Distance: ', 'GMW' ),
			'driving_distance'	=> __( 'Driving distance:', 'GMW' ),
			'address'           => __( 'Address: ',  'GMW' ),
			'formatted_address' => __( 'Address: ',  'GMW' ),
			'directions'        => __( 'Get Directions', 'GMW' ),
			'your_location'     => __( 'Your Location ', 'GMW' ),
			'pt_no_results'		=> __( 'No results found', 'GMW' ),
			'fl_no_results'		=> __( 'No members found', 'GMW' ),
			'gl_no_results'		=> __( 'No groups found', 'GMW' ),
			'ug_no_results'		=> __( 'No users found', 'GMW' ),
			'not_avaliable'		=> __( 'N/A', 'GMW' ),
			'read_more'			=> __( 'Read more',	'GMW' ),
			'contact_info'		=> array(
				'phone'	  		=> __( 'Phone: ', 'GMW' ),
				'fax'	  		=> __( 'Fax: ', 'GMW' ),
				'email'	  		=> __( 'Email: ', 'GMW' ),
				'website' 		=> __( 'website: ', 'GMW' ),
				'na'	  		=> __( 'N/A', 'GMW' ),
				'contact_info'	=> __( 'Contact Information','GMW' ),
			),
			'opening_hours'			=> __( 'Opening Hours' ),
			'member_info'			=> __( 'Member Information', 'GMW' ),
			'google_map_directions' => __( 'Show directions on Google Map', 'GMW' ),
			'active_since'			=> __( 'active %s', 'GMW' )
		),
		'results_message' 	=> array(
				'showing'
		),
		'info_window'		=> array(
			'address'  			 => __( 'Address: ', 'GMW' ),
			'directions'         => __( 'Get Directions', 'GMW' ),
			'formatted_address'  => __( 'Formatted Address: ', 'GMW' ),
			'distance' 			 => __( 'Distance: ', 'GMW' ),
			'phone'	   			 => __( 'Phone: ', 'GMW' ),
			'fax'	   			 => __( 'Fax: ', 'GMW' ),
			'email'	   			 => __( 'Email: ', 'GMW' ),
			'website'  			 => __( 'website: ', 'GMW' ),
			'na'	   			 => __( 'N/A', 'GMW' ),
			'your_location'		 => __( 'Your Location ', 'GMW' ),
			'contact_info'		 => __( 'Contact Information','GMW' ),
			'read_more'			 => __( 'Read more', 'GMW' ),
			'member_info'	     => __( 'Member Information', 'GMW' )
		)
	);
	
	//modify the labels
	$labels = apply_filters( 'gmw_set_labels', $labels, $form );

	if ( ! empty( $form['ID'] ) ) {
		$labels = apply_filters( "gmw_set_labels_{$form['ID']}", $labels, $form );
	}

	return $labels;
}

/**
 * Generate map in search results
 * 
 * @version 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_results_map( $gmw ) {
	
	$args = array( 
		'map_id'		 => $gmw['ID'],
		'prefix'		 => $gmw['prefix'],
		'map_type'		 => $gmw['addon'],
		'map_width'		 => '100%',
		'map_height'	 => '350px',
		'expand_on_load' => ! empty( $gmw['general_settings']['pl_expand_map'] ) ? true : false,
		'form'			 => $gmw,
		'init_visible'	 => $gmw['display_map'] == 'shortcode' ? false : true
	);

	return GMW_Maps_API::get_map_element( $args );
}

	function gmw_results_map( $gmw, $in_results = true ) {
		
		if ( $in_results && $gmw['form_submission']['display_map'] != 'results' ) {
			return;
		}

		if ( ! $in_results && $gmw['form_submission']['display_map'] != 'shortcode' ) {
			return;
		}
			
	    do_action( "gmw_before_map", 				  $gmw );
		do_action( "gmw_{$gmw['prefix']}_before_map", $gmw );
	    
	    echo gmw_get_results_map( $gmw );
	  
	    do_action( "gmw_after_map", 				 $gmw );
		do_action( "gmw_{$gmw['prefix']}_after_map", $gmw );
	}

/**
 * GMW Search Form
 *
 * Get the search form template file PATH and Stylesheet URL
 * 
 * @param array $gmw the form being proccessed
 * @param unknown_type $folder
 * 
 */
/*function gmw_get_search_form( $gmw, $folder ) {

	// search form template name from form settings
	$template_name = $gmw['search_form']['form_template'];	
	
	// search forms folders structure. Can be modified using this
	// filter to allow plugin add their own folders.
	// url    - url to the search forms folder in the plugin's folder
	// path   - path to the search forms folder in the plugins's folder
	// custom - folder that hold custom search forms in theme's child/geo-my-wp/
	$folders = apply_filters( 'gmw_search_forms_folder', array(
		'pt'  => array(
			'url'	 => GMW_URL.'/plugins/posts/search-forms/',
			'path'	 => GMW_PATH.'/plugins/posts/search-forms/',
			'custom' => 'posts/search-forms/'
		),		
		'fl'  => array(
			'url'	 => GMW_URL.'/plugins/friends/search-forms/',
			'path'	 => GMW_PATH.'/plugins/friends/search-forms/',
			'custom' => 'friends/search-forms/'
		),
	), $gmw );

	// abort if no folder provided
	if ( ! empty( $folder ) ) {
		$folders = array_merge( $folders, $folder );
	}
	
	//abort if add-on doesn't exist
	if ( empty( $folders[$gmw['prefix']] ) ) {
		return;
	}
	
	//Load custom search form and css from child/theme folder
	if ( strpos( $template_name, 'custom_' ) !== false ) {
		$template_name  				  = str_replace( 'custom_', '', $template_name );
		$search_form['stylesheet_handle'] = "gmw-{$gmw['ID']}-{$gmw['prefix']}-search-form-{$template_name}";
		$search_form['stylesheet_url']	  = get_stylesheet_directory_uri()."/geo-my-wp/{$folders[$gmw['prefix']]['custom']}{$template_name}/css/style.css";
		$search_form['content_path'] 	  = STYLESHEETPATH . "/geo-my-wp/{$folders[$gmw['prefix']]['custom']}{$template_name}/search-form.php";
	} else {
		$search_form['stylesheet_handle'] = "gmw-{$gmw['ID']}-{$gmw['prefix']}-search-form-{$template_name}";
		$search_form['stylesheet_url'] 	  = $folders[$gmw['prefix']]['url'].$template_name.'/css/style.css';
		$search_form['content_path']      = $folders[$gmw['prefix']]['path'].$template_name.'/search-form.php';
	}
	
	return $search_form;
}
*/

/**
 * Display search form and enqueue its stylesheet
 * 
 * @param  array $gmw the form being processed 
 * 
 * @return display search form
 */
function gmw_search_form( $gmw ) {

	// get search form template files
	$search_form = gmw_get_template( $gmw['slug'], 'search-forms', $gmw['search_form']['form_template'] );

	// enqueue style only once
	if ( ! wp_style_is( $search_form['stylesheet_handle'], 'enqueued' ) ) {
		wp_enqueue_style( $search_form['stylesheet_handle'], $search_form['stylesheet_url'], array(), GMW_VERSION );
	}

	// if results page is set get its permalink
	if ( ! empty( $gmw['form_submission']['results_page'] ) ) {

		$gmw['form_submission']['results_page'] = get_permalink( $gmw['form_submission']['results_page'] );

	// if this is a widget and results page is not set in the shorcode settings we will get the results page from the main settings
	} elseif ( $gmw['in_widget'] ) {
		
		global $gmw_options;

		$gmw['form_submission']['results_page'] = get_permalink( $gmw_options['general_settings']['results_page'] );
	} else {
		
		$gmw['form_submission']['results_page'] = false;
	}

	do_action( "gmw_before_search_form", $gmw );
	do_action( "gmw_{$gmw['prefix']}_before_search_form", $gmw );

	include( $search_form['content_path'] );

	do_action( "gmw_after_search_form", $gmw );
	do_action( "gmw_{$gmw['prefix']}_after_search_form", $gmw );
}

/**
 * Form submission hidden fields
 * 
 * @param  array  $gmw          the form being used
 * @param  string $submit_value the default value of the submit button
 * 
 * @return mix HTML elements of the submission fields
 */
function gmw_get_form_submit_fields( $gmw, $submit_value ) {
	
	$id 	   	   = absint( $gmw['ID'] );
	$submit_value  = ! empty( $submit_value ) ? $submit_value : $gmw['labels']['search_form']['submit'];
	$submit_button = '<input type="submit" id="gmw-submit-'.$id.'" class="gmw-submit gmw-submit-button gmw-submit-'.$id.'" value="'.esc_attr( $submit_value ).'" />';

	// modify the submit button
	$submit_button = apply_filters( 'gmw_form_submit_button', $submit_button, $gmw, $submit_value ); 

	$per_page = current( explode( ",", absint( $gmw['form_submission']['per_page'] ) ) );
	$address  = ! empty( $_GET[$gmw['url_px'].'address'] ) ? esc_attr( sanitize_text_field( stripslashes( implode( ' ', $_GET[$gmw['url_px'].'address'] ) ) ) ) : '';
	$lat	  = ! empty( $_GET[$gmw['url_px'].'lat'] )     ? esc_attr( sanitize_text_field( $_GET[$gmw['url_px'].'lat'] ) ) : '';
	$lng	  = ! empty( $_GET[$gmw['url_px'].'lng'] )     ? esc_attr( sanitize_text_field( $_GET[$gmw['url_px'].'lng'] ) ) : '';
	$url_px   = esc_attr( $gmw['url_px'] );
	
	$output = array();

    $output['wrap_start'] = "<div id=\"gmw-submit-wrapper-{$id}\" class=\"gmw-submit-wrapper\">";    
    $output['field_id']   = "<input type=\"hidden\" id=\"gmw-form-id-{$id}\" class=\"gmw-form-id gmw-form-id-{$id}\" name=\"{$url_px}form\" value=\"{$id}\" />";
        
    // set the page number to 1. We do this to reset the page number when form submitted again
    $output['field_page'] 	  = "<input type=\"hidden\" id=\"gmw-page-{$id}\" class=\"gmw-page gmw-page-{$id}\" name=\"paged\" value=\"1\" />";
    $output['field_per_page'] = "<input type=\"hidden\" id=\"gmw-per-page-{$id}\" class=\"gmw-per-page gmw-per-page-{$id}\" name=\"{$url_px}per_page\" value=\"{$per_page}\" />";   	
    $output['field_address']  = "<input type=\"hidden\" id=\"prev-address-{$id}\" class=\"prev-address prev-address-{$id}\" value=\"{$address}\"/>";
    $output['field_lat'] 	  = "<input type=\"hidden\" id=\"gmw-lat-{$id}\" class=\"gmw-lat gmw-lat-{$id}\" name=\"{$url_px}lat\" value=\"{$lat}\"/>";      	
    $output['field_lng'] 	  = "<input type=\"hidden\" id=\"gmw-lng-{$id}\" class=\"gmw-lng gmw-lng-{$id}\" name=\"{$url_px}lng\" value=\"{$lng}\"/>";
    $output['field_prefix']   = "<input type=\"hidden\" id=\"gmw-prefix-{$id}\" class=\"gmw-prefix gmw-prefix-{$id}\" name=\"{$url_px}px\" value=\"{$gmw['prefix']}\" />";   	
    $output['field_action']   = "<input type=\"hidden\" id=\"gmw-action-{$id}\" class=\"gmw-action gmw-action-{$id}\" name=\"action\" value=\"{$url_px}post\" />";
    $output['field_submit']   = $submit_button;  
    $output['wrap_end'] 	  = '</div>';

    $output  = apply_filters( 'gmw_from_submit_fields', $output, $gmw, $_GET );

    return implode( ' ', $output );
}

	function gmw_form_submit_fields( $gmw, $submit_value ) {
		echo gmw_get_form_submit_fields( $gmw, $submit_value );
	}

/**
 * GMW get address field
 * 
 * @param  array  $gmw    the form being used

 * @return mix            HTML element
 * 
 * @since 1.0
 */
function gmw_get_search_form_address_field( $gmw ) {

	$id    		  = absint( $gmw['ID'] );
    $mandatory 	  = isset( $gmw['search_form']['address_field']['mandatory'] ) ? 'mandatory' : '';
    $label 		  = ! empty( $gmw['search_form']['address_field']['title'] ) ? esc_attr( $gmw['search_form']['address_field']['title'] ) : '';
	$placeholder  = isset( $gmw['search_form']['address_field']['within'] ) ? $label : '';
	$autocomplete = isset( $gmw['search_form']['address_field']['address_autocomplete'] ) ? 'gmw-address-autocomplete' : '';
	$value		  = ! empty( $_GET[$gmw['url_px'].'address'] ) ? esc_attr( sanitize_text_field( stripslashes( implode( ' ', $_GET[$gmw['url_px'].'address'] ) ) ) ) : ''; 

	$locator 	= ( ! empty( $gmw['search_form']['locator']['enabled'] ) && ! empty( $gmw['search_form']['locator']['within'] ) ) ? true : false;
	$icon_usage = ( empty( $gmw['search_form']['locator']['usage'] ) || $gmw['search_form']['locator']['usage'] == 'icon' ) ? 'icon' : 'text';
	
    $output = '<div id="gmw-address-field-wrapper-'.$id.'" class="gmw-address-field-wrapper">';
    
    if ( empty( $gmw['search_form']['address_field']['within'] ) && ! empty( $label ) ) {
        $output .= '<label class="gmw-field-label" for="gmw-address-'.$id.'">'. $label .'</label>';
    }

    $output .= '<input type="text" name="'.esc_attr( $gmw['url_px'] ).'address[]" id="gmw-address-'.$id.'" autocomplete="off" class="gmw-form-field gmw-address gmw-full-address '.$mandatory.' '.$autocomplete.'" value="'.$value.'" placeholder="'.$placeholder.'" />';
    
    // if the locator button in within the address field
    if ( $locator && $icon_usage == 'icon' ) {
    	
    	$output .= gmw_get_locator_button( $gmw );
    }
    
    $output .= '</div>';
    
    //return the field
    return apply_filters( 'gmw_search_form_address_field', $output, $gmw );
}

	function gmw_search_form_address_field( $gmw, $id=false, $class=false ) {
		echo gmw_get_search_form_address_field( $gmw );
	}

/**
 * GMW geolocator button
 * 
 * @param  arrat $gmw  the form being used
 * @param  false $class deprecated
 * 
 * @return mix          HTML element of the locator button
 */
/*
deprecated
function gmw_get_search_form_locator_icon( $gmw ) {
	
	// abort if disabled
	if ( ! $gmw['search_form']['locator']['enabled'] ) {
		return;
	}

	// abort if displaying inside the address field
	if ( $gmw['search_form']['locator']['within'] && ( ! empty( $gmw['search_form']['locator']['usage'] ) && $gmw['search_form']['locator']['usage'] =='icon' ) ) {
		return;
	}
      
    $output = gmw_get_locator_button( $gmw );
    
    return apply_filters( 'gmw_search_form_locator_button', $output, $gmw );
}
*/
function gmw_search_form_locator_button( $gmw, $class=false ) {

	// abort if disabled
	if ( empty( $gmw['search_form']['locator']['enabled'] ) ) {
		return;
	}

	// abort if displaying inside the address field
	if ( $gmw['search_form']['locator']['within'] && ! empty( $gmw['search_form']['locator']['usage'] ) && $gmw['search_form']['locator']['usage'] != 'text' ) {
		return;
	}
	
	echo gmw_get_locator_button( $gmw );
}

/**
 * Generate the locator button
 * 
 * @param  [type] $gmw form being processed
 * 
 * @return HTML element
 */
function gmw_get_locator_button( $gmw ) {

	$icon     = $gmw['search_form']['locator']['icon'];
	$submit   = isset( $gmw['search_form']['locator']['submit'] ) ? '1' : '0';
	$id       = esc_attr( $gmw['ID'] );
	$usage    = ( empty( $gmw['search_form']['locator']['usage'] ) || $gmw['search_form']['locator']['usage'] == 'icon' ) ? 'icon' : 'text';
	$location = ( ! empty( $gmw['search_form']['locator']['within'] ) && $gmw['search_form']['locator']['usage'] != 'text' ) ? 'inside' : 'outside';

	$output = array();

	$output['open_element'] = '<div class="gmw-locator-btn-wrapper usage-'.$usage.' location-'.$location.'">'; 

	// when using an icon
	if ( $usage == 'icon' ) {
		
		if ( $icon == '_icon' ) {

			$output['icon'] = '<i id="gmw-locate-button-'.$id.'" class="gmw-locate-btn gmw-icon-target" data-locator_submit="'.$submit.'" data-form_id="'.$id.'"></i>';
		} else {
			
			$img = GMW_IMAGES .'/locator-images/'.$icon;

			$output['icon'] = '<img id="gmw-locate-button-'.$id.'" class="gmw-locate-btn" data-locator_submit="'.$submit.'" src="'.esc_url( $img ).'" data-form_id="'.$id.'" alt="'.__( 'locator button', 'GMW' ).'" />';
		}

	// text button
	} else {

		$label = ! empty( $gmw['search_form']['locator']['label'] ) ? esc_attr( $gmw['search_form']['locator']['label'] ) : '';

		$output['text'] = '<i id="gmw-locate-button-'.$id.'" class="gmw-locate-btn" data-locator_submit="'.$submit.'" data-form_id="'.$id.'">'. $label .'</i>';
	}
	
	$output['loader'] = '<i id="gmw-locate-loader-'.$id.'" class="gmw-locate-loader gmw-icon-spin-3 animate-spin" alt="Locator image loader" style="display:none;"></i>';
	
	$output['close_element'] = '</div>';

	$output = apply_filters( 'gmw_get_locator_button', $output, $gmw );

	return implode( ' ', $output );
}

/**
 * Radius dropdown
 *
 * @param array $gmw the form being processed
 * 
 * @return HTML select dropdown
 *
 * Since 1.0
 */
function gmw_get_search_form_radius( $gmw ) {
	
	if ( $gmw['search_form']['units'] == 'both' ) {
		$label = $gmw['labels']['search_form']['radius_within'];
	} else {
    	$label = $gmw['search_form']['units'] == 'imperial' ? $gmw['labels']['search_form']['miles'] : $gmw['labels']['search_form']['kilometers'];
	}

	$id      = absint( $gmw['ID'] );
	$label   = esc_attr( apply_filters( 'gmw_radius_dropdown_title', $label, $gmw ) );
	$values  = explode( ",", $gmw['search_form']['radius'] );
	$default = esc_attr( apply_filters( 'gmw_search_form_default_radius', end( $values ), $gmw ) );
	$url_px  = esc_attr( $gmw['url_px'] );
	$output  = '';

	if ( count( $values ) > 1 ) {
	    	
        $output .= '<select id="gmw-distance-'.$id.'" class="gmw-form-field gmw-distance" name="'.$url_px.'distance">';
        $output .= 	'<option value="'.$default.'">'.esc_html( $label ).'</option>';

        foreach ( $values as $value ) {
      
        	if ( ! is_numeric( $value ) ) {
        		continue;
        	}

            if ( isset( $_GET[$gmw['url_px'].'distance'] ) && $_GET[$gmw['url_px'].'distance'] == $value ) {
                $value_s = 'selected="selected"';
            } else {
                $value_s = "";
            }
            $output .= '<option value="'.$value.'" '.$value_s.'>'.$value.'</option>';
            
        }
        $output .= "</select>";
        
	} else {
        $output = '<input type="hidden" name="'.$url_px.'distance" value="'.$default.'" />';
	}
	
	$output = apply_filters( "gmw_radius_dropdown_output", $output, $gmw );
	$output = apply_filters( "gmw_radius_dropdown_output_{$gmw['ID']}", $output, $gmw );
	
    return $output;
}

	function gmw_search_form_radius( $gmw ) {
		echo gmw_get_search_form_radius( $gmw );
	}

/**
 * GMW Search form units 
 * 
 * @param  array $gmw the form being used
 * 
 * @return HTML element 
 */
function gmw_get_search_form_units( $gmw ) {
	
	$id     = absint( $gmw['ID'] );
	$url_px = esc_attr( $gmw['url_px'] );

	if ( $gmw['search_form']['units'] == 'both' ) {
	        
        $selected = ( isset( $_GET[$gmw['url_px'].'units'] ) && $_GET[$gmw['url_px'].'units'] == 'metric' ) ? 'selected="selected"' : '';
  
        $output  = '<select name="'.$url_px.'units" id="gmw-units-'.$id.'" class="gmw-form-field gmw-units">';
        $output .= '<option value="imperial" selected="selected">'.esc_html( $gmw['labels']['search_form']['miles'] ).'</option>';
        $output .= '<option value="metric" '.$selected.'>'.esc_html( $gmw['labels']['search_form']['kilometers'] ).'</option>';
        $output .= "</select>";

	} else {
        $output = '<input type="hidden" name="'.$url_px.'units" value="'.esc_attr( sanitize_text_field( $gmw['search_form']['units'] ) ).'" />';
	} 
	
	$output = apply_filters( "gmw_search_form_units", $output, $gmw );
	$output = apply_filters( "gmw_search_form_unit_{$gmw['ID']}", $output, $gmw );
	
	return $output;
}
	function gmw_search_form_units( $gmw, $class=false ) {
		echo gmw_get_search_form_units( $gmw );
	}

/**
 * Generate the results count message.
 * 
 * @param  array  $gmw the form being used
 * @param  array $args the results message elements
 * @return string      results message
 */
function gmw_get_results_message( $gmw, $args=false )  {

	// if no args pass use the default
	if ( empty( $args ) ) {
	
		if ( $gmw['prefix'] == 'pt' ) {
			$args = array(
				'message' 		=> $gmw['labels']['search_results']['pt_results_message'],
				'count'	  		=> $gmw['results_count'],
				'total_count'	=> $gmw['total_results'],
				'distance'		=> $gmw['radius'],
				'units'			=> $gmw['units_array']['name'],
				'address'		=> $gmw['org_address'],
				'showing'		=> true,
				'within'		=> true
			);			
		} elseif ( $gmw['prefix'] == 'fl' ) {
			global $members_template;
			$args = array(
				'message' 		=> $gmw['labels']['search_results']['fl_results_message'],
				'count'	  		=> $members_template->member_count,
				'total_count'	=> $members_template->total_member_count,
				'distance'		=> $gmw['radius'],
				'units'			=> $gmw['units_array']['name'],
				'address'		=> $gmw['org_address'],
				'showing'		=> false,
				'within'		=> true
			);
		}

		//allow plugins to modify or create thier own
		$args = apply_filters( 'gmw_results_message_args', $args, $gmw );
	}

	$output = '';
		
	if ( $args['showing'] ) {
		$output .= sprintf( $args['message']['showing'], $args['count'], $args['total_count'] );
	}
	
	if ( $args['within'] && $gmw['submitted'] && ! empty( $args['address'] ) ) {
		$output .= ' '.sprintf( $args['message']['within'], $args['distance'].' '.$args['units'], $args['address'] );
	}

	return esc_html( $output );
}

	function gmw_results_message( $gmw, $args ) {
		echo gmw_get_results_message( $gmw, $args );
	}

/**
 * GMW Pagination function
 *
 * This function uses the WordPress function paginate_links();
 * 
 * @version 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_pagination( $gmw, $page_name, $max_pages ) {

	$max_pages   = ceil( $max_pages );
	$paged_check = ( is_front_page() ) ? 'page' : $page_name;

	// pagination arguments. 
	$args = apply_filters( 'gmw_get_pagination_args', array(
		'base'         		 => add_query_arg( $page_name, '%#%' ),
		'format'       		 => '',
		'total'        		 => $max_pages,
		'current'      		 => max( 1, get_query_var( $paged_check ) ),
		'show_all'     		 => False,
		'end_size'     		 => 1,
		'mid_size'     		 => 2,
		'prev_next'    		 => True,
		'prev_text'    		 => $gmw['labels']['pagination']['prev'],
		'next_text'    		 => $gmw['labels']['pagination']['next'],
		'type'         	 	 => 'array',
		'add_args'     		 => False,
		'add_fragment' 		 => '',
		'before_page_number' => '',
		'after_page_number'  => ''
	), $gmw, $page_name, $max_pages );

	// generate the link
	$pags = paginate_links( $args );

	$output = '';

	if ( is_array( $pags ) ) {
		$output = '<ul class="gmw-pagination">';
		foreach ( $pags as $link ) {
			$output .= '<li>'.$link.'</li>';
		}
		$output .= '</ul>';
	}

	return apply_filters( 'gmw_get_pagination_output', $output, $pags, $args, $gmw, $page_name, $max_pages );
}

	function gmw_pagination( $gmw, $page_name, $max_pages ) {
		echo gmw_get_pagination( $gmw, $page_name, $max_pages );
	}

/**
 * GMW per page dropdown
 * 
 * @since 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_per_page( $gmw, $total_results, $page_name ) {
  
    $per_page 	  = ( $gmw['page_load_action'] ) ? explode( ",", $gmw['page_load_results']['per_page'] ) : explode( ",", $gmw['form_submission']['per_page'] );
    $paged    	  = $gmw['paged'];
    $url_px   	  = esc_attr( $gmw['url_px'] );
    $current_page = ! empty( $_GET[$gmw['url_px'].'per_page'] ) ? $_GET[$gmw['url_px'].'per_page'] : reset( $per_page );
    $output = '';

    if ( count( $per_page ) > 1 ) {

        $output .= '<select name="'.$url_px.'per_page" class="gmw-per-page" data-form_id="'.absint( $gmw['ID'] ).'" data-per_page="'.esc_attr( $current_page ).'" data-total_results="'.esc_attr( $total_results ).'" data-page_name="'.esc_attr( $page_name ).'" data-paged="'.esc_attr( $paged ).'" data-gmw_post="'.esc_attr( $gmw['submitted'] ).'" data-url_px="'.esc_attr( $url_px ).'">';

        foreach ( $per_page as $pp ) {

            $selected = ( isset( $_GET[$gmw['url_px'].'per_page'] ) && $_GET[$gmw['url_px'].'per_page'] == $pp ) ? 'selected="selected"' : '';
                        
            $output .= '<option value="'.$pp.'" '.$selected.'>'.$pp.' '.$gmw['labels']['form_submission']['per_page'].'</option>';
        }
        
        $output .= '</select>';
    }

    return apply_filters( 'gmw_per_page', $output, $gmw, $total_results, $page_name );
}
	
	function gmw_per_page( $gmw, $total_results, $page_name ) {
		echo gmw_get_per_page( $gmw, $total_results, $page_name );
	}

/**
 * Get the distance to location
 * 
 * @param  object $info the item info
 * @param  array  $gmw  the form being used
 * 
 * @return string       distance + units
 */
function gmw_get_distance_to_location( $info, $gmw ) {
	

	if ( empty( $info->distance ) ) {
		return;
	}
	
	$distance = $info->distance . ' ' . $gmw['units_array']['name'];
	
	$distance = apply_filters( "gmw_distance_to_location", $distance, $info, $gmw );
	$distance = apply_filters( "gmw_distance_to_location_{$gmw['ID']}", $distance, $info, $gmw );
	
	return esc_attr( $distance );
}

	function gmw_distance_to_location( $info, $gmw ) {
		echo gmw_get_distance_to_location( $info, $gmw );
	}
	
/**
 * Calculate the distance between two points
 * 
 * @param  [type] $start_lat latitude of start point
 * @param  [type] $start_lng longitude of start point
 * @param  [type] $end_lat   latitude of end point
 * @param  [type] $end_lng   longitude of end point
 * @param  string $units     m for miles k for kilometers
 * 
 * @return [type]            [description]
 */
function gmw_calculate_distance( $start_lat, $start_lng, $end_lat, $end_lng, $units="m" ) {

	$theta 	  = $start_lng - $end_lng;
	$distance = sin( deg2rad( $start_lat ) ) * sin( deg2rad( $end_lat ) ) +  cos( deg2rad( $start_lat ) ) * cos( deg2rad( $end_lat ) ) * cos( deg2rad( $theta ) );
	$distance = acos( $distance );
	$distance = rad2deg( $distance );
	$miles 	  = $distance * 60 * 1.1515;

	if ( $units == "k" ) {
		$distance = ( $miles * 1.609344 );
	} else {
		$distance = ( $miles * 0.8684 );
	}

	return round( $distance, 2 );
}

/**
 * Display excerpt
 *
 * Display only a specific number of words and add a read more link to 
 * the content.
 * 
 * @param unknown_type $post
 * @param unknown_type $gmw
 * 
 * @param unknown_type $count
 */
function gmw_get_excerpt( $info, $gmw, $content, $count, $read_more=false ) {

	$content = apply_filters( 'gmw_except_content', $content, $info, $gmw );

	if ( empty( $content ) ) {
		return;
	}

	// trim number of words
	if ( ! empty( $count ) ) {

		// generate read more link
		if ( ! empty( $read_more ) ) {		
			$more_link = apply_filters( 'gmw_excerpt_more_link', ' <a href="'.get_the_permalink( $info->ID ).'" class="gmw-more-link" title="read more">'.esc_html( $read_more ).'</a>', $info, $gmw, $content, $count, $read_more );
		} else {
			$more_link = '';
		}
		$content = wp_trim_words( $content, $count, $more_link );
	}	
	
	// enable/disable shortcodes in excerpt
	if ( ! apply_filters( 'gmw_except_shortcodes_enabled', true, $info, $gmw ) ) {
		$content = strip_shortcodes( $content );
	}
	
	$content = apply_filters( 'the_content', $content );	
	$content = str_replace( ']]>', ']]&gt;', $content );
	
	return $content;
}
	function gmw_excerpt( $info, $gmw, $content, $count, $read_more=false ) {
		echo gmw_get_excerpt( $info, $gmw, $content, $count, $read_more );
	}
	
/**
 * Output list of opening hours
 * 
 * @param  int | object  $location location ID or location object
 * @param  array 		 $gmw      the form being processes if in search results
 * @return HTML list
 */
function gmw_get_opening_hours( $location, $gmw=array() ) {

	// if location ID
	if ( is_int( $location ) ) {
	
		$days_hours = gmw_get_location_meta( $location, 'days_hours' );
	
	} elseif ( is_object( $location ) && ! empty( $location->object_type ) && ! empty( $location->object_id ) ) {
		
		$days_hours = gmw_get_location_meta_by_object( $location->object_type, $location->object_id, 'days_hours' );
	
	} else {
		return;
	}
    
    $output = array();
    $data   = '';
    $count  = 0;

    if ( ! empty( $days_hours ) && is_array( $days_hours ) ) {

        foreach ( $days_hours as $day ) {

            if ( array_filter( $day ) ) {

                $count++;
                $data .= '<li><span class="days">'.esc_attr( $day['days'] ).': </span><span class="hours">'.esc_attr( $day['hours'] ).'</span></li>';
            }
        }
    }

    $output['wrap']  = '<div class="opening-hours-wrapper">';
    $output['title'] = '<h4>'. esc_attr( $gmw['labels']['search_results']['opening_hours'] ).'</h4>';

    if ( $count > 0 ) {
  		
  		$output['ul']   = '<ul class="days-hours">';
        $output['data'] = $data;
        $output['/ul']  = '</ul>';
      
    } else {

        $output['na'] ='<p class="days-na">' . __( 'N/A' ) . '</p>';
    }
    
    $output['/wrap'] = '</div>';
    
    $output = apply_filters( 'gmw_opening_hours_output', $output, $location, $days_hours, $data );

    return implode( ' ', $output );
}

    function gmw_opening_hours( $location, $gmw=array() ) {
        echo gmw_get_opening_hours( $location, $gmw );
    }

/**
 * Pop-up Info window function - info window toggle
 * @param $gmw     - current form being used
 * @param $info    - $post, $member or so on
 * @param $id      - ID tag for the button
 * @param $toggled - id of the element to be toggled. Dont use # just the tag name
 * $param $showIcon - fontawesome to be used for the show info button
 * $param $hideIcon - fontawesome to be used for the hide info button
 * @param $animation - true | false - animation will use animated height to show/hide. Otherwise will use jQuery slideToggle
 * @param minHeight  - value to be used with animation. The height when the element is hidden.
 * @param maxHeight  - value to be used with animation. The height when the element is visible.
 * 
 */
function gmw_window_toggle( $gmw, $info, $id, $toggled, $showIcon, $hideIcon, $animation, $minHeight, $maxHeight ) {

	$id 	     = ( !empty( $id ) ) 		? $id   	 : 'gmw-window-toggle-'.rand(10, 999);
	$showIcon    = ( !empty( $showIcon ) )  ? $showIcon  : 'dashicons-arrow-down-alt2';
	$hideIcon    = ( !empty( $hideIcon ) )  ? $hideIcon  : 'dashicons-arrow-up-alt2';
	$toggled     = ( !empty( $toggled ) )   ? '#'.$toggled  : '#'.$id;
	$minHeight 	 = ( !empty( $minHeight ) ) ? $minHeight : '30px';
	$maxHeight 	 = ( !empty( $maxHeight ) ) ? $maxHeight : '100%';
	
	if ( $animation != false ) {
		$slideToggle = '0';
	} else {
		$slideToggle = '1';
		$animation   = 'height';
	}	
	
	echo apply_filters( "gmw_{$gmw['prefix']}_window_toggle", "<a href=\"#\" id=\"{$id}\" class=\"gmw-window-toggle gmw-{$gmw['prefix']}-window-toggle dashicons {$hideIcon}\" title=\"Hide information\"></a>", $info, $gmw, $hideIcon, $showIcon );
	
	echo "<script>
	jQuery(document).ready(function($) {

		$('#{$id}').click(function(e) {
			e.preventDefault();

			if ( '{$slideToggle}' == '1' ) {		
				var toggle = $(this);
				$('{$toggled}').slideToggle(function() {
					toggle.toggleClass('{$showIcon} {$hideIcon}');
				});
			
			} else {
				var toggle    = $(this);
				var toggled   = $('{$toggled}');
				var minHeight = '{$minHeight}';
				var maxHeight = '{$maxHeight}';
				
				if ( toggled.hasClass('open') ) {
					toggled.css('overflow','hidden');
					toggled.animate({{$animation}:minHeight},200, function() {
						toggle.toggleClass('{$showIcon} {$hideIcon}');
						toggle.attr('title', 'Show information');
					}).toggleClass('open');
				} else {
					toggled.animate({{$animation}:maxHeight},200, function() {
						toggle.toggleClass('{$showIcon} {$hideIcon} open');
						toggle.attr('title', 'Hide information');
						toggled.css('overflow','initial');
					}).toggleClass('open');				
				}
			}
		});
	});
	</script>";	
}

/**
 * Pop-up Info window function - display draggable icon
 * @param unknown_type $gmw
 * @param unknown_type $info ( $post, $member...)
 * @param unknown_type $draggable - the element to be draggble
 * @param unknown_type $handle - the element to be used as dragging handle. False for no hangle.
 * @param unknown_type $handleIcon - fontawesome to be used as the handle icon\
 * @param unknown_type $containment - the element of the draggable area.
 */
function gmw_get_draggable_handle( $gmw, $info, $draggable, $handle, $handleIcon, $containment ) {
		
	$handleIcon = ( !empty( $handleIcon ) ) ? $handleIcon : 'dashicons-editor-justify';	
	
	echo "<script>
	jQuery(document).ready(function($) {

		var handle 		= ( '{handle}' != '' ) 	  ? '#gmw-drag-area-handle-{$gmw['ID']}' : false;
		var containment = ( '{$containment}' != '' ) ? '{$containment}' : false;

		$( document.getElementById('{$draggable}') ).draggable({
			containment: containment,
			handle: handle,
		});
	});
	</script>";
	
	if ( $handle == true ) {
		return apply_filters( "gmw_{$gmw['prefix']}_get_draggable_handle", "<a href=\"#\" id=\"gmw-drag-area-handle-{$gmw['ID']}\" class=\"gmw-drag-area-handle gmw-{$gmw['prefix']}-drag-area-handle dashicons {$handleIcon}\" onclick=\"event.preventDefault()\"></a>", $info, $gmw, $handleIcon );
	} else {
		return false;
	}
}

/**
 * GMW function - display close icon -to be used mostly with info-windows and info-boxes
 * @param unknown_type $post
 * @param unknown_type $gmw
 */
function gmw_get_close_button( $info, $gmw, $icon, $prefix ) {

	$icon   = ( !empty( $icon ) ) ? $icon : 'dashicons-no';
	return	apply_filters( "gmw_{$gmw['prefix']}_{$prefix}_get_close_button", "<a id=\"gmw-close-button-{$gmw['ID']}\" class=\"gmw-close-button gmw-{$prefix}-close-button gmw-{$gmw['prefix']}-{$prefix}-close-button dashicons {$icon}\"></a>", $info, $gmw, $icon, $prefix );
}

/**
 * Display xprofile fields in info window
 * 
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_iw_xprofile_fields( $gmw ) {

	if ( empty( $gmw['info_window']['profile_fields'] ) && empty( $gmw['info_window']['profile_fields_date'] ) ) {
		return;
	}

	if ( empty( $gmw['info_window']['profile_fields'] ) ) {
		$gmw['info_window']['profile_fields'] = array();
	}

	if ( !empty( $gmw['info_window']['profile_fields_date'] ) ) {
		array_unshift( $gmw['info_window']['profile_fields'], $gmw['info_window']['profile_fields_date'] );
	}

	gmw_fl_member_xprofile_fields( $gmw, $gmw['info_window']['profile_fields'], 'ul' );
}

/**
 * GMW FL search results function - xprofile fields
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_member_xprofile_fields( $gmw, $fields, $tag ) {

	if ( empty( $fields ) ) {
		return;
	}
	
	if ( $tag == 'ul' || $tag == 'li' ) {
		$subTag = 'li';
	} else {
		$tag = $subTag = 'div';
	}
	
	global $members_template;

	echo '<'.$tag.' class="gmw-xprofile-fields-wrapper">';

	foreach ( $fields as $field_id) {
			
		$field_data = new BP_XProfile_Field( $field_id );
		$field_value = xprofile_get_field_data($field_id, $members_template->member->ID);
			
		if ( $field_data->type == 'datebox')  {
			$field_value 	  = ( date ( "Y", time() ) - $field_value) . ' ' . __( ' Years old','GMW' );
			$field_data->name = __( 'Age','GMW' );
		}
			
		echo '<'.$subTag.' class="gmw-fl-profile-field field_'.$field_data->type.' field_'.$field_id.'">';
		echo '<span class="label">'.$field_data->name . ':</span>';
		echo '<span class="field">';

		if ( !empty( $field_value ) ) {
			echo ( is_array($field_value) ) ? implode(', ' , $field_value) : $field_value;
		} else {
			echo __('N/A','GMW');
		}
		echo '</span>';
		echo '</'.$subTag.'>';
	}

	echo '</'.$tag.'>';
}

/**
 * GMW function - Driving distance.
 * @version 1.0
 * @author Eyal Fitoussi
 *
 * Deprectaed 
 */
function gmw_driving_distance( $info, $gmw, $title ) {
	return false;
	/*
	if ( empty( $gmw['lat'] ) || empty( $gmw['lng'] ) )
		return;
	
	if ( !$info->lat || !$info->lng )
		return;
	
	if ( $info->lat == 0.000000 || $info->lat == 0.000000 )
		return;
	
	if ( empty( $title ) )
		 $title = $gmw['labels']['search_results']['driving_distance'];
	
	$unitsSystem = strtoupper($gmw['units_array']['units']);
	
	echo '<div id="gmw-driving-distance-'.$info->ID.'" class="gmw-driving-distance gmw-'.$gmw['prefix'].'-driving-distance"><span class="label">'.$title.' </span><span class="distance"></span></div>';
	
    echo "<script>
        var directionsDisplay;
        var directionsService = new google.maps.DirectionsService();
        var directionsDisplay = new google.maps.DirectionsRenderer();

        var start = new google.maps.LatLng('{$gmw['lat']}', '{$gmw['lng']}');
        var end = new google.maps.LatLng('{$info->lat}', '{$info->lng}');
        var request = {
            origin: start,
            destination: end,
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.{$unitsSystem}
        };
        
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);              
                jQuery('#gmw-driving-distance-{$info->ID} span.distance').text(result.routes[0].legs[0].distance.text);
            }
        });
    </script>";
    */
}

/*
*** deprecated
*/
function gmw_no_results_found( $gmw, $message ) {

	if ( empty( $message ) )
		$message = $gmw['labels']['search_results'][$gmw['prefix'].'_no_results'];
	
	$class = ( isset( $gmw['location']['error'] ) ) ? 'gmw-geocode-error' : '';
		
	do_action( 'gmw_'.$gmw['prefix'].'_before_no_results', $gmw  );
	
	$no_results = '<div class="gmw-no-results-wrapper gmw-'.$gmw['prefix'].'-no-results-wrapper '.$class.'"><p>'.$message.'</p></div>';
	echo apply_filters( 'gmw_'.$gmw['prefix'].'_no_results_message', $no_results, $gmw, $message );
	
	do_action( 'gmw_'.$gmw['prefix'].'_after_no_results', $gmw );
}