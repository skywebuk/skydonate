<?php
/**
 * SkyDonate Remote WooCommerce Product Panels
 *
 * Loads WooCommerce product data panel functions from the remote server.
 * The actual panel rendering functions are defined in remote functions,
 * while hooks are registered locally for proper timing.
 *
 * @package SkyDonate
 * @since 2.0.12
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_Remote_WC_Panels {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return SkyDonate_Remote_WC_Panels
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
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     * Hooks are registered on admin_init to ensure remote functions have loaded
     */
    private function init_hooks() {
        // Register WooCommerce hooks after remote functions have loaded
        // Remote functions load on 'init' priority 5, so admin_init is safe
        add_action( 'admin_init', array( $this, 'register_wc_hooks' ) );
    }

    /**
     * Register WooCommerce product data hooks
     * These call functions that are defined in remote functions
     */
    public function register_wc_hooks() {
        // Only load on product edit screens
        if ( ! $this->is_product_edit_screen() ) {
            return;
        }

        // Register product data panels hook - function defined on server
        add_action( 'woocommerce_product_data_panels', array( $this, 'render_product_data_panels' ) );
    }

    /**
     * Check if we're on a product edit screen
     *
     * @return bool
     */
    private function is_product_edit_screen() {
        global $pagenow, $typenow;

        // Check for post.php or post-new.php with product type
        if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
            if ( isset( $_GET['post'] ) ) {
                $post_type = get_post_type( absint( $_GET['post'] ) );
                return $post_type === 'product';
            }
            if ( isset( $_GET['post_type'] ) ) {
                return $_GET['post_type'] === 'product';
            }
            if ( $typenow === 'product' ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render product data panels - wrapper for remote function
     * Calls the server-defined skydonate_render_product_data_panels function
     */
    public function render_product_data_panels() {
        // Check if the remote function is available
        if ( function_exists( 'skydonate_render_product_data_panels' ) ) {
            skydonate_render_product_data_panels();
        } else {
            // Log if function not available (only in debug mode)
            $this->log( 'skydonate_render_product_data_panels function not available - check remote functions' );
        }
    }

    /**
     * Log debug messages
     *
     * @param string $message Message to log
     */
    private function log( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[SkyDonate Remote WC Panels] ' . $message );
        }
    }
}

/**
 * Get remote WC panels instance
 *
 * @return SkyDonate_Remote_WC_Panels
 */
function skydonate_remote_wc_panels() {
    return SkyDonate_Remote_WC_Panels::instance();
}
