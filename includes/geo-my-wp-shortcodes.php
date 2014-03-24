<?php

/**
 * User's current location class
 *
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Current_location {

    /**
     * __constructor
     */
    public function __construct() {

        add_shortcode('gmw_current_location', array($this, 'current_location'));
        add_action('wp_enqueue_scripts', array($this, 'register_scripts_frontend'));

        if (!has_action('wp_footer', array($this, 'cl_template')))
            add_action('wp_footer', array($this, 'cl_template'));

        add_action('init', array($this, 'submitted_location'));

    }

    /**
     * Register scripts
     */
    public function register_scripts_frontend() {

        wp_register_script('gmw-cl-js', GMW_URL . '/assets/js/gmw-cl-js.js', array('jquery'), GMW_VERSION, true);

    }

    /**
     * Get current location
     * @param $args
     */
    public function current_location($args) {

        extract(shortcode_atts(array(
            'title'      => '',
            'display_by' => 'city,country',
            'show_name'  => 0
                        ), $args));

        $userAddress = false;

        $location = false;
        $location .= '';
        $location .= '<div class="gmw-cl-wrapper">';

        if ($show_name == 1) {

            if (is_user_logged_in()) {

                global $current_user;
                get_currentuserinfo();
                $hMessage = __('Hello, ', 'GMW') . $current_user->user_login . '!';
            } else {

                $hMessage = __('Hello, guest!', 'GMW');
            }
            $location .= '<div class="gmw-cl-welcome-message">' . $hMessage . '</div>';
        }

        if (isset($_COOKIE['gmw_lat']) && isset($_COOKIE['gmw_lng'])) {

            $userAddress   = array();
            foreach (explode(',', $display_by) as $field)
                if (isset($_COOKIE['gmw_' . $field]))
                    $userAddress[] = urldecode($_COOKIE['gmw_' . $field]);

            if (isset($title) && !empty($title))
                $location .= '<span class="gmw-cl-title">' . $title . ' </span>';

            $location .= '<span class="gmw-cl-location"><a href="#" class="gmw-cl-form-trigger" title="' . __('Your Current Location', 'GMW') . '">' . implode(' ', $userAddress) . '</a></span>';
        } else {

            $location .= '<span class="gmw-cl-title"><a href="#" class="gmw-cl-form-trigger" title="' . __('Your Current Location', 'GMW') . '">';
            $location .=__('Get your current location', 'GMW');
            $location .= '</a></span>';
        }

        if (!wp_script_is('gmw-cl-js', 'enqueue'))
            wp_enqueue_script('gmw-cl-js');

        return apply_filters('gmw_cl_display_widget', $location, $userAddress, $display_by, $title, $show_name);

    }

    public function hidden_form() {

        $form = '<div id="gmw-cl-hidden-form-wrapper">
                    <form id="gmw-cl-hidden-form" method="post">
                        <input type="hidden" id="gmw-cl-street" name="gmw_cl_location[street]" value="" />
                        <input type="hidden" id="gmw-cl-city" name="gmw_cl_location[city]" value="" />
                        <input type="hidden" id="gmw-cl-state" name="gmw_cl_location[state]" value="" />
                        <input type="hidden" id="gmw-cl-state-long" name="gmw_cl_location[state_long]" value="" />
                        <input type="hidden" id="gmw-cl-zipcode" name="gmw_cl_location[zipcode]" value="" />
                        <input type="hidden" id="gmw-cl-country" name="gmw_cl_location[country]" value="" />
                        <input type="hidden" id="gmw-cl-country-long" name="gmw_cl_location[country_long]" value="" />
                        <input type="hidden" id="gmw-cl-formatted-address" name="gmw_cl_location[address]" value="" />
                        <input type="hidden" id="gmw-cl-lat" name="gmw_cl_location[lat]" value="" />
                        <input type="hidden" id="gmw-cl-lng" name="gmw_cl_location[lng]" value="" />
                        <input type="hidden" id="gmw-cl-action" name="gmw_cl_action" value="post" />
                    </form>
                </div>';

        return $form;

    }

    /**
     * Current location form
     */
    public function cl_template() {

        $template = '';
        $template .= '<div id="gmw-cl-form-wrapper">';
        $template .= '<span id="gmw-cl-close-btn">X</span>';
        $template .= '<form id="gmw-cl-form" name="gmw_cl_form" onsubmit="return false">';
        $template .= '<div id="gmw-cl-info-wrapper">';
        $template .= '<div id="gmw-cl-location-message">' . __('- Enter Your Location -', 'GMW') . '</div>';
        $template .= '<div id="gmw-cl-input-fields"><input type="text" name="gmw-cl_address" id="gmw-cl-address" value="" placeholder="zipcode or full address..." /><input type="submit" value="go" /></div>';
        $template .= '<div> - or - </div>';
        $template .= '<div id="gmw-cl-get-location"><a href="#" id="gmw-cl-trigger" >';
        $template .= __('Get your current location', 'GMW');
        $template .= '</a></div>';
        $template .= '</div>';
        $template .= '<div id="gmw-cl-spinner" style="display:none;"><img src="' . GMW_IMAGES . '/gmw-loader.gif" /></div>';
        $template .= '<div id="gmw-cl-message"></div>';
        $template .= '<div id="gmw-cl-map" style="width:100%;height:100px;display:none;"></div>';
        $template .= '</form>';
        $template .= '</div>';

        $template .= $this->hidden_form();

        echo $template;

    }

    /**
     * Submit user current location
     * @param unknown_type $location
     */
    public function submitted_location($location) {

        if (isset($_POST['gmw_cl_action']) && !empty($_POST['gmw_cl_action']))

        //do something with the information
            do_action('gmw_user_current_location_submitted', $_POST['gmw_cl_location'], get_current_user_id());

    }

}

new GMW_Current_Location;
