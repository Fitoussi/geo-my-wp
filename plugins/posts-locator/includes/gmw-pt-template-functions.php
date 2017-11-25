<?php
/**
 * get search form post types dropdown
 * 
 * @Since 1.0
 * 
 * @author Eyal Fitoussi
 */
if ( ! function_exists( 'gmw_get_search_form_post_types' ) ) {

    function gmw_get_search_form_post_types( $gmw ) {

        $gmw_id = esc_attr( $gmw['ID'] );
        $url_px = esc_attr( $gmw['url_px'] );

        // default it to post if not already set
    	if ( empty( $gmw['search_form']['post_types'] ) ) {
    		$gmw['search_form']['post_types'] = array( 'post' );
        }
    	
        // if a single post type entered, make it hidden field.
        if ( count( $gmw['search_form']['post_types'] ) == 1 ) { 	
        	
            $output = '<input type="hidden" id="gmw-single-post-type-'.$gmw_id.'" class="gmw-single-post-type" name="'.$url_px.'post" value="'.implode( ' ', $gmw['search_form']['post_types'] ).'" />';
            
            return apply_filters( 'gmw_form_single_post_types', $output, $gmw, false, false, false );
        }
        
        $output = '';
        
        // if checkboxes - for premium extension only
        if ( isset( $gmw['search_form']['post_types_type'] ) && $gmw['search_form']['post_types_type'] == 'checkbox' ) {
    		
        	$output .= '<span class="search-all">'.esc_html( $gmw['labels']['search_form']['search_site'] ).'</span>';
        	
            $output .= '<ul class="post-types-checkboxes">';

    		foreach ( $gmw['search_form']['post_types'] as $post_type ) {
    		      
                $pto = get_post_type_object( $post_type );

                if ( empty( $pto ) ) {
                   continue;
                }

    			if ( isset( $_GET[$gmw['url_px'].'post'] ) && $_GET[$gmw['url_px'].'post'] == $post_type ) {
    				$pti_post = 'checked="checked"';
                } else {
    				$pti_post = "";
                }

    			$output .= '<li id="gmw-'.$gmw_id.'-'.$post_type.'-post-type-cb-wrapper" class="post-type-checkbox-wrapper '.$post_type.'">';
    			$output .= '<input type="checkbox" name="'.$gmw['url_px'].'post[]" id="gmw-'.$gmw['ID'].'-'.$post_type.'-post-type-cb" class="post-type-checkbox pt-'.$post_type.'" value="'.$post_type.'" '.$pti_post.' checked="checked">';
    			$output .= '<label for="gmw-'.$gmw['ID'].'-'.$post_type.'-post-type-cb">'.$pto->labels->name.'</label></li>';
    		}
    		
    	} else {
    	   
            $label = apply_filters( 'gmw_forms_post_types_label' , '', $gmw );
    		
            if ( ! empty( $label ) ) {
    			$output .= '<label for="gmw-posts-dropdown-'.$gmw_id.'">'.esc_html( $label ).'</label>';
    		}
    		
        	$output .= '<select name="'.$url_px.'post" id="gmw-posts-dropdown-'.$gmw_id.'" class="gmw-posts-dropdown">';
        	$output .=     '<option value="1">'.esc_html( $gmw['labels']['search_form']['search_site'] ).'</option>';

    	    foreach ( $gmw['search_form']['post_types'] as $post_type ) {
    	
    	        $selected = ( isset( $_GET[$gmw['url_px'].'post'] ) && $_GET[$gmw['url_px'].'post'] == $post_type ) ? 'selected="selected"' : '';

    	        $output .= '<option value="'.esc_attr( $post_type ).'" '.$selected.'>'.esc_attr( get_post_type_object( $post_type )->labels->name ).'</option>';
    	
    	    }
        	$output .= '</select>';
    	}
    	
        return apply_filters( 'gmw_form_post_types', $output, $gmw, false, false, false );
    }
}

    function gmw_search_form_post_types( $gmw ) {
        echo gmw_get_search_form_post_types( $gmw );
    }

/**
 * Display Taxonomies in fron-end search form
 *     
 * @param  array  $gmw   the form being displayed
 * @param  string $tag   taxonomies wrapper element tag
 * @param  string $class deprecated
 * 
 * @return 
 */
if ( ! function_exists( 'gmw_get_search_form_taxonomies' ) ) {

    function gmw_get_search_form_taxonomies( $gmw, $tag = 'div' ) {

        // abort if multiple post types were set.
        if ( empty( $gmw['search_form']['post_types'] ) || count( $gmw['search_form']['post_types'] ) != 1 ) {
            return;   
        }

        $post_type = $gmw['search_form']['post_types'][0];

        // abort if no taxonomies were set for the selected post type.
        if ( empty( $gmw['search_form']['taxonomies'] ) || empty( $gmw['search_form']['taxonomies'][$post_type] ) ) {
            return;
        }

        // modify the wrapping tag
        $tag = apply_filters( 'gmw_search_form_taxnomies_tag', $tag, $gmw );

        $org_tag    = $tag;
        $chosen_set = false;
        $output     = array();

        // set the wrapper element tag
        if ( $org_tag == 'ul' ) {
            $output['list'] = '<ul>';
            $tag = 'li';
        } elseif ( $org_tag == 'ol') {
            $output['list'] = '<ol>';
            $tag = 'li';
        } else {
            $tag = 'div';
        }

        // taxes holder
        $output['taxes'] = '';

        // Loop through and generate taxonomies
        foreach ( $gmw['search_form']['taxonomies'][$post_type] as $tax_name => $values ) {

            // abort if set as pre_defined
            if ( empty( $values['style'] ) || $values['style'] == 'pre_defined' || $values['style'] == 'na' ) {
                continue;
            }
            
            // set some default args
            $gmw_settings    = gmw_get_options_group( 'gmw_options' );
            $tax_name        = esc_attr( $tax_name );   
            $taxonomy        = get_taxonomy( $tax_name );
            $label           = ! empty( $values['label'] ) ? esc_attr( $values['label'] ) : esc_attr( $taxonomy->labels->name );
            $cat_icons       = ( $values['style'] == 'checkbox' && isset( $values['cat_icons'] ) ) ? $gmw_settings['pt_category_icons']['set_icons'] : false;
            $hierarchical    = ( is_taxonomy_hierarchical( $tax_name ) ) ? true : false;

            // support older versions of the plugin.
            // To be removed in the future
            // TODO : remove
            if ( $values['style'] == 'check' ) {
                $values['style'] = 'checkbox';
            } elseif ( $values['style'] == 'drop' ) {
                $values['style'] = 'dropdown';
            }

            $style = esc_attr( $values['style'] );

            //set taxonomy args
            $args = apply_filters( 'gmw_'.$values['style'].'_taxonomy_args', array(
                'taxonomy'          => $tax_name,
                'orderby'           => ! empty( $values['orderby'] ) ? $values['orderby'] : 'id',
                'order'             => ! empty( $values['order'] )   ? $values['order'] : 'ASC',
                'hide_empty'        => 0,
                'include'           => ! empty( $values['include'] ) ? $values['include'] : '',
                'exclude'           => ! empty( $values['exclude'] ) ? $values['exclude'] : '',
                'exclude_tree'      => '',
                'number'            => 0,
                'hierarchical'      => $hierarchical, 
                'child_of'          => 0,
                'pad_counts'        => 1, 
                'selected'          => ! empty( $_GET['tax_'.$tax_name] ) ? $_GET['tax_'.$tax_name] : '',
                'depth'             => $hierarchical ? 0 : -1,
                'cat_icons'         => isset( $values['cat_icons'] ) ? array( 
                    'url'   => $gmw_settings['pt_category_icons']['url'],
                    'icons' => $cat_icons
                ) : false,
                'show_option_all'   => isset( $values['label_within'] ) ? $values['label'] : __( ' - All - ', 'GMW' ),
                'show_count'        => ! empty( $values['show_count'] ) ? 1 : 0,
                'style'             => $values['style'],
                'placeholder'       => isset( $values['label_within'] ) ? $values['label'] : __( 'Choose ', 'GMW' ). $taxonomy->labels->name,
                'no_results_text'   => __( 'No results match', 'GMW' ),
                'multiple_text'     => __( 'Select Some Options', 'GMW' ),
                'multiple'          => true,
            ), $gmw, $taxonomy, $tax_name, $values );
            
            //set terms_hash args. only args that control the output of the terms should be here.
            // This will be used with the cache helper
            $terms_args = array(
                'taxonomy'     => $args['taxonomy'],
                'orderby'      => $args['orderby'],
                'order'        => $args['order'],
                'hide_empty'   => $args['hide_empty'],
                'exclude'      => $args['exclude'],
                'exclude_tree' => $args['exclude_tree'],
                'include'      => $args['include'],
                'hierarchical' => $args['hierarchical'], 
                'child_of'     => $args['child_of']
            );

            // include GMW_Post_Category_Walker file
            if ( ! class_exists( 'GMW_Post_Category_Walker' ) ) {
                include( GMW_PT_PATH . '/includes/class-gmw-post-category-walker.php' );
            }
            
            $tax_output = array();

            // if dropdown style taxonomies
            if ( $values['style'] == 'dropdown' ) {

                $tax_output['wrap'] = "<{$tag} id=\"{$tax_name}-taxonomy-wrapper\" class=\"gmw-form-field-wrapper gmw-single-taxonomy-wrapper {$style} {$tax_name} \">";

                // if showing label    
                if ( ! isset( $values['label_within'] ) ) {
                    $tax_output['label'] = "<label class=\"gmw-field-label\" for=\"{$tax_name}-taxonomy\">{$label}: </label>";
                }
                // select tag
                $tax_output['select'] = "<select name=\"tax_{$tax_name}\" id=\"{$tax_name}-taxonomy\" class=\"gmw-taxonomy {$tax_name} dropdown\">";

                // if option all exists
                
                $tax_output['options'] = '';

                if ( ! empty( $args['show_option_all'] ) ) {
                    $tax_output['options'] .= '<option value="0" selected="selected">'.esc_attr( $args['show_option_all'] ).'</option>';
                }

                // get the taxonomies terms
                $terms = gmw_get_terms( $tax_name, $terms_args );

                // new category walker
                $walker = new GMW_Post_Category_Walker;

                // run the category walker
                $tax_output['options'] .= $walker->walk( $terms, $args['depth'], $args );

                // closing select tag
                $tax_output['/select'] = '</select>';

                // taxonomy wrapper end
                $tax_output['/wrap'] = '</'.$tag.'>';

            // Generate your custom style
            } else {
                $tax_output = apply_filters( 'gmw_generate_'.$values['style'].'_taxonomy', $tax_output, $taxonomy, $values, $args, $tag );
            } 
            
            if ( empty( $tax_output ) ) {
                return;
            }

            // modify the taxonomy output
            $tax_output = apply_filters( 'gmw_search_form_taxonomy', $tax_output, $gmw, $args, $taxonomy, $values );
            $tax_output = apply_filters( "gmw_search_form_{$values['style']}_taxonomy", $tax_output, $gmw, $args, $taxonomy, $values );

            $output['taxes'] .= is_array( $tax_output ) ? implode( ' ', $tax_output ) : $tax_output;        
        }

        // end of list wrapper
        if ( $org_tag == 'ul' ) {
            $output['list_tag_close'] = '</ul>';    
        } elseif ( $org_tag == 'ol') {
            $output['list_tag_close'] =  '</ol>';
        }
        
        return implode( ' ', $output );
    }
}

	function gmw_search_form_taxonomies( $gmw, $tag='div' ) {
		echo gmw_get_search_form_taxonomies( $gmw, $tag );
	}
	
if ( ! function_exists( 'gmw_get_taxonomies_list' ) ) {

    /**
     * Display taxonomies per item in the results
     *
     * @Since 1.0
     * 
     * @param  [type] $gmw  [description]
     * @param  [type] $post [description]
     * @return [type]       [description]
     */
    function gmw_get_taxonomies_list( $post, $gmw ) {

        // abort if not set to show taxes
        if ( ! isset( $gmw['search_results']['custom_taxes'] ) ) {
            return;
        }
        
        // get taxonomies attached to the post type
       	$taxonomies = apply_filters( 'gmw_pt_results_taxonomies', get_object_taxonomies( $post->post_type, 'objects' ), $gmw, $post );
        $term_link = apply_filters( 'gmw_taxonomy_term_link_enabled', true, $post, $gmw );

        $output = array();

        // loop through taxonomies 
        foreach ( $taxonomies as $taxonomy ) {
            
            // get terms attached to the post
        	$terms = gmw_get_the_terms( $post->ID, $taxonomy->name );

        	if ( $terms && ! is_wp_error( $terms ) ) {

                $tax_output = array();
        		$terms_list = array();
        		
                //generate comma separated list of terms with or without a link
                foreach ( $terms as $term ) {
        			$terms_list[] = $term_link ? '<a href="'.esc_url( get_category_link( $term->term_id ) ).'">'.esc_attr( $term->name ).'</a>' : esc_attr( $term->name );
        		}

        		$tax_output['wrap']  = '<div class="gmw-taxonomy-terms gmw-taxes '. esc_attr( $taxonomy->rewrite['slug'] ).'">';
        		$tax_output['label'] = '<span class="label">'. esc_attr( $taxonomy->label ) .': </span>';
        		$tax_output['span']  = '<span class="gmw-terms-wrapper">';
                $tax_output['items'] = join( ", ", $terms_list );
                $tax_output['/span'] = '</span>';
        		$tax_output['/wrap'] = '</div>';
        		
                // deprecated. To be removed
        		$tax_output = apply_filters( 'gmw_pt_results_taxonomy', $tax_output, $gmw, $post, $taxonomies, $taxonomy, $terms, $terms_list );

                // filter the taxonomy list outpupt
                $tax_output = apply_filters( 'gmw_results_taxonomy_output', $tax_output, $taxonomy, $post, $gmw, $taxonomies, $terms, $terms_list );

                $output[$taxonomy->name] = implode( ' ', $tax_output );
        	}
        }

        // modify the taxonomies output
        $output = apply_filters( 'gmw_results_taxonomies_output', $output, $post, $taxonomies, $gmw );

        return implode( ' ', $output );
    }
}

    function gmw_taxonomies_list( $post, $gmw ) {
        echo gmw_get_taxonomies_list( $post, $gmw );
    }