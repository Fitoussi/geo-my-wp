<div class="gmw-no-results-wrapper gmw-pt-no-results-wrapper <?php echo $class; ?>">
	
	<?php do_action( 'gmw_no_results_template_start', $gmw, $message ); ?>

	<p><?php echo esc_attr( $message ); ?></p>
	
	<?php do_action( 'gmw_no_results_template_end', $gmw, $message ); ?> 
</div>

