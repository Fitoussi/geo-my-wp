<?php
if ( !defined( 'ABSPATH' ) ) exit;

function gmw_pt_db_installation() {
	global $wpdb;

	$gmw_sql = "CREATE TABLE {$wpdb->prefix}places_locator (
		`post_id` 			bigint(30) NOT NULL,
		`feature`			tinyint NOT NULL default '0',
		`post_status` 		varchar(20) NOT NULL ,
		`post_type` 		varchar(20) default 'post',
		`post_title` 		TEXT,
		`lat` 				float(10,6) NOT NULL ,
		`long` 				float(10,6) NOT NULL ,
		`street` 			varchar(128) NOT NULL ,
		`apt` 				varchar(50) NOT NULL ,
		`city` 				varchar(128) NOT NULL ,
		`state` 			varchar(50) NOT NULL ,
		`state_long` 		varchar(128) NOT NULL ,
		`zipcode` 			varchar(40) NOT NULL ,
		`country` 			varchar(50) NOT NULL ,
		`country_long` 		varchar(128) NOT NULL ,
		`address` 			varchar(255) NOT NULL ,
		`formatted_address` varchar(255) NOT NULL ,
		`phone` 			varchar(50) NOT NULL ,
		`fax` 				varchar(50) NOT NULL ,
		`email` 			varchar(255) NOT NULL ,
		`website` 			varchar(255) NOT NULL ,
		`map_icon`			varchar(50) NOT NULL ,
	UNIQUE KEY id (post_id)
	
	)	DEFAULT CHARSET=utf8;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($gmw_sql);
}

?>