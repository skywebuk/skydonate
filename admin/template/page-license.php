<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$license_info = SkyDonate_License_Admin::get_license_info();
$has_license  = $license_info['has_license'];
$status       = $license_info['status'];
$data         = $license_info['data'];
$masked_key   = $license_info['masked_key'];

$features     = $data['features'] ?? array();
$widgets      = $data['widgets'] ?? array();
$layouts      = $data['layouts'] ?? array();
$capabilities = $data['capabilities'] ?? array();
$expires      = $data['expires'] ?? '';
?>

<div class="skydonate-license-page">

    <!-- Header -->
    <div class="license-header">
        <div class="license-header__content">
            <h1 class="license-header__title">License Management</h1>
            <p class="license-header__subtitle">Manage your SkyDonate license and view enabled features</p>
        </div>
        <div class="license-header__logo">
            <span class="dashicons dashicons-shield"></span>
        </div>
    </div>

    <!-- Status Card -->
    <div class="license-status-card <?php echo esc_attr( 'status--' . $status ); ?>">
        <div class="license-status-card__icon">
            <?php if ( $status === 'valid' ) : ?>
                <span class="dashicons dashicons-yes-alt"></span>
            <?php elseif ( $status === 'expired' ) : ?>
                <span class="dashicons dashicons-warning"></span>
            <?php else : ?>
                <span class="dashicons dashicons-dismiss"></span>
            <?php endif; ?>
        </div>
        <div class="license-status-card__content">
            <div class="license-status-card__status">
                <?php echo SkyDonate_License_Admin::get_status_badge( $status ); ?>
            </div>
            <h2 class="license-status-card__title">
                <?php if ( $status === 'valid' ) : ?>
                    Your license is active
                <?php elseif ( $status === 'expired' ) : ?>
                    Your license has expired
                <?php elseif ( $has_license ) : ?>
                    License validation failed
                <?php else : ?>
                    No license activated
                <?php endif; ?>
            </h2>
            <?php if ( $has_license && $masked_key ) : ?>
                <p class="license-status-card__key">
                    <span class="key-label">License Key:</span>
                    <code><?php echo esc_html( $masked_key ); ?></code>
                </p>
            <?php endif; ?>
            <?php if ( $expires && $status === 'valid' ) : ?>
                <p class="license-status-card__expires">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    Expires: <?php echo esc_html( date( 'F j, Y', strtotime( $expires ) ) ); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php if ( $has_license && $status === 'valid' ) : ?>
            <div class="license-status-card__actions">
                <button type="button" class="button button-secondary" id="skydonate-refresh-license">
                    <span class="dashicons dashicons-update"></span> Refresh
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- License Form -->
    <div class="license-form-card">
        <h3 class="license-form-card__title">
            <?php echo $has_license ? 'Update License' : 'Activate License'; ?>
        </h3>

        <form method="post" action="" class="license-form" id="skydonate-license-form">
            <?php wp_nonce_field( 'skydonate_license_action', 'skydonate_license_nonce' ); ?>

            <div class="license-form__input-group">
                <input
                    type="text"
                    name="skydonate_license_key"
                    id="skydonate_license_key"
                    class="license-form__input"
                    placeholder="Enter your license key"
                    value="<?php echo $has_license ? esc_attr( $license_info['key'] ) : ''; ?>"
                    autocomplete="off"
                />
                <div class="license-form__buttons">
                    <?php if ( $has_license ) : ?>
                        <button type="submit" name="skydonate_license_action" value="activate" class="button button-primary">
                            <span class="dashicons dashicons-update"></span> Update
                        </button>
                        <button type="submit" name="skydonate_license_action" value="deactivate" class="button button-secondary button-deactivate">
                            <span class="dashicons dashicons-no"></span> Deactivate
                        </button>
                    <?php else : ?>
                        <button type="submit" name="skydonate_license_action" value="activate" class="button button-primary button-hero">
                            <span class="dashicons dashicons-yes"></span> Activate License
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <p class="license-form__help">
                <span class="dashicons dashicons-info"></span>
                Enter your license key to unlock all features.
                <a href="https://skydonate.com" target="_blank">Get a license</a>
            </p>
        </form>
    </div>

    <?php if ( $status === 'valid' && ! empty( $data ) ) : ?>

        <!-- Features Section -->
        <?php if ( ! empty( $features ) ) : ?>
        <div class="license-section">
            <div class="license-section__header">
                <h3 class="license-section__title">
                    <span class="dashicons dashicons-admin-plugins"></span>
                    Features
                </h3>
                <span class="license-section__count"><?php echo count( array_filter( $features ) ); ?> enabled</span>
            </div>
            <div class="license-features-grid">
                <?php foreach ( $features as $key => $enabled ) : ?>
                    <div class="license-feature <?php echo $enabled ? 'license-feature--enabled' : 'license-feature--disabled'; ?>">
                        <span class="license-feature__icon">
                            <?php if ( $enabled ) : ?>
                                <span class="dashicons dashicons-yes"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-no"></span>
                            <?php endif; ?>
                        </span>
                        <span class="license-feature__name">
                            <?php echo esc_html( SkyDonate_License_Admin::format_feature_name( $key ) ); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Widgets Section -->
        <?php if ( ! empty( $widgets ) ) : ?>
        <div class="license-section">
            <div class="license-section__header">
                <h3 class="license-section__title">
                    <span class="dashicons dashicons-screenoptions"></span>
                    Widgets
                </h3>
                <span class="license-section__count"><?php echo count( array_filter( $widgets ) ); ?> enabled</span>
            </div>
            <div class="license-widgets-grid">
                <?php foreach ( $widgets as $key => $enabled ) : ?>
                    <div class="license-widget <?php echo $enabled ? 'license-widget--enabled' : 'license-widget--disabled'; ?>">
                        <div class="license-widget__icon">
                            <span class="dashicons dashicons-welcome-widgets-menus"></span>
                        </div>
                        <div class="license-widget__content">
                            <span class="license-widget__name">
                                <?php echo esc_html( SkyDonate_License_Admin::format_feature_name( $key ) ); ?>
                            </span>
                            <span class="license-widget__status">
                                <?php echo $enabled ? 'Enabled' : 'Disabled'; ?>
                            </span>
                        </div>
                        <div class="license-widget__badge">
                            <?php if ( $enabled ) : ?>
                                <span class="dashicons dashicons-yes-alt"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-lock"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Layouts Section -->
        <?php if ( ! empty( $layouts ) ) : ?>
        <div class="license-section">
            <div class="license-section__header">
                <h3 class="license-section__title">
                    <span class="dashicons dashicons-layout"></span>
                    Layouts
                </h3>
            </div>
            <div class="license-layouts-grid">
                <?php foreach ( $layouts as $key => $layout ) : ?>
                    <div class="license-layout">
                        <span class="license-layout__name">
                            <?php echo esc_html( SkyDonate_License_Admin::format_feature_name( $key ) ); ?>
                        </span>
                        <span class="license-layout__value">
                            <?php echo esc_html( ucfirst( str_replace( '-', ' ', $layout ) ) ); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Capabilities Section -->
        <?php if ( ! empty( $capabilities ) ) : ?>
        <div class="license-section">
            <div class="license-section__header">
                <h3 class="license-section__title">
                    <span class="dashicons dashicons-admin-network"></span>
                    Capabilities
                </h3>
            </div>
            <div class="license-capabilities-list">
                <?php foreach ( $capabilities as $key => $enabled ) : ?>
                    <div class="license-capability">
                        <span class="license-capability__toggle <?php echo $enabled ? 'toggle--on' : 'toggle--off'; ?>">
                            <span class="toggle-track">
                                <span class="toggle-thumb"></span>
                            </span>
                        </span>
                        <span class="license-capability__name">
                            <?php echo esc_html( SkyDonate_License_Admin::format_feature_name( $key ) ); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php elseif ( ! $has_license ) : ?>

        <!-- No License Placeholder -->
        <div class="license-placeholder">
            <div class="license-placeholder__icon">
                <span class="dashicons dashicons-lock"></span>
            </div>
            <h3 class="license-placeholder__title">Unlock Premium Features</h3>
            <p class="license-placeholder__description">
                Activate your license to access all premium features, widgets, and layouts.
            </p>
            <div class="license-placeholder__features">
                <div class="placeholder-feature">
                    <span class="dashicons dashicons-yes"></span>
                    All Elementor Widgets
                </div>
                <div class="placeholder-feature">
                    <span class="dashicons dashicons-yes"></span>
                    Premium Layouts
                </div>
                <div class="placeholder-feature">
                    <span class="dashicons dashicons-yes"></span>
                    Priority Support
                </div>
                <div class="placeholder-feature">
                    <span class="dashicons dashicons-yes"></span>
                    Automatic Updates
                </div>
            </div>
            <a href="https://skydonate.com" target="_blank" class="button button-primary button-hero">
                Get a License
            </a>
        </div>

    <?php endif; ?>

</div>

<script>
jQuery(document).ready(function($) {
    // Refresh license button
    $('#skydonate-refresh-license').on('click', function() {
        var $btn = $(this);
        var originalHtml = $btn.html();

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Refreshing...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'skydonate_refresh_license',
                nonce: $('#skydonate_license_nonce').val()
            },
            success: function(response) {
                location.reload();
            },
            error: function() {
                $btn.prop('disabled', false).html(originalHtml);
                alert('Failed to refresh license. Please try again.');
            }
        });
    });
});
</script>
