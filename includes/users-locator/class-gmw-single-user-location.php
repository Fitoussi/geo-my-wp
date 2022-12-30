<?php
/**
 * GMW Users Locator - Single user Location.
 *
 * @package gmw-wordpress-users-locator
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Single_Location' ) ) {
	return;
}

/**
 * GMW_Single_Group_Location Class
 *
 * Display different location elements of a group.
 *
 * @author Eyal Fitoussi
 *
 * @since 2.0
 */
class GMW_Single_User_Location extends GMW_Single_Location {

	/**
	 * Extends the default shortcode atts
	 *
	 * @var array
	 *
	 * Public $args
	 */
	protected $args = array(
		'elements'            => 'name,address,map,distance,directions_link',
		'object'              => 'user',
		'prefix'              => 'ul',
		'item_info_window'    => 'name,address,distance',
		'show_in_single_post' => 0,
		'no_location_message' => '',
	);

	/**
	 * [__construct description]
	 *
	 * @param array $atts shortcode attriubtes.
	 */
	public function __construct( $atts = array() ) {

		$this->args['no_location_message'] = __( 'The user has not added a location yet.', 'gmw-wp-users-locator' );

		if ( is_single() && empty( $atts['show_in_single_post'] ) ) {
			$atts['no_location_message'] = false;
		}

		parent::__construct( $atts );
	}

	/**
	 * Try getting use ID when missing.
	 *
	 * @return [type] [description]
	 */
	public function get_object_id() {
		return gmw_try_get_user_id();
	}

	/**
	 * Get user data.
	 *
	 * This is needed in order to get the name and permalink.
	 *
	 * @return [type] [description]
	 */
	public function get_object_data() {

		$object = get_userdata( $this->args['object_id'] );

		return ! empty( $object->data ) ? $object->data : false;
	}

	/**
	 * Get user's name.
	 *
	 * @param object $location location object.
	 *
	 * @return [type] [description]
	 */
	public function name( $location ) {

		$user = $this->object_data;

		return apply_filters( 'gmw_sl_user_name', '<div class="gmw-sl-title-wrapper"><h3 class="gmw-sl-title user-name gmw-sl-element">' . esc_attr( $user->display_name ) . '</h3></div>', $location, $this->args, $this->user_position, $this );

		//return apply_filters( 'gmw_sl_user_name', '<div class="gmw-sl-title-wrapper"><h3 class="gmw-sl-title user-name gmw-sl-element"><a href="' . esc_url( gmw_get_search_results_user_permalink( $user ) ) . '">' . esc_attr( $user->display_name ) . '</a></h3></div>', $location, $this->args, $this->user_position, $this );
	}

	/**
	 * Use title for the name.
	 *
	 * @param object $location location object.
	 *
	 * @return [type] [description]
	 */
	public function title( $location ) {
		return $this->name( $location );
	}
}

/**
 * GMW Single User Location shortcode
 *
 * @version 2.0
 *
 * @param array $atts shortcode attriubtes.
 *
 * @author Eyal Fitoussi
 */
function gmw_ul_single_user_location_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	$atts['object'] = 'user';

	if ( isset( $atts['user_id'] ) ) {
		$atts['object_id'] = $atts['user_id'];
	}

	$user_location = new GMW_Single_User_Location( $atts );

	return $user_location->output();
}
add_shortcode( 'gmw_user_location', 'gmw_ul_single_user_location_shortcode' );
