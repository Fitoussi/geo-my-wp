<?php
if ( !defined( 'ABSPATH' ) ) exit;

/*
 * Update friends locator database to 1.1
 */
function gmw_fl_db_update() {
	global $wpdb;
	
	if ( get_option( "gmw_fl_db_version" ) != '' && get_option( "gmw_fl_db_version" ) > '1.0' ) {
		update_option( "gmw_fl_db_version", GMW_FL_DB_VERSION );
		return;
	}
	
	/*
	 * run update
	 */
	if ( isset($_POST['action']) && $_POST['action'] == 'gmw_fl_db_update' ) {
		
		/*
		 * duplicate friends_locator table in case something goes wrong when modifying the table
		*/
		$wpdb->get_results("CREATE TABLE wppl_friends_locator_backup_1_0 LIKE wppl_friends_locator");
		$wpdb->get_results("INSERT wppl_friends_locator_backup_1_0 SELECT * FROM wppl_friends_locator");
		
		/*
		 * Change columns type from VARCHAR to FLOAT for better performance
		*/
		$columnTypes = $wpdb->get_results("SELECT column_name, column_type
			FROM information_schema.columns
			WHERE table_schema = '".DB_NAME."'
			AND table_name = 'wppl_friends_locator'
			AND column_name IN ('lat','long')", ARRAY_A);
	
		foreach ( $columnTypes as $column ) {
			if ( $column['column_name'] == 'lat' ) $wpdb->get_results("alter table `wppl_friends_locator` MODIFY COLUMN `lat` FLOAT(10,6)");
			if ( $column['column_name'] == 'long' ) $wpdb->get_results("alter table `wppl_friends_locator` MODIFY COLUMN `long` FLOAT(10,6)");
		}

		/*
		 * Delete deleted users from wppl_friends_locator
		*/
		$wpdb->get_results("DELETE b FROM wppl_friends_locator b LEFT JOIN {$wpdb->users} f ON f.ID = b.member_id WHERE f.ID IS NULL");
		
		/*
		 * Display update completed notice
		 */
		function gmw_fl_update_notice_completed() {
		?>
		    <div class="gmw-upgrade-db-message" style="padding:0px 10px; margin: 5px 0 15px;border:1px solid;border-color: #81B3AF;background-color: #C5E0E4;font-size: 16px">
	        	<p>
	        		<?php _e( 'Thank you. GEO my WP database tables and data updated and everything seems to be ok.', 'GMW' ); ?>
	        	</p>
		    </div>
		<?php
		}
		add_action( 'admin_notices', 'gmw_fl_update_notice_completed' );
		update_option( "gmw_fl_db_version", GMW_FL_DB_VERSION );
		
		return;
	}
	
	/*
	 * Display update notice
	 */
	function gmw_fl_update_notice() {
	?>
	    <div class="gmw-upgrade-db-message" style="padding:0px 10px; margin: 5px 0 15px;border:1px solid;border-color: #81B3AF;background-color: #C5E0E4;font-size: 16px">
	    	<form method="post">
	        	<p>
	        		<?php _e( 'GEO my WP Friends Locator add-on database tables must be updated. Please Backup your database before updating it.', 'GMW' ); ?>
	        		<input type="submit" value="<?php _e('Update database','GMW'); ?>" />
	        		<input type="hidden" name="action" value="gmw_fl_db_update" />
	        	</p>
	        </form>
	    </div>
	<?php
	}
	add_action( 'admin_notices', 'gmw_fl_update_notice' );

}

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

	dbDelta($gmw_sql);

	$old_table = $wpdb->prefix . 'wppl_friends_locator';
	$check_table_rows = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $old_table );

	if($check_table_rows > 0) {
		$wpdb->get_results('INSERT INTO wppl_friends_locator SELECT * FROM ' . $old_table);
		$wpdb->get_results('RENAME table '  . $old_table. ' to old_' . $old_table);
	} elseif ($check_table_rows == 0) {
		$wpdb->get_results('RENAME table '  . $old_table. ' to old_' . $old_table);
	}
}

?>