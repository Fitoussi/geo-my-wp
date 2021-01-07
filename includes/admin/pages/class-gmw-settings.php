<?php
/**
 * GEO my WP Admin Settings class.
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
	 * @return void
	 */
	public function __construct() {

		if ( ( empty( $_GET['page'] ) || 'gmw-settings' !== $_GET['page'] ) && ( empty( $_POST['option_page'] ) || 'gmw_options' !== $_POST['option_page'] ) ) { // WPCS: CSRF ok.
			return;
		}

		$this->settings_group = 'gmw_options';

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Setup default settings values
	 */
	public function setup_defaults() {

		$defaults = apply_filters(
			'gmw_admin_settings_setup_defaults',
			array(
				'general_settings' => array(
					'allow_tracking'       => '',
					'google_map_api_usage' => 'enabled',
					'google_api'           => '',
					'js_geocode'           => 1,
					'country_code'         => 'US',
					'language_code'        => 'EN',
					'results_page'         => '',
					'auto_locate'          => 1,
				),
				'api_providers'    => array(
					'maps_provider'                   => 'google_maps',
					'geocoding_provider'              => 'google_maps',
					'google_maps_client_side_api_key' => '',
					'google_maps_server_side_api_key' => '',
					'nominatim_email'                 => get_bloginfo( 'admin_email' ),
				),
			)
		);

		$gmw_options = get_option( 'gmw_options' );

		if ( empty( $gmw_options ) ) {
			$gmw_options = array();
		}

		$count = 0;

		foreach ( $defaults as $group_name => $values ) {

			if ( empty( $gmw_options[ $group_name ] ) ) {
				$gmw_options[ $group_name ] = $values;
				$count++;
			}
		}

		if ( $count > 0 ) {
			update_option( 'gmw_options', $gmw_options );
		}
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
					'google_maps' => 'Google Maps',
					'leaflet'     => 'LeafLet',
				),
				'geocoding' => array(),
			)
		);

		return apply_filters(
			'gmw_admin_settings_groups',
			array(
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
								__( 'Enter the country code that will be used as the default with the API providers. The country code controls the default region when geocoding an address and when using other services provided by the API providers. List of countries code can be found <a href="%s" target="_blank">here</a>.', 'geo-my-wp' ),
								'http://geomywp.com/country-code/'
							),
							'attributes'  => array( 'size' => '5' ),
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
								__( 'Set the default language to be used with the API providers. The language codes can be found <a href="%s" target="_blank">here</a>.', 'geo-my-wp' ),
								'https://sites.google.com/site/tomihasa/google-language-codes'
							),
							'attributes'  => array( 'size' => '5' ),
							'priority'    => 30,
						),
						'auto_locate'    => array(
							'name'       => 'auto_locate',
							'type'       => 'checkbox',
							'default'    => '',
							'label'      => __( 'Auto Locator', 'geo-my-wp' ),
							'cb_label'   => __( 'Enable', 'geo-my-wp' ),
							'desc'       => __( "GEO my WP will try to retrieve the visitor's current location when first visits the website. If a location was found, it will be saved via cookies and will be used with some of GEO my WP features; such as dynamically displaying results nearby the visitor.", 'geo-my-wp' ),
							'attributes' => array(),
							'priority'   => 40,
						),
						'results_page'   => array(
							'name'       => 'results_page',
							'type'       => 'select',
							'default'    => '0',
							'label'      => __( 'Results Page', 'geo-my-wp' ),
							'desc'       => __( 'The page you select here displays the search results ( of any of your forms ) when using the "GMW Search Form" widget. The plugin will first check if a results page was set in the form settings, and if so, the results will be displayed on that page. Otherwise, if no results page was set in the form settings, the results will be displayed on the page you select here. To use this feature, select the results page from the dropdown menu and paste the shortcode <code>[gmw form="results"]</code> to the content area of the page that you selected.', 'geo-my-wp' ),
							'options'    => $this->get_pages(),
							'attributes' => array(),
							'priority'   => 50,
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
							'default'    => 'google_maps',
							'label'      => __( 'Maps Provider', 'geo-my-wp' ),
							'desc'       => __( 'Select the maps provider that you would like to use.', 'geo-my-wp' ),
							'attributes' => array(),
							'options'    => $api_providers['maps'],
							'priority'   => 10,
						),
						'geocoding_provider'  => array(
							'name'       => 'geocoding_provider',
							'type'       => 'hidden',
							'default'    => 'google_maps',
							'label'      => __( 'Maps Provider', 'geo-my-wp' ),
							'desc'       => __( 'Select the maps provider that you would like to use.', 'geo-my-wp' ),
							'attributes' => array(),
							'options'    => $api_providers['maps'],
							'priority'   => 10,
						),
						'google_maps_options' => array(
							'name'       => 'google_maps_options',
							'type'       => 'fields_group',
							'label'      => __( 'Google Maps API', 'geo-my-wp' ),
							'desc'       => sprintf(
								/* translators: %s: link. */
								__( 'Enter your Google Maps API keys ( See <a href="%s" target="_blank">this tutorial</a> ). Note that GEO my WP now requires 2 Google Maps API keys; a client-side and server-side keys.', 'geo-my-wp' ),
								'https://docs.geomywp.com/article/141-generate-and-setup-google-maps-api-keys'
							),
							'fields'     => array(
								'google_maps_client_side_api_key' => array(
									'name'        => 'google_maps_client_side_api_key',
									'type'        => 'text',
									'default'     => '',
									'label'       => __( 'Google Maps Client-side API key', 'geo-my-wp' ),
									'placeholder' => __( 'Client-side API key', 'geo-my-wp' ),
									'desc'        => __( 'This API key is responsible for displaying maps, directions, address autocomplete, and client-side geocoding when using the location form.', 'geo-my-wp' ),
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
										__( 'This API key is responsible for server side geocoding. Without this key some of GEO my WP functions will not work properly. After generating and entering your server API key, you can test it %1$shere%2$s.', 'geo-my-wp' ),
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
									'desc'       => __( 'Check this checkbox if your server is located in China and Google Maps features are not working on your site.', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 15,
								),
							),
							'attributes' => '',
							'optionsbox' => 1,
							'priority'   => 30,
						),
						'nominatim_options'   => array(
							'name'       => 'nominatim_options',
							'type'       => 'fields_group',
							'label'      => __( 'Nominatim ( OpenStreetMaps )', 'geo-my-wp' ),
							'desc'       => __( 'Setup Nominatim options.', 'geo-my-wp' ),
							'fields'     => array(
								'nominatim_email' => array(
									'name'        => 'nominatim_email',
									'type'        => 'text',
									'default'     => '',
									'placeholder' => __( 'Enter email address', 'geo-my-wp' ),
									'label'       => __( 'Valid email address', 'geo-my-wp' ),
									'desc'        => sprintf(
										/* translators: %1$s: link, %2$s: link. */
										__( 'Nominatim is a geocoding provider for OpenStreetMaps. The provider requires a valid email address to use its services. See this <a href="%1$s" target="_blank">this page</a> to learn more about this service. Also see the Nominatim <a href="%2$s" target="_blank">usage policy</a>.', 'geo-my-wp' ),
										'https://wiki.openstreetmap.org/wiki/Nominatim',
										'https://operations.osmfoundation.org/policies/nominatim/'
									),
									'attributes'  => array( 'size' => '50' ),
									'priority'    => 5,
								),
							),
							'attributes' => '',
							'optionsbox' => 1,
							'priority'   => 40,
						),
					),
					'priority' => 5,
				),
			)
		);
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
	 * @return void
	 */
	protected function init_settings() {

		// generate default values.
		$this->setup_defaults();

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
	 * Get list of pages
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
	 * Register_settings function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_settings() {

		if ( empty( $_POST['option_page'] ) || $_POST['option_page'] !== $this->settings_group ) { // WPCS: CSRF ok.
			return;
		}

		if ( empty( $_POST['action'] ) || 'update' !== $_POST['action'] ) { // WPCS: CSRF ok.
			return;
		}

		register_setting( $this->settings_group, 'gmw_options', array( $this, 'validate' ) );
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

		// get the submitted values into the valid_input array
		// then below we run validation through the valid_input.
		$valid_input = $values;

		foreach ( $this->settings as $section_name => $section ) {

			foreach ( $section as $option ) {

				switch ( $option['type'] ) {

					case 'tab_section':
						break;

					case 'function':
						if ( ! empty( $values[ $section_name ][ $option['name'] ] ) ) {
							$valid_input[ $section_name ][ $option['name'] ] = $values[ $section_name ][ $option['name'] ];
						}
						break;

					case 'checkbox':
						if ( ! empty( $values[ $section_name ][ $option['name'] ] ) ) {
							$valid_input[ $section_name ][ $option['name'] ] = 1;
						}
						break;

					case 'multicheckbox':
						if ( empty( $values[ $section_name ][ $option['name'] ] ) || ! is_array( $values[ $section_name ][ $option['name'] ] ) ) {

							$valid_input[ $section_name ][ $option['name'] ] = is_array( $option['default'] ) ? $option['default'] : array();

						} else {

							foreach ( $option['options'] as $key_val => $name ) {

								if ( ! empty( $values[ $section_name ][ $option['name'] ][ $key_val ] ) ) {
									$valid_input[ $section_name ][ $option['name'] ][ $key_val ] = 1;
								}
							}
						}
						break;

					case 'multiselect':
						if ( empty( $values[ $section_name ][ $option['name'] ] ) || ! is_array( $values[ $section_name ][ $option['name'] ] ) ) {

							$valid_input[ $section_name ][ $option['name'] ] = is_array( $option['default'] ) ? $option['default'] : array();

						} else {

							$valid_input[ $section_name ][ $option['name'] ] = array();

							foreach ( $option['options'] as $key_val => $name ) {

								if ( in_array( $key_val, $values[ $section_name ][ $option['name'] ] ) ) {

									$valid_input[ $section_name ][ $option['name'] ][] = $key_val;
								}
							}
						}
						break;

					case 'multicheckboxvalues':
						if ( empty( $values[ $section_name ][ $option['name'] ] ) || ! is_array( $values[ $section_name ][ $option['name'] ] ) ) {

							$valid_input[ $section_name ][ $option['name'] ] = is_array( $option['default'] ) ? $option['default'] : array();

						} else {

							$valid_input[ $section_name ][ $option['name'] ] = array();

							foreach ( $option['options'] as $key_val => $name ) {

								if ( in_array( $key_val, $values[ $section_name ][ $option['name'] ] ) ) {

									$valid_input[ $section_name ][ $option['name'] ][] = $key_val;
								}
							}
						}
						break;

					case 'select':
					case 'radio':
						if ( ! empty( $values[ $section_name ][ $option['name'] ] ) && in_array( $values[ $section_name ][ $option['name'] ], array_keys( $option['options'] ) ) ) {
							$valid_input[ $section_name ][ $option['name'] ] = $values[ $section_name ][ $option['name'] ];
						} else {
							$valid_input[ $section_name ][ $option['name'] ] = ( ! empty( $option['default'] ) ) ? $option['default'] : '';
						}
						break;

					case 'textarea':
						if ( ! empty( $values[ $section_name ][ $option['name'] ] ) ) {
							$valid_input[ $section_name ][ $option['name'] ] = esc_textarea( $values[ $section_name ][ $option['name'] ] );
						} else {
							$valid_input[ $section_name ][ $option['name'] ] = ( ! empty( $option['default'] ) ) ? esc_textarea( $option['default'] ) : '';
						}
						break;

					case 'number':
						if ( ! empty( $values[ $section_name ][ $option['name'] ] ) ) {
							$num_value = sanitize_text_field( $values[ $section_name ][ $option['name'] ] );

						} else {
							$num_value = isset( $option['default'] ) ? sanitize_text_field( $option['default'] ) : '';
						}

						$valid_value = preg_replace( '/[^0-9]/', '', $num_value );

						break;

					case 'text':
					case 'password':
						if ( ! empty( $values[ $section_name ][ $option['name'] ] ) ) {
							$valid_input[ $section_name ][ $option['name'] ] = sanitize_text_field( $values[ $section_name ][ $option['name'] ] );
						} else {
							$valid_input[ $section_name ][ $option['name'] ] = ( ! empty( $option['default'] ) ) ? sanitize_text_field( $option['default'] ) : '';
						}
						break;
				}
			}
		}

		return $valid_input;
	}

	/**
	 * Get form fields
	 *
	 * @param  array  $settings form settings.
	 * @param  array  $option   field options.
	 * @param  string $tab      tab name.
	 * @param  array  $section  settings section.
	 */
	public function get_form_field( $settings, $option, $tab, $section ) {

		$option['default']  = isset( $option['default'] ) ? $option['default'] : '';
		$option['name']     = esc_attr( $option['name'] );
		$option['cb_label'] = isset( $option['cb_label'] ) ? esc_attr( $option['cb_label'] ) : '';
		$value              = ! empty( $settings[ $tab ][ $option['name'] ] ) ? $settings[ $tab ][ $option['name'] ] : $option['default'];
		$attr_id            = 'setting-' . $tab . '-' . $option['name'];
		$placeholder        = ! empty( $option['placeholder'] ) ? 'placeholder="' . esc_attr( $option['placeholder'] ) . '"' : '';
		$attr_name          = 'gmw_options[' . $tab . '][' . $option['name'] . ']';
		$attributes         = array();

		if ( ! isset( $option['type'] ) ) {
			$option['type'] = 'text';
		}

		// attributes.
		if ( ! empty( $option['attributes'] ) && is_array( $option['attributes'] ) ) {
			foreach ( $option['attributes'] as $attribute_name => $attribute_value ) {
				$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		// display settings fields.
		switch ( $option['type'] ) {

			// create custom function.
			case 'function':
				$function   = ! empty( $option['function'] ) ? $option['function'] : $option['name'];
				$name_attr  = 'gmw_options[' . $tab . '][' . $option['name'] . ']';
				$this_value = ! empty( $settings[ $tab ][ $option['name'] ] ) ? $settings[ $tab ][ $option['name'] ] : array();

				do_action( 'gmw_main_settings_' . $function, $this_value, $name_attr, $settings, $tab, $option );

				break;

			case 'checkbox':
				?>
				<label>
					<input 
						type="checkbox"
						id="<?php echo esc_attr( $attr_id ); ?>"
						class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox" 
						name="<?php echo esc_attr( $attr_name ); ?>"
						value="1" 
						<?php echo implode( ' ', $attributes ); // WPCS: XSS ok. ?>
						<?php checked( '1', $value ); ?> 
					/> 
					<?php echo $option['cb_label']; // WPCS: XSS ok. ?>
				</label>
				<?php
				break;

			case 'multicheckbox':
				foreach ( $option['options'] as $key_val => $name ) {

					$value = ! empty( $value[ $key_val ] ) ? $value[ $key_val ] : $option['default'];
					?>
					<label>
						<input 
							type="checkbox" 
							id="<?php echo esc_attr( $attr_id . '-' . $key_val ); ?>" class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox multicheckbox"
							name="<?php echo esc_attr( $attr_name . '[' . $key_val . ']' ); ?>"
							value="1" <?php checked( '1', $value ); ?> 
						/> 
						<?php echo esc_html( $name ); ?>
					</label>
					<?php
				}
				break;

			case 'multicheckboxvalues':
				$option['default'] = is_array( $option['default'] ) ? $option['default'] : array();

				foreach ( $option['options'] as $key_val => $name ) {

					$checked = in_array( $key_val, $value ) ? 'checked="checked"' : '';
					?>
					<label>
						<input 
							type="checkbox" 
							id="<?php echo esc_attr( $attr_id . '-' . $key_val ); ?>" 
							class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox multicheckboxvalues" 
							name="<?php echo esc_attr( $attr_name ) . '[]'; ?>" 
							value="<?php echo esc_attr( $key_val ); ?>" 
							<?php echo $checked; // WPCS: XSS ok. ?> 
						/> 
						<?php echo esc_html( $name ); ?>
					</label>
					<?php
				}
				break;

			case 'textarea':
				?>
				<textarea 
					id="<?php echo esc_attr( $attr_id ); ?>" 
					class="<?php echo 'setting-' . esc_attr( $option['name'] ); ?> textarea large-text" 
					cols="50" 
					rows="3" 
					name="<?php echo esc_attr( $attr_name ); ?>" 
					<?php echo implode( ' ', $attributes ); // WPCS: XSS ok. ?> 
					<?php echo $placeholder; // WPCS: XSS ok. ?>><?php echo esc_textarea( $value ); ?></textarea>
				<?php
				break;

			case 'radio':
				$rc = 1;
				foreach ( $option['options'] as $key_val => $name ) {

					$checked = ( 1 === $rc ) ? 'checked="checked"' : checked( $value, $key_val, false );
					?>
					<label>
						<input 
							type="radio" 
							id="<?php esc_attr( $attr_id ); ?>" 
							class="setting-<?php echo esc_attr( $option['name'] ); ?>" 
							name="<?php echo esc_attr( $attr_name ); ?>" 
							value="<?php echo esc_attr( $key_val ); ?>"
							<?php echo $checked; // WPCS: XSS ok. ?> 
						/>
						<?php echo esc_attr( $name ); ?>
					</label>
					&nbsp;&nbsp;
					<?php
					$rc++;
				}
				break;

			case 'select':
				?>
				<select 
					id="<?php echo esc_attr( $attr_id ); ?>"
					class="<?php echo 'setting-' . esc_attr( $option['name'] ); ?> select" 
					name="<?php echo esc_attr( $attr_name ); ?>" 
					<?php echo implode( ' ', $attributes ); // WPCS: XSS ok. ?>
				>
					<?php foreach ( $option['options'] as $key_val => $name ) { ?>
						<?php echo '<option value="' . esc_attr( $key_val ) . '" ' . selected( $value, $key_val, false ) . '>' . esc_html( $name ) . '</option>'; ?>
					<?php } ?>
				</select>
				<?php
				break;

			case 'multiselect':
				?>
				<select 
					id="<?php echo esc_attr( $attr_id ); ?>" 
					multiple 
					class="<?php echo 'setting-' . esc_attr( $option['name'] ); ?> select" 
					name="<?php echo esc_attr( $attr_name ); ?>[]" 
					<?php echo implode( ' ', $attributes ); // WPCS: XSS ok. ?>>
					<?php
					foreach ( $option['options'] as $key_val => $name ) {
						$selected = ( is_array( $value ) && in_array( $key_val, $value ) ) ? 'selected="selected"' : '';
						echo '<option value="' . esc_attr( $key_val ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $name ) . '</option>';
					}
					?>
				</select>
				<?php

				break;

			case 'password':
				?>
				<input 
					type="password" 
					id="<?php echo esc_attr( $attr_id ); ?>" 
					class="<?php echo 'setting-' . esc_attr( $option['name'] ); ?> regular-text password" name="<?php echo esc_attr( $attr_name ); ?>" 
					value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>"
					<?php echo implode( ' ', $attributes ); // WPCS: XSS ok. ?> 
					<?php echo $placeholder; // WPCS: XSS ok. ?> 
				/>
				<?php
				break;

			case 'hidden':
				?>
				<input
					type="hidden"
					id="<?php echo esc_attr( $attr_id ); ?>"
					class="<?php echo 'setting-' . esc_attr( $option['name'] ); ?> regular-text password" name="<?php echo esc_attr( $attr_name ); ?>"
					value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>"
					<?php echo implode( ' ', $attributes ); // WPCS: XSS ok. ?> 
				/>
				<?php
				break;

			// number.
			case 'number':
				?>
				<input 
					type="number"
					id="<?php echo esc_attr( $attr_id ); ?>"
					class="<?php echo 'setting-' . esc_attr( $option['name'] ); ?> regular-text text"
					name="<?php echo esc_attr( $attr_name ); ?>"
					value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>"
					<?php echo implode( ' ', $attributes ); // WPCS: XSS ok. ?> 
				/>
				<?php
				break;

			case '':
			case 'input':
			case 'text':
			default:
				?>
				<input 
					type="text" 
					id="<?php echo esc_attr( $attr_id ); ?>"
					class="<?php echo 'setting-' . esc_attr( $option['name'] ); ?> regular-text text" 
					name="<?php echo esc_attr( $attr_name ); ?>"
					value="<?php echo esc_attr( sanitize_text_field( $value ) ); ?>" 
					<?php echo implode( ' ', $attributes ); // WPCS: XSS ok. ?> 
					<?php echo $placeholder; // WPCS: XSS ok. ?> 
				/>
				<?php
				break;
		}
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
		$settings = get_option( 'gmw_options' );
		?>
		<div id="gmw-settings-page" class="wrap gmw-admin-page">

			<h2>
				<i class="gmw-icon-cog-alt"></i>
				<?php esc_attr_e( 'GEO my WP Settings', 'geo-my-wp' ); ?>
				<?php gmw_admin_helpful_buttons(); ?>
			</h2>
			<div class="clear"></div>
			<form method="post" action="options.php" class="gmw-settings-form">
				<?php settings_fields( $this->settings_group ); ?>

				<?php
				if ( ! empty( $_GET['settings-updated'] ) ) { // WPCS: CSRF ok.

					flush_rewrite_rules();

					echo '<div class="updated fade gmw-settings-updated"><p>' . esc_attr__( 'Settings successfully saved!', 'geo-my-wp' ) . '</p></div>';
				}
				?>
				<div class="update-button-wrapper top">
					<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'geo-my-wp' ); ?>" />
				</div>

				<div class="gmw-settings-wrapper gmw-left-tabs-menu-wrapper">

					<ul class="gmw-tabs-wrapper">

						<?php uasort( $this->settings_groups, 'gmw_sort_by_priority' ); ?>

						<?php foreach ( $this->settings_groups as $tab ) { ?>

							<?php
							// for previous versions.
							if ( ! empty( $tab['id'] ) ) {
								$tab['slug'] = $tab['id'];
							}
							?>
							<li>
								<a href="#" 
									id="<?php echo esc_attr( sanitize_title( $tab['slug'] ) ); ?>" 
									title="<?php echo esc_attr( $tab['label'] ); ?>" 
									class="gmw-nav-tab" 
									data-name="<?php echo esc_attr( sanitize_title( $tab['slug'] ) ); ?>"
								>
								<?php if ( ! empty( $tab['icon'] ) ) { ?>
									<i class="gmw-icon-<?php echo esc_attr( $tab['icon'] ); ?>"></i>
								<?php } ?>

								<span><?php echo esc_attr( $tab['label'] ); ?></span>
							</a>
						<?php } ?>
					</ul>

					<div class="gmw-panels-wrapper">

						<?php foreach ( $this->settings as $tab => $section ) { ?>

							<?php uasort( $section, 'gmw_sort_by_priority' ); ?>

							<div class="gmw-tab-panel <?php echo esc_attr( sanitize_title( $tab ) ); ?>">
								<table class="widefat">
									<tbody>

										<?php
										foreach ( $section as $option ) {
											// section tab.
											if ( 'tab_section' === $option['type'] ) {
												?>
												<tr valign="top" class="gmw-tab-section">
													<td><?php echo esc_html( $option['title'] ); ?></td>
													<td></td>    
												</tr>
												<?php
												continue;
											}

											$option['type'] = ! empty( $option['type'] ) ? $option['type'] : '';
											$class          = ! empty( $option['class'] ) ? $option['class'] . ' ' . $option['name'] . ' ' . $option['type'] : $option['name'] . ' ' . $option['type'] . ' ' . $tab;
											?>
											<tr 
												valign="top" 
												id="<?php echo esc_attr( $tab ); ?>-<?php echo esc_attr( $option['name'] ); ?>-tr" 
												class="feature-<?php echo esc_attr( $class ); ?>"
											>

												<td class="gmw-form-feature-desc">              
													<?php if ( isset( $option['label'] ) ) { ?>
														<label for="setting-<?php echo esc_attr( $option['name'] ); ?>">
															<?php echo esc_html( $option['label'] ); ?> 
														</label>                    
													<?php } ?>

													<?php if ( isset( $option['desc'] ) ) { ?>
														<div class="gmw-form-feature-desc-content"> 
															<em class="description">
																<?php
																	echo wp_kses(
																		$option['desc'],
																		array(
																			'a' => array(
																				'href'   => array(),
																				'title'  => array(),
																				'target' => array(),
																			),
																			'code' => array(),
																		)
																	);
																?>
															</em>
														</div>
													<?php } ?>
												</td>
																						   
												<td class="gmw-form-feature-settings <?php echo ! empty( $option['type'] ) ? esc_attr( $option['type'] ) : ''; ?>">  
												<?php if ( 'fields_group' === $option['type'] && array_filter( $option['fields'] ) ) { ?>

													<?php $ob_class = ! empty( $option['optionsbox'] ) ? 'gmw-options-box' : ''; ?>

													<div class="<?php echo $ob_class; // WPCS: XSS ok. ?> <?php
													if ( isset( $option['name'] ) ) {
														echo 'fields-group-' . esc_attr( $option['name'] );}
													?>
													">                                                       
															<?php foreach ( $option['fields'] as $option ) { ?>

																<div class="single-option option-<?php echo esc_attr( $option['name'] ); ?> <?php echo esc_attr( $option['type'] ); ?>">
																	<?php
																	/**
																	If ( $option['type'] == 'checkbox' ) { ?>

																		<?php $this->get_form_field( $settings, $option, $tab, $section ); ?>

																		<?php if ( ! empty( $option['desc'] ) ) { ?>
																			<p class="description"><?php echo $option['desc']; ?></p>
																		<?php } ?>

																	<?php } else { */
																	?>

																	<?php if ( ! empty( $option['label'] ) ) { ?>
																		<label for="setting-<?php echo esc_attr( $option['name'] ); ?>">
																			<?php echo esc_html( $option['label'] ); ?> 
																		</label>                    
																	<?php } ?>

																	<div class="option-content">
																		<?php $this->get_form_field( $settings, $option, $tab, $section ); ?>

																		<?php if ( isset( $option['desc'] ) ) { ?>
																			<p class="description">
																				<?php
																					echo wp_kses(
																						$option['desc'],
																						array(
																							'a' => array(
																								'href'   => array(),
																								'title'  => array(),
																								'target' => array(),
																							),
																							'code' => array(),
																						)
																					);
																				?>
																				</p>
																		<?php } ?>
																	</div>

																</div>
															<?php } ?>
														</div>
													</div>
												<?php } else { ?>
													<?php $this->get_form_field( $settings, $option, $tab, $section ); ?>
												<?php } ?>
											</td>
										</tr>
										<?php } ?> 
									</tbody>
								</table>
							</div>
						<?php } ?>
					</div>
				</div> <!-- menu wrapper -->

				<div class="update-button-wrapper bottom">
					<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'geo-my-wp' ); ?>" />
				</div>
			</form>
		</div>
		<script type="text/javascript">

			jQuery( document ).ready( function( $ ) {

				function gmw_api_providers_setting_changer() {

					$( '.gmw-tab-panel.api_providers' ).find( 'table tr' ).not( '#api_providers-maps_provider-tr, #api_providers-geocoding_provider-tr').hide();

					var mapProvider = $( '#setting-api_providers-maps_provider' ).val();

					if ( mapProvider == 'leaflet' ) {
						mapProvider = 'nominatim';
					}

					$( '#api_providers-' + mapProvider + '_options-tr' ).show();

					if ( jQuery( '#setting-api_providers-geocoding_provider' ).attr( 'type' ) == 'hidden' ) {
						jQuery( '#setting-api_providers-geocoding_provider' ).val( mapProvider );
					}
					//var geocodeProvider = $( '#setting-api_providers-geocoding_provider' ).val();
					//$( '#api_providers-' + geocodeProvider + '_options-tr' ).show();
				}

				gmw_api_providers_setting_changer();

				$( '#setting-api_providers-maps_provider, #setting-api_providers-geocoding_provider' ).on( 'change', function() {
					gmw_api_providers_setting_changer();
				} ); 

				/*function gmw_api_providers_setting_changer() {

					$( '.gmw-tab-panel.api_providers' ).find( 'table tr' ).not( '#api_providers-maps_provider-tr, #api_providers-geocoding_provider-tr').hide();

					var mapProvider = $( '#setting-api_providers-maps_provider' ).val();
					$( '#api_providers-' + mapProvider + '_options-tr' ).show();

					var geocodeProvider = $( '#setting-api_providers-geocoding_provider' ).val();
					$( '#api_providers-' + geocodeProvider + '_options-tr' ).show();
				}

				gmw_api_providers_setting_changer();

				$( '#setting-api_providers-maps_provider, #setting-api_providers-geocoding_provider' ).on( 'change', function() {
					gmw_api_providers_setting_changer();
				} ); */
			});
		</script>
		<?php
		// load select2.
		if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
			wp_enqueue_script( 'select2' );
			wp_enqueue_style( 'select2' );
		}
	}
}
