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

        $this->settings       = get_option('gmw_options');
        $this->displayed_user = $bp->displayed_user->id;
        //get the information of the user from database
        $this->location       = $wpdb->get_row($wpdb->prepare("SELECT * FROM wppl_friends_locator WHERE member_id = %s", $this->displayed_user));

        $this->display_location_form($this->location);

    }

    public function address_fields_init() {

        $this->location_fields = apply_filters('gmw_fl_location_page', array(
            'address_autocomplete' => array(
                __('Address Autocomplete', 'GMW'),
                array(
                    array(
                        'name'        => 'formatted_address',
                        'std'         => '',
                        'id'          => 'gmw-fl-address-autocomplete',
                        'placeholder' => __('Type an address for autocomplete', 'GMW'),
                        'label'       => '',
                        'desc'        => '',
                        'type'        => 'text',
                        'attributes'  => array('style' => 'width:100%')
                    ),
                ),
            ),
            'address_fields'       => array(
                __('Address Fields', 'GMW'),
                array(
                    array(
                        'name'        => 'street',
                        'std'         => '',
                        'id'          => 'gmw-street',
                        'placeholder' => '',
                        'label'       => __('Street', 'GMW'),
                        'desc'        => '',
                        'type'        => 'text',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'apt',
                        'std'         => '',
                        'id'          => 'gmw-apt',
                        'placeholder' => '',
                        'label'       => __('Apt/Suit', 'GMW'),
                        'desc'        => '',
                        'type'        => 'text',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'city',
                        'std'         => '',
                        'id'          => 'gmw-city',
                        'placeholder' => '',
                        'label'       => __('City', 'GMW'),
                        'desc'        => '',
                        'type'        => 'text',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'state',
                        'std'         => '',
                        'id'          => 'gmw-state',
                        'placeholder' => '',
                        'label'       => __('State', 'GMW'),
                        'desc'        => '',
                        'type'        => 'text',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'state_long',
                        'std'         => '',
                        'id'          => 'gmw-state-long',
                        'placeholder' => '',
                        'label'       => __('State Long Name', 'GMW'),
                        'desc'        => '',
                        'type'        => 'hidden',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'zipcode',
                        'std'         => '',
                        'id'          => 'gmw-zipcode',
                        'placeholder' => '',
                        'label'       => __('Zipcode', 'GMW'),
                        'desc'        => '',
                        'type'        => 'text',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'country',
                        'std'         => '',
                        'id'          => 'gmw-country',
                        'placeholder' => '',
                        'label'       => __('Country', 'GMW'),
                        'desc'        => '',
                        'type'        => 'text',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'country_long',
                        'std'         => '',
                        'id'          => 'gmw-country-long',
                        'placeholder' => '',
                        'label'       => __('Country Long Name', 'GMW'),
                        'desc'        => '',
                        'type'        => 'hidden',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'address',
                        'std'         => '',
                        'id'          => 'gmw-address',
                        'placeholder' => '',
                        'label'       => __('address', 'GMW'),
                        'desc'        => '',
                        'type'        => 'hidden',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'formatted_address',
                        'std'         => '',
                        'id'          => 'gmw-formatted-address',
                        'placeholder' => '',
                        'label'       => __('Formatted Address', 'GMW'),
                        'desc'        => '',
                        'type'        => 'hidden',
                        'attributes'  => array('size' => '40')
                    ),
                ),
            ),
            'latlng_fields'        => array(
                __('Latitude Longitude', 'GMW'),
                array(
                    array(
                        'name'        => 'lat',
                        'std'         => '',
                        'id'          => 'gmw-lat',
                        'placeholder' => '',
                        'label'       => __('Latitude', 'GMW'),
                        'desc'        => '',
                        'type'        => 'text',
                        'attributes'  => array('size' => '40')
                    ),
                    array(
                        'name'        => 'long',
                        'std'         => '',
                        'id'          => 'gmw-lng',
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

    public function display_location_fields($section, $tag, $tag_class, $title) {

        $this->address_fields_init();

        $location_fields = $this->location_fields;
        $location        = $this->location;

        if ($tag == 'table') {
            $table = 'table';
            $tr    = 'tr';
            $th    = 'th';
            $td    = 'td';
        } elseif ($tag == 'ul') {
            $table = 'ul';
            $tr    = 'li';
            $th    = 'div';
            $td    = 'div';
        } elseif ($tag == 'ol') {
            $table = 'ol';
            $tr    = 'li';
            $th    = 'div';
            $td    = 'div';
        } elseif ($tag == 'div') {
            $table = 'div';
            $tr    = 'div';
            $th    = 'span';
            $td    = 'span';
        }

        if (isset($title) && $title == true)
            echo '<span id="gmw-fl-' . $section . '-fields-title" class="gmw-fl-location-fields-title">' . $location_fields[$section][0] . '</span>';

        echo '<' . $table . '  class="gmw-fl-location-fields-wrapper ' . $tag_class . '">';

        foreach ($location_fields[$section][1] as $option) {

            $placeholder = (!empty($option['placeholder']) ) ? 'placeholder="' . $option['placeholder'] . '"' : '';
            $class       = !empty($option['class']) ? $option['class'] : '';
            $id          = !empty($option['id']) ? $option['id'] : '';
            $value       = ( isset($location->$option['name']) ) ? $location->$option['name'] : '';
            $attributes  = array();
            $hidden      = ( $option['type'] == 'hidden' ) ? 'style="display:none"' : '';

            if (!empty($option['attributes']) && is_array($option['attributes']))
                foreach ($option['attributes'] as $attribute_name => $attribute_value)
                    $attributes[] = esc_attr($attribute_name) . '="' . esc_attr($attribute_value) . '"';

            echo '<' . $tr . ' ' . $hidden . ' >';

            if (isset($option['label']) && !empty($option['label'])) {

                echo '<' . $th . '>';

                echo '<label for="setting-' . $option['name'] . '" >' . $option['label'] . '</label>';

                echo '</' . $th . '>';
            }
            echo '<' . $td . '>';

            switch ($option['type']) {

                case "checkbox" :
                    ?><label><input id="<?php echo $option['id']; ?>" name="gmw_<?php echo $option['name']; ?>" type="checkbox" value="1" <?php echo implode(' ', $attributes); ?> <?php checked('1', $value); ?> /> <?php echo $option['cb_label']; ?></label><?php
                    break;
                case "textarea" :
                    ?><textarea id="<?php echo $option['id']; ?>" class="large-text" cols="50" rows="3" name="gmw_<?php echo $option['name']; ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?>><?php echo esc_textarea($value); ?></textarea><?php
                break;
                
                case "select" :
                    ?><select id="<?php echo $option['id']; ?>" class="regular-text" name="gmw_<?php echo $option['name']; ?>" <?php echo implode(' ', $attributes); ?>><?php
                    foreach ($option['options'] as $key => $name) {
                        echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($name) . '</option>';
                    }
                    ?></select><?php
                break;
                
                case "password" :
                    ?><input id="<?php echo $option['id']; ?>" class="gmw-fl-input-text" type="password" name="gmw_<?php echo $option['name']; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> /><?php
                break;
                case "text" :
                        ?><input id="<?php echo $option['id']; ?>" class="gmw-fl-input-text" type="text" name="gmw_<?php echo $option['name']; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> /><?php
                    if ($option['desc']) {
                        echo ' <p class="description">' . $option['desc'] . '</p>';
                    }
                break;
                case "hidden" :
                    ?><input id="<?php echo $option['id']; ?>" class="gmw-fl-location-form-hidden-field" type="hidden" name="gmw_<?php echo $option['name']; ?>" value="<?php echo $value; ?>" /><?php
                break;
            }

            echo '</' . $td . '>';

            echo '</' . $tr . '>';
        }

        echo '</' . $table . '>';

    }

    public function display_location_form($location) {
        ?>
        <div id="gmw-fl-location-page">

            <div id="gmw-fl-location-form-wrapper">

                <?php do_action('gmw_fl_location_page_start'); ?>

                <div id="gmw-fl-your-location-wrapper">
                    <span class="gmw-fl-location-fields-title"><?php _e('Your Location', 'GMW'); ?></span>

                    <input type="text" id="gmw-fl-your-location" value="<?php echo ( isset($location->formatted_address) ) ? $location->formatted_address : ''; ?>" disabled="disabled" />

                    <input type="button" id="gmw-fl-location-edit" class="first" value="<?php _e('Edit Location', 'GMW'); ?>" />
                    <input type="button" id="gmw-fl-location-delete" value="<?php _e('Delete Location', 'GMW'); ?>" />
                    <img src="<?php echo GMW_FL_URL . 'assets/images/ajax-loader.gif'; ?>" id="gmw-fl-delete-spinner" style="display:none;width:25px;" alt="" />
                    <div id="gmw-fl-location-delete-message"></div>
                </div>

                <div class="clear"></div>

                <form name="gmw_fl_location_form" method="post" action="" id="gmw-fl-location-form" style="display:none;">

                    <span class="gmw-fl-location-fields-title"><?php _e('Edit your location', 'GMW'); ?></span>

                    <?php do_action('gmw_fl_location_page_locator', $this->location, $this->displayed_user); ?>

                    <input type="button" id="gmw-fl-location-locate-me-btn" value="<?php _e('Get you current location', 'GMW'); ?>" />
                    <img src="<?php echo GMW_FL_URL . 'assets/images/ajax-loader.gif'; ?>" id="gmw-fl-locator-spinner" style="display:none;width:25px;" alt="" />

                    <div class="clear"></div>

                    <?php do_action('gmw_fl_location_page_map', $this->location, $this->displayed_user); ?>

                    <span class="gmw-fl-location-fields-title"><?php _e('Find your location on the map', 'GMW'); ?></span>

                    <div id="gmw-fl-location-map-wrapper">	
                        <div id="gmw-fl-location-map" class="gmw-map" style="height:210px;width:100%;"></div>
                    </div><!-- map holder -->	

                    <?php do_action('gmw_fl_location_page_autocomplete', $this->location, $this->displayed_user); ?>

                    <?php echo $this->display_location_fields('address_autocomplete', 'div', 'location', true); ?>

                    <div>
                        <span class="gmw-fl-location-fields-title"><?php _e('Enter your location manually', 'GMW'); ?></span>

                        <ul id="gmw-fl-location-fields-tabs">

                            <li id="gmw-fl-address-fields-tab" class="gmw-fl-location-tab active"><?php _e('Address', 'GMW'); ?></li>
                            <li id="gmw-fl-latlng-fields-tab" class="gmw-fl-location-tab" ><?php _e('Latitude / Longitude', 'GMW'); ?></li>

                            <?php echo do_action('gmw_fl_location_page_tabs', $this->location, $this->displayed_user); ?>
                        </ul>

                        <div id="gmw-fl-address-fields-tab-wrapper" class="gmw-fl-location-tab-wrapper gmw-fl-address-wrapper">

                            <?php do_action('gmw_fl_address_tab_start', $this->location, $this->displayed_user); ?>

                            <?php echo $this->display_location_fields('address_fields', 'table', 'location', false); ?>

                            <?php do_action('gmw_fl_address_tab_end', $this->location, $this->displayed_user); ?>

                        </div>

                        <div id="gmw-fl-latlng-fields-tab-wrapper" class="gmw-fl-location-tab-wrapper gmw-fl-latlng-wrapper" style="display:none;">

                            <?php do_action('gmw_fl_latlng_tab_start', $this->location, $this->displayed_user); ?>

                            <?php echo $this->display_location_fields('latlng_fields', 'table', 'location', false); ?>

                            <?php do_action('gmw_fl_latlng_tab_end', $this->location, $this->displayed_user); ?>

                        </div>

                        <?php echo do_action('gmw_fl_location_page_after_tabs', $this->location, $this->displayed_user); ?>

                        <div class="clear"></div>

                        <div class="gmw-fl-address-wrapper">
                            <input type="button" id="gmw-fl-get-latlng" value="<?php _e('Update Location', 'GMW'); ?>" style="float:left;" />
                        </div>

                        <div class="gmw-fl-latlng-wrapper" style="display:none;">
                            <input type="button" id="gmw-fl-get-address" value="<?php _e('Update Location', 'GMW'); ?>" style="float:left;" />
                        </div>

                        <div class="gmw-fl-updater-spinner" style="display:none;float:left;margin-left:10px;">
                            <img src="<?php echo GMW_FL_URL . 'assets/images/ajax-loader.gif'; ?>" style="width:20px;margin-right:3px" alt="" /><?php _e('Updating Location...', 'GMW'); ?>
                        </div>

                    </div>

                    <div class="clear"></div>
                    <div style="clear:both;" id="gmw-fl-location-message"></div>		
                    <input type="hidden" id="gmw-fl-update-location" class="" value="" />

                </form>

                <?php do_action('gmw_fl_location_page_end', $this->location, $this->displayed_user); ?>

            </div>

        </div>
        <?php
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('gmw-fl', GMW_FL_URL . 'assets/js/fl.js', array('jquery'), GMW_VERSION, true);

    }

}

new GMW_FL_Location_Page;
?>