<?php
/**
 * GMW_FL_Search_Query class
 *
 */
class GMW_FL_Search_Query extends GMW {

    /**
     * __construct function.
     */
    function __construct( $form ) {

        do_action(  'gmw_fl_search_query_start', $form );
       
        add_action( 'gmw_search_results_loop_item_start', array( $this, 'modify_member'   ), 10    );

        parent::__construct( $form );     
    }
    
    /**
     * members query clauses
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function query_clauses() {

        global $wpdb;

        $clauses['bp_user_query'] 	  = false;
        $clauses['wp_user_query'] 	  = false;
		$this->advanced_query 	  	  = apply_filters( 'gmw_fl_advanced_query', 	  true,  $this->form );
		$this->show_non_located_users = apply_filters( 'show_users_without_location', false, $this->form );
		
        /*
         * prepare the filter of bp_query_user. the filter will modify the SQL function and will check the distance
         * of each user from the address entered and will results in user ID's of the users that within
         * the radius entered. The user IDs will then pass to the next wp_query_user below.
         */
        if ( !empty($this->form['org_address'] ) ) {
            /*
             * if address entered:
             * prepare the filter of the select clause of the SQL function. the function join Buddypress's members table with
             * wppl_friends_locator table, will calculate the distance and will get only the members that
             * within the radius was chosen
             */
        	
        	if ( $this->advanced_query ) { 
        		     		
        		$clauses['bp_user_query']['select']   = $wpdb->prepare("
        				SELECT u.ID as id, ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance ",
        				array( $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] ) );
        		 
        		$clauses['bp_user_query']['from']     = " FROM {$wpdb->users} u INNER JOIN wppl_friends_locator gmwlocations ON u.id = gmwlocations.member_id";
        		$clauses['bp_user_query']['where']    = " ";
        	} else {
        		
        		$clauses['bp_user_query']['select']   = $wpdb->prepare("
        				SELECT gmwlocations.member_id as id, ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance ",
        				$this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] );
        		$clauses['bp_user_query']['from']     = " FROM wppl_friends_locator gmwlocations";
        		$clauses['bp_user_query']['where']    = " ";
        	}
        		
        	$clauses['bp_user_query']['having']   = $wpdb->prepare( 'HAVING distance <= %d OR distance IS NULL ', $this->form['radius'] );
        	$clauses['bp_user_query']['order_by'] = 'ORDER BY distance';
        
        } else {
            /*
             * if no address entered choose all members that in members table and wppl_friends_locator table
             * check agains the useids (returned from xprofile fields query) if exist and results in ids
             */
        	if ( $this->advanced_query ) {       		
        		
        		$clauses['bp_user_query']['select']   = " SELECT u.ID as id ";      		
        		
        		if ( $this->show_non_located_users ) {
        			$clauses['bp_user_query']['from']     = " FROM {$wpdb->users} u LEFT JOIN wppl_friends_locator gmwlocations ON u.id = gmwlocations.member_id";      
        			$clauses['bp_user_query']['where']    = " ";
        		} else {
        			$clauses['bp_user_query']['from']     = " FROM {$wpdb->users} u INNER JOIN wppl_friends_locator gmwlocations ON u.id = gmwlocations.member_id";
        			$clauses['bp_user_query']['where']    = " ";
        		}
        	} else {
	            $clauses['bp_user_query']['select']   = " SELECT gmwlocations.member_id as id ";	            
	            $clauses['bp_user_query']['from']     = " FROM wppl_friends_locator gmwlocations";
	            $clauses['bp_user_query']['where']    = " ";
        	}
        	$clauses['bp_user_query']['having']   = "";
        	$clauses['bp_user_query']['order_by'] = "";      	
        }
       
        /*
         * prepare the filter of the wp_query_user which is within bp_query_user.
         * the filter will modify the SQL function and will calculate the distance of each user
         * in the array of user IDs that was returned from the function above.
         * the filter will also add the members information from wppl_friends_locator table into the results
         * as well as the distance.
         */
        if ( !empty( $this->form['org_address'] ) ) {
        	
        	$clauses['wp_user_query']['query_fields'] = $wpdb->prepare(" , gmwlocations.lat, gmwlocations.long, gmwlocations.address, gmwlocations.formatted_address, gmwlocations.map_icon, ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", 
        			$this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] );
        	
        	$clauses['wp_user_query']['query_from']    = " INNER JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id ";
        	$clauses['wp_user_query']['query_where']   = " ";

        } else {
        	$clauses['wp_user_query']['query_fields'] = " , gmwlocations.lat, gmwlocations.long, gmwlocations.address, gmwlocations.formatted_address, gmwlocations.map_icon ";
        	
        	if ( $this->show_non_located_users ) {
        		$clauses['wp_user_query']['query_from']    = " LEFT JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id ";
        		$clauses['wp_user_query']['query_where']   = "";
        	} else {
        		$clauses['wp_user_query']['query_from']    = " INNER JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id ";
        		$clauses['wp_user_query']['query_where']   = " ";       		
        	}
        }
        $clauses['wp_user_query']['query_orderby'] = " ORDER BY user_login ASC ";
        $clauses['wp_user_query']['query_limit']   = "";

        //query xprofile fields
        if ( apply_filters( 'gmw_fl_do_xprofile_query', true ) ) {
	        
	        $xprofile_users = gmw_fl_query_xprofile_fields( $this->form ,$_GET );
	       
	        //if fields entered but no users returned abort the query
	        if ( $xprofile_users['status'] == 'no_ids_found' ) {
	        	$clauses['bp_user_query']['where'] .= " AND 1 = 0 ";
	        //if users returned. add them to query
	        } elseif ( $xprofile_users['status'] == 'ids_found' ) {
	        	$clauses['bp_user_query']['where'] .= $wpdb->prepare(" AND gmwlocations.member_id IN (" . str_repeat( "%d,", count( $xprofile_users['ids'] ) - 1 ) . "%d )", $xprofile_users['ids'] );
	        } 
        }
        
        return apply_filters( 'gmw_fl_after_query_clauses', $clauses, $this->form );
    }

    /**
     * Add filter to BP_user_query
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function gmwBpQuery( $gmwBpQuery ) {    	
    	 	
    	if ( $this->advanced_query ) {
	    	
	    	if ( empty( $gmwBpQuery->uid_clauses['where'] ) ) {
	    		$gmwBpQuery->uid_clauses['where'] = " WHERE 1 = 1 ";
	    	}
	    	$gmwBpQuery->uid_clauses['where'] .= $this->clauses['bp_user_query']['where'];
    	
    	} else {	
	        $gmwBpQuery->uid_clauses['where'] = $this->clauses['bp_user_query']['where'];	        
    	}
    	    	
    	// modify the function to to calculate the total rows(members).
    	$gmwBpQuery->query_vars['count_total'] = 'sql_calc_found_rows';
    	$gmwBpQuery->uid_clauses['select']     = $this->clauses['bp_user_query']['select'];
    	$gmwBpQuery->uid_clauses['select'] 	  .= $this->clauses['bp_user_query']['from'];
    	$gmwBpQuery->uid_clauses['where'] 	  .= $this->clauses['bp_user_query']['having'];
    	
        if ( isset( $this->clauses['bp_user_query']['order_by'] ) ) {
            $gmwBpQuery->uid_clauses['orderby']    = $this->clauses['bp_user_query']['order_by'];
        }
        if ( isset( $this->clauses['bp_user_query']['order'] ) ) {
            $gmwBpQuery->uid_clauses['order']      = $this->clauses['bp_user_query']['order'];
        }

        return $gmwBpQuery;
    }

    /**
     * Add filter to WP_user_query
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function gmwWpQuery( $gmwWpQuery ) {
    	
        $gmwWpQuery->query_fields  .= $this->clauses['wp_user_query']['query_fields'];
        $gmwWpQuery->query_from    .= $this->clauses['wp_user_query']['query_from'];
        $gmwWpQuery->query_where   .= $this->clauses['wp_user_query']['query_where'];
        $gmwWpQuery->query_orderby  = $this->clauses['wp_user_query']['query_orderby'];
        $gmwWpQuery->query_limit    = $this->clauses['wp_user_query']['query_limit'];
        
        return $gmwWpQuery;
    }

    /**
     * modify members_template in the loop
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function modify_member() {
        global $members_template;

        $members_template->member->member_count 		= $this->form['member_count'];
        $members_template->member->mapIcon      		= apply_filters( 'gmw_fl_map_icon', 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' . $members_template->member->member_count . '|FF776B|000000', $members_template->member, $this->form );
		$members_template->member->info_window_content 	= self::info_window_content( $members_template->member );
        
		$this->form['member_count'] ++;

        $members_template = apply_filters( 'gmw_fl_modify_member', $members_template, $this->form, $this->settings );
    }
	
    /**
     * create the content of the info window
     * @since 2.5
     * @param unknown_type $member
     * @return string
     */
    public function info_window_content( $member ) {
    
    	$address = ( !empty( $member->formatted_address ) ) ? $member->formatted_address : $member->address;
    	 
    	$output  = '';
    	$output .= '<div class="wppl-fl-info-window">';
    	$output .= '<div class="wppl-info-window-thumb">'.bp_get_member_avatar($args= 'type=full').'</div>';
    	$output .= '<div class="wppl-info-window-info">';
    	$output .= '<table>';
    	$output .= '<tr><td><div class="wppl-info-window-permalink"><a href="'.bp_get_member_permalink().'">'.$member->display_name.'</a></div></td></tr>';
    	$output .= '<tr><td><span>'.$this->form['labels']['info_window']['address'].'</span>'.$address.'</td></tr>';
    	if ( isset( $member->distance ) ) {
    		$output .= '<tr><td><span>'.$this->form['labels']['info_window']['distance'].'</span>'.$member->distance.' '.$this->form['units_array']['name'].'</td></tr>';
    	}
    	 
    	$output .= '</table>';
    	$output .= '</div>';
    	$output .= '</div>';
    	 
    	return apply_filters( 'gmw_fl_info_window_content', $output, $member, $this->form );
    }
    
    public function results() {

        //prevent BuddyPress from using its own "paged" value for the current page
        if ( !empty( $_REQUEST['upage'] ) )
        	unset( $_REQUEST['upage'] );
        
        $this->form['query_args'] = array(
        		'type'     	=> 'distance',
        		'per_page' 	=> $this->form['get_per_page'],
        		'page'		=> $this->form['paged']
        );

        // Hooks
        $this->form = apply_filters( 'gmw_fl_form_before_members_query', $this->form, $this->settings );
        
        // query clauses
        $this->clauses = $this->query_clauses();
       
        add_action( 'bp_pre_user_query', array( $this, 'gmwBpQuery' ) );
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
    }
}