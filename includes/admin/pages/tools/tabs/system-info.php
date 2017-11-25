<?php
/**
 * admin tools "Reset" tab
 * 
 * @since  2.5
 * @author Eyal Fitoussi
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

include( dirname(__FILE__).'/../class-gmw-system-info.php' );

$system_info = new GMW_System_Info();

add_action( 'gmw_tools_system_info_tab', array( $system_info, 'output' ) );
