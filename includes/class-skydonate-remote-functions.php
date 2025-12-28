<?php
/**
 * SkyDonate Remote Functions Loader
 *
 * Handles loading and executing remote functions directly from the license server
 * Compatible with Sky License Manager remote functions server
 *
 * @package SkyDonate
 * @version 2.0.25
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_Remote_Functions {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Cache key for remote functions code
     */
    private $code_cache_key = 'skydonate_remote_functions_code';

    /**
     * Cache key for remote functions hash
     */
    private $hash_cache_key = 'skydonate_remote_functions_hash';

    /**
     * Cache key for version info
     */
    private $version_key = 'skydonate_remote_functions_version';

    /**
     * Cache key for tier info
     */
    private $tier_key = 'skydonate_remote_functions_tier';

    /**
     * Cache key for last loaded timestamp
     */
    private $loaded_key = 'skydonate_remote_functions_loaded';

    /**
     * Cache duration (configurable via constant)
     */
    private $cache_duration;

    /**
     * Last load status
     */
    private $last_status = null;

    /**
     * Last error message
     */
    private $last_error = null;

    /**
     * Whether functions have been executed this request
     */
    private $executed = false;

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
        // Allow customizing cache duration via constant (default: 1 day)
        $this->cache_duration = defined( 'SKYDONATE_REMOTE_CACHE_DURATION' )
            ? SKYDONATE_REMOTE_CACHE_DURATION
            : DAY_IN_SECONDS;

        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'load_remote_functions' ), 5 );

        // Schedule automatic updates
        add_action( 'init', array( $this, 'schedule_auto_update' ) );
        add_action( 'skydonate_remote_functions_update', array( $this, 'auto_update_check' ) );

        // Clear scheduled event on plugin deactivation
        register_deactivation_hook( SKYDONATE_FILE ?? __FILE__, array( $this, 'clear_scheduled_event' ) );

        // AJAX handler for manual refresh
        add_action( 'wp_ajax_skydonate_refresh_remote_functions', array( $this, 'ajax_refresh' ) );
    }

    /**
     * Schedule automatic update check
     */
    public function schedule_auto_update() {
        if ( ! wp_next_scheduled( 'skydonate_remote_functions_update' ) ) {
            // Check every 6 hours for updates
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'skydonate_six_hours', 'skydonate_remote_functions_update' );
        }

        // Register custom cron interval
        add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
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
     * Clear scheduled event
     */
    public function clear_scheduled_event() {
        wp_clear_scheduled_hook( 'skydonate_remote_functions_update' );
    }

    /**
     * AJAX handler for manual refresh
     */
    public function ajax_refresh() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied', 'skydonate' ) ) );
        }

        check_ajax_referer( 'skydonate_remote_functions_refresh', 'nonce' );

        $result = $this->force_refresh();

        if ( $result ) {
            wp_send_json_success( array(
                'message' => __( 'Remote functions refreshed successfully', 'skydonate' ),
                'status'  => $this->get_status_info(),
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $this->last_error ?: __( 'Failed to refresh remote functions', 'skydonate' ),
                'status'  => $this->get_status_info(),
            ) );
        }
    }

    /**
     * Auto update check - runs in background via cron
     * Compares version/hash with server before downloading
     */
    public function auto_update_check() {
        $license_data = $this->get_license_data();

        if ( empty( $license_data ) || empty( $license_data['success'] ) ) {
            $this->log( 'Auto-update check: No valid license data' );
            return;
        }

        if ( empty( $license_data['remote_functions_url'] ) ) {
            $this->log( 'Auto-update check: No remote functions URL configured' );
            return;
        }

        // Check capability - server uses 'allow_remote_functions'
        if ( empty( $license_data['capabilities']['allow_remote_functions'] ) ) {
            $this->log( 'Auto-update check: Remote functions not allowed by license' );
            return;
        }

        // Make HEAD request to check version without downloading full content
        $check_url = add_query_arg( 'check_version', '1', $license_data['remote_functions_url'] );

        $response = wp_remote_head( $check_url, array(
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => $this->get_request_headers(),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->log( 'Auto-update check failed: ' . $response->get_error_message() );
            return;
        }

        $status_code = wp_remote_retrieve_response_code( $response );

        // 304 = Not Modified (no update needed)
        if ( $status_code === 304 ) {
            $this->log( 'Auto-update check: No updates available (304)' );
            return;
        }

        // 403 = License invalid or remote functions not allowed
        if ( $status_code === 403 ) {
            $this->log( 'Auto-update check: License validation failed (403)' );
            return;
        }

        // 200 = Update available, fetch new version
        if ( $status_code === 200 ) {
            $remote_hash = wp_remote_retrieve_header( $response, 'X-Functions-Hash' );
            $remote_version = wp_remote_retrieve_header( $response, 'X-Functions-Version' );
            $remote_tier = wp_remote_retrieve_header( $response, 'X-License-Tier' );
            $current_hash = get_transient( $this->hash_cache_key );

            // Compare hashes
            if ( $remote_hash && $current_hash && hash_equals( $current_hash, $remote_hash ) ) {
                $this->log( 'Auto-update check: Hash unchanged, skipping' );
                return;
            }

            // Clear cache and reload
            $this->log( 'Auto-update check: New version detected (v' . $remote_version . ', tier: ' . $remote_tier . '), updating...' );
            $this->clear_cache();
            $this->load_remote_functions();
        }
    }

    /**
     * Get request headers for server communication
     * Headers match Sky License Manager server expectations
     * Includes security headers for signed requests
     *
     * @return array
     */
    private function get_request_headers() {
        $license_key = $this->get_license_key();
        $timestamp = time();
        $nonce = wp_generate_password( 32, false );

        $headers = array(
            'X-LICENSE-KEY'            => $license_key,
            'X-SITE-URL'               => home_url(),
            'X-Plugin-Version'         => defined( 'SKYDONATE_VERSION' ) ? SKYDONATE_VERSION : '1.0.0',
            'X-Current-Hash'           => get_transient( $this->hash_cache_key ) ?: '',
            'X-ALLOW-REMOTE-FUNCTIONS' => '1',
            'X-Request-Timestamp'      => $timestamp,
            'X-Request-Nonce'          => $nonce,
        );

        // Add HMAC signature if security class available
        if ( function_exists( 'skydonate_security' ) && ! empty( $license_key ) ) {
            $sign_data = array(
                'license'   => $license_key,
                'site_url'  => home_url(),
                'timestamp' => $timestamp,
                'nonce'     => $nonce,
            );
            $signature = skydonate_security()->sign_request( $sign_data, $license_key, $timestamp );
            $headers['X-Request-Signature'] = $signature;
        }

        return $headers;
    }

    /**
     * Check if running on localhost
     *
     * @return bool
     */
    private function is_localhost() {
        $host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
        $server_addr = isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : '';

        $localhost_patterns = array(
            'localhost',
            '127.0.0.1',
            '::1',
            '.local',
            '.test',
            '.dev',
        );

        foreach ( $localhost_patterns as $pattern ) {
            if ( stripos( $host, $pattern ) !== false ) {
                return true;
            }
        }

        if ( in_array( $server_addr, array( '127.0.0.1', '::1' ), true ) ) {
            return true;
        }

        return false;
    }

    /**
     * Load remote functions from local file (for localhost development)
     *
     * @return bool Success status
     */
    private function load_from_local_file() {
        // Path: wp-content/remote/remote-functions.php
        $local_path = WP_CONTENT_DIR . '/remote/remote-functions.php';

        if ( ! file_exists( $local_path ) ) {
            $this->log( 'Local remote functions file not found: ' . $local_path );
            $this->last_status = 'local_file_not_found';
            $this->last_error = sprintf( __( 'Local file not found: %s', 'skydonate' ), $local_path );
            return false;
        }

        // Include the file directly
        try {
            require_once $local_path;
            $this->executed = true;
            $this->last_status = 'loaded_from_local';
            $this->last_error = null;
            $this->log( 'Remote functions loaded from local file: ' . $local_path );
            return true;
        } catch ( Throwable $e ) {
            $this->log( 'Error loading local remote functions: ' . $e->getMessage() );
            $this->last_status = 'local_load_error';
            $this->last_error = sprintf( __( 'Error loading local file: %s', 'skydonate' ), $e->getMessage() );
            return false;
        }
    }

    /**
     * Load and execute remote functions directly from server
     * Code is cached in transient and executed via eval()
     */
    public function load_remote_functions() {
        // Prevent multiple executions in same request
        if ( $this->executed ) {
            return;
        }

        // For localhost, load from local file instead
        if ( $this->is_localhost() ) {
            $this->load_from_local_file();
            return;
        }

        $license_data = $this->get_license_data();

        if ( empty( $license_data ) || empty( $license_data['success'] ) ) {
            $this->last_status = 'no_license';
            $this->last_error = __( 'No valid license data available', 'skydonate' );
            return;
        }

        if ( empty( $license_data['remote_functions_url'] ) ) {
            $this->last_status = 'no_url';
            $this->last_error = __( 'Remote functions URL not configured', 'skydonate' );
            return;
        }

        // Check capability - server uses 'allow_remote_functions'
        if ( empty( $license_data['capabilities']['allow_remote_functions'] ) ) {
            $this->last_status = 'not_allowed';
            $this->last_error = __( 'Remote functions not allowed by license', 'skydonate' );
            return;
        }

        // Check if we have valid cached code
        $cached_code = get_transient( $this->code_cache_key );
        $cached_hash = get_transient( $this->hash_cache_key );

        if ( $cached_code !== false && $cached_hash !== false ) {
            // Verify code integrity before executing
            if ( $this->verify_code_integrity( $cached_code, $cached_hash ) ) {
                $this->execute_code( $cached_code );
                $this->last_status = 'loaded_from_cache';
                return;
            }
            // Code integrity failed, clear cache and reload
            $this->log( 'Code integrity check failed, reloading from server...' );
            $this->clear_cache();
        }

        // Fetch remote functions from server
        $response = wp_remote_get( $license_data['remote_functions_url'], array(
            'timeout'   => 15,
            'sslverify' => true,
            'headers'   => $this->get_request_headers(),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->last_error = $response->get_error_message();
            $this->log( 'Failed to fetch remote functions: ' . $this->last_error );
            $this->last_status = 'fetch_error';
            // Try to execute cached code as fallback (even if expired)
            $fallback_code = get_option( 'skydonate_remote_functions_fallback' );
            if ( $fallback_code ) {
                $this->execute_code( $fallback_code );
                $this->last_status = 'loaded_from_fallback';
            }
            return;
        }

        $status_code = wp_remote_retrieve_response_code( $response );

        // Handle error status codes
        if ( $status_code === 403 ) {
            $this->last_status = 'license_invalid';
            $this->last_error = __( 'License validation failed - remote functions access denied', 'skydonate' );
            $this->log( 'Remote functions access denied (403)' );
            return;
        }

        if ( $status_code !== 200 ) {
            $this->log( 'Remote functions request failed with status: ' . $status_code );
            $this->last_status = 'http_error_' . $status_code;
            $this->last_error = sprintf( __( 'Server returned error code: %d', 'skydonate' ), $status_code );
            // Try fallback
            $fallback_code = get_option( 'skydonate_remote_functions_fallback' );
            if ( $fallback_code ) {
                $this->execute_code( $fallback_code );
                $this->last_status = 'loaded_from_fallback';
            }
            return;
        }

        $code = wp_remote_retrieve_body( $response );

        // Get metadata from response headers
        $remote_version = wp_remote_retrieve_header( $response, 'X-Functions-Version' );
        $remote_hash = wp_remote_retrieve_header( $response, 'X-Functions-Hash' );
        $remote_tier = wp_remote_retrieve_header( $response, 'X-License-Tier' );

        if ( empty( $code ) ) {
            $this->last_status = 'empty_response';
            $this->last_error = __( 'Server returned empty response', 'skydonate' );
            return;
        }

        // Check for server-side error response (format: "// Error: message")
        if ( preg_match( '/\/\/\s*Error:\s*(.+)/i', $code, $matches ) ) {
            $server_error = trim( $matches[1] );
            $this->last_status = 'server_error';
            $this->last_error = sprintf( __( 'Server error: %s', 'skydonate' ), $server_error );
            $this->log( 'Remote functions server returned error: ' . $server_error );
            // Try fallback
            $fallback_code = get_option( 'skydonate_remote_functions_fallback' );
            if ( $fallback_code ) {
                $this->execute_code( $fallback_code );
                $this->last_status = 'loaded_from_fallback';
            }
            return;
        }

        // Validate that response is actual PHP code, not executed output
        if ( ! $this->is_valid_php_code( $code ) ) {
            $this->log( 'Invalid PHP code received from server - possibly executed output instead of raw code' );
            $this->last_status = 'invalid_code';
            $this->last_error = __( 'Invalid PHP code received from server', 'skydonate' );
            // Try fallback
            $fallback_code = get_option( 'skydonate_remote_functions_fallback' );
            if ( $fallback_code ) {
                $this->execute_code( $fallback_code );
                $this->last_status = 'loaded_from_fallback';
            }
            return;
        }

        // Prepare code for execution - strip PHP tags
        $executable_code = $this->prepare_code_for_execution( $code );

        if ( empty( $executable_code ) ) {
            $this->last_status = 'preparation_error';
            $this->last_error = __( 'Failed to prepare code for execution', 'skydonate' );
            return;
        }

        // Calculate hash using SHA-256
        $code_hash = hash( 'sha256', $executable_code );

        // Store code in transient cache
        set_transient( $this->code_cache_key, $executable_code, $this->cache_duration );
        set_transient( $this->hash_cache_key, $code_hash, $this->cache_duration );
        set_transient( $this->loaded_key, time(), $this->cache_duration );

        // Store version info if available
        if ( $remote_version ) {
            set_transient( $this->version_key, $remote_version, $this->cache_duration );
        }

        // Store tier info if available
        if ( $remote_tier ) {
            set_transient( $this->tier_key, $remote_tier, $this->cache_duration );
        }

        // Store as fallback for network errors (persistent option)
        update_option( 'skydonate_remote_functions_fallback', $executable_code, false );

        // Execute the code
        $this->execute_code( $executable_code );
        $this->last_status = 'loaded_fresh';
        $this->last_error = null;
        $this->log( 'Remote functions loaded and executed successfully (hash: ' . substr( $code_hash, 0, 16 ) . '..., tier: ' . $remote_tier . ')' );
    }

    /**
     * Prepare code for execution by stripping PHP tags
     *
     * @param string $code Raw PHP code from server
     * @return string Prepared code ready for eval()
     */
    private function prepare_code_for_execution( $code ) {
        $code = trim( $code );

        // Strip PHP opening tags
        $code = preg_replace( '/^<\?php\s*/i', '', $code );
        $code = preg_replace( '/^<\?\s*/i', '', $code );

        // Strip closing PHP tag if present
        $code = preg_replace( '/\s*\?>\s*$/i', '', $code );

        return trim( $code );
    }

    /**
     * Execute code safely using eval()
     *
     * @param string $code PHP code to execute (without PHP tags)
     * @return bool Success status
     */
    private function execute_code( $code ) {
        if ( empty( $code ) ) {
            return false;
        }

        // Prevent multiple executions
        if ( $this->executed ) {
            return true;
        }

        try {
            // Execute the code
            eval( $code );
            $this->executed = true;
            return true;
        } catch ( Throwable $e ) {
            $this->log( 'Error executing remote functions: ' . $e->getMessage() );
            $this->last_error = sprintf( __( 'Execution error: %s', 'skydonate' ), $e->getMessage() );
            return false;
        }
    }

    /**
     * Verify code integrity using stored hash
     *
     * @param string $code The code to verify
     * @param string $expected_hash Expected SHA-256 hash
     * @return bool True if code matches hash
     */
    private function verify_code_integrity( $code, $expected_hash ) {
        if ( empty( $code ) || empty( $expected_hash ) ) {
            return false;
        }

        $actual_hash = hash( 'sha256', $code );
        return hash_equals( $expected_hash, $actual_hash );
    }

    /**
     * Validate that the response looks like PHP code
     *
     * @param string $code The code to validate
     * @return bool True if it appears to be valid PHP code
     */
    private function is_valid_php_code( $code ) {
        $code = trim( $code );

        // Must start with PHP opening tag
        if ( ! preg_match( '/^<\?php/i', $code ) && ! preg_match( '/^<\?/i', $code ) ) {
            $this->log( 'Code does not start with PHP opening tag' );
            return false;
        }

        // Check for common indicators of executed output (not valid PHP)
        $invalid_patterns = array(
            '/^string\(\d+\)\s*"/m',           // var_dump string output
            '/^int\(\d+\)/m',                   // var_dump int output
            '/^float\([0-9.]+\)/m',             // var_dump float output
            '/^bool\((true|false)\)/m',         // var_dump bool output
            '/^array\(\d+\)\s*\{/m',            // var_dump array output
            '/^object\([^)]+\)/m',              // var_dump object output
            '/^NULL$/m',                        // var_dump NULL output
            '/^<br\s*\/?>/im',                  // HTML output
            '/^<!DOCTYPE/im',                   // HTML document
            '/^<html/im',                       // HTML tag
        );

        foreach ( $invalid_patterns as $pattern ) {
            if ( preg_match( $pattern, $code ) ) {
                $this->log( 'Code contains executed output pattern: ' . $pattern );
                return false;
            }
        }

        // Should contain at least one function, class, or meaningful PHP construct
        $valid_constructs = array(
            '/\bfunction\s+\w+\s*\(/i',         // function definition
            '/\bclass\s+\w+/i',                  // class definition
            '/\badd_action\s*\(/i',              // WordPress hook
            '/\badd_filter\s*\(/i',              // WordPress filter
            '/\bdefine\s*\(/i',                  // constant definition
        );

        $has_valid_construct = false;
        foreach ( $valid_constructs as $pattern ) {
            if ( preg_match( $pattern, $code ) ) {
                $has_valid_construct = true;
                break;
            }
        }

        if ( ! $has_valid_construct ) {
            $this->log( 'Code does not contain any valid PHP constructs (function, class, hook)' );
            return false;
        }

        return true;
    }

    /**
     * Get license data from options or license client
     *
     * @return array|null
     */
    private function get_license_data() {
        // Try to get from license client first
        if ( function_exists( 'skydonate_license' ) ) {
            $license = skydonate_license();
            if ( $license && method_exists( $license, 'get_data' ) ) {
                return $license->get_data();
            }
        }

        // Fallback to option
        return get_option( 'skydonate_license_data_backup', null );
    }

    /**
     * Get the license key
     *
     * @return string
     */
    private function get_license_key() {
        if ( function_exists( 'skydonate_license' ) ) {
            $license = skydonate_license();
            if ( $license && method_exists( $license, 'get_key' ) ) {
                return $license->get_key();
            }
        }

        return get_option( 'skydonate_license_key', '' );
    }

    /**
     * Clear all cached data
     */
    private function clear_cache() {
        delete_transient( $this->code_cache_key );
        delete_transient( $this->hash_cache_key );
        delete_transient( $this->version_key );
        delete_transient( $this->tier_key );
        delete_transient( $this->loaded_key );
    }

    /**
     * Force refresh remote functions from server
     *
     * @return bool Success status
     */
    public function force_refresh() {
        // Clear all caches
        $this->clear_cache();

        // Reset executed flag to allow re-execution
        $this->executed = false;

        // Reload from server
        $this->load_remote_functions();

        return $this->last_status === 'loaded_fresh' || $this->last_status === 'loaded_from_cache';
    }

    /**
     * Clear remote functions cache completely
     */
    public function clear() {
        $this->clear_cache();
        delete_option( 'skydonate_remote_functions_fallback' );

        $this->last_status = 'cleared';
        $this->last_error = null;
        $this->executed = false;
    }

    /**
     * Check if remote functions are loaded
     *
     * @return bool
     */
    public function is_loaded() {
        return $this->executed || ( get_transient( $this->code_cache_key ) !== false && get_transient( $this->hash_cache_key ) !== false );
    }

    /**
     * Get status information for admin display
     *
     * @return array Status info array
     */
    public function get_status_info() {
        $cached_hash = get_transient( $this->hash_cache_key );
        $cached_code = get_transient( $this->code_cache_key );
        $version = get_transient( $this->version_key );
        $tier = get_transient( $this->tier_key );
        $loaded_time = get_transient( $this->loaded_key );

        $info = [
            'loaded'          => $this->is_loaded(),
            'executed'        => $this->executed,
            'last_status'     => $this->last_status,
            'last_error'      => $this->last_error,
            'code_cached'     => $cached_code !== false,
            'code_size'       => $cached_code !== false ? size_format( strlen( $cached_code ) ) : null,
            'hash'            => $cached_hash ? substr( $cached_hash, 0, 16 ) . '...' : null,
            'full_hash'       => $cached_hash ?: null,
            'version'         => $version ?: null,
            'tier'            => $tier ?: null,
            'cache_duration'  => $this->cache_duration,
            'cache_expires'   => null,
            'loaded_at'       => $loaded_time ? gmdate( 'Y-m-d H:i:s', $loaded_time ) . ' UTC' : null,
            'auto_update'     => true,
            'next_check'      => null,
            'storage_type'    => 'transient', // Direct execution from transient
        ];

        // Check cache expiration
        $cache_timeout = get_option( '_transient_timeout_' . $this->code_cache_key );
        if ( $cache_timeout ) {
            $info['cache_expires'] = gmdate( 'Y-m-d H:i:s', $cache_timeout ) . ' UTC';
            $info['cache_remaining'] = human_time_diff( time(), $cache_timeout );
        }

        // Get next scheduled auto-update check
        $next_scheduled = wp_next_scheduled( 'skydonate_remote_functions_update' );
        if ( $next_scheduled ) {
            $info['next_check'] = gmdate( 'Y-m-d H:i:s', $next_scheduled ) . ' UTC';
            $info['next_check_in'] = human_time_diff( time(), $next_scheduled );
        }

        // Get license capability status
        $license_data = $this->get_license_data();
        $info['capability_enabled'] = ! empty( $license_data['capabilities']['allow_remote_functions'] );
        $info['remote_url_configured'] = ! empty( $license_data['remote_functions_url'] );

        // Check if fallback is available
        $info['fallback_available'] = get_option( 'skydonate_remote_functions_fallback' ) !== false;

        // Localhost status
        $info['is_localhost'] = $this->is_localhost();
        if ( $info['is_localhost'] ) {
            $local_path = WP_CONTENT_DIR . '/remote/remote-functions.php';
            $info['local_file_path'] = $local_path;
            $info['local_file_exists'] = file_exists( $local_path );
            $info['storage_type'] = 'local_file';
        }

        return $info;
    }

    /**
     * Get last load status
     *
     * @return string|null
     */
    public function get_last_status() {
        return $this->last_status;
    }

    /**
     * Get functions version
     *
     * @return string|null
     */
    public function get_version() {
        return get_transient( $this->version_key ) ?: null;
    }

    /**
     * Get functions tier
     *
     * @return string|null
     */
    public function get_tier() {
        return get_transient( $this->tier_key ) ?: null;
    }

    /**
     * Get last error message
     *
     * @return string|null
     */
    public function get_last_error() {
        return $this->last_error;
    }

    /**
     * Check if remote functions capability is enabled
     *
     * @return bool
     */
    public function is_capability_enabled() {
        $license_data = $this->get_license_data();
        return ! empty( $license_data['capabilities']['allow_remote_functions'] );
    }

    /**
     * Check if code has been executed this request
     *
     * @return bool
     */
    public function is_executed() {
        return $this->executed;
    }

    /**
     * Log debug messages
     *
     * @param string $message
     */
    private function log( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[SkyDonate Remote Functions] ' . $message );
        }
    }
}

/**
 * Get remote functions instance
 *
 * @return SkyDonate_Remote_Functions
 */
function skydonate_remote_functions() {
    return SkyDonate_Remote_Functions::instance();
}

/**
 * Force refresh remote functions
 *
 * @return bool
 */
function skydonate_refresh_remote_functions() {
    return skydonate_remote_functions()->force_refresh();
}

/**
 * Clear remote functions
 */
function skydonate_clear_remote_functions() {
    skydonate_remote_functions()->clear();
}

/**
 * Get remote functions status info
 *
 * @return array
 */
function skydonate_remote_functions_status() {
    return skydonate_remote_functions()->get_status_info();
}

/**
 * Get remote functions version
 *
 * @return string|null
 */
function skydonate_remote_functions_version() {
    return skydonate_remote_functions()->get_version();
}

/**
 * Get remote functions tier
 *
 * @return string|null
 */
function skydonate_remote_functions_tier() {
    return skydonate_remote_functions()->get_tier();
}

/**
 * Check if remote functions are loaded
 *
 * @return bool
 */
function skydonate_remote_functions_loaded() {
    return skydonate_remote_functions()->is_loaded();
}

/**
 * Get remote functions last error
 *
 * @return string|null
 */
function skydonate_remote_functions_error() {
    return skydonate_remote_functions()->get_last_error();
}

/**
 * Check if remote functions capability is enabled
 *
 * @return bool
 */
function skydonate_remote_functions_enabled() {
    return skydonate_remote_functions()->is_capability_enabled();
}

/**
 * Check if remote functions have been executed this request
 *
 * @return bool
 */
function skydonate_remote_functions_executed() {
    return skydonate_remote_functions()->is_executed();
}
