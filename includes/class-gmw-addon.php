<?php
/**
 * GEO my WP Addon Class.
 *
 * Use this class to properly register an addon.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// load class only once.
if ( ! class_exists( 'GMW_Addon' ) ) :

	/**
	 * GMW_Addon class
	 *
	 * Register new add-on
	 */
	class GMW_Addon {

		/********** Required variables ********/

		/**
		 * Add-on's slug.
		 *
		 * Identifire to be used with URL, settings, add-on setup... Ex. "posts_locator"
		 *
		 * @var string
		 */
		public $slug = '';

		/**
		 * Add-on's name.
		 *
		 * To be used on GEO my WP pages, Add-ons page... Ex. "Post Types Locator".
		 *
		 * @var string
		 */
		public $name = '';

		/**
		 * Add-on prefix.
		 *
		 * To be used with hooks, CLASS/ID tags... Ex. if add-on's name is Posts Locator the prefix can be "pl".
		 *
		 * @var string
		 */
		public $prefix = '';

		/**
		 * Add-on's version
		 *
		 * @var string
		 */
		public $version = '1.0';

		/********** End required variables ********/

		/**
		 * Add-on's author
		 *
		 * Optional only if using a license key
		 *
		 * Otherwise, If left blank the plugin's author will be used
		 *
		 * @var string
		 */
		public $author = '';

		/******* Required if license key is being used ********/

		/**
		 * License name/slug - must provided in order to activate licesing
		 *
		 * This will usually be the same as the plugin's slug
		 *
		 * @var boolean
		 */
		public $license_name = false;

		/**
		 * When licesing is being used the the $_item_name will be the title of the plugin's post in http://geomywp.com which hosts the add-ons.
		 *
		 * @var string
		 */
		public $item_name = null;

		/**
		 * When licesing is being used the the item_id will be the post ID of the plugin's post in http://geomywp.com which hosts the add-ons.
		 *
		 * @var string
		 */
		public $item_id = null;

		/**
		 * URL of the site hosting the add-on ( currently works with geo my wp hosted add-on only ).
		 *
		 * @var string
		 */
		private $api_url = 'https://geomywp.com';


		/********** Optional variables ************/

		/**
		 * Object
		 *
		 * Set this if the add-ons will use its own objects for location. For example, post, BP member, WP user....
		 *
		 * Example:
		 *
		 * array(
		 *  'slug' => 'post', // the slug of the object
		 *  'name' => 'WordPress Post', // name/label for the pbject
		 *  'type' => 'post' // the type of the object which will also be saved in the locations database ( post, user, group... )
		 * );
		 *
		 * @var boolean | array
		 */
		public $objects = false;

		/**
		 * This is not being used at the moment and is generated automatically.
		 *
		 * Use the objects array above to generate an object which also include its type.
		 *
		 * @var boolean
		 */
		public $object_type = false;

		/**
		 * Locations blog ID
		 *
		 * For some objects, such as user, WordPress use a global database table when using multisite installation.
		 * That is instead of creating a db table per blog ( subsite ).
		 *
		 * In such case we need to have a similar behaviour with GEO my WP. For that we can use this variable
		 * to set a specific blog ID per object type, and all locations from all subsites will be saved with this blog ID
		 * in GEO my WP locations table.
		 *
		 * @var boolean
		 */
		public $locations_blog_id = false;

		/**
		 * Add-on's description for Extensions page.
		 *
		 * If left blank the plugin description will be used
		 *
		 * @var string
		 */
		public $description = '';

		/**
		 * Link to the addon's detailes/purchase page. Will show in Extensions page.
		 *
		 * @var string
		 */
		public $addon_page = '';

		/**
		 * Link to support page/site. Will show in Extensions page.
		 *
		 * @var string
		 */
		public $support_page = '';

		/**
		 * Link to documentaion page/site. Will show in Extensions page.
		 *
		 * @var string
		 */
		public $docs_page = '';

		/**
		 * Text domain. Set to false if not being used.
		 *
		 * @var boolean
		 */
		public $textdomain = false;

		/**
		 * Full path the the plugin. Usually will be __FILE__
		 *
		 * @var string
		 */
		public $full_path = __FILE__;

		/**
		 * Basename
		 *
		 * Will be generated automatically if left blank
		 *
		 * @var string
		 */
		public $basename = false;

		/**
		 * Plugin_dir
		 *
		 * Will be generated automatically if left blank
		 *
		 * @var string
		 */
		public $plugin_dir = false;

		/**
		 * Plugin_url
		 *
		 * Will be generated automatically if left blank
		 *
		 * @var string
		 */
		public $plugin_url = false;

		/**
		 * Core add-ons are built-in GEO my WP.
		 *
		 * @var boolean
		 */
		public $is_core = false;

		/**
		 * Minimum version of GEO my WP required to work with this version of the add-on.
		 *
		 * @var string
		 */
		public $gmw_min_version = GMW_VERSION;

		/**
		 * Set to true, or pass the folder name as a string, if the extension uses template files.
		 *
		 * When set to true the folder of the custom template files will be the extension's slug.
		 *
		 * Otherwise, pass the folder name as a string.
		 *
		 * The template files in the plugin's folder must placed under the folder templates
		 *
		 * for example gmw-places-locator/templates/....
		 *
		 * @var string | boolean
		 *
		 * --- Not being used at the moment. ---
		 */
		public $templates_folder = false;

		/**
		 * Array of extension ( slug ) required for this extension to work
		 *
		 * @var array
		 */
		public $required = array();

		/**
		 * Required function
		 */
		public function required() {
			return false;
		}

		/**
		 *  Create GEO my WP submenu item
		 *
		 *  To create a submenu you will need to pass an array with the following arg:
		 *
		 *  parent_slug - the parent menu. By default, and in most cases, it will be GEO my WP menu item ( 'gmw-extensions' ).
		 *  page_title - The menu item's page title ( ex. Tools Page )
		 *  menu_title - The menu item's title ( ex. Tools )
		 *  capability - User Capability that can access the menu item ( default is manage_options ).
		 *  menu_slug -  menu slug ( ex. gmw_tools ).
		 *  callback_function - the callback function for the menu items ( ex. gmw_get_tools_page ). It can also be a class method by passing an array with the name of the class and the method. For ex. array( 'Tools_Page', 'output' )
		 *  priority - priority of the menu item ( ex. 25 ).
		 *
		 * example :
		 *
		 *  array(
		 *      'parent_slug' 		=> 'gmw-extensions' ,
		 *      'page_title'  		=> 'Tools Page',
		 *      'menu_title'  		=> 'Tools',
		 *      'capability'  		=> 'manage_options',
		 *      'menu_slug'   	    => 'gmw-tools',
		 *      'callback_function' => 'gmw_get_tools_page',
		 *      'priority'          => 25
		 *  );
		 *
		 * More information about creating submenu items can be found here -> https://codex.wordpress.org/Function_Reference/add_submenu_page
		 *
		 *  You can also create multiple menu items by passing a multidimensional array or items.
		 */
		public function admin_menu_items() {
			return false;
		}

		/**
		 * Create GEO my WP admin settings groups
		 *
		 * Pass an array with the following arg:
		 *
		 *  slug - the slug for the group, which will also be used to save the data in database.
		 *  label - the label/title of the group's tab in the settings page.
		 *  icon - any of GEO my WP font icons.
		 *  priority - the priority the tab will show in the settings page.
		 *
		 *  example :
		 *
		 *  array(
		 *      'slug'       => 'posts_locator'
		 *      'label'      => 'Posts Locator',
		 *      'icon'       => 'pinboard',
		 *      'priority'   => 5
		 *  );
		 *
		 *  You can also create multiple groups by passing a multidimensional array.
		 */
		public function admin_settings_groups() {
			return false;
		}

		/**
		 * Create GEO my WP admin settings groups
		 *
		 * Pass an array with the following arg:
		 *
		 *  slug - the slug for the group, which will also be used to save the data in database.
		 *  label - the label/title of the group's tab in the settings page.
		 *  icon - any of GEO my WP font icons.
		 *  priority - the priority the tab will show in the settings page.
		 *
		 *  example :
		 *
		 *  array(
		 *      'slug'       => 'posts_locator'
		 *      'label'      => 'Posts Locator',
		 *      'icon'       => 'pinboard',
		 *      'priority'   => 5
		 *  );
		 *
		 *  You can also create multiple groups by passing a multidimensional array.
		 */
		public function form_settings_groups() {
			return false;
		}

		/**
		 * Create GEO my WP "New form" button for your add-on
		 *
		 * Pass an array with the following arg:
		 *
		 *  slug - the slug for the button. Can be as the slug of the extension unless the extension creates multiple buttons.
		 *  name - the name/title of the button.
		 *  prefix - a prefix for the button and form( ex. for post_type a good prefix would be "pt" ). Leave blank to use addon's prefix.
		 *  priority - the priority the button will show in the buttons dropdown
		 *
		 *  example :
		 *
		 *  array(
		 *      'slug'       => 'posts_locator',
		 *      'name'       => 'Posts Locator',
		 *      'prefix'     => pt,
		 *      'priority'   => 1
		 *  );
		 *
		 *  You can also create multiple buttons by passing a multidimensional array or buttons.
		 */
		public function form_buttons() {
			return false;
		}

		/**
		 * Collection of addons class that need to be registered by GEO my WP plugin.
		 *
		 * Registration takes place on 'plugins_loaded' action.
		 *
		 * @var array
		 */
		private static $registered_addons = array();

		/**
		 * Register an addon
		 *
		 * Use this function to register an addon properly.
		 *
		 * GEO my WP collects its addons and initialize them after is done loading.
		 *
		 * To make sure that addons do not load before GEO my WP.
		 *
		 * @param string $class the class name.
		 */
		public static function register( $class = false ) {

			if ( $class && ! in_array( $class, self::$registered_addons ) ) {
				self::$registered_addons[] = $class;
			}
		}

		/**
		 * Initializes addons.
		 *
		 * This class initializes the addons once GEO my WP is loaded.
		 *
		 * An addons must use the register function above so the addon
		 *
		 * will initialize properly using this function.
		 */
		public static function init_addons() {

			foreach ( self::$registered_addons as $addon ) {

				call_user_func( array( $addon, 'get_instance' ) );

			}
		}

		/**
		 * WordPress active plugins.
		 *
		 * @var boolean
		 */
		public static $active_plugins = false;

		/**
		 * Check if a WordPress plugin is active.
		 *
		 * @param  [type] $basename [description].
		 *
		 * @return boolean           [description]
		 */
		public static function is_plugin_active( $basename ) {

			// get the data from database only once.
			if ( ! self::$active_plugins ) {
				self::$active_plugins = get_option( 'active_plugins' );
			}

			return in_array( $basename, self::$active_plugins ) ? true : false;
		}

		/**
		 * Saved addon data which is being used in the front end
		 *
		 * @var boolean
		 */
		public static $saved_addons_data = false;

		/**
		 * Check status of saved addon data.
		 *
		 * @param  [type] $addon [description].
		 *
		 * @return boolean           [description]
		 */
		public static function verify_saved_addon_data( $addon ) {

			// get the data from database only once.
			if ( ! self::$saved_addons_data ) {
				self::$saved_addons_data = get_option( 'gmw_addons_data' );
			}

			return ( isset( self::$saved_addons_data[ $addon ] ) && isset( self::$saved_addons_data[ $addon ]['slug'] ) ) ? true : false;
		}

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// setup plugin's globals.
			$this->setup_globals();

			// run verifications and veget status
			// abort if addon is not active.
			if ( 'active' !== $this->get_status() ) {
				return;
			}

			// initialize the addon.
			$this->initialize();
		}

		/**
		 * Setup addon's globals
		 */
		public function setup_globals() {

			// add object to global.
			if ( ! empty( $this->object_type ) ) {

				if ( ! $this->object ) {
					$this->object = $this->object_type;
				}

				// add objects to global.
				GMW()->objects[] = $this->object;

				// add object type to global.
				GMW()->object_types[ $this->object ] = $this->object_type;
				GMW()->object_types[ $this->slug ]   = $this->object_type;
			}

			if ( is_multisite() && absint( $this->locations_blog_id ) ) {
				GMW()->locations_blogs[ $this->object_type ] = $this->locations_blog_id;
			}

			// plugin basename.
			if ( ! $this->basename ) {
				$this->basename = plugin_basename( $this->full_path );
			}

			// plugin dir.
			if ( ! $this->plugin_dir ) {
				$this->plugin_dir = untrailingslashit( plugin_dir_path( $this->full_path ) );
			}

			// plugin URL.
			if ( ! $this->plugin_url ) {
				$this->plugin_url = untrailingslashit( plugins_url( basename( plugin_dir_path( $this->full_path ) ), dirname( $this->full_path ) ) );
			}

			// appened addon to loaded addons.
			GMW()->registered_addons[] = $this->slug;

			// appened addon to core addons.
			if ( $this->is_core ) {
				GMW()->core_addons[] = $this->slug;
			}

			// load textdomain if needed.
			if ( ! empty( $this->textdomain ) ) {

				if ( did_action( 'plugins_loaded' ) === 1 ) {
					self::load_textdomain();
				} else {
					add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
				}
			}
		}

		/**
		 * Verify addon requirements, and get its status and data.
		 *
		 * Requirments such as specific theme, plugin or an addon.
		 *
		 * This function mostly runs on the back-end and save the data in the options table
		 *
		 * to later be used in the front-end.
		 *
		 * @return [type] [description]
		 */
		public function get_status() {

			/**
			 * We try to prevent the below from running in front-end to save memory and performance.
			 *
			 * I.e collecting data and verifying activation.
			 *
			 * The addon data will be collected while in admin and saved in options table so it can be used
			 *
			 * in the fron-end. The data is collected when activating/deactivating plugins.
			 *
			 * if the data does not exists in option, it will be then retrived using the below in the front-end as well.
			 */
			if ( IS_ADMIN || ! isset( GMW()->addons[ $this->slug ] ) || empty( GMW()->addons[ $this->slug ]['status'] ) ) {

				$this->status         = 'inactive';
				$this->status_details = false;

				// min version of the addon required to work with the current version of GEO my WP.
				$this->min_version = ! empty( GMW()->required_versions[ $this->slug ] ) ? GMW()->required_versions[ $this->slug ] : '1.0';

				// required theme, addons and plugins
				// check if passed via array. Otherwise, maybe via function.
				if ( empty( $this->required ) ) {
					$this->required = $this->required();
				}

				// verify activation and get status.
				$this->status = self::verify_activation();

				// generate addon data.
				GMW()->addons[ $this->slug ] = self::setup_addon_data();

				/*
				 * verify a few scenarios where the addon status doesn't match in database the value in database,
				 *
				 * in this object, or missing some data.
				 *
				 * Once example where the status can be different is if a plugin or a theme that the addon depends on
				 *
				 * was activated/deactivated.
				 *
				 * In this case we will update the status in database.
				 *
				 */
				if ( ! isset( GMW()->addons_status[ $this->slug ] ) || $this->status != GMW()->addons_status[ $this->slug ] || ! $this->verify_saved_addon_data( $this->slug ) ) {

					/**
					 * This function updates both the addon status and addon data objects
					 */
					gmw_update_addon_status( $this->slug, $this->status, $this->status_details );
				}

				// only in admin.
				if ( IS_ADMIN ) {

					// generate license data.
					GMW()->licenses[ $this->slug ] = self::setup_license_data();

					// activate addon when WordPress plugin activated
					// register_activation_hook( $this->full_path, array( $this, 'activate_addon' ) );
					// deactivate addon when WordPress plugin deactivated
					register_deactivation_hook( $this->full_path, array( $this, 'deactivate_addon' ) );
					// run installer.
					// check for add-ons data if missing, when probably first installed, or if plugin updated
					$this->installer();

					// load license handler.
					if ( ! empty( $this->license_name ) ) {
						self::addon_updater();
					}
				}

				// if addon data exists in databases we only need to get the status.
			} else {
				$this->status = GMW()->addons[ $this->slug ]['status'];
			}

			return $this->status;
		}

		/**
		 * Initialize the addon
		 */
		public function initialize() {

			// enqueue scripts admin.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// enqueue scripts front-end.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// setup constants.
			$this->constants();

			// pre initial function for admin and front-end.
			$this->pre_init();

			// run add-on in init mode.
			add_action( 'init', array( $this, 'init' ) );

			// if in admin.
			if ( IS_ADMIN ) {

				// if ( ! defined( 'DOING_AJAX' ) ) {
					// generate admin menu items.
					add_filter( 'gmw_admin_menu_items', array( $this, 'admin_menu_items_init' ) );

					// generate admin settings groups.
					add_filter( 'gmw_admin_settings_groups', array( $this, 'admin_settings_groups_init' ) );

					// generate form settings groups.
					add_filter( 'gmw_form_settings_groups', array( $this, 'form_settings_groups_init' ) );

					// generate form button.
					add_filter( 'gmw_admin_new_form_button', array( $this, 'form_buttons_init' ) );
				// }
				// pre init admin.
				$this->pre_init_admin();

				// pre init frontend.
			} else {

				// pre init front-end only.
				$this->pre_init_frontend();
			}

			// include widgets.
			add_action( 'widgets_init', array( $this, 'init_widgets' ) );
		}

		/**
		 * Collect add-on's data
		 *
		 * @return array pass to $_register_addons
		 */
		public function setup_addon_data() {

			// for now, the template files are being generated by GEO my WP.
			// It might be possible to control the folders name in the future.
			if ( ! empty( $this->templates_folder ) ) {

				$this->templates_folder = is_string( $this->templates_folder ) ? $this->templates_folder : $this->slug;

			} else {

				$this->templates_folder = '';
			}

			return array(
				'slug'              => $this->slug,
				'status'            => $this->status,
				'name'              => $this->name,
				'prefix'            => $this->prefix,
				'version'           => $this->version,
				'is_core'           => $this->is_core,
				'objects'           => $this->objects,
				'object_type'       => $this->object_type,
				'locations_blog_id' => $this->locations_blog_id,
				'full_path'         => $this->full_path,
				'basename'          => $this->basename,
				'plugin_dir'        => $this->plugin_dir,
				'plugin_url'        => $this->plugin_url,
				'templates_folder'  => $this->templates_folder,
				// 'custom_templates_folder'     => $this->custom_templates_folder
			);
		}

		/**
		 * Collect add-on's license data.
		 *
		 * This data required in the backend only.
		 *
		 * @return array pass to $_register_addons
		 */
		public function setup_license_data() {

			return array(
				'author'         => $this->author,
				'description'    => $this->description,
				'addon_page'     => $this->addon_page,
				'docs_page'      => $this->docs_page,
				'support_page'   => $this->support_page,
				'required'       => $this->required,
				'license_name'   => $this->license_name,
				'item_name'      => $this->item_name,
				'item_id'        => $this->item_id,
				'status_details' => $this->status_details,
			);
		}

		/**
		 * Verify extension activation
		 *
		 * @return boolean
		 */
		public function verify_activation() {

			$verified = array(
				'status'  => true,
				'details' => false,
			);

			$licenses_data = GMW()->licenses_data;

			// extensions disabled by the admin are not allowed.
			if ( ! empty( $this->license_name ) && ! empty( $licenses_data[ $this->license_name ] ) && isset( $licenses_data[ $this->license_name ]['status'] ) && 'disabled' === $licenses_data[ $this->license_name ]['status'] ) {

				$verified['details'] = array(
					'error'            => 'license_disabled_by_admin',
					'required_version' => '',
					'notice'           => $details['notice'] = sprintf(
						__( 'GEO my WP %1$s extension is disabled due to an issue with the license key. Contact support for more information.', 'geo-my-wp' ),
						$this->name
					),
				);

				// display admin notice.
				add_action( 'admin_notices', array( $this, 'verify_activation_notice' ) );

				$verified['status'] = false;
			}

			// verify GEO my WP min version.
			if ( ! $this->is_core && version_compare( GMW_VERSION, $this->gmw_min_version, '<' ) ) {

				$verified['details'] = array(
					'error'            => 'gmw_version_mismatch',
					'required_version' => $this->gmw_min_version,
					'notice'           => $details['notice'] = sprintf(
						__( '%1$s extension version %2$s requires GEO my WP plugin version %3$s or higher.', 'geo-my-wp' ),
						$this->name,
						$this->version,
						$this->gmw_min_version
					),
				);

				// display admin notice.
				add_action( 'admin_notices', array( $this, 'verify_activation_notice' ) );

				$verified['status'] = false;
			}

			// verify addon reqired version with this version of GEO my WP.
			if ( $verified['status'] && version_compare( $this->version, $this->min_version, '<' ) ) {

				$verified['details'] = array(
					'error'            => 'addon_version_mismatch',
					'required_version' => $this->min_version,
					'notice'           => sprintf(
						__( '%1$s extension requires an update to version %2$s.', 'geo-my-wp' ),
						$this->name,
						$this->min_version
					),
				);

				// display admin notice.
				add_action( 'admin_notices', array( $this, 'verify_activation_notice' ) );

				$verified['status'] = false;
			}

			// verify required themes, plugins and addons and return the status.
			if ( $verified['status'] && ! empty( $this->required ) ) {
				$verified = $this->verify_required();
			}

			// allow extensions do custom verify activation
			// if ( ! $this->custom_verify_activation() ) {
			// return false;
			// }
			// disable the addon if requirments did not match.
			if ( ! $verified['status'] ) {

				$this->status_details = $verified['details'];

				return 'disabled';
			}

			/**
			 * if this isn't a core addon, which means it is a WordPress plugin
			 *
			 * And is activated in WordPress, then we need to activate it in GEO my WP as well.
			 */
			if ( ! $this->is_core && self::is_plugin_active( $this->basename ) ) {
				return 'active';
			}

			// deactivate if status is missing or is not set to 'active'.
			if ( empty( GMW()->addons_status[ $this->slug ] ) || 'active' !== GMW()->addons_status[ $this->slug ] ) {

				return 'inactive';
			}

			// Everythis is good, active me.
			return 'active';
		}

		/**
		 * Verify required theme, extensions and plugins
		 *
		 * @return [type] [description]
		 */
		public function verify_required() {

			$verified = array(
				'status'  => true,
				'details' => false,
			);

			// default status.
			$status = true;

			// if theme is required.
			if ( ! empty( $this->required['theme'] ) ) {

				$required = $this->required['theme'];

				if ( empty( $required['version'] ) ) {

					$required['version'] = '1.0';
				}

				// get parent theme data.
				$theme = wp_get_theme( get_template() );

				// check template and version.
				if ( $theme->template != $required['template'] || version_compare( $theme->version, $required['version'], '<' ) ) {

					$type   = 'theme';
					$status = false;
				}
			}

			// verify required GEO my WP addons.
			if ( $status && ! empty( $this->required['addons'] ) ) {

				foreach ( $this->required['addons'] as $required ) {

					if ( empty( $required['version'] ) ) {

						$required['version'] = '1.0';
					}

					$required_addon = GMW()->addons[ $required['slug'] ];

					// check if addon active and its version.
					if ( 'active' !== $required_addon['status'] || version_compare( $required_addon['version'], $required['version'], '<' ) ) {

						$type   = 'addon';
						$status = false;

						break;
					}
				}
			}

			// verify required plugins.
			if ( $status && ! empty( $this->required['plugins'] ) ) {

				foreach ( $this->required['plugins'] as $required ) {

					if ( empty( $required['version'] ) ) {

						$required['version'] = '1.0';
					}

					if ( ! function_exists( $required['function'] ) && ! class_exists( $required['function'] ) ) {

						$type   = 'plugin';
						$status = false;

						break;
					}
				}
			}

			// if required did not meet, disable the extension.
			if ( ! $status ) {

				// error notice.
				if ( empty( $required['notice'] ) ) {

					$required['notice'] = sprintf(
						__( '%1$s extension requires additional %2$s. Contact support form more information.', 'geo-my-wp' ),
						$this->name,
						$type
					);
				}

				$verified['details'] = array(
					'error'            => $type . '_missing',
					'required_version' => $required['version'],
					'notice'           => $required['notice'],
				);

				$verified['status'] = false;
			}

			return $verified;
		}

		/**
		 * Allow plugins verify requierments, such as specific plugin or a version,
		 *
		 * Before addon/extension is being activated.
		 *
		 * @return true | false
		 */
		public function custom_verify_activation() {
			return true;
		}

		/**
		 * Add-on's min version admin notice
		 */
		public function verify_activation_notice() {
			?>
			<div class="error">
				<p><?php echo esc_html( $this->status_details['notice'] ); ?></p>
			</div>  
			<?php
		}

		/**
		 * Load plugin's text domain
		 *
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( $this->textdomain, false, dirname( plugin_basename( $this->full_path ) ) . '/languages/' );
		}

		/**
		 * Activate addon / extension
		 *
		 * @return [type] [description]
		 */
		/*
		public function activate_addon() {
			gmw_update_addon_status( $this->slug, 'active' );
		}*/

		/**
		 * Deactivate addon / extension
		 *
		 * @return [type] [description]
		 */
		
		public function deactivate_addon() {
			gmw_update_addon_status( $this->slug, 'inactive' );
		}

		/**
		 * When plugin first installed or updated.
		 */
		protected function installer() {

			$installed_versions = get_option( 'gmw_addons_version' );

			// install plugin.
			if ( empty( $installed_versions[ $this->slug ] ) ) {

				// performe upgrade tasks.
				$this->install( $this->version );

				// update new version in database.
				$installed_versions[ $this->slug ] = $this->version;

				// update add-on version.
				update_option( 'gmw_addons_version', $installed_versions );

				// update plugin if version changed.
			} elseif ( $installed_versions[ $this->slug ] != $this->version ) {

				// performe upgrade tasks.
				$this->upgrade( $installed_versions[ $this->slug ], $this->version );

				// update new version in database.
				$installed_versions[ $this->slug ] = $this->version;

				// update add-ons version in DB.
				update_option( 'gmw_addons_version', $installed_versions );
			}
		}

		/**
		 * Performe addon upgrade task when new version installed
		 *
		 * @param  string $new_version new version.
		 */
		protected function install( $new_version ) {
			return false;
		}

		/**
		 * Performe addon upgrade task when new version installed
		 *
		 * @param  string $previous_version previous version.
		 *
		 * @param  string $new_version      new version.
		 */
		protected function upgrade( $previous_version, $new_version ) {
			return false;
		}

		/**
		 * Initiate add-on updater if needed
		 *
		 * @return void
		 */
		public function addon_updater() {

			if ( class_exists( 'GMW_License' ) ) {

				$gmw_license = new GMW_License(
					$this->full_path,
					$this->item_name,
					$this->license_name,
					$this->version,
					$this->author,
					$this->api_url,
					$this->item_id,
					'gmw_action_links'
				);
			}
		}

		/**
		 * Generate add-on constants
		 *
		 * Constant will begin with GMW_ and the addon prefix. EX. GMW_PT_.
		 *
		 * @return void
		 */
		public function constants() {

			// deafult add-ons prefix to be used with constants.
			$this->gmw_px = 'GMW_' . strtoupper( $this->prefix );

			define( $this->gmw_px . '_VERSION', $this->version );
			define( $this->gmw_px . '_FILE', $this->full_path );
			define( $this->gmw_px . '_PATH', $this->plugin_dir );
			define( $this->gmw_px . '_URL', $this->plugin_url );
		}


		/**
		 * Generate admin menu items
		 *
		 * @since 3.0
		 *
		 * @param  array $items
		 *
		 * @return array
		 */
		/*
		public function admin_menu_items( $items ) {

			// Loop through multi-array for multiple menu item
			if ( ! empty( $this->menu_items[0] ) && is_array( $this->menu_items[0] ) ) {

				foreach ( $this->menu_items as $key => $item ) {

					if ( empty( $item['menu_slug'] ) ) {
						return;
					}

					$items[$item['menu_slug']] = $item;
				}

			// generate single menu item
			} elseif ( ! empty( $this->menu_items['menu_slug'] ) ) {

				$items[$this->menu_items['menu_slug']] = $this->menu_items;
			}

			return $items;
		} */

		/**
		 * Generate admin settings groups
		 *
		 * @since 3.0
		 *
		 * @return array
		 */
		public function init_objects() {

			$objects = $this->set_objects();

			// Loop through multi-array for multiple menu item.
			if ( ! empty( $objects[0] ) && is_array( $objects[0] ) ) {

				foreach ( $objects as $key => $object ) {

					if ( empty( $object['slug'] ) ) {
						return;
					}

					$settings_groups[ $group['slug'] ] = $this->get_settings_group( $group );
				}

				// generate single menu item.
			} elseif ( ! empty( $groups['slug'] ) ) {

				$settings_groups[ $groups['slug'] ] = $this->get_settings_group( $groups );
			}

			return $settings_groups;
		}

		/**
		 * Generate menu item
		 *
		 * @param  array $menu_item menu item array.
		 *
		 * @return array
		 */
		protected function get_menu_item( $menu_item ) {

			return array(
				'parent_slug'       => ! empty( $menu_item['slug'] ) ? $menu_item['slug'] : 'gmw-extensions',
				'page_title'        => ! empty( $menu_item['page_title'] ) ? $menu_item['page_title'] : $this->name,
				'menu_title'        => ! empty( $menu_item['menu_title'] ) ? $menu_item['menu_title'] : $this->name,
				'capability'        => ! empty( $menu_item['capability'] ) ? $menu_item['capability'] : 'manage_options',
				'menu_slug'         => ! empty( $menu_item['menu_slug'] ) ? $menu_item['menu_slug'] : $this->slug,
				'callback_function' => $menu_item['callback_function'],
				'priority'          => ! empty( $menu_item['menu_slug'] ) ? $menu_item['menu_slug'] : $this->slug,
			);
		}

		/**
		 * Generate admin settings groups
		 *
		 * @since 3.0
		 *
		 * @param  array $menu_items menu items array.
		 *
		 * @return array
		 */
		public function admin_menu_items_init( $menu_items ) {

			if ( ( $items = $this->admin_menu_items() ) == false ) {
				return $menu_items;
			}

			// Loop through multi-array for multiple menu item.
			if ( ! empty( $items[0] ) && is_array( $items[0] ) ) {

				foreach ( $items as $key => $item ) {
					$menu_items[] = $item;
				}

				// generate single menu item.
			} else {

				$menu_items[] = $items;
			}

			return $menu_items;
		}

		/**
		 * Generate new settings group
		 *
		 * @param  array $group group array.
		 *
		 * @return array
		 */
		protected function get_settings_group( $group ) {

			// return button args.
			return array(
				'slug'     => ! empty( $group['slug'] ) ? $group['slug'] : $this->slug,
				'label'    => ! empty( $group['label'] ) ? $group['label'] : $this->name,
				'icon'     => ! empty( $group['icon'] ) ? $group['icon'] : 'location-outline',
				'fields'   => ! empty( $group['fields'] ) ? $group['fields'] : array(),
				'priority' => ! empty( $group['priority'] ) ? $group['priority'] : 99,
			);
		}

		/**
		 * Generate admin settings groups
		 *
		 * @since 3.0
		 *
		 * @param  array $settings_groups settings groups array.
		 *
		 * @return array
		 */
		public function admin_settings_groups_init( $settings_groups ) {

			$groups = $this->admin_settings_groups();

			// Loop through multi-array for multiple menu item.
			if ( ! empty( $groups[0] ) && is_array( $groups[0] ) ) {

				foreach ( $groups as $key => $group ) {

					if ( empty( $group['slug'] ) ) {
						return;
					}

					$settings_groups[ $group['slug'] ] = $this->get_settings_group( $group );
				}

				// generate single menu item.
			} elseif ( ! empty( $groups['slug'] ) ) {

				$settings_groups[ $groups['slug'] ] = $this->get_settings_group( $groups );
			}

			return $settings_groups;
		}

		/**
		 * Generate form settings groups
		 *
		 * @since 3.0
		 *
		 * @param  array $settings_groups settings groups array.
		 *
		 * @return array
		 */
		public function form_settings_groups_init( $settings_groups ) {

			$groups = $this->form_settings_groups();

			// Loop through multi-array for multiple menu item.
			if ( ! empty( $groups[0] ) && is_array( $groups[0] ) ) {

				foreach ( $groups as $key => $group ) {

					if ( empty( $group['slug'] ) ) {
						return;
					}

					$settings_groups[ $group['slug'] ] = $group;
				}

				// generate single menu item.
			} elseif ( ! empty( $groups['slug'] ) ) {

				$settings_groups[ $groups['slug'] ] = $groups;
			}

			return $settings_groups;
		}

		/**
		 * Create new form button
		 *
		 * @param  array $button buttom.
		 *
		 * @return array
		 */
		protected function get_form_button( $button ) {

			$component = '';

			if ( ! empty( $button['component'] ) ) {

				$component = $button['component'];

				if ( empty( $button['object_type'] ) ) {
					$button['object_type'] = GMW()->object_types[ $component ];
				}
			} else {

				$component = $button['slug'];

				if ( empty( $button['object_type'] ) ) {
					$button['object_type'] = $this->object_type;
				}
			}

			// return button args.
			return array(
				'slug'        => ! empty( $button['slug'] ) ? $button['slug'] : $this->slug,
				'addon'       => $this->slug,
				'component'   => $component,
				'object_type' => $button['object_type'],
				'name'        => ! empty( $button['name'] ) ? $button['name'] : $this->name,
				'prefix'      => ! empty( $button['prefix'] ) ? $button['prefix'] : $this->prefix,
				'priority'    => ! empty( $button['priority'] ) ? $button['priority'] : 99,
			);
		}

		/**
		 * Generate new form buttons from array of args
		 *
		 * @since 3.0
		 *
		 * @param  array $form_buttons button array.
		 *
		 * @return array
		 */
		public function form_buttons_init( $form_buttons ) {

			$buttons = $this->form_buttons();

			// Generate multiple button using multi-array.
			if ( ! empty( $buttons[0] ) && is_array( $buttons[0] ) ) {

				foreach ( $buttons as $button ) {

					if ( empty( $button['slug'] ) ) {
						return;
					}

					$form_buttons[ $button['slug'] ] = $this->get_form_button( $button );
				}

				// generate single button from an array.
			} elseif ( ! empty( $buttons['slug'] ) ) {

				$form_buttons[ $buttons['slug'] ] = $this->get_form_button( $buttons );
			}

			return $form_buttons;
		}

		/**
		 * Pre init execution. Runs in front and back-end. Gets executed before all init functions.
		 *
		 * Perform tasks that must be done prior to init.
		 *
		 * @since 3.0
		 */
		public function pre_init() {}

		/**
		 * Include widgets files
		 *
		 * @since 3.0
		 */
		public function init_widgets() {}

		/**
		 * Admin only pre-init execution.
		 *
		 * @since 3.0
		 */
		public function pre_init_admin() {}

		/**
		 * Pre init front-end only.
		 *
		 * @since 3.0
		 */
		public function pre_init_frontend() {}

		/**
		 * Plugin initialization.
		 */
		public function init() {

			// runs during ajax.
			if ( defined( 'DOING_AJAX' ) ) {

				$this->init_ajax();
			}

			// in admin.
			if ( IS_ADMIN ) {

				$this->init_admin();

				// fron-end.
			} else {

				$this->init_frontend();
			}
		}

		/**
		 * Initialization code in admin...
		 */
		public function init_admin() {}

		/**
		 * Initialization code in the front-end
		 */
		protected function init_frontend() {}

		/**
		 * Initialization code in AJAX mode
		 */
		protected function init_ajax() {}

		/**
		 * Enqueue scripts
		 */
		public function enqueue_scripts() {}
	}

endif;
