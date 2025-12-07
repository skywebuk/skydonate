<?php 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$all_currencies = get_woocommerce_currencies();
$selected_currencies = get_option('skydonate_selected_currency', []);
if (!is_array($selected_currencies)) {
    $selected_currencies = explode(',', (string) $selected_currencies);
}
$selected_currencies = array_map('strval', $selected_currencies);

$is_enabled = get_option('skydonate_currency_changer_enabled', 0);

// New geolocation settings
$geo_enabled = get_option('skydonate_geo_currency_enabled', 0);
$geo_mode = get_option('skydonate_geo_currency_mode', 'all'); // 'all' or 'selected'
$geo_default_all = get_option('skydonate_geo_default_all', 0); // New switch
?>

<div class="skydonate-settings-panel">
    <form class="skydonate-currency-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>

        <table class="form-table">

            <!-- Enable Currency -->
            <tr>
                <th scope="row">
                    <label for="skydonate_currency_changer_enabled">
                        <?php _e('Enable Currency Feature', 'skydonate'); ?>
                    </label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="skydonate_currency_changer_enabled" id="skydonate_currency_changer_enabled" value="1" <?php checked($is_enabled, 1); ?>>
                        <span class="switch"></span>
                        <small><?php _e('Turn this on to activate the currency feature.', 'skydonate'); ?></small>
                    </label>
                </td>
            </tr>

            <!-- Show all by default switch -->
            <tr>
                <th scope="row">
                    <label for="skydonate_geo_default_all">
                        <?php _e('Show All Currencies', 'skydonate'); ?>
                    </label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="skydonate_geo_default_all" id="skydonate_geo_default_all" value="1" <?php checked($geo_default_all, 1); ?>>
                        <span class="switch"></span>
                        <small><?php _e('If enabled, all currencies will be shown by default and the selection box will be hidden.', 'skydonate'); ?></small>
                    </label>
                </td>
            </tr>

            <!-- Select Currencies (hidden if default all enabled) -->
            <tr class="currency-selector-row">
                <th scope="row">
                    <label for="skydonate_selected_currency">
                        <?php _e('Select Currencies', 'skydonate'); ?>
                    </label>
                </th>
                <td>
                    <select name="skydonate_selected_currency[]" id="skydonate_selected_currency" class="select_type_items" multiple="multiple" style="width:100%;">
                        <?php foreach ($all_currencies as $code => $name) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php echo in_array((string) $code, $selected_currencies, true) ? 'selected' : ''; ?>>
                                <?php echo esc_html("$name ($code)"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small><?php _e('Select one or more currencies to be available in your store.', 'skydonate'); ?></small>
                </td>
            </tr>

            <!-- Automatic Geolocation Detection -->
            <tr>
                <th scope="row">
                    <label for="skydonate_geo_currency_enabled">
                        <?php _e('Auto-detect Currency', 'skydonate'); ?>
                    </label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="skydonate_geo_currency_enabled" id="skydonate_geo_currency_enabled" value="1" <?php checked($geo_enabled, 1); ?>>
                        <span class="switch"></span>
                        <small><?php _e('Automatically detect each supporter\'s default currency based on their geolocation.', 'skydonate'); ?></small>
                    </label>

                    <div class="geo-sub-options" style="margin-top:12px; <?php echo $geo_enabled ? '' : 'display:none;'; ?>">
                        <label style="display:block; margin-bottom:5px;">
                            <input type="radio" name="skydonate_geo_currency_mode" value="all" <?php checked($geo_mode, 'all'); ?>>
                            <?php _e('Enable for all currency types', 'skydonate'); ?>
                        </label>
                        <label style="display:block;">
                            <input type="radio" name="skydonate_geo_currency_mode" value="selected" <?php checked($geo_mode, 'selected'); ?>>
                            <?php _e('Enable only for selected currencies', 'skydonate'); ?>
                        </label>
                    </div>
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

<script>
jQuery(document).ready(function($){
    // Toggle sub-options visibility
    $('#skydonate_geo_currency_enabled').on('change', function(){
        if($(this).is(':checked')){
            $('.geo-sub-options').slideDown(200);
        } else {
            $('.geo-sub-options').slideUp(200);
        }
    });
});
</script>
