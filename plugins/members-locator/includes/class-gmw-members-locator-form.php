<?php
/**
 * GEO my WP Members Locator form class.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extend the Memebrs Locator Form classes.
 *
 * @since 4.0.
 */
trait GMW_Members_Locator_Form_Trait {

	/**
	 * Total number of users.
	 *
	 * @var int
	 */
	public $total_users = 0;

	/**
	 * Modifies the BP members query.
	 *
	 * Join GMW locations table and do proximity search when needed.
	 *
	 * @since 1.1
	 *
	 * @param  object $query object of query clauses.
	 *
	 * @return string        modified sql query
	 */
	public function query_clauses( $query ) {

		global $wpdb;

		// Try getting the column using the SELECT clause.
		if ( strpos( $query->uid_clauses['select'], 'ON u.ID' ) !== false ) {
			$column = 'ID';
		} elseif ( strpos( $query->uid_clauses['select'], 'ON u.user_id' ) !== false ) {
			$column = 'user_id';
		} else {
			$column = in_array( $this->form['query_args']['type'], array( 'active', 'newest', 'popular', 'online' ), true ) ? 'user_id' : 'ID';
		}

		$location_status = '';

		if ( apply_filters( 'gmw_search_query_location_status_enabled', true, $this->form ) ) {
			$location_status = ' AND gmw_locations.status = 1';
		}

		$blog_id      = gmw_get_blog_id( 'user' );
		$fields       = ', ' . $this->db_fields;
		$having       = '';
		$where        = '';
		$join         = "INNER JOIN {$wpdb->base_prefix}gmw_locations gmw_locations ON ( u.{$column} = gmw_locations.object_id AND gmw_locations.object_type = 'user' AND gmw_locations.blog_id = {$blog_id}{$location_status} ) ";
		$units        = '';
		$distance_sql = "'' AS distance";

		// get address filters query.
		$address_filters = gmw_get_address_fields_filters_sql( $this->form['address_filters'], $this->form );

		// search within map bounderies.
		if ( ! empty( $this->form['form_values']['nelatlng'] ) && ! empty( $this->form['form_values']['swlatlng'] ) ) {

			$where .= gmw_get_locations_within_boundaries_sql( $this->form['form_values']['swlatlng'], $this->form['form_values']['nelatlng'] );

			// when address provided, and not filtering based on address fields, we will do proximity search.
		} elseif ( '' === $address_filters && ! empty( $this->form['lat'] ) && ! empty( $this->form['lng'] ) ) {

			// generate some radius/units data.
			if ( 'imperial' === $this->form['units'] ) {
				$earth_radius = 3959;
				$units        = 'mi';
				$degree       = 69.0;
			} else {
				$earth_radius = 6371;
				$units        = 'km';
				$degree       = 111.045;
			}

			// since these values are repeatable, we escape them previous
			// the query instead of running multiple prepares.
			$lat          = esc_sql( $this->form['lat'] );
			$lng          = esc_sql( $this->form['lng'] );
			$distance     = ! empty( $this->form['radius'] ) ? esc_sql( $this->form['radius'] ) : '';
			$distance_sql = "ROUND( {$earth_radius} * acos( cos( radians( {$lat} ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance";

			if ( ! empty( $distance ) ) {

				if ( ! apply_filters( 'gmw_disable_query_clause_between', false, 'gmw_' . $this->form['prefix'], $this->form ) ) {

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
			}

			// if we order by the distance.
			if ( 'distance' === $this->form['query_args']['type'] ) {
				$query->uid_clauses['orderby'] = 'ORDER BY distance';
			}
		} else {

			// if showing members without location.
			if ( ! empty( $this->form['enable_objects_without_location'] ) ) {

				// left join the location table into the query to display posts with no location as well.
				$join = str_replace( 'INNER', 'LEFT', $join );

			} else {

				$where .= ' AND ( gmw_locations.latitude != 0.000000 && gmw_locations.longitude != 0.000000 )';
			}

			$where .= ' ' . $address_filters;
		}

		$fields .= ", {$distance_sql}, '{$units}' AS units";

		$clauses = array();

		// we need to sepeate the SELECT and FROM caluses in the original query.
		$select = explode( 'FROM', $query->uid_clauses['select'] );

		$clauses['select']  = 'global_maps' === $this->form['addon'] ? 'SELECT' : 'SELECT SQL_CALC_FOUND_ROWS';
		$clauses['fields']  = str_replace( 'SELECT', '', $select[0] ) . $fields;
		$clauses['from']    = " FROM {$select[1]}";
		$clauses['join']    = $join;
		$clauses['where']   = $query->uid_clauses['where'];
		$clauses['where']  .= $where;
		$clauses['groupby'] = "GROUP BY u.{$column}";
		$clauses['having']  = $having;
		$clauses['orderby'] = $query->uid_clauses['orderby'];
		$clauses['order']   = $query->uid_clauses['order'];
		$clauses['limit']   = $query->uid_clauses['limit'];

		// Deprecated filter. Use the below instead.
		$clauses = apply_filters( 'gmw_' . $this->form['prefix'] . '_location_query_clauses', $clauses, $this->form, $query, $this );

		// New Filters.
		$clauses = apply_filters( 'gmw_members_locator_query_clauses', $clauses, $this->form, $this, $query );
		$clauses = apply_filters( 'gmw_' . $this->form['prefix'] . '_members_query_clauses', $clauses, $this->form, $this, $query );

		// If orderby is an array.
		if ( is_array( $clauses['orderby'] ) ) {

			$orderby_multiple = array();

			foreach ( $clauses['orderby'] as $part ) {
				$orderby_multiple[] = $part[0] . ' ' . $part[1];
			}

			$clauses['orderby'] = 'ORDER BY ' . implode( ', ', $orderby_multiple );
			$clauses['order']   = '';
		}

		// get results of locations + users data.
		$this->locations = $wpdb->get_results( implode( ' ', $clauses ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( 'global_maps' === $this->form['addon'] ) {

			$query->gmw_locations = $this->locations;

			return $query;
		}

		// get total results.
		$this->total_users = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // WPCS: db call ok, cache ok.

		// if locations found.
		if ( ! empty( $this->locations ) ) {

			$ids = wp_list_pluck( $this->locations, 'id' );

			/**
			 * Otherwise, when no locations are found
			 *
			 * We pass 0 as the users id to include
			 *
			 * in the users query to make it fail and show no results.
			 */
		} else {

			$ids = array( 0 );
		}

		$ids = implode( ',', $ids );

		$query->uid_clauses['where']  .= " AND u.{$column} IN ( {$ids} )";
		$query->uid_clauses['orderby'] = '';
		$query->uid_clauses['order']   = '';
		$query->uid_clauses['limit']   = '';

		return $query;
	}

	/**
	 * Merge locations data with users data in the results.
	 *
	 * @since 1.1
	 *
	 * @param  array $results array of objects.
	 *
	 * @return array of objects with locations.
	 */
	public function append_location_data_to_results( $results ) {

		$users = array();

		foreach ( $results['users'] as $user ) {
			$users[ $user->ID ] = $user;
		}

		$temp_users = array();

		// merge users data with locations data.
		foreach ( $this->locations as $location ) {

			if ( ! isset( $users[ $location->id ] ) ) {
				continue;
			}

			// Merge location object with user object.
			$location = (object) array_merge( (array) $location, (array) $users[ $location->id ] );

			// Add some data manually as it might be missing for users without a location.
			if ( empty( $location->location_id ) ) {
				$location->object_type   = 'user';
				$location->object_id     = $location->ID;
				$location->user_id       = $location->ID;
				$location->location_name = isset( $location->name ) ? $location->name : '';
			}

			$location     = apply_filters( 'gmw_' . $this->form['prefix'] . '_modify_location_data_to_results', $location, $results, $this->form, $this );
			$temp_users[] = $location;
		}

		// Return new data to BuddyPress.
		$results['users'] = $temp_users;
		$results['total'] = $this->total_users;

		return $results;
	}

	/**
	 * Perform xProfile fields query.
	 *
	 * @param  [type] $query [description].
	 *
	 * @return [type]        [description]
	 */
	public function xprofile_query( $query ) {

		if ( empty( $this->form['query_args']['xprofile_query'] ) ) {
			return $query;
		}

		if ( ! empty( $query->query_vars['xprofile_query'] ) ) {
			$query->query_vars['xprofile_query'] = array_merge( $query->query_vars['xprofile_query'], $this->form['query_args']['xprofile_query'] );
		} else {
			$query->query_vars['xprofile_query'] = $this->form['query_args']['xprofile_query'];
		}

		return $query;
	}

	/**
	 * Pass an array of arguments to the search query.
	 *
	 * Arguments can be modified using the filter:
	 *
	 * apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_args', $this->form['query_args'], $this->form, $this );
	 *
	 * The filter can be found in geo-my-wp/includes/class-gmw-base-form.php.
	 *
	 * in GMW_Form_Core->parse_query_args();
	 *
	 * @since 4.0
	 *
	 * @return [type] [description]
	 */
	public function get_query_args() {

		return array(
			'type'           => $this->form['orderby'],
			'per_page'       => ( -1 === $this->form['per_page'] || '-1' === $this->form['per_page'] ) ? '' : $this->form['per_page'],
			'page'           => $this->form['paged'],
			'count_total'    => false, // we do a total count in our custom members query.
			'xprofile_query' => gmw_get_xprofile_query_args( $this->form ),
			'page_arg'       => 'paged',
		);
	}

	/**
	 * Execute the search query.
	 *
	 * There are various filters that can be used to filter the form object and the query before and after the search query takes place.
	 *
	 * The filters can be found in geo-my-wp/includes/class-gmw-base-form.php.
	 *
	 * in GMW_Form_Core->pre_search_query_hooks() and GMW_Form_Core->post_search_query_hooks();
	 *
	 * Pre search query filters:
	 *
	 * apply_filters( 'gmw_form_before_search_query', $this->form, $this );
	 *
	 * apply_filters( 'gmw_' . $this->form['component'] . '_form_before_search_query', $this->form, $this );
	 *
	 * apply_filters( 'gmw_' . $this->form['prefix'] . '_form_before_search_query', $this->form, $this );
	 *
	 * Post search query filters:
	 *
	 * // Modify the form object.
	 * $this->form  = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_after_search_query', $this->form, $this );
	 *
	 * // Modify the search query.
	 * $this->query = apply_filters( 'gmw_' . $this->form['prefix'] . '_query_after_search_query', $this->query, $this->form, $this );
	 *
	 * @since 4.0
	 */
	public function parse_search_query() {

		// filter the members query.
		// Use high priority to allow other plugins to use this filter before GEO my WP does.
		add_action( 'bp_pre_user_query_construct', array( $this, 'xprofile_query' ), 90 );
		add_action( 'bp_pre_user_query', array( $this, 'query_clauses' ), 90 );
		add_filter( 'bp_core_get_users', array( $this, 'append_location_data_to_results' ), 90 );

		bp_has_members( $this->form['query_args'] ) ? true : false;

		remove_action( 'bp_pre_user_query_construct', array( $this, 'xprofile_query' ), 90 );
		remove_action( 'bp_pre_user_query', array( $this, 'query_clauses' ), 90 );
		remove_filter( 'bp_core_get_users', array( $this, 'append_location_data_to_results' ), 39 );

		global $members_template;

		$this->query = $members_template;
	}

	/**
	 * Parse the search query results.
	 *
	 * @since 4.0
	 */
	public function parse_query_results() {

		global $members_template;

		// For when coming from cache.
		$members_template = $this->query;

		if ( ! empty( $members_template->members ) ) {

			$this->form['results']       = $members_template->members;
			$this->form['results_count'] = count( $members_template->members );
			$this->form['total_results'] = $members_template->total_member_count;
			$this->form['max_pages']     = ! empty( absint( $this->form['per_page'] ) ) ? ceil( $this->form['total_results'] / $this->form['per_page'] ) : 1;
		}
	}

	/**
	 * The members loop.
	 *
	 * To be used when displaying map only without the results.
	 *
	 * In that case we need to run the loop in order to collect some data for the map.
	 *
	 * @since 4.0
	 *
	 * @param  boolean $include [description].
	 */
	public function object_loop( $include = false ) {

		global $members_template;

		// The variables are for AJAX forms deprecated template files. To be removed.
		// phpcs:disable.
		$gmw       = $this->form;
		$gmw_form  = $this;
		$gmw_query = $this->query;
		// phpcs:enable.

		while ( bp_members() ) :

			bp_the_member();

			$member = $members_template->member;

			do_action( 'gmw_the_object_location', $member, $this->form );

			// For AJAX forms deprecated template files. To be removed.
			if ( $include ) {

				if ( empty( $gmw['search_results']['styles']['disable_single_item_template'] ) ) {
					include $gmw['results_template']['content_path'] . 'single-result.php';
				} else {
					do_action( 'gmw_search_results_single_item_template', $member, $this->form );
				}
			}

		endwhile;
	}

	/**
	 * Get info-window data.
	 *
	 * @param  object $member member object.
	 *
	 * @return [type]           [description]
	 */
	public function get_info_window_args( $member ) {

		if ( isset( $this->form['info_window']['image'] ) && empty( $this->form['info_window']['image']['enabled'] ) ) {

			$avatar = false;

		} else {

			$args = array(
				'object_type'  => 'user',
				'object_id'    => $member->ID,
				'width'        => ! empty( $this->form['info_window']['image']['width'] ) ? $this->form['info_window']['image']['width'] : '150px',
				'height'       => ! empty( $this->form['info_window']['image']['height'] ) ? $this->form['info_window']['image']['height'] : 'auto',
				'show_grav'    => isset( $this->form['info_window']['image']['show_grav'] ) ? $this->form['info_window']['image']['show_grav'] : true,
				'show_default' => isset( $this->form['info_window']['image']['show_default'] ) ? $this->form['info_window']['image']['show_default'] : true,
				'permalink'    => false,
				'wrapper'      => false,
				'where'        => 'info_window',
			);

			$avatar = gmw_get_bp_avatar( $args, $member, $this->form );
		}

		return array(
			'prefix' => $this->prefix,
			'type'   => ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard',
			'image'  => $avatar,
			'url'    => function_exists( 'bp_members_get_user_url' ) ? bp_members_get_user_url( $member->ID ) : bp_core_get_user_domain( $member->ID ),
			'title'  => $member->display_name,
		);
	}
}

/**
 * Members Locator search query class
 *
 * @author Eyal Fitoussi
 */
class GMW_Members_Locator_Form extends GMW_Form {

	/**
	 * Inherit search queries from Trait.
	 */
	use GMW_Members_Locator_Form_Trait;

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
}
