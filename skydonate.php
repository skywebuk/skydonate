<?php
/**
 * Plugin Name:       SkyDonate
 * Plugin URI:        https://skywebdesign.co.uk/
 * Description:       A secure, user-friendly donation system built to simplify and manage charitable contributions.
 * Version:           2.0.21
 * Author:            Sky Web Design
 * Author URI:        https://skywebdesign.co.uk/
 * Text Domain:       skydonate
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WooCommerce check
 */
function skydonate_is_wc_active() {

    $active = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

    if ( in_array( 'woocommerce/woocommerce.php', $active, true ) ) {
        return true;
    }

    if ( is_multisite() ) {
        $network = array_keys( get_site_option( 'active_sitewide_plugins', [] ) );
        return in_array( 'woocommerce/woocommerce.php', $network, true );
    }

    return false;
}

if ( ! skydonate_is_wc_active() ) {

    add_action( 'admin_notices', function () {
        echo '<div class="error"><p>'
            . esc_html__( 'SkyDonate requires WooCommerce. Please install and activate WooCommerce first.', 'skydonate' )
            . '</p></div>';
    } );

    add_action( 'admin_init', function () {
        $plugin = plugin_basename( __FILE__ );

        if ( is_plugin_active( $plugin ) ) {
            deactivate_plugins( $plugin );
        }

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    } );

    return;
}

/**
 * ---------------------------------------------------------
 *  SKYDONATE MAIN CLASS - WITH ORIGINAL CONSTANT NAMES
 * ---------------------------------------------------------
 */
final class SkyDonate {

    const VERSION = '2.0.21';
    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->start_plugin();
    }

    private function define_constants() {

        $this->define_const( 'SKYDONATE_VERSION', self::VERSION );
        $this->define_const( 'SKYDONATE_FILE', __FILE__ );
        $this->define_const( 'SKYDONATE_BASE', plugin_basename( __FILE__ ) );
        $this->define_const( 'SKYDONATE_DIR_PATH', plugin_dir_path( __FILE__ ) );

        $this->define_const( 'SKYDONATE_INCLUDES_PATH', SKYDONATE_DIR_PATH . 'includes' );
        $this->define_const( 'SKYDONATE_ADMIN_PATH', SKYDONATE_DIR_PATH . 'admin' );
        $this->define_const( 'SKYDONATE_PUBLIC_PATH', SKYDONATE_DIR_PATH . 'public' );

        $this->define_const( 'SKYDONATE_URL', plugin_dir_url( __FILE__ ) );
        $this->define_const( 'SKYDONATE_PUBLIC_ASSETS', SKYDONATE_URL . 'public' );
        $this->define_const( 'SKYDONATE_ADMIN_ASSETS', SKYDONATE_URL . 'admin/' );
        $this->define_const( 'SKYDONATE_ASSETS', SKYDONATE_URL . 'public' );

        $this->define_const( 'SKYDONATE_OPTION_URL', admin_url( 'admin-post.php' ) );

        // Alias constants for license system compatibility
        $this->define_const( 'SKYDONATE_VERSION', self::VERSION );
        $this->define_const( 'SKYDONATE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        $this->define_const( 'SKYDONATE_PLUGIN_FILE', __FILE__ );
        $this->define_const( 'SKYDONATE_PLUGIN_BASE', plugin_basename( __FILE__ ) );
    }

    private function define_const( $key, $value ) {
        if ( ! defined( $key ) ) {
            define( $key, $value );
        }
    }

    private function load_dependencies() {
        require_once SKYDONATE_INCLUDES_PATH . '/class-skydonate-system.php';
    }

    private function start_plugin() {
        $plugin = new Skydonate_System();
        $plugin->run();

        $GLOBALS['SKDS']        = $plugin;
        $GLOBALS['SKDS_notice'] = false;
    }
}

/**
 * Loader
 */
add_action( 'plugins_loaded', function () {
    SkyDonate::instance();
}, 20 );

/**
 * Plugin activation - recalculate donation meta
 */
register_activation_hook( __FILE__, 'skydonate_activation_recalculate_meta' );

function skydonate_activation_recalculate_meta() {
    // Schedule recalculation to run after plugin is fully loaded
    if ( ! wp_next_scheduled( 'skydonate_recalculate_all_donations' ) ) {
        wp_schedule_single_event( time() + 5, 'skydonate_recalculate_all_donations' );
    }
}

add_action( 'skydonate_recalculate_all_donations', 'skydonate_do_recalculate_all_donations' );

/**
 * Recalculate donation meta for all products
 */
function skydonate_do_recalculate_all_donations() {
    global $wpdb;

    // Get all products
    $products = get_posts( array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'publish',
    ) );

    if ( empty( $products ) ) {
        return;
    }

    $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
    $order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';

    // Check if HPOS is enabled
    $hpos_enabled = false;
    if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
        $hpos_enabled = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    foreach ( $products as $product_id ) {
        // Get order count and total for this product from completed orders
        if ( $hpos_enabled ) {
            $orders_table = $wpdb->prefix . 'wc_orders';

            // Get completed order IDs containing this product
            $results = $wpdb->get_row( $wpdb->prepare( "
                SELECT
                    COUNT(DISTINCT oi.order_id) as order_count,
                    COALESCE(SUM(
                        CASE WHEN oim_total.meta_value IS NOT NULL
                        THEN CAST(oim_total.meta_value AS DECIMAL(10,2))
                        ELSE 0 END
                    ), 0) as total_amount
                FROM {$order_items_table} AS oi
                INNER JOIN {$order_itemmeta_table} AS oim ON oi.order_item_id = oim.order_item_id
                LEFT JOIN {$order_itemmeta_table} AS oim_total ON oi.order_item_id = oim_total.order_item_id AND oim_total.meta_key = '_line_total'
                INNER JOIN {$orders_table} AS o ON oi.order_id = o.id
                WHERE oi.order_item_type = 'line_item'
                AND oim.meta_key = '_product_id'
                AND oim.meta_value = %d
                AND o.status = 'wc-completed'
            ", $product_id ) );
        } else {
            // Legacy - use posts table
            $results = $wpdb->get_row( $wpdb->prepare( "
                SELECT
                    COUNT(DISTINCT oi.order_id) as order_count,
                    COALESCE(SUM(
                        CASE WHEN oim_total.meta_value IS NOT NULL
                        THEN CAST(oim_total.meta_value AS DECIMAL(10,2))
                        ELSE 0 END
                    ), 0) as total_amount
                FROM {$order_items_table} AS oi
                INNER JOIN {$order_itemmeta_table} AS oim ON oi.order_item_id = oim.order_item_id
                LEFT JOIN {$order_itemmeta_table} AS oim_total ON oi.order_item_id = oim_total.order_item_id AND oim_total.meta_key = '_line_total'
                INNER JOIN {$wpdb->posts} AS p ON oi.order_id = p.ID
                WHERE oi.order_item_type = 'line_item'
                AND oim.meta_key = '_product_id'
                AND oim.meta_value = %d
                AND p.post_status = 'wc-completed'
            ", $product_id ) );
        }

        $order_count = $results ? intval( $results->order_count ) : 0;
        $total_amount = $results ? floatval( $results->total_amount ) : 0;

        // Update product meta
        update_post_meta( $product_id, '_order_count', $order_count );
        update_post_meta( $product_id, '_total_sales_amount', $total_amount );
    }
}

/**
 * Plugin deactivation cleanup
 */
register_deactivation_hook( __FILE__, function () {
    // Clear currency changer scheduled events
    if ( class_exists( 'Skydonate_Currency_Changer' ) ) {
        Skydonate_Currency_Changer::deactivate();
    }

    // Clear any pending recalculation
    wp_clear_scheduled_hook( 'skydonate_recalculate_all_donations' );
} );
