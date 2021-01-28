<?php
/**
 * GEO my WP - Members Locator BP Component.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// load Members Locator component.
if ( class_exists( 'GMW_Locations_Component' ) ) {
	return;
}

/**
 * Members Locator component
 *
 * @author Eyal Fitoussi
 */
class GMW_Members_Locator_Component extends BP_Component {

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name = 'Locations';

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'locations';

	/**
	 * Constructor method
	 *
	 * @package GMW
	 */
	public function __construct() {

		parent::start(
			'gmw_locations',
			__( 'Locations', 'geo-my-wp' ),
			GMW_FL_PATH
		);
	}

	/**
	 * Setup globals.
	 *
	 * @param  array $args arguments.
	 */
	public function setup_globals( $args = array() ) {

		// Set up the $globals array to be passed along to parent::setup_globals().
		$globals = array(
			'slug'                  => $this->slug,
			'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : $this->slug,
			'has_directory'         => false,
			'notification_callback' => false,
			'search_string'         => __( 'Search Location...', 'geo-my-wp' ),
		);

		parent::setup_globals( $globals );
	}

	/**
	 * Setup Location tab
	 *
	 * @param  array $main_nav [description].
	 *
	 * @param  array $sub_nav  [description].
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		if ( gmw_is_addon_active( 'members_locator' ) ) {

			include GMW_FL_PATH . '/includes/gmw-members-locator-location-screen.php';

			// Add 'location' to the main navigation.
			$main_nav = apply_filters(
				'gmw_fl_setup_nav',
				array(
					'name'                => __( 'Location', 'geo-my-wp' ),
					'slug'                => $this->slug,
					'position'            => 60,
					'screen_function'     => 'gmw_fl_screen_display_location',
					'default_subnav_slug' => $this->slug,
				),
				buddypress()->displayed_user
			);
		}

		parent::setup_nav( $main_nav );
	}

	/**
	 * Location item in admin bar menu
	 *
	 * @param  array $wp_admin_nav [description].
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {

		if ( gmw_is_addon_active( 'members_locator' ) && is_user_logged_in() ) {

			// Prevent debug notices.
			$wp_admin_nav = array();

			// Setup the logged in user variables.
			$location_link = trailingslashit( bp_loggedin_user_domain() . $this->slug );

			// Add location tab.
			$wp_admin_nav[] = apply_filters(
				'gmw_fl_setup_admin_bar',
				array(
					'parent' => 'my-account-buddypress',
					'id'     => 'my-account-' . $this->slug,
					'title'  => __( 'Location', 'geo-my-wp' ),
					'href'   => $location_link,
				)
			);

			// add submenu tab.
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->slug,
				'id'     => 'my-account-' . $this->slug . '-gmw-location',
				'title'  => __( 'Update Location', 'geo-my-wp' ),
				'href'   => $location_link,
			);
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}
}
