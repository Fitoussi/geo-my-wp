<?php
/**
 * GEO my WP Tools page
 *
 * @since 2.5
 *
 * @Author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// include files in tools page only.
if ( empty( $_GET['page'] ) || 'gmw-tools' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
	return;
}

require_once 'tabs/reset-gmw.php';
require_once 'tabs/system-info.php';
require_once 'tabs/api-testing.php';

// Map icons tabs when the Premium Settings extension is activated.
if ( gmw_is_addon_active( 'premium_settings' ) ) {
	require_once 'tabs/map-icons.php';
}
require_once 'tabs/cache.php';

/**
 * GMW Tools page
 *
 * @since 2.5
 */
class GMW_Tools {

	/**
	 * [__construct description]
	 */
	public function __construct() {}

	/**
	 * Display Tools page
	 */
	public function output() {

		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'system_info'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		?>
		<?php gmw_admin_pages_header(); ?>

		<div id="gmw-tools-page" class="wrap gmw-admin-page gmw-admin-page-wrapper">

			<nav class="gmw-admin-page-navigation-bg"></nav>
			<nav class="gmw-admin-page-navigation">

				<?php
				$tabs = $this->get_tabs();

				foreach ( $tabs as $slug => $tab ) {

					// for previous versions.
					if ( ! is_array( $tab ) ) {

						$tab = array(
							'slug'  => $slug,
							'label' => $tab,
						);

						$tabs[ $slug ] = $tab;
					}

					// Prepare tab URL.
					$url = add_query_arg( array( 'tab' => $tab['slug'] ), admin_url( 'admin.php?page=gmw-tools' ) );

					// Get tab icon.
					$icon = ! empty( $tab['icon'] ) ? 'gmw-icon-' . esc_attr( $tab['icon'] ) : '';

					printf(
						'<a href="%s"%s><span class="%s"></span></span> <span class="label">%s</span></a>',
						esc_url( $url ),
						$current_tab === $tab['slug'] ? ' class="active"' : '',
						$icon,
						esc_html( $tab['label'] )
					);
				}
				?>
			</nav>

			<div class="gmw-admin-page-panels-wrapper" id="tab_<?php echo esc_attr( $current_tab ); ?>">

				<?php gmw_admin_page_loader(); ?>

				<div id="gmw-admin-notices-holder"></div>

				<h1 style="display:none"></h1>

				<div id="gmw-<?php echo esc_attr( $current_tab ); ?>-tab-content"
					class="gmw-tools-tab-content gmw-admin-page-content-inner">
					<?php do_action( 'gmw_tools_' . $current_tab . '_tab' ); ?>
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

		$tabs                = array();
		$tabs['system_info'] = array(
			'slug'  => 'system_info',
			'label' => __( 'System Info', 'geo-my-wp' ),
		);

		$tabs['api_testing'] = array(
			'slug'  => 'api_testing',
			'label' => __( 'API Testing', 'geo-my-wp' ),
		);

		$tabs['reset_gmw']   = array(
			'slug'  => 'reset_gmw',
			'label' => __( 'Uninstall GEO my WP', 'geo-my-wp' ),
		);

		if ( gmw_is_addon_active( 'premium_settings' ) ) {
			$tabs['map_icons'] = array(
				'slug'  => 'map_icons',
				'label' => __( 'Map Icons', 'geo-my-wp' ),
			);
		}

		$tabs['internal_cache']   = array(
			'slug'  => 'internal_cache',
			'label' => __( 'Cache', 'geo-my-wp' ),
		);

		return apply_filters( 'gmw_tools_tabs', $tabs );
	}
}
