<?php
/**
 * SkyDonate Autoloader
 *
 * Centralized file to include all plugin dependencies
 *
 * @package SkyDonate
 * @since 1.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load a plugin file if it exists
 *
 * @param string $file Relative path from plugin directory
 * @return bool True if file was loaded
 */
function skydonate_load_file( $file ) {
    $filepath = SKYDONATE_DIR_PATH . $file;
    if ( file_exists( $filepath ) ) {
        require_once $filepath;
        return true;
    }
    return false;
}

/**
 * Core includes - always loaded
 */
function skydonate_load_core_includes() {
    // Core classes
    skydonate_load_file( 'includes/class-skydonate-loader.php' );
    skydonate_load_file( 'includes/class-skydonate-i18n.php' );

    // Security (load before license for HMAC signing)
    skydonate_load_file( 'includes/class-skydonate-security.php' );

    // License and updater
    skydonate_load_file( 'includes/class-skydonate-license-client.php' );
    skydonate_load_file( 'includes/class-skydonate-updater.php' );

    // Functions
    skydonate_load_file( 'includes/functions.php' );
    skydonate_load_file( 'includes/class-skydonate-helper-functions.php' );
    skydonate_load_file( 'includes/class-skydonate-snippet-functions.php' );
}

/**
 * Admin includes
 */
function skydonate_load_admin_includes() {
    skydonate_load_file( 'admin/class-skydonate-admin.php' );
    skydonate_load_file( 'admin/class-skydonate-settings.php' );
    skydonate_load_file( 'admin/class-skydonate-dashboard.php' );
    skydonate_load_file( 'admin/class-skydonate-license.php' );
}

/**
 * Public includes
 */
function skydonate_load_public_includes() {
    skydonate_load_file( 'public/class-skydonate-public.php' );
    skydonate_load_file( 'public/class-skydonate-public-styles.php' );
    skydonate_load_file( 'public/class-skydonate-public-scripts.php' );
}

/**
 * WooCommerce feature includes
 */
function skydonate_load_woocommerce_includes() {
    skydonate_load_file( 'includes/class-skydonate-wc-donation-settings.php' );
    skydonate_load_file( 'includes/class-skydonate-metabox.php' );
    skydonate_load_file( 'includes/class-skydonate-shortcode.php' );
    skydonate_load_file( 'includes/class-skydonate-donation-fees.php' );
    skydonate_load_file( 'includes/class-skydonate-extra-donation.php' );
    skydonate_load_file( 'includes/class-skydonate-gift-aid.php' );
}

/**
 * Conditional includes based on settings
 */
function skydonate_load_conditional_includes() {
    // Currency changer
    if ( skydonate_is_feature_enabled( 'currency_changer' ) && get_option( 'skydonate_currency_changer_enabled', 0 ) == 1 ) {
        skydonate_load_file( 'includes/class-skydonate-currency.php' );
    }

    // Notification
    if ( skydonate_is_feature_enabled( 'notification' ) && ! empty( get_option( 'notification_select_donations', [] ) ) ) {
        skydonate_load_file( 'includes/class-skydonate-notification.php' );
    }

    // Donation module
    if ( sky_status_check( 'enable_sky_donations_module' ) && skydonate_is_feature_enabled( 'sky_donations_module' ) ) {
        skydonate_load_file( 'includes/class-skydonate-wc-donation-options.php' );
        skydonate_load_file( 'includes/class-skydonate-wc-field-visibility.php' );
    }

    // Address autoload
    if ( skydonate_is_feature_enabled( 'address_autocomplete' ) && sky_status_check( 'address_autoload_status' ) ) {
        skydonate_load_file( 'includes/class-skydonate-address-autoload.php' );
    }

    // Recent donations
    if ( skydonate_is_feature_enabled( 'recent_donation_country' ) && sky_status_check( 'recent_donation_list_with_country' ) ) {
        skydonate_load_file( 'includes/class-skydonate-wc-recent-donations.php' );
    }

    // Auto complete processing
    if ( skydonate_is_feature_enabled( 'auto_complete_processing' ) && sky_status_check( 'auto_complete_processing' ) ) {
        skydonate_load_file( 'includes/class-skydonate-wc-auto-complete.php' );
    }

    // Donation goal
    if ( skydonate_is_feature_enabled( 'donation_goal' ) && sky_status_check( 'enable_donation_goal' ) ) {
        skydonate_load_file( 'includes/class-skydonate-wc-donation-goal.php' );
    }

    // Title prefix
    if ( skydonate_is_feature_enabled( 'title_prefix' ) && sky_status_check( 'enable_title_prefix' ) ) {
        skydonate_load_file( 'includes/class-skydonate-wc-title-prefix.php' );
    }
}

/**
 * Elementor addon includes
 */
function skydonate_load_elementor_includes() {
    if ( ! class_exists( 'Elementor\Plugin' ) ) {
        return;
    }

    skydonate_load_file( 'includes/class-skydonate-elementor-addons.php' );
    skydonate_load_file( 'includes/class-skydonate-icon-manager.php' );

    // Load individual addon widgets
    $addon_files = [
        'class-skydonate-addon-button.php',
        'class-skydonate-addon-card.php',
        'class-skydonate-addon-card-2.php',
        'class-skydonate-addon-form.php',
        'class-skydonate-addon-gift-aid-toggle.php',
        'class-skydonate-addon-icon-list.php',
        'class-skydonate-addon-impact-slider.php',
        'class-skydonate-addon-metal-values.php',
        'class-skydonate-addon-progress.php',
        'class-skydonate-addon-progress-2.php',
        'class-skydonate-addon-qurbani-status.php',
        'class-skydonate-addon-recent-order.php',
        'class-skydonate-addon-zakat-calculator-classic.php',
        'class-skydonate-addon-zakat-preview.php',
        'class-skydonate-addon-extra-donation.php',
        'class-skydonate-addon-quick-donate.php',
    ];

    foreach ( $addon_files as $file ) {
        skydonate_load_file( 'includes/addons/' . $file );
    }
}

/**
 * Load all plugin dependencies
 */
function skydonate_load_all_dependencies() {
    // Core files first
    skydonate_load_core_includes();

    // Initialize license
    if ( function_exists( 'skydonate_license' ) ) {
        skydonate_license();
    }
    if ( function_exists( 'skydonate_updater' ) ) {
        skydonate_updater();
    }

    // Admin files
    skydonate_load_admin_includes();

    // Public files
    skydonate_load_public_includes();

    // WooCommerce features
    skydonate_load_woocommerce_includes();

    // Conditional features
    skydonate_load_conditional_includes();

    // Elementor addons
    skydonate_load_elementor_includes();
}
