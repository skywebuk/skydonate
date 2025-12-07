<?php 
    if (!defined('ABSPATH')) exit; // Exit if accessed directly
    $selected_titles = get_option('select_title_prefix', []);
?>

<div class="skydonate-settings-panel">
    <form class="skydonate-general-form" method="post" action="">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
        <table class="form-table">

            <?php if(skydonate_is_feature_enabled('sky_donations_module')): ?>
            <tr>
                <th scope="row"><label for="enable_sky_donations_module">Enable Sky Donations Module</label></th>
                <td>
                    <input type="checkbox" name="enable_sky_donations_module" id="enable_sky_donations_module" value="1" <?php checked(get_option('enable_sky_donations_module'), 1); ?>>
                    <small>Enable or disable the Sky Donations module. Check this box to activate the module.</small>
                </td>
            </tr>
            <?php endif; ?>

            <?php if(skydonate_is_feature_enabled('custom_login_form')): ?>
            <tr>
                <th scope="row"><label for="enable_custom_login_form">Enable Custom Login Form</label></th>
                <td>
                    <input type="checkbox" name="enable_custom_login_form" id="enable_custom_login_form" value="1" <?php checked(get_option('enable_custom_login_form'), 1); ?>>
                    <small>Enable or disable a custom login form provided by the Sky Donations plugin.</small>
                </td>
            </tr>
            <?php endif; ?>

            <?php if(skydonate_is_feature_enabled('checkout_custom_field_style')): ?>
            <tr>
                <th scope="row"><label for="checkout_custom_field_style">Checkout Custom Field Style</label></th>
                <td>
                    <input type="checkbox" name="checkout_custom_field_style" id="checkout_custom_field_style" value="1" <?php checked(get_option('checkout_custom_field_style'), 1); ?>>
                    <small>Check the styling option for custom fields during the checkout process.</small>
                </td>
            </tr>
            <?php endif; ?>

            <tr>
                <th scope="row"><label for="recent_donation_list_with_country">Recent Donation List with Country Name and Flag</label></th>
                <td>
                    <input type="checkbox" name="recent_donation_list_with_country" id="recent_donation_list_with_country" value="1" <?php checked(get_option('recent_donation_list_with_country'), 1); ?>>
                    <small>If enabled, you will add the functionality to include the Recent Donation List with country name and flag.</small>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="auto_complete_processing">Auto Complete Processing</label></th>
                <td>
                    <input type="checkbox" name="auto_complete_processing" id="auto_complete_processing" value="1" <?php checked(get_option('auto_complete_processing'), 1); ?>>
                    <small>If enabled, automatically complete donation payments on the Thank You page.</small>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="enable_donation_goal">Enable Donation Goal</label></th>
                <td>
                    <input type="checkbox" name="enable_donation_goal" id="enable_donation_goal" value="1" <?php checked(get_option('enable_donation_goal'), 1); ?>>
                    <small>If enabled, an option for a Donation Goal will be available for the donation appeals.</small>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="enable_title_prefix">Enable Title Prefix</label></th>
                <td>
                    <input type="checkbox" name="enable_title_prefix" id="enable_title_prefix" value="1" <?php checked(get_option('enable_title_prefix'), 1); ?>>
                    <small>Enable or disable a Title Prefix for donation entries.</small>
                </td>
            </tr>

            <tr class="title_prefix_row" style="<?php echo get_option('enable_title_prefix') ? '' : 'display:none;'; ?>">
                <th scope="row"><label for="select_title_prefix">Select Title Prefix</label></th>
                <td>
                    <?php
                    if (!is_array($selected_titles)) {
                        $selected_titles = !empty($selected_titles) ? explode(',', $selected_titles) : [];
                    }
                    ?>
                    <select name="select_title_prefix" class="select_type_items" id="select_title_prefix" 
                        multiple="multiple" style="width: 100%; height: 150px;">
                        <?php
                        foreach ($selected_titles as $key => $name) {
                            $selected = in_array($name, $selected_titles) ? 'selected' : '';
                            echo '<option value="' . esc_attr($name) . '" ' . $selected . '>' . esc_html($name) . '</option>';
                        }
                        ?>
                    </select>
                    <p><small>Select one or more title prefixes.</small></p>
                </td>
            </tr>

        </table>

        <br>
        <p>
            <button type="submit" class="skydonation-button"><?php _e( 'Save Settings', 'skydonate' ); ?></button>
        </p>
    </form>
</div>
