<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

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
    					'name'		  	=> __( 'GMW Form', 'geo-my-wp' ),
    					'basic_usage' 	=> '[GMW]',
    					'template_usage'=> '&#60;&#63;php echo do_shortcode(\'[gmw]\'); &#63;&#62;',
    					'desc'        	=> __( "Use this shortcode to display any of the forms you created in the \"Forms\" Page of GEO my WP.", "GMW" ),
    					'attributes'  	=> array(
    							array(
    									'attr'	 => 'form',
    									'values' => array(
    											'Form ID',
    											'Results',
    									),
    									'desc'	 => __( "1) Use the attribute \"form\" with one of your forms ID to display the search form and results on that same page.", 'geo-my-wp' ).
    												__( "2) Use the value \"results\" when you want to display only the search results. This is usefull when you want to display the search form in on page.", 'geo-my-wp' ).
    												__( "and the search results in a different page or when need to display the results when using the serch form in a widget.", "GMW" )

    							),
    							array(
    									'attr'	 => 'map',
    									'values' => array(
    											__( 'Form ID','geo-my-wp' ),
    									),
    									'desc'	 => __( "Use anywhere on a page where you want to display the results map.", "GMW" )
    							),
    					),
    					'examples'  => array(
    							array(
    									'example' => '[gmw form="1"]',
    									'desc'	  => __( "Display the search form and search results of the form with ID 1.", "GMW" )

    							),
    							array(
    									'example' => '[gmw form="results"]',
    									'desc'	  => __( "Display only the seasrch results.", "GMW" )
    							),
    							array(
    									'example' => '[gmw map="1"]',
    									'desc'	  => __( "Place this shortcode anywhere on the page where you want to display the map of form 1", "GMW" )
    							),
    					),
    			),
    			'current_location' => array(
    					'name'		  	=> __( 'Curren Location', 'geo-my-wp' ),
    					'basic_usage' 	=> '[gmw_current_location]',
    					'template_usage'=> '&#60;&#63;php echo do_shortcode(\'[gmw_current_location]\'); &#63;&#62;',
    					'desc'        	=> __( "The shortcode will display a link which once clicked will open a popup window that will allow the user to get his current location.", 'geo-my-wp' ).
    									   __( " The location, if found, will then be saved via cookies. The location in the cookies later will be", 'geo-my-wp' ).
    									   __( "used with GEO my WP and other add-ons for different functionlities. ", "GMW" ),
    					'attributes'  => array(
    							array(
    									'attr'	 	=> 'element_id',
    									'values' 	=> array(
    											'numeric value'
    									),
    									'default'	=> 'random numeric value',
    									'desc'	 	=> __( "You can assign a unique element ID to the shortcode. The unique ID can be useful for styling purposes as well when using the hooks provided by the shortcoded when custom modifications required.", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'elements',
    									'values' 	=> array(
    											'username',
    											'address',
    											'map',
    									),
    									'default'	=> 'username,address,map',
    									'desc'	 	=> __( "Enter the elements that you want to display, in the order that you want them to be displayed comma saperated. The avaliable elements are:<ol><li>username - display the username or guest ( for logged out users ) with a greeting</li><li>address - the address of the user's current position</li><li>map - google map showing the user's osition</li></ol>", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'address_fields ( previously used as display_by )',
    									'values' 	=> array(
    											'street, city, state, zipcode, country, address',
    									),
    									'default'	=> 'city,country',
    									'desc'	 	=> __( "Use any of the address components, comma separated ( ex. street,city,state,zipcode,county ) or use address for the full address, to display the address on the screen once the user's location was found.", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'title',
    									'values' 	=> array(
    											__( 'any text','geo-my-wp' ),
    									),
    									'default'	=> 'Your location',
    									'desc'	 	=> __( "The title that will be display before the address. For example if you use the title \"Your location\" it will be displayes as Your Location Hollywood Florida ( assuming that the user's location is Hollywood Florida ).", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'address_as_text ( previously used as text_only )',
    									'values' 	=> array(
    											'1',
    											'0',
    									),
    									'default'	=> '0',
    									'desc'	 	=> __( "Use the value 1 to display the current location of the user as text only instead of an hyperlink which once clicked it popups the current location form.", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'user_message',
    									'values' 	=> array(
    											__( 'any text','geo-my-wp' ),
    									),
    									'default'	=> 'Hello',
    									'desc'		=> __( "Greeting message that will be displayed before the user name when logged in ( ex Hello admin ).", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'guest_message',
    									'values' 	=> array(
    											__( 'any text','geo-my-wp' ),
    									),
    									'default'	=> 'Hello guest!',
    									'desc'		=> __( "Greeting message that will be displayed when the users is logged out.", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'map_height',
    									'values' 	=> array(
    											'value in px or %',
    									),
    									'default'	=> '200px',
    									'desc'	 	=> __( "Map height in pixels or percentage ( ex 200px or 100% ).", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'map_width',
    									'values' 	=> array(
    											'value in px or %',
    									),
    									'default'	=> '200px',
    									'desc'	 	=> __( "Map width in pixels or percentage ( ex 200px or 100% ).", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'map_type',
    									'values' 	=> array(
    											'ROADMAP',
    											'SATELLITE',
    											'HYBRID',
    											'TERRAIN'
    									),
    									'default'	=> 'ROADMAP',
    									'desc'	 	=> __( "Set the map type", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'zoom_level',
    									'values' 	=> array(
    											__( 'Numeric value between 1 to 18', 'geo-my-wp' ),
    									),
    									'default'	=> '12',
    									'desc'	 	=> __( "Set the map zoom level", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'scrollwheel',
    									'values' 	=> array(
    											'1',
    											'0',
    									),
    									'default'	=> '1',
    									'desc'	 	=> __( "Use the value 1 to enable the map zoom in/out using the mouse scrollwheel.", "GMW" )
    							),
    							array(
    									'attr'	 	=> 'map_marker',
    									'values' 	=> array(
    											'Link to an image.',
    									),
    									'default'	=> 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
    									'desc'	 	=> __( "Use a link to an image that will be used as the map marker.", "GMW" )
    							),
    							
    							
    					),
    					'examples'  => array(
    							array(
    									'example' => "[gmw_current_location elements=\"username,address,map\" title=\"Your Location\" address_fields=\"city,state,country\"]",
    									'desc'	  => __( "This example will display:", 'gmw' ).
    												 "<br />Hello ( username )<br />
    												  Your Location Hollywood Florida US.<br />
    									              [ Map will be displayed here ]"

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

            <h2 class="gmw-wrap-top-h2">
                <?php echo _e( 'Shortcodes', 'geo-my-wp' ); ?>
                <?php gmw_admin_helpful_buttons(); ?>
            </h2>

            <div class="clear"></div>
            
            <form method="post" action="options.php">

                <div class="gmw-tabs-wrapper gmw-shortcodes-page-nav-tabs">
               		<?php
                 	foreach ( $this->shortcodes as $key => $options ) {
                    	echo '<span><a href="#settings-' . sanitize_title( $key ) . '" class="gmw-nav-tab">' . esc_html( $options['name'] ) . '</a></span>';
               		}
                    ?>
				</div>

                <?php foreach ( $this->shortcodes as $key => $options ) { ?>

                    <div id="settings-<?php echo sanitize_title( $key ); ?>" class="gmw-settings-panel gmw-tab-panel">
                                        
                    <table class="widefat fixed" style="margin-bottom:5px;margin-top: -2px;">
                        <thead>
                            <tr>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:20%;padding:11px 10px;">
                                	<label><?php _e( 'Post/Page Content Usage', 'geo-my-wp' ); ?></label>
                               	</th>
                               	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:40%;padding:11px 10px;">
                                	<label><?php _e( 'Template File Usage', 'geo-my-wp' ); ?></label>
                               	</th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:40%;padding:11px 10px;">
                                	<label><?php _e( 'Description', 'geo-my-wp' ); ?></label>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        	<tr>
                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top">
                        			<lablel><code><?php echo $options['basic_usage']; ?></code></lablel>
                        		</td>
                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top">
                        			<lablel><code><?php echo $options['template_usage']; ?></code></lablel>
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
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="text-align:center;padding:11px 10px;"><?php _e( 'Shortcode Attributes', 'geo-my-wp' ); ?></th>
                            </tr>
                        </thead>
                    </table>
                    
                    <table class="widefat fixed" style="margin-top:-2px">
                        <thead  style="background-color:#f9f9f9;">
                            <tr valign="top" >
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Attribute', 'geo-my-wp' ); ?></th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Values Accepted', 'geo-my-wp' ); ?></th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Default Value', 'geo-my-wp' ); ?></th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Additional Information', 'geo-my-wp' ); ?></th> 
                            </tr>
                        </thead>
                        <tbody>
                        	<?php 
                        	$rowNumber = 1;
                        	foreach ( $options['attributes'] as $attr ) { 
	                        	$alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : ''; ?>
	                        	<tr valign="top">
	                        		<th scope="row" style="color: #555;border-bottom:1px solid #eee;"><label for="setting-google_api"><?php echo $attr['attr']; ?></label></th>
	                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top">
	                        		
	                        		<?php $rowCount = 1; ?>
	                        		<?php foreach ( $attr['values'] as $value ) { ?>                        		
	                        			<p class="description"><?php echo $rowCount++.')'.' '.$value; ?></p>
	                        		<?php } ?>
	                        		</td>
	                        		<?php $default = ( isset( $attr['default'] ) ) ? $attr['default'] : __( 'N/A', 'geo-my-wp' ); ?>
	                        		<td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top"><p class="description"><?php echo $default; ?></p></td>
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
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="text-align:center;padding:11px 10px;"><?php _e( 'Shortcode Examples', 'geo-my-wp' ); ?></th>
                            </tr>
                        </thead>
                    </table>
                    
                    <?php if ( isset( $options['examples'] ) && !empty( $options['examples'] ) ) { ?>
	                    <table class="widefat fixed" style="margin-top:-2px">
	                        <thead  style="background-color:#f9f9f9;">
	                            <tr valign="top">
	                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Usage', 'geo-my-wp' ); ?></th>
	                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'Description', 'geo-my-wp' ); ?></th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                        	<?php 
	                        	$rowNumber = 1;
	                        	foreach ( $options['examples'] as $example ) { 
		                        	$alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : ''; ?>
		                        	<tr valign="top">
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
        <?php
    }
}
