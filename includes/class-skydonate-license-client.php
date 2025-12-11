<?php
/**
 * SkyDonate License Client
 *
 * Handles license validation, activation, updates, and feature checks
 *
 * @package SkyDonate
 * @version 2.0.0
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
     * Cache duration (12 hours)
     */
    private $cache_duration = 43200;

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

        // Defer hook initialization to 'init' action to comply with WordPress 6.7+ translation timing requirements
        add_action( 'init', array( $this, 'init_hooks' ), 0 );
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
     *
     * Called on 'init' action to comply with WordPress 6.7+ translation timing requirements.
     * This ensures translations are loaded before any translation-dependent hooks fire.
     */
    public function init_hooks() {
        // Prevent double initialization
        static $initialized = false;
        if ( $initialized ) {
            return;
        }
        $initialized = true;

        // Schedule daily license check
        $this->schedule_license_check();
        add_action( 'skydonate_daily_license_check', array( $this, 'daily_license_check' ) );

        // Admin notices
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Clear cache on plugin update
        add_action( 'upgrader_process_complete', array( $this, 'on_plugin_update' ), 10, 2 );

        // Site health integration
        add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );

        // Handle deactivation cleanup (must be registered early, not translation-dependent)
        register_deactivation_hook( SKYDONATE_FILE ?? __FILE__, array( $this, 'on_deactivation' ) );
    }

    /**
     * Schedule daily license check
     */
    public function schedule_license_check() {
        if ( ! wp_next_scheduled( 'skydonate_daily_license_check' ) ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'skydonate_daily_license_check' );
        }
    }

    /**
     * Daily license check callback
     */
    public function daily_license_check() {
        if ( $this->get_key() ) {
            $this->validate( null, true );
        }
    }

    /**
     * Plugin deactivation cleanup
     */
    public function on_deactivation() {
        wp_clear_scheduled_hook( 'skydonate_daily_license_check' );
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
        // Check rate limiting
        if ( $this->is_rate_limited() ) {
            return [
                'success' => false,
                'status'  => 'rate_limited',
                'message' => __( 'Too many requests. Please try again later.', 'skydonate' )
            ];
        }

        $url = trailingslashit( $this->server_url ) . '?sky_license_' . sanitize_key( $endpoint ) . '=1';

        $args = [
            'method'      => 'POST',
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'sslverify'   => true,
            'headers'     => [
                'Content-Type'    => 'application/json; charset=utf-8',
                'Accept'          => 'application/json',
                'User-Agent'      => 'SkyDonate/' . $this->plugin_version . ' WordPress/' . get_bloginfo( 'version' ),
                'X-Site-URL'      => home_url(),
                'X-Client-IP'     => $this->get_client_ip(),
            ],
            'body'        => wp_json_encode( $data ),
            'data_format' => 'body',
        ];

        // Allow filtering request args
        $args = apply_filters( 'skydonate_license_request_args', $args, $endpoint, $data );

        $this->log( "API Request to {$endpoint}: " . wp_json_encode( $data ) );

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

            return [
                'success' => false,
                'status'  => 'connection_error',
                'message' => __( 'Unable to connect to license server. Please check your internet connection.', 'skydonate' )
            ];
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        $this->log( "API Response [{$status_code}]: {$body}" );

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
                'message' => sprintf( __( 'Server error (%d). Please try again later.', 'skydonate' ), $status_code )
            ];
        }

        // Parse JSON response
        $json = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->log( 'JSON Parse Error: ' . json_last_error_msg() . ' | Body: ' . substr( $body, 0, 500 ) );
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

