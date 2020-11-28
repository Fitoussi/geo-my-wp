<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * System info class
 *
 * This class was originally developed for the System Snapshot Report plugin by Reaktiv Studios, thank you.
 *
 * Modified by Eyal Fitoussi to work with GEO my WP.
 * 
 * @Since 3.0
 * 
 */
class GMW_System_Info {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_action( 'gmw_download_system_info', array( $this, 'generate_system_info_file' ) );
    }
    
    /**
     * helper function for number conversions
     *
     * @access public
     * @param mixed $v
     * @return void
     */
    public function num_convt( $v ) {
        $l   = substr( $v, -1 );
        $ret = substr( $v, 0, -1 );

        switch ( strtoupper( $l ) ) {
            case 'P': // fall-through
            case 'T': // fall-through
            case 'G': // fall-through
            case 'M': // fall-through
            case 'K': // fall-through
                $ret *= 1024;
                break;
            default:
                break;
        }

        return $ret;
    }

    /**
     * generate data for report
     *
     */
    public function get_data() {

        // call WP database
        global $wpdb;

        // check for browser class add on
        if ( ! class_exists( 'Browser' ) ) {
            require_once( GMW_PATH . '/includes/libraries/browser.php' );
        }

        // do WP version check and get data accordingly
        $browser = new Browser();
        if ( get_bloginfo( 'version' ) < '3.4' ) :
            $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
            $theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
        else:
            $theme_data = wp_get_theme();
            $theme      = $theme_data->Name . ' ' . $theme_data->Version;
        endif;

        // host name
        $host = false;

        if ( defined( 'WPE_APIKEY' ) ) {
            $host = 'WP Engine';
        } elseif ( defined( 'PAGELYBIN' ) ) {
            $host = 'Pagely';
        } else {
            $host = gethostname();
        }

        // data checks for later
        $frontpage  = get_option( 'page_on_front' );
        $frontpost  = get_option( 'page_for_posts' );
        $mu_plugins = get_mu_plugins();
        $plugins    = get_plugins();
        $active     = get_option( 'active_plugins', array() );

        // multisite details
        $nt_plugins = is_multisite() ? wp_get_active_network_plugins() : array();
        $nt_active  = is_multisite() ? get_site_option( 'active_sitewide_plugins', array() ) : array();

        $ms_sites = null;

        if ( is_multisite() ) {

            if ( function_exists( 'get_sites' ) ) {
                $ms_sites = get_sites();
            } elseif ( function_exists( 'wp_get_sites' ) ) {
                $ms_sites = wp_get_sites();
            }
        }

        // yes / no specifics
        $ismulti    = is_multisite() ? __( 'Yes', 'geo-my-wp' ) : __( 'No', 'geo-my-wp' );
        $safemode   = ini_get( 'safe_mode' ) ? __( 'Yes', 'geo-my-wp' ) : __( 'No', 'geo-my-wp' );
        $wpdebug    = defined( 'WP_DEBUG' ) ? WP_DEBUG ? __( 'Enabled', 'geo-my-wp' ) : __( 'Disabled', 'geo-my-wp' ) : __( 'Not Set', 'geo-my-wp' );
        $tbprefx    = strlen( $wpdb->prefix ) < 16 ? __( 'Acceptable', 'geo-my-wp' ) : __( 'Too Long', 'geo-my-wp' );
        $fr_page    = $frontpage ? get_the_title( $frontpage ).' (ID# '.$frontpage.')'.'' : __( 'n/a', 'geo-my-wp' );
        $fr_post    = $frontpage ? get_the_title( $frontpost ).' (ID# '.$frontpost.')'.'' : __( 'n/a', 'geo-my-wp' );
        $errdisp    = ini_get( 'display_errors' ) != false ? __( 'On', 'geo-my-wp' ) : __( 'Off', 'geo-my-wp' );

        $jquchk     = wp_script_is( 'jquery', 'registered' ) ? $GLOBALS['wp_scripts']->registered['jquery']->ver : __( 'n/a', 'geo-my-wp' );

        $sessenb    = isset( $_SESSION ) ? __( 'Enabled', 'geo-my-wp' ) : __( 'Disabled', 'geo-my-wp' );
        $usecck     = ini_get( 'session.use_cookies' ) ? __( 'On', 'geo-my-wp' ) : __( 'Off', 'geo-my-wp' );
        $useocck    = ini_get( 'session.use_only_cookies' ) ? __( 'On', 'geo-my-wp' ) : __( 'Off', 'geo-my-wp' );
        $hasfsock   = function_exists( 'fsockopen' ) ? __( 'Your server supports fsockopen.', 'geo-my-wp' ) : __( 'Your server does not support fsockopen.', 'geo-my-wp' );
        $hascurl    = function_exists( 'curl_init' ) ? __( 'Your server supports cURL.', 'geo-my-wp' ) : __( 'Your server does not support cURL.', 'geo-my-wp' );
        $hassoap    = class_exists( 'SoapClient' ) ? __( 'Your server has the SOAP Client enabled.', 'geo-my-wp' ) : __( 'Your server does not have the SOAP Client enabled.', 'geo-my-wp' );
        $hassuho    = extension_loaded( 'suhosin' ) ? __( 'Your server has SUHOSIN installed.', 'geo-my-wp' ) : __( 'Your server does not have SUHOSIN installed.', 'geo-my-wp' );
        $openssl    = extension_loaded('openssl') ? __( 'Your server has OpenSSL installed.', 'geo-my-wp' ) : __( 'Your server does not have OpenSSL installed.', 'geo-my-wp' );

        // start generating report
        $report  = '';
        $report .= '<textarea readonly="readonly" id="gmw-system-info-content" name="gmw_system_info_content" style="width:100%;min-height:500px;">';
        
        $report .= '### Begin System Info ###'."\n";
        
        // add filter for adding to report opening
        //$report .= apply_filters( 'gmw_system_info_start', $report );

        if ( $host ) {
            $report .= "\n".'Host Provider:' . "\t\t\t" . $host ."\n";
        }

        $report .= "\n".'---------------------------------------------';
        $report .= "\n\t\t".'** WORDPRESS DATA **'."\n";
        $report .= '---------------------------------------------'."\n";
        $report .= 'Multisite:'."\t\t\t\t".$ismulti."\n";
        $report .= 'SITE_URL:'."\t\t\t\t".site_url()."\n";
        $report .= 'HOME_URL:'."\t\t\t\t".home_url()."\n";
        $report .= 'WP Version:'."\t\t\t\t".get_bloginfo( 'version' )."\n";
        $report .= 'Permalink:'."\t\t\t\t".get_option( 'permalink_structure' )."\n";
        $report .= 'Active Theme:'."\t\t\t".$theme."\n";
        $report .= 'Post Types:'."\t\t\t\t".implode( ', ', get_post_types( '', 'names' ) )."\n";
        $report .= 'Post Stati:'."\t\t\t\t".implode( ', ', get_post_stati() )."\n";
        $report .= 'User Count:'."\t\t\t\t".count( get_users() )."\n";

        $report .= "\n".'---------------------------------------------';
        $report .= "\n\t\t".'** WORDPRESS CONFIG **'."\n";
        $report .= '---------------------------------------------'."\n";
        $report .= 'WP_DEBUG:'."\t\t\t\t".$wpdebug."\n";
        $report .= 'WP Memory Limit:'."\t\t\t".$this->num_convt( WP_MEMORY_LIMIT )/( 1024 ).'MB'."\n";
        $report .= 'Table Prefix:'."\t\t\t\t".$wpdb->base_prefix."\n";
        $report .= 'Prefix Length:'."\t\t\t".$tbprefx.' ('.strlen( $wpdb->prefix ).' characters)'."\n";
        $report .= 'Show On Front:'."\t\t\t".get_option( 'show_on_front' )."\n";
        $report .= 'Page On Front:'."\t\t\t".$fr_page."\n";
        $report .= 'Page For Posts:'."\t\t\t".$fr_post."\n";

        if ( is_multisite() ) :

            $report .= "\n".'---------------------------------------------';
            $report .= "\n\t\t".'** MULTISITE INFORMATION **'."\n";
            $report .= '---------------------------------------------'."\n";
            $report .= 'Total Sites:'."\t\t\t\t".get_blog_count()."\n";

            if ( is_array( $ms_sites[0] ) ) {
                $report .= 'Base Site:'."\t\t\t\t".$ms_sites[0]['domain']."\n";
            } elseif ( is_object( $ms_sites[0] ) ) {
                $report .= 'Base Site:'."\t\t\t\t".$ms_sites[0]->domain."\n";
            }
            
            $report .= 'All Sites:'."\n";
            
            foreach ( $ms_sites as $site ) {
                
                if ( is_array( $site ) ) {
                    
                    if ( $site['path'] != '/' ) {
                        
                        $report .= "\t\t".'- '. $site['domain'].$site['path']."\n";
                    } 

                } elseif ( is_object( $site ) ) {

                   if ( $site->path != '/' ) {
                        
                        $report .= "\t\t".'- '. $site->domain.$site->path."\n";
                    }  
                }
            }

            $report .= "\n";
        endif;

        
        $report .= "\n".'---------------------------------------------';
        $report .= "\n\t\t".'** BROWSER DATA **'."\n";
        $report .= '---------------------------------------------'."\n";
        $report .= 'Platform:'."\t\t\t\t\t".$browser->getPlatform()."\n";
        $report .= 'Browser Name'."\t\t\t". $browser->getBrowser() ."\n";
        $report .= 'Browser Version:'."\t\t\t".$browser->getVersion()."\n";
        $report .= 'Browser User Agent:'."\t\t".$browser->getUserAgent()."\n";
        
       
        $report .= "\n".'---------------------------------------------';
        $report .= "\n\t\t".'** SERVER DATA **'."\n";
        $report .= '---------------------------------------------'."\n";
        $report .= 'jQuery Version'."\t\t\t".$jquchk."\n";
        $report .= 'PHP Version:'."\t\t\t\t".PHP_VERSION."\n";
        $report .= 'MySQL Version:'."\t\t\t".$wpdb->db_version()."\n";
        $report .= 'Server Software:'."\t\t\t".$_SERVER['SERVER_SOFTWARE']."\n";

        $report .= "\n".'---------------------------------------------';
        $report .= "\n\t\t".'** PHP CONFIGURATION **'."\n";
        $report .= '---------------------------------------------'."\n";
        $report .= 'Safe Mode:'."\t\t\t\t".$safemode."\n";
        $report .= 'Memory Limit:'."\t\t\t".ini_get( 'memory_limit' )."\n";
        $report .= 'Upload Max:'."\t\t\t\t".ini_get( 'upload_max_filesize' )."\n";
        $report .= 'Post Max:'."\t\t\t\t".ini_get( 'post_max_size' )."\n";
        $report .= 'Time Limit:'."\t\t\t\t".ini_get( 'max_execution_time' )."\n";
        $report .= 'Max Input Vars:'."\t\t\t".ini_get( 'max_input_vars' )."\n";
        $report .= 'Display Errors:'."\t\t\t".$errdisp."\n";
        $report .= 'Sessions:'."\t\t\t\t".$sessenb."\n";
        $report .= 'Session Name:'."\t\t\t".esc_html( ini_get( 'session.name' ) )."\n";
        $report .= 'Cookie Path:'."\t\t\t\t".esc_html( ini_get( 'session.cookie_path' ) )."\n";
        $report .= 'Save Path:'."\t\t\t\t".esc_html( ini_get( 'session.save_path' ) )."\n";
        $report .= 'Use Cookies:'."\t\t\t\t".$usecck."\n";
        $report .= 'Use Only Cookies:'."\t\t\t".$useocck."\n";
        $report .= 'FSOCKOPEN:'."\t\t\t\t".$hasfsock."\n";
        $report .= 'cURL:'."\t\t\t\t\t".$hascurl."\n";
        $report .= 'SOAP Client:'."\t\t\t\t".$hassoap."\n";
        $report .= 'SUHOSIN:'."\t\t\t\t".$hassuho."\n";
        $report .= 'OpenSSL:'."\t\t\t\t".$openssl."\n";
    
        $report .= "\n".'---------------------------------------------';
        $report .= "\n\t\t".'** PLUGIN INFORMATION **'."\n";
        $report .= '---------------------------------------------'."\n";

        if ( $plugins && $mu_plugins ) :
            $report .= 'Total Plugins:'."\t\t\t\t".( count( $plugins ) + count( $mu_plugins ) + count( $nt_plugins ) )."\n";
        endif;

        // output must-use plugins
        if ( $mu_plugins ) :

            $report .= "\n".'------------------------'."\n";
            $report .= 'Must-Use Plugins: ('.count( $mu_plugins ).')';
            $report .= "\n".'------------------------'."\n";

            foreach ( $mu_plugins as $mu_path => $mu_plugin ) :
                $report .= "\t".'- '.$mu_plugin['Name'] . ' ' . $mu_plugin['Version'] ."\n";
            endforeach;
            $report .= "\n";
        endif;

        // if multisite, grab active network as well
        if ( is_multisite() ) :
            // active network
            $report .= "\n".'-------------------------------'."\n";
            $report .= 'Network Active Plugins: ('.count( $nt_plugins ).')';
            $report .= "\n".'-------------------------------'."\n";

            foreach ( $nt_plugins as $plugin_path ) :
                $plugin_base = plugin_basename( $plugin_path );

                if ( ! array_key_exists( $plugin_base, $nt_active ) )
                    continue;

                $plugin = get_plugin_data( $plugin_path );

                $report .= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
            endforeach;
            $report .= "\n";

        endif;

        // output active plugins
        if ( $plugins ) :

            $report .= "\n".'------------------------'."\n";
            $report .= 'Active Plugins: ('.count( $active ).')';
            $report .= "\n".'------------------------'."\n";

            foreach ( $plugins as $plugin_path => $plugin ) :
                if ( ! in_array( $plugin_path, $active ) )
                    continue;
                $report .= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
            endforeach;
            $report .= "\n";
        endif;

        // output inactive plugins
        if ( $plugins ) :

            $report .= "\n".'------------------------'."\n";
            $report .= 'Inactive Plugins: ('.( count( $plugins ) - count( $active ) ).')';
            $report .= "\n".'------------------------'."\n";

            foreach ( $plugins as $plugin_path => $plugin ) :
                if ( in_array( $plugin_path, $active ) )
                    continue;
                $report .= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
            endforeach;
            $report .= "\n";
        endif;

        $report .= "\n".'---------------------------------------------';
        $report .= "\n\t\t".'** GEO my WP Data **'."\n";
        $report .= '---------------------------------------------'."\n";
        $report .= serialize( gmw_get_options_group() );

        // add filter for end of report
        //$report .= apply_filters( 'gmw_system_info_end', $report );

        // end it all
        $report .= "\n\n".'---------------------------------------------'."\n";
        $report .= "\n".'### End System Info ###';
        $report .= '</textarea>';

        return $report;
    }

    /**
     * generate text file for download
     *
     * @return system report file
     */
    public function generate_system_info_file() {

        if ( ! isset( $_POST['gmw_action'] ) || isset( $_POST['gmw_action'] ) && $_POST['gmw_action'] !== 'download_system_info' ) {
            return;
        }

        // build out filename and timestamp
        $name   = sanitize_title_with_dashes( get_bloginfo( 'name' ), '', 'save' );
        $file   = $name.'-gmw-system-info.txt';

        $now    = time();
        $stamp  = __( 'Report Generated: ', 'geo-my-wp' ).date( 'm-d-Y @ g:i:sa', $now ).' system time';

        $data   = '';
        $data   .= $stamp."\n\n";
        $data   .= wp_strip_all_tags( $_POST['gmw_system_info_content'] );
        $data   .= "\n\n".$stamp;

        nocache_headers();

        header( "Content-type: text/plain" );
        header( 'Content-Disposition: attachment; filename="'.$file.'"' );

        echo $data;

        die();
    }

    /**
     * display settings 
     *
     * @access public
     * @return void
     */
    public function output() {
        global $wpdb, $gmw_options;

        // get theme information
        if ( get_bloginfo( 'version' ) < '3.4' ) {
            $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
            $theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
        } else {
            $theme_data = wp_get_theme();
            $theme      = $theme_data->Name . ' ' . $theme_data->Version;
        }
        ?>     
        <form action="" method="post">
            <p>
                <input type="hidden" name="gmw_action" value="download_system_info">
                <input type="submit" value="<?php _e( 'Download system info file', 'geo-my-wp' ); ?>" class="button button-primary gmw-system-info-download" name="gmw_system_info_download">
                <input type="button" value="<?php _e( 'Highlight Data', 'geo-my-wp' ); ?>" onclick="jQuery( 'textarea#gmw-system-info-content' ).focus().select();" class="button button-secondary" name="">
            </p>
            <p><?php echo $this->get_data(); ?></p>
            <p>
                <input type="submit" value="<?php _e( 'Download system info file', 'geo-my-wp' ); ?>" class="button button-primary gmw-system-info-download" name="gmw_system_info_download">
                <input type="button" value="<?php _e( 'Highlight Data', 'geo-my-wp' ); ?>" onclick="jQuery( 'textarea#gmw-system-info-content' ).focus().select();" class="button button-secondary" name="">
            </p>
        </form>
        <?php
    }
}
