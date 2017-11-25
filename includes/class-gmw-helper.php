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

	/**
	 * [__construct description]
	 */
	public function __construct() {}

	/**
	 * Get the user's current location from cookies
	 * 
	 * @return [type] [description]
	 */
	public static function get_user_current_location() {

		// abort if user's location does not exist in cookies
		if ( empty( $_COOKIE['gmw_ul_lat'] ) || empty( $_COOKIE['gmw_ul_lng'] ) ) {
			return false;
		}

		$fields = array( 
			'street',
			'city',
			'region_name',
			'region_code',
			'postcode',
			'country_name',
			'country_code',
			'address',
			'formatted_address'
		);
		
		$location = wp_cache_get( 'gmw_user_current_location' );

		if ( false === $location ) {

			$location = ( object ) array();

			$location->lat = urldecode( $_COOKIE['gmw_ul_lat'] );
			$location->lng = urldecode( $_COOKIE['gmw_ul_lng'] );

			foreach ( $fields as $field ) {
			    
			    if ( ! empty( $_COOKIE['gmw_ul_'.$field] ) ) {
			        $location->$field = urldecode( $_COOKIE['gmw_ul_'.$field] );
			    } else {
			    	$location->$field = '';
			    }
			}	
			
			wp_cache_set( 'gmw_user_current_location', $location, '', 86400 );
		}

		return $location;
	}

	/**
	 * Get add-on's template files.
	 *
	 * The functions will resturn array of template files from the plugin's folder 
	 * as well as custom template files from the themes folder.
	 *
	 * @since 3.0
	 * 
	 * @param  string $addon       add-on's slug
	 * @param  string $folder_name folder name ( ex. search-results, search-forms... ).
	 * @param  string $iw_type     info-window type. Will be used when $folder_name is set to info-window
	 * @param  string $base_path   base path. Can be used when a single or multiple addons exist inside a another based addon
	 *                             
	 * @return array  list of templates
	 */
	public static function get_templates( $addon = '', $folder_name = 'search-forms', $iw_type = 'popup', $base_path = '' ) {
		
		// abort if addon is inactive
		if ( ! gmw_is_addon_active( $addon ) || empty( $folder_name ) ) {
			return array();
		}

		// addon data
		$addon_data    = gmw_get_addon_data( $addon );
		$custom_folder = $addon_data['custom_templates_folder'];

		if ( $base_path == '' ) {
			$path = $addon_data['plugin_dir'];
		} else {
			$path = $base_path.'/'.$custom_folder;
		}

		$themes = array();

		if ( $folder_name == 'info-window' ) {
			$url = $path.'/'.$addon_data['templates_folder'].'/'.$folder_name.'/'.$iw_type.'/*';
			$custom_url = STYLESHEETPATH . '/geo-my-wp/'.$custom_folder.'/'.$folder_name.'/'.$iw_type.'/*';
		} else {
			$url = $path.'/'.$addon_data['templates_folder'].'/'.$folder_name.'/*';
			$custom_url = STYLESHEETPATH . '/geo-my-wp/'.$custom_folder.'/'.$folder_name.'/*';
		}

		// get templates from plugin's folder
		foreach ( glob( $url, GLOB_ONLYDIR ) as $dir ) {
			$themes[basename( $dir )] = basename( $dir );
		}
		
		// look for custom templates
		$custom_templates = glob( $custom_url, GLOB_ONLYDIR );
		
		// append custom templates from theme/child theme folder if found
		if ( ! empty( $custom_templates ) ) {
			foreach ( $custom_templates as $dir ) {
				$themes['custom_'.basename( $dir )] = 'Custom: '.basename( $dir );
			}
		}
		
		return $themes;	
	}

	/**
	 * Get template file and its stylesheet
	 *
	 * @since 3.0
	 * 
	 * @param  string $addon         the slug of the add-on which the template file belongs to.
	 * @param  string $folder_name   folder name ( search-forms, search-results, info-window... ).
	 * @param  string $iw_type       info-window type ( used when folder name is set to "info-window" ).
	 * @param  string $template_name template folder name ( ex. default );
	 * @param  string $base_addon    when an addon exists inside another addon, we pass the slug of the main extension as base_addon.
	 * @return 
	 */
	public static function get_template( $addon = 'posts_locator', $folder_name = 'search-forms', $iw_type = 'popup', $template_name = 'default', $base_addon = '' ) {
		
		// abort if addon is inactive or folder is missing
		if ( ! gmw_is_addon_active( $addon ) ) {
			return false;
		}

		// get addon data
		$addon_data = gmw_get_addon_data( $addon );

		$output = array();

		if ( $folder_name == 'info-window' ) {
			$handle = $folder_name .'-'.$iw_type;
			$folder = $folder_name .'/'.$iw_type;
		} else {
			$folder = $handle = $folder_name;
		}

		// Get custom template and css from child/theme folder
		if ( strpos( $template_name, 'custom_' ) !== false ) {

			$template_name = str_replace( 'custom_', '', $template_name );

			$output['stylesheet_handle'] = "gmw-{$addon_data['prefix']}-{$handle}-custom-{$template_name}";
			$output['stylesheet_url']	 = get_stylesheet_directory_uri(). "/geo-my-wp/{$addon_data['custom_templates_folder']}/{$folder}/{$template_name}/css/style.css";
			$output['content_path'] 	 = STYLESHEETPATH . "/geo-my-wp/{$addon_data['custom_templates_folder']}/{$folder}/{$template_name}/content.php";

			// for previous version of GEO my WP. Need to rename all custom template files to content.php
			// to be removed
			if ( ! file_exists( $output['content_path'] ) ) {

				if ( $folder_name == 'search-forms' ) {
					$output['content_path'] = STYLESHEETPATH . "/geo-my-wp/{$addon_data['custom_templates_folder']}/{$folder}/{$template_name}/search-form.php";
				} elseif ( $folder_name == 'search-results' ) {
					$output['content_path'] = STYLESHEETPATH . "/geo-my-wp/{$addon_data['custom_templates_folder']}/{$folder}/{$template_name}/results.php";
				}
			}
		
		// load template files from plugin's folder
		} else {
			
			if ( $base_addon == '' ) {
				
				$plugin_url = $addon_data['plugin_url'];
				$plugin_dir = $addon_data['plugin_dir'];

			} else {

				$base_addon = gmw_get_addon_data( $base_addon );

				$plugin_url = $base_addon['plugin_url'].'/'.$addon_data['custom_templates_folder'];
				$plugin_dir = $base_addon['plugin_dir'].'/'.$addon_data['custom_templates_folder'];
			}
			
			$output['stylesheet_handle'] = "gmw-{$addon_data['prefix']}-{$handle}-{$template_name}";
			$output['stylesheet_url'] 	 = $plugin_url."/{$addon_data['templates_folder']}/{$folder}/{$template_name}/css/style.css";
			$output['content_path']      = $plugin_dir."/{$addon_data['templates_folder']}/{$folder}/{$template_name}/content.php";
		}

		return $output;
	}
}