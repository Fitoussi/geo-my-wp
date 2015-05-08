<?php
/**
 * GMW FL page - Content of the "Location" tab for the looged in user
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>

<?php
class GMW_FL_Location_Page {

    function __construct() {

        global $bp, $wpdb;

        $this->displayed_user = $bp->displayed_user->id;
        //get the information of the user from database
        $this->location       = $wpdb->get_row($wpdb->prepare( "SELECT * FROM wppl_friends_locator WHERE member_id = %s", $this->displayed_user ) );

        $this->display_location_form( $this->location, $this->displayed_user );
    }

    public function address_fields_init() {

    	$this->location_fields = apply_filters('gmw_fl_location_page', array(
    			'address_autocomplete' => array(
    					__('Address Autocomplete', 'GMW'),
    					array(
    							'autocomplete' => array(
    									'name'        => 'formatted_address',
    									'std'         => '',
    									'id'          => 'gmw-yl-autocomplete',
    									'class'		  => '',
    									'placeholder' => __('Type an address for autocomplete', 'GMW'),
    									'label'       => '',
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('style' => 'width:100%')
    							),
    					),
    			),
    			'map' => array(
    					__( 'Find your location on the map', 'GMW' ),
    					array(
    							'map' => array(
    									'name'        => 'map',
    									'std'         => '',
    									'id'          => 'gmw-yl-map',
    									'class'		  => 'gmw-map',
    									'placeholder' => '',
    									'label'       => '',
    									'desc'        => '',
    									'type'        => 'div',
    									'attributes'  => array( 'style' => 'height:210px;width:100%' )
    							),
    					),
    			),
    			'locator' => array(
    					__( 'Auto-Locator', 'GMW' ),
    					array(
    							'locator' 	=> array(
    									'name'        => 'locator',
    									'title'		  => __( 'Get Your Current Location','GMW' ),
    									'std'         => '',
    									'id'          => 'gmw-yl-locator-btn',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => '',
    									'desc'        => '',
    									'type'        => 'button',
    									'attributes'  => array()
    							),
    					),
    			),
    			'address_fields'       => array(
    					__('Address Fields', 'GMW'),
    					array(
                                'street' 		=> array(
    									'name'        => 'street',
    									'std'         => '',
    									'id'          => 'gmw-street',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('Street', 'GMW'),
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('size' => '40')
    							),
    							'apt' 			=> array(
    									'name'        => 'apt',
    									'std'         => '',
    									'id'          => 'gmw-apt',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('Apt/Suit', 'GMW'),
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('size' => '40')
    							),
    							'city' 			=> array(
    									'name'        => 'city',
    									'std'         => '',
    									'id'          => 'gmw-city',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('City', 'GMW'),
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('size' => '40')
    							),
    							'state' 		=> array(
    									'name'        => 'state',
    									'std'         => '',
    									'id'          => 'gmw-state',
    									'placeholder' => '',
    									'label'       => __('State', 'GMW'),
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('size' => '40')
    							),
    							'state_long' 	=> array(
    									'name'        => 'state_long',
    									'std'         => '',
    									'id'          => 'gmw-state-long',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('State Long Name', 'GMW'),
    									'desc'        => '',
    									'type'        => 'hidden',
    									'attributes'  => array('size' => '40')
    							),
    							'zipcode'		=> array(
    									'name'        => 'zipcode',
    									'std'         => '',
    									'id'          => 'gmw-zipcode',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('Zipcode', 'GMW'),
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('size' => '40')
    							),
    							'country'		=> array(
    									'name'        => 'country',
    									'std'         => '',
    									'id'          => 'gmw-country',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('Country', 'GMW'),
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('size' => '40')
    							),
    							'country_long'	=> array(
    									'name'        => 'country_long',
    									'std'         => '',
    									'id'          => 'gmw-country-long',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('Country Long Name', 'GMW'),
    									'desc'        => '',
    									'type'        => 'hidden',
    									'attributes'  => array('size' => '40')
    							),
    							'address'		=> array(
    									'name'        => 'address',
    									'std'         => '',
    									'id'          => 'gmw-address',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('address', 'GMW'),
    									'desc'        => '',
    									'type'        => 'hidden',
    									'attributes'  => array('size' => '40')
    							),
    							'formatted_address' => array(
    									'name'        => 'formatted_address',
    									'std'         => '',
    									'id'          => 'gmw-formatted-address',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('Formatted Address', 'GMW'),
    									'desc'        => '',
    									'type'        => 'hidden',
    									'attributes'  => array('size' => '40')
    							),
                                'street_number'   => array(
                                        'name'        => 'street_number',
                                        'std'         => '',
                                        'id'          => 'gmw-street-number',
                                        'class'       => '',
                                        'placeholder' => '',
                                        'label'       => __('Street number', 'GMW'),
                                        'desc'        => '',
                                        'type'        => 'hidden',
                                        'attributes'  => array('size' => '40')
                                ),
                                'street_name'  => array(
                                        'name'        => 'street_name',
                                        'std'         => '',
                                        'id'          => 'gmw-street-name',
                                        'class'       => '',
                                        'placeholder' => '',
                                        'label'       => __('Street name', 'GMW'),
                                        'desc'        => '',
                                        'type'        => 'hidden',
                                        'attributes'  => array('size' => '40')
                                ),
    					),
    			),
    			'latlng_fields'        => array(
    					__('Latitude Longitude', 'GMW'),
    					array(
    							'lat'		=> array(
    									'name'        => 'lat',
    									'std'         => '',
    									'id'          => 'gmw-lat',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('Latitude', 'GMW'),
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('size' => '40')
    							),
    							'lng' 		=> array(
    									'name'        => 'long',
    									'std'         => '',
    									'id'          => 'gmw-lng',
    									'class'		  => '',
    									'placeholder' => '',
    									'label'       => __('Longitude', 'GMW'),
    									'desc'        => '',
    									'type'        => 'text',
    									'attributes'  => array('size' => '40')
    							),
    					),
    			),
    	)
    	);

    }

    public function display_location_fields( $section, $tag, $tag_class, $title ) {

    	$this->address_fields_init();

        $location_fields = $this->location_fields;
        $location        = $this->location;

        if ( $tag == 'table' ) {
        	$element = array(
        			'table',
        			'tr',
        			'th',
        			'td',
        	);
        } elseif ( $tag == 'ul' ) {
        	$element = array(
        			'ul',
        			'li',
        			'div',
        			'div',
        	);
        } elseif ($tag == 'ol') {
            $element = array(
        			'ol',
        			'li',
        			'div',
        			'div',
        	);
        } elseif ($tag == 'div') {
            $element = array(
        			'div',
        			'div',
        	);
        }

        echo '<div class="gmw-yl-section-wrapper gmw-yl-' . $section . '-section-wrapper">';
        
	        if ( isset( $title ) && $title == true ) {
	        	echo '<p id="gmw-yl-' . $section . '-title" class="field-title">' . $location_fields[$section][0] . '</p>';
	        }
	        
	        echo '<' . $element[0] . '  class="gmw-yl-fields-wrapper ' . $tag_class . '">';
	               
		        foreach ( $location_fields[$section][1] as $key => $option ) {
	
		        	$title 		 = ( !empty( $option['title'] ) ) 		   ? $option['title'] : '';
		        	$placeholder = ( !empty( $option['placeholder'] ) )    ? 'placeholder="' . $option['placeholder'] . '"' : '';
		            $class       = !empty( $option['class'] ) 			   ? $option['class'] : '';
		            $id          = !empty( $option['id'] ) 				   ? $option['id'] : '';
		            
		            if ( ( $section == 'address_fields' || $section == 'latlng_fields' || $section == 'address_autocomplete' ) && empty( $location ) ) {
		            	$optionName = ( $option['name'] == 'long' ) ? 'lng' : $option['name'];
		            	$value = ( isset( $_COOKIE['gmw_'.$optionName] ) ) ? urldecode( $_COOKIE['gmw_'.$optionName] ) : '';
		            } else {
		            	$value = ( isset( $location->$option['name'] ) ) ? $location->$option['name'] : '';
		            }      
		            
		            $attributes  = array();
		            $hidden      = ( $option['type'] == 'hidden' ) ? 'style="display:none"' : '';
		
		            if ( !empty( $option['attributes'] ) && is_array( $option['attributes'] ) ) {
		                foreach( $option['attributes'] as $attribute_name => $attribute_value ) {
		                    $attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
		                }
		            }
		
		            if ( $tag != 'div' ) echo '<' . $element[1] . ' ' . $hidden . ' >';
		
			            if ( isset( $option['label'] ) && !empty( $option['label'] ) ) {
			
			               if ( $tag == 'table' ) echo '<' . $element[2] . '>';
			
			                	echo '<label for="setting-' . $option['name'] . '" >' . $option['label'] . '</label>';
			
			                if ( $tag == 'table' ) echo '</' . $element[2] . '>';
			            }
			            
			            	if ( $tag == 'table' ) echo '<' . $element[3] . '>';
		
					            switch ( $option['type'] ) {
					
					                case "checkbox" :
					                    ?>
					                    <label>
					                    	<input type="checkbox" name="gmw_<?php echo $option['name']; ?>" id="<?php echo $id; ?>" class="<?php echo $class; ?>" value="1" <?php echo implode(' ', $attributes); ?> <?php checked('1', $value); ?> /> 
					                    	<?php echo $option['cb_label']; ?>
					                    </label>
					                    <?php
					                break;
					                
					                case "textarea" :
					                    ?>
					                    <textarea name="gmw_<?php echo $option['name']; ?>" id="<?php echo $id; ?>" class="<?php echo $class; ?>" cols="50" rows="3"  <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?>>
					                    	<?php echo esc_textarea($value); ?>
					                    </textarea>
					                    <?php
					                break;
					                
					                case "select" :
					                    ?>
					                    <select name="gmw_<?php echo $option['name']; ?>" id="<?php echo $id; ?>" class="<?php echo $class; ?>" <?php echo implode(' ', $attributes); ?>>
					                    <?php
					                    foreach ($option['options'] as $key => $name) {
					                        echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($name) . '</option>';
					                    }
					                    ?>
					                    </select>
					                    <?php
					                break;
					                
					                case "password" :
					                    ?>
					                    <input id="<?php echo $id; ?>" class="<?php echo $class; ?>" type="password" name="gmw_<?php echo $option['name']; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> />
					                    <?php
					                break;
					                
					                case "div" :
					                	?>
					                	<div id="<?php echo $option['id']; ?>" class="<?php echo $class; ?>" <?php echo implode(' ', $attributes); ?>>
					                		<?php echo $title; ?>
					                	</div>
					                	<?php
					                break;
					                
					                case "text" :
					                    ?>
					                    	<input type="text" name="gmw_<?php echo $option['name']; ?>" id="<?php echo $id; ?>" class="<?php echo $class; ?>"  value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> />
					                    <?php
					                    if ($option['desc']) {
					                        echo ' <p class="description">' . $option['desc'] . '</p>';
					                    }
					                break;
					                
					                case "button" :
					                	?>
					                     	<input type="button" name="gmw_<?php echo $option['name']; ?>" id="<?php echo $id; ?>" class="<?php echo $class; ?>"  value="<?php echo $title; ?>" <?php echo implode(' ', $attributes); ?> />
					               		<?php
					                break;
					                
					                case "hidden" :
					                    ?>
					                    	<input type="hidden" name="gmw_<?php echo $option['name']; ?>" id="<?php echo $id; ?>" class="<?php echo $class; ?> gmw-yl-hidden-field"  value="<?php echo $value; ?>" <?php echo implode(' ', $attributes); ?> />
					                    <?php
					                break;
					                
					            }
		
		            		if ( $tag == 'table' ) echo '</' . $element[3] . '>';
		
		            if ( $tag != 'div' ) echo '</' . $element[1] . '>';
		        }
	
	        echo '</' . $element[0] . '>';
	        
		echo '</div>';

    }

    public function display_location_form( $location, $user_id ) {
    	
    	$fieldsLabel = apply_filters( 'gmw_fl_your_location_page_titles', array(
    			'your_location' 	=> __( 'Your Location', 'GMW' ),
    			'no_location' 		=> __( "You haven't set a location yet", 'GMW' ),
    			'edit_location' 	=> __( 'Edit Location', 'GMW' ),
    			'delete_location' 	=> __( 'Delete Location', 'GMW' ),
    			'manualy_enter' 	=> __( 'Enter your location manually', 'GMW' ),
    			'address' 			=> __( 'Address', 'GMW' ),
    			'coords' 			=> __( 'Latitude / Longitude', 'GMW' ),
    			'save_location' 	=> __( 'Save Location', 'GMW' ),
    	) );
    	
        ?>
        <div id="gmw-your-location-wrapper">
			
			<?php do_action( 'gmw_yl_page_start', $location, $user_id ); ?>

            <div id="your-location-section">
            	
            	<p class="field-title"><?php echo $fieldsLabel['your_location']; ?></p>
            	
            	<div id="your-location-section-inner">
            		<input type="text" id="gmw-yl-field" value="<?php echo ( isset($location->formatted_address) ) ? $location->formatted_address : $fieldsLabel['no_location']; ?>" disabled="disabled" />
            		<input type="button" id="gmw-yl-edit" class="first" value="<?php echo $fieldsLabel['edit_location']; ?>" />
            		<input type="button" id="gmw-yl-delete" value="<?php echo $fieldsLabel['delete_location']; ?>" />
           			<img src="<?php echo GMW_FL_URL . 'assets/images/ajax-loader.gif'; ?>" id="gmw-yl-spinner" alt="" />
           		</div>

            </div>
                
            <?php do_action( 'gmw_yl_before_form', $location, $user_id ); ?>
                
                <form id="gmw-yl-form" name="gmw_yl_location_form" method="post" action="">
                
                	<?php do_action('gmw_yl_form_start', $location, $user_id ); ?>
					
					<!--  locator button -->
                    <?php echo $this->display_location_fields('locator', 'div', 'locate-me-button', false ); ?>                
  
                    <?php do_action( 'gmw_yl_before_map', $location, $user_id ); ?>

     				<!-- Display Map -->
					<?php echo $this->display_location_fields( 'map', 'div', 'map-wrapper', true ); ?> 
					
                    <?php do_action( 'gmw_yl_before_autocomplete', $location, $user_id ); ?>

                    <!--  autocomplete -->
                    <?php echo $this->display_location_fields( 'address_autocomplete', 'div', 'location', true ); ?>

                    <?php do_action( 'gmw_yl_before_manuall_section', $location, $user_id ); ?>
                    
                    <!-- menually section -->
                    <div id="gmw-yl-manuall-section">
                    
                    	<p class="field-title"><?php echo $fieldsLabel['manualy_enter']; ?></p>
						
						<div id="gmw-yl-tabs-section">
						
	                    	<!-- tabs -->
	                        <ul id="gmw-yl-tabs">
								
								<?php echo do_action( 'gmw_yl_tabs_start', $location, $user_id ); ?>
								
	                            <li id="gmw-yl-address-tab" class="gmw-yl-tab active"><?php echo $fieldsLabel['address']; ?></li>
	                            <li id="gmw-yl-latlng-tab" class="gmw-yl-tab" ><?php echo $fieldsLabel['coords']; ?></li>
	
	                            <?php echo do_action( 'gmw_yl_tabs_end', $location, $user_id ); ?>
	                        </ul>
	                        
	                        <?php echo do_action( 'gmw_yl_before_tabs_wrapper', $location, $user_id ); ?>
	
	                        <!-- address tab -->
	                        <div id="gmw-yl-address-tab-wrapper" class="gmw-yl-tab-wrapper address">
	
	                            <?php do_action( 'gmw_yl_address_tab_start', $location, $user_id ); ?>
	
	                            <?php echo $this->display_location_fields( 'address_fields', 'table', 'location', false ); ?>
	
	                            <?php do_action( 'gmw_yl_address_tab_end', $location, $user_id ); ?>
	
	                        </div>
							
							<!-- coords tab -->
	                        <div id="gmw-yl-latlng-tab-wrapper" class="gmw-yl-tab-wrapper latlng" style="display:none;">
	
	                            <?php do_action('gmw_yl_latlng_tab_start', $location, $user_id ); ?>
	
	                            <?php echo $this->display_location_fields('latlng_fields', 'table', 'location', false); ?>
	
	                            <?php do_action('gmw_yl_latlng_tab_end', $location, $user_id ); ?>
	
	                        </div>
	
	                        <?php echo do_action( 'gmw_yl_after_tabs_wrapper', $location, $user_id ); ?>
	
	                        <div id="gmw-yl-address-tab-btn-wrapper" class="update-btn-wrapper">
	                            <input type="button" id="gmw-yl-get-latlng" value="<?php echo $fieldsLabel['save_location']; ?>" />
	                        </div>
	
	                        <div id="gmw-yl-latlng-tab-btn-wrapper" class="update-btn-wrapper" style="display:none;">
	                            <input type="button" id="gmw-yl-get-address" value="<?php echo $fieldsLabel['save_location']; ?>" />
	                        </div>
	                
	                	</div>
                        
                    </div>
                    
                    <?php do_action('gmw_yl_form_end', $location, $user_id ); ?>
                    
                    <input type="hidden" id="gmw-yl-update-location" class="" value="" />

                </form>
                
				<div id="gmw-yl-message"><p></p></div>		
				
                <?php do_action('gmw_yl_after_form', $location, $user_id ); ?>

        </div>
        <?php
        wp_enqueue_script( 'gmw-fl' );
    }
}
new GMW_FL_Location_Page;
?>