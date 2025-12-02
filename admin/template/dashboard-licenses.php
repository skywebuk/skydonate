<?php 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Retrieve license status and license key
$is_active = get_option('license_key_status');

$full_license_key = get_option('license_key');

// Mask the license key (show only the last 4 characters)
$masked_license_key = !empty($full_license_key) ? str_repeat('*', strlen($full_license_key) - 4) . substr($full_license_key, -4) : '';
?>

<div class="skyweb-settings-panel">
    <form class="skydonation-licenses-form">
        <!-- Hidden field for AJAX action -->
        <input type="hidden" name="action" value="save_sky_donation_settings">

        <!-- Nonce for security -->
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="license_key">License Key</label></th>
                <td>
                    <!-- License Key Input -->
                    <input 
                        type="text" 
                        name="license_key" 
                        id="license_key" 
                        class="regular-text" 
                        value="<?php echo esc_attr($masked_license_key); ?>" 
                        <?php echo $is_active === 'active' ? 'readonly' : ''; ?>
                    >

                    <!-- Activation/Deactivation Buttons -->
                    <?php if ($is_active === 'active'): ?>
                        <input type="hidden" name="status" value="deactive">
                        <button type="submit" class="button-secondary"><?php esc_html_e('Deactivate', 'skydonation'); ?></button>
                    <?php else: ?>
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="button-primary"><?php esc_html_e('Activate', 'skydonation'); ?></button>
                    <?php endif; ?>
                    
                    <!-- License Key Messages -->
                    <?php if ($is_active === 'active'): ?>
                        <p class="small">
                            <small><?php esc_html_e('✅ License is active and you are receiving updates and support.', 'skydonation'); ?></small>
                        </p>
                    <?php elseif ($is_active === 'deactive'): ?>
                        <p class="small">
                            <small><?php esc_html_e('❌ Your license is inactive. Enter your license key and activate it to unlock full functionality.', 'skydonation'); ?></small>
                        </p>
                    <?php else: ?>
                        <p class="small">
                            <small><?php esc_html_e('Enter your license key to activate the plugin.', 'skydonation'); ?></small>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </form>
</div>
