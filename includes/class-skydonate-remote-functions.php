<?php
/**
 * SkyDonate Remote Functions Loader
 *
 * Handles loading and executing remote functions from the license server
 *
 * @package SkyDonate
 * @version 1.0.0
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
     * Cache duration (1 day)
     */
    private $cache_duration = DAY_IN_SECONDS;

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
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'load_remote_functions' ), 5 );
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
     */
    public function load_remote_functions() {
        $license_data = $this->get_license_data();

        if ( empty( $license_data ) || empty( $license_data['success'] ) ) {
            return;
        }

        if ( empty( $license_data['remote_functions_url'] ) ) {
            return;
        }

        if ( empty( $license_data['capabilities']['allow_remote_functions'] ) ) {
            return;
        }

        $functions_file = $this->get_functions_file_path();

        // Check cache (load once per day)
        $cached_hash = get_transient( $this->cache_key );
        if ( $cached_hash !== false && file_exists( $functions_file ) ) {
            $this->include_functions_file( $functions_file );
            return;
        }

        // Fetch remote functions
        $response = wp_remote_get( $license_data['remote_functions_url'], array(
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => array(
                'X-License-Key' => $this->get_license_key(),
                'X-Site-URL'    => home_url(),
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->log( 'Failed to fetch remote functions: ' . $response->get_error_message() );
            // Try to load cached file if available
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
            }
            return;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code !== 200 ) {
            $this->log( 'Remote functions request failed with status: ' . $status_code );
            // Try to load cached file if available
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
            }
            return;
        }

        $code = wp_remote_retrieve_body( $response );

        if ( empty( $code ) ) {
            return;
        }

        // Validate that response is actual PHP code, not executed output
        if ( ! $this->is_valid_php_code( $code ) ) {
            $this->log( 'Invalid PHP code received from server - possibly executed output instead of raw code' );
            // Try to load cached file if available
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
            }
            return;
        }

        // Strip any existing PHP opening tags from the response
        $code = preg_replace( '/^<\?php\s*/i', '', trim( $code ) );
        $code = preg_replace( '/^<\?\s*/i', '', $code );

        // Also strip closing PHP tag if present
        $code = preg_replace( '/\s*\?>\s*$/i', '', $code );

        // Write to file and include instead of using eval()
        $file_content = '<?php' . "\n" . '// Remote functions loaded at: ' . gmdate( 'Y-m-d H:i:s' ) . "\n" . $code;

        // Validate the final file content is valid PHP
        if ( ! $this->validate_php_syntax( $file_content ) ) {
            $this->log( 'PHP syntax validation failed for remote functions' );
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
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
            set_transient( $this->cache_key, md5( $code ), $this->cache_duration );
            $this->include_functions_file( $functions_file );
            $this->log( 'Remote functions loaded successfully' );
        } else {
            $this->log( 'Failed to write remote functions file' );
        }
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

        $functions_file = $this->get_functions_file_path();
        if ( file_exists( $functions_file ) ) {
            wp_delete_file( $functions_file );
        }
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