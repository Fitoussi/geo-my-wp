<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Addon' ) ) {
	return;
}

/**
 * Current Location addon class
 */
class GMW_Single_Location_Addon extends GMW_Addon {

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'single_location';

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name = 'Single Location';

	/**
	 * Prefix
	 *
	 * @var string
	 */
	public $prefix = 'sl';

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
	 * Path
	 *
	 * @var string
	 */
	public $full_path = __FILE__;

	/**
	 * Description
	 *
	 * @var string
	 */
	public $description = 'Display location of certain component ( post, member... ) via shortcode and widget.';

	/**
	 * Is core addon.
	 *
	 * @var string
	 */
	public $is_core = true;

	/**
	 * Always active.
	 *
	 * @var string
	 */
	public $always_active = true;

	/**
	 * Docs page.
	 *
	 * @var string
	 */
	public $docs_page = 'https://docs.geomywp.com/category/47-single-location';

	/**
	 * Instance of Single Locaiton.
	 *
	 * @var string
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
	 * Init widgets
	 */
	public function init_widgets() {
		require_once 'includes/class-gmw-single-location-widget.php';
	}

	/**
	 * Include files
	 */
	public function pre_init() {

		parent::pre_init();

		require_once 'includes/class-gmw-single-location.php';
		require_once 'includes/gmw-single-location-shortcode.php';
	}
}
GMW_Addon::register( 'GMW_Single_Location_Addon' );
