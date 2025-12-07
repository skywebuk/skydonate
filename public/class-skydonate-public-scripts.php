<?php
/**
 * SkyDonate Public Scripts
 *
 * Handles all frontend JavaScript registration and enqueuing
 *
 * @package SkyDonate
 * @since 1.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_Public_Scripts {

    /**
     * Plugin version
     */
    private $version;

    /**
     * Constructor
     */
    public function __construct( $version = SKYDONATE_VERSION ) {
        $this->version = $version;
    }

    /**
     * Register all public scripts
     */
    public function register_scripts() {
        // Addon scripts
        $this->register_addon_scripts();

        // Core scripts
        $this->register_core_scripts();

        // Library scripts
        $this->register_library_scripts();
    }

    /**
     * Register addon widget scripts
     */
    private function register_addon_scripts() {
        $addon_scripts = [
            'zakat-calculator-classic' => 'zakat-calculator-classic.js',
            'zakat-calculator-preview' => 'zakat-calculator-preview.js',
            'recent-donation'          => 'recent-donation.js',
            'recent-donation-two'      => 'recent-donation-two.js',
            'quick-donation'           => 'quick-donation.js',
            'gift-aid-toggle'          => 'gift-aid-toggle.js',
            'donation-progress'        => 'donation-progress.js',
            'donation-card'            => 'donation-card.js',
            'donation-impact-slider'   => 'donation-impact-slider.js',
            'donation-button'          => 'donation-button.js',
            'donation-icon-list'       => 'donation-icon-list.js',
        ];

        foreach ( $addon_scripts as $handle => $file ) {
            wp_register_script(
                $handle,
                SKYDONATE_ASSETS . '/addons/js/' . $file,
                [ 'jquery' ],
                $this->version,
                true
            );
        }

        // Localize addon scripts
        $this->localize_addon_scripts();
    }

    /**
     * Localize addon scripts with AJAX data
     */
    private function localize_addon_scripts() {
        $ajax_data = [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'skydonate_nonce' ),
            'cart_url' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
        ];

        $scripts_with_cart = [
            'zakat-calculator',
            'zakat-calculator-classic',
            'zakat-calculator-preview',
        ];

        $scripts_without_cart = [
            'donation-form',
            'quick-donation',
            'recent-donation',
            'recent-donation-two',
        ];

        foreach ( $scripts_with_cart as $handle ) {
            wp_localize_script( $handle, 'skydonate_extra_donation_ajax', $ajax_data );
        }

        $ajax_data_no_cart = [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'skydonate_nonce' ),
        ];

        foreach ( $scripts_without_cart as $handle ) {
            wp_localize_script( $handle, 'skydonate_extra_donation_ajax', $ajax_data_no_cart );
        }

        // Donation card specific localization
        wp_localize_script( 'donation-card', 'skydonateDonation', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ] );
    }

    /**
     * Register core plugin scripts
     */
    private function register_core_scripts() {
        wp_register_script(
            'lity-lightbox',
            SKYDONATE_PUBLIC_ASSETS . '/js/lity-min.js',
            [ 'jquery' ],
            $this->version,
            true
        );

        wp_register_script(
            'skydonate-swiper',
            SKYDONATE_ASSETS . '/js/swiper.min.js',
            [ 'jquery' ],
            '5.4.5',
            true
        );
    }

    /**
     * Register third-party library scripts
     */
    private function register_library_scripts() {
        wp_register_script(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11',
            [ 'jquery' ],
            null,
            true
        );
    }

    /**
     * Enqueue core scripts
     */
    public function enqueue_core_scripts() {
        // Account/Checkout registration script
        if ( is_account_page() || is_checkout() ) {
            wp_enqueue_script(
                'wc-registration-script',
                SKYDONATE_PUBLIC_ASSETS . '/js/wc-registration.js',
                [ 'jquery' ],
                $this->version,
                true
            );
        }

        // SweetAlert2
        wp_enqueue_script( 'sweetalert2' );

        // Single product script
        wp_enqueue_script(
            'wc-single-script',
            SKYDONATE_PUBLIC_ASSETS . '/js/wc-single.js',
            [ 'jquery' ],
            $this->version,
            true
        );

        // Frontend global script
        wp_enqueue_script(
            'sky-frontend-global',
            SKYDONATE_PUBLIC_ASSETS . '/js/frontend-global.js',
            [ 'jquery' ],
            $this->version,
            true
        );

        // jQuery UI Datepicker
        wp_enqueue_script( 'jquery-ui-datepicker' );
    }

    /**
     * Enqueue conditional scripts based on settings
     */
    public function enqueue_conditional_scripts() {
        // Account page
        if ( is_account_page() ) {
            wp_enqueue_script(
                'account-page',
                SKYDONATE_PUBLIC_ASSETS . '/js/account-page.js',
                [ 'jquery' ],
                $this->version,
                true
            );

            wp_localize_script( 'account-page', 'account_page_ajax', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'save_account_data' ),
            ] );
        }
    }

    /**
     * Enqueue donation form scripts based on layout
     */
    public function enqueue_donation_form_scripts() {

        if ( skydonate_get_layout('addons_donation_form') == 'layout-3' ) {
            wp_enqueue_script(
                'donation-form',
                SKYDONATE_ASSETS . '/addons/js/donation-form-three.js',
                [ 'jquery' ],
                $this->version,
                true
            );
        } else {
            wp_enqueue_script(
                'donation-form',
                SKYDONATE_ASSETS . '/addons/js/donation-form.js',
                [ 'jquery' ],
                $this->version,
                true
            );
        }

        wp_localize_script( 'donation-form', 'skydonate_extra_donation_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'skydonate_nonce' ),
        ] );
    }

    /**
     * Main enqueue method - calls all script registration
     */
    public function enqueue_scripts() {
        $this->register_scripts();
        $this->enqueue_core_scripts();
        $this->enqueue_conditional_scripts();
        $this->enqueue_donation_form_scripts();
    }
}
