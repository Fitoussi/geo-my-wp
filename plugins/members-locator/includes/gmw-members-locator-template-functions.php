<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
    
/**
 * Get buddyPress Xprofile Fields
 * 
 * @version 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_xprofile_fields( $gmw ) {

	if ( ! array_filter( $gmw['search_form']['profile_fields'] ) ) {
		return;
	}

	// Look for profile fields in form settings
	$total_fields = ! empty( $gmw['search_form']['profile_fields']['fields'] ) ? $gmw['search_form']['profile_fields']['fields'] : array();

	// look for date profile field in form settings
	if ( ! empty( $gmw['search_form']['profile_fields']['date_field'] ) ) {
		array_unshift( $total_fields, $gmw['search_form']['profile_fields']['date_field'] );
	}
	
	// abort if no profile fields were chosen
	if ( empty( $total_fields ) ) {
		return;
	}

	$output  = '';
	$output .= '<div id="gmw-search-form-xprofile-fields-'.esc_attr( $gmw['ID'] ).'" class="gmw-search-form-xprofile-fields gmw-fl-form-xprofile-fields">';

	$total_fields = apply_filters( 'gmw_fl_form_xprofile_field_before_displayed', $total_fields, $gmw );
	
	foreach ( $total_fields as $field_id ) {

		// get the field ID
		$field_id = absint( $field_id );
		
		// get the field data
		$field_data = new BP_XProfile_Field( $field_id );

		// field name attribute
		$name_attr = 'field_'.$field_id;
		
		// field label can be modified
		$label = apply_filters( 'gmw_fl_xprofile_form_field_label', $field_data->name, $field_id, $field_data );
		$label = __( $label, 'GMW' );
		$label = esc_attr( $label );
		
		// field class
		$field_class = 'gmw-xprofile-field';
		$fid	= 'field_'. $field_id;//esc_attr( 'gmw-'.$gmw['ID'].'-field-'.$field_id );
		$value  = '';

		// get the submitted value if form submitted
		if ( ! empty( $_GET[$name_attr] ) ) {
			
			$value = ( is_array( $_GET[$name_attr] ) ) ? array_map( 'esc_attr', $_GET[$name_attr] ) : esc_attr( stripslashes( $_GET[$name_attr] ) );

		// otherwise get the default value
		} elseif ( ! $gmw['submitted'] ) {
			
			$value = apply_filters( 'gmw_fl_xprofile_form_default_value', '', $field_id, $field_data );

			if ( ! empty( $value ) ) {
				$value = ( is_array( $value ) ) ? array_map( 'esc_attr', $value ) : esc_attr( stripslashes( $value ) );
			}
		}

		// field wrapper
		$output .= '<div class="gmw-xprofile-field-wrapper editfield field_type_'.esc_attr( $field_data->type ).' field_'.$field_id .' field_'.sanitize_title( $field_data->name ) .'">';
		
		// display field
		switch ( $field_data->type ) {

			// date field
			case 'datebox':
			case 'birthdate':

				// get value
				$max = ! empty( $_GET[$name_attr . '_max'] ) ? esc_attr( stripslashes( $_GET[$name_attr.'_max'] ) ) : '';
				
				$output .= '<label for="'.$fid.'">' . __( 'Age Range (min - max)', 'GMW' ) . '</label>';
				$output .= '<input size="3" type="text" name="'.$name_attr.'" id="'.$fid.'" class="'.$field_class.'" value="'.$value.'" placeholder="'.__( 'Min', 'GMW' ).'" />';
				$output .= '&nbsp;-&nbsp;';
				$output .= '<input size="3" type="text" name="'.$name_attr.'_max" id="'.$fid.'_max" class="'.$field_class.'_max" value="'.$max.'" placeholder="'.__( 'Max', 'GMW' ).'" />';
			break;	

			// textbox field
			case 'textbox':				 
				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<input type="text" name="'.$name_attr.'" id="'.$fid.'" class="'.$field_class.'" value="'.$value.'" />';
			break;

			// number field
			case 'number':
				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<input type="number" name="'.$name_attr.'" id="'.$fid.'" value="'.$value.'" />';
			break;	

			// textarea 
			case 'textarea':
				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<textarea rows="5" cols="40" name="'.$name_attr.'" id="'.$fid.'" class="'.$field_class.'">'.$value.'</textarea>';
			break;

			// selectbox
			case 'selectbox':

				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<select name="'.$name_attr.'" id="'.$fid.'" class="'.$field_class.'">';

				// all option
				$option_all = apply_filters( 'gmw_fl_xprofile_form_dropdown_option_all', __( ' -- All -- ', 'GMW' ), $field_id, $field_data );

				if ( ! empty( $option_all ) ) {
					$output .= '<option value="">'.esc_attr( $option_all ).'</option>';
				}

				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {

					$option   = trim( $child->name );
					$selected = ( $option == $value ) ? "selected='selected'" : "";

					$output .= '<option '.$selected.' value="'.$option.'" />'. __( $option, 'GMW' ). '</label>';
				}
				 
				$output .= '</select>';

			break;

			// field belong to Buddypress Xprofile Custom Fields Type plugin
			case 'select_custom_post_type':

				$options = $field_data->get_children();

				// get the post type need to filter
				$post_type_selected = $options[0]->name;
	            
	            if ( $options ) {

	                $post_type_selected = $options[0]->name;

	                // Get posts of custom post type selected.
	                $posts = new WP_Query( array(
	                    'posts_per_page'	=> -1,
	                    'post_type'      	=> $post_type_selected,
	                    'orderby'        	=> 'title',
	                    'order'          	=> 'ASC'
	                ) );

	                echo '<label for="'.$fid.'">'.$label.'</label>';
					echo '<select name="'.$field_name.'" id="'.$fid.'" class="'.$field_class.'">';

					$option_all = apply_filters( 'gmw_fl_xprofile_form_dropdown_option_all', __( ' -- All -- ', 'GMW' ), $field_id, $field_data );

					if ( ! empty( $option_all ) ) {
						echo '<option value="">'. esc_attr( $option_all ).'</option>';
					}

					if ( $posts ) {

						foreach ( $posts->posts as $post ) {
							
							$selected = ( $post->ID == $value ) ? "selected='selected'" : "";
							echo '<option '.$selected.' value="'.$post->ID.'" />'.$post->post_title.'</option>';
	                    }
					}
					 
					echo '</select>';
	            }

            break;

			// multiselect box
			case 'multiselectbox' :
			case 'multiselect_custom_post_type' :
			case 'multiselect_custom_taxonomy' :

				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<select name="'.$name_attr.'[]" id="'.$fid.'" class="'.$field_class.'" multiple="multiple">';
				
				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {

					$option   = trim( $child->name );
					$selected = ( ! empty( $value ) && in_array( $option, $value ) ) ? "selected='selected'" : "";

					$output .= '<option '.$selected.' value="'.$option.'" />'. __( $option, 'GMW' ) .'</label>';
				}
				 
				$output .= '</select>';

			break;
			
			// radio buttons
			case 'radio':

				$output .= '<div class="radio">';
				$output .= '<span class="label">'.$label.'</span>';

				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {

					$option  = trim( $child->name );
					$checked = ( $child->name == $value ) ? "checked='checked'" : "";
					
					$output .= '<label><input '.$checked.' type="radio" name="'.$name_attr.'" value="'.$option.'" />'. __( $option, 'GMW' ).'</label>';
				}

				$output .= '<a href="#" onclick="event.preventDefault();jQuery(this).closest(\'div\').find(\'input\').prop(\'checked\', false);">'. __( 'Clear', 'buddypress' ). '</a><br/>';
				$output .= '</div>';

			break;

			// checkboxes
			case 'checkbox':

				$output .= '<div class="checkbox">';
				$output .= '<span class="label">'.$label.'</span>';

				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {

					$option	 = trim( $child->name );
					$checked = ( ! empty( $value ) && in_array( $option, $value ) ) ? "checked='checked'" : "";
					
					$output .= '<label><input '.$checked.' type="checkbox" name="'.$name_attr.'[]" value="'.$option.'" />'.$option.'</label>';
				}
				$output .= '</div>';

			break;

			/**
             * Make multiselect_custom_taxonomy field type compatible with
             * GEO my WP.
             * 
             * Buddypress Xprofile Custom Fields Type plugin 
             * 
             * @author Miguel López <miguel@donmik.com>
             *
             */
            case 'multiselect_custom_taxonomy':

	            $name_of_allow_new_tags = 'allow_new_tags';
	            
	            if ( class_exists( 'Bxcft_Field_Type_MultiSelectCustomTaxonomy' ) ) {
	                $name_of_allow_new_tags = Bxcft_Field_Type_MultiSelectCustomTaxonomy::ALLOW_NEW_TAGS;
	            }

	            $options = $field_data->get_children();

	            $taxonomy_selected = false;

	            foreach ( $options as $option ) {
	                
	                if ( $name_of_allow_new_tags !== $option->name && taxonomy_exists( $option->name ) ) {
	                    
	                    $taxonomy_selected = $option->name;
	                    
	                    break;
	                }
	            }

	            if ( $taxonomy_selected ) {

	                $terms = get_terms( $taxonomy_selected, 
	                	array( 'hide_empty' => false ) 
	                );

	                if ( $terms ) {

	                    echo '<label for="' . $fid . '">' . $label . '</label>';
	                    echo '<select name="' . $field_name . '[]" id="' . $fid.'" class="' . $field_class . '" multiple="multiple">';

	                    foreach ( $terms as $term ) {
	                        
	                        $selected = ( ! empty( $value ) && in_array( $term->term_id, $value ) ) ? "selected='selected'" : "";
	                        printf( '<option value="%s"%s>%s</option>',
	                            $term->term_id, $selected, $term->name
	                        );
	                    }

	                    echo "</select>";
	                }
	            }

            break;

			default : 

				$output = apply_filters( 'gmw_fl_get_xprofile_fields', $output, $field_id, $field_data, $name_attr, $label, $field_class, $fid, $value );
			break;

		} // switch

		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}

	function gmw_fl_xprofile_fields( $gmw, $class = false ) {
		echo gmw_get_xprofile_fields( $gmw );
	}

/**
 * Query xprofile fields
 *
 * Note $formValues might come from URL. IT needs to be sanitized before being used
 * 
 * @version 1.0
 * @author Eyal Fitoussi
 * @author Some of the code in this function was inspired by the code written by Andrea Taranti the creator of BP Profile Search - Thank you
 * 
 */
function gmw_fl_query_xprofile_fields( $gmw, $form_values ) {

	if ( class_exists( 'GMW_Members_Locator_Search_Query' ) ) {
		return GMW_Members_Locator_Search_Query::gmw_fl_query_xprofile_fields( $gmw, $form_values );
	}
}

/**
 * Get list of BuddyPress groups in checkboxes or dropdown
 * 
 * @param  [type] $gmw    [description]
 * @param  [type] $usage  [description]
 * @param  [type] $groups [description]
 * @param  [type] $name   [description]
 * @return [type]         [description]
 */
function gmw_fl_get_bp_groups( $gmw, $usage, $groups, $name ) {
	
	// abort if Groups component is inactive
	if ( ! bp_is_active( 'groups' ) ) {
		return;
	}
	
	$gmw['ID'] = esc_attr( $gmw['ID'] );
	$name 	   = esc_attr( $name );
	$usage     = esc_attr( $usage );

	$output = '';
	
	// if checkboxes
	if ( $usage == 'checkbox' ) {

		$output .= '<span class="search-all">'. esc_attr( $gmw['labels']['search_form']['select_groups'] ).'</span>';
		$output .= '<ul id="gmw-'.$gmw['ID'].'-'.$name.'-checkboxes-wrapper" class="gmw-'.$name.'-checkboxes-wrapper gmw-'. esc_attr( $gmw['prefix'] ).'-'.$name.'-checkboxes-wrapper">';
	
	// otherwise, for dropdown
	} else {
		$output .= '<label for="gmw-bp-groups-'.$usage.'-'.$gmw['ID'].'">'.$gmw['labels']['search_form']['select_groups'].'</label>';
		$output .= '<select name="'.$name.'" id="gmw-bp-groups-dropdown-'.$gmw['ID'].'" class="gmw-bp-groups-dropdown gmw-'.$gmw['prefix'].'-bp-groups-dropdown">';
		$output .= '<option value="">' . esc_attr( $gmw['labels']['search_form']['no_groups'] ) . '</option>';
		$output .= '<option value="' . implode( ',', $groups ).'">'. esc_attr( $gmw['labels']['search_form']['all_groups'] ) .' </option>';
	}
	
	// if there are any groups
	if ( bp_has_groups() ) {
		
		// do the groups loop
		while ( bp_groups() ) { 

			// get group data
			bp_the_group();
		
			// group ID
			$gid = bp_get_group_id();
			$gid = esc_attr( $gid );

			// check if we need to display the group
			if ( ! in_array( $gid, $groups ) ) 
				continue;
			
			// get the group name
			$gname = bp_get_group_name();
			$gname = esc_attr( $gname );
			$gname = __( $gname, 'GMW' );

			// if doing checkbox
			if ( $usage == 'checkbox' ) {

				$output .= '<li id="gmw-'.$gmw['ID'].'-'.$name.'-'. $gid .'-checkbox-wrapper" class="gmw-'.$name.'-checkbox-wrapper gmw-'.$gmw['prefix'].'-'.$name.'-checkbox-wrapper">';
				$output .= '<input type="checkbox" id="gmw-'.$gmw['ID'].'-'.$name.'-'. $gid .'-checkbox" value="'.$gid.'" name='.$name.'[]" />';
				$output .= '<label for="gmw-'.$gmw['ID'].'-'.$name.'-'.$gid.'-checkbox">'.$gname.'</label>';
				$output .= '</li>';
			
			// dropdown option
			} else {

				$output .= '<option value="'.$gid.'">'.$gname.'</option>';
			}			
		}
	}
	
	if ( $usage == 'checkbox' ) {
		$output .= '<ul>';
	} else {
		$output .= '</select>';
	}
	
	return apply_filters( 'gmw_fl_get_bp_groups', $output, $gmw, $usage, $groups, $name );
}

	function gmw_fl_bp_groups( $gmw, $usage, $groups, $name ) {
		echo gmw_fl_get_bp_groups( $gmw, $usage, $groups, $name );
	}