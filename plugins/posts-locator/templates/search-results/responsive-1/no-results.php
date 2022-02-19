<?php
/**
 * GEO my WP No Results template.
 *
 * @param $gmw  ( array ) the form being used
 *
 * @param $gmw_form ( object ) the form object
 *
 * @package geo-my-wp
 */

?>
<div class="gmw-no-results">
	<?php do_action( 'gmw_no_results_start', $gmw ); ?>

	<?php gmw_no_results_message( $gmw ); ?>

	<?php do_action( 'gmw_no_results_end', $gmw ); ?> 
</div>
