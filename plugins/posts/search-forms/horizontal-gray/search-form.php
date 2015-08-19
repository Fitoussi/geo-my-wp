<?php 
/**
 * Posts Locator "horizontal-gray" search form template file. 
 * 
 * The information on this file will be displayed as the search forms.
 * 
 * The function pass 1 args for you to use:
 * $gmw  - the form being used ( array )
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "horizontal-gray" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts/search-forms/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Posts locator form.
 * It will show in the "Search results" dropdown menu as "Custom: horizontal-gray".
 */
?>
<?php do_action( 'gmw_before_search_form_template', $gmw ); ?>

<div class="gmw-form-wrapper gmw-form-wrapper<?php echo $gmw['ID']; ?> gmw-pt-form-wrapper gmw-pt-horizontal-gray-form-wrapper">
	
	<?php do_action( 'gmw_before_search_form', $gmw ); ?>
	
	<form class="gmw-form gmw-form-<?php echo $gmw['ID']; ?>" name="gmw_form" action="<?php echo $gmw['search_results']['results_page']; ?>" method="get">
			
		<?php do_action( 'gmw_search_form_start', $gmw ); ?>
				
		<?php do_action( 'gmw_search_form_before_address', $gmw ); ?>
		
		<div class="address-locator-wrapper">
			
			<!-- Address Field -->
			<?php gmw_search_form_address_field( $gmw, $id='', $class='' ); ?>
		
			<!--  locator icon -->
			<?php gmw_search_form_locator_icon( $gmw ); ?>
		</div>

		<?php do_action( 'gmw_search_form_before_post_types', $gmw ); ?>
				
		<!-- post types dropdown -->
		<?php gmw_pt_form_post_types_dropdown( $gmw, false, false, false ); ?>

		<?php do_action( 'gmw_search_form_before_taxonomies', $gmw ); ?>
		
		<!-- Display taxonomies/categories --> 
		<?php gmw_pt_form_taxonomies( $gmw, 'div', false ); ?>
				            		
		<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
		
		<!--distance values -->
		<?php gmw_search_form_radius_values( $gmw, $class='' ); ?>
		
		<!--distance units-->
		<?php gmw_search_form_units( $gmw, $class='' ); ?>	
		
		<?php gmw_form_submit_fields( $gmw, false ); ?>
		
		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
	
	<?php do_action( 'gmw_after_search_form', $gmw ); ?>
	
</div><!--form wrapper -->	

<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>