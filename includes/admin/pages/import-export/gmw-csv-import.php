<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * gmw_csv_import 
 *
 * import data to database table via CSV file 
 *
 * @author Eyal Fitoussi
 * @since 3.0
 * @param  file $file        file to import
 * @param  string  $db_table name of database table to import data to
 * 
 * @return [type]            [description]
 */
function gmw_csv_import( $file = false, $db_table = '' ) {

	if ( ! ( bool ) apply_filters( 'gmw_csv_import_capability', current_user_can( 'manage_options' ) ) ) {
		wp_die( __( 'You do not have permission to import data.', 'geo-my-wp' ), __( 'Error', 'geo-my-wp' ) );
	}

	if ( empty( $file ) ) {
		wp_die( __( 'Please upload a file to import', 'geo-my-wp' ) );
	}

	if ( empty( $db_table ) ) {
		wp_die( __( 'You did not provide a database table name.', 'geo-my-wp' ) );
	}

	$row 	 = 0;
	$col 	 = 0;
	$results = array();
	$handle  = @fopen( $file, 'r' );
	$page 	 = ! empty( $_GET['page'] ) ? $_GET['page'] : '';
	$tab     = ! empty( $_GET['tab'] )  ? '&tab='.$_GET['tab'] : '';

	if ( $handle ) {

		while ( ( $row = fgetcsv( $handle, 4096) ) !== false ) {

			if ( empty( $fields ) ) {
		
				$fields = $row;				
				$count  = count( $fields );
				continue;
			}

			foreach ( $row as $k => $value ) {	
				if ( $k < $count ) {
					$results[$col][$fields[$k]] = $value;
				}
			}
				
			$col++;

			unset( $row );
		}

		if ( ! feof( $handle ) ) {
			echo "Error: unexpected fgets() failn";
		}

		fclose( $handle );
	}

	// abort if not data to import
	if ( empty( $results ) ) {

		wp_safe_redirect( admin_url( "admin.php?page={$page}{$tab}&gmw_notice=data_import_failed&gmw_notice_status=error" ) );
		
		exit;
	}
	
	global $wpdb;
	
	$table_name = $wpdb->prefix . $db_table;
		
	// check if table exists already 
	$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$table_name}'", ARRAY_A );

	// if form table not exists create it
	if ( count( $table_exists ) == 0 ) {
		wp_die( __( "The database table {$table_name} not exist", 'geo-my-wp' ) );
	}

	$columns_count = $wpdb->query( "describe {$table_name}" ); 

	if ( $columns_count != count( $results[0] ) ) {
		wp_die( __( "Columns in file do not match the database table.", 'geo-my-wp' ) );
	}
	
	// update data in database
	foreach ( $results as $location ) {	
		$wpdb->replace( $wpdb->prefix . $db_table, $location );	
	}
	
	wp_safe_redirect( admin_url( "admin.php?page={$page}{$tab}&gmw_notice=data_imported&gmw_notice_status=updated" ) );
	exit;
}