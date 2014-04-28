<?php 
/**
 * Default search form for Post, post types and pages.
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<div class="gmw-form-wrapper gmw-form-wrapper-<? echo $gmw['ID']; ?> gmw-pt-form-wrapper gmw-pt-default-form-wrapper">
	
	<form class="gmw-form gmw-form-<? echo $gmw['ID']; ?>" name="gmw_form" action="<?php echo $gmw['search_results']['results_page']; ?>" method="get">
			
		<?php do_action( 'gmw_search_form_start', $gmw ); ?>
		
		<div class="gmw-post-types-wrapper">
			<!-- post types dropdown -->
			<?php gmw_pt_form_post_types_dropdown( $gmw, $title='', $class='', $all= __(' -- Search Site -- ','GMW') ); ?>
		</div>
		
		<?php do_action( 'gmw_search_form_before_taxonomies', $gmw ); ?>
		
		<div class="gmw-taxonomies-wrapper">
			<!-- Display taxonomies/categories --> 
			<?php gmw_pt_form_taxonomies( $gmw, $tag='', $class='' ); ?>
		</div>
		
		<?php do_action( 'gmw_search_form_before_address', $gmw ); ?>
		            
		<!-- Address Field -->
		<?php gmw_search_form_address_field( $gmw, $id='', $class='' ); ?>
		
		<?php do_action( 'gmw_search_form_before_locator', $gmw ); ?>
		
		<!--  locator icon -->
		<?php gmw_search_form_locator_icon( $gmw, $class='' ); ?>
			
		<div class="clear"></div>
		
		<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
		
		<div class="gmw-unit-distance-wrapper">
			
			<div class="gmw-radius-dropdown-wrapper">
            	<!--distance values -->
            	<?php gmw_search_form_radius_values( $gmw, $class='' ); ?>
            </div>
            
			<div class="gmw-units-dropdown-wrapper">
            	<!--distance units-->
            	<?php gmw_search_form_units( $gmw, $class='' ); ?>
            </div>	
            
		</div><!-- distance unit wrapper -->
		
		<?php gmw_form_submit_fields( $gmw, $subValue=__('Submit','GMW') ); ?>
		
		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
</div><!--form wrapper -->	