<?php
/**
 * SkyDonate Remote Functions Loader
 *
 * Handles loading and executing remote functions from the license server
 *
 * @package SkyDonate
 * @version 1.1.0
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
     * Whether remote functions have been loaded in this request
     */
    private $loaded = false;

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'load_remote_functions' ), 5 );

        // Refresh remote functions when license is refreshed or activated
        add_action( 'skydonate_license_activated', array( $this, 'on_license_refresh' ), 10, 2 );
        add_action( 'skydonate_license_validated', array( $this, 'on_license_refresh' ), 10, 1 );

        // Clean up stale temp files periodically
        add_action( 'skydonate_daily_cleanup', array( $this, 'cleanup_temp_files' ) );
        if ( ! wp_next_scheduled( 'skydonate_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'skydonate_daily_cleanup' );
        }
    }

    /**
     * Handle license refresh/activation - regenerate remote functions
     *
     * @param array|string $data License data or key
     */
    public function on_license_refresh( $data = null ) {
        // Clear existing cache to force reload
        delete_transient( $this->cache_key );

        // Delete existing file
        $functions_file = $this->get_functions_file_path();
        if ( file_exists( $functions_file ) ) {
            wp_delete_file( $functions_file );
        }

        // Reload remote functions
        $this->load_remote_functions();

        $this->log( 'Remote functions regenerated after license refresh' );
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
            $this->log( 'Remote functions response body is empty' );
            return;
        }

        // Sanitize the remote code - strip PHP opening and closing tags
        $code = $this->sanitize_php_code( $code );

        if ( empty( trim( $code ) ) ) {
            $this->log( 'Remote functions code is empty after sanitization' );
            return;
        }

        // Validate that the code doesn't contain obvious security issues
        if ( ! $this->validate_code( $code ) ) {
            $this->log( 'Remote functions code failed validation' );
            return;
        }

        // Build the file content with proper PHP opening tag
        $file_content = "<?php\n";
        $file_content .= "/**\n";
        $file_content .= " * SkyDonate Remote Functions\n";
        $file_content .= " * Loaded at: " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n";
        $file_content .= " * Hash: " . md5( $code ) . "\n";
        $file_content .= " * DO NOT EDIT - This file is automatically generated\n";
        $file_content .= " */\n\n";
        $file_content .= "if ( ! defined( 'ABSPATH' ) ) { exit; }\n\n";
        $file_content .= $code;

        // Ensure directory exists
        $upload_dir = wp_upload_dir();
        if ( ! file_exists( $upload_dir['basedir'] ) ) {
            wp_mkdir_p( $upload_dir['basedir'] );
        }

        // Write the file atomically using a temporary file
        $temp_file = $functions_file . '.tmp.' . wp_generate_password( 8, false );
        $result = file_put_contents( $temp_file, $file_content, LOCK_EX );

        if ( $result === false || $result === 0 ) {
            $this->log( 'Failed to write remote functions to temporary file' );
            if ( file_exists( $temp_file ) ) {
                wp_delete_file( $temp_file );
            }
            // Try to load cached file if available
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
            }
            return;
        }

        // Atomically replace the old file with the new one
        if ( ! rename( $temp_file, $functions_file ) ) {
            $this->log( 'Failed to rename temporary file to remote functions file' );
            if ( file_exists( $temp_file ) ) {
                wp_delete_file( $temp_file );
            }
            // Try to load cached file if available
            if ( file_exists( $functions_file ) ) {
                $this->include_functions_file( $functions_file );
            }
            return;
        }

        set_transient( $this->cache_key, md5( $code ), $this->cache_duration );
        $this->include_functions_file( $functions_file );
        $this->log( 'Remote functions loaded successfully (' . strlen( $code ) . ' bytes)' );
    }

    /**
     * Include the functions file safely
     *
     * @param string $file Path to the functions file
     * @return bool True if file was included successfully
     */
    private function include_functions_file( $file ) {
        if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
            $this->log( 'Remote functions file does not exist or is not readable: ' . $file );
            return false;
        }

        // Check file size to prevent loading huge files
        $file_size = filesize( $file );
        if ( $file_size === false || $file_size > 1048576 ) { // 1MB max
            $this->log( 'Remote functions file is too large or unreadable: ' . $file_size . ' bytes' );
            return false;
        }

        try {
            include_once $file;
            $this->loaded = true;
            return true;
        } catch ( Exception $e ) {
            $this->log( 'Error including remote functions file: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Sanitize PHP code by removing opening and closing tags
     *
     * @param string $code The raw PHP code
     * @return string Sanitized code without PHP tags
     */
    private function sanitize_php_code( $code ) {
        // Trim whitespace
        $code = trim( $code );

        if ( empty( $code ) ) {
            return '';
        }

        // Remove PHP opening tags (<?php, <?, <?=) from the beginning
        // Using a more robust pattern that handles various whitespace scenarios
        $code = preg_replace( '/^<\?(?:php)?\s*/i', '', $code );

        // Remove short echo tag if present at the beginning
        $code = preg_replace( '/^<\?=\s*/i', '', $code );

        // Remove PHP closing tag from the end (and any trailing whitespace)
        $code = preg_replace( '/\s*\?>\s*$/i', '', $code );

        // Handle cases where code might have multiple opening tags from copy-paste issues
        // Only strip opening tag if code starts with one after previous operations
        while ( preg_match( '/^<\?(?:php)?\s*/i', $code ) ) {
            $code = preg_replace( '/^<\?(?:php)?\s*/i', '', $code );
        }

        return trim( $code );
    }

    /**
     * Validate the code doesn't contain dangerous patterns
     *
     * @param string $code The PHP code to validate
     * @return bool True if code passes validation
     */
    private function validate_code( $code ) {
        if ( empty( $code ) ) {
            return false;
        }

        // Check for minimum reasonable code length
        if ( strlen( $code ) < 10 ) {
            $this->log( 'Remote functions code is too short to be valid' );
            return false;
        }

        // Check that the code doesn't try to include PHP opening tags in the middle
        // (which would cause syntax errors)
        if ( preg_match( '/<\?(?:php|=)/i', $code ) ) {
            $this->log( 'Remote functions code contains embedded PHP opening tags' );
            return false;
        }

        // Basic check that it looks like PHP code (contains at least some valid PHP syntax)
        // This is a simple heuristic - the remote server should provide valid code
        $has_function = preg_match( '/function\s+\w+\s*\(/i', $code );
        $has_class = preg_match( '/class\s+\w+/i', $code );
        $has_variable = preg_match( '/\$\w+/', $code );
        $has_statement = preg_match( '/(?:return|if|echo|print|add_action|add_filter|apply_filters|do_action)\s*[\(\s]/i', $code );

        if ( ! $has_function && ! $has_class && ! $has_variable && ! $has_statement ) {
            $this->log( 'Remote functions code does not appear to contain valid PHP constructs' );
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
        return $this->loaded || ( file_exists( $functions_file ) && get_transient( $this->cache_key ) !== false );
    }

    /**
     * Get the hash of currently loaded remote functions
     *
     * @return string|null
     */
    public function get_current_hash() {
        $hash = get_transient( $this->cache_key );
        return $hash !== false ? $hash : null;
    }

    /**
     * Get info about the currently loaded remote functions
     *
     * @return array
     */
    public function get_info() {
        $functions_file = $this->get_functions_file_path();
        $info = array(
            'loaded'      => $this->is_loaded(),
            'file_exists' => file_exists( $functions_file ),
            'file_path'   => $functions_file,
            'hash'        => $this->get_current_hash(),
            'cached'      => get_transient( $this->cache_key ) !== false,
        );

        if ( $info['file_exists'] ) {
            $info['file_size'] = filesize( $functions_file );
            $info['file_modified'] = gmdate( 'Y-m-d H:i:s', filemtime( $functions_file ) );
        }

        return $info;
    }

    /**
     * Clean up stale temporary files
     */
    public function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'];

        if ( ! is_dir( $base_path ) ) {
            return;
        }

        $files = glob( $base_path . '/skydonate-remote-functions.php.tmp.*' );

        if ( empty( $files ) ) {
            return;
        }

        $cleaned = 0;
        $cutoff_time = time() - HOUR_IN_SECONDS; // Clean up files older than 1 hour

        foreach ( $files as $file ) {
            if ( is_file( $file ) && filemtime( $file ) < $cutoff_time ) {
                wp_delete_file( $file );
                $cleaned++;
            }
        }

        if ( $cleaned > 0 ) {
            $this->log( 'Cleaned up ' . $cleaned . ' stale temporary files' );
        }
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
 * Check if remote functions are loaded
 *
 * @return bool
 */
function skydonate_remote_functions_loaded() {
    return skydonate_remote_functions()->is_loaded();
}

/**
 * Get remote functions info
 *
 * @return array
 */
function skydonate_remote_functions_info() {
    return skydonate_remote_functions()->get_info();
}