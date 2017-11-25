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
			'id'	   	   		=> 0, 
			'content' 	    	=> '',
			'words_count'   	=> '10',
			'link' 				=> '',
			'link_text'			=> __( 'read more...', 'GMW' ),
			'enable_shortcodes' => 1,
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
		
		// enable/disable shortcodes in excerpt
		if ( ! $args['enable_shortcodes'] ) {
			$content = strip_shortcodes( $content );
		}
		
		$content = apply_filters( 'the_content', $content );	
		$content = str_replace( ']]>', ']]&gt;', $content );

		return $content;
	}

	/**
	 * Get pagination
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
			'prev_text'    		 => __( 'Prev', 'GMW' ),
			'next_text'    		 => __( 'Next', 'GMW' ),
			'type'         	 	 => 'array',
			'add_args'     		 => False,
			'add_fragment' 		 => '',
			'before_page_number' => '',
			'after_page_number'  => '',
			'page_name'			 => 'page'
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
	 * Per page dropdown
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
  			'label'			=> __( 'per page', 'GMW' ),
  			'per_page' 		=> '10',
  			'paged'    		=> '1',
  			'total_results' => '',
  			'page_name'		=> 'page',
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

	        $output .= '<select '.$id_attr.' name="'.esc_attr( $args['name'] ).'" class="gmw-per-page '.esc_attr( $args['class'] ).'" onchange="window.location.href=this.value">';

	        foreach ( $args['per_page'] as $value ) {

	        	if ( ! absint( $value ) ) {
	        		continue;
	        	}

	        	$link = add_query_arg( array( 
        			$args['name'] => $value, 
        			'form' 		  => $id, 
        			$paged_name   => 1 
	        	) );

	            $selected = $selected_value == $value ? 'selected="selected"' : '';
	                 
	            $output .= '<option value="'.esc_url( $link ).'" '.$selected.'>'.$value.' '.esc_html( $args['label'] ).'</option>';
	        }
	        
	        $output .= '</select>';
	    }

	    return $output;
	}
}