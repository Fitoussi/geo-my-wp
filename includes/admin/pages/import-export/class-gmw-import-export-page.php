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
if ( ( empty( $_GET['page'] ) || 'gmw-import-export' !== $_GET['page'] ) && ! defined( 'DOING_AJAX' ) ) { // WPCS: CSRF ok.
	return;
}

// include files.
require_once 'locations-importer/class-gmw-locations-importer.php';
require_once 'tabs/gmw-data.php';
require_once 'tabs/forms.php';
require_once 'tabs/location-tables.php';
require_once 'tabs/gmw-v-3-import.php';
require_once 'tabs/posts-locator.php';
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

		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'data'; // WPCS: CSRF ok.
		?>
		<div id="gmw-import-export-page" class="wrap gmw-admin-page">

			<h2 class="gmw-wrap-top-h2">

				<i class="gmw-icon-wrench"></i>

				<?php esc_attr_e( 'Import / Export', 'geo-my-wp' ); ?>

				<?php gmw_admin_helpful_buttons(); ?>

			</h2>

			<div class="clear"></div>

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $this->get_tabs() as $tab_id => $tab_name ) {

					$tab_url = admin_url( 'admin.php?page=gmw-import-export&tab=' . $tab_id );
					$active  = $active_tab == $tab_id ? ' nav-tab-active' : '';
					echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . esc_attr( $active ) . '">' . esc_html( $tab_name ) . '</a>';
				}
				?>
			</h2>

			<div class="content metabox-holder">

				<div id="gmw-<?php echo esc_attr( $active_tab ); // WPCS: CSRF ok. ?>-tab-content" class="gmw-tools-tab-content">

					<?php do_action( 'gmw_import_export_' . esc_attr( $active_tab ) . '_tab' ); ?>

				</div>

			</div><!-- .metabox-holder -->

		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Retrieve tools tabs
	 *
	 * @since       2.5
	 * @return      array
	 */
	public function get_tabs() {

		$tabs                    = array();
		$tabs['data']            = __( 'Data', 'geo-my-wp' );
		$tabs['forms']           = __( 'Forms', 'geo-my-wp' );
		$tabs['location_tables'] = __( 'Location Tables', 'geo-my-wp' );

		// if posts locator add-on active.
		if ( gmw_is_addon_active( 'posts_locator' ) ) {

			// create tab.
			$tabs['posts_locator'] = __( 'Posts Locator', 'geo-my-wp' );
			// include tab file.
			include_once 'tabs/posts-locator.php';
		}

		// if posts locator add-on active.
		/*if ( gmw_is_addon_active( 'members_locator' ) ) {
			// $tabs['members_locator'] = __( 'Members Locator', 'geo-my-wp' );
		}*/

		return apply_filters( 'gmw_import_export_tabs', $tabs );
	}
}
