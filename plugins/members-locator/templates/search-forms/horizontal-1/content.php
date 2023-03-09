<?php
/**
 * GEO my WP Search Form Template. 
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-forms/
 *
 * You will then be able to select your custom template from the "Search Form Templates" select dropdown option in the "Search Form" tab of the form editor.
 *
 * It will be named as "Custom: %folder-name%".
 *
 * @param $gmw_form ( object ) the entire form object.
 *
 * @param $gmw      ( array )  GEO my WP's form.
 *
 * @author Eyal Fitoussi
 *
 * @package gmw-my-wp
 */

?>
<?php do_action( 'gmw_before_search_form_template', $gmw ); ?>

<div class="<?php gmw_form_class( 'form_wrapper', $gmw ); ?>">

	<?php do_action( 'gmw_before_search_form', $gmw ); ?>

	<form class="gmw-form gmw-grid-filters-wrapper" name="gmw_form" action="<?php gmw_form_results_page( $gmw ); ?>" method="get" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">

		<?php do_action( 'gmw_search_form_start', $gmw ); ?>

		<?php gmw_search_form_keywords_field( $gmw ); ?>

		<?php gmw_search_form_address_field( $gmw ); ?>

		<?php gmw_search_form_radius_field( $gmw ); ?>
	
		<?php gmw_search_form_units( $gmw ); ?>

		<?php gmw_search_form_modal_box( 'open', $gmw ); ?>

		<?php do_action( 'gmw_search_form_filters_start', $gmw ); ?>

		<?php gmw_search_form_locator_button( $gmw ); ?>

		<?php gmw_search_form_radius_slider( $gmw ); ?>

		<?php gmw_search_form_bp_member_types_field( $gmw ); ?>

		<?php gmw_search_form_bp_groups_field( $gmw ); ?>

		<?php gmw_search_form_xprofile_fields( $gmw ); ?>

		<?php gmw_search_form_user_role_field( $gmw ); ?>

		<?php gmw_search_form_custom_fields( $gmw ); ?>

		<?php do_action( 'gmw_search_form_filters_end', $gmw ); ?>

		<?php gmw_search_form_modal_box( 'close', $gmw ); ?>

		<?php gmw_search_form_modal_box_toggle( $gmw ); ?>

		<?php gmw_search_form_reset_button( $gmw ); ?>

		<?php gmw_search_form_submit_button( $gmw ); ?>

		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
	
	<?php do_action( 'gmw_after_search_form', $gmw ); ?>

</div>	

<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>
