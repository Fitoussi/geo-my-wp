<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display the search results message
 * 
 * @param  [type] $gmw [description]
 * @return [type]      [description]
 */
function gmw_get_results_message( $gmw )  {
    return ! empty( $gmw['results_message'] ) ? esc_html( $gmw['results_message'] ) : '';
}

    function gmw_results_message( $gmw ) {
        echo gmw_get_results_message( $gmw );
    }

/**
 * No results message
 * 
 * @param  array  $gmw [description]
 * @return [type]      [description]
 */
function gmw_get_no_results_message( $gmw = array() ) {

    // allowed characters can be filtered
    $allowed = array(
        'a' => array(
            'href'  => array(),
            'title' => array(),
            'alt'   => array()
        ),
        'br'     => array(),
        'em'     => array(),
        'strong' => array(),
        'p'      => array()
    );

    $message = isset( $gmw['no_results_message'] ) ? $gmw['no_results_message'] : '';
    
    // filter the no results message
    $message = apply_filters( 'gmw_no_results_message', $message, $gmw );

    return wp_kses( $message, $allowed );
}
    function gmw_no_results_message( $gmw = array() ) {
        echo gmw_get_no_results_message( $gmw );
    }

/**
 * Generate map in search results
 * 
 * @version 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_results_map( $gmw, $init_visible = true, $implode = true ) {
    
    $args = array( 
        'map_id'         => $gmw['ID'],
        'prefix'         => $gmw['prefix'],
        'map_type'       => $gmw['addon'],
        'map_width'      => $gmw['results_map']['map_width'],
        'map_height'     => $gmw['results_map']['map_height'],
        'expand_on_load' => ! empty( $gmw['results_map']['expand_on_load'] ) ? true : false,
        'init_visible'   => $init_visible
    );

    return GMW_Maps_API::get_map_element( $args, $implode );
}
    
    /**
     * Output map in search results template file
     * 
     * @param  [type]  $gmw        [description]
     * @return [type]              [description]
     */
    function gmw_results_map( $gmw, $init_visible = true ) {
        
        if ( $gmw['map_usage'] != 'results' ) {
            return;
        }

        //if ( ! $in_results && $gmw['map_usage'] != 'shortcode' ) {
        //    return;
        //}
            
        do_action( "gmw_before_map",                  $gmw );
        do_action( "gmw_{$gmw['prefix']}_before_map", $gmw );
        
        echo gmw_get_results_map( $gmw, $init_visible );
      
        do_action( "gmw_after_map",                  $gmw );
        do_action( "gmw_{$gmw['prefix']}_after_map", $gmw );
    }

    /**
     * Output map in shortcode
     * 
     * @param  [type]  $gmw        [description]
     * @param  boolean $in_results [description]
     * @return [type]              [description]
     */
    function gmw_shortcode_map( $gmw ) {
        
        if ( $gmw['map_usage'] != 'shortcode' ) {
            return;
        }
            
        do_action( "gmw_before_shortcode_map", $gmw );
        
        echo gmw_get_results_map( $gmw );
      
        do_action( "gmw_after_shortcode_map", $gmw );
    }

/**
 * GMW Pagination function
 *
 * This function uses the WordPress function paginate_links();
 * 
 * @version 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_pagination( $gmw = array() ) {

    // pagination arguments. 
    $args = array(
        'id'                 => $gmw['ID'],
        'total'              => $gmw['max_pages'],
        'prev_text'          => __( 'Prev', 'geo-my-wp' ),
        'next_text'          => __( 'Next', 'geo-my-wp' ),
        'page_name'          => $gmw['paged_name']
    );

    return GMW_Template_Functions_Helper::get_pagination( $args );  
}

    function gmw_pagination( $gmw = array() ) {
        echo gmw_get_pagination( $gmw );
    }

/**
 * GMW Pagination function for ajax forms
 * 
 * @version 3.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_ajax_pagination( $gmw = array() ) {

    // pagination arguments. 
    $args = array(
        'id'                 => $gmw['ID'],
        'total'              => $gmw['max_pages'],
        'prev_text'          => __( 'Prev', 'geo-my-wp' ),
        'next_text'          => __( 'Next', 'geo-my-wp' ),
        'current'            => $gmw['paged']
    );

    return GMW_Template_Functions_Helper::get_ajax_pagination( $args );
}

    function gmw_ajax_pagination( $gmw = array() ) {
        echo gmw_get_ajax_pagination( $gmw );
    }

/**
 * Display per page dropdown in search results 
 * 
 * @since 1.0
 * 
 * @author Eyal Fitoussi
 */
function gmw_get_per_page( $gmw = array() ) {
    
    $args = array(
        'id'            => $gmw['ID'],
        'label'         => __( 'Per page', 'geo-my-wp' ),
        'per_page'      => $gmw['page_load_action'] ? explode( ",", $gmw['page_load_results']['per_page'] ) : explode( ",", $gmw['search_results']['per_page'] ),
        'paged'         => $gmw['paged'],
        'total_results' => $gmw['total_results'],
        'page_name'     => $gmw['paged_name'],
        'submitted'     => $gmw['submitted']            
    );

    return GMW_Template_Functions_Helper::get_per_page( $args );
}
    
    function gmw_per_page( $gmw = array() ) {
        echo gmw_get_per_page( $gmw );
    }

/**
 * Get the distance to location
 * 
 * @param  object $object the item object
 * 
 * @return string distance + units
 */
function gmw_get_distance_to_location( $object = array() ) {
    
    if ( empty( $object->distance ) ) {
        return false;
    }
    
    $distance = $object->distance . ' ' . $object->units;
    $distance = apply_filters( 'gmw_distance_to_location', $distance, $object );
    
    return esc_attr( $distance );
}

    function gmw_distance_to_location( $object = array() ) {
        echo gmw_get_distance_to_location( $object );
    }

/**
 * Display excerpt
 *
 * Display specific number of words and add a read more link to 
 * a content.
 * 
 * @param unknown_type $post
 * @param unknown_type $gmw
 * 
 * @param unknown_type $count
 */
function gmw_get_excerpt( $args = array(), $gmw = false ) {

    // temporary, to support older search results template files
    if ( is_object( $args ) && ! empty( $gmw ) ) {

        trigger_error( 'Do not use gmw_get_excerpt nor gmw_excerpt functions directly to retrive the post excerpt in the search results template file. Use gmw_search_results_post_excerpt functions instead. Since GEO my WP 3.0.' , E_USER_NOTICE );

        echo gmw_search_results_post_excerpt( $args, $gmw );

        return;
    }

    if ( empty( $args['content'] ) ) {
        return;
    }

    return GMW_Template_Functions_Helper::get_excerpt( $args );
}

    function gmw_excerpt( $args = array(), $gmw = false ) {
        echo gmw_get_excerpt( $args, $gmw );
    }

/**
 * Display hours of operation 
 * 
 * @param  [type] $object [description]
 * 
 * @return [type]         [description]
 *
 * @since 3.0
 */
function gmw_get_hours_of_operation( $object ) {

    // if location ID
    if ( is_int( $object ) ) {
    
        $days_hours = gmw_get_location_meta( $object, 'days_hours' );
    
    } elseif ( is_object( $object ) && ! empty( $object->object_type ) && ! empty( $object->object_id ) ) {
        
        $days_hours = gmw_get_location_meta_by_object( $object->object_type, $object->object_id, 'days_hours' );
    
    } else {
        return;
    }
    
    $output = '';
    $data   = '';
    $count  = 0;

    if ( ! empty( $days_hours ) && is_array( $days_hours ) ) {

        foreach ( $days_hours as $day ) {

            if ( array_filter( $day ) ) {

                $days = esc_attr( $day['days'] );

                $count++;
                $data .= '<li class="day '.$days.'"><span class="days">'.$days.': </span><span class="hours">'.esc_attr( $day['hours'] ).'</span></li>';
            }
        }
    }

    if ( $count == 0 ) {
        return false;
    }

    $output = '';
    $output .= '<ul class="gmw-hours-of-operation">';
    $output .= $data;
    $output .= '</ul>';
    
    return $output;
}

    function gmw_hours_of_operation( $location ) {
        echo gmw_get_hours_of_operation( $location );
    }
