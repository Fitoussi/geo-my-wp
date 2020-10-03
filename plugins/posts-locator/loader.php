<?php
/**
 * GEO my WP - Posts Locator Loader.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// abort if register add-on class is not found.
if ( ! class_exists( 'GMW_Addon' ) ) {
	return;
}

/**
 * Register Posts locator add-on
 */
class GMW_Posts_Locator_Addon extends GMW_Addon {

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'posts_locator';

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name = 'Posts Locator';

	/**
	 * Prefix
	 *
	 * @var string
	 */
	public $prefix = 'pt';

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version = GMW_VERSION;

	/**
	 * Author
	 *
	 * @var string
	 */
	public $author = 'Eyal Fitoussi';

	/**
	 * Description
	 *
	 * @var string
	 */
	public $description = 'Provides geolocation for WordPress post types.';

	/**
	 * Object
	 *
	 * @var string
	 */
	public $object = 'post';

	/**
	 * Object type
	 *
	 * @var string
	 */
	public $object_type = 'post';

	/**
	 * Prefix
	 *
	 * @var string
	 */
	public $global_db = false;

	/**
	 * Templates folder
	 *
	 * @var string
	 */
	public $templates_folder = 'posts-locator';

	/**
	 * Path
	 *
	 * @var string
	 */
	public $full_path = __FILE__;

	/**
	 * Is core add-on?
	 *
	 * @var string
	 */
	public $is_core = true;

	/**
	 * Docs page.
	 *
	 * @var string
	 */
	public $docs_page = 'https://docs.geomywp.com/category/45-posts-locator';

	/**
	 * Settings groups
	 *
	 * @return [type] [description]
	 */
	public function admin_settings_groups() {
		return array(
			'slug'     => 'post_types_settings',
			'label'    => __( 'Posts Locator', 'geo-my-wp' ),
			'icon'     => 'pinboard',
			'priority' => 7,
		);
	}

	/**
	 * Form button
	 *
	 * @return [type] [description]
	 */
	public function form_buttons() {
		return array(
			'slug'     => 'posts_locator',
			'name'     => 'Posts Locator',
			'prefix'   => 'pt',
			'priority' => 5,
		);
	}

	/**
	 * Instance of Posts Locator.
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Create new instance
	 *
	 * @return [type] [description]
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * [pre_init description]
	 *
	 * @return [type] [description]
	 */
	public function pre_init() {

		parent::pre_init();

		include 'includes/gmw-posts-locator-functions.php';
		include 'includes/gmw-posts-locator-shortcodes.php';

		// include admin files.
		if ( IS_ADMIN ) {

			include_once 'includes/admin/class-gmw-posts-locator-form-editor.php';
			include_once 'includes/admin/class-gmw-posts-locator-admin-settings.php';
			include_once 'includes/admin/class-gmw-posts-locator-screens.php';
		}

		// include template functions.
		include_once 'includes/gmw-posts-locator-search-form-template-functions.php';
		include_once 'includes/gmw-posts-locator-search-results-template-functions.php';
		include_once 'includes/class-gmw-posts-locator-form.php';
		include_once 'includes/class-gmw-post-location-form.php';

		// load single location post.
		if ( gmw_is_addon_active( 'single_location' ) ) {

			if ( IS_ADMIN ) {

				/**
				 * Add post object to objects dropdown in single location widget
				 *
				 * @param  array $args array of args.
				 *
				 * @return array       array of new args.
				 */
				function gmw_pt_single_location_widget_object( $args ) {

					$args['post'] = __( 'Post', 'geo-my-wp' );

					return $args;
				}
				add_filter( 'gmw_single_location_widget_objects', 'gmw_pt_single_location_widget_object', 5 );

			}

			if ( ! IS_ADMIN || defined( 'DOING_AJAX' ) ) {
				include_once 'includes/class-gmw-single-post-location.php';
			}
		}
	}
}
GMW_Addon::register( 'GMW_Posts_Locator_Addon' );
