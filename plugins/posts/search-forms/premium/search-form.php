<?php 
/**
 * Premium search form for Post, post types and pages.
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<div id="gmw-form-wrapper-<? echo $gmw['form_id']; ?>" class="gmw-form-wrapper gmw-form-wrapper-<? echo $gmw['form_id']; ?> gmw-pt-form-wrapper">
	<form id="gmw-form-<? echo $gmw['form_id']; ?>" class="standard-form gmw-form gmw-form-<? echo $gmw['form_id']; ?> gmw-pt-form " name="wppl_form" action="<?php echo $gmw['results_page']; ?>" method="get">
					
		<div class="gmw-post-types-wrapper">
			<!-- post types dropdown -->
			<?php gmw_pt_form_post_types_dropdown($gmw, $title='', $class='', $all= __(' -- Search Site -- ','GMW')); ?>
		</div>
		
		<div class="gmw-taxonomies-wrapper">
			<!-- Display taxonomies/categories --> 
			<?php gmw_pt_form_taxonomies_premium($gmw); ?>
		</div>
		
		<div class="gmw-custom-fields-wrapper">
			<!-- custom fields --> 
			<?php gmw_pt_custom_fields($gmw); ?>		
		</div>
		
		<div class="gmw-keywords-wrapper">
			<!-- keywords field -->
			<?php gmw_pt_form_keywords( $gmw, $id='gmw-keywords', $class='gmw-keywords' ); ?>
		</div>
		   		
		<div class="gmw-address-field-wrapper">
			<!-- Address Field -->
			<?php gmw_search_form_address_field($gmw, $class=''); ?>
		</div>	
		
		<!--  locator icon -->
		<?php gmw_search_form_locator_icon($gmw, $class=''); ?>
			
		<div class="clear"></div>
		
		<div class="gmw-unit-distance-wrapper">
			<!--distance values -->
			<?php gmw_search_form_radius_values($gmw, $class='', $btitle='', $stitle=''); ?>
			<!--distance units-->
			<?php gmw_search_form_units($gmw, $class=''); ?>	
		</div><!-- distance unit wrapper -->
		
		<?php gmw_form_submit_fields($gmw, $subValue=__('Submit','GMW')); ?>
	</form>
</div><!--form wrapper -->	