<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define('MLOG', get_option('master_logedin') === 'active' );
define('LTUS', get_option('license_key_status') === 'active' );
define('LDIS', LTUS ? '' : 'disabled' );

class SkyDonation_Functions {
    
    public function __construct() {
       if(LTUS){
           // add_action( 'wp_ajax_skydonation_setup_general_settings', [ $this, 'skydonation_setup_general_settings' ] );
            add_action( 'wp_ajax_skydonation_general_settings', [ $this, 'skydonation_general_settings' ] );
            add_action( 'wp_ajax_skydonation_currency_changer_settings', [ $this, 'skydonation_currency_changer_settings' ] );
            add_action( 'wp_ajax_skydonation_advanced_settings', [ $this, 'skydonation_advanced_settings' ] );
            add_action( 'wp_ajax_skydonation_setup_options_settings', [ $this, 'skydonation_setup_options_settings' ] );
            add_action( 'wp_ajax_skydonation_widget_save_setting', [ $this, 'skydonation_widget_save_setting' ] );
            add_action( 'wp_ajax_skydonation_widget_setup_setting', [ $this, 'skydonation_widget_setup_setting' ] );
            add_action( 'wp_ajax_skydonation_fees_settings', [ $this, 'skydonation_fees_settings' ] );
            add_action( 'wp_ajax_save_skyweb_gift_aid_settings', [ $this, 'save_skyweb_gift_aid_settings' ] );
            add_action( 'wp_ajax_save_address_autoload_settings', [ $this, 'save_address_autoload_settings' ] );
            add_action( 'wp_ajax_save_skydonation_color_settings', [ $this, 'save_skydonation_color_settings' ] );
            add_action( 'wp_ajax_skydonation_api_settings', [ $this, 'skydonation_api_settings' ] );
            add_action( 'wp_ajax_skydonation_setup_fees_settings', [ $this, 'skydonation_setup_fees_settings' ] );
            add_action( 'wp_ajax_skydonation_setup_notification_settings', [ $this, 'skydonation_setup_notification_settings' ] );
            add_action( 'wp_ajax_skydonation_notification_settings', [ $this, 'save_notification_settings' ] );
            add_action( 'wp_ajax_skydonation_login_settings', [ $this, 'skydonation_login_settings' ] );
	   }
        add_action( 'wp_ajax_skydonation_license_settings', [ $this, 'save_license_settings' ] );
        add_action('wp_logout', [$this, 'skydonation_update_master_logged_out']);
        add_action('admin_init', [$this, 'check_and_update_master_status']);


        add_action( 'wp_ajax_skydonation_extra_donation_settings', [ $this, 'save_extra_donation_settings' ] );
        // Removed nopriv action - settings should only be modified by authenticated admin users

    }
    public function save_extra_donation_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }

    

    public function check_and_update_master_status() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'skydonation-setup') {
            update_option('master_logedin', 'inactive');
        }
    }

    public function skydonation_update_master_logged_out() {
        update_option('master_logedin', 'inactive');
    }


    public function skydonation_login_settings() {

        // Verify nonce for security
       if (!isset($_POST['save_sky_donation_settings']) || !wp_verify_nonce($_POST['save_sky_donation_settings'], 'sky_donation_nonce')) {
			wp_send_json_error(__('Nonce verification failed.', 'skydonation'));
			return;
		}
    
        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
    
        // Initialize a mapping for received values
        $data = array_column($formData, 'value', 'name');
    
        // Sanitize input data
        $username = isset($data['username']) ? wp_kses_post($data['username']) : '';
        $password = isset($data['password']) ? wp_kses_post($data['password']) : '';
    
        // Check for missing username or password
        if ( empty($username) ) {
            wp_send_json_error(__('Empty Username.', 'skydonation'));
        } elseif ( empty($password) ) {
            wp_send_json_error(__('Empty Password.', 'skydonation'));
        }
    
        // Authenticate user
        $isAuthenticated = Skyweb_Donation_System_Authenticate::authenticateUser($username, $password);
    
        if ($isAuthenticated) {
            // Check if user is logged in and has the required permissions
            if ( is_user_logged_in() ) {
                if ( current_user_can('administrator')) {
                    update_option('master_logedin', 'active');
                    
                    // Instead of wp_redirect(), return the URL in the response
                    wp_send_json_success(array(
                        'message' => __('Successfully logged in!', 'skydonation'),
                        'redirect_url' => admin_url('admin.php?page=skydonation-setup')
                    ));
                } else {
                    wp_send_json_error(__('You are not an administrator.', 'skydonation'));
                }
            } else {
                update_option('master_logedin', 'inactive');
            }
        } else {
            wp_send_json_error(__('You are not authorized.', 'skydonation'));
        }
    }
    
    
    
   

   

    public function skydonation_setup_general_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'setup_enable_sky_donations_module',
            'setup_enable_custom_login_form',
            'setup_checkout_custom_field_style',
            'setup_recent_donation_list_with_country',
            'setup_auto_complete_processing',
            'setup_enable_donation_goal',
            'setup_enable_title_prefix'
        );
    
        // Initialize a mapping for received values
        $received_data = array_column($formData, 'value', 'name');
    
        // Iterate over allowed keys to update options
        foreach ($allowed_keys as $key) {
            $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
            update_option($key, $value);
        }
    
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }

    public function skydonation_advanced_settings() {
        // Initialize the array for title prefixes
        $select_title_prefix = array();

        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'skydonation_settings_nonce')) {
            wp_send_json_error(__('Security check failed.', 'skydonation'));
            return;
        }

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }


    public function skydonation_general_settings() {
        // Initialize the array for notification donations
        $select_title_prefix = array();

        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }
    
    
    public function skydonation_currency_changer_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
        $baseCurrency = get_option('woocommerce_currency');
    
        // Initialize selected currencies array
        $skyweb_selected_currency = array();
    
        // Extract selected currency values
        foreach ($formData as $item) {
            if (isset($item['name'], $item['value']) && $item['name'] === 'skyweb_selected_currency[]') {
                $value = sanitize_text_field($item['value']);
                if (!empty($value)) {
                    $skyweb_selected_currency[] = strtoupper($value);
                }
            }
        }

        // Ensure base currency is included
        if (!in_array($baseCurrency, $skyweb_selected_currency)) {
            $skyweb_selected_currency[] = $baseCurrency;
        }

        // Ensure unique values
        $skyweb_selected_currency = array_unique($skyweb_selected_currency);
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'skyweb_currency_changer_enabled',
            'skyweb_selected_currency',
            'skyweb_geo_currency_enabled',
            'skyweb_geo_currency_mode',
            'skyweb_geo_default_all',
        );
    
        // Map formData into key => value pairs
        $received_data = array_column($formData, 'value', 'name');
    
        // Update the options
        foreach ($allowed_keys as $key) {
            if ($key === 'skyweb_selected_currency') {
                update_option($key, $skyweb_selected_currency);
            } elseif ($key === 'skyweb_currency_changer_enabled' || $key === 'skyweb_geo_currency_enabled' || $key === 'skyweb_geo_default_all') {
                $value = isset($received_data[$key]) ? 1 : 0;
                update_option($key, $value);
            } elseif ($key === 'skyweb_geo_currency_mode') {
                $value = isset($received_data[$key]) ? sanitize_text_field($received_data[$key]) : 'all';
                update_option($key, $value);
            } else {
                $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
                update_option($key, $value);
            }
        }
    
        // Respond with success
        wp_send_json_success(__('Currency settings saved successfully.', 'skydonation'));
    }



    public function skydonation_setup_options_settings() {
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
            return;
        }

        // Initialize $formData with posted data or empty array if not set
        $formData = $_POST['formData'] ?? array();
    
        // Initialize an array to hold all values
        $settings_data = array();

    
        // Iterate over form data
        foreach ($formData as $item) {
            if (isset($item['name'], $item['value'])) {
                $value = $item['value'];
                if (!empty($value)) {
                    // Save each item as an array value using its name as the key
                    $settings_data[$item['name']][] = $value;
                }
            }
        }
        
        // Allowed keys for specific settings
        $allowed_keys = array(
            'select_card_layouts',
            'addons_card_layout',
            'addons_donation_form_layout',
            'recent_donation_layout',
            'progress_bar_layout'
        );
    
        // Update options for each allowed key
        foreach ($allowed_keys as $key) {
            if (isset($settings_data[$key])) {
                update_option($key, $settings_data[$key]);
            }
        }
    
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }
    
    

    public function skydonation_setup_notification_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'setup_enable_notification',
        );
    
        // Initialize a mapping for received values
        $received_data = array_column($formData, 'value', 'name');
    
        // Iterate over allowed keys to update options
        foreach ($allowed_keys as $key) {
            $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
            update_option($key, $value);
        }
    
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }

    public function skydonation_setup_fees_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = $_POST['formData'] ?? array();
    
        // Specify the allowed keys that you want to save
        $allowed_keys = array(
            'setup_enable_donation_fees',
        );
    
        // Initialize a mapping for received values
        $received_data = array_column($formData, 'value', 'name');
    
        // Iterate over allowed keys to update options
        foreach ($allowed_keys as $key) {
            $value = isset($received_data[$key]) ? wp_kses_post($received_data[$key]) : null;
            update_option($key, $value);
        }
    
        // Respond with success
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }
    
    public function skydonation_fees_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }
    
    public function save_address_autoload_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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
        wp_send_json_success(__('Address Autocomplete settings saved successfully.', 'skydonation'));
    }

    public function save_skydonation_color_settings() {
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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

        wp_send_json_success(__('Accent color settings saved successfully.', 'skydonation'));
    }



    public function save_skyweb_gift_aid_settings() {
        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }
    
    public function save_notification_settings() {
        // Initialize the array for notification donations
        $notification_select_donations = array();

        // Verify nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
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
        wp_send_json_success(__('Settings saved successfully.', 'skydonation'));
    }
    
    
    public function skydonation_widget_save_setting() {
        // Check the AJAX nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
            return;
        }

        // Get and sanitize the posted data
        $widgets = isset($_POST['widgets']) ? $_POST['widgets'] : [];
        if (!empty($widgets) && is_array($widgets)) {
            // Sanitize each element
            $sanitized_widgets = array_map('wp_kses_post', $widgets);
            // Update the option with sanitized data
            update_option('skydonation_widgets', $sanitized_widgets);
            wp_send_json_success(__('Settings saved successfully!', 'skydonation'));
        } else {
            wp_send_json_error(__('Nothing to save.', 'skydonation'));
        }
        exit;
    }
    public function skydonation_widget_setup_setting() {
        // Check the AJAX nonce for security
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
            return;
        }

        // Get and sanitize the posted data
        $widgets = isset($_POST['setup_widgets']) ? $_POST['setup_widgets'] : [];
        if (!empty($widgets) && is_array($widgets)) {
            // Sanitize each element
            $sanitized_widgets = array_map('wp_kses_post', $widgets);
            // Update the option with sanitized data
            update_option('skydonation_widgets_setup', $sanitized_widgets);
            wp_send_json_success(__('Settings saved successfully!', 'skydonation'));
        } else {
            wp_send_json_error(__('Nothing to save.', 'skydonation'));
        }
        exit;
    }

    public function save_license_settings() {
        check_ajax_referer('skydonation_settings_nonce', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'skydonation'));
            return;
        }

        // Parse form data sent via AJAX
        $formData = isset($_POST['formdata']) ? $_POST['formdata'] : array();
    
        // Transform formData from serialized to associative array
        $form_data_assoc = [];
        foreach ($formData as $item) {
            if (isset($item['name']) && isset($item['value'])) {
                $form_data_assoc[$item['name']] = $item['value'];
            }
        }

        $license_key = isset($form_data_assoc['license_key']) ? wp_kses_post($form_data_assoc['license_key']) : '';
        $status = isset($form_data_assoc['status']) ? wp_kses_post($form_data_assoc['status']) : '';
    
        // Handle license activation or deactivation based on 'status'
        if ($status === 'active' && !empty($license_key)) {
            if (Skyweb_Donation_System_Authenticate::setup_update_status($license_key)) {
				update_option('license_key_status', 'active');
				update_option('license_key', $license_key);
                wp_send_json_success(__('License activated successfully.', 'skydonation'));
            } else {
                wp_send_json_error(__('Invalid license key.', 'skydonation'));
            }
        } elseif ($status === 'deactive') {
            delete_option('license_key_status');
            delete_option('license_key');
    
            wp_send_json_success(__('License deactivated successfully.', 'skydonation'));
        } else {
            // If no valid data is provided, return an error response
            wp_send_json_error(__('Nothing to save. Please check your input.', 'skydonation'));
        }
    }

}

new SkyDonation_Functions();