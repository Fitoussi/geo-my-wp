<?php
/**
 * GEO my WP GMW_Cron class.
 *
 * Cron tasks.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Cron class.
 *
 * @author This class was originally written by Pippin Williamson for Easy Digital Downloads plugin
 *
 * and modifyed to work with GEO my WP. Thank you!
 *
 * @since 3.0
 */
class GMW_Cron {

	/**
	 * Run GMW_Cron
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
		add_action( 'wp', array( $this, 'schedule_events' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @since 3.0
	 *
	 * @param array $schedules default schedules.
	 *
	 * @return array
	 */
	public function add_schedules( $schedules = array() ) {

		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'geo-my-wp' ),
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function schedule_events() {
		$this->weekly_events();
		//$this->daily_events();
		//$this->hourly_events();
	}

	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since 3.0
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'gmw_weekly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'gmw_weekly_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @access private
	 * @since 3.0
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'gmw_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'gmw_daily_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @access private
	 * @since 3.0
	 * @return void
	 */
	private function hourly_events() {
		if ( ! wp_next_scheduled( 'gmw_hourly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'gmw_hourly_scheduled_events' );
		}
	}
}
$gmw_cron = new gmw_Cron();
