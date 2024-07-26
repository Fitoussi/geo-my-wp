<?php
/**
 * GEO my WP - admin functions.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processes all GMW admin notices.
 *
 * Notice type pass via $_GET['gmw_notice'] and notice status via $_GET['gmw_notice_stastus']
 *
 * @since 2.5
 *
 * @author Eyal Fitoussi
 */
function gmw_output_admin_notices() {

	// check if notice exist.
	if ( empty( $_REQUEST['gmw_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended, CSRF ok.
		return;
	}

	$gmw_messages = apply_filters(
		'gmw_admin_notices_messages',
		array(
			'posts_db_table_updated'   => __( 'GEO my WP posts locations db table successfully updated.', 'geo-my-wp' ),
			'members_db_table_updated' => __( 'GEO my WP members locations db table successfully updated.', 'geo-my-wp' ),
			'tracking_allowed'         => __( 'Thank you for helping us improve GEO my WP.', 'geo-my-wp' ),
		)
	);

	$notice_type   = isset( $_REQUEST['gmw_notice'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['gmw_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
	$notice_status = isset( $_REQUEST['gmw_notice_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['gmw_notice_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

	if ( 'updated' === $notice_status ) {
		$notice_status = 'success';
	}

	$allowed = array(
		'a'  => array(
			'title' => array(),
			'href'  => array(),
		),
		'p'  => array(),
		'em' => array(),
	);
	?>
	<div class="gmw-admin-notice-top notice notice-<?php echo esc_attr( $notice_status ); ?>">
		<p><?php echo isset( $gmw_messages[ $notice_type ] ) ? wp_kses( $gmw_messages[ $notice_type ], $allowed ) : ''; ?></p>
	</div>
	<?php

}
add_action( 'admin_notices', 'gmw_output_admin_notices' );

/**
 * Generate link to update plugins page
 *
 * @param  [type] $basename add-on based name.
 *
 * @return mixed link.
 */
function gmw_get_update_addon_link( $basename ) {
	return '<a href="' . admin_url( 'plugins.php' ) . '#' . esc_attr( strtolower( preg_replace( '/[-]+/i', '-', str_replace( ' ', '-', $basename ) ) ) ) . '" title="Plugins Page" style="color:white;text-decoration: underline"><i class="fa fa-refresh"></i>  Update now</a>';
}

/**
 * Get array of all registered post types.
 *
 * @return [type] [description]
 */
function gmw_get_post_types_array() {

	$output = array();

	foreach ( get_post_types() as $post ) {
		$output[ $post ] = get_post_type_object( $post )->labels->name . ' ( ' . $post . ' )';
	}

	return $output;
}

/**
 * Get admin settings field.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 *
 * @param  array  $field     [description].
 *
 * @param  string $name_attr [description].
 *
 * @param  string $value     [description].
 *
 * @return [type]            [description]
 */
function gmw_get_admin_settings_field( $field = array(), $name_attr = '', $value = '' ) {
	return GMW_Form_Settings_Helper::get_settings_field( $field, $name_attr , $value );
}

/**
 * Get form builder field arguments.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 *
 * @param  [type] $args [description].
 *
 * @return [type]       [description]
 */
function gmw_get_admin_setting_args( $args ) {
	return GMW_Form_Settings_Helper::get_setting_args( $args );
}

/**
 * Main menu for admin pages.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
function gmw_admin_pages_menu() {

	global $submenu;

	$menu_items = $submenu['gmw-extensions'];
	$menu_icons = array(
		'gmw-extensions'    => 'gmw-icon-puzzle',
		'gmw-forms'         => 'gmw-icon-doc-text',
		'gmw-settings'      => 'gmw-icon-cog-alt',
		'gmw-import-export' => 'gmw-icon-updown-circle',
		'gmw-tools'         => 'gmw-icon-wrench',
	);
	?>
	<div class="gmw-admin-pages-menu-wrapper">

		<?php /*<a href="https://geomywp.com/" target=_blank">
			<img id="site-logo-header" style="width: 170px;height: 50px;" alt="" src="<?php echo GMW_URL . '/gmw-logo.png'; ?>" class="ct-image">
		</a> */
		?>

		<div class="gmw-admin-pages-menu-inner">

			<a href="https://geomywp.com/" target=_blank">
				<img id="site-logo-header" style="width: 170px;height: 50px;" alt="" src="<?php echo GMW_URL . '/gmw-logo.png'; ?>" class="ct-image">
			</a>

			<?php $count = 0; ?>

			<div style="display: flex;flex-direction: row;max-width: 860px;width: 100%;justify-content: space-between;">

			<?php foreach ( $menu_items as $menu_item ) { ?>

				<?php

				// We want only the first main menu items. No need all.
				if ( 5 === $count ) {
					break;
				}

				$count++;
				?>

				<?php $active = ( ! empty( $_GET['page'] ) && $_GET['page'] === $menu_item[2] ) ? 'active' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok. ?>
				<?php $icon = ! empty( $menu_icons[ $menu_item[2] ] ) ? $menu_icons[ $menu_item[2] ] : ''; ?>
				<?php $url = strpos( $menu_item[2], 'edit.php' ) !== false ? $menu_item[2] : 'admin.php?page=' . $menu_item[2]; ?>
				<a
					class="gmw-admin-pages-menu-item <?php echo esc_attr( $icon ); ?> <?php echo esc_attr( $active ); ?>"
					title="<?php esc_attr( $menu_item[3] ); ?>"
					href="<?php echo esc_url( admin_url( $url ) ); ?>">
					<?php echo esc_attr( $menu_item[0] ); ?>
				</a>
			<?php } ?>
			</div>
		</div>
	</div>
	<?php do_action( 'gmw_admin_pages_menu' ); ?>
	<?php
}

/**
 * GEO my WP top credits
 */
function gmw_admin_helpful_buttons() {
	?>
	<div class="gmw-helpful-links-wrapper">
		<div class="gmw-helpful-links-inner">
			<a
				class="gmw-helpful-links"
				title="Official Website"
				href="https://geomywp.com"
				target="_blank">
				<i class="dashicons dashicons-admin-site-alt3"></i>GEOmyWP.com
			</a>

			<a
				class="gmw-helpful-links"
				title="Extensions"
				href="https://geomywp.com/extensions"
				target="_blank">
				<i class="gmw-icon-puzzle"></i>Extensions
			</a>

			<a
				class="gmw-helpful-links"
				title="documentation"
				href="https://docs.geomywp.com"
				target="_blank">
				<i class="gmw-icon-doc-text"></i>
				<?php esc_html_e( 'Docs', 'geo-my-wp' ); ?>
			</a>

			<a
				class="gmw-helpful-links"
				title="support"
				href="https://geomywp.com/support"
				target="_blank">
				<i class="gmw-icon-lifebuoy"></i>
				<?php esc_html_e( 'Support', 'geo-my-wp' ); ?>
			</a>

			<a
				class="gmw-helpful-links"
				title="Demo"
				href="http://demo.geomywp.com"
				target="_blank">
				<i class="gmw-icon-monitor"></i>
				<?php esc_html_e( 'Demo', 'geo-my-wp' ); ?>
			</a>

			<a
				class="gmw-helpful-links"
				title="Donate"
				href="https://www.paypal.me/fitoussi"
				target="_blank">
				<i style="font-size: 18px;margin-right: -2px;" class="dashicons dashicons-money-alt"></i>
				<?php esc_html_e( 'Donate', 'geo-my-wp' ); ?>
			</a>

			<a
				class="gmw-helpful-links"
				title="GEO my WP on GitHub"
				href="https://github.com/Fitoussi/GEO-my-WP"
				target="_blank">
				<i class="gmw-icon-github"></i>Contribute
			</a>

			<a
				class="gmw-helpful-links"
				title="Show your support"
				href="https://wordpress.org/support/view/plugin-reviews/geo-my-wp?filter=5"
				target="_blank">
				<i class="gmw-icon-star"></i>
				<?php esc_html_e( 'Love', 'geo-my-wp' ); ?>
			</a>

			<a
				class="gmw-helpful-links"
				title="GEO my WP on Facebook"
				href="https://www.facebook.com/geomywp"
				target="_blank">
				<i class="gmw-icon-facebook-squared"></i>
				<?php esc_html_e( 'Like', 'geo-my-wp' ); ?>
			</a>

			<a
				class="gmw-helpful-links"
				title="GEO my WP on Twitter"
				href="https://twitter.com/GEOmyWP"
				target="_blank">
				<i class="gmw-icon-twitter"></i>
				<?php esc_html_e( 'Follow', 'geo-my-wp' ); ?>
			</a>

			<?php do_action( 'gmw_admin_helpful_buttons' ); ?>
		</div>
	</div>
	<?php
}

/**
 * Generate the admin page loader skin/icon.
 *
 * @author Eyal Fitoussi
 *
 * @since 4.0
 */
function gmw_admin_page_loader() {
	?>
	<div id="gmw-admin-page-loader">
		<div id="gmw-admin-page-loader-inner">
			<i class="gmw-icon gmw-icon-spin-3 animate-spin"></i>
			<span></span>
		</div>
	</div>
	<?php
}

/**
 * Output header for admin pages.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 */
function gmw_admin_pages_header() {
	gmw_admin_helpful_buttons();
	gmw_admin_pages_menu();
}

/**
 * Generate content for the admin's pages sidebar.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 *
 * @return [type] [description]
 */
function gmw_admin_sidebar_content() {
	return;
	/* ?>
	<!-- <iframe class="mj-w-res-iframe" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://app.mailjet.com/widget/iframe/5XJU/KMe" width="100%"></iframe>
	<script type="text/javascript" src="https://app.mailjet.com/statics/js/iframeResizer.min.js"></script> -->
	<!-- <img src="https://graphic-mama.s3.amazonaws.com/previews/0v47rp9m1g8yk5ldownyjzq2/61603fd530949-Marker-mascot-pose3_original.jpg" style="max-width:100%" /> -->
	<?php */
}
