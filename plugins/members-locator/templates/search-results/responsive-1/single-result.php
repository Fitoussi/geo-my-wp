<?php
/**
 * GEO my WP Single Item template.
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
<div id="single-member-<?php echo absint( $member->id ); ?>" class="<?php echo esc_attr( $member->location_class ); ?>" data-bp-item-id="<?php echo absint( $member->id ); ?>" data-bp-item-component="members">

	<?php $address = gmw_get_search_results_address( $member, $gmw, true ); ?>

	<div class="gmw-item-inner">

		<?php do_action( 'gmw_search_results_loop_item_start', $member, $gmw ); ?>

		<div class="gmw-item-header">

			<?php gmw_search_results_distance( $member, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_before_image', $member, $gmw ); ?>

			<?php gmw_search_results_bp_avatar( $member, $gmw ); ?>
		</div>

		<div class="gmw-item-content">
		
			<h3 class="gmw-item gmw-item-title">
				<?php gmw_search_results_linked_title( bp_get_member_permalink(), bp_get_member_name(), $member, $gmw ); ?>
			</h3>

			<div class="gmw-item gmw-item-address"><?php echo $address; // WPCS: XSS ok. ?></div>
			
			<?php gmw_search_results_bp_last_active( $member, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_content_start', $member, $gmw ); ?>

			<?php gmw_search_results_location_meta( $member, $gmw ); ?>

			<?php gmw_search_results_hours_of_operation( $member, $gmw ); ?>

			<?php gmw_search_results_directions_link( $member, $gmw ); ?>

			<?php gmw_search_results_bp_friendship_button( $member, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_content_end', $member, $gmw ); ?>
		</div>

		<div class="gmw-item-footer">
			<div class="gmw-item gmw-item-address"><?php echo $address; // WPCS: XSS ok. ?></div>
		</div>

		<?php do_action( 'gmw_search_results_loop_item_end', $member, $gmw ); ?>
	</div>

</div>
