<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display address fields in search results
 * 
 * @param  [type] $object [description]
 * @param  array  $gmw    [description]
 * @return [type]         [description]
 */
function gmw_search_results_address( $object, $gmw = array() ) {

    if ( empty( $gmw['search_results']['address_fields'] ) ) {
        $fields = array( 'formatted_address' );
    } else {
        $fields = $gmw['search_results']['address_fields'];
    }

    $output = gmw_get_location_address( $object, $fields, $gmw );

    if ( $output != false ) {
        echo '<i class="gmw-icon-location-thin"></i>'.$output;
    }
}

/**
 * Display address that links to a new page with Google Map
 * 
 * @param  [type] $post [description]
 * @param  array  $gmw  [description]
 * @return [type]       [description]
 */
function gmw_search_results_linked_address( $post, $gmw = array() ) {

    if ( ! isset( $gmw['search_results']['address_fields'] ) ) {
        $fields = array( 'formatted_address' );
    } else {
        $fields = $gmw['search_results']['address_fields'];
    }

    $output = gmw_get_linked_location_address( $post, $fields, $gmw );

    if ( $output != false ) {
        echo '<i class="gmw-icon-location-thin"></i>'.$output;
    }
}

/**
 * Get the distance to location
 * 
 * @param  object $info the item info
 * @param  array  $gmw  the form being used
 * 
 * @return string       distance + units
 */
function gmw_search_results_distance( $object = array(), $gmw = array() ) {
    $distance = gmw_get_distance_to_location( $object );
    if ( $distance ) {
        echo '<span class="distance">'.$distance.'</span>';
    }
}

/**
 * Display list of location meta in search results
 * 
 * @param  [type]  $object [description]
 * @param  array   $gmw    [description]
 * @param  boolean $label  [description]
 * @return [type]          [description]
 */
function gmw_search_results_location_meta( $object, $gmw = array(), $label = true ) {

    if ( empty( $gmw['search_results']['location_meta'] ) ) {
        return;
    }   
    
    $data = gmw_get_location_meta_list( $object, $gmw['search_results']['location_meta'] );

    if ( $data == false ) {
        return;
    }

    $output = '<div class="gmw-location-meta-wrapper">';

    if ( ! empty( $label ) ) {
        $label = is_string( $label ) ? esc_html( $label ) : __( 'Contact Information', 'geo-my-wp' ); 
        $output .= '<h3>'.$label.'</h3>';
    }
    
    $output .= $data;
    $output .= '</div>';

    echo $output;
}
    
/**
 * Display hours of operation in search results
 * 
 * @param  [type]  $object [description]
 * @param  array   $gmw    [description]
 * @param  boolean $label  [description]
 * @return [type]          [description]
 */
function gmw_search_results_hours_of_operation( $object, $gmw = array(), $label = true ) {

    if ( ! isset( $gmw['search_results']['opening_hours'] ) || $gmw['search_results']['opening_hours'] == '' ) {
        return;
    }   
    
    $data = gmw_get_hours_of_operation( $object );

    if ( $data == false ) {
        return;
    }

    $output = '';

    $output .= '<div class="gmw-hours-of-operation-wrapper">';
    
    if ( ! empty( $label ) ) {
        $label = is_string( $label ) ? esc_html( $label ) : __( 'Hours of operation', 'geo-my-wp' );  
        $output .= '<h3>'.$label.'</h3>';
    }
    
    $output .= $data;
    $output .= '</div>';

    echo $output;
}

/**
 * Display directions link in search results
 * 
 * @param  [type] $object [description]
 * @param  array  $gmw    [description]
 * @return [type]         [description]
 */
function gmw_search_results_directions_link( $object, $gmw = array() ) {

    if ( ! isset( $gmw['search_results']['directions_link'] ) || $gmw['search_results']['directions_link'] == '' ) {
        return;
    }   

    $from_coords = array(
        'lat' => $gmw['lat'],
        'lng' => $gmw['lng']
    );

    echo '<span class="gmw-directions-link">'.gmw_get_directions_link( $object, $from_coords ).'</span>';
}

/**
 * Get directions system
 * @param  [type] $gmw [description]
 * @return [type]      [description]
 */
function gmw_search_results_directions_system( $gmw ) {

    $args = array( 'id' => absint( $gmw['ID'] ) );
    
    echo gmw_get_directions_system( $args );
}

/**
 * Ouput orderby dropdown in search results template file
 *
 * @since 3.0
 * 
 * @param  array  $gmw [description]
 * @return [type]      [description]
 * 
 */
function gmw_get_search_results_orderby_filter( $gmw = array(), $args = false ) {

    if ( empty( $gmw['search_results']['orderby'] ) ) {
        return;
    }

    $orderby = explode( ',', $gmw['search_results']['orderby'] );

    if ( count( $orderby ) < 1 ) {
        return;
    }

    $options = array();

    // generate orderby options
    foreach( $orderby as $item ) {
        
        $item = explode( ':', $item );

        if ( isset( $item[0] ) ) {
            $options[$item[0]] = isset( $item[1] ) ? $item[1] : $item[0];
        }
    } 

    if ( ! $args ) {
        
        $args = array(
            'id' => $gmw['ID'],
        );
    }

    return GMW_Template_Functions_Helper::get_orderby_filter( $args, $options );
}

    function gmw_search_results_orderby_filter( $gmw = array() ) {
        echo gmw_get_search_results_orderby_filter( $gmw );
    }
