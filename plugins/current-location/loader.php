<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Addon' ) ) {
	return;
}

/**
 * Current Location addon
 *
 */
class GMW_Current_Location_Addon extends GMW_Addon {

	// slug
	public $slug = 'current_location';

	// add-on's name
	public $name = 'Current Location';

	// prefix
	public $prefix = 'cl';

	// version
	public $version = GMW_VERSION;

	// description
	public $description = "Retreive and display the visitor's current position.";

	// path
	public $full_path = __FILE__;

	// core add-on
	public $is_core = true;

	/**
	 * Docs page.
	 *
	 * @var string
	 */
	public $docs_page = 'https://docs.geomywp.com/category/48-current-location';

	private static $instance = null;

	/**
	 * Create new instance
	 *
	 * @return [type] [description]
	 */
	public static function get_instance() {

		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load widget
	 *
	 * @return [type] [description]
	 */
	public function init_widgets() {
		include( 'includes/class-gmw-current-location-widget.php' );
	}

	/**
	 * Include files
	 * @return [type] [description]
	 */
	public function pre_init() {

		parent::pre_init();

		if ( ! IS_ADMIN || defined( 'DOING_AJAX' ) ) {
			include( 'includes/class-gmw-current-location.php' );
			include( 'includes/gmw-current-location-shortcode.php' );
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @return [type] [description]
	 */
	public function enqueue_scripts() {

		// register gmw script
		//wp_register_script( 'gmw-current-location', GMW_URL.'/assets/js/gmw.current.location.min.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );
	}
}
GMW_Addon::register( 'GMW_Current_Location_Addon' );
