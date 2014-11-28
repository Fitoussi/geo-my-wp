<?php
/**
 * GMW deprecated function - Display radius distance.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_by_radius( $gmw, $post ) {
	_deprecated_function( 'gmw_pt_by_radius', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $post, $gmw );
}

function gmw_pt_thumbnail( $gmw, $post ) {
	if ( !isset( $gmw['search_results']['featured_image']['use'] ) || !has_post_thumbnail( $post-> ID ) )
		return;
	_deprecated_function( 'gmw_pt_thumbnail', '2.5', 'the_post_thumbnail' );
	the_post_thumbnail( array( $gmw['search_results']['featured_image']['width'], $gmw['search_results']['featured_image']['height'] ) );
}

/**
 * GMW deprecated function - Additional information.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_additional_info( $gmw, $post, $tag ) {
	_deprecated_function( 'gmw_pt_additional_info', '2.5', 'gmw_additional_info' );
	gmw_additional_info( $post, $gmw, $gmw['search_results']['additional_info'], $gmw['labels']['search_results']['contact_info'], $tag );
}

/**
 * GMW deprecated function - Excerpt from content.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_excerpt( $gmw, $post ) {
	_deprecated_function( 'gmw_pt_excerpt', '2.5', 'gmw_excerpt' );
	if ( !isset( $gmw['search_results']['excerpt']['use'] ) )
		return;
	gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'] );
}

/**
 * GMW deprecated function - results message.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_within( $gmw, $sm, $om, $rm, $wm, $fm, $nm ) {
	_deprecated_function( 'gmw_pt_within', '2.5', 'gmw_results_message' );
	gmw_results_message( $gmw, false );
}

/**
 * GMW deprecated function - get directions link
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_directions( $gmw, $post, $title ) {
	if ( !isset( $gmw['search_results']['get_directions'] ) )
		return;
	_deprecated_function( 'gmw_pt_directions', '2.5', 'gmw_directions_link' );
	gmw_directions_link( $post, $gmw, $title );
}

/**
 * GMW deprecated function - get driving distance
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_driving_distance( $gmw, $post, $class, $title ) {
	if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
		return;
	_deprecated_function( 'gmw_pt_driving_distance', '2.5', 'gmw_driving_distance' );
	gmw_driving_distance( $post, $gmw, $title );	
}

/**
 * GMW deprecated function - pagination
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_paginations( $gmw ) {
	_deprecated_function( 'gmw_pt_paginations', '2.5', 'gmw_pagination' );
	gmw_pagination( $gmw, 'paged', $gmw['max_pages'] );
}

/**
 * GMW deprecated function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_per_page_dropdown( $gmw, $class ) {
	_deprecated_function( 'gmw_pt_per_page_dropdown', '2.5', 'gmw_per_page' );
	gmw_per_page( $gmw, $gmw['total_results'], 'paged' );
}

/**
 * GMW deprecated function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_per_page_dropdown( $gmw, $class ) {
	_deprecated_function( 'gmw_fl_per_page_dropdown', '2.5', 'gmw_per_page' );
	global $members_template;
	gmw_per_page( $gmw, $members_template->total_member_count, 'upage' );
}
	
/**
 * GMW deprecated function - Display user's full address
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_member_address( $gmw ) {
	_deprecated_function( 'gmw_fl_member_address', '2.5', 'gmw_location_address' );
	global $members_template;
	gmw_location_address( $members_template->member, $gmw );
}

/**
 * GMW deprecated function - Display distance from user
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_by_radius( $gmw ) {
	_deprecated_function( 'gmw_fl_by_radius', '2.5', 'gmw_distance_to_location' );
	global $members_template;
	gmw_distance_to_location( $members_template->member, $gmw );
}

/**
 * GMW deprecated function - directions link to user
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_directions_link( $gmw, $title ) {
	if ( !isset( $gmw['search_results']['get_directions'] ) )
		return;
	global $members_template;
	_deprecated_function( 'gmw_fl_directions_link', '2.5', 'gmw_directions_link' );
	gmw_directions_link( $members_template->member, $gmw, $title );
}

/**
 * GMW deprecated function - results message.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_wihtin_message( $gmw ) {
	global $members_template;
	_deprecated_function( 'gmw_fl_within_message', '2.5', 'gmw_results_message' );
	gmw_results_message( $gmw, false );
}

function gmw_fl_driving_distance( $gmw, $member ) {
	if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
		return;
	global $members_template;
	_deprecated_function( 'gmw_fl_driving_distance', '2.5', 'gmw_driving_distance' );
	gmw_driving_distance( $members_template->member, $gmw, false);
}

/**
 * GMW deprecated function - display members count
 * @para  $gmw
 * @param $gmw_options
 */
function gmw_fl_member_count($gmw) {
	global $members_template;
	_deprecated_function( 'gmw_fl_member_count', '2.5', 'member->member_count' );
	echo $members_template->member->member_count;
}

/**
 * GMW deprecated function - no members found
 */
function gmw_fl_no_members( $gmw ) {
	_deprecated_function( 'gmw_fl_no_members', '2.5', 'gmw_no_results_found' );
	gmw_no_results_found( $gmw, $gmw['labels']['search_results']['fl_no_results'] );
}

/*
 *GMW Users Geolocation deprecated functions 
 */
	
/**
 * GMW deprecated function - pagination
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_paginations( $gmw ) {
	_deprecated_function( 'gmw_ug_paginations', '2.5', 'gmw_pagination' );
	gmw_pagination( $gmw, 'paged', $gmw['max_pages'] );
}

/**
 * GMW UG deprecated function - Display Radius distance
 * @since 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_by_radius( $gmw, $user ) {
	_deprecated_function( 'gmw_ug_by_radius', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $user, $gmw );
}

/**
 * GMW UG deprectated function - Get directions.
 * @since 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_directions( $gmw, $user, $title ) {
	if ( !isset( $gmw['search_results']['get_directions'] ) )
		return;

	if ( empty( $title ) )
		$title = $gmw['labels']['search_results']['directions'];

	_deprecated_function( 'gmw_ug_directions', '2.5', 'gmw_directions_link' );
	gmw_directions_link( $user, $gmw, $title );
}

/**
 * GMW GL deprecated function - driving distance
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_driving_distance( $gmw, $user ) {
	if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
		return;

	_deprecated_function( 'gmw_ug_driving_distance', '2.5', 'gmw_driving_distance' );
	gmw_driving_distance( $user, $gmw, false);
}

/**
 * GMW GL deprecated function - display within distance message
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_within( $gmw, $sm, $om, $rm , $wm, $fm, $nm ) {
	_deprecated_function( 'gmw_ug_within', '2.5', 'gmw_results_message' );
	gmw_results_message( $gmw, false );
}

/**
 * GMW deprecated function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_per_page( $gmw, $class ) {
	_deprecated_function( 'gmw_ug_per_page_dropdown', '2.5', 'gmw_per_page' );
	gmw_per_page( $gmw, $gmw['total_user_count'], 'paged' );
}

/**
 * GMW deprecated function - avatar
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_ug_avatar( $gmw, $user ) {
	_deprecated_function( 'gmw_ug_avatar', '2.5', 'get_avatar' );
	echo get_avatar( $user->ID, $gmw['search_results']['avatar']['width'] );
}

/*
 *groups locator 
 */
/**
 * GMW GL function - Display group's full address
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_group_address( $gmw ) {
	global $groups_template;
	_deprecated_function( 'gmw_gl_group_address', '2.5', 'gmw_location_address' );
	gmw_location_address( $groups_template->group, $gmw );
}

/**
 * GMW GL function - Display Radius distance
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_by_radius($gmw) {
	_deprecated_function( 'gmw_gl_by_radius', '2.5', 'gmw_distance_to_location' );
	global $groups_template;
	gmw_distance_to_location( $groups_template->group, $gmw );
}

/**
 * GMW GL function - "Get directions" link
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_get_directions($gmw) {
	if ( !isset( $gmw['search_results']['get_directions'] ) )
		return;

	global $groups_template;
	if ( empty( $title ) )
		$title = 'Get directions';

	_deprecated_function( 'gmw_gl_get_directions', '2.5', 'gmw_directions_link' );
	gmw_directions_link( $groups_template->group, $gmw, $title );
}

/**
 * GMW GL function - display within distance message
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_within_message( $gmw ) {
	_deprecated_function( 'gmw_gl_within_message', '2.5', 'gmw_results_message' );
	gmw_results_message( $gmw, false );
}

/**
 * Deprecated  - for older versions
 */
function gmgl_distance( $group, $gmw ) {
	_deprecated_function( 'gmgl_distance', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $group, $gmw );
}

/**
 * Deprecated - for older versions
 */
function gmgl_get_directions( $group, $gmw, $title ) {
	_deprecated_function( 'gmgl_get_directions', '2.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link(  $group, $gmw, $title );
}

function gmw_gl_driving_distance( $gmw, $member ) {
	if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
		return;
	global $groups_template;
	_deprecated_function( 'gmw_gl_driving_distance', '2.5', 'gmw_driving_distance' );
	gmw_driving_distance( $groups_template->group, $gmw, false);
}

/**
 * GMW deprecated function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_gl_per_page_dropdown( $gmw, $class ) {
	_deprecated_function( 'gmw_gl_per_page_dropdown', '2.5', 'gmw_per_page' );
	global $groups_template;
	gmw_per_page( $gmw, $groups_template->total_group_count, 'grpage' );
}

function gmw_gl_group_number( $gmw ) {
	global $groups_template;
	_deprecated_function( 'gmw_gl_group_number', '2.5', '$groups_template->group->group_count' );
	return $groups_template->group->group_count;
}

/**
 * premium settings
 */
/**
 * Deprecated - for older versions
 */
function gmw_ps_pt_excerpt( $info, $gmw, $count ) {
	_deprecated_function( 'gmw_ps_pt_excerpt', '1.5', 'gmw_get_excerpt' );
	echo gmw_get_excerpt( $info, $gmw, $info->post_content, $count );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_pt_get_address( $post, $gmw ) {
	_deprecated_function( 'gmw_ps_pt_get_address', '1.5', 'gmw_get_location_address' );
	echo gmw_get_location_address( $post, $gmw );
}

/**
 * Deprecated  - for older versions
 */
function gmw_ps_pt_distance( $post, $gmw ) {
	_deprecated_function( 'gmw_ps_pt_distance', '1.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $post, $gmw );
}

/**
 * Deprecated  - for older versions
 */
function gmw_ps_pt_additional_info( $post, $gmw ) {
	_deprecated_function( 'gmw_ps_pt_additional_info', '1.5', 'gmw_additional_info' );
	gmw_additional_info( $post, $gmw, $gmw['info_window']['additional_info'], $gmw['labels']['info_window'], 'ul' );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_pt_get_directions( $post, $gmw, $title ) {
	_deprecated_function( 'gmw_ps_pt_get_directions', '1.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link(  $post, $gmw, $title );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_fl_iw_member_address( $member, $gmw ) {
	_deprecated_function( 'gmw_ps_fl_iw_member_address', '1.5', 'gmw_get_location_address' );
	echo gmw_get_location_address( $member, $gmw );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_fl_distance( $member, $gmw ) {
	_deprecated_function( 'gmw_ps_fl_distance', '1.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $member, $gmw );
}

/**
 * Deprecated - for older versions
 */
function gmw_ps_fl_get_directions( $member, $gmw, $title ) {
	_deprecated_function( 'gmw_ps_fl_get_directions', '1.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link(  $member, $gmw, $title );
}

/**
 * Global Maps
 *
 */
/**
 * Deprecated - for older versions
 */
function gmpt_excerpt( $info, $gmw, $count ) {
	_deprecated_function( 'gmpt_excerpt', '2.5', 'gmw_get_excerpt' );
	echo gmw_get_excerpt( $info, $gmw, $info->post_content, $count );
}

/**
 * Deprecated - for older versions
 */
function gmpt_address( $post, $gmw ) {
	_deprecated_function( 'gmpt_address', '2.5', 'gmw_get_location_address' );
	echo gmw_get_location_address( $post, $gmw );
}

/**
 * Deprecated  - for older versions
 */
function gmpt_distance( $post, $gmw ) {
	_deprecated_function( 'gmpt_distance', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $post, $gmw );
}

/**
 * Deprecated  - for older versions
 */
function gmpt_additional_info( $post, $gmw ) {
	_deprecated_function( 'gmpt_additional_info', '2.5', 'gmw_additional_info' );
	gmw_additional_info( $post, $gmw, $gmw['info_window']['additional_info'], 'info_window', 'div' );
}

/**
 * Deprecated - for older versions
 */
function gmpt_get_directions( $post, $gmw, $title ) {
	_deprecated_function( 'gmpt_get_directions', '2.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link(  $post, $gmw, $title );
}

/**
 * Deprecated - for older versions
 */
function gmfl_distance( $member, $gmw ) {
	_deprecated_function( 'gmfl_distance', '2.5', 'gmw_distance_to_location' );
	gmw_distance_to_location( $member, $gmw );
}

/**
 * Deprecated - for older versions
 */
function gmfl_get_directions( $member, $gmw, $title ) {
	_deprecated_function( 'gmfl_get_directions', '2.5', 'gmw_get_directions_link' );
	echo gmw_get_directions_link( $member, $gmw, $title );
}