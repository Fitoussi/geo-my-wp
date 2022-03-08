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

				<?php gmw_results_map( $gmw ); ?>

				<div class="gmw-results-filters gmw-flexed-wrapper">

					<?php gmw_per_page( $gmw ); ?>

					<?php do_shortcode( 'gmw_search_results_filters', $gmw ); ?>

					<?php gmw_results_view_toggle( $gmw ); ?>
				</div> 

				<div id="members-dir-list" class="members dir-list" data-bp-list="member">

					<?php bp_nouveau_before_loop(); ?>

					<?php
					$message_button_args = array(
						'link_text'         => '<i class="bb-icon-mail-small"></i>',
						'button_attr' => array(
							'data-balloon-pos' => 'down',
							'data-balloon' => __( 'Message', 'buddyboss-theme' ),
						)
					);

					$footer_buttons_class = ( bp_is_active('friends') && bp_is_active('messages') ) ? 'footer-buttons-on' : '';
					$is_follow_active     = bp_is_active('activity') && function_exists('bp_is_activity_follow_active') && bp_is_activity_follow_active();
					$follow_class         = $is_follow_active ? 'follow-active' : '';
					$gmw_view             = ( strpos( $gmw_form->get_class_attr( 'results_wrap' ), 'gmw-grid-view' ) !== false ) ? 'grid' : 'list';
					?>
					<ul id="members-list" class="gmw-results-list members-list-wrapper item-list members-list bp-list <?php echo $gmw_view; // WPCS: XSS ok. ?>">

						<?php while ( bp_members() ) {

							bp_the_member();

							$member = $members_template->member;

							if ( empty( $gmw['search_results']['styles']['disable_single_item_template'] ) ) {
								include 'single-result.php';
							} else {
								do_action( 'gmw_search_results_single_item_template', $member, $gmw );
							}
						}
						?>
					</ul>

					<div class="pagination-per-page-wrapper">
						<?php gmw_results_message( $gmw ); ?><?php gmw_pagination( $gmw ); ?>
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
			do_action( 'bp_after_directory_members' );
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









