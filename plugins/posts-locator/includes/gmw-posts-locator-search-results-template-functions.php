<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display featured image in search results
 * 
 * @param  [type] $post [description]
 * @param  array  $gmw  [description]
 * @return [type]       [description]
 */
function gmw_search_results_featured_image( $post, $gmw = array() ) {

    if ( ! $gmw['search_results']['image']['enabled'] || ! has_post_thumbnail() ) { 
        return;
    }
    ?>                                    
    <div class="post-thumbnail">
        <?php 
            the_post_thumbnail( array( 
                $gmw['search_results']['image']['width'], 
                $gmw['search_results']['image']['height'] 
            ) ); 
        ?>
    </div>
    <?php
}

/**
 * Get taxonomies in search results
 * 
 * @param  [type] $post [description]
 * @param  array  $gmw  [description]
 * @return [type]       [description]
 */
function gmw_search_results_taxonomies( $post, $gmw = array() ) {

    if ( ! isset( $gmw['search_results']['taxonomies'] ) || $gmw['search_results']['taxonomies'] == '' ) {
        return;
    }

    $args = array(
        'id' => $gmw['ID']
    );

    echo '<div class="taxonomies-list-wrapper">'.gmw_get_post_taxonomies_terms_list( $post, $args ).'</div>';
}

/**
 * Display excerpt in search results
 * 
 * @param  [type] $post [description]
 * @param  array  $gmw  [description]
 * @return [type]       [description]
 */
function gmw_search_results_post_excerpt( $post, $gmw = array() ) {

    if ( empty( $gmw['search_results']['excerpt']['enabled'] ) || empty( $post->post_content ) ) {
        return;
    }

    // verify usage value
    $usage = isset( $gmw['search_results']['excerpt']['usage'] ) ? $gmw['search_results']['excerpt']['usage'] : 'post_content';
        
    $args = array(
        'id'                => $gmw['ID'], 
        'content'           => $post->$usage,
        'words_count'       => isset( $gmw['search_results']['excerpt']['count'] ) ? $gmw['search_results']['excerpt']['count'] : '',
        'link'              => get_the_permalink( $post->ID ),
        'link_text'         => isset( $gmw['search_results']['excerpt']['link'] ) ? $gmw['search_results']['excerpt']['link'] : '',
        'enable_shortcodes'  => 1,
        'the_content_filter' => 1
    );

    echo '<div class="excerpt">'.GMW_Template_Functions_Helper::get_excerpt( $args ).'</div>';
}