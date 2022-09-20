<?php
/**
 * GEO my WP Search Results Template ( NOTE: this template is deprecated and is no longer being supported ).
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/search-results/
 *
 * You will then be able to select your custom template from the "Search Results Templates" select dropdown option in the "Search Results" tab of the form editor.
 *
 * It will be named as "Custom: %folder-name%".
 *
 * @param $gmw  ( array ) the form being used
 *
 * @param $gmw_form ( object ) the form object
 *
 * @param $post ( object ) post object in the loop
 *
 * @package geo-my-wp
 */

?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper custom <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">

	<?php if ( $gmw_form->has_locations() ) : ?>

		<div class="gmw-results">

			<?php do_action( 'gmw_search_results_start', $gmw ); ?>

			<div class="gmw-results-count">
				<span><?php gmw_results_message( $gmw ); ?></span>
				<?php do_action( 'gmw_search_results_after_results_message', $gmw ); ?>
			</div>

			<?php do_action( 'gmw_before_top_pagination', $gmw ); ?>

			<div class="pagination-per-page-wrapper top gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
				<?php gmw_per_page( $gmw ); ?>
				<?php gmw_pagination( $gmw ); ?>
			</div> 

			<?php gmw_results_map( $gmw ); ?>

			<div class="clear"></div>

			<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>

			<ul class="gmw-posts-wrapper">

				<?php
				while ( $gmw_query->have_posts() ) :
					$gmw_query->the_post();
					?>

					<?php global $post; ?>

					<?php do_action( 'gmw_the_object_location', $post, $gmw ); ?>

					<li id="single-post-<?php echo absint( $post->ID ); ?>" class="<?php echo esc_attr( $post->location_class ); ?>">

						<?php do_action( 'gmw_search_results_loop_item_start', $gmw, $post ); ?>

						<h2>
							<a href="<?php gmw_search_results_permalink( get_permalink(), $post, $gmw ); ?>">
								<?php gmw_search_results_title( get_the_title(), $post, $gmw ); ?> 
							</a>
							<?php gmw_search_results_distance( $post, $gmw ); ?>
						</h2>

						<?php do_action( 'gmw_search_results_before_image', $gmw, $post ); ?>

						<?php gmw_search_results_featured_image( $post, $gmw ); ?>

						<?php do_action( 'gmw_search_results_before_excerpt', $gmw, $post ); ?>

						<?php gmw_search_results_post_excerpt( $post, $gmw ); ?>

						<?php gmw_search_results_taxonomies( $post, $gmw ); ?>

						<div class="clear"></div>

						<div class="wppl-info">

							<?php do_action( 'gmw_search_results_before_contact_info', $post, $gmw ); ?>

							<?php gmw_search_results_location_meta( $post, $gmw ); ?>

							<?php do_action( 'gmw_search_results_before_hours_of_operation', $post, $gmw ); ?>

							<?php gmw_search_results_hours_of_operation( $post, $gmw ); ?>

							<div class="wppl-address">
								<?php gmw_search_results_address( $post, $gmw ); ?>
							</div>

							<?php gmw_search_results_directions_link( $post, $gmw ); ?>

						</div> 

						<?php do_action( 'gmw_search_results_loop_item_end', $gmw, $post ); ?>

					</li>

					<div class="clear"></div>     

				<?php endwhile; ?>

			</ul>   

			<?php do_action( 'gmw_search_results_after_loop', $gmw ); ?>

			<div class="pagination-per-page-wrapper bottom gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
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
