<?php
/**
 * License Page Template
 *
 * Modern license management interface
 *
 * @package    Skyweb_Donation_System
 */

if (!defined('ABSPATH')) {
    exit;
}

$license_manager = skydonate_license();
$is_licensed = $license_manager->is_license_valid();
$license_status = $license_manager->get_license_status();
$license_key = $license_manager->get_license_key();
$expiry_date = $license_manager->get_expiry_date();
$days_until_expiry = $license_manager->get_days_until_expiry();
$features = $license_manager->get_features();
$capabilities = $license_manager->get_capabilities();

// Mask the license key
$masked_key = !empty($license_key) ? 'SKY-****-****-****-' . substr($license_key, -4) : '';

// Define all available features
$all_features = array(
    'pro_widgets' => array(
        'label' => __('Pro Widgets', 'skydonate'),
        'description' => __('Access to premium Elementor widgets and layouts', 'skydonate'),
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
    ),
    'advanced_seo' => array(
        'label' => __('Advanced SEO', 'skydonate'),
        'description' => __('Enhanced SEO features for donation pages', 'skydonate'),
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>',
    ),
    'ai' => array(
        'label' => __('AI Features', 'skydonate'),
        'description' => __('AI-powered donation suggestions and insights', 'skydonate'),
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"></path></svg>',
    ),
    'no_branding' => array(
        'label' => __('Remove Branding', 'skydonate'),
        'description' => __('Remove SkyDonate branding from frontend', 'skydonate'),
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>',
    ),
    'img_optimize' => array(
        'label' => __('Image Optimization', 'skydonate'),
        'description' => __('Automatic image optimization and lazy loading', 'skydonate'),
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
    ),
    'performance_boost' => array(
        'label' => __('Performance Boost', 'skydonate'),
        'description' => __('Advanced caching and performance optimizations', 'skydonate'),
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
    ),
);
?>

<div class="skydonate-license-wrap">
    <!-- License Status Hero -->
    <div class="license-hero <?php echo $is_licensed ? 'licensed' : 'unlicensed'; ?>">
        <div class="hero-background">
            <div class="hero-pattern"></div>
        </div>
        <div class="hero-content">
            <div class="status-badge <?php echo esc_attr($license_status); ?>">
                <?php if ($is_licensed): ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?php esc_html_e('License Active', 'skydonate'); ?>
                <?php else: ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?php esc_html_e('No Active License', 'skydonate'); ?>
                <?php endif; ?>
            </div>

            <h1 class="hero-title">
                <?php if ($is_licensed): ?>
                    <?php esc_html_e('Your License is Active', 'skydonate'); ?>
                <?php else: ?>
                    <?php esc_html_e('Activate Your License', 'skydonate'); ?>
                <?php endif; ?>
            </h1>

            <p class="hero-subtitle">
                <?php if ($is_licensed): ?>
                    <?php esc_html_e('You have full access to all premium features and automatic updates.', 'skydonate'); ?>
                <?php else: ?>
                    <?php esc_html_e('Enter your license key to unlock premium features and automatic updates.', 'skydonate'); ?>
                <?php endif; ?>
            </p>

            <?php if ($is_licensed && !empty($expiry_date)): ?>
            <div class="expiry-info">
                <?php if ($days_until_expiry !== null && $days_until_expiry <= 30): ?>
                    <span class="expiry-warning">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        <?php printf(esc_html__('Expires in %d days', 'skydonate'), $days_until_expiry); ?>
                    </span>
                <?php else: ?>
                    <span class="expiry-date">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <?php printf(esc_html__('Valid until: %s', 'skydonate'), date_i18n(get_option('date_format'), strtotime($expiry_date))); ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- License Form Card -->
    <div class="license-form-card">
        <div class="form-card-header">
            <h2>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <?php esc_html_e('License Key', 'skydonate'); ?>
            </h2>
        </div>
        <div class="form-card-body">
            <form id="skydonate-license-form" class="license-form">
                <?php wp_nonce_field('skydonate_license_nonce', 'license_nonce'); ?>

                <div class="form-group">
                    <label for="license_key_input"><?php esc_html_e('Enter your license key', 'skydonate'); ?></label>
                    <div class="input-group">
                        <div class="input-prefix">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="license_key_input"
                            name="license_key"
                            class="license-input"
                            placeholder="SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX"
                            value="<?php echo esc_attr($is_licensed ? $masked_key : ''); ?>"
                            <?php echo $is_licensed ? 'readonly' : ''; ?>
                            autocomplete="off"
                        >
                        <?php if ($is_licensed): ?>
                        <button type="button" class="input-action" id="toggle-license-visibility" title="<?php esc_attr_e('Show/Hide License', 'skydonate'); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                    <p class="form-hint">
                        <?php esc_html_e('Your license key was sent to your email after purchase.', 'skydonate'); ?>
                        <a href="https://skydonate.com/my-account/" target="_blank"><?php esc_html_e('Forgot your key?', 'skydonate'); ?></a>
                    </p>
                </div>

                <div class="form-actions">
                    <?php if ($is_licensed): ?>
                        <button type="button" id="check-license-btn" class="skydonate-btn skydonate-btn-outline">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                            </svg>
                            <?php esc_html_e('Refresh Status', 'skydonate'); ?>
                        </button>
                        <button type="button" id="deactivate-license-btn" class="skydonate-btn skydonate-btn-danger">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <?php esc_html_e('Deactivate License', 'skydonate'); ?>
                        </button>
                    <?php else: ?>
                        <button type="submit" id="activate-license-btn" class="skydonate-btn skydonate-btn-primary skydonate-btn-lg">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <?php esc_html_e('Activate License', 'skydonate'); ?>
                        </button>
                    <?php endif; ?>
                </div>

                <div id="license-message" class="license-message" style="display: none;"></div>
            </form>
        </div>
    </div>

    <!-- Features Grid -->
    <div class="features-section">
        <h2 class="section-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <?php esc_html_e('Premium Features', 'skydonate'); ?>
        </h2>
        <p class="section-subtitle"><?php esc_html_e('Your license includes access to these powerful features', 'skydonate'); ?></p>

        <div class="features-grid">
            <?php foreach ($all_features as $feature_key => $feature): ?>
                <?php
                $is_enabled = isset($features[$feature_key]) && $features[$feature_key] === true;
                $feature_class = $is_enabled ? 'enabled' : 'disabled';
                ?>
                <div class="feature-card <?php echo esc_attr($feature_class); ?>">
                    <div class="feature-icon">
                        <?php echo $feature['icon']; ?>
                    </div>
                    <div class="feature-content">
                        <h3 class="feature-title"><?php echo esc_html($feature['label']); ?></h3>
                        <p class="feature-description"><?php echo esc_html($feature['description']); ?></p>
                    </div>
                    <div class="feature-status">
                        <?php if ($is_enabled): ?>
                            <span class="status-enabled">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                        <?php else: ?>
                            <span class="status-disabled">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Help Section -->
    <div class="help-section">
        <div class="help-card">
            <div class="help-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="help-content">
                <h3><?php esc_html_e('Need Help?', 'skydonate'); ?></h3>
                <p><?php esc_html_e('If you have any issues with your license, our support team is here to help.', 'skydonate'); ?></p>
            </div>
            <div class="help-actions">
                <a href="https://skydonate.com/support/" target="_blank" class="skydonate-btn skydonate-btn-outline">
                    <?php esc_html_e('Contact Support', 'skydonate'); ?>
                </a>
                <a href="https://skydonate.com/docs/" target="_blank" class="skydonate-btn skydonate-btn-text">
                    <?php esc_html_e('Documentation', 'skydonate'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('#skydonate-license-form');
    var $message = $('#license-message');
    var $input = $('#license_key_input');
    var isShowingFull = false;
    var fullLicenseKey = '<?php echo esc_js($license_key); ?>';
    var maskedKey = '<?php echo esc_js($masked_key); ?>';

    // Toggle license visibility
    $('#toggle-license-visibility').on('click', function() {
        isShowingFull = !isShowingFull;
        $input.val(isShowingFull ? fullLicenseKey : maskedKey);
    });

    // Activate license
    $form.on('submit', function(e) {
        e.preventDefault();

        var licenseKey = $input.val().trim();

        if (!licenseKey) {
            showMessage('error', '<?php esc_html_e('Please enter a license key.', 'skydonate'); ?>');
            return;
        }

        var $btn = $('#activate-license-btn');
        $btn.prop('disabled', true).addClass('loading');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'skydonate_activate_license',
                license_key: licenseKey,
                nonce: $('#license_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.data.message || '<?php esc_html_e('License activated successfully!', 'skydonate'); ?>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('error', response.data.message || '<?php esc_html_e('Failed to activate license.', 'skydonate'); ?>');
                }
            },
            error: function() {
                showMessage('error', '<?php esc_html_e('Connection error. Please try again.', 'skydonate'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('loading');
            }
        });
    });

    // Deactivate license
    $('#deactivate-license-btn').on('click', function() {
        if (!confirm('<?php esc_html_e('Are you sure you want to deactivate your license?', 'skydonate'); ?>')) {
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).addClass('loading');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'skydonate_deactivate_license',
                nonce: $('#license_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.data.message || '<?php esc_html_e('License deactivated.', 'skydonate'); ?>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showMessage('error', response.data.message || '<?php esc_html_e('Failed to deactivate license.', 'skydonate'); ?>');
                }
            },
            error: function() {
                showMessage('error', '<?php esc_html_e('Connection error. Please try again.', 'skydonate'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('loading');
            }
        });
    });

    // Check license status
    $('#check-license-btn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).addClass('loading');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'skydonate_check_license',
                nonce: $('#license_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', '<?php esc_html_e('License is valid and active.', 'skydonate'); ?>');
                } else {
                    showMessage('warning', response.data.message || '<?php esc_html_e('License validation issue.', 'skydonate'); ?>');
                }
            },
            error: function() {
                showMessage('error', '<?php esc_html_e('Connection error. Please try again.', 'skydonate'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('loading');
            }
        });
    });

    function showMessage(type, text) {
        $message.removeClass('success error warning').addClass(type).html(text).fadeIn();
        setTimeout(function() {
            $message.fadeOut();
        }, 5000);
    }
});
</script>
