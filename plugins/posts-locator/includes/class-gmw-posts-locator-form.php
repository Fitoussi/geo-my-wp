<?php 
if ( ! class_exists( 'GMW_Form_Init' ) ) {
	return;
}

/**
 * GMW_PT_Search_Query class
 * 
 */
class GMW_Posts_Locator_Form extends GMW_Form {
    
    /**
     * Permalink hook
     * @var string
     */
    public $object_permalink_hook = 'the_permalink';

    /**
     * [get_info_window_args description]
     * @param  [type] $location [description]
     * @return [type]           [description]
     */
    public function get_info_window_args( $post ) {
        return array(
            'prefix' => $this->prefix,
            'type'   => ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard',
            'image'  => get_the_post_thumbnail( $post->ID ),
            'url'    => get_permalink( $post->ID ),
            'title'  => $post->post_title
        );
    }

    /**
     * Query taxonomies on form submission
     * 
     * @param  [type] $tax_args [description]
     * @param  [type] $gmw      [description]
     * @return [type]           [description]
     */
    public function query_taxonomies() {

        // query taxonomies if submitted in form
        if ( empty( $this->form['form_values']['tax'] ) ) {
            return false;
        }

        $tax_value = false;
        $output    = array( 'relation' => 'AND' );
            
        foreach ( $this->form['form_values']['tax'] as $taxonomy => $values ) {

            if ( array_filter( $values ) )  { 
                $output[] = array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'id',
                    'terms'    => $values,
                    'operator' => 'IN'
                );
            }

            // extend the taxonomy query
            $output = apply_filters( 'gmw_pt_query_taxonomy', $output, $taxonomy, $values, $this->form );
        }

        // verify that there is at least one query to performe
        if ( empty( $output[0] ) ) {
            $output = false;
        }

        return $output;
    }

    /**
     * Query results
     * 
     * @return [type] [description]
     */
    public function search_query() {

        $locations_objects_id = $this->pre_get_locations_data();

        // do locations query. If nothing was found abort and show no result
        if ( empty( $locations_objects_id ) ) {
            return false;
        }

    	// get the post types from page load settings
        if ( $this->form['page_load_action'] ) {  

            $post_types = ! empty( $this->form['page_load_results']['post_types'] ) ? $this->form['page_load_results']['post_types'] : 'post';	

        // when set to 1 means that we need to show all post types
        } elseif ( ! empty( $this->form['form_values']['post'] ) && array_filter( $this->form['form_values']['post'] ) ) {
            
            $post_types = $this->form['form_values']['post'];
        
        } else {
            
            $post_types = ! empty( $this->form['search_form']['post_types'] ) ? $this->form['search_form']['post_types'] : 'post';
        }

        /*} elseif ( ! empty( $this->form['form_values']['post'] ) && $_GET[$this->form['url_px'].'post'] != '1' ) {
            
            if ( ! is_array( $this->form['form_values']['post'] ) ) {
                
                $post_types = explode( ' ', $this->form['form_values']['post'] );
            } 

        } else {     	
        	$post_types = ! empty( $this->form['search_form']['post_types'] ) ? $this->form['search_form']['post_types'] : 'post';
        } */
        
        // get query args for cache
        if ( $this->form['page_load_action'] ) {

            $gmw_query_args = $this->form['page_load_results'];

        } elseif ( $this->form['submitted'] ) {  

            $gmw_query_args = $this->form['form_values'];
        }

        // tax query can be disable if a custom query is needed.
        if ( apply_filters( 'gmw_enable_taxonomy_search_query', true, $this->form, $this ) ) {
            
            $tax_args = $this->query_taxonomies(); 
        
        } else {
        
            $tax_args = array();
        }
        
        $meta_args = false;
        
        if ( ! empty( $this->form['org_address'] ) ) {
            
            $post__in = $locations_objects_id;
            $order_by = 'post__in';
   
        } else {

            $order_by = '';
            $post__in = apply_filters( "gmw_show_posts_without_location", false, $this->form, $this ) ? '' : $locations_objects_id;
        }

        //query args
        $this->form['query_args'] = apply_filters( 'gmw_pt_search_query_args', array(
            'post_type'           => $post_types,
            'post_status'         => array( 'publish' ),
            'tax_query'           => apply_filters( 'gmw_pt_tax_query', $tax_args, $this->form ),
            'posts_per_page'      => $this->form['get_per_page'],
            'paged'               => $this->form['paged'],
            'meta_query'          => apply_filters( 'gmw_pt_meta_query', $meta_args, $this->form ),
            'ignore_sticky_posts' => 1,
            'post__in'            => $post__in,
            'orderby'             => $order_by,
            'gmw_args'            => $gmw_query_args
        ), $this->form, $this );

        //Modify the form before the search query
        $this->form = apply_filters( 'gmw_pt_form_before_posts_query', $this->form, $this );

        $internal_cache = GMW()->internal_cache;

        if ( $internal_cache ) {

            // cache key
            $hash = md5( json_encode( $this->form['query_args'] ) );
            $query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_post_query' );
        }

        // look for query in cache
        if ( ! $internal_cache || false === ( $this->query = get_transient( $query_args_hash ) ) ) {
            
            //print_r( 'WP posts query done' );
        
	        // posts query
	        $this->query = new WP_Query( $this->form['query_args'] );

            // set new query in transient     
            if ( $internal_cache ) {
                    
                /**
                 * This is a temporary solution for an issue with caching SQL requests
                 * For some reason when LIKE is being used in SQL WordPress replace the % of the LIKE
                 * with long random numbers. This SQL is still being saved in the transient. Hoever,
                 * it is not being pulled back properly when GEO my WP trying to use it. 
                 * It shows an error "unserialize(): Error at offset " and the value returns blank.
                 * As a temporary work around, we remove the [request] value, which contains the long numbers, from the WP_Query and save it in the transien without it. 
                 * @var [type]
                 */
                $request = $this->query->request;
                unset( $this->query->request );
                set_transient( $query_args_hash, $this->query, GMW()->internal_cache_expiration );
                $this->query->request = $request;
            }
        }   

        //Modify the form before the search query
        $this->form = apply_filters( 'gmw_pt_form_after_posts_query', $this->form, $this );

        // make sure posts exist
        if ( empty( $this->query->posts ) ) {
            return false;
        }

        $this->form['results'] 	     = $this->query->posts;
        $this->form['results_count'] = count( $this->query->posts );
        $this->form['total_results'] = $this->query->found_posts;
        $this->form['max_pages']     = $this->query->max_num_pages;
	               
        $temp_array = array();

        foreach ( $this->form['results'] as $post ) {
            $temp_array[] = parent::the_location( $post->ID, $post );
        }

        $this->form['results'] = $temp_array;

        // Modify the form values before the loop
        //$this->form = apply_filters( 'gmw_pt_form_before_posts_loop', $this->form );

        return $this->form['results'];
    }
}
?>