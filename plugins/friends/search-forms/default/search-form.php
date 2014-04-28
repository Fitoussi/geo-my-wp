<?php
/**
 * Default search form for Buddypress members.
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<div class="gmw-form-wrapper gmw-form-wrapper-<? echo $gmw['ID']; ?> gmw-fl-form-wrapper gmw-fl-default-form-wrapper">

    <form class="standard-form gmw-form gmw-form-<? echo $gmw['ID']; ?>" name="gmw_form" action="<?php echo $gmw[ 'search_results' ][ 'results_page' ]; ?>" method="get">

        <?php do_action( 'gmw_search_form_start', $gmw ); ?>

        <?php gmw_fl_xprofile_fields( $gmw, $class='' ); ?>

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
        </div>

        <?php gmw_form_submit_fields( $gmw, __( 'Submit', 'GMW' ) ); ?>

        <?php do_action( 'gmw_search_form_end', $gmw ); ?>

    </form>

</div><!--form wrapper -->	
