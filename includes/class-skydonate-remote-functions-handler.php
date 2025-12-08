<?php
/**
 * SkyDonate Remote Functions Handler
 *
 * Handles lazy loading of remote functions with license verification.
 * Remote functions are only loaded when requested and after verifying
 * the license key and domain are valid.
 *
 * @package SkyDonate
 * @since 1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_Remote_Functions_Handler {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Remote functions file path
     */
    private $remote_functions_file;

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
        $this->remote_functions_file = SKYDONATE_INCLUDES_PATH . '/remote-functions.php';
        $this->init_hooks();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception( 'Cannot unserialize singleton' );
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Register AJAX handlers for both logged-in and non-logged-in users
        add_action( 'wp_ajax_skydonate_load_remote_functions', array( $this, 'handle_remote_functions_request' ) );
        add_action( 'wp_ajax_nopriv_skydonate_load_remote_functions', array( $this, 'handle_remote_functions_request' ) );

        // Register AJAX handler for executing remote widget activation
        add_action( 'wp_ajax_skydonate_activate_remote_widget', array( $this, 'handle_widget_activation_request' ) );
    }

    /**
     * Handle AJAX request to load remote functions
     *
     * This endpoint verifies the license key and domain before
     * confirming that remote functions can be accessed.
     */
    public function handle_remote_functions_request() {
        // Verify nonce
        if ( ! check_ajax_referer( 'skydonate_remote_functions_nonce', 'nonce', false ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security verification failed.', 'skydonate' ),
                'code'    => 'invalid_nonce'
            ), 403 );
        }

        // Get license key and domain from request
        $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
        $domain      = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';

        // Validate required parameters
        if ( empty( $license_key ) ) {
            wp_send_json_error( array(
                'message' => __( 'License key is required.', 'skydonate' ),
                'code'    => 'missing_license_key'
            ), 400 );
        }

        if ( empty( $domain ) ) {
            wp_send_json_error( array(
                'message' => __( 'Domain is required.', 'skydonate' ),
                'code'    => 'missing_domain'
            ), 400 );
        }

        // Verify the license
        $verification_result = $this->verify_license( $license_key, $domain );

        if ( ! $verification_result['success'] ) {
            wp_send_json_error( array(
                'message' => $verification_result['message'],
                'code'    => $verification_result['code']
            ), 403 );
        }

        // License is valid - confirm remote functions are available
        wp_send_json_success( array(
            'message'     => __( 'License verified. Remote functions are available.', 'skydonate' ),
            'license_status' => 'valid',
            'features'    => $verification_result['features'] ?? array(),
            'widgets'     => $verification_result['widgets'] ?? array()
        ) );
    }

    /**
     * Handle AJAX request to activate a remote widget
     *
     * This endpoint verifies the license, then loads and executes
     * the remote functions to activate the requested widget.
     */
    public function handle_widget_activation_request() {
        // Verify nonce
        if ( ! check_ajax_referer( 'skydonate_remote_functions_nonce', 'nonce', false ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security verification failed.', 'skydonate' ),
                'code'    => 'invalid_nonce'
            ), 403 );
        }

        // Check user capability
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to perform this action.', 'skydonate' ),
                'code'    => 'insufficient_permissions'
            ), 403 );
        }

        // Get license key and domain from request
        $license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
        $domain      = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';
        $setup       = isset( $_POST['setup'] ) ? sanitize_text_field( wp_unslash( $_POST['setup'] ) ) : '';
        $zip_url     = isset( $_POST['zip_url'] ) ? esc_url_raw( wp_unslash( $_POST['zip_url'] ) ) : '';

        // Validate required parameters
        if ( empty( $license_key ) || empty( $domain ) ) {
            wp_send_json_error( array(
                'message' => __( 'License key and domain are required.', 'skydonate' ),
                'code'    => 'missing_credentials'
            ), 400 );
        }

        if ( empty( $setup ) || empty( $zip_url ) ) {
            wp_send_json_error( array(
                'message' => __( 'Setup configuration and ZIP URL are required.', 'skydonate' ),
                'code'    => 'missing_parameters'
            ), 400 );
        }

        // Verify the license
        $verification_result = $this->verify_license( $license_key, $domain );

        if ( ! $verification_result['success'] ) {
            wp_send_json_error( array(
                'message' => $verification_result['message'],
                'code'    => $verification_result['code']
            ), 403 );
        }

        // License is valid - load remote functions and execute
        $load_result = $this->load_remote_functions();

        if ( ! $load_result['success'] ) {
            wp_send_json_error( array(
                'message' => $load_result['message'],
                'code'    => 'load_failed'
            ), 500 );
        }

        // Execute the widget activation
        try {
            if ( function_exists( 'skydonate_system_properties' ) ) {
                skydonate_system_properties( array(
                    'setup'   => $setup,
                    'zip_url' => $zip_url
                ) );

                wp_send_json_success( array(
                    'message' => __( 'Widget activation completed successfully.', 'skydonate' )
                ) );
            } else {
                wp_send_json_error( array(
                    'message' => __( 'Remote functions not available.', 'skydonate' ),
                    'code'    => 'functions_unavailable'
                ), 500 );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage(),
                'code'    => 'execution_error'
            ), 500 );
        }
    }

    /**
     * Verify license key and domain
     *
     * @param string $license_key The license key to verify
     * @param string $domain The domain to verify
     * @return array Verification result with success status and message
     */
    private function verify_license( $license_key, $domain ) {
        // Get the license client instance
        if ( ! function_exists( 'skydonate_license' ) ) {
            return array(
                'success' => false,
                'message' => __( 'License system is not available.', 'skydonate' ),
                'code'    => 'license_system_unavailable'
            );
        }

        $license_client = skydonate_license();

        // Normalize the provided license key
        $normalized_key = strtoupper( trim( $license_key ) );

        // Normalize the provided domain (remove www prefix for consistency)
        $normalized_domain = strtolower( preg_replace( '/^www\./i', '', trim( $domain ) ) );

        // Get the stored license key
        $stored_key = $license_client->get_key();

        // Get the site's domain
        $site_domain = $license_client->get_domain();

        // Verify the license key matches the stored key
        if ( empty( $stored_key ) || $normalized_key !== strtoupper( trim( $stored_key ) ) ) {
            return array(
                'success' => false,
                'message' => __( 'Invalid license key.', 'skydonate' ),
                'code'    => 'invalid_license_key'
            );
        }

        // Verify the domain matches the site domain
        if ( $normalized_domain !== $site_domain ) {
            return array(
                'success' => false,
                'message' => __( 'Domain mismatch. The provided domain does not match the registered domain.', 'skydonate' ),
                'code'    => 'domain_mismatch'
            );
        }

        // Validate the license with the server
        $validation_result = $license_client->validate( $stored_key );

        if ( empty( $validation_result['success'] ) ) {
            $status = $validation_result['status'] ?? 'unknown';
            $message = $validation_result['message'] ?? __( 'License validation failed.', 'skydonate' );

            return array(
                'success' => false,
                'message' => $message,
                'code'    => 'validation_failed_' . $status
            );
        }

        // License is valid
        return array(
            'success'  => true,
            'message'  => __( 'License verified successfully.', 'skydonate' ),
            'code'     => 'valid',
            'features' => $validation_result['features'] ?? array(),
            'widgets'  => $validation_result['widgets'] ?? array()
        );
    }

    /**
     * Load remote functions file
     *
     * @return array Result with success status and message
     */
    private function load_remote_functions() {
        if ( ! file_exists( $this->remote_functions_file ) ) {
            return array(
                'success' => false,
                'message' => __( 'Remote functions file not found.', 'skydonate' )
            );
        }

        try {
            require_once $this->remote_functions_file;
            return array(
                'success' => true,
                'message' => __( 'Remote functions loaded successfully.', 'skydonate' )
            );
        } catch ( Exception $e ) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Get the nonce for remote functions requests
     *
     * @return string The nonce value
     */
    public static function get_nonce() {
        return wp_create_nonce( 'skydonate_remote_functions_nonce' );
    }

    /**
     * Get localized script data for client-side use
     *
     * @return array Script localization data
     */
    public static function get_localized_data() {
        $license_client = function_exists( 'skydonate_license' ) ? skydonate_license() : null;

        return array(
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => self::get_nonce(),
            'license_key' => $license_client ? $license_client->get_key() : '',
            'domain'      => $license_client ? $license_client->get_domain() : ''
        );
    }
}

/**
 * Get the remote functions handler instance
 *
 * @return SkyDonate_Remote_Functions_Handler
 */
function skydonate_remote_functions_handler() {
    return SkyDonate_Remote_Functions_Handler::instance();
}
