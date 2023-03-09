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

// Check if members_list_item has content.
ob_start();

bp_nouveau_member_hook( '', 'members_list_item' );

$members_list_item_content = ob_get_clean();
$member_loop_has_content   = ! empty( $members_list_item_content );

// Get member followers element.
$followers_count = '';

if ( $enabled_followers && function_exists( 'bb_get_followers_count' ) ) {
	ob_start();
	bb_get_followers_count( bp_get_member_user_id() );
	$followers_count = ob_get_clean();
}

// Member joined data.
$member_joined_date = bb_get_member_joined_date( bp_get_member_user_id() );

// Member last activity.
$member_last_activity = bp_get_last_activity( bp_get_member_user_id() );

// Primary and secondary profile action buttons.
$profile_actions = bb_member_directories_get_profile_actions( bp_get_member_user_id() );

// Member switch button.
$member_switch_button = bp_get_add_switch_button( bp_get_member_user_id() );

// Get Primary action.
$primary_action_btn = function_exists( 'bb_get_member_directory_primary_action' ) ? bb_get_member_directory_primary_action() : '';
?>

<li id="gmw-single-member-<?php echo esc_attr( $member->id ); ?>" <?php bp_member_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php bp_member_user_id(); ?>" data-bp-item-component="members">
	<div class="list-wrap
	<?php
		echo esc_attr( $footer_buttons_class ) .
			esc_attr( $follow_class ) .
			esc_attr( true === $member_loop_has_content ? ' has_hook_content' : '' ) .
			esc_attr( ! empty( $profile_actions['secondary'] ) ? ' secondary-buttons' : ' no-secondary-buttons' ) .
			esc_attr( ! empty( $primary_action_btn ) ? ' primary-button' : ' no-primary-buttons' );
	?>
	">

		<div class="list-wrap-inner">

			<?php if ( ! empty( $gmw['search_results']['image']['enabled'] ) ) { ?>

				<div class="item-avatar">
					<a href="<?php bp_member_permalink(); ?>">
						<?php
						if ( $enabled_online_status && function_exists( 'bb_current_user_status' ) ) {
							bb_current_user_status( bp_get_member_user_id() );
						}
						bp_member_avatar( bp_nouveau_avatar_args() );
						?>
					</a>
				</div>

			<?php } ?>

			<div class="item">

				<div class="item-block">

					<?php
					if ( $enabled_profile_type && function_exists( 'bp_member_type_enable_disable' ) && true === bp_member_type_enable_disable() && true === bp_member_type_display_on_profile() ) {
						echo '<p class="item-meta member-type only-grid-view">' . wp_kses_post( bp_get_user_member_type( bp_get_member_user_id() ) ) . '</p>';
					}
					?>

					<h2 class="list-title member-name">
						<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
					</h2>

					<?php
					if ( $enabled_profile_type && function_exists( 'bp_member_type_enable_disable' ) && true === bp_member_type_enable_disable() && true === bp_member_type_display_on_profile() ) {
						echo '<p class="item-meta member-type only-list-view">' . wp_kses_post( bp_get_user_member_type( bp_get_member_user_id() ) ) . '</p>';
					}

					if ( ! empty( $gmw['search_results']['last_active'] ) && ( ( $enabled_last_active && $member_last_activity ) || ( $enabled_joined_date && $member_joined_date ) ) ) :

						echo '<p class="item-meta last-activity">';
						if ( $enabled_joined_date ) {
							echo wp_kses_post( $member_joined_date );
						}

						if ( ( $enabled_last_active && $member_last_activity ) && ( $enabled_joined_date && $member_joined_date ) ) {
							echo '<span class="separator">&bull;</span>';
						}

						if ( $enabled_last_active ) {
							echo wp_kses_post( $member_last_activity );
						}
						echo '</p>';
					endif;
					?>

					<?php gmw_search_results_meta_fields( $member, $gmw ); ?>

					<?php gmw_search_results_location_meta( $member, $gmw ); ?>

					<?php gmw_search_results_member_xprofile_fields( $member, $gmw ); ?>

					<?php gmw_search_results_member_types( $member, $gmw ); ?>

					<?php gmw_search_results_hours_of_operation( $member, $gmw ); ?>

					<?php gmw_search_results_address( $member, $gmw ); ?>

					<?php gmw_search_results_directions_link( $member, $gmw ); ?>

					<?php gmw_search_results_distance( $member, $gmw ); ?>
				</div>

				<div class="flex align-items-center follow-container justify-center">
					<?php echo wp_kses_post( $followers_count ); ?>
				</div>

				<div class="flex only-grid-view align-items-center primary-action justify-center">
					<?php echo wp_kses_post( $profile_actions['primary'] ); ?>
				</div>
			</div><!-- // .item -->

			<?php if ( ! empty( $gmw['search_results']['friendship_button'] ) ) { ?>

				<div class="member-buttons-wrap">

					<?php if ( $profile_actions['secondary'] ) { ?>
						<div class="flex only-grid-view button-wrap member-button-wrap footer-button-wrap">
							<?php echo wp_kses_post( $profile_actions['secondary'] ); ?>
						</div>
					<?php } ?>

					<?php if ( $profile_actions['primary'] ) { ?>
						<div class="flex only-list-view align-items-center primary-action justify-center">
							<?php echo wp_kses_post( $profile_actions['primary'] ); ?>
						</div>
					<?php } ?>

				</div><!-- .member-buttons-wrap -->
			<?php } ?>

		</div>

		<div class="bp-members-list-hook">
			<?php if ( $member_loop_has_content ) { ?>
				<a class="more-action-button" href="#"><i class="bb-icon-menu-dots-h"></i></a>
			<?php } ?>
			<div class="bp-members-list-hook-inner">
				<?php bp_nouveau_member_hook( '', 'members_list_item' ); ?>
			</div>
		</div>

		<?php if ( ! empty( $member_switch_button ) ) { ?>
		<div class="bb_more_options member-dropdown">
			<a href="#" class="bb_more_options_action bp-tooltip" data-bp-tooltip-pos="up" data-bp-tooltip="<?php esc_html_e( 'More Options', 'buddyboss' ); ?>">
				<i class="bb-icon-menu-dots-h"></i>
			</a>
			<div class="bb_more_options_list">
				<?php echo wp_kses_post( $member_switch_button ); ?>
			</div>
		</div><!-- .bb_more_options -->
		<?php } ?>
	</div>
</li>
