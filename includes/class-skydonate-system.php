<?php
/**
 * SkyDonate Main System Class
 *
 * The core plugin class that handles dependencies, hooks, and plugin initialization.
 *
 * @package SkyDonate
 * @since 1.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Skydonate_System {

    /**
     * The loader responsible for maintaining and registering hooks
     *
     * @var Skydonate_Loader
     */
    protected $loader;

    /**
     * The unique identifier of this plugin
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     *
     * @var string
     */
    protected $version;

    /**
     * Initialize the plugin
     */
    public function __construct() {
        if ( defined( 'SKYDONATE_VERSION' ) ) {
            $this->version = SKYDONATE_VERSION;
        } else {
            $this->version = '1.2.3';
        }
        $this->plugin_name = 'skydonate';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->verification();

        // Initialize WooCommerce integration classes on init to avoid early translation loading
        add_action( 'init', [ $this, 'init_woocommerce_integrations' ], 0 );
    }

    /**
     * Initialize WooCommerce integration classes after translations are loaded
     */
    public function init_woocommerce_integrations() {
        // Initialize classes that interact with WooCommerce
        if ( class_exists( 'Skydonate_Shortcode' ) ) {
            new Skydonate_Shortcode();
        }
        if ( class_exists( 'Skydonate_Extra_Donation' ) ) {
            new Skydonate_Extra_Donation();
        }
        if ( class_exists( 'Skydonate_Metabox' ) ) {
            new Skydonate_Metabox();
        }
    }

    /**
     * Load all required plugin dependencies
     */
    private function load_dependencies() {
        // Core functionality
        $this->include_file( 'includes/class-skydonate-snippet-functions.php' );
        $this->include_file( 'includes/class-skydonate-loader.php' );
        $this->include_file( 'includes/class-skydonate-i18n.php' );

        // License and updater
        $this->include_file( 'includes/class-skydonate-license-client.php' );
        $this->include_file( 'includes/class-skydonate-updater.php' );

        // Remote functions loader
        $this->include_file( 'includes/class-skydonate-remote-functions.php' );

        // Admin functionality
        $this->include_file( 'admin/class-skydonate-admin.php' );
        $this->include_file( 'admin/class-skydonate-settings.php' );
        $this->include_file( 'admin/class-skydonate-dashboard.php' );
        $this->include_file( 'admin/class-skydonate-license.php' );

        // Initialize license, updater, and remote functions
        if ( function_exists( 'skydonate_license' ) ) {
            skydonate_license();
        }
        if ( function_exists( 'skydonate_updater' ) ) {
            skydonate_updater();
        }
        if ( function_exists( 'skydonate_remote_functions' ) ) {
            skydonate_remote_functions();
        }

        // Public functionality
        $this->include_file( 'public/class-skydonate-public.php' );
        $this->include_file( 'public/class-skydonate-public-styles.php' );
        $this->include_file( 'public/class-skydonate-public-scripts.php' );

        // Global functions and helpers
        $this->include_file( 'includes/functions.php' );
        $this->include_file( 'includes/class-skydonate-helper-functions.php' );

        // WooCommerce integration
        $this->include_file( 'includes/class-skydonate-wc-donation-settings.php' );
        $this->include_file( 'includes/class-skydonate-metabox.php' );
        $this->include_file( 'includes/class-skydonate-shortcode.php' );
        $this->include_file( 'includes/class-skydonate-extra-donation.php' );


        if ( skydonate_is_feature_enabled( 'donation_fees' ) ) {
            $this->include_file( 'includes/class-skydonate-donation-fees.php' );
        }

        // Currency changer
        if ( sky_status_check( 'skydonate_currency_changer_enabled' )) {
            $this->include_file( 'includes/class-skydonate-currency.php' );
            new Skydonate_Currency_Changer();
        }

        // Notification system
        if ( skydonate_is_feature_enabled( 'notification' ) && ! empty( get_option( 'notification_select_donations', [] ) ) ) {
            $this->include_file( 'includes/class-skydonate-notification.php' );
        }

        // Donation module
        if ( sky_status_check( 'enable_sky_donations_module' ) && skydonate_is_feature_enabled( 'sky_donations_module' ) ) {
            $this->include_file( 'includes/class-skydonate-wc-donation-options.php' );
            $this->include_file( 'includes/class-skydonate-wc-field-visibility.php' );
            $this->conditionally_initialize_class( 'WC_Custom_Donation_Options' );
            $this->conditionally_initialize_class( 'WC_Field_Visibility' );
        }

        // Gift Aid
        if( skydonate_is_feature_enabled( 'enhanced_gift_aid' ) && sky_status_check( 'enable_gift_aid' ) ){
            $this->include_file( 'includes/class-skydonate-gift-aid.php' );
        }

        // Address autoload
        if ( sky_status_check( 'address_autoload_status' ) ) {
            $this->include_file( 'includes/class-skydonate-address-autoload.php' );
        }

        // Recent donations
        if ( skydonate_is_feature_enabled( 'recent_donation_country' ) && sky_status_check( 'recent_donation_list_with_country' ) ) {
            $this->include_file( 'includes/class-skydonate-wc-recent-donations.php' );
            $this->conditionally_initialize_class( 'WC_Recent_Donations' );
        }

        // Auto complete processing
        if ( skydonate_is_feature_enabled( 'auto_complete_processing' ) && sky_status_check( 'auto_complete_processing' ) ) {
            $this->include_file( 'includes/class-skydonate-wc-auto-complete.php' );
            $this->conditionally_initialize_class( 'WC_Auto_Complete_Processing' );
        }

        // Donation goal
        if ( skydonate_is_feature_enabled( 'donation_goal' ) && sky_status_check( 'enable_donation_goal' ) ) {
            $this->include_file( 'includes/class-skydonate-wc-donation-goal.php' );
            $this->conditionally_initialize_class( 'WC_Donation_Goal' );
        }

        // Title prefix
        if ( skydonate_is_feature_enabled( 'title_prefix' ) && sky_status_check( 'enable_title_prefix' ) ) {
            $this->include_file( 'includes/class-skydonate-wc-title-prefix.php' );
            $this->conditionally_initialize_class( 'WC_Title_Prefix' );
        }

        // Elementor addons
        if ( class_exists( 'Elementor\Plugin' ) ) {
            $this->include_file( 'includes/class-skydonate-elementor-addons.php' );
            $this->include_file( 'includes/class-skydonate-icon-manager.php' );
        }

        $this->loader = new Skydonate_Loader();
    }

    /**
     * Set up plugin internationalization
     *
     * Load textdomain on init action to comply with WordPress 6.7+ timing requirements
     */
    private function set_locale() {
        $plugin_i18n = new Skydonate_i18n();
        // Load textdomain on init instead of plugins_loaded to avoid early translation loading warnings
        $this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain', 0 );
    }

    /**
     * Register all admin-related hooks
     */
    private function define_admin_hooks() {
        $plugin_admin = new Skydonate_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
        $this->loader->add_action( 'admin_dashboard_menu_tabs', $plugin_admin, 'admin_dashboard_menu_tabs' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_elementor_widgets' );
        $this->loader->add_action( 'skydonate_menus', $plugin_admin, 'skydonate_menus' );
        $this->loader->add_filter( 'skydonate_menu_array', $plugin_admin, 'skydonate_menu_array' );
        $this->loader->add_filter( 'skydonate_general_settings_tabs', $plugin_admin, 'skydonate_general_settings_tabs' );
    }

    /**
     * Register all public-facing hooks
     */
    private function define_public_hooks() {
        $plugin_public = new Skydonate_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_filter( 'body_class', $plugin_public, 'add_checkout_custom_style_class' );
        $this->loader->add_filter( 'woocommerce_locate_template', $plugin_public, 'sky_donation_woocommerce_myaccount_login_template', 10, 3 );

        if ( skydonate_is_feature_enabled( 'custom_login_form' ) && sky_status_check( 'enable_custom_login_form' ) ) {
            $this->loader->add_filter( 'woocommerce_locate_template', $plugin_public, 'sky_donation_woocommerce_myaccount_custom_login_template', 10, 3 );
        }

        $this->loader->add_filter( 'woocommerce_hidden_order_itemmeta', $plugin_public, 'custom_woocommerce_hidden_order_itemmeta' );
        $this->loader->add_filter( 'woocommerce_order_item_get_formatted_meta_data', $plugin_public, 'custom_hide_order_item_meta', 10, 2 );
        $this->loader->add_filter( 'woocommerce_order_item_display_meta_key', $plugin_public, 'custom_hide_order_item_meta_in_emails', 10, 3 );
        $this->loader->add_action( 'woocommerce_after_order_itemmeta', $plugin_public, 'display_custom_order_item_meta', 10, 3 );
        $this->loader->add_action( 'woocommerce_saved_order_items', $plugin_public, 'save_custom_order_item_meta', 10, 2 );
        $this->loader->add_action( 'wp_loaded', $plugin_public, 'remove_woocommerce_widget_shopping_cart_total', 10, 2 );
    }

    /**
     * Load a plugin template file
     *
     * @param string $content_path Path to the template file
     */
    public function load_plugin_template( $content_path ) {
        if ( file_exists( $content_path ) ) {
            include $content_path;
        } else {
            $notice = sprintf(
                esc_html__( 'Unable to locate file at location "%s". Some features may not work properly in this plugin. Please contact us!', 'skydonate' ),
                $content_path
            );
            $this->plugin_admin_notice( $notice, 'error' );
        }
    }

    /**
     * Display an admin notice
     *
     * @param string $message The notice message
     * @param string $type    The notice type (error, update, success, update-nag)
     */
    public static function plugin_admin_notice( $message, $type = 'error' ) {
        $classes = 'notice ';

        switch ( $type ) {
            case 'update':
                $classes .= 'updated is-dismissible';
                break;
            case 'update-nag':
                $classes .= 'update-nag is-dismissible';
                break;
            case 'success':
                $classes .= 'notice-success is-dismissible';
                break;
            default:
                $classes .= 'notice-error is-dismissible';
        }

        printf(
            '<div class="%s"><p>%s</p></div>',
            esc_attr( $classes ),
            esc_html( $message )
        );
    }

    /**
     * Run the loader to execute all hooks
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Get the plugin name
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Get the loader instance
     *
     * @return Skydonate_Loader
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Get the plugin version
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Include a file if it exists
     *
     * @param string $file Relative path from plugin directory
     */
    private function include_file( $file ) {
        $filepath = SKYDONATE_DIR_PATH . $file;
        if ( file_exists( $filepath ) ) {
            require_once $filepath;
        }
    }

    /**
     * Initialize a class if it exists
     *
     * @param string $class Class name to initialize
     */
    private function conditionally_initialize_class( $class ) {
        if ( class_exists( $class ) ) {
            new $class();
        }
    }

    /**
     * Placeholder for license verification
     */
    public function verification() {}
}

// Backwards compatibility alias
class_alias( 'Skydonate_System', 'Skyweb_Donation_System' );
