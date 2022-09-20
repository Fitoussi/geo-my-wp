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
<div id="members-dir-list" class="gmw-results members dir-list">

	<?php do_action( 'gmw_search_results_start', $gmw ); ?>

	<div class="gmw-results-message">
		<span><?php gmw_results_message( $gmw ); ?></span>
	</div>

	<?php gmw_results_map( $gmw ); ?>

	<div class="gmw-results-filters gmw-flexed-wrapper">

		<?php gmw_per_page( $gmw ); ?>

		<?php do_shortcode( 'gmw_search_results_filters', $gmw ); ?>

		<?php gmw_search_results_orderby_filter( $gmw ); ?>

		<?php gmw_results_view_toggle( $gmw ); ?>
	</div> 

	<?php bp_nouveau_before_loop(); ?>

	<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>

	<?php bp_nouveau_pagination( 'top' ); ?>

	<ul id="members-list" class="gmw-bp-results-list <?php bp_nouveau_loop_classes(); ?>">

		<?php
		while ( bp_members() ) {

			bp_the_member();

			$member = $members_template->member;

			// This action is required. Do not remove.
			do_action( 'gmw_the_object_location', $member, $gmw );

			if ( empty( $gmw['search_results']['styles']['disable_single_item_template'] ) ) {
				include 'single-result.php';
			} else {
				do_action( 'gmw_search_results_single_item_template', $member, $gmw );
			}
		}
		?>
	</ul>

	<?php bp_nouveau_after_loop(); ?>

	<?php do_action( 'gmw_search_results_after_loop', $gmw ); ?>

	<?php bp_nouveau_pagination( 'bottom' ); ?>

	<?php do_action( 'gmw_search_results_end', $gmw ); ?>

</div>
