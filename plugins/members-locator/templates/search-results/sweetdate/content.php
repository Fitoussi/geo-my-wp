<?php
/**
 * Members locator "sweetdate" search results template file.
 *
 * This file outputs the search results.
 *
 * You can modify this file to apply custom changes. However, it is not recomended
 * since your changes will be overwritten on the next update of the plugin.
 *
 * Instead you can copy-paste this template ( the "sweetdate" folder contains this file
 * and the "css" folder ) into the theme's or child theme's folder of your site
 * and apply your changes from there.
 *
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 *
 * Once the template folder is in the theme's folder you will be able to select
 * it in the form editor. It will show in the "Search results" dropdown menu as "Custom: gsweetdate".
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
