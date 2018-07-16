<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate tax_query args for taxonomy terms query.
 *
 * To be used with posts locator WP_Query.
 *
 * @since 3.1
 *
 * @param  array  $tax_args [description]
 * @param  [type] $gmw      [description]
 * @return [type]           [description]
 */
function gmw_pt_get_tax_query_args( $tax_args = array(), $gmw ) {

	$tax_value = false;
	$output    = array( 'relation' => 'AND' );

	foreach ( $tax_args as $taxonomy => $values ) {

		if ( array_filter( $values ) ) {
			$output[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $values,
				'operator' => 'IN',
			);
		}

		// extend the taxonomy query
		$output = apply_filters( 'gmw_' . $gmw['prefix'] . '_query_taxonomy', $output, $taxonomy, $values, $gmw );
	}

	// verify that there is at least one query to performe
	if ( empty( $output[0] ) ) {
		$output = array();
	}

	return $output;
}

/**
 * Display featured image in search results
 *
 * @param  [type] $post [description]
 * @param  array  $gmw  [description]
 * @return [type]       [description]
 */
function gmw_search_results_featured_image( $post, $gmw = array() ) {

    if ( ! $gmw['search_results']['image']['enabled'] ) { 
        return;
    }
    if ( has_post_thumbnail() ) {
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
	} else {
		?>
		<div class="post-thumbnail no-image">
			<img 
				src="<?php echo GMW_IMAGES . '/no-image.jpg'; ?>" 
				width="<?php echo esc_attr( $gmw['search_results']['image']['width'] ); ?>"
				height="<?php echo esc_attr( $gmw['search_results']['image']['height'] ); ?>"
			/>
		</div>
		<?php
	}
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

    if ( empty( $gmw['search_results']['excerpt']['enabled'] ) ) {
        return;
    }

    // verify usage value
    $usage = isset( $gmw['search_results']['excerpt']['usage'] ) ? $gmw['search_results']['excerpt']['usage'] : 'post_content';
      
    if ( empty( $post->$usage ) )  {
    	return;
	}

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
