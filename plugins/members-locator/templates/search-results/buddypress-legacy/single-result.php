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
<li id="gmw-single-member-<?php echo absint( $member->id ); ?>" <?php bp_member_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php absint( $member->id ); ?>" data-bp-item-component="members">

	<?php do_action( 'gmw_search_results_loop_item_start', $member, $gmw ); ?>

	<?php gmw_search_results_distance( $member, $gmw ); ?>

	<div class="item-avatar">
		<a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
	</div>

	<div class="item">

		<div class="item-title">

			<?php gmw_search_results_linked_title( bp_get_member_permalink(), bp_get_member_name(), $member, $gmw ); ?>

			<?php if ( bp_get_member_latest_update() ) : ?>

				<span class="update"> <?php bp_member_latest_update(); ?></span>

			<?php endif; ?>

		</div>

		<div class="item-meta">
			<span class="activity" data-livestamp="<?php bp_core_iso8601_date( bp_get_member_last_active( array( 'relative' => false ) ) ); ?>">
				<?php gmw_search_results_bp_last_active( $member, $gmw ); ?>
			</span>
		</div>

		<?php

		/**
		 * Fires inside the display of a directory member item.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_directory_members_item' ); ?>

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
		 /***
		  * If you want to show specific profile fields here you can,
		  * but it'll add an extra query for each member in the loop
		  * (only one regardless of the number of fields you show):
		  *
		  * bp_member_profile_data( 'field=the field name' );
		  */
		?>
	</div>

	<div class="action">

		<?php

		/**
		 * Fires inside the members action HTML markup to display actions.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_directory_members_actions' ); ?>

	</div>

	<?php do_action( 'gmw_search_results_loop_item_end', $member, $gmw ); ?>

	<div class="clear"></div>
</li>
