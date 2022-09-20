<?php
/**
 * GEO my WP Search Results Template ( NOTE: this template is deprecated and is no longer being supported ).
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
<?php global $members_template; ?>

<div class="gmw-results-wrapper default gmw-fl-default-results-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">

	<?php if ( $gmw_form->has_locations() ) : ?>

		<div class="gmw-results">

			<?php do_action( 'gmw_search_results_start', $gmw ); ?>

			<div id="pag-top" class="pagination">

				<div class="pag-count" id="member-dir-count-top">
					<span><?php gmw_results_message( $gmw ); ?></span>
					<?php do_action( 'gmw_search_results_after_results_message', $gmw ); ?>
				</div>

				<div class="clear"></div>

				<?php gmw_per_page( $gmw ); ?>

				<div class="pagination-links" id="member-dir-pag-top">
					<?php gmw_pagination( $gmw ); ?>
				</div>

			</div>

			<div class="clear"></div>

			<?php gmw_results_map( $gmw ); ?>

			<?php do_action( 'bp_before_directory_members_list' ); ?>

			<ul id="members-list" class="item-list" role="main">

				<?php
				while ( bp_members() ) :
					bp_the_member();
					?>

					<?php $member = $members_template->member; ?>

					<?php do_action( 'gmw_the_object_location', $member, $gmw ); ?>

					<li id="single-member-<?php echo absint( $member->id ); ?>" class="single-member <?php echo esc_attr( $member->location_class ); ?>">

						<?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>

						<div class="item">

							<div class="item-title">

								<div class="gmw-fl-member-count">
									<?php echo absint( $member->location_count ); ?>)
								</div>

								<a href="<?php gmw_search_results_permalink( bp_member_permalink(), $member, $gmw ); ?>">
									<?php gmw_search_results_title( bp_member_name(), $member, $gmw ); ?> 
								</a>

								<?php do_action( 'gmw_search_results_before_distance', $gmw, $member ); ?>

								<?php gmw_search_results_distance( $member, $gmw ); ?>

								<?php if ( function_exists( 'bp_get_member_latest_update' ) && bp_get_member_latest_update() ) : ?>
									<div class="update"><?php bp_member_latest_update(); ?></div>
								<?php endif; ?>

							</div>

							<?php do_action( 'gmw_search_results_before_image', $gmw, $member ); ?>

							<?php gmw_search_results_bp_avatar( $member, $gmw ); ?>

							<div class="item-meta">
								<?php if ( function_exists( 'bp_member_last_active' ) ) { ?>
									<span class="activity">
										<?php bp_member_last_active(); ?>
									</span>
								<?php } ?>
							</div>

							<?php do_action( 'bp_directory_members_item' ); ?>

							<?php do_action( 'gmw_search_results_before_contact_info', $member, $gmw ); ?>

							<?php gmw_search_results_location_meta( $member, $gmw ); ?>

							<?php do_action( 'gmw_search_results_before_hours_of_operation', $member, $gmw ); ?>

							<?php gmw_search_results_hours_of_operation( $member, $gmw ); ?>

							<?php do_action( 'gmw_fl_search_results_member_items', $gmw, $member ); ?>

							<?php
							/**
							 * If you want to show specific profile fields here you can,
							 * but it'll add an extra query for each member in the loop
							 * (only one regardless of the number of fields you show):
							 *
							 * bp_member_profile_data( 'field=the field name' );
							 */
							?>
						</div>

						<div class="action">
							<?php do_action( 'bp_directory_members_actions' ); ?>
						</div>

						<div class="clear"></div>

						<?php do_action( 'gmw_search_results_before_address', $gmw, $member ); ?>

						<?php gmw_search_results_address( $member, $gmw ); ?>

						<?php gmw_search_results_directions_link( $member, $gmw ); ?>

						<?php do_action( 'gmw_search_results_loop_item_end', $gmw, $member ); ?>
					</li>

				<?php endwhile; ?>

			</ul>

			<?php do_action( 'bp_after_directory_members_list' ); ?>

			<?php bp_member_hidden_fields(); ?>

			<div id="pag-bottom" class="pagination">

				<div class="pag-count" id="member-dir-count-top">
					<span><?php gmw_results_message( $gmw ); ?></span>
				</div>

				<div class="clear"></div>

				<?php gmw_per_page( $gmw ); ?>

				<div class="pagination-links" id="member-dir-pag-top">
					<?php gmw_pagination( $gmw ); ?>
				</div>

			</div>

			<?php do_action( 'gmw_search_results_end', $gmw ); ?>	

		</div>

	<?php else : ?>

		<div class="gmw-no-results">

			<?php do_action( 'gmw_no_results_start', $gmw ); ?>

			<?php gmw_no_results_message( $gmw ); ?>

			<?php do_action( 'gmw_no_results_end', $gmw ); ?> 

		</div>

	<?php endif; ?>

</div>
