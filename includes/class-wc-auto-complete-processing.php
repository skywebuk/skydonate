<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Auto_Complete_Processing {

    public function __construct() {
        // Check if the auto-completion feature is enabled in settings
        if (sky_status_check('auto_complete_processing')) {
            // Hook into the order processed action to auto-complete orders
            add_action('woocommerce_payment_complete', [$this, 'auto_complete_order'], 10, 1);
        }
    }

    /**
     * Change the order status to 'completed' after processing.
     *
     * @param int $order_id The ID of the order.
     */
    public function auto_complete_order($order_id) {
        // Make sure it's a valid order ID
        if (!$order_id) {
            return;
        }

        // Get the order object
        $order = wc_get_order($order_id);

        // Check if the order status is still 'processing' before updating to 'completed'
        if ($order->get_status() === 'processing') {
            // Update order status to completed
            $order->update_status('completed');
        }
    }
}

// Initialize the class if in the admin area
if (is_admin()) {
    new WC_Auto_Complete_Processing();
}
?>
