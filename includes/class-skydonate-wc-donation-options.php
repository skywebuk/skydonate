<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Custom_Donation_Options {
    public function __construct() {
        add_action('woocommerce_product_data_tabs', [$this, 'skydonate_register_product_data_tabs']);
        add_action('woocommerce_product_data_panels', [$this, 'skydonate_render_product_data_panels']);
        add_action('save_post', [$this, 'skydonate_save_product_fields']);
        add_filter('woocommerce_get_item_data', [$this, 'skydonate_display_cart_item_custom_data'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'skydonate_save_order_item_custom_data'], 10, 4);
        add_filter('woocommerce_add_cart_item_data', [$this, 'skydonate_capture_cart_item_custom_data'], 10, 3);
        add_filter('woocommerce_is_subscription', [$this, 'skydonate_maybe_mark_donation_as_subscription'], 10, 3);
        add_action( 'woocommerce_add_to_cart', array( $this, 'skydonate_subscription_schemes_on_add_to_cart' ), 19, 6 );
        add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'skydonate_apply_subscriptions' ), 5, 1 );
        add_filter( 'wc_stripe_force_save_source', '__return_true' );
        add_filter( 'wc_stripe_skip_payment_request', '__return_false' );
        add_filter( 'woocommerce_cart_needs_payment', [ $this, 'force_payment_for_zero_total' ], 10, 2 );
        add_filter( 'woocommerce_subscriptions_product_price_string', [$this, 'skydonate_custom_subscription_price_string'], 10, 3 );
    }
    
    public function skydonate_custom_subscription_price_string( $subscription_string, $product, $include ) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->subscription_price_string( $subscription_string, $product, $include );
    }



    /**
     * Force WooCommerce to require payment even when total = £0
     */
    public function force_payment_for_zero_total( $needs_payment, $cart ) {
        // Force payment collection even when total is £0
        if ( WC()->cart && WC()->cart->total == 0 ) {
            $needs_payment = true;
        }
        return $needs_payment;
    }

    /**
     * Capture custom cart item data for donations and subscriptions.
     */
     public function skydonate_capture_cart_item_custom_data($cart_item, $product_id, $variation_id = 0) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->capture_cart_item_data($cart_item, $product_id, $variation_id);
    }


    /**
     * Apply subscription schemes when adding to cart
     */
    public static function skydonate_subscription_schemes_on_add_to_cart($item_key, $product_id, $quantity, $variation_id, $variation, $cart_item) {
        self::skydonate_apply_subscriptions(WC()->cart);
    }


    /**
     * Apply subscriptions to all applicable cart items
     * Uses remote stub for protected function
     */
    public static function skydonate_apply_subscriptions($cart) {
        skydonate_remote_stubs()->apply_subscriptions($cart);
    }


    /**
     * Apply a subscription plan to a single cart item
     * Uses remote stub for protected function
     */
    public static function apply_subscription($cart_item) {
        return skydonate_remote_stubs()->apply_subscription($cart_item);
    }


    /**
     * Get subscription scheme from cart item
     * Uses remote stub for protected function
     */
    public static function get_subscription_scheme($cart_item) {
        return skydonate_remote_stubs()->get_subscription_scheme($cart_item);
    }


    /**
     * Set subscription meta for a product/cart item
     * Uses remote stub for protected function
     */
    public static function set_subscription_scheme($cart_item, $scheme) {
        return skydonate_remote_stubs()->set_subscription_scheme($cart_item, $scheme);
    }


    public function skydonate_save_order_item_custom_data($item, $cart_item_key, $values, $order) {
        // Use remote stub for protected function
        skydonate_remote_stubs()->save_order_item_data($item, $cart_item_key, $values, $order);
    }
    
    // Add custom tabs
    public function skydonate_register_product_data_tabs($tabs) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->register_product_data_tabs($tabs);
    }

    public function skydonate_render_product_data_panels() {
        // Use remote stub for protected function
        skydonate_remote_stubs()->render_product_data_panels();
    }

    public function skydonate_save_product_fields($post_id) {
        // Use remote stub for protected function
        skydonate_remote_stubs()->save_product_fields($post_id);
    }

    public function skydonate_display_cart_item_custom_data($item_data, $cart_item) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->display_cart_item_custom_data($item_data, $cart_item);
    }

    public function skydonate_maybe_mark_donation_as_subscription( $is_subscription, $product_id, $product ) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->maybe_mark_donation_as_subscription($is_subscription, $product_id, $product);
    }

}