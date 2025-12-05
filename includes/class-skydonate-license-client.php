<?php
/**
 * SkyDonate License Client
 *
 * Validates licenses and manages feature access
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_License_Client {

    /**
     * License server URL
     */
    private $server_url = 'https://skydonate.com';

    /**
     * Cache key for license data
     */
    private $cache_key = 'skydonate_license_data';

    /**
     * Cache expiration (12 hours)
     */
    private $cache_expiration = 43200;

    /**
     * Validate license
     */
    public function validate_license( $license_key, $force_refresh = false ) {
        // Check cache first (unless force refresh)
        if ( ! $force_refresh ) {
            $cached = get_transient( $this->cache_key );
            if ( $cached !== false ) {
                return $cached;
            }
        }

        // Get current domain
        $domain = wp_parse_url( home_url(), PHP_URL_HOST );

        // Make API request
        $response = wp_remote_post( $this->server_url . '/?sky_license_validate=1', array(
            'method'    => 'POST',
            'timeout'   => 30,
            'sslverify' => false,
            'headers'   => array(
                'Content-Type' => 'application/json',
                'User-Agent'   => 'SkyDonate/' . SKYWEB_DONATION_SYSTEM_VERSION . '; ' . home_url(),
            ),
            'body' => wp_json_encode( array(
                'license' => $license_key,
                'domain'  => $domain,
            ) ),
        ) );


        error_log( 'SkyDonate License Validation Response: ' . print_r( $response, true ) );

        // Check for errors
        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => 'Connection error: ' . $response->get_error_message(),
            );
        }

        // Parse response
        $body = trim( wp_remote_retrieve_body( $response ) );
        $body = preg_replace( '/^\xEF\xBB\xBF/', '', $body );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( 'SkyDonate Validate Error - JSON: ' . json_last_error_msg() . ' Body: ' . substr( $body, 0, 500 ) );
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => 'Invalid server response',
            );
        }

        // Cache successful response
        if ( ! empty( $data['success'] ) ) {
            set_transient( $this->cache_key, $data, $this->cache_expiration );
        }

        return $data;
    }

    /**
     * Activate license
     */
    public function activate_license( $license_key ) {
        $domain = wp_parse_url( home_url(), PHP_URL_HOST );

        $response = wp_remote_post( $this->server_url . '/?sky_license_activate=1', array(
            'method'    => 'POST',
            'timeout'   => 30,
            'sslverify' => false,
            'headers'   => array(
                'Content-Type' => 'application/json',
                'User-Agent'   => 'SkyDonate/' . SKYWEB_DONATION_SYSTEM_VERSION . '; ' . home_url(),
            ),
            'body' => wp_json_encode( array(
                'license' => $license_key,
                'domain'  => $domain,
            ) ),
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => 'Connection error: ' . $response->get_error_message(),
            );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $body = trim( wp_remote_retrieve_body( $response ) );

        // Check for HTTP errors
        if ( $response_code !== 200 ) {
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => 'Server returned HTTP ' . $response_code,
            );
        }

        // Remove BOM if present
        $body = preg_replace( '/^\xEF\xBB\xBF/', '', $body );

        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            // Log the actual response for debugging
            error_log( 'SkyDonate License Error - JSON Error: ' . json_last_error_msg() );
            error_log( 'SkyDonate License Error - Response body: ' . substr( $body, 0, 500 ) );
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => 'Invalid server response. Check debug.log for details.',
            );
        }

        // Handle server error responses
        if ( isset( $data['success'] ) && $data['success'] === false ) {
            return array(
                'success' => false,
                'status'  => $data['status'] ?? 'error',
                'message' => $data['message'] ?? 'License activation failed.',
            );
        }

        // Cache successful response and save license key
        if ( ! empty( $data['success'] ) ) {
            update_option( 'skydonate_license_key', $license_key );
            set_transient( $this->cache_key, $data, $this->cache_expiration );
        }

        return $data;
    }

    /**
     * Deactivate license
     */
    public function deactivate_license( $license_key ) {
        $domain = wp_parse_url( home_url(), PHP_URL_HOST );

        $response = wp_remote_post( $this->server_url . '/?sky_license_deactivate=1', array(
            'method'    => 'POST',
            'timeout'   => 30,
            'sslverify' => false,
            'headers'   => array(
                'Content-Type' => 'application/json',
                'User-Agent'   => 'SkyDonate/' . SKYWEB_DONATION_SYSTEM_VERSION . '; ' . home_url(),
            ),
            'body' => wp_json_encode( array(
                'license' => $license_key,
                'domain'  => $domain,
            ) ),
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => 'Connection error: ' . $response->get_error_message(),
            );
        }

        $body = trim( wp_remote_retrieve_body( $response ) );
        $body = preg_replace( '/^\xEF\xBB\xBF/', '', $body );
        $data = json_decode( $body, true );

        // Clear cache and license key
        $this->clear_cache();
        delete_option( 'skydonate_license_key' );

        return $data;
    }

    /**
     * Check for updates
     */
    public function check_for_updates( $license_key, $current_version ) {
        $domain = wp_parse_url( home_url(), PHP_URL_HOST );

        $response = wp_remote_post( $this->server_url . '/?sky_license_update=1', array(
            'method'  => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode( array(
                'license' => $license_key,
                'domain'  => $domain,
                'version' => $current_version,
            ) ),
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = wp_remote_retrieve_body( $response );
        return json_decode( $body, true );
    }

    /**
     * Check if feature is enabled
     */
    public function is_feature_enabled( $feature_key ) {
        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            return false;
        }

        $data = $this->validate_license( $license_key );

        if ( empty( $data['success'] ) || $data['status'] !== 'valid' ) {
            return false;
        }

        return ! empty( $data['features'][ $feature_key ] );
    }

    /**
     * Check if widget is enabled
     */
    public function is_widget_enabled( $widget_key ) {
        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            return false;
        }

        $data = $this->validate_license( $license_key );

        if ( empty( $data['success'] ) || $data['status'] !== 'valid' ) {
            return false;
        }

        return ! empty( $data['widgets'][ $widget_key ] );
    }

    /**
     * Get layout for component
     */
    public function get_layout( $component_key ) {
        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            return 'layout-1';
        }

        $data = $this->validate_license( $license_key );

        if ( empty( $data['success'] ) || $data['status'] !== 'valid' ) {
            return 'layout-1';
        }

        return ! empty( $data['layouts'][ $component_key ] ) ? $data['layouts'][ $component_key ] : 'layout-1';
    }

    /**
     * Get capability
     */
    public function get_capability( $capability_key ) {
        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            return false;
        }

        $data = $this->validate_license( $license_key );

        if ( empty( $data['success'] ) || $data['status'] !== 'valid' ) {
            return false;
        }

        return ! empty( $data['capabilities'][ $capability_key ] );
    }

    /**
     * Get all license data
     */
    public function get_license_data() {
        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            return null;
        }

        return $this->validate_license( $license_key );
    }

    /**
     * Get license status
     */
    public function get_license_status() {
        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            return 'inactive';
        }

        $data = $this->validate_license( $license_key );

        if ( empty( $data['success'] ) ) {
            return 'error';
        }

        return $data['status'] ?? 'unknown';
    }

    /**
     * Clear license cache
     */
    public function clear_cache() {
        delete_transient( $this->cache_key );
    }
}

/**
 * Initialize license client
 */
function skydonate_license_client() {
    static $instance = null;

    if ( $instance === null ) {
        $instance = new SkyDonate_License_Client();
    }

    return $instance;
}

/**
 * Helper functions for easy access
 */
function skydonate_is_feature_enabled( $feature ) {
    return skydonate_license_client()->is_feature_enabled( $feature );
}

function skydonate_is_widget_enabled( $widget ) {
    return skydonate_license_client()->is_widget_enabled( $widget );
}

function skydonate_get_layout( $component ) {
    return skydonate_license_client()->get_layout( $component );
}

function skydonate_has_capability( $capability ) {
    return skydonate_license_client()->get_capability( $capability );
}
