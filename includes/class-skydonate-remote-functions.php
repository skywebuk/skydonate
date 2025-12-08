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

        // Write to file and include instead of using eval()
        $file_content = '<?php' . "\n" . '// Remote functions loaded at: ' . gmdate( 'Y-m-d H:i:s' ) . "\n" . $code;

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
