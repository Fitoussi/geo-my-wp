<?php
if ( !defined( 'ABSPATH' ) ) exit;

/*
 * Update posts locator database to 1.1
*/
function gmw_pt_db_update() {
	global $wpdb;

	if ( get_option( "gmw_pt_db_version" ) != '' && get_option( "gmw_pt_db_version" ) > '1.0' ) {
		update_option( "gmw_pt_db_version", GMW_PT_DB_VERSION );
		return;
	}
	//run update
	if ( isset($_POST['action']) && $_POST['action'] == 'gmw_pt_db_update' ) {

		//duplicate posts_locator table in case something goes wrong when modifying the table
		$wpdb->get_results("CREATE TABLE {$wpdb->prefix}places_locator_1_0 LIKE {$wpdb->prefix}places_locator");
		$wpdb->get_results("INSERT {$wpdb->prefix}places_locator_1_0 SELECT * FROM {$wpdb->prefix}places_locator");

		//Change columns type from VARCHAR to FLOAT for better performance
		$columnTypes = $wpdb->get_results("SELECT column_name, column_type
				FROM information_schema.columns
				WHERE table_schema = '".DB_NAME."'
				AND table_name = '{$wpdb->prefix}places_locator'
				AND column_name IN ('lat','long')", ARRAY_A);

		foreach ( $columnTypes as $column ) {
			if ( $column['column_name'] == 'lat' ) $wpdb->get_results("alter table `{$wpdb->prefix}places_locator` MODIFY COLUMN `lat` FLOAT(10,6)");
			if ( $column['column_name'] == 'long' ) $wpdb->get_results("alter table `{$wpdb->prefix}places_locator` MODIFY COLUMN `long` FLOAT(10,6)");
		}
		//delete unused columns post_title, post_type, post_status
		//$wpdb->query("ALTER TABLE `{$wpdb->prefix}places_locator` DROP column `post_type`, DROP column `post_title`, DROP column `post_status`");

		//Display update completed notice
		function gmw_pt_update_notice_completed() {
			?>
		    <div class="gmw-upgrade-db-message" style="padding:0px 10px; margin: 5px 0 15px;border:1px solid;border-color: #81B3AF;background-color: #C5E0E4;font-size: 16px">
	        	<p>
	        		<?php _e( 'Thank you. GEO my WP database tables and data updated and everything seems to be ok.', 'GMW' ); ?>
	        	</p>
		    </div>
		<?php
		}
		add_action( 'admin_notices', 'gmw_pt_update_notice_completed' );
		update_option( "gmw_pt_db_version", GMW_PT_DB_VERSION );
		
		return;
	}
	
	//Display update notice
	function gmw_pt_update_notice() {
	?>
	    <div class="gmw-upgrade-db-message" style="padding:0px 10px; margin: 5px 0 15px;border:1px solid;border-color: #81B3AF;background-color: #C5E0E4;font-size: 16px">
	    	<form method="post">
	        	<p>
	        		<?php _e( 'GEO my WP Posts Locator add-on database tables must be updated. Please Backup your database before updating it.', 'GMW' ); ?>
	        		<input type="submit" value="<?php _e('Update database','GMW'); ?>" />
	        		<input type="hidden" name="action" value="gmw_pt_db_update" />
	        	</p>
	        </form>
	    </div>
	<?php
	}
	add_action( 'admin_notices', 'gmw_pt_update_notice' );
}