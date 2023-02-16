<?php
/**
 * GEO my WP Info-Window Template. 
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/info-window/popup/
 *
 * You will then be able to select your custom template from the "Templates" select dropdown option in the "Info Window" tab of the form editor.
 *
 * It will be named as "Custom: %folder-name%".
 *
 * @param $gmw    ( array )  GEO my WP's form.
 *
 * @param $member ( object ) the memebr's object.
 *
 * @author Eyal Fitoussi
 *
 * @package gmw-my-wp
 */

?>
<?php do_action( 'gmw_info_window_before', $member, $gmw ); ?>  

<div class="buttons-wrapper">
	<span></span>
	<?php gmw_info_window_distance( $member, $gmw ); ?>
	<?php gmw_element_close_button( 'gmw-icon-cancel-circled' ); ?>
</div>

<span class="gmw-element-drawer-toggle gmw-icon-arrow-left" data-direction="left" data-duration="500" data-toggle_open="gmw-icon-arrow-right" data-toggle_close="gmw-icon-arrow-left"></span>

<div class="gmw-info-window-inner popup template-content-wrapper">

	<?php do_action( 'gmw_info_window_start', $member, $gmw ); ?>

	<div class="gmw-item-header">
		<?php gmw_search_results_bp_avatar( $member, $gmw, 'info_window' ); ?>
	</div>

	<div class="gmw-item-content">

		<h3 class="gmw-item gmw-item-title">
			<?php gmw_search_results_linked_title( bp_get_member_permalink(), bp_get_member_name(), $member, $gmw ); ?>
			<?php gmw_search_results_bp_last_active( $member, $gmw, 'info_window' ); ?>
		</h3>

		<?php do_action( 'gmw_info_window_content_start', $member, $gmw ); ?>

		<?php gmw_info_window_address( $member, $gmw ); ?>

		<?php gmw_search_results_meta_fields( $member, $gmw, 'info_window' ); ?>

		<?php gmw_info_window_location_meta( $member, $gmw ); ?>

		<?php gmw_search_results_member_xprofile_fields( $member, $gmw, 'info_window' ); ?>

		<?php gmw_search_results_member_types( $member, $gmw, 'info_window' ); ?>

		<?php gmw_info_window_hours_of_operation( $member, $gmw ); ?>

		<?php gmw_info_window_directions_link( $member, $gmw ); ?>

		<?php gmw_search_results_bp_friendship_button( $member, $gmw, 'info_window' ); ?>

		<?php do_action( 'gmw_info_window_content_end', $member, $gmw ); ?>
	</div>

	<?php do_action( 'gmw_info_window_end', $member, $gmw ); ?>       	    
</div>  

<?php do_action( 'gmw_info_window_after', $member, $gmw ); ?>
