<?php 
/**
 * Members Locator "yellow" search form template file. 
 * 
 * The information on this file will be displayed as the search forms.
 * 
 * The function pass 1 args for you to use:
 * $gmw  - the form being used ( array )
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "yellow" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/friends/search-forms/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: yellow".
 */
?>
<?php 

if  ( !function_exists('gmw_users_locator_button') ) {
	function gmw_users_locator_button( $button, $gmw, $class ) {
		
		$lSubmit = ( isset( $gmw['search_form']['locator_submit'] ) && $gmw['search_form']['locator_submit'] == 1 ) ? 'gmw-locator-submit' : '';
		
		//remove this filter to prevent it from effecting other forms
		remove_filter( 'gmw_search_form_locator_button_img','gmw_users_locator_button', 10, 3 );
		
		return '<div id="'.$gmw['ID'].'" class="gmw-locator-button gmw-locate-btn  '.$class.' '.$lSubmit.'">'.$gmw['labels']['search_form']['get_my_location'].'</div>';
	}
	add_filter( 'gmw_search_form_locator_button_img','gmw_users_locator_button', 10, 3 );
}
?>
<?php do_action( 'gmw_before_search_form_template', $gmw ); ?>

<div class="gmw-form-wrapper gmw-form-wrapper-<?php echo $gmw['ID']; ?> gmw-fl-form-wrapper gmw-fl-yellow-form-wrapper">
	
	<?php do_action( 'gmw_before_search_form', $gmw ); ?>
	
	<form class="gmw-form gmw-form-<?php echo $gmw['ID']; ?>" name="gmw_form" action="<?php echo $gmw['search_results']['results_page']; ?>" method="get">
			
		<?php do_action( 'gmw_search_form_start', $gmw ); ?>
		
		<div class="xfield-trigger-wrapper">
			<div class="xfield-trigger" onclick="jQuery(this).closest('form').find('.gmw-fl-form-xprofile-fields').slideToggle();jQuery(this).html(jQuery(this).html() == 'Hide Options' ? 'Show Options' : 'Hide Options');">
				<?php echo $gmw['labels']['search_form']['show_options']; ?>
			</div>
		</div>
		
		<?php do_action( 'gmw_search_form_before_xprofile', $gmw ); ?>
		
		<?php gmw_fl_xprofile_fields( $gmw, $class='' ); ?>
		
		<?php do_action( 'gmw_search_form_before_address', $gmw ); ?>
		
		<!-- Address Field -->
		<?php gmw_search_form_address_field( $gmw, $id='', $class='' ); ?>
		
		<?php do_action( 'gmw_search_form_before_locator', $gmw ); ?>
		
		<!--  locator icon -->
		<?php gmw_search_form_locator_icon( $gmw ); ?>	
		
		<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
		
		<div class="gmw-unit-distance-wrapper">
			<!--distance values -->
			<?php gmw_search_form_radius_values( $gmw, $class='' ); ?>
			<!--distance units-->
			<?php gmw_search_form_units( $gmw, $class='' ); ?>	
		</div><!-- distance unit wrapper -->
		
		<?php gmw_form_submit_fields( $gmw, false ); ?>
		
		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
	
	<?php do_action( 'gmw_after_search_form', $gmw ); ?>
	
</div><!--form wrapper -->	

<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>