<?php
/**
 * GEO my WP helper class.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_Helper class
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

		$ulc_prefix = gmw_get_ulc_prefix();

		// abort if user's location does not exist in cookies.
		if ( empty( $_COOKIE[ $ulc_prefix . 'lat' ] ) || empty( $_COOKIE[ $ulc_prefix . 'lng' ] ) ) {
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
			'formatted_address',
		);

		$location = wp_cache_get( 'gmw_user_current_location' );

		if ( empty( $location ) ) {

			$location = (object) array();

			$location->lat = urldecode( sanitize_text_field( wp_unslash( $_COOKIE[ $ulc_prefix . 'lat' ] ) ) );
			$location->lng = urldecode( sanitize_text_field( wp_unslash( $_COOKIE[ $ulc_prefix . 'lng' ] ) ) );

			foreach ( $fields as $field ) {

				if ( ! empty( $_COOKIE[ $ulc_prefix . $field ] ) ) {
					$location->$field = urldecode( sanitize_text_field( wp_unslash( $_COOKIE[ $ulc_prefix . $field ] ) ) );
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
	 * @param  array $args  array of arguments listed below.
	 *
	 * $component   slug of the add-on/component the template file belongs to.
	 * $folder_name folder name ( ex. search-results, search-forms... ). can also left blank if no inside any folder.
	 * $iw_type     info-window type. Will be used when $folder_name is set to info-window
	 * $addon       slug of the addon when different than the component's addon. Can be used when a single or multiple addons exist inside a another base addon. In this case the the sub-addons should be placed inside a "plugins" folder within the base add-on.
	 *
	 * @return array  list of templates
	 */
	public static function get_templates( $args = array() ) {

		$defaults = array(
			'component'   => 'posts_locator',
			'addon'       => '',
			'folder_name' => '',
			'iw_type'     => 'popup',
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		$themes = array();
		$folder = ! empty( $folder_name ) ? $folder_name . '/' : '';

		// addon data.
		$component_data   = gmw_get_addon_data( $component );
		$templates_folder = $component_data['templates_folder'];

		// get plugin's template folders path
		// if no based addon provided.
		if ( '' === $addon || $addon == $component ) {

			$addon_data = false;
			$path       = $component_data['plugin_dir'] . '/templates/' . $folder;

			// with based addon.
		} else {

			$addon_data = gmw_get_addon_data( $addon );
			$path       = $addon_data['plugin_dir'] . '/plugins/' . $templates_folder . '/templates/' . $folder;
		}

		// if this is info-window templates.
		$path .= 'info-window' == $folder_name ? $iw_type . '/*' : '*';

		// get templates from plugin's folder.
		foreach ( glob( $path, GLOB_ONLYDIR ) as $dir ) {
			$themes[ basename( $dir ) ] = basename( $dir );
		}

		// can modify the PATH of the custom template files.
		$custom_path = apply_filters( 'gmw_get_templates_path', STYLESHEETPATH . '/geo-my-wp', $component, $folder_name, $iw_type, $addon );

		if ( $addon_data != false && ! empty( $addon_data['templates_folder'] ) ) {
			$templates_folder .= '/' . $addon_data['templates_folder'];
		}

		if ( 'info-window' === $folder_name ) {

			$custom_path          = $custom_path . '/' . $templates_folder . '/' . $folder . $iw_type . '/*';
			$template_custom_path = TEMPLATEPATH . '/geo-my-wp/' . $templates_folder . '/' . $folder . $iw_type . '/*';

		} else {
			$custom_path          = $custom_path . '/' . $templates_folder . '/' . $folder . '*';
			$template_custom_path = TEMPLATEPATH . '/geo-my-wp/' . $templates_folder . '/' . $folder . '*';
		}

		// look for custom templates in child theme or custom path. If not found check in parent theme.
		if ( ( $custom_templates = glob( $custom_path, GLOB_ONLYDIR ) ) == false ) {
			$custom_templates = glob( $template_custom_path, GLOB_ONLYDIR );
		};

		// append custom templates from theme/child theme folder if found.
		if ( ! empty( $custom_templates ) ) {
			foreach ( $custom_templates as $dir ) {
				$themes[ 'custom_' . basename( $dir ) ] = 'Custom: ' . basename( $dir );
			}
		}

		return $themes;
	}

	/**
	 * Get template file and its stylesheet
	 *
	 * @since 3.0
	 *
	 * @param array $args list of arguments listed below.
	 *
	 * array(
	 *   $component     the slug of the add-on/component which the template file belongs to.
	 *   $base_addon    when an addon/component exists inside another addon ( ex. Posts Locator inside global maps ), we pass the slug of the base addon.
	 *   $folder_name   folder name ( search-forms, search-results, info-window... ). can also left blank if not inside any folder.
	 *   $iw_type       info-window type ( used when folder name is set to "info-window" ).
	 *   $template_name template folder name ( ex. default );
	 * );
	 *
	 * return $output.
	 */
	public static function get_template( $args = array() ) {

		$defaults = array(
			'component'        => 'posts_locator',
			'addon'            => '',
			'folder_name'      => 'search-forms',
			'iw_type'          => 'popup',
			'template_name'    => 'default',
			'file_name'        => 'content.php',
			'include_template' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		// get addon data.
		$component_data = gmw_get_addon_data( $component );

		$output = array();

		if ( 'info-window' === $folder_name ) {

			$folder_handle = $folder_name . '-' . $iw_type . '-';
			$folder        = $folder_name . '/' . $iw_type . '/';

		} else {

			if ( ! empty( $folder_name ) ) {

				$folder        = $folder_name . '/';
				$folder_handle = $folder_name . '-';

			} else {

				$folder        = '';
				$folder_handle = '';
			}
		}

		$prefix_handle = ( '' === $addon || $addon == $component ) ? $component_data['prefix'] : $addon . '-' . $component_data['prefix'];

		// Get custom template and css from child/theme folder.
		if ( strpos( $template_name, 'custom_' ) !== false ) {

			$template_name    = str_replace( 'custom_', '', $template_name );
			$templates_folder = $component_data['templates_folder'];

			$output['stylesheet_handle'] = "gmw-{$prefix_handle}-{$folder_handle}custom-{$template_name}";

			// modify the PATH and URI of the custom template files.
			$custom_path_uri = array(
				'path' => STYLESHEETPATH . '/geo-my-wp',
				'uri'  => get_stylesheet_directory_uri() . '/geo-my-wp',
			);

			$custom_path_uri = apply_filters( 'gmw_get_template_path_uri', $custom_path_uri, $component, $folder_name, $iw_type, $template_name, $addon );

			if ( '' !== $addon && $addon != $component ) {

				$addon_data = gmw_get_addon_data( $addon );

				if ( $addon_data != false && ! empty( $addon_data['templates_folder'] ) ) {
					$templates_folder .= '/' . $addon_data['templates_folder'];
				}
			}

			// look for template in custom location or in child theme. If not found check in parent theme.
			if ( file_exists( $custom_path_uri['path'] . "/{$templates_folder}/{$folder}{$template_name}/" ) ) {

				$output['content_path']   = $custom_path_uri['path'] . "/{$templates_folder}/{$folder}{$template_name}/";
				$output['stylesheet_uri'] = $custom_path_uri['uri'] . "/{$templates_folder}/{$folder}{$template_name}/css/style.css";

			} else {

				$output['content_path']   = TEMPLATEPATH . "/geo-my-wp/{$templates_folder}/{$folder}{$template_name}/";
				$output['stylesheet_uri'] = get_template_directory_uri() . "/geo-my-wp/{$templates_folder}/{$folder}{$template_name}/css/style.css";
			}

			// for previous version of GEO my WP. Need to rename all custom template files to content.php
			// to be removed.
			if ( file_exists( $output['content_path'] . $file_name ) ) {

				$output['content_path'] .= $file_name;

			} else {

				if ( 'search-forms' === $folder_name ) {

					$output['content_path'] .= 'search-form.php';

				} elseif ( 'search-results' === $folder_name ) {

					$output['content_path'] .= 'results.php';
				}
			}

			// load template files from plugin's folder.
		} else {

			if ( '' === $addon || $addon == $component ) {

				$plugin_url = $component_data['plugin_url'];
				$plugin_dir = $component_data['plugin_dir'];

			} else {

				$addon      = gmw_get_addon_data( $addon );
				$plugin_url = $addon['plugin_url'] . '/plugins/' . $component_data['templates_folder'];
				$plugin_dir = $addon['plugin_dir'] . '/plugins/' . $component_data['templates_folder'];
			}

			$output['stylesheet_handle'] = "gmw-{$prefix_handle}-{$folder_handle}{$template_name}";
			$output['stylesheet_uri']    = $plugin_url . "/templates/{$folder}{$template_name}/css/style.css";
			$output['content_path']      = $plugin_dir . "/templates/{$folder}{$template_name}/{$file_name}";
		}

		$output = apply_filters( 'gmw_get_template_output', $output, $addon );

		// include file if needed.
		if ( $include_template ) {

			// enqueue stylesheet if not already enqueued.
			if ( ! wp_style_is( $output['stylesheet_handle'], 'enqueued' ) ) {
				wp_enqueue_style( $output['stylesheet_handle'], $output['stylesheet_uri'] );
			}

			include $output['content_path'];

			// otherwise return.
		} else {
			return $output;
		}
	}
}
