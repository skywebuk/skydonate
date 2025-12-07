<?php 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Default values
$default_api_key       = get_option('address_autoload_api_key', '');
$default_status        = get_option('address_autoload_status', 1); // 1 = enabled
$default_label         = get_option('address_autoload_label', 'Start typinng your address');
$default_placeholder   = get_option('address_autoload_placeholder', 'Type your first line of address');
$default_provider      = get_option('address_autoload_provider', 'legacy'); // default to 'new'
$default_address2_mode = get_option('address_autoload_address2_mode', 'normal'); // default: normal
?>

<div class="skydonate-settings-panel">
    <form class="skydonate-address-autoload-form" method="post" action="">
        <table class="form-table">
            <!-- Enable Address Auto Load -->
            <tr>
                <th scope="row">
                    <label for="address_autoload_status"><?php esc_html_e('Enable Address Auto Load', 'skydonate'); ?></label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input 
                            type="checkbox" 
                            name="address_autoload_status" 
                            id="address_autoload_status" 
                            value="1" 
                            <?php checked($default_status, 1); ?>
                        >
                        <span class="switch"></span>
                        <small><?php esc_html_e('Enable address autocomplete in WooCommerce checkout fields.', 'skydonate'); ?></small>
                    </label>
                </td>
            </tr>

            <!-- Google Places API Key -->
            <tr>
                <th scope="row">
                    <label for="address_autoload_api_key"><?php esc_html_e('Google Places API Key', 'skydonate'); ?></label>
                </th>
                <td>
                    <input type="text" name="address_autoload_api_key" id="address_autoload_api_key" value="<?php echo esc_attr($default_api_key); ?>" style="width:100%;">
                    <p><small>
                        <?php esc_html_e('Enter your Google Places API key to enable address autocomplete.', 'skydonate'); ?> 
                        <a href="https://ultimateelementor.com/docs/get-google-places-api-key/" target="_blank"><?php esc_html_e('Get API Key', 'skydonate'); ?></a>
                    </small></p>
                </td>
            </tr>
            

            <!-- New Input Label -->
            <tr>
                <th scope="row">
                    <label for="address_autoload_label"><?php esc_html_e('Input Label', 'skydonate'); ?></label>
                </th>
                <td>
                    <input 
                        type="text" 
                        name="address_autoload_label" 
                        id="address_autoload_label" 
                        value="<?php echo esc_attr($default_label); ?>" 
                        style="width:100%;"
                    >
                    <p><small><?php esc_html_e('Set the label text displayed above the address input field.', 'skydonate'); ?></small></p>
                </td>
            </tr>


            <!-- Input Placeholder -->
            <tr>
                <th scope="row">
                    <label for="address_autoload_placeholder"><?php esc_html_e('Input Placeholder', 'skydonate'); ?></label>
                </th>
                <td>
                    <input type="text" name="address_autoload_placeholder" id="address_autoload_placeholder" value="<?php echo esc_attr($default_placeholder); ?>" style="width:100%;">
                    <p><small><?php esc_html_e('Set the placeholder text for the address input field.', 'skydonate'); ?></small></p>
                </td>
            </tr>


            <!-- Address Lookup Provider -->
            <tr>
                <th scope="row">
                    <label for="address_autoload_provider"><?php esc_html_e('Address Lookup Provider', 'skydonate'); ?></label>
                </th>
                <td>
                    <select name="address_autoload_provider" id="address_autoload_provider" style="width:100%;">
                        <option value="legacy" <?php selected($default_provider, 'legacy'); ?>><?php esc_html_e('Google Places API (Legacy)', 'skydonate'); ?></option>
                        <option value="new" <?php selected($default_provider, 'new'); ?>><?php esc_html_e('Google Places API (New)', 'skydonate'); ?></option>
                    </select>
                    <p><small><?php esc_html_e('Choose which Google Places API version to use for address autocomplete.', 'skydonate'); ?></small></p>
                </td>
            </tr>


            <tr>
                <th scope="row">
                    <label for="address_autoload_address2_mode"><?php esc_html_e('Address Line 2 Handling', 'skydonate'); ?></label>
                </th>
                <td>
                    <select name="address_autoload_address2_mode" id="address_autoload_address2_mode" style="width:100%;">
                        <option value="normal" <?php selected($default_address2_mode, 'normal'); ?>><?php esc_html_e('Populate Address Line 2 normally', 'skydonate'); ?></option>
                        <option value="subpremise_only" <?php selected($default_address2_mode, 'subpremise_only'); ?>><?php esc_html_e('Only populate with "subpremise"', 'skydonate'); ?></option>
                        <option value="append_to_line1" <?php selected($default_address2_mode, 'append_to_line1'); ?>><?php esc_html_e('Append to the first line of the address', 'skydonate'); ?></option>
                    </select>
                    <p><small><?php esc_html_e('Choose how the Address Line 2 field should be populated from Google Places.', 'skydonate'); ?></small></p>
                </td>
            </tr>


        </table>

        <p>
            <button type="submit" class="skydonate-button"><?php esc_html_e('Save Settings', 'skydonate'); ?></button>
        </p>

        <div class="skydonate-address-autoload-success-message" style="display:none;">
            <?php esc_html_e('Address Auto Load settings saved.', 'skydonate'); ?>
        </div>
    </form>
</div>
