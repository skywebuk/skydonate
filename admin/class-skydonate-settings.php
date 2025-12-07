<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SkyDonation_Functions {
    
    public function __construct() {
        
        add_action( 'wp_ajax_skydonation_general_settings', [ $this, 'skydonation_general_settings' ] );
        add_action( 'wp_ajax_skydonation_currency_changer_settings', [ $this, 'skydonation_currency_changer_settings' ] );
        add_action( 'wp_ajax_skydonation_advanced_settings', [ $this, 'skydonation_advanced_settings' ] );
        add_action( 'wp_ajax_skydonation_widget_save_setting', [ $this, 'skydonation_widget_save_setting' ] );
        add_action( 'wp_ajax_skydonation_fees_settings', [ $this, 'skydonation_fees_settings' ] );
        add_action( 'wp_ajax_save_skydonate_gift_aid_settings', [ $this, 'save_skydonate_gift_aid_settings' ] );
        add_action( 'wp_ajax_save_address_autoload_settings', [ $this, 'save_address_autoload_settings' ] );
        add_action( 'wp_ajax_save_skydonation_color_settings', [ $this, 'save_skydonation_color_settings' ] );
        add_action( 'wp_ajax_skydonation_api_settings', [ $this, 'skydonation_api_settings' ] );
        add_action( 'wp_ajax_skydonation_notification_settings', [ $this, 'save_notification_settings' ] );

        add_action( 'wp_ajax_skydonation_extra_donation_settings', [ $this, 'save_extra_donation_settings' ] );
    }
    
    public function save_extra_donation_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Donation items (array of id + amount + title)
        $donation_items = isset($_POST['donation_items']) ? (array) $_POST['donation_items'] : [];

        // Sanitize each donation item
        $clean_items = [];
        foreach ($donation_items as $item) {
            $clean_items[] = [
                'id'     => isset($item['id']) ? absint($item['id']) : 0,
                'amount' => isset($item['amount']) ? floatval($item['amount']) : 0,
                'title'  => isset($item['title']) ? sanitize_text_field($item['title']) : '',
            ];
        }

        update_option('skydonation_extra_donation_items', $clean_items);

        // Respond
        wp_send_json_success(__('Settings saved successfully.', 'skydonate'));
    }

    
    public function skydonation_advanced_settings() {
        // Initialize the array for title prefixes
        $select_title_prefix = array();

        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'skydonation_settings_nonce')) {
            wp_send_json_error(__('Security check failed.', 'skydonate'));
            return;
        }

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();

        // Advanced settings keys matching your HTML panel
        $allowed_keys = array(
            'donation_monthly_heart_icon',
            'register_text_replacements',
            'init_woocommerce_customizations',
            'init_order_postcode_search',
            'init_menu_label_changes',
            'init_stripe_order_meta_modifications',
            'init_guest_checkout_for_existing_customers',
            'add_checkout_password_note',
            'init_project_column_for_orders',
            'init_email_product_name_replacement',
            'customize_woocommerce_login_message',
            'init_checkout_email_typo_correction',
            'init_guest_checkout_data_saver',
        );

        // Map form data to key => value
        $received_data = array_column($formData, 'value', 'name');

        // Update options
        foreach ($allowed_keys as $key) {
            $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : '';
            update_option($key, $value);
        }
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonate'));
    }


    public function skydonation_general_settings() {
        // Initialize the array for notification donations
        $select_title_prefix = array();

        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();

        // Filter out the 'select_title_prefix' values from formData
        foreach ($formData as $item) {
            if (isset($item['name'], $item['value']) && $item['name'] === 'select_title_prefix') {
                // Ensure that the 'value' is sanitized and valid
                $value = sanitize_text_field($item['value']);
                if (!empty($value)) {
                    $select_title_prefix[$value] = ucfirst($value);
                }
            }
        }
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'enable_sky_donations_module',
            'enable_custom_login_form',
            'checkout_custom_field_style',
            'recent_donation_list_with_country',
            'auto_complete_processing',
            'enable_donation_goal',
            'enable_title_prefix',
            'select_title_prefix',
        );
    
        // Initialize a mapping for received values
        $received_data = array_column($formData, 'value', 'name');
    
        // Iterate over allowed keys to update options
        foreach ($allowed_keys as $key) {
            if ($key === 'select_title_prefix') {
                // Update the options only for 'select_title_prefix'
                update_option($key, $select_title_prefix);
            } else {
                // For other keys, sanitize the values before updating
                $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
                update_option($key, $value);
            }
        }
		do_action('general_settings_field_save',$formData);
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonate'));
    }
    
    
    public function skydonation_currency_changer_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
        $baseCurrency = get_option('woocommerce_currency');
    
        // Initialize selected currencies array
        $skydonate_selected_currency = array();
    
        // Extract selected currency values
        foreach ($formData as $item) {
            if (isset($item['name'], $item['value']) && $item['name'] === 'skydonate_selected_currency[]') {
                $value = sanitize_text_field($item['value']);
                if (!empty($value)) {
                    $skydonate_selected_currency[] = strtoupper($value);
                }
            }
        }

        // Ensure base currency is included
        if (!in_array($baseCurrency, $skydonate_selected_currency)) {
            $skydonate_selected_currency[] = $baseCurrency;
        }

        // Ensure unique values
        $skydonate_selected_currency = array_unique($skydonate_selected_currency);
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'skydonate_currency_changer_enabled',
            'skydonate_selected_currency',
            'skydonate_geo_currency_enabled',
            'skydonate_geo_currency_mode',
            'skydonate_geo_default_all',
        );
    
        // Map formData into key => value pairs
        $received_data = array_column($formData, 'value', 'name');
    
        // Update the options
        foreach ($allowed_keys as $key) {
            if ($key === 'skydonate_selected_currency') {
                update_option($key, $skydonate_selected_currency);
            } elseif ($key === 'skydonate_currency_changer_enabled' || $key === 'skydonate_geo_currency_enabled' || $key === 'skydonate_geo_default_all') {
                $value = isset($received_data[$key]) ? 1 : 0;
                update_option($key, $value);
            } elseif ($key === 'skydonate_geo_currency_mode') {
                $value = isset($received_data[$key]) ? sanitize_text_field($received_data[$key]) : 'all';
                update_option($key, $value);
            } else {
                $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
                update_option($key, $value);
            }
        }
    
        // Respond with success
        wp_send_json_success(__('Currency settings saved successfully.', 'skydonate'));
    }

    public function skydonation_fees_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'enable_donation_fees',
            'donation_fee_percentage',
            'additional_text',
            'checkbox_label',
            'fees_tooltip_text',
            'fees_checkbox_default_status'
        );
    
        // Initialize a mapping for received values
        $received_data = array_column($formData, 'value', 'name');
    
        // Iterate over allowed keys to update options
        foreach ($allowed_keys as $key) {
            $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
            update_option($key, $value);
        }
    
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonate'));
    }
    
    public function save_address_autoload_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();


        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'address_autoload_status',
            'address_autoload_api_key',
            'address_autoload_placeholder',
            'address_autoload_label',
            'address_autoload_provider',
            'address_autoload_address2_mode',
        );

        // Initialize a mapping for received values
        $received_data = array_column($formData, 'value', 'name');

        // Iterate over allowed keys to update options
        foreach ($allowed_keys as $key) {
            // For other keys, sanitize the values before updating
            $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
            update_option($key, $value);
        }

        // Respond with success
        wp_send_json_success(__('Address Autocomplete settings saved successfully.', 'skydonate'));
    }

    public function save_skydonation_color_settings() {
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        $formData = $_POST['formData'] ?? [];

        $allowed_keys = [
            'skydonation_accent_color',
            'skydonation_accent_dark_color',
            'skydonation_accent_light_color'
        ];

        $received_data = array_column($formData, 'value', 'name');

        foreach ($allowed_keys as $key) {
            $value = isset($received_data[$key]) ? sanitize_text_field($received_data[$key]) : '';
            update_option($key, $value);
        }

        wp_send_json_success(__('Accent color settings saved successfully.', 'skydonate'));
    }



    public function save_skydonate_gift_aid_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Get and parse form data
        $formData = $_POST['formData'] ?? array();
        $received_data = array_column($formData, 'value', 'name');

        // Define allowed option keys for Gift Aid settings
        $allowed_keys = array(
            'enable_gift_aid',
            'gift_aid_description',
            'gift_aid_checkbox_label',
            'gift_aid_note',
            'gift_aid_default_status',
            'gift_aid_logo'
        );

        // Loop through allowed keys and update WordPress options
        foreach ($allowed_keys as $key) {
            $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : '';
            update_option($key, $value);
        }

        // Send success response
        wp_send_json_success(__('Gift Aid settings saved successfully.', 'skydonate'));
    }

    public function skydonation_api_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'zakat_calc_api',
            'currencyapi_key'
        );
    
        // Initialize a mapping for received values
        $received_data = array_column($formData, 'value', 'name');
    
        // Iterate over allowed keys to update options
        foreach ($allowed_keys as $key) {
            $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
            update_option($key, $value);
        }
    
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonate'));
    }
    
    public function save_notification_settings() {
        // Initialize the array for notification donations
        $notification_select_donations = array();

        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
    
        // Filter out the 'notification_select_donations' values from formData
        foreach ($formData as $item) {
            if (isset($item['name']) && $item['name'] == 'notification_select_donations') {
                // Ensure that the 'value' is numeric and valid
                if (is_numeric($item['value'])) {
                    $notification_select_donations[] = $item['value'];
                }
            }
        }
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'notification_select_donations',
            'supporter_name_display_style',
            'enable_emoji_notifications',
            'enable_location_visibility',
            'enable_title_visibility',
            'enable_timestamp_display',
            'notification_limit',
            'start_date_range',
            'notifi_start_time',
            'notifi_visible_time',
            'notifi_gap_time',
            'show_element_urls',
            'hide_element_urls',
        );
    
        // Initialize a mapping for received values
        $received_data = array_column($formData, 'value', 'name');
    
        // Iterate over allowed keys to update options
        foreach ($allowed_keys as $key) {
            if ($key === 'notification_select_donations') {
                // Update the options only for 'notification_select_donations'
                update_option($key, $notification_select_donations);
            } else {
                // For other keys, sanitize the values before updating
                $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
                update_option($key, $value);
            }
        }
    
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonate'));
    }
    
    
    public function skydonation_widget_save_setting() {
        // Check the AJAX nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonate'));
            return;
        }

        // Get and sanitize the posted data
        $widgets = isset($_POST['widgets']) ? $_POST['widgets'] : [];
        if (!empty($widgets) && is_array($widgets)) {
            // Sanitize each element
            $sanitized_widgets = array_map('wp_kses_post', $widgets);
            // Update the option with sanitized data
            update_option('skydonation_widgets', $sanitized_widgets);
            wp_send_json_success(__('Settings saved successfully!', 'skydonate'));
        } else {
            wp_send_json_error(__('Nothing to save.', 'skydonate'));
        }
        exit;
    }

    public static function render_components( $components ) {
        if ( ! is_array( $components ) || empty( $components ) ) {
            return;
        }

        foreach ($components as $component) {
            $name 			= array_key_exists('name', $component) ? $component['name'] : $component['id'];
            $button_id 		= array_key_exists('button_id', $component) ? $component['button_id'] : "";
            $button_text 	= array_key_exists('button_text', $component) ? $component['button_text'] : "";
            $description 	= isset($component['desc']) && !is_null($component['desc']) ? $component['desc'] : '';
            $class 			= array_key_exists('class', $component) ? esc_attr($component['class']) : '';
            $wrapper_class 	= array_key_exists('wrapper_class', $component) ? esc_attr($component['wrapper_class']) : '';
            $placeholder 	= array_key_exists('placeholder', $component) ? esc_attr($component['placeholder']) : '';
            $multiple 		= array_key_exists('multiple', $component) ? true : false;
            $multicheck 	= array_key_exists('multicheck', $component) ? true : false;
            $value 			= (isset($component['value']) && !empty($component['value'])) ? $component['value'] : (isset($component['default']) ? $component['default'] : "");
            $row 			= isset($component['row']) && !is_null($component['row']) ? $component['row'] : '5';
            $input_after	= isset($component['input_after']) ? $component['input_after'] : '';
            $input_before 	= isset($component['input_before']) ? $component['input_before'] : '';
            $checked 		= array_key_exists('checked', $component) ? $component['checked'] : '';
            $required 		= isset($component['required']) ? 'required' : '';
            $disabled 		= (isset($component['valid']) && $component['valid'] == 1) ? '' : 'disabled';

            switch ($component['type']) {
                case 'title':
                    ?>
                    <h4 class="<?php echo $class; ?>" ><?php echo esc_html($component['label']); ?></h4>
                    <?php
                    break;
                case 'section_seperate':
                    ?>
                    <p class="section-seperate" ></p>
                    <?php
                    break;
                case 'html':
                    ?>
                    <h4 class="<?php echo $class; ?>" ><?php echo esc_html($component['label']); ?></h4>
                    <?php
                    echo $value;
                    break;
                    
                case 'hidden':
                    ?>
                    <input type="hidden" id="<?php echo esc_attr($component['id']); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>"/>
                    <?php
                    break;
                case 'link':
                    
                    ?>
                    <tr class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined">
                                <?php
                                $url = sprintf('<a href="%s">%s</a>',$component['url'],$component['label']);
                                echo $url;
                                ?>
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                    case 'price':
                    $price_prefix = get_apca_currency_symbol();
                    ?>
                    <tr class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined">
                                <span><?php echo $price_prefix; ?></span>
                                <input 
                                    class="text-field__input <?php echo $class; ?>" 
                                    name="<?php echo esc_attr($name); ?>"
                                    id="<?php echo esc_attr($component['id']); ?>"
                                    type="number"
                                    value="<?php echo $value; ?>"
                                    placeholder="<?php echo $placeholder; ?>"
                                    size="20"
                                    <?php echo $required;?>
                                    >
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'number':
                    ?>
                    <tr class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined">
                                <span class="input-before"><?php echo $input_before; ?></span>
                                <input 
                                    class="text-field__input <?php echo $class; ?>" 
                                    name="<?php echo esc_attr($name); ?>"
                                    id="<?php echo esc_attr($component['id']); ?>"
                                    type="<?php echo esc_attr($component['type']); ?>"
                                    value="<?php echo $value; ?>"
                                    placeholder="<?php echo $placeholder; ?>"
                                    <?php echo $required;?>
                                    >
                                    <?php echo esc_attr($input_after); ?>
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'email':
                    ?>
                    <tr class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined">
                                <span class="input-before"><?php echo $input_before; ?></span>
                                <input 
                                    class="text-field__input <?php echo $class; ?>" 
                                    name="<?php echo esc_attr($name); ?>"
                                    id="<?php echo esc_attr($component['id']); ?>"
                                    type="<?php echo esc_attr($component['type']); ?>"
                                    value="<?php echo $value; ?>"
                                    placeholder="<?php echo $placeholder; ?>"
                                    <?php echo $required;?>
                                    >
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'media':
                    ?>
                    <tr class="form-group <?php echo esc_attr($component['type']); ?> <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined">
                                <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>" class="regular-text"/>
                                <button type="button" data-value="<?php echo esc_attr($name); ?>" class="skydonation-button upload-media">Upload</button>
                        </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'text':
                    ?>
                    <tr class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined">
                                <span class="input-before"><?php echo $input_before; ?></span>
                                <input 
                                    class="<?php echo $class; ?>" 
                                    name="<?php echo esc_attr($name); ?>"
                                    id="<?php echo esc_attr($component['id']); ?>"
                                    type="<?php echo esc_attr($component['type']); ?>"
                                    value="<?php echo $value; ?>"
                                    placeholder="<?php echo $placeholder; ?>"
                                    <?php echo $required;?>
                                >
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                    case 'date':
                    ?>
                    <tr class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined">
                                <span class="input-before"><?php echo $input_before; ?></span>
                                <input 
                                    class="<?php echo $class; ?>" 
                                    name="<?php echo esc_attr($name); ?>"
                                    id="<?php echo esc_attr($component['id']); ?>"
                                    type="<?php echo esc_attr($component['type']); ?>"
                                    value="<?php echo $value; ?>"
                                    placeholder="<?php echo $placeholder; ?>"
                                    <?php echo $required;?>
                                    >
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'textarea':
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_attr($component['label']); ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined text-field-textarea">
                                <span class="text-field-resizer">
                                <span class="input-before"><?php echo $input_before; ?></span>
                                    <textarea class="<?php echo $class; ?>" rows="<?php echo $row; ?>" cols="60" aria-label="Label" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($component['id']); ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $required;?>><?php echo esc_textarea($value); // WPCS: XSS ok.       ?></textarea>
                                </span>
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'codeEditor':
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_attr($component['label']); ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined text-field-textarea" for="text-field-hero-input">
                                <textarea class="text-field-textarea <?php echo $class; ?>" rows="<?php echo $row; ?>" cols="25" aria-label="Label" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($component['id']); ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $required;?>><?php echo $value; // WPCS: XSS ok.       ?></textarea>
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'wysiwyg':

                    // default settings
                    $content = $value;
                    $editor_id = $component['id'];
                    $editor_settings = array(
                        'wpautop' => false, // use wpautop?
                        'media_buttons' => false, // show insert/upload button(s)
                        'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
                        'textarea_rows' => get_option('default_post_edit_rows', 10), // rows="..."
                        'tabindex' => '',
                        'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
                        'editor_class' => '', // add extra class(es) to the editor textarea
                        'teeny' => false, // output the minimal editor config used in Press This
                        'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
                        'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                        'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
                    );
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_attr($component['label']); ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="text-field text-field-outlined text-field-textarea"  for="text-field-hero-input">
                                <?php
                                $size = isset($component['size']) && !is_null($component['size']) ? $component['size'] : '500px';
                                echo '<div style="max-width: ' . $size . ';">';
                                wp_editor($value, esc_attr($component['id']), $editor_settings);
                                echo '</div>';
                                ?>
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'select':
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_html($component['label']); ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="form-select">
                                <span class="input-before"><?php echo $input_before; ?></span>
                                <select name="<?php echo esc_attr($name); ?><?php echo ( true === $multiple ) ? '[]' : ''; ?>" id="<?php echo esc_attr($component['id']); ?>" class="mdl-textfield__input <?php echo $class; ?>" <?php echo ($multiple == true) ? 'multiple="multiple"' : ''; ?> <?php echo $required;?> <?php echo $disabled;?>>
                                    <?php
                                    $multiple_value = $value;
                                    foreach ($component['options'] as $field_key=>$field_value) {
                                        ?>
                                        <option value="<?php echo strtolower($field_key); ?>"
                                        <?php
                                        if (is_array($component['value']) && $multiple == true) {
                                            selected(in_array(strtolower(strtolower($field_key)), $multiple_value), true);
                                        } else {
                                            selected(trim(strtolower($component['value'])),trim(strtolower($field_key)));
                                        }
                                        ?>>
                                        <?php echo esc_html($field_value); ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <div class="text-field-helper-line">
                                    <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                    case 'options':
                    $option_values = $value;
                    
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_html($component['label']); ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="form-select">
                                <input type="hidden" name="booking_quote_custom_field_options" value="1">
                                <table class="table" width="100%" id="options-table">
                                    <thead>
                                        <tr>
                                            <td><?php _e('Value');?></td>
                                            <td><?php _e('Text');?></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $key = 0;
                                        if(!empty($option_values)):
                                        
                                        foreach($option_values as $option_value=>$option_text):
                                        $key++;
                                        ?>
                                        <tr>
                                            <td><input type="text" name="option_value[]" value="<?php echo $option_value;?>" /></td>
                                            <td><input type="text" name="option_text[]" value="<?php echo $option_text;?>" /></td>
                                            <?php if($key >1):?>
                                                <td><button type="button" class="remove-options small-text"><?php _e('Remove','apca');?></button></td>
                                            <?php endif;?>
                                        </tr>
                                        <?php endforeach;?>
                                        <?php else:?>
                                        <tr>
                                            <td><input type="text" name="option_value[]" /></td>
                                            <td><input type="text" name="option_text[]" class="" /></td>
                                            <?php if($key >1):?>
                                                <td><button type="button" class="remove-options small-text"><?php _e('Remove','apca');?></button></td>
                                            <?php endif;?>
                                        </tr>
                                        <?php endif;?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3"><button type="button" class="add-options"><?php _e('Add more','apca');?></button></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="text-field-helper-line">
                                    <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                    
                case 'checkbox':
                
                ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="form-field">
                                <?php if ($multicheck) { 
                                    $multicheck_value = get_post_meta($post_id, trim($component['id']), true);
                                    foreach ($component['options'] as $key => $checkbox_value) {
                                        $checked_value = isset($multicheck_value[$key]) ? $multicheck_value[$key] : "";
                                        ?>
                                        <label class="checkbox-switch">
                                            <input 
                                                type="checkbox"
                                                name="<?php echo trim(esc_attr($name)); ?>[<?php echo $key; ?>]"
                                                id="<?php echo esc_attr($component['id'] . '_' . $key); ?>"
                                                value="<?php echo esc_attr($checkbox_value); ?>"
                                                <?php checked(trim($checked_value), trim($checkbox_value)); ?>
                                            >
                                            <span class="switch"></span>
                                            <small><?php echo esc_html($checkbox_value); ?></small>
                                        </label>
                                    <?php } 
                                } else { ?>
                                    <label class="checkbox-switch">
                                        <input 
                                            type="checkbox"
                                            name="<?php echo esc_attr($name); ?>"
                                            id="<?php echo esc_attr($component['id']); ?>"
                                            value="<?php echo esc_attr($value); ?>"
                                            <?php if (!$disabled) { checked($checked, $value); } ?>
                                            <?php echo $required; ?>
                                            <?php echo $disabled; ?>
                                        >
                                        <span class="switch"></span>
                                        <small><?php echo esc_html($description); ?></small>
                                    </label>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                    
                    break;
                case 'radio':
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); ?></label>
                        </th>
                        <td class="form-group-control">
                            <div class="form-field">
                                <?php if ($multicheck) { ?>

                                    <?php

                                    
                                    foreach ($component['options'] as $checkbox_value) {
                                    
                                        ?>
                                        <div class="checkbox">
                                            <label>
                                            <input 
                                                name="<?php echo esc_attr($name); ?>"
                                                id="<?php echo esc_attr($component['id']); ?>"
                                                type="radio"
                                                class="checkbox-native-control <?php echo $class; ?>"
                                                value="<?php echo $checkbox_value; ?>"
                                                <?php checked(trim($value), trim($checkbox_value)); ?> 
                                                />
                                                <?php
                                                echo $checkbox_value;
                                                ?>
                                                </label>
                                            </div>
                                            <?php }  ?>
                                
                                    <?php echo esc_html($description); // WPCS: XSS ok.  ?>

                                    <?php
                                } else {
                                    $checked;
                                    //echo esc_attr(get_post_meta(743, '_quote_rides_shortcode_enforce_autocomplete_restriction', true));
                                    ?>
                                    <div class="checkbox">
                                        <span class="input-before"><?php echo $input_before; ?></span>
                                        <input 
                                            name="<?php echo esc_attr($name); ?>"
                                            id="<?php echo esc_attr($component['id']); ?>"
                                            type="radio"
                                            class="checkbox-native-control <?php echo $class; ?>"
                                            value="<?php echo $value; ?>"
                                            <?php
                                            if ('1' === $checked) {
                                                checked($checked, $value);
                                            }
                                            ?> <?php echo $required;?>/>

                                        <?php echo esc_html($description); // WPCS: XSS ok.   ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'color':
                    ?>
                    <tr class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.      ?></label>
                        </th>
                        <td class="form-group-control color-fields">
                            <div>
                                <input 
                                    class="text-field-input colorpicker <?php echo $class; ?>" 
                                    name="<?php echo esc_attr($name); ?>"
                                    id="<?php echo esc_attr($component['id']); ?>"
                                    type="text"
                                    value="<?php echo esc_attr($value); ?>"
                                    placeholder="<?php echo $placeholder; ?>"
                                    <?php echo $required;?>
                                    >
                            </div>
                        </td>
                    </tr>

                    <?php
                    break;
                case 'password':
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group-label">
                            <label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.      ?></label>
                        </th>
                        <td class="form-group-control">
                                <span class="generate-password"><i class="material-icons" tabindex="0" role="button">Generate</i></span>
                            <div class="text-field text-field-outlined text-field-with-trailing-icon">
                                <input 
                                    class="text-field__input <?php echo $class; ?> form__password" 
                                    name="<?php echo esc_attr($name); ?>"
                                    id="<?php echo esc_attr($component['id']); ?>"
                                    type="<?php echo esc_attr($component['type']); ?>"
                                    value="<?php echo esc_attr($value); ?>"
                                    placeholder="<?php echo $placeholder; ?>"
                                    <?php echo $required;?>
                                    >
                                <span class="password-visible"><i class="material-icons password-hidden" tabindex="0" role="button">visibility</i></span>
                                
                            </div>
                            <div class="text-field-helper-line">
                                <div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
                            </div>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'nonce':
                    wp_nonce_field(esc_attr($component['name']), esc_attr($component['id']));
                    break;
                case 'heading':
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <td colspan="2">
                            <?php
                                echo '<' . esc_attr($component['tag']) . ' class="' . $class . '">' . esc_attr($component['label']) . '</' . esc_attr($component['tag']) . '>';
                            ?>
                        </td>
                    </tr>
                    <?php
                    break;
                case 'button':
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <td class="form-group__control" colspan="2">
                            <button class="skydonation-button <?php echo $class; ?>" name="<?php echo esc_attr($name); ?>"
                                    id="<?php echo esc_attr($component['id']); ?>"> <span class="button__ripple"></span>
                                <span class="button__label"><?php echo esc_attr($component['button_text']); ?></span>
                            </button>
                        </td>
                    </tr>
                    <?php
                    break;

                case 'submit':
                    ?>
                    <tr class="form-group  <?php echo $wrapper_class; ?>">
                        <th class="form-group__label"></th>
                        <td class="form-group__control">
                            <input type="submit" class="button button-primary" 
                                name="<?php echo esc_attr($name); ?>"
                                id="<?php echo esc_attr($component['id']); ?>"
                                value="<?php echo esc_attr($component['label']); ?>"
                                />
                        </td>
                    </tr>
                    <?php
                    break;
                default:
                    break;
            }
        }

    }

    
}

new SkyDonation_Functions();