<?php
/**
 * Popup "default" info-window template file .
 *
 * The content of this file will be displayed in the map markers info-window.
 *
 * You can modify this file to apply custom changes. However, it is not recomended
 * to make the changes directly in this file,
 * because your changes will be overwritten with the next update of the plugin.
 *
 * Instead, you can copy or move this template ( the folder contains this file
 * and the "css" folder ) into the theme's or child theme's folder of your site,
 * and apply your changes from there.
 *
 * The custom template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/global-maps/info-window/popup/
 *
 * Once the template folder is in the theme's folder, you will be able to select
 * it in the form editor.
 *
 * $gmw  - the form being used ( array )
 *
 * $post - the post being displayed ( object )
 *
 * @package gmw-global-maps.
 */

?>
<?php do_action( 'gmw_iw_popup_template_before', $post, $gmw ); ?>  

<div class="buttons-wrapper">
	<?php gmw_element_dragging_handle(); ?>
	<?php gmw_element_toggle_button(); ?>
	<?php gmw_element_close_button( 'gmw-icon-cancel' ); ?>
</div>

<div class="gmw-info-window-inner popup gmw-pt-iw-template-inner">

	<?php do_action( 'gmw_info_window_start', $post, $gmw ); ?>

	<?php gmw_info_window_featured_image( $post, $gmw ); ?>	

	<a href="<?php gmw_info_window_permalink( get_permalink(), $post, $gmw ); ?>">
		<?php gmw_info_window_title( get_the_title(), $post, $gmw ); ?>
	</a>

	<?php do_action( 'gmw_info_window_before_address', $post, $gmw ); ?>

	<?php gmw_info_window_address( $post, $gmw ); ?>

	<?php gmw_info_window_directions_link( $post, $gmw ); ?>

	<?php gmw_info_window_distance( $post, $gmw ); ?>

	<?php do_action( 'gmw_info_window_before_excerpt', $post, $gmw ); ?>

	<?php gmw_info_window_post_excerpt( $post, $gmw ); ?>

	<?php do_action( 'gmw_info_window_before_location_meta', $post, $gmw ); ?>

	<?php gmw_info_window_location_meta( $post, $gmw, false ); ?>

	<?php gmw_info_window_directions_system( $post, $gmw ); ?>

	<?php do_action( 'gmw_info_window_end', $post, $gmw ); ?>	

</div>  
<?php do_action( 'gmw_info_window_after', $post, $gmw ); ?>
