<?php
/**
 * GEO my WP Info-Window Template. 
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/info-window/popup/
 *
 * You will then be able to select your custom template from the "Templates" select dropdown option in the "Info Window" tab of the form editor.
 *
 * It will be named as "Custom: %folder-name%".
 *
 * @param $gmw  ( array )  GEO my WP's form.
 *
 * @param $post ( object ) the post's object.
 *
 * @author Eyal Fitoussi
 *
 * @package gmw-my-wp
 */

?>
<?php do_action( 'gmw_info_window_before', $post, $gmw ); ?> 

<div class="buttons-wrapper">
	<?php gmw_element_dragging_handle( array( 'handle' => '.buttons-wrapper' ) ); ?>
	<?php gmw_element_toggle_button(); ?>
	<?php gmw_info_window_distance( $post, $gmw ); ?>
	<?php gmw_element_close_button( 'gmw-icon-cancel-circled' ); ?>
</div>

<div class="gmw-info-window-inner">

	<?php do_action( 'gmw_info_window_start', $post, $gmw ); ?>

	<div class="gmw-item-header">
		<?php gmw_info_window_featured_image( $post, $gmw ); ?>	
	</div>

	<div class="gmw-item-content">

		<h3 class="gmw-item gmw-item-title">
			<?php gmw_info_window_linked_title( get_permalink( $post->ID ), $post->post_title, $post, $gmw ); ?>
			<?php gmw_info_window_address( $post, $gmw ); ?>
		</h3>

		<?php do_action( 'gmw_info_window_content_start', $post, $gmw ); ?>

		<?php gmw_info_window_post_excerpt( $post, $gmw ); ?>

		<?php gmw_search_results_meta_fields( $post, $gmw, 'info_window' ); ?>

		<?php gmw_info_window_location_meta( $post, $gmw ); ?>

		<?php gmw_info_window_hours_of_operation( $post, $gmw ); ?>

		<?php gmw_search_results_taxonomies( $post, $gmw, 'info_window' ); ?>

		<?php gmw_info_window_directions_link( $post, $gmw ); ?>

		<?php do_action( 'gmw_info_window_content_end', $post, $gmw ); ?>         
	</div>

	<?php do_action( 'gmw_info_window_end', $post, $gmw ); ?>            	    
</div>

<?php do_action( 'gmw_info_window_after', $post, $gmw ); ?>
