<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Installer' ) ) :

/**
 * GMW_Installer class
 *
 * Create and updated tables, import forms....
 *
 * @since  3.0
 * 
 * @author Eyal Fitoussi
 */
class GMW_Installer {

	/**
	 * Run installer
	 */
	public static function init() {

		// update license keys
		self::update_license_keys();

		// create database tables
		self::create_tables();

		// schedule cron jobs
		self::schedule_cron();	

		// run update if version changed
		if ( version_compare( GMW_VERSION, get_option( 'gmw_version' ) , '>' ) ) {
			self::update();
		}
		
		// get db version
		$gmw_db_version = get_option( 'gmw_db_version' );
		
		// upgrade db if needed
		if ( empty( $gmw_db_version ) || version_compare( GMW_DB_VERSION, $gmw_db_version, '>' ) ) {
			self::upgrade_db();
		}

		// update versions
		update_option( 'gmw_db_version', GMW_DB_VERSION );
		update_option( 'gmw_version', GMW_VERSION );	
	}

	/**
	 * Update loicense key database.
	 * 
	 * @return [type] [description]
	 */
	public static function update_license_keys() {

		if ( get_option( 'gmw_premium_plugin_status' ) != FALSE ) {
	
			$statuses 	  = get_option( 'gmw_premium_plugin_status' );
			$license_keys = get_option( 'gmw_license_keys' );

			$new_licenses = array();
			
			foreach ( $license_keys as $key => $value ) {

				if ( empty( $key ) ) {
					continue;
				}
				
				if ( ! is_array( $value ) ) {
					$new_licenses[$key] = array(
						'key' 	 => $value,
						'status' => ! empty( $statuses[$key] ) ? $statuses[$key] : 'inactive'
					);
				} else {
					$new_licenses[$key] = $value;
				}
			}
						
			update_option( 'gmw_license_keys', $new_licenses );
		}
	}

	/**
	 * Create GEO my WP database tables
	 * 
	 * @return [type] [description]
	 */
	public static function create_tables() {

		global $wpdb;

		// charset
		$charset_collate  = ! empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : "DEFAULT CHARACTER SET utf8";
		
		// collation
		$charset_collate .= ! empty( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : " COLLATE utf8_general_ci";

		// forms table name
		$forms_table = $wpdb->prefix . 'gmw_forms';
		
		// check if table exists already 
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$forms_table}'", ARRAY_A );
		
		// if form table not exists create it
		if ( count( $table_exists ) == 0 ) {

			// generate table sql
			$sql = "
				CREATE TABLE $forms_table (
				ID INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				slug VARCHAR( 50 ) NOT NULL,
				addon VARCHAR( 50 ) NOT NULL,
				name VARCHAR( 50 ) NOT NULL,
				title VARCHAR( 50 ) NOT NULL,
				prefix VARCHAR( 20 ) NOT NULL,
				data LONGTEXT NOT NULL,
				PRIMARY KEY ID (ID)
			) $charset_collate;";
	
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			// create database table
			dbDelta( $sql );

			// import existing forms to the new table
			self::import_forms();
		}

		// locations table name
		$locations_table = $wpdb->base_prefix . 'gmw_locations';

		// check if table already exists
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$locations_table}'", ARRAY_A );
		
		// create table if not already exists
		if ( count( $table_exists ) == 0 ) {

			// generate table sql
			$sql = "
				CREATE TABLE $locations_table (
				ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				object_type VARCHAR(20) NOT NULL,
				object_id BIGINT(20) UNSIGNED NOT NULL default 0,
				blog_id BIGINT(20) UNSIGNED NOT NULL default 0,
				user_id BIGINT(20) UNSIGNED NOT NULL default 0,
				parent BIGINT(20) UNSIGNED NOT NULL default 0,
				status INT(11) NOT NULL default 1,
				featured TINYINT NOT NULL default 0,
				title TEXT,
				latitude FLOAT( 10, 6 ) NOT NULL,
	  			longitude FLOAT( 10, 6 ) NOT NULL,
				street_number VARCHAR( 60 ) NOT NULL default '',
				street_name VARCHAR( 144 ) NOT NULL default '',
				street VARCHAR( 144 ) NOT NULL default '',
				premise VARCHAR( 50 ) NOT NULL default '',
				neighborhood VARCHAR( 96 ) NOT NULL default '',
				city VARCHAR( 128 ) NOT NULL default '',
				county VARCHAR( 128 ) NOT NULL default '',	
				region_name VARCHAR( 50 ) NOT NULL default '',
				region_code CHAR( 50 ) NOT NULL,
				postcode VARCHAR( 24 ) NOT NULL default '',
				country_name VARCHAR( 96 ) NOT NULL default '',
				country_code CHAR( 2 ) NOT NULL,
				address varchar( 255 ) NOT NULL default '',
				formatted_address VARCHAR( 255 ) NOT NULL,
				place_id VARCHAR( 255 ) NOT NULL,
				map_icon VARCHAR(50) NOT NULL ,
				created DATETIME NOT NULL default '0000-00-00 00:00:00',
				updated DATETIME NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY ID (ID),
				KEY coordinates (latitude,longitude),
				KEY latitude (latitude),
				KEY longitude (longitude),
				KEY city (city),
				KEY region (region_name),
				KEY postcode (postcode),
				KEY country (country_name),
				KEY country_code (country_code)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// create database table
			dbDelta( $sql );
		}
		
		// location meta table
		$location_meta_table = $wpdb->base_prefix . 'gmw_locationmeta';

		// check if table already exists
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '$location_meta_table'", ARRAY_A );

		// create table if not exists already
		if ( count( $table_exists ) == 0 ) {

			// generate table sql
			$sql = "
				CREATE TABLE $location_meta_table (
				meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				location_id BIGINT(20) UNSIGNED NOT NULL default 0,
				meta_key VARCHAR(255) NULL,
				meta_value LONGTEXT NULL,
				PRIMARY KEY meta_id (meta_id),
				KEY location_id (location_id),
				KEY meta_key (meta_key)
			) $charset_collate; ";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// create database table
			dbDelta( $sql );
		}

		// look for post types table
		$posts_table = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}places_locator'", ARRAY_A );

		// look for users table
		$members_table = $wpdb->get_results( "SHOW TABLES LIKE 'wppl_friends_locator'", ARRAY_A );

		// if any of the tables exist set an option that will rigger an admin notice
		// to import existing db tables
		if ( count( $posts_table ) != 0 || count( $members_table ) != 0 ) {
		    update_option( 'gmw_old_locations_tables_exist', true );
		}
	}
	
	/**
	 * Import existing forms to new database table created in version 3.0
	 * 
	 * @return [type] [description]
	 */
	public static function import_forms() {
	    
	    global $wpdb;

	     // get existing forms
	    $gmw_forms = get_option( 'gmw_forms' );
		
		// abort if not forms to import
		if ( empty( $gmw_forms ) ) {
			return;
		}

	    // look for forms table
		$forms_table  = $wpdb->prefix . 'gmw_forms';
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$forms_table}'", ARRAY_A );

		// abort if forms table not exists
		if ( count( $table_exists ) == 0 ) {

			trigger_error( 'GEO my WP Forms table not exists.', E_USER_NOTICE );

			return;
		}
		
		// loop through forms and insert into new table
		foreach ( $gmw_forms as $key => $form ) {

			if ( empty( $form['ID'] ) || empty( $form['addon'] ) ) {
				continue;
			}

			$form_id = ( int ) $form['ID'];
			unset( $form['ID'] );
			
			$addon = $form['addon'];
			unset( $form['addon'] );

			if ( ! empty( $form['form_type'] ) ) {

				$slug = $form['form_type'];
				unset( $form['form_type'] );

			} else {

				$slug = $addon;
			}

			if ( ! empty( $form['form_title'] ) ) {

				$form_name = $form['form_title'];
				unset( $form['form_title'] );

			} else {

				$form_name = '';
			}
			
			if ( ! empty( $form['name'] ) ) {
				
				$form_title = $form['name'];
				unset( $form['name'] );
			
			} else {
				$form_title = 'form_id_'.$form_id;
			}
			
			if ( ! empty( $form['prefix'] ) ) {

				$prefix = $form['prefix'];
				unset( $form['prefix'] );
			} else {

				$prefix = '';
			}

			// update the new form_submission tab
			$form['form_submission'] = array(
				'results_page'    => ! empty( $form['search_results']['results_page'] ) ? $form['search_results']['results_page'] : '',
				'per_page' 	      => ! empty( $form['search_results']['per_page'] )     ? $form['search_results']['per_page'] : '',
				'display_results' => '',
				'display_map'     => ! empty( $form['search_results']['display_map'] ) ? $form['search_results']['display_map'] : 'results'
			);

			$form['search_results']['image'] = array(
				'enabled' => '',
				'width'   => '200px',
				'height'  => '200px'
			);

			// update posts locator form data
			if ( $slug == 'posts' ) {

				if ( ! empty( $form['page_load_results']['display_posts'] ) ) {
					$form['page_load_results']['display_results'] = 1;
				}

				if ( ! empty( $form['search_results']['display_posts'] ) ) {
					$form['form_submission']['display_results'] = 1;
				}

				if ( ! empty( $form['search_results']['featured_image']['use'] ) ) {
					$form['search_results']['image']['enabled'] = 1;
					$form['search_results']['image']['height']  = $form['search_results']['featured_image']['height'];
					$form['search_results']['image']['width']   = $form['search_results']['featured_image']['width']; 
				}
			}

			// update friends locator form data
			if ( $slug == 'friends' ) {

				if ( ! empty( $form['page_load_results']['display_posts'] ) ) {
					$form['page_load_results']['display_results'] = 1;
				}

				if ( ! empty( $form['search_form']['profile_fields'] ) ) {
					$form['search_form']['profile_fields']['fields'] = $form['search_form']['profile_fields'];
				}

				if ( ! empty( $form['search_form']['profile_fields_date'] ) ) {
					$form['search_form']['profile_fields']['date_field'] = $form['search_form']['profile_fields_date'];
				}

				if ( ! empty( $form['search_results']['display_members'] ) ) {
					$form['form_submission']['display_results'] = 1;
				}

				if ( ! empty( $form['search_results']['avatar']['use'] ) ) {
					$form['search_results']['image']['enabled'] = 1;
					$form['search_results']['image']['height']  = $form['search_results']['avatar']['height'];
					$form['search_results']['image']['width']   = $form['search_results']['avatar']['width']; 
				}
			}

			$data = array(
				'ID'	 => $form_id,
				'slug'	 => $slug,
				'addon'  => $addon, 
				'name'   => $form_name,
				'title'  => $form_title,
				'prefix' => $prefix,
				'data'   => maybe_serialize( $form ),
			);

			// Insert form to database
			$wpdb->insert( 
				$forms_table, 
				$data, 
				array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' ) 
			);
		}

		// backup old forms in a new option to prevent the plugin
		// from trying to import forms again after it was already done.
		update_option( 'gmw_forms_old', $gmw_forms );
		delete_option( 'gmw_forms' );

		self::fix_forms_table();
	}

	/**
	 * Update data in forms table
	 * @return [type] [description]
	 */
	public static function fix_forms_table() {

		global $wpdb;

		// forms table name
		$forms_table = $wpdb->prefix . 'gmw_forms';
		
		// check if table exists already 
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$forms_table}'", ARRAY_A );
		
		// if form table not exists create it
		if ( count( $table_exists ) == 0 ) {

			trigger_error( 'GEO my WP Forms table not exists.', E_USER_NOTICE );

			return;
		}

		$wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   => 'posts_locator',
                'addon'  => 'posts_locator',
                'name'   => 'Posts Locator',
                'prefix' => 'pt'
            ), 
            array( 
            	'addon' => 'posts'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   => 'members_locator',
                'addon'  => 'members_locator',
                'name'   => 'Memebrs Locator',
                'prefix' => 'fl'
            ), 
            array( 
            	'addon' => 'friends'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
	}

	/**
	 * Run plugin's updates
	 * 
	 * @return [type] [description]
	 */
	public static function update() {}

	/**
	 * Updarte database tables if needed
	 * 
	 * @return [type] [description]
	 */
	public static function upgrade_db() {}

	/**
	 * Setup cron jobs
	 */
	private static function schedule_cron() {
		wp_clear_scheduled_hook( 'gmw_clear_expired_transients' );
		wp_schedule_event( time(), 'twicedaily', 'gmw_clear_expired_transients' );
	}
}

endif;