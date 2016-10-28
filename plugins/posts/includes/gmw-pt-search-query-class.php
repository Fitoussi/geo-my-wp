<?php 
if ( !class_exists( 'GMW' ) )
	return;

/**
 * GMW_PT_Search_Query class
 * 
 */
class GMW_PT_Search_Query extends GMW {
    
    /**
     * Places_locator database fields used in the query
     * @var array
     */
    public $db_fields = array( 
        '',
        'lat', 
        'long', 
        'feature', 
        'street', 
        'city', 
        'state', 
        'zipcode', 
        'country', 
        'address', 
        'formatted_address',
        'phone', 
        'fax', 
        'email', 
        'website', 
        'map_icon' 
    );

    /**
     * Modify wp_query clauses to search by distance
     * @param $clauses
     * @return $clauses
     */
    public function query_clauses( $clauses ) {

    	global $wpdb;
		        
        $this->enable_non_located_posts = apply_filters( 'enable_non_located_posts', false, $this->form );
        
        $this->db_fields = implode( ', gmwlocations.', apply_filters( 'gmw_pt_database_fields', $this->db_fields, $this->form ) );
 
        // add the radius calculation and add the locations fields into the results
        if ( !empty( $this->form['org_address'] ) ) {

        	$clauses['join']   .= " INNER JOIN {$wpdb->prefix}places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";
        	$clauses['fields'] .= $wpdb->prepare( "{$this->db_fields},
        			ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", 
        			array( $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] ) );
        	
        	$clauses['where'] .= " AND ( gmwlocations.lat != 0.000000 && gmwlocations.long != 0.000000 ) ";
        	$clauses['having'] = $wpdb->prepare( "HAVING distance <= %s OR distance IS NULL", $this->form['radius'] );
        	
        	if ( !$this->form['advanced_query'] || ( $this->form['advanced_query'] && $this->form['query_args']['orderby'] == 'distance' ) ) {
        		$clauses['orderby'] = 'distance';
        	}

        } else {
        	
        	//if showing posts without location
        	if ( $this->enable_non_located_posts ) {
        		
        		// left join the location table into the query to display posts with no location as well
        		$clauses['join']  .= " LEFT JOIN {$wpdb->prefix}places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";     		
        		$clauses['where'] .= " ";
        	} else {
        		$clauses['join']  .= " INNER JOIN {$wpdb->prefix}places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";
        		$clauses['where'] .= " AND ( gmwlocations.lat != 0.000000 && gmwlocations.long != 0.000000 ) ";
        	}
        	
        	$clauses['fields'] .= $this->db_fields;       
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
             
        $clauses = apply_filters( 'gmw_pt_location_query_clauses', $clauses, $this->form );
        $clauses = apply_filters( "gmw_pt_location_query_clauses_{$this->form['ID']}", $clauses, $this->form );

        if ( !empty( $clauses['having'] ) ) {

            if ( empty( $clauses['groupby'] ) ) {
                $clauses['groupby'] = $wpdb->prefix.'posts.ID';
            }
            $clauses['groupby'] .= ' '.$clauses['having'];
            unset( $clauses['having'] );
        } 

        return $clauses; 
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
				$get_tax = sanitize_text_field( $_GET['tax_'.$tax] );

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
	     
    	//get the post types
        if ( $this->form['page_load_results_trigger'] ) {    	
        	$post_types = ( !empty( $this->form['page_load_results']['post_types'] ) ) ? $this->form['page_load_results']['post_types'] : array( 'post' );	
        } elseif ( !empty( $_GET[$this->form['url_px'].'post'] ) ) {    	
        	$post_types = gmw_multiexplode( array( ' ', '+' ), $_GET[$this->form['url_px'].'post'] );       	
        } else {     	
        	$post_types = ( !empty( $this->form['search_form']['post_types'] ) ) ? $this->form['search_form']['post_types'] : array( 'post' );
        }
             
        $this->form['advanced_query'] = true;
        $this->form['advanced_query'] = apply_filters( 'gmw_pt_advanced_query', $this->form['advanced_query'], $this->form );
        $this->form['advanced_query'] = apply_filters( "gmw_pt_advanced_query_{$this->form['ID']}", $this->form['advanced_query'], $this->form );
                
        if ( $this->form['advanced_query'] ) {
            
            /*
            
            //some results caching for the future
            
            $url_string = $_GET;
            $url_string['paged'] = $this->form['paged'];

            $query_args_hash = 'gmw-' . md5( json_encode( $url_string ) . GEO_my_WP_Cache_Helper::get_transient_version( 'gmw-pt-search-results' ) );
                    
            echo $query_args_hash;
            */
            //if ( false === ( $gmw_query = get_transient( $query_args_hash ) ) ) {
            
            if ( 1 == 1 ) {

                //echo 'do_query';
                
            	$tax_args  = false;
    	        $meta_args = false;
    	        
    	        //query args
    	        $this->form['query_args'] = apply_filters( 'gmw_pt_search_query_args', array(
    	        		'post_type'           => $post_types,
    	        		'post_status'         => array( 'publish' ),
    	        		'tax_query'           => apply_filters( 'gmw_pt_tax_query', $tax_args, $this->form ),
    	        		'posts_per_page'      => $this->form['get_per_page'],
    	        		'paged'               => $this->form['paged'],
    	        		'meta_query'          => apply_filters( 'gmw_pt_meta_query', $meta_args, $this->form ),
    	        		'ignore_sticky_posts' => 1,
    	        		'orderby'			  => 'distance'
    	        ), $this->form );
    	       
    	        //Modify the form before the search query
    	        $this->form = apply_filters( 'gmw_pt_form_before_posts_query', $this->form, $this->settings );
    	        $this->form = apply_filters( "gmw_pt_form_before_posts_query_{$this->form['ID']}", $this->form, $this->settings );
    	
    	        //add filters to wp_query to do radius calculation and get locations detail into results
    	        add_filter( 'posts_clauses', array( $this, 'query_clauses' ) );
    	   
    	        /* posts query */
    	        $gmw_query = new WP_Query( $this->form['query_args'] );

                //set new query in transient - for future caching        
                //set_transient( $query_args_hash, $gmw_query, DAY_IN_SECONDS * 30 );
    	
    	        /* remove filter */
    	        remove_filter( 'posts_clauses', array( $this, 'query_clauses' ) );
            
            }

	        $this->form['results'] 	     = $gmw_query->posts;
	        $this->form['results_count'] = count( $gmw_query->posts );
	        $this->form['total_results'] = $gmw_query->found_posts;
	        $this->form['max_pages']     = $gmw_query->max_num_pages;
	        
        //do "simpler" query for improved performance
        } else {
        		
        	global $wpdb;
        
        	$clauses['select'] 	 = "SELECT SQL_CALC_FOUND_ROWS";
            $clauses['distinct'] = "";
        	$clauses['fields'] 	 = "$wpdb->posts.*";
        	$clauses['from']	 = $wpdb->posts;
        	$clauses['join']	 = "";
        	$clauses['where']    = $wpdb->prepare( "AND $wpdb->posts.post_type IN (".str_repeat( "%s,", count( $post_types ) - 1 ) . "%s ) AND $wpdb->posts.post_status = 'publish'", $post_types );
        	$clauses['groupby']	 = "";
            $clauses['having']   = "";
        	$clauses['orderby']  = "$wpdb->posts.post_date DESC";
        	$clauses['limits']   = "";
        	
        	if ( !empty( $this->form['get_per_page'] ) ) {
        		
        		$stating_page     = ( $this->form['paged'] == 1 ) ? 0 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) );
        		$clauses['limits'] = "LIMIT {$stating_page},{$this->form['get_per_page']}";
        	}
        		
        	add_filter( 'gmw_pt_filter_custom_query_clauses', array( $this, 'query_clauses' 				   ) );
        	add_filter( 'gmw_pt_filter_custom_query_clauses', array( $this, 'include_exclude_tax_custom_query' ) );
        	
        	//Hooks before query
        	$this->form  = apply_filters( 'gmw_pt_form_before_custom_posts_query', $this->form 		  );
        	$clauses 	 = apply_filters( 'gmw_pt_filter_custom_query_clauses', $clauses, $this->form );

        	remove_filter( 'gmw_pt_filter_custom_query_clauses', array( $this, 'query_clauses' 				   ) );
        	remove_filter( 'gmw_pt_filter_custom_query_clauses', array( $this, 'include_exclude_tax_custom_query' ) );
        	
            if ( !empty( $clauses['groupby'] ) ) {
                $clauses['groupby'] = 'GROUP BY ' . $clauses['groupby'];
            }
            if ( !empty( $clauses['orderby'] ) ) {
                $clauses['orderby'] = 'ORDER BY ' . $clauses['orderby'];
            }
  
            $request = "{$clauses['select']} DISTINCT {$clauses['fields']} FROM {$clauses['from']} {$clauses['join']} WHERE 1=1 {$clauses['where']} {$clauses['groupby']} {$clauses['orderby']} {$clauses['limits']}";

        	$this->form['results']       = $wpdb->get_results( $request );
        	$foundRows 					 = $wpdb->get_row( "SELECT FOUND_ROWS()", ARRAY_A );
        	$this->form['results_count'] = count( $this->form['results'] );
        	$this->form['total_results'] = $foundRows['FOUND_ROWS()'];
        	$this->form['max_pages']     = ( empty( $this->form['get_per_page'] ) || $this->form['get_per_page'] == 1 ) ? 1 : $this->form['total_results']/$this->form['get_per_page'];
        }
                
        //Modify the form values before the loop
        $this->form = apply_filters( 'gmw_pt_form_before_posts_loop', $this->form, $this->settings );
        $this->form = apply_filters( "gmw_pt_form_before_posts_loop_{$this->form['ID']}", $this->form, $this->settings );

        //enqueue stylesheet and get results template file
        $results_template = $this->results_template();
        
        //check if we got results and if so run the loop
        if ( !empty( $this->form['results'] ) ) {  
        	                                           
            $this->form['post_count'] = ( $this->form['paged'] == 1 ) ? 1 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) ) + 1;
 
            if ( $this->form['advanced_query'] ) {
            	add_action( 'the_post', array( $this, 'the_post' ), 1 );
        	} else {
        		add_action( 'gmw_search_results_loop_item_start', array( $this, 'the_post' ), 1 );    		
        	}
        	
            do_action( 'gmw_pt_have_posts_start', $this->form, $this->settings );
            do_action( "gmw_pt_have_posts_start_{$this->form['ID']}", $this->form, $this->settings );

            //call results template file
            if ( isset( $this->form['search_results']['display_posts'] ) ) {	
            	
                global $post;

            	$gmw = $this->form;
                include( $results_template );             
            /*
             * in case that we do not display posts we still run the loop on "empty" in order
             * to add element to the info windows of the map
             */
            } elseif ( $this->form['search_results']['display_map'] != 'na' ) {
            	
            	if ( $this->form['advanced_query'] ) {
            		
                    global $post;

                	while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); endwhile;
            	} else {
            		
            		foreach ( $this->form['results'] as $key => $post ) {
            			
            			$this->form['results'][$key] = self::the_post( $post );
            		}
            	}
            }

            do_action( 'gmw_pt_have_posts_end', $this->form, $this->settings );
            do_action( "gmw_pt_have_posts_end_{$this->form['ID']}", $this->form, $this->settings );

            if ( $this->form['advanced_query'] ) {
            	remove_action( 'the_post', array( $this, 'the_post' ), 1 );
            } else {
            	remove_action( 'gmw_search_results_loop_item_start', array( $this, 'the_post' ), 1 );
            }
            
            wp_reset_query();  
        } 
    }
    
    /**
     * GMW Function - append location details to permalink
     * @param $url
     * @since 2.5
     */
    public function append_address_to_permalink( $url ) {
    	
    	if ( empty( $this->form['org_address'] ) )
    		return $url;
    	
    	global $post;
    	
    	$url_args = array(
    			'address' 	=> str_replace( ' ', '+', $this->form['org_address'] ),
    			'lat'	  	=> $this->form['your_lat'],
    			'lng'	  	=> $this->form['your_lng'],
    			'distance'	=> $post->distance,
    			'units'	    => $this->form['units_array']['name']
    	);

    	return apply_filters( 'gmw_pt_post_permalink', $url. '?'.http_build_query( $url_args ), $url, $url_args );
    }
   
    /**
     * Modify each post within the loop
     */
    public function the_post( $post ) {

    	if ( !$this->form['advanced_query'] ) {
    		global $post;
    	}

    	add_filter( 'the_permalink', array( $this, 'append_address_to_permalink') );
    	
        // add permalink and thumbnail into each post in the loop
        // we are doing it here to be able to display it in the info window of the map
        $post->post_count     	   = $this->form['post_count'];
        $post->mapIcon        	   = apply_filters( 'gmw_pt_map_icon', 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='.$post->post_count.'|FF776B|000000', $post, $this->form );
        $post->info_window_content = self::info_window_content( $post );
		
        $this->form['post_count']++;
            
        do_action( 'gmw_pt_loop_the_post', $post, $this->form, $this->settings );
        
        $post = apply_filters( "gmw_pt_loop_modify_the_post", $post, $this->form, $this->settings );
        $post = apply_filters( "gmw_pt_loop_modify_the_post_{$this->form['ID']}", $post, $this->form, $this->settings );
                
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

    	$address   = ( !empty( $post->formatted_address ) ) ? $post->formatted_address : $post->address;
    	$permalink = get_permalink( $post->ID );
    	$thumb	   = get_the_post_thumbnail( $post->ID );
    	
    	$output  			     = array();
    	$output['start']		 = "<div class=\"gmw-pt-info-window-wrapper wppl-pt-info-window\">";
    	$output['thumb'] 		 = "<div class=\"thumb wppl-info-window-thumb\">{$thumb}</div>";
    	$output['content_start'] = "<div class=\"content wppl-info-window-info\"><table>";
    	$output['title'] 		 = "<tr><td><div class=\"title wppl-info-window-permalink\"><a href=\"{$permalink}\">{$post->post_title}</a></div></td></tr>";
    	$output['address'] 		 = "<tr><td><span class=\"address\">{$this->form['labels']['info_window']['address']}</span>{$address}</td></tr>";
    	
    	if ( isset( $post->distance ) ) {
    		$output['distance'] = "<tr><td><span class=\"distance\">{$this->form['labels']['info_window']['distance']}</span>{$post->distance} {$this->form['units_array']['name']}</td></tr>";
    	}
    	
    	if ( !empty( $this->form['search_results']['additional_info'] ) ) {
    	
    		foreach ( $this->form['search_results']['additional_info'] as $field ) {
	    		if ( isset( $post->$field ) ) {
	    			$output[$this->form['labels']['info_window'][$field]] = "<tr><td><span class=\"{$this->form['labels']['info_window'][$field]}\">{$this->form['labels']['info_window'][$field]}</span>{$post->$field}</td></tr>";
	    		}
    		}
    	}
    	
    	$output['content_end'] = "</table></div>";
    	$output['end'] 		   = "</div>";

    	$output = apply_filters( 'gmw_pt_info_window_content', $output, $post, $this->form );
    	$output = apply_filters( "gmw_pt_info_window_content_{$this->form['ID']}", $output, $post, $this->form );
    	
    	return implode( ' ', $output );
    }
}
?>