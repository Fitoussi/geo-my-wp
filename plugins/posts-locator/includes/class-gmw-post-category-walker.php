<?php

if ( ! class_exists( 'GMW_Post_Category_Walker' ) ) :

/**
 * GMW PT Category Walker class
 */
class GMW_Post_Category_Walker extends Walker {
    	
	var $tree_type = 'category';
	
	var $db_fields = array ( 
		'parent' => 'parent', 
		'id' 	 => 'term_id', 
		'slug' 	 => 'slug' 
	);

	/**
	 * Start level
	 * @param  &$output 
	 * @param  integer $depth
	 * @param  array   $args  
	 * @return [type]
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

		if ( $args['style'] != 'checkbox' ) {
			return; 
		}

		$pad 	 = str_repeat( '&nbsp;', $depth * 3 );
		$output .= "<ul class='gmw-checkbox-children gmw-checkbox-level-{$depth}'>\n";
	}
	
	/**
	 * End level
	 * @param  &$output 
	 * @param  integer $depth  
	 * @param  array   $args   
	 * @return [type]  
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		
		if ( $args['style'] != 'checkbox' ) {
			return;
		}

		$pad 	 = str_repeat( '&nbsp;', $depth * 3 );
		$output .= "</ul>";
	}
		
	/**
	 * @see Walker_Category::start_el
	 */
	public function start_el( &$output, $term, $depth = 0, $args = array(), $current_object_id = 0 ) {
		
		if ( ! empty( $args['hierarchical'] ) ) {
		
			$pad = str_repeat( '&nbsp;', $depth * 3 );
		
		} else {

			$pad = '';
		}

		$tax_value = is_array( $args['selected'] ) ? array_map( 'esc_attr', $args['selected'] ) : esc_attr( $args['selected'] );
		$term_name = $args['show_count'] ? $term->name . '&nbsp;(' . $term->count . ')' : $term->name;
		$term_id   = esc_attr( $term->term_id );

		if ( 'checkbox' == $args['style'] ) {
			
			$checked 		  	 = '';
			$icon_checked     	 = '';
			$category_icon_ok 	 = false;
			$display_none 	  	 = '';
    		$category_icon_class = '';
    		
			if ( is_array( $tax_value ) && in_array( $term->term_id, $tax_value ) ) {
				$checked 	  = 'checked="checked"';
				$icon_checked = 'checked';	
			}
			
			if ( ! empty( $args['cat_icons']['icons'][$term->term_id] ) ) {
				$category_icon_ok 	 = true;
				$display_none 	  	 = 'style="display:none;"';
				$category_icon_class = 'category-icons';
			}
				
			$checkbox  = '<li class="gmw-checkbox-wrapper checkbox-id-'.$term_id.'-wrapper '.$category_icon_class.'">'; 
			$checkbox .= '<input type="checkbox" name="tax_'.esc_attr( $args['taxonomy'] ).'[]" id="gmw-checkbox-id-'.$term_id.'" class="gmw-single-checkbox '.esc_attr( $args['taxonomy'] ).'" value="'.$term_id.'" '.$checked.' '.$display_none.' />';
			
			if ( $category_icon_ok ) {
				$checkbox .= '<img id="gmw-cat-icon-'.$term_id.'" class="category-icon gmw-checkbox-cat-icons '.$icon_checked.'" src="'.esc_url( $args['cat_icons']['url'].$args['cat_icons']['icons'][$term_id] ).'" onclick="jQuery(this).toggleClass(\'checked\');jQuery(this).closest(\'li.gmw-checkbox-wrapper\').find(\'.gmw-single-checkbox\').click();" />';
			}
			
			$checkbox .= '<label for="gmw-checkbox-id-'.$term_id.'">'.esc_attr( $term_name ).' </label>';
			$checkbox .= '</li>';
			$output   .= apply_filters( "gmw_search_form_display_checkbox", $checkbox, $term, $args['taxonomy'], $tax_value );
		
		} else { 
			$selected  = ( ( isset( $tax_value ) && $tax_value == $term->term_id ) || ( is_array( $tax_value ) && ! empty( $tax_value ) && in_array( $term->term_id, $tax_value ) ) ) ? 'selected="selected"' : '';
			$output   .= "\t<option class=\"level-$depth\" value=\"$term_id\" $selected>$pad $term_name</option>\n";	
		}
	}
}

endif;