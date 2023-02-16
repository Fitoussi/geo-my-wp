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
 * @param $member ( object ) the member's object in the loop
 *
 * @package geo-my-wp
 */

?>
<div id="gmw-single-member-<?php echo absint( $member->id ); ?>" class="<?php gmw_object_class( $member, $gmw ); ?>" data-bp-item-id="<?php echo absint( $member->id ); ?>" data-bp-item-component="members">

	<div class="gmw-item-inner">

		<?php do_action( 'gmw_search_results_loop_item_start', $member, $gmw ); ?>

		<div class="gmw-item-header">
			<?php gmw_search_results_bp_avatar( $member, $gmw ); ?>
		</div>

		<div class="gmw-item-content">

			<?php gmw_search_results_distance( $member, $gmw ); ?>

			<h3 class="gmw-item gmw-item-title">
				<?php gmw_search_results_linked_title( bp_get_member_permalink(), bp_get_member_name(), $member, $gmw ); ?>
				<?php gmw_search_results_bp_last_active( $member, $gmw ); ?>
				<?php gmw_search_results_address( $member, $gmw ); ?>
			</h3>

			<?php do_action( 'gmw_search_results_loop_content_start', $member, $gmw ); ?>

			<?php gmw_search_results_meta_fields( $member, $gmw ); ?>

			<?php gmw_search_results_location_meta( $member, $gmw ); ?>

			<?php gmw_search_results_member_xprofile_fields( $member, $gmw ); ?>

			<?php gmw_search_results_member_types( $member, $gmw ); ?>

			<?php gmw_search_results_hours_of_operation( $member, $gmw ); ?>

			<?php gmw_search_results_directions_link( $member, $gmw ); ?>

			<?php gmw_search_results_bp_friendship_button( $member, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_content_end', $member, $gmw ); ?>
		</div>

		<?php do_action( 'gmw_search_results_loop_item_end', $member, $gmw ); ?>
	</div>

</div>
