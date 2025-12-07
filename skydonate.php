<?php
/**
 * Plugin Name:       SkyDonate
 * Plugin URI:        https://skywebdesign.co.uk/
 * Description:       A secure, user-friendly donation system built to simplify and manage charitable contributions.
 * Version:           1.2.3
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

    const VERSION = '1.2.3';
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
 * Plugin deactivation cleanup
 */
register_deactivation_hook( __FILE__, function () {
    // Clear currency changer scheduled events
    if ( class_exists( 'Skydonate_Currency_Changer' ) ) {
        Skydonate_Currency_Changer::deactivate();
    }
} );
