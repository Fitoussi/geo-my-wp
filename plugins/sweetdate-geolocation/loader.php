<?php
//make sure Sweet-date theme existsand active
$active_theme = wp_get_theme();
if ( $active_theme->get('Name') != 'Sweetdate' && $active_theme->get('Template') !=  'Sweetdate' && $active_theme->get('Template') != 'sweetdate' ) {

	function gmw_sd_sw_deactivated_admin_notice() {
		?>
    <div class="error">
    	<p><?php _e( 'Sweet-date Geolocation add-on requires Sweet-date theme to be installed and activated.', 'GMW' ); ?></p>
    </div>  
    <?php
	}
    return add_action( 'admin_notices', 'gmw_sd_sw_deactivated_admin_notice' );
}

//make sure BuddyPress is activated
if ( !class_exists( 'BuddyPress' ) ) {

	function gmw_sd_bp_deactivated_admin_notice() {
		?>
    <div class="error">
    	<p><?php _e( 'Sweet-date Geolocation add-on requires BuddyPress plugin version 2.0 or higher in order to work.', 'GMW' ); ?></p>
    </div>  
    <?php
	}
    return add_action( 'admin_notices', 'gmw_sd_bp_deactivated_admin_notice' );
}

function gmw_sweetdate_geolocation_init() {
	
	define( 'GMW_SD_PATH', GMW_PATH . '/plugins/sweetdate-geolocation/' );
	define( 'GMW_SD_URL', GMW_URL . '/plugins/sweetdate-geolocation/' );
		
	//admin settings
	if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
		include( 'includes/admin/geo-my-wp-sd-admin.php' );
		new GMW_SD_Admin;
	}
	
	//include members query only on members page
	if ( bp_current_component() == 'members' ) {
		include( 'includes/geo-my-wp-sd-class.php' );
		new GMW_SD_Class_Query;
	}
}
add_action( 'bp_init', 'gmw_sweetdate_geolocation_init', 20 );
