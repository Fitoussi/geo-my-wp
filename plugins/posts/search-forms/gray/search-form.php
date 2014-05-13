<?php 
/**
 * Search Form - Gray
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<?php 

if  ( !function_exists('gmw_locator_button') ) {
	function gmw_locator_button( $button, $gmw, $class ) {
		
		$lSubmit = ( isset( $gmw['search_form']['locator_submit'] ) && $gmw['search_form']['locator_submit'] == 1 ) ? 'gmw-locator-submit' : '';
		
		return '<div id="'.$gmw['ID'].'" class="gmw-locator-button gmw-locate-btn '.$class.' '.$lSubmit.'">'.__( 'Get my current location','GMW'). '</div>';
	}
	add_filter( 'gmw_search_form_locator_button_img','gmw_locator_button', 10, 3 );
}

if ( !function_exists('gmw_submit_button') ) {
	function gmw_submit_button( $button, $gmw, $value ) {
				
		return '<div id="gmw-submit-'.$gmw['ID'].'" class="gmw-submit-button gmw-submit">'.__('Search', 'GMW'). '</div>';
	}
	add_filter( 'gmw_form_submit_button','gmw_submit_button', 10, 3 );
}
?>
<div class="gmw-form-wrapper gmw-form-wrapper<?php echo $gmw['ID']; ?> gmw-pt-form-wrapper gmw-pt-gray-form-wrapper">
	
	<form class="gmw-form gmw-form-<?php echo $gmw['ID']; ?>" name="gmw_form" action="<?php echo $gmw['search_results']['results_page']; ?>" method="get">
			
		<?php do_action( 'gmw_search_form_start', $gmw ); ?>
		
		<div class="post-types-wrapper">
			<!-- post types dropdown -->
			<?php gmw_pt_form_post_types_dropdown( $gmw, $title='', $class='', $all= __(' -- Search Site -- ','GMW') ); ?>
		</div>
		
		<?php do_action( 'gmw_search_form_before_taxonomies', $gmw ); ?>
		
		<div class="taxonomies-wrapper">
			<!-- Display taxonomies/categories --> 
			<?php gmw_pt_form_taxonomies( $gmw, $tag='', $class='' ); ?>
		</div>
		
		<?php do_action( 'gmw_search_form_before_address', $gmw ); ?>
		            
		<!-- Address Field -->
		<?php gmw_search_form_address_field( $gmw, $id='', $class='' ); ?>
				
		<!--  locator icon -->
		<?php gmw_search_form_locator_icon( $gmw, $class='' ); ?>
				
		<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
		
		<div class="gmw-unit-distance-wrapper">
			<!--distance values -->
			<?php gmw_search_form_radius_values( $gmw, $class='' ); ?>
			<!--distance units-->
			<?php gmw_search_form_units( $gmw, $class='' ); ?>	
		</div><!-- distance unit wrapper -->
		
		<?php gmw_form_submit_fields( $gmw, $subValue=__('Submit','GMW') ); ?>
		
		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
</div><!--form wrapper -->	