<?php
/**
 * SkyDonate Remote Functions Loader
 *
 * Handles loading and executing remote functions from the license server
 * Compatible with Sky License Manager remote functions server
 *
 * @package SkyDonate
 * @version 2.0.6
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
     * Cache key for remote functions hash
     */
    private $cache_key = 'skydonate_remote_functions_hash';

    /**
     * Cache key for version info
     */
    private $version_key = 'skydonate_remote_functions_version';

    /**
     * Cache key for tier info
     */
    private $tier_key = 'skydonate_remote_functions_tier';

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
        $schedules['skydonate_six_hours'] = array(
            'interval' => 6 * HOUR_IN_SECONDS,
            'display'  => __( 'Every 6 Hours', 'skydonate' ),
        );
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
            $current_hash = get_transient( $this->cache_key );

            // Compare hashes
            if ( $remote_hash && $current_hash && hash_equals( $current_hash, $remote_hash ) ) {
                $this->log( 'Auto-update check: Hash unchanged, skipping' );
                return;
            }

            // Clear cache and reload
            $this->log( 'Auto-update check: New version detected (v' . $remote_version . ', tier: ' . $remote_tier . '), updating...' );
            delete_transient( $this->cache_key );
            delete_transient( $this->version_key );
            delete_transient( $this->tier_key );
            $this->load_remote_functions();
        }
    }

    /**
     * Get request headers for server communication
     * Headers match Sky License Manager server expectations
     *
     * @return array
     */
    private function get_request_headers() {
        return array(
            'X-LICENSE-KEY'    => $this->get_license_key(),
            'X-SITE-URL'       => home_url(),
            'X-Plugin-Version' => defined( 'SKYDONATE_VERSION' ) ? SKYDONATE_VERSION : '1.0.0',
            'X-Current-Hash'   => get_transient( $this->cache_key ) ?: '',
        );
    }

    /**
     * Get the remote functions file path
     *
     * @return string
     */
    private function get_functions_file_path() {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/skydonate-remote-functions.php';
    }

    /**
     * Load remote functions safely
     * Compatible with Sky License Manager remote functions server
     */
    public function load_remote_functions() {
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

        $functions_file = $this->get_functions_file_path();

        // Check cache and verify file integrity
        $cached_hash = get_transient( $this->cache_key );
        if ( $cached_hash !== false && file_exists( $functions_file ) ) {
            // Verify file integrity before loading
            if ( $this->verify_file_integrity( $functions_file, $cached_hash ) ) {
                $this->include_functions_file( $functions_file );
                $this->last_status = 'loaded_from_cache';
                return;
            }
            // File integrity failed, clear cache and reload
            $this->log( 'File integrity check failed, reloading...' );
            delete_transient( $this->cache_key );
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
            // Try to load cached file if available
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
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
            // Try to load cached file if available
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
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

        // Validate that response is actual PHP code, not executed output
        if ( ! $this->is_valid_php_code( $code ) ) {
            $this->log( 'Invalid PHP code received from server - possibly executed output instead of raw code' );
            $this->last_status = 'invalid_code';
            // Try to load cached file if available
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
                $this->last_status = 'loaded_from_fallback';
            }
            return;
        }

        // Strip any existing PHP opening tags from the response
        $code = preg_replace( '/^<\?php\s*/i', '', trim( $code ) );
        $code = preg_replace( '/^<\?\s*/i', '', $code );

        // Also strip closing PHP tag if present
        $code = preg_replace( '/\s*\?>\s*$/i', '', $code );

        // Calculate hash using SHA-256 (more secure than MD5)
        $code_hash = hash( 'sha256', $code );

        // Build file header with metadata
        $header = '<?php' . "\n";
        $header .= '// SkyDonate Remote Functions' . "\n";
        $header .= '// Loaded: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC' . "\n";
        $header .= '// Hash: ' . $code_hash . "\n";
        if ( $remote_version ) {
            $header .= '// Version: ' . sanitize_text_field( $remote_version ) . "\n";
        }
        if ( $remote_tier ) {
            $header .= '// Tier: ' . sanitize_text_field( $remote_tier ) . "\n";
        }
        $header .= '// DO NOT EDIT - This file is automatically generated' . "\n\n";

        $file_content = $header . $code;

        // Validate the final file content is valid PHP
        if ( ! $this->validate_php_syntax( $file_content ) ) {
            $this->log( 'PHP syntax validation failed for remote functions' );
            $this->last_status = 'syntax_error';
            $this->last_error = __( 'PHP syntax validation failed', 'skydonate' );
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
                $this->last_status = 'loaded_from_fallback';
            }
            return;
        }

        // Ensure directory exists
        $upload_dir = wp_upload_dir();
        if ( ! file_exists( $upload_dir['basedir'] ) ) {
            wp_mkdir_p( $upload_dir['basedir'] );
        }

        // Write the file
        $result = file_put_contents( $functions_file, $file_content );

        if ( $result !== false ) {
            // Store hash using SHA-256
            set_transient( $this->cache_key, $code_hash, $this->cache_duration );

            // Store version info if available
            if ( $remote_version ) {
                set_transient( $this->version_key, $remote_version, $this->cache_duration );
            }

            // Store tier info if available
            if ( $remote_tier ) {
                set_transient( $this->tier_key, $remote_tier, $this->cache_duration );
            }

            $this->include_functions_file( $functions_file );
            $this->last_status = 'loaded_fresh';
            $this->last_error = null;
            $this->log( 'Remote functions loaded successfully (hash: ' . substr( $code_hash, 0, 16 ) . '..., tier: ' . $remote_tier . ')' );
        } else {
            $this->last_status = 'write_error';
            $this->last_error = __( 'Failed to write remote functions file', 'skydonate' );
            $this->log( 'Failed to write remote functions file' );
        }
    }

    /**
     * Verify file integrity using stored hash
     *
     * @param string $file_path Path to file
     * @param string $expected_hash Expected SHA-256 hash
     * @return bool True if file matches hash
     */
    private function verify_file_integrity( $file_path, $expected_hash ) {
        if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
            return false;
        }

        $content = file_get_contents( $file_path );
        if ( $content === false ) {
            return false;
        }

        // Extract code portion (skip header comments)
        $code = preg_replace( '/^<\?php\s*(?:\/\/[^\n]*\n)*\s*/i', '', $content );
        $code = trim( $code );

        // Calculate hash of code portion
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
     * Validate PHP syntax using token_get_all
     *
     * @param string $code The PHP code to validate
     * @return bool True if syntax is valid
     */
    private function validate_php_syntax( $code ) {
        // Use token_get_all to check for parse errors
        try {
            $tokens = @token_get_all( $code );
            if ( empty( $tokens ) ) {
                return false;
            }

            // Check if tokenization produced valid results
            // Look for T_OPEN_TAG as first meaningful token
            foreach ( $tokens as $token ) {
                if ( is_array( $token ) ) {
                    if ( $token[0] === T_OPEN_TAG ) {
                        return true;
                    }
                    // Skip whitespace
                    if ( $token[0] === T_WHITESPACE ) {
                        continue;
                    }
                    // If first non-whitespace token is not open tag, invalid
                    return false;
                }
            }

            return false;
        } catch ( Exception $e ) {
            $this->log( 'PHP syntax validation exception: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Include the functions file safely
     *
     * @param string $file Path to the functions file
     */
    private function include_functions_file( $file ) {
        if ( file_exists( $file ) && is_readable( $file ) ) {
            include_once $file;
        }
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
     * Force refresh remote functions
     *
     * @return bool Success status
     */
    public function force_refresh() {
        // Clear cache
        delete_transient( $this->cache_key );

        // Delete existing file
        $functions_file = $this->get_functions_file_path();
        if ( file_exists( $functions_file ) ) {
            wp_delete_file( $functions_file );
        }

        // Reload
        $this->load_remote_functions();

        return file_exists( $functions_file );
    }

    /**
     * Clear remote functions cache and file
     */
    public function clear() {
        delete_transient( $this->cache_key );
        delete_transient( $this->version_key );
        delete_transient( $this->tier_key );

        $functions_file = $this->get_functions_file_path();
        if ( file_exists( $functions_file ) ) {
            wp_delete_file( $functions_file );
        }

        $this->last_status = 'cleared';
        $this->last_error = null;
    }

    /**
     * Check if remote functions are loaded
     *
     * @return bool
     */
    public function is_loaded() {
        $functions_file = $this->get_functions_file_path();
        return file_exists( $functions_file ) && get_transient( $this->cache_key ) !== false;
    }

    /**
     * Get status information for admin display
     *
     * @return array Status info array
     */
    public function get_status_info() {
        $functions_file = $this->get_functions_file_path();
        $cached_hash = get_transient( $this->cache_key );
        $version = get_transient( $this->version_key );
        $tier = get_transient( $this->tier_key );

        $info = [
            'loaded'          => $this->is_loaded(),
            'last_status'     => $this->last_status,
            'last_error'      => $this->last_error,
            'file_exists'     => file_exists( $functions_file ),
            'file_path'       => $functions_file,
            'hash'            => $cached_hash ? substr( $cached_hash, 0, 16 ) . '...' : null,
            'full_hash'       => $cached_hash ?: null,
            'version'         => $version ?: null,
            'tier'            => $tier ?: null,
            'cache_duration'  => $this->cache_duration,
            'cache_expires'   => null,
            'auto_update'     => true,
            'next_check'      => null,
        ];

        // Get file info if exists
        if ( $info['file_exists'] ) {
            $info['file_size'] = size_format( filesize( $functions_file ) );
            $info['file_modified'] = gmdate( 'Y-m-d H:i:s', filemtime( $functions_file ) ) . ' UTC';
        }

        // Check cache expiration
        $cache_timeout = get_option( '_transient_timeout_' . $this->cache_key );
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