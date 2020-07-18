<?php
/**
 * GEO my WP Members Locator form class.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Members Locator search query class
 *
 * @author Eyal Fitoussi
 */
class GMW_Members_Locator_Form extends GMW_Form {

	/**
	 * Permalink hook
	 *
	 * @var string
	 */
	public $object_permalink_hook = 'bp_get_member_permalink';

	/**
	 * Results message
	 *
	 * @var array
	 */
	public function results_message_placeholders() {
		return array(
			'count_message'    => __( 'Viewing {from_count} - {to_count} of {total_results} members', 'geo-my-wp' ),
			'location_message' => __( ' within {radius}{units} from {address}', 'geo-my-wp' ),
		);
	}

	/**
	 * Get info-window data.
	 *
	 * @param  object $member member object.
	 *
	 * @return [type]           [description]
	 */
	public function get_info_window_args( $member ) {

		if ( isset( $this->form['info_window']['image'] ) ) {
			if ( '' === $this->form['info_window']['image']['enabled'] ) {
				$avatar = false;
			} else {
				$avatar = bp_core_fetch_avatar(
					array(
						'item_id' => $member->ID,
						'type'    => 'full',
						'width'   => $this->form['info_window']['image']['width'],
						'height'  => $this->form['info_window']['image']['height'],
					)
				);
			}
		} else {
			$avatar = bp_core_fetch_avatar(
				array(
					'item_id' => $member->ID,
					'width'   => 180,
					'height'  => 180,
				)
			);
		}

		return array(
			'prefix' => $this->prefix,
			'type'   => ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard',
			'image'  => $avatar,
			'url'    => bp_core_get_user_domain( $member->ID ),
			'title'  => $member->display_name,
		);
	}

	/**
	 * Opening div before the search results.
	 */
	public function before_search_results() {
		echo '<div id="buddypress">';
	}

	/**
	 * Closing div after the search results.
	 */
	public function after_search_results() {
		echo '</div>';
	}

	/**
	 * Modify the members query.
	 *
	 * Join GMW locations table and do proximity search when needed.
	 *
	 * @since 3.2
	 *
	 * @param  array  $sql   clauses array.
	 *
	 * @param  object $query search query object.
	 *
	 * @return [type]        [description]
	 */
	public function modify_members_query_clauses( $sql, $query ) {

		global $wpdb;

		// Get the table column based on the type argument.
		$column = in_array( $this->form['query_args']['type'], array( 'active', 'newest', 'popular', 'online' ), true ) ? 'user_id' : 'ID';

		// add the location db fields to the query.
		$fields = ', gmw_locations.' . implode( ', gmw_locations.', $this->db_fields );
		$having = '';
		$where  = '';
		$tjoin  = "{$wpdb->base_prefix}gmw_locations gmw_locations ON u.{$column} = gmw_locations.object_id AND gmw_locations.object_type = 'user' ";
		$join   = '';

		// include specific users ID if returned from xprofile filters.
		if ( ! empty( $this->form['query_args']['gmw_args']['xprofile_users_id'] ) ) {
			$users_id = esc_sql( implode( ',', $this->form['query_args']['gmw_args']['xprofile_users_id'] ) );
			$where   .= " AND u.{$column} IN ( {$users_id} ) ";
		}

		// get address filters query.
		$address_filters = GMW_Location::query_address_fields( $this->get_address_filters(), $this->form );

		// when address provided, and not filtering based on address fields, we will do proximity search.
		if ( empty( $address_filters ) && ! empty( $this->form['lat'] ) && ! empty( $this->form['lng'] ) ) {

			// generate some radius/units data.
			if ( in_array( $this->form['units_array']['units'], array( 'imperial', 3959, 'miles', '3959' ), true ) ) {
				$earth_radius = 3959;
				$units        = 'mi';
				$degree       = 69.0;
			} else {
				$earth_radius = 6371;
				$units        = 'km';
				$degree       = 111.045;
			}

			// add units to locations data.
			$fields .= ", '{$units}' AS units";

			// since these values are repeatable, we escape them previous
			// the query instead of running multiple prepares.
			$lat      = esc_sql( $this->form['lat'] );
			$lng      = esc_sql( $this->form['lng'] );
			$distance = ! empty( $this->form['radius'] ) ? esc_sql( $this->form['radius'] ) : '';

			$fields .= " , ROUND( {$earth_radius} * acos( cos( radians( {$lat} ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance";

			$join = "INNER JOIN {$tjoin}";

			if ( ! empty( $distance ) ) {

				if ( ! apply_filters( 'gmw_disable_query_clause_between', false, 'gmw_fl' ) ) {

					// calculate the between point.
					$bet_lat1 = $lat - ( $distance / $degree );
					$bet_lat2 = $lat + ( $distance / $degree );
					$bet_lng1 = $lng - ( $distance / ( $degree * cos( deg2rad( $lat ) ) ) );
					$bet_lng2 = $lng + ( $distance / ( $degree * cos( deg2rad( $lat ) ) ) );

					$where .= " AND gmw_locations.latitude BETWEEN {$bet_lat1} AND {$bet_lat2}";
					$where .= " AND gmw_locations.longitude BETWEEN {$bet_lng1} AND {$bet_lng2} ";
				}

				// filter locations based on the distance.
				$having = "Having distance <= {$distance} OR distance IS NULL";

				// if we order by the distance.
				if ( 'distance' === $this->form['query_args']['type'] ) {
					$sql['orderby'] = 'ORDER BY distance';
				}
			}
		} else {

			// if showing members without location.
			if ( $this->enable_objects_without_location ) {

				// left join the location table into the query to display posts with no location as well.
				$join   = "LEFT JOIN {$tjoin}";
				$where .= " {$address_filters}";

			} else {

				$join   = "INNER JOIN {$tjoin}";
				$where .= " {$address_filters} AND ( gmw_locations.latitude != 0.000000 && gmw_locations.longitude != 0.000000 )";
			}
		}

		$clauses = array();

		// we need to sepeate the SELECT and FROM caluses in the original query.
		$select = explode( 'FROM', $sql['select'] );

		/**
		 * Build custom query using BuddyPress members query clauses
		 *
		 * Combine with the locations table and proximity search.
		 */
		$clauses['select']  = 'SELECT SQL_CALC_FOUND_ROWS';
		$clauses['fields']  = str_replace( 'SELECT', '', $select[0] ) . $fields;
		$clauses['from']    = " FROM {$select[1]}";
		$clauses['join']    = $join;
		$clauses['where']   = ! empty( $sql['where'] ) ? 'WHERE ' . implode( ' AND ', $sql['where'] ) : 'WHERE 1 = 1';
		$clauses['where']  .= $where;
		$clauses['groupby'] = "GROUP BY u.{$column}";
		$clauses['having']  = $having;
		$clauses['orderby'] = $sql['orderby'];
		$clauses['order']   = $sql['order'];
		$clauses['limit']   = $sql['limit'];

		// modify the query.
		$clauses = apply_filters( 'gmw_fl_location_query_clauses', $clauses, $this->form, $query, $this );

		// get results of locations + users data.
		$this->locations = $wpdb->get_results( implode( ' ', $clauses ) ); // WPCS: db call ok, cache ok, unprepared SQL ok.

		// get total results.
		$this->total_users = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // WPCS: db call ok, cache ok.

		// if locations found.
		if ( ! empty( $this->locations ) ) {

			/**
			 * Here we build a fake query to return back to the SQL function of the BuddyPress query.
			 *
			 * Since we already retrieved the users data using our custom query
			 *
			 * We dont need BuddyPress to run another query to get the users id.
			 *
			 * This query will simply use the users id we already have.
			 */
			$users_id_sql = '';
			$count        = 0;

			foreach ( $this->locations as $location ) {

				if ( $count > 0 ) {
					$users_id_sql .= ' union all ';
				}

				$users_id_sql .= 'select ' . $location->id . ' id';

				$count++;
			}

			/**
			 * Otherwise, when no locations are found, we pass 0 as the users id to include
			 *
			 * In the users query to make it fail and show no results.
			 */
		} else {

			$users_id_sql = 'SELECT 0 id';
		}

		$sql['select']  = $users_id_sql;
		$sql['where']   = '';
		$sql['orderby'] = '';
		$sql['order']   = '';
		$sql['limit']   = '';

		return $sql;
	}

	/**
	 * Merge locations data with users data in the results.
	 *
	 * @since 3.2
	 *
	 * @param  array $results search results array.
	 *
	 * @return [type]          [description]
	 */
	public function append_location_data_to_results( $results ) {

		$users = array();

		foreach ( $results['users'] as $u ) {
			$users[ $u->ID ] = $u;
		}

		$temp = array();

		// merge users data with locations data.
		foreach ( $this->locations as $location ) {

			if ( ! empty( $users[ $location->id ] ) ) {
				foreach ( $users[ $location->id ] as $key => $value ) {
					$location->$key = $value;
				}
			}

			$temp[] = $location;
		}

		// return new data to BuddyPress.
		$results['users'] = $temp;
		$results['total'] = $this->total_users;

		return $results;
	}

	/**
	 * Members search query.
	 *
	 * @return [type] [description]
	 */
	public function search_query() {

		// look for xprofile values in URL.
		if ( isset( $this->form['form_values']['xf'] ) && array_filter( $this->form['form_values']['xf'] ) ) {

			$fields_values = $this->form['form_values']['xf'];

			// otherwise, can do something custom with xprofile fields
			// by passing array of array( fields => value ).
		} else {
			$fields_values = apply_filters( 'gmw_fl_xprofile_fields_query_default_values', array(), $this->form );
		}

		$xp_users_id = array();

		/**
		 * Query xprofile fields.
		 *
		 * What xprofile query returns -1, it means that no users were
		 *
		 * found and we can abort and return no results.
		 */
		if ( apply_filters( 'gmw_fl_xprofile_query_enabled', true, $this->form ) && array_filter( $fields_values ) && ( $xp_users_id = gmw_query_xprofile_fields( $fields_values, $this->form ) ) == -1 ) {
			return false;
		}

		// add users ID to GEO my WP cache. We will use this data when modiying the query.
		$this->query_cache_args['xprofile_users_id'] = $xp_users_id;

		/**
		 * [ DEPRECATED ] can show members without locations.
		 *
		 * Instead, use the filter 'gmw_form_enable_objects_without_location' to return true || false
		 *
		 * Or use the filter 'gmw_fl_search_query_args' below to set the
		 *
		 * $this->form['query_args']['gmw_args']['showing_objects_without_location'] to true or false.
		 */
		$show_non_located_members = apply_filters( 'gmw_show_members_without_locations', false, $this->form );

		if ( $show_non_located_members ) {
			$this->enable_objects_without_location                      = true;
			$this->query_cache_args['showing_objects_without_location'] = true;
		}

		// query args.
		$this->form['query_args'] = apply_filters(
			'gmw_fl_search_query_args',
			array(
				'type'        => 'distance',
				'per_page'    => $this->form['get_per_page'],
				'page'        => $this->form['paged'],
				'count_total' => false, // we do a total count in our custom members query.
				'gmw_args'    => $this->query_cache_args,
			),
			$this->form,
			$this
		);

		// modify the form values before the query takes place.
		$this->form     = apply_filters( 'gmw_fl_form_before_members_query', $this->form, $this );
		$internal_cache = GMW()->internal_cache;

		global $members_template;

		if ( $internal_cache ) {

			// prepare for cache.
			$hash             = md5( wp_json_encode( $this->form['query_args'] ) );
			$query_args_hash  = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_user_query' );
			$members_template = get_transient( $query_args_hash );
		}

		if ( ! $internal_cache || empty( $members_template ) ) {

			// filter the members query.
			// Use high priority to allow other plugins to use this filter before GEO my WP does.
			add_action( 'bp_user_query_uid_clauses', array( $this, 'modify_members_query_clauses' ), 500, 2 );
			add_filter( 'bp_core_get_users', array( $this, 'append_location_data_to_results' ), 30 );

			// query members.
			$results = bp_has_members( $this->form['query_args'] ) ? true : false;

			remove_action( 'bp_user_query_uid_clauses', array( $this, 'modify_members_query_clauses' ), 500, 2 );
			remove_filter( 'bp_core_get_users', array( $this, 'append_location_data_to_results' ), 30 );

			// set new query in transient.
			if ( $internal_cache ) {
				set_transient( $query_args_hash, $members_template, GMW()->internal_cache_expiration );
			}
		}

		// Modify the form after the search query.
		$this->form       = apply_filters( 'gmw_fl_form_after_members_query', $this->form, $this );
		$members_template = apply_filters( 'gmw_fl_members_before_members_loop', $members_template, $this->form, $this );
		$this->query      = $members_template;

		$this->form['results_count'] = count( $members_template->members );
		$this->form['total_results'] = $members_template->total_member_count;
		$this->form['max_pages']     = ceil( $this->form['total_results'] / $this->form['get_per_page'] );

		$temp_array = array();

		foreach ( $members_template->members as $member ) {
			$temp_array[] = parent::the_location( $member->id, $member );
		}

		$this->form['results']     = $temp_array;
		$members_template->members = $temp_array;

		return $this->form['results'];
	}
}
