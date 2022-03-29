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

$show_message_button = buddyboss_theme()->buddypress_helper()->buddyboss_theme_show_private_message_button( $member->id, bp_loggedin_user_id() );

//Check if members_list_item has content.
ob_start();
bp_nouveau_member_hook( '', 'members_list_item' );
$members_list_item_content = ob_get_contents();
ob_end_clean();

$member_loop_has_content = empty( $members_list_item_content ) ? false : true;
$classes                 = explode( ' ', $member->location_class . ' item-entry' );
?>

<li id="gmw-single-member-<?php echo absint( $member->id ); ?>" <?php bp_member_class( $classes ); ?> data-bp-item-id="<?php bp_member_user_id(); ?>" data-bp-item-component="members">

	<div class="list-wrap <?php echo $footer_buttons_class; ?> <?php echo $follow_class; ?> <?php echo $member_loop_has_content ? ' has_hook_content' : ''; ?>">
	
		<div class="list-wrap-inner">

			<?php if ( ! empty( $gmw['search_results']['image']['enabled'] ) ) { ?>

				<div class="item-avatar">
					<a href="<?php bp_member_permalink(); ?>">
						<?php bb_user_status( bp_get_member_user_id() ); ?>
						<?php bp_member_avatar( bp_nouveau_avatar_args() ); ?>
					</a>
				</div>

			<?php } ?>

			<div class="item">

				<div class="item-block">
					<h2 class="list-title member-name">
						<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
						<?php
						if ( function_exists('bp_member_type_enable_disable') && true === bp_member_type_enable_disable() && true === bp_member_type_display_on_profile() ) {
							echo '<p class="item-meta last-activity">' . bp_get_user_member_type( bp_get_member_user_id() ) . '</p>';
						} else {
							?>
							<?php if ( bp_nouveau_member_has_meta() ) : ?>

								<?php if ( ! empty( $gmw['search_results']['last_active'] ) ) { ?>

									<p class="item-meta last-activity">
										<?php bp_nouveau_member_meta(); ?>
									</p>

								<?php } ?>

							<?php endif; ?>
							<?php
						}
						?>
					</h2>
					<?php gmw_search_results_location_meta( $member, $gmw ); ?>

					<?php gmw_search_results_hours_of_operation( $member, $gmw ); ?>

					<?php gmw_search_results_address( $member, $gmw ); ?>

					<?php gmw_search_results_directions_link( $member, $gmw ); ?>

					<?php gmw_search_results_distance( $member, $gmw ); ?>
				</div>

				<div class="button-wrap member-button-wrap only-list-view">
					<?php buddyboss_theme_followers_count( bp_get_member_user_id() ); ?>

					<?php
					if ( bp_is_active('friends') && ! empty( $gmw['search_results']['friendship_button'] ) ) {
						bp_add_friend_button( bp_get_member_user_id() );
					}

					if ( bp_is_active('messages') ) {
						if ( 'yes' === $show_message_button ) {
							add_filter( 'bp_displayed_user_id', 'buddyboss_theme_member_loop_set_member_id' );
							add_filter( 'bp_is_my_profile', 'buddyboss_theme_member_loop_set_my_profile' );
							bp_send_message_button( $message_button_args );
							remove_filter( 'bp_displayed_user_id', 'buddyboss_theme_member_loop_set_member_id' );
							remove_filter( 'bp_is_my_profile', 'buddyboss_theme_member_loop_set_my_profile' );
						}
					}

					if ( $is_follow_active ) {
						bp_add_follow_button( bp_get_member_user_id(), bp_loggedin_user_id() );
					}
					?>
				</div>

				<?php if ( $is_follow_active ) {
					$justify_class = ( bp_get_member_user_id() == bp_loggedin_user_id() ) ? 'justify-center' : '';
					?>
					<div class="flex only-grid-view align-items-center follow-container <?php echo $justify_class; ?>">
						<?php buddyboss_theme_followers_count( bp_get_member_user_id() ); ?>
						<?php bp_add_follow_button( bp_get_member_user_id(), bp_loggedin_user_id() ); ?>
					</div>
				<?php } ?>

			</div><!-- // .item -->

			<?php if( ! empty( $gmw['search_results']['friendship_button'] ) && bp_is_active('friends') && bp_is_active('messages') && ( bp_get_member_user_id() != bp_loggedin_user_id() ) ) { ?>
				<div class="flex only-grid-view button-wrap member-button-wrap footer-button-wrap"><?php
					bp_add_friend_button( bp_get_member_user_id() );
					if ( 'yes' === $show_message_button ) {
						add_filter( 'bp_displayed_user_id', 'buddyboss_theme_member_loop_set_member_id' );
						add_filter( 'bp_is_my_profile', 'buddyboss_theme_member_loop_set_my_profile' );
						bp_send_message_button( $message_button_args );
						remove_filter( 'bp_displayed_user_id', 'buddyboss_theme_member_loop_set_member_id' );
						remove_filter( 'bp_is_my_profile', 'buddyboss_theme_member_loop_set_my_profile' );
					}
					?></div>
			<?php } ?>

			<?php if( bp_is_active('friends') && ! bp_is_active('messages') && ! empty( $gmw['search_results']['friendship_button'] ) ) { ?>
				<div class="only-grid-view button-wrap member-button-wrap on-top">
					<?php bp_add_friend_button( bp_get_member_user_id() ); ?>
				</div>
			<?php } ?>

			<?php if( ! bp_is_active('friends') && bp_is_active('messages') ) { ?>
				<div class="only-grid-view button-wrap member-button-wrap on-top">
					<?php
					if ( 'yes' === $show_message_button ) {
						add_filter( 'bp_displayed_user_id', 'buddyboss_theme_member_loop_set_member_id' );
						add_filter( 'bp_is_my_profile', 'buddyboss_theme_member_loop_set_my_profile' );
						bp_send_message_button( $message_button_args );
						remove_filter( 'bp_displayed_user_id', 'buddyboss_theme_member_loop_set_member_id' );
						remove_filter( 'bp_is_my_profile', 'buddyboss_theme_member_loop_set_my_profile' );
					}
					?>
				</div>
			<?php } ?>
		</div>

		<div class="bp-members-list-hook">
			<?php 
				if($member_loop_has_content){ ?>
					<a class="more-action-button" href="#"><i class="bb-icon-menu-dots-h"></i></a>
				<?php } ?>
				<div class="bp-members-list-hook-inner">
					<?php bp_nouveau_member_hook( '', 'members_list_item' ); ?>
				</div>
		</div>
	</div>
</li>
