<?php
/**
 * Members locator "grid-gray" search results template file.
 *
 * This file outputs the search results.
 *
 * You can modify this file to apply custom changes. However, it is not recomended
 * since your changes will be overwritten on the next update of the plugin.
 *
 * Instead you can copy-paste this template ( the "grid-gray" folder contains this file
 * and the "css" folder ) into the theme's or child theme's folder of your site
 * and apply your changes from there.
 *
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 *
 * Once the template folder is in the theme's folder you will be able to select
 * it in the form editor. It will show in the "Search results" dropdown menu as "Custom: grid-gray".
 *
 * @param $gmw ( array ) the form being used
 *
 * @param $members_template ( object ) buddypress members object
 *
 * @param $members_template->member ( object ) each member in the loop
 *
 * @package geo-my-wp
 */

?>
<?php global $members_template; ?>

<!--  Main results wrapper -->
<div class="gmw-results-wrapper grid-gray gmw-fl-grid-gray-results-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">

	<?php if ( $gmw_form->has_locations() ) : ?>

		<div class="gmw-results">

			<?php do_action( 'gmw_search_results_start', $gmw ); ?>

			<div class="gmw-results-message results-count-wrapper">
				<span><?php gmw_results_message( $gmw ); ?></span>
				<?php do_action( 'gmw_search_results_after_results_message', $gmw ); ?>
			</div>

			<div class="pagination-per-page-wrapper top">
				<?php gmw_per_page( $gmw ); ?>
				<?php gmw_pagination( $gmw ); ?>
			</div>

			<?php gmw_results_map( $gmw ); ?>

			<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>

			<ul id="members-list" class="members-list-wrapper">

				<?php
				while ( bp_members() ) :
					bp_the_member();
					?>

					<?php $member = $members_template->member; ?>

					<li id="single-member-<?php echo absint( $member->id ); ?>" class="single-member <?php echo esc_attr( $member->location_class ); ?>">

						<?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>

						<div class="wrapper-inner">

							<div class="top-wrapper">	

								<?php do_action( 'gmw_search_results_before_title', $gmw, $member ); ?>

								<h2 class="user-name-wrapper">
									<a href="<?php gmw_search_results_permalink( bp_member_permalink(), $member, $gmw ); ?>">
										<?php gmw_search_results_title( bp_member_name(), $member, $gmw ); ?> 
									</a>
								</h2>    	
								<?php gmw_search_results_distance( $member, $gmw ); ?>
							</div>

							<div class="user-info">

								<?php do_action( 'gmw_search_results_before_avatar', $gmw, $member ); ?>

								<?php gmw_search_results_bp_avatar( $member, $gmw ); ?>

								<?php if ( function_exists( 'bp_member_last_active' ) ) { ?>
									<span class="activity">
										<?php bp_member_last_active(); ?>
									</span>
								<?php } ?>

								<?php do_action( 'bp_directory_members_actions' ); ?>

								<?php if ( function_exists( 'bp_get_member_latest_update' ) && bp_get_member_latest_update() ) : ?>
									<div class="update"><?php bp_member_latest_update(); ?></div>
								<?php endif; ?>

								<?php do_action( 'gmw_search_results_before_get_directions', $gmw, $member ); ?>

								<?php gmw_search_results_directions_link( $member, $gmw ); ?>

								<?php do_action( 'bp_directory_members_item' ); ?>

								<?php do_action( 'gmw_search_results_before_contact_info', $member, $gmw ); ?>

								<?php gmw_search_results_location_meta( $member, $gmw ); ?>

								<?php do_action( 'gmw_search_results_before_hours_of_operation', $member, $gmw ); ?>

								<?php gmw_search_results_hours_of_operation( $member, $gmw ); ?>

								<?php do_action( 'gmw_fl_search_results_member_items', $gmw, $member ); ?>
							</div>

							<div class="bottom-wrapper">
								<div class="address-wrapper">
									<?php gmw_search_results_linked_address( $member, $gmw ); ?>
								</div>       
							</div>	
						</div>

						<?php do_action( 'gmw_search_results_loop_item_end', $gmw, $members_template->member ); ?>
					</li>

				<?php endwhile; ?>

			</ul>

			<?php do_action( 'bp_after_directory_members_list' ); ?>

			<?php bp_member_hidden_fields(); ?>

			<div class="pagination-per-page-wrapper bottom">	
				<?php gmw_per_page( $gmw ); ?>	
				<?php gmw_pagination( $gmw ); ?>
			</div>
		</div>

	<?php else : ?>

		<div class="gmw-no-results">

			<?php do_action( 'gmw_no_results_start', $gmw ); ?>

			<?php gmw_no_results_message( $gmw ); ?>

			<?php do_action( 'gmw_no_results_end', $gmw ); ?> 

		</div>

	<?php endif; ?>

</div>
