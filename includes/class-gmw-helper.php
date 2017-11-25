<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * GMW_Helper class
 * 
 */
class GMW_Helper {

	public function __construct() {}

	/**
	 * Verify if add-on is active
	 * 
	 * @param  [array] $addon
	 * @return [boolean]      
	 */
	/*static function get_addon_data( $addon ) {
		
		if ( ! empty( GMW()->addons[$addon] ) )  {
			return GMW()->addons[$addon];
		} else {
			return false;
		}
	} */

	/**
	 * Verify if add-on is active
	 * 
	 * @param  [array] $addon
	 * @return [boolean]      
	 */
	static function is_addon_active( $addon = false ) {

		if ( ! empty( GMW()->addons_status[$addon] ) && GMW()->addons_status[$addon] == 'active' && ! isset( $_POST['gmw_premium_license'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update forms cache
	 * 
	 * @return [type] [description]
	 */
	static function update_forms_cache() {

		global $wpdb;

		$forms = $wpdb->get_results( "SELECT ID, data FROM {$wpdb->prefix}gmw_forms", ARRAY_A );

		$output = array();

		foreach ( $forms as $form ) {
			$output[$form['ID']] = unserialize( $form['data'] );
		}

		$forms = $output;

		wp_cache_set( 'all_forms', $forms, 'gmw_forms' );
	}

	/**
	 * Get all forms from database
	 * 
	 * @return array of forms
	 */
	static function get_forms() {
		
		$forms = wp_cache_get( 'all_forms', 'gmw_forms' );

		if ( false === $forms ) {
			
			global $wpdb;

			$forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}gmw_forms", ARRAY_A );

			$output = array();

			foreach ( $forms as $form ) {
				
				// if happened that form has no data we need to apply the defaults
				if ( empty( $form['data'] ) ) {
					$form['data'] = serialize( GMW_Forms::new_form_defaults( array() ) );
				}

				$data = maybe_unserialize( $form['data'] );
				
				$output[$form['ID']] = array_merge( $form, $data );
				
				unset( $output[$form['ID']]['data'] );
			}

			$forms = $output;

			wp_cache_set( 'all_forms', $forms, 'gmw_forms' );
	    } 

		return ! empty( $forms ) ? $forms : false;
	}

	/**
	 * Get specific form by form ID
	 * 
	 * @param  boolean $formID - form ID
	 * 
	 * @return array specific form if form ID pass otherwise all forms
	 */
	static function get_form( $form_id = false ) {
		
		absint( $form_id );

		// abort if no ID passes
		if ( empty( $form_id ) ) {

			return false;
		}

		$form = wp_cache_get( $form_id, 'gmw_forms' );

		if ( false === $form ) {
	
			global $wpdb;

			$form = $wpdb->get_row( 
				$wpdb->prepare( 
					"SELECT * FROM {$wpdb->prefix}gmw_forms WHERE ID = %d", $form_id 
				), ARRAY_A 
			);

			if ( ! empty( $form ) ) {
				
				// if happened that form has no data we need to apply the defaults
				if ( empty( $form['data'] ) ) {
					$form['data'] = serialize( GMW_Forms::new_form_defaults( array() ) );
				}

				$form = array_merge( $form, maybe_unserialize( $form['data'] ) );
				
	 			unset( $form['data'] );

	 			wp_cache_set( $form_id, $form, 'gmw_forms' );
	 		
	 		} else {

	 			$form = false;

	 			wp_cache_delete( $form_id, 'gmw_forms' );
	 		}
	    }

		return ! empty( $form ) ? $form : false;
	}

	/**
	 * GEt add-on's template files.
	 *
	 * The functions will resturn array of template files from the plugin's folder 
	 * as well as custom template files from the themes folder.
	 *
	 * @since 3.0
	 * 
	 * @param  string $addon       add-on's slug
	 * @param  string $folder_name folder name ( ex. search-results, search-forms... ).
	 * @param  array  $default     array( key => value ) for default value.
	 *                             
	 * @return array  list of tempaltes
	 */
	static function get_addon_templates( $addon = '', $folder_name = '', $default = array() ) {
		
		// abort if addon is inactive
		if ( ! GMW_Helper::is_addon_active( $addon ) || empty( $folder_name ) ) {
			return array();
		}

		// addon data
		$addon_data = gmw_get_addon_data( $addon );

		$themes = array();

		if ( is_array( $default ) && ! empty( $default ) ) {
			$themes = $default;
		}

		// get templates from plugin's folder
		foreach ( glob( $addon_data['plugin_dir'].'/templates/'.$folder_name.'/*', GLOB_ONLYDIR ) as $dir ) {
			$themes[basename( $dir )] = basename( $dir );
		}
		
		// look for custom tempalte folder name.
		if ( empty( $addon_data['templates_folder'] ) ) {
			return $themes;
		}

		// look for custom templates
		$custom_templates = glob( STYLESHEETPATH . '/geo-my-wp/'.$addon_data['templates_folder'].'/'.$folder_name.'/*', GLOB_ONLYDIR );
		
		// append custom templates from theme/child theme folder if found
		if ( ! empty( $custom_templates ) ) {
			foreach ( $custom_templates as $dir ) {
				$themes['custom_'.basename( $dir )] = 'Custom: '.basename( $dir );
			}
		}
		
		return $themes;	
	}

	/**
	 * get tempalte file and its stylesheet
	 *
	 * @since 3.0
	 * 
	 * @param  string $addon         the slug of the add-on which the tmeplate file belongs to.
	 * @param  string $folder_name   folder name ( search-forms, search-results... ).
	 * @param  string $template_name tempalte name
	 * @return 
	 */
	static function get_template( $addon = 'posts_locator', $folder_name = 'search-forms', $template_name = 'default' ) {
		
		// abort if addon is inactive or folder is missing
		if ( ! gmw_is_addon_active( $addon ) || empty( $folder_name ) || empty( $template_name ) ) {
			return false;
		}

		// get addon data
		$addon_data = gmw_get_addon_data( $addon );

		$output = array();

		// Get custom template and css from child/theme folder
		if ( strpos( $template_name, 'custom_' ) !== false ) {

			$template_name  		     = str_replace( 'custom_', '', $template_name );
			$output['stylesheet_handle'] = "gmw-{$addon_data['prefix']}-{$folder_name}-custom-{$template_name}";
			$output['stylesheet_url']	 = get_stylesheet_directory_uri(). "/geo-my-wp/{$addon_data['templates_folder']}/{$folder_name}/{$template_name}/css/style.css";
			$output['content_path'] 	 = STYLESHEETPATH . "/geo-my-wp/{$addon_data['templates_folder']}/{$folder_name}/{$template_name}/content.php";

			// for previous version of GEO my WP. Need to rename all custom tempalte files to content.php
			// to be removed
			if ( ! file_exists( $output['content_path'] ) ) {

				if ( $folder_name == 'search-forms' ) {
					$output['content_path'] = STYLESHEETPATH . "/geo-my-wp/{$addon_data['templates_folder']}/{$folder_name}/{$template_name}/search-form.php";
				} elseif ( $folder_name == 'search-results' ) {
					$output['content_path'] = STYLESHEETPATH . "/geo-my-wp/{$addon_data['templates_folder']}/{$folder_name}/{$template_name}/results.php";
				}
			}
		
		// load template files from plugin's folder
		} else {
			$output['stylesheet_handle'] = "gmw-{$addon_data['prefix']}-{$folder_name}-{$template_name}";
			$output['stylesheet_url'] 	 = $addon_data['plugin_url']."/templates/{$folder_name}/{$template_name}/css/style.css";
			$output['content_path']      = $addon_data['plugin_dir']."/templates/{$folder_name}/{$template_name}/content.php";
		}

		return $output;
	}
}