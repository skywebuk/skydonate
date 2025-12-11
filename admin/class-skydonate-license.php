<?php
/**
 * SkyDonate License Admin
 *
 * Handles license page and AJAX submissions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_License_Admin {

    /**
     * Instance
     */
    private static $instance = null;

    /**
     * Get instance (singleton)
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
    public function __construct() {
        // AJAX handlers
        add_action( 'wp_ajax_skydonate_activate_license', array( $this, 'ajax_activate' ) );
        add_action( 'wp_ajax_skydonate_deactivate_license', array( $this, 'ajax_deactivate' ) );
        add_action( 'wp_ajax_skydonate_refresh_license', array( $this, 'ajax_refresh' ) );
        add_action( 'wp_ajax_skydonate_check_update', array( $this, 'ajax_check_update' ) );
        
        // Enqueue scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts( $hook ) {
        if ( strpos( $hook, 'skydonate-license' ) === false ) {
            return;
        }

        // Use correct constants
        $plugin_url = defined( 'SKYDONATE_URL' ) ? SKYDONATE_URL : plugin_dir_url( dirname( __FILE__ ) );
        $version = defined( 'SKYDONATE_VERSION' ) ? SKYDONATE_VERSION : '1.0.0';

        // Styles are inline in the template, so no external CSS needed
        // Scripts are inline in the template as well

        wp_localize_script( 'jquery', 'skydonateLicense', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'skydonate_license_nonce' ),
            'i18n'    => array(
                'activating'   => __( 'Activating...', 'skydonate' ),
                'deactivating' => __( 'Deactivating...', 'skydonate' ),
                'refreshing'   => __( 'Refreshing...', 'skydonate' ),
                'confirm_deactivate' => __( 'Are you sure you want to deactivate this license?', 'skydonate' ),
            ),
        ) );
    }

    /**
     * Render license page
     */
    public function render_page() {
        $info = self::get_info();
        ?>
        <div class="wrap skydonate-license-wrap">
            <h1><?php esc_html_e( 'SkyDonate License', 'skydonate' ); ?></h1>
            
            <div class="skydonate-license-card">
                <!-- License Status -->
                <div class="skydonate-license-status">
                    <h2><?php esc_html_e( 'License Status', 'skydonate' ); ?></h2>
                    <?php echo self::get_badge( $info['status'] ); ?>
                    
                    <?php if ( $info['is_valid'] && $info['expires'] ) : ?>
                        <p class="skydonate-expires">
                            <?php printf( 
                                esc_html__( 'Expires: %s', 'skydonate' ), 
                                esc_html( date_i18n( get_option( 'date_format' ), strtotime( $info['expires'] ) ) )
                            ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- License Form -->
                <div class="skydonate-license-form">
                    <?php if ( $info['is_valid'] ) : ?>
                        <!-- Active License Display -->
                        <div class="skydonate-license-active">
                            <label><?php esc_html_e( 'License Key', 'skydonate' ); ?></label>
                            <div class="skydonate-license-key">
                                <code><?php echo esc_html( $info['masked_key'] ); ?></code>
                            </div>
                            
                            <div class="skydonate-license-actions">
                                <button type="button" class="button" id="skydonate-refresh-license">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e( 'Refresh', 'skydonate' ); ?>
                                </button>
                                <button type="button" class="button button-link-delete" id="skydonate-deactivate-license">
                                    <?php esc_html_e( 'Deactivate', 'skydonate' ); ?>
                                </button>
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- Activation Form -->
                        <form id="skydonate-license-form">
                            <label for="skydonate-license-key"><?php esc_html_e( 'License Key', 'skydonate' ); ?></label>
                            <input 
                                type="text" 
                                id="skydonate-license-key" 
                                name="license_key" 
                                class="regular-text" 
                                placeholder="SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX"
                                value="<?php echo esc_attr( $info['key'] ); ?>"
                            />
                            
                            <?php if ( ! empty( $info['message'] ) && ! $info['is_valid'] ) : ?>
                                <p class="skydonate-error-message"><?php echo esc_html( $info['message'] ); ?></p>
                            <?php endif; ?>
                            
                            <button type="submit" class="button button-primary" id="skydonate-activate-license">
                                <?php esc_html_e( 'Activate License', 'skydonate' ); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Message Container -->
                <div id="skydonate-license-message" class="skydonate-message" style="display:none;"></div>
            </div>

            <?php if ( $info['is_valid'] ) : ?>
                <!-- Features & Capabilities -->
                <div class="skydonate-license-details">
                    <?php if ( ! empty( $info['features'] ) ) : ?>
                        <div class="skydonate-detail-section">
                            <h3><?php esc_html_e( 'Features', 'skydonate' ); ?></h3>
                            <ul class="skydonate-feature-list">
                                <?php foreach ( $info['features'] as $feature => $enabled ) : ?>
                                    <li class="<?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                                        <span class="dashicons <?php echo $enabled ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                                        <?php echo esc_html( self::format_name( $feature ) ); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $info['widgets'] ) ) : ?>
                        <div class="skydonate-detail-section">
                            <h3><?php esc_html_e( 'Widgets', 'skydonate' ); ?></h3>
                            <ul class="skydonate-feature-list">
                                <?php foreach ( $info['widgets'] as $widget => $enabled ) : ?>
                                    <li class="<?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                                        <span class="dashicons <?php echo $enabled ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                                        <?php echo esc_html( self::format_name( $widget ) ); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $info['capabilities'] ) ) : ?>
                        <div class="skydonate-detail-section">
                            <h3><?php esc_html_e( 'Capabilities', 'skydonate' ); ?></h3>
                            <ul class="skydonate-feature-list">
                                <?php foreach ( $info['capabilities'] as $cap => $enabled ) : ?>
                                    <li class="<?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                                        <span class="dashicons <?php echo $enabled ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>"></span>
                                        <?php echo esc_html( self::format_name( $cap ) ); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * AJAX: Activate license
     */
    public function ajax_activate() {
        // Verify nonce
        if ( ! check_ajax_referer( 'skydonate_license_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'skydonate' ) ) );
        }

        // Check capability
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized access', 'skydonate' ) ) );
        }

        // Sanitize input
        $key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

        if ( empty( $key ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a license key', 'skydonate' ) ) );
        }

        // Validate format
        $key = strtoupper( trim( $key ) );
        if ( ! preg_match( '/^SKY-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}$/', $key ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid license key format. Expected: SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX', 'skydonate' ) ) );
        }

        // Attempt activation
        $result = skydonate_license()->activate( $key );

        if ( ! empty( $result['success'] ) ) {
            wp_send_json_success( array(
                'message' => __( 'License activated successfully!', 'skydonate' ),
                'reload'  => true,
            ) );
        } else {
            $message = $result['message'] ?? __( 'Activation failed', 'skydonate' );
            
            // Provide more helpful messages based on status
            if ( isset( $result['status'] ) ) {
                switch ( $result['status'] ) {
                    case 'invalid':
                        $message = __( 'Invalid license key. Please check and try again.', 'skydonate' );
                        break;
                    case 'expired':
                        $message = __( 'This license has expired. Please renew to continue.', 'skydonate' );
                        break;
                    case 'domain_mismatch':
                        $message = __( 'This license is registered to a different domain.', 'skydonate' );
                        break;
                    case 'inactive':
                        $message = __( 'This license is inactive. Please contact support.', 'skydonate' );
                        break;
                    case 'rate_limited':
                        $message = __( 'Too many attempts. Please wait a moment and try again.', 'skydonate' );
                        break;
                }
            }

            wp_send_json_error( array( 'message' => $message ) );
        }
    }

    /**
     * AJAX: Deactivate license
     */
    public function ajax_deactivate() {
        // Verify nonce
        if ( ! check_ajax_referer( 'skydonate_license_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'skydonate' ) ) );
        }

        // Check capability
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized access', 'skydonate' ) ) );
        }

        // Deactivate
        $result = skydonate_license()->deactivate();

        if ( ! empty( $result['success'] ) ) {
            wp_send_json_success( array(
                'message' => __( 'License deactivated successfully', 'skydonate' ),
                'reload'  => true,
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $result['message'] ?? __( 'Deactivation failed', 'skydonate' ),
            ) );
        }
    }

    /**
     * AJAX: Refresh license
     */
    public function ajax_refresh() {
        // Verify nonce
        if ( ! check_ajax_referer( 'skydonate_license_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'skydonate' ) ) );
        }

        // Check capability
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized access', 'skydonate' ) ) );
        }

        // Clear all cached license data (transients AND backup option) for a complete refresh
        $license = skydonate_license();
        $license->clear_cache();
        delete_option( 'skydonate_license_data_backup' );

        // Force re-validate from server
        $result = $license->validate( null, true );

        if ( ! empty( $result['success'] ) ) {
            wp_send_json_success( array(
                'message' => __( 'License data refreshed successfully', 'skydonate' ),
                'reload'  => true,
                'data'    => array(
                    'status'  => $result['status'] ?? 'valid',
                    'expires' => $result['expires'] ?? '',
                ),
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $result['message'] ?? __( 'Failed to refresh license data', 'skydonate' ),
            ) );
        }
    }

    /**
     * AJAX: Check for updates
     */
    public function ajax_check_update() {
        // Verify nonce
        if ( ! check_ajax_referer( 'skydonate_license_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'skydonate' ) ) );
        }

        // Check capability
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized access', 'skydonate' ) ) );
        }

        $result = skydonate_license()->check_update();

        if ( ! empty( $result['success'] ) ) {
            wp_send_json_success( array(
                'update_available' => $result['update_available'] ?? false,
                'version'          => $result['version'] ?? '',
                'changelog'        => $result['changelog'] ?? '',
                'download_url'     => $result['download_url'] ?? '',
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $result['message'] ?? __( 'Failed to check for updates', 'skydonate' ),
            ) );
        }
    }

    /**
     * Get license info for template
     */
    public static function get_info() {
        $license = skydonate_license();
        $key = $license->get_key();
        $data = $key ? $license->get_data() : null;
        $status = $data['status'] ?? 'inactive';
        $is_valid = ! empty( $data['success'] ) && $status === 'valid';

        // Mask key for display (show first 8 and last 4 characters)
        $masked = '';
        if ( $key ) {
            $masked = substr( $key, 0, 12 ) . str_repeat( '*', max( 0, strlen( $key ) - 16 ) ) . substr( $key, -4 );
        }

        // Get update info if available
        $update_available = false;
        $latest_version = '';
        $current_version = defined( 'SKYDONATE_VERSION' ) ? SKYDONATE_VERSION : '1.0.0';
        if ( function_exists( 'skydonate_updater' ) && $is_valid ) {
            $updater = skydonate_updater();
            $latest_version = $updater->get_available_version();

            // Show update notification based on version comparison, regardless of allow_auto_updates capability
            // The capability only affects whether they can auto-update, not whether they see the notification
            if ( ! empty( $latest_version ) && version_compare( $current_version, $latest_version, '<' ) ) {
                $update_available = true;
            }
        }

        // Get plugin info from license data
        $plugin_info = $data['plugin_info'] ?? array();

        return array(
            'key'              => $key,
            'masked_key'       => $masked,
            'status'           => $status,
            'is_valid'         => $is_valid,
            'data'             => $data,
            'features'         => $data['features'] ?? array(),
            'widgets'          => $data['widgets'] ?? array(),
            'layouts'          => $data['layouts'] ?? array(),
            'capabilities'     => $data['capabilities'] ?? array(),
            'expires'          => $data['expires'] ?? '',
            'message'          => $data['message'] ?? '',
            'current_version'  => $current_version,
            'latest_version'   => $latest_version ?: $current_version,
            'update_available' => $update_available,
            'plugin_info'      => $plugin_info,
        );
    }

    /**
     * Get status badge HTML
     */
    public static function get_badge( $status ) {
        $badges = array(
            'valid'            => '<span class="skydonate-badge skydonate-badge--valid">' . esc_html__( 'Active', 'skydonate' ) . '</span>',
            'expired'          => '<span class="skydonate-badge skydonate-badge--expired">' . esc_html__( 'Expired', 'skydonate' ) . '</span>',
            'inactive'         => '<span class="skydonate-badge skydonate-badge--inactive">' . esc_html__( 'Inactive', 'skydonate' ) . '</span>',
            'invalid'          => '<span class="skydonate-badge skydonate-badge--invalid">' . esc_html__( 'Invalid', 'skydonate' ) . '</span>',
            'domain_mismatch'  => '<span class="skydonate-badge skydonate-badge--error">' . esc_html__( 'Domain Mismatch', 'skydonate' ) . '</span>',
            'domain_blocked'   => '<span class="skydonate-badge skydonate-badge--error">' . esc_html__( 'Domain Blocked', 'skydonate' ) . '</span>',
            'localhost_blocked'=> '<span class="skydonate-badge skydonate-badge--warning">' . esc_html__( 'Localhost Blocked', 'skydonate' ) . '</span>',
            'rate_limited'     => '<span class="skydonate-badge skydonate-badge--warning">' . esc_html__( 'Rate Limited', 'skydonate' ) . '</span>',
            'error'            => '<span class="skydonate-badge skydonate-badge--error">' . esc_html__( 'Error', 'skydonate' ) . '</span>',
        );
        
        return $badges[ $status ] ?? $badges['inactive'];
    }

    /**
     * Format feature/capability name for display
     */
    public static function format_name( $key ) {
        // Remove common prefixes
        $key = preg_replace( '/^(feature_|widget_|layout_|allow_|disable_)/', '', $key );
        // Convert to title case
        return ucwords( str_replace( '_', ' ', $key ) );
    }

    /**
     * Check if license page
     */
    public static function is_license_page() {
        return isset( $_GET['page'] ) && $_GET['page'] === 'skydonate-license';
    }
}

// Initialize
SkyDonate_License_Admin::instance();
