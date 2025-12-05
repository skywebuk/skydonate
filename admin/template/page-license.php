<?php
/**
 * SkyDonate License Admin Page Template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$info = SkyDonate_License_Admin::get_info();
$is_valid = $info['is_valid'];
$nonce = wp_create_nonce( 'skydonate_license_nonce' );

// Count enabled items
$enabled_features = ! empty( $info['features'] ) ? count( array_filter( $info['features'] ) ) : 0;
$total_features = ! empty( $info['features'] ) ? count( $info['features'] ) : 0;
$enabled_widgets = ! empty( $info['widgets'] ) ? count( array_filter( $info['widgets'] ) ) : 0;
$total_widgets = ! empty( $info['widgets'] ) ? count( $info['widgets'] ) : 0;

// Format expiry date
$expires_formatted = '';
$expires_days_left = 0;
$is_expiring_soon = false;
if ( ! empty( $info['expires'] ) ) {
    $expires_timestamp = strtotime( $info['expires'] );
    $expires_formatted = date_i18n( get_option( 'date_format' ), $expires_timestamp );
    $expires_days_left = max( 0, ceil( ( $expires_timestamp - time() ) / DAY_IN_SECONDS ) );
    $is_expiring_soon = $expires_days_left <= 30 && $expires_days_left > 0;
}

// Version info
$current_version = $info['current_version'] ?? '1.0.0';
$latest_version = $info['latest_version'] ?? $current_version;
$update_available = $info['update_available'] ?? false;
?>

<div class="skydonate-license-page <?php echo ! $is_valid ? 'license-inactive' : ''; ?>">

    <!-- Toaster Container -->
    <div id="skydonate-toaster" role="alert" aria-live="polite"></div>

    <?php if ( ! $is_valid ) : ?>
        <!-- Activation Form -->
        <div class="license-activation-wrapper">
            <div class="license-activation-card">
                <div class="license-card-icon">
                    <span class="dashicons dashicons-shield"></span>
                </div>

                <h1><?php esc_html_e( 'Activate SkyDonate', 'skydonate' ); ?></h1>
                <p class="license-subtitle"><?php esc_html_e( 'Enter your license key to unlock all features', 'skydonate' ); ?></p>

                <form id="skydonate-activate-form" class="license-form" autocomplete="off">
                    <div class="license-input-group">
                        <label for="license_key" class="screen-reader-text"><?php esc_html_e( 'License Key', 'skydonate' ); ?></label>
                        <input
                            type="text"
                            name="license_key"
                            id="license_key"
                            placeholder="SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX"
                            value="<?php echo esc_attr( $info['key'] ); ?>"
                            autocomplete="off"
                            spellcheck="false"
                            required
                            pattern="SKY-[A-Za-z0-9]{8}-[A-Za-z0-9]{8}-[A-Za-z0-9]{8}-[A-Za-z0-9]{8}"
                            title="<?php esc_attr_e( 'Format: SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX', 'skydonate' ); ?>"
                        />
                        <span class="license-input-icon">
                            <span class="dashicons dashicons-admin-network"></span>
                        </span>
                    </div>

                    <button type="submit" class="license-btn license-btn-primary" id="activate-btn">
                        <span class="btn-icon"><span class="dashicons dashicons-yes"></span></span>
                        <span class="btn-text"><?php esc_html_e( 'Activate License', 'skydonate' ); ?></span>
                        <span class="btn-loading">
                            <span class="spinner-icon"></span>
                            <?php esc_html_e( 'Activating...', 'skydonate' ); ?>
                        </span>
                    </button>

                    <?php if ( $info['key'] && ! $is_valid && ! empty( $info['message'] ) ) : ?>
                        <div class="license-error-msg" role="alert">
                            <span class="dashicons dashicons-warning"></span>
                            <span><?php echo esc_html( $info['message'] ); ?></span>
                        </div>
                    <?php endif; ?>
                </form>

                <div class="license-card-footer">
                    <a href="https://skydonate.com/pricing" target="_blank" rel="noopener">
                        <span class="dashicons dashicons-cart"></span>
                        <?php esc_html_e( 'Get a license', 'skydonate' ); ?>
                    </a>
                    <span class="separator">|</span>
                    <a href="https://skydonate.com/support" target="_blank" rel="noopener">
                        <span class="dashicons dashicons-editor-help"></span>
                        <?php esc_html_e( 'Need help?', 'skydonate' ); ?>
                    </a>
                </div>
            </div>
        </div>

    <?php else : ?>
        <!-- License Dashboard -->
        <div class="license-dashboard">

            <!-- Header -->
            <div class="license-header">
                <div class="license-header-content">
                    <h1><?php esc_html_e( 'License Management', 'skydonate' ); ?></h1>
                    <p><?php esc_html_e( 'Manage your SkyDonate license and view enabled features', 'skydonate' ); ?></p>
                </div>
                <div class="license-header-icon">
                    <span class="dashicons dashicons-shield"></span>
                </div>
            </div>

            <!-- Status Card -->
            <div class="license-status-card <?php echo $is_expiring_soon ? 'expiring-soon' : ''; ?>">
                <div class="license-status-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="license-status-content">
                    <?php echo SkyDonate_License_Admin::get_badge( $info['status'] ); ?>
                    <h2><?php esc_html_e( 'Your license is active', 'skydonate' ); ?></h2>
                    
                    <?php if ( $info['masked_key'] ) : ?>
                        <p class="license-key-display">
                            <strong><?php esc_html_e( 'Key:', 'skydonate' ); ?></strong>
                            <code><?php echo esc_html( $info['masked_key'] ); ?></code>
                            <button type="button" class="copy-key-btn" data-key="<?php echo esc_attr( $info['key'] ); ?>" title="<?php esc_attr_e( 'Copy full key', 'skydonate' ); ?>">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ( $expires_formatted ) : ?>
                        <p class="license-expires <?php echo $is_expiring_soon ? 'warning' : ''; ?>">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php 
                            if ( $is_expiring_soon ) {
                                printf(
                                    /* translators: %1$s: expiry date, %2$d: days remaining */
                                    esc_html__( 'Expires: %1$s (%2$d days left)', 'skydonate' ),
                                    esc_html( $expires_formatted ),
                                    $expires_days_left
                                );
                            } else {
                                printf(
                                    /* translators: %s: expiry date */
                                    esc_html__( 'Expires: %s', 'skydonate' ),
                                    esc_html( $expires_formatted )
                                );
                            }
                            ?>
                        </p>
                        <?php if ( $is_expiring_soon ) : ?>
                            <a href="https://skydonate.com/renew" target="_blank" rel="noopener" class="renew-link">
                                <?php esc_html_e( 'Renew now', 'skydonate' ); ?> →
                            </a>
                        <?php endif; ?>
                    <?php else : ?>
                        <p class="license-expires lifetime">
                            <span class="dashicons dashicons-awards"></span>
                            <?php esc_html_e( 'Lifetime License', 'skydonate' ); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="license-status-actions">
                    <button type="button" class="button button-secondary" id="refresh-btn" title="<?php esc_attr_e( 'Refresh license data', 'skydonate' ); ?>">
                        <span class="dashicons dashicons-update"></span>
                        <span class="btn-text"><?php esc_html_e( 'Refresh', 'skydonate' ); ?></span>
                    </button>
                    <button type="button" class="button button-link-delete" id="deactivate-btn" title="<?php esc_attr_e( 'Deactivate license', 'skydonate' ); ?>">
                        <span class="dashicons dashicons-no"></span>
                        <span class="btn-text"><?php esc_html_e( 'Deactivate', 'skydonate' ); ?></span>
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="license-quick-stats">
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html( $enabled_features ); ?>/<?php echo esc_html( $total_features ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Features', 'skydonate' ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html( $enabled_widgets ); ?>/<?php echo esc_html( $total_widgets ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Widgets', 'skydonate' ); ?></span>
                </div>
                <?php if ( ! empty( $info['layouts'] ) ) : ?>
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html( count( $info['layouts'] ) ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Layouts', 'skydonate' ); ?></span>
                </div>
                <?php endif; ?>
                <div class="stat-item <?php echo $update_available ? 'has-update' : ''; ?>">
                    <span class="stat-value">
                        <?php echo esc_html( $current_version ); ?>
                        <?php if ( $update_available ) : ?>
                            <span class="dashicons dashicons-arrow-right-alt" style="font-size:16px;width:16px;height:16px;color:var(--sky-success);"></span>
                            <span style="color:var(--sky-success);"><?php echo esc_html( $latest_version ); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="stat-label">
                        <?php if ( $update_available ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" style="color:var(--sky-success);text-decoration:none;">
                                <?php esc_html_e( 'Update Available', 'skydonate' ); ?>
                            </a>
                        <?php else : ?>
                            <?php esc_html_e( 'Version', 'skydonate' ); ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- Features Section -->
            <?php if ( ! empty( $info['features'] ) ) : ?>
            <div class="license-section">
                <div class="license-section-header">
                    <h3>
                        <span class="dashicons dashicons-admin-plugins"></span>
                        <?php esc_html_e( 'Features', 'skydonate' ); ?>
                    </h3>
                    <span class="license-count">
                        <?php 
                        printf(
                            /* translators: %d: number of enabled features */
                            esc_html__( '%d enabled', 'skydonate' ),
                            $enabled_features
                        ); 
                        ?>
                    </span>
                </div>
                <div class="license-grid license-features-grid">
                    <?php foreach ( $info['features'] as $key => $enabled ) : ?>
                        <div class="license-item <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                            <span class="item-icon">
                                <span class="dashicons <?php echo $enabled ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
                            </span>
                            <span class="item-name"><?php echo esc_html( SkyDonate_License_Admin::format_name( $key ) ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Widgets Section -->
            <?php if ( ! empty( $info['widgets'] ) ) : ?>
            <div class="license-section">
                <div class="license-section-header">
                    <h3>
                        <span class="dashicons dashicons-screenoptions"></span>
                        <?php esc_html_e( 'Elementor Widgets', 'skydonate' ); ?>
                    </h3>
                    <span class="license-count">
                        <?php 
                        printf(
                            /* translators: %d: number of enabled widgets */
                            esc_html__( '%d enabled', 'skydonate' ),
                            $enabled_widgets
                        ); 
                        ?>
                    </span>
                </div>
                <div class="license-grid license-widgets-grid">
                    <?php foreach ( $info['widgets'] as $key => $enabled ) : ?>
                        <div class="license-widget-item <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                            <span class="widget-icon">
                                <span class="dashicons dashicons-welcome-widgets-menus"></span>
                            </span>
                            <div class="widget-info">
                                <span class="widget-name"><?php echo esc_html( SkyDonate_License_Admin::format_name( $key ) ); ?></span>
                                <span class="widget-status">
                                    <?php echo $enabled ? esc_html__( 'Enabled', 'skydonate' ) : esc_html__( 'Disabled', 'skydonate' ); ?>
                                </span>
                            </div>
                            <span class="widget-status-icon">
                                <span class="dashicons <?php echo $enabled ? 'dashicons-yes-alt' : 'dashicons-lock'; ?>"></span>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Layouts Section -->
            <?php if ( ! empty( $info['layouts'] ) ) : ?>
            <div class="license-section">
                <div class="license-section-header">
                    <h3>
                        <span class="dashicons dashicons-layout"></span>
                        <?php esc_html_e( 'Layouts', 'skydonate' ); ?>
                    </h3>
                </div>
                <div class="license-grid license-layouts-grid">
                    <?php foreach ( $info['layouts'] as $key => $layout ) : ?>
                        <div class="license-layout-item">
                            <span class="layout-icon">
                                <span class="dashicons dashicons-grid-view"></span>
                            </span>
                            <div class="layout-info">
                                <span class="layout-name"><?php echo esc_html( SkyDonate_License_Admin::format_name( $key ) ); ?></span>
                                <span class="layout-value"><?php echo esc_html( ucfirst( str_replace( array( '-', '_' ), ' ', $layout ) ) ); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Capabilities Section -->
            <?php if ( ! empty( $info['capabilities'] ) ) : ?>
            <div class="license-section">
                <div class="license-section-header">
                    <h3>
                        <span class="dashicons dashicons-admin-network"></span>
                        <?php esc_html_e( 'Capabilities', 'skydonate' ); ?>
                    </h3>
                </div>
                <div class="license-capabilities-list">
                    <?php foreach ( $info['capabilities'] as $key => $enabled ) : ?>
                        <div class="license-capability <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                            <span class="capability-toggle <?php echo $enabled ? 'on' : 'off'; ?>" aria-hidden="true">
                                <span class="toggle-track">
                                    <span class="toggle-thumb"></span>
                                </span>
                            </span>
                            <span class="capability-name"><?php echo esc_html( SkyDonate_License_Admin::format_name( $key ) ); ?></span>
                            <span class="capability-status">
                                <?php echo $enabled ? esc_html__( 'On', 'skydonate' ) : esc_html__( 'Off', 'skydonate' ); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="license-footer">
                <p>
                    <?php esc_html_e( 'Need help?', 'skydonate' ); ?>
                    <a href="https://skydonate.com/docs" target="_blank" rel="noopener"><?php esc_html_e( 'Documentation', 'skydonate' ); ?></a>
                    |
                    <a href="https://skydonate.com/support" target="_blank" rel="noopener"><?php esc_html_e( 'Support', 'skydonate' ); ?></a>
                </p>
            </div>

        </div>
    <?php endif; ?>

</div>

<style>
/* ===================================
   SkyDonate License Page Styles
   =================================== */

/* CSS Variables */
.skydonate-license-page {
    --sky-primary: #3442ad;
    --sky-primary-dark: #2a3590;
    --sky-primary-light: #4a5bc4;
    --sky-success: #10b981;
    --sky-success-dark: #059669;
    --sky-warning: #f59e0b;
    --sky-warning-dark: #d97706;
    --sky-danger: #ef4444;
    --sky-danger-dark: #dc2626;
    --sky-gray-50: #f9fafb;
    --sky-gray-100: #f3f4f6;
    --sky-gray-200: #e5e7eb;
    --sky-gray-300: #d1d5db;
    --sky-gray-400: #9ca3af;
    --sky-gray-500: #6b7280;
    --sky-gray-600: #4b5563;
    --sky-gray-700: #374151;
    --sky-gray-800: #1f2937;
    --sky-gray-900: #111827;
    --sky-radius: 8px;
    --sky-radius-lg: 12px;
    --sky-shadow: 0 1px 3px rgba(0,0,0,0.1);
    --sky-shadow-md: 0 4px 6px rgba(0,0,0,0.1);
    --sky-shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
}

/* Toaster */
#skydonate-toaster {
    position: fixed;
    top: 50px;
    right: 20px;
    z-index: 999999;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.skydonate-toast {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    border-radius: var(--sky-radius);
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    box-shadow: var(--sky-shadow-lg);
    animation: toastSlideIn 0.3s ease;
    min-width: 300px;
    max-width: 400px;
}

.skydonate-toast.success { background: linear-gradient(135deg, var(--sky-success), var(--sky-success-dark)); }
.skydonate-toast.error { background: linear-gradient(135deg, var(--sky-danger), var(--sky-danger-dark)); }
.skydonate-toast.info { background: linear-gradient(135deg, var(--sky-primary), var(--sky-primary-dark)); }
.skydonate-toast.warning { background: linear-gradient(135deg, var(--sky-warning), var(--sky-warning-dark)); }

.skydonate-toast .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.skydonate-toast.hiding { animation: toastSlideOut 0.3s ease forwards; }

@keyframes toastSlideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes toastSlideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* Activation Page */
.license-activation-wrapper {
    min-height: calc(100vh - 150px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, var(--sky-gray-100) 0%, var(--sky-gray-50) 100%);
    margin: -20px -20px 0 -20px;
}

.license-activation-card {
    background: #fff;
    border-radius: var(--sky-radius-lg);
    box-shadow: var(--sky-shadow-lg);
    padding: 48px;
    max-width: 480px;
    width: 100%;
    text-align: center;
}

.license-card-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--sky-primary), var(--sky-primary-dark));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    box-shadow: 0 8px 24px rgba(52, 66, 173, 0.3);
}

.license-card-icon .dashicons {
    font-size: 36px;
    width: 36px;
    height: 36px;
    color: #fff;
}

.license-activation-card h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--sky-gray-900);
    margin: 0 0 8px;
}

.license-subtitle {
    color: var(--sky-gray-500);
    font-size: 16px;
    margin: 0 0 32px;
}

.license-input-group {
    position: relative;
    margin-bottom: 20px;
}

.license-input-group input {
    width: 100%;
    padding: 16px 20px 16px 48px;
    font-size: 15px;
    border: 2px solid var(--sky-gray-200);
    border-radius: var(--sky-radius);
    transition: all 0.2s;
    font-family: 'SF Mono', 'Consolas', monospace;
    text-transform: uppercase;
}

.license-input-group input:focus {
    outline: none;
    border-color: var(--sky-primary);
    box-shadow: 0 0 0 3px rgba(52, 66, 173, 0.1);
}

.license-input-group input::placeholder {
    text-transform: none;
    font-family: inherit;
    color: var(--sky-gray-400);
}

.license-input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sky-gray-400);
}

.license-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 16px 24px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: var(--sky-radius);
    cursor: pointer;
    transition: all 0.2s;
}

.license-btn-primary {
    background: linear-gradient(135deg, var(--sky-primary), var(--sky-primary-dark));
    color: #fff;
    box-shadow: 0 4px 14px rgba(52, 66, 173, 0.4);
}

.license-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52, 66, 173, 0.5);
}

.license-btn .btn-loading { display: none; }
.license-btn.loading .btn-text,
.license-btn.loading .btn-icon { display: none; }
.license-btn.loading .btn-loading { 
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.license-btn.loading { opacity: 0.8; pointer-events: none; }

.spinner-icon {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.license-error-msg {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    padding: 12px 16px;
    background: rgba(239, 68, 68, 0.1);
    border-radius: var(--sky-radius);
    color: var(--sky-danger);
    font-size: 14px;
    text-align: left;
}

.license-error-msg .dashicons {
    flex-shrink: 0;
}

.license-card-footer {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--sky-gray-200);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    font-size: 14px;
}

.license-card-footer a {
    color: var(--sky-primary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.license-card-footer a:hover { text-decoration: underline; }
.license-card-footer .separator { color: var(--sky-gray-300); }
.license-card-footer .dashicons { font-size: 16px; width: 16px; height: 16px; }

/* Dashboard */
.license-dashboard {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px 0;
}

.license-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.license-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--sky-gray-900);
    margin: 0 0 4px;
}

.license-header p {
    color: var(--sky-gray-500);
    margin: 0;
}

.license-header-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--sky-primary), var(--sky-primary-dark));
    border-radius: var(--sky-radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(52, 66, 173, 0.3);
}

.license-header-icon .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
    color: #fff;
}

/* Status Card */
.license-status-card {
    background: #fff;
    border-radius: var(--sky-radius-lg);
    padding: 28px;
    display: flex;
    gap: 24px;
    align-items: flex-start;
    box-shadow: var(--sky-shadow);
    border: 1px solid var(--sky-gray-200);
    margin-bottom: 24px;
}

.license-status-card.expiring-soon {
    border-color: var(--sky-warning);
    background: linear-gradient(135deg, #fff 0%, rgba(245, 158, 11, 0.05) 100%);
}

.license-status-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--sky-success), var(--sky-success-dark));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.license-status-icon .dashicons {
    font-size: 28px;
    width: 28px;
    height: 28px;
    color: #fff;
}

.license-status-content {
    flex: 1;
}

.license-status-content h2 {
    font-size: 20px;
    font-weight: 600;
    color: var(--sky-gray-900);
    margin: 8px 0 12px;
}

.license-key-display {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 8px;
    font-size: 14px;
    color: var(--sky-gray-600);
}

.license-key-display code {
    background: var(--sky-gray-100);
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
}

.copy-key-btn {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: var(--sky-gray-400);
    transition: color 0.2s;
}

.copy-key-btn:hover { color: var(--sky-primary); }

.license-expires {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
    font-size: 14px;
    color: var(--sky-gray-500);
}

.license-expires.warning { color: var(--sky-warning-dark); font-weight: 500; }
.license-expires.lifetime { color: var(--sky-success); font-weight: 500; }

.renew-link {
    display: inline-block;
    margin-top: 8px;
    color: var(--sky-warning-dark);
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
}

.renew-link:hover { text-decoration: underline; }

.license-status-actions {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}

.license-status-actions .button {
    display: inline-flex;
    align-items: center;
    gap: var(--sky-space-2);
    padding: var(--sky-space-2) var(--sky-space-4);
    font-size: var(--sky-font-size-sm);
    font-weight: 500;
    border-radius: var(--sky-radius-lg);
    cursor: pointer;
    transition: var(--sky-transition);
    text-decoration: none;
    line-height: 1.4;
}

.license-status-actions .button .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.license-status-actions .button.loading .dashicons { animation: spin 0.8s linear infinite; }
.license-status-actions .button.loading { opacity: 0.7; pointer-events: none; }


.license-status-actions .button.button-secondary {
    background: #fff;
    border: 1px solid var(--sky-gray-300);
    color: var(--sky-gray-700);
}

.license-status-actions .button.button-secondary:hover {
    background: var(--sky-gray-50);
    border-color: var(--sky-gray-400);
    color: var(--sky-gray-900);
}

.license-status-actions .button.button-secondary:focus {
    outline: none;
    box-shadow: 0 0 0 3px var(--sky-primary-light);
    border-color: var(--sky-primary);
}

/* Delete/Danger Button (Deactivate) */
.license-status-actions .button.button-link-delete {
    background: var(--sky-error-light);
    border: 1px solid transparent;
    color: var(--sky-error);
}

.license-status-actions .button.button-link-delete:hover {
    background: var(--sky-error);
    color: #fff;
}

.license-status-actions .button.button-link-delete:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
}


/* Quick Stats */
.license-quick-stats {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
}

.license-quick-stats .stat-item {
    flex: 1;
    background: #fff;
    border: 1px solid var(--sky-gray-200);
    border-radius: var(--sky-radius);
    padding: 20px;
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: var(--sky-primary);
    line-height: 1;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 13px;
    color: var(--sky-gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Sections */
.license-section {
    background: #fff;
    border: 1px solid var(--sky-gray-200);
    border-radius: var(--sky-radius-lg);
    padding: 24px;
    margin-bottom: 20px;
}

.license-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--sky-gray-100);
}

.license-section-header h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 600;
    color: var(--sky-gray-900);
    margin: 0;
}

.license-section-header h3 .dashicons {
    color: var(--sky-primary);
}

.license-count {
    font-size: 13px;
    color: var(--sky-gray-500);
    background: var(--sky-gray-100);
    padding: 4px 12px;
    border-radius: 9999px;
}

/* Features Grid */
.license-grid {
    display: grid;
    gap: 12px;
}

.license-features-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
}

.license-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: var(--sky-gray-50);
    border-radius: var(--sky-radius);
    transition: all 0.2s;
}

.license-item.enabled {
    background: rgba(16, 185, 129, 0.1);
}

.license-item.disabled {
    opacity: 0.6;
}

.license-item .item-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.license-item.enabled .item-icon .dashicons { color: var(--sky-success); }
.license-item.disabled .item-icon .dashicons { color: var(--sky-gray-400); }

.license-item .item-name {
    font-size: 14px;
    color: var(--sky-gray-700);
}

/* Widgets Grid */
.license-widgets-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}

.license-widget-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: var(--sky-gray-50);
    border-radius: var(--sky-radius);
    transition: all 0.2s;
}

.license-widget-item.enabled {
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.license-widget-item.disabled {
    opacity: 0.6;
}

.license-widget-item .widget-icon {
    width: 40px;
    height: 40px;
    background: var(--sky-gray-200);
    border-radius: var(--sky-radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.license-widget-item.enabled .widget-icon {
    background: var(--sky-success);
}

.license-widget-item .widget-icon .dashicons {
    color: var(--sky-gray-500);
}

.license-widget-item.enabled .widget-icon .dashicons {
    color: #fff;
}

.license-widget-item .widget-info {
    flex: 1;
}

.license-widget-item .widget-name {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: var(--sky-gray-800);
}

.license-widget-item .widget-status {
    font-size: 12px;
    color: var(--sky-gray-500);
}

.license-widget-item.enabled .widget-status {
    color: var(--sky-success);
}

.license-widget-item .widget-status-icon .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}

.license-widget-item.enabled .widget-status-icon .dashicons {
    color: var(--sky-success);
}

.license-widget-item.disabled .widget-status-icon .dashicons {
    color: var(--sky-gray-400);
}

/* Layouts Grid */
.license-layouts-grid {
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
}

.license-layout-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--sky-gray-50);
    border-radius: var(--sky-radius);
}

.license-layout-item .layout-icon {
    width: 36px;
    height: 36px;
    background: var(--sky-primary);
    border-radius: var(--sky-radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.license-layout-item .layout-icon .dashicons {
    color: #fff;
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.license-layout-item .layout-info {
    flex: 1;
}

.license-layout-item .layout-name {
    display: block;
    font-size: 13px;
    color: var(--sky-gray-500);
}

.license-layout-item .layout-value {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--sky-gray-800);
}

/* Capabilities */
/* Capabilities List */
.license-capabilities-list {
    display: flex;
    flex-direction: column;
    gap: var(--sky-space-3);
}

/* Capability Item */
.license-capability {
    display: flex;
    align-items: center;
    gap: var(--sky-space-4);
    padding: var(--sky-space-4) var(--sky-space-5);
    background: var(--sky-gray-50);
    border-radius: var(--sky-radius-lg);
    transition: var(--sky-transition);
}

.license-capability:hover {
    background: var(--sky-gray-100);
}

.license-capability.enabled {
    background: rgba(16, 185, 129, 0.06);
}

.license-capability.enabled:hover {
    background: rgba(16, 185, 129, 0.1);
}

.license-capability.disabled {
    background: var(--sky-gray-50);
}

/* Toggle Switch */
.capability-toggle {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
}

.capability-toggle .toggle-track {
    display: block;
    width: 44px;
    height: 24px;
    background: var(--sky-gray-300);
    border-radius: var(--sky-radius-full);
    position: relative;
    transition: var(--sky-transition);
    cursor: default;
}

.capability-toggle.on .toggle-track {
    background: var(--sky-success);
}

.capability-toggle.off .toggle-track {
    background: var(--sky-gray-300);
}

.capability-toggle .toggle-thumb {
    position: absolute;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: var(--sky-radius-full);
    top: 3px;
    left: 3px;
    transition: var(--sky-transition);
    box-shadow: var(--sky-shadow-sm), 0 1px 2px rgba(0, 0, 0, 0.1);
}

.capability-toggle.on .toggle-thumb {
    transform: translateX(20px);
}

/* Capability Name */
.capability-name {
    flex: 1;
    font-size: var(--sky-font-size-sm);
    font-weight: 500;
    color: var(--sky-gray-700);
}

.license-capability.enabled .capability-name {
    color: var(--sky-gray-800);
}

.license-capability.disabled .capability-name {
    color: var(--sky-gray-500);
}

/* Capability Status */
.capability-status {
    font-size: var(--sky-font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: var(--sky-space-1) var(--sky-space-3);
    border-radius: var(--sky-radius-full);
    min-width: 44px;
    text-align: center;
}

.license-capability.enabled .capability-status {
    color: var(--sky-success);
    background: var(--sky-success-light);
}

.license-capability.disabled .capability-status {
    color: var(--sky-gray-500);
    background: var(--sky-gray-200);
}

/* Responsive */
@media (max-width: 480px) {
    .license-capability {
        padding: var(--sky-space-3) var(--sky-space-4);
        gap: var(--sky-space-3);
    }
    
    .capability-toggle .toggle-track {
        width: 38px;
        height: 20px;
    }
    
    .capability-toggle .toggle-thumb {
        width: 14px;
        height: 14px;
    }
    
    .capability-toggle.on .toggle-thumb {
        transform: translateX(18px);
    }
    
    .capability-name {
        font-size: var(--sky-font-size-xs);
    }
    
    .capability-status {
        font-size: 10px;
        padding: 2px var(--sky-space-2);
    }
}


/* Badges */
.skydonate-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.skydonate-badge--valid { background: rgba(16, 185, 129, 0.1); color: var(--sky-success-dark); }
.skydonate-badge--expired { background: rgba(239, 68, 68, 0.1); color: var(--sky-danger-dark); }
.skydonate-badge--inactive { background: var(--sky-gray-100); color: var(--sky-gray-600); }
.skydonate-badge--invalid { background: rgba(239, 68, 68, 0.1); color: var(--sky-danger-dark); }
.skydonate-badge--error { background: rgba(239, 68, 68, 0.1); color: var(--sky-danger-dark); }
.skydonate-badge--warning { background: rgba(245, 158, 11, 0.1); color: var(--sky-warning-dark); }

/* Footer */
.license-footer {
    text-align: center;
    padding-top: 24px;
    margin-top: 24px;
    border-top: 1px solid var(--sky-gray-200);
}

.license-footer p {
    margin: 0;
    color: var(--sky-gray-500);
    font-size: 14px;
}

.license-footer a {
    color: var(--sky-primary);
    text-decoration: none;
}

.license-footer a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
    .license-activation-card { padding: 32px 24px; }
    
    .license-status-card {
        flex-direction: column;
        text-align: center;
    }
    
    .license-status-icon { margin: 0 auto; }
    
    .license-key-display {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .license-status-actions {
        width: 100%;
        justify-content: center;
    }
    
    .license-quick-stats {
        flex-direction: column;
    }
    
    .license-features-grid,
    .license-widgets-grid,
    .license-layouts-grid {
        grid-template-columns: 1fr;
    }
    
    .license-header {
        flex-direction: column;
        text-align: center;
        gap: 16px;
    }
}
</style>

<script>
jQuery(function($) {
    'use strict';

    var nonce = <?php echo wp_json_encode( $nonce ); ?>;
    var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

    // Toaster function
    function showToast(message, type) {
        type = type || 'info';
        var icons = {
            success: 'yes-alt',
            error: 'warning',
            info: 'info',
            warning: 'flag'
        };
        
        var $toast = $('<div class="skydonate-toast ' + type + '">' +
            '<span class="dashicons dashicons-' + icons[type] + '"></span>' +
            '<span>' + $('<div>').text(message).html() + '</span>' +
        '</div>');

        $('#skydonate-toaster').append($toast);

        setTimeout(function() {
            $toast.addClass('hiding');
            setTimeout(function() { $toast.remove(); }, 300);
        }, 4000);
    }

    // Copy license key
    $('.copy-key-btn').on('click', function() {
        var key = $(this).data('key');
        var $btn = $(this);
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(key).then(function() {
                showToast('<?php echo esc_js( __( 'License key copied to clipboard', 'skydonate' ) ); ?>', 'success');
                $btn.find('.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
                setTimeout(function() {
                    $btn.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-clipboard');
                }, 2000);
            });
        }
    });

    // Auto-format license key input
    $('#license_key').on('input', function() {
        var val = $(this).val().toUpperCase().replace(/[^A-Z0-9-]/g, '');
        $(this).val(val);
    });

    // Activate License
    $('#skydonate-activate-form').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#activate-btn');
        var key = $('#license_key').val().trim();

        if (!key) {
            showToast('<?php echo esc_js( __( 'Please enter a license key', 'skydonate' ) ); ?>', 'error');
            return;
        }

        // Validate format
        if (!/^SKY-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}$/.test(key)) {
            showToast('<?php echo esc_js( __( 'Invalid license key format', 'skydonate' ) ); ?>', 'error');
            return;
        }

        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'skydonate_activate_license',
                nonce: nonce,
                license_key: key
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.data.message, 'success');
                    if (response.data.reload) {
                        setTimeout(function() { location.reload(); }, 1500);
                    }
                } else {
                    showToast(response.data.message || '<?php echo esc_js( __( 'Activation failed', 'skydonate' ) ); ?>', 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                showToast('<?php echo esc_js( __( 'Connection error. Please try again.', 'skydonate' ) ); ?>', 'error');
                $btn.removeClass('loading').prop('disabled', false);
            }
        });
    });

    // Deactivate License
    $('#deactivate-btn').on('click', function() {
        if (!confirm('<?php echo esc_js( __( 'Are you sure you want to deactivate this license?', 'skydonate' ) ); ?>')) {
            return;
        }

        var $btn = $(this);
        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'skydonate_deactivate_license',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.data.message, 'info');
                    if (response.data.reload) {
                        setTimeout(function() { location.reload(); }, 1500);
                    }
                } else {
                    showToast(response.data.message || '<?php echo esc_js( __( 'Deactivation failed', 'skydonate' ) ); ?>', 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                }
            },
            error: function() {
                showToast('<?php echo esc_js( __( 'Connection error. Please try again.', 'skydonate' ) ); ?>', 'error');
                $btn.removeClass('loading').prop('disabled', false);
            }
        });
    });

    // Refresh License
    $('#refresh-btn').on('click', function() {
        var $btn = $(this);
        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'skydonate_refresh_license',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.data.message, 'success');
                    if (response.data.reload) {
                        setTimeout(function() { location.reload(); }, 1500);
                    }
                } else {
                    showToast(response.data.message || '<?php echo esc_js( __( 'Refresh failed', 'skydonate' ) ); ?>', 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                }
            },
            error: function() {
                showToast('<?php echo esc_js( __( 'Connection error. Please try again.', 'skydonate' ) ); ?>', 'error');
                $btn.removeClass('loading').prop('disabled', false);
            }
        });
    });
});
</script>
