<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

	// version.
	public $version = GMW_VERSION;

	// description.
	public $description = 'Geotag Buddypress members and create proximity form to search and find BuddyPress members location based.';

	// object.
	public $object = 'bp_member';

	// database object type.
	public $object_type = 'user';

	// db table prefix. We use the base prefix table to save users across all subsites
	// in multisite installation.
	public $global_db = true;

	// templates folder name.
	public $templates_folder = 'members-locator';

	// path.
	public $full_path = __FILE__;

	// is core add-on.
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

	private static $instance = null;

	/**
	 * Create new instance.
	 *
	 * @return $instance
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

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

		if ( IS_ADMIN ) {
			include( 'includes/admin/class-gmw-members-locator-form-editor.php' );
		}

		include( 'includes/gmw-members-locator-functions.php' );
		include( 'includes/class-gmw-members-locator-location-tab.php' );
		include( 'includes/gmw-members-locator-actions.php' );
		include( 'includes/gmw-members-locator-activity.php' );
		include( 'includes/gmw-members-locator-template-functions.php' );
		include( 'includes/class-gmw-members-locator-form.php' );

		// load single member location.
		if ( gmw_is_addon_active( 'single_location' ) ) {

			if ( IS_ADMIN ) {

				/**
				 * Add post object to objects dropdown in single location widget.
				 *
				 * @param  [type] $args [description]
				 */
				function gmw_fl_single_location_widget_object( $args ) {

					$args['bp_member'] = __( 'Buddypress Member', 'geo-my-wp' );

					return $args;
				}
				add_filter( 'gmw_single_location_widget_objects', 'gmw_fl_single_location_widget_object', 10 );

			}

			if ( ! IS_ADMIN || defined( 'DOING_AJAX' ) ) {
				include( 'includes/class-gmw-single-member-location.php' );
			}
		}
	}
}
GMW_Addon::register( 'GMW_Members_Locator_Addon' );
