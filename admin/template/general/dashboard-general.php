<?php
if (!defined('ABSPATH')) exit;

$selected_titles = get_option('select_title_prefix', []);
if (!is_array($selected_titles)) {
    $selected_titles = !empty($selected_titles) ? explode(',', $selected_titles) : [];
}

$currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '£';
$monthly_goal = get_option('skydonate_monthly_goal', 10000);
?>

<style>
.settings-wrap {
    max-width: 1200px;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}

.settings-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}

.settings-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    background: #f8fafc;
}

.settings-card-header svg {
    width: 20px;
    height: 20px;
    color: #6366f1;
}

.settings-card-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.settings-card-body {
    padding: 24px;
}

.setting-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}

.setting-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.setting-row:first-child {
    padding-top: 0;
}

.setting-info {
    flex: 1;
    padding-right: 20px;
}

.setting-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 4px;
}

.setting-desc {
    font-size: 13px;
    color: #64748b;
    line-height: 1.4;
}

.setting-control {
    flex-shrink: 0;
}

/* Modern Toggle Switch */
.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.3s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #6366f1;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(20px);
}

.toggle-switch input:disabled + .toggle-slider {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Input Fields */
.setting-input {
    width: 100%;
    max-width: 300px;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.setting-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.setting-input:disabled {
    background: #f8fafc;
    cursor: not-allowed;
}

.setting-select {
    width: 100%;
    max-width: 300px;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    cursor: pointer;
}

.setting-select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Tags Input */
.tags-input-container {
    max-width: 300px;
}

.tags-input {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    min-height: 42px;
    background: #fff;
}

.tags-input:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.tag-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #f1f5f9;
    border-radius: 6px;
    font-size: 13px;
    color: #475569;
}

.tag-remove {
    cursor: pointer;
    color: #94a3b8;
    font-size: 16px;
    line-height: 1;
}

.tag-remove:hover {
    color: #ef4444;
}

.tag-input-field {
    border: none;
    outline: none;
    flex: 1;
    min-width: 100px;
    font-size: 14px;
    padding: 4px;
}

/* Save Button */
.settings-footer {
    display: flex;
    justify-content: flex-end;
    padding: 20px 0;
    border-top: 1px solid #e2e8f0;
    margin-top: 20px;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-save:hover {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-save svg {
    width: 16px;
    height: 16px;
}

/* Section Headers */
.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.section-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.section-header p {
    color: #64748b;
    margin: 4px 0 0;
    font-size: 14px;
}

/* Full Width Card */
.settings-card-full {
    grid-column: 1 / -1;
}

/* Notice */
.license-notice {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 10px;
    margin-bottom: 24px;
}

.license-notice svg {
    width: 20px;
    height: 20px;
    color: #d97706;
    flex-shrink: 0;
}

.license-notice p {
    margin: 0;
    font-size: 14px;
    color: #92400e;
}

.license-notice a {
    color: #d97706;
    font-weight: 500;
}
</style>

<div class="settings-wrap">
    <form class="skydonation-general-form" method="post" action="">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>

        <?php if (!LTUS): ?>
        <div class="license-notice">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p><?php _e('Some settings are disabled. <a href="admin.php?page=skydonation-licenses">Activate your license</a> to unlock all features.', 'skydonate'); ?></p>
        </div>
        <?php endif; ?>

        <div class="settings-grid">
            <!-- General Settings Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <h3><?php _e('General Settings', 'skydonate'); ?></h3>
                </div>
                <div class="settings-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Enable Donations Module', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Enable or disable the Sky Donations module.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="enable_sky_donations_module" value="1" <?php checked(get_option('enable_sky_donations_module'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Custom Login Form', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Enable a custom login form for donors.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="enable_custom_login_form" value="1" <?php checked(get_option('enable_custom_login_form'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Checkout Custom Style', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Apply custom styling to checkout fields.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="checkout_custom_field_style" value="1" <?php checked(get_option('checkout_custom_field_style'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Show Country Flags', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Display country flags in recent donations list.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="recent_donation_list_with_country" value="1" <?php checked(get_option('recent_donation_list_with_country'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Auto Complete Orders', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Automatically complete donations on Thank You page.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="auto_complete_processing" value="1" <?php checked(get_option('auto_complete_processing'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Enable Donation Goals', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Allow setting fundraising goals for campaigns.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="enable_donation_goal" value="1" <?php checked(get_option('enable_donation_goal'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Settings Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <h3><?php _e('System Settings', 'skydonate'); ?></h3>
                </div>
                <div class="settings-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Monthly Fundraising Goal', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Set your monthly donation target for the dashboard.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <input type="number" name="skydonate_monthly_goal" class="setting-input" value="<?php echo esc_attr($monthly_goal); ?>" min="0" step="100" <?php echo LTUS ? '' : 'disabled'; ?>>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Text Replacements', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Replace WooCommerce labels with donation-friendly text.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="register_text_replacements" value="1" <?php checked(get_option('register_text_replacements'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('WooCommerce UI Tweaks', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Apply styling and checkout improvements.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_woocommerce_customizations" value="1" <?php checked(get_option('init_woocommerce_customizations'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Admin Menu Labels', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Rename menu items (Products → Campaigns).', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_menu_label_changes" value="1" <?php checked(get_option('init_menu_label_changes'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Order Search by Postcode', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Enable searching orders by customer postcode.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_order_postcode_search" value="1" <?php checked(get_option('init_order_postcode_search'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Project Column in Orders', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Show campaign/project name in orders list.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_project_column_for_orders" value="1" <?php checked(get_option('init_project_column_for_orders'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Settings Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <h3><?php _e('Checkout Settings', 'skydonate'); ?></h3>
                </div>
                <div class="settings-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Guest Checkout for Existing', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Allow existing customers to checkout without login.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_guest_checkout_for_existing_customers" value="1" <?php checked(get_option('init_guest_checkout_for_existing_customers'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Password Note', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Show helpful password requirements note.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="add_checkout_password_note" value="1" <?php checked(get_option('add_checkout_password_note'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Email Typo Correction', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Auto-suggest corrections for email typos.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_checkout_email_typo_correction" value="1" <?php checked(get_option('init_checkout_email_typo_correction'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Guest Data Saver', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Preserve form data for guest checkout.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_guest_checkout_data_saver" value="1" <?php checked(get_option('init_guest_checkout_data_saver'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Custom Login Message', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Show donor-friendly login messages.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="customize_woocommerce_login_message" value="1" <?php checked(get_option('customize_woocommerce_login_message'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email & Display Settings Card -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <h3><?php _e('Email & Display', 'skydonate'); ?></h3>
                </div>
                <div class="settings-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Email Product Name Replace', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Use custom titles in WooCommerce emails.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_email_product_name_replacement" value="1" <?php checked(get_option('init_email_product_name_replacement'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Stripe Order Meta', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Store extra Stripe metadata for reporting.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="init_stripe_order_meta_modifications" value="1" <?php checked(get_option('init_stripe_order_meta_modifications'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Monthly Button Heart Icon', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Show heart icon on monthly donation buttons.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="donation_monthly_heart_icon" value="1" <?php checked(get_option('donation_monthly_heart_icon'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Enable Title Prefix', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Add title prefixes (Mr, Mrs, etc.) for donors.', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <label class="toggle-switch">
                                <input type="checkbox" name="enable_title_prefix" id="enable_title_prefix" value="1" <?php checked(get_option('enable_title_prefix'), 1); ?> <?php echo LTUS ? '' : 'disabled'; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="setting-row title-prefix-options" style="<?php echo get_option('enable_title_prefix') ? '' : 'display:none;'; ?>">
                        <div class="setting-info">
                            <span class="setting-label"><?php _e('Title Prefixes', 'skydonate'); ?></span>
                            <span class="setting-desc"><?php _e('Add titles separated by comma (Mr, Mrs, Ms, Dr).', 'skydonate'); ?></span>
                        </div>
                        <div class="setting-control">
                            <input type="text" name="select_title_prefix" class="setting-input" value="<?php echo esc_attr(is_array($selected_titles) ? implode(', ', $selected_titles) : $selected_titles); ?>" placeholder="Mr, Mrs, Ms, Dr" <?php echo LTUS ? '' : 'disabled'; ?>>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-footer">
            <button type="submit" class="btn-save" <?php echo LTUS ? '' : 'disabled'; ?>>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <?php _e('Save Settings', 'skydonate'); ?>
            </button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle title prefix options visibility
    $('#enable_title_prefix').on('change', function() {
        if ($(this).is(':checked')) {
            $('.title-prefix-options').slideDown();
        } else {
            $('.title-prefix-options').slideUp();
        }
    });
});
</script>
