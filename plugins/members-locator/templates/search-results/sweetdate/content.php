<?php
/**
 * This is the search results template files.
 *
 * To modify this template file copy-paste this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 *
 * You will then be able to select your custom template from the "Search Results Templates" select dropdown option in the "Search Results" tab of the form editor.
 *
 * It will be labed as "Custom: %folder-name%".
 *
 * @param $gmw  ( array ) the form being used
 *
 * @param $gmw_form ( object ) the form object
 *
 * @param $member ( object ) member object in the loop
 *
 * @package geo-my-wp
 */

?>
<?php global $members_template; ?>

<div class="gmw-results-wrapper sweetdate gmw-fl-sweetdate-results-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">

	<?php if ( $gmw_form->has_locations() ) : ?>

		<div class="gmw-results">

			<?php do_action( 'gmw_search_results_start', $gmw ); ?>

			<div class="results-count-wrapper">
				<span><?php gmw_results_message( $gmw ); ?></span>
				<?php do_action( 'gmw_search_results_after_results_message', $gmw ); ?>
			</div>

			<div class="pagination-per-page-wrapper top">
				<?php gmw_per_page( $gmw ); ?>

				<div class="pagination-wrapper">
					<?php gmw_pagination( $gmw ); ?>
				</div>
			</div>

			<?php gmw_results_map( $gmw ); ?>

			<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>

			<?php do_action( 'bp_after_directory_members_list' ); ?>

			<div class="item-list search-list" id="members-list">

				<!-- members loop -->
				<?php
				while ( bp_members() ) :
					bp_the_member();
					?>

					<!-- do not remove this line -->
					<?php $member = $members_template->member; ?>

					<?php do_action( 'gmw_the_object_location', $member, $gmw ); ?>

					<!-- do not remove this line -->
					<?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>

					<div class="four columns">

						<div class="search-item">

							<div class="avatar">

								<a href="<?php gmw_search_results_permalink( bp_member_permalink(), $member, $gmw ); ?>"><?php bp_member_avatar( 'type=full&width=94&height=94&class=' ); ?></a>

								<?php do_action( 'bp_members_inside_avatar' ); ?>

								<span class="radius">
									<?php gmw_search_results_distance( $member, $gmw ); ?>
								</span>

							</div>

							<?php do_action( 'bp_members_meta' ); ?>

							<div class="search-body">
								<?php do_action( 'bp_directory_members_item' ); ?>
							</div>

							<div class="location-wrapper">

								<?php do_action( 'gmw_search_results_before_address', $gmw, $member ); ?>

								<div class="address-wrapper">
									<?php gmw_search_results_linked_address( $member, $gmw ); ?>
								</div>

								<?php do_action( 'gmw_search_results_before_get_directions', $gmw, $member ); ?>

								<?php gmw_search_results_directions_link( $member, $gmw ); ?>

							</div>           

							<div class="bp-member-dir-buttons">
								<?php do_action( 'bp_directory_members_item_last' ); ?>
							</div>

						</div>

					</div>

				<?php endwhile; ?>

			</div>

			<?php do_action( 'bp_after_directory_members_list' ); ?>

			<?php bp_member_hidden_fields(); ?>

			<div class="pagination-per-page-wrapper bottom">	
				<?php gmw_per_page( $gmw ); ?>	

				<div class="pagination-wrapper">
					<?php gmw_pagination( $gmw ); ?>
				</div>
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
