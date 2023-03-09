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
<li id="gmw-single-member-<?php echo absint( $member->id ); ?>" <?php bp_member_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php echo absint( $member->id ) ?>" data-bp-item-component="members">

	<div class="list-wrap">

		<?php do_action( 'gmw_search_results_loop_item_start', $member, $gmw ); ?>

		<?php gmw_search_results_distance( $member, $gmw ); ?>

		<div class="item-avatar">
			<a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar( bp_nouveau_avatar_args() ); ?></a>
		</div>

		<div class="item">

			<div class="item-block">

				<h2 class="list-title member-name">
					<?php gmw_search_results_linked_title( bp_get_member_permalink(), bp_get_member_name(), $member, $gmw ); ?>
				</h2>

				<?php if ( bp_nouveau_member_has_meta() ) : ?>
					<p class="item-meta last-activity">
						<?php bp_nouveau_member_meta(); ?>
					</p><!-- .item-meta -->
				<?php endif; ?>

				<?php if ( function_exists( 'bp_nouveau_member_has_extra_content' ) && bp_nouveau_member_has_extra_content() ) : ?>
					<div class="item-extra-content">
						<?php bp_nouveau_member_extra_content() ; ?>
					</div><!-- .item-extra-content -->
				<?php endif ; ?>

				<div class="gmw-location-items-wrapper">

					<?php do_action( 'gmw_search_results_loop_content_start', $member, $gmw ); ?>

					<?php gmw_search_results_address( $member, $gmw ); ?>

					<?php gmw_search_results_meta_fields( $member, $gmw ); ?>

					<?php gmw_search_results_location_meta( $member, $gmw ); ?>

					<?php gmw_search_results_member_xprofile_fields( $member, $gmw ); ?>

					<?php gmw_search_results_member_types( $member, $gmw ); ?>

					<?php gmw_search_results_hours_of_operation( $member, $gmw ); ?>

					<?php gmw_search_results_directions_link( $member, $gmw ); ?>

					<?php do_action( 'gmw_search_results_loop_content_end', $member, $gmw ); ?>
				</div>

				<?php 
				if ( ! empty( $gmw['search_results']['friendship_button'] ) ) {
					bp_nouveau_members_loop_buttons(
						array(
							'container'      => 'ul',
							'button_element' => 'button',
						)
					);
				}
				?>
			</div>

			<?php if ( bp_get_member_latest_update() && ! bp_nouveau_loop_is_grid() ) : ?>
				<div class="user-update">
					<p class="update"> <?php bp_member_latest_update(); ?></p>
				</div>
			<?php endif; ?>

		</div><!-- // .item -->

		<?php do_action( 'gmw_search_results_loop_item_end', $member, $gmw ); ?>

	</div>
</li>
