<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_Shortcodes_page
 */

class GMW_Shortcodes_page {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {}

    /**
     * Shortcodes array.
     *
     * @access protected
     * @return void
     */
    protected function get_shortcodes() {

    	$shortcodes = apply_filters( 'gmw_admin_shortcodes_page', array(
    			'gmw_form' => array(
    					'name'		  => __( 'GMW Form', 'GMW' ),
    					'basic_usage' => '[GMW]',
    					'desc'        => __( 'Use this shortcode to display any of the forms you created in the "Forms" Page of GEO my WP.', 'GMW' ),
    					'attributes'  => array(
    							array(
    									'attr'	 => __( 'form', 'GMW' ),
    									'values' => array(
    											__( 'Form ID','GMW' ),
    											__( 'Results','GMW' ),
    									),
    									'desc'	 => __( '1) Use the attribute "form" with one of your forms ID to display the search form and results on that same page. <br />
    											2) Use the value "results" when you want to display only the search results. This is usefull when you want to display the search form in on page
    											and the search results in a different page or when need to display the results when using the serch form in a widget.', 'GMW')

    							),
    							array(
    									'attr'	 => __( 'map', 'GMW' ),
    									'values' => array(
    											__( 'Form ID','GMW' ),
    									),
    									'desc'	 => __( 'Use anywhere of a page where you want to display the results map.', 'GMW')
    							),	
    					),
    					'examples'  => array(
    							array(
    									'example' => __('[gmw form="1"]', 'GMW'),
    									'desc'	  => __('Use this shortcode on a page to display the search form and search results of the form with ID 1.', 'GMW')

    							),
    							array(
    									'example' => __('[gmw form="results"]','GMW' ),
    									'desc'	  => 'Use this on a page where you want to display the seasrch results.'
    							),
    							array(
    									'example' => __('[gmw map="1"]','GMW' ),
    									'desc'	  => __( 'Place this shortcode anywhere on the page where you want to display the map of form 1')
    							),
    					),
    			),
    			'current_location' => array(
    					'name'		  => __( 'Curren Location', 'GMW' ),
    					'basic_usage' => '[gmw_current_location]',
    					'desc'        => __( 'The shortcode will display a link which once clicked will open a popup window that will allow the user to get his current location. The location, if found, will then be saved via cookies. The location in the cookies later will be
    							used with GEO my WP and other add-ons for different functionlities. ', 'GMW' ),
    					'attributes'  => array(
    							array(
    									'attr'	 => __( 'title', 'GMW' ),
    									'values' => array(
    											__( 'any text','GMW' ),
    									),
    									'desc'	 => __( 'You can use this to display a title above the "Get Current Location" link. Ex "Your Location".', 'GMW')
    							),
    							array(
    									'attr'	 => __( 'display_by', 'GMW' ),
    									'values' => array(
    											__( 'street, city, state, zipcode, country','GMW' ),
    									),
    									'desc'	 => __( 'Use any of the address components, ( street,city,state,zipcode,county ) comma separated, that you want to display on the screen once
    											the user\'s location found.', 'GMW')
    							),
    							array(
    									'attr'	 => __( 'show_name', 'GMW' ),
    									'values' => array(
    											__( '0 || 1','GMW' ),
    									),
    									'desc'	 => __( 'Use the value 1 to display the word "Hello" and the name of the logged-in user or "Guest" for visitors next to the "current Location" link.
    											When using this feature the shorcode will display "Hello ( username ), Get your current location".', 'GMW')
    							),
    					),
    					'examples'  => array(
    							array(
    									'example' => __( '[gmw_current_location title="Your Location" display_by="city,state,country" show_name="1"]', 'GMW' ),
    									'desc'	  => __( 'This example will display: <br />
    													Your Location <br />
    													Hello ( username ), Get your current location. ', 'GMW' )

    							),
    					),
    					
    			),
    	));
    	
    	return $shortcodes;

    }

    /**
     * display settings 
     *
     * @access public
     * @return void
     */
    public function output() {
    	$this->shortcodes = self::get_shortcodes();
        ?>
        <div class="wrap">

            <?php echo GMW_Admin::gmw_credits(); ?>
            <h2 class="gmw-wrap-top-h2"><?php echo _e( 'GEO my WP Shortcodes', 'GMW' ); ?></h2>

            <div class="clear"></div>

            <form method="post" action="options.php">

                <table class="widefat fixed gmw-tabs-table">
                    <thead>
                        <tr>
                            <th class="widgets-holder-wrap closed gmw-nav-tab-wrapper" style="padding:0px;border-left: 4px solid #7ad03a;padding-left:0px;">

                                <?php
                                foreach ( $this->shortcodes as $key => $options ) {
                                    echo '<span><a href="#settings-' . sanitize_title( $key ) . '" title="' . esc_html( $options['name'] ) . '"  class="gmw-nav-tab">' . esc_html( $options['name'] ) . '</a></span>';
                                }
                                ?>

                            </th>
                        </tr>
                    </thead>
                </table>
                <br />

                <?php
                foreach ( $this->shortcodes as $key => $options ) {

                    echo '<div id="settings-' . sanitize_title( $key ) . '" class="settings_panel">';
                    ?>
                    <table class="widefat fixed" style="margin-bottom:5px;">
                        <thead>
                            <tr class="alternate">
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px;">
                                	<label><?php _e( 'Basic Usage', 'GMW' ); ?></label>
                               	</th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px;">
                                	<label><?php _e( 'Description', 'GMW' ); ?></label>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        	<tr>
                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top">
                        			<lablel><code><?php echo $options['basic_usage']; ?></code></lablel>
                        		</td>
                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top">
                        			<p class="description"><?php echo $options['desc']; ?></p>
                        		</td>
                        	</tr>
                        </tbody>
                    </table>
                    
                    <table class="widefat fixed" style="margin-top:10px;">
                        <thead>
                            <tr>
                                <th scope="col" id="cb" class="alternate manage-column column-cb check-column" style="text-align:center;padding:11px 10px;"><?php _e( 'Shortcode Attributes', 'GMW' ); ?></th>
                            </tr>
                        </thead>
                    </table>
                    
                    <table class="widefat fixed">
                        <thead>
                            <tr valign="top">
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Attribute', 'GMW' ); ?></th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Values', 'GMW' ); ?></th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Additional Information', 'GMW' ); ?></th> 
                            </tr>
                        </thead>
                        <tbody>
                        	<?php 
                        	$rowNumber = 0;
                        	foreach ( $options['attributes'] as $attr ) { 
	                        	$alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : ''; ?>
	                        	<tr valign="top" class="<?php echo $alternate; ?>">
	                        		<th scope="row" style="color: #555;border-bottom:1px solid #eee;"><label for="setting-google_api"><?php echo $attr['attr']; ?></label></th>
	                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top">
	                        		
	                        		<?php $rowCount = 1; ?>
	                        		<?php foreach ( $attr['values'] as $value ) { ?>                        		
	                        			<p class="description"><?php echo $rowCount++.')'.' '.$value; ?></p>
	                        		<?php } ?>
	                        		</td>
	                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top"><p class="description"><?php echo $attr['desc']; ?></p></td>
	                        	</tr>
                        		<?php  
                        		$rowNumber++; 
                        	} 
                        	?>
                        </tbody>
                    </table>
                    
                    <table class="widefat fixed" style="margin-top:10px;">
                        <thead>
                            <tr>
                                <th scope="col" id="cb" class="alternate manage-column column-cb check-column" style="text-align:center;padding:11px 10px;"><?php _e( 'Shortcode Examples', 'GMW' ); ?></th>
                            </tr>
                        </thead>
                    </table>
                    
                    <?php if ( isset( $options['examples'] ) && !empty( $options['examples'] ) ) { ?>
	                    <table class="widefat fixed">
	                        <thead>
	                            <tr valign="top">
	                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Usage', 'GMW' ); ?></th>
	                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Description', 'GMW' ); ?></th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                        	<?php 
	                        	$rowNumber = 0;
	                        	foreach ( $options['examples'] as $example ) { 
		                        	$alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : ''; ?>
		                        	<tr valign="top" class="<?php echo $alternate; ?>">
		                        		<th scope="row" style="color: #555;border-bottom:1px solid #eee;"><label for="setting-google_api"><code><?php echo $example['example']; ?></code></label></th>
		                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top"><p class="description"><?php echo $example['desc']; ?></p></td>
		                        	</tr>
	                        		<?php  
	                        		$rowNumber++; 
	                        	} 
	                        	?>
	                        </tbody>
	                    </table>
	               	<?php } ?>           
            </div>
            <?php
        }
        ?>
        </form>
        </div>
        <script type="text/javascript">
            jQuery('.gmw-nav-tab-wrapper a').click(function() {
                jQuery('.settings_panel').hide();
                jQuery('.gmw-nav-tab-active').css('background', '#f7f7f7');
                jQuery('.gmw-nav-tab-active').removeClass('gmw-nav-tab-active');

                jQuery(jQuery(this).attr('href')).show();
                jQuery(this).addClass('gmw-nav-tab-active');
                jQuery(this).css('background', '#C3D5E6');

                return false;
            });

            jQuery('.gmw-nav-tab-wrapper a:first').click();
        </script>
        <?php

    }

}
