<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Register_Addon' ) ) {
    return;
}

/**
 * Current Location addon
 * 
 */
class GMW_Sweetdate_Geolcation_Addon extends GMW_Register_Addon {
    
    /**
     * Slug 
     * 
     * @var string
     */
    public $slug = "sweetdate_geolocation";

    /**
     * Name
     * 
     * @var string
     */
    public $name = "Sweet Date Geolocation";

     /**
     * Description
     * 
     * @var string
     */
    public $description = "Enhance the Sweet Date theme with geolocation features.";

    /**
     * prefix
     * 
     * @var string
     */
    public $prefix = "sd";

    // version
    public $version = GMW_VERSION;
     
    /**
     * Path
     * 
     * @var [type]
     */
    public $full_path = __FILE__;
    
    /**
     * Is core add-on
     * 
     * @var boolean
     */
    public $is_core = true;
    
    /**
     * required extensions
     * @var array
     */
    public function required() {

        return array( 
            'theme' => array(
                'template' => 'sweetdate',
                'notice'   => sprintf( __( 'Sweet Date Geolocation extension requires the Sweet Date theme. The theme can be purchased separately from <a href="%s" target="_blank">here</a>.' ), 'https://themeforest.net/item/sweet-date-more-than-a-wordpress-dating-theme/4994573?ref=GEOmyWP', 'GMW' ),
            ),
            'addons' => array(
                array(
                    'slug'    => 'members_locator',
                    'notice'  => __( 'Sweet Date Geolocation extension requires the Members Locator core extension.', 'GMW' )
                )
            )
        );
    }

    /**
     * Register scripts
     * 
     * @return [type] [description]
     */
    public function enqueue_scripts() {

        if ( ! IS_ADMIN ) {
    	   wp_register_script( 'gmw-sd', GMW_SD_URL . '/assets/js/gmw.sd.js', array( 'jquery', 'gmw' ), GMW_VERSION, true );
        } 
    }

    /**
     * Disable activation in add-ons page if BP is not installed.
     * 
     * @return [type] [description]
     */
    /*
    public function disable_activation() {

        // verify if Members Locator add-on activated
        $this->verify_activation = gmw_is_addon_active( 'members_locator' ) ? true : false;

        if ( ! $this->verify_activation ) {

            return $this->deactivation_message = __( 'Sweetdate Geolocation extension requires the Members Locator core extension.', 'GMW' );

        // verify Sweet Date theme
        } else {

            $this->verify_activation = get_option( 'template' ) != 'sweetdate' ? false : true;

            if ( ! $this->verify_activation ) {

                return $this->deactivation_message = sprintf( __( 'Sweetdate Geolocation add-on requires the Sweet Date theme. The theme can be purchased separately from <a href="%s" target="_blank">here</a>.' ), 'https://themeforest.net/item/sweet-date-more-than-a-wordpress-dating-theme/4994573?ref=GEOmyWP', 'GMW' );
            }

        }

        return false;
    }
*/
    /**
     * MDeactivation notice
     * 
     * @return [type] [description]
     */
    /*
    public function deactivation_notice() {
        ?>
        <div class="error">
            <p><?php echo $this->deactivation_message; ?></p>
        </div> 
        <?php 
    }
*/
    /**
     * Verify BuddyPress plugin
     * 
     * @return [type] [description]
     */
    /*
    public function verify_activation() {

        if ( ! $this->verify_activation ) {
            
            add_action( 'admin_notices', array( $this, 'deactivation_notice' ) );

            // deactivate addon
            if ( IS_ADMIN ) {
                $this->deactivate_addon();
            }

            return false;

        } else {

            return true;
        }
    }*/

    /**
     * Run on BuddyPress init
     * 
     * @return void
     */
    public function pre_init() {

    	add_action( 'bp_init', array( $this, 'sd_init' ), 20 );
	}

	/**
	 * Load add-on
	 * 
	 * @return [type] [description]
	 */
    public function sd_init() {

    	//admin settings
		if ( is_admin() ) {

			include( 'includes/admin/geo-my-wp-sd-admin.php' );

			new GMW_Sweet_Date_Admin;
		}

		//include members query only on members page
		if ( bp_current_component() == 'members' && gmw_get_option( 'sweet_date','features_enabled', '' ) ) {

			include( 'includes/class-gmw-sweet-date-search-query.php' );

			$gmw_sd_class = new GMW_Sweet_Date_Search_Query;
		}
    }
}
new GMW_Sweetdate_Geolcation_Addon();