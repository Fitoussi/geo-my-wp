<?php
/**
 * GEO my WP Results Wrapper template.
 *
 * @param $gmw  ( array ) the form being used
 *
 * @param $gmw_form ( object ) the form object
 *
 * @param $member ( object ) the member's object in the loop
 *
 * @package geo-my-wp
 */

?>
<div class="gmw-results">

	<?php do_action( 'gmw_search_results_start', $gmw ); ?>

	<div class="gmw-results-message">
		<span><?php gmw_results_message( $gmw ); ?></span>
		<?php do_action( 'gmw_search_results_after_results_message', $gmw ); ?>
	</div>

	<?php gmw_results_map( $gmw ); ?>

	<div class="gmw-results-filters gmw-flexed-wrapper">

		<?php gmw_per_page( $gmw ); ?>

		<?php do_shortcode( 'gmw_search_results_filters', $gmw ); ?>

		<?php gmw_results_view_toggle( $gmw ); ?>
	</div> 

	<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>

	<div class="gmw-results-list members-list-wrapper">
		<?php
		while ( bp_members() ) {

			bp_the_member();

			$member = $members_template->member;

			if ( empty( $gmw['search_results']['styles']['disable_single_item_template'] ) ) {
				include 'single-result.php';
			} else {
				do_action( 'gmw_search_results_single_item_template', $member, $gmw );
			}
		}
		?>
	</div>

	<?php do_action( 'gmw_search_results_after_loop', $gmw ); ?>

	<div class="pagination-per-page-wrapper">
		<?php gmw_pagination( $gmw ); ?>
	</div> 

	<?php do_action( 'gmw_search_results_end', $gmw ); ?>

</div>
