<?php
/**
 * GEO my WP form editor.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Edit_Form calss
 *
 * Edit GMW forms in the back end
 *
 * @since 2.5
 *
 * @author Fitoussi Eyal
 */
class GMW_Form_Editor {

	/**
	 * Enable / disable ajax in form editor.
	 *
	 * @var boolean
	 */
	public $ajax_enabled = true;

	/**
	 * __construct function.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'wp_ajax_gmw_get_field_options', array( 'GMW_Form_Settings_Helper', 'get_field_options_ajax' ) );
		
		// custom functions.
		add_action( 'gmw_form_settings_info_window_template', array( 'GMW_Form_Editor', 'info_window_template' ), 10, 4 );
		add_filter( 'gmw_validate_form_settings_info_window_template', array( 'GMW_Form_Editor', 'validate_info_window_template' ), 10, 4 );

		// trigger ajax form update.
		if ( apply_filters( 'gmw_form_editor_ajax_enabled', true ) ) {

			$this->ajax_enabled = true;

			add_action( 'wp_ajax_gmw_update_admin_form', array( $this, 'ajax_update_form' ) );
		}

		// Modify form editor for Mashup Map forms.
		// This hooks must be on top otherwise they won't get triggered during the AJAX request when updating the form.
		add_filter( 'gmw_mashup_map_form_default_settings', array( $this, 'mashup_map_default_settings' ), 5, 90 );
		add_filter( 'gmw_mashup_map_form_settings_groups', array( $this, 'mashup_map_form_settings_groups' ), 5, 90 );
		add_filter( 'gmw_mashup_map_form_settings', array( $this, 'mashup_map_form_settings' ), 5, 90 );

		// verify that this is the Form edit page.
		if ( empty( $_GET['page'] ) || 'gmw-forms' !== $_GET['page'] || empty( $_GET['gmw_action'] ) || 'edit_form' !== $_GET['gmw_action'] ) { // WPCS: CSRF ok.
			return;
		}

		do_action( 'gmw_admin_form_editor_init', $_GET ); // WPCS: CSRF ok.

		// Enable only when AJAX submission is disabled.
		if ( ! $this->ajax_enabled ) {
			add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
			add_action( 'gmw_update_admin_form', array( $this, 'update_form' ) );
		}

		// make sure form ID passed.
		if ( empty( $_GET['form_id'] ) || ! absint( $_GET['form_id'] ) ) { // WPCS: CSRF ok.
			wp_die( esc_attr__( 'Form ID is missing.', 'geo-my-wp' ) );
		}

		$form_id = (int) $_GET['form_id'];

		// get form data.
		$this->form = GMW_Forms_Helper::get_form( $form_id );

		if ( empty( $this->form['addon'] ) || ! gmw_is_addon_active( $this->form['addon'] ) ) {

			$allowed = array(
				'a' => array(
					'href' => array(),
				),
			);

			$link = sprintf(
				// Translators: %s extensions page URl.
				wp_kses( __( 'The extension that this form belongs to is deactivated. <a href="%s">Manage extensions</a>.', 'geo-my-wp' ), $allowed ),
				esc_url( 'admin.php?page=gmw-extensions' )
			);

			wp_die( $link ); // WPCS: XSS ok.
		}

		// varify if the form exists.
		if ( empty( $this->form ) ) {
			wp_die( esc_html__( 'The form that you are trying to edit doe\'s not exist!', 'geo-my-wp' ) );
		}

		add_action( 'gmw_form_settings_form_name', array( $this, 'form_name_setting' ), 10, 3 );
		add_action( 'gmw_form_settings_form_usage', array( $this, 'form_usage' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {

		wp_enqueue_code_editor(
			array(
				'type'       => 'text/css',
				'codemirror' => array(
					'autoRefresh' => true,
				),
			)
		);
	}

	/**
	 * GMW Function - add notice messages.
	 *
	 * @param  [type] $messages [description].
	 *
	 * @return [type]           [description]
	 */
	public function notices_messages( $messages ) {

		$messages['form_updated']     = __( 'Form successfully updated.', 'geo-my-wp' );
		$messages['form_not_updated'] = __( 'There was an error while trying to update the form.', 'geo-my-wp' );

		return $messages;
	}

	/**
	 * Mashup Map default settings.
	 *
	 * @param  array $settings settings.
	 *
	 * @param  array $form     form.
	 *
	 * @return [type]           [description]
	 */
	public function mashup_map_default_settings( $settings, $form ) {

		$settings['page_load_results']['enabled']         = 1;
		$settings['page_load_results']['display_results'] = 0;
		$settings['page_load_results']['display_map']     = 'shortcode';
		$settings['page_load_results']['per_page']        = 500;
		$settings['search_form']['form_template']         = '';

		return $settings;
	}

	/**
	 * Modify the form for Mashup Maps.
	 *
	 * Hide some tabs and settigns that are not needed.
	 *
	 * @param  [type] $settings [description].
	 *
	 * @param  [type] $form     [description].
	 *
	 * @return [type]           [description]
	 */
	public function mashup_map_form_settings_groups( $settings, $form ) {

		unset( $settings['no_results'] );

		$settings['page_load_results']['label'] = __( 'Map Filters', 'geo-my-wp' );

		$settings['search_form']['tab_class']       = 'gmw-hidden-form-editor-object';
		$settings['search_form']['panel_class']     = 'gmw-hidden-form-editor-object';
		$settings['search_results']['tab_class']    = 'gmw-hidden-form-editor-object';
		$settings['search_results']['panel_class']  = 'gmw-hidden-form-editor-object';
		$settings['form_submission']['tab_class']   = 'gmw-hidden-form-editor-object';
		$settings['form_submission']['panel_class'] = 'gmw-hidden-form-editor-object';

		return $settings;
	}

	/**
	 * Modify the form settings for Mashup Maps.
	 *
	 * Set some hidden default values.
	 *
	 * @param  [type] $settings [description].
	 *
	 * @param  [type] $form     [description].
	 *
	 * @return [type]           [description]
	 */
	public function mashup_map_form_settings( $settings, $form ) {

		$settings['page_load_results']['enabled']['wrap_class']    = 'gmw-hidden-form-editor-object';
		$settings['page_load_results']['enabled']['default']       = 1;
		$settings['page_load_results']['enabled']['force_default'] = 1;

		$settings['page_load_results']['display_results']['wrap_class']    = 'gmw-hidden-form-editor-object';
		$settings['page_load_results']['display_results']['type']          = 'hidden';
		$settings['page_load_results']['display_results']['default']       = 0;
		$settings['page_load_results']['display_results']['force_default'] = 1;

		$settings['page_load_results']['display_map']['wrap_class']    = 'gmw-hidden-form-editor-object';
		$settings['page_load_results']['display_map']['type']          = 'hidden';
		$settings['page_load_results']['display_map']['default']       = 'shortcode';
		$settings['page_load_results']['display_map']['force_default'] = 1;

		$settings['page_load_results']['per_page']['label']   = __( 'Locations Count', 'geo-my-wp' );
		$settings['page_load_results']['per_page']['desc']    = __( 'Enter the maximum number of locations to show on the map.', 'geo-my-wp' );
		$settings['page_load_results']['per_page']['type']    = 'number';
		$settings['page_load_results']['per_page']['default'] = '';

		$settings['search_form']['form_template']['type']          = 'hidden';
		$settings['search_form']['form_template']['default']       = '';
		$settings['search_form']['form_template']['force_default'] = 1;

		return $settings;
	}

	/**
	 * Form name setting.
	 *
	 * @since 4.0
	 *
	 * @param  mixed  $value     value.
	 *
	 * @param  string $attr_name [description].
	 *
	 * @param  array  $form      form object.
	 */
	public function form_name_setting( $value, $attr_name, $form ) {

		$form_name = ! empty( $form['title'] ) ? $form['title'] : 'form_id_' . $form['ID'];
		?>
		<div class="gmw-settings-panel-field gmw-form-feature-settings form-actions">
			<div class="gmw-settings-panel-input-container">

				<div class="gmw-settings-multiple-fields-wrapper">

					<div class="gmw-settings-panel-input-container option-type-text">
						<input type="text" name="gmw_form[title]" value="<?php echo esc_attr( $form_name ); ?>" placeholder="Form name" />
					</div>
					<div class="gmw-settings-panel-description"><?php esc_attr_e( 'Enter the form\'s name.', 'geo-my-wp' ); ?></div>
					<a
						class="duplicate-form gmw-action-button button-primary"
						title="<?php esc_attr_e( 'Duplicate form', 'geo-my-wp' ); ?>"
						href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=duplicate_form&slug=' . $form['slug'] . '&form_id=' . $form['ID'] ); ?>">
						<?php esc_attr_e( 'Duplicate Form', 'geo-my-wp' ); ?>
					</a>

					<a
						class="delete-form gmw-action-button button-primary" 
						title="<?php esc_attr_e( 'Delete form', 'geo-my-wp' ); ?>" 
						href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=delete_form&form_id=' . $form['ID'] ); ?>" 
						onclick="return confirm( '<?php esc_attr_e( 'This action cannot be undone. Would you like to proceed?', 'geo-my-wp' ); ?>' );">
						<?php esc_html_e( 'Delete Form', 'geo-my-wp' ); ?>
					</a>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Form usage section.
	 *
	 * @param  mixed  $value     value.
	 *
	 * @param  string $attr_name [description].
	 *
	 * @param  array  $form      form object.
	 */
	public function form_usage( $value, $attr_name, $form ) {

		$form_id = absint( $form['ID'] );
		?>
		<div class="gmw-settings-panel-field gmw-form-feature-settings shortcode-usage">
			<table class="widefat gmw-form-shortcode-usage-table">
				<thead>
					<tr>
						<th scope="col" style="width: 33%;"><?php esc_html_e( 'Description', 'geo-my-wp' ); ?></th>
						<th scope="col" style="width: 27%;"><?php esc_html_e( 'Post/Page Content', 'geo-my-wp' ); ?></th>
						<th scope="col" style="width: 40%;"><?php esc_html_e( 'Template file', 'geo-my-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php if ( strpos( $form['slug'], '_mashup_map' ) !== false ) { ?>

						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php esc_html_e( 'Display the mashup map anywhere on the page.', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[gmw map="<?php echo $form_id; // WPCS: XSS ok. ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[gmw map="' . $form_id . '"]\' ); &#63;&#62;'; // WPCS: XSS ok. ?></code></p>
							</td>
						</tr>

					<?php } elseif ( 'global_maps' === $form['addon'] ) { ?>

						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php esc_html_e( 'Display the global map anywhere on the page.', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[gmw_global_map form="<?php echo $form_id; // WPCS: XSS ok. ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[gmw_global_map form="' . $form_id . '"]\' ); &#63;&#62;'; // WPCS: XSS ok. ?></code></p>
							</td>
						</tr>

					<?php } else { ?>

					<?php $scpx = ( 'ajax_forms' !== $form['addon'] ) ? 'gmw' : 'gmw_ajax_form'; ?>

						<div>
							<td class="gmw-form-usage-desc">
								<p><?php esc_html_e( 'Display the complete form ( search form, map, and search results ).', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[<?php echo $scpx; // WPCS: XSS ok. ?> form="<?php echo $form_id; // WPCS: XSS ok. ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[ ' . $scpx . ' form="' . $form_id . '"]\' ); &#63;&#62;'; // WPCS: XSS ok. ?></code></p>
							</td>                			
						</tr>
						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php esc_html_e( 'Display the search form only.', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[<?php echo $scpx; // WPCS: XSS ok. ?> search_form="<?php echo $form_id; // WPCS: XSS ok. ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[ ' . $scpx . ' search_form="' . $form_id . '"]\' ); &#63;&#62;'; // WPCS: XSS ok. ?></code></p>
							</td>		
						</tr>
						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php esc_html_e( 'Display the search results of this form only. Can be used to display the search results in a different page or when using the search form in a widget.', 'geo-my-wp' ); ?></p>
							</td>            
							<td class="gmw-form-usage">
								<p><code>[<?php echo $scpx; // WPCS: XSS ok. ?> search_results="<?php echo $form_id; // WPCS: XSS ok. ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[ ' . $scpx . ' search_results="' . $form_id . '"]\' ); &#63;&#62;'; // WPCS: XSS ok. ?></code></p>
							</td>
						</tr>
						<tr>
							<td class="gmw-form-usage-desc">
								<p><?php esc_html_e( 'Display the results map anywhere on a page. By default, the form you create will display the map above the list of results, but using this shortcode you can display the map anywhere else on the page. Notice that you need to set the "Display map" setting of the "Form Submission" tab to "Using shortcode". ', 'geo-my-wp' ); ?></p>
							</td>
							<td class="gmw-form-usage">
								<p><code>[<?php echo $scpx; // WPCS: XSS ok. ?> map="<?php echo $form_id; // WPCS: XSS ok. ?>"]</code></p>
							</td>
							<td class="gmw-form-usage">
								<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[ ' . $scpx . ' map="' . $form_id . '"]\' ); &#63;&#62;'; // WPCS: XSS ok. ?></code></p>
							</td>    
						</tr>

						<?php if ( 'ajax_forms' !== $form['addon'] ) { ?>
							<tr>
								<td class="gmw-form-usage-desc">
									<p><?php esc_html_e( 'Display the search results of any form.', 'geo-my-wp' ); ?></p>
								</td>
								<td class="gmw-form-usage">
									<p><code>[<?php echo $scpx; // WPCS: XSS ok. ?> form="results"]</code></p>
								</td>
								<td class="gmw-form-usage">
									<p><code><?php echo '&#60;&#63;php echo do_shortcode( \'[gmw form="results"]\' ); &#63;&#62;'; ?></code></p>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Form groups
	 *
	 * @return [type] [description]
	 */
	public function fields_groups() {

		/* translators: %s: link to extensions page. */
		$premium_settings_message = sprintf( __( 'Check out the <a href="%s" target="_blank">Premium Settings extension</a> for additional field options.', 'geo-my-wp' ), 'https://geomywp.com/extensions/premium-settings' );
		$results_page             = array(
			'' => __( ' -- Same Page -- ', 'geo-my-wp' ),
		);

		$template_object = 'posts_locator' === $this->form['addon'] ? 'post' : 'member';

		if ( ! empty( $this->form['form_submission']['results_page'] ) ) {
			$results_page[ $this->form['form_submission']['results_page'] ] = get_the_title( $this->form['form_submission']['results_page'] );
		}

		// settings groups.
		$groups = array(
			'hidden'            => array(
				'slug'     => 'hidden',
				'type'     => 'hidden',
				'label'    => __( 'hidden', 'geo-my-wp' ),
				'fields'   => array(),
				'priority' => 1,
			),
			'general_settings'  => array(
				'slug'     => 'general_settings',
				'type'     => 'function',
				'label'    => __( 'Form Settings', 'geo-my-wp' ),
				'fields'   => array(
					'form_name'       => gmw_get_admin_setting_args(
						array(
							'name'       => 'form_name',
							'type'       => 'function',
							'label'      => __( 'Form Name', 'geo-my-wp' ),
							'wrap_class' => 'always-visible',
							'priority'   => 10,
						),
					),
					'visible_options' => gmw_get_admin_setting_args(
						array(
							'name'       => 'visible_options',
							'type'       => 'checkbox',
							'default'    => '',
							'label'      => __( 'Visible Form Options', 'geo-my-wp' ),
							'desc'       => __( 'Keep form options visible by default.', 'geo-my-wp' ),
							'cb_label'   => __( 'Enable', 'geo-my-wp' ),
							'wrap_class' => 'always-visible',
							'priority'   => 15,
							'sub_option' => false,
						)
					),
					'form_usage'      => gmw_get_admin_setting_args(
						array(
							'name'       => 'form_usage',
							'type'       => 'function',
							'label'      => __( 'Form Usage', 'geo-my-wp' ),
							'wrap_class' => 'always-visible',
							'priority'   => 99,
						),
					),
				),
				'priority' => 5,
			),
			'page_load_results' => array(
				'slug'     => 'page_load_results',
				'type'     => 'fields',
				'label'    => __( 'Page Load Results', 'geo-my-wp' ),
				'notice'   => __( 'Manage the appearance of the form during its intial load ( when the page first loads ).', 'geo-my-wp' ),
				'fields'   => array(
					'enabled'          => array(
						'name'       => 'enabled',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Form Load Options', 'geo-my-wp' ),
						'desc'       => __( 'Dynamically display results when the form first loads.', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'class'      => 'gmw-options-toggle',
						'attributes' => array(),
						'wrap_class' => 'always-visible',
						'priority'   => 2,
					),
					'proximity_filter' => array(
						'name'       => 'proximity_filter',
						'type'       => 'fields_group',
						'label'      => __( 'Proximity Search Filter', 'geo-my-wp' ),
						'desc'       => __( 'Use these options to perform a proximity search query and display nearby locations when the form first loads. You can filter the results based on the user\'s current location ( when available ) or based on a specific address.<br /> When using both options, the plugin will first check for the user\'s current location, and when it is not available, it will use the default address.', 'geo-my-wp' ),
						'fields'     => array(
							'user_location'  => gmw_get_admin_setting_args(
								array(
									'name'       => 'user_location',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'User\'s Current Location Filter', 'geo-my-wp' ),
									'desc'       => __( 'When the form first loads, GEO my WP will check for the users\'s current location and display locations nearby it.', 'geo-my-wp' ),
									'cb_label'   => __( 'Enable', 'geo-my-wp' ),
									'priority'   => 5,
									'sub_option' => false,
								)
							),
							'address_filter' => gmw_get_admin_setting_args(
								array(
									'name'        => 'address_filter',
									'type'        => 'text',
									'default'     => '',
									'placeholder' => __( 'Enter an address', 'geo-my-wp' ),
									'label'       => __( 'Address Filter', 'geo-my-wp' ),
									'desc'        => __( 'Enter an address to search for locations nearby when the form first loads.', 'geo-my-wp' ),
									'priority'    => 10,
									'sub_option'  => false,
								)
							),
							'radius'         => gmw_get_admin_setting_args(
								array(
									'name'        => 'radius',
									'type'        => 'number',
									'default'     => '',
									'placeholder' => __( 'Ex. 100', 'geo-my-wp' ),
									'label'       => __( 'Distance / Radius', 'geo-my-wp' ),
									'desc'        => __( 'Enter a radius ( numeric value ) to search for locations nearby.', 'geo-my-wp' ),
									'priority'    => 15,
									'sub_option'  => false,
								)
							),
							'units'          => gmw_get_admin_setting_args(
								array(
									'name'       => 'units',
									'type'       => 'select',
									'default'    => 'imperial',
									'label'      => __( 'Distance Unit', 'geo-my-wp' ),
									'desc'       => __( 'Select the distance unit.', 'geo-my-wp' ),
									'options'    => array(
										'imperial' => __( 'Miles ( Imperial )', 'geo-my-wp' ),
										'metric'   => __( 'Kilometers ( Metric )', 'geo-my-wp' ),
									),
									'class'      => 'gmw-smartbox-not',
									'priority'   => 20,
									'sub_option' => false,
								)
							),
						),
						'attributes' => array(),
						'priority'   => 15,
					),
					'address_filters'  => array(
						'name'       => 'address_filters',
						'type'       => 'fields_group',
						'label'      => __( 'Address Filters', 'geo-my-wp' ),
						'desc'       => __( 'You can use the filters below ( or leave blank to ignore them ) to filter the search results based on specific address fields. These filters searche GEO my WP\'s database for locations based on the exact keywords ( instead of doing a proximity search query ).<br />Note that when using any of the filters below, the proximity search settings above will be ignored.', 'geo-my-wp' ),
						'fields'     => array(
							'city_filter'    => gmw_get_admin_setting_args(
								array(
									'name'       => 'city_filter',
									'type'       => 'text',
									'default'    => '',
									'label'      => __( 'City Filter', 'geo-my-wp' ),
									'desc'       => __( 'Enter a city name to filter the results.', 'geo-my-wp' ),
									'priority'   => 5,
									'sub_option' => false,
								)
							),
							'state_filter'   => gmw_get_admin_setting_args(
								array(
									'name'       => 'state_filter',
									'type'       => 'text',
									'default'    => '',
									'label'      => __( 'State Filter', 'geo-my-wp' ),
									'desc'       => __( 'Enter a state name to filter the results.', 'geo-my-wp' ),
									'priority'   => 10,
									'sub_option' => false,
								)
							),
							'zipcode_filter' => gmw_get_admin_setting_args(
								array(
									'name'       => 'zipcode_filter',
									'type'       => 'text',
									'default'    => '',
									'label'      => __( 'Zipcode Filter', 'geo-my-wp' ),
									'desc'       => __( 'Enter a zipcode to filter the results.', 'geo-my-wp' ),
									'priority'   => 15,
									'sub_option' => false,
								)
							),
							'country_filter' => gmw_get_admin_setting_args(
								array(
									'name'       => 'country_filter',
									'type'       => 'select',
									'default'    => '',
									'label'      => __( 'Country Filter', 'geo-my-wp' ),
									'desc'       => __( 'Select a country to filter the results.', 'geo-my-wp' ),
									'options'    => gmw_get_countries_list_array( 'Disable' ),
									'priority'   => 20,
									'sub_option' => false,
								)
							),
						),
						'attributes' => array(),
						'priority'   => 20,
					),
					'display_results'  => gmw_get_admin_setting_args(
						array(
							'name'     => 'display_results',
							'type'     => 'checkbox',
							'default'  => '',
							'label'    => __( 'Result list', 'geo-my-wp' ),
							'desc'     => __( 'Check this checkbox to display the list of results.', 'geo-my-wp' ),
							'cb_label' => __( 'Enable', 'geo-my-wp' ),
							'priority' => 25,
						)
					),
					'display_map'      => gmw_get_admin_setting_args(
						array(
							'name'     => 'display_map',
							'type'     => 'select',
							'default'  => '',
							'label'    => __( 'Results Map', 'geo-my-wp' ),
							/* translators: %d: form ID. */
							'desc'     => sprintf( __( 'You can disable the map, display it above the list of result ( when enabled ), or display it anywhere on the page using the shortcode <code>[gmw map="%d"]</code>.', 'geo-my-wp' ), $this->form['ID'] ),
							'options'  => array(
								''          => __( 'Disable the map', 'geo-my-wp' ),
								'results'   => __( 'Display above the list of result', 'geo-my-wp' ),
								'shortcode' => __( 'Display using a shortcode', 'geo-my-wp' ),
							),
							'class'    => 'gmw-smartbox-not',
							'priority' => 30,
						)
					),
					'per_page'         => gmw_get_admin_setting_args(
						array(
							'name'     => 'per_page',
							'type'     => 'text',
							'default'  => '5,10,15,25',
							'label'    => __( 'Results Per Page', 'geo-my-wp' ),
							'desc'     => __( 'Enter a single value that will be the default per-page value or multiple values, comma separated, to display a per-page select dropdown box in the search results.', 'geo-my-wp' ),
							'priority' => 35,
						)
					),
				),
				'priority' => 10,
			),
			'search_form'       => array(
				'slug'     => 'search_form',
				'type'     => 'fields',
				'label'    => __( 'Search Form', 'geo-my-wp' ),
				'notice'   => __( 'Manage the search form appearance, filters, styling and more. You can also disable the search form completely.', 'geo-my-wp' ),
				'fields'   => array(
					'form_template'  => gmw_get_admin_setting_args(
						array(
							'name'       => 'form_template',
							'type'       => 'select',
							'default'    => '',
							'label'      => __( 'Search Form Template', 'geo-my-wp' ),
							'desc'       => __( 'Select the search form template file that you would like to use or select "Disabled" to disable the search form.', 'geo-my-wp' ),
							'attributes'  => array(
								'data-gmw_ajax_load_options'   => 'gmw_get_templates',
								'data-gmw_ajax_load_component' => $this->form['component'],
								'data-gmw_ajax_load_addon'     => $this->form['addon'],
								'data-gmw_ajax_load_type'      => 'search-forms',
							),
							'options'    => $form_templates,
							'class'      => 'gmw-options-toggle',
							'wrap_class' => 'always-visible',
							'priority'   => 5,
						)
					),
					'filters_modal'  => array(
						'name'            => 'filters_modal',
						'type'            => 'fields_group',
						'label'           => __( 'Filters Modal Box', 'geo-my-wp' ),
						'desc'            => __( 'Display some of the form filters inside a popup modal box.', 'geo-my-wp' ),
						'fields'          => array(
							'enabled'      => gmw_get_admin_setting_args(
								array(
									'name'       => 'enabled',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Enable Modal', 'geo-my-wp' ),
									//'desc'       => __( 'Display some of the form filters inside a popup modal box.', 'geo-my-wp' ),
									'cb_label'   => __( 'Enable', 'geo-my-wp' ),
									'class'      => 'gmw-options-toggle',
									'attributes' => array(),
									'priority'   => 5,
								)
							),
							'toggle_label'                => gmw_get_admin_setting_args(
								array(
									'option_type' => 'label',
									'name'        => 'toggle_label',
									'label'       => __( 'Modal Toggle label', 'geo-my-wp' ),
									'desc'        => __( 'Enter the label for the modal box toggle.', 'geo-my-wp' ),
									'priority'    => 10,
								)
							),
							'modal_title'       => gmw_get_admin_setting_args(
								array(
									'name'     => 'modal_title',
									'type'     => 'text',
									'default'  => '',
									'label'    => __( 'Modal Box Title', 'geo-my-wp' ),
									'desc'     => __( 'Enter the title of the modal box or leave empty to hide the title.', 'geo-my-wp' ),
									'priority' => 15,
								)
							),

						),
						'attributes'      => '',
						'priority'        => 8,
					),
					'address_field'  => array(
						'name'            => 'address_field',
						'type'            => 'fields_group',
						'label'           => __( 'Address Field', 'geo-my-wp' ),
						// 'desc'       => __( 'Setup the address field of the search form.', 'geo-my-wp' ),
						'fields'          => array(
							'usage'                => gmw_get_admin_setting_args(
								array(
									'name'          => 'usage',
									'type'          => 'hidden',
									'default'       => 'single',
									'force_default' => true,
									'wrap_class'    => 'single-address-field-option',
									'priority'      => 99,
								)
							),
							'label'                => gmw_get_admin_setting_args(
								array(
									'option_type' => 'label',
									'wrap_class'  => 'single-address-field-option',
								)
							),
							'placeholder'          => gmw_get_admin_setting_args(
								array(
									'option_type' => 'placeholder',
									'wrap_class'  => 'single-address-field-option',
								)
							),
							'address_autocomplete' => gmw_get_admin_setting_args(
								array(
									'name'       => 'address_autocomplete',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Address Autocomplete', 'geo-my-wp' ),
									'desc'       => __( 'Enable Google Map\'s address autocomplete feature.', 'geo-my-wp' ),
									'wrap_class' => 'single-address-field-option',
									'cb_label'   => __( 'Enable', 'geo-my-wp' ),
									'priority'   => 15,
								)
							),
							'locator'              => gmw_get_admin_setting_args(
								array(
									'name'       => 'locator',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Locator Button', 'geo-my-wp' ),
									'desc'       => __( 'Display a locator button inside the address field.', 'geo-my-wp' ),
									'cb_label'   => __( 'Enable', 'geo-my-wp' ),
									'wrap_class' => 'single-address-field-option',
									'priority'   => 20,
								)
							),
							'locator_submit'       => gmw_get_admin_setting_args(
								array(
									'name'       => 'locator_submit',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Locator Button Submission', 'geo-my-wp' ),
									'desc'       => __( 'Dynamically submit the search form when the user\'s location was found via the locator button.', 'geo-my-wp' ),
									'cb_label'   => __( 'Enable', 'geo-my-wp' ),
									'wrap_class' => 'single-address-field-option',
									'priority'   => 25,
								)
							),
							'required'             => gmw_get_admin_setting_args(
								array(
									'option_type' => 'required',
									'wrap_class'  => 'single-address-field-option',
								)
							),
						),
						/* translators: %s premium settings extension page link. */
						'premium_message' => array(
							'class'   => 'GMW_Premium_Settings_Addon',
							'message' => $premium_settings_message,
						),
						'attributes'      => '',
						'priority'        => 12,
					),
					'locator_button' => array(
						'name'       => 'locator_button',
						'type'       => 'fields_group',
						'label'      => __( 'Locator Button', 'geo-my-wp' ),
						'desc'       => __( 'Users can use the locator button to dynamically retrive their current location.', 'geo-my-wp' ),
						'fields'     => array(
							'usage'          => gmw_get_admin_setting_args(
								array(
									'option_type' => 'usage_select',
									'label'       => __( 'Locator Button Usage', 'geo-my-wp' ),
									'options'     => array(
										'disabled' => __( 'Disabled', 'geo-my-wp' ),
										'text'     => __( 'Text button', 'geo-my-wp' ),
										'url'      => __( 'Custom URL Image button', 'geo-my-wp' ),
										'image'    => __( 'Image button', 'geo-my-wp' ),
									),
									'class'       => 'gmw-smartbox-not',
									'priority'    => 5,
								)
							),
							'text'           => gmw_get_admin_setting_args(
								array(
									'name'     => 'text',
									'type'     => 'text',
									'default'  => '',
									'label'    => __( 'Custom Text', 'geo-my-wp' ),
									'desc'     => __( 'Enter the lable for the button.', 'geo-my-wp' ),
									'priority' => 10,
								)
							),
							'url'            => gmw_get_admin_setting_args(
								array(
									'name'        => 'url',
									'type'        => 'text',
									'default'     => GMW_IMAGES . '/locator-images/locate-me-blue.png',
									'label'       => __( 'Custom URL Image', 'geo-my-wp' ),
									'desc'        => __( 'Enter a URL of the image that you would like to use as the locator button.', 'geo-my-wp' ),
									'placeholder' => __( 'Custom URL', 'geo-my-wp' ),
									'priority'    => 10,
								)
							),
							'image'          => gmw_get_admin_setting_args(
								array(
									'name'     => 'image',
									'type'     => 'radio',
									'default'  => '',
									'label'    => __( 'Image Button', 'geo-my-wp' ),
									'desc'     => __( 'Select the image that will be used as the locator button.', 'geo-my-wp' ),
									'options'  => $this->locator_images(),
									'priority' => 10,
								)
							),
							'locator_submit' => gmw_get_admin_setting_args(
								array(
									'name'     => 'locator_submit',
									'type'     => 'checkbox',
									'default'  => '',
									'label'    => __( 'Auto Form Submission', 'geo-my-wp' ),
									'desc'     => __( 'Dynamically submit the search form when the user\'s location was found via the locator button.', 'geo-my-wp' ),
									'cb_label' => __( 'Enable', 'geo-my-wp' ),
									'priority' => 15,
								)
							),
						),
						'attributes' => '',
						'priority'   => 30,
					),
					'radius'         => array(
						'name'            => 'radius',
						'type'            => 'fields_group',
						'label'           => __( 'Radius / Distance Field', 'geo-my-wp' ),
						'fields'          => array(
							'usage'            => gmw_get_admin_setting_args(
								array(
									'option_type' => 'usage_select',
									'default'     => 'dropdown',
									'options'     => array(
										'default' => __( 'Default value', 'geo-my-wp' ),
										'select'  => __( 'Select dropdown', 'geo-my-wp' ),
									),
									'priority'    => 5,
									'class'       => 'gmw-smartbox-not',
								)
							),
							'label'            => gmw_get_admin_setting_args(
								array(
									'option_type' => 'label',
									'wrap_class'  => 'usage_select usage_slider',
								)
							),
							'show_options_all' => gmw_get_admin_setting_args(
								array(
									'option_type' => 'show_options_all',
									'wrap_class'  => 'usage_select',
									'priority'    => 50,
								)
							),
							'options'          => gmw_get_admin_setting_args(
								array(
									'name'       => 'options',
									'type'       => 'textarea',
									'default'    => "5\n10\n25\n50\n100",
									'label'      => __( 'Dropdown Options', 'geo-my-wp' ),
									'desc'       => __( 'Enter each option on a new line. For more control, you can specify both a value and label like this: <br /> 10 : 10 Miles', 'geo-my-wp' ),
									'wrap_class' => 'usage_select',
									'priority'   => 20,
								)
							),
							'default_value'    => gmw_get_admin_setting_args(
								array(
									'name'       => 'default_value',
									'type'       => 'number',
									'default'    => '50',
									'label'      => __( 'Default Value', 'geo-my-wp' ),
									'wrap_class' => 'usage_default usage_slider',
									'priority'   => 20,
								)
							),
							'required'         => gmw_get_admin_setting_args(
								array(
									'option_type' => 'required',
									'name'        => 'required',
									'wrap_class'  => 'usage_select usage_slider',
								)
							),
						),
						'premium_message' => array(
							'class'   => 'GMW_Premium_Settings_Addon',
							'message' => $premium_settings_message,
						),
						'attributes'      => '',
						'priority'        => 40,
					),
					'units'          => array(
						'name'       => 'units',
						'type'       => 'fields_group',
						'label'      => __( 'Distance Unit Field', 'geo-my-wp' ),
						'fields'     => array(
							'options' => gmw_get_admin_setting_args(
								array(
									'name'     => 'options',
									'type'     => 'select',
									'default'  => 'both',
									'label'    => __( 'Usage', 'geo-my-wp' ),
									'desc'     => __( 'Select "Miles" or "Kilometers" to set a default distance unit or select "Dropdown menu filter" to display a "Units" dropdown filter in the search form.', 'geo-my-wp' ),
									'options'  => array(
										'imperial' => __( 'Miles ( Imperial )', 'geo-my-wp' ),
										'metric'   => __( 'Kilometers ( Metric )', 'geo-my-wp' ),
										'both'     => __( 'Dropdown menu filter', 'geo-my-wp' ),
									),
									'priority' => 5,
									'class'    => 'gmw-smartbox-not',
								)
							),
							'label'   => gmw_get_admin_setting_args( 'label' ),
						),
						'attributes' => '',
						'priority'   => 50,
					),
					'submit_button'  => array(
						'name'       => 'submit_button',
						'type'       => 'fields_group',
						'label'      => __( 'Submit Button', 'geo-my-wp' ),
						'fields'     => array(
							'label' => gmw_get_admin_setting_args(
								array(
									'option_type' => 'label',
									'default'     => __( 'Search', 'geo-my-wp' ),
									'label'       => __( 'Button Label', 'geo-my-wp' ),
									'desc'        => __( 'Enter the label for the submit button.', 'geo-my-wp' ),
									'priority'    => 5,
								)
							),
						),
						'attributes' => '',
						'priority'   => 60,
					),
					'styles'         => array(
						'name'     => 'styles',
						'type'     => 'fields_group',
						'label'    => __( 'Form Styling', 'geo-my-wp' ),
						'fields'   => array(
							'disable_enhanced_fields'    => gmw_get_admin_setting_args(
								array(
									'name'       => 'disable_enhanced_fields',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Disabled Enhanced Fields', 'geo-my-wp' ),
									'desc'       => __( 'Disabled enhanced CSS for the form filters.', 'geo-my-wp' ),
									'cb_label'   => __( 'Disable', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 5,
								),
							),
							'disable_core_styles'           => gmw_get_admin_setting_args(
								array(
									'name'       => 'disable_core_styles',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Disable Built-In Style', 'geo-my-wp' ),
									'desc'       => __( 'Chechk this to prevent GEO my WP from applying it\'s core styling on this template file.', 'geo-my-wp' ),
									'cb_label'   => __( 'Disable', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 10,
								),
							),
							'custom_css'         => gmw_get_admin_setting_args(
								array(
									'name'     => 'custom_css',
									'type'     => 'textarea',
									'default'  => '',
									'label'    => __( 'Custom CSS', 'geo-my-wp' ),
									'desc'     => __( 'Use custom CSS to apply custom styling to the template file.', 'geo-my-wp' ),
									'class'    => 'gmw-code-mirror-field',
									'priority' => 15,
								),
							),
							/*'disable_stylesheet' => gmw_get_admin_setting_args(
								array(
									'name'       => 'disable_stylesheet',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Disable Stylesheet', 'geo-my-wp' ),
									'desc'       => __( 'Disable the stylesheet ( CSS file ) of the template file. This can be useful if you\'d like to use the custom CSS above instead of using the stylesheet. This can also imporve performance slightly by not loading an extra CSS file.', 'geo-my-wp' ),
									'cb_label'   => __( 'Disable', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 15,
								),
							),
						),
						'priority' => 99,
					),
				),
				'priority' => 20,
			),
			'form_submission'   => array(
				'slug'     => 'form_submission',
				'type'     => 'fields',
				'label'    => __( 'Form Submission', 'geo-my-wp' ),
				'notice'   => __( 'Manage the form submission actions.', 'geo-my-wp' ),
				'fields'   => array(
					'results_page'    => gmw_get_admin_setting_args(
						array(
							'name'       => 'results_page',
							'type'       => 'select',
							'default'    => '',
							'label'      => __( 'Search Results Page', 'geo-my-wp' ),
							'desc'       => __( 'Select a specific result page when using the "GMW Search Form" widget, or when you wish to display the search form in one page and the list of result in a different page. <br /> To do so, select a page from the dropdown menu then place the shortcode <code>[gmw form="results"]</code> in the content area of that page.<br /> Otherwise, select "Same Page" to display both the search form and search result in the same page.', 'geo-my-wp' ),
							'options'    => $results_page,
							'attributes' => array(
								'data-gmw_ajax_load_options' => 'gmw_get_pages',
							),
							'priority'   => 10,
						)
					),
					'display_results' => gmw_get_admin_setting_args(
						array(
							'name'     => 'display_results',
							'type'     => 'checkbox',
							'default'  => '',
							'label'    => __( 'Result List', 'geo-my-wp' ),
							'desc'     => __( 'Display the list of result on form submission.', 'geo-my-wp' ),
							'cb_label' => __( 'Enable', 'geo-my-wp' ),
							'priority' => 30,
						)
					),
					'display_map'     => gmw_get_admin_setting_args(
						array(
							'name'     => 'display_map',
							'type'     => 'select',
							'default'  => '',
							'label'    => __( 'Results Map', 'geo-my-wp' ),
							/* translators: %d: Form ID. */
							'desc'     => sprintf( __( 'You can disable the map, display it above the list of result ( when enabled ), or display it anywhere on the page using the shortcode <code>[gmw map="%d"]</code>.', 'geo-my-wp' ), $this->form['ID'] ),
							'options'  => array(
								''          => __( 'Disable the map', 'geo-my-wp' ),
								'results'   => __( 'Display above the list of result', 'geo-my-wp' ),
								'shortcode' => __( 'Display using a shortcode', 'geo-my-wp' ),
							),
							'class'    => 'gmw-smartbox-not',
							'priority' => 40,
						)
					),
				),
				'priority' => 30,
			),
			'search_results'    => array(
				'slug'     => 'search_results',
				'type'     => 'fields',
				'label'    => __( 'Search Results', 'geo-my-wp' ),
				'notice'   => __( 'Manage the appearance of the search results. Choose what will be displayed in the list of results.', 'geo-my-wp' ),
				'fields'   => array(
					'results_template' => gmw_get_admin_setting_args(
						array(
							'name'       => 'results_template',
							'type'       => 'select',
							'default'    => 'gray',
							'label'      => __( 'Search Results Template', 'geo-my-wp' ),
							'desc'       => __( 'Select the search result template file.', 'geo-my-wp' ),
							'attributes'  => array(
								'data-gmw_ajax_load_options'   => 'gmw_get_templates',
								'data-gmw_ajax_load_component' => $this->form['component'],
								'data-gmw_ajax_load_addon'     => $this->form['addon'],
								'data-gmw_ajax_load_type'      => 'search-results',
							),
							'options'    => ! empty( $this->form['search_results']['results_template'] ) ? array( $this->form['search_results']['results_template'] => $this->form['search_results']['results_template'] ) : array(),
							'sub_option' => false,
							'wrap_class' => 'always-visible',
							'priority'   => 5,
						)
					),
					'results_view'     => array(
						'name'     => 'results_view',
						'type'     => 'fields_group',
						'label'    => __( 'Results View', 'geo-my-wp' ),
						'fields'   => array(
							'default' => gmw_get_admin_setting_args(
								array(
									'name'     => 'default',
									'type'     => 'select',
									'default'  => 'grid',
									'label'    => __( 'Results View', 'geo-my-wp' ),
									'desc'     => __( 'Select the view style of the search results.', 'geo-my-wp' ),
									'options'  => array(
										'grid' => __( 'Grid view', 'geo-my-wp' ),
										'list' => __( 'List view', 'geo-my-wp' ),
									),
									'priority' => 5,
									'class'    => 'gmw-smartbox-not',
								)
							),
							'toggle'  => gmw_get_admin_setting_args(
								array(
									'name'     => 'toggle',
									'type'     => 'checkbox',
									'default'  => '',
									'label'    => __( 'Enable View Toggle', 'geo-my-wp' ),
									'desc'     => __( 'Enable toggle in the search results that switches between grid and list view.', 'geo-my-wp' ),
									'cb_label' => __( 'Enable', 'geo-my-wp' ),
									'priority' => 10,
								)
							),
						),
						'priority' => 10,
					),
					'per_page'         => gmw_get_admin_setting_args(
						array(
							'name'        => 'per_page',
							'type'        => 'text',
							'default'     => '5,10,15,25',
							'placeholder' => __( 'Enter values', 'geo-my-wp' ),
							'label'       => __( 'Results Per Page', 'geo-my-wp' ),
							'desc'        => __( 'Enter a single value that will be the default per-page value, or enter multiple values, comma separated, to display a per-page select dropdown menu in the search results.', 'geo-my-wp' ),
							'priority'    => 15,
						)
					),
					'address'            => array(
						'name'       => 'address',
						'type'       => 'fields_group',
						'label'      => __( 'Address', 'geo-my-wp' ),
						'fields'     => array(
							'enabled'      => gmw_get_admin_setting_args(
								array(
									'name'       => 'enabled',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Display Address', 'geo-my-wp' ),
									'desc'       => __( 'Display the address of each location in the list of result.', 'geo-my-wp' ),
									'cb_label'   => __( 'Enable', 'geo-my-wp' ),
									'class'      => 'gmw-options-toggle',
									'attributes' => array(),
									'priority'   => 5,
								)
							),
							'linked'      => gmw_get_admin_setting_args(
								array(
									'name'       => 'linked',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Link Address To Google Maps.', 'geo-my-wp' ),
									'desc'       => __( 'Clicking on the address will open a new window showing the location on Google Map.', 'geo-my-wp' ),
									'cb_label'   => __( 'Enable', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 20,
								)
							),
						),
						'attributes' => '',
						'priority'   => 25,
						'iw_option'  => true,
					),
					'distance'  => gmw_get_admin_setting_args(
						array(
							'name'     => 'distance',
							'type'     => 'checkbox',
							'default'  => '',
							'label'    => __( 'Distance', 'geo-my-wp' ),
							'cb_label' => __( 'Enable', 'geo-my-wp' ),
							'desc'     => __( 'Display the distance.', 'geo-my-wp' ),
							'priority' => 30,
							'iw_option' => true,
						)
					),
					'directions_link'  => gmw_get_admin_setting_args(
						array(
							'name'     => 'directions_link',
							'type'     => 'checkbox',
							'default'  => '',
							'label'    => __( 'Directions Link', 'geo-my-wp' ),
							'cb_label' => __( 'Enable', 'geo-my-wp' ),
							'desc'     => __( 'Display directions link of each location in the list of result. The link will open a new window showing the driving directions on Google Map.', 'geo-my-wp' ),
							'priority' => 35,
							'iw_option' => true,
						)
					),
					'image'            => array(
						'name'       => 'image',
						'type'       => 'fields_group',
						'label'      => __( 'Image', 'geo-my-wp' ),
						'fields'     => array(
							'enabled'      => gmw_get_admin_setting_args(
								array(
									'name'       => 'enabled',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Display Image', 'geo-my-wp' ),
									'desc'       => __( 'Display the image ( featured image, avatar, etc ) of each location in the list of result.', 'geo-my-wp' ),
									'cb_label'   => __( 'Enable', 'geo-my-wp' ),
									'class'      => 'gmw-options-toggle',
									'attributes' => array(),
									'priority'   => 5,
								)
							),
							'width'        => gmw_get_admin_setting_args(
								array(
									'name'        => 'width',
									'type'        => 'text',
									'default'     => '150px',
									'label'       => __( 'Image Width', 'geo-my-wp' ),
									'desc'        => __( 'Enter the image width ( 100%, 200px, etc... ). ', 'geo-my-wp' ),
									'placeholder' => 'image width',
									'priority'    => 10,
								)
							),
							'height'       => gmw_get_admin_setting_args(
								array(
									'name'        => 'height',
									'type'        => 'text',
									'default'     => '150px',
									'label'       => __( 'Image Height', 'geo-my-wp' ),
									'desc'        => __( 'Enter the image height ( 100%, 200px, etc... ). ', 'geo-my-wp' ),
									'placeholder' => 'image height',
									'priority'    => 15,
								)
							),
							'no_image_url' => gmw_get_admin_setting_args(
								array(
									'name'        => 'no_image_url',
									'type'        => 'text',
									'default'     => '',
									'label'       => __( 'No Image URL', 'geo-my-wp' ),
									/* translators: %s: Original no image URL. */
									'desc'        => sprintf( __( 'Enter a URL to an image that will be displayed when no image was found for the object. Otherwise, leave blank to show nothing instead. The URL for GEO my WP\'s default no-image is %s.', 'geo-my-wp' ), GMW_IMAGES . '/no-image.jpg' ),
									'placeholder' => 'No Image URL',
									'priority'    => 20,
								)
							),
						),
						'attributes' => '',
						'priority'   => 30,
					),
					'location_meta'    => gmw_get_admin_setting_args(
						array(
							'name'        => 'location_meta',
							'type'        => 'multiselect',
							'default'     => '',
							'label'       => __( 'Location Meta', 'geo-my-wp' ),
							'placeholder' => __( 'Select location metas', 'geo-my-wp' ),
							'desc'        => __( 'Select the the location meta fields that you would like to display of each location in the list of results.', 'geo-my-wp' ),
							'options'     => ! empty( $this->form['search_results']['location_meta'] ) ? array_flip( $this->form['search_results']['location_meta'] ) : array(),
							'attributes'  => array(
								'data-gmw_ajax_load_options' => 'gmw_get_location_meta',
								'data-sortable'      => '1',
								'data-options_order' => ( ! empty( $this->form['search_results']['location_meta'] ) && is_array( $this->form['search_results']['location_meta'] ) ) ? implode( ',', $this->form['search_results']['location_meta'] ) : '',
							),
							'priority'    => 35,
						)
					),
					'opening_hours'    => gmw_get_admin_setting_args(
						array(
							'name'     => 'opening_hours',
							'type'     => 'checkbox',
							'default'  => '',
							'label'    => __( 'Hours of Operation', 'geo-my-wp' ),
							'cb_label' => __( 'Enable', 'geo-my-wp' ),
							'desc'     => __( 'Display the days & hours of operation of each location in the list of results.', 'geo-my-wp' ),
							'priority' => 40,
						)
					),
					'directions_link'  => gmw_get_admin_setting_args(
						array(
							'name'     => 'directions_link',
							'type'     => 'checkbox',
							'default'  => '',
							'label'    => __( 'Directions Link', 'geo-my-wp' ),
							'cb_label' => __( 'Enable', 'geo-my-wp' ),
							'desc'     => __( 'Display directions link of each location in the list of result. The link will open a new window showing the driving directions on Google Map.', 'geo-my-wp' ),
							'priority' => 45,
						)
					),
					'styles'           => array(
						'name'     => 'styles',
						'type'     => 'fields_group',
						'label'    => __( 'Styling', 'geo-my-wp' ),
						'fields'   => array(
							'disable_enhanced_fields'              => gmw_get_admin_setting_args(
								array(
									'name'       => 'disable_enhanced_fields',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Disable Enhanced Fields', 'geo-my-wp' ),
									'desc'       => __( 'Disable enhanced CSS for the search results filters.', 'geo-my-wp' ),
									'cb_label'   => __( 'Disable', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 5,
								),
							),
							'disable_core_styles'           => gmw_get_admin_setting_args(
								array(
									'name'       => 'disable_core_styles',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Disable Built-In Styles', 'geo-my-wp' ),
									'desc'       => __( 'Chechk this to prevent GEO my WP from applying it\'s core styling on this template file.', 'geo-my-wp' ),
									'cb_label'   => __( 'Disable', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 10,
								),
							),
							/*'disable_stylesheet'           => gmw_get_admin_setting_args(
								array(
									'name'       => 'disable_stylesheet',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Disable Stylesheet', 'geo-my-wp' ),
									'desc'       => __( 'Disable the stylesheet ( CSS file ) of the template file. This can be useful if you\'d like to use the custom CSS above instead of using the stylesheet. This can also imporve performance slightly by not loading an extra CSS file.', 'geo-my-wp' ),
									'cb_label'   => __( 'Disable', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 15,
								),
							),*/
							'disable_single_item_template' => gmw_get_admin_setting_args(
								array(
									'name'       => 'disable_single_item_template',
									'type'       => 'checkbox',
									'default'    => '',
									'label'      => __( 'Disable Single Item Template', 'geo-my-wp' ),
									/* translators: %s object name. */
									'desc'       => sprintf( __( 'Disable the template file that executes the single item in the loop. You can use this to apply a custom single item function using the action hook <code>do_action( \'gmw_search_results_single_item_template\', $%s, $gmw );</code>', 'geo-my-wp' ), $template_object ),
									'cb_label'   => __( 'Disable', 'geo-my-wp' ),
									'attributes' => array(),
									'priority'   => 15,
								),
							),
							'custom_css'                   => gmw_get_admin_setting_args(
								array(
									'name'     => 'custom_css',
									'type'     => 'textarea',
									'default'  => '',
									'label'    => __( 'Custom CSS', 'geo-my-wp' ),
									'desc'     => __( 'Use custom CSS to apply custom styling to the template file.', 'geo-my-wp' ),
									'class'    => 'gmw-code-mirror-field',
									'priority' => 20,
								),
							),
						),
						'priority' => 99,
						'iw_option'  => true,
					),
				),
				'priority' => 40,
			),
			'results_map'       => array(
				'slug'     => 'results_map',
				'type'     => 'fields',
				'label'    => __( 'Map Settings', 'geo-my-wp' ),
				'notice'   => __( 'Manage the appearance of the map.', 'geo-my-wp' ),
				'fields'   => array(
					'map_width'          => gmw_get_admin_setting_args(
						array(
							'name'     => 'map_width',
							'type'     => 'text',
							'default'  => '100%',
							'label'    => __( 'Map width', 'geo-my-wp' ),
							'desc'     => __( 'Enter the map\'s width ( 100%, 200px, etc... ).', 'geo-my-wp' ),
							'priority' => 10,
						)
					),
					'map_height'         => gmw_get_admin_setting_args(
						array(
							'name'     => 'map_height',
							'type'     => 'text',
							'default'  => '300px',
							'label'    => __( 'Map height', 'geo-my-wp' ),
							'desc'     => __( 'Enter the map\'s height ( 100%, 200px, etc... ).', 'geo-my-wp' ),
							'priority' => 20,
						)
					),
					'zoom_level'         => gmw_get_admin_setting_args(
						array(
							'name'       => 'zoom_level',
							'default'    => 'auto',
							'type'       => 'select',
							'label'      => __( 'Zoom level', 'geo-my-wp' ),
							'desc'       => __( 'Select "Auto zoom" to fit all the markers on the map, or select a numeric value that will be used to zoom into the marker that represents the user\'s current location on the map.', 'geo-my-wp' ),
							'options'    => array(
								'auto' => __( 'Auto Zoom', 'geo-my-wp' ),
								'1'    => '1',
								'2'    => '2',
								'3'    => '3',
								'4'    => '4',
								'5'    => '5',
								'6'    => '6',
								'7'    => '7',
								'8'    => '8',
								'9'    => '9',
								'10'   => '10',
								'11'   => '11',
								'12'   => '12',
								'13'   => '13',
								'14'   => '14',
								'15'   => '15',
								'16'   => '16',
								'17'   => '17',
								'18'   => '18',
								'19'   => '19',
								'20'   => '20',
							),
							'class'      => 'gmw-smartbox-not',
							'attributes' => '',
							'priority'   => 40,
						)
					),
					/*'no_results_enabled' => gmw_get_admin_setting_args(
						array(
							'name'          => 'no_results_enabled',
							'type'          => 'hidden',
							'default'       => 1,
							'force_default' => 1,
							'priority'      => 99,
						)
					),*/
				),
				'priority' => 50,
			),
			'info_window'       => array(
				'slug'     => 'info_window',
				'type'     => 'fields',
				'label'    => __( 'Info Window', 'geo-my-wp' ),
				'notice'   => __( 'Manage the appearance of the marker\'s info window.', 'geo-my-wp' ),
				'fields'   => array(
					'iw_appearance'           => array(
						'name'       => 'iw_appearance',
						'type'       => 'fields_group',
						'label'      => __( 'Appearance', 'geo-my-wp' ),
						'wrap_class' => 'always-visible',
						'fields'     => array(
							'iw_type'          => gmw_get_admin_setting_args(
								array(
									'name'       => 'iw_type',
									'type'       => 'select',
									'default'    => 'popup',
									'label'      => __( 'Info Window Type', 'gmw-ajax-forms' ),
									'desc'       => __( 'Select the info-window type.', 'gmw-ajax-forms' ),
									'options'    => array(
										'standard' => 'Standard',
										'popup'    => 'Popup Window',
									),
									'class'      => 'gmw-smartbox-not',
									'attributes' => array(),
									'priority'   => 5,
									'sub_option' => false,
								)
							),
							'template'         => gmw_get_admin_setting_args(
								array(
									'name'       => 'template',
									'type'       => 'function',
									'default'    => 'default',
									// name it ajaxfms_info_window_template instead of info_window_template
									// to prevent conflict with premium settings addon.
									'function'   => 'info_window_template',
									'label'      => __( 'Template', 'gmw-ajax-forms' ),
									'desc'       => __( 'Select the info window template.', 'gmw-ajax-forms' ),
									'class'      => 'gmw-smartbox-not',
									'attributes' => array(),
									'priority'   => 15,
									'sub_option' => false,
								)
							),
						),
						'priority'   => 5,
					),
				),
				'priority' => 60,
			),
		);
	
		$disable_iw = apply_filters( 'gmw_form_editor_disable_info_window', true, $this->form, $groups );

		// Contact info and hours of operation settings are disabled by default. It can be enabled using this filter.
		if ( $disable_iw ) {
			unset( $groups['info_window'] );
		}

		if ( ! class_exists( 'GMW_Premium_Settings_Addon' ) ) {

			$premium_settings_data = array(
				'extension_slug'    => 'premium_settings',
				'extension_url'     => 'https://geomywp.com/extensions/premium-settings',
				'extension_name'    => 'Premium Settings',
				'extension_content' => 'Enhance GEO my WP forms with additional taxonomy options, custom map markers, AJAX info-windows, keywords search box, markers clusters, "no results" options, and more...',
			);

			$groups['no_results'] = array_merge(
				array(
					'slug'     => 'no_results',
					'type'     => 'premium',
					'label'    => 'No Results',
					'fields'   => array(),
					'priority' => 100,
				),
				$premium_settings_data,
			);

			$groups['map_markers'] = array_merge(
				array(
					'slug'     => 'map_markers',
					'type'     => 'premium',
					'label'    => 'Map Markers',
					'fields'   => array(),
					'priority' => 101,
				),
				$premium_settings_data,
			);

			if ( ! isset( $groups['info_window'] ) ) {

				$groups['info_window'] = array_merge(
					array(
						'slug'     => 'info_window',
						'type'     => 'premium',
						'label'    => 'Info Window',
						'fields'   => array(),
						'priority' => 102,
					),
					$premium_settings_data,
				);
			}
		}

		if ( ! class_exists( 'GMW_Exclude_Locations_Addon' ) ) {

			$groups['exclude_locations'] = array(
				'slug'              => 'exclude_locations',
				'type'              => 'premium',
				'label'             => 'Exclude Locations',
				'fields'            => array(),
				'priority'          => 103,
				'extension_slug'    => 'exclude_locations',
				'extension_url'     => 'https://geomywp.com/extensions/exclude-locations',
				'extension_name'    => 'Exclude Locations',
				'extension_content' => 'Exclude locations based on user roles, user ID, post IDs, member types, and more...',
			);
		}

		$groups['results_map']['fields']['map_type'] = gmw_get_admin_setting_args(
			array(
				'name'          => 'map_type',
				'type'          => 'hidden',
				'default'       => 'ROADMAP',
				'force_default' => true,
				'label'         => __( 'Map type', 'geo-my-wp' ),
				'desc'          => __( 'Select the map type.', 'geo-my-wp' ),
				'class'         => 'gmw-smartbox-not',
				'priority'      => 30,
			)
		);

		if ( 'google_maps' === GMW()->maps_provider ) {

			if ( isset( $groups['results_map'] ) ) {

				$groups['results_map']['fields']['map_type']['type']    = 'select';
				$groups['results_map']['fields']['map_type']['options'] = array(
					'ROADMAP'   => __( 'ROADMAP', 'geo-my-wp' ),
					'SATELLITE' => __( 'SATELLITE', 'geo-my-wp' ),
					'HYBRID'    => __( 'HYBRID', 'geo-my-wp' ),
					'TERRAIN'   => __( 'TERRAIN', 'geo-my-wp' ),
				);
			}
		} else {

			$groups['search_form']['fields']['address_field']['fields']['address_autocomplete']['attributes'] = array(
				'disabled' => 'disabled',
			);

			$groups['search_form']['fields']['address_field']['fields']['address_autocomplete']['desc'] .= '<br /><span style="color:red">' . __( 'This feature is available with Google Maps provider only.', 'geo-my-wp' ) . '</span>';
		}

		$disable_additional_fields = apply_filters( 'gmw_form_editor_disable_additional_fields', true, $groups, $this->form['slug'], $this );
		$disable_additional_fields = apply_filters( 'gmw_' . $this->form['slug'] . '_form_editor_disable_additional_fields', $disable_additional_fields, $groups, $this->form['slug'], $this );

		// Contact info and hours of operation settings are disabled by default. It can be enabled using this filter.
		if ( $disable_additional_fields ) {
			unset( $groups['search_results']['fields']['location_meta'], $groups['search_results']['fields']['opening_hours'] );
		}

		$temp_array = array();

		// generate slug for groups. To easier unset groups if needed.
		foreach ( $groups as $group ) {

			$temp_array[ $group['slug'] ] = $group;

			foreach ( $group['fields'] as $sub_group ) {

				if ( 'fields_group' === $sub_group['type'] ) {

					$temp_array[ $group['slug'] ]['fields'][ $sub_group['name'] ]['fields'] = array();

					foreach ( $sub_group['fields'] as $last_group ) {
						$temp_array[ $group['slug'] ]['fields'][ $sub_group['name'] ]['fields'][ $last_group['name'] ] = $last_group;
					}
				} else {

					$temp_array[ $group['slug'] ]['fields'][ $sub_group['name'] ] = $sub_group;
				}
			}
		}

		$groups = $temp_array;
		$groups = apply_filters( 'gmw_' . $this->form['component'] . '_component_form_settings_groups', $groups, $this->form );
		$groups = apply_filters( 'gmw_' . $this->form['slug'] . '_form_settings_groups', $groups, $this->form );
		$groups = apply_filters( 'gmw_' . $this->form['addon'] . '_addon_form_settings_groups', $groups, $this->form );

		if ( strpos( $this->form['slug'], '_mashup_map' ) !== false ) {
			$groups = apply_filters( 'gmw_mashup_map_form_settings_groups', $groups, $this->form );
			$groups = apply_filters( 'gmw_' . $this->form['slug'] . '_mashup_map_form_settings_groups', $groups, $this->form );
		}

		$groups = apply_filters( 'gmw_form_settings_groups', $groups, $this->form );

		// Add the new 'type' argument if missing.
		foreach ( $groups as $key => $group ) {

			if ( empty( $group['type'] ) ) {
				$groups[ $key ]['type'] = 'fields';
			}
		}

		uasort( $groups, 'gmw_sort_by_priority' );

		return $groups;
	}

	/**
	 * Get nfo_window_template files
	 *
	 * @param  string $value       info window template value.
	 * @param  string $name_attr   name attribute.
	 * @param  array  $form        gmw form.
	 * @param  array  $settings form fields.
	 */
	public static function info_window_template( $value, $name_attr, $form, $settings ) {

		echo '<div id="info-window-templates-wrapper">';

		$iw_types = $settings['info_window']['iw_appearance']['fields']['iw_type']['options'];

		foreach ( $iw_types as $iw_name => $iw_title ) {

			// get templates.
			$templates = gmw_get_info_window_templates( $form['component'], $iw_name );

			// Get templates from deprecated location in theme's folder.
			// Used to be in geo-my-wp/core-extensions/extensions-name/info-window
			// now it is in geo-my-wp/core-extensions/info-window.
			$dep_loc_templates = gmw_get_info_window_templates( $form['component'], $iw_name, 'ajax_forms' );
 
			$templates = array_merge( $templates, $dep_loc_templates );
			?>
			<div class="gmw-info-window-template <?php echo esc_attr( $iw_name ); ?>" style="display:none;">
				<select name="<?php echo esc_attr( $name_attr . '[' . $iw_name . ']' ); ?>" class="gmw-smartbox-not">			

					<?php foreach ( $templates as $template_value => $template_name ) { ?>

						<?php $selected = ( isset( $value[ $iw_name ] ) && $value[ $iw_name ] === $template_value ) ? 'selected="selected"' : ''; ?>

						<option value="<?php echo esc_attr( $template_value ); ?>" <?php echo $selected;  // WPCS: XSS ok. ?>>
							<?php echo esc_html( $template_name ); ?>	
						</option>
					<?php } ?>
				</select>
			</div>
			<?php
		}
		?>
		</div>
		<?php
	}

	/**
	 * Validate info window settings
	 *
	 * @param  string $output info window template value.
	 *
	 * @return validate value.
	 */
	public static function validate_info_window_template( $output ) {

		if ( ! is_array( $output ) ) {
			$output = array();
		}

		$output = array_map( 'sanitize_key', $output );

		return $output;
	}

	/**
	 * Get fields
	 *
	 * @return [type] [description]
	 */
	public function get_fields() {

		$fields = array();

		// loop through settings groups.
		foreach ( $this->form_settings_groups as $key => $group ) {

			// verify groups slug.
			if ( empty( $group['slug'] ) ) {
				continue;
			}

			// Generate the group if does not exsist.
			if ( ! isset( $fields[ $group['slug'] ] ) ) {

				$fields[ $group['slug'] ] = ! empty( $group['fields'] ) ? $group['fields'] : array();

				// Otherwise, merge the fields of the existing group
				// with the current group.
			} else {

				$fields[ $group['slug'] ] = array_merge_recursive( $fields[ $group['slug'] ], $group['fields'] );

				// remove the duplicate group/tab.
				unset( $this->form_settings_groups[ $key ] );
			}

			// allow filtering the specific group.
			$fields[ $group['slug'] ] = apply_filters( 'gmw_' . $group['slug'] . '_form_settings', $fields[ $group['slug'] ], $this->form['slug'], $this->form );
		}

		// filter all fields groups.
		$fields = apply_filters( 'gmw_' . $this->form['component'] . '_component_form_settings', $fields, $this->form );
		$fields = apply_filters( 'gmw_' . $this->form['slug'] . '_form_settings', $fields, $this->form );
		$fields = apply_filters( 'gmw_' . $this->form['addon'] . '_addon_form_settings', $fields, $this->form );

		if ( strpos( $this->form['slug'], '_mashup_map' ) !== false ) {
			$fields = apply_filters( 'gmw_mashup_map_form_settings', $fields, $this->form );
			$fields = apply_filters( 'gmw_' . $this->form['slug'] . '_mashup_map_form_settings', $fields, $this->form );
		}

		$fields = apply_filters( 'gmw_form_settings', $fields, $this->form );

		// Generate optoons for info-window using the search results options.
		if ( isset( $fields['info_window'] ) ) {

			foreach ( $fields['search_results'] as $option ) {

				if ( ! empty( $option['iw_option'] ) ) {

					$fields['info_window'][ $option['name'] ] = $option;
				}
			}

			if ( ! empty( $fields['info_window']['location_meta'] ) ) {
				$fields['info_window']['location_meta']['options'] = ! empty( $this->form['info_window']['location_meta'] ) ? array_combine( $this->form['info_window']['location_meta'], $this->form['info_window']['location_meta'] ) : array();
			}
		}

		return $fields;
	}

	/**
	 * Get premium Message.
	 *
	 * @param  [type] $message [description].
	 *
	 * @return [type]          [description]
	 */
	public function get_premium_message( $message ) {

		$allowed = array(
			'a'  => array(
				'href'   => array(),
				'target' => array(),
				'title'  => array(),
			),
			'p'  => array(
				'class' => array(),
			),
			'br' => array(),
		);

		return wp_kses( $message, $allowed );
	}

	/**
	 * Form Fields
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function init_form_settings() {

		// get groups.
		$this->form_settings_groups = $this->fields_groups();

		// get fields.
		$this->form_fields = $this->get_fields();

		// backward capability for settings before settings groups were created.
		foreach ( $this->form_fields as $key => $section ) {

			if ( ! empty( $section[0] ) && ! empty( $section[1] ) && is_string( $section[0] ) ) {

				gmw_trigger_error( 'Using deprecated method for registering GMW settings and settings groups.', E_USER_NOTICE );

				$this->form_settings_groups[] = array(
					'slug'  => $key,
					'label' => $section[0],
				);

				$this->form_fields[ $key ] = $section[1];
			}
		}
	}

	/**
	 * Get locator button images
	 *
	 * @return [type] [description]
	 */
	public function locator_images() {

		$locator_images = glob( GMW_PATH . '/assets/images/locator-images/*.png' );
		$display_image  = GMW_IMAGES . '/locator-images/';

		$options = array();

		foreach ( $locator_images as $locator_image ) {
			$basename                         = basename( $locator_image );
			$options[ basename( $basename ) ] = '<img src="' . esc_url( $display_image . $basename ) . '"/>';
		}

		return $options;
	}

	/**
	 * Get form fields.
	 *
	 * @param  [type] $options      [description].
	 *
	 * @param  [type] $tab          [description].
	 *
	 * @param  [type] $fields_group [description].
	 */
	public function get_form_field( $options = array(), $tab = '', $fields_group = '' ) {

		$options['name'] = esc_attr( $options['name'] );

		if ( empty( $options['type'] ) ) {
			$options['type'] = 'text';
		}

		// display settings fields.
		switch ( $options['type'] ) {

			// custom function.
			case 'function':
				if ( ! empty( $fields_group ) && ! empty( $options['sub_option'] ) ) {

					$attr_name        = 'gmw_form[' . $tab . '][' . $fields_group . '][' . $options['name'] . ']';
					$value            = ! empty( $this->form[ $tab ][ $fields_group ][ $options['name'] ] ) ? $this->form[ $tab ][ $fields_group ][ $options['name'] ] : $options['default'];
					$options['id']    = esc_attr( 'setting-' . $tab . '-' . $fields_group . '-' . $options['name'] );
					$class            = 'setting-' . $fields_group . '-' . $options['name'];
					$options['class'] = ! empty( $options['class'] ) ? $options['class'] . ' ' . $class : $class;
					$options['class'] = esc_attr( $options['class'] );
				} else {
					$attr_name        = 'gmw_form[' . $tab . '][' . $options['name'] . ']';
					$value            = ! empty( $this->form[ $tab ][ $options['name'] ] ) ? $this->form[ $tab ][ $options['name'] ] : $options['default'];
					$options['id']    = esc_attr( 'setting-' . $tab . '-' . $options['name'] );
					$class            = 'setting-' . $options['name'];
					$options['class'] = ! empty( $options['class'] ) ? $options['class'] . ' ' . $class : $class;
					$options['class'] = esc_attr( $options['class'] );
				}

				// $attr_name = esc_attr( 'gmw_form[' . $tab . '][' . $options['name'] . ']' );
				$function = ! empty( $options['function'] ) ? $options['function'] : $options['name'];

				// $value = ! empty( $this->form[ $tab ][ $options['name'] ] ) ? $this->form[ $tab ][ $options['name'] ] : $options['default'];
				do_action( 'gmw_' . $this->form['slug'] . '_form_settings_' . $function, $value, $attr_name, $this->form, $this->form_fields, $tab, $options );
				do_action( 'gmw_' . $this->form['addon'] . '_addon_form_settings_' . $function, $value, $attr_name, $this->form, $this->form_fields, $tab, $options );
				do_action( 'gmw_' . $this->form['component'] . '_component_form_settings_' . $function, $value, $attr_name, $this->form, $this->form_fields, $tab, $options );
				do_action( 'gmw_form_settings_' . $function, $value, $attr_name, $this->form, $this->form_fields, $tab, $options );

				break;

			case 'checkbox':
			case 'multicheckbox':
			case 'multicheckboxvalues':
			case 'textarea':
			case 'radio':
			case 'select':
			case 'multiselect':
			case 'multiselect_name_value':
			case 'password':
			case 'hidden':
			case 'number':
			case '':
			case 'input':
			case 'text':
				if ( ! empty( $fields_group ) && ! empty( $options['sub_option'] ) ) {

					$name_attr        = 'gmw_form[' . $tab . '][' . $fields_group . ']';
					$value            = ! empty( $this->form[ $tab ][ $fields_group ][ $options['name'] ] ) ? $this->form[ $tab ][ $fields_group ][ $options['name'] ] : $options['default'];
					$options['id']    = esc_attr( 'setting-' . $tab . '-' . $fields_group . '-' . $options['name'] );
					$class            = 'setting-' . $fields_group . '-' . $options['name'];
					$options['class'] = ! empty( $options['class'] ) ? $options['class'] . ' ' . $class : $class;
				} else {

					$name_attr        = 'gmw_form[' . $tab . ']';
					$value            = ! empty( $this->form[ $tab ][ $options['name'] ] ) ? $this->form[ $tab ][ $options['name'] ] : $options['default'];
					$options['id']    = esc_attr( 'setting-' . $tab . '-' . $options['name'] );
					$class            = 'setting-' . $options['name'];
					$options['class'] = ! empty( $options['class'] ) ? $options['class'] . ' ' . $class : $class;
				}

				echo gmw_get_admin_settings_field( $options, esc_attr( $name_attr ), $value ); // WPCS: XSS ok.

				break;
		}
	}

	/**
	 * Output edit form page.
	 *
	 * @access public
	 */
	public function output() {

		// get form fields.
		$this->init_form_settings();
		?>
		<style>
			#adminmenumain,
			#adminmenuback {
				width: 36px;
				overflow: hidden;
			}
		</style>
		<?php
		$class = 'gmw-edit-form';

		if ( class_exists( 'GMW_Premium_Settings_Addon' ) ) {
			$class .= ' gmw-premium-settings-enabled';
		}
		?>
		<form method="post" action="" id="gmw-form-editor" class="<?php echo $class; // WPCS: XSS ok. ?>" data-ajax_enabled="<?php echo esc_attr( $this->ajax_enabled ); ?>" data-nonce="<?php echo wp_create_nonce( 'gmw_edit_form_nonce' ); // WPCS: XSS ok. ?>">

			<?php gmw_admin_helpful_buttons(); ?>

			<div class="gmw-edit-form-page-top-wrapper gmw-admin-page">

				<img id="site-logo-header" style="width: 170px;height: 50px;" alt="" src="https://geomywp.com/wp-content/uploads/assets/svg/gmw-logo-new.png" class="ct-image">

				<div class="gmw-edit-form-page-top-inner">

				<span class="edit-form-title">
					<span class="dashicons dashicons-edit-large"></span>
					<?php echo esc_html__( 'Editing Form', 'geo-my-wp' ) . ' ' . absint( $this->form['ID'] ) . ' <em style="font-size: 13px;font-weight: 100">( ' . esc_attr( $this->form['name'] ) . ' )</em> '; ?>
				</span>

					<div class="action-area">

						<a href="#" id="gmw-form-editor-submit" class="gmw-settings-action-button button-primary">
							<span class="saving-form">
								<i class="gmw-icon gmw-icon-cog animate-spin"></i>
								<?php esc_html_e( 'Saving...', 'geo-my-wp' ); ?>
							</span>
							<span class="form-saved">
								<i class="gmw-icon gmw-icon-ok"></i>
								<?php esc_html_e( 'Changes Saved', 'geo-my-wp' ); ?>
							</span>
							<span class="form-saved-failed">
								<i class="gmw-icon gmw-icon-cancel"></i>
								<?php esc_html_e( 'Saving form failed', 'geo-my-wp' ); ?>
							</span>
							<span><?php esc_html_e( 'Save Changes', 'geo-my-wp' ); ?></span>
						</a>

						<a
							id="form-editor-close-button"
							class="form-editor-close gmw-settings-action-button button-primary"
							href="admin.php?page=gmw-forms"><i class="gmw-icon gmw-icon-cancel"
							title="Close form"></i>
						</a>
					</div>
				</div>
			</div>

			<div id="gmw-edit-form-page" class="wrap gmw-admin-page-content gmw-admin-page gmw-admin-page-wrapper gmw-admin-page-no-sidebar">

				<nav class="gmw-admin-page-navigation gmw-tabs-wrapper gmw-edit-form-page-nav-tabs">

					<?php
					foreach ( $this->form_settings_groups as $group ) {

						if ( isset( $group['id'] ) ) {
							$group['slug'] = $group['id'];
						}

						// Verify that there are settings for this tab.
						if ( ( empty( $this->form_fields[ $group['slug'] ] ) || 'hidden' === $group['type'] ) && 'function' !== $group['type'] && 'premium' !== $group['type'] ) {
							continue;
						}

						if ( 'premium' === $group['type'] ) {
							?>
							<a
								href="#" 
								class="gmw-premium-feature" 
								data-feature="<?php echo esc_attr( $group['extension_slug'] ); ?>"
								data-name="<?php echo esc_attr( $group['extension_name'] ); ?>"
								data-url="<?php echo esc_attr( $group['extension_url'] ); ?>"
								data-content="<?php echo esc_attr( $group['extension_content'] ); ?>"
								>
								<span><?php echo esc_attr( $group['label'] ); ?></span>
							</a>

						<?php } else { ?>

							<a
								href="#settings-<?php echo esc_attr( sanitize_title( $group['slug'] ) ); ?>"
								id="<?php echo esc_attr( $group['slug'] ); ?>"
								class="gmw-nav-tab gmw-nav-trigger<?php echo ! empty( $group['tab_class'] ) ? esc_attr( ' ' . $group['tab_class'] ) : ''; ?>"
								data-name="<?php echo esc_attr( sanitize_title( $group['slug'] ) ); ?>"
								>
								<span><?php echo esc_attr( $group['label'] ); ?></span>
							</a>

						<?php } ?>  

					<?php } ?>
				</nav>

				<div class="gmw-admin-page-panels-wrapper">

					<h1 style="display:none"></h1>

					<?php wp_nonce_field( 'gmw_edit_form_nonce', 'gmw_edit_form_nonce' ); ?> 

					<input type="hidden" name="gmw_action" value="update_admin_form" />
					<input type="hidden" name="gmw_form[ID]" value="<?php echo absint( $this->form['ID'] ); ?>" />	
					<input type="hidden" name="gmw_form[slug]" value="<?php echo esc_attr( sanitize_text_field( $this->form['slug'] ) ); ?>" />
					<input type="hidden" name="gmw_form[addon]" value="<?php echo esc_attr( sanitize_text_field( $this->form['addon'] ) ); ?>" />
					<input type="hidden" name="gmw_form[component]" value="<?php echo esc_attr( sanitize_text_field( $this->form['component'] ) ); ?>" />

					<?php
					$tab_options  = array();
					$allowed_html = apply_filters(
						'gmw_form_editor_feature_desc_allowed_html',
						array(
							'a'    => array(
								'href'   => array(),
								'title'  => array(),
								'target' => array(),
							),
							'span' => array(
								'style' => array(),
								'class' => array(),
								'id'    => array(),
							),
							'code' => array(),
							'br'   => array(),
							'em'   => array(),
							'p'    => array(),
						)
					);

					// form fields.
					foreach ( $this->form_fields as $tab => $section ) {

						if ( ( ! array_filter( $section ) || ! is_array( $section ) ) && 'function' !== $this->form_settings_groups[ $tab ]['type'] && 'premium' !== $this->form_settings_groups[ $tab ]['type'] ) {
							continue;
						}

						// sort fields by priority.
						uasort( $section, 'gmw_sort_by_priority' );

						$tab         = esc_attr( $tab );
						$this_class  = esc_attr( $this->form['component'] . ' ' . $this->form['slug'] . ' ' . $this->form['addon'] );
						$this_class .= ! empty( $this->form_settings_groups[ $tab ]['panel_class'] ) ? ' ' . esc_attr( $this->form_settings_groups[ $tab ]['panel_class'] ) : '';
						?>
						<div id="settings-<?php echo $tab; // WPCS: XSS ok. ?>" class="gmw-settings-form gmw-tab-panel <?php echo $tab; // WPCS: XSS ok. ?> <?php echo $this_class; // WPCS: XSS ok. ?>">

							<?php
							// Tab notice.
							if ( ! empty( $this->form_settings_groups[ $tab ]['notice'] ) ) {

								echo '<div class="gmw-admin-notice-box" style="grid-column: span 2;">';
								// echo '<span><i class="gmw-icon-info-circled"></i>';
								echo '<span>';
								echo wp_kses( $this->form_settings_groups[ $tab ]['notice'], $allowed_html );
								echo '</span></div>';
							}

							do_action( 'form_editor_tab_start', $tab, $section, $this->form['ID'], $this->form );
							do_action( 'form_editor_' . $tab . '_tab_start', $tab, $section, $this->form['ID'], $this->form );

							foreach ( $section as $sec => $option ) {

								$grid_column_css  = ! empty( $option['grid_column'] ) ? 'gmw-settings-panel-grid-column-' . esc_attr( $option['grid_column'] ) : '';
								$feature_disbaled = '';

								if ( ! empty( $option['feature_disabled'] ) ) {

									$feature_disbaled = 'feature-disabled';

									if ( ! empty( $option['disabled_message'] ) ) {
										$option['desc'] .= '<span class="gmw-admin-notice-box gmw-admin-notice-error">' . $option['disabled_message'] . '</span>';
									}
								}

								$id_attr = ! empty( $option['name'] ) ? esc_attr( $tab . '-' . $option['name'] . '-tr' ) : '';
								$desc    = ! empty( $option['desc'] ) ? wp_kses( $option['desc'], $allowed_html ) : '';
								?>
								<fieldset 
									id="<?php echo $id_attr; // WPCS: XSS ok. ?>"
									class="gmw-settings-panel <?php echo $grid_column_css; // WPCS: XSS ok. ?> gmw-item-sort gmw-form-field-wrapper <?php echo ! empty( $option['wrap_class'] ) ? esc_attr( $option['wrap_class'] ) : ''; ?> <?php echo esc_attr( $tab ); ?> <?php echo ! empty( $option['type'] ) ? esc_attr( $option['type'] ) : ''; ?>">

									<legend class="gmw-settings-panel-title">

										<?php if ( isset( $option['label'] ) ) { ?>

											<?php
											$tab_options[] = array(
												'tab'   => $tab,
												'id'    => $id_attr,
												'label' => $option['label'],
												'tab_label' => ! empty( $this->form_settings_groups[ $tab ]['label'] ) ? $this->form_settings_groups[ $tab ]['label'] : '',
											);
											?>

											<i class="gmw-icon-cog"></i>
											<label for="setting-<?php echo esc_attr( $option['name'] ); ?>">
												<?php echo esc_html( $option['label'] ); ?>	
											</label>

											<?php
											/*
											if ( '' !== $desc ) { ?>
												<i class="gmw-settings-desc-tooltip dashicons dashicons-editor-help gmw-tooltip" aria-label="<?php echo $desc // WPCS: XSS ok.; ?>"></i>
											<?php } */
											?>
										<?php } ?>

									</legend>

									<div class="gmw-settings-panel-content gmw-form-feature-settings <?php echo ! empty( $option['type'] ) ? esc_attr( $option['type'] ) : ''; ?>">

										<?php if ( '' !== $desc ) { ?>
											<div class="gmw-settings-panel-description"><?php echo $desc; // WPCS: XSS ok. ?></div>
										<?php } ?>

										<?php if ( 'fields_group' === $option['type'] && array_filter( $option['fields'] ) ) { ?>

											<?php $fields_group = ! empty( $option['name'] ) ? esc_attr( $option['name'] ) : ''; ?>

											<?php uasort( $option['fields'], 'gmw_sort_by_priority' ); ?>

											<div class="gmw-settings-multiple-fields-wrapper">

												<?php foreach ( $option['fields'] as $field_options ) { ?>

													<?php $grid_column_css = ! empty( $field_options['grid_column'] ) ? 'gmw-settings-panel-grid-column-' . esc_attr( $field_options['grid_column'] ) : ''; ?>

													<div class="<?php echo $id_attr; // WPCS: XSS ok. ?> gmw-settings-panel-field gmw-form-feature-settings single-option option-<?php echo esc_attr( $field_options['name'] ); ?> <?php echo $feature_disbaled; // WPCS: XSS ok. ?> <?php echo ! empty( $field_options['type'] ) ? esc_attr( $field_options['type'] ) : ''; ?> <?php echo ! empty( $field_options['wrap_class'] ) ? esc_attr( $field_options['wrap_class'] ) : ''; ?>">

														<div class="gmw-settings-panel-header">
															<label class="gmw-settings-label"><?php echo ( ! empty( $field_options['label'] ) ) ? esc_html( $field_options['label'] ) : ''; ?></label>
														</div>

														<div class="gmw-settings-panel-input-container option-type-<?php echo esc_attr( $field_options['type'] ); ?>">
															<?php $this->get_form_field( $field_options, $tab, $fields_group ); ?>
														</div>				

														<?php if ( ! empty( $field_options['desc'] ) ) { ?>
															<div class="gmw-settings-panel-description"><?php echo wp_kses( $field_options['desc'], $allowed_html ); ?></div>
														<?php } ?>
													</div>

													<?php if ( ! empty( $field_options['premium_message']['message'] ) && ! empty( $field_options['premium_message']['class'] ) && ! class_exists( $field_options['premium_message']['class'] ) ) { ?>
														<div class="gmw-premium-options-message">
															<?php echo $this->get_premium_message( $field_options['premium_message']['message'] ); // WPCS: XSS ok. ?>
														</div>
													<?php } ?>

												<?php } ?>

												<?php if ( ! empty( $option['premium_message']['message'] ) && ! empty( $option['premium_message']['class'] ) && ! class_exists( $option['premium_message']['class'] ) ) { ?>
													<div class="gmw-premium-options-message">
														<?php echo $this->get_premium_message( $option['premium_message']['message'] ); // WPCS: XSS ok. ?>
													</div>
												<?php } ?>
											</div>

										<?php } else { ?>

											<div class="gmw-settings-panel-field gmw-form-feature-settings <?php echo $feature_disbaled; ?> <?php echo ! empty( $option['type'] ) ? esc_attr( $option['type'] ) : ''; ?>">
												<div class="gmw-settings-panel-input-container">
													<?php $this->get_form_field( $option, $tab ); ?>
												</div>
											</div>

											<?php if ( ! empty( $option['premium_message']['message'] ) && ! empty( $option['premium_message']['class'] ) && ! class_exists( $option['premium_message']['class'] ) ) { ?>
												<div class="gmw-premium-options-message">
													<?php echo $this->get_premium_message( $option['premium_message']['message'] ); // WPCS: XSS ok. ?>
												</div>
											<?php } ?>

										<?php } ?>
									</div>
								</fieldset>

							<?php } ?> 
							<?php do_action( 'form_editor_tab_end', $tab, $section, $this->form['ID'], $this->form ); ?>
							<?php do_action( 'form_editor_' . $tab . '_tab_end', $tab, $section, $this->form['ID'], $this->form ); ?>

						</div>
					<?php } ?>
					<?php
					/*
					<div id="gmw-jump-to-option-wrapper">

						<select id="jump-top-option" class="gmw-smartbox-no">

							<option value=""><?php echo _e( 'Jump to option', 'geo-my-wp' ); ?></option>

							<optgroup label="Page Load Results">
							<?php

							$current_tab = 'Page Load Results';

							foreach( $tab_options as $tab_option => $tab_args ) { ?>

								<?php if ( $current_tab !== $tab_args['tab_label'] ) { ?>

									</optgroup><optgroup label="<?php echo $tab_args['tab_label']; ?>">

									<?php $current_tab = $tab_args['tab_label']; ?>

								<?php } ?>

								<option value="<?php echo $tab_args['tab']; ?>|<?php echo $tab_args['id']; ?>"><?php echo $tab_args['label']; ?></option>

							<?php } ?>
						</select>
					</div>
					*/
					?>
				</div>
			</div> 
		</form>

		<script>
		jQuery( document ).ready( function( $ ) {
			jQuery( 'body' ).addClass( 'geo-my-wp_page_gmw-form-editor' );
			jQuery( 'html' ).addClass( 'folded' ).find( '#adminmenumain, #adminmenuback' ).css( 'overflow', 'initial' );
		});
		</script>         
		<?php
	}

	/**
	 * Validate single field.
	 *
	 * @param  [type] $value  [description].
	 *
	 * @param  [type] $option [description].
	 *
	 * @param  [type] $form   [description].
	 *
	 * @return [type]         [description].
	 */
	public function validate_field( $value, $option, $form ) {

		$valid_value = '';

		// Generate fieldf options dynamically for fields with options that are generated via AJAX.
		if ( ! empty( $option['attributes']['data-gmw_ajax_load_options'] ) ) {

			$args = array();

			foreach ( $option['attributes'] as $name => $attribute ) {
				$args[ str_replace( 'data-', '', $name ) ] = $attribute;
			}

			$option['options'] = GMW_Form_Settings_Helper::get_field_options( $args );
		}

		switch ( $option['type'] ) {

			// custom functions validation.
			case 'function':
				// save custom settings value as is. without validation.
				if ( ! empty( $value ) ) {
					$valid_value = $value;
				} else {
					$value       = '';
					$valid_value = '';
				}

				// use this filter to validate custom settigns.
				$function = ! empty( $option['function'] ) ? $option['function'] : $option['name'];

				$valid_value = apply_filters( 'gmw_' . $form['slug'] . '_validate_form_settings_' . $function, $valid_value, $form );
				$valid_value = apply_filters( 'gmw_' . $this->form['addon'] . '_addon_validate_form_settings_' . $function, $valid_value, $form );
				$valid_value = apply_filters( 'gmw_validate_form_settings_' . $function, $valid_value, $form );

				break;

			// checkbox.
			case 'checkbox':
				$valid_value = ! empty( $value ) ? 1 : '';
				break;

			// multi checbox.
			case 'multicheckbox':
				if ( empty( $value ) || ! is_array( $value ) ) {
					$valid_value = is_array( $option['default'] ) ? $option['default'] : array();
				} else {
					foreach ( $option['options'] as $v => $l ) {
						if ( in_array( $v, $value ) ) {
							$valid_value[] = $v;
						}
					}
				}

				break;

			case 'multicheckboxvalues':
				if ( empty( $value ) || ! is_array( $value ) ) {
					$valid_value = is_array( $option['default'] ) ? $option['default'] : array();
				} else {

					$valid_value = array();

					foreach ( $option['options'] as $key_val => $name ) {

						if ( in_array( $key_val, $value ) ) {

							$valid_value[] = $key_val;
						}
					}
				}
				break;

			case 'multiselect':
			case 'multiselect_name_value':
				if ( empty( $value ) || ! is_array( $value ) ) {

					$valid_value = is_array( $option['default'] ) ? $option['default'] : array();

				} else {

					$valid_value = array();

					foreach ( $option['options'] as $key_val => $name ) {

						if ( in_array( $key_val, $value ) ) {

							if ( 'multiselect_name_value' === $option['type'] ) {
								$valid_value[ $name ] = $key_val;
							} else {
								$valid_value[] = $key_val;
							}
						}
					}
				}

				break;

			case 'select':
			case 'radio':
				if ( ! empty( $value ) && in_array( $value, array_keys( $option['options'] ) ) ) {
					$valid_value = $value;
				} else {
					$valid_value = ! empty( $option['default'] ) ? $option['default'] : '';
				}
				break;

			case 'textarea':
				if ( ! empty( $value ) ) {
					$valid_value = sanitize_textarea_field( $value );
				} else {
					$valid_value = ! empty( $option['default'] ) ? sanitize_textarea_field( $option['default'] ) : '';
				}
				break;

			case 'number':
				if ( ! empty( $value ) ) {
					$num_value = $value;
				} else {
					$num_value = isset( $option['default'] ) ? $option['default'] : '';
				}
				$valid_value = preg_replace( '/[^0-9]/', '', $num_value );
				break;

			case "''":
			case 'text':
			case 'password':
			case 'search':
				if ( ! empty( $value ) ) {
					$this_value = $value;
				} else {
					$this_value = ! empty( $option['default'] ) ? $option['default'] : '';
				}
				$valid_value = sanitize_text_field( $this_value );
				break;

			case 'hidden':
				if ( ! empty( $option['force_default'] ) ) {

					$this_value = ! empty( $option['default'] ) ? $option['default'] : '';

				} elseif ( ! empty( $value ) ) {

					$this_value = $value;

				} else {
					$this_value = ! empty( $option['default'] ) ? $option['default'] : '';
				}

				$valid_value = sanitize_text_field( $this_value );

				break;
		}

		return $valid_value;
	}

	/**
	 * Validate form input fields
	 *
	 * @param  array $values Form values after form submission.
	 *
	 * @return array validated/sanitized values
	 */
	public function validate( $values ) {

		// get the current form being updated.
		$this->form = GMW_Forms_Helper::get_form( $values['ID'] );

		$valid_input = array();

		// get basic form values.
		$valid_input['ID']    = absint( $values['ID'] );
		$valid_input['title'] = sanitize_text_field( $values['title'] );
		$valid_input['slug']  = sanitize_text_field( $values['slug'] );

		// get form fields.
		$this->init_form_settings();

		// loop through and validate fields.
		foreach ( $this->form_fields as $section_name => $section ) {

			foreach ( $section as $sec => $option ) {

				if ( is_array( $section ) && ! array_filter( $section ) ) {
					continue;
				}

				$option['type'] = ! empty( $option['type'] ) ? $option['type'] : 'text';

				if ( 'fields_group' === $option['type'] && array_filter( $option['fields'] ) ) {

					foreach ( $option['fields'] as $group_option ) {

						if ( ! empty( $group_option['sub_option'] ) ) {

							if ( empty( $values[ $section_name ][ $option['name'] ][ $group_option['name'] ] ) ) {
								$values[ $section_name ][ $option['name'] ][ $group_option['name'] ] = '';
							}

							$valid_input[ $section_name ][ $option['name'] ][ $group_option['name'] ] = $this->validate_field( $values[ $section_name ][ $option['name'] ][ $group_option['name'] ], $group_option, $this->form );

						} else {

							if ( empty( $values[ $section_name ][ $group_option['name'] ] ) ) {
								$values[ $section_name ][ $group_option['name'] ] = '';
							}

							$valid_input[ $section_name ][ $group_option['name'] ] = $this->validate_field( $values[ $section_name ][ $group_option['name'] ], $group_option, $this->form );
						}
					}
				} else {

					if ( empty( $values[ $section_name ][ $option['name'] ] ) ) {
						$values[ $section_name ][ $option['name'] ] = '';
					}

					$valid_input[ $section_name ][ $option['name'] ] = $this->validate_field( $values[ $section_name ][ $option['name'] ], $option, $this->form );
				}
			}
		}

		$valid_input = apply_filters( 'gmw_validated_form_settings', $valid_input, $this->form );

		return $valid_input;
	}

	/**
	 * Update form via AJAX
	 *
	 * Run the form values through validations and update the form in database
	 *
	 * @return void
	 */
	public function ajax_update_form() {

		// verify nonce.
		check_ajax_referer( 'gmw_edit_form_nonce', 'security', true );

		$form_values = array();

		// get the submitted values.
		if ( ! empty( $_POST['form_values'] ) ) {
			parse_str( $_POST['form_values'], $form_values ); // WPCS: CSRF ok.
		}

		$form = $form_values['gmw_form'];

		// validate the values.
		$valid_input = self::validate( $form );
		$form_id     = $valid_input['ID'];
		$title       = $valid_input['title'];

		unset( $valid_input['ID'] );
		unset( $valid_input['title'] );
		unset( $valid_input['slug'] );

		global $wpdb;

		$form_udpated = $wpdb->update(

			$wpdb->prefix . 'gmw_forms',
			array(
				'data'  => serialize( $valid_input ),
				'title' => $title,
			),
			array( 'ID' => $form_id ),
			array(
				'%s',
				'%s',
			),
			array( '%d' )
		); // DB call ok, cache ok.

		// update form in database.
		if ( false === $form_udpated ) {

			wp_die(
				esc_html__( 'Failed saving data in database.', 'geo-my-wp' ),
				esc_html__( 'Error', 'geo-my-wp' ),
				array( 'response' => 403 )
			);

		} else {

			// delete form from cache. We are updating it with new data.
			wp_cache_delete( 'all_forms', 'gmw_forms' );
			wp_cache_delete( $form['ID'], 'gmw_forms' );

			wp_send_json( $valid_input );
		}
	}

	/**
	 * Update form via page load
	 *
	 * Run the form values through validations and update the form in database
	 *
	 * @return void
	 */
	public function update_form() {

		// run a quick security check.
		if ( empty( $_POST['gmw_form'] ) || empty( $_POST['gmw_edit_form_nonce'] ) || ! check_admin_referer( 'gmw_edit_form_nonce', 'gmw_edit_form_nonce' ) ) {
			wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
		}

		// validate the values.
		$valid_input = self::validate( $_POST['gmw_form'] ); // WPCS: CSRF ok, sanitization ok.

		global $wpdb;

		$form_updated = $wpdb->update(

			$wpdb->prefix . 'gmw_forms',
			array(
				'data'  => maybe_serialize( $valid_input ),
				'title' => $valid_input['title'],
			),
			array( 'ID' => $valid_input['ID'] ),
			array(
				'%s',
				'%s',
			),
			array( '%d' )
		); // DB call ok, cache ok.

		// update form in database.
		if ( false === $form_udpated ) {

			// update forms in cache.
			GMW_Forms_Helper::update_forms_cache();

			wp_safe_redirect(
				add_query_arg(
					array(
						'gmw_notice'        => 'form_not_update',
						'gmw_notice_status' => 'error',
					)
				)
			);

		} else {

			// update forms in cache.
			GMW_Forms_Helper::update_forms_cache();

			wp_safe_redirect(
				add_query_arg(
					array(
						'gmw_notice'        => 'form_updated',
						'gmw_notice_status' => 'updated',
					)
				)
			);
		};

		exit;
	}
}
