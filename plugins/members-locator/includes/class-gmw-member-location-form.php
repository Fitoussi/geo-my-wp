<?php
/**
 * GEO my WP Member Location Form Class.
 *
 * Generates the proximity search forms.
 *
 * This class should be extended for different object types.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_FL_Location_Form Class extends GMW_Location_Form class
 *
 * Location form for Location tab of BuddyPress member's profile page.
 *
 * @since 3.0
 */
class GMW_Member_Location_Form extends GMW_Location_Form {

	/**
	 * Addon slug
	 *
	 * @var string
	 */
	public $slug = 'members_locator';

	/**
	 * [$object_type description]
	 *
	 * @var string
	 */
	public $object_type = 'user';

	/**
	 * Object slug to be used with hooks.
	 *
	 * @var string
	 */
	public $object_slug = 'member';

	/**
	 * [__construct description]
	 *
	 * @param array $attr [description].
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
		do_action( 'gmw_member_location_tabs_panels', $this );
	}
}
