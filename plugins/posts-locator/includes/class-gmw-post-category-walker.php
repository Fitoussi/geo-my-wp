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

	public $category_icons = false;

	/**
	 * Start level
	 * @param  &$output 
	 * @param  integer $depth
	 * @param  array   $args  
	 * @return [type]
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {

		if ( $args['usage'] != 'checkbox' ) {
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
		
		if ( $args['usage'] != 'checkbox' ) {
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

		$value 	   = ! empty( $args['selected'] ) ? $args['selected'] : array();
		$term_name = $args['show_count'] ? $term->name . '&nbsp;(' . $term->count . ')' : $term->name;
		$term_id   = absint( $term->term_id );

		if ( 'checkbox' == $args['usage'] ) {
			
			$checked 		  	 = '';
			$icon_checked     	 = '';
			$category_icon_ok 	 = false;
    		$category_icon_class = '';

    		// get icons only once
    		if ( $args['category_icons'] && ! $this->category_icons ) {
    			$icons = gmw_get_icons();
    			$this->category_icons = $icons['pt_category_icons'];
    		}
    		
			if ( in_array( $term->term_id, $value ) ) {
				$checked 	  = 'checked="checked"';
				$icon_checked = 'checked';	
			}
			
			if ( isset( $this->category_icons['set_icons'][$term->term_id] ) ) {
				$category_icon_ok 	 = true;
				$category_icon_class = ' category-icon';
				$icon = esc_url( $this->category_icons['url'].$this->category_icons['set_icons'][$term->term_id] );
			}
			
			$checkbox  = '<li class="gmw-taxonomy-checkbox-wrapper term-'.$term_id.$category_icon_class.'">'; 
			$checkbox .= '<label>';
			$checkbox .= '<input type="checkbox" name="tax['.esc_attr( $args['taxonomy'] ).'][]" id="'.$term_id.'" class="gmw-taxonomy-checkbox" value="'.$term_id.'" '.$checked.'/>';
			
			if ( $category_icon_ok ) {
				$checkbox .= '<img class="category-icon gmw-checkbox-cat-icon'.$icon_checked.'" src="'.$icon.'" onclick="jQuery(this).toggleClass(\'checked\');" />';
			}
			
			$checkbox .= esc_html( $term_name );
			$checkbox .= '</label>';
			$checkbox .= '</li>';
			$output   .= $checkbox;
		
		} else { 
			$selected  = in_array( $term->term_id, $value ) ? 'selected="selected"' : '';
			$output   .= "\t<option class=\"level-$depth\" value=\"$term_id\" $selected>$pad $term_name</option>\n";	
		}

		return $output;
	}
}

endif;