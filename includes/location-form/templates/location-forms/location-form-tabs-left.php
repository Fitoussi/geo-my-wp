<?php
/**
 * Location form left tabs tempalte file
 *
 * @params $gmw_location_form - the location form object.
 *
 * @since 3.0
 *
 * @package geo-my-wp
 */

?>
<div class="gmw-lf-tabs-bg"></div>

<!-- Left tabs -->
<ul class="gmw-lf-tabs-wrapper">

	<?php do_action( 'gmw_lf_before_tabs', $gmw_location_form ); ?>

	<?php echo $gmw_location_form->display_tabs(); // WPCS: XSS ok. ?>

	<?php do_action( 'gmw_lf_after_tabs', $gmw_location_form ); ?>
</ul>

<!-- tabs content -->
<div class="gmw-lf-content-wrapper">

	<div class="gmw-lf-content-inner tabs-wrapper">

		<?php do_action( 'gmw_lf_content_start', $gmw_location_form ); ?>

		<!-- location fields tab -->
		<div id="location-tab-panel" class="section-wrapper location">

			<?php do_action( 'gmw_lf_location_section_start', $gmw_location_form ); ?>

			<?php $gmw_location_form->display_form_fields_group( 'location' ); ?>

			<?php do_action( 'gmw_lf_location_section_end', $gmw_location_form ); ?>

		</div>

		<!-- address tab -->
		<div id="address-tab-panel" class="section-wrapper address">

			<?php do_action( 'gmw_lf_address_section_start', $gmw_location_form ); ?>

			<?php $gmw_location_form->display_form_fields_group( 'address' ); ?>

			<?php do_action( 'gmw_lf_address_section_end', $gmw_location_form ); ?>

		</div>

		<!-- coords tab -->
		<div id="coordinates-tab-panel" class="section-wrapper coords">

			<?php do_action( 'gmw_lf_coords_section_start', $gmw_location_form ); ?>

			<?php $gmw_location_form->display_form_fields_group( 'coordinates' ); ?>

			<?php do_action( 'gmw_lf_coords_section_end', $gmw_location_form ); ?>

		</div>

		<?php do_action( 'gmw_lf_content_end', $gmw_location_form ); ?>

	</div>

	<ul class="gmw-lf-form-actions-wrapper">

		<li class="gmw-lf-form-actions">
			<?php $gmw_location_form->display_form_fields_group( 'actions' ); ?>
		</li>

	</ul>   
</div>   
