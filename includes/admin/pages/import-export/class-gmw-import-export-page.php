<?php
/**
 * GEO my WP Export Import Page.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// we check if we are in impor/export page to prevent it from loading on all admin pages.
// We also check if this is an ajax call because the importer class uses ajax.
if ( ( empty( $_GET['page'] ) || 'gmw-import-export' !== $_GET['page'] ) && ! defined( 'DOING_AJAX' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
	return;
}

// include files.
require_once 'locations-importer/class-gmw-locations-importer.php';
require_once 'class-gmw-meta-fields-importer.php';
require_once 'tabs/gmw-data.php';
require_once 'tabs/forms.php';
require_once 'tabs/location-tables.php';
require_once 'tabs/gmw-v-3-import.php';
require_once 'tabs/posts-locator.php';
require_once 'tabs/users-locator.php';
require_once 'tabs/members-locator.php';
//require_once 'tabs/transfer-locations.php';
require_once 'class-gmw-export.php';
require_once 'gmw-csv-import.php';

/**
 * GMW Import / Export page
 *
 * @Since 3.0
 */
class GMW_Import_Export_Page {

	/**
	 * [__construct description]
	 */
	public function __construct() {
		add_filter( 'gmw_admin_notices_messages', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notices
	 *
	 * @param  array $messages messages.
	 *
	 * @return [type]           [description]
	 */
	public function admin_notices( $messages ) {

		$messages['data_imported']      = __( 'Data successfully imported.', 'geo-my-wp' );
		$messages['data_import_failed'] = __( 'Data import failed.', 'geo-my-wp' );

		return $messages;
	}

	/**
	 * Display Tools page
	 */
	public function output() {

		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'data'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		?>
		<?php gmw_admin_pages_header(); ?>

		<div id="gmw-import-export-page" class="wrap gmw-admin-page gmw-admin-page-wrapper">

			<nav class="gmw-admin-page-navigation-bg"></nav>
			<nav class="gmw-admin-page-navigation">

				<?php
				$tabs = $this->get_tabs();

				foreach ( $tabs as $slug => $tab ) {

					// for previous versions.
					if ( ! is_array( $tab ) ) {

						$tab           = array(
							'slug'  => $slug,
							'label' => $tab,
						);
						$tabs[ $slug ] = $tab;
					}

					// Prepare tab URL.
					$url = add_query_arg( array( 'tab' => $tab['slug'] ), admin_url( 'admin.php?page=gmw-import-export' ) );

					// Get tab icon.
					$icon = ! empty( $tab['icon'] ) ? 'gmw-icon-' . esc_attr( $tab['icon'] ) : '';

					printf(
						'<a href="%s"%s><span class="%s"></span></span> <span class="label">%s</span></a>',
						esc_url( $url ),
						$current_tab === $tab['slug'] ? ' class="active"' : '',
						esc_attr( $icon ),
						esc_html( $tab['label'] )
					);
				}
				?>
			</nav>

			<div class="gmw-admin-page-panels-wrapper" id="tab_<?php echo esc_attr( $current_tab ); ?>">

				<?php gmw_admin_page_loader(); ?>

				<h1 style="display:none;"></h1>

				<div id="gmw-admin-notices-holder"></div>

				<div id="gmw-<?php echo esc_attr( $current_tab ); ?>-tab-content" class="gmw-import-export-tab-content gmw-admin-page-content-inner">
					<?php do_action( 'gmw_import_export_' . $current_tab . '_tab' ); ?>
				</div>
			</div>

			<nav class="gmw-admin-page-sidebar">
				<?php gmw_admin_sidebar_content(); ?>
			</nav>
		</div>
		<?php
	}

	/**
	 * Retrieve tools tabs
	 *
	 * @since       2.5
	 * @return      array
	 */
	public function get_tabs() {

		$tabs         = array();
		$tabs['data'] = array(
			'slug'  => 'data',
			'label' => __( 'General Data', 'geo-my-wp' ),
		);

		$tabs['forms'] = array(
			'slug'  => 'forms',
			'label' => __( 'Forms', 'geo-my-wp' ),
		);

		$tabs['location_tables'] = array(
			'slug'  => 'location_tables',
			'label' => __( 'Location Tables', 'geo-my-wp' ),
		);

		/*$tabs['transfer_locations'] = array(
			'slug'  => 'transfer_locations',
			'label' => __( 'Transper Locations', 'geo-my-wp' ),
		);*/

		// if posts locator add-on active.
		if ( gmw_is_addon_active( 'posts_locator' ) ) {

			$tabs['posts_locator'] = array(
				'slug'  => 'posts_locator',
				'label' => __( 'Posts Locator', 'geo-my-wp' ),
			);

			// include tab file.
			require_once 'tabs/posts-locator.php';
		}

		// if posts locator add-on active.
		if ( gmw_is_addon_active( 'members_locator' ) ) {

			$tabs['members_locator'] = array(
				'slug'  => 'members_locator',
				'label' => __( 'Members Locator', 'geo-my-wp' ),
			);

			// include tab file.
			require_once 'tabs/members-locator.php';
		}

		// if posts locator add-on active.
		if ( gmw_is_addon_active( 'users_locator' ) ) {

			$tabs['users_locator'] = array(
				'slug'  => 'users_locator',
				'label' => __( 'Users Locator', 'geo-my-wp' ),
			);

			// include tab file.
			require_once 'tabs/users-locator.php';
		}

		return apply_filters( 'gmw_import_export_tabs', $tabs );
	}
}
