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
<article id="gmw-single-post-<?php echo absint( $post->ID ); ?>" <?php post_class( explode( ' ', gmw_get_object_class( $post, $gmw ) ) ); ?>>

	<div class="post-inner-wrap">

		<?php gmw_search_results_distance( $post, $gmw ); ?>

		<?php
		if ( ! empty( $gmw['search_results']['image']['enabled'] ) ) {
			buddyboss_theme_entry_header( $post );
		}
		?>

		<div class="entry-content-wrap">

			<header class="entry-header">

				<h2 class="gmw-item gmw-item-title entry-title">
					<?php gmw_search_results_linked_title( get_permalink(), get_the_title(), $post, $gmw ); ?>
					<?php gmw_search_results_address( $post, $gmw ); ?>
				</h2>

				<?php $first_url = buddyboss_theme_get_first_url_content( $post->post_content ); ?>

				<?php if ( has_post_format( 'link' ) && function_exists( 'buddyboss_theme_get_first_url_content' ) && '' !== $first_url ) { ?>
					<p class="post-main-link"><?php echo $first_url; ?></p>
				<?php } ?>

			</header>

			<?php do_action( 'gmw_search_results_loop_content_start', $post, $gmw ); ?>

			<div class="entry-content">
				<?php gmw_search_results_post_excerpt( $post, $gmw ); ?>
			</div>

			<?php gmw_search_results_meta_fields( $post, $gmw ); ?>

			<?php gmw_search_results_location_meta( $post, $gmw ); ?>

			<?php gmw_search_results_hours_of_operation( $post, $gmw ); ?>

			<?php gmw_search_results_taxonomies( $post, $gmw ); ?>

			<?php gmw_search_results_directions_link( $post, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_content_end', $post, $gmw ); ?>

			<?php
			if ( ! has_post_format( 'quote' ) ) {
				get_template_part( 'template-parts/entry-meta' );
			}
			?>
		</div>

	</div>

</article>
