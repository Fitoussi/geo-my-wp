<?php
/**
 * GEO my WP Tools page - system info tab.
 *
 * @since  2.5
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once GMW_PATH . '/includes/admin/pages/tools/class-gmw-system-info.php';

if ( class_exists( 'GMW_System_Info' ) ) {
	$system_info = new GMW_System_Info();
}

add_action( 'gmw_tools_system_info_tab', array( $system_info, 'output' ) );
