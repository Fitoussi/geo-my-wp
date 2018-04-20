<?php
// Exit if accessed directly
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
	 * addon
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
	 * Run the form class
	 *
	 * @param array $attr [description]
	 */
	public function __construct( $attr = array() ) {

		parent::__construct( $attr );

		// add custom tab panels
		add_action( 'gmw_lf_content_end', array( $this, 'create_tabs_panels' ) );
	}

	/**
	 * Additional custom form tabs
	 *
	 * @return array
	 */
	public function form_tabs() {

		$tabs = parent::form_tabs();

		return apply_filters( 'gmw_member_location_form_tabs', $tabs, $this->args );
	}

	/**
	 * Additional form fields
	 *
	 * @return array
	 */
	public function form_fields() {

		// retreive parent fields
		$fields = parent::form_fields();

		return apply_filters( 'gmw_member_location_tab_fields', $fields, $this->args );
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
