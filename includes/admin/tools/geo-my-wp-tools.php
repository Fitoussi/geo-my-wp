<?php
/**
 * GMW main Tools page
 * 
 * @since 2.5
 * @Author Eyal Fitoussi
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit; 

//include tab files
//include_once ( 'gmw-tools-general-tab.php' );
include_once ( 'gmw-tools-import-export-tab.php');
include_once ( 'gmw-tools-reset-tab.php' );

/**
 * GMW Tools page function - Display tools page tabs
 * 
 * @since 2.5
 * @author Original function written by Pippin Williamson and modified for the needs of GEO my WP
 * 
 */
function gmw_tools_page_output() {
    	
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'import_export';
	?>
    	<div class="wrap">
    		
            <h2 class="gmw-wrap-top-h2">
                <i class="fa fa-wrench"></i>
                <?php echo _e('Tools', 'GMW'); ?>
                <?php gmw_admin_support_button(); ?>
            </h2>

            <div class="clear"></div>

    		<h2 class="nav-tab-wrapper" style=margin-bottom:20px;">
    			<?php
    			foreach( gmw_get_tools_tabs() as $tab_id => $tab_name ) {

    				$tab_url = admin_url( 'admin.php?page=gmw-tools&tab='.$tab_id );
    							
    				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
    				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
    			}
    			?>
    		</h2>
    		<div class="gmw-tools metabox-holder">
    			<?php do_action( 'gmw_tools_tab_' . $active_tab ); ?>
    		</div><!-- .metabox-holder -->
    	</div><!-- .wrap -->
	<?php
}
       
/**
 * Retrieve tools tabs
 *
 * @since       2.5
 * @return      array
 * 
 */
function gmw_get_tools_tabs() {

	$tabs                      		= array();
	//$tabs['general']       	   		= __( 'General', 'GMW' );
	$tabs['import_export']			= __( 'Import/Export', 'GMW' );
	$tabs['reset_gmw']       	   	= __( 'Reset GEO my WP', 'GMW' );
	 
	return apply_filters( 'gmw_tools_tabs', $tabs );
}