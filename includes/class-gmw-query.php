<?php 
/**
 * The main member template loop class.
 *
 * Responsible for loading a group of members into a loop for display.
 */
class GMW_Query {

	/**
	 * The number of members returned by the paged query.
	 *
	 * @access public
	 * @var int
	 */
	public $location_count = 0;


	/**
	 * The loop iterator.
	 *
	 * @access public
	 * @var int
	 */
	public $current_location = -1;

	
	/**
	 * Array of members located by the query.
	 *
	 * @access public
	 * @var array
	 */
	public $locations;

	/**
	 * The member object currently being iterated on.
	 *
	 * @access public
	 * @var object
	 */
	public $location;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	public $in_the_loop = false;

	/**
	 * The type of member being requested. Used for ordering results.
	 *
	 * @access public
	 * @var string
	 */
	
	public $include_users;

	public $exclude_users;

	public $type;

	public $address;

	public $radius;

	public $units;

	public $coords = array(
		'lat' => null,
		'lng' => null
	);

	public $address_fields;

	public $fields;

	/**
	 * The unique string used for pagination queries.
	 *
	 * @access public
	 * @var string
	 */
	public $pag_arg;

	/**
	 * The page number being requested.
	 *
	 * @access public
	 * @var string
	 */
	public $page;

	/**
	 * The number of items being requested per page.
	 *
	 * @access public
	 * @var string
	 */
	public $locations_per_page;

	/**
	 * An HTML string containing pagination links.
	 *
	 * @access public
	 * @var string
	 */
	public $pag_links;

	/**
	 * The total number of members matching the query parameters.
	 *
	 * @access public
	 * @var int
	 */
	public $found_locations = 0;

	public $max_num_pages = 0;

	/**
	 * Constructor method.
	 *
	 * @see BP_User_Query for an in-depth description of parameters.
	 *
	 * @param string       $type            Sort order.
	 * @param int          $page_number     Page of results.
	 * @param int          $per_page        Number of results per page.
	 * @param int          $max             Max number of results to return.
	 * @param int          $user_id         Limit to friends of a user.
	 * @param string       $search_terms    Limit to users matching search terms.
	 * @param array        $include         Limit results by these user IDs.
	 * @param bool         $populate_extras Fetch optional extras.
	 * @param array        $exclude         Exclude these IDs from results.
	 * @param array        $meta_key        Limit to users with a meta_key.
	 * @param array        $meta_value      Limit to users with a meta_value (with meta_key).
	 * @param string       $page_arg        Optional. The string used as a query parameter in pagination links.
	 *                                      Default: 'upage'.
	 * @param array|string $member_type     Array or comma-separated string of member types to limit results to.
	 */
	function __construct( $args = '' ) {

		if ( ! empty( $args ) ) {
			
	        $this->parse_query( $args );

            return $this->get_locations();
	    }
	}



	public function parse_query( $args =  '' ) {
		
		// type: active ( default ) | random | newest | popular | online | alphabetical
		$qv = wp_parse_args( $args, array(
			//'type'            => array( 'post' ),
			//'page'            => 1,
			'paged'			  => 1,
			'per_page'        => -1,
			'offset'		  => null,
			//'max'             => false,
			//'page_arg'        => 'upage', 
			'include_users'   => null,
			'exclude_users'   => null,
			'location_type'   => null,
			'address_fields'  => null,
			'address'    	  => null,
			'lat'			  => null,
			'lng'			  => null,   
			'radius' 		  => 200,
			'units'			  => 'imperial',
			'oredrby'		  => 'ID',
			'order'			  => 'ASC',
			'fields'		  => '*',
		) );

		//$this->pag_arg  = sanitize_key( $page_arg );
		//$this->pag_page = bp_sanitize_pagination_arg( $this->pag_arg, $page_number );
		//$this->pag_num  = bp_sanitize_pagination_arg( 'num',          $per_page    );
		//
		
		$qv['include_users'] = ( is_array( $qv['include_users'] ) && ! empty( $qv['include_users'] ) ) ? array_map( 'absint', $qv['include_users'] ) : null;
		$qv['exclude_users'] = ( is_array( $qv['exclude_users'] ) && ! empty( $qv['exclude_users'] ) ) ? array_map( 'absint', $qv['exclude_users'] ) : null;

		$qv['offset'] = absint( $qv['offset'] );

		$qv['locations_type'] = is_array( $qv['locations_type'] ) ? $qv['locations_type'] : null;
		$qv['units']		  = $qv['units'] == 'metric' ? 'metric' : 'imperial';
		$qv['earth_radius']	  = $qv['units'] == 'imperial' ? 3959 : 6371;

		if ( is_numeric( $qv['lat'] ) && is_numeric( $qv['lng'] ) ) {
			$this->coords = array(
				'lat' => $qv['lat'],
				'lng' => $qv['lng']
			);
		} else {
			$qv['lat'] = null;
			$qv['lng'] = null;
		}

		$qv['radius']   = is_numeric( $qv['radius'] ) ? $qv['radius'] : null;
		$qv['oreder']	= $qv['order'] == 'DESC' ? 'DESC' : 'ASC';
		//$qv['orederby']	= $qv['orederby'] == 'distance' && ( ! empty( $qv['address'] )   ? 3959 : 6371;
		$qv['fields']   = trim( $qv['fields'] );

		$this->query = $this->query_vars = $qv;

	}

	private function set_found_locations( $qv, $limits ) {
		global $wpdb;

		// Bail if posts is an empty array. Continue if posts is an empty string,
		// null, or false to accommodate caching plugins that fill posts later.
		if ( $qv['no_found_rows'] || ( is_array( $this->locations ) && ! $this->locations ) )
			return;

		if ( ! empty( $limits ) ) {
			/**
			 * Filter the query to run for retrieving the found posts.
			 *
			 * @since 2.1.0
			 *
			 * @param string   $found_posts The query to run to find the found posts.
			 * @param WP_Query &$this       The WP_Query instance (passed by reference).
			 */
			$this->found_locations = $wpdb->get_var( apply_filters_ref_array( 'found_posts_query', array( 'SELECT FOUND_ROWS()', &$this ) ) );
		} else {
			$this->found_locations = count( $this->locations );
		}

		/**
		 * Filter the number of found posts for the query.
		 *
		 * @since 2.1.0
		 *
		 * @param int      $found_posts The number of posts found.
		 * @param WP_Query &$this       The WP_Query instance (passed by reference).
		 */
		//$this->found_locations = apply_filters_ref_array( 'found_posts', array( $this->found_locations, &$this ) );

		if ( ! empty( $limits ) )
			$this->max_num_pages = ceil( $this->found_locations / $qv['per_page'] );
	}

	public function get_locations() {

		global $gmw_query;

		$gmw_query = $this;

		//$this->parse_query();

		$qv = $this->query_vars;

		//print_r($qv);
		global $wpdb;

		$clauses = array(
			'found_rows' => '',
			'distinct'   => '',
			'fields' 	 => $this->query_vars['fields'],
			'from'	     => $wpdb->prefix.'gmw_locations gmwlocations',
			'join'       => '',
			'where'      => '',
			'groupby'    => '',
			'having'     => '',
			'orderby'    => '',
			'limits'     => ''
		);

		//filter location type
		if ( ! empty( $qv['location_type'] ) ) {
			$clauses['where'] .= $this->parse_search_location_type();
		}

		//filter user ID
		if ( ! empty( $qv['include_users'] ) || ! empty( $qv['exclude_users'] ) ) {
			$clauses['where'] .= $this->parse_search_users();
		}

		//filter address fields
        if ( ! empty( $this->address_fields ) ) {
        	$clauses['where'] .= $this->parse_search_address_fields();
        }

		//Filter by address or coordinates
        if ( ! empty( $this->address ) || ( isset( $qv['lat'] ) && isset( $qv['lng'] ) ) ) {
			
			//check for coords first
			if ( ! isset( $qv['lat'] ) || ! isset( $qv['lng'] ) ) {
				sdf();
				//if no coords found geocode the addres entered
				include_once( GMW_PATH . '/includes/gmw-geocoder.php' );
	        	
	        	$geocoded = gmw_geocoder( $this->address );

	        	//get the coords from the geocoded data
	        	$this->coords['lat'] = $geocoded['lat'];
	        	$this->coords['lng'] = $geocoded['lng'];
	        }

	    	$clauses['fields'] .= $wpdb->prepare( ",ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.lng ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", 
	    		array( $qv['earth_radius'], $this->coords['lat'], $this->coords['lng'], $this->coords['lat'] ) );
	    	
	        //make sure we pass numeric or decimal as radius
	        if ( ! empty( $qv['radius'] ) && is_numeric( $qv['radius'] ) )  {
	    	   $clauses['having'] .= $wpdb->prepare( "distance <= %s OR distance IS NULL", $qv['radius'] );
	    	}
        } 

        if ( empty( $clauses['orderby'] ) ) {

                $clauses['orderby'] = "gmwlocations.ID " . $qv['order'];

        } elseif ( ! empty( $qv['order'] ) ) {

                $clauses['orderby'] .= " {$qv['order']}";
        }

        // Paging
        if ( $qv['per_page'] != -1 ) {
	        
	        $page = absint( $qv['paged'] );
	            
	        if ( ! $page ) {
	        	$page = 1;
	        }

	   		if ( empty( $qv['offset'] ) ) {
	            
	            $pgstrt = absint( ( $page - 1 ) * $qv['per_page'] ) . ', ';
	        
	        } else { 
	        	// we're ignoring $page and using 'offset'
	            $qv['offset'] = absint( $qv['offset'] );
	            $pgstrt = $qv['offset'] . ', ';
	        }

	        $clauses['limits']     = 'LIMIT ' . $pgstrt . $qv['per_page'];
	        
	    }
		
	    if ( ! empty( $clauses['limits'] ) ) {
	    	$clauses['found_rows'] = 'SQL_CALC_FOUND_ROWS'; 
	    }

        $this->request  = "SELECT {$clauses['found_rows']} {$clauses['distinct']} {$clauses['fields']}";
        $this->request .= " FROM {$clauses['from']} {$clauses['join']}"; 
        $this->request .= " WHERE 1=1 {$clauses['where']}";

        if ( ! empty( $clauses['groupby'] ) ) {
        	$this->request .= " GROUP BY {$clauses['groupby']}"; 
        }

        if ( ! empty( $clauses['having'] ) ) {
        	$this->request .= " HAVING {$clauses['having']}";
        }

        $this->request .= " ORDER BY {$clauses['orderby']}";
         $this->request .= $clauses['limits'];
        

        $this->locations = $wpdb->get_results( $this->request );

        if ( $this->locations ) {
			
			$this->location_count = count( $this->locations );
			$this->set_found_locations( $q, $limits );

		} else {
			$this->location_count = 0;
			$this->locations = array();
		}

		

		return $this->posts;

        

	}

	public function parse_search_location_type() {

		global $wpdb;

		if ( is_array( $this->location_type ) ) {
			$output = $wpdb->prepare( " AND  gmwlocations.object_type IN (".str_repeat( "%s,", count( $this->query_vars['location_type'] ) - 1 ) . "%s )", $this->query_vars['location_type'] );
		} else {
			$output = $wpdb->prepare( " AND gmwlocations.object_type = %s", $this->query_vars['location_type'] );
		}

		return apply_filters( 'gmw_query_search_location_type', $output, array( &$this ) );
	}

	public function parse_search_users() {

		global $wpdb;

		if ( isset( $this->query_vars['include_users'] ) ) {
			$users = $this->query_vars['include_users'];
			$or1   = 'IN';
			$or2   = '=';
		} else {
			$users = $this->query_vars['exclude_users'];
			$or1   = 'NOT IN';
			$or2   = '!=';
		}
		
		$output = '';
		if ( is_array( $users ) ) {
			$output = $wpdb->prepare( " AND  gmwlocations.user_id {$or1} (".str_repeat( "%d,", count( $users ) - 1 ) . "%d )", $users );
		} else {
			$output = $wpdb->prepare( " AND gmwlocations.user_id {$or2} %d", $users );
		}	

		return apply_filters( 'gmw_query_search_users', $output, array( &$this ) );
	}

	public function parse_search_address_fields() {
        
        global $wpdb;

        $output = '';

        foreach ( $this->query_vars['address_fields'] as $field => $value ) {
        	
        	if ( empty( $value ) )
        		continue;

        	if ( in_array( $field, array( 'region_name', 'region_code', 'state' ) ) ) {
        		
        		$output .= $wpdb->prepare( " AND ( gmwlocations.region_name = %s OR gmwlocations.region_code = %s ) ", $value, $value );

        	} elseif ( in_array( $field, array( 'country_name', 'country_code', 'country' ) ) ) {
        		
        		$output .= $wpdb->prepare( " AND ( gmwlocations.country_name = %s OR gmwlocations.country_code = %s ) ", $value , $value );
        	
        	} elseif ( $field  == 'postcode' || $field  == 'zipcode' ) {
        	
        		$output .= $wpdb->prepare( " AND gmwlocations.postcode = %s ", $value );
        	
        	} elseif ( in_array( $field, array( 'street', 'county', 'neighborhood', 'city' ) ) ) {
        		$output .= $wpdb->prepare( " AND gmwlocations.{$field} = %s ", $value );
        	}
        }  

    	return apply_filters( 'gmw_query_search_address_fields', $output, array( &$this ) );
	}

	public function parse_search_location() {

		//check for coords first
		if ( ! isset( $this->query_vars['lat'] ) || ! isset( $this->query_vars['lng'] ) ) {
			
			//if no coords found geocode the addres entered
			include_once( GMW_PATH . '/includes/gmw-geocoder.php' );
        	
        	$geocoded = gmw_geocoder( $this->address );

        	//get the coords from the geocoded data
        	$this->coords['lat'] = $geocoded['lat'];
        	$this->coords['lng'] = $geocoded['lng'];
        }

    	$clauses['fields'] .= $wpdb->prepare( ",ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.lng ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", 
    		array( $this->units, $this->coords['lat'], $this->coords['lng'], $this->coords['lat'] ) );
    	
        //make sure we pass numeric or decimal as radius
        if ( is_numeric( $this->radius ) || is_float( $this->radius ) )  {
    	   $clauses['having'] .= $wpdb->prepare( "HAVING distance <= %s OR distance IS NULL", $this->radius );
    	}

	}

	/**
	 * Whether there are members available in the loop.
	 *
	 * @see bp_has_members()
	 *
	 * @return bool True if there are items in the loop, otherwise false.
	 */
	function has_locations() {
		if ( $this->location_count )
			return true;

		return false;
	}

	/**
	 * Set up the next member and iterate index.
	 *
	 * @return object The next member to iterate over.
	 */
	function next_location() {
		$this->current_location++;
		$this->location = $this->locations[$this->current_location];

		return $this->location;
	}

	/**
	 * Rewind the members and reset member index.
	 */
	function rewind_locations() {
		$this->current_location = -1;
		if ( $this->location_count > 0 ) {
			$this->location = $this->locations[0];
		}
	}

	/**
	 * Set up the current member inside the loop.
	 *
	 * Used by {@link bp_the_member()} to set up the current member data
	 * while looping, so that template tags used during that iteration make
	 * reference to the current member.
	 *
	 * @see bp_the_member()
	 */
	function the_location() {

		global $gmw_location;

		$this->in_the_loop = true;

		// loop has just started
		if ( $this->current_location == -1 ) {

			/**
             * Fires once the loop is started.
             *
             * @since 2.0.0
             *
             * @param WP_Query &$this The WP_Query instance (passed by reference).
             */
			do_action( 'location_loop_start', array( &$this ) );
		}

		$gmw_location = $this->location = $this->next_location();
	}

	/**
	 * Whether there are members left in the loop to iterate over.
	 *
	 * This method is used by {@link bp_members()} as part of the while loop
	 * that controls iteration inside the members loop, eg:
	 *     while ( bp_members() ) { ...
	 *
	 * @see bp_members()
	 *
	 * @return bool True if there are more members to show, otherwise false.
	 */
	
	function have_locations() {

		if ( $this->current_location + 1 < $this->location_count ) {
			return true;
		} elseif ( $this->current_location + 1 == $this->location_count && $this->location_count > 0 ) {

			/**
			 * Fires right before the rewinding of members listing.
			 *
			 * @since BuddyPress (1.5.0)
			 */
			do_action( 'location_loop_end', array( &$this ) );
			// Do some cleaning up after the loop
			$this->rewind_locations();
		}

		$this->in_the_loop = false;
		return false;
	}

	
}

/**
 * Set up the current member inside the loop.
 *
 * @return object
 */
function gmw_the_location() {
	global $gmw_query;
	return $gmw_query->the_location();
}

/**
 * Check whether there are more members to iterate over.
 *
 * @return bool
 */
function gmw_have_locations() {
	global $gmw_query;
	print_r($wp_query);
	return $gmw_query->have_locations();
}


echo '<pre>';

$args = array( 
	'address'		  => 'sdfdfsdfdsffsdfs',
	'include_users'   => '',
	//'exclude_users' => 1,
	//'location_type'   => array( 'user','post' ), 
	'radius'	=> '22020202020',
	'units'	 	=> 'metric',
	//'coords'    => array(
	'lat'	=> 26.0197012,
	'lng'	=> -80.1819268,
	//),
	'address_fields' => array(
		'state' => 'hawaii'
	)
	//'fields'	=> 'street,city,lat,lng'
);

//print_r( new GMW_Query( $args ) );
global $gmw_query;

$wp_query =  new GMW_Query( $args );

print_r($wp_query);
while ( gmw_have_locations() ) : gmw_the_location();
	global $gmw_query;
echo '11';
	//echo 
	print_r( $gmw_query->location );
	//print_r($gmw_query );
endwhile;
/*
while ( gmw_locations()) : gmw_the_location();
	self::modify_member();
endwhile;
*/
echo '</pre>';