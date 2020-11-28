<?php
/**
 * GEO my WP - GMW_Admin class.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Admin class.
 */
class GMW_Admin {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// get options.
		$this->settings = get_option( 'gmw_options' );

		// admin notice to import location to the new database table.
		if ( get_option( 'gmw_old_locations_tables_exist' ) !== false && get_option( 'gmw_old_locations_tables_updated' ) === false ) {
			add_action( 'admin_notices', array( $this, 'update_database_notice' ) );
		}

		// admin notice to import location to the new database table.
		if ( get_option( 'gmw_folders_names_changed_notice_viewed' ) === false ) {
			add_action( 'admin_init', array( $this, 'folders_names_notice_dismiss' ) );
			add_action( 'admin_notices', array( $this, 'deprecated_folder_names_notice' ) );
		}

		// do some actions.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_init', array( $this, 'init_addons' ) );

		// "GMW Form" button in edit post page.
		if ( self::add_form_button_pages() ) {
			add_action( 'media_buttons', array( $this, 'add_form_button' ), 25 );
			add_action( 'admin_footer', array( $this, 'form_insert_popup' ) );
		}

		do_action( 'gmw_pre_admin_include_pages' );

		// admin functions.
		include_once 'gmw-admin-functions.php';
		include_once 'class-gmw-tracking.php';
		include_once 'updater/class-gmw-license.php';
		include_once 'class-gmw-form-settings-helper.php';

		// admin pages.
		include_once 'pages/class-gmw-extensions.php';
		include_once 'pages/class-gmw-settings.php';
		include_once 'pages/class-gmw-forms-page.php';
		include_once 'pages/class-gmw-form-editor.php';
		include_once 'pages/tools/class-gmw-tools.php';
		include_once 'pages/import-export/class-gmw-import-export-page.php';

		// set pages.
		$this->addons_page   = new GMW_Extensions();
		$this->settings_page = new GMW_Settings();
		$this->forms_page    = new GMW_Forms_Page();

		if ( isset( $_GET['page'] ) && 'gmw-forms' === $_GET['page'] && isset( $_GET['gmw_action'] ) && 'edit_form' === $_GET['gmw_action'] && ! empty( $_GET['prefix'] ) && class_exists( 'GMW_' . $_GET['prefix'] . '_Form_Editor' ) ) { // WPCS: CSRF ok.
			$class_name = 'GMW_' . sanitize_text_field( wp_unslash( $_GET['prefix'] ) ) . '_Form_Editor'; // WPCS: CSRF ok.
		} else {
			$class_name = 'GMW_Form_Editor';
		}

		$this->edit_form_page     = new $class_name();
		$this->tools_page         = new GMW_Tools();
		$this->import_export_page = new GMW_Import_Export_Page();
		// $this->shortcodes_page    = new GMW_Shortcodes_page();
		add_filter( 'plugin_action_links_' . GMW_BASENAME, array( $this, 'gmw_action_links' ), 10, 2 );

		/**
		 * Registers all data exporters.
		 *
		 * @param array $exporters
		 *
		 * @return mixed
		 */
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_user_data_exporters' ) );

	}

	/**
	 * Hook into WordPress Personal Data Exported.
	 *
	 * @author Eyal Fitoussi
	 *
	 * @since 3.6.3
	 *
	 * @param  [type] $exporters [description].
	 *
	 * @return [type]            [description]
	 */
	public function register_user_data_exporters( $exporters ) {

		$exporters['geo_my_wp'] = array(
			'exporter_friendly_name' => __( 'GEO my WP Location Data', 'text-domain' ),
			'callback'               => array( $this, 'export_user_data' ),
		);
		return $exporters;
	}

	/**
	 * Export User's Locations data.
	 *
	 * @author Eyal Fitoussi
	 *
	 * @since 3.6.3
	 *
	 * @param  string  $email_address email address.
	 * @param  integer $page          page number.
	 *
	 * @return [type]                 [description]
	 */
	public function export_user_data( $email_address, $page = 1 ) {

		// Get user's data.
		$user         = get_user_by( 'email', $email_address );
		$export_items = array(
			'data' => array(),
			'done' => true,
		);

		// Abort if user not found.
		if ( empty( $user ) || empty( $user->ID ) ) {
			return $export_items;
		}

		global $wpdb, $blog_id;

		$table        = $wpdb->base_prefix . 'gmw_locations';
		$number       = 200;
		$page         = (int) $page;
		$offset       = ( $page - 1 ) * $number;
		$group_labels = array(
			'post'     => __( 'Posts Locations', 'geo-my-wp' ),
			'user'     => __( 'User Locations', 'geo-my-wp' ),
			'bp_group' => __( 'BuddyPress Groups Locations', 'geo-my-wp' ),
		);

		// get user's locations.
		$locations = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT *
	            FROM   $table
	            WHERE  blog_id = %d
	            AND    user_id = %s
	            ORDER BY object_type ASC, ID ASC
	            LIMIT {$offset}, {$number}",
				$blog_id,
				$user->ID
			),
			OBJECT
		); // WPCS: db call ok, cache ok, unprepared SQL ok.

		// Abort if no location were found.
		if ( empty( $locations ) ) {
			return $export_items;
		}

		// loop through locations and collect data.
		foreach ( $locations as $location ) {

			$item_id     = "{$location->object_type}_location_{$location->ID}";
			$group_id    = "{$location->object_type}_location";
			$group_label = ! empty( $group_labels[ $location->object_type ] ) ? $group_labels[ $location->object_type ] : __( 'GEO my WP Locations', 'geo-my-wp' );
			$data        = array();

			foreach ( $location as $field => $value ) {
				$data[] = array(
					'name'  => $field,
					'value' => $value,
				);
			}

			// Look for location meta.
			$location_meta = gmw_get_location_meta( $location->ID );

			if ( ! empty( $location_meta ) ) {

				foreach ( $location_meta as $meta_field => $meta_value ) {

					$data[] = array(
						'name'  => $meta_field,
						'value' => $meta_value,
					);
				}
			}

			$export_items[] = array(
				'group_id'    => $group_id,
				'group_label' => $group_label,
				'item_id'     => $item_id,
				'data'        => $data,
			);
		}

		$done = count( $locations ) < $number;

		return array(
			'data' => $export_items,
			'done' => $done,
		);
	}

	/**
	 * Admin notice.
	 */
	public function update_database_notice() {
		?>
		<div class="error">
			<p><?php echo sprintf( __( 'GEO my WP needs to import existing locations into its new database table. <a href="%s" class="button-primary"> Import locations</a>' ), admin_url( 'admin.php?page=gmw-import-export&tab=gmw_v_3' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Admin notice.
	 */
	public function folders_names_notice_dismiss() {

		if ( ! empty( $_GET['action'] ) && 'gmw_folders_names_dismiss' === $_GET['action'] ) { // WPCS: CSRF ok.

			update_option( 'gmw_folders_names_changed_notice_viewed', 1 );

			wp_safe_redirect( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}
	}

	/**
	 * Admin notice.
	 */
	public function deprecated_folder_names_notice() {

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'gmw-extensions'; // WPCS: CSRF ok.
		?>
		<div class="notice notice-warning">
			<p><?php echo sprintf( __( '<h2>Important GEO my WP notice!</h2><p>The folders names for the custom template files have changed since GEO my WP version 3.0. If you have custom template files placed in the theme or child theme folder, you need to rename the folders to be able to use your custom template files.</p><p>See <a href="%1$s" target="_blank">this post</a> to learn about the new folders names.</p><p><a href="%2$s" class="button-secondary">Dismiss</a></p>' ), 'https://geomywp.com/geo-my-wp-3-0-upgrade-process/#folders-names', admin_url( 'admin.php?page=' . $page . '&action=gmw_folders_names_dismiss' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Admin action links.
	 *
	 * @param  [type] $links [description].
	 *
	 * @param  [type] $file  [description].
	 *
	 * @return [type]        [description]
	 */
	public function gmw_action_links( $links, $file ) {

		$links['settings']   = '<a href="' . admin_url( 'admin.php?page=gmw-settings' ) . '">' . __( 'Settings', 'geo-my-wp' ) . '</a>';
		$links['extensions'] = '<a href="' . admin_url( 'admin.php?page=gmw-extensions' ) . '">' . __( 'Extensions', 'geo-my-wp' ) . '</a>';

		return $links;
	}

	/**
	 * Admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {

		// GEO my WP menu items.
		add_menu_page( 'GEO my WP', 'GEO my WP', 'manage_options', 'gmw-extensions', array( $this->addons_page, 'output' ), 'dashicons-location-alt', 66 );

		// sub menu pages.
		$menu_items = array();

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Extensions', 'geo-my-wp' ),
			'menu_title'        => __( 'Extensions', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-extensions',
			'callback_function' => array( $this->addons_page, 'output' ),
			'priority'          => 1,
		);

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Settings', 'geo-my-wp' ),
			'menu_title'        => __( 'Settings', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-settings',
			'callback_function' => array( $this->settings_page, 'output' ),
			'priority'          => 5,
		);

		$forms_output = ( ! empty( $_GET['gmw_action'] ) && 'edit_form' === $_GET['gmw_action'] ) ? $this->edit_form_page : $this->forms_page; // WPCS: CSRF ok.

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Forms', 'geo-my-wp' ),
			'menu_title'        => __( 'Forms', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-forms',
			'callback_function' => array( $forms_output, 'output' ),
			'priority'          => 8,
		);

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Import / Export', 'geo-my-wp' ),
			'menu_title'        => __( 'Import / Export', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-import-export',
			'callback_function' => array( $this->import_export_page, 'output' ),
			'priority'          => 10,
		);

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Tools', 'geo-my-wp' ),
			'menu_title'        => __( 'Tools', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-tools',
			'callback_function' => array( $this->tools_page, 'output' ),
			'priority'          => 15,
		);

		/**
		 *
		 * Hook your add-on's menu item and page
		 *
		 * To do so you need to append an array of menu items as the example below:
		 *
		 * $menu_items[] = array(
		 *    'parent_slug'       => 'gmw-extensions', // the main menu to add your sub-menu item to. Should always be gmw-extensions
		 *    'page_title'        => __( 'GEO my WP Tools', 'geo-my-wp' ),
		 *    'menu_title'        => __( 'Tools', 'geo-my-wp' ),
		 *    'capability'        => 'manage_options',
		 *    'menu_slug'         => 'gmw-tools',
		 *    'callback_function' => array( 'tools_page', 'output' ), // this can be either a string when using a function or array of class and the function to execute.
		 *    'priority'          => 25
		 * );
		 */
		$menu_items = apply_filters( 'gmw_admin_menu_items', $menu_items );

		// order menu items by priority.
		usort( $menu_items, 'gmw_sort_by_priority' );

		// gmw admin pages.
		$gmw_pages = array();

		// loop and create menu items and pages.
		foreach ( $menu_items as $key => $item ) {

			add_submenu_page(
				! empty( $item['parent_slug'] ) ? $item['parent_slug'] : 'gmw-extensions',
				! empty( $item['page_title'] ) ? $item['page_title'] : '',
				! empty( $item['menu_title'] ) ? $item['menu_title'] : '',
				! empty( $item['capability'] ) ? $item['capability'] : 'manage_options',
				! empty( $item['menu_slug'] ) ? $item['menu_slug'] : '',
				$item['callback_function']
			);

			$gmw_pages[] = $item['menu_slug'];
		}

		// apply credit and enqueue scripts and styles in GEO my WP admin pages only.
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $gmw_pages ) ) { // WPCS: CSRF ok.

			add_filter( 'admin_footer_text', array( $this, 'gmw_credit_footer' ), 10 );
		}
	}

	/**
	 * Initiate GEO my WP's add-ons
	 */
	public function init_addons() {

		/******* Compability with previous versions of add-ons. To be removed in the future. */

		$deprecated_addons = array();

		// hook your add-on here ( deprecated ).
		$deprecated_addons = apply_filters( 'gmw_admin_addons_page', $deprecated_addons );

		foreach ( $deprecated_addons as $key => $addon ) {

			$new_addon                    = array();
			$new_addon['slug']            = $key;
			$new_addon['name']            = $addon['title'];
			$new_addon['prefix']          = ! empty( $addon['prefix'] ) ? $addon['prefix'] : '1.0';
			$new_addon['version']         = ! empty( $addon['version'] ) ? $addon['version'] : '1.0';
			$new_addon['author']          = ! empty( $addon['author'] ) ? $addon['author'] : 'Eyal Fitoussi';
			$new_addon['description']     = ! empty( $addon['desc'] ) ? $addon['desc'] : '';
			$new_addon['is_core']         = false;
			$new_addon['object_type']     = false;
			$new_addon['full_path']       = ! empty( $addon['file'] ) ? $addon['file'] : '';
			$new_addon['basename']        = plugin_basename( $new_addon['full_path'] );
			$new_addon['plugin_dir']      = untrailingslashit( plugin_dir_path( $new_addon['full_path'] ) );
			$new_addon['plugin_url']      = untrailingslashit( plugins_url( basename( plugin_dir_path( $new_addon['full_path'] ) ), dirname( $new_addon['full_path'] ) ) );
			$new_addon['required']        = false;
			$new_addon['min_version']     = ! empty( GMW()->required_versions[ $new_addon['slug'] ] ) ? GMW()->required_versions[ $new_addon['slug'] ] : '1.0';
			$new_addon['gmw_min_version'] = ! empty( $addon['gmw_version'] ) ? $addon['gmw_version'] : GMW_VERSION;
			$new_addon['item_name']       = ! empty( $addon['item'] ) ? $addon['item'] : false;
			$new_addon['item_id']         = ! empty( $addon['item_id'] ) ? $addon['item_id'] : null;
			$new_addon['api_url']         = ! empty( $addon['api_url'] ) ? $addon['api_url'] : null;
			$new_addon['license_name']    = ! empty( $addon['name'] ) ? $addon['name'] : false;
			$new_addon['trigger_license'] = ! empty( $addon['trigger_license'] ) ? $addon['trigger_license'] : false;

				// check for min requirements of add-ons with current version of GEO my WP.
			if ( ! empty( $new_addon['gmw_min_version'] ) && version_compare( GMW_VERSION, $new_addon['gmw_min_version'], '<' ) ) {

				$new_addon['status']                             = 'disabled';
				$new_addon['status_details']['error']            = 'gmw_version_mismatch';
				$new_addon['status_details']['required_version'] = $new_addon['gmw_min_version'];
				$new_addon['status_details']['notice']           = sprintf(
					__( '%1$s extension version %2$s requires GEO my WP plugin version %3$s or higher.', 'geo-my-wp' ),
					$new_addon['name'],
					$new_addon['version'],
					$new_addon['gmw_min_version']
				);

			} elseif ( ! empty( $new_addon['min_version'] ) && version_compare( $new_addon['version'], $new_addon['min_version'], '<' ) ) {

				$new_addon['status']                             = 'disabled';
				$new_addon['status_details']['error']            = 'addon_version_mismatch';
				$new_addon['status_details']['required_version'] = $new_addon['min_version'];
				$new_addon['status_details']['notice']           = sprintf(
					__( '%1$s extension requires an update to version %2$s.', 'geo-my-wp' ),
					$new_addon['name'],
					$new_addon['min_version']
				);

				// otherwise mark add-on as activated.
			} else {

				$new_addon['status'] = 'active';
			}

				// trigger license key.
			if ( class_exists( 'GMW_License' ) && ! empty( $new_addon['full_path'] ) ) {

				$gmw_license = new GMW_License(
					$new_addon['full_path'],
					$new_addon['item_name'],
					$new_addon['slug'],
					$new_addon['version'],
					$new_addon['author'],
					$new_addon['api_url'],
					$new_addon['item_id']
				);
			}

				GMW()->addons[ $new_addon['slug'] ] = $new_addon;

				GMW()->registered_addons[] = $new_addon['slug'];

			if ( ! empty( $new_addon['object_type'] ) ) {
				GMW()->object_types[] = $new_addon['object_type'];
			}
			// }
		}

		/********* End */
	}

	/**
	 * Add_form_button_pages
	 *
	 * Verify allowed pages for
	 */
	public static function add_form_button_pages() {

		// alowed pages can be modified.
		$pages = apply_filters( 'gmw_add_form_button_admin_pages', array( 'post.php', 'page.php', 'page-new.php', 'post-new.php' ) );

		return ( is_array( $pages ) && in_array( basename( wp_unslash( $_SERVER['PHP_SELF'] ) ), $pages ) ) ? 1 : 0; // WPCS: CSRF ok.
	}

	/**
	 * Action target that adds the "Insert Form" button to the post/page edit screen
	 *
	 * This script inspired by the the work of the developers of Gravity Forms plugin
	 */
	public static function add_form_button() {
		?>
		<style>
			.gmw_media_icon:before {
				content: "\f230" !important;
				color: rgb(103, 199, 134) !important;
			}
			.gmw_media_icon {
				vertical-align: text-top;
				width: 18px;
			}
			.wp-core-ui a.gmw_media_link{
				padding-left: 0.4em;
			}
		</style>

		<a 
			href="#TB_inline?width=480&inlineId=select_gmw_form" 
			class="thickbox button gmw_media_link" 
			id="add_gmw_form" 
			title="<?php esc_html_e( 'GEO my WP Form Shortcode', 'geo-my-wp' ); ?>"
		>
		<span class="dashicons-location-alt dashicons"></span>
			<?php esc_html_e( 'GMW Form', 'geo-my-wp' ); ?>
		</a>
		<?php
	}

	/**
	 * Form_insert_popup
	 *
	 * Popup form to inset GEO my WP form shortcode into content area
	 */
	public static function form_insert_popup() {
		?>
		<script>
			function gmwInsertForm() {

				var form_id = jQuery( '#gmw_form_id' ).val();

				if ( form_id == "" ){

					alert( '<?php _e( 'Please select a form', 'geo-my-wp' ); ?>' );

					return;
				}

				var form_name = jQuery( "#gmw_form_id option[value='" + form_id + "']" ).text().replace(/[\[\]]/g, '');
				var addon     = jQuery( "#gmw_form_id option[value='" + form_id + "']" ).data( 'type' );
				var prefix    = 'gmw';
				var attribute = jQuery( ".gmw_form_type:checked" ).val();

				if ( addon == 'ajax_forms' ) {
					prefix = 'gmw_ajax_form';
				} else if ( addon == 'global_maps' ) {
					prefix    = 'gmw_global_map';
					attribute = 'form';
				}

				window.send_to_editor( '[' + prefix + ' ' + attribute + '="' + form_id + '" name="' + form_name + '"]' );
			}
		</script>

		<div id="select_gmw_form" style="display:none;">
			<div class="gmw-form-shortcode-thickbox-wrap">
				<div>
					<div>
						<h3><?php esc_html_e( 'Insert A Form Shortcode', 'geo-my-wp' ); ?></h3>
						<p><?php esc_html_e( 'Select the type of shortcode you wish to add:', 'geo-my-wp' ); ?></p>
					</div>

					<div class="checkboxes">
						<label>
							<input 
								type="radio" class="gmw_form_type" checked="checked" name="gmw_form_type" value="form" 
								onclick="if ( jQuery( '#gmw-forms-dropdown-wrapper' ).is( ':hidden' ) ) jQuery( '#gmw-forms-dropdown-wrapper' ).slideToggle();" 
							/> 
							<?php esc_html_e( 'Complete Form', 'geo-my-wp' ); ?>	
						</label>

						<label>
							<input type="radio" class="gmw_form_type" name="gmw_form_type"  value="search_form" 
								onclick="if ( jQuery( '#gmw-forms-dropdown-wrapper' ).is( ':hidden' ) ) jQuery('#gmw-forms-dropdown-wrapper' ).slideToggle();" 
							/> 
							<?php esc_html_e( 'Search Form Only', 'geo-my-wp' ); ?>	
						</label>

						<label>
							<input type="radio" class="gmw_form_type" name="gmw_form_type"  value="map" 
								onclick="if ( jQuery( '#gmw-forms-dropdown-wrapper' ).is( ':hidden' ) ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" 
							/> 
							<?php esc_html_e( 'Map Only', 'geo-my-wp' ); ?>	
						</label>

						<label>
							<input type="radio" class="gmw_form_type" name="gmw_form_type" value="search_results" 
								onclick="if ( jQuery( '#gmw-forms-dropdown-wrappe' ).is( ':visible' ) ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" 
							/> 
							<?php esc_html_e( 'Search Results Only', 'geo-my-wp' ); ?>	
						</label>

					</div>

					<div id="gmw-forms-dropdown-wrapper">
						<select id="gmw_form_id">
							<option value="">
								<?php esc_html_e( 'Select a Form', 'geo-my-wp' ); ?>	
							</option>
							<?php
								$forms = gmw_get_forms();

							if ( empty( $forms ) || ! is_array( $forms ) ) {
								$forms = array();
							}

							foreach ( $forms as $form ) {

								$form['title'] = ! empty( $form['title'] ) ? $form['title'] : 'form_id_' . $form['ID'];
								?>
								<option value="<?php echo absint( $form['ID'] ); ?>" data-type="<?php echo esc_attr( $form['addon'] ); ?>"><?php echo esc_html( $form['title'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
				   
					<div>
						<input 
							type="button" 
							class="button-primary" 
							value="<?php esc_html_e( 'Insert Shortcode', 'geo-my-wp' ); ?>" 
							onclick="gmwInsertForm();"
						/>
						<a class="button" href="#" onclick="tb_remove(); return false;">
							<?php esc_html_e( 'Cancel', 'geo-my-wp' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * GMW credit footer.
	 *
	 * @param  [type] $content [description].
	 *
	 * @return [type]          [description]
	 */
	public static function gmw_credit_footer( $content ) {
		return preg_replace( '/[.,]/', '', $content ) . ' ' . sprintf( __( 'and Geolocating with <a %1$s>GEO my WP</a>. Your <a %2$s>feedback</a> on GEO my WP is greatly appriciated.', 'geo-my-wp' ), 'href="http://geomywp.com" target="_blank" title="GEO my WP website"', '<a href="https://wordpress.org/support/view/plugin-reviews/geo-my-wp?filter=5" target="_blank" title="Rate GEO my WP"' );
	}
}
new GMW_Admin();
?>
