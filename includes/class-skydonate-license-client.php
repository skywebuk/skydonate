<?php
/**
 * SkyDonate License Client
 *
 * Handles license validation, activation, updates, and feature checks
 *
 * @package SkyDonate
 * @version 2.0.25
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_License_Client {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * License server URL
     */
    private $server_url = 'https://skydonate.com';

    /**
     * Option name for license key
     */
    private $license_option = 'skydonate_license_key';

    /**
     * Option name for license data backup
     */
    private $license_data_option = 'skydonate_license_data_backup';

    /**
     * Transient name for cached data
     */
    private $cache_key = 'skydonate_license_data';

    /**
     * Transient for rate limit tracking
     */
    private $rate_limit_key = 'skydonate_license_rate_limit';

    /**
     * Cache duration (6 hours)
     */
    private $cache_duration = 21600;

    /**
     * Offline grace period (7 days)
     */
    private $grace_period = 604800;

    /**
     * Max retry attempts
     */
    private $max_retries = 3;

    /**
     * Plugin version (for update checks)
     */
    private $plugin_version = '1.0.0';

    /**
     * Plugin slug
     */
    private $plugin_slug = 'skydonate';

    /**
     * Debug mode
     */
    private $debug = false;

    /**
     * Last error
     */
    private $last_error = null;

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
        // Set debug mode based on WP_DEBUG
        $this->debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
        
        // Set plugin version if defined
        if ( defined( 'SKYDONATE_VERSION' ) ) {
            $this->plugin_version = SKYDONATE_VERSION;
        }

        // Allow filtering server URL
        $this->server_url = apply_filters( 'skydonate_license_server_url', $this->server_url );

        // Initialize hooks
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
        // Schedule automatic license check (every 6 hours)
        add_action( 'init', array( $this, 'schedule_license_check' ) );
        add_action( 'skydonate_license_auto_check', array( $this, 'daily_license_check' ) );
        // Keep old hook for backwards compatibility
        add_action( 'skydonate_daily_license_check', array( $this, 'daily_license_check' ) );

        // Admin notices
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Clear cache on plugin update
        add_action( 'upgrader_process_complete', array( $this, 'on_plugin_update' ), 10, 2 );

        // Site health integration
        add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );

        // Handle deactivation cleanup
        register_deactivation_hook( SKYDONATE_FILE ?? __FILE__, array( $this, 'on_deactivation' ) );
    }

    /**
     * Schedule automatic license check (every 6 hours)
     */
    public function schedule_license_check() {
        // Register custom cron interval
        add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );

        // Clear old daily schedule if exists
        if ( wp_next_scheduled( 'skydonate_daily_license_check' ) ) {
            wp_clear_scheduled_hook( 'skydonate_daily_license_check' );
        }

        // Schedule new 6-hour check
        if ( ! wp_next_scheduled( 'skydonate_license_auto_check' ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'skydonate_six_hours', 'skydonate_license_auto_check' );
        }
    }

    /**
     * Add custom cron interval
     */
    public function add_cron_interval( $schedules ) {
        if ( ! isset( $schedules['skydonate_six_hours'] ) ) {
            $schedules['skydonate_six_hours'] = array(
                'interval' => 6 * HOUR_IN_SECONDS,
                'display'  => __( 'Every 6 Hours', 'skydonate' ),
            );
        }
        return $schedules;
    }

    /**
     * Auto license check callback - runs in background every 6 hours
     * Refreshes all license system data: license, updates, and remote functions
     */
    public function daily_license_check() {
        if ( ! $this->get_key() ) {
            return;
        }

        $this->log( 'Auto license check: Starting full refresh...' );

        // 1. Clear and refresh license data
        $this->clear_cache();
        $result = $this->validate( null, true );

        if ( $result && ! empty( $result['success'] ) ) {
            $this->log( 'Auto license check: License valid' );

            // 2. Refresh update/version info (use force_check to properly cache)
            if ( function_exists( 'skydonate_updater' ) ) {
                skydonate_updater()->force_check();
                $this->log( 'Auto license check: Update info refreshed' );
            }

            // 3. Refresh remote functions if enabled
            if ( function_exists( 'skydonate_remote_functions' ) && ! empty( $result['capabilities']['allow_remote_functions'] ) ) {
                skydonate_remote_functions()->force_refresh();
                $this->log( 'Auto license check: Remote functions refreshed' );
            }

            $this->log( 'Auto license check: Full refresh completed' );
        } else {
            $this->log( 'Auto license check: Validation failed - ' . ( $result['message'] ?? 'Unknown error' ) );
        }
    }

    /**
     * Plugin deactivation cleanup
     */
    public function on_deactivation() {
        wp_clear_scheduled_hook( 'skydonate_daily_license_check' );
        wp_clear_scheduled_hook( 'skydonate_license_auto_check' );
    }

    /**
     * Clear cache on plugin update
     */
    public function on_plugin_update( $upgrader, $options ) {
        if ( 
            isset( $options['action'] ) && $options['action'] === 'update' &&
            isset( $options['type'] ) && $options['type'] === 'plugin' &&
            isset( $options['plugins'] ) && is_array( $options['plugins'] )
        ) {
            foreach ( $options['plugins'] as $plugin ) {
                if ( strpos( $plugin, 'skydonate' ) !== false ) {
                    $this->clear_cache();
                    break;
                }
            }
        }
    }

    /**
     * Make API request to license server with retry logic
     */
    private function api_request( $endpoint, $data = [], $retry = 0 ) {
        // Check rate limiting (enhanced security)
        if ( function_exists( 'skydonate_check_rate_limit' ) ) {
            if ( ! skydonate_check_rate_limit( 'license_api', 20, 60 ) ) {
                return [
                    'success' => false,
                    'status'  => 'rate_limited',
                    'message' => __( 'Too many requests. Please try again later.', 'skydonate' )
                ];
            }
        } elseif ( $this->is_rate_limited() ) {
            return [
                'success' => false,
                'status'  => 'rate_limited',
                'message' => __( 'Too many requests. Please try again later.', 'skydonate' )
            ];
        }

        // Security: Detect debugging/inspection attempts
        if ( function_exists( 'skydonate_security' ) && skydonate_security()->detect_inspection() ) {
            $this->log( 'Security: Inspection attempt detected' );
            // Continue but log it
        }

        $url = trailingslashit( $this->server_url ) . '?sky_license_' . sanitize_key( $endpoint ) . '=1';

        // Build secure headers
        $timestamp = time();
        $nonce = wp_generate_password( 32, false );

        $headers = [
            'Content-Type'        => 'application/json; charset=utf-8',
            'Accept'              => 'application/json',
            'User-Agent'          => 'SkyDonate/' . $this->plugin_version . ' WordPress/' . get_bloginfo( 'version' ),
            'X-Site-URL'          => home_url(),
            'X-Client-IP'         => $this->get_client_ip(),
            'X-Request-Timestamp' => $timestamp,
            'X-Request-Nonce'     => $nonce,
        ];

        // Add HMAC signature if security class available
        if ( function_exists( 'skydonate_security' ) && ! empty( $data['license'] ) ) {
            $sign_data = array_merge( $data, [
                'timestamp' => $timestamp,
                'nonce'     => $nonce,
            ] );
            $signature = skydonate_security()->sign_request( $sign_data, $data['license'], $timestamp );
            $headers['X-Request-Signature'] = $signature;
        }

        $args = [
            'method'      => 'POST',
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'sslverify'   => true,
            'headers'     => $headers,
            'body'        => wp_json_encode( $data ),
            'data_format' => 'body',
        ];

        // Allow filtering request args
        $args = apply_filters( 'skydonate_license_request_args', $args, $endpoint, $data );

        $this->log( "API Request to {$endpoint}" );

        $response = wp_remote_post( $url, $args );

        // Handle network errors with retry
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $this->log( "API Request Error (attempt {$retry}): {$error_message}" );
            $this->last_error = $error_message;

            // Retry on network errors
            if ( $retry < $this->max_retries ) {
                sleep( pow( 2, $retry ) ); // Exponential backoff
                return $this->api_request( $endpoint, $data, $retry + 1 );
            }

            // Return safe error message
            $safe_message = function_exists( 'skydonate_security' )
                ? skydonate_security()->safe_error_message( $error_message, 'connection_error' )
                : __( 'Unable to connect to license server.', 'skydonate' );

            return [
                'success' => false,
                'status'  => 'connection_error',
                'message' => $safe_message
            ];
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        $this->log( "API Response [{$status_code}]" );

        // Handle HTTP errors
        if ( $status_code >= 400 ) {
            // Rate limiting
            if ( $status_code === 429 ) {
                $this->set_rate_limited();
                return [
                    'success' => false,
                    'status'  => 'rate_limited',
                    'message' => __( 'Too many requests. Please try again later.', 'skydonate' )
                ];
            }

            // Server errors - retry
            if ( $status_code >= 500 && $retry < $this->max_retries ) {
                sleep( pow( 2, $retry ) );
                return $this->api_request( $endpoint, $data, $retry + 1 );
            }

            return [
                'success' => false,
                'status'  => 'server_error',
                'message' => __( 'Server error. Please try again later.', 'skydonate' )
            ];
        }

        // Parse JSON response
        $json = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->log( 'JSON Parse Error: ' . json_last_error_msg() );
            $this->last_error = 'Invalid JSON response';

            return [
                'success' => false,
                'status'  => 'parse_error',
                'message' => __( 'Invalid response from license server.', 'skydonate' )
            ];
        }

        // Fire action for successful API response
        do_action( 'skydonate_license_api_response', $json, $endpoint, $data );

        return $json;
    }

    /**
     * Log debug messages
     */
    private function log( $message ) {
        if ( $this->debug ) {
            error_log( '[SkyDonate License] ' . $message );
        }
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = [ 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
        
        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
                $ip = explode( ',', $ip )[0];
                if ( filter_var( trim( $ip ), FILTER_VALIDATE_IP ) ) {
                    return trim( $ip );
                }
            }
        }
        
        return '127.0.0.1';
    }

    /**
     * Check if rate limited
     */
    private function is_rate_limited() {
        return (bool) get_transient( $this->rate_limit_key );
    }

    /**
     * Set rate limited state
     */
    private function set_rate_limited() {
        set_transient( $this->rate_limit_key, true, MINUTE_IN_SECONDS * 5 );
    }

    /**
     * Get current domain (normalized)
     */
    public function get_domain() {
        $domain = wp_parse_url( home_url(), PHP_URL_HOST );
        // Remove www prefix for consistency
        $domain = preg_replace( '/^www\./i', '', $domain );
        return strtolower( $domain );
    }

    /**
     * Validate license with server
     */
    public function validate( $license_key = null, $force = false ) {
        if ( $license_key === null ) {
            $license_key = $this->get_key();
        }

        if ( empty( $license_key ) ) {
            return [
                'success' => false,
                'status'  => 'inactive',
                'message' => __( 'No license key provided.', 'skydonate' ),
            ];
        }

        // Normalize key
        $license_key = $this->normalize_key( $license_key );

        // Check cache first (unless force refresh)
        if ( ! $force ) {
            $cached = $this->get_cached_data();
            if ( $cached !== null && isset( $cached['license_key'] ) && $cached['license_key'] === $license_key ) {
                return $cached;
            }
        }

        // Make API request
        $result = $this->api_request( 'validate', [
            'license' => $license_key,
            'domain'  => $this->get_domain(),
        ] );

        // Handle successful response
        if ( ! empty( $result['success'] ) ) {
            $result['license_key'] = $license_key;
            $result['cached_at'] = time();
            $result['domain'] = $this->get_domain();
            
            // Cache the result
            set_transient( $this->cache_key, $result, $this->cache_duration );
            
            // Also save as backup for offline grace period
            update_option( $this->license_data_option, $result, false );

            do_action( 'skydonate_license_validated', $result );
            
            return $result;
        }

        // On failure, check if we have valid backup data within grace period
        if ( ! $force ) {
            $backup = $this->get_backup_data();
            if ( $backup && $this->is_within_grace_period( $backup ) ) {
                $backup['from_backup'] = true;
                $backup['backup_reason'] = $result['message'] ?? 'Connection failed';
                return $backup;
            }
        }

        do_action( 'skydonate_license_validation_failed', $result );

        return $result;
    }

    /**
     * Normalize license key
     */
    private function normalize_key( $key ) {
        return strtoupper( trim( sanitize_text_field( $key ) ) );
    }

    /**
     * Get cached license data
     */
    private function get_cached_data() {
        $cached = get_transient( $this->cache_key );
        return $cached !== false ? $cached : null;
    }

    /**
     * Get backup license data
     */
    private function get_backup_data() {
        return get_option( $this->license_data_option, null );
    }

    /**
     * Check if backup data is within grace period
     */
    private function is_within_grace_period( $data ) {
        if ( empty( $data['cached_at'] ) ) {
            return false;
        }
        return ( time() - $data['cached_at'] ) < $this->grace_period;
    }

    /**
     * Activate license (validates and saves the key)
     */
    public function activate( $license_key ) {
        if ( empty( $license_key ) ) {
            return [
                'success' => false,
                'status'  => 'error',
                'message' => __( 'License key is required.', 'skydonate' ),
            ];
        }

        // Normalize
        $license_key = $this->normalize_key( $license_key );

        // Validate format
        if ( ! $this->is_valid_format( $license_key ) ) {
            return [
                'success' => false,
                'status'  => 'invalid_format',
                'message' => __( 'Invalid license key format. Expected: SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX', 'skydonate' ),
            ];
        }

        // Clear existing cache
        $this->clear_cache();

        // Validate with server
        $result = $this->api_request( 'validate', [
            'license' => $license_key,
            'domain'  => $this->get_domain(),
        ] );

        // Save on success
        if ( ! empty( $result['success'] ) ) {
            update_option( $this->license_option, $license_key, false );
            
            $result['license_key'] = $license_key;
            $result['cached_at'] = time();
            $result['domain'] = $this->get_domain();
            
            set_transient( $this->cache_key, $result, $this->cache_duration );
            update_option( $this->license_data_option, $result, false );

            do_action( 'skydonate_license_activated', $license_key, $result );
        }

        return $result;
    }

    /**
     * Validate license key format
     */
    public function is_valid_format( $license_key ) {
        return (bool) preg_match( '/^SKY-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}$/', $license_key );
    }

    /**
     * Deactivate license (clears local data)
     */
    public function deactivate() {
        $license_key = $this->get_key();

        if ( empty( $license_key ) ) {
            return [
                'success' => false,
                'status'  => 'error',
                'message' => __( 'No license to deactivate.', 'skydonate' ),
            ];
        }

        // Clear all license data
        delete_option( $this->license_option );
        delete_option( $this->license_data_option );
        delete_transient( $this->cache_key );
        delete_transient( $this->rate_limit_key );

        do_action( 'skydonate_license_deactivated', $license_key );

        return [
            'success' => true,
            'status'  => 'deactivated',
            'message' => __( 'License deactivated successfully.', 'skydonate' ),
        ];
    }

    /**
     * Check for plugin updates
     */
    public function check_update() {
        $license_key = $this->get_key();

        if ( empty( $license_key ) ) {
            return [
                'success' => false,
                'status'  => 'inactive',
                'message' => __( 'No license key.', 'skydonate' ),
            ];
        }

        $result = $this->api_request( 'update', [
            'license' => $license_key,
            'domain'  => $this->get_domain(),
            'version' => $this->plugin_version,
        ] );

        if ( ! empty( $result['success'] ) && ! empty( $result['update_available'] ) ) {
            do_action( 'skydonate_update_available', $result );
        }

        return $result;
    }

    /**
     * Get download URL for updates
     */
    public function get_download_url() {
        $license_key = $this->get_key();

        if ( empty( $license_key ) ) {
            return false;
        }

        return add_query_arg( [
            'sky_license_download' => '1',
            'license' => rawurlencode( $license_key ),
            'domain'  => rawurlencode( $this->get_domain() ),
        ], $this->server_url );
    }

    /**
     * Get stored license key
     */
    public function get_key() {
        return get_option( $this->license_option, '' );
    }

    /**
     * Get masked license key for display
     */
    public function get_masked_key() {
        $key = $this->get_key();
        if ( empty( $key ) ) {
            return '';
        }
        return substr( $key, 0, 12 ) . str_repeat( '*', 20 ) . substr( $key, -4 );
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
        $status = $this->get_status();
        return $status === 'valid';
    }

    /**
     * Check if license is active (valid or in grace period)
     */
    public function is_active() {
        $data = $this->get_data();
        return ! empty( $data['success'] ) || ! empty( $data['from_backup'] );
    }

    /**
     * Get license data (cached)
     */
    public function get_data() {
        $key = $this->get_key();

        if ( empty( $key ) ) {
            return null;
        }

        return $this->validate( $key );
    }

    /**
     * Get fresh license data (bypass cache)
     */
    public function refresh() {
        $key = $this->get_key();

        if ( empty( $key ) ) {
            return null;
        }

        return $this->validate( $key, true );
    }

    /**
     * Refresh all license system data
     * Clears and refreshes: license data, update info, remote functions
     *
     * @return array Result with success status and refreshed data
     */
    public function refresh_all() {
        $key = $this->get_key();

        if ( empty( $key ) ) {
            return [
                'success' => false,
                'message' => __( 'No license key found', 'skydonate' ),
            ];
        }

        $this->log( 'Full refresh: Starting...' );

        // 1. Clear and refresh license data
        $this->clear_cache();
        $result = $this->validate( $key, true );

        if ( empty( $result['success'] ) ) {
            return $result;
        }

        // 2. Clear and refresh update/version info (use force_check to properly cache)
        $update_info = null;
        if ( function_exists( 'skydonate_updater' ) ) {
            $update_info = skydonate_updater()->force_check();
            $this->log( 'Full refresh: Update info refreshed' );
        }

        // 3. Refresh remote functions if enabled
        $remote_refreshed = false;
        if ( function_exists( 'skydonate_remote_functions' ) && ! empty( $result['capabilities']['allow_remote_functions'] ) ) {
            $remote_refreshed = skydonate_remote_functions()->force_refresh();
            $this->log( 'Full refresh: Remote functions refreshed' );
        }

        $this->log( 'Full refresh: Completed successfully' );

        // Return combined result
        return [
            'success'          => true,
            'message'          => __( 'All license data refreshed successfully', 'skydonate' ),
            'license'          => $result,
            'update_info'      => $update_info,
            'remote_refreshed' => $remote_refreshed,
        ];
    }

    /**
     * Clear cache
     */
    public function clear_cache() {
        delete_transient( $this->cache_key );
        delete_transient( $this->rate_limit_key );
    }

    /**
     * Get last error message
     */
    public function get_last_error() {
        return $this->last_error;
    }

    // ===========================================
    // Feature / Widget / Layout / Capability Checks
    // ===========================================

    /**
     * Check if feature is enabled
     */
    public function has_feature( $feature ) {
        if ( ! $this->is_active() ) {
            return apply_filters( "skydonate_default_feature_{$feature}", false );
        }

        $data = $this->get_data();
        $enabled = ! empty( $data['features'][ $feature ] );
        
        return apply_filters( "skydonate_has_feature_{$feature}", $enabled, $data );
    }

    /**
     * Check if widget is enabled
     */
    public function has_widget( $widget ) {
        if ( ! $this->is_active() ) {
            return apply_filters( "skydonate_default_widget_{$widget}", false );
        }

        $data = $this->get_data();
        $enabled = ! empty( $data['widgets'][ $widget ] );
        
        return apply_filters( "skydonate_has_widget_{$widget}", $enabled, $data );
    }

    /**
     * Get layout setting
     */
    public function get_layout( $component ) {
        $default = apply_filters( "skydonate_default_layout_{$component}", 'layout-1' );
        
        if ( ! $this->is_active() ) {
            return $default;
        }

        $data = $this->get_data();
        $layout = $data['layouts'][ $component ] ?? $default;
        
        return apply_filters( "skydonate_layout_{$component}", $layout, $data );
    }

    /**
     * Check capability
     */
    public function has_capability( $capability ) {
        if ( ! $this->is_active() ) {
            return apply_filters( "skydonate_default_capability_{$capability}", false );
        }

        $data = $this->get_data();
        $enabled = ! empty( $data['capabilities'][ $capability ] );
        
        return apply_filters( "skydonate_has_capability_{$capability}", $enabled, $data );
    }

    /**
     * Get all features
     */
    public function get_features() {
        $data = $this->get_data();
        return apply_filters( 'skydonate_features', $data['features'] ?? [], $data );
    }

    /**
     * Get all widgets
     */
    public function get_widgets() {
        $data = $this->get_data();
        return apply_filters( 'skydonate_widgets', $data['widgets'] ?? [], $data );
    }

    /**
     * Get all layouts
     */
    public function get_layouts() {
        $data = $this->get_data();
        return apply_filters( 'skydonate_layouts', $data['layouts'] ?? [], $data );
    }

    /**
     * Get all capabilities
     */
    public function get_capabilities() {
        $data = $this->get_data();
        return apply_filters( 'skydonate_capabilities', $data['capabilities'] ?? [], $data );
    }

    /**
     * Get remote functions URL
     */
    public function get_remote_functions_url() {
        $data = $this->get_data();
        return $data['remote_functions_url'] ?? null;
    }


    /**
     * Get license key
     */
    public function get_license_key() {
        $data = $this->get_data();
        return $data['license_key'] ?? null;
    }

    /**
     * Get remote config URL
     */
    public function get_remote_config_url() {
        $data = $this->get_data();
        return $data['remote_config_url'] ?? null;
    }

    /**
     * Get license tier (basic, pro, agency, enterprise, lifetime)
     */
    public function get_tier() {
        $data = $this->get_data();
        return $data['tier'] ?? null;
    }

    /**
     * Get plugin info from server
     */
    public function get_plugin_info() {
        $data = $this->get_data();
        return $data['plugin_info'] ?? array();
    }

    /**
     * Check if license has specific tier
     */
    public function is_tier( $tier ) {
        return strtolower( $this->get_tier() ?? '' ) === strtolower( $tier );
    }

    /**
     * Check if tier is at least a specific level
     * Order: basic < pro < agency < enterprise < lifetime
     */
    public function is_tier_at_least( $minimum_tier ) {
        $tier_order = array(
            'basic'      => 1,
            'pro'        => 2,
            'agency'     => 3,
            'enterprise' => 4,
            'lifetime'   => 5,
        );

        $current_tier = strtolower( $this->get_tier() ?? 'basic' );
        $minimum_tier = strtolower( $minimum_tier );

        $current_level = $tier_order[ $current_tier ] ?? 0;
        $minimum_level = $tier_order[ $minimum_tier ] ?? 0;

        return $current_level >= $minimum_level;
    }

    // ===========================================
    // Expiration Helpers
    // ===========================================

    /**
     * Get expiration date
     */
    public function get_expiration() {
        $data = $this->get_data();
        return $data['expires'] ?? null;
    }

    /**
     * Get expiration timestamp
     */
    public function get_expiration_timestamp() {
        $expires = $this->get_expiration();
        return $expires ? strtotime( $expires ) : null;
    }

    /**
     * Get days until expiration
     */
    public function get_days_until_expiration() {
        $timestamp = $this->get_expiration_timestamp();
        
        if ( ! $timestamp ) {
            return null; // Lifetime license
        }
        
        $days = ceil( ( $timestamp - time() ) / DAY_IN_SECONDS );
        return max( 0, $days );
    }

    /**
     * Check if license is lifetime
     */
    public function is_lifetime() {
        return $this->get_expiration() === null;
    }

    /**
     * Check if license is expiring soon
     */
    public function is_expiring_soon( $days = 30 ) {
        $days_left = $this->get_days_until_expiration();
        
        if ( $days_left === null ) {
            return false; // Lifetime license
        }
        
        return $days_left <= $days && $days_left > 0;
    }

    /**
     * Check if license is expired
     */
    public function is_expired() {
        $timestamp = $this->get_expiration_timestamp();
        
        if ( ! $timestamp ) {
            return false; // Lifetime license
        }
        
        return $timestamp < time();
    }

    // ===========================================
    // Admin Features
    // ===========================================

    /**
     * Display admin notices
     */
    public function admin_notices() {
        // Only show to admins on relevant pages
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'skydonate' ) === false ) {
            return;
        }

        // Show expiring soon notice
        if ( $this->is_valid() && $this->is_expiring_soon() ) {
            $days = $this->get_days_until_expiration();
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong><?php esc_html_e( 'SkyDonate License:', 'skydonate' ); ?></strong>
                    <?php 
                    printf(
                        /* translators: %d: number of days */
                        esc_html__( 'Your license expires in %d days. Please renew to continue receiving updates and support.', 'skydonate' ),
                        $days
                    ); 
                    ?>
                    <a href="https://skydonate.com/renew" target="_blank" rel="noopener">
                        <?php esc_html_e( 'Renew Now', 'skydonate' ); ?>
                    </a>
                </p>
            </div>
            <?php
        }

        // Show grace period notice
        $data = $this->get_data();
        if ( ! empty( $data['from_backup'] ) ) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e( 'SkyDonate License:', 'skydonate' ); ?></strong>
                    <?php esc_html_e( 'Unable to verify license with server. Using cached data. Please check your internet connection.', 'skydonate' ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Add site health test
     */
    public function add_site_health_test( $tests ) {
        $tests['direct']['skydonate_license'] = [
            'label' => __( 'SkyDonate License Status', 'skydonate' ),
            'test'  => array( $this, 'site_health_test' ),
        ];
        return $tests;
    }

    /**
     * Site health test callback
     */
    public function site_health_test() {
        $status = $this->get_status();

        $result = [
            'label'       => __( 'SkyDonate license is active', 'skydonate' ),
            'status'      => 'good',
            'badge'       => [
                'label' => __( 'SkyDonate', 'skydonate' ),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __( 'Your SkyDonate license is active and all features are available.', 'skydonate' )
            ),
            'test'        => 'skydonate_license',
        ];

        if ( $status !== 'valid' ) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'SkyDonate license is not active', 'skydonate' );
            $result['description'] = sprintf(
                '<p>%s</p>',
                __( 'Your SkyDonate license is not active. Some features may be limited.', 'skydonate' )
            );
            $result['actions'] = sprintf(
                '<a href="%s">%s</a>',
                admin_url( 'admin.php?page=skydonate-license' ),
                __( 'Activate License', 'skydonate' )
            );
        } elseif ( $this->is_expiring_soon() ) {
            $result['status'] = 'recommended';
            $result['label'] = __( 'SkyDonate license expiring soon', 'skydonate' );
            $result['description'] = sprintf(
                '<p>%s</p>',
                sprintf(
                    /* translators: %d: number of days */
                    __( 'Your SkyDonate license expires in %d days.', 'skydonate' ),
                    $this->get_days_until_expiration()
                )
            );
        }

        return $result;
    }

    // ===========================================
    // Localhost Detection
    // ===========================================

    /**
     * Check if current domain is localhost/development
     */
    public function is_localhost() {
        $domain = $this->get_domain();

        $localhost_patterns = [
            'localhost',
            '127.0.0.1',
            '::1',
            '.local',
            '.test',
            '.dev',
            '.localhost',
            'dev.',
            'local.',
            'staging.',
        ];

        foreach ( $localhost_patterns as $pattern ) {
            if ( $domain === $pattern ||
                 strpos( $domain, $pattern ) !== false ||
                 fnmatch( '*' . $pattern, $domain ) ) {
                return true;
            }
        }

        // Check for private IP ranges
        $ip = gethostbyname( $domain );
        if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
            return true;
        }

        return false;
    }

    // ===========================================
    // Specific Feature Getters (Match Server Schema)
    // Server returns features without 'feature_' prefix
    // ===========================================

    /**
     * Check if Sky donations module is enabled
     */
    public function has_sky_donations_module() {
        return $this->has_feature( 'sky_donations_module' );
    }

    /**
     * Check if custom login form is enabled
     */
    public function has_custom_login_form() {
        return $this->has_feature( 'custom_login_form' );
    }

    /**
     * Check if checkout custom field style is enabled
     */
    public function has_checkout_custom_field_style() {
        return $this->has_feature( 'checkout_custom_field_style' );
    }

    /**
     * Check if enhanced gift aid is enabled
     */
    public function has_enhanced_gift_aid() {
        return $this->has_feature( 'enhanced_gift_aid' );
    }

    /**
     * Check if recent donation country is enabled
     */
    public function has_recent_donation_country() {
        return $this->has_feature( 'recent_donation_country' );
    }

    /**
     * Check if auto complete processing is enabled
     */
    public function has_auto_complete_processing() {
        return $this->has_feature( 'auto_complete_processing' );
    }

    /**
     * Check if donation goal is enabled
     */
    public function has_donation_goal() {
        return $this->has_feature( 'donation_goal' );
    }

    /**
     * Check if title prefix is enabled
     */
    public function has_title_prefix() {
        return $this->has_feature( 'title_prefix' );
    }

    /**
     * Check if donation fees is enabled
     */
    public function has_donation_fees() {
        return $this->has_feature( 'donation_fees' );
    }

    /**
     * Check if notification is enabled
     */
    public function has_notification() {
        return $this->has_feature( 'notification' );
    }

    /**
     * Check if address autocomplete is enabled
     */
    public function has_address_autocomplete() {
        return $this->has_feature( 'address_autocomplete' );
    }

    /**
     * Check if currency changer is enabled
     */
    public function has_currency_changer() {
        return $this->has_feature( 'currency_changer' );
    }

    // Legacy aliases for backward compatibility
    public function has_donations_module() {
        return $this->has_sky_donations_module();
    }

    public function has_gift_aid() {
        return $this->has_enhanced_gift_aid();
    }

    public function has_notifications() {
        return $this->has_notification();
    }

    public function has_goal_tracking() {
        return $this->has_donation_goal();
    }

    // ===========================================
    // Specific Capability Getters (Match Server Schema)
    // ===========================================

    /**
     * Check if auto updates are allowed
     */
    public function can_auto_update() {
        return $this->has_capability( 'allow_auto_updates' );
    }

    /**
     * Check if remote functions are allowed
     */
    public function can_remote_functions() {
        return $this->has_capability( 'allow_remote_functions' );
    }

    /**
     * Check if localhost is allowed
     */
    public function can_localhost() {
        return $this->has_capability( 'allow_localhost' );
    }

    /**
     * Check if beta access is allowed
     */
    public function can_beta_access() {
        return $this->has_capability( 'allow_beta' );
    }

    /**
     * Check if update notifications are disabled
     */
    public function is_update_notifications_disabled() {
        return $this->has_capability( 'disable_update_notifications' );
    }

    /**
     * Check if usage tracking is disabled
     */
    public function is_usage_tracking_disabled() {
        return $this->has_capability( 'disable_usage_tracking' );
    }

    // ===========================================
    // Specific Widget Getters (Match Server Schema)
    // Server returns widgets without 'widget_' prefix
    // ===========================================

    /**
     * Check if zakat calculator classic widget is enabled
     */
    public function has_widget_zakat_calculator_classic() {
        return $this->has_widget( 'zakat_calculator_classic' );
    }

    /**
     * Check if metal values widget is enabled
     */
    public function has_widget_metal_values() {
        return $this->has_widget( 'metal_values' );
    }

    /**
     * Check if recent donation widget is enabled
     */
    public function has_widget_recent_donation() {
        return $this->has_widget( 'recent_donation' );
    }

    /**
     * Check if donation progress widget is enabled
     */
    public function has_widget_donation_progress() {
        return $this->has_widget( 'donation_progress' );
    }

    /**
     * Check if donation form widget is enabled
     */
    public function has_widget_donation_form() {
        return $this->has_widget( 'donation_form' );
    }

    /**
     * Check if donation card widget is enabled
     */
    public function has_widget_donation_card() {
        return $this->has_widget( 'donation_card' );
    }

    /**
     * Check if impact slider widget is enabled
     */
    public function has_widget_impact_slider() {
        return $this->has_widget( 'impact_slider' );
    }

    /**
     * Check if qurbani status widget is enabled
     */
    public function has_widget_qurbani_status() {
        return $this->has_widget( 'qurbani_status' );
    }

    /**
     * Check if extra donation widget is enabled
     */
    public function has_widget_extra_donation() {
        return $this->has_widget( 'extra_donation' );
    }

    /**
     * Check if quick donation widget is enabled
     */
    public function has_widget_quick_donation() {
        return $this->has_widget( 'quick_donation' );
    }

    /**
     * Check if gift aid toggle widget is enabled
     */
    public function has_widget_gift_aid_toggle() {
        return $this->has_widget( 'gift_aid_toggle' );
    }

    /**
     * Check if donation button widget is enabled
     */
    public function has_widget_donation_button() {
        return $this->has_widget( 'donation_button' );
    }

    /**
     * Check if icon slider widget is enabled
     */
    public function has_widget_icon_slider() {
        return $this->has_widget( 'icon_slider' );
    }

    // ===========================================
    // Specific Layout Getters (Match Server Schema)
    // Server returns layouts without 'layout_' prefix
    // ===========================================

    /**
     * Get recent donation layout
     */
    public function get_recent_donation_layout() {
        return $this->get_layout( 'recent_donation' );
    }

    /**
     * Get progress bar layout
     */
    public function get_progress_bar_layout() {
        return $this->get_layout( 'progress_bar' );
    }

    /**
     * Get addons card layout
     */
    public function get_addons_card_layout() {
        return $this->get_layout( 'addons_card' );
    }

    /**
     * Get addons donation form layout
     */
    public function get_addons_donation_form_layout() {
        return $this->get_layout( 'addons_donation_form' );
    }

    // ===========================================
    // Status Info for Admin
    // ===========================================

    /**
     * Get comprehensive license status info for admin display
     */
    public function get_status_info() {
        $data = $this->get_data();
        $key = $this->get_key();

        $info = [
            'has_key'          => ! empty( $key ),
            'key_masked'       => $this->get_masked_key(),
            'status'           => $data['status'] ?? 'inactive',
            'tier'             => $data['tier'] ?? null,
            'is_valid'         => $this->is_valid(),
            'is_active'        => $this->is_active(),
            'is_localhost'     => $this->is_localhost(),
            'domain'           => $this->get_domain(),
            'server_url'       => $this->server_url,
            'from_backup'      => ! empty( $data['from_backup'] ),
            'backup_reason'    => $data['backup_reason'] ?? null,
        ];

        // Expiration info
        $info['expires'] = $this->get_expiration();
        $info['is_lifetime'] = $this->is_lifetime();
        $info['days_remaining'] = $this->get_days_until_expiration();
        $info['is_expired'] = $this->is_expired();
        $info['is_expiring_soon'] = $this->is_expiring_soon();

        // Cache info
        $info['cached_at'] = $data['cached_at'] ?? null;
        if ( $info['cached_at'] ) {
            $info['cached_at_formatted'] = gmdate( 'Y-m-d H:i:s', $info['cached_at'] ) . ' UTC';
            $info['cache_age'] = human_time_diff( $info['cached_at'], time() );
        }

        // Next auto check
        $next_check = wp_next_scheduled( 'skydonate_license_auto_check' );
        if ( $next_check ) {
            $info['next_check'] = gmdate( 'Y-m-d H:i:s', $next_check ) . ' UTC';
            $info['next_check_in'] = human_time_diff( time(), $next_check );
        }

        // Feature counts
        $info['features_count'] = count( array_filter( $data['features'] ?? [] ) );
        $info['widgets_count'] = count( array_filter( $data['widgets'] ?? [] ) );
        $info['capabilities_count'] = count( array_filter( $data['capabilities'] ?? [] ) );

        // Server message
        $info['message'] = $data['message'] ?? null;

        return $info;
    }

    /**
     * Get status badge HTML for admin display
     */
    public function get_status_badge() {
        $status = $this->get_status();

        $badges = [
            'valid'             => [ 'label' => __( 'Active', 'skydonate' ), 'class' => 'sky-badge-success' ],
            'expired'           => [ 'label' => __( 'Expired', 'skydonate' ), 'class' => 'sky-badge-danger' ],
            'inactive'          => [ 'label' => __( 'Inactive', 'skydonate' ), 'class' => 'sky-badge-warning' ],
            'disabled'          => [ 'label' => __( 'Disabled', 'skydonate' ), 'class' => 'sky-badge-danger' ],
            'invalid'           => [ 'label' => __( 'Invalid', 'skydonate' ), 'class' => 'sky-badge-danger' ],
            'domain_mismatch'   => [ 'label' => __( 'Domain Mismatch', 'skydonate' ), 'class' => 'sky-badge-danger' ],
            'domain_blocked'    => [ 'label' => __( 'Domain Blocked', 'skydonate' ), 'class' => 'sky-badge-danger' ],
            'localhost_blocked' => [ 'label' => __( 'Localhost Blocked', 'skydonate' ), 'class' => 'sky-badge-warning' ],
        ];

        $badge = $badges[ $status ] ?? [ 'label' => ucfirst( $status ), 'class' => 'sky-badge-secondary' ];

        return sprintf(
            '<span class="sky-badge %s">%s</span>',
            esc_attr( $badge['class'] ),
            esc_html( $badge['label'] )
        );
    }

    /**
     * Get tier badge HTML for admin display
     */
    public function get_tier_badge() {
        $tier = $this->get_tier();

        if ( empty( $tier ) ) {
            return '';
        }

        $badges = [
            'basic'      => [ 'label' => __( 'Basic', 'skydonate' ), 'class' => 'sky-badge-tier-basic' ],
            'pro'        => [ 'label' => __( 'Pro', 'skydonate' ), 'class' => 'sky-badge-tier-pro' ],
            'agency'     => [ 'label' => __( 'Agency', 'skydonate' ), 'class' => 'sky-badge-tier-agency' ],
            'enterprise' => [ 'label' => __( 'Enterprise', 'skydonate' ), 'class' => 'sky-badge-tier-enterprise' ],
            'lifetime'   => [ 'label' => __( 'Lifetime', 'skydonate' ), 'class' => 'sky-badge-tier-lifetime' ],
        ];

        $tier_key = strtolower( $tier );
        $badge = $badges[ $tier_key ] ?? [ 'label' => ucfirst( $tier ), 'class' => 'sky-badge-tier-basic' ];

        return sprintf(
            '<span class="sky-badge %s">%s</span>',
            esc_attr( $badge['class'] ),
            esc_html( $badge['label'] )
        );
    }
}

// ===========================================
// Global Functions
// ===========================================

/**
 * Get license client instance (singleton)
 */
function skydonate_license() {
    return SkyDonate_License_Client::instance();
}

/**
 * Legacy function name for compatibility
 */
function skydonate_license_client() {
    return skydonate_license();
}

/**
 * Helper functions
 */
function skydonate_is_licensed() {
    return skydonate_license()->is_valid();
}

function skydonate_is_active() {
    return skydonate_license()->is_active();
}

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

function skydonate_is_localhost() {
    return skydonate_license()->is_localhost();
}

function skydonate_license_status_info() {
    return skydonate_license()->get_status_info();
}

function skydonate_license_status_badge() {
    return skydonate_license()->get_status_badge();
}

// Specific feature checks (Match Server Schema)
function skydonate_has_sky_donations_module() {
    return skydonate_license()->has_sky_donations_module();
}

function skydonate_has_custom_login_form() {
    return skydonate_license()->has_custom_login_form();
}

function skydonate_has_checkout_custom_field_style() {
    return skydonate_license()->has_checkout_custom_field_style();
}

function skydonate_has_enhanced_gift_aid() {
    return skydonate_license()->has_enhanced_gift_aid();
}

function skydonate_has_recent_donation_country() {
    return skydonate_license()->has_recent_donation_country();
}

function skydonate_has_auto_complete_processing() {
    return skydonate_license()->has_auto_complete_processing();
}

function skydonate_has_donation_goal() {
    return skydonate_license()->has_donation_goal();
}

function skydonate_has_title_prefix() {
    return skydonate_license()->has_title_prefix();
}

function skydonate_has_donation_fees() {
    return skydonate_license()->has_donation_fees();
}

function skydonate_has_notification() {
    return skydonate_license()->has_notification();
}

function skydonate_has_address_autocomplete() {
    return skydonate_license()->has_address_autocomplete();
}

function skydonate_has_currency_changer() {
    return skydonate_license()->has_currency_changer();
}

// Legacy aliases for backward compatibility
function skydonate_has_gift_aid() {
    return skydonate_license()->has_gift_aid();
}

function skydonate_has_notifications() {
    return skydonate_license()->has_notifications();
}

function skydonate_has_goal_tracking() {
    return skydonate_license()->has_goal_tracking();
}

// Capability checks (Match Server Schema)
function skydonate_can_auto_update() {
    return skydonate_license()->can_auto_update();
}

function skydonate_can_remote_functions() {
    return skydonate_license()->can_remote_functions();
}

function skydonate_can_localhost() {
    return skydonate_license()->can_localhost();
}

function skydonate_can_beta_access() {
    return skydonate_license()->can_beta_access();
}

function skydonate_is_update_notifications_disabled() {
    return skydonate_license()->is_update_notifications_disabled();
}

function skydonate_is_usage_tracking_disabled() {
    return skydonate_license()->is_usage_tracking_disabled();
}

function skydonate_get_license_status() {
    return skydonate_license()->get_status();
}

function skydonate_get_license_data() {
    return skydonate_license()->get_data();
}

function skydonate_activate_license( $key ) {
    return skydonate_license()->activate( $key );
}

function skydonate_deactivate_license() {
    return skydonate_license()->deactivate();
}

function skydonate_refresh_license() {
    return skydonate_license()->refresh();
}

function skydonate_refresh_all_license_data() {
    return skydonate_license()->refresh_all();
}

function skydonate_is_license_expiring_soon( $days = 30 ) {
    return skydonate_license()->is_expiring_soon( $days );
}

function skydonate_get_license_expiration() {
    return skydonate_license()->get_expiration();
}

function skydonate_get_remote_functions_url() {
    return skydonate_license()->get_remote_functions_url();
}

function skydonate_get_license_key() {
    return skydonate_license()->get_license_key();
}

// Tier functions (Match Server Schema)
function skydonate_get_license_tier() {
    return skydonate_license()->get_tier();
}

function skydonate_is_tier( $tier ) {
    return skydonate_license()->is_tier( $tier );
}

function skydonate_is_tier_at_least( $minimum_tier ) {
    return skydonate_license()->is_tier_at_least( $minimum_tier );
}

function skydonate_get_tier_badge() {
    return skydonate_license()->get_tier_badge();
}

function skydonate_get_plugin_info() {
    return skydonate_license()->get_plugin_info();
}

