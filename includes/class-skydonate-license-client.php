<?php
/**
 * SkyDonate License Client
 *
 * Handles license validation, activation, and deactivation
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
     * Option name for license key
     */
    private $license_option = 'skydonate_license_key';

    /**
     * Transient name for cached data
     */
    private $cache_key = 'skydonate_license_data';

    /**
     * Cache duration (12 hours)
     */
    private $cache_duration = 43200;

    /**
     * Make API request to license server
     */
    private function api_request( $endpoint, $data = [] ) {

        $url = $this->server_url . '/?sky_license_' . $endpoint . '=1';

        $args = [
            'method'    => 'POST',
            'timeout'   => 30,
            'sslverify' => false,
            'headers'   => [
                'Content-Type' => 'application/json',
                'User-Agent'   => 'Mozilla/5.0',
            ],
            'body'        => wp_json_encode($data),
            'data_format' => 'body', // CRITICAL!!!
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Request failed: ' . $response->get_error_message()
            ];
        }

        $body = wp_remote_retrieve_body($response);

        $json = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Invalid JSON input from server: ' . substr($body, 0, 200)
            ];
        }

        return $json;
    }



    /**
     * Get current domain
     */
    private function get_domain() {
        return wp_parse_url( home_url(), PHP_URL_HOST );
    }

    /**
     * Validate license with server
     */
    public function validate( $license_key = null, $force = false ) {
        if ( $license_key === null ) {
            $license_key = $this->get_key();
        }

        if ( empty( $license_key ) ) {
            return array(
                'success' => false,
                'status'  => 'inactive',
                'message' => 'No license key',
            );
        }

        // Check cache
        if ( ! $force ) {
            $cached = get_transient( $this->cache_key );
            if ( $cached !== false && isset( $cached['license_key'] ) && $cached['license_key'] === $license_key ) {
                return $cached;
            }
        }

        // Make API request
        $result = $this->api_request( 'validate', array(
            'license' => $license_key,
            'domain'  => $this->get_domain(),
        ) );

        // Cache successful response
        if ( ! empty( $result['success'] ) ) {
            $result['license_key'] = $license_key;
            set_transient( $this->cache_key, $result, $this->cache_duration );
        }

        return $result;
    }

    /**
     * Activate license
     */
    public function activate( $license_key ) {
        if ( empty( $license_key ) ) {
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => 'License key is required',
            );
        }

        $result = $this->api_request( 'activate', array(
            'license' => $license_key,
            'domain'  => $this->get_domain(),
        ) );

        // Save on success
        if ( ! empty( $result['success'] ) && $result['status'] === 'valid' ) {
            update_option( $this->license_option, $license_key );
            $result['license_key'] = $license_key;
            set_transient( $this->cache_key, $result, $this->cache_duration );
        }

        return $result;
    }

    /**
     * Deactivate license
     */
    public function deactivate() {
        $license_key = $this->get_key();

        if ( empty( $license_key ) ) {
            return array(
                'success' => false,
                'status'  => 'error',
                'message' => 'No license to deactivate',
            );
        }

        $result = $this->api_request( 'deactivate', array(
            'license' => $license_key,
            'domain'  => $this->get_domain(),
        ) );

        // Always clear local data
        delete_option( $this->license_option );
        delete_transient( $this->cache_key );

        return $result;
    }

    /**
     * Get stored license key
     */
    public function get_key() {
        return get_option( $this->license_option, '' );
    }

    /**
     * Get license status
     */
    public function get_status() {
        $key = $this->get_key();

        if ( empty( $key ) ) {
            return 'inactive';
        }

        $data = $this->validate( $key );
        return $data['status'] ?? 'error';
    }

    /**
     * Check if license is valid
     */
    public function is_valid() {
        return $this->get_status() === 'valid';
    }

    /**
     * Get license data
     */
    public function get_data() {
        $key = $this->get_key();

        if ( empty( $key ) ) {
            return null;
        }

        return $this->validate( $key );
    }

    /**
     * Clear cache
     */
    public function clear_cache() {
        delete_transient( $this->cache_key );
    }

    /**
     * Check if feature is enabled
     */
    public function has_feature( $feature ) {
        $data = $this->get_data();
        return ! empty( $data['features'][ $feature ] );
    }

    /**
     * Check if widget is enabled
     */
    public function has_widget( $widget ) {
        $data = $this->get_data();
        return ! empty( $data['widgets'][ $widget ] );
    }

    /**
     * Get layout setting
     */
    public function get_layout( $component ) {
        $data = $this->get_data();
        return $data['layouts'][ $component ] ?? 'layout-1';
    }

    /**
     * Check capability
     */
    public function has_capability( $capability ) {
        $data = $this->get_data();
        return ! empty( $data['capabilities'][ $capability ] );
    }
}

/**
 * Get license client instance
 */
function skydonate_license() {
    static $instance = null;
    if ( $instance === null ) {
        $instance = new SkyDonate_License_Client();
    }
    return $instance;
}

// Legacy function name for compatibility
function skydonate_license_client() {
    return skydonate_license();
}

/**
 * Helper functions
 */
function skydonate_is_feature_enabled( $feature ) {
    return skydonate_license()->has_feature( $feature );
}

function skydonate_is_widget_enabled( $widget ) {
    return skydonate_license()->has_widget( $widget );
}

function skydonate_get_layout( $component ) {
    return skydonate_license()->get_layout( $component );
}

function skydonate_has_capability( $capability ) {
    return skydonate_license()->has_capability( $capability );
}