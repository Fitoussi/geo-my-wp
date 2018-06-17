<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Search Form Helper class
 *
 * @author Eyal Fitoussi
 * 
 * @Since 3.0
 */
class GMW_Search_Form_Helper {

	/**
	 * Options selector field generator
	 * 
	 * @param  array  $args    [description]
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */
	public static function options_selector_builder( $args = array(), $options = array() ) {

		$defaults = array(
            'id'               => 0,
            'id_tag'		   => '',
            'class_tag'        => '',
            'usage'            => 'dropdown',
            'object'       	   => '',
            'show_options_all' => '',
            'options'		   => array(),
            'name_tag'         => ''
        );

		// can pass $options via args or separate array as argument
		if ( ! empty( $options ) ) {
			$args['options'] = $options;
		}

        $args = wp_parse_args( $args, $defaults );

        // modify the args
        $args = apply_filters( 'gmw_search_form_options_selector_builder_args', $args );
        $args = apply_filters( 'gmw_search_form_'.str_replace( '-', '_', $args['object'] ).'_options_selector_builder_args', $args );

        $id_tag = $args['id_tag'] != '' ? 'id="'.$args['id_tag'].'"' : '';     

        $output = '';

        if ( $args['usage'] == 'hidden' ) {

	        foreach ( $args['options'] as $value => $name ) {
	        	$output .= '<input type="hidden" name="'.esc_attr( $args['name_tag'] ).'[]" value="'.esc_attr( sanitize_text_field( $value ) ).'" />';
	        }

	    // dropdown
        } else if ( $args['usage'] == 'dropdown' ) {
	            
	        $output .= '<select name="'.esc_attr( $args['name_tag'] ).'[]" '.$id_tag.' class="gmw-form-field gmw-'.esc_attr( $args['object'] ).'-field '.esc_attr( $args['class_tag'] ).'">';

	        if ( $args['show_options_all'] != '' ) {
	            $output .= '<option value="">'.esc_html( $args['show_options_all'] ).'</option>';
	        }

	        foreach ( $args['options'] as $value => $name ) {
	    	
	            $selected = ( isset( $_GET[$args['name_tag']] ) && in_array( $value, $_GET[$args['name_tag']] ) ) ? 'selected="selected"' : '';

	            $output .= '<option value="'.esc_attr( $value ).'" '.$selected.'>'.esc_html( $name ).'</option>';
	        }

	        $output .= '</select>';  

	    // add custom styles
	    } else {
            $output .= apply_filters( 'gmw_search_form_'.$args['usage'].'_options_selector', $output, $args, $args['options'] );
	    }

	    return $output;
	}

	/**
	 * Keywords field
	 *
	 * @since 3.0
	 *  
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function keywords_field( $args = array() ) {

		$url_px = gmw_get_url_prefix();

		$defaults = array(
			'id'		  => 0, 
			'placeholder' => __( 'Enter keywords', 'geo-my-wp' ),
			'class'		  => '',
			'name_tag'	  => $url_px.'keywords'
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_forms_keywords_args', $args );
 
	    $value  = ! empty( $_GET[$url_px.'keywords'] ) ? esc_attr( sanitize_text_field( stripslashes( $_GET[$url_px.'keywords'] ) ) ) : '';

	    return '<input type="text" id="gmw-keywords-'.absint( $args['id'] ).'" class="gmw-form-field keywords-field '.esc_attr( $args['class'] ).'" name="'.esc_attr( $args['name_tag'] ).'" value="'.$value.'" placeholder="'.esc_attr( $args['placeholder'] ).'" />';
	}

	/**
	 * Address fields
	 *
	 * @since 3.0
	 *  
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function address_field( $args = array() ) {

		$url_px = gmw_get_url_prefix();

		$defaults = array(
			'id'			   	   => 0,
			'id_attr'			   => '',
			'class_attr'		   => '',
			'placeholder'		   => __( 'Enter address', 'geo-my-wp' ), 
			'locator_button'	   => 1,
			'locator_submit'	   => 0,
			'icon'				   => 'gmw-icon-target-light',
			'mandatory'			   => 0,
			'address_autocomplete' => 1,
			'name_attr'			   => $url_px.'address[]',
			'value'				   => '',
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_forms_address_args', $args );

	    $mandatory 	  = $args['mandatory'] ? 'mandatory' : '';
		$placeholder  = isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';
		$autocomplete = $args['address_autocomplete'] ? 'gmw-address-autocomplete' : '';

		if ( isset( $_GET[$url_px.'address'] ) ) {
			$value = is_array( $_GET[$url_px.'address'] ) ? implode( ' ', $_GET[$url_px.'address'] ) : $_GET[$url_px.'address'];
			$value = esc_attr( stripslashes( $value ) );
		} else if ( $args['value'] != '' ) {
			$value = esc_attr( stripslashes( $args['value'] ) );
		} else {
			$value = '';
		}
		
		$id = esc_attr( $args['id'] );
		$id_attr = $args['id_attr'] != '' ? esc_attr( $args['id_attr'] ) : 'gmw-address-field-'.$id;

	    $output = '<input type="text" name="'.esc_attr( $args['name_attr'] ).'" id="'.$id_attr.'" class="gmw-form-field gmw-address gmw-full-address '.$mandatory.' '.$autocomplete.' '.esc_attr( $args['class_attr'] ).'" value="'.$value.'" placeholder="'.$placeholder.'" autocorrect="off" autocapitalize="off" spellcheck="false" />';
	   
	    // if the locator button in within the address field
	    if ( $args['locator_button'] ) {
	    	$output .= '<i class="gmw-locator-button inside '.$args['icon'].'" data-locator_submit="'.esc_attr( $args['locator_submit'] ).'" data-form_id="'.$id.'"></i>';
	    }
	    
	    return $output;
	}

	/**
	 * Radius field
	 *
	 * @since 3.0
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function radius_field( $args = array() ) {

		$url_px = gmw_get_url_prefix();

		$defaults = array(
			'id'			=> 0, 
			'class'			=> '',
			'label'		    => __( 'Miles', 'geo-my-wp' ),
			'default_value' => '',
			'options'		=> '10,15,25,50,100',
			'name_tag'		=> $url_px.'distance'
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_forms_radius_args', $args );

		$id      = absint( $args['id'] );
		$options = explode( ',', $args['options'] );
		$default_value = ! empty( $args['default_value'] ) ? esc_attr( $args['default_value'] ) : end( $options );
		
		$output = '';

		if ( count( $options ) > 1 ) {
		    
	        $output .= '<select id="gmw-distance-'.$id.'" class="gmw-form-field distance '.esc_attr( $args['class'] ).'" name="'.esc_attr( $args['name_tag'] ).'">';
	        $output .= 	'<option value="'.$default_value.'">'.esc_attr( $args['label'] ).'</option>';

	        foreach ( $options as $option ) {
	      
	        	if ( ! is_numeric( $option ) ) {
	        		continue;
	        	}

	            $selected = ( isset( $_GET[$url_px.'distance'] ) && $_GET[$url_px.'distance'] == $option ) ? 'selected="selected"' : '';

	            $output .= '<option value="'.$option.'" '.$selected.'>'.$option.'</option>';
	        }
	        $output .= '</select>';

		} else {
	        $output = '<input type="hidden" name="'.$url_px.'distance" value="'.$options[0].'" />';
		}
				
	    return $output;
	}

	/**
	 * Radius field
	 *
	 * @since 3.0
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function units_field( $args = array() ) {

		$defaults = array(
			'id'	   => 0, 
			'class'	   => '',
			'units'	   => 'imperial',
			'mi_label' => __( 'Miles', 'geo-my-wp' ),
			'km_label' => __( 'Kilometers', 'geo-my-wp' )
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_forms_units_args', $args );

		$url_px = gmw_get_url_prefix();
		$id     = absint( $args['id'] );

		if ( $args['units'] == 'both' ) {
		        
	        $selected = ( isset( $_GET[$url_px.'units'] ) && $_GET[$url_px.'units'] == 'metric' ) ? 'selected="selected"' : '';
	  	
	        $output  = '<select name="'.$url_px.'units" id="gmw-units-'.$id.'" class="gmw-form-field units '.esc_attr( $args['class'] ).'">';
	        $output .= '<option value="imperial" selected="selected">'.esc_attr( $args['mi_label'] ).'</option>';
	        $output .= '<option value="metric" '.$selected.'>'.esc_attr( $args['km_label'] ).'</option>';
	        $output .= "</select>";

		} else {
	        $output = '<input type="hidden" id="gmw-units-'.$id.'" name="'.$url_px.'units" value="'.esc_attr( sanitize_text_field( $args['units'] ) ).'" />';
		} 
		
		return $output;
	}

	/**
	 * Locator button
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function locator_button( $args = array() ) {

		$defaults = array(
			'id'	      => 0, 
			'class'		  => '',
			'usage'	      => 'image',
			'image' 	  => 'locate-me-blue.png',
			'form_submit' => 0,
			'label'		  => __( 'Get my current location', 'geo-my-wp' )
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_forms_locator_button_args', $args );

		$id    = absint( $args['id'] );
		$usage = esc_attr( $args['usage'] );

		$output = '';

		// when using an icon
		if ( $usage == 'image' ) {
							
			$img_url = GMW_IMAGES .'/locator-images/'.$args['image'];

			$output .= '<img id="gmw-locator-image-'.$id.'" class="gmw-locator-button image '.esc_attr( $args['class'] ).'" data-locator_submit="'. absint( $args['form_submit'] ).'" src="'.esc_url( $img_url ).'" alt="'.__( 'locator button', 'geo-my-wp' ).'" data-form_id="'.$id.'" />';

		// text button
		} elseif ( $usage == 'text' ) {

			$label = ! empty( $args['label'] ) ? esc_attr( $args['label'] ) : '';

			$output .= '<span id="gmw-locator-text-'.$id.'" class="gmw-locator-button text" data-locator_submit="'.absint( $args['form_submit'] ).'" data-form_id="'.$id.'">'. esc_attr( $args['label'] ) .'</span>';
		}
		
		$output .= '<i id="gmw-locator-loader-'.$id.'" class="gmw-locator-loader gmw-icon-spin animate-spin" style="display:none;"></i>';

		return $output;
	}
	/**
	 * Submit Button
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function submit_button( $args = array() ) {

		$defaults = array( 
			'id'	=> 0,
			'class' => '',
			'label' => __( 'Submit', 'geo-my-wp' )
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_forms_submit_button_args', $args );

		$args['id'] = absint( $args['id'] );

		if ( ! empty( $args['id'] ) ) {
			$id = 'gmw-submit-'.$args['id'];
		} else {
			$id = 'gmw-submit';
		}

		return '<input type="submit" id="'.$id.'" class="gmw-submit gmw-submit-button '.esc_attr( $args['class'] ).'" value="'.esc_attr( $args['label'] ).'" />';
	}

	/**
	 * Hidden submission fields
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function submission_fields( $id = 0, $per_page = '' ) {

		$id 	  = absint( $id );
		$per_page = esc_attr( $per_page );
		$url_px   = gmw_get_url_prefix();
		$lat 	  = ! empty( $_GET[$url_px.'lat'] ) ? esc_attr( urldecode( $_GET[$url_px.'lat'] ) ) : '';
		$lng      = ! empty( $_GET[$url_px.'lng'] ) ? esc_attr( urldecode( $_GET[$url_px.'lng'] ) ) : '';
		$state    = ! empty( $_GET['state'] ) 	   ? esc_attr( urldecode( $_GET['state'] ) ) : false;
		$country  = ! empty( $_GET['country'] ) 	   ? esc_attr( urldecode( $_GET['country'] ) ) : false;

		// generate fields
	    $output  = "<div id=\"gmw-submission-fields-{$id}\" class=\"gmw-submission-fields\" data-form_id=\"{$id}\" style=\"display:none\">";
	    // set the page number to 1. We do this to reset the page number when form submitted again
	    $output .= "<input type=\"hidden\" id=\"gmw-page-{$id}\" class=\"gmw-page\" name=\"page\" value=\"1\" />";
	    $output .= "<input type=\"hidden\" id=\"gmw-per-page-{$id}\" class=\"gmw-per-page\" name=\"{$url_px}per_page\" value=\"{$per_page}\" />";   	
	    $output .= "<input type=\"hidden\" id=\"gmw-lat-{$id}\" class=\"gmw-lat\" name=\"{$url_px}lat\" value=\"{$lat}\"/>";      	
	    $output .= "<input type=\"hidden\" id=\"gmw-lng-{$id}\" class=\"gmw-lng\" name=\"{$url_px}lng\" value=\"{$lng}\"/>";
	    $output .= "<input type=\"hidden\" id=\"gmw-form-id-{$id}\" class=\"gmw-form-id\" name=\"{$url_px}form\" value=\"{$id}\" />";

	    $disabled = $state == false ? 'disabled="disabled"' : '';
	    $output .= "<input type=\"hidden\" id=\"gmw-state-{$id}\" class=\"gmw-state\" name=\"state\" value=\"{$state}\" {$disabled}/>";      	
	    $disabled = $country == false ? 'disabled="disabled"' : '';
	    $output .= "<input type=\"hidden\" id=\"gmw-country-{$id}\" class=\"gmw-country\" name=\"country\" value=\"{$country}\" {$disabled}/>";

	    $output = apply_filters( 'gmw_submission_fields', $output );

	    $output .= '</div>';

	    return $output;
	}

	/**
	 * Search form BP Member Types filter
	 * 
	 * @param  array  $gmw [description]
	 * @return [type]      [description]
	 */
	public static function bp_member_types_filter( $args = array(), $member_types = array() ) {

		$url_px = gmw_get_url_prefix();
      	
        $defaults = array(
            'id'               => 1,
            'usage'            => 'smartbox',
            'id_tag'           => '',
            'class_tag'        => '',
            'name_tag'         => $url_px.'bpmt',
            'object'           => 'bp-member-types',
            'show_options_all' => __( 'Search member types', 'geo-my-wp' )
        );

        $args = wp_parse_args( $args, $defaults );

        if ( $args['id_tag'] == '' ) {
            $args['id_tag'] = 'gmw-bp-member-types-'.$args['id'];
        }
        	      	        
        /**
      	 * If types are not provided
      	 * 
      	 * we will get all types rgistered types.
      	 */
      	if ( empty( $member_types ) ) {
	      	
	      	$member_types = array();

	        foreach( bp_get_member_types( array(), 'object' ) as $type ) {
	            $member_types[$type->name] = $type->labels['name'];
	        }
	    }

	    return GMW_Search_Form_Helper::options_selector_builder( $args, $member_types );
	}

	/**
     * BP groups filter
     *
     * We place this function here since multiple add-ons will be using it.
     *
     * So we do not want to have the functiosn duplicated.
     * 
     * @param  array  $args   [description]
     * @param  array  $groups [description]
     * @return [type]         [description]
     */
    public static function bp_groups_filter( $args = array(), $groups = array() ) {

        if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'groups' ) ) {
            return;
        }

        $url_px = gmw_get_url_prefix();
      
        $defaults = array(
            'id'               => 1,
            'usage'            => 'smartbox',
            'id_tag'           => '',
            'class_tag'        => '',
            'name_tag'         => $url_px.'bp_groups',
            'object'           => 'bp-groups',
            'show_options_all' => __( 'Search groups', 'gmw-premium-settings' )
        );

        $args = wp_parse_args( $args, $defaults );

        if ( $args['id_tag'] == '' ) {
            $args['id_tag'] = 'gmw-bp-groups-'.$args['id'];
        }
        
        /**
         * Use BP built in class to have more options when pulling groups from database.
         * This might be a bit more memory consuming and so it is disabled by default.
         */
        if ( apply_filters( 'gmw_ps_advanced_get_bp_groups_list', false ) ) {

            $groups = BP_Groups_Group::get( apply_filters( 'gmw_search_form_get_groups_list_args', array(
                'type'               => 'alphabetical',
                'per_page'           => 999,
                'orderby'            => 'date_created',
                'order'              => 'DESC',
                'page'               => null,
                'user_id'            => 0,
                'slug'               => array(),
                'search_terms'       => false,
                'search_columns'     => array(),
                'group_type'         => '',
                'group_type__in'     => '',
                'group_type__not_in' => '',
                'meta_query'         => false,
                'include'            => ! empty( $groups ) ? $groups : '',
                'parent_id'          => null,
                'update_meta_cache'  => true,
                'update_admin_cache' => false,
                'exclude'            => false,
                'show_hidden'        => false,
                'status'             => array()
            ) ) );

            $groups = $groups['groups'];

        /**
         * Simpler method to retrive the list of groups
         */
        } else {

            global $wpdb;

            $where = '';

            if ( ! empty( $groups ) ) {
                $groups     = array_map( 'absint', $groups );
                $groups_var = implode( ',', $groups );
                $where      = "WHERE id IN ( {$groups_var} )";
            }

            $groups = $wpdb->get_results( "
                SELECT id, name 
                FROM {$wpdb->prefix}bp_groups
                {$where}
                "
            );
        }

        $options = array();

        foreach( $groups as $group ) {
            
            if ( $group->id == 0 ) {
                continue;
            }

            $options[$group->id] = $group->name;
        }

        $args['options'] = $options;

        // get the list of groups
        return GMW_Search_Form_Helper::options_selector_builder( $args );
    }
}
