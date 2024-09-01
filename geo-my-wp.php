<?php
/**
 * Plugin Name:       GEO my WP
 * Plugin URI:        http://www.geomywp.com
 * Description:       GEO my WP is an adavanced geolocation, mapping, and proximity search plugin. Geotag post types and BuddyPress members, and create advanced, proximity search forms to search and find locations based on address, radius,categories and more.
 * Author:            Eyal Fitoussi
 * Author URI:        http://www.geomywp.com
 * Version:           4.5.0.4
 * Requires at least: 5.6
 * Tested up to:      6.6.1
 * Requires PHP:      7.0
 * Text Domain:       geo-my-wp
 * Domain Path:       /languages
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GEO my WP class.
 */
class GEO_MY_WP {

	/**
	 * GEO my WP version.
	 *
	 * @var string
	 */
	public $version = '4.5.0.4';

	/**
	 * GEO my WP & Extensions options.
	 *
	 * @var [type]
	 */
	public $options;

	/**
	 * GEO my WP URL parameteres prefix.
	 *
	 * This is the prefix used for the URL paramaters that GEO my WP
	 * uses with submitted form. It can modified using the filter 'gmw_form_url_prefix', 'gmw_'.
	 *
	 * @var string
	 */
	public $url_prefix = '';

	/**
	 * Prefix for the user's location cookies.
	 *
	 * @var string
	 */
	public $ulc_prefix = 'gmw_ul_';

	/**
	 * Showing on mobile device?
	 *
	 * @var boolean
	 */
	public $is_mobile = false;

	/**
	 * Ajax URl.
	 *
	 * @var boolean
	 */
	public $ajax_url = false;

	/**
	 * Default Maps provider.
	 *
	 * @var string
	 */
	public $maps_provider = 'google_maps';

	/**
	 * Default Geocoding Provider.
	 *
	 * @var string
	 */
	public $geocoding_provider = 'google_maps';

	/**
	 * Enable disable internal caching system.
	 *
	 * @var boolean
	 */
	public $internal_cache = true;

	/**
	 * Enable disable internal caching system.
	 *
	 * @var boolean
	 */
	public $internal_cache_expiration = DAY_IN_SECONDS;

	/**
	 * Minimum versions required for this version of GEO my WP.
	 *
	 * @var array
	 */
	public $required_versions = array(
		'ajax_forms'                       => '2.0',
		'bp_groups_locator'                => '2.0.3',
		'groups_locator'                   => '2.0.3', // old slug.
		'bp_members_directory_geolocation' => '2.0.2',
		'geo_members_directory'            => '2.0.2', // old slug.
		'bp_xprofile_geolocation'          => '2.0',
		'xprofile_fields'                  => '2.0', // old slug.
		'exclude_locations'                => '2.0',
		'exclude_members'                  => '2.0', // old slug.
		'global_maps'                      => '3.0',
		'ip_address_locator'               => '1.1',
		'gmw_meta_fields_geo'              => '1.0',
		'gmw_multiple_locations'           => '2.0',
		'nearby_locations'                 => '2.0',
		'nearby_posts'                     => '2.0', // old slug.
		'gmw_peepso_geolocation'           => '1.0',
		'premium_settings'                 => '3.1',
		'radius_per_location'              => '2.0',
		'users_locator'                    => '2.0.2',
		'wp_users_geo-location'            => '2.0.2', // old slug.
		'gmw_kleo_geolocation'             => '1.9',
	);

	/**
	 * Registered Objects.
	 *
	 * @var array
	 */
	public $objects = array();

	/**
	 * Registered Objects Types.
	 *
	 * @var array
	 */
	public $object_types = array();

	/**
	 * Loaded addons.
	 *
	 * @var array
	 */
	public $registered_addons = array();

	/**
	 * Addons Status.
	 *
	 * @var array
	 */
	public $addons_status = array();

	/**
	 * Colelction of GEO my WP extensions' licenses.
	 *
	 * @var array
	 */
	public $licenses = array();

	/**
	 * Collections of object types and blog ID.
	 * This will be used on multisite installation.
	 * and with objects that use different blog IDs. For example,
	 * users will be saved in the main blog even on multisite since users
	 * share the same table across all blogs.
	 *
	 * @var array
	 */
	public $locations_blogs = array();

	/**
	 * Core addons.
	 *
	 * @var array
	 */
	public $core_addons = array();

	/**
	 * Addons data.
	 *
	 * @var array
	 */
	public $addons = array();

	/**
	 * Licenses data.
	 *
	 * Needed in admin only.
	 *
	 * @var array
	 */
	public $licenses_data = array();

	/**
	 * Current Form being loaded.
	 *
	 * @var array
	 */
	public $current_form = array();

	/**
	 * Default map icons.
	 *
	 * @var array
	 */
	public $default_icons = array();

	/**
	 * Saved map icons ( to be used with the Premium Settings extension ).
	 *
	 * @var array
	 */
	public $icons = array();

	/**
	 * Set the default icons and icon size.
	 *
	 * @var array
	 */
	public function set_default_icons() {
		$this->default_icons = array(
			'shadow_icon_url'         => 'https://unpkg.com/leaflet@1.3.1/dist/images/marker-shadow.png',
			/** 'location_icon_url'     => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png', */
			'location_icon_url'       => GMW_IMAGES . '/marker-icon-red-2x.png',
			'location_icon_size'      => array( 25, 41 ),
			/** 'user_location_icon_url'=> 'https://unpkg.com/leaflet@1.3.1/dist/images/marker-icon-2x.png', */
			'user_location_icon_url'  => GMW_IMAGES . '/marker-icon-blue-2x.png',
			'user_location_icon_size' => array( 25, 41 ),
		);
	}

	/**
	 * GEO my WP instance.
	 *
	 * @var object GEO my WP.
	 *
	 * @since 2.4
	 */
	private static $instance;

	/**
	 *
	 * GEO_my_WP Instance.
	 *
	 * Make sure that only one instance exists.
	 *
	 * @since 2.4
	 *
	 * @return GEO_my_WP
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof GEO_my_WP ) ) {

			self::$instance = new GEO_my_WP();
			self::$instance->constants();

			// run plugin installer once GEO my WP activated.
			register_activation_hook( __FILE__, array( self::$instance, 'install' ) );

			// setup some global variables.
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->actions();
		}

		return self::$instance;
	}

	/**
	 * A dummy constructor to prevent GEO my WP from being loaded more than once.
	 *
	 * @since 2.4
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of GEO my WP.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ), '3.0' ); // phpcs:ignore: XSS ok.
	}

	/**
	 * Prevent GEO my WP from being unserialized.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ), '3.0' ); // phpcs:ignore: XSS ok.
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 2.4
	 * @return void
	 */
	private function constants() {

		// Define constants.
		if ( ! defined( 'GMW_REMOTE_SITE_URL' ) ) {
			define( 'GMW_REMOTE_SITE_URL', 'https://geomywp.com' );
		}

		if ( ! defined( 'IS_ADMIN' ) ) {
			define( 'IS_ADMIN', is_admin() );
		}

		define( 'GMW_VERSION', $this->version );
		define( 'GMW_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'GMW_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'GMW_PLUGINS_PATH', GMW_PATH . '/plugins' );
		define( 'GMW_PLUGINS_URL', GMW_URL . '/plugins' );
		define( 'GMW_IMAGES', GMW_URL . '/assets/images' );
		define( 'GMW_FILE', __FILE__ );
		define( 'GMW_BASENAME', plugin_basename( GMW_FILE ) );
	}

	/**
	 * Runs once GEO my WP Loaded.
	 *
	 * @return void
	 */
	public static function loaded() {

		// load textdomain.
		load_plugin_textdomain( 'geo-my-wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// fires when GEO my WP has loaded.
		do_action( 'gmw_loaded' );

		// initializing add-ons that registered using GMW_Addon class.
		if ( class_exists( 'GMW_Addon' ) ) {
			GMW_Addon::init_addons();
		}
	}

	/**
	 * Plugin installer.
	 *
	 * Execute when plugin activated.
	 */
	public function install() {

		require_once 'includes/class-gmw-installer.php';

		GMW_Installer::init();

		flush_rewrite_rules();
	}

	/**
	 * Plugin Updates.
	 */
	public function update() {

		// check if version changed.
		if ( version_compare( GMW_VERSION, get_option( 'gmw_version' ), '>' ) ) {

			require_once 'includes/class-gmw-installer.php';

			GMW_Installer::init();

			flush_rewrite_rules();
		}
	}

	/**
	 * Setup global variables.
	 */
	public function setup_globals() {

		// for previous version, should be removed in the future.
		global $gmw_options;

		// get some addons data.
		$gmw_options   = get_option( 'gmw_options' );
		$this->options = $gmw_options;
		$addons_status = get_option( 'gmw_addons_status' );

		if ( IS_ADMIN ) {
			$this->licenses_data = get_option( 'gmw_license_data' );
		}

		if ( empty( $addons_status ) ) {
			$addons_status = array();
		}

		/**
		 * We get the addons data from database only in front-end.
		 *
		 * While in the back-end the addons data is being collected
		 *
		 * and saved in the options table to later be used in the front-end.
		 *
		 * We do this to prevent some addons data from generating on every page load.
		 */
		if ( ! IS_ADMIN ) {

			$addons_data = get_option( 'gmw_addons_data' );

			if ( empty( $addons_data ) ) {
				$addons_data = array();
			}

			$this->addons = $addons_data;
		}

		// addons statuses: active, inactive or disabled.
		$this->addons_status = $addons_status;
		$this->ajax_url      = admin_url( 'admin-ajax.php', is_ssl() ? 'admin' : 'http' );
		$this->is_mobile     = ( function_exists( 'wp_is_mobile' ) && wp_is_mobile() ) ? true : false;

		if ( ! empty( $this->options['api_providers']['maps_provider'] ) ) {

			$maps_provider = $this->options['api_providers']['maps_provider'];

			if ( 'google_maps' === $maps_provider ) {

				$this->maps_provider      = 'google_maps';
				$this->geocoding_provider = 'google_maps';

			} else if ( 'leaflet_locationiq' === $maps_provider ) {

				$this->maps_provider      = 'leaflet';
				$this->geocoding_provider = 'locationiq';

			} else {

				$this->maps_provider      = 'leaflet';
				$this->geocoding_provider = 'nominatim';
			}
		}

		// set default icons.
		$this->set_default_icons();
	}

	/**
	 * Include files.
	 *
	 * @since 2.4
	 */
	public function includes() {

		// include files.
		require_once 'includes/class-gmw-cache-helper.php';
		require_once 'includes/class-gmw-helper.php';
		require_once 'includes/class-gmw-forms-helper.php';
		require_once 'includes/gmw-functions.php';
		require_once 'includes/class-gmw-addon.php';
		require_once 'includes/class-gmw-location-meta.php';
		require_once 'includes/class-gmw-location.php';
		require_once 'includes/gmw-location-functions.php';
		require_once 'includes/users-locator/gmw-user-location-functions.php';
		require_once 'includes/class-gmw-maps-api.php';
		require_once 'includes/gmw-deprecated-functions.php';
		require_once 'includes/class-gmw-cron.php';
		require_once 'includes/gmw-enqueue-scripts.php';
		require_once 'includes/location-form/includes/class-gmw-location-form.php';
		require_once 'includes/template-functions/class-gmw-search-form-helper.php';
		require_once 'includes/template-functions/class-gmw-template-functions-helper.php';
		require_once 'includes/template-functions/gmw-template-functions.php';
		require_once 'includes/template-functions/gmw-search-form-template-functions.php';
		require_once 'includes/template-functions/gmw-search-results-template-functions.php';
		require_once 'includes/class-gmw-form-core.php';
		require_once 'includes/class-gmw-form.php';
		require_once 'includes/gmw-shortcodes.php';
		require_once 'includes/class-gmw-geocoder.php';
		require_once 'includes/gmw-geocoding-providers.php';

		// load core add-ons.
		self::$instance->load_core_addons();

		// include admin files.
		if ( IS_ADMIN ) {
			require_once GMW_PATH . '/includes/admin/class-gmw-admin.php';
		}
	}

	/**
	 * Add actions.
	 *
	 * Run update on admin init.
	 *
	 * @since 2.4
	 */
	public function actions() {

		add_action( 'plugins_loaded', array( $this, 'loaded' ) );
		add_action( 'widgets_init', array( $this, 'widgets_init' ), 5 );
		add_action( 'admin_init', array( $this, 'update' ) );
		add_action( 'init', array( $this, 'wp_init' ) );
	}

	/**
	 * Loads widgets.
	 */
	public function widgets_init() {
		require_once 'includes/class-gmw-widget.php';
		require_once 'includes/widgets/class-gmw-search-form-widget.php';
	}

	/**
	 * Verify if add-on is active ( deprecated ).
	 *
	 * @param  string $addon addon slug to check against.
	 *
	 * @return [boolean]
	 */
	public static function gmw_check_addon( $addon ) {
		return gmw_is_addon_active( $addon );
	}

	/**
	 * Include core add-ons.
	 */
	private function load_core_addons() {
		require_once GMW_PLUGINS_PATH . '/single-location/loader.php';
		require_once GMW_PLUGINS_PATH . '/posts-locator/loader.php';
		require_once GMW_PLUGINS_PATH . '/members-locator/loader.php';
		require_once GMW_PLUGINS_PATH . '/bp-profile-search-geolocation/loader.php';
		require_once GMW_PLUGINS_PATH . '/current-location/loader.php';
	}

	/**
	 * When WordPress loaded.
	 */
	public function wp_init() {

		// run some filters.
		$this->url_prefix                = esc_attr( apply_filters( 'gmw_form_url_prefix', $this->url_prefix ) );
		$this->ulc_prefix                = esc_attr( apply_filters( 'gmw_user_location_cookie_prefix', $this->ulc_prefix ) );
		$this->internal_cache_expiration = apply_filters( 'gmw_internal_cache_expiration', $this->internal_cache_expiration );

		$cache_status = get_option( 'gmw_internal_cache_status' );

		$this->internal_cache = ! empty( $cache_status ) ? true : false;
		$this->internal_cache = apply_filters( 'gmw_internal_cache_enabled', $this->internal_cache );
	}
}

/**
 * GMW Instance.
 *
 * @since 1.1.1
 *
 * @return object Instance of GEO_MY_WP.
 */
function GMW() {
	return GEO_MY_WP::instance();
}

// Init GMW.
$GLOBALS['geomywp'] = GMW();
