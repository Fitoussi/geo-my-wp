<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * GMW_Forms class.
 */

class GMW_Forms {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->forms_group = 'gmw_forms_group';
        $this->forms       = get_option('gmw_forms');

        if (isset($_GET['gmw_form_action']) && $_GET['gmw_form_action'] == 'new')
            $this->new_form();

        if (isset($_GET['gmw_form_action']) && $_GET['gmw_form_action'] == 'duplicate')
            $this->duplicate_form();

        if (isset($_GET['gmw_form_action']) && $_GET['gmw_form_action'] == 'delete')
            $this->delete_form();

        add_action('admin_init', array($this, 'register_settings'));

    }

    /**
     * Display list of forms or edit form page
     */
    public function output() {

        if (isset($_GET['gmw_form_action']) && $_GET['gmw_form_action'] == 'edit')
            self::form_settings_output();
        else
            self::forms_list_output();

    }

    /**
     * Create new form
     * 
     * @access public
     * @return void
     * 
     */
    private function new_form() {

        $this->forms[$_GET['gmw_form_id']] = array(
            'ID'         => $_GET['gmw_form_id'],
            'addon'      => $_GET['gmw_form_addon'],
            'form_title' => $_GET['gmw_form_title'],
            'form_type'  => $_GET['gmw_form_type'],
            'prefix'     => $_GET['gmw_form_prefix'],
            'ajaxurl'    => GMW_AJAX
        );

        foreach ($this->forms as $key => $option) :

            if (!isset($option['ID']) || empty($option['ID']) || !is_numeric($option['ID']) || !isset($option['form_type']) || empty($option['form_type'])) :
                unset($this->forms[$key]);
            endif;

        endforeach;

        update_option('gmw_forms', $this->forms);

    }

    /**
     * Duplicate form
     * 
     * @access public
     * @return void
     * 
     */
    private function duplicate_form() {

        $newForm       = $this->forms[$_GET['formID']];
        $newForm['ID'] = $_GET['gmw_form_id'];

        if (array_key_exists($newForm['ID'], $this->forms))
            return;

        array_push($this->forms, $newForm);

        foreach ($this->forms as $key => $option) :

            if (!isset($option['ID']) || empty($option['ID']) || !is_numeric($option['ID']) || !isset($option['form_type']) || empty($option['form_type'])) :
                unset($this->forms[$key]);
            endif;

        endforeach;

        update_option('gmw_forms', $this->forms);

    }

    private function delete_form() {

        unset($this->forms[$_GET['formID']]);
        update_option('gmw_forms', $this->forms);

    }

    /**
     * output list of forms
     *
     * @access public
     * @return void
     */
    public function forms_list_output() {

        $this->addons = get_option('gmw_addons');
        $nextFormID   = 1;

        if (!empty($this->forms))
            $nextFormID = key(array_slice($this->forms, -1, 1, TRUE)) + 1;

        //$gmw_forms = get_option( 'gmw_forms' );
        ?>
        <div class="wrap">

        <?php echo GMW_Admin::gmw_credits(); ?>
            <h2 class="gmw-wrap-top-h2"><?php echo _e('GEO my WP Forms', 'GMW'); ?></h2>
            <div class="clear"></div>

            <table class="widefat fixed gmw-tabs-table">
                <thead>
                    <tr>
                        <th style="padding:0px;border-left: 4px solid #7ad03a;padding-left:0px;">

                <h3 style="display: inline-table;color: #555;margin: 0px;background: #F7F7F7;padding: 12px 10px;float: left;margin-right:3px;"><?php _e('Create new form', 'GMW'); ?></h3>

                <?php
                //create and display new form buttons

                /*
                 *  you can add your own button with the filter below. 
                 *  example of array to pass:
                 *  $buttons = array(
                 *  			'name'		 => 'posts',
                 *  			'title' 	 => __('Post Types','GMW'),
                 *  			'link_title' => 'Create new post types form",
                 *  		);
                 */
                $buttons = array();
                $buttons = apply_filters('gmw_admin_new_form_button', $buttons);
                ksort($buttons);

                if (empty($buttons)) {
                    echo '<span><a style="background:#FFE2E2;" class="gmw-nav-tab" href="' . admin_url('admin.php?page=gmw-add-ons') . '" >' . __('You need to activate some add-ons in order to create form', 'GMW') . '</a></span>';
                } else {
                    //display buttons
                    foreach ($buttons as $button) {
                        echo '<span><a style="background: #' . $button['color'] . ';" title="' . $button['link_title'] . '" href="admin.php?page=gmw-forms&gmw_form_action=new&gmw_form_title=' . $button['title'] . '&gmw_form_addon=' . $button['addon'] . '&gmw_form_prefix=' . $button['prefix'] . '&gmw_form_type=' . $button['name'] . '&gmw_form_id=' . $nextFormID . '" class="gmw-nav-tab">' . $button['title'] . '</a></span>';
                    }
                }
                ?>

                </th>
                </tr>
                </thead>  	
            </table>

            <br />
            <table class="widefat">
                <thead>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e('ID', 'GMW'); ?></th>
                        <th scope="col" id="id" class="manage-column"><?php _e('Type', 'GMW'); ?></th>
                        <th scope="col" id="id" class="manage-column"><?php _e('Action', 'GMW'); ?></th>
                        <th scope="col" id="active" class="manage-column"><?php _e('Usage', 'GMW'); ?></th> 

                    </tr>
                </thead>
                <tbody class="list:user user-list">

                    <?php if (!empty($this->forms)) : ?>

                        <?php $rowNumber = 0; ?>

            <?php foreach ($this->forms as $key => $option) : ?>

                <?php if (isset($option['addon']) && !empty($option['addon']) && isset($this->addons[$option['addon']]) && $this->addons[$option['addon']] == 'active') : ?>

                    <?php $alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : ''; ?>

                                <tr class="<?php echo $alternate; ?>" style="height:50px;">
                                    <td>
                                        <span><?php echo $option['ID']; ?></span>
                                    </td>
                                    <td>
                                        <span><a title="<?php __('Edit this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_form_action=edit&gmw_form_title=<?php echo $option['form_title']; ?>&gmw_form_prefix=<?php $button['prefix']; ?>&form_type=<?php echo $option['form_type']; ?>&formID=<?php echo $option['ID']; ?>"><?php echo $option['form_title']; ?></a></span>
                                    </td>

                                    <td>		
                                        <span class="edit">
                                            <a title="<?php __('Edit this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_form_action=edit&gmw_form_title=<?php echo $option['form_title']; ?>&gmw_form_prefix=<?php $button['prefix']; ?>&form_type=<?php echo $option['form_type']; ?>&formID=<?php echo $option['ID']; ?>"><?php _e('Edit', 'GMW'); ?></a> | 
                                        </span>
                                        <span class="edit">
                                            <a title="<?php __('Duplicate this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_form_action=duplicate&form_type=<?php echo $option['form_type']; ?>&formID=<?php echo $option['ID']; ?>&gmw_form_id=<?php echo $nextFormID; ?>"><?php _e('Duplicate', 'GMW'); ?></a> | 
                                        </span>
                                        <span class="edit">
                                            <a title="<?php __('Delete this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_form_action=delete&formID=<?php echo $option['ID']; ?>"><?php _e('Delete', 'GMW'); ?></a>                                                    
                                        </span>	
                                    </td>
                                    <td class="column-title" style="padding: 5px 0px;">
                                        <code>[gmw form="<?php echo $option['ID']; ?>"]</code>
                                    </td>
                                </tr>

                            <?php endif; ?>

                <?php $rowNumber++; ?>

            <?php endforeach; ?>

        <?php else : ?>

                        <tr class="<?php echo $alternate; ?>" style="height:50px;">
                            <td>
                                <span></span>
                            </td>
                            <td>
                                <span><?php _e('You don\'t have any forms yet.', 'GMW'); ?></span>
                            </td>
                            <td class="column-title" style="padding: 5px 0px;">

                            </td>
                            <td>		

                            </td>			          
                        </tr>

        <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="col" id="cb" class="manage-column  column-cb check-column" style="width:50px;padding:11px 10px"><?php _e('ID', 'GMW'); ?></th>
                        <th scope="col" id="id" class="manage-column"><?php _e('Type', 'GMW'); ?></th>
                        <th scope="col" id="active" class="manage-column"><?php _e('Usage', 'GMW'); ?></th> 
                        <th scope="col" id="id" class="manage-column"><?php _e('Action', 'GMW'); ?></th>
                    </tr>
                </tfoot>        
            </table>	
        </div>

        <?php

    }

    /**
     * init form settings function.
     *
     * @access protected
     * @return void
     */
    protected function init_form_settings() {

        $form_settings = apply_filters('gmw_' . $_GET['form_type'] . '_form_settings', array(
            'hidden' => array(
                __('hidden'),
                array(),
            ),
            'search_form'    => array(
                __('Search Form', 'GMW'),
                array(),
            ),
            'search_results' => array(
                __('Search Results', 'GMW'),
                array(),
            ),
            'results_map'    => array(
                __('Map', 'GMW'),
                array(),
            ),
                )
        );

        return $form_settings;

    }

    /**
     * register_settings function.
     *
     * @access public
     * @return void
     */
    public function register_settings() {
        //$this->init_form_settings();

        /* $gmw_options = array();
          foreach ( $this->form_settings as $key => $section ) {

          foreach ( $section[1] as $option ) {

          if ( isset( $option['std'] ) )
          $gmw_options[$key][$option['name']] = $option['std'];
          }
          }

          add_option( 'gmw_options', $gmw_options ); */


        register_setting($this->forms_group, 'gmw_forms', array($this, 'validate'));

    }

    /**
     * Update form
     * 
     * @param $newForm 
     */
    function validate($newForm) {

        foreach ($this->forms as $key => $form) {
            if ($key == key($newForm))
                $this->forms[$key] = $newForm[$key];
        }

        return $this->forms;

    }

    /**
     * output edit form page.
     *
     * @access public
     * @return void
     */
    public function form_settings_output() {
        $this->form_settings = $this->init_form_settings();

        $gmw_forms = get_option('gmw_forms');
        $formID    = $_GET['formID'];
        $form_type = $_GET['form_type'];
        ?>
        <div class="wrap">

        <?php echo GMW_Admin::gmw_credits(); ?>
            <h2 class="gmw-wrap-top-h2">
        <?php echo _e('GEO my WP Form ID ' . $formID, 'GMW'); ?>
                <a class="button action" title="<?php __('Delete this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_form_action=delete&formID=<?php echo $formID; ?>"><?php _e('Delete', 'GMW'); ?></a>                                                    
            </h2>

            <div class="clear"></div>

            <form method="post" action="options.php">

            <?php echo '<input type="hidden" name="gmw_forms[' . $formID . '][ID]" value="' . $gmw_forms[$formID]['ID'] . '" />'; ?>
            <?php echo '<input type="hidden" name="gmw_forms[' . $formID . '][addon]" value="' . $gmw_forms[$formID]['addon'] . '" />'; ?>
            <?php echo '<input type="hidden" name="gmw_forms[' . $formID . '][form_title]" value="' . $gmw_forms[$formID]['form_title'] . '" />'; ?>
            <?php echo '<input type="hidden" name="gmw_forms[' . $formID . '][form_type]" value="' . $gmw_forms[$formID]['form_type'] . '" />'; ?>
            <?php echo '<input type="hidden" name="gmw_forms[' . $formID . '][prefix]" value="' . $gmw_forms[$formID]['prefix'] . '" />'; ?>
            <?php echo '<input type="hidden" name="gmw_forms[' . $formID . '][ajaxurl]" value="' . GMW_AJAX . '" />'; ?>

            <?php settings_fields($this->forms_group); ?>

                <table class="widefat fixed gmw-tabs-table">
                    <thead>
                        <tr>
                            <th class="widgets-holder-wrap closed gmw-nav-tab-wrapper" style="padding:0px;border-left: 4px solid #7ad03a;padding-left:0px;">

                                <?php
                                foreach ($this->form_settings as $key => $section) {
                                    if ($key != 'hidden')
                                        echo '<span><a  href="#settings-' . sanitize_title($key) . '" class="gmw-nav-tab">' . esc_html($section[0]) . '</a></span>';
                                }
                                ?>

                            </th>
                        </tr>
                    </thead>
                </table>
                <br />
                <?php
                if (!empty($_GET['settings-updated'])) {
                    flush_rewrite_rules();
                    echo '<div class="updated fade" style="clear:both"><p>' . __('Settings successfully saved', 'GMW') . '</p></div>';
                }

                foreach ($this->form_settings as $key => $section) {

                    echo '<div id="settings-' . sanitize_title($key) . '" class="settings_panel">';

                    echo '<table class="widefat">
                            <thead>
                                    <tr>
                                            <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:20% !important;padding:11px 10px">' . __('Feature', 'GMW') . '</th>
                                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50%;padding:11px 10px">' . __('Settings', 'GMW') . '</th>
                                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:30%;padding:11px 10px">' . __('Explanation', 'GMW') . '</th>
                            </tr>
                        </thead>';


                    $rowNumber = 0;

                    foreach ($section[1] as $option) {

                        $placeholder     = (!empty($option['placeholder']) ) ? 'placeholder="' . $option['placeholder'] . '"' : '';
                        $class           = !empty($option['class']) ? $option['class'] : '';
                        $value           = ( isset($gmw_forms[$formID][$key][$option['name']]) && !empty($gmw_forms[$formID][$key][$option['name']]) ) ? $gmw_forms[$formID][$key][$option['name']] : $option['std'];
                        $option['type']  = !empty($option['type']) ? $option['type'] : '';
                        $attributes      = array();
                        $checkboxDefault = ( isset($option['std']) && !empty($option['std']) ) ? $option['std'] : 1;

                        if (!empty($option['attributes']) && is_array($option['attributes']))
                            foreach ($option['attributes'] as $attribute_name => $attribute_value)
                                $attributes[] = esc_attr($attribute_name) . '="' . esc_attr($attribute_value) . '"';

                        $alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : '';

                        echo '<tr valign="top" class="' . $class . ' ' . $alternate . '" ><th scope="row" style="width:70px !important;color: #555;border-bottom:1px solid #eee;"><label for="setting-' . $option['name'] . '" >' . $option['label'] . '</label></th><td style="width:70px !important;color: #555;border-bottom:1px solid #eee;">';

                        switch ($option['type']) {

                            case "function" :

                                $function = ( isset($option['function']) && !empty($option['function']) ) ? $option['function'] : $option['name'];

                                do_action('gmw_' . $_GET['form_type'] . '_form_settings_' . $function, $gmw_forms, $formID, $key, $option);

                            break;
                                
                            case "multicheckbox" :
                                foreach ($option['options'] as $keyVal => $name) {
                                    ?><label><input class="setting-<?php echo $option['name']; ?>" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . '][' . $keyVal . ']'; ?>" type="checkbox" value="<?php echo $checkboxDefault; ?>" <?php if (isset($gmw_forms[$formID][$key][$option['name']][$keyVal]) && $gmw_forms[$formID][$key][$option['name']][$keyVal] == $checkboxDefault) echo 'checked="checked"'; ?> /> <?php echo $name; ?></label><br /> <?php
                                }
                            break;
                            
                            case "checkbox" :
                                ?><label><input class="setting-<?php echo $option['name']; ?>" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" type="checkbox" value="<?php echo $checkboxDefault; ?>" <?php echo implode(' ', $attributes); ?> <?php checked($checkboxDefault, $value); ?> /> <?php echo $option['cb_label']; ?></label><?php
                            break;
                            
                            case "textarea" :
                                ?><textarea id="setting-<?php echo $option['name']; ?>" class="large-text" cols="50" rows="3" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?>><?php echo esc_textarea($value); ?></textarea><?php
                            break;
                            
                            case "radio" :

                            $rc = 1;
                            foreach ($option['options'] as $keyVal => $name) {
                                $checked = ( $rc == 1 ) ? 'checked="checked"' : checked($value, $keyVal, false);
                                echo '<input type="radio" class="setting-' . $option['name'] . '" name="gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']" value="' . esc_attr($keyVal) . '" ' . $checked . ' />' . $name . ' ';
                                $rc++;
                            }
                            break;
                            
                            case "select" :
                                ?><select id="setting-<?php echo $option['name']; ?>" class="regular-text" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" <?php echo implode(' ', $attributes); ?>><?php
                                foreach ($option['options'] as $keyVal => $name)
                                    echo '<option value="' . esc_attr($keyVal) . '" ' . selected($value, $keyVal, false) . '>' . esc_html($name) . '</option>';
                                ?></select><?php
                            break;
                            
                            case "password" :
                                ?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="password" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> /><?php
                            break;
                            
                            case "hidden" :
                                ?><input class="gmw-form-hidden-field" id="setting-<?php echo $option['name']; ?>" class="regular-text" type="hidden" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" value="<?php echo $value; ?>" /><?php
                            break;
                                
                            default :
                                ?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo 'gmw_forms[' . $formID . '][' . $key . '][' . $option['name'] . ']'; ?>" value="<?php esc_attr_e($value); ?>" <?php echo implode(' ', $attributes); ?> <?php echo $placeholder; ?> /><?php
                            break;
                            }

                            echo '</td>';
                            echo '<td style="color: #555;border-bottom:1px solid #eee;">';

                            if (isset($option['desc']) && !empty($option['desc']))
                                echo ' <p class="description">' . $option['desc'] . '</p>';

                            echo '</td>';
                        echo '</tr>';

                        $rowNumber++;
                        }

                        echo '<tfoot>
                                <tr style="height:40px;">
                                <th scope="col" id="cb" class="manage-column  column-cb check-column" style="width:50px;padding:11px 10px">
                                                        <input type="submit" class="button-primary" value="' . __('Save Changes', 'GMW') . '" />
                                                </th>
                                <th scope="col" id="id" class="manage-column"></th>
                                <th scope="col" id="active" class="manage-column"></th> 	
                                </tr>
                                </tfoot>
                        </table>
                        </div>';
                    }
                    ?>

            </form>
        </div>
        <script type="text/javascript">
            jQuery('.gmw-form-hidden-field').each(function() {
                jQuery(this).closest('tr').hide();
            });

            jQuery('.gmw-nav-tab-wrapper a').click(function() {
                jQuery('.settings_panel').hide();
                jQuery('.gmw-nav-tab-active').css('background', '#eee');
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
