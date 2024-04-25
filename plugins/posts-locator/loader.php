<?php
/**
 * GEO my WP - Posts Locator Loader.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	public function get_description() {
		return __( 'Geotag any of your post types and create proxmity search form that search and find posts based on location.', 'geo-my-wp' );
	}

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
			'img_slug' => 'posts_locator',
			'desc'     => __( 'Posts Locator general settings.', 'geo-my-wp' ),
			'priority' => 7,
		);
	}

	/**
	 * Form button
	 *
	 * @return [type] [description]
	 */
	public function form_buttons() {

		$buttons = array(
			array(
				'slug'      => 'posts_locator',
				'name'      => __( 'Posts Locator Form', 'geo-my-wp' ),
				'component' => 'posts_locator',
				'prefix'    => 'pt',
				'priority'  => 5,
			),
			array(
				'slug'      => 'posts_locator_mashup_map',
				'name'      => __( 'Posts Mashup Map', 'geo-my-wp' ),
				'component' => 'posts_locator',
				'prefix'    => 'ptmmap',
				'priority'  => 7,
			),
		);

		if ( ! gmw_is_addon_active( 'ajax_forms' ) ) {

			$buttons[] = array(
				'slug'        => 'posts_locator_ajax',
				'name'        => __( 'Posts Locator AJAX Form', 'geo-my-wp' ),
				'prefix'      => 'ajaxfmspt',
				'component'   => 'posts_locator',
				'object_type' => 'post',
				'premium'     => 'ajax_forms',
				'priority'    => 7,
			);
		}

		if ( ! gmw_is_addon_active( 'global_maps' ) ) {

			$buttons[] = array(
				'slug'        => 'posts_locator_global_map',
				'name'        => __( 'Posts Global Map', 'geo-my-wp' ),
				'prefix'      => 'gmapspt',
				'component'   => 'posts_locator',
				'object_type' => 'post',
				'premium'     => 'global_maps',
				'priority'    => 7,
			);
		}

		return $buttons;
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

		require_once 'includes/gmw-posts-locator-functions.php';
		require_once 'includes/gmw-posts-locator-shortcodes.php';

		// include admin files.
		if ( IS_ADMIN ) {

			require_once 'includes/admin/class-gmw-posts-locator-form-editor.php';
			require_once 'includes/admin/class-gmw-posts-locator-admin-settings.php';
			require_once 'includes/admin/class-gmw-posts-locator-screens.php';
		}

		// include template functions.
		require_once 'includes/gmw-posts-locator-search-form-template-functions.php';
		require_once 'includes/gmw-posts-locator-search-results-template-functions.php';
		require_once 'includes/class-gmw-wp-query.php';
		require_once 'includes/class-gmw-posts-locator-form.php';
		require_once 'includes/class-gmw-posts-locator-mashup-map-form.php';
		require_once 'includes/class-gmw-post-location-form.php';

		// load single location post.
		if ( gmw_is_addon_active( 'single_location' ) ) {

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

			require_once 'includes/class-gmw-single-post-location.php';
		}
	}
}
GMW_Addon::register( 'GMW_Posts_Locator_Addon' );
