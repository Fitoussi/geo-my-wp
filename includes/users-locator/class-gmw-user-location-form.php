<?php
/**
 * GEO my WP Users Locator - location form class.
 *
 * @package gmw-wp-users-locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_FL_Location_Form Class extends GMW_Location_Form class
 *
 * Location form for Location tab of BuddyPress member's profile page.
 *
 * @since 3.0
 */
class GMW_User_Location_Form extends GMW_Location_Form {

	/**
	 * Addon slug
	 *
	 * @var string
	 */
	public $slug = 'users_locator';

	/**
	 * Object type
	 *
	 * @var string
	 */
	public $object_type = 'user';

	/**
	 * Run the form class
	 *
	 * @param array $attr shortcode attributes.
	 */
	public function __construct( $attr = array() ) {

		parent::__construct( $attr );

		// add custom tab panels.
		add_action( 'gmw_lf_content_end', array( $this, 'create_tabs_panels' ) );
	}

	/**
	 * Generate custom tabs panels
	 *
	 * @return void
	 */
	public function create_tabs_panels() {
		do_action( 'gmw_user_location_tabs_panels', $this );
	}
}

/**
 * Generate the user location form
 *
 * @param  array $args function arguments.
 *
 * @return [type]       [description]
 */
function gmw_ul_user_location_form( $args = array() ) {

	if ( isset( $args['user_id'] ) ) {
		$args['object_id'] = $args['user_id'];
	}

	// default args.
	$defaults = array(
		'object_id'             => 0,
		'exclude_fields_groups' => gmw_get_option( 'users_locator', 'location_form_exclude_fields_groups', array() ),
		'exclude_fields'        => gmw_get_option( 'users_locator', 'location_form_exclude_fields', array() ),
		'form_template'         => gmw_get_option( 'users_locator', 'location_form_template', 'location-form-tabs-top' ),
		'submit_enabled'        => 1,
		'stand_alone'           => 1,
		'ajax_enabled'          => 1,
		'auto_confirm'          => 1,
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_user_location_form_args', $args );

	if ( ! absint( $args['object_id'] ) ) {

		if ( get_current_user_id() ) {

			$args['object_id'] = get_current_user_id();

		} else {

			$args['object_id'] = 0;
		}
	}

	require_once 'class-gmw-user-location-form.php';

	if ( ! class_exists( 'GMW_User_Location_Form' ) ) {
		return;
	}

	// generate new location form.
	$location_form = new GMW_User_Location_Form( $args );

	// display the location form.
	$location_form->display_form();
}

/**
 * Generate the user location form using shortcode
 *
 * @param  array $atts shortcode attributes.
 *
 * @return [type]       [description]
 */
function gmw_ul_user_location_form_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	ob_start();

	gmw_ul_user_location_form( $atts );

	$content = ob_get_clean();

	return $content;
}
add_shortcode( 'gmw_user_location_form', 'gmw_ul_user_location_form_shortcode' );
