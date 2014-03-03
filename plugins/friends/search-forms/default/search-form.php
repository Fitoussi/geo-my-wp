<?php 
/**
 * Default search form for Buddypress members.
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<div id="gmw-form-wrapper-<? echo $gmw['form_id']; ?>" class="gmw-form-wrapper gmw-form-wrapper-<? echo $gmw['form_id']; ?> gmw-fl-form-wrapper">
	<form id="gmw-form-<? echo $gmw['form_id']; ?>" class="standard-form gmw-form gmw-form-<? echo $gmw['form_id']; ?> gmw-fl-form" name="wppl_form" action="<?php echo $gmw['results_page']; ?>" method="get">
		
		<?php if ( ( isset($gmw['profile_fields']) || isset($gmw['profile_fields_date']) ) ) gmw_fl_fields_dropdown($gmw, $id='', $class='' ); ?>
		
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
			<?php gmw_search_form_units($gmw, $class='' ); ?>	
		</div>
		
		<?php gmw_form_submit_fields($gmw, __('Submit','GMW')); ?>
	</form>
</div><!--form wrapper -->	
