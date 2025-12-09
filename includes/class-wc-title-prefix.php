<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Title_Prefix {

    public function __construct() {
        if (sky_status_check('enable_title_prefix')) {
            add_filter('woocommerce_checkout_fields', [$this, 'add_billing_name_title_field']);
            add_action('woocommerce_checkout_update_order_meta', [$this, 'save_billing_name_title_order_meta']);
            add_filter('woocommerce_order_formatted_billing_address', [$this, 'add_name_title_to_billing_address'], 10, 2);
            add_filter('woocommerce_order_get_formatted_billing_full_name', [$this, 'add_name_title_to_full_billing_name'], 10, 2);
            add_action('woocommerce_edit_account_form_start', [$this, 'add_billing_name_title_to_account_form']);
            add_action('woocommerce_save_account_details', [$this, 'save_billing_name_title_account_details']);
            add_filter('woocommerce_order_export_modify_order_data', [$this, 'include_billing_name_title_in_export'], 10, 2);
            add_action('wp_footer', [$this, 'add_local_storage_script']);
        }
    }

    public function add_billing_name_title_field($fields) {
        // Check condition first
        if ( ! function_exists('sky_status_check') || ! sky_status_check('enable_title_prefix') ) {
            return $fields; // Don't show the field
        }

        // Get custom titles from options
        $custom_titles = get_option('select_title_prefix', []);

        // Ensure it's an array
        if (!is_array($custom_titles)) {
            $custom_titles = !empty($custom_titles) ? explode(',', $custom_titles) : [];
        }

        // If custom titles exist, use them; otherwise use default titles
        $default_titles = array(
            ''     => 'Select Title',
            'Mr'   => 'Mr',
            'Mrs'  => 'Mrs',
            'Ms'   => 'Ms',
            'Dr'   => 'Dr',
            'Miss' => 'Miss',
            'Sir'  => 'Sir',
        );

        $options = !empty($custom_titles) ? array_combine($custom_titles, $custom_titles) : $default_titles;

        // Add the select field
        $fields['billing']['billing_name_title'] = array(
            'type'        => 'select',
            'label'       => __('Title', 'woocommerce'),
            'required'    => true,
            'options'     => $options,
            'class'       => array('form-row-wide'),
            'priority'    => 5,
        );

        // Set default value if user has saved title
        $user_id = get_current_user_id();
        if ($user_id) {
            $saved_title = get_user_meta($user_id, 'billing_name_title', true);
            if ($saved_title && isset($options[$saved_title])) {
                $fields['billing']['billing_name_title']['default'] = $saved_title;
                $fields['billing']['billing_name_title']['custom_attributes'] = array(
                    'data-selected' => $saved_title
                );
            }
        }

        return $fields;

    }

    public function save_billing_name_title_order_meta($order_id) {
        if (!empty($_POST['billing_name_title'])) {
            update_post_meta($order_id, '_billing_name_title', sanitize_text_field($_POST['billing_name_title']));
        }
    }

    public function add_name_title_to_billing_address($address, $order) {
        $name_title = get_post_meta($order->get_id(), '_billing_name_title', true);
        if ($name_title && strpos($address['first_name'], $name_title) !== 0) {
            $address['first_name'] = $name_title . ' ' . $address['first_name'];
        }
        return $address;
    }

    public function add_name_title_to_full_billing_name($full_name, $order) {
        $name_title = get_post_meta($order->get_id(), '_billing_name_title', true);
        if ($name_title && strpos($full_name, $name_title) !== 0) {
            $full_name = $name_title . ' ' . $full_name;
        }
        return $full_name;
    }

    public function add_billing_name_title_to_account_form() {
        $user_id = get_current_user_id();
        $name_title = get_user_meta($user_id, 'billing_name_title', true);
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="billing_name_title"><?php _e('Name Title', 'woocommerce'); ?></label>
            <select name="billing_name_title" id="billing_name_title" class="woocommerce-Input woocommerce-Input--select">
                <option value=""><?php _e('Select Title', 'woocommerce'); ?></option>
                <option value="Mr" <?php selected($name_title, 'Mr'); ?>><?php _e('Mr', 'woocommerce'); ?></option>
                <option value="Miss" <?php selected($name_title, 'Miss'); ?>><?php _e('Miss', 'woocommerce'); ?></option>
                <option value="Mrs" <?php selected($name_title, 'Mrs'); ?>><?php _e('Mrs', 'woocommerce'); ?></option>
                <option value="Ms" <?php selected($name_title, 'Ms'); ?>><?php _e('Ms', 'woocommerce'); ?></option>
                <option value="Dr" <?php selected($name_title, 'Dr'); ?>><?php _e('Dr', 'woocommerce'); ?></option>
            </select>
        </p>
        <?php
    }

    public function save_billing_name_title_account_details($user_id) {
        if (isset($_POST['billing_name_title'])) {
            update_user_meta($user_id, 'billing_name_title', sanitize_text_field($_POST['billing_name_title']));
        }
    }

    public function include_billing_name_title_in_export($order_data, $order) {
        $order_data['billing_name_title'] = get_post_meta($order->get_id(), '_billing_name_title', true);
        return $order_data;
    }

    public function add_local_storage_script() {
        if (is_checkout()) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    // Retrieve the saved title from local storage and set it in the field
                    var savedTitle = localStorage.getItem('billing_name_title');
                    if (savedTitle) {
                        $('#billing_name_title').val(savedTitle);
                    }

                    // Save the selected title to local storage when changed
                    $('#billing_name_title').on('change', function () {
                        localStorage.setItem('billing_name_title', $(this).val());
                    });
                });
            </script>
            <?php
        }
    }
}

if (is_admin()) {
    new WC_Title_Prefix();
}
?>