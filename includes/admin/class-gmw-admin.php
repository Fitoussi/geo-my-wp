<?php
/**
 * GEO my WP - GMW_Admin class.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_Admin class.
 */
class GMW_Admin {

	/**
	 * If current page is a GEO my WP's admin page.
	 *
	 * @var boolean
	 */
	public $gmw_page = false;

	/**
	 * GEO my WP's Form Editor page.
	 *
	 * @var object
	 */
	public $edit_form_page = '';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// phpcs:disable.
		// get options.
		//$this->settings = get_option( 'gmw_options' );

		// admin notice to import location to the new database table.
		/*
		if ( get_option( 'gmw_old_locations_tables_exist' ) !== false && get_option( 'gmw_old_locations_tables_updated' ) === false ) {
			add_action( 'admin_notices', array( $this, 'update_database_notice' ) );
		}*/

		// admin notice to import location to the new database table.
		/*
		if ( get_option( 'gmw_folders_names_changed_notice_viewed' ) === false ) {
			add_action( 'admin_init', array( $this, 'folders_names_notice_dismiss' ) );
			add_action( 'admin_notices', array( $this, 'deprecated_folder_names_notice' ) );
		}*/
		// phpcs:enable.

		// do some actions.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_init', array( $this, 'init_addons' ) );
		add_filter( 'admin_body_class', array( $this, 'modify_body_class' ), 50 );

		// "GMW Form" button in edit post page.
		if ( self::add_form_button_pages() ) {
			add_action( 'media_buttons', array( $this, 'add_form_button' ), 25 );
			add_action( 'admin_footer', array( $this, 'form_insert_popup' ) );
		}

		do_action( 'gmw_pre_admin_include_pages' );

		// admin functions.
		require_once 'gmw-admin-functions.php';
		require_once 'class-gmw-tracking.php';
		require_once 'updater/class-gmw-license.php';
		require_once 'class-gmw-form-settings-helper.php';
		require_once 'class-gmw-forms-table.php';

		// admin pages.
		require_once 'pages/class-gmw-extensions.php';
		require_once 'pages/class-gmw-settings.php';
		require_once 'pages/class-gmw-forms-page.php';
		require_once 'pages/class-gmw-form-editor.php';
		require_once 'pages/tools/class-gmw-tools.php';
		require_once 'pages/import-export/class-gmw-import-export-page.php';

		//require_once GMW_PATH . '/includes/grid-stack/class-gmw-grid-stack.php';

		// set pages.
		// phpcs:disable.
		/*$this->addons_page   = new GMW_Extensions();
		$this->settings_page = new GMW_Settings();
		$this->forms_page    = new GMW_Forms_Page();

		if ( isset( $_GET['page'] ) && 'gmw-forms' === $_GET['page'] && isset( $_GET['gmw_action'] ) && 'edit_form' === $_GET['gmw_action'] && ! empty( $_GET['prefix'] ) && class_exists( 'GMW_' . $_GET['prefix'] . '_Form_Editor' ) ) { // WPCS: CSRF ok, sanitization OK.
			$class_name = 'GMW_' . sanitize_text_field( wp_unslash( $_GET['prefix'] ) ) . '_Form_Editor'; // WPCS: CSRF ok.
		} else {
			$class_name = 'GMW_Form_Editor';
		}

		$this->edit_form_page     = new $class_name();
		$this->tools_page         = new GMW_Tools();
		$this->import_export_page = new GMW_Import_Export_Page();*/
		// phpcs:enable.

		// We need to load this file here becuase it uses AJAX to save the form. the 'admin_menu' action doesn't fire during ajax.
		if ( defined( 'DOING_AJAX' ) ) {
			new GMW_Form_Editor();
		}

		add_filter( 'plugin_action_links_' . GMW_BASENAME, array( $this, 'action_links' ), 10 );

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
			'exporter_friendly_name' => __( 'GEO my WP Location Data', 'geo-my-wp' ),
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

		$table        = esc_sql( $wpdb->base_prefix . 'gmw_locations' );
		$number       = 200;
		$page         = (int) $page;
		$offset       = ( $page - 1 ) * $number;
		$group_labels = array(
			'post'     => __( 'Posts Locations', 'geo-my-wp' ),
			'user'     => __( 'User Locations', 'geo-my-wp' ),
			'bp_group' => __( 'BuddyPress Groups Locations', 'geo-my-wp' ),
		);

		// phpcs:disable
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
		); // phpcs:ignore: db call ok, cache ok, unprepared SQL ok.
		// phpcs:enable

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
					'name'  => ! is_array( $field ) ? $field : '',
					'value' => ! is_array( $value ) ? $value : '',
				);
			}

			// Look for location meta.
			$location_meta = gmw_get_location_meta( $location->ID );

			if ( ! empty( $location_meta ) ) {

				foreach ( $location_meta as $meta_field => $meta_value ) {

					$data[] = array(
						'name'  => ! is_array( $meta_field ) ? $meta_field : '',
						'value' => ! is_array( $meta_value ) ? $meta_value : '',
					);
				}
			}

			$export_items['data'][] = array(
				'group_id'    => $group_id,
				'group_label' => $group_label,
				'item_id'     => $item_id,
				'data'        => $data,
			);
		}

		$export_items['done'] = count( $locations ) < $number;

		return $export_items;
	}

	/**
	 * Admin notice.
	 */
	public function update_database_notice() {
		$allowed = array(
			'a' => array(
				'class' => array(),
			),
		);
		?>
		<div class="error">
			<p>
				<?php
				printf(
					/* translators: %s link to importer page. */
					wp_kses( __( 'GEO my WP needs to import existing locations into its new database table. <a href="%s" class="button-primary">Import locations</a>', 'geo-my-wp' ), $allowed ),
					esc_url( admin_url( 'admin.php?page=gmw-import-export&tab=gmw_v_3' ) )
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Admin notice.
	 */
	// phpcs:disable.
	/*
	public function folders_names_notice_dismiss() {

		if ( ! empty( $_GET['action'] ) && 'gmw_folders_names_dismiss' === $_GET['action'] ) { // WPCS: CSRF ok.

			update_option( 'gmw_folders_names_changed_notice_viewed', 1 );

			wp_safe_redirect( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // WPCS: CSRF ok, sanitization ok.
		}
	}*/
	// phpcs:enable.

	/**
	 * Admin notice.
	 *
	 * DEPRECATED
	 */
	// phpcs:disable.
	/*
	public function deprecated_folder_names_notice() {

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'gmw-extensions'; // WPCS: CSRF ok.
		?>
		<div class="notice notice-warning">
			<p><?php echo sprintf( __( '<h2>Important GEO my WP notice!</h2><p>The folders names for the custom template files have changed since GEO my WP version 3.0. If you have custom template files placed in the theme or child theme folder, you need to rename the folders to be able to use your custom template files.</p><p>See <a href="%1$s" target="_blank">this post</a> to learn about the new folders names.</p><p><a href="%2$s" class="button-secondary">Dismiss</a></p>' ), 'https://geomywp.com/geo-my-wp-3-0-upgrade-process/#folders-names', admin_url( 'admin.php?page=' . $page . '&action=gmw_folders_names_dismiss' ) ); ?></p>
		</div>
		<?php
	}*/
	// phpcs:enable.

	/**
	 * Admin action links.
	 *
	 * @param  array $links action links.
	 *
	 * @return array
	 */
	public function action_links( $links ) { // phpcs:ignore.

		$links['settings']   = '<a href="' . admin_url( 'admin.php?page=gmw-settings' ) . '">' . __( 'Settings', 'geo-my-wp' ) . '</a>';
		$links['extensions'] = '<a href="' . admin_url( 'admin.php?page=gmw-extensions' ) . '">' . __( 'Extensions', 'geo-my-wp' ) . '</a>';
		$links['docs']       = '<a href="https://docs.geomywp.com/" target="_blank">' . __( 'Documentation', 'geo-my-wp' ) . '</a>';

		return $links;
	}

	/**
	 * Admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {

		$addons_page        = new GMW_Extensions();
		$settings_page      = new GMW_Settings();
		$forms_page         = new GMW_Forms_Page();
		$tools_page         = new GMW_Tools();
		$import_export_page = new GMW_Import_Export_Page();

		if ( isset( $_GET['page'] ) && 'gmw-forms' === $_GET['page'] && isset( $_GET['gmw_action'] ) && 'edit_form' === $_GET['gmw_action'] && ! empty( $_GET['prefix'] ) && class_exists( 'GMW_' . $_GET['prefix'] . '_Form_Editor' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			$form_editor_class = 'GMW_' . sanitize_text_field( wp_unslash( $_GET['prefix'] ) ) . '_Form_Editor'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		} else {
			$form_editor_class = 'GMW_Form_Editor';
		}

		$edit_form_page = new $form_editor_class();

		// GEO my WP menu items.
		add_menu_page( 'GEO my WP', 'GEO my WP', 'manage_options', 'gmw-extensions', array( $addons_page, 'output' ), GMW_URL . '/menu-icon.png', 80 );

		// sub menu pages.
		$menu_items = array();

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Extensions', 'geo-my-wp' ),
			'menu_title'        => __( 'Extensions & Licenses', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-extensions',
			'callback_function' => array( $addons_page, 'output' ),
			'priority'          => 1,
		);

		$forms_output = ( ! empty( $_GET['gmw_action'] ) && 'edit_form' === $_GET['gmw_action'] ) ? $edit_form_page : $forms_page; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Forms', 'geo-my-wp' ),
			'menu_title'        => __( 'Forms', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-forms',
			'callback_function' => array( $forms_output, 'output' ),
			'priority'          => 3,
		);

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Settings', 'geo-my-wp' ),
			'menu_title'        => __( 'Settings', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-settings',
			'callback_function' => array( $settings_page, 'output' ),
			'priority'          => 5,
		);

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Import / Export', 'geo-my-wp' ),
			'menu_title'        => __( 'Import / Export', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-import-export',
			'callback_function' => array( $import_export_page, 'output' ),
			'priority'          => 7,
		);

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Tools', 'geo-my-wp' ),
			'menu_title'        => __( 'Tools', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-tools',
			'callback_function' => array( $tools_page, 'output' ),
			'priority'          => 9,
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
		foreach ( $menu_items as $item ) {

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
		if ( ( isset( $_GET['page'] ) && in_array( $_GET['page'], $gmw_pages, true ) ) || ( isset( $_GET['post_type'] ) && 'gmw_location_type' === $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

			$this->gmw_page = true;

			add_filter( 'admin_footer_text', array( $this, 'gmw_credit_footer' ), 10 );
			add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 200 );
			add_filter( 'admin_enqueue_scripts', array( $this, 'dequeue_scripts' ), 99999999999999 );
		}
	}

	// phpcs:disable.
	/*public function admin_menu() {

		$this->addons_page   = new GMW_Extensions();
		$this->settings_page = new GMW_Settings();
		$this->forms_page    = new GMW_Forms_Page();

		if ( isset( $_GET['page'] ) && 'gmw-forms' === $_GET['page'] && isset( $_GET['gmw_action'] ) && 'edit_form' === $_GET['gmw_action'] && ! empty( $_GET['prefix'] ) && class_exists( 'GMW_' . $_GET['prefix'] . '_Form_Editor' ) ) { // WPCS: CSRF ok, sanitization OK.
			$class_name = 'GMW_' . sanitize_text_field( wp_unslash( $_GET['prefix'] ) ) . '_Form_Editor'; // WPCS: CSRF ok.
		} else {
			$class_name = 'GMW_Form_Editor';
		}

		$this->edit_form_page     = new $class_name();
		$this->tools_page         = new GMW_Tools();
		$this->import_export_page = new GMW_Import_Export_Page();

		// GEO my WP menu items.
		add_menu_page( 'GEO my WP', 'GEO my WP', 'manage_options', 'gmw-extensions', array( $this->addons_page, 'output' ), GMW_URL . '/menu-icon.png', 66 );

		// sub menu pages.
		$menu_items = array();

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Extensions', 'geo-my-wp' ),
			'menu_title'        => __( 'Extensions & Licenses', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-extensions',
			'callback_function' => array( $this->addons_page, 'output' ),
			'priority'          => 1,
		);

		$forms_output = ( ! empty( $_GET['gmw_action'] ) && 'edit_form' === $_GET['gmw_action'] ) ? $this->edit_form_page : $this->forms_page; // WPCS: CSRF ok.

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Forms', 'geo-my-wp' ),
			'menu_title'        => __( 'Forms', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-forms',
			'callback_function' => array( $forms_output, 'output' ),
			'priority'          => 3,
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

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Import / Export', 'geo-my-wp' ),
			'menu_title'        => __( 'Import / Export', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-import-export',
			'callback_function' => array( $this->import_export_page, 'output' ),
			'priority'          => 7,
		);

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Tools', 'geo-my-wp' ),
			'menu_title'        => __( 'Tools', 'geo-my-wp' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'gmw-tools',
			'callback_function' => array( $this->tools_page, 'output' ),
			'priority'          => 9,
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
		/*$menu_items = apply_filters( 'gmw_admin_menu_items', $menu_items );

		// order menu items by priority.
		usort( $menu_items, 'gmw_sort_by_priority' );

		// gmw admin pages.
		$this->gmw_pages = array();

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

			$this->gmw_pages[] = $item['menu_slug'];
		}

		// apply credit and enqueue scripts and styles in GEO my WP admin pages only.
		if ( ( isset( $_GET['page'] ) && in_array( $_GET['page'], $this->gmw_pages, true ) ) || ( isset( $_GET['post_type'] ) && 'gmw_location_type' === $_GET['post_type'] ) ) { // WPCS: CSRF ok, sanitization ok.

			$this->gmw_page = true;

			add_filter( 'admin_footer_text', array( $this, 'gmw_credit_footer' ), 10 );
			add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}*/
	// phpcs:enable.

	/**
	 * MOdify body tag's class attribute.
	 *
	 * @since 4.0
	 *
	 * @param  [type] $classes [description].
	 *
	 * @return [type]          [description]
	 */
	public function modify_body_class( $classes ) {

		if ( $this->gmw_page ) {

			$page = '';

			if ( ! empty( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

				$page = sanitize_text_field( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

			} elseif ( ! empty( $_GET['post_type'] ) && 'gmw_location_type' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

				$page = 'location-types';
			}

			$hide_notices = gmw_get_option( 'general_settings', 'hide_admin_notices', false ) ? 'gmw-admin-notices-disabled' : '';
			$classes     .= ' gmw-admin-page gmw-' . $page . '-page ' . $hide_notices; // WPCS: XSS ok.
		}

		return $classes;
	}

	/**
	 * Dequeue/deregister scripts that conflict with GEO my WP on GEO my WP's admin pages.
	 *
	 * @since 4.3.3
	 *
	 * @return void
	 */
	public function dequeue_scripts() {

		// Deregister the select-2 library that is included by different plugins on GEO my WP admin page to prevent conflics.
		// GEO my WP will load its own select-2.
		wp_deregister_style( 'gamipress-select2-css' );
		wp_deregister_script( 'gamipress-select2-js' );
		wp_dequeue_style( 'gamipress-select2-css' );
		wp_dequeue_script( 'gamipress-select2-js' );

		wp_deregister_style( 'select2-goft' );
		wp_deregister_script( 'select2-goft' );
		wp_dequeue_style( 'select2-goft' );
		wp_dequeue_script( 'select2-goft' );

		wp_deregister_style( 'wpfepp-select2' );
		wp_deregister_script( 'wpfepp-select2' );
		wp_dequeue_style( 'wpfepp-select2' );
		wp_dequeue_script( 'wpfepp-select2' );

		wp_dequeue_script( 'wcv-vendor-select' );
		wp_dequeue_style( 'wcv-vendor-select' );

		wp_dequeue_script( 'selectWoo' );
		wp_deregister_script( 'selectWoo' );
		wp_dequeue_style( 'selectWoo' );
		wp_deregister_style( 'selectWoo' );

		wp_dequeue_style( 'rtcl-admin' );
		wp_dequeue_style( 'rtcl-public' );

		// ProfileGrid plugin.
		wp_dequeue_script( 'profilegrid_select2_js' );
		wp_dequeue_style( 'profilegrid_select2_css' );
		wp_deregister_script( 'profilegrid_select2_js' );
		wp_deregister_style( 'profilegrid_select2_css' );

		wp_dequeue_script( 'select2full' );
		wp_dequeue_style( 'select2full' );
		wp_deregister_script( 'select2full' );
		wp_deregister_style( 'select2full' );

		// JhonnyGo theme.
		wp_dequeue_script( 'ui-select-select2' );
		wp_dequeue_style( 'ui-select-select2' );
		wp_deregister_style( 'ui-select-select2' );
		wp_deregister_script( 'ui-select-select2' );

		// Event tickets plugin.
		wp_dequeue_script( 'tribe-select2' );
		wp_deregister_script( 'tribe-select2' );
		wp_dequeue_style( 'tribe-select2-css' );
		wp_deregister_style( 'tribe-select2-css' );

		// My Listings Theme.
		wp_dequeue_script( 'mylisting-select2' );
		wp_deregister_script( 'mylisting-select2' );
		wp_dequeue_style( 'mylisting-select2' );
		wp_deregister_style( 'mylisting-select2' );

		wp_dequeue_script( 'select2' );
		wp_dequeue_style( 'select2' );
		wp_deregister_style( 'select2' );
		wp_deregister_script( 'select2' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 4.0.
	 *
	 * @return [type] [description]
	 */
	public function enqueue_scripts() {

		$pages = array( 'gmw-extensions', 'gmw-settings', 'gmw-forms', 'gmw-import-export' );

		if ( ( ! empty( $_GET['page'] ) && ! in_array( $_GET['page'], $pages, true ) ) || ( isset( $_GET['post_type'] ) && 'gmw_location_type' === $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

			wp_deregister_style( 'select2' );
			wp_deregister_script( 'select2' );
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery('select').addClass('gmw-smartbox-not');
				});
			</script>
			<?php

			return;
		}

		if ( ! wp_script_is( 'jquery-ui-tooltip', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_style( 'jquery-ui-tooltip' );
		}

		if ( ! wp_script_is( 'jquery-confirm', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery-confirm' );
			wp_enqueue_style( 'jquery-confirm' );
		}

		// load select2.
		if ( ! wp_script_is( 'gmw-select2', 'enqueued' ) ) {
			wp_enqueue_script( 'gmw-select2' );
		}

		// load select2.
		if ( ! wp_style_is( 'gmw-select2', 'enqueued' ) ) {
			wp_enqueue_style( 'gmw-select2' );
		}

		if ( ! wp_style_is( 'wp-color-picker', 'enqueued' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}
		?>
		<style>
			html {
				font-size: 16px ! important;
			}
		</style>
		<?php
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
					/* translators: %1$s extension's name, %2$s version, %3$s min version. */
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
					/* translators: %1$s extension's name, %3$s min version. */
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

				new GMW_License(
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

		return ( is_array( $pages ) && ! empty( $_SERVER['PHP_SELF'] ) && in_array( basename( wp_unslash( $_SERVER['PHP_SELF'] ) ), $pages, true ) ) ? 1 : 0; // WPCS: CSRF ok, sanitization ok.
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

			.wp-core-ui a.gmw_media_link {
				padding-left: 0.4em;
			}

			#TB_title {
				background: 0 0;
				border-bottom: none;
				display: flex;
				height: auto;
				justify-content: space-between;
				padding: 1.675rem 2rem 1.375rem;
				background: #f7f7f7;
				border-bottom: 1px solid #ededed;
			}

			.tb-close-icon {
				margin-top: -5px;
				margin-right: 20px;
			}

			#TB_ajaxContent #gmw-form-shortcode-content p {
				font-weight: 500;
				margin-bottom: 15px;
			}

			#TB_ajaxWindowTitle {
				color: #242748;
				font-size: 1rem;
				font-weight: 400;
				line-height: 1.5rem;
				padding: 0;
				white-space: normal;
			}
		</style>

		<a href="#TB_inline?width=480&inlineId=select_gmw_form" class="thickbox button gmw_media_link" id="add_gmw_form"
			title="<?php esc_html_e( 'Insert GEO my WP Form', 'geo-my-wp' ); ?>">
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

		$message = __( 'You need to select a form', 'geo-my-wp' );
		?>
		<script>
			function gmwInsertForm() {

				var form_id = jQuery('#gmw_form_id').val();

				if ("" === form_id) {

					alert('<?php echo esc_html( $message ); ?>');

					return;
				}

				var form_name = jQuery("#gmw_form_id option[value='" + form_id + "']").text().replace(/[\[\]]/g, '').trim(),
					addon = jQuery("#gmw_form_id option[value='" + form_id + "']").data('type'),
					prefix = 'gmw',
					attribute = jQuery(".gmw_form_type:checked").val();

				window.send_to_editor('[' + prefix + ' ' + attribute + '="' + form_id + '" name="' + form_name + '"]');
			}
		</script>
		<div id="select_gmw_form" style="display:none;">

			<div class="gmw-form-shortcode-thickbox-wrap">

				<div id="gmw-form-shortcode-content">

					<p>
						<?php esc_html_e( 'Select the type of form you wish to add:', 'geo-my-wp' ); ?>
					</p>

					<div class="checkboxes">
						<label>
							<input type="radio" class="gmw_form_type" checked="checked" name="gmw_form_type" value="form"
								onclick="if ( jQuery( '#gmw-forms-dropdown-wrapper' ).is( ':hidden' ) ) jQuery( '#gmw-forms-dropdown-wrapper' ).slideToggle();" />
							<?php esc_html_e( 'Complete Form', 'geo-my-wp' ); ?>
						</label>

						<label>
							<input type="radio" class="gmw_form_type" name="gmw_form_type" value="search_form"
								onclick="if ( jQuery( '#gmw-forms-dropdown-wrapper' ).is( ':hidden' ) ) jQuery('#gmw-forms-dropdown-wrapper' ).slideToggle();" />
							<?php esc_html_e( 'Search Form Only', 'geo-my-wp' ); ?>
						</label>

						<label>
							<input type="radio" class="gmw_form_type" name="gmw_form_type" value="map"
								onclick="if ( jQuery( '#gmw-forms-dropdown-wrapper' ).is( ':hidden' ) ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" />
							<?php esc_html_e( 'Map Only', 'geo-my-wp' ); ?>
						</label>

						<label>
							<input type="radio" class="gmw_form_type" name="gmw_form_type" value="search_results"
								onclick="if ( jQuery( '#gmw-forms-dropdown-wrappe' ).is( ':visible' ) ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" />
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
								<option value="<?php echo absint( $form['ID'] ); ?>"
									data-type="<?php echo esc_attr( $form['addon'] ); ?>"><?php echo esc_html( $form['title'] ); ?>
								</option>
								<?php
							}
							?>
						</select>
					</div>

					<div>
						<input type="button" class="button-primary" value="<?php esc_html_e( 'Insert form', 'geo-my-wp' ); ?>"
							onclick="gmwInsertForm();" />
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
	 * @param  [type] $text [description].
	 *
	 * @return [type]          [description]
	 */
	public static function gmw_credit_footer( $text ) {
		?>
		<style type="text/css">
			body #footer-thankyou {
				position: fixed;
				bottom: 7px;
				z-index: 999;
				margin-left: 245px;
			}

			body.toplevel_page_gmw-extensions #footer-thankyou,
			.geo-my-wp_page_gmw-forms:not(.geo-my-wp_page_gmw-form-editor) #footer-thankyou {
				margin-left: 0;
			}
		</style>
		<?php

		$rate_text = sprintf(
			/* translators: %1$s link to GEO my WP website, %2$s link to WordPress.org */
			__( 'and geolocating with <a href="%1$s" target="_blank">GEO my WP</a>! Please <a href="%2$s" target="_blank">rate us on WordPress.org</a>.', 'geo-my-wp' ),
			'https://geomywp.com',
			'https://wordpress.org/support/view/plugin-reviews/geo-my-wp?filter=5'
		);

		$allowed = array(
			'a' => array(
				'href'   => array(),
				'target' => array(),
			),
		);

		return str_replace( '.</span>', '', $text ) . ' ' . wp_kses( $rate_text, $allowed ) . '</span>';
	}
}
new GMW_Admin();
