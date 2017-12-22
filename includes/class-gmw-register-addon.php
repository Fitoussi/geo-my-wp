<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * GMW_Register_Addon class
 *
 * Register new add-on
 */
class GMW_Register_Addon {

	/********** required variables ********/

	/**
	 * Add-on's slug. 
	 * 
	 * Identifire to be used with URL, settings, add-on setup... Ex. "posts_locator"
	 * 
	 * @var string
	 */
	public $slug = "";

	/**
	 * Add-on's name. 
	 * 
	 * To be used on GEO my WP pages, Add-ons page... Ex. "Post Types Locator".
	 * 
	 * @var string
	 */
	public $name = "";

	/**
	 * Add-on prefix. 
	 * 
	 * To be used with hooks, CLASS/ID tags... Ex. if add-on's name is Posts Locator the prefix can be "pl".
	 * 
	 * @var string
	 */
	public $prefix = "";

	/**
	 * Add-on's version
	 * @var string
	 */
	public $version = "1.0";

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
    public $author = "";

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
	 * @var string
	 */
	public $item_id = null;

	/**
	 * URL of the site hosting the add-on ( currently works with geo my wp hosted add-on only ).
	 * @var string
	 */
	private $api_url = 'https://geomywp.com';
	

	/********** Optional variables ************/

	/**
	 * Object type 
	 * 
	 * if the add-ons will use its own object type for location. For example, post, member, user....
	 * 
	 * @var boolean | string
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
	public $description = "";

    /**
     * Link to the addon's detailes/purchase page. Will show in Extensions page.
     * 
     * @var string
     */
    public $addon_page = "";

	/**
	 * Link to support page/site. Will show in Extensions page.
	 * @var string
	 */
	public $support_page = "";

	/**
	 * Link to documentaion page/site. Will show in Extensions page.
	 * @var string
	 */
	public $docs_page = "";

	/**
	 * Text domain. Set to false if not being used.
	 * 
	 * @var boolean
	 */
	public $textdomain = false;

	/**
	 * Full path the the plugin. Usually will be __FILE__
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
	 * plugin_dir
	 *
	 * Will be generated automatically if left blank
	 * 
	 * @var string
	 */
	public $plugin_dir = false;

	/**
	 * plugin_url
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
	 * @var string
	 */
	public $gmw_min_version = GMW_VERSION;

	/**
	 * Set to true if the extension uses template files
	 *
	 * When enabled, the template files in the extension must be 
	 *
	 * in the extension's-folder/templates/.
	 * 
	 * The name of the folder which holds the custom templates files will be genrated
	 * 
	 * from the extension slug when underscore will be replaced with a dash. for example
	 * 
	 * for the Post Types locator extension with the slug posts_locator, the folder name will be
	 * 
	 * posts-locator. And this custom folder with the template fiels should be placed in
	 *
	 * the theme's-folder/geo-my-wp/
	 *
	 * In the future it might be possible to change the name of the template and custom
	 * 
	 * template files using the arguments below.
	 * 
	 * @var boolean
	 */
	public $template_files = false;

	/**
	 * add-on's folder name
	 *
	 * The folder that holds the template files.
	 * 
	 * @var string | boolean
	 *
	 * --- Not being used at the moment. ---
	 */
	public $templates_folder = false;

	/**
	 * add-on's custom folder name
	 *
	 * The folder that holds custom template files and other custom file.
	 * 
	 * @var string | boolean
	 *
	 * --- Not being used at the moment. ---
	 */
	public $custom_templates_folder = false;

	/**
	 * Array of extension ( slug ) required for this extension to work
	 * 
	 * @var array
	 */
	public $required = array();

	/**
	 * required function
	 *
	 * @return [type] [description]
	 * 
	 */
	public function required() {
		return false;
	}

	/*
     *  Create GEO my WP submenu item
     *  
     *  To create a submenu you will need to pass an array with the following arg:
     *
     *  parent_slug - the parent menu. By default, and in ost cases, it will be GEO my WP menu item ( 'gmw-extensions' ).
     *  page_title - The menu item's page title ( ex. Tools Page )
     *  menu_title - The menu item's title ( ex. Tools )
     *  capability - User Capability that can access the menu item ( default is 'manage_options ).
     *  menu_slug -  menu slug ( ex. gmw_tools ).
     *  callback_function - the callback function for the menu items ( ex. gmw_get_tools_page ). IT can also be a class method by passing an array with the name of the class and the method. For ex. array( 'Tools_Page', 'output' )
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
     *  );
     *  
     * 	More information about creating submenu items can be found here -> https://codex.wordpress.org/Function_Reference/add_submenu_page
     * 	
     *  You can also create multiple menu items by passing a multidimensional array or items.
     *  
     */
	public $menu_items = false;

	/*
     *  Create GEO my WP "New form" button for your add-on
     *  
     *  pass an array with the following arg:
     *
     *  slug - the slug for the button. Can be as the slug of the extension unless the extension creates multiple buttons.
     *  name - the name/title of the button.
     *  prefix - a prefix for the button and form( ex. for post_type a good prefix would be "pt" ). Leave blank to use addon's prefix.
     *  priority - the priority the button will show in the buttons dropdown
     *
     *  example :
     *  
     *  array(
     *  	'slug'       => 'posts_locator'
     *      'name'       => 'Posts Locator',
     *      'prefix'     => pt,
     *      'priority'   => 1
     *  );
     *   
     *  You can also create multiple buttons by passing a multidimensional array or buttons.
     *  
     */
	public $form_buttons = false;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
			
		// add object type to global
		if ( ! empty( $this->object_type ) ) {
			GMW()->object_types[] = $this->object_type;
		}

		// add object type to global
		if ( is_multisite() && absint( $this->locations_blog_id ) ) {
			GMW()->locations_blogs[$this->object_type] = $this->locations_blog_id;
		}

		// plugin basename
		if ( ! $this->basename ) {
			$this->basename = plugin_basename( $this->full_path );
		}

		// plugin dir
		if ( ! $this->plugin_dir ) {
			$this->plugin_dir = untrailingslashit( plugin_dir_path( $this->full_path ) );
		}

		// plugin URL
		if ( ! $this->plugin_url ) {
			$this->plugin_url = untrailingslashit( plugins_url( basename( plugin_dir_path( $this->full_path ) ), dirname( $this->full_path ) ) );
		}

		// appened addon to loaded addons 
		GMW()->loaded_addons[] = $this->slug;

		// appened addon to core addons
		if ( $this->is_core ) {
			GMW()->core_addons[] = $this->slug;
		}

		// load textdomain if needed
		if ( ! empty( $this->textdomain ) ) {

			if ( did_action( 'plugins_loaded' ) === 1 ) { 
				self::load_textdomain();
			} else {
				add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			}
		}

		/** 
		 * we try to prevent the below from running in front-end to save on memory and performance.
		 *
		 * i.e collecting data and verifying activation.
		 * The addon data will be collected while in admin and saved in options table so it can be used 
		 * in the fron-end. The data is collected when activating/deactivating plugins.
		 * if the data does not exists in option, it will be then retrived using the below in the front-end as well.
		 * 
		 */
		if ( IS_ADMIN || empty( GMW()->addons[$this->slug] ) || empty( GMW()->addons[$this->slug]['status'] ) ) {

			// default status and details
			$this->status 		  = 'inactive';
			$this->status_details = false;

			// min version of the addon required to work with the current version of GEO my WP
			$this->min_version = ! empty( GMW()->required_versions[$this->slug] ) ? GMW()->required_versions[$this->slug] : '1.0';

			// required theme, addons and plugins
			// check if passed via array. Otherwise, maybe via function
			if ( empty( $this->required ) ) {
				$this->required = $this->required();
			} 

			// verify activation and get status
			$this->status = self::verify_activation();

			// verify that addon status matches in database and in this object
			// it can be different if a plugin or a theme that the addon depends on was activated/deactivated
			// In this case we will update the status in database
			if ( empty( GMW()->addons_status[$this->slug] ) || $this->status != GMW()->addons_status[$this->slug] ) {
				gmw_update_addon_status( $this->slug, $this->status, $this->status_details );
			}
			
			// generate addon data
			GMW()->addons[$this->slug] = self::setup_addon_data();

			// only in admin
			if ( IS_ADMIN ) {

				// generate license data
				GMW()->licenses[$this->slug] = self::setup_license_data();

				// activate addon when WordPress plugin activated
				register_activation_hook( $this->full_path, array( $this, 'activate_addon' ) );

				// deactivate addon when WordPress plugin deactivated
				register_deactivation_hook( $this->full_path, array( $this, 'deactivate_addon' ) );

				// run installer. 
				// check for add-ons data if missing, when probably first installed, or if plugin updated
				$this->installer();

				// load license handler
				if ( ! empty( $this->license_name ) ) {
					self::addon_updater();
				}
			} 

		// if addon data exists in databases we only need to get the status.
		} else {
			$this->status = GMW()->addons[$this->slug]['status'];
		}

		// abort if plugin is not active
		// no need to proceed.
		if ( $this->status != 'active' ) {
			return;
		}

		// enqueue scripts admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// enqueue scripts front-end
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// setup constants
		$this->constants();

		// pre initial function for admin and front-end
		$this->pre_init();

		// run add-on in init mode
		add_action( 'init', array( $this, 'init' ) );

		// if in admin
		if ( IS_ADMIN ) {

			// generate admin menu items
			if ( ! empty( $this->menu_items ) && is_array( $this->menu_items ) ) {
				add_filter( 'gmw_admin_menu_items', array( $this, 'admin_menu_items' ) );
			}

			// create form button
			if ( ! empty( $this->form_buttons ) && is_array( $this->form_buttons ) ) {
				add_filter( 'gmw_admin_new_form_button', array( $this, 'new_form_buttons' ) );
			}

			// pre init admin
			$this->pre_init_admin();

		// pre init frontend
		} else {

			// pre init front-end only
			$this->pre_init_frontend();
		}

		// include widgets
		$this->init_widgets();
	}

	/**
	 * Collect add-on's data
	 * 
	 * @return array pass to $_register_addons
	 */
	public function setup_addon_data() {

		// make sure custom template folder exists if template folder exsist as well
		// -- not being used at the momoent.
		/*if ( $this->templates_folder != false && $this->custom_templates_folder == false ) {
			$this->custom_templates_folder = str_replace( '_', '-', $this->slug );
		} */

		// for now, the template files are being generated by GEO my WP.
		// It might be possible to control the folders name in the future.
		if ( $this->template_files ) {
			$this->templates_folder = 'templates';
			$this->custom_templates_folder = str_replace( '_', '-', $this->slug );	
		} else {
			$this->templates_folder = '';
			$this->custom_templates_folder = '';	
		}

    	return array(
    		'slug'    	   				=> $this->slug,
    		'status'	   				=> $this->status,
    		'name'   	   				=> $this->name,
    		'prefix'					=> $this->prefix,
			'version' 	   				=> $this->version,	
			'is_core'         			=> $this->is_core,
			'object_type'				=> $this->object_type,
			'locations_blog_id'			=> $this->locations_blog_id,
			'full_path'    				=> $this->full_path,
			'basename'     				=> $this->basename,
			'plugin_dir'				=> $this->plugin_dir,
			'plugin_url'				=> $this->plugin_url,
			'templates_folder'  	   	=> $this->templates_folder,
			'custom_templates_folder'  	=> $this->custom_templates_folder
    	);
    }

    /**
	 * Collect add-on's data
	 * 
	 * @return array pass to $_register_addons
	 */
	public function setup_license_data() {

    	return array(
			'author'  	   		=> $this->author,
			'description'    	=> $this->description,
			'addon_page'		=> $this->addon_page,
			'docs_page'		    => $this->docs_page,
			'support_page'		=> $this->support_page,				
			'required' 	   		=> $this->required,
			'license_name' 	   	=> $this->license_name,
			'item_name'         => $this->item_name,
            'item_id'      		=> $this->item_id,			
            'status_details'	=> $this->status_details			
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
  			'details' => false
  		);

		// verify GEO my WP min version 
		if ( ! $this->is_core && version_compare( GMW_VERSION, $this->gmw_min_version, '<' ) ) {

			$verified['details'] = array(
				'error'  		   => 'gmw_version_mismatch',
				'required_version' => $this->gmw_min_version,
				'notice' 		   => $details['notice'] = sprintf( 
					__( '%s extension version %s requires GEO my WP plugin version %s or higher.', 'GMW' ), 
					$this->name, 
					$this->version, 
					$this->gmw_min_version 
				)
			);

			// display admin notice
            add_action( 'admin_notices', array( $this, 'min_version_notice' ) );      

            $verified['status'] = false;
        }

        // verify addon reqired version with this version of GEO my WP
        if ( $verified['status'] && version_compare( $this->version, $this->min_version, '<' ) ) {
			
			$verified['details'] = array(
				'error'  		   => 'addon_version_mismatch',
				'required_version' => $this->min_version,
				'notice' 		   => sprintf( 
					__( '%s extension requires an update to version %s.', 'GMW' ), 
					$this->name,
					$this->min_version
				)
			);

			// display admin notice
			add_action( 'admin_notices', array( $this, 'min_version_notice' ) );      
            
            $verified['status'] = false;
		} 

		if ( $verified['status'] && ! empty( $this->required ) ) {
			$verified = $this->verify_required();
		}

		// allow extensions do custom verify activation
		//if ( ! $this->custom_verify_activation() ) {

		//	return false;
		//}

		// disable addon
		if ( ! $verified['status'] ) {
			
			$this->status_details = $verified['details'];

			return 'disabled'; 
		}

		if ( empty( GMW()->addons_status[$this->slug] ) || GMW()->addons_status[$this->slug] != 'active' ) {
			
			return 'inactive';
		}
		
		// activate addon
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
			'details' => false
		);

    	// default status
    	$status = true;

    	// if theme is required
		if ( ! empty( $this->required['theme'] ) ) {

			$required = $this->required['theme'];

			if ( empty( $required['version'] ) ) {

				$required['version'] = '1.0';
			}

			// get parent theme data
			$theme = wp_get_theme( get_template() );

			// check template and version
    		if ( $theme->template != $required['template'] || version_compare( $theme->version, $required['version'], '<' ) ) {

    			$type   = 'theme';		
    			$status = false;
    		}
		}

		// verify required addons
		if ( $status && ! empty( $this->required['addons'] ) ) {

			foreach ( $this->required['addons'] as $required ) {

				if ( empty( $required['version'] ) ) {

					$required['version'] = '1.0';
				}

				$required_addon = GMW()->addons[$required['slug']];

				// check if addon active and its version
				if ( $required_addon['status'] != 'active' || version_compare( $required_addon['version'], $required['version'], '<' ) ) {

					$type   = 'addon';
	    			$status = false;

	    			break;
	    		}
	    	}
		}

		// verify required plugins
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

		// if required did not meet, disable the extension
		if ( ! $status ) {
			
			// error notice
			if ( empty( $required['notice'] ) ) {

				$required['notice'] = sprintf( 
					__( '%s extension requires additional %s. Contact support form more information.', 'GMW' ), 
					$this->name, 
					$type
				);	
			}
			
			$verified['details'] = array(
				'error' 		   => $type.'_missing',
				'required_version' => $required['version'],
				'notice' 		   => $required['notice']
			);
			
			$verified['status'] = false;
		}

		return $verified;
    }

    /**
     * Allow plugins verify requierments, such as specific plugin or a version, before addon/extension
     * is being activated
     * 
     * @return true | false
     */
    public function custom_verify_activation() {
    	return true;
    }

    /**
     * Add-on's min version admin notice
     * 
     * @return [type] [description]
     */
    public function min_version_notice() {
        ?>
        <div class="error">
            <p><?php echo $this->status_details['notice']; ?></p>
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
	 * @return [type] [description]
	 */
	public function activate_addon() {
		gmw_update_addon_status( $this->slug, 'active' );
	}

	/**
	 * Deactivate addon / extension
	 * @return [type] [description]
	 */
	public function deactivate_addon() {
		gmw_update_addon_status( $this->slug, 'inactive' );
    }

	/**
	 * When plugin first installed or updated
	 * 
	 * @return [type] [description]
	 */
	protected function installer() {

		$installed_versions = get_option( 'gmw_addons_version' );
		
		// install plugin
		if ( empty( $installed_versions[$this->slug] ) ) {

			// performe upgrade tasks
			$this->install( $this->version );

			// update new version in database
			$installed_versions[$this->slug] = $this->version;

			// update add-on version
			update_option( 'gmw_addons_version', $installed_versions );
			
		// update plugin if version changed
		} elseif ( $installed_versions[$this->slug] != $this->version ) {

			// performe upgrade tasks
			$this->upgrade( $installed_versions[$this->slug], $this->version );

			// update new version in database
			$installed_versions[$this->slug] = $this->version;

			// update add-ons version in DB
			update_option( 'gmw_addons_version', $installed_versions );
		}
	}

	/**
	 * Performe addon upgrade task when new version installed
	 * 
	 * @param  string $previous_version previous version
	 * @param  string $new_version      new version
	 * @return void            
	 */
	protected function install( $new_version ) {
		return;
	}

	/**
	 * Performe addon upgrade task when new version installed
	 * @param  string $previous_version previous version
	 * @param  string $new_version      new version
	 * @return void            
	 */
	protected function upgrade( $previous_version, $new_version ) {
		return;
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
				$this->item_id 
			);
		}
	}

    /**
     * Generate add-on constants
     *
     * constant will begin with GMW_ and the addon prefix. EX. GMW_PT_
     * 
     * @return void
     */
	public function constants() {

		// deafult add-ons prefix to be used with constants
		$this->gmw_px = 'GMW_'.strtoupper( $this->prefix );

		define( $this->gmw_px.'_VERSION', $this->version );
        define( $this->gmw_px.'_FILE', $this->full_path );
        define( $this->gmw_px.'_PATH', $this->plugin_dir );
        define( $this->gmw_px.'_URL', $this->plugin_url );    
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
	}

	/**
	 * Create new form button
	 * 	
	 * @param  array $button 
	 * @return array
	 */
	protected function get_form_button( $button ) {

		// return button args
    	return array(
    		'slug'	   => ! empty( $button['slug'] ) ? $button['slug'] : $this->slug,
            'addon'    => $this->slug,
            'name'     => ! empty( $button['name'] ) ? $button['name'] : $this->name,
            'prefix'   => ! empty( $button['prefix'] ) ? $button['prefix'] : $this->prefix,
            'priority' => ! empty( $button['priority'] ) ? $button['priority'] : 99,
        );
	}

	/**
	 * Generate new form buttons from array of args
	 *
	 * @since 3.0 
	 * 
	 * @param  array $buttons 
	 * 
	 * @return array          
	 */
	public function new_form_buttons( $buttons ) {

		// Generate multiple button using multi-array
		if ( ! empty( $this->form_buttons[0] ) && is_array( $this->form_buttons[0] ) ) {

			foreach ( $this->form_buttons as $key => $button ) {

				if ( empty( $button['slug'] ) ) {
					return;
				}

				$buttons[$key] = $this->get_form_button( $button );
			}
			
		// generate single button from an array
		} elseif ( ! empty( $this->form_buttons['slug'] ) ) {

			$buttons[$this->form_buttons['slug']] = $this->get_form_button( $this->form_buttons );
		}
	    	
        return $buttons;
	}

	/**
	 * Pre init execution. Runs in front and back-end. Gets executed before all init functions.
	 * 
	 * Perform tasks that must be done prior to init.
	 *
	 * @since 3.0
	 * 
	 */
	public function pre_init() {}

	/**
	 * Include widgets files
	 *
	 * @since 3.0 
	 * 
	 * @return [type] [description]
	 * 
	 */
	public function init_widgets() {}

	/**
	 * admin only pre-init execution. 
	 * 	
	 * @since 3.0
	 * 
	 */
	public function pre_init_admin() {}

	/**
	 * Pre init front-end only.
	 * 
	 * @since 3.0
	 * 
	 */
	public function pre_init_frontend() {}

	/**
	 * Plugin initialization.
	 */
	public function init() {

		// runs during ajax
		if ( defined( 'DOING_AJAX' ) ) {

			$this->init_ajax();
		} 

		// in admin
		if ( IS_ADMIN ) {

			$this->init_admin();

		// fron-end
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
	 * enqueue_scripts
	 * 
	 * @return [type] [description]
	 */
	public function enqueue_scripts() {}	
}