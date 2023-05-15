<?php
/**
 * Current Location loader.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Addon' ) ) {
	return;
}

/**
 * Current Location addon
 */
class GMW_Current_Location_Addon extends GMW_Addon {

	/**
	 * Slug.
	 *
	 * @var string
	 */
	public $slug = 'current_location';

	/**
	 * Name.
	 *
	 * @var string
	 */
	public $name = 'Current Location';

	/**
	 * Prefix.
	 *
	 * @var string
	 */
	public $prefix = 'cl';

	/**
	 * Version.
	 *
	 * @var [type]
	 */
	public $version = GMW_VERSION;

	/**
	 * Description.
	 *
	 * @var string
	 */
	public $description = "Retreive and display the visitor's current position.";

	/**
	 * Path.
	 *
	 * @var [type]
	 */
	public $full_path = __FILE__;

	/**
	 * Core addon.
	 *
	 * @var boolean
	 */
	public $is_core = true;

	/**
	 * Docs page.
	 *
	 * @var string
	 */
	public $docs_page = 'https://docs.geomywp.com/category/48-current-location';

	/**
	 * [$instance description].
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

		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load widget
	 */
	public function init_widgets() {
		require_once 'includes/class-gmw-current-location-widget.php';
	}

	/**
	 * Include files
	 */
	public function pre_init() {

		parent::pre_init();

		//if ( ! IS_ADMIN || defined( 'DOING_AJAX' ) ) {
			require_once 'includes/class-gmw-current-location.php';
			require_once 'includes/gmw-current-location-shortcode.php';
		//}
	}
}
GMW_Addon::register( 'GMW_Current_Location_Addon' );
