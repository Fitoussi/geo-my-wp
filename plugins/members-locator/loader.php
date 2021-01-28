<?php
/**
 * GEO my WP - Members Locator Loader.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'GMW_Addon' ) ) {
	return;
}

/**
 * Members Locator add-on class.
 */
class GMW_Members_Locator_Addon extends GMW_Addon {

	/**
	 * Slug.
	 *
	 * @var string
	 */
	public $slug = 'members_locator';

	/**
	 * Title.
	 *
	 * @var string
	 */
	public $name = 'Members Locator';

	/**
	 * Prefix.
	 *
	 * @var string
	 */
	public $prefix = 'fl';

	/**
	 * Version.
	 *
	 * @var [type]
	 */
	public $version = GMW_VERSION;

	/**
	 * Description
	 *
	 * @var string
	 */
	public $description = 'Geotag Buddypress members and create proximity form to search and find BuddyPress members location based.';

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $object = 'bp_member';

	/**
	 * Database object type.
	 *
	 * @var string
	 */
	public $object_type = 'user';

	/**
	 * DB table prefix. We use the base prefix table to save users across all subsites in multisite installation.
	 *
	 * @var boolean
	 */
	public $global_db = true;

	/**
	 * Templates folder name.
	 *
	 * @var string
	 */
	public $templates_folder = 'members-locator';

	/**
	 * Path.
	 *
	 * @var [type]
	 */
	public $full_path = __FILE__;

	/**
	 * Core add-on.
	 *
	 * @var boolean
	 */
	public $is_core = true;

	/**
	 * Form button.
	 *
	 * @return [type] [description]
	 */
	public function form_buttons() {
		return array(
			'slug'     => 'members_locator',
			'name'     => 'BP Members Locator',
			'prefix'   => 'fl',
			'priority' => 10,
		);
	}

	/**
	 * Instance.
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Create new instance.
	 *
	 * @return $instance
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * [__construct description]
	 */
	public function __construct() {

		// When multisite enabled, and buddypress is multisite activated,
		// the blog_id we will be using will be buddypress's root blog.
		// Otherwise, members will be saved per blog.
		if ( is_multisite() && function_exists( 'bp_is_network_activated' ) && bp_is_network_activated() ) {
			$this->locations_blog_id = BP_ROOT_BLOG;
		}

		parent::__construct();
	}

	/**
	 * Required plugins.
	 */
	public function required() {
		return array(
			'plugins' => array(
				array(
					'function' => 'BuddyPress',
					'notice'   => __( 'Members Locator add-on requires BuddyPress plugin version 2.8 or higher.', 'geo-my-wp' ),
				),
			),
		);
	}

	/**
	 * Initiate the plugin.
	 */
	public function pre_init() {

		parent::pre_init();

		add_action( 'bp_init', array( $this, 'bp_init_functions' ) );

		if ( IS_ADMIN ) {
			include_once 'includes/admin/class-gmw-members-locator-form-editor.php';
		}

		include_once 'includes/gmw-members-locator-functions.php';
		include_once 'includes/class-gmw-members-locator-location-tab.php';
		include_once 'includes/gmw-members-locator-actions.php';
		include_once 'includes/gmw-members-locator-activity.php';
		include_once 'includes/gmw-members-locator-template-functions.php';
		include_once 'includes/class-gmw-members-locator-form.php';
		include_once 'includes/class-gmw-member-location-form.php';

		// init the location tab.
		add_action( 'bp_setup_nav', array( 'GMW_Members_Locator_Addon', 'init_location_tab' ), 20 );

		// load single member location.
		if ( gmw_is_addon_active( 'single_location' ) ) {

			if ( IS_ADMIN ) {

				/**
				 * Add post object to objects dropdown in single location widget.
				 *
				 * @param  array $args arguments.
				 */
				function gmw_fl_single_location_widget_object( $args ) {

					$args['bp_member'] = __( 'Buddypress Member', 'geo-my-wp' );

					return $args;
				}
				add_filter( 'gmw_single_location_widget_objects', 'gmw_fl_single_location_widget_object', 10 );

			}

			if ( ! IS_ADMIN || defined( 'DOING_AJAX' ) ) {
				include_once 'includes/class-gmw-single-bp-member-location.php';
			}
		}
	}

	/**
	 * Custom functions on bp_init
	 */
	public function bp_init_functions() {

		// Handle members cache.
		if ( GMW()->internal_cache ) {

			// clear internal cache when friendship status changes.
			foreach ( array( 'post_delete', 'accepted', 'requested', 'rejected', 'withdrawn' ) as $action ) {
				add_action( 'friends_friendship_' . $action, array( $this, 'flush_user_cache' ) );
			}

			// // clear internal cache when updating settings and profile fields
			add_action( 'xprofile_data_after_save', array( $this, 'flush_user_cache' ) );
			add_action( 'xprofile_data_after_delete', array( $this, 'flush_user_cache' ) );

			// clear internal cache when changing privacy ( BuddyPress Profile Visibility Manager plugin ).
			if ( bp_is_settings_component() && is_user_logged_in() && ! empty( $_POST['bppv_save_submit'] ) ) { // WPCS: CSRF ok.
				$this->flush_user_cache();
			}
		}
	}
	/**
	 * Clear internal cache.
	 */
	public function flush_user_cache() {
		GMW_Cache_Helper::flush_cache_by_object( 'user' );
	}

	/**
	 * Init the location tab.
	 */
	public static function init_location_tab() {
		$location_tab = new GMW_Members_Locator_Location_Tab();
	}
}
GMW_Addon::register( 'GMW_Members_Locator_Addon' );
