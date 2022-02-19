<?php
/**
 * This is the search results template files.
 *
 * To modify this template file copy-paste this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 *
 * You will then be able to select your custom folder from the form editor, under the "Search results" dropdown menu.
 *
 * It will be labed as "Custom: %folder-name%".
 *
 * @param $gmw  ( array ) the form being used
 *
 * @param $gmw_form ( object ) the form object
 *
 * @param $member ( object ) member object in the loop
 *
 * @package geo-my-wp
 */

?>
<!--  Main results wrapper -->
<div class="<?php echo esc_attr( $gmw_form->get_class_attr( 'results_wrap' ) ); ?>" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">
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
