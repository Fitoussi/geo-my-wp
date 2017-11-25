<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display featured image in search results
 * 
 * @param  [type] $post [description]
 * @param  array  $gmw  [description]
 * @return [type]       [description]
 */
function gmw_search_results_bp_avatar( $member, $gmw = array() ) {

    if ( ! $gmw['search_results']['image']['enabled'] ) { 
        return;
    }

    ?>   
    <div class="item-avatar">
        <a href="<?php bp_member_permalink(); ?>">    
        <?php 
            bp_member_avatar( array( 
                'type'   => 'full', 
                'width'  => $gmw['search_results']['image']['width'], 
                'height' => $gmw['search_results']['image']['height']
            ) ); 
        ?>
        </a>
    </div>                                 
    <?php
}

/**
 * Get buddyPress Xprofile Fields
 * 
 * @version 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_search_form_xprofile_fields( $gmw ) {

	// Look for profile fields in form settings
	$total_fields = ! empty( $gmw['search_form']['xprofile_fields']['fields'] ) ? $gmw['search_form']['xprofile_fields']['fields'] : array();

	// look for date profile field in form settings
	if ( ! empty( $gmw['search_form']['xprofile_fields']['date_field'] ) ) {
		array_unshift( $total_fields, $gmw['search_form']['xprofile_fields']['date_field'] );
	}
	
	// abort if no profile fields were chosen
	if ( empty( $total_fields ) ) {
		return;
	}

	$output  = '';
	$output .= '<div id="gmw-search-form-xprofile-fields-'.esc_attr( $gmw['ID'] ).'" class="gmw-search-form-xprofile-fields gmw-fl-form-xprofile-fields">';

	$total_fields = apply_filters( 'gmw_fl_form_xprofile_field_before_displayed', $total_fields, $gmw );
	
	$values = isset( $gmw['form_values']['xf'] ) ? $gmw['form_values']['xf'] : array();

	foreach ( $total_fields as $field_id ) {

		$field_id    = absint( $field_id );
		$fid	     = 'field_'. $field_id;
		$field_class = 'gmw-xprofile-field';
		$field_data  = new BP_XProfile_Field( $field_id );
		
		// field label can be modified
		$label = apply_filters( 'gmw_fl_xprofile_form_field_label', $field_data->name, $field_id, $field_data );
		$label = esc_html( $label );
		$value = '';

		// get the submitted value if form submitted
		if ( isset( $values[$field_id] ) ) {
			$value = $values[$field_id];
		// otherwise set default values
		} elseif ( ! $gmw['submitted'] ) {	
			$value = apply_filters( 'gmw_fl_xprofile_form_default_value', '', $field_id, $field_data );
		}

		if ( $value != '' ) {
			$value = is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( stripslashes( $value ) );
		}

		// field wrapper
		$output .= '<div class="gmw-form-field-wrapper gmw-xprofile-field-wrapper editfield field_type_'.esc_attr( $field_data->type ).' field_'.$field_id .' field_'.sanitize_title( $field_data->name ) .'">';
		
		// display field
		switch ( $field_data->type ) {

			// date field
			case 'datebox':
			case 'birthdate':

				if ( ! is_array( $value ) ) {
					$value = array(
						'min' => '',
						'max' => ''
					);
				}

				$output .= '<label for="'.$fid.'">' . __( 'Age Range (min - max)', 'GMW' ) . '</label>';
				$output .= '<input type="number" name="xf['.$field_id.'][min]" id="'.$fid.'_min" class="'.$field_class.' range-min" value="'.$value['min'].'" placeholder="'.__( 'Min', 'GMW' ).'" />';
				$output .= '<input type="number" name="xf['.$field_id.'][max]" id="'.$fid.'_max" class="'.$field_class.' range-max" value="'.$value['max'].'" placeholder="'.__( 'Max', 'GMW' ).'" />';
			break;	

			// textbox field
			case 'textbox':				 
				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<input type="text" name="xf['.$field_id.']" id="'.$fid.'" class="'.$field_class.'" value="'.$value.'" />';
			break;

			// number field
			case 'number':
				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<input type="number" name="xf['.$field_id.']" id="'.$fid.'" value="'.$value.'" />';
			break;	

			// textarea 
			case 'textarea':
				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<textarea rows="5" cols="40" name="xf['.$field_id.']" id="'.$fid.'" class="'.$field_class.'">'.$value.'</textarea>';
			break;

			// selectbox
			case 'selectbox':

				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<select name="xf['.$field_id.']" id="'.$fid.'" class="'.$field_class.'">';

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
					$output  .= '<option '.$selected.' value="'.$option.'">'. __( $option, 'GMW' ). '</option>';
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
	                    'posts_per_page' => -1,
	                    'post_type'      => $post_type_selected,
	                    'orderby'        => 'title',
	                    'order'          => 'ASC'
	                ) );

	                $output .= '<label for="'.$fid.'">'.$label.'</label>';
					$output .= '<select name="xf['.$field_id.']" id="'.$fid.'" class="'.$field_class.'">';

					$option_all = apply_filters( 'gmw_fl_xprofile_form_dropdown_option_all', __( ' -- All -- ', 'GMW' ), $field_id, $field_data );

					if ( ! empty( $option_all ) ) {
						$output .= '<option value="">'. esc_attr( $option_all ).'</option>';
					}

					if ( $posts ) {
						foreach ( $posts->posts as $post ) {
							$selected = ( $post->ID == $value ) ? "selected='selected'" : "";
							echo '<option '.$selected.' value="'.$post->ID.'">'.$post->post_title.'</option>';
	                    }
					}
					 
					$output .= '</select>';
	            }

            break;

			// multiselect box
			case 'multiselectbox' :
			case 'multiselect_custom_post_type' :
			case 'multiselect_custom_taxonomy' :

				$output .= '<label for="'.$fid.'">'.$label.'</label>';
				$output .= '<select name="xf['.$field_id.'][]" id="'.$fid.'" class="'.$field_class.'" multiple="multiple">';
				
				// get options
				$children = $field_data->get_children();

				foreach ( $children as $child ) {
					$option   = trim( $child->name );
					$selected = ( ! empty( $value ) && in_array( $option, $value ) ) ? "selected='selected'" : "";

					$output .= '<option '.$selected.' value="'.$option.'">'. __( $option, 'GMW' ) .'</option>';
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
					
					$output .= '<label><input '.$checked.' type="radio" name="xf['.$field_id.']" value="'.$option.'" />'. __( $option, 'GMW' ).'</label>';
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
					
					$output .= '<label><input '.$checked.' type="checkbox" name="xf['.$field_id.'][]" value="'.$option.'" />'.$option.'</label>';
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

	                    $output .= '<label for="' . $fid . '">' . $label . '</label>';
	                    $output .= '<select name="xf['.$field_id.'][]" id="' . $fid.'" class="' . $field_class . '" multiple="multiple">';

	                    foreach ( $terms as $term ) {
	                        
	                        $selected = ( ! empty( $value ) && in_array( $term->term_id, $value ) ) ? "selected='selected'" : "";
	                        $output .= sprintf( '<option value="%s"%s>%s</option>',
	                            $term->term_id, $selected, $term->name
	                        );
	                    }

	                    $output .= "</select>";
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

	function gmw_search_form_xprofile_fields( $gmw ) {
		echo gmw_get_search_form_xprofile_fields( $gmw );
	}

/**
 * GMW FL search results function - xprofile fields
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_member_xprofile_fields( $member_id = 0, $fields = array() ) {

	if ( empty( $fields ) ) {
		return;
	}

	$output = '';

	foreach ( $fields as $field_id ) {
			
		$field_data  = new BP_XProfile_Field( $field_id );
		$field_value = xprofile_get_field_data( $field_id, $member_id );

		if ( empty( $field_value ) ) {
			continue;
		}

		if ( $field_data->type == 'datebox' )  {
			$age = intval( date( 'Y', time() - strtotime( $field_value ) ) ) - 1970;
			$field_value 	  =   sprintf( __( ' %s Years old','GMW' ), $age );
			$field_data->name = __( 'Age','GMW' );
		}
			
		$output .= '<li class="gmw-xprofile-field type-'.esc_attr( $field_data->type ).' id-'.esc_attr( $field_id ).'">';
		$output .= '<span class="label">'.esc_html( $field_data->name ).':</span>';
		$output .= '<span class="field">';		
		$output .= is_array( $field_value ) ? implode( ', ' , $field_value ) : $field_value;
		$output .= '</span>';
		$output .= '</li>';
	}

	return $output == '' ? false : '<ul class="gmw-xprofile-fields">'.$output.'</ul>';
}
