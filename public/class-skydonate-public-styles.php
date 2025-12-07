<?php
/**
 * SkyDonate Public Styles
 *
 * Handles all frontend stylesheet registration and enqueuing
 *
 * @package SkyDonate
 * @since 1.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_Public_Styles {

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
     * Register all public stylesheets
     */
    public function register_styles() {
        // Addon styles
        $this->register_addon_styles();

        // Core styles
        $this->register_core_styles();

        // Library styles
        $this->register_library_styles();
    }

    /**
     * Register addon widget styles
     */
    private function register_addon_styles() {
        $addon_styles = [
            'zakat-calculator'         => 'zakat-calculator.css',
            'zakat-calculator-classic' => 'zakat-calculator-classic.css',
            'zakat-calculator-preview' => 'zakat-calculator-preview.css',
            'donation-button'          => 'donation-button.css',
            'quick-donation'           => 'quick-donation.css',
            'donation-icon-list'       => 'donation-icon-list.css',
            'donation-impact-slider'   => 'donation-impact-slider.css',
            'recent-donation'          => 'recent-donation.css',
            'recent-donation-two'      => 'recent-donation-two.css',
            'donation-progress'        => 'donation-progress.css',
            'donation-card'            => 'donation-card.css',
        ];

        foreach ( $addon_styles as $handle => $file ) {
            wp_register_style(
                $handle,
                SKYDONATE_ASSETS . '/addons/css/' . $file,
                [],
                $this->version
            );
        }
    }

    /**
     * Register core plugin styles
     */
    private function register_core_styles() {
        wp_register_style(
            'skydonate-swiper',
            SKYDONATE_ASSETS . '/css/swiper.min.css',
            [],
            '5.4.5'
        );

        wp_register_style(
            'swiper-override',
            SKYDONATE_PUBLIC_ASSETS . '/css/swiper-override.css',
            [],
            $this->version
        );

        wp_register_style(
            'lity-lightbox',
            SKYDONATE_PUBLIC_ASSETS . '/css/lity-min.css',
            [],
            $this->version
        );
    }

    /**
     * Register third-party library styles
     */
    private function register_library_styles() {
        // Font Awesome
        wp_dequeue_style( 'elementor-icons-shared-0' );
        wp_dequeue_style( 'elementor-icons-fa-solid' );
        wp_deregister_style( 'elementor-icons-shared-0' );
        wp_deregister_style( 'elementor-icons-fa-solid' );

        wp_enqueue_style(
            'fontawesome-all',
            'https://site-assets.fontawesome.com/releases/v7.1.0/css/all.css',
            [],
            null
        );
    }

    /**
     * Enqueue core styles
     */
    public function enqueue_core_styles() {
        // Additional fees
        wp_enqueue_style(
            'additional-fees-styles',
            SKYDONATE_PUBLIC_ASSETS . '/css/additional-fees-styles.css',
            [],
            $this->version
        );

        // Checkout custom style
        if ( skydonate_is_feature_enabled( 'checkout_custom_field_style' ) && sky_status_check( 'checkout_custom_field_style' ) ) {
            wp_enqueue_style(
                'checkout-custom-style',
                SKYDONATE_PUBLIC_ASSETS . '/css/checkout-custom-style.css',
                [],
                $this->version
            );
        }

        // Bootstrap
        wp_enqueue_style(
            'bootstrap',
            SKYDONATE_PUBLIC_ASSETS . '/css/bootstrap-min.css',
            [],
            $this->version
        );

        // Account/Checkout registration
        if ( is_account_page() || is_checkout() ) {
            wp_enqueue_style(
                'wc-registration-style',
                SKYDONATE_PUBLIC_ASSETS . '/css/wc-registration.css',
                [],
                $this->version
            );
        }

        // Main frontend stylesheet
        wp_enqueue_style(
            'frontend-global',
            SKYDONATE_PUBLIC_ASSETS . '/css/frontend-global.css',
            [],
            $this->version
        );

        // Add custom color CSS variables
        $this->add_color_variables();
    }

    /**
     * Enqueue conditional styles based on settings
     */
    public function enqueue_conditional_styles() {
        // Donation goal
        if ( sky_status_check( 'enable_donation_goal' ) ) {
            wp_enqueue_style(
                'donation-goal',
                SKYDONATE_PUBLIC_ASSETS . '/css/donation-goal.css',
                [],
                $this->version
            );
        }

        // Recent donations with country
        if ( sky_status_check( 'recent_donation_list_with_country' ) ) {
            wp_enqueue_style(
                'recent-donations',
                SKYDONATE_PUBLIC_ASSETS . '/css/recent-donations.css',
                [],
                $this->version
            );

            wp_enqueue_style(
                'flag-icons',
                'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css'
            );
        }

        // Account page
        if ( is_account_page() ) {
            wp_enqueue_style(
                'account-page-style',
                SKYDONATE_PUBLIC_ASSETS . '/css/account-page.css',
                [],
                $this->version
            );
        }
    }

    /**
     * Enqueue donation form styles based on layout
     */
    public function enqueue_donation_form_styles() {
        $layout = skydonate_layout_option( 'addons_donation_form_layout' );

        if ( ! is_array( $layout ) ) {
            $layout = [ 'layout1' ];
        }

        if ( in_array( 'layout3', $layout ) ) {
            wp_enqueue_style(
                'donation-form-three',
                SKYDONATE_ASSETS . '/addons/css/donation-form-three.css',
                [],
                $this->version
            );
        } elseif ( in_array( 'layout2', $layout ) ) {
            wp_enqueue_style(
                'donation-form-two',
                SKYDONATE_ASSETS . '/addons/css/donation-form-two.css',
                [],
                $this->version
            );
        } else {
            wp_enqueue_style(
                'donation-form-one',
                SKYDONATE_ASSETS . '/addons/css/donation-form-one.css',
                [],
                $this->version
            );
        }
    }

    /**
     * Add custom color CSS variables
     */
    private function add_color_variables() {
        $accent_color       = get_option( 'skydonation_accent_color', '#3442ad' );
        $accent_dark_color  = get_option( 'skydonation_accent_dark_color', '#282699' );
        $accent_light_color = get_option( 'skydonation_accent_light_color', '#ebecf7' );

        $custom_css = sprintf(
            'body {
                --accent-color: %1$s;
                --accent-dark-color: %2$s;
                --accent-light-color: %3$s;
            }',
            esc_attr( $accent_color ),
            esc_attr( $accent_dark_color ),
            esc_attr( $accent_light_color )
        );

        wp_add_inline_style( 'frontend-global', $custom_css );
    }

    /**
     * Main enqueue method - calls all style registration
     */
    public function enqueue_styles() {
        $this->register_styles();
        $this->enqueue_core_styles();
        $this->enqueue_conditional_styles();
        $this->enqueue_donation_form_styles();
    }
}
