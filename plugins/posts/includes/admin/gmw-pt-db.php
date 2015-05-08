<?php
if ( !defined( 'ABSPATH' ) )
    exit;

//check if table exists
global $wpdb;
$ptTable = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}places_locator'", ARRAY_A );

//create or update database
if ( get_option( "gmw_pt_db_version" ) == '' || get_option( "gmw_pt_db_version" ) != GMW_PT_DB_VERSION || count( $ptTable ) == 0 ) {

    if ( count( $ptTable ) == 0 ) {
    	
        gmw_pt_db_installation();
        update_option( "gmw_pt_db_version", GMW_PT_DB_VERSION );
        
    } elseif ( count( $ptTable ) == 1 ) {    	
        gmw_pt_db_update();
    }
}

function gmw_pt_db_installation() {
	global $wpdb;

	$gmw_sql = "CREATE TABLE {$wpdb->prefix}places_locator (
	`post_id`           bigint(30) NOT NULL,
	`feature`           tinyint NOT NULL default '0',
	`post_status`       varchar(20) NOT NULL ,
	`post_type`         varchar(20) default 'post',
	`post_title`        TEXT,
	`lat`               float(10,6) NOT NULL ,
	`long`              float(10,6) NOT NULL ,
	`street_number` 	varchar(60) NOT NULL,
	`street_name` 		varchar(128) NOT NULL,
	`street`            varchar(128) NOT NULL ,
	`apt`               varchar(50) NOT NULL ,
	`city`              varchar(128) NOT NULL ,
	`state`             varchar(50) NOT NULL ,
	`state_long`        varchar(128) NOT NULL ,
	`zipcode`           varchar(40) NOT NULL ,
	`country`           varchar(50) NOT NULL ,
	`country_long`      varchar(128) NOT NULL ,
	`address`           varchar(255) NOT NULL ,
	`formatted_address` varchar(255) NOT NULL ,
	`phone`             varchar(50) NOT NULL ,
	`fax`               varchar(50) NOT NULL ,
	`email`             varchar(255) NOT NULL ,
	`website`           varchar(255) NOT NULL ,
	`map_icon`          varchar(50) NOT NULL ,
	UNIQUE KEY id (post_id)

	)	DEFAULT CHARSET=utf8;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta( $gmw_sql );
}

function gmw_pt_update_posts_database_table_notice() {
    
    if ( get_option( "gmw_pt_db_version" ) != '' && version_compare(  get_option( "gmw_pt_db_version" ) , '1.1', '==' ) ) {
    	global $wpdb;
	    ?>
	        <div class="error">
	            <form method="post" action="">
	                <p>
	                    <?php _e( "GEO my WP needs to update its posts location database table ( {$wpdb->prefix}places_locator ). Please consider making a backup of the table before proceeding with the update.", "GMW" ); ?>
	                    <input type="hidden" name="gmw_action" value="posts_db_table_update" />
	                    <?php wp_nonce_field( 'gmw_posts_db_table_update_nonce', 'gmw_posts_db_table_update_nonce' ); ?>
	                    <?php submit_button( __( 'Update', 'GMW' ), 'primary', 'submit', false ); 
	                    ?>
	                </p>
	            </form>
	        </div>
	    <?php
    }
}
add_action( 'admin_notices', 'gmw_pt_update_posts_database_table_notice' );

function gmw_pt_update_posts_database_table() {

    if ( empty( $_POST['gmw_action'] ) || $_POST['gmw_action'] != 'posts_db_table_update' )
        return;

    //look for nonce
    if ( empty( $_POST['gmw_posts_db_table_update_nonce'] ) )
       wp_die( __( 'Cheatin\' eh?!', 'GMW' ) );

    //varify nonce
    if ( !wp_verify_nonce( $_POST['gmw_posts_db_table_update_nonce'], 'gmw_posts_db_table_update_nonce' ) )
        wp_die( __( 'Cheatin\' eh?!', 'GMW' ) );

    global $wpdb;

    $dbTable = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}places_locator" );
    	
	//Add column if not present.
	if ( !isset( $dbTable->street_number ) ) {
		$wpdb->query("ALTER TABLE {$wpdb->prefix}places_locator ADD COLUMN `street_name` varchar(128) NOT NULL AFTER `long`");
		$wpdb->query("ALTER TABLE {$wpdb->prefix}places_locator ADD COLUMN `street_number` varchar(60) NOT NULL AFTER `long`");
		//update database version
		update_option( "gmw_pt_db_version", GMW_PT_DB_VERSION );
	} else {
		update_option( "gmw_pt_db_version", GMW_PT_DB_VERSION );
	}

    wp_safe_redirect( admin_url( 'admin.php?page=gmw-add-ons&gmw_notice=posts_db_table_updated&gmw_notice_status=updated' ) );
    exit;
}
add_action( 'gmw_posts_db_table_update', 'gmw_pt_update_posts_database_table' );

/*
 * Update posts locator database to 1.1
 */

function gmw_pt_db_update() {
         
    if ( get_option( "gmw_pt_db_version" ) != '' && get_option( "gmw_pt_db_version" ) == '1.0' ) {
    	
	    //run update
	    if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'gmw_pt_db_update' ) {
	
			global $wpdb;

	        //duplicate posts_locator table in case something goes wrong when modifying the table
	        $wpdb->get_results( "CREATE TABLE {$wpdb->prefix}places_locator_1_0 LIKE {$wpdb->prefix}places_locator" );
	        $wpdb->get_results( "INSERT {$wpdb->prefix}places_locator_1_0 SELECT * FROM {$wpdb->prefix}places_locator" );
	
	        //Change columns type from VARCHAR to FLOAT for better performance
	        $columnTypes = $wpdb->get_results( "SELECT column_name, column_type
					FROM information_schema.columns
					WHERE table_schema = '" . DB_NAME . "'
					AND table_name = '{$wpdb->prefix}places_locator'
					AND column_name IN ('lat','long')", ARRAY_A );
	
	        foreach ( $columnTypes as $column ) {
	            if ( $column[ 'column_name' ] == 'lat' )
	                $wpdb->get_results( "alter table `{$wpdb->prefix}places_locator` MODIFY COLUMN `lat` FLOAT(10,6)" );
	            if ( $column[ 'column_name' ] == 'long' )
	                $wpdb->get_results( "alter table `{$wpdb->prefix}places_locator` MODIFY COLUMN `long` FLOAT(10,6)" );
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
	                    <input type="submit" value="<?php _e( 'Update database', 'GMW' ); ?>" />
	                    <input type="hidden" name="action" value="gmw_pt_db_update" />
	                </p>
	            </form>
	        </div>
	        <?php
	
	    }
	
	    add_action( 'admin_notices', 'gmw_pt_update_notice' );
    }
}
?>