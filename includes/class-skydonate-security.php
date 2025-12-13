<?php
/**
 * SkyDonate Security Handler
 *
 * Provides security features for license validation and remote functions
 * - Request signing with HMAC
 * - File integrity verification
 * - Encryption/Decryption
 * - Anti-tampering protection
 * - Replay attack prevention
 *
 * @package SkyDonate
 * @version 2.0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_Security {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Encryption method
     */
    private $cipher = 'AES-256-CBC';

    /**
     * Request timestamp tolerance (5 minutes)
     */
    private $timestamp_tolerance = 300;

    /**
     * Integrity check cache key
     */
    private $integrity_cache_key = 'skydonate_file_integrity';

    /**
     * Critical files to monitor
     */
    private $critical_files = array(
        'skydonate.php',
        'includes/class-skydonate-license-client.php',
        'includes/class-skydonate-remote-functions.php',
        'includes/class-skydonate-updater.php',
        'includes/class-skydonate-security.php',
    );

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
        // Run integrity check on admin pages
        add_action( 'admin_init', array( $this, 'verify_file_integrity' ) );

        // Add security headers
        add_action( 'send_headers', array( $this, 'add_security_headers' ) );
    }

    // ===========================================
    // REQUEST SIGNING (HMAC)
    // ===========================================

    /**
     * Generate HMAC signature for request
     *
     * @param array  $data      Request data
     * @param string $secret    Secret key (license key)
     * @param int    $timestamp Unix timestamp
     * @return string HMAC signature
     */
    public function sign_request( $data, $secret, $timestamp = null ) {
        if ( $timestamp === null ) {
            $timestamp = time();
        }

        // Create canonical string
        $canonical = $this->create_canonical_string( $data, $timestamp );

        // Generate HMAC-SHA256 signature
        return hash_hmac( 'sha256', $canonical, $secret );
    }

    /**
     * Verify request signature
     *
     * @param array  $data      Request data
     * @param string $signature Provided signature
     * @param string $secret    Secret key
     * @param int    $timestamp Request timestamp
     * @return bool True if valid
     */
    public function verify_signature( $data, $signature, $secret, $timestamp ) {
        // Check timestamp is within tolerance
        if ( ! $this->is_timestamp_valid( $timestamp ) ) {
            return false;
        }

        // Generate expected signature
        $expected = $this->sign_request( $data, $secret, $timestamp );

        // Timing-safe comparison
        return hash_equals( $expected, $signature );
    }

    /**
     * Create canonical string for signing
     *
     * @param array $data      Data to sign
     * @param int   $timestamp Unix timestamp
     * @return string Canonical string
     */
    private function create_canonical_string( $data, $timestamp ) {
        // Sort data by key
        ksort( $data );

        // Build canonical string
        $parts = array();
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $value = wp_json_encode( $value );
            }
            $parts[] = rawurlencode( $key ) . '=' . rawurlencode( $value );
        }

        return implode( '&', $parts ) . '&timestamp=' . $timestamp;
    }

    /**
     * Check if timestamp is within acceptable range
     *
     * @param int $timestamp Unix timestamp
     * @return bool True if valid
     */
    public function is_timestamp_valid( $timestamp ) {
        $now = time();
        return abs( $now - $timestamp ) <= $this->timestamp_tolerance;
    }

    /**
     * Get signed request headers
     *
     * @param string $license_key License key
     * @param array  $data        Request data
     * @return array Headers with signature
     */
    public function get_signed_headers( $license_key, $data = array() ) {
        $timestamp = time();
        $nonce = $this->generate_nonce();

        // Add timestamp and nonce to data for signing
        $sign_data = array_merge( $data, array(
            'timestamp' => $timestamp,
            'nonce'     => $nonce,
        ) );

        $signature = $this->sign_request( $sign_data, $license_key, $timestamp );

        return array(
            'X-LICENSE-KEY'       => $license_key,
            'X-SITE-URL'          => home_url(),
            'X-REQUEST-TIMESTAMP' => $timestamp,
            'X-REQUEST-NONCE'     => $nonce,
            'X-REQUEST-SIGNATURE' => $signature,
        );
    }

    // ===========================================
    // ENCRYPTION / DECRYPTION
    // ===========================================

    /**
     * Get encryption key
     *
     * @return string Encryption key
     */
    private function get_encryption_key() {
        // Use WordPress auth key + site-specific salt
        $key = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'skydonate_default_key';
        $salt = defined( 'AUTH_SALT' ) ? AUTH_SALT : get_option( 'siteurl' );

        return hash( 'sha256', $key . $salt, true );
    }

    /**
     * Encrypt data
     *
     * @param mixed $data Data to encrypt
     * @return string|false Encrypted string or false
     */
    public function encrypt( $data ) {
        if ( ! function_exists( 'openssl_encrypt' ) ) {
            // Fallback: Only allow scalar data types for serialization
            if ( is_object( $data ) ) {
                return false;
            }
            return base64_encode( serialize( $data ) );
        }

        $key = $this->get_encryption_key();
        $iv_length = openssl_cipher_iv_length( $this->cipher );
        $iv = openssl_random_pseudo_bytes( $iv_length );

        $encrypted = openssl_encrypt(
            serialize( $data ),
            $this->cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ( $encrypted === false ) {
            return false;
        }

        // Combine IV + encrypted data + HMAC
        $combined = $iv . $encrypted;
        $hmac = hash_hmac( 'sha256', $combined, $key, true );

        return base64_encode( $hmac . $combined );
    }

    /**
     * Decrypt data
     *
     * @param string $encrypted Encrypted string
     * @return mixed|false Decrypted data or false
     */
    public function decrypt( $encrypted ) {
        if ( ! function_exists( 'openssl_decrypt' ) ) {
            // Fallback: use allowed_classes to prevent object injection
            $decoded = base64_decode( $encrypted );
            if ( $decoded === false ) {
                return false;
            }
            return unserialize( $decoded, array( 'allowed_classes' => false ) );
        }

        $key = $this->get_encryption_key();
        $decoded = base64_decode( $encrypted );

        if ( $decoded === false || strlen( $decoded ) < 48 ) {
            return false;
        }

        // Extract HMAC, IV, and encrypted data
        $hmac = substr( $decoded, 0, 32 );
        $iv_length = openssl_cipher_iv_length( $this->cipher );
        $iv = substr( $decoded, 32, $iv_length );
        $encrypted_data = substr( $decoded, 32 + $iv_length );

        // Verify HMAC
        $combined = $iv . $encrypted_data;
        $expected_hmac = hash_hmac( 'sha256', $combined, $key, true );

        if ( ! hash_equals( $expected_hmac, $hmac ) ) {
            return false; // Tampered data
        }

        $decrypted = openssl_decrypt(
            $encrypted_data,
            $this->cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ( $decrypted === false ) {
            return false;
        }

        return unserialize( $decrypted );
    }

    // ===========================================
    // FILE INTEGRITY
    // ===========================================

    /**
     * Calculate file checksums
     *
     * @return array File checksums
     */
    public function calculate_file_checksums() {
        $checksums = array();
        $base_path = defined( 'SKYDONATE_PATH' ) ? SKYDONATE_PATH : plugin_dir_path( dirname( __FILE__ ) );

        foreach ( $this->critical_files as $file ) {
            $file_path = $base_path . $file;
            if ( file_exists( $file_path ) ) {
                $checksums[ $file ] = hash_file( 'sha256', $file_path );
            }
        }

        return $checksums;
    }

    /**
     * Save file checksums
     */
    public function save_checksums() {
        $checksums = $this->calculate_file_checksums();
        $encrypted = $this->encrypt( $checksums );
        update_option( $this->integrity_cache_key, $encrypted, false );
    }

    /**
     * Verify file integrity
     *
     * @return array|true True if valid, array of modified files otherwise
     */
    public function verify_file_integrity() {
        // Only check on admin pages, not every request
        if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return true;
        }

        $stored = get_option( $this->integrity_cache_key );

        // First run - save checksums
        if ( empty( $stored ) ) {
            $this->save_checksums();
            return true;
        }

        $stored_checksums = $this->decrypt( $stored );
        if ( ! is_array( $stored_checksums ) ) {
            $this->save_checksums();
            return true;
        }

        $current_checksums = $this->calculate_file_checksums();
        $modified = array();

        foreach ( $stored_checksums as $file => $hash ) {
            if ( ! isset( $current_checksums[ $file ] ) ) {
                $modified[] = $file . ' (deleted)';
            } elseif ( $current_checksums[ $file ] !== $hash ) {
                $modified[] = $file . ' (modified)';
            }
        }

        // Check for new files
        foreach ( $current_checksums as $file => $hash ) {
            if ( ! isset( $stored_checksums[ $file ] ) ) {
                $modified[] = $file . ' (new)';
            }
        }

        if ( ! empty( $modified ) ) {
            // Log the tampering attempt
            $this->log_security_event( 'file_tampering', array(
                'files' => $modified,
                'ip'    => $this->get_client_ip(),
            ) );

            // Notify admin (only once per day)
            $this->maybe_notify_admin( $modified );

            return $modified;
        }

        return true;
    }

    /**
     * Reset file checksums (after legitimate update)
     */
    public function reset_checksums() {
        delete_option( $this->integrity_cache_key );
        $this->save_checksums();
    }

    // ===========================================
    // ANTI-TAMPERING
    // ===========================================

    /**
     * Verify code integrity before execution
     *
     * @param string $code PHP code
     * @param string $expected_hash Expected hash
     * @return bool True if valid
     */
    public function verify_code_integrity( $code, $expected_hash ) {
        if ( empty( $code ) || empty( $expected_hash ) ) {
            return false;
        }

        $actual_hash = hash( 'sha256', $code );
        return hash_equals( $expected_hash, $actual_hash );
    }

    /**
     * Check for debugging/inspection tools
     *
     * @return bool True if suspicious activity detected
     */
    public function detect_inspection() {
        // Check for common debugging headers
        $suspicious_headers = array(
            'HTTP_X_DEBUG',
            'HTTP_X_FORWARDED_DEBUG',
            'HTTP_XDEBUG_SESSION',
            'HTTP_XDEBUG_SESSION_START',
        );

        foreach ( $suspicious_headers as $header ) {
            if ( ! empty( $_SERVER[ $header ] ) ) {
                return true;
            }
        }

        // Check for suspicious query parameters
        $suspicious_params = array( 'XDEBUG_SESSION_START', 'debug', 'trace' );
        foreach ( $suspicious_params as $param ) {
            if ( isset( $_GET[ $param ] ) || isset( $_POST[ $param ] ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate license key format strictly
     *
     * @param string $key License key
     * @return bool True if valid format
     */
    public function validate_license_format( $key ) {
        // Must be exactly SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX
        // Where X is uppercase alphanumeric
        if ( ! preg_match( '/^SKY-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}$/', $key ) ) {
            return false;
        }

        // Additional entropy check - ensure it's not a simple pattern
        $segments = explode( '-', substr( $key, 4 ) ); // Remove SKY- prefix
        foreach ( $segments as $segment ) {
            // Check for repeating characters
            if ( preg_match( '/^(.)\1{7}$/', $segment ) ) {
                return false; // All same character
            }
            // Check for sequential patterns
            if ( preg_match( '/^(01234567|12345678|ABCDEFGH)$/i', $segment ) ) {
                return false;
            }
        }

        return true;
    }

    // ===========================================
    // SECURITY UTILITIES
    // ===========================================

    /**
     * Generate secure nonce
     *
     * @param int $length Length of nonce
     * @return string Nonce
     */
    public function generate_nonce( $length = 32 ) {
        if ( function_exists( 'random_bytes' ) ) {
            return bin2hex( random_bytes( $length / 2 ) );
        }
        return wp_generate_password( $length, false );
    }

    /**
     * Get client IP address (secure)
     *
     * @return string IP address
     */
    public function get_client_ip() {
        // Only trust REMOTE_ADDR for security
        // X-Forwarded-For can be spoofed
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';

        // Validate IP
        if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            return $ip;
        }

        return '0.0.0.0';
    }

    /**
     * Add security headers
     */
    public function add_security_headers() {
        if ( is_admin() && isset( $_GET['page'] ) && strpos( $_GET['page'], 'skydonate' ) !== false ) {
            header( 'X-Content-Type-Options: nosniff' );
            header( 'X-Frame-Options: SAMEORIGIN' );
            header( 'X-XSS-Protection: 1; mode=block' );
        }
    }

    /**
     * Sanitize and validate domain
     *
     * @param string $domain Domain to validate
     * @return string|false Sanitized domain or false
     */
    public function sanitize_domain( $domain ) {
        // Remove protocol
        $domain = preg_replace( '#^https?://#i', '', $domain );

        // Remove path and query
        $domain = preg_replace( '#[/\?].*$#', '', $domain );

        // Remove www
        $domain = preg_replace( '/^www\./i', '', $domain );

        // Validate domain format
        if ( ! preg_match( '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*$/i', $domain ) ) {
            return false;
        }

        return strtolower( $domain );
    }

    /**
     * Obfuscate error message (hide system info)
     *
     * @param string $message Original message
     * @param string $code    Error code for logging
     * @return string Safe message
     */
    public function safe_error_message( $message, $code = '' ) {
        // Log the real error
        if ( ! empty( $code ) ) {
            $this->log_security_event( 'error', array(
                'code'    => $code,
                'message' => $message,
            ) );
        }

        // Return generic message
        $safe_messages = array(
            'connection_error'  => __( 'Unable to connect. Please try again.', 'skydonate' ),
            'validation_failed' => __( 'Validation failed. Please check your license.', 'skydonate' ),
            'access_denied'     => __( 'Access denied.', 'skydonate' ),
            'invalid_request'   => __( 'Invalid request.', 'skydonate' ),
            'server_error'      => __( 'Server error. Please try again later.', 'skydonate' ),
        );

        return $safe_messages[ $code ] ?? __( 'An error occurred.', 'skydonate' );
    }

    // ===========================================
    // LOGGING
    // ===========================================

    /**
     * Log security event
     *
     * @param string $event Event type
     * @param array  $data  Event data
     */
    public function log_security_event( $event, $data = array() ) {
        $log_entry = array(
            'event'     => $event,
            'timestamp' => current_time( 'mysql' ),
            'ip'        => $this->get_client_ip(),
            'user_id'   => get_current_user_id(),
            'data'      => $data,
        );

        // Store in options (limited to last 100 events)
        $log = get_option( 'skydonate_security_log', array() );
        array_unshift( $log, $log_entry );
        $log = array_slice( $log, 0, 100 );
        update_option( 'skydonate_security_log', $log, false );

        // Also log to error_log in debug mode
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[SkyDonate Security] ' . $event . ': ' . wp_json_encode( $data ) );
        }
    }

    /**
     * Get security log
     *
     * @param int $limit Number of entries
     * @return array Log entries
     */
    public function get_security_log( $limit = 50 ) {
        $log = get_option( 'skydonate_security_log', array() );
        return array_slice( $log, 0, $limit );
    }

    /**
     * Clear security log
     */
    public function clear_security_log() {
        delete_option( 'skydonate_security_log' );
    }

    /**
     * Maybe notify admin of security issue
     *
     * @param array $modified Modified files
     */
    private function maybe_notify_admin( $modified ) {
        $last_notify = get_transient( 'skydonate_security_notify' );

        if ( $last_notify ) {
            return; // Already notified today
        }

        // Set transient to prevent spam
        set_transient( 'skydonate_security_notify', true, DAY_IN_SECONDS );

        // Send email to admin
        $admin_email = get_option( 'admin_email' );
        $subject = __( '[SkyDonate] Security Alert: File Modification Detected', 'skydonate' );
        $message = sprintf(
            __( "Potential file tampering detected in SkyDonate plugin.\n\nModified files:\n%s\n\nIf you recently updated the plugin, you can ignore this message.\n\nTime: %s\nSite: %s", 'skydonate' ),
            implode( "\n", $modified ),
            current_time( 'mysql' ),
            home_url()
        );

        wp_mail( $admin_email, $subject, $message );
    }

    // ===========================================
    // RATE LIMITING
    // ===========================================

    /**
     * Check rate limit
     *
     * @param string $action   Action name
     * @param int    $limit    Max requests
     * @param int    $window   Time window in seconds
     * @return bool True if allowed
     */
    public function check_rate_limit( $action, $limit = 10, $window = 60 ) {
        $ip = $this->get_client_ip();
        $key = 'skydonate_rate_' . md5( $action . $ip );

        $data = get_transient( $key );

        if ( $data === false ) {
            set_transient( $key, array( 'count' => 1, 'start' => time() ), $window );
            return true;
        }

        if ( $data['count'] >= $limit ) {
            $this->log_security_event( 'rate_limit_exceeded', array(
                'action' => $action,
                'ip'     => $ip,
                'count'  => $data['count'],
            ) );
            return false;
        }

        $data['count']++;
        $remaining_time = $window - ( time() - $data['start'] );
        // Ensure minimum transient expiration of 1 second
        if ( $remaining_time < 1 ) {
            $remaining_time = 1;
        }
        set_transient( $key, $data, $remaining_time );

        return true;
    }
}

/**
 * Get security instance
 *
 * @return SkyDonate_Security
 */
function skydonate_security() {
    return SkyDonate_Security::instance();
}

/**
 * Encrypt data
 *
 * @param mixed $data Data to encrypt
 * @return string Encrypted data
 */
function skydonate_encrypt( $data ) {
    return skydonate_security()->encrypt( $data );
}

/**
 * Decrypt data
 *
 * @param string $encrypted Encrypted data
 * @return mixed Decrypted data
 */
function skydonate_decrypt( $encrypted ) {
    return skydonate_security()->decrypt( $encrypted );
}

/**
 * Verify file integrity
 *
 * @return array|true True if OK, array of modified files otherwise
 */
function skydonate_verify_integrity() {
    return skydonate_security()->verify_file_integrity();
}

/**
 * Check rate limit
 *
 * @param string $action Action name
 * @param int    $limit  Max requests
 * @param int    $window Time window
 * @return bool True if allowed
 */
function skydonate_check_rate_limit( $action, $limit = 10, $window = 60 ) {
    return skydonate_security()->check_rate_limit( $action, $limit, $window );
}
