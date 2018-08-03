<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Form' ) ) {
	return;
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
	 * [get_info_window_args description]
	 *
	 * @param  [type] $location [description]
	 * @return [type]           [description]
	 */
	public function get_info_window_args( $member ) {

		if ( isset( $this->form['info_window']['image'] ) ) {
			if ( $this->form['info_window']['image']['enabled'] == '' ) {
				$avatar = false;
			} else {
				$avatar = bp_core_fetch_avatar(
					array(
						'item_id' => $member->ID,
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

	public function before_search_results() {
		echo '<div id="buddypress">';
	}

	public function after_search_results() {
		echo '</div>';
	}

	/**
	 * Inclued users Id returned from xprofile fields.
	 *
	 * @param  [type] $sql [description]
	 * @return [type]      [description]
	 *
	 * @since 3.0
	 */
	public function include_xprofile_users_id( $sql ) {

		$column = in_array( $this->form['query_args']['type'], array( 'active', 'newest', 'popular', 'online' ) ) ? 'user_id' : 'ID';

		$users_id       = esc_sql( implode( ',', $this->xp_users_id ) );
		$sql['where'][] = " u.{$column} IN ( {$users_id} ) ";

		return $sql;
	}

	/**
	 * Inclued users Id returned from locations query.
	 *
	 * $this->objects_id is returned from pre_get_locations_data function.
	 *
	 * @param  [type] $sql [description]
	 * @return [type]      [description]
	 *
	 * @since 3.0
	 */
	public function include_locations_users_id( $sql ) {

		$column = in_array( $this->form['query_args']['type'], array( 'active', 'newest', 'popular', 'online' ) ) ? 'user_id' : 'ID';

		$users_id       = esc_sql( implode( ',', $this->objects_id ) );
		$sql['where'][] = " u.{$column} IN ( {$users_id} ) ";

		return $sql;
	}

	/**
	 * Order by distance
	 *
	 * $this->objects_id is returned from pre_get_locations_data function.
	 *
	 * @param  [type] $clauses [description]
	 * @param  [type] $vars    [description]
	 * @return [type]          [description]
	 */
	function order_results_by_distance( $clauses, $vars ) {

		if ( 'distance' == $vars->query_vars['type'] ) {

			$users_id           = esc_sql( implode( ',', $this->objects_id ) );
			$clauses['orderby'] = " ORDER BY FIELD( id, {$users_id} )";
		}

		return $clauses;
	}

	/**
	 * Query results
	 *
	 * @return [type] [description]
	 */
	public function search_query() {

		// look for xprofile values in URL
		if ( isset( $this->form['form_values']['xf'] ) && array_filter( $this->form['form_values']['xf'] ) ) {

			$fields_values = $this->form['form_values']['xf'];

			// otherwise, can do something custom with xprofile fields
			// by passing array of array( fields => value ).
		} else {
			$fields_values = apply_filters( 'gmw_fl_xprofile_fields_query_default_values', array(), $this->form );
		}

		$this->xp_users_id = array();

		/**
		 *
		 * Query xprofile fields
		 *
		 * if xprofile query returns -1, it means no users were
		 *
		 * found and we can skip the rest and return no results.
		 */
		if ( apply_filters( 'gmw_fl_xprofile_query_enabled', true, $this->form ) && array_filter( $fields_values ) && ( $this->xp_users_id = gmw_query_xprofile_fields( $fields_values, $this->form ) ) == -1 ) {

			return false;
		}

		// can show members without locations
		$show_non_located_members = apply_filters( 'gmw_show_members_without_locations', false, $this->form );

		/**
		 * Get users locations data.
		 *
		 * This function returns an array of locations data ( $this->locations_data ) and an array
		 *
		 * of users ID ( $this->objects_id ), that is ordered by the distance.
		 *
		 * We use this array to include in the members query and in the orderby caluse to order
		 *
		 * the results by distance.
		 *
		 * We also pass the users ID returned from the xprofile fields query into the function.
		 *
		 * If no users returned from the function, we can abort and skip the rest.
		 */
		$this->pre_get_locations_data( $this->xp_users_id );

		$address_ok = ! empty( $this->form['org_address'] ) ? true : false;
		$objects_ok = ! empty( $this->objects_id ) ? true : false;

		// abort based on location found and non_located_members status
		if ( ( $address_ok && ! $objects_ok ) || ( ! $address_ok && ! $show_non_located_members && ! $objects_ok ) ) {
			return false;
		}

		// get query args for cache
		if ( $this->form['page_load_action'] ) {

			$gmw_query_args = $this->form['page_load_results'];

		} elseif ( $this->form['submitted'] ) {

			$gmw_query_args = $this->form['form_values'];
		}

		$gmw_query_args['non_located_members'] = $show_non_located_members;

		// query args
		$this->form['query_args'] = apply_filters(
			'gmw_fl_search_query_args', array(
				'type'     => 'distance',
				'per_page' => $this->form['get_per_page'],
				'page'     => $this->form['paged'],
				'gmw_args' => $gmw_query_args,
			), $this->form, $this
		);

		// modify the form values before the query takes place
		$this->form = apply_filters( 'gmw_fl_form_before_members_query', $this->form, $this );

		$internal_cache = GMW()->internal_cache;

		if ( $internal_cache ) {

			// prepare for cache
			$hash            = md5( json_encode( $this->form['query_args'] ) );
			$query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_user_query' );
		}

		global $members_template;

		if ( ! $internal_cache || false === ( $members_template = get_transient( $query_args_hash ) ) ) {
			//if ( 1 == 1 ){
			// print_r( 'Members query done' );
			// if address entered and showing only located members
			if ( ( $address_ok && $objects_ok ) || ( ! $address_ok && ! $show_non_located_members ) ) {

				// include users ID
				add_filter( 'bp_user_query_uid_clauses', array( $this, 'include_locations_users_id' ), 20 );

				// order results by distance
				add_filter( 'bp_user_query_uid_clauses', array( $this, 'order_results_by_distance' ), 21, 2 );

				// otherwise, if showing also non located members
				// we need to pass the users Id from xprofile fields
				// into the query directly.
			} elseif ( ! empty( $this->xp_users_id ) ) {

				// include users ID
				add_filter( 'bp_user_query_uid_clauses', array( $this, 'include_xprofile_users_id' ), 20 );
			}

			// query members
			$results = bp_has_members( $this->form['query_args'] ) ? true : false;

			// include users ID
			remove_filter( 'bp_user_query_uid_clauses', array( $this, 'include_locations_users_id' ), 20 );
			remove_filter( 'bp_user_query_uid_clauses', array( $this, 'order_results_by_distance' ), 21, 2 );
			remove_filter( 'bp_user_query_uid_clauses', array( $this, 'include_xprofile_users_id' ), 20 );

			// set new query in transient
			if ( $internal_cache ) {
				set_transient( $query_args_hash, $members_template, GMW()->internal_cache_expiration );
			}
		}

		// Modify the form after the search query
		$this->form = apply_filters( 'gmw_fl_form_after_members_query', $this->form, $this );

		$members_template = $this->query = apply_filters( 'gmw_fl_members_before_members_loop', $members_template, $this->form, $this );

		$this->form['results_count'] = count( $members_template->members );
		$this->form['total_results'] = $members_template->total_member_count;
		$this->form['max_pages']     = $this->form['total_results'] / $this->form['get_per_page'];

		$temp_array = array();

		foreach ( $members_template->members as $member ) {
			$temp_array[] = parent::the_location( $member->id, $member );
		}

		$this->form['results'] = $members_template->members = $temp_array;

		return $this->form['results'];
	}

	/**
	 * Merge member object with location object
	 *
	 * @param  [type] $member [description]
	 * @return [type]         [description]
	 */
	public function the_member( $member ) {
		return $this->the_location( $member->id, $member );
	}
}
