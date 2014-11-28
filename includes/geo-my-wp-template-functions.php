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

	return  apply_filters( 'gmw_'.$form['prefix'].'_set_labels', array(
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
	), $form );
}

/**
 * GMW FL Search results function - display map
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_results_map( $gmw ) {
	
	//abort shortcode map if not set to do so in the form settings
	//if ( isset( $gmw['params']['map'] ) && $gmw['search_results']['display_map'] != 'shortcode'  )
		//return;
	
    $frame    = ( isset( $gmw['results_map']['map_frame'] ) ) ? 'gmw-map-frame' : '';
    $display  = 'display:none;';
    $expanded = '';
    
	if ( $gmw['addon'] == 'global_maps' ) {
		$display = '';
		$expanded = ( isset( $gmw['general_settings']['pl_expand_map'] ) ) ? 'gmw-expanded-map' : '';
	}
		
	//if ( ( $gmw['prefix'] == 'gmpt' || $gmw['prefix'] == 'gmfl' ) )
    $output['open']    		  = '<div id="gmw-map-wrapper-'.$gmw['ID'].'" class="gmw-map-wrapper gmw-map-wrapper-'.$gmw['ID'].' gmw-'.$gmw['addon'].'-map-wrapper gmw-'.$gmw['prefix'].'-map-wrapper ' . $frame . ' '.$expanded.'"  style="'.$display.'width:'.$gmw['results_map']['map_width'].';height:' . $gmw['results_map']['map_height'] . ';">';
    $output['expend_toggle']  = '<div id="gmw-expand-map-trigger-'.$gmw['ID'].'" class="gmw-expand-map-trigger dashicons dashicons-editor-expand" style="display:none;" title="'.__( 'Resize map','GMW' ) .'"></div>';
    $output['loader']  		  = 	'<div id="gmw-map-loader-wrapper-'.$gmw['ID'].'" class="gmw-map-loader-wrapper gmw-'.$gmw['prefix'].'-loader-wrapper">';
    $output['loader'] 		 .= 		'<img id="gmw-map-loader-'.$gmw['ID'].'" class="gmw-map-loader gmw-'.$gmw['prefix'].'-map-loader" src="'.$gmw['map_loader'].'" alt="'.__( 'Map loader', 'GMW' ) .'" />';
    $output['loader'] 		 .= 	'</div>';
    $output['map']     		  = 	'<div id="gmw-map-'.$gmw['ID'].'" class="gmw-map gmw-' . $gmw['prefix'] . '-map" style="width:100%; height:100%"></div>';
    $output['close']   		  = '</div>';

    $output = apply_filters( 'gmw_'.$gmw['prefix'].'_map_output', $output, $gmw );
    
    return implode( ' ', $output );
}

function gmw_results_map( $gmw ) {
	
	if ( $gmw['search_results']['display_map'] != 'results' )
		return;
	
	//check that we have only one map for each form
	if ( apply_filters( 'gmw_disable_maps', false ) ) 
		return;
	
	//disable th creation of a map after first map is displayed
	add_filter( 'gmw_disable_maps', '__return_true', 10 );

    do_action( 'gmw_'.$gmw['prefix'].'_before_map', $gmw );
    
    echo gmw_get_results_map( $gmw );
  
    do_action( 'gmw_'.$gmw['prefix'].'_after_map', $gmw );
}

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
	
	if ( !empty( $folder ) )
		$folders = array_merge( $folders, $folder );
	
	if ( empty( $folders[$gmw['prefix']] ) )
		return;
	
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
			wp_enqueue_style( $search_form['stylesheet_handle'], $search_form['stylesheet_url'] );
		}
		
		do_action( 'gmw_'.$gmw['prefix'].'_before_search_form', $gmw );
		
		include( $search_form['content_path'] );
		
		do_action( 'gmw_'.$gmw['prefix'].'_after_search_form', $gmw );
	}

/**
 * GMW search form function - form submit hidden fields
 *
 */
function gmw_form_submit_fields( $gmw, $subValue ) {
	$subValue = ( !empty( $subValue ) ) ? $subValue : $gmw['labels']['search_form']['submit'];
	
    ?>
    <div id="gmw-submit-wrapper-<?php echo $gmw['ID']; ?>" class="gmw-submit-wrapper gmw-submit-wrapper-<?php echo $gmw['ID']; ?>">
        <input type="hidden" id="gmw-form-id-<?php echo $gmw['ID']; ?>" class="gmw-form-id gmw-form-id-<?php echo $gmw['ID']; ?>" name="gmw_form" value="<?php echo $gmw['ID']; ?>" />
        
        <input 
        	type="hidden" 
        	id="gmw-per-page-<?php echo $gmw['ID']; ?>" 
        	class="gmw-per-page gmw-per-page-<?php echo $gmw['ID']; ?>" 
        	name="gmw_per_page" value="<?php echo current( explode( ",", $gmw['search_results']['per_page'] ) ); ?>" 
        	/>
        	
        <input 
        	type="hidden" 
        	id="prev-address-<?php echo $gmw['ID']; ?>" 
        	class="prev-address prev-address-<?php echo $gmw['ID']; ?>" 
        	value="<?php if ( !empty( $_GET['gmw_address'] ) ) echo implode( ' ', $_GET['gmw_address'] ); ?>"
        	/>
    
        <input 
        	type="hidden" 
        	id="gmw-lat-<?php echo $gmw['ID']; ?>" 
        	class="gmw-lat gmw-lat-<?php echo $gmw['ID']; ?>" 
        	name="gmw_lat" value="<?php if ( !empty( $_GET['gmw_lat'] ) ) echo $_GET['gmw_lat']; ?>"
        	/>
        	
        <input 
        	type="hidden" 
        	id="gmw-long-<?php echo $gmw['ID']; ?>" 
        	class="gmw-lng gmw-long-<?php echo $gmw['ID']; ?>" 
        	name="gmw_lng" value="<?php if ( !empty( $_GET['gmw_lng'] ) ) echo $_GET['gmw_lng']; ?>"
        	/>

        <input 
        	type="hidden" 
        	id="gmw-prefix-<?php echo $gmw['ID']; ?>" 
        	class="gmw-prefix gmw-prefix-<?php echo $gmw['ID']; ?>" 
        	name="gmw_px" value="<?php echo $gmw['prefix']; ?>" 
        	/>
        	
        <input 
        	type="hidden" 
        	id="gmw-action-<?php echo $gmw['ID']; ?>" 
        	class="gmw-action gmw-action-<?php echo $gmw['ID']; ?>" 
        	name="action" 
        	value="gmw_post" 
        	/>

        <?php do_action( 'gmw_from_submit_fields', $gmw ); ?>
        
        <?php $submit_button = '<input type="submit" id="gmw-submit-' . $gmw['ID'] . '" class="gmw-submit gmw-submit-' . $gmw['ID'] . '" value="' . $subValue . '" />'; ?>
        <?php echo apply_filters( 'gmw_form_submit_button', $submit_button, $gmw, $subValue ); ?>
    </div>
    <?php
}

/**
 * GMW Search form function - Address Field
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_address_field( $gmw, $id, $class ) {
	echo gmw_get_search_form_address_field( $gmw, $id, $class );
}
	function gmw_get_search_form_address_field( $gmw, $id, $class ) {
	
	    $address_field 	= '';
	    $am 		   	= ( !empty( $gmw['search_form']['address_field']['mandatory'] ) ) ? 'mandatory' : '';
	    $title 		   	= ( !empty( $gmw['search_form']['address_field']['title'] ) ) ? $gmw['search_form']['address_field']['title'] : '';
		$class		   	= ( !empty( $class ) ) ? $class : '';
		$id		   	   	= ( !empty( $id ) )    ? $id : 'gmw-address-field-wrapper-'.$gmw['ID'];
		$value			= ( !empty( $_GET['gmw_address'] ) ) ? str_replace( '+', ' ', implode( ' ', $_GET['gmw_address'] ) ) : ''; 
		$place_holder	= ( !empty( $gmw['search_form']['address_field']['within'] ) ) ? 'placeholder="'.$title.'"' : '';
		
	    $address_field .= '<div id="'.$id.'" class="gmw-address-field-wrapper gmw-address-field-wrapper-'.$gmw['ID'].' '.$class.'">';
	    
	    if ( !isset( $gmw['search_form']['address_field']['within'] ) && !empty( $title ) ) {
	        $address_field .= '<label for="gmw-address-'.$gmw['ID'].'">' . $title . '</label>';
	    }
	    $address_field .= '<input type="text" name="gmw_address[]" id="gmw-address-'.$gmw['ID'].'" autocomplete="off" class="'.$am.' gmw-address gmw-full-address gmw-address-'.$gmw['ID'].' '.$class.'" value="'.$value.'" '.$place_holder.'/>';
	    
	    if ( $gmw['search_form']['locator_icon'] == 'within_address_field' ) {
	    	
	    	$lSubmit = ( isset( $gmw['search_form']['locator_submit'] ) ) ? 'gmw-locator-submit' : '';
	    	
	    	$address_field .= '<div class="gmw-locator-btn-wrapper gmw-locator-btn-within-wrapper">';
	    	$address_field .= 	'<span id="'.$gmw['ID'].'" class="dashicons-before dashicons-location gmw-locator-btn-within gmw-locator-button gmw-locate-btn '.$lSubmit.'"></span>';
	    	$address_field .= 	'<img class="gmw-locator-btn-loader" src="'.GMW_IMAGES.'/gmw-loader.gif" alt="Locator image loader" style="display:none;">';
	    	$address_field .= '</div>';
	    }
	    
	    $address_field .= '</div>';
	
	    if ( !empty( $gmw['search_form']['address_field']['address_autocomplete'] ) ) {
	    	GEO_my_WP::google_places_address_autocomplete( array( 'gmw-address-'.$gmw['ID'] ) );
	    }
	    
	    return apply_filters( 'gmw_search_form_address_field', $address_field, $gmw, $id, $class );
	}

/**
 * GMW Search form function - Locator Icon
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_locator_icon( $gmw, $class ) {
	echo gmw_get_search_form_locator_icon( $gmw, $class );

}
	function gmw_get_search_form_locator_icon( $gmw, $class ) {
	
		$icon = $gmw['search_form']['locator_icon'];
		
		if ( $icon == 'gmw_na' || $icon == 'within_address_field' )
			return;
		
	    $lSubmit = ( isset( $gmw['search_form']['locator_submit'] ) ) ? 'gmw-locator-submit' : '';
	    
	    $button  = '<div class="gmw-locator-btn-wrapper gmw-locator-btn-wrapper-'.$gmw['ID'].'">';
	    $button .= apply_filters( 'gmw_search_form_locator_button_img', '<img id="gmw-locate-button-'.$gmw['ID'].'" class="gmw-locate-btn '.$lSubmit.' ' .$class.'" src="'.GMW_IMAGES.'/locator-images/'.$icon.'" alt="'.__( 'Locator image', 'GMW' ) .'" />', $gmw, $class );
	    $button .= '<img class="gmw-locator-btn-loader" src="'.GMW_IMAGES.'/gmw-loader.gif" alt="'.__( 'Locator image loader', 'GMW' ) .'" style="display:none;" />';	
	    $button .= '</div>';
	
	    return apply_filters( 'gmw_search_form_locator_button', $button, $gmw );
	}

/**
 * GMW Search form function - Radius Values
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_radius_values( $gmw, $class ) {
	echo gmw_get_search_form_radius_values( $gmw, $class );
}
	
	function gmw_get_search_form_radius_values( $gmw, $class ) {
	
	    $miles  = explode( ",", $gmw['search_form']['radius'] );
		$output ='';
	    
		if ( count( $miles ) > 1 ) {
		    
	    	if ( $gmw['search_form']['units'] == 'both' ) {
				$title =  $gmw['labels']['search_form']['radius_within'];
			} else {
	        	$title = ( $gmw['search_form']['units'] == 'imperial' ) ? $gmw['labels']['search_form']['miles'] : $gmw['labels']['search_form']['kilometers'];
			}
	   		
			$title = apply_filters( 'gmw_radius_dropdown_title', $title, $gmw );
			
	        $output .= '<select class="gmw-distance-select gmw-distance-select-' . $gmw['ID'] . ' '.$class.'" name="gmw_distance">';
	        $output .= 	'<option value="'.end($miles).'">'.$title.'</option>';
	
	        foreach ( $miles as $mile ) {
	
	            if ( isset( $_GET['gmw_distance'] ) && $_GET['gmw_distance'] == $mile ) {
	                $mile_s = 'selected="selected"';
	            } else {
	                $mile_s = "";
	            }
	            $output .= '<option value="'.$mile.'" '.$mile_s.'>'.$mile.'</option>';
	            
	        }
	        $output .= '</select>';
	        
		} else {
	        $output = '<input type="hidden" name="gmw_distance" value="'.end( $miles ).'" />';
		}
	    return apply_filters( 'gmw_radius_dropdown_output', $output, $gmw, $class );
	}

/**
 * GMW Search form function - Distance units
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_units( $gmw, $class ) {
	
	if ( $gmw['search_form']['units'] == 'both' ) {
	        
        $selected = ( isset( $_GET['gmw_units'] ) && $_GET['gmw_units'] == 'metric' ) ? 'selected="selected"' : '';
  
        echo '<select name="gmw_units" id="gmw-units-' . $gmw['ID'] . '" class="gmw-units gmw-units-' . $gmw['ID'] . ' ' . $class . '">';
        echo 	'<option value="imperial" selected="selected">'.$gmw['labels']['search_form']['miles'].'</option>';
        echo 	'<option value="metric" '.$selected.'>'.$gmw['labels']['search_form']['kilometers'].'</option>';
        echo '</select>';

	} else {
        echo '<input type="hidden" name="gmw_units" value="' . $gmw['search_form']['units'] . '" />';
	} 
}

/**
 * GMW function - search results message
 * @param $gmw
 * @param $args ( array )
 */
function gmw_results_message( $gmw, $args ) {
	echo gmw_get_results_message( $gmw, $args );
}

	function gmw_get_results_message( $gmw, $args )  {
	
		$args = apply_filters( 'gmw_results_message_args' , false, $gmw );
		
		if (  $args == false ) {
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
		} 

		$output = '';
			
		if ( $args['showing'] )
			$output .= sprintf( $args['message']['showing'], $args['count'], $args['total_count'] );
		
		if ( $args['within'] && !empty( $_GET['action'] ) && $_GET['action'] == 'gmw_post' && !empty( $args['address'] ) ) {
			$output .= ' '.sprintf( $args['message']['within'], $args['distance'].' '.$args['units'], $args['address'] );
		}

		return $output;
	}
	
/**
 * GMW function - get the address of location
 * @param unknown_type $post
 * @param unknown_type $gmw
 */
function gmw_location_address( $info, $gmw ) {
	echo gmw_get_location_address( $info, $gmw );
}

	function gmw_get_location_address( $info, $gmw ) {
	
		if ( empty( $info->formatted_address ) && empty( $info->address ) )
			return;
	
		$address = ( !empty( $info->formatted_address ) ) ? $info->formatted_address : $info->address;
	
		return apply_filters( 'gmw_'.$gmw['prefix'].'_location_address', $address, $info, $gmw );
	}
	
/**
 * GMW function - display distance to location
 * @param unknown_type $info ( $post, $member... )
 * @param unknown_type $gmw
 */
function gmw_get_distance_to_location( $info, $gmw ) {

	if ( empty( $info->distance ) )
		return;
	
	return apply_filters( 'gmw_'.$gmw['prefix'].'_distance_to_location', $info->distance . ' ' .$gmw['units_array']['name'], $info, $gmw );
}

	function gmw_distance_to_location( $info, $gmw ) {
		echo gmw_get_distance_to_location( $info, $gmw );
	}

/**
 * GMW Function - display additional information
 * @param unknown_type $post
 * @param unknown_type $gmw
 */
function gmw_additional_info( $info, $gmw, $array, $labels, $tag ) {
	echo gmw_get_additional_info( $info, $gmw, $array, $labels, $tag );
}
	function gmw_get_additional_info( $info, $gmw, $array, $labels, $tag ) {
	
		if ( empty( $array ) ) 
			return;
		
		$tag = ( !empty( $tag ) ) ? $tag : 'div';
		
		if ( $tag == 'ul' || $tag == 'ol' ) {
			$subTag = 'li';
		} else {
			$tag = 'div';
			$subTag = 'div';
		}
		
		$count 	 = 0;
		$output  = '';
		$output .= '<'.$tag.' class="gmw-additional-info-wrapper">';
	
		foreach ( $array as $key => $value ) {
	
			if ( !empty( $info->$key ) ) {
	
				if ( $key == 'email' ) {
					$count++;
					$output .= '<'.$subTag.' class="'.$key.'"><span class="label">'.$labels[$key].'</span><span class="information"><a href="mailto:'.$info->$key.'" >' . $info->$key . '</a></span></'.$subTag.'>';
				} elseif ( $key == 'website') {
					$count++;
					$output .= '<'.$subTag.' class="'.$key.'"><span class="label">'.$labels[$key].'</span><span class="information"><a href="'.$info->$key.'" >' . $info->$key . '</a></span></'.$subTag.'>';
				} elseif ( $key != 'formatted_address') {
					$count++;
					$output .= '<'.$subTag.' class="'.$key.'"><span class="label">'.$labels[$key].'</span><span class="information">'.$info->$key . '</span></a></'.$subTag.'>';
				}
			}
		}
		$output .= '</'.$tag.'>';
			
		return ( $count > 0 ) ? $output : '';
	}
	
/**
 * GMW function - Get directions link.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_directions_link( $info, $gmw, $title ) {
	echo gmw_get_directions_link( $info, $gmw, $title );
}
	
	function gmw_get_directions_link( $info, $gmw, $title ) {
	
		if ( !$info->lat || !$info->long )
			return;
	
		if ( $info->lat == 0.000000 || $info->lat == 0.000000 )
			return;
	
		$title 	  = ( !empty( $title ) ) ? $title : $gmw['labels']['search_results']['directions'];
		$ulLatLng = ( !empty( $gmw['your_lat'] ) && $gmw['your_lat'] != 'false' && !empty( $gmw['your_lng'] ) ) ? "{$gmw['your_lat']},{$gmw['your_lng']}" : "";
	
		return apply_filters( 'gmw_'.$gmw['prefix'].'_get_directions_link', "<a class=\"gmw-get-directions get-directions {$gmw['prefix']}\" title=\"{$title}\" href=\"http://maps.google.com/maps?f=d&hl={$gmw['language']}&region={$gmw['region']}&doflg={$gmw['units_array']['map_units']}&saddr={$ulLatLng}&daddr={$info->lat},{$info->long}&ie=UTF8&z=12\" target=\"_blank\">{$title}</a>", $gmw, $info, $title );
	}
	
/**
 * GMW function - Display post's excerpt
 * @param unknown_type $post
 * @param unknown_type $gmw
 * @param unknown_type $count
 */
function gmw_excerpt( $info, $gmw, $content, $count ) {
	echo gmw_get_excerpt( $info, $gmw, $content, $count );
}
	function gmw_get_excerpt( $info,$gmw, $content, $count ) {
	
		if ( empty( $content ) )
			return;
	
		if ( empty( $count ) ) {
			$count = 99999999;
		}
		
		return wp_trim_words( $content, $count, '' );
		//return wp_trim_words( $content, $count, '' ) .'... [<a class="read-more" href="'.get_permalink( $post->ID ).'">'.$gmw['labels']['info_window']['read_more'].'</a>]';
	}
	
/**
 * Pop-up Info window function - info window toggle
 * @param $gmw     - current form being used
 * @param $info    - $post, $member or so on
 * @param $id      - ID tag for the button
 * @param $toggled - id of the element to be toggled. Dont use # just the tag name
 * $param $showIcon - dashicon to be used for the show info button
 * $param $hideIcon - dashicon to be used for the hide info button
 * @param $animation - true | false - animation will use animated height to show/hide. Otherwise will use jQuery slideToggle
 * @param minHeight  - value to be used with animation. The height when the element is hidden.
 * @param maxHeight  - value to be used with animation. The height when the element is visible.
 * 
 */
function gmw_window_toggle( $gmw, $info, $id, $toggled, $showIcon, $hideIcon, $animation, $minHeight, $maxHeight ) {

	$id 	     = ( !empty( $id ) ) 		  ? $id 	   : 'gmw-window-toggle-'.rand(10, 999);
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
	
	echo apply_filters( 'gmw_'.$gmw['prefix'].'_window_toggle', '<a href="#" id="'.$id.'" class="gmw-window-toggle gmw-'.$gmw['prefix'].'-window-toggle dashicons '.$hideIcon.'" title="Hide information"></a>', $info, $gmw, $hideIcon, $showIcon );
	
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
 * @param unknown_type $handleIcon - dashicon to be used as the handle icon\
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
		return apply_filters( 'gmw_'.$gmw['prefix'].'_get_draggable_handle', '<a href="#" id="gmw-drag-area-handle-'.$gmw['ID'].'" class="gmw-drag-area-handle gmw-'.$gmw['prefix'].'-drag-area-handle dashicons '.$handleIcon.'" onclick="event.preventDefault()"></a>', $info, $gmw, $handleIcon );
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
	return	apply_filters( 'gmw_'.$gmw['prefix'].'_'.$prefix.'_get_close_button', '<a id="gmw-close-button-'.$gmw['ID'].'" class="gmw-close-button gmw-'.$prefix.'-close-button gmw-'.$gmw['prefix'].'-'.$prefix.'-close-button dashicons '.$icon.'"></a>', $info, $gmw, $icon, $prefix );
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
function gmw_live_directions( $info, $gmw ) {

	$id 		   	= $info->ID;
	$start_address 	= '';
	$end_address   	= ( !empty( $info->address ) ) ? $info->address : $info->formatted_address;
	$end_coords	   	= "{$info->lat},{$info->long}";
	$labels 		= $gmw['labels']['live_directions'];
	
	if ( !empty( $gmw['org_address'] ) ) {
		$start_address = $gmw['org_address'];
	} elseif ( !empty( $gmw['ul_address'] ) && $gmw['ul_address'] != 'false'  ) {
		$start_address = $gmw['ul_address'];
	}
		
	?>
	<div id="gmw-get-directions-wrapper-<?php echo $id; ?>" class="gmw-get-directions-wrapper">
				
		<h3><?php echo $labels['directions_label']; ?></h3>
		<form id="gmw-get-directions-form-<?php echo $id; ?>" class="get-directions-form">
					
			<div class="get-directions-address-field-wrapper">
			
				<div class="address-wrapper start-address-wrapper"> 
					<label for="gmw-directions-start-point"><?php echo $labels['from']; ?></label>
					<input type="text" id="gmw-directions-start-point-<?php echo $id; ?>" class="gmw-directions-start-point" value="<?php echo $start_address; ?>" placeholder="<?php echo $labels['start_point']; ?>" />
					<a href="#" type="submit" id="get-directions-submit-<?php echo $id; ?>" class="get-directions-submit dashicons dashicons-search get-directions-submit-icon"></a>
				</div>
				
				<div class="address-wrapper end-address-wrapper"> 
					<label for="gmw-directions-end-point"><?php echo $labels['to']; ?></label>
					<input type="text"   id="gmw-directions-end-point-<?php echo $id; ?>"  class="gmw-directions-end-point"  value="<?php echo $end_address; ?>" readonly placeholder="<?php echo $labels['end_point']; ?>" />
					<input type="hidden" id="gmw-directions-end-coords-<?php echo $id; ?>" class="gmw-directions-end-coords" value="<?php echo $end_coords; ?>" />		
				</div>
				
			</div>
			
			<ul id="travel-mode-options-<?php echo $id; ?>" class="gmw-get-directions-options travel-mode-options">
				<li>
					<a href="#" id="DRIVING" class="travel-mode-trigger active""><?php echo $labels['driving']; ?></a>
				</li>
				
				<li>
					<a href="#" id="WALKING" class="travel-mode-trigger"><?php echo $labels['walking']; ?></a>
				</li>
				
				<li>
					<a href="#" id="BICYCLING" class="travel-mode-trigger"><?php echo $labels['bicycling']; ?></a>
				</li>
				
				<li>
					<a href="#" id="TRANSIT" class="travel-mode-trigger"><?php echo $labels['transit']; ?></a>
				</li>
			</ul>	
			
			<div id="unit-system-options-wrapper-<?php echo $id; ?>" class="gmw-get-directions-options-wrapper unit-system-options-wrapper">
				<span><?php echo $labels['units_label']; ?></span>
				<ul id="unit-system-options-<?php echo $id; ?>" class="gmw-get-directions-options unit-system-options">				
					<li>
						<input type="radio" id="unit-system-imperial-trigger-<?php echo $id; ?>" name="unit_system_trigger" class="unit-system-trigger"  value="IMPERIAL" checked="checked" />
						<label for="unit-system-imperial-trigger"><?php echo $labels['units_mi']; ?></label>
					</li>
					<li>
						<input type="radio" id="unit-system-metric-trigger-<?php echo $id; ?>"   name="unit_system_trigger" class="unit-system-trigger"  value="METRIC" />
						<label for="unit-system-metric-trigger"><?php echo $labels['units_km']; ?></label>
					</li>
				</ul>
			</div>
			
			<div id="route-avoid-options-wrapper-<?php echo $id; ?>" class="gmw-get-directions-options-wrapper route-avoid-options-wrapper">
				<span><?php echo $labels['avoid_label']; ?></span>
				<ul id="route-avoid-options-<?php echo $id; ?>" class="gmw-get-directions-options route-avoid-options">
					<li>
						<input type="checkbox" id="route-avoid-highways-trigger-<?php echo $id; ?>" class="route-avoid-trigger" value="1" />
						<label for="route-avoid-highways-trigger-<?php echo $id; ?>"><?php echo $labels['avoid_hw']; ?></label>
					</li>
					
					<li>
						<input type="checkbox" id="route-avoid-tolls-trigger-<?php echo $id; ?>"    class="route-avoid-trigger" value="1" />
						<label for="route-avoid-tolls-trigger-<?php echo $id; ?>"><?php echo $labels['avoid_tolls']; ?></label>
					</li>
	
				</ul>
        	</div>
        	<input type="hidden" class="gmw-directions-form-id" value="<?php echo $id; ?>" />	
        	<input type="hidden" class="gmw-form-id" value="<?php echo $gmw['ID']; ?>" />		
		</form>
		
	</div>
	<?php 	
}
/**
 * GMW function - live directions results panel. To be used with live directions function only.
 * @param unknown_type $info
 * @param unknown_type $gmw
 */
function gmw_live_directions_panel( $info, $gmw ) {
	$id = $info->ID;
	?><div id="directions-panel-wrapper-<?php echo $id; ?>" class="directions-panel-wrapper"></div><?php 	
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
            travelMode: google.maps.TravelMode.DRIVING
        };
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
                if ('{$gmw['units_array']['name']}' == 'Mi') {
                    totalDistance = (Math.round(result.routes[0].legs[0].distance.value * 0.000621371192 * 10) / 10) + 'mi';
                } else {
                    totalDistance = (Math.round(result.routes[0].legs[0].distance.value * 0.01) / 10) + 'km';
                }
                jQuery('#gmw-driving-distance-{$info->ID} span.distance').text(totalDistance);
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

	$maxPages = ceil( $maxPages );
	
	$args = apply_filters( 'gmw_get_pagination_args', array(
			'base'         		 => add_query_arg( $pageName, '%#%' ),
			'format'       		 => '',
			'total'        		 => $maxPages,
			'current'      		 => max( 1, get_query_var( $pageName ) ),
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

        echo '<select name="gmw_per_page" class="gmw-per-page gmw-'.$gmw['ID'].'-per-page gmw-'.$gmw['prefix'].'-per-page gmw-'.$gmw['prefix'].'-per-page-dropdown">';

        foreach ( $perPage as $pp ) {
        	$pp_s = "";
            if ( isset( $_GET['gmw_per_page'] ) && $_GET['gmw_per_page'] == $pp ) {
                $pp_s = 'selected="selected"';
        	}                
            echo '<option value="'.$pp.'" '.$pp_s.'>'.$pp.' '.$gmw['labels']['search_results']['per_page'].'</option>';
        }
        echo '</select>';
    
    echo "<script> 
        jQuery(document).ready(function($) {

            $('.gmw-{$gmw['ID']}-per-page').change(function() {

                var totalResults = {$totalCount};
                var lastPage     = Math.ceil(totalResults / $(this).val());
                var newPaged     = ( {$paged} > lastPage || lastPage == 1 ) ? lastPage : {$paged};

                if ( window.location.search.indexOf('gmw_post') === -1 ) {     
                	window.location.href = window.location.href + '?gmw_auto=auto&gmw_per_page='+$(this).val() + '&gmw_form={$gmw['ID']}&{$pagName}='+newPaged;           			   		
			   	} else {
			   		window.location.href = location.href.replace(/([?&]gmw_per_page)=([^#&]*)/g, '$1='+$(this).val()).replace(/([?/]page[?/])([^#/]*)/g,'$1'+newPaged);
			   	}
            });
        });
    </script>";
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