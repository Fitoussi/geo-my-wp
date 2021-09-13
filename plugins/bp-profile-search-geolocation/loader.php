<?php
/**
 * GEO my WP - BP Profile Search geolocation loader.
 *
 * @author Eyal Fitoussi
 *
 * @created 3/2/2019
 *
 * @since 3.3
 *
 * @package gmw-bp-profile-search-integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'GMW_Addon' ) ) {
	return; // look for GMW add-on registration class.
}

/**
 * GMW_BP_Profile_Search_Geolocation_Addon.
 */
class GMW_BP_Profile_Search_Geolocation_Addon extends GMW_Addon {

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'bp_profile_search_geolocation';

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name = 'BuddyPress Profile Search Geolocation';

	/**
	 * Prefix
	 *
	 * @var string
	 */
	public $prefix = 'bpsgeo';

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
	 * Description
	 *
	 * @var string
	 */
	public $description = 'Geolocation integration with BP Profile Search plguin.';

	/**
	 * Support page.
	 *
	 * @var string
	 */
	public $support_page = 'https://geomywp.com/support/';

	/**
	 * Docs page.
	 *
	 * @var string
	 */
	public $docs_page = 'https://docs.geomywp.com';

	/**
	 * Required extensions
	 *
	 * @var array
	 */
	public function required() {

		return array(
			'addons'  => array(
				array(
					'slug'   => 'members_locator',
					'notice' => __( 'BP Profile Search Geolocation extension requires the Members Locator core extension.', 'gmw-my-wp' ),
				),
			),
			'plugins' => array(
				array(
					'function' => 'bps_buddypress',
					'notice'   => __( 'BP Profile Search Geolocation extension requires the BP Profile Search plguin.', 'geo-my-wp' ),
				),
			),
		);
	}

	/**
	 * Settings groups
	 *
	 * @return [type] [description]
	 */
	/**
		Return array(
			'slug'     => 'bp_profile_search_geolocation',
			'label'    => __( 'BP Profile Search Geolocation', 'gmw-my-wp' ),
			'icon'     => 'buddypress',
			'priority' => 13,
		);
	}*/

	/**
	 * [$instance description]
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
	 * Pre init functions
	 */
	public function pre_init() {

		if ( ! function_exists( 'bps_buddypress' ) ) {
			return;
		}

		parent::pre_init();

		// init add-on bp_init.
		add_action( 'bp_init', array( $this, 'bpsgeo_init' ) );
	}

	/**
	 * Init extension
	 */
	public function bpsgeo_init() {

		// include in admin only.
		if ( IS_ADMIN && ! defined( 'DOING_AJAX' ) ) {
			include_once 'includes/admin/class-gmw-bp-profile-search-geolocation-admin.php';
		}

		//add_filter( 'bp_get_template_stack', array( $this, 'template_stack' ), 30 );

		include_once 'includes/class-gmw-bp-profile-search-geolocation.php';
	}

	/*public function template_stack( $stack ) {

		$stack[] = dirname(__FILE__) . '/templates/bps-fields';

		return $stack;
	}*/

	/**
	 * Enqueue/register scripts
	 */
	public function enqueue_scripts() {

		wp_register_script(
			'gmw-bpsgeo',
			$this->plugin_url . '/assets/js/gmw.bpsgeo.min.js',
			array( 'gmw' ),
			$this->version,
			true
		);
	}
}
GMW_Addon::register( 'GMW_BP_Profile_Search_Geolocation_Addon' );
