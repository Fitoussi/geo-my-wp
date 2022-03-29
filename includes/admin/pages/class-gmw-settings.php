<?php
/**
 * GEO my WP Admin Settings page.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Settings class.
 */
class GMW_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {

		if ( ( empty( $_GET['page'] ) || 'gmw-settings' !== $_GET['page'] ) && ( empty( $_POST['option_page'] ) || 'gmw_options' !== $_POST['option_page'] ) ) { // WPCS: CSRF ok.
			return;
		}

		add_action( 'admin_init', array( $this, 'update_settings' ), 999 );
	}

	/**
	 * Setup settings default values.
	 */
	public static function setup_defaults() {

		$defaults = apply_filters(
			'gmw_admin_settings_setup_defaults',
			array(
				'general_settings' => array(
					'allow_tracking' => '',
					'country_code'   => 'US',
					'language_code'  => 'EN',
					'auto_locate'    => 1,
					'results_page'   => '',
				),
				'api_providers'    => array(
					'maps_provider'                   => 'leaflet',
					'geocoding_provider'              => 'nominatim',
					'nominatim_email'                 => get_bloginfo( 'admin_email' ),
					'google_maps_client_side_api_key' => '',
					'google_maps_server_side_api_key' => '',
					'google_maps_api_china'           => '',
				),
				'styles'           => array(
					'color_primary'           => '#1e90ff',
					'color_hover_primary'     => '#2b97ff',
					'font_color_primary'      => '#ffffff',
					'color_secondary'         => '#63CC61',
					'color_hover_secondary'   => '#70d56e',
					'font_color_secondary'    => '#ffffff',
					'color_accent'            => '#FFA600',
					'color_hover_accent'      => '#ee9e08',
					'font_color_accent'       => '#ffffff',
					'notice_color_success'    => '#63CC61',
					'notice_color_failed'     => '#FF0200',
					'notice_color_info'       => '#FFA600',
					'notice_color_processing' => '#FFA600',
				),
			)
		);

		$gmw_options = get_option( 'gmw_options' );

		if ( empty( $gmw_options ) ) {
			$gmw_options = array();
		}

		$save_options = false;

		foreach ( $defaults as $group_name => $group_options ) {

			if ( empty( $gmw_options[ $group_name ] ) ) {

				$gmw_options[ $group_name ] = $group_options;

				$save_options = true;

			} else {

				foreach ( $group_options as $option_key => $option_value ) {

					if ( ! isset( $gmw_options[ $group_name ][ $option_key ] ) ) {

						$gmw_options[ $group_name ][ $option_key ] = $option_value;

						$save_options = true;
					}
				}
			}
		}

		if ( $save_options ) {
			update_option( 'gmw_options', $gmw_options );
		}
	}

	/**
	 * Get the current Settings page tab.
	 *
	 * @since 4.0
	 *
	 * @return [type] [description]
	 */
	public function get_current_tab() {
		return ! empty( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'general_settings'; // WPCS: CSRF ok, sanitization ok.
	}

	/**
	 * Settings groups
	 *
	 * @return [type] [description]
	 */
	public function settings_groups() {

		// API providers settings.
		$api_providers = apply_filters(
			'gmw_admin_settings_api_providers_options',
			array(
				'maps'      => array(
					'leaflet'     => 'LeafLet',
					'google_maps' => 'Google Maps',
				),
				'geocoding' => array(),
			)
		);

		$results_page = gmw_get_option( 'general_settings', 'results_page', '' );
		$results_page = ! empty( $results_page ) ? array( get_the_title( $results_page ) ) : array();

		$settings = array(
			'general_settings' => array(
				'slug'     => 'general_settings',
				'label'    => __( 'General Settings', 'geo-my-wp' ),
				'icon'     => 'cog',
				'fields'   => array(
					'allow_tracking' => array(
						'name'       => 'allow_tracking',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Plugin Usage Tracking', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'desc'       => __( 'Check this checkbox to allow GEO my WP track the plugin usage on your site.', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 10,
					),
					/**
					'google_maps_api_usage' => array(
						'name'       => 'google_maps_api_usage',
						'type'       => 'select',
						'default'    => 'enabled',
						'label'      => __( 'Google Maps API', 'geo-my-wp' ),
						'desc'       => __( 'Using this feature you prevent GEO my WP from registering the Google Map API ( which it does by default ). In most cases this feature should be set to "Enabled". It should disabled only if there are other mapping plugin installed on your site, which also register the Google Maps API and cause for conflicts. Note that disabling this feature might solve a conflict cause by multiple calles to Google Maps API, it might also results in GEO my WP to not work properly.', 'geo-my-wp' ),
						'attributes' => array(),
						'options'    => array(
							'enabled'  => __( 'Enabled', 'geo-my-wp' ),
							'frontend' => __( 'Disable in the front-end only', 'geo-my-wp' ),
							'admin'    => __( 'Disable in the back-end only', 'geo-my-wp' ),
							'disabled' => __( 'Disable completely', 'geo-my-wp' ),
						),
						'priority'   => 20,
					),
					*/
					'country_code'   => array(
						'name'        => 'country_code',
						'type'        => 'text',
						'default'     => '',
						'placeholder' => 'ex. US',
						'label'       => __( 'Default Region', 'geo-my-wp' ),
						'desc'        => sprintf(
							/* translators: %s: link */
							__( 'Enter the country code of the country that will be used by default when geocoding an address and when using other API services. The list of country codes can be found <a href="%s" target="_blank">here</a>.', 'geo-my-wp' ),
							'http://geomywp.com/country-code/'
						),
						'attributes'  => array(),
						'priority'    => 20,
					),
					'language_code'  => array(
						'name'        => 'language_code',
						'type'        => 'text',
						'default'     => '',
						'placeholder' => 'ex. EN',
						'label'       => __( 'Default Language', 'geo-my-wp' ),
						'desc'        => sprintf(
							/* translators: %s: link */
							__( 'Enter the language code of the default language that will be used with the API providers. This will affect the language of the map and the address autocomplete suggested results. See <a href="%s" target="_blank">this page</a> for the list of language codes.', 'geo-my-wp' ),
							'https://sites.google.com/site/tomihasa/google-language-codes'
						),
						'attributes'  => array(),
						'priority'    => 30,
					),
					'auto_locate'    => array(
						'name'       => 'auto_locate',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Auto Locator', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'desc'       => __( 'When enabled, GEO my WP will try to retrieve the user\'s current location when first visiting your website. If a location was found, it will be saved via cookies and will be used with some of GEO my WP features; such as dynamically displaying results nearby the user.', 'geo-my-wp' ),
						'attributes' => array(),
						'priority'   => 40,
					),
					'results_page'   => array(
						'name'        => 'results_page',
						'type'        => 'select',
						'placeholder' => 'select page',
						'default'     => '0',
						'label'       => __( 'Results Page', 'geo-my-wp' ),
						'desc'        => __( 'Select the page that will display the search result when using the "GMW Search Form" widget, then place the shortcode <code>[gmw form="results"]</code> in the content area of that page. The page that you select here will effect all of GEO my WP forms by default, but can be overriden when editing a specific form.', 'geo-my-wp' ),
						'options'     => $results_page,
						'attributes'  => array(
							'data-gmw_ajax_load_options' => 'gmw_get_pages',
						),
						'priority'    => 50,
					),
				),
				'priority' => 3,
			),
			'api_providers'    => array(
				'slug'     => 'api_providers',
				'label'    => __( 'Maps & Geocoders', 'geo-my-wp' ),
				'icon'     => 'map-o',
				'fields'   => array(
					'maps_provider'       => array(
						'name'       => 'maps_provider',
						'type'       => 'select',
						'default'    => 'leaflet',
						'label'      => __( 'Maps & Geocoding Provider', 'geo-my-wp' ),
						'desc'       => __( 'Select the maps & geocoding provider that you would like to use.', 'geo-my-wp' ),
						'attributes' => array(),
						'class'      => 'gmw-smartbox-not',
						'options'    => $api_providers['maps'],
						'priority'   => 10,
					),
					'geocoding_provider'  => array(
						'name'       => 'geocoding_provider',
						'type'       => 'hidden',
						'default'    => 'leaflet',
						'label'      => __( 'Maps Provider', 'geo-my-wp' ),
						'desc'       => __( 'Select the maps provider that you would like to use.', 'geo-my-wp' ),
						'attributes' => array(),
						'options'    => $api_providers['maps'],
						'priority'   => 10,
					),
					'nominatim_options'   => array(
						'name'            => 'nominatim_options',
						'type'            => 'fields_group',
						'label'           => __( 'Nominatim ( OpenStreetMaps )', 'geo-my-wp' ),
						// 'desc'       => __( 'Setup Nominatim options.', 'geo-my-wp' ),
						'fields'          => array(
							'nominatim_email' => array(
								'name'        => 'nominatim_email',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => __( 'Enter email address', 'geo-my-wp' ),
								'label'       => __( 'Valid Email Address', 'geo-my-wp' ),
								'desc'        => sprintf(
									/* translators: %1$s: link, %2$s: link. */
									__( 'Nominatim is a geocoding provider for OpenStreetMaps. The provider requires a valid email address in order to use its services. <br />See this <a href="%1$s" target="_blank">this page</a> to learn more about this service. Also see the Nominatim <a href="%2$s" target="_blank">usage policy</a>.', 'geo-my-wp' ),
									'https://wiki.openstreetmap.org/wiki/Nominatim',
									'https://operations.osmfoundation.org/policies/nominatim/'
								),
								'attributes'  => array( 'size' => '50' ),
								'priority'    => 5,
							),
						),
						'priority'        => 30,
						'settings_toggle' => array(
							'element' => 'maps_provider',
							'value'   => 'leaflet',
						),
					),
					'google_maps_options' => array(
						'name'            => 'google_maps_options',
						'type'            => 'fields_group',
						'label'           => __( 'Google Maps API Settings', 'geo-my-wp' ),
						'desc'            => sprintf(
							/* translators: %s: link. */
							__( 'GEO my WP requires two API keys: A browser and a server API keys. See <a href="%s" target="_blank">this tutorial</a> to learn how to generate your API keys.', 'geo-my-wp' ),
							'https://docs.geomywp.com/article/141-generate-and-setup-google-maps-api-keys'
						),
						'fields'          => array(
							'google_maps_client_side_api_key' => array(
								'name'        => 'google_maps_client_side_api_key',
								'type'        => 'text',
								'default'     => '',
								'label'       => __( 'Google Maps Browser ( Client-side ) API key', 'geo-my-wp' ),
								'placeholder' => __( 'Browser API key', 'geo-my-wp' ),
								'desc'        => __( 'The browser API key is responsible for generating maps, directions, address autocomplete, and client-side geocoding when using the location form.', 'geo-my-wp' ),
								'priority'    => 5,
							),
							'google_maps_server_side_api_key' => array(
								'name'        => 'google_maps_server_side_api_key',
								'type'        => 'text',
								'default'     => '',
								'label'       => __( 'Google Maps Server API key', 'geo-my-wp' ),
								'placeholder' => __( 'Server API key', 'geo-my-wp' ),
								'desc'        => sprintf(
									/* translators: %1$s: oen <a> tag, %2$s: close </a> tag. */
									__( 'The server API key is responsible for server side geocoding. Without this key some of GEO my WP functions will not work properly. After generating and entering your server API key, you can test it %1$shere%2$s.', 'geo-my-wp' ),
									'<a href=" ' . admin_url( 'admin.php?page=gmw-tools&tab=api_testing' ) . ' ">',
									'</a>'
								),
								'attributes'  => array( 'size' => '50' ),
								'priority'    => 10,
							),
							'google_maps_api_china' => array(
								'name'       => 'google_maps_api_china',
								'type'       => 'checkbox',
								'default'    => '',
								'label'      => __( 'Google Maps API For China', 'geo-my-wp' ),
								'cb_label'   => __( 'Enabled', 'geo-my-wp' ),
								'desc'       => __( 'Check this checkbox if your server is located in China and Google Maps features are not working properly on your site.', 'geo-my-wp' ),
								'attributes' => array(),
								'priority'   => 15,
							),
						),
						'optionsbox'      => 1,
						'priority'        => 40,
						'settings_toggle' => array(
							'element' => 'maps_provider',
							'value'   => 'google_map',
						),
					),
				),
				'priority' => 5,
			),
			'styles'           => array(
				'slug'     => 'styles',
				'label'    => __( 'Styling', 'geo-my-wp' ),
				'icon'     => 'cog',
				'fields'   => array(
					'main_colors'   => array(
						'name'     => 'main_colors',
						'type'     => 'fields_group',
						'label'    => __( 'Main Colors', 'geo-my-wp' ),
						'desc'     => __( 'Manage some of the colors that GEO my WP uses in various places in the plugin.', 'geo-my-wp' ),
						'fields'   => array(
							'color_primary'         => array(
								'name'        => 'color_primary',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Primary Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 5,
							),
							'color_hover_primary'   => array(
								'name'        => 'color_hover_primary',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Primary Hover Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 10,
							),
							'font_color_primary'    => array(
								'name'        => 'font_color_primary',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Primary Font Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 15,
							),
							'color_secondary'       => array(
								'name'        => 'color_secondary',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Secondary Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 20,
							),
							'color_hover_secondary' => array(
								'name'        => 'color_hover_secondary',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Secondary Hover Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 25,
							),
							'font_color_secondary'  => array(
								'name'        => 'font_color_primary',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Secondary Font Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 30,
							),
							'color_accent'          => array(
								'name'        => 'color_accent',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Accent Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 35,
							),
							'color_hover_accent'    => array(
								'name'        => 'color_hover_accent',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Accent Hover Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 40,
							),
							'font_color_accent'     => array(
								'name'        => 'font_color_accent',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Accent Font Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 45,
							),
						),
						'priority' => 30,
					),
					'notice_colors' => array(
						'name'     => 'notice_colors',
						'type'     => 'fields_group',
						'label'    => __( 'Notices Color', 'geo-my-wp' ),
						'desc'     => __( 'Manage the colors of GEO my WP\'s notices.', 'geo-my-wp' ),
						'fields'   => array(
							'notice_color_success'    => array(
								'name'        => 'notice_color_success',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Success Notice Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 5,
							),
							'notice_color_failed'     => array(
								'name'        => 'notice_color_failed',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Failed Notice Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 10,
							),
							'notice_color_info'       => array(
								'name'        => 'notice_color_info',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Info / Warning Notice Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 15,
							),
							'notice_color_processing' => array(
								'name'        => 'notice_color_processing',
								'type'        => 'text',
								'default'     => '',
								'placeholder' => '',
								'label'       => __( 'Processing Notice Color', 'geo-my-wp' ),
								'desc'        => '',
								'class'       => 'gmw-color-picker-field',
								'attributes'  => array(
									'data-alpha-enabled' => true,
								),
								'priority'    => 20,
							),
						),
						'priority' => 30,
					),
				),
				'priority' => 99,
			),
		);

		// Premium Tabs.
		if ( ! class_exists( 'GMW_Premium_Settings_Addon' ) ) {

			$settings['premium_settings'] = array(
				'premium_feature'   => true,
				'slug'              => 'premium_settings',
				'label'             => 'Premium Settings',
				'fields'            => array(),
				'priority'          => 100,
				'extension_url'     => 'https://geomywp.com/extensions/premium-settings',
				'extension_name'    => 'Premium Settings',
				'extension_content' => 'Enhance GEO my WP\'s forms and other components with premium features.',
			);
		}

		// Premium Tabs.
		if ( ! class_exists( 'GMW_Multiple_Locations_Addon' ) ) {

			$settings['multiple_locations'] = array(
				'premium_feature'   => true,
				'slug'              => 'gmw_multiple_locations',
				'label'             => 'Multiple Locations',
				'fields'            => array(),
				'priority'          => 100,
				'extension_url'     => 'https://geomywp.com/extensions/multiple-locations',
				'extension_name'    => 'Multiple Locations',
				'extension_content' => 'Manage multiple locations per object.',
			);
		}

		// Premium Tabs.
		if ( ! class_exists( 'GMW_Ajax_Forms_Addon' ) ) {

			$settings['ajax_forms'] = array(
				'premium_feature'   => true,
				'slug'              => 'ajax_forms',
				'label'             => 'AJAX Forms',
				'fields'            => array(),
				'priority'          => 100,
				'extension_url'     => 'https://geomywp.com/extensions/ajax-forms',
				'extension_name'    => 'AJAX Forms',
				'extension_content' => 'Create AJAX powered proximity search forms using GEO my WP forms builder.',
			);
		}

		if ( ! class_exists( 'GMW_IP_Address_Locator_Addon' ) ) {

			$settings['ip_address_locator'] = array(
				'premium_feature'   => true,
				'slug'              => 'ip_address_locator',
				'label'             => 'IP Address Locator',
				'fields'            => array(),
				'priority'          => 100,
				'extension_url'     => 'https://geomywp.com/extensions/ip-address-locator',
				'extension_name'    => 'IP Address Locator',
				'extension_content' => 'Retrieve the user\'s current location using its IP address rather than using the browser\'s geolocation.',
			);
		}

		if ( ! class_exists( 'GMW_WP_Users_Locator_Addon' ) ) {

			$settings['users_locator'] = array(
				'premium_feature'   => true,
				'slug'              => 'users_locator',
				'label'             => 'WP Users Locator',
				'icon'              => 'users',
				'fields'            => array(),
				'priority'          => 101,
				'extension_url'     => 'https://geomywp.com/extensions/wordpress-users-locator',
				'extension_name'    => 'WordPress Users Locator',
				'extension_content' => 'Enhance WordPress users with geolocation and mapping features.',
			);
		}

		if ( ! class_exists( 'GMW_BP_Groups_Locator_Addon' ) ) {

			$settings['bp_groups_locator'] = array(
				'premium_feature'   => true,
				'slug'              => 'bp_groups_locator',
				'label'             => 'BP Groups Locator',
				'icon'              => 'group',
				'fields'            => array(),
				'priority'          => 103,
				'extension_url'     => 'https://geomywp.com/extensions/groups-locator',
				'extension_name'    => 'BP Group Locator',
				'extension_content' => 'Enhance BuddyPress Groups component with geolocation and mapping features.',
			);
		}

		return apply_filters( 'gmw_admin_settings_groups', $settings );
	}

	/**
	 * Generate settings
	 *
	 * @return [type] [description]
	 */
	public function settings() {

		$settings = array();

		// loop through settings groups.
		foreach ( $this->settings_groups as $key => $group ) {

			// verify groups slug.
			if ( empty( $group['slug'] ) ) {
				continue;
			}

			// Generate the group if does not exsist.
			if ( ! isset( $settings[ $group['slug'] ] ) ) {

				$settings[ $group['slug'] ] = $group['fields'];

				// otehrwise, merge the fields of the existing group with the current group.
			} else {

				$settings[ $group['slug'] ] = array_merge_recursive( $settings[ $group['slug'] ], $group['fields'] );

				// remove the duplicate group/tab.
				unset( $this->settings_groups[ $key ] );
			}

			// allow filtering the specific group.
			$settings[ $group['slug'] ] = apply_filters( 'gmw_' . $group['slug'] . '_admin_settings', $settings[ $group['slug'] ], $settings );
		}

		// filter all settings groups.
		$settings = apply_filters( 'gmw_admin_settings', $settings );

		return $settings;
	}

	/**
	 * Init_settings function.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function init_settings() {

		// generate default values.
		self::setup_defaults();

		// get settings groups.
		$this->settings_groups = $this->settings_groups();

		// get settings.
		$this->settings = $this->settings();

		// backward capability for settings before settings groups were created.
		foreach ( $this->settings as $key => $section ) {

			if ( ! empty( $section[0] ) && ! empty( $section[1] ) && is_string( $section[0] ) ) {

				gmw_trigger_error( 'Using deprecated method for registering GMW settings and settings groups.' );

				$this->settings_groups[] = array(
					'slug'     => $key,
					'label'    => $section[0],
					'icon'     => '',
					'priority' => 99,
				);

				$this->settings[ $key ] = $section[1];
			}
		}

		// backward capability for replacing std with default.
		foreach ( $this->settings as $key => $section ) {

			if ( empty( $section ) ) {
				continue;
			}

			foreach ( $section as $sec_key => $sec_value ) {

				// skip hidden field.
				if ( empty( $sec_value ) ) {
					continue;
				}

				if ( isset( $sec_value['std'] ) && ! isset( $sec_value['default'] ) ) {

					gmw_trigger_error( '"std" attribute is no longer supported in GMW settings and was replaced with "default" in version 3.0.' );

					$this->settings[ $key ][ $sec_key ]['default'] = ! empty( $sec_value['std'] ) ? $sec_value['std'] : '';

					unset( $this->settings[ $key ][ $sec_key ]['std'] );
				}
			}
		}
	}

	/**
	 * Get list of pages.
	 *
	 * @return array of pages
	 */
	public function get_pages() {

		$pages = array();

		foreach ( get_pages() as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}

		return $pages;
	}

	/**
	 * Update settings.
	 */
	public function update_settings() {

		if ( empty( $_POST['gmw_settings_save_nonce'] ) ) { // WPCS: CSRF ok.
			return false;
		}

		// Verify nonce.
		check_admin_referer( 'gmw_settings_save', 'gmw_settings_save_nonce' );

		// Current tab.
		$current_tab     = $this->get_current_tab();
		$current_options = gmw_get_options_group();
		$options         = ! empty( $_POST['gmw_options'] ) ? $_POST['gmw_options'] : array(); // WPCS: CSRF ok, sanitization ok.

		// Validate options.
		$validated = $this->validate( $options );

		$current_options[ $current_tab ] = $validated[ $current_tab ];

		update_option( 'gmw_options', $current_options );

		$uri = ! empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : ''; // WPCS: CSRF ok, sanitization ok.

		wp_safe_redirect( home_url( $uri ) );

		exit;
	}

	/**
	 * Get form fields
	 *
	 * @param  array  $settings      form settings.
	 *
	 * @param  array  $options       field options.
	 *
	 * @param  string $tab           tab name.
	 *
	 * @param  array  $fields_group  fields group.
	 */
	public function get_form_field( $settings, $options, $tab, $fields_group = '' ) {

		if ( ! empty( $fields_group ) && ! empty( $options['sub_option'] ) ) {
			$name_attr        = 'gmw_options[' . $tab . '][' . $fields_group . ']';
			$value            = ! empty( $settings[ $tab ][ $fields_group ][ $options['name'] ] ) ? $settings[ $tab ][ $fields_group ][ $options['name'] ] : $options['default'];
			$options['id']    = esc_attr( 'setting-' . $tab . '-' . $fields_group . '-' . $options['name'] );
			$class            = 'setting-' . $fields_group . '-' . $options['name'];
			$options['class'] = ! empty( $options['class'] ) ? $options['class'] . ' ' . $class : $class;
			$options['class'] = esc_attr( $options['class'] );
		} else {

			$name_attr        = 'gmw_options[' . $tab . ']';
			$value            = ! empty( $settings[ $tab ][ $options['name'] ] ) ? $settings[ $tab ][ $options['name'] ] : $options['default'];
			$options['id']    = 'setting-' . $tab . '-' . $options['name'];
			$class            = 'setting-' . $options['name'];
			$options['class'] = ! empty( $options['class'] ) ? $options['class'] . ' ' . $class : $class;
		}

		echo gmw_get_admin_settings_field( $options, esc_attr( $name_attr ), $value ); // WPCS: XSS ok.
	}

	/**
	 * Display settings.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function output() {

		$this->init_settings();

		$settings    = get_option( 'gmw_options' );
		$current_tab = $this->get_current_tab();
		$section     = ! empty( $this->settings[ $current_tab ] ) ? $this->settings[ $current_tab ] : $this->settings['general_settings'];
		?>
		<?php gmw_admin_pages_header(); ?>

		<div id="gmw-settings-page" class="wrap gmw-admin-page-content gmw-admin-page gmw-admin-page-wrapper">

			<nav class="gmw-admin-page-navigation">

				<?php uasort( $this->settings_groups, 'gmw_sort_by_priority' ); ?>

				<?php foreach ( $this->settings_groups as $tab ) { ?>

					<?php

					if ( ! empty( $tab['premium_feature'] ) ) {

						printf(
							'<a href="#" class="gmw-premium-feature" data-feature="%s" data-name="%s" data-url="%s" data-content="%s"><span class="gmw-icon gmw-icon-%s"></span> <span class="label">%s</span></a>',
							esc_attr( $tab['slug'] ),
							esc_attr( $tab['extension_name'] ),
							esc_attr( $tab['extension_url'] ),
							esc_attr( $tab['extension_content'] ),
							'',
							esc_attr( $tab['label'] )
						);

					} else {

						// for previous versions.
						if ( ! empty( $tab['id'] ) ) {
							$tab['slug'] = $tab['id'];
						}

						// Prepare tab URL.
						$url = add_query_arg( array( 'tab' => $tab['slug'] ), admin_url( 'admin.php?page=gmw-settings' ) );

						// Get tab icon.
						$icon = ! empty( $tab['icon'] ) ? esc_attr( $tab['icon'] ) : 'cog';

						printf(
							'<a href="%s"%s><span class="gmw-icon gmw-icon-%s"></span> <span class="label">%s</span></a>',
							esc_url( $url ),
							$current_tab === $tab['slug'] ? ' class="active"' : '',
							'',
							esc_html( $tab['label'] )
						);
					}
					?>
				<?php } ?>
			</nav>

			<div class="gmw-admin-page-panels-wrapper" id="tab_<?php echo esc_attr( $current_tab ); ?>">

				<h1 style="display:none"></h1>

				<div id="gmw-settings-tab-<?php echo esc_attr( $current_tab ); ?>" class="gmw-settings-form gmw-tab-panel <?php echo esc_attr( $current_tab ); ?>">

					<form method="post" action="" class="gmw-settings-form">

						<?php uasort( $section, 'gmw_sort_by_priority' ); ?>

						<?php
						foreach ( $section as $option ) {

							$option['type']   = ! empty( $option['type'] ) ? $option['type'] : '';
							$class            = ! empty( $option['class'] ) ? $option['class'] . ' ' . $option['name'] . ' ' . $option['type'] : $option['name'] . ' ' . $option['type'] . ' ' . $current_tab;
							$setting_toggle   = '';
							$grid_column_css  = ! empty( $option['grid_column'] ) ? 'gmw-settings-panel-grid-column-' . esc_attr( $option['grid_column'] ) : '';
							$feature_disbaled = '';

							if ( ! empty( $option['feature_disabled'] ) ) {

								$feature_disbaled = 'feature-disabled';

								if ( ! empty( $option['disabled_message'] ) ) {
									$option['desc'] .= '<span class="gmw-admin-notice-box gmw-admin-notice-error">' . $option['disabled_message'] . '</span>';
								}
							}

							if ( ! empty( $option['settings_toggle'] ) && is_array( $option['settings_toggle'] ) ) {
								$setting_toggle = 'data-gmw_toggle_element="' . esc_attr( $option['settings_toggle']['element'] ) . '" data-gmw_toggle_value="' . esc_attr( $option['settings_toggle']['value'] ) . '"';
							}

							$allowed_html = apply_filters(
								'gmw_settings_page_feature_desc_allowed_html',
								array(
									'a'      => array(
										'href'   => array(),
										'title'  => array(),
										'target' => array(),
									),
									'span'   => array(
										'style' => array(),
										'class' => array(),
										'id'    => array(),
									),
									'div'    => array(
										'style' => array(),
										'class' => array(),
										'id'    => array(),
									),
									'code'   => array(),
									'br'     => array(),
									'em'     => array(),
									'p'      => array(),
									'strong' => array(),
									'b'      => array(),
									'u'      => array(),
								)
							);
							?>
							<fieldset 
								id="<?php echo esc_attr( $current_tab ); ?>-<?php echo esc_attr( $option['name'] ); ?>-tr"
								class="gmw-settings-panel feature-<?php echo esc_attr( $class ); ?> <?php echo $grid_column_css; // WPCS: XSS ok. ?>"
								<?php echo $setting_toggle; // WPCS: XSS ok. ?>>

								<legend class="gmw-settings-panel-title">
									<i class="gmw-icon-cog"></i>
									<?php if ( isset( $option['label'] ) ) { ?>
										<?php echo esc_html( $option['label'] ); ?>                 
									<?php } ?>
								</legend>

								<div class="gmw-settings-panel-content gmw-form-feature-settings <?php echo ! empty( $option['type'] ) ? esc_attr( $option['type'] ) : ''; ?>">

									<div class="gmw-settings-panel-description"><?php echo ( ! empty( $option['desc'] ) ) ? wp_kses( $option['desc'], $allowed_html ) : ''; ?></div>

									<?php if ( 'fields_group' === $option['type'] && array_filter( $option['fields'] ) ) { ?>

										<?php uasort( $option['fields'], 'gmw_sort_by_priority' ); ?>

										<div class="gmw-settings-multiple-fields-wrapper">

											<?php foreach ( $option['fields'] as $option ) { ?>

												<div class="gmw-settings-panel-field gmw-form-feature-settings single-option option-<?php echo esc_attr( $option['name'] ); ?> <?php echo $feature_disbaled; // WPCS: XSS ok. ?> <?php echo ! empty( $option['type'] ) ? esc_attr( $option['type'] ) : ''; ?> <?php echo ! empty( $option['wrap_class'] ) ? esc_attr( $option['wrap_class'] ) : ''; ?>">

													<div class="gmw-settings-panel-header">
														<label class="gmw-settings-label"><?php echo ( ! empty( $option['label'] ) ) ? esc_attr( $option['label'] ) : ''; ?></label>
													</div>

													<div class="gmw-settings-panel-input-container option-type-<?php echo esc_attr( $option['type'] ); ?>">
														<?php $this->get_form_field( $settings, $option, $current_tab, $section ); ?>
													</div>				

													<div class="gmw-settings-panel-description"><?php echo ( ! empty( $option['desc'] ) ) ? wp_kses( $option['desc'], $allowed_html ) : ''; ?></div>
												</div>
											<?php } ?>

										</div>

									<?php } else { ?>

										<div class="gmw-settings-panel-field gmw-form-feature-settings <?php echo $feature_disbaled; // WPCS: XSS ok. ?> <?php echo ! empty( $option['type'] ) ? esc_attr( $option['type'] ) : ''; ?>">
											<div class="gmw-settings-panel-input-container">
												<?php $this->get_form_field( $settings, $option, $current_tab, $section ); ?>
											</div>
										</div>

									<?php } ?>
								</div>

							</fieldset>

						<?php } ?> 

						<div class="gmw-update-settings-button update-button-wrapper bottom">
							<input type="submit" class="gmw-settings-action-button button-primary" value="<?php esc_attr_e( 'Save Changes', 'geo-my-wp' ); ?>" />
						</div>

						<?php wp_nonce_field( 'gmw_settings_save', 'gmw_settings_save_nonce' ); ?>

					</form>
				</div>
			</div>

			<!-- Side bar -->
			<div class="gmw-admin-page-sidebar">
				<?php gmw_admin_sidebar_content(); ?>
			</div>

		</div>

		<script type="text/javascript">

			jQuery( document ).ready( function( $ ) {

				jQuery( '.gmw-settings-panel[data-gmw_toggle_element]' ).each( function() {

					var element     = jQuery( this );
					var toggle      = jQuery( '.setting-' + element.attr( 'data-gmw_toggle_element' ) );
					var toggleValue = element.attr( 'data-gmw_toggle_value' );

					toggle.on( 'change', function() {

						if ( jQuery( this ).val() == toggleValue ) {
							element.show();
						} else {
							element.hide();
						}
					} );

					toggle.trigger( 'change' );
				});

				// Toggle Map & Geocoding providers.
				$( '#setting-api_providers-maps_provider' ).on( 'change', function() {

					var mapProvider = $( '#setting-api_providers-maps_provider' ).val();

					if ( mapProvider == 'leaflet' ) {
						mapProvider = 'nominatim';
					}

					$( '#api_providers-' + mapProvider + '_options-tr' ).show();

					if ( jQuery( '#setting-api_providers-geocoding_provider' ).attr( 'type' ) == 'hidden' ) {
						jQuery( '#setting-api_providers-geocoding_provider' ).val( mapProvider );
					}
				} );

				jQuery( '#setting-api_providers-maps_provider' ).trigger( 'change' );
			});
		</script>
		<?php

		if ( ! wp_script_is( 'jquery-confirm', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery-confirm', GMW_URL . '/assets/lib/jquery-confirm/jquery-confirm.min.js', array( 'jquery' ), GMW_VERSION, true );
			wp_enqueue_style( 'jquery-confirm', GMW_URL . '/assets/lib/jquery-confirm/jquery-confirm.min.css', array(), GMW_VERSION );
		}

		// load select2.
		if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
			wp_enqueue_script( 'select2' );
			wp_enqueue_style( 'select2' );
		}

		if ( ! wp_style_is( 'wp-color-picker', 'enqueued' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}
	}

	/**
	 * Validate inputs
	 *
	 * @param  array $values original values.
	 *
	 * @return array         validated values.
	 */
	public function validate( $values ) {

		$this->init_settings();

		$current_tab = $this->get_current_tab();
		$section     = $this->settings[ $current_tab ];

		foreach ( $section as $key => $option ) {

			if ( 'fields_group' === $option['type'] ) {

				foreach ( $option['fields'] as $of_key => $of_value ) {
					$section[ $of_key ] = $of_value;
				}

				unset( $section[ $key ] );
			}
		}

		// get the submitted values into the valid_input array
		// then below we run validation through the valid_input.
		$valid_input = $values;

		foreach ( $section as $option ) {

			// Generate field options dynamically for fields with options that are generated via AJAX.
			if ( ! empty( $option['attributes']['data-gmw_ajax_load_options'] ) ) {

				$args = array();

				foreach ( $option['attributes'] as $name => $attribute ) {
					$args[ str_replace( 'data-', '', $name ) ] = $attribute;
				}

				$option['options'] = GMW_Form_Settings_Helper::get_field_options( $args );
			}

			switch ( $option['type'] ) {

				case 'tab_section':
					break;

				case 'function':
					if ( ! empty( $values[ $current_tab ][ $option['name'] ] ) ) {
						$valid_input[ $current_tab ][ $option['name'] ] = $values[ $current_tab ][ $option['name'] ];
					}
					break;

				case 'checkbox':
					if ( ! empty( $values[ $current_tab ][ $option['name'] ] ) ) {
						$valid_input[ $current_tab ][ $option['name'] ] = 1;
					}
					break;

				case 'multicheckbox':
					if ( empty( $values[ $current_tab ][ $option['name'] ] ) || ! is_array( $values[ $current_tab ][ $option['name'] ] ) ) {

						$valid_input[ $current_tab ][ $option['name'] ] = is_array( $option['default'] ) ? $option['default'] : array();

					} else {

						foreach ( $option['options'] as $key_val => $name ) {

							if ( ! empty( $values[ $current_tab ][ $option['name'] ][ $key_val ] ) ) {
								$valid_input[ $current_tab ][ $option['name'] ][ $key_val ] = 1;
							}
						}
					}
					break;

				case 'multiselect':
				case 'multiselect_name_value':
					if ( empty( $values[ $current_tab ][ $option['name'] ] ) || ! is_array( $values[ $current_tab ][ $option['name'] ] ) ) {

						$valid_input[ $current_tab ][ $option['name'] ] = is_array( $option['default'] ) ? $option['default'] : array();

					} else {

						$valid_input[ $current_tab ][ $option['name'] ] = array();

						foreach ( $option['options'] as $key_val => $name ) {

							if ( in_array( $key_val, $values[ $current_tab ][ $option['name'] ] ) ) {

								$valid_input[ $current_tab ][ $option['name'] ][] = $key_val;
							}
						}
					}
					break;

				case 'multicheckboxvalues':
					if ( empty( $values[ $current_tab ][ $option['name'] ] ) || ! is_array( $values[ $current_tab ][ $option['name'] ] ) ) {

						$valid_input[ $current_tab ][ $option['name'] ] = is_array( $option['default'] ) ? $option['default'] : array();

					} else {

						$valid_input[ $current_tab ][ $option['name'] ] = array();

						foreach ( $option['options'] as $key_val => $name ) {

							if ( in_array( $key_val, $values[ $current_tab ][ $option['name'] ] ) ) {

								$valid_input[ $current_tab ][ $option['name'] ][] = $key_val;
							}
						}
					}
					break;

				case 'select':
				case 'radio':
					if ( ! empty( $values[ $current_tab ][ $option['name'] ] ) && in_array( $values[ $current_tab ][ $option['name'] ], array_keys( $option['options'] ) ) ) {
						$valid_input[ $current_tab ][ $option['name'] ] = $values[ $current_tab ][ $option['name'] ];
					} else {
						$valid_input[ $current_tab ][ $option['name'] ] = ( ! empty( $option['default'] ) ) ? $option['default'] : '';
					}
					break;

				case 'textarea':
					if ( ! empty( $values[ $current_tab ][ $option['name'] ] ) ) {
						$valid_input[ $current_tab ][ $option['name'] ] = esc_textarea( $values[ $current_tab ][ $option['name'] ] );
					} else {
						$valid_input[ $current_tab ][ $option['name'] ] = ( ! empty( $option['default'] ) ) ? esc_textarea( $option['default'] ) : '';
					}
					break;

				case 'number':
					if ( ! empty( $values[ $current_tab ][ $option['name'] ] ) ) {
						$num_value = sanitize_text_field( $values[ $current_tab ][ $option['name'] ] );

					} else {
						$num_value = isset( $option['default'] ) ? sanitize_text_field( $option['default'] ) : '';
					}

					$valid_value = preg_replace( '/[^0-9]/', '', $num_value );

					break;

				case 'text':
				case 'password':
					if ( ! empty( $values[ $current_tab ][ $option['name'] ] ) ) {
						$valid_input[ $current_tab ][ $option['name'] ] = sanitize_text_field( $values[ $current_tab ][ $option['name'] ] );
					} else {
						$valid_input[ $current_tab ][ $option['name'] ] = ( ! empty( $option['default'] ) ) ? sanitize_text_field( $option['default'] ) : '';
					}
					break;
			}
		}

		return $valid_input;
	}
}
