<?php 
    if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<div class="skyweb-settings-panel">
    <form class="skydonation-donation-fees-form" method="post" action="">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="enable_donation_fees">Enable Donation Fees</label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input 
                            type="checkbox" 
                            name="enable_donation_fees" 
                            id="enable_donation_fees" 
                            value="1" 
                            <?php checked( get_option('enable_donation_fees'), 1 ); ?> 
                        
                        >
                        <span class="switch"></span>
                        <small>Enable or disable the Donations fees. Check this box to activate the fees.</small>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="donation_fee_percentage">Donation Fee Percentage</label></th>
                <td>
                    <input type="number" name="donation_fee_percentage" id="donation_fee_percentage" value="<?php echo esc_attr(get_option('donation_fee_percentage', '')); ?>" min="0" step="0.01">
                    <p><small>Enter the percentage fee to apply to donations (e.g., 5 for 5%).</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="additional_text">Additional Text</label></th>
                <td>
                    <textarea name="additional_text" id="additional_text" rows="4" cols="50"><?php echo esc_textarea(get_option('additional_text', '')); ?></textarea>
                    <p><small>Add any additional text or description here.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="checkbox_label">Checkbox Label</label></th>
                <td>
                    <input type="text" name="checkbox_label" id="checkbox_label" value="<?php echo esc_attr(get_option('checkbox_label', '')); ?>">
                    <p><small>Set the label text for the donation fee checkbox.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="fees_tooltip_text">Tooltip Text</label></th>
                <td>
                    <textarea name="fees_tooltip_text" id="fees_tooltip_text" rows="4" cols="50"><?php echo esc_textarea(get_option('fees_tooltip_text', '')); ?></textarea>
                    <p><small>Add any Tooltip Text here.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="fees_checkbox_default_status">Fees Checkbox Default Status</label></th>
                <td>
                    <input type="checkbox" name="fees_checkbox_default_status" id="fees_checkbox_default_status" value="1" <?php checked(get_option('fees_checkbox_default_status'), 1); ?>>
                    <small>Check this box to make the "Enable Donation Fees" checkbox checked by default.</small>
                </td>
            </tr>
        </table>
        <br>
        <br>
        <p>
            <button type="submit" class="skydonation-button"><?php _e( 'Save Settings', 'skydonate' ); ?></button>
        </p>
    </form>
</div>
