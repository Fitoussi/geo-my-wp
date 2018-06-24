<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//abort if register add-on class is not found
if ( ! class_exists( 'GMW_Addon' ) ) {
	return;
}

/**
 * Register Posts locator add-on
 */
class GMW_Posts_Locator_Addon extends GMW_Addon {

	// slug
	public $slug = 'posts_locator';

	// add-on's name
	public $name = 'Posts Locator';

	// prefix
	public $prefix = 'pt';

	// version
	public $version = GMW_VERSION;

	// author
	public $author = 'Eyal Fitoussi';

	// description
	public $description = 'Provides geolocation for WordPress post types.';

	// object
	public $object = 'post';

	// database object type
	public $object_type = 'post';

	// db table prefix
	public $global_db = false;

	// Plugin use template files
	public $templates_folder = 'posts-locator';

	// path
	public $full_path = __FILE__;

	// core add-on
	public $is_core = true;

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

	private static $instance = null;

	/**
	 * Create new instance
	 *
	 * @return [type] [description]
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * [pre_init description]
	 * @return [type] [description]
	 */
	public function pre_init() {

		parent::pre_init();

		include( 'includes/gmw-posts-locator-functions.php' );

		// include admin files
		if ( IS_ADMIN ) {

			include_once( 'includes/admin/class-gmw-posts-locator-form-editor.php' );
			include_once( 'includes/admin/class-gmw-posts-locator-admin-settings.php' );
			include_once( 'includes/admin/class-gmw-posts-locator-screens.php' );
		}

		// include template functions.
		include_once( 'includes/gmw-posts-locator-search-form-template-functions.php' );
		include_once( 'includes/gmw-posts-locator-search-results-template-functions.php' );
		include_once( 'includes/class-gmw-posts-locator-form.php' );

		// load single location post
		if ( gmw_is_addon_active( 'single_location' ) ) {

			if ( IS_ADMIN ) {

				/**
				 * Add post object to objects dropdown in single location widget
				 *
				 * @param  [type] $args [description]
				 * @return [type]       [description]
				 */
				function gmw_pt_single_location_widget_object( $args ) {

					$args['post'] = __( 'Post', 'geo-my-wp' );

					return $args;
				}
				add_filter( 'gmw_single_location_widget_objects', 'gmw_pt_single_location_widget_object', 5 );

			}

			if ( ! IS_ADMIN || defined( 'DOING_AJAX' ) ) {
				include_once( 'includes/class-gmw-single-post-location.php' );
			}
		}
	}
}
GMW_Addon::register( 'GMW_Posts_Locator_Addon' );
