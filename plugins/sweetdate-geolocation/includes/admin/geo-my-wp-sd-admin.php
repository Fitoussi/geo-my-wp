<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * GMW_Sweet_Date_Admin class
 */
class GMW_Sweet_Date_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->settings = get_option( 'gmw_options' );
        
        //create post types settings tab/group 
        add_filter( 'gmw_admin_settings_groups', array( $this, 'admin_settings_group' ), 5 );
        add_filter( 'gmw_admin_settings', array( $this, 'settings_init' ) );
        add_filter( 'admin_footer', array( $this, 'footer_script' ) );

        if ( empty( $this->settings['sweet_date'] ) ) {
            add_filter( 'admin_init', array( $this, 'default_options' ) );
        }   
    }
   
    /**
     * Set deafult values if not exists
     * 
     */
    public function default_options() {

    	$this->settings['sweet_date'] = array(
    		'enabled'	    		=> 1,
    		'address_autocomplete'  => 1,
			'radius'     			=> '10,25,50,100,200',
			'units'      			=> '3959',
			'orderby'				=> 1,
			'map'        		    => 1,
			'map_width'  			=> '100%',
			'map_height' 			=> '300px',
			'map_type'   			=> 'ROADMAP',
			'distance'   			=> 1,
			'address'    			=> 1,
			'directions' 			=> 1
    	);
    	
    	update_option( 'gmw_options', $this->settings );
    }

    /**
     * Create Post Types settings group
     * 
     * @param  [type] $groups [description]
     * @return [type]         [description]
     */
    public function admin_settings_group( $groups ) {

        $groups[] = array(
            'id'    => 'sweet_date',
            'label' => __( 'Sweet Date', 'GMW' )
        );  

        return $groups;
    }

    /**
     * addon settings page function.
     *
     * @access public
     * @return $settings
     */
    public function settings_init( $settings ) {

    	$settings['sweet_date'] = array(
    		
    		'features_enabled' => array(
				'name'       => 'features_enabled',
				'type'       => 'checkbox',
				'cb_label'   => 'Enabled',
				'default'    => '',
				'label'      => __( 'Sweet Date Geolocation', 'GMW' ),
				'desc'       => __( 'Here you can completely enable/disable the Geolocation features for the Sweet Date theme.', 'GMW' ),
				'attributes' => array(),
				'priority'	 => 2
			),
			'address_autocomplete' => array(
				'name'       => 'address_autocomplete',
				'type'       => 'checkbox',
				'cb_label'   => 'Enabled',
				'default'    => '',
				'label'      => __( 'Google Places Address Autocomplete', 'GMW' ),
				'desc'       => __( 'Display suggested results via Google address autocomplete while typing an address.', 'GMW' ),
				'attributes' => array(),
				'priority'	 => 5
			),
			'radius'     => array(
				'name'        => 'radius',
				'type'        => 'text',
				'default'     => '10,25,50,100,200',
				'label'       => __( 'Radius', 'GMW' ),
				'placeholder' => __( 'Enter radius values', 'GMW' ),
				'desc'        => __( 'Enter the radius values. Single value to be default value or multiple values comma separated for a drop-down select box.', 'GMW' ),
				'attributes' => array(),
				'priority'	 => 10
			),
			'units'      => array(
				'name'       => 'units',
				'type'       => 'select',
				'default'    => '3959',
				'label'      => __( 'Units', 'GMW' ),
				'desc'       => __( 'Choose Between Miles and Kilometers.', 'GMW' ),
				'options'    => array(
					'3959' => __( 'Miles', 'GMW' ),
					'6371' => __( 'Kilometers', 'GMW' ),
				),
				'attributes' => array(),
				'priority'	 => 15
			),
			'orderby'  	  => array(
				'name'       => 'orderby',
				'type'       => 'checkbox',
				'cb_label'   => 'Enabled',
				'default'    => '',
				'label'      => __( 'Order-by filter', 'GMW' ),
				'desc'       => __( 'Display Order-by dropdown select box.', 'GMW' ),
				'attributes' => array(),
				'priority'	 => 20
			),
			'map'    => array(
				'name'       => 'map',
				'type'       => 'checkbox',
				'default'    => '',
				'cb_label'   => 'Enabled',
				'label'      => __( 'Google Map', 'GMW' ),
				'desc'       => __( 'Show Google map above list of members.', 'GMW' ),
				'attributes' => array(),
				'priority'	 => 25
			),
			'map_width'  => array(
				'name'        => 'map_width',
				'type'        => 'text',
				'default'     => '100%',
				'label'       => __( 'Map Width', 'GMW' ),
				'placeholder' => __( 'Map width in pixels or percentage', 'GMW' ),
				'desc'        => __( 'Maps width in pixels or percentage', 'GMW' ),
				'attributes'  => array(),
				'priority'	  => 30
				
			),
			'map_height' => array(
				'name'        => 'map_height',
				'type'        => 'text',
				'default'     => '300px',
				'label'       => __( 'Map Height', 'GMW' ),
				'placeholder' => __( 'Map height in pixels or percentage', 'GMW' ),
				'desc'        => __( 'Maps height in pixels or percentage', 'GMW' ),
				'attributes'  => array(),
				'priority'	  => 35
			),
			'map_type'   => array(
				'name'        => 'map_type',
				'type'        => 'select',
				'default'     => 'ROADMAP',
				'label'       => __( 'Map Type', 'GMW' ),
				'desc'        => __( 'Map type', 'GMW' ),
				'options'     => array(
					'ROADMAP'   => __( 'ROADMAP', 'GMW' ),
					'SATELLITE' => __( 'SATELLITE', 'GMW' ),
					'HYBRID'    => __( 'HYBRID', 'GMW' ),
					'TERRAIN'   => __( 'TERRAIN', 'GMW' ),
				),
				'attributes'  => array(),
				'priority'	  => 40
			),
			'distance'   => array(
				'name'        => 'distance',
				'type'        => 'checkbox',
				'default'     => '',
				'label'       => __( 'Distance', 'GMW' ),
				'cb_label'    => __( 'Enabled', 'GMW' ),
				'desc'        => __( 'Show the distance for each member in the list of results.', 'GMW' ),
				'attributes'  => array(),
				'priority'	  => 45
			),
			'address'    => array(
				'name'        => 'address',
				'default'     => '',
				'label'       => __( 'Member Address', 'GMW' ),
				'cb_label'    => __( 'Enabled', 'GMW' ),
				'desc'        => __( 'Show the address for each member in the list of results.', 'GMW' ),
				'type'        => 'checkbox',
				'attributes'  => array(),
				'priority'	  => 50
			),
			'directions' => array(
				'name'        => 'directions',
				'type'        => 'checkbox',
				'default'     => '',
				'label'       => __( 'Get directions Link', 'GMW' ),
				'cb_label'    => __( 'Enabled', 'GMW' ),
				'desc'        => __( 'Show get directions link for each memebr in the list of results. The link will open a new page with Google map showing the directions from the address entered by the user to the member\'s location.', 'GMW' ),
				'attributes'  => array(),
				'priority'	  => 55
			),
    	);

    	return $settings;
    }

    /**
     * JavaScripts 
     * @return [type] [description]
     */
    function footer_script() {
    	?>
		<script>
		jQuery( document ).ready( function( $ ) {

			function hideRows() {
				$( "#setting-sweet_date-features_enabled-row" ).show().siblings().toggle();
			}

			if ( ! $( '#setting-sweet_date-features_enabled' ).is( ':checked' ) ) {
				hideRows();
			}

			$( '#setting-sweet_date-features_enabled' ).on( 'change', function() {
				hideRows();
			});
		} );
		</script>
		<?php
    }
}