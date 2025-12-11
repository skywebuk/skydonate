<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Recent_Donations {

    public function __construct() {
        if (sky_status_check('recent_donation_list_with_country')) {
            add_shortcode('recent_orders', [$this, 'get_recent_orders_for_product']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_recent_donations_styles']);
            add_filter('woocommerce_checkout_fields', [$this, 'add_anonymous_donation_checkbox']);
            add_action('woocommerce_checkout_update_order_meta', [$this, 'save_anonymous_donation_field']);
        }
    }

    public function enqueue_recent_donations_styles() {
        wp_enqueue_style('recent-donations',SKYDONATE_PUBLIC_ASSETS. '/css/recent-donations.css');
        wp_enqueue_style('flag-icons', 'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css');
    }

    public function add_anonymous_donation_checkbox($fields) {
        $fields['order']['order_anonymous_donation'] = array(
            'type' => 'checkbox',
            'label' => __('Make this Donation Anonymous', 'your-text-domain'),
            'class' => array('form-row-wide'),
            'clear' => true,
            'priority' => 22,
        );

        return $fields;
    }

    public function save_anonymous_donation_field($order_id) {
        if (!empty($_POST['order_anonymous_donation'])) {
            update_post_meta($order_id, '_anonymous_donation', sanitize_text_field($_POST['order_anonymous_donation']));
        }
    }

    public function get_recent_orders_for_product($atts) {
        global $post;
        $product_id = '';
        if (is_product()) {
            $product_id = $post->ID;
        } else {
            $atts = shortcode_atts([
                'product_id' => '',
            ], $atts, 'recent_orders');
            $product_id = $atts['product_id'];
        }

        if (!$product_id) {
            return 'No product ID provided.';
        }

        $args = [
            'limit' => 20,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $orders = wc_get_orders($args);
        $filtered_orders = [];

        foreach ($orders as $order) {
            if ($order->get_status() !== 'completed') {
                continue;
            }

            foreach ($order->get_items() as $item) {
                if ($item->get_product_id() == $product_id) {
                    $filtered_orders[] = $order;
                    break;
                }
            }
        }


        $output = '<div class="order-box">';
        $output .= '<ul id="order-list">';

        foreach ($filtered_orders as $order) {
            $order_date = $order->get_date_created();
            $time_ago = human_time_diff($order_date->getTimestamp(), current_time('timestamp')) . ' ago';
            $is_anonymous = get_post_meta($order->get_id(), '_anonymous_donation', true);

            if ($is_anonymous === '1') {
                $customer_name = 'Anonymous';
            } else {
                $customer_first_name = $order->get_billing_first_name();
                $customer_last_name = $order->get_billing_last_name();
                $customer_initial = strtoupper(substr($customer_last_name, 0, 1));
                $customer_name = $customer_first_name . ' ' . $customer_initial . '.';
            }

            $order_total = wc_price($order->get_total());
            $billing_city = $order->get_billing_city();
            $billing_country = $order->get_billing_country();
            $country_code = strtolower($billing_country);

            $countries = WC()->countries->get_countries();
            $country_name = isset($countries[$billing_country]) ? $countries[$billing_country] : $billing_country;
            $country_name = preg_replace('/\s*\(.*?\)\s*/', '', $country_name);

            $output .= '<li class="order-item">';
            $output .= '<p><strong>' . esc_html($customer_name) . '</strong> donated <strong>' . wp_kses_post($order_total) . '</strong></p>';
            $output .= '<p><span class="flag-icon flag-icon-' . esc_html($country_code) . '"></span> ' . esc_html($billing_city) . ', ' . esc_html($country_name) . '</p>';
            $output .= '<p class="time">' . esc_html($time_ago) . '</p>';
            $output .= '</li>';
        }

        $output .= '</ul>';
        $output .= '</div>';

        if (empty($filtered_orders)) {
            return '<p style="margin: 0; text-align: center;">No recent donations for this project.</p>';
        }

        return $output;
    }
}

// Anonymous checkbox functions - hooks registered via skydonate_init_recent_donations_hooks()
function display_admin_order_meta_anonymous_checkbox($order) {
    $is_anonymous = get_post_meta($order->get_id(), '_anonymous_donation', true);
    $checked = $is_anonymous ? 'checked="checked"' : '';
    echo '<p><strong>' . __('Anonymous Donation', 'your-text-domain') . ':</strong> <br/><label><input type="checkbox" name="anonymous_donation" ' . $checked . ' /> Make this donation anonymous</label></p>';
}

function save_admin_order_meta_anonymous_checkbox($post_id, $post) {
    if (isset($_POST['anonymous_donation'])) {
        update_post_meta($post_id, '_anonymous_donation', '1');
    } else {
        update_post_meta($post_id, '_anonymous_donation', '');
    }
}


// Display the anonymous donation checkbox on the Thank You page and Donation Details under My Account
function add_anonymous_donation_option($order_id) {
    if (!$order_id) {
        return;
    }

    $is_anonymous = get_post_meta($order_id, '_anonymous_donation', true);
    $checked = checked($is_anonymous, '1', false);

    // Ensuring the form submits back to the same page
    $current_url = add_query_arg(['order_id' => $order_id], home_url(add_query_arg(null, null)));

    echo '<style>
        .anonymous-donation-form {
            padding: 20px;
            border: 2px solid #bbcfed;
            border-radius: 10px;
            margin: 20px auto;
            text-align: center; /* Center the content */
        }
        .anonymous-donation-form p {
            margin: 0 0 15px;
        }
        .anonymous-donation-form strong {
            font-size: 1.5em;
            color: var(--accent-color);
            text-align: center !important;
        }
        .anonymous-donation-form label {
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            align-items: center;
            justify-content: center;
            padding-top: 10px !important;
        }
        .anonymous-donation-form input[type="checkbox"] {
            margin-right: 10px;
        }
        .anonymous-donation-form input[type="submit"] {
            background-color: #fff;
            border: 2px solid #bbcfed;
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            border: 2px solid var(--accent-color);
            width: 100%;
        }
        .anonymous-donation-form input[type="submit"]:hover {
            background-color: var(--accent-color);
            color: white;
        }
    </style>';

    echo '<form action="' . esc_url($current_url) . '" method="post" class="anonymous-donation-form">';
    wp_nonce_field('anonymous_donation_' . $order_id, 'anonymous_donation_nonce');
    echo '<p><strong>' . __('Would you like to donate anonymously?', 'skydonate') . '</strong><br/>';
    echo '<label><input type="checkbox" name="anonymous_donation" value="1" ' . $checked . '> ' . __('Yes, I would like to make my donation anonymous', 'skydonate') . '</label>';
    echo '<input type="hidden" name="order_id" value="' . esc_attr($order_id) . '">';
    echo '<input type="submit" name="submit_anonymous_donation" class="button" value="' . __('Update', 'skydonate') . '"></p>';
    echo '</form>';
}

// Display the anonymous donation option on the Thank You page
function add_anonymous_donation_option_on_thankyou_page($order_id) {
    add_anonymous_donation_option($order_id);
}

// Display the anonymous donation option on the Donation Details under My Account
function add_anonymous_donation_option_on_account_page($order) {
    if (is_a($order, 'WC_Order')) {
        add_anonymous_donation_option($order->get_id());
    }
}

// Handle the form submission and update the order meta
function handle_anonymous_donation_submission() {
    if (isset($_POST['submit_anonymous_donation']) && !empty($_POST['order_id'])) {
        $order_id = absint($_POST['order_id']);

        // Security: Verify nonce
        if (!isset($_POST['anonymous_donation_nonce']) || !wp_verify_nonce($_POST['anonymous_donation_nonce'], 'anonymous_donation_' . $order_id)) {
            wc_add_notice(__('Security check failed. Please try again.', 'skydonate'), 'error');
            return;
        }

        // Security: Verify user owns this order
        $order = wc_get_order($order_id);
        if (!$order) {
            wc_add_notice(__('Invalid order.', 'skydonate'), 'error');
            return;
        }

        $current_user_id = get_current_user_id();
        $order_user_id = $order->get_user_id();

        // Allow if user owns the order OR if it's a guest order and they're on the thank you page with valid order key
        $is_owner = ($current_user_id > 0 && $current_user_id === $order_user_id);
        $is_guest_order = ($order_user_id === 0 && isset($_GET['key']) && $order->get_order_key() === $_GET['key']);

        if (!$is_owner && !$is_guest_order) {
            wc_add_notice(__('You do not have permission to update this order.', 'skydonate'), 'error');
            return;
        }

        if (isset($_POST['anonymous_donation']) && $_POST['anonymous_donation'] === '1') {
            update_post_meta($order_id, '_anonymous_donation', '1');
        } else {
            delete_post_meta($order_id, '_anonymous_donation');
        }

        // Add a success notice
        wc_add_notice(__('Your donation anonymity preference has been updated.', 'skydonate'), 'success');

        // Redirect to avoid form resubmission issues, ensure all query params are kept
        wp_safe_redirect(esc_url_raw(add_query_arg([])));
        exit;
    }
}

// Remove the fee section from the Thank You page
function remove_fee_from_thankyou_page($total_rows, $order, $tax_display) {
    if (is_checkout() && !is_wc_endpoint_url('order-received')) {
        return $total_rows;
    }
    
    foreach ($total_rows as $key => $total_row) {
        if ($key === 'fee') {
            unset($total_rows[$key]);
        }
    }
    return $total_rows;
}

/**
 * Initialize WooCommerce recent donations hooks on init to avoid early translation loading
 * This fixes WordPress 6.7+ translation timing requirements
 */
function skydonate_init_recent_donations_hooks() {
    add_action('woocommerce_admin_order_data_after_billing_address', 'display_admin_order_meta_anonymous_checkbox', 10, 1);
    add_action('woocommerce_process_shop_order_meta', 'save_admin_order_meta_anonymous_checkbox', 45, 2);
    add_filter('woocommerce_get_order_item_totals', 'remove_fee_from_thankyou_page', 10, 3);
    add_action('woocommerce_order_details_before_order_table', 'add_anonymous_donation_option_on_account_page', 10, 1);
    add_action('template_redirect', 'handle_anonymous_donation_submission');
}
add_action( 'init', 'skydonate_init_recent_donations_hooks', 0 );

// Class is initialized in Skydonate_System via conditionally_initialize_class()
// to comply with WordPress 6.7+ translation timing requirements