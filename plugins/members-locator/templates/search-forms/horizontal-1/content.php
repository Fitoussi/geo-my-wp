<?php
/**
 * This is the search form template file.
 *
 * To modify this template file copy-paste this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-forms/
 *
 * You will then be able to select your custom template from the form editor, under the "Search Form Templates" dropdown menu.
 *
 * It will be labed with "Custom: %folder-name%".
 *
 * @param $gmw_form ( object ) the entire form object.
 *
 * @param $gmw      ( array )  the form settings and values only.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

?>
<?php do_action( 'gmw_before_search_form_template', $gmw ); ?>

<div class="<?php echo esc_attr( $gmw_form->get_class_attr( 'form_wrap' ) ); ?>">

	<?php do_action( 'gmw_before_search_form', $gmw ); ?>

	<form class="gmw-form" name="gmw_form" action="<?php echo esc_attr( $gmw_form->get_results_page() ); ?>" method="get" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">

		<?php do_action( 'gmw_search_form_start', $gmw ); ?>

		<div class="gmw-main-filters-wrapper">

			<?php gmw_search_form_keywords_field( $gmw ); ?>

			<?php gmw_search_form_address_field( $gmw ); ?>

			<?php gmw_search_form_toggle_button( $gmw ); ?>

			<?php gmw_search_form_submit_button( $gmw ); ?>
		</div>

		<div class="gmw-additional-filters-wrapper" data-type="popup">

			<div class="gmw-additional-filters-inner">

				<div class="gmw-additional-filters-header">
					<span class="gmw-close-filters-button gmw-icon-cancel-circled"></span>
					<span class="gmw-additional-filters-title"><?php esc_attr_e( 'More Filters', 'geo-my-wp' ); ?></span>
				</div>

				<div class="gmw-additional-filters-content gmw-grid-filters-wrapper">

					<?php gmw_search_form_locator_button( $gmw ); ?>

					<?php gmw_search_form_radius( $gmw ); ?>
				
					<?php gmw_search_form_units( $gmw ); ?>

					<?php gmw_search_form_bp_member_types_field( $gmw ); ?>

					<?php gmw_search_form_bp_groups_field( $gmw ); ?>

					<?php gmw_search_form_xprofile_fields( $gmw ); ?>

					<?php gmw_search_form_custom_fields( $gmw ); ?>

					<?php do_action( 'gmw_search_form_filters', $gmw ); ?>

					<?php gmw_search_form_reset_button( $gmw ); ?>
				</div>
			</div>

		</div>

		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
	
	<?php do_action( 'gmw_after_search_form', $gmw ); ?>

</div>	

<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>
