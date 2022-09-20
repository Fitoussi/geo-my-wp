<?php
/**
 * GEO my WP Search Form Template ( NOTE: this template is deprecated and is no longer being supported ).
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/search-forms/
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

<div class="gmw-form-wrapper purple gmw-pt-purple-form-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?>">
	
	<?php do_action( 'gmw_before_search_form', $gmw ); ?>
	
	<form class="gmw-form" name="gmw_form" action="<?php gmw_form_results_page( $gmw ); ?>" method="get" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">
		
		<?php do_action( 'gmw_search_form_start', $gmw ); ?>
		
		<?php gmw_search_form_keywords_field( $gmw ); ?>

		<?php gmw_search_form_address_field( $gmw ); ?>

		<?php gmw_search_form_locator_button( $gmw ); ?>

		<?php do_action( 'gmw_search_form_filters', $gmw ); ?>

		<?php gmw_search_form_post_types( $gmw ); ?>
		
		<?php gmw_search_form_taxonomies( $gmw ); ?>

		<?php gmw_search_form_custom_fields( $gmw ); ?>	
				
		<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>

		<?php gmw_search_form_radius( $gmw ); ?>

		<?php gmw_search_form_units( $gmw ); ?>

		<?php do_action( 'gmw_search_form_before_submit', $gmw ); ?>

		<?php gmw_search_form_reset_button( $gmw ); ?>

		<?php gmw_search_form_submit_button( $gmw ); ?>
		
		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
	
	<?php do_action( 'gmw_after_search_form', $gmw ); ?>
	
</div>

<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>
