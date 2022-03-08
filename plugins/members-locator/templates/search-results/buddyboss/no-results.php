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
<div class="bp-feedback bp-messages info">
	<?php do_action( 'gmw_no_results_start', $gmw ); ?>

	<span class="bp-icon" aria-hidden="true"></span>
	<p><?php gmw_no_results_message( $gmw ); ?></p>

	<?php do_action( 'gmw_no_results_end', $gmw ); ?> 
</div>
