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
        if ( function_exists( 'skydonate_remote_subscription_price_string' ) ) {
            return skydonate_remote_subscription_price_string( $subscription_string, $product, $include );
        }
        return $subscription_string;
    }

    /**
     * Force WooCommerce to require payment even when total = £0
     */
    public function force_payment_for_zero_total( $needs_payment, $cart ) {
        if ( WC()->cart && WC()->cart->total == 0 ) {
            $needs_payment = true;
        }
        return $needs_payment;
    }

    /**
     * Capture custom cart item data for donations and subscriptions.
     */
    public function skydonate_capture_cart_item_custom_data($cart_item, $product_id, $variation_id = 0) {
        if ( function_exists( 'skydonate_remote_capture_cart_item_data' ) ) {
            return skydonate_remote_capture_cart_item_data($cart_item, $product_id, $variation_id);
        }
        return $cart_item;
    }

    /**
     * Apply subscription schemes when adding to cart
     */
    public static function skydonate_subscription_schemes_on_add_to_cart($item_key, $product_id, $quantity, $variation_id, $variation, $cart_item) {
        if ( function_exists( 'skydonate_remote_subscription_schemes_on_add_to_cart' ) ) {
            skydonate_remote_subscription_schemes_on_add_to_cart($item_key, $product_id, $quantity, $variation_id, $variation, $cart_item);
            return;
        }
        self::skydonate_apply_subscriptions(WC()->cart);
    }

    /**
     * Apply subscriptions to all applicable cart items
     */
    public static function skydonate_apply_subscriptions($cart) {
        if ( function_exists( 'skydonate_remote_apply_subscriptions' ) ) {
            skydonate_remote_apply_subscriptions($cart);
        }
    }

    /**
     * Apply a subscription plan to a single cart item
     */
    public static function apply_subscription($cart_item) {
        if ( function_exists( 'skydonate_remote_apply_subscription' ) ) {
            return skydonate_remote_apply_subscription($cart_item);
        }
        return $cart_item;
    }

    /**
     * Get subscription scheme from cart item
     */
    public static function get_subscription_scheme($cart_item) {
        if ( function_exists( 'skydonate_remote_get_subscription_scheme' ) ) {
            return skydonate_remote_get_subscription_scheme($cart_item);
        }
        return isset($cart_item['bos4w_data']) ? $cart_item['bos4w_data'] : [];
    }

    /**
     * Set subscription meta for a product/cart item
     */
    public static function set_subscription_scheme($cart_item, $scheme) {
        if ( function_exists( 'skydonate_remote_set_subscription_scheme' ) ) {
            return skydonate_remote_set_subscription_scheme($cart_item, $scheme);
        }
        return false;
    }

    public function skydonate_save_order_item_custom_data($item, $cart_item_key, $values, $order) {
        if ( function_exists( 'skydonate_remote_save_order_item_data' ) ) {
            skydonate_remote_save_order_item_data($item, $cart_item_key, $values, $order);
        }
        if (!empty($values['fundraising_title'])) {
            $item->add_meta_data('Fundraiser', sanitize_text_field($values['fundraising_title']), true);
        }

        if (!empty($values['fundraise_id'])) {
            $item->add_meta_data('_fundraise_id', sanitize_text_field($values['fundraise_id']), true);
        }
    }

    // Add custom tabs
    public function skydonate_register_product_data_tabs($tabs) {
        if ( function_exists( 'skydonate_remote_register_product_data_tabs' ) ) {
            return skydonate_remote_register_product_data_tabs($tabs);
        }
        return $tabs;
    }

    public function skydonate_render_product_data_panels() {
        if ( function_exists( 'skydonate_remote_render_product_data_panels' ) ) {
            skydonate_remote_render_product_data_panels();
        }
    }

    public function skydonate_save_product_fields($post_id) {
        if ( function_exists( 'skydonate_remote_save_product_fields' ) ) {
            skydonate_remote_save_product_fields($post_id);
        }
    }

    public function skydonate_display_cart_item_custom_data($item_data, $cart_item) {
        if ( function_exists( 'skydonate_remote_display_cart_item_custom_data' ) ) {
            $item_data = skydonate_remote_display_cart_item_custom_data($item_data, $cart_item);
        }

        if (!empty($cart_item['fundraising_title'])) {
            $item_data[] = array(
                'name'  => __('Fundraiser', 'skydonate'),
                'value' => sanitize_text_field($cart_item['fundraising_title']),
            );
        }
        return $item_data;
    }

    public function skydonate_maybe_mark_donation_as_subscription( $is_subscription, $product_id, $product ) {
        if ( function_exists( 'skydonate_remote_maybe_mark_donation_as_subscription' ) ) {
            return skydonate_remote_maybe_mark_donation_as_subscription($is_subscription, $product_id, $product);
        }
        return $is_subscription;
    }

}
