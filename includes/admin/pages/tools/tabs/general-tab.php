<?php
/**
 * admin tools "General" tab
 *
 * @since  2.5
 *
 * @author Eyal Fitoussi
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * General tab output
 *
 * @access public
 *
 * @since 2.5
 *
 * @author Eyal Fitoussi
 */
function gmw_output_general_tab() {
	?>
	<div id="gmw-general-tab-content" class="gmw-tools-tab-content">

		<?php do_action( 'gmw_general_tab_top' ); ?>

		<?php do_action( 'gmw_general_tab_bottom' ); ?>

	</div>
	<?php
}
add_action( 'gmw_tools_tab_general', 'gmw_output_general_tab' );
