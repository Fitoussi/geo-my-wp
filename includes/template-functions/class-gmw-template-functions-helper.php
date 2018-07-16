<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GMW_Template_Functions_Helper class
 *
 * @author Eyal Fitoussi
 * 
 * @Since 3.0
 */
class GMW_Template_Functions_Helper {

	/**
	 * Get Excerpt
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function get_excerpt( $args = array() ) {

		$defaults = array(
			'id'	   	   		 => 0, 
			'content' 	    	 => '',
			'words_count'   	 => '10',
			'link' 				 => '',
			'link_text'			 => __( 'read more...', 'geo-my-wp' ),
			'enable_shortcodes'  => 1,
			'the_content_filter' => 1
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_results_excerpt_args', $args );

		if ( empty( $args['content'] ) ) {
			return;
		}

		$content = $args['content'];
		
		// trim number of words
		if ( ! empty( $args['words_count'] ) ) {

			// generate read more link
			if ( ! empty( $args['link_text'] ) && ! empty( $args['link'] ) ) {		
				$more_link = ' <a href="'.esc_url( $args['link'] ).'" class="gmw-more-link">'.esc_html( $args['link_text'] ).'</a>';
			} else {
				$more_link = '';
			}
			
			$content = wp_trim_words( $content, $args['words_count'], $more_link );
		}	
		
		// disable shortcodes in excerpt
		if ( ! $args['enable_shortcodes'] ) {
			
			$content = strip_shortcodes( $content );

		// enable shortcodes
		} else {

			if ( $args['the_content_filter'] ) {

				$content = apply_filters( 'the_content', $content, 50 );
			
			// use this filter instead of the_content to prevent conflicts with
			// other plugins and themes.
			} else {

				$content = apply_filters( 'wpautop', $content, 50 );
			}

			$content = str_replace( ']]>', ']]>', $content );
		}

		return $content;
	}

	/**
	 * Get pagination.
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function get_pagination( $args = array() ) {

		$defaults = array(
			'id'				 => 0,
			'base'         		 => '',
			'format'       		 => '',
			'total'        		 => '1',
			'current'      		 => '',
			'show_all'     		 => False,
			'end_size'     		 => 1,
			'mid_size'     		 => 2,
			'prev_next'    		 => True,
			'prev_text'    		 => __( 'Prev', 'geo-my-wp' ),
			'next_text'    		 => __( 'Next', 'geo-my-wp' ),
			'type'         	 	 => 'array',
			'add_args'     		 => False,
			'add_fragment' 		 => '',
			'before_page_number' => '',
			'after_page_number'  => '',
			'page_name'			 => 'page',
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_get_pagination_args', $args );

		$page_name 	   = is_front_page() ? 'page' : $args['page_name'];
		$args['total'] = ceil( $args['total'] );

		if ( $args['base'] == '' ) {
			$args['base'] = add_query_arg( $page_name, '%#%' );
		}

		if ( $args['current'] == '' ) {
			$args['current'] = max( 1, get_query_var( $page_name ) );
		}

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

		return apply_filters( 'gmw_get_pagination_output', $output, $pags, $args );
	}

	/**
	 * Pagination for ajax forms.
	 *
	 * @since 3.0
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function get_ajax_pagination( $args = array() ) {

		$defaults = array(
			'id'				 => 0,
			'total'        		 => '1',
			'current'      		 => '1',
			'show_all'     		 => False,
			'end_size'     		 => 3,
			'mid_size'     		 => 3,
			'prev_next'    		 => True,
			'prev_text'    		 => __( 'Prev', 'geo-my-wp' ),
			'next_text'    		 => __( 'Next', 'geo-my-wp' ),
			'add_fragment' 		 => ''
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_get_ajax_pagination_args', $args );

		if ( $args['total'] <= 1 ) {
			return false;
		};

		$start_pages = range( 1, $args['end_size'] );
		$end_pages   = range( $args['total'] - $args['end_size'] + 1, $args['total'] );
		$mid_pages   = range( $args['current'] - $args['mid_size'], $args['current'] + $args['mid_size'] );
		$pages       = array_intersect( range( 1, $args['total'] ), array_merge( $start_pages, $end_pages, $mid_pages ) );
		$prev_page   = 0;

		$output = '<ul>';
			
		if ( $args['current'] && $args['current'] > 1 ) {

			$output .= '<li><a href="#" class="prev page-numbers" data-page="'.( $args['current'] - 1 ).'">'.$args['prev_text'].'</a></li>';
		}

		foreach ( $pages as $page ) {
			
			if ( $prev_page != $page - 1 ) {

				$output .= '<li><span class="dots">...</span></li>';
			}
			
			if ( $args['current'] == $page ) {
				
				$output .= '<li><span class="page-numbers current" data-page="'.$page.'">'.$page.'</span></li>';
			
			} else {
				
				$output .= '<li><a href="#" class="page-numbers" data-page="'.$page.'">'.$page.'</a></li>';
			}

			$prev_page = $page;
		}

		if ( $args['current'] && $args['current'] < $args['total'] ) {
			
			$output .= '<li><a href="#" class="next page-numbers" data-page="'.( $args['current'] + 1 ).'">'.$args['next_text'].'</a></li>';
		}
	
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Per page dropdown
	 *
	 * @since 3.0
	 * 
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public static function get_per_page( $args = array() ) {
  		
  		$url_px = gmw_get_url_prefix();

  		$defaults = array(
  			'id' 	   		=> 0,
  			'id_attr'       => '',
  			'class'			=> '',
  			'name'          => $url_px.'per_page',
  			'label'			=> __( 'per page', 'geo-my-wp' ),
  			'per_page' 		=> '10',
  			'paged'    		=> '1',
  			'total_results' => '',
  			'page_name'		=> 'page',
  			'ajax_enabled'  => false,
  			'submitted'		=> 0			
  		);

  		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_per_page_args', $args );

		$id      = absint( $args['id'] );
        $id_attr = $args['id_attr'] != '' ? 'id="'.esc_attr( $args['id_attr'] ).'"' : '';
	 
	   	$paged_name     = is_front_page() ? 'page' : esc_attr( $args['page_name'] );
	    $selected_value = isset( $_GET[$args['name']] ) ? $_GET[$args['name']] : reset( $args['per_page'] );

	    $output = '';

	    if ( count( $args['per_page'] ) > 1 ) {

	    	$on_change = ! $args['ajax_enabled'] ? 'onchange="window.location.href=this.value"' : '';

	        $output .= '<select '.$id_attr.' name="'.esc_attr( $args['name'] ).'" class="gmw-per-page '.esc_attr( $args['class'] ).'" '.$on_change.'>';

	        foreach ( $args['per_page'] as $value ) {

	        	if ( ! absint( $value ) ) {
	        		continue;
	        	}


	        	if ( $args['ajax_enabled'] ) {
	        		
	        		$option = $value;
	        	
	        	} else {

		        	$option = esc_url( add_query_arg( array( 
	        			$args['name'] => $value, 
	        			'form' 		  => $id, 
	        			$paged_name   => 1 
		        	) ) );
		       	}

	            $selected = $selected_value == $value ? 'selected="selected"' : '';
	                 
	            $output .= '<option value="'.$option.'" '.$selected.'>'.$value.' '.esc_html( $args['label'] ).'</option>';
	        }
	        
	        $output .= '</select>';
	    }

	    return $output;
	}

	/**
     * Get orderby filter element
     *
     * @since 3.0
     * 
     * @param  array  $args    [description]
     * @param  array  $options [description]
     * @return [type]          [description]
     */
    public static function get_orderby_filter( $args = array(), $options = array( 'distance' => 'Distance', 'post_title' => 'Title' ) ) {

        if ( $options < 1 ) {
            return;
        }

        $url_px = gmw_get_url_prefix();

        $defaults = array(
            'id'            => 0,
            'id_attr'       => '',
            'class'         => '',
            'name'          => $url_px.'orderby',
            'label'         => __( 'Default order', 'gmw-premium-settings' ),
            'default'       => 'distance',
            'submitted'     => 0,
            'ajax_enabled'  => false,           
        );

        $args = wp_parse_args( $args, $defaults );
        $args = apply_filters( 'gmw_orderby_args', $args );

        $id      = absint( $args['id'] );
        $id_attr = $args['id_attr'] != '' ? 'id="'.esc_attr( $args['id_attr'] ).'"' : '';

        $selected_value = isset( $_GET[$args['name']] ) ? $_GET[$args['name']] : reset( $options );

        $on_change = ! $args['ajax_enabled'] ? 'onchange="window.location.href=this.value"' : '';

        $output = '<select '.$id_attr.' name="'.esc_attr( $args['name'] ).'" class="gmw-orderby-dropdown '.esc_attr( $args['class'] ).'" '.$on_change.'>';

        $output .= '<option value="" selected="selected">'.esc_html( $args['label'] ).'</option>';

        foreach ( $options as $value => $label ) {

        	if ( $args['ajax_enabled'] ) {
	        		
        		$option = esc_attr( $value );
        	
        	} else {

	        	$option = esc_url( add_query_arg( array( 
	                $args['name'] => $value, 
	                'form'        => $id 
	            ) ) );
	       	}
 
            $selected = $selected_value == $value ? 'selected="selected"' : '';
                 
            $output .= '<option value="'.trim( $option ).'" '.$selected.'>'.esc_html( $label ).'</option>';
        }
 
        $output .= '</select>';

        return $output;
    }

    /**
     * Generate results found message using placeholders.
     *
     * @since 3.0
     * 
     * @param  array  $args [description]
     * @param  array  $gmw  [description]
     * @return [type]       [description]
     */
    public static function generate_results_message( $args = array(), $gmw = array() ) {

    	$defaults = array(
            'page'     		   		=> 1,
            'per_page' 		   		=> 1,
            'results_count'    		=> 1,
            'results_count_only'    => 0,
            'total_count'      		=> 1,
            'form_submitted'   		=> false,
            'address'		   		=> '',
            'radius'		   		=> '',
            'units'			   		=> 'imperial',
            'count_message'    		=> __( 'Showing {from_count} - {to_count} of {total_results} locations', 'geo-my-wp' ),
            'single_count_message'  => __( '1 location found', 'geo-my-wp' ),
	    	'location_message' 		=> __( ' within {radius}{units} from {address}', 'geo-my-wp' ), 
        );

        $args = wp_parse_args( $args, $defaults );
        $args = extract( apply_filters( 'gmw_results_found_message', $args, $gmw ) );

		$from_count = intval( ( $page - 1 ) * $per_page ) + 1;
		$to_count   = ( $from_count + ( $per_page - 1 ) > $total_count ) ? $total_count : $from_count + ( $per_page - 1 );
		$units      = $units == 'imperial' ? 'mi' : 'km';
		$output     = '';

		if ( ! empty( $count_message ) ) {

			if ( $results_count == 1 && $total_count == 1 ) {

				$count_message = $single_count_message;
				
			} elseif ( $results_count >! $total_count ) {
				
				$count_message = str_replace( 
					array( '{results_count}', '{total_results}', '{from_count}', '{to_count}' ),
					array( $results_count, $total_count, $from_count, $to_count ),
					$count_message
				);

			// when showing all results
			} else {

				$count_message = str_replace( 
					array( '{results_count}', '{total_results}', '{from_count}', '{to_count}' ),
					array( $results_count, $total_count, '1', $total_count ),
					$count_message
				);
			}

			$output .= $count_message .' ';
		}
		
		if ( ! empty( $location_message ) && $form_submitted && ! empty( $address ) ) {

			$location_message = str_replace( 
				array( '{radius}', '{units}', '{address}' ),
				array( $radius, $units, $address ),
				$location_message
			);

			$output .= $location_message;
		}

		return $output;
    }
}
