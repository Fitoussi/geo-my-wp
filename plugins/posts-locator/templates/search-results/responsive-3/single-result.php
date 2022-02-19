<?php
/**
 * GEO my WP Single Item template.
 *
 * @param $gmw  ( array ) the form being used
 *
 * @param $gmw_form ( object ) the form object
 *
 * @param $post ( object ) the post's object in the loop
 *
 * @package geo-my-wp
 */

?>
<div id="single-post-<?php echo absint( $post->ID ); ?>" class="<?php echo esc_attr( $post->location_class ); ?>">

	<div class="gmw-item-inner">

		<?php do_action( 'gmw_search_results_loop_item_start', $post, $gmw ); ?>

		<div class="gmw-item-header">

			<?php gmw_search_results_distance( $post, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_before_image', $post, $gmw ); ?>

			<?php gmw_search_results_featured_image( $post, $gmw ); ?>

		</div>

		<div class="gmw-item-content">
		
			<h3 class="gmw-item gmw-item-title">
				<?php gmw_search_results_linked_title( get_permalink(), get_the_title(), $post, $gmw ); ?>
			</h3>

			<div class="gmw-item gmw-item-address"><?php gmw_search_results_linked_address( $post, $gmw ); ?></div>
			
			<?php do_action( 'gmw_search_results_loop_content_start', $post, $gmw ); ?>

			<?php gmw_search_results_post_excerpt( $post, $gmw ); ?>

			<?php gmw_search_results_location_meta( $post, $gmw ); ?>

			<?php gmw_search_results_hours_of_operation( $post, $gmw ); ?>

			<?php gmw_search_results_taxonomies( $post, $gmw ); ?>

			<?php gmw_search_results_directions_link( $post, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_content_end', $post, $gmw ); ?>
		</div>

		<?php do_action( 'gmw_search_results_loop_item_end', $post, $gmw ); ?>
	</div>

</div>
