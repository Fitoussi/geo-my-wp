<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Addon' ) ) {
	return;
}

/**
 * Current Location addon class
 *
 */
class GMW_Single_Location_Addon extends GMW_Addon {

	// slug
	public $slug = 'single_location';

	// add-on's name
	public $name = 'Single Location';

	// prefix
	public $prefix = 'sl';

	// version
	public $version = GMW_VERSION;

	// author
	public $author = 'Eyal Fitoussi';

	// path
	public $full_path = __FILE__;

	// description
	public $description = 'Display location of certain component ( post, member... ) via shortcode and widget.';

	// core add-on
	public $is_core = true;

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
	 * Init widgets
	 *
	 * @return [type] [description]
	 */
	function init_widgets() {
		include_once( 'includes/class-gmw-single-location-widget.php' );
	}

	/**
	 * Include files
	 *
	 * @return [type] [description]
	 */
	public function pre_init() {

		parent::pre_init();

		//include classes files
		if ( ! IS_ADMIN || defined( 'DOING_AJAX' ) ) {
			include_once( 'includes/class-gmw-single-location.php' );
			include_once( 'includes/gmw-single-location-shortcode.php' );
		}
	}
}
GMW_Addon::register( 'GMW_Single_Location_Addon' );
