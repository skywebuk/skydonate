<?php 
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>

<div class="skydonate-settings-panel">
    <form class="skydonate-api-form" method="post" action="">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
        
        <table class="form-table">
            <!-- Zakat Calculator API -->
            <tr>
                <th scope="row"><label for="zakat_calc_api">Zakat Calculator API</label></th>
                <td>
                    <input type="text" name="zakat_calc_api" id="zakat_calc_api" value="<?php echo esc_attr(get_option('zakat_calc_api', '')); ?>">
                    <p class="description">
                        Enter your GoldAPI key here. To obtain a new API key, visit the 
                        <a href="https://www.goldapi.io/" target="_blank">GoldAPI website</a> and log in using your Google account. 
                        After saving the API key, deactivate and reactivate the Sky Donation System plugin.
                    </p>
                </td>
            </tr>

            <!-- Currency API Key -->
            <tr>
                <th scope="row"><label for="currencyapi_key">CurrencyAPI Key</label></th>
                <td>
                    <input type="text" name="currencyapi_key" id="currencyapi_key" value="<?php echo esc_attr(get_option('currencyapi_key', '')); ?>">
                    <p class="description">
                        Enter your <strong>CurrencyAPI</strong> key here. You can get your free or paid API key from 
                        <a href="https://app.currencyapi.com/dashboard" target="_blank">CurrencyAPI Dashboard</a>.
                        After saving, please deactivate and reactivate the Sky Donation System plugin to apply changes.
                    </p>
                </td>
            </tr>

        </table>

        <br>
        <p>
            <button type="submit" class="skydonation-button">
                <?php _e('Save Settings', 'skydonate'); ?>
            </button>
        </p>
    </form>
</div>
