<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GEO_my_WP_Installer
 */
class GEO_my_WP_Installer {

	/**
	 * Install GEO my WP
	 */
	public static function install() {
		global $wpdb;

		self::schedule_cron();

		//do some updating
		//................

		update_option( 'gmw_version', GMW_VERSION );
	}

	/**
	 * Setup cron jobs
	 */
	private static function schedule_cron() {
		wp_clear_scheduled_hook( 'gmw_clear_expired_transients' );
		wp_schedule_event( time(), 'twicedaily', 'gmw_clear_expired_transients' );
	}
}
