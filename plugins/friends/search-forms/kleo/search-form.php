<?php 
/**
 * Members Locator "kleo" search form template file. 
 * 
 * The information on this file will be displayed as the search forms.
 * 
 * The function pass 1 args for you to use:
 * $gmw  - the form being used ( array )
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "kleo" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/friends/search-forms/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: kleo".
 */
?>
<?php do_action( 'gmw_before_search_form_template', $gmw ); ?>

<div id="buddypress">

	<div id="members-dir-search" class="dir-search gmw-form-wrapper gmw-form-wrapper<?php echo $gmw['ID']; ?> gmw-fl-form-wrapper gmw-fl-kleo-form-wrapper">
		
		<?php do_action( 'gmw_before_search_form', $gmw ); ?>
		
		<form id="search-members-form" class="search-members-form gmw-form gmw-form-<?php echo $gmw['ID']; ?>" name="gmw_form" action="<?php echo $gmw['search_results']['results_page']; ?>" method="get">
				
			<?php do_action( 'gmw_search_form_start', $gmw ); ?>
			
			<?php do_action( 'gmw_search_form_before_address', $gmw ); ?>		

			<label for="members_search">

				<!-- Address Field -->
				<?php gmw_search_form_address_field( $gmw, $id='members_search', $class='' ); ?>
					
				<!--  locator icon -->
				<?php gmw_search_form_locator_icon( $gmw ); ?>

			</label>
			
			<div class="xfield-trigger-wrapper">
				<div class="xfield-trigger" onclick="jQuery(this).closest('form').find('.gmw-kleo-advanced-search-wrapper' ).slideToggle();jQuery(this).html(jQuery(this).html() == 'Hide Options' ? 'More Options' : 'Hide Options');">
					More Options
				</div>
			</div>

			<?php gmw_form_submit_fields( $gmw, false ); ?>	

			<div class="gmw-kleo-advanced-search-wrapper">
				
				<!--distance values -->
				<?php gmw_search_form_radius_values( $gmw, $class='' ); ?>
				
				<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
				
				<!--distance units-->
				<?php gmw_search_form_units( $gmw, $class='' ); ?>	
						
				<?php do_action( 'gmw_search_form_before_xprofile', $gmw ); ?>
						            						
				<?php gmw_fl_xprofile_fields( $gmw, $class='' ); ?>
				
			</div>
			
			<?php do_action( 'gmw_search_form_end', $gmw ); ?>
			
		</form>
		
		<?php do_action( 'gmw_after_search_form', $gmw ); ?>
		
	</div><!--form wrapper -->	

</div>
<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>