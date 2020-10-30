<?php
/**
 * GEO my WP CSV importer.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW CSV importer class.
 *
 * Import data to database table via CSV file.
 *
 * @author Eyal Fitoussi
 *
 * @since 3.0
 *
 * @param  file   $file     file to import.
 *
 * @param  string $db_table name of database table to import data to.
 */
function gmw_csv_import( $file = false, $db_table = '' ) {

	if ( ! (bool) apply_filters( 'gmw_csv_import_capability', current_user_can( 'manage_options' ) ) ) {
		wp_die( esc_html__( 'You do not have permission to import data.', 'geo-my-wp' ), esc_html__( 'Error', 'geo-my-wp' ) );
	}

	if ( empty( $file ) ) {
		wp_die( esc_html__( 'Please upload a file to import', 'geo-my-wp' ) );
	}

	if ( empty( $db_table ) ) {
		wp_die( esc_html__( 'You did not provide a database table name.', 'geo-my-wp' ) );
	}

	if ( ! class_exists( 'parseCSV' ) ) {
		require_once GMW_PATH . '/includes/libraries/parsecsv/parsecsv.lib.php';
	}

	if ( ! class_exists( 'parseCSV' ) ) {
		wp_die( esc_html__( 'The parseCSV class is missing.', 'geo-my-wp' ) );
	}

	$csv = new parseCSV();
	$csv->auto( $file );

	$results = $csv->data;
	$page    = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // WPSC: CSRF ok.
	$tab     = ! empty( $_GET['tab'] ) ? '&tab=' . sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // WPSC: CSRF ok.

	// abort if not data to import.
	if ( empty( $results ) ) {

		wp_safe_redirect( admin_url( "admin.php?page={$page}{$tab}&gmw_notice=data_import_failed&gmw_notice_status=error" ) );

		exit;
	}

	global $wpdb;

	$table_name = $wpdb->prefix . $db_table;

	// check if table exists already.
	$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$table_name}'", ARRAY_A ); // WPCS: db call ok, cache ok, unprepared SQL ok.

	// if form table not exists create it.
	if ( 0 === count( $table_exists ) ) {
		/* Translators: %s : DB table name */
		wp_die( sprintf( esc_html__( 'The database table %s is missing', 'geo-my-wp' ), $table_name ) );
	}

	$columns_count = $wpdb->query( "describe {$table_name}" ); // WPCS: db call ok, cache ok, unprepared SQL ok.

	if ( absint( $columns_count ) !== count( $results[0] ) ) {
		wp_die( esc_html__( 'Columns in file do not match the database table.', 'geo-my-wp' ) );
	}

	// update data in database.
	foreach ( $results as $location ) {
		$wpdb->replace( $wpdb->prefix . $db_table, $location ); // WPCS: db call ok, cache ok, unprepared SQL ok.
	}

	wp_safe_redirect( admin_url( "admin.php?page={$page}{$tab}&gmw_notice=data_imported&gmw_notice_status=updated" ) );
	exit;
}
