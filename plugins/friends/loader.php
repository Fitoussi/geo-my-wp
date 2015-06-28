<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

function gmw_members_locator_loader() {
    global $bp;

    include_once( 'includes/gmw-fl-component.php' );
    if ( class_exists( 'GMW_Members_Locator_Component' ) ) {
        $bp->gmw_location = new GMW_Members_Locator_Component;
    }
}
add_action( 'bp_loaded', 'gmw_members_locator_loader', 20 );