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
	 * @param  string $folder_name folder name ( ex. search-results, search-forms... ). can also left blank if no inside any folder.
	 * @param  string $iw_type     info-window type. Will be used when $folder_name is set to info-window
	 * @param  string $base_addon  slug of base addon. Can be used when a single or multiple addons exist 
	 *                             inside a another base addon. In this case the the sub-addons 
	 *                             should be placed inside a "plugins" folder within the base add-on.
	 *                             
	 * @return array  list of templates
	 */
	public static function get_templates( $addon = '', $folder_name = 'search-forms', $iw_type = 'popup', $base_addon = '' ) {
		
		// abort if addon is inactive
		if ( ! gmw_is_addon_active( $addon ) ) {
			return array();
		}

		$themes = array();
		$folder = ! empty( $folder_name ) ? $folder_name.'/' : '';
			
		// addon data
		$addon_data    	  = gmw_get_addon_data( $addon );
		$templates_folder = $addon_data['templates_folder'];

		// get plugin's template folders path
		// if no based addon provided
		if ( $base_addon == '' ) {

			$ba_data = false;
			$path 	 = $addon_data['plugin_dir'].'/templates/'.$folder;

		// with based addon
		} else {
			$ba_data = gmw_get_addon_data( $base_addon );
			$path 	 = $ba_data['plugin_dir'].'/plugins/'.$templates_folder.'/templates/'.$folder;
		}

		// if this is info-window templates
		$path .= $folder_name == 'info-window' ? $iw_type.'/*' : '*';

		// get templates from plugin's folder
		foreach ( glob( $path, GLOB_ONLYDIR ) as $dir ) {
			$themes[basename( $dir )] = basename( $dir );
		}

		// can modify the PATH of the custom template files.
		$custom_path = apply_filters( 'gmw_get_templates_path', STYLESHEETPATH.'/geo-my-wp', $addon, $folder_name, $iw_type, $base_addon );

		if ( $ba_data != false && ! empty( $ba_data['templates_folder'] ) ) {
			$templates_folder .= '/'.$ba_data['templates_folder']; 
		}

		if ( $folder_name == 'info-window' ) {

			$custom_path 	  	  = $custom_path .'/'.$templates_folder.'/'.$folder.$iw_type.'/*';
			$template_custom_path = TEMPLATEPATH . '/geo-my-wp/'.$templates_folder.'/'.$folder.$iw_type.'/*';

		} else {
			$custom_path 	  	  = $custom_path .'/'.$templates_folder.'/'.$folder.'*';
			$template_custom_path = TEMPLATEPATH . '/geo-my-wp/'.$templates_folder.'/'.$folder.'*';
		}
		//echo 'mama' .$custom_path . ' <br />';
		//sdf();
		// look for custom templates in child theme or custom path. If not found check in parent theme
		if (  empty( $custom_templates = glob( $custom_path, GLOB_ONLYDIR ) ) ) {
			$custom_templates = glob( $template_custom_path, GLOB_ONLYDIR );
		};
		
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
	 * @param  string $folder_name   folder name ( search-forms, search-results, info-window... ). can also left blank if not inside any folder.
	 * @param  string $iw_type       info-window type ( used when folder name is set to "info-window" ).
	 * @param  string $template_name template folder name ( ex. default );
	 * @param  string $base_addon    when an addon exists inside another addon, we pass the slug of the main extension as base_addon.
	 * @return 
	 */
	public static function get_template( $args = array() ) {

		$defaults = array(
			'addon' 	       => 'posts_locator', 
			'folder_name'      => 'search-forms', 
			'iw_type'          => 'popup', 
			'template_name'    => 'default', 
			'file_name'        => 'content.php', 
			'base_addon'       => '',
			'include_template' => false
		);

		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );

		// abort if addon is inactive 
		if ( ! gmw_is_addon_active( $addon ) ) {
			return false;
		}

		// get addon data
		$addon_data = gmw_get_addon_data( $addon );

		$output = array();

		if ( $folder_name == 'info-window' ) {

			$folder_handle = $folder_name .'-'.$iw_type.'-';
			$folder 	   = $folder_name .'/'.$iw_type.'/';

		} else {
			
			if ( ! empty( $folder_name ) ) {

				$folder = $folder_name.'/';
				$folder_handle = $folder_name.'-';

			} else {

				$folder = $folder_handle = '';
			}
		}

		$prefix_handle = $base_addon == '' ? $addon_data['prefix'] : $base_addon.'-'.$addon_data['prefix'];

		// Get custom template and css from child/theme folder
		if ( strpos( $template_name, 'custom_' ) !== false ) {

			$template_name 	  = str_replace( 'custom_', '', $template_name );
			$templates_folder = $addon_data['templates_folder'];

			$output['stylesheet_handle'] = "gmw-{$prefix_handle}-{$folder_handle}custom-{$template_name}";
			
			// modify the PATH and URI of the custom template files.
			$custom_path_uri = array(
				'path' => STYLESHEETPATH.'/geo-my-wp',
				'uri'  => get_stylesheet_directory_uri().'/geo-my-wp' 
			);

			$custom_path_uri = apply_filters( 'gmw_get_template_path_uri', $custom_path_uri, $addon, $folder_name, $iw_type, $template_name, $base_addon );

			if ( $base_addon != '' ){

				$ba_data = gmw_get_addon_data( $base_addon );

				if ( $ba_data != false && ! empty( $ba_data['templates_folder'] ) ) {
					$templates_folder .= '/'.$ba_data['templates_folder']; 
				}
			}

			// look for template in custom location or in child theme. If not found check in parent theme.
			if ( file_exists( $custom_path_uri['path']."/{$templates_folder}/{$folder}{$template_name}/" ) ) {

				$output['content_path'] = $custom_path_uri['path']."/{$templates_folder}/{$folder}{$template_name}/{$file_name}";
				$output['stylesheet_uri'] = $custom_path_uri['uri']."/{$templates_folder}/{$folder}{$template_name}/css/style.css";

			} else {

				$output['content_path'] = TEMPLATEPATH . "/geo-my-wp/{$templates_folder}/{$folder}{$template_name}/{$file_name}";
				$output['stylesheet_uri'] = get_template_directory_uri(). "/geo-my-wp/{$templates_folder}/{$folder}{$template_name}/css/style.css";
			}

			// for previous version of GEO my WP. Need to rename all custom template files to content.php
			// to be removed
			if ( ! file_exists( $output['content_path'] ) ) {

				if ( $folder_name == 'search-forms' ) {

					$output['content_path'] = STYLESHEETPATH . "/geo-my-wp/{$templates_folder}/{$folder}{$template_name}/search-form.php";
				
				} elseif ( $folder_name == 'search-results' ) {
					
					$output['content_path'] = STYLESHEETPATH . "/geo-my-wp/{$templates_folder}/{$folder}{$template_name}/results.php";
				}
			}
			
		// load template files from plugin's folder
		} else {

			if ( $base_addon == '' ) {	

				$plugin_url = $addon_data['plugin_url'];
				$plugin_dir = $addon_data['plugin_dir'];

			} else {

				$base_addon = gmw_get_addon_data( $base_addon );
				$plugin_url = $base_addon['plugin_url'].'/plugins/'.$addon_data['templates_folder'];
				$plugin_dir = $base_addon['plugin_dir'].'/plugins/'.$addon_data['templates_folder'];
			}

			$output['stylesheet_handle'] = "gmw-{$prefix_handle}-{$folder_handle}{$template_name}";
			$output['stylesheet_uri'] 	 = $plugin_url."/templates/{$folder}{$template_name}/css/style.css";
			$output['content_path']      = $plugin_dir."/templates/{$folder}{$template_name}/{$file_name}";
		}

		// include file if needed
		if ( $include_template ) {

			// enqueue stylesheet if not already enqueued
			if ( ! wp_style_is( $output['stylesheet_handle'], 'enqueued' ) ) {
				wp_enqueue_style( $output['stylesheet_handle'], $output['stylesheet_uri'] );
			}

			include( $output['content_path'] ); 

		// otherwise return 
		} else {
			return $output;
		}	
	}
}