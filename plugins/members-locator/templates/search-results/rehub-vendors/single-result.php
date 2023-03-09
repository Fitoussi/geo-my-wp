<?php
/**
 * GEO my WP Search Results Template.
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 *
 * You will then be able to select your custom template from the "Search Results Templates" select dropdown option in the "Search Results" tab of the form editor.
 *
 * It will be named as "Custom: %folder-name%".
 *
 * @param $gmw  ( array ) the form being used
 *
 * @param $gmw_form ( object ) the form object
 *
 * @param $member ( object ) the member's object in the loop
 *
 * @package geo-my-wp
 */

?>

<?php $member = $members_template->member; ?>
<?php $vendor_id = $member->id; ?>
<?php
if ( class_exists( 'WeDevs_Dokan' ) && dokan_is_user_seller( $vendor_id ) ) {

	$store_info = dokan_get_store_info( $vendor_id );
	$shop_link  = dokan_get_store_url( $vendor_id );
	$shop_name  = esc_html( $store_info['store_name'] );

} elseif ( class_exists( 'WCMp' ) && is_user_wcmp_vendor( $vendor_id ) ) {

	$vendorobj = get_wcmp_vendor( $vendor_id );
	$shop_link = $vendorobj->permalink;
	$shop_name = get_user_meta( $vendor_id, '_vendor_page_title', true );

} elseif ( defined( 'WCFMmp_TOKEN' ) && wcfm_is_vendor( $vendor_id ) ) {

	$shop_link = wcfmmp_get_store_url( $vendor_id );
	$shop_name = get_user_meta( $vendor_id, 'store_name', true );

} elseif ( defined( 'wcv_plugin_dir' ) && WCV_Vendors::is_vendor( $vendor_id ) ) {

	$shop_link = WCV_Vendors::get_vendor_shop_page( $vendor_id );
	$shop_name = WCV_Vendors::get_vendor_sold_by( $vendor_id );

} else {

	$shop_link = bp_get_member_permalink();
	$shop_name = $member->display_name;
}
?>

<?php $classes = array( 'col_item', gmw_get_object_class( $member, $gmw ) ); ?>

<div id="gmw-single-member-<?php echo absint( $member->id ); ?>" <?php bp_member_class( $classes ); ?> data-bp-item-id="<?php echo absint( $member->id ); ?>" data-bp-item-component="members">

	<?php do_action( 'gmw_search_results_loop_item_start', $member, $gmw ); ?>

	<div class="gmw-item-inner member-inner-list">

		<div class="vendor-list-like act-rehub-login-popup">
			<?php echo getShopLikeButton( $vendor_id ); ?>
		</div>

		<a href="<?php echo esc_url( $shop_link ); ?>">
			<span class="cover_logo" style="<?php echo rh_show_vendor_bg( $vendor_id ); ?>"></span>
		</a>

		<div class="gmw-item-content member-details">

			<div class="item-avatar">
				<a href="<?php echo esc_url( $shop_link ); ?>">
					<img src="<?php echo rh_show_vendor_avatar( $vendor_id, 80, 80 ); ?>" class="vendor_store_image_single" width=80 height=80 />
				</a>
			</div>

			<a href="<?php echo esc_url( $shop_link ); ?>" class="wcv-grid-shop-name">
				<?php echo esc_attr( $shop_name ); ?>
			</a>
			
			<?php
			if ( class_exists( 'WCVendors_Pro' ) ) {
				if ( ! WCVendors_Pro::get_option( 'ratings_management_cap' ) ) {
					echo '<div class="wcv_grid_rating">';
					echo WCVendors_Pro_Ratings_Controller::ratings_link( $vendor_id, true );
					echo '</div>';
				}
			}
			?>

			<div class="font70 greycolor"><?php bp_member_last_active(); ?></div>

			<!--<div class="adress-vendor-gmw-list">

				<div class="distance-to-user-geo">
					<?php gmw_search_results_distance( $member, $gmw ); ?>
				</div>

				<div class="adress-user-geo">     
					<?php gmw_search_results_address( $member, $gmw ); ?>
					<?php gmw_search_results_directions_link( $member, $gmw ); ?>              
				</div>                        
			</div> -->

			<?php do_action( 'gmw_search_results_loop_content_start', $member, $gmw ); ?>

			<?php gmw_search_results_meta_fields( $member, $gmw ); ?>

			<?php gmw_search_results_location_meta( $member, $gmw ); ?>

			<?php gmw_search_results_member_xprofile_fields( $member, $gmw ); ?>

			<?php gmw_search_results_member_types( $member, $gmw ); ?>

			<?php gmw_search_results_hours_of_operation( $member, $gmw ); ?>

			<?php gmw_search_results_directions_link( $member, $gmw ); ?>

			<?php gmw_search_results_bp_friendship_button( $member, $gmw ); ?>

			<?php do_action( 'gmw_search_results_loop_content_end', $member, $gmw ); ?>
		</div>

		<div class="gmw-item-footer">
			<?php gmw_search_results_address( $member, $gmw ); ?>
		</div>                            

		<?php do_action( 'gmw_search_results_loop_item_end', $member, $gmw ); ?>           
	</div>
</div>
