<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * Set labels
 * most of the labels of the forms ares set below.
 * it makes it easier to manager and it is now possible to modify a single or multiple
 * labels using the filter provided instead of using the translation files.
 *
 * You can create a custom function in the functions.php file of your theme and hook it using the filter gmw_shortcode_set_labels.
 * You should check for the $form['ID'] in your custom function to make sure the function apply only for the wanted forms.
 * @since 2.5
 */
function gmw_form_set_labels( $form ) {

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
					'per_page'			=> __( 'per page', 'GMW' ),
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
					'member_info'	     => __( 'Member Information', 'GMW' ),
			),
			'live_directions'	=> array(
					'start_point' 		=> __( 'Start point', 'GMW' ),
					'end_point' 		=> __( 'Destination point', 'GMW' ),
					'directions_label'	=> __( 'Directions', 'GMW' ),
					'from'				=> __( 'From:' , 'GMW' ),
					'to'				=> __( 'To:' , 'GMW' ),
					'driving'			=> __( 'Driving' , 'GMW' ),
					'walking'			=> __( 'Walking' , 'GMW' ),
					'bicycling'			=> __( 'Bicycling' , 'GMW' ),
					'transit'			=> __( 'Transit' , 'GMW' ),
					'units_label'		=> __( 'Distance Units' , 'GMW' ),
					'units_mi'			=> __( 'Miles' , 'GMW' ),
					'units_km'			=> __( 'Kilometers' , 'GMW' ),
					'avoid_label'		=> __( 'Avoid' , 'GMW' ),
					'avoid_hw'			=> __( 'highways' , 'GMW' ),
					'avoid_tolls'		=> __( 'Tolls' , 'GMW' ),
			)
	);
	
	//modify the labels
	$labels = apply_filters( "gmw_set_labels", $labels, $form );
	$labels = apply_filters( "gmw_set_labels_{$form['ID']}", $labels, $form );
	$labels = apply_filters( "gmw_{$form['prefix']}_set_labels", $labels, $form );
	
	return $labels;
}

/**
 * GMW get the map element
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_results_map( $gmw ) {
		
	$gmw = apply_filters( "gmw_map_output_args", $gmw );
    $gmw = apply_filters( "gmw_map_output_args_{$gmw['ID']}", $gmw );
    $gmw = apply_filters( "gmw_{$gmw['prefix']}_map_output_args", $gmw );

    $frame = ( isset( $gmw['results_map']['map_frame'] ) ) ? 'gmw-map-frame' : '';

    if ( !empty( $gmw['expand_map_on_load'] ) || !empty( $gmw['general_settings']['pl_expand_map'] ) ) {
    	$expanded = 'gmw-expanded-map';
    	$display  = '';
    	$trigger  = 'fa fa-compress';
    } else {
    	$display  = 'display:none;';
    	$expanded = '';
    	$trigger  = 'fa fa-expand';
   	}
		
	if ( $gmw['addon'] == 'global_maps' ) {
		$display  = '';
	}

    $output['open']    		  = "<div id=\"gmw-map-wrapper-{$gmw['ID']}\" class=\"gmw-map-wrapper gmw-map-wrapper-{$gmw['ID']} gmw-{$gmw['addon']}-map-wrapper gmw-{$gmw['prefix']}-map-wrapper {$frame} {$expanded}\"  style=\"{$display}width:{$gmw['results_map']['map_width']};height:{$gmw['results_map']['map_height']};\">";
    $output['resize_toggle']  = '<span id="gmw-resize-map-trigger-'.$gmw['ID'].'" class="gmw-resize-map-trigger gmw-'.$gmw['prefix'].'-resize-map-trigger '.$trigger.'" style="display:none;" title="'.__( 'Resize map','GMW' ) .'"></span>';
    $output['map']     		  = "<div id=\"gmw-map-{$gmw['ID']}\" class=\"gmw-map gmw-{$gmw['prefix']}-map\" style=\"width:100%; height:100%\"></div>";
    $output['loader'] 	  	  = "<i id=\"gmw-map-loader-{$gmw['ID']}\" class=\"gmw-map-loader gmw-{$gmw['prefix']}-map-loader fa fa-spinner fa-spin fa-3x fa-fw\"></i>";
    $output['close']   		  = '</div>';

    //modify the map element
    $output = apply_filters( "gmw_map_output", $output, $gmw );
    $output = apply_filters( "gmw_map_output_{$gmw['ID']}", $output, $gmw );
    $output = apply_filters( "gmw_{$gmw['prefix']}_map_output", $output, $gmw );
    
    return implode( ' ', $output );
}

	function gmw_results_map( $gmw ) {
		
		//temporary. needs to be removed.
		if ( $gmw['search_results']['display_map'] != 'results' && empty( $gmw['map_shortcode'] ) )
			return;
			
	    do_action( "gmw_before_map", 				  $gmw );
		do_action( "gmw_before_map_{$gmw['ID']}", 	  $gmw );
		do_action( "gmw_{$gmw['prefix']}_before_map", $gmw );
	    
	    echo gmw_get_results_map( $gmw );
	  
	    do_action( "gmw_after_map", 				 $gmw );
		do_action( "gmw_after_map_{$gmw['ID']}", 	 $gmw );
		do_action( "gmw_{$gmw['prefix']}_after_map", $gmw );
	}

/**
 * GMW Search Form
 * 
 * @param unknown_type $gmw
 * @param unknown_type $folder
 */
function gmw_get_search_form( $gmw, $folder ) {
	
	$sForm   = $gmw['search_form']['form_template'];	
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
	
	if ( !empty( $folder ) ) {
		$folders = array_merge( $folders, $folder );
	}
	
	if ( empty( $folders[$gmw['prefix']] ) ) {
		return;
	}
	
	//Load custom search form and css from child/theme folder
	if ( strpos( $sForm, 'custom_' ) !== false ) {
		$sForm  						  = str_replace( 'custom_', '', $sForm );
		$search_form['stylesheet_handle'] = "gmw-{$gmw['ID']}-{$gmw['prefix']}-search-form-{$sForm}";
		$search_form['stylesheet_url']	  = get_stylesheet_directory_uri()."/geo-my-wp/{$folders[$gmw['prefix']]['custom']}{$sForm}/css/style.css";
		$search_form['content_path'] 	  = STYLESHEETPATH . "/geo-my-wp/{$folders[$gmw['prefix']]['custom']}{$sForm}/search-form.php";
	} else {
		$search_form['stylesheet_handle'] = "gmw-{$gmw['ID']}-{$gmw['prefix']}-search-form-{$sForm}";
		$search_form['stylesheet_url'] 	  = $folders[$gmw['prefix']]['url'].$sForm.'/css/style.css';
		$search_form['content_path']      = $folders[$gmw['prefix']]['path'].$sForm.'/search-form.php';
	}
	
	return $search_form;
}

	function gmw_search_form( $gmw, $folder=false ) {

		$search_form = gmw_get_search_form( $gmw, $folder );

		if ( !wp_style_is( $search_form['stylesheet_handle'], 'enqueued' ) ) {
			wp_enqueue_style( $search_form['stylesheet_handle'], $search_form['stylesheet_url'], array(), GMW_VERSION );
		}

		do_action( "gmw_before_search_form", $gmw );
		do_action( "gmw_before_search_form_{$gmw['ID']}", $gmw );
		do_action( "gmw_{$gmw['prefix']}_before_search_form", $gmw );

		include( $search_form['content_path'] );

		do_action( "gmw_after_search_form", $gmw );
		do_action( "gmw_after_search_form_{$gmw['ID']}", $gmw );
		do_action( "gmw_{$gmw['prefix']}_after_search_form", $gmw );
	}

/**
 * Form submission fields
 * @param  array $gmw       the form being used
 * @param  string $subValue the default value of the submit button
 * @return mix              HTML elements of the submission fields
 */
function gmw_get_form_submit_fields( $gmw, $subValue ) {
	
	$subValue 	   = ( !empty( $subValue ) ) ? $subValue : $gmw['labels']['search_form']['submit'];
	$submit_button = '<input type="submit" id="gmw-submit-'.$gmw['ID'].'" class="gmw-submit gmw-submit-'.$gmw['ID'].'" value="'.esc_attr( $subValue ).'" />';
	$submit_button = apply_filters( 'gmw_form_submit_button', $submit_button, $gmw, $subValue ); 

	$per_page = current( explode( ",", absint( $gmw['search_results']['per_page'] ) ) );
	$address  = ( !empty( $_GET[$gmw['url_px'].'address'] ) ) ? esc_attr( sanitize_text_field( stripslashes( implode( ' ', $_GET[$gmw['url_px'].'address'] ) ) ) ) : '';
	$lat	  = ( !empty( $_GET[$gmw['url_px'].'lat'] ) ) 	  ? esc_attr( sanitize_text_field( $_GET[$gmw['url_px'].'lat'] ) ) : '';
	$lng	  = ( !empty( $_GET[$gmw['url_px'].'lng'] ) ) 	  ? esc_attr( sanitize_text_field( $_GET[$gmw['url_px'].'lng'] ) ) : '';
	$url_px   = esc_attr( $gmw['url_px'] );
	
	$output = array();

    $output['wrap_start'] = "<div id=\"gmw-submit-wrapper-{$gmw['ID']}\" class=\"gmw-submit-wrapper gmw-submit-wrapper-{$gmw['ID']}\">";    
    $output['field_id']   = "<input type=\"hidden\" id=\"gmw-form-id-{$gmw['ID']}\" class=\"gmw-form-id gmw-form-id-{$gmw['ID']}\" name=\"{$url_px}form\" value=\"{$gmw['ID']}\" />";
        
    //set the page number to 1. We do this to reset the page number when form submitted again
    $output['field_page'] 	  = "<input type=\"hidden\" id=\"gmw-page-{$gmw['ID']}\" class=\"gmw-page gmw-page-{$gmw['ID']}\" name=\"paged\" value=\"1\" />";
    $output['field_pre_page'] = "<input type=\"hidden\" id=\"gmw-per-page-{$gmw['ID']}\" class=\"gmw-per-page gmw-per-page-{$gmw['ID']}\" name=\"{$url_px}per_page\" value=\"{$per_page}\" />";   	
    $output['field_address']  = "<input type=\"hidden\" id=\"prev-address-{$gmw['ID']}\" class=\"prev-address prev-address-{$gmw['ID']}\" value=\"{$address}\"/>";
    $output['field_lat'] 	  = "<input type=\"hidden\" id=\"gmw-lat-{$gmw['ID']}\" class=\"gmw-lat gmw-lat-{$gmw['ID']}\" name=\"{$url_px}lat\" value=\"{$lat}\"/>";      	
    $output['field_lng'] 	  = "<input type=\"hidden\" id=\"gmw-long-{$gmw['ID']}\" class=\"gmw-lng gmw-long-{$gmw['ID']}\" name=\"{$url_px}lng\" value=\"{$lng}\"/>";
    $output['field_prefix']   = "<input type=\"hidden\" id=\"gmw-prefix-{$gmw['ID']}\" class=\"gmw-prefix gmw-prefix-{$gmw['ID']}\" name=\"{$url_px}px\" value=\"{$gmw['prefix']}\" />";   	
    $output['field_action']   = "<input type=\"hidden\" id=\"gmw-action-{$gmw['ID']}\" class=\"gmw-action gmw-action-{$gmw['ID']}\" name=\"action\" value=\"{$url_px}post\" />";
    $output['field_submit']   = $submit_button;  
    $output['wrap_end'] 	  = '</div>';

    $output  = apply_filters( 'gmw_from_submit_fields', $output, $gmw, $_GET );

    return implode( ' ', $output );
}

	function gmw_form_submit_fields( $gmw, $subValue ) {
		echo gmw_get_form_submit_fields( $gmw, $subValue );
	}

/**
 * GMW get address field
 * @param  array  $gmw    the form being used
 * @param  false $id      deprecated
 * @param  string  $class additional classes for the input field
 * @return mix            HTML element
 * @since 1.0
 */
function gmw_get_search_form_address_field( $gmw, $id=false, $class='') {

    $am 		 = ( isset(  $gmw['search_form']['address_field']['mandatory'] ) ) ? 'mandatory' : '';
    $title 		 = ( !empty( $gmw['search_form']['address_field']['title'] ) ) ? $gmw['search_form']['address_field']['title'] : '';
	$value		 = ( !empty( $_GET[$gmw['url_px'].'address'] ) ) ? esc_attr( sanitize_text_field( stripslashes( implode( ' ', $_GET[$gmw['url_px'].'address'] ) ) ) ) : ''; 
	$placeholder = ( isset(  $gmw['search_form']['address_field']['within'] ) ) ? $title : '';

    $output = '<div id="gmw-address-field-wrapper-'.$gmw['ID'].'" class="gmw-address-field-wrapper gmw-address-field-wrapper-'.$gmw['ID'].' '.esc_attr( $class ) .'">';
    
    if ( !isset( $gmw['search_form']['address_field']['within'] ) && !empty( $title ) ) {
        $output .= '<label class="gmw-field-label" for="gmw-address-'.$gmw['ID'].'">'.esc_attr( $title ).'</label>';
    }

    $output .= '<input type="text" name="'.esc_attr( $gmw['url_px'] ).'address[]" id="gmw-address-'.$gmw['ID'].'" autocomplete="off" 
    		class="gmw-address gmw-full-address gmw-address-'.$gmw['ID'].' '.$class.' '.$am.'" 
    		value="'.$value.'" placeholder="'.esc_attr( $placeholder ).'" />';
    
    if ( $gmw['search_form']['locator_icon'] == 'within_address_field' ) {
    	
    	$lSubmit = ( isset( $gmw['search_form']['locator_submit'] ) ) ? 'gmw-locator-submit' : '';
    	
    	$output .= '<div class="gmw-locator-btn-wrapper gmw-locator-btn-within-wrapper">';
    	$output .= "<i id=\"{$gmw['ID']}\" class=\"fa fa-map-marker gmw-locator-btn-within gmw-locator-button gmw-locate-btn {$lSubmit}\"></i>";
    	$output .= "<i id=\"gmw-locator-btn-loader-{$gmw['ID']}\" class=\"gmw-locator-btn-loader fa fa-refresh fa-spin\" alt=\"Locator image loader\" style=\"display:none;\"></i>";
    	$output .= '</div>';
    }
    
    $output .= '</div>';

    if ( isset( $gmw['search_form']['address_field']['address_autocomplete'] ) ) {
    	GEO_my_WP::google_places_address_autocomplete( array( 'gmw-address-'.$gmw['ID'] ) );
    }
    
    return apply_filters( 'gmw_search_form_address_field', $output, $gmw, false, $class );
}

	function gmw_search_form_address_field( $gmw, $id=false, $class='' ) {
		echo gmw_get_search_form_address_field( $gmw, $id, $class );
	}

/**
 * GMW auto-locator button
 * @param  arrat  $gmw  the form being used
 * @param  false $class deprecated
 * @return mix          HTML element of the locator button
 */
function gmw_get_search_form_locator_icon( $gmw, $class=false ) {
	
	//abort if no icon or if icon is within the input field
	if ( $gmw['search_form']['locator_icon'] == 'gmw_na' || $gmw['search_form']['locator_icon'] == 'within_address_field' )
		return;
	
	$icon 	 = GMW_IMAGES.'/locator-images/'.$gmw['search_form']['locator_icon'];
    $lSubmit = ( isset( $gmw['search_form']['locator_submit'] ) ) ? 'gmw-locator-submit' : '';
    $button  = '<img id="gmw-locate-button-'.$gmw['ID'].'" class="gmw-locate-btn '.$lSubmit.'" src="'.$icon.'" alt="'.__( 'locator button', 'GMW' ).'" />';

    $output  = "<div class=\"gmw-locator-btn-wrapper gmw-locator-btn-wrapper-{$gmw['ID']}\">";   
    $output .= apply_filters( "gmw_search_form_locator_button_img", $button, $gmw, false );
    $output .= "<i id=\"gmw-locator-btn-loader-{$gmw['ID']}\" class=\"gmw-locator-btn-loader fa fa-refresh fa-spin\" style=\"display:none;\"></i>";	
    $output .= '</div>';

    return apply_filters( 'gmw_search_form_locator_button', $output, $gmw );
}
	function gmw_search_form_locator_icon( $gmw, $class=false ) {
		echo gmw_get_search_form_locator_icon( $gmw, $class );
	}

/**
 * GMW search form radius
 * @param  array $gmw   the form being used
 * @param  false $class deprecated
 * @return mix          html element
 * @since 1.0
 */
function gmw_get_search_form_radius_values( $gmw, $class=false ) {

    $miles  	   = explode( ",", $gmw['search_form']['radius'] );
	$output 	   = '';
	$default_value = apply_filters( 'gmw_search_form_default_radius', end( $miles ), $gmw );
	
	if ( count( $miles ) > 1 ) {
	    
    	if ( $gmw['search_form']['units'] == 'both' ) {
			$title =  $gmw['labels']['search_form']['radius_within'];
		} else {
        	$title = ( $gmw['search_form']['units'] == 'imperial' ) ? $gmw['labels']['search_form']['miles'] : $gmw['labels']['search_form']['kilometers'];
		}
   		
		$title = apply_filters( 'gmw_radius_dropdown_title', $title, $gmw );
		
        $output .= "<select class=\"gmw-distance-select gmw-distance-select-{$gmw['ID']}\" name=\"{$gmw['url_px']}distance\">";
        $output .= 	'<option value="'.esc_attr( $default_value ).'">'.esc_attr( $title ).'</option>';

        foreach ( $miles as $mile ) {
      
        	if ( !is_numeric( $mile ) )
        		continue;

            if ( isset( $_GET[$gmw['url_px'].'distance'] ) && $_GET[$gmw['url_px'].'distance'] == $mile ) {
                $mile_s = 'selected="selected"';
            } else {
                $mile_s = "";
            }
            $output .= "<option value=\"{$mile}\" {$mile_s}>{$mile}</option>";
            
        }
        $output .= "</select>";
        
	} else {
        $output = "<input type=\"hidden\" name=\"{$gmw['url_px']}distance\" value=\"{$default_value}\" />";
	}
	
	$output = apply_filters( "gmw_radius_dropdown_output", $output, $gmw, false );
	$output = apply_filters( "gmw_radius_dropdown_output_{$gmw['ID']}", $output, $gmw, false );
	
    return $output;
}

	function gmw_search_form_radius_values( $gmw, $class=false ) {
		echo gmw_get_search_form_radius_values( $gmw, $class );
	}

/**
 * GMW Search form units 
 * @param  array $gmw   the form being used
 * @param  false $class deprecated
 * @return HTML element 
 */
function gmw_get_search_form_units( $gmw, $class=false ) {
	
	if ( $gmw['search_form']['units'] == 'both' ) {
	        
        $selected = ( isset( $_GET[$gmw['url_px'].'units'] ) && $_GET[$gmw['url_px'].'units'] == 'metric' ) ? 'selected="selected"' : '';
  
        $output  = '<select name="'.esc_attr( $gmw['url_px'] ).'units" id="gmw-units-'.$gmw['ID'].'" class=\"gmw-units gmw-units-'.$gmw['ID'].' \">';
        $output .= '<option value="imperial" selected="selected">'.esc_attr( $gmw['labels']['search_form']['miles'] ).'</option>';
        $output .= '<option value="metric" '.$selected.'>'.esc_attr( $gmw['labels']['search_form']['kilometers'] ).'</option>';
        $output .= "</select>";

	} else {
        $output = '<input type="hidden" name="'.esc_attr( $gmw['url_px'] ).'units" value="'.esc_attr( $gmw['search_form']['units'] ).'" />';
	} 
	
	$output = apply_filters( "gmw_search_form_units", $output, $gmw );
	$output = apply_filters( "gmw_search_form_unit_{$gmw['ID']}", $output, $gmw );
	
	return $output;
}
	function gmw_search_form_units( $gmw, $class=false ) {
		echo gmw_get_search_form_units( $gmw, $class );
	}

/**
 * GMW Pagination
 * @param  array  $gmw the form being used
 * @param  array $args the results message elements
 * @return string      results message
 */
function gmw_get_results_message( $gmw, $args=false )  {

	//if not arg pass vi function used the default
	if ( $args == false ) {
	
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
	
	if ( $args['within'] && !empty( $_GET['action'] ) && $_GET['action'] == $gmw['url_px'].'post' && !empty( $args['address'] ) ) {
		$output .= ' '.sprintf( $args['message']['within'], $args['distance'].' '.$args['units'], $args['address'] );
	}

	return esc_attr( $output );
}

	function gmw_results_message( $gmw, $args ) {
		echo gmw_get_results_message( $gmw, $args );
	}

/**
 * get the address of an item ( post, member... )
 * @param  object $info the item info
 * @param  array $gmw  	the form being used
 * @return string       address
 */
function gmw_get_location_address( $info, $gmw ) {

	if ( empty( $info->formatted_address ) && empty( $info->address ) )
		return esc_attr( $gmw['labels']['search_results']['not_avaliable'] );
	
	$address = ( !empty( $info->formatted_address ) ) ? $info->formatted_address : $info->address;

	$address = apply_filters( "gmw_location_address", $address, $info, $gmw );
	$address = apply_filters( "gmw_location_address_{$gmw['ID']}", $address, $info, $gmw );
	
	return esc_attr( $address ); 
}

	function gmw_location_address( $info, $gmw ) {
		echo gmw_get_location_address( $info, $gmw );
	}

/**
 * Get the distance to a location
 * @param  object $info the item info
 * @param  array $gmw   the form being used
 * @return string       distance + units
 */
function gmw_get_distance_to_location( $info, $gmw ) {

	if ( empty( $info->distance ) )
		return;
	
	$distance = $info->distance . ' ' .$gmw['units_array']['name'];
	
	$distance = apply_filters( "gmw_distance_to_location", $distance, $info, $gmw );
	$distance = apply_filters( "gmw_distance_to_location_{$gmw['ID']}", $distance, $info, $gmw );
	
	return esc_attr( $distance );
}

	function gmw_distance_to_location( $info, $gmw ) {
		echo gmw_get_distance_to_location( $info, $gmw );
	}

/**
 * GMW Addition information
 * @param  object $info   the item's info
 * @param  array $gmw  	  the form being used
 * @param  array $fields  array of the fields that needs to be displayed
 * @param  array $labels  array of labels
 * @param  string $tag    opening and closing tag
 * @return HTML           HTML element of the additional info
 */
function gmw_get_additional_info( $info, $gmw, $fields, $labels, $tag='div' ) {

	if ( empty( $fields ) ) 
		return;
	
	$tag = ( !empty( $tag ) ) ? $tag : 'div';
	
	if ( $tag == 'ul' || $tag == 'ol' ) {
		$subTag = 'li';
	} elseif ( $tag == 'div' ) {
		$tag = 'div';
		$subTag = 'div';
	}  else {
		return;
	}
	
	$count 	= 0;
	$output['wrap_start'] = '<'.$tag.' class="gmw-additional-info-wrapper gmw-'.$gmw['prefix'].'-additiona-info-wrapper">';

	foreach ( $fields as $key => $value ) {

		$key = esc_attr( $key );

		if ( !empty( $info->$key ) ) {

			$info_key = esc_attr( $info->$key );

			if ( $key == 'email' ) {
				
				$count++;
				$output['email']  = "<{$subTag} class=\"{$key}\">";
				$output['email'] .= '<span class="label">'.esc_attr( $labels[$key] ).'</span>';
				$output['email'] .= "<span class=\"information\"><a href=\"mailto:{$info_key}\">{$info_key}</a></span></{$subTag}>";			

			} elseif ( $key == 'website') {
				
				$count++;
				$url = parse_url($info->$key);

				if ( empty( $url['scheme'] ) ) {
					$url['scheme'] = 'http';
				}
				
				$scheme = $url['scheme'].'://';
				$path   = str_replace( $scheme,'',$info_key );
				
				$output['website']  = "<{$subTag} class=\"{$key}\">";
				$output['website'] .= '<span class="label">'. esc_attr( $labels[$key] ).'</span>';
				$output['website'] .= "<span class=\"information\"><a href=\"{$scheme}{$path}\" title=\"{$path}\" target=\"_blank\">{$path}</a></span></{$subTag}>";
				
			} elseif ( $key != 'formatted_address') {
				$count++;
				
				$output[$key]  = "<{$subTag} class=\"{$key}\">";
				$output[$key] .= '<span class="label">'.esc_attr( $labels[$key] ).'</span>';
				$output[$key] .= "<span class=\"information\">{$info_key}</span></a></{$subTag}>";
			}
		}
		$output = apply_filters( 'gmw_additional_info_end', $output, $info, $gmw, $fields, $labels );
	}
	$output['wrap_end'] = "</{$tag}>";
		
	$output = apply_filters( 'gmw_additional_info_otuput', 				$output, $info, $gmw, $fields, $labels );
	$output = apply_filters( "gmw_additional_info_output_{$gmw['ID']}", $output, $info, $gmw, $fields, $labels );

	return ( $count > 0 ) ? implode( '', $output ) : false;
}
	
	function gmw_additional_info( $info, $gmw, $fields, $labels, $tag='div' ) {
		echo gmw_get_additional_info( $info, $gmw, $fields, $labels, $tag );
	}

/**
 * Get directions link - open new page with google map
 * @param  object $info  the item's info ( post, member... )
 * @param  array  $gmw   the form being used
 * @param  string $title the title of the directions link
 * @return string        directions link
 */
function gmw_get_directions_link( $info, $gmw, $title ) {

	//check for coordinates
	if ( empty( $info->lat ) || empty( $info->long ) )
		return;

	//make sure coordinates are legit
	if ( $info->lat == 0.000000 || $info->lat == 0.000000 )
		return;

	//
	$title 	  = ( !empty( $title ) ) ? $title : $gmw['labels']['search_results']['directions'];
	$ulLatLng = ( !empty( $gmw['your_lat'] ) && !empty( $gmw['your_lng'] ) ) ? "{$gmw['your_lat']},{$gmw['your_lng']}" : "";
	
	$output = '<a class="gmw-get-directions gmw-'.$gmw['prefix'].'-get-directions" 
			title="'.$title.'" 
			href="http://maps.google.com/maps?f=d&hl='.esc_attr( $gmw['language'] ).'&region='.esc_attr( $gmw['region'] ).'&doflg='.esc_attr( $gmw['units_array']['map_units'] ).'&saddr='.esc_attr( $ulLatLng ).'&daddr='.esc_attr( $info->lat ).','.esc_attr( $info->long ).'&ie=UTF8&z=12" target="_blank">'.esc_attr( $title ).'</a>';
	
	$output = apply_filters( "gmw_get_directions_link", 				 $output, $gmw, $info, $title );
	$output = apply_filters( "gmw_{$gmw['prefix']}_get_directions_link", $output, $gmw, $info, $title );
	$output = apply_filters( "gmw_get_directions_link_{$gmw['ID']}", 	 $output, $gmw, $info, $title );
	
	return $output; 
}

	function gmw_directions_link( $info, $gmw, $title ) {
		echo gmw_get_directions_link( $info, $gmw, $title );
	}
	
/**
 * GMW function - Display post's excerpt
 * @param unknown_type $post
 * @param unknown_type $gmw
 * @param unknown_type $count
 */
function gmw_get_excerpt( $info, $gmw, $content, $count, $read_more=false ) {

	$content = apply_filters( 'gmw_except_content', $content, $info, $gmw );

	if ( empty( $content ) )
		return;

	//trim number of words
	if ( !empty( $count ) ) {

		//build read more link
		if ( !empty( $read_more ) ) {		
			$more_link = apply_filters( 'gmw_excerpt_more_link', ' <a href="'.get_the_permalink( $info->ID ).'" class="gmw-more-link" title="read more">'.esc_attr( $read_more ).'</a>', $info, $gmw, $content, $count, $read_more );
		} else {
			$more_link = '';
		}
		$content = wp_trim_words( $content, $count, $more_link );
	}	
	
	if ( !apply_filters( 'gmw_except_shortcodes_enabled', true, $info, $gmw ) ) {
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

function gmw_multiexplode( $delimiters, $string ) {

    $ready  = str_replace( $delimiters, $delimiters[0], $string );
    $launch = explode( $delimiters[0], $ready );
    return $launch;
}

/**
 * GMW function - live directions function. To be used with Premium-settings and Global Maps add-on only. 
 * If used without the add-ons will results in errors.
 * @param unknown_type $info - $post, $member...
 * @param unknown_type $gmw
 */
function gmw_get_live_directions( $info, $gmw ) {

	$id 		   	= $info->ID;
	$start_address 	= '';
	$end_address   	= ( !empty( $info->address ) ) ? $info->address : $info->formatted_address;
	$end_coords	   	= "{$info->lat},{$info->long}";
	$labels 		= $gmw['labels']['live_directions'];
	
	if ( !empty( $gmw['org_address'] ) ) {
		$start_address = $gmw['org_address'];
	} elseif ( !empty( $gmw['user_position']['address'] ) && $gmw['user_position']['address'] != 'false'  ) {
		$start_address = $gmw['user_position']['address'];
	}
			
	$output = "<div id=\"gmw-get-directions-wrapper-{$id}\" class=\"gmw-get-directions-wrapper\">";
				
	$output .= "<h3>{$labels['directions_label']}</h3>";
	$output .= "<form id=\"gmw-get-directions-form-{$id}\" class=\"get-directions-form\">";
					
	$output .= 	"<div class=\"get-directions-address-field-wrapper\">";
			
	$output .= 	"<div class=\"address-wrapper start-address-wrapper\">";
	$output .= 		"<label for=\"gmw-directions-start-point\">{$labels['from']}</label>";
	$output .= 		"<input type=\"text\" id=\"gmw-directions-start-point-{$id}\" class=\"gmw-directions-start-point\" value=\"{$start_address}\" placeholder=\"{$labels['start_point']}\" />";
	$output .= 		"<a href=\"#\" type=\"submit\" id=\"get-directions-submit-{$id}\" class=\"get-directions-submit fa fa-search get-directions-submit-icon\"></a>";
	$output .= 	"</div>";
				
	$output .= 	"<div class=\"address-wrapper end-address-wrapper\">"; 
	$output .= 		"<label for=\"gmw-directions-end-point\">{$labels['to']}</label>";
	$output .= 		"<input type=\"text\" id=\"gmw-directions-end-point-{$id}\"  class=\"gmw-directions-end-point\" value=\"{$end_address}\" readonly placeholder=\"{$labels['end_point']}\" />";
	$output .= 		"<input type=\"hidden\" id=\"gmw-directions-end-coords-{$id}\" class=\"gmw-directions-end-coords\" value=\"{$end_coords}\" />";		
	$output .= 	"</div>";
				
	$output .= 	"</div>";
			
	$output .=  "<ul id=\"travel-mode-options-{$id}\" class=\"gmw-get-directions-options gmw-get-directions-options-holder travel-mode-options\">";
	$output .= 	 "<li>";
	$output .= 	 	"<a href=\"#\" id=\"DRIVING\" class=\"travel-mode-trigger active\">{$labels['driving']}</a>";
	$output .= 	 "</li>";
				
	$output .= 	 "<li>";
	$output .= 	 	"<a href=\"#\" id=\"WALKING\" class=\"travel-mode-trigger\">{$labels['walking']}</a>";
	$output .= 	 "</li>";
				
	$output .= 	 "<li>";
	$output .= 	 	"<a href=\"#\" id=\"BICYCLING\" class=\"travel-mode-trigger\">{$labels['bicycling']}</a>";
	$output .= 	 "</li>";
				
	$output .= 	 "<li>";
	$output .= 	 	"<a href=\"#\" id=\"TRANSIT\" class=\"travel-mode-trigger\">{$labels['transit']}</a>";
	$output .= 	 "</li>";
	$output .= 	 "</ui>";
			
	$output .= 	 "<div id=\"unit-system-options-wrapper-{$id}\" class=\"gmw-get-directions-options-wrapper unit-system-options-wrapper\">";
	$output .= 	 "<span>{$labels['units_label']}</span>";
	$output .= 	 "<ul id=\"unit-system-options-{$id}\" class=\"gmw-get-directions-options unit-system-options\">";				
	$output .= 	 	"<li>";
	$output .= 	 		"<input type=\"radio\" id=\"unit-system-imperial-trigger-{$id}\" name=\"unit_system_trigger\" class=\"unit-system-trigger\"  value=\"IMPERIAL\" checked=\"checked\" />";
	$output .= 	 		"<label for=\"unit-system-imperial-trigger\">{$labels['units_mi']}</label>";
	$output .= 	 	"</li>";
	$output .= 	 	"<li>";
	$output .= 	 		"<input type=\"radio\" id=\"unit-system-metric-trigger-{$id}\"   name=\"unit_system_trigger\" class=\"unit-system-trigger\"  value=\"METRIC\" />";
	$output .= 	 		"<label for=\"unit-system-metric-trigger\">{$labels['units_km']}</label>";
	$output .= 	 	"</li>";
	$output .= 	 "</ul>";
	$output .= 	"</div>";
			
	$output .= 	 "<div id=\"route-avoid-options-wrapper-{$id}\" class=\"gmw-get-directions-options-wrapper route-avoid-options-wrapper\">";
	$output .= 	 "<span>{$labels['avoid_label']}</span>";
	$output .= 	 "<ul id=\"route-avoid-options-{$id}\" class=\"gmw-get-directions-options route-avoid-options\">";
	$output .= 	 	"<li>";
	$output .= 	 		"<input type=\"checkbox\" id=\"route-avoid-highways-trigger-{$id}\" class=\"route-avoid-trigger\" value=\"1\" />";
	$output .= 	 		"<label for=\"route-avoid-highways-trigger-{$id}\">{$labels['avoid_hw']}</label>";
	$output .= 	 "</li>";
					
	$output .= 	 "<li>";
	$output .= 	 	"<input type=\"checkbox\" id=\"route-avoid-tolls-trigger-{$id}\" class=\"route-avoid-trigger\" value=\"1\" />";
	$output .= 		"<label for=\"route-avoid-tolls-trigger-{$id}\">{$labels['avoid_tolls']}</label>";
	$output .= 	 "</li>";
	
	$output .= 	 "</ul>";
   	$output .= 	 "</div>";
    $output .= 	 	"<input type=\"hidden\" class=\"gmw-directions-form-id\" value=\"{$id}\" />";	
    $output .= 	 	"<input type=\"hidden\" class=\"gmw-form-id\" value=\"{$gmw['ID']}\" />";		
	$output .= 	 "</form>";
		
	$output .= 	 "</div>";

	return $output;
}

	function gmw_live_directions( $info, $gmw ) {
		echo gmw_get_live_directions( $info, $gmw );
	}
/**
 * GMW function - live directions results panel. To be used with live directions function only.
 * @param unknown_type $info
 * @param unknown_type $gmw
 */
function gmw_get_live_directions_panel( $info, $gmw ) {
	
	$id = $info->ID;
	return "<div id=\"directions-panel-wrapper-{$id}\" class=\"directions-panel-wrapper\"></div>"; 	
}
	function gmw_live_directions_panel( $info, $gmw ) {
		echo gmw_get_live_directions_panel( $info, $gmw );
}	

/**
 * GMW ML function - display xprofile fields in info window
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_iw_xprofile_fields( $gmw ) {

	if ( empty( $gmw['info_window']['profile_fields'] ) && empty( $gmw['info_window']['profile_fields_date'] ) )
		return;

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

	if ( empty( $fields ) ) 
		return;
	
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
 */
function gmw_driving_distance( $info, $gmw, $title ) {

	if ( empty( $gmw['your_lat'] ) || empty( $gmw['your_lng'] ) )
		return;
	
	if ( !$info->lat || !$info->long )
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

        var start = new google.maps.LatLng('{$gmw['your_lat']}', '{$gmw['your_lng']}');
        var end = new google.maps.LatLng('{$info->lat}', '{$info->long}');
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
}

/**
 * GMW function - Pagination
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_pagination( $gmw, $pageName, $maxPages ) {

	$maxPages    = ceil( $maxPages );
	$paged_check = ( is_front_page() ) ? 'page' : $pageName;
	
	$args = apply_filters( 'gmw_get_pagination_args', array(
			'base'         		 => add_query_arg( $pageName, '%#%' ),
			'format'       		 => '',
			'total'        		 => $maxPages,
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
	), $gmw, $pageName, $maxPages );

	$pags = paginate_links( $args );

	$output = '';
	if ( is_array( $pags ) ) {
		$output = '<ul class="gmw-pagination gmw-prem-pagination gmw-pagination-'.$gmw['ID'].'">';
		foreach ( $pags as $link ) {
			$output .= '<li>'.$link.'</li>';
		}
		$output .= '</ul>';
	}

	return apply_filters( 'gmw_get_pagination_output', $output, $pags, $args, $gmw, $pageName, $maxPages );
}

	function gmw_pagination( $gmw, $pageName, $maxPages ) {
		echo gmw_get_pagination( $gmw, $pageName, $maxPages );
	}
	
/**
 * GMW FL Search results function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_per_page( $gmw, $totalCount, $pagName ) {
   
    $perPage  = ( $gmw['page_load_results_trigger'] ) ? explode( ",", $gmw['page_load_results']['per_page'] ) : explode( ",", $gmw['search_results']['per_page'] );
    $lastpage = ceil( $totalCount / $gmw['get_per_page'] );
    $paged    = $gmw['paged'];
    	
    if ( count( $perPage ) > 1) {

        echo '<select name="'.esc_attr( $gmw['url_px'] ).'per_page" class="gmw-per-page gmw-'.$gmw['ID'].'-per-page gmw-'.$gmw['prefix'].'-per-page gmw-'.$gmw['prefix'].'-per-page-dropdown">';

        foreach ( $perPage as $pp ) {
        	$pp_s = "";
            if ( isset( $_GET[$gmw['url_px'].'per_page'] ) && $_GET[$gmw['url_px'].'per_page'] == $pp ) {
                $pp_s = 'selected="selected"';
        	}                
            echo '<option value="'.$pp.'" '.$pp_s.'>'.$pp.' '.$gmw['labels']['search_results']['per_page'].'</option>';
        }
        echo '</select>';

        $url_px = esc_attr( $gmw['url_px'] );
        $action = ( empty( $_GET['action'] ) || $_GET['action'] != $url_px.'post' ) ? 0 : 1;
        $currentPerPage = ( ! empty( $_GET[$gmw['url_px'].'per_page'] ) ) ? $_GET[$gmw['url_px'].'per_page'] : reset( $perPage );

        echo '<input type="hidden" id="gmw-per-page-hidden" data-formid="'.$gmw['ID'].'" data-perpage="'.$currentPerPage.'" data-totalcount="'.$totalCount.'" data-pagename="'.$pagName.'" data-paged="'.$paged.'" data-gmwpost="'.$action.'" data-urlpx="'.$url_px.'">';
    }
}

/**
 * GMW function - no results found
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