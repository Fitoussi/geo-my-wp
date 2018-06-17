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
 */
class GMW_Sweetdate_Geolcation_Addon extends GMW_Addon {

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'sweetdate_geolocation';

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name = 'Sweet Date Geolocation';

	/**
	 * Description
	 *
	 * @var string
	 */
	public $description = 'Enhance the Sweet Date theme with geolocation features.';

	/**
	 * prefix
	 *
	 * @var string
	 */
	public $prefix = 'sdate_geo';

	// version
	public $version = GMW_VERSION;

	/**
	 * Path
	 *
	 * @var [type]
	 */
	public $full_path = __FILE__;

	/**
	 * Is core add-on
	 *
	 * @var boolean
	 */
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
	 * required extensions
	 *
	 * @var array
	 */
	public function required() {

		return array(
			'theme'  => array(
				'template' => 'sweetdate',
				'notice'   => sprintf( __( 'Sweet Date Geolocation extension requires the Sweet Date theme version 2.9 order higher. The theme can be purchased separately from <a href="%s" target="_blank">here</a>.', 'geo-my-wp' ), 'https://themeforest.net/item/sweet-date-more-than-a-wordpress-dating-theme/4994573?ref=GEOmyWP' ),
				'version'  => '2.9',
			),
			'addons' => array(
				array(
					'slug'   => 'members_locator',
					'notice' => __( 'Sweet Date Geolocation extension requires the Members Locator core extension.', 'geo-my-wp' ),
				),
			),
		);
	}

	/**
	 * Register scripts
	 *
	 * @return [type] [description]
	 */
	public function enqueue_scripts() {
		if ( ! IS_ADMIN ) {
			wp_register_script( 'gmw-sdate-geo', GMW_SDATE_GEO_URL . '/assets/js/gmw.sd.min.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );
		}
	}

	/**
	 * Run on BuddyPress init
	 *
	 * @return void
	 */
	public function pre_init() {

		parent::pre_init();

		add_action( 'bp_init', array( $this, 'sweetdate_geolocation_init' ), 20 );
	}

	/**
	 * Load add-on
	 *
	 * @return [type] [description]
	 */
	public function sweetdate_geolocation_init() {

		// admin settings
		if ( is_admin() ) {
			include_once( 'includes/admin/class-gmw-sweet-date-admin-settings.php' );
			new GMW_Sweet_Date_Admin_Settings;
		}

		// include members query only on members page
		if ( 'members' == bp_current_component() && '' != gmw_get_option( 'sweet_date', 'enabled', '' ) ) {
			include_once( 'includes/class-gmw-sweet-date-geolocation.php' );
			new GMW_Sweet_Date_Geolocation;
		}
	}
}
GMW_Addon::register( 'GMW_Sweetdate_Geolcation_Addon' );
