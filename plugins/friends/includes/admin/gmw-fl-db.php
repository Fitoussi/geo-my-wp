<?php
if ( !defined( 'ABSPATH' ) ) exit;

function gmw_fl_db_installation() {
		
	global $wpdb;
	$gmw_sql = array();

	$gmw_sql[] = "CREATE TABLE wppl_friends_locator (
		`member_id` 		bigint(30) NOT NULL,
		`lat` 				FLOAT(10,6) NOT NULL ,
		`long` 				FLOAT(10,6) NOT NULL ,
		`street` 			VARCHAR(255) NOT NULL ,
		`apt` 				VARCHAR(255) NOT NULL ,
		`city` 				VARCHAR(255) NOT NULL ,
		`state` 			VARCHAR(255) NOT NULL ,
		`state_long` 		VARCHAR(255) NOT NULL ,
		`zipcode` 			VARCHAR(255) NOT NULL ,
		`country` 			VARCHAR(255) NOT NULL ,
		`country_long` 		VARCHAR(255) NOT NULL ,
		`address` 			VARCHAR(255) NOT NULL ,
		`formatted_address` VARCHAR(255) NOT NULL ,
		`map_icon` 			VARCHAR(255) NOT NULL ,
		UNIQUE KEY id (member_id)

	)	DEFAULT CHARSET=utf8;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	dbDelta( $gmw_sql );

	$old_table = $wpdb->prefix . 'wppl_friends_locator';
	
	$oldTable = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}wppl_friends_locator'", ARRAY_A);
	
	if ( count( $oldTable ) == 1 ) {

		$check_table_rows = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $old_table );

		if( $check_table_rows > 0 ) {
			$wpdb->get_results('INSERT INTO wppl_friends_locator SELECT * FROM ' . $old_table);
			$wpdb->get_results('RENAME table '  . $old_table. ' to old_' . $old_table);
		} elseif ($check_table_rows == 0) {
			$wpdb->get_results('RENAME table '  . $old_table. ' to old_' . $old_table);
		}
		
	}
}

?>