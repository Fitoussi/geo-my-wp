<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GMW_PT_Search_Form_Helper class
 */
class GMW_PT_Search_Form_Helper {

    /**
     * Generate a single search form taxonomy
     * 
     * @param  array  $args [description]
     * @param  array  $gmw  [description]
     * @return [type]       [description]
     */
    public static function get_taxonomy( $args = array(), $gmw = array() ) {

        $defaults = array(
            'id'                  => 0,
            'taxonomy'            => 'category',
            'usage'               => 'dropdown',
            'show_options_all'    => true,
            'orderby'             => 'id',
            'order'               => 'ASC',
            'include'             => '',
            'exclude'             => '',
            'show_count'          => 0,
            'hide_empty'          => 1,
            'category_icons'      => 0,
            'multiple_selections' => 0
        );

        $args         = wp_parse_args( $args, $defaults );
        $url_px       = gmw_get_url_prefix();
        $id           = absint( $args['id'] );
        $tax_name     = $args['taxonomy'];
        $taxonomy     = get_taxonomy( $tax_name );
        $hierarchical = is_taxonomy_hierarchical( $tax_name ) ? true : false;
        $options_all  = 0;
        $placeholder  = 0;

        if ( ! empty( $args['show_options_all'] ) ) {
            
            if ( $args['show_options_all'] == '' ) {

                $options_all = 0;
                $placeholder = 0;
            
            } elseif ( is_string( $args['show_options_all'] ) ) {

                $options_all = $args['show_options_all'];
                $placeholder = $args['show_options_all'];

            } else {

                $options_all = sprintf( __( 'All %s', 'geo-my-wp' ), $taxonomy->labels->name );
                $placeholder = sprintf( __( 'Select %s', 'geo-my-wp' ), $taxonomy->labels->name );
            }
        } 

        //set taxonomy args
        $tax_args = apply_filters( 'gmw_search_form_'.$args['usage'].'_taxonomy_args', array(
            'taxonomy'            => $tax_name,
            'orderby'             => $args['orderby'],
            'order'               => $args['order'],
            'hide_empty'          => $args['hide_empty'],
            'include'             => $args['include'],
            'exclude'             => $args['exclude'],
            'exclude_tree'        => '',
            'number'              => 0,
            'hierarchical'        => $hierarchical, 
            'child_of'            => 0,
            'pad_counts'          => 1, 
            'selected'            => ! empty( $_GET['tax'][$tax_name] ) ? $_GET['tax'][$tax_name] : '',
            'depth'               => $hierarchical ? 0 : -1,
            'category_icons'      => $args['category_icons'],
            'gmw_form_id'         => $id,
            'show_option_all'     => $options_all,
            'show_count'          => $args['show_count'] == 1 ? 1 : 0,
            'usage'               => $args['usage'],
            'multiple_selections' => $args['multiple_selections'],
            'placeholder'         => $placeholder,
            'no_results_text'     => __( 'No results match', 'geo-my-wp' ),
        ), $taxonomy, $gmw );

        // deprected hook. Will be removed in the future.
        apply_filters( 'gmw_pt_'.$args['usage'].'_taxonomy_args', $tax_args, $gmw, $taxonomy, $tax_name, $args );
            
        //set terms_hash args. only args that control the output of the terms should be here.
        // This will be used with the cache helper
        $terms_args = array(
            'taxonomy'     => $tax_args['taxonomy'],
            'orderby'      => $tax_args['orderby'],
            'order'        => $tax_args['order'],
            'hide_empty'   => $tax_args['hide_empty'],
            'exclude'      => $tax_args['exclude'],
            'exclude_tree' => $tax_args['exclude_tree'],
            'include'      => $tax_args['include'],
            'hierarchical' => $tax_args['hierarchical'], 
            'child_of'     => $tax_args['child_of']
        );

        // include GMW_Post_Category_Walker file
        if ( ! class_exists( 'GMW_Post_Category_Walker' ) ) {
            include_once( GMW_PT_PATH . '/includes/class-gmw-post-category-walker.php' );
        }
            
        $output = '';

        // if dropdown style taxonomies
        if ( $args['usage'] == 'dropdown' ) {

            // select tag
            $output .= "<select name=\"tax[{$tax_name}][]\" id=\"{$tax_name}-taxonomy-{$id}\" class=\"gmw-form-field gmw-taxonomy {$tax_name}\">";

            if ( ! empty( $tax_args['show_option_all'] ) ) {
                $output .= '<option value="" selected="selected">'.esc_attr( $tax_args['show_option_all'] ).'</option>';
            }

            // get the taxonomies terms
            $terms = gmw_get_terms( $tax_name, $terms_args );

            // new category walker
            $walker = new GMW_Post_Category_Walker;

            // run the category walker
            $output .= $walker->walk( $terms, $tax_args['depth'], $tax_args );

            // closing select tag
            $output .= '</select>';

        // Filter to generate your custom style
        } else {
            $output = apply_filters( 'gmw_generate_'.$args['usage'].'_taxonomy', $output, $tax_args, $taxonomy );
        } 

        return $output;
    } 
}

/**
 * Get search form post types field element
 * 
 * @param  [type] $gmw [description]
 * @return [type]      [description]
 */
function gmw_get_search_form_post_types( $args = array(), $post_types = array( 'post' ) ) {

    $url_px = gmw_get_url_prefix();
    
    $defaults = array(
        'id'               => 0,
        'usage'            => 'dropdown',
        'id_tag'           => '',
        'class_tag'        => '',
        'object'           => 'post-types',
        'show_options_all' => __( 'Search site', 'geo-my-wp' ),
        'name_tag'         => $url_px.'post'
    );

    $args = wp_parse_args( $args, $defaults );

    if ( $args['id_tag'] == '' ) {
        $args['id_tag'] = 'gmw-posts-type-'.$args['id'];
    }
    
    // if a single post type we make it a hidden field
    if ( count( $post_types ) == 1 ) {      
        $args['usage'] = 'hidden';
    }
    
    $options = array();

     if ( empty( $post_types ) ) {
        $post_types = array( 'post' );
    }

    foreach( $post_types as $post_type ) {
        
        if ( ( $post_object = get_post_type_object( $post_type ) ) != false ) {
            $options[$post_type] = $post_object->labels->name;
        }
    }

    // generate new post types selector
    return GMW_Search_Form_Helper::options_selector_builder( $args, $options );
}
    
    /**
     * Output post types filter in search form
     * 
     * @param  array  $gmw [description]
     * @return [type]      [description]
     */
    function gmw_search_form_post_types( $gmw = array() ) {
        
        if ( isset( $gmw['search_form']['post_types_settings'] ) ) {

            $settings = $gmw['search_form']['post_types_settings'];

        // for different cases like Global Maps
        } elseif ( isset( $gmw['search_form']['post_types_usage'] ) ) {

            $settings = array(
                'usage' => $gmw['search_form']['post_types_usage']
            );
        
        } else {

            $settings = array();
        }

        $args = array(
            'id'               => $gmw['ID'],
            'usage'            => isset( $settings['usage'] ) ? $settings['usage'] : 'dropdown',
            'show_options_all' => isset( $settings['show_options_all'] ) ? $settings['show_options_all'] : __( 'Search site', 'geo-my-wp' ),
        );

        $element = gmw_get_search_form_post_types( $args, $gmw['search_form']['post_types'] );

        // if a single post type we make it a hidden field
        if ( count( $gmw['search_form']['post_types'] ) == 1 ) {      
            $args['usage'] = 'hidden';
        }
        
        $output = '';

        // if multiple post types selected we wrap it within a div
        if ( $args['usage'] != 'pre_defined' && $args['usage'] != 'hidden' ) {

            $output .= '<div class="gmw-form-field-wrapper gmw-post-types-wrapper gmw-post-types-'.esc_attr( $args['usage'] ).'">';

            if ( ! empty( $settings['label'] ) ) {

                $tag = ( $args['usage'] == 'checkboxes' ) ? 'span' : 'label';

                $output .= '<'.$tag.' class="gmw-field-label">'.esc_attr( $settings['label'] ).'</'.$tag.'>';
            }

            $output .= $element;
            $output .= '</div>';
        
        } else {
            $output .= $element;
        }

        echo $output;
    }

/**
 * Display taxonomies in fron-end search form
 *     
 * @param  array  $gmw   the form being displayed
 * @param  string $tag   taxonomies wrapper element tag
 * @param  string $class deprecated
 * 
 * @return 
 */
function gmw_get_search_form_taxonomies( $gmw ) {

    // abort if multiple post types were set.
    if ( empty( $gmw['search_form']['post_types'] ) || count( $gmw['search_form']['post_types'] ) != 1 ) {
        return;   
    }

    $post_type = $gmw['search_form']['post_types'][0];

    // abort if no taxonomies were set for the selected post type.
    if ( empty( $gmw['search_form']['taxonomies'] ) || empty( $gmw['search_form']['taxonomies'][$post_type] ) ) {
        return;
    }

    $output = '';

    // Loop through and generate taxonomies
    foreach ( $gmw['search_form']['taxonomies'][$post_type] as $taxonomy => $args ) {

        $usage = $args['style'];

        // abort if set as pre_defined or disabled
        if ( empty( $usage ) || in_array( $usage, array( 'pre_defined', 'disabled', 'na' ) ) ) {
            continue;
        }
        
        $taxonomy = esc_attr( $taxonomy );   

        // support older versions of the plugin.
        // To be removed in the future
        if ( $usage == 'check' ) {
            $usage = 'checkbox';
        } elseif ( $usage == 'drop' ) {
            $usage = 'dropdown';
        }

        $tax_args = array(
            'id'                  => $gmw['ID'],
            'taxonomy'            => $taxonomy,
            'usage'               => $usage,
            'show_options_all'    => isset( $args['show_options_all'] ) ? $args['show_options_all'] : true,
            'orderby'             => ! empty( $args['orderby'] ) ? $args['orderby'] : 'id',
            'order'               => ! empty( $args['order'] )   ? $args['order']   : 'ASC',
            'include'             => ! empty( $args['include'] ) ? $args['include'] : '',
            'exclude'             => ! empty( $args['exclude'] ) ? $args['exclude'] : '',
            'show_count'          => isset( $args['show_count'] ) ? 1 : 0,
            'hide_empty'          => isset( $args['hide_empty'] ) ? 1 : 0,
            'category_icons'      => isset( $args['cat_icons'] ) ? 1 : 0,
            'multiple_selections' => isset( $args['multiple_selections'] ) ? 1 : 0,
        );
        
        $tax_element = GMW_PT_Search_Form_Helper::get_taxonomy( $tax_args, $gmw );

        if ( empty( $tax_element ) ) {
            continue;
        }

        $output .= "<div id=\"{$taxonomy}-taxonomy-wrapper\" class=\"gmw-form-field-wrapper gmw-single-taxonomy-wrapper gmw-{$usage}-taxonomy-wrapper\">";

        // if showing label    
        if ( ! empty( $args['label'] ) ) {
            $output .= '<label class="gmw-field-label" for="'.$taxonomy.'-taxonomy">'.esc_attr( $args['label'] ).'</label>';
        }

        $output .= $tax_element;

        // taxonomy wrapper end
        $output .= '</div>';  
    }

    return $output;
}

	function gmw_search_form_taxonomies( $gmw, $tag='div' ) {
		echo gmw_get_search_form_taxonomies( $gmw, $tag );
	}
	
