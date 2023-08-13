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
<?php $classes = array( 'col_item', gmw_get_object_class( $member, $gmw ) ); ?>

<div id="gmw-single-member-<?php echo absint( $member->id ); ?>" <?php bp_member_class( $classes ); ?> data-bp-item-id="<?php echo absint( $member->id ); ?>" data-bp-item-component="members">

	<div class="gmw-item-inner" style="<?php rh_cover_image_url( 'members', 120, true ); ?>">

	    <?php do_action( 'gmw_search_results_loop_item_start', $member, $gmw ); ?>

        <?php
            $membertype        = bp_get_member_type( $member->id );
            $membertype_object = bp_get_member_type_object( $membertype );
            $membertype_label  = ( !empty( $membertype_object ) && is_object( $membertype_object ) ) ? $membertype_object->labels['singular_name'] : '';
        ?>
        <?php if( $membertype_label ) { ?>
            <span class="rh-user-m-type rh-user-m-type-<?php echo ''.$membertype;?>"><?php echo ''.$membertype_label;?></span>
        <?php } ?>

        <div class="gmw-item-header">
            <?php gmw_search_results_bp_avatar( $member, $gmw ); ?>
        </div>

        <div class="gmw-item-content">

        	<?php gmw_search_results_distance( $member, $gmw ); ?>

            <h3 class="item-title gmw-item gmw-item-title">
                <a href="<?php bp_member_permalink(); ?>">
                    <?php the_author_meta( 'display_name', $member->id ); ?>
                </a>
                <?php gmw_search_results_bp_last_active( $member, $gmw ); ?>
            </h3>

            <?php echo rh_bp_show_vendor_in_loop( $member->id ); ?>

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
