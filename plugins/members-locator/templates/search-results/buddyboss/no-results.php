<?php
/**
 * GEO my WP Search Results Template.
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 *
 * You will then be able to select your custom template from the "Search Results Templates" select dropdown option in the "Search Results" tab of the form editor.
 *
 * It will be named as "Custom: %folder-name%".
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
