<?php 
/**
 * Members Locator "horizontal-gray" search form template file. 
 * 
 * The information on this file outputs the search form.
 * 
 * You can modify this file to apply custom changes. However, it is not recomended
 * since your changes will be overridden on the next update of the plugin.
 * 
 * Instead you can copy-paste this template ( the "horizontal-gray" folder contains this file 
 * and the "css" folder ) into the theme's or child theme's folder of your site 
 * and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-forms/
 * 
 * Once the template folder is in the theme's folder you will be able to select 
 * it in the form editor. It will show in the "Search form" dropdown menu as "Custom: horizontal-gray".
 *
 * @param $gmw_form ( object ) the entire form object
 * @param $gmw      ( array )  the form settings and values only
 * 
 * @author Eyal Fitoussi
 * 
 */
?>
<?php do_action( 'gmw_before_search_form_template', $gmw ); ?>

<div class="gmw-form-wrapper horizontal-gray gmw-fl-horizontal-gray-form-wrapper <?php echo $gmw['ID']; ?> <?php echo $gmw['prefix']; ?>">
	
	<?php do_action( 'gmw_before_search_form', $gmw ); ?>
	
	<form class="standard-form gmw-form data-form_id="<?php echo $gmw['ID']; ?>" name="gmw_form" action="<?php echo $gmw_form->get_results_page(); ?>" method="get">
			
		<?php do_action( 'gmw_search_form_start', $gmw ); ?>
		
		<?php do_action( 'gmw_search_form_before_address', $gmw ); ?>
				
		<div class="address-locator-wrapper">
			
			<?php gmw_search_form_address_field( $gmw ); ?>
				
			<?php gmw_search_form_locator_button( $gmw ); ?>

		</div>
				
		<?php do_action( 'gmw_search_form_before_xprofile', $gmw ); ?>
		
		<div class="xfield-trigger-wrapper">

			<div class="xfield-trigger" onclick="jQuery(this).closest( 'form' ).find( '.gmw-search-form-xprofile-fields' ).slideToggle(); jQuery(this).html( jQuery(this).html() == 'Hide Options' ? 'Show Options' : 'Hide Options');">
				
				<?php echo $gmw['labels']['search_form']['show_options']; ?>
			
			</div>

		</div>
				            		
		<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
		
		<?php gmw_search_form_radius( $gmw ); ?>

		<?php gmw_search_form_units( $gmw ); ?>	
				
		<?php gmw_fl_xprofile_fields( $gmw ); ?>
		
		<?php gmw_form_submit_fields( $gmw, false ); ?>
		
		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
	
	<?php do_action( 'gmw_after_search_form', $gmw ); ?>
	
</div>

<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>