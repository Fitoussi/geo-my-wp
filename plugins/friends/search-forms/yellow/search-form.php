<?php 
/**
 * Members Locator search form - Yellow
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<?php 

if  ( !function_exists('gmw_users_locator_button') ) {
	function gmw_users_locator_button( $button, $gmw, $class ) {
		
		$lSubmit = ( isset( $gmw['search_form']['locator_submit'] ) && $gmw['search_form']['locator_submit'] == 1 ) ? 'gmw-locator-submit' : '';
		
		return '<div id="'.$gmw['ID'].'" class="gmw-locator-button gmw-locate-btn  '.$class.' '.$lSubmit.'">'.__( 'Get my current location','GMW-UG'). '</div>';
	}
	add_filter( 'gmw_search_form_locator_button_img','gmw_users_locator_button', 10, 3 );
}

if ( !function_exists('gmw_users_submit_button') ) {
	function gmw_users_submit_button( $button, $gmw, $value ) {
				
		return '<div id="gmw-submit-'.$gmw['ID'].'" class="gmw-submit-button gmw-submit">'.__('Search', 'GMW-UG'). '</div>';
	}
	add_filter( 'gmw_form_submit_button','gmw_users_submit_button', 10, 3 );
}

?>
<div class="gmw-form-wrapper gmw-form-wrapper-<?php echo $gmw['ID']; ?> gmw-fl-form-wrapper gmw-fl-yellow-form-wrapper">
	
	<form class="gmw-form gmw-form-<?php echo $gmw['ID']; ?>" name="gmw_form" action="<?php echo $gmw['search_results']['results_page']; ?>" method="get">
			
		<?php do_action( 'gmw_search_form_start', $gmw ); ?>
		
		<div class="xfield-trigger-wrapper">
			<div class="xfield-trigger" onclick="jQuery(this).closest('form').find('.gmw-fl-form-xprofile-fields').slideToggle();jQuery(this).html(jQuery(this).html() == 'Hide Options' ? 'Show Options' : 'Hide Options');">
				<?php _e( 'Show Options','GMW' ); ?>
			</div>
		</div>
		<?php gmw_fl_xprofile_fields( $gmw, $class='' ); ?>
		
		<!-- Address Field -->
		<?php gmw_search_form_address_field( $gmw, $id='', $class='' ); ?>
		
		<?php do_action( 'gmw_search_form_before_locator', $gmw ); ?>
		
		<!--  locator icon -->
		<?php gmw_search_form_locator_icon( $gmw, $class='' ); ?>
			
		<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
		
		<div class="gmw-unit-distance-wrapper">
			<!--distance values -->
			<?php gmw_search_form_radius_values( $gmw, $class='' ); ?>
			<!--distance units-->
			<?php gmw_search_form_units( $gmw, $class='' ); ?>	
		</div><!-- distance unit wrapper -->
		
		<?php gmw_form_submit_fields( $gmw, $subValue=__('Submit','GMW-UG') ); ?>
		
		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
</div><!--form wrapper -->	