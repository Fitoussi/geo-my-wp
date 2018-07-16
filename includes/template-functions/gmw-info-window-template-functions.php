<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display address fields in infow window
 * 
 * @param  [type] $object [description]
 * @param  array  $gmw    [description]
 * @return [type]         [description]
 */
function gmw_info_window_address( $object, $gmw = array() ) {

    if ( empty( $gmw['info_window']['address_fields'] ) ) {
        return;
    }

    $output = gmw_get_location_address( $object, $gmw['info_window']['address_fields'], $gmw );

    if ( $output != false ) {
        echo '<span class="address"><i class="gmw-icon-location-thin"></i>'.$output.'</span>';
    }
}

/**in info-window
 * Display address that links to a new page with Google Map
 * 
 * @param  [type] $post [description]
 * @param  array  $gmw  [description]
 * @return [type]       [description]
 */
function gmw_info_window_linked_address( $post, $gmw = array() ) {

    if ( empty( $gmw['info_window']['address_fields'] ) ) {
        return;
    }

    $output = gmw_get_linked_location_address( $post, $gmw['info_window']['address_fields'], $gmw );

    if ( $output != false ) {
        echo '<i class="gmw-icon-location-thin"></i>'.$output;
    }
}

/**
 * Display distance in AJAX info-window
 * 
 * @param  array  $object [description]
 * @param  array  $gmw    [description]
 * @return [type]         [description]
 */
function gmw_info_window_distance( $object = array(), $gmw = array() ) {
    if ( ! empty( $object->distance ) && $gmw['info_window']['distance'] ) {
        echo '<span class="distance">'.gmw_get_distance_to_location( $object ).'</span>';
    } 
}

/**
 * Display list of location meta in info-window
 * 
 * @param  [type]  $object [description]
 * @param  array   $gmw    [description]
 * @param  boolean $label  [description]
 * @return [type]          [description]
 */
function gmw_info_window_location_meta( $object, $gmw = array(), $label = true ) {

    if ( empty( $gmw['info_window']['location_meta'] ) ) {
        return;
    }   
    
    $data = gmw_get_location_meta_list( $object, $gmw['info_window']['location_meta'] );

    if ( $data == false ) {
        return;
    }

    $output = '<div class="gmw-location-meta-wrapper">';

    if ( ! empty( $label ) ) {
        $label = is_string( $label ) ? esc_html( $label ) : __( 'Contact Information', 'gmw-premium-settings' ); 
        $output .= '<h3>'.$label.'</h3>';
    }
    
    $output .= $data;
    $output .= '</div>';

    echo $output;
}
    
/**
 * Display hours of operation in info-window
 * 
 * @param  [type]  $object [description]
 * @param  array   $gmw    [description]
 * @param  boolean $label  [description]
 * @return [type]          [description]
 */
function gmw_info_window_hours_of_operation( $object, $gmw = array(), $label = true ) {

    if ( empty( $gmw['info_window']['opening_hours'] ) ) {
        return;
    }   
    
    $data = gmw_get_hours_of_operation( $object );

    if ( $data == false ) {
        return;
    }

    $output = '';

    $output .= '<div class="gmw-hours-of-operation-wrapper">';
    
    if ( ! empty( $label ) ) {
        $label = is_string( $label ) ? esc_html( $label ) : __( 'Hours of operation', 'gmw-premium-settings' );  
        $output .= '<h3>'.$label.'</h3>';
    }
    
    $output .= $data;
    $output .= '</div>';

    echo $output;
}

/**
 * Display directions link in info window
 * 
 * @param  [type] $object [description]
 * @param  array  $gmw    [description]
 * @return [type]         [description]
 */
function gmw_info_window_directions_link( $object, $gmw = array() ) {

    if ( ! $gmw['info_window']['directions_link'] ) {
        return;
    }   

    $from_coords = array(
        'lat' => $gmw['lat'],
        'lng' => $gmw['lng']
    );

    echo gmw_get_directions_link( $object, $from_coords );
}

/**
 * Display directions system in info window
 * 
 * @param  [type] $gmw [description]
 * @return [type]      [description]
 */
function gmw_info_window_directions_system( $gmw ) {

    if ( ! $gmw['info_window']['directions_system'] ){
        return;
    }

    $args = array( 'id' => absint( $gmw['ID'] ) );
    
    echo gmw_get_directions_system( $args );
}

/**
 * Posts locator iw functions
 */
if ( gmw_is_addon_active( 'posts_locator' ) ) {

    /**
     * Display featured image in info window
     * 
     * @param  [type] $post [description]
     * @param  array  $gmw  [description]
     * @return [type]       [description]
     */
    function gmw_info_window_featured_image( $post, $gmw = array() ) {

        if ( ! $gmw['info_window']['image']['enabled'] || ! has_post_thumbnail() ) { 
            return;
        }
        ?> 
        <a class="image" href="<?php echo get_permalink( $post->ID ); ?>" >
            <?php 
            the_post_thumbnail( array( 
                $gmw['info_window']['image']['width'], 
                $gmw['info_window']['image']['height'] 
            ) ); 
            ?> 
        </a>                                  
        <?php
    }

    /**
     * Display excerpt in info window
     * 
     * @param  [type] $post [description]
     * @param  array  $gmw  [description]
     * @return [type]       [description]
     */
    function gmw_info_window_post_excerpt( $post, $gmw = array() ) {

	    if ( empty( $gmw['info_window']['excerpt']['enabled'] ) ) {
	        return;
	    }

	    // verify usage value
	    $usage = isset( $gmw['info_window']['excerpt']['usage'] ) ? $gmw['info_window']['excerpt']['usage'] : 'post_content';
	      
	    if ( empty( $post->$usage ) )  {
	    	return;
		}

        $args = array(
            'id'                => $gmw['ID'], 
            'content'           => $post->$usage,
            'words_count'       => $gmw['info_window']['excerpt']['count'],
            'link'              => get_the_permalink( $post->ID ),
            'link_text'         => $gmw['info_window']['excerpt']['link'],
            'enable_shortcodes' => 1
        );

        echo '<div class="excerpt">'.GMW_Template_Functions_Helper::get_excerpt( $args ).'</div>';
    }
}

/**
 * Users Locator functions
 */
if ( gmw_is_addon_active( 'users_locator' ) ) {

    /**
     * Display user avatar in info window
     * 
     * @param  [type] $user [description]
     * @param  array  $gmw  [description]
     * @return [type]       [description]
     */
    function gmw_info_window_user_avatar( $user, $gmw = array() ) {

        if ( ! $gmw['info_window']['image']['enabled'] ) { 
            return;
        }

        $url  = gmw_get_search_results_user_permalink( $user, $gmw );
        ?>   
        <div class="image user-avatar">
            <a href="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( $user->display_name ); ?> avatar">
                <?php 
                $args = array(
                    'width'  => $gmw['search_results']['image']['width'], 
                    'height' => $gmw['search_results']['image']['height']
                );
                echo get_avatar( 
                    $user->ID, '', '', '', $args ); 
                ?>
            </a>
        </div>                                 
        <?php
    }
}

/**
 * BuddyPress group and memebr functions
 */
if ( class_exists( 'buddypress' ) && ( gmw_is_addon_active( 'members_locator' ) || gmw_is_addon_active( 'bp_groups_locator' ) ) ) {
    
    /**
     * Display BP avatar in info window ( for group or member )
     * 
     * @param  [type] $member [description]
     * @param  array  $gmw    [description]
     * @return [type]         [description]
     */
    function gmw_info_window_bp_avatar( $object, $gmw = array() ) {

        if ( ! $gmw['info_window']['image']['enabled'] ) { 
            return;
        }

        $object_type = $gmw['component'] == 'bp_groups_locator' ? 'group' : 'member'; 

        $permalink_function = 'bp_'.$object_type.'_permalink';
        $avatar_function    = 'bp_'.$object_type.'_avatar';
        ?>
        <a class="image" href="<?php $permalink_function(); ?>" >
            <?php 
            $avatar_function( array( 
                'type'   => 'full', 
                'width'  => $gmw['info_window']['image']['width'], 
                'height' => $gmw['info_window']['image']['height']
            ) );  
            ?> 
        </a>                                  
        <?php
    }

    /**
     * Display xprofile fields in search results
     * 
     * @param  [type] $member [description]
     * @param  array  $gmw    [description]
     * @return [type]         [description]
     */
    function gmw_info_window_member_xprofile_fields( $member, $gmw = array() ) {

        if ( ! function_exists( 'gmw_get_member_xprofile_fields' ) ) {
            return;
        }

        // Look for profile fields in form settings
        $total_fields = ! empty( $gmw['info_window']['xprofile_fields']['fields'] ) ? $gmw['info_window']['xprofile_fields']['fields'] : array();

        // look for date profile field in form settings
        if ( $gmw['info_window']['xprofile_fields']['date_field'] != '' ) {
            array_unshift( $total_fields, $gmw['info_window']['xprofile_fields']['date_field'] );
        }
        
        // abort if no profile fields were chosen
        if ( empty( $total_fields ) ) {
            return;
        }

        if ( is_object( $member ) ) {
        	$user_id = $member->id;
        } else if ( is_int( $member ) ) {
        	$user_id = $member;
        } else {
        	return false;
        }

        echo gmw_get_member_xprofile_fields( $user_id, $total_fields );
    }   
}
