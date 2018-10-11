<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Single_Member_Location Class
 *
 * Display different location elements of a post.
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 *
 * @since 2.6.1
 */
class GMW_Single_BP_Member_Location extends GMW_Single_Location {

	/**
	 * Extends the default shortcode atts
	 *
	 * @since 2.6.1
	 * Public $args
	 */
	protected $args = array(
		'elements'            => 'name,address,map,distance,directions_link',
		'object'              => 'member',
		'object_type'         => 'user',
		'prefix'              => 'fl',
		'item_info_window'    => 'name,address,distance',
		'show_in_single_post' => 1,
		'no_location_message' => '',
	);

	/**
	 * [__construct description]
	 * 
	 * @param array $atts [description]
	 */
	public function __construct( $atts = array() ) {

		$this->args['no_location_message'] = __( 'The member has not added a location yet.', 'geo-my-wp' );

		if ( is_single() && empty( $atts['show_in_single_post'] ) ) {
			$atts['no_location_message'] = false;
		}

		parent::__construct( $atts );
	}

	/**
	 * Try to get user ID if missing.
	 * 
	 * @return [type] [description]
	 */
	public function get_object_id() {

		global $members_template;

		// look for BP displayed user ID
		if ( bp_is_user() ) {

			global $bp;

			return $bp->displayed_user->id;

			// look form member ID in members loop
		} elseif ( ! empty( $members_template->member->ID ) ) {

			return $members_template->member->ID;

			// if in single post page look for post author
		} elseif ( is_single() && ! empty( $this->args['show_in_single_post'] ) ) {

			global $post;

			if ( ! empty( $post->post_author ) ) {

				return $post->post_author;

			} else {

				return false;
			}

		} else {

			return false;
		}
	}

	/**
	 * Get member's name.
	 *
	 * @return [type] [description]
	 */
	public function name() {
		return apply_filters( 'gmw_sl_title', '<h3 class="gmw-sl-title member-name gmw-sl-element">' . bp_core_get_userlink( $this->args['object_id'] ) . '</h3>', $this->location_data, $this->args, $this->user_position, $this );
	}

	/**
	 * Use title for the name.
	 *
	 * @return [type] [description]
	 */
	public function title() {
		return $this->name();
	}
}

/**
 * GMW Single member location shortcode
 *
 * @version 2.0
 *
 * @author Eyal Fitoussi
 */
function gmw_single_bp_member_location_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	$atts['object'] = 'bp_member';

	if ( isset( $atts['member_id'] ) ) {

		$atts['object_id'] = $atts['member_id'];

	} elseif ( isset( $atts['user_id'] ) ) {

		$atts['object_id'] = $atts['user_id'];
	}

	$single_member_location = new GMW_Single_BP_Member_Location( $atts );

	return $single_member_location->output();
}
add_shortcode( 'gmw_bp_member_location', 'gmw_single_bp_member_location_shortcode' );
