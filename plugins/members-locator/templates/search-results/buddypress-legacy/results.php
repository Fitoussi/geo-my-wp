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

	<div id="pag-top" class="pagination">

		<div class="pag-count" id="member-dir-count-top">
			<?php bp_members_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="member-dir-pag-top">
			<?php bp_members_pagination_links(); ?>
		</div>
	</div>

	<?php do_action( 'bp_before_directory_members_list' ); ?>

	<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>

	<ul id="members-list" class="gmw-bp-results-list item-list" aria-live="assertive" aria-relevant="all">

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

	<?php do_action( 'bp_after_directory_members_list' ); ?>

	<?php do_action( 'gmw_search_results_after_loop', $gmw ); ?>

	<?php bp_member_hidden_fields(); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="member-dir-count-bottom">
			<?php bp_members_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="member-dir-pag-bottom">
			<?php bp_members_pagination_links(); ?>
		</div>

	</div>

	<?php do_action( 'gmw_search_results_end', $gmw ); ?>

</div>
