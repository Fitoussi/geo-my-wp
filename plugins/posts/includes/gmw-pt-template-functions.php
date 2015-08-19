<?php
/**
 * PT Search form function - Posts, Pages post types dropdown.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_form_get_post_types_dropdown( $gmw, $title, $class, $all_label ) {

	if ( empty( $gmw['search_form']['post_types'] ) )
		$gmw['search_form']['post_types'] = array( 'post' );
	
    if ( count( $gmw['search_form']['post_types'] ) == 1 ) { 	
    	$output = '<input type="hidden" id="gmw-single-post-type-' . $gmw['ID'] . '" class="gmw-single-post-type gmw-single-post-type-' . $gmw['ID'] . ' ' . $class . '" name="'.$gmw['url_px'].'post" value="'.implode( ' ', $gmw['search_form']['post_types'] ).'" />';
        return apply_filters( 'gmw_form_single_post_types', $output, $gmw, $title, $class, $all_label );
    }
    
    if ( empty( $all_label ) ) 
    	 $all_label = $gmw['labels']['search_form']['search_site'];
    
    $output = '';
    
    if ( isset( $gmw['search_form']['post_types_type'] ) && $gmw['search_form']['post_types_type'] == 'checkbox' ) {
		
    	$output .= '<span class="search-all">'.$all_label.'</span>';
    	$output .= '<ul class="post-types-checkboxes">';

		foreach ( $gmw['search_form']['post_types'] as $post_type ) {
		      
            $pto = get_post_type_object( $post_type );

            if ( empty( $pto ) ) 
               continue;

			if ( isset( $_GET[$gmw['url_px'].'post'] ) && $_GET[$gmw['url_px'].'post'] == $post_type ) {
				$pti_post = 'checked="checked"';
            } else {
				$pti_post = "";
            }

			$output .= '<li id="gmw-'.$gmw['ID'].'-'.$post_type.'-post-type-cb-wrapper" class="post-type-checkbox-wrapper pt-'.$post_type.'">';
			$output .= '<input type="checkbox" name="'.$gmw['url_px'].'post[]" id="gmw-'.$gmw['ID'].'-'.$post_type.'-post-type-cb" class="post-type-checkbox pt-'.$post_type.'" value="'.$post_type.'" '.$pti_post.' checked="checked">';
			$output .= '<label for="gmw-'.$gmw['ID'].'-'.$post_type.'-post-type-cb">'.$pto->labels->name.'</label></li>';
		}
		
	} else {
	
		if ( !empty( $title ) ) {
			$output .= '<label for="gmw-posts-dropdown-'.$gmw['ID'].'">'.$title.'</label>';
		}
		
    	$output .= '<select name="'.$gmw['url_px'].'post" id="gmw-posts-dropdown-' . $gmw['ID'] . '" class="gmw-posts-dropdown gmw-posts-dropdown-'.$gmw['ID'].' '.$class.'">';
    	$output .= '<option value="'.implode( ' ', $gmw['search_form']['post_types'] ).'">'.$all_label.'</option>';

	    foreach ( $gmw['search_form']['post_types'] as $post_type ) {
	
	        $pti_post = ( isset( $_GET[$gmw['url_px'].'post'] ) && $_GET[$gmw['url_px'].'post'] == $post_type ) ? 'selected="selected"' : '';

	        $output .= '<option value="'.$post_type.'" '.$pti_post.'>'.get_post_type_object( $post_type )->labels->name.'</option>';
	
	    }
    	$output .= '</select>';
	}
	
    return apply_filters( 'gmw_form_post_types', $output, $gmw, $title, $class, $all_label );
}

function gmw_pt_form_post_types_dropdown( $gmw, $title, $class, $all ) {
    echo gmw_pt_form_get_post_types_dropdown( $gmw, $title, $class, $all );
}

/**
 * PT search form function - Display taxonomies/categories
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_get_form_taxonomies( $gmw, $tag, $class ) {
	
	$tag	= ( !empty( $tag ) )   ? $tag   : 'div';
	$class  = ( !empty( $class ) ) ? $class : '';
	
    $taxonomies = apply_filters( 'gmw_search_form_taxonomies', false, $gmw, $tag, $class );
   
    if ( $taxonomies !== false )
        return;

    if ( count( $gmw['search_form']['post_types'] ) != 1  || ( !isset( $gmw['search_form']['taxonomies'] ) ) ) 
    	return;

    $postType = $gmw['search_form']['post_types'][0];
    
    if ( !isset($gmw['search_form']['taxonomies'][$postType] ) || empty( $gmw['search_form']['taxonomies'][$postType] ) )
    	return;
        
	$output = '';
	$orgTag = $tag;
		
	if ( $tag == 'ul' || $tag == 'ol' ) {
		$subTag = 'li';
	} else {
		$tag = 'div';
		$subTag = 'div';
	}

	if ( $tag != 'div' )
		$output .= '<'.$tag.'>';
	
    //output dropdown for each taxonomy 
	foreach ( $gmw['search_form']['taxonomies'][$postType] as $tax => $values ) :
      
		if ( isset( $values['style'] ) && $values['style'] == 'drop' ) :

        	$taxonomy   = get_taxonomy( $tax );
        	$tax_value = false;
			
       		$single_tax = '<'.$subTag.' class="gmw-single-taxonomy-wrapper gmw-dropdown-taxonomy-wrapper gmw-dropdown-' . $tax . '-wrapper '.$class.'">';

            if ( apply_filters( 'gmw_pt_show_tax_label', true, $gmw, $taxonomy, $values ) ) {
       		   $single_tax .= '<label for="' . $taxonomy->rewrite['slug'] . '">' . apply_filters( 'gmw_pt_' . $gmw['ID'] . '_' . $tax . '_label', $taxonomy->labels->singular_name .':', $tax, $gmw ) . '</label>';
            }

        	if ( isset( $_GET['tax_' . $tax] ) )
            	$tax_value = sanitize_text_field( $_GET['tax_' . $tax] );

        	$args = apply_filters( 'gmw_pt_dropdown_taxonomy_args', array(
        			'taxonomy'        => $tax,
        			'echo'            => false,
        			'hide_empty'      => 1,
        			'depth'           => 10,
        			'hierarchical'    => 1,
        			'show_count'      => 0,
        			'class'           => 'gmw-dropdown-' . $tax . ' gmw-dropdown-taxonomy',
        			'id'              => $tax . '-tax',
        			'name'            => 'tax_' . $tax,
        			'selected'        => $tax_value,
        			'show_option_all' => sprintf( __( 'All %s', 'GMW' ), $taxonomy->labels->name )
        	), $gmw, $taxonomy, $tax, $values );

       		$single_tax .= wp_dropdown_categories( $args );
       		$single_tax .= '</'.$subTag.'>';

         	$output .= apply_filters( 'gmw_search_form_taxonomy', $single_tax, $gmw, $args, $tag, $class, $tax, $taxonomy );

        endif;

    endforeach;
    
    if ( $tag != 'div' )
    	$output .= '</'.$tag.'>';
    
    return $output;
      
}

	function gmw_pt_form_taxonomies( $gmw, $tag, $class ) {
		echo gmw_pt_get_form_taxonomies( $gmw, $tag, $class );
	}
	
/**
 * GMW function - Query taxonomies/categories dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_query_taxonomies( $tax_args, $gmw ) {

    if ( !isset( $gmw['search_form']['taxonomies'] ) || empty( $gmw['search_form']['taxonomies'] ) )
        return $tax_args;

    $ptc = ( isset( $_GET[$gmw['url_px'].'post'] ) ) ? count( explode( " ", $_GET[$gmw['url_px'].'post'] ) ) : count( $gmw['search_form']['post_types'] );

    if ( isset( $ptc ) && $ptc > 1 )
        return $tax_args;

    $rr       = 0;
    $get_tax  = false;
    $args     = array( 'relation' => 'AND' );
    $postType = $gmw['search_form']['post_types'][0];
    
    if ( empty( $gmw['search_form']['taxonomies'][$postType] ) )
    	return;
    
    foreach ( $gmw['search_form']['taxonomies'][$postType] as $tax => $values ) {

    	if ( $values['style'] == 'drop' ) {
    		 
    		$get_tax = false;
    		if ( isset( $_GET['tax_' . $tax] ) )
    			$get_tax = sanitize_text_field( $_GET['tax_' . $tax] );

    		if ( $get_tax != 0 ) {
    			$rr++;
    			$args[] = array(
    					'taxonomy' => $tax,
    					'field'    => 'id',
    					'terms'    => array( $get_tax )
    			);
    		}
    	} 
    }

    if ( $rr == 0 )
        $args = false;

    return $args;

}
add_filter( 'gmw_pt_tax_query', 'gmw_pt_query_taxonomies', 10, 2 );

/**
 * PT results function - Display taxonomies per result.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_get_taxonomies( $gmw, $post ) {

    if ( !isset( $gmw['search_results']['custom_taxes'] ) )
        return;
        
   	$taxonomies = apply_filters( 'gmw_pt_results_taxonomies', get_object_taxonomies( $post->post_type, 'names' ), $gmw, $post );
    
    $output ='';

    foreach ( $taxonomies as $tax ) {
    		
    	$terms = get_the_terms( $post->ID, $tax );

    	if ( $terms && !is_wp_error( $terms ) ) {

    		$termsArray = array();
    		$the_tax = get_taxonomy( $tax );
       
    		foreach ( $terms as $term ) {
    			$termsArray[] = $term->name;
    		}

    		$tax_output  = '<div class="gmw-taxes gmw-taxonomy-' . $the_tax->rewrite['slug'] . '">';
    		$tax_output .= 	'<span class="tax-label">' . $the_tax->labels->singular_name . ': </span>';
    		$tax_output .= 	'<span class="gmw-terms-wrapper gmw-'.$the_tax->rewrite['slug'].'-terms">'.join( ", ", $termsArray ).'</span>';
    		$tax_output .= '</div>';
    		
    		$output .= apply_filters( 'gmw_pt_results_taxonomy', $tax_output, $gmw, $post, $taxonomies, $the_tax, $terms, $termsArray );

    	}
    }
    
   	return $output;
}

function gmw_pt_taxonomies( $gmw, $post ) {
    echo gmw_pt_get_taxonomies( $gmw, $post );
}

/**
 * PT results function - Day & Hours.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_get_days_hours( $post, $gmw ) {

    $days_hours = get_post_meta( $post->ID, '_wppl_days_hours', true );
    $output     ='';
    $dh_output  = '';
    $dc         = 0;

    if ( !empty( $days_hours ) && is_array( $days_hours ) ) {

        foreach ( $days_hours as $day ) {
            if ( array_filter( $day ) ) {
                $dc++;
                $dh_output .= '<li class="single-days-hours"><span class="single-day">'.esc_attr( $day['days'] ).': </span><span class="single-hour">'.esc_attr( $day['hours'] ).'</span></li>';
            }
        }
    }

    if ( $dc > 0 ) {

        $output .= '<ul class="opening-hours-wrapper">';
        $output .= '<h4>'. esc_attr( $gmw['labels']['search_results']['opening_hours'] ).'</h4>';
        $output .= $dh_output;
        $output .= '</ul>';

    } elseif ( !empty( $nr_message ) && ( empty( $days_hours ) ) ) {
        $output .='<p class="days-na">' . esc_attr( $nr_message ) . '</p>';
    }

    return $output;
}

    function gmw_pt_days_hours( $post, $gmw ) {
        echo gmw_pt_get_days_hours( $post, $gmw );
    }