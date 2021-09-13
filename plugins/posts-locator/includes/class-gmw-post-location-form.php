<?php
/**
 * GMW Post Location form class.
 *
 * @package gmw-my-wp.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Location_Form' ) ) {
	return;
}

/**
 * GMW_Posts_Location_Form Class extends GMW_Location_Form class
 *
 * Location form for Post types in "Edit post" page.
 *
 * @since 3.0
 */
class GMW_Post_Location_Form extends GMW_Location_Form {

	/**
	 * Addon
	 *
	 * @var string
	 */
	public $slug = 'posts_locator';

	/**
	 * Object type
	 *
	 * @var string
	 */
	public $object_type = 'post';

	/**
	 * Enable the contact fields by default.
	 *
	 * @var boolean
	 */
	public $disable_additional_fields = false;

	/**
	 * Run the form class.
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
		do_action( 'gmw_post_location_tabs_panels', $this );
	}
}
