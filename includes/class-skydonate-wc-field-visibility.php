<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Field_Visibility {

    private $recipient_filter_callback;

    public function __construct() {
        add_action('woocommerce_product_data_tabs', array($this, 'add_custom_tabs'));
        add_action('woocommerce_product_data_panels', array($this, 'add_custom_tab_content'));
        add_action('woocommerce_process_product_meta', array($this, 'save_custom_fields'));

        add_filter('woocommerce_checkout_create_order_line_item', array($this, 'add_field_data_to_order'), 10, 4);

        // Trigger after checkout, though often before "completed". Kept for backward compatibility.
        add_action('woocommerce_checkout_update_order_meta', array($this, 'trigger_new_order_email'), 10, 2);

        // Trigger when order is truly completed
        add_action('woocommerce_order_status_completed', array($this, 'trigger_new_order_email_on_completed'), 10);

        // Show “Name on Plaque” in the checkout, but not in the cart
        add_filter('woocommerce_checkout_cart_item_name', array($this, 'skydonate_field_to_checkout'), 10, 3);

        // Resend notification button on the order admin page
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'add_resend_notification_button'));
        add_action('wp_ajax_resend_notification', array($this, 'ajax_resend_notification'));
        add_action('wp_ajax_nopriv_resend_notification', array($this, 'ajax_resend_notification'));
    }

    // Attach user-entered data as line item meta
    public function add_field_data_to_order($item, $cart_item_key, $values, $order) {
        if (!empty($values['title_field'])) {
            $item->add_meta_data(__('Name on Plaque/ Banner', 'skydonate'), sanitize_text_field($values['title_field']), true);
        }
        
        if (!empty($values['donation_amount'])) {
            $item->add_meta_data(__('Selected Amount', 'skydonate'), floatval($values['donation_amount']), true);
        }
        return $item;
    }

    // Show the “Name on Plaque” at checkout only
    public function skydonate_field_to_checkout($product_name, $cart_item, $cart_item_key) {
        if (isset($cart_item['title_field'])) {
            $product_name .= '<br>' . esc_html($cart_item['title_field']);
        }
        return $product_name;
    }

    // Add a new “Field Visibility” tab in the product data section
    public function add_custom_tabs($tabs) {
        $tabs['field_visibility'] = array(
            'label'    => __('Field Visibility', 'woocommerce'),
            'target'   => 'field_visibility_options',
            'class'    => array('show_if_simple', 'show_if_variable'),
            'priority' => 22,
        );
        return $tabs;
    }


    // The actual fields in the “Field Visibility” tab
    public function add_custom_tab_content() {
        global $post;

        $field_visibility_enabled   = get_post_meta(get_the_ID(), '_field_visibility_enabled', true);
        $field_visibility_value     = get_post_meta(get_the_ID(), '_field_visibility_value', true);
        $field_label                = get_post_meta(get_the_ID(), '_field_label', true);
        $field_placeholder          = get_post_meta(get_the_ID(), '_field_placeholder', true);
        $field_label_visibility     = get_post_meta(get_the_ID(), '_field_label_visibility', true);
        $email_notification_enabled = get_post_meta(get_the_ID(), '_email_notification_enabled', true);
        $custom_email_recipient     = get_post_meta(get_the_ID(), '_custom_email_recipient', true);
        
        ?>
        <div id="field_visibility_options" class="panel woocommerce_options_panel">
            <div class="skydonate-option-card field-visibility-settings">
                <h3 class="skydonate-option-title">
                    <?php _e('Field Visibility Settings', 'woocommerce'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Configure the visibility, labeling, and notifications for this custom field.', 'woocommerce'); ?>
                </p>
                <br>

                <div class="skydonate-block-options">
                    <!-- Enable Field Visibility -->
                    <div class="skydonate-input-group">
                        <label for="field_visibility_enabled" class="skydonate-label">
                            <?php _e('Field Visibility', 'woocommerce'); ?>
                        </label>
                        <span class="woocommerce-help-tip" 
                            data-tip="<?php esc_attr_e('Check this to enable conditional visibility for this field.', 'woocommerce'); ?>"></span>
                        <input type="checkbox"
                            id="field_visibility_enabled"
                            name="field_visibility_enabled"
                            value="yes"
                            <?php checked($field_visibility_enabled, 'yes'); ?>>
                    </div>

                    <!-- Condition Value -->
                    <div class="skydonate-input-group">
                        <label for="field_visibility_value" class="skydonate-label">
                            <?php _e('Condition Value', 'woocommerce'); ?>
                        </label>
                        <span class="woocommerce-help-tip" 
                            data-tip="<?php esc_attr_e('Set the value that triggers the field to be shown.', 'woocommerce'); ?>"></span>
                        <input type="number"
                            class="short-controll"
                            id="field_visibility_value"
                            name="field_visibility_value"
                            value="<?php echo esc_attr($field_visibility_value); ?>">
                    </div>

                    <!-- Field Label -->
                    <div class="skydonate-input-group">
                        <label for="field_label" class="skydonate-label">
                            <?php _e('Field Label', 'woocommerce'); ?>
                        </label>
                        <span class="woocommerce-help-tip" 
                            data-tip="<?php esc_attr_e('This label will appear above the field on the product page.', 'woocommerce'); ?>"></span>
                        <input type="text"
                            class="short-controll"
                            id="field_label"
                            name="field_label"
                            value="<?php echo esc_attr($field_label); ?>">
                    </div>

                    <!-- Field Placeholder -->
                    <div class="skydonate-input-group">
                        <label for="field_placeholder" class="skydonate-label">
                            <?php _e('Field Placeholder', 'woocommerce'); ?>
                        </label>
                        <span class="woocommerce-help-tip" 
                            data-tip="<?php esc_attr_e('Placeholder text displayed inside the field.', 'woocommerce'); ?>"></span>
                        <input type="text"
                            class="short-controll"
                            id="field_placeholder"
                            name="field_placeholder"
                            value="<?php echo esc_attr($field_placeholder); ?>">
                    </div>

                    <!-- Show Field Label -->
                    <div class="skydonate-input-group">
                        <label for="field_label_visibility" class="skydonate-label">
                            <?php _e('Show Field Label', 'woocommerce'); ?>
                        </label>
                        <span class="woocommerce-help-tip" 
                            data-tip="<?php esc_attr_e('Check to display the field label on the product page.', 'woocommerce'); ?>"></span>
                        <input type="checkbox"
                            id="field_label_visibility"
                            name="field_label_visibility"
                            value="yes"
                            <?php checked($field_label_visibility, 'yes'); ?>>
                    </div>

                    <!-- Email Notification -->
                    <div class="skydonate-input-group">
                        <label for="email_notification_enabled" class="skydonate-label">
                            <?php _e('Email Notification', 'woocommerce'); ?>
                        </label>
                        <span class="woocommerce-help-tip" 
                            data-tip="<?php esc_attr_e('Send an email notification when this field is used.', 'woocommerce'); ?>"></span>
                        <input type="checkbox"
                            id="email_notification_enabled"
                            name="email_notification_enabled"
                            value="yes"
                            <?php checked($email_notification_enabled, 'yes'); ?>>
                    </div>

                    <!-- Custom Email Recipient -->
                    <div class="skydonate-input-group">
                        <label for="custom_email_recipient" class="skydonate-label">
                            <?php _e('Email Recipient', 'woocommerce'); ?>
                        </label>
                        <span class="woocommerce-help-tip" 
                            data-tip="<?php esc_attr_e('Specify a custom email address to receive notifications.', 'woocommerce'); ?>"></span>
                        <input type="email"
                            class="short-controll"
                            id="custom_email_recipient"
                            name="custom_email_recipient"
                            value="<?php echo esc_attr($custom_email_recipient); ?>">
                    </div>
                </div>
            </div>

        </div>
        <?php
    }

    // Persist our custom meta fields
    public function save_custom_fields($post_id) {
        $field_visibility_enabled = isset($_POST['field_visibility_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_field_visibility_enabled', $field_visibility_enabled);

        if (isset($_POST['field_visibility_value'])) {
            update_post_meta($post_id, '_field_visibility_value', sanitize_text_field($_POST['field_visibility_value']));
        }

        if (isset($_POST['field_label'])) {
            update_post_meta($post_id, '_field_label', sanitize_text_field($_POST['field_label']));
        }

        if (isset($_POST['field_placeholder'])) {
            update_post_meta($post_id, '_field_placeholder', sanitize_text_field($_POST['field_placeholder']));
        }

        $field_label_visibility = isset($_POST['field_label_visibility']) ? 'yes' : 'no';
        update_post_meta($post_id, '_field_label_visibility', $field_label_visibility);

        $email_notification_enabled = isset($_POST['email_notification_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_email_notification_enabled', $email_notification_enabled);

        if (isset($_POST['custom_email_recipient'])) {
            update_post_meta($post_id, '_custom_email_recipient', sanitize_email($_POST['custom_email_recipient']));
        }
    }

    // Called after checkout, but can happen before "completed" status
    public function trigger_new_order_email($order_id, $data = null) {
        $order = wc_get_order($order_id);
        if ($order && 'completed' === $order->get_status()) {
            $this->maybe_send_email_for_order($order);
        }
    }

    // Called when order changes to completed
    public function trigger_new_order_email_on_completed($order_id) {
        $order = wc_get_order($order_id);
        if ($order && 'completed' === $order->get_status()) {
            $this->maybe_send_email_for_order($order);
        }
    }

    // Core logic that forcibly sends the new-order email to a custom recipient if conditions match
    private function maybe_send_email_for_order($order) {
        // Only send if an item meets the conditions
        if (!$this->order_has_resendable_items($order)) {
            return;
        }
        // Find a custom recipient (if any). If none found, it will still send to the store default.
        $custom_recipient = $this->find_first_custom_recipient($order);
        // Get the "new order" email object
        $mailer  = WC()->mailer();
        $message = $mailer->emails['WC_Email_New_Order'];

        // Temporarily force it to be enabled, ignoring WooCommerce settings
        $original_enabled   = $message->enabled;
        $message->enabled   = 'yes';

        // If there's a custom recipient, replace the default
        $original_recipient = $message->recipient;
        if (!empty($custom_recipient)) {
            $message->recipient = $custom_recipient;
        }

        // Reset so we can re-send multiple times
        // Avoid creating dynamic property in PHP 8.2+
        if (property_exists($message, 'sent_to_admin')) {
            $message->sent_to_admin = false;
        }
        if (property_exists($message, 'used_for_order_ids')) {
            $message->used_for_order_ids = array();
        }

        // Send now
        $message->trigger($order->get_id());

        // Revert to previous settings
        $message->recipient = $original_recipient;
        $message->enabled   = $original_enabled;
    }


    // Check if any item meets "visibility, notification, price threshold"
    private function order_has_resendable_items($order) {
        foreach ($order->get_items() as $item) {
            $product_id                 = $item->get_product_id();
            $field_visibility_enabled   = get_post_meta($product_id, '_field_visibility_enabled', true);
            $email_notification_enabled = get_post_meta($product_id, '_email_notification_enabled', true);
            $field_visibility_value     = (float) get_post_meta($product_id, '_field_visibility_value', true);
            $custom_price               = (float) $item->get_meta('Selected Amount', true);

            if (
                $field_visibility_enabled === 'yes'
                && $email_notification_enabled === 'yes'
                && $custom_price >= $field_visibility_value
            ) {
                return true;
            }
        }
        return false;
    }

    // Return the first custom email recipient found among line items that meet conditions
    private function find_first_custom_recipient($order) {
        foreach ($order->get_items() as $item) {
            $product_id                 = $item->get_product_id();
            $field_visibility_enabled   = get_post_meta($product_id, '_field_visibility_enabled', true);
            $email_notification_enabled = get_post_meta($product_id, '_email_notification_enabled', true);
            $field_visibility_value     = (float) get_post_meta($product_id, '_field_visibility_value', true);
            $custom_price               = (float) $item->get_meta('Selected Amount', true);
            $custom_email               = get_post_meta($product_id, '_custom_email_recipient', true);

            if (
                $field_visibility_enabled === 'yes'
                && $email_notification_enabled === 'yes'
                && $custom_price >= $field_visibility_value
                && !empty($custom_email)
            ) {
                return $custom_email;
            }
        }
        return '';
    }

    // Show a "Resend Notification" button if items meet the condition
    public function add_resend_notification_button($order) {
        if (!$this->order_has_resendable_items($order)) {
            return; // No matching items, don't show the button
        }
        $order_id = $order->get_id();
        ?>
        <div class="order-resend-notification">
            <button class="button resend-notification-button">
                <?php echo esc_html__('Resend Notification', 'woocommerce'); ?>
            </button>
            <span class="resend-success-message" style="display:none; color: green;">
                <?php echo esc_html__('Notification sent successfully', 'woocommerce'); ?>
            </span>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.resend-notification-button').on('click', function(e){
                e.preventDefault();
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'resend_notification',
                        order_id: '<?php echo esc_js($order_id); ?>',
                        security: '<?php echo wp_create_nonce('resend_notification_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('.resend-success-message').show();
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }

    // Handle AJAX for resending
    public function ajax_resend_notification() {
        check_ajax_referer('resend_notification_nonce', 'security');
        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        if (!$order_id) {
            wp_send_json_error();
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error();
        }

        // Force a send if the order qualifies
        $this->maybe_send_email_for_order($order);

        wp_send_json_success();
    }
}
