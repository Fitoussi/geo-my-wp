<?php

if ( !class_exists( 'GMW' ) )
	return;

/**
 * GMW_FL_Search_Query class
 *
 */
class GMW_FL_Search_Query extends GMW {

    /**
     * Places_locator database fields used in the query
     * @var array
     */
    public $db_fields = array( 
        '',
        'lat', 
        'long', 
        'street', 
        'city', 
        'state', 
        'zipcode', 
        'country', 
        'address', 
        'formatted_address',
        'map_icon' 
    );

    /**
     * Add filter to BP_user_query
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function gmwBpQuery( $gmwBpQuery ) {    	

    	global $wpdb;
    	
    	//break the select clause into 2: SELECT and FROM so we can modify it based on our needs
    	$select_clause = explode( 'FROM', $gmwBpQuery->uid_clauses['select']);
    	
        //modify the database columns if needed
        $this->db_fields = implode( ', gmwlocations.', apply_filters( 'gmw_fl_database_fields', $this->db_fields, $this->form ) );

    	//find the user_id column based on the query type
    	$uid_col = ( in_array( $gmwBpQuery->query_vars['type'], array( "alphabetical", 'distance' ) ) ) ? 'u.ID' : 'u.user_id';
    	
    	//default values
    	$fields  = '';
    	$from    = '';
    	$having  = '';
    	$where   = '';
    	$orderby = '';

    	//if address entered
    	if ( !empty($this->form['org_address'] ) ) { 
    		
    		//do radius calculation
    		$fields = $wpdb->prepare(" , ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance ",
    				array( $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] ) );
    		
    		//from clause joining locations table
    		$from = "INNER JOIN wppl_friends_locator gmwlocations ON {$uid_col} = gmwlocations.member_id";
    		
    		//HAVING clause to display members within the distance entered
    		$having = $wpdb->prepare( 'HAVING distance <= %d OR distance IS NULL ', $this->form['radius'] );
    	
    	//if no address entered 
    	} else {
    		
    		//set the type of join. Depends if showing members with no location or not
    		$join = ( $this->show_non_located_users ) ? 'LEFT' : 'INNER';
    		
    		//join the locations table to the query
    		$from = "{$join} JOIN wppl_friends_locator gmwlocations ON {$uid_col} = gmwlocations.member_id";
    	}
    	
    	//query the xprofile fields. This is done in a saperate function
    	if ( apply_filters( 'gmw_fl_do_xprofile_query', true ) ) {
    		
    		$xprofile_users = gmw_fl_query_xprofile_fields( $this->form ,$_GET );
    	
    		//if fields entered but no users returned abort the query
    		if ( $xprofile_users['status'] == 'no_ids_found' ) {

    			$where = " AND 1 = 0 ";

    			//if users returned. add them to query
    		} elseif ( $xprofile_users['status'] == 'ids_found' ) {

    			$where = $wpdb->prepare(" AND {$uid_col} IN (" . str_repeat( "%d,", count( $xprofile_users['ids'] ) - 1 ) . "%d )", $xprofile_users['ids'] );
    		}
    	}
    	
    	//query based on address fields filters. For page load resutls
    	if ( $this->form['page_load_results_trigger'] ) {
    	
    		//if filtering by city
    		if ( !empty( $this->form['page_load_results']['city_filter'] ) ) {
    			$where .= " AND gmwlocations.city = '{$this->form['page_load_results']['city_filter']}' ";
    		}
    	
    		//if filtering by state
    		if ( !empty( $this->form['page_load_results']['state_filter'] ) ) {
    			$where .= " AND ( gmwlocations.state = '{$this->form['page_load_results']['state_filter']}' OR gmwlocations.state_long = '{$this->form['page_load_results']['state_filter']}' ) ";
    		}
    	
    		//if filtering by zipcode
    		if ( !empty( $this->form['page_load_results']['zipcode_filter'] ) ) {
    			$where .= " AND gmwlocations.zipcode = '{$this->form['page_load_results']['zipcode_filter']}' ";
    		}
    	
    		//if filtering by country
    		if ( !empty( $this->form['page_load_results']['country_filter'] ) ) {
    			$where .= " AND ( gmwlocations.country = '{$this->form['page_load_results']['country_filter']}' OR gmwlocations.country_long = '{$this->form['page_load_results']['country_filter']}' ) ";
    		}
    	}
    	
    	//apply our filters to BP_user_qeury clauses
    	$gmwBpQuery->query_vars['count_total'] = 'sql_calc_found_rows';
    	$gmwBpQuery->uid_clauses['select'] 	   = "{$select_clause[0]} {$fields} FROM {$select_clause[1]} {$from} ";   	 
    	$gmwBpQuery->uid_clauses['where'] 	  .= $where;
    	$gmwBpQuery->uid_clauses['where'] 	  .= $having;
    	
    	//if order by distance
    	if ( !empty( $this->form['org_address'] ) && $gmwBpQuery->query_vars['type'] == 'distance' ) {
    		$gmwBpQuery->uid_clauses['orderby'] = 'ORDER BY distance';
    	}
    	
    	//modify the clauses
    	$gmwBpQuery = apply_filters( 'gmw_fl_after_query_clauses', $gmwBpQuery, $this->form );
    	$gmwBpQuery = apply_filters( "gmw_fl_after_query_clauses_{$this->form['ID']}", $gmwBpQuery, $this->form );
        
        return $gmwBpQuery;
    }

    /**
     * Add filter to WP_user_query
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function gmwWpQuery( $gmwWpQuery ) {
    	
    	global $wpdb;
    	
    	$fields = '';
    	$from   = '';
    	    	 
    	if ( !empty($this->form['org_address'] ) ) {
    	
    		$fields = $wpdb->prepare("{$this->db_fields}, 
                    ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", 
        			$this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] );
    	
    		$from = " INNER JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id";
    	
    	} else {
    		$fields = $this->db_fields;
    		
    		$join = ( $this->show_non_located_users ) ? 'LEFT' : 'INNER';
    		$from = " {$join} JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id";
    	}
    	  	
        $gmwWpQuery->query_fields  .= $fields;
        $gmwWpQuery->query_from    .= $from;

        return $gmwWpQuery;
    }

    /**
     * Display results
     */
    public function results() {
    
    	//Show/hide members with no location in the results. Default set to false
    	$this->show_non_located_users = apply_filters( 'show_users_without_location', false, $this->form );
    	 
    	//prevent BuddyPress from using its own "paged" value for the current page
    	if ( !empty( $_REQUEST['upage'] ) )
    		unset( $_REQUEST['upage'] );
        
        /*
        $xp_ids = array();

        //query the xprofile fields. This is done in a saperate function
        if ( apply_filters( 'gmw_fl_do_xprofile_query', true ) ) {
            
            $xprofile_users = gmw_fl_query_xprofile_fields( $this->form ,$_GET );
        
            //if fields entered but no users returned abort the query
            if ( $xprofile_users['status'] == 'no_ids_found' ) {

                $xp_ids = 0;

            //if users returned. add them to query
            } elseif ( $xprofile_users['status'] == 'ids_found' ) {

                $xp_ids = $xprofile_users['ids'];
            }
        }
        */
       
    	//query args
    	$this->form['query_args'] = apply_filters( 'gmw_fl_search_query_args', array(
    			'type'     	=> 'distance',
    			'per_page' 	=> $this->form['get_per_page'],
    			'page'		=> $this->form['paged'],
                //'include'   => $xp_ids
    	), $this->form );
    
    	//modify the form values before query
    	$this->form = apply_filters( 'gmw_fl_form_before_members_query', $this->form, $this->settings );
    	$this->form = apply_filters( "gmw_fl_form_before_members_query_{$this->form['ID']}", $this->form, $this->settings );
    
    	//modify BP users query
    	add_action( 'bp_pre_user_query', array( $this, 'gmwBpQuery' ) );
    
    	//modify WP User query
    	add_action( 'pre_user_query', 	 array( $this, 'gmwWpQuery' ) );
    
    	//enqueue stylesheet and get results template file
    	$results_template = $this->search_results();
    
    	if ( bp_has_members( $this->form['query_args'] ) ) {
    		 
    		global $members_template;
    		$members_template = apply_filters( 'gmw_fl_members_before_members_loop', $members_template, $this->form, $this->settings );
    		 
    		//setup member count
    		$this->form['member_count']  = ( $this->form['paged'] == 1 ) ? 1 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) +1 ) ;
    		$this->form['results']       = $members_template->members;
    		$this->form['total_results'] = $members_template->total_member_count;
    		$this->form['max_pages']	 = $this->form['total_results']/$this->form['get_per_page'];
    		 
    		echo '<div id="buddypress">';
    		 
    		//load results template file to display list of members
    		if ( isset( $this->form['search_results']['display_members'] ) && !$this->form['in_widget'] ) {
    			 
    			add_action( 'gmw_search_results_loop_item_start', array( $this, 'modify_member'   ), 10    );
    
    			$gmw = $this->form;
    			include( $results_template );
    
    			/*
    			 * if we do not display list of members we still need to have a loop
    			* and add some information to each members in order to be able to
    			* display it on the map
    			*/
    		} elseif ( $this->form['search_results']['display_map'] != 'na' ) {
    			while ( bp_members()) : bp_the_member();
    			self::modify_member();
    			endwhile;
    		}
    
    		echo '</div>';
    	}
    	 
    	remove_action( 'bp_pre_user_query', array( $this, 'gmwBpQuery' ) );
    	remove_action( 'pre_user_query', 	array( $this, 'gmwWpQuery' ) );
    }
    
    /**
     * modify members_template in the loop
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function modify_member() {
        
    	global $members_template;

        $members_template->member->member_count 		= $this->form['member_count'];
        $members_template->member->mapIcon      		= apply_filters( 'gmw_fl_map_icon', "https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld={$members_template->member->member_count}|FF776B|000000", $members_template->member, $this->form );
		$members_template->member->info_window_content 	= self::info_window_content( $members_template->member );
        
		$this->form['member_count'] ++;

		$members_template = apply_filters( 'gmw_fl_modify_member', $members_template, $this->form, $this->settings );
        $members_template = apply_filters( "gmw_fl_modify_member_{$this->form['ID']}", $members_template, $this->form, $this->settings );
    }
	
    /**
     * create the content of the info window
     * @since 2.5
     * @param unknown_type $member
     * @return string
     */
    public function info_window_content( $member ) {
     	
    	if ( !empty( $member->formatted_address ) ) {
    		$address = $member->formatted_address; 
    	} elseif ( !empty( $member->address ) ) {
    		$address = $member->address;
    	} else {
    		$address = $this->form['labels']['search_results']['not_avaliable'];
    	}
    	 
    	$output  				 = array();
    	$output['start'] 		 = '<div class="gmw-fl-infow-window-wrapper wppl-fl-info-window">';
    	$output['thumb'] 		 = '<div class="thumb wppl-info-window-thumb">'.bp_get_member_avatar($args= 'type=full').'</div>';
    	$output['content_start'] = '<div class="content wppl-info-window-info"><table>';
    	$output['name']			 = '<tr><td><span class="wppl-info-window-permalink"><a href="'.bp_get_member_permalink().'">'.$member->display_name.'</a></span></td></tr>';
    	$output['address']		 = '<tr><td><span>'.$this->form['labels']['info_window']['address'].'</span>'.$address.'</td></tr>';
    	
    	if ( isset( $member->distance ) ) {
    		$output['distance'] = '<tr><td><span>'.$this->form['labels']['info_window']['distance'].'</span>'.$member->distance.' '.$this->form['units_array']['name'].'</td></tr>';
    	}
    	 
    	$output['content_end']  = '</table></div>';
    	$output['end'] 			= '</div>';
    	
    	$output = apply_filters( 'gmw_fl_info_window_content', $output, $member, $this->form );
    	$output = apply_filters( "gmw_fl_info_window_content_{$this->form['ID']}", $output, $member, $this->form );
    	
    	return implode( ' ', $output );
    }
}