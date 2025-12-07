<?php 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Default values
$default_description = 'Boost your donation by 25p of Gift Aid for every £1 you donate, at no extra cost to you.';
$default_checkbox_label = 'Yes, I would like to claim Gift Aid';
$default_note = 'I understand that if I pay less Income Tax and/or Capital Gains Tax than the amount of Gift Aid claimed on all my donations in that tax year it is my responsibility to pay any difference. Please remember to notify Global Helping Hands: if you want to cancel this declaration, change your name or home address or no longer pay sufficient tax on your income and/or capital gains.';
$default_logo = SKYDONATE_PUBLIC_ASSETS . '/img/gift-aid-uk-logo.svg';
?>

<div class="skydonate-settings-panel">
    <form class="skydonate-gift-aid-form" method="post" action="">
        <input type="hidden" name="action" value="save_sky_gift_aid_settings">
        <?php wp_nonce_field('save_sky_gift_aid_settings', 'sky_gift_aid_nonce'); ?>
        
        <table class="form-table">
            <!-- Enable Gift Aid -->
            <tr>
                <th scope="row">
                    <label for="enable_gift_aid"><?php esc_html_e('Enable Gift Aid', 'skydonate'); ?></label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input 
                            type="checkbox" 
                            name="enable_gift_aid" 
                            id="enable_gift_aid" 
                            value="1" 
                            <?php checked( get_option('enable_gift_aid', 0), 1 ); ?>
                        >
                        <span class="switch"></span>
                        <small><?php esc_html_e('Enable this option to activate the full Gift Aid system with advanced features and functionality.', 'skydonate'); ?></small>
                    </label>
                </td>
            </tr>
            <!-- Gift Aid Description -->
            <tr>
                <th scope="row">
                    <label for="gift_aid_description"><?php esc_html_e('Gift Aid Description', 'skydonate'); ?></label>
                </th>
                <td>
                    <textarea name="gift_aid_description" id="gift_aid_description" rows="4" cols="50"><?php echo esc_textarea(get_option('gift_aid_description', $default_description)); ?></textarea>
                    <p><small><?php esc_html_e('Enter the description text for Gift Aid shown on the donation form.', 'skydonate'); ?></small></p>
                </td>
            </tr>

            <!-- Checkbox Label -->
            <tr>
                <th scope="row">
                    <label for="gift_aid_checkbox_label"><?php esc_html_e('Checkbox Label', 'skydonate'); ?></label>
                </th>
                <td>
                    <input type="text" name="gift_aid_checkbox_label" id="gift_aid_checkbox_label" value="<?php echo esc_attr(get_option('gift_aid_checkbox_label', $default_checkbox_label)); ?>">
                    <p><small><?php esc_html_e('Set the label text for the Gift Aid checkbox.', 'skydonate'); ?></small></p>
                </td>
            </tr>

            <!-- Gift Aid Note / Tooltip -->
            <tr>
                <th scope="row">
                    <label for="gift_aid_note"><?php esc_html_e('Gift Aid Note', 'skydonate'); ?></label>
                </th>
                <td>
                    <textarea name="gift_aid_note" id="gift_aid_note" rows="6" cols="50"><?php echo esc_textarea(get_option('gift_aid_note', $default_note)); ?></textarea>
                    <p><small><?php esc_html_e('Add the note text explaining Gift Aid responsibility or instructions.', 'skydonate'); ?></small></p>
                </td>
            </tr>

            <!-- Checkbox Default Status -->
            <tr>
                <th scope="row">
                    <label for="gift_aid_default_status"><?php esc_html_e('Checkbox Default Status', 'skydonate'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="gift_aid_default_status" id="gift_aid_default_status" value="1" <?php checked(get_option('gift_aid_default_status', 1), 1); ?>>
                    <small><?php esc_html_e('Check this box to have the Gift Aid checkbox checked by default.', 'skydonate'); ?></small>
                </td>
            </tr>

            <!-- Gift Aid Logo URL -->
            <tr>
                <th scope="row">
                    <label for="gift_aid_logo"><?php esc_html_e('Gift Aid Logo URL', 'skydonate'); ?></label>
                </th>
                <td>
                    <input type="text" name="gift_aid_logo" id="gift_aid_logo" value="<?php echo esc_url(get_option('gift_aid_logo', $default_logo)); ?>">
                    <p><small><?php esc_html_e('Enter the full URL of the Gift Aid logo to display on the donation form.', 'skydonate'); ?></small></p>
                </td>
            </tr>

        </table>

        <p>
            <button type="submit" class="skydonation-button"><?php esc_html_e('Save Settings', 'skydonate'); ?></button>
        </p>

        <div class="skydonate-gift-aid-success-message" style="display:none;">
            <?php esc_html_e('Gift Aid settings saved.', 'skydonate'); ?>
        </div>
    </form>
</div>
