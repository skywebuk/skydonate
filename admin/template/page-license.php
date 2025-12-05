<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$info = SkyDonate_License_Admin::get_info();
$is_valid = $info['is_valid'];
$nonce = wp_create_nonce( 'skydonate_license_nonce' );
?>

<div class="skydonate-license-page <?php echo ! $is_valid ? 'license-inactive' : ''; ?>">

    <!-- Toaster Container -->
    <div id="skydonate-toaster"></div>

    <?php if ( ! $is_valid ) : ?>
        <!-- Activation Form -->
        <div class="license-activation-wrapper">
            <div class="license-activation-card">
                <div class="license-card-icon">
                    <span class="dashicons dashicons-shield"></span>
                </div>

                <h1>Activate SkyDonate</h1>
                <p class="license-subtitle">Enter your license key to unlock all features</p>

                <form id="skydonate-activate-form" class="license-form">
                    <div class="license-input-group">
                        <input
                            type="text"
                            name="license_key"
                            id="license_key"
                            placeholder="SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX"
                            value="<?php echo esc_attr( $info['key'] ); ?>"
                            autocomplete="off"
                            required
                        />
                    </div>

                    <button type="submit" class="license-btn license-btn-primary" id="activate-btn">
                        <span class="dashicons dashicons-yes"></span>
                        <span class="btn-text">Activate License</span>
                        <span class="btn-loading" style="display:none;">Activating...</span>
                    </button>

                    <?php if ( $info['key'] && ! $is_valid && $info['message'] ) : ?>
                        <p class="license-error-msg">
                            <span class="dashicons dashicons-warning"></span>
                            <?php echo esc_html( $info['message'] ); ?>
                        </p>
                    <?php endif; ?>
                </form>

                <div class="license-card-footer">
                    <a href="https://skydonate.com" target="_blank">Get a license</a>
                    <span>|</span>
                    <a href="https://skydonate.com/support" target="_blank">Need help?</a>
                </div>
            </div>
        </div>

    <?php else : ?>
        <!-- License Dashboard -->
        <div class="license-dashboard">

            <!-- Header -->
            <div class="license-header">
                <div>
                    <h1>License Management</h1>
                    <p>Manage your SkyDonate license and view enabled features</p>
                </div>
                <span class="dashicons dashicons-shield license-header-icon"></span>
            </div>

            <!-- Status Card -->
            <div class="license-status-card">
                <div class="license-status-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="license-status-content">
                    <?php echo SkyDonate_License_Admin::get_badge( $info['status'] ); ?>
                    <h2>Your license is active</h2>
                    <?php if ( $info['masked_key'] ) : ?>
                        <p class="license-key-display">
                            <strong>Key:</strong> <code><?php echo esc_html( $info['masked_key'] ); ?></code>
                        </p>
                    <?php endif; ?>
                    <?php if ( $info['expires'] ) : ?>
                        <p class="license-expires">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            Expires: <?php echo esc_html( date( 'F j, Y', strtotime( $info['expires'] ) ) ); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="license-status-actions">
                    <button type="button" class="button" id="refresh-btn">
                        <span class="dashicons dashicons-update"></span> Refresh
                    </button>
                    <button type="button" class="button" id="deactivate-btn">
                        <span class="dashicons dashicons-no"></span> Deactivate
                    </button>
                </div>
            </div>

            <!-- Features -->
            <?php if ( ! empty( $info['features'] ) ) : ?>
            <div class="license-section">
                <div class="license-section-header">
                    <h3><span class="dashicons dashicons-admin-plugins"></span> Features</h3>
                    <span class="license-count"><?php echo count( array_filter( $info['features'] ) ); ?> enabled</span>
                </div>
                <div class="license-grid license-features-grid">
                    <?php foreach ( $info['features'] as $key => $enabled ) : ?>
                        <div class="license-item <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                            <span class="dashicons <?php echo $enabled ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
                            <span><?php echo esc_html( SkyDonate_License_Admin::format_name( $key ) ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Widgets -->
            <?php if ( ! empty( $info['widgets'] ) ) : ?>
            <div class="license-section">
                <div class="license-section-header">
                    <h3><span class="dashicons dashicons-screenoptions"></span> Widgets</h3>
                    <span class="license-count"><?php echo count( array_filter( $info['widgets'] ) ); ?> enabled</span>
                </div>
                <div class="license-grid license-widgets-grid">
                    <?php foreach ( $info['widgets'] as $key => $enabled ) : ?>
                        <div class="license-widget-item <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                            <span class="dashicons dashicons-welcome-widgets-menus"></span>
                            <div class="widget-info">
                                <span class="widget-name"><?php echo esc_html( SkyDonate_License_Admin::format_name( $key ) ); ?></span>
                                <span class="widget-status"><?php echo $enabled ? 'Enabled' : 'Disabled'; ?></span>
                            </div>
                            <span class="dashicons <?php echo $enabled ? 'dashicons-yes-alt' : 'dashicons-lock'; ?>"></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Layouts -->
            <?php if ( ! empty( $info['layouts'] ) ) : ?>
            <div class="license-section">
                <div class="license-section-header">
                    <h3><span class="dashicons dashicons-layout"></span> Layouts</h3>
                </div>
                <div class="license-grid license-layouts-grid">
                    <?php foreach ( $info['layouts'] as $key => $layout ) : ?>
                        <div class="license-layout-item">
                            <span class="layout-name"><?php echo esc_html( SkyDonate_License_Admin::format_name( $key ) ); ?></span>
                            <span class="layout-value"><?php echo esc_html( ucfirst( str_replace( '-', ' ', $layout ) ) ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Capabilities -->
            <?php if ( ! empty( $info['capabilities'] ) ) : ?>
            <div class="license-section">
                <div class="license-section-header">
                    <h3><span class="dashicons dashicons-admin-network"></span> Capabilities</h3>
                </div>
                <div class="license-capabilities-list">
                    <?php foreach ( $info['capabilities'] as $key => $enabled ) : ?>
                        <div class="license-capability">
                            <span class="capability-toggle <?php echo $enabled ? 'on' : 'off'; ?>">
                                <span class="toggle-track"><span class="toggle-thumb"></span></span>
                            </span>
                            <span><?php echo esc_html( SkyDonate_License_Admin::format_name( $key ) ); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

</div>

<style>
/* Toaster Styles */
#skydonate-toaster {
    position: fixed;
    top: 40px;
    right: 20px;
    z-index: 999999;
}
.skydonate-toast {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    margin-bottom: 10px;
    border-radius: 6px;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: slideIn 0.3s ease;
    min-width: 280px;
}
.skydonate-toast.success {
    background: linear-gradient(135deg, #10b981, #059669);
}
.skydonate-toast.error {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}
.skydonate-toast.info {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}
.skydonate-toast .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
.skydonate-toast.hiding {
    animation: slideOut 0.3s ease forwards;
}

/* Button loading state */
.license-btn.loading .btn-text,
.button.loading .dashicons { display: none; }
.license-btn.loading .btn-loading { display: inline !important; }
.button.loading { opacity: 0.7; pointer-events: none; }
</style>

<script>
jQuery(function($) {
    var nonce = '<?php echo esc_js( $nonce ); ?>';

    // Toaster function
    function showToast(message, type) {
        type = type || 'info';
        var icon = type === 'success' ? 'yes-alt' : (type === 'error' ? 'warning' : 'info');
        var $toast = $('<div class="skydonate-toast ' + type + '">' +
            '<span class="dashicons dashicons-' + icon + '"></span>' +
            '<span>' + message + '</span>' +
        '</div>');

        $('#skydonate-toaster').append($toast);

        setTimeout(function() {
            $toast.addClass('hiding');
            setTimeout(function() { $toast.remove(); }, 300);
        }, 4000);
    }

    // Activate License
    $('#skydonate-activate-form').on('submit', function(e) {
        e.preventDefault();

        var $btn = $('#activate-btn');
        var key = $('#license_key').val();

        if (!key) {
            showToast('Please enter a license key', 'error');
            return;
        }

        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
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
                    showToast(response.data.message || 'Activation failed', 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                }
            },
            error: function() {
                showToast('Connection error. Please try again.', 'error');
                $btn.removeClass('loading').prop('disabled', false);
            }
        });
    });

    // Deactivate License
    $('#deactivate-btn').on('click', function() {
        if (!confirm('Are you sure you want to deactivate this license?')) {
            return;
        }

        var $btn = $(this);
        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
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
                    showToast(response.data.message || 'Deactivation failed', 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                }
            },
            error: function() {
                showToast('Connection error. Please try again.', 'error');
                $btn.removeClass('loading').prop('disabled', false);
            }
        });
    });

    // Refresh License
    $('#refresh-btn').on('click', function() {
        var $btn = $(this);
        $btn.addClass('loading').prop('disabled', true);
        $btn.find('.dashicons').addClass('spin');

        $.ajax({
            url: ajaxurl,
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
                    showToast(response.data.message || 'Refresh failed', 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                    $btn.find('.dashicons').removeClass('spin');
                }
            },
            error: function() {
                showToast('Connection error. Please try again.', 'error');
                $btn.removeClass('loading').prop('disabled', false);
                $btn.find('.dashicons').removeClass('spin');
            }
        });
    });
});
</script>
