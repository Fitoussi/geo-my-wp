<?php
/**
 * Members Locator "kleo" search form template file.
 *
 * The information on this file will be displayed as the search forms.
 *
 * The function pass 1 args for you to use:
 * $gmw  - the form being used ( array )
 *
 * You could but It is not recomemnded to edit this file directly as your changes will be overwritten on the next update of the plugin.
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

	<div id="members-dir-search" class="dir-search gmw-form-wrapper gmw-fl-kleo-form-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?>">
		
		<?php do_action( 'gmw_before_search_form', $gmw ); ?>
		
		<form id="search-members-form" class="search-members-form gmw-form" name="gmw_form" action="<?php echo esc_attr( $gmw_form->get_results_page() ); ?>" method="get" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">
				
			<?php do_action( 'gmw_search_form_start', $gmw ); ?>
			
			<?php gmw_search_form_address_field( $gmw, $id = 'members_search', $class = '' ); ?>
				
			<?php gmw_search_form_locator_button( $gmw ); ?>

			<span class="xprofile-fields-trigger" onclick="jQuery(this).closest('form').find('.gmw-kleo-advanced-search-wrapper' ).slideToggle();">
				<?php _e( 'More options', 'geo-my-wp' ); ?>
			</span>

			<?php do_action( 'gmw_search_form_before_submit', $gmw ); ?>

			<?php gmw_search_form_submit_button( $gmw ); ?>	

			<div class="gmw-kleo-advanced-search-wrapper">				
				
				<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
				
				<?php gmw_search_form_radius( $gmw ); ?>
			
				<?php gmw_search_form_units( $gmw ); ?>	

				<?php do_action( 'gmw_search_form_filters', $gmw ); ?>
															
				<?php gmw_search_form_xprofile_fields( $gmw ); ?>
				
			</div>
			
			<?php do_action( 'gmw_search_form_end', $gmw ); ?>
			
		</form>
		
		<?php do_action( 'gmw_after_search_form', $gmw ); ?>
		
	</div>

</div>

<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>
