<?php
/**
 * GEO my WP Search Results Template.
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
<div id="gmw-single-post-<?php echo absint( $post->ID ); ?>" class="<?php gmw_object_class( $post, $gmw ); ?>">

	<div class="gmw-item-inner">

		<?php do_action( 'gmw_search_results_loop_item_start', $post, $gmw ); ?>

		<div class="gmw-item-header">
			<?php gmw_search_results_featured_image( $post, $gmw ); ?>
		</div>

		<div class="gmw-item-content">

			<?php gmw_search_results_distance( $post, $gmw ); ?>

			<h3 class="gmw-item gmw-item-title">
				<?php gmw_search_results_linked_title( get_permalink(), get_the_title(), $post, $gmw ); ?>
			</h3>

			<?php gmw_search_results_address( $post, $gmw ); ?>
			
			<?php do_action( 'gmw_search_results_loop_content_start', $post, $gmw ); ?>

			<?php gmw_search_results_post_excerpt( $post, $gmw ); ?>

			<?php gmw_search_results_meta_fields( $post, $gmw ); ?>

			<?php gmw_search_results_location_meta( $post, $gmw ); ?>

			<?php gmw_search_results_hours_of_operation( $post, $gmw ); ?>

			<?php gmw_search_results_taxonomies( $post, $gmw ); ?>

			<?php gmw_search_results_directions_link( $post, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_content_end', $post, $gmw ); ?>
		</div>

		<?php do_action( 'gmw_search_results_loop_item_end', $post, $gmw ); ?>
	</div>

</div>
