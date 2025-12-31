<?php
/**
 * SkyDonate Remote Function Stubs
 *
 * This file provides stub functions that call the remotely loaded core functions.
 * If remote functions are not loaded, these stubs will return safe defaults or errors.
 *
 * @package SkyDonate
 * @version 2.0.31
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class to manage remote function stubs and validation
 */
class SkyDonate_Remote_Stubs {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Whether remote functions are available
     */
    private $remote_available = false;

    /**
     * Get singleton instance
     */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Check remote functions availability after they should be loaded
        add_action( 'init', array( $this, 'check_remote_availability' ), 10 );

        // Add admin notice if remote functions not available
        add_action( 'admin_notices', array( $this, 'admin_notice_remote_required' ) );
    }

    /**
     * Check if remote functions are available
     */
    public function check_remote_availability() {
        $this->remote_available = defined( 'SKYDONATE_REMOTE_FUNCTIONS_LOADED' ) && SKYDONATE_REMOTE_FUNCTIONS_LOADED;
    }

    /**
     * Check if remote functions are loaded
     *
     * @return bool
     */
    public function is_remote_available() {
        return $this->remote_available || ( defined( 'SKYDONATE_REMOTE_FUNCTIONS_LOADED' ) && SKYDONATE_REMOTE_FUNCTIONS_LOADED );
    }

    /**
     * Display admin notice if remote functions are not available
     */
    public function admin_notice_remote_required() {
        // Only show to admins
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Skip if remote functions are available
        if ( $this->is_remote_available() ) {
            return;
        }

        // Check if license is valid
        $license_data = get_option( 'skydonate_license_data_backup', null );
        if ( empty( $license_data ) || empty( $license_data['success'] ) ) {
            ?>
            <div class="notice notice-error">
                <p><strong><?php esc_html_e( 'SkyDonate:', 'skydonate' ); ?></strong>
                <?php esc_html_e( 'Please activate your license to enable all plugin features.', 'skydonate' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=skydonate-license' ) ); ?>">
                    <?php esc_html_e( 'Activate License', 'skydonate' ); ?>
                </a>
                </p>
            </div>
            <?php
            return;
        }

        // Remote functions not loaded
        ?>
        <div class="notice notice-warning">
            <p><strong><?php esc_html_e( 'SkyDonate:', 'skydonate' ); ?></strong>
            <?php esc_html_e( 'Some features are temporarily unavailable. Please check your internet connection and refresh the page.', 'skydonate' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Log error when remote function is called but not available
     *
     * @param string $function_name The function that was called
     */
    private function log_remote_error( $function_name ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[SkyDonate] Remote function not available: ' . $function_name );
        }
    }

    /**
     * =========================================================================
     * DONATION CALCULATION STUBS
     * =========================================================================
     */

    /**
     * Get total donation sales - STUB
     *
     * @param int $product_id Product ID
     * @return float
     */
    public function get_total_donation_sales( $product_id ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_total_donation_sales' ) ) {
            return skydonate_remote_get_total_donation_sales( $product_id );
        }

        $this->log_remote_error( 'get_total_donation_sales' );

        // Return cached value if available
        $cached = get_post_meta( $product_id, '_total_sales_amount', true );
        return $cached ? floatval( $cached ) : 0;
    }

    /**
     * Get donation order count - STUB
     *
     * @param int $product_id Product ID
     * @return int
     */
    public function get_donation_order_count( $product_id ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_donation_order_count' ) ) {
            return skydonate_remote_get_donation_order_count( $product_id );
        }

        $this->log_remote_error( 'get_donation_order_count' );

        // Return cached value if available
        $cached = get_post_meta( $product_id, '_order_count', true );
        return $cached ? intval( $cached ) : 0;
    }

    /**
     * Get order IDs by product ID - STUB
     *
     * @param array  $product_ids  Product IDs
     * @param array  $order_status Order statuses
     * @param int    $limit        Limit
     * @param string $start_date   Start date
     * @return array
     */
    public function get_orders_ids_by_product_id( $product_ids = [], $order_status = ['wc-completed'], $limit = 100, $start_date = '' ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_orders_ids_by_product_id' ) ) {
            return skydonate_remote_get_orders_ids_by_product_id( $product_ids, $order_status, $limit, $start_date );
        }

        $this->log_remote_error( 'get_orders_ids_by_product_id' );
        return [];
    }

    /**
     * Get top amount orders by product IDs - STUB
     *
     * @param array  $product_ids  Product IDs
     * @param array  $order_status Order statuses
     * @param int    $limit        Limit
     * @param string $start_date   Start date
     * @return array
     */
    public function get_top_amount_orders_by_product_ids( $product_ids = [], $order_status = ['wc-completed'], $limit = 100, $start_date = '' ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_top_amount_orders_by_product_ids' ) ) {
            return skydonate_remote_get_top_amount_orders_by_product_ids( $product_ids, $order_status, $limit, $start_date );
        }

        $this->log_remote_error( 'get_top_amount_orders_by_product_ids' );
        return [];
    }

    /**
     * =========================================================================
     * CURRENCY STUBS
     * =========================================================================
     */

    /**
     * Convert currency - STUB
     *
     * @param string $baseCurrency   Base currency
     * @param string $targetCurrency Target currency
     * @param float  $amount         Amount
     * @return float
     */
    public function convert_currency( $baseCurrency, $targetCurrency, $amount ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_convert_currency' ) ) {
            return skydonate_remote_convert_currency( $baseCurrency, $targetCurrency, $amount );
        }

        $this->log_remote_error( 'convert_currency' );
        return $amount; // Return original amount as fallback
    }

    /**
     * Get exchange rate - STUB
     *
     * @param string $from From currency
     * @param string $to   To currency
     * @return float|null
     */
    public function get_rate( $from = 'GBP', $to = 'USD' ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_rate' ) ) {
            return skydonate_remote_get_rate( $from, $to );
        }

        $this->log_remote_error( 'get_rate' );
        return null;
    }

    /**
     * Get saved rates - STUB
     *
     * @return array
     */
    public function get_saved_rates() {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_saved_rates' ) ) {
            return skydonate_remote_get_saved_rates();
        }

        $this->log_remote_error( 'get_saved_rates' );

        // Return from option as fallback
        $data = get_option( 'skydonate_currency_rates', [] );
        return $data['rates'] ?? [];
    }

    /**
     * =========================================================================
     * GIFT AID STUBS
     * =========================================================================
     */

    /**
     * Export Gift Aid orders - STUB
     *
     * @param int $paged Page number
     * @return array
     */
    public function export_gift_aid_orders( $paged = 1 ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_export_gift_aid_orders' ) ) {
            return skydonate_remote_export_gift_aid_orders( $paged );
        }

        $this->log_remote_error( 'export_gift_aid_orders' );
        return [
            'done'  => true,
            'csv'   => '',
            'error' => __( 'Export feature requires active license.', 'skydonate' ),
        ];
    }

    /**
     * Export Gift Aid orders by date - STUB
     *
     * @param string $start_date Start date
     * @param string $end_date   End date
     * @param int    $paged      Page number
     * @return array
     */
    public function export_gift_aid_orders_by_date( $start_date, $end_date, $paged = 1 ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_export_gift_aid_orders_by_date' ) ) {
            return skydonate_remote_export_gift_aid_orders_by_date( $start_date, $end_date, $paged );
        }

        $this->log_remote_error( 'export_gift_aid_orders_by_date' );
        return [
            'done'  => true,
            'csv'   => '',
            'error' => __( 'Export feature requires active license.', 'skydonate' ),
        ];
    }

    /**
     * =========================================================================
     * DONATION FEES STUBS
     * =========================================================================
     */

    /**
     * Calculate donation fee - STUB
     *
     * @param float $cart_total Cart total
     * @return float
     */
    public function calculate_donation_fee( $cart_total ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_calculate_donation_fee' ) ) {
            return skydonate_remote_calculate_donation_fee( $cart_total );
        }

        $this->log_remote_error( 'calculate_donation_fee' );
        return 0; // Return 0 as fallback
    }

    /**
     * =========================================================================
     * HELPER STUBS
     * =========================================================================
     */

    /**
     * Check if HPOS is active - STUB
     *
     * @return bool
     */
    public function is_hpos_active() {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_is_hpos_active' ) ) {
            return skydonate_remote_is_hpos_active();
        }

        // Fallback check
        if ( ! class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
            return false;
        }
        return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    /**
     * Render recent donations layout one - STUB
     *
     * @param array $order_ids    Order IDs
     * @param array $product_ids  Product IDs
     * @param bool  $hidden_class Hidden class flag
     */
    public function render_recent_donations_layout_one( $order_ids, $product_ids, $hidden_class = false ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_render_recent_donations_layout_one' ) ) {
            skydonate_remote_render_recent_donations_layout_one( $order_ids, $product_ids, $hidden_class );
            return;
        }

        $this->log_remote_error( 'render_recent_donations_layout_one' );
        echo '<li class="sky-order"><div class="item-wrap"><p>' . esc_html__( 'Loading...', 'skydonate' ) . '</p></div></li>';
    }

    /**
     * Render recent donations layout two - STUB
     *
     * @param array  $order_ids    Order IDs
     * @param array  $product_ids  Product IDs
     * @param string $list_icon    Icon HTML
     * @param bool   $hidden_class Hidden class flag for slider effect
     */
    public function render_recent_donations_layout_two( $order_ids, $product_ids, $list_icon = '<i class="fas fa-hand-holding-heart"></i>', $hidden_class = false ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_render_recent_donations_layout_two' ) ) {
            skydonate_remote_render_recent_donations_layout_two( $order_ids, $product_ids, $list_icon, $hidden_class );
            return;
        }

        $this->log_remote_error( 'render_recent_donations_layout_two' );
        echo '<li class="sky-order"><div class="item-wrap"><p>' . esc_html__( 'Loading...', 'skydonate' ) . '</p></div></li>';
    }

    /**
     * Get currency by country code - STUB
     *
     * @param string $country_code Country code
     * @return string
     */
    public function get_currency_by_country_code( $country_code ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_currency_by_country_code' ) ) {
            return skydonate_remote_get_currency_by_country_code( $country_code );
        }

        $this->log_remote_error( 'get_currency_by_country_code' );
        return get_woocommerce_currency(); // Fallback to store currency
    }

    /**
     * Get user country name - STUB
     *
     * @param string $format 'name' or 'code'
     * @return string
     */
    public function get_user_country_name( $format = 'name' ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_user_country_name' ) ) {
            return skydonate_remote_get_user_country_name( $format );
        }

        $this->log_remote_error( 'get_user_country_name' );
        return 'Unknown';
    }

    /**
     * =========================================================================
     * DONATION OPTIONS STUBS
     * =========================================================================
     */

    /**
     * Capture cart item custom data - STUB
     *
     * @param array $cart_item    Cart item data
     * @param int   $product_id   Product ID
     * @param int   $variation_id Variation ID
     * @return array
     */
    public function capture_cart_item_data( $cart_item, $product_id, $variation_id = 0 ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_capture_cart_item_data' ) ) {
            return skydonate_remote_capture_cart_item_data( $cart_item, $product_id, $variation_id );
        }

        $this->log_remote_error( 'capture_cart_item_data' );
        return $cart_item; // Return unmodified as fallback
    }

    /**
     * Apply subscription to cart item - STUB
     *
     * @param array $cart_item Cart item data
     * @return array
     */
    public function apply_subscription( $cart_item ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_apply_subscription' ) ) {
            return skydonate_remote_apply_subscription( $cart_item );
        }

        $this->log_remote_error( 'apply_subscription' );
        return $cart_item; // Return unmodified as fallback
    }

    /**
     * Get subscription scheme - STUB
     *
     * @param array $cart_item Cart item data
     * @return array
     */
    public function get_subscription_scheme( $cart_item ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_get_subscription_scheme' ) ) {
            return skydonate_remote_get_subscription_scheme( $cart_item );
        }

        $this->log_remote_error( 'get_subscription_scheme' );
        return isset($cart_item['bos4w_data']) ? $cart_item['bos4w_data'] : [];
    }

    /**
     * Set subscription scheme - STUB
     *
     * @param array $cart_item Cart item data
     * @param array $scheme    Subscription scheme
     * @return bool
     */
    public function set_subscription_scheme( $cart_item, $scheme ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_set_subscription_scheme' ) ) {
            return skydonate_remote_set_subscription_scheme( $cart_item, $scheme );
        }

        $this->log_remote_error( 'set_subscription_scheme' );
        return false;
    }

    /**
     * Apply subscriptions to cart - STUB
     *
     * @param WC_Cart $cart Cart object
     */
    public function apply_subscriptions( $cart ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_apply_subscriptions' ) ) {
            skydonate_remote_apply_subscriptions( $cart );
            return;
        }

        $this->log_remote_error( 'apply_subscriptions' );
    }

    /**
     * Save order item data - STUB
     *
     * @param WC_Order_Item $item          Order item
     * @param string        $cart_item_key Cart item key
     * @param array         $values        Cart item values
     * @param WC_Order      $order         Order object
     */
    public function save_order_item_data( $item, $cart_item_key, $values, $order ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_save_order_item_data' ) ) {
            skydonate_remote_save_order_item_data( $item, $cart_item_key, $values, $order );
            return;
        }

        $this->log_remote_error( 'save_order_item_data' );
    }

    /**
     * Custom subscription price string - STUB
     *
     * @param string     $subscription_string Subscription string
     * @param WC_Product $product            Product object
     * @param array      $include            Include options
     * @return string
     */
    public function subscription_price_string( $subscription_string, $product, $include ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_subscription_price_string' ) ) {
            return skydonate_remote_subscription_price_string( $subscription_string, $product, $include );
        }

        $this->log_remote_error( 'subscription_price_string' );
        return $subscription_string; // Return original as fallback
    }

    /**
     * =========================================================================
     * SHORTCODE RENDER STUBS
     * =========================================================================
     */

    /**
     * Render donation form layout one - STUB
     *
     * @param int   $id   Product ID
     * @param array $atts Shortcode attributes
     */
    public function render_layout_one( $id, $atts ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_render_layout_one' ) ) {
            skydonate_remote_render_layout_one( $id, $atts );
            return;
        }

        $this->log_remote_error( 'render_layout_one' );
        echo '<div class="donation-form-error"><p>' . esc_html__( 'Donation form requires active license.', 'skydonate' ) . '</p></div>';
    }

    /**
     * Render donation form layout two - STUB
     *
     * @param int   $id   Product ID
     * @param array $atts Shortcode attributes
     */
    public function render_layout_two( $id, $atts ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_render_layout_two' ) ) {
            skydonate_remote_render_layout_two( $id, $atts );
            return;
        }

        $this->log_remote_error( 'render_layout_two' );
        echo '<div class="donation-form-error"><p>' . esc_html__( 'Donation form requires active license.', 'skydonate' ) . '</p></div>';
    }

    /**
     * Render donation form layout three - STUB
     *
     * @param int   $id   Product ID
     * @param array $atts Shortcode attributes
     */
    public function render_layout_three( $id, $atts ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_render_layout_three' ) ) {
            skydonate_remote_render_layout_three( $id, $atts );
            return;
        }

        $this->log_remote_error( 'render_layout_three' );
        echo '<div class="donation-form-error"><p>' . esc_html__( 'Donation form requires active license.', 'skydonate' ) . '</p></div>';
    }

    /**
     * Render name on plaque field - STUB
     *
     * @param int $id Product ID
     */
    public function render_name_on_plaque( $id ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_render_name_on_plaque' ) ) {
            skydonate_remote_render_name_on_plaque( $id );
            return;
        }

        $this->log_remote_error( 'render_name_on_plaque' );
        // Silent fail - don't show anything if not available
    }

    /**
     * =========================================================================
     * WOOCOMMERCE PRODUCT DATA TABS STUBS
     * =========================================================================
     */

    /**
     * Register product data tabs - STUB
     *
     * @param array $tabs Existing tabs
     * @return array Modified tabs
     */
    public function register_product_data_tabs( $tabs ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_register_product_data_tabs' ) ) {
            return skydonate_remote_register_product_data_tabs( $tabs );
        }

        $this->log_remote_error( 'register_product_data_tabs' );
        // Fallback - add tab directly
        $tabs['skydonate_donation_fields'] = [
            'label' => __('Donation Fields', 'skydonate'),
            'target' => 'skydonate_options_data',
            'class' => ['show_if_simple', 'show_if_variable'],
            'priority' => 21,
        ];
        return $tabs;
    }

    /**
     * Render product data panels - STUB
     */
    public function render_product_data_panels() {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_render_product_data_panels' ) ) {
            skydonate_remote_render_product_data_panels();
            return;
        }

        $this->log_remote_error( 'render_product_data_panels' );
        echo '<div id="skydonate_options_data" class="panel woocommerce_options_panel">';
        echo '<div class="options_group"><p>' . esc_html__( 'Donation fields require active license.', 'skydonate' ) . '</p></div>';
        echo '</div>';
    }

    /**
     * Save product fields - STUB
     *
     * @param int $post_id Post ID
     */
    public function save_product_fields( $post_id ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_save_product_fields' ) ) {
            skydonate_remote_save_product_fields( $post_id );
            return;
        }

        $this->log_remote_error( 'save_product_fields' );
        // Silent fail - can't save without remote functions
    }

    /**
     * Display cart item custom data - STUB
     *
     * @param array $item_data  Item data
     * @param array $cart_item  Cart item
     * @return array Modified item data
     */
    public function display_cart_item_custom_data( $item_data, $cart_item ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_display_cart_item_custom_data' ) ) {
            return skydonate_remote_display_cart_item_custom_data( $item_data, $cart_item );
        }

        $this->log_remote_error( 'display_cart_item_custom_data' );
        // Fallback - add basic display
        if (!empty($cart_item['donation_type'])) {
            $item_data[] = [
                'name'  => __('Donation Type', 'skydonate'),
                'value' => esc_html($cart_item['donation_type']),
            ];
        }
        if (!empty($cart_item['custom_amount_label'])) {
            $item_data[] = [
                'name'  => __('Amount Label', 'skydonate'),
                'value' => esc_html($cart_item['custom_amount_label']),
            ];
        }
        return $item_data;
    }

    /**
     * Maybe mark donation as subscription - STUB
     *
     * @param bool       $is_subscription Whether the product is already considered a subscription
     * @param int        $product_id      The product ID being checked
     * @param WC_Product $product         The product object
     * @return bool
     */
    public function maybe_mark_donation_as_subscription( $is_subscription, $product_id, $product ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_maybe_mark_donation_as_subscription' ) ) {
            return skydonate_remote_maybe_mark_donation_as_subscription( $is_subscription, $product_id, $product );
        }

        $this->log_remote_error( 'maybe_mark_donation_as_subscription' );
        // Fallback - check meta directly
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return $is_subscription;
        }
        $plans = $product->get_meta( '_subscription_plan_data' );
        if ( $plans ) {
            return true;
        }
        return $is_subscription;
    }

    /**
     * Subscription schemes on add to cart - STUB
     *
     * @param string $item_key     Cart item key
     * @param int    $product_id   Product ID
     * @param int    $quantity     Quantity
     * @param int    $variation_id Variation ID
     * @param array  $variation    Variation data
     * @param array  $cart_item    Cart item data
     */
    public function subscription_schemes_on_add_to_cart( $item_key, $product_id, $quantity, $variation_id, $variation, $cart_item ) {
        if ( $this->is_remote_available() && function_exists( 'skydonate_remote_subscription_schemes_on_add_to_cart' ) ) {
            skydonate_remote_subscription_schemes_on_add_to_cart( $item_key, $product_id, $quantity, $variation_id, $variation, $cart_item );
            return;
        }

        $this->log_remote_error( 'subscription_schemes_on_add_to_cart' );
        // Fallback - call apply_subscriptions directly
        $this->apply_subscriptions( WC()->cart );
    }
}

/**
 * Get remote stubs instance
 *
 * @return SkyDonate_Remote_Stubs
 */
function skydonate_remote_stubs() {
    return SkyDonate_Remote_Stubs::instance();
}

// Initialize
skydonate_remote_stubs();
