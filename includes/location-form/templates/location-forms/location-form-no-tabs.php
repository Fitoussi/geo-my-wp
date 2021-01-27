<?php
/**
 * Location form no tabs tempalte file
 *
 * @params $gmw_location_form - the location form object.
 *
 * @since 3.0
 *
 * @package geo-my-wp
 */

?>
<div class="gmw-lf-content-wrapper">

	<div class="gmw-lf-content-inner">

		<?php do_action( 'gmw_lf_content_start', $gmw_location_form ); ?>

		<!-- location fields tab -->
		<div id="location-section" class="section-wrapper location">

			<?php do_action( 'gmw_lf_location_section_start', $gmw_location_form ); ?>

			<?php $gmw_location_form->display_form_fields_group( 'location' ); ?>

			<?php do_action( 'gmw_lf_location_section_end', $gmw_location_form ); ?>

		</div>

		<!-- address tab -->
		<div id="address-section" class="section-wrapper address">

			<?php do_action( 'gmw_lf_address_section_start', $gmw_location_form ); ?>

			<?php $gmw_location_form->display_form_fields_group( 'address' ); ?>

			<?php do_action( 'gmw_lf_address_section_end', $gmw_location_form ); ?>

		</div>

		<!-- coords tab -->
		<div id="coordinates-section" class="section-wrapper coords">

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
