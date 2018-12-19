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
	if ( empty( $_GET['gmw_notice'] ) && empty( $_POST['gmw_notice'] ) ) { // WPCS: CSRF ok.
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

	$notice_type   = isset( $_GET['gmw_notice'] ) ? $_GET['gmw_notice'] : $_POST['gmw_notice']; // WPCS: CSRF ok, sanitization ok.
	$notice_status = isset( $_GET['gmw_notice_status'] ) ? $_GET['gmw_notice_status'] : $_POST['gmw_notice_status']; // WPCS: CSRF ok, sanitization ok.

	$notice_type   = sanitize_text_field( wp_unslash( $notice_type ) );
	$notice_status = sanitize_text_field( wp_unslash( $notice_status ) );

	$allowed = array(
		'a'  => array(
			'title' => array(),
			'href'  => array(),
		),
		'p'  => array(),
		'em' => array(),
	);
	?>
	<div class="<?php echo esc_attr( $notice_status ); ?>">
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
 * @return HTML link.
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
 * GEO my WP top credits
 */
function gmw_admin_helpful_buttons() {

	if ( ! empty( $_GET['page'] ) && 'gmw-forms' !== $_GET['page'] ) { // WPCS: CSRF ok.
		?>
		<span style="font-size:14px;margin-right:5px;"> - 
			<?php
			echo sprintf(
				/* translators: %s: developer's name. */
				esc_html__( 'GEO my WP developed by %s', 'geo-my-wp' ),
				'Eyal Fitoussi'
			);
			?>
		</span>
		<a 
			class="button action gmw-donate" 
			title="Donate" href="https://www.paypal.me/fitoussi" 
			target="_blank">
			<i style="color:red;margin-right:4px;" class="gmw-icon-heart"></i>
			<?php esc_html_e( 'Donate', 'geo-my-wp' ); ?>
		</a>

	<?php } ?>

	<span class="gmw-helpful-links-wrapper">
		<a 
			class="button action" 
			title="Official Website" 
			href="https://geomywp.com" 
			target="_blank">
			<i class="dashicons dashicons-welcome-view-site" style="font-size:18px;margin-top:4px"></i>GEOmyWP.com
		</a>

		<a 
			class="button action" 
			title="Extensions" 
			href="https://geomywp.com/extensions" 
			target="_blank" 
			style="color:green">
			<i class="gmw-icon-puzzle"></i>Extensions
		</a>

		<a 
			class="button action" 
			title="Demo" 
			href="http://demo.geomywp.com" 
			target="_blank">
			<i class="gmw-icon-monitor"></i>
			<?php esc_html_e( 'Demo', 'geo-my-wp' ); ?>
		</a>

		<a 
			class="button action" 
			title="documentation" 
			href="https://docs.geomywp.com" 
			target="_blank">
			<i class="gmw-icon-doc-text"></i>
			<?php esc_html_e( 'Docs', 'geo-my-wp' ); ?>
		</a>

		<a 
			class="button action" 
			title="support" 
			href="https://geomywp.com/support" 
			target="_blank">
			<i class="gmw-icon-lifebuoy"></i>
			<?php esc_html_e( 'Support', 'geo-my-wp' ); ?>
		</a>

		<a 
			class="button action" 
			title="GEO my WP on GitHub" 
			href="https://github.com/Fitoussi/GEO-my-WP" 
			target="_blank">
			<i class="gmw-icon-github"></i>GitHub
		</a>

		<a 
			class="button action" 
			title="Show your support" 
			href="https://wordpress.org/support/view/plugin-reviews/geo-my-wp?filter=5" 
			target="_blank">
			<i style="color:orange" class="gmw-icon-star"></i>
			<?php esc_html_e( 'Love', 'geo-my-wp' ); ?>
		</a>

		<a 
			class="button action" 
			title="GEO my WP on Facebook" 
			href="https://www.facebook.com/geomywp" 
			target="_blank">
			<i style="color:blue;font-size: 16px" class="gmw-icon-facebook-squared"></i>
			<?php esc_html_e( 'Like', 'geo-my-wp' ); ?>
		</a>

		<a 
			class="button action" 
			title="GEO my WP on Twitter" 
			href="https://twitter.com/GEOmyWP" 
			target="_blank">
			<i style="color:lightblue;font-size: 16px;" class="gmw-icon-twitter"></i>
			<?php esc_html_e( 'Follow', 'geo-my-wp' ); ?>
		</a>
		<?php do_action( 'gmw_admin_helpful_buttons' ); ?>
	</span>
	<?php
}
