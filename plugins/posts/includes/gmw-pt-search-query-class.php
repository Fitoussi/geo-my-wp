<?php 
/**
 * GMW_PT_Search_Query class
 * 
 */
class GMW_PT_Search_Query extends GMW {

    /**
     * __construct function.
     */
    function __construct( $form ) {

        do_action( 'gmw_pt_search_query_start', $form );   
  
        parent::__construct( $form );       
    }
  
    /**
     * Modify wp_query clauses to search by distance
     * @param $clauses
     * @return $clauses
     */
    public function query_clauses( $clauses ) {
        global $wpdb;
		
        $this->show_non_located_posts = apply_filters( 'show_posts_without_location', false, $this->form );
        
        // join the location table into the query
        $clauses['join']  .= " INNER JOIN {$wpdb->prefix}places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";
        $clauses['where'] .= " AND ( gmwlocations.lat != 0.000000 && gmwlocations.long != 0.000000 ) ";
        
        // add the radius calculation and add the locations fields into the results
        if ( !empty( $this->form['org_address'] ) ) {

        	$clauses['fields'] .= $wpdb->prepare( " , gmwlocations.lat, gmwlocations.long, gmwlocations.address, gmwlocations.formatted_address,
        			gmwlocations.phone, gmwlocations.fax, gmwlocations.email, gmwlocations.website, gmwlocations.map_icon,
        			ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", 
        			array( $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] ) );
        	
        	if ( !$this->form['advanced_query'] ) {
        		$px[0] = 'having';
        		$px[1] = '';
        	} else {
        		$px[0] = 'groupby';
        		$px[1] = $wpdb->posts.'.ID';
        	}
        	$clauses[$px[0]]    = $wpdb->prepare( " {$px[1]} HAVING distance <= %d OR distance IS NULL", $this->form['radius'] );
        	$clauses['orderby'] = ( !$this->form['advanced_query'] ) ? "ORDER BY distance" : 'distance';

        } else {
        	
        	if ( $this->show_non_located_posts ) {
        		// left join the location table into the query to display posts with no location as well
        		$clauses['join']  .= " LEFT JOIN {$wpdb->prefix}places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";
        		$clauses['where'] .= " ";      		
        	}
        	
        	$clauses['fields'] .= ", gmwlocations.lat, gmwlocations.long, gmwlocations.address, gmwlocations.formatted_address,
        	gmwlocations.phone, gmwlocations.fax, gmwlocations.email, gmwlocations.website, gmwlocations.map_icon";       
        }
        
        if ( $this->form['page_load_results_trigger'] ) {
        	
	        //if filtering by city
	        if ( !empty( $this->form['page_load_results']['city_filter'] ) ) {
	        	$clauses['where'] .= " AND gmwlocations.city = '{$this->form['page_load_results']['city_filter']}' ";
	        }
	        
	        //if filtering by state
	        if ( !empty( $this->form['page_load_results']['state_filter'] ) ) {
	        	$clauses['where'] .= " AND ( gmwlocations.state = '{$this->form['page_load_results']['state_filter']}' OR gmwlocations.state_long = '{$this->form['page_load_results']['state_filter']}' ) ";
	        }
	        
	        //if filtering by zipcode
	        if ( !empty( $this->form['page_load_results']['zipcode_filter'] ) ) {
	        	$clauses['where'] .= " AND gmwlocations.zipcode = '{$this->form['page_load_results']['zipcode_filter']}' ";
	        }
	        
	        //if filtering by country
	        if ( !empty( $this->form['page_load_results']['country_filter'] ) ) {
	        	$clauses['where'] .= " AND ( gmwlocations.country = '{$this->form['page_load_results']['country_filter']}' OR gmwlocations.country_long = '{$this->form['page_load_results']['country_filter']}' ) ";
	        }	        
        }
                
        return apply_filters( 'gmw_pt_location_query_clauses', $clauses, $this->form );
    }

	/**
	 * Include/exclude taxonomies on page load
	 * @param unknown_type $gmw
	 */
	function include_exclude_tax_custom_query( $clauses ) {
				
		//on form submission without taxonomies
		if ( !$this->form['submitted'] )
			return $clauses;

		if ( count( $this->form['search_form']['post_types'] ) != 1 ) 
			return $clauses;
		
		$postType = $this->form['search_form']['post_types'][0];
		
		if ( empty( $this->form['search_form']['taxonomies'][$postType] ) )
			return;

		$terms_array = array();
		$tax_array 	 = array();
		
		foreach ( $this->form['search_form']['taxonomies'][$postType] as $tax => $values ) {
				
			if ( !empty( $_GET['tax_'.$tax] ) )  {
				$get_tax = $_GET['tax_'.$tax];

				if ( $get_tax != 0 ) {
					$children    = get_term_children( $get_tax, $tax );
					$terms_array = array_merge( $terms_array, array( $get_tax ), $children );
					$tax_array[] = $tax;
				}
			}
		}

		if ( empty( $terms_array ) ) 
			return $clauses;
						
		$posts_id = get_objects_in_term( $terms_array, $tax_array );
		
		if ( count( $tax_array ) > 1 )
			$posts_id = array_unique( array_diff_assoc( $posts_id, array_unique( $posts_id ) ) );
		
		if ( empty( $posts_id ) ) {
			$clauses['where'] .= " AND 1 = 2 ";
			return $clauses;
		}
		
		global $wpdb;
		
		$clauses['where'] .= $wpdb->prepare( " AND ( {$wpdb->prefix}posts.ID IN (".str_repeat( "%d,", count( $posts_id ) - 1 ) . "%d ) )", $posts_id );

		return $clauses;
	}
	
    /**
     * Display Results
     * @access public
     */
    public function results() {
	     
        if ( $this->form['page_load_results_trigger'] ) {    	
        	$post_types = ( !empty( $this->form['page_load_results']['post_types'] ) ) ? $this->form['page_load_results']['post_types'] : array( 'post' );	
        } elseif ( !empty( $_GET['gmw_post'] ) ) {    	
        	$post_types = gmw_multiexplode( array( ' ', '+' ), $_GET['gmw_post'] );       	
        } else {     	
        	$post_types = ( !empty( $this->form['search_form']['post_types'] ) ) ? $this->form['search_form']['post_types'] : array( 'post' );
        }
             
        $this->form['advanced_query'] = apply_filters( 'gmw_pt_advanced_query', true, $this->form );
                
        if ( $this->form['advanced_query'] ) {
        
        	$tax_args  = false;
	        $meta_args = false;
	        
	        //query args
	        $this->form['query_args'] = array(
	        		'post_type'           => $post_types,
	        		'post_status'         => array( 'publish' ),
	        		'tax_query'           => apply_filters( 'gmw_pt_tax_query', $tax_args, $this->form ),
	        		'posts_per_page'      => $this->form['get_per_page'],
	        		'paged'               => $this->form['paged'],
	        		'meta_query'          => apply_filters( 'gmw_pt_meta_query', $meta_args, $this->form ),
	        		'ignore_sticky_posts' => 1
	        );
	
	        //Hooks before query 
	        $this->form = apply_filters( 'gmw_pt_form_before_posts_query', $this->form, $this->settings );
	
	        /* add filters to wp_query to do radius calculation and get locations detail into results */
	        add_filter( 'posts_clauses', array( $this, 'query_clauses' ) );
	
	        /* posts query */
	        $gmw_query = new WP_Query( $this->form['query_args'] );
	
	        /* remove filter */
	        remove_filter( 'posts_clauses', array( $this, 'query_clauses' ) );
        
	        $this->form['results'] 	     = $gmw_query->posts;
	        $this->form['results_count'] = count( $gmw_query->posts );
	        $this->form['total_results'] = $gmw_query->found_posts;
	        $this->form['max_pages']     = $gmw_query->max_num_pages;
	        
        	//do "simpler" query for improved performance
        } else {
        		
        	global $wpdb;
        
        	$clauses['select'] 	= "SELECT SQL_CALC_FOUND_ROWS";
        	$clauses['fields'] 	= "$wpdb->posts.*";
        	$clauses['from']	= "FROM $wpdb->posts";
        	$clauses['join']	= "";
        	$clauses['where']   = $wpdb->prepare( "WHERE $wpdb->posts.post_type IN (".str_repeat( "%s,", count( $post_types ) - 1 ) . "%s ) AND $wpdb->posts.post_status = 'publish'", $post_types );
        	$clauses['distinct']= "";
        	$clauses['groupby']	= "GROUP BY $wpdb->posts.ID";
        	$clauses['having']	= "";
        	$clauses['orderby'] = "ORDER BY $wpdb->posts.post_date DESC";
        	$clauses['limits']  = "";
        	
        	if ( !empty( $this->form['get_per_page'] ) ) {
        		$stating_page = ( $this->form['paged'] == 1 ) ? 0 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) );
        		$clauses['limit'] = "LIMIT {$stating_page},{$this->form['get_per_page']}";
        	}
        		
        	add_filter( 'gmw_pt_filter_custom_query_clauses', array( $this, 'query_clauses' 				   ) );
        	add_filter( 'gmw_pt_filter_custom_query_clauses', array( $this, 'include_exclude_tax_custom_query' ) );
        	
        	//Hooks before query
        	$this->form  = apply_filters( 'gmw_pt_form_before_custom_posts_query', $this->form 		  );
        	$clauses 	 = apply_filters( 'gmw_pt_filter_custom_query_clauses', $clauses, $this->form );

        	remove_filter( 'gmw_pt_filter_custom_query_clauses', array( $this, 'query_clauses' 				   ) );
        	remove_filter( 'gmw_pt_filter_custom_query_clauses', array( $this, 'include_exclude_tax_custom_query' ) );
        	
        	$this->form['results'] 		 = $wpdb->get_results( implode( ' ', $clauses ) );
        	$foundRows 					 = $wpdb->get_row( "SELECT FOUND_ROWS()", ARRAY_A );
        	$this->form['results_count'] = count( $this->form['results'] );
        	$this->form['total_results'] = $foundRows['FOUND_ROWS()'];
        	$this->form['max_pages']     = ( empty( $this->form['get_per_page'] ) || $this->form['get_per_page'] == 1 ) ? 1 : $this->form['total_results']/$this->form['get_per_page'];
        }
                
        /* hooks before posts loop */
        $this->form = apply_filters( 'gmw_pt_form_before_posts_loop',  $this->form, $this->settings );

        //enqueue stylesheet and get results template file
        $results_template = $this->search_results();
        
        //check if we got results and if so run the loop
        if ( !empty( $this->form['results'] ) ) {            
        	global $post;
                                  
            $this->form['post_count'] = ( $this->form['paged'] == 1 ) ? 1 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) ) + 1;
 
            if ( $this->form['advanced_query'] ) {
            	add_action( 'the_post', array( $this, 'the_post' ), 1 );
        	} else {
        		add_action( 'gmw_search_results_loop_item_start', array( $this, 'the_post' ), 1 );    		
        	}
        	
            do_action( 'gmw_pt_have_posts_start', $this->form, $this->settings );

            //call results template file
            if ( isset( $this->form['search_results']['display_posts'] ) && !$this->form['in_widget'] ) {			
            	$gmw = $this->form;
                include( $results_template );             
            /*
             * in case that we do not display posts we still run the loop on "empty" in order
             * to add element to the info windows of the map
             */
            } elseif ( $this->form['search_results']['display_map'] != 'na' ) {
            	if ( $this->form['advanced_query'] ) {
                	while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); endwhile;
            	} else {
            		foreach ( $this->form['results'] as $key => $post ) {
            			$this->form['results'][$key] = self::the_post( $post );
            		}
            	}
            }

            do_action( 'gmw_pt_have_posts_end', $this->form, $this->settings );

            if ( $this->form['advanced_query'] ) {
            	remove_action( 'the_post', array( $this, 'the_post' ), 1 );
            } else {
            	remove_action( 'gmw_search_results_loop_item_start', array( $this, 'the_post' ), 1 );
            }
            
            wp_reset_query();  
        } 
    }
    
    /**
     * GMW Function - append address to permalink
     * @param $url
     * @since 2.5
     */
    function append_address_to_permalink( $url ) {
    	
    	if ( !isset( $this->form['org_address'] ) || empty( $this->form['org_address'] ) )
    		return $url;
    	
    	return $url. '?address='.str_replace( ' ', '+', $this->form['org_address']);
    }
   
    /**
     * Modify each post within the loop
     */
    public function the_post( $post ) {

    	if ( !$this->form['advanced_query'] ) {
    		global $post;
    	}

    	add_filter('the_permalink', array( $this, 'append_address_to_permalink') );
    	
        // add permalink and thumbnail into each post in the loop
        // we are doing it here to be able to display it in the info window of the map
        $post->post_count     	   = $this->form['post_count'];
        $post->mapIcon        	   = apply_filters( 'gmw_pt_map_icon', 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='.$post->post_count.'|FF776B|000000', $post, $this->form );
        $post->info_window_content = self::info_window_content( $post );
		
        $this->form['post_count']++;
            
        do_action( 'gmw_pt_loop_the_post', $post, $this->form, $this->settings );
        
        if ( !$this->form['advanced_query'] ) {
        	return $post;
        }
    }
    
    /**
     * Create the content of the info window
     * @since 2.5
     * @param unknown_type $post
     */
    public function info_window_content( $post ) {

    	$address = ( !empty( $post->formatted_address ) ) ? $post->formatted_address : $post->address;
    	
    	$output  			     = array();
    	$output['start']		 = '<div class="gmw-pt-info-window-wrapper wppl-pt-info-window">';
    	$output['thumb'] 		 = '<div class="thumb wppl-info-window-thumb">'.get_the_post_thumbnail( $post->ID ).'</div>';
    	$output['content_start'] = '<div class="content wppl-info-window-info"><table>';
    	$output['title'] 		 = '<tr><td><div class="title wppl-info-window-permalink"><a href="'.get_permalink( $post->ID ).'">'.$post->post_title.'</a></div></td></tr>';
    	$output['address'] 		 = '<tr><td><span class="address">'.$this->form['labels']['info_window']['address'].'</span>'.$address.'</td></tr>';
    	
    	if ( isset( $post->distance ) ) {
    		$output['distance'] = '<tr><td><span class="distance">'.$this->form['labels']['info_window']['distance'].'</span>'.$post->distance.' '.$this->form['units_array']['name'].'</td></tr>';
    	}
    	
    	if ( !empty( $this->form['search_results']['additional_info'] ) ) {
    	
    		foreach ( $this->form['search_results']['additional_info'] as $field ) {
	    		if ( isset( $post->$field ) ) {
	    			$output[$this->form['labels']['info_window'][$field]] = '<tr><td><span class="'.$this->form['labels']['info_window'][$field].'">'.$this->form['labels']['info_window'][$field].'</span>'.$post->$field.'</td></tr>';
	    		}
    		}
    	}
    	
    	$output['content_end'] = '</table></div>';
    	$output['end'] 		   = '</div>';
    	
    	$output = apply_filters( 'gmw_pt_info_window_content', $output, $post, $this->form );
    	
    	return implode( ' ', $output );
    }
}
?>