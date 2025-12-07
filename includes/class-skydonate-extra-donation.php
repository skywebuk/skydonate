<?php
if (!defined('ABSPATH')) exit;

class Skydonate_Extra_Donation {

    public function __construct() {
        
        // Register shortcode
        add_shortcode('extra_donation', [$this, 'render_donation_shortcode']);

        // AJAX add/remove donation
        add_action('wp_ajax_add_extra_donation_to_cart', [$this, 'add_extra_donation_to_cart']);
        add_action('wp_ajax_nopriv_add_extra_donation_to_cart', [$this, 'add_extra_donation_to_cart']);
        add_action('wp_ajax_remove_extra_donation_from_cart', [$this, 'remove_extra_donation_from_cart']);
        add_action('wp_ajax_nopriv_remove_extra_donation_from_cart', [$this, 'remove_extra_donation_from_cart']);

        // Override cart product name & price
        add_action('woocommerce_before_calculate_totals', [$this, 'update_donation_in_cart'], 20);

        // Enqueue JS for checkout
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_filter('woocommerce_add_to_cart_fragments', [$this, 'shopping_mini_cart_fragment'], 100);

    }

    public function shopping_mini_cart_fragment($fragments) {
        // Force totals recalculation so donation price is applied
        if ( WC()->cart ) {
            WC()->cart->calculate_totals();
        }

        ob_start();
        echo '<div class="widget_shopping_cart_content">';
        woocommerce_mini_cart();
        echo '</div>';

        $fragments['.widget_shopping_cart_content'] = ob_get_clean();

        return $fragments;
    }


    /**
     * Display donation checkboxes via shortcode
     */
    public function render_donation_shortcode() {
        $donation_items = get_option('skydonate_extra_donation_items', []);
        if (empty($donation_items)) return;

        // Get currently selected donations from cart
        $cart_donations = $this->get_donation_items_in_cart();
        ob_start();
        echo '<div class="extra-donation-checkout">';
        foreach ($donation_items as $item) {
            $id     = esc_attr($item['id'] ?? '');
            $title  = !empty($item['title']) ? esc_html($item['title']) : get_the_title($id);
            $amount = esc_attr($item['amount'] ?? '');
            $checked = in_array($id, $cart_donations) ? 'checked' : '';
            ?>
            <p class="form-row donation-item">
                <label class="sky-smart-switch checkbox">
                    <input type="checkbox"
                           class="input-checkbox extra-donation-checkbox"
                           id="extra_donation_<?php echo $id; ?>"
                           data-product-id="<?php echo $id; ?>"
                           data-title="<?php echo $title; ?>"
                           data-amount="<?php echo $amount; ?>"
                           <?php echo $checked; ?>>
                        <span class="switch"></span>
                        <?php echo $title; ?>
                </label>
            </p>
            <?php
        }
        echo '</div>';
        return ob_get_clean();
    }

    private function get_donation_items_in_cart() {
        $donations = [];
        if (!WC()->cart) return $donations;

        foreach (WC()->cart->get_cart() as $cart_item) {
            if (!empty($cart_item['donation_product_id']) && $cart_item['donation_extra']) {
                $donations[] = $cart_item['donation_product_id']; // add to list
            }
        }

        return $donations;
    }



    public function update_donation_in_cart($cart) {
        if ( is_admin() && !( defined('DOING_AJAX') && DOING_AJAX ) ) return;
        // Loop by reference so price changes stick
        foreach ( $cart->get_cart() as &$cart_item ) {
            if ( ! empty( $cart_item['donation_amount'] ) ) {
                $cart_item['data']->set_price( floatval( $cart_item['donation_amount'] ) );
            }
        }
    }

    public function add_extra_donation_to_cart() {
        check_ajax_referer('skydonate_nonce', 'nonce');
        $product_id = intval($_POST['product_id'] ?? 0);
        $amount     = floatval($_POST['amount'] ?? 0);
        $extra      = $_POST['extra'] ?? false;
        $title      = sanitize_text_field($_POST['title'] ?? '');
        $frequency  = sanitize_text_field($_POST['donation_frequency'] ?? '');
        $name_on_plaque = sanitize_text_field($_POST['name_on_plaque'] ?? '');
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date   = sanitize_text_field($_POST['end_date'] ?? '');

        if (!$product_id || !$amount) {
            wp_send_json_error(['message' => 'Invalid donation data']);
        }

        // Base cart item data
        $cart_item_data = [
            'donation_extra'     => $extra,
            'donation_amount'     => $amount,
            'donation_product_id' => $product_id,
            'donation_option'     => 'Once', // default
            'bos4w_data'          => [ '' ], // always define
        ];

        // Add start and end dates only if present
        if (!empty($start_date)) {
            $cart_item_data['start_date'] = $start_date;
        }
        if (!empty($end_date)) {
            $cart_item_data['end_date'] = $end_date;
        }
        if (!empty($name_on_plaque)) {
            $cart_item_data['title_field'] = $name_on_plaque;
        }

        // Subscription logic (if not a one-time donation)
        if (!empty($frequency) && strtolower($frequency) !== 'once') {
            $period_interval = 1;

            // Map human-readable frequency to WooCommerce period keys
            $period_map = [
                'daily'   => 'day',
                'weekly'  => 'week',
                'monthly' => 'month',
                'yearly'  => 'year',
            ];

            $period = $period_map[strtolower($frequency)] ?? 'month';
            $selected_subscription = "{$period_interval}_{$period}_0";

            $donation_options = [
                'day'   => 'Daily',
                'week'  => 'Weekly',
                'month' => 'Monthly',
                'year'  => 'Yearly',
            ];

            $donation_period = $donation_options[$period] ?? 'Monthly';

            // Merge all subscription-related meta
            $subscription_data = [
                'bos4w_data' => [
                    'selected_subscription' => $selected_subscription,
                    'discounted_price'      => 0,
                ],
                '_subscription_period'          => $period,
                '_subscription_period_interval' => $period_interval,
                'donation_option'               => $donation_period,
                'selected_amount'               => $amount,
            ];

            $cart_item_data = array_merge($cart_item_data, $subscription_data);
        }
        
        // Add donation to WooCommerce cart
        $key = WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);

        if ($key) {
            wp_send_json_success(['message' => 'Donation added to cart']);
        }

        wp_send_json_error(['message' => 'Failed to add donation']);
    }



    /**
     * AJAX: Remove donation from cart
     */
    public function remove_extra_donation_from_cart() {
        check_ajax_referer('skydonate_nonce', 'nonce');

        $product_id = intval($_POST['product_id'] ?? 0);
        if (!$product_id) wp_send_json_error(['message' => 'Invalid product ID']);

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['donation_extra'] && !empty($cart_item['donation_product_id']) && $cart_item['donation_product_id'] == $product_id) {
                WC()->cart->remove_cart_item($cart_item_key);
                wp_send_json_success(['message' => 'Donation removed']);
            }
        }

        wp_send_json_error(['message' => 'Donation not found in cart']);
    }



    /**
     * Enqueue JS and pass AJAX URL & nonce
     */
    public function enqueue_scripts() {
        wp_register_script(
            'extra-donation',
            SKYDONATE_ASSETS . '/addons/js/extra-donation.js',
            ['jquery'],
            SKYDONATE_VERSION,
            true
        );

        wp_localize_script('extra-donation', 'skydonate_extra_donation_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_nonce')
        ]);

        // Enqueue the script on checkout and cart pages
        if ( is_checkout() || is_cart() ) {
            wp_enqueue_script('extra-donation');
        }
    }
}

new Skydonate_Extra_Donation();
