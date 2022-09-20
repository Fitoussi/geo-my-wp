<?php
/**
 * GEO my WP Search Results Template.
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/search-results/
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
<!--  Main results wrapper -->
<div class="post-grid bb-grid <?php gmw_form_class( 'results_wrapper', $gmw ); ?>" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">
	<?php
	do_action( 'gmw_search_results_wrapper_start', $gmw );

	if ( $gmw_form->has_locations() ) {

		include 'results.php';

	} else {
		include 'no-results.php';
	}

	do_action( 'gmw_search_results_wrapper_start', $gmw );
	?>
</div>
