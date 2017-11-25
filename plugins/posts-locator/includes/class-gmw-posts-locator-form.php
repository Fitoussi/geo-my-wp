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
     * [get_object_data description]
     * @param  [type] $location [description]
     * @return [type]           [description]
     */
    public function get_object_data( $post ) {
     
        return array(
            'url'   => get_permalink( $post->ID ),
            'title' => esc_attr( $post->post_title ),
            'image' => get_the_post_thumbnail( $post->ID )
        );
    }

    /**
     * Query taxonomies
     * 
     * @param  [type] $tax_args [description]
     * @param  [type] $gmw      [description]
     * @return [type]           [description]
     */
    public function query_taxonomies() {

        if ( apply_filters( 'gmw_pt_disable_tax_query', false, $this->form, $this ) ) {
            return false;
        }

        // abort if multiple post types were set.
        if ( empty( $this->form['search_form']['post_types'] ) || count( $this->form['search_form']['post_types'] ) != 1 ) {
            return false;   
        }

        $postType = $this->form['search_form']['post_types'][0];

        // abort if no taxonomies were set for the choosen post type.
        if ( empty( $this->form['search_form']['taxonomies'] ) || empty( $this->form['search_form']['taxonomies'][$postType] ) ) {
            return false;
        }

        $rr      = 0;
        $get_tax = false;
        $args    = array( 'relation' => 'AND' );
            
        foreach ( $this->form['search_form']['taxonomies'][$postType] as $tax => $values ) {

            if ( $values['style'] == 'dropdown' ) {
                 
                $get_tax = false;

                if ( isset( $_GET['tax_' . $tax] ) ) {
                    $get_tax = sanitize_text_field( $_GET['tax_' . $tax] );
                }

                if ( $get_tax != 0 ) {
                    $rr++;
                    $args[] = array(
                        'taxonomy' => $tax,
                        'field'    => 'id',
                        'terms'    => array( $get_tax )
                    );
                }
            } 
        }

        if ( $rr == 0 ) {
            $args = false;
        }

        return $args;
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
        } elseif ( ! empty( $this->form['form_values']['post'] ) && $_GET[$this->form['url_px'].'post'] != '1' ) {
            
            if ( ! is_array( $this->form['form_values']['post'] ) ) {
                
                $post_types = explode( ' ', $this->form['form_values']['post'] );
            } 

        } else {     	
        	$post_types = ! empty( $this->form['search_form']['post_types'] ) ? $this->form['search_form']['post_types'] : 'post';
        }
        
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
            'gmw_args'            => $gmw_query_args,
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
                set_transient( $query_args_hash, $this->query, GMW()->internal_cache_expiration );
            }
        }

        //Modify the form before the search query
        $this->form = apply_filters( 'gmw_pt_form_after_posts_query', $this->form, $this );

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