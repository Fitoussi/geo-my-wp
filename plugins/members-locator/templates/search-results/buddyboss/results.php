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
<div id="buddypress" class="gmw-results buddypress-wrap bp-dir-hori-nav">
<?php
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since BuddyPress 2.3.0
	 */
	do_action( 'bp_before_directory_members_page' );
?>

	<div class="members-directory-wrapper">

		<?php
			/**
			 * Fires before the display of the members.
			 *
			 * @since BuddyPress 1.1.0
			 */
			do_action( 'bp_before_directory_members' );
		?>

		<div class="members-directory-container">

			<?php
				/**
				 * Fires before the display of the members content.
				 *
				 * @since BuddyPress 1.1.0
				 */
				do_action( 'bp_before_directory_members_content' );
			?>

			<div class="screen-content members-directory-content">

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

				<div id="members-dir-list" class="members dir-list" data-bp-list="member">

					<?php
					bp_nouveau_before_loop();

					$footer_buttons_class = ( bp_is_active( 'friends' ) && bp_is_active( 'messages' ) ) ? ' footer-buttons-on' : '';
					$is_follow_active     = bp_is_active( 'activity' ) && function_exists( 'bp_is_activity_follow_active' ) && bp_is_activity_follow_active();
					$follow_class         = $is_follow_active ? ' follow-active' : '';

					// Member directories elements.
					$enabled_online_status = ! function_exists( 'bb_enabled_member_directory_element' ) || bb_enabled_member_directory_element( 'online-status' );
					$enabled_profile_type  = ! function_exists( 'bb_enabled_member_directory_element' ) || bb_enabled_member_directory_element( 'profile-type' );
					$enabled_followers     = ! function_exists( 'bb_enabled_member_directory_element' ) || bb_enabled_member_directory_element( 'followers' );
					$enabled_last_active   = ! function_exists( 'bb_enabled_member_directory_element' ) || bb_enabled_member_directory_element( 'last-active' );
					$enabled_joined_date   = ! function_exists( 'bb_enabled_member_directory_element' ) || bb_enabled_member_directory_element( 'joined-date' );
					$gmw_view              = gmw_get_current_results_view( $gmw );
					?>

					<ul id="members-list" class="gmw-results-list members-list-wrapper item-list members-list bp-list <?php echo esc_attr( $gmw_view ); ?>">

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

					<div class="gmw-pagination-message-wrapper">
						<span class="gmw-results-message"><?php gmw_results_message( $gmw ); ?></span><?php gmw_pagination( $gmw ); ?>
					</div>

					<?php bp_nouveau_after_loop(); ?>

				</div>

				<?php do_action( 'gmw_search_results_start', $gmw ); ?>


				<?php
				/**
				 * Fires and displays the members content.
				 *
				 * @since BuddyPress 1.1.0
				 */
				do_action( 'bp_directory_members_content' );
				?>
			</div><!-- // .screen-content -->

			<?php
				/**
				 * Fires after the display of the members content.
				 *
				 * @since BuddyPress 1.1.0
				 */
				do_action( 'bp_after_directory_members_content' );
			?>

		</div>

		<?php
			/**
			 * Fires after the display of the members.
			 *
			 * @since BuddyPress 1.1.0
			 */
			//do_action( 'bp_after_directory_members' );
		?>

	</div>

	<?php
	/**
	 * Fires at the bottom of the members directory template file.
	 *
	 * @since BuddyPress 1.5.0
	 */
	do_action( 'bp_after_directory_members_page' );
	?>
</div>
